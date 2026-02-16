<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidate_front extends CI_Controller
{
    public function __construct()
    {
        parent::__construct(); 
        
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('Candidate_front_model');
    }
    
    public function check_candidate_document_link($unique_id) {  
       $orders = $this->Candidate_front_model->get_candidate_unique_by_id($unique_id);
       if($orders->num_rows()>0){
        $id = $orders->row()->id;
        $enc_id= $this->auth_model->encrypt_decrypt('encrypt',$id); 
        redirect(site_url('candidate/documentation/'.$enc_id));
       }
       else{
         $this->session->set_flashdata('error_message', get_phrase('order_details_not_found!'));
         redirect(site_url('patient/not-found'));
      }
    }
    
     public function documentation($enc_id) {  
        $id= $this->auth_model->encrypt_decrypt('decrypt',$enc_id); 
        $orders = $this->Candidate_front_model->get_candidate_details_by_id($id);
        $page_data['states']    = $this->crud_model->get_states();
    	$page_data['banks']     = $this->common_model->getResultById('emp_bank','id,name',array('status'=>1));
        if(!empty($orders)){
            $page_data['enc_id'] = $enc_id;
            $page_data['order_id'] = $id;
            $page_data['data'] = $orders;
            $page_data['details'] = $this->Candidate_front_model->get_candidate_document_by_id($id);
            $page_data['page_name'] = "documentation";
            $page_data['page_title'] = "Documentation | Rajasthan Aushadhalaya";
            $page_data['meta_description'] = "Rajasthan Aushadhalaya | Manufacture of Ayurvedic medicine in Mumbai.";
            $page_data['meta_keyword'] = "Rajasthan Aushadhalaya | Manufacture of Ayurvedic medicine in Mumbai.";
            $this->load->view('frontend/default/candidate_index', $page_data);
        }
        else{
            $this->session->set_flashdata('error_message', get_phrase('order_details_not_found!'));
            redirect(site_url('patient/not-found'));
        }
    }  
    
    public function thank_you() { 
		$page_data['page_name'] = "thank_you_candidate";
		$page_data['page_title'] = "Thank You | Rajasthan Aushadhalaya";
		$page_data['meta_description'] = "Rajasthan Aushadhalaya | Manufacture of Ayurvedic medicine in Mumbai.";
		$page_data['meta_keyword'] = "Rajasthan Aushadhalaya | Manufacture of Ayurvedic medicine in Mumbai.";
		$this->load->view('frontend/default/index', $page_data);  
	}  
	
	
    public function add_documentation($param1) {
        $this->Candidate_front_model->add_documentation($param1);
    }
    
     public function exit_form($enc_id) {  
        $id= $this->auth_model->encrypt_decrypt('decrypt',$enc_id); 
        $orders = $this->Candidate_front_model->get_candidate_details_by_id($id);
        $page_data['states']    = $this->crud_model->get_states();
    	$page_data['banks']     = $this->common_model->getResultById('emp_bank','id,name',array('status'=>1));
        if(!empty($orders)){
            $page_data['enc_id'] = $enc_id;
            $page_data['order_id'] = $id;
            $page_data['data'] = $orders;
            $page_data['details'] = $this->Candidate_front_model->get_candidate_document_by_id($id);
            $page_data['page_name'] = "exit_form";
            $page_data['page_title'] = "Exit Form | Rajasthan Aushadhalaya";
            $page_data['meta_description'] = "Rajasthan Aushadhalaya | Manufacture of Ayurvedic medicine in Mumbai.";
            $page_data['meta_keyword'] = "Rajasthan Aushadhalaya | Manufacture of Ayurvedic medicine in Mumbai.";
            $this->load->view('frontend/default/candidate_index', $page_data);
        }
        else{
            $this->session->set_flashdata('error_message', get_phrase('order_details_not_found!'));
            redirect(site_url('patient/not-found'));
        }
    }  
     	
    public function add_exit_form($param1) {
        $this->Candidate_front_model->add_exit_form($param1);
    }
    
}