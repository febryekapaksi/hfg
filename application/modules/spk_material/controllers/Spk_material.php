<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Spk_material extends Admin_Controller
{
    protected $viewPermission   = 'Spk_Material.View';
    protected $managePermission = 'Spk_Material.Manage';
    protected $addPermission    = 'Spk_Material.Add';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('spk_material/Spk_material_model');
        $this->template->title('SPK Material');
        $this->template->page_icon('fa fa-clipboard-list');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->username = $this->auth->nama();
        $this->datetime = date('Y-m-d H:i:s');
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->render('index');
    }

    // ---------------------------------------------------------------
    // ADD
    // ---------------------------------------------------------------

    public function add()
    {
        $this->auth->restrict($this->addPermission);

        $data['mode'] = 'add';
        $data['spk'] = null;
        $data['details'] = [];

        $this->template->render('form', $data);
    }

    // ---------------------------------------------------------------
    // EDIT
    // ---------------------------------------------------------------

    public function edit($spk_no = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$spk_no) {
            redirect('spk_material');
        }

        $spk = $this->Spk_material_model->get_spk($spk_no);

        if (!$spk) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('spk_material');
        }

        // Validate editable status
        $editable_statuses = ['Material Requested', 'Material Confirmed'];
        if (!in_array($spk['status'], $editable_statuses)) {
            $this->session->set_flashdata('error', 'SPK tidak dapat diedit karena status: ' . $spk['status']);
            redirect('spk_material/view/' . $spk_no);
        }

        $data['mode'] = 'edit';
        $data['spk'] = $spk;
        $data['details'] = $this->Spk_material_model->get_spk_details($spk_no);

        $this->template->render('form', $data);
    }

    // ---------------------------------------------------------------
    // VIEW
    // ---------------------------------------------------------------

    public function view($spk_no = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$spk_no) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('spk_material');
        }

        $spk = $this->Spk_material_model->get_spk($spk_no);

        if (!$spk) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('spk_material');
        }

        $data['spk']     = $spk;
        $data['details'] = $this->Spk_material_model->get_spk_details($spk_no);

        $this->template->render('view', $data);
    }

    // ---------------------------------------------------------------
    // SAVE (Create / Update)
    // ---------------------------------------------------------------

    public function save()
    {
        $mode = $this->input->post('mode');

        if ($mode === 'edit') {
            $this->auth->restrict($this->managePermission);
        } else {
            $this->auth->restrict($this->addPermission);
        }

        // Get input data
        $tgl_spk    = $this->input->post('tgl_spk');
        $due_date   = $this->input->post('due_date');
        $shift_ids  = $this->input->post('shift_ids');
        $shift_names = $this->input->post('shift_names');
        $catatan    = $this->input->post('catatan');
        $products   = $this->input->post('products');

        // Basic validation
        if (empty($tgl_spk)) {
            return $this->_json(['status' => 0, 'message' => 'Tanggal SPK wajib diisi.']);
        }
        if (empty($shift_ids)) {
            return $this->_json(['status' => 0, 'message' => 'Shift wajib dipilih minimal satu.']);
        }
        if (empty($products) || !is_array($products)) {
            return $this->_json(['status' => 0, 'message' => 'Minimal 1 baris produk harus diisi.']);
        }

        // Validate each product line
        $product_ids = [];
        $no_bom_products = [];

        foreach ($products as $i => $prod) {
            $line_num = $i + 1;

            if (empty($prod['id_produk_fg'])) {
                return $this->_json(['status' => 0, 'message' => "Baris {$line_num}: Produk harus dipilih."]);
            }

            $qty = (int) $prod['target_qty'];
            if ($qty < 1 || $qty > 999999) {
                return $this->_json(['status' => 0, 'message' => "Baris {$line_num}: Target Qty harus antara 1 - 999.999."]);
            }

            // Check duplicate products
            if (in_array($prod['id_produk_fg'], $product_ids)) {
                return $this->_json(['status' => 0, 'message' => "Baris {$line_num}: Produk sudah dipilih di baris lain."]);
            }
            $product_ids[] = $prod['id_produk_fg'];

            // Check BOM existence
            if (!$this->Spk_material_model->has_bom($prod['id_produk_fg'])) {
                $no_bom_products[] = isset($prod['nm_produk_fg']) ? $prod['nm_produk_fg'] : $prod['id_produk_fg'];
            }
        }

        if (!empty($no_bom_products)) {
            return $this->_json(['status' => 0, 'message' => 'BOM belum dibuat untuk produk: ' . implode(', ', $no_bom_products)]);
        }

        // Start transaction
        $this->db->trans_start();

        if ($mode === 'edit') {
            $spk_no = $this->input->post('spk_no');

            // Validate SPK exists and is editable
            $spk = $this->Spk_material_model->get_spk($spk_no);
            if (!$spk || !in_array($spk['status'], ['Material Requested', 'Material Confirmed'])) {
                $this->db->trans_rollback();
                return $this->_json(['status' => 0, 'message' => 'SPK tidak dapat diedit.']);
            }

            // Delete old details and insert new ones
            $this->Spk_material_model->delete_spk_details($spk_no);

            // Build detail records
            $details = [];
            $total_target_qty = 0;
            $total_weight_header = 0;
            foreach ($products as $i => $prod) {
                $t_qty = (int) $prod['target_qty'];
                $t_weight = (float) (isset($prod['total_weight']) ? $prod['total_weight'] : 0);
                
                $details[] = [
                    'spk_no'        => $spk_no,
                    'urut'          => $i + 1,
                    'id_produk_fg'  => $prod['id_produk_fg'],
                    'nm_produk_fg'  => isset($prod['nm_produk_fg']) ? $prod['nm_produk_fg'] : '',
                    'target_qty'    => $t_qty,
                    'berat_per_unit'=> (float) (isset($prod['berat_per_unit']) ? $prod['berat_per_unit'] : 0),
                    'total_weight'  => $t_weight,
                ];
                
                $total_target_qty += $t_qty;
                $total_weight_header += $t_weight;
            }
            $this->Spk_material_model->insert_spk_details($details);

            // Update header
            $this->Spk_material_model->update_spk_header($spk_no, [
                'tgl_spk'      => $tgl_spk,
                'due_date'     => !empty($due_date) ? $due_date : null,
                'shift_ids'    => $shift_ids,
                'shift_names'  => $shift_names,
                'target_qty'   => $total_target_qty,
                'total_weight' => $total_weight_header,
                'catatan'      => $catatan,
                'updated_by'   => $this->id_user,
                'updated_at'   => $this->datetime,
            ]);

        } else {
            // CREATE MODE
            $spk_no = $this->Spk_material_model->generate_spk_no();

            // Build detail records first to get totals
            $details = [];
            $total_target_qty = 0;
            $total_weight_header = 0;
            foreach ($products as $i => $prod) {
                $t_qty = (int) $prod['target_qty'];
                $t_weight = (float) (isset($prod['total_weight']) ? $prod['total_weight'] : 0);
                
                $details[] = [
                    'spk_no'        => $spk_no,
                    'urut'          => $i + 1,
                    'id_produk_fg'  => $prod['id_produk_fg'],
                    'nm_produk_fg'  => isset($prod['nm_produk_fg']) ? $prod['nm_produk_fg'] : '',
                    'target_qty'    => $t_qty,
                    'berat_per_unit'=> (float) (isset($prod['berat_per_unit']) ? $prod['berat_per_unit'] : 0),
                    'total_weight'  => $t_weight,
                ];
                
                $total_target_qty += $t_qty;
                $total_weight_header += $t_weight;
            }

            // Insert header
            $this->Spk_material_model->insert_spk_header([
                'spk_no'      => $spk_no,
                'tgl_spk'     => $tgl_spk,
                'due_date'    => !empty($due_date) ? $due_date : null,
                'shift_ids'   => $shift_ids,
                'shift_names' => $shift_names,
                'target_qty'   => $total_target_qty,
                'total_weight' => $total_weight_header,
                'catatan'     => $catatan,
                'status'      => 'Material Requested',
                'created_by'  => $this->id_user,
                'created_at'  => $this->datetime,
            ]);

            // Insert details
            $this->Spk_material_model->insert_spk_details($details);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return $this->_json(['status' => 0, 'message' => 'Gagal menyimpan SPK. Silakan coba lagi.']);
        }

        $msg = ($mode === 'edit') ? 'SPK berhasil diupdate.' : 'SPK berhasil dibuat.';
        return $this->_json(['status' => 1, 'message' => $msg, 'spk_no' => $spk_no]);
    }

    // ---------------------------------------------------------------
    // DATATABLES SERVER-SIDE
    // ---------------------------------------------------------------

    public function data_side()
    {
        $this->auth->restrict($this->viewPermission);

        $requestData = $_REQUEST;
        $search      = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $start       = (int) (isset($requestData['start']) ? $requestData['start'] : 0);
        $length      = (int) (isset($requestData['length']) ? $requestData['length'] : 10);
        $order_col   = isset($requestData['order'][0]['column']) ? $requestData['order'][0]['column'] : 1;
        $order_dir   = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'desc';

        $col_map = [1 => 'h.spk_no', 2 => 'h.tgl_spk', 3 => 'h.shift_names', 4 => 'h.status', 5 => 'detail_count'];
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'h.created_at';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (h.spk_no LIKE '%{$s}%' OR h.status LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_spk_material_header h WHERE 1=1 {$where_search}";
        $total_q = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "SELECT h.*, (SELECT COUNT(*) FROM tr_spk_material_detail d WHERE d.spk_no = h.spk_no) as detail_count
                FROM tr_spk_material_header h
                WHERE 1=1 {$where_search}
                ORDER BY {$order_by} {$order_dir}
                LIMIT {$start}, {$length}";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no = $start + 1;

        foreach ($rows as $row) {
            // Status badge colors
            $status_badges = [
                'Material Requested' => 'bg-warning text-dark',
                'Material Confirmed' => 'bg-info text-dark',
                'Released'           => 'bg-success',
                'Cancelled'          => 'bg-danger'
            ];
            $badge_class = isset($status_badges[$row['status']]) ? $status_badges[$row['status']] : 'bg-secondary';
            $status_html = "<span class='badge rounded-pill {$badge_class}'>{$row['status']}</span>";

            // Action buttons — only View and Print
            $btn_view = '<a href="'.site_url('spk_material/view/'.$row['spk_no']).'" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>';
            $btn_pdf = ' <a href="'.site_url('spk_material/print_pdf/'.$row['spk_no']).'" target="_blank" class="btn btn-sm btn-secondary" title="Print PDF"><i class="fa fa-file-pdf"></i></a>';

            $aksi = $btn_view . $btn_pdf;

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['spk_no'],
                date('d/m/Y', strtotime($row['tgl_spk'])),
                htmlspecialchars($row['shift_names']),
                "<div class='text-center'>{$status_html}</div>",
                "<div class='text-center'>{$row['detail_count']}</div>",
                "<div class='text-center'>{$aksi}</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval(isset($requestData['draw']) ? $requestData['draw'] : 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ---------------------------------------------------------------
    // AJAX: Get Produk List (for Select2 AJAX)
    // ---------------------------------------------------------------

    public function get_produk_list()
    {
        $this->auth->restrict($this->viewPermission);

        $q = $this->input->get('q');
        $products = $this->Spk_material_model->get_produk_fg_list($q);
        return $this->_json(['status' => 1, 'data' => $products]);
    }

    // ---------------------------------------------------------------
    // AJAX: Get Shift List
    // ---------------------------------------------------------------

    public function get_shift_list()
    {
        $this->auth->restrict($this->viewPermission);

        $shifts = $this->Spk_material_model->get_active_shifts();
        return $this->_json(['status' => 1, 'data' => $shifts]);
    }

    // ---------------------------------------------------------------
    // AJAX: Get Produk Info
    // ---------------------------------------------------------------

    public function get_produk_info($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id) {
            return $this->_json(['status' => 0, 'message' => 'ID produk tidak valid.']);
        }

        $weight = $this->Spk_material_model->get_produk_weight($id);
        return $this->_json(['status' => 1, 'data' => ['weight' => $weight]]);
    }

    // ---------------------------------------------------------------
    // AJAX: Get Material BOM
    // ---------------------------------------------------------------

    public function get_material_bom($id_produk = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id_produk) {
            return $this->_json(['status' => 0, 'message' => 'ID produk tidak valid.']);
        }

        $target_qty = (int) $this->input->get('target_qty');
        if ($target_qty < 1) {
            return $this->_json(['status' => 0, 'message' => 'Target qty harus minimal 1.']);
        }

        if (!$this->Spk_material_model->has_bom($id_produk)) {
            return $this->_json(['status' => 0, 'message' => 'BOM belum dibuat untuk produk ini.']);
        }

        $materials = $this->Spk_material_model->get_bom_materials($id_produk, $target_qty);
        return $this->_json(['status' => 1, 'data' => $materials]);
    }

    // ---------------------------------------------------------------
    // UPDATE STATUS
    // ---------------------------------------------------------------

    public function update_status($spk_no = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$spk_no) {
            return $this->_json(['status' => 0, 'message' => 'SPK No tidak valid.']);
        }

        $new_status = $this->input->post('status');
        if (empty($new_status)) {
            return $this->_json(['status' => 0, 'message' => 'Status baru wajib diisi.']);
        }

        $current_status = $this->Spk_material_model->get_spk_status($spk_no);
        if (!$current_status) {
            return $this->_json(['status' => 0, 'message' => 'SPK tidak ditemukan.']);
        }

        // Terminal states - no transition allowed
        $terminal = ['Released', 'Cancelled'];
        if (in_array($current_status, $terminal)) {
            return $this->_json(['status' => 0, 'message' => 'SPK sudah berstatus akhir (' . $current_status . '), tidak dapat diubah.']);
        }

        // Allowed transitions
        $allowed = [
            'Material Requested' => ['Material Confirmed', 'Cancelled'],
            'Material Confirmed' => ['Released', 'Cancelled'],
        ];

        if (!isset($allowed[$current_status]) || !in_array($new_status, $allowed[$current_status])) {
            return $this->_json(['status' => 0, 'message' => 'Transisi status dari "' . $current_status . '" ke "' . $new_status . '" tidak diizinkan.']);
        }

        $result = $this->Spk_material_model->update_spk_status($spk_no, [
            'status'     => $new_status,
            'updated_by' => $this->id_user,
            'updated_at' => $this->datetime,
        ]);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Status SPK berhasil diubah ke "' . $new_status . '".']);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal mengubah status SPK.']);
    }

    // ---------------------------------------------------------------
    // PRINT PDF (via window.print)
    // ---------------------------------------------------------------

    public function print_pdf($spk_no = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$spk_no) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('spk_material');
        }

        $spk = $this->Spk_material_model->get_spk($spk_no);
        if (!$spk) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('spk_material');
        }

        $details = $this->Spk_material_model->get_spk_details($spk_no);

        // Get BOM materials for each product
        foreach ($details as &$detail) {
            $detail['materials'] = $this->Spk_material_model->get_bom_details_for_request($detail['id_produk_fg']);
        }

        $data['spk']             = $spk;
        $data['details']         = $details;
        $data['created_by_name'] = $this->username;

        $this->load->view('print_spk', $data);
    }

    // ---------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------

    private function _json($data)
    {
        if (ob_get_length()) ob_clean();
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
