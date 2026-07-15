<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_shift_model extends BF_Model
{
	protected $table_name = 'master_shift';
	protected $key        = 'id';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get DataTables server-side query
	 */
	public function get_datatable($search = '', $col_order = 0, $col_dir = 'asc', $start = 0, $length = 10)
	{
		$like = $this->db->escape_like_str($search);

		$sql_base = "
			SELECT a.*
			FROM {$this->table_name} a
			WHERE a.deleted_date IS NULL
			AND (
				a.nama_shift LIKE '%{$like}%'
				OR a.keterangan LIKE '%{$like}%'
			)
		";

		// Total filtered
		$total = $this->db->query($sql_base)->num_rows();

		// Order
		$columns = [
			0 => 'a.id',
			1 => 'a.nama_shift',
			2 => 'a.keterangan',
			3 => 'a.created_date',
			4 => 'a.created_date',
		];

		$order_col = isset($columns[$col_order]) ? $columns[$col_order] : 'a.nama_shift';
		$sql_base .= " ORDER BY {$order_col} {$col_dir}";
		$sql_base .= " LIMIT {$start}, {$length}";

		$query = $this->db->query($sql_base);

		return [
			'totalData'     => $total,
			'totalFiltered' => $total,
			'query'         => $query,
		];
	}

	/**
	 * Get semua shift aktif (untuk dropdown/select)
	 */
	public function get_all_active()
	{
		return $this->db
			->where('deleted_date IS NULL', NULL, FALSE)
			->order_by('nama_shift', 'ASC')
			->get($this->table_name)
			->result();
	}

	/**
	 * Get satu data shift by ID
	 */
	public function get_by_id($id)
	{
		return $this->db
			->where('id', $id)
			->where('deleted_date IS NULL', NULL, FALSE)
			->get($this->table_name)
			->row();
	}

	/**
	 * Insert data shift baru
	 */
	public function insert_shift($data)
	{
		$this->db->insert($this->table_name, $data);
		return $this->db->insert_id();
	}

	/**
	 * Update data shift
	 */
	public function update_shift($id, $data)
	{
		$this->db->where('id', $id);
		return $this->db->update($this->table_name, $data);
	}

	/**
	 * Soft delete shift
	 */
	public function delete_shift($id, $user_id)
	{
		$this->db->where('id', $id);
		return $this->db->update($this->table_name, [
			'deleted_by'   => $user_id,
			'deleted_date' => date('Y-m-d H:i:s'),
		]);
	}

	/**
	 * Cek duplikat nama shift
	 */
	public function is_duplicate($nama_shift, $exclude_id = null)
	{
		$this->db->where('nama_shift', $nama_shift);
		$this->db->where('deleted_date IS NULL', NULL, FALSE);

		if ($exclude_id) {
			$this->db->where('id !=', $exclude_id);
		}

		return $this->db->count_all_results($this->table_name) > 0;
	}
}
