<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hr extends CI_Controller{
	
    public function __construct(){
        parent::__construct(); 
        
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('hr_model');
    }
    
    function paginate($url, $total_rows){
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
    
    public function index(){
        if ($this->session->userdata('hr_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }
    
    public function dashboard(){
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }    
		$filter_data=array();		
		$page_data['today_calls'] = $this->hr_model->count_today_calls();
        $page_data['today_followup'] = $this->hr_model->count_today_followup();
        $page_data['total_candidate'] =  $this->hr_model->get_paginated_candidate_count($filter_data);
        $page_data['total_shortlist'] =  $this->hr_model->get_paginated_shortlist_count($filter_data);
        $page_data['total_interview_schedule'] =  $this->hr_model->get_paginated_interview_schedule_count($filter_data);
		$page_data['total_pending_doc']  = $this->hr_model->get_pending_doc_count();
		$page_data['total_not_verified_doc'] = $this->hr_model->get_verified_doc_count();
        
		
        /*$page_data['total_field_staff']  = $this->common_model->getCountsById('candidate',array('is_kyc'=>'1','salary_type'=>'FIELD STAFF'));
        $page_data['total_office_staff']  = $this->common_model->getCountsById('candidate',array('is_kyc'=>'1','salary_type'=>'OFFICE STAFF'));
        $page_data['total_raj_godown']  = $this->common_model->getCountsById('candidate',array('is_kyc'=>'1','salary_type'=>'RAJASTHAN-GODOWN'));
        $page_data['total_lonavala']  = $this->common_model->getCountsById('candidate',array('is_kyc'=>'1','salary_type'=>'LONAVALA'));
        $page_data['total_chavsar']  = $this->common_model->getCountsById('candidate',array('is_kyc'=>'1','salary_type'=>'CHAVSAR'));
        $page_data['total_wada']  = $this->common_model->getCountsById('candidate',array('is_kyc'=>'1','salary_type'=>'WADA'));
		*/
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('dashboard');
        $this->load->view('backend/index.php', $page_data);
    }
    
	
	
   /******** Calls Start *********/
     public function get_staff_type()    {
        $category_id = $this->input->post('category_id', true);
        $states   = $this->hr_model->get_staff_type($category_id);
        foreach ($states as $item) {
            echo '<option value="' . $item['id'] . '">' . $item['name'] . '</option>';
        }
    }
	
    public function old_calls($param1 = "", $param2 = "")    {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $page_data['page_name']  = 'old_calls';
        $page_data['page_title'] = get_phrase('calls');
        $this->load->view('backend/index', $page_data);
    }
    
    public function calls($param1 = "", $param2 = "" ){
    	if ($this->session->userdata('hr_login') != true) {
    		redirect(site_url('login'), 'refresh');
    	}
    	
    	elseif ($param1 == "add_new_calls") {
    		$this->hr_model->add_new_calls($param2);
    	}
    	elseif ($param1 == "add_old_calls") {
    		$this->hr_model->add_old_calls($param2);
    	}
    	elseif ($param1 == "update_calls") {
    		$this->hr_model->update_calls($param2);    	
    	}
    	elseif ($param1 == "edit_post") {
    		$this->hr_model->edit_calls($param2);
    		redirect($this->agent->referrer()); 
    	}
    	else{
    	
    	$filter_data['type']      = $this->input->get('type');
    	$filter_data['c_date']    = $this->input->get('c_date');
    	$filter_data['keywords']  = $this->input->get('keywords');
    	$total_count              = $this->hr_model->get_paginated_calls_count($filter_data);
    	$page_data['total_count'] = $total_count;
    	$pagination               = $this->paginate(hr_url() . 'calls', $total_count);
    	$page_data['orders']      = $this->hr_model->get_paginated_calls($filter_data, $pagination['per_page'], $pagination['offset']);
    	
    	$page_data['page_name']  = 'calls';
    	$page_data['page_title'] = get_phrase('calls_list');
    	$this->load->view('backend/index', $page_data);
    	}
    }

    public function calls_form($param1 = "", $param2 = "" ){
    	if ($this->session->userdata('hr_login') != true) {
    		redirect(site_url('login'), 'refresh');
    	}

         $page_data['staff_category']     = $this->hr_model->get_staff_category();
    	if ($param1 == 'calls_add') {
            $page_data['states']     = $this->crud_model->get_states();;
    		$page_data['page_name']  = 'calls_add';
    		$page_data['page_title'] = 'Add Calls';
    		$this->load->view('backend/index', $page_data);
    	}
    	
    	elseif ($param1 == 'calls_edit') {
    		$page_data['page_name']  = 'calls_edit';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Edit Calls';
    		$this->load->view('backend/index', $page_data);
    	}	
    }
    
    public function ajax_candidate_list(){
       if ($this->input->is_ajax_request()) {
         $this->hr_model->ajax_candidate_list();
      } 
    } 

    public function ajax_pure_candidate_list(){
       if ($this->input->is_ajax_request()) {
         $this->hr_model->ajax_pure_candidate_list();
	   }
    } 
    
    public function get_ajax_candidate_details()
    {
        $doctor_id = addslashes($this->input->post('candidate_id'));
        $this->hr_model->get_ajax_candidate_id($doctor_id);
    }
    
    public function today_followup($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $filter_data['user_type'] = 'HR';
        $filter_data['type']      = $this->input->get('type');
    	$total_count              = $this->hr_model->get_paginated_today_followup_count($filter_data);
    	$page_data['total_count'] = $total_count;
    	$pagination               = $this->paginate(hr_url() . 'today-followup', $total_count);
    	$page_data['orders']      = $this->hr_model->get_paginated_today_followup($filter_data, $pagination['per_page'], $pagination['offset']);
        $page_data['page_name']   = 'today_followup';
        $page_data['page_title']  = "Today's Followup List";
        $this->load->view('backend/index', $page_data);
        
    }
    
    public function other_followup($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $filter_data['user_type'] = 'HR';
        $filter_data['type']      = $this->input->get('type');
        $filter_data['keywords']  = $this->input->get('keywords');
    	$total_count              = $this->hr_model->get_paginated_other_followup_count($filter_data);
    	$page_data['total_count'] = $total_count;
    	$pagination               = $this->paginate(hr_url() . 'other-followup', $total_count);
    	$page_data['orders']      = $this->hr_model->get_paginated_other_followup($filter_data, $pagination['per_page'], $pagination['offset']);
    	
        $page_data['page_name']  = 'other_followup';
        $page_data['page_title'] = get_phrase('other_followup_list');
        $this->load->view('backend/index', $page_data);
    }
        
    public function get_timeline_form(){
        $candidate_id = $this->input->post('candidate_id', true);
        $type = $this->input->post('type', true);
        $coordinator    = $this->hr_model->get_timeline($candidate_id);
        //print_r($coordinator);die;
         //echo json_encode($coordinator);
        $i = 1;
		if($type=='calls'){
          $output= '<div class="col-12"><div class="card mb-0"><div class="card-body py-1 my-0 mb-0"><h4 class="mb-0">Timeline</h4></div></div></div>';
		}
		
        if (count($coordinator) > 0) {
            foreach ($coordinator as $item) {
			$output .= 
            '<div class="col-12">
				<div class="card" style="margin-bottom: 0rem;">
					<div class="card-body py-1 my-0">';
				     
				   if($item['action'] == 'timeline'){ 
				     $output .=   '<div class="offcanvas-body mx-0 flex-grow-0">
							<li class="timeline-item">
								  <span class="timeline-point timeline-point-secondary">
							    	<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
								  </span>
								  <div class="timeline-event">
									<div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
									  <h6>'. $item['added_date'] .'</h6>
									</div>
									<span
									  class="follow-up-btn collapsed"
									  type="button"
									  data-bs-toggle="collapse"
									  data-bs-target="#collapse-timeline-'. $item['id'] .'"
									  aria-expanded="false"		
									  aria-controls="collapse-timeline-'. $item['id'] .'"
									>
									 <b>Timeline :</b> '. $item['name'] .'
									</span>
									<div class="collapse" id="collapse-timeline-'. $item['id'] .'">
									<div class="p-1 border">
										
									<div class="col-md-12">
										<p style="margin-bottom:0"><b>Remark</b></p>
										<small style="margin-bottom:0">'. $item['remark'] .'</small>
									</div>
									</div>
								   </div>
								  </div>
							   </li>
						</div>';
				     } else{					 
					 
					$output .=	'<div class="offcanvas-body mx-0 flex-grow-0">
							<li class="timeline-item">
								  <span class="timeline-point timeline-point-secondary">
								  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-phone-call"><path d="M15.05 5A5 5 0 0 1 19 8.95M15.05 1A9 9 0 0 1 23 8.94m-1 7.98v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
								  </span>
								  <div class="timeline-event">
									<div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
									  <h6>'. $item['added_date'] .'</h6>
									</div>
									<span
									  class="follow-up-btn collapsed"
									  type="button"
									  data-bs-toggle="collapse"
									  data-bs-target="#collapse-timeline-'. $item['id'] .'"
									  aria-expanded="false"		
									  aria-controls="collapse-timeline-'. $item['id'] .'"
									>
									  <b>Called By :</b> '. $item['name'] .'
									</span>
									<div class="collapse" id="collapse-timeline-'. $item['id'] .'">
									<div class="p-1 border">';
									if($item['action'] == 'add'){	
									$output .=	'<div class="col-md-12">
        											<p style="margin-bottom:0"><b>Remark</b></p>
        											<p style="margin-bottom:0">'. $item['remark'] .'</p>
        										</div><div class="col-md-12">
											<p style="margin-bottom:0"><b>Followup Date / Time</b></p>
											<p style="margin-bottom:0">'. $item['follow_up_date'] .' / '. $item['follow_up_time'].'</p>
										</div>';
									}elseif($item['action'] == 'reject' || $item['action'] == 'selected'){	
									$output .=	'<div class="col-md-12">
            										<p style="margin-bottom:0"><b>Remark</b></p>
            										<p style="margin-bottom:0">'. $item['remark'] .'</p>
            									</div>';
									}elseif($item['action'] == 'schedule' || $item['action'] == 're-schedule'){	
									$output .=	'<div class="col-md-12">
            										<p style="margin-bottom:0"><b>Remark</b></p>
            										<p style="margin-bottom:0">'. $item['remark'] .'</p>
            									</div><div class="col-md-12">
											<p style="margin-bottom:0"><b>Interview Date / Time</b></p>
											<p style="margin-bottom:0">'. $item['follow_up_date'] .' / '. $item['follow_up_time'].'</p>
										</div>';
									}else{	
									   $output .='<div class="col-md-12">
            									<p style="margin-bottom:0"><b>Remark</b></p>
            										<p style="margin-bottom:0">'. $item['remark'] .'</p>
            									</div><div class="col-md-12">
											<p style="margin-bottom:0"><b>Followup Date / Time</b></p>
											<p style="margin-bottom:0">'. $item['follow_up_date'] .' / '. $item['follow_up_time'].'</p>
										</div>';
									}	
									$output .='</div>
								   </div>
								  </div>
							   </li>
							   </div>'; 
							   }
				     
					$output .=	'</div>
					</div>        
				</div>';  
                $i++;
            
            }
		echo $output;
        } else {
            echo 'No Timeline Found!';
        }
        
    }
    

    public function candidate($param1 = "", $param2 = "" ){
    	if ($this->session->userdata('hr_login') != true) {
    		redirect(site_url('login'), 'refresh');
    	} elseif ($param1 == "edit_candidate") {
    		$this->hr_model->edit_candidate($param2);
    	} elseif ($param1 == "shortlist") {
    		$this->hr_model->schedule_shortlist($param2);
    		redirect($this->agent->referrer());
    	} else{
        	$filter_data['staff_type']= $this->input->get('staff_type');
        	$filter_data['keywords']  = $this->input->get('keywords');
        	$total_count              = $this->hr_model->get_paginated_candidate_count($filter_data);
        	$page_data['total_count'] = $total_count;
        	$pagination               = $this->paginate(hr_url() . 'candidate', $total_count);
        	$page_data['orders']      = $this->hr_model->get_paginated_candidate($filter_data, $pagination['per_page'], $pagination['offset']);
        	
        	$page_data['page_name']  = 'candidate';
        	$page_data['page_title'] = get_phrase('candidate_list');
        	$this->load->view('backend/index', $page_data);
    	}
    }
    
    public function candidate_form($param1 = "", $param2 = "" ) {
    	if ($this->session->userdata('hr_login') != true) {
    		redirect(site_url('login'), 'refresh');
    	}
    	elseif ($param1 == 'candidate_edit') { 
			$data  = $this->hr_model->get_candidate_by_id($param2)->row_array();
			$page_data['data']   		   = $data;
			$page_data['staff_category']   = $this->hr_model->get_staff_category();
            $page_data['staff_types']      = $this->hr_model->get_staff_type($data['staff_catid']);
    	    $page_data['states']     = $this->crud_model->get_states();           
    		$page_data['page_name']  = 'candidate_edit';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Edit Candidate';
    		$this->load->view('backend/index', $page_data);
    	}	
    	elseif ($param1 == 'candidate_document') {
            $page_data['data']       = $this->hr_model->get_candidate_by_id($param2)->row_array();
            $page_data['details']    = $this->hr_model->get_candidate_document_by_id($param2)->row_array();
    		$page_data['page_name']  = 'candidate_document';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Candidate Documents';
    		$this->load->view('backend/index', $page_data);
    	}  
		elseif ($param1 == 'update_staff') {
			
    	    $page_data['banks']     = $this->common_model->getResultById('emp_bank','id,name',array('status'=>1));
			
            $page_data['data']  	 = $this->hr_model->get_candidate_by_id($param2)->row_array();
    		$page_data['page_name']  = 'update_staff';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Update Staff';
    		$this->load->view('backend/index', $page_data);
    	}	
    }
	
    public function shortlist($param1 = "", $param2 = ""){
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }elseif ($param1 == "schedule_interview") {
    		$this->hr_model->schedule_interview($param2);
    		redirect($this->agent->referrer()); 
    	}elseif ($param1 == "reject_interview") {
    		$this->hr_model->reject_interview($param2);
    		redirect($this->agent->referrer()); 
    	}else{
    	    $filter_data['staff_type']      = $this->input->get('staff_type');
        	$filter_data['keywords']  = $this->input->get('keywords');
        	$total_count              = $this->hr_model->get_paginated_shortlist_count($filter_data);
        	$page_data['total_count'] = $total_count;
        	$pagination               = $this->paginate(hr_url() . 'shortlist', $total_count);
        	$page_data['orders']      = $this->hr_model->get_paginated_shortlist($filter_data, $pagination['per_page'], $pagination['offset']);
        	
            $page_data['page_name']  = 'shortlist';
            $page_data['page_title'] = get_phrase('shortlist_candidate_list');
            $this->load->view('backend/index', $page_data);
    	}      
    	
    }
    
    public function interview_schedule($param1 = "", $param2 = "")    {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }elseif ($param1 == "re_schedule_interview") {
    		$this->hr_model->re_schedule_interview($param2);
    		redirect($this->agent->referrer()); 
    	}elseif ($param1 == "accept") {
    		$this->hr_model->accept_interview($param2);
    		redirect($this->agent->referrer()); 
    	}else{
    	    
    	    $filter_data['staff_type']      = $this->input->get('staff_type');
        	$filter_data['keywords']  = $this->input->get('keywords');
        	$total_count              = $this->hr_model->get_paginated_interview_schedule_count($filter_data);
        	$page_data['total_count'] = $total_count;
        	$pagination               = $this->paginate(hr_url() . 'interview-schedule', $total_count);
        	$page_data['orders']      = $this->hr_model->get_paginated_interview_schedule($filter_data, $pagination['per_page'], $pagination['offset']);
        	
    	    $page_data['page_name']  = 'interview_schedule';
            $page_data['page_title'] = get_phrase('interview_schedule_list');
            $this->load->view('backend/index', $page_data);
    	}   	
        
    }
        
    public function documentation($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else{    	   
    	    $filter_data['is_pure']   = 0; 
    	    $filter_data['is_doc']    = 0; 
    	    $filter_data['staff_type']      = $this->input->get('staff_type');
        	$filter_data['keywords']  = $this->input->get('keywords');
        	$total_count              = $this->hr_model->get_paginated_documentation_count($filter_data);
        	$page_data['total_count'] = $total_count;
        	$pagination               = $this->paginate(hr_url() . 'documentation', $total_count);
        	$page_data['orders']      = $this->hr_model->get_paginated_documentation($filter_data, $pagination['per_page'], $pagination['offset']);
        	
			$page_data['pending_count']  = $this->hr_model->get_pending_doc_count();
			$page_data['verified_count'] = $this->hr_model->get_verified_doc_count();
			
    	    $page_data['page_name']  = 'documentation';
            $page_data['page_title'] = get_phrase('pending_documentation');
            $this->load->view('backend/index', $page_data);
    	}  	
        
    }  

	public function verified_documentation($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else{    	   
    	    $filter_data['is_pure']   = 0; 
    	    $filter_data['is_doc']    = 1; 
    	    $filter_data['staff_type']      = $this->input->get('staff_type');
        	$filter_data['keywords']  = $this->input->get('keywords');
        	$total_count              = $this->hr_model->get_paginated_documentation_count($filter_data);
        	$page_data['total_count'] = $total_count;
        	$pagination               = $this->paginate(hr_url() . 'verified-documentation', $total_count);
        	$page_data['orders']      = $this->hr_model->get_paginated_documentation($filter_data, $pagination['per_page'], $pagination['offset']);
        	
			$page_data['pending_count']  = $this->hr_model->get_pending_doc_count();
			$page_data['verified_count'] = $this->hr_model->get_verified_doc_count();
			
    	    $page_data['page_name']  = 'verified_documentation';
            $page_data['page_title'] = get_phrase('not_verified_documentation');
            $this->load->view('backend/index', $page_data);
    	}   	
        
    }
      
    public function send_sms_link(){
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $id = html_escape($this->input->post('id'));
        $this->hr_model->send_sms_link($id);        
    }
          
    public function approved_documentation($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else{    	    
    	    $filter_data['is_pure']   = 1;
    	    $filter_data['is_doc']    = 1; 
    	    $filter_data['staff_type']= $this->input->get('staff_type');
        	$filter_data['keywords']  = $this->input->get('keywords');
        	$total_count              = $this->hr_model->get_paginated_documentation_count($filter_data);
        	$page_data['total_count'] = $total_count;
        	$pagination               = $this->paginate(hr_url() . 'approved-documentation', $total_count);
        	$page_data['orders']      = $this->hr_model->get_paginated_documentation($filter_data, $pagination['per_page'], $pagination['offset']);
        	
    	    $page_data['page_name']  = 'approved_documentation';
            $page_data['page_title'] = get_phrase('approved_documentation');
            $this->load->view('backend/index', $page_data);
    	}   	
        
    }
	
    public function candidate_details($id = ""){
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $orders = $this->hr_model->get_candidate_details_by_id($id);
        if (!empty($orders)) {
            $page_data['order_id']   = $id;
            $page_data['id']         = $id;
            $page_data['data']       = $orders;
            $page_data['page_name']  = 'candidate_details';           
            
            $page_data['page_title'] = get_phrase('candidate_details');
            $this->load->view('backend/index', $page_data);
        } else {
            $this->session->set_flashdata('error_message', get_phrase('candidate_details_not_found!'));
            redirect($this->agent->referrer());
        }
    }
	
	public function approved_candidate($id) {
        if ($this->session->userdata('hr_login') != true) {
          redirect(site_url('login'), 'refresh');
        }

        $resultpost=$this->hr_model->approved_candidate($id);   
        return $resultpost; 
    }
	

    /*Staff Upcoming Bday Starts*/
  	public function staff_upcoming_birthday($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }       
		$page_data['page_name']  = 'staff_upcoming_birthday';
		$page_data['page_title'] = 'Staff Upcoming Birthday';
		$this->load->view('backend/index', $page_data);        
    } 
    /*Staff Upcoming Bday Ends*/
	
	
    /*HR Head Starts*/
	public function assign_salary($param1 = "", $param2 = "" ){
    	if ($this->session->userdata('hr_login') != true) {
    	  redirect(site_url('login'), 'refresh');
    	} elseif ($param1 == "update_salary") {
            $this->hr_model->update_candidate_salary($param2);
        } elseif ($param1 == "update_staff_details") {
            $this->hr_model->update_staff_details($param2);
        }	
    	else{
		
		 
         $page_data['page_name']  = 'assign_salary';
         $page_data['page_title'] = get_phrase('assign_salary');
         $this->load->view('backend/index', $page_data);
      } 
    }  

	public function get_assign_salary()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->hr_model->get_assign_salary();
        }
    }
     
   	
     public function update_salary($id = ""){
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $data = $this->hr_model->get_candidate_details_by_id($id);
        if (!empty($data)) {	
			$page_data['staff_category']   = $this->hr_model->get_staff_category();
            $page_data['staff_types']      = $this->hr_model->get_staff_type($data['staff_catid']);
			
    	    $page_data['shift_types'] = $this->common_model->getResultById('emp_shift_Type','name,value,',array('status'=>1)); 
    	    $page_data['banks']      = $this->common_model->getResultById('emp_bank','id,name',array('status'=>1)); 
			$page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
			$page_data['p_citys']    = $this->crud_model->get_city_by_state($data['p_state_id']);
			
            $page_data['order_id']   = $id;
            $page_data['id']         = $id;
            $page_data['data']       = $data;
            $page_data['page_name']  = 'update_salary_details';
            $page_data['page_title'] = $data['name'].' - '.get_phrase('update_salary_details');
            $this->load->view('backend/index', $page_data);
        } else {
            $this->session->set_flashdata('error_message', get_phrase('candidate_details_not_found!'));
            redirect($this->agent->referrer());
        }
    }
    
	
	 public function update_staff_details($id = ""){
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $data = $this->hr_model->get_candidate_details_by_id($id);
        if (!empty($data)) {
			$page_data['states']    = $this->crud_model->get_states();
			$page_data['banks']     = $this->common_model->getResultById('emp_bank','id,name',array('status'=>1)); 

			$page_data['citys']   = $this->crud_model->get_city_by_state($data['state_id']);  
			$page_data['p_citys'] = $this->crud_model->get_city_by_state($data['p_state_id']);
			
            $page_data['order_id']   = $id;
            $page_data['id']         = $id;
            $page_data['data']       = $data;
			
			
            $page_data['page_name']  = 'update_staff_details';           
            
            $page_data['page_title'] = $data['name'].' - '.get_phrase('update_staff_details');
            $this->load->view('backend/index', $page_data);
        } else {
            $this->session->set_flashdata('error_message', get_phrase('candidate_details_not_found!'));
            redirect($this->agent->referrer());
        }
    }
    
	
	 public function staff_list($param1 = "", $param2 = "" ){
    	if ($this->session->userdata('hr_login') != true) {
    	  redirect(site_url('login'), 'refresh');
    	}
		elseif ($param1=='left_staff') {
            $this->hr_model->move_to_left($param2);
    	}
		elseif ($param1 == "move_to_candidate") {
            $this->hr_model->move_to_candidate($param2);
        }
    	else{
			
		 
         $page_data['page_name']  = 'staff_list';
         $page_data['page_title'] = get_phrase('staff_list');
         $this->load->view('backend/index', $page_data);
      } 
    }

	public function get_staff_list()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->hr_model->get_staff_list();
        }
    }  
	
		
	public function holidays($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        elseif ($param1 == "add_post") {
            $this->hr_model->add_holidays($param2);
        } elseif ($param1 == "edit_post") {
            $this->hr_model->edit_holidays($param2);
        } elseif ($param1 == "delete") {
            $this->hr_model->delete_holidays($param2);
            redirect(site_url('hr/holidays'), 'refresh');
        }else {	
				
            $page_data['page_name']  = 'holidays';
            $page_data['page_title'] = 'Manage holidays';
            $this->load->view('backend/index', $page_data);
        }
    }    
	
    public function holidays_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
          $page_data['states']     = $this->crud_model->get_states();
          $page_data['staff_category']     = $this->hr_model->get_staff_category();
        if ($param1 == 'add') {
            $page_data['page_name']  = 'holidays_add';
            $page_data['page_title'] = 'Add Holidays';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
			$data = $this->common_model->getRowById('holidays','*',array('id'=>$param2));
            $page_data['data']       = $data;
            $page_data['page_name']  = 'holidays_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Holidays';
            $this->load->view('backend/index', $page_data);
        } 
    }
    
    public function get_holidays()  { 
        if ($this->input->is_ajax_request()) {
        $this->hr_model->get_holidays();
        }
    }
	
	
	
	 public function left_staff($param1 = "", $param2 = "" ){
    	if ($this->session->userdata('hr_login') != true) {
    	  redirect(site_url('login'), 'refresh');
    	}			
		 
         $page_data['page_name']  = 'left_staff';
         $page_data['page_title'] = get_phrase('left_staff');
         $this->load->view('backend/index', $page_data);
      
    }

	public function get_left_staff_list()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->hr_model->get_left_staff_list();
        }
    }  
	
    /*HR Head Ends*/
	
	
	
	
	
}