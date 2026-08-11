<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Master_rate_labor_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ── Master Rate Labor (Direct/Indirect) ──────────────────────────────────

    /** Get active labor rates list */
    public function get_rate_labor_list()
    {
        return $this->db
            ->where('is_delete', 0)
            ->get('ms_rate_labor')
            ->result();
    }

    /** Get single labor rate by ID */
    public function get_rate_labor($id)
    {
        return $this->db
            ->where('id', $id)
            ->where('is_delete', 0)
            ->get('ms_rate_labor')
            ->row();
    }

    /** Save / Update labor rate */
    public function save_rate_labor($id, $rate, $remark, $user_id)
    {
        return $this->db->update('ms_rate_labor', [
            'rate'         => (float) $rate,
            'remark'       => $remark,
            'updated_by'   => $user_id,
            'updated_date' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
    }

    // ── Master Rate Process Product ──────────────────────────────────────────

    /** Get active products with process rates */
    public function get_rate_process_product_list()
    {
        // Fetch current master rates for defaults
        $direct_rate_row = $this->db->get_where('ms_rate_labor', ['type' => 'direct', 'is_delete' => 0])->row();
        $indirect_rate_row = $this->db->get_where('ms_rate_labor', ['type' => 'indirect', 'is_delete' => 0])->row();
        
        $default_direct_rate   = $direct_rate_row ? (float) $direct_rate_row->rate : 0.0;
        $default_indirect_rate = $indirect_rate_row ? (float) $indirect_rate_row->rate : 0.0;

        return $this->db
            ->select("
                p.code_lv4,
                p.nama AS nm_produk,
                p.trade_name,
                COALESCE(p.weight, 0) AS weight,
                COALESCE(r.bahan_pendukung_khusus, 0) AS bahan_pendukung_khusus,
                COALESCE(r.consumable, 0) AS consumable,
                COALESCE(r.foh, 0) AS foh,
                COALESCE(r.cycle_time, 0) AS cycle_time,
                COALESCE(r.mp, 0) AS mp,
                COALESCE(r.total_man_hour, 0) AS total_man_hour,
                COALESCE(NULLIF(r.man_hour_rate, 0), {$default_direct_rate}) AS man_hour_rate,
                COALESCE(NULLIF(p.weight, 0), COALESCE(r.kg_pcs, 0)) AS kg_pcs,
                COALESCE(r.gaji_direct, 0) AS gaji_direct,
                COALESCE(NULLIF(r.rate_indirect, 0), {$default_indirect_rate}) AS rate_indirect,
                COALESCE(r.gaji_indirect, 0) AS gaji_indirect,
                COALESCE(r.standard_biaya_gaji, 0) AS standard_biaya_gaji,
                COALESCE(r.standard_biaya_gaji_round, 0) AS standard_biaya_gaji_round
            ")
            ->from('product_lvl_4 p')
            ->join('ms_rate_process_product r', 'r.code_lv4 = p.code_lv4 AND r.is_delete = 0', 'left')
            ->where('p.category', 'product')
            ->where('p.deleted_date', NULL)
            ->where('p.status', 1)
            ->order_by('p.nama', 'ASC')
            ->get()
            ->result();
    }

    /** Save all process rates for products */
    public function save_rate_process_product($products, $user_id)
    {
        $this->db->trans_start();

        // Fetch current master rates
        $direct_rate_row = $this->db->get_where('ms_rate_labor', ['type' => 'direct', 'is_delete' => 0])->row();
        $indirect_rate_row = $this->db->get_where('ms_rate_labor', ['type' => 'indirect', 'is_delete' => 0])->row();
        
        $default_direct_rate   = $direct_rate_row ? (float) $direct_rate_row->rate : 0.0;
        $default_indirect_rate = $indirect_rate_row ? (float) $indirect_rate_row->rate : 0.0;

        foreach ($products as $code_lv4 => $p) {
            $bahan      = isset($p['bahan_pendukung_khusus']) ? (float) $p['bahan_pendukung_khusus'] : 0.0;
            $consumable = isset($p['consumable']) ? (float) $p['consumable'] : 0.0;
            $foh        = isset($p['foh']) ? (float) $p['foh'] : 0.0;
            $cycle_time = isset($p['cycle_time']) ? (float) $p['cycle_time'] : 0.0;
            $mp         = isset($p['mp']) ? (float) $p['mp'] : 0.0;

            // Fetch weight from product_lvl_4 if available
            $product_row = $this->db->select('weight')->get_where('product_lvl_4', ['code_lv4' => $code_lv4])->row();
            $kg_pcs      = ($product_row && (float)$product_row->weight > 0) ? (float)$product_row->weight : (isset($p['kg_pcs']) ? (float)$p['kg_pcs'] : 0.0);

            // Recalculate formulas on backend for data safety
            $total_man_hour = ($cycle_time * $mp) / 60.0;
            $man_hour_rate  = $default_direct_rate;
            
            $gaji_direct = 0.0;
            if ($kg_pcs > 0) {
                $gaji_direct = ($total_man_hour * $man_hour_rate) / $kg_pcs;
            }

            $rate_indirect = $default_indirect_rate;
            $gaji_indirect = $gaji_direct * ($rate_indirect / 100.0);
            $std_biaya_gaji = $gaji_direct + $gaji_indirect;
            $std_biaya_gaji_round = (int) round($std_biaya_gaji);

            $data = [
                'bahan_pendukung_khusus'    => $bahan,
                'consumable'                => $consumable,
                'foh'                       => $foh,
                'cycle_time'                => $cycle_time,
                'mp'                        => $mp,
                'total_man_hour'            => $total_man_hour,
                'man_hour_rate'             => $man_hour_rate,
                'kg_pcs'                    => $kg_pcs,
                'gaji_direct'               => $gaji_direct,
                'rate_indirect'             => $rate_indirect,
                'gaji_indirect'             => $gaji_indirect,
                'standard_biaya_gaji'       => $std_biaya_gaji,
                'standard_biaya_gaji_round' => $std_biaya_gaji_round,
                'updated_by'                => $user_id,
                'updated_date'              => date('Y-m-d H:i:s'),
                'is_delete'                 => 0
            ];

            $exists = $this->db->get_where('ms_rate_process_product', ['code_lv4' => $code_lv4])->row();
            if ($exists) {
                $this->db->update('ms_rate_process_product', $data, ['code_lv4' => $code_lv4]);
            } else {
                $data['code_lv4'] = $code_lv4;
                $this->db->insert('ms_rate_process_product', $data);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
