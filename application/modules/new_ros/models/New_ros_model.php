<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class New_ros_model extends BF_Model
{
    protected $table_name    = 'tr_ros_header';
    protected $key           = 'id';
    protected $created_field = 'created_on';
    protected $modified_field = 'modified_on';
    protected $date_format   = 'datetime';
    protected $log_user      = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate nomor ROS baru: NROS-MM-YY-000001
     */
    public function generate_no_ros()
    {
        $prefix = 'NROS-' . date('m-y');
        $row = $this->db->query("SELECT MAX(id) AS max_id FROM tr_ros_header WHERE id LIKE '%" . $prefix . "%'")->row();
        $urutan = 0;
        if ($row && $row->max_id) {
            $urutan = (int) substr($row->max_id, 11, 6);
        }
        $urutan++;
        return $prefix . '-' . sprintf("%06d", $urutan);
    }

    /**
     * DataTables server-side untuk list ROS
     */
    // public function get_datatables()
    // {
    //     $requestData = $_REQUEST;
    //     $search      = $requestData['search']['value'];
    //     $col_index   = $requestData['order'][0]['column'];
    //     $col_dir     = $requestData['order'][0]['dir'];
    //     $start       = $requestData['start'];
    //     $length      = $requestData['length'];

    //     $columns_order = [0 => 'a.id', 1 => 'a.id', 2 => 'a.no_po', 3 => 'a.nm_supplier', 4 => 'a.nilai_po_pib_rp'];

    //     $where = "1=1";
    //     if ($search) {
    //         $like = $this->db->escape_like_str($search);
    //         $where .= " AND (a.id LIKE '%{$like}%' OR a.no_po LIKE '%{$like}%' OR a.nm_supplier LIKE '%{$like}%' OR b.no_surat LIKE '%{$like}%')";
    //     }

    //     $sql_base = "
    //         SELECT a.*, b.no_surat
    //         FROM tr_ros_header a
    //         LEFT JOIN tr_purchase_order b ON b.no_po = a.no_po
    //         WHERE {$where}
    //     ";

    //     $totalData     = $this->db->query($sql_base)->num_rows();
    //     $totalFiltered = $totalData;

    //     $order_col = isset($columns_order[$col_index]) ? $columns_order[$col_index] : 'a.created_on';
    //     $sql = $sql_base . " ORDER BY a.created_on DESC, {$order_col} {$col_dir} LIMIT {$start}, {$length}";
    //     $query = $this->db->query($sql);

    //     return [
    //         'totalData'     => $totalData,
    //         'totalFiltered' => $totalFiltered,
    //         'query'         => $query
    //     ];
    // }

    public function get_datatables($tab = 'draft')
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'];
        $col_index   = $requestData['order'][0]['column'];
        $col_dir     = $requestData['order'][0]['dir'];
        $start       = $requestData['start'];
        $length      = $requestData['length'];

        $columns_order = [
            0 => 'a.id',
            1 => 'a.id',
            2 => 'a.no_po',
            3 => 'a.nm_supplier',
            4 => 'a.nilai_po_pib_rp'
        ];

        // Filter status berdasarkan tab
        // - open              : status = 0 (draft, belum di-close)
        // - payment_process   : status = 1 & status_payment = proses_payment (Import menunggu pembayaran)
        // - payment_completed : status = 1 & status_payment = close (Lokal / Import lunas)
        // (nilai lama 'draft'/'close' tetap didukung untuk kompatibilitas)
        if ($tab === 'open' || $tab === 'draft') {
            $where = "a.status = '0'";
        } elseif ($tab === 'payment_process') {
            $where = "a.status = '1' AND a.status_payment = 'proses_payment'";
        } elseif ($tab === 'payment_completed') {
            $where = "a.status = '1' AND a.status_payment = 'close'";
        } else {
            // fallback ('close' lama) → semua yang sudah final
            $where = "a.status = '1'";
        }

        if ($search) {
            $like = $this->db->escape_like_str($search);
            $where .= " AND (a.id LIKE '%{$like}%' OR a.no_po LIKE '%{$like}%' OR a.nm_supplier LIKE '%{$like}%' OR b.no_surat LIKE '%{$like}%')";
        }

        $sql_base = "
        SELECT a.*, b.no_surat
        FROM tr_ros_header a
        LEFT JOIN tr_purchase_order b ON b.no_po = a.no_po
        WHERE {$where}
    ";

        $totalData     = $this->db->query($sql_base)->num_rows();
        $totalFiltered = $totalData;

        $order_col = isset($columns_order[$col_index]) ? $columns_order[$col_index] : 'a.created_on';
        $sql = $sql_base . " ORDER BY a.created_on DESC, {$order_col} {$col_dir} LIMIT {$start}, {$length}";
        $query = $this->db->query($sql);

        return [
            'totalData'     => $totalData,
            'totalFiltered' => $totalFiltered,
            'query'         => $query
        ];
    }

    /**
     * Ambil list PO yang belum dipakai di New ROS
     */
    public function list_available_po()
    {
        return $this->db->query("
            SELECT a.no_po, a.no_surat, a.id_suplier, b.nama as nm_supplier
            FROM tr_purchase_order a
            LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier
            LEFT JOIN tr_ros_header c ON c.no_po = a.no_po
            WHERE a.status = 2
            AND c.id IS NULL
            ORDER BY a.no_po DESC
        ")->result_array();
    }

    /**
     * Ambil detail material dari PO
     */
    public function get_po_materials($no_po)
    {
        return $this->db->query("
            SELECT 
                a.id as id_po_detail,
                a.idmaterial,
                a.namamaterial as nm_barang,
                c.trade_name as nm_alias,
                c.nama as nm_erp,
                a.qty as kg_unit,
                a.hargasatuan as unit_price_usd,
                a.jumlahharga as total_value_usd,
                c.hscode as hscode_id,
                b.matauang as currency,
                b.id_suplier
            FROM dt_trans_po a
            LEFT JOIN tr_purchase_order b ON b.no_po = a.no_po
            LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.idmaterial OR c.id = a.idmaterial
            WHERE a.no_po = ?
            ORDER BY a.id ASC
        ", [$no_po])->result_array();
    }

    /**
     * Ambil BM% dari HS Code berdasarkan material dan origin supplier
     * Lookup: new_inventory_4.hscode -> hscode_origin (by supplier country) -> hscode_bm_origin.bm_value
     */
    // public function get_bm_persen($id_material, $id_supplier)
    // {
    //     // Ambil hscode dari material
    //     $material = $this->db->select('hscode')->get_where('new_inventory_4', ['code_lv4' => $id_material])->row();
    //     if (!$material || !$material->hscode) {
    //         // Coba cari by id
    //         $material = $this->db->select('hscode')->get_where('new_inventory_4', ['id' => $id_material])->row();
    //     }
    //     if (!$material || !$material->hscode) return 0;

    //     // Ambil country dari supplier
    //     $supplier = $this->db->select('id_country')->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row();
    //     if (!$supplier || !$supplier->id_country) return 0;

    //     // Cari country id di tabel countries berdasarkan iso3
    //     $country = $this->db->select('id')->get_where('countries', ['country_code' => $supplier->id_country])->row();
    //     if (!$country) {
    //         $country = $this->db->select('id')->get_where('country_all', ['iso3' => $supplier->id_country])->row();
    //     }

    //     $country_id = $country ? $country->id : null;

    //     // Cari hscode_origin
    //     $origin = $this->db->get_where('hscode_origin', [
    //         'hscode_id' => $material->hscode,
    //         'origin_id' => $country_id
    //     ])->row();

    //     if (!$origin) return 0;

    //     // Ambil BM value (ambil yang pertama / BM utama)
    //     $bm = $this->db->select('bm_value')
    //         ->where('hscode_origin_id', $origin->id)
    //         ->order_by('id', 'ASC')
    //         ->get('hscode_bm_origin')
    //         ->row();

    //     return $bm ? (float) $bm->bm_value : 0;
    // }

    public function get_bm_persen($id_material, $id_supplier)
    {
        // 1. Ambil hscode dari material
        $material = $this->db->select('hscode')->get_where('new_inventory_4', ['code_lv4' => $id_material])->row();
        if (!$material || !$material->hscode) {
            $material = $this->db->select('hscode')->get_where('new_inventory_4', ['id' => $id_material])->row();
        }

        if (!$material || !$material->hscode) return 0;

        // 2. Cari hscode_origin TANPA melihat origin_id (Negara)
        // Kita ambil yang pertama ditemukan (limit 1)
        $origin = $this->db->get_where('hscode_origin', [
            'hscode_id' => $material->hscode
        ])->row();

        if (!$origin) return 0;

        // 3. Ambil BM value
        $bm = $this->db->select('bm_value')
            ->where('hscode_origin_id', $origin->id)
            ->order_by('id', 'ASC')
            ->get('hscode_bm_origin')
            ->row();

        return $bm ? (float) $bm->bm_value : 0;
    }

    /**
     * Ambil data header ROS lengkap
     */
    public function get_header($id_ros)
    {
        return $this->db->get_where('tr_ros_header', ['id' => $id_ros])->row_array();
    }

    /**
     * Ambil materials untuk ROS tertentu
     */
    public function get_materials($id_ros)
    {
        return $this->db->get_where('tr_ros_material', ['id_ros' => $id_ros])->result_array();
    }

    /**
     * Ambil coils untuk material tertentu
     */
    public function get_coils($id_ros_material)
    {
        return $this->db->get_where('tr_ros_material_coil', ['id_ros_material' => $id_ros_material])->result_array();
    }

    /**
     * Ambil others cost
     */
    public function get_others($id_ros)
    {
        return $this->db->get_where('tr_ros_others', ['id_ros' => $id_ros])->result_array();
    }

    /**
     * Total others cost
     */
    public function get_total_others($id_ros)
    {
        $row = $this->db->select('IFNULL(SUM(nilai), 0) as total')
            ->get_where('tr_ros_others', ['id_ros' => $id_ros])
            ->row();
        return $row ? (float) $row->total : 0;
    }

    /**
     * Generate pack code: PCK-YYMM-XXX (global counter)
     */
    public function generate_pack_code()
    {
        $prefix = 'PCK-' . date('ym');
        $row = $this->db->query("SELECT MAX(pack_code) AS max_code FROM tr_ros_pack WHERE pack_code LIKE '{$prefix}%'")->row();
        $urutan = 0;
        if ($row && $row->max_code) {
            // PCK-YYMM-XXX → ambil 3 digit terakhir
            $urutan = (int) substr($row->max_code, -3);
        }
        $urutan++;
        return $prefix . '-' . sprintf("%03d", $urutan);
    }
}
