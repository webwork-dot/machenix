<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Nahid\JsonQ\Jsonq;

class CacheModel extends CI_Model{  
    function __construct() {
        parent::__construct();
        /*cache control*/
        date_default_timezone_set('Asia/Calcutta'); 
        $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'file'));
    }
    
  
    /*SALARY STAFF*/
    public function get_staff_salary_summary(){ 
        $params['draw'] = $_REQUEST['draw'];
        $start  = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		
		$year=date('Y', strtotime(date('Y-m-d') . ' -1 month'));
		$month=date('n', strtotime(date('Y-m-d') . ' -1 month'));
     
	 
    	if (!$this->cache->get('bas_staff_salary_summary')){ 	 
			$bank_list = array();
			$bank_list = array(
				array(
					"bank_id" => 21,
					"bank" => 'ICICI TO ICICI',
				),
				array(
					"bank_id" => 'other',
					"bank" => 'ICICI TO OTHERS',
				),
				array(
					"bank_id" => 10,
					"bank" => 'SBI TO SBI',
				),
				array(
					"bank_id" => 20,
					"bank" => 'HDFC TO HDFC',
				),
				array(
					"bank_id" => 'total',
					"bank" => 'TOTAL AMOUNT',
				),
			);
		
	    $total_count=count($bank_list);
		if (!empty($bank_list)) {
			foreach ($bank_list as $item) {
			$salary=0;  
			$bank_id=$item['bank_id'];
			
			if($item['bank_id']=='other'){
			  $get_salary = $this->db->query("SELECT COALESCE(SUM(a.final_salary), 0) as final_salary FROM `emp_generated_salary` a
			  RIGHT JOIN candidate e ON a.emp_id = e.emp_id WHERE a.year='$year' AND a.month='$month' AND (e.bank_id NOT IN (20, 21, 10))");		
			  if($get_salary->num_rows()>0){
			   $salary=$get_salary->row()->final_salary; 		
			   
			  }
			}
			if($item['bank_id']=='total'){
			 $get_salary = $this->db->query("SELECT COALESCE(SUM(a.final_salary), 0) as final_salary FROM `emp_generated_salary` a
			 RIGHT JOIN candidate e ON a.emp_id = e.emp_id WHERE a.year='$year' AND a.month='$month' ");		
			 if($get_salary->num_rows()>0){
			  $salary=$get_salary->row()->final_salary; 
			 }	
			}
			else{
			$get_salary = $this->db->query("SELECT COALESCE(SUM(a.final_salary), 0) as final_salary FROM `emp_generated_salary` a
			 RIGHT JOIN candidate e ON a.emp_id = e.emp_id WHERE a.year='$year' AND a.month='$month' AND e.bank_id='$bank_id' GROUP BY e.bank_id LIMIT 1");		
			 if($get_salary->num_rows()>0){
			  $salary=$get_salary->row()->final_salary; 
			 }	
			}
			 $data[] = array(
				"sr_no"         		=> ++$start,   
				"bank"         			=> $item['bank'],   
				"salary"         		=> round_int($salary),   
			 );
		   }
		  }		 
	     $ttl = 60 * 60 * 24; // time to live s/b 24 hours
	     $this->cache->save('bas_staff_salary_summary', $data, $ttl);
	   }
		
		$items = $this->cache->get('bas_staff_salary_summary');
    	$json_data= json_encode($items);
    	$q = new Jsonq($json_data); 
    	$total_count=$q->count();
    	if($q->count()>0){
    		 $item = $q->get();           
    		 $data=json_decode($item,TRUE);
    	}
    	else{
    		$data= array();
    	}
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
	
	
	 public function get_staff_upcoming_bday_summary(){ 
         $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		
		$year=date('Y', strtotime(date('Y-m-d')));
		$month=date('n', strtotime(date('Y-m-d')));

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['keyword']  = $search_value;
        
   

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_pure = '1' AND is_doc = '1'  AND is_kyc = '1' AND dob IS NOT NULL AND MONTH(dob) = MONTH(CURDATE())  AND DAY(dob) >= DAY(CURDATE()) LIMIT 5")->num_rows();
     
        $query = $this->db->query("SELECT id, emp_id, name, dob, salary_type FROM candidate WHERE is_pure = '1' AND is_doc = '1'  AND is_kyc = '1' AND dob IS NOT NULL AND MONTH(dob) = MONTH(CURDATE())  AND DAY(dob) >= DAY(CURDATE()) ORDER BY DAY(dob) ASC  LIMIT 5");
		
		
		
	 
    	if (!$this->cache->get('bas_staff_upcoming_bday_summary')){ 	
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $emp_id=$item['emp_id'];
			 
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "emp_name"		=> $item['name'].' #'.$item['emp_id'], 
                    "salary_type"	=> $item['salary_type'], 
				    "dob" 			=> date("d M, Y", strtotime($item['dob'])),
                    "action"        => $action,       
                );
            }
		   $ttl = 10800; // 3 hours in seconds
	       $this->cache->save('bas_staff_upcoming_bday_summary', $data, $ttl);
         }
		}
		
		$items = $this->cache->get('bas_staff_upcoming_bday_summary');
    	$json_data= json_encode($items);
    	$q = new Jsonq($json_data); 
    	$total_count=$q->count();
    	if($q->count()>0){
    		 $item = $q->get();           
    		 $data=json_decode($item,TRUE);
    	}
    	else{
    		$data= array();
    	}
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
	
	
    /*SALARY STAFF*/


}