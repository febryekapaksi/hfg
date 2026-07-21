<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * Controller for Master Warehouse
 */
class Warehouse extends Admin_Controller
{
    protected $viewPermission   = 'Warehouse.View';
    protected $addPermission    = 'Warehouse.Add';
    protected $managePermission = 'Warehouse.Manage';
    protected $deletePermission = 'Warehouse.Delete';

    const GUDANG_PRODUKSI   = 'PRO';
    const GUDANG_SLITTING   = 'SLI';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Warehouse/Warehouse_model', 'All/All_model']);
        $this->template->title('Gudang');
        $this->template->page_icon('fa fa-cubes');
        date_default_timezone_set('Asia/Bangkok');
    }

    // ── PAGES ──────────────────────────────────────────────────────────────

    public function index()
    {
        $this->template->title('Material Stock (Per Coil)');
        $this->template->render('index');
    }

    public function kartu_stok()
    {
        $this->template->title('Kartu Stok');
        $this->template->page_icon('fa fa-file');
        $this->template->render('kartu_stok');
    }

    public function stock_value()
    {
        $this->template->title('Stock Value');
        $this->template->page_icon('fa fa-dollar');

        $list_gudang = $this->db->query("
            SELECT id, nm_gudang, kd_gudang
            FROM warehouse
            WHERE status = 'Y'
            ORDER BY urut ASC
        ")->result_array();

        $this->template->set(['list_gudang' => $list_gudang]);
        $this->template->render('stock_value');
    }

    // ── SERVER SIDE — STOCK PER COIL (index) ──────────────────────────────
    // Tab Produksi
    public function data_side_stock_produksi()
    {
        $this->Warehouse_model->get_json_warehouse_stock(self::GUDANG_PRODUKSI);
    }

    // Tab Slitting
    public function data_side_stock_slitting()
    {
        $this->Warehouse_model->get_json_warehouse_stock(self::GUDANG_SLITTING);
    }

    // ── SERVER SIDE — STOCK VALUE PER MATERIAL ────────────────────────────
    // Tab Produksi

    public function data_side_stock_value_produksi()
    {
        $this->Warehouse_model->get_json_stock_value(self::GUDANG_PRODUKSI);
    }

    // Tab Slitting
    public function data_side_stock_value_slitting()
    {
        $this->Warehouse_model->get_json_stock_value(self::GUDANG_SLITTING);
    }

    // Backward-compat (semua gudang)
    public function data_side_stock_value()
    {
        $this->Warehouse_model->get_json_stock_value('');
    }

    // ── GRAND TOTAL (footer DataTables) ───────────────────────────────────

    public function get_grand_total_stock_value()
    {
        $kd_gudang       = $this->input->post('kd_gudang')       ?? '';
        $id_gudang       = $this->input->post('id_gudang')       ?? '';
        $filter_material = $this->input->post('filter_material') ?? '';

        $total = $this->Warehouse_model->get_grand_total_stock_value($kd_gudang, $id_gudang, $filter_material);
        echo json_encode(['total' => number_format($total, 0, ',', '.')]);
    }

    // ── HISTORY MATERIAL ──────────────────────────────────────────────────

    public function get_history_material()
    {
        $id_material = $this->input->post('id_material');
        $id_gudang   = $this->input->post('id_gudang');

        $this->db->select('wh.*, w.nm_gudang, w.kd_gudang');
        $this->db->from('warehouse_history wh');
        $this->db->join('warehouse w', 'w.id = wh.id_gudang', 'left');
        $this->db->where('wh.id_material', $id_material);
        if (!empty($id_gudang)) {
            $this->db->where('wh.id_gudang', $id_gudang);
        }
        $this->db->order_by('wh.update_date', 'ASC');

        echo json_encode($this->db->get()->result_array());
    }

    // ── EXPORT EXCEL ──────────────────────────────────────────────────────

    public function export_excel_stock_value()
    {
        $kd_gudang = $this->input->get('kd_gudang');
        $id_gudang = $this->input->get('id_gudang');

        $where = "WHERE ws.qty_stock > 0";
        if (!empty($kd_gudang)) {
            $kd     = $this->db->escape($kd_gudang);
            $where .= " AND ws.kd_gudang = {$kd}";
        }
        if (!empty($id_gudang)) {
            $where .= " AND ws.id_gudang = " . (int) $id_gudang;
        }

        $data = $this->db->query("
            SELECT
                ws.code_lv4     AS id_material,
                ws.nm_material,
                ws.trade_name,
                ws.id_gudang,
                ws.kd_gudang,
                ws.qty_stock,
                ws.harga_beli,
                ws.total_nilai,
                w.nm_gudang,
                COUNT(wsc.id)   AS jumlah_coil
            FROM warehouse_stock ws
            LEFT JOIN warehouse w
                ON w.id = ws.id_gudang
            LEFT JOIN warehouse_stock_coil wsc
                ON CONVERT(wsc.id_material USING utf8mb4)
                = CONVERT(ws.code_lv4    USING utf8mb4)
            {$where}
            GROUP BY ws.code_lv4, ws.id_gudang
            ORDER BY ws.nm_material ASC
            ")->result_array();

        // ── Query data coil ───────────────────────────────────────────────────────
        $where_coil = "WHERE wsc.status = 1";
        if (!empty($kd_gudang)) {
            $kd          = $this->db->escape($kd_gudang);
            $where_coil .= " AND wsc.kd_gudang = {$kd}";
        }
        if (!empty($id_gudang)) {
            $where_coil .= " AND wsc.id_gudang = " . (int) $id_gudang;
        }

        $data_coil = $this->db->query("
        SELECT
            wsc.nm_material,
            wsc.trade_name,
            wsc.kode_internal,
            wsc.no_coil,
            wsc.gross_weight,
            wsc.net_weight,
            wsc.length,
            ws.harga_beli
        FROM warehouse_stock_coil wsc
        LEFT JOIN warehouse_stock ws
            ON CONVERT(ws.code_lv4 USING utf8mb4) = CONVERT(wsc.id_material USING utf8mb4)
        {$where_coil}
        ORDER BY wsc.nm_material ASC, wsc.no_coil ASC
        ")->result_array();

        $label_gudang = 'Semua Gudang';
        if ($kd_gudang === 'PRO') $label_gudang = 'Gudang Produksi';
        if ($kd_gudang === 'SLI') $label_gudang = 'Gudang Slitting';

        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();

        // ══════════════════════════════════════════════════════════════════════════
        // SHEET 1 — Stock Value
        // ══════════════════════════════════════════════════════════════════════════
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stock Value');

        // ── Judul ─────────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'STOCK VALUE REPORT — ' . strtoupper($label_gudang));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Per Tanggal: ' . date('d F Y'));
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header kolom ──────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Kode Material',
            'C' => 'Nama Material',
            'D' => 'Nama Lain (Trade Name)',
            'E' => 'Jumlah Coil',
            'F' => 'Qty Stock (Kg)',
            'G' => 'Harga Beli (Avg)',
            'H' => 'Total Nilai',
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

        // ── Lebar kolom ───────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(12); // Jumlah Coil
        $sheet->getColumnDimension('F')->setWidth(15); // Qty Stock
        $sheet->getColumnDimension('G')->setWidth(20); // Harga Beli
        $sheet->getColumnDimension('H')->setWidth(20); // Total Nilai

        // ── Data rows ─────────────────────────────────────────────────────────
        $row         = 5;
        $grand_total = 0;
        $fmt_number  = '#,##0';

        foreach ($data as $no => $d) {
            $harga_beli  = (int) round((float) $d['harga_beli']);
            $total_nilai = (int) round((float) $d['total_nilai']);
            $qty_stock   = (float) $d['qty_stock'];
            $jml_coil    = (int) $d['jumlah_coil'];
            $trade_name  = $d['trade_name'] ?? '';

            $sheet->setCellValueExplicit('A' . $row, $no + 1,           PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, $d['id_material'],  PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $d['nm_material'],  PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $trade_name,        PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $jml_coil,          PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F' . $row, $qty_stock,         PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $row, $harga_beli,        PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $row, $total_nilai,       PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($fmt_number);

            $sheet->getStyle('A' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':H' . $row)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FA');
            }

            $grand_total += $total_nilai;
            $row++;
        }

        // ── Grand total ───────────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'GRAND TOTAL',  PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . $row, $grand_total,   PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($fmt_number);

        // ══════════════════════════════════════════════════════════════════════════
        // SHEET 2 — Detail Coil
        // ══════════════════════════════════════════════════════════════════════════
        $objPHPExcel->createSheet();
        $sheet2 = $objPHPExcel->getSheet(1);
        $sheet2->setTitle('Detail Coil');

        // ── Judul ─────────────────────────────────────────────────────────────
        $sheet2->mergeCells('A1:J1');
        $sheet2->setCellValue('A1', 'DETAIL COIL — ' . strtoupper($label_gudang));
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet2->mergeCells('A2:J2');
        $sheet2->setCellValue('A2', 'Per Tanggal: ' . date('d F Y'));
        $sheet2->getStyle('A2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header kolom ──────────────────────────────────────────────────────
        $headers2 = [
            'A' => 'No',
            'B' => 'Nama Material',
            'C' => 'Trade Name',
            'D' => 'Kode Internal',
            'E' => 'No Coil',
            'F' => 'Gross Weight (Kg)',
            'G' => 'Net Weight (Kg)',
            'H' => 'Length (m)',
            'I' => 'Costbook',
            'J' => 'Total Value',
        ];

        foreach ($headers2 as $col => $label) {
            $cell = $col . '4';
            $sheet2->setCellValue($cell, $label);
            $sheet2->getStyle($cell)->getFont()->setBold(true)
                ->getColor()->setRGB('FFFFFF');
            $sheet2->getStyle($cell)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1F4E79');
            $sheet2->getStyle($cell)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle($cell)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet2->getRowDimension(4)->setRowHeight(20);

        // ── Lebar kolom ───────────────────────────────────────────────────────
        $sheet2->getColumnDimension('A')->setWidth(5);
        $sheet2->getColumnDimension('B')->setWidth(40);
        $sheet2->getColumnDimension('C')->setWidth(30);
        $sheet2->getColumnDimension('D')->setWidth(20);
        $sheet2->getColumnDimension('E')->setWidth(20);
        $sheet2->getColumnDimension('F')->setWidth(18);
        $sheet2->getColumnDimension('G')->setWidth(18);
        $sheet2->getColumnDimension('H')->setWidth(15);
        $sheet2->getColumnDimension('I')->setWidth(20);
        $sheet2->getColumnDimension('J')->setWidth(20);

        // ── Data rows ─────────────────────────────────────────────────────────
        $row2              = 5;
        $total_gross       = 0;
        $total_net         = 0;
        $total_length      = 0;
        $grand_total_value = 0;

        foreach ($data_coil as $no => $c) {
            $gross_weight = (float) $c['gross_weight'];
            $net_weight   = (float) $c['net_weight'];
            $length       = (float) $c['length'];
            $costbook     = (int) round((float) $c['harga_beli']);
            $total_value  = (int) round($costbook * $net_weight);

            $sheet2->setCellValueExplicit('A' . $row2, $no + 1,             PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('B' . $row2, $c['nm_material'],   PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('C' . $row2, $c['trade_name'],    PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('D' . $row2, $c['kode_internal'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('E' . $row2, $c['no_coil'],       PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('F' . $row2, $gross_weight,       PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('G' . $row2, $net_weight,         PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('H' . $row2, $length,             PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('I' . $row2, $costbook,           PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('J' . $row2, $total_value,        PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet2->getStyle('A' . $row2 . ':J' . $row2)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

            $sheet2->getStyle('F' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet2->getStyle('G' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet2->getStyle('H' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet2->getStyle('I' . $row2)->getNumberFormat()->setFormatCode('#,##0');
            $sheet2->getStyle('J' . $row2)->getNumberFormat()->setFormatCode('#,##0');

            $sheet2->getStyle('A' . $row2)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            if ($no % 2 === 0) {
                $sheet2->getStyle('A' . $row2 . ':J' . $row2)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FA');
            }

            $total_gross       += $gross_weight;
            $total_net         += $net_weight;
            $total_length      += $length;
            $grand_total_value += $total_value;
            $row2++;
        }

        // ── Total baris ───────────────────────────────────────────────────────
        $sheet2->mergeCells('A' . $row2 . ':E' . $row2);
        $sheet2->setCellValueExplicit('A' . $row2, 'TOTAL',             PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet2->setCellValueExplicit('F' . $row2, $total_gross,        PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->setCellValueExplicit('G' . $row2, $total_net,          PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->setCellValueExplicit('H' . $row2, $total_length,       PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->setCellValueExplicit('I' . $row2, '',                  PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet2->setCellValueExplicit('J' . $row2, $grand_total_value,  PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->getStyle('A' . $row2 . ':J' . $row2)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row2 . ':J' . $row2)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet2->getStyle('A' . $row2 . ':J' . $row2)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet2->getStyle('A' . $row2)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $sheet2->getStyle('F' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet2->getStyle('G' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet2->getStyle('H' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet2->getStyle('J' . $row2)->getNumberFormat()->setFormatCode('#,##0');

        // ── Set sheet aktif ke Sheet 1 sebelum output ─────────────────────────
        $objPHPExcel->setActiveSheetIndex(0);

        // ── Output ────────────────────────────────────────────────────────────
        $filename  = 'Stock_Value_' . str_replace(' ', '_', $label_gudang) . '_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    public function export_excel_stock_coil()
    {
        $kd_gudang = $this->input->get('kd_gudang');

        $where = "WHERE 1=1";
        if (!empty($kd_gudang)) {
            $kd     = $this->db->escape($kd_gudang);
            $where .= " AND wsc.kd_gudang = {$kd}";
        }

        $data = $this->db->query("
        SELECT
            wsc.id,
            wsc.id_material,
            wsc.no_coil,
            wsc.kode_internal,
            wsc.gross_weight,
            wsc.net_weight,
            wsc.length,
            wsc.kd_gudang,
            wsc.trade_name,
            w.nm_gudang,
            ni.nama     AS nm_barang
        FROM warehouse_stock_coil wsc
        LEFT JOIN warehouse w
            ON w.kd_gudang = wsc.kd_gudang
        LEFT JOIN new_inventory_4 ni
            ON ni.code_lv4 = wsc.id_material
        {$where}
        ORDER BY ni.nama ASC, wsc.no_coil ASC
    ")->result_array();

        $label_gudang = 'Semua Gudang';
        if ($kd_gudang === 'PRO') $label_gudang = 'Gudang Produksi';
        if ($kd_gudang === 'SLI') $label_gudang = 'Gudang Slitting';

        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stock Coil');

        // ── Judul ─────────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'STOCK MATERIAL PER COIL — ' . strtoupper($label_gudang));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Per Tanggal: ' . date('d F Y'));
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header kolom ──────────────────────────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Nama Material',
            'C' => 'Nama Lain (Trade Name)',
            'D' => 'Kode Material',
            'E' => 'No. Coil',
            'F' => 'Kode Internal',
            'G' => 'Nett Weight (Kg)',
            'H' => 'Gross Weight (Kg)',
            'I' => 'Length (M)',
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

        // ── Lebar kolom ───────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(15);

        // ── Data rows ─────────────────────────────────────────────────────────
        $row            = 5;
        $total_nett     = 0;
        $total_gross    = 0;
        $total_length   = 0;
        $fmt_decimal    = '#,##0.000';

        foreach ($data as $no => $d) {
            $net_weight   = (float) ($d['net_weight']   ?? 0);
            $gross_weight = (float) ($d['gross_weight'] ?? 0);
            $length       = (float) ($d['length']       ?? 0);
            $trade_name   = $d['trade_name']   ?? '';
            $kode_int     = $d['kode_internal'] ?? '-';

            $sheet->setCellValueExplicit('A' . $row, $no + 1,             PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, $d['nm_barang'],      PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $trade_name,          PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $d['id_material'],    PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $d['no_coil'],        PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, $kode_int,            PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $row, $net_weight,          PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $row, $gross_weight,        PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $row, $length,              PHPExcel_Cell_DataType::TYPE_NUMERIC);

            // Border
            $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

            // Format desimal
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($fmt_decimal);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($fmt_decimal);
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode($fmt_decimal);

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // Warna selang-seling
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FA');
            }

            $total_nett   += $net_weight;
            $total_gross  += $gross_weight;
            $total_length += $length;
            $row++;
        }

        // ── Grand total row ───────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'TOTAL',        PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('G' . $row, $total_nett,    PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('H' . $row, $total_gross,   PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('I' . $row, $total_length,  PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($fmt_decimal);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($fmt_decimal);
        $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode($fmt_decimal);

        // ── Output ────────────────────────────────────────────────────────────
        $filename  = 'Stock_Coil_' . str_replace(' ', '_', $label_gudang) . '_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }
    // ── KARTU STOK ────────────────────────────────────────────────────────

    public function data_side_kartu_stok()
    {
        $this->Warehouse_model->get_json_kartu_stok();
    }

    // Backward-compat
    public function data_side_warehouse_stock()
    {
        $this->Warehouse_model->get_json_warehouse_stock('');
    }

    public function get_detail_coil()
    {
        $id_material = $this->input->post('id_material');
        $id_gudang   = $this->input->post('id_gudang');

        $rows = $this->db->query("
            SELECT no_coil, kode_internal, net_weight, gross_weight, length
            FROM warehouse_stock_coil
            WHERE id_material = ?
            AND id_gudang     = ?
            ORDER BY no_coil ASC
        ", [$id_material, $id_gudang])->result_array();

        echo json_encode($rows);
    }

    public function data_side_stock_perday_produksi()
    {
        $this->Warehouse_model->get_json_warehouse_stock_perday(self::GUDANG_PRODUKSI);
    }

    public function data_side_stock_perday_slitting()
    {
        $this->Warehouse_model->get_json_warehouse_stock_perday(self::GUDANG_SLITTING);
    }

    public function data_side_stock_value_perday()
    {
        $this->Warehouse_model->get_json_stock_value_perday('');
    }

    public function data_side_stock_value_perday_produksi()
    {
        $this->Warehouse_model->get_json_stock_value_perday(self::GUDANG_PRODUKSI);
    }

    public function data_side_stock_value_perday_slitting()
    {
        $this->Warehouse_model->get_json_stock_value_perday(self::GUDANG_SLITTING);
    }

    public function get_grand_total_stock_value_perday()
    {
        $kd_gudang       = $this->input->post('kd_gudang')       ?? '';
        $id_gudang       = $this->input->post('id_gudang')       ?? '';
        $filter_material = $this->input->post('filter_material') ?? '';
        $date_snap       = $this->input->post('date_snap')       ?? '';

        $total = $this->Warehouse_model->get_grand_total_stock_value_perday(
            $kd_gudang,
            $id_gudang,
            $filter_material,
            $date_snap
        );

        echo json_encode(['total' => number_format($total, 3, ',', '.')]);
    }

    public function data_side_stock_perday()
    {
        $kd_gudang = $this->input->post('kd_gudang') ?? '';
        $this->Warehouse_model->get_json_warehouse_stock_perday($kd_gudang);
    }

    // Untuk tabel history modal (menggantikan / melengkapi get_history_material)
    public function get_history_summary()
    {
        $id_material = $this->input->post('id_material');
        $id_gudang   = $this->input->post('id_gudang');

        $rows = $this->db->query("
        SELECT *
        FROM warehouse_stock_transaction_summary
        WHERE id_material = ?
          AND id_gudang   = ?
        ORDER BY tanggal ASC, id ASC
        ", [$id_material, $id_gudang])->result_array();

        echo json_encode($rows);
    }

    // Untuk modal drill-down coil per transaksi
    public function get_summary_detail_coil()
    {
        $kode_trans      = $this->input->post('kode_trans');
        $id_material = $this->input->post('id_material');
        $id_gudang   = $this->input->post('id_gudang');

        $rows = $this->db->query("
        SELECT *
        FROM warehouse_stock_transaction_detail
        WHERE kode_trans  = ?
          AND id_material = ?
          AND id_gudang   = ?
        ORDER BY id ASC
        ", [$kode_trans, $id_material, $id_gudang])->result_array();

        echo json_encode($rows);
    }

    public function export_excel_stock_value_history()
    {
        $kd_gudang       = $this->input->get('kd_gudang')       ?? '';
        $filter_material = $this->input->get('filter_material') ?? '';
        $date_snap       = $this->input->get('date_snap')       ?? '';

        if (empty($date_snap)) {
            echo 'Tanggal tidak boleh kosong.';
            return;
        }

        $snap_datetime = $date_snap . ' 23:59:59';

        // ── WHERE builder ─────────────────────────────────────────────────────
        $where_sub = " WHERE spd.hist_date <= '{$snap_datetime}' ";
        if (!empty($kd_gudang)) {
            $kd         = $this->db->escape($kd_gudang);
            $where_sub .= " AND spd.kd_gudang = {$kd} ";
        }
        if (!empty($filter_material)) {
            $f          = $this->db->escape_like_str($filter_material);
            $where_sub .= " AND (spd.nm_material LIKE '%{$f}%' OR spd.id_material LIKE '%{$f}%') ";
        }

        // ── Query Sheet 1: Stock Value Per Day ────────────────────────────────
        $data_summary = $this->db->query("
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
                (
                    SELECT COUNT(*)
                    FROM warehouse_coil_per_day cpd
                    WHERE cpd.id_material = latest.id_material
                    AND cpd.id_gudang   = latest.id_gudang
                    AND cpd.hist_date  <= '{$snap_datetime}'
                    AND cpd.status      = 'IN'
                ) -
                (
                    SELECT COUNT(*)
                    FROM warehouse_coil_per_day cpd
                    WHERE cpd.id_material = latest.id_material
                    AND cpd.id_gudang   = latest.id_gudang
                    AND cpd.hist_date  <= '{$snap_datetime}'
                    AND cpd.status      = 'OUT'
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
            ORDER BY latest.nm_material ASC
            ")->result_array();

        // ── WHERE coil detail s.d. tanggal ────────────────────────────────────
        $where_coil = " WHERE cpd.hist_date <= '{$snap_datetime}' ";
        if (!empty($kd_gudang)) {
            $kd          = $this->db->escape($kd_gudang);
            $where_coil .= " AND cpd.kd_gudang = {$kd} ";
        }
        if (!empty($filter_material)) {
            $f           = $this->db->escape_like_str($filter_material);
            $where_coil .= " AND (ni.nama LIKE '%{$f}%' OR cpd.id_material LIKE '%{$f}%') ";
        }

        // ── Query Sheet 2: Detail Coil Per Day ───────────────────────────────
        $data_coil = $this->db->query("
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
                ni.nama       AS nm_material,
                ni.trade_name,
                ws.harga_beli,
                w.nm_gudang
            FROM warehouse_coil_per_day cpd
            LEFT JOIN new_inventory_4 ni  ON ni.code_lv4  = cpd.id_material
            LEFT JOIN warehouse_stock ws
                ON ws.code_lv4  = cpd.id_material
            LEFT JOIN warehouse w         ON w.kd_gudang  = cpd.kd_gudang
            {$where_coil}
            ORDER BY ni.nama ASC, cpd.hist_date ASC, cpd.no_coil ASC
            ")->result_array();

        // ── Label ─────────────────────────────────────────────────────────────
        $label_gudang = 'Semua Gudang';
        if ($kd_gudang === 'PRO') $label_gudang = 'Gudang Produksi';
        if ($kd_gudang === 'SLI') $label_gudang = 'Gudang Slitting';

        $label_period = ' | Per Tanggal: ' . date('d/m/Y', strtotime($date_snap));

        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();

        // ════════════════════════════════════════════════════════════════════════
        // SHEET 1 — Stock Value History (Summary)
        // ════════════════════════════════════════════════════════════════════════
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stock Value History');

        // ── Judul ─────────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'STOCK VALUE HISTORY — ' . strtoupper($label_gudang) . $label_period);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Dicetak: ' . date('d F Y H:i'));
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header kolom ──────────────────────────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Kode Material',
            'C' => 'Nama Material',
            'D' => 'Nama Lain (Trade Name)',
            'E' => 'Gudang',
            'F' => 'Tanggal',
            'G' => 'Jumlah Coil',
            'H' => 'Qty Stock (Kg)',
            'I' => 'Harga Beli / Costbook (Avg)',
            'J' => 'Total Nilai',
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

        // ── Lebar kolom ───────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(24);
        $sheet->getColumnDimension('J')->setWidth(22);

        // ── Data rows ─────────────────────────────────────────────────────────
        $row         = 5;
        $grand_total = 0.0;

        foreach ($data_summary as $no => $d) {
            $harga_beli  = (float) ($d['harga_beli']  ?? 0);
            $total_nilai = (float) ($d['total_nilai']  ?? 0);
            $qty_stock   = (float) ($d['qty_stock']    ?? 0);
            $jml_coil    = (int)    $d['jumlah_coil'];
            $trade_name  = $d['trade_name'] ?? '';
            $tgl         = !empty($d['hist_date']) ? date('d/m/Y', strtotime($d['hist_date'])) : '-';
            $nm_gudang   = ($d['nm_gudang'] ?? $d['kd_gudang']) ?? '-';

            $sheet->setCellValueExplicit('A' . $row, $no + 1,          PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, $d['id_material'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $d['nm_material'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $trade_name,       PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $nm_gudang,        PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, $tgl,              PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $row, $jml_coil,         PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $row, $qty_stock,        PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $row, $harga_beli,       PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J' . $row, $total_nilai,      PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle('A' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FA');
            }

            $grand_total += $total_nilai;
            $row++;
        }

        // ── Grand total ───────────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'GRAND TOTAL', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('J' . $row, $grand_total,  PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // ════════════════════════════════════════════════════════════════════════
        // SHEET 2 — Detail Coil History
        // ════════════════════════════════════════════════════════════════════════
        $objPHPExcel->createSheet();
        $sheet2 = $objPHPExcel->getSheet(1);
        $sheet2->setTitle('Detail Coil History');

        // ── Judul ─────────────────────────────────────────────────────────────
        $sheet2->mergeCells('A1:M1');
        $sheet2->setCellValue('A1', 'DETAIL COIL HISTORY — ' . strtoupper($label_gudang) . $label_period);
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet2->mergeCells('A2:M2');
        $sheet2->setCellValue('A2', 'Dicetak: ' . date('d F Y H:i'));
        $sheet2->getStyle('A2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // ── Header kolom ──────────────────────────────────────────────────────
        $headers2 = [
            'A' => 'No',
            'B' => 'Nama Material',
            'C' => 'Trade Name',
            'D' => 'Gudang',
            'E' => 'No Coil',
            'F' => 'Kode Internal',
            'G' => 'Tanggal',
            'H' => 'Status',
            'I' => 'Gross Weight (Kg)',
            'J' => 'Net Weight (Kg)',
            'K' => 'Length (m)',
            'L' => 'Costbook',
            'M' => 'Total Value',
        ];

        foreach ($headers2 as $col => $label) {
            $cell = $col . '4';
            $sheet2->setCellValue($cell, $label);
            $sheet2->getStyle($cell)->getFont()->setBold(true)
                ->getColor()->setRGB('FFFFFF');
            $sheet2->getStyle($cell)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1F4E79');
            $sheet2->getStyle($cell)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle($cell)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet2->getRowDimension(4)->setRowHeight(20);

        // ── Lebar kolom ───────────────────────────────────────────────────────
        $sheet2->getColumnDimension('A')->setWidth(5);
        $sheet2->getColumnDimension('B')->setWidth(40);
        $sheet2->getColumnDimension('C')->setWidth(30);
        $sheet2->getColumnDimension('D')->setWidth(22);
        $sheet2->getColumnDimension('E')->setWidth(20);
        $sheet2->getColumnDimension('F')->setWidth(18);
        $sheet2->getColumnDimension('G')->setWidth(15);
        $sheet2->getColumnDimension('H')->setWidth(10);
        $sheet2->getColumnDimension('I')->setWidth(18);
        $sheet2->getColumnDimension('J')->setWidth(18);
        $sheet2->getColumnDimension('K')->setWidth(15);
        $sheet2->getColumnDimension('L')->setWidth(20);
        $sheet2->getColumnDimension('M')->setWidth(22);

        // ── Data rows ─────────────────────────────────────────────────────────
        $row2              = 5;
        $total_gross       = 0.0;
        $total_net         = 0.0;
        $total_length      = 0.0;
        $grand_total_value = 0.0;

        foreach ($data_coil as $no => $c) {
            $gross_weight = (float) ($c['gross_weight'] ?? 0);
            $net_weight   = (float) ($c['net_weight']   ?? 0);
            $length       = (float) ($c['length']       ?? 0);
            $costbook     = (float) ($c['harga_beli']   ?? 0);
            $total_value  = $costbook * $net_weight;
            $tgl          = !empty($c['hist_date']) ? date('d/m/Y', strtotime($c['hist_date'])) : '-';
            $status       = $c['status']   ?? '-';
            $nm_gudang    = ($c['nm_gudang'] ?? $c['kd_gudang']) ?? '-';

            $sheet2->setCellValueExplicit('A' . $row2, $no + 1,             PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('B' . $row2, $c['nm_material'],   PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('C' . $row2, $c['trade_name'],    PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('D' . $row2, $nm_gudang,          PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('E' . $row2, $c['no_coil'],       PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('F' . $row2, $c['kode_internal'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('G' . $row2, $tgl,                PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('H' . $row2, $status,             PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet2->setCellValueExplicit('I' . $row2, $gross_weight,       PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('J' . $row2, $net_weight,         PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('K' . $row2, $length,             PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('L' . $row2, $costbook,           PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet2->setCellValueExplicit('M' . $row2, $total_value,        PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet2->getStyle('A' . $row2 . ':M' . $row2)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet2->getStyle('I' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet2->getStyle('J' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet2->getStyle('K' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet2->getStyle('L' . $row2)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet2->getStyle('M' . $row2)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet2->getStyle('A' . $row2)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('G' . $row2)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('H' . $row2)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $bg = $status === 'IN' ? 'E2EFDA' : 'FCE4D6';
            $sheet2->getStyle('H' . $row2)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bg);

            if ($no % 2 === 0) {
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'I', 'J', 'K', 'L', 'M'] as $col) {
                    $sheet2->getStyle($col . $row2)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('EBF3FA');
                }
            }

            $total_gross       += $gross_weight;
            $total_net         += $net_weight;
            $total_length      += $length;
            $grand_total_value += $total_value;
            $row2++;
        }

        // ── Total row Sheet 2 ─────────────────────────────────────────────────
        $sheet2->mergeCells('A' . $row2 . ':H' . $row2);
        $sheet2->setCellValueExplicit('A' . $row2, 'TOTAL',            PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet2->setCellValueExplicit('I' . $row2, $total_gross,       PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->setCellValueExplicit('J' . $row2, $total_net,         PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->setCellValueExplicit('K' . $row2, $total_length,      PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->setCellValueExplicit('L' . $row2, '',                 PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet2->setCellValueExplicit('M' . $row2, $grand_total_value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet2->getStyle('A' . $row2 . ':M' . $row2)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row2 . ':M' . $row2)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');
        $sheet2->getStyle('A' . $row2 . ':M' . $row2)->getBorders()->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet2->getStyle('A' . $row2)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle('I' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet2->getStyle('J' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet2->getStyle('K' . $row2)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet2->getStyle('M' . $row2)->getNumberFormat()->setFormatCode('#,##0.00');

        // ── Set sheet aktif ke Sheet 1 sebelum output ─────────────────────────
        $objPHPExcel->setActiveSheetIndex(0);

        // ── Output ────────────────────────────────────────────────────────────
        $filename  = 'Stock_Value_History_' . str_replace(' ', '_', $label_gudang) . '_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    public function export_excel_stock_coil_perday()
    {
        $kd_gudang = $this->input->get('kd_gudang') ?? '';
        $date_snap = $this->input->get('date_snap') ?? '';
        $this->Warehouse_model->export_excel_coil_perday($kd_gudang, $date_snap);
    }
}
