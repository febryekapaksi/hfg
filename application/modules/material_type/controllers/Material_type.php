<?php
if (!defined('BASEPATH')) {
  exit('No direct script access allowed');
}

class Material_type extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Material_Type.View';
  protected $addPermission    = 'Material_Type.Add';
  protected $managePermission = 'Material_Type.Manage';
  protected $deletePermission = 'Material_Type.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->model(array(
      'Material_type/Material_type_model'
    ));
    $this->template->title('Manage Material Type');
    $this->template->page_icon('fa fa-building-o');

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');

    $this->template->page_icon('fa fa-users');

    $where = [
      'deleted_date' => NULL,
      'category' => 'material'
    ];
    $listData = $this->Material_type_model->get_data($where);

    $data = [
      'result' =>  $listData
    ];

    history("View index material type");
    $this->template->set($data);
    $this->template->title('Metal Type');
    $this->template->render('index');
  }

  public function add($id = null)
  {
    if (empty($id)) {
      $this->auth->restrict($this->addPermission);
    } else {
      $this->auth->restrict($this->managePermission);
    }
    if ($this->input->post()) {
      $post = $this->input->post();
      $generate_id = $this->Material_type_model->generate_id();

      $id   = $post['id'];
      $code = (!empty($id)) ? $post['code'] : $generate_id;
      $status = (!empty($id)) ? $post['status'] : 1;
      $nama = $post['nama'];

      $last_by    = (!empty($id)) ? 'updated_by' : 'created_by';
      $last_date  = (!empty($id)) ? 'updated_date' : 'created_date';
      $label      = (!empty($id)) ? 'Edit' : 'Add';

      $dataProcess = [
        'category'  => 'material',
        'code_lv1'  => $code,
        'nama'      => $nama,
        // 'status'    => $status,
        $last_by    => $this->id_user,
        $last_date  => $this->datetime
      ];

      $this->db->trans_start();
      if (empty($id)) {
        $this->db->insert('new_inventory_1', $dataProcess);
      } else {
        $this->db->where('id', $id);
        $this->db->update('new_inventory_1', $dataProcess);
      }
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $status  = array(
          'pesan'    => 'Failed process data!',
          'status'  => 0
        );
      } else {
        $this->db->trans_commit();
        $status  = array(
          'pesan'    => 'Success process data!',
          'status'  => 1
        );
        history($label . " material type: " . $code);
      }
      echo json_encode($status);
    } else {
      $listData = $this->db->get_where('new_inventory_1', array('id' => $id))->result();

      $data = [
        'listData' => $listData
      ];
      $this->template->set($data);
      $this->template->render('add');
    }
  }

  public function toggle_status()
  {
    $this->auth->restrict($this->managePermission);

    // Ambil data dari POST
    $id = $this->input->post('id');  // Pastikan id diterima dengan benar
    $current_status = $this->input->post('status');  // Status yang dikirim dari checkbox

    // Tentukan status baru (toggle antara 1 dan 0)
    $new_status = ($current_status == 1) ? 0 : 1;

    // Persiapkan data untuk update
    $dataProcess = ['status' => $new_status];

    // Memulai transaksi untuk memastikan data konsisten
    $this->db->trans_start();

    // Update status berdasarkan ID
    $this->db->where('id', $id);
    $this->db->update('new_inventory_1', $dataProcess);

    // Menyelesaikan transaksi
    $this->db->trans_complete();

    // Tentukan pesan dan status berdasarkan hasil operasi
    if ($this->db->trans_status() === FALSE) {
      $keterangan = "FAILED, change status Type: $id";
      $status = 0;
    } else {
      $status_text = ($new_status == 1) ? 'Active' : 'Inactive';
      $keterangan = "SUCCESS, change status Type ID: $id to $status_text";
      $status = 1;
    }

    // Simpan aktivitas untuk audit trail
    $nm_hak_akses = $this->managePermission;
    $kode_universal = $id;
    $jumlah = 1;
    $sql = $this->db->last_query();  // Ambil query terakhir untuk logging
    simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

    // Kembalikan hasil sebagai JSON
    echo json_encode([
      'status' => $status,         // Status operasi
      'new_status' => $new_status, // Status baru setelah toggle
      'message' => $keterangan    // Pesan untuk memberitahukan hasil
    ]);
  }


  public function delete()
  {
    $this->auth->restrict($this->deletePermission);

    $id = $this->input->post('id');
    $data = [
      'deleted_by'     => $this->id_user,
      'deleted_date'   => $this->datetime
    ];

    $this->db->trans_begin();
    $this->db->where('id', $id)->update("new_inventory_1", $data);

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $status  = array(
        'pesan'    => 'Failed process data!',
        'status'  => 0
      );
    } else {
      $this->db->trans_commit();
      $status  = array(
        'pesan'    => 'Success process data!',
        'status'  => 1
      );
      history("Delete material type : " . $id);
    }
    echo json_encode($status);
  }
}
