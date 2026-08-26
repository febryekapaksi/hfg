<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengajuan_mutasi extends Admin_Controller
{
    protected $viewPermission   = 'Request_Mutation.View';
    protected $addPermission    = 'Request_Mutation.Add';
    protected $managePermission = 'Request_Mutation.Manage';
    protected $deletePermission = 'Request_Mutation.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pengajuan_mutasi/pengajuan_mutasi_model');
        $this->template->title('Request Mutation');
        $this->template->page_icon('fa fa-exchange-alt');

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
        $this->template->title('Request Mutation');
        $this->template->render('index');
    }

    // ---------------------------------------------------------------
    // RENDER PARTIAL TABLE PER TAB (AJAX)
    // ---------------------------------------------------------------

    public function render_open()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->pengajuan_mutasi_model->get_list([0, 1, 6]);
        $this->template->render('table/open_mutation', $data);
    }

    public function render_close()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->pengajuan_mutasi_model->get_list([2, 4]);
        $this->template->render('table/close_mutation', $data);
    }

    public function render_cancel()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->pengajuan_mutasi_model->get_list([3, 5]);
        $this->template->render('table/cancel_mutation', $data);
    }

    // ---------------------------------------------------------------
    // FORM (ADD / EDIT / VIEW)
    // ---------------------------------------------------------------

    public function form($mode = 'add', $id = null)
    {
        if ($mode === 'add') {
            $this->auth->restrict($this->addPermission);
        } else {
            $this->auth->restrict($this->viewPermission);
        }

        $data['mode']       = $mode;
        $data['id']         = $id;
        $data['warehouses'] = $this->pengajuan_mutasi_model->get_all_warehouse();
        $data['mutation']  = null;

        if ($id && in_array($mode, ['edit', 'view'])) {
            $mutation = $this->pengajuan_mutasi_model->get_detail($id);

            if (!$mutation) {
                $this->session->set_flashdata('error', 'Mutation data not found.');
                redirect(site_url('pengajuan_mutasi'));
            }

            if ($mode === 'edit' && !in_array($mutation['status'], [0, 6])) {
                $this->session->set_flashdata('error', 'This data cannot be edited due to invalid status.');
                redirect(site_url('pengajuan_mutasi'));
            }

            $data['mutation'] = $mutation;
        }

        $this->template->title(ucfirst($mode) . ' Request Mutation');
        $this->template->render('form', $data);
    }

    // ---------------------------------------------------------------
    // AJAX — GET MATERIAL BY GUDANG
    // ---------------------------------------------------------------

    public function get_material()
    {
        $id_gudang = $this->input->get('id_gudang');

        if (!$id_gudang) {
            return $this->_json(['status' => 0, 'message' => 'id_gudang wajib diisi']);
        }

        $materials = $this->pengajuan_mutasi_model->get_material_by_gudang($id_gudang);

        return $this->_json(['status' => 1, 'data' => $materials]);
    }

    // ---------------------------------------------------------------
    // AJAX — GET COIL BY WAREHOUSE STOCK
    // ---------------------------------------------------------------

    public function get_coil()
    {
        $code_lv4  = $this->input->get('code_lv4');
        $id_gudang = $this->input->get('id_gudang');

        if (!$code_lv4) {
            return $this->_json(['status' => 0, 'message' => 'code_lv4 wajib diisi']);
        }

        if (!$id_gudang) {
            return $this->_json(['status' => 0, 'message' => 'id_gudang wajib diisi']);
        }

        $coils = $this->pengajuan_mutasi_model->get_coil_by_material($code_lv4, $id_gudang);

        return $this->_json(['status' => 1, 'data' => $coils]);
    }

    // ---------------------------------------------------------------
    // AJAX — GET PACKS BY GUDANG (for pack-based mutation)
    // ---------------------------------------------------------------

    public function get_packs()
    {
        $id_gudang = $this->input->get('id_gudang');

        if (!$id_gudang) {
            return $this->_json(['status' => 0, 'message' => 'id_gudang wajib diisi']);
        }

        $packs = $this->pengajuan_mutasi_model->get_packs_by_gudang($id_gudang);

        return $this->_json(['status' => 1, 'data' => $packs]);
    }

    // ---------------------------------------------------------------
    // AJAX — GET COILS INSIDE A PACK (for detail modal)
    // ---------------------------------------------------------------

    public function get_pack_coils()
    {
        $id_pack = (int) $this->input->get('id_pack');

        if (!$id_pack) {
            return $this->_json(['status' => 0, 'message' => 'id_pack wajib diisi']);
        }

        $coils = $this->pengajuan_mutasi_model->get_coils_by_pack($id_pack);

        return $this->_json(['status' => 1, 'data' => $coils]);
    }

    // ---------------------------------------------------------------
    // SAVE (ADD)
    // ---------------------------------------------------------------

    public function save()
    {
        $this->auth->restrict($this->addPermission);

        $post = $this->input->post();

        // Validasi no berita acara
        if (empty($post['no_berita_acara'])) {
            return $this->_json(['status' => 0, 'message' => 'Minutes of Meeting No. is required.']);
        }

        // Validasi gudang
        if (empty($post['id_gudang_from']) || empty($post['id_gudang_to'])) {
            return $this->_json(['status' => 0, 'message' => 'Source and destination warehouse must be selected.']);
        }

        if ($post['id_gudang_from'] == $post['id_gudang_to']) {
            return $this->_json(['status' => 0, 'message' => 'Source and destination warehouse cannot be the same.']);
        }

        // Validasi detail (dikirim sebagai JSON string dalam form field)
        $details_raw = json_decode($post['details_json'] ?? '', true);
        if (empty($details_raw)) {
            return $this->_json(['status' => 0, 'message' => 'At least one material must be selected.']);
        }

        // Ambil info gudang
        $gudang_from = $this->_get_gudang($post['id_gudang_from']);
        $gudang_to   = $this->_get_gudang($post['id_gudang_to']);

        if (!$gudang_from || !$gudang_to) {
            return $this->_json(['status' => 0, 'message' => 'Warehouse data is invalid.']);
        }

        // Handle upload file (optional)
        $file_name_original = null;
        $file_name_hash     = null;

        if (!empty($_FILES['berita_acara_file']['name'])) {
            $upload_result = $this->_upload_berita_acara();
            if ($upload_result['status'] === false) {
                return $this->_json(['status' => 0, 'message' => $upload_result['message']]);
            }
            $file_name_original = $upload_result['original_name'];
            $file_name_hash     = $upload_result['hash_name'];
        }

        $mutation_number = $this->pengajuan_mutasi_model->generate_mutation_number();

        // Hitung total_nilai_transaksi & total_net_weight_transaksi dari detail coils
        $details = $this->_parse_details($details_raw);
        $calc    = $this->_calculate_totals($details, $post['id_gudang_from']);

        $header = [
            'mutation_number'            => $mutation_number,
            'mutation_date'              => date('Y-m-d'),
            'no_berita_acara'            => $post['no_berita_acara'],
            'file_name_original'         => $file_name_original,
            'file_name_hash'             => $file_name_hash,
            'id_gudang_from'             => $post['id_gudang_from'],
            'kd_gudang_from'             => $gudang_from['kd_gudang'],
            'nm_gudang_from'             => $gudang_from['nm_gudang'],
            'id_gudang_to'               => $post['id_gudang_to'],
            'kd_gudang_to'               => $gudang_to['kd_gudang'],
            'nm_gudang_to'               => $gudang_to['nm_gudang'],
            'description'                => $post['description'],
            'total_nilai_transaksi'      => $calc['total_nilai'],
            'total_net_weight_transaksi' => $calc['total_net_weight'],
            'status'                     => 0,
            'create_by'                  => $this->username,
            'create_date'                => $this->datetime,
        ];
        $result  = $this->pengajuan_mutasi_model->save_mutation($header, $details);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Data saved successfully.', 'id' => $result]);
        }

        // Rollback file jika DB gagal
        if ($file_name_hash) {
            @unlink(FCPATH . 'uploads/berita_acara_mutasi/' . $file_name_hash);
        }

        return $this->_json(['status' => 0, 'message' => 'Failed to save data.']);
    }

    // ---------------------------------------------------------------
    // UPDATE (EDIT)
    // ---------------------------------------------------------------

    public function update($id)
    {
        $this->auth->restrict($this->managePermission);

        $post = $this->input->post();

        $mutation = $this->pengajuan_mutasi_model->get_detail($id);
        if (!$mutation || !in_array($mutation['status'], [0, 6])) {
            return $this->_json([
                'status' => 0,
                'message' => 'Data cannot be modified.'
            ]);
        }

        if (empty($post['no_berita_acara'])) {
            return $this->_json(['status' => 0, 'message' => 'Minutes of Meeting No. is required.']);
        }

        if (empty($post['id_gudang_from']) || empty($post['id_gudang_to'])) {
            return $this->_json(['status' => 0, 'message' => 'Source and destination warehouse must be selected.']);
        }

        if ($post['id_gudang_from'] == $post['id_gudang_to']) {
            return $this->_json(['status' => 0, 'message' => 'Source and destination warehouse cannot be the same.']);
        }

        $details_raw = json_decode($post['details_json'] ?? '', true);
        if (empty($details_raw)) {
            return $this->_json(['status' => 0, 'message' => 'At least one material must be selected.']);
        }

        $gudang_from = $this->_get_gudang($post['id_gudang_from']);
        $gudang_to   = $this->_get_gudang($post['id_gudang_to']);

        // Handle upload file baru (optional — jika tidak upload, pakai file lama)
        $file_name_original = $mutation['file_name_original'];
        $file_name_hash     = $mutation['file_name_hash'];
        $old_hash           = $mutation['file_name_hash'];

        if (!empty($_FILES['berita_acara_file']['name'])) {
            $upload_result = $this->_upload_berita_acara();
            if ($upload_result['status'] === false) {
                return $this->_json(['status' => 0, 'message' => $upload_result['message']]);
            }
            $file_name_original = $upload_result['original_name'];
            $file_name_hash     = $upload_result['hash_name'];

            // Hapus file lama
            if ($old_hash) {
                @unlink(FCPATH . 'uploads/berita_acara_mutasi/' . $old_hash);
            }
        }

        // Hitung total_nilai_transaksi & total_net_weight_transaksi dari detail coils
        $details = $this->_parse_details($details_raw);
        $calc    = $this->_calculate_totals($details, $post['id_gudang_from']);

        $header = [
            'no_berita_acara'            => $post['no_berita_acara'],
            'file_name_original'         => $file_name_original,
            'file_name_hash'             => $file_name_hash,
            'id_gudang_from'             => $post['id_gudang_from'],
            'kd_gudang_from'             => $gudang_from['kd_gudang'],
            'nm_gudang_from'             => $gudang_from['nm_gudang'],
            'id_gudang_to'               => $post['id_gudang_to'],
            'kd_gudang_to'               => $gudang_to['kd_gudang'],
            'nm_gudang_to'               => $gudang_to['nm_gudang'],
            'description'                => $post['description'],
            'total_nilai_transaksi'      => $calc['total_nilai'],
            'total_net_weight_transaksi' => $calc['total_net_weight'],
            'update_by'                  => $this->username,
            'update_date'                => $this->datetime,
        ];

        $result  = $this->pengajuan_mutasi_model->update_mutation($id, $header, $details);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Data updated successfully.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Failed to update data.']);
    }

    // ---------------------------------------------------------------
    // AJUKAN (status 0 → 1)
    // ---------------------------------------------------------------

    public function submit($id)
    {
        $this->auth->restrict($this->managePermission);

        $result = $this->pengajuan_mutasi_model->submit_mutation($id, $this->username);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Mutation submitted successfully.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Failed to submit mutation.']);
    }

    // ---------------------------------------------------------------
    // CANCEL (status 0 → 5)
    // ---------------------------------------------------------------

    public function cancel($id)
    {
        $this->auth->restrict($this->managePermission);
        $reject_reason = $this->input->post('reject_reason');
        if (empty(trim($reject_reason))) {
            return $this->_json(['status' => 0, 'message' => 'Cancellation reason is required.']);
        }

        $result = $this->pengajuan_mutasi_model->cancel_mutation($id, $this->username, $reject_reason);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Mutation cancelled successfully.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Failed to cancel mutation or status has changed.']);
    }

    // ---------------------------------------------------------------
    // PRINT QR LABEL (untuk mutasi yang sudah close/approved)
    // ---------------------------------------------------------------

    public function print_qr($id)
    {
        $this->auth->restrict($this->viewPermission);

        $mutation = $this->pengajuan_mutasi_model->get_detail($id);

        if (!$mutation || !in_array($mutation['status'], [2, 4])) {
            show_error('Mutation data not found or not yet approved.', 404);
            return;
        }

        // Group details by pack
        $pack_groups = [];
        foreach ($mutation['details'] as $detail) {
            $pk = $detail['pack_code'] ?: ($detail['id_warehouse_pack'] ?: 'unknown');
            if (!isset($pack_groups[$pk])) {
                $pack_groups[$pk] = [
                    'pack_code'   => $detail['pack_code'] ?: $pk,
                    'materials'   => [],
                    'thicknesses' => [],
                    'total_nw'    => 0,
                    'total_gw'    => 0,
                ];
            }
            $mat_name = $detail['trade_name'] ?: $detail['nm_material'];
            if ($mat_name && !in_array($mat_name, $pack_groups[$pk]['materials'])) {
                $pack_groups[$pk]['materials'][] = $mat_name;
            }

            foreach ($detail['coils'] ?? [] as $coil) {
                $pack_groups[$pk]['total_nw'] += (float) $coil['net_weight'];
                $pack_groups[$pk]['total_gw'] += (float) $coil['gross_weight'];
            }

            // Thickness from inventory
            if (!empty($detail['code_lv4'])) {
                $inv = $this->db->select('thickness')->get_where('new_inventory_4', ['code_lv4' => $detail['code_lv4']])->row();
                if ($inv && !empty($inv->thickness)) {
                    $pack_groups[$pk]['thicknesses'][] = $inv->thickness;
                }
            }
        }

        // Build results per pack
        $results = [];
        foreach ($pack_groups as $pg) {
            $results[] = [
                'pack_code'        => $pg['pack_code'],
                'no_ros'           => '',
                'materials'        => $pg['materials'],
                'thicknesses'      => $pg['thicknesses'],
                'total_nw'         => $pg['total_nw'],
                'total_gw'         => $pg['total_gw'],
                'nm_gudang_tujuan' => $mutation['nm_gudang_to'],
                'kd_gudang_ke'     => $mutation['kd_gudang_to'],
                'id_gudang_ke'     => $mutation['id_gudang_to'],
            ];
        }

        if (empty($results)) {
            show_error('No pack data found for this mutation.', 404);
            return;
        }

        $data = ['results' => $results, 'mutation' => $mutation];
        $this->load->view('print_qr_label', $data);
    }

    // ---------------------------------------------------------------
    // HELPERS PRIVATE
    // ---------------------------------------------------------------

    private function _upload_berita_acara()
    {
        $upload_path = FCPATH . 'uploads/berita_acara_mutasi/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $file      = $_FILES['berita_acara_file'];
        $allowed   = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return ['status' => false, 'message' => 'Format file tidak didukung. Gunakan PDF, JPG, atau PNG.'];
        }

        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            return ['status' => false, 'message' => 'Ukuran file maksimal 5MB.'];
        }

        $original_name = $file['name'];
        $hash_name     = md5(uniqid(rand(), true)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $upload_path . $hash_name)) {
            return ['status' => false, 'message' => 'Gagal menyimpan file.'];
        }

        return [
            'status'        => true,
            'original_name' => $original_name,
            'hash_name'     => $hash_name,
        ];
    }

    private function _get_gudang($id)
    {
        return $this->db->select('id, kd_gudang, nm_gudang')
            ->from('warehouse')
            ->where('id', $id)
            ->get()->row_array();
    }

    private function _parse_details($details_raw)
    {
        $details = [];

        foreach ($details_raw as $d) {
            $coils = [];

            if (!empty($d['coils'])) {
                foreach ($d['coils'] as $c) {
                    $coils[] = [
                        'id_warehouse_stock_coil' => $c['id_warehouse_stock_coil'] ?? ($c['id'] ?? 0),
                        'id_warehouse_pack'       => $c['id_warehouse_pack'] ?? null,
                        'pack_code'               => $c['pack_code'] ?? null,
                        'no_coil'                 => $c['no_coil'] ?? '',
                        'no_ipp'                  => $c['no_ipp'] ?? '',
                        'no_po'                   => $c['no_po'] ?? '',
                        'no_ros'                  => $c['no_ros'] ?? '',
                        'kode_internal'           => $c['kode_internal'] ?? '',
                        'parent_coil_id'          => $c['parent_coil_id'] ?? null,
                        'is_baby_coil'            => $c['is_baby_coil'] ?? 0,
                        'gross_weight'            => $c['gross_weight'] ?? 0,
                        'net_weight'              => $c['net_weight'] ?? 0,
                        'length'                  => $c['length'] ?? 0,
                        'qty_roll'                => $c['qty_roll'] ?? 1,
                        'harga_beli'              => $c['harga_beli'] ?? 0,
                        'total_nilai_mutasi'      => $c['total_nilai_mutasi'] ?? 0,
                    ];
                }
            }

            $details[] = [
                'id_warehouse_stock' => $d['id_warehouse_stock'] ?? 0,
                'id_warehouse_pack'  => $d['id_warehouse_pack'] ?? null,
                'pack_code'          => $d['pack_code'] ?? null,
                'id_material'        => $d['id_material'] ?? '',
                'nm_material'        => $d['nm_material'] ?? '',
                'trade_name'         => $d['trade_name'] ?? '',
                'code_lv4'           => $d['code_lv4'] ?? '',
                'id_unit'            => $d['id_unit'] ?? null,
                'qty'                => !empty($coils) ? 0 : ($d['qty'] ?? 0),
                'harga_beli'         => $d['harga_beli'] ?? 0,
                'coils'              => $coils,
            ];
        }

        return $details;
    }

    /**
     * Hitung total_nilai_transaksi dan total_net_weight_transaksi
     * dari detail coils berdasarkan harga_beli (costbook) di warehouse_stock gudang asal.
     */
    private function _calculate_totals($details, $id_gudang_from)
    {
        $total_nilai      = 0;
        $total_net_weight = 0;

        foreach ($details as $detail) {
            // Ambil harga_beli (costbook) dari warehouse_stock gudang asal
            $stock = $this->db->select('harga_beli')
                ->from('warehouse_stock')
                ->where('code_lv4', $detail['code_lv4'])
                ->where('id_gudang', $id_gudang_from)
                ->get()->row();

            $costbook = $stock ? (float) $stock->harga_beli : (float) ($detail['harga_beli'] ?? 0);

            if (!empty($detail['coils'])) {
                foreach ($detail['coils'] as $coil) {
                    $nw = (float) $coil['net_weight'];
                    $total_net_weight += $nw;
                    $total_nilai      += $costbook * $nw;
                }
            }
        }

        return [
            'total_nilai'      => (int) round($total_nilai),
            'total_net_weight' => round($total_net_weight, 2),
        ];
    }

    private function _json($data)
    {
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
