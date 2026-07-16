<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Request_list extends Admin_Controller
{
    protected $viewPermission   = 'Request_List.View';
    protected $managePermission = 'Request_List.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('request_list/Request_list_model');
        $this->template->title('Request List');
        $this->template->page_icon('fa fa-clipboard-check');

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
    // DATATABLES SERVER-SIDE
    // ---------------------------------------------------------------

    public function data_side()
    {
        $this->auth->restrict($this->viewPermission);

        $search    = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';
        $start     = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
        $length    = isset($_REQUEST['length']) ? (int) $_REQUEST['length'] : 10;
        $order_col = isset($_REQUEST['order'][0]['column']) ? $_REQUEST['order'][0]['column'] : 1;
        $order_dir = isset($_REQUEST['order'][0]['dir']) ? $_REQUEST['order'][0]['dir'] : 'desc';
        $draw      = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 1;

        $col_map = array(
            1 => 'h.spk_no',
            2 => 'h.tgl_spk',
            3 => 'h.shift_names',
            4 => 'h.status',
            5 => 'detail_count'
        );
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'h.created_at';

        $rows       = $this->Request_list_model->get_spk_list($search, $start, $length, $order_by, $order_dir);
        $totalData  = $this->Request_list_model->count_spk_filtered($search);

        $data = array();
        $no   = $start + 1;

        foreach ($rows as $row) {
            // Mengikuti status langsung dari database
            $display_status = $row['status'];

            // Menentukan warna badge berdasarkan nilai di database
            switch ($display_status) {
                case 'Material Requested':
                    $badge_class = 'bg-warning text-dark';
                    break;
                case 'Material On Load':
                    $badge_class = 'bg-info text-dark';
                    break;
                case 'Material Confirmed':
                    $badge_class = 'bg-success';
                    break;
                case 'Released':
                    $badge_class = 'bg-secondary';
                    break;
                case 'Cancelled':
                    $badge_class = 'bg-danger';
                    break;
                default:
                    $badge_class = 'bg-dark';
                    break;
            }

            $status_html = "<span class='badge rounded-pill " . $badge_class . "'>" . $display_status . "</span>";
            // Action buttons
            $btn_view = '<a href="' . site_url('request_list/view_spk_coil/' . $row['spk_no']) . '" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>';

            $aksi = $btn_view;

            // Show "Create SPK Coil" button logic:
            // Hanya muncul jika statusnya adalah 'Material Requested'
            $show_create_btn = false;

            if (in_array($display_status, ['Material Requested', 'Material On Load'])) {
                $has_unfulfilled = $this->Request_list_model->has_unfulfilled_material($row['spk_no']);
                if ($has_unfulfilled) {
                    $show_create_btn = true;
                }
            }

            if ($show_create_btn) {
                $btn_create = ' <a href="' . site_url('request_list/create_spk_coil/' . $row['spk_no']) . '" class="btn btn-sm btn-primary" title="Create SPK Coil"><i class="fa fa-plus"></i></a>';
                $aksi .= $btn_create;
            }

            if ($display_status != 'Material Requested') {
                $btn_print_pengambilan = ' <a href="' . site_url('request_list/print_spk_pengambilan/' . $row['spk_no']) . '" target="_blank" class="btn btn-sm btn-secondary" title="Print SPK Pengambilan Coil"><i class="fa fa-print"></i></a>';
                $aksi .= $btn_print_pengambilan;
            }

            $data[] = array(
                "<div class='text-center'>" . $no . "</div>",
                $row['spk_no'],
                date('d/m/Y', strtotime($row['tgl_spk'])),
                htmlspecialchars(isset($row['shift_names']) ? $row['shift_names'] : ''),
                "<div class='text-center'>" . $status_html . "</div>",
                "<div class='text-center'>" . (isset($row['detail_count']) ? $row['detail_count'] : 0) . "</div>",
                "<div class='text-center'>" . $aksi . "</div>",
            );
            $no++;
        }

        echo json_encode(array(
            'draw'            => $draw,
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ));
    }

    // ---------------------------------------------------------------
    // VIEW SPK COIL DETAIL
    // ---------------------------------------------------------------

    public function view_spk_coil($spk_no = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$spk_no) {
            $this->session->set_flashdata('error', 'SPK No tidak valid.');
            redirect('request_list');
        }

        // Get SPK details
        $spk_data = $this->Request_list_model->get_spk_with_details($spk_no);

        if (!$spk_data) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('request_list');
        }

        // Get saved coils
        $saved_coils = $this->Request_list_model->get_saved_coils_by_spk($spk_no);

        $data['spk_no']   = $spk_no;
        $data['header']   = $spk_data['header'];
        $data['products'] = $spk_data['products'];
        $data['saved_coils'] = $saved_coils;

        $this->template->render('view_spk_coil', $data);
    }

    // ---------------------------------------------------------------
    // CREATE SPK COIL
    // ---------------------------------------------------------------

    public function create_spk_coil($spk_no = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$spk_no) {
            $this->session->set_flashdata('error', 'SPK No tidak valid.');
            redirect('request_list');
        }

        // Get SPK details
        $spk_data = $this->Request_list_model->get_spk_with_details($spk_no);

        if (!$spk_data) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('request_list');
        }

        // Validate status — allow creation if status is 'Material Requested' or 'Material On Load'
        $valid_statuses = array('Material Requested', 'Material On Load');
        if (!in_array($spk_data['header']['status'], $valid_statuses)) {
            $this->session->set_flashdata('error', 'SPK tidak dapat dibuatkan SPK Coil karena status: ' . $spk_data['header']['status']);
            redirect('request_list');
        }

        $data['spk_no']   = $spk_no;
        $data['header']   = $spk_data['header'];
        $data['products'] = $spk_data['products'];

        $this->template->render('form_create_spk_coil', $data);
    }

    // ---------------------------------------------------------------
    // AJAX: GET AVAILABLE COILS
    // ---------------------------------------------------------------

    public function get_available_coils($id_material = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id_material) {
            return $this->_json(array('status' => 0, 'message' => 'ID Material tidak valid.'));
        }

        $spk_no = isset($_GET['spk_no']) ? $_GET['spk_no'] : '';

        if (empty($spk_no)) {
            return $this->_json(array('status' => 0, 'message' => 'SPK No tidak valid.'));
        }

        $coils = $this->Request_list_model->get_available_coils($id_material, $spk_no);

        // Group by id_gudang
        $grouped = array(
            'gudang_coil' => array(),
            'wip'         => array()
        );

        foreach ($coils as $coil) {
            if ($coil['source_type'] == 1) {
                $grouped['gudang_coil'][] = $coil;
            } elseif ($coil['source_type'] == 3) {
                $grouped['wip'][] = $coil;
            }
        }

        return $this->_json(array(
            'status' => 1,
            'data'   => $grouped,
            'total_gudang_coil' => count($grouped['gudang_coil']),
            'total_wip'         => count($grouped['wip'])
        ));
    }

    // ---------------------------------------------------------------
    // SAVE SPK COIL
    // ---------------------------------------------------------------

    public function save_spk_coil()
    {
        $this->auth->restrict($this->managePermission);

        $spk_no = $this->input->post('spk_no');
        $coils  = $this->input->post('coils');

        // Basic validation
        if (empty($spk_no)) {
            return $this->_json(array('status' => 0, 'message' => 'SPK No wajib diisi.'));
        }

        if (empty($coils) || !is_array($coils)) {
            return $this->_json(array('status' => 0, 'message' => 'Minimal 1 coil harus dipilih.'));
        }

        // Validate SPK exists and status allows coil creation
        $spk_data = $this->Request_list_model->get_spk_with_details($spk_no);
        if (!$spk_data) {
            return $this->_json(array('status' => 0, 'message' => 'SPK tidak ditemukan.'));
        }

        $valid_statuses = array('Material Requested', 'Material On Load');
        if (!in_array($spk_data['header']['status'], $valid_statuses)) {
            return $this->_json(array('status' => 0, 'message' => 'SPK tidak dapat dibuatkan SPK Coil karena status: ' . $spk_data['header']['status']));
        }

        // Server-side validation per material group
        $unavailable_coils = array();

        foreach ($coils as $coil) {
            $id_coil       = isset($coil['id_coil']) ? $coil['id_coil'] : '';

            // Check coil still available
            $id_gudang_sumber = isset($coil['id_gudang_sumber']) ? (int) $coil['id_gudang_sumber'] : 0;
            $coil_data = $this->Request_list_model->check_coil_available($id_coil, $id_gudang_sumber);
            if (!$coil_data) {
                $coil_label = isset($coil['no_coil']) ? $coil['no_coil'] : $id_coil;
                $unavailable_coils[] = $coil_label;
                continue;
            }
        }

        // Return error if any coil is unavailable
        if (!empty($unavailable_coils)) {
            return $this->_json(array(
                'status'  => 0,
                'message' => 'Coil berikut sudah tidak tersedia: ' . implode(', ', $unavailable_coils)
            ));
        }

        // Start transaction
        $this->db->trans_start();

        // Generate SPK Coil number
        $spk_coil_no = $this->Request_list_model->generate_spk_coil_no();

        // Insert request header
        $header_data = array(
            'spk_no'       => $spk_no,
            'spk_coil_no'  => $spk_coil_no,
            'request_date' => $this->datetime,
            'status'       => 'Material On Load',
            'created_by'   => $this->id_user,
            'created_at'   => $this->datetime,
        );
        $request_id = $this->Request_list_model->insert_request_header($header_data);

        // Insert coil details
        $detail_records = array();
        foreach ($coils as $coil) {
            $detail_records[] = array(
                'request_id'     => $request_id,
                'id_coil'        => isset($coil['id_coil']) ? $coil['id_coil'] : 0,
                'id_material'    => isset($coil['id_material']) ? $coil['id_material'] : '',
                'nm_material'    => isset($coil['nm_material']) ? $coil['nm_material'] : '',
                'kode_internal'  => isset($coil['kode_internal']) ? $coil['kode_internal'] : '',
                'no_coil'        => isset($coil['no_coil']) ? $coil['no_coil'] : '',
                'id_gudang_sumber' => isset($coil['id_gudang_sumber']) ? (int) $coil['id_gudang_sumber'] : 0,
            );
        }

        if (!empty($detail_records)) {
            $this->Request_list_model->insert_coil_details($detail_records);
        }

        // Update SPK Material status to 'Material On Load'
        $this->Request_list_model->update_spk_material_status($spk_no, array(
            'status'     => 'Material On Load',
            'updated_by' => $this->id_user,
            'updated_at' => $this->datetime,
        ));

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return $this->_json(array('status' => 0, 'message' => 'Gagal menyimpan SPK Coil. Silakan coba lagi.'));
        }

        return $this->_json(array(
            'status'      => 1,
            'message'     => 'SPK Coil berhasil dibuat.',
            'spk_coil_no' => $spk_coil_no
        ));
    }

    // ---------------------------------------------------------------
    // PRINT SPK PENGAMBILAN COIL
    // ---------------------------------------------------------------

    public function print_spk_pengambilan($spk_no = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$spk_no) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('request_list');
        }

        $this->load->model('spk_material/Spk_material_model');

        $spk = $this->Spk_material_model->get_spk($spk_no);
        if (!$spk) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan.');
            redirect('request_list');
        }

        $details = $this->Spk_material_model->get_spk_details($spk_no);

        // Get BOM materials for each product
        foreach ($details as &$detail) {
            $detail['materials'] = $this->Spk_material_model->get_bom_details_for_request($detail['id_produk_fg']);
        }

        $data['spk']             = $spk;
        $data['details']         = $details;
        $data['created_by_name'] = $this->username;

        $this->load->view('print_spk_pengambilan', $data);
    }

    // ---------------------------------------------------------------
    // PRINT SPK COIL
    // ---------------------------------------------------------------

    public function print_spk_coil($request_id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$request_id) {
            $this->session->set_flashdata('error', 'Request ID tidak valid.');
            redirect('request_list');
        }

        $request = $this->Request_list_model->get_request_by_id($request_id);
        if (!$request) {
            $this->session->set_flashdata('error', 'SPK Coil tidak ditemukan.');
            redirect('request_list');
        }

        $coil_details = $this->Request_list_model->get_coil_details($request_id);

        $data['request']      = $request;
        $data['coil_details'] = $coil_details;

        // Load view standalone — no template
        $this->load->view('print_spk_coil', $data);
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
