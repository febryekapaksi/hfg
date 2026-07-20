<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_payment_po extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Purchase_Order.View';
    protected $addPermission    = 'Purchase_Order.Add';
    protected $managePermission = 'Purchase_Order.Manage';
    protected $deletePermission = 'Purchase_Order.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Report_payment_po_model');
        $this->template->title('Report Payment PO');
        $this->template->page_icon('fa fa-file-excel-o');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        
        $no_po = $this->input->post('no_po'); // if filtered
        $data = $this->Report_payment_po_model->get_report_data($no_po);

        $grouped_data = [];
        foreach ($data as $item) {
            $grouped_data[$item['no_po']][] = $item;
        }

        $this->template->set('results', $grouped_data);
        $this->template->title('Report Payment PO');
        $this->template->render('report_payment_po');
    }
    
    public function debug_cols() {
        echo "<pre>";
        $q = $this->db->query("SHOW COLUMNS FROM payment_approve_details");
        print_r($q->result());
        echo "</pre>";
        exit;
    }

    public function download_excel()
    {
        $no_po = $this->input->post('no_po');
        
        $data = $this->Report_payment_po_model->get_report_data($no_po);

        require_once APPPATH . 'libraries/PHPExcel.php';
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Report Payment PO');

        // Styles
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9D9D9']
            ]
        ];
        
        $dataStyle = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
            ],
        ];

        // Headers
        // Row 1 (Merged Headers)
        $sheet->setCellValue('A1', 'PO');
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('F1', 'ROS/Incoming');
        $sheet->mergeCells('F1:H1');
        $sheet->setCellValue('I1', 'Receive Invoice');
        $sheet->mergeCells('I1:L1');
        $sheet->setCellValue('M1', 'Payment');
        $sheet->mergeCells('M1:R1');

        // Row 2 (Sub Headers)
        $sheet->setCellValue('A2', 'PO');
        $sheet->setCellValue('B2', 'Tipe PO');
        $sheet->setCellValue('C2', 'Value PO');
        $sheet->setCellValue('D2', 'Tipe TOP');
        $sheet->setCellValue('E2', 'Value %');
        $sheet->setCellValue('F2', 'Total Material');
        $sheet->setCellValue('G2', 'Unbill');
        $sheet->setCellValue('H2', 'Selisih Kurs');
        $sheet->setCellValue('I2', 'Receive Invoice');
        $sheet->setCellValue('J2', 'Kurs');
        $sheet->setCellValue('K2', 'Value Receive');
        $sheet->setCellValue('L2', 'Selisih Kurs');
        $sheet->setCellValue('M2', 'Invoice Pay');
        $sheet->setCellValue('N2', 'Kurs Pay');
        $sheet->setCellValue('O2', 'Currency Pay');
        $sheet->setCellValue('P2', 'Payment IDR');
        $sheet->setCellValue('Q2', 'Admin');
        $sheet->setCellValue('R2', 'Selisih Kurs'); // R is unmerged above in concept

        $sheet->getStyle('A1:R2')->applyFromArray($headerStyle);

        $row = 3;
        
        $grouped_data = [];
        foreach ($data as $item) {
            $grouped_data[$item['no_po']][] = $item;
        }
        
        foreach ($grouped_data as $po_no => $items) {
            $is_first = true;
            foreach ($items as $item) {
                if ($item['category'] == 'dp') {
                    $category_label = 'Uang Muka';
                } elseif ($item['category'] == 'import') {
                    $category_label = 'Pelunasan (After ROS)';
                } elseif ($item['category'] == 'local') {
                    $category_label = 'Pelunasan (After Incoming)';
                } else {
                    $category_label = ucfirst($item['category']);
                }
                
                $po_display = $is_first ? $po_no : '';
                
                $sheet->setCellValue('A' . $row, $po_display);
                $sheet->setCellValue('B' . $row, $category_label);
                $sheet->setCellValue('C' . $row, $item['value_po']);
                $sheet->setCellValue('D' . $row, $item['tipe_top']);
                $sheet->setCellValue('E' . $row, $item['value_pct']);
                $sheet->setCellValue('F' . $row, $item['total_material']);
                $sheet->setCellValue('G' . $row, $item['unbill']);
                $sheet->setCellValue('H' . $row, $item['selisih_kurs_receive_1']);
                $sheet->setCellValue('I' . $row, $item['receive_invoice_value']);
                $sheet->setCellValue('J' . $row, $item['receive_kurs']);
                $sheet->setCellValue('K' . $row, $item['value_receive_idr']);
                $sheet->setCellValue('L' . $row, $item['selisih_kurs_receive_2']);
                $sheet->setCellValue('M' . $row, $item['invoice_pay']);
                $sheet->setCellValue('N' . $row, $item['kurs_pay']);
                $sheet->setCellValue('O' . $row, $item['currency_pay']);
                $sheet->setCellValue('P' . $row, $item['payment_idr']);
                $sheet->setCellValue('Q' . $row, $item['admin_bank']);
                $sheet->setCellValue('R' . $row, $item['selisih_kurs_admin']);
                
                $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray($dataStyle);
                
                $is_first = false;
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_clean();
        }
        
        $filename = "Report_Payment_PO_" . (!empty($no_po) ? str_replace('/', '_', $no_po) : "All") . "_" . date('Ymd') . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
