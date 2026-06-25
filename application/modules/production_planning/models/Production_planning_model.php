<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_planning_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate nomor plan: PP-YYYYMM-XXXX
     */
    public function generate_plan_no()
    {
        $prefix = 'PP-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(plan_no) AS max_no FROM tr_production_plan WHERE plan_no LIKE '{$prefix}%'"
        )->row();
        $last = $row ? (int) substr($row->max_no, -4) : 0;
        return $prefix . sprintf('%04d', $last + 1);
    }

    /**
     * Ambil daftar produk FG dari product_lvl_4
     * Digunakan untuk dropdown di form production planning
     */
    public function get_produk_fg_list()
    {
        return $this->db
            ->select('p4.code_lv4, p4.nama, p4.trade_name')
            ->from('product_lvl_4 p4')
            ->where('p4.category', 'product')
            ->where('p4.deleted_date', null)
            ->where('p4.status', 1)
            ->order_by('p4.nama', 'ASC')
            ->get()->result();
    }

    /**
     * Simpan plan baru, status default Draft
     */
    public function save_plan($data, $details = [])
    {
        $this->db->trans_start();

        $plan_no = $this->generate_plan_no();
        $this->db->insert('tr_production_plan', array_merge($data, [
            'plan_no'    => $plan_no,
            'status'     => 'Draft',
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        foreach ($details as $detail) {
            $this->db->insert('tr_production_plan_detail', array_merge($detail, ['plan_no' => $plan_no]));
            $this->db->insert('tr_production_plan_coil_alloc', [
                'plan_no'      => $plan_no,
                'no_coil'      => $detail['no_coil'],
                'status_alloc' => 'allocated',
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $plan_no : false;
    }

    /**
     * Update plan yang masih Draft
     */
    public function update_plan($plan_no, $data, $details = [])
    {
        $this->db->trans_start();

        $this->db->where('plan_no', $plan_no)->update('tr_production_plan', $data);
        $this->db->where('plan_no', $plan_no)->delete('tr_production_plan_detail');
        $this->db->where('plan_no', $plan_no)->delete('tr_production_plan_coil_alloc');

        foreach ($details as $detail) {
            $this->db->insert('tr_production_plan_detail', array_merge($detail, ['plan_no' => $plan_no]));
            $this->db->insert('tr_production_plan_coil_alloc', [
                'plan_no'      => $plan_no,
                'no_coil'      => $detail['no_coil'],
                'status_alloc' => 'allocated',
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Ambil satu plan beserta detail
     */
    public function get_plan($plan_no)
    {
        return $this->db->get_where('tr_production_plan', ['plan_no' => $plan_no])->row();
    }

    public function get_plan_details($plan_no)
    {
        return $this->db->get_where('tr_production_plan_detail', ['plan_no' => $plan_no])->result();
    }

    public function get_coil_alloc($plan_no)
    {
        return $this->db->get_where('tr_production_plan_coil_alloc', ['plan_no' => $plan_no])->result();
    }

    /**
     * Ambil coil available di gudang berdasarkan material dari BOM produk
     * Hitung estimate qty = net weight coil / berat per unit produk
     *
     * @param string $id_produk  code_lv4 dari product_lvl_4
     * @return array
     */
    public function get_coil_available($id_produk_fg = null)
    {
        if (empty($id_produk_fg)) {
            // Fallback: tampilkan semua coil available jika tidak ada produk dipilih
            $sql = "
                SELECT
                    c.no_coil,
                    c.id_material,
                    c.nm_material AS nm_material,
                    c.qty AS berat_bersih,
                    c.qty AS net_weight,
                    c.no_ros,
                    c.id_gudang,
                    w.nm_gudang,
                    0 AS berat_kotor,
                    NULL AS berat_per_unit,
                    NULL AS estimate_qty
                FROM warehouse_stock_coil c
                LEFT JOIN warehouse w ON w.id = c.id_gudang
                LEFT JOIN tr_production_plan_coil_alloc alloc
                    ON alloc.no_coil COLLATE utf8mb4_general_ci = c.no_coil COLLATE utf8mb4_general_ci
                    AND alloc.status_alloc IN ('allocated','issued')
                WHERE alloc.id IS NULL
                  AND c.no_coil IS NOT NULL
                  AND c.no_coil != ''
                  AND c.qty > 0
                  AND c.status = 1
                ORDER BY c.no_coil ASC
            ";
            return $this->db->query($sql)->result();
        }

        // Ambil berat per unit produk dari product_lvl_4
        $produk = $this->db->select('weight, nama')
            ->from('product_lvl_4')
            ->where('code_lv4', $id_produk_fg)
            ->get()->row();

        $berat_per_unit = ($produk && $produk->weight > 0) ? (float) $produk->weight : 0;

        // Ambil material yang dibutuhkan dari BOM
        $bom = $this->db->select('b.id_material')
            ->from('ms_bom_header h')
            ->join('ms_bom_detail b', 'b.id_bom = h.id')
            ->where('h.id_produk', $id_produk_fg)
            ->get()->result();

        $material_ids = array_map(function($r) { return $r->id_material; }, $bom);

        // Query coil dari warehouse_stock_coil yang materialnya sesuai BOM
        $this->db->select('
            c.no_coil,
            c.id_material,
            c.nm_material,
            c.qty AS net_weight,
            c.no_ros,
            c.id_gudang,
            w.nm_gudang
        ');
        $this->db->from('warehouse_stock_coil c');
        $this->db->join('warehouse w', 'w.id = c.id_gudang', 'left');
        $this->db->join(
            'tr_production_plan_coil_alloc alloc',
            'alloc.no_coil = c.no_coil
             AND alloc.status_alloc IN (\'allocated\',\'issued\')',
            'left'
        );
        $this->db->where('alloc.id IS NULL', null, false);
        $this->db->where('c.no_coil IS NOT NULL', null, false);
        $this->db->where("c.no_coil != ''", null, false);
        $this->db->where('c.qty >', 0);
        $this->db->where('c.kd_gudang', 'PRO');
        $this->db->where('c.status', 1);

        if (!empty($material_ids)) {
            $this->db->where_in('c.id_material', $material_ids);
        }

        $this->db->order_by('c.id_material', 'ASC');
        $this->db->order_by('c.no_coil', 'ASC');

        $rows = $this->db->get()->result();

        // Inject estimate_qty dan berat_per_unit ke setiap row
        foreach ($rows as $row) {
            $row->berat_per_unit = $berat_per_unit;
            $row->estimate_qty   = ($berat_per_unit > 0)
                ? floor((float) $row->net_weight / $berat_per_unit)
                : 0;
        }

        return $rows;
    }

    /**
     * Ambil berat per unit produk dari product_lvl_4
     */
    public function get_berat_per_unit($id_produk_fg)
    {
        $row = $this->db->select('weight, nama, trade_name')
            ->from('product_lvl_4')
            ->where('code_lv4', $id_produk_fg)
            ->get()->row();
        return $row;
    }

    /**
     * Release plan: ubah status ke Released
     * Plan yang sudah Released tidak bisa diedit
     */
    public function release_plan($plan_no)
    {
        $plan = $this->get_plan($plan_no);
        if (!$plan || $plan->status !== 'Draft') {
            return ['success' => false, 'message' => 'Plan tidak ditemukan atau status bukan Draft'];
        }

        $details = $this->get_plan_details($plan_no);
        if (empty($details)) {
            return ['success' => false, 'message' => 'Plan tidak dapat di-release karena belum ada coil yang dialokasikan'];
        }

        $this->db->trans_start();

        // Ubah status plan ke Released
        $this->db->where('plan_no', $plan_no)->update('tr_production_plan', [
            'status'     => 'Released',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Auto-generate Draft SPK dari plan ini (Requirements 1.4)
        $spk_no = $this->_generate_spk_from_plan($plan, $details);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal me-release plan'];
        }

        return ['success' => true, 'plan_no' => $plan_no, 'spk_no' => $spk_no];
    }

    /**
     * Generate Draft SPK dari plan yang di-release
     * Dipanggil dalam transaksi release_plan
     */
    private function _generate_spk_from_plan($plan, $details)
    {
        $prefix = 'SPK-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(spk_no) AS max_no FROM tr_spk_production WHERE spk_no LIKE '{$prefix}%'"
        )->row();
        $last   = ($row && $row->max_no) ? (int) substr($row->max_no, -4) : 0;
        $spk_no = $prefix . sprintf('%04d', $last + 1);

        $this->db->insert('tr_spk_production', [
            'spk_no'       => $spk_no,
            'plan_no'      => $plan->plan_no,
            'produk_fg'    => $plan->id_produk_fg,
            'nm_produk_fg' => $plan->nm_produk_fg,
            'target_qty'   => $plan->target_qty,
            'tgl_spk'      => date('Y-m-d'),
            'due_date'     => $plan->due_date,
            'status'       => 'In Process',
            'created_by'   => $plan->created_by,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // Salin detail coil dari plan ke SPK
        foreach ($details as $d) {
            $this->db->insert('tr_spk_material_detail', [
                'spk_no'      => $spk_no,
                'no_coil'     => $d->no_coil,
                'id_material' => $d->id_material,
                'nm_material' => $d->nm_material,
                'no_ros'      => $d->no_ros,
                'net_weight'  => $d->net_weight_coil,
                'scan_status' => 'pending',
            ]);
        }

        return $spk_no;
    }

    /**
     * Cancel plan: hanya bisa jika belum ada SPK aktif
     */
    public function cancel_plan($plan_no)
    {
        $plan = $this->get_plan($plan_no);
        if (!$plan || !in_array($plan->status, ['Draft', 'Released'])) {
            return ['success' => false, 'message' => 'Plan tidak dapat dibatalkan'];
        }

        // Cek apakah sudah ada SPK
        $spk_count = $this->db->where('plan_no', $plan_no)
            ->where_not_in('status', ['Cancelled'])
            ->count_all_results('tr_spk_production');

        if ($spk_count > 0) {
            return ['success' => false, 'message' => 'Plan tidak dapat dibatalkan karena sudah ada SPK aktif'];
        }

        $this->db->trans_start();
        $this->db->where('plan_no', $plan_no)->update('tr_production_plan', ['status' => 'Cancelled']);
        $this->db->where('plan_no', $plan_no)->update('tr_production_plan_coil_alloc', ['status_alloc' => 'cancelled']);
        $this->db->trans_complete();

        return ['success' => $this->db->trans_status()];
    }

    /**
     * Cek apakah semua SPK sudah Closed, jika ya ubah plan ke Closed
     */
    public function check_and_close_plan($plan_no)
    {
        $total = $this->db->where('plan_no', $plan_no)
            ->where_not_in('status', ['Cancelled'])
            ->count_all_results('tr_spk_production');

        $closed = $this->db->where('plan_no', $plan_no)
            ->where('status', 'Closed')
            ->count_all_results('tr_spk_production');

        if ($total > 0 && $total === $closed) {
            $this->db->where('plan_no', $plan_no)->update('tr_production_plan', ['status' => 'Closed']);
            return true;
        }
        return false;
    }

    /**
     * Query untuk DataTables server-side
     */
    public function get_list_for_datatable($params)
    {
        $search    = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        $cols     = ['p.plan_no', 'p.tgl_plan', 'p.nm_produk_fg', 'p.target_qty', 'p.status', 'p.created_at'];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'p.created_at';

        $base_sql = "FROM tr_production_plan p WHERE 1=1";
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $base_sql .= " AND (p.plan_no LIKE '%{$s}%' OR p.nm_produk_fg LIKE '%{$s}%' OR p.status LIKE '%{$s}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;
        $query    = $this->db->query("SELECT p.* {$base_sql} ORDER BY {$order_by} {$order_dir} LIMIT {$start},{$length}");

        return ['total' => $total, 'filtered' => $filtered, 'data' => $query->result()];
    }
}
