<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Master_rate_labor extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('master_rate_labor/Master_rate_labor_model');
        $this->template->title('Master Rate Labor');
        $this->template->page_icon('fa fa-money');
    }

    /** List Direct & Indirect Labor Rates */
    public function index()
    {
        $rates = $this->Master_rate_labor_model->get_rate_labor_list();
        $this->template->set('rates', $rates);
        $this->template->render('index');
    }

    /** AJAX: Form Edit Labor Rate */
    public function edit_rate($id)
    {
        $rate = $this->Master_rate_labor_model->get_rate_labor($id);
        if (!$rate) {
            echo '<div class="alert alert-danger">Data tidak ditemukan.</div>';
            return;
        }

        $this->template->set('rate', $rate);
        $this->template->set_layout('ajax');
        $this->template->render('edit_rate');
    }

    /** AJAX POST: Save Labor Rate */
    public function save_rate()
    {
        $id   = $this->input->post('id');
        $rate = $this->input->post('rate');

        if (empty($id) || !isset($rate)) {
            echo json_encode(['status' => 0, 'pesan' => 'Parameter tidak lengkap']);
            return;
        }

        $user_id = $this->auth->nama() ?: 'admin';
        $result  = $this->Master_rate_labor_model->save_rate_labor($id, $rate, $user_id);

        if ($result) {
            echo json_encode(['status' => 1, 'pesan' => 'Tarif labor berhasil diperbarui!']);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal memperbarui tarif labor']);
        }
    }

    /** Grid List for Product Process Rates */
    public function process_product()
    {
        $products = $this->Master_rate_labor_model->get_rate_process_product_list();
        $this->template->title('Master Rate Process Product');
        $this->template->set('products', $products);
        $this->template->render('process_product');
    }

    /** Form Grid spreadsheet to edit Product Process Rates */
    public function process_product_form()
    {
        $products = $this->Master_rate_labor_model->get_rate_process_product_list();
        $this->template->title('Input Master Rate Process Product');
        $this->template->set('products', $products);
        $this->template->render('process_product_form');
    }

    /** AJAX POST: Save all product process rates */
    public function save_rate_process_product()
    {
        $products = $this->input->post('products');

        if (empty($products) || !is_array($products)) {
            echo json_encode(['status' => 0, 'pesan' => 'Tidak ada data produk untuk disimpan']);
            return;
        }

        $user_id = $this->auth->nama() ?: 'admin';
        $result  = $this->Master_rate_labor_model->save_rate_process_product($products, $user_id);

        if ($result) {
            echo json_encode(['status' => 1, 'pesan' => 'Data Standard Biaya Gaji Produk berhasil disimpan!']);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menyimpan data Standard Biaya Gaji Produk']);
        }
    }
}
