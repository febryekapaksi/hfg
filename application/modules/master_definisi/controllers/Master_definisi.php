<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_definisi extends Admin_Controller
{
	// Permission
	protected $viewPermission   = 'Master_definisi.View';
	protected $addPermission    = 'Master_definisi.Add';
	protected $managePermission = 'Master_definisi.Manage';
	protected $deletePermission = 'Master_definisi.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->template->title('Master Definisi');
		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);

		$data = $this->db->get_where('ms_definisi', array('deleted' => 'N'))->result();

		history("View data definisi");
		$this->template->set('results', $data);
		$this->template->title('Master Definisi');
		$this->template->render('index');
	}

	public function add($id = null)
	{
		if ($this->input->post()) {
			$data = $this->input->post();

			$session  = $this->session->userdata('app_session');
			$username = $session['id_user'];
			$datetime = date('Y-m-d H:i:s');

			$id       = $data['id'];
			$istilah  = $data['istilah'];
			$definisi = $data['definisi'];

			$field_by   = (empty($id)) ? 'created_by' : 'updated_by';
			$field_date = (empty($id)) ? 'created_date' : 'updated_date';
			$field_hist = (empty($id)) ? 'Add' : 'Edit';

			$ArrHeader = array(
				'istilah'   => $istilah,
				'definisi'  => $definisi,
				$field_by   => $username,
				$field_date => $datetime
			);

			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('ms_definisi', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('ms_definisi', $ArrHeader);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data = array(
					'pesan'  => 'Process Failed !',
					'status' => 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data = array(
					'pesan'  => 'Process Success !',
					'status' => 1
				);
				history($field_hist . " data definisi " . $id);
			}

			echo json_encode($Arr_Data);
		} else {
			$session = $this->session->userdata('app_session');
			$header  = $this->db->get_where('ms_definisi', array('id' => $id))->result();

			$data = [
				'header' => $header,
			];
			$this->template->title('Add Master Definisi');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add', $data);
		}
	}

	/**
	 * API endpoint untuk floating widget definisi
	 * Return JSON list semua definisi (untuk AJAX)
	 */
	public function get_all()
	{
		$keyword = $this->input->get('q');

		$this->db->where('deleted', 'N');
		if (!empty($keyword)) {
			$this->db->group_start();
			$this->db->like('istilah', $keyword);
			$this->db->or_like('definisi', $keyword);
			$this->db->group_end();
		}
		$this->db->order_by('istilah', 'ASC');
		$data = $this->db->get('ms_definisi')->result();

		echo json_encode(['status' => 1, 'data' => $data]);
	}

	public function hapus()
	{
		$data = $this->input->post();
		$session = $this->session->userdata('app_session');
		$id      = $data['id'];

		$ArrHeader = array(
			'deleted'      => "Y",
			'deleted_by'   => $session['id_user'],
			'deleted_date' => date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->update('ms_definisi', $ArrHeader);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data = array(
				'pesan'  => 'Process Failed !',
				'status' => 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data = array(
				'pesan'  => 'Process Success !',
				'status' => 1
			);
			history("Delete data definisi " . $id);
		}

		echo json_encode($Arr_Data);
	}
}
