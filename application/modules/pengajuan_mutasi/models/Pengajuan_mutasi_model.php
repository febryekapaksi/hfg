<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pengajuan_mutasi_model extends BF_Model
{
    protected $ENABLE_ADD;
    protected $ENABLE_MANAGE;
    protected $ENABLE_VIEW;
    protected $ENABLE_DELETE;

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD    = has_permission('Request_Mutation.Add');
        $this->ENABLE_MANAGE = has_permission('Request_Mutation.Manage');
        $this->ENABLE_VIEW   = has_permission('Request_Mutation.View');
        $this->ENABLE_DELETE = has_permission('Request_Mutation.Delete');
    }

    // ---------------------------------------------------------------
    // NUMBERING
    // ---------------------------------------------------------------

    /**
     * Generate nomor mutasi format: MUTYYMMXXX (reset per bulan)
     */
    public function generate_mutation_number()
    {
        $yymm = date('ym'); // e.g. 2506
        $prefix = 'MUT' . $yymm;

        $query = $this->db->query("
            SELECT mutation_number 
            FROM material_mutations 
            WHERE mutation_number LIKE '{$prefix}%' 
            ORDER BY mutation_number DESC 
            LIMIT 1
        ");

        if ($query->num_rows() > 0) {
            $last   = $query->row()->mutation_number;
            $seq    = (int) substr($last, -3);
            $next   = $seq + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // ---------------------------------------------------------------
    // WAREHOUSE
    // ---------------------------------------------------------------

    public function get_all_warehouse()
    {
        return $this->db->select('id, kd_gudang, nm_gudang')
            ->from('warehouse')
            ->get()->result_array();
    }

    // ---------------------------------------------------------------
    // MATERIAL
    // ---------------------------------------------------------------

    /**
     * Get material list berdasarkan gudang (untuk dropdown form)
     */
    public function get_material_by_gudang($id_gudang)
    {
        return $this->db->select('
                ws.id,
                ws.code_incoming,
                ws.nm_material,
                ws.trade_name,
                ws.code_lv1,
                ws.code_lv2,
                ws.code_lv3,
                ws.code_lv4,
                ws.id_unit,
                ws.qty_free,
                ws.harga_beli
            ')
            ->from('warehouse_stock ws')
            ->where('ws.id_gudang', $id_gudang)
            ->where('ws.qty_free >', 0)
            ->get()->result_array();
    }

    /**
     * Get coil list berdasarkan id_material dan id_gudang
     */
    public function get_coil_by_material($code_lv4)
    {
        return $this->db->select('
                wsc.id,
                wsc.no_coil,
                wsc.no_ipp,
                wsc.no_po,
                wsc.no_ros,
                wsc.gross_weight,
				wsc.kode_internal,
                wsc.net_weight,
                wsc.length,
                wsc.harga_beli,
                wsc.total_nilai
            ')
            ->from('warehouse_stock_coil wsc')
            ->where('wsc.id_material', $code_lv4)
            ->get()->result_array();
    }

    // ---------------------------------------------------------------
    // CRUD MUTASI
    // ---------------------------------------------------------------

    public function get_list($status_list = [])
    {
        $this->db->select('
            mm.id,
            mm.mutation_number,
            mm.mutation_date,
            mm.no_berita_acara,
            mm.file_name_original,
            mm.file_name_hash,
            mm.id_gudang_from,
            mm.kd_gudang_from,
            mm.nm_gudang_from,
            mm.id_gudang_to,
            mm.kd_gudang_to,
            mm.nm_gudang_to,
            mm.description,
            mm.status,
            mm.reject_reason,
            mm.create_by,
            mm.create_date
        ');
        $this->db->from('material_mutations mm');
        $this->db->where('mm.is_delete', 0);

        if (!empty($status_list)) {
            $this->db->where_in('mm.status', $status_list);
        }

        $this->db->order_by('mm.create_date', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_detail($id)
    {
        $this->db->select('
            mm.id,
            mm.mutation_number,
            mm.mutation_date,
            mm.no_berita_acara,
            mm.file_name_original,
            mm.file_name_hash,
            mm.id_gudang_from,
            mm.kd_gudang_from,
            mm.nm_gudang_from,
            mm.id_gudang_to,
            mm.kd_gudang_to,
            mm.nm_gudang_to,
            mm.description,
            mm.status,
            mm.reject_reason,
            mm.approved_by,
            mm.approved_date,
            mm.create_by,
            mm.create_date,
            mm.update_by,
            mm.update_date
        ');
        $this->db->from('material_mutations mm');
        $this->db->where('mm.id', $id);
        $this->db->where('mm.is_delete', 0);

        $header = $this->db->get()->row_array();

        if (!$header) return null;

        // Ambil detail material
        $details = $this->db->select('*')
            ->from('material_mutation_details')
            ->where('id_material_mutation', $id)
            ->get()->result_array();

        // Ambil coil per detail
        foreach ($details as &$detail) {
            $detail['coils'] = $this->db->select('*')
                ->from('material_mutation_details_coil')
                ->where('id_mutation_detail', $detail['id'])
                ->get()->result_array();
        }

        $header['details'] = $details;

        return $header;
    }

    public function save_mutation($data, $details)
    {
        $this->db->trans_start();

        // Insert header
        $this->db->insert('material_mutations', $data);
        $id_mutation = $this->db->insert_id();

        foreach ($details as $detail) {
            $coils = isset($detail['coils']) ? $detail['coils'] : [];
            unset($detail['coils']);

            $detail['id_material_mutation'] = $id_mutation;
            $this->db->insert('material_mutation_details', $detail);
            $id_detail = $this->db->insert_id();

            foreach ($coils as $coil) {
                $coil['id_mutation_detail'] = $id_detail;
                $this->db->insert('material_mutation_details_coil', $coil);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }

        return $id_mutation;
    }

    public function update_mutation($id, $data, $details)
    {
        $this->db->trans_start();

        // Update header
        $this->db->where('id', $id);
        $this->db->update('material_mutations', $data);

        // Hapus detail & coil lama
        $old_details = $this->db->select('id')
            ->from('material_mutation_details')
            ->where('id_material_mutation', $id)
            ->get()->result_array();

        foreach ($old_details as $od) {
            $this->db->where('id_mutation_detail', $od['id'])
                ->delete('material_mutation_details_coil');
        }

        $this->db->where('id_material_mutation', $id)
            ->delete('material_mutation_details');

        // Insert detail & coil baru
        foreach ($details as $detail) {
            $coils = isset($detail['coils']) ? $detail['coils'] : [];
            unset($detail['coils']);

            $detail['id_material_mutation'] = $id;
            $this->db->insert('material_mutation_details', $detail);
            $id_detail = $this->db->insert_id();

            foreach ($coils as $coil) {
                $coil['id_mutation_detail'] = $id_detail;
                $this->db->insert('material_mutation_details_coil', $coil);
            }
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== FALSE;
    }

    /**
     * Ubah status ke 1 (ajukan / pending approval)
     */
    public function submit_mutation($id, $update_by)
    {
        $this->db->where('id', $id);
        $this->db->where_in('status', [0, 6]); 
        $this->db->update('material_mutations', [
            'status'      => 1,
            'update_by'   => $update_by,
            'update_date' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Cancel mutasi (status = 5)
     */
    public function cancel_mutation($id, $update_by, $reject_reason)
    {
        $this->db->where('id', $id);
        $this->db->where_in('status', [0]);
        $this->db->update('material_mutations', [
            'status'        => 5,
            'reject_reason' => $reject_reason,
            'update_by'     => $update_by,
            'update_date'   => date('Y-m-d H:i:s'),
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Hitung total nilai mutasi dari semua detail (regular + coil)
     */
    public function get_total_nilai($id_mutation)
    {
        // Total dari detail regular
        $q1 = $this->db->query("
            SELECT COALESCE(SUM(total_nilai_mutasi), 0) as total
            FROM material_mutation_details
            WHERE id_material_mutation = {$id_mutation}
        ")->row();

        return (float) $q1->total;
    }
}
