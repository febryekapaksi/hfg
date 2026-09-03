<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Finalize_incoming extends Admin_Controller
{
    protected $viewPermission   = 'Finalize_Incoming.View';
    protected $addPermission    = 'Finalize_Incoming.Add';
    protected $managePermission = 'Finalize_Incoming.Manage';
    protected $deletePermission = 'Finalize_Incoming.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Finalize_Incoming/Finalize_incoming_model', 'jurnal_nomor/Jurnal_model', 'all/All_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Finalize Incoming');
        $this->template->render('index');
    }

    // DATA TABLE — Tab DRAFT (submitted)
    public function data_side_draft()
    {
        $ENABLE_MANAGE = has_permission('Finalize_Incoming.Manage');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [1 => 'r.id', 2 => 'r.no_po', 3 => 's.nama', 4 => 'r.submitted_date'];
        $order_by = $col_map[$order_col] ?? 'r.submitted_date';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%'
                OR EXISTS (
                    SELECT 1 FROM tr_purchase_order p
                    WHERE FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
                      AND p.no_surat LIKE '%{$s}%'
                ))";
        }

        $base_sql = " FROM tr_ros_header r
                  LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
                  WHERE r.status = '1' AND r.status_incoming = 'submitted'
                  {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
        SELECT r.*, s.nama AS nm_supplier,
               GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list,
               u.nm_lengkap AS submitted_by_name
        FROM tr_ros_header r
        LEFT JOIN new_supplier s        ON r.id_supplier = s.kode_supplier
        LEFT JOIN tr_purchase_order p   ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
        LEFT JOIN users u               ON u.id_user = r.submitted_by
        WHERE r.status = '1' AND r.status_incoming = 'submitted'
        {$where_search}
        GROUP BY r.id
        ORDER BY {$order_by} {$order_dir}
        LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $no_po_display = !empty($row['no_surat_list']) ? $row['no_surat_list'] : $row['no_po'];

            // ── TOMBOL UTAMA (FINALIZE & REVISI) ──
            $btn_finalize = '';
            if ($ENABLE_MANAGE) {
                $btn_finalize = '<button class="btn btn-sm btn-success btn-finalize" data-id="' . $row['id'] . '" title="Finalize & Close" style="width:100px">
                <i class="fa fa-check-circle"></i> Finalize
            </button>';
            }

            $btn_revisi = '<button class="btn btn-sm btn-danger btn-revisi" data-id="' . $row['id'] . '" title="Revise / Return" style="width:100px">
            <i class="fa fa-undo"></i> Revise
        </button>';

            // ── LOGIKA AMBIL ID COIL (UNTUK PRINT QR) ──
            $coil_ids = $this->db->query("
            SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m ON m.id = c.id_ros_material
            WHERE m.id_ros = ?
        ", [$row['id']])->row();
            $coil_ids_str = $coil_ids ? $coil_ids->ids : '';

            // Tombol Print QR
            $btn_print = $coil_ids_str
                ? '<a href="' . base_url('incoming/print_qr/' . $row['id']) . '" target="_blank" 
                  class="btn btn-sm btn-info" style="width:100px" title="Print QR">
                  <i class="fa fa-print"></i> Print QR
               </a>'
                : '';

            // ── TOMBOL PRINT PL ──
            $btn_print_pl = '<a href="' . base_url('incoming/print_pl_by_gudang/' . $row['id']) . '" target="_blank" 
                            class="btn btn-sm btn-secondary" style="width:100px" title="Print Packing List">
                            <i class="fa fa-file-alt"></i> Print PL
                         </a>';

            // ── GABUNGKAN TOMBOL AKSI (Berjajar ke Bawah) ──
            $aksi = "<div class='d-flex flex-column align-items-center gap-1'>
                    {$btn_finalize} 
                    {$btn_revisi}
                    {$btn_print_pl} 
                    {$btn_print}
                 </div>";

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-center'>" . ($row['submitted_date'] ? date('d/m/Y H:i', strtotime($row['submitted_date'])) : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars($row['submitted_by_name'] ?? '-') . "</div>",
                "<div class='text-center'><span class='badge rounded-pill bg-info text-dark'>Submitted</span></div>",
                "<div class='text-center'>{$aksi}</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // DATA TABLE — Tab CLOSE
    public function data_side_close()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 5;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [
            1 => 'inc.kode_trans',
            2 => 'inc.no_ros',
            3 => 'inc.no_po',
            4 => 'inc.id_supplier',
            5 => 'inc.created_at',
            6 => 'inc.kode_trans',
        ];
        $order_by = $col_map[$order_col] ?? 'inc.created_at';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " WHERE (inc.kode_trans LIKE '%{$s}%' OR inc.no_ros LIKE '%{$s}%' OR inc.no_po LIKE '%{$s}%' OR inc.no_surat LIKE '%{$s}%' OR inc.nm_supplier LIKE '%{$s}%')";
        }

        // 1. Query Hitung Total Data
        $total_q = $this->db->query("
        SELECT COUNT(*) as cnt 
        FROM tr_incoming_header inc 
        {$where_search}
        ")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        // 2. Query Utama Ambil Data
        $sql = "
        SELECT 
            inc.kode_trans    AS kode_incoming,
            inc.no_ros        AS id_ros,
            inc.no_surat         AS no_po_display, 
            inc.nm_supplier,
            inc.created_at    AS tgl_finalize
        FROM tr_incoming_header inc
        {$where_search}
        ORDER BY {$order_by} {$order_dir}
        LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $sts_badge = '<span class="badge rounded-pill bg-success">Closed</span>';

            // ── TOMBOL DETAIL ──
            $btn_view = '<a href="' . base_url('incoming/view/' . $row['kode_incoming']) . '" 
                        class="btn btn-sm btn-primary" style="width:120px" title="View Detail">
                        <i class="fa fa-eye"></i> Detail
                     </a>';

            // ── LOGIKA AMBIL ID COIL (UNTUK PRINT QR) ──
            $coil_ids = $this->db->query("
                SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
                FROM tr_ros_material_coil c
                JOIN tr_ros_material m ON m.id = c.id_ros_material
                WHERE m.id_ros = ?
            ", [$row['id_ros']])->row();
            $coil_ids_str = $coil_ids ? $coil_ids->ids : '';

            // Tombol Print QR (Hanya muncul jika ada coil-nya)
            $btn_print = $coil_ids_str
                ? '<a href="' . base_url('incoming/print_qr/' . $row['id_ros']) . '" target="_blank" 
                  class="btn btn-sm btn-dark" style="width:120px" title="Print QR">
                  <i class="fa fa-qrcode"></i> Print QR
               </a>'
                : '';

            // ── TOMBOL PRINT PL ──
            $btn_print_pl = '<a href="' . base_url('incoming/print_pl_by_gudang/' . $row['id_ros']) . '" target="_blank" 
                            class="btn btn-sm btn-secondary" style="width:120px" title="Print Packing List">
                            <i class="fa fa-print"></i> Packing List
                         </a>';

            $aksi = "<div class='d-flex flex-column align-items-center gap-1'>
                    {$btn_view} 
                    {$btn_print_pl} 
                    {$btn_print}
                 </div>";

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['kode_incoming'],
                $row['id_ros'],
                $row['no_po_display'],
                $row['nm_supplier'] ?? '-',
                "<div class='text-center'>" . ($row['tgl_finalize'] ? date('d/m/Y', strtotime($row['tgl_finalize'])) : '-') . "</div>",
                $row['kode_incoming'],
                "<div class='text-center'>{$sts_badge}</div>",
                "<div class='text-center'>{$aksi}</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // AJAX — Preview data coil untuk modal finalize
    public function get_draft_preview()
    {
        $no_ros = $this->input->post('no_ros');

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'ROS number is required!']);
            return;
        }

        $header = $this->db->query("
            SELECT h.id, h.no_po, h.no_surat, h.incoming_date, s.nama AS nm_supplier
            FROM tr_ros_header h
            LEFT JOIN new_supplier s ON s.kode_supplier = h.id_supplier
            WHERE h.id = ? LIMIT 1
        ", [$no_ros])->row_array();

        if (empty($header)) {
            echo json_encode(['status' => 0, 'pesan' => 'ROS data not found!']);
            return;
        }

        // Query coils dengan info pack
        $rows = $this->db->query("
            SELECT c.no_coil, c.berat_kotor, c.berat_bersih, c.panjang,
                c.status_qc, c.kd_gudang_ke, c.is_baby_coil, c.qty_roll, c.id_ros_pack,
                m.nm_erp AS nm_material, m.nm_alias, m.id_barang AS id_material,
                d.qty AS qty_po,
                p.pack_code, p.pack_no
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m  ON m.id = c.id_ros_material
            LEFT JOIN dt_trans_po d ON d.id = m.id_po_detail
            LEFT JOIN tr_ros_pack p ON p.id = c.id_ros_pack
            WHERE m.id_ros = ?
            ORDER BY p.pack_no ASC, m.id_barang ASC, c.no_coil ASC
        ", [$no_ros])->result_array();

        // Group by pack
        $packs = [];
        foreach ($rows as $row) {
            $pack_id = $row['id_ros_pack'] ?: 0;

            if (!isset($packs[$pack_id])) {
                $packs[$pack_id] = [
                    'pack_code'    => $row['pack_code'] ?: '-',
                    'pack_no'      => $row['pack_no'] ?: 0,
                    'kd_gudang_ke' => $row['kd_gudang_ke'],
                    'total_nw'     => 0,
                    'total_gw'     => 0,
                    'coil_count'   => 0,
                    'materials'    => [],
                    'coils'        => [],
                ];
            }

            // Skip mother coil from display count
            $is_mother_with_baby = ((int) $row['is_baby_coil'] === 0 && (int) $row['qty_roll'] > 1);
            if (!$is_mother_with_baby) {
                $packs[$pack_id]['total_nw'] += (float) $row['berat_bersih'];
                $packs[$pack_id]['total_gw'] += (float) $row['berat_kotor'];
                $packs[$pack_id]['coil_count']++;
                $packs[$pack_id]['coils'][] = $row;
            }

            // Collect unique materials
            $mat_key = $row['id_material'];
            if (!isset($packs[$pack_id]['materials'][$mat_key])) {
                $packs[$pack_id]['materials'][$mat_key] = [
                    'nm_alias'    => $row['nm_alias'],
                    'nm_material' => $row['nm_material'],
                ];
            }
        }

        echo json_encode([
            'status' => 1,
            'header' => $header,
            'packs'  => array_values($packs),
        ]);
    }


    // REVISI — Kembalikan ke incoming (status saved)
    public function revisi()
    {
        $no_ros = $this->input->post('no_ros');
        $note   = $this->input->post('revision_note') ?: '';

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'ROS number is required!']);
            return;
        }

        $ros_header = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '1',
            'status_incoming' => 'submitted',
        ])->row();

        if (empty($ros_header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data not found or already processed!']);
            return;
        }

        $this->db->update('tr_ros_header', [
            'status_incoming' => 'saved',
            'revised_by'      => $this->auth->user_id(),
            'revised_date'    => date('Y-m-d H:i:s'),
            'revision_note'   => $note,
        ], ['id' => $no_ros]);

        echo json_encode(['status' => 1, 'pesan' => 'Data has been returned to Incoming for revision.']);
    }

    // FINALIZE — Proses stok + jurnal + close
    // public function finalize()
    // {
    //     ob_start();
    //     $this->auth->restrict($this->managePermission);
    //     $no_ros  = $this->input->post('no_ros');
    //     $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
    //     $qc_json = $this->input->post('qc_data') ?: '[]';

    //     if (empty($no_ros)) {
    //         echo json_encode(['status' => 0, 'pesan' => 'ROS number is required!']);
    //         return;
    //     }

    //     // Decode QC data dari modal finalize
    //     $qc_data = json_decode($qc_json, true);
    //     $qc_map  = [];
    //     if (!empty($qc_data) && is_array($qc_data)) {
    //         foreach ($qc_data as $qc) {
    //             $qc_map[$qc['no_coil']] = $qc['status_qc'] ?? 'OK';
    //         }
    //     }

    //     $ros_header = $this->db->get_where('tr_ros_header', [
    //         'id'              => $no_ros,
    //         'status'          => '1',
    //         'status_incoming' => 'submitted',
    //     ])->row();

    //     if (empty($ros_header)) {
    //         echo json_encode(['status' => 0, 'pesan' => 'Draft data not found!']);
    //         return;
    //     }

    //     // Ambil semua coil
    //     $coils = $this->db->query("
    //     SELECT
    //         c.id                    AS id_ros_material_coil,
    //         c.no_coil,
    //         c.kode_internal,
    //         c.berat_kotor,
    //         c.berat_bersih,
    //         c.panjang,
    //         c.bpm,
    //         c.id_gudang_ke,
    //         c.kd_gudang_ke,
    //         c.status_qc,
    //         c.price_per_coil,
    //         c.is_baby_coil,
    //         c.qty_roll,
    //         c.parent_coil_id,
    //         c.id_ros_pack,
    //         m.id                    AS id_ros_material,
    //         m.id_barang             AS id_material,
    //         m.nm_erp                AS nm_material,
    //         m.nm_alias              AS trade_name,
    //         m.cost_book,
    //         m.total_nilai_inventory,
    //         m.id_po_detail,
    //         h.no_po,
    //         h.id_supplier,
    //         h.kurs_pib,
    //         p.pack_code
    //     FROM tr_ros_material_coil c
    //     JOIN tr_ros_material m ON m.id = c.id_ros_material
    //     JOIN tr_ros_header h   ON h.id = m.id_ros
    //     LEFT JOIN tr_ros_pack p ON p.id = c.id_ros_pack
    //     WHERE m.id_ros = ?
    //     ORDER BY m.id_barang, c.no_coil ASC
    //     ", [$no_ros])->result_array();

    //     if (empty($coils)) {
    //         echo json_encode(['status' => 0, 'pesan' => 'No coil data available!']);
    //         return;
    //     }

    //     foreach ($coils as $c) {
    //         if (empty($c['id_gudang_ke'])) {
    //             echo json_encode(['status' => 0, 'pesan' => 'Some coils have not been assigned a warehouse!']);
    //             return;
    //         }
    //     }

    //     // Update status_qc dari modal finalize (hanya penanda)
    //     foreach ($coils as &$c) {
    //         if (isset($qc_map[$c['no_coil']])) {
    //             $c['status_qc'] = $qc_map[$c['no_coil']];
    //             $this->db->update('tr_ros_material_coil', [
    //                 'status_qc' => $c['status_qc'],
    //             ], ['id' => (int) $c['id_ros_material_coil']]);
    //         }
    //     }
    //     unset($c);

    //     // Validasi template jurnal — harus sudah dikonfigurasi sebelum proses
    //     $po_row   = $this->db->get_where('tr_purchase_order', ['no_po' => $ros_header->no_po])->row();
    //     $is_lokal = ($po_row && strtolower($po_row->loi) === 'lokal');
    //     $action_key = $is_lokal ? 'finalize_lokal' : 'finalize_import';
    //     $tipe_label = $is_lokal ? 'Lokal' : 'Import';

    //     $jurnal_mapping = $this->db->get_where('ms_jurnal_mapping', [
    //         'menu'   => 'finalize_incoming',
    //         'action' => $action_key
    //     ])->row();

    //     if (!$jurnal_mapping) {
    //         ob_clean();
    //         header('Content-Type: application/json');
    //         echo json_encode([
    //             'status' => 0,
    //             'pesan'  => "Template jurnal untuk Incoming {$tipe_label} belum dibuat. Silakan konfigurasi di Master Jurnal Mapping (menu: finalize_incoming, action: {$action_key})."
    //         ]);
    //         exit;
    //     }

    //     // ── TAMBAHAN: Validasi header & detail template jurnal juga sudah lengkap ──
    //     // ms_jurnal_mapping hanya menyimpan pointer (kode_master_jurnal);
    //     // isi template sesungguhnya ada di master_oto_jurnal_header / master_oto_jurnal_detail
    //     // di database 'accounting'. Kalau belum lengkap, proses harus diblok DI SINI,
    //     // sebelum trans_begin(), supaya stok/warehouse tidak ikut ter-commit
    //     // sementara jurnal tidak bisa dibuat.
    //     $db_acc = $this->load->database('accounting', TRUE);

    //     $tpl_header = $db_acc->get_where('master_oto_jurnal_header', [
    //         'kode_master_jurnal' => $jurnal_mapping->kode_master_jurnal
    //     ])->row();

    //     $tpl_detail_count = $db_acc
    //         ->where('kode_master_jurnal', $jurnal_mapping->kode_master_jurnal)
    //         ->count_all_results('master_oto_jurnal_detail');

    //     if (!$tpl_header || $tpl_detail_count === 0) {
    //         ob_clean();
    //         header('Content-Type: application/json');
    //         echo json_encode([
    //             'status' => 0,
    //             'pesan'  => "Template jurnal '{$jurnal_mapping->kode_master_jurnal}' untuk Incoming {$tipe_label} belum lengkap (header/detail belum dikonfigurasi). Silakan lengkapi di menu Master Jurnal Template sebelum finalize."
    //         ]);
    //         exit;
    //     }

    //     $this->db->trans_begin();

    //     $kode_incoming      = $this->Finalize_incoming_model->generate_id_incoming();
    //     $total_berat        = 0;
    //     $total_nilai_inc    = 0;
    //     $details_to_insert  = [];
    //     $pack_codes_inserted = []; // Track pack_code yang sudah di-insert ke warehouse_pack

    //     foreach ($coils as $c) {
    //         if ((float) $c['berat_bersih'] <= 0) continue;

    //         // Skip mother coil yang punya baby (bukan unit fisik)
    //         $is_mother_with_baby = ((int) $c['is_baby_coil'] === 0 && (int) $c['qty_roll'] > 1);
    //         if ($is_mother_with_baby) continue;

    //         $berat_bersih    = (float) $c['berat_bersih'];
    //         $cost_book       = (float) $c['cost_book'];
    //         $price_per_coil  = (float) $c['price_per_coil'];
    //         $nilai_inventory = (float) $c['price_per_coil'];

    //         // Resolve parent no_coil untuk baby coil
    //         $parent_no_coil = null;
    //         if ((int) $c['is_baby_coil'] === 1 && !empty($c['parent_coil_id'])) {
    //             $parent_row = $this->db->get_where('tr_ros_material_coil', ['id' => (int) $c['parent_coil_id']])->row();
    //             $parent_no_coil = $parent_row ? $parent_row->no_coil : null;
    //         }

    //         $details_to_insert[] = [
    //             'kode_trans'           => $kode_incoming,
    //             'id_ros_material_coil' => $c['id_ros_material_coil'],
    //             'id_ros_material'      => $c['id_ros_material'],
    //             'id_material'          => $c['id_material'],
    //             'nm_material'          => $c['nm_material'],
    //             'trade_name'           => $c['trade_name'],
    //             'no_coil'              => $c['no_coil'],
    //             'parent_no_coil'       => $parent_no_coil,
    //             'kode_internal'        => $c['kode_internal'],
    //             'berat_kotor'          => $c['berat_kotor'],
    //             'berat_bersih'         => $berat_bersih,
    //             'panjang'              => $c['panjang'],
    //             'bpm'                  => $c['bpm'],
    //             'id_gudang_ke'         => $c['id_gudang_ke'],
    //             'kd_gudang_ke'         => $c['kd_gudang_ke'],
    //             'status_qc'            => $c['status_qc'] ?? 'OK',
    //             'price_per_coil'       => $price_per_coil,
    //             'cost_book'            => $cost_book,
    //             'nilai_inventory'      => $nilai_inventory,
    //             'is_baby_coil'         => (int) $c['is_baby_coil'],
    //             'qty_roll'             => (int) $c['qty_roll'],
    //             'parent_coil_id'       => $c['parent_coil_id'],
    //             'pack_code'            => $c['pack_code'],
    //             'id_ros_pack'          => $c['id_ros_pack'],
    //         ];

    //         // Semua coil fisik masuk stok
    //         $total_berat     += $berat_bersih;
    //         $total_nilai_inc += $price_per_coil;

    //         // Insert warehouse_pack (sekali per pack_code unik)
    //         $pack_code = $c['pack_code'];
    //         if (!empty($pack_code) && !isset($pack_codes_inserted[$pack_code])) {
    //             $this->db->insert('warehouse_pack', [
    //                 'pack_code'  => $pack_code,
    //                 'ref_number' => $kode_incoming,
    //                 'no_po'      => $ros_header->no_po,
    //                 'id_gudang'  => (int) $c['id_gudang_ke'],
    //                 'kd_gudang'  => $c['kd_gudang_ke'],
    //                 'status'     => 1,
    //                 'created_by' => $this->auth->user_id(),
    //                 'created_on' => date('Y-m-d H:i:s'),
    //             ]);
    //             $pack_codes_inserted[$pack_code] = $this->db->insert_id();
    //         }

    //         // Update warehouse stock
    //         $this->_update_stock_and_history(
    //             $c['id_material'],
    //             $c['nm_material'],
    //             $berat_bersih,
    //             $cost_book,
    //             $kode_incoming,
    //             $ros_header->no_po,
    //             $c['no_coil'],
    //             (int) $c['id_gudang_ke'],
    //             $c['kd_gudang_ke'],
    //             $no_ros,
    //             [
    //                 'id_pack'        => isset($pack_codes_inserted[$c['pack_code']]) ? $pack_codes_inserted[$c['pack_code']] : null,
    //                 'qty_roll'       => (int) $c['qty_roll'],
    //                 'is_baby_coil'   => (int) $c['is_baby_coil'],
    //                 'parent_coil_id' => $c['parent_coil_id'],
    //             ]
    //         );

    //         // Update qty_in di dt_trans_po
    //         $this->db->set('qty_in', 'qty_in + ' . $berat_bersih, FALSE);
    //         $this->db->where('id', $c['id_po_detail']);
    //         $this->db->update('dt_trans_po');
    //     }

    //     // ── Susun summary per material per gudang ────────────────────────────────
    //     $summary_map = [];
    //     foreach ($details_to_insert as $d) {
    //         if ((float)$d['berat_bersih'] <= 0) continue;

    //         $key = $d['id_material'] . '_' . $d['id_gudang_ke'];

    //         if (!isset($summary_map[$key])) {
    //             $first_hist = $this->db->query("
    //     SELECT saldo_awal, qty_stock_awal, harga_lama
    //     FROM warehouse_history
    //     WHERE no_ipp = ? AND id_material = ? AND id_gudang = ?
    //     ORDER BY id ASC LIMIT 1
    //     ", [$kode_incoming, $d['id_material'], $d['id_gudang_ke']])->row();

    //             $last_hist = $this->db->query("
    //     SELECT saldo_akhir, qty_stock_akhir, harga_baru
    //     FROM warehouse_history
    //     WHERE no_ipp = ? AND id_material = ? AND id_gudang = ?
    //     ORDER BY id DESC LIMIT 1
    //     ", [$kode_incoming, $d['id_material'], $d['id_gudang_ke']])->row();

    //             $summary_map[$key] = [
    //                 'kode_trans'    => $kode_incoming,
    //                 'id_material'   => $d['id_material'],
    //                 'nm_material'   => $d['nm_material'],
    //                 'id_gudang'     => $d['id_gudang_ke'],
    //                 'kd_gudang'     => $d['kd_gudang_ke'],
    //                 'tanggal'       => $tanggal,
    //                 'jumlah_coil'   => 0,
    //                 'qty_awal'      => $first_hist ? (float)$first_hist->qty_stock_awal : 0,
    //                 'qty_transaksi' => 0,
    //                 'qty_akhir'     => $last_hist  ? (float)$last_hist->qty_stock_akhir : 0,
    //                 'costbook'      => $last_hist  ? (float)$last_hist->harga_baru      : 0,
    //                 'total_harga'   => 0,
    //                 'saldo_awal'    => $first_hist ? (int)$first_hist->saldo_awal       : 0,
    //                 'saldo_akhir'   => $last_hist  ? (int)$last_hist->saldo_akhir       : 0,
    //                 'harga_lama'    => $first_hist ? (float)$first_hist->harga_lama     : 0,
    //                 'created_by'    => $this->auth->user_id(),
    //                 'created_at'    => date('Y-m-d H:i:s'),
    //             ];
    //         }

    //         $summary_map[$key]['jumlah_coil']++;
    //         $summary_map[$key]['qty_transaksi'] += (float)$d['berat_bersih'];
    //         $summary_map[$key]['total_harga']   += (float)$d['price_per_coil'];

    //         // Insert detail snapshot coil
    //         $this->db->insert('warehouse_stock_transaction_detail', [
    //             'kode_trans'     => $kode_incoming,
    //             'id_material'    => $d['id_material'],
    //             'nm_material'    => $d['nm_material'],
    //             'id_gudang'      => $d['id_gudang_ke'],
    //             'kd_gudang'      => $d['kd_gudang_ke'],
    //             'no_coil'        => $d['no_coil'],
    //             'parent_no_coil' => $d['parent_no_coil'] ?? null,
    //             'kode_internal'  => $d['kode_internal'],
    //             'gross_weight'   => $d['berat_kotor'],
    //             'net_weight'     => $d['berat_bersih'],
    //             'length'         => $d['panjang'],
    //             'price_per_coil' => $d['price_per_coil'],
    //             'cost_book'      => $d['cost_book'],
    //             'status_qc'      => 'IN',
    //             'created_at'     => date('Y-m-d H:i:s'),
    //         ]);
    //     }

    //     // Insert summary per material
    //     foreach ($summary_map as $s) {
    //         $this->db->insert('warehouse_stock_transaction_summary', $s);
    //     }

    //     // Insert header incoming
    //     $supplier_row = $this->db->get_where('new_supplier', ['kode_supplier' => $ros_header->id_supplier])->row();
    //     $nm_supplier  = $supplier_row ? $supplier_row->nama : '';

    //     // Hitung value_inc_pro dan value_inc_sli dari price_per_coil per gudang
    //     // Hitung value_inc_pro dan value_inc_sli dari price_per_coil per gudang
    //     $value_inc_pro = 0;
    //     $value_inc_sli = 0;
    //     foreach ($details_to_insert as $d) {
    //         $kd = strtoupper(trim($d['kd_gudang_ke']));
    //         if ($kd === 'SLI') {
    //             $value_inc_sli += (float) $d['price_per_coil'];
    //         } else {
    //             $value_inc_pro += (float) $d['price_per_coil'];
    //         }
    //     }

    //     // Round setelah akumulasi, supaya tidak ada error pembulatan bertumpuk
    //     $value_inc_pro = round($value_inc_pro, 2);
    //     $value_inc_sli = round($value_inc_sli, 2);

    //     $this->db->insert('tr_incoming_header', [
    //         'kode_trans'         => $kode_incoming,
    //         'no_ros'             => $no_ros,
    //         'no_po'              => $ros_header->no_po,
    //         'no_surat'           => $ros_header->no_surat,
    //         'id_supplier'        => $ros_header->id_supplier,
    //         'nm_supplier'        => $nm_supplier,
    //         'tanggal'            => $tanggal,
    //         'total_berat_bersih' => $total_berat,
    //         'total_nilai'        => $total_nilai_inc,
    //         'value_inc_pro'      => $value_inc_pro,
    //         'value_inc_sli'      => $value_inc_sli,

    //         // ── Field GL baru, di-copy dari ROS ──
    //         'gl_advance_purchase_from_ros'      => round((float) ($ros_header->gl_advance_purchase ?? 0), 2),
    //         'gl_persediaan_intransit_from_ros'  => round((float) ($ros_header->gl_persediaan_intransit ?? 0), 2),
    //         'gl_unbill_from_ros'                => round((float) ($ros_header->gl_unbill ?? 0), 2),

    //         'file_dokumen'       => $ros_header->file_hash   ?? '',
    //         'file_original'      => $ros_header->file_original ?? '',
    //         'status'             => 'finalized',
    //         'created_by'         => $this->auth->user_id(),
    //         'created_at'         => date('Y-m-d H:i:s'),
    //         'finalized_by'       => $this->auth->user_id(),
    //         'finalized_at'       => date('Y-m-d H:i:s'),
    //     ]);

    //     // Insert detail incoming
    //     foreach ($details_to_insert as $d) {
    //         $this->db->insert('tr_incoming_detail', $d);
    //     }

    //     // Close ROS
    //     $this->db->update('tr_ros_header', [
    //         'status_incoming' => 'closed',
    //     ], ['id' => $no_ros]);

    //     if ($this->db->trans_status() === FALSE) {
    //         $this->db->trans_rollback();
    //         ob_clean();
    //         echo json_encode(['status' => 0, 'pesan' => 'Failed to process finalize!']);
    //         return;
    //     }
    //     $this->db->trans_commit();

    //     // Generate jurnal
    //     $jurnal_error = null;
    //     if ($total_nilai_inc > 0) {
    //         try {
    //             $this->_generate_jurnal_incoming(
    //                 $kode_incoming,
    //                 $ros_header->no_po,
    //                 $ros_header->no_surat ?? '',
    //                 $ros_header->id_supplier,
    //                 $no_ros,
    //                 $details_to_insert,
    //                 round((float) ($ros_header->gl_advance_purchase ?? 0), 2),
    //                 round((float) ($ros_header->gl_persediaan_intransit ?? 0), 2),
    //                 round((float) ($ros_header->gl_unbill ?? 0), 2)
    //             );
    //         } catch (Exception $e) {
    //             $jurnal_error = $e->getMessage();
    //             log_message('error', 'GL error finalize_incoming ' . $kode_incoming . ': ' . $jurnal_error);
    //         }
    //     }

    //     ob_clean();
    //     header('Content-Type: application/json');

    //     if ($jurnal_error) {
    //         echo json_encode(['status' => 2, 'pesan' => 'Finalize successful, but GL Interface failed: ' . $jurnal_error]);
    //     } else {
    //         echo json_encode(['status' => 1, 'pesan' => 'Finalize successful! Stock and journal have been processed.']);
    //     }
    //     exit;
    // }

    public function _update_stock_and_history(
        $id_material,
        $nm_material,
        $qty_in,
        $price_unit_idr,
        $kode_trans,
        $no_po,
        $no_coil,
        $id_gudang = 1,
        $kd_gudang = 'PRO',
        $no_ros = '',
        $extra = []
    ) {
        // ── 1. Ambil data coil dari ROS ───────────────────────────────────────
        $ros_coil = $this->db->query("
            SELECT
                c.id              AS id_coil,
                c.no_coil,
                c.berat_kotor,
                c.berat_bersih,
                c.panjang,
                c.kode_internal,
                c.status_qc,
                m.id              AS id_ros_material,
                m.nm_erp,
                m.cost_book,
                m.total_nilai_inventory,
                inv.code_lv1,
                inv.code_lv2,
                inv.code_lv3,
                inv.id_unit,
                inv.id_unit_packing,
                inv.trade_name  
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m        ON m.id = c.id_ros_material
            LEFT JOIN new_inventory_4 inv ON inv.code_lv4 = m.id_barang
            WHERE c.no_coil = ? AND m.id_barang = ?
            LIMIT 1
            ", [$no_coil, $id_material])->row();

        $code_lv1        = $ros_coil->code_lv1        ?? '';
        $code_lv2        = $ros_coil->code_lv2        ?? '';
        $code_lv3        = $ros_coil->code_lv3        ?? '';
        $id_unit         = $ros_coil->id_unit         ?? null;
        $id_unit_packing = $ros_coil->id_unit_packing ?? null;
        $berat_kotor     = (float) ($ros_coil->berat_kotor  ?? 0);
        $berat_bersih    = (float) ($ros_coil->berat_bersih ?? $qty_in);
        $panjang         = (float) ($ros_coil->panjang      ?? 0);
        $kode_internal   = $ros_coil->kode_internal ?? '';
        $trade_name      = $ros_coil->trade_name ?? '';

        // ── 2. Ambil ROS header ───────────────────────────────────────────────
        $ros_header = null;
        if (!empty($no_ros)) {
            $ros_header = $this->db->query(
                "SELECT * FROM tr_ros_header WHERE id = ? LIMIT 1",
                [$no_ros]
            )->row();
        }
        $kurs_pib = $ros_header ? (float) $ros_header->kurs_pib : 0;

        // ── 3. Lock & ambil warehouse_stock ───────────────────────────────────
        $get_stock = $this->db->query("
        SELECT * FROM warehouse_stock
        WHERE code_lv4 = ? AND id_gudang = ?
        LIMIT 1 FOR UPDATE
        ", [$id_material, $id_gudang])->row();

        $qty_awal      = $get_stock ? (float) $get_stock->qty_stock   : 0;
        $harga_lama    = $get_stock ? (float) $get_stock->harga_beli  : 0;
        $qty_book_awal = $get_stock ? (float) $get_stock->qty_booking : 0;
        $qty_free_awal = $get_stock ? (float) $get_stock->qty_free    : 0;
        $saldo_awal    = $get_stock ? (float) $get_stock->total_nilai  : 0;  // ubah dari (int) round() ke (float)
        $incoming_lama = $get_stock ? (float) $get_stock->incoming    : 0;

        // ── 4. Hitung moving average ──────────────────────────────────────────
        $nilai_baru        = (float) $qty_in * (float) $price_unit_idr;  // hapus (int) round()
        $qty_akhir         = $qty_awal + $qty_in;
        $total_nilai_akhir = $saldo_awal + $nilai_baru;
        $costbook          = $qty_akhir > 0
            ? ($total_nilai_akhir / $qty_akhir)
            : $price_unit_idr;

        // ── 5. Upsert warehouse_stock ─────────────────────────────────────────
        $now     = date('Y-m-d H:i:s');
        $today   = date('Y-m-d');
        $user_id = $this->auth->user_id();

        if (empty($get_stock)) {
            $this->db->insert('warehouse_stock', [
                'code_lv1'        => $code_lv1,
                'code_lv2'        => $code_lv2,
                'code_lv3'        => $code_lv3,
                'code_lv4'        => $id_material,
                'code_incoming'   => $kode_trans,
                'nm_material'     => $nm_material,
                'trade_name'      => $trade_name,
                'id_gudang'       => $id_gudang,
                'kd_gudang'       => $kd_gudang,
                'id_unit'         => $id_unit,
                'id_unit_packing' => $id_unit_packing,
                'begining'        => 0,
                'incoming'        => $qty_in,
                'outgoing'        => 0,
                'qty_stock'       => $qty_akhir,
                'qty_booking'     => 0,
                'qty_free'        => $qty_akhir,
                'use_qty_free'    => 0,
                'harga_beli'      => $costbook,             // hapus (int) round()
                'total_nilai'     => $total_nilai_akhir,    // hapus (int) round()
                'update_by'       => $user_id,
                'update_date'     => $now,
            ]);
        } else {
            $this->db->update('warehouse_stock', [
                'code_incoming'   => $kode_trans,
                'trade_name'      => $trade_name,
                'incoming'        => $incoming_lama + $qty_in,
                'qty_stock'       => $qty_akhir,
                'qty_free'        => $qty_free_awal + $qty_in,
                'harga_beli'      => $costbook,             // hapus (int) round()
                'total_nilai'     => $total_nilai_akhir,    // hapus (int) round()
                'update_by'       => $user_id,
                'update_date'     => $now,
            ], ['id' => $get_stock->id]);
        }

        // ── 6. Snapshot harian warehouse_stock_per_day ────────────────────────
        $snap = $this->db->query("
        SELECT id FROM warehouse_stock_per_day
        WHERE id_material = ? AND id_gudang = ? AND DATE(hist_date) = ?
        LIMIT 1
        ", [$id_material, $id_gudang, $today])->row();

        $snap_data = [
            'qty_stock'   => $qty_akhir,
            'qty_booking' => $qty_book_awal,
            'qty_free'    => $qty_free_awal + $qty_in,
            'harga_beli'  => $costbook,             // hapus (int) round()
            'total_nilai' => $total_nilai_akhir,    // hapus (int) round()
            'kd_gudang'   => $kd_gudang,
            'hist_date'   => $now,
            'hist_by'     => $user_id,
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

        // ── 6b. Snapshot harian warehouse_coil_per_day ───────────────────────
        $coil_snap = $this->db->query("
        SELECT id FROM warehouse_coil_per_day
        WHERE id_material = ?
        AND id_gudang   = ?
        AND no_coil     = ?
        AND DATE(hist_date) = ?
        LIMIT 1
        ", [$id_material, $id_gudang, $no_coil, $today])->row();

        $coil_snap_data = [
            'nm_material'   => $nm_material,
            'kd_gudang'     => $kd_gudang,
            'kode_internal' => $kode_internal,
            'gross_weight'  => $berat_kotor,
            'net_weight'    => $berat_bersih,
            'length'        => $panjang,
            'harga_beli'    => $costbook,
            'total_nilai'   => $berat_bersih * $costbook,
            'status'        => 'IN',
            'hist_date'     => $now,
            'hist_by'       => $user_id,
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

        // ── 7. Insert warehouse_stock_coil ────────────────────────────────────
        $existing_coil = $this->db->query("
        SELECT id FROM warehouse_stock_coil
        WHERE id_material = ? AND no_coil = ? AND id_gudang = ?
        LIMIT 1
        ", [$id_material, $no_coil, $id_gudang])->row();

        if (empty($existing_coil)) {
            $this->db->insert('warehouse_stock_coil', [
                'id_material'    => $id_material,
                'id_pack'        => isset($extra['id_pack']) ? (int) $extra['id_pack'] : null,
                'no_coil'        => $no_coil,
                'kode_internal'  => $kode_internal,
                'nm_material'    => $nm_material,
                'trade_name'     => $trade_name,
                'gross_weight'   => $berat_kotor,
                'net_weight'     => $berat_bersih,
                'length'         => $panjang,
                'qty_roll'       => isset($extra['qty_roll']) ? (int) $extra['qty_roll'] : 1,
                'id_gudang'      => $id_gudang,
                'kd_gudang'      => $kd_gudang,
                'no_ipp'         => $kode_trans,
                'no_po'          => $no_po,
                'no_ros'         => $no_ros,
                'harga_beli'     => $costbook,
                'total_nilai'    => $berat_bersih * $costbook,
                'status_proses'  => 'in_warehouse',
                'parent_coil_id' => isset($extra['parent_coil_id']) ? $extra['parent_coil_id'] : null,
                'is_baby_coil'   => isset($extra['is_baby_coil']) ? (int) $extra['is_baby_coil'] : 0,
                'created_by'     => $this->auth->user_id(),
                'created_on'     => date('Y-m-d H:i:s'),
            ]);
        } else {
            log_message('warning', "Duplicate coil skipped: {$no_coil} for material {$id_material} at gudang {$id_gudang}");
        }

        // ── 8. warehouse_history ──────────────────────────────────────────────
        $this->db->insert('warehouse_history', [
            'id_material'     => $id_material,
            'nm_material'     => $nm_material,
            'id_gudang'       => $id_gudang,
            'kd_gudang'       => $kd_gudang,
            'id_gudang_dari'  => $id_gudang,
            'kd_gudang_dari'  => $kd_gudang,
            'id_gudang_ke'    => $id_gudang,
            'kd_gudang_ke'    => $kd_gudang,
            'qty_stock_awal'  => $qty_awal,
            'qty_stock_akhir' => $qty_akhir,
            'no_ipp'          => $kode_trans,
            'jumlah_mat'      => $qty_in,
            'ket'             => 'QC Incoming Coil Check'
                . ' (Coil: ' . $no_coil
                . ', PO: '   . $no_po
                . ', Kurs: ' . number_format($kurs_pib, 0, ',', '.') . ')',
            'no_coil'         => $no_coil,
            'harga_beli'      => $price_unit_idr,       // hapus (int) round()
            'total_harga'     => $nilai_baru,            // hapus (int) round()
            'saldo_awal'      => $saldo_awal,            // hapus (int) round()
            'saldo_akhir'     => $total_nilai_akhir,     // hapus (int) round()
            'harga_baru'      => $costbook,              // hapus (int) round()
            'harga_lama'      => $harga_lama,            // hapus (int) round()
            'update_by'       => $user_id,
            'update_date'     => $now,
        ]);

        // ── 9. kartu_stok ─────────────────────────────────────────────────────
        $this->db->insert('kartu_stok', [
            'no_transaksi'     => $kode_trans,
            'id_gudang'        => $id_gudang,
            'transaksi'        => 'Incoming Material',
            'tgl_transaksi'    => $now,
            'code_lv4'         => $id_material,
            'code_material'    => $id_material,
            'nm_material'      => $nm_material,
            'qty'              => $qty_awal,
            'qty_book'         => $qty_book_awal,
            'qty_free'         => $qty_free_awal,
            'qty_akhir'        => $qty_akhir,
            'qty_transaksi'    => $qty_in,
            'qty_book_akhir'   => $qty_book_awal,
            'qty_free_akhir'   => $qty_free_awal + $qty_in,
            'harga_stok'       => $costbook,             // hapus (int) round()
            'status_transaksi' => 'in',
            'created_by'       => $user_id,
            'created_on'       => $now,
        ]);
    }

    private function _allocate_value_by_pack(array $coils_by_material)
    {
        $pack_values = [];

        foreach ($coils_by_material as $id_material => $coils) {
            if (empty($coils)) {
                continue;
            }

            // total_nilai_inventory sama untuk semua coil dalam 1 material
            $total_nilai = (float) $coils[0]['total_nilai_inventory'];

            if ($total_nilai == 0) {
                continue;
            }

            // Kelompokkan coil per pack dalam material ini
            $by_pack = [];
            foreach ($coils as $c) {
                $pack_id = $c['id_ros_pack'];
                if (empty($pack_id)) {
                    continue; // coil tanpa pack (seharusnya tidak terjadi, tapi jaga-jaga)
                }
                $by_pack[$pack_id][] = $c;
            }

            if (empty($by_pack)) {
                continue;
            }

            $total_berat = 0;
            foreach ($coils as $c) {
                $total_berat += (float) $c['berat_bersih'];
            }

            if ($total_berat <= 0) {
                // Fallback: kalau berat 0 semua, bagi rata per pack
                $n = count($by_pack);
                $equal = round($total_nilai / $n, 2);
                $i = 0;
                $sisa = $total_nilai;
                foreach ($by_pack as $pack_id => $pack_coils) {
                    $i++;
                    $val = ($i === $n) ? round($sisa, 2) : $equal; // baris terakhir ambil sisa
                    $pack_values[$pack_id] = ($pack_values[$pack_id] ?? 0) + $val;
                    $sisa -= $val;
                }
                continue;
            }

            // ── Largest remainder method ──
            $floor_sum  = 0.0;
            $exacts     = [];
            $floors     = [];
            $remainders = [];

            foreach ($by_pack as $pack_id => $pack_coils) {
                $berat_pack   = 0;
                foreach ($pack_coils as $pc) {
                    $berat_pack += (float) $pc['berat_bersih'];
                }
                $exact = $total_nilai * ($berat_pack / $total_berat);
                $floor = floor($exact * 100) / 100;

                $exacts[$pack_id]     = $exact;
                $floors[$pack_id]     = $floor;
                $remainders[$pack_id] = $exact - $floor;
                $floor_sum           += $floor;
            }

            // Sisa dalam satuan sen (integer), didistribusikan ke pack dengan
            // remainder terbesar terlebih dahulu.
            $sisa_sen = (int) round(($total_nilai - $floor_sum) * 100);

            arsort($remainders); // urutkan dari remainder terbesar

            foreach ($remainders as $pack_id => $r) {
                if ($sisa_sen <= 0) {
                    break;
                }
                $floors[$pack_id] += 0.01;
                $sisa_sen--;
            }

            // Jika sisa_sen masih > 0 (kasus ekstrem/pembulatan ganda),
            // lempar semua ke pack pertama supaya total tetap presisi.
            if ($sisa_sen > 0) {
                $first_pack_id = array_key_first($floors);
                $floors[$first_pack_id] += $sisa_sen / 100;
            }

            foreach ($floors as $pack_id => $val) {
                $pack_values[$pack_id] = ($pack_values[$pack_id] ?? 0) + round($val, 2);
            }
        }

        return $pack_values;
    }


    /* ============================================================================
 * B. MODUL INCOMING — GANTI FUNGSI finalize() SECARA UTUH
 * ========================================================================= */

    public function finalize()
    {
        ob_start();
        $this->auth->restrict($this->managePermission);
        $no_ros  = $this->input->post('no_ros');
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        $qc_json = $this->input->post('qc_data') ?: '[]';

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'ROS number is required!']);
            return;
        }

        // Decode QC data dari modal finalize
        $qc_data = json_decode($qc_json, true);
        $qc_map  = [];
        if (!empty($qc_data) && is_array($qc_data)) {
            foreach ($qc_data as $qc) {
                $qc_map[$qc['no_coil']] = $qc['status_qc'] ?? 'OK';
            }
        }

        $ros_header = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '1',
            'status_incoming' => 'submitted',
        ])->row();

        if (empty($ros_header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Draft data not found!']);
            return;
        }

        // Ambil semua coil
        $coils = $this->db->query("
        SELECT
            c.id                    AS id_ros_material_coil,
            c.no_coil,
            c.kode_internal,
            c.berat_kotor,
            c.berat_bersih,
            c.panjang,
            c.bpm,
            c.id_gudang_ke,
            c.kd_gudang_ke,
            c.status_qc,
            c.price_per_coil,
            c.is_baby_coil,
            c.qty_roll,
            c.parent_coil_id,
            c.id_ros_pack,
            m.id                    AS id_ros_material,
            m.id_barang             AS id_material,
            m.nm_erp                AS nm_material,
            m.nm_alias              AS trade_name,
            m.cost_book,
            m.total_nilai_inventory,
            m.id_po_detail,
            h.no_po,
            h.id_supplier,
            h.kurs_pib,
            p.pack_code
        FROM tr_ros_material_coil c
        JOIN tr_ros_material m ON m.id = c.id_ros_material
        JOIN tr_ros_header h   ON h.id = m.id_ros
        LEFT JOIN tr_ros_pack p ON p.id = c.id_ros_pack
        WHERE m.id_ros = ?
        ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        if (empty($coils)) {
            echo json_encode(['status' => 0, 'pesan' => 'No coil data available!']);
            return;
        }

        foreach ($coils as $c) {
            if (empty($c['id_gudang_ke'])) {
                echo json_encode(['status' => 0, 'pesan' => 'Some coils have not been assigned a warehouse!']);
                return;
            }
        }

        // Update status_qc dari modal finalize (hanya penanda)
        foreach ($coils as &$c) {
            if (isset($qc_map[$c['no_coil']])) {
                $c['status_qc'] = $qc_map[$c['no_coil']];
                $this->db->update('tr_ros_material_coil', [
                    'status_qc' => $c['status_qc'],
                ], ['id' => (int) $c['id_ros_material_coil']]);
            }
        }
        unset($c);

        // Validasi template jurnal — harus sudah dikonfigurasi sebelum proses
        $po_row   = $this->db->get_where('tr_purchase_order', ['no_po' => $ros_header->no_po])->row();
        $is_lokal = ($po_row && strtolower($po_row->loi) === 'lokal');
        $action_key = $is_lokal ? 'finalize_lokal' : 'finalize_import';
        $tipe_label = $is_lokal ? 'Lokal' : 'Import';

        $jurnal_mapping = $this->db->get_where('ms_jurnal_mapping', [
            'menu'   => 'finalize_incoming',
            'action' => $action_key
        ])->row();

        if (!$jurnal_mapping) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 0,
                'pesan'  => "Template jurnal untuk Incoming {$tipe_label} belum dibuat. Silakan konfigurasi di Master Jurnal Mapping (menu: finalize_incoming, action: {$action_key})."
            ]);
            exit;
        }

        // ── Validasi header & detail template jurnal juga sudah lengkap ──
        $db_acc = $this->load->database('accounting', TRUE);

        $tpl_header = $db_acc->get_where('master_oto_jurnal_header', [
            'kode_master_jurnal' => $jurnal_mapping->kode_master_jurnal
        ])->row();

        $tpl_detail_count = $db_acc
            ->where('kode_master_jurnal', $jurnal_mapping->kode_master_jurnal)
            ->count_all_results('master_oto_jurnal_detail');

        if (!$tpl_header || $tpl_detail_count === 0) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 0,
                'pesan'  => "Template jurnal '{$jurnal_mapping->kode_master_jurnal}' untuk Incoming {$tipe_label} belum lengkap (header/detail belum dikonfigurasi). Silakan lengkapi di menu Master Jurnal Template sebelum finalize."
            ]);
            exit;
        }

        $this->db->trans_begin();

        $kode_incoming      = $this->Finalize_incoming_model->generate_id_incoming();
        $total_berat        = 0;
        $total_nilai_inc    = 0;
        $details_to_insert  = [];
        $pack_codes_inserted = []; // Track pack_code yang sudah di-insert ke warehouse_pack

        // ★ BARU: kumpulkan coil per material (utk alokasi pack) & pack->gudang map
        $coils_by_material_for_alloc = [];
        $pack_gudang_map             = []; // id_ros_pack => kd_gudang_ke

        foreach ($coils as $c) {
            if ((float) $c['berat_bersih'] <= 0) continue;

            // Skip mother coil yang punya baby (bukan unit fisik)
            $is_mother_with_baby = ((int) $c['is_baby_coil'] === 0 && (int) $c['qty_roll'] > 1);
            if ($is_mother_with_baby) continue;

            $berat_bersih    = (float) $c['berat_bersih'];
            $cost_book       = (float) $c['cost_book'];
            $price_per_coil  = (float) $c['price_per_coil'];
            $nilai_inventory = (float) $c['price_per_coil'];

            // Resolve parent no_coil untuk baby coil
            $parent_no_coil = null;
            if ((int) $c['is_baby_coil'] === 1 && !empty($c['parent_coil_id'])) {
                $parent_row = $this->db->get_where('tr_ros_material_coil', ['id' => (int) $c['parent_coil_id']])->row();
                $parent_no_coil = $parent_row ? $parent_row->no_coil : null;
            }

            $details_to_insert[] = [
                'kode_trans'           => $kode_incoming,
                'id_ros_material_coil' => $c['id_ros_material_coil'],
                'id_ros_material'      => $c['id_ros_material'],
                'id_material'          => $c['id_material'],
                'nm_material'          => $c['nm_material'],
                'trade_name'           => $c['trade_name'],
                'no_coil'              => $c['no_coil'],
                'parent_no_coil'       => $parent_no_coil,
                'kode_internal'        => $c['kode_internal'],
                'berat_kotor'          => $c['berat_kotor'],
                'berat_bersih'         => $berat_bersih,
                'panjang'              => $c['panjang'],
                'bpm'                  => $c['bpm'],
                'id_gudang_ke'         => $c['id_gudang_ke'],
                'kd_gudang_ke'         => $c['kd_gudang_ke'],
                'status_qc'            => $c['status_qc'] ?? 'OK',
                'price_per_coil'       => $price_per_coil,
                'cost_book'            => $cost_book,
                'nilai_inventory'      => $nilai_inventory,
                'is_baby_coil'         => (int) $c['is_baby_coil'],
                'qty_roll'             => (int) $c['qty_roll'],
                'parent_coil_id'       => $c['parent_coil_id'],
                'pack_code'            => $c['pack_code'],
                'id_ros_pack'          => $c['id_ros_pack'],
            ];

            // Semua coil fisik masuk stok
            $total_berat     += $berat_bersih;
            $total_nilai_inc += $price_per_coil;

            // ★ BARU: kumpulkan data untuk alokasi nilai per pack
            $coils_by_material_for_alloc[$c['id_ros_material']][] = [
                'id_ros_pack'            => $c['id_ros_pack'],
                'berat_bersih'           => $berat_bersih,
                'total_nilai_inventory'  => (float) $c['total_nilai_inventory'],
            ];
            if (!empty($c['id_ros_pack'])) {
                $pack_gudang_map[$c['id_ros_pack']] = strtoupper(trim($c['kd_gudang_ke']));
            }

            // Insert warehouse_pack (sekali per pack_code unik)
            $pack_code = $c['pack_code'];
            if (!empty($pack_code) && !isset($pack_codes_inserted[$pack_code])) {
                $this->db->insert('warehouse_pack', [
                    'pack_code'  => $pack_code,
                    'ref_number' => $kode_incoming,
                    'no_po'      => $ros_header->no_po,
                    'id_gudang'  => (int) $c['id_gudang_ke'],
                    'kd_gudang'  => $c['kd_gudang_ke'],
                    'status'     => 1,
                    'created_by' => $this->auth->user_id(),
                    'created_on' => date('Y-m-d H:i:s'),
                ]);
                $pack_codes_inserted[$pack_code] = $this->db->insert_id();
            }

            // Update warehouse stock
            $this->_update_stock_and_history(
                $c['id_material'],
                $c['nm_material'],
                $berat_bersih,
                $cost_book,
                $kode_incoming,
                $ros_header->no_po,
                $c['no_coil'],
                (int) $c['id_gudang_ke'],
                $c['kd_gudang_ke'],
                $no_ros,
                [
                    'id_pack'        => isset($pack_codes_inserted[$c['pack_code']]) ? $pack_codes_inserted[$c['pack_code']] : null,
                    'qty_roll'       => (int) $c['qty_roll'],
                    'is_baby_coil'   => (int) $c['is_baby_coil'],
                    'parent_coil_id' => $c['parent_coil_id'],
                ]
            );

            // Update qty_in di dt_trans_po
            $this->db->set('qty_in', 'qty_in + ' . $berat_bersih, FALSE);
            $this->db->where('id', $c['id_po_detail']);
            $this->db->update('dt_trans_po');
        }

        // ── Susun summary per material per gudang ────────────────────────────────
        $summary_map = [];
        foreach ($details_to_insert as $d) {
            if ((float)$d['berat_bersih'] <= 0) continue;

            $key = $d['id_material'] . '_' . $d['id_gudang_ke'];

            if (!isset($summary_map[$key])) {
                $first_hist = $this->db->query("
        SELECT saldo_awal, qty_stock_awal, harga_lama
        FROM warehouse_history
        WHERE no_ipp = ? AND id_material = ? AND id_gudang = ?
        ORDER BY id ASC LIMIT 1
        ", [$kode_incoming, $d['id_material'], $d['id_gudang_ke']])->row();

                $last_hist = $this->db->query("
        SELECT saldo_akhir, qty_stock_akhir, harga_baru
        FROM warehouse_history
        WHERE no_ipp = ? AND id_material = ? AND id_gudang = ?
        ORDER BY id DESC LIMIT 1
        ", [$kode_incoming, $d['id_material'], $d['id_gudang_ke']])->row();

                $summary_map[$key] = [
                    'kode_trans'    => $kode_incoming,
                    'id_material'   => $d['id_material'],
                    'nm_material'   => $d['nm_material'],
                    'id_gudang'     => $d['id_gudang_ke'],
                    'kd_gudang'     => $d['kd_gudang_ke'],
                    'tanggal'       => $tanggal,
                    'jumlah_coil'   => 0,
                    'qty_awal'      => $first_hist ? (float)$first_hist->qty_stock_awal : 0,
                    'qty_transaksi' => 0,
                    'qty_akhir'     => $last_hist  ? (float)$last_hist->qty_stock_akhir : 0,
                    'costbook'      => $last_hist  ? (float)$last_hist->harga_baru      : 0,
                    'total_harga'   => 0,
                    'saldo_awal'    => $first_hist ? (int)$first_hist->saldo_awal       : 0,
                    'saldo_akhir'   => $last_hist  ? (int)$last_hist->saldo_akhir       : 0,
                    'harga_lama'    => $first_hist ? (float)$first_hist->harga_lama     : 0,
                    'created_by'    => $this->auth->user_id(),
                    'created_at'    => date('Y-m-d H:i:s'),
                ];
            }

            $summary_map[$key]['jumlah_coil']++;
            $summary_map[$key]['qty_transaksi'] += (float)$d['berat_bersih'];
            $summary_map[$key]['total_harga']   += (float)$d['price_per_coil'];

            // Insert detail snapshot coil
            $this->db->insert('warehouse_stock_transaction_detail', [
                'kode_trans'     => $kode_incoming,
                'id_material'    => $d['id_material'],
                'nm_material'    => $d['nm_material'],
                'id_gudang'      => $d['id_gudang_ke'],
                'kd_gudang'      => $d['kd_gudang_ke'],
                'no_coil'        => $d['no_coil'],
                'parent_no_coil' => $d['parent_no_coil'] ?? null,
                'kode_internal'  => $d['kode_internal'],
                'gross_weight'   => $d['berat_kotor'],
                'net_weight'     => $d['berat_bersih'],
                'length'         => $d['panjang'],
                'price_per_coil' => $d['price_per_coil'],
                'cost_book'      => $d['cost_book'],
                'status_qc'      => 'IN',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        // Insert summary per material
        foreach ($summary_map as $s) {
            $this->db->insert('warehouse_stock_transaction_summary', $s);
        }

        // Insert header incoming
        $supplier_row = $this->db->get_where('new_supplier', ['kode_supplier' => $ros_header->id_supplier])->row();
        $nm_supplier  = $supplier_row ? $supplier_row->nama : '';

        // ★★★ PERUBAHAN UTAMA ★★★
        // value_inc_pro / value_inc_sli TIDAK LAGI dijumlah dari SUM(price_per_coil)
        // per coil (yang rawan rounding error karena banyak baris). Sebagai
        // gantinya, dialokasikan per PACK memakai largest-remainder, sehingga
        // SUM(value_inc_pro + value_inc_sli) presisi 100% = SUM(total_nilai_inventory).
        $pack_values = $this->_allocate_value_by_pack($coils_by_material_for_alloc);

        $value_inc_pro = 0;
        $value_inc_sli = 0;
        foreach ($pack_values as $pack_id => $val) {
            $kd = $pack_gudang_map[$pack_id] ?? '';
            if ($kd === 'SLI') {
                $value_inc_sli += $val;
            } else {
                $value_inc_pro += $val;
            }
        }

        $value_inc_pro = round($value_inc_pro, 2);
        $value_inc_sli = round($value_inc_sli, 2);

        $this->db->insert('tr_incoming_header', [
            'kode_trans'         => $kode_incoming,
            'no_ros'             => $no_ros,
            'no_po'              => $ros_header->no_po,
            'no_surat'           => $ros_header->no_surat,
            'id_supplier'        => $ros_header->id_supplier,
            'nm_supplier'        => $nm_supplier,
            'tanggal'            => $tanggal,
            'total_berat_bersih' => $total_berat,
            'total_nilai'        => $total_nilai_inc,
            'value_inc_pro'      => $value_inc_pro,
            'value_inc_sli'      => $value_inc_sli,

            // ── Field GL, di-copy dari ROS ──
            'gl_advance_purchase_from_ros'      => round((float) ($ros_header->gl_advance_purchase ?? 0), 2),
            'gl_persediaan_intransit_from_ros'  => round((float) ($ros_header->gl_persediaan_intransit ?? 0), 2),
            'gl_unbill_from_ros'                => round((float) ($ros_header->gl_unbill ?? 0), 2),
            // 'gl_pembulatan_from_ros'            => round((float) ($ros_header->gl_pembulatan ?? 0), 2), // ★ BARU

            'file_dokumen'       => $ros_header->file_hash   ?? '',
            'file_original'      => $ros_header->file_original ?? '',
            'status'             => 'finalized',
            'created_by'         => $this->auth->user_id(),
            'created_at'         => date('Y-m-d H:i:s'),
            'finalized_by'       => $this->auth->user_id(),
            'finalized_at'       => date('Y-m-d H:i:s'),
        ]);

        // Insert detail incoming
        foreach ($details_to_insert as $d) {
            $this->db->insert('tr_incoming_detail', $d);
        }

        // Close ROS
        $this->db->update('tr_ros_header', [
            'status_incoming' => 'closed',
        ], ['id' => $no_ros]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            ob_clean();
            echo json_encode(['status' => 0, 'pesan' => 'Failed to process finalize!']);
            return;
        }
        $this->db->trans_commit();

        // Generate jurnal
        $jurnal_error = null;
        if ($total_nilai_inc > 0) {
            try {
                $this->_generate_jurnal_incoming(
                    $kode_incoming,
                    $ros_header->no_po,
                    $ros_header->no_surat ?? '',
                    $ros_header->id_supplier,
                    $no_ros,
                    $details_to_insert,
                    round((float) ($ros_header->gl_advance_purchase ?? 0), 2),
                    round((float) ($ros_header->gl_persediaan_intransit ?? 0), 2),
                    round((float) ($ros_header->gl_unbill ?? 0), 2),
                    round((float) ($ros_header->gl_pembulatan ?? 0), 2), // ★ BARU
                    $value_inc_pro,   // ★ BARU: dikirim langsung, bukan dihitung ulang
                    $value_inc_sli    // ★ BARU
                );
            } catch (Exception $e) {
                $jurnal_error = $e->getMessage();
                log_message('error', 'GL error finalize_incoming ' . $kode_incoming . ': ' . $jurnal_error);
            }
        }

        ob_clean();
        header('Content-Type: application/json');

        if ($jurnal_error) {
            echo json_encode(['status' => 2, 'pesan' => 'Finalize successful, but GL Interface failed: ' . $jurnal_error]);
        } else {
            echo json_encode(['status' => 1, 'pesan' => 'Finalize successful! Stock and journal have been processed.']);
        }
        exit;
    }

    // public function _generate_jurnal_incoming(
    //     $kode_trans,
    //     $no_po,
    //     $no_surat,
    //     $id_supplier,
    //     $no_ros,
    //     array $details,
    //     $gl_advance_purchase = 0,
    //     $gl_persediaan_intransit = 0,
    //     $gl_unbill = 0
    // ) {
    //     // Hitung value per gudang dari details (tetap dipakai utk validasi & value_inc_pro/sli)
    //     $value_inc_pro = 0;
    //     $value_inc_sli = 0;
    //     $total_kredit  = 0;

    //     foreach ($details as $d) {
    //         if ($d['status_qc'] !== 'OK') continue;

    //         $kd = strtoupper(trim($d['kd_gudang_ke']));
    //         if ($kd === 'SLI') {
    //             $value_inc_sli += (float) $d['price_per_coil'];
    //         } else {
    //             $value_inc_pro += (float) $d['price_per_coil'];
    //         }
    //         $total_kredit += (float) $d['price_per_coil'];
    //     }

    //     // Round setelah akumulasi
    //     $value_inc_pro = round($value_inc_pro, 2);
    //     $value_inc_sli = round($value_inc_sli, 2);
    //     $total_kredit  = round($total_kredit, 2);

    //     // Round nilai GL dari ROS (jaga-jaga kalau caller belum round)
    //     $gl_advance_purchase     = round((float) $gl_advance_purchase, 2);
    //     $gl_persediaan_intransit = round((float) $gl_persediaan_intransit, 2);
    //     $gl_unbill               = round((float) $gl_unbill, 2);

    //     if ($total_kredit <= 0) {
    //         // Tidak ada nilai fisik yang perlu dijurnal (semua reject / nilai 0).
    //         log_message('info', "Skip generate jurnal incoming {$kode_trans}: total_kredit = 0 (kemungkinan semua coil status_qc != OK).");
    //         return;
    //     }

    //     // Tentukan tipe PO: Lokal atau Import
    //     $po_row   = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
    //     $is_lokal = ($po_row && strtolower($po_row->loi) === 'lokal');

    //     // Lookup mapping berdasarkan tipe (action berbeda untuk lokal/import)
    //     $action_key = $is_lokal ? 'finalize_lokal' : 'finalize_import';
    //     $mapping = $this->db->get_where('ms_jurnal_mapping', [
    //         'menu'   => 'finalize_incoming',
    //         'action' => $action_key
    //     ])->row();

    //     $tipe_label = $is_lokal ? 'Lokal' : 'Import';

    //     if (!$mapping) {
    //         // Template jurnal belum dikonfigurasi — block proses
    //         throw new Exception("Template jurnal untuk Incoming {$tipe_label} belum dibuat. Silakan konfigurasi di ms_jurnal_mapping (menu: finalize_incoming, action: {$action_key}).");
    //     }

    //     // ── Validasi tambahan: pastikan header & detail template jurnal juga sudah lengkap ──
    //     // (ms_jurnal_mapping hanya menyimpan pointer kode_master_jurnal;
    //     //  isi template sebenarnya ada di master_oto_jurnal_header/_detail)
    //     $db_acc = $this->load->database('accounting', TRUE);

    //     $tpl_header = $db_acc->get_where('master_oto_jurnal_header', [
    //         'kode_master_jurnal' => $mapping->kode_master_jurnal
    //     ])->row();

    //     $tpl_detail_count = $db_acc
    //         ->where('kode_master_jurnal', $mapping->kode_master_jurnal)
    //         ->count_all_results('master_oto_jurnal_detail');

    //     if (!$tpl_header || $tpl_detail_count === 0) {
    //         throw new Exception("Template jurnal '{$mapping->kode_master_jurnal}' untuk Incoming {$tipe_label} belum lengkap (header/detail belum dikonfigurasi). Silakan lengkapi di menu Master Jurnal Template.");
    //     }

    //     // ── Gunakan generate_jurnal_dari_template ──
    //     $this->load->model('gl_interface/Gl_interface_model');

    //     $supplier      = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row();
    //     $supplier_name = $supplier ? $supplier->nama : '-';

    //     $data_source = [
    //         'tanggal'        => date('Y-m-d'),
    //         'no_po'          => $no_po,
    //         'no_surat'       => $no_surat,
    //         'id_supplier'    => $id_supplier,
    //         'nm_supplier'    => $supplier_name,
    //         'no_ros'         => $no_ros,
    //         'kode_trans'     => $kode_trans,
    //         'no_request'     => $kode_trans,
    //         'no_doc'         => $no_po,
    //         'value_inc_pro'  => $value_inc_pro,
    //         'value_inc_sli'  => $value_inc_sli,

    //         // ── Samakan nama key dengan field di master_oto_jurnal_detail ──
    //         'gl_advance_purchase_from_ros'     => $gl_advance_purchase,
    //         'gl_persediaan_intransit_from_ros' => $gl_persediaan_intransit,
    //         'gl_unbill_from_ros'               => $gl_unbill,

    //         'loi'            => $tipe_label,
    //     ];

    //     $kode_jurnal = $mapping->kode_master_jurnal;
    //     $id_gl = $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $data_source);

    //     // Safety net: kalau meski sudah divalidasi di atas, generate_jurnal_dari_template
    //     // tetap return false (misal race condition template dihapus di tengah proses),
    //     // jangan biarkan finalize() melaporkan sukses.

    //     if ($id_gl === false) {
    //         throw new Exception("Gagal generate jurnal dari template '{$kode_jurnal}' untuk Incoming {$tipe_label}.");
    //     }
    // }

    /* ============================================================================
 * B. MODUL INCOMING — GANTI FUNGSI _generate_jurnal_incoming() SECARA UTUH
 * ========================================================================= */

    public function _generate_jurnal_incoming(
        $kode_trans,
        $no_po,
        $no_surat,
        $id_supplier,
        $no_ros,
        array $details,
        $gl_advance_purchase = 0,
        $gl_persediaan_intransit = 0,
        $gl_unbill = 0,
        $gl_pembulatan = 0,     // ★ BARU
        $value_inc_pro = null,  // ★ BARU: kalau null, fallback hitung dari $details (backward compatible)
        $value_inc_sli = null   // ★ BARU
    ) {
        // ★ PERUBAHAN: value_inc_pro/sli sebaiknya dikirim langsung dari finalize()
        // (hasil alokasi per-pack yang presisi). Kalau caller lama belum
        // mengirimkannya (null), fallback ke cara lama (SUM per coil) supaya
        // fungsi ini tetap backward-compatible jika dipanggil dari tempat lain.
        if ($value_inc_pro === null || $value_inc_sli === null) {
            $value_inc_pro = 0;
            $value_inc_sli = 0;
            foreach ($details as $d) {
                if ($d['status_qc'] !== 'OK') continue;
                $kd = strtoupper(trim($d['kd_gudang_ke']));
                if ($kd === 'SLI') {
                    $value_inc_sli += (float) $d['price_per_coil'];
                } else {
                    $value_inc_pro += (float) $d['price_per_coil'];
                }
            }
            $value_inc_pro = round($value_inc_pro, 2);
            $value_inc_sli = round($value_inc_sli, 2);
        }

        $total_kredit = round($value_inc_pro + $value_inc_sli, 2);

        // Round nilai GL dari ROS (jaga-jaga kalau caller belum round)
        $gl_advance_purchase     = round((float) $gl_advance_purchase, 2);
        $gl_persediaan_intransit = round((float) $gl_persediaan_intransit, 2);
        $gl_unbill               = round((float) $gl_unbill, 2);
        $gl_pembulatan           = round((float) $gl_pembulatan, 2); // ★ BARU

        if ($total_kredit <= 0) {
            log_message('info', "Skip generate jurnal incoming {$kode_trans}: total_kredit = 0 (kemungkinan semua coil status_qc != OK).");
            return;
        }

        // Tentukan tipe PO: Lokal atau Import
        $po_row   = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
        $is_lokal = ($po_row && strtolower($po_row->loi) === 'lokal');

        $action_key = $is_lokal ? 'finalize_lokal' : 'finalize_import';
        $mapping = $this->db->get_where('ms_jurnal_mapping', [
            'menu'   => 'finalize_incoming',
            'action' => $action_key
        ])->row();

        $tipe_label = $is_lokal ? 'Lokal' : 'Import';

        if (!$mapping) {
            throw new Exception("Template jurnal untuk Incoming {$tipe_label} belum dibuat. Silakan konfigurasi di ms_jurnal_mapping (menu: finalize_incoming, action: {$action_key}).");
        }

        $db_acc = $this->load->database('accounting', TRUE);

        $tpl_header = $db_acc->get_where('master_oto_jurnal_header', [
            'kode_master_jurnal' => $mapping->kode_master_jurnal
        ])->row();

        $tpl_detail_count = $db_acc
            ->where('kode_master_jurnal', $mapping->kode_master_jurnal)
            ->count_all_results('master_oto_jurnal_detail');

        if (!$tpl_header || $tpl_detail_count === 0) {
            throw new Exception("Template jurnal '{$mapping->kode_master_jurnal}' untuk Incoming {$tipe_label} belum lengkap (header/detail belum dikonfigurasi). Silakan lengkapi di menu Master Jurnal Template.");
        }

        $this->load->model('gl_interface/Gl_interface_model');

        $supplier      = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row();
        $supplier_name = $supplier ? $supplier->nama : '-';

        $value_inc_pro_gl = (int) round($value_inc_pro, 0);
        $value_inc_sli_gl = (int) round($value_inc_sli, 0);

        $gl_advance_purchase     = (int) round($gl_advance_purchase, 0);
        $gl_persediaan_intransit = (int) round($gl_persediaan_intransit, 0);
        $gl_unbill               = (int) round($gl_unbill, 0);
        $gl_pembulatan           = (int) round($gl_pembulatan, 0);

        $data_source = [
            'tanggal'        => date('Y-m-d'),
            'no_po'          => $no_po,
            'no_surat'       => $no_surat,
            'id_supplier'    => $id_supplier,
            'nm_supplier'    => $supplier_name,
            'no_ros'         => $no_ros,
            'kode_trans'     => $kode_trans,
            'no_request'     => $kode_trans,
            'no_doc'         => $no_po,
            'value_inc_pro'  => $value_inc_pro_gl,
            'value_inc_sli'  => $value_inc_sli_gl,

            'gl_advance_purchase_from_ros'     => $gl_advance_purchase,
            'gl_persediaan_intransit_from_ros' => $gl_persediaan_intransit,
            'gl_unbill_from_ros'               => $gl_unbill,

            'loi'            => $tipe_label,
        ];

        $kode_jurnal = $mapping->kode_master_jurnal;
        $id_gl = $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $data_source);

        if ($id_gl === false) {
            throw new Exception("Gagal generate jurnal dari template '{$kode_jurnal}' untuk Incoming {$tipe_label}.");
        }
    }
}
