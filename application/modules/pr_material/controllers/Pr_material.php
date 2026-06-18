<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pr_material extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'PR_Material.View';
    protected $addPermission    = 'PR_Material.Add';
    protected $managePermission = 'PR_Material.Manage';
    protected $deletePermission = 'PR_Material.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Pr_material/pr_material_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->page_icon('fa fa-cubes');
        $this->template->title('PR Material');
        $this->template->render('index');
    }

    public function add()
    {
        $this->template->page_icon('fa fa-cubes');
        $this->template->title('Re-order Point Material');
        $this->template->render('add');
    }

    public function data_side_material_planning()
    {
        $this->pr_material_model->get_data_json_material_planning();
    }

    public function server_side_reorder_point()
    {
        $this->pr_material_model->get_data_json_reorder_point();
    }

    public function save_reorder_change()
    {
        $data = $this->input->post();

        $id_material    = $data['id_material'];
        $purchase       = str_replace(',', '', $data['purchase']);
        $tanggal        = $data['tanggal'];
        $keterangan     = $data['keterangan'];
        $forecast       = str_replace(',', '', $data['forecast']);

        $ArrHeader = array(
            'request'           => $purchase,
            'tgl_dibutuhkan'    => $tanggal,
            'keterangan'        => $keterangan,
            'forecast'          => $forecast,
        );

        $this->db->trans_start();
        $this->db->where('code_lv4', $id_material);
        $this->db->update('new_inventory_4', $ArrHeader);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Change propose request material ' . $id_material . ' / ' . $purchase . ' / ' . $tanggal);
        }
        echo json_encode($Arr_Data);
    }

    public function save_reorder_all()
    {
        $data = $this->input->post();

        $Ym       = date('ym'); // contoh: 2407
        $prefix   = "SPR";

        $qIPP = "SELECT MAX(so_number) AS maxP 
         FROM material_planning_base_on_produksi 
         WHERE so_number LIKE '{$prefix}{$Ym}%'";

        $resultIPP  = $this->db->query($qIPP)->row_array();
        $lastNumber = $resultIPP['maxP'];

        $urutan = ($lastNumber) ? (int)substr($lastNumber, strlen($prefix . $Ym), 5) : 0;
        $urutan++;

        $urutFormatted = sprintf('%05d', $urutan);
        $so_number     = $prefix . $Ym . $urutFormatted;

        $getraw_materials   = $this->db->get_where('new_inventory_4', array('request >' => 0))->result_array();

        $ArrSaveDetail = [];
        $SUM = 0;
        $SUM_FORECAST = 0;
        foreach ($getraw_materials as $key => $value) {
            $SUM += $value['request'];
            $ArrSaveDetail[$key]['so_number']           = $so_number;
            $ArrSaveDetail[$key]['id_material']         = $value['code_lv4'];
            $ArrSaveDetail[$key]['propose_purchase']    = $value['request'];
            $ArrSaveDetail[$key]['note']                = $value['keterangan'];
            $SUM_FORECAST                                 += $value['forecast'];
        }

        $ArrSaveHeader = array(
            'so_number'         => $so_number,
            'no_pr'             => generateNoPR(),
            'category'          => 'pr material',
            'tgl_so'            => date('Y-m-d'),
            'id_customer'       => 'C100-2401002',
            'project'           => 'Pengisian Stok Material',
            'qty_propose'       => $SUM,
            'qty_forecast'      => $SUM_FORECAST,
            'tgl_dibutuhkan'    => $value['tgl_dibutuhkan'],
            'created_by'        => $this->auth->user_id(),
            'created_date'      => date('Y-m-d H:i:s'),
            'booking_by'        => $this->auth->user_id(),
            'booking_date'      => date('Y-m-d H:i:s'),
            'tingkat_pr'        => $data['tingkat_pr'],
            'app_post'          => '3',
            'app_1'             => '1',
            'app_2'             => '1',
        );

        $this->db->trans_start();
        $this->db->insert('material_planning_base_on_produksi', $ArrSaveHeader);
        if (!empty($ArrSaveDetail)) {
            $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrSaveDetail);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Save pengajuan propose material all');
        }
        echo json_encode($Arr_Data);
    }

    public function save_reorder_change_date()
    {
        $data = $this->input->post();

        $tanggal        = $data['tanggal'];
        $get_materials  = $this->db->get_where('new_inventory_4', array('category' => 'material'))->result_array();

        foreach ($get_materials as $key => $value) {
            $ArrUpdate[$key]['code_lv4']        = $value['code_lv4'];
            $ArrUpdate[$key]['tgl_dibutuhkan']  = $tanggal;
        }

        $this->db->trans_start();
        $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Change propose request material tgl dibutuhkan all ' . $tanggal);
        }
        echo json_encode($Arr_Data);
    }

    public function set_update_propose_reorder()
    {
        $data = $this->input->post();
        $tgl_now = date('Y-m-d');
        $GET_OUTANDING_PR   = get_pr_on_progress();
        $tgl_next_month     = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
        $get_materials      = $this->db
            ->select('a.*, b.qty_stock')
            ->join('warehouse_stock b', 'a.code_lv4 = b.code_lv4 AND b.id_gudang = 1', 'left')
            ->get_where('new_inventory_4 a', array('a.category' => 'material'))
            ->result_array();

        foreach ($get_materials as $key => $value) {
            $outanding_pr   = (!empty($GET_OUTANDING_PR[$value['code_lv4']]) and $GET_OUTANDING_PR[$value['code_lv4']] > 0) ? $GET_OUTANDING_PR[$value['code_lv4']] : 0;

            $QTY_PR = NULL;
            if ($value['qty_stock'] < $value['min_stok']) {
                $QTY_PR = ($value['max_stok'] - ($value['qty_stock'] + $outanding_pr));
                $QTY_PR = ($QTY_PR < 0) ? NULL : $QTY_PR;
            }

            $ArrUpdate[$key]['code_lv4']        = $value['code_lv4'];
            $ArrUpdate[$key]['request']         = $QTY_PR;
            $ArrUpdate[$key]['tgl_dibutuhkan']  = $tgl_next_month;
        }

        $this->db->trans_start();
        $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'   => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'   => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Set propose request material');
        }
        echo json_encode($Arr_Data);
    }

    public function clear_update_reorder()
    {
        $data               = $this->input->post();
        $tgl_now            = date('Y-m-d');
        $tgl_next_month     = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
        $get_materials      = $this->db->get_where('new_inventory_4', array('category' => 'material'))->result_array();

        foreach ($get_materials as $key => $value) {
            $ArrUpdate[$key]['code_lv4']        = $value['code_lv4'];
            $ArrUpdate[$key]['request']         = 0;
            $ArrUpdate[$key]['forecast']         = 0;
            $ArrUpdate[$key]['tgl_dibutuhkan']  = $tgl_next_month;
        }

        $this->db->trans_start();
        $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'   => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'   => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Clear all propose request material');
        }
        echo json_encode($Arr_Data);
    }

    public function detail_planning($so_number = null)
    {
        // Ambil header
        $header = $this->db
            ->select('a.*, b.due_date, c.name_customer')
            ->join('so_internal b', 'a.so_number = b.so_number', 'left')
            ->join('master_customers c', 'a.id_customer = c.id_customer', 'left')
            ->get_where('material_planning_base_on_produksi a', ['a.so_number' => $so_number])
            ->result_array();

        // Ambil detail
        $detail = $this->db
            ->select('a.*, b.max_stok, b.min_stok')
            ->join('new_inventory_4 b', 'a.id_material = b.code_lv4', 'left')
            ->get_where('material_planning_base_on_produksi_detail a', ['a.so_number' => $so_number])
            ->result_array();

        // Ambil data inventory level 4 (dulunya dari get_inventory_lv4)
        $GET_LEVEL4 = [];
        $query = $this->db->select('code_lv4, nama')
            ->from('new_inventory_4')
            ->where('deleted_date IS NULL')
            ->get()
            ->result_array();
        foreach ($query as $row) {
            $GET_LEVEL4[$row['code_lv4']] = ['nama' => $row['nama']];
        }

        // Ambil stok pusat (dulunya dari getStokMaterial)
        $get_stok_pusat = [];
        $stok = $this->db
            ->select('a.code_lv4, a.qty_stock, a.qty_booking, b.konversi')
            ->join('new_inventory_4 b', 'a.code_lv4 = b.code_lv4')
            ->get_where('warehouse_stock a', ['a.id_gudang' => 1])
            ->result_array();

        foreach ($stok as $s) {
            $stok_packing = 0;
            if ($s['qty_stock'] > 0 && $s['konversi'] > 0) {
                $stok_packing = $s['qty_stock'] / $s['konversi'];
            }
            $get_stok_pusat[$s['code_lv4']] = [
                'stok'          => $s['qty_stock'],
                'booking'       => $s['qty_booking'],
                'stok_packing'  => $stok_packing,
                'konversi'      => $s['konversi']
            ];
        }

        // Kirim ke view
        $data = [
            'so_number'       => $so_number,
            'header'          => $header,
            'detail'          => $detail,
            'GET_LEVEL4'      => $GET_LEVEL4,
            'GET_STOK_PUSAT'  => $get_stok_pusat
        ];

        $this->template->page_icon('fa fa-cart-plus');
        $this->template->title('Detail Purchase Request : ' . $so_number);
        $this->template->render('detail_planning', $data);
    }


    public function edit_planning($so_number = null)
    {
        // Ambil header
        $header = $this->db
            ->select('a.*, b.due_date, c.name_customer')
            ->join('so_internal b', 'a.so_number = b.so_number', 'left')
            ->join('master_customers c', 'a.id_customer = c.id_customer', 'left')
            ->get_where('material_planning_base_on_produksi a', ['a.so_number' => $so_number])
            ->result_array();

        // Ambil detail
        $detail = $this->db
            ->select('a.*, b.max_stok, b.min_stok')
            ->join('new_inventory_4 b', 'a.id_material = b.code_lv4', 'left')
            ->get_where('material_planning_base_on_produksi_detail a', ['a.so_number' => $so_number])
            ->result_array();

        // Ambil data inventory level 4 (dulunya dari get_inventory_lv4)
        $GET_LEVEL4 = [];
        $query = $this->db->select('code_lv4, nama, trade_name')
            ->from('new_inventory_4')
            ->where('deleted_date IS NULL')
            ->get()
            ->result_array();
        foreach ($query as $row) {
            $GET_LEVEL4[$row['code_lv4']] = ['nama' => $row['nama']];
            $GET_LEVEL4[$row['code_lv4']] = ['trade_name' => $row['trade_name']];
        }

        // Ambil stok pusat (dulunya dari getStokMaterial)
        $get_stok_pusat = [];
        $stok = $this->db
            ->select('a.code_lv4, a.qty_stock, a.qty_booking, b.konversi')
            ->join('new_inventory_4 b', 'a.code_lv4 = b.code_lv4')
            ->get_where('warehouse_stock a', ['a.id_gudang' => 1])
            ->result_array();

        foreach ($stok as $s) {
            $stok_packing = 0;
            if ($s['qty_stock'] > 0 && $s['konversi'] > 0) {
                $stok_packing = $s['qty_stock'] / $s['konversi'];
            }
            $get_stok_pusat[$s['code_lv4']] = [
                'stok'          => $s['qty_stock'],
                'booking'       => $s['qty_booking'],
                'stok_packing'  => $stok_packing,
                'konversi'      => $s['konversi']
            ];
        }

        // NON PR 
        $this->db->select('a.*');
        $this->db->from('new_inventory_4 a');
        $this->db->where('a.category', 'material');
        $this->db->where('(SELECT COUNT(aa.id) FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = "' . $so_number . '" AND aa.id_material = a.code_lv4) <', 1);
        $list_material_non_pr = $this->db->get()->result_array();

        // Kirim ke view
        $data = [
            'so_number'             => $so_number,
            'header'                => $header,
            'detail'                => $detail,
            'list_material_non_pr'  => $list_material_non_pr,
            'GET_LEVEL4'            => $GET_LEVEL4,
            'GET_STOK_PUSAT'        => $get_stok_pusat
        ];

        $this->template->page_icon('fa fa-edit');
        $this->template->title('Edit PR : ' . $so_number);
        $this->template->render('edit_planning', $data);
    }

    // public function process_update_all()
    // {
    //     $data       = $this->input->post();
    //     $detail      = $data['detail'];
    //     $so_number  = $data['so_number'];

    //     $ArrUpdate = [];
    //     foreach ($detail as $key => $value) {
    //         $ArrUpdate[$key]['id'] = $value['id'];
    //         $ArrUpdate[$key]['propose_purchase'] = str_replace(',', '', $value['qty']);
    //         $ArrUpdate[$key]['note'] = $value['note'];
    //     }

    //     $get_pr = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $so_number])->row();


    //     $this->db->trans_start();
    //     $this->db->update('material_planning_base_on_produksi', [
    //         'no_rev' => ($get_pr->no_rev + 1),
    //         'reject_status' => '0',
    //         'tgl_dibutuhkan' => $data['tgl_dibutuhkan'],
    //         'tingkat_pr' => $data['tingkat_pr'],
    //         // 'keterangan_1' => $data['keterangan_1'],
    //         // 'keterangan_2' => $data['keterangan_2'],
    //         'keterangan_3' => $data['keterangan_3'],
    //         'sts_reject3' => null,
    //         'sts_reject3_by' => null,
    //         'sts_reject3_date' => null,
    //         'rejected' => null,
    //         'app_post' => 3,
    //     ], ['so_number' => $so_number]);
    //     if (!empty($ArrUpdate)) {
    //         $this->db->update_batch('material_planning_base_on_produksi_detail', $ArrUpdate, 'id');
    //     }
    //     $this->db->trans_complete();

    //     if ($this->db->trans_status() === FALSE) {
    //         $this->db->trans_rollback();
    //         $Arr_Data  = array(
    //             'pesan'    => 'Process Failed !',
    //             'status'  => 0,
    //             'so_number'  => $so_number
    //         );
    //     } else {
    //         $this->db->trans_commit();
    //         $Arr_Data  = array(
    //             'pesan'    => 'Process Success !',
    //             'status'  => 1,
    //             'so_number'  => $so_number
    //         );
    //         history("Update qty pr material  : " . $so_number);
    //     }
    //     echo json_encode($Arr_Data);
    // }

    public function process_update_all()
    {
        $data      = $this->input->post();
        $detail    = $data['detail'] ?? [];
        $so_number = $data['so_number'];

        $ArrUpdate = [];
        foreach ($detail as $key => $value) {
            if (!isset($value['id']) || !isset($value['qty'])) {
                continue;
            }

            $ArrUpdate[] = [
                'id'               => $value['id'],
                'propose_purchase' => (float) str_replace(',', '', $value['qty']),
                'note'             => $value['note'] ?? '',
            ];
        }

        $get_pr = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $so_number])->row();

        $this->db->trans_start();

        $this->db->update('material_planning_base_on_produksi', [
            'no_rev'           => ($get_pr->no_rev + 1),
            'reject_status'    => '0',
            'tgl_dibutuhkan'   => $data['tgl_dibutuhkan'],
            'tingkat_pr'       => $data['tingkat_pr'],
            'keterangan_3'     => $data['keterangan_3'],
            'sts_reject3'      => null,
            'sts_reject3_by'   => null,
            'sts_reject3_date' => null,
            'rejected'         => null,
            'app_3'            => null, 
            'app_post'         => 3,
        ], ['so_number' => $so_number]);

        if (!empty($ArrUpdate)) {
            $this->db->update_batch('material_planning_base_on_produksi_detail', $ArrUpdate, 'id');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = [
                'pesan'     => 'Process Failed !',
                'status'    => 0,
                'so_number' => $so_number
            ];
        } else {
            $this->db->trans_commit();
            $Arr_Data = [
                'pesan'     => 'Process Success !',
                'status'    => 1,
                'so_number' => $so_number
            ];
            history("Update qty pr material: " . $so_number);
        }

        echo json_encode($Arr_Data);
    }

    public function print_new($so_number = null)
    {
        if ($so_number === null) {
            $so_number = $this->uri->segment(3);
        }

        $header = $this->db->query("
        SELECT a.*
        FROM material_planning_base_on_produksi a
        WHERE a.so_number = ?
        LIMIT 1
        ", [$so_number])->row();

        if (!$header) {
            show_error('SO not found: ' . htmlspecialchars($so_number), 404);
            return;
        }

        $detail = $this->db->query("
        SELECT d.*,
               p.nama                AS material,
               p.weight              AS berat,
               s1.code               AS satuan,
               w.harga_beli          AS harga,
               w.code_material        AS item       
        FROM material_planning_base_on_produksi_detail d
        LEFT JOIN new_inventory_4 p ON p.code_lv4 = d.id_material
        LEFT JOIN warehouse_stock w ON w.id_material = d.id_material
        LEFT JOIN ms_satuan s1 ON s1.id = p.id_unit
        WHERE d.so_number = ?
        ORDER BY d.id ASC
        ", [$so_number])->result();

        $data = [
            'printby'       => $this->session->userdata('app_session')['id_user'] ?? '',
            'header'        => $header,
            'detail'        => $detail,
            'kode'          => $so_number,
        ];

        ob_clean();
        ob_start();
        $this->load->view('print_new', $data);
        $html = ob_get_clean();

        $this->load->view('print_new', $data);
    }


    public function print_new2()
    {
        $kode  = $this->uri->segment(3);
        $data_session  = $this->session->userdata;
        $session        = $this->session->userdata('app_session');
        $printby    = $session['id_user'];

        $data_url    = base_url();
        $Split_Beda    = explode('/', $data_url);
        $Jum_Beda    = count($Split_Beda);
        $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

        $getData        = $this->db->get_where('material_planning_base_on_produksi', array('so_number' => $kode))->result_array();
        $getDataDetail  = $this->db->get_where('material_planning_base_on_produksi_detail', array('so_number' => $kode))->result_array();
        $getCustomer    = $this->db->get_where('master_customers', array('id_customer' => $getData[0]['id_customer']))->result_array();

        $data = array(
            'Nama_Beda' => $Nama_Beda,
            'printby' => $printby,
            'getData' => $getData,
            'getDataDetail' => $getDataDetail,
            'getCustomer' => $getCustomer,
            'GET_DET_Lv4' => get_inventory_lv4(),
            'GET_ACCESSORIES' => get_accessories(),
            'kode' => $kode
        );
        $this->load->view('print_new', $data);
    }

    public function edit_detail()
    {
        $post = $this->input->post();

        $valid = 1;
        $this->db->trans_begin();

        $this->db->update('material_planning_base_on_produksi_detail', [
            'propose_purchase'  => $post['qty_pr'],
            'note'              => $post['notes']
        ], [
            'id' => $post['id']
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function refresh_pr_detail()
    {
        $post = $this->input->post();
        $so_number = $post['so_number'];

        $detail     = $this->db
            ->select('a.*, b.max_stok, b.min_stok, b.nama as nm_material')
            ->join('new_inventory_4 b', 'a.id_material=b.code_lv4', 'left')
            ->get_where(
                'material_planning_base_on_produksi_detail a',
                array(
                    'a.so_number' => $so_number
                )
            )
            ->result_array();

        $hasil = '';
        $GET_LEVEL4 = get_inventory_lv4();
        $GET_STOK_PUSAT = getStokMaterial(1);
        foreach ($detail as $key => $value) {
            $key++;
            $nm_material   = (!empty($GET_LEVEL4[$value['id_material']]['nama'])) ? $GET_LEVEL4[$value['id_material']]['nama'] : '';
            $stock_free   = $value['stock_free'];
            $use_stock     = $value['use_stock'];
            $sisa_free     = $stock_free - $use_stock;
            $propose     = $value['propose_purchase'];



            if ($propose > 0) {
                $hasil .= "<tr>";
                $hasil .= "<td class='text-center'>" . $key . "</td>";
                $hasil .= "	<td class='text-left'>" . $value['nm_material'] . "
            
            </td>";
                $hasil .= "<td class='text-right min_stok'>" . number_format($value['min_stok'], 2) . "</td>";
                $hasil .= "<td class='text-right max_stok'>" . number_format($value['max_stok'], 2) . "</td>";
                $hasil .= "<td class='text-right min_order'>" . number_format(0, 2) . "</td>";
                if ($value['status_app'] == 'N') {
                    $hasil .= "<td align='center'>";
                    $hasil .= "<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>";
                    $hasil .= "<input type='text' name='detail[" . $key . "][qty]' class='form-control input-sm text-center qty_pr_" . $value['id'] . " autoNumeric2' style='width:100px;' value='" . $propose . "'>";
                    $hasil .= "</td>";
                    $hasil .= "<td class='text-center'><span class='badge bg-blue text-bold'>Waiting Process</span></td>";
                }
                if ($value['status_app'] == 'Y') {
                    $hasil .= "<td class='text-center'>" . number_format($propose, 2) . "</td>";
                    $hasil .= "<td class='text-center'><span class='badge bg-green text-bold'>Approved</span></td>";
                }
                if ($value['status_app'] == 'D') {
                    $hasil .= "<td class='text-center'>" . number_format($propose, 2) . "</td>";
                    $hasil .= "<td class='text-center'><span class='badge bg-red text-bold'>Rejected</span></td>";
                }
                $hasil .= "<td class='text-center'><input type='text' class='form-control notes_" . $value['id'] . "' name='detail[" . $key . "][note]' value='" . $value['note'] . "'></td>";
                $hasil .= '<td class="text-center">
            <button type="button" class="btn btn-sm btn-warning edit_detail" data-id="' . $value['id'] . '"><i class="fa fa-edit"></i></button>
            <button type="button" class="btn btn-sm btn-danger del_detail" data-id="' . $value['id'] . '"><i class="fa fa-trash"></i></button>
          </td>';
                $hasil .= "</tr>";
            }
        }

        echo $hasil;
    }

    public function del_detail()
    {
        $id = $this->input->post('id');

        $this->db->trans_begin();

        $this->db->delete('material_planning_base_on_produksi_detail', ['id' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function add_material()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $ArrData = [
            'so_number'         => $post['so_number'],
            'id_material'       => $post['id_material'],
            'propose_purchase'  => $post['qty_pr'],
            'status_app'        => 'N',
            'note'              => $post['notes']
        ];
        $this->db->insert('material_planning_base_on_produksi_detail', $ArrData);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $valid = 0;
            $msg = "Sorry, please try again !";
        } else {
            $this->db->trans_commit();

            $valid = 1;
            $msg = "Success, new material has been added";
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function get_refresh_material()
    {
        $post = $this->input->post();

        $arr_pr_material = [];
        $get_pr_material = $this->db->select('id_material')->get_where('material_planning_base_on_produksi_detail', ['so_number' => $post['so_number']])->result_array();
        foreach ($get_pr_material as $pr_material) {
            $arr_pr_material[] = $pr_material['id_material'];
        }
        var_dump($arr_pr_material);
        exit;

        $this->db->select('a.code_lv4, a.nama');
        $this->db->from('new_inventory_4 a');
        $this->db->where('a.category', 'material');
        $this->db->where_not_in('a.code_lv4', $arr_pr_material);
        $get_material_non_pr = $this->db->get()->result_array();

        $hasil = '';
        $no = 1;
        foreach ($get_material_non_pr as $material_non_pr) {
            $hasil .= '<tr>';
            $hasil .= '<td class="text-center">' . $no . '</td>';
            $hasil .= '<td>' . $material_non_pr['nama'] . '</td>';
            $hasil .= '<td class="text-right">' . number_format($material_non_pr['min_stok'], 2) . '</td>';
            $hasil .= '<td class="text-right">' . number_format($material_non_pr['max_stok'], 2) . '</td>';
            $hasil .= '<td class="text-right">' . number_format(0, 2) . '</td>';
            $hasil .= '<td><input type="text" class="form-control form-control-sm autoNumeric2 nmat_qty_pr_' . $material_non_pr['code_lv4'] . '" data-id_material="' . $material_non_pr['code_lv4'] . '"></td>';
            $hasil .= '<td><input type="text" class="form-control form-control-sm nmat_notes_' . $material_non_pr['code_lv4'] . '" data-id_material="' . $material_non_pr['code_lv4'] . '"></td>';
            $hasil .= '<td class="text-center"><button type="button" class="btn btn-sm btn-success add_material_pr add_material_pr_' . $material_non_pr['code_lv4'] . '" data-id_material="' . $material_non_pr['code_lv4'] . '"><i class="fa fa-plus"></i></button></td>';
            $hasil .= '</tr>';

            $no++;
        }

        echo $hasil;
    }

    public function close_pr_modal()
    {
        $so_number = $this->input->post('so_number');

        $get_no_pr = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $so_number])->row();

        $this->template->set('no_pr', $get_no_pr->no_pr);
        $this->template->set('so_number', $so_number);
        $this->template->render('close_pr_modal');
    }

    public function close_pr()
    {
        $so_number = $this->input->post('so_number');
        $close_pr_reason = $this->input->post('close_pr_reason');

        $this->db->trans_start();

        $update_close_pr = $this->db->update('material_planning_base_on_produksi', ['close_pr' => 1, 'close_pr_desc' => $close_pr_reason], ['so_number' => $so_number]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $valid = 0;
        } else {
            $this->db->trans_commit();

            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }
}
