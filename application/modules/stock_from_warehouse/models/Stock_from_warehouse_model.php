<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stock_from_warehouse_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // SERVER-SIDE: Production Transit (warehouse_stock_coil WHERE kd_gudang = 'PRT')
    // ---------------------------------------------------------------

    public function get_json_transit()
    {
        $requestData = $_REQUEST;
        $search      = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $start       = (int) (isset($requestData['start']) ? $requestData['start'] : 0);
        $length      = (int) (isset($requestData['length']) ? $requestData['length'] : 10);
        $order_col   = isset($requestData['order'][0]['column']) ? $requestData['order'][0]['column'] : 1;
        $order_dir   = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';

        $col_map = [
            1 => 'pt.nm_material',
            2 => 'pt.no_coil',
            3 => 'pt.kode_internal',
            4 => 'pt.net_weight',
            5 => 'pt.gross_weight',
            6 => 'pt.length',
        ];
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'pt.nm_material';

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
            FROM warehouse_stock_coil pt
            WHERE pt.kd_gudang = 'PRT'
              AND pt.status = 1
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
                pt.kd_gudang
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
                htmlspecialchars(isset($row['nm_material']) ? $row['nm_material'] : '')
                    . '<br><small class="text-muted">' . htmlspecialchars(isset($row['id_material']) ? $row['id_material'] : '') . '</small>'
                    . (isset($row['trade_name']) && $row['trade_name'] ? '<br><small class="text-info">' . htmlspecialchars($row['trade_name']) . '</small>' : ''),
                "<div class='text-center'>" . htmlspecialchars(isset($row['no_coil']) ? $row['no_coil'] : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars(isset($row['kode_internal']) ? $row['kode_internal'] : '-') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['length'], 3, ',', '.') . "</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval(isset($requestData['draw']) ? $requestData['draw'] : 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ---------------------------------------------------------------
    // SERVER-SIDE: WIP (warehouse_stock_coil WHERE kd_gudang = 'WIP')
    // ---------------------------------------------------------------

    public function get_json_wip()
    {
        $requestData = $_REQUEST;
        $search      = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $start       = (int) (isset($requestData['start']) ? $requestData['start'] : 0);
        $length      = (int) (isset($requestData['length']) ? $requestData['length'] : 10);
        $order_col   = isset($requestData['order'][0]['column']) ? $requestData['order'][0]['column'] : 1;
        $order_dir   = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';

        $col_map = [
            1 => 'wip.nm_material',
            2 => 'wip.no_coil',
            3 => 'wip.kode_internal',
            4 => 'wip.net_weight',
            5 => 'wip.gross_weight',
            6 => 'wip.length',
        ];
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'wip.nm_material';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (wip.nm_material LIKE '%{$s}%'
                               OR wip.no_coil     LIKE '%{$s}%'
                               OR wip.kode_internal LIKE '%{$s}%'
                               OR wip.id_material LIKE '%{$s}%')";
        }

        $base_from = "
            FROM warehouse_stock_coil wip
            WHERE wip.kd_gudang = 'WIP'
              AND wip.status = 1
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
                htmlspecialchars(isset($row['nm_material']) ? $row['nm_material'] : '')
                    . '<br><small class="text-muted">' . htmlspecialchars(isset($row['id_material']) ? $row['id_material'] : '') . '</small>',
                "<div class='text-center'>" . htmlspecialchars(isset($row['no_coil']) ? $row['no_coil'] : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars(isset($row['kode_internal']) ? $row['kode_internal'] : '-') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['length'], 3, ',', '.') . "</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval(isset($requestData['draw']) ? $requestData['draw'] : 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ---------------------------------------------------------------
    // SERVER-SIDE: On Hold (warehouse_stock_coil WHERE id_gudang = 5, kd_gudang = 'HLD')
    // ---------------------------------------------------------------

    public function get_json_on_hold()
    {
        $requestData = $_REQUEST;
        $search      = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $start       = (int) (isset($requestData['start']) ? $requestData['start'] : 0);
        $length      = (int) (isset($requestData['length']) ? $requestData['length'] : 10);
        $order_col   = isset($requestData['order'][0]['column']) ? $requestData['order'][0]['column'] : 1;
        $order_dir   = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';

        $col_map = [
            1 => 'hld.nm_material',
            2 => 'hld.no_coil',
            3 => 'hld.kode_internal',
            4 => 'hld.net_weight',
            5 => 'hld.gross_weight',
            6 => 'hld.length',
        ];
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'hld.nm_material';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (hld.nm_material LIKE '%{$s}%'
                               OR hld.no_coil     LIKE '%{$s}%'
                               OR hld.kode_internal LIKE '%{$s}%'
                               OR hld.id_material LIKE '%{$s}%'
                               OR hld.trade_name LIKE '%{$s}%')";
        }

        $base_from = "
            FROM warehouse_stock_coil hld
            WHERE hld.id_gudang = 5
              AND hld.kd_gudang = 'HLD'
              AND hld.status = 1
            {$where_search}
        ";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_from}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT
                hld.id,
                hld.id_material,
                hld.nm_material,
                hld.trade_name,
                hld.no_coil,
                hld.kode_internal,
                hld.gross_weight,
                hld.net_weight,
                hld.length,
                hld.kd_gudang
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
                htmlspecialchars(isset($row['nm_material']) ? $row['nm_material'] : '')
                    . '<br><small class="text-muted">' . htmlspecialchars(isset($row['id_material']) ? $row['id_material'] : '') . '</small>'
                    . (isset($row['trade_name']) && $row['trade_name'] ? '<br><small class="text-info">' . htmlspecialchars($row['trade_name']) . '</small>' : ''),
                "<div class='text-center'>" . htmlspecialchars(isset($row['no_coil']) ? $row['no_coil'] : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars(isset($row['kode_internal']) ? $row['kode_internal'] : '-') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['length'], 3, ',', '.') . "</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval(isset($requestData['draw']) ? $requestData['draw'] : 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ---------------------------------------------------------------
    // EXPORT: Stock per Gudang (Transit / WIP / On Hold)
    // ---------------------------------------------------------------

    public function export_stock($kd_gudang, $label_gudang, $id_gudang = null)
    {
        $where_clauses = "WHERE kd_gudang = '" . $this->db->escape_str($kd_gudang) . "' AND status = 1";

        if ($id_gudang !== null) {
            $where_clauses .= " AND id_gudang = " . (int) $id_gudang;
        }

        $rows = $this->db->query("
            SELECT
                id_material,
                nm_material,
                trade_name,
                no_coil,
                kode_internal,
                net_weight,
                gross_weight,
                length,
                harga_beli
            FROM warehouse_stock_coil
            {$where_clauses}
            ORDER BY nm_material ASC, no_coil ASC
        ")->result_array();

        ini_set('memory_limit', '512M');
        $ci = &get_instance();
        $ci->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stock ' . $kd_gudang);

        // ── Judul ──────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'STOCK FROM WAREHOUSE — ' . strtoupper($label_gudang));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Dicetak: ' . date('d F Y H:i'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header ─────────────────────────────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Nama Material',
            'C' => 'Trade Name',
            'D' => 'No. Coil',
            'E' => 'Kode Internal',
            'F' => 'Nett Weight (Kg)',
            'G' => 'Gross Weight (Kg)',
            'H' => 'Length (M)',
            'I' => 'Costbook',
            'J' => 'Total Value',
        ];

        foreach ($headers as $col => $label) {
            $cell = $col . '4';
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1F4E79');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ── Lebar kolom ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(20);

        // ── Data rows ──────────────────────────────────────────────────────
        $row               = 5;
        $total_nett        = 0.0;
        $total_gross       = 0.0;
        $total_len         = 0.0;
        $grand_total_value = 0.0;

        foreach ($rows as $no => $d) {
            $net_weight   = (float) (isset($d['net_weight']) ? $d['net_weight'] : 0);
            $gross_weight = (float) (isset($d['gross_weight']) ? $d['gross_weight'] : 0);
            $length       = (float) (isset($d['length']) ? $d['length'] : 0);
            $costbook     = (float) (isset($d['harga_beli']) ? $d['harga_beli'] : 0);
            $total_value  = $costbook * $net_weight;

            $sheet->setCellValueExplicit('A' . $row, $no + 1, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, isset($d['nm_material']) ? $d['nm_material'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, isset($d['trade_name']) ? $d['trade_name'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, isset($d['no_coil']) ? $d['no_coil'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, isset($d['kode_internal']) ? $d['kode_internal'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, $net_weight, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $row, $gross_weight, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $row, $length, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $row, $costbook, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J' . $row, $total_value, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            // Border
            $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

            // Format angka
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // Zebra stripe
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FA');
            }

            $total_nett        += $net_weight;
            $total_gross       += $gross_weight;
            $total_len         += $length;
            $grand_total_value += $total_value;
            $row++;
        }

        // ── Total row ──────────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'TOTAL', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . $row, $total_nett, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('G' . $row, $total_gross, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('H' . $row, $total_len, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('I' . $row, '', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('J' . $row, $grand_total_value, PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // ── Output ─────────────────────────────────────────────────────────
        $filename = 'Stock_' . $kd_gudang . '_' . date('Ymd_His') . '.xls';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    // ---------------------------------------------------------------
    // SERVER-SIDE: History Per Days (warehouse_stock_transaction_detail)
    // ---------------------------------------------------------------

    public function get_json_history()
    {
        $requestData = $_REQUEST;
        $search      = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $start       = (int) (isset($requestData['start']) ? $requestData['start'] : 0);
        $length      = (int) (isset($requestData['length']) ? $requestData['length'] : 10);
        $order_col   = isset($requestData['order'][0]['column']) ? $requestData['order'][0]['column'] : 1;
        $order_dir   = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';

        $date_filter = isset($requestData['date_filter']) ? $requestData['date_filter'] : '';
        $kd_gudang   = isset($requestData['kd_gudang']) ? $requestData['kd_gudang'] : '';

        $col_map = [
            1 => 'h.nm_material',
            2 => 'h.no_coil',
            3 => 'h.kode_internal',
            4 => 'h.kd_gudang',
            5 => 'h.net_weight',
            6 => 'h.gross_weight',
            7 => 'h.length',
            8 => 'h.kode_trans',
        ];
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'h.created_at';

        $where_clauses = "WHERE 1=1";

        if (!empty($date_filter)) {
            $where_clauses .= " AND DATE(h.created_at) <= '" . $this->db->escape_str($date_filter) . "'";
        }

        if (!empty($kd_gudang)) {
            $where_clauses .= " AND h.kd_gudang = '" . $this->db->escape_str($kd_gudang) . "'";
        }

        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_clauses .= " AND (h.nm_material LIKE '%{$s}%'
                                 OR h.no_coil LIKE '%{$s}%'
                                 OR h.kode_internal LIKE '%{$s}%'
                                 OR h.id_material LIKE '%{$s}%'
                                 OR h.kode_trans LIKE '%{$s}%')";
        }

        $base_from = "FROM warehouse_stock_transaction_detail h {$where_clauses}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_from}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT
                h.id,
                h.id_material,
                h.nm_material,
                h.no_coil,
                h.kode_internal,
                h.kd_gudang,
                h.net_weight,
                h.gross_weight,
                h.length,
                h.kode_trans,
                h.created_at
            {$base_from}
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            // Badge gudang asal
            $gudang_label = isset($row['kd_gudang']) ? $row['kd_gudang'] : '-';
            if ($gudang_label == 'PRT') {
                $gudang_html = '<span class="badge bg-primary">PRT</span>';
            } elseif ($gudang_label == 'WIP') {
                $gudang_html = '<span class="badge bg-warning text-dark">WIP</span>';
            } else {
                $gudang_html = '<span class="badge bg-secondary">' . htmlspecialchars($gudang_label) . '</span>';
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                htmlspecialchars(isset($row['nm_material']) ? $row['nm_material'] : '')
                    . '<br><small class="text-muted">' . htmlspecialchars(isset($row['id_material']) ? $row['id_material'] : '') . '</small>',
                "<div class='text-center'>" . htmlspecialchars(isset($row['no_coil']) ? $row['no_coil'] : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars(isset($row['kode_internal']) ? $row['kode_internal'] : '-') . "</div>",
                "<div class='text-center'>" . $gudang_html . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-end'>" . number_format((float) $row['length'], 3, ',', '.') . "</div>",
                "<div class='text-center'><small>" . htmlspecialchars(isset($row['kode_trans']) ? $row['kode_trans'] : '-') . "</small></div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval(isset($requestData['draw']) ? $requestData['draw'] : 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ---------------------------------------------------------------
    // EXPORT: History Per Days (XLS format menggunakan PHPExcel)
    // ---------------------------------------------------------------

    public function get_history_for_export($date_filter, $kd_gudang)
    {
        if (empty($date_filter)) {
            echo 'Tanggal tidak boleh kosong.';
            return;
        }

        $where_clauses = "WHERE DATE(h.created_at) = '" . $this->db->escape_str($date_filter) . "'";

        if (!empty($kd_gudang)) {
            $where_clauses .= " AND h.kd_gudang = '" . $this->db->escape_str($kd_gudang) . "'";
        }

        $rows = $this->db->query("
            SELECT
                h.id_material,
                h.nm_material,
                h.no_coil,
                h.kode_internal,
                h.kd_gudang,
                h.net_weight,
                h.gross_weight,
                h.length,
                h.price_per_coil,
                h.cost_book,
                h.kode_trans,
                h.created_at
            FROM warehouse_stock_transaction_detail h
            {$where_clauses}
            ORDER BY h.nm_material ASC, h.no_coil ASC
        ")->result_array();

        // ── Label ──────────────────────────────────────────────────────────
        $label_source = 'Semua Sumber';
        if ($kd_gudang === 'PRO') $label_source = 'Production (PRO)';
        if ($kd_gudang === 'WIP') $label_source = 'WIP';

        $label_date = date('d/m/Y', strtotime($date_filter));

        ini_set('memory_limit', '512M');
        $ci = &get_instance();
        $ci->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('History Per Day');

        // ── Judul ──────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'HISTORY STOCK FROM WAREHOUSE — ' . strtoupper($label_source) . ' | Tanggal: ' . $label_date);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Dicetak: ' . date('d F Y H:i'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header ─────────────────────────────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Nama Material',
            'C' => 'No. Coil',
            'D' => 'Kode Internal',
            'E' => 'Gudang Asal',
            'F' => 'Nett Weight (Kg)',
            'G' => 'Gross Weight (Kg)',
            'H' => 'Length (M)',
            'I' => 'Costbook',
            'J' => 'Total Value',
            'K' => 'Kode Transaksi',
        ];

        foreach ($headers as $col => $label) {
            $cell = $col . '4';
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1F4E79');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ── Lebar kolom ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(25);

        // ── Data rows ──────────────────────────────────────────────────────
        $row               = 5;
        $total_nett        = 0.0;
        $total_gross       = 0.0;
        $total_len         = 0.0;
        $grand_total_value = 0.0;

        foreach ($rows as $no => $d) {
            $net_weight   = (float) (isset($d['net_weight']) ? $d['net_weight'] : 0);
            $gross_weight = (float) (isset($d['gross_weight']) ? $d['gross_weight'] : 0);
            $length       = (float) (isset($d['length']) ? $d['length'] : 0);
            $costbook     = (float) (isset($d['cost_book']) ? $d['cost_book'] : 0);
            $total_value  = $costbook * $net_weight;

            $sheet->setCellValueExplicit('A' . $row, $no + 1, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, isset($d['nm_material']) ? $d['nm_material'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, isset($d['no_coil']) ? $d['no_coil'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, isset($d['kode_internal']) ? $d['kode_internal'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, isset($d['kd_gudang']) ? $d['kd_gudang'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, $net_weight, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $row, $gross_weight, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $row, $length, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $row, $costbook, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J' . $row, $total_value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K' . $row, isset($d['kode_trans']) ? $d['kode_trans'] : '', PHPExcel_Cell_DataType::TYPE_STRING);

            // Border
            $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

            // Format angka
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // Zebra stripe
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':K' . $row)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FA');
            }

            $total_nett        += $net_weight;
            $total_gross       += $gross_weight;
            $total_len         += $length;
            $grand_total_value += $total_value;
            $row++;
        }

        // ── Total row ──────────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'TOTAL', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . $row, $total_nett, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('G' . $row, $total_gross, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('H' . $row, $total_len, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('I' . $row, '', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('J' . $row, $grand_total_value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('K' . $row, '', PHPExcel_Cell_DataType::TYPE_STRING);

        $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':K' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // ── Output ─────────────────────────────────────────────────────────
        $filename = 'History_Stock_From_Warehouse_' . date('Ymd', strtotime($date_filter)) . '.xls';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }
}
