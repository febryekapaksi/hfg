<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App_pr_material extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Approval_PR_Material.View';
    protected $addPermission    = 'Approval_PR_Material.Add';
    protected $managePermission = 'Approval_PR_Material.Manage';
    protected $deletePermission = 'Approval_PR_Material.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('App_pr_material/app_pr_material_model'));
        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index()
    {
        $this->template->title('Approval PR Material');
        $this->template->render('index');
    }

    public function data_side_approval()
    {
        $this->app_pr_material_model->get_json_data_approval();
    }

    public function approval_planning($so_number = null)
    {
        if ($this->input->post()) {
            $data       = $this->input->post();
            $session    = $this->session->userdata('app_session');

            $so_number        = $data['so_number'];
            $tgl_dibutuhkan   = (!empty($data['tgl_dibutuhkan'])) ? date('Y-m-d', strtotime($data['tgl_dibutuhkan'])) : NULL;
            $detail           = $data['detail'];

            $ArrPlanningDetail = [];
            $SUM_USE = 0;
            $SUM_PROPOSE = 0;
            if (!empty($detail)) {
                foreach ($detail as $key => $value) {
                    //Planning
                    $use_stock = str_replace(',', '', $value['use_stock']);
                    $propose = str_replace(',', '', $value['propose']);

                    $ArrPlanningDetail[$key]['id'] = $value['id'];
                    $ArrPlanningDetail[$key]['stock_free'] = $value['stock_free'];
                    $ArrPlanningDetail[$key]['min_stock'] = $value['min_stok'];
                    $ArrPlanningDetail[$key]['max_stock'] = $value['max_stok'];
                    $ArrPlanningDetail[$key]['use_stock'] = $use_stock;
                    $ArrPlanningDetail[$key]['propose_purchase'] = $propose;

                    $SUM_USE += $use_stock;
                    $SUM_PROPOSE += $propose;
                }
            }

            $ArrHeader = array(
                'tgl_dibutuhkan'  => $tgl_dibutuhkan,
                'qty_use_stok'  => $SUM_USE,
                'qty_propose'  => $SUM_PROPOSE,
                'updated_by'      => $this->id_user,
                'updated_date'    => $this->datetime
            );

            $this->db->trans_start();
            $this->db->where('so_number', $so_number);
            $this->db->update('material_planning_base_on_produksi', $ArrHeader);

            if (!empty($ArrPlanningDetail)) {
                $this->db->update_batch('material_planning_base_on_produksi_detail', $ArrPlanningDetail, 'id');
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Data  = array(
                    'pesan'    => 'Save failed. Please try again later...',
                    'status'  => 0
                );
            } else {
                $this->db->trans_commit();
                $Arr_Data  = array(
                    'pesan'    => 'Save successful. Thank you.',
                    'status'  => 1
                );
                history("Create material planning  : " . $so_number);
            }
            echo json_encode($Arr_Data);
        } else {
            // Ambil header
            $header = $this->db
                ->select('a.*, b.due_date, c.name_customer')
                ->join('so_internal b', 'a.so_number = b.so_number', 'left')
                ->join('master_customers c', 'a.id_customer = c.id_customer', 'left')
                ->get_where('material_planning_base_on_produksi a', ['a.so_number' => $so_number])
                ->result_array();

            // Ambil detail
            $detail = $this->db
                ->select('a.*, b.max_stok, b.min_stok, b.nama, b.trade_name')
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

            $this->template->title('Approval PR : ' . $so_number);
            $this->template->render('approval_planning', $data);
        }
    }

    public function detail_planning($so_number = null)
    {
        $post = $this->input->post();
        $so_number = $post['so_number'];
        // Ambil header
        $header = $this->db
            ->select('a.*, b.due_date, c.name_customer')
            ->join('so_internal b', 'a.so_number = b.so_number', 'left')
            ->join('master_customers c', 'a.id_customer = c.id_customer', 'left')
            ->get_where('material_planning_base_on_produksi a', ['a.so_number' => $so_number])
            ->result_array();

        // Ambil detail
        $detail = $this->db
            ->select('a.*, b.max_stok, b.min_stok, b.nama')
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

    public function process_approval_satuan()
    {
        $data           = $this->input->post();
        $id             = $data['id'];
        $action         = $data['action'];
        $so_number      = $data['so_number'];
        $pr_rev         = str_replace(',', '', $data['pr_rev']);

        $ArrHeader = array(
            'propose_rev'   => ($action == 'approve') ? $pr_rev : NULL,
            'status_app'    => ($action == 'approve') ? 'Y' : 'D',
            'app_by'        => $this->id_user,
            'app_date'      => $this->datetime
        );

        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->update('material_planning_base_on_produksi_detail', $ArrHeader);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Process Failed !',
                'status'  => 0,
                'so_number'  => $so_number
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Process Success !',
                'status'  => 1,
                'so_number'  => $so_number
            );
            history($action . " satuan pr material  : " . $id);
        }
        echo json_encode($Arr_Data);
    }

    public function process_approval_all()
    {
        $data       = $this->input->post();
        $check      = $data['check'];
        $so_number  = $data['so_number'];
        $tingkat_approval = $data['tingkat_approval'];

        $ArrUpdateHeader = [];
        $ArrUpdate = [];
        if ($tingkat_approval == '3') :
            $ArrUpdateHeader = [
                'app_3' => 1,
                'app_3_by' => $this->auth->user_id(),
                'app_3_date' => date('Y-m-d H:i:s'),
                'app_post' => 4
            ];

            foreach ($check as $key => $value) {
                $ArrUpdate[$key]['id'] = $value;
                $ArrUpdate[$key]['propose_rev'] = str_replace(',', '', $data['pr_rev_' . $value]);
                $ArrUpdate[$key]['status_app'] = 'Y';
                $ArrUpdate[$key]['app_by'] = $this->id_user;
                $ArrUpdate[$key]['app_date'] = $this->datetime;
            }
        else :
            $ArrUpdateHeader = [
                'app_' . $tingkat_approval => 1,
                'app_' . $tingkat_approval . '_by' => $this->auth->user_id(),
                'app_' . $tingkat_approval . '_date' => date('Y-m-d H:i:s'),
                'keterangan_' . $tingkat_approval => $data['keterangan_' . $tingkat_approval],
                'app_post' => ($tingkat_approval == '2') ? 3 : 2
            ];
        endif;

        $this->db->trans_start();

        if (!empty($ArrUpdateHeader)) {
            $this->db->update('material_planning_base_on_produksi', $ArrUpdateHeader, ['so_number' => $so_number]);
        }
        if (!empty($ArrUpdate)) {
            $this->db->update_batch('material_planning_base_on_produksi_detail', $ArrUpdate, 'id');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Process Failed !',
                'status'  => 0,
                'so_number'  => $so_number
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Process Success !',
                'status'  => 1,
                'so_number'  => $so_number
            );
            history("Approve pr material  : " . $so_number);
        }
        echo json_encode($Arr_Data);
    }

    public function process_reject()
    {
        $data               = $this->input->post();
        // $check      = $data['check'];
        $so_number          = $data['so_number'];
        $tingkat_approval   = $data['tingkat_approval'];

        $this->db->trans_start();

        $ArrData = [
            'sts_reject' . $tingkat_approval => 1,
            'sts_reject' . $tingkat_approval . '_by' => $this->auth->user_id(),
            'sts_reject' . $tingkat_approval . '_date' => date('Y-m-d H:i:s'),
            'reject_reason' . $tingkat_approval => $data['reject_reason'],
            'keterangan_' . $tingkat_approval => $data['keterangan_' . $tingkat_approval],
            'app_post' => null,
            'rejected' => 1,
        ];

        $this->db->update('material_planning_base_on_produksi', $ArrData, ['so_number' => $so_number]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Reject Failed !',
                'status'  => 0,
                'so_number'  => $so_number
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Reject Success !',
                'status'  => 1,
                'so_number'  => $so_number
            );
            history("Approve pr material  : " . $so_number);
        }
        echo json_encode($Arr_Data);
    }
}
