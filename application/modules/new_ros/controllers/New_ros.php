<?php
defined('BASEPATH') or exit('No direct script access allowed');

class New_ros extends Admin_Controller
{
    protected $viewPermission   = 'ROS_(Packing_List).View';
    protected $addPermission    = 'ROS_(Packing_List).Add';
    protected $managePermission = 'ROS_(Packing_List).Manage';
    protected $deletePermission = 'ROS_(Packing_List).Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('New_ros/New_ros_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    // ─── LIST ────────────────────────────────────────────────────────
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('ROS List');
        $this->template->render('index');
    }

    public function data_side()
    {
        $ENABLE_MANAGE = has_permission('ROS_(Packing_List).Manage');
        $ENABLE_DELETE = has_permission('ROS_(Packing_List).Delete');
        $ENABLE_CLOSE  = has_permission('ROS_(Packing_List).Manage');

        $tab   = $this->input->post('tab') ?: 'draft';
        $fetch = $this->New_ros_model->get_datatables($tab);
        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $requestData = $_REQUEST;
        $data  = [];
        $urut1 = 1;
        $urut2 = 0;

        foreach ($query->result_array() as $row) {
            $start_dari = $requestData['start'];
            $asc_desc   = $requestData['order'][0]['dir'];
            $nomor = ($asc_desc == 'asc') ? ($totalData - $start_dari) - $urut2 : $urut1 + $start_dari;

            $edit_btn = '';
            if ($ENABLE_MANAGE && $row['status'] == '0') {
                $edit_btn = '<a href="' . base_url('new_ros/edit/' . $row['id']) . '" class="btn btn-sm btn-warning" style="width: 80px; title="Edit"><i class="fas fa-edit"> Edit</i></a>';
            }

            $del_btn = '';
            if ($ENABLE_DELETE && $row['status'] == '0') {
                $del_btn = '<a href="javascript:void(0)" class="btn btn-sm btn-danger del_ros" style="width: 80px;" title="Delete" data-id="' . $row['id'] . '"><i class="fa fa-trash"></i> Delete</a>';
            }

            $close_btn = '';
            if ($ENABLE_CLOSE && $row['status'] == '0') {
                $close_btn = '<a href="javascript:void(0)" class="btn btn-sm btn-success btn_close_ros" style="width: 80px;" title="Close ROS" data-id="' . $row['id'] . '"><i class="fas fa-check-double"></i> Close</a>';
            }

            $view_btn = '<a href="' . base_url('new_ros/view/' . $row['id']) . '" class="btn btn-sm btn-info" style="width: 80px;" title="View"><i class="fa fa-eye"> View</i></a>';

            $sts = '<span class="badge rounded-pill bg-warning">Draft</span>';
            if ($row['status'] == '1') {
                $sts = '<span class="badge rounded-pill bg-success">Final</span>';
            }

            $action_buttons = '
            <div style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                <div style="display: flex; gap: 5px;">
                    ' . $view_btn . '
                    ' . $edit_btn . '
                </div>
                <div style="display: flex; gap: 5px;">
                    ' . $del_btn . '
                    ' . $close_btn . '
                </div>
            </div>';

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$nomor}</div>";
            $nestedData[] = "<div class='text-left'>{$row['id']}</div>";
            $nestedData[] = "<div class='text-left'>" . ($row['no_surat'] ?: $row['no_po']) . "</div>";
            $nestedData[] = "<div class='text-left'>{$row['nm_supplier']}</div>";
            $nestedData[] = "<div class='text-end'>" . number_format($row['nilai_po_pib_rp'], 2) . "</div>";
            $nestedData[] = "<div class='text-center'>{$sts}</div>";
            $nestedData[] = "<div class='text-center'>{$action_buttons}</div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    // ─── ADD ─────────────────────────────────────────────────────────
    public function add()
    {
        $this->auth->restrict($this->addPermission);

        $list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result_array();

        $this->db->where('is_delete', '0');
        $master_forwarding = $this->db->get('master_forwarding_cost')->row();
        $forwarding_rate = ($master_forwarding) ? $master_forwarding->value_cost : 0;

        $this->template->set('list_supplier', $list_supplier);
        $this->template->set('forwarding_rate', $forwarding_rate);
        $this->template->set('mode', 'add');
        $this->template->title('Add New ROS');
        $this->template->render('add');
    }

    // ─── AJAX: Get PO by Supplier ────────────────────────────────────
    public function get_po_by_supplier()
    {
        $id_supplier = $this->input->post('id_supplier');

        $this->db->select('no_po, no_surat');
        $this->db->from('tr_purchase_order');
        $this->db->where('id_suplier', $id_supplier);
        $this->db->where('status', 2);
        $this->db->where("(close_po IS NULL OR close_po != '1')", NULL, FALSE);

        $this->db->order_by('no_po', 'DESC');
        $list = $this->db->get()->result_array();

        echo json_encode(['status' => 1, 'data' => $list]);
    }

    // ─── EDIT ────────────────────────────────────────────────────────
    public function edit($id_ros)
    {
        $this->auth->restrict($this->managePermission);

        $header    = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            show_404();
            return;
        }

        $materials = $this->New_ros_model->get_materials($id_ros);
        foreach ($materials as &$mat) {
            $mat['coils'] = $this->New_ros_model->get_coils($mat['id']);
        }
        $others        = $this->New_ros_model->get_others($id_ros);
        $list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result_array();

        // ========================================================================
        // UPDATE: Ambil list PO untuk supplier ini (tanpa JOIN ke ROS)
        // Sesuai dengan logic get_po_by_supplier()
        // ========================================================================
        $this->db->select('no_po, no_surat');
        $this->db->from('tr_purchase_order');
        $this->db->where('id_suplier', $header['id_supplier']);
        $this->db->where('status', 2);
        $this->db->where("(close_po IS NULL OR close_po != '1')", NULL, FALSE);
        $this->db->order_by('no_po', 'DESC');
        $list_po = $this->db->get()->result_array();
        // ========================================================================

        $this->db->where('is_delete', '0');
        $master_forwarding = $this->db->get('master_forwarding_cost')->row();
        $forwarding_rate = ($master_forwarding) ? $master_forwarding->value_cost : 0;

        $this->template->set('header', $header);
        $this->template->set('materials', $materials);
        $this->template->set('others', $others);
        $this->template->set('list_po', $list_po);
        $this->template->set('list_supplier', $list_supplier);
        $this->template->set('forwarding_rate', $forwarding_rate);
        $this->template->set('mode', 'edit');
        $this->template->title('Edit New ROS');
        $this->template->render('add');
    }

    // ─── VIEW ────────────────────────────────────────────────────────
    public function view($id_ros)
    {
        $this->auth->restrict($this->viewPermission);

        $header = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            show_404();
            return;
        }

        $materials = $this->New_ros_model->get_materials($id_ros);
        foreach ($materials as &$mat) {
            $mat['coils'] = $this->New_ros_model->get_coils($mat['id']);
        }
        $others = $this->New_ros_model->get_others($id_ros);

        $this->template->set('header', $header);
        $this->template->set('materials', $materials);
        $this->template->set('others', $others);
        $this->template->set('mode', 'view');
        $this->template->title('View New ROS');
        $this->template->render('view');
    }

    // ─── AJAX: Get PO Materials ──────────────────────────────────────
    public function get_po_materials()
    {
        $no_po      = $this->input->post('no_po');
        $kurs_pib   = (float) str_replace(',', '', $this->input->post('kurs_pib'));
        $materials  = $this->New_ros_model->get_po_materials($no_po);

        // Ambil supplier dari PO
        $po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
        $id_supplier = $po ? $po->id_suplier : '';

        $result = [];
        foreach ($materials as $mat) {
            $bm_persen = $this->New_ros_model->get_bm_persen($mat['idmaterial'], $id_supplier);
            $total_value_usd = (float) $mat['total_value_usd'];
            $total_value_rp  = $total_value_usd * $kurs_pib;
            $bm_rp           = $total_value_rp * ($bm_persen / 100);

            $result[] = [
                'id_po_detail'    => $mat['id_po_detail'],
                'idmaterial'      => $mat['idmaterial'],
                'nm_barang'       => $mat['nm_barang'],
                'nm_erp'          => $mat['nm_erp'] ?: $mat['nm_barang'],
                'nm_alias'        => $mat['nm_alias'] ?: $mat['nm_barang'],
                'kg_unit'         => (float) $mat['kg_unit'],
                'unit_price_usd'  => (float) $mat['unit_price_usd'],
                'total_value_usd' => $total_value_usd,
                'total_value_rp'  => $total_value_rp,
                'bm_persen'       => $bm_persen,
                'bm_rp'           => $bm_rp,
                'currency'        => $mat['currency'],
            ];
        }

        // var_dump($bm_persen);die;

        echo json_encode(['status' => 1, 'data' => $result]);
    }

    // ─── AJAX: Save Others Cost ──────────────────────────────────────
    public function save_others()
    {
        $post   = $this->input->post();
        $id_ros = $post['id_ros'];

        $this->db->trans_begin();
        $this->db->insert('tr_ros_others', [
            'id_ros'     => $id_ros,
            'keterangan' => $post['keterangan'],
            'nilai'      => str_replace(',', '', $post['nilai']),
            'created_by' => $this->auth->user_id(),
            'created_on' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0]);
        } else {
            $this->db->trans_commit();
            $total = $this->New_ros_model->get_total_others($id_ros);
            $others = $this->New_ros_model->get_others($id_ros);
            echo json_encode(['status' => 1, 'total' => $total, 'others' => $others]);
        }
    }

    // ─── AJAX: Delete Others Cost ────────────────────────────────────
    public function delete_others()
    {
        $id     = $this->input->post('id');
        $id_ros = $this->input->post('id_ros');

        $this->db->trans_begin();
        $this->db->delete('tr_ros_others', ['id' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0]);
        } else {
            $this->db->trans_commit();
            $total = $this->New_ros_model->get_total_others($id_ros);
            echo json_encode(['status' => 1, 'total' => $total]);
        }
    }

    // ─── SAVE ROS ────────────────────────────────────────────────────
    public function save()
    {
        $post = $this->input->post();
        $this->db->trans_begin();

        $kurs_pib          = (float) str_replace(',', '', $post['kurs_pib']);
        $total_kg_bersih   = (float) str_replace(',', '', $post['total_kg_bersih_pib']);
        $insurance         = (float) str_replace(',', '', $post['insurance']);
        $biaya_ls          = (float) str_replace(',', '', $post['biaya_ls']);

        // Hitung total others
        $total_others = 0;
        if (isset($post['others_nilai']) && is_array($post['others_nilai'])) {
            foreach ($post['others_nilai'] as $val) {
                $total_others += (float) str_replace(',', '', $val);
            }
        }

        // ── Upload Doc PIB ──
        $file_original_name_pib = null;
        $file_hash_name_pib     = null;

        if (isset($_FILES['doc_pib']) && $_FILES['doc_pib']['error'] === UPLOAD_ERR_OK) {
            $upload_path = FCPATH . 'uploads/pib_ros/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $config_pib = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'pdf|jpg|jpeg|png|xlsx|xls|doc|docx',
                'max_size'      => 10240,
                'encrypt_name'  => TRUE,
            ];
            $this->load->library('upload', $config_pib);
            $this->upload->initialize($config_pib);

            if ($this->upload->do_upload('doc_pib')) {
                $file_data              = $this->upload->data();
                $file_original_name_pib = $file_data['client_name'];
                $file_hash_name_pib     = $file_data['file_name'];
            } else {
                // JIKA UPLOAD GAGAL: Rollback database dan hentikan proses
                $this->db->trans_rollback();

                // Mengambil pesan error dari library CodeIgniter
                $error_msg = $this->upload->display_errors('', '');

                echo json_encode([
                    'status' => 0,
                    'msg'    => 'Upload file gagal: ' . $error_msg
                ]);
                return; // Berhenti di sini
            }
        }

        $is_new = ($post['id_ros'] == 'New');

        if ($is_new) {
            $id_ros = $this->New_ros_model->generate_no_ros();
        } else {
            $id_ros = $post['id_ros'];
        }

        // Ambil supplier info
        $get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $post['id_supplier']])->row_array();

        // ── Header ──
        $header_data = [
            'id_supplier'        => $post['id_supplier'],
            'nm_supplier'        => $get_supplier ? $get_supplier['nama'] : '',
            'no_po'              => $post['no_po'],
            'no_surat'           => $post['no_surat'],
            'nilai_po_usd'       => (float) str_replace(',', '', $post['nilai_po_usd']),
            'kurs_pib'           => $kurs_pib,
            'nilai_po_pib_rp'    => (float) str_replace(',', '', $post['nilai_po_pib_rp']),
            'total_kg_kotor_pib' => (float) str_replace(',', '', $post['total_kg_kotor_pib']),
            'total_kg_bersih_pib' => $total_kg_bersih,
            'cost_bm'            => (float) str_replace(',', '', $post['cost_bm']),
            'cost_bm_kite'       => (float) str_replace(',', '', $post['cost_bm_kite']),
            'cost_bmt'           => (float) str_replace(',', '', $post['cost_bmt']),
            'cost_cukai'         => (float) str_replace(',', '', $post['cost_cukai']),
            'cost_ppn'           => (float) str_replace(',', '', $post['cost_ppn']),
            'cost_ppnbm'         => (float) str_replace(',', '', $post['cost_ppnbm']),
            'cost_pph_import'    => (float) str_replace(',', '', $post['cost_pph_import']),
            'biaya_ls'           => $biaya_ls,
            'ppn_ls'             => (float) str_replace(',', '', $post['ppn_ls']),
            'pph_ls'             => (float) str_replace(',', '', $post['pph_ls']),
            'insurance'             => $insurance,
            'no_pengajuan'          => $post['no_pengajuan'] ?? null,
            'no_billing'            => $post['no_billing'] ?? null,
            'tgl_billing'           => !empty($post['tgl_billing']) ? $post['tgl_billing'] : null,
            'no_invoice_ls'         => $post['no_invoice_ls'] ?? null,
            'no_insurance'          => $post['no_insurance'] ?? null,
        ];

        if ($file_original_name_pib) {
            $header_data['file_original_name_pib'] = $file_original_name_pib;
            $header_data['file_hash_name_pib']     = $file_hash_name_pib;
        }

        if ($is_new) {
            $header_data['id']         = $id_ros;
            $header_data['status']     = 0;
            $header_data['created_by'] = $this->auth->user_id();
            $header_data['created_on'] = date('Y-m-d H:i:s');
            $this->db->insert('tr_ros_header', $header_data);
        } else {
            $header_data['modified_by'] = $this->auth->user_id();
            $header_data['modified_on'] = date('Y-m-d H:i:s');
            $this->db->update('tr_ros_header', $header_data, ['id' => $id_ros]);

            // Hapus material & coil lama
            $old_materials = $this->db->select('id')->get_where('tr_ros_material', ['id_ros' => $id_ros])->result_array();
            foreach ($old_materials as $om) {
                $this->db->delete('tr_ros_material_coil', ['id_ros_material' => $om['id']]);
            }
            $this->db->delete('tr_ros_material', ['id_ros' => $id_ros]);

            // Hapus others lama dan insert ulang
            $this->db->delete('tr_ros_others', ['id_ros' => $id_ros]);
        }

        // ── Others Cost ──
        // Baris 349 (Estimasi)
        if (isset($post['others_keterangan']) && is_array($post['others_keterangan'])) {
            foreach ($post['others_keterangan'] as $idx => $ket) {
                // Tambahkan pengecekan isset untuk index $idx
                $nilai_raw = isset($post['others_nilai'][$idx]) ? $post['others_nilai'][$idx] : 0;
                $nilai_other = (float) str_replace(',', '', $nilai_raw);

                if (!empty($ket) || $nilai_other > 0) {
                    $no_others_raw = isset($post['others_no'][$idx]) ? $post['others_no'][$idx] : null;
                    $this->db->insert('tr_ros_others', [
                        'id_ros'     => $id_ros,
                        'no_others'  => $no_others_raw,
                        'keterangan' => $ket,
                        'nilai'      => $nilai_other,
                        'created_by' => $this->auth->user_id(),
                        'created_on' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // ── Hitung total KG LS untuk prorate ──
        $total_kg_ls = 0;
        if (isset($post['mat']) && is_array($post['mat'])) {
            foreach ($post['mat'] as $mat) {
                $ls_flag = isset($mat['ls_flag']) ? $mat['ls_flag'] : 'TIDAK';
                if ($ls_flag == 'YA') {
                    $total_kg_ls += (float) str_replace(',', '', $mat['kg_unit']);
                }
            }
        }

        // ── Materials ──
        if (isset($post['mat']) && is_array($post['mat'])) {
            foreach ($post['mat'] as $mat) {
                $kg_unit         = (float) str_replace(',', '', $mat['kg_unit']);
                $unit_price_usd  = (float) str_replace(',', '', $mat['unit_price_usd']);
                $total_value_usd = (float) str_replace(',', '', $mat['total_value_usd']);
                $total_value_rp  = $total_value_usd * $kurs_pib;
                $bm_persen       = (float) $mat['bm_persen'];
                $bm_rp           = $total_value_rp * ($bm_persen / 100);
                $ls_flag         = isset($mat['ls_flag']) ? $mat['ls_flag'] : 'TIDAK';

                // Prorate LS
                $prorate_ls = 0;
                if ($ls_flag == 'YA' && $total_kg_ls > 0) {
                    $prorate_ls = $biaya_ls * ($kg_unit / $total_kg_ls);
                }

                // Forwarding cost = Rate dari master_forwarding_cost * kg_unit
                $this->db->where('is_delete', '0');
                $master_fwd = $this->db->get('master_forwarding_cost')->row();
                $fwd_rate = ($master_fwd) ? (float) $master_fwd->value_cost : 0;
                $forwarding_cost = $fwd_rate * $kg_unit;

                // Prorate Insurance
                $prorate_insurance = 0;
                if ($total_kg_bersih > 0) {
                    $prorate_insurance = $insurance * ($kg_unit / $total_kg_bersih);
                }

                // Prorate Others
                $prorate_others = 0;
                if ($total_kg_bersih > 0) {
                    $prorate_others = $total_others * ($kg_unit / $total_kg_bersih);
                }

                // Total Nilai Inventory
                $total_nilai_inventory = $total_value_rp + $bm_rp + $prorate_ls + $forwarding_cost + $prorate_insurance + $prorate_others;

                // Cost Book
                $cost_book = ($kg_unit > 0) ? $total_nilai_inventory / $kg_unit : 0;

                $this->db->insert('tr_ros_material', [
                    'id_ros'               => $id_ros,
                    'id_po_detail'         => $mat['id_po_detail'],
                    'id_barang'            => $mat['id_barang'],
                    'nm_barang'            => $mat['nm_barang'],
                    'nm_erp'               => $mat['nm_erp'],
                    'nm_alias'             => $mat['nm_alias'],
                    'kg_unit'              => $kg_unit,
                    'unit_price_usd'       => $unit_price_usd,
                    'total_value_usd'      => $total_value_usd,
                    'total_value_rp'       => $total_value_rp,
                    'bm_persen'            => $bm_persen,
                    'bm_rp'                => $bm_rp,
                    'prorate_ls'           => $prorate_ls,
                    'forwarding_cost'      => $forwarding_cost,
                    'prorate_insurance'    => $prorate_insurance,
                    'prorate_others'       => $prorate_others,
                    'total_nilai_inventory' => $total_nilai_inventory,
                    'cost_book'            => $cost_book,
                    'ls_flag'              => $ls_flag,
                    'created_by'           => $this->auth->user_id(),
                    'created_on'           => date('Y-m-d H:i:s')
                ]);

                $id_ros_material = $this->db->insert_id();

                // ── Coils ──
                // ── Coils ──
                if (isset($mat['coil']) && is_array($mat['coil'])) {
                    // Hitung jumlah coil valid dulu
                    $valid_coils = [];
                    foreach ($mat['coil'] as $coil) {
                        $berat_kotor  = (float) str_replace(',', '', $coil['berat_kotor']);
                        $berat_bersih = (float) str_replace(',', '', $coil['berat_bersih']);
                        if ($berat_kotor > 0 || !empty($coil['no_coil'])) {
                            $valid_coils[] = $coil;
                        }
                    }

                    $jumlah_coil   = count($valid_coils);
                    $price_per_coil = ($jumlah_coil > 0) ? $total_nilai_inventory / $jumlah_coil : 0;

                    foreach ($valid_coils as $coil) {
                        $this->db->insert('tr_ros_material_coil', [
                            'id_ros_material' => $id_ros_material,
                            'no_coil'         => $coil['no_coil'],
                            'berat_kotor'     => (float) str_replace(',', '', $coil['berat_kotor']),
                            'berat_bersih'    => (float) str_replace(',', '', $coil['berat_bersih']),
                            'panjang'         => (float) str_replace(',', '', $coil['panjang']),
                            'kode_internal'   => isset($coil['kode_internal']) ? $coil['kode_internal'] : '',
                            'bpm'             => isset($coil['bpm']) ? (float) str_replace(',', '', $coil['bpm']) : 0,
                            'price_per_coil'  => $price_per_coil,
                            'created_by'      => $this->auth->user_id(),
                            'created_on'      => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Failed to save ROS data.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1, 'msg' => 'ROS data saved successfully.', 'id' => $id_ros]);
        }
    }

    // ─── DELETE ───────────────────────────────────────────────────────
    public function delete()
    {
        $id_ros = $this->input->post('id');

        $this->db->trans_begin();

        // Hapus coils
        $materials = $this->db->select('id')->get_where('tr_ros_material', ['id_ros' => $id_ros])->result_array();
        foreach ($materials as $m) {
            $this->db->delete('tr_ros_material_coil', ['id_ros_material' => $m['id']]);
        }
        $this->db->delete('tr_ros_material', ['id_ros' => $id_ros]);
        $this->db->delete('tr_ros_others', ['id_ros' => $id_ros]);
        $this->db->delete('tr_ros_header', ['id' => $id_ros]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0]);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1]);
        }
    }

    // ─── DOWNLOAD TEMPLATE EXCEL ─────────────────────────────────────
    public function download_template()
    {
        // Format input baru: JSON array of { nm_alias, count }
        $materials_coil_json = $this->input->post('materials_coil');
        $materials_coil = json_decode($materials_coil_json, true);

        if (empty($materials_coil)) {
            show_error('No material data available.');
            return;
        }

        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Packing List');

        // ── Style Header ──
        $headerStyle = array(
            'font' => array('bold' => true, 'size' => 10),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ),
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            ),
            'fill' => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'D9E1F2'),
            ),
        );

        // ── Style Data (baris biasa) ──
        $dataStyle = array(
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            ),
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
        );

        // ── Style baris nama material (warna berbeda per group) ──
        $matColors = ['EBF5FB', 'E9F7EF', 'FEF9E7', 'F9EBEA', 'F4ECF7'];

        // ── Kolom widths ──
        $sheet->getColumnDimension('A')->setWidth(20);  // COIL NO.
        $sheet->getColumnDimension('B')->setWidth(40);  // Alias Name
        $sheet->getColumnDimension('C')->setWidth(40);  // Original Name (nm_barang)
        $sheet->getColumnDimension('D')->setWidth(12);  // N.W.
        $sheet->getColumnDimension('E')->setWidth(12);  // G.W.
        $sheet->getColumnDimension('F')->setWidth(12);  // LENGTH
        $sheet->getColumnDimension('G')->setWidth(10);  // BPM

        // ── Header Row ──
        $sheet->setCellValue('A1', 'COIL NO.');
        $sheet->setCellValue('B1', 'Alias Name');
        $sheet->setCellValue('C1', 'Original Name');
        $sheet->setCellValue('D1', "N.W.\n(KGS)");
        $sheet->setCellValue('E1', "G.W.\n(KGS)");
        $sheet->setCellValue('F1', "LENGTH\n(M)");
        $sheet->setCellValue('G1', 'BPM');
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ── Data Rows: 1 baris per coil, sudah disiapkan sesuai jumlah ──
        $row       = 2;
        $colorIdx  = 0;
        foreach ($materials_coil as $mat) {
            $nm_alias  = isset($mat['nm_alias'])  ? $mat['nm_alias']  : '';
            $nm_barang = isset($mat['nm_barang']) ? $mat['nm_barang'] : '';
            $count     = isset($mat['count'])     ? max(1, (int) $mat['count']) : 1;
            $bgColor   = $matColors[$colorIdx % count($matColors)];

            // Style khusus per material group (warna berbeda)
            $matStyle = array(
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                ),
                'fill' => array(
                    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => $bgColor),
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
            );

            for ($c = 1; $c <= $count; $c++) {
                $sheet->setCellValue('A' . $row, '');           // COIL NO — user isi
                $sheet->setCellValue('B' . $row, $nm_alias);    // Nama Lain/Alias — sudah terisi
                $sheet->setCellValue('C' . $row, $nm_barang);   // Nama Asli — sudah terisi
                $sheet->setCellValue('D' . $row, '');            // N.W.
                $sheet->setCellValue('E' . $row, '');            // G.W.
                $sheet->setCellValue('F' . $row, '');            // LENGTH
                $sheet->setCellValue('G' . $row, '');            // BPM

                $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($matStyle);
                $sheet->getRowDimension($row)->setRowHeight(18);
                $row++;
            }

            // Baris kosong pemisah antar material (kecuali material terakhir)
            if ($colorIdx < count($materials_coil) - 1) {
                $sheet->getRowDimension($row)->setRowHeight(18);
                $row++;
            }

            $colorIdx++;
        }

        // ── Freeze panes pada row pertama data ──
        $sheet->freezePane('A2');

        // ── Output ──
        $filename = 'Template_Packing_List_' . date('Ymd_His') . '.xlsx';

        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    // ─── PARSE PACKING LIST (Excel) → Return JSON tanpa save ────────
    // Bisa dipakai untuk ROS baru (belum disimpan) maupun edit
    public function parse_packing_list()
    {
        // Upload file
        $config['upload_path']   = FCPATH . 'uploads/new_ros/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size']      = 10240;
        $config['encrypt_name']  = TRUE;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_packing_list')) {
            echo json_encode(['status' => 0, 'msg' => strip_tags($this->upload->display_errors())]);
            return;
        }

        $file_data     = $this->upload->data();
        $file_path     = $file_data['full_path'];
        $original_name = $file_data['client_name'];
        $hash_name     = $file_data['file_name'];

        // Ambil inisial supplier
        $id_supplier = $this->input->post('id_supplier');
        $supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row_array();
        $inisial  = isset($supplier['inisial']) ? $supplier['inisial'] : '';

        // Existing coil count (untuk counter kode internal)
        $existing_count = (int) $this->input->post('existing_coil_count');

        // Parse Excel
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        try {
            $objReader = PHPExcel_IOFactory::createReaderForFile($file_path);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file_path);
        } catch (Exception $e) {
            echo json_encode(['status' => 0, 'msg' => 'Failed to read Excel file: ' . $e->getMessage()]);
            return;
        }

        $sheet   = $objPHPExcel->getActiveSheet();
        $highRow = $sheet->getHighestRow();

        // Mapping kolom
        // ── Deteksi header row & mapping kolom (format baru) ──
        $start_row   = 2;
        $col_coil_no = 'A';
        $col_nama_alias = 'B';
        $col_nm_barang  = 'C';
        $col_nw      = 'D';
        $col_gw      = 'E';
        $col_length  = 'F';
        $col_bpm     = 'G';

        for ($r = 1; $r <= min($highRow, 10); $r++) {
            $cellA = strtolower(trim((string) $sheet->getCell('A' . $r)->getValue()));
            $cellB = strtolower(trim((string) $sheet->getCell('B' . $r)->getValue()));

            if (strpos($cellA, 'coil no') !== false) {
                // Format baru: A=COIL NO, B=Alias, C=Nama Asli, D=NW, E=GW, F=Length, G=BPM
                $col_coil_no    = 'A';
                $col_nama_alias = 'B';
                $col_nm_barang  = 'C';
                $col_nw         = 'D';
                $col_gw         = 'E';
                $col_length     = 'F';
                $col_bpm        = 'G';
                $start_row      = $r + 1;
                break;
            } elseif (strpos($cellB, 'coil no') !== false) {
                // Format lama fallback: B=COIL NO, C=Alias, D=Number, E=NW, F=GW, G=Length, H=BPM
                $col_coil_no    = 'B';
                $col_nama_alias = 'C';
                $col_nm_barang  = 'D';
                $col_nw         = 'E';
                $col_gw         = 'F';
                $col_length     = 'G';
                $col_bpm        = 'H';
                $start_row      = $r + 1;
                break;
            }
        }

        $counter = $existing_count + 1;
        $coils   = [];

        for ($row = $start_row; $row <= $highRow; $row++) {
            $getCellValue = function ($col) use ($sheet, $row) {
                $cell = $sheet->getCell($col . $row);
                if ($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_FORMULA) {
                    return $cell->getOldCalculatedValue();
                }
                return $cell->getValue();
            };

            $coil_no    = trim((string) $getCellValue($col_coil_no));
            $nama_alias = trim((string) $getCellValue($col_nama_alias));
            $nm_barang  = trim((string) $getCellValue($col_nm_barang));
            $nw         = $getCellValue($col_nw);
            $gw         = $getCellValue($col_gw);
            $length     = $getCellValue($col_length);
            $bpm        = $getCellValue($col_bpm);

            if (empty($coil_no) || strtolower($coil_no) == 'total') continue;
            if (strpos(strtolower($coil_no), 'error') !== false) continue;
            // Skip baris kosong (baris pemisah antar material)
            if (empty($nama_alias) && empty($nm_barang)) continue;

            $nw_val     = (float) str_replace(',', '', (string) $nw);
            $gw_val     = (float) str_replace(',', '', (string) $gw);
            $length_val = (float) str_replace(',', '', (string) $length);
            $bpm_val    = (float) str_replace(',', '', (string) $bpm);

            $kode_internal = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

            $coils[] = [
                'no_coil'       => $coil_no,
                'nama_alias'    => $nama_alias,   // ← key baru, dipakai untuk matching
                'nm_barang'     => $nm_barang,
                'berat_bersih'  => $nw_val,
                'berat_kotor'   => $gw_val,
                'panjang'       => $length_val,
                'bpm'           => $bpm_val,
                'kode_internal' => $kode_internal,
            ];
            $counter++;
        }

        echo json_encode([
            'status'        => 1,
            'msg'           => 'Successfully read ' . count($coils) . ' coil rows.',
            'coils'         => $coils,
            'total'         => count($coils),
            'file_original' => $original_name,
            'file_hash'     => $hash_name,
        ]);
    }

    // ─── UPLOAD PACKING LIST (Excel) → Simpan ke tabel sementara ────
    public function upload_packing_list()
    {
        $id_ros = $this->input->post('id_ros');
        if (!$id_ros || $id_ros == 'New') {
            echo json_encode(['status' => 0, 'msg' => 'Please save the ROS first before uploading the packing list.']);
            return;
        }

        // Cek ROS exists
        $header = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            echo json_encode(['status' => 0, 'msg' => 'ROS data not found.']);
            return;
        }

        // Upload file
        $config['upload_path']   = FCPATH . 'uploads/new_ros/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size']      = 10240; // 10MB
        $config['encrypt_name']  = TRUE;

        // Buat folder jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_packing_list')) {
            echo json_encode(['status' => 0, 'msg' => strip_tags($this->upload->display_errors())]);
            return;
        }

        $file_data     = $this->upload->data();
        $file_path     = $file_data['full_path'];
        $original_name = $file_data['client_name'];
        $hash_name     = $file_data['file_name'];

        // Parse Excel
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        try {
            $objReader = PHPExcel_IOFactory::createReaderForFile($file_path);
            $objReader->setReadDataOnly(true); // baca cached value saja, tidak kalkulasi ulang
            $objPHPExcel = $objReader->load($file_path);
        } catch (Exception $e) {
            echo json_encode(['status' => 0, 'msg' => 'Failed to read Excel file: ' . $e->getMessage()]);
            return;
        }

        $sheet    = $objPHPExcel->getActiveSheet();
        $highRow  = $sheet->getHighestRow();

        // Ambil materials untuk ROS ini
        $materials = $this->New_ros_model->get_materials($id_ros);
        if (empty($materials)) {
            echo json_encode(['status' => 0, 'msg' => 'No materials found in this ROS. Please load PO data and save first.']);
            return;
        }

        // Build lookup: nm_alias / nm_barang (lowercase) → material id
        $mat_lookup = [];
        foreach ($materials as $mat) {
            $key = strtolower(trim($mat['nm_alias']));
            if ($key) $mat_lookup[$key] = $mat['id'];
            $key2 = strtolower(trim($mat['nm_barang']));
            if ($key2 && !isset($mat_lookup[$key2])) $mat_lookup[$key2] = $mat['id'];
            // Juga nm_erp
            $key3 = strtolower(trim($mat['nm_erp']));
            if ($key3 && !isset($mat_lookup[$key3])) $mat_lookup[$key3] = $mat['id'];
        }

        // Ambil inisial supplier
        $supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $header['id_supplier']])->row_array();
        $inisial  = isset($supplier['inisial']) ? $supplier['inisial'] : '';

        // Hapus data temp lama untuk ROS + session ini
        $session_id = session_id();
        $this->db->delete('tr_ros_upload_temp', ['id_ros' => $id_ros, 'session_id' => $session_id]);

        // Cari header row dan tentukan mapping kolom
        // Format Excel: (A kosong) | B: COIL NO. | C: NAMA Sesuai PO | D: COIL NUMBER | E: N.W. | F: G.W. | G: LENGTH | H: BPM
        $start_row = 2;
        $col_coil_no = 'B';
        $col_nama_po = 'C';
        $col_number  = 'D';
        $col_nw = 'E';
        $col_gw = 'F';
        $col_length  = 'G';
        $col_bpm = 'H';

        for ($r = 1; $r <= min($highRow, 10); $r++) {
            $cellA = strtolower(trim((string) $sheet->getCell('A' . $r)->getValue()));
            $cellB = strtolower(trim((string) $sheet->getCell('B' . $r)->getValue()));

            if (strpos($cellA, 'coil no') !== false) {
                // Data mulai dari kolom A
                $col_coil_no = 'A';
                $col_nama_po = 'B';
                $col_number = 'C';
                $col_nw = 'D';
                $col_gw = 'E';
                $col_length = 'F';
                $col_bpm = 'G';
                $start_row = $r + 1;
                break;
            } elseif (strpos($cellB, 'coil no') !== false) {
                // Data mulai dari kolom B (kolom A kosong)
                $col_coil_no = 'B';
                $col_nama_po = 'C';
                $col_number = 'D';
                $col_nw = 'E';
                $col_gw = 'F';
                $col_length = 'G';
                $col_bpm = 'H';
                $start_row = $r + 1;
                break;
            }
        }

        // Hitung existing coil count untuk counter kode internal
        $this->db->select('c.id');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material');
        $this->db->where('m.id_ros', $id_ros);
        $existing_count = $this->db->get()->num_rows();
        $counter = $existing_count + 1;

        $rows_parsed = 0;
        $rows_matched = 0;

        for ($row = $start_row; $row <= $highRow; $row++) {
            $getCellValue = function ($col) use ($sheet, $row) {
                $cell = $sheet->getCell($col . $row);
                if ($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_FORMULA) {
                    return $cell->getOldCalculatedValue();
                }
                return $cell->getValue();
            };

            $coil_no     = trim((string) $getCellValue($col_coil_no));
            $nm_po       = trim((string) $getCellValue($col_nama_po));
            $coil_number = $getCellValue($col_number);
            $nw          = $getCellValue($col_nw);
            $gw          = $getCellValue($col_gw);
            $length      = $getCellValue($col_length);
            $bpm         = $getCellValue($col_bpm);

            // Skip row kosong atau row TOTAL/ERROR
            if (empty($coil_no) || strtolower($coil_no) == 'total') continue;
            if (strpos(strtolower($coil_no), 'error') !== false) continue;

            $nw_val     = (float) str_replace(',', '', (string) $nw);
            $gw_val     = (float) str_replace(',', '', (string) $gw);
            $length_val = (float) str_replace(',', '', (string) $length);
            $bpm_val    = (float) str_replace(',', '', (string) $bpm);

            // Match material
            $nm_po_lower = strtolower(trim($nm_po));
            $id_ros_material = isset($mat_lookup[$nm_po_lower]) ? $mat_lookup[$nm_po_lower] : null;
            $is_matched = $id_ros_material ? 1 : 0;

            // Generate kode internal
            $kode_internal = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

            $this->db->insert('tr_ros_upload_temp', [
                'id_ros'          => $id_ros,
                'session_id'      => $session_id,
                'no_coil'         => $coil_no,
                'nama_sesuai_po'  => $nm_po,
                'coil_number'     => (int) $coil_number ?: 1,
                'berat_bersih'    => $nw_val,
                'berat_kotor'     => $gw_val,
                'panjang'         => $length_val,
                'bpm'             => $bpm_val,
                'id_ros_material' => $id_ros_material,
                'kode_internal'   => $kode_internal,
                'is_matched'      => $is_matched,
                'created_on'      => date('Y-m-d H:i:s')
            ]);

            $rows_parsed++;
            if ($is_matched) $rows_matched++;
            $counter++;
        }

        // Simpan info file ke header
        $this->db->update('tr_ros_header', [
            'file_original_name' => $original_name,
            'file_hash_name'     => $hash_name,
            'modified_by'        => $this->auth->user_id(),
            'modified_on'        => date('Y-m-d H:i:s')
        ], ['id' => $id_ros]);

        echo json_encode([
            'status'       => 1,
            'msg'          => "Successfully read {$rows_parsed} rows. {$rows_matched} matched, " . ($rows_parsed - $rows_matched) . " not matched.",
            'total_parsed' => $rows_parsed,
            'total_matched' => $rows_matched,
            'file'         => $original_name
        ]);
    }

    // ─── AJAX: Get data temp untuk review di modal ───────────────────
    public function get_upload_review()
    {
        $id_ros     = $this->input->post('id_ros');
        $session_id = session_id();

        $this->db->select('t.*, m.nm_alias, m.nm_barang');
        $this->db->from('tr_ros_upload_temp t');
        $this->db->join('tr_ros_material m', 'm.id = t.id_ros_material', 'left');
        $this->db->where('t.id_ros', $id_ros);
        $this->db->where('t.session_id', $session_id);
        $this->db->order_by('t.id', 'ASC');
        $data = $this->db->get()->result_array();

        echo json_encode(['status' => 1, 'data' => $data, 'total' => count($data)]);
    }

    // ─── AJAX: Konfirmasi upload → pindah dari temp ke tabel asli ────
    public function confirm_upload()
    {
        $id_ros     = $this->input->post('id_ros');
        $session_id = session_id();

        $temp_data = $this->db->get_where('tr_ros_upload_temp', [
            'id_ros'     => $id_ros,
            'session_id' => $session_id,
            'is_matched' => 1
        ])->result_array();

        if (empty($temp_data)) {
            echo json_encode(['status' => 0, 'msg' => 'No data available for confirmation.']);
            return;
        }

        // Hitung price_per_coil per material (jumlah coil matched per id_ros_material)
        $coil_count_per_mat = [];
        foreach ($temp_data as $row) {
            $id_mat = $row['id_ros_material'];
            $coil_count_per_mat[$id_mat] = isset($coil_count_per_mat[$id_mat])
                ? $coil_count_per_mat[$id_mat] + 1 : 1;
        }

        // Ambil total_nilai_inventory per id_ros_material
        $inventory_per_mat = [];
        foreach (array_keys($coil_count_per_mat) as $id_mat) {
            $mat = $this->db->get_where('tr_ros_material', ['id' => $id_mat])->row();
            $inventory_per_mat[$id_mat] = $mat ? (float) $mat->total_nilai_inventory : 0;
        }

        $this->db->trans_begin();

        $inserted = 0;
        foreach ($temp_data as $row) {
            $id_mat         = $row['id_ros_material'];
            $jumlah_coil    = $coil_count_per_mat[$id_mat];
            $total_inv      = $inventory_per_mat[$id_mat];
            $price_per_coil = ($jumlah_coil > 0) ? $total_inv / $jumlah_coil : 0;

            $this->db->insert('tr_ros_material_coil', [
                'id_ros_material' => $id_mat,
                'no_coil'         => $row['no_coil'],
                'berat_kotor'     => $row['berat_kotor'],
                'berat_bersih'    => $row['berat_bersih'],
                'panjang'         => $row['panjang'],
                'kode_internal'   => $row['kode_internal'],
                'bpm'             => isset($row['bpm']) ? (float) $row['bpm'] : 0,
                'price_per_coil'  => $price_per_coil,
                'created_by'      => $this->auth->user_id(),
                'created_on'      => date('Y-m-d H:i:s')
            ]);
            $inserted++;
        }

        $this->db->delete('tr_ros_upload_temp', ['id_ros' => $id_ros, 'session_id' => $session_id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Failed to save coil data.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1, 'msg' => "Successfully saved {$inserted} coils.", 'total' => $inserted]);
        }
    }

    // ─── AJAX: Batal upload → hapus data temp ────────────────────────
    public function cancel_upload()
    {
        $id_ros     = $this->input->post('id_ros');
        $session_id = session_id();

        $this->db->delete('tr_ros_upload_temp', ['id_ros' => $id_ros, 'session_id' => $session_id]);
        echo json_encode(['status' => 1]);
    }

    // ─── AJAX: Get Coil List (untuk modal print QR) ──────────────────
    public function get_coil_list()
    {
        $id_ros = $this->input->post('id_ros');

        $this->db->select('c.*, m.nm_barang, m.nm_alias, m.nm_erp');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material', 'left');
        $this->db->where('m.id_ros', $id_ros);
        $this->db->order_by('m.id', 'ASC');
        $this->db->order_by('c.id', 'ASC');
        $coils = $this->db->get()->result_array();

        // Group by material
        $groups = [];
        foreach ($coils as $c) {
            $groups[$c['id_ros_material']][] = $c;
        }

        $html = '<div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Material</th>
                    <th class="text-center">Alias Name</th>
                    <th class="text-center">Coil No.</th>
                    <th class="text-center">Internal Code</th>
                    <th class="text-center">N.W. (Kg)</th>
                    <th class="text-center">G.W. (Kg)</th>
                    <th class="text-center">Length (M)</th>
                    <th class="text-center"><input type="checkbox" id="check_all_modal"></th>
                </tr>
            </thead>
            <tbody>';

        if (!empty($groups)) {
            $no = 1;
            foreach ($groups as $id_mat => $rows) {
                $rowspan = count($rows);
                foreach ($rows as $idx => $coil) {
                    $html .= '<tr>';
                    if ($idx === 0) {
                        $html .= '<td class="text-center" rowspan="' . $rowspan . '" style="vertical-align:middle">' . $no . '</td>';
                        $html .= '<td rowspan="' . $rowspan . '" style="vertical-align:middle">' . $coil['nm_erp'] . '</td>';
                        $html .= '<td rowspan="' . $rowspan . '" style="vertical-align:middle">' . $coil['nm_alias'] . '</td>';
                    }
                    $html .= '<td class="text-center">' . $coil['no_coil'] . '</td>';
                    $html .= '<td class="text-center">' . $coil['kode_internal'] . '</td>';
                    $html .= '<td class="text-end">' . number_format($coil['berat_bersih'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($coil['berat_kotor'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($coil['panjang'], 2) . '</td>';
                    $html .= '<td class="text-center"><input type="checkbox" class="check_item_modal" value="' . $coil['id'] . '"></td>';
                    $html .= '</tr>';
                }
                $no++;
            }
        } else {
            $html .= '<tr><td colspan="9" class="text-center">No coil data available. Please upload the packing list first.</td></tr>';
        }

        $html .= '</tbody></table></div>';
        echo $html;
    }

    // ─── PRINT QR CODE ───────────────────────────────────────────────
    public function print_qr($ids)
    {
        $array_id = explode('-', $ids);

        $this->db->select('c.*, m.nm_barang, m.nm_alias, m.nm_erp, h.id as no_ros, h.nm_supplier');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material', 'left');
        $this->db->join('tr_ros_header h', 'h.id = m.id_ros', 'left');
        $this->db->where_in('c.id', $array_id);
        $data_coil = $this->db->get()->result_array();

        if (empty($data_coil)) {
            die("Data not found.");
        }

        $data = ['results' => $data_coil];
        $this->load->view('print_qr_label', $data);
    }

    public function finalize()
    {
        $id_ros = $this->input->post('id_ros');

        // Cek apakah sudah ada coil
        $this->db->select('c.id');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material');
        $this->db->where('m.id_ros', $id_ros);
        $coil_count = $this->db->get()->num_rows();

        if ($coil_count == 0) {
            echo json_encode(['status' => 0, 'msg' => 'Please upload the packing list before finalizing.']);
            return;
        }

        // Ambil header ROS
        $header = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            echo json_encode(['status' => 0, 'msg' => 'ROS data not found.']);
            return;
        }

        // Ambil semua material beserta coil
        $materials = $this->New_ros_model->get_materials($id_ros);
        foreach ($materials as &$mat) {
            $mat['coils'] = $this->New_ros_model->get_coils($mat['id']);
        }

        $this->db->trans_begin();

        $this->db->update('tr_ros_header', [
            'status'      => 1,
            'modified_by' => $this->auth->user_id(),
            'modified_on' => date('Y-m-d H:i:s')
        ], ['id' => $id_ros]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Failed to finalize ROS.']);
            return;
        }

        $this->db->trans_commit();

        // Hitung total untuk GL Interface
        $total_inventory = 0;
        $materials_gl    = [];

        foreach ($materials as $mat) {
            $total_inventory += (float) $mat['total_nilai_inventory'];

            $materials_gl[] = [
                'id_material'      => $mat['id_barang'],
                'nm_material'      => $mat['nm_erp'] ?: $mat['nm_barang'],
                'qty'              => (float) $mat['kg_unit'],
                'harga'            => (float) $mat['cost_book'],
                'total_persediaan' => (float) $mat['total_nilai_inventory'],
                'biaya_masuk'      => (float) $mat['bm_rp'],
                'forwarding'       => (float) $mat['forwarding_cost'],
                'price_coil_usd'   => (float) $mat['unit_price_usd'],
                'price_coil_idr'   => (float) $mat['total_value_rp'],
                'no_coil'          => '',
                'id_gudang_ke'     => null,
                'kd_gudang_ke'     => '',
            ];
        }

        // Generate GL Interface
        $jurnal_error = null;
        if ($total_inventory > 0) {
            try {
                $this->_generate_gl_interface(
                    $id_ros,
                    $header['no_po'],
                    $total_inventory,
                    $header['id_supplier'],
                    $materials_gl,
                    $header['kurs_pib']
                );

                // ── DEBUG: cek apakah sudah masuk ke gl_interface ──
                $cek_gl = $this->db->get_where('gl_interface', [
                    'no_request' => $id_ros,  // atau sesuai field yang dipakai
                    'jenis_transaksi' => 'finalize ros'
                ])->result_array();

                var_dump($cek_gl);
                die(); // stop di sini dulu biar keliatan hasilnya

            } catch (Exception $e) {
                $jurnal_error = $e->getMessage();
                log_message('error', 'GL Interface error finalize ROS ' . $id_ros . ': ' . $jurnal_error);
            }
        }

        if ($jurnal_error) {
            echo json_encode(['status' => 2, 'msg' => 'ROS finalized successfully, but GL Interface creation failed. Please repost via the GL Interface menu.']);
        } else {
            echo json_encode(['status' => 1, 'msg' => 'ROS finalized successfully. Please proceed in the Incoming menu.']);
        }
    }

    private function _generate_gl_interface($no_ros, $no_po, $total_rp, $id_supplier, $materials = [], $kurs_pib = 0)
    {
        $tgl_inv       = date('Y-m-d');
        $supplier      = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row();
        $supplier_name = $supplier ? $supplier->nama : '';

        $po_data  = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
        $currency = $po_data ? strtoupper(trim($po_data->matauang)) : 'IDR';

        $coa_dp      = ($currency === 'IDR') ? '1104-01-01' : '1104-01-02';
        $coa_unbill  = '2101-01-06';
        $coa_bm      = '1108-01-09';
        $coa_forward = '2104-01-13';

        $coa_persediaan_map     = ['PUS' => '1105-01-01', 'PEN' => '1105-01-03'];
        $coa_persediaan_default = '1105-01-01';

        $keterangan = "Finalize ROS: {$no_ros} | PO: {$no_po}";
        $user_id    = $this->auth->user_id();
        $created_on = date('Y-m-d H:i:s');

        $total_biaya_masuk = array_sum(array_column($materials, 'biaya_masuk'));
        $total_forwarding  = array_sum(array_column($materials, 'forwarding'));
        $total_unbill      = $total_rp - $total_biaya_masuk - $total_forwarding;

        $nomor_jv = $this->_generate_nomor_jv_ros();

        // Insert header GL Interface
        $this->db->insert('gl_interface', [
            'nomor'           => $nomor_jv,
            'tgl'             => $tgl_inv,
            'bulan'           => date('m'),
            'tahun'           => date('Y'),
            'kdcab'           => '101',
            'jenis'           => 'JV',
            'keterangan'      => $keterangan,
            'jenis_transaksi' => 'finalize ros',
            'status'          => 'pending',
            'user_id'         => $user_id,
            'memo'            => json_encode([
                'id_supplier'   => $id_supplier,
                'nama_supplier' => $supplier_name,
                'no_reff'       => $no_po,
                'no_request'    => $no_ros,
            ]),
        ]);
        $id_gl = $this->db->insert_id();

        // DEBET persediaan per material
        foreach ($materials as $mat) {
            $kd_gd          = $mat['kd_gudang_ke'] ?? '';
            $coa_persediaan = $coa_persediaan_map[$kd_gd] ?? $coa_persediaan_default;

            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => null,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_persediaan,
                'id_material'     => $mat['id_material'],
                'nm_material'     => $mat['nm_material'],
                'id_gudang'       => $mat['id_gudang_ke'] ?? null,
                'no_coil'         => $mat['no_coil'] ?? null,
                'keterangan'      => "Finalize ROS: {$no_ros} | PO: {$no_po} | {$mat['nm_material']}",
                'no_reff'         => $no_po,
                'no_request'      => $no_ros,
                'debet'           => $mat['total_persediaan'],
                'kredit'          => 0,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT Unbill
        if ($total_unbill > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => null,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_unbill,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => null,
                'no_coil'         => null,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_po,
                'no_request'      => $no_ros,
                'debet'           => 0,
                'kredit'          => $total_unbill,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT Prepaid BM
        if ($total_biaya_masuk > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => null,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_bm,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => null,
                'no_coil'         => null,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_po,
                'no_request'      => $no_ros,
                'debet'           => 0,
                'kredit'          => $total_biaya_masuk,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT Hutang Forwarder
        if ($total_forwarding > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => null,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_forward,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => null,
                'no_coil'         => null,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_po,
                'no_request'      => $no_ros,
                'debet'           => 0,
                'kredit'          => $total_forwarding,
                'created_at'      => $created_on,
            ]);
        }
    }

    private function _generate_nomor_jv_ros()
    {
        $cabang = $this->db->query(
            "SELECT nomorJC FROM " . DBACC . ".pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1 FOR UPDATE"
        )->row();

        if (empty($cabang)) {
            throw new Exception('Branch data not found for generating JV number!');
        }

        $nomor_urut = (int) $cabang->nomorJC + 1;
        $nomor_jv   = '101-AJV' . date('ym') . $nomor_urut;

        $this->db->query(
            "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'"
        );

        return $nomor_jv;
    }

    // ─── AJAX: Get Coils data for view after upload ──────────────────
    public function get_coils_data()
    {
        $id_ros = $this->input->post('id_ros');

        $this->db->select('c.*, m.nm_barang, m.nm_alias');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material', 'left');
        $this->db->where('m.id_ros', $id_ros);
        $this->db->order_by('m.id', 'ASC');
        $this->db->order_by('c.id', 'ASC');
        $coils = $this->db->get()->result_array();

        echo json_encode(['status' => 1, 'data' => $coils, 'total' => count($coils)]);
    }

    public function close_ros()
    {
        ob_start();
        $id_ros = $this->input->post('id_ros');

        // Cek ROS exists & masih draft
        $header = $this->New_ros_model->get_header($id_ros);
        if (!$header || $header['status'] != '0') {
            echo json_encode(['status' => 0, 'msg' => 'Data not found or status is no longer Draft.']);
            return;
        }

        $materials = $this->New_ros_model->get_materials($id_ros);
        $others    = $this->New_ros_model->get_others($id_ros);

        // ── total komponen biaya ──
        $total_inventory  = 0;
        $total_bm         = 0;
        $total_forwarding = 0;
        $total_ls          = (int) round((float) $header['biaya_ls']);
        $total_insurance   = (int) round((float) $header['insurance']);
        $total_others_val = 0;

        foreach ($materials as $mat) {
            $total_inventory  += (int) round((float) $mat['total_nilai_inventory']);
            $total_bm         += (int) round((float) $mat['bm_rp']);
            $total_forwarding += (int) round((float) $mat['forwarding_cost']);
        }
        foreach ($others as $ot) {
            $total_others_val += (int) round((float) $ot['nilai']);
        }

        // ── Nilai DP ──
        $po_data          = $this->db->get_where('tr_purchase_order', ['no_po' => $header['no_po']])->row();
        $uang_muka_idr_po = $po_data ? (float) $po_data->uang_muka_idr : 0;
        $nilai_dp_rp      = (float) $header['nilai_po_pib_rp'];
        $kurs_pib         = (float) $header['kurs_pib'];

        // ── Selisih Kurs ──
        $selisih_kurs     = ($uang_muka_idr_po > 0) ? ($nilai_dp_rp - $uang_muka_idr_po) : 0;
        $selisih_kurs_abs = abs($selisih_kurs);

        // ── Pembulatan ──
        $total_kredit = $nilai_dp_rp
            + $total_bm
            + $total_ls
            + $total_forwarding
            + $total_insurance
            + $total_others_val
            + ($selisih_kurs > 0 ? $selisih_kurs_abs : 0);

        $total_debet = $total_inventory
            + ($selisih_kurs < 0 ? $selisih_kurs_abs : 0);

        $pembulatan = $total_kredit - $total_debet;

        // ── Validasi COA sebelum proses ──
        if ($total_inventory > 0) {
            $coa = [
                'transit' => '1105-01-03',
                'dp'      => '1104-01-02',
                'bm'      => '1108-01-09',
                'ls'      => '1111-01-01',
                'fwd'     => '2104-01-14',
                'ins'     => '1111-01-02',
                'oth'     => '1111-01-03',
                'kurs'    => '7201-01-07',
                'round'   => '7201-01-05',
            ];

            $coa_check = $this->_validate_and_get_coa_names($coa);
            if (!$coa_check['valid']) {
                echo json_encode([
                    'status' => 3,
                    'msg'    => 'The following COA numbers are not registered in the Master COA and must be added first: '
                        . implode(', ', $coa_check['not_found']),
                ]);
                return;
            }
        }

        // ── Update status ROS ──
        $this->db->trans_begin();
        $this->db->update('tr_ros_header', [
            'status'          => '1',
            'status_incoming' => 'open',
            'modified_by'     => $this->auth->user_id(),
            'modified_on'     => date('Y-m-d H:i:s')
        ], ['id' => $id_ros]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Failed to update ROS status.']);
            return;
        }
        $this->db->trans_commit();

        // data header untuk hitung raw
        $total_kg_pib = (float) $header['total_kg_bersih_pib'];
        $kurs_pib     = (float) $header['kurs_pib'];
        $biaya_ls     = (float) $header['biaya_ls'];
        $insurance    = (float) $header['insurance'];
        $forwarding_master = $this->db->get_where('master_forwarding_cost', [
            'is_delete' => 0
        ])->row();

        $tarif_forwarding = (float) $forwarding_master->value_cost;

        foreach ($materials as $mat) {
            // total_nilai_inventory on-the-fly
            $total_value_rp_raw = (float)$mat['unit_price_usd'] * (float)$mat['kg_unit'] * $kurs_pib;
            $bm_rp_raw          = $total_value_rp_raw * (float)$mat['bm_persen'] / 100;
            $prorate_ls_raw     = $biaya_ls * (float)$mat['kg_unit'] / $total_kg_pib;
            $forwarding_raw     = (float)$mat['kg_unit'] * $tarif_forwarding;
            $insurance_raw      = $insurance * (float)$mat['kg_unit'] / $total_kg_pib;

            $total_nilai_inv_raw = $total_value_rp_raw + $bm_rp_raw + $prorate_ls_raw
                + $forwarding_raw + $insurance_raw;

            $cost_book_raw = $total_nilai_inv_raw / (float)$mat['kg_unit'];
            $coils_mat = $this->db->get_where('tr_ros_material_coil', [
                'id_ros_material' => $mat['id']
            ])->result_array();

            foreach ($coils_mat as $coil) {
                $berat_bersih   = (float) $coil['berat_bersih'];
                $price_per_coil = round($berat_bersih * $cost_book_raw, 2);

                $this->db->update('tr_ros_material_coil', [
                    'cost_book_raw'  => $cost_book_raw,
                    'price_per_coil' => $price_per_coil,
                ], ['id' => $coil['id']]);
            }
        }

        // ── Generate Jurnal GL Interface ──
        if ($total_inventory > 0) {
            try {
                $this->_generate_jurnal_ros(
                    $id_ros,
                    $header['no_po'],
                    $header['no_surat'],
                    $header['id_supplier'],
                    $total_inventory,
                    $nilai_dp_rp,
                    $uang_muka_idr_po,
                    $total_bm,
                    $total_ls,
                    $total_forwarding,
                    $total_insurance,
                    $total_others_val,
                    $selisih_kurs,
                    $pembulatan,
                    $kurs_pib
                );
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['status' => 1, 'msg' => 'ROS closed successfully and JV journal has been created.']);
                exit;
            } catch (Exception $e) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['status' => 2, 'msg' => 'ROS closed, but journal error: ' . $e->getMessage()]);
                exit;
            }
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 1, 'msg' => 'ROS closed successfully.']);
            exit;
        }
    }

    private function _generate_jurnal_ros(
        $id_ros,
        $no_po,
        $no_surat,
        $id_supplier,
        $total_inventory,
        $nilai_dp_rp,
        $uang_muka_idr_po,
        $total_bm,
        $total_ls,
        $total_forwarding,
        $total_insurance,
        $total_others,
        $selisih_kurs,
        $pembulatan,
        $kurs_pib
    ) {
        $tgl_inv    = date('Y-m-d');
        $created_on = date('Y-m-d H:i:s');
        $user_id    = $this->auth->user_id();

        $coa = [
            'transit' => '1105-01-03',
            'dp'      => '1104-01-02',
            'hutang_belum_tagih' => '2101-01-06',
            'bm'      => '1108-01-09',
            'ls'      => '1111-01-01',
            'fwd'     => '2104-01-14',
            'ins'     => '1111-01-02',
            'oth'     => '1111-01-03',
            'kurs'    => '7201-01-07',
            'round'   => '7201-01-05',
        ];

        // ── nama COA dari DBACC ──
        $coa_check = $this->_validate_and_get_coa_names($coa);
        if (!$coa_check['valid']) {
            throw new Exception('COA not found in Master: ' . implode(', ', $coa_check['not_found']));
        }
        $coa_names = $coa_check['names'];

        $keterangan = "ROS: {$id_ros} | PO: {$no_surat}";
        $nomor_jv   = $this->_generate_nomor_jv_ros();

        // ── Insert header GL Interface ──
        $this->db->insert('gl_interface', [
            'nomor'           => $nomor_jv,
            'tgl'             => $tgl_inv,
            'bulan'           => date('m'),
            'tahun'           => date('Y'),
            'kdcab'           => '101',
            'jenis'           => 'JV',
            'keterangan'      => $keterangan,
            'jenis_transaksi' => 'ros',
            'status'          => 'pending',
            'user_id'         => $user_id,
            'memo'            => json_encode([
                'id_supplier' => $id_supplier,
                'no_reff'     => $no_surat,
                'no_request'  => $id_ros,
                'kurs_pib'    => $kurs_pib,
                'nilai_dp_rp' => $nilai_dp_rp,
                'dp_dibayar'  => $uang_muka_idr_po,
            ]),
        ]);
        $id_gl = $this->db->insert_id();

        // ── Helper insert detail ──
        $ins = function ($no_coa, $desc, $debet, $kredit) use ($id_gl, $tgl_inv, $no_surat, $id_ros, $created_on, $nomor_jv) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => $nomor_jv,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $no_coa,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => null,
                'no_coil'         => null,
                'keterangan'      => $desc,
                'no_reff'         => $no_surat,
                'no_request'      => $id_ros,
                'debet'           => (int) round($debet),
                'kredit'          => (int) round($kredit),
                'created_at'      => $created_on,
            ]);
        };

        // ── Insert detail jurnal ──

        // 1. DEBET — Persediaan In Transit
        $ins($coa['transit'], $coa_names['transit'] . " | {$keterangan}", $total_inventory, 0);

        // 2. KREDIT — Advance Purchase ($) — dari jumlah_rupiah tr_receive_invoice_dp
        $total_dp_rupiah = (float)($this->db
            ->select_sum('jumlah_rupiah')
            ->where('no_po', $no_po)
            ->get('tr_receive_invoice_dp')
            ->row()
            ->jumlah_rupiah ?? 0);
        $ins($coa['dp'], $coa_names['dp'] . " | {$keterangan}", 0, $total_dp_rupiah);

        // 3. KREDIT — Hutang Belum Tertagih (2101-01-06)
        // Rumus: (nilai_po_usd dari tr_ros_header - SUM nilai dari tr_top_po WHERE group_top=76) × kurs_pib
        $ros_header = $this->db->get_where('tr_ros_header', ['id' => $id_ros])->row();
        $nilai_po_usd = (float)($ros_header->nilai_po_usd ?? 0);
        $kurs_pib_ros = (float)($ros_header->kurs_pib ?? $kurs_pib);

        $sum_top_76 = (float)($this->db
            ->select_sum('nilai')
            ->where('no_po', $no_po)
            ->where('group_top', 76)
            ->get('tr_top_po')
            ->row()
            ->nilai ?? 0);

        $hutang_belum_tagih = ($nilai_po_usd - $sum_top_76) * $kurs_pib_ros;
        $ins($coa['hutang_belum_tagih'], $coa_names['hutang_belum_tagih'] . " | {$keterangan}", 0, (int)round($hutang_belum_tagih));

        // 4. KREDIT — BM Dibayar Dimuka
        $ins($coa['bm'], $coa_names['bm'] . " | {$keterangan}", 0, $total_bm);

        // 5. KREDIT — Prepaid Expense LS
        $ins($coa['ls'], $coa_names['ls'] . " | {$keterangan}", 0, $total_ls);

        // 6. KREDIT — Hutang Biaya Forwarding
        $ins($coa['fwd'], $coa_names['fwd'] . " | {$keterangan}", 0, $total_forwarding);

        // 7. KREDIT — Prepaid Expense Insurance
        $ins($coa['ins'], $coa_names['ins'] . " | {$keterangan}", 0, $total_insurance);

        // 8. KREDIT — Prepaid Expense Other
        $ins($coa['oth'], $coa_names['oth'] . " | {$keterangan}", 0, $total_others);

        // 9. DEBET/KREDIT — Selisih Kurs
        // Hitung selisih kurs berdasarkan nilai DP yang benar-benar di-kredit-kan (total_dp_rupiah)
        // vs nilai DP pada kurs PIB/ROS (nilai_dp_rp).
        // Jika kurs saat bayar DP berbeda dengan kurs PIB, selisihnya masuk ke akun selisih kurs.
        $selisih_kurs_actual = ($total_dp_rupiah > 0) ? ($nilai_dp_rp - $total_dp_rupiah) : 0;
        $ins(
            $coa['kurs'],
            $coa_names['kurs'] . " (PIB: " . number_format($kurs_pib, 0, ',', '.') . ") | {$keterangan}",
            ($selisih_kurs_actual < 0) ? abs($selisih_kurs_actual) : 0,
            ($selisih_kurs_actual > 0) ? $selisih_kurs_actual       : 0
        );

        // 10. DEBET/KREDIT — Pembulatan
        // Hitung ulang pembulatan berdasarkan nilai aktual yang diposting agar jurnal balance
        $total_debet_posted  = $total_inventory
            + (($selisih_kurs_actual < 0) ? abs($selisih_kurs_actual) : 0);
        $total_kredit_posted = $total_dp_rupiah
            + (int)round($hutang_belum_tagih)
            + $total_bm
            + $total_ls
            + $total_forwarding
            + $total_insurance
            + $total_others
            + (($selisih_kurs_actual > 0) ? $selisih_kurs_actual : 0);

        $pembulatan_actual = $total_kredit_posted - $total_debet_posted;
        // Jika kredit > debet, pembulatan di sisi debet (positif). Jika debet > kredit, pembulatan di sisi kredit (negatif).
        $ins(
            $coa['round'],
            $coa_names['round'] . " | {$keterangan}",
            ($pembulatan_actual > 0) ? $pembulatan_actual       : 0,
            ($pembulatan_actual < 0) ? abs($pembulatan_actual)  : 0
        );
    }

    // ─── AJAX: Get data ROS untuk preview modal close ────────────────
    public function get_ros_preview()
    {
        $id_ros = $this->input->post('id_ros');

        $header = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            echo json_encode(['status' => 0, 'msg' => 'Data not found.']);
            return;
        }

        $materials = $this->New_ros_model->get_materials($id_ros);
        foreach ($materials as &$mat) {
            $coils = $this->New_ros_model->get_coils($mat['id']);

            $seen = [];
            $unique_coils = [];
            foreach ($coils as $coil) {
                $key = $coil['no_coil'] . '_' . $coil['id_ros_material'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $unique_coils[] = $coil;
                }
            }
            $mat['coils'] = $unique_coils;
        }
        unset($mat);

        $others = $this->New_ros_model->get_others($id_ros);

        $total_others_val = 0;
        foreach ($others as $ot) {
            $total_others_val += (float) $ot['nilai'];
        }

        $total_fc = $header['cost_bm'] + $header['cost_bm_kite'] + $header['cost_bmt']
            + $header['cost_cukai'] + $header['cost_ppn'] + $header['cost_ppnbm']
            + $header['cost_pph_import'];

        $total_coil = 0;
        $total_nw   = 0;
        $total_gw   = 0;
        foreach ($materials as $mat) {
            if (!empty($mat['coils'])) {
                $total_coil += count($mat['coils']);
                foreach ($mat['coils'] as $coil) {
                    $total_nw += (float) $coil['berat_bersih'];
                    $total_gw += (float) $coil['berat_kotor'];
                }
            }
        }

        echo json_encode([
            'status'           => 1,
            'header'           => $header,
            'materials'        => $materials,
            'others'           => $others,
            'total_others_val' => $total_others_val,
            'total_fc'         => $total_fc,
            'total_coil'       => $total_coil,
            'total_nw'         => $total_nw,
            'total_gw'         => $total_gw,
        ]);
    }

    private function _validate_and_get_coa_names(array $coa_list)
    {
        $db_acc    = $this->load->database(DBACC, TRUE);
        $not_found = [];
        $names     = [];

        foreach ($coa_list as $key => $no_perkiraan) {
            $row = $db_acc->get_where('coa_master', ['no_perkiraan' => $no_perkiraan])->row();
            if (!$row) {
                $not_found[] = $no_perkiraan;
            } else {
                $names[$key] = $row->nama;
            }
        }

        return [
            'valid'     => empty($not_found),
            'names'     => $names,
            'not_found' => $not_found,
        ];
    }
}
