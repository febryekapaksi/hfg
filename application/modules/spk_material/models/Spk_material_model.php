<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spk_material_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // SPK NUMBER GENERATION
    // ---------------------------------------------------------------

    /**
     * Generate nomor SPK berurutan format SPK-YYYYMM-XXXX
     * Query tr_spk_material_header untuk counter terakhir bulan ini
     *
     * @return string Nomor SPK baru (e.g. SPK-202506-0001)
     */
    public function generate_spk_no()
    {
        $prefix = 'SPK-' . date('Ym') . '-';

        $last = $this->db
            ->like('spk_no', $prefix, 'after')
            ->order_by('spk_no', 'DESC')
            ->limit(1)
            ->get('tr_spk_material_header')
            ->row();

        $next_counter = 1;
        if ($last) {
            $parts = explode('-', $last->spk_no);
            $next_counter = (int) end($parts) + 1;
        }

        return $prefix . str_pad($next_counter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor SPK dengan row-level lock (FOR UPDATE) untuk mencegah race condition.
     * Harus dipanggil di dalam transaction (trans_begin sudah dipanggil sebelumnya).
     *
     * @return string Nomor SPK baru
     */
    public function generate_spk_no_locked()
    {
        $prefix = 'SPK-' . date('Ym') . '-';

        // SELECT ... FOR UPDATE akan lock row sehingga concurrent request harus menunggu
        $sql = "SELECT spk_no FROM tr_spk_material_header 
                WHERE spk_no LIKE ? 
                ORDER BY spk_no DESC 
                LIMIT 1 
                FOR UPDATE";

        $last = $this->db->query($sql, [$prefix . '%'])->row();

        $next_counter = 1;
        if ($last) {
            $parts = explode('-', $last->spk_no);
            $next_counter = (int) end($parts) + 1;
        }

        return $prefix . str_pad($next_counter, 4, '0', STR_PAD_LEFT);
    }

    // ---------------------------------------------------------------
    // SPK HEADER & DETAIL CRUD
    // ---------------------------------------------------------------

    /**
     * Insert SPK header ke tabel tr_spk_material_header
     *
     * @param array $data Associative array kolom header SPK
     * @return bool Insert result
     */
    public function insert_spk_header($data)
    {
        return $this->db->insert('tr_spk_material_header', $data);
    }

    /**
     * Insert batch SPK details ke tabel tr_spk_material_detail
     *
     * @param array $details Array of associative arrays untuk setiap baris produk
     * @return bool Insert batch result
     */
    public function insert_spk_details($details)
    {
        return $this->db->insert_batch('tr_spk_material_detail', $details);
    }

    /**
     * Update SPK header berdasarkan spk_no
     *
     * @param string $spk_no Nomor SPK
     * @param array  $data   Kolom yang akan diupdate
     * @return bool Update result
     */
    public function update_spk_header($spk_no, $data)
    {
        $this->db->where('spk_no', $spk_no);
        return $this->db->update('tr_spk_material_header', $data);
    }

    /**
     * Delete semua detail baris produk untuk SPK tertentu
     * Digunakan saat update SPK (delete lama, insert baru)
     *
     * @param string $spk_no Nomor SPK
     * @return bool Delete result
     */
    public function delete_spk_details($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        return $this->db->delete('tr_spk_material_detail');
    }

    /**
     * Get single SPK header berdasarkan spk_no
     *
     * @param string $spk_no Nomor SPK
     * @return array|null SPK header data atau null jika tidak ditemukan
     */
    public function get_spk($spk_no)
    {
        return $this->db->where('spk_no', $spk_no)->get('tr_spk_material_header')->row_array();
    }

    /**
     * Get semua detail baris produk untuk SPK tertentu
     *
     * @param string $spk_no Nomor SPK
     * @return array Array of detail rows
     */
    public function get_spk_details($spk_no)
    {
        return $this->db->where('spk_no', $spk_no)->order_by('urut', 'ASC')->get('tr_spk_material_detail')->result_array();
    }

    // ---------------------------------------------------------------
    // MASTER DATA LOOKUP
    // ---------------------------------------------------------------

    /**
     * Get daftar shift aktif dari master_shift
     * WHERE deleted_date IS NULL
     *
     * @return array Array of shift records (id, nama_shift)
     */
    public function get_active_shifts()
    {
        $this->db->select('id, nama_shift');
        $this->db->from('master_shift');
        $this->db->where('deleted_date IS NULL', null, false);
        return $this->db->get()->result_array();
    }

    /**
     * Get daftar produk finished goods untuk dropdown
     * Dari product_lvl_4 WHERE status aktif AND deleted_date IS NULL
     *
     * @param string|null $search Optional search keyword for filtering
     * @return array Array of product records (code_lv4, nama)
     */
    public function get_produk_fg_list($search = null)
    {
        $this->db->select('p.code_lv4, p.nama');
        $this->db->from('product_lvl_4 p');
        $this->db->join('ms_bom_header bh', 'bh.id_produk = p.code_lv4');
        $this->db->where('bh.is_delete', 0);
        $this->db->where('p.status', 1);
        $this->db->where('p.deleted_date IS NULL', null, false);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.nama', $search);
            $this->db->or_like('p.code_lv4', $search);
            $this->db->group_end();
        }
        $this->db->group_by('p.code_lv4, p.nama');
        $this->db->limit(50);
        return $this->db->get()->result_array();
    }

    /**
     * Get berat per unit produk dari product_lvl_4
     *
     * @param string $id_produk code_lv4 produk
     * @return mixed Weight value atau null
     */
    public function get_produk_weight($id_produk)
    {
        $this->db->select('weight');
        $this->db->from('product_lvl_4');
        $this->db->where('code_lv4', $id_produk);
        $row = $this->db->get()->row();
        return $row ? $row->weight : null;
    }

    // ---------------------------------------------------------------
    // BOM & MATERIAL
    // ---------------------------------------------------------------

    /**
     * Get daftar material dari BOM beserta kebutuhan qty dan stok gudang
     * JOIN ms_bom_header + ms_bom_detail, hitung qty_needed = bom_qty × target_qty
     * LEFT JOIN warehouse_stock untuk stok WIP dan Gudang Produksi
     *
     * @param string $id_produk  ID produk (code_lv4/id_produk)
     * @param int    $target_qty Target quantity produksi
     * @return array Array of material records dengan qty_needed, stok_wip, stok_produksi
     */
    public function get_bom_materials($id_produk, $target_qty)
    {
        $sql = "SELECT 
            bd.id_material, 
            bd.nm_material, 
            ROUND(bd.qty * ?, 4) as qty_needed,
            bd.id_unit,
            bd.nm_unit,
            COALESCE((SELECT SUM(ws.qty_stock) FROM warehouse_stock ws WHERE ws.code_lv4 = bd.id_material AND ws.kd_gudang = 'WIP'), 0) as stok_wip,
            COALESCE((SELECT SUM(ws2.qty_stock) FROM warehouse_stock ws2 WHERE ws2.code_lv4 = bd.id_material AND ws2.kd_gudang = 'PRO'), 0) as stok_produksi
        FROM ms_bom_header bh
        JOIN ms_bom_detail bd ON bd.id_bom = bh.id
        WHERE bh.id_produk = ? AND bh.is_delete = 0 AND bd.is_delete = 0
        ORDER BY bd.nm_material ASC";

        return $this->db->query($sql, [$target_qty, $id_produk])->result_array();
    }

    /**
     * Cek apakah produk memiliki BOM aktif
     * Query ms_bom_header WHERE id_produk AND is_delete = 0
     *
     * @param string $id_produk ID produk
     * @return bool True jika BOM ada, false jika tidak
     */
    public function has_bom($id_produk)
    {
        $count = $this->db->where('id_produk', $id_produk)
                          ->where('is_delete', 0)
                          ->count_all_results('ms_bom_header');
        return $count > 0;
    }

    /**
     * Get BOM detail materials untuk warehouse request
     * JOIN ms_bom_detail dengan ms_bom_header berdasarkan id_produk
     *
     * @param string $id_produk ID produk (code_lv4/id_produk)
     * @return array Array of material records (id_material, nm_material, qty, id_unit, nm_unit)
     */
    public function get_bom_details_for_request($id_produk)
    {
        $this->db->select('bd.id_material, bd.nm_material, bd.qty, bd.id_unit, bd.nm_unit');
        $this->db->from('ms_bom_detail bd');
        $this->db->join('ms_bom_header bh', 'bh.id = bd.id_bom');
        $this->db->where('bh.id_produk', $id_produk);
        $this->db->where('bh.is_delete', 0);
        $this->db->where('bd.is_delete', 0);
        return $this->db->get()->result_array();
    }

    // ---------------------------------------------------------------
    // STATUS MANAGEMENT
    // ---------------------------------------------------------------

    /**
     * Update status SPK di tr_spk_material_header
     * Pure update query — validasi workflow dilakukan di controller
     *
     * @param string $spk_no Nomor SPK
     * @param array  $data   Array berisi status, updated_by, updated_at
     * @return bool Update result
     */
    public function update_spk_status($spk_no, $data)
    {
        $this->db->where('spk_no', $spk_no);
        return $this->db->update('tr_spk_material_header', $data);
    }

    /**
     * Get current status SPK
     *
     * @param string $spk_no Nomor SPK
     * @return string|null Status SPK atau null jika tidak ditemukan
     */
    public function get_spk_status($spk_no)
    {
        $row = $this->db->select('status')->where('spk_no', $spk_no)->get('tr_spk_material_header')->row();
        return $row ? $row->status : null;
    }
}
