<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Master_jurnal_mapping extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->template->title('Master Jurnal Mapping');
        $this->template->page_icon('fa fa-list');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        // $this->auth->restrict('Master_Jurnal_Mapping.View'); // Bypass permission for now
        $this->db->order_by('menu', 'ASC');
        $this->db->order_by('action', 'ASC');
        $data = $this->db->get('ms_jurnal_mapping')->result();

        $this->template->set('results', $data);
        $this->template->title('Master Jurnal Mapping');
        $this->template->render('index');
    }

    public function save()
    {
        // $this->auth->restrict('Master_Jurnal_Mapping.Manage');
        $post = $this->input->post();

        if(!empty($post['id'])) {
            $id = $post['id'];
            
            $data_update = [
                'kode_master_jurnal' => $post['kode_master_jurnal'],
                'keterangan'         => $post['keterangan'] ?? null,
                'updated_by'         => (isset($this->auth)) ? $this->auth->user_name() : 'Admin',
                'updated_on'         => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $id);
            $this->db->update('ms_jurnal_mapping', $data_update);

            echo json_encode(['status' => 1, 'message' => 'Data berhasil diupdate']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Data ID tidak ditemukan']);
        }
    }
}
