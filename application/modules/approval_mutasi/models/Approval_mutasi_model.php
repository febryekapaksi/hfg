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
            mm.id, mm.mutation_number, mm.mutation_date, mm.no_berita_acara,
            mm.nm_gudang_from, mm.nm_gudang_to, mm.description, mm.status,
            mm.reject_reason, mm.approved_by, mm.approved_date, mm.create_by, mm.create_date
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
            mm.id, mm.mutation_number, mm.mutation_date, mm.no_berita_acara,
            mm.file_name_original, mm.file_name_hash,
            mm.id_gudang_from, mm.kd_gudang_from, mm.nm_gudang_from,
            mm.id_gudang_to, mm.kd_gudang_to, mm.nm_gudang_to,
            mm.description, mm.status, mm.reject_reason,
            mm.approved_by, mm.approved_date, mm.create_by, mm.create_date,
            mm.update_by, mm.update_date
        ');
        $this->db->from('material_mutations mm');
        $this->db->where('mm.id', $id);
        $this->db->where('mm.is_delete', 0);
        $header = $this->db->get()->row_array();

        if (!$header) return null;

        $details = $this->db->select('*')
            ->from('material_mutation_details')
            ->where('id_material_mutation', $id)
            ->get()->result_array();

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
            return false;
        }

        $mutation = $this->get_detail($id);

        $now   = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $id_gudang_from = $mutation['id_gudang_from'];
        $kd_gudang_from = $mutation['kd_gudang_from'];
        $id_gudang_to   = $mutation['id_gudang_to'];
        $kd_gudang_to   = $mutation['kd_gudang_to'];

        $summary_map = [];

        foreach ($mutation['details'] as $detail) {
            $id_material = $detail['code_lv4'];
            $nm_material = $detail['nm_material'];

            foreach ($detail['coils'] as $coil) {
                // Ambil data terkini dari warehouse_stock_coil
                $live_coil = $this->db->query("
                    SELECT * FROM warehouse_stock_coil
                    WHERE id = ? LIMIT 1 FOR UPDATE
                ", [$coil['id_warehouse_stock_coil']])->row();

                $net_weight    = (float) ($live_coil ? $live_coil->net_weight : ($coil['net_weight'] ?? 0));
                $gross_weight  = (float) ($live_coil ? $live_coil->gross_weight : ($coil['gross_weight'] ?? 0));
                $length        = (float) ($live_coil ? $live_coil->length : ($coil['length'] ?? 0));
                $harga_beli    = (float) ($live_coil ? $live_coil->harga_beli : ($coil['harga_beli'] ?? 0));
                $kode_internal = $live_coil ? $live_coil->kode_internal : ($coil['kode_internal'] ?? '');
                $no_coil       = $live_coil ? $live_coil->no_coil : ($coil['no_coil'] ?? '');

                // ═══════════════════════════════════════════════════
                // A. GUDANG ASAL — kurangi stock
                // ═══════════════════════════════════════════════════

                $stock_from = $this->db->query("
                    SELECT * FROM warehouse_stock
                    WHERE code_lv4 = ? AND id_gudang = ?
                    LIMIT 1 FOR UPDATE
                ", [$id_material, $id_gudang_from])->row();

                // Default values jika stock_from tidak ditemukan
                $costbook_from = $harga_beli;

                if ($stock_from) {
                    $qty_awal_from   = (float) $stock_from->qty_stock;
                    $qty_free_from   = (float) $stock_from->qty_free;
                    $saldo_awal_from = (float) $stock_from->total_nilai;
                    $harga_lama_from = (float) $stock_from->harga_beli;
                    $outgoing_from   = (float) $stock_from->outgoing;

                    $qty_akhir_from   = $qty_awal_from - $net_weight;
                    $nilai_keluar     = $harga_lama_from * $net_weight;
                    $saldo_akhir_from = $saldo_awal_from - $nilai_keluar;
                    $costbook_from    = $qty_akhir_from > 0
                        ? ($saldo_akhir_from / $qty_akhir_from)
                        : $harga_lama_from;

                    // Update warehouse_stock gudang asal
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
                        $id_material,
                        $nm_material,
                        $id_gudang_from,
                        $kd_gudang_from,
                        $qty_akhir_from,
                        (float) $stock_from->qty_booking,
                        $qty_free_from - $net_weight,
                        $costbook_from,
                        $saldo_akhir_from,
                        $now,
                        $approved_by
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
                        'no_ipp'          => $mutation['mutation_number'],
                        'jumlah_mat'      => $net_weight,
                        'ket'             => 'Mutasi Keluar ' . $mutation['mutation_number']
                            . ' (Coil: ' . $no_coil . ') ke ' . $mutation['nm_gudang_to'],
                        'harga_beli'      => $harga_lama_from,
                        'total_harga'     => $nilai_keluar,
                        'saldo_awal'      => $saldo_awal_from,
                        'saldo_akhir'     => $saldo_akhir_from,
                        'harga_baru'      => $costbook_from,
                        'harga_lama'      => $harga_lama_from,
                        'update_by'       => $approved_by,
                        'update_date'     => $now,
                    ]);

                    // kartu_stok (gudang asal - OUT)
                    $this->db->insert('kartu_stok', [
                        'no_transaksi'     => $mutation['mutation_number'],
                        'id_gudang'        => $id_gudang_from,
                        'transaksi'        => 'Mutasi Keluar',
                        'tgl_transaksi'    => $now,
                        'code_lv4'         => $id_material,
                        'code_material'    => $id_material,
                        'nm_material'      => $nm_material,
                        'qty'              => $qty_awal_from,
                        'qty_book'         => (float) $stock_from->qty_booking,
                        'qty_free'         => $qty_free_from,
                        'qty_akhir'        => $qty_akhir_from,
                        'qty_transaksi'    => $net_weight,
                        'qty_book_akhir'   => (float) $stock_from->qty_booking,
                        'qty_free_akhir'   => $qty_free_from - $net_weight,
                        'harga_stok'       => $costbook_from,
                        'status_transaksi' => 'out',
                        'created_by'       => $approved_by,
                        'created_on'       => $now,
                    ]);
                }

                // ═══════════════════════════════════════════════════
                // B. GUDANG TUJUAN — tambah stock
                // ═══════════════════════════════════════════════════

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

                // Harga masuk = costbook gudang asal (moving average saat ini)
                $price_in        = $costbook_from;
                $nilai_masuk     = $price_in * $net_weight;
                $qty_akhir_to    = $qty_awal_to + $net_weight;
                $saldo_akhir_to  = $saldo_awal_to + $nilai_masuk;
                $costbook_to     = $qty_akhir_to > 0
                    ? ($saldo_akhir_to / $qty_akhir_to)
                    : $price_in;

                if (empty($stock_to)) {
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
                    $id_material,
                    $nm_material,
                    $id_gudang_to,
                    $kd_gudang_to,
                    $qty_akhir_to,
                    $qty_book_to,
                    $qty_free_to + $net_weight,
                    $costbook_to,
                    $saldo_akhir_to,
                    $now,
                    $approved_by
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
                    'no_ipp'          => $mutation['mutation_number'],
                    'jumlah_mat'      => $net_weight,
                    'ket'             => 'Mutasi Masuk ' . $mutation['mutation_number']
                        . ' (Coil: ' . $no_coil . ') dari ' . $mutation['nm_gudang_from'],
                    'harga_beli'      => $price_in,
                    'total_harga'     => $nilai_masuk,
                    'saldo_awal'      => $saldo_awal_to,
                    'saldo_akhir'     => $saldo_akhir_to,
                    'harga_baru'      => $costbook_to,
                    'harga_lama'      => $harga_lama_to,
                    'update_by'       => $approved_by,
                    'update_date'     => $now,
                ]);

                // kartu_stok (gudang tujuan - IN)
                $this->db->insert('kartu_stok', [
                    'no_transaksi'     => $mutation['mutation_number'],
                    'id_gudang'        => $id_gudang_to,
                    'transaksi'        => 'Mutasi Masuk',
                    'tgl_transaksi'    => $now,
                    'code_lv4'         => $id_material,
                    'code_material'    => $id_material,
                    'nm_material'      => $nm_material,
                    'qty'              => $qty_awal_to,
                    'qty_book'         => $qty_book_to,
                    'qty_free'         => $qty_free_to,
                    'qty_akhir'        => $qty_akhir_to,
                    'qty_transaksi'    => $net_weight,
                    'qty_book_akhir'   => $qty_book_to,
                    'qty_free_akhir'   => $qty_free_to + $net_weight,
                    'harga_stok'       => $costbook_to,
                    'status_transaksi' => 'in',
                    'created_by'       => $approved_by,
                    'created_on'       => $now,
                ]);

                // ═══════════════════════════════════════════════════
                // C. UPDATE warehouse_stock_coil — pindah gudang
                // ═══════════════════════════════════════════════════

                $this->db->where('id', $coil['id_warehouse_stock_coil']);
                $this->db->update('warehouse_stock_coil', [
                    'id_gudang'  => $id_gudang_to,
                    'kd_gudang'  => $kd_gudang_to,
                    'harga_beli' => $price_in,
                ]);

                // ═══════════════════════════════════════════════════
                // D. warehouse_coil_per_day (OUT dari asal, IN ke tujuan)
                // ═══════════════════════════════════════════════════

                $this->_upsert_coil_per_day(
                    $id_material,
                    $nm_material,
                    $id_gudang_from,
                    $kd_gudang_from,
                    $no_coil,
                    $kode_internal,
                    $gross_weight,
                    $net_weight,
                    $length,
                    'OUT',
                    $now,
                    $approved_by
                );

                $this->_upsert_coil_per_day(
                    $id_material,
                    $nm_material,
                    $id_gudang_to,
                    $kd_gudang_to,
                    $no_coil,
                    $kode_internal,
                    $gross_weight,
                    $net_weight,
                    $length,
                    'IN',
                    $now,
                    $approved_by
                );

                // ═══════════════════════════════════════════════════
                // E. warehouse_stock_transaction_detail (KEDUA GUDANG)
                // ═══════════════════════════════════════════════════

                // E1. Detail gudang ASAL (OUT)
                if ($stock_from) {
                    $this->db->insert('warehouse_stock_transaction_detail', [
                        'kode_trans'     => $mutation['mutation_number'],
                        'id_material'    => $id_material,
                        'nm_material'    => $nm_material,
                        'id_gudang'      => $id_gudang_from,
                        'kd_gudang'      => $kd_gudang_from,
                        'no_coil'        => $no_coil,
                        'kode_internal'  => $kode_internal,
                        'gross_weight'   => $gross_weight,
                        'net_weight'     => $net_weight,
                        'length'         => $length,
                        'price_per_coil' => $nilai_keluar,
                        'cost_book'      => $costbook_from,
                        'status_qc'      => 'OUT',
                        'created_at'     => $now,
                    ]);
                }

                // E2. Detail gudang TUJUAN (IN)
                $this->db->insert('warehouse_stock_transaction_detail', [
                    'kode_trans'     => $mutation['mutation_number'],
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
                    'status_qc'      => 'IN',
                    'created_at'     => $now,
                ]);

                // ═══════════════════════════════════════════════════
                // F. Aggregate warehouse_stock_transaction_summary (KEDUA GUDANG)
                // ═══════════════════════════════════════════════════

                // F1. Summary gudang ASAL (OUT — qty berkurang)
                if ($stock_from) {
                    $summary_key_from = $id_material . '_' . $id_gudang_from . '_OUT';
                    if (!isset($summary_map[$summary_key_from])) {
                        $summary_map[$summary_key_from] = [
                            'kode_trans'    => $mutation['mutation_number'],
                            'id_material'   => $id_material,
                            'nm_material'   => $nm_material,
                            'id_gudang'     => $id_gudang_from,
                            'kd_gudang'     => $kd_gudang_from,
                            'tanggal'       => $today,
                            'jumlah_coil'   => 0,
                            'qty_awal'      => $qty_awal_from,
                            'qty_transaksi' => 0,
                            'qty_akhir'     => 0,
                            'costbook'      => 0,
                            'total_harga'   => 0,
                            'saldo_awal'    => $saldo_awal_from,
                            'saldo_akhir'   => 0,
                            'harga_lama'    => $harga_lama_from,
                            'created_by'    => $approved_by,
                            'created_at'    => $now,
                        ];
                    }
                    $summary_map[$summary_key_from]['jumlah_coil']++;
                    $summary_map[$summary_key_from]['qty_transaksi'] += $net_weight;
                    $summary_map[$summary_key_from]['total_harga']   += $nilai_keluar;
                    $summary_map[$summary_key_from]['qty_akhir']      = $qty_akhir_from;
                    $summary_map[$summary_key_from]['costbook']       = $costbook_from;
                    $summary_map[$summary_key_from]['saldo_akhir']    = $saldo_akhir_from;
                }

                // F2. Summary gudang TUJUAN (IN — qty bertambah)
                $summary_key_to = $id_material . '_' . $id_gudang_to . '_IN';
                if (!isset($summary_map[$summary_key_to])) {
                    $summary_map[$summary_key_to] = [
                        'kode_trans'    => $mutation['mutation_number'],
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
                $summary_map[$summary_key_to]['jumlah_coil']++;
                $summary_map[$summary_key_to]['qty_transaksi'] += $net_weight;
                $summary_map[$summary_key_to]['total_harga']   += $nilai_masuk;
                $summary_map[$summary_key_to]['qty_akhir']      = $qty_akhir_to;
                $summary_map[$summary_key_to]['costbook']       = $costbook_to;
                $summary_map[$summary_key_to]['saldo_akhir']    = $saldo_akhir_to;
            }
        }

        // Insert warehouse_stock_transaction_summary (aggregated per material)
        foreach ($summary_map as $s) {
            $this->db->insert('warehouse_stock_transaction_summary', $s);
        }

        // ═══════════════════════════════════════════════════
        // G. UPDATE warehouse_pack — pindahkan pack ke gudang tujuan
        // ═══════════════════════════════════════════════════
        $pack_ids_moved = [];
        foreach ($mutation['details'] as $detail) {
            if (!empty($detail['id_warehouse_pack']) && !in_array($detail['id_warehouse_pack'], $pack_ids_moved)) {
                $this->db->update('warehouse_pack', [
                    'id_gudang' => $id_gudang_to,
                    'kd_gudang' => $kd_gudang_to,
                ], ['id' => $detail['id_warehouse_pack']]);
                $pack_ids_moved[] = $detail['id_warehouse_pack'];
            }
        }

        return true;
    }

    // ---------------------------------------------------------------
    // HELPER: Upsert warehouse_stock_per_day
    // ---------------------------------------------------------------

    private function _upsert_stock_per_day(
        $id_material,
        $nm_material,
        $id_gudang,
        $kd_gudang,
        $qty_stock,
        $qty_booking,
        $qty_free,
        $harga_beli,
        $total_nilai,
        $now,
        $user
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
        $id_material,
        $nm_material,
        $id_gudang,
        $kd_gudang,
        $no_coil,
        $kode_internal,
        $gross_weight,
        $net_weight,
        $length,
        $status,
        $now,
        $user
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
    // GENERATE JURNAL MUTASI → GL INTERFACE (via Template)
    // ---------------------------------------------------------------

    public function _generate_jurnal_mutasi($mutation)
    {
        // Hitung total nilai mutasi berdasarkan costbook final setelah perpindahan stock
        $total_nilai      = 0;
        $total_net_weight = 0;

        foreach ($mutation['details'] as $detail) {
            foreach ($detail['coils'] as $coil) {
                $live_coil = $this->db->query("
                    SELECT harga_beli, net_weight FROM warehouse_stock_coil WHERE id = ? LIMIT 1
                ", [$coil['id_warehouse_stock_coil']])->row();

                if ($live_coil) {
                    $nw = (float) $live_coil->net_weight;
                    $total_nilai += (float) $live_coil->harga_beli * $nw;
                    $total_net_weight += $nw;
                } else {
                    $nw = (float) ($coil['net_weight'] ?? 0);
                    $total_nilai += (float) ($coil['harga_beli'] ?? 0) * $nw;
                    $total_net_weight += $nw;
                }
            }
        }

        if ($total_nilai <= 0) return;

        // Update total_nilai_transaksi & total_net_weight_transaksi di header
        $this->db->update('material_mutations', [
            'total_nilai_transaksi'      => (int) round($total_nilai),
            'total_net_weight_transaksi' => round($total_net_weight, 2),
        ], ['id' => $mutation['id']]);

        // Tentukan kode jurnal berdasarkan gudang sumber
        // PRO (Produksi) → JV010, SLI (Slitting) → JV011
        $kd_gudang_from = $mutation['kd_gudang_from'];

        if ($kd_gudang_from === 'PRO') {
            $kode_jurnal_fallback = 'JV010';
            $action_mapping       = 'approve_mutasi_pro';
        } else {
            $kode_jurnal_fallback = 'JV011';
            $action_mapping       = 'approve_mutasi_sli';
        }

        // Cek mapping dari tabel ms_jurnal_mapping
        $mapping = $this->db->get_where('ms_jurnal_mapping', [
            'menu'   => 'Mutasi',
            'action' => $action_mapping
        ])->row();

        $kode_jurnal = $mapping ? $mapping->kode_master_jurnal : $kode_jurnal_fallback;

        // Siapkan data_source untuk template jurnal
        $data_source = [
            'tanggal'                    => date('Y-m-d'),
            'total_nilai_transaksi'      => (int) round($total_nilai),
            'total_net_weight_transaksi' => round($total_net_weight, 2),
            'mutation_number'            => $mutation['mutation_number'],
            'no_doc'                     => $mutation['mutation_number'],
            'no_request'                 => $mutation['mutation_number'],
            'nm_gudang_from'             => $mutation['nm_gudang_from'],
            'nm_gudang_to'               => $mutation['nm_gudang_to'],
            'id_gudang_from'             => $mutation['id_gudang_from'],
            'id_gudang_to'               => $mutation['id_gudang_to'],
            'kd_gudang_from'             => $mutation['kd_gudang_from'],
            'kd_gudang_to'               => $mutation['kd_gudang_to'],
            'description'                => $mutation['description'] ?? '',
        ];

        // Generate jurnal via template (persis seperti close_ros)
        $this->load->model('gl_interface/Gl_interface_model');
        $result = $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $data_source);

        if (!$result) {
            throw new Exception("Template jurnal '$kode_jurnal' gagal diproses atau tidak ditemukan.");
        }
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
