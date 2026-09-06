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

        $tab   = $this->input->post('tab') ?: 'open';
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

            // Badge status
            $sts = '<span class="badge rounded-pill bg-warning">Draft</span>';
            if ($row['status'] == '1') {
                $status_payment = isset($row['status_payment']) ? $row['status_payment'] : null;
                if ($status_payment == 'proses_payment') {
                    $sts = '<span class="badge rounded-pill bg-info">Payment Process</span>';
                } elseif ($status_payment == 'close') {
                    $sts = '<span class="badge rounded-pill bg-success">Payment Completed</span>';
                } else {
                    $sts = '<span class="badge rounded-pill bg-success">Final</span>';
                }
            }

            $action_buttons = '
            <div style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                <div style="display: flex; gap: 5px;">
                    ' . $edit_btn . '
                    ' . $del_btn . '
                </div>
                <div style="display: flex; gap: 5px;">
                    ' . $close_btn . '
                </div>
            </div>';

            // Nomor ROS jadi link ke View
            $ros_link = '<a href="' . base_url('new_ros/view/' . $row['id']) . '" title="View ROS">' . $row['id'] . '</a>';

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$nomor}</div>";
            $nestedData[] = "<div class='text-left'>{$ros_link}</div>";
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

    // ─── AJAX: List ROS di Payment Process (grouped per ROS + detail payment) ──
    public function get_payment_process_list()
    {
        $search = trim((string) $this->input->post('search'));

        // Ambil ROS Import yang status_payment = proses_payment
        $this->db->select('a.id, a.no_po, a.no_surat, a.nm_supplier, a.nilai_po_pib_rp, a.created_on');
        $this->db->from('tr_ros_header a');
        $this->db->where('a.status', '1');
        $this->db->where('a.status_payment', 'proses_payment');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.id', $search);
            $this->db->or_like('a.no_po', $search);
            $this->db->or_like('a.no_surat', $search);
            $this->db->or_like('a.nm_supplier', $search);
            $this->db->group_end();
        }

        $this->db->order_by('a.created_on', 'DESC');
        $ros_list = $this->db->get()->result_array();

        // Ambil semua payment untuk ROS-ROS tsb
        $result = [];
        foreach ($ros_list as $ros) {
            $payments = $this->db->get_where('tr_ros_payment', ['id_ros_header' => $ros['id']])->result_array();
            $ros['payments'] = $payments;
            $result[] = $ros;
        }

        echo json_encode(['status' => 1, 'data' => $result]);
    }

    // ─── AJAX: Jumlah ROS di Payment Process (untuk badge) ──
    public function get_payment_process_count()
    {
        $this->db->where('status', '1');
        $this->db->where('status_payment', 'proses_payment');
        $count = $this->db->count_all_results('tr_ros_header');

        echo json_encode(['status' => 1, 'count' => (int) $count]);
    }

    // ─── AJAX: Ajukan satu baris payment (belum_diajukan -> diajukan) ──
    // Selain ubah status tr_ros_payment, juga INSERT ke request_payment
    // (mengikuti pola invoice_import/invoice_local) agar muncul di menu Request Payment.
    public function ajukan_payment()
    {
        $id_payment = $this->input->post('id_payment');
        $bank_id    = trim((string) $this->input->post('bank_id'));     // free-text nama bank
        $accnumber  = trim((string) $this->input->post('accnumber'));   // free-text no rekening
        $accname    = trim((string) $this->input->post('accname'));     // free-text atas nama

        $payment = $this->db->get_where('tr_ros_payment', ['id' => $id_payment])->row();
        if (!$payment) {
            echo json_encode(['status' => 0, 'msg' => 'Data payment tidak ditemukan.']);
            return;
        }

        if ($payment->status !== 'belum_diajukan') {
            echo json_encode(['status' => 0, 'msg' => 'Payment ini sudah diajukan atau sudah lunas.']);
            return;
        }

        if ($bank_id === '' || $accnumber === '' || $accname === '') {
            echo json_encode(['status' => 0, 'msg' => 'Bank, No. Rekening, dan Atas Nama wajib diisi.']);
            return;
        }

        // Ambil header ROS untuk data supplier & no_po
        $header = $this->New_ros_model->get_header($payment->id_ros_header);
        if (!$header) {
            echo json_encode(['status' => 0, 'msg' => 'Data ROS tidak ditemukan.']);
            return;
        }

        // Mapping tipe request_payment + label keperluan
        $tipe_map = [
            'bm'         => ['tipe' => 'ros_bm',         'label' => 'BM'],
            'ls'         => ['tipe' => 'ros_ls',         'label' => 'LS (Surveyor)'],
            'insurance'  => ['tipe' => 'ros_insurance',  'label' => 'Insurance'],
            'other_cost' => ['tipe' => 'ros_other_cost', 'label' => 'Other Cost'],
        ];
        $map      = isset($tipe_map[$payment->payment_type]) ? $tipe_map[$payment->payment_type] : null;
        if (!$map) {
            echo json_encode(['status' => 0, 'msg' => 'Tipe payment tidak dikenali.']);
            return;
        }
        $tipe_rp = $map['tipe'];

        $get_user   = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();
        $keterangan = 'Pembayaran ' . $map['label'] . ' ROS - ' . $header['id']
            . ' - ' . ($header['no_surat'] ?: $header['no_po'])
            . (($payment->payment_type === 'other_cost' && $payment->keterangan) ? ' (' . $payment->keterangan . ')' : '');

        $data_insert = [
            'no_doc'      => $header['no_po'] ?? '',
            'no_surat'    => $header['no_surat'] ?? '',
            'nama'        => $get_user['nm_lengkap'] ?? $this->auth->user_name(),
            'tgl_doc'     => date('Y-m-d'),
            'keperluan'   => $keterangan,
            'tipe'        => $tipe_rp,
            'jumlah'      => (float) $payment->nominal,
            'status'      => 'open',
            'tanggal'     => null,
            'currency'    => 'IDR',
            'bank_id'     => $bank_id,
            'accnumber'   => $accnumber,
            'accname'     => $accname,
            'ids'         => (string) $payment->id,   // referensi ke tr_ros_payment.id
            'id_ros'      => $header['id'] ?? null,
            'admin_bank'  => 0,
            'total_pph'   => 0,
            'id_supplier' => $header['id_supplier'] ?? '',
            'nm_supplier' => $header['nm_supplier'] ?? '',
            'created_by'  => $get_user['nm_lengkap'] ?? $this->auth->user_name(),
            'created_on'  => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_begin();

        // Insert ke request_payment
        $this->db->insert('request_payment', $data_insert);
        $id_rp = $this->db->insert_id();

        // Update tr_ros_payment: status + id_request_payment
        $this->db->update('tr_ros_payment', [
            'status'             => 'diajukan',
            'id_request_payment' => $id_rp,
            'modified_by'        => $this->auth->user_id(),
            'modified_on'        => date('Y-m-d H:i:s'),
        ], ['id' => $id_payment]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Gagal mengajukan payment.']);
            return;
        }
        $this->db->trans_commit();

        echo json_encode(['status' => 1, 'msg' => 'Payment berhasil diajukan ke Request Payment.']);
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

        $this->db->select('no_po, no_surat, loi');
        $this->db->from('tr_purchase_order');
        $this->db->where('id_suplier', $id_supplier);
        $this->db->where('status', 2);
        $this->db->where("(close_po IS NULL OR close_po != '1')", NULL, FALSE);

        $this->db->order_by('no_po', 'DESC');
        $list = $this->db->get()->result_array();

        echo json_encode(['status' => 1, 'data' => $list]);
    }

    // ─── AJAX: Validasi DP PO sebelum load materials ───────────────
    public function check_dp_status()
    {
        $no_po = $this->input->post('no_po');

        // Cek apakah PO ini punya TOP DP (group_top = 76)
        $top_dp = $this->db->get_where('tr_top_po', [
            'no_po'     => $no_po,
            'group_top' => 76
        ])->row();

        if (!$top_dp) {
            // Tidak ada term DP, tidak perlu validasi — lanjut
            echo json_encode(['status' => 1, 'dp_required' => false]);
            return;
        }

        // Ada term DP, cek apakah sudah dibayar (tr_receive_invoice tipe=dp, status=payment)
        $invoice_dp = $this->db->get_where('tr_receive_invoice', [
            'id_top'  => $top_dp->id,
            'tipe'    => 'dp',
            'status'  => 'payment'
        ])->row();

        if ($invoice_dp) {
            // DP sudah dibayar — lanjut
            echo json_encode(['status' => 1, 'dp_required' => false]);
        } else {
            // DP belum dibayar — block
            echo json_encode([
                'status'      => 1,
                'dp_required' => true,
                'message'     => 'PO ini memiliki term DP yang belum dibayar. Silakan proses pembayaran DP terlebih dahulu.',
                'link'        => base_url('purchase_order_payment/index/dp')
            ]);
        }
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
        $this->db->select('no_po, no_surat, loi');
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
            $coils = $this->New_ros_model->get_coils($mat['id']);

            $seen = [];
            $unique_coils = [];
            foreach ($coils as $coil) {
                // Skip mother coil yang punya baby (bukan unit fisik) — sama seperti mode edit/finalize
                $is_mother_with_baby = ((int) $coil['is_baby_coil'] === 0 && (int) $coil['qty_roll'] > 1);
                if ($is_mother_with_baby) continue;

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

        // Ambil loi (Lokal/Import) dari PO
        $po_data = $this->db->get_where('tr_purchase_order', ['no_po' => $header['no_po']])->row();
        $loi = $po_data ? $po_data->loi : 'Import';

        $this->template->set('header', $header);
        $this->template->set('materials', $materials);
        $this->template->set('others', $others);
        $this->template->set('loi', $loi);
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

        // Sertakan info loi (Lokal/Import) dan total PO value
        $loi = $po ? $po->loi : 'Import';
        $total_po_value = array_sum(array_column($result, 'total_value_usd'));

        echo json_encode([
            'status' => 1,
            'data'   => $result,
            'loi'    => $loi,
            'total_po_value' => $total_po_value
        ]);
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

        // ── Tentukan tipe PO: Lokal atau Import ──
        $po_data_save  = $this->db->get_where('tr_purchase_order', ['no_po' => $post['no_po']])->row();
        $is_lokal_save = ($po_data_save && strtolower($po_data_save->loi) === 'lokal');
        $jenis_po      = $is_lokal_save ? 'lokal' : 'import';

        // ── Header ──
        $header_data = [
            'id_supplier'        => $post['id_supplier'],
            'nm_supplier'        => $get_supplier ? $get_supplier['nama'] : '',
            'no_po'              => $post['no_po'],
            'jenis_po'           => $jenis_po,
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

            // Hapus pack lama
            $this->db->delete('tr_ros_pack', ['id_ros' => $id_ros]);

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
        $all_pack_coils = []; // Collect pack_no → coil data untuk generate tr_ros_pack nanti
        if (isset($post['mat']) && is_array($post['mat'])) {
            foreach ($post['mat'] as $mat) {
                $kg_unit         = (float) str_replace(',', '', $mat['kg_unit']);
                $unit_price_usd  = (float) str_replace(',', '', $mat['unit_price_usd']);
                $total_value_usd = (float) str_replace(',', '', $mat['total_value_usd']);
                $total_value_rp  = $total_value_usd * $kurs_pib;
                $bm_persen       = (float) $mat['bm_persen'];
                $ls_flag         = isset($mat['ls_flag']) ? $mat['ls_flag'] : 'TIDAK';

                if ($is_lokal_save) {
                    // PO Lokal: tidak ada komponen biaya tambahan
                    $bm_rp             = 0;
                    $prorate_ls        = 0;
                    $forwarding_cost   = 0;
                    $prorate_insurance = 0;
                    $prorate_others    = 0;
                } else {
                    $bm_rp = $total_value_rp * ($bm_persen / 100);

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

                    // Pisahkan mother coils dan baby coils
                    $mother_coils_save = [];
                    $baby_coils_save   = [];
                    foreach ($valid_coils as $coil) {
                        if (isset($coil['is_baby_coil']) && (int) $coil['is_baby_coil'] === 1) {
                            $baby_coils_save[] = $coil;
                        } else {
                            $mother_coils_save[] = $coil;
                        }
                    }

                    // Hitung jumlah coil fisik (baby + normal, skip mother yang punya baby)
                    $physical_coil_count = 0;
                    foreach ($mother_coils_save as $mc) {
                        $mc_qty = isset($mc['qty_roll']) ? (int) $mc['qty_roll'] : 1;
                        if ($mc_qty <= 1) {
                            $physical_coil_count++; // normal coil (tanpa baby)
                        }
                    }
                    $physical_coil_count += count($baby_coils_save);
                    $price_per_coil_physical = ($physical_coil_count > 0) ? $total_nilai_inventory / $physical_coil_count : 0;

                    // Mapping no_coil mother → inserted ID
                    $mother_id_map_save = [];

                    // Insert Mother Coils terlebih dahulu
                    foreach ($mother_coils_save as $coil) {
                        $coil_pack_no = isset($coil['pack_no']) && $coil['pack_no'] !== '' ? (int) $coil['pack_no'] : null;
                        $coil_qty_roll = isset($coil['qty_roll']) ? (int) $coil['qty_roll'] : 1;

                        // Mother dengan baby → price = 0, Normal coil → price = price_per_coil_physical
                        $mother_price = ($coil_qty_roll > 1) ? 0 : $price_per_coil_physical;

                        $this->db->insert('tr_ros_material_coil', [
                            'id_ros_material' => $id_ros_material,
                            'no_coil'         => $coil['no_coil'],
                            'berat_kotor'     => (float) str_replace(',', '', $coil['berat_kotor']),
                            'berat_bersih'    => (float) str_replace(',', '', $coil['berat_bersih']),
                            'panjang'         => (float) str_replace(',', '', $coil['panjang']),
                            'qty_roll'        => $coil_qty_roll,
                            'kode_internal'   => isset($coil['kode_internal']) ? $coil['kode_internal'] : '',
                            'parent_coil_id'  => null,
                            'is_baby_coil'    => 0,
                            'bpm'             => isset($coil['bpm']) ? (float) str_replace(',', '', $coil['bpm']) : 0,
                            'price_per_coil'  => $mother_price,
                            'created_by'      => $this->auth->user_id(),
                            'created_on'      => date('Y-m-d H:i:s')
                        ]);

                        $coil_id_inserted = $this->db->insert_id();
                        $mother_id_map_save[$coil['no_coil']] = $coil_id_inserted;

                        // Collect pack_no untuk proses nanti
                        if ($coil_pack_no !== null) {
                            if (!isset($all_pack_coils[$coil_pack_no])) {
                                $all_pack_coils[$coil_pack_no] = [];
                            }
                            $all_pack_coils[$coil_pack_no][] = [
                                'coil_id' => $coil_id_inserted,
                                'id_ros_material' => $id_ros_material
                            ];
                        }
                    }

                    // Insert Baby Coils dengan parent_coil_id
                    foreach ($baby_coils_save as $coil) {
                        $coil_pack_no = isset($coil['pack_no']) && $coil['pack_no'] !== '' ? (int) $coil['pack_no'] : null;

                        // Resolve parent_coil_id: cari mother coil dari no_coil baby
                        // Baby no_coil format: PARENT-01, PARENT-02, jadi parent = semua sebelum -XX terakhir
                        $parent_coil_id = null;
                        $no_coil_str = $coil['no_coil'];
                        $last_dash = strrpos($no_coil_str, '-');
                        if ($last_dash !== false) {
                            $parent_no_coil = substr($no_coil_str, 0, $last_dash);
                            if (isset($mother_id_map_save[$parent_no_coil])) {
                                $parent_coil_id = $mother_id_map_save[$parent_no_coil];
                            }
                        }

                        $this->db->insert('tr_ros_material_coil', [
                            'id_ros_material' => $id_ros_material,
                            'no_coil'         => $coil['no_coil'],
                            'berat_kotor'     => (float) str_replace(',', '', $coil['berat_kotor']),
                            'berat_bersih'    => (float) str_replace(',', '', $coil['berat_bersih']),
                            'panjang'         => (float) str_replace(',', '', $coil['panjang']),
                            'qty_roll'        => 1,
                            'kode_internal'   => isset($coil['kode_internal']) ? $coil['kode_internal'] : '',
                            'parent_coil_id'  => $parent_coil_id,
                            'is_baby_coil'    => 1,
                            'bpm'             => isset($coil['bpm']) ? (float) str_replace(',', '', $coil['bpm']) : 0,
                            'price_per_coil'  => $price_per_coil_physical,
                            'created_by'      => $this->auth->user_id(),
                            'created_on'      => date('Y-m-d H:i:s')
                        ]);

                        $coil_id_inserted = $this->db->insert_id();

                        // Collect pack_no untuk proses nanti
                        if ($coil_pack_no !== null) {
                            if (!isset($all_pack_coils[$coil_pack_no])) {
                                $all_pack_coils[$coil_pack_no] = [];
                            }
                            $all_pack_coils[$coil_pack_no][] = [
                                'coil_id' => $coil_id_inserted,
                                'id_ros_material' => $id_ros_material
                            ];
                        }
                    }
                }
            }
        }

        // ── Generate Pack records dari data coil ──
        if (!empty($all_pack_coils)) {
            $material_pack_assigned = [];
            foreach ($all_pack_coils as $pack_no => $pack_coil_list) {
                $pack_code = $this->New_ros_model->generate_pack_code();
                $this->db->insert('tr_ros_pack', [
                    'id_ros'     => $id_ros,
                    'pack_no'    => $pack_no,
                    'pack_code'  => $pack_code,
                    'created_by' => $this->auth->user_id(),
                    'created_on' => date('Y-m-d H:i:s')
                ]);
                $id_ros_pack = $this->db->insert_id();

                // Update id_ros_pack di coils
                foreach ($pack_coil_list as $pc) {
                    $this->db->update('tr_ros_material_coil', ['id_ros_pack' => $id_ros_pack], ['id' => $pc['coil_id']]);
                    // Track pack per material
                    if (!isset($material_pack_assigned[$pc['id_ros_material']])) {
                        $material_pack_assigned[$pc['id_ros_material']] = $id_ros_pack;
                    }
                }
            }

            // Update id_ros_pack di tr_ros_material
            foreach ($material_pack_assigned as $mat_id => $pack_id) {
                $this->db->update('tr_ros_material', ['id_ros_pack' => $pack_id], ['id' => $mat_id]);
            }
        }

        // ── Hitung dan simpan GL Values ke tr_ros_header ──
        $this->_calculate_and_save_gl_values($id_ros);

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
        $this->db->delete('tr_ros_pack', ['id_ros' => $id_ros]);
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
        $sheet->getColumnDimension('H')->setWidth(12);  // Qty Roll
        $sheet->getColumnDimension('I')->setWidth(10);  // Pack

        // ── Header Row ──
        $sheet->setCellValue('A1', 'COIL NO.');
        $sheet->setCellValue('B1', 'Alias Name');
        $sheet->setCellValue('C1', 'Original Name');
        $sheet->setCellValue('D1', "N.W.\n(KGS)");
        $sheet->setCellValue('E1', "G.W.\n(KGS)");
        $sheet->setCellValue('F1', "LENGTH\n(M)");
        $sheet->setCellValue('G1', 'BPM');
        $sheet->setCellValue('H1', "Qty\nRoll");
        $sheet->setCellValue('I1', 'Pack');
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
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
                $sheet->setCellValue('H' . $row, '');            // Qty Roll
                $sheet->setCellValue('I' . $row, '');            // Pack

                $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($matStyle);
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
        $start_row      = 2;
        $col_coil_no    = 'A';
        $col_nama_alias = 'B';
        $col_nm_barang  = 'C';
        $col_nw         = 'D';
        $col_gw         = 'E';
        $col_length     = 'F';
        $col_bpm        = 'G';
        $col_qty_roll   = 'H';
        $col_pack       = 'I';

        for ($r = 1; $r <= min($highRow, 10); $r++) {
            $cellA = strtolower(trim((string) $sheet->getCell('A' . $r)->getValue()));
            $cellB = strtolower(trim((string) $sheet->getCell('B' . $r)->getValue()));

            if (strpos($cellA, 'coil no') !== false) {
                // Format baru: A=COIL NO, B=Alias, C=Nama Asli, D=NW, E=GW, F=Length, G=BPM, H=Qty Roll, I=Pack
                $col_coil_no    = 'A';
                $col_nama_alias = 'B';
                $col_nm_barang  = 'C';
                $col_nw         = 'D';
                $col_gw         = 'E';
                $col_length     = 'F';
                $col_bpm        = 'G';
                $col_qty_roll   = 'H';
                $col_pack       = 'I';
                $start_row      = $r + 1;
                break;
            } elseif (strpos($cellB, 'coil no') !== false) {
                // Format lama fallback: B=COIL NO, C=Alias, D=Number, E=NW, F=GW, G=Length, H=BPM, I=Qty Roll, J=Pack
                $col_coil_no    = 'B';
                $col_nama_alias = 'C';
                $col_nm_barang  = 'D';
                $col_nw         = 'E';
                $col_gw         = 'F';
                $col_length     = 'G';
                $col_bpm        = 'H';
                $col_qty_roll   = 'I';
                $col_pack       = 'J';
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
            $qty_roll   = $getCellValue($col_qty_roll);
            $pack       = $getCellValue($col_pack);

            if (empty($coil_no) || strtolower($coil_no) == 'total') continue;
            if (strpos(strtolower($coil_no), 'error') !== false) continue;
            // Skip baris kosong (baris pemisah antar material)
            if (empty($nama_alias) && empty($nm_barang)) continue;

            $nw_val     = (float) str_replace(',', '', (string) $nw);
            $gw_val     = (float) str_replace(',', '', (string) $gw);
            $length_val = (float) str_replace(',', '', (string) $length);
            $bpm_val    = (float) str_replace(',', '', (string) $bpm);
            $qty_roll_val = max(1, (int) $qty_roll);
            $pack_val     = trim((string) $pack) !== '' ? (int) $pack : null;

            // Jika qty_roll > 1, insert mother coil + baby coils
            if ($qty_roll_val > 1) {
                $nw_avg = $nw_val / $qty_roll_val;
                $gw_avg = $gw_val / $qty_roll_val;

                // Mother Coil (record induk)
                $kode_internal_mother = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $coils[] = [
                    'no_coil'        => $coil_no,
                    'parent_no_coil' => null,
                    'is_baby_coil'   => 0,
                    'qty_roll'       => $qty_roll_val,
                    'pack_no'        => $pack_val,
                    'nama_alias'     => $nama_alias,
                    'nm_barang'      => $nm_barang,
                    'berat_bersih'   => $nw_val,
                    'berat_kotor'    => $gw_val,
                    'panjang'        => $length_val,
                    'bpm'            => $bpm_val,
                    'kode_internal'  => $kode_internal_mother,
                ];
                $counter++;

                // Baby Coils (pecahan)
                for ($bc = 1; $bc <= $qty_roll_val; $bc++) {
                    $baby_coil_no  = $coil_no . '-' . str_pad($bc, 2, '0', STR_PAD_LEFT);
                    $kode_internal = $inisial . '-' . $baby_coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

                    $coils[] = [
                        'no_coil'        => $baby_coil_no,
                        'parent_no_coil' => $coil_no,
                        'is_baby_coil'   => 1,
                        'qty_roll'       => 1,
                        'pack_no'        => $pack_val,
                        'nama_alias'     => $nama_alias,
                        'nm_barang'      => $nm_barang,
                        'berat_bersih'   => round($nw_avg, 4),
                        'berat_kotor'    => round($gw_avg, 4),
                        'panjang'        => $length_val,
                        'bpm'            => $bpm_val,
                        'kode_internal'  => $kode_internal,
                    ];
                    $counter++;
                }
            } else {
                $kode_internal = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

                $coils[] = [
                    'no_coil'        => $coil_no,
                    'parent_no_coil' => null,
                    'is_baby_coil'   => 0,
                    'qty_roll'       => 1,
                    'pack_no'        => $pack_val,
                    'nama_alias'     => $nama_alias,
                    'nm_barang'      => $nm_barang,
                    'berat_bersih'   => $nw_val,
                    'berat_kotor'    => $gw_val,
                    'panjang'        => $length_val,
                    'bpm'            => $bpm_val,
                    'kode_internal'  => $kode_internal,
                ];
                $counter++;
            }
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
        // Format Excel baru: A: COIL NO. | B: Alias Name | C: Original Name | D: N.W. | E: G.W. | F: LENGTH | G: BPM | H: Qty Roll | I: Pack
        $start_row   = 2;
        $col_coil_no = 'A';
        $col_nama_po = 'B';
        $col_number  = 'C';
        $col_nw      = 'D';
        $col_gw      = 'E';
        $col_length  = 'F';
        $col_bpm     = 'G';
        $col_qty_roll = 'H';
        $col_pack    = 'I';

        for ($r = 1; $r <= min($highRow, 10); $r++) {
            $cellA = strtolower(trim((string) $sheet->getCell('A' . $r)->getValue()));
            $cellB = strtolower(trim((string) $sheet->getCell('B' . $r)->getValue()));

            if (strpos($cellA, 'coil no') !== false) {
                // Format baru: Data mulai dari kolom A
                $col_coil_no  = 'A';
                $col_nama_po  = 'B';
                $col_number   = 'C';
                $col_nw       = 'D';
                $col_gw       = 'E';
                $col_length   = 'F';
                $col_bpm      = 'G';
                $col_qty_roll = 'H';
                $col_pack     = 'I';
                $start_row    = $r + 1;
                break;
            } elseif (strpos($cellB, 'coil no') !== false) {
                // Format lama fallback: Data mulai dari kolom B
                $col_coil_no  = 'B';
                $col_nama_po  = 'C';
                $col_number   = 'D';
                $col_nw       = 'E';
                $col_gw       = 'F';
                $col_length   = 'G';
                $col_bpm      = 'H';
                $col_qty_roll = 'I';
                $col_pack     = 'J';
                $start_row    = $r + 1;
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
            $qty_roll    = $getCellValue($col_qty_roll);
            $pack        = $getCellValue($col_pack);

            // Skip row kosong atau row TOTAL/ERROR
            if (empty($coil_no) || strtolower($coil_no) == 'total') continue;
            if (strpos(strtolower($coil_no), 'error') !== false) continue;

            $nw_val       = (float) str_replace(',', '', (string) $nw);
            $gw_val       = (float) str_replace(',', '', (string) $gw);
            $length_val   = (float) str_replace(',', '', (string) $length);
            $bpm_val      = (float) str_replace(',', '', (string) $bpm);
            $qty_roll_val = max(1, (int) $qty_roll);
            $pack_val     = trim((string) $pack) !== '' ? (int) $pack : null;

            // Match material
            $nm_po_lower = strtolower(trim($nm_po));
            $id_ros_material = isset($mat_lookup[$nm_po_lower]) ? $mat_lookup[$nm_po_lower] : null;
            $is_matched = $id_ros_material ? 1 : 0;

            // Jika qty_roll > 1, insert mother coil + baby coils
            if ($qty_roll_val > 1) {
                $nw_avg = $nw_val / $qty_roll_val;
                $gw_avg = $gw_val / $qty_roll_val;

                // Insert Mother Coil (record induk)
                $kode_internal_mother = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $this->db->insert('tr_ros_upload_temp', [
                    'id_ros'          => $id_ros,
                    'session_id'      => $session_id,
                    'pack_no'         => $pack_val,
                    'no_coil'         => $coil_no,
                    'parent_no_coil'  => null,
                    'is_baby_coil'    => 0,
                    'nama_sesuai_po'  => $nm_po,
                    'coil_number'     => (int) $coil_number ?: 1,
                    'qty_roll'        => $qty_roll_val,
                    'berat_bersih'    => $nw_val,
                    'berat_kotor'     => $gw_val,
                    'panjang'         => $length_val,
                    'bpm'             => $bpm_val,
                    'id_ros_material' => $id_ros_material,
                    'kode_internal'   => $kode_internal_mother,
                    'is_matched'      => $is_matched,
                    'created_on'      => date('Y-m-d H:i:s')
                ]);
                $rows_parsed++;
                if ($is_matched) $rows_matched++;
                $counter++;

                // Insert Baby Coils (pecahan)
                for ($bc = 1; $bc <= $qty_roll_val; $bc++) {
                    $baby_coil_no  = $coil_no . '-' . str_pad($bc, 2, '0', STR_PAD_LEFT);
                    $kode_internal = $inisial . '-' . $baby_coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

                    $this->db->insert('tr_ros_upload_temp', [
                        'id_ros'          => $id_ros,
                        'session_id'      => $session_id,
                        'pack_no'         => $pack_val,
                        'no_coil'         => $baby_coil_no,
                        'parent_no_coil'  => $coil_no,
                        'is_baby_coil'    => 1,
                        'nama_sesuai_po'  => $nm_po,
                        'coil_number'     => (int) $coil_number ?: 1,
                        'qty_roll'        => 1,
                        'berat_bersih'    => round($nw_avg, 4),
                        'berat_kotor'     => round($gw_avg, 4),
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
            } else {
                // Normal coil (qty_roll = 1)
                $kode_internal = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

                $this->db->insert('tr_ros_upload_temp', [
                    'id_ros'          => $id_ros,
                    'session_id'      => $session_id,
                    'pack_no'         => $pack_val,
                    'no_coil'         => $coil_no,
                    'parent_no_coil'  => null,
                    'is_baby_coil'    => 0,
                    'nama_sesuai_po'  => $nm_po,
                    'coil_number'     => (int) $coil_number ?: 1,
                    'qty_roll'        => 1,
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

        // Hitung price_per_coil per material (hanya coil fisik: baby coils + normal coils)
        // Mother coil dengan qty_roll > 1 TIDAK dihitung karena bukan unit fisik terpisah
        $coil_count_per_mat = [];
        foreach ($temp_data as $row) {
            $is_mother_with_baby = ((int) $row['is_baby_coil'] === 0 && (int) $row['qty_roll'] > 1);
            if ($is_mother_with_baby) continue; // skip mother dari hitungan
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

        // ── Generate Pack records ──
        // Kumpulkan semua pack_no unik dari temp_data
        $pack_nos = [];
        foreach ($temp_data as $row) {
            if (!empty($row['pack_no'])) {
                $pack_nos[$row['pack_no']] = true;
            }
        }

        // Generate tr_ros_pack untuk setiap pack_no unik dan buat mapping pack_no → id
        $pack_map = []; // pack_no => id_ros_pack
        foreach (array_keys($pack_nos) as $pack_no) {
            $pack_code = $this->New_ros_model->generate_pack_code();
            $this->db->insert('tr_ros_pack', [
                'id_ros'     => $id_ros,
                'pack_no'    => $pack_no,
                'pack_code'  => $pack_code,
                'created_by' => $this->auth->user_id(),
                'created_on' => date('Y-m-d H:i:s')
            ]);
            $pack_map[$pack_no] = $this->db->insert_id();
        }

        // ── Insert coils ──
        $inserted = 0;
        $material_pack_assigned = []; // track id_ros_pack per material

        // Pisahkan mother coils dan baby coils
        $mother_coils = [];
        $baby_coils   = [];
        foreach ($temp_data as $row) {
            if ((int) $row['is_baby_coil'] === 0) {
                $mother_coils[] = $row;
            } else {
                $baby_coils[] = $row;
            }
        }

        // Mapping: no_coil mother → inserted ID (untuk parent_coil_id baby)
        $mother_id_map = []; // no_coil => id di tr_ros_material_coil

        // Insert Mother Coils terlebih dahulu
        foreach ($mother_coils as $row) {
            $id_mat         = $row['id_ros_material'];
            $jumlah_coil    = isset($coil_count_per_mat[$id_mat]) ? $coil_count_per_mat[$id_mat] : 0;
            $total_inv      = isset($inventory_per_mat[$id_mat]) ? $inventory_per_mat[$id_mat] : 0;

            // Mother coil dengan qty_roll > 1 → price_per_coil = 0 (bukan unit fisik)
            $qty_roll_mother = isset($row['qty_roll']) ? (int) $row['qty_roll'] : 1;
            if ($qty_roll_mother > 1) {
                $price_per_coil = 0;
            } else {
                $price_per_coil = ($jumlah_coil > 0) ? $total_inv / $jumlah_coil : 0;
            }

            $id_ros_pack = null;
            if (!empty($row['pack_no']) && isset($pack_map[$row['pack_no']])) {
                $id_ros_pack = $pack_map[$row['pack_no']];
            }

            $this->db->insert('tr_ros_material_coil', [
                'id_ros_material' => $id_mat,
                'id_ros_pack'     => $id_ros_pack,
                'no_coil'         => $row['no_coil'],
                'berat_kotor'     => $row['berat_kotor'],
                'berat_bersih'    => $row['berat_bersih'],
                'panjang'         => $row['panjang'],
                'qty_roll'        => isset($row['qty_roll']) ? (int) $row['qty_roll'] : 1,
                'kode_internal'   => $row['kode_internal'],
                'parent_coil_id'  => null,
                'is_baby_coil'    => 0,
                'bpm'             => isset($row['bpm']) ? (float) $row['bpm'] : 0,
                'price_per_coil'  => $price_per_coil,
                'created_by'      => $this->auth->user_id(),
                'created_on'      => date('Y-m-d H:i:s')
            ]);

            $mother_id_map[$row['no_coil']] = $this->db->insert_id();
            $inserted++;

            if ($id_ros_pack && !isset($material_pack_assigned[$id_mat])) {
                $material_pack_assigned[$id_mat] = $id_ros_pack;
            }
        }

        // Insert Baby Coils dengan parent_coil_id
        foreach ($baby_coils as $row) {
            $id_mat         = $row['id_ros_material'];
            $jumlah_coil    = isset($coil_count_per_mat[$id_mat]) ? $coil_count_per_mat[$id_mat] : 0;
            $total_inv      = isset($inventory_per_mat[$id_mat]) ? $inventory_per_mat[$id_mat] : 0;
            $price_per_coil = ($jumlah_coil > 0) ? $total_inv / $jumlah_coil : 0;

            $id_ros_pack = null;
            if (!empty($row['pack_no']) && isset($pack_map[$row['pack_no']])) {
                $id_ros_pack = $pack_map[$row['pack_no']];
            }

            // Resolve parent_coil_id dari mother_id_map
            $parent_coil_id = null;
            if (!empty($row['parent_no_coil']) && isset($mother_id_map[$row['parent_no_coil']])) {
                $parent_coil_id = $mother_id_map[$row['parent_no_coil']];
            }

            $this->db->insert('tr_ros_material_coil', [
                'id_ros_material' => $id_mat,
                'id_ros_pack'     => $id_ros_pack,
                'no_coil'         => $row['no_coil'],
                'berat_kotor'     => $row['berat_kotor'],
                'berat_bersih'    => $row['berat_bersih'],
                'panjang'         => $row['panjang'],
                'qty_roll'        => 1,
                'kode_internal'   => $row['kode_internal'],
                'parent_coil_id'  => $parent_coil_id,
                'is_baby_coil'    => 1,
                'bpm'             => isset($row['bpm']) ? (float) $row['bpm'] : 0,
                'price_per_coil'  => $price_per_coil,
                'created_by'      => $this->auth->user_id(),
                'created_on'      => date('Y-m-d H:i:s')
            ]);
            $inserted++;

            if ($id_ros_pack && !isset($material_pack_assigned[$id_mat])) {
                $material_pack_assigned[$id_mat] = $id_ros_pack;
            }
        }

        // ── Update id_ros_pack di tr_ros_material ──
        foreach ($material_pack_assigned as $id_mat => $id_pack) {
            $this->db->update('tr_ros_material', ['id_ros_pack' => $id_pack], ['id' => $id_mat]);
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

    // ─── CALCULATE & SAVE GL VALUES ke tr_ros_header ─────────────────
    /**
     * Menghitung 10 nilai COA dan menyimpannya ke kolom gl_* di tr_ros_header.
     * Dipanggil setiap kali save() ROS (insert maupun update).
     *
     * COA yang dihitung:
     *  1. 1105-01-03 - PERSEDIAAN BAHAN BAKU INTRANSIT (gl_persediaan_intransit)
     *  2. 1104-01-02 - ADVANCE PURCHASE               (gl_advance_purchase)
     *  3. 2101-01-06 - HUTANG BELUM TERTAGIH           (gl_unbill)
     *  4. 1108-01-09 - BM DIBAYAR DIMUKA               (gl_bm_dibayar_dimuka)
     *  5. 1111-01-01 - PREPAID EXPENSE LS              (gl_prepaid_ls)
     *  6. 2104-01-14 - HUTANG BIAYA FORWADING          (gl_hutang_forwarding)
     *  7. 1111-01-02 - PREPAID EXPENSE INSURANCE       (gl_prepaid_insurance)
     *  8. 1111-01-03 - PREPAID EXPENSE OTHER           (gl_prepaid_other)
     *  9. 7201-01-07 - B. SELISIH KURS                 (gl_selisih_kurs)
     * 10. 7201-01-05 - B. PEMBULATAN                   (gl_pembulatan)
     */
    private function _calculate_and_save_gl_values($id_ros)
    {
        // ── Ambil data header ──
        $header = $this->db->get_where('tr_ros_header', ['id' => $id_ros])->row_array();
        if (!$header) {
            return;
        }

        $no_po       = $header['no_po'];
        $kurs_pib    = (float) $header['kurs_pib'];
        $nilai_dp_rp = (float) $header['nilai_po_pib_rp'];
        $biaya_ls    = (float) $header['biaya_ls'];
        $insurance   = (float) $header['insurance'];

        // ── Ambil data materials ──
        $materials = $this->db->get_where('tr_ros_material', ['id_ros' => $id_ros])->result_array();

        // ── 1. PERSEDIAAN BAHAN BAKU INTRANSIT (1105-01-03) ──
        // SUM total_nilai_inventory dari semua material
        $gl_persediaan_intransit = 0;
        $gl_bm_dibayar_dimuka    = 0;
        $gl_hutang_forwarding    = 0;

        foreach ($materials as $mat) {
            $gl_persediaan_intransit += (int) round((float) $mat['total_nilai_inventory']);
            $gl_bm_dibayar_dimuka    += (int) round((float) $mat['bm_rp']);
            $gl_hutang_forwarding    += (int) round((float) $mat['forwarding_cost']);
        }

        // ── 2. ADVANCE PURCHASE (1104-01-02) ──
        // SUM jumlah_rupiah dari tr_receive_invoice WHERE no_po AND tipe = 'dp'
        $gl_advance_purchase = (float) ($this->db
            ->select_sum('gl_value_dp')
            ->where('no_po', $no_po)
            ->where('tipe', 'dp')
            ->get('tr_receive_invoice')
            ->row()
            ->gl_value_dp ?? 0);

        // ── 3. UNBILL / HUTANG BELUM TERTAGIH (2101-01-06) ──
        // (nilai_po_usd - SUM(tr_top_po.nilai WHERE group_top=76)) × kurs_pib
        $nilai_po_usd = (float) ($header['nilai_po_usd'] ?? 0);

        $sum_top_76 = (float) ($this->db
            ->select_sum('nilai')
            ->where('no_po', $no_po)
            ->where('group_top', 76)
            ->get('tr_top_po')
            ->row()
            ->nilai ?? 0);

        $gl_unbill = (int) round(($nilai_po_usd - $sum_top_76) * $kurs_pib);

        // ── 4. BM DIBAYAR DIMUKA (1108-01-09) ──
        // Sudah dihitung di loop materials di atas

        // ── 5. PREPAID EXPENSE LS (1111-01-01) ──
        $gl_prepaid_ls = (int) round($biaya_ls);

        // ── 6. HUTANG BIAYA FORWARDING (2104-01-14) ──
        // Sudah dihitung di loop materials di atas

        // ── 7. PREPAID EXPENSE INSURANCE (1111-01-02) ──
        $gl_prepaid_insurance = (int) round($insurance);

        // ── 8. PREPAID EXPENSE OTHER (1111-01-03) ──
        $others_sum = $this->db
            ->select_sum('nilai')
            ->where('id_ros', $id_ros)
            ->get('tr_ros_others')
            ->row();
        $gl_prepaid_other = (int) round((float) ($others_sum->nilai ?? 0));

        // ── 9. B. SELISIH KURS (7201-01-07) ──
        // Rumus: (kurs_pib_form - kurs_receive_invoice) × nilai_invoice
        // Data diambil dari tr_receive_invoice WHERE no_po = no_po AND tipe = 'dp'
        $receive_invoice_dp = $this->db
            ->select('kurs, nilai_invoice')
            ->where('no_po', $no_po)
            ->where('tipe', 'dp')
            ->get('tr_receive_invoice')
            ->row();

        $gl_selisih_kurs = 0;
        $gl_advance_purchase_kurs = 0;

        if ($receive_invoice_dp) {
            $kurs_ri       = (float) $receive_invoice_dp->kurs;
            $nilai_invoice = (float) $receive_invoice_dp->nilai_invoice;

            // $gl_selisih_kurs = (int) round(($kurs_pib - $kurs_ri) * $nilai_invoice);
            $gl_selisih_kurs = (int) round(($kurs_ri - $kurs_pib) * $nilai_invoice);
            $gl_advance_purchase_kurs = $nilai_invoice;
        }

        // Hitung unbill kurs
        $gl_unbill_kurs = $nilai_po_usd - $gl_advance_purchase_kurs;

        // ── 10. B. PEMBULATAN (7201-01-05) ──
        // Hitung balance: total debet vs total kredit
        // Kondisi debet/kredit ikut dibalik
        $total_debet_calc = $gl_persediaan_intransit
            + (($gl_selisih_kurs > 0) ? $gl_selisih_kurs : 0);

        $total_kredit_calc = $gl_advance_purchase
            + $gl_unbill
            + $gl_bm_dibayar_dimuka
            + $gl_prepaid_ls
            + $gl_hutang_forwarding
            + $gl_prepaid_insurance
            + $gl_prepaid_other
            + (($gl_selisih_kurs < 0) ? abs($gl_selisih_kurs) : 0);

        // Positif = debet (kredit > debet), Negatif = kredit (debet > kredit)
        $gl_pembulatan = (int) round($total_kredit_calc - $total_debet_calc);

        // ── Update tr_ros_header dengan GL values ──
        // $gl_data = [
        //     'gl_persediaan_intransit'  => $gl_persediaan_intransit,
        //     'gl_advance_purchase'      => $gl_advance_purchase,
        //     'gl_unbill'                => $gl_unbill,
        //     'gl_bm_dibayar_dimuka'     => $gl_bm_dibayar_dimuka,
        //     'gl_prepaid_ls'            => $gl_prepaid_ls,
        //     'gl_hutang_forwarding'     => $gl_hutang_forwarding,
        //     'gl_prepaid_insurance'     => $gl_prepaid_insurance,
        //     'gl_prepaid_other'         => $gl_prepaid_other,
        //     'gl_selisih_kurs'          => $gl_selisih_kurs,
        //     'gl_pembulatan'            => $gl_pembulatan,
        //     'gl_unbill_kurs'           => $gl_unbill_kurs,
        //     'gl_advance_purchase_kurs' => $gl_advance_purchase_kurs,
        // ];

        // echo '<pre>';
        // echo '=== GL VALUES DEBUG ===<br>';
        // echo 'id_ros: ' . $id_ros . '<br>';
        // echo 'no_po: ' . $no_po . '<br>';
        // echo 'kurs_pib: ' . $kurs_pib . '<br>';
        // echo 'nilai_po_usd: ' . $nilai_po_usd . '<br>';
        // echo 'sum_top_76: ' . $sum_top_76 . '<br>';
        // echo 'gl_advance_purchase_kurs (nilai_invoice DP): ' . $gl_advance_purchase_kurs . '<br>';
        // echo 'gl_unbill_kurs: ' . $gl_unbill_kurs . '<br>';
        // echo 'kurs_receive_invoice (DP): ' . ($kurs_ri ?? 'N/A') . '<br>';
        // echo 'total_debet_calc: ' . $total_debet_calc . '<br>';
        // echo 'total_kredit_calc: ' . $total_kredit_calc . '<br>';
        // echo '<br>';
        // var_dump($gl_data);
        // echo '</pre>';
        // die;

        $this->db->update('tr_ros_header', [
            'gl_persediaan_intransit'  => $gl_persediaan_intransit,
            'gl_advance_purchase'      => $gl_advance_purchase,
            'gl_unbill'                => $gl_unbill,
            'gl_bm_dibayar_dimuka'     => $gl_bm_dibayar_dimuka,
            'gl_prepaid_ls'            => $gl_prepaid_ls,
            'gl_hutang_forwarding'     => $gl_hutang_forwarding,
            'gl_prepaid_insurance'     => $gl_prepaid_insurance,
            'gl_prepaid_other'         => $gl_prepaid_other,
            'gl_selisih_kurs'          => $gl_selisih_kurs,
            'gl_pembulatan'            => $gl_pembulatan,
            'gl_unbill_kurs'           => $gl_unbill_kurs,
            'gl_advance_purchase_kurs' => $gl_advance_purchase_kurs,
        ], ['id' => $id_ros]);
    }
    // function _generate_gl_interface dan _generate_nomor_jv_ros dihapus karena sudah tidak terpakai

    // ─── AJAX: Get Coils data for view after upload ──────────────────
    public function get_coils_data()
    {
        $id_ros = $this->input->post('id_ros');

        $this->db->select('c.*, m.nm_barang, m.nm_alias, p.pack_no, p.pack_code');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material', 'left');
        $this->db->join('tr_ros_pack p', 'p.id = c.id_ros_pack', 'left');
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

        // (Perhitungan manual komponen biaya, selisih kurs, dan validasi COA lama dihapus
        //  karena sudah menggunakan gl_* di tr_ros_header dan template JV005)

        // Tentukan tipe PO: Lokal atau Import
        $po_data  = $this->db->get_where('tr_purchase_order', ['no_po' => $header['no_po']])->row();
        $is_lokal = ($po_data && strtolower($po_data->loi) === 'lokal');

        // status_payment: Lokal langsung 'close', Import 'proses_payment' (menunggu pembayaran)
        $status_payment = $is_lokal ? 'close' : 'proses_payment';

        // ── Update status ROS ──
        $this->db->trans_begin();
        $this->db->update('tr_ros_header', [
            'status'          => '1',
            'status_incoming' => 'open',
            'status_payment'  => $status_payment,
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

            if ($is_lokal) {
                // PO Lokal: tidak ada komponen biaya tambahan
                $bm_rp_raw      = 0;
                $prorate_ls_raw = 0;
                $forwarding_raw = 0;
                $insurance_raw  = 0;
            } else {
                $bm_rp_raw      = $total_value_rp_raw * (float)$mat['bm_persen'] / 100;
                $prorate_ls_raw = $biaya_ls * (float)$mat['kg_unit'] / $total_kg_pib;
                $forwarding_raw = (float)$mat['kg_unit'] * $tarif_forwarding;
                $insurance_raw  = $insurance * (float)$mat['kg_unit'] / $total_kg_pib;
            }

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

        // ── Generate Jurnal GL Interface (hanya untuk PO Import) ──
        if ($is_lokal) {
            // PO Lokal: skip generate jurnal GL Interface, langsung close (payment completed)
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 1, 'msg' => 'ROS closed successfully.']);
            exit;
        }

        // ── PO Import: buat kebutuhan pembayaran (tr_ros_payment) ──
        $this->_generate_ros_payment($id_ros, $header, $others);

        if (isset($header['gl_persediaan_intransit']) && $header['gl_persediaan_intransit'] > 0) {
            try {
                $this->load->model('gl_interface/Gl_interface_model');
                $data_source = $header;
                $data_source['tanggal'] = date('Y-m-d');
                
                $mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'ROS', 'action' => 'close_ros'])->row();
                $kode_jurnal = $mapping ? $mapping->kode_master_jurnal : 'JV006'; // fallback
                $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $data_source);
                
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

    // function _generate_jurnal_ros telah dihapus karena digantikan oleh generate_jurnal_dari_template('JV005')

    // ─── Generate kebutuhan pembayaran ROS (PO Import) ───────────────
    /**
     * Membuat baris tr_ros_payment saat close ROS Import.
     * - bm         : total F&C Estimation (cost_bm + kite + bmt + cukai + ppn + ppnbm + pph_import)
     * - ls         : biaya_ls + ppn_ls - pph_ls (Total Biaya LS)
     * - insurance  : insurance
     * - other_cost : 1 baris per item tr_ros_others (nominal + keterangan)
     * Hanya membuat baris untuk nominal > 0.
     */
    private function _generate_ros_payment($id_ros, $header, $others)
    {
        $user_id = $this->auth->user_id();
        $now     = date('Y-m-d H:i:s');

        // Hindari duplikat: hapus payment lama untuk ROS ini yang belum diproses
        $this->db->where('id_ros_header', $id_ros);
        $this->db->where('status', 'belum_diajukan');
        $this->db->delete('tr_ros_payment');

        $rows = [];

        // 1. BM = total F&C Estimation
        $total_bm = (float) $header['cost_bm']
            + (float) $header['cost_bm_kite']
            + (float) $header['cost_bmt']
            + (float) $header['cost_cukai']
            + (float) $header['cost_ppn']
            + (float) $header['cost_ppnbm']
            + (float) $header['cost_pph_import'];
        if ($total_bm > 0) {
            $rows[] = [
                'payment_type' => 'bm',
                'keterangan'   => 'Pembayaran BM',
                'nominal'      => $total_bm,
            ];
        }

        // 2. LS = biaya_ls + ppn_ls - pph_ls
        $total_ls = (float) $header['biaya_ls']
            + (float) $header['ppn_ls']
            - (float) $header['pph_ls'];
        if ($total_ls > 0) {
            $rows[] = [
                'payment_type' => 'ls',
                'keterangan'   => 'Pembayaran LS (Surveyor)',
                'nominal'      => $total_ls,
            ];
        }

        // 3. Insurance
        $total_insurance = (float) $header['insurance'];
        if ($total_insurance > 0) {
            $rows[] = [
                'payment_type' => 'insurance',
                'keterangan'   => 'Pembayaran Insurance',
                'nominal'      => $total_insurance,
            ];
        }

        // 4. Other Cost = 1 baris per item tr_ros_others
        if (!empty($others)) {
            foreach ($others as $ot) {
                $nilai = (float) $ot['nilai'];
                if ($nilai <= 0) continue;
                $rows[] = [
                    'payment_type' => 'other_cost',
                    'keterangan'   => $ot['keterangan'] ?: 'Other Cost',
                    'nominal'      => $nilai,
                ];
            }
        }

        // Insert semua baris payment
        foreach ($rows as $r) {
            $this->db->insert('tr_ros_payment', [
                'id_ros_header' => $id_ros,
                'payment_type'  => $r['payment_type'],
                'keterangan'    => $r['keterangan'],
                'nominal'       => $r['nominal'],
                'status'        => 'belum_diajukan',
                'created_by'    => $user_id,
                'created_on'    => $now,
            ]);
        }
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
                // Skip mother coil yang punya baby (bukan unit fisik) — sama seperti mode edit/finalize
                $is_mother_with_baby = ((int) $coil['is_baby_coil'] === 0 && (int) $coil['qty_roll'] > 1);
                if ($is_mother_with_baby) continue;

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

        // Ambil loi (Lokal/Import) dari PO
        $po_data = $this->db->get_where('tr_purchase_order', ['no_po' => $header['no_po']])->row();
        $loi = $po_data ? $po_data->loi : 'Import';

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
            'loi'              => $loi,
            'total_others_val' => $total_others_val,
            'total_fc'         => $total_fc,
            'total_coil'       => $total_coil,
            'total_nw'         => $total_nw,
            'total_gw'         => $total_gw,
        ]);
    }

    // function _validate_and_get_coa_names dihapus karena sudah tidak terpakai
}
