<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stock_from_warehouse_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // SERVER-SIDE: Production Transit
    // ---------------------------------------------------------------

    public function get_json_transit()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start']  ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'asc';

        $col_map = [
            1 => 'pt.nm_material',
            2 => 'pt.no_coil',
            3 => 'pt.kode_internal',
            4 => 'pt.net_weight',
            5 => 'pt.gross_weight',
            6 => 'pt.length',
            7 => 'pt.type',
        ];
        $order_by = $col_map[$order_col] ?? 'pt.nm_material';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (pt.nm_material LIKE '%{$s}%'
                               OR pt.no_coil     LIKE '%{$s}%'
                               OR pt.kode_internal LIKE '%{$s}%'
                               OR pt.id_material LIKE '%{$s}%'
                               OR pt.trade_name LIKE '%{$s}%')";
        }

        $base_from = "
            FROM warehouse_stock_coil_production_transit pt
            WHERE pt.status = 1
            {$where_search}
        ";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_from}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT
                pt.id,
                pt.id_material,
                pt.nm_material,
                pt.trade_name,
                pt.no_coil,
                pt.kode_internal,
                pt.gross_weight,
                pt.net_weight,
                pt.length,
                pt.type,
                pt.kd_gudang
            {$base_from}
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            // Type badge
            $type_label = ($row['type'] == 'from_warehouse') ? 'From Warehouse' : $row['type'];
            $type_html  = "<span class='badge bg-primary'>" . htmlspecialchars($type_label) . "</span>";

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                htmlspecialchars($row['nm_material'] ?? '')
                    . '<br><small class="text-muted">' . htmlspecialchars($row['id_material'] ?? '') . '</small>'
                    . ($row['trade_name'] ? '<br><small class="text-info">' . htmlspecialchars($row['trade_name']) . '</small>' : ''),
                "<div class='text-center'>" . htmlspecialchars($row['no_coil'] ?? '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars($row['kode_internal'] ?? '-') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['length'], 3, ',', '.') . "</div>",
                "<div class='text-center'>" . $type_html . "</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ---------------------------------------------------------------
    // SERVER-SIDE: WIP
    // ---------------------------------------------------------------

    public function get_json_wip()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start']  ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'asc';

        $col_map = [
            1 => 'wip.nm_material',
            2 => 'wip.no_coil',
            3 => 'wip.kode_internal',
            4 => 'wip.qty',
            5 => 'wip.net_weight',
            6 => 'wip.gross_weight',
        ];
        $order_by = $col_map[$order_col] ?? 'wip.nm_material';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (wip.nm_material LIKE '%{$s}%'
                               OR wip.no_coil     LIKE '%{$s}%'
                               OR wip.kode_internal LIKE '%{$s}%'
                               OR wip.id_material LIKE '%{$s}%')";
        }

        $base_from = "
            FROM warehouse_stock_coil_wip wip
            WHERE wip.status = 'active'
            {$where_search}
        ";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_from}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT
                wip.id,
                wip.id_material,
                wip.nm_material,
                wip.no_coil,
                wip.kode_internal,
                wip.gross_weight,
                wip.net_weight,
                wip.qty,
                wip.length
            {$base_from}
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $data[] = [
                "<div class='text-center'>{$no}</div>",
                htmlspecialchars($row['nm_material'] ?? '')
                    . '<br><small class="text-muted">' . htmlspecialchars($row['id_material'] ?? '') . '</small>',
                "<div class='text-center'>" . htmlspecialchars($row['no_coil'] ?? '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars($row['kode_internal'] ?? '-') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) ($row['qty'] ?? 0), 3, ',', '.') . "</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }
}
