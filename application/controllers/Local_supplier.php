<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Local_supplier extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('local_supplier_model');
    }

    public function index($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->local_supplier_model->add_supplier();
        } elseif ($param1 == "edit_post") {
            $this->local_supplier_model->edit_supplier($param2);
        } elseif ($param1 == "delete") {
            $this->local_supplier_model->delete_supplier($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'local_supplier';
            $page_data['page_title'] = get_phrase('local_supplier');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function add()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['countries']     = $this->crud_model->get_countries();
        $page_data['states']     = array(); // Start with empty states for add mode
        $page_data['page_name']  = 'local_supplier_add';
        $page_data['page_title'] = 'Add Local Supplier';
        $this->load->view('backend/index', $page_data);
    }

    public function edit($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['countries']     = $this->crud_model->get_countries();
        $data                    = $this->local_supplier_model->get_supplier_by_id($id)->row_array();
        $page_data['data']       = $data;
        $page_data['states']     = $this->crud_model->get_states_by_country($data['country_id'] ?? 0);
        $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id'] ?? 0);
        $page_data['page_name']  = 'local_supplier_edit';
        $page_data['id']         = $id;
        $page_data['page_title'] = 'Edit Local Supplier';
        $this->load->view('backend/index', $page_data);
    }

    public function ledger($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $data                    = $this->local_supplier_model->get_supplier_by_id($id)->row_array();
        $page_data['data']       = $data;
        $page_data['id']         = $id;
        $page_data['outstanding'] = $this->local_supplier_model->get_supplier_outstanding($id);
        $page_data['payments'] = $this->local_supplier_model->get_supplier_payments($id);
        $page_data['page_name']  = 'local_supplier_ledger';
        $page_data['page_title'] = 'Local Supplier Ledger';
        $this->load->view('backend/index', $page_data);
    }

    public function get_local_supplier()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->local_supplier_model->get_supplier();
        }
    }
}
