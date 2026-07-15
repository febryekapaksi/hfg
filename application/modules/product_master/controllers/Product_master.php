<?php
if (!defined('BASEPATH')) {
  exit('No direct script access allowed');
}

class Product_master extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Product_Master.View';
  protected $addPermission    = 'Product_Master.Add';
  protected $managePermission = 'Product_Master.Manage';
  protected $deletePermission = 'Product_Master.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->model(array(
      'Product_master/Product_master_model'
    ));
    $this->template->title('Manage Product Jenis');
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
      'category' => 'product'
    ];
    $listData = $this->Product_master_model->get_data($where);

    $data = [
      'result' =>  $listData,
      'get_level_1' =>  $this->db->order_by('nama', 'asc')->get_where('product_lvl_1', array('category' => 'product', 'deleted_date' => NULL))->result_array(),
      'get_level_2' =>  $this->db->order_by('nama', 'asc')->get_where('product_lvl_2', array('category' => 'product', 'deleted_date' => NULL))->result_array(),
      'get_level_3' =>  $this->db->order_by('nama', 'asc')->get_where('product_lvl_3', array('category' => 'product', 'deleted_date' => NULL))->result_array(),
    ];

    history("View index product master");
    $this->template->set($data);
    $this->template->title('Product Master');
    $this->template->render('index');
  }

  public function get_json_product_master()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_product_master(
      $requestData['level1'],
      $requestData['level2'],
      $requestData['level3'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData      = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query          = $fetch['query'];

    $ENABLE_ADD     = has_permission('Product_Master.Add');
    $ENABLE_MANAGE  = has_permission('Product_Master.Manage');
    $ENABLE_VIEW    = has_permission('Product_Master.View');
    $ENABLE_DELETE  = has_permission('Product_Master.Delete');

    $get_level_1 = get_list_product_lv1('product');
    $get_level_2 = get_list_product_lv2('product');
    $get_level_3 = get_list_product_lv3('product');

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    foreach ($query->result_array() as $row) {
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = $urut1 + $start_dari;
      }
      if ($asc_desc == 'desc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }

      $product_type       = (!empty($get_level_1[$row['code_lv1']]['nama'])) ? $get_level_1[$row['code_lv1']]['nama'] : '';
      $product_category   = (!empty($get_level_2[$row['code_lv1']][$row['code_lv2']]['nama'])) ? $get_level_2[$row['code_lv1']][$row['code_lv2']]['nama'] : '';
      $product_jenis       = (!empty($get_level_3[$row['code_lv1']][$row['code_lv2']][$row['code_lv3']]['nama'])) ? $get_level_3[$row['code_lv1']][$row['code_lv2']][$row['code_lv3']]['nama'] : '';

      $nestedData   = array();
      $nestedData[]  = "<div align='left'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['code_lv4']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($product_type) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($product_category) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($product_jenis) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['code']) . "</div>";

      if ($row['status'] == '1') {
        $Label = "<label class='label label-success'>Aktif</label>";
      } else {
        $Label = "<label class='label label-danger'>Non Aktif</label>";
      }
      $nestedData[]  = "<div align='left'>" . $Label . "</div>";

      $edit  = "";
      $delete  = "";
      if ($ENABLE_MANAGE) {
        $edit = "<a class='btn btn-primary btn-sm edit' href='javascript:void(0)' title='Edit' data-id='" . $row['id'] . "'><i class='fa fa-edit'></i></a>";
      }
      if ($ENABLE_DELETE) {
        $delete = "&nbsp;<a class='btn btn-danger btn-sm delete' href='javascript:void(0)' title='Delete' data-id='" . $row['id'] . "'><i class='fa fa-trash'></i></a>";
      }

      $nestedData[]  = "<div align='center'>" . $edit . $delete . "</div>";
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function get_query_json_product_master($level1, $level2, $level3, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $WHERE_1 = "";
    if ($level1 != '0') {
      $WHERE_1 = " AND a.code_lv1 = '" . $level1 . "'";
    }

    $WHERE_2 = "";
    if ($level2 != '0') {
      $WHERE_2 = " AND a.code_lv2 = '" . $level2 . "'";
    }

    $WHERE_3 = "";
    if ($level3 != '0') {
      $WHERE_3 = " AND a.code_lv3 = '" . $level3 . "'";
    }

    $sql = "SELECT
                (@row:=@row+1) AS nomor,
                a.*
              FROM
                product_lvl_4 a,
                (SELECT @row:=0) r
              WHERE 
                a.deleted_date IS NULL 
                AND a.category='product' " . $WHERE_1 . $WHERE_2 . $WHERE_3 . "
                AND (
                  a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                  OR a.trade_name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                  OR a.code LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                  OR a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
      ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'code_lv1',
      2 => 'code_lv2',
      3 => 'code_lv3',
      4 => 'nama',
      5 => 'nama'
    );

    $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function add($id = null)
  {
    if (empty($id)) {
      $this->auth->restrict($this->addPermission);
    } else {
      $this->auth->restrict($this->managePermission);
    }

    if ($this->input->post()) {
      $post        = $this->input->post();
      $generate_id = $this->Product_master_model->generate_id();

      $id         = $post['id'];
      $code_lv1   = $post['code_lv1'];
      $code_lv2   = $post['code_lv2'];
      $code_lv3   = $post['code_lv3'];
      $code_lv4   = (!empty($id)) ? $post['code_lv4'] : $generate_id;
      $status     = (!empty($id)) ? $post['status'] : 1;
      $nama       = $post['nama'];
      $retail     = $post['retail'];
      $code       = $post['code'];
      $trade_name = $post['trade_name'];

      $id_unit_packing = $post['id_unit_packing'];
      $id_unit         = $post['id_unit'];
      $konversi        = str_replace(',', '', $post['konversi']);

      $max_stok = str_replace(',', '', $post['max_stok']);
      $min_stok = str_replace(',', '', $post['min_stok']);
      $moq      = str_replace(',', '', $post['moq']);

      $length = str_replace(',', '', $post['length']);
      $wide   = str_replace(',', '', $post['wide']);
      $high   = str_replace(',', '', $post['high']);
      
      $panjang = str_replace(',', '', $post['panjang']);
      $tebal   = str_replace(',', '', $post['tebal']);

      $weight = $post['weight'];
      $cub    = str_replace(',', '', $post['cub']);

      $last_by   = (!empty($id)) ? 'updated_by'   : 'created_by';
      $last_date = (!empty($id)) ? 'updated_date'  : 'created_date';
      $label     = (!empty($id)) ? 'Edit'          : 'Add';

      $dataProcess1 = [
        'category'        => 'product',
        'code_lv1'        => $code_lv1,
        'code_lv2'        => $code_lv2,
        'code_lv3'        => $code_lv3,
        'code_lv4'        => $code_lv4,
        'nama'            => $nama,
        'retail'          => $retail,
        'code'            => $code,
        'trade_name'      => $trade_name,
        'id_unit_packing' => $id_unit_packing,
        'id_unit'         => $id_unit,
        'konversi'        => $konversi,
        'length'          => $length,
        'panjang'         => $panjang,
        'tebal'           => $tebal,
        'max_stok'        => $max_stok,
        'min_stok'        => $min_stok,
        'moq'             => $moq,
        'wide'            => $wide,
        'high'            => $high,
        'weight'          => $weight,
        'cub'             => $cub,
        'status'          => $status,
        $last_by          => $this->id_user,
        $last_date        => $this->datetime,
      ];

      // Upload MSDS
      $dataProcess2 = [];
      if (!empty($_FILES['photo']['tmp_name'])) {
        $upload_dir   = get_root3() . '/uploads/product_lv_4/msds/';
        $link_dir     = 'uploads/product_lv_4/msds/';

        // Buat folder jika belum ada
        if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0755, true);
        }

        $name_file     = 'msds-' . $code_lv4 . '-' . date('Ymdhis');
        $target_file   = basename($_FILES['photo']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $nama_upload   = $upload_dir . $name_file . '.' . $imageFileType;

        move_uploaded_file($_FILES['photo']['tmp_name'], $nama_upload);
        $link_url = $link_dir . $name_file . '.' . $imageFileType;

        $dataProcess2 = ['file_msds' => $link_url];
      }

      $dataProcess = array_merge($dataProcess1, $dataProcess2);

      $this->db->trans_start();

      if (empty($id)) {
        // Insert product saja, tanpa warehouse
        $this->db->insert('product_lvl_4', $dataProcess);
      } else {
        // Update product
        $this->db->where('id', $id);
        $this->db->update('product_lvl_4', $dataProcess);

        // Update nama product di product_costing jika ada perubahan
        $this->db->where('code_lv4', $code_lv4);
        $this->db->update('product_costing', [
          'product_name' => $nama,
        ]);
      }

      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        $result = [
          'pesan'  => 'Failed process data!',
          'status' => 0,
        ];
      } else {
        $this->db->trans_commit();
        $result = [
          'pesan'  => 'Success process data!',
          'status' => 1,
        ];
        history($label . ' product master: ' . $code_lv4);
      }

      echo json_encode($result);
    } else {

      $listData       = $this->db->get_where('product_lvl_4', ['id' => $id])->result();
      $code_lv1       = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : 0;
      $code_lv2       = (!empty($listData[0]->code_lv2)) ? $listData[0]->code_lv2 : 0;
      $satuan         = $this->db->get_where('ms_satuan', ['deleted_date' => NULL, 'category' => 'unit'])->result();
      $satuan_packing = $this->db->get_where('ms_satuan', ['deleted_date' => NULL, 'category' => 'packing'])->result();

      $data = [
        'listData'      => $listData,
        'listLevel1'    => get_list_product_lv1('product'),
        'listLevel2'    => (!empty(get_list_product_lv2('product')[$code_lv1]))             ? get_list_product_lv2('product')[$code_lv1]             : [],
        'listLevel3'    => (!empty(get_list_product_lv3('product')[$code_lv1][$code_lv2])) ? get_list_product_lv3('product')[$code_lv1][$code_lv2] : [],
        'satuan'        => $satuan,
        'satuan_packing' => $satuan_packing,
      ];

      $this->template->set($data);
      $this->template->render('add');
    }
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
    $this->db->where('id', $id)->update("product_lvl_4", $data);

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
      history("Delete product master : " . $id);
    }
    echo json_encode($status);
  }

  public function get_list_level1($id = null)
  {
    $code_lv1 = $this->input->post('code_lv1');
    $result  = get_list_product_lv2('product');

    if (!empty($result[$code_lv1])) {
      $option  = "<option value='0'>Select Product Category</option>";
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

  public function get_list_level3($id = null)
  {
    $code_lv1 = $this->input->post('code_lv1');
    $code_lv2 = $this->input->post('code_lv2');
    $result  = get_list_product_lv3('product');

    if (!empty($result[$code_lv1][$code_lv2])) {
      $option  = "<option value='0'>Select Product Jenis</option>";
      foreach ($result[$code_lv1][$code_lv2] as $val => $valx) {
        $sel = ($id == $valx['code_lv3']) ? 'selected' : '';
        $option .= "<option value='" . $valx['code_lv3'] . "' " . $sel . ">" . strtoupper($valx['nama']) . "</option>";
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

  public function get_list_level4_name()
  {
    $code_lv1 = $this->input->post('code_lv1');
    $code_lv2 = $this->input->post('code_lv2');
    $code_lv3 = $this->input->post('code_lv3');

    $get_level_1 =  get_list_product_lv1('product');
    $get_level_2 =  get_list_product_lv2('product');
    $get_level_3 =  get_list_product_lv3('product');

    $product_type     = (!empty($get_level_1[$code_lv1]['nama'])) ? $get_level_1[$code_lv1]['nama'] : '';
    $product_category = (!empty($get_level_2[$code_lv1][$code_lv2]['nama'])) ? $get_level_2[$code_lv1][$code_lv2]['nama'] : '';
    $product_jenis     = (!empty($get_level_3[$code_lv1][$code_lv2][$code_lv3]['nama'])) ? $get_level_3[$code_lv1][$code_lv2][$code_lv3]['nama'] : '';

    $code_type       = (!empty($get_level_1[$code_lv1]['code'])) ? $get_level_1[$code_lv1]['code'] : '';
    $code_category  = (!empty($get_level_2[$code_lv1][$code_lv2]['code'])) ? $get_level_2[$code_lv1][$code_lv2]['code'] : '';
    $code_jenis     = (!empty($get_level_3[$code_lv1][$code_lv2][$code_lv3]['code'])) ? $get_level_3[$code_lv1][$code_lv2][$code_lv3]['code'] : '';


    $ArrJson  = array(
      'nama' => strtoupper($product_type . " " . $product_category . "; " . $product_jenis),
      'code' => $code_type . "-" . $code_category . "-" . $code_jenis,
    );
    // exit;
    echo json_encode($ArrJson);
  }

  public function download_excel()
  {
    // Matikan error output agar tidak mengganggu header
    @ini_set('display_errors', 0);
    error_reporting(0);
    set_time_limit(0);
    ini_set('memory_limit', '1024M');

    // Bersihkan buffer sebelum mulai
    while (ob_get_level()) {
      ob_end_clean();
    }

    $this->load->library("PHPExcel");

    $objPHPExcel     = new PHPExcel();
    $whiteCenterBold = whiteCenterBold();
    $mainTitle       = mainTitle();
    $tableBodyLeft   = tableBodyLeft();

    $sheet   = $objPHPExcel->getActiveSheet();
    $Row     = 1;
    $NewRow  = $Row + 1;

    // Hitung total kolom = 13 (A sampai M)
    $Col_Akhir = 'N';

    // ── TITLE ──────────────────────────────────────────────
    $sheet->setCellValue('A' . $Row, 'PRODUCT MASTER');
    $sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($mainTitle);
    $sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

    $NewRow  = $NewRow + 2;
    $NextRow = $NewRow;

    // ── HEADER KOLOM ──────────────────────────────────────
    $headers = [
      'A' => '#',
      'B' => 'PRODUCT TYPE',
      'C' => 'PRODUCT CATEGORY',
      'D' => 'PRODUCT JENIS',
      'E' => 'CODE PROGRAM',
      'F' => 'PRODUCT MASTER',
      'G' => 'PRODUCT CODE',
      'H' => 'TRADE NAME',
      'I' => 'PACKING UNIT',
      'J' => 'KONVERSI',
      'K' => 'UNIT MEASUREMENT',
      'L' => 'MOQ',
      'M' => 'MINIMUM STOK',
      'N' => 'BERAT/UNIT',
    ];

    foreach ($headers as $col => $label) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
      $sheet->setCellValue($col . $NewRow, $label);
      $sheet->getStyle($col . $NewRow . ':' . $col . $NextRow)->applyFromArray($whiteCenterBold);
      $sheet->mergeCells($col . $NewRow . ':' . $col . $NextRow);
    }

    // ── AMBIL DATA ─────────────────────────────
    $dataResult = $this->db
      ->where('category', 'product')
      ->where('deleted_date IS NULL')
      ->get('product_lvl_4')
      ->result_array();

    // var_dump($dataResult); die;
    $GET_UNIT   = get_list_satuan();
    $GET_LEVEL3 = get_product_lv3();
    $GET_LEVEL2 = get_product_lv2();
    $GET_LEVEL1 = get_list_product_lv1('product');

    $s = function ($val) {
      return ($val === null || $val === false) ? '' : (string)$val;
    };

    // ── ISI DATA ──────────────────────────────────────────
    if (!empty($dataResult)) {
      $awal_row = $NextRow;
      $no       = 0;

      foreach ($dataResult as $vals) {
        $no++;
        $awal_row++;

        $code_lv1 = $s(!empty($GET_LEVEL1[$vals['code_lv1']]['nama']) ? $GET_LEVEL1[$vals['code_lv1']]['nama'] : '');
        $code_lv2 = $s(!empty($GET_LEVEL2[$vals['code_lv2']]['nama']) ? $GET_LEVEL2[$vals['code_lv2']]['nama'] : '');
        $code_lv3 = $s(!empty($GET_LEVEL3[$vals['code_lv3']]['nama']) ? $GET_LEVEL3[$vals['code_lv3']]['nama'] : '');
        $unit_packing = $s(!empty($GET_UNIT[$vals['id_unit_packing']]['code']) ? $GET_UNIT[$vals['id_unit_packing']]['code'] : '');
        $unit         = $s(!empty($GET_UNIT[$vals['id_unit']]['code'])          ? $GET_UNIT[$vals['id_unit']]['code']          : '');

        $row_data = [
          'A' => $s($no),
          'B' => $code_lv1,
          'C' => $code_lv2,
          'D' => $code_lv3,
          'E' => $s($vals['code_lv4']),
          'F' => $s($vals['nama']),
          'G' => $s($vals['code']),
          'H' => $s($vals['trade_name']),
          'I' => $unit_packing,
          'J' => $s($vals['konversi']),
          'K' => $unit,
          'L' => $s($vals['max_stok']),
          'M' => $s($vals['min_stok']),
          'N' => $s($vals['weight']),
        ];

        foreach ($row_data as $col => $value) {
          $sheet->setCellValue($col . $awal_row, $value);
          $sheet->getStyle($col . $awal_row)->applyFromArray($tableBodyLeft);
        }
      }
    }

    // ── SAVE & OUTPUT ─────────────────────────────────────
    $sheet->setTitle('Product Master');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    // Pastikan buffer bersih sebelum kirim header
    while (ob_get_level()) {
      ob_end_clean();
    }

    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="product-master.xls"');

    $objWriter->save("php://output");
    exit;
  }
}
