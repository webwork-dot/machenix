<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common extends CI_Controller{
    public function __construct()    {
        parent::__construct();
        
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('shared_model');  
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
        if ($this->session->userdata('super_user_id') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }
    
    
	 public function reminder($param1 = "", $param2 = ""){
        if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        elseif ($param1 == "add_post") {
            $this->shared_model->add_reminder($param2);
        } elseif ($param1 == "edit_post") {
            $this->shared_model->edit_reminder($param2);
        } elseif ($param1 == "delete") {
            $this->shared_model->delete_reminder($param2);
            //redirect(site_url('common/reminder'), 'refresh');
        }
		else {         
		    $page_data['status'] = 'pending';
            $page_data['page_name']  = 'reminder';
            $page_data['page_title'] = get_phrase('manage_pending_reminder');
            $this->load->view('backend/common_index.php', $page_data);
        }
    }

	public function reminder_done($param1 = "", $param2 = ""){
        if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->shared_model->delete_reminder_done($param2);
            //redirect(site_url('common/reminder-done'), 'refresh');
        }
		else{          
		  $page_data['status'] = 'done';
          $page_data['page_name']  = 'reminder';
          $page_data['page_title'] = get_phrase('manage_done_reminder');
          $this->load->view('backend/common_index.php', $page_data);
		}
        
    }
     
    public function reminder_form($param1 = "", $param2 = ""){
        if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
         
        if ($param1 == 'add') {			
            $page_data['page_name']   = 'reminder_add';
            $page_data['page_title']  = get_phrase('add_reminder');
            $this->load->view('backend/common_index.php', $page_data);
        } elseif ($param1 == 'edit') {
            $data = $this->common_model->getRowById('reminder','title, description, status,reminder_date',array('id'=>$param2));
            $page_data['data']       = $data;
            $page_data['page_name']  = 'reminder_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = get_phrase('edit_reminder');
            $this->load->view('backend/common_index.php', $page_data);
        } 
    }
    
    public function get_reminder()  { 
        if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
        $this->shared_model->get_reminder();
        }
    }
	 
	public function get_ajax_reminder_list()  { 
		if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
         $this->shared_model->get_ajax_reminder_list();   
    } 
	
	public function action_reminder_done()  { 
		if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
         $this->shared_model->action_reminder_done();   
    } 

	
    
}    
    
?>    