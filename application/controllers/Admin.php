<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller{
    public function __construct()    {
        parent::__construct();
        
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('admin_model');  
    }
    
    function paginate($url, $total_rows)
    {
        //initialize pagination
        $page     = $this->security->xss_clean($this->input->get('page'));
        $per_page = $this->input->get('show', true);
        if (empty($page)) {
            $page = 0;
        }
        
        if ($page != 0) {
            $page = $page - 1;
        }
        
        if (empty($per_page)) {
            $per_page = 20;
        }
        $config['num_links']          = 4;
        $config['base_url']           = $url;
        $config['total_rows']         = $total_rows;
        $config['per_page']           = $per_page;
        $config['reuse_query_string'] = true;
        $this->pagination->initialize($config);
        
        return array(
            'per_page' => $per_page,
            'offset' => $page * $per_page
        );
    }
    
    public function index()
    {
        if ($this->session->userdata('admin_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }
    
    public function dashboard(){
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('dashboard');
        $this->load->view('backend/index.php', $page_data);
    }
    
    public function staff($param1 = "", $param2 = "") {
        // if ($this->session->userdata('admin_login') != true) {
        //     redirect(site_url('login'), 'refresh');
        // }
        
        if ($param1 == "add_post") {
            $this->crud_model->add_staff($param2);
        } elseif ($param1 == "edit_post") {
            $this->crud_model->edit_staff($param2);
        } elseif ($param1 == "delete") {
            $this->crud_model->delete_staff($param2);
        }elseif ($param1 == "change_password") {
            $id = $param1;
            $this->crud_model->user_change_password($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());            
            $page_data['page_name']  = 'staff';
            $page_data['page_title'] = 'Manage Staff';
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function staff_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
         
        if ($param1 == 'staff_add') {
            $page_data['states']     = $this->crud_model->get_states();
            $page_data['user_types'] = $this->crud_model->get_user_type();
            $page_data['page_name']  = 'staff_add';
            $page_data['page_title'] = 'Add Staff';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'staff_edit') {
            $data                    = $this->crud_model->get_staff_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['states']     = $this->crud_model->get_states();
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['user_types'] = $this->crud_model->get_user_type();            
            $page_data['page_name']  = 'staff_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Staff';
            $this->load->view('backend/index', $page_data);
        } 
        elseif ($param1 == 'staff_change_password') {
            $page_data['page_name']  = 'change_password';
            $page_data['id']         = $param2;
            $page_data['page_title'] = get_phrase('change_password_login');
            $this->load->view('backend/index', $page_data);
        }
    }
   
    public function get_staff() {  
		if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
			//$this->load->model('CacheModel');
           // $this->CacheModel->get_staff();
            $this->crud_model->get_staff();
        } 
    }
    

    public function get_cities() {
        $state_id = $this->input->post('state_id', true);
        $states   = $this->crud_model->get_city_by_state($state_id);
        foreach ($states as $item) {
            echo '<option value="' . $item['id'] . '">' . $item['name'] . '</option>';
        }
    }
    
    public function get_area() {
        $area_id = $this->input->post('area_id', true);
        $areas   = $this->crud_model->get_area_by_city($area_id);
        
        foreach ($areas as $item) {
            echo '<option value="' . $item['id'] . '">' . $item['name'] . '</option>';
        }
    }
    
	
    
}