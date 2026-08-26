<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Warehouse"
 */

class Warehouse_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Warehouse.Add');
        $this->ENABLE_MANAGE  = has_permission('Warehouse.Manage');
        $this->ENABLE_VIEW    = has_permission('Warehouse.View');
        $this->ENABLE_DELETE  = has_permission('Warehouse.Delete');
    }

    // ── LIST & GET ─────────────────────────────────────────────────────────

    public function GetListWarehouse()
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->order_by('a.id', 'asc');
        $query = $this->db->get();
        return $query->num_rows() ? $query->result() : false;
    }

    public function GetDataWarehouse($id)
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();
        return $query->num_rows() ? $query->row() : false;
    }

    // ── STOCK PER COIL (index) — sumber: warehouse_stock_coil ─────────────
    // $kd_gudang: 'PRO' = Produksi, 'SLI' = Slitting

    public function get_json_warehouse_stock($kd_gudang = '')
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start']  ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'asc';

        $col_map = [
            1 => 'ni.nama',
            2 => 'wsc.no_coil',
            3 => 'wsc.net_weight',
            4 => 'wsc.gross_weight',
            5 => 'wsc.length',
            6 => 'w.nm_gudang',
        ];
        $order_by = $col_map[$order_col] ?? 'ni.nama';

        // Build base WHERE
        $where_gudang = '';
        if (!empty($kd_gudang)) {
            $kd = $this->db->escape($kd_gudang);
            $where_gudang = " AND wsc.kd_gudang = {$kd}";
        }

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (ni.nama LIKE '%{$s}%'
                               OR wsc.no_coil   LIKE '%{$s}%'
                               OR wsc.id_material LIKE '%{$s}%'
                               OR w.nm_gudang   LIKE '%{$s}%')";
        }

        $base_from = "
            FROM warehouse_stock_coil wsc
            LEFT JOIN warehouse w
                ON w.kd_gudang = wsc.kd_gudang
            LEFT JOIN warehouse_stock ws
                ON ws.code_lv4 = wsc.id_material
                AND ws.id_gudang = wsc.id_gudang
            LEFT JOIN new_inventory_4 ni
                ON ni.code_lv4 = wsc.id_material
            WHERE 1=1
            {$where_gudang}
            {$where_search}
        ";

        // Hitung total & filtered — pakai raw query agar tidak konflik CI builder
        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_from}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        // Data utama
        $sql = "
            SELECT
                wsc.id,
                wsc.id_material,
                wsc.no_coil,
                wsc.kode_internal,
                wsc.gross_weight,
                wsc.net_weight,
                wsc.length,
                wsc.kd_gudang,      
                wsc.id_gudang,     
                ws.harga_beli,
                ni.nama AS nm_barang,
                ni.trade_name,
                w.nm_gudang
            {$base_from}
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $qr_text = ($row['kode_internal'] ?? '-') . '/' . strtolower($row['nm_gudang'] ?? '-');
            $qr_html = "<button type='button' class='btn btn-sm btn-info btn-show-qr' data-qr='" . htmlspecialchars($qr_text) . "'><i class='fa fa-qrcode'></i> QR</button>";

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['nm_barang']
                    . '<br><small class="text-muted">' . $row['id_material'] . '</small>'
                    . ($row['trade_name'] ? '<br><small class="text-info">' . $row['trade_name'] . '</small>' : ''),
                "<div class='text-center'>" . $row['no_coil']       . "</div>",
                "<div class='text-center'>" . ($row['kode_internal'] ?? '-') . "</div>",
                "<div class='text-end'>"    . number_format((float) $row['net_weight'],   3, ',', '.') . "</div>",
                "<div class='text-end'>"    . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>"    . number_format((float) $row['length'],       3, ',', '.') . "</div>",
                "<div class='text-center'>" . $qr_html . "</div>",
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

    // ── STOCK VALUE PER MATERIAL — sumber: warehouse_stock ────────────────

    public function get_json_stock_value($kd_gudang = '')
    {
        $requestData     = $_REQUEST;
        $id_gudang       = $_POST['id_gudang']       ?? '';
        $filter_material = $_POST['filter_material'] ?? '';

        $from = $this->_from_stock_value($kd_gudang, $id_gudang, $filter_material);

        // Total & filtered (sama karena filter sudah masuk ke FROM)
        $cnt_row   = $this->db->query("SELECT COUNT(DISTINCT ws.code_lv4, ws.id_gudang) AS cnt {$from}")->row();
        $totalData = $cnt_row ? (int) $cnt_row->cnt : 0;

        // Order
        $col_order = [
            1 => 'ws.code_lv4',
            2 => 'ws.nm_material',
            3 => 'w.nm_gudang',
            4 => 'jumlah_coil',
            5 => 'ws.qty_stock',
            6 => 'ws.harga_beli',
            7 => 'ws.total_nilai',
        ];
        $order_col = (int) ($requestData['order'][0]['column'] ?? 2);
        $order_dir = in_array(strtolower($requestData['order'][0]['dir'] ?? ''), ['asc', 'desc'])
            ? $requestData['order'][0]['dir'] : 'asc';
        $order_by  = $col_order[$order_col] ?? 'ws.nm_material';

        $start  = (int) ($requestData['start']  ?? 0);
        $length = (int) ($requestData['length'] ?? 25);
        $limit  = ($length != -1) ? "LIMIT {$start}, {$length}" : '';

        $sql = "
            SELECT
                ws.code_lv4    AS id_material,
                ws.nm_material,
                ws.trade_name,
                ws.id_gudang,
                ws.kd_gudang,
                ws.qty_stock,
                ws.qty_booking,
                ws.qty_free,
                ws.harga_beli,
                ws.total_nilai,
                w.nm_gudang,
                COUNT(wsc.id)  AS jumlah_coil
            {$from}
            GROUP BY ws.code_lv4, ws.id_gudang
            ORDER BY {$order_by} {$order_dir}
            {$limit}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $btn_history = "<button class='btn btn-sm btn-info'
                onclick=\"showHistory('{$row['id_material']}','{$row['nm_material']}','{$row['id_gudang']}')\">
                <i class='fa fa-history'></i> History
            </button>";

            $jml_coil = (int) $row['jumlah_coil'];
            $btn_coil  = "<div class='text-center'>
                <a href='#'
                onclick=\"showDetailCoil('{$row['id_material']}','{$row['nm_material']}','{$row['id_gudang']}'); return false;\">
                    {$jml_coil} roll
                </a>
            </div>";

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id_material'],
                $row['nm_material'] . ($row['trade_name'] ? '<br><small class="text-muted">' . $row['trade_name'] . '</small>' : ''),
                $btn_coil,
                "<div class='text-right'>" . number_format((float) $row['qty_stock'],        3, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((int) round($row['harga_beli']),  0, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((int) round($row['total_nilai']), 0, ',', '.') . "</div>",
                "<div class='text-center'>{$btn_history}</div>",
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

    // Bangun klausa WHERE untuk stock value (raw, aman dari collation issue)
    private function _where_stock_value($kd_gudang = '', $id_gudang = '', $filter_material = '')
    {
        $where = " WHERE ws.qty_stock > 0 ";

        if (!empty($kd_gudang)) {
            $kd    = $this->db->escape($kd_gudang);
            $where .= " AND ws.kd_gudang = {$kd} ";
        }
        if (!empty($id_gudang)) {
            $where .= " AND ws.id_gudang = " . (int) $id_gudang . " ";
        }
        if (!empty($filter_material)) {
            $f     = $this->db->escape_like_str($filter_material);
            $where .= " AND (ws.nm_material LIKE '%{$f}%' OR ws.code_lv4 LIKE '%{$f}%') ";
        }
        if (!empty($_POST['search']['value'])) {
            $s     = $this->db->escape_like_str($_POST['search']['value']);
            $where .= " AND (ws.nm_material LIKE '%{$s}%'
                          OR ws.code_lv4    LIKE '%{$s}%'
                          OR w.nm_gudang    LIKE '%{$s}%') ";
        }

        return $where;
    }

    private function _from_stock_value($kd_gudang = '', $id_gudang = '', $filter_material = '')
    {
        $where = $this->_where_stock_value($kd_gudang, $id_gudang, $filter_material);

        return "
        FROM warehouse_stock ws
        LEFT JOIN warehouse w ON w.id = ws.id_gudang
        LEFT JOIN warehouse_stock_coil wsc
            ON wsc.id_material = ws.code_lv4
            AND wsc.id_gudang = ws.id_gudang
        {$where}
        ";
    }

    public function get_grand_total_stock_value($kd_gudang = '', $id_gudang = '', $filter_material = '')
    {
        $where = " WHERE ws.qty_stock > 0 ";

        if (!empty($kd_gudang)) {
            $kd    = $this->db->escape($kd_gudang);
            $where .= " AND ws.kd_gudang = {$kd} ";
        }
        if (!empty($id_gudang)) {
            $where .= " AND ws.id_gudang = " . (int) $id_gudang . " ";
        }
        if (!empty($filter_material)) {
            $f     = $this->db->escape_like_str($filter_material);
            $where .= " AND (ws.nm_material LIKE '%{$f}%' OR ws.code_lv4 LIKE '%{$f}%') ";
        }

        $row = $this->db->query("
            SELECT SUM(ws.total_nilai) AS grand_total
            FROM warehouse_stock ws
            LEFT JOIN warehouse w ON w.id = ws.id_gudang
            {$where}
        ")->row();

        return $row ? (int) round($row->grand_total) : 0;
    }

    // ── KARTU STOK ────────────────────────────────────────────────────────

    public function get_json_kartu_stok()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_kartu_stok(
            $requestData['search']['value']      ?? '',
            $requestData['order'][0]['column']   ?? null,
            $requestData['order'][0]['dir']      ?? null,
            $requestData['start']                ?? 0,
            $requestData['length']               ?? 10
        );

        $data  = [];
        $urut1 = 1;

        foreach ($fetch['query']->result_array() as $row) {
            $nomor = $urut1 + $requestData['start'];

            $data[] = [
                "<div align='center'>{$nomor}</div>",
                date('d/M/Y', strtotime($row['tgl_transaksi'])),
                $row['no_transaksi'],
                $row['transaksi'],
                $row['code_lv4'],
                $row['nm_material'] . ($row['trade_name'] ? '<br><small class="text-muted">' . $row['trade_name'] . '</small>' : ''),
                number_format($row['qty']),
                number_format($row['qty_book']),
                number_format($row['qty_free']),
                number_format($row['qty_transaksi']),
                number_format($row['qty_book_akhir']),
                number_format($row['qty_akhir']),
                number_format($row['qty_book_akhir']),
                number_format($row['qty_free_akhir']),
            ];
            $urut1++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => intval($fetch['totalData']),
            'recordsFiltered' => intval($fetch['totalFiltered']),
            'data'            => $data,
        ]);
    }

    public function get_query_json_kartu_stok($like_value = null, $column_order = null, $column_dir = null, $limit_start = 0, $limit_length = 10)
    {
        $columns_order_by = [
            1  => 'ks.tgl_transaksi',
            2  => 'ks.no_transaksi',
            3  => 'ks.transaksi',
            4  => 'ks.code_lv4',
            5  => 'ks.nm_material',
            6  => 'ks.qty',
            7  => 'ks.qty_book',
            8  => 'ks.qty_free',
            9  => 'ks.qty_transaksi',
            10 => 'ks.qty_book_akhir',
            11 => 'ks.qty_akhir',
            12 => 'ks.qty_book_akhir',
            13 => 'ks.qty_free_akhir',
        ];

        // Total semua (tanpa search)
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        $totalData = $this->db->count_all_results();

        // Total filtered (dengan search)
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('ks.code_lv4',    $like_value);
            $this->db->or_like('ks.nm_material',  $like_value);
            $this->db->or_like('ks.no_transaksi', $like_value);
            $this->db->or_like('ks.transaksi',    $like_value);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        // Data
        $this->db->select('ks.*');
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('ks.code_lv4',    $like_value);
            $this->db->or_like('ks.nm_material',  $like_value);
            $this->db->or_like('ks.no_transaksi', $like_value);
            $this->db->or_like('ks.transaksi',    $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('ks.tgl_transaksi', 'desc');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        return [
            'totalData'     => $totalData,
            'totalFiltered' => $totalFiltered,
            'query'         => $this->db->get(),
        ];
    }

    public function get_detail_coil_by_material($id_material, $id_gudang)
    {
        return $this->db->query("
            SELECT no_coil, kode_internal, net_weight, gross_weight, length
            FROM warehouse_stock_coil
            WHERE id_material = ?
            AND id_gudang     = ?
            ORDER BY no_coil ASC
        ", [$id_material, $id_gudang])->result_array();
    }

    public function get_json_warehouse_stock_perday($kd_gudang = '')
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start']  ?? 0);
        $length      = (int) ($requestData['length'] ?? 25);

        $date_snap = $_POST['date_snap'] ?? '';

        if (empty($date_snap)) {
            echo json_encode([
                'draw'            => intval($requestData['draw'] ?? 1),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
            return;
        }

        $snap_datetime = $date_snap . ' 23:59:59';

        // ── Logika snapshot:
        // Ambil coil yang punya status IN s.d. tanggal,
        // tapi belum ada status OUT s.d. tanggal yang sama
        // → artinya coil MASIH ADA di gudang per tanggal tersebut
        $where = " WHERE cpd.hist_date <= '{$snap_datetime}'
               AND cpd.status = 'IN'
               AND NOT EXISTS (
                   SELECT 1 FROM warehouse_coil_per_day cpd2
                   WHERE cpd2.no_coil     = cpd.no_coil
                     AND cpd2.id_gudang   = cpd.id_gudang
                     AND cpd2.status      = 'OUT'
                     AND cpd2.hist_date  <= '{$snap_datetime}'
               ) ";

        if (!empty($kd_gudang)) {
            $kd     = $this->db->escape($kd_gudang);
            $where .= " AND cpd.kd_gudang = {$kd} ";
        }

        if (!empty($search)) {
            $s      = $this->db->escape_like_str($search);
            $where .= " AND (
            ni.nama            LIKE '%{$s}%'
            OR cpd.no_coil     LIKE '%{$s}%'
            OR cpd.id_material LIKE '%{$s}%'
            OR w.nm_gudang     LIKE '%{$s}%'
        ) ";
        }

        $col_map = [
            0 => 'cpd.id',
            1 => 'ni.nama',
            2 => 'w.nm_gudang',
            3 => 'cpd.no_coil',
            4 => 'cpd.kode_internal',
            5 => 'cpd.net_weight',
            6 => 'cpd.gross_weight',
            7 => 'cpd.length',
            8 => 'cpd.status',
        ];

        $order_col = $requestData['order'][0]['column'] ?? 1;
        $order_dir = $requestData['order'][0]['dir']    ?? 'asc';
        $order_by  = $col_map[$order_col] ?? 'ni.nama';

        $base_from = "
        FROM warehouse_coil_per_day cpd
        LEFT JOIN warehouse w
            ON w.kd_gudang = cpd.kd_gudang
        LEFT JOIN new_inventory_4 ni
            ON ni.code_lv4 = cpd.id_material
        {$where}
    ";

        // ── Count ──────────────────────────────────────────────────────────
        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_from}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        // ── Data ───────────────────────────────────────────────────────────
        $sql = "
        SELECT
            cpd.id,
            cpd.id_material,
            cpd.no_coil,
            cpd.kode_internal,
            cpd.gross_weight,
            cpd.net_weight,
            cpd.length,
            cpd.kd_gudang,
            cpd.status,
            ni.nama       AS nm_barang,
            ni.trade_name,
            w.nm_gudang
        {$base_from}
        ORDER BY {$order_by} {$order_dir}
        LIMIT {$start}, {$length}
    ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $status_badge = "<span class='badge bg-success'>IN</span>"; // selalu IN

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['nm_barang']
                    . '<br><small class="text-muted">' . $row['id_material'] . '</small>'
                    . ($row['trade_name'] ? '<br><small class="text-info">' . $row['trade_name'] . '</small>' : ''),
                "<div class='text-center'>" . $row['nm_gudang'] . "</div>",
                "<div class='text-center'>" . $row['no_coil'] . "</div>",
                "<div class='text-center'>" . ($row['kode_internal'] ?? '-') . "</div>",
                "<div class='text-end'>"    . number_format((float) $row['net_weight'],   3, ',', '.') . "</div>",
                "<div class='text-end'>"    . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>"    . number_format((float) $row['length'],       3, ',', '.') . "</div>",
                "<div class='text-center'>" . $status_badge . "</div>",
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

    // ── HISTORY PER PACK PER DAY — DataTable server-side ──────────────────

    public function get_json_warehouse_pack_perday($kd_gudang = '')
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start']  ?? 0);
        $length      = (int) ($requestData['length'] ?? 25);

        $date_snap = $_POST['date_snap'] ?? '';

        if (empty($date_snap)) {
            echo json_encode([
                'draw'            => intval($requestData['draw'] ?? 1),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
            return;
        }

        $snap_datetime = $date_snap . ' 23:59:59';

        // Ambil coils yang masih IN per tanggal (belum OUT)
        $where_gudang = '';
        if (!empty($kd_gudang)) {
            $kd = $this->db->escape($kd_gudang);
            $where_gudang = " AND cpd.kd_gudang = {$kd}";
        }

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (wp.pack_code LIKE '%{$s}%' OR ni.nama LIKE '%{$s}%' OR ni.trade_name LIKE '%{$s}%')";
        }

        // Sub-query: coils masih IN per tanggal, join ke warehouse_stock_coil untuk id_pack
        $base_sql = "
            FROM warehouse_coil_per_day cpd
            JOIN warehouse_stock_coil wsc ON wsc.no_coil = cpd.no_coil AND wsc.id_gudang = cpd.id_gudang
            JOIN warehouse_pack wp ON wp.id = wsc.id_pack
            LEFT JOIN new_inventory_4 ni ON ni.code_lv4 = cpd.id_material
            WHERE cpd.hist_date <= '{$snap_datetime}'
              AND cpd.status = 'IN'
              AND NOT EXISTS (
                  SELECT 1 FROM warehouse_coil_per_day cpd2
                  WHERE cpd2.no_coil    = cpd.no_coil
                    AND cpd2.id_gudang  = cpd.id_gudang
                    AND cpd2.status     = 'OUT'
                    AND cpd2.hist_date <= '{$snap_datetime}'
              )
              {$where_gudang}
              {$where_search}
        ";

        // Count distinct packs
        $total_q   = $this->db->query("SELECT COUNT(DISTINCT wp.id) AS cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        // Query grouped by pack
        $order_col = $requestData['order'][0]['column'] ?? 1;
        $order_dir = $requestData['order'][0]['dir']    ?? 'asc';
        $col_map   = [1 => 'wp.pack_code', 3 => 'roll_count', 4 => 'total_nw'];
        $order_by  = $col_map[$order_col] ?? 'wp.pack_code';

        $sql = "
            SELECT
                wp.id AS id_pack,
                wp.pack_code,
                wp.kd_gudang,
                GROUP_CONCAT(DISTINCT CONCAT(IFNULL(ni.trade_name,''), '||', IFNULL(ni.nama,'')) SEPARATOR ';;') AS materials_concat,
                COUNT(cpd.id) AS roll_count,
                SUM(cpd.net_weight) AS total_nw,
                AVG(cpd.net_weight) AS nw_per_roll,
                SUM(cpd.gross_weight) AS total_gw,
                AVG(cpd.gross_weight) AS gw_per_roll
            {$base_sql}
            GROUP BY wp.id
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $mat_html = '';
            if (!empty($row['materials_concat'])) {
                $mats = array_unique(explode(';;', $row['materials_concat']));
                foreach ($mats as $m) {
                    $parts = explode('||', $m);
                    $trade = $parts[0] ?? '';
                    $nm    = $parts[1] ?? '';
                    $mat_html .= '<div style="font-size:11px;"><span class="text-primary me-1">&#9679;</span><b>' . htmlspecialchars($trade) . '</b> <small class="text-muted">(' . htmlspecialchars($nm) . ')</small></div>';
                }
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                "<div class='text-center'><span class='badge bg-primary'>" . htmlspecialchars($row['pack_code']) . "</span></div>",
                $mat_html,
                "<div class='text-center'>" . (int) $row['roll_count'] . "</div>",
                "<div class='text-end'>" . number_format((float) $row['total_nw'], 2, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['nw_per_roll'], 2, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['total_gw'], 2, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gw_per_roll'], 2, ',', '.') . "</div>",
                "<div class='text-center'><span class='badge bg-success'>IN</span></div>",
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

    public function get_json_stock_value_perday($kd_gudang = '')
    {
        $requestData     = $_REQUEST;
        $id_gudang       = $_POST['id_gudang']       ?? '';
        $filter_material = $_POST['filter_material'] ?? '';
        $date_snap       = $_POST['date_snap']        ?? '';

        if (empty($date_snap)) {
            echo json_encode([
                'draw'            => intval($requestData['draw'] ?? 1),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
            return;
        }

        $snap_datetime = $date_snap . ' 23:59:59';

        // ── Subquery: ambil row TERBARU per material per gudang s.d. tanggal ──
        // Menggunakan subquery untuk dapat row terakhir (bukan GROUP BY yang ambil sembarang)
        $where_sub = " WHERE spd.hist_date <= '{$snap_datetime}' ";

        if (!empty($kd_gudang)) {
            $kd         = $this->db->escape($kd_gudang);
            $where_sub .= " AND spd.kd_gudang = {$kd} ";
        }
        if (!empty($id_gudang)) {
            $where_sub .= " AND spd.id_gudang = " . (int) $id_gudang . " ";
        }
        if (!empty($filter_material)) {
            $f          = $this->db->escape_like_str($filter_material);
            $where_sub .= " AND (spd.nm_material LIKE '%{$f}%' OR spd.id_material LIKE '%{$f}%') ";
        }

        $search = $_POST['search']['value'] ?? '';
        if (!empty($search)) {
            $s          = $this->db->escape_like_str($search);
            $where_sub .= " AND (
            spd.nm_material  LIKE '%{$s}%'
            OR spd.id_material LIKE '%{$s}%'
            OR spd.kd_gudang   LIKE '%{$s}%'
        ) ";
        }

        // ── Count total distinct material+gudang yang punya data s.d. tanggal ─
        $cnt_row = $this->db->query("
        SELECT COUNT(*) AS cnt
        FROM (
            SELECT spd.id_material, spd.id_gudang
            FROM warehouse_stock_per_day spd
            {$where_sub}
            GROUP BY spd.id_material, spd.id_gudang
        ) t
        ")->row();
        $totalData = $cnt_row ? (int) $cnt_row->cnt : 0;

        // ── Order ─────────────────────────────────────────────────────────────
        $col_order = [
            1 => 'latest.id_material',
            2 => 'latest.nm_material',
            3 => 'latest.kd_gudang',
            4 => 'latest.qty_stock',
            5 => 'latest.harga_beli',
            6 => 'latest.total_nilai',
        ];
        $order_idx = (int) ($requestData['order'][0]['column'] ?? 2);
        $order_dir = in_array(strtolower($requestData['order'][0]['dir'] ?? ''), ['asc', 'desc'])
            ? $requestData['order'][0]['dir'] : 'asc';
        $order_by  = $col_order[$order_idx] ?? 'latest.nm_material';

        $start  = (int) ($requestData['start']  ?? 0);
        $length = (int) ($requestData['length'] ?? 25);
        $limit  = ($length != -1) ? "LIMIT {$start}, {$length}" : '';

        // ── Query utama: row terbaru per material+gudang s.d. tanggal ─────────
        // Pakai JOIN ke subquery MAX(hist_date) agar dapat nilai snapshot terakhir
        $sql = "
        SELECT
            latest.id_material,
            latest.nm_material,
            latest.id_gudang,
            latest.kd_gudang,
            latest.qty_stock,
            latest.harga_beli,
            latest.total_nilai,
            latest.hist_date,
            w.nm_gudang,
            ws.trade_name,
            -- Jumlah coil netto sampai tanggal ini:
            -- IN yang masuk s.d. tanggal dikurangi OUT s.d. tanggal
            (
                SELECT COUNT(*)
                FROM warehouse_coil_per_day cpd
                WHERE cpd.id_material  = latest.id_material
                  AND cpd.id_gudang    = latest.id_gudang
                  AND cpd.hist_date   <= '{$snap_datetime}'
                  AND cpd.status       = 'IN'
            ) -
            (
                SELECT COUNT(*)
                FROM warehouse_coil_per_day cpd
                WHERE cpd.id_material  = latest.id_material
                  AND cpd.id_gudang    = latest.id_gudang
                  AND cpd.hist_date   <= '{$snap_datetime}'
                  AND cpd.status       = 'OUT'
            ) AS jumlah_coil
        FROM (
            SELECT spd.*
            FROM warehouse_stock_per_day spd
            INNER JOIN (
                SELECT id_material, id_gudang, MAX(hist_date) AS max_date
                FROM warehouse_stock_per_day
                WHERE hist_date <= '{$snap_datetime}'
                GROUP BY id_material, id_gudang
            ) mx ON mx.id_material = spd.id_material
                AND mx.id_gudang   = spd.id_gudang
                AND mx.max_date    = spd.hist_date
            {$where_sub}
        ) latest
        LEFT JOIN warehouse w  ON w.id        = latest.id_gudang
        LEFT JOIN warehouse_stock ws
               ON ws.code_lv4  = latest.id_material
              AND ws.id_gudang  = latest.id_gudang
        ORDER BY {$order_by} {$order_dir}
        {$limit}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $btn_history = "
            <button class='btn btn-sm btn-info'
                onclick=\"showHistory('{$row['id_material']}','{$row['nm_material']}','{$row['id_gudang']}')\">
                <i class='fa fa-history'></i> History
            </button>";

            $jml_coil = max(0, (int) $row['jumlah_coil']);

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id_material'],
                $row['nm_material'] . ($row['trade_name'] ? '<br><small class="text-muted">' . $row['trade_name'] . '</small>' : ''),
                $row['nm_gudang'],
                "<div class='text-center'>" . date('d/m/Y', strtotime($row['hist_date'])) . "</div>",
                "<div class='text-center'>{$jml_coil}</div>",
                "<div class='text-right'>" . number_format((float) $row['qty_stock'],   3, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((float) $row['harga_beli'],  3, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((float) $row['total_nilai'], 3, ',', '.') . "</div>",
                "<div class='text-center'>{$btn_history}</div>",
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

    /**
     * Grand total untuk footer DataTables stock value per-day
     */
    public function get_grand_total_stock_value_perday(
        $kd_gudang = '',
        $id_gudang = '',
        $filter_material = '',
        $date_snap = '',
        $date_to = ''  // $date_to diabaikan, tetap ada agar tidak breaking
    ) {
        if (empty($date_snap)) return 0;

        $snap_datetime = $date_snap . ' 23:59:59';

        $where_sub = " WHERE spd.hist_date <= '{$snap_datetime}' ";
        if (!empty($kd_gudang)) {
            $kd         = $this->db->escape($kd_gudang);
            $where_sub .= " AND spd.kd_gudang = {$kd} ";
        }
        if (!empty($id_gudang)) {
            $where_sub .= " AND spd.id_gudang = " . (int) $id_gudang . " ";
        }
        if (!empty($filter_material)) {
            $f          = $this->db->escape_like_str($filter_material);
            $where_sub .= " AND (spd.nm_material LIKE '%{$f}%' OR spd.id_material LIKE '%{$f}%') ";
        }

        $row = $this->db->query("
        SELECT SUM(latest.total_nilai) AS grand_total
        FROM (
            SELECT spd.total_nilai
            FROM warehouse_stock_per_day spd
            INNER JOIN (
                SELECT id_material, id_gudang, MAX(hist_date) AS max_date
                FROM warehouse_stock_per_day
                WHERE hist_date <= '{$snap_datetime}'
                GROUP BY id_material, id_gudang
            ) mx ON mx.id_material = spd.id_material
                AND mx.id_gudang   = spd.id_gudang
                AND mx.max_date    = spd.hist_date
            {$where_sub}
        ) latest
        ")->row();

        return $row ? (float) $row->grand_total : 0;
    }

    public function export_excel_coil_perday($kd_gudang = '', $date_snap = '')
    {
        if (empty($date_snap)) {
            echo 'Tanggal tidak boleh kosong.';
            return;
        }

        $snap_datetime = $date_snap . ' 23:59:59';

        // ── WHERE (logika snapshot) ────────────────────────────────────────
        $where = " WHERE cpd.hist_date <= '{$snap_datetime}'
               AND cpd.status = 'IN'
               AND NOT EXISTS (
                   SELECT 1 FROM warehouse_coil_per_day cpd2
                   WHERE cpd2.no_coil    = cpd.no_coil
                     AND cpd2.id_gudang  = cpd.id_gudang
                     AND cpd2.status     = 'OUT'
                     AND cpd2.hist_date <= '{$snap_datetime}'
               ) ";

        if (!empty($kd_gudang)) {
            $kd     = $this->db->escape($kd_gudang);
            $where .= " AND cpd.kd_gudang = {$kd} ";
        }

        // ── Query — tambah join ke warehouse_stock untuk costbook ──────────
        $rows = $this->db->query("
            SELECT
                cpd.id_material,
                cpd.no_coil,
                cpd.kode_internal,
                cpd.gross_weight,
                cpd.net_weight,
                cpd.length,
                cpd.kd_gudang,
                cpd.status,
                cpd.hist_date,
                ni.nama        AS nm_barang,
                ni.trade_name,
                ws.harga_beli,
                w.nm_gudang
            FROM warehouse_coil_per_day cpd
            LEFT JOIN warehouse w
                ON w.kd_gudang = cpd.kd_gudang
            LEFT JOIN new_inventory_4 ni
                ON ni.code_lv4 = cpd.id_material
            LEFT JOIN warehouse_stock ws
            ON ws.code_lv4 = cpd.id_material
            {$where}
            ORDER BY ni.nama ASC, cpd.no_coil ASC
        ")->result_array();

        // ── Label ──────────────────────────────────────────────────────────
        $label_gudang = 'Semua Gudang';
        if ($kd_gudang === 'PRO') $label_gudang = 'Gudang Produksi';
        if ($kd_gudang === 'SLI') $label_gudang = 'Gudang Slitting';

        $label_date   = ' | Per Tanggal: ' . date('d/m/Y', strtotime($date_snap));

        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stock Coil Per Day');

        // ── Judul ──────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue(
            'A1',
            'STOCK COIL — ' . strtoupper($label_gudang) . $label_date
        );
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'Dicetak: ' . date('d F Y H:i'));
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header ─────────────────────────────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Nama Material (Lv.4)',
            'C' => 'Trade Name',
            'D' => 'Gudang',
            'E' => 'No. Coil',
            'F' => 'Kode Internal',
            'G' => 'Status',
            'H' => 'Nett Weight (Kg)',
            'I' => 'Gross Weight (Kg)',
            'J' => 'Length (M)',
            'K' => 'Costbook',
            'L' => 'Total Value',
        ];

        foreach ($headers as $col => $label) {
            $cell = $col . '4';
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true)
                ->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1F4E79');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ── Lebar kolom ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(22);

        // ── Data rows ──────────────────────────────────────────────────────
        $row               = 5;
        $total_nett        = 0.0;
        $total_gross       = 0.0;
        $total_len         = 0.0;
        $grand_total_value = 0.0;

        foreach ($rows as $no => $d) {
            $net_weight   = (float) ($d['net_weight']   ?? 0);
            $gross_weight = (float) ($d['gross_weight'] ?? 0);
            $length       = (float) ($d['length']       ?? 0);
            $costbook     = (float) ($d['harga_beli']   ?? 0);
            $total_value  = $costbook * $net_weight;
            $status       = $d['status'] ?? 'IN';

            $sheet->setCellValueExplicit('A' . $row, $no + 1,                  PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, $d['nm_barang'],          PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $d['trade_name'] ?? '',   PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $d['nm_gudang'],          PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $d['no_coil'],            PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, $d['kode_internal'] ?? '-', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $row, $status,                  PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('H' . $row, $net_weight,              PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $row, $gross_weight,            PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J' . $row, $length,                  PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K' . $row, $costbook,                PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L' . $row, $total_value,             PHPExcel_Cell_DataType::TYPE_NUMERIC);

            // ── Border ────────────────────────────────────────────────────
            $sheet->getStyle('A' . $row . ':L' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

            // ── Format angka ──────────────────────────────────────────────
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // ── Alignment ─────────────────────────────────────────────────
            $sheet->getStyle('A' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // ── Warna status (IN = hijau, OUT = merah) ────────────────────
            $bg_status = $status === 'IN' ? 'E2EFDA' : 'FCE4D6';
            $sheet->getStyle('G' . $row)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bg_status);

            // ── Zebra stripe (skip kolom G agar warna status tidak tertimpa)
            if ($no % 2 === 0) {
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'J', 'K', 'L'] as $col) {
                    $sheet->getStyle($col . $row)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('EBF3FA');
                }
            }

            $total_nett        += $net_weight;
            $total_gross       += $gross_weight;
            $total_len         += $length;
            $grand_total_value += $total_value;
            $row++;
        }

        // ── Total row ──────────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'TOTAL',             PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . $row, $total_nett,         PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('I' . $row, $total_gross,        PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('J' . $row, $total_len,          PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('K' . $row, '',                  PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('L' . $row, $grand_total_value,  PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $sheet->getStyle('A' . $row . ':L' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':L' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':L' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // ── Output ─────────────────────────────────────────────────────────
        $filename  = 'Stock_Coil_PerDay_'
            . str_replace(' ', '_', $label_gudang)
            . '_' . date('Ymd', strtotime($date_snap)) . '.xls';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    // ── STOCK PER PACK — DataTable server-side ────────────────────────────

    public function get_json_warehouse_pack($kd_gudang = '')
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start']  ?? 0);
        $length      = (int) ($requestData['length'] ?? 25);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'asc';

        $col_map = [
            1 => 'wp.pack_code',
            2 => 'materials',
            3 => 'roll_count',
            4 => 'total_nw',
            5 => 'nw_per_roll',
            6 => 'total_gw',
            7 => 'gw_per_roll',
        ];
        $order_by = $col_map[$order_col] ?? 'wp.pack_code';

        $where_gudang = '';
        if (!empty($kd_gudang)) {
            $kd = $this->db->escape($kd_gudang);
            $where_gudang = " AND wp.kd_gudang = {$kd}";
        }

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (wp.pack_code LIKE '%{$s}%' OR wsc.nm_material LIKE '%{$s}%' OR wsc.trade_name LIKE '%{$s}%')";
        }

        // Count total packs
        $count_sql = "
            SELECT COUNT(DISTINCT wp.id) AS cnt
            FROM warehouse_pack wp
            LEFT JOIN warehouse_stock_coil wsc ON wsc.id_pack = wp.id AND wsc.is_baby_coil = 1
            WHERE wp.status = 1
            {$where_gudang}
            {$where_search}
        ";
        $total_q   = $this->db->query($count_sql)->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        // Also count normal coils (is_baby_coil = 0 AND qty_roll = 1 means it IS a physical coil)
        // The pack view shows: baby coils + normal coils (not mother-with-baby)
        $sql = "
            SELECT
                wp.id AS id_pack,
                wp.pack_code,
                wp.kd_gudang,
                GROUP_CONCAT(DISTINCT CONCAT(wsc.trade_name, '||', wsc.nm_material) SEPARATOR ';;') AS materials_concat,
                COUNT(wsc.id) AS roll_count,
                SUM(wsc.net_weight) AS total_nw,
                AVG(wsc.net_weight) AS nw_per_roll,
                SUM(wsc.gross_weight) AS total_gw,
                AVG(wsc.gross_weight) AS gw_per_roll
            FROM warehouse_pack wp
            LEFT JOIN warehouse_stock_coil wsc ON wsc.id_pack = wp.id
                AND (wsc.is_baby_coil = 1 OR (wsc.is_baby_coil = 0 AND wsc.qty_roll <= 1))
            WHERE wp.status = 1
            {$where_gudang}
            {$where_search}
            GROUP BY wp.id
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            // Parse materials_concat → build material list HTML
            $mat_html = '';
            if (!empty($row['materials_concat'])) {
                $mats = array_unique(explode(';;', $row['materials_concat']));
                foreach ($mats as $m) {
                    $parts = explode('||', $m);
                    $trade = $parts[0] ?? '';
                    $nm    = $parts[1] ?? '';
                    $mat_html .= '<div style="font-size:11px;"><span class="text-primary me-1">&#9679;</span><b>' . htmlspecialchars($trade) . '</b> <small class="text-muted">(' . htmlspecialchars($nm) . ')</small></div>';
                }
            }

            // QR button (content = pack_code)
            $qr_html = "<button type='button' class='btn btn-sm btn-info btn-show-qr' data-qr='" . htmlspecialchars($row['pack_code']) . "'><i class='fa fa-qrcode'></i></button>";

            // Detail button
            $detail_html = "<button type='button' class='btn btn-sm btn-outline-primary btn-show-pack-detail' data-pack-id='" . $row['id_pack'] . "' data-pack-code='" . htmlspecialchars($row['pack_code']) . "'><i class='fa fa-eye'></i></button>";

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                "<div class='text-center'><span class='badge bg-primary'>" . htmlspecialchars($row['pack_code']) . "</span></div>",
                $mat_html,
                "<div class='text-center'>" . (int) $row['roll_count'] . "</div>",
                "<div class='text-end'>" . number_format((float) $row['total_nw'], 2, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['nw_per_roll'], 2, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['total_gw'], 2, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gw_per_roll'], 2, ',', '.') . "</div>",
                "<div class='text-center d-flex justify-content-center gap-1'>{$qr_html} {$detail_html}</div>",
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

    // ── GET PACK DETAIL (untuk modal) ─────────────────────────────────────

    public function get_pack_detail($id_pack)
    {
        return $this->db->query("
            SELECT wsc.no_coil, wsc.nm_material, wsc.trade_name, wsc.kode_internal,
                   wsc.net_weight, wsc.gross_weight, wsc.length, wsc.is_baby_coil, wsc.qty_roll
            FROM warehouse_stock_coil wsc
            WHERE wsc.id_pack = ?
              AND (wsc.is_baby_coil = 1 OR (wsc.is_baby_coil = 0 AND wsc.qty_roll <= 1))
            ORDER BY wsc.nm_material ASC, wsc.no_coil ASC
        ", [$id_pack])->result_array();
    }
}
