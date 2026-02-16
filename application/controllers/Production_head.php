<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Production_head extends CI_Controller{
    public function __construct()    {
        parent::__construct();
        
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('production_model');  
    }
    
    function paginate($url, $total_rows) {
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
    
    public function index() {
        if ($this->session->userdata('production_head_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }
    
    public function dashboard(){
        if ($this->session->userdata('production_head_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
		$page_data['total_products']  = $this->common_model->getCountsById('products',array('is_deleted'=>0));
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('dashboard');
        $this->load->view('backend/index.php', $page_data);
    }
    
    public function products($param1 = "", $param2 = "") {
        if ($this->session->userdata('production_head_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        elseif ($param1 == "add_post") {
            $this->production_model->add_products($param2);
        } elseif ($param1 == "edit_post") {
            $this->production_model->edit_products($param2);
        } elseif ($param1 == "delete") {
            $this->production_model->delete_products($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());            
            $page_data['page_name']  = 'products';
            $page_data['page_title'] = get_phrase('products');
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function products_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('production_head_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $page_data['units_list']    	  = $this->common_model->select('units');
        $page_data['department_list']     = $this->common_model->select('department');
        $page_data['raw_list']    		  = $this->common_model->getResultById('raw_products','id,name',array('is_deleted'=>0));
          
        if ($param1 == 'add') {
            $page_data['page_name']  = 'products_add';
            $page_data['page_title'] = 'Add Products';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') { 
			$data   				 = $this->production_model->get_products_details_by_id($param2);
            $page_data['data']       = $data;
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['page_name']  = 'products_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Products';
            $this->load->view('backend/index', $page_data);
        } 
    }
	 
	public function get_products() {  
		if ($this->session->userdata('production_head_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->production_model->get_products();
        } 
    }
    
     public function get_raw_product_details_by_id(){
        if ($this->input->is_ajax_request()) {
			$id = $this->input->post('id', true);        
			$product_types = $this->production_model->get_raw_product_details_by_id($id);
		}
    }
    
}    
    
?>    