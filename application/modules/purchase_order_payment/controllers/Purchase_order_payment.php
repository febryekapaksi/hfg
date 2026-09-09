<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Syamsudin
 * @copyright Copyright (c) 2022, Syamsudin
 *
 * This is controller for Purchase Order Payment
 */

class Purchase_order_payment extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Purchase_Order.View';
	protected $addPermission  	= 'Purchase_Order.Add';
	protected $managePermission = 'Purchase_Order.Manage';
	protected $deletePermission = 'Purchase_Order.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'Purchase_order_payment/Pr_model',
			'Purchase_order_payment/Jurnal_model',
		));
		$this->template->title('Receive Invoice');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');

		$this->db->select('a.*, b.nm_lengkap');
		$this->db->from('tr_purchase_order a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->where('a.status', '2');
		$get_list_po = $this->db->get()->result_array();

		$this->template->set('list_po', $get_list_po);
		$this->template->title('Receive Invoice');
		$this->template->render('index');
	}

	public function check_menus()
	{
		echo "<pre>";
		print_r($this->db->query("SHOW COLUMNS FROM payment_approve_details")->result());
		echo "================\n";
		print_r($this->db->query("SHOW COLUMNS FROM payment_approve")->result());
		exit;
	}

	public function checkbx()
	{
		$post = $this->input->post();
		$tipe = $post['checkbx'] ?? 'dp';

		$get_supplier = $this->db->get('new_supplier')->result();

		if ($tipe == 'dp') {
			$this->db->select('
				a.no_po, a.no_surat, a.id_suplier, a.tanggal, a.loi, a.status,
				c.nama as nm_supplier,
				e.id as id_top, e.progress, e.nilai, e.keterangan as keterangan_top,
				rid.id as id_receive_dp, rid.status as status_receive_dp, rid.nomor_invoice,
				rp.id as id_request_payment, rp.status as status_request,
				pa.no_doc as no_payment, rid.status as status_payment
			');
			$this->db->from('tr_purchase_order a');
			$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
			$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
			$this->db->join('tr_receive_invoice rid', "rid.id_top = e.id AND rid.tipe = 'dp'", 'left');
			$this->db->join('request_payment rp', "rp.no_doc = rid.id AND rp.tipe = 'invoice_dp'", 'left');
			$this->db->join('payment_approve pa', "pa.id_payment = rid.no_po AND pa.tipe = 'invoice_dp'", 'left');
			$this->db->where('e.group_top', 76);
			$this->db->where('a.status', '2');
			$this->db->group_by('e.id');
			$this->db->order_by('a.created_on', 'desc');
			$list_po = $this->db->get()->result_array();

			$this->template->set('list_po', $list_po);
			$this->template->set('list_supplier', $get_supplier);
			$this->template->render('list_dp');
		} elseif ($tipe == 'import') {
			$this->db->select('
				a.no_po, a.no_surat, a.id_suplier, a.tanggal, a.loi,
				c.nama as nm_supplier,
				e.id as id_top, e.progress, e.nilai, e.keterangan as keterangan_top,
				rh.id as no_ros, rh.nilai_po_usd, rh.kurs_pib,
				ril.id as id_receive_il, ril.nomor_invoice, ril.status as status_receive_il,
				(SELECT COALESCE(SUM(dp.jumlah_rupiah), 0) FROM tr_receive_invoice dp WHERE dp.no_po = a.no_po AND dp.tipe = \'dp\') as total_dp_rupiah,
				rp.id as id_request_payment, rp.status as status_request,
				pa.no_doc as no_payment
			');
			$this->db->from('tr_ros_header rh');
			$this->db->join('tr_purchase_order a', 'a.no_po = rh.no_po');
			$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
			$this->db->join('tr_top_po e', 'e.no_po = a.no_po AND e.group_top = 101', 'left');
			$this->db->join('tr_receive_invoice ril', "ril.id_ros = rh.id AND ril.tipe = 'import'", 'left');
			$this->db->join('request_payment rp', "rp.no_doc = a.no_po AND rp.tipe = 'invoice_import'", 'left');
			$this->db->join('payment_approve pa', "pa.id_payment = ril.no_po AND pa.tipe = 'invoice_import'", 'left');
			$this->db->where('a.loi', 'Import');
			$this->db->where('rh.status', 1);
			// Sembunyikan PO yang DP-nya 100% DAN sudah lunas (tidak ada sisa tagihan)
			$this->db->where("NOT EXISTS (
				SELECT 1 FROM tr_top_po dptop
				JOIN tr_receive_invoice dpri ON dpri.id_top = dptop.id AND dpri.tipe = 'dp' AND dpri.status = 'payment'
				WHERE dptop.no_po = a.no_po AND dptop.group_top = 76 AND dptop.progress >= 100
			)", null, false);
			$this->db->group_by('rh.id');
			$this->db->order_by('rh.created_on', 'desc');
			$list_po = $this->db->get()->result_array();

			$this->template->set('list_po', $list_po);
			$this->template->set('list_supplier', $get_supplier);
			$this->template->render('list_import');
		} elseif ($tipe == 'local') {
			$this->db->select('
				a.no_po, a.no_surat, a.id_suplier, a.loi,
				c.nama as nm_supplier,
				e.id as id_top, e.progress, e.nilai, e.keterangan as keterangan_top,
				ih.id as id_incoming, ih.kode_trans, ih.no_ros, ih.tanggal, ih.gl_unbill_from_ros,
				ril.id as id_receive_il, ril.nomor_invoice, ril.status as status_receive_il,
				(SELECT COALESCE(SUM(dp.jumlah_rupiah), 0) FROM tr_receive_invoice dp WHERE dp.no_po = a.no_po AND dp.tipe = \'dp\') as total_dp_rupiah,
				rp.id as id_request_payment, rp.status as status_request,
				pa.no_doc as no_payment
			');
			$this->db->from('tr_incoming_header ih');
			$this->db->join('tr_purchase_order a', 'a.no_po = ih.no_po');
			$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
			$this->db->join('tr_top_po e', 'e.no_po = a.no_po AND e.group_top = 101', 'left');
			$this->db->join('tr_receive_invoice ril', "ril.id_incoming = ih.kode_trans AND ril.tipe = 'local'", 'left');
			$this->db->join('request_payment rp', "rp.no_doc = ril.id AND rp.tipe = 'invoice_local'", 'left');
			$this->db->join('payment_approve pa', "pa.id_payment = ril.no_po AND pa.tipe = 'invoice_local'", 'left');
			$this->db->where('a.loi', 'Lokal');
			$this->db->where('ih.status', 'finalized');
			// Sembunyikan PO yang DP-nya 100% DAN sudah lunas (tidak ada sisa tagihan)
			$this->db->where("NOT EXISTS (
				SELECT 1 FROM tr_top_po dptop
				JOIN tr_receive_invoice dpri ON dpri.id_top = dptop.id AND dpri.tipe = 'dp' AND dpri.status = 'payment'
				WHERE dptop.no_po = a.no_po AND dptop.group_top = 76 AND dptop.progress >= 100
			)", null, false);
			$this->db->group_by('ih.id');
			$this->db->order_by('ih.created_at', 'desc');
			$list_po = $this->db->get()->result_array();

			$this->template->set('list_po', $list_po);
			$this->template->set('list_supplier', $get_supplier);
			$this->template->render('list_local');
		}
	}

	public function form_dp()
	{
		$id_top = $this->input->post('id_top');
		$no_po  = $this->input->post('no_po');

		if (empty($id_top) || empty($no_po)) {
			echo "<div class='alert alert-warning'>Data tidak valid.</div>";
			return;
		}

		// Ambil data PO
		$data_po = $this->db->select('
            a.*,
            e.id as id_top,
            e.group_top,
            e.progress,
            e.nilai,
            e.keterangan as keterangan_top
        ')
			->from('tr_purchase_order a')
			->join('tr_top_po e', 'e.no_po = a.no_po')
			->where('a.no_po', $no_po)
			->where('e.id', $id_top)
			->get()
			->row_array();

		if (empty($data_po)) {
			echo "<div class='alert alert-warning'>Data PO tidak ditemukan.</div>";
			return;
		}

		// Ambil supplier
		$get_supplier = $this->db->get_where('new_supplier', [
			'kode_supplier' => $data_po['id_suplier']
		])->row_array();

		// DPP = value_dp (tr_top_po.nilai) × 11/12
		$dpp = (float)($data_po['nilai'] ?? 0) * (11 / 12);

		// Nilai PPN = DPP × 12% (konsisten dengan tab local)
		$nilai_ppn = $dpp * 0.12;

		// Jumlah PO murni dari database (hargatotal)
		$jumlah_po = (float)($data_po['hargatotal'] ?? 0);

		// Total DP existing untuk PO ini — sudah dalam IDR (jumlah_rupiah sudah dikali kurs saat save)
		$total_dp_existing = (float)($this->db
			->select_sum('jumlah_rupiah')
			->where('no_po', $no_po)
			->where('tipe', 'dp')
			->get('tr_receive_invoice')
			->row()
			->jumlah_rupiah ?? 0);

		$this->template->set('mode',               'form');
		$this->template->set('data_po',            $data_po);
		$this->template->set('get_supplier',       $get_supplier);
		$this->template->set('dpp',                $dpp);
		$this->template->set('nilai_ppn',          $nilai_ppn);
		$this->template->set('jumlah_po',          $jumlah_po);         // foreign currency, JS yang kali kurs
		$this->template->render('form_dp');
	}

	public function form_il()
	{
		$id_top      = $this->input->post('id_top');
		$no_po       = $this->input->post('no_po');
		$tipe        = $this->input->post('tipe');        // 'import', 'local', atau 'dp'
		$id_dp       = $this->input->post('id_dp');       // nullable
		$id_ros      = $this->input->post('id_ros');      // ID ROS untuk import
		$id_incoming = $this->input->post('id_incoming'); // kode_trans incoming untuk local

		if (empty($no_po) || empty($tipe)) {
			echo "<div class='alert alert-warning'>Data tidak valid.</div>";
			return;
		}

		// Ambil data PO
		$data_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row_array();
		if (empty($data_po)) {
			echo "<div class='alert alert-warning'>Data PO tidak ditemukan.</div>";
			return;
		}

		// Ambil TOP jika ada
		$data_top = null;
		if (!empty($id_top)) {
			$data_top = $this->db->get_where('tr_top_po', ['id' => $id_top])->row_array();
		}

		// Ambil supplier
		$get_supplier = $this->db->get_where('new_supplier', [
			'kode_supplier' => $data_po['id_suplier']
		])->row_array();

		$data_ros = null;
		if ($tipe === 'import' && !empty($id_ros)) {
			$data_ros = $this->db->get_where('tr_ros_header', ['id' => $id_ros])->row_array();
		}

		// Data incoming header untuk local
		$data_incoming = null;
		if ($tipe === 'local' && !empty($id_incoming)) {
			$data_incoming = $this->db->get_where('tr_incoming_header', ['kode_trans' => $id_incoming])->row_array();
		}

		// Sisa tagihan: import dari ROS (gl_unbill_kurs), local dari incoming (gl_unbill_from_ros)
		if ($tipe === 'local') {
			$sisa_tagihan = (float)($data_incoming['gl_unbill_from_ros'] ?? 0);
		} else {
			$sisa_tagihan = (float)($data_ros['gl_unbill_kurs'] ?? 0);
		}

		// Persentase DP diambil dari tr_top_po.progress
		$persen_dp = (float)($data_top['progress'] ?? 0);

		// Total DP existing (dalam IDR)
		$total_dp_rupiah = (float)($this->db
			->select_sum('gl_value_dp')
			->where('no_po', $no_po)
			->where('tipe', 'dp')
			->get('tr_receive_invoice')
			->row()
			->gl_value_dp ?? 0);

		// Currency dari PO
		$currency = strtoupper(trim($data_po['matauang'] ?? 'IDR'));

		$this->template->set('mode', 'form');
		$this->template->set('data_po',         $data_po);
		$this->template->set('data_top',        $data_top);
		$this->template->set('data_ros',        $data_ros);
		$this->template->set('get_supplier',    $get_supplier);
		$this->template->set('tipe',            $tipe);
		$this->template->set('id_top',          $id_top);
		$this->template->set('id_dp',           $id_dp);
		$this->template->set('id_ros',          $id_ros);
		$this->template->set('id_incoming',     $id_incoming);
		$this->template->set('sisa_tagihan',    $sisa_tagihan);
		$this->template->set('persen_dp',       $persen_dp);
		$this->template->set('total_dp_rupiah', $total_dp_rupiah);
		$this->template->set('currency',        $currency);
		$this->template->render('form_il');
	}

	public function save_dp()
	{
		// Validasi field wajib
		$required = ['id_top', 'no_po', 'no_surat', 'nomor_invoice', 'invoice_date', 'bank', 'no_bank', 'nm_acc_bank'];
		foreach ($required as $field) {
			if (empty($this->input->post($field))) {
				echo json_encode(['status' => 0, 'message' => 'Field ' . $field . ' wajib diisi.']);
				return;
			}
		}

		$id_top   = $this->input->post('id_top');
		$no_po    = $this->input->post('no_po');
		$no_surat = $this->input->post('no_surat');

		// Cek duplikat — 1 id_top hanya boleh 1 record
		$cek = $this->db->get_where('tr_receive_invoice', ['id_top' => $id_top, 'tipe' => 'dp'])->row();
		if ($cek) {
			echo json_encode(['status' => 0, 'message' => 'Invoice DP untuk PO ini sudah pernah dibuat.']);
			return;
		}

		// Validasi kurs
		$currency = $this->input->post('currency');
		$kurs_raw = str_replace(',', '', $this->input->post('kurs') ?? '0');
		$kurs     = strtoupper($currency) === 'IDR' ? 1 : (float)$kurs_raw;

		if (strtoupper($currency) !== 'IDR' && $kurs <= 0) {
			echo json_encode(['status' => 0, 'message' => 'Kurs wajib diisi dan harus lebih dari 0.']);
			return;
		}

		// Handle upload file
		$file_invoice = null;
		if (!empty($_FILES['upload_invoice']['name'])) {
			$upload_path = FCPATH . 'uploads/invoice_dp/';
			if (!is_dir($upload_path)) mkdir($upload_path, 0755, true);

			$config_upload = [
				'upload_path'   => FCPATH . 'uploads/invoice_dp/',
				'allowed_types' => '*',
				'max_size'      => 5120,
				'file_name'     => 'inv_dp_' . $id_top . '_' . time()
			];

			$this->load->library('upload', $config_upload);
			$this->upload->initialize($config_upload);

			if ($this->upload->do_upload('upload_invoice')) {
				$file_invoice = $this->upload->data('file_name');
			} else {
				echo json_encode(['status' => 0, 'message' => 'Gagal upload file: ' . $this->upload->display_errors('', '')]);
				return;
			}
		}

		// Helper bersihkan format angka dari autoNumeric
		$clean = function ($val) {
			return (float)str_replace(',', '', $val ?? '0');
		};

		// Recompute PPN server-side (anti-manipulasi), konsisten dengan tab local:
		// DPP = value_dp × 11/12 ; nilai_ppn = DPP × 12%
		$value_dp      = $clean($this->input->post('value_dp'));
		$dpp_dp        = $value_dp * 11 / 12;
		$nilai_ppn_dp  = $dpp_dp * 0.12;
		// jumlah_rupiah = (value_dp + nilai_ppn) × kurs
		$jumlah_rupiah = ($value_dp + $nilai_ppn_dp) * $kurs;

		// Value DP dalam IDR (value_dp × kurs, tanpa PPN)
		// - value_dp_idr : nilai asli IDR (belum di-round) untuk kebutuhan perhitungan lanjutan
		// - gl_value_dp  : sudah di-round, untuk kebutuhan jurnal
		$value_dp_idr = $value_dp * $kurs;
		$gl_value_dp  = round($value_dp_idr);

		// Ambil data PO untuk recalculate server-side (anti manipulasi)
		$po_row        = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row_array();
		$subtotal_po   = (float)($po_row['subtotal']        ?? 0);
		$disc_po       = (float)($po_row['nilai_disc']       ?? 0);
		$ppn_persen_po = (float)($po_row['total_ppn_persen'] ?? 0);
		$ppn_po        = $ppn_persen_po > 0
			? ($subtotal_po - $disc_po) * $ppn_persen_po / 100
			: (float)($po_row['total_ppn'] ?? 0);

		// Ambil tipe_top (group_top) dari tr_top_po
		$top_row  = $this->db->get_where('tr_top_po', ['id' => $id_top])->row_array();
		$tipe_top = $top_row['group_top'] ?? null;

		$data_insert = [
			'tipe'                 => 'dp',
			'no_po'                => $no_po,
			'no_surat'             => $no_surat,
			'id_top'               => $id_top,
			'tipe_top'             => $tipe_top,
			'nomor_invoice'        => $this->input->post('nomor_invoice'),
			'invoice_date'         => $this->input->post('invoice_date'),
			'invoice_date_real'    => $this->input->post('invoice_date_real') ?: null,
			'nilai_invoice'        => $value_dp,
			'nilai_ppn'            => $nilai_ppn_dp,
			'gl_ppn'               => round($nilai_ppn_dp),
			'currency'             => $currency,
			'kurs'                 => $kurs,
			'jumlah_rupiah'        => $jumlah_rupiah,
			'gl_hutang_dagang'     => round($jumlah_rupiah),
			'value_dp_idr'         => $value_dp_idr,
			'gl_value_dp'          => $gl_value_dp,
			'nomor_faktur_pajak'   => $this->input->post('nomor_faktur_pajak') ?: null,
			'tanggal_faktur_pajak' => $this->input->post('tanggal_faktur_pajak') ?: null,
			'file_invoice'         => $file_invoice,
			'status'               => 'draft',
			'bank'                 => $this->input->post('bank'),
			'no_bank'              => $this->input->post('no_bank'),
			'nm_acc_bank'          => $this->input->post('nm_acc_bank'),
			'created_by'           => $this->auth->user_id(),
			'created_on'           => date('Y-m-d H:i:s'),
		];

		$this->db->insert('tr_receive_invoice', $data_insert);

		if ($this->db->affected_rows() > 0) {
			$id_dp = $this->db->insert_id();
			$data_insert['id'] = $id_dp;

			try {
				$this->load->model('gl_interface/Gl_interface_model');

				// Kode jurnal DP berbeda berdasarkan LOI PO (Import vs Lokal)
				$loi_po = strtolower(trim($po_row['loi'] ?? ''));
				if ($loi_po === 'import') {
					$action_jurnal = 'save_dp_import';
				} else {
					$action_jurnal = 'save_dp_local';
				}

				$mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'Purchase Order Payment', 'action' => $action_jurnal])->row();
				$kode_jurnal = $mapping ? $mapping->kode_master_jurnal : 'JV004'; // fallback
				$this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $data_insert);
			} catch (Exception $e) {
				log_message('error', 'Generate jurnal DP failed: ' . $e->getMessage());
			}

			if (ob_get_length()) ob_clean();
			header('Content-Type: application/json');
			echo json_encode(['status' => 1, 'message' => 'Invoice DP berhasil disimpan.']);
		} else {
			if (ob_get_length()) ob_clean();
			header('Content-Type: application/json');
			echo json_encode(['status' => 0, 'message' => 'Gagal menyimpan data.']);
		}
	}

	private function ceil_away_from_zero($val)
	{
		return $val >= 0 ? ceil($val) : floor($val);
	}

	public function save_import()
	{
		// Validasi field wajib
		$required = ['no_po', 'no_surat', 'nomor_invoice', 'invoice_date', 'bank', 'no_bank', 'nm_acc_bank'];
		foreach ($required as $field) {
			if (empty($this->input->post($field))) {
				echo json_encode(['status' => 0, 'message' => 'Field ' . $field . ' wajib diisi.']);
				return;
			}
		}

		$no_po    = $this->input->post('no_po');
		$no_surat = $this->input->post('no_surat');
		$id_top   = $this->input->post('id_top') ?: null;
		$id_dp    = $this->input->post('id_dp') ?: null;
		$id_ros   = $this->input->post('id_ros') ?: null;

		// Cek duplikat berdasarkan id_ros
		if (!empty($id_ros)) {
			$cek = $this->db->get_where('tr_receive_invoice', [
				'id_ros' => $id_ros,
				'tipe'   => 'import'
			])->row();
			if ($cek) {
				echo json_encode(['status' => 0, 'message' => 'Invoice Import untuk ROS ini sudah pernah dibuat.']);
				return;
			}
		}

		// Validasi kurs
		$currency = $this->input->post('currency');
		$kurs_raw = str_replace(',', '', $this->input->post('kurs') ?? '0');
		$kurs     = ceil((float)$kurs_raw);

		if (strtoupper($currency) !== 'IDR' && $kurs <= 0) {
			echo json_encode(['status' => 0, 'message' => 'Kurs wajib diisi dan harus lebih dari 0.']);
			return;
		}

		// Handle upload file
		$file_invoice = null;
		if (!empty($_FILES['upload_invoice']['name'])) {
			$upload_path = FCPATH . 'uploads/invoice_il/';
			if (!is_dir($upload_path)) mkdir($upload_path, 0755, true);

			$config_upload = [
				'upload_path'   => $upload_path,
				'allowed_types' => 'pdf|jpg|jpeg|png',
				'max_size'      => 5120,
				'file_name'     => 'inv_import_' . $no_po . '_' . time()
			];

			$this->load->library('upload', $config_upload);
			$this->upload->initialize($config_upload);   // ← tambahkan ini

			if ($this->upload->do_upload('upload_invoice')) {
				$file_invoice = $this->upload->data('file_name');
			} else {
				echo json_encode(['status' => 0, 'message' => 'Gagal upload file: ' . $this->upload->display_errors('', '')]);
				return;
			}
		}

		$clean = function ($val) {
			return (float)str_replace(',', '', $val ?? '0');
		};

		$sisa_nilai = $clean($this->input->post('sisa_nilai'));
		$jumlah_rupiah = $sisa_nilai * $kurs;
		$gl_hutang_dagang = round($jumlah_rupiah);

		// Hitung unbill dan selisih kurs
		$nominal_unbill = 0;
		$selisih = 0;

		if (!empty($id_ros)) {
			$ros_header = $this->db->get_where('tr_ros_header', ['id' => $id_ros])->row();
			if ($ros_header) {
				$nominal_unbill = (float) $ros_header->gl_unbill;
				$kurs_pib       = (float) $ros_header->kurs_pib;
				$gl_unbill_kurs = (float) $ros_header->gl_unbill_kurs;
				// $selisih = $this->ceil_away_from_zero(($kurs - $kurs_pib) * $gl_unbill_kurs);
				// $selisih = round(($kurs - $kurs_pib) * $gl_unbill_kurs);
				$selisih = $gl_hutang_dagang - round($nominal_unbill);
			}
		}

		// Ambil tipe_top (group_top) dari tr_top_po
		$top_row  = $this->db->get_where('tr_top_po', ['id' => $id_top])->row_array();
		$tipe_top = $top_row['group_top'] ?? null;

		$data_insert = [
			'no_po'                => $no_po,
			'no_surat'             => $no_surat,
			'id_top'               => $id_top,
			'tipe_top'             => $tipe_top,
			'tipe'                 => 'import',
			'id_ros'               => $id_ros,
			'nomor_invoice'        => $this->input->post('nomor_invoice'),
			'invoice_date'         => $this->input->post('invoice_date'),
			'invoice_date_real'    => $this->input->post('invoice_date_real') ?: null,
			'nilai_invoice'        => $sisa_nilai,
			'nilai_ppn'            => 0,
			'jumlah_rupiah'        => $jumlah_rupiah,
			'value_ros_by_po'      => $nominal_unbill,
			'gl_unbill'            => $nominal_unbill,
			'gl_selisih'           => $selisih,
			'gl_hutang_dagang'     => $gl_hutang_dagang,
			'currency'             => $currency,
			'kurs'                 => $kurs,
			'nomor_faktur_pajak'   => $this->input->post('nomor_faktur_pajak') ?: null,
			'tanggal_faktur_pajak' => $this->input->post('tanggal_faktur_pajak') ?: null,
			'file_invoice'         => $file_invoice,
			'status'               => 'draft',
			'bank'                 => $this->input->post('bank'),
			'no_bank'              => $this->input->post('no_bank'),
			'nm_acc_bank'          => $this->input->post('nm_acc_bank'),
			'created_by'           => $this->auth->user_id(),
			'created_on'           => date('Y-m-d H:i:s'),
		];

		// var_dump([
		// 	'kurs_pib'       => $kurs_pib ?? 0,
		// 	'kurs_form'      => $kurs,
		// 	'gl_unbill_kurs' => $gl_unbill_kurs ?? 0,
		// 	'id_ros'		 => $id_ros,
		// 	'gl_selisih'     => $selisih,
		// 	'gl_unbill'      => $nominal_unbill
		// ]);
		// exit;

		// echo '<pre>';
		// var_dump($data_insert);
		// echo '</pre>';
		// die;

		$this->db->insert('tr_receive_invoice', $data_insert);

		if ($this->db->affected_rows() > 0) {
			$id_receive = $this->db->insert_id();

			try {
				$this->load->model('gl_interface/Gl_interface_model');
				$data_source = $data_insert;
				$data_source['tanggal'] = date('Y-m-d');
				$data_source['id'] = $id_receive; // Set id untuk generate jurnal jika diperlukan
				$mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'Purchase Order Payment', 'action' => 'save_import'])->row();
				$kode_jurnal = $mapping ? $mapping->kode_master_jurnal : 'JV007'; // fallback
				$this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $data_source);
			} catch (Exception $e) {
				log_message('error', 'Generate jurnal invoice import failed: ' . $e->getMessage());
			}

			if (ob_get_length()) ob_clean();
			header('Content-Type: application/json');
			echo json_encode(['status' => 1, 'message' => 'Invoice Import berhasil disimpan.']);
		} else {
			if (ob_get_length()) ob_clean();
			header('Content-Type: application/json');
			echo json_encode(['status' => 0, 'message' => 'Gagal menyimpan data.']);
		}
	}

	// function _generate_jurnal_invoice_import dihapus karena sudah digantikan dengan generate_jurnal_dari_template('JV007')

	public function save_local()
	{
		// Validasi field wajib
		$required = ['no_po', 'no_surat', 'nomor_invoice', 'invoice_date', 'bank', 'no_bank', 'nm_acc_bank'];
		foreach ($required as $field) {
			if (empty($this->input->post($field))) {
				echo json_encode(['status' => 0, 'message' => 'Field ' . $field . ' wajib diisi.']);
				return;
			}
		}

		$id_top   = $this->input->post('id_top') ?: null;
		$no_po    = $this->input->post('no_po');
		$no_surat = $this->input->post('no_surat');
		$id_dp    = $this->input->post('id_dp') ?: null;
		$id_incoming = $this->input->post('id_incoming') ?: null;

		// Cek duplikat per incoming
		if (!empty($id_incoming)) {
			$cek = $this->db->get_where('tr_receive_invoice', [
				'id_incoming' => $id_incoming,
				'tipe'        => 'local'
			])->row();
			if ($cek) {
				echo json_encode(['status' => 0, 'message' => 'Invoice Local untuk Incoming ini sudah pernah dibuat.']);
				return;
			}
		}

		// Validasi kurs
		$currency = $this->input->post('currency');
		$kurs_raw = str_replace(',', '', $this->input->post('kurs') ?? '0');
		$kurs     = (float)$kurs_raw;

		if (strtoupper($currency) !== 'IDR' && $kurs <= 0) {
			echo json_encode(['status' => 0, 'message' => 'Kurs wajib diisi dan harus lebih dari 0.']);
			return;
		}

		// Handle upload file
		$file_invoice = null;
		if (!empty($_FILES['upload_invoice']['name'])) {
			$upload_path = FCPATH . 'uploads/invoice_il/';

			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0777, true);
			}

			$config = [
				'upload_path'   => $upload_path,
				'allowed_types' => 'pdf|jpg|jpeg|png',
				'max_size'      => 5120,
				'file_name'     => 'inv_local_' . $id_top . '_' . time()
			];

			// FIX: selalu load lalu selalu initialize, sama seperti save_import().
			// load->library() no-op kalau sudah pernah di-load (misal via autoload),
			// tapi initialize() memaksa config (termasuk upload_path) ter-set ulang.
			$this->load->library('upload', $config);
			$this->upload->initialize($config);

			if ($this->upload->do_upload('upload_invoice')) {
				$file_invoice = $this->upload->data('file_name');
			} else {
				echo json_encode(['status' => 0, 'message' => 'Gagal upload file: ' . $this->upload->display_errors('', '')]);
				return;
			}
		}

		$clean = function ($val) {
			return (float)str_replace(',', '', $val ?? '0');
		};

		$sisa_nilai = $clean($this->input->post('sisa_nilai'));

		// Local selalu IDR (kurs = 1). Hitung ulang PPn server-side (anti-manipulasi):
		// DPP = sisa tagihan * 11/12 ; PPn = DPP * 12% ; Jumlah Invoice = sisa tagihan + PPn
		$dpp_local     = $sisa_nilai * 11 / 12;
		$nilai_ppn     = $dpp_local * 0.12;
		$jumlah_rupiah = $sisa_nilai + $nilai_ppn;
		$gl_hutang_dagang = round($jumlah_rupiah);

		// Hitung unbill dan selisih kurs dari incoming header (gl_unbill_from_ros)
		$nominal_unbill = 0;
		$selisih = 0;

		if (!empty($id_incoming)) {
			$incoming_header = $this->db->get_where('tr_incoming_header', ['kode_trans' => $id_incoming])->row();
			if ($incoming_header) {
				$nominal_unbill = (float) $incoming_header->gl_unbill_from_ros;
				$selisih = $gl_hutang_dagang - round($nominal_unbill);
			}
		}

		// Ambil tipe_top (group_top) dari tr_top_po
		$top_row  = $this->db->get_where('tr_top_po', ['id' => $id_top])->row_array();
		$tipe_top = $top_row['group_top'] ?? null;

		$data_insert = [
			'no_po'                => $no_po,
			'no_surat'             => $no_surat,
			'id_top'               => $id_top,
			'tipe_top'             => $tipe_top,
			'tipe'                 => 'local',
			'id_incoming'          => $id_incoming,
			'nomor_invoice'        => $this->input->post('nomor_invoice'),
			'invoice_date'         => $this->input->post('invoice_date'),
			'invoice_date_real'    => $this->input->post('invoice_date_real') ?: null,
			'nilai_invoice'        => $sisa_nilai,
			'nilai_ppn'            => $nilai_ppn,
			'jumlah_rupiah'        => $jumlah_rupiah,
			'value_ros_by_po'      => $nominal_unbill,
			'gl_unbill'            => $nominal_unbill,
			'gl_selisih'           => $selisih,
			'gl_hutang_dagang'     => $gl_hutang_dagang,
			'gl_ppn'               => round($nilai_ppn),
			'currency'             => $currency,
			'kurs'                 => $kurs,
			'nomor_faktur_pajak'   => $this->input->post('nomor_faktur_pajak') ?: null,
			'tanggal_faktur_pajak' => $this->input->post('tanggal_faktur_pajak') ?: null,
			'file_invoice'         => $file_invoice,
			'status'               => 'draft',
			'bank'                 => $this->input->post('bank'),
			'no_bank'              => $this->input->post('no_bank'),
			'nm_acc_bank'          => $this->input->post('nm_acc_bank'),
			'created_by'           => $this->auth->user_id(),
			'created_on'           => date('Y-m-d H:i:s'),
		];

		$this->db->insert('tr_receive_invoice', $data_insert);

		if ($this->db->affected_rows() > 0) {
			$id_receive = $this->db->insert_id();

			try {
				$this->load->model('gl_interface/Gl_interface_model');
				$data_source = $data_insert;
				$data_source['tanggal'] = date('Y-m-d');
				$data_source['id'] = $id_receive;
				$mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'Purchase Order Payment', 'action' => 'save_local'])->row();
				$kode_jurnal = $mapping ? $mapping->kode_master_jurnal : 'JV009'; // fallback
				$this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $data_source);
			} catch (Exception $e) {
				log_message('error', 'Generate jurnal invoice local failed: ' . $e->getMessage());
			}

			if (ob_get_length()) ob_clean();
			header('Content-Type: application/json');
			echo json_encode(['status' => 1, 'message' => 'Invoice Local berhasil disimpan.']);
		} else {
			if (ob_get_length()) ob_clean();
			header('Content-Type: application/json');
			echo json_encode(['status' => 0, 'message' => 'Gagal menyimpan data.']);
		}
	}

	// ─────────────────────────────────────────────
	// VIEW DP
	// ─────────────────────────────────────────────
	public function view_dp()
	{
		$id = $this->input->post('id');

		if (empty($id)) {
			echo "<div class='alert alert-warning'>Data tidak valid.</div>";
			return;
		}

		$data = $this->db->select('
            r.*,
            r.nomor_invoice as nomor_invoice,
            r.nilai_invoice as value_dp,
            r.file_invoice as file_invoice,
            r.invoice_date as invoice_date,
            r.invoice_date_real as invoice_date_real,
            r.nilai_ppn as nilai_ppn,
            p.no_po, p.no_surat, p.matauang, p.hargatotal,
            s.nama as nm_supplier,
            e.progress as persen_dp, e.nilai, e.keterangan as keterangan_top,
            pa.no_doc as no_payment, r.status as status_payment, pa.id_payment,
        ')
			->from('tr_receive_invoice r')
			->join('tr_purchase_order p', 'p.no_po = r.no_po', 'left')
			->join('new_supplier s', 's.kode_supplier = p.id_suplier', 'left')
			->join('tr_top_po e', 'e.id = r.id_top', 'left')
			->join('payment_approve pa', 'pa.id_payment = r.no_po', 'left')
			->where('r.id', $id)
			->get()
			->row_array();

		if (empty($data)) {
			echo "<div class='alert alert-warning'>Data tidak ditemukan.</div>";
			return;
		}


		// Hitung DPP = value_dp - nilai_ppn
		$data['dpp'] = (float)($data['value_dp'] ?? 0) - (float)($data['nilai_ppn'] ?? 0);

		$this->template->set('mode', 'view');
		$this->template->set('data', $data);
		$this->template->render('form_dp');
	}

	// ─────────────────────────────────────────────
	// VIEW IL (Import / Local)
	// ─────────────────────────────────────────────
	public function view_il()
	{
		$id = $this->input->post('id');

		if (empty($id)) {
			echo "<div class='alert alert-warning'>Data tidak valid.</div>";
			return;
		}

		$data = $this->db->select('
            r.*,
            r.nomor_invoice as nomor_invoice,
            r.nilai_invoice as nilai_invoice,
            r.file_invoice,
            p.no_po, p.no_surat as no_surat_po, p.matauang, p.hargatotal,
            s.nama as nm_supplier,
            e.progress, e.nilai, e.keterangan as keterangan_top,
            rh.gl_advance_purchase as total_dp_rupiah_val,
            ih.gl_unbill_from_ros as incoming_unbill, ih.no_ros as incoming_no_ros,
            pa.no_doc as no_payment, r.status as status_payment, pa.id_payment
        ')
			->from('tr_receive_invoice r')
			->join('tr_purchase_order p', 'p.no_po = r.no_po', 'left')
			->join('new_supplier s', 's.kode_supplier = p.id_suplier', 'left')
			->join('tr_top_po e', 'e.id = r.id_top', 'left')
			->join('tr_ros_header rh', 'rh.id = r.id_ros', 'left')
			->join('tr_incoming_header ih', 'ih.kode_trans = r.id_incoming', 'left')
			->join('payment_approve pa', "pa.id_payment = r.no_po AND pa.tipe = CONCAT('invoice_', r.tipe)", 'left')
			->where('r.id', $id)
			->get()
			->row_array();

		if (empty($data)) {
			echo "<div class='alert alert-warning'>Data tidak ditemukan.</div>";
			return;
		}

		$this->template->set('mode', 'view');
		$this->template->set('data', $data);
		$this->template->set('tipe', $data['tipe'] ?? 'import');
		$this->template->render('form_il');
	}

	public function search_dp()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('
			a.no_po, a.no_surat, a.id_suplier, a.tanggal, a.loi, a.status,
			c.nama as nm_supplier,
			e.id as id_top, e.progress, e.nilai, e.keterangan as keterangan_top,
			rid.id as id_receive_dp,
			rid.nomor_invoice,
			rp.id as id_request_payment, rp.status as status_request,
			pa.id_payment as no_payment, rid.status as status_payment
		');
		$this->db->from('tr_purchase_order a');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
		$this->db->join('tr_receive_invoice rid', 'rid.id_top = e.id', 'left');
		$this->db->join('request_payment rp', "rp.no_doc = rid.id AND rp.tipe = 'invoice_dp'", 'left');
		$this->db->join('payment_approve pa', 'pa.no_doc = rid.id', 'left');
		$this->db->where('e.group_top', 76);
		$this->db->where('a.status', '2');
		$this->db->where('a.id_suplier', $kode_supplier);
		$this->db->group_by('e.id');
		$this->db->order_by('a.created_on', 'desc');
		$list_po = $this->db->get()->result_array();

		$this->template->set('list_po', $list_po);
		$this->template->render('_partial/table_dp');
	}

	public function search_import()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('
        a.no_po, a.no_surat, a.id_suplier, a.tanggal, a.loi,
        c.nama as nm_supplier,
        e.id as id_top, e.progress, e.nilai, e.keterangan as keterangan_top,
        ril.id as id_receive_il,
        ril.nomor_invoice,
        rid.id as id_dp, rid.value_dp as nilai_dp,
        rp.id as id_request_payment, rp.status as status_request,
        pa.id_payment as no_payment
    ');
		$this->db->from('tr_purchase_order a');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
		$this->db->join('tr_ros_header rh', 'rh.no_po = a.no_po');
		$this->db->join('tr_receive_invoice ril', "ril.id_top = e.id AND ril.tipe = 'import'", 'left');
		$this->db->join('tr_receive_invoice rid', "rid.no_po = a.no_po AND rid.tipe = 'dp'", 'left');
		$this->db->join('request_payment rp', "rp.no_doc = ril.id AND rp.tipe = 'invoice_import'", 'left');
		$this->db->join('payment_approve pa', 'pa.no_doc = ril.id', 'left');
		$this->db->where('a.loi', 'Import');
		$this->db->where('e.group_top', 101);
		$this->db->where('rh.status_incoming', 'closed');
		$this->db->where('a.id_suplier', $kode_supplier);
		// Sembunyikan PO yang DP-nya 100% DAN sudah lunas (tidak ada sisa tagihan)
		$this->db->where("NOT EXISTS (
			SELECT 1 FROM tr_top_po dptop
			JOIN tr_receive_invoice dpri ON dpri.id_top = dptop.id AND dpri.tipe = 'dp' AND dpri.status = 'payment'
			WHERE dptop.no_po = a.no_po AND dptop.group_top = 76 AND dptop.progress >= 100
		)", null, false);
		$this->db->group_by('e.id');
		$this->db->order_by('a.created_on', 'desc');
		$list_po = $this->db->get()->result_array();

		$this->template->set('list_po', $list_po);
		$this->template->render('_partial/table_import');
	}

	public function search_local()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('
        a.no_po, a.no_surat, a.id_suplier, a.loi,
        c.nama as nm_supplier,
        e.id as id_top, e.progress, e.nilai, e.keterangan as keterangan_top,
        ih.id as id_incoming, ih.kode_trans, ih.no_ros, ih.tanggal, ih.gl_unbill_from_ros,
        ril.id as id_receive_il, ril.nomor_invoice, ril.status as status_receive_il,
        (SELECT COALESCE(SUM(dp.jumlah_rupiah), 0) FROM tr_receive_invoice dp WHERE dp.no_po = a.no_po AND dp.tipe = \'dp\') as total_dp_rupiah,
        rp.id as id_request_payment, rp.status as status_request,
        pa.no_doc as no_payment
    ');
		$this->db->from('tr_incoming_header ih');
		$this->db->join('tr_purchase_order a', 'a.no_po = ih.no_po');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po AND e.group_top = 101', 'left');
		$this->db->join('tr_receive_invoice ril', "ril.id_incoming = ih.kode_trans AND ril.tipe = 'local'", 'left');
		$this->db->join('request_payment rp', "rp.no_doc = ril.id AND rp.tipe = 'invoice_local'", 'left');
		$this->db->join('payment_approve pa', "pa.id_payment = ril.no_po AND pa.tipe = 'invoice_local'", 'left');
		$this->db->where('a.loi', 'Lokal');
		$this->db->where('ih.status', 'finalized');
		$this->db->where('a.id_suplier', $kode_supplier);
		// Sembunyikan PO yang DP-nya 100% DAN sudah lunas (tidak ada sisa tagihan)
		$this->db->where("NOT EXISTS (
			SELECT 1 FROM tr_top_po dptop
			JOIN tr_receive_invoice dpri ON dpri.id_top = dptop.id AND dpri.tipe = 'dp' AND dpri.status = 'payment'
			WHERE dptop.no_po = a.no_po AND dptop.group_top = 76 AND dptop.progress >= 100
		)", null, false);
		$this->db->group_by('ih.id');
		$this->db->order_by('ih.created_at', 'desc');
		$list_po = $this->db->get()->result_array();

		$this->template->set('list_po', $list_po);
		$this->template->render('_partial/table_local');
	}


	public function req_app()
	{
		$no_surat = $this->input->post('no_po');
		$id_top = $this->input->post('id_top');
		$tipe = $this->input->post('tipe');


		$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $no_surat])->row_array();
		$get_currency = $this->db->get('mata_uang')->result_array();
		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po['id_suplier']])->row_array();

		$get_total_po = $this->db->select('hargatotal as ttl_po')->get_where('tr_purchase_order', ['no_po' => $get_po['no_po']])->row_array();

		$get_top = $this->db->get_where('tr_top_po', ['id' => $id_top])->row();

		$progress = $get_top->progress;
		$nilai_disc = ($get_po['nilai_disc']);
		$nilai_ppn = (($get_po['total_ppn']) * $progress / 100);

		$this->template->set('data_po', $get_po);
		$this->template->set('list_currency', $get_currency);
		$this->template->set('get_total_po', $get_total_po);
		$this->template->set('get_supplier', $get_supplier);
		$this->template->set('get_top', $get_top);
		$this->template->set('id_top', $id_top);
		$this->template->set('nilai_ppn', $nilai_ppn);
		$this->template->set('nilai_disc', $nilai_disc);
		$this->template->set('progress', $progress);

		if ($tipe == 'dp') {
			$this->template->render('add');
		}
		if ($tipe == 'pro') {
			$this->template->render('add_pro');
		}
		if ($tipe == 'ret') {
			$this->template->render('add_ret');
		}
	}

	public function req_inc_app()
	{
		$no_surat = $this->input->post('no_po');
		$tipe_incoming = $this->input->post('tipe_incoming');


		$get_currency = $this->db->get('mata_uang')->result_array();

		if ($tipe_incoming == 'incoming material') {
			$get_inc = $this->db->get_where('tr_incoming_check', ['kode_trans' => $no_surat])->row_array();
			$get_total_po = $this->db->query('
				SELECT SUM(a.jumlahharga) as ttl_po
				FROM
					dt_trans_po a 
				WHERE
					a.id IN (SELECT aa.id_po_detail FROM tr_incoming_check_detail aa WHERE aa.kode_trans = "' . $no_surat . '")
			')->row_array();

			$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $get_inc['no_ipp']])->row();

			$this->db->select('a.nama as nm_supplier');
			$this->db->from('new_supplier a');
			$this->db->join('tr_purchase_order b', 'b.id_suplier = a.kode_supplier', 'left');
			$this->db->where('b.no_po', $get_inc['no_ipp']);
			$get_supplier = $this->db->get()->row();

			// $get_list_inc = $this->db->get_where('tr_incoming_check_detail', ['kode_trans' => $no_surat])->result_array();

			$this->db->select('a.*, b.hargasatuan, b.qty as qty_po, c.no_surat, (d.qty_ng + d.qty_oke) as qty_incoming');
			$this->db->from('tr_incoming_check_detail a');
			$this->db->join('dt_trans_po b', 'b.id = a.id_po_detail', 'left');
			$this->db->join('tr_purchase_order c', 'c.no_po = b.no_po', 'left');
			$this->db->join('tr_checked_incoming_detail d', 'd.id_detail = a.id', 'left');
			$this->db->where('a.kode_trans', $no_surat);
			// $this->db->group_by('a.id');
			$get_list_inc = $this->db->get()->result_array();

			$no_surat = $get_list_inc[0]['no_surat'];

			$get_invoice = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();

			$total_dp = 0;
			$get_total_dp = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();
			if (!empty($get_total_dp)) {
				$total_dp = $get_total_dp['value_dp'];
			}

			$total_incoming = 0;
			foreach ($get_list_inc as $item) {
				$total_incoming += ($item['qty_incoming'] * $item['hargasatuan']);
			}
		} else {
			$get_inc = $this->db->get_where('warehouse_adjustment', ['kode_trans' => $no_surat])->row_array();
			$get_total_po = $this->db->query('
				SELECT SUM(a.jumlahharga) as ttl_po
				FROM
					dt_trans_po a 
				WHERE
					a.id IN (SELECT aa.no_ipp FROM warehouse_adjustment_detail aa WHERE aa.kode_trans = "' . $no_surat . '")
			')->row_array();

			// $get_list_inc = $this->db->get_where('tr_incoming_check_detail', ['kode_trans' => $no_surat])->result_array();

			$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $get_inc['no_ipp']])->row();

			$this->db->select('a.nama as nm_supplier');
			$this->db->from('new_supplier a');
			$this->db->join('tr_purchase_order b', 'b.id_suplier = a.kode_supplier', 'left');
			$this->db->where('b.no_po', $get_inc['no_ipp']);
			$get_supplier = $this->db->get()->row();

			$get_list_inc = $this->db->query("
				SELECT
					a.*, b.hargasatuan, b.qty as qty_po, c.no_surat, (a.qty_oke + a.qty_rusak) as qty_incoming
				FROM
					warehouse_adjustment_detail a
					LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
					LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
				WHERE
					a.kode_trans = '" . $no_surat . "'
			")->result_array();

			$no_surat = $get_list_inc[0]['no_surat'];

			$get_invoice = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();

			$total_dp = 0;
			$get_total_dp = $this->db->get_where('tr_invoice_po', ['no_po' => $no_surat])->row_array();
			if (!empty($get_total_dp)) {
				$total_dp = $get_total_dp['value_dp'];
			}

			$total_incoming = 0;
			foreach ($get_list_inc as $item) {
				$total_incoming += ($item['qty_incoming'] * $item['hargasatuan']);
			}
		}

		$this->template->set('data_inc', $get_inc);
		$this->template->set('list_currency', $get_currency);
		$this->template->set('get_total_po', $get_total_po);
		$this->template->set('list_inc', $get_list_inc);
		$this->template->set('total_dp', $total_dp);
		$this->template->set('total_incoming', $total_incoming);
		$this->template->set('tipe_incoming', $tipe_incoming);
		$this->template->set('get_supplier', $get_supplier);
		$this->template->set('data_po', $get_po);
		$this->template->render('add_inc');
	}

	public function view()
	{
		$id = $this->input->post('id');
		$tipe = $this->input->post('tipe');

		$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $id])->row_array();
		$id_top = $get_invoice['id_top'];

		$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $get_invoice['no_po']])->row();
		$get_top = $this->db->get_where('tr_top_po', ['id' => $id_top])->row();

		$this->template->set('data_invoice', $get_invoice);
		$this->template->set('nilai_ppn', $get_invoice['nilai_ppn']);
		$this->template->set('nilai_disc', $get_invoice['nilai_disc']);
		$this->template->set('nilai_top', $get_top->nilai);
		if ($tipe == 'dp') {
			$this->template->render('view');
		}
		if ($tipe == 'pro') {
			$this->template->render('view_pro');
		}
		if ($tipe == 'ret') {
			$this->template->render('view_ret');
		}
	}

	public function view_inc()
	{
		$id = $this->input->post('id');

		$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $id])->row_array();
		$id_po = str_replace(', ', ',', $get_invoice['no_incoming']);
		$no_incoming = explode(',', $id_po);

		var_dump($id_po);

		$this->template->set('data_invoice', $get_invoice);
		$this->template->set('no_incoming', $no_incoming);

		$this->template->render('view_inc');
	}

	// public function save_invoice()
	// {
	// 	$post = $this->input->post();

	// 	// Validasi kurs wajib diisi jika currency bukan IDR
	// 	$currency = strtoupper(trim($post['currency'] ?? ''));
	// 	$kurs_raw = (float) str_replace(',', '', $post['kurs'] ?? '0');
	// 	if ($currency !== 'IDR' && $kurs_raw <= 0) {
	// 		echo json_encode(['status' => 0, 'message' => 'Kurs wajib diisi dan harus lebih dari 0 jika currency bukan IDR!']);
	// 		return;
	// 	}

	// 	$config['upload_path'] = './uploads/invoice'; //path folder
	// 	$config['allowed_types'] = '*'; //type yang dapat diakses bisa anda sesuaikan
	// 	$config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
	// 	$config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
	// 	$config['remove_spaces'] = FALSE; // Remove spaces from the file name.

	// 	$this->load->library('upload', $config);
	// 	$this->upload->initialize($config);

	// 	$this->db->trans_begin();

	// 	$link_doc = '';
	// 	if ($this->upload->do_upload('upload_invoice')) {
	// 		$data_upload_po = $this->upload->data();
	// 		$link_doc = 'uploads/invoice/' . $data_upload_po['file_name'];
	// 	}

	// 	$no_po = $post['no_po'];
	// 	$no_po1 = $post['nomor_po'];
	// 	$kurs = str_replace(',', '', $post['kurs']);

	// 	$no_invoice = $this->Pr_model->generate_no_invoice();

	// 	if ($post['tipe_req'] == 'dp') {
	// 		$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $post['nomor_po']])->row();
	// 		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po->id_suplier])->row();

	// 		$insert_invoice = $this->db->insert('tr_invoice_po', [
	// 			'id' => $no_invoice,
	// 			'no_incoming' => $post['no_incoming'],
	// 			'no_po' => $post['no_po'],
	// 			'curr' => $post['currency'],
	// 			'invoice_date' => $post['invoice_date'],
	// 			'value_dp' => str_replace(',', '', $post['value_dp']),
	// 			'invoice_no' => $post['nomor_invoice'],
	// 			'total_pembelian' => str_replace(',', '', $post['total_pembelian']),
	// 			'no_faktur_pajak' => $post['nomor_faktur_pajak'],
	// 			'persen_dp' => $post['persen_dp'],
	// 			'link_doc' => $link_doc,
	// 			'invoice_date_real' => $post['invoice_date_real'],
	// 			'tanggal_faktur_pajak' => $post['tanggal_faktur_pajak'],
	// 			'id_supplier' => $get_supplier->kode_supplier,
	// 			'nm_supplier' => $get_supplier->nama,
	// 			'id_top' => $post['id_top'],
	// 			'bank' => $post['bank'],
	// 			'no_bank' => $post['no_bank'],
	// 			'nm_acc_bank' => $post['nm_acc_bank'],
	// 			'nilai_disc' => str_replace(',', '', $post['nilai_disc']),
	// 			'nilai_ppn' => str_replace(',', '', $post['nilai_ppn']),
	// 			'total_invoice' => str_replace(',', '', $post['nilai_ppn']) + str_replace(',', '', $post['total_pembelian']),
	// 			'kurs' => str_replace(',', '', $post['kurs']),
	// 			'created_by' => $this->auth->user_id(),
	// 			'created_date' => date('Y-m-d H:i:s')
	// 		]);
	// 		if (!$insert_invoice) {
	// 			print_r($this->db->error($insert_invoice));
	// 		}
	// 	} else {
	// 		$arr_id_suplier = [];
	// 		$get_id_suplier = $this->db->query("SELECT a.id_suplier FROM tr_purchase_order a WHERE a.no_surat IN ('" . str_replace(",", "','", $post['nomor_po']) . "') GROUP BY a.id_suplier")->result();
	// 		foreach ($get_id_suplier as $item_id_suplier) {
	// 			$arr_id_suplier[] = $item_id_suplier->id_suplier;
	// 		}

	// 		$arr_nm_supplier = [];
	// 		$get_nm_supplier = $this->db->query("SELECT a.nama FROM new_supplier a WHERE a.kode_supplier IN ('" . str_replace(",", "','", implode(',', $arr_id_suplier)) . "')")->result();
	// 		foreach ($arr_nm_supplier as $item_nm_supplier) {
	// 			$arr_nm_supplier[] = $item_nm_supplier->nama;
	// 		}

	// 		$insert_invoice = $this->db->insert('tr_invoice_po', [
	// 			'id' => $no_invoice,
	// 			'no_incoming' => $post['no_incoming'],
	// 			'no_po' => $post['no_po'],
	// 			'curr' => $post['currency'],
	// 			'invoice_date' => $post['invoice_date'],
	// 			'value_dp' => str_replace(',', '', $post['value_dp']),
	// 			'invoice_no' => $post['nomor_invoice'],
	// 			'total_pembelian' => str_replace(',', '', $post['total_pembelian']),
	// 			'no_faktur_pajak' => $post['nomor_faktur_pajak'],
	// 			'link_doc' => $link_doc,
	// 			'req_payment_po' => str_replace(',', '', $post['req_payment_po']),
	// 			'total_invoice' => str_replace(',', '', $post['total_invoice']),
	// 			'notes' => $post['notes'],
	// 			'invoice_date_real' => $post['invoice_date_real'],
	// 			'tanggal_faktur_pajak' => $post['tanggal_faktur_pajak'],
	// 			'id_supplier' => $post['kode_supplier'],
	// 			'nm_supplier' => $post['nama_supplier'],
	// 			'nilai_ppn' => str_replace(',', '', $post['nilai_ppn']),
	// 			'nilai_disc' => str_replace(',', '', $post['nilai_disc']),
	// 			'bank' => $post['bank'],
	// 			'no_bank' => $post['no_bank'],
	// 			'nm_acc_bank' => $post['nm_acc_bank'],
	// 			'kurs' => str_replace(',', '', $post['kurs']),
	// 			'created_by' => $this->auth->user_id(),
	// 			'created_date' => date('Y-m-d H:i:s')
	// 		]);
	// 		if (!$insert_invoice) {
	// 			print_r($this->db->error($insert_invoice));
	// 		}
	// 	}

	// 	$get_users = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();

	// 	if ($post['tipe_req'] == 'dp') {
	// 		$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $post['nomor_po']])->row();
	// 		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po->id_suplier])->row();

	// 		$get_top = $this->db->get_where('tr_top_po', ['id' => $post['id_top']])->row();
	// 		if ($get_top->group_top == 76) {
	// 			$insert_expense = $this->db->insert('tr_expense', [
	// 				'no_doc' => $no_invoice,
	// 				'tgl_doc' => $post['invoice_date'],
	// 				'nama' => $get_users['nm_lengkap'],
	// 				'approval' => $get_users['nm_lengkap'],
	// 				'status' => 1,
	// 				'created_by' => $get_users['nm_lengkap'],
	// 				'created_on' => date('Y-m-d H:i:s'),
	// 				'approved_by' => $get_users['nm_lengkap'],
	// 				'approved_on' => date('Y-m-d H:i:s'),
	// 				'jumlah' => str_replace(',', '', $post['value_dp']),
	// 				'informasi' => 'Pembayaran DP : ' . $no_po . ' (' . $get_supplier->nama . ')',
	// 				'exp_inv_po' => 1,
	// 				'bank_id' => $post['bank'],
	// 				'accnumber' => $post['no_bank'],
	// 				'accname' => $post['nm_acc_bank'],
	// 				'id_po' => $post['nomor_po']
	// 			]);
	// 			if (!$insert_expense) {
	// 				print_r($this->db->error($insert_expense));
	// 				exit;
	// 			}

	// 			$insert_expense_detail = $this->db->insert('tr_expense_detail', [
	// 				'tanggal' => $post['invoice_date'],
	// 				'no_doc' => $no_invoice,
	// 				'deskripsi' => 'Pembayaran DP : ' . $no_po . ' (' . $get_supplier->nama . ')',
	// 				'qty' => 1,
	// 				'harga' => str_replace(',', '', $post['value_dp']),
	// 				'total_harga' => str_replace(',', '', $post['value_dp']),
	// 				'status' => 0,
	// 				'keterangan' => 'Pembayaran DP : ' . $no_po . ' (' . $get_supplier->nama . ')',
	// 				'expense' => str_replace(',', '', $post['value_dp']),
	// 				'created_by' => $get_users['nm_lengkap'],
	// 				'created_on' => date('Y-m-d H:i:s')
	// 			]);
	// 			if (!$insert_expense_detail) {
	// 				print_r($this->db->error($insert_expense_detail));
	// 				exit;
	// 			}

	// 			if ($post['currency'] == 'IDR') {
	// 				$kurs  = 1;
	// 			} else {
	// 				$kurs  = str_replace(',', '', $post['kurs']);
	// 			}

	// 			$dpp_dp_idr = (str_replace(',', '', $post['total_pembelian']) * $kurs);
	// 			$dpp_dp = (str_replace(',', '', $post['total_pembelian']));

	// 			$update_uang_muka = $this->db->update('tr_purchase_order', ['uang_muka_idr' => $dpp_dp_idr], ['no_surat' => $no_po1]);
	// 			$update_uang_muka1 = $this->db->update('tr_purchase_order', ['uang_muka' => $dpp_dp], ['no_surat' => $no_po1]);
	// 			$update_kurs       = $this->db->update('tr_purchase_order', ['kurs_terima_invoice' => $kurs], ['no_surat' => $no_po1]);
	// 		}
	// 		if ($get_top->group_top == 77) {
	// 			$insert_expense = $this->db->insert('tr_expense', [
	// 				'no_doc' => $no_invoice,
	// 				'tgl_doc' => $post['invoice_date'],
	// 				'nama' => $get_users['nm_lengkap'],
	// 				'approval' => $get_users['nm_lengkap'],
	// 				'status' => 1,
	// 				'created_by' => $get_users['nm_lengkap'],
	// 				'created_on' => date('Y-m-d H:i:s'),
	// 				'approved_by' => $get_users['nm_lengkap'],
	// 				'approved_on' => date('Y-m-d H:i:s'),
	// 				'jumlah' => str_replace(',', '', $post['value_dp']),
	// 				'informasi' => 'Pembayaran Progress : ' . $no_po . ' (' . $get_supplier->nama . ')',
	// 				'exp_inv_po' => 1,
	// 				'bank_id' => $post['bank'],
	// 				'accnumber' => $post['no_bank'],
	// 				'accname' => $post['nm_acc_bank']
	// 			]);
	// 			if (!$insert_expense) {
	// 				print_r($this->db->error($insert_expense));
	// 				exit;
	// 			}

	// 			$insert_expense_detail = $this->db->insert('tr_expense_detail', [
	// 				'tanggal' => $post['invoice_date'],
	// 				'no_doc' => $no_invoice,
	// 				'deskripsi' => 'Pembayaran Progress : ' . $no_po . ' (' . $get_supplier->nama . ')',
	// 				'qty' => 1,
	// 				'harga' => str_replace(',', '', $post['value_dp']),
	// 				'total_harga' => str_replace(',', '', $post['value_dp']),
	// 				'status' => 0,
	// 				'keterangan' => 'Pembayaran Progress : ' . $no_po . ' (' . $get_supplier->nama . ')',
	// 				'expense' => str_replace(',', '', $post['value_dp']),
	// 				'created_by' => $get_users['nm_lengkap'],
	// 				'created_on' => date('Y-m-d H:i:s')
	// 			]);
	// 			if (!$insert_expense_detail) {
	// 				print_r($this->db->error($insert_expense_detail));
	// 				exit;
	// 			}

	// 			if ($post['currency'] == 'IDR') {
	// 				$kurs  = 1;
	// 			} else {
	// 				$kurs  = str_replace(',', '', $post['kurs']);
	// 			}

	// 			$dpp_dp_idr = (str_replace(',', '', $post['total_pembelian']) * $kurs);
	// 			$dpp_dp = (str_replace(',', '', $post['total_pembelian']));

	// 			$update_uang_muka = $this->db->update('tr_purchase_order', ['uang_muka_idr' => $dpp_dp_idr], ['no_surat' => $no_po1]);
	// 			$update_uang_muka1 = $this->db->update('tr_purchase_order', ['uang_muka' => $dpp_dp], ['no_surat' => $no_po1]);
	// 			$update_kurs       = $this->db->update('tr_purchase_order', ['kurs_terima_invoice' => $kurs], ['no_surat' => $no_po1]);
	// 		}
	// 		if ($get_top->group_top == 78) {
	// 			$insert_expense = $this->db->insert('tr_expense', [
	// 				'no_doc' => $no_invoice,
	// 				'tgl_doc' => $post['invoice_date'],
	// 				'nama' => $get_users['nm_lengkap'],
	// 				'approval' => $get_users['nm_lengkap'],
	// 				'status' => 1,
	// 				'created_by' => $get_users['nm_lengkap'],
	// 				'created_on' => date('Y-m-d H:i:s'),
	// 				'approved_by' => $get_users['nm_lengkap'],
	// 				'approved_on' => date('Y-m-d H:i:s'),
	// 				'jumlah' => str_replace(',', '', $post['value_dp']),
	// 				'informasi' => 'Pembayaran Retensi : ' . $no_po1 . ' (' . $get_supplier->nama . ')',
	// 				'exp_inv_po' => 1,
	// 				'bank_id' => $post['bank'],
	// 				'accnumber' => $post['no_bank'],
	// 				'accname' => $post['nm_acc_bank']
	// 			]);
	// 			if (!$insert_expense) {
	// 				print_r($this->db->error($insert_expense));
	// 				exit;
	// 			}

	// 			$insert_expense_detail = $this->db->insert('tr_expense_detail', [
	// 				'tanggal' => $post['invoice_date'],
	// 				'no_doc' => $no_invoice,
	// 				'deskripsi' => 'Pembayaran Retensi : ' . $no_po1 . ' (' . $get_supplier->nama . ')',
	// 				'qty' => 1,
	// 				'harga' => str_replace(',', '', $post['value_dp']),
	// 				'total_harga' => str_replace(',', '', $post['value_dp']),
	// 				'status' => 0,
	// 				'keterangan' => 'Pembayaran Retensi : ' . $no_po1 . ' (' . $get_supplier->nama . ')',
	// 				'expense' => str_replace(',', '', $post['value_dp']),
	// 				'created_by' => $get_users['nm_lengkap'],
	// 				'created_on' => date('Y-m-d H:i:s')
	// 			]);
	// 			if (!$insert_expense) {
	// 				print_r($this->db->error($insert_expense));
	// 				exit;
	// 			}
	// 		}
	// 	} else {
	// 		$arr_id_suplier = [];
	// 		$get_id_suplier = $this->db->query("SELECT a.id_suplier FROM tr_purchase_order a WHERE a.no_surat IN ('" . str_replace(",", "','", $post['no_po']) . "') GROUP BY a.id_suplier")->result();
	// 		foreach ($get_id_suplier as $item_id_suplier) {
	// 			$arr_id_suplier[] = $item_id_suplier->id_suplier;
	// 		}

	// 		// print_r(str_replace(",", "','", $post['nomor_po']));
	// 		// exit;

	// 		$arr_nm_supplier = [];
	// 		if (!empty($arr_id_suplier)) {
	// 			$get_nm_supplier = $this->db->select('nama')->from('new_supplier')->where_in('kode_supplier', $arr_id_suplier)->get()->result();
	// 			foreach ($get_nm_supplier as $item_nm_supplier) {
	// 				$arr_nm_supplier[] = $item_nm_supplier->nama;
	// 			}
	// 		}

	// 		$check_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $no_po])->result();
	// 		if (count($check_po) < 1) {
	// 			$update_kurs       = $this->db->update('rutin_non_planning_header', ['kurs_terima_invoice_progress' => $kurs], ['no_pr' => $no_po]);
	// 		} else {
	// 			$update_kurs       = $this->db->update('tr_purchase_order', ['kurs_terima_invoice_progress' => $kurs], ['no_surat' => $no_po]);
	// 		}


	// 		$insert_expense = $this->db->insert('tr_expense', [
	// 			'no_doc' => $no_invoice,
	// 			'tgl_doc' => $post['invoice_date'],
	// 			'nama' => $get_users['nm_lengkap'],
	// 			'approval' => $get_users['nm_lengkap'],
	// 			'status' => 1,
	// 			'created_by' => $get_users['nm_lengkap'],
	// 			'created_on' => date('Y-m-d H:i:s'),
	// 			'approved_by' => $get_users['nm_lengkap'],
	// 			'approved_on' => date('Y-m-d H:i:s'),
	// 			'jumlah' => str_replace(',', '', $post['req_payment_po']),
	// 			'informasi' => 'Pembayaran PO : ' . $no_po . ' (' . implode(', ', $arr_nm_supplier) . ')',
	// 			'bank_id' => $post['bank'],
	// 			'accnumber' => $post['no_bank'],
	// 			'accname' => $post['nm_acc_bank'],
	// 			'id_po' => $post['no_po'],
	// 			'exp_inv_po' => 1
	// 		]);
	// 		if (!$insert_expense) {
	// 			print_r($this->db->error($insert_expense));
	// 			exit;
	// 		}

	// 		$insert_expense_detail = $this->db->insert('tr_expense_detail', [
	// 			'tanggal' => $post['invoice_date'],
	// 			'no_doc' => $no_invoice,
	// 			'deskripsi' => 'Pembayaran PO : ' . $no_po . ' (' . implode(', ', $arr_nm_supplier) . ')',
	// 			'qty' => 1,
	// 			'harga' => str_replace(',', '', $post['req_payment_po']),
	// 			'total_harga' => str_replace(',', '', $post['req_payment_po']),
	// 			'status' => 0,
	// 			'keterangan' => 'Pembayaran PO : ' . $no_po . ' (' . implode(', ', $arr_nm_supplier) . ')',
	// 			'expense' => str_replace(',', '', $post['req_payment_po']),
	// 			'created_by' => $get_users['nm_lengkap'],
	// 			'created_on' => date('Y-m-d H:i:s')
	// 		]);
	// 		if (!$insert_expense_detail) {
	// 			print_r($this->db->error($insert_expense_detail));
	// 			exit;
	// 		}
	// 	}

	// 	if ($post['tipe_req'] == 'dp') {
	// 		$update_po = $this->db->update('tr_purchase_order', ['po_inv_create' => 1], ['no_surat' => $post['nomor_po']]);
	// 		if (!$update_po) {
	// 			print_r($this->db->error($update_po));
	// 			exit;
	// 		}
	// 	} else {
	// 		$clean_no_po = str_replace(', ', ',', $post['nomor_po']);
	// 		// if ($post['tipe_incoming'] == 'incoming material') {
	// 		// 	$this->db->update('tr_incoming_check', ['inc_inv_create' => 1], ['kode_trans' => $post['nomor_po']]);
	// 		// } else {
	// 		// 	$this->db->update('warehouse_adjustment', ['inc_inv_create' => 1], ['kode_trans' => $post['nomor_po']]);
	// 		// }
	// 		$update_incoming = $this->db->where_in('kode_trans', explode(',', $clean_no_po));
	// 		$update_incoming = $this->db->update('tr_incoming_check', ['inc_inv_create' => 1]);
	// 		if (!$update_incoming) {
	// 			print_r($this->db->error($update_incoming));
	// 			exit;
	// 		}

	// 		$update_warehouse = $this->db->where_in('kode_trans', explode(',', $clean_no_po));
	// 		$update_warehouse = $this->db->update('warehouse_adjustment', ['inc_inv_create' => 1]);
	// 		if (!$update_warehouse) {
	// 			print_r($this->db->error($update_warehouse));
	// 			exit;
	// 		}

	// 		$update_invoice = $this->db->where_in('kode_trans', explode(',', $clean_no_po));
	// 		$update_invoice = $this->db->delete('tr_check_invoice');
	// 		if (!$update_invoice) {
	// 			print_r($this->db->error($update_invoice));
	// 			exit;
	// 		}
	// 	}

	// 	//tambahan syam 16/07/2024

	// 	$totalunbill = 0;
	// 	$totalap = 0;
	// 	$coaunbill = '';
	// 	$coaap = '';


	// 	if ($post['tipe_req'] == 'dp') {
	// 		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po->id_suplier])->row();
	// 		if ($post['currency'] == 'IDR') {
	// 			$kurs  = 1;
	// 			$jenis_jurnal = 'JV083';
	// 		} else {
	// 			$kurs  = str_replace(',', '', $post['kurs']);
	// 			$jenis_jurnal = 'JV084';
	// 		}

	// 		$nilai_invoice = str_replace(',', '', $post['total_pembelian']) * $kurs;
	// 		$nilai_ppn = str_replace(',', '', $post['nilai_ppn']) * $kurs;
	// 		$kode_supplier = $get_supplier->kode_supplier;
	// 		$nama = $get_supplier->nama;
	// 	} else {

	// 		if ($post['currency'] == 'IDR') {
	// 			$kurs  = 1;
	// 			$jenis_jurnal = 'JV003';
	// 		} else {
	// 			$kurs  = str_replace(',', '', $post['kurs']);
	// 			$jenis_jurnal = 'JV006';
	// 		}

	// 		$nilai_invoice = str_replace(',', '', $post['total_invoice']) * $kurs;
	// 		$nilai_ppn = str_replace(',', '', $post['nilai_ppn']) * $kurs;
	// 		$kode_supplier = implode(', ', $arr_id_suplier);
	// 		$nama = implode(', ', $arr_nm_supplier);
	// 	}

	// 	// print_r($jenis_jurnal);
	// 	// exit;

	// 	$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' order by parameter_no")->result();

	// 	// Untuk DP: cari berdasarkan no_surat (nomor_po), untuk inc: cari berdasarkan no_po
	// 	if ($post['tipe_req'] == 'dp') {
	// 		$data_po = $this->db->query("select * from tr_purchase_order WHERE no_surat='" . $this->db->escape_str($post['nomor_po']) . "'")->row();
	// 	} else {
	// 		$data_po = $this->db->query("select * from tr_purchase_order WHERE no_surat='$no_po'")->row();
	// 	}

	// 	if (empty($data_po)) {
	// 		$this->db->trans_rollback();
	// 		echo json_encode(['status' => 0, 'message' => 'Data PO tidak ditemukan']);
	// 		return;
	// 	}

	// 	// print_r($data_po);
	// 	// exit;

	// 	$unbill      = $data_po->hutang_idr;
	// 	$kurs_unbill = $data_po->kurs_terima_barang;
	// 	$kurs_um     = $data_po->kurs_terima_invoice;
	// 	$um          = $data_po->uang_muka;
	// 	$umidr       = $data_po->uang_muka_idr;
	// 	if ($data_po->matauang == 'IDR') {
	// 		$kurs_unbill = 1;
	// 		$kurs_um = 1;
	// 	}

	// 	$selisih_um  = (($nilai_invoice) - ($unbill - $umidr));

	// 	if ($selisih_um < 0) {
	// 		$selisihdebet  = 0;
	// 		$selisihkredit = $selisih_um * (-1);
	// 	} elseif ($selisih_um > 0) {
	// 		$selisihdebet  = $selisih_um;
	// 		$selisihkredit = 0;
	// 	}

	// 	$hutangimport = $nilai_invoice;

	// 	$nomor_jurnal = $jenis_jurnal . $no_po . rand(100, 999);
	// 	$payment_date = $post['invoice_date']; //date("Y-m-d");
	// 	$det_Jurnaltes1 = array();
	// 	//			$total=($data->nilai_terima_barang_kurs);
	// 	if ($post['tipe_req'] == 'dp') {
	// 		if ($nilai_invoice > 0) {
	// 			foreach ($datajurnal1 as $rec) {
	// 				if ($rec->parameter_no == "1") {

	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => $nilai_invoice,
	// 						'kredit' => 0,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 					$totalunbill = $nilai_invoice;
	// 					$coaunbill = $rec->no_perkiraan;
	// 				}
	// 				if ($rec->parameter_no == "3") {
	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => 0,
	// 						'kredit' => $nilai_invoice + $nilai_ppn,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 					$totalap = $nilai_invoice + $nilai_ppn;
	// 					$coaap = $rec->no_perkiraan;
	// 				}
	// 				if ($rec->parameter_no == "2") {
	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => $nilai_ppn,
	// 						'kredit' => 0,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 				}
	// 			}
	// 		}
	// 	} else {
	// 		if ($nilai_invoice > 0) {
	// 			foreach ($datajurnal1 as $rec) {
	// 				if ($rec->parameter_no == "1") {

	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => $unbill,
	// 						'kredit' => 0,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 					$totalunbill = $unbill;
	// 					$coaunbill = $rec->no_perkiraan;
	// 				}
	// 				if ($rec->parameter_no == "2") {
	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => 0,
	// 						'kredit' => $hutangimport + $nilai_ppn,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 					$totalap = $hutangimport;
	// 					$coaap = $rec->no_perkiraan;
	// 				}
	// 				if ($rec->parameter_no == "3") {
	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => $nilai_ppn,
	// 						'kredit' => 0,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 				}
	// 				if ($rec->parameter_no == "4") {
	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => 0,
	// 						'kredit' => $umidr,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 				}
	// 				if ($rec->parameter_no == "5") {
	// 					$det_Jurnaltes1[] = array(
	// 						'nomor' => $nomor_jurnal,
	// 						'tanggal' => $payment_date,
	// 						'tipe' => 'JV',
	// 						'no_perkiraan' => $rec->no_perkiraan,
	// 						'keterangan' => 'PO ' . $post['nomor_po'] . ', FP:' . $post['nomor_faktur_pajak'] . ', Sup:' . $nama,
	// 						'no_reff' => $post['nomor_invoice'],
	// 						'debet' => $selisihdebet,
	// 						'kredit' => $selisihkredit,
	// 						'jenis_jurnal' => $jenis_jurnal,
	// 						'nocust' => $kode_supplier,
	// 						'stspos' => '1'
	// 					);
	// 				}
	// 			}
	// 		}
	// 	}
	// 	$insert_jurnaltras = $this->db->insert_batch('jurnaltras', $det_Jurnaltes1);
	// 	if (!$insert_jurnaltras) {
	// 		print_r($this->db->error($insert_jurnaltras));
	// 		exit;
	// 	}

	// 	//auto jurnal → insert ke gl_interface (staging), posting manual via GL Interface

	// 	$tanggal = $post['invoice_date_real'];
	// 	$Bln	= substr($tanggal, 5, 2);
	// 	$Thn	= substr($tanggal, 0, 4);
	// 	$total	= 0;
	// 	$keterangan = 'Receive Invoice ' . $no_invoice;

	// 	// Insert header ke gl_interface
	// 	$this->db->insert('gl_interface', [
	// 		'nomor'           => null,
	// 		'tgl'             => $tanggal,
	// 		'bulan'           => $Bln,
	// 		'tahun'           => $Thn,
	// 		'kdcab'           => '101',
	// 		'jenis'           => 'JV',
	// 		'keterangan'      => $keterangan,
	// 		'jenis_transaksi' => 'receive invoice',
	// 		'status'          => 'pending',
	// 		'user_id'         => $this->auth->user_id(),
	// 		'memo'            => json_encode([
	// 			'id_supplier'   => $kode_supplier,
	// 			'nama_supplier' => $nama,
	// 			'no_reff'       => $post['nomor_po'],
	// 			'coaunbill'     => $coaunbill,
	// 			'totalunbill'   => $totalunbill,
	// 			'coaap'         => $coaap,
	// 			'totalap'       => $totalap,
	// 		]),
	// 	]);
	// 	$id_gl_interface = $this->db->insert_id();

	// 	// Insert detail ke gl_interface_detail
	// 	foreach ($det_Jurnaltes1 as $vals) {
	// 		$total = ($total + $vals['debet']);
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl_interface,
	// 			'no_batch'        => null,
	// 			'tipe'            => 'JV',
	// 			'tanggal'         => $tanggal,
	// 			'no_perkiraan'    => $vals['no_perkiraan'],
	// 			'keterangan'      => $vals['keterangan'],
	// 			'no_reff'         => $vals['no_reff'],
	// 			'debet'           => $vals['debet'],
	// 			'kredit'          => $vals['kredit'],
	// 			'created_at'      => date('Y-m-d H:i:s'),
	// 		]);
	// 	}

	// 	// Update jml (total debet) di header
	// 	$this->db->update('gl_interface', ['jml' => $total], ['id' => $id_gl_interface]);

	// 	//end auto jurnal



	// 	if ($this->db->trans_status() === false) {
	// 		$this->db->trans_rollback();
	// 		$valid = 0;
	// 	} else {
	// 		$this->db->trans_commit();
	// 		$valid = 1;
	// 	}

	// 	echo json_encode([
	// 		'status' => $valid
	// 	]);
	// }

	public function save_invoice()
	{
		$post = $this->input->post();

		// ================================================================
		// VALIDASI KURS
		// ================================================================
		$currency = strtoupper(trim($post['currency'] ?? ''));
		$kurs_raw = (float) str_replace(',', '', $post['kurs'] ?? '0');
		if ($currency !== 'IDR' && $kurs_raw <= 0) {
			echo json_encode(['status' => 0, 'message' => 'Kurs wajib diisi dan harus lebih dari 0 jika currency bukan IDR!']);
			return;
		}

		// ================================================================
		// UPLOAD FILE
		// ================================================================
		$config['upload_path']   = './uploads/invoice';
		$config['allowed_types'] = '*';
		$config['max_size']      = 100000000;
		$config['encrypt_name']  = TRUE;
		$config['remove_spaces'] = FALSE;

		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		$this->db->trans_begin();

		$link_doc = '';
		if ($this->upload->do_upload('upload_invoice')) {
			$data_upload = $this->upload->data();
			$link_doc    = 'uploads/invoice/' . $data_upload['file_name'];
		}

		// ================================================================
		// VARIABEL UMUM
		// ================================================================
		$no_po      = $post['no_po'];
		$no_po1     = $post['nomor_po'];
		$tipe_req   = $post['tipe_req']; // 'dp' = DP/Progress/Retensi, 'inc' = Incoming
		$no_invoice = $this->Pr_model->generate_no_invoice();
		$get_users  = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();

		$kurs = ($currency === 'IDR') ? 1 : str_replace(',', '', $post['kurs'] ?? '0');

		// ================================================================
		// BAGIAN DP / PROGRESS / RETENSI (tipe_req == 'dp')
		// ================================================================
		if ($tipe_req == 'dp') {

			$get_po       = $this->db->get_where('tr_purchase_order', ['no_surat' => $no_po1])->row();
			$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $get_po->id_suplier])->row();
			$get_top      = $this->db->get_where('tr_top_po', ['id' => $post['id_top']])->row();

			// Tentukan label berdasarkan group_top
			$group_top = $get_top->group_top;
			if ($group_top == 76) {
				$label_tipe = 'DP';
			} elseif ($group_top == 77) {
				$label_tipe = 'Progress';
			} elseif ($group_top == 78) {
				$label_tipe = 'Retensi';
			} else {
				$label_tipe = 'DP';
			}

			// ---- INSERT tr_invoice_po ----
			$insert_invoice = $this->db->insert('tr_invoice_po', [
				'id'                  => $no_invoice,
				'no_incoming'         => $post['no_incoming'] ?? '',
				'no_po'               => $no_po,
				'curr'                => $post['currency'],
				'invoice_date'        => $post['invoice_date'],
				'value_dp'            => str_replace(',', '', $post['value_dp']),
				'invoice_no'          => $post['nomor_invoice'],
				'total_pembelian'     => str_replace(',', '', $post['total_pembelian']),
				'no_faktur_pajak'     => $post['nomor_faktur_pajak'] ?? '',
				'persen_dp'           => $post['persen_dp'],
				'link_doc'            => $link_doc,
				'invoice_date_real'   => $post['invoice_date_real'],
				'tanggal_faktur_pajak' => $post['tanggal_faktur_pajak'] ?? '',
				'id_supplier'         => $get_supplier->kode_supplier,
				'nm_supplier'         => $get_supplier->nama,
				'id_top'              => $post['id_top'],
				'bank'                => $post['bank'] ?? '',
				'no_bank'             => $post['no_bank'] ?? '',
				'nm_acc_bank'         => $post['nm_acc_bank'] ?? '',
				'nilai_disc'          => str_replace(',', '', $post['nilai_disc']),
				'nilai_ppn'           => str_replace(',', '', $post['nilai_ppn']),
				'total_invoice'       => str_replace(',', '', $post['nilai_ppn']) + str_replace(',', '', $post['total_pembelian']),
				'kurs'                => $kurs,
				'created_by'          => $this->auth->user_id(),
				'created_date'        => date('Y-m-d H:i:s'),
			]);
			if (!$insert_invoice) {
				$this->db->trans_rollback();
				echo json_encode(['status' => 0, 'message' => 'Gagal insert invoice: ' . json_encode($this->db->error())]);
				return;
			}

			// ---- INSERT tr_expense & tr_expense_detail ----
			$insert_expense = $this->db->insert('tr_expense', [
				'no_doc'      => $no_invoice,
				'tgl_doc'     => $post['invoice_date'],
				'nama'        => $get_users['nm_lengkap'],
				'approval'    => $get_users['nm_lengkap'],
				'status'      => 1,
				'created_by'  => $get_users['nm_lengkap'],
				'created_on'  => date('Y-m-d H:i:s'),
				'approved_by' => $get_users['nm_lengkap'],
				'approved_on' => date('Y-m-d H:i:s'),
				'jumlah'      => str_replace(',', '', $post['value_dp']),
				'informasi'   => 'Pembayaran ' . $label_tipe . ' : ' . $no_po . ' (' . $get_supplier->nama . ')',
				'exp_inv_po'  => 1,
				'bank_id'     => $post['bank'] ?? '',
				'accnumber'   => $post['no_bank'] ?? '',
				'accname'     => $post['nm_acc_bank'] ?? '',
				'id_po'       => $no_po1,
			]);
			if (!$insert_expense) {
				$this->db->trans_rollback();
				echo json_encode(['status' => 0, 'message' => 'Gagal insert expense']);
				return;
			}

			$insert_expense_detail = $this->db->insert('tr_expense_detail', [
				'tanggal'     => $post['invoice_date'],
				'no_doc'      => $no_invoice,
				'deskripsi'   => 'Pembayaran ' . $label_tipe . ' : ' . $no_po . ' (' . $get_supplier->nama . ')',
				'qty'         => 1,
				'harga'       => str_replace(',', '', $post['value_dp']),
				'total_harga' => str_replace(',', '', $post['value_dp']),
				'status'      => 0,
				'keterangan'  => 'Pembayaran ' . $label_tipe . ' : ' . $no_po . ' (' . $get_supplier->nama . ')',
				'expense'     => str_replace(',', '', $post['value_dp']),
				'created_by'  => $get_users['nm_lengkap'],
				'created_on'  => date('Y-m-d H:i:s'),
			]);
			if (!$insert_expense_detail) {
				$this->db->trans_rollback();
				echo json_encode(['status' => 0, 'message' => 'Gagal insert expense detail']);
				return;
			}

			// ---- UPDATE tr_purchase_order (hanya untuk DP dan Progress) ----
			if (in_array($group_top, [76, 77])) {
				$dpp_idr = str_replace(',', '', $post['total_pembelian']) * $kurs;
				$dpp     = str_replace(',', '', $post['total_pembelian']);
				$this->db->update('tr_purchase_order', [
					'uang_muka_idr'     => $dpp_idr,
					'uang_muka'         => $dpp,
					'kurs_terima_invoice' => $kurs,
				], ['no_surat' => $no_po1]);
			}
			// Retensi (group_top 78) tidak update uang_muka

			// ---- FLAG po_inv_create ----
			$this->db->update('tr_purchase_order', ['po_inv_create' => 1], ['no_surat' => $no_po1]);

			// ---- JURNAL: DP/Pro/Ret pakai JV083/JV084 ----
			$jenis_jurnal  = ($currency === 'IDR') ? 'JV083' : 'JV084';
			$nilai_invoice = str_replace(',', '', $post['total_pembelian']) * $kurs;
			$nilai_ppn     = str_replace(',', '', $post['nilai_ppn']) * $kurs;
			$kode_supplier = $get_supplier->kode_supplier;
			$nama          = $get_supplier->nama;

			// ================================================================
			// BAGIAN INCOMING (tipe_req == 'inc')
			// ================================================================
		} else {

			// ---- Ambil data supplier ----
			$arr_id_suplier = [];
			$get_id_suplier = $this->db->query("
            SELECT a.id_suplier 
            FROM tr_purchase_order a 
            WHERE a.no_surat IN ('" . str_replace(",", "','", $no_po1) . "') 
            GROUP BY a.id_suplier
        	")->result();
			foreach ($get_id_suplier as $item) {
				$arr_id_suplier[] = $item->id_suplier;
			}

			$arr_nm_supplier = [];
			if (!empty($arr_id_suplier)) {
				// FIX BUG: sebelumnya loop ke $arr_nm_supplier yang kosong
				$get_nm_supplier = $this->db->select('nama')
					->from('new_supplier')
					->where_in('kode_supplier', $arr_id_suplier)
					->get()->result();
				foreach ($get_nm_supplier as $item) {
					$arr_nm_supplier[] = $item->nama;
				}
			}

			$nm_supplier_str  = implode(', ', $arr_nm_supplier);
			$kode_supplier_str = implode(', ', $arr_id_suplier);

			// ---- INSERT tr_invoice_po ----
			$insert_invoice = $this->db->insert('tr_invoice_po', [
				'id'                   => $no_invoice,
				'no_incoming'          => $post['no_incoming'] ?? '',
				'no_po'                => $no_po,
				'curr'                 => $post['currency'],
				'invoice_date'         => $post['invoice_date'],
				'value_dp'             => str_replace(',', '', $post['value_dp'] ?? '0'),
				'invoice_no'           => $post['nomor_invoice'],
				'total_pembelian'      => str_replace(',', '', $post['total_pembelian'] ?? '0'),
				'no_faktur_pajak'      => $post['nomor_faktur_pajak'] ?? '',
				'link_doc'             => $link_doc,
				'req_payment_po'       => str_replace(',', '', $post['req_payment_po'] ?? '0'),
				'total_invoice'        => str_replace(',', '', $post['total_invoice'] ?? '0'),
				'notes'                => $post['notes'] ?? '',
				'invoice_date_real'    => $post['invoice_date_real'],
				'tanggal_faktur_pajak' => $post['tanggal_faktur_pajak'] ?? '',
				'id_supplier'          => $post['kode_supplier'] ?? $kode_supplier_str,
				'nm_supplier'          => $post['nama_supplier'] ?? $nm_supplier_str,
				'nilai_ppn'            => str_replace(',', '', $post['nilai_ppn'] ?? '0'),
				'nilai_disc'           => str_replace(',', '', $post['nilai_disc'] ?? '0'),
				'bank'                 => $post['bank'] ?? '',
				'no_bank'              => $post['no_bank'] ?? '',
				'nm_acc_bank'          => $post['nm_acc_bank'] ?? '',
				'kurs'                 => $kurs,
				'created_by'           => $this->auth->user_id(),
				'created_date'         => date('Y-m-d H:i:s'),
			]);
			if (!$insert_invoice) {
				$this->db->trans_rollback();
				echo json_encode(['status' => 0, 'message' => 'Gagal insert invoice incoming']);
				return;
			}

			// ---- UPDATE kurs di PO atau rutin_non_planning ----
			$check_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $no_po])->result();
			if (count($check_po) < 1) {
				$this->db->update('rutin_non_planning_header', ['kurs_terima_invoice_progress' => $kurs], ['no_pr' => $no_po]);
			} else {
				$this->db->update('tr_purchase_order', ['kurs_terima_invoice_progress' => $kurs], ['no_surat' => $no_po]);
			}

			// ---- INSERT tr_expense & tr_expense_detail ----
			$insert_expense = $this->db->insert('tr_expense', [
				'no_doc'      => $no_invoice,
				'tgl_doc'     => $post['invoice_date'],
				'nama'        => $get_users['nm_lengkap'],
				'approval'    => $get_users['nm_lengkap'],
				'status'      => 1,
				'created_by'  => $get_users['nm_lengkap'],
				'created_on'  => date('Y-m-d H:i:s'),
				'approved_by' => $get_users['nm_lengkap'],
				'approved_on' => date('Y-m-d H:i:s'),
				'jumlah'      => str_replace(',', '', $post['req_payment_po'] ?? '0'),
				'informasi'   => 'Pembayaran PO : ' . $no_po . ' (' . $nm_supplier_str . ')',
				'bank_id'     => $post['bank'] ?? '',
				'accnumber'   => $post['no_bank'] ?? '',
				'accname'     => $post['nm_acc_bank'] ?? '',
				'id_po'       => $no_po,
				'exp_inv_po'  => 1,
			]);
			if (!$insert_expense) {
				$this->db->trans_rollback();
				echo json_encode(['status' => 0, 'message' => 'Gagal insert expense incoming']);
				return;
			}

			$insert_expense_detail = $this->db->insert('tr_expense_detail', [
				'tanggal'     => $post['invoice_date'],
				'no_doc'      => $no_invoice,
				'deskripsi'   => 'Pembayaran PO : ' . $no_po . ' (' . $nm_supplier_str . ')',
				'qty'         => 1,
				'harga'       => str_replace(',', '', $post['req_payment_po'] ?? '0'),
				'total_harga' => str_replace(',', '', $post['req_payment_po'] ?? '0'),
				'status'      => 0,
				'keterangan'  => 'Pembayaran PO : ' . $no_po . ' (' . $nm_supplier_str . ')',
				'expense'     => str_replace(',', '', $post['req_payment_po'] ?? '0'),
				'created_by'  => $get_users['nm_lengkap'],
				'created_on'  => date('Y-m-d H:i:s'),
			]);
			if (!$insert_expense_detail) {
				$this->db->trans_rollback();
				echo json_encode(['status' => 0, 'message' => 'Gagal insert expense detail incoming']);
				return;
			}

			// ---- FLAG inc_inv_create & hapus tr_check_invoice ----
			$clean_no_po = str_replace(', ', ',', $post['nomor_po'] ?? '');
			if (!empty($clean_no_po)) {
				$arr_no_po = explode(',', $clean_no_po);

				$this->db->where_in('kode_trans', $arr_no_po);
				$this->db->update('tr_incoming_check', ['inc_inv_create' => 1]);

				$this->db->where_in('kode_trans', $arr_no_po);
				$this->db->update('warehouse_adjustment', ['inc_inv_create' => 1]);

				$this->db->where_in('kode_trans', $arr_no_po);
				$this->db->delete('tr_check_invoice');
			}

			// ---- JURNAL: Incoming pakai JV003/JV006 ----
			$jenis_jurnal  = ($currency === 'IDR') ? 'JV003' : 'JV006';
			$nilai_invoice = str_replace(',', '', $post['total_invoice'] ?? '0') * $kurs;
			$nilai_ppn     = str_replace(',', '', $post['nilai_ppn'] ?? '0') * $kurs;
			$kode_supplier = $kode_supplier_str;
			$nama          = $nm_supplier_str;
		}

		// ================================================================
		// DATA PO UNTUK JURNAL (dipakai kedua tipe)
		// ================================================================
		if ($tipe_req == 'dp') {
			$data_po = $this->db->query("
            SELECT * FROM tr_purchase_order 
            WHERE no_surat = '" . $this->db->escape_str($no_po1) . "'
        ")->row();
		} else {
			$data_po = $this->db->query("
            SELECT * FROM tr_purchase_order 
            WHERE no_surat = '" . $this->db->escape_str($no_po) . "'
        ")->row();
		}

		if (empty($data_po)) {
			$this->db->trans_rollback();
			echo json_encode(['status' => 0, 'message' => 'Data PO tidak ditemukan untuk jurnal']);
			return;
		}

		$unbill      = $data_po->hutang_idr;
		$umidr       = $data_po->uang_muka_idr;
		$kurs_unbill = ($data_po->matauang == 'IDR') ? 1 : $data_po->kurs_terima_barang;

		// FIX BUG: inisialisasi default supaya tidak error jika selisih_um == 0
		$selisihdebet  = 0;
		$selisihkredit = 0;
		$selisih_um    = ($nilai_invoice) - ($unbill - $umidr);
		if ($selisih_um < 0) {
			$selisihkredit = abs($selisih_um);
		} elseif ($selisih_um > 0) {
			$selisihdebet = $selisih_um;
		}

		$hutangimport  = $nilai_invoice;
		$totalunbill   = 0;
		$totalap       = 0;
		$coaunbill     = '';
		$coaap         = '';

		// ================================================================
		// JURNAL OTOMATIS
		// ================================================================
		// $datajurnal1 = $this->db->query("
		// SELECT * FROM " . DBACC . ".master_oto_jurnal_detail 
		// WHERE kode_master_jurnal = '" . $jenis_jurnal . "' 
		// ORDER BY parameter_no
		// ")->result();

		// $nomor_jurnal   = $jenis_jurnal . $no_po . rand(100, 999);
		// $payment_date   = $post['invoice_date'];
		// $det_Jurnaltes1 = [];

		// $no_reff_jurnal = $post['nomor_po'] ?? '';
		// $no_inv_jurnal  = $post['nomor_invoice'] ?? $no_invoice;
		// $fp_jurnal      = $post['nomor_faktur_pajak'] ?? '';

		// if ($tipe_req == 'dp') {
		// 	// Jurnal DP/Progress/Retensi: parameter 1, 2, 3
		// 	if ($nilai_invoice > 0) {
		// 		foreach ($datajurnal1 as $rec) {
		// 			$base = [
		// 				'nomor'        => $nomor_jurnal,
		// 				'tanggal'      => $payment_date,
		// 				'tipe'         => 'JV',
		// 				'no_perkiraan' => $rec->no_perkiraan,
		// 				'keterangan'   => 'PO ' . $no_reff_jurnal . ', FP:' . $fp_jurnal . ', Sup:' . $nama,
		// 				'no_reff'      => $no_inv_jurnal,
		// 				'jenis_jurnal' => $jenis_jurnal,
		// 				'nocust'       => $kode_supplier,
		// 				'stspos'       => '1',
		// 				'debet'        => 0,
		// 				'kredit'       => 0,
		// 			];
		// 			if ($rec->parameter_no == '1') {
		// 				$base['debet']  = $nilai_invoice;
		// 				$totalunbill    = $nilai_invoice;
		// 				$coaunbill      = $rec->no_perkiraan;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 			if ($rec->parameter_no == '2') {
		// 				$base['debet']  = $nilai_ppn;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 			if ($rec->parameter_no == '3') {
		// 				$base['kredit'] = $nilai_invoice + $nilai_ppn;
		// 				$totalap        = $nilai_invoice + $nilai_ppn;
		// 				$coaap          = $rec->no_perkiraan;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 		}
		// 	}
		// } else {
		// 	// Jurnal Incoming: parameter 1, 2, 3, 4, 5
		// 	if ($nilai_invoice > 0) {
		// 		foreach ($datajurnal1 as $rec) {
		// 			$base = [
		// 				'nomor'        => $nomor_jurnal,
		// 				'tanggal'      => $payment_date,
		// 				'tipe'         => 'JV',
		// 				'no_perkiraan' => $rec->no_perkiraan,
		// 				'keterangan'   => 'PO ' . $no_reff_jurnal . ', FP:' . $fp_jurnal . ', Sup:' . $nama,
		// 				'no_reff'      => $no_inv_jurnal,
		// 				'jenis_jurnal' => $jenis_jurnal,
		// 				'nocust'       => $kode_supplier,
		// 				'stspos'       => '1',
		// 				'debet'        => 0,
		// 				'kredit'       => 0,
		// 			];
		// 			if ($rec->parameter_no == '1') {
		// 				$base['debet']  = $unbill;
		// 				$totalunbill    = $unbill;
		// 				$coaunbill      = $rec->no_perkiraan;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 			if ($rec->parameter_no == '2') {
		// 				$base['kredit'] = $hutangimport + $nilai_ppn;
		// 				$totalap        = $hutangimport;
		// 				$coaap          = $rec->no_perkiraan;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 			if ($rec->parameter_no == '3') {
		// 				$base['debet']  = $nilai_ppn;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 			if ($rec->parameter_no == '4') {
		// 				$base['kredit'] = $umidr;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 			if ($rec->parameter_no == '5') {
		// 				$base['debet']  = $selisihdebet;
		// 				$base['kredit'] = $selisihkredit;
		// 				$det_Jurnaltes1[] = $base;
		// 			}
		// 		}
		// 	}
		// }

		// if (!empty($det_Jurnaltes1)) {
		// 	$insert_jurnal = $this->db->insert_batch('jurnaltras', $det_Jurnaltes1);
		// 	if (!$insert_jurnal) {
		// 		$this->db->trans_rollback();
		// 		echo json_encode(['status' => 0, 'message' => 'Gagal insert jurnal']);
		// 		return;
		// 	}
		// }

		// ================================================================
		// GL INTERFACE
		// ================================================================
		// $tanggal_gl = $post['invoice_date_real'];
		// $total_gl   = 0;

		// $this->db->insert('gl_interface', [
		// 	'nomor'           => null,
		// 	'tgl'             => $tanggal_gl,
		// 	'bulan'           => substr($tanggal_gl, 5, 2),
		// 	'tahun'           => substr($tanggal_gl, 0, 4),
		// 	'kdcab'           => '101',
		// 	'jenis'           => 'JV',
		// 	'keterangan'      => 'Receive Invoice ' . $no_invoice,
		// 	'jenis_transaksi' => 'receive invoice',
		// 	'status'          => 'pending',
		// 	'user_id'         => $this->auth->user_id(),
		// 	'memo'            => json_encode([
		// 		'id_supplier'   => $kode_supplier,
		// 		'nama_supplier' => $nama,
		// 		'no_reff'       => $no_reff_jurnal,
		// 		'coaunbill'     => $coaunbill,
		// 		'totalunbill'   => $totalunbill,
		// 		'coaap'         => $coaap,
		// 		'totalap'       => $totalap,
		// 	]),
		// ]);
		// $id_gl_interface = $this->db->insert_id();

		// foreach ($det_Jurnaltes1 as $vals) {
		// 	$total_gl += $vals['debet'];
		// 	$this->db->insert('gl_interface_detail', [
		// 		'id_gl_interface' => $id_gl_interface,
		// 		'no_batch'        => null,
		// 		'tipe'            => 'JV',
		// 		'tanggal'         => $tanggal_gl,
		// 		'no_perkiraan'    => $vals['no_perkiraan'],
		// 		'keterangan'      => $vals['keterangan'],
		// 		'no_reff'         => $vals['no_reff'],
		// 		'debet'           => $vals['debet'],
		// 		'kredit'          => $vals['kredit'],
		// 		'created_at'      => date('Y-m-d H:i:s'),
		// 	]);
		// }

		// $this->db->update('gl_interface', ['jml' => $total_gl], ['id' => $id_gl_interface]);

		// ================================================================
		// COMMIT / ROLLBACK
		// ================================================================
		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			echo json_encode(['status' => 0, 'message' => 'Transaksi gagal, semua perubahan dibatalkan']);
		} else {
			$this->db->trans_commit();
			echo json_encode(['status' => 1]);
		}
	}

	public function search_inc()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $kode_supplier])->row();

		$this->db->select('a.*');
		$this->db->from('tr_invoice_po a');
		$this->db->like('a.no_po', 'TR');
		$get_list_inc = $this->db->get()->result_array();

		$hasil = '
			<table class="table table-bordered table_req_pay_inc">
            <thead class="bg-blue">
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">Tanggal Invoice</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center">Status</th>
					<th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
			';

		$no_po = [];
		foreach ($get_list_inc as $item) {
			$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
			if (!empty($get_no_po)) {
				$list_no_po = [];
				foreach ($get_no_po as $item_no_po) {
					$list_no_po[] = $item_no_po->no_surat;
				}

				if (!empty($list_no_po)) {
					$list_no_po = implode(', ', $list_no_po);

					$no_po[$item['kode_trans']] = $list_no_po;
				} else {
					$no_po[$item['kode_trans']] = '';
				}
			} else {
				$no_po[$item['kode_trans']] = '';
			}
		}

		$total_invoice = [];
		foreach ($get_list_inc as $item) {
			$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
			if (!empty($get_total_invoice)) {
				$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
			} else {
				$total_invoice[$item['kode_trans']] = 0;
			}
		}

		$no = 1;
		foreach ($get_list_inc as $item) {

			$exp_no_po = explode(',', $item['no_po']);

			$nm_supplier = [];

			$no_ipp = [];
			$this->db->select('a.no_ipp');
			$this->db->from('tr_incoming_check a');
			$this->db->where_in('a.kode_trans', $exp_no_po);
			$get_no_ipp = $this->db->get()->result();
			foreach ($get_no_ipp as $item_ipp) {
				$no_ipp[] = $item_ipp->no_ipp;
			}

			$this->db->select('a.no_ipp');
			$this->db->from('warehouse_adjustment a');
			$this->db->where_in('a.kode_trans', $exp_no_po);
			$get_no_ipp_ware = $this->db->get()->result();
			foreach ($get_no_ipp_ware as $item_ipp_ware) {
				$no_ipp[] = $item_ipp_ware->no_ipp;
			}
			if (count($no_ipp) > 0) {
				$no_ipp = implode(',', $no_ipp);
			} else {
				$no_ipp = '';
			}

			$this->db->select('b.nama as nm_supplier');
			$this->db->from('tr_purchase_order a');
			$this->db->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left');
			$this->db->where_in('a.no_po', explode(',', $no_ipp));
			$this->db->group_by('b.nama');
			$get_nm_supplier = $this->db->get()->result();
			foreach ($get_nm_supplier as $item_nm_supplier) {
				$nm_supplier[] = $item_nm_supplier->nm_supplier;
			}

			if (count($nm_supplier) > 0) {
				$nm_supplier = implode(', ', $nm_supplier);
			} else {
				$nm_supplier = '';
			}

			$status = '<div class="badge bg-yellow">Waiting</div>';
			// if($id_rec_invoice !== ''){
			$get_invoice_payment = $this->db->get_where('payment_approve', ['no_doc' => $item['id'], 'status' => 2])->result();
			if (count($get_invoice_payment) > 0) {
				$complete = 1;
				$status = '<div class="badge bg-green">Complete</div>';
			}
			// }

			$view = '<button type="button" class="btn btn-sm btn-info view_inc" data-id="' . $item['id'] . '"><i class="fa fa-eye"></i></button>';
			if ($kode_supplier !== '') {
				if (strpos($nm_supplier, $get_supplier->nama) !== false) {
					$hasil .= '<tr>';
					$hasil .= '<td style="text-align: center;">' . $no . '</td>';
					$hasil .= '<td style="text-align: center;">' . $item['id'] . '</td>';
					$hasil .= '<td style="text-align: center;">' . date('d F Y', strtotime($item['invoice_date'])) . '</td>';
					$hasil .= '<td>' . $nm_supplier . '</td>';
					$hasil .= '<td style="text-align: center;">' . $status . '</td>';
					$hasil .= '<td style="text-align: center;">' . $view . '</td>';
					$hasil .= '</tr>';
					$no++;
				}
			} else {
				$hasil .= '<tr>';
				$hasil .= '<td style="text-align: center;">' . $no . '</td>';
				$hasil .= '<td style="text-align: center;">' . $item['id'] . '</td>';
				$hasil .= '<td style="text-align: center;">' . date('d F Y', strtotime($item['invoice_date'])) . '</td>';
				$hasil .= '<td>' . $nm_supplier . '</td>';
				$hasil .= '<td style="text-align: center;">' . $status . '</td>';
				$hasil .= '<td style="text-align: center;">' . $view . '</td>';
				$hasil .= '</tr>';
				$no++;
			}
		}
		$hasil .= '
            </tbody>
        </table>
		';

		echo $hasil;
	}

	// public function search_dp()
	// {
	// 	$kode_supplier = $this->input->post('kode_supplier');

	// 	$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
	// 	$this->db->from('tr_purchase_order a');
	// 	$this->db->join('users b', 'b.id_user = a.created_by', 'left');
	// 	$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
	// 	$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
	// 	$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
	// 	$this->db->where('e.group_top', 76);
	// 	$this->db->where('a.status', '2');
	// 	$this->db->order_by('a.created_on', 'desc');
	// 	if ($kode_supplier !== '') {
	// 		$this->db->where('a.id_suplier', $kode_supplier);
	// 	}
	// 	$this->db->group_by('e.id');
	// 	$get_list_po = $this->db->get()->result_array();

	// 	$hasil = '
	// 		<table class="table table-bordered table_req_pay_dp">
	//         <thead class="bg-green">
	//             <tr>
	// 				<th class="text-center">No</th>
	// 				<th class="text-center">No. PO</th>
	// 				<th class="text-center">No. Purchase Invoice</th>
	// 				<th class="text-center">No. Invoice</th>
	// 				<th class="text-center">Nama Supplier</th>
	// 				<th class="text-center">Tanggal PO</th>
	// 				<th class="text-center">Keterangan</th>
	// 				<th class="text-center">Created By</th>
	// 				<th class="text-center">Status</th>
	// 				<th class="text-center">Action</th>
	// 			</tr>
	//         </thead>
	//         <tbody>
	// 		';

	// 	// $no_po = [];
	// 	// foreach ($get_list_po as $item) {
	// 	// 	$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
	// 	// 	if (!empty($get_no_po)) {
	// 	// 		$list_no_po = [];
	// 	// 		foreach ($get_no_po as $item_no_po) {
	// 	// 			$list_no_po[] = $item_no_po->no_surat;
	// 	// 		}

	// 	// 		if (!empty($list_no_po)) {
	// 	// 			$list_no_po = implode(', ', $list_no_po);

	// 	// 			$no_po[$item['kode_trans']] = $list_no_po;
	// 	// 		} else {
	// 	// 			$no_po[$item['kode_trans']] = '';
	// 	// 		}
	// 	// 	} else {
	// 	// 		$no_po[$item['kode_trans']] = '';
	// 	// 	}
	// 	// }

	// 	// $total_invoice = [];
	// 	// foreach ($get_list_po as $item) {
	// 	// 	$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
	// 	// 	if (!empty($get_total_invoice)) {
	// 	// 		$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
	// 	// 	} else {
	// 	// 		$total_invoice[$item['kode_trans']] = 0;
	// 	// 	}
	// 	// }

	// 	$no = 1;
	// 	foreach ($get_list_po as $item) {

	// 		$sts = '<div class="badge bg-blue">Waiting</div>';
	// 		$close = 0;
	// 		if ($item['ttl_persen_dp'] == $item['progress']) {
	// 			$sts = '<div class="badge bg-green">Complete</div>';
	// 			$close = 1;
	// 		} else {
	// 			if ($item['ttl_persen_dp'] > 0 && $item['ttl_persen_dp'] < 100) {
	// 				$sts = '<div class="badge bg-yellow">Partial</div>';
	// 			}
	// 		}

	// 		$get_incoming = $this->db->get_where('tr_incoming_check', ['no_ipp' => $item['no_po']])->result();
	// 		$arr_id_incoming = [];

	// 		foreach ($get_incoming as $item_incoming) {
	// 			$arr_id_incoming[] = $item_incoming->kode_trans;
	// 		}

	// 		if (!empty($arr_id_incoming)) {
	// 			$this->db->select('count(a.no_po) as num_po');
	// 			$this->db->from('tr_invoice_po a');
	// 			$this->db->where_in('a.no_po', $arr_id_incoming);
	// 			$num_invoice = $this->db->get()->row();

	// 			if ($num_invoice->num_po > 0) {
	// 				$sts = '<div class="badge bg-green">Complete</div>';
	// 				$close = 1;
	// 			}
	// 		}



	// 		$view_btn = '';
	// 		$req_pay_btn = '<button type="button" class="btn btn-sm btn-primary req_app" style="margin-left: 0.5rem" title="Receive Invoice" data-no_po="' . $item['no_surat'] . '" data-id_top="' . $item['id_top'] . '" data-tipe="dp"><i class="fa fa-file-invoice"></i> Receive Invoice</button>';
	// 		if ($close == 1) {
	// 			$get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['no_surat'], 'id_top' => $item['id_top']])->row_array();

	// 			$view_btn = '<button type="button" class="btn btn-sm btn-info view" data-id="' . $get_invoice['id'] . '" data-id_top="' . $get_invoice['id_top'] . '" data-tipe="dp" title="view"><i class="fa fa-eye"></i></button>';
	// 			$req_pay_btn = '';
	// 		}

	// 		$list_dp_btn = '';
	// 		// if($item['ttl_persen_dp'] > 0) {
	// 		//     $list_dp_btn = '<button type="button" class="btn btn-sm btn-warning list_dp" data-no_po="'.$item['no_po'].'" style="margin-left: 0.5rem"><i class="fa fa-list"></i></button>';
	// 		// }

	// 		$no_purchase_invoice = [];
	// 		$no_invoice = [];

	// 		$get_invoice = $this->db->select('a.*')
	// 			->from('tr_invoice_po a')
	// 			->where('a.id_top', $item['id_top'])
	// 			->like('a.no_po', $item['no_surat'])
	// 			->get()
	// 			->result();

	// 		foreach ($get_invoice as $item_invoice) {
	// 			$no_purchase_invoice[] = str_replace(',', '', $item_invoice->id);
	// 			$no_invoice[] = str_replace(',', '', $item_invoice->invoice_no);
	// 		}

	// 		if (!empty($no_purchase_invoice)) {
	// 			$no_purchase_invoice = implode(', ', $no_purchase_invoice);
	// 		} else {
	// 			$no_purchase_invoice = '';
	// 		}

	// 		if (!empty($no_invoice)) {
	// 			$no_invoice = implode(', ', $no_invoice);
	// 		} else {
	// 			$no_invoice = '';
	// 		}

	// 		$hasil .= '<tr>';
	// 		$hasil .= '<td class="text-center">' . $no . '</td>';
	// 		$hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
	// 		$hasil .= '<td class="text-center">' . $no_purchase_invoice . '</td>';
	// 		$hasil .= '<td class="text-center">' . $no_invoice . '</td>';
	// 		$hasil .= '<td class="text-center">' . $item['nm_supplier'] . '</td>';
	// 		$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
	// 		$hasil .= '<td class="text-center">' . $item['keterangan_top'] . '</td>';
	// 		$hasil .= '<td class="text-center">' . $item['nm_lengkap'] . '</td>';
	// 		$hasil .= '<td class="text-center">' . $sts . '</td>';
	// 		$hasil .= '<td style="text-align: center;">' . $view_btn . $req_pay_btn . $list_dp_btn . '</td>';
	// 		$hasil .= '</tr>';

	// 		$no++;
	// 	}
	// 	$hasil .= '
	//         </tbody>
	//     </table>
	// 	';

	// 	echo $hasil;
	// }

	public function search_pro()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
		$this->db->from('tr_purchase_order a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
		$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
		$this->db->where('e.group_top', 77);
		$this->db->where('a.status', '2');
		$this->db->order_by('a.created_on', 'desc');
		if ($kode_supplier !== '') {
			$this->db->where('a.id_suplier', $kode_supplier);
		}
		$this->db->group_by('e.id');
		$get_list_po = $this->db->get()->result_array();

		$hasil = '
			<table class="table table-bordered table_req_pay_pro">
            <thead class="bg-yellow">
                <tr>
					<th class="text-center">No</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. Purchase Invoice</th>
					<th class="text-center">No. Invoice</th>
					<th class="text-center">Nama Supplier</th>
					<th class="text-center">Tanggal PO</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Created By</th>
					<th class="text-center">Status</th>
					<th class="text-center">Action</th>
				</tr>
            </thead>
            <tbody>
			';

		// $no_po = [];
		// foreach ($get_list_po as $item) {
		// 	$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
		// 	if (!empty($get_no_po)) {
		// 		$list_no_po = [];
		// 		foreach ($get_no_po as $item_no_po) {
		// 			$list_no_po[] = $item_no_po->no_surat;
		// 		}

		// 		if (!empty($list_no_po)) {
		// 			$list_no_po = implode(', ', $list_no_po);

		// 			$no_po[$item['kode_trans']] = $list_no_po;
		// 		} else {
		// 			$no_po[$item['kode_trans']] = '';
		// 		}
		// 	} else {
		// 		$no_po[$item['kode_trans']] = '';
		// 	}
		// }

		// $total_invoice = [];
		// foreach ($get_list_po as $item) {
		// 	$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
		// 	if (!empty($get_total_invoice)) {
		// 		$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
		// 	} else {
		// 		$total_invoice[$item['kode_trans']] = 0;
		// 	}
		// }

		$no = 1;
		foreach ($get_list_po as $item) {

			$sts = '<div class="badge bg-blue">Waiting</div>';
			$close = 0;
			if ($item['ttl_persen_dp'] == $item['progress']) {
				$sts = '<div class="badge bg-green">Complete</div>';
				$close = 1;
			} else {
				if ($item['ttl_persen_dp'] > 0 && $item['ttl_persen_dp'] < 100) {
					$sts = '<div class="badge bg-yellow">Partial</div>';
				}
			}

			$get_incoming = $this->db->get_where('tr_incoming_check', ['no_ipp' => $item['no_po']])->result();
			$arr_id_incoming = [];

			foreach ($get_incoming as $item_incoming) {
				$arr_id_incoming[] = $item_incoming->kode_trans;
			}

			if (!empty($arr_id_incoming)) {
				$this->db->select('count(a.no_po) as num_po');
				$this->db->from('tr_invoice_po a');
				$this->db->where_in('a.no_po', $arr_id_incoming);
				$num_invoice = $this->db->get()->row();

				if ($num_invoice->num_po > 0) {
					$sts = '<div class="badge bg-green">Complete</div>';
					$close = 1;
				}
			}



			$view_btn = '';
			$req_pay_btn = '<button type="button" class="btn btn-sm btn-primary req_app" style="margin-left: 0.5rem" title="Receive Invoice" data-no_po="' . $item['no_surat'] . '" data-id_top="' . $item['id_top'] . '" data-tipe="dp"><i class="fa fa-file-invoice"></i> Receive Invoice</button>';
			if ($close == 1) {
				$get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['no_surat'], 'id_top' => $item['id_top']])->row_array();

				$view_btn = '<button type="button" class="btn btn-sm btn-info view" data-id="' . $get_invoice['id'] . '" data-id_top="' . $get_invoice['id_top'] . '" data-tipe="dp" title="view"><i class="fa fa-eye"></i></button>';
				$req_pay_btn = '';
			}

			$list_dp_btn = '';
			// if($item['ttl_persen_dp'] > 0) {
			//     $list_dp_btn = '<button type="button" class="btn btn-sm btn-warning list_dp" data-no_po="'.$item['no_po'].'" style="margin-left: 0.5rem"><i class="fa fa-list"></i></button>';
			// }
			$no_purchase_invoice = [];
			$no_invoice = [];

			$get_invoice = $this->db->select('a.*')
				->from('tr_invoice_po a')
				->where('a.id_top', $item['id_top'])
				->like('a.no_po', $item['no_surat'])
				->get()
				->result();

			foreach ($get_invoice as $item_invoice) {
				$no_purchase_invoice[] = str_replace(',', '', $item_invoice->id);
				$no_invoice[] = str_replace(',', '', $item_invoice->invoice_no);
			}

			if (!empty($no_purchase_invoice)) {
				$no_purchase_invoice = implode(', ', $no_purchase_invoice);
			} else {
				$no_purchase_invoice = '';
			}

			if (!empty($no_invoice)) {
				$no_invoice = implode(', ', $no_invoice);
			} else {
				$no_invoice = '';
			}

			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $no . '</td>';
			$hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
			$hasil .= '<td class="text-center">' . $no_purchase_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $no_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_supplier'] . '</td>';
			$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
			$hasil .= '<td class="text-center">' . $item['keterangan_top'] . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_lengkap'] . '</td>';
			$hasil .= '<td class="text-center">' . $sts . '</td>';
			$hasil .= '<td style="text-align: center;">' . $view_btn . $req_pay_btn . $list_dp_btn . '</td>';
			$hasil .= '</tr>';

			$no++;
		}
		$hasil .= '
            </tbody>
        </table>
		';

		echo $hasil;
	}

	public function search_ret()
	{
		$kode_supplier = $this->input->post('kode_supplier');

		$this->db->select('a.*, b.nm_lengkap, c.nama as nm_supplier, IF(SUM(d.persen_dp) IS NULL, 0, SUM(d.persen_dp)) as ttl_persen_dp, e.id as id_top, e.progress, e.nilai as nilai_top, e.keterangan as keterangan_top');
		$this->db->from('tr_purchase_order a');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		$this->db->join('new_supplier c', 'c.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po e', 'e.no_po = a.no_po');
		$this->db->join('tr_invoice_po d', 'd.no_po = a.no_surat AND d.id_top = e.id', 'left');
		$this->db->where('e.group_top', 78);
		$this->db->where('a.status', '2');
		$this->db->order_by('a.created_on', 'desc');
		if ($kode_supplier !== '') {
			$this->db->where('a.id_suplier', $kode_supplier);
		}
		$this->db->group_by('e.id');
		$get_list_po = $this->db->get()->result_array();

		$hasil = '
			<table class="table table-bordered table_req_pay_ret">
            <thead class="bg-red">
                <tr>
					<th class="text-center">No</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. Purchase Invoice</th>
					<th class="text-center">No. Invoice</th>
					<th class="text-center">Nama Supplier</th>
					<th class="text-center">Tanggal PO</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Created By</th>
					<th class="text-center">Status</th>
					<th class="text-center">Action</th>
				</tr>
            </thead>
            <tbody>
			';

		// $no_po = [];
		// foreach ($get_list_po as $item) {
		// 	$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
		// 	if (!empty($get_no_po)) {
		// 		$list_no_po = [];
		// 		foreach ($get_no_po as $item_no_po) {
		// 			$list_no_po[] = $item_no_po->no_surat;
		// 		}

		// 		if (!empty($list_no_po)) {
		// 			$list_no_po = implode(', ', $list_no_po);

		// 			$no_po[$item['kode_trans']] = $list_no_po;
		// 		} else {
		// 			$no_po[$item['kode_trans']] = '';
		// 		}
		// 	} else {
		// 		$no_po[$item['kode_trans']] = '';
		// 	}
		// }

		// $total_invoice = [];
		// foreach ($get_list_po as $item) {
		// 	$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
		// 	if (!empty($get_total_invoice)) {
		// 		$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
		// 	} else {
		// 		$total_invoice[$item['kode_trans']] = 0;
		// 	}
		// }

		$no = 1;
		foreach ($get_list_po as $item) {

			$sts = '<div class="badge bg-blue">Waiting</div>';
			$close = 0;
			if ($item['ttl_persen_dp'] == $item['progress']) {
				$sts = '<div class="badge bg-green">Complete</div>';
				$close = 1;
			} else {
				if ($item['ttl_persen_dp'] > 0 && $item['ttl_persen_dp'] < 100) {
					$sts = '<div class="badge bg-yellow">Partial</div>';
				}
			}

			$get_incoming = $this->db->get_where('tr_incoming_check', ['no_ipp' => $item['no_po']])->result();
			$arr_id_incoming = [];

			foreach ($get_incoming as $item_incoming) {
				$arr_id_incoming[] = $item_incoming->kode_trans;
			}

			if (!empty($arr_id_incoming)) {
				$this->db->select('count(a.no_po) as num_po');
				$this->db->from('tr_invoice_po a');
				$this->db->where_in('a.no_po', $arr_id_incoming);
				$num_invoice = $this->db->get()->row();

				if ($num_invoice->num_po > 0) {
					$sts = '<div class="badge bg-green">Complete</div>';
					$close = 1;
				}
			}



			$view_btn = '';
			$req_pay_btn = '<button type="button" class="btn btn-sm btn-primary req_app" style="margin-left: 0.5rem" title="Receive Invoice" data-no_po="' . $item['no_surat'] . '" data-id_top="' . $item['id_top'] . '" data-tipe="dp"><i class="fa fa-file-invoice"></i> Receive Invoice</button>';
			if ($close == 1) {
				$get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['no_surat'], 'id_top' => $item['id_top']])->row_array();

				$view_btn = '<button type="button" class="btn btn-sm btn-info view" data-id="' . $get_invoice['id'] . '" data-id_top="' . $get_invoice['id_top'] . '" data-tipe="dp" title="view"><i class="fa fa-eye"></i></button>';
				$req_pay_btn = '';
			}

			$list_dp_btn = '';
			// if($item['ttl_persen_dp'] > 0) {
			//     $list_dp_btn = '<button type="button" class="btn btn-sm btn-warning list_dp" data-no_po="'.$item['no_po'].'" style="margin-left: 0.5rem"><i class="fa fa-list"></i></button>';
			// }
			$no_purchase_invoice = [];
			$no_invoice = [];

			$get_invoice = $this->db->select('a.*')
				->from('tr_invoice_po a')
				->where('a.id_top', $item['id_top'])
				->like('a.no_po', $item['no_surat'])
				->get()
				->result();

			foreach ($get_invoice as $item_invoice) {
				$no_purchase_invoice[] = str_replace(',', '', $item_invoice->id);
				$no_invoice[] = str_replace(',', '', $item_invoice->invoice_no);
			}

			if (!empty($no_purchase_invoice)) {
				$no_purchase_invoice = implode(', ', $no_purchase_invoice);
			} else {
				$no_purchase_invoice = '';
			}

			if (!empty($no_invoice)) {
				$no_invoice = implode(', ', $no_invoice);
			} else {
				$no_invoice = '';
			}

			$hasil .= '<tr>';
			$hasil .= '<td class="text-center">' . $no . '</td>';
			$hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
			$hasil .= '<td class="text-center">' . $no_purchase_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $no_invoice . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_supplier'] . '</td>';
			$hasil .= '<td class="text-center">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
			$hasil .= '<td class="text-center">' . $item['keterangan_top'] . '</td>';
			$hasil .= '<td class="text-center">' . $item['nm_lengkap'] . '</td>';
			$hasil .= '<td class="text-center">' . $sts . '</td>';
			$hasil .= '<td style="text-align: center;">' . $view_btn . $req_pay_btn . $list_dp_btn . '</td>';
			$hasil .= '</tr>';

			$no++;
		}
		$hasil .= '
            </tbody>
        </table>
		';

		echo $hasil;
	}

	public function check_list_dp()
	{
		$this->db->select('a.*, b.nama as nama_supplier, c.group_top, c.nilai');
		$this->db->from('tr_purchase_order a');
		$this->db->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left');
		$this->db->join('tr_top_po c', 'c.no_po = a.no_po', 'left');
		$this->db->where('c.group_top', 76);
		$this->db->order_by('a.no_po', 'DESC');

		$list_po = $this->db->get()->result_array();
		$list_supplier = $this->db->get('new_supplier')->result_array();

		$this->template->set('list_dp', $list_po);
		$this->template->set('list_supplier', $list_supplier);

		$this->template->render('check_list_dp');
	}

	public function check_list_inc()
	{
		$get_list_inc = $this->db->query('
				SELECT
					a.kode_trans as kode_trans,
					a.no_ipp as no_ipp,
					a.inc_inv_create as inc_inv_create,
					a.tanggal as tanggal,
					"incoming material" as tipe_incoming
				FROM
					tr_incoming_check a
				WHERE
					a.checked = "Y"
					AND a.inc_inv_create IS NULL

				UNION ALL

				SELECT 
					a.kode_trans as kode_trans,
					a.no_ipp as no_ipp,
					a.inc_inv_create as inc_inv_create,
					a.tanggal as tanggal,
					a.category as tipe_incoming
				FROM
					warehouse_adjustment a
				WHERE
					a.category = "incoming stok" OR a.category = "incoming non rutin" OR a.category = "incoming asset"

				ORDER BY tanggal DESC
			')->result_array();

		$no_po = [];
		foreach ($get_list_inc as $item) {
			$get_no_po = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $item['no_ipp']) . "') OR a.no_surat IN ('" . str_replace(",", "','", $item['no_ipp']) . "')")->result();
			if (!empty($get_no_po)) {
				$list_no_po = [];
				foreach ($get_no_po as $item_no_po) {
					$list_no_po[] = $item_no_po->no_surat;
				}

				if (!empty($list_no_po)) {
					$list_no_po = implode(', ', $list_no_po);

					$no_po[$item['kode_trans']] = $list_no_po;
				} else {
					$no_po[$item['kode_trans']] = '';
				}
			} else {
				$no_po[$item['kode_trans']] = '';
			}
		}

		$total_invoice = [];
		foreach ($get_list_inc as $item) {
			$get_total_invoice = $this->db->select('total_invoice')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row();
			if (!empty($get_total_invoice)) {
				$total_invoice[$item['kode_trans']] = $get_total_invoice->total_invoice;
			} else {
				if ($item['tipe_incoming'] == 'incoming non rutin') {
					$this->db->select('SUM(a.total_harga) as ttl_harga');
					$this->db->from('tr_pr_detail_kasbon a');
					$this->db->where('a.id_kasbon', $item['no_ipp']);
					$get_total = $this->db->get()->row();

					$total_invoice[$item['kode_trans']] = $get_total->ttl_harga;
				} else if ($item['tipe_incoming'] == 'incoming asset') {
					$this->db->select('SUM(a.harga_total) as ttl_harga');
					$this->db->from('dt_trans_po a');
					$this->db->join('tr_purchase_order b', 'b.no_po = a.no_po');
					$this->db->where('b.no_surat', $item['no_ipp']);
					$get_total = $this->db->get()->row();

					$total_invoice[$item['kode_trans']] = $get_total->ttl_harga;
				} else {
					$total_invoice[$item['kode_trans']] = 0;
				}
			}
		}

		$get_supplier = $this->db->get('new_supplier')->result();

		$this->template->set('list_inc', $get_list_inc);
		$this->template->set('no_po', $no_po);
		$this->template->set('total_invoice', $total_invoice);
		$this->template->set('list_supplier', $get_supplier);
		$this->template->render('check_list_inc');
	}

	public function check_invoice()
	{
		$kode_trans = $this->input->post('kode_trans');
		$tipe_incoming = $this->input->post('tipe_incoming');
		$tipe = $this->input->post('tipe');

		$this->db->trans_start();
		if ($tipe == 1) {
			$checked_invoice = $this->db->get_where('tr_check_invoice', ['kode_trans' => $kode_trans, 'id_user' => $this->auth->user_id()])->result();
			if (count($checked_invoice) < 1) {
				$insert_check_invoice = $this->db->insert('tr_check_invoice', [
					'kode_trans' => $kode_trans,
					'id_user' => $this->auth->user_id()
				]);
				if (!$insert_check_invoice) {
					print_r($this->db->error($insert_check_invoice));
					exit;
				}
			}
		} else {
			$this->db->delete('tr_check_invoice', ['kode_trans' => $kode_trans, 'id_user' => $this->auth->user_id()]);
		}

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

	public function clear_checked_invoice()
	{
		$this->db->trans_start();

		$this->db->delete('tr_check_invoice', ['id_user' => $this->auth->user_id()]);
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

	public function checkCheckedInv()
	{
		$get_checked_invoice = $this->db->get_where('tr_check_invoice', ['id_user' => $this->auth->user_id()])->result();

		echo count($get_checked_invoice);
	}

	public function rec_invoice_btn()
	{
		$id_user = $this->auth->user_id();
		$data_po = $this->db->select('
                            tr_purchase_order.*, 
                            tr_top_po.id as id_top, 
                            tr_top_po.group_top, 
                            tr_top_po.progress, 
                            tr_top_po.nilai, 
                            tr_top_po.keterangan
                        ')
			->from('tr_purchase_order')
			->join('tr_check_invoice', 'tr_check_invoice.kode_trans = tr_purchase_order.no_po')
			->join('tr_top_po', 'tr_top_po.no_po = tr_purchase_order.no_po', 'left')
			->where('tr_check_invoice.id_user', $id_user)
			->get()
			->row_array();

		if (empty($data_po)) {
			echo "<div class='alert alert-warning text-center'>Tidak ada data PO yang dipilih atau data tidak ditemukan.</div>";
			return;
		}

		$get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $data_po['id_suplier']])->row_array();

		$this->template->set('data_po',      $data_po);
		$this->template->set('get_supplier', $get_supplier);
		$this->template->set('get_top',      (object)['progress' => $data_po['progress'], 'nilai' => $data_po['nilai']]);
		$this->template->render('add_dp');
	}

	public function req_payment_dp()
	{
		$id_receive = $this->input->post('id_receive');
		$tipe       = $this->input->post('tipe'); // 'dp', 'import', 'local'

		if (empty($id_receive) || empty($tipe)) {
			echo json_encode(['status' => 0, 'message' => 'Data tidak valid.']);
			return;
		}

		// Tentukan tabel dan tipe_rp berdasarkan tipe
		$tabel_receive = 'tr_receive_invoice';

		// Tentukan tipe_rp berdasarkan tipe
		if ($tipe === 'dp') {
			$tipe_rp       = 'invoice_dp';
		} elseif ($tipe === 'import') {
			$tipe_rp       = 'invoice_import';
		} elseif ($tipe === 'local') {
			$tipe_rp       = 'invoice_local';
		} else {
			echo json_encode(['status' => 0, 'message' => 'Tipe tidak valid.']);
			return;
		}

		// Cek data exist
		$data = $this->db->get_where($tabel_receive, ['id' => $id_receive])->row_array();
		if (empty($data)) {
			echo json_encode(['status' => 0, 'message' => 'Data invoice tidak ditemukan.']);
			return;
		}

		// Cek status — jangan bisa re-request kalau sudah diajukan
		if (isset($data['status']) && strtolower($data['status']) !== 'draft') {
			echo json_encode(['status' => 0, 'message' => 'Invoice ini sudah pernah diajukan.']);
			return;
		}

		// Cek duplikat di request_payment
		$cek_rp_cond = [
			'no_doc' => $data['no_po'],
			'tipe'   => $tipe_rp
		];

		if ($tipe === 'import' && !empty($data['id_ros'])) {
			$cek_rp_cond['id_ros'] = $data['id_ros'];
		}

		$cek_rp = $this->db->get_where('request_payment', $cek_rp_cond)->row();
		if ($cek_rp) {
			echo json_encode(['status' => 0, 'message' => 'Request payment untuk invoice ini sudah pernah dibuat.']);
			return;
		}

		// Ambil data PO & Supplier
		$data_po = $this->db->get_where('tr_purchase_order', ['no_po' => $data['no_po']])->row_array();
		$get_supplier = $this->db->get_where('new_supplier', [
			'kode_supplier' => $data_po['id_suplier'] ?? ''
		])->row_array();

		// Hitung jumlah (simpan dalam currency asli, kurs diterapkan saat pembayaran)
		$kurs = (float)($data['kurs'] ?? 1);
		if ($kurs <= 0) $kurs = 1;

		$jumlah = (float)($data['nilai_invoice'] ?? 0);
		$jumlah_total = $jumlah + (float)($data['nilai_ppn'] ?? 0);

		// Ambil data user
		$get_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();

		// INSERT ke request_payment dengan status=1
		// Status 1 = muncul di halaman index untuk diisi tanggal pembayaran
		$data_insert = [
			'no_doc'      => $data['no_po'] ?? '',
			'no_surat'    => $data['no_surat'] ?? '',
			'nama'        => $get_user['nm_lengkap'] ?? $this->auth->user_name(),
			'tgl_doc'     => $data['invoice_date'] ?? date('Y-m-d'),
			'keperluan'   => 'Pembayaran ' . ucfirst(str_replace('_', ' ', $tipe_rp)) . ' - ' . ($data['no_surat'] ?? '') . ' - ' . ($data['nomor_invoice'] ?? ''),
			'tipe'        => $tipe_rp,
			'jumlah'      => $jumlah_total,
			'status'      => 'open',
			'tanggal'     => null,
			'currency'    => $data['currency'] ?? 'IDR',
			'bank_id'     => $data['bank'] ?? '',
			'accnumber'   => $data['no_bank'] ?? '',
			'accname'     => $data['nm_acc_bank'] ?? '',
			'bank_name'   => $data['bank'] ?? '',
			'ids'         => (string)$id_receive,
			'id_ros'      => $data['id_ros'] ?? null, // Tambahkan id_ros di sini
			'admin_bank'  => 0,
			'total_pph'   => 0,
			'id_supplier' => $data_po['id_suplier'] ?? '',
			'nm_supplier' => $get_supplier['nama'] ?? '',
			'created_by'  => $get_user['nm_lengkap'] ?? $this->auth->user_name(),
			'created_on'  => date('Y-m-d H:i:s'),
		];

		$this->db->trans_begin();

		// Update status di tabel receive → 'request payment'
		$this->db->update(
			$tabel_receive,
			[
				'status'     => 'request payment',
				'updated_by' => $this->auth->user_id(),
				'updated_on' => date('Y-m-d H:i:s'),
			],
			['id' => $id_receive]
		);

		// Insert ke request_payment
		$this->db->insert('request_payment', $data_insert);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			echo json_encode(['status' => 0, 'message' => 'Gagal mengajukan request payment.']);
		} else {
			$this->db->trans_commit();
			echo json_encode(['status' => 1, 'message' => 'Request payment berhasil diajukan. Silakan isi tanggal pembayaran di menu Request Payment.']);
		}
	}

	// ═══════════════════════════════════════════════════════════════════
	// PRIVATE: Generate Jurnal Invoice DP ke gl_interface
	// ═══════════════════════════════════════════════════════════════════

	private function _generate_jurnal_invoice_dp($id_dp, $no_po, $no_surat, $jumlah_rupiah)
	{
		$tgl_inv    = date('Y-m-d');
		$created_on = date('Y-m-d H:i:s');
		$user_id    = $this->auth->user_id();

		// COA: hanya 2 akun
		$coa = [
			'dp'     => '1104-01-02', // Advance Purchase (Uang Muka Pembelian) - DEBET
			'hutang' => '2101-01-02', // Hutang Usaha (Accounts Payable) - KREDIT
		];

		// Validate COA dari DBACC
		$coa_names = [];
		$db_acc = $this->load->database('accounting', TRUE);
		foreach ($coa as $key => $no_perkiraan) {
			$row = $db_acc->get_where('coa_master', ['no_perkiraan' => $no_perkiraan])->row();
			$coa_names[$key] = (!empty($row)) ? $row->nama : $no_perkiraan;
		}

		$nominal = (int) round($jumlah_rupiah);
		$keterangan = "Invoice DP - {$no_surat} | PO: {$no_po}";
		$nomor_jv   = $this->_generate_nomor_jv_dp();

		// ── Insert header GL Interface ──
		$this->db->insert('gl_interface', [
			'nomor'           => $nomor_jv,
			'tgl'             => $tgl_inv,
			'bulan'           => date('m'),
			'tahun'           => date('Y'),
			'kdcab'           => '101',
			'jenis'           => 'JV',
			'keterangan'      => $keterangan,
			'jenis_transaksi' => 'invoice dp',
			'status'          => 'pending',
			'user_id'         => $user_id,
			'memo'            => json_encode([
				'id_dp'       => $id_dp,
				'no_po'       => $no_po,
				'no_surat'    => $no_surat,
				'nominal'     => $nominal,
			]),
		]);

		$id_gl = $this->db->insert_id();

		// ── Insert detail: DEBET - Advance Purchase ──
		$this->db->insert('gl_interface_detail', [
			'id_gl_interface' => $id_gl,
			'no_batch'        => $nomor_jv,
			'tipe'            => 'JV',
			'tanggal'         => $tgl_inv,
			'no_perkiraan'    => $coa['dp'],
			'id_material'     => null,
			'nm_material'     => null,
			'id_gudang'       => null,
			'no_coil'         => null,
			'keterangan'      => $coa_names['dp'] . " | {$keterangan}",
			'no_reff'         => $no_surat,
			'debet'           => $nominal,
			'kredit'          => 0,
			'created_at'      => $created_on,
		]);

		// ── Insert detail: KREDIT - Hutang Usaha ──
		$this->db->insert('gl_interface_detail', [
			'id_gl_interface' => $id_gl,
			'no_batch'        => $nomor_jv,
			'tipe'            => 'JV',
			'tanggal'         => $tgl_inv,
			'no_perkiraan'    => $coa['hutang'],
			'id_material'     => null,
			'nm_material'     => null,
			'id_gudang'       => null,
			'no_coil'         => null,
			'keterangan'      => $coa_names['hutang'] . " | {$keterangan}",
			'no_reff'         => $no_surat,
			'debet'           => 0,
			'kredit'          => $nominal,
			'created_at'      => $created_on,
		]);
	}

	private function _generate_nomor_jv_dp()
	{
		$db_acc = $this->load->database('accounting', TRUE);

		$cabang = $db_acc->query(
			"SELECT nomorJC FROM pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1 FOR UPDATE"
		)->row();

		if (empty($cabang)) {
			// Fallback jika data cabang belum ada
			return '101-AJV' . date('ym') . '0001';
		}

		$nomor_urut = (int) $cabang->nomorJC + 1;
		$nomor_jv   = '101-AJV' . date('ym') . $nomor_urut;

		$db_acc->query(
			"UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'"
		);

		return $nomor_jv;
	}
}
