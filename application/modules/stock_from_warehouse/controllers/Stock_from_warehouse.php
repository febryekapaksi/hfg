<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stock_from_warehouse extends Admin_Controller
{
    protected $viewPermission   = 'Stock_From_Warehouse.View';
    protected $managePermission = 'Stock_From_Warehouse.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('stock_from_warehouse/Stock_from_warehouse_model');
        $this->template->title('Stock From Warehouse');
        $this->template->page_icon('fa fa-dolly');

        date_default_timezone_set('Asia/Bangkok');
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->render('index');
    }

    // ---------------------------------------------------------------
    // DATATABLES SERVER-SIDE — Transit
    // ---------------------------------------------------------------

    public function data_side_transit()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Stock_from_warehouse_model->get_json_transit();
    }

    // ---------------------------------------------------------------
    // DATATABLES SERVER-SIDE — WIP
    // ---------------------------------------------------------------

    public function data_side_wip()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Stock_from_warehouse_model->get_json_wip();
    }

    // ---------------------------------------------------------------
    // DATATABLES SERVER-SIDE — On Hold
    // ---------------------------------------------------------------

    public function data_side_on_hold()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Stock_from_warehouse_model->get_json_on_hold();
    }

    // ---------------------------------------------------------------
    // DATATABLES SERVER-SIDE — History Per Days
    // ---------------------------------------------------------------

    public function data_side_history()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Stock_from_warehouse_model->get_json_history();
    }

    // ---------------------------------------------------------------
    // EXPORT EXCEL — Transit
    // ---------------------------------------------------------------

    public function export_excel_transit()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Stock_from_warehouse_model->export_stock('PRT', 'WRH Production 2');
    }

    // ---------------------------------------------------------------
    // EXPORT EXCEL — WIP
    // ---------------------------------------------------------------

    public function export_excel_wip()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Stock_from_warehouse_model->export_stock('WIP', 'WIP (Coil Remains)');
    }

    // ---------------------------------------------------------------
    // EXPORT EXCEL — On Hold
    // ---------------------------------------------------------------

    public function export_excel_on_hold()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Stock_from_warehouse_model->export_stock('HLD', 'On Hold', 5);
    }

    // ---------------------------------------------------------------
    // EXPORT EXCEL — History Per Days
    // ---------------------------------------------------------------

    public function export_excel_history()
    {
        $this->auth->restrict($this->viewPermission);

        $date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
        $kd_gudang   = isset($_GET['kd_gudang']) ? $_GET['kd_gudang'] : '';

        $this->Stock_from_warehouse_model->get_history_for_export($date_filter, $kd_gudang);
    }
}
