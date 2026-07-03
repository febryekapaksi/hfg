<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Confirm_spk_coil extends Admin_Controller
{
    protected $viewPermission   = 'Confirm_Spk_Coil.View';
    protected $managePermission = 'Confirm_Spk_Coil.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('confirm_spk_coil/Confirm_spk_coil_model');
        $this->template->title('Confirm SPK Coil');
        $this->template->page_icon('fa fa-qrcode');

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

        $requestData = $_REQUEST;
        $search      = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $start       = (int) (isset($requestData['start']) ? $requestData['start'] : 0);
        $length      = (int) (isset($requestData['length']) ? $requestData['length'] : 10);
        $order_col   = isset($requestData['order'][0]['column']) ? $requestData['order'][0]['column'] : 1;
        $order_dir   = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'desc';

        $col_map = [
            1 => 'wrh.spk_coil_no',
            2 => 'wrh.spk_no',
            3 => 'smh.tgl_spk',
            4 => 'wrh.status'
        ];
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'wrh.created_at';

        $rows      = $this->Confirm_spk_coil_model->get_pending_confirmations($search, $start, $length, $order_by, $order_dir);
        $totalData = $this->Confirm_spk_coil_model->count_pending_filtered($search);

        $data = array();
        $no   = $start + 1;

        foreach ($rows as $row) {
            // Status badge
            $badge_class = 'bg-info text-dark';
            $status_html = "<span class='badge rounded-pill " . $badge_class . "'>" . htmlspecialchars($row['status']) . "</span>";

            // Action button — Confirm Coil
            $btn_confirm = '<a href="' . site_url('confirm_spk_coil/detail/' . $row['id']) . '" class="btn btn-sm btn-success" title="Confirm Coil"><i class="fa fa-check-circle"></i> Confirm Coil</a>';

            $aksi = $btn_confirm;

            $tanggal = isset($row['tgl_spk']) ? date('d/m/Y', strtotime($row['tgl_spk'])) : '-';

            $data[] = array(
                "<div class='text-center'>" . $no . "</div>",
                isset($row['spk_coil_no']) ? htmlspecialchars($row['spk_coil_no']) : '-',
                isset($row['spk_material_no']) ? htmlspecialchars($row['spk_material_no']) : htmlspecialchars($row['spk_no']),
                $tanggal,
                "<div class='text-center'>" . $status_html . "</div>",
                "<div class='text-center'>" . $aksi . "</div>",
            );
            $no++;
        }

        echo json_encode(array(
            'draw'            => intval(isset($requestData['draw']) ? $requestData['draw'] : 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ));
    }

    // ---------------------------------------------------------------
    // DETAIL — Tampilkan daftar coil + scan status
    // ---------------------------------------------------------------

    public function detail($request_id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$request_id) {
            $this->session->set_flashdata('error', 'Request ID tidak valid.');
            redirect('confirm_spk_coil');
        }

        $request = $this->Confirm_spk_coil_model->get_request($request_id);

        if (!$request) {
            $this->session->set_flashdata('error', 'SPK Coil tidak ditemukan.');
            redirect('confirm_spk_coil');
        }

        if ($request['status'] != 'Material On Load') {
            $this->session->set_flashdata('error', 'SPK Coil tidak dapat dikonfirmasi karena status: ' . $request['status']);
            redirect('confirm_spk_coil');
        }

        $data['request']      = $request;
        $data['coil_details'] = $this->Confirm_spk_coil_model->get_coil_details($request_id);

        $this->template->render('confirm_detail', $data);
    }

    // ---------------------------------------------------------------
    // AJAX: Scan QR Code Coil
    // ---------------------------------------------------------------

    public function scan_coil()
    {
        $this->auth->restrict($this->managePermission);

        $kode_internal = $this->input->post('kode_internal');
        $request_id    = $this->input->post('request_id');

        // Validate not empty
        if (empty($kode_internal) || empty($request_id)) {
            return $this->_json(array('status' => 0, 'message' => 'Kode internal dan request ID wajib diisi.'));
        }

        // Find coil by kode_internal in this SPK Coil
        $coil = $this->Confirm_spk_coil_model->find_coil_by_kode_internal($request_id, $kode_internal);

        if (!$coil) {
            return $this->_json(array('status' => 0, 'message' => 'Coil tidak ditemukan dalam SPK ini'));
        }

        // Already scanned
        if ($coil['scan_status'] == 1) {
            return $this->_json(array('status' => 2, 'message' => 'Coil sudah di-scan sebelumnya', 'detail_id' => $coil['id']));
        }

        // Mark as scanned
        $this->Confirm_spk_coil_model->update_scan_status($coil['id'], array(
            'scan_status' => 1,
            'scanned_at'  => $this->datetime,
            'scanned_by'  => $this->id_user,
        ));

        // Check if all coils scanned
        $all_scanned = $this->Confirm_spk_coil_model->all_coils_scanned($request_id);

        return $this->_json(array(
            'status'      => 1,
            'message'     => 'Coil berhasil di-scan.',
            'detail_id'   => $coil['id'],
            'all_scanned' => $all_scanned,
        ));
    }

    // ---------------------------------------------------------------
    // AJAX: Confirm Pengeluaran Coil
    // ---------------------------------------------------------------

    public function confirm($request_id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$request_id) {
            return $this->_json(array('status' => 0, 'message' => 'Request ID tidak valid.'));
        }

        // Validate request exists and status is correct
        $request = $this->Confirm_spk_coil_model->get_request($request_id);

        if (!$request) {
            return $this->_json(array('status' => 0, 'message' => 'SPK Coil tidak ditemukan.'));
        }

        if ($request['status'] != 'Material On Load') {
            return $this->_json(array('status' => 0, 'message' => 'SPK Coil tidak dapat dikonfirmasi karena status: ' . $request['status']));
        }

        // Check all coils scanned
        if (!$this->Confirm_spk_coil_model->all_coils_scanned($request_id)) {
            return $this->_json(array('status' => 0, 'message' => 'Semua coil harus di-scan terlebih dahulu'));
        }

        // Get coil details
        $coil_details = $this->Confirm_spk_coil_model->get_coil_details($request_id);

        // Start transaction
        $this->db->trans_start();

        foreach ($coil_details as $coil) {
            if ($coil['id_gudang_sumber'] == 1) {
                // Source is Gudang Coil — reduce stock and create WIP record
                $source_data = $this->Confirm_spk_coil_model->get_coil_source_data($coil['id_coil']);

                // Reduce stock
                $this->Confirm_spk_coil_model->reduce_coil_stock($coil['id_coil'], $coil['plan_use']);

                // Insert WIP record - copy fields from source + override
                if ($source_data) {
                    $wip_data = array(
                        'id_material'   => isset($source_data['id_material']) ? $source_data['id_material'] : '',
                        'nm_material'   => isset($source_data['nm_material']) ? $source_data['nm_material'] : '',
                        'trade_name'    => isset($source_data['trade_name']) ? $source_data['trade_name'] : '',
                        'kode_internal' => isset($source_data['kode_internal']) ? $source_data['kode_internal'] : '',
                        'no_coil'       => isset($source_data['no_coil']) ? $source_data['no_coil'] : '',
                        'no_ipp'        => isset($source_data['no_ipp']) ? $source_data['no_ipp'] : '',
                        'no_po'         => isset($source_data['no_po']) ? $source_data['no_po'] : '',
                        'no_ros'        => isset($source_data['no_ros']) ? $source_data['no_ros'] : '',
                        'gross_weight'  => isset($source_data['gross_weight']) ? $source_data['gross_weight'] : 0,
                        'net_weight'    => isset($source_data['net_weight']) ? $source_data['net_weight'] : 0,
                        'length'        => isset($source_data['length']) ? $source_data['length'] : 0,
                        'harga_beli'    => isset($source_data['harga_beli']) ? $source_data['harga_beli'] : 0,
                        'total_nilai'   => isset($source_data['total_nilai']) ? $source_data['total_nilai'] : 0,
                        'id_gudang'     => 3,
                        'kd_gudang'     => 'WIP',
                        'type'          => 'from_warehouse',
                        'qty'           => $coil['plan_use'],
                        'status'        => 1,
                        'created_by'    => $this->id_user,
                        'created_on'    => $this->datetime,
                    );
                    $this->Confirm_spk_coil_model->insert_wip_record($wip_data);
                }
            }
            // id_gudang_sumber == 3 (already WIP) — no stock movement needed
        }

        // Update request status to Material Confirmed
        $this->Confirm_spk_coil_model->update_request_status($request_id, array(
            'status'       => 'Material Confirmed',
            'confirmed_by' => $this->id_user,
            'confirmed_at' => $this->datetime,
        ));

        // Check if all SPK Coil for this SPK Material are confirmed
        $spk_no = $request['spk_no'];
        if ($this->Confirm_spk_coil_model->all_spk_coil_confirmed($spk_no)) {
            $this->Confirm_spk_coil_model->update_spk_material_status($spk_no, array(
                'status'     => 'Material Confirmed',
                'updated_by' => $this->id_user,
                'updated_at' => $this->datetime,
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return $this->_json(array('status' => 0, 'message' => 'Gagal mengkonfirmasi. Silakan coba lagi.'));
        }

        return $this->_json(array('status' => 1, 'message' => 'SPK Coil berhasil dikonfirmasi.'));
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
