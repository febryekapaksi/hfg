<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_shift extends Admin_Controller
{
	protected $viewPermission   = 'Master_Shift.View';
	protected $addPermission    = 'Master_Shift.Add';
	protected $managePermission = 'Master_Shift.Manage';
	protected $deletePermission = 'Master_Shift.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('master_shift/master_shift_model');
		date_default_timezone_set('Asia/Bangkok');
	}

	/**
	 * Halaman index - list data shift
	 */
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		history("View index master shift");

		$this->template->title('Master Shift');
		$this->template->render('index');
	}

	/**
	 * DataTables server-side JSON
	 */
	public function data_side()
	{
		$requestData = $_REQUEST;
		$search      = $requestData['search']['value'] ?? '';
		$col_order   = $requestData['order'][0]['column'] ?? 1;
		$col_dir     = $requestData['order'][0]['dir'] ?? 'asc';
		$start       = (int) ($requestData['start'] ?? 0);
		$length      = (int) ($requestData['length'] ?? 10);

		$fetch         = $this->master_shift_model->get_datatable($search, $col_order, $col_dir, $start, $length);
		$totalData     = $fetch['totalData'];
		$totalFiltered = $fetch['totalFiltered'];
		$query         = $fetch['query'];

		$ENABLE_MANAGE = has_permission('Master_Shift.Manage');
		$ENABLE_DELETE = has_permission('Master_Shift.Delete');

		$data  = [];
		$urut1 = 1;
		$urut2 = 0;

		foreach ($query->result_array() as $row) {
			$total_data = $totalData;
			$start_dari = $start;
			$asc_desc   = $col_dir;

			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			} else {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$nestedData = [];

			// #
			$nestedData[] = "<div class='text-center'>{$nomor}</div>";

			// Nama Shift
			$nestedData[] = "<div class='fw-semibold'>" . htmlspecialchars($row['nama_shift']) . "</div>";

			// Keterangan
			$ket = !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-';
			$nestedData[] = "<div>{$ket}</div>";

			// Last By
			$last_by = !empty($row['updated_by']) ? $row['updated_by'] : $row['created_by'];
			$nestedData[] = "<div class='text-nowrap'>" . strtoupper(get_name('users', 'nm_lengkap', 'id_user', $last_by)) . "</div>";

			// Last Date
			$last_date = !empty($row['updated_date']) ? $row['updated_date'] : $row['created_date'];
			$nestedData[] = "<div class='text-center text-nowrap'>" . date('d-M-Y H:i', strtotime($last_date)) . "</div>";

			// Action
			$edit   = '';
			$delete = '';

			if ($ENABLE_MANAGE) {
				$edit = "<button type='button' class='btn-icon btn-icon-edit edit' data-id='{$row['id']}' title='Edit'>
							<i class='ti ti-edit'></i>
						</button>";
			}

			if ($ENABLE_DELETE) {
				$delete = "<button type='button' class='btn-icon btn-icon-delete delete' data-id='{$row['id']}' title='Delete'>
							<i class='ti ti-trash'></i>
						</button>";
			}

			$nestedData[] = "<div class='text-end d-inline-flex gap-1'>{$edit}{$delete}</div>";

			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		echo json_encode([
			"draw"            => intval($requestData['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data,
		]);
	}

	/**
	 * Form tambah / edit (loaded via AJAX into modal)
	 */
	public function form()
	{
		$id   = $this->input->post('id');
		$data = ['row' => null];

		if (!empty($id)) {
			$data['row'] = $this->master_shift_model->get_by_id($id);
		}

		$this->load->view('form', $data);
	}

	/**
	 * Simpan data (insert / update)
	 */
	public function save()
	{
		$this->auth->restrict($this->addPermission);

		$session     = $this->session->userdata('app_session');
		$id          = $this->input->post('id');
		$nama_shift  = trim($this->input->post('nama_shift'));
		$keterangan  = trim($this->input->post('keterangan'));

		// Validation
		if (empty($nama_shift)) {
			echo json_encode(['status' => 0, 'pesan' => 'Shift Name is required!']);
			return;
		}

		// Check duplicate
		if ($this->master_shift_model->is_duplicate($nama_shift, $id ?: null)) {
			echo json_encode(['status' => 0, 'pesan' => 'Shift Name already exists!']);
			return;
		}

		$this->db->trans_start();

		if (empty($id)) {
			// INSERT
			$this->master_shift_model->insert_shift([
				'nama_shift'   => $nama_shift,
				'keterangan'   => $keterangan,
				'created_by'   => $session['id_user'],
				'created_date' => date('Y-m-d H:i:s'),
			]);
			$action = 'Insert';
		} else {
			// UPDATE
			$this->master_shift_model->update_shift($id, [
				'nama_shift'   => $nama_shift,
				'keterangan'   => $keterangan,
				'updated_by'   => $session['id_user'],
				'updated_date' => date('Y-m-d H:i:s'),
			]);
			$action = 'Update';
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			echo json_encode(['status' => 0, 'pesan' => 'Failed to save data!']);
		} else {
			$this->db->trans_commit();
			history("{$action} master shift: {$nama_shift}");
			echo json_encode(['status' => 1, 'pesan' => "Data saved successfully."]);
		}
	}

	/**
	 * Delete data (soft delete)
	 */
	public function hapus()
	{
		$this->auth->restrict($this->deletePermission);

		$id      = $this->input->post('id');
		$session = $this->session->userdata('app_session');

		if (empty($id)) {
			echo json_encode(['status' => 0, 'pesan' => 'ID not found!']);
			return;
		}

		$this->db->trans_start();
		$this->master_shift_model->delete_shift($id, $session['id_user']);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			echo json_encode(['status' => 0, 'pesan' => 'Failed to delete data!']);
		} else {
			$this->db->trans_commit();
			history("Delete master shift id: {$id}");
			echo json_encode(['status' => 1, 'pesan' => 'Data deleted successfully.']);
		}
	}
}
