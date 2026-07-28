<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_sound_model extends BF_Model
{
    protected $table_name = 'ms_sound_app';
    protected $key        = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get DataTables Server-Side Processing
     */
    public function get_datatable($search = '', $col_order = 1, $col_dir = 'asc', $start = 0, $length = 10)
    {
        $columns = [
            0 => 'id',
            1 => 'sound_name',
            2 => 'sound_code',
            3 => 'vibrate_level',
            4 => 'file_original_name',
            5 => 'status',
            6 => 'updated_date'
        ];

        $order_col = $columns[$col_order] ?? 'id';

        // Base Query
        $this->db->from($this->table_name);

        $totalData = $this->db->count_all_results('', false);

        // Filter Search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('sound_name', $search);
            $this->db->or_like('sound_code', $search);
            $this->db->or_like('file_original_name', $search);
            $this->db->or_like('keterangan', $search);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results('', false);

        // Ordering & Paging
        $this->db->order_by($order_col, $col_dir);
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();

        return [
            'totalData'     => $totalData,
            'totalFiltered' => $totalFiltered,
            'query'         => $query
        ];
    }

    /**
     * Get data sound by ID or all
     */
    public function get_data($id = null)
    {
        if ($id !== null) {
            return $this->db->get_where($this->table_name, ['id' => $id])->row_array();
        }
        return $this->db->order_by('sound_name', 'ASC')->get($this->table_name)->result_array();
    }

    /**
     * Get sound by code
     */
    public function get_by_code($code)
    {
        return $this->db->get_where($this->table_name, ['sound_code' => $code, 'status' => 1])->row_array();
    }

    /**
     * Save data (Insert / Update)
     */
    public function save_data($data, $id = null)
    {
        if (!empty($id)) {
            $this->db->where('id', $id);
            return $this->db->update($this->table_name, $data);
        } else {
            $this->db->insert($this->table_name, $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Delete record and return file info for cleanup
     */
    public function delete_data($id)
    {
        $row = $this->get_data($id);
        if ($row) {
            $this->db->where('id', $id)->delete($this->table_name);
            return $row;
        }
        return false;
    }
}
