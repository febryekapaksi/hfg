<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Request_list_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // SPK LIST & DATATABLES
    // ---------------------------------------------------------------

    /**
     * Get daftar SPK Material dengan filter status untuk DataTables server-side
     * WHERE status IN ('Material Requested', 'Material On Load', 'Material Confirmed')
     * Include subquery detail_count untuk jumlah produk
     *
     * @param string $search    Search keyword (spk_no)
     * @param int    $start     Offset untuk pagination
     * @param int    $length    Limit rows
     * @param string $order_by  Kolom untuk ORDER BY
     * @param string $order_dir ASC atau DESC
     * @return array Array of SPK Material rows
     */
    public function get_spk_list($search, $start, $length, $order_by, $order_dir)
    {
        $this->db->select('h.*, (SELECT COUNT(*) FROM tr_spk_material_detail d WHERE d.spk_no = h.spk_no) as detail_count');
        $this->db->from('tr_spk_material_header h');
        $this->db->where_in('h.status', ['Material Requested', 'Material On Load', 'Material Confirmed']);

        if (!empty($search)) {
            $this->db->like('h.spk_no', $search);
        }

        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);

        return $this->db->get()->result_array();
    }

    /**
     * Count total SPK Material filtered untuk DataTables recordsFiltered
     *
     * @param string $search Search keyword
     * @return int Total count
     */
    public function count_spk_filtered($search)
    {
        $this->db->from('tr_spk_material_header');
        $this->db->where_in('status', ['Material Requested', 'Material On Load', 'Material Confirmed']);

        if (!empty($search)) {
            $this->db->like('spk_no', $search);
        }

        return $this->db->count_all_results();
    }

    // ---------------------------------------------------------------
    // SPK DETAIL & BOM
    // ---------------------------------------------------------------

    /**
     * Get SPK header + detail produk + BOM materials per produk
     * JOIN tr_spk_material_detail untuk produk, lalu get BOM via ms_bom_header + ms_bom_detail
     *
     * @param string $spk_no Nomor SPK Material
     * @return array|null Array dengan 'header' dan 'products' (each with 'materials')
     */
    public function get_spk_with_details($spk_no)
    {
        // Get header
        $header = $this->db->where('spk_no', $spk_no)->get('tr_spk_material_header')->row_array();

        if (!$header) {
            return null;
        }

        // Get detail produk
        $products = $this->db
            ->where('spk_no', $spk_no)
            ->order_by('urut', 'ASC')
            ->get('tr_spk_material_detail')
            ->result_array();

        // Get BOM materials per produk
        foreach ($products as &$product) {
            $sql = "SELECT bd.id_material, bd.nm_material, bd.qty, bd.id_unit, bd.nm_unit
                    FROM ms_bom_header bh
                    JOIN ms_bom_detail bd ON bd.id_bom = bh.id
                    WHERE bh.id_produk = ? AND bh.is_delete = 0 AND bd.is_delete = 0
                    ORDER BY bd.nm_material ASC";

            $product['materials'] = $this->db->query($sql, [$product['id_produk_fg']])->result_array();
        }

        return [
            'header'   => $header,
            'products' => $products
        ];
    }

    // ---------------------------------------------------------------
    // COIL AVAILABILITY
    // ---------------------------------------------------------------

    /**
     * Get available coils dari warehouse_stock_coil
     * Filter: id_material, id_gudang IN (1, 3), status = 'active'
     * Exclude: coil yang sudah dipilih di SPK Coil sebelumnya untuk SPK yang sama (non-Rejected)
     *
     * @param string $id_material ID material
     * @param string $spk_no      Nomor SPK Material (untuk exclude already selected)
     * @return array Array of available coil rows
     */
    public function get_available_coils($id_material, $spk_no)
    {
        $sql = "SELECT wsc.*
                FROM warehouse_stock_coil wsc
                WHERE wsc.id_material = ?
                  AND wsc.id_gudang IN (1, 3)
                  AND wsc.status = 1
                  AND wsc.id NOT IN (
                      SELECT wrcd.id_coil 
                      FROM tr_warehouse_request_coil_detail wrcd
                      JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                      WHERE wrh.spk_no = ?
                        AND wrh.status != 'Rejected'
                  )
                ORDER BY wsc.id_gudang ASC, wsc.no_coil ASC";

        return $this->db->query($sql, [$id_material, $spk_no])->result_array();
    }

    /**
     * Get total coil count per material per gudang
     *
     * @param string $id_material ID material
     * @param int    $id_gudang   ID gudang (1=Gudang Coil, 3=WIP)
     * @return int Count of available coils
     */
    public function get_coil_count_by_gudang($id_material, $id_gudang)
    {
        $this->db->where('id_material', $id_material);
        $this->db->where('id_gudang', $id_gudang);
        $this->db->where('status', 1);

        return $this->db->count_all_results('warehouse_stock_coil');
    }

    /**
     * Check single coil availability
     * SELECT dari warehouse_stock_coil WHERE id=? AND status=1
     *
     * @param int $id_coil ID coil
     * @return array|null Coil row atau null jika tidak tersedia
     */
    public function check_coil_available($id_coil)
    {
        return $this->db
            ->where('id', $id_coil)
            ->where('status', 1)
            ->get('warehouse_stock_coil')
            ->row_array();
    }

    // ---------------------------------------------------------------
    // SPK COIL NUMBER GENERATION
    // ---------------------------------------------------------------

    /**
     * Generate nomor SPK Coil format SPKC-YYYYMM-XXXX
     * Query last counter dari tr_warehouse_request_header bulan ini
     *
     * @return string Nomor SPK Coil baru (e.g. SPKC-202506-0001)
     */
    public function generate_spk_coil_no()
    {
        $prefix = 'SPKC-' . date('Ym') . '-';

        $last = $this->db
            ->like('spk_coil_no', $prefix, 'after')
            ->order_by('spk_coil_no', 'DESC')
            ->limit(1)
            ->get('tr_warehouse_request_header')
            ->row();

        $next = 1;
        if ($last) {
            $parts = explode('-', $last->spk_coil_no);
            $next = (int) end($parts) + 1;
        }

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ---------------------------------------------------------------
    // INSERT & UPDATE OPERATIONS
    // ---------------------------------------------------------------

    /**
     * Insert header warehouse request ke tr_warehouse_request_header
     *
     * @param array $data Associative array kolom header
     * @return int Insert ID
     */
    public function insert_request_header($data)
    {
        $this->db->insert('tr_warehouse_request_header', $data);
        return $this->db->insert_id();
    }

    /**
     * Insert batch coil details ke tr_warehouse_request_coil_detail
     *
     * @param array $details Array of associative arrays untuk setiap coil
     * @return bool Insert batch result
     */
    public function insert_coil_details($details)
    {
        return $this->db->insert_batch('tr_warehouse_request_coil_detail', $details);
    }

    /**
     * Update status SPK Material di tr_spk_material_header
     *
     * @param string $spk_no Nomor SPK
     * @param array  $data   Array berisi status, updated_by, updated_at
     * @return bool Update result
     */
    public function update_spk_material_status($spk_no, $data)
    {
        $this->db->where('spk_no', $spk_no);
        return $this->db->update('tr_spk_material_header', $data);
    }

    // ---------------------------------------------------------------
    // EXISTING COIL SELECTIONS
    // ---------------------------------------------------------------

    /**
     * Get coil IDs yang sudah dipilih untuk SPK Material tertentu
     * Dari tr_warehouse_request_coil_detail JOIN header WHERE spk_no AND status != 'Rejected'
     *
     * @param string $spk_no Nomor SPK Material
     * @return array Array of id_coil values
     */
    public function get_selected_coil_ids($spk_no)
    {
        $this->db->select('wrcd.id_coil');
        $this->db->from('tr_warehouse_request_coil_detail wrcd');
        $this->db->join('tr_warehouse_request_header wrh', 'wrh.id = wrcd.request_id');
        $this->db->where('wrh.spk_no', $spk_no);
        $this->db->where('wrh.status !=', 'Rejected');

        $result = $this->db->get()->result_array();

        return array_column($result, 'id_coil');
    }

    // ---------------------------------------------------------------
    // READ OPERATIONS
    // ---------------------------------------------------------------

    /**
     * Get single warehouse request header by ID
     *
     * @param int $request_id ID request
     * @return array|null Request row atau null
     */
    public function get_request_by_id($request_id)
    {
        return $this->db
            ->where('id', $request_id)
            ->get('tr_warehouse_request_header')
            ->row_array();
    }

    /**
     * Get coil details by request_id
     *
     * @param int $request_id ID request header
     * @return array Array of coil detail rows
     */
    public function get_coil_details($request_id)
    {
        return $this->db
            ->where('request_id', $request_id)
            ->get('tr_warehouse_request_coil_detail')
            ->result_array();
    }

    // ---------------------------------------------------------------
    // SPK COIL STATUS
    // ---------------------------------------------------------------

    /**
     * Count SPK Coil (non-Rejected) untuk SPK Material tertentu
     *
     * @param string $spk_no Nomor SPK Material
     * @return int Jumlah SPK Coil
     */
    public function get_spk_coil_count($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        $this->db->where('status !=', 'Rejected');

        return $this->db->count_all_results('tr_warehouse_request_header');
    }

    /**
     * Check apakah masih ada material dari BOM yang belum fully covered oleh coil selections
     * Membandingkan material BOM list vs coil yang sudah dipilih (grouped by id_material)
     *
     * @param string $spk_no Nomor SPK Material
     * @return bool True jika masih ada material yang belum terpenuhi
     */
    public function has_unfulfilled_material($spk_no)
    {
        // Get semua material dari BOM untuk SPK ini
        $sql_bom = "SELECT DISTINCT bd.id_material
                    FROM tr_spk_material_detail sd
                    JOIN ms_bom_header bh ON bh.id_produk = sd.id_produk_fg AND bh.is_delete = 0
                    JOIN ms_bom_detail bd ON bd.id_bom = bh.id AND bd.is_delete = 0
                    WHERE sd.spk_no = ?";

        $bom_materials = $this->db->query($sql_bom, [$spk_no])->result_array();

        if (empty($bom_materials)) {
            return false;
        }

        // Get material IDs yang sudah punya coil selection (non-Rejected)
        $sql_selected = "SELECT DISTINCT wrcd.id_material
                         FROM tr_warehouse_request_coil_detail wrcd
                         JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                         WHERE wrh.spk_no = ? AND wrh.status != 'Rejected'";

        $selected_materials = $this->db->query($sql_selected, [$spk_no])->result_array();
        $selected_ids = array_column($selected_materials, 'id_material');

        // Check if any BOM material belum ada di selections
        foreach ($bom_materials as $material) {
            if (!in_array($material['id_material'], $selected_ids)) {
                return true;
            }
        }

        return false;
    }
}
