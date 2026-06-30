<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approval_mutasi extends Admin_Controller
{
    protected $viewPermission   = 'Approval_mutasi.View';
    protected $managePermission = 'Approval_mutasi.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Approval_mutasi/approval_mutasi_model');
        $this->template->title('Approval Mutasi');
        $this->template->page_icon('fa fa-check-circle');

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
        $this->template->title('Approval Mutasi');
        $this->template->render('index');
    }

    // ---------------------------------------------------------------
    // RENDER PARTIAL TABLE PER TAB (AJAX)
    // ---------------------------------------------------------------

    public function render_pending()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->approval_mutasi_model->get_list([1]);
        $this->template->render('table/pending_mutation', $data);
    }

    public function render_approved()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->approval_mutasi_model->get_list([2]);
        $this->template->render('table/approved_mutation', $data);
    }

    public function render_rejected()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->approval_mutasi_model->get_list([3]);
        $this->template->render('table/rejected_mutation', $data);
    }

    public function render_revision()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->approval_mutasi_model->get_list([6]);
        $this->template->render('table/revision_mutation', $data);
    }

    // ---------------------------------------------------------------
    // FORM VIEW DETAIL
    // ---------------------------------------------------------------

    public function detail($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id) {
            redirect('approval_mutasi');
        }

        $mutation = $this->approval_mutasi_model->get_detail($id);

        if (!$mutation) {
            $this->session->set_flashdata('error', 'Data mutasi tidak ditemukan.');
            redirect('approval_mutasi');
        }

        $data['mutation'] = $mutation;
        $data['id']       = $id;

        $this->template->title('Detail Approval Mutasi');
        $this->template->render('detail', $data);
    }

    // ---------------------------------------------------------------
    // APPROVE (status 1 → 2, pindah stock)
    // ---------------------------------------------------------------

    public function approve($id)
    {
        $this->auth->restrict($this->managePermission);

        $mutation = $this->approval_mutasi_model->get_detail($id);

        if (!$mutation || $mutation['status'] != 1) {
            return $this->_json(['status' => 0, 'message' => 'Data tidak valid atau status sudah berubah.']);
        }

        $result = $this->approval_mutasi_model->approve_mutation($id, $this->username, $this->datetime);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Mutasi berhasil diapprove. Stock telah dipindahkan.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal melakukan approval.']);
    }

    // ---------------------------------------------------------------
    // REJECT (status 1 → 3, permanen ditolak)
    // ---------------------------------------------------------------

    public function reject($id)
    {
        $this->auth->restrict($this->managePermission);

        $reason = $this->input->post('reject_reason');

        if (empty(trim($reason))) {
            return $this->_json(['status' => 0, 'message' => 'Alasan reject wajib diisi.']);
        }

        $mutation = $this->approval_mutasi_model->get_detail($id);

        if (!$mutation || $mutation['status'] != 1) {
            return $this->_json(['status' => 0, 'message' => 'Data tidak valid atau status sudah berubah.']);
        }

        $result = $this->approval_mutasi_model->reject_mutation($id, $this->username, $this->datetime, $reason);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Mutasi berhasil ditolak.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal melakukan reject.']);
    }

    // ---------------------------------------------------------------
    // REVISI (status 1 → 6, dikembalikan untuk perbaikan)
    // ---------------------------------------------------------------

    public function revision($id)
    {
        $this->auth->restrict($this->managePermission);

        $reason = $this->input->post('reject_reason');

        if (empty(trim($reason))) {
            return $this->_json(['status' => 0, 'message' => 'Catatan revisi wajib diisi.']);
        }

        $mutation = $this->approval_mutasi_model->get_detail($id);

        if (!$mutation || $mutation['status'] != 1) {
            return $this->_json(['status' => 0, 'message' => 'Data tidak valid atau status sudah berubah.']);
        }

        $result = $this->approval_mutasi_model->revision_mutation($id, $this->username, $this->datetime, $reason);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Mutasi dikembalikan untuk revisi.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal mengembalikan untuk revisi.']);
    }

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    private function _json($data)
    {
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
