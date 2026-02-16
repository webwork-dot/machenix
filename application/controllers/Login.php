<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // Your own constructor code
        $this->load->database();
        $this->load->library('session');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }
       
     public function index() {
       	$roles = array(
			'admin', 'hr', 'inventory','admin','production_head','quality_control'
		);

		foreach ($roles as $role) {
			if ($this->session->userdata($role . '_login')) {
				redirect(site_url($role), 'refresh');
			}
		}  
         
        $page_data['page_name'] = 'login';
        $page_data['page_title'] = get_phrase('login');
        $this->load->view('backend/login', $page_data);
        
        
    }

    public function validate_login($from = "") {
        $email = $this->input->post('email');
        $password = sha1($this->input->post('password'));
        
         $query = $this->db->query("SELECT id,role_id,email,phone,access_code,type,first_name,last_name,warehouse_id FROM sys_users WHERE (email = '$email' AND email != '' || phone='$email') AND password = '$password' AND status='1'");
        //echo $this->db->last_query();
       // exit();
        if ($query->num_rows() > 0) {
            $row = $query->row();
		
		    $data_arr = array();		   
		    $data_arr = array('is_read' => 0);		   
			$this->db->where('id', $row->id);
			$this->db->update('sys_users', $data_arr);    
			
            $this->session->set_userdata('is_birthday_read', 0);
            $this->session->set_userdata('super_user_id', $row->id);
            $this->session->set_userdata('super_role_id', $row->role_id);
            $this->session->set_userdata('super_email', $row->email);
            $this->session->set_userdata('super_mobile', $row->phone);
            $this->session->set_userdata('access_code', $row->access_code);
            $this->session->set_userdata('super_type', $row->type);
            $this->session->set_userdata('super_name', $row->first_name.' '.$row->last_name);
            $this->session->set_flashdata('flash_message', get_phrase('welcome').' '.$row->first_name.' '.$row->last_name); 
                
            if ($row->role_id == 1) {
                 $this->session->set_userdata('super_role', 'admin');
                $this->session->set_userdata('admin_login', '1');
                redirect(site_url('admin/dashboard'), 'refresh');
            }	
			else if ($row->role_id == 3) {
                 $this->session->set_userdata('super_role', 'inventory');
                $this->session->set_userdata('inventory_login', '3');
                redirect(site_url('inventory/dashboard'), 'refresh');
            }
        }else {
            $this->session->set_flashdata('error_message',get_phrase('invalid_login_credentials'));
            redirect(site_url('login'), 'refresh');
        }
    }

    public function logout($from = "") {
        //destroy sessions of specific userdata. We've done this for not removing the cart session
        $this->session_destroy();
        redirect(site_url('login'), 'refresh');
    }

    public function session_destroy() {
		$this->session->unset_userdata('super_user_id');
		$this->session->unset_userdata('access_code');
		$this->session->unset_userdata('super_role_id');
		$this->session->unset_userdata('super_email');
		$this->session->unset_userdata('super_mobile');
		$this->session->unset_userdata('super_type');
		$this->session->unset_userdata('super_name');
		$this->session->unset_userdata('warehouse_id');
		$this->session->unset_userdata('warehouse');
		$this->session->unset_userdata('company_id');
		$roles = array();
		$roles = array(
			'admin', 'hr', 'inventory','admin','production_head','quality_control'
		);
		
		foreach ($roles as $role) {
			if ($this->session->userdata($role . '_login')) {
				$this->session->unset_userdata($role . '_login');
			}
		}	 	
	  $this->session->sess_destroy();
	}
}
