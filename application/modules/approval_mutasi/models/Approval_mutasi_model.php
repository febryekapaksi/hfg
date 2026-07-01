<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Approval_mutasi_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // LIST
    // ---------------------------------------------------------------

    public function get_list($status_list = [])
    {
        $this->db->select('
            mm.id,
            mm.mutation_number,
            mm.mutation_date,
            mm.no_berita_acara,
            mm.nm_gudang_from,
            mm.nm_gudang_to,
            mm.description,
            mm.status,
            mm.reject_reason,
            mm.approved_by,
            mm.approved_date,
            mm.create_by,
            mm.create_date
        ');
        $this->db->from('material_mutations mm');
        $this->db->where('mm.is_delete', 0);

        if (!empty($status_list)) {
            $this->db->where_in('mm.status', $status_list);
        }

        $this->db->order_by('mm.create_date', 'DESC');

        return $this->db->get()->result_array();
    }

    // ---------------------------------------------------------------
    // DETAIL
    // ---------------------------------------------------------------

    public function get_detail($id)
    {
        $this->db->select('
            mm.id,
            mm.mutation_number,
            mm.mutation_date,
            mm.no_berita_acara,
            mm.file_name_original,
            mm.file_name_hash,
            mm.id_gudang_from,
            mm.kd_gudang_from,
            mm.nm_gudang_from,
            mm.id_gudang_to,
            mm.kd_gudang_to,
            mm.nm_gudang_to,
            mm.description,
            mm.status,
            mm.reject_reason,
            mm.approved_by,
            mm.approved_date,
            mm.create_by,
            mm.create_date,
            mm.update_by,
            mm.update_date
        ');
        $this->db->from('material_mutations mm');
        $this->db->where('mm.id', $id);
        $this->db->where('mm.is_delete', 0);

        $header = $this->db->get()->row_array();

        if (!$header) return null;

        // Ambil detail material
        $details = $this->db->select('*')
            ->from('material_mutation_details')
            ->where('id_material_mutation', $id)
            ->get()->result_array();

        // Ambil coil per detail
        foreach ($details as &$detail) {
            $detail['coils'] = $this->db->select('*')
                ->from('material_mutation_details_coil')
                ->where('id_mutation_detail', $detail['id'])
                ->get()->result_array();
        }

        $header['details'] = $details;

        return $header;
    }

    // ---------------------------------------------------------------
    // APPROVE — pindah stock + catat history + summary + per_day
    // ---------------------------------------------------------------

    public function approve_mutation($id, $approved_by, $approved_date)
    {
        $this->db->trans_start();

        // Update status header
        $this->db->where('id', $id);
        $this->db->where('status', 1);
        $this->db->update('material_mutations', [
            'status'        => 2,
            'approved_by'   => $approved_by,
            'approved_date' => $approved_date,
            'update_by'     => $approved_by,
            'update_date'   => $approved_date,
        ]);

        if ($this->db->affected_rows() == 0) {
            $this->db->trans_rollback();
            return false;
        }

        // Ambil data mutasi untuk proses stock
        $mutation = $this->get_detail($id);

        $now   = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $id_gudang_from = $mutation['id_gudang_from'];
        $kd_gudang_from = $mutation['kd_gudang_from'];
        $id_gudang_to   = $mutation['id_gudang_to'];
        $kd_gudang_to   = $mutation['kd_gudang_to'];

        // Summary map untuk warehouse_incoming_summary (per material per gudang tujuan)
        $summary_map = [];

        foreach ($mutation['details'] as $detail) {
            $id_material = $detail['code_lv4'];  // code_lv4 = key material di warehouse_stock & warehouse_stock_coil
            $nm_material = $detail['nm_material'];
            $code_lv4    = $detail['code_lv4'];

            foreach ($detail['coils'] as $coil) {
                // Ambil data terkini langsung dari warehouse_stock_coil
                $live_coil = $this->db->query("
                    SELECT * FROM warehouse_stock_coil
                    WHERE id = ? LIMIT 1 FOR UPDATE
                ", [$coil['id_warehouse_stock_coil']])->row();

                // Gunakan harga_beli dari warehouse_stock_coil (harga asli per coil)
                $net_weight    = (float) ($live_coil ? $live_coil->net_weight : ($coil['net_weight'] ?? 0));
                $gross_weight  = (float) ($live_coil ? $live_coil->gross_weight : ($coil['gross_weight'] ?? 0));
                $length        = (float) ($live_coil ? $live_coil->length : ($coil['length'] ?? 0));
                $harga_beli    = (float) ($live_coil ? $live_coil->harga_beli : ($coil['harga_beli'] ?? 0));
                $kode_internal = $live_coil ? $live_coil->kode_internal : ($coil['kode_internal'] ?? '');
                $no_coil       = $live_coil ? $live_coil->no_coil : ($coil['no_coil'] ?? '');
                $no_ipp        = $live_coil ? $live_coil->no_ipp : ($coil['no_ipp'] ?? '');

                // ═══════════════════════════════════════════════════════════
                // A. GUDANG ASAL — kurangi stock
                // ═══════════════════════════════════════════════════════════

                $stock_from = $this->db->query("
                    SELECT * FROM warehouse_stock
                    WHERE code_lv4 = ? AND id_gudang = ?
                    LIMIT 1 FOR UPDATE
                ", [$id_material, $id_gudang_from])->row();

                if ($stock_from) {
                    $qty_awal_from      = (float) $stock_from->qty_stock;
                    $qty_free_from      = (float) $stock_from->qty_free;
                    $saldo_awal_from    = (float) $stock_from->total_nilai;
                    $harga_lama_from    = (float) $stock_from->harga_beli;
                    $outgoing_from      = (float) $stock_from->outgoing;

                    $qty_akhir_from     = $qty_awal_from - $net_weight;
                    // Nilai keluar berdasarkan harga_beli dari warehouse_stock_coil
                    $nilai_keluar       = $harga_beli * $net_weight;
                    $saldo_akhir_from   = $saldo_awal_from - $nilai_keluar;
                    $costbook_from      = $qty_akhir_from > 0
                        ? ($saldo_akhir_from / $qty_akhir_from)
                        : $harga_lama_from;

                    $this->db->update('warehouse_stock', [
                        'outgoing'    => $outgoing_from + $net_weight,
                        'qty_stock'   => $qty_akhir_from,
                        'qty_free'    => $qty_free_from - $net_weight,
                        'harga_beli'  => $costbook_from,
                        'total_nilai' => $saldo_akhir_from,
                        'update_by'   => $approved_by,
                        'update_date' => $now,
                    ], ['id' => $stock_from->id]);

                    // warehouse_stock_per_day (gudang asal)
                    $this->_upsert_stock_per_day(
                        $id_material, $nm_material, $id_gudang_from, $kd_gudang_from,
                        $qty_akhir_from, (float) $stock_from->qty_booking,
                        $qty_free_from - $net_weight, $costbook_from,
                        $saldo_akhir_from, $now, $approved_by
                    );

                    // warehouse_history (gudang asal - OUT)
                    $this->db->insert('warehouse_history', [
                        'id_material'     => $id_material,
                        'nm_material'     => $nm_material,
                        'no_coil'         => $no_coil,
                        'nm_category'     => 'Mutasi Keluar',
                        'id_gudang'       => $id_gudang_from,
                        'kd_gudang'       => $kd_gudang_from,
                        'id_gudang_dari'  => $id_gudang_from,
                        'kd_gudang_dari'  => $kd_gudang_from,
                        'id_gudang_ke'    => $id_gudang_to,
                        'kd_gudang_ke'    => $kd_gudang_to,
                        'qty_stock_awal'  => $qty_awal_from,
                        'qty_stock_akhir' => $qty_akhir_from,
                        'no_ipp'          => $no_ipp,
                        'jumlah_mat'      => $net_weight,
                        'ket'             => 'Mutasi Keluar ' . $mutation['mutation_number'] . ' ke ' . $mutation['nm_gudang_to'],
                        'harga_beli'      => $harga_beli,
                        'total_harga'     => $nilai_keluar,
                        'saldo_awal'      => $saldo_awal_from,
                        'saldo_akhir'     => $saldo_akhir_from,
                        'harga_baru'      => $costbook_from,
                        'harga_lama'      => $harga_lama_from,
                        'update_by'       => $approved_by,
                        'update_date'     => $now,
                    ]);
                }

                // ═══════════════════════════════════════════════════════════
                // B. GUDANG TUJUAN — tambah stock
                // ═══════════════════════════════════════════════════════════

                $stock_to = $this->db->query("
                    SELECT * FROM warehouse_stock
                    WHERE code_lv4 = ? AND id_gudang = ?
                    LIMIT 1 FOR UPDATE
                ", [$id_material, $id_gudang_to])->row();

                $qty_awal_to   = $stock_to ? (float) $stock_to->qty_stock   : 0;
                $saldo_awal_to = $stock_to ? (float) $stock_to->total_nilai : 0;
                $harga_lama_to = $stock_to ? (float) $stock_to->harga_beli  : 0;
                $incoming_to   = $stock_to ? (float) $stock_to->incoming    : 0;
                $qty_free_to   = $stock_to ? (float) $stock_to->qty_free    : 0;
                $qty_book_to   = $stock_to ? (float) $stock_to->qty_booking : 0;

                // Harga masuk = harga_beli dari warehouse_stock_coil (harga asli per coil)
                $price_in        = $harga_beli;
                $nilai_masuk     = $price_in * $net_weight;
                $qty_akhir_to    = $qty_awal_to + $net_weight;
                $saldo_akhir_to  = $saldo_awal_to + $nilai_masuk;
                $costbook_to     = $qty_akhir_to > 0
                    ? ($saldo_akhir_to / $qty_akhir_to)
                    : $price_in;

                if (empty($stock_to)) {
                    // Ambil info tambahan dari warehouse_stock gudang asal
                    $this->db->insert('warehouse_stock', [
                        'code_lv1'        => $stock_from->code_lv1 ?? '',
                        'code_lv2'        => $stock_from->code_lv2 ?? '',
                        'code_lv3'        => $stock_from->code_lv3 ?? '',
                        'code_lv4'        => $id_material,
                        'code_incoming'   => $mutation['mutation_number'],
                        'nm_material'     => $nm_material,
                        'trade_name'      => $detail['trade_name'] ?? '',
                        'id_gudang'       => $id_gudang_to,
                        'kd_gudang'       => $kd_gudang_to,
                        'id_unit'         => $stock_from->id_unit ?? null,
                        'id_unit_packing' => $stock_from->id_unit_packing ?? null,
                        'begining'        => 0,
                        'incoming'        => $net_weight,
                        'outgoing'        => 0,
                        'qty_stock'       => $qty_akhir_to,
                        'qty_booking'     => 0,
                        'qty_free'        => $qty_akhir_to,
                        'use_qty_free'    => 0,
                        'harga_beli'      => $costbook_to,
                        'total_nilai'     => $saldo_akhir_to,
                        'update_by'       => $approved_by,
                        'update_date'     => $now,
                    ]);
                } else {
                    $this->db->update('warehouse_stock', [
                        'incoming'    => $incoming_to + $net_weight,
                        'qty_stock'   => $qty_akhir_to,
                        'qty_free'    => $qty_free_to + $net_weight,
                        'harga_beli'  => $costbook_to,
                        'total_nilai' => $saldo_akhir_to,
                        'update_by'   => $approved_by,
                        'update_date' => $now,
                    ], ['id' => $stock_to->id]);
                }

                // warehouse_stock_per_day (gudang tujuan)
                $this->_upsert_stock_per_day(
                    $id_material, $nm_material, $id_gudang_to, $kd_gudang_to,
                    $qty_akhir_to, $qty_book_to,
                    $qty_free_to + $net_weight, $costbook_to,
                    $saldo_akhir_to, $now, $approved_by
                );

                // warehouse_history (gudang tujuan - IN)
                $this->db->insert('warehouse_history', [
                    'id_material'     => $id_material,
                    'nm_material'     => $nm_material,
                    'no_coil'         => $no_coil,
                    'nm_category'     => 'Mutasi Masuk',
                    'id_gudang'       => $id_gudang_to,
                    'kd_gudang'       => $kd_gudang_to,
                    'id_gudang_dari'  => $id_gudang_from,
                    'kd_gudang_dari'  => $kd_gudang_from,
                    'id_gudang_ke'    => $id_gudang_to,
                    'kd_gudang_ke'    => $kd_gudang_to,
                    'qty_stock_awal'  => $qty_awal_to,
                    'qty_stock_akhir' => $qty_akhir_to,
                    'no_ipp'          => $no_ipp,
                    'jumlah_mat'      => $net_weight,
                    'ket'             => 'Mutasi Masuk ' . $mutation['mutation_number'] . ' dari ' . $mutation['nm_gudang_from'],
                    'harga_beli'      => $price_in,
                    'total_harga'     => $nilai_masuk,
                    'saldo_awal'      => $saldo_awal_to,
                    'saldo_akhir'     => $saldo_akhir_to,
                    'harga_baru'      => $costbook_to,
                    'harga_lama'      => $harga_lama_to,
                    'update_by'       => $approved_by,
                    'update_date'     => $now,
                ]);

                // ═══════════════════════════════════════════════════════════
                // C. UPDATE warehouse_stock_coil — pindah gudang
                // ═══════════════════════════════════════════════════════════

                $this->db->where('id', $coil['id_warehouse_stock_coil']);
                $this->db->update('warehouse_stock_coil', [
                    'id_gudang' => $id_gudang_to,
                    'kd_gudang' => $kd_gudang_to,
                ]);

                // ═══════════════════════════════════════════════════════════
                // D. warehouse_coil_per_day (OUT dari asal, IN ke tujuan)
                // ═══════════════════════════════════════════════════════════

                // OUT dari gudang asal
                $this->_upsert_coil_per_day(
                    $id_material, $nm_material, $id_gudang_from, $kd_gudang_from,
                    $no_coil, $kode_internal, $gross_weight, $net_weight, $length,
                    'OUT', $now, $approved_by
                );

                // IN ke gudang tujuan
                $this->_upsert_coil_per_day(
                    $id_material, $nm_material, $id_gudang_to, $kd_gudang_to,
                    $no_coil, $kode_internal, $gross_weight, $net_weight, $length,
                    'IN', $now, $approved_by
                );

                // ═══════════════════════════════════════════════════════════
                // E. warehouse_incoming_summary_detail
                // ═══════════════════════════════════════════════════════════

                $this->db->insert('warehouse_incoming_summary_detail', [
                    'no_ipp'         => $mutation['mutation_number'],
                    'id_material'    => $id_material,
                    'nm_material'    => $nm_material,
                    'id_gudang'      => $id_gudang_to,
                    'kd_gudang'      => $kd_gudang_to,
                    'no_coil'        => $no_coil,
                    'kode_internal'  => $kode_internal,
                    'gross_weight'   => $gross_weight,
                    'net_weight'     => $net_weight,
                    'length'         => $length,
                    'price_per_coil' => $nilai_masuk,
                    'cost_book'      => $costbook_to,
                    'status_qc'      => '',
                    'created_at'     => $now,
                ]);

                // ═══════════════════════════════════════════════════════════
                // F. Aggregate untuk warehouse_incoming_summary
                // ═══════════════════════════════════════════════════════════

                $summary_key = $id_material . '_' . $id_gudang_to;
                if (!isset($summary_map[$summary_key])) {
                    $summary_map[$summary_key] = [
                        'no_ipp'        => $mutation['mutation_number'],
                        'id_material'   => $id_material,
                        'nm_material'   => $nm_material,
                        'id_gudang'     => $id_gudang_to,
                        'kd_gudang'     => $kd_gudang_to,
                        'tanggal'       => $today,
                        'jumlah_coil'   => 0,
                        'qty_awal'      => $qty_awal_to,
                        'qty_transaksi' => 0,
                        'qty_akhir'     => 0,
                        'costbook'      => 0,
                        'total_harga'   => 0,
                        'saldo_awal'    => $saldo_awal_to,
                        'saldo_akhir'   => 0,
                        'harga_lama'    => $harga_lama_to,
                        'created_by'    => $approved_by,
                        'created_at'    => $now,
                    ];
                }

                $summary_map[$summary_key]['jumlah_coil']++;
                $summary_map[$summary_key]['qty_transaksi'] += $net_weight;
                $summary_map[$summary_key]['total_harga']   += $nilai_masuk;
                $summary_map[$summary_key]['qty_akhir']      = $qty_akhir_to;
                $summary_map[$summary_key]['costbook']       = $costbook_to;
                $summary_map[$summary_key]['saldo_akhir']    = $saldo_akhir_to;
            }
        }

        // Insert warehouse_incoming_summary (aggregated per material)
        foreach ($summary_map as $s) {
            $this->db->insert('warehouse_incoming_summary', $s);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== FALSE;
    }

    // ---------------------------------------------------------------
    // HELPER: Upsert warehouse_stock_per_day
    // ---------------------------------------------------------------

    private function _upsert_stock_per_day(
        $id_material, $nm_material, $id_gudang, $kd_gudang,
        $qty_stock, $qty_booking, $qty_free, $harga_beli,
        $total_nilai, $now, $user
    ) {
        $today = date('Y-m-d');

        $snap = $this->db->query("
            SELECT id FROM warehouse_stock_per_day
            WHERE id_material = ? AND id_gudang = ? AND DATE(hist_date) = ?
            LIMIT 1
        ", [$id_material, $id_gudang, $today])->row();

        $snap_data = [
            'qty_stock'   => $qty_stock,
            'qty_booking' => $qty_booking,
            'qty_free'    => $qty_free,
            'harga_beli'  => $harga_beli,
            'total_nilai' => $total_nilai,
            'kd_gudang'   => $kd_gudang,
            'hist_date'   => $now,
            'hist_by'     => $user,
        ];

        if (empty($snap)) {
            $this->db->insert('warehouse_stock_per_day', array_merge([
                'id_material' => $id_material,
                'nm_material' => $nm_material,
                'id_gudang'   => $id_gudang,
            ], $snap_data));
        } else {
            $this->db->update('warehouse_stock_per_day', $snap_data, ['id' => $snap->id]);
        }
    }

    // ---------------------------------------------------------------
    // HELPER: Upsert warehouse_coil_per_day
    // ---------------------------------------------------------------

    private function _upsert_coil_per_day(
        $id_material, $nm_material, $id_gudang, $kd_gudang,
        $no_coil, $kode_internal, $gross_weight, $net_weight, $length,
        $status, $now, $user
    ) {
        $today = date('Y-m-d');

        $coil_snap = $this->db->query("
            SELECT id FROM warehouse_coil_per_day
            WHERE id_material = ? AND id_gudang = ? AND no_coil = ? AND DATE(hist_date) = ?
            LIMIT 1
        ", [$id_material, $id_gudang, $no_coil, $today])->row();

        $coil_snap_data = [
            'nm_material'   => $nm_material,
            'kd_gudang'     => $kd_gudang,
            'kode_internal' => $kode_internal,
            'gross_weight'  => $gross_weight,
            'net_weight'    => $net_weight,
            'length'        => $length,
            'status'        => $status,
            'hist_date'     => $now,
            'hist_by'       => $user,
        ];

        if (empty($coil_snap)) {
            $this->db->insert('warehouse_coil_per_day', array_merge([
                'id_material' => $id_material,
                'id_gudang'   => $id_gudang,
                'no_coil'     => $no_coil,
            ], $coil_snap_data));
        } else {
            $this->db->update('warehouse_coil_per_day', $coil_snap_data, ['id' => $coil_snap->id]);
        }
    }

    

    // ---------------------------------------------------------------
    // GENERATE JURNAL MUTASI → GL INTERFACE
    // ---------------------------------------------------------------

    public function _generate_jurnal_mutasi($mutation)
    {
        $tgl_inv    = date('Y-m-d');
        $created_on = date('Y-m-d H:i:s');
        $user_id    = $this->auth->user_id();

        // Hitung total nilai mutasi (sum net_weight × harga_beli per coil)
        $total_nilai = 0;
        foreach ($mutation['details'] as $detail) {
            foreach ($detail['coils'] as $coil) {
                // Ambil harga dari warehouse_stock_coil
                $live_coil = $this->db->query("
                    SELECT harga_beli, net_weight FROM warehouse_stock_coil WHERE id = ? LIMIT 1
                ", [$coil['id_warehouse_stock_coil']])->row();

                if ($live_coil) {
                    $total_nilai += (float)$live_coil->harga_beli * (float)$live_coil->net_weight;
                } else {
                    $total_nilai += (float)($coil['harga_beli'] ?? 0) * (float)($coil['net_weight'] ?? 0);
                }
            }
        }

        if ($total_nilai <= 0) return;

        // Tentukan COA berdasarkan arah mutasi
        // Produksi = 1105-01-01, Slitting = 1105-01-02
        $id_gudang_from = $mutation['id_gudang_from'];
        $id_gudang_to   = $mutation['id_gudang_to'];

        // Gudang Produksi (id=1) → 1105-01-01
        // Gudang Slitting (id=2) → 1105-01-02
        $coa_produksi = '1105-01-01';
        $coa_slitting = '1105-01-02';

        if ($id_gudang_from == 1) {
            // Produksi → Slitting
            $coa_debet  = $coa_slitting;  // Slitting bertambah
            $coa_kredit = $coa_produksi;  // Produksi berkurang
        } else {
            // Slitting → Produksi
            $coa_debet  = $coa_produksi;  // Produksi bertambah
            $coa_kredit = $coa_slitting;  // Slitting berkurang
        }

        // Validate COA names from coa_master
        $coa_list = [
            'debet'  => $coa_debet,
            'kredit' => $coa_kredit,
        ];
        $coa_check = $this->_validate_and_get_coa_names($coa_list);
        if (!$coa_check['valid']) {
            throw new Exception('COA not found in Master: ' . implode(', ', $coa_check['not_found']));
        }
        $coa_names = $coa_check['names'];

        $keterangan = "Mutation: {$mutation['mutation_number']} | {$mutation['nm_gudang_from']} ke {$mutation['nm_gudang_to']}";
        $nomor_jv   = $this->_generate_nomor_jv();

        // Insert header GL Interface
        $this->db->insert('gl_interface', [
            'nomor'           => $nomor_jv,
            'tgl'             => $tgl_inv,
            'bulan'           => date('m'),
            'tahun'           => date('Y'),
            'kdcab'           => '101',
            'jenis'           => 'JV',
            'keterangan'      => $keterangan,
            'jenis_transaksi' => 'mutation',
            'status'          => 'pending',
            'user_id'         => $user_id,
            'memo'            => json_encode([
                'mutation_number' => $mutation['mutation_number'],
                'gudang_from'     => $mutation['nm_gudang_from'],
                'gudang_to'       => $mutation['nm_gudang_to'],
            ]),
        ]);
        $id_gl = $this->db->insert_id();

        // DEBET — gudang tujuan bertambah
        $this->db->insert('gl_interface_detail', [
            'id_gl_interface' => $id_gl,
            'no_batch'        => $nomor_jv,
            'tipe'            => 'JV',
            'tanggal'         => $tgl_inv,
            'no_perkiraan'    => $coa_debet,
            'id_material'     => null,
            'nm_material'     => null,
            'id_gudang'       => $id_gudang_to,
            'no_coil'         => null,
            'keterangan'      => $coa_names['debet'] .  " | " .$mutation['nm_gudang_to'] ." BERTAMBAH",
            'no_reff'         => $mutation['mutation_number'],
            'no_request'      => $mutation['mutation_number'],
            'debet'           => (int) round($total_nilai),
            'kredit'          => 0,
            'created_at'      => $created_on,
        ]);

        // KREDIT — gudang asal berkurang
        $this->db->insert('gl_interface_detail', [
            'id_gl_interface' => $id_gl,
            'no_batch'        => $nomor_jv,
            'tipe'            => 'JV',
            'tanggal'         => $tgl_inv,
            'no_perkiraan'    => $coa_kredit,
            'id_material'     => null,
            'nm_material'     => null,
            'id_gudang'       => $id_gudang_from,
            'no_coil'         => null,
            'keterangan'      => $coa_names['kredit'] . " | " .$mutation['nm_gudang_from'] ." BERKURANG",
            'no_reff'         => $mutation['mutation_number'],
            'no_request'      => $mutation['mutation_number'],
            'debet'           => 0,
            'kredit'          => (int) round($total_nilai),
            'created_at'      => $created_on,
        ]);
    }

    // ---------------------------------------------------------------
    // VALIDATE COA NAMES FROM DBACC
    // ---------------------------------------------------------------

    private function _validate_and_get_coa_names(array $coa_list)
    {
        $db_acc    = $this->load->database(DBACC, TRUE);
        $not_found = [];
        $names     = [];

        foreach ($coa_list as $key => $no_perkiraan) {
            $row = $db_acc->get_where('coa_master', ['no_perkiraan' => $no_perkiraan])->row();
            if (!$row) {
                $not_found[] = $no_perkiraan;
            } else {
                $names[$key] = $row->nama;
            }
        }

        return [
            'valid'     => empty($not_found),
            'names'     => $names,
            'not_found' => $not_found,
        ];
    }

    // ---------------------------------------------------------------
    // GENERATE NOMOR JV
    // ---------------------------------------------------------------

    private function _generate_nomor_jv()
    {
        $cabang = $this->db->query(
            "SELECT nomorJC FROM " . DBACC . ".pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1 FOR UPDATE"
        )->row();

        if (empty($cabang)) {
            throw new Exception('Branch data not found for generating JV number!');
        }

        $nomor_urut = (int) $cabang->nomorJC + 1;
        $nomor_jv   = '101-AJV' . date('ym') . $nomor_urut;

        $this->db->query(
            "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'"
        );

        return $nomor_jv;
    }

    // ---------------------------------------------------------------
    // REJECT — tolak permanen (status → 3)
    // ---------------------------------------------------------------

    public function reject_mutation($id, $rejected_by, $rejected_date, $reason)
    {
        $this->db->where('id', $id);
        $this->db->where('status', 1);
        $this->db->update('material_mutations', [
            'status'        => 3,
            'reject_reason' => $reason,
            'approved_by'   => $rejected_by,
            'approved_date' => $rejected_date,
            'update_by'     => $rejected_by,
            'update_date'   => $rejected_date,
        ]);

        return $this->db->affected_rows() > 0;
    }

    // ---------------------------------------------------------------
    // REVISI — kembalikan untuk perbaikan (status → 6)
    // ---------------------------------------------------------------

    public function revision_mutation($id, $revised_by, $revised_date, $reason)
    {
        $this->db->where('id', $id);
        $this->db->where('status', 1);
        $this->db->update('material_mutations', [
            'status'        => 6,
            'reject_reason' => $reason,
            'update_by'     => $revised_by,
            'update_date'   => $revised_date,
        ]);

        return $this->db->affected_rows() > 0;
    }
}
