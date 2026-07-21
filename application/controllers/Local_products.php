<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Local_products extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('local_products_model');
        $this->load->model('category_model');
        $this->load->model('file_model');
    }

    public function index($param1 = "", $param2 = "", $param3 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->local_products_model->add_raw_products();
        } elseif ($param1 == "edit_post") {
            $this->local_products_model->edit_raw_products($param2);
        } elseif ($param1 == "delete") {
            $this->local_products_model->delete_raw_products($param2);
        } elseif ($param1 == "delete_variation") {
            $this->local_products_model->delete_raw_products_variation($param2, $param3);
        } elseif ($param1 == "delete_variation_sku") {
            $this->local_products_model->delete_raw_products_variation_sku($param2, $param3);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'local_products';
            $page_data['page_title'] = get_phrase('local_products');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function add()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $company_id = $this->session->userdata('company_id');

        $categories = $this->category_model->getCategories();
        $category_tree = $this->category_model->buildTree($categories);
        $page_data['category_tree'] = $category_tree;

        $page_data['units_list']     = $this->common_model->select('units');
        $page_data['product_units']  = $this->common_model->getResultById('product_unit', 'id, name', ['is_delete' => '0']);
        $page_data['suppliers']     = $this->common_model->getResultById('supplier', 'id, name', ['company_id' => $company_id]);
        $page_data['commissions']    = $this->common_model->getResultById('product_commission_slab', 'id, name, commission', ['is_deleted' => '0']);

        $page_data['modesy_images'] = $this->file_model->get_sess_product_images_array();
        $page_data['page_name']  = 'local_products_add';
        $page_data['page_title'] = 'Add Local Products';
        $this->load->view('backend/index', $page_data);
    }

    public function edit($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $company_id = $this->session->userdata('company_id');

        $categories = $this->category_model->getCategories();
        $category_tree = $this->category_model->buildTree($categories);
        $page_data['category_tree'] = $category_tree;

        $page_data['units_list']     = $this->common_model->select('units');
        $page_data['product_units']  = $this->common_model->getResultById('product_unit', 'id, name', ['is_delete' => '0']);
        $page_data['suppliers']     = $this->common_model->getResultById('supplier', 'id, name', ['company_id' => $company_id]);
        $page_data['commissions']    = $this->common_model->getResultById('product_commission_slab', 'id, name, commission', ['is_deleted' => '0']);

        $data                    = $this->local_products_model->get_raw_products_by_id($id)->row_array();
        $sku_products            = $this->common_model->getResultById('product_sku', 'id, product_id, sku_code', ['product_id' => $id]);
        $variations              = $this->common_model->getResultById('product_variation', '*', ['product_id' => $id]);
        $page_data['modesy_images'] = $this->file_model->get_product_images_uncached($id);

        $page_data['data']       = $data;
        $page_data['skus']       = $sku_products;
        $page_data['variations'] = ($variations != '') ? $variations : [];
        $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id'] ?? 0);
        $page_data['page_name']  = 'local_products_edit';
        $page_data['id']         = $id;
        $page_data['page_title'] = 'Edit Local Products';
        $this->load->view('backend/index', $page_data);
    }

    public function get_local_products()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->local_products_model->get_raw_products();
        }
    }

    public function local_products_delete_sku()
    {
        $this->local_products_model->raw_products_delete_sku();
    }

    public function local_products_delete_variation()
    {
        $this->local_products_model->raw_products_delete_variation();
    }
}
