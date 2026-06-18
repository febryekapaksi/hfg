<?php
if (!defined('BASEPATH')) {
  exit('No direct script access allowed');
}

class Material_jenis extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Material_Jenis.View';
  protected $addPermission    = 'Material_Jenis.Add';
  protected $managePermission = 'Material_Jenis.Manage';
  protected $deletePermission = 'Material_Jenis.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->model(array(
      'Material_jenis/Material_jenis_model'
    ));
    $this->template->title('Manage Material Jenis');
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
    $listData = $this->Material_jenis_model->get_data($where);

    $data = [
      'result' =>  $listData,
      'get_level_1' =>  get_list_inventory_lv1('material'),
      'get_level_2' =>  get_list_inventory_lv2('material')
    ];

    history("View index material jenis");
    $this->template->set($data);
    $this->template->title('Boron / Non Boron');
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
      $generate_id = $this->Material_jenis_model->generate_id();

      $id         = $post['id'];
      $code_lv1   = $post['code_lv1'];
      $code_lv2   = $post['code_lv2'];
      $code       = (!empty($id)) ? $post['code'] : $generate_id;
      $status     = (!empty($id)) ? $post['status'] : 1;
      $nama       = $post['nama'];

      $last_by    = (!empty($id)) ? 'updated_by' : 'created_by';
      $last_date  = (!empty($id)) ? 'updated_date' : 'created_date';
      $label      = (!empty($id)) ? 'Edit' : 'Add';

      $dataProcess = [
        'category'  => 'material',
        'code_lv1'  => $code_lv1,
        'code_lv2'  => $code_lv2,
        'code_lv3'  => $code,
        'nama'      => $nama,
        // 'status'    => $status,
        $last_by    => $this->id_user,
        $last_date  => $this->datetime
      ];

      $this->db->trans_start();
      if (empty($id)) {
        $this->db->insert('new_inventory_3', $dataProcess);
      } else {
        $this->db->where('id', $id);
        $this->db->update('new_inventory_3', $dataProcess);
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
        history($label . " material category: " . $code);
      }
      echo json_encode($status);
    } else {
      $listData = $this->db->get_where('new_inventory_3', array('id' => $id))->result();
      $code_lv1 = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : 0;
      $data = [
        'listData' => $listData,
        'listLevel1' => get_list_inventory_lv1('material'),
        'listLevel2' => (!empty(get_list_inventory_lv2('material')[$code_lv1])) ? get_list_inventory_lv2('material')[$code_lv1] : array()
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
    $this->db->update('new_inventory_3', $dataProcess);

    // Menyelesaikan transaksi
    $this->db->trans_complete();

    // Tentukan pesan dan status berdasarkan hasil operasi
    if ($this->db->trans_status() === FALSE) {
      $keterangan = "FAILED, change status Jenis: $id";
      $status = 0;
    } else {
      $status_text = ($new_status == 1) ? 'Active' : 'Inactive';
      $keterangan = "SUCCESS, change status Jenis ID: $id to $status_text";
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
    $this->db->where('id', $id)->update("new_inventory_3", $data);

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
      history("Delete material category : " . $id);
    }
    echo json_encode($status);
  }

  public function get_list_level1($id = null)
  {
    $code_lv1 = $this->input->post('code_lv1');
    $result  = get_list_inventory_lv2('material');

    if (!empty($result[$code_lv1])) {
      $option  = "<option value='0'>Select Material Category</option>";
      foreach ($result[$code_lv1] as $val => $valx) {
        $sel = ($id == $valx['code_lv2']) ? 'selected' : '';
        $option .= "<option value='" . $valx['code_lv2'] . "' " . $sel . ">" . strtoupper($valx['nama']) . "</option>";
      }
    } else {
      $option  = "<option value='0'>List Not Found</option>";
    }

    $ArrJson  = array(
      'option' => $option
    );
    // exit;
    echo json_encode($ArrJson);
  }
}
