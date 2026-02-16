<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin_model extends CI_Model{
    
    function __construct() {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }
    
    /*Dr Starts 19-09-2022 */
    public function count_regular_dr_0_120(){  
        $sql = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)>=0 AND DATEDIFF(CURRENT_DATE,last_date)<=120) AND sale_value>'50'");
        return $sql->num_rows();
    }
   
    public function count_regular_dr_121_240(){   
       $sql = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)>=121 AND DATEDIFF(CURRENT_DATE,last_date)<=240) AND sale_value>'50'");
       return $sql->num_rows();
    }
   
    public function count_regular_dr_241_above(){  
        $sql = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)>=241) AND sale_value>'50'");
        return $sql->num_rows();
    }
    
    public function count_samples_dr(){    
        $sql = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND sale_value<='50'");
        return $sql->num_rows();
    }
    
    public function count_unpure_dr(){   
        $sql = $this->db->query("SELECT id FROM doctor WHERE is_pure='0'");
        return $sql->num_rows();
    }
    /*Dr Ends 19-09-2022 */   
  
    public function count_dss_dr(){
        $user_id = $this->session->userdata('super_user_id');
        $sql = $this->db->query("SELECT doc.id FROM samman_samaroh_doctors as doc INNER JOIN samman_samaroh as dss
    	ON doc.samman_id = dss.id WHERE dss.approval_status='1'");
        return $sql->num_rows();
    }
    
    public function count_np_dr(){
        $current_date = date('Y-m-d');
        $sql= $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)>90) AND sale_value>'0'");
        return $sql->num_rows();
    }
    
    public function count_nnp_dr(){
        $current_date = date('Y-m-d');
        $sql= $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND sale_value='0'");
        return $sql->num_rows();
    }
    
    public function count_regular_dr(){
        $current_date = date('Y-m-d');
        $sql= $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)<=90) AND sale_value>'0' ");
        return $sql->num_rows();
    }
      
       public function count_doctors_anniversary($filter_data){
        $current_date = date('Y-m-d');
        
        $query   = $this->db->query("SELECT id FROM doctor WHERE DAY(doa) = DAY(NOW()) GROUP BY id ORDER BY doa ASC");
        
        return $query->num_rows();
    }
    
    public function count_doctors_birthday($filter_data){
        $current_date = date('Y-m-d');
        $query   = $this->db->query("SELECT id FROM doctor WHERE DAY(dob) = DAY(NOW()) GROUP BY id ORDER BY dob ASC");
        return $query->num_rows();
    }
    
     public function count_interested_doctors(){
        $query_staff    = $this->db->query("SELECT id FROM camp_form WHERE request_letter='Pending'");
        return $query_staff->num_rows();
    }  
    
    public function count_upcoming_camp_doctors()  {
        $current_day    = date('Y-m-d');
        $query_staff    = $this->db->query("SELECT id FROM camp_form WHERE approve='1' AND '$current_day' <= DATE(camp_date)");
        return $query_staff->num_rows();
    } 
    
	public function count_completed_camp_doctors()  {
        $current_day    = date('Y-m-d');
        $query_staff    = $this->db->query("SELECT id FROM camp_form WHERE approve='1' AND '$current_day' > DATE(camp_date)");
        return $query_staff->num_rows();
    }

    public function count_upcoming_samman_samaroh(){
        $current_day    = date('Y-m-d');
        $query_staff    = $this->db->query("SELECT b.id FROM samman_samaroh as b INNER JOIN check_in_out as co ON co.samman_id=b.id  WHERE co.check_out_time IS NULL AND '$current_day' <= DATE(b.date)");
        return $query_staff->num_rows();
    }
        
    public function count_interested_gsb($filter_data) {
        $current_date         = date('Y-m-d');
        $check_in_date_filter = '';
        $query_staff    = $this->db->query("SELECT id FROM check_in_out	WHERE glow_sign_approached='Interested' AND check_in_time!='' AND check_out_time!='' AND DATE(date) ='$current_date' ");
        return $query_staff->num_rows();
        
    } 
    
    public function count_today_calls($filter_data) {
        $current_date = date('Y-m-d');
       
        $query_staff = $this->db->query("SELECT doc.id FROM doctor as doc 
                                   INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
                                   WHERE DATE(follow.added_date) ='$current_date'");
        return $query_staff->num_rows();
    }
    
     public function count_today_followup($filter_data)
    {
        $keyword_filter = "";
        $calls_filter = "";
        $resultdata     = array();
        $current_date   = date('Y-m-d');
 

        $query= $this->db->query("SELECT doc.id FROM doctor as doc 
                                   INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
                                   WHERE DATE(follow.follow_up_date) ='$current_date' ORDER BY follow.id DESC, follow.follow_up_time DESC");
        $master_id      = array();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                if (!in_array($item['doc_id'], $master_id)) {
                    $master_id[] = $item['doc_id'];
                    if ($item['follow_up_date'] == $current_date) {
                        $resultdata[] = array(
                            "id" => $item['id']
                        );
                    }
                }
            }
        }
        return $query->num_rows();
    }
   
    public function count_pending_expense()  {
        $query_staff    = $this->db->query("SELECT a.id
    	  FROM asm_users as a INNER JOIN attendance as at ON at.user_id = a.id 
    	  WHERE a.is_deleted='0' AND a.status='1' AND at.check_out_time!='' and at.co_approved='0' ");
        return $query_staff->num_rows();
    }
    
    public function count_pending_tour_program($filter_data){
       $count          = 0;
       $resultdata     = array();
       $query_staff = $this->db->query("SELECT a.id,atp.days FROM asm_users as a INNER JOIN asm_tour_plan as atp ON a.id = atp.asm_id");
    	if ($query_staff->num_rows() > 0) {
    		foreach ($query_staff->result_array() as $item_staf) {
    			$days = json_decode($item_staf['days']);
    			foreach ($days as $days_) {
    				if ($days_->is_request == 1) {
    					$count = $count + 1;
    				}
    			}
    		}
    	}
    	return $count;
    }
    
    public function get_paginated_orders_count($filter_data){
        $query = $this->db->query("SELECT id FROM orders WHERE (order_type = 'accounts') AND (approval_type='0' AND approval_status='0') ");
        return $query->num_rows();
    }
    
     public function get_paginated_approved_orders_count($filter_data){
        $query = $this->db->query("SELECT id FROM orders WHERE (order_type = 'accounts')  AND (approval_type!='0') ORDER BY id desc");
        return $query->num_rows();
    }
    
     public function get_graph_billed_order(){
     $resultdata  = array();
     $curr_date   =  date("Y-m-d");
     $month=date('m');
     $year=date('Y');
     /*$num = cal_days_in_month(CAL_GREGORIAN, $month, $year);*/
     $dates_month = array();
     $curr_day=date('d', strtotime($curr_date));   
 	 $user_id=$this->session->userdata('super_user_id');           
     for ($i = 1; $i <= $curr_day; $i++) { 
      $mktime = mktime(0, 0, 0, $month, $i, $year);
      $added_date = date("Y-m-d", $mktime);
	  $query = $this->db->query("SELECT SUM(price_total) as price_total FROM `orders` WHERE (order_type='accounts') AND (approval_type='4' AND approval_status='1') AND (DATE(order_date)='$added_date') GROUP BY DATE(order_date) ORDER BY DATE(order_date) asc LIMIT 1");
	 /* echo $this->db->last_query();
	  exit();*/
	  if(!empty($query)) {
    	$item=$query->row_array();		
		$resultdata[] = array(
			"date"    => $added_date,
			"day"    => date('d', strtotime($added_date)),
			"price_total" => ($item['price_total']==null ? 0:$item['price_total']),                  
		);
	   }
     } 
     return $resultdata;   
   }
   
   
      
   public function get_graph_payment_collection_order(){
     $resultdata  = array();
     $curr_date   =  date("Y-m-d");
     $month=date('m');
     $year=date('Y');
     $dates_month = array();
     $curr_day=date('d', strtotime($curr_date));   
 	 $user_id=$this->session->userdata('super_user_id');           
     for ($i = 1; $i <= $curr_day; $i++) { 
      $mktime = mktime(0, 0, 0, $month, $i, $year);
      $added_date = date("Y-m-d", $mktime);
	  $query = $this->db->query("SELECT SUM(price_total) as price_total FROM `payment_collection` WHERE (DATE(payment_date)='$added_date') GROUP BY DATE(payment_date) ORDER BY DATE(payment_date) asc LIMIT 1");
	  if(!empty($query)) {
    	$item=$query->row_array();		
		$resultdata[] = array(
			"date"    => $added_date,
			"day"    => date('d', strtotime($added_date)),
			"price_total" => ($item['price_total']==null ? 0:$item['price_total']),                  
		);
	   }
     } 
     return $resultdata;   
   }
   
   public function get_upcoming_doctors_birthday(){
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array();
		
		if(!empty($search_value)){
            $total_count = $this->db->query("SELECT id FROM doctor WHERE name like '%".$search_value."%' AND dob IS NOT NULL ORDER BY id desc ")->num_rows();
            $query = $this->db->query("SELECT id,dob,name,email,phone,state_name,city_name FROM doctor WHERE name like '%".$search_value."%' AND  dob IS NOT NULL ORDER BY id desc LIMIT $start, $length");
        }else{
           $total_count = $this->db->query("SELECT id FROM doctor WHERE dob IS NOT NULL ORDER BY id desc ")->num_rows();
            $query = $this->db->query("SELECT id,dob,name,email,phone,state_name,city_name FROM doctor WHERE dob IS NOT NULL ORDER BY id desc LIMIT $start, $length"); 
        }
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) {
                $data[] = array(
                    "id" => $item['id'], 
                    "dob" => date("d M, Y", strtotime($item['dob'])),
                    "name" => $item['name'],
                    "email" => $item['email'] != "null" ? $item['email']: '-',
                    "phone" => $item['phone'],
                    "state_name" => $item['state_name'],
                    "city_name" => $item['city_name']
                );
            }
         }
      
       
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   }
    
          
    public function get_upcoming_doctors_anniversary(){
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array();

		if(!empty($search_value)){
            $total_count = $this->db->query("SELECT id FROM doctor WHERE name like '%".$search_value."%' AND doa IS NOT NULL ORDER BY id desc ")->num_rows();
            $query = $this->db->query("SELECT id,doa,name,email,phone,state_name,city_name FROM doctor WHERE name like '%".$search_value."%' AND  doa IS NOT NULL ORDER BY id desc  LIMIT $start, $length");
        }else{
           $total_count = $this->db->query("SELECT id FROM doctor WHERE doa IS NOT NULL ORDER BY id desc  ")->num_rows();
           $query = $this->db->query("SELECT id,doa,name,email,phone,state_name,city_name FROM doctor WHERE doa IS NOT NULL ORDER BY id desc  LIMIT $start, $length"); 
        } 
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) {
                $data[] = array(
                    "id" => $item['id'], 
                    "doa" => date("d M, Y", strtotime($item['doa'])),
                    "name" => $item['name'],
                    "email" => $item['email'] != "null" ? $item['email']: '-',
                    "phone" => $item['phone'],
                    "state_name" => $item['state_name'],
                    "city_name" => $item['city_name']
                );
            }
        }
     
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   }
   
   
   
    public function get_asm_users(){
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
		if(!empty($search_value)){
		    $keyword=$search_value;
		    $keyword_filter =" AND (concat_ws(' ',first_name,last_name) like '%".$keyword."%'  
              or email like '%".$keyword."%'
              or phone like '%".$keyword."%'
              or state_name like '%".$keyword."%'
              or city_name like '%".$keyword."%')"; 
              
            $total_count = $this->db->query("SELECT id FROM asm_users WHERE (id<>'') and is_deleted='0' AND status='1'   $keyword_filter ORDER BY id desc")->num_rows();
            $query = $this->db->query("SELECT id,first_name,last_name,phone,email,state_name,city_name,added_by FROM asm_users WHERE (id<>'') and is_deleted='0' AND status='1'  $keyword_filter ORDER BY id desc LIMIT $start, $length");
        }else{
           $total_count = $this->db->query("SELECT id FROM asm_users WHERE (id<>'') and is_deleted='0' AND status='1' ORDER BY id desc ")->num_rows();
            $query = $this->db->query("SELECT id,first_name,last_name,phone,email,state_name,city_name,added_by FROM asm_users WHERE (id<>'') and is_deleted='0' AND status='1'  ORDER BY id desc LIMIT $start, $length"); 
        }
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) {
                $data[] = array(
                    "name" => $item['first_name'] . ' ' . $item['last_name'],
                    "phone" => $item['phone'],
                    "email" => $item['email'],
                    "state_name" => $item['state_name'],
                    "city_name" => $item['city_name'],
                    "coordinator" => $this->auth_model->get_user_name($item['added_by']),
                );
            }
        }
       
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    } 
   
     public function get_asm_attendance(){  
        $c_date = $_REQUEST['date_range'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
        $attendance_date_filter = "";
        
         if(isset($c_date) && $c_date!=""){
              $c_date= date("Y-m-d", strtotime($c_date));
              $c_year= date("Y", strtotime($c_date));
              $c_month= date("n", strtotime($c_date));
              $c_new_date= date("d F Y, D", strtotime($c_date));
              $attendance_date_filter =" AND (DATE(check_in_date) = '$c_date')"; 
         }
         else{
             $c_date= date("Y-m-d", strtotime(date("Y-m-d")));
             $c_year= date("Y", strtotime(date("Y-m-d")));
             $c_month= date("n", strtotime(date("Y-m-d")));
             $c_new_date= date("d F Y, D", strtotime(date("Y-m-d")));
             $attendance_date_filter =" AND (DATE(check_in_date) = '$c_date')";
         }	 
        

		if(!empty($search_value)){
		    $keyword=$search_value;
		    $keyword_filter =" AND (concat_ws(' ',first_name,last_name) like '%".$keyword."%')"; 
              
            $total_count = $this->db->query("SELECT id FROM asm_users WHERE (id<>'') AND is_deleted='0' AND status='1' $keyword_filter ORDER BY id desc")->num_rows();
            $query = $this->db->query("SELECT id,first_name,last_name FROM asm_users WHERE (id<>'')  AND is_deleted='0' AND status='1' $keyword_filter ORDER BY id desc LIMIT $start, $length");
        }else{
           $total_count = $this->db->query("SELECT id FROM asm_users WHERE (id<>'')  AND is_deleted='0' AND status='1' ORDER BY id desc ")->num_rows();
            $query = $this->db->query("SELECT id,first_name,last_name FROM asm_users WHERE (id<>'') AND is_deleted='0' AND status='1' ORDER BY id desc LIMIT $start, $length"); 
        }
        
        if (!empty($query)) {
          foreach ($query->result_array() as $fitem) {
            $asm_id = $fitem['id'];
            $user_name = $fitem['first_name'].' '.$fitem['last_name'];
             
            $area = '';
            $query_data = $this->db->query("SELECT days FROM `asm_tour_plan` WHERE asm_id='$asm_id' and year='$c_year' and month='$c_month' limit 1");
            
            if($query_data->num_rows() > 0){
                $row_data = $query_data->row_array();
                $days = json_decode($row_data['days']);
                foreach($days as $key=> $days_){
                    if($days_->day == $c_new_date){
                        $area = $days_->area;
                    }
                }
            }
              
                  
            $query = $this->db->query("SELECT * FROM `attendance` WHERE user_id='$asm_id' $attendance_date_filter ORDER BY id desc");
            /* echo $this->db->last_query();exit();*/
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $item) {
                $output='';   
                 $is_present=1;
                 if($is_present == 1){
    			   if($item['is_half_day'] == 0){
        			  $output .='<p class="text-success">Present</p>';
        			  $output .='<p>'. $area.'</p>';
        		    }else {
        			    $output .='<p class="text-info">Half Day</p>';
        			    $output .='<p>'. $area.'</p>';
        			}
                        
                  } else{ 
    			    $output .=' <p class="text-danger">Absent</p>';
    			    $output .='<p>'.  $area.'</p>';
    			   }  
                    
                   $check_in  = date('h:i:s A', strtotime($item['check_in_time']));
                   $check_out = $item['check_out_time'] != null ? date('h:i:s A', strtotime($item['check_out_time'])) : '-';
                   $location = $item['location'] != null ? $item['location'] : '-';
                   $checkout_location = $item['checkout_location'] != null ? $item['checkout_location'] : '-';
                   
                   
                   $data[] = array(
                    "name" => $user_name.' '.$output,
                    "check_in" => $check_in. '<br>' .$location,
                    "check_out" =>$check_out. '<br>' .$checkout_location,
                   );   
                    
                }
            }
            else{  
                $output='';   
                $is_present=0;
                 if($is_present == 0){ 
    			    $output .=' <p class="text-danger">Absent</p>';
    			    $output .='<p>'.  $area.'</p>';
    			}
    			
                $data[] = array(
                    "name" => $user_name.''.$output,
                    "check_in" => '-',
                    "check_out" =>'-',
                );
              }    
              
            }
         }
     
       
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
   
    public function get_asm_expense(){  
        $c_date = $_REQUEST['date_range'];
        $co_approved = $_REQUEST['co_approved'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
        $attendance_date_filter = "";
        

         if(isset($c_date) && $c_date!=""){
          $c_date= date("Y-m-d", strtotime($c_date));
          $attendance_date_filter .=" AND (DATE(at.check_in_date) = '$c_date')"; 
         }else{
             $c_date= date("Y-m-d", strtotime(date("Y-m-d")));
             $attendance_date_filter .=" AND (DATE(at.check_in_date) = '$c_date')";
         }	
        
         if(isset($co_approved) && $co_approved!=""){
          $co_approved= $co_approved;
          $attendance_date_filter .=" AND at.co_approved = '$co_approved'"; 
         }	
         
		if(!empty($search_value)){
		  $keyword=$search_value;
		  $keyword_filter =" AND (concat_ws(' ',a.first_name,a.last_name) like '%".$keyword."%')"; 
 
          $total_count = $this->db->query("SELECT a.id
    	  FROM asm_users as a INNER JOIN attendance as at ON at.user_id = a.id 
    	  WHERE a.is_deleted='0' AND a.status='1' AND at.check_out_time!='' $attendance_date_filter $keyword_filter")->num_rows();
	  
          $query = $this->db->query("SELECT a.first_name,a.last_name,a.id as asm_id,at.allowance,at.allowance_value,at.expenses,at.status,at.check_in_date,
          at.co_approved,at.manger_approved,at.audit_approved,at.account_approved,at.id as at_id
          FROM asm_users as a INNER JOIN attendance as at ON at.user_id = a.id 
          WHERE a.is_deleted='0' AND a.status='1' AND at.check_out_time!='' $attendance_date_filter $keyword_filter LIMIT $start,$length");
        }
        else{
          $total_count = $this->db->query("SELECT a.id
    	  FROM asm_users as a INNER JOIN attendance as at ON at.user_id = a.id 
    	  WHERE a.is_deleted='0' AND a.status='1' AND at.check_out_time!='' $attendance_date_filter")->num_rows();
	  
          $query = $this->db->query("SELECT a.first_name,a.last_name,a.id as asm_id,at.allowance,at.allowance_value,at.expenses,at.status,at.check_in_date,
          at.co_approved,at.manger_approved,at.audit_approved,at.account_approved,at.id as at_id
          FROM asm_users as a INNER JOIN attendance as at ON at.user_id = a.id 
          WHERE a.is_deleted='0' AND a.status='1' AND at.check_out_time!='' $attendance_date_filter LIMIT $start,$length");
        }
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) { 
                $user_name = $item['first_name'].' '.$item['last_name'];
                $allowance= $item['allowance'] != null ? $item['allowance'] : '-';
                $allowance_value= $item['allowance_value'] != null ? $item['allowance_value'] : '-';
                $expenses= $item['expenses'] != null ? $item['expenses'] : '-';
                $url=site_url('admin/asm-expense/details/'.$item['at_id']);
                
                if($item['co_approved'] == 0){
                 $status='<p class="badge badge-light-warning">Pending</p>';
                }
                else{
                   if($item['co_approved'] == 1 && $item['manger_approved'] == 0 && $item['audit_approved'] == 0 && $item['account_approved'] == 0){
                      $status='<span class="badge badge-light-success">Approved By Coodinator</span>';
			       }
			        if($item['co_approved'] == 1 && $item['manger_approved'] == 1 && $item['audit_approved'] == 0 && $item['account_approved'] == 0){
			           $status='<span class="badge badge-light-success">Approved By Manager</span>';
			       }
			       if($item['co_approved'] == 1 && $item['manger_approved'] == 1 && $item['audit_approved'] == 1 && $item['account_approved'] == 0){
			         $status='<span class="badge badge-light-success">Approved By Audit</span>';
			       }
			       if($item['co_approved'] == 1 && $item['manger_approved'] == 1 && $item['audit_approved'] == 1 && $item['account_approved'] == 1){
			            $status='<span class="badge badge-light-success">Payment Done</span>';
			       } 
                }  
                
                $data[] = array(
                    "name" => $user_name,
                    "allowance" => $allowance.' - ₹'.$allowance_value,
                    "expenses" => $expenses,
                    "status" => $status,
                    "action" => '<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button> </a>',
                );
            }
        }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 

    
    public function get_asm_check(){  
        $c_date = $_REQUEST['date_range'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
        $coordinator_id = $this->session->userdata('super_user_id');

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $asm_filter= ""; 
        $keyword_filter= ""; 
        $attendance_date_filter = "";
       
       	$filter_data['date_range']  = $_REQUEST['date_range'];
        $filter_data['keywords']    = $_REQUEST['keywords'];
        $filter_data['type_check']  = $_REQUEST['type_check'];
        
         $check_out_filter = '';
         if (isset($filter_data['type_check']) && $filter_data['type_check'] != "") {
            $type_check = $filter_data['type_check'];
            if ($type_check == 'in') {
               $check_out_filter = "AND  at.check_out_time IS NULL";
            }
            else if ($type_check == 'out') {
                $check_out_filter = "AND  at.check_in_time!='' and  at.check_out_time!=''";
            }
        }

        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
           $keyword        = $filter_data['keywords'];
           $keyword_filter = "AND (CONCAT(a.first_name,  ' ',a.last_name ) LIKE '%$keyword%'
           OR a.phone LIKE '%$keyword%'
           OR a.email LIKE '%$keyword%'
           OR at.check_type like '%" . $keyword . "%')";
        endif;
    
        if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
          $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  date('Y-m-d',strtotime($order_date[1])); 
          $order_date_filter =" AND (DATE(at.date) BETWEEN '$from' AND '$to')"; 
         else:
          $from =  date('Y-m-d'); 
          $to =  date('Y-m-d'); 
		  $order_date_filter =" AND (DATE(at.date) BETWEEN '$from' AND '$to')";         
         endif;	 
      
        $total_count = $this->db->query("SELECT a.id FROM asm_users as a 
        INNER JOIN check_in_out as at ON at.user_id = a.id 
        WHERE a.id<>'' AND a.is_deleted='0' AND a.status='1' $check_out_filter $keyword_filter $order_date_filter")->num_rows();
       
	   $query = $this->db->query("SELECT at.*,a.first_name,a.last_name,a.phone,a.id as asm_id FROM asm_users as a 
        INNER JOIN check_in_out as at ON at.user_id = a.id 
        WHERE a.id<>'' AND a.is_deleted='0' AND a.status='1' $check_out_filter $keyword_filter $order_date_filter order by at.id desc LIMIT $start,$length");
         //  echo $this->db->last_query();     
        if (!empty($query)) {
          foreach ($query->result_array() as $item) {
            $asm_id 	= $item['asm_id'];
            $user_name  = $item['first_name'].' '.$item['last_name'];
            
            $check_out_time = $item['check_out_time'];     
            $doctor_id     = $item['doctor_id'];     
			$doctor_name   = $this->crud_model->get_asm_doc_name($doctor_id);
    
            $url = site_url('admin/check-doctor/details/'.$item['id']); 
            $add_checkout_url="confirm_modal('".base_url()."admin/asm_check/add_checkout/".$item['id']."','Are Your, Want to Add Checkout')";
            $remove_checkout_url="confirm_modal('".base_url()."admin/asm_check/remove_checkout/".$item['id']."','Are Your, Want to Remove Checkout')";
					
            $action='';
            $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button> </a>';
		  
		    if($check_out_time==NULL || $check_out_time==''){
			  $action .=' <a href="#" onclick="'.$add_checkout_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Checkout"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" ><i class="fa fa-plus-square-o" aria-hidden="true"></i> Checkout</button></a>'; 
		    }
		    else{
		     $action .=' <a href="#" onclick="'.$remove_checkout_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Remove Checkout"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-minus-square-o" aria-hidden="true"></i> Checkout</button></a>';  
		    }  
            $check_type =''; 
            if($item['check_type']=='Doctor'){
	        $check_type='<div class="chip chip-primary mr-1">
                <div class="chip-body">
                    <span class="chip-text">Doctor</span>
                </div>
            </div>';
           }
           elseif($item['check_type']=='Medical Camp'){
	       $check_type='<div class="chip chip-warning mr-1">
                <div class="chip-body">
                    <span class="chip-text"> Medical Camp</span>
                </div>
            </div>';
            } elseif($item['check_type']=='Samman Samaroh'){	
	        $check_type='<div class="chip chip-success mr-1">
                <div class="chip-body">
                    <span class="chip-text"> Samman Samaroh</span>
                </div>
            </div>';
            } else{ $check_type=$item['check_type']; } 
            
            
            $data[] = array(
                "sr_no"              => ++$start,
                "date"               => date("d M, Y", strtotime($item['date'])),
                "uname"              => $user_name.' <br> '. $item['phone'],
                "check_type"         => $check_type,
                "doctor_name"        => ($doctor_name!=''? $doctor_name:'-'),
                "check_in"           => $item['check_in_time'].' <br> '. $item['location'],
                "check_out"          => $item['check_out_time'].' <br> '. $item['checkout_location'],
                "action"             => $action,
            );
          }
         }
     
       
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   }
       
    
    public function get_asm_doctors(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
 
		if(!empty($search_value)){
		  $keyword=$search_value;
		  $keyword_filter =" AND (concat_ws(' ',doctor_name) like '%".$keyword."%')"; 
 
          $total_count = $this->db->query("SELECT id FROM asm_doctors WHERE id<>'' $keyword_filter")->num_rows();
          $query = $this->db->query("SELECT id,user_id,doctor_name,doctor_phone,doctor_email,created_at FROM asm_doctors WHERE id<>'' $keyword_filter LIMIT $start,$length");
        }
        else{
          $total_count = $this->db->query("SELECT id FROM asm_doctors")->num_rows();
          $query = $this->db->query("SELECT id,user_id,doctor_name,doctor_phone,doctor_email,created_at FROM asm_doctors LIMIT $start,$length");
        }
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) { 
  
                $url=site_url('admin/asm-doctors/details/'.$item['id']);
                $asm_id = $item['user_id'];
                $asm_name = $this->crud_model->get_asm_name_($asm_id);
                $data[] = array(
                    "asm_name"     => $asm_name,
                    "doctor_name"  => $item['doctor_name'],
                    "doctor_phone" => $item['doctor_phone'],
					"doctor_email" => $item['doctor_email'],
                    "created_at"   => date("d M, Y", strtotime($item['created_at'])),
                    "action" => '<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button> </a>',
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    } 

   
    public function get_calls(){  
        $c_date = $_REQUEST['date_range'];
        $user_type = $_REQUEST['user_type'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
        $attendance_date_filter = "";
        $gsb_filter = "";
        

        if(isset($c_date) && $c_date!=""){
            $c_date= date("Y-m-d", strtotime($c_date));
            $attendance_date_filter=" AND (DATE(follow.added_date) = '$c_date')"; 
        }
        else{
            $c_date= date("Y-m-d", strtotime(date("Y-m-d")));
            $attendance_date_filter=" AND (DATE(follow.added_date) = '$c_date')";
        }	
         
        if(isset($user_type) && $user_type!= ""):
            $attendance_date_filter .= " AND (follow.user_type like '$user_type')";
        endif;
        
		if(!empty($search_value)){
		 $keyword=$search_value;
		 $keyword_filter =" AND (doc.name like '%".$keyword."%'
		 OR doc.phone like '%" . $keyword . "%')"; 

         $total_count = $this->db->query("SELECT doc.id FROM doctor as doc 
         INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
         WHERE  doc.id <>'' $keyword_filter $attendance_date_filter ORDER BY id desc")->num_rows();   
        
         $query = $this->db->query("SELECT doc.id,follow.added_by_name,doc.name,doc.phone,doc.state_name,doc.city_name,doc.area_name,follow.type,follow.follow_up_date,follow.follow_up_time,follow.user_type,follow.added_date 
         FROM doctor as doc 
         INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
         WHERE  doc.id <>'' $keyword_filter $attendance_date_filter ORDER BY follow.id desc LIMIT $start,$length");
       }
       else{
	     $total_count = $this->db->query("SELECT doc.id FROM doctor as doc 
         INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
         WHERE doc.id <>'' $attendance_date_filter ORDER BY id desc")->num_rows();   

         $query = $this->db->query("SELECT doc.id,follow.added_by_name,doc.name,doc.phone,doc.state_name,doc.city_name,doc.area_name,follow.type,follow.follow_up_date,follow.follow_up_time,follow.user_type,follow.added_date 
         FROM doctor as doc 
         INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
         WHERE doc.id <>'' $attendance_date_filter ORDER BY follow.id desc LIMIT $start,$length");
       }
        
       if (!empty($query)) {
           foreach ($query->result_array() as $item) { 
             $follow_up_date = $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-';
             $follow_up_time = $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-';
             $added_date = date("h:i A", strtotime($item['added_date']));
                    
             $data[] = array(
                "added_by" => $item['added_by_name'],
                "name" => $item['name'].'<br/>'.$item['phone'],
                "type" => $item['type'],
                "state_name" => $item['state_name'],
                "follow_up_date" => $follow_up_date,
                "follow_up_time" => $follow_up_time,
                "added_date" => $added_date
             ); 
            }
         }
      
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 


    public function get_today_followup(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
     

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (doc.name like '%" . $keyword . "%' or doc.phone like '%" . $keyword . "%')"; 
  
           $total_count = $this->db->query("SELECT doc.id
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' $keyword_filter ORDER BY follow.id DESC, follow.follow_up_time DESC")->num_rows();
                                       
           $query = $this->db->query("SELECT doc.id,doc.name,follow.doctor_id as doc_id,doc.phone,follow.type,follow.follow_up_date,follow.follow_up_time 
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' $keyword_filter  ORDER BY follow.id DESC, follow.follow_up_time DESC LIMIT $start,$length");                     
        }
        else{
           $total_count = $this->db->query("SELECT doc.id
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' ORDER BY follow.id DESC, follow.follow_up_time DESC")->num_rows();
                                       
           $query = $this->db->query("SELECT doc.id,doc.name,follow.doctor_id as doc_id,doc.phone,follow.type,follow.follow_up_date,follow.follow_up_time 
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' ORDER BY follow.id DESC, follow.follow_up_time DESC LIMIT $start,$length");       
        }
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) { 
              $follow_up_date = $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-';
              $follow_up_time = $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-';
              $doctor_id=$item['doc_id'];
              $action='<button  type="button" class="btn icon-btn mr-1 mb-1" type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasEnd"
                aria-controls="offcanvasEnd"
                onclick="get_timeline_('.$doctor_id.');"><span>View Timeline</span></button>';
                
                $data[] = array(
                    "name" => $item['name'].'<br>'.$item['phone'],
                    "type" =>  $item['type'],
                    "follow_up_date" =>$follow_up_date,
                    "follow_up_time" =>$follow_up_time,
                    "action" => $action,
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 


     public function get_timeline($doctor_id)   {
        $resultdata = array();
       
        $query      = $this->db->query("SELECT id,follow_up_date, follow_up_time,remark,added_by_name,added_date FROM `doctor_followup` 
        where doctor_id='$doctor_id' order by id desc");
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $date         = date("d M, Y", strtotime($item['follow_up_date'])) . ' ' . date("h:i A", strtotime($item['follow_up_time']));
                $resultdata[] = array(
                    "id" => $item['id'],
                    "remark" => $item['remark'],
                    "follow_up_date" => $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-',
                    "follow_up_time" => $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-',
                    "date" => $date,
                    "name" => $item['added_by_name'],
                    "added_date" => date('d-m-Y h:i A', strtotime($item['added_date']))
                );
            }
        }
        
        $date_time = array();
        foreach ($resultdata as $key => $row) {
            $date_time[$key] = $row['id'];
        }
        array_multisort($date_time, SORT_DESC, $resultdata);
        return $resultdata;
    }
     
   
    public function get_other_followup(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
        $current_date       = date('Y-m-d');
     

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (doc.name like '%" . $keyword . "%' or doc.phone like '%" . $keyword . "%')"; 
  
           $total_count = $this->db->query("SELECT doc.id
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' AND DATE(follow.follow_up_date) >'$current_date'  $keyword_filter ORDER BY follow.id DESC, follow.follow_up_time DESC")->num_rows();
                                       
           $query = $this->db->query("SELECT doc.id,doc.name,follow.doctor_id as doc_id,doc.phone,follow.type,follow.follow_up_date,follow.follow_up_time 
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' AND DATE(follow.follow_up_date) >'$current_date' $keyword_filter  ORDER BY follow.id DESC, follow.follow_up_time DESC LIMIT $start,$length");                     
        }
        else{
           $total_count = $this->db->query("SELECT doc.id
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' AND DATE(follow.follow_up_date) >'$current_date' ORDER BY follow.id DESC, follow.follow_up_time DESC")->num_rows();
                                       
           $query = $this->db->query("SELECT doc.id,doc.name,follow.doctor_id as doc_id,doc.phone,follow.type,follow.follow_up_date,follow.follow_up_time 
           FROM doctor as doc 
           INNER JOIN doctor_followup as follow ON doc.id = follow.doctor_id 
           WHERE doc.id<>'' AND DATE(follow.follow_up_date) >'$current_date' ORDER BY follow.id DESC, follow.follow_up_time DESC LIMIT $start,$length");       
        }
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) { 
              $follow_up_date = $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-';
              $follow_up_time = $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-';
                
                $data[] = array(
                    "name" => $item['name'].'<br>'.$item['phone'],
                    "type" =>  $item['type'],
                    "follow_up_date" =>$follow_up_date,
                    "follow_up_time" =>$follow_up_time,
                    "added_date" => date("m-d-Y h:i A", strtotime($item['added_date'])),
                );
            }
         }
      
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 
  
     
     public function pending_co_orders_count(){
        $query = $this->db->query("SELECT id FROM orders WHERE order_type = 'accounts' AND approval_type='0' ");
        return $query->num_rows();
    } 
    
    public function approved_co_orders_count(){
        $user_id=$this->session->userdata('super_user_id');
        $query = $this->db->query("SELECT id FROM orders WHERE order_type = 'accounts' AND approval_type!='0'");
        return $query->num_rows();
    }
      
    public function get_orders(){  
        $approval_type = $_REQUEST['approval_type'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter=$approval_filter=""; 
         
        if($approval_type=="0"){
            $approval_filter=" AND (approval_type='$approval_type')"; 
        }
        elseif($approval_type=="1"){
            $approval_filter=" AND (approval_type !='0')"; 
        }
         

		if(!empty($search_value)){
		   $keyword=$search_value;
           $keyword_filter = " AND (id like '%" . $keyword . "%'
           OR doctor_name like '%" . $keyword . "%'
           OR mobile_no like '%" . $keyword . "%'
           OR mr_name like '%" . $keyword . "%')";
		   
           $total_count = $this->db->query("SELECT id FROM orders WHERE id<>'' AND (order_type = 'accounts')  $approval_filter $keyword_filter ORDER BY id desc")->num_rows();
           $query = $this->db->query("SELECT id,doctor_name,mobile_no,mr_name,added_date,added_by_name FROM `orders` WHERE id<>'' AND (order_type = 'accounts') $approval_filter $keyword_filter ORDER BY id desc LIMIT $start,$length");
        }
        else{
        $total_count = $this->db->query("SELECT id FROM orders WHERE id<>'' AND (order_type = 'accounts') $approval_filter ORDER BY id desc")->num_rows();
           $query = $this->db->query("SELECT id,doctor_name,mobile_no,mr_name,added_date,added_by_name FROM `orders` WHERE id<>'' AND (order_type = 'accounts') $approval_filter ORDER BY id desc LIMIT $start,$length");
        }
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $date_         = date("Y-m-d H:i:s", strtotime($item['added_date']));			 
              $pending_since = get_time_difference($date_);
              $status='<span class="badge badge-primary">'.$this->crud_model->get_approval_status($item['id']).'</span>';
              $url=base_url().'admin/order-details/'.$item['id'];
              $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
                
                $data[] = array(
                    "id" => $item['id'],
                    "coordinator" =>  $item['added_by_name'],
                    "doctor_name" =>  $item['doctor_name'].'<br/>'.$item['mobile_no'],
                    "mr_name" =>  $item['mr_name'],
                    "date" => date("d M, Y", strtotime($item['added_date'])),
                    "status" =>$status,
                    "pending_since" =>$pending_since,
                    "action" => $action,
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 
   
    public function get_orders_distributor(){  
        $approval_type = $_REQUEST['approval_type'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter=$approval_filter=""; 
         

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter = " AND (id like '%" . $keyword . "%'
            OR doctor_name like '%" . $keyword . "%'
            OR mobile_no like '%" . $keyword . "%'
            OR mr_name like '%" . $keyword . "%')";
            
           $total_count = $this->db->query("SELECT id FROM orders WHERE id<>'' AND (order_type = 'distributor')  $keyword_filter ORDER BY id desc")->num_rows();
           $query = $this->db->query("SELECT id,doctor_name,mobile_no,mr_name,distributor_name,added_date,added_by_name FROM `orders` WHERE id<>'' AND (order_type = 'distributor') $approval_filter $keyword_filter ORDER BY id desc LIMIT $start,$length");
        }
        else{
        $total_count = $this->db->query("SELECT id FROM orders WHERE id<>'' AND (order_type = 'distributor') ORDER BY id desc")->num_rows();
           $query = $this->db->query("SELECT id,doctor_name,mobile_no,mr_name,distributor_name,added_date,added_by_name FROM `orders` WHERE id<>'' AND (order_type = 'distributor') ORDER BY id desc LIMIT $start,$length");
        }
        //echo $this->db->last_query();exit();
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $url=base_url().'admin/distributor-order-details/'.$item['id'];
              $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
                
                $data[] = array(
                    "id" => $item['id'],
                    "coordinator" =>  $item['added_by_name'],
                    "doctor_name" =>  $item['doctor_name'].'<br/>'.$item['mobile_no'],
                    "mr_name" =>  $item['mr_name'],
                    "distributor" =>  $item['distributor_name'],
                    "date" => date("d M, Y", strtotime($item['added_date'])),
                    "action" => $action,
                );
            }
         }
      
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   }  
   
      
     public function get_hold_order_count(){
      $user_id=$this->session->userdata('super_user_id');
      $count = 0;
      $manager_id = $this->session->userdata('super_user_id'); 
      $count = $this->db->query("SELECT id FROM orders WHERE order_type='hold_order' AND is_deleted='0' AND (added_by_id='$user_id')")->num_rows();
      return $count;
    }
        
    public function get_pending_orders_count(){
        $query = $this->db->query("SELECT id FROM orders WHERE order_type = 'accounts' AND (approval_type='0' AND approval_status='0') ");
        return $query->num_rows();
    } 
    
    public function get_hold_orders(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter = " AND (id like '%" . $keyword . "%'
            OR doctor_name like '%" . $keyword . "%'
            OR mobile_no like '%" . $keyword . "%'
            OR mr_name like '%" . $keyword . "%')";
            
           $total_count = $this->db->query("SELECT id FROM orders WHERE (order_type = 'hold_order')  AND is_deleted='0' AND (approval_type='1' AND approval_status='0') $keyword_filter ORDER BY id asc")->num_rows();
           $query = $this->db->query("SELECT id,doctor_name,mobile_no,mr_name,added_date,added_by_name FROM `orders` WHERE (order_type = 'hold_order') AND is_deleted='0' AND (approval_type='1' AND approval_status='0') $keyword_filter ORDER BY id asc LIMIT $start,$length");
        }
        else{
          $total_count = $this->db->query("SELECT id FROM orders WHERE (order_type = 'hold_order') AND is_deleted='0' AND (approval_type='1' AND approval_status='0') ORDER BY id asc")->num_rows();
          $query = $this->db->query("SELECT id,doctor_name,mobile_no,mr_name,added_date,added_by_name FROM `orders` WHERE (order_type = 'hold_order') AND is_deleted='0' AND (approval_type='1' AND approval_status='0') ORDER BY id asc LIMIT $start,$length");
        }
        //echo $this->db->last_query();exit();
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $date_         = date("Y-m-d H:i:s", strtotime($item['added_date']));			 
              $pending_since = get_time_difference($date_);
              $status='<span class="badge badge-primary">'.$this->crud_model->get_approval_status($item['id']).'</span>';
              $url=base_url().'admin/hold-order/details/'.$item['id'];
              $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
                
                $data[] = array(
                    "id" => $item['id'],
                    "doctor_name" =>  $item['doctor_name'].'<br/>'.$item['mobile_no'],
                    "mr_name" =>  $item['mr_name'],
                    "coordinator" =>  $item['added_by_name'],
                    "pending_since" =>$pending_since,
                    "date" => date("d M, Y", strtotime($item['added_date'])),
                    "action" => $action,
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
    
   
    public function get_billed_orders(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;  
		   $keyword_filter = " AND (id like '%" . $keyword . "%'
            OR doctor_name like '%" . $keyword . "%'
            OR mobile_no like '%" . $keyword . "%'
            OR mr_name like '%" . $keyword . "%')";
            
           $total_count = $this->db->query("SELECT id FROM orders WHERE (order_type = 'accounts')  AND (approval_type='4' AND approval_status='1') $keyword_filter ORDER BY id desc")->num_rows();
           $query = $this->db->query("SELECT id,doctor_name,mobile_no,mr_name,added_date,bill_no,bill_url,acc_approval_by_name,order_date,added_by_name FROM `orders` WHERE (order_type = 'accounts') AND (approval_type='4' AND approval_status='1') $keyword_filter ORDER BY id desc LIMIT $start,$length");
        }
        else{
          $total_count = $this->db->query("SELECT id FROM orders WHERE (order_type = 'accounts') AND (approval_type='4' AND approval_status='1') ORDER BY id desc")->num_rows();
          $query = $this->db->query("SELECT id,doctor_name,mobile_no,billed_by,mr_name,added_date,bill_no,bill_url,acc_approval_by_name,order_date,added_by_name FROM `orders` WHERE (order_type = 'accounts') AND (approval_type='4' AND approval_status='1') ORDER BY id desc LIMIT $start,$length");
        }
        //echo $this->db->last_query();exit();
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $date_         = date("Y-m-d H:i:s", strtotime($item['added_date']));			 
              $pending_since = get_time_difference($date_);
              $status='<span class="badge badge-primary">'.$this->crud_model->get_approval_status($item['id']).'</span>';
             
              $bill_url_arr=explode(",",$item['bill_url']);
              $output='';
              $output=count($bill_url_arr).' Bill';  
              $bill=$output;
              
              $url=base_url().'admin/billed-order/details/'.$item['id'];
              $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
              
                $data[] = array(
                    "coordinator" =>  $item['added_by_name'],
                    "bill_no" => $item['bill_no'],
                    "id" => $item['id'],
                    "doctor_name" =>  $item['doctor_name'].'<br/>'.$item['mobile_no'],
                    "bill" =>  $bill,
                    "acc_approval_by_name" =>   $item['acc_approval_by_name'],
                    "billed_by" => $item['billed_by'],
                    "order_date" =>date("d M, Y", strtotime($item['order_date'])),
                    "action" => $action,
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 
     
   
    public function get_pending_approval(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter = " AND (id like '%" . $keyword . "%'
            OR doctor_name like '%" . $keyword . "%'
            OR doctor_mobile like '%" . $keyword . "%')";
		   
           $total_count = $this->db->query("SELECT id FROM camp_form WHERE request_letter='Yes' AND approve='0' $keyword_filter ORDER BY id desc")->num_rows();  
           $query = $this->db->query("SELECT id,user_id,doctor_name,doctor_mobile,state,district,camp_date,created_at FROM camp_form WHERE request_letter='Yes' AND approve='0' $keyword_filter ORDER BY id desc LIMIT $start,$length");    
		}
        else{
           $total_count = $this->db->query("SELECT id FROM camp_form WHERE request_letter='Yes' AND approve='0' ORDER BY id desc")->num_rows();  
           $query = $this->db->query("SELECT id,user_id,doctor_name,doctor_mobile,state,district,camp_date,created_at FROM camp_form WHERE request_letter='Yes' AND approve='0' ORDER BY id desc LIMIT $start,$length");   
        }
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
                $asm_name = '';
                $asm_id         = explode(',',$item['user_id']);
                 foreach($asm_id as $asm_id_){
                    if($asm_id_!=''){ 
                        $asm_name .= $this->crud_model->get_asm_name_($asm_id_).'<br/>';
                    }
              }
           
              $url=base_url().'admin/pending-approval/details/'.$item['id'];
              $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
              
        
                $data[] = array(
                    "asm_name" => $asm_name,
                    "doctor_name" => $item['doctor_name'].'<br/>'.$item['doctor_mobile'],
                    "state" => $item['state'],
                    "district" =>  $item['district'],
                    "camp_date" =>  date("d M, Y", strtotime($item['camp_date'])),
                    "camp_date1" => get_time_difference_php(date("Y-m-d H:i:s", strtotime($item['created_at']))),
                    "action" => $action,
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }

   
    public function get_approved_camp(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter = " AND (id like '%" . $keyword . "%'
            OR doctor_name like '%" . $keyword . "%'
            OR doctor_mobile like '%" . $keyword . "%')";
            
           $total_count = $this->db->query("SELECT id FROM camp_form WHERE approve='1' $keyword_filter ORDER BY id desc")->num_rows();  
           $query = $this->db->query("SELECT * FROM camp_form WHERE  approve='1' $keyword_filter ORDER BY id desc LIMIT $start,$length");    
		}
        else{
           $total_count = $this->db->query("SELECT id FROM camp_form WHERE approve='1' ORDER BY id desc")->num_rows();  
           $query = $this->db->query("SELECT id,user_id,asm_name,doctor_name,doctor_mobile,state,district,camp_date,approve_date FROM camp_form WHERE approve='1' ORDER BY id desc LIMIT $start,$length");   
        }
        
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $url=base_url().'admin/approved-camp/details/'.$item['id'];
              $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
              
                $data[] = array(
                    "asm_name" => $item['asm_name'],
                    "doctor_name" => $item['doctor_name'].'<br/>'.$item['doctor_mobile'],
                    "state" => $item['state'],
                    "district" =>  $item['district'],
                    "camp_date" =>  date("d M, Y", strtotime($item['camp_date'])),
                    "approve_date"   => date("d M, Y h:i", strtotime($item['approve_date'])),
                    "action" => $action,
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 
   
   
   
      public function get_payment_collection(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (txn_no like '%" . $keyword . "%')"; 
	       $total_count = $this->db->query("SELECT id FROM `payment_collection` WHERE id<>'' $keyword_filter ORDER BY id desc")->num_rows();
	       $query = $this->db->query("SELECT id,asm_id,asm_name,price_total,payment_date,txn_no,payment_mode,status FROM `payment_collection` WHERE id<>'' $keyword_filter ORDER BY id desc LIMIT $start,$length");
		}
        else{
           $total_count = $this->db->query("SELECT id FROM `payment_collection` ORDER BY id desc")->num_rows();
	       $query = $this->db->query("SELECT id,asm_id,asm_name,price_total,payment_date,txn_no,payment_mode,status FROM `payment_collection` ORDER BY id desc LIMIT $start,$length");
    	 }
        
        if (!empty($query)) {
          foreach ($query->result_array() as $item) {
            if($item['status']== 'approved'):
              $status='<small><p> <span class="badge badge-light-success">Approved</span></p></small>';
             else:  
              $status='<small><p> <span class="badge badge-light-warning">Pending</p></small>';
             endif;     
             
             $url=base_url().'admin/payment-collection-details/'.$item['id'];
             $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
              
             $data[] = array(
                "asm_name" => $item['asm_name'],
                "txn_no" => $item['txn_no'],
                "payment_mode" =>  $item['payment_mode'],
                "price_total" =>  $item['price_total'],
                "status" => $status,
                "payment_date"   => date("d M, Y", strtotime($item['payment_date'])),
                "action" => $action,
            );
           }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    } 
   
     
    public function get_manager_state_mapping(){  
        $manager_id = $_REQUEST['manager_id'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (state_name like '%" . $keyword . "%')"; 
	       $total_count = $this->db->query("SELECT `id`, `state_name`, `added_date` FROM `manager_state_mapping` WHERE mngt_id='$manager_id' $keyword_filter ORDER BY id desc")->num_rows();
	       $query = $this->db->query("SELECT `id`, `state_name`, `added_date` FROM `manager_state_mapping` WHERE mngt_id='$manager_id' $keyword_filter ORDER BY id desc LIMIT $start,$length");
		}
        else{
           $total_count = $this->db->query("SELECT `id`, `state_name`, `added_date` FROM `manager_state_mapping` WHERE mngt_id='$manager_id' ORDER BY id desc")->num_rows();
	       $query = $this->db->query("SELECT `id`, `state_name`, `added_date` FROM `manager_state_mapping` WHERE mngt_id='$manager_id' ORDER BY id desc LIMIT $start,$length");
    	 }
        
        if (!empty($query)) {
          foreach ($query->result_array() as $item) {
             
             $action='<i style="font-size: 18px;color: red;" class="fa fa-trash" class="me-50" onclick="get_delete_(' . $item['id'] . ');" aria-hidden="true"></i>';
              
             $data[] = array(
                "manager" => $this->auth_model->get_user_name($manager_id),
                "state" => $item['state_name'],
                "date"   => date("d M, Y H:i", strtotime($item['added_date'])),
                "action" => $action,
            );
           }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    } 
   
     public function add_manager_state_mapping(){
         $resultpost = array(
            "status" => 200,
            "message" => 'Coordinator Mapped Successfully'
        ); 
        
        $state_ids  = html_escape($this->input->post('state_id'));  
        $mngt_id    = html_escape($this->input->post('mngt_id'));  
        foreach($state_ids as $state_id){   
          if($state_id!='' && $state_id!=0){
           $check_count = $this->db->query("SELECT id FROM manager_state_mapping WHERE mngt_id='$mngt_id' AND state_id='$state_id'")->num_rows();
           if($check_count==0){
              $data=array();
              $data['mngt_id']     = $mngt_id;
              $data['state_id']    = $state_id;
              $data['state_name']  = $this->auth_model->get_state_name($state_id);
              $data['added_date']  = date("Y-m-d H:i:s");
              $this->db->insert('manager_state_mapping', $data);
           }
          }
        }
    	
     $this->session->set_flashdata('flash_message', get_phrase('added_coordinator_mapping_successfully'));
      return simple_json_output($resultpost); 
    }
    
    
    
     public function get_samman_samaroh(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (state_name like '%" . $keyword . "%')"; 
	
	     $total_count = $this->db->query("SELECT id FROM samman_samaroh WHERE id<>'' $keyword_filter ORDER BY id desc")->num_rows();
	     $query = $this->db->query("SELECT id,asm_name,venue,state_name,district_name,date FROM samman_samaroh WHERE id<>'' $keyword_filter ORDER BY id desc  LIMIT $start,$length");
		}
        else{
	     $total_count = $this->db->query("SELECT id FROM samman_samaroh ORDER BY id desc")->num_rows();
	     $query = $this->db->query("SELECT id,asm_name,venue,state_name,district_name,date FROM samman_samaroh ORDER BY id desc  LIMIT $start,$length");
    	 }
        
        if (!empty($query)) {
          foreach ($query->result_array() as $item) {
             $data[] = array(
                "venue" => $item['venue'],
                "date"  => date("d M, Y", strtotime($item['date'])),
                "state_name" => $item['state_name'],
                "district_name" => $item['district_name'],
                "asm_name" => $item['asm_name'],
            );
           }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    } 
    
    public function get_pending_tour_program(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (CONCAT(a.first_name,  ' ',a.last_name ) LIKE '%$keyword%')"; 
           $count          = 0;
           $query_staff = $this->db->query("SELECT a.id,atp.days FROM asm_users as a INNER JOIN asm_tour_plan as atp ON a.id = atp.asm_id $keyword_filter");
           if ($query_staff->num_rows() > 0) {
                foreach ($query_staff->result_array() as $item_staf) {
                    $days = json_decode($item_staf['days']);
                    foreach ($days as $days_) {
                        if ($days_->is_request == 1) {
                            $count = $count + 1;
                        }
                    }
                }
            }
    	   $total_count = $count;   
            
    	   $query = $this->db->query("SELECT a.id AS asm_id,a.first_name,a.last_name,atp.days,atp.days,atp.id as tour_id 
    	   FROM asm_users as a INNER JOIN asm_tour_plan as atp ON a.id = atp.asm_id WHERE a.id<>'' $keyword_filter LIMIT $start,$length");
		}
        else{
            $count          = 0;
    	   $query_staff = $this->db->query("SELECT a.id,atp.days FROM asm_users as a INNER JOIN asm_tour_plan as atp ON a.id = atp.asm_id");
           if ($query_staff->num_rows() > 0) {
                foreach ($query_staff->result_array() as $item_staf) {
                    $days = json_decode($item_staf['days']);
                    foreach ($days as $days_) {
                        if ($days_->is_request == 1) {
                            $count = $count + 1;
                        }
                    }
                }
            }
    	   $total_count = $count;
    	   
    	   $query = $this->db->query("SELECT a.id AS asm_id,a.first_name,a.last_name,atp.days,atp.id as tour_id 
    	   FROM asm_users as a INNER JOIN asm_tour_plan as atp ON a.id = atp.asm_id LIMIT $start,$length");
    	 }
        
        if (!empty($query)) {
          foreach ($query->result_array() as $item) {
            $asm_id    = $item['asm_id'];
            $user_name = $item['first_name'] . ' ' . $item['last_name'];
            $days      = json_decode($item['days']); 
            
            foreach ($days as $key => $days_) {
                if ($days_->is_request == 1) {
                    $data[] = array(
                      "day" => date("d M, Y", strtotime($days_->day)),
                      "uname" => $user_name,
                      "id" => $item['tour_id'],
                      "area" => $days_->area,
                      "request_area" => $days_->request_area,
                      "date" => get_time_difference_php(date("Y-m-d H:i:s", strtotime($days_->date))),
                  );
                }
            }
          }
        }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    } 
    
    
    public function get_asm_tour_plan(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (CONCAT(a.first_name,  ' ',a.last_name ) LIKE '%$keyword%')"; 
		   
           $total_count = $this->db->query("SELECT a.id FROM  asm_tour_plan as a
           INNER JOIN asm_users as m ON m.id = a.asm_id
           WHERE a.asm_id!='' $keyword_filter ORDER BY id desc")->num_rows();  
        	        
           $query = $this->db->query("SELECT a.* FROM  asm_tour_plan as a
           INNER JOIN asm_users as m ON m.id = a.asm_id
           WHERE a.asm_id!='' $keyword_filter ORDER BY id desc  LIMIT  $start,$length");  
		}
        else{
           $total_count = $this->db->query("SELECT a.id FROM  asm_tour_plan as a
           INNER JOIN asm_users as m ON m.id = a.asm_id
           WHERE a.asm_id!='' ORDER BY id desc")->num_rows();  
        	        
           $query = $this->db->query("SELECT a.* FROM  asm_tour_plan as a
           INNER JOIN asm_users as m ON m.id = a.asm_id
           WHERE a.asm_id!='' ORDER BY id desc  LIMIT  $start,$length");  
    	 }
        
        if (!empty($query)) {
          foreach ($query->result_array() as $item) {
             $asm_id     = $item['asm_id'];
                $asm_name   = $this->crud_model->get_asm_name_($asm_id);
                $month_id     = $item['month'];
                $month_name   = $this->crud_model->get_month_name_($month_id);
                
                $data[] = array(
                "name"      => $asm_name,
                "year"      => $item['year'],
                "month"     => $month_name,
                "date"      => date("d M, Y", strtotime($item['added_date']))
              );
            
            
          }
        }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    } 
    
    
    
    
   
    public function get_interested_gsb(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
        $attendance_date_filter = "";
        

         if(isset($c_date) && $c_date!=""){
          $c_date= date("Y-m-d", strtotime($c_date));
          $attendance_date_filter .=" AND (DATE(at.check_in_date) = '$c_date')"; 
         }else{
             $c_date= date("Y-m-d", strtotime(date("Y-m-d")));
             $attendance_date_filter .=" AND (DATE(at.check_in_date) = '$c_date')";
         }	
        
         if(isset($co_approved) && $co_approved!=""){
          $co_approved= $co_approved;
          $attendance_date_filter .=" AND at.co_approved = '$co_approved'"; 
         }	
         
		if(!empty($search_value)){
		  $keyword=$search_value;
		  $keyword_filter =" AND (doc.doctor_name like '%".$keyword."%' 
		  OR doc.doctor_phone like '%".$keyword."%'
		  OR doc.doctor_email like '%".$keyword."%')"; 
 
          $total_count = $this->db->query("SELECT doc.doctor_name FROM asm_doctors as doc INNER JOIN check_in_out as c ON doc.id = c.doctor_id 
          WHERE c.glow_sign_approached='Interested' $keyword_filter")->num_rows();

          $query = $this->db->query("SELECT doc.doctor_name,doc.doctor_phone,doc.doctor_email,doc.doctor_state,doc.doctor_district,c.user_id as asm_id,c.date FROM asm_doctors as doc INNER JOIN check_in_out as c ON doc.id = c.doctor_id 
          WHERE c.glow_sign_approached='Interested' $keyword_filter LIMIT $start,$length"); 
        }
        else{
          $total_count = $this->db->query("SELECT doc.doctor_name FROM asm_doctors as doc INNER JOIN check_in_out as c ON doc.id = c.doctor_id 
          WHERE c.glow_sign_approached='Interested'")->num_rows();

          $query = $this->db->query("SELECT doc.doctor_name,doc.doctor_phone,doc.doctor_email,doc.doctor_state,doc.doctor_district,c.user_id as asm_id,c.date FROM asm_doctors as doc INNER JOIN check_in_out as c ON doc.id = c.doctor_id 
          WHERE c.glow_sign_approached='Interested' LIMIT $start,$length"); 
        }
        
        if (!empty($query)) {
          foreach ($query->result_array() as $item) { 
            $asm_id     = $item['asm_id'];
            $asm_name   = $this->crud_model->get_asm_name_($asm_id); 
              
            $data[] = array(
                "asm" => $asm_name,
                "doctor" => $item['doctor_name'],
                "state" => $item['doctor_state'],
                "city" => $item['doctor_district'],
                "date" =>  date("d M, Y", strtotime($item['date'])),
               
            );
          }
         }
      
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   } 

    
    public function get_timeline_form($doctor_id){
        $resultdata = array();
        $logs_data = array();
        $user_id    = $this->session->userdata('super_user_id');
        $query      = $this->db->query("SELECT id,follow_up_date, follow_up_time,remark,added_by_name,added_date FROM `doctor_followup` 
        where doctor_id='$doctor_id' order by id desc");
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $date         = date("d M, Y", strtotime($item['follow_up_date'])) . ' ' . date("h:i A", strtotime($item['follow_up_time']));
                $logs_data[] = array(
                    "id" => $item['id'],
                    "type" => "timeline",
                    "remark" => $item['remark'] != null ? $item['remark'] : '-',
                    "follow_up_date" => $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-',
                    "follow_up_time" => $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-',
                    "date" => $date,
                    "name" => $item['added_by_name'],
                    "added_date" => date('d-m-Y h:i A', strtotime($item['added_date']))
                );
            }
        }
        
         $check_in = array();
        $doc_order   = $this->db->query("SELECT id,doctor_id,price_total,bill_url,bill_no,added_by_name,added_date FROM orders WHERE doctor_id='$doctor_id'  AND (order_type='accounts') AND (approval_type='4' AND approval_status='1') ORDER BY id desc");
        foreach($doc_order->result_array() as $item_doc) {
            $doctor_id  = $item_doc['doctor_id'];  
            
            $bill_url_arr=explode(",",$item_doc['bill_url']);
            $output='';
            foreach($bill_url_arr as $key=> $bill_url){
              $f_bill_url=base_url().$bill_url;
              $output .='<a class="bill_url" href="'.$f_bill_url.'" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> View Bill '.($key+1).'</a> &nbsp; &nbsp;  ';  
            }
            $check_in[] = array(
					"id"           => $doctor_id,
					"type"         => "order",
					"price_total"  => $item_doc['price_total'] != null ? $item_doc['price_total'] : '-',
					"bill_url" 	   => $output,
					"bill_no" 	   => $item_doc['bill_no'] != null ? $item_doc['bill_no'] : '-',
					"name"         => $item_doc['added_by_name'],
					"added_date"   => date('d-m-Y h:i A', strtotime($item_doc['added_date']))
				);
				
        }
        
        
        $camp_order = array();
        $sql_order   = $this->db->query("SELECT id,doctor_id,bill_no,attach_bill,added_by_name,bill_date as added_date FROM camp_form WHERE (doctor_id='$doctor_id' AND approve_bill='1') ORDER BY id desc");
        foreach($sql_order->result_array() as $item_camp) {
            $bill_url_arr=explode(",",$item_camp['attach_bill']);
            $output='';
            foreach($bill_url_arr as $key=> $bill_url){
              $f_bill_url=base_url().$bill_url;
              $output .=' <a class="bill_url" href="'.$f_bill_url.'" target="_blank"> <i class="fa fa-file-pdf-o" aria-hidden="true"></i> View Bill '.($key+1).'</a> &nbsp; &nbsp;  ';  
            }
            
            $check_in[] = array(
					"id"           =>  $item_camp['doctor_id'],
					"type"         => "camp_order",
					"bill_url" 	   => $output,
					"bill_no" 	   => $item_camp['bill_no'] != null ? $item_camp['bill_no'] : '-',
					"name"         => $item_camp['added_by_name'],
					"added_date"   => date('d-m-Y h:i A', strtotime($item_camp['added_date']))
				);
				
        }
        
        
        $data=array_merge($logs_data,$check_in,$camp_order);
        
        $date_time = array();
        foreach ($data as $key => $row)
        {
            $date_time[$key] = $row['added_date'];
        }
        array_multisort($date_time, SORT_ASC, $data);
        return $data;
    }
	
	
	    
    public function count_completed_samman_samaroh(){
        $current_day    = date('Y-m-d');
        $query_staff    = $this->db->query("SELECT b.id FROM samman_samaroh as b INNER JOIN check_in_out as co ON co.samman_id=b.id  WHERE co.check_out_time IS NOT NULL");
        return $query_staff->num_rows();
    }
    
    /* Tarique Start */
	
    public function get_warehouse(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";
        
        //$start= page_number(20);
        
		if(!empty($search_value)){
		   $keyword=$search_value;  
		   $keyword_filter = " AND (name like '%" . $keyword . "%')";
           $total_count = $this->db->query("SELECT id FROM oc_warehouse WHERE (id<>'') and is_delete='0' $keyword_filter ORDER BY id desc")->num_rows();
           $query = $this->db->query("SELECT * FROM oc_warehouse WHERE (id<>'') and is_delete='0' $keyword_filter ORDER BY id desc LIMIT $start, $length");
        }
        else{
          $total_count = $this->db->query("SELECT id FROM oc_warehouse WHERE (id<>'') and is_delete='0' $keyword_filter ORDER BY id desc")->num_rows();
          $query = $this->db->query("SELECT * FROM oc_warehouse WHERE (id<>'') and is_delete='0' $keyword_filter ORDER BY id desc LIMIT $start, $length");
        }

        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $id = $item['id'];
              $url=base_url().'admin/warehouse/edit/'.$item['id'];
              $delete_url="confirm_modal('".base_url()."admin/warehouse/delete/".$id."','Confirm Delete')";
              $action='
              <a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
              <a href="#" onclick="'.$delete_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a> 
              ';
              
                $data[] = array(
                    "id"     =>  $item['id'],
                    "sr"     =>  ++$start,
                    "name"   =>  $item['name'],
                    "created_at" =>date("d M, Y", strtotime($item['added_date'])),
                    "action" => $action,
                );
            }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
    
    public function get_warehouse_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('oc_warehouse');
    }
    
    public function add_warehouse(){
        $url = base_url('admin/warehouse');
        
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('warehouse_added_successfully'),
            "url" =>$url,
        );
        
        $name = trim($this->input->post('name'));
        $check_duplicate = $this->db->query("SELECT id FROM oc_warehouse WHERE (id<>'') and is_delete='0' and name='$name' limit 1")->num_rows();
        if($check_duplicate > 0){
            $resultpost = array(
                "status" => 400,
                "message" => 'Warehouse Name Already Exists !!!',
            );
        }else{
            $data['name']        = html_escape($this->input->post('name'));
            $data['states_id']        = html_escape($this->input->post('states_id'));
            $data['added_date']  = date("Y-m-d H:i:s");
            $this->db->insert('oc_warehouse', $data);
            $this->session->set_flashdata('flash_message', get_phrase('added_warehouse_successfully'));
        }
        
        return simple_json_output($resultpost);
    }
    
    public function edit_warehouse($id){
        $url = base_url('admin/warehouse');
        
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('updated_warehouse_successfully'),
            "url" =>$url,
        );
        
        $name = trim($this->input->post('name'));
        $check_duplicate = $this->db->query("SELECT id FROM oc_warehouse WHERE (id<>'') and is_delete='0' and name='$name' and id!='$id' limit 1")->num_rows();
        if($check_duplicate > 0){
            $resultpost = array(
                "status" => 400,
                "message" => 'Warehouse Name Already Exists !!!',
            );
        }else{
            $data['name']        = $name = html_escape($this->input->post('name'));
            $data['states_id']        = html_escape($this->input->post('states_id'));
            $this->db->where('id', $id);
            $this->db->update('oc_warehouse', $data);
            $this->session->set_flashdata('flash_message', get_phrase('updated_warehouse_successfully'));
        }
        
        return simple_json_output($resultpost);
    }
    
    
    public function delete_warehouse($id)
    {
        $data['is_delete'] = '1';
        $this->db->where('id', $id);
        $this->db->update('oc_warehouse',$data);
        return true;
    }
    
     /* Tarique End */
     
      public function get_dss_doctors(){  
        $current_date = date('Y-m-d');
        $user_id = $this->session->userdata('super_user_id');
        $dss_date = $_REQUEST['dss_date'];
        $venue = $_REQUEST['venue'];
        
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter= ""; 
        $venue_filter= ""; 
        $dss_date_filter= ""; 
        
              
        if(isset($dss_date) && $dss_date != ""):
            $dss_date_filter = " AND (DATE(dss.date)='$dss_date')";   
        else:
            $dss_date_filter = " ";     
        endif;  
        
        if(isset($venue) && $venue != ""):
            $venue_filter = " AND (dss.id='$venue')";   
        else:
            $venue_filter = " ";     
        endif;
     
		if(!empty($search_value)){
		   $keyword=$search_value;
		   $keyword_filter =" AND (doc.name like '%" . $keyword . "%' 
		   OR doc.phone like '%" . $keyword . "%' 
		   OR dss.venue like '%" . $keyword . "%')"; 
  
          $total_count = $this->db->query("SELECT doc.id FROM samman_samaroh_doctors as doc 
          INNER JOIN samman_samaroh as dss ON doc.samman_id = dss.id WHERE dss.approval_status='1' $dss_date_filter $venue_filter $keyword_filter  ORDER BY id DESC")->num_rows(); 
         
          $query = $this->db->query("SELECT doc.id,doc.name,doc.phone,doc.degree,doc.dob,doc.anniversary,dss.date,dss.venue FROM samman_samaroh_doctors as doc 
          INNER JOIN samman_samaroh as dss ON doc.samman_id = dss.id WHERE dss.approval_status='1'  $dss_date_filter $venue_filter $keyword_filter ORDER BY doc.id DESC LIMIT $start,$length");                    
     
        }
        else{
          $total_count = $this->db->query("SELECT doc.id FROM samman_samaroh_doctors as doc 
          INNER JOIN samman_samaroh as dss ON doc.samman_id = dss.id WHERE dss.approval_status='1'  $dss_date_filter $venue_filter  ORDER BY doc.id DESC")->num_rows(); 
          
          $query = $this->db->query("SELECT doc.id,doc.name,doc.phone,doc.degree,doc.dob,doc.anniversary,dss.date,dss.venue FROM samman_samaroh_doctors as doc 
          INNER JOIN samman_samaroh as dss ON doc.samman_id = dss.id WHERE dss.approval_status='1'  $dss_date_filter $venue_filter ORDER BY doc.id DESC LIMIT $start,$length");          
        }
        
        if (!empty($query)) {
              foreach ($query->result_array() as $item) { 
              $id=$item['id'];
              $phone=$item['phone'];
			 
			  $sql = $this->db->query("SELECT id,last_date,sale_value FROM doctor WHERE is_pure='1' AND phone='$phone' LIMIT 1");          
              if($sql->num_rows()>0){
                $doctor=$sql->row();
                $diff=total_days($doctor->last_date);
                if($doctor->sale_value=='0'){ $doctor_type='NNP'; }
                if($diff>=90 && $doctor->sale_value>'0'){ $doctor_type='NP'; } 
                if($diff<=90 && $doctor->sale_value>'0'){ $doctor_type='Regular'; }
                $doctor_type_label='<span class="badge badge badge-primary badge-pill">'.$doctor_type.'</span>';
              }  
              else{
                $doctor_type='NEW';
                $doctor_type_label='<span class="badge badge badge-success badge-pill">'.$doctor_type.'</span>';  
              }
			
             $dss_date=date("d-M-Y", strtotime($item['date']));
             $data[] = array(
                "venue" =>  $item['venue'].'<br>'.$dss_date,
                "name" => $item['name'].'<br>'. $item['phone'],
                "degree" =>  $item['degree'], 
				"birthday"    => $item['dob'],
				"anniversary" => $item['anniversary'],
                "doctor_type" =>  $doctor_type_label,
             );
           }
        }
      
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
   }   
   
    public function add_checkout($id){	
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('checkout_added_successfully!'),
            "url" => $this->session->userdata('previous_url'),
        ); 
        
        $check = $this->db->query("SELECT id,check_type,latitude,longitude,check_in_time,check_in_stamp,location,check_out_time,checkout_location,checkout_latitude,checkout_longitude,distance FROM check_in_out WHERE id='$id' LIMIT 1");
        
        if($check->num_rows()>0){
          $item = $check->row_array();
          if($item['check_out_time']==NULL || $item['check_out_time']==''){
              $data=array();   
              $data['check_out_time']     = $item['check_in_time'];
              $data['checkout_location']  = $item['location'];
              $data['checkout_latitude']  = $item['latitude'];
              $data['checkout_longitude'] = $item['longitude'];
              $data['distance'] = 0;
              $this->db->where('id', $id);
              $this->db->update('check_in_out',$data);
              
              
                 
            $data['added_by_id']   = $this->session->userdata('super_user_id');
            $data['added_by_name'] = $this->session->userdata('super_name');
        		
        	$update_data = array();
        	$update_data = array(
        		'order_id' => $id,
        		'function_name' => 'admin_add_checkout',
        		'json_request' => json_encode($data),
        		'json_data' => json_encode($item),
        		'remark' => $this->session->userdata('super_user_id'),
        		'created_date' => date("Y-m-d H:i:s"),
        	);
        	$this->db->insert('cronjob_track', $update_data);  
              
          }
          else{
          $resultpost = array(
              'status' => 400,
              'message' => "Already Checkout!"
          );
          }
        
        }
        else{ 
        $resultpost = array(
              'status' => 400,
              'message' => "No Checkout Found!"
          );
        }
      return simple_json_output($resultpost); 
    }
    
    
    public function remove_checkout($id){	
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('checkout_removed_successfully!'),
            "url" => $this->session->userdata('previous_url'),
        ); 
        
        $check = $this->db->query("SELECT id,check_type,latitude,longitude,check_in_time,check_in_stamp,location,check_out_time,checkout_location,checkout_latitude,checkout_longitude,distance FROM check_in_out WHERE id='$id' LIMIT 1");
        
        if($check->num_rows()>0){
         $item = $check->row_array();
         if($item['check_out_time']!=NULL && $item['check_out_time']!=''){
            $data=array();   
            $data['check_out_time']     = NULL;
            $data['checkout_location']  = NULL;
            $data['checkout_latitude']  = NULL;
            $data['checkout_longitude'] = NULL;
            $data['distance'] = NULL;
            $this->db->where('id', $id);
            $this->db->update('check_in_out',$data);
              
            $data['added_by_id']   = $this->session->userdata('super_user_id');
            $data['added_by_name'] = $this->session->userdata('super_name');
        		
        	$update_data = array();
        	$update_data = array(
        		'order_id' => $id,
        		'function_name' => 'admin_remove_checkout',
        		'json_request' => json_encode($data),
        		'json_data' => json_encode($item),
        		'remark' => $this->session->userdata('super_user_id'),
        		'created_date' => date("Y-m-d H:i:s"),
        	);
        	$this->db->insert('cronjob_track', $update_data);
              
          }
          else{
           $resultpost = array(
              'status' => 400,
              'message' => "No Checkout Data Found!"
           );
          }
        }
        else{ 
          $resultpost = array(
              'status' => 400,
              'message' => "No Data Found!"
          );
            
        }
      return simple_json_output($resultpost); 
    }
    
    public function get_access_list(){
        $resultdata=array();
        $query = $this->db->query("SELECT id, name, page_name FROM `oc_access_list` ORDER BY sort asc");
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $item['name'],
                 "page_name" => $item['page_name'],
              );
        
            }
        }
      return $resultdata;
    }
    
     public function get_doctor_report(){ 
        $user_id=$this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $total_count=0;
        $order_dr_unpure_filter="";
        $order_date_filter="";
       	$filter_data['date_range']  = $_REQUEST['date_range'];
         
         if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
          $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  date('Y-m-d',strtotime($order_date[1])); 
          $order_date_filter =" AND (DATE(pure_date) BETWEEN '$from' AND '$to')"; 
          $order_dr_unpure_filter =" AND (DATE(date_added) BETWEEN '$from' AND '$to')"; 
         endif;	 
 	
         
        if ($order_date_filter!="") {
         $total_count=6;
         $total_regular_120=$total_regular_240=$total_regular_240_plus=$total_only_samples=$total_unpure=0;  
         
         $url=base_url().'admin/doctors?type=regular120&date_range='.$filter_data['date_range'];
         $action='<a href="'.$url.'" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
         
         $total_regular_120 = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)>=0 AND DATEDIFF(CURRENT_DATE,last_date)<=120) AND sale_value>'50' $order_date_filter")->num_rows();		
         $data[] = array(
            "count" => $total_regular_120,
            "name" => 'Regular Doctors (0 to 120 Days)',  
            "query" => $this->db->last_query(),
            "action" => $action,
         );
		 
		 $url=base_url().'admin/doctors?type=regular240&date_range='.$filter_data['date_range'];
         $action='<a href="'.$url.'" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
         
		 $total_regular_240 = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)>=121 AND DATEDIFF(CURRENT_DATE,last_date)<=240) AND sale_value>'50' $order_date_filter")->num_rows();		
         $data[] = array(
            "count" => $total_regular_240,
            "name" => 'Regular Doctors (121 to 240 Days)', 
            "query" => $this->db->last_query(), 
            "action" => $action,  
         );
		
		
		 $url=base_url().'admin/doctors?type=regular241&date_range='.$filter_data['date_range'];
         $action='<a href="'.$url.'" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
         
		 $total_regular_240_plus = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND (DATEDIFF(CURRENT_DATE,last_date)>=241) AND sale_value>'50' $order_date_filter")->num_rows();		
         $data[] = array(
            "count" => $total_regular_240_plus,
            "name" => 'Regular Doctors (241 & Above)', 
            "query" => $this->db->last_query(), 
            "action" => $action,  
         );
         
		 $url=base_url().'admin/doctors?type=samples&date_range='.$filter_data['date_range'];
         $action='<a href="'.$url.'" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
         
		 $total_only_samples = $this->db->query("SELECT id FROM doctor WHERE is_pure='1' AND sale_value<='50' $order_date_filter")->num_rows();		
         $data[] = array(
            "count" => $total_only_samples,
            "name" => 'Only Samples Doctors',  
            "query" => $this->db->last_query(),
            "action" => $action,  
         );
		  
		 $url=base_url().'admin/doctors?type=unpure&date_range='.$filter_data['date_range'];
         $action='<a href="'.$url.'" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
         
		 $total_unpure = $this->db->query("SELECT id FROM doctor WHERE is_pure='0' $order_dr_unpure_filter")->num_rows();		
         $data[] = array(
            "count" => $total_unpure,
            "name" => 'No Sample / No Sale Doctors',   
            "query" => $this->db->last_query(),
            "action" => $action, 
         );
       
     }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
    
 
    /*CRM-2.0 STARTS*/  
     public function get_transfer_co_by_coordinator($co_id){
        $coord = $this->common_model->getRowById('sys_users','state_id',array('id'=>$co_id));
        $added_by_id=$co_id;
        $state_id=$coord['state_id'];
        $resultdata=array();
        $query = $this->db->query("SELECT id, first_name, last_name FROM `sys_users` WHERE id!='$added_by_id' AND status='1' AND is_deleted='0' AND (type='Coordinator')");
       
        
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
              $user_name = $item['first_name'].' '.$item['last_name'];
               $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $user_name,
              ); 
           }
        }
        
      return $resultdata;
    }
    
    public function get_transfer_dss_by_coordinator(){
		$co_id = $_REQUEST['co_id'];
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		$search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $keyword_filter="";

         if (isset($search_value) && $search_value != ""):
            $keyword        = $search_value;
            $keyword_filter = " AND (state_name like '%".$keyword."%'  
            OR venue like '%" . $keyword . "%'
            OR asm_name like '%" . $keyword . "%')";
        endif;
        

	     $total_count = $this->db->query("SELECT id FROM samman_samaroh WHERE added_by_id='$co_id' $keyword_filter ORDER BY id desc")->num_rows();
	     $query = $this->db->query("SELECT id,asm_name,venue,state_name,district_name,date FROM samman_samaroh WHERE added_by_id='$co_id' $keyword_filter ORDER BY id desc  LIMIT $start,$length");

      
        if (!empty($query) && $co_id!='') {
          foreach ($query->result_array() as $item) {
              
             $select="";
             $select='<div class="checkbox checkbox-primary text-center"><input type="checkbox" class="dss_id" id="order_'.$item['id'].'" name="dss_id[]" value="'. $item['id'].'" onclick="getCount()" ><label for="order_'.$item['id'].'">&nbsp;</label></div>';
             
             
             $data[] = array(
                "id" => $select,
                "venue" => $item['venue'],
                "date"  => date("d M, Y", strtotime($item['date'])),
                "state_name" => $item['state_name'],
                "district_name" => $item['district_name'],
                "asm_name" => $item['asm_name'],
            );
           }
         }
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
    
     public function transfer_dss_data(){   
        $resultpost = array(
            "status" => 200,
            "message" => 'DSS Transferred Successfully!',
            "url" => base_url('admin/transfer-dss')
        ); 
        
        $from_co_id = html_escape($this->input->post('from_co_id')); 
        $new_co_id = html_escape($this->input->post('to_co_id')); 
		
        $check_list = $this->common_model->getResultById('samman_samaroh ','id',array('added_by_id'=> $from_co_id));
		
        if(count($check_list)>0){       
        $new_co_name   = $this->crud_model->get_coordinator_name_($new_co_id);
        $co_name   = $this->crud_model->get_coordinator_name_($from_co_id);
        $co_id   = $from_co_id;
        
       $order_arr  = html_escape($this->input->post('dss_id'));
	   $dss_ids=array();        
       if(count($order_arr)>0){
        foreach($order_arr as $dss_id){
		 $dss_ids[]=$dss_id;
	
		   
         $update_data = array();
         $update_data = array(
            'added_by_id' => $new_co_id,
            'added_by_name' => $new_co_name,
            'last_modified' => date("Y-m-d H:i:s"),
         );
         $this->db->where('id', $dss_id);
         $this->db->where('added_by_id', $from_co_id);
         $insert=$this->db->update('samman_samaroh', $update_data);
		}   
        
        if($insert){
         $data_logs = array();
         $data_logs = array(
			'dss_id'   => implode(",",$dss_ids),
			'co_id'       => $co_id,
			'co_name'     => $co_name,
			'new_co_id'   => $new_co_id,
			'new_co_name' => $new_co_name,
			'added_date'  => date("Y-m-d H:i:s"),
        );
        $this->db->insert('oc_dss_transfer_logs', $data_logs);
       }
    	
       $this->session->set_flashdata('flash_message', "DSS Transferred Successfully!");
      }
      else{
         $resultpost = array(
            "status" => 400,
            "message" => 'Alert! Select atleast one DSS to transfer!',
        );   
      }   
    }
    else{
         $resultpost = array(
            "status" => 400,
            "message" => 'Alert, No DSS Available to transfer!'
        );   
      }      
      return simple_json_output($resultpost); 
    }
    /*CRM-2.0 ENDS*/  
    
}
