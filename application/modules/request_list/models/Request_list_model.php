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

        // Calculate sum from detail
        $sum_qty = 0;
        $sum_weight = 0;
        foreach ($products as $p) {
            // Check possible column names for qty
            if (isset($p['target_qty'])) $sum_qty += (float)$p['target_qty'];

            // Check possible column names for weight
            if (isset($p['weight'])) $sum_weight += (float)$p['weight'];
            elseif (isset($p['total_weight'])) $sum_weight += (float)$p['total_weight'];
        }

        $header['target_qty'] = $sum_qty;
        $header['total_weight'] = $sum_weight;

        // Get BOM materials per produk
        foreach ($products as &$product) {
            $sql = "SELECT bd.id_material, bd.nm_material, bd.qty, bd.id_unit, bd.nm_unit
                    FROM ms_bom_header bh
                    JOIN ms_bom_detail bd ON bd.id_bom = bh.id
                    WHERE bh.id_produk = ? AND bh.is_delete = 0 AND bd.is_delete = 0
                    ORDER BY bd.nm_material ASC";

            $materials = $this->db->query($sql, [$product['id_produk_fg']])->result_array();

            foreach ($materials as &$mat) {
                // Stock Produksi (Gudang 1)
                $prod_row = $this->db->select('qty_stock')
                    ->where('id_material', $mat['id_material'])
                    ->where('id_gudang', 1)
                    ->get('warehouse_stock')
                    ->row();
                $mat['stock_produksi'] = $prod_row ? (float) $prod_row->qty_stock : 0;

                // Stock WIP (dari warehouse_stock_coil dengan kd_gudang = 'WIP')
                $wip_row = $this->db->select_sum('net_weight', 'qty')
                    ->where('id_material', $mat['id_material'])
                    ->where('kd_gudang', 'WIP')
                    ->where('status', 1)
                    ->get('warehouse_stock_coil')
                    ->row();
                $mat['stock_wip'] = $wip_row ? (float) $wip_row->qty : 0;
            }

            $product['materials'] = $materials;
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
        $sql_pro = "SELECT c.id, c.id_material, c.nm_material, c.kode_internal, c.no_coil, c.net_weight, c.id_gudang, c.kd_gudang, 1 as source_type,
                        (SELECT wrh.spk_coil_no 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 1 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_spkc,
                        (SELECT wrcd.scan_status 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 1 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as scan_status,
                        (SELECT wrcd.request_id 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 1 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_request_id
                    FROM warehouse_stock_coil c
                    WHERE c.id_material = ?
                    AND c.kd_gudang = 'PRO'
                    AND c.status = 1
                    AND c.status_proses = 'in_warehouse'";

        $sql_wip = "SELECT c.id, c.id_material, c.nm_material, c.kode_internal, c.no_coil, c.net_weight, c.id_gudang, c.kd_gudang, 4 as source_type,
                        (SELECT wrh.spk_coil_no 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 4 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_spkc,
                        (SELECT wrcd.scan_status 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 4 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as scan_status,
                        (SELECT wrcd.request_id 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 4 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_request_id
                    FROM warehouse_stock_coil c
                    WHERE c.id_material = ?
                    AND c.kd_gudang = 'WIP'
                    AND c.status = 1
                    AND c.status_proses = 'wip'";

        $pro_coils = $this->db->query($sql_pro, [$id_material])->result_array();
        $wip_coils = $this->db->query($sql_wip, [$id_material])->result_array();

        return array_merge($pro_coils, $wip_coils);
    }

    // ---------------------------------------------------------------
    // PACK-BASED AVAILABILITY
    // ---------------------------------------------------------------

    /**
     * Get available packs that contain a specific material
     * Exclude packs yang sudah fully assigned ke SPK Coil aktif (non-Rejected/Cancelled)
     *
     * @param string $id_material ID material
     * @param string $spk_no      SPK number (untuk context)
     * @return array Array of pack rows dengan roll_count, total_nw, total_gw, assigned info
     */
    public function get_available_packs_by_material($id_material, $spk_no)
    {
        $sql = "
            SELECT
                wp.id AS id_pack,
                wp.pack_code,
                wp.kd_gudang,
                COUNT(wsc.id) AS roll_count,
                SUM(wsc.net_weight) AS total_nw,
                SUM(wsc.gross_weight) AS total_gw,
                (
                    SELECT GROUP_CONCAT(DISTINCT wrh.spk_coil_no SEPARATOR ', ')
                    FROM tr_warehouse_request_coil_detail wrcd
                    JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                    WHERE wrcd.pack_code = wp.pack_code
                      AND wrh.status NOT IN ('Rejected', 'Cancelled')
                    LIMIT 1
                ) AS assigned_spkc,
                (
                    SELECT MAX(wrcd2.scan_status)
                    FROM tr_warehouse_request_coil_detail wrcd2
                    JOIN tr_warehouse_request_header wrh2 ON wrh2.id = wrcd2.request_id
                    WHERE wrcd2.pack_code = wp.pack_code
                      AND wrh2.status NOT IN ('Rejected', 'Cancelled')
                ) AS scan_status,
                (
                    SELECT wrcd3.request_id
                    FROM tr_warehouse_request_coil_detail wrcd3
                    JOIN tr_warehouse_request_header wrh3 ON wrh3.id = wrcd3.request_id
                    WHERE wrcd3.pack_code = wp.pack_code
                      AND wrh3.status NOT IN ('Rejected', 'Cancelled')
                    LIMIT 1
                ) AS assigned_request_id
            FROM warehouse_pack wp
            JOIN warehouse_stock_coil wsc ON wsc.id_pack = wp.id
                AND wsc.id_material = ?
                AND wsc.status = 1
                AND (wsc.is_baby_coil = 1 OR (wsc.is_baby_coil = 0 AND wsc.qty_roll <= 1))
            WHERE wp.status = 1
            GROUP BY wp.id
            ORDER BY wp.pack_code ASC
        ";

        return $this->db->query($sql, [$id_material])->result_array();
    }

    /**
     * Get all coils inside a specific pack for a given material
     *
     * @param int    $id_pack     ID pack
     * @param string $id_material ID material (optional filter, null = all materials in pack)
     * @return array Array of coil rows
     */
    public function get_coils_in_pack($id_pack, $id_material = null)
    {
        $where_material = '';
        $params = [$id_pack];
        if ($id_material) {
            $where_material = ' AND wsc.id_material = ?';
            $params[] = $id_material;
        }

        $sql = "
            SELECT wsc.id, wsc.id_material, wsc.nm_material, wsc.trade_name,
                   wsc.no_coil, wsc.kode_internal, wsc.net_weight, wsc.gross_weight,
                   wsc.length, wsc.id_gudang, wsc.kd_gudang, wsc.id_pack,
                   wsc.is_baby_coil, wsc.qty_roll,
                   wp.pack_code
            FROM warehouse_stock_coil wsc
            JOIN warehouse_pack wp ON wp.id = wsc.id_pack
            WHERE wsc.id_pack = ?
              AND wsc.status = 1
              AND (wsc.is_baby_coil = 1 OR (wsc.is_baby_coil = 0 AND wsc.qty_roll <= 1))
              {$where_material}
            ORDER BY wsc.nm_material ASC, wsc.no_coil ASC
        ";

        return $this->db->query($sql, $params)->result_array();
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
    public function check_coil_available($id_coil, $id_gudang_sumber = 0)
    {
        // Semua coil (PRO, WIP) sekarang ada di tabel warehouse_stock_coil
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
     * Generate nomor SPK Coil format [spk_no]/TRS-001
     * Query last counter dari tr_warehouse_request_header per SPK Material
     *
     * @param string $spk_no Nomor SPK Material
     * @return string Nomor SPK Coil baru (e.g. SPK-001/TRS-001)
     */
    public function generate_spk_coil_no($spk_no)
    {
        $prefix = $spk_no . '/TRS-';

        // #3: FOR UPDATE lock untuk prevent race condition nomor urut
        $sql = "SELECT spk_coil_no FROM tr_warehouse_request_header
                WHERE spk_no = ? AND spk_coil_no LIKE ?
                ORDER BY spk_coil_no DESC
                LIMIT 1
                FOR UPDATE";

        $last = $this->db->query($sql, array($spk_no, $prefix . '%'))->row();

        $next = 1;
        if ($last) {
            $parts = explode('/TRS-', $last->spk_coil_no);
            if (isset($parts[1])) {
                $next = (int) $parts[1] + 1;
            }
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
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
     * Get saved coils by SPK number
     *
     * @param string $spk_no Nomor SPK Material
     * @return array Array of coil details with request header info
     */
    public function get_saved_coils_by_spk($spk_no)
    {
        $this->db->select('wrcd.*, wrh.spk_coil_no, wrh.status as request_status');
        $this->db->from('tr_warehouse_request_coil_detail wrcd');
        $this->db->join('tr_warehouse_request_header wrh', 'wrh.id = wrcd.request_id');
        $this->db->where('wrh.spk_no', $spk_no);
        $this->db->where('wrh.status !=', 'Rejected');
        return $this->db->get()->result_array();
    }

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
     * Get request header by ID with FOR UPDATE lock (untuk double-confirm prevention)
     *
     * @param int $request_id
     * @return array|null
     */
    public function get_request_by_id_locked($request_id)
    {
        return $this->db->query(
            "SELECT * FROM tr_warehouse_request_header WHERE id = ? LIMIT 1 FOR UPDATE",
            array($request_id)
        )->row_array();
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
            ->select('d.*, c.net_weight')
            ->from('tr_warehouse_request_coil_detail d')
            ->join('warehouse_stock_coil c', 'c.id = d.id_coil', 'left')
            ->where('d.request_id', $request_id)
            ->get()
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

    // ---------------------------------------------------------------
    // SCAN & CONFIRM SPK COIL OPERATIONS (Migrated from Confirm_spk_coil_model)
    // ---------------------------------------------------------------

    /**
     * Get pending SPK Coils (request headers) by SPK Material
     */
    public function get_pending_spkc_by_spk($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        $this->db->where('status', 'Material On Load');
        return $this->db->get('tr_warehouse_request_header')->result_array();
    }

    /**
     * Get ALL SPK Coils (request headers) by SPK Material — untuk print (hanya yang confirmed)
     */
    public function get_all_spkc_by_spk($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        $this->db->where('status', 'Material Confirmed');
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get('tr_warehouse_request_header')->result_array();
    }

    public function update_scan_status($detail_id, $data)
    {
        $this->db->where('id', $detail_id);
        return $this->db->update('tr_warehouse_request_coil_detail', $data);
    }

    public function find_coil_by_kode_internal($request_id, $kode_internal, $nm_gudang)
    {
        $this->db->select('d.*, w.kd_gudang, w.nm_gudang');
        $this->db->from('tr_warehouse_request_coil_detail d');
        $this->db->join('warehouse w', 'w.id = d.id_gudang_sumber', 'left');
        $this->db->where('d.request_id', $request_id);
        $this->db->where('d.kode_internal', $kode_internal);
        $this->db->where('LOWER(w.nm_gudang)', strtolower($nm_gudang));

        return $this->db->get()->row_array();
    }

    public function all_coils_scanned($request_id)
    {
        $this->db->where('request_id', $request_id);
        $this->db->where('scan_status', 0);
        $count = $this->db->count_all_results('tr_warehouse_request_coil_detail');

        return $count == 0;
    }

    public function reduce_coil_stock($id_coil, $kode_trans, $created_by)
    {
        $coil = $this->db->query(
            "SELECT * FROM warehouse_stock_coil WHERE id = ? AND status = 1 LIMIT 1 FOR UPDATE",
            [$id_coil]
        )->row_array();

        if (!$coil) {
            return false;
        }

        $source_id_gudang = $coil['id_gudang'];
        $source_kd_gudang = $coil['kd_gudang'];
        $is_from_wip       = ($coil['status_proses'] === 'wip');
        $now               = date('Y-m-d H:i:s');
        $today             = date('Y-m-d');

        // ========================================================================
        // SKENARIO B: Coil berasal dari WIP -> hanya di-BOOKING, TIDAK berubah
        // (TIDAK ADA PERUBAHAN DI SINI)
        // ========================================================================
        if ($is_from_wip) {
            $harga_lama = (float) $coil['harga_beli'];

            $this->db->where('id', $id_coil)->update('warehouse_stock_coil', [
                'status_proses' => 'booked',
            ]);

            $this->db->insert('warehouse_history', [
                'id_material'     => $coil['id_material'],
                'nm_material'     => $coil['nm_material'],
                'id_gudang'       => $source_id_gudang,
                'kd_gudang'       => $source_kd_gudang,
                'id_gudang_dari'  => $source_id_gudang,
                'kd_gudang_dari'  => $source_kd_gudang,
                'id_gudang_ke'    => $source_id_gudang,
                'kd_gudang_ke'    => $source_kd_gudang,
                'qty_stock_awal'  => 0,
                'qty_stock_akhir' => 0,
                'no_ipp'          => $kode_trans,
                'jumlah_mat'      => $coil['net_weight'],
                'ket'             => 'WIP coil dibooking untuk SPK ' . $kode_trans . ' (Coil: ' . $coil['no_coil'] . ', tetap di WIP)',
                'no_coil'         => $coil['no_coil'],
                'harga_beli'      => $harga_lama,
                'total_harga'     => isset($coil['total_nilai']) ? $coil['total_nilai'] : 0,
                'saldo_awal'      => 0,
                'saldo_akhir'     => 0,
                'harga_baru'      => $harga_lama,
                'harga_lama'      => $harga_lama,
                'update_by'       => $created_by,
                'update_date'     => $now,
            ]);

            $this->db->insert('warehouse_stock_transaction_detail', [
                'kode_trans'     => $kode_trans,
                'id_material'    => $coil['id_material'],
                'nm_material'    => $coil['nm_material'],
                'id_gudang'      => $source_id_gudang,
                'kd_gudang'      => $source_kd_gudang,
                'no_coil'        => $coil['no_coil'],
                'parent_no_coil' => isset($coil['parent_coil_id']) ? $coil['parent_coil_id'] : null,
                'kode_internal'  => $coil['kode_internal'],
                'gross_weight'   => !empty($coil['gross_weight']) ? $coil['gross_weight'] : 0,
                'net_weight'     => !empty($coil['net_weight'])   ? $coil['net_weight']   : 0,
                'length'         => !empty($coil['length'])       ? $coil['length']       : 0,
                'price_per_coil' => $harga_lama,
                'cost_book'      => $harga_lama,
                'status_qc'      => 'OUT',
                'to_status'      => 'booked',
                'created_at'     => $now,
            ]);

            $coil_snap = $this->db->query("
            SELECT id FROM warehouse_coil_per_day
            WHERE id_material = ? AND id_gudang = ? AND no_coil = ? AND DATE(hist_date) = ?
            LIMIT 1
        ", [$coil['id_material'], $source_id_gudang, $coil['no_coil'], $today])->row();

            $snap_data = [
                'nm_material'   => $coil['nm_material'],
                'kd_gudang'     => $source_kd_gudang,
                'kode_internal' => $coil['kode_internal'],
                'gross_weight'  => $coil['gross_weight'],
                'net_weight'    => $coil['net_weight'],
                'length'        => $coil['length'],
                'harga_beli'    => $harga_lama,
                'total_nilai'   => $coil['net_weight'] * $harga_lama,
                'status'        => 'BOOKED',
                'hist_date'     => $now,
                'hist_by'       => $created_by,
            ];

            if (empty($coil_snap)) {
                $this->db->insert('warehouse_coil_per_day', array_merge([
                    'id_material' => $coil['id_material'],
                    'id_gudang'   => $source_id_gudang,
                    'no_coil'     => $coil['no_coil'],
                ], $snap_data));
            } else {
                $this->db->update('warehouse_coil_per_day', $snap_data, ['id' => $coil_snap->id]);
            }

            return [
                'id_material' => $coil['id_material'],
                'nm_material' => $coil['nm_material'],
                'id_gudang'   => $source_id_gudang,
                'kd_gudang'   => $source_kd_gudang,
                'from_wip'    => true,
                'net_weight'  => $coil['net_weight'],
                'qty_awal'    => 0,
                'qty_akhir'   => 0,
                'saldo_awal'  => 0,
                'saldo_akhir' => 0,
                'total_nilai' => $coil['net_weight'] * $harga_lama,
                'costbook'    => $harga_lama,
                'harga_lama'  => $harga_lama,
            ];
        }

        // ========================================================================
        // SKENARIO A: Coil normal dari PRO/SLI -> pindah fisik ke PRT
        // RUMUS BARU: total_harga, saldo_akhir, harga_baru berbasis pengurangan
        // dari nilai warehouse_stock SEBELUM transaksi (bukan recalc SUM lagi)
        // ========================================================================
        $stock_source = $this->db->query(
            "SELECT * FROM warehouse_stock WHERE code_lv4 = ? AND kd_gudang = ? LIMIT 1 FOR UPDATE",
            [$coil['id_material'], $source_kd_gudang]
        )->row_array();

        $qty_awal_source   = $stock_source ? (float) $stock_source['qty_stock']   : 0;
        $saldo_awal_source = $stock_source ? (float) $stock_source['total_nilai'] : 0;
        $harga_lama        = $stock_source ? (float) $stock_source['harga_beli']  : 0; // harga_baru sebelumnya

        $qty_transaksi = (float) $coil['net_weight'];

        // ===== RUMUS BARU: total_harga = qty_transaksi × harga_lama =====
        $total_harga = $qty_transaksi * $harga_lama;

        // ===== RUMUS BARU: qty_akhir & saldo_akhir gudang sumber (dikurangi) =====
        $qty_akhir_source   = $qty_awal_source - $qty_transaksi;
        $saldo_akhir_source = $saldo_awal_source - $total_harga;

        // ===== RUMUS BARU: harga_baru = saldo_akhir / qty_akhir =====
        // Guard divide-by-zero / qty_akhir <= 0 (misal stok jadi habis / minus karena data tidak konsisten)
        if ($qty_akhir_source > 0) {
            $harga_baru_source = $saldo_akhir_source / $qty_akhir_source;
        } else {
            $harga_baru_source = $harga_lama; // fallback: pertahankan harga lama kalau qty habis/negatif
            if ($qty_akhir_source < 0) {
                log_message('error', "reduce_coil_stock: qty_akhir_source NEGATIF ({$qty_akhir_source}) untuk material {$coil['id_material']} gudang {$source_kd_gudang}. Kemungkinan data qty_awal di header tidak sinkron dengan kondisi real.");
            }
        }

        // ===== UPDATE COIL: pindah ke PRT + set stage =====
        $this->db->where('id', $id_coil)->update('warehouse_stock_coil', [
            'id_gudang'     => 3,
            'kd_gudang'     => 'PRT',
            'status_proses' => 'in_transit',
        ]);

        // ===== UPDATE warehouse_stock GUDANG SUMBER =====
        if ($stock_source) {
            $this->db->where('code_lv4', $coil['id_material'])
                ->where('kd_gudang', $source_kd_gudang)
                ->set('qty_stock', $qty_akhir_source)
                ->set('total_nilai', $saldo_akhir_source)
                ->set('harga_beli', $harga_baru_source)
                ->update('warehouse_stock');
        } else {
            log_message('warning', "reduce_coil_stock: stock_source tidak ditemukan untuk material {$coil['id_material']} gudang {$source_kd_gudang}, update warehouse_stock sumber di-skip.");
        }

        // ===== RUMUS BARU: gudang PRT (dibalik — ditambah) =====
        $stock_prt = $this->db->query(
            "SELECT * FROM warehouse_stock WHERE code_lv4 = ? AND kd_gudang = ? LIMIT 1 FOR UPDATE",
            [$coil['id_material'], 'PRT']
        )->row_array();

        $qty_awal_prt   = $stock_prt ? (float) $stock_prt['qty_stock']   : 0;
        $saldo_awal_prt = $stock_prt ? (float) $stock_prt['total_nilai'] : 0;

        $qty_akhir_prt   = $qty_awal_prt + $qty_transaksi;
        $saldo_akhir_prt = $saldo_awal_prt + $total_harga; // total_harga SAMA dengan yang di sumber

        $harga_baru_prt = $qty_akhir_prt > 0
            ? ($saldo_akhir_prt / $qty_akhir_prt)
            : $harga_lama;

        if ($stock_prt) {
            $this->db->where('code_lv4', $coil['id_material'])
                ->where('kd_gudang', 'PRT')
                ->set('qty_stock', $qty_akhir_prt)
                ->set('total_nilai', $saldo_akhir_prt)
                ->set('harga_beli', $harga_baru_prt)
                ->update('warehouse_stock');
        } else {
            $this->db->insert('warehouse_stock', [
                'code_lv4'    => $coil['id_material'],
                'nm_material' => $coil['nm_material'],
                'id_gudang'   => 3,
                'kd_gudang'   => 'PRT',
                'qty_stock'   => $qty_akhir_prt,     // = qty_transaksi (karena qty_awal_prt = 0)
                'total_nilai' => $saldo_akhir_prt,   // = total_harga
                'harga_beli'  => $harga_baru_prt,    // = harga_lama (karena qty_awal_prt = 0)
            ]);
        }

        // ===== LEDGER: warehouse_history =====
        // Catatan: harga_baru di sini merepresentasikan harga_baru SISI SUMBER
        // (harga_baru_prt tersimpan terpisah di header warehouse_stock PRT)
        $this->db->insert('warehouse_history', [
            'id_material'     => $coil['id_material'],
            'nm_material'     => $coil['nm_material'],
            'id_gudang'       => $source_id_gudang,
            'kd_gudang'       => $source_kd_gudang,
            'id_gudang_dari'  => $source_id_gudang,
            'kd_gudang_dari'  => $source_kd_gudang,
            'id_gudang_ke'    => 3,
            'kd_gudang_ke'    => 'PRT',
            'qty_stock_awal'  => $qty_awal_source,
            'qty_stock_akhir' => $qty_akhir_source,
            'no_ipp'          => $kode_trans,
            'jumlah_mat'      => $coil['net_weight'],
            'ket'             => 'Coil pindah ke PRT via SPK ' . $kode_trans . ' (Coil: ' . $coil['no_coil'] . ', dari ' . $source_kd_gudang . ')',
            'no_coil'         => $coil['no_coil'],
            'harga_beli'      => $harga_lama,
            'total_harga'     => $total_harga,          // <-- pakai hasil rumus baru
            'saldo_awal'      => $saldo_awal_source,
            'saldo_akhir'     => $saldo_akhir_source,    // <-- pakai hasil rumus baru
            'harga_baru'      => $harga_baru_source,     // <-- pakai hasil rumus baru
            'harga_lama'      => $harga_lama,
            'update_by'       => $created_by,
            'update_date'     => $now,
        ]);

        // ===== TRANSACTION DETAIL: 2 baris — OUT dari sumber, IN ke PRT =====
        // cost_book tetap harga_lama (harga barang yang benar-benar berpindah,
        // bukan harga rata-rata baru hasil rumus)
        $this->db->insert('warehouse_stock_transaction_detail', [
            'kode_trans'     => $kode_trans,
            'id_material'    => $coil['id_material'],
            'nm_material'    => $coil['nm_material'],
            'id_gudang'      => $source_id_gudang,
            'kd_gudang'      => $source_kd_gudang,
            'no_coil'        => $coil['no_coil'],
            'parent_no_coil' => null,
            'kode_internal'  => $coil['kode_internal'],
            'gross_weight'   => !empty($coil['gross_weight']) ? $coil['gross_weight'] : 0,
            'net_weight'     => !empty($coil['net_weight'])   ? $coil['net_weight']   : 0,
            'length'         => !empty($coil['length'])       ? $coil['length']       : 0,
            'price_per_coil' => !empty($coil['harga_beli']) ? $coil['harga_beli'] : 0,
            'cost_book'      => $harga_lama,
            'status_qc'      => 'OUT',
            'to_status'      => 'in_transit',
            'created_at'     => $now,
        ]);

        $this->db->insert('warehouse_stock_transaction_detail', [
            'kode_trans'     => $kode_trans,
            'id_material'    => $coil['id_material'],
            'nm_material'    => $coil['nm_material'],
            'id_gudang'      => 3,
            'kd_gudang'      => 'PRT',
            'no_coil'        => $coil['no_coil'],
            'parent_no_coil' => null,
            'kode_internal'  => $coil['kode_internal'],
            'gross_weight'   => !empty($coil['gross_weight']) ? $coil['gross_weight'] : 0,
            'net_weight'     => !empty($coil['net_weight'])   ? $coil['net_weight']   : 0,
            'length'         => !empty($coil['length'])       ? $coil['length']       : 0,
            'price_per_coil' => !empty($coil['harga_beli']) ? $coil['harga_beli'] : 0,
            'cost_book'      => $harga_lama,
            'status_qc'      => 'IN',
            'to_status'      => 'in_transit',
            'created_at'     => $now,
        ]);

        // ===== SNAPSHOT HARIAN: sisi OUT dari gudang sumber =====
        $coil_snap_out = $this->db->query("
        SELECT id FROM warehouse_coil_per_day
        WHERE id_material = ? AND id_gudang = ? AND no_coil = ? AND DATE(hist_date) = ?
        LIMIT 1
    ", [$coil['id_material'], $source_id_gudang, $coil['no_coil'], $today])->row();

        $coil_snap_out_data = [
            'nm_material'   => $coil['nm_material'],
            'kd_gudang'     => $source_kd_gudang,
            'kode_internal' => $coil['kode_internal'],
            'gross_weight'  => $coil['gross_weight'],
            'net_weight'    => $coil['net_weight'],
            'length'        => $coil['length'],
            'harga_beli'    => $harga_lama,
            'total_nilai'   => $coil['net_weight'] * $harga_lama,
            'status'        => 'OUT',
            'hist_date'     => $now,
            'hist_by'       => $created_by,
        ];

        if (empty($coil_snap_out)) {
            $this->db->insert('warehouse_coil_per_day', array_merge([
                'id_material' => $coil['id_material'],
                'id_gudang'   => $source_id_gudang,
                'no_coil'     => $coil['no_coil'],
            ], $coil_snap_out_data));
        } else {
            $this->db->update('warehouse_coil_per_day', $coil_snap_out_data, ['id' => $coil_snap_out->id]);
        }

        // ===== SNAPSHOT HARIAN: sisi IN ke gudang PRT =====
        $coil_snap_in = $this->db->query("
        SELECT id FROM warehouse_coil_per_day
        WHERE id_material = ? AND id_gudang = ? AND no_coil = ? AND DATE(hist_date) = ?
        LIMIT 1
    ", [$coil['id_material'], 3, $coil['no_coil'], $today])->row();

        $coil_snap_in_data = [
            'nm_material'   => $coil['nm_material'],
            'kd_gudang'     => 'PRT',
            'kode_internal' => $coil['kode_internal'],
            'gross_weight'  => $coil['gross_weight'],
            'net_weight'    => $coil['net_weight'],
            'length'        => $coil['length'],
            'harga_beli'    => $harga_lama,
            'total_nilai'   => $coil['net_weight'] * $harga_lama,
            'status'        => 'IN',
            'hist_date'     => $now,
            'hist_by'       => $created_by,
        ];

        if (empty($coil_snap_in)) {
            $this->db->insert('warehouse_coil_per_day', array_merge([
                'id_material' => $coil['id_material'],
                'id_gudang'   => 3,
                'no_coil'     => $coil['no_coil'],
            ], $coil_snap_in_data));
        } else {
            $this->db->update('warehouse_coil_per_day', $coil_snap_in_data, ['id' => $coil_snap_in->id]);
        }

        return [
            'id_material'   => $coil['id_material'],
            'nm_material'   => $coil['nm_material'],
            'id_gudang'     => $source_id_gudang,
            'kd_gudang'     => $source_kd_gudang,
            'qty_awal'      => $qty_awal_source,
            'qty_akhir'     => $qty_akhir_source,
            'saldo_awal'    => $saldo_awal_source,
            'saldo_akhir'   => $saldo_akhir_source,
            'net_weight'    => $coil['net_weight'],
            'total_nilai'   => $total_harga,        // <-- pakai hasil rumus baru
            'costbook'      => $harga_baru_source,  // <-- harga baru hasil rumus (sisi sumber)
            'harga_lama'    => $harga_lama,
        ];
    }

    public function insert_transaction_summary($data)
    {
        return $this->db->insert('warehouse_stock_transaction_summary', $data);
    }

    public function get_coil_source_data($id_coil)
    {
        $this->db->where('id', $id_coil);
        return $this->db->get('warehouse_stock_coil')->row_array();
    }

    public function update_request_status($request_id, $data)
    {
        $this->db->where('id', $request_id);
        return $this->db->update('tr_warehouse_request_header', $data);
    }

    public function remove_coil_from_spkc($request_id, $id_coil)
    {
        $this->db->where('request_id', $request_id);
        $this->db->where('id_coil', $id_coil);
        return $this->db->delete('tr_warehouse_request_coil_detail');
    }

    public function check_and_cancel_empty_spkc($request_id)
    {
        $this->db->where('request_id', $request_id);
        $count = $this->db->count_all_results('tr_warehouse_request_coil_detail');
        if ($count == 0) {
            $this->db->where('id', $request_id);
            $this->db->update('tr_warehouse_request_header', array('status' => 'Cancelled'));
        }
    }

    /**
     * Get saved SPK Coils (headers + details) for a given SPK Material
     *
     * @param string $spk_no
     * @return array Array of SPK Coil headers with 'coils' key containing details
     */
    public function get_saved_spk_coils_grouped($spk_no)
    {
        $headers = $this->db
            ->where('spk_no', $spk_no)
            ->where('status !=', 'Cancelled')
            ->where('status !=', 'Rejected')
            ->order_by('id', 'ASC')
            ->get('tr_warehouse_request_header')
            ->result_array();

        if (empty($headers)) {
            return array();
        }

        foreach ($headers as &$header) {
            $this->db->select('wrcd.*, w.nm_gudang');
            $this->db->from('tr_warehouse_request_coil_detail wrcd');
            $this->db->join('warehouse w', 'w.id = wrcd.id_gudang_sumber', 'left');
            $this->db->where('wrcd.request_id', $header['id']);
            $header['coils'] = $this->db->get()->result_array();
        }

        return $headers;
    }

    /**
     * Delete SPK Coil header & details by request ID, and update SPK Material status if empty
     *
     * @param int $request_id
     * @return array Array containing status (bool) and message (string)
     */
    public function delete_spk_coil_by_id($request_id)
    {
        $header = $this->db->where('id', $request_id)->get('tr_warehouse_request_header')->row_array();
        if (!$header) {
            return array('status' => false, 'message' => 'SPK Coil tidak ditemukan.');
        }

        if (in_array($header['status'], array('Material Confirmed', 'Confirmed', 'Released'))) {
            return array('status' => false, 'message' => 'SPK Coil tidak dapat dihapus karena status sudah ' . $header['status']);
        }

        // Cek apakah ada coil non-WIP yang sudah discan manual (coil WIP auto-scan boleh dihapus)
        $scanned_count = $this->db
            ->where('request_id', $request_id)
            ->where('scan_status', 1)
            ->where('id_gudang_sumber !=', 4)
            ->count_all_results('tr_warehouse_request_coil_detail');

        if ($scanned_count > 0) {
            return array('status' => false, 'message' => 'SPK Coil tidak dapat dihapus karena terdapat coil (non-WIP) yang sudah discan.');
        }

        $spk_no = $header['spk_no'];

        $this->db->trans_start();

        // Delete coil details
        $this->db->where('request_id', $request_id)->delete('tr_warehouse_request_coil_detail');

        // Delete request header
        $this->db->where('id', $request_id)->delete('tr_warehouse_request_header');

        // Check remaining active SPK Coils for this spk_no
        $remaining_count = $this->db
            ->where('spk_no', $spk_no)
            ->where('status !=', 'Cancelled')
            ->where('status !=', 'Rejected')
            ->count_all_results('tr_warehouse_request_header');

        if ($remaining_count == 0) {
            $this->db->where('spk_no', $spk_no)->update('tr_spk_material_header', array(
                'status' => 'Material Requested'
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('status' => false, 'message' => 'Gagal menghapus SPK Coil.');
        }

        return array('status' => true, 'message' => 'SPK Coil ' . $header['spk_coil_no'] . ' berhasil dihapus.', 'spk_no' => $spk_no);
    }

    /**
     * Delete 1 coil item from SPK Coil detail by detail ID
     *
     * @param int $detail_id
     * @return array Array containing status (bool) and message (string)
     */
    public function delete_spk_coil_detail_item($detail_id)
    {
        $detail = $this->db->where('id', $detail_id)->get('tr_warehouse_request_coil_detail')->row_array();
        if (!$detail) {
            return array('status' => false, 'message' => 'Detail coil tidak ditemukan.');
        }

        $request_id = $detail['request_id'];
        $header     = $this->db->where('id', $request_id)->get('tr_warehouse_request_header')->row_array();

        if (!$header) {
            return array('status' => false, 'message' => 'SPK Coil tidak ditemukan.');
        }

        if (in_array($header['status'], array('Material Confirmed', 'Confirmed', 'Released'))) {
            return array('status' => false, 'message' => 'Item tidak dapat dihapus karena status SPK Coil sudah ' . $header['status']);
        }

        if (isset($detail['scan_status']) && $detail['scan_status'] == 1) {
            // Coil dari WIP (id_gudang_sumber=4) boleh dihapus meskipun scan_status=1 (auto-scan)
            // Hanya coil non-WIP yang sudah discan manual yang tidak bisa dihapus
            $is_wip = (isset($detail['id_gudang_sumber']) && $detail['id_gudang_sumber'] == 4);
            if (!$is_wip) {
                return array('status' => false, 'message' => 'Coil ' . (!empty($detail['no_coil']) ? $detail['no_coil'] : $detail['kode_internal']) . ' sudah discan dan tidak dapat dihapus.');
            }
        }

        $spk_no = $header['spk_no'];
        $no_coil = !empty($detail['no_coil']) ? $detail['no_coil'] : $detail['kode_internal'];

        $this->db->trans_start();

        // Delete single detail item
        $this->db->where('id', $detail_id)->delete('tr_warehouse_request_coil_detail');

        // Check if SPK Coil has 0 items remaining -> cancel SPK Coil
        $this->check_and_cancel_empty_spkc($request_id);

        // Check remaining active SPK Coils for this spk_no
        $remaining_count = $this->db
            ->where('spk_no', $spk_no)
            ->where('status !=', 'Cancelled')
            ->where('status !=', 'Rejected')
            ->count_all_results('tr_warehouse_request_header');

        if ($remaining_count == 0) {
            $this->db->where('spk_no', $spk_no)->update('tr_spk_material_header', array(
                'status' => 'Material Requested'
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('status' => false, 'message' => 'Gagal menghapus item coil.');
        }

        return array('status' => true, 'message' => 'Coil ' . $no_coil . ' berhasil dikeluarkan dari SPK Coil.', 'spk_no' => $spk_no);
    }

    /**
     * Add batch coils to an existing SPK Coil request_id
     *
     * @param int $request_id
     * @param array $coils Array of coil records to insert
     * @return array Array containing status (bool) and message (string)
     */
    public function add_coils_to_spkc($request_id, $coils)
    {
        $header = $this->db->where('id', $request_id)->get('tr_warehouse_request_header')->row_array();
        if (!$header) {
            return array('status' => false, 'message' => 'SPK Coil tidak ditemukan.');
        }

        if (in_array($header['status'], array('Material Confirmed', 'Confirmed', 'Released', 'Cancelled', 'Rejected'))) {
            return array('status' => false, 'message' => 'Tidak dapat menambah coil ke SPK Coil dengan status: ' . $header['status']);
        }

        if (empty($coils) || !is_array($coils)) {
            return array('status' => false, 'message' => 'Minimal 1 coil harus dipilih.');
        }

        $this->db->trans_start();

        $detail_records = array();
        foreach ($coils as $coil) {
            $assigned_req_id = isset($coil['assigned_request_id']) ? $coil['assigned_request_id'] : '';
            if (!empty($assigned_req_id) && $assigned_req_id != $request_id) {
                $this->remove_coil_from_spkc($assigned_req_id, $coil['id_coil']);
                $this->check_and_cancel_empty_spkc($assigned_req_id);
            }

            $id_gudang_sumber_val = isset($coil['id_gudang_sumber']) ? (int) $coil['id_gudang_sumber'] : 0;

            $detail_records[] = array(
                'request_id'       => $request_id,
                'id_coil'          => isset($coil['id_coil']) ? $coil['id_coil'] : 0,
                'id_material'      => isset($coil['id_material']) ? $coil['id_material'] : '',
                'nm_material'      => isset($coil['nm_material']) ? $coil['nm_material'] : '',
                'kode_internal'    => isset($coil['kode_internal']) ? $coil['kode_internal'] : '',
                'no_coil'          => isset($coil['no_coil']) ? $coil['no_coil'] : '',
                'id_gudang_sumber' => $id_gudang_sumber_val,
                // Coil dari WIP (id_gudang_sumber=4) auto scan, tidak perlu scan manual
                'scan_status'      => ($id_gudang_sumber_val == 4) ? 1 : 0,
            );
        }

        if (!empty($detail_records)) {
            $this->insert_coil_details($detail_records);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('status' => false, 'message' => 'Gagal menambah coil ke SPK Coil.');
        }

        return array('status' => true, 'message' => count($detail_records) . ' coil berhasil ditambahkan ke ' . $header['spk_coil_no'] . '.', 'spk_no' => $header['spk_no']);
    }
}
