<?php
defined('BASEPATH') or exit('No direct script access allowed');
$data_status = array();
class Pembayaran_material extends Admin_Controller
{
	protected $viewPermission   = 'Incoming_Stok.View';
	protected $addPermission    = 'Incoming_Stok.Add';
	protected $managePermission = 'Incoming_Stok.Manage';
	protected $deletePermission = 'Incoming_Stok.Delete';

	protected $data_status;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'pembayaran_material/master_model',
			'pembayaran_material/Pembayaran_material_model',
			'all/All_model',
			'pembayaran_material/Jurnal_model'
		));
		$this->data_status = array('0' => 'Pengajuan', '1' => 'Approve', '2' => 'Selesai');
	}
	//==================================================================================================================
	//==================================================REQUEST PEMBAYARAN==============================================
	//==================================================================================================================
	function index()
	{
		$data_Group			= $this->master_model->getArray('groups', array(), 'id', 'name');
		$data_list 			= $this->pembayaran_material_model->get_data_json_request_payment();
		$data_listnm 			= $this->pembayaran_material_model->get_data_json_request_payment_nm();
		$data = array(
			'title'			=> 'Indeks Of Request Payment',
			'action'		=> 'index',
			'row_group'		=> $data_Group,
			'results'		=> $data_list,
			'resultsnm'		=> $data_listnm,
			'data_status'	=> $this->data_status
		);
		history('View Request Payment');
		$this->load->view('Pembayaran_material/index_request_payment', $data);
	}


	public function request_payment_save()
	{
		$id_req = $this->input->post("id_req");
		$request_date = $this->input->post("request_date");
		$no_po = $this->input->post("no_po");
		$id_supplier = $this->input->post("id_supplier");
		$nilai_ppn = $this->input->post("nilai_ppn");
		$curs_header = $this->input->post("curs_header");
		$nilai_total = $this->input->post("nilai_total");
		$total_bayar = $this->input->post("total_bayar");
		$po_belum_dibayar = $this->input->post("po_belum_dibayar");
		$sisa_dp = $this->input->post("sisa_dp");
		$no_request = $this->input->post("no_request");
		$no_invoice = $this->input->post("no_invoice");
		$nilai_invoice = $this->input->post("nilai_invoice");
		$keterangan = $this->input->post("keterangan");
		$potongan_dp = $this->input->post("potongan_dp");
		$potongan_claim = $this->input->post("potongan_claim");
		$keterangan_potongan = $this->input->post("keterangan_potongan");
		$request_payment = $this->input->post("request_payment");
		$invoice_ppn = $this->input->post("invoice_ppn");
		$nilai_pph_invoice = $this->input->post("nilai_pph_invoice");
		$nilai_po_invoice = $this->input->post("nilai_po_invoice");
		$tipe = $this->input->post("tipe");
		$tipetrans = $this->input->post("tipetrans");
		$payfor = $this->input->post("payfor");
		$coa_pph = $this->input->post("coa_pph");
		$bank_transfer = $this->input->post("bank_transfer");

		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');

		$this->db->trans_begin();
		$dataheader =  array(
			'nilai_po_invoice' => $nilai_po_invoice,
			'nilai_pph_invoice' => $nilai_pph_invoice,
			'request_payment' => $request_payment,
			'request_date' => $request_date,
			'no_invoice' => $no_invoice,
			'nilai_invoice' => $nilai_invoice,
			'keterangan' => $keterangan,
			'tipe' => $tipe,
			'invoice_ppn' => $invoice_ppn,
			'potongan_dp' => $potongan_dp,
			'potongan_claim' => $potongan_claim,
			'keterangan_potongan' => $keterangan_potongan,
			'coa_pph' => $coa_pph,
			'bank_transfer' => $bank_transfer,
			'modified_on' => date('Y-m-d H:i:s'),
			'modified_by' => $Username
		);
		if ($tipetrans == "2") {
			$this->All_model->DataUpdate('purchase_order_request_payment_nm', $dataheader, array('id' => $id_req));
			$this->All_model->DataUpdate('tran_po_detail', array('status_pay' => ''), array('status_pay' => $no_request));
			if (!empty($payfor)) {
				foreach ($payfor as $val) {
					if ($val != "") $this->All_model->DataUpdate('tran_po_detail', array('status_pay' => $no_request), array('id' => $val));
				}
			}
		} else {
			$this->All_model->DataUpdate('purchase_order_request_payment', $dataheader, array('id' => $id_req));
			$this->All_model->DataUpdate('tran_material_po_detail', array('status_pay' => ''), array('status_pay' => $no_request));
			if (!empty($payfor)) {
				foreach ($payfor as $val) {
					if ($val != "") $this->All_model->DataUpdate('tran_material_po_detail', array('status_pay' => $no_request), array('id' => $val));
				}
			}
		}
		$this->db->trans_complete();
		if ($this->db->trans_status()) {
			$this->db->trans_commit();
			$keterangan     = "SUKSES, simpan data ";
			$result         = TRUE;
		} else {
			$this->db->trans_rollback();
			$keterangan     = "GAGAL, simpan data ";
			$result = FALSE;
			history('Save Edit Request Payment, No ' . $id_req);
		}
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}



	//==================================================================================================================
	//===============================================PEMBAYARAN=========================================================
	//==================================================================================================================
	public function payment_list()
	{
		// PR: expense dengan exp_inv_po=1 ATAU tipe invoice PO (DP/Import/Local) ATAU tipe Cash
		$results = $this->db->query("
			SELECT a.*, 
				GROUP_CONCAT(DISTINCT d.no_doc SEPARATOR '||') AS detail_no_doc
			FROM payment_approve a 
			LEFT JOIN tr_expense b ON b.no_doc = a.no_doc AND a.tipe = 'expense'
			LEFT JOIN payment_approve_details d ON d.payment_id = a.no_doc
			WHERE (a.id_payment IS NOT NULL AND a.id_payment <> '')
			AND (
				(a.tipe = 'expense' AND b.exp_inv_po = 1)
				OR a.tipe IN ('invoice_dp', 'invoice_import', 'invoice_local')
				OR a.tipe = 'Cash'
			)
			GROUP BY a.no_doc 
			ORDER BY a.created_on DESC
		")->result();

		// Non-PR: sisanya (bukan invoice PO, bukan Cash, bukan expense PO)
		$results2 = $this->db->query("
			SELECT a.*, 
				GROUP_CONCAT(DISTINCT d.no_doc SEPARATOR '||') AS detail_no_doc
			FROM payment_approve a 
			LEFT JOIN tr_expense b ON b.no_doc = a.no_doc AND a.tipe = 'expense'
			LEFT JOIN payment_approve_details d ON d.payment_id = a.no_doc
			WHERE (a.id_payment IS NOT NULL AND a.id_payment <> '')
			AND a.tipe NOT IN ('invoice_dp', 'invoice_import', 'invoice_local', 'Cash')
			AND (a.tipe <> 'expense' OR b.exp_inv_po IS NULL OR b.exp_inv_po <> 1)
			GROUP BY a.id_payment 
			ORDER BY a.created_on DESC
		")->result();

		$data = array(
			'title'       => 'Payment List',
			'action'      => 'index',
			'data_status' => $this->data_status,
			'results'     => $results,
			'results2'    => $results2
		);
		$this->template->set($data);
		$this->template->render('index_payment_new');
	}

	public function form_payment_new()
	{

		$id_payment = explode(';', $_GET['id_payment']);

		$check_transpoty_driver = $this->Pembayaran_material_model->check_transport_payment($id_payment);

		$jurnal_refill_petty_cash = '';
		if ($check_transpoty_driver > 0) {
			$jurnal_refill_petty_cash = $this->Pembayaran_material_model->jurnal_refill_petty_cash($id_payment);
		}

		$get_payment = $this->db
			->select('a.*')
			->from('request_payment a')
			->where_in('a.id', $id_payment)
			->get()
			->result();
		$get_supplier = $this->db->get('new_supplier')->result();
		$get_mata_uang = $this->db->get_where('mata_uang', ['deleted_by' => 0, 'activation' => 'active'])->result();

		// Ambil list bank dari coa_master (database accounting)
		$db_acc = $this->load->database('accounting', TRUE);
		$db_acc->select('no_perkiraan, nama');
		$db_acc->from('coa_master');
		$db_acc->where_in('no_perkiraan', [
			'1101-01-01',
			'1101-01-02',
			'1101-01-03',
			'1101-01-04',
			'1101-01-05',
			'1101-01-06',
			'1101-01-07',
			'1101-01-08',
			'1101-02-01',
			'1101-02-02',
			'1101-02-03',
			'1101-02-04',
			'1101-02-05',
			'1101-02-06',
			'1101-02-07',
			'1101-02-08',
			'1101-02-09',
			'1101-02-10',
			'1101-02-11',
			'1101-02-12',
			'1101-02-13',
			'1101-02-14',
			'1101-02-15',
			'1101-02-16',
			'1101-02-17',
			'1101-02-18',
			'1101-02-19',
			'1101-02-20',
			'1101-02-21',
			'1101-02-22',
			'1101-02-23'
		]);
		$db_acc->order_by('no_perkiraan', 'ASC');
		$get_bank = $db_acc->get()->result();

		$data = [
			'id_payment' => implode(',', $id_payment),
			'result_payment' => $get_payment,
			'list_supplier' => $get_supplier,
			'list_bank' => $get_bank,
			'list_mata_uang' => $get_mata_uang,
			'jurnal_refill_petty_cash' => $jurnal_refill_petty_cash
		];
		$this->template->set('results', $data);
		$this->template->render('form_payment_new');
	}

	public function save_payment_new()
	{
		$id_req = $this->input->post("id_req");
		$payment_date = $this->input->post("payment_date");
		$bank_coa = $this->input->post("bank_coa");
		$bank_nilai = $this->input->post("bank_nilai");
		$curs = $this->input->post("curs");
		$id_supplier = $this->input->post("id_supplier");
		$curs_header = $this->input->post("curs_header");

		$biaya_admin_forex = $this->input->post("biaya_admin_forex");
		$biaya_admin = $this->input->post("biaya_admin");
		$curs_admin = $this->input->post("curs_admin");

		$biaya_admin_forex2 = $this->input->post("biaya_admin_forex2");
		$biaya_admin2 = $this->input->post("biaya_admin2");
		$curs_admin2 = $this->input->post("curs_admin2");
		$bank_coa_admin = $this->input->post("bank_coa_admin");

		$nilai_bayar_bank = $this->input->post("nilai_bayar_bank");

		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');
		$alokasi_dp = $this->input->post("alokasi_dp");
		$alokasi_hutang = $this->input->post("alokasi_hutang");
		$tipetrans = $this->input->post("tipetrans");
		$selisih_kurs1 = 0;
		$this->db->trans_begin();
		$jenis_jurnal = 'BUK20';
		if ($curs_header != 'IDR') $jenis_jurnal = 'BUK21';
		try {

			$no_payment = $this->All_model->GetAutoGenerate('format_payment');
			$nomor_jurnal = $jenis_jurnal . $no_payment . rand(100, 999);

			$dataheader =  array(
				'no_payment' => $no_payment,
				'id_supplier' => $id_supplier,
				'curs_header' => $curs_header,
				'payment_date' => $payment_date,
				'bank_coa' => $bank_coa,
				'nilai_bayar_bank' => $nilai_bayar_bank,
				'curs' => $curs,
				'bank_nilai' => $bank_nilai,
				'modul' => 'PO',
				'biaya_admin_forex' => $biaya_admin_forex,
				'biaya_admin' => $biaya_admin,
				'curs_admin' => $curs_admin,
				'biaya_admin_forex2' => $biaya_admin_forex2,
				'biaya_admin2' => $biaya_admin2,
				'curs_admin2' => $curs_admin2,
				'bank_coa_admin' => $bank_coa_admin,
				'status' => '1',
				'created_on' => date('Y-m-d H:i:s'),
				'created_by' => $Username
			);

			$this->All_model->dataSave('purchase_order_request_payment_header', $dataheader);
			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('1','4','8') order by parameter_no")->result();
			$det_Jurnaltes1 = array();
			$total = 0;
			foreach ($datajurnal1 as $rec) {
				// CASH BANK
				if ($rec->parameter_no == "1") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => ($bank_nilai),
						'debet' => 0,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => $nilai_bayar_bank,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK EXPENSE
				if ($rec->parameter_no == "4") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin2,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK
				if ($rec->parameter_no == "8") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin,
						'debet' => 0,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin2,
						'debet' => 0,
						'nilai_valas_debet' => 0,
						'nilai_valas_kredit' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
			}
			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);
			foreach ($id_req as $keys) {
				$this->All_model->DataUpdate('purchase_order_request_payment', array('status' => '2', 'payment_date' => $payment_date, 'no_payment' => $no_payment), array('id' => $keys));
				$data = $this->db->query("select * from purchase_order_request_payment where id='" . $keys . "'")->row();
				$selisih_kurs = 0;
				$nilai_terima_barang_idr = 0;
				$datapoheader = $this->db->query("select * from tran_material_po_header where no_po='" . $data->no_po . "'")->row();
				if ($datapoheader->terima_barang_idr != 0) {
					$kurs_hutang = $datapoheader->kurs_terima;
					$selisih_kurs = (($data->nilai_po_invoice * $curs) - $data->nilai_po_invoice * $datapoheader->kurs_terima);

					if ($selisih_kurs < 0) {
						$selisih_kurs1 = $selisih_kurs * (-1);
					} else {
						$selisih_kurs1 = $selisih_kurs;
					}
				} else {

					$kurs_hutang = $data->kurs_receive_invoice;

					$selisih_kurs = (($data->nilai_po_invoice * $curs) - $data->nilai_po_invoice * $data->kurs_receive_invoice);

					if ($selisih_kurs < 0) {
						$selisih_kurs1 = $selisih_kurs * (-1);
					} else {
						$selisih_kurs1 = $selisih_kurs;
					}
				}


				// update PO
				$nilai_dp_kurs = 0;
				$addsql = "";
				if ($data->tipe == 'TR-01') {
					$nilai_dp_kurs = ($data->nilai_po_invoice * $curs);
					$addsql = ", nilai_dp_kurs=" . $nilai_dp_kurs . "";
				}

				if ($data->tipe == 'TR-01') {
					$this->db->query("update tran_material_po_header set terima_barang_kurs=0, terima_barang_idr=0
				" . $addsql . ", total_bayar=(total_bayar+" . ($data->nilai_po_invoice) . "),
				total_bayar_rupiah=(total_bayar_rupiah+" . ($data->nilai_po_invoice * $curs) . "),
				bayar_kurs=(bayar_kurs+" . ($data->nilai_po_invoice) . "),
				bayar_idr=(bayar_idr+" . ($data->nilai_po_invoice * $curs) . ")
				" .
						($data->tipe == 'TR-01' ?
							",nilai_dp=(nilai_dp+" . $data->nilai_po_invoice . "), sisa_dp=(sisa_dp+" . $data->nilai_po_invoice . ")" :
							", nilai_dp=(nilai_dp-" . $data->potongan_dp . "), sisa_dp=(sisa_dp-" . $data->potongan_dp . ")") .
						" where no_po='" . $data->no_po . "'");
				}

				if ($data->tipe == 'TR-02') {
					$this->db->query("update tran_material_po_header set terima_barang_kurs=0, terima_barang_idr=0
				" . $addsql . ", total_bayar=(total_bayar+" . ($data->nilai_po_invoice) . "),
				total_bayar_rupiah=(total_bayar_rupiah+" . ($data->nilai_po_invoice * $curs) . "),
				bayar_kurs=(bayar_kurs+" . ($data->nilai_po_invoice) . "),
				bayar_idr=(bayar_idr+" . ($data->nilai_po_invoice * $curs) . "),
				sisa_hutang_kurs=(sisa_hutang_kurs-" . ($data->nilai_po_invoice) . "),
				sisa_hutang_idr=(sisa_hutang_idr-" . ($data->nilai_po_invoice * $curs) . ")				
				" .
						($data->tipe == 'TR-01' ?
							",nilai_dp=(nilai_dp+" . $data->nilai_po_invoice . "), sisa_dp=(sisa_dp+" . $data->nilai_po_invoice . ")" :
							", nilai_dp=(nilai_dp-" . $data->potongan_dp . "), sisa_dp=(sisa_dp-" . $data->potongan_dp . ")") .
						" where no_po='" . $data->no_po . "'");
				}

				$keterangan		= 'Pembayaran ' . $no_payment;
				$data_coa 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='3'")->row();
				$data_supplier 	= $this->db->query("select * from supplier where id_supplier='" . $data->id_supplier . "'")->row();
				$data_coa2 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='2'")->row();

				if ($data->curs_header == 'IDR') {
					$coahutang = '2101-01-01';
				} else {
					$coahutang = '2101-01-04';
				}

				if ($data->tipe == 'TR-01') {
					$datahutang = array(
						'tipe'       	 => 'BUK',
						'nomor'       	 => $Nomor_JV,
						'tanggal'        => $tanggal,
						'no_perkiraan'   => $coahutang,
						'keterangan'     => $keterangan,
						'no_reff'     	 => $data->no_po,
						'debet'      	 => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
						'kredit'         => 0,
						'id_supplier'    => $data->id_supplier,
						'nama_supplier'  => $data_supplier->nm_supplier,
						'no_request'     => $no_payment,
						'debet_usd'		 => (($curs_header != 'IDR') ? ($data->nilai_po_invoice + $data->invoice_ppn) : 0),
						'kredit_usd'	=> 0,

					);
					$this->db->insert('tr_kartu_hutang', $datahutang);
				}


				if ($data->tipe == 'TR-02') {
					$datahutang = array(
						'tipe'       	 => 'BUK',
						'nomor'       	 => $Nomor_JV,
						'tanggal'        => $tanggal,
						'no_perkiraan'   => $coahutang,
						'keterangan'     => $keterangan,
						'no_reff'     	 => $data->no_po,
						'debet'      	 => (($data->nilai_po_invoice + $data->invoice_ppn) * $kurs_hutang),
						'kredit'         => 0,
						'id_supplier'    => $data->id_supplier,
						'nama_supplier'  => $data_supplier->nm_supplier,
						'no_request'     => $no_payment,
						'debet_usd'		 => (($curs_header != 'IDR') ? ($data->nilai_po_invoice + $data->invoice_ppn) : 0),
						'kredit_usd'	=> 0,

					);
					$this->db->insert('tr_kartu_hutang', $datahutang);
				}

				$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('2','3','5','6','7','9') order by parameter_no")->result();
				foreach ($datajurnal1 as $rec) {
					if ($data->modul == 'PO') {
						// UANG MUKA
						if ($rec->parameter_no == "2") {
							if ($data->tipe == 'TR-01') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => round(($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
									'nilai_valas_debet' => ($data->nilai_po_invoice + $data->invoice_ppn),
									'nilai_valas_kredit' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								if ($data->potongan_dp > 0) {
									$det_Jurnaltes1[] = array(
										'nomor' => $nomor_jurnal,
										'tanggal' => $payment_date,
										'tipe' => 'BUK',
										'no_perkiraan' => $rec->no_perkiraan,
										'keterangan' => $data->keterangan,
										'no_request' => $data->no_po,
										'debet' => 0,
										'kredit' => 0,
										'nilai_valas_debet' => 0,
										'nilai_valas_kredit' => 0,
										'no_reff' => $no_payment,
										'jenis_jurnal' => $jenis_jurnal,
										'nocust' => $data->id_supplier,
										'stspos' => '1'
									);
								}
							}
						}
						// HUTANG
						if ($rec->parameter_no == "3") {
							if ($data->tipe == 'TR-02') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => round((($data->nilai_po_invoice + $data->invoice_ppn) * $kurs_hutang)),
									'nilai_valas_debet' => ($data->nilai_po_invoice + $data->invoice_ppn),
									'nilai_valas_kredit' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => 0,
									'nilai_valas_debet' => 0,
									'nilai_valas_kredit' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							}
						}
					}

					if ($data->modul == 'FORWARDER') {
						// HUTANG FORWARDER
						if ($rec->parameter_no == "5") {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => 'FORWARDER ',
								'no_request' => $data->no_po,
								'kredit' => 0,
								'debet' => round(($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
								'nilai_valas_debet' => 0,
								'nilai_valas_kredit' => 0,
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// PPN
					if ($rec->parameter_no == "6") {
						/*
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUK', 'no_perkiraan' => $rec->no_perkiraan, 'keterangan' => $data->keterangan, 'no_request' => $data->no_po, 'kredit' => 0, 'debet' => ($data->invoice_ppn*$curs), 'no_reff' => $no_payment, 'jenis_jurnal'=>$jenis_jurnal, 'nocust'=>$data->id_supplier
					);
*/
					}
					// PPH
					if ($rec->parameter_no == "7") {
						if ($data->nilai_pph_invoice <> 0) {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $data->coa_pph,
								'keterangan' => $data->keterangan,
								'no_request' => $data->no_po,
								'kredit' => round($data->nilai_pph_invoice * $curs),
								'debet' => 0,
								'nilai_valas_debet' => 0,
								'nilai_valas_kredit' => 0,
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// SELISIH KURS
					if ($rec->parameter_no == "9") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => $data->keterangan,
							'no_request' => $data->no_po,
							'kredit' => round($selisih_kurs < 0 ? ($selisih_kurs * -1) : 0),
							'debet' => round($selisih_kurs >= 0 ? $selisih_kurs : 0),
							'nilai_valas_debet' => 0,
							'nilai_valas_kredit' => 0,
							'no_reff' => $no_payment,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $data->id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$this->db->insert_batch('jurnaltras', $det_Jurnaltes1);

			//auto jurnal

			foreach ($det_Jurnaltes1 as $vals) {
				$datadetail = array(
					'tipe'			=> 'BUK',
					'nomor'			=> $Nomor_JV,
					'tanggal'		=> $tanggal,
					'no_perkiraan'	=> $vals['no_perkiraan'],
					'keterangan'	=> $vals['keterangan'],
					'no_reff'		=> $vals['no_reff'],
					'debet'			=> $vals['debet'],
					'kredit'		=> $vals['kredit'],
					'nilai_valas_debet'			=> $vals['nilai_valas_debet'],
					'nilai_valas_kredit'		=> $vals['nilai_valas_kredit'],
				);
				$total = ($total + $vals['debet']);
				$this->db->insert(DBACC . '.jurnal', $datadetail);
			}

			$keterangan		= 'Pembayaran ' . $no_payment;
			$dataJVhead = array(
				'nomor' 	    	=> $Nomor_JV,
				'tgl'	         	=> $tanggal,
				'jml'	            => $total,
				'jenis_ap'	        => 'V',
				'bayar_kepada'		=> $data_supplier->nm_supplier,
				'kdcab'				=> '101',
				'jenis_reff' 		=> 'BUK',
				'no_reff' 			=> $no_payment,
				'note'				=> $keterangan,
				'user_id'			=> $Username,
				'ho_valid'			=> '',
			);

			$this->db->insert(DBACC . '.japh', $dataJVhead);
			$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
			$this->db->query($Qry_Update_Cabang_acc);


			//end auto jurnal

			$this->db->trans_complete();
			if ($this->db->trans_status()) {
				$this->db->trans_commit();
				$result         = TRUE;
				history('Save Payment');
			} else {
				$this->db->trans_rollback();
				$result = FALSE;
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$result = FALSE;
		}

		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	/**
	 * Save Payment untuk tipe Invoice PO (DP/Import/Local)
	 * - Insert ke tr_payment_paid
	 * - Update payment_approve (status=2, detail pembayaran)
	 * - Insert ke gl_interface + gl_interface_detail (tipe BUK)
	 * - UNTUK PAYMENT DP
	 */
	// public function save_payment_po()
	// {
	// 	if (ob_get_level()) ob_end_clean();
	// 	$post = $this->input->post();

	// 	$this->db->trans_begin();

	// 	try {
	// 		$id_payment_arr = explode(',', $post['id_payment']);
	// 		$tgl_bayar      = $post['tgl_bayar'];
	// 		$bank_coa       = $post['bank'];           // no_perkiraan COA bank
	// 		$mata_uang      = $post['mata_uang'];
	// 		$kurs           = (float)str_replace(',', '', $post['kurs_payment'] ?? '1');
	// 		if ($kurs <= 0) $kurs = 1;
	// 		$payment_bank   = (float)str_replace(',', '', $post['payment_bank'] ?? '0'); // nominal IDR yang dibayar via bank
	// 		$bank_charge    = (float)str_replace(',', '', $post['bank_charge'] ?? '0');
	// 		$keterangan     = $post['keterangan_pembayaran'] ?? '';
	// 		$id_supplier    = $post['supplier_input'] ?? '';
	// 		$nm_supplier    = $post['nm_supplier_input'] ?? '';

	// 		// Upload dokumen
	// 		$filenames = '';
	// 		if (!empty($_FILES['upload_doc']['name'])) {
	// 			$config_upload = [
	// 				'upload_path'   => FCPATH . 'assets/expense/',
	// 				'allowed_types' => '*',
	// 				'remove_spaces' => TRUE,
	// 				'encrypt_name'  => TRUE
	// 			];
	// 			$this->load->library('upload', $config_upload);
	// 			$this->upload->initialize($config_upload);
	// 			if ($this->upload->do_upload('upload_doc')) {
	// 				$filenames = $this->upload->data('file_name');
	// 			}
	// 		}

	// 		// Generate ID payment paid
	// 		$db_acc = $this->load->database('accounting', TRUE);
	// 		$kode_bank = '';
	// 		$id_payment_paid = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $tgl_bayar);

	// 		// Ambil tagihan_idr yang sudah disimpan saat approval management
	// 		$this->db->select('tagihan_idr, jumlah');
	// 		$this->db->where_in('id', $id_payment_arr);
	// 		$row_payment = $this->db->get('payment_approve')->row();
	// 		$total_doc_idr_paid = (int)($row_payment->tagihan_idr ?? round(($row_payment->jumlah ?? 0) * $kurs));
	// 		$selisih_total = $total_doc_idr_paid - (int)round($payment_bank);

	// 		// Hitung nominal_asli, nominal_asli_idr, selisih_kurs_idr
	// 		$nominal_asli = (!empty($row_payment)) ? (float)$row_payment->jumlah : 0;
	// 		$nominal_asli_idr = $kurs * $nominal_asli;
	// 		$tagihan_idr_pa = (!empty($row_payment) && $row_payment->tagihan_idr > 0) ? (float)$row_payment->tagihan_idr : $total_doc_idr_paid;
	// 		$selisih_kurs_idr_calc = $nominal_asli_idr - $tagihan_idr_pa;

	// 		// 1. Insert ke tr_payment_paid
	// 		$this->db->insert('tr_payment_paid', [
	// 			'id'                    => $id_payment_paid,
	// 			'bank_charge'           => $bank_charge,
	// 			'created_by'            => $this->auth->user_id(),
	// 			'created_on'            => date('Y-m-d H:i:s'),
	// 			'tgl_bayar'             => $tgl_bayar,
	// 			'coa_bank'              => $bank_coa,
	// 			'supplier'              => $id_supplier,
	// 			'nm_supplier'           => $nm_supplier,
	// 			'mata_uang'             => $mata_uang,
	// 			'kurs_payment'          => $kurs,
	// 			'payment_bank'          => $payment_bank,
	// 			'payment_bank_charge'   => $bank_charge,
	// 			'total_doc'             => $total_doc_idr_paid,
	// 			'selisih_total'         => $selisih_total,
	// 			'keterangan_pembayaran' => $keterangan,
	// 			'link_doc'              => $filenames,
	// 			'nominal_asli'          => $nominal_asli,
	// 			'nominal_asli_idr'      => $nominal_asli_idr,
	// 		]);

	// 		// 2. Update payment_approve — status, id_payment, dan data pembayaran
	// 		$nm_coa_bank = '';
	// 		$row_bank = $db_acc->get_where('coa_master', ['no_perkiraan' => $bank_coa])->row();
	// 		if (!empty($row_bank)) $nm_coa_bank = $row_bank->nama;

	// 		// Hitung total_pph dan total_ppn dari form
	// 		$total_pph = 0;
	// 		$total_ppn = 0;
	// 		$tipe_pph_val = '';
	// 		if (!empty($post['dt'])) {
	// 			foreach ($post['dt'] as $detail) {
	// 				$total_pph += (float)str_replace(',', '', $detail['nilai_pph'] ?? '0');
	// 				$total_ppn += (float)str_replace(',', '', $detail['nilai_ppn'] ?? '0');
	// 				$tipe_pph_val = ($detail['tipe_pph'] == 1) ? 'PPH 23' : 'PPH 22';
	// 			}
	// 		}

	// 		$this->db->where_in('id', $id_payment_arr);
	// 		$this->db->update('payment_approve', [
	// 			'id_payment'            => $id_payment_paid,
	// 			'status'                => 2,
	// 			'tgl_bayar'             => $tgl_bayar,
	// 			'pay_by'                => $this->auth->user_name(),
	// 			'pay_on'                => date('Y-m-d H:i:s'),
	// 			'supplier'              => $id_supplier,
	// 			'keterangan_pembayaran' => $keterangan,
	// 			'coa_bank'              => $bank_coa,
	// 			'nm_coa_bank'           => $nm_coa_bank,
	// 			'mata_uang'             => $mata_uang,
	// 			'kurs_payment'          => $kurs,
	// 			'payment_bank'          => $payment_bank,
	// 			'total_payment'         => $total_doc_idr_paid,
	// 			'selisih'               => $selisih_total,
	// 			'total_pph'             => $total_pph,
	// 			'total_ppn'             => $total_ppn,
	// 			'tipe_pph'              => $tipe_pph_val,
	// 			'id_supplier'           => $id_supplier,
	// 			'nm_supplier'           => $nm_supplier,
	// 			'link_doc'              => $filenames,
	// 			'bank_charge'           => $bank_charge,
	// 			'nominal_asli'          => $nominal_asli,
	// 			'nominal_asli_idr'      => $nominal_asli_idr,
	// 			'tagihan_idr'           => $tagihan_idr_pa,
	// 			'dibayar_idr'           => (int)round($payment_bank),
	// 			'selisih_kurs_idr'      => $selisih_kurs_idr_calc,
	// 		]);

	// 		// 3. Generate GL Interface (tipe BUK)
	// 		// Nominal hutang sudah dihitung di atas: $total_doc_idr_paid
	// 		$coa_hutang  = '2101-01-02';
	// 		$coa_selisih = '7201-01-07';
	// 		$coa_bank_charge = '7201-01-01';

	// 		// Ambil nama COA dari coa_master
	// 		$nm_hutang  = 'Hutang Usaha';
	// 		$nm_bank    = $bank_coa;
	// 		$nm_selisih = 'Selisih Kurs';
	// 		$nm_bank_charge  = 'Bank Charge';

	// 		$row_hutang = $db_acc->get_where('coa_master', ['no_perkiraan' => $coa_hutang])->row();
	// 		if (!empty($row_hutang)) $nm_hutang = $row_hutang->nama;

	// 		$row_bank = $db_acc->get_where('coa_master', ['no_perkiraan' => $bank_coa])->row();
	// 		if (!empty($row_bank)) $nm_bank = $row_bank->nama;

	// 		$row_selisih = $db_acc->get_where('coa_master', ['no_perkiraan' => $coa_selisih])->row();
	// 		if (!empty($row_selisih)) $nm_selisih = $row_selisih->nama;

	// 		$row = $db_acc->get_where('coa_master', ['no_perkiraan' => $coa_bank_charge])->row();
	// 		if (!empty($row)) $nm_bank_charge = $row->nama;

	// 		// Nominal jurnal
	// 		$nominal_hutang = $total_doc_idr_paid;         // 2101-01-02 DEBET (jumlah PO × kurs)
	// 		$nominal_bank   = (int)round($payment_bank);   // BANK KREDIT (inputan user)
	// 		$selisih_kurs   = $nominal_hutang - $nominal_bank; // 7201-01-07

	// 		$keterangan_jv = "Pembayaran PO - " . $id_payment_paid . " | " . $keterangan;

	// 		// Generate nomor JV
	// 		$cabang = $db_acc->query("SELECT nomorJC FROM pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1")->row();
	// 		$nomor_urut = (!empty($cabang)) ? (int)$cabang->nomorJC + 1 : 1;
	// 		$nomor_jv = '101-ABK' . date('ym') . $nomor_urut;
	// 		$db_acc->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'");

	// 		// Insert header gl_interface
	// 		$this->db->insert('gl_interface', [
	// 			'nomor'           => $nomor_jv,
	// 			'tgl'             => $tgl_bayar,
	// 			'bulan'           => date('m', strtotime($tgl_bayar)),
	// 			'tahun'           => date('Y', strtotime($tgl_bayar)),
	// 			'kdcab'           => '101',
	// 			'jenis'           => 'BUK',
	// 			'keterangan'      => $keterangan_jv,
	// 			'jenis_transaksi' => 'payment po',
	// 			'status'          => 'pending',
	// 			'user_id'         => $this->auth->user_id(),
	// 			'memo'            => json_encode([
	// 				'id_payment_paid' => $id_payment_paid,
	// 				'id_supplier'     => $id_supplier,
	// 				'nm_supplier'     => $nm_supplier,
	// 				'mata_uang'       => $mata_uang,
	// 				'kurs'            => $kurs,
	// 			]),
	// 		]);
	// 		$id_gl = $this->db->insert_id();

	// 		$created_on = date('Y-m-d H:i:s');

	// 		// Detail 1: DEBET - Hutang Usaha (2101-01-02)
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl,
	// 			'no_batch'        => $nomor_jv,
	// 			'tipe'            => 'BUK',
	// 			'tanggal'         => $tgl_bayar,
	// 			'no_perkiraan'    => $coa_hutang,
	// 			'keterangan'      => $nm_hutang . ' | ' . $keterangan_jv,
	// 			'no_reff'         => $id_payment_paid,
	// 			'no_request'      => implode(',', $id_payment_arr),
	// 			'debet'           => $nominal_hutang,
	// 			'kredit'          => 0,
	// 			'created_at'      => $created_on,
	// 		]);

	// 		// Detail 2: KREDIT - Bank (inputan user)
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl,
	// 			'no_batch'        => $nomor_jv,
	// 			'tipe'            => 'BUK',
	// 			'tanggal'         => $tgl_bayar,
	// 			'no_perkiraan'    => $bank_coa,
	// 			'keterangan'      => $nm_bank . ' | ' . $keterangan_jv,
	// 			'no_reff'         => $id_payment_paid,
	// 			'no_request'      => implode(',', $id_payment_arr),
	// 			'debet'           => 0,
	// 			'kredit'          => $nominal_bank,
	// 			'created_at'      => $created_on,
	// 		]);

	// 		// Detail 3: Selisih Kurs (7201-01-07) — selalu ditampilkan
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl,
	// 			'no_batch'        => $nomor_jv,
	// 			'tipe'            => 'BUK',
	// 			'tanggal'         => $tgl_bayar,
	// 			'no_perkiraan'    => $coa_selisih,
	// 			'keterangan'      => $nm_selisih . ' | ' . $keterangan_jv,
	// 			'no_reff'         => $id_payment_paid,
	// 			'no_request'      => implode(',', $id_payment_arr),
	// 			'debet'           => ($selisih_kurs < 0) ? abs($selisih_kurs) : 0,
	// 			'kredit'          => ($selisih_kurs > 0) ? $selisih_kurs : 0,
	// 			'created_at'      => $created_on,
	// 		]);

	// 		// Detail 4: DEBET - Bank Charge (7201-01-01)
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl,
	// 			'no_batch'        => $nomor_jv,
	// 			'tipe'            => 'BUK',
	// 			'tanggal'         => $tgl_bayar,
	// 			'no_perkiraan'    => $coa_bank_charge,
	// 			'keterangan'      => $nm_bank_charge . ' | ' . $keterangan_jv,
	// 			'no_reff'         => $id_payment_paid,
	// 			'no_request'      => implode(',', $id_payment_arr),
	// 			'debet'           => $bank_charge, // sudah default 0 dari parsing di atas, jadi selalu ke-insert walau 0
	// 			'kredit'          => 0,
	// 			'created_at'      => $created_on,
	// 		]);

	// 		$this->db->trans_commit();

	// 		$this->output->set_content_type('application/json');
	// 		echo json_encode([
	// 			'status' => 1,
	// 			'pesan'  => 'Payment berhasil disimpan.'
	// 		]);
	// 	} catch (Exception $e) {
	// 		$this->db->trans_rollback();
	// 		echo json_encode([
	// 			'status' => 0,
	// 			'pesan'  => 'Gagal: ' . $e->getMessage()
	// 		]);
	// 	}
	// }

	public function save_payment_po()
	{
		// Mulai buffer baru — supaya semua Notice/Warning/Deprecated yang mungkin
		// muncul di tengah proses TERTANGKAP di buffer ini, bukan langsung ke output.
		ob_start();

		$post = $this->input->post();

		$this->db->trans_begin();

		try {
			// id_payment dari form = ID request_payment (bisa lebih dari 1, dipisah koma)
			$id_req_arr = [];
			$raw_ids = explode(',', $post['id_payment']);
			foreach ($raw_ids as $rid) {
				$rid = trim($rid);
				if (!empty($rid) && is_numeric($rid)) {
					$id_req_arr[] = $rid;
				}
			}
			// Fallback: ambil dari dt[n][id_payment]
			if (empty($id_req_arr) && !empty($post['dt'])) {
				foreach ($post['dt'] as $dt_item) {
					if (!empty($dt_item['id_payment'])) {
						$id_req_arr[] = $dt_item['id_payment'];
					}
				}
			}
			if (empty($id_req_arr)) {
				throw new Exception('Data request payment tidak ditemukan.');
			}

			// Ambil data request_payment untuk referensi
			$req_payment_rows = $this->db->where_in('id', $id_req_arr)->get('request_payment')->result();
			if (empty($req_payment_rows)) {
				throw new Exception('Data request payment tidak valid.');
			}
			$first_req = $req_payment_rows[0];
			$tipe_payment = $first_req->tipe; // invoice_dp, invoice_import, invoice_local

			$tgl_bayar      = $post['tgl_bayar'];
			$bank_coa       = $post['bank'];
			$mata_uang      = $post['mata_uang'];
			$kurs           = (float)str_replace(',', '', $post['kurs_payment'] ?? '1');
			if ($kurs <= 0) $kurs = 1;
			$payment_bank   = (float)str_replace(',', '', $post['payment_bank'] ?? '0'); // Nilai Bank (foreign currency)
			$bank_charge    = (float)str_replace(',', '', $post['bank_charge'] ?? '0');
			$keterangan     = $post['keterangan_pembayaran'] ?? '';
			$id_supplier    = $post['supplier_input'] ?? '';
			$nm_supplier    = $post['nm_supplier_input'] ?? '';

			// Nilai Bank IDR = Nilai Bank × Kurs Payment
			$nilai_bank_idr = $payment_bank * $kurs;

			// Generate no_doc (ID payment_approve) dengan format BK-
			$db_acc = $this->load->database('accounting', TRUE);
			$kode_bank = '';
			$no_doc_payment = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $tgl_bayar);

			// Hitung total_pph, total_ppn, dan subtotal dari detail
			$total_pph = 0;
			$total_ppn = 0;
			$subtotal = 0;
			$tipe_pph_val = '';

			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$det_pph = (float)str_replace(',', '', $detail['nilai_pph'] ?? '0');
					$det_ppn = (float)str_replace(',', '', $detail['nilai_ppn'] ?? '0');

					// Subtotal per baris = jumlah asli PO (dt[n][jumlah]) x kurs form
					$det_jumlah_asli = (float)str_replace(',', '', $detail['jumlah'] ?? '0');
					$det_jumlah_idr  = $det_jumlah_asli * $kurs;

					$total_pph += $det_pph;
					$total_ppn += $det_ppn;
					$subtotal += $det_jumlah_idr;

					if (!empty($detail['tipe_pph'])) {
						$tipe_pph_val = ($detail['tipe_pph'] == 1) ? 'PPH 23' : 'PPH 22';
					}
				}
			}

			// Grand Total Payment = Subtotal + PPN - PPH + Bank Charge
			$grand_total = $subtotal + $total_ppn - $total_pph + $bank_charge;

			// Selisih Kurs: berdasarkan kurs_receive_invoice vs kurs_payment
			$selisih_kurs_idr = 0;
			$nominal_kurs_receive = 0; // jumlah_rupiah dari tr_receive_invoice (nilai IDR saat receive)
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$id_ri = $detail['ids'] ?? '';

					if (!empty($id_ri)) {
						$ri = $this->db->get_where('tr_receive_invoice', ['id' => $id_ri])->row();
						if ($ri) {
							$nominal_kurs_receive += (float)($ri->jumlah_rupiah ?? 0);
						}
					}
				}
				// Selisih Kurs = Nilai Bank IDR - Nominal dari tr_receive_invoice
				$selisih_kurs_idr = $nilai_bank_idr - $nominal_kurs_receive;
			}

			// Nama COA Bank
			$nm_coa_bank = '';
			$row_bank = $db_acc->get_where('coa_master', ['no_perkiraan' => $bank_coa])->row();
			if (!empty($row_bank)) $nm_coa_bank = $row_bank->nama;

			// id_payment = no_doc dari request_payment (referensi ke PO)
			$id_payment_val = $first_req->no_doc;

			// INSERT ke payment_approve (header)
			// NB: 'link_doc' di header TIDAK dipakai lagi — lampiran sekarang
			// per baris PO, disimpan di payment_approve_details.link_doc
			$this->db->insert('payment_approve', [
				'no_doc'                => $no_doc_payment,
				'tipe'                  => $tipe_payment,
				'tgl_bayar'             => $tgl_bayar,
				'tgl_doc'               => $tgl_bayar,
				'pay_by'                => $this->auth->user_name(),
				'pay_on'                => date('Y-m-d H:i:s'),
				'created_by'            => $this->auth->user_name(),
				'created_on'            => date('Y-m-d H:i:s'),
				'supplier'              => $id_supplier,
				'keterangan_pembayaran' => $keterangan,
				'coa_bank'              => $bank_coa,
				'nm_coa_bank'           => $nm_coa_bank,
				'mata_uang'             => $mata_uang,
				'currency'              => $mata_uang,
				'kurs_payment'          => $kurs,
				'payment_bank'          => $payment_bank,
				'total_payment'         => $subtotal,
				'total_pph'             => $total_pph,
				'total_ppn'             => $total_ppn,
				'grand_total_payment'   => $grand_total,
				'tipe_pph'              => $tipe_pph_val,
				'id_supplier'           => $id_supplier,
				'nm_supplier'           => $nm_supplier,
				'link_doc'              => '',
				'bank_charge'           => $bank_charge,
				'nominal_asli'          => $payment_bank,
				'nominal_asli_idr'      => $nilai_bank_idr,
				'tagihan_idr'           => $nominal_kurs_receive,
				'dibayar_idr'           => (int)round($nilai_bank_idr),
				'selisih_kurs_idr'      => $selisih_kurs_idr,
				'selisih'               => 0,
				'id_payment'            => $id_payment_val,
				'gl_hutang_dagang'      => (int)round($nominal_kurs_receive),
				'gl_selisih_kurs'       => (int)round($selisih_kurs_idr),
				'gl_total_payment'      => (int)round($subtotal),
			]);

			// INSERT payment_approve_details per PO
			if (!empty($post['dt'])) {
				$total_bayar_kurs = $payment_bank; // sudah di-parse dari $post['payment_bank'] di atas
				$total_bayar_idr  = (float)str_replace(',', '', $post['nilai_bank_idr'] ?? '0');

				foreach ($post['dt'] as $detail) {
					$det_ppn = (float)str_replace(',', '', $detail['nilai_ppn'] ?? '0');
					$det_pph = (float)str_replace(',', '', $detail['nilai_pph'] ?? '0');
					$det_kurs_invoice = (float)str_replace(',', '', $detail['kurs_invoice'] ?? '0');

					// Ambil nilai_invoice & jumlah_rupiah dari tr_receive_invoice
					$id_ri = $detail['ids'] ?? '';
					$nilai_invoice_kurs = 0;
					$nilai_invoice_idr  = 0;
					if (!empty($id_ri)) {
						$ri = $this->db->get_where('tr_receive_invoice', ['id' => $id_ri])->row();
						if ($ri) {
							$nilai_invoice_kurs = $ri->nilai_invoice ?? 0;
							$nilai_invoice_idr  = $ri->jumlah_rupiah ?? 0;
						}
					}

					// BARU: upload lampiran per baris (opsional), key = id_payment baris ini
					$file_upload_row = $this->upload_doc_per_row($detail['id_payment'] ?? '');

					$this->db->insert('payment_approve_details', [
						'payment_id'         => $no_doc_payment,
						'no_doc'             => $detail['no_surat'] ?? '',
						'no_po'              => $detail['no_doc'] ?? '',
						'deskripsi'          => 'Payment ' . ($detail['no_surat'] ?? $detail['no_doc'] ?? ''),

						'nilai_invoice_kurs' => $nilai_invoice_kurs,
						'nilai_invoice_idr'  => $nilai_invoice_idr,
						'total_bayar_kurs'   => $total_bayar_kurs,
						'total_bayar_idr'    => $total_bayar_idr,

						'total'              => $nilai_invoice_idr,
						'nilai_invoice'      => $nilai_invoice_idr,

						'nilai_ppn'          => $det_ppn,
						'nilai_pph'          => $det_pph,
						'tipe_pph'           => (($detail['tipe_pph'] ?? null) == 1) ? 'PPH 23' : 'PPH 22',
						'kurs_invoice'       => $det_kurs_invoice,
						'id_receive_invoice' => $detail['ids'] ?? null,
						'currency'           => $mata_uang,
						'file_original_name' => $file_upload_row['file_original_name'], // BARU: nama asli file (opsional)
						'file_hash_name'     => $file_upload_row['file_hash_name'],     // BARU: nama file tersimpan di server (opsional)
						'created_by'         => $this->auth->user_name(),
						'created_on'         => date('Y-m-d H:i:s'),
					]);
				}
			}

			// Update status tr_receive_invoice menjadi 'payment'
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					if (!empty($detail['ids'])) {
						$this->db->update('tr_receive_invoice', ['status' => 'payment'], ['id' => $detail['ids']]);
					}
				}
			}

			$this->db->where_in('id', $id_req_arr);
			$this->db->update('request_payment', ['status' => 'finish']);

			// Hapus tr_choosed_payment untuk item yang sudah diproses
			$this->db->where('id_user', $this->auth->user_id());
			$this->db->where_in('id_payment', $id_req_arr);
			$this->db->delete('tr_choosed_payment');

			$row_lengkap = $this->db->get_where('payment_approve', ['no_doc' => $no_doc_payment])->row_array();

			if (empty($row_lengkap)) {
				throw new Exception('Gagal mengambil data payment_approve yang baru disimpan (no_doc: ' . $no_doc_payment . ').');
			}

			$this->load->model('gl_interface/Gl_interface_model');

			// Kode jurnal pembayaran DP berbeda berdasarkan LOI PO (Import vs Lokal).
			// no_po diambil dari request_payment.no_doc (referensi PO).
			$po_ref = $this->db->select('loi')
				->get_where('tr_purchase_order', ['no_po' => $first_req->no_doc])
				->row();
			$loi_po = strtolower(trim($po_ref->loi ?? ''));
			if ($loi_po === 'import') {
				$action_jurnal = 'save_payment_po_import';
			} else {
				$action_jurnal = 'save_payment_po_local';
			}

			$mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'Pembayaran Material', 'action' => $action_jurnal])->row();
			$kode_jurnal = $mapping ? $mapping->kode_master_jurnal : 'BUK002'; // fallback
			$id_gl = $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $row_lengkap);

			if ($id_gl === false) {
				throw new Exception('Gagal generate jurnal: template ' . $kode_jurnal . ' tidak ditemukan/kosong');
			}

			$this->db->trans_commit();

			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 1,
				'pesan'  => 'Payment berhasil disimpan.'
			]);
			exit;
		} catch (Exception $e) {
			$this->db->trans_rollback();

			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 0,
				'pesan'  => 'Gagal: ' . $e->getMessage()
			]);
			exit;
		}
	}

	/**
	 * Save Payment untuk Document Import (ROS Payment: BM/LS/Insurance/Other Cost)
	 * Nilai dibaca dari tr_ros_payment.nominal (bukan tr_receive_invoice).
	 * Tanpa PPN/PPH. Setelah simpan:
	 *  - tr_ros_payment.status  -> 'lunas'
	 *  - request_payment.status -> 'finish'
	 *  - jika SEMUA payment ROS lunas -> tr_ros_header.status_payment = 'close'
	 */
	public function save_payment_ros()
	{
		ob_start();

		$post = $this->input->post();

		$this->db->trans_begin();

		try {
			// id_payment dari form = ID request_payment (bisa lebih dari 1, dipisah koma)
			$id_req_arr = [];
			$raw_ids = explode(',', $post['id_payment']);
			foreach ($raw_ids as $rid) {
				$rid = trim($rid);
				if (!empty($rid) && is_numeric($rid)) {
					$id_req_arr[] = $rid;
				}
			}
			if (empty($id_req_arr) && !empty($post['dt'])) {
				foreach ($post['dt'] as $dt_item) {
					if (!empty($dt_item['id_payment'])) {
						$id_req_arr[] = $dt_item['id_payment'];
					}
				}
			}
			if (empty($id_req_arr)) {
				throw new Exception('Data request payment tidak ditemukan.');
			}

			$req_payment_rows = $this->db->where_in('id', $id_req_arr)->get('request_payment')->result();
			if (empty($req_payment_rows)) {
				throw new Exception('Data request payment tidak valid.');
			}
			$first_req    = $req_payment_rows[0];
			$tipe_payment = $first_req->tipe; // ros_bm / ros_ls / ros_insurance / ros_other_cost

			$tgl_bayar    = $post['tgl_bayar'];
			$bank_coa     = $post['bank'];
			$mata_uang    = $post['mata_uang'] ?? 'IDR';
			$kurs         = (float) str_replace(',', '', $post['kurs_payment'] ?? '1');
			if ($kurs <= 0) $kurs = 1;
			$payment_bank = (float) str_replace(',', '', $post['payment_bank'] ?? '0');
			$bank_charge  = (float) str_replace(',', '', $post['bank_charge'] ?? '0');
			$keterangan   = $post['keterangan_pembayaran'] ?? '';
			$id_supplier  = $post['supplier_input'] ?? '';
			$nm_supplier  = $post['nm_supplier_input'] ?? '';

			$nilai_bank_idr = $payment_bank * $kurs;

			$db_acc = $this->load->database('accounting', TRUE);
			$kode_bank = '';
			$no_doc_payment = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $tgl_bayar);

			// Subtotal = SUM nominal tr_ros_payment dari item terpilih (tanpa PPN/PPH)
			$subtotal        = 0;
			$ros_payment_ids = [];
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$id_rospay = $detail['ids'] ?? '';
					if (!empty($id_rospay)) {
						$rp = $this->db->get_where('tr_ros_payment', ['id' => $id_rospay])->row();
						if ($rp) {
							$subtotal          += (float) $rp->nominal;
							$ros_payment_ids[]  = $rp->id;
						}
					}
				}
			}

			// Grand Total = subtotal + bank_charge (tanpa PPN/PPH)
			$grand_total = $subtotal + $bank_charge;

			$nm_coa_bank = '';
			$row_bank = $db_acc->get_where('coa_master', ['no_perkiraan' => $bank_coa])->row();
			if (!empty($row_bank)) $nm_coa_bank = $row_bank->nama;

			$id_payment_val = $first_req->no_doc;

			// INSERT header payment_approve
			$this->db->insert('payment_approve', [
				'no_doc'                => $no_doc_payment,
				'tipe'                  => $tipe_payment,
				'tgl_bayar'             => $tgl_bayar,
				'tgl_doc'               => $tgl_bayar,
				'pay_by'                => $this->auth->user_name(),
				'pay_on'                => date('Y-m-d H:i:s'),
				'created_by'            => $this->auth->user_name(),
				'created_on'            => date('Y-m-d H:i:s'),
				'supplier'              => $id_supplier,
				'keterangan_pembayaran' => $keterangan,
				'coa_bank'              => $bank_coa,
				'nm_coa_bank'           => $nm_coa_bank,
				'mata_uang'             => $mata_uang,
				'currency'              => $mata_uang,
				'kurs_payment'          => $kurs,
				'payment_bank'          => $payment_bank,
				'total_payment'         => $subtotal,
				'total_pph'             => 0,
				'total_ppn'             => 0,
				'grand_total_payment'   => $grand_total,
				'tipe_pph'              => '',
				'id_supplier'           => $id_supplier,
				'nm_supplier'           => $nm_supplier,
				'link_doc'              => '',
				'bank_charge'           => $bank_charge,
				'nominal_asli'          => $payment_bank,
				'nominal_asli_idr'      => $nilai_bank_idr,
				'tagihan_idr'           => $subtotal,
				'dibayar_idr'           => (int) round($nilai_bank_idr),
				'selisih_kurs_idr'      => 0,
				'selisih'               => 0,
				'id_payment'            => $id_payment_val,
				'gl_hutang_dagang'      => (int) round($subtotal),
				'gl_selisih_kurs'       => 0,
				'gl_total_payment'      => (int)round($subtotal),
			]);

			// INSERT payment_approve_details per item ros_*
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$id_rospay = $detail['ids'] ?? '';
					$nominal   = 0;
					$ket_row   = '';
					if (!empty($id_rospay)) {
						$rp = $this->db->get_where('tr_ros_payment', ['id' => $id_rospay])->row();
						if ($rp) {
							$nominal = (float) $rp->nominal;
							$ket_row = $rp->keterangan;
						}
					}

					$file_upload_row = $this->upload_doc_per_row($detail['id_payment'] ?? '');

					$this->db->insert('payment_approve_details', [
						'payment_id'         => $no_doc_payment,
						'no_doc'             => $detail['no_surat'] ?? '',
						'no_po'              => $detail['no_doc'] ?? '',
						'deskripsi'          => 'Payment ' . ($detail['no_surat'] ?? $detail['no_doc'] ?? '') . ($ket_row ? ' - ' . $ket_row : ''),
						'nilai_invoice_kurs' => $nominal,
						'nilai_invoice_idr'  => $nominal,
						'total_bayar_kurs'   => $payment_bank,
						'total_bayar_idr'    => (float) str_replace(',', '', $post['nilai_bank_idr'] ?? '0'),
						'total'              => $nominal,
						'nilai_invoice'      => $nominal,
						'nilai_ppn'          => 0,
						'nilai_pph'          => 0,
						'tipe_pph'           => '',
						'kurs_invoice'       => $kurs,
						'id_receive_invoice' => null,
						'currency'           => $mata_uang,
						'file_original_name' => $file_upload_row['file_original_name'],
						'file_hash_name'     => $file_upload_row['file_hash_name'],
						'created_by'         => $this->auth->user_name(),
						'created_on'         => date('Y-m-d H:i:s'),
					]);
				}
			}

			// Update tr_ros_payment -> lunas
			if (!empty($ros_payment_ids)) {
				$this->db->where_in('id', $ros_payment_ids);
				$this->db->update('tr_ros_payment', [
					'status'      => 'lunas',
					'modified_by' => $this->auth->user_id(),
					'modified_on' => date('Y-m-d H:i:s'),
				]);
			}

			// Update request_payment -> finish
			$this->db->where_in('id', $id_req_arr);
			$this->db->update('request_payment', ['status' => 'finish']);

			// Hapus tr_choosed_payment untuk item yang sudah diproses
			$this->db->where('id_user', $this->auth->user_id());
			$this->db->where_in('id_payment', $id_req_arr);
			$this->db->delete('tr_choosed_payment');

			// ── Auto close ROS: jika SEMUA tr_ros_payment untuk ROS ini sudah lunas ──
			$ros_header_ids = [];
			if (!empty($ros_payment_ids)) {
				$rows_ros = $this->db->select('DISTINCT id_ros_header', false)
					->where_in('id', $ros_payment_ids)
					->get('tr_ros_payment')
					->result();
				foreach ($rows_ros as $r) {
					$ros_header_ids[] = $r->id_ros_header;
				}
			}
			foreach (array_unique($ros_header_ids) as $id_ros_header) {
				$belum_lunas = $this->db
					->where('id_ros_header', $id_ros_header)
					->where('status !=', 'lunas')
					->count_all_results('tr_ros_payment');

				if ($belum_lunas == 0) {
					$this->db->update('tr_ros_header', [
						'status_payment' => 'close',
						'modified_by'    => $this->auth->user_id(),
						'modified_on'    => date('Y-m-d H:i:s'),
					], ['id' => $id_ros_header]);
				}
			}

			// ── Generate Jurnal via ms_jurnal_mapping ──
			$row_lengkap = $this->db->get_where('payment_approve', ['no_doc' => $no_doc_payment])->row_array();
			if (empty($row_lengkap)) {
				throw new Exception('Gagal mengambil data payment_approve yang baru disimpan (no_doc: ' . $no_doc_payment . ').');
			}

			$this->load->model('gl_interface/Gl_interface_model');

			$suffix_map = [
				'ros_bm'         => 'save_payment_ros_bm',
				'ros_ls'         => 'save_payment_ros_ls',
				'ros_insurance'  => 'save_payment_ros_insurance',
				'ros_other_cost' => 'save_payment_ros_other_cost',
			];
			$action_jurnal = isset($suffix_map[$tipe_payment]) ? $suffix_map[$tipe_payment] : 'save_payment_ros_bm';

			$mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'Pembayaran Material', 'action' => $action_jurnal])->row();
			if (!$mapping) {
				throw new Exception('Template jurnal untuk ' . $action_jurnal . ' belum dikonfigurasi di Master Jurnal Mapping.');
			}
			$kode_jurnal = $mapping->kode_master_jurnal;
			$id_gl = $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $row_lengkap);

			if ($id_gl === false) {
				throw new Exception('Gagal generate jurnal: template ' . $kode_jurnal . ' tidak ditemukan/kosong.');
			}

			$this->db->trans_commit();

			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 1,
				'pesan'  => 'Payment Document Import berhasil disimpan.'
			]);
			exit;
		} catch (Exception $e) {
			$this->db->trans_rollback();

			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 0,
				'pesan'  => 'Gagal: ' . $e->getMessage()
			]);
			exit;
		}
	}

	/**
	 * Save Payment untuk tipe Invoice Local
	 * Logika penyimpanan identik dengan save_payment_po,
	 * hanya kode jurnal yang berbeda (BUK003).
	 */
	public function save_payment_local()
	{
		// Mulai buffer baru — supaya semua Notice/Warning/Deprecated yang mungkin
		// muncul di tengah proses TERTANGKAP di buffer ini, bukan langsung ke output.
		ob_start();

		$post = $this->input->post();

		$this->db->trans_begin();

		try {
			// id_payment dari form = ID request_payment (bisa lebih dari 1, dipisah koma)
			$id_req_arr = [];
			$raw_ids = explode(',', $post['id_payment']);
			foreach ($raw_ids as $rid) {
				$rid = trim($rid);
				if (!empty($rid) && is_numeric($rid)) {
					$id_req_arr[] = $rid;
				}
			}
			// Fallback: ambil dari dt[n][id_payment]
			if (empty($id_req_arr) && !empty($post['dt'])) {
				foreach ($post['dt'] as $dt_item) {
					if (!empty($dt_item['id_payment'])) {
						$id_req_arr[] = $dt_item['id_payment'];
					}
				}
			}
			if (empty($id_req_arr)) {
				throw new Exception('Data request payment tidak ditemukan.');
			}

			// Ambil data request_payment untuk referensi
			$req_payment_rows = $this->db->where_in('id', $id_req_arr)->get('request_payment')->result();
			if (empty($req_payment_rows)) {
				throw new Exception('Data request payment tidak valid.');
			}
			$first_req = $req_payment_rows[0];
			$tipe_payment = $first_req->tipe; // invoice_local

			$tgl_bayar      = $post['tgl_bayar'];
			$bank_coa       = $post['bank'];
			$mata_uang      = $post['mata_uang'];
			$kurs           = (float)str_replace(',', '', $post['kurs_payment'] ?? '1');
			if ($kurs <= 0) $kurs = 1;
			$payment_bank   = (float)str_replace(',', '', $post['payment_bank'] ?? '0'); // Nilai Bank (foreign currency)
			$bank_charge    = (float)str_replace(',', '', $post['bank_charge'] ?? '0');
			$keterangan     = $post['keterangan_pembayaran'] ?? '';
			$id_supplier    = $post['supplier_input'] ?? '';
			$nm_supplier    = $post['nm_supplier_input'] ?? '';

			// Nilai Bank IDR = Nilai Bank × Kurs Payment
			$nilai_bank_idr = $payment_bank * $kurs;

			// Generate no_doc (ID payment_approve) dengan format BK-
			$db_acc = $this->load->database('accounting', TRUE);
			$kode_bank = '';
			$no_doc_payment = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $tgl_bayar);

			// Hitung total_pph, total_ppn, dan subtotal dari detail
			$total_pph = 0;
			$total_ppn = 0;
			$subtotal = 0;
			$tipe_pph_val = '';

			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$det_pph = (float)str_replace(',', '', $detail['nilai_pph'] ?? '0');
					$det_ppn = (float)str_replace(',', '', $detail['nilai_ppn'] ?? '0');

					// Subtotal per baris = jumlah asli PO (dt[n][jumlah]) x kurs form
					$det_jumlah_asli = (float)str_replace(',', '', $detail['jumlah'] ?? '0');
					$det_jumlah_idr  = $det_jumlah_asli * $kurs;

					$total_pph += $det_pph;
					$total_ppn += $det_ppn;
					$subtotal += $det_jumlah_idr;

					if (!empty($detail['tipe_pph'])) {
						$tipe_pph_val = ($detail['tipe_pph'] == 1) ? 'PPH 23' : 'PPH 22';
					}
				}
			}

			// Grand Total Payment = Subtotal + PPN - PPH + Bank Charge
			$grand_total = $subtotal + $total_ppn - $total_pph + $bank_charge;

			// Selisih Kurs: berdasarkan kurs_receive_invoice vs kurs_payment
			$selisih_kurs_idr = 0;
			$nominal_kurs_receive = 0; // jumlah_rupiah dari tr_receive_invoice (nilai IDR saat receive)
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$id_ri = $detail['ids'] ?? '';

					if (!empty($id_ri)) {
						$ri = $this->db->get_where('tr_receive_invoice', ['id' => $id_ri])->row();
						if ($ri) {
							$nominal_kurs_receive += (float)($ri->jumlah_rupiah ?? 0);
						}
					}
				}
				// Selisih Kurs = Nilai Bank IDR - Nominal dari tr_receive_invoice
				$selisih_kurs_idr = $nilai_bank_idr - $nominal_kurs_receive;
			}

			// Nama COA Bank
			$nm_coa_bank = '';
			$row_bank = $db_acc->get_where('coa_master', ['no_perkiraan' => $bank_coa])->row();
			if (!empty($row_bank)) $nm_coa_bank = $row_bank->nama;

			// id_payment = no_doc dari request_payment (referensi ke PO)
			$id_payment_val = $first_req->no_doc;

			// INSERT ke payment_approve (header)
			// NB: 'link_doc' di header TIDAK dipakai lagi — lampiran sekarang
			// per baris PO, disimpan di payment_approve_details.link_doc
			$this->db->insert('payment_approve', [
				'no_doc'                => $no_doc_payment,
				'tipe'                  => $tipe_payment,
				'tgl_bayar'             => $tgl_bayar,
				'tgl_doc'               => $tgl_bayar,
				'pay_by'                => $this->auth->user_name(),
				'pay_on'                => date('Y-m-d H:i:s'),
				'created_by'            => $this->auth->user_name(),
				'created_on'            => date('Y-m-d H:i:s'),
				'supplier'              => $id_supplier,
				'keterangan_pembayaran' => $keterangan,
				'coa_bank'              => $bank_coa,
				'nm_coa_bank'           => $nm_coa_bank,
				'mata_uang'             => $mata_uang,
				'currency'              => $mata_uang,
				'kurs_payment'          => $kurs,
				'payment_bank'          => $payment_bank,
				'total_payment'         => $subtotal,
				'total_pph'             => $total_pph,
				'total_ppn'             => $total_ppn,
				'grand_total_payment'   => $grand_total,
				'tipe_pph'              => $tipe_pph_val,
				'id_supplier'           => $id_supplier,
				'nm_supplier'           => $nm_supplier,
				'link_doc'              => '',
				'bank_charge'           => $bank_charge,
				'nominal_asli'          => $payment_bank,
				'nominal_asli_idr'      => $nilai_bank_idr,
				'tagihan_idr'           => $nominal_kurs_receive,
				'dibayar_idr'           => (int)round($nilai_bank_idr),
				'selisih_kurs_idr'      => $selisih_kurs_idr,
				'selisih'               => 0,
				'id_payment'            => $id_payment_val,
				'gl_hutang_dagang'      => (int)round($nominal_kurs_receive),
				'gl_selisih_kurs'       => (int)round($selisih_kurs_idr),
				'gl_total_payment'      => (int)round($subtotal),
			]);

			// INSERT payment_approve_details per PO
			if (!empty($post['dt'])) {
				$total_bayar_kurs = $payment_bank; // sudah di-parse dari $post['payment_bank'] di atas
				$total_bayar_idr  = (float)str_replace(',', '', $post['nilai_bank_idr'] ?? '0');

				foreach ($post['dt'] as $detail) {
					$det_ppn = (float)str_replace(',', '', $detail['nilai_ppn'] ?? '0');
					$det_pph = (float)str_replace(',', '', $detail['nilai_pph'] ?? '0');
					$det_kurs_invoice = (float)str_replace(',', '', $detail['kurs_invoice'] ?? '0');

					// Ambil nilai_invoice & jumlah_rupiah dari tr_receive_invoice
					$id_ri = $detail['ids'] ?? '';
					$nilai_invoice_kurs = 0;
					$nilai_invoice_idr  = 0;
					if (!empty($id_ri)) {
						$ri = $this->db->get_where('tr_receive_invoice', ['id' => $id_ri])->row();
						if ($ri) {
							$nilai_invoice_kurs = $ri->nilai_invoice ?? 0;
							$nilai_invoice_idr  = $ri->jumlah_rupiah ?? 0;
						}
					}

					// BARU: upload lampiran per baris (opsional), key = id_payment baris ini
					$file_upload_row = $this->upload_doc_per_row($detail['id_payment'] ?? '');

					$this->db->insert('payment_approve_details', [
						'payment_id'         => $no_doc_payment,
						'no_doc'             => $detail['no_surat'] ?? '',
						'no_po'              => $detail['no_doc'] ?? '',
						'deskripsi'          => 'Payment ' . ($detail['no_surat'] ?? $detail['no_doc'] ?? ''),

						'nilai_invoice_kurs' => $nilai_invoice_kurs,
						'nilai_invoice_idr'  => $nilai_invoice_idr,
						'total_bayar_kurs'   => $total_bayar_kurs,
						'total_bayar_idr'    => $total_bayar_idr,

						'total'              => $nilai_invoice_idr,
						'nilai_invoice'      => $nilai_invoice_idr,

						'nilai_ppn'          => $det_ppn,
						'nilai_pph'          => $det_pph,
						'tipe_pph'           => (($detail['tipe_pph'] ?? null) == 1) ? 'PPH 23' : 'PPH 22',
						'kurs_invoice'       => $det_kurs_invoice,
						'id_receive_invoice' => $detail['ids'] ?? null,
						'currency'           => $mata_uang,
						'file_original_name' => $file_upload_row['file_original_name'],
						'file_hash_name'     => $file_upload_row['file_hash_name'],
						'created_by'         => $this->auth->user_name(),
						'created_on'         => date('Y-m-d H:i:s'),
					]);
				}
			}

			// Update status tr_receive_invoice menjadi 'payment'
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					if (!empty($detail['ids'])) {
						$this->db->update('tr_receive_invoice', ['status' => 'payment'], ['id' => $detail['ids']]);
					}
				}
			}

			$this->db->where_in('id', $id_req_arr);
			$this->db->update('request_payment', ['status' => 'finish']);

			// Hapus tr_choosed_payment untuk item yang sudah diproses
			$this->db->where('id_user', $this->auth->user_id());
			$this->db->where_in('id_payment', $id_req_arr);
			$this->db->delete('tr_choosed_payment');

			$row_lengkap = $this->db->get_where('payment_approve', ['no_doc' => $no_doc_payment])->row_array();

			if (empty($row_lengkap)) {
				throw new Exception('Gagal mengambil data payment_approve yang baru disimpan (no_doc: ' . $no_doc_payment . ').');
			}

			$this->load->model('gl_interface/Gl_interface_model');

			// Invoice Local menggunakan template jurnal BUK003
			$mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'Pembayaran Material', 'action' => 'save_payment_local'])->row();
			$kode_jurnal = $mapping ? $mapping->kode_master_jurnal : 'BUK003'; // fallback
			$id_gl = $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $row_lengkap);

			if ($id_gl === false) {
				throw new Exception('Gagal generate jurnal: template ' . $kode_jurnal . ' tidak ditemukan/kosong');
			}

			$this->db->trans_commit();

			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 1,
				'pesan'  => 'Payment berhasil disimpan.'
			]);
			exit;
		} catch (Exception $e) {
			$this->db->trans_rollback();

			ob_end_clean();
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 0,
				'pesan'  => 'Gagal: ' . $e->getMessage()
			]);
			exit;
		}
	}

	/**
	 * Save Payment untuk tipe Invoice Import
	 * - Insert ke tr_payment_paid
	 * - Update payment_approve (status=2)
	 * - Insert ke gl_interface + gl_interface_detail (tipe BUK)
	 * - COA: 2101-01-01 (DEBET), Bank (KREDIT), 7201-01-07 (Selisih)
	 */
	// public function save_payment_import()
	// {
	// 	if (ob_get_level()) ob_end_clean();
	// 	$post = $this->input->post();

	// 	$this->db->trans_begin();

	// 	try {
	// 		$id_payment_arr = explode(',', $post['id_payment']);
	// 		$tgl_bayar      = $post['tgl_bayar'];
	// 		$bank_coa       = $post['bank'];
	// 		$mata_uang      = $post['mata_uang'];
	// 		$kurs           = (float)str_replace(',', '', $post['kurs_payment'] ?? '1');
	// 		if ($kurs <= 0) $kurs = 1;
	// 		$payment_bank   = (float)str_replace(',', '', $post['payment_bank'] ?? '0');
	// 		$bank_charge    = (float)str_replace(',', '', $post['bank_charge'] ?? '0');
	// 		$keterangan     = $post['keterangan_pembayaran'] ?? '';
	// 		$id_supplier    = $post['supplier_input'] ?? '';
	// 		$nm_supplier    = $post['nm_supplier_input'] ?? '';

	// 		// Upload dokumen
	// 		$filenames = '';
	// 		if (!empty($_FILES['upload_doc']['name'])) {
	// 			$config_upload = [
	// 				'upload_path'   => FCPATH . 'assets/expense/',
	// 				'allowed_types' => '*',
	// 				'remove_spaces' => TRUE,
	// 				'encrypt_name'  => TRUE
	// 			];
	// 			$this->load->library('upload', $config_upload);
	// 			$this->upload->initialize($config_upload);
	// 			if ($this->upload->do_upload('upload_doc')) {
	// 				$filenames = $this->upload->data('file_name');
	// 			}
	// 		}

	// 		// Generate ID payment paid
	// 		$db_acc = $this->load->database('accounting', TRUE);
	// 		$kode_bank = '';
	// 		$id_payment_paid = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $tgl_bayar);

	// 		// Ambil nominal hutang dagang dari gl_interface_detail (kredit 2101-01-01, jenis_transaksi = 'invoice import')
	// 		// Cari berdasarkan payment_approve.ids (= id_receive di tr_receive_invoice_imp_lok)
	// 		$get_payment = $this->db->select('ids')->where_in('id', $id_payment_arr)->get('payment_approve')->row();
	// 		$id_receive = (!empty($get_payment)) ? $get_payment->ids : '';

	// 		// Ambil id_ros dari tr_receive_invoice_imp_lok
	// 		$get_receive = $this->db->get_where('tr_receive_invoice_imp_lok', ['id' => $id_receive])->row();
	// 		$id_ros = (!empty($get_receive)) ? $get_receive->id_ros : '';

	// 		// Ambil kredit 2101-01-01 dari gl_interface_detail berdasarkan id_ros dan jenis_transaksi 'invoice import'
	// 		$get_hutang = $this->db
	// 			->select('gd.kredit')
	// 			->from('gl_interface_detail gd')
	// 			->join('gl_interface g', 'g.id = gd.id_gl_interface')
	// 			->where('gd.no_request', $id_ros)
	// 			->where('gd.no_perkiraan', '2101-01-01')
	// 			->where('g.jenis_transaksi', 'invoice import')
	// 			->get()
	// 			->row();

	// 		$nominal_hutang = (!empty($get_hutang)) ? (int)$get_hutang->kredit : 0;
	// 		$nominal_bank   = (int)round($payment_bank);
	// 		$selisih_kurs   = $nominal_hutang - $nominal_bank;

	// 		$selisih_total = $nominal_hutang - $nominal_bank;

	// 		// 1. Insert ke tr_payment_paid
	// 		$this->db->insert('tr_payment_paid', [
	// 			'id'                    => $id_payment_paid,
	// 			'bank_charge'           => $bank_charge,
	// 			'created_by'            => $this->auth->user_id(),
	// 			'created_on'            => date('Y-m-d H:i:s'),
	// 			'tgl_bayar'             => $tgl_bayar,
	// 			'coa_bank'              => $bank_coa,
	// 			'supplier'              => $id_supplier,
	// 			'nm_supplier'           => $nm_supplier,
	// 			'mata_uang'             => $mata_uang,
	// 			'kurs_payment'          => $kurs,
	// 			'payment_bank'          => $payment_bank,
	// 			'payment_bank_charge'   => $bank_charge,
	// 			'total_doc'             => $nominal_hutang,
	// 			'selisih_total'         => $selisih_total,
	// 			'keterangan_pembayaran' => $keterangan,
	// 			'link_doc'              => $filenames,
	// 		]);

	// 		// 2. Update payment_approve
	// 		$nm_coa_bank = '';
	// 		$row_bank = $db_acc->get_where('coa_master', ['no_perkiraan' => $bank_coa])->row();
	// 		if (!empty($row_bank)) $nm_coa_bank = $row_bank->nama;

	// 		$this->db->where_in('id', $id_payment_arr);
	// 		$this->db->update('payment_approve', [
	// 			'id_payment'            => $id_payment_paid,
	// 			'status'                => 2,
	// 			'tgl_bayar'             => $tgl_bayar,
	// 			'pay_by'                => $this->auth->user_name(),
	// 			'pay_on'                => date('Y-m-d H:i:s'),
	// 			'supplier'              => $id_supplier,
	// 			'keterangan_pembayaran' => $keterangan,
	// 			'coa_bank'              => $bank_coa,
	// 			'nm_coa_bank'           => $nm_coa_bank,
	// 			'mata_uang'             => $mata_uang,
	// 			'kurs_payment'          => $kurs,
	// 			'payment_bank'          => $payment_bank,
	// 			'total_payment'         => $nominal_hutang,
	// 			'selisih'               => $selisih_total,
	// 			'id_supplier'           => $id_supplier,
	// 			'nm_supplier'           => $nm_supplier,
	// 			'link_doc'              => $filenames,
	// 			'tagihan_idr'           => $nominal_hutang,
	// 			'dibayar_idr'           => $nominal_bank,
	// 			'selisih_kurs_idr'      => $selisih_total,
	// 		]);

	// 		// 3. Generate GL Interface (tipe BUK)
	// 		$coa_hutang  = '2101-01-01';
	// 		$coa_selisih = '7201-01-07';

	// 		$nm_hutang  = 'Hutang Dagang';
	// 		$nm_bank_name = $bank_coa;
	// 		$nm_selisih = 'Selisih Kurs';

	// 		$row = $db_acc->get_where('coa_master', ['no_perkiraan' => $coa_hutang])->row();
	// 		if (!empty($row)) $nm_hutang = $row->nama;
	// 		if (!empty($row_bank)) $nm_bank_name = $row_bank->nama;
	// 		$row = $db_acc->get_where('coa_master', ['no_perkiraan' => $coa_selisih])->row();
	// 		if (!empty($row)) $nm_selisih = $row->nama;

	// 		$keterangan_jv = "Pembayaran Import - " . $id_payment_paid . " | " . $keterangan;

	// 		// Generate nomor JV
	// 		$cabang = $db_acc->query("SELECT nomorJC FROM pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1")->row();
	// 		$nomor_urut = (!empty($cabang)) ? (int)$cabang->nomorJC + 1 : 1;
	// 		$nomor_jv = '101-ABK' . date('ym') . $nomor_urut;
	// 		$db_acc->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'");

	// 		// Insert header gl_interface
	// 		$this->db->insert('gl_interface', [
	// 			'nomor'           => $nomor_jv,
	// 			'tgl'             => $tgl_bayar,
	// 			'bulan'           => date('m', strtotime($tgl_bayar)),
	// 			'tahun'           => date('Y', strtotime($tgl_bayar)),
	// 			'kdcab'           => '101',
	// 			'jenis'           => 'BUK',
	// 			'keterangan'      => $keterangan_jv,
	// 			'jenis_transaksi' => 'payment import',
	// 			'status'          => 'pending',
	// 			'user_id'         => $this->auth->user_id(),
	// 			'memo'            => json_encode([
	// 				'id_payment_paid' => $id_payment_paid,
	// 				'id_ros'          => $id_ros,
	// 				'id_supplier'     => $id_supplier,
	// 				'nm_supplier'     => $nm_supplier,
	// 				'mata_uang'       => $mata_uang,
	// 				'kurs'            => $kurs,
	// 			]),
	// 		]);
	// 		$id_gl = $this->db->insert_id();
	// 		$created_on = date('Y-m-d H:i:s');

	// 		// Detail 1: DEBET - Hutang Dagang (2101-01-01)
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl,
	// 			'no_batch'        => $nomor_jv,
	// 			'tipe'            => 'BUK',
	// 			'tanggal'         => $tgl_bayar,
	// 			'no_perkiraan'    => $coa_hutang,
	// 			'keterangan'      => $nm_hutang . ' | ' . $keterangan_jv,
	// 			'no_reff'         => $id_payment_paid,
	// 			'no_request'      => $id_ros,
	// 			'debet'           => $nominal_hutang,
	// 			'kredit'          => 0,
	// 			'created_at'      => $created_on,
	// 		]);

	// 		// Detail 2: KREDIT - Bank (inputan user)
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl,
	// 			'no_batch'        => $nomor_jv,
	// 			'tipe'            => 'BUK',
	// 			'tanggal'         => $tgl_bayar,
	// 			'no_perkiraan'    => $bank_coa,
	// 			'keterangan'      => $nm_bank_name . ' | ' . $keterangan_jv,
	// 			'no_reff'         => $id_payment_paid,
	// 			'no_request'      => $id_ros,
	// 			'debet'           => 0,
	// 			'kredit'          => $nominal_bank,
	// 			'created_at'      => $created_on,
	// 		]);

	// 		// Detail 3: Selisih Kurs (7201-01-07)
	// 		$this->db->insert('gl_interface_detail', [
	// 			'id_gl_interface' => $id_gl,
	// 			'no_batch'        => $nomor_jv,
	// 			'tipe'            => 'BUK',
	// 			'tanggal'         => $tgl_bayar,
	// 			'no_perkiraan'    => $coa_selisih,
	// 			'keterangan'      => $nm_selisih . ' | ' . $keterangan_jv,
	// 			'no_reff'         => $id_payment_paid,
	// 			'no_request'      => $id_ros,
	// 			'debet'           => ($selisih_kurs < 0) ? abs($selisih_kurs) : 0,
	// 			'kredit'          => ($selisih_kurs > 0) ? $selisih_kurs : 0,
	// 			'created_at'      => $created_on,
	// 		]);

	// 		$this->db->trans_commit();

	// 		echo json_encode([
	// 			'status' => 1,
	// 			'pesan'  => 'Payment Import berhasil disimpan.'
	// 		]);
	// 	} catch (Exception $e) {
	// 		$this->db->trans_rollback();
	// 		echo json_encode([
	// 			'status' => 0,
	// 			'pesan'  => 'Gagal: ' . $e->getMessage()
	// 		]);
	// 	}
	// }

	private function upload_doc_per_row($file_key)
	{
		$empty_result = [
			'file_original_name' => '',
			'file_hash_name'     => '',
		];

		if (empty($file_key)) {
			return $empty_result;
		}

		// Tidak ada file yang diupload untuk baris ini -> opsional, skip saja
		if (empty($_FILES['upload_doc']['name'][$file_key])) {
			return $empty_result;
		}

		// Susun ulang jadi struktur $_FILES tunggal supaya bisa dipakai
		// library upload CodeIgniter (yang defaultnya cuma baca 1 file per key)
		$_FILES['upload_doc_single'] = [
			'name'     => $_FILES['upload_doc']['name'][$file_key],
			'type'     => $_FILES['upload_doc']['type'][$file_key],
			'tmp_name' => $_FILES['upload_doc']['tmp_name'][$file_key],
			'error'    => $_FILES['upload_doc']['error'][$file_key],
			'size'     => $_FILES['upload_doc']['size'][$file_key],
		];

		$config_upload = [
			'upload_path'   => FCPATH . 'uploads/payment_invoice/',
			'allowed_types' => '*',
			'remove_spaces' => TRUE,
			'encrypt_name'  => TRUE
		];
		$this->load->library('upload', $config_upload);
		$this->upload->initialize($config_upload);

		if ($this->upload->do_upload('upload_doc_single')) {
			$upload_data = $this->upload->data();
			return [
				'file_original_name' => $upload_data['client_name'] ?? $_FILES['upload_doc']['name'][$file_key],
				'file_hash_name'     => $upload_data['file_name'],
			];
		}

		// Gagal upload (misal tipe file aneh) -> jangan gagalkan seluruh transaksi,
		// cukup dianggap tidak ada lampiran untuk baris ini
		return $empty_result;
	}

	public function save_payment_import()
	{
		if (ob_get_level()) ob_end_clean();
		$post = $this->input->post();

		$this->db->trans_begin();

		try {
			// id_payment dari form = ID request_payment (bisa lebih dari 1, dipisah koma)
			$id_req_arr = [];
			$raw_ids = explode(',', $post['id_payment']);
			foreach ($raw_ids as $rid) {
				$rid = trim($rid);
				if (!empty($rid) && is_numeric($rid)) {
					$id_req_arr[] = $rid;
				}
			}
			// Fallback: ambil dari dt[n][id_payment]
			if (empty($id_req_arr) && !empty($post['dt'])) {
				foreach ($post['dt'] as $dt_item) {
					if (!empty($dt_item['id_payment'])) {
						$id_req_arr[] = $dt_item['id_payment'];
					}
				}
			}
			if (empty($id_req_arr)) {
				throw new Exception('Data request payment tidak ditemukan.');
			}

			// Ambil data request_payment untuk referensi
			$req_payment_rows = $this->db->where_in('id', $id_req_arr)->get('request_payment')->result();
			if (empty($req_payment_rows)) {
				throw new Exception('Data request payment tidak valid.');
			}
			$first_req = $req_payment_rows[0];

			$tgl_bayar      = $post['tgl_bayar'];
			$bank_coa       = $post['bank'];
			$mata_uang      = $post['mata_uang'];
			$kurs           = (float)str_replace(',', '', $post['kurs_payment'] ?? '1');
			if ($kurs <= 0) $kurs = 1;
			$payment_bank   = (float)str_replace(',', '', $post['payment_bank'] ?? '0'); // Nilai Bank (foreign currency)
			$bank_charge    = (float)str_replace(',', '', $post['bank_charge'] ?? '0');
			$keterangan     = $post['keterangan_pembayaran'] ?? '';
			$id_supplier    = $post['supplier_input'] ?? '';
			$nm_supplier    = $post['nm_supplier_input'] ?? '';

			// Nilai Bank IDR = Nilai Bank × Kurs Payment
			$nilai_bank_idr = $payment_bank * $kurs;

			// Generate no_doc (ID payment_approve) dengan format BK-
			$db_acc = $this->load->database('accounting', TRUE);
			$kode_bank = '';
			$no_doc_payment = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $tgl_bayar);

			// Hitung total_pph, total_ppn, dan subtotal dari detail
			$total_pph = 0;
			$total_ppn = 0;
			$subtotal = 0;
			$tipe_pph_val = '';

			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$det_pph = (float)str_replace(',', '', $detail['nilai_pph'] ?? '0');
					$det_ppn = (float)str_replace(',', '', $detail['nilai_ppn'] ?? '0');
					$det_jumlah = (float)str_replace(',', '', $detail['jumlah'] ?? '0');

					$total_pph += $det_pph;
					$total_ppn += $det_ppn;
					$subtotal += ($det_jumlah * $kurs);

					if (!empty($detail['tipe_pph'])) {
						$tipe_pph_val = ($detail['tipe_pph'] == 1) ? 'PPH 23' : 'PPH 22';
					}
				}
			}

			// Grand Total Payment = Subtotal + PPN - PPH + Bank Charge
			$grand_total = $subtotal + $total_ppn - $total_pph + $bank_charge;

			// Selisih Kurs: ambil jumlah_rupiah dari tr_receive_invoice
			$selisih_kurs_idr = 0;
			$nominal_kurs_receive = 0;
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$id_ri = $detail['ids'] ?? '';
					if (!empty($id_ri)) {
						$ri = $this->db->get_where('tr_receive_invoice', ['id' => $id_ri])->row();
						if ($ri) {
							$nominal_kurs_receive += (float)($ri->jumlah_rupiah ?? 0);
						}
					}
				}
				$selisih_kurs_idr = $nilai_bank_idr - $nominal_kurs_receive;
			}

			// Nama COA Bank
			$nm_coa_bank = '';
			$row_bank = $db_acc->get_where('coa_master', ['no_perkiraan' => $bank_coa])->row();
			if (!empty($row_bank)) $nm_coa_bank = $row_bank->nama;

			// id_payment = no_doc dari request_payment (referensi ke PO)
			$id_payment_val = $first_req->no_doc;

			// INSERT ke payment_approve (header)
			// NB: 'link_doc' di header TIDAK dipakai lagi — lampiran sekarang
			// per baris PO, disimpan di payment_approve_details.link_doc
			$this->db->insert('payment_approve', [
				'no_doc'                => $no_doc_payment,
				'tipe'                  => 'invoice_import',
				'tgl_bayar'             => $tgl_bayar,
				'tgl_doc'               => $tgl_bayar,
				'pay_by'                => $this->auth->user_name(),
				'pay_on'                => date('Y-m-d H:i:s'),
				'created_by'            => $this->auth->user_name(),
				'created_on'            => date('Y-m-d H:i:s'),
				'supplier'              => $id_supplier,
				'keterangan_pembayaran' => $keterangan,
				'coa_bank'              => $bank_coa,
				'nm_coa_bank'           => $nm_coa_bank,
				'mata_uang'             => $mata_uang,
				'currency'              => $mata_uang,
				'kurs_payment'          => $kurs,
				'payment_bank'          => $payment_bank,
				'total_payment'         => $subtotal,
				'total_pph'             => $total_pph,
				'total_ppn'             => $total_ppn,
				'tipe_pph'              => $tipe_pph_val,
				'id_supplier'           => $id_supplier,
				'nm_supplier'           => $nm_supplier,
				'link_doc'              => '',
				'bank_charge'           => $bank_charge,
				'nominal_asli'          => $payment_bank,
				'grand_total_payment'   => $grand_total,
				'nominal_asli_idr'      => $nilai_bank_idr,
				'tagihan_idr'           => $nominal_kurs_receive,
				'dibayar_idr'           => (int)round($nilai_bank_idr),
				'selisih_kurs_idr'      => $selisih_kurs_idr,
				'selisih'               => 0,
				'id_payment'            => $id_payment_val,
				'gl_hutang_dagang'      => (int)round($nominal_kurs_receive),
				'gl_selisih_kurs'       => (int)round($selisih_kurs_idr),
				'gl_total_payment'      => (int)round($subtotal),
			]);

			// INSERT payment_approve_details per PO
			if (!empty($post['dt'])) {
				$total_bayar_kurs = $payment_bank; // sudah di-parse dari $post['payment_bank'] di atas
				$total_bayar_idr  = (float)str_replace(',', '', $post['nilai_bank_idr'] ?? '0');

				foreach ($post['dt'] as $detail) {
					$det_jumlah = (float)str_replace(',', '', $detail['jumlah'] ?? '0');
					$det_ppn = (float)str_replace(',', '', $detail['nilai_ppn'] ?? '0');
					$det_pph = (float)str_replace(',', '', $detail['nilai_pph'] ?? '0');
					$det_kurs_invoice = (float)str_replace(',', '', $detail['kurs_invoice'] ?? '0');

					// Ambil nilai_invoice & jumlah_rupiah dari tr_receive_invoice
					$id_ri = $detail['ids'] ?? '';
					$nilai_invoice_kurs = 0;
					$nilai_invoice_idr  = 0;
					if (!empty($id_ri)) {
						$ri = $this->db->get_where('tr_receive_invoice', ['id' => $id_ri])->row();
						if ($ri) {
							$nilai_invoice_kurs = $ri->nilai_invoice ?? 0;
							$nilai_invoice_idr  = $ri->jumlah_rupiah ?? 0;
						}
					}

					// BARU: upload lampiran per baris (opsional), key = id_payment baris ini
					$file_upload_row = $this->upload_doc_per_row($detail['id_payment'] ?? '');

					$this->db->insert('payment_approve_details', [
						'payment_id'         => $no_doc_payment,
						'no_doc'             => $detail['no_surat'] ?? '',
						'no_po'              => $detail['no_doc'] ?? '',
						'deskripsi'          => 'Payment Import ' . ($detail['no_surat'] ?? $detail['no_doc'] ?? ''),

						'nilai_invoice_kurs' => $nilai_invoice_kurs,
						'nilai_invoice_idr'  => $nilai_invoice_idr,
						'total_bayar_kurs'   => $total_bayar_kurs,
						'total_bayar_idr'    => $total_bayar_idr,

						'total'              => $det_jumlah,
						'nilai_invoice'      => $det_jumlah,

						'nilai_ppn'          => $det_ppn,
						'nilai_pph'          => $det_pph,
						'tipe_pph'           => ($detail['tipe_pph'] == 1) ? 'PPH 23' : 'PPH 22',
						'kurs_invoice'       => $det_kurs_invoice,
						'id_receive_invoice' => $detail['ids'] ?? null,
						'currency'           => $mata_uang,
						'file_original_name' => $file_upload_row['file_original_name'], // BARU: nama asli file (opsional)
						'file_hash_name'     => $file_upload_row['file_hash_name'],     // BARU: nama file tersimpan di server (opsional)
						'created_by'         => $this->auth->user_name(),
						'created_on'         => date('Y-m-d H:i:s'),
					]);
				}
			}

			// Update status tr_receive_invoice menjadi 'payment'
			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					if (!empty($detail['ids'])) {
						$this->db->update('tr_receive_invoice', ['status' => 'payment'], ['id' => $detail['ids']]);
					}
				}
			}

			$this->db->where_in('id', $id_req_arr);
			$this->db->update('request_payment', ['status' => 'finish']);

			// Hapus tr_choosed_payment untuk item yang sudah diproses
			$this->db->where('id_user', $this->auth->user_id());
			$this->db->where_in('id_payment', $id_req_arr);
			$this->db->delete('tr_choosed_payment');

			// Generate GL Interface via template
			$row_lengkap = $this->db->get_where('payment_approve', ['no_doc' => $no_doc_payment])->row_array();

			$this->load->model('gl_interface/Gl_interface_model');

			$mapping = $this->db->get_where('ms_jurnal_mapping', ['menu' => 'Pembayaran Material', 'action' => 'save_payment_import'])->row();
			$kode_jurnal = $mapping ? $mapping->kode_master_jurnal : 'BUK002'; // fallback
			$id_gl = $this->Gl_interface_model->generate_jurnal_dari_template($kode_jurnal, $row_lengkap);
			if ($id_gl === false) {
				throw new Exception('Gagal generate jurnal: template BUK002 tidak ditemukan/kosong');
			}

			$this->db->trans_commit();

			header('Content-Type: application/json');
			echo json_encode([
				'status' => 1,
				'pesan'  => 'Payment Import berhasil disimpan.'
			]);
			exit;
		} catch (Exception $e) {
			$this->db->trans_rollback();
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 0,
				'pesan'  => 'Gagal: ' . $e->getMessage()
			]);
			exit;
		}
	}

	public function view_payment_new($id)
	{
		// $id di sini adalah no_doc langsung (dari link)
		$get_supplier   = $this->db->get('new_supplier')->result();
		$get_mata_uang  = $this->db->get_where('mata_uang', ['deleted_by' => 0, 'activation' => 'active'])->result();

		// Ambil list bank dari coa_master
		$db_acc = $this->load->database('accounting', TRUE);
		$db_acc->select('no_perkiraan, nama');
		$db_acc->from('coa_master');
		$db_acc->where_in('no_perkiraan', [
			'1101-01-00',
			'1101-01-01',
			'1101-01-02',
			'1101-01-03',
			'1101-01-04',
			'1101-01-05',
			'1101-01-06',
			'1101-01-07',
			'1101-01-08',
			'1101-02-00',
			'1101-02-01',
			'1101-02-02',
			'1101-02-03',
			'1101-02-04',
			'1101-02-05',
			'1101-02-06',
			'1101-02-07',
			'1101-02-08',
			'1101-02-09',
			'1101-02-10',
			'1101-02-11',
			'1101-02-12',
			'1101-02-13',
			'1101-02-14',
			'1101-02-15',
			'1101-02-16',
			'1101-02-17',
			'1101-02-18',
			'1101-02-19',
			'1101-02-20',
			'1101-02-21',
			'1101-02-22',
			'1101-02-23'
		]);
		$db_acc->order_by('no_perkiraan', 'ASC');
		$get_bank = $db_acc->get()->result();

		$get_payment_header = $this->db
			->select('a.*')
			->from('payment_approve a')
			->where('a.no_doc', $id)
			->group_by('a.id_payment')
			->get()
			->row();

		$get_payment_details = [];
		if ($get_payment_header) {
			$get_payment_details = $this->db
				->get_where('payment_approve_details', ['payment_id' => $get_payment_header->no_doc])
				->result();
		}

		$bank_charge = $get_payment_header->bank_charge ?? 0;

		$data = [
			'id_payment'      => $get_payment_header->id_payment ?? '',
			'result_header'   => $get_payment_header,
			'result_payment'  => $get_payment_details,
			'list_supplier'   => $get_supplier,
			'list_bank'       => $get_bank,
			'list_mata_uang'  => $get_mata_uang,
			'bank_charge'     => $bank_charge
		];
		$this->template->set('results', $data);
		$this->template->render('view_payment_new');
	}



	//==================================================================================================================
	//================================================== PAYMENT JURNAL ================================================
	//==================================================================================================================



	public function save_payment_new_nonmaterial()
	{
		$id_req = $this->input->post("id_req");
		$payment_date = $this->input->post("payment_date");
		$bank_coa = $this->input->post("bank_coa");
		$bank_nilai = $this->input->post("bank_nilai");
		$curs = $this->input->post("curs");
		$id_supplier = $this->input->post("id_supplier");
		$curs_header = $this->input->post("curs_header");

		$biaya_admin_forex = $this->input->post("biaya_admin_forex");
		$biaya_admin = $this->input->post("biaya_admin");
		$curs_admin = $this->input->post("curs_admin");

		$biaya_admin_forex2 = $this->input->post("biaya_admin_forex2");
		$biaya_admin2 = $this->input->post("biaya_admin2");
		$curs_admin2 = $this->input->post("curs_admin2");
		$bank_coa_admin = $this->input->post("bank_coa_admin");

		$nilai_bayar_bank = $this->input->post("nilai_bayar_bank");

		$data_session	= $this->session->userdata;
		$Username 		= $this->session->userdata['ORI_User']['username'];
		$dateTime		= date('Y-m-d H:i:s');
		$alokasi_dp = $this->input->post("alokasi_dp");
		$alokasi_hutang = $this->input->post("alokasi_hutang");
		$tipetrans = $this->input->post("tipetrans");
		$this->db->trans_begin();
		$jenis_jurnal = 'BUK20';
		if ($curs_header != 'IDR') $jenis_jurnal = 'BUK21';
		try {

			$no_payment = $this->All_model->GetAutoGenerate('format_payment');
			$nomor_jurnal = $jenis_jurnal . $no_payment . rand(100, 999);

			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);

			$dataheader =  array(
				'no_payment' => $no_payment,
				'id_supplier' => $id_supplier,
				'curs_header' => $curs_header,
				'payment_date' => $payment_date,
				'bank_coa' => $bank_coa,
				'nilai_bayar_bank' => $nilai_bayar_bank,
				'curs' => $curs,
				'bank_nilai' => $bank_nilai,
				'modul' => 'PO',
				'tipe' => 'nonmaterial',
				'biaya_admin_forex' => $biaya_admin_forex,
				'biaya_admin' => $biaya_admin,
				'curs_admin' => $curs_admin,
				'biaya_admin_forex2' => $biaya_admin_forex2,
				'biaya_admin2' => $biaya_admin2,
				'curs_admin2' => $curs_admin2,
				'bank_coa_admin' => $bank_coa_admin,
				'status' => '1',
				'created_on' => date('Y-m-d H:i:s'),
				'created_by' => $Username
			);

			$this->All_model->dataSave('purchase_order_request_payment_header', $dataheader);
			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('1','4','8') order by parameter_no")->result();
			$det_Jurnaltes1 = array();
			foreach ($datajurnal1 as $rec) {
				// CASH BANK
				if ($rec->parameter_no == "1") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => ($bank_nilai),
						'debet' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK EXPENSE
				if ($rec->parameter_no == "4") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => 0,
						'debet' => $biaya_admin2,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				// ADMIN BANK
				if ($rec->parameter_no == "8") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin,
						'debet' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $rec->keterangan,
						'no_request' => $no_payment,
						'kredit' => $biaya_admin2,
						'debet' => 0,
						'no_reff' => $no_payment,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
			}
			foreach ($id_req as $keys) {
				$this->All_model->DataUpdate('purchase_order_request_payment_nm', array('status' => '2', 'payment_date' => $payment_date, 'no_payment' => $no_payment), array('id' => $keys));
				$data = $this->db->query("select * from purchase_order_request_payment_nm where id='" . $keys . "'")->row();
				$selisih_kurs = 0;
				$nilai_terima_barang_idr = 0;
				$datapoheader = $this->db->query("select * from tran_po_header where no_po='" . $data->no_po . "'")->row();
				if ($datapoheader->terima_barang_idr != 0) {
					$selisih_kurs = (($data->nilai_po_invoice * $curs) - $datapoheader->terima_barang_idr);
				}
				// update PO
				$nilai_dp_kurs = 0;
				$addsql = "";
				if ($data->tipe == 'TR-01') {
					$nilai_dp_kurs = ($data->nilai_po_invoice * $curs);
					$addsql = ", nilai_dp_kurs=" . $nilai_dp_kurs . "";
				}
				$this->db->query("update tran_po_header set terima_barang_kurs=0, terima_barang_idr=0
				" . $addsql . ", total_bayar=(total_bayar+" . ($data->nilai_po_invoice) . "),
				total_bayar_rupiah=(total_bayar_rupiah+" . ($data->nilai_po_invoice * $curs) . ")
				" .
					($data->tipe == 'TR-01' ?
						",nilai_dp=(nilai_dp+" . $data->nilai_po_invoice . "), sisa_dp=(sisa_dp+" . $data->nilai_po_invoice . ")" :
						", nilai_dp=(nilai_dp-" . $data->potongan_dp . "), sisa_dp=(sisa_dp-" . $data->potongan_dp . ")") .
					" where no_po='" . $data->no_po . "'");

				$keterangan		= 'Pembayaran ' . $no_payment;
				$data_coa 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='3'")->row();
				$data_supplier 	= $this->db->query("select * from supplier where id_supplier='" . $data->id_supplier . "'")->row();
				$datahutang = array(
					'tipe'       	 => 'BUK',
					'nomor'       	 => $Nomor_JV,
					'tanggal'        => $tanggal,
					'no_perkiraan'   => $data_coa->no_perkiraan,
					'keterangan'     => $keterangan,
					'no_reff'     	 => $data->no_po,
					'debet'      	 => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
					'kredit'         => 0,
					'id_supplier'    => $data->id_supplier,
					'nama_supplier'  => $data_supplier->nm_supplier,
					'no_request'     => $no_payment,
					'debet_usd'		 => (($curs_header != 'IDR') ? ($data->nilai_po_invoice + $data->invoice_ppn) : 0),
					'kredit_usd'	=> 0,
				);
				$this->db->insert('tr_kartu_hutang', $datahutang);


				if ($data->curs_header == 'IDR') {
					$coahutang = '2101-01-01';
				} else {
					$coahutang = '2101-01-04';
				}


				$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no in ('2','3','5','6','7','9') order by parameter_no")->result();
				foreach ($datajurnal1 as $rec) {
					if ($data->modul == 'PO') {
						// UANG MUKA
						if ($rec->parameter_no == "2") {
							if ($data->tipe == 'TR-01') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $coahutang,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								if ($data->potongan_dp > 0) {
									$det_Jurnaltes1[] = array(
										'nomor' => $nomor_jurnal,
										'tanggal' => $payment_date,
										'tipe' => 'BUK',
										'no_perkiraan' => $coahutang,
										'keterangan' => $data->keterangan,
										'no_request' => $data->no_po,
										'debet' => 0,
										'kredit' => 0,
										'no_reff' => $no_payment,
										'jenis_jurnal' => $jenis_jurnal,
										'nocust' => $data->id_supplier,
										'stspos' => '1'
									);
								}
							}
						}
						// HUTANG
						if ($rec->parameter_no == "3") {
							if ($data->tipe == 'TR-02') {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $rec->no_perkiraan,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							} else {
								$det_Jurnaltes1[] = array(
									'nomor' => $nomor_jurnal,
									'tanggal' => $payment_date,
									'tipe' => 'BUK',
									'no_perkiraan' => $rec->no_perkiraan,
									'keterangan' => $data->keterangan,
									'no_request' => $data->no_po,
									'kredit' => 0,
									'debet' => 0,
									'no_reff' => $no_payment,
									'jenis_jurnal' => $jenis_jurnal,
									'nocust' => $data->id_supplier,
									'stspos' => '1'
								);
							}
						}
					}

					if ($data->modul == 'FORWARDER') {
						// HUTANG FORWARDER
						if ($rec->parameter_no == "5") {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => 'FORWARDER ',
								'no_request' => $data->no_po,
								'kredit' => 0,
								'debet' => (($data->nilai_po_invoice + $data->invoice_ppn) * $curs),
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// PPN
					if ($rec->parameter_no == "6") {
						/*
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal, 'tanggal' => $payment_date, 'tipe' => 'BUK', 'no_perkiraan' => $rec->no_perkiraan, 'keterangan' => $data->keterangan, 'no_request' => $data->no_po, 'kredit' => 0, 'debet' => ($data->invoice_ppn*$curs), 'no_reff' => $no_payment, 'jenis_jurnal'=>$jenis_jurnal, 'nocust'=>$data->id_supplier
					);
*/
					}
					// PPH
					if ($rec->parameter_no == "7") {
						if ($data->nilai_pph_invoice <> 0) {
							$det_Jurnaltes1[] = array(
								'nomor' => $nomor_jurnal,
								'tanggal' => $payment_date,
								'tipe' => 'BUK',
								'no_perkiraan' => $rec->no_perkiraan,
								'keterangan' => $data->keterangan,
								'no_request' => $data->no_po,
								'kredit' => ($data->nilai_pph_invoice * $curs),
								'debet' => 0,
								'no_reff' => $no_payment,
								'jenis_jurnal' => $jenis_jurnal,
								'nocust' => $data->id_supplier,
								'stspos' => '1'
							);
						}
					}
					// SELISIH KURS
					if ($rec->parameter_no == "9") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => $data->keterangan,
							'no_request' => $data->no_po,
							'kredit' => ($selisih_kurs < 0 ? ($selisih_kurs * -1) : 0),
							'debet' => ($selisih_kurs >= 0 ? $selisih_kurs : 0),
							'no_reff' => $no_payment,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $data->id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$this->db->insert_batch('jurnaltras', $det_Jurnaltes1);


			//auto jurnal
			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);
			$total = 0;
			foreach ($det_Jurnaltes1 as $vals) {
				$datadetail = array(
					'tipe'			=> 'BUK',
					'nomor'			=> $Nomor_JV,
					'tanggal'		=> $tanggal,
					'no_perkiraan'	=> $vals['no_perkiraan'],
					'keterangan'	=> $vals['keterangan'],
					'no_reff'		=> $vals['no_reff'],
					'debet'			=> $vals['debet'],
					'kredit'		=> $vals['kredit'],
				);
				$total = ($total + $vals['debet']);
				$this->db->insert(DBACC . '.jurnal', $datadetail);
			}

			$dataJVhead = array(
				'nomor' 	    	=> $Nomor_JV,
				'tgl'	         	=> $tanggal,
				'jml'	            => $total,
				'jenis_ap'	        => 'V',
				'bayar_kepada'		=> $data_supplier->nm_supplier,
				'kdcab'				=> '101',
				'jenis_reff' 		=> 'BUK',
				'no_reff' 			=> $no_payment,
				'note'				=> $keterangan,
				'user_id'			=> $Username,
				'ho_valid'			=> '',
			);

			$this->db->insert(DBACC . '.japh', $dataJVhead);
			$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
			$this->db->query($Qry_Update_Cabang_acc);

			//end auto jurnal

			$this->db->trans_complete();
			if ($this->db->trans_status()) {
				$this->db->trans_commit();
				$result         = TRUE;
				history('Save Payment');
			} else {
				$this->db->trans_rollback();
				$result = FALSE;
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$result = FALSE;
		}

		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}

	public function list_request_payment($jenis_payment)
	{
		$this->template->set('jenis_payment', $jenis_payment);
		$this->template->title('List Request Payment');
		$this->template->render('list_request_payment');
	}

	public function check_payment()
	{
		// var_dump($this->input->post());die;
		$id      = $this->input->post('id');
		$checked = $this->input->post('checked');
		$tipe    = $this->input->post('tipe');

		$this->db->trans_start();

		if ($checked == 1) {
			$this->db->insert('tr_choosed_payment', [
				'id_user'    => $this->auth->user_id(),
				'id_payment' => $id,
				'tipe'       => $tipe
			]);
		} else {
			$this->db->delete('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $id]);
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function clear_choosed_payment()
	{
		$id_user = $this->auth->user_id();

		$this->db->trans_start();

		$this->db->delete('tr_choosed_payment', ['id_user' => $id_user]);

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

	public function proses_payment()
	{
		$id_user = $this->auth->user_id();

		$arr_choosed_payment = [];
		$arr_tipe            = [];

		$get_choosed_payment = $this->db
			->where('id_user', $id_user)
			->get('tr_choosed_payment')
			->result();

		foreach ($get_choosed_payment as $item) {
			$arr_choosed_payment[] = $item->id_payment;
			$arr_tipe[]            = $item->tipe;
		}

		if (ob_get_length()) ob_clean();
		header('Content-Type: application/json');
		echo json_encode([
			'count_choosed_payment' => count($get_choosed_payment),
			'arr_choosed_payment'   => implode(';', $arr_choosed_payment),
			'arr_tipe'              => implode(';', $arr_tipe)   // ← tambahan
		]);
	}

	public function save_payment()
	{
		$post = $this->input->post();

		try {
			$get_coa_bank = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $post['bank']])->row();
			$nm_coa_bank = '';
			$kode_bank = '';
			if (!empty($get_coa_bank)) {
				$nm_coa_bank = $get_coa_bank->nama;
				$kode_bank = $get_coa_bank->kode_bank;
			}

			$id_payment_paid = $this->Pembayaran_material_model->generate_id_payment_paid($kode_bank, $post['tgl_bayar']);

			$config['upload_path'] = 'assets/expense/';
			$config['allowed_types'] = '*';
			$config['remove_spaces'] = TRUE;
			$config['encrypt_name'] = TRUE;
			$filenames = '';

			if (!empty($_FILES['upload_doc']['name'])) {
				$_FILES['file']['name'] = $_FILES['upload_doc']['name'];
				$_FILES['file']['type'] = $_FILES['upload_doc']['type'];
				$_FILES['file']['tmp_name'] = $_FILES['upload_doc']['tmp_name'];
				$_FILES['file']['error'] = $_FILES['upload_doc']['error'];
				$_FILES['file']['size'] = $_FILES['upload_doc']['size'];
				// $this->load->library('upload', $config);
				$this->upload->initialize($config);
				if ($this->upload->do_upload('file')) {
					$uploadData = $this->upload->data();
					$filenames = $uploadData['file_name'];
				}
			}

			$insert_payment_paid = $this->db->insert('tr_payment_paid', [
				'id' => $id_payment_paid,
				'bank_charge' => str_replace(',', '', $post['bank_charge']),
				'created_by' => $this->auth->user_id(),
				'created_on' => date('Y-m-d H:i:s')
			]);
			if (!$insert_payment_paid) {
				throw new Exception($this->db->error($insert_payment_paid));
			}

			$this->db->where_in('id', explode(',', $post['id_payment']));
			$update_payment1 = $this->db->update('payment_approve', [
				'id_payment' => $id_payment_paid,
				'tgl_bayar' => $post['tgl_bayar'],
				'supplier' => $post['supplier_input'],
				'keterangan_pembayaran' => $post['keterangan_pembayaran'],
				'coa_bank' => $post['bank'],
				'nm_coa_bank' => $nm_coa_bank,
				'mata_uang' => $post['mata_uang'],
				'payment_bank' => str_replace(',', '', $post['payment_bank']),
				'total_payment' => $post['total_payment'],
				'selisih' => ($post['total_payment'] - str_replace(',', '', $post['payment_bank'])),
				'status' => 2,
				'link_doc' => $filenames,
				'id_supplier' => $post['supplier_input'],
				'nm_supplier' => $post['nm_supplier_input'],
				'kurs_payment' => str_replace(',', '', $post['kurs_payment'])
			]);
			if (!$update_payment1) {
				throw new Exception($this->db->error($update_payment1));
			}

			if (!empty($post['dt'])) {
				foreach ($post['dt'] as $detail) {
					$tipe_pph = ($detail['tipe_pph'] == 1) ? 'PPH 23' : 'PPH 22';

					$this->db->where('id', $detail['id_payment']);
					$update_payment_detail = $this->db->update('payment_approve', [
						'total_ppn' => str_replace(',', '', $detail['nilai_ppn']),
						'total_pph' => str_replace(',', '', $detail['nilai_pph']),
						'tipe_pph' => $tipe_pph
					]);

					$kurs_invoice = $detail['kurs_invoice'];
					if (!$update_payment_detail) {
						throw new Exception($this->db->error($update_payment_detail));
						// print_r($this->db->error($update_payment_detail));
						// exit;
					}
				}
			}

			$arr_jurnal = [];
			$no_jurnal = 1;
			if (isset($post['jurnal_ls'])) {
				// print_r($post['jurnal_ls']);
				// exit;
				foreach ($post['jurnal_ls'] as $item_jurnal) {
					// if (isset($item_jurnal['tanggal_jurnal'])) {
					$id_jurnal = $this->Pembayaran_material_model->generate_id_invoice_jurnal($no_jurnal);

					$arr_jurnal[] = [
						'no_jurnal' => $id_jurnal,
						'tgl_jurnal' => date('Y-m-d'),
						'coa' => $item_jurnal['coa'],
						'id_company' => $item_jurnal['id_company'],
						'nm_company' => $item_jurnal['nm_company'],
						'nm_coa' => $item_jurnal['nm_coa'],
						'debit' => $item_jurnal['debit'],
						'kredit' => $item_jurnal['kredit'],
						'keterangan' => $item_jurnal['keterangan'],
						'no_transaksi' => $id_payment_paid,
						'jenis_transaksi' => 'Transport',
						'id_divisi' => $item_jurnal['id_divisi'],
						'nm_divisi' => $item_jurnal['nm_divisi'],
						'created_by' => $this->auth->user_id(),
						'created_date' => date('Y-m-d')
					];

					$no_jurnal++;
					// }
				}
			}
			// else {
			// 	throw new Exception('Data jurnal tidak terdeteksi !');
			// }

			// if (!empty($arr_jurnal)) {
			$insert_jurnal = $this->db->insert_batch('tr_jurnal', $arr_jurnal);
			if (!$insert_jurnal) {
				throw new Exception('Data jurnal gagal dibuat !');
			}

			$no_payment = $post['id_payment'];

			if ($post['mata_uang'] == 'IDR') {
				$jenis_jurnal = 'BUK001';
				$kurs         = 1;
				$selisih      = 0;
				$hutang       = (str_replace(',', '', $post['total_payment']) * $kurs) + (str_replace(',', '', $detail['nilai_ppn']) * $kurs);
			} else {
				$jenis_jurnal = 'BUK004';
				$kurs         = str_replace(',', '', $post['kurs_payment']);
				$selisih      = $kurs - $kurs_invoice;
				$hutang       = (str_replace(',', '', $post['total_payment']) * $kurs_invoice) + (str_replace(',', '', $detail['nilai_ppn']) * $kurs_invoice);
			}

			$bank_coa     = $post['bank'];
			$no_request   = $post['id_payment'];
			$keterangan   = $post['keterangan_pembayaran'];
			$bankcharge   = (str_replace(',', '', $post['bank_charge'])) * $kurs;
			$bank_nilai   = (str_replace(',', '', $post['payment_bank'])) * $kurs;
			$ap           = str_replace(',', '', $post['total_payment']);
			$selisihkurs  = $selisih * $ap;

			if ($selisihkurs < 0) {
				$selisihdebet  = 0;
				$selisihkredit = $selisihkurs * (-1);
			} elseif ($selisihkurs > 0) {
				$selisihdebet  = $selisihkurs;
				$selisihkredit = 0;
			}

			$nomor_jurnal = $nomor_jurnal = $jenis_jurnal . $no_payment . rand(100, 999);
			$payment_date = $post['tgl_bayar'];
			$id_supplier = $post['supplier_input'];
			$nm_supplier = $post['nm_supplier_input'];
			$no_reff     = $post['id_payment'];
			$Username    = $this->auth->user_id();

			$datajurnal1 = $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' order by parameter_no")->result();
			$det_Jurnaltes1 = array();
			foreach ($datajurnal1 as $rec) {
				if ($rec->parameter_no == "1") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $bank_coa,
						'keterangan' => $no_request . '. ' . $keterangan,
						'no_request' => $id_payment_paid,
						'kredit' => ($bank_nilai),
						'debet' => 0,
						'no_reff' => $no_request,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}
				if ($rec->parameter_no == "2") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $no_request . '. ' . $keterangan,
						'no_request' => $id_payment_paid,
						'kredit' => 0,
						'debet' => $hutang,
						'no_reff' => $no_request,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}

				if ($rec->parameter_no == "4") {
					$det_Jurnaltes1[] = array(
						'nomor' => $nomor_jurnal,
						'tanggal' => $payment_date,
						'tipe' => 'BUK',
						'no_perkiraan' => $rec->no_perkiraan,
						'keterangan' => $no_request . '. ' . $keterangan,
						'no_request' => $id_payment_paid,
						'kredit' => 0,
						'debet' => $bankcharge,
						'no_reff' => $no_request,
						'jenis_jurnal' => $jenis_jurnal,
						'nocust' => $id_supplier,
						'stspos' => '1'
					);
				}

				if ($jenis_jurnal = 'BUK004') {
					if ($rec->parameter_no == "5") {
						$det_Jurnaltes1[] = array(
							'nomor' => $nomor_jurnal,
							'tanggal' => $payment_date,
							'tipe' => 'BUK',
							'no_perkiraan' => $rec->no_perkiraan,
							'keterangan' => $no_request . '. ' . $keterangan,
							'no_request' => $id_payment_paid,
							'kredit' => $selisihkredit,
							'debet' => $selisihdebet,
							'no_reff' => $no_request,
							'jenis_jurnal' => $jenis_jurnal,
							'nocust' => $id_supplier,
							'stspos' => '1'
						);
					}
				}
			}
			$insert_jurnal_tras = $this->db->insert_batch('jurnaltras', $det_Jurnaltes1);
			if (!$insert_jurnal_tras) {
				throw new Exception('Input JurnalTras gagal !');
			}

			//auto jurnal
			$tanggal = $payment_date;
			$Bln	= substr($tanggal, 5, 2);
			$Thn	= substr($tanggal, 0, 4);
			$Nomor_JV = $this->Jurnal_model->get_no_buk('101', $tanggal);
			$total = 0;
			foreach ($det_Jurnaltes1 as $vals) {
				$datadetail = array(
					'tipe'			=> 'BUK',
					'nomor'			=> $Nomor_JV,
					'tanggal'		=> $tanggal,
					'no_perkiraan'	=> $vals['no_perkiraan'],
					'keterangan'	=> $vals['keterangan'],
					'no_reff'		=> $vals['no_reff'],
					'debet'			=> $vals['debet'],
					'kredit'		=> $vals['kredit'],
				);
				$total = ($total + $vals['debet']);
				$insert_jurnal_det = $this->db->insert(DBACC . '.jurnal', $datadetail);
				if (!$insert_jurnal_det) {
					throw new Exception('Input Jurnal Detail gagal !');
				}
			}

			$keterangan		= 'Pembayaran ' . $no_reff;
			$dataJVhead = array(
				'nomor' 	    	=> $Nomor_JV,
				'tgl'	         	=> $tanggal,
				'jml'	            => $total,
				'jenis_ap'	        => 'V',
				'bayar_kepada'		=> $nm_supplier,
				'kdcab'				=> '101',
				'jenis_reff' 		=> 'BUK',
				'no_reff' 			=> $no_reff,
				'note'				=> $keterangan,
				'user_id'			=> $Username,
				'ho_valid'			=> '',
			);

			$insert_japh = $this->db->insert(DBACC . '.japh', $dataJVhead);
			if (!$insert_japh) {
				throw new Exception('Insert JAPH gagak !');
			}
			$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
			$this->db->query($Qry_Update_Cabang_acc);

			$data_coa 	= $this->db->query("select * from " . DBACC . ".master_oto_jurnal_detail where kode_master_jurnal='" . $jenis_jurnal . "' and parameter_no='3'")->row();
			$datahutang = array(
				'tipe'       	 => 'BUK',
				'nomor'       	 => $Nomor_JV,
				'tanggal'        => $tanggal,
				'no_perkiraan'   => $data_coa->no_perkiraan,
				'keterangan'     => $keterangan,
				'no_reff'     	 => $no_reff,
				'debet'      	 => $hutang,
				'kredit'         => 0,
				'id_supplier'    => $id_supplier,
				'nama_supplier'  => $nm_supplier,
				'no_request'     => $no_request,
			);
			$insert_kartu_hutang = $this->db->insert('tr_kartu_hutang', $datahutang);
			if (!$insert_kartu_hutang) {
				throw new Exception('Insert Kartu Hutang gagal !');
			}

			//end auto jurnal

			// }

			$valid = 1;
			$pesan = 'Selamat, data telah berhasil dibayar !';
			// }
			$this->db->trans_commit();
			echo json_encode([
				'status' => $valid,
				'pesan' => $pesan
			]);
		} catch (Exception $e) {
			$this->db->trans_rollback();

			$response = [
				'status' => 0,
				'pesan' => $e->getMessage()
			];

			echo json_encode($response);
		}
	}

	public function used_choosed_payment()
	{
		$this->db->trans_start();

		$this->db->delete('tr_choosed_payment', ['id_user' => $this->auth->user_id()]);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
	}

	public function get_list_req_payment()
	{
		$this->Pembayaran_material_model->get_list_req_payment();
	}

	public function set_jurnal()
	{
		$this->Pembayaran_material_model->set_jurnal();
	}


	public function set_jurnal_refill()
	{
		$this->Pembayaran_material_model->set_jurnal_refill();
	}
}
