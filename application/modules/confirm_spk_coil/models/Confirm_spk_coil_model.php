<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Confirm_spk_coil_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // DATATABLES - PENDING CONFIRMATIONS
    // ---------------------------------------------------------------

    /**
     * Get daftar SPK Coil status "Material On Load" untuk DataTables server-side
     * JOIN tr_spk_material_header untuk info SPK
     *
     * @param string $search    Search keyword (spk_coil_no / spk_no)
     * @param int    $start     Offset untuk pagination
     * @param int    $length    Limit per page
     * @param string $order_by  Kolom untuk ORDER BY
     * @param string $order_dir ASC atau DESC
     * @return array Array of pending confirmation rows
     */
    public function get_pending_confirmations($search, $start, $length, $order_by, $order_dir)
    {
        $this->db->select('wrh.*, smh.spk_no as spk_material_no, smh.tgl_spk, smh.shift_names');
        $this->db->from('tr_warehouse_request_header wrh');
        $this->db->join('tr_spk_material_header smh', 'smh.spk_no = wrh.spk_no', 'left');
        $this->db->where('wrh.status', 'Material On Load');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('wrh.spk_coil_no', $search);
            $this->db->or_like('wrh.spk_no', $search);
            $this->db->group_end();
        }

        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);

        return $this->db->get()->result_array();
    }

    /**
     * Count total pending confirmations dengan filter search untuk DataTables
     *
     * @param string $search Search keyword
     * @return int Total filtered count
     */
    public function count_pending_filtered($search)
    {
        $this->db->from('tr_warehouse_request_header wrh');
        $this->db->join('tr_spk_material_header smh', 'smh.spk_no = wrh.spk_no', 'left');
        $this->db->where('wrh.status', 'Material On Load');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('wrh.spk_coil_no', $search);
            $this->db->or_like('wrh.spk_no', $search);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    // ---------------------------------------------------------------
    // REQUEST & COIL DETAIL
    // ---------------------------------------------------------------

    /**
     * Get single warehouse request header by ID, JOIN spk_material_header untuk info SPK
     *
     * @param int $request_id ID dari tr_warehouse_request_header
     * @return array|null Request header data atau null
     */
    public function get_request($request_id)
    {
        $this->db->select('wrh.*, smh.spk_no as spk_material_no, smh.tgl_spk, smh.shift_names');
        $this->db->from('tr_warehouse_request_header wrh');
        $this->db->join('tr_spk_material_header smh', 'smh.spk_no = wrh.spk_no', 'left');
        $this->db->where('wrh.id', $request_id);

        return $this->db->get()->row_array();
    }

    /**
     * Get semua coil detail untuk request tertentu
     *
     * @param int $request_id ID dari tr_warehouse_request_header
     * @return array Array of coil detail rows
     */
    public function get_coil_details($request_id)
    {
        $this->db->where('request_id', $request_id);
        return $this->db->get('tr_warehouse_request_coil_detail')->result_array();
    }

    // ---------------------------------------------------------------
    // SCAN OPERATIONS
    // ---------------------------------------------------------------

    /**
     * Update scan status per coil detail (tandai sudah di-scan)
     *
     * @param int   $detail_id ID dari tr_warehouse_request_coil_detail
     * @param array $data      Array berisi scan_status, scanned_at, scanned_by
     * @return bool Update result
     */
    public function update_scan_status($detail_id, $data)
    {
        $this->db->where('id', $detail_id);
        return $this->db->update('tr_warehouse_request_coil_detail', $data);
    }

    /**
     * Find coil by kode_internal dalam SPK Coil tertentu
     *
     * @param int    $request_id    ID dari tr_warehouse_request_header
     * @param string $kode_internal Kode internal coil dari QR scan
     * @return array|null Coil detail row atau null jika tidak ditemukan
     */
    public function find_coil_by_kode_internal($request_id, $kode_internal)
    {
        $this->db->where('request_id', $request_id);
        $this->db->where('kode_internal', $kode_internal);
        return $this->db->get('tr_warehouse_request_coil_detail')->row_array();
    }

    /**
     * Check apakah semua coil dalam SPK Coil sudah di-scan
     *
     * @param int $request_id ID dari tr_warehouse_request_header
     * @return bool True jika semua coil sudah di-scan, false jika masih ada yang belum
     */
    public function all_coils_scanned($request_id)
    {
        $this->db->where('request_id', $request_id);
        $this->db->where('scan_status', 0);
        $count = $this->db->count_all_results('tr_warehouse_request_coil_detail');

        return $count == 0;
    }

    // ---------------------------------------------------------------
    // STOCK OPERATIONS
    // ---------------------------------------------------------------

    /**
     * Reduce qty di warehouse_stock_coil
     *
     * @param int          $id_coil ID coil di warehouse_stock_coil
     * @param float|string $qty     Jumlah yang dikurangi (plan_use)
     * @return bool Update result
     */
    public function reduce_coil_stock($id_coil, $qty)
    {
        $this->db->set('qty', 'qty - ' . (float) $qty, false);
        $this->db->where('id', $id_coil);
        return $this->db->update('warehouse_stock_coil');
    }

    /**
     * Insert record baru ke warehouse_stock_wip
     *
     * @param array $data Associative array kolom WIP record
     * @return bool Insert result
     */
    public function insert_wip_record($data)
    {
        return $this->db->insert('warehouse_stock_wip', $data);
    }

    /**
     * Get source coil data dari warehouse_stock_coil untuk di-copy ke WIP
     *
     * @param int $id_coil ID coil di warehouse_stock_coil
     * @return array|null Coil data row atau null
     */
    public function get_coil_source_data($id_coil)
    {
        $this->db->where('id', $id_coil);
        return $this->db->get('warehouse_stock_coil')->row_array();
    }

    // ---------------------------------------------------------------
    // STATUS UPDATES
    // ---------------------------------------------------------------

    /**
     * Update status warehouse request header
     *
     * @param int   $request_id ID dari tr_warehouse_request_header
     * @param array $data       Array berisi status, confirmed_by, confirmed_at
     * @return bool Update result
     */
    public function update_request_status($request_id, $data)
    {
        $this->db->where('id', $request_id);
        return $this->db->update('tr_warehouse_request_header', $data);
    }

    /**
     * Update status SPK Material header
     *
     * @param string $spk_no Nomor SPK Material
     * @param array  $data   Array berisi status, updated_by, updated_at
     * @return bool Update result
     */
    public function update_spk_material_status($spk_no, $data)
    {
        $this->db->where('spk_no', $spk_no);
        return $this->db->update('tr_spk_material_header', $data);
    }

    /**
     * Check apakah semua SPK Coil untuk SPK Material sudah confirmed
     * Return true jika TIDAK ADA request yang belum confirmed (selain Rejected)
     *
     * @param string $spk_no Nomor SPK Material
     * @return bool True jika semua SPK Coil sudah confirmed
     */
    public function all_spk_coil_confirmed($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        $this->db->where('status !=', 'Material Confirmed');
        $this->db->where('status !=', 'Rejected');
        $count = $this->db->count_all_results('tr_warehouse_request_header');

        return $count == 0;
    }
}
