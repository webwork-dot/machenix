<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends CI_Controller{
    public function __construct() {
        parent::__construct(); 
        
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('attendance_model');
        $this->load->model('hr_model');
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
    
    public function index(){
        if ($this->session->userdata('hr_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }
    

     /*Attendance Starts*/
     public function attendance_form($param1 = "", $param2 = "" ) {
    	if ($this->session->userdata('hr_login') != true) {
    		redirect(site_url('login'), 'refresh');
    	}
    	elseif ($param1 == 'import-attendance') {
    		$page_data['page_name']  = 'import_attendance';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Import Attendance';
    		$this->load->view('backend/index', $page_data);
    	}	
    	elseif ($param1 == 'attendance-list') {
			$page_data['candidate_list']  = $this->common_model->getResultById('candidate','id,emp_id,name',array('status'=>1,'is_kyc'=>1,'is_left'=>0));
    		$page_data['page_name']  = 'attendance_list';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Attendance List';
    		$this->load->view('backend/index', $page_data);
    	}	
    	elseif ($param1 == 'generate-salary') {
    		$page_data['page_name']  = 'generate_salary';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Generate Salary';
    		$this->load->view('backend/index', $page_data);
    	}
    	elseif ($param1 == 'salary-report') {		
			if(isset($_GET['month_id']) && $_GET['month_id']!="") :
			  $month_id=$_GET['month_id'];
			  $year_id=date('Y');
		  	  $selDate=$year_id.'-'.$month_id.'-01';
			  $month_name = date('F',strtotime($selDate));	
			  $dis_month=$month_name .'-'.$year_id;
    		  $page_data['page_title'] = $dis_month.' | Salary Report';
			else:  
    		  $page_data['page_title'] = 'Salary Report';
			endif;  
		
    		$page_data['page_name']  = 'salary_report';
    		$page_data['id']         = $param2;
    		$this->load->view('backend/index', $page_data);
    	}
	    elseif ($param1 == 'icici-salary-report') {		
			if(isset($_GET['month_id']) && $_GET['month_id']!="") :
			  $month_id=$_GET['month_id'];
			  $year_id=date('Y');
		  	  $selDate=$year_id.'-'.$month_id.'-01';
			  $month_name = date('F',strtotime($selDate));	
			  $dis_month=$month_name .'-'.$year_id;
    		  $page_data['page_title'] = $dis_month.' | ICICI Salary Report';
			else:  
    		  $page_data['page_title'] = 'ICICI Salary Report';
			endif;  
		
    		$page_data['page_name']  = 'icici_salary_report';
    		$page_data['id']         = $param2;
    		$this->load->view('backend/index', $page_data);
    	}
		elseif ($param1 == 'hdfc-salary-report') {		
			if(isset($_GET['month_id']) && $_GET['month_id']!="") :
			  $month_id=$_GET['month_id'];
			  $year_id=date('Y');
		  	  $selDate=$year_id.'-'.$month_id.'-01';
			  $month_name = date('F',strtotime($selDate));	
			  $dis_month=$month_name .'-'.$year_id;
    		  $page_data['page_title'] = $dis_month.' | HDFC Salary Report';
			else:  
    		  $page_data['page_title'] = 'HDFC Salary Report';
			endif;  
		
    		$page_data['page_name']  = 'hdfc_salary_report';
    		$page_data['id']         = $param2;
    		$this->load->view('backend/index', $page_data);
    	}	
		elseif ($param1 == 'sbi-salary-report') {		
			if(isset($_GET['month_id']) && $_GET['month_id']!="") :
			  $month_id=$_GET['month_id'];
			  $year_id=date('Y');
		  	  $selDate=$year_id.'-'.$month_id.'-01';
			  $month_name = date('F',strtotime($selDate));	
			  $dis_month=$month_name .'-'.$year_id;
    		  $page_data['page_title'] = $dis_month.' | SBI Salary Report';
			else:  
    		  $page_data['page_title'] = 'SBI Salary Report';
			endif;  
		
    		$page_data['page_name']  = 'sbi_salary_report';
    		$page_data['id']         = $param2;
    		$this->load->view('backend/index', $page_data);
    	}	
		elseif ($param1 == 'other-bank-salary-report') {		
			if(isset($_GET['month_id']) && $_GET['month_id']!="") :
			  $month_id=$_GET['month_id'];
			  $year_id=date('Y');
		  	  $selDate=$year_id.'-'.$month_id.'-01';
			  $month_name = date('F',strtotime($selDate));	
			  $dis_month=$month_name .'-'.$year_id;
    		  $page_data['page_title'] = $dis_month.' | Other Bank Salary Report';
			else:  
    		  $page_data['page_title'] = 'Other Bank Salary Report';
			endif;  
		
    		$page_data['page_name']  = 'other_bank_salary_report';
    		$page_data['id']         = $param2;
    		$this->load->view('backend/index', $page_data);
    	}	
		elseif ($param1 == 'generate-field-force-salary') {
    		$page_data['page_name']  = 'generate_field_force_salary';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Generate Field Force Salary';
    		$this->load->view('backend/index', $page_data);
    	}  
		elseif ($param1 == 'hold-salary') {
    		$page_data['page_name']  = 'hold_salary';
    		$page_data['id']         = $param2;
    		$page_data['page_title'] = 'Hold Salary';
    		$this->load->view('backend/index', $page_data);
    	}  
		elseif ($param1 == 'ff-salary-report') {		
			if(isset($_GET['month_id']) && $_GET['month_id']!="") :
			  $month_id=$_GET['month_id'];
			  $year_id=date('Y');
		  	  $selDate=$year_id.'-'.$month_id.'-01';
			  $month_name = date('F',strtotime($selDate));	
			  $dis_month=$month_name .'-'.$year_id;
    		  $page_data['page_title'] = $dis_month.' | Field Force Salary Report';
			else:  
    		  $page_data['page_title'] = 'Field Force Salary Report';
			endif;  
		
    		$page_data['page_name']  = 'ff_salary_report';
    		$page_data['id']         = $param2;
    		$this->load->view('backend/index', $page_data);
    	}
		
    }
	
	public function get_attendance_list()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_attendance_list();
        }
    } 
    

	
	
	public function get_salary_report_list()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_salary_report_list();
        }
    } 	
	
	public function get_field_force_salary_report_list()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_field_force_salary_report_list();
        }
    } 	
	
	public function check_ff_sandwich_attendance()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
           $month=8;
		   $year=2023;
		   $asm_id=177;
		   $asm_state_id=27;
         echo $this->attendance_model->check_ff_sandwich_attendance($month,$year,$asm_id,$asm_state_id);
        
    }	
	
	public function check_holiday_sandwich($month_id,$emp_id)  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
         echo $this->attendance_model->check_holiday_sandwich($month_id,$emp_id);
        
    }
	
	/*public function update_attendance_status()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
          $this->attendance_model->update_attendance_status();
        
    }*/

    public function calculateLateAndHalfDays() {
        $lateMarks = 11; 
        $halfMarks = 0; 

        $result = $this->attendance_model->calculateLateAndHalfDays($lateMarks,$halfMarks);

        //Display the result
        echo json_encode($result);
    }
	
    public function get_calculate_pf() {

        $param1 = $this->input->post('param1');
        $param2 = $this->input->post('param2');

        // Call the helper function and return the result as JSON
        echo calculate_pf($param1, $param2);
    }  

	public function get_calculate_esic() {
        $param1 = $this->input->post('param1');
        $param2 = $this->input->post('param2');
        $param3 = $this->input->post('param3');

        // Call the helper function and return the result as JSON
        echo calculate_esic($param1, $param2, $param3);
    }
	
	public function get_calculate_ptax() {

        $param1 = $this->input->post('param1');
        $param2 = $this->input->post('param2');
        $param3 = $this->input->post('param3');

        // Call the helper function and return the result as JSON
        echo calculate_ptax($param1, $param2, $param3);
    }  
	
		
	public function generate_salary()   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
         $this->attendance_model->generate_salary();
		}
        
    }	
	
	public function hold_salary()   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
         $this->attendance_model->hold_salary();
		}        
    }
    
	public function get_generated_salary_report()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_generated_salary_report();
        }
    } 	
	
	public function get_bank_wise_salary_report()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_bank_wise_salary_report();
        }
    } 
	
	
  public function get_staff_salary_summary()  { 
        if ($this->session->userdata('hr_login') != true && $this->session->userdata('calls_monitor_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
			$this->load->model('CacheModel');
            $this->CacheModel->get_staff_salary_summary();
        }
    }  
	
	
	public function get_hold_salary_report()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_hold_salary_report();
        }
    } 
	public function get_generated_ff_salary_report()  { 
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_generated_ff_salary_report();
        }
    } 		
   /*Attendance Ends*/
  
  
    /*Loans Starts*/
  	public function loans($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        elseif ($param1 == "add_post") {
            $this->attendance_model->add_loans($param2);
        } elseif ($param1 == "edit_post") {
            $this->attendance_model->edit_loans($param2);
        } elseif ($param1 == "delete") {
            $this->attendance_model->delete_loans($param2);
            redirect(site_url('hr/loans'), 'refresh');
        }else {
            $page_data['page_name']  = 'loans';
            $page_data['page_title'] = 'Manage loans';
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function loans_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($param1 == 'add') {
            $page_data['page_name']  = 'loans_add';
            $page_data['page_title'] = 'Add loans';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
			$data = $this->common_model->getRowById('loans','*',array('id'=>$param2));
            $page_data['data']       = $data;
            $page_data['page_name']  = 'loans_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit loans';
            $this->load->view('backend/index', $page_data);
        } 
    }
    
    public function get_loans()  { 
        if ($this->input->is_ajax_request()) {
        $this->attendance_model->get_loans();
        }
    }
	
	   
    public function update_ajax_attendance_status()  { 
        if ($this->input->is_ajax_request()) {
        $this->attendance_model->update_ajax_attendance_status();
        }
    } 
    /*Loans Ends*/
	
	
	/*Advance Starts*/
  	public function advance($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        elseif ($param1 == "add_post") {
            $this->attendance_model->add_advance($param2);
        } elseif ($param1 == "edit_post") {
            $this->attendance_model->edit_advance($param2);
        } elseif ($param1 == "delete") {
            $this->attendance_model->delete_advance($param2);
            redirect(site_url('hr/advance'), 'refresh');
        }else {
            $page_data['page_name']  = 'advance';
            $page_data['page_title'] = 'Manage Advance';
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function advance_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($param1 == 'add') {
            $page_data['page_name']  = 'advance_add';
            $page_data['page_title'] = 'Add Advance';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
			$data = $this->common_model->getRowById('advance','*',array('id'=>$param2));
            $page_data['data']       = $data;
            $page_data['page_name']  = 'advance_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Advance';
            $this->load->view('backend/index', $page_data);
        } 
    }
    
    public function get_advance()  { 
        if ($this->input->is_ajax_request()) {
        $this->attendance_model->get_advance();
        }
    }	
    /*Advance Ends*/
	
	
	
	/*Adjustment Starts*/
  	public function adjustment($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        elseif ($param1 == "add_post") {
            $this->attendance_model->add_adjustment($param2);
        } elseif ($param1 == "edit_post") {
            $this->attendance_model->edit_adjustment($param2);
        } elseif ($param1 == "delete") {
            $this->attendance_model->delete_adjustment($param2);
            redirect(site_url('hr/adjustment'), 'refresh');
        }else {
            $page_data['page_name']  = 'adjustment';
            $page_data['page_title'] = 'Manage Adjustment';
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function adjustment_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($param1 == 'add') {
            $page_data['page_name']  = 'adjustment_add';
            $page_data['page_title'] = 'Add adjustment';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
			$data = $this->common_model->getRowById('emp_adjustment','*',array('id'=>$param2));
            $page_data['data']       = $data;
            $page_data['page_name']  = 'adjustment_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Adjustment';
            $this->load->view('backend/index', $page_data);
        } 
    }
    
    public function get_adjustment()  { 
        if ($this->input->is_ajax_request()) {
        $this->attendance_model->get_adjustment();
        }
    }	
    /*Adjustment Ends*/
	
    /*Staff Upcoming Bday Starts*/
	  public function get_staff_upcoming_bday_summary()  { 
        if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
			$this->load->model('CacheModel');
            $this->CacheModel->get_staff_upcoming_bday_summary();
        }
    } 

  	public function staff_upcoming_birthday($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }       
		$page_data['page_name']  = 'staff_upcoming_birthday';
		$page_data['page_title'] = 'Staff Upcoming Birthday';
		$this->load->view('backend/index', $page_data);        
    }  
     
   public function get_staff_upcoming_bday()  { 
        if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_staff_upcoming_bday();
        }
    }   
	
  	public function education_bonus($page = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
		$page=urldecode($page);

		if($page == "OFFICE STAFF"){
		 $page_data['page']  = $page;			
		} 		
		elseif($page == "FIELD STAFF"){
		$page_data['page']  = $page;			
		}
		else{	
		 $page_data['page']  = "OFFICE STAFF";		
		}
	
		$page_data['total_office_staff']  = $this->attendance_model->get_total_edu_bonus_staff('OFFICE STAFF');
		$page_data['total_field_staff']  = $this->attendance_model->get_total_edu_bonus_staff('FIELD STAFF');
		
		$page_data['page_name']  = 'education_bonus';
		$page_data['page_title'] = 'Education Bonus - '.$page_data['page'];
		$this->load->view('backend/index', $page_data); 
		   
    }  
	
	public function get_education_bonus()  { 
        if ($this->session->userdata('super_user_id') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->attendance_model->get_education_bonus();
        }
    }    
    /*Staff Upcoming Bday Ends*/
	
	/*paidleave Starts*/
  	public function paidleave($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        elseif ($param1 == "add_post") {
            $this->attendance_model->add_paidleave($param2);
        } elseif ($param1 == "edit_post") {
            $this->attendance_model->edit_paidleave($param2);
        } elseif ($param1 == "delete") {
            $this->attendance_model->delete_paidleave($param2);
            redirect(site_url('hr/paidleave'), 'refresh');
        }else {
            $page_data['page_name']  = 'paidleave';
            $page_data['page_title'] = 'Manage Paid Leave';
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function paidleave_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($param1 == 'add') {
            $page_data['page_name']  = 'paidleave_add';
            $page_data['page_title'] = 'Add Paid Leave';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
			$data = $this->common_model->getRowById('emp_paidleave','*',array('id'=>$param2));
            $page_data['data']       = $data;
            $page_data['page_name']  = 'paidleave_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Paid Leave';
            $this->load->view('backend/index', $page_data);
        } 
    }
    
    public function get_paidleave()  { 
        if ($this->input->is_ajax_request()) {
        $this->attendance_model->get_paidleave();
        }
    }	
    /*paidleave Ends*/
    

/*tds Starts*/
  	public function tds($param1 = "", $param2 = "")   {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        elseif ($param1 == "add_post") {
            $this->attendance_model->add_tds($param2);
        } elseif ($param1 == "edit_post") {
            $this->attendance_model->edit_tds($param2);
        } elseif ($param1 == "delete") {
            $this->attendance_model->delete_tds($param2);
            redirect(site_url('hr/tds'), 'refresh');
        }else {
            $page_data['page_name']  = 'tds';
            $page_data['page_title'] = 'Manage TDS';
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function tds_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('hr_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($param1 == 'add') {
            $page_data['page_name']  = 'tds_add';
            $page_data['page_title'] = 'Add TDS';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
			$data = $this->common_model->getRowById('emp_tds','*',array('id'=>$param2));
            $page_data['data']       = $data;
            $page_data['page_name']  = 'tds_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit TDS';
            $this->load->view('backend/index', $page_data);
        } 
    }
    
    public function get_tds()  { 
        if ($this->input->is_ajax_request()) {
        $this->attendance_model->get_tds();
        }
    }	
    /*tds Ends*/
}