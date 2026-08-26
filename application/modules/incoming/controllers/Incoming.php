<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Incoming extends Admin_Controller
{
    protected $viewPermission   = 'Incoming.View';
    protected $addPermission    = 'Incoming.Add';
    protected $managePermission = 'Incoming.Manage';
    protected $deletePermission = 'Incoming.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Incoming/Incoming_model', 'jurnal_nomor/Jurnal_model', 'all/All_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->render('index');
    }

    public function data_side_all()
    {
        $ENABLE_ADD    = has_permission('Incoming.Add');
        $ENABLE_MANAGE = has_permission('Incoming.Manage');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [
            1 => 'r.id',
            2 => 'r.no_po',
            3 => 's.nama',
            4 => 'r.kurs_pib',
            5 => 'r.status_incoming',
        ];
        $order_by = $col_map[$order_col] ?? 'r.id';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
          LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
          WHERE r.status = '1'
          AND (r.status_incoming IS NULL OR r.status_incoming IN ('open', 'saved'))
          {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
        SELECT r.*, s.nama AS nm_supplier,
               u.nm_lengkap AS draft_by_name,
               GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list
        FROM tr_ros_header r
        LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
        LEFT JOIN tr_purchase_order p ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
        LEFT JOIN users u ON u.id_user = r.draft_by
        WHERE r.status = '1'
        AND (r.status_incoming IS NULL OR r.status_incoming IN ('open', 'saved'))
        {$where_search}
        GROUP BY r.id
        ORDER BY {$order_by} {$order_dir}
        LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $status = $row['status_incoming'];
            $no_po_display = !empty($row['no_surat_list']) ? $row['no_surat_list'] : $row['no_po'];

            // Badge status
            if (empty($status) || $status === 'open') {
                $sts_badge = '<span class="badge rounded-pill bg-warning text-dark">Open</span>';
            } else {
                $sts_badge = '<span class="badge rounded-pill bg-primary text-white">Saved</span>';
            }

            // Tombol aksi
            $aksi = '';
            if (empty($status) || $status === 'open') {
                if ($ENABLE_ADD) {
                    $aksi = '<a href="' . base_url('incoming/add/' . $row['id']) . '" 
                            class="btn btn-sm btn-success" title="Proses Incoming">
                            <i class="fa fa-sign-in-alt"></i> Incoming
                         </a>';
                }
            } else {
                // Status saved — tampilkan Edit, Ajukan, Print
                $revision_info = '';
                if (!empty($row['revision_note'])) {
                    $revision_info = '<br><small class="text-danger"><i class="fa fa-exclamation-circle"></i> Revisi: '
                        . htmlspecialchars($row['revision_note']) . '</small>';
                }

                $btn_edit = '<a href="' . base_url('incoming/edit_draft/' . $row['id']) . '" 
                            class="btn btn-sm btn-warning" style="width:100px">
                            <i class="fa fa-edit"></i> Edit
                         </a>';

                $btn_submit = '<button class="btn btn-sm btn-success btn-submit-draft" 
                                data-id="' . $row['id'] . '" style="width:100px">
                                <i class="fa fa-paper-plane"></i> Submit
                           </button>';

                $coil_ids = $this->db->query(
                    "SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
                 FROM tr_ros_material_coil c
                 JOIN tr_ros_material m ON m.id = c.id_ros_material
                 WHERE m.id_ros = ?",
                    [$row['id']]
                )->row();
                $coil_ids_str = $coil_ids ? $coil_ids->ids : '';

                $btn_print = $coil_ids_str
                    ? '<a href="' . base_url('incoming/print_qr/' . $row['id']) . '" target="_blank" 
                      class="btn btn-sm btn-info" style="width:100px">
                      <i class="fa fa-print"></i> Print QR
                   </a>'
                    : '';

                $btn_print_pl = '<a href="' . base_url('incoming/print_pl_by_gudang/' . $row['id']) . '" target="_blank" 
                                class="btn btn-sm btn-secondary" style="width:100px">
                                <i class="fa fa-file-alt"></i> Print PL
                             </a>';

                $aksi = "<div class='d-flex flex-column align-items-center gap-1'>
                        <div class='d-flex gap-1'>{$btn_edit} {$btn_submit}</div>
                        <div class='d-flex gap-1'>{$btn_print} {$btn_print_pl}</div>
                     </div>";

                // Sertakan revision note di kolom No ROS
                $row['id'] .= $revision_info;
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-right'>" . number_format((float) $row['kurs_pib'], 0, ',', '.') . "</div>",
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

    // DATA TABLE — Tab OPEN
    public function data_side_incoming()
    {
        $ENABLE_ADD  = has_permission('Incoming.Add');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [
            1 => 'r.id',
            2 => 'r.no_po',
            3 => 's.nama',
            4 => 'r.kurs_pib',
        ];
        $order_by = $col_map[$order_col] ?? 'r.id';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
              LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
              WHERE r.status = '1' 
              AND (r.status_incoming IS NULL OR r.status_incoming = 'open')
              {$where_search}";


        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;


        $sql = "
    SELECT r.*, s.nama AS nm_supplier,
           GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list
    FROM tr_ros_header r
    LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
    LEFT JOIN tr_purchase_order p ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
    WHERE r.status = '1' 
    AND (r.status_incoming IS NULL OR r.status_incoming = 'open')
    {$where_search}
    GROUP BY r.id
    ORDER BY {$order_by} {$order_dir}
    LIMIT {$start}, {$length}
";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $sts_badge    = '<span class="badge rounded-pill bg-warning text-dark">Open</span>';
            $btn_incoming = '';
            if ($ENABLE_ADD) {
                $btn_incoming = '<a href="' . base_url('incoming/add/' . $row['id']) . '" class="btn btn-sm btn-success" title="Proses Incoming">
                    <i class="fa fa-sign-in-alt"></i> Incoming
                </a>';
            }
            $no_po_display = !empty($row['no_surat_list']) ? $row['no_surat_list'] : $row['no_po'];

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-right'>" . number_format((float) $row['kurs_pib'], 0, ',', '.') . "</div>",
                "<div class='text-center'>{$sts_badge}</div>",
                "<div class='text-center'>{$btn_incoming}</div>",
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

    // DATA TABLE — Tab SAVED (belum diajukan)
    public function data_side_saved()
    {
        $ENABLE_MANAGE = has_permission('Incoming.Manage');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [1 => 'r.id', 2 => 'r.no_po', 3 => 's.nama', 4 => 'r.draft_date'];
        $order_by = $col_map[$order_col] ?? 'r.draft_date';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
              LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
              WHERE r.status = '1' AND r.status_incoming = 'saved'
              {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT r.*, s.nama AS nm_supplier,
                   GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list,
                   u.nm_lengkap AS draft_by_name
            FROM tr_ros_header r
            LEFT JOIN new_supplier s   ON r.id_supplier = s.kode_supplier
            LEFT JOIN tr_purchase_order p ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
            LEFT JOIN users u          ON u.id_user = r.draft_by
            WHERE r.status = '1' AND r.status_incoming = 'saved'
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

            // Tampilkan catatan revisi jika ada
            $revision_info = '';
            if (!empty($row['revision_note'])) {
                $revision_info = '<br><small class="text-danger"><i class="fa fa-exclamation-circle"></i> Revisi: ' . htmlspecialchars($row['revision_note']) . '</small>';
            }

            $btn_edit = '<a href="' . base_url('incoming/edit_draft/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit" style="width:100px">
                <i class="fa fa-edit"></i> Edit
            </a>';

            $btn_submit = '<button class="btn btn-sm btn-success btn-submit-draft" data-id="' . $row['id'] . '" title="Submit Draft" style="width:100px">
                <i class="fa fa-paper-plane"></i> Submit
            </button>';

            $coil_ids = $this->db->query(
                "SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
                 FROM tr_ros_material_coil c
                 JOIN tr_ros_material m ON m.id = c.id_ros_material
                 WHERE m.id_ros = ?",
                [$row['id']]
            )->row();
            $coil_ids_str = $coil_ids ? $coil_ids->ids : '';

            $btn_print = $coil_ids_str
                ? '<a href="' . base_url('incoming/print_qr/' . $row['id']) . '" target="_blank" class="btn btn-sm btn-info" title="Print Label" style="width:100px">
                    <i class="fa fa-print"></i> Print QR
                  </a>'
                : '';

            $btn_print_pl = '<a href="' . base_url('incoming/print_pl_by_gudang/' . $row['id']) . '" target="_blank" class="btn btn-sm btn-secondary" title="Print PL" style="width:100px">
                    <i class="fa fa-file-alt"></i> Print PL
                </a>';

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'] . $revision_info,
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-center'>" . ($row['draft_date'] ? date('d/m/Y H:i', strtotime($row['draft_date'])) : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars($row['draft_by_name'] ?? '-') . "</div>",
                "<span class='badge rounded-pill bg-warning text-dark'>Saved</span>",
                "<div class='text-center d-flex flex-column align-items-center gap-1'>
                    <div class='d-flex gap-1'>{$btn_edit} {$btn_submit}</div>
                    <div class='d-flex gap-1'>{$btn_print} {$btn_print_pl}</div>
                </div>"
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

    // SUBMIT DRAFT — Ajukan ke finalize_incoming
    public function submit_draft()
    {
        $no_ros = $this->input->post('no_ros');

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
            return;
        }

        $ros_header = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '1',
            'status_incoming' => 'saved',
        ])->row();

        if (empty($ros_header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data not found or already submitted!']);
            return;
        }

        // Validasi semua coil sudah ada gudang
        $coils_no_gudang = $this->db->query("
            SELECT c.id FROM tr_ros_material_coil c
            JOIN tr_ros_material m ON m.id = c.id_ros_material
            WHERE m.id_ros = ? AND (c.id_gudang_ke IS NULL OR c.id_gudang_ke = 0)
        ", [$no_ros])->num_rows();

        if ($coils_no_gudang > 0) {
            echo json_encode(['status' => 0, 'pesan' => 'Some coils have not been assigned a destination warehouse! Please edit first.']);
            return;
        }

        $this->db->update('tr_ros_header', [
            'status_incoming' => 'submitted',
            'submitted_by'    => $this->auth->user_id(),
            'submitted_date'  => date('Y-m-d H:i:s'),
            'revision_note'   => null, // Clear revision note
        ], ['id' => $no_ros]);

        echo json_encode(['status' => 1, 'pesan' => 'Draft submitted to Finalize Incoming successfully.']);
    }

    // DATA TABLE — Tab DRAFT
    public function data_side_draft()
    {
        $ENABLE_MANAGE = has_permission('Incoming.Manage');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [1 => 'r.id', 2 => 'r.no_po', 3 => 's.nama', 4 => 'r.draft_date'];
        $order_by = $col_map[$order_col] ?? 'r.id';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
              LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
              WHERE r.status = '1' AND r.status_incoming = 'draft'
              {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
    SELECT r.*, s.nama AS nm_supplier,
           GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list,
           u.nm_lengkap AS draft_by_name
    FROM tr_ros_header r
    LEFT JOIN new_supplier s   ON r.id_supplier = s.kode_supplier
    LEFT JOIN tr_purchase_order p ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
    LEFT JOIN users u          ON u.id_user = r.draft_by
    WHERE r.status = '1' AND r.status_incoming = 'draft'
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
            $coil_ids = $this->db->query(
                "SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
                 FROM tr_ros_material_coil c
                 JOIN tr_ros_material m ON m.id = c.id_ros_material
                 WHERE m.id_ros = ?",
                [$row['id']]
            )->row();
            $coil_ids_str = $coil_ids ? $coil_ids->ids : '';

            $btn_edit = '<a href="' . base_url('incoming/edit_draft/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit Draft" style="width:100px">
                <i class="fa fa-edit"></i> Edit
            </a>';

            $btn_print = $coil_ids_str
                ? '<a href="' . base_url('incoming/print_qr/' . $row['id']) . '" target="_blank" class="btn btn-sm btn-info" title="Print Label" style="width:100px">
                    <i class="fa fa-print"></i> Print QR
                  </a>'
                : '';

            $btn_print_pl = '<a href="' . base_url('incoming/print_pl_by_gudang/' . $row['id']) . '" target="_blank" class="btn btn-sm btn-secondary" title="Print Packing List per Gudang" style="width:100px">
                    <i class="fa fa-file-alt"></i> Print PL
                </a>';

            $btn_finalize = '';
            if ($ENABLE_MANAGE) {
                $btn_finalize = '<button class="btn btn-sm btn-success btn-finalize" data-id="' . $row['id'] . '" title="Finalize & Close" style="width:100px">
                    <i class="fa fa-check-circle"></i> Finalize
                </button>';
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-center'>" . ($row['draft_date'] ? date('d/m/Y H:i', strtotime($row['draft_date'])) : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars($row['draft_by_name'] ?? '-') . "</div>",
                "<span class='badge rounded-pill bg-info text-dark'>Draft</span>",
                "<div class='text-center d-flex flex-column align-items-center gap-1'>
                    <div class='d-flex gap-1'>
                        {$btn_edit} 
                        {$btn_print}
                    </div>
                    <div class='d-flex gap-1'>
                        {$btn_print_pl} 
                        {$btn_finalize}
                    </div>
                </div>"
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

    // FORM ADD
    public function add()
    {
        $this->auth->restrict($this->viewPermission);
        $no_ros_default = $this->uri->segment(3);

        $list_supplier = $this->db->query("
            SELECT DISTINCT(b.kode_supplier), b.nama
            FROM tr_purchase_order a
            LEFT JOIN new_supplier b ON a.id_suplier = b.kode_supplier
            WHERE a.status = '2' AND b.kode_supplier IS NOT NULL
            ORDER BY b.nama ASC
        ")->result();

        $list_gudang = $this->db->query("
            SELECT id, nm_gudang, kd_gudang FROM warehouse WHERE status = 'Y' AND kd_gudang IN ('PRO','SLI') ORDER BY urut ASC
        ")->result_array();

        $ros_data = null;
        if (!empty($no_ros_default)) {
            $ros_data = $this->db->get_where('tr_ros_header', [
                'id'             => $no_ros_default,
                'status'         => '1',
            ])->row();
            // Hanya boleh add jika masih open
            if ($ros_data && !in_array($ros_data->status_incoming, ['open', null, ''])) {
                redirect('incoming');
            }
        }

        $data = [
            'page_mode'      => 'add',
            'list_supplier'  => $list_supplier,
            'list_gudang'    => $list_gudang,
            'no_ros_default' => $no_ros_default,
            'ros_data'       => $ros_data,
            'draft_coils'    => [],
        ];

        // echo '<pre>';
        // var_dump($data);die;
        // echo '</pre>';
        $this->template->set($data);
        $this->template->title('Incoming Based on ROS');
        $this->template->render('form');
    }

    // FORM EDIT DRAFT
    public function edit_draft()
    {
        $this->auth->restrict($this->viewPermission);
        $no_ros = $this->uri->segment(3);

        $ros_data = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '1',
            'status_incoming' => 'saved',
        ])->row();

        if (empty($ros_data)) {
            show_404();
        }

        $list_supplier = $this->db->query("
            SELECT DISTINCT(b.kode_supplier), b.nama
            FROM tr_purchase_order a
            LEFT JOIN new_supplier b ON a.id_suplier = b.kode_supplier
            WHERE a.status = '2' AND b.kode_supplier IS NOT NULL
            ORDER BY b.nama ASC
        ")->result();

        $list_gudang = $this->db->query("
            SELECT id, nm_gudang, kd_gudang FROM warehouse WHERE status = 'Y' AND kd_gudang IN ('PRO','SLI') ORDER BY urut ASC
        ")->result_array();

        $draft_coils = $this->db->query("
            SELECT c.id AS id_ros_coil_detail, c.no_coil, c.berat_kotor AS ros_kotor,
                   c.berat_bersih AS ros_bersih, c.id_gudang_ke, c.kd_gudang_ke, c.status_qc,
                   m.id AS id_ros_material, m.nm_erp AS nm_material, m.id_barang AS id_material,
                   m.unit_price_usd AS price_coil, m.total_value_rp AS price_coil_idr,
                   d.qty AS qty_po, d.qty_in, d.id AS id_po_detail,
                   h.id AS no_ros, h.incoming_date
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m      ON m.id = c.id_ros_material
            JOIN tr_ros_header h        ON h.id = m.id_ros
            LEFT JOIN dt_trans_po d     ON d.id = m.id_po_detail
            WHERE h.id = ?
            ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        $draft_coils_map = [];
        foreach ($draft_coils as $c) {
            $draft_coils_map[$c['id_ros_coil_detail']] = [
                'id_gudang_ke' => $c['id_gudang_ke'],
                'kd_gudang_ke' => $c['kd_gudang_ke'],
                'status_qc'    => $c['status_qc'] ?? 'OK',
            ];
        }

        $data = [
            'page_mode'       => 'edit_draft',
            'list_supplier'   => $list_supplier,
            'list_gudang'     => $list_gudang,
            'no_ros_default'  => $no_ros,
            'ros_data'        => $ros_data,
            'draft_coils'     => $draft_coils,
            'draft_coils_map' => $draft_coils_map,
        ];

        $this->template->set($data);
        $this->template->title('Edit Draft Incoming - ROS ' . $no_ros);
        $this->template->render('form');
    }

    // FORM VIEW
    public function view()
    {
        $kode_trans = $this->uri->segment(3);
        $get_incoming = $this->db->get_where('tr_incoming_header', ['kode_trans' => $kode_trans])->row();

        $sql = "
            SELECT 
                inc.kode_trans, 
                inc.tanggal, 
                inc.no_ros, 
                det.id_material, 
                det.nm_material,
                1 AS qty_order, -- Alias agar view tidak error (default 1 untuk coil)
                det.kode_internal AS keterangan, 
                det.price_per_coil AS harga,
                det.no_coil, 
                det.berat_kotor, 
                det.berat_bersih, 
                det.panjang AS length,
                det.status_qc, 
                det.id_gudang_ke,
                det.kd_gudang_ke AS kd_gudang,
                wh.nm_gudang,
                mat.nm_erp AS nm_barang,
                sup.nama AS nm_supplier
            FROM tr_incoming_header inc
            JOIN tr_incoming_detail det ON det.kode_trans = inc.kode_trans
            LEFT JOIN tr_ros_material mat ON mat.id = det.id_ros_material
            LEFT JOIN warehouse wh ON wh.id = det.id_gudang_ke
            LEFT JOIN new_supplier sup ON sup.kode_supplier = inc.id_supplier
            WHERE inc.kode_trans = ?
            ORDER BY det.id_material, det.no_coil ASC
        ";

        $detail_ros = $this->db->query($sql, [$kode_trans])->result_array();

        $no_surat_list = [];
        if ($get_incoming) {
            // 3. Ubah 'no_ipp' menjadi 'no_po' sesuai kolom baru
            $po_list = array_map('trim', explode(',', $get_incoming->no_po ?? ''));
            if (!empty($po_list)) {
                $get_no_surat = $this->db->select('no_surat')->from('tr_purchase_order')
                    ->where_in('no_po', $po_list)->get()->result();
                foreach ($get_no_surat as $item) {
                    $no_surat_list[] = $item->no_surat;
                }
            }
        }

        // var_dump($detail_ros[0]['nm_supplier']);die;

        $data = [
            'page_mode'              => 'view',
            'detail_ros'             => $detail_ros,
            'no_surat'               => implode(', ', $no_surat_list),
            'tanggal'                => $get_incoming ? date('d F Y', strtotime($get_incoming->tanggal)) : '-',
            // 4. Sesuaikan nama kolom file upload
            'file_incoming_material' => $get_incoming->file_dokumen ?? '',
            'ros_data'               => null,
            'draft_coils'            => [],
            'draft_coils_map'        => [],
            'nm_supplier_view'       => !empty($detail_ros) ? ($detail_ros[0]['nm_supplier'] ?? '-') : '-',
            'no_ros_view'            => $get_incoming->no_ros ?? '-',
            'list_supplier'          => [],
            'list_gudang'            => [],
            'no_ros_default'         => '',
        ];

        $this->template->set($data);
        $this->template->title('Detail Incoming');
        $this->template->render('form');
    }

    // SAVE DRAFT
    public function save_draft()
    {
        $post   = $this->input->post();
        $no_ros = $post['no_ros'] ?? '';

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
            return;
        }

        // ── Decode detail dari JSON ──
        $detail_json = $post['detail_json'] ?? '';
        $details     = json_decode($detail_json, true);

        if (empty($details)) {
            echo json_encode(['status' => 0, 'pesan' => 'Coil data not found!']);
            return;
        }

        // ── Validasi gudang ──
        foreach ($details as $val) {
            if (empty($val['id_gudang_ke'])) {
                echo json_encode(['status' => 0, 'pesan' => 'Semua coil harus dipilih gudang tujuannya!']);
                return;
            }
        }

        // ── Handle file upload ──
        $file_original = $_FILES['file_incoming_material']['name'][0] ?? '';
        $file_hash     = '';
        $tanggal       = $post['tanggal'] ?? date('Y-m-d');

        if (!empty($file_original)) {
            $file_hash = $this->_upload_incoming_files('file_incoming_material');
        } else {
            $file_original = $post['existing_file_original'] ?? '';
            $file_hash     = $post['existing_file_hash']     ?? '';
        }

        $this->db->trans_begin();

        // Cache lookup warehouse agar tidak query berulang
        $gudang_cache = [];
        foreach ($details as $val) {
            $id_gudang = (int) $val['id_gudang_ke'];
            if (!isset($gudang_cache[$id_gudang])) {
                $gd = $this->db->get_where('warehouse', ['id' => $id_gudang])->row();
                $gudang_cache[$id_gudang] = $gd ? $gd->kd_gudang : '';
            }
            $kd_gudang = $gudang_cache[$id_gudang];

            $this->db->update('tr_ros_material_coil', [
                'id_gudang_ke' => $id_gudang,
                'kd_gudang_ke' => $kd_gudang,
                'status_qc'    => $val['status_qc'] ?? 'OK',
            ], ['id' => (int) $val['id_ros_coil']]);
        }

        $this->db->update('tr_ros_header', [
            'status_incoming'   => 'saved',
            'draft_by'          => $this->auth->user_id(),
            'draft_date'        => date('Y-m-d H:i:s'),
            'incoming_date'     => $tanggal,
            'file_original'     => $file_original,
            'file_hash'         => $file_hash,
        ], ['id' => $no_ros]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Failed to save draft!']);
            return;
        }

        $this->db->trans_commit();

        // Jika flag submit_after_save, langsung ajukan
        $submit_after = $post['submit_after_save'] ?? '';
        if ($submit_after === '1') {
            $this->db->update('tr_ros_header', [
                'status_incoming' => 'submitted',
                'submitted_by'    => $this->auth->user_id(),
                'submitted_date'  => date('Y-m-d H:i:s'),
                'revision_note'   => null,
            ], ['id' => $no_ros]);
        }

        $coil_ids = $this->db->query(
            "SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
         FROM tr_ros_material_coil c
         JOIN tr_ros_material m ON m.id = c.id_ros_material
         WHERE m.id_ros = ?",
            [$no_ros]
        )->row();

        echo json_encode([
            'status'       => 1,
            'pesan'        => 'Draft saved successfully!',
            'no_ros'       => $no_ros,
            'print_url'    => base_url('incoming/print_qr/' . $no_ros),
            'print_pl_url' => base_url('incoming/print_pl_by_gudang/' . $no_ros),
        ]);
    }

    // UPDATE DRAFT (dari edit_draft)
    public function update_draft()
    {
        $this->save_draft();
    }

    // public function finalize()
    // {
    //     ob_start(); // Start output buffering untuk menangkap output debug jika ada
    //     $this->auth->restrict($this->managePermission);
    //     $no_ros = $this->input->post('no_ros');

    //     if (empty($no_ros)) {
    //         echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
    //         return;
    //     }

    //     $ros_header = $this->db->get_where('tr_ros_header', [
    //         'id'              => $no_ros,
    //         'status'          => '1',
    //         'status_incoming' => 'draft',
    //     ])->row();

    //     if (empty($ros_header)) {
    //         echo json_encode(['status' => 0, 'pesan' => 'Data draft tidak ditemukan atau sudah difinalize!']);
    //         return;
    //     }

    //     // Ambil semua coil dari draft (yang sudah ada gudang & QC)
    //     $coils = $this->db->query("
    //         SELECT c.*, m.id AS id_ros_material, m.nm_erp AS nm_material,
    //                m.id_barang AS id_material, m.unit_price_usd, m.total_value_rp,
    //                m.bm_rp, m.forwarding_cost, m.total_nilai_inventory, m.cost_book,
    //                m.id_po_detail, d.qty AS qty_po, d.qty_in, d.id AS id_po_detail_id,
    //                h.no_po
    //         FROM tr_ros_material_coil c
    //         JOIN tr_ros_material m  ON m.id = c.id_ros_material
    //         JOIN tr_ros_header h    ON h.id = m.id_ros
    //         LEFT JOIN dt_trans_po d ON d.id = m.id_po_detail
    //         WHERE m.id_ros = ?
    //         ORDER BY m.id_barang, c.no_coil ASC
    //     ", [$no_ros])->result_array();

    //     if (empty($coils)) {
    //         echo json_encode(['status' => 0, 'pesan' => 'Tidak ada data coil untuk diproses!']);
    //         return;
    //     }

    //     // Validasi semua coil sudah ada gudang
    //     foreach ($coils as $c) {
    //         if (empty($c['id_gudang_ke'])) {
    //             echo json_encode(['status' => 0, 'pesan' => 'Masih ada coil yang belum dipilih gudang. Silakan edit draft terlebih dahulu.']);
    //             return;
    //         }
    //     }

    //     // Ambil data pendukung dari POST (tanggal, file, dll dari form finalize)
    //     $tanggal        = $this->input->post('tanggal') ?: date('Y-m-d');
    //     $uang_muka_idr  = (float) str_replace(',', '', $this->input->post('uang_muka_idr') ?? 0);
    //     $uang_muka_usd  = (float) str_replace(',', '', $this->input->post('uang_muka') ?? 0);
    //     if (!empty($_FILES['file_incoming_material']['name'][0])) {
    //         $link = $this->_upload_incoming_files('file_incoming_material');
    //         $file_original_final = $_FILES['file_incoming_material']['name'][0] ?? '';
    //     } else {
    //         $link                = $ros_header->file_hash     ?? '';
    //         $file_original_final = $ros_header->file_original ?? '';
    //     }

    //     $coa_finalize = [
    //         'produksi'  => '1105-01-01',
    //         'slitting'  => '1105-01-02',
    //         'intransit' => '1105-01-03',
    //     ];

    //     $coa_check_fin = $this->_validate_and_get_coa_names($coa_finalize);
    //     if (!$coa_check_fin['valid']) {
    //         echo json_encode([
    //             'status' => 3,
    //             'pesan'  => 'Nomor COA berikut belum terdaftar di Master COA dan harus ditambahkan terlebih dahulu: '
    //                 . implode(', ', $coa_check_fin['not_found']),
    //         ]);
    //         return;
    //     }

    //     $this->db->trans_begin();

    //     $kode_incoming      = $this->Incoming_model->generate_id_incoming();
    //     $total_harga_check  = 0;
    //     $total_berat_check  = 0;
    //     $materials_incoming = [];
    //     $no_po              = $ros_header->no_po;
    //     $id_supplier        = $ros_header->id_supplier;
    //     $id_gudang_ke_last  = null;

    //     foreach ($coils as $val) {
    //         $aktual_bersih   = (float) ($val['berat_bersih'] ?? 0);
    //         $id_material     = $val['id_material'];
    //         $id_po_detail    = $val['id_po_detail_id'];
    //         $id_ros_material = $val['id_ros_material'];
    //         $no_coil         = $val['no_coil'];
    //         $status_qc       = $val['status_qc'] ?? 'OK';
    //         $id_gudang_ke    = (int) $val['id_gudang_ke'];
    //         $kd_gudang_ke    = $val['kd_gudang_ke'];

    //         if ($aktual_bersih <= 0) continue;

    //         $get_mat = $this->db->select('a.*, d.nama as unit, e.nama as packing')
    //             ->from('new_inventory_4 a')
    //             ->join('ms_satuan d', 'a.id_unit = d.id', 'left')
    //             ->join('ms_satuan e', 'a.id_unit_packing = e.id', 'left')
    //             ->where('a.code_lv4', $id_material)
    //             ->get()->row();

    //         $get_po = $this->db->get_where('dt_trans_po', ['id' => $id_po_detail])->row();

    //         if (empty($get_mat) || empty($get_po)) continue;

    //         $price_per_kg = !empty($val['cost_book'])
    //             ? (float) $val['cost_book']
    //             : (($val['berat_bersih'] > 0)
    //                 ? ((float) $val['total_nilai_inventory'] / (float) $val['berat_bersih'])
    //                 : 0);

    //         // Insert detail incoming
    //         $this->db->insert('tr_incoming_check_detail', [
    //             'kode_trans'      => $kode_incoming,
    //             'id_po_detail'    => $id_po_detail,
    //             'no_ipp'          => $no_po,
    //             'id_material_req' => $id_material,
    //             'id_material'     => $id_material,
    //             'nm_material'     => $get_mat->nama,
    //             'qty_order'       => $get_po->qty,
    //             'harga'           => $price_per_kg,
    //             'keterangan'      => "Coil Nomor: " . $no_coil,
    //         ]);
    //         $id_detail_inc = $this->db->insert_id();

    //         // Insert detail QC
    //         $this->db->insert('tr_checked_incoming_detail', [
    //             'kode_trans'   => $kode_incoming,
    //             'id_detail'    => $id_detail_inc,
    //             'id_material'  => $id_material,
    //             'nm_material'  => $get_mat->nama,
    //             'unit'         => $get_mat->unit,
    //             'packing'      => $get_mat->packing,
    //             'no_ipp'       => $no_po,
    //             'qty_order'    => $get_po->qty,
    //             'qty_incoming' => ($status_qc == 'OK') ? $aktual_bersih : 0,
    //             'qty_oke'      => ($status_qc == 'OK') ? $aktual_bersih : 0,
    //             'qty_ng'       => ($status_qc == 'REJECT') ? $aktual_bersih : 0,
    //             'sts'          => '1',
    //             'harga'        => $price_per_kg,
    //             'total_harga'  => $aktual_bersih * $price_per_kg,
    //             'status_qc'    => $status_qc,
    //             'id_gudang_ke' => $id_gudang_ke,
    //         ]);

    //         if ($status_qc == 'OK') {
    //             $nilai_coil = (int) round($aktual_bersih * $price_per_kg, 0);

    //             $this->_update_stock_and_history(
    //                 $id_material,
    //                 $get_mat->nama,
    //                 $aktual_bersih,
    //                 $price_per_kg,
    //                 $kode_incoming,
    //                 $no_po,
    //                 $no_coil,
    //                 $id_gudang_ke,
    //                 $kd_gudang_ke,
    //                 $no_ros
    //             );

    //             $this->db->set('qty_in', 'qty_in + ' . (float) $aktual_bersih, FALSE);
    //             $this->db->where('id', $id_po_detail);
    //             $this->db->update('dt_trans_po');

    //             $total_harga_check += $nilai_coil;
    //             $total_berat_check += $aktual_bersih;
    //             $id_gudang_ke_last  = $id_gudang_ke;

    //             $materials_incoming[] = [
    //                 'id_material'      => $id_material,
    //                 'nm_material'      => $get_mat->nama,
    //                 'qty'              => $aktual_bersih,
    //                 'harga'            => $price_per_kg,
    //                 'total_persediaan' => $nilai_coil,
    //                 'biaya_masuk'      => (float) ($val['bm_rp'] ?? 0),
    //                 'forwarding'       => (float) ($val['forwarding_cost'] ?? 0),
    //                 'price_coil_usd'   => (float) ($val['unit_price_usd'] ?? 0),
    //                 'price_coil_idr'   => (float) ($val['total_value_rp'] ?? 0),
    //                 'no_coil'          => $no_coil,
    //                 'id_gudang_ke'     => $id_gudang_ke,
    //                 'kd_gudang_ke'     => $kd_gudang_ke,
    //             ];
    //         }
    //     }

    //     // Insert header incoming
    //     $this->db->insert('tr_incoming_check', [
    //         'kode_trans'             => $kode_incoming,
    //         'tanggal'                => $tanggal,
    //         'no_ipp'                 => $no_po,
    //         'no_ros'                 => $no_ros,
    //         'category'               => 'incoming material',
    //         'jumlah_mat'             => $total_berat_check,
    //         'id_gudang_dari'         => 1,
    //         'kd_gudang_dari'         => 'PRO',
    //         'id_gudang_ke'           => null,
    //         'kd_gudang_ke'           => 'MULTI',
    //         'checked'                => 'Y',
    //         'file_incoming_material' => $link,
    //         'file_original'          => $file_original_final,
    //         'created_by'             => $this->auth->user_id(),
    //         'created_date'           => date('Y-m-d H:i:s'),
    //     ]);

    //     // Close ROS
    //     $this->db->update('tr_ros_header', [
    //         'status'          => '1',
    //         'status_incoming' => 'closed',
    //     ], ['id' => $no_ros]);

    //     $jurnal_params = null;
    //     if ($total_harga_check > 0) {
    //         $jurnal_params = [
    //             'kode_incoming' => $kode_incoming,
    //             'no_po'         => $no_po,
    //             'total'         => $total_harga_check,
    //             'id_supplier'   => $id_supplier,
    //             'materials'     => $materials_incoming,
    //             'uang_muka_idr' => $uang_muka_idr,
    //             'uang_muka_usd' => $uang_muka_usd,
    //             'no_ros'        => $no_ros,
    //             'id_gudang_ke'  => $id_gudang_ke_last,
    //         ];
    //     }

    //     if ($this->db->trans_status() === FALSE) {
    //         $this->db->trans_rollback();
    //         ob_clean();
    //         echo json_encode(['status' => 0, 'pesan' => 'Gagal memproses finalize!']);
    //         return;
    //     }
    //     $this->db->trans_commit();

    //     $jurnal_error = null;
    //     if ($jurnal_params !== null) {
    //         try {
    //             $this->_generate_jurnal_and_debt(
    //                 $jurnal_params['kode_incoming'],
    //                 $jurnal_params['no_po'],
    //                 $jurnal_params['total'],
    //                 $jurnal_params['id_supplier'],
    //                 $jurnal_params['materials'],
    //                 $jurnal_params['uang_muka_idr'],
    //                 $jurnal_params['uang_muka_usd'],
    //                 $jurnal_params['no_ros'],
    //                 $jurnal_params['id_gudang_ke']
    //             );
    //         } catch (Exception $e) {
    //             $jurnal_error = $e->getMessage();
    //             log_message('error', 'Jurnal error finalize ' . $kode_incoming . ': ' . $jurnal_error);
    //         }
    //     }

    //     ob_clean();
    //     header('Content-Type: application/json');

    //     if ($jurnal_error) {
    //         echo json_encode(['status' => 2, 'pesan' => 'Finalize berhasil, namun jurnal akuntansi gagal: ' . $jurnal_error]);
    //     } else {
    //         echo json_encode(['status' => 1, 'pesan' => 'Finalize berhasil! Stok, Hutang, dan Jurnal telah diproses.']);
    //     }
    //     exit;
    // }

    // FINALIZE (proses stok + jurnal + close)
    public function finalize()
    {
        ob_start();
        $this->auth->restrict($this->managePermission);
        $no_ros  = $this->input->post('no_ros');
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        $qc_json = $this->input->post('qc_data') ?: '[]';

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
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
            'status_incoming' => 'draft',
        ])->row();

        if (empty($ros_header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Draft data not found!']);
            return;
        }

        // Ambil semua coil beserta price_per_coil dan cost_book dari ROS
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
            m.id                    AS id_ros_material,
            m.id_barang             AS id_material,
            m.nm_erp                AS nm_material,
            m.nm_alias              AS trade_name,
            m.cost_book,          
            m.total_nilai_inventory,
            m.id_po_detail,
            h.no_po,
            h.id_supplier,
            h.kurs_pib
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m ON m.id = c.id_ros_material
            JOIN tr_ros_header h   ON h.id = m.id_ros
            WHERE m.id_ros = ?
            ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        if (empty($coils)) {
            echo json_encode(['status' => 0, 'pesan' => 'Tidak ada data coil!']);
            return;
        }

        foreach ($coils as $c) {
            if (empty($c['id_gudang_ke'])) {
                echo json_encode(['status' => 0, 'pesan' => 'Some coils have not been assigned a warehouse!']);
                return;
            }
        }

        // Update status_qc dari modal finalize ke tr_ros_material_coil (hanya penanda, tidak pengaruh ke stok/jurnal)
        foreach ($coils as &$c) {
            if (isset($qc_map[$c['no_coil']])) {
                $c['status_qc'] = $qc_map[$c['no_coil']];
                // Simpan ke database
                $this->db->update('tr_ros_material_coil', [
                    'status_qc' => $c['status_qc'],
                ], ['id' => (int) $c['id_ros_material_coil']]);
            }
        }
        unset($c);

        // Validasi COA
        $coa_map = ['produksi' => '1105-01-01', 'slitting' => '1105-01-02', 'intransit' => '1105-01-03'];
        $coa_check = $this->_validate_and_get_coa_names($coa_map);
        if (!$coa_check['valid']) {
            echo json_encode(['status' => 3, 'pesan' => 'COA belum terdaftar: ' . implode(', ', $coa_check['not_found'])]);
            return;
        }

        $this->db->trans_begin();

        $kode_incoming      = $this->Incoming_model->generate_id_incoming();
        $total_berat        = 0;
        $total_nilai_inc    = 0;
        $details_to_insert  = [];

        foreach ($coils as $c) {
            if ((float) $c['berat_bersih'] <= 0) continue;

            $berat_bersih    = (float) $c['berat_bersih'];
            $cost_book       = (float) $c['cost_book'];
            $price_per_coil  = (float) $c['price_per_coil'];
            $nilai_inventory = (float) $c['price_per_coil'];

            $details_to_insert[] = [
                'kode_trans'           => $kode_incoming,
                'id_ros_material_coil' => $c['id_ros_material_coil'],
                'id_ros_material'      => $c['id_ros_material'],
                'id_material'          => $c['id_material'],
                'nm_material'          => $c['nm_material'],
                'trade_name'           => $c['trade_name'],
                'no_coil'              => $c['no_coil'],
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
            ];

            // Semua coil masuk stok terlepas status QC (QC hanya penanda)
            $total_berat     += $berat_bersih;
            $total_nilai_inc += $price_per_coil;

            // Update warehouse
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
                $no_ros
            );

            // Update qty_in di dt_trans_po
            $this->db->set('qty_in', 'qty_in + ' . $berat_bersih, FALSE);
            $this->db->where('id', $c['id_po_detail']);
            $this->db->update('dt_trans_po');
        }

        // ── Susun summary per material per gudang ────────────────────────────────
        // $details_to_insert sudah terisi lengkap dari loop di atas
        $summary_map = [];
        foreach ($details_to_insert as $d) {
            if ((float)$d['berat_bersih'] <= 0) continue;

            $key = $d['id_material'] . '_' . $d['id_gudang_ke'];

            if (!isset($summary_map[$key])) {
                // Ambil snapshot qty_awal & saldo_awal dari warehouse_history
                // (data terakhir sebelum transaksi ini — ambil dari _update_stock_and_history yang sudah jalan)
                // Kita ambil row pertama coil material ini dari warehouse_history
                $first_hist = $this->db->query("
            SELECT saldo_awal, qty_stock_awal, harga_lama
            FROM warehouse_history
            WHERE no_ipp = ? AND id_material = ? AND id_gudang = ?
            ORDER BY id ASC LIMIT 1
            ", [$kode_incoming, $d['id_material'], $d['id_gudang_ke']])->row();

                // Ambil saldo_akhir & qty_akhir dari row terakhir
                $last_hist = $this->db->query("
            SELECT saldo_akhir, qty_stock_akhir, harga_baru
            FROM warehouse_history
            WHERE no_ipp = ? AND id_material = ? AND id_gudang = ?
            ORDER BY id DESC LIMIT 1
            ", [$kode_incoming, $d['id_material'], $d['id_gudang_ke']])->row();

                $summary_map[$key] = [
                    'no_ipp'        => $kode_incoming,
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
                'kode_trans'    => $kode_incoming,
                'id_material'   => $d['id_material'],
                'nm_material'   => $d['nm_material'],
                'id_gudang'     => $d['id_gudang_ke'],
                'kd_gudang'     => $d['kd_gudang_ke'],
                'no_coil'       => $d['no_coil'],
                'kode_internal' => $d['kode_internal'],
                'gross_weight'  => $d['berat_kotor'],
                'net_weight'    => $d['berat_bersih'],
                'length'        => $d['panjang'],
                'price_per_coil' => $d['price_per_coil'],
                'cost_book'     => $d['cost_book'],
                'status_qc'     => $d['status_qc'],
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        // Insert summary per material
        foreach ($summary_map as $s) {
            $this->db->insert('warehouse_stock_transaction_summary', $s);
        }

        // Insert header incoming baru
        $supplier_row = $this->db->get_where('new_supplier', ['kode_supplier' => $ros_header->id_supplier])->row();
        $nm_supplier  = $supplier_row ? $supplier_row->nama : '';

        $this->db->insert('tr_incoming_header', [
            'kode_trans'         => $kode_incoming,
            'no_ros'             => $no_ros,
            'no_po'              => $ros_header->no_po,
            'id_supplier'        => $ros_header->id_supplier,
            'nm_supplier'        => $nm_supplier,
            'tanggal'            => $tanggal,
            'total_berat_bersih' => $total_berat,
            'total_nilai'        => $total_nilai_inc,
            'file_dokumen'       => $ros_header->file_hash   ?? '',
            'file_original'      => $ros_header->file_original ?? '',
            'status'             => 'finalized',
            'created_by'         => $this->auth->user_id(),
            'created_at'         => date('Y-m-d H:i:s'),
            'finalized_by'       => $this->auth->user_id(),
            'finalized_at'       => date('Y-m-d H:i:s'),
        ]);

        // Insert detail incoming (batch)
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

        // GL Interface — setelah commit
        $jurnal_error = null;
        if ($total_nilai_inc > 0) {
            try {
                $this->_generate_jurnal_incoming(
                    $kode_incoming,
                    $ros_header->no_po,
                    $ros_header->no_surat,
                    $ros_header->id_supplier,
                    $no_ros,
                    $details_to_insert   // sudah berisi price_per_coil & kd_gudang_ke
                );
            } catch (Exception $e) {
                $jurnal_error = $e->getMessage();
                log_message('error', 'GL error incoming ' . $kode_incoming . ': ' . $jurnal_error);
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

    public function _generate_jurnal_incoming($kode_trans, $no_po, $no_surat, $id_supplier, $no_ros, array $details)
    {
        $tgl        = date('Y-m-d');
        $created_on = date('Y-m-d H:i:s');
        $user_id    = $this->auth->user_id();

        $coa_map   = ['produksi' => '1105-01-01', 'slitting' => '1105-01-02', 'intransit' => '1105-01-03'];
        $coa_check = $this->_validate_and_get_coa_names($coa_map);
        if (!$coa_check['valid']) {
            throw new Exception('COA not found: ' . implode(', ', $coa_check['not_found']));
        }
        $coa_names = $coa_check['names'];

        $supplier      = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row();
        $supplier_name = $supplier ? $supplier->nama : '-';
        $keterangan    = "Incoming PO: {$no_surat} | ROS: {$no_ros}";
        $nomor_jv      = $this->_generate_nomor_jv();

        // Hitung DEBET per gudang & KREDIT — keduanya dari price_per_coil
        $debet_per_gudang = [];
        $total_kredit     = 0;

        foreach ($details as $d) {
            if ($d['status_qc'] !== 'OK') continue;

            $kd         = strtoupper(trim($d['kd_gudang_ke']));
            $coa_gudang = ($kd === 'SLI') ? $coa_map['slitting'] : $coa_map['produksi'];

            if (!isset($debet_per_gudang[$coa_gudang])) {
                $debet_per_gudang[$coa_gudang] = [
                    'coa'    => $coa_gudang,
                    'nm_coa' => ($kd === 'SLI') ? $coa_names['slitting'] : $coa_names['produksi'],
                    'total'  => 0,
                ];
            }

            // Keduanya pakai price_per_coil → DEBET = KREDIT, selalu balance tanpa selisih
            $debet_per_gudang[$coa_gudang]['total'] += (float) $d['price_per_coil'];
            $total_kredit                           += (float) $d['price_per_coil'];
        }

        if ($total_kredit <= 0) return;

        // Insert header GL Interface
        $this->db->insert('gl_interface', [
            'nomor'           => $nomor_jv,
            'tgl'             => $tgl,
            'bulan'           => date('m'),
            'tahun'           => date('Y'),
            'kdcab'           => '101',
            'jenis'           => 'JV',
            'keterangan'      => $keterangan,
            'jenis_transaksi' => 'incoming',
            'status'          => 'pending',
            'user_id'         => $user_id,
            'memo'            => json_encode([
                'id_supplier'   => $id_supplier,
                'nama_supplier' => $supplier_name,
                'no_reff'       => $no_surat,
                'no_request'    => $kode_trans,
                'no_ros'        => $no_ros,
            ]),
        ]);
        $id_gl = $this->db->insert_id();

        $ins = function ($coa, $ket, $debet, $kredit)
        use ($id_gl, $tgl, $no_surat, $kode_trans, $created_on, $nomor_jv) {
            if (round($debet, 2) == 0 && round($kredit, 2) == 0) return;
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => $nomor_jv,
                'tipe'            => 'JV',
                'tanggal'         => $tgl,
                'no_perkiraan'    => $coa,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => null,
                'no_coil'         => null,
                'keterangan'      => $ket,
                'no_reff'         => $no_surat,
                'no_request'      => $kode_trans,
                'debet'           => round($debet,  2),
                'kredit'          => round($kredit, 2),
                'created_at'      => $created_on,
            ]);
        };

        // DEBET: Persediaan per gudang (1 baris per COA gudang)
        foreach ($debet_per_gudang as $entry) {
            $ins(
                $entry['coa'],
                $entry['nm_coa'] . " | {$keterangan}",
                $entry['total'],
                0
            );
        }

        // KREDIT: Persediaan In-Transit (balik jurnal ROS)
        $ins(
            $coa_map['intransit'],
            $coa_names['intransit'] . " | Balik {$keterangan}",
            0,
            $total_kredit
        );
    }

    // private function _generate_jurnal_and_debt(
    //     $kode_trans,
    //     $no_po,
    //     $total_rp,
    //     $id_supplier,
    //     $materials = [],
    //     $uang_muka_idr = 0,
    //     $uang_muka_usd = 0,
    //     $no_ros = '',
    //     $id_gudang_ke = null
    // ) {
    //     $tgl_inv    = date('Y-m-d');
    //     $user_id    = $this->auth->user_id();
    //     $created_on = date('Y-m-d H:i:s');

    //     $supplier      = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row();
    //     $supplier_name = $supplier ? $supplier->nama : '-';

    //     $keterangan = "Incoming PO: {$no_po} | ROS: {$no_ros}";
    //     $nomor_jv   = $this->_generate_nomor_jv();

    //     $coa_list = [
    //         'produksi'  => '1105-01-01',
    //         'slitting'  => '1105-01-02',
    //         'intransit' => '1105-01-03',
    //     ];

    //     // ── Ambil & validasi nama COA dari DBACC ──
    //     $coa_check = $this->_validate_and_get_coa_names($coa_list);
    //     if (!$coa_check['valid']) {
    //         throw new Exception('COA tidak ditemukan di Master: ' . implode(', ', $coa_check['not_found']));
    //     }
    //     $coa_names = $coa_check['names'];

    //     // ── Hitung total per kelompok gudang ──
    //     $total_produksi     = 0;
    //     $total_slitting = 0;

    //     foreach ($materials as $mat) {
    //         $kd_gudang = strtoupper(trim($mat['kd_gudang_ke'] ?? 'PRO'));
    //         $nilai     = (float) $mat['total_persediaan'];

    //         if ($kd_gudang === 'SLI') {
    //             $total_slitting += $nilai;
    //         } else {
    //             $total_produksi += $nilai;
    //         }
    //     }

    //     $total_debet = $total_produksi + $total_slitting;
    //     if ($total_debet <= 0) return;

    //     $this->db->trans_begin();

    //     // ── Insert Header GL Interface ──
    //     $this->db->insert('gl_interface', [
    //         'nomor'           => $nomor_jv,
    //         'tgl'             => $tgl_inv,
    //         'bulan'           => date('m'),
    //         'tahun'           => date('Y'),
    //         'kdcab'           => '101',
    //         'jenis'           => 'JV',
    //         'keterangan'      => $keterangan,
    //         'jenis_transaksi' => 'incoming',
    //         'status'          => 'pending',
    //         'user_id'         => $user_id,
    //         'memo'            => json_encode([
    //             'id_supplier'   => $id_supplier,
    //             'nama_supplier' => $supplier_name,
    //             'no_reff'       => $no_po,
    //             'no_request'    => $kode_trans,
    //         ]),
    //     ]);
    //     $id_gl = $this->db->insert_id();

    //     // ── DEBET 1: Persediaan Bahan Baku Produksi ──
    //     if ($total_produksi > 0) {
    //         $this->db->insert('gl_interface_detail', [
    //             'id_gl_interface' => $id_gl,
    //             'no_batch'        => $nomor_jv,
    //             'tipe'            => 'JV',
    //             'tanggal'         => $tgl_inv,
    //             'no_perkiraan'    => $coa_list['produksi'],
    //             'id_material'     => null,
    //             'nm_material'     => null,
    //             'id_gudang'       => $id_gudang_ke,
    //             'no_coil'         => null,
    //             'keterangan'      => $keterangan . " | " . $coa_names['produksi'],
    //             'no_reff'         => $no_po,
    //             'no_request'      => $kode_trans,
    //             'debet'           => round($total_produksi, 2),
    //             'kredit'          => 0,
    //             'created_at'      => $created_on,
    //         ]);
    //     }

    //     // ── DEBET 2: Persediaan Bahan Baku Intransit Slitting ──
    //     if ($total_slitting > 0) {
    //         $this->db->insert('gl_interface_detail', [
    //             'id_gl_interface' => $id_gl,
    //             'no_batch'        => $nomor_jv,
    //             'tipe'            => 'JV',
    //             'tanggal'         => $tgl_inv,
    //             'no_perkiraan'    => $coa_list['slitting'],
    //             'id_material'     => null,
    //             'nm_material'     => null,
    //             'id_gudang'       => $id_gudang_ke,
    //             'no_coil'         => null,
    //             'keterangan'      => $keterangan . " | " . $coa_names['slitting'],
    //             'no_reff'         => $no_po,
    //             'no_request'      => $kode_trans,
    //             'debet'           => round($total_slitting, 2),
    //             'kredit'          => 0,
    //             'created_at'      => $created_on,
    //         ]);
    //     }

    //     // ── KREDIT: Persediaan In Transit ──
    //     $this->db->insert('gl_interface_detail', [
    //         'id_gl_interface' => $id_gl,
    //         'no_batch'        => $nomor_jv,
    //         'tipe'            => 'JV',
    //         'tanggal'         => $tgl_inv,
    //         'no_perkiraan'    => $coa_list['intransit'],
    //         'id_material'     => null,
    //         'nm_material'     => null,
    //         'id_gudang'       => $id_gudang_ke,
    //         'no_coil'         => null,
    //         'keterangan'      => $keterangan . " | Balik " . $coa_names['intransit'],
    //         'no_reff'         => $no_po,
    //         'no_request'      => $kode_trans,
    //         'debet'           => 0,
    //         'kredit'          => round($total_debet, 2),
    //         'created_at'      => $created_on,
    //     ]);

    //     if ($this->db->trans_status() === FALSE) {
    //         $this->db->trans_rollback();
    //         throw new Exception("Gagal membuat Jurnal Incoming.");
    //     }

    //     $this->db->trans_commit();
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
        $no_ros = ''
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
        $saldo_awal    = $get_stock ? (int) round($get_stock->total_nilai) : 0;
        $incoming_lama = $get_stock ? (float) $get_stock->incoming    : 0;

        // ── 4. Hitung moving average ──────────────────────────────────────────
        $nilai_baru        = (int) round($qty_in * $price_unit_idr);
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
                'harga_beli'      => $costbook,
                'total_nilai'     => $total_nilai_akhir,
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
                'harga_beli'      => $costbook,
                'total_nilai'     => $total_nilai_akhir,
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
            'harga_beli'  => $costbook,
            'total_nilai' => $total_nilai_akhir,
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
        // Satu record per coil per hari; status 'IN' karena proses incoming.
        // Jika pada hari yang sama coil sama dikirim ulang (koreksi), UPDATE saja.
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
                'id_material'   => $id_material,
                'no_coil'       => $no_coil,
                'kode_internal' => $kode_internal,
                'nm_material'   => $nm_material,
                'trade_name'    => $trade_name,
                'gross_weight'  => $berat_kotor,
                'net_weight'    => $berat_bersih,
                'length'        => $panjang,
                'id_gudang'     => $id_gudang,
                'kd_gudang'     => $kd_gudang,
                'no_ipp'        => $kode_trans,
                'no_ros'        => $no_ros,
            ]);
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
            'harga_beli'      => (int) round($price_unit_idr),
            'total_harga'     => $nilai_baru,
            'saldo_awal'      => $saldo_awal,
            'saldo_akhir'     => $total_nilai_akhir,
            'harga_baru'      => (int) round($costbook),
            'harga_lama'      => (int) round($harga_lama),
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
            'harga_stok'       => (int) round($costbook),
            'status_transaksi' => 'in',
            'created_by'       => $user_id,
            'created_on'       => $now,
        ]);
    }

    // PRINT QR — per pack (barcode = pack_code)
    public function print_qr($no_ros)
    {
        // Ambil semua pack untuk ROS ini
        $packs = $this->db->query("
            SELECT p.id, p.pack_no, p.pack_code, p.id_ros,
                   h.id AS no_ros, h.nm_supplier
            FROM tr_ros_pack p
            JOIN tr_ros_header h ON h.id = p.id_ros
            WHERE p.id_ros = ?
            ORDER BY p.pack_no ASC
        ", [$no_ros])->result_array();

        // Fallback: jika belum ada pack data, group coils by id_ros_pack dari material_coil
        if (empty($packs)) {
            // Coba ambil pack dari coils yang punya id_ros_pack
            $packs = $this->db->query("
                SELECT DISTINCT p.id, p.pack_no, p.pack_code, p.id_ros,
                       h.id AS no_ros, h.nm_supplier
                FROM tr_ros_material_coil c
                JOIN tr_ros_material m ON m.id = c.id_ros_material
                JOIN tr_ros_header h ON h.id = m.id_ros
                JOIN tr_ros_pack p ON p.id = c.id_ros_pack
                WHERE m.id_ros = ?
                ORDER BY p.pack_no ASC
            ", [$no_ros])->result_array();
        }

        // Jika masih kosong, coba generate 1 virtual pack dari semua coils
        if (empty($packs)) {
            $coils_exist = $this->db->query("
                SELECT COUNT(*) as cnt FROM tr_ros_material_coil c
                JOIN tr_ros_material m ON m.id = c.id_ros_material
                WHERE m.id_ros = ?
            ", [$no_ros])->row();

            if ($coils_exist && (int) $coils_exist->cnt > 0) {
                $packs = [[
                    'id'        => 0,
                    'pack_no'   => 1,
                    'pack_code' => $no_ros,
                    'id_ros'    => $no_ros,
                    'no_ros'    => $no_ros,
                    'nm_supplier' => '',
                    '_virtual'  => true,
                ]];
            }
        }

        if (empty($packs)) {
            die("No pack data found for this ROS.");
        }

        // Untuk setiap pack, ambil coils dan hitung total
        $results = [];
        foreach ($packs as $pack) {
            $is_virtual = isset($pack['_virtual']) && $pack['_virtual'];

            if ($is_virtual) {
                // Virtual pack: ambil semua coils
                $coils = $this->db->query("
                    SELECT c.no_coil, c.berat_bersih, c.berat_kotor, c.is_baby_coil, c.qty_roll,
                           c.id_gudang_ke, c.kd_gudang_ke,
                           m.nm_alias, m.nm_erp, m.nm_barang, m.id_barang,
                           w.nm_gudang AS nm_gudang_tujuan,
                           i4.thickness
                    FROM tr_ros_material_coil c
                    JOIN tr_ros_material m ON m.id = c.id_ros_material
                    LEFT JOIN warehouse w ON w.id = c.id_gudang_ke
                    LEFT JOIN new_inventory_4 i4 ON i4.code_lv4 = m.id_barang
                    WHERE m.id_ros = ?
                    ORDER BY m.nm_alias ASC, c.no_coil ASC
                ", [$no_ros])->result_array();
            } else {
                $coils = $this->db->query("
                    SELECT c.no_coil, c.berat_bersih, c.berat_kotor, c.is_baby_coil, c.qty_roll,
                           c.id_gudang_ke, c.kd_gudang_ke,
                           m.nm_alias, m.nm_erp, m.nm_barang, m.id_barang,
                           w.nm_gudang AS nm_gudang_tujuan,
                           i4.thickness
                    FROM tr_ros_material_coil c
                    JOIN tr_ros_material m ON m.id = c.id_ros_material
                    LEFT JOIN warehouse w ON w.id = c.id_gudang_ke
                    LEFT JOIN new_inventory_4 i4 ON i4.code_lv4 = m.id_barang
                    WHERE c.id_ros_pack = ?
                    ORDER BY m.nm_alias ASC, c.no_coil ASC
                ", [$pack['id']])->result_array();
            }

            // Hitung total (skip mother with baby)
            $total_nw = 0;
            $total_gw = 0;
            $materials = [];
            $thicknesses = [];
            $mat_thickness_collected = []; // track material yang sudah diambil thickness-nya
            foreach ($coils as $c) {
                $is_mother_with_baby = ((int) $c['is_baby_coil'] === 0 && (int) $c['qty_roll'] > 1);
                if (!$is_mother_with_baby) {
                    $total_nw += (float) $c['berat_bersih'];
                    $total_gw += (float) $c['berat_kotor'];
                }
                $mat_name = $c['nm_alias'] ?: $c['nm_erp'];
                if ($mat_name && !in_array($mat_name, $materials)) {
                    $materials[] = $mat_name;
                }
                // Collect thickness per material (1 thickness per material, bukan unique)
                $mat_id = $c['id_barang'] ?? '';
                if (!empty($c['thickness']) && $mat_id && !isset($mat_thickness_collected[$mat_id])) {
                    $thicknesses[] = $c['thickness'];
                    $mat_thickness_collected[$mat_id] = true;
                }
            }

            // Gudang dari coil pertama
            $gudang_name = '';
            $kd_gudang = '';
            $id_gudang = null;
            foreach ($coils as $c) {
                if (!empty($c['id_gudang_ke'])) {
                    $gudang_name = $c['nm_gudang_tujuan'] ?: '';
                    $kd_gudang = $c['kd_gudang_ke'] ?: '';
                    $id_gudang = $c['id_gudang_ke'];
                    break;
                }
            }

            $results[] = [
                'pack_code'        => $pack['pack_code'],
                'pack_no'          => $pack['pack_no'],
                'no_ros'           => $pack['no_ros'],
                'materials'        => $materials,
                'thicknesses'      => $thicknesses,
                'total_nw'         => $total_nw,
                'total_gw'         => $total_gw,
                'nm_gudang_tujuan' => $gudang_name,
                'kd_gudang_ke'     => $kd_gudang,
                'id_gudang_ke'     => $id_gudang,
            ];
        }

        $data = ['results' => $results];
        $this->load->view('print_qr_label', $data);
    }

    // AJAX helpers (tidak berubah)
    public function get_po_by_supplier()
    {
        $id_supplier = $this->input->post('id_supplier');
        $data = $this->db->query("
            SELECT no_po, no_surat, uang_muka, uang_muka_idr
            FROM tr_purchase_order
            WHERE id_suplier = ? AND status = '2'
            ORDER BY no_po DESC
        ", [$id_supplier])->result();
        echo json_encode($data);
    }

    public function get_ros_by_po_select()
    {
        $no_po = $this->input->post('no_po');
        $data = $this->db->query("
            SELECT DISTINCT a.id AS no_ros
            FROM tr_ros_header a
            JOIN tr_ros_material b ON b.id_ros = a.id
            JOIN dt_trans_po c     ON c.id = b.id_po_detail
            WHERE c.no_po = ?
              AND a.status = '0'
              AND (a.status_incoming = 'open' OR a.status_incoming IS NULL)
            ORDER BY a.id ASC
        ", [$no_po])->result();
        echo json_encode($data);
    }

    public function get_ros_detail_to_table()
    {
        $no_ros = $this->input->post('no_ros');

        // Query semua coil dengan info pack
        $query = "
            SELECT c.id AS id_ros_coil_detail, c.no_coil,
                c.berat_kotor AS ros_kotor, c.berat_bersih AS ros_bersih, c.panjang,
                c.id_gudang_ke, c.kd_gudang_ke, c.status_qc,
                c.is_baby_coil, c.qty_roll, c.id_ros_pack,
                b.id AS id_ros_material, b.nm_erp AS nm_material, b.nm_alias,
                b.id_barang AS id_material, b.unit_price_usd AS price_coil,
                b.total_value_rp AS price_coil_idr, b.bm_rp, b.forwarding_cost,
                d.qty AS qty_po, d.qty_in, d.id AS id_po_detail, b.id_ros AS no_ros,
                p.pack_no, p.pack_code
            FROM tr_ros_material b
            JOIN tr_ros_material_coil c ON b.id = c.id_ros_material
            LEFT JOIN dt_trans_po d     ON b.id_po_detail = d.id
            LEFT JOIN tr_ros_pack p     ON p.id = c.id_ros_pack
            WHERE b.id_ros = ?
            ORDER BY p.pack_no ASC, b.id_barang ASC, c.no_coil ASC
        ";
        $rows = $this->db->query($query, [$no_ros])->result_array();

        // Group by pack
        $packs = [];
        foreach ($rows as $row) {
            $pack_id = $row['id_ros_pack'] ?: 0;

            if (!isset($packs[$pack_id])) {
                $packs[$pack_id] = [
                    'id_ros_pack' => $row['id_ros_pack'],
                    'pack_no'     => $row['pack_no'],
                    'pack_code'   => $row['pack_code'],
                    'total_nw'    => 0,
                    'total_gw'    => 0,
                    'coil_count'  => 0,
                    'materials'   => [],
                    'coils'       => [],
                    // Gudang dari coil pertama (semua coil dalam pack seharusnya sama)
                    'id_gudang_ke' => $row['id_gudang_ke'],
                    'kd_gudang_ke' => $row['kd_gudang_ke'],
                ];
            }

            // Skip mother coil dari hitungan (hanya tampilkan baby/normal)
            $is_mother_with_baby = ((int) $row['is_baby_coil'] === 0 && (int) $row['qty_roll'] > 1);
            if (!$is_mother_with_baby) {
                $packs[$pack_id]['total_nw'] += (float) $row['ros_bersih'];
                $packs[$pack_id]['total_gw'] += (float) $row['ros_kotor'];
                $packs[$pack_id]['coil_count']++;
            }

            // Collect material names (unique)
            $mat_key = $row['id_material'];
            if (!isset($packs[$pack_id]['materials'][$mat_key])) {
                $packs[$pack_id]['materials'][$mat_key] = [
                    'nm_alias'    => $row['nm_alias'],
                    'nm_material' => $row['nm_material'],
                    'id_material' => $row['id_material'],
                    'qty_po'      => $row['qty_po'],
                    'qty_in'      => $row['qty_in'],
                    'id_po_detail' => $row['id_po_detail'],
                ];
            }

            // Collect all coils for this pack (for hidden inputs)
            $packs[$pack_id]['coils'][] = $row;
        }

        // Convert to indexed array
        $result = array_values($packs);

        echo json_encode($result);
    }

    // PROSES INCOMING COIL (SAVE)
    public function process_incoming_coil()
    {
        $post     = $this->input->post();
        $dateTime = date('Y-m-d H:i:s');

        // Validasi gudang per baris
        if (!empty($post['detail'])) {
            foreach ($post['detail'] as $val) {
                if (empty($val['id_gudang_ke'])) {
                    echo json_encode(['status' => 0, 'pesan' => 'Semua coil harus dipilih gudang tujuannya!']);
                    return;
                }
            }
        }

        $this->db->trans_begin();

        $kode_incoming      = $this->Incoming_model->generate_id_incoming();
        $link               = $this->_upload_incoming_files('file_incoming_material');
        $total_harga_check  = 0;
        $total_berat_check  = 0;
        $list_ros           = [];
        $materials_incoming = [];
        $id_gudang_ke       = null;

        foreach ($post['detail'] as $val) {
            $aktual_bersih = (float) str_replace(',', '', $val['aktual_bersih'] ?? 0);
            if ($aktual_bersih <= 0) continue;

            $id_material     = $val['id_material'];
            $id_po_detail    = $val['id_po_detail'];
            $id_ros_material = $val['id_ros_material'];
            $id_ros_coil     = $val['id_ros_coil'];

            // Data material dari inventory
            $get_mat = $this->db
                ->select('a.*, d.nama as unit, e.nama as packing')
                ->from('new_inventory_4 a')
                ->join('ms_satuan d', 'a.id_unit = d.id', 'left')
                ->join('ms_satuan e', 'a.id_unit_packing = e.id', 'left')
                ->where('a.code_lv4', $id_material)
                ->get()->row();

            $get_po       = $this->db->get_where('dt_trans_po',          ['id' => $id_po_detail])->row();
            $get_ros_mat  = $this->db->get_where('tr_ros_material',       ['id' => $id_ros_material])->row();
            $get_ros_coil = $this->db->get_where('tr_ros_material_coil',  ['id' => $id_ros_coil])->row();

            if (empty($get_mat) || empty($get_po) || empty($get_ros_mat) || empty($get_ros_coil)) continue;

            // Harga per kg: gunakan cost_book jika ada
            $price_per_kg = !empty($get_ros_mat->cost_book)
                ? (float) $get_ros_mat->cost_book
                : (($get_ros_coil->berat_bersih > 0)
                    ? ((float) $get_ros_mat->total_nilai_inventory / (float) $get_ros_coil->berat_bersih)
                    : 0);

            // Insert detail incoming
            $this->db->insert('tr_incoming_check_detail', [
                'kode_trans'      => $kode_incoming,
                'id_po_detail'    => $id_po_detail,
                'no_ipp'          => $post['no_po'],
                'id_material_req' => $id_material,
                'id_material'     => $id_material,
                'nm_material'     => $get_mat->nama,
                'qty_order'       => $get_po->qty,
                'harga'           => $price_per_kg,
                'keterangan'      => "Coil Nomor: " . $val['no_coil'],
            ]);
            $id_detail_inc = $this->db->insert_id();

            // Insert detail QC
            $this->db->insert('tr_checked_incoming_detail', [
                'kode_trans'   => $kode_incoming,
                'id_detail'    => $id_detail_inc,
                'id_material'  => $id_material,
                'nm_material'  => $get_mat->nama,
                'unit'         => $get_mat->unit,
                'packing'      => $get_mat->packing,
                'no_ipp'       => $post['no_po'],
                'qty_order'    => $get_po->qty,
                'qty_incoming' => ($val['status_qc'] == 'OK') ? $aktual_bersih : 0,
                'qty_oke'      => ($val['status_qc'] == 'OK') ? $aktual_bersih : 0,
                'qty_ng'       => ($val['status_qc'] == 'REJECT') ? $aktual_bersih : 0,
                'sts'          => '1',
                'harga'        => $price_per_kg,
                'total_harga'  => $aktual_bersih * $price_per_kg,
                'status_qc'    => $val['status_qc'],
            ]);

            if ($val['status_qc'] == 'OK') {
                $id_gudang_ke = (int) $val['id_gudang_ke'];
                $gd           = $this->db->get_where('warehouse', ['id' => $id_gudang_ke])->row();
                $kd_gudang_ke = $gd ? $gd->kd_gudang : 'PRO';

                $nilai_coil = (int) round($aktual_bersih * $price_per_kg, 0);

                $this->_update_stock_and_history(
                    $id_material,
                    $get_mat->nama,
                    $aktual_bersih,
                    $price_per_kg,
                    $kode_incoming,
                    $post['no_po'],
                    $val['no_coil'],
                    $id_gudang_ke,
                    $kd_gudang_ke,
                    $post['no_ros']
                );

                // Update qty_in di dt_trans_po
                $this->db->set('qty_in', 'qty_in + ' . (float) $aktual_bersih, FALSE);
                $this->db->where('id', $id_po_detail);
                $this->db->update('dt_trans_po');

                $total_harga_check += $nilai_coil;
                $total_berat_check += $aktual_bersih;

                $materials_incoming[] = [
                    'id_material'      => $id_material,
                    'nm_material'      => $get_mat->nama,
                    'qty'              => $aktual_bersih,
                    'harga'            => $price_per_kg,
                    'total_persediaan' => $nilai_coil,
                    'biaya_masuk'      => (float) ($get_ros_mat->bm_rp ?? 0),
                    'forwarding'       => (float) ($get_ros_mat->forwarding_cost ?? 0),
                    'price_coil_usd'   => (float) ($get_ros_mat->unit_price_usd ?? 0),
                    'price_coil_idr'   => (float) ($get_ros_mat->total_value_rp ?? 0),
                    'no_coil'          => $val['no_coil'],
                    'id_gudang_ke'     => $id_gudang_ke,
                    'kd_gudang_ke'     => $kd_gudang_ke,
                ];
            }

            if (!empty($val['id_ros_header'])) {
                $list_ros[] = $val['id_ros_header'];
            }
        }

        // Insert header incoming
        $this->db->insert('tr_incoming_check', [
            'kode_trans'             => $kode_incoming,
            'tanggal'                => $post['tanggal'],
            'no_ipp'                 => $post['no_po'],
            'no_ros'                 => $post['no_ros'],
            'category'               => 'incoming material',
            'jumlah_mat'             => $total_berat_check,
            'id_gudang_dari'         => 1,
            'kd_gudang_dari'         => 'PRO',
            'id_gudang_ke'           => null,
            'kd_gudang_ke'           => 'MULTI',
            'checked'                => 'Y',
            'file_incoming_material' => $link,
            'created_by'             => $this->auth->user_id(),
            'created_date'           => $dateTime,
        ]);

        // Update status ROS → '1' (Closed) di tabel baru
        if (!empty($list_ros)) {
            $this->db->where_in('id', array_unique($list_ros));
            $this->db->update('tr_ros_header', ['status' => '1']);
        }

        // Siapkan parameter jurnal
        $jurnal_params = null;
        if ($total_harga_check > 0) {
            $jurnal_params = [
                'kode_incoming' => $kode_incoming,
                'no_po'         => $post['no_po'],
                'total'         => $total_harga_check,
                'id_supplier'   => $post['id_supplier'],
                'materials'     => $materials_incoming,
                'uang_muka_idr' => (float) str_replace(',', '', $post['uang_muka_idr']),
                'uang_muka_usd' => (float) str_replace(',', '', $post['uang_muka']),
                'no_ros'        => $post['no_ros'],
                'id_gudang_ke'  => $id_gudang_ke,
            ];
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Failed to save transaction data!']);
            return;
        }
        $this->db->trans_commit();

        // Jurnal dijalankan setelah commit (error jurnal tidak rollback stok)
        $jurnal_error = null;
        if ($jurnal_params !== null) {
            try {
                $this->_generate_jurnal_and_debt(
                    $jurnal_params['kode_incoming'],
                    $jurnal_params['no_po'],
                    $jurnal_params['total'],
                    $jurnal_params['id_supplier'],
                    $jurnal_params['materials'],
                    $jurnal_params['uang_muka_idr'],
                    $jurnal_params['uang_muka_usd'],
                    $jurnal_params['no_ros'],
                    $jurnal_params['id_gudang_ke']
                );
            } catch (Exception $e) {
                $jurnal_error = $e->getMessage();
                log_message('error', 'Jurnal error untuk ' . $jurnal_params['kode_incoming'] . ': ' . $jurnal_error);
            }
        }

        if ($jurnal_error) {
            echo json_encode(['status' => 2, 'pesan' => 'Transaction data saved successfully, but accounting journal failed. Please repost via GL Interface menu.']);
        } else {
            echo json_encode(['status' => 1, 'pesan' => 'Success! Stock, payables, and journal have been processed.']);
        }
    }

    // UPDATE STOK & HISTORY
    // private function _update_stock_and_history($id_material, $nm_material, $qty_in, $price_unit_idr, $kode_trans, $no_po, $no_coil, $id_gudang = 1, $kd_gudang = 'PRO', $no_ros = '')
    // {
    //     $get_stock = $this->db->query(
    //         "SELECT * FROM warehouse_stock WHERE id_material = ? AND id_gudang = ? LIMIT 1 FOR UPDATE",
    //         [$id_material, $id_gudang]
    //     )->row();

    //     $qty_awal      = !empty($get_stock) ? (float) $get_stock->qty_stock   : 0;
    //     $harga_lama    = !empty($get_stock) ? (float) $get_stock->harga_beli  : 0;
    //     $qty_book_awal = !empty($get_stock) ? (float) $get_stock->qty_booking : 0;
    //     $qty_free_awal = !empty($get_stock) ? (float) $get_stock->qty_free    : 0;
    //     $saldo_awal    = !empty($get_stock) ? (int) round($get_stock->total_nilai) : 0;

    //     $nilai_baru        = (int) round($qty_in * $price_unit_idr);
    //     $qty_akhir         = $qty_awal + $qty_in;
    //     $total_nilai_akhir = (int) round($saldo_awal + $nilai_baru);
    //     $costbook          = ($qty_akhir > 0) ? $total_nilai_akhir / $qty_akhir : $price_unit_idr;

    //     if (empty($get_stock)) {
    //         $this->db->insert('warehouse_stock', [
    //             'id_material' => $id_material,
    //             'code_lv4'    => $id_material,
    //             'nm_material' => $nm_material,
    //             'id_gudang'   => $id_gudang,
    //             'kd_gudang'   => $kd_gudang,
    //             'incoming'    => $qty_in,
    //             'qty_booking' => 0,
    //             'qty_stock'   => $qty_akhir,
    //             'qty_free'    => $qty_akhir,
    //             'harga_beli'  => $costbook,
    //             'total_nilai' => $total_nilai_akhir,
    //             'update_by'   => $this->auth->user_id(),
    //             'update_date' => date('Y-m-d H:i:s'),
    //         ]);
    //     } else {
    //         $this->db->update('warehouse_stock', [
    //             'incoming'    => $qty_in,
    //             'qty_stock'   => $qty_akhir,
    //             'qty_booking' => $qty_book_awal,
    //             'qty_free'    => $qty_free_awal + $qty_in,
    //             'harga_beli'  => $costbook,
    //             'total_nilai' => $total_nilai_akhir,
    //             'update_by'   => $this->auth->user_id(),
    //             'update_date' => date('Y-m-d H:i:s'),
    //         ], ['id' => $get_stock->id]);
    //     }

    //     $this->db->insert('warehouse_history', [
    //         'id_material'     => $id_material,
    //         'nm_material'     => $nm_material,
    //         'id_gudang'       => $id_gudang,
    //         'kd_gudang'       => $kd_gudang,
    //         'id_gudang_dari'  => $id_gudang,
    //         'kd_gudang_dari'  => $kd_gudang,
    //         'id_gudang_ke'    => $id_gudang,
    //         'kd_gudang_ke'    => $kd_gudang,
    //         'qty_stock_awal'  => $qty_awal,
    //         'qty_stock_akhir' => $qty_akhir,
    //         'no_ipp'          => $kode_trans,
    //         'jumlah_mat'      => $qty_in,
    //         'ket'             => 'QC Incoming Coil Check (Coil NO: ' . $no_coil . ' , PO: ' . $no_po . ')',
    //         'no_coil'         => $no_coil,
    //         'harga_beli'      => (int) round($price_unit_idr),
    //         'total_harga'     => $nilai_baru,
    //         'saldo_awal'      => $saldo_awal,
    //         'saldo_akhir'     => $total_nilai_akhir,
    //         'harga_baru'      => $costbook,
    //         'harga_lama'      => $harga_lama,
    //         'update_by'       => $this->auth->user_id(),
    //         'update_date'     => date('Y-m-d H:i:s'),
    //     ]);

    //     $this->db->insert('kartu_stok', [
    //         'no_transaksi'     => $kode_trans,
    //         'id_gudang'        => $id_gudang,
    //         'transaksi'        => 'Incoming Material',
    //         'tgl_transaksi'    => date('Y-m-d H:i:s'),
    //         'code_lv4'         => $id_material,
    //         'code_material'    => $id_material,
    //         'nm_material'      => $nm_material,
    //         'qty'              => $qty_awal,
    //         'qty_book'         => $qty_book_awal,
    //         'qty_free'         => $qty_free_awal,
    //         'qty_akhir'        => $qty_akhir,
    //         'qty_transaksi'    => $qty_in,
    //         'qty_book_akhir'   => $qty_book_awal,
    //         'qty_free_akhir'   => $qty_free_awal + $qty_in,
    //         'harga_stok'       => $costbook,
    //         'status_transaksi' => 'in',
    //         'created_by'       => $this->auth->user_id(),
    //         'created_on'       => date('Y-m-d H:i:s'),
    //     ]);

    //     $this->db->insert('warehouse_stock_coil', [
    //         'id_material' => $id_material,
    //         'nm_material' => $nm_material,
    //         'id_gudang'   => $id_gudang,
    //         'kd_gudang'   => $kd_gudang,
    //         'no_coil'     => $no_coil,
    //         'no_ipp'      => $kode_trans,
    //         'no_po'       => $no_po,
    //         'no_ros'      => $no_ros,
    //         'qty'         => $qty_in,
    //         'harga_beli'  => (int) round($price_unit_idr),
    //         'total_nilai' => $nilai_baru,
    //         'status'      => 1,
    //         'created_by'  => $this->auth->user_id(),
    //         'created_on'  => date('Y-m-d H:i:s'),
    //     ]);
    // }


    // GENERATE JURNAL & HUTANG (GL Interface)
    // private function _generate_jurnal_and_debt(
    //     $kode_trans,
    //     $no_po,
    //     $total_rp,
    //     $id_supplier,
    //     $materials = [],
    //     $uang_muka_idr = 0,
    //     $uang_muka_usd = 0,
    //     $no_ros = '',
    //     $id_gudang_ke = null
    // ) {
    //     $tgl_inv       = date('Y-m-d');
    //     $user_id       = $this->auth->user_id();
    //     $created_on    = date('Y-m-d H:i:s');
    //     $supplier_name = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row()->nama;
    //     $keterangan    = "Incoming PO: {$no_po} | ROS: {$no_ros}";

    //     // COA
    //     $coa_in_transit     = '1105-01-02'; // PERSEDIAAN IN TRANSIT  → KREDIT
    //     // Gudang produksi → 1105-01-01, Gudang jualan → 1105-01-02
    //     $coa_gudang_map = [
    //         'PRO' => '1105-01-01', // PERSEDIAAN BAHAN BAKU
    //         'SLI' => '1105-01-02', // PERSEDIAAN BAHAN BAKU
    //     ];
    //     $coa_gudang_default = '1105-01-01';

    //     $nomor_jv = $this->_generate_nomor_jv();

    //     // ── Insert header GL Interface ──
    //     $this->db->insert('gl_interface', [
    //         'nomor'           => $nomor_jv,
    //         'tgl'             => $tgl_inv,
    //         'bulan'           => date('m'),
    //         'tahun'           => date('Y'),
    //         'kdcab'           => '101',
    //         'jenis'           => 'JV',
    //         'keterangan'      => $keterangan,
    //         'jenis_transaksi' => 'incoming',
    //         'status'          => 'pending',
    //         'user_id'         => $user_id,
    //         'memo'            => json_encode([
    //             'id_supplier'   => $id_supplier,
    //             'nama_supplier' => $supplier_name,
    //             'no_reff'       => $no_po,
    //             'no_request'    => $kode_trans,
    //         ]),
    //     ]);
    //     $id_gl = $this->db->insert_id();

    //     // ── DEBET: Persediaan Gudang per material/coil ──
    //     // Mapping COA berdasarkan kd_gudang tujuan
    //     foreach ($materials as $mat) {
    //         $kd_gudang      = $mat['kd_gudang_ke'] ?? '';
    //         $coa_persediaan = $coa_gudang_map[$kd_gudang] ?? $coa_gudang_default;

    //         $this->db->insert('gl_interface_detail', [
    //             'id_gl_interface' => $id_gl,
    //             'no_batch'        => null,
    //             'tipe'            => 'JV',
    //             'tanggal'         => $tgl_inv,
    //             'no_perkiraan'    => $coa_persediaan,
    //             'id_material'     => $mat['id_material'],
    //             'nm_material'     => $mat['nm_material'],
    //             'id_gudang'       => $mat['id_gudang_ke'] ?? null,
    //             'no_coil'         => $mat['no_coil'] ?? null,
    //             'keterangan'      => $keterangan . ' | ' . $mat['nm_material'] . ' (Coil: ' . ($mat['no_coil'] ?? '-') . ')',
    //             'no_reff'         => $no_po,
    //             'no_request'      => $kode_trans,
    //             'debet'           => $mat['total_persediaan'],
    //             'kredit'          => 0,
    //             'created_at'      => $created_on,
    //         ]);
    //     }

    //     // ── KREDIT: Persediaan In Transit (total semua material) ──
    //     $this->db->insert('gl_interface_detail', [
    //         'id_gl_interface' => $id_gl,
    //         'no_batch'        => null,
    //         'tipe'            => 'JV',
    //         'tanggal'         => $tgl_inv,
    //         'no_perkiraan'    => $coa_in_transit,
    //         'id_material'     => null,
    //         'nm_material'     => null,
    //         'id_gudang'       => null,
    //         'no_coil'         => null,
    //         'keterangan'      => $keterangan . ' | Persediaan In Transit',
    //         'no_reff'         => $no_po,
    //         'no_request'      => $kode_trans,
    //         'debet'           => 0,
    //         'kredit'          => $total_rp,
    //         'created_at'      => $created_on,
    //     ]);
    // }

    // POST GL INTERFACE (step 2 - posting ke accounting)
    private function _post_gl_interface($nomor_jv)
    {
        $header = $this->db->get_where('gl_interface', ['nomor' => $nomor_jv, 'status' => 'pending'])->row_array();
        if (empty($header)) return;

        $memo          = !empty($header['memo']) ? json_decode($header['memo'], true) : [];
        $id_supplier   = $memo['id_supplier']   ?? null;
        $nama_supplier = $memo['nama_supplier'] ?? null;

        $details = $this->db->get_where('gl_interface_detail', ['id_gl_interface' => $header['id']])->result_array();
        if (empty($details)) return;

        // Balance debet vs kredit, selisih diadjust ke unbill
        $total_debet  = array_sum(array_column($details, 'debet'));
        $total_kredit = array_sum(array_column($details, 'kredit'));
        $selisih      = round($total_debet) - round($total_kredit);
        if ($selisih != 0) {
            foreach ($details as &$line) {
                if ($line['no_perkiraan'] === '2101-01-06') {
                    $line['kredit'] = round($line['kredit'] + $selisih);
                    $this->db->update('gl_interface_detail', ['kredit' => $line['kredit']], ['id' => $line['id']]);
                    break;
                }
            }
            unset($line);
        }

        $this->db->insert(DBACC . '.javh', [
            'nomor' => $nomor_jv,
            'tgl' => $header['tgl'],
            'jml' => $header['jml'],
            'kdcab' => $header['kdcab'],
            'jenis' => $header['jenis'],
            'keterangan' => $header['keterangan'],
            'bulan' => $header['bulan'],
            'tahun' => $header['tahun'],
            'user_id' => $header['user_id'],
        ]);

        foreach ($details as $line) {
            $this->db->insert(DBACC . '.jurnal', [
                'tipe' => $line['tipe'],
                'nomor' => $nomor_jv,
                'tanggal' => $line['tanggal'],
                'no_perkiraan' => $line['no_perkiraan'],
                'keterangan' => $line['keterangan'],
                'no_reff' => $line['no_reff'],
                'debet' => $line['debet'],
                'kredit' => $line['kredit'],
                'id_material' => $line['id_material'],
                'nm_material' => $line['nm_material'],
                'id_gudang' => $line['id_gudang'],
                'no_coil' => $line['no_coil'],
                'created_by' => $header['user_id'],
                'created_on' => date('Y-m-d H:i:s'),
            ]);

            if ($line['kredit'] > 0) {
                $this->db->insert('tr_kartu_hutang', [
                    'tipe' => $line['tipe'],
                    'nomor' => $nomor_jv,
                    'tanggal' => $line['tanggal'],
                    'no_perkiraan' => $line['no_perkiraan'],
                    'keterangan' => $line['keterangan'],
                    'no_reff' => $line['no_reff'],
                    'debet' => 0,
                    'kredit' => $line['kredit'],
                    'id_supplier' => $id_supplier,
                    'nama_supplier' => $nama_supplier,
                    'no_request' => $line['no_request'],
                ]);
            }
        }

        // $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'");
        $this->db->update('gl_interface', ['status' => 'posted', 'posted_at' => date('Y-m-d H:i:s')], ['id' => $header['id']]);
    }

    // REPOST GL INTERFACE
    public function repost_gl_interface()
    {
        $nomor_jv = $this->input->post('nomor_jv');
        if (empty($nomor_jv)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'nomor_jv tidak boleh kosong']));
        }

        $already = $this->db->get_where(DBACC . '.javh', ['nomor' => $nomor_jv])->num_rows();
        if ($already > 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Nomor JV ' . $nomor_jv . ' sudah diposting']));
        }

        $gl = $this->db->get_where('gl_interface', ['nomor' => $nomor_jv, 'status' => 'pending'])->row();
        if (empty($gl)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Data not found or already posted']));
        }

        $this->db->trans_start();
        $this->_post_gl_interface($nomor_jv);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Repost failed']));
        }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => 1, 'pesan' => 'Repost successful for JV ' . $nomor_jv]));
    }

    // UPLOAD FILE
    private function _upload_incoming_files($input_name)
    {
        if (empty($_FILES[$input_name]['name'][0])) return '';

        $config = [
            'upload_path'   => './uploads/incoming_material',
            'allowed_types' => 'gif|jpg|jpeg|png|pdf|zip|rar',
            'max_size'      => 102400,
            'encrypt_name'  => TRUE,
            'remove_spaces' => TRUE,
        ];

        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, TRUE);

        $this->load->library('upload', $config);

        $uploaded_paths = [];
        $files = $_FILES[$input_name];

        foreach ($files['name'] as $key => $image) {
            $_FILES['temp_upload'] = [
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error'    => $files['error'][$key],
                'size'     => $files['size'][$key],
            ];
            $this->upload->initialize($config);
            if ($this->upload->do_upload('temp_upload')) {
                $d = $this->upload->data();
                $uploaded_paths[] = 'uploads/incoming_material/' . $d['file_name'];
            }
        }

        return !empty($uploaded_paths) ? implode('|', $uploaded_paths) : '';
    }

    public function _generate_nomor_jv()
    {
        $cabang = $this->db->query(
            "SELECT nomorJC FROM " . DBACC . ".pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1 FOR UPDATE"
        )->row();

        if (empty($cabang)) {
            throw new Exception('Branch data not found for generating JV number!');
        }

        $nomor_urut = (int) $cabang->nomorJC + 1;

        // Hasil: 101-AJV2606779
        $nomor_jv = '101-AJV' . date('ym') . $nomor_urut;

        $this->db->query(
            "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'"
        );

        return $nomor_jv;
    }

    // DATA TABLE — Tab CLOSE
    public function data_side_close()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [
            1 => 'r.id',
            2 => 'r.no_po',
            3 => 's.nama',
            4 => 'inc.created_at',
            5 => 'inc.kode_trans',
        ];
        $order_by = $col_map[$order_col] ?? 'inc.created_at';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%' OR inc.kode_trans LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
              LEFT JOIN new_supplier s       ON s.kode_supplier  = r.id_supplier
              LEFT JOIN tr_incoming_header inc ON inc.no_ros     = r.id
              WHERE r.status = '1' AND r.status_incoming = 'closed'
              {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
    SELECT
        r.*,
        s.nama              AS nm_supplier,
        inc.kode_trans      AS kode_incoming,
        inc.created_at      AS tgl_finalize,      -- ← sesuaikan nama kolom
        GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list
    FROM tr_ros_header r
    LEFT JOIN new_supplier s         ON s.kode_supplier  = r.id_supplier
    LEFT JOIN tr_incoming_header inc ON inc.no_ros       = r.id     -- ← ganti tabel
    LEFT JOIN tr_purchase_order p    ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
    WHERE r.status = '1' AND r.status_incoming = 'closed'
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

            $sts_badge = '<span class="badge rounded-pill bg-success">Closed</span>';

            $btn_view = '';
            if (!empty($row['kode_incoming'])) {
                $btn_view = '<a href="' . base_url('incoming/view/' . $row['kode_incoming']) . '"
                        class="btn btn-sm btn-primary" title="Lihat Detail">
                        <i class="fa fa-eye"></i> Detail
                     </a>';
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-center'>" . ($row['tgl_finalize'] ? date('d/m/Y H:i', strtotime($row['tgl_finalize'])) : '-') . "</div>",
                "<div class='text-center'>" . ($row['kode_incoming'] ?? '-') . "</div>",
                "<div class='text-center'>{$sts_badge}</div>",
                "<div class='text-center'>{$btn_view}</div>",
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

    public function print_pl_by_gudang($no_ros)
    {
        $this->auth->restrict($this->viewPermission);

        // ── 1. Header ROS ────────────────────────────────────────────────────
        $header_ros = $this->db->query("
        SELECT a.*,
               b.nama AS nm_supplier
        FROM tr_ros_header a
        LEFT JOIN new_supplier b ON a.id_supplier = b.kode_supplier
        WHERE a.id = ?
        LIMIT 1
    ", [$no_ros])->row_array();

        if (empty($header_ros)) {
            show_404();
        }

        $detail_coils = $this->db->query("
        SELECT
            c.id                AS id_coil,
            c.no_coil,
            c.berat_kotor,
            c.berat_bersih,
            c.panjang           AS length,
            c.kode_internal,
            c.status_qc,
            c.id_gudang_ke,
            c.kd_gudang_ke,
            c.is_baby_coil,
            c.qty_roll,
            c.id_ros_pack,
            w.nm_gudang,
            b.nm_erp            AS nm_material,
            b.id_barang         AS id_material,
            b.nm_alias          AS trade_name,
            p.pack_code,
            p.pack_no,
            IFNULL(ms.code, 'Kg') AS unit_satuan
        FROM tr_ros_material_coil c
        JOIN tr_ros_material b      ON c.id_ros_material = b.id
        JOIN tr_ros_header a        ON b.id_ros = a.id
        LEFT JOIN warehouse w       ON c.id_gudang_ke = w.id
        LEFT JOIN tr_ros_pack p     ON p.id = c.id_ros_pack
        LEFT JOIN dt_trans_po f     ON b.id_po_detail = f.id AND f.tipe IS NOT NULL
        LEFT JOIN ms_satuan ms      ON ms.id = (
            COALESCE(
                (SELECT id_unit FROM new_inventory_4 WHERE code_lv4 = b.id_barang LIMIT 1),
                (SELECT id_unit_gudang FROM accessories WHERE id = b.id_barang LIMIT 1)
            )
        )
        WHERE a.id = ?
          AND c.id_gudang_ke IS NOT NULL
        ORDER BY w.nm_gudang ASC, p.pack_no ASC, b.id_barang ASC, c.no_coil ASC
    ", [$no_ros])->result_array();

        $grouped_by_gudang = [];

        foreach ($detail_coils as $row) {
            $gid = $row['id_gudang_ke'] ?? 'unset';

            if (!isset($grouped_by_gudang[$gid])) {
                $grouped_by_gudang[$gid] = [
                    'id_gudang'  => $row['id_gudang_ke'],
                    'kd_gudang'  => $row['kd_gudang_ke'],
                    'nm_gudang'  => $row['nm_gudang'] ?? '-',
                    'packs'      => [],
                ];
            }

            // Group by pack within gudang
            $pack_id = $row['id_ros_pack'] ?: 0;
            if (!isset($grouped_by_gudang[$gid]['packs'][$pack_id])) {
                $grouped_by_gudang[$gid]['packs'][$pack_id] = [
                    'pack_code' => $row['pack_code'] ?: '-',
                    'pack_no'   => $row['pack_no'] ?: 0,
                    'items'     => [],
                ];
            }

            // Skip mother coil with baby from items
            $is_mother_with_baby = ((int) $row['is_baby_coil'] === 0 && (int) $row['qty_roll'] > 1);
            if (!$is_mother_with_baby) {
                $grouped_by_gudang[$gid]['packs'][$pack_id]['items'][] = $row;
            }
        }

        // ── 4. Kirim ke view ──────────────────────────────────────────────────
        $data = [
            'header_ros'        => $header_ros,
            'grouped_by_gudang' => $grouped_by_gudang,
            'no_ros'            => $no_ros,
        ];

        $this->load->view('incoming/print_pl_by_gudang', $data);
    }

    // AJAX — ambil data preview coil untuk modal finalize
    public function get_draft_preview()
    {
        $no_ros = $this->input->post('no_ros');

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
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

        $coils = $this->db->query("
            SELECT c.no_coil, c.berat_kotor, c.berat_bersih, c.panjang,
                c.status_qc, c.kd_gudang_ke,
                m.nm_erp AS nm_material, m.id_barang AS id_material,
                d.qty AS qty_po
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m  ON m.id = c.id_ros_material
            LEFT JOIN dt_trans_po d ON d.id = m.id_po_detail
            WHERE m.id_ros = ?
            ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        echo json_encode([
            'status' => 1,
            'header' => $header,
            'coils'  => $coils,
        ]);
    }

    public function _validate_and_get_coa_names(array $coa_list)
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
}
