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

        $this->load->model('inventory_model');

        $data = $this->local_supplier_model->get_supplier_by_id($id)->row_array();
        $fy   = $this->inventory_model->get_indian_fy_range();

        $from = $fy['from'];
        $to   = $fy['to'];
        $date_range = trim((string) $this->input->get('date_range', true));
        if ($date_range !== '' && strpos($date_range, ' - ') !== false) {
            $parts = explode(' - ', $date_range);
            $from_dt = DateTime::createFromFormat('d-m-Y', trim($parts[0]));
            $to_dt   = DateTime::createFromFormat('d-m-Y', trim($parts[1]));
            if ($from_dt && $to_dt) {
                $from = $from_dt->format('Y-m-d');
                $to   = $to_dt->format('Y-m-d');
            }
        }

        $ledger = $this->inventory_model->build_local_supplier_ledger($id, $from, $to);

        $page_data['data']       = $data;
        $page_data['id']         = $id;
        $page_data['ledger']     = $ledger;
        $page_data['from_date']  = $from;
        $page_data['to_date']    = $to;
        $page_data['date_range'] = date('d-m-Y', strtotime($from)) . ' - ' . date('d-m-Y', strtotime($to));
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
