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
}
