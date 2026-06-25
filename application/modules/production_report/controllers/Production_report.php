<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_report extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Production_report/Production_report_model');
        $this->load->helper('config_param');
        $this->template->title('Laporan Produksi');
        $this->template->page_icon('fa fa-clipboard-list');
    }

    // =========================================================================
    // Pages
    // =========================================================================

    /**
     * List laporan produksi
     */
    public function index()
    {
        $this->template->render('index');
    }

    /**
     * Form tambah laporan produksi
     *
     * @param string|null $spk_no SPK yang sudah In Process
     */
    public function add($spk_no = null)
    {
        $spk = null;
        if ($spk_no) {
            $spk = $this->db->get_where('tr_spk_production', ['spk_no' => $spk_no])->row();
            if (!$spk || $spk->status !== 'In Process') {
                $this->session->set_flashdata('error', 'SPK tidak ditemukan atau belum berstatus In Process');
                redirect('production_report');
            }
        }

        // Daftar SPK In Process untuk dropdown
        $spk_list = $this->db->where('status', 'In Process')
            ->order_by('spk_no', 'DESC')
            ->get('tr_spk_production')->result();

        $this->template->set(['spk' => $spk, 'spk_list' => $spk_list]);
        $this->template->render('form_report');
    }

    /**
     * Form edit laporan produksi (hanya status Draft)
     *
     * @param string $report_no
     */
    public function edit($report_no)
    {
        $report = $this->Production_report_model->get_report($report_no);
        if (!$report) {
            $this->session->set_flashdata('error', 'Laporan tidak ditemukan');
            redirect('production_report');
        }
        if ($report->status !== 'Draft') {
            $this->session->set_flashdata('error', 'Laporan hanya bisa diedit dalam status Draft');
            redirect('production_report/view/' . $report_no);
        }

        $result   = $this->Production_report_model->get_report_result($report_no);
        $spk_list = $this->db->where('status', 'In Process')
            ->order_by('spk_no', 'DESC')
            ->get('tr_spk_production')->result();

        $this->template->set(['report' => $report, 'result' => $result, 'spk_list' => $spk_list]);
        $this->template->render('form_report');
    }

    /**
     * Detail laporan produksi
     *
     * @param string $report_no
     */
    public function view($report_no)
    {
        $report = $this->Production_report_model->get_report($report_no);
        if (!$report) {
            $this->session->set_flashdata('error', 'Laporan tidak ditemukan');
            redirect('production_report');
        }

        $result = $this->Production_report_model->get_report_result($report_no);

        // Hitung yield
        $yield = [];
        if ($result) {
            $result_arr = (array) $result;
            $yield = $this->Production_report_model->calculate_yield($result_arr, $result->total_berat_coil);
        }

        // Cek deviasi FG
        $deviasi       = null;
        $berat_standar = 0;
        if ($result && $result->fg_qty > 0) {
            $berat_standar = $this->Production_report_model->get_berat_standar_fg($report->produk_fg);
            $deviasi       = $this->Production_report_model->check_deviasi_fg($result->berat_satuan_fg, $berat_standar);
        }

        $this->template->set([
            'report'        => $report,
            'result'        => $result,
            'yield'         => $yield,
            'deviasi'       => $deviasi,
            'berat_standar' => $berat_standar,
        ]);
        $this->template->render('view_report');
    }


    // =========================================================================
    // JSON Endpoints
    // =========================================================================

    /**
     * AJAX: ambil info SPK (produk_fg, berat_standar) untuk form
     *
     * @param string $spk_no
     */
    public function get_spk_info($spk_no)
    {
        $spk = $this->db->get_where('tr_spk_production', ['spk_no' => $spk_no])->row();
        if (!$spk) {
            echo json_encode(['success' => false]);
            return;
        }
        // var_dump($spk);die;

        // Ambil no_coil pertama dari SPK
        $coil = $this->db->where('spk_no', $spk_no)->limit(1)->get('tr_spk_material_detail')->row();
        $no_coil = $coil ? $coil->no_coil : null;

        // Ambil nama supplier dari tr_ros_detail → tr_ros (nm_supplier sudah ada di tr_ros)
        $nm_supplier = '';
        if ($no_coil) {
            $sup = $this->db->select('r.nm_supplier')
                ->from('tr_ros_detail rd')
                ->join('tr_ros r', 'r.id = rd.no_ros', 'left')
                ->where('rd.no_coil', $no_coil)
                ->limit(1)->get()->row();
            $nm_supplier = $sup ? $sup->nm_supplier : '';
        }

        echo json_encode([
            'success'      => true,
            'produk_fg'    => $spk->produk_fg,
            'nm_produk_fg' => isset($spk->nm_produk_fg) ? $spk->nm_produk_fg : '',
            'no_coil'      => $no_coil,
            'nm_supplier'  => $nm_supplier,
        ]);
    }

    /**
     * AJAX: ambil data packing list (berat_kotor, berat_bersih, length) dari tr_ros_detail
     */
    public function get_coil_packing_list()
    {
        $no_coil = $this->input->get('no_coil');
        $row = $this->db->select('net_weight_coil, estimasi_fg')
            ->from('tr_production_plan_detail')
            ->where('no_coil', $no_coil)
            ->limit(1)->get()->row();

            // var_dump($no_coil);die;

        if ($row) {
            echo json_encode([
                'success'      => true,
                // 'berat_kotor'  => $row->berat_kotor,
                'net_weight_coil' => $row->net_weight_coil,
                'estimasi_fg'       => $row->estimasi_fg,
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    /**
     * DataTables server-side endpoint
     */
    public function data_side_report()
    {
        $result = $this->Production_report_model->get_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $status_badge = $this->_status_badge($row->status);
            $btn_view     = '<a href="' . base_url('production_report/view/' . $row->report_no) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
            $btn_edit     = $row->status === 'Draft'
                ? ' <a href="' . base_url('production_report/edit/' . $row->report_no) . '" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>'
                : '';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                $row->report_no,
                $row->created_at,
                $row->spk_no,
                $row->no_coil,
                isset($row->nm_produk_fg) ? $row->nm_produk_fg : '-',
                $status_badge,
                $btn_view . $btn_edit,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    // =========================================================================
    // POST Actions
    // =========================================================================

    /**
     * POST: validasi, hitung kalkulasi, cek deviasi FG, simpan laporan
     */
    public function save_report()
    {
        $spk_no      = $this->input->post('spk_no');
        $no_coil     = $this->input->post('no_coil');
        $id_produk_fg = $this->input->post('id_produk_fg');

        if (empty($spk_no) || empty($no_coil)) {
            $this->session->set_flashdata('error', 'SPK dan No Coil wajib diisi');
            redirect('production_report/add');
        }

        $data = [
            'spk_no'       => $spk_no,
            'no_coil'      => $no_coil,
            'id_produk_fg' => $id_produk_fg,
            'created_by'   => $this->auth->user_id(),
        ];

        $results = [
            'reject_supplier'  => (float) $this->input->post('reject_supplier'),
            'waste_potong'     => (float) $this->input->post('waste_potong'),
            'ng_internal'      => (float) $this->input->post('ng_internal'),
            'ng_supplier'      => (float) $this->input->post('ng_supplier'),
            'plat_bs'          => (float) $this->input->post('plat_bs'),
            'fg_kg'            => (float) $this->input->post('fg_kg'),
            'fg_qty'           => (float) $this->input->post('fg_qty'),
            'kw2_internal_kg'  => (float) $this->input->post('kw2_internal_kg'),
            'kw2_internal_qty' => (float) $this->input->post('kw2_internal_qty'),
            'kw2_supplier_kg'  => (float) $this->input->post('kw2_supplier_kg'),
            'kw2_supplier_qty' => (float) $this->input->post('kw2_supplier_qty'),
            'tong_coil'        => (float) $this->input->post('tong_coil'),
        ];

        $save = $this->Production_report_model->save_report($data, $results);

        if (!$save['success']) {
            $this->session->set_flashdata('error', $save['message']);
            redirect('production_report/add/' . $spk_no);
        }

        // Cek deviasi FG
        if ($save['deviasi'] && $save['deviasi']['is_exception']) {
            $pct = round($save['deviasi']['deviasi_pct'] * 100, 2);
            $this->session->set_flashdata('warning',
                'Laporan disimpan. PERHATIAN: Deviasi berat FG sebesar ' . $pct . '% melebihi toleransi. '
                . 'Diperlukan konfirmasi override sebelum posting.'
            );
        } else {
            $this->session->set_flashdata('success', 'Laporan produksi ' . $save['report_no'] . ' berhasil disimpan');
        }

        redirect('production_report/view/' . $save['report_no']);
    }

    /**
     * POST: submit laporan, return JSON
     *
     * @param string $report_no
     */
    public function process_submit($report_no)
    {
        $user_id = $this->auth->user_id();
        $result  = $this->Production_report_model->submit_report($report_no, $user_id);
        echo json_encode($result);
    }

    /**
     * POST: approve laporan (cek self-approval), return JSON
     *
     * @param string $report_no
     */
    public function process_approve($report_no)
    {
        $approver_id = $this->auth->user_id();
        $result      = $this->Production_report_model->approve_report($report_no, $approver_id);
        echo json_encode($result);
    }

    /**
     * POST: reject laporan + alasan, return JSON
     *
     * @param string $report_no
     */
    public function process_reject($report_no)
    {
        $approver_id = $this->auth->user_id();
        $alasan      = $this->input->post('alasan');

        if (empty($alasan)) {
            echo json_encode(['success' => false, 'message' => 'Alasan reject wajib diisi']);
            return;
        }

        $result = $this->Production_report_model->reject_report($report_no, $approver_id, $alasan);
        echo json_encode($result);
    }

    /**
     * POST: posting laporan ke FG, return JSON
     *
     * @param string $report_no
     */
    public function process_post($report_no)
    {
        $user_id = $this->auth->user_id();

        // Cek apakah ada deviasi FG yang belum di-override
        $report = $this->Production_report_model->get_report($report_no);
        $result = $this->Production_report_model->get_report_result($report_no);

        if ($report && $result && $result->fg_qty > 0) {
            $berat_standar = $this->Production_report_model->get_berat_standar_fg($report->produk_fg);
            $deviasi       = $this->Production_report_model->check_deviasi_fg($result->berat_satuan_fg, $berat_standar);

            if ($deviasi['is_exception'] && !$report->override_fg) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Deviasi berat FG melebihi toleransi. Diperlukan konfirmasi override sebelum posting.',
                    'need_override' => true,
                ]);
                return;
            }
        }

        $post_result = $this->Production_report_model->post_report($report_no, $user_id);
        echo json_encode($post_result);
    }

    /**
     * POST: konfirmasi override deviasi berat FG, return JSON
     *
     * @param string $report_no
     */
    public function process_override_fg($report_no)
    {
        $user_id = $this->auth->user_id();
        $alasan  = $this->input->post('alasan');

        if (empty($alasan)) {
            echo json_encode(['success' => false, 'message' => 'Alasan override wajib diisi']);
            return;
        }

        $result = $this->Production_report_model->override_fg($report_no, $alasan, $user_id);
        echo json_encode($result);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function _status_badge($status)
    {
        $map = [
            'Draft'        => 'secondary',
            'Submitted'    => 'primary',
            'Approved'     => 'success',
            'Rejected'     => 'danger',
            'Posted to FG' => 'dark',
        ];
        $color = isset($map[$status]) ? $map[$status] : 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }
}
