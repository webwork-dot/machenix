<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Attendance_model extends CI_Model
{
    
    function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }
	
	public function check_month_validation($punch_date, $month_id, $year) {
		$count = 0;
		$punch_month = date("n", strtotime($punch_date));
		$punch_year = date("Y", strtotime($punch_date));

		if ($punch_month != $month_id || $punch_year != $year) {
			$count = 1;
		}
		return $count;
	}
	
   
	public function check_attendance($emp_id,$punch_date) {
      $count=0;
      $sql = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND DATE(punch_date)='$punch_date' LIMIT 1");
      $count=$sql->row()->id;
      return $count;
    } 
		
    function import_emp_attendance_excel_insert($fetchData,$month_id,$year){
	    $curr_data=date("Y-m-d H:i:s");
	    $count=0;
	    $Images_arr=array();
	    $returnData=array();
				 
        $data_arr = array();
        $data_arr = array(
            "slot_no" 		=> 0,
            "month" 		=> $month_id,
            "year"		    => $year,
            "created_at"    => $curr_data,
            "added_by_id"   => $this->session->userdata('super_user_id'),
            "added_by_name" => $this->session->userdata('super_name')
        );
        $this->db->insert('emp_attendance_logs', $data_arr);
        $parent_id = $this->db->insert_id();
        
    	$total_data=$insert_data=$update_data=0;
        foreach($fetchData as $item){
		   $punch_date=date("Y-m-d", strtotime($item['punch_date']));
		   if($item['check_in_date']!='' && strtotime($item['check_in_date']) !== false){
		    $check_in_date_=$punch_date.' '.$item['check_in_date'];
			$check_in_date=date("Y-m-d H:i:s", strtotime($check_in_date_));
		   }
		   else{
			$check_in_date=NULL;   
		   }

		   if($item['check_out_date']!='' && strtotime($item['check_out_date']) !== false){
		    $check_out_date_=$punch_date.' '.$item['check_out_date'];
			$check_out_date=date("Y-m-d H:i:s", strtotime($check_out_date_));
		   }
		   else{
			$check_out_date=NULL;   
		   }
		   
		   $emp_id=$item['emp_id'];
		   
		   $emp=$this->common_model->getRowById('candidate','shift_type,staff_catid,salary_type,staff_typeid,staff_type',array('emp_id'=>$emp_id));	
		   $data=array();
           $data['shift_type']	  = $emp['shift_type'];
           $data['staff_catid']	  = $emp['staff_catid'];
           $data['salary_type']	  = $emp['salary_type'];
           $data['staff_typeid']  = $emp['staff_typeid'];
           $data['staff_type']	  = $emp['staff_type'];
           $data['logs_id']		  = $parent_id;
           $data['punch_date']	  = $punch_date;
           $data['emp_id']		  = $emp_id;
           $data['name']		  = trim($item['name']);
           $data['check_in_date'] = $check_in_date;
           $data['check_out_date']= $check_out_date;
           $data['total_hrs']	  = trim($item['total_hrs']);
           $data['status']	  	  = '';
           $data['rule_check']	  = 0;
           $data['is_generated']  = 0;
           $data['generated_date']= NULL;
           $data['is_hold']       = 0;
           $data['hold_date']     = NULL;
         
           $count_id=$this->check_attendance($emp_id,$punch_date);  
           $total_data=$total_data+1;
		   

           if($count_id>0){   
              $update_data=$update_data+1;   
              $data['updated_at']  	   = $curr_data;  
			  $data['updated_by_id']   = $this->session->userdata('super_user_id');
			  $data['updated_by_name'] = $this->session->userdata('super_name');   
			  $this->db->where('id', $count_id);
			  $this->db->update('emp_attendance', $data);   
           }
           else{
              $insert_data=$insert_data+1;
			  $data['added_by_id']   = $this->session->userdata('super_user_id');
			  $data['added_by_name'] = $this->session->userdata('super_name');
              $data['created_at']  = $curr_data;
              $this->db->insert('emp_attendance', $data);
           }  
    	}
    	
		
		//delete if generated salary starts
		$uniqueEmpIds = array();
		foreach ($fetchData as $entry) {
			$emp_id = $entry['emp_id'];
			$uniqueEmpIds[$emp_id] = true;
		}

		$uniqueEmpIdsArray = array_keys($uniqueEmpIds);

		foreach($uniqueEmpIdsArray as $emp_id) {
			$this->db->where('emp_id', $emp_id);
			$this->db->where('year', $year);
			$this->db->where('month', $month_id);
			$this->db->delete('emp_generated_salary');
			
			$data_up=array();
			$data_up['is_early'] = '0';
			$this->db->where('emp_id', $emp_id);
			$this->db->where('YEAR(punch_date)', $year);
			$this->db->where('MONTH(punch_date)', $month_id);
			$this->db->update('emp_attendance',$data_up);    
			
			//paid_leave_history
			$this->db->where('emp_id', $emp_id);
			$this->db->where('year', $year);
			$this->db->where('month', $month_id);
			$this->db->delete('paid_leave_history');
			
		   //delete hold
		   	$this->db->where('emp_id', $emp_id);
			$this->db->where('year', $year);
			$this->db->where('month', $month_id);
			$this->db->where('salary_type!=', 'FIELD STAFF');
			$this->db->delete('emp_hold_salary');
			
			 //update advance
			$data_advance=array();
			$data_advance['status'] = 'ongoing';
			$this->db->where('emp_id', $emp_id);
			$this->db->where('year', $year);
			$this->db->where('month', $month_id);
			$this->db->where('status', 'paid');
			$this->db->update('advance',$data_advance);    
			
			 //update adjustment
			$data_adjustment=array();
			$data_adjustment['status'] = 'ongoing';
			$this->db->where('emp_id', $emp_id);
			$this->db->where('year', $year);
			$this->db->where('month', $month_id);
			$this->db->where('status', 'paid');
			$this->db->update('emp_adjustment',$data_adjustment);  
			
			 //update tds
			$data_tds=array();
			$data_tds['status'] = 'ongoing';
			$this->db->where('emp_id', $emp_id);
			$this->db->where('year', $year);
			$this->db->where('month', $month_id);
			$this->db->where('status', 'paid');
			$this->db->update('emp_tds',$data_tds);  
			
			 //update pl
			$data_paidleave=array();
			$data_paidleave['status'] = 'ongoing';
			$this->db->where('emp_id', $emp_id);
			$this->db->where('year', $year);
			$this->db->where('month', $month_id);
			$this->db->where('status', 'completed');
			$this->db->update('emp_paidleave',$data_paidleave);  
									
			//loans_history
			$gmobile = $this->db->query("SELECT a.id,IFNULL(SUM(r.amount), 0) AS total_loan FROM loan_repayments AS r INNER JOIN loans as a WHERE a.is_deleted='0' AND a.status='ongoing' AND r.loan_type='mobile_loan' AND r.emp_id='$emp_id' AND r.year='$year' AND r.month='$month_id'")->row;	
			
			$mobile_total_loan=price_format_decimal($gmobile->total_loan);				
			$update_mobile_loan = $this->db->query("UPDATE loans SET amount_paid=CAST((amount_paid-$mobile_total_loan) as decimal(10,2)) WHERE is_deleted='0' AND status='ongoing' AND loan_type='mobile_loan' AND emp_id='$emp_id' AND id='$gmobile->id'");   
			
			$gcash = $this->db->query("SELECT IFNULL(SUM(r.amount), 0) AS total_loan FROM loan_repayments AS r INNER JOIN loans as a WHERE a.is_deleted='0' AND a.status='ongoing' AND r.loan_type='cash_loan' AND r.emp_id='$emp_id' AND r.year='$year' AND r.month='$month_id'")->row();	
			$cash_total_loan=price_format_decimal($gcash->total_loan);				
			$update_cash_loan = $this->db->query("UPDATE loans SET amount_paid=CAST((amount_paid-$cash_total_loan) as decimal(10,2)) WHERE is_deleted='0' AND status='ongoing' AND loan_type='cash_loan' AND emp_id='$emp_id' AND id='$gcash->id'");  
			
			$check_loan = $this->db->query("SELECT l.id FROM loan_repayments as lr INNER JOIN loans as l ON l.id = lr.loan_id WHERE l.is_deleted='0' AND l.status = 'ongoing' AND lr.emp_id = '$emp_id' AND lr.year = '$year' AND lr.month = '$month_id'")->num_rows(); 

            if($check_loan>0){
				$delete_loans = $this->db->query("DELETE lr
				FROM loan_repayments AS lr
				WHERE lr.emp_id = '$emp_id'
				AND lr.year = '$year'
				AND lr.month = '$month_id'
				AND EXISTS (
					SELECT 1
					FROM loans AS l
					WHERE l.id = lr.loan_id
					AND l.is_deleted='0' AND  l.status = 'ongoing')");
			}


			$this->update_attendance_status($month_id,$year,$emp_id,$emp['staff_catid']);		
		}
		//delete if generated salary ends
	
    	
        $update_data_logs = array();
        $update_data_logs = array(
            'slot_no' => 'SLOT_'.sprintf('%04d',$parent_id),
            'total_data' => $total_data,
            'update_data' => $update_data,
            'insert_data' => $insert_data,
        );
       $this->db->where('id', $parent_id);
       $this->db->update('emp_attendance_logs', $update_data_logs); 
       return $returnData;
	}	
	
	public function rule_check_attendance() {
      $count=0;
      $sql = $this->db->query("SELECT id FROM emp_attendance WHERE rule_check='0' AND DATE(punch_date)='$punch_date' LIMIT 1");
      $count=$sql->row()->id;
      return $count;
    } 	
	
	public function check_emp_id($emp_id) {
      $count=0;
      $sql = $this->db->query("SELECT id FROM candidate WHERE emp_id='$emp_id' AND shift_type!='' LIMIT 1");
      $count=$sql->num_rows();
      return $count;
    } 	
	
	
 	public function get_attendance_list(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		
	    $status_list=$this->common_model->getResultById('emp_attn_status','name',array('status'=>1));		

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['month_id']    = $_REQUEST['month_id'];
       	$filter_data['year'] 	    = $_REQUEST['year'];
        $emp_id   					= $_REQUEST['emp_id'];
        $filter_data['salary_type'] = $_REQUEST['salary_type'];
        
        if(isset($filter_data['month_id']) && $filter_data['month_id']!="") :
          $month_id=$filter_data['month_id'];
          $year=$filter_data['year'];
		  $selDate=$year.'-'.$month_id.'-01';
		  $from = date('Y-m-d', strtotime($selDate));
		  $to = date('Y-m-t', strtotime($selDate));	
          $sql_filter .=" AND (DATE(punch_date) BETWEEN '$from' AND '$to')"; 
        endif;  
            
		
		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];
            $sql_filter .= " AND (salary_type='$salary_type')";
        endif;
		
       $total_count=0;
	   if($filter_data['month_id']!='' && $emp_id!=''){
		$total_count = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' $sql_filter ORDER BY id desc")->num_rows();
     
        $query = $this->db->query("SELECT id,emp_id,shift_type,salary_type,name,punch_date,check_in_date,check_out_date,total_hrs,status FROM emp_attendance WHERE emp_id='$emp_id'  $sql_filter ORDER BY DATE(punch_date) ASC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id'];  
			   $punch_date=($item['punch_date']!='' ? date("d M, Y", strtotime($item['punch_date'])):'-');
			   $shift_type=$item['shift_type'];
			   $emp_id=$item['emp_id'];
			   
			   $action='';
			   $action .='
			   <input type="hidden" class="form-control" name="id[]" value="'.$id.'" readonly>
			   <select class="form-control" name="status[]" required>';
			   
			   $action .='<option value="">Select</option>';
			   foreach($status_list as $status){	
                 $is_selected = ($status['name'] == $item['status']) ? 'selected':'';			   
			     $action .='<option value="'.$status['name'].'" '.$is_selected.' >'.$status['name'].'</option>';
			   }
			   $action .='</select>';
			   
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],                   
                    "emp_id"		=> $item['emp_id'], 
                    "shift_type"	=> $item['shift_type'], 
                    "salary_type"	=> $item['salary_type'], 
					"name"         	=> $item['name'],      
				    "punch_date"    => $punch_date,
				    "day"    		=> date("l", strtotime($item['punch_date'])),
				    "check_in_date" => ($item['check_in_date']!='' ? date("h:i A", strtotime($item['check_in_date'])):'-'),
				    "check_out_date"=> ($item['check_out_date']!='' ? date("h:i A", strtotime($item['check_out_date'])):'-'),
                    "total_hrs"     => $item['total_hrs'],         
                    "status"     	=> $item['status'],         
                    "action"     	=> $action,         
                );
            }
         }
		 
		
			$statuses = array_column($data, 'status');

			// Use array_count_values() to get the count of each status
			$statusCounts = array_count_values($statuses); 
	
		 
		}
		
		$shift_type_=$this->common_model->getNameByIdArr('emp_shift_Type','name',array("value"=>$shift_type));
   
		
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data,
            "shift_type" => $shift_type_,
            "Present" => $statusCounts['Present'] ?? 0,
            "Absent" => $statusCounts['Absent'] ?? 0,
            "Late" => $statusCounts['Late'] ?? 0,
            "Half Day" => $statusCounts['Half Day'] ?? 0,
            "Weekly Off" => $statusCounts['Weekly Off'] ?? 0,
            "Holiday" => $statusCounts['Holiday'] ?? 0,
        );
        echo json_encode($json_data);
    }
		
	public function get_salary_report_list(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start  = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['month_id']    = $_REQUEST['month_id'];
       	$filter_data['year']   		= $_REQUEST['year'];
        $filter_data['salary_type'] = $_REQUEST['salary_type'];
        $filter_data['keywords']	= $_REQUEST['keywords'];
        
        if(isset($filter_data['month_id']) && $filter_data['month_id']!="") :
          $month_id=$filter_data['month_id'];
          $year=$filter_data['year'];
		  $selDate=$year.'-'.$month_id.'-01';
		  $from = date('Y-m-d', strtotime($selDate));
		  $to = date('Y-m-t', strtotime($selDate));	
          $sql_filter .=" AND (DATE(a.punch_date) BETWEEN '$from' AND '$to')"; 
        endif;           
		
		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];
            $sql_filter .= " AND (a.staff_typeid='$salary_type')";
        endif;
		
		
		if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
          $keyword=$filter_data['keywords'];
          $sql_filter .=" AND (e.name like '%".$keyword."%'  
            OR e.emp_id = '$keyword'
            OR e.phone like '%" . $keyword . "%')"; 
        endif;           
		
       $total_count=0;
	   if($filter_data['month_id']!=''){
		$total_count = $this->db->query("SELECT
			e.id
		FROM
			emp_attendance a
		INNER JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL AND a.shift_type IS NOT NULL
		AND a.is_generated=0
		AND a.is_hold=0
		AND e.is_left=0
		AND e.status=1
		$sql_filter
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC")->num_rows();
         		
		$query = $this->db->query("SELECT
			a.id,
			a.punch_date,
			e.name,
			e.gender,
			e.emp_id,
			e.salary,
			e.basic_salary,
			e.hra,
			e.gross_edu,
			e.is_pf,
			e.is_esic,
			e.is_tds,
			e.is_ptax,
			e.is_esic,
			e.is_tds,
			COUNT(DISTINCT DATE_FORMAT(a.punch_date, '%Y-%m-%d')) AS total_days,
			SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS days_present,
			SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS days_absent,
			SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS days_late,
			SUM(CASE WHEN a.status = 'Half Day' THEN 1 ELSE 0 END) AS days_half
		FROM
			emp_attendance a

		INNER JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL AND a.shift_type IS NOT NULL
		AND a.is_generated=0
		AND a.is_hold=0	
		AND e.is_left=0
		AND e.status=1
		$sql_filter
		GROUP BY 
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC LIMIT $start, $length");	
		//echo $this->db->last_query();exit();
		
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id']; 
               $gender=$item['gender']; 
               $punch_date=$item['punch_date']; 
               $is_pf=$item['is_pf']; 
               $is_esic=$item['is_esic']; 
               $is_tds=$item['is_tds']; 
               $is_ptax=$item['is_ptax']; 
               $emp_id=$item['emp_id']; 
			   $month_id      = date('n',strtotime($punch_date));
			   $year          = date('Y',strtotime($punch_date));
			   $staff_name	  = $item['name'].' #'.$item['emp_id'];
			   
			   $paid_leave=$basic_salary=$hra=$gross_edu=$gross_package=$gross_salary_earned=0;$loans_advances=$mobile_loan=$mobile_loan_instl=$mobile_loan_instl_amt=0;  
               $adjustment=$tds=$pf=$p_tax=$esic=$loan_deduction=$total_deduction=0;  
               $balance_loan_cf=$mobile_loan_cf=$net_salary_after_adj=$final_salary=0;
			   
			   $day_of_month=$present_day=$absent_day=$late_day=$half_day=$sys_total_days=0; 
			   //$day_of_month=$item['total_days']; 
			   $sys_total_days=$item['total_days']; 
			   $day_of_month = get_days_in_month($month_id, $year);
			  // echo $day_of_month;exit();
			   
			   if($sys_total_days!=$day_of_month){
                 $days_absent=$item['days_absent']+($day_of_month-$sys_total_days);
			   }
			   else{
                 $days_absent=$item['days_absent'];				   
			   }
			   
               $days_present=$item['days_present'];  
               $late_day=$item['days_late'];
               $half_day=$item['days_half']; 
			   
               $pl_paidleave=$this->get_set_paidleave($emp_id,$month_id,$year);  
			   
			   $cal_absent=$total_absent=0;
			   $cal_absent=$this->calculateLateAndHalfDays($late_day,$half_day);
			   $absent_day=$days_absent+$cal_absent;
			   $present_day=$day_of_month+$pl_paidleave-$absent_day;
			   if($present_day>$day_of_month){ $present_day=$day_of_month; }
			  
               $paid_leave=0;  
               $basic_salary=round_int($item['basic_salary']);   
               $hra=round_int($item['hra']);                
               $gross_edu=round_int($item['gross_edu']);   
               $gross_package=round_int($item['salary']);   
			   
			   
               $gross_salary_earned=gross_salary_earned($gross_package,$day_of_month,$present_day);  
			   
               $loans_advances=$mobile_loan=$balance_cash_loan=$balance_mobile_loan=0; 
			   
			   $loans_details=$this->get_loans_details($emp_id,$month_id,$year);			   
               $loans_advances=$loans_details['loans_advances'];  
               $balance_cash_loan=$loans_details['balance_cash_loan'];  
			   
               $mobile_loan=$loans_details['mobile_loan'];  
               $balance_mobile_loan=$loans_details['balance_mobile_loan'];  
			    
			   
               $advance_amt=$this->get_advance_amount($emp_id,$month_id,$year);  
               $adjustment_amt=$this->get_adjustment_amount($emp_id,$month_id,$year);
               $tds_amt=$this->get_tds_amount($emp_id,$month_id,$year);
			   
               $mobile_loan_instl=0;  
               $mobile_loan_instl_amt=0;  
                         
               $pf=0;  
               $p_tax=0;  
               $esic=0;  			   
			   		 	   
			   $new_basic_salary = get_salary_per($gross_salary_earned,50);   
               $new_hra = get_salary_per($gross_salary_earned,25);                
               $new_gross_edu = get_salary_per($gross_salary_earned,25);   
			   
			   if($is_ptax==1){
			      $p_tax=calculate_ptax($punch_date,$gross_salary_earned,$gender);		
			   }
				
				if($is_pf==1){
				 $pf=calculate_pf($new_basic_salary, $new_gross_edu);
			    }
				
				if($is_esic==1){
				  $esic=calculate_esic($new_basic_salary, $new_hra, $new_gross_edu);
				}
			   			     
               $total_deduction=0;    
			   
			   $check_pl=$this->common_model->getCountsById('paid_leave_history',array('emp_id'=>$emp_id,'year'=>$year));			  
				
				$isReadonly = ($pl_paidleave > 0) ? 'readonly' : ''; 
			   if($check_pl>0){  
				 $paid_leave_history_url = "showAjaxModal('".site_url('modal/popup/modal_paid_leave_history/'.$emp_id)."', 'Paid Leave History')";  

				 $paid_leave='<input type="text" min="0" max="30" step="any"  onkeypress="return isNumberKey(event,this)" class="form-control m-deduct" name="paid_leave" id="paid_leave_'.$id.'" data-id="'.$id.'" value="'.$pl_paidleave.'" '.$isReadonly.' required>
			   
			     <i class="feather icon-alert-circle d-inline text-danger"  onclick="'.$paid_leave_history_url.'" data-toggle="tooltip" title="Click To Check History"></i>';  				   
			   }
			   else{
				 $paid_leave='<input type="text" min="0" max="30" step="any"  onkeypress="return isNumberKey(event,this)" class="form-control m-deduct" name="paid_leave" id="paid_leave_'.$id.'" data-id="'.$id.'" value="'.$pl_paidleave.'" '.$isReadonly.' required>';  				   
			   }
			   
			   $isReadonly_adj = ($adjustment_amt > 0) ? 'readonly' : ''; 
			   $adjustment_remark = "smallAjaxModal('".site_url('modal/popup/modal_adjustment_remark/'.$emp_id.'/'.$id)."', '$staff_name - Adj Remark')"; 
               $adjustment='<input type="number" step="any" class="form-control m-deduct" name="adjustment" id="adjustment_'.$id.'" value="'.$adjustment_amt.'" data-id="'.$id.'" '.$isReadonly_adj.' required>
			    <i class="feather icon-edit d-inline text-danger"  onclick="'.$adjustment_remark.'" data-toggle="tooltip" title="Add Remark"></i>
				<input type="hidden" name="adj_remark" id="adj_remark_'.$id.'" data-id="'.$id.'">';  
			   
			   	  
			  if($balance_cash_loan>0){
				$cash_loan_history_url = "showAjaxModal('".site_url('modal/popup/modal_loans_history/'.$emp_id.'/cash_loan')."', 'Loans/Advance History')"; 
				$loan_deduction='<input type="number" step="any" min="0" class="form-control m-deduct" name="loan_deduction" id="loan_deduction_'.$id.'"  data-id="'.$id.'" value="'.$loans_details['cash_emi'].'" required> 
			    <i class="feather icon-alert-circle d-inline text-danger"  onclick="'. $cash_loan_history_url.'" data-toggle="tooltip" title="Click To Check History"></i>';  
				   
			   }
			   else{
				 $loan_deduction='<input type="hidden" name="loan_deduction" id="loan_deduction_'.$id.'"  data-id="'.$id.'" value="0">
				 N/A';  
			   }


			   if($balance_mobile_loan>0){ 
			     $mobile_loan_history_url = "showAjaxModal('".site_url('modal/popup/modal_loans_history/'.$emp_id.'/mobile_loan')."', 'Mobile Loan History')";  

				 $mobile_deduction='<input type="number" step="any" min="0" class="form-control m-deduct" name="mobile_deduction" id="mobile_deduction_'.$id.'"  data-id="'.$id.'" value="'.$loans_details['mobile_emi'].'" required>
			   
			     <i class="feather icon-alert-circle d-inline text-danger"  onclick="'.$mobile_loan_history_url.'" data-toggle="tooltip" title="Click To Check History"></i>';   
				   
			   }
			   else{
				 $mobile_deduction='<input type="hidden" name="mobile_deduction" id="mobile_deduction_'.$id.'"  data-id="'.$id.'" value="0">
				 N/A';  				   
			   }
			      	 
			   
			   if($is_tds==1){ 
				 $isReadonly_tds = ($tds_amt > 0) ? 'readonly' : ''; 
                 $tds='<input type="number" step="any" min="0" class="form-control m-deduct" name="tds" id="tds_'.$id.'" value="'.$tds_amt.'" data-id="'.$id.'"  '.$isReadonly_tds.' required>';   
			   }
			   else{
				   $tds='<input type="hidden" name="tds" id="tds_'.$id.'" data-id="'.$id.'" value="0">
				   N/A';
			   }

			   
               $net_salary_after_adj=0;  
               $final_salary=0;  
			   
			   	
			  $total_deduction=$final_salary=0;
			  $total_deduction=round_int($pf + $p_tax + $esic + $tds_amt + $loans_details['cash_emi'] + $loans_details['mobile_emi']);
			  $final_salary=round_int($gross_salary_earned-$total_deduction+$adjustment_amt-$advance_amt); 
			   
			   $absent_day_html='<span id="absent_'.$id.'">'.$absent_day.'</span>
			   <input type="hidden" id="absent_input_'.$id.'" value="'.$absent_day.'">';
				
			   $present_day_html='<span id="present_'.$id.'">'.$present_day.'</span>
			   <input type="hidden" id="present_input_'.$id.'" value="'.$present_day.'">';
			    			   
			   $total_deduction_html='<span id="total_deduction_'.$id.'">'.$total_deduction.'</span>';
			 
			   $gross_salary_earned_html='<span id="gross_salary_earned_'.$id.'">'.$gross_salary_earned.'</span>';
			   $pf_html='<span id="pf_'.$id.'">'.$pf.'</span>';
			   $p_tax_html='<span id="p_tax_'.$id.'">'.$p_tax.'</span>';
			   $esic_html='<span id="esic_'.$id.'">'.$esic.'</span>';
			   
			   $final_salary_html='<span id="final_salary_'.$id.'">'.$final_salary.'</span>';
	
			   
               $action='';   			         
			   $action .='<button type="button" data-id="'.$id.'" class="btn btn-primary mr-1 mb-1 btn-generate-salary" id="btn_generate_'.$id.'" data><i class="fa fa-refresh"></i> Generate Salary</button>';
			   $action .='<button type="button" data-id="'.$id.'" class="btn btn-danger mr-1 mb-1 btn-hold-salary" id="btn_hold_'.$id.'" data><i class="fa fa-pause"></i> Hold Salary</button>';
			    
			   $hidden_input='';
			   $hidden_input='
			   <input type="hidden" id="name_'.$id.'" value="'.$item['name'].'">
			   <input type="hidden" id="emp_id_'.$id.'" value="'.$emp_id.'">
			   <input type="hidden" id="is_esic_'.$id.'" value="'.$is_esic.'">
			   <input type="hidden" id="is_pf_'.$id.'" value="'.$is_pf.'">
			   <input type="hidden" id="is_ptax_'.$id.'" value="'.$is_ptax.'">
			   <input type="hidden" id="day_of_month_'.$id.'" value="'.$day_of_month.'">
			   <input type="hidden" id="gross_package_'.$id.'" value="'.$gross_package.'">
			   <input type="hidden" id="month_'.$id.'" value="'.$month_id.'">
			   <input type="hidden" id="year_'.$id.'" value="'.$year.'">
			   <input type="hidden" id="gender_'.$id.'" value="'.$gender.'">
			   <input type="hidden" id="punch_date_'.$id.'" value="'.$punch_date.'">
			   <input type="hidden" id="advance_amt_'.$id.'" value="'.$advance_amt.'">
			   ';
			
                $data[] = array(
                    "sr_no"         		=> ++$start,                      
                    "class_name"         	=> 'item-row_main'.$id,                      
                    "name"         		    => $item['name'].' #'.$item['emp_id'].'<br/>'.$hidden_input,                   
                    "day_of_month"			=> $day_of_month, 
                    "paid_leave"			=> $paid_leave, 
                    "present_day"			=> $present_day_html, 
                    "absent_day"			=> $absent_day_html, 
                    "basic_salary"			=> $basic_salary, 
                    "hra"					=> $hra, 
                    "gross_edu"				=> $gross_edu, 
                    "gross_package"			=> $gross_package, 
                    "gross_salary_earned"	=> $gross_salary_earned_html, 
                    "loans_advances" 		=> $loans_advances, 
                    "mobile_loan" 			=> $mobile_loan, 
                    "advance_amt" 			=> $advance_amt,
				    "loan_deduction"   	    => $loan_deduction,
                    "mobile_deduction"	  	=> $mobile_deduction, 
					"adjustment"    		=> $adjustment,      
				    "tds"    	  		 	=> $tds,
				    "pf"    	  		 	=> $pf_html,
				    "p_tax"    	  		 	=> $p_tax_html,
				    "esic"    	  		    => $esic_html,
				    "total_deduction"   	=> $total_deduction_html,
				    "final_salary"   	    => $final_salary_html,   
				    "gender"   	    		=> $gender,           
				    "action"   	    		=> $action,       
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
	

	
	
	public function getWorkingDays($month, $year, $salary_type,$asm_state_id="") {   
			// Validate the input parameters (optional)
			if (!checkdate($month, 1, $year)) {
				return 0; // Invalid month or year
			}

			$first_day = date('Y-m-01', strtotime("$year-$month-01"));
			$last_day = date('Y-m-t', strtotime("$year-$month-01"));

			$total_sundays = 0;
			$total_holidays = 0;
			$current_date = $first_day;
			while ($current_date <= $last_day) {
				if (date('N', strtotime($current_date)) == 7) {
					$total_sundays++;
				}
				$current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
			}

            if($salary_type=='FIELD STAFF'){
			   $this->db->where('state_id', $asm_state_id);	
			   $this->db->where('state_id IS NOT NULL', null, false); 
			   //$this->db->where_not_in('state_id', ['', null]);			
			}			
			$this->db->where('salary_type', $salary_type);
			$this->db->where('YEAR(holiday_date)', $year);
			$this->db->where('MONTH(holiday_date)', $month);
			$this->db->where('is_deleted', 0);
			$this->db->from('holidays'); 
			$total_holidays = $this->db->count_all_results();
			//echo $this->db->last_query();exit();

			// Calculate the total number of days in the specified month
			 $total_days= get_days_in_month($month, $year);

			// Calculate the working days
			$working_days = $total_days - ($total_sundays + $total_holidays);
			
			$resultdata = array();
			$resultdata = array(
			  "total_days" => $total_days,
			  "working_days" => $working_days,
			  "total_sundays" => $total_sundays,
			  "total_holidays" => $total_holidays,
            );
			return $resultdata;
		}
		   
	public function get_loans_details($emp_id,$month,$year){  
	   $sql_mobile = $this->db->query("SELECT id, SUM(amount) AS total_loan,SUM(amount_paid) AS paid_total_loan,emi FROM loans WHERE is_deleted='0' AND emp_id='$emp_id' AND (year < '$year' OR (year = '$year' AND month <= '$month')) AND loan_type='mobile_loan' AND status='ongoing' GROUP BY loan_type");
	  // echo $this->db->last_query();
	   if($sql_mobile->num_rows()>0){
		  $gmobile = $sql_mobile->row();
		  $mobile_id=$gmobile->id;
		  $mobile_loan=$gmobile->total_loan;
		  $mobile_amount_paid=$gmobile->paid_total_loan;
		  $balance_mobile_loan=$mobile_loan-$mobile_amount_paid;
		  $mobile_emi=($gmobile->emi>$balance_mobile_loan ? $balance_mobile_loan:$gmobile->emi);
	   }
	   else{
		 $mobile_id=0;
		 $mobile_loan=0;
		 $mobile_emi=0;
		 $balance_mobile_loan=0;
	   }	   
	   
	   $sql_cash = $this->db->query("SELECT id,SUM(amount) AS total_loan,SUM(amount_paid) AS paid_total_loan,emi FROM loans WHERE is_deleted='0' AND emp_id='$emp_id' AND (year < '$year' OR (year = '$year' AND month <= '$month')) AND loan_type='cash_loan' AND status='ongoing' GROUP BY loan_type");
	   if($sql_cash->num_rows()>0){
		  $gcash = $sql_cash->row();
		  $cash_id=$gcash->id;
		  $cash_loan=$gcash->total_loan;
		  $cash_amount_paid=$gcash->paid_total_loan;
		  $balance_cash_loan=$cash_loan-$cash_amount_paid;
		  $cash_emi=($gcash->emi>$balance_cash_loan ? $balance_cash_loan:$gcash->emi);
	   }
	   else{
		 $cash_id=0;
		 $cash_loan=0;
		 $cash_emi=0;
		 $balance_cash_loan=0;
	   }	   
	   
      
	   $data = array();
       $data = array(
		 "mobile_id"         	 => $mobile_id ?? 0,                      
		 "mobile_loan"           => $mobile_loan ?? 0,                      
		 "mobile_amount_paid"    => $mobile_amount_paid ?? 0,       
		 "balance_mobile_loan"   => $balance_mobile_loan ?? 0,   
		 
		 "mobile_emi"  		     => $mobile_emi ?? 0,       
		 "cash_emi"   			 => $cash_emi ?? 0, 
		 
		 "cash_id"       	     => $cash_id ?? 0,
		 "loans_advances"        => $cash_loan ?? 0,
		 "cash_amount_paid"      => $cash_amount_paid ?? 0,
		 "balance_cash_loan"     => $balance_cash_loan ?? 0,
	   );	
   
	  return $data;
	}  
	
	public function check_paid_leaves($emp_id,$paid_leave,$month,$year){  
	   $resultpost = array(
          "status" => 200,
          "message" => 'success'
       );
       
	   $sql_candidate = $this->db->query("SELECT paid_leaves FROM candidate WHERE emp_id='$emp_id' LIMIT 1");
	   if($sql_candidate->num_rows()>0){
		  $row = $sql_candidate->row();
		  $systumm_pl=$row->paid_leaves;		  
		  $total_paid_leave_ = $this->db->query("SELECT IFNULL(SUM(paid_leave), 0) AS total_paid_leave FROM paid_leave_history WHERE emp_id='$emp_id' AND year='$year'")->row()->total_paid_leave;
		 
		  $total_paid_leave=$total_paid_leave_+$paid_leave;
		  
		  if($total_paid_leave>$systumm_pl){
		    $bal_pl=$systumm_pl-$total_paid_leave_;
		  	$resultpost = array(
               "status" => 400,
               "message" => 'Alert, Only '.$bal_pl.' Paid leaves left!',           
             );		  
		  }
	   }
	   else{
		   $resultpost = array(
              "status" => 400,
              "message" => 'Candidate information not found!',           
           );
	   }	  
      return $resultpost; 
	}
      
	
	/*Attendance Rule Starts*/	
	public function check_holiday_sandwich($month_id,$emp_id) {	
		$year=date('Y');
	//check holiday sandwich
		$this->db->select('id,holiday_date');
		$this->db->where('MONTH(holiday_date)', $month_id);
		$this->db->where('YEAR(holiday_date)', $year);
		$this->db->where('is_deleted', 0);
		$holiday_list = $this->db->get('holidays');
		
		if($holiday_list->num_rows()>0){ 
		  foreach ($holiday_list->result_array() as $holi) {		
			//holiday  
			$holiday_date_= date('Y-m-d', strtotime($holi['holiday_date']));
			$holiday_date = date('Y-m-d', strtotime('+1 day', strtotime($holiday_date_)));
		    $sys_present_day = $this->get_previous_day_status($holiday_date,$emp_id);
								  
		    if($sys_present_day == 'Absent'){
				$previous_day = '';
				$previous_day=date('Y-m-d', strtotime('-2 day', strtotime($holiday_date)));			
				$previous_day_status = $this->get_previous_day_status($previous_day,$emp_id);
				
				if ($previous_day_status == 'Absent') {
					$this->db->where('emp_id', $emp_id);
					$this->db->where('DATE(punch_date)', $holiday_date_);
					$this->db->update('emp_attendance',array('status' => 'Absent','rule_check'=>1));
				}			  
			 }			
		  }
		}
	}
        
	public function update_attendance_status($month_id,$year,$emp_id,$staff_type) {		
			$this->db->select('id, emp_id,punch_date, shift_type, salary_type, check_in_date, check_out_date,is_early');
			$this->db->where('rule_check', 0);
			$this->db->where('shift_type!=', '');
			$this->db->where('emp_id', $emp_id);
			$this->db->where('MONTH(punch_date)', $month_id);
			$this->db->where('YEAR(punch_date)', $year);		
			$attendance_data = $this->db->get('emp_attendance')->result_array();

			foreach ($attendance_data as $attendance) {
				$status = $this->calculate_status($attendance['shift_type'], $attendance['check_in_date'], $attendance['check_out_date'], $attendance['punch_date'], $attendance['emp_id'],$attendance['is_early'],$staff_type);
							
				//holiday
				$punch_date= date("Y-m-d", strtotime($attendance['punch_date']));
			    $this->db->select('id');
				$this->db->where('DATE(holiday_date)', $punch_date);
				$this->db->where('FIND_IN_SET("'.$staff_type.'", staff_typeid) > 0', null, false);
				$this->db->where('is_deleted', 0);
				$chk_holiday = $this->db->get('holidays');
				 
				if ($chk_holiday->num_rows() > 0) {
					$emp_id=$attendance['emp_id'];						
					if ($status=='Absent') {	
						$this->db->where('id', $attendance['id']);
						$this->db->update('emp_attendance',array('status' => 'Holiday','rule_check'=>1));
						
					}
					else{				
						$this->db->where('id', $attendance['id']);
						$this->db->update('emp_attendance',array('status' => 'Holiday','rule_check'=>1));
					}
				}
				else{					
					$this->db->where('id', $attendance['id']);
					$this->db->update('emp_attendance',array('status' => $status,'rule_check'=>1));
				}				
			}
			  
			
			$year       = $year;
			$month      = $month_id;
				
			//early going if last date
			$this->db->select('id,emp_id,shift_type,punch_date,salary_type');
			$this->db->where('rule_check', 1);
			$this->db->where('is_early', 0);
			$this->db->where('shift_type!=', '');
			$this->db->where('emp_id', $emp_id);
			$this->db->where('MONTH(punch_date)', $month_id);
			$this->db->where('YEAR(punch_date)', $year);			
			$this->db->group_by('emp_id');
			$early_data = $this->db->get('emp_attendance')->result_array();
			foreach ($early_data as $early) {		
		        $emp_id=$early['emp_id'];			   
		        $punch_date=$early['punch_date'];			   
		        $shift_type_lower=strtolower($early['shift_type']);	
								
				if ($shift_type_lower == 'shift-1') {
					$check_early1 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND TIME(check_in_date) <= '10:00' AND TIME(check_out_date) >= '16:30' AND is_early='0' ORDER BY id ASC LIMIT 1");
					
					$check_early2 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND (TIME(check_in_date) >= '10:00' AND TIME(check_in_date) <= '10:05') AND TIME(check_out_date) >= '17:30' AND is_early='0' ORDER BY id ASC LIMIT 1");
					
					if ($check_early1->num_rows()>0) {
						$early_id=$check_early1->row()->id;
					
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET is_early='2',status='Present' WHERE id='$early_id'");	
				   }				   
				   elseif ($check_early2->num_rows()>0) {
						$early_id=$check_early2->row()->id;
					
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET is_early='2',status='Present' WHERE id='$early_id'");	
				   }
				}	
				
				if ($shift_type_lower == 'shift-2') {
					$check_early1 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND TIME(check_in_date) <= '10:00' AND TIME(check_out_date) >= '17:00' AND is_early='0' ORDER BY id ASC  LIMIT 1");
					
					$check_early2 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND (TIME(check_in_date) >= '10:00' AND TIME(check_in_date) <= '10:05')  AND TIME(check_out_date) >= '18:00' AND is_early='0' ORDER BY id ASC  LIMIT 1");
					
					if ($check_early1->num_rows()>0) {
						$early_id=$check_early1->row()->id;
						
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET  is_early='2',status='Present' WHERE id='$early_id'");		
				   }
				   elseif ($check_early2->num_rows()>0) {
						$early_id=$check_early2->row()->id;
						
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET  is_early='2',status='Present' WHERE id='$early_id'");		
				   }
				}
			}
			

		/*check holiday sandwich
		$this->db->select('id,holiday_date');
		$this->db->where('MONTH(holiday_date)', $month_id);
		$this->db->where('YEAR(holiday_date)', $year);
		$this->db->where('is_deleted', 0);
		$holiday_list = $this->db->get('holidays');
		if($holiday_list->num_rows()>0){
		  foreach ($holiday_list->result_array() as $holi) {		
			//holiday  
			$holiday_date_=date('Y-m-d', strtotime($holi['holiday_date']));
			$holiday_date = date('Y-m-d', strtotime('+1 day', strtotime($holiday_date_)));
		    $sys_present_day = $this->get_previous_day_status($holiday_date,$emp_id);
									  
		    if($sys_present_day == 'Absent'){
				$previous_day = '';
				$previous_day=date('Y-m-d', strtotime('-2 day', strtotime($holiday_date)));			
				$previous_day_status = $this->get_previous_day_status($previous_day,$emp_id);
				if ($previous_day_status == 'Absent') {
					$this->db->where('emp_id', $emp_id);
					$this->db->where('DATE(punch_date)', $holiday_date_);
					$this->db->update('emp_attendance',array('status' => 'Absent','rule_check'=>1));
				}			  
			 }			
		  }
		}*/

		//check holiday sandwich
		$this->db->select('id,holiday_date');
		$this->db->where('FIND_IN_SET("'.$staff_type.'", staff_typeid) > 0', null, false);
		$this->db->where('MONTH(holiday_date)', $month_id);
		$this->db->where('YEAR(holiday_date)', $year);
		$this->db->where('is_deleted', 0);
		$holiday_list = $this->db->get('holidays');
		if($holiday_list->num_rows()>0){ 
		  foreach ($holiday_list->result_array() as $holi) {		
			//holiday  
			$holiday_date_=$holi['holiday_date'];
			$holiday_date = date('Y-m-d', strtotime('+1 day', strtotime($holiday_date_)));
			
			
			 $punch_year_month=$previous_year_month='';
			 $punch_datetime    = new DateTime($holiday_date_);
			 $previous_datetime = new DateTime($holiday_date);

			 $punch_year_month    = $punch_datetime->format('Y-m');
			 $previous_year_month = $previous_datetime->format('Y-m');
			 
			$sys_present_day = $this->get_previous_day_status($holiday_date,$emp_id,$staff_type);
		    if($sys_present_day == 'Absent' && $punch_year_month === $previous_year_month){
			  	$previous_day = '';
				
				$previous_day = date('Y-m-d', strtotime('-1 day', strtotime($holiday_date_)));	
			
				if(date('N', strtotime($previous_day))==7){			
				 $previous_day = date('Y-m-d', strtotime('-2 day', strtotime($holiday_date_)));	
				}
				elseif($this->is_holiday($previous_day,$staff_type)){			
				 $previous_day = date('Y-m-d', strtotime('-2 day', strtotime($holiday_date_)));	
				}
				
				 $punch_year_month=$previous_year_month='';
				 $punch_datetime    = new DateTime($holiday_date_);
				 $previous_datetime = new DateTime($previous_day);

				 $punch_year_month    = $punch_datetime->format('Y-m');
				 $previous_year_month = $previous_datetime->format('Y-m');
						
				 $previous_day_status = $this->get_previous_day_status($previous_day,$emp_id,$staff_type);
				 if ($previous_day_status == 'Absent' && $punch_year_month === $previous_year_month) {
					$this->db->where('emp_id', $emp_id);
					$this->db->where('DATE(punch_date)', $holiday_date_);
					$this->db->update('emp_attendance',array('status' => 'Absent','rule_check'=>1));
				}			  
			  } 				
		  }
		}
	 }

	private function calculate_status($shift_type, $check_in_date, $check_out_date,$punch_date,$emp_id,$is_early,$staff_type) {
			$check_in_time = ($check_in_date!=NULL ? date('H:i:s', strtotime($check_in_date)):NULL);
			
			$check_out_time = ($check_out_date!=NULL ? date('H:i:s', strtotime($check_out_date)):NULL);

			$shift_type_lower = strtolower($shift_type);
			$day_of_week = date('l', strtotime($punch_date));
			
			$year = date('Y', strtotime($punch_date));
			$month = date('m', strtotime($punch_date));
			
			$firstSaturday = date("Y-m-d",strtotime("first saturday of $year-$month"));
			$thirdSaturday = date("Y-m-d",strtotime("third saturday of $year-$month"));
	  

			 //9 AM TO 5:30 PM
			if ($shift_type_lower == 'shift-1') {
				if ($check_in_time == NULL) {
					$status='Absent';
				}	
				elseif ($check_out_time == NULL) {
					$status='Absent';
				}					
				elseif ($check_in_time > '13:00:00') {
					$status='Absent';
				}
				elseif ($check_in_time <= '09:10:00' && $check_out_time < '13:00:00') {
					$status='Absent';
				}
				elseif ($check_in_time == '13:00:00' || $check_out_time <= '13:00:00') {
					$status='Half Day';
				}	
				elseif ($check_in_time <= '09:10:00' && $check_out_time < '16:30:00') {
					$status='Half Day';
				}
				elseif (($check_in_time >= '09:11:00' && $check_in_time <= '10:00:00') && $check_out_time >= '17:00:00') {
					$status='Late';
				}
				elseif ($check_in_time >= '10:00:00' && $check_in_time <= '13:00:00') {
					$status='Half Day';
				}
				elseif ($check_in_time <= '10:00:00') {
					// Check for late marks for SHIFT-1 (9 AM OR 9.10 AM)
					if (($check_in_time >= '09:00:00' && $check_in_time < '09:11:00') && $check_out_time >= '16:30:00') {
						$status='Present';
					}
					elseif (($check_in_time >= '09:11:00' && $check_in_time <= '10:00:00') && $check_out_time >= '16:30:00') {
						$status='Late';
					}
					elseif ($check_out_time < '13:00:00') {
						$status='Absent';
					}
					elseif ($check_out_time < '16:30:00') {
						$status='Half Day';
					}		
					else {
						$status='Present';						
					}
				} else {
					$status='Present';
				}
			}
			//9 AM TO 06 PM
			elseif ($shift_type_lower == 'shift-2') {
				if ($check_in_time == NULL) {
					$status='Absent';
				}	
				elseif ($check_out_time == NULL) {
					$status='Absent';
				}					
				elseif ($check_in_time > '13:00:00') {
					$status='Absent';
				}
				elseif ($check_in_time <= '09:10:00' && $check_out_time < '13:00:00') {
					$status='Absent';
				}
				elseif ($check_in_time == '13:00:00' || $check_out_time <= '13:00:00') {
					$status='Half Day';
				}	
				elseif ($check_in_time <= '09:10:00' && $check_out_time < '17:00:00') {
					$status='Half Day';
				}
				elseif (($check_in_time >= '09:11:00' && $check_in_time <= '10:00:00') && $check_out_time >= '17:00:00') {
					$status='Late';
				}
				elseif ($check_in_time >= '10:00:00' && $check_in_time <= '13:00:00') {
					$status='Half Day';
				}
				elseif ($check_in_time <= '10:00:00') {
					// Check for late marks for SHIFT-1 (9 AM OR 9.10 AM)
					if (($check_in_time >= '09:00:00' && $check_in_time < '09:11:00') && $check_out_time >= '17:0:00') {
						$status='Present';
					}
					elseif (($check_in_time >= '09:11:00' && $check_in_time <= '10:00:00') && $check_out_time >= '17:00:00') {
						$status='Late';
					}
					elseif ($check_out_time < '13:00:00') {
						$status='Absent';
					}
					elseif ($check_out_time < '17:00:00') {
						$status='Half Day';
					}		
					else {
						$status='Present';						
					}
				} else {
					$status='Present';
				}
			} 
			//6 AM TO 6 PM
			elseif ($shift_type_lower == 'shift-3') {
				if ($check_in_time == NULL) {
					$status='Absent';
				}	
				elseif ($check_out_time == NULL) {
					$status='Absent';
				}
			} else {
				$status='Present';
			}
		   			
			
			if ($day_of_week == 'Sunday') {
				$status='Weekly Off';
			}
		
		
		  
			if ($day_of_week == 'Monday' && $status=='Absent') {
				if(!$this->is_holiday($punch_date,$staff_type)){
					$previous_day =$previous_sunday= '';
					$previous_day = date('Y-m-d', strtotime('-2 day', strtotime($punch_date)));
					$previous_sunday = date('Y-m-d', strtotime('-1 day', strtotime($punch_date)));	
					$previous_day_status = $this->get_previous_day_status($previous_day,$emp_id,$staff_type);
										
					 $punch_year_month=$previous_year_month='';
					 $punch_datetime    = new DateTime($punch_date);
					 $previous_datetime = new DateTime($previous_day);

					 $punch_year_month    = $punch_datetime->format('Y-m');
					 $previous_year_month = $previous_datetime->format('Y-m');

					if ($previous_day_status == 'Absent'  && $punch_year_month === $previous_year_month) {
						$this->db->where('punch_date', $previous_sunday);
						$this->db->where('emp_id', $emp_id);
						$this->db->update('emp_attendance', array('status' => 'Absent'));
					} 
			  } else{
				    $previous_day = '';
					$previous_day = date('Y-m-d', strtotime('-2 day', strtotime($punch_date)));			
					$previous_day_status = $this->get_previous_day_status($previous_day,$emp_id,$staff_type);
					
					$previous_sunday = date('Y-m-d', strtotime('-1 day', strtotime($punch_date)));
					
					$after_day='';
					$after_day = date('Y-m-d', strtotime('+1 day', strtotime($punch_date)));
					$after_day_status = $this->get_previous_day_status($after_day,$emp_id,$staff_type);
					
					
					 $punch_year_month  = $previous_year_month='';
					 $punch_datetime    = new DateTime($punch_date);
					 $previous_datetime = new DateTime($previous_day);

					 $punch_year_month    = $punch_datetime->format('Y-m');
					 $previous_year_month = $previous_datetime->format('Y-m');
					 
					if ($previous_day_status == 'Absent' && $after_day_status == 'Absent' && $punch_year_month === $previous_year_month) {
						$this->db->where('punch_date', $previous_sunday);
						$this->db->where('emp_id', $emp_id);
						$this->db->update('emp_attendance', array('status' => 'Absent'));
					}				  
			  }
			}
								
			
		    $year = date('Y', strtotime($punch_date));
		    $month = date('n', strtotime($punch_date));	
			
		    $early_flag=0;
		    if($is_early==0){
				if ($shift_type_lower == 'shift-1') {
					$check_early1 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND TIME(check_in_date) <= '10:00' AND TIME(check_out_date) >= '16:30' AND is_early='0' ORDER BY id ASC LIMIT 1");
					
					$check_early2 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND (TIME(check_in_date) >= '10:00' AND TIME(check_in_date) <= '10:05') AND TIME(check_out_date) >= '17:30' AND is_early='0' ORDER BY id ASC LIMIT 1");
					
					if ($check_early1->num_rows()>0) {
						$early_id=$check_early1->row()->id;
					
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET is_early='2',status='Present' WHERE id='$early_id'");	
				   }				   
				   elseif ($check_early2->num_rows()>0) {
						$early_id=$check_early2->row()->id;
					
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET is_early='2',status='Present' WHERE id='$early_id'");	
				   }
				}	
				
				if ($shift_type_lower == 'shift-2') {
					$check_early1 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND TIME(check_in_date) <= '10:00' AND TIME(check_out_date) >= '17:00' AND is_early='0' ORDER BY id ASC  LIMIT 1");
					
					$check_early2 = $this->db->query("SELECT id FROM emp_attendance WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month' AND status='Half Day' AND (TIME(check_in_date) >= '10:00' AND TIME(check_in_date) <= '10:05')  AND TIME(check_out_date) >= '18:00' AND is_early='0' ORDER BY id ASC  LIMIT 1");
					
					if ($check_early1->num_rows()>0) {
						$early_id=$check_early1->row()->id;
						
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET  is_early='2',status='Present' WHERE id='$early_id'");		
				   }
				   elseif ($check_early2->num_rows()>0) {
						$early_id=$check_early2->row()->id;
						
						$update_early2 = $this->db->query("UPDATE emp_attendance SET is_early='1' WHERE emp_id='$emp_id' AND YEAR(punch_date)='$year' AND MONTH(punch_date)='$month'");
						$update_early1 = $this->db->query("UPDATE emp_attendance SET  is_early='2',status='Present' WHERE id='$early_id'");		
				   }
				}
			}
		  
		  return $status;			
		}
		
	
	private function get_previous_day_status($date,$emp_id,$staff_type) {
		$this->db->select('status');
		$this->db->where('emp_id', $emp_id);
		$this->db->where('DATE(punch_date)', $date);
		$query = $this->db->get('emp_attendance');

		if ($query->num_rows() > 0) {
			$row = $query->row();
			return $row->status;
		} else {
			if (date('N', strtotime($date)) == 7) {
				return 'Present';	
			}
			else{
				if ($this->is_holiday($date,$staff_type)) {
					return 'Present';
				} else {
					return 'Absent';
				}
			}
		}
		
	}

  
	public function calculateLateAndHalfDays($lateMarks,$halfMarks) {
			$total_late_mark=$total_half_days=$total_absent=0;
			$total_late_mark=$lateMarks;
			$total_half_days=$halfMarks;
			
			$late_no=3;
			$late_per=0.5;
			
			$half_no=1;
			$half_per=0.5; 
			
			$lateAbsent = roundToNearestHalf($total_late_mark*$late_per/$late_no);
			$halfAbsent = roundToNearestHalf($total_half_days*$half_per/$half_no);	       
			$total_absent = roundToNearestHalf($lateAbsent+$halfAbsent);        
			return $total_absent;
		}

    public function get_salary_report_by_id($year,$month,$emp_id){
        $data=array();	
		
		$query = $this->db->query("SELECT
			a.id,
			a.shift_type,
			a.punch_date,
			e.name,
			e.gender,
			e.emp_id,
			e.salary,
			e.basic_salary,
			e.hra,
			e.gross_edu,
			e.is_pf,
			e.is_esic,
			e.is_tds,
			e.staff_catid,
			e.salary_type,
			e.staff_typeid,
			e.staff_type,
			e.is_ptax,
			e.is_left,
			e.status,
			e.state_name,
			COUNT(DISTINCT DATE_FORMAT(a.punch_date, '%Y-%m-%d')) AS total_days,
			SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS days_present,
			SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS days_absent,
			SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS days_late,
			SUM(CASE WHEN a.status = 'Half Day' THEN 1 ELSE 0 END) AS days_half
		FROM
			emp_attendance a

		RIGHT JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL AND a.shift_type IS NOT NULL
		AND a.emp_id='$emp_id'
		AND MONTH(a.punch_date) = $month 
        AND YEAR(a.punch_date) = $year
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC LIMIT 1");		
        if (!empty($query)) { 
               $item=$query->row_array();
               $id=$item['id']; 
               $gender=$item['gender']; 
               $punch_date=$item['punch_date']; 
               $is_pf=$item['is_pf']; 
               $is_esic=$item['is_esic']; 
               $is_tds=$item['is_tds']; 
               $is_ptax=$item['is_ptax']; 
               $emp_id=$item['emp_id']; 
               $salary_type=$item['salary_type']; 
               $staff_catid=$item['staff_catid']; 
               $staff_typeid=$item['staff_typeid']; 
               $staff_type=$item['staff_type']; 
			   
			
			   $paid_leave=$basic_salary=$hra=$gross_edu=$gross_package=$gross_salary_earned=0;$loans_advances=$mobile_loan=$mobile_loan_instl=$mobile_loan_instl_amt=0;  
               $adjustment=$tds=$pf=$p_tax=$esic=$loan_deduction=$total_deduction=0;  
               $balance_loan_cf=$mobile_loan_cf=$net_salary_after_adj=$final_salary=0;
			   
			   $day_of_month=$present_day=$absent_day=$late_day=$half_day=$sys_total_days=0; 
			   //$day_of_month=$item['total_days'];  
			   $sys_total_days=$item['total_days']; 
			   
			   $year       = date('Y',strtotime($punch_date));
			   $month      = date('n',strtotime($punch_date));
			   $day_of_month= get_days_in_month($month, $year);
			   
			   $gwork=$this->getWorkingDays($month, $year, $salary_type,'');
			   $working_days=$gwork['working_days']; 
			   $total_sundays=$gwork['total_sundays'];
			   $total_holidays=$gwork['total_holidays'];
			   
			   if($sys_total_days!=$day_of_month){
                 $days_absent=$item['days_absent']+($day_of_month-$sys_total_days);
			   }
			   else{
                 $days_absent=$item['days_absent'];				   
			   }
			     
               $days_present=$item['days_present']; 
               $late_day=$item['days_late'];
               $half_day=$item['days_half'];
			              
			   $cal_absent=$total_absent=0;
			   $cal_absent=$this->calculateLateAndHalfDays($late_day,$half_day);
			   $absent_day=$days_absent+$cal_absent;
			   $present_day=$day_of_month-$absent_day;
			   
               $paid_leave=0;  
               $basic_salary=round_int($item['basic_salary']);   
               $hra=round_int($item['hra']);                
               $gross_edu=round_int($item['gross_edu']);   
               $gross_package=round_int($item['salary']);   
			   
			   
               $gross_salary_earned=gross_salary_earned($gross_package,$day_of_month,$present_day);  
			   
               $loans_advances=0;  
               $mobile_loan=0;  
               $mobile_loan_instl=0;  
               $mobile_loan_instl_amt=0;  
                         
               $pf=0;  
               $p_tax=0;  
               $esic=0;  			   
			   		 	   
			   $new_basic_salary = get_salary_per($gross_salary_earned,50);   
               $new_hra = get_salary_per($gross_salary_earned,25);                
               $new_gross_edu = get_salary_per($gross_salary_earned,25);   
			   
               $advance_amt=$this->get_advance_amount($emp_id,$month,$year);  	
               $pl_paidleave=$this->get_set_paidleave($emp_id,$month,$year);  
               $adjustment_amt=$this->get_adjustment_amount($emp_id,$month,$year);
               $tds_amt=$this->get_tds_amount($emp_id,$month,$year);
     
                $data = array(                                        
					"status"          	    => $item['status'],   		
					"state_name"          	=> $item['state_name'],        		
					"is_left"          	    => $item['is_left'],         		
				    "shift_type"           	=> $item['shift_type'],                   
					"salary_type"           => $item['salary_type'],                                        
					"staff_catid"           => $staff_catid,                                      
					"staff_typeid"          => $staff_typeid,                                     
					"staff_type"          	=> $staff_type,                                     
                    "pl_paidleave"         	=> $pl_paidleave,                   
                    "adjustment_amt"        => $adjustment_amt,                   
                    "tds_amt"         		=> $tds_amt,                   
                    "punch_date"         	=> $punch_date,                   
                    "name"         		    => $item['name'],                   
                    "emp_id"         		=> $item['emp_id'],                          
                    "day_of_month"			=> $day_of_month, 
                    "paid_leave"			=> $paid_leave, 
                    "present_day"			=> $present_day, 
                    "absent_day"			=> $absent_day, 
                    "basic_salary"			=> round_int($basic_salary), 
                    "hra"					=> round_int($hra), 
                    "gross_edu"				=> round_int($gross_edu), 
                    "gross_package"			=> round_int($gross_package), 
                    "gross_salary_earned"	=> round_int($gross_salary_earned), 
                    "loans_advances" 		=> $loans_advances, 
                    "mobile_loan" 			=> $mobile_loan, 
                    "gender" 				=> $gender, 
                    "is_pf" 				=> $is_pf, 
                    "is_esic" 				=> $is_esic, 
                    "is_tds" 				=> $is_tds,
                    "is_ptax" 				=> $is_ptax,
					
                    "working_days" 			=> $working_days,
                    "total_sundays"			=> $total_sundays,
                    "sys_present_day" 		=> 0,
                    "sys_absent_day" 		=> 0,
                    "total_calls" 			=> 0,
                    "calls_done" 			=> 0,
                    "total_dss" 			=> 0,
                    "total_camp" 			=> 0,
                    "calls_after_dss_camp" 	=> 0,
                    "balance_call" 			=> 0,
                    "no_days_absent" 		=> 0,
                    "calls_absent" 			=> 0,
                    "absent_sunday" 		=> 0,
                    "advance_amt"			=> $advance_amt,
					
                    "total_holidays"		=> $total_holidays,
                    "adj_present_day"		=> 0,
                    "adj_calls_done"		=> 0,
                );
            }
          		
   
        return $data;
    }
	
	  
      public function generate_salary(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('salary_generated_successfully'),
            "url" => $this->agent->referrer(),
        );        
        
    	$type= $this->input->post('type');
    	$month_id= $this->input->post('month_id');
    	$year = $this->input->post('year');
    	$paid_leave= $this->input->post('paid_leave');
    	$adjustment= $this->input->post('adjustment');
    	$loan_deduction= $this->input->post('loan_deduction');
    	$mobile_deduction= $this->input->post('mobile_deduction');
    	$tds= $this->input->post('tds');
    	$attn_id= $this->input->post('id');
    	$emp_id= $this->input->post('emp_id');
    	$adj_remark= $this->input->post('adj_remark');
			
		if($type=='OTHERS'){
			$first_emp=$this->common_model->getRowById('emp_attendance','punch_date',array('id'=>$attn_id,'emp_id'=>$emp_id));	
			
			$punch_date = $first_emp['punch_date'];
			$year       = date('Y',strtotime($punch_date));
			$month      = date('n',strtotime($punch_date));
			$month_name = date('F',strtotime($punch_date));				
			$day_of_month= get_days_in_month($month, $year);
			
			$emp=$this->get_salary_report_by_id($year,$month,$emp_id);		
		}
		else{
	       $emp=array();
		}
		
	   $loans_advances=$mobile_loan=$balance_cash_loan=$balance_mobile_loan=0; 
	   
	   $loans_details=$this->get_loans_details($emp_id,$month,$year);			   
	   $cash_id=$loans_details['cash_id'];  
	   $loans_advances=$loans_details['loans_advances'];  
	   $cash_amount_paid=$loans_details['cash_amount_paid'];  
	   $balance_cash_loan=$loans_details['balance_cash_loan'];  
	   	   
	   $mobile_id=$loans_details['mobile_id'];  
	   $mobile_loan=$loans_details['mobile_loan'];  
	   $mobile_amount_paid=$loans_details['mobile_amount_paid'];  
	   $balance_mobile_loan=$loans_details['balance_mobile_loan'];  
	  // echo json_encode($loans_details);
	   //exit();
	   
	   
	   $check_pl=$this->check_paid_leaves($emp_id,$paid_leave,$month,$year);	
		
		if(empty($emp)){
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('there_some_issue_while_generating!'),
            );			
		}
		elseif($emp['is_left']==1){
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('staff_is_left_from_company!'),
            );			
		}
		elseif($emp['status']==0){
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('staff_is_in_active!'),
            );			
		}
	    elseif($loan_deduction>$balance_cash_loan){
			 $resultpost = array(
				"status" => 400,
				"message" => "LOANS/ADVANCE Amount should be less than balance",
             );			
		} 
		elseif($mobile_deduction>$balance_mobile_loan){
			 $resultpost = array(
				"status" => 400,
				"message" => "MOBILE LOAN Amount should be less than balance",
             );			
		}  
		elseif($check_pl['status']==400){        
			$resultpost=$check_pl;
       }
	   else{	 
		 $present_day=$emp['present_day'];
		 $absent_day=$emp['absent_day'];
		 $gross_salary_earned=0;	
		 $gross_package=$emp['gross_package'];	
		 $advance_amt=$emp['advance_amt'];	
		 $pl_paidleave=$emp['pl_paidleave'];	
		 $adjustment_amt=$emp['adjustment_amt'];	
		 $tds_amt=$emp['tds_amt'];	
		 $total_present_days=$present_day+$paid_leave;	
		
		 $gross_salary_earned=gross_salary_earned($gross_package,$day_of_month,$total_present_days);  
		
		 $gender			= $emp['gender']; 
		 $basic_salary		= $emp['basic_salary']; 
		 $hra				= $emp['hra']; 
		 $gross_edu			= $emp['gross_edu'];			 
				              
		 $pf=0;  
		 $p_tax=0;  
		 $esic=0;  			   
					   
		 $new_basic_salary = get_salary_per($gross_salary_earned,50);   
		 $new_hra = get_salary_per($gross_salary_earned,25);                
		 $new_gross_edu = get_salary_per($gross_salary_earned,25);  	
		 
		 if($emp['is_ptax']==1){
		    $p_tax=calculate_ptax($punch_date,$gross_salary_earned,$gender);	
		 }
		   
		 if($emp['is_pf']==1){
		   $pf=calculate_pf($new_basic_salary, $new_gross_edu);
		 }
	 
		 if($emp['is_esic']==1){
		   $esic=calculate_esic($new_basic_salary, $new_hra, $new_gross_edu);		 
		 }
		
		$total_deduction=$final_salary=0;
		$total_deduction=round_int($loan_deduction + $mobile_deduction + $tds + $pf + $p_tax + $esic);
		$final_salary=round_int($gross_salary_earned - $total_deduction + $adjustment-$advance_amt);
		
		 if(count($emp)==0){
			  $resultpost = array(
				"status" => 400,
				"message" => get_phrase('there_some_issue_while_generating!'),
            );        
        
		 }
		 else{  
		  $data_report=array();			
		  $data_report= array(                  
			"emp_id"         	    => $emp_id,                               
			"gender"            	=> $emp['gender'],                   
			"state_name"            => $emp['state_name'],                   
			"shift_type"            => $emp['shift_type'],                   
			"salary_type"           => $emp['salary_type'],   
			"staff_catid"           => $emp['staff_catid'],   
			"staff_typeid"          => $emp['staff_typeid'],   
			"staff_type"            => $emp['staff_type'],   
			
			"name"          		=> $emp['name'],                   
			"year"          		=> $year,
			"month"          		=> $month,
			"month_name"          	=> $month_name,              
			"day_of_month"			=> $day_of_month, 
			"paid_leave"			=> $paid_leave, 
			"present_day"			=> $total_present_days, 
			"absent_day"			=> $absent_day, 
			"basic_salary"			=> round_int($basic_salary), 
			"hra"					=> round_int($hra), 
			"gross_edu"				=> round_int($gross_edu), 
			"gross_package"			=> round_int($gross_package), 
			"gross_salary_earned"	=> round_int($gross_salary_earned), 
			"loans_advances" 		=> $emp['loans_advances'] ?? 0, 
			"mobile_loan" 			=> $emp['mobile_loan'], 
			"loan_deduction"   	    => $loan_deduction,
			"mobile_deduction"	  	=> $mobile_deduction, 
			"adjustment"    		=> $adjustment,      
			"adj_remark"    		=> $adj_remark,      
			"tds"    	  		 	=> $tds,
			"pf"    	  		 	=> $pf,
			"p_tax"    	  		 	=> $p_tax,
			"esic"    	  		    => $esic,
			"total_deduction"   	=> round_int($total_deduction),
			"advance_amt"   		=> $advance_amt,
			"final_salary"   	    => round_int($final_salary),  

		    "working_days" 			=> $emp['working_days'],
			"total_calls" 			=> $emp['total_calls'],
			"calls_done" 			=> $emp['calls_done'],
			"total_dss" 			=> $emp['total_dss'],
			"total_camp" 			=> $emp['total_camp'],
			"calls_after_dss_camp" 	=> $emp['calls_after_dss_camp'],
			
			"total_sundays" 		=> $emp['total_sundays'],
			"sys_present_day" 		=> $emp['sys_present_day'],
			"sys_absent_day" 		=> $emp['sys_absent_day'],
			"balance_call" 			=> $emp['balance_call'],
			"no_days_absent" 		=> $emp['no_days_absent'],
			"calls_absent" 		    => $emp['calls_absent'],
			"absent_sunday" 		=> $emp['absent_sunday'],
			
			"total_holidays" 		=> $emp['total_holidays'],
			"adj_present_day" 		=> $emp['adj_present_day'],
			"adj_calls_done" 		=> $emp['adj_calls_done'],
		  );
			 
		  /*echo json_encode($data_report);
	   exit();*/
		 $check = $this->db->query("SELECT id FROM emp_generated_salary WHERE emp_id='$emp_id' AND year='$year' AND month='$month' LIMIT 1");
   
 
		if($check->num_rows()>0){	
		  $report_id =$check->row()->id;	
		  $data_report['updated_at'] = date("Y-m-d H:i:s");
		  $this->db->where('id', $report_id);
		  $this->db->update('emp_generated_salary',$data_report);   
		}
		else{
		  $data_report['added_by_id']   = $this->session->userdata('super_user_id');
		  $data_report['added_by_name'] = $this->session->userdata('super_name');
		  $data_report['created_at']	= date("Y-m-d H:i:s");
		  $this->db->insert('emp_generated_salary', $data_report);
		  $report_id = $this->db->insert_id();				
		}
			
		if($report_id){
		  $curr_date=date("Y-m-d H:i:s");
		  if($type=='OTHERS'){
			  $data = array();
			  $data['is_generated'] = 1;
			  $data['generated_date'] = $curr_date;
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('MONTH(punch_date)', $month);
			  $this->db->where('YEAR(punch_date)', $year);
			  $this->db->update('emp_attendance',$data);
			}
		  
		  
		  //advance_amt
		   if($advance_amt>0 || $advance_amt < 0){	
			  $data_adv = array();
			  $data_adv['status'] = 'paid';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('advance',$data_adv);
		   }  
		   
		  //adjustment_amt
		   if($adjustment_amt>0 || $adjustment_amt < 0){	
			  $data_adj = array();
			  $data_adj['status'] = 'paid';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('emp_adjustment',$data_adj);
		   }	

		   //tds_amt 
		   if($tds_amt>0){	
			  $data_tds = array();
			  $data_tds['status'] = 'paid';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('emp_tds',$data_tds);
		   }	   
		    
		   //pl_paidleave
		   if($pl_paidleave>0){	
			  $data_paidleave = array();
			  $data_paidleave['status'] = 'completed';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('emp_paidleave',$data_paidleave);
		   }
		  
		  //paid_leave_history
		  if($paid_leave>0):
			  $data_pl_history= array();			
			  $data_pl_history= array(                  
				"emp_id"        => $emp_id,                      
				"emp_name"      => $emp['name'],                    
				"salary_type"   => $emp['salary_type'],                    
				"paid_leave"    => $paid_leave,                   
				"gen_id"        => $report_id,                   
				"month"         => $month,
				"month_name"    => $month_name,
				"year"          => $year,
				"created_at" 	=> $curr_date,
			  );			
			 $check_paid_leave = $this->db->query("SELECT id FROM paid_leave_history WHERE emp_id='$emp_id' AND year='$year' AND month='$month' LIMIT 1")->num_rows();
			 if ($check_paid_leave > 0) {
				$this->db->where('emp_id', $emp_id);
				$this->db->where('year', $year);
				$this->db->where('month', $month);
				$this->db->update('paid_leave_history', $data_pl_history);
			 } else {
				$data_pl_history['added_by_id']   = $this->session->userdata('super_user_id');
				$data_pl_history['added_by_name'] = $this->session->userdata('super_name');
				$this->db->insert('paid_leave_history', $data_pl_history);
			 }
		  endif;
		  
		  
		  //cash loan_repayments
		  if($loan_deduction>0):
			  $data_cash_loan= array();			
			  $data_cash_loan= array(                  
				"emp_id"        => $emp_id,                      
				"emp_name"      => $emp['name'],                    
				"salary_type"   => $emp['salary_type'],                
				"loan_id"  	    => $cash_id,                   
				"loan_type"     => 'cash_loan',                   
				"amount"        => $loan_deduction,
				"gen_id"        => $report_id,
				"month"    		=> $month,
				"month_name"    => $month_name,
				"year"          => $year,
				"added_by_id"   => $this->session->userdata('super_user_id'),
				"added_by_name" => $this->session->userdata('super_name'),
				"repayment_date"=> $curr_date,
			  );
			 $cash_insert=$this->db->insert('loan_repayments', $data_cash_loan);	

			if($cash_insert):
				//loans 
				$amount_paid=$diff=0;
				$amount_paid=$cash_amount_paid+$loan_deduction;
				//$status=($loans_advances==$amount_paid ? 'paid':'ongoing');
				
				$diff = abs($loans_advances - $amount_paid);
				if ($loans_advances == $amount_paid || $diff <=1) {
					$status = 'paid';
				} else {
					$status = 'ongoing';
				}
				
				$data_loans= array();			
				$data_loans['status']	   = $status;
				$data_loans['amount_paid'] = $amount_paid;
				$this->db->where('id', $cash_id);
				$this->db->where('loan_type', 'cash_loan');
				$this->db->update('loans',$data_loans);    
			endif;
						  
		  endif;
		  
		   //mobile loan_repayments
		  if($mobile_deduction>0):  
			  $data_cash_loan= array();			
			  $data_cash_loan= array(                  
				"emp_id"        => $emp_id,                      
				"emp_name"      => $emp['name'],           
				"salary_type"   => $emp['salary_type'],                    
				"loan_id"  	    => $mobile_id,                   
				"loan_type"     => 'mobile_loan',                   
				"amount"        => $mobile_deduction,
				"gen_id"        => $report_id,
				"month"    		=> $month,
				"month_name"    => $month_name,
				"year"          => $year,
				"added_by_id"   => $this->session->userdata('super_user_id'),
				"added_by_name" => $this->session->userdata('super_name'),
				"repayment_date"=> $curr_date,
			  );
			  $mobile_insert=$this->db->insert('loan_repayments', $data_cash_loan);

			  if($mobile_insert):
				//loans 
			    $amount_paid=$diff=0;
				$amount_paid=$mobile_amount_paid+$mobile_deduction;
				//$status=($mobile_loan==$amount_paid ? 'paid':'ongoing');
				
				$diff = abs($mobile_loan - $amount_paid);
				if ($mobile_loan == $amount_paid || $diff <=1) {
					$status = 'paid';
				} else {
					$status = 'ongoing';
				}
				
				$data_loans= array();			
				$data_loans['status']	   = $status;
				$data_loans['amount_paid'] = $amount_paid;
				$this->db->where('id', $mobile_id);
				$this->db->where('loan_type', 'mobile_loan');
				$this->db->update('loans',$data_loans); 
				
			endif;
		  endif;
		  
		}	
        $this->session->set_flashdata('flash_message', get_phrase('salary_generated_successfully'));       
	   }
	  }
      return simple_json_output($resultpost); 
    }
    
	/*public function hold_salary(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('salary_holded_successfully'),
            "url" => $this->agent->referrer(),
        );        
        
    	$type= $this->input->post('type');
    	$month_id= $this->input->post('month_id');
    	$attn_id= $this->input->post('id');
    	$emp_id= $this->input->post('emp_id');
		
		
		if($type=='OTHERS'){
			$first_emp=$this->common_model->getRowById('emp_attendance','punch_date',array('id'=>$attn_id,'emp_id'=>$emp_id));	
			
			$punch_date = $first_emp['punch_date'];
			$year       = date('Y',strtotime($punch_date));
			$month      = date('n',strtotime($punch_date));
			$month_name = date('F',strtotime($punch_date));				
			$day_of_month= get_days_in_month($month, $year);
			
			$emp=$this->get_salary_report_by_id($year,$month,$emp_id);		
		}
		elseif($type=='FIELD-STAFF'){			
			$year         = date('Y'); 
			$month        = $month_id; 
			$punch_date   = $year . '-' . $month . '-01';				
			$month_name   = date('F',strtotime($punch_date));				
			$day_of_month = get_days_in_month($month, $year);
			
			$emp=$this->get_ff_salary_report_by_id($year,$month,$emp_id);			
		}
		else{
	    $emp=array();
		}
			
	   $check = $this->db->query("SELECT id FROM emp_hold_salary WHERE emp_id='$emp_id' AND year='$year' AND month='$month' LIMIT 1")->num_rows();
	   //echo $this->db->last_query();exit();
		 
		if(empty($emp)){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('there_some_issue_while_generating!'),
            );			
		}	 
		elseif($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('already_added_in_hold_salary!'),
            );			
		}
		else{
		  
		$present_day=$emp['present_day'];
		$absent_day=$emp['absent_day'];
		$gross_salary_earned=0;	
		$gross_package=$emp['gross_package'];	
		$total_present_days=$present_day;	
		
		$gross_salary_earned=gross_salary_earned($gross_package,$day_of_month,$total_present_days); 
		
		 $gender = $emp['gender']; 
				              
		 $pf=0;  
		 $p_tax=0;  
		 $esic=0;  			   
					   
		 $new_basic_salary = get_salary_per($gross_salary_earned,50);   
		 $new_hra = get_salary_per($gross_salary_earned,25);                
		 $new_gross_edu = get_salary_per($gross_salary_earned,25);  	
		
		 if($emp['is_ptax']==1){
		    $p_tax=calculate_ptax($punch_date,$gross_salary_earned,$gender);	
		 }
		
		 if($emp['is_pf']==1){
		   $pf=calculate_pf($new_basic_salary, $new_gross_edu);
		 }
	 
		 if($emp['is_esic']==1){
		   $esic=calculate_esic($new_basic_salary, $new_hra, $new_gross_edu);		 
		 }
		
		$total_deduction=$final_salary=$adjustment=0;
		$total_deduction=round_int($pf + $p_tax + $esic);
		$final_salary=round_int($gross_salary_earned - $total_deduction + $adjustment);
		
		 if(count($emp)==0){
			  $resultpost = array(
				"status" => 400,
				"message" => get_phrase('there_some_issue_while_generating!'),
            );        
        
		 }
		 else{
		     
		  $data_report=array();			
		  $data_report= array(                                               
			"emp_id"         	    => $emp_id,                                      
			"emp_name"          	=> $emp['name'], 
			"salary_type"          	=> $emp['salary_type'], 
			"salary"   	   		    => $final_salary,   
			"month"          		=> $month,                  
			"year"          		=> $year,
			"month_name"          	=> $month_name,              
			"added_by_id"          	=> $this->session->userdata('super_user_id'),             
			"added_by_name"         => $this->session->userdata('super_name'),              
			"created_at"          	=> date("Y-m-d H:i:s"),    
		  );
		
		 if($this->db->insert('emp_hold_salary', $data_report)){
			$report_id=$this->db->insert_id();			 
			if($type=='OTHERS'){
			  $curr_date=date("Y-m-d H:i:s");
			  $data = array();
			  $data['is_hold'] = 1;
			  $data['hold_date'] = $curr_date;
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('MONTH(punch_date)', $month);
			  $this->db->where('YEAR(punch_date)', $year);
			  $this->db->update('emp_attendance',$data);
			} 
		 }	 
		 	
        $this->session->set_flashdata('flash_message', get_phrase('salary_holded_successfully'));  		
	   }
	  }
      return simple_json_output($resultpost); 
    }*/
	
	public function hold_salary(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('salary_holded_successfully'),
            "url" => $this->agent->referrer(),
        );        
        
    	$type= $this->input->post('type');
    	$month_id= $this->input->post('month_id');
    	$year= $this->input->post('year');
    	$paid_leave= $this->input->post('paid_leave');
    	$adjustment= $this->input->post('adjustment');
    	$loan_deduction= $this->input->post('loan_deduction');
    	$mobile_deduction= $this->input->post('mobile_deduction');
    	$tds= $this->input->post('tds');
    	$attn_id= $this->input->post('id');
    	$emp_id= $this->input->post('emp_id');
    	$adj_remark= $this->input->post('adj_remark');
			
		if($type=='OTHERS'){
			$first_emp=$this->common_model->getRowById('emp_attendance','punch_date',array('id'=>$attn_id,'emp_id'=>$emp_id));	
			
			$punch_date = $first_emp['punch_date'];
			$year       = date('Y',strtotime($punch_date));
			$month      = date('n',strtotime($punch_date));
			$month_name = date('F',strtotime($punch_date));				
			$day_of_month= get_days_in_month($month, $year);
			
			$emp=$this->get_salary_report_by_id($year,$month,$emp_id);		
		}
		elseif($type=='FIELD-STAFF'){			
			$year         = $year; 
			$month        = $month_id; 
			$punch_date   = $year . '-' . $month . '-01';				
			$month_name   = date('F',strtotime($punch_date));				
			$day_of_month = get_days_in_month($month, $year);			
			$emp=$this->get_ff_salary_report_by_id($year,$month,$emp_id);		
						
		}
		else{
	       $emp=array();
		}
		
		$check = $this->db->query("SELECT id FROM emp_generated_salary WHERE emp_id='$emp_id' AND year='$year' AND month='$month' AND is_hold=1 LIMIT 1")->num_rows();
		//echo $this->db->last_query();exit();
		 
		if(empty($emp)){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('there_some_issue_while_generating!'),
            );			
		}	 
		elseif($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('already_added_in_hold_salary!'),
            );			
		}
		else{
		
	   $loans_advances=$mobile_loan=$balance_cash_loan=$balance_mobile_loan=0; 
	   
	   $loans_details=$this->get_loans_details($emp_id,$month,$year);			   
	   $cash_id=$loans_details['cash_id'];  
	   $loans_advances=$loans_details['loans_advances'];  
	   $cash_amount_paid=$loans_details['cash_amount_paid'];  
	   $balance_cash_loan=$loans_details['balance_cash_loan'];  
	   	   
	   $mobile_id=$loans_details['mobile_id'];  
	   $mobile_loan=$loans_details['mobile_loan'];  
	   $mobile_amount_paid=$loans_details['mobile_amount_paid'];  
	   $balance_mobile_loan=$loans_details['balance_mobile_loan'];  
	  // echo json_encode($loans_details);
	   //exit();
	   
	   
	   $check_pl=$this->check_paid_leaves($emp_id,$paid_leave,$month,$year);	
		
		if(empty($emp)){
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('there_some_issue_while_generating!'),
            );			
		}
		elseif($emp['is_left']==1){
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('staff_is_left_from_company!'),
            );			
		}
		elseif($emp['status']==0){
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('staff_is_in_active!'),
            );			
		}
	    elseif($loan_deduction>$balance_cash_loan){
			 $resultpost = array(
				"status" => 400,
				"message" => "LOANS/ADVANCE Amount should be less than balance",
             );			
		} 
		elseif($mobile_deduction>$balance_mobile_loan){
			 $resultpost = array(
				"status" => 400,
				"message" => "MOBILE LOAN Amount should be less than balance",
             );			
		}  
		elseif($check_pl['status']==400){        
			$resultpost=$check_pl;
       }
	   else{	 
		 $present_day=$emp['present_day'];
		 $absent_day=$emp['absent_day'];
		 $gross_salary_earned=0;	
		 $gross_package=$emp['gross_package'];	
		 $advance_amt=$emp['advance_amt'];	
		 $pl_paidleave=$emp['pl_paidleave'];	
		 $adjustment_amt=$emp['adjustment_amt'];	
		 $tds_amt=$emp['tds_amt'];	
		 $total_present_days=$present_day+$paid_leave;	
		
		 $gross_salary_earned=gross_salary_earned($gross_package,$day_of_month,$total_present_days);  
		
		 $gender			= $emp['gender']; 
		 $basic_salary		= $emp['basic_salary']; 
		 $hra				= $emp['hra']; 
		 $gross_edu			= $emp['gross_edu'];			 
				              
		 $pf=0;  
		 $p_tax=0;  
		 $esic=0;  			   
					   
		 $new_basic_salary = get_salary_per($gross_salary_earned,50);   
		 $new_hra = get_salary_per($gross_salary_earned,25);                
		 $new_gross_edu = get_salary_per($gross_salary_earned,25);  	
		 
		 if($emp['is_ptax']==1){
		    $p_tax=calculate_ptax($punch_date,$gross_salary_earned,$gender);	
		 }
		   
		 if($emp['is_pf']==1){
		   $pf=calculate_pf($new_basic_salary, $new_gross_edu);
		 }
	 
		 if($emp['is_esic']==1){
		   $esic=calculate_esic($new_basic_salary, $new_hra, $new_gross_edu);		 
		 }
		
		$total_deduction=$final_salary=0;
		$total_deduction=round_int($loan_deduction + $mobile_deduction + $tds + $pf + $p_tax + $esic);
		$final_salary=round_int($gross_salary_earned - $total_deduction + $adjustment-$advance_amt);
		
		 if(count($emp)==0){
			  $resultpost = array(
				"status" => 400,
				"message" => get_phrase('there_some_issue_while_generating!'),
            );        
        
		 }
		 else{  
		  $curr_date=date("Y-m-d H:i:s");
		  $data_report=array();			
		  $data_report= array(                  
			"emp_id"         	    => $emp_id,                               
			"gender"            	=> $emp['gender'],                   
			"state_name"            => $emp['state_name'],                   
			"shift_type"            => $emp['shift_type'],                   
			"salary_type"           => $emp['salary_type'],                   
			"name"          		=> $emp['name'],                   
			"year"          		=> $year,
			"month"          		=> $month,
			"month_name"          	=> $month_name,              
			"day_of_month"			=> $day_of_month, 
			"paid_leave"			=> $paid_leave, 
			"present_day"			=> $total_present_days, 
			"absent_day"			=> $absent_day, 
			"basic_salary"			=> round_int($basic_salary), 
			"hra"					=> round_int($hra), 
			"gross_edu"				=> round_int($gross_edu), 
			"gross_package"			=> round_int($gross_package), 
			"gross_salary_earned"	=> round_int($gross_salary_earned), 
			"loans_advances" 		=> $emp['loans_advances'] ?? 0, 
			"mobile_loan" 			=> $emp['mobile_loan'], 
			"loan_deduction"   	    => $loan_deduction,
			"mobile_deduction"	  	=> $mobile_deduction, 
			"adjustment"    		=> $adjustment,      
			"adj_remark"    		=> $adj_remark,      
			"tds"    	  		 	=> $tds,
			"pf"    	  		 	=> $pf,
			"p_tax"    	  		 	=> $p_tax,
			"esic"    	  		    => $esic,
			"total_deduction"   	=> round_int($total_deduction),
			"advance_amt"   		=> $advance_amt,
			"final_salary"   	    => round_int($final_salary),  

		    "working_days" 			=> $emp['working_days'],
			"total_calls" 			=> $emp['total_calls'],
			"calls_done" 			=> $emp['calls_done'],
			"total_dss" 			=> $emp['total_dss'],
			"total_camp" 			=> $emp['total_camp'],
			"calls_after_dss_camp" 	=> $emp['calls_after_dss_camp'],
			
			"total_sundays" 		=> $emp['total_sundays'],
			"sys_present_day" 		=> $emp['sys_present_day'],
			"sys_absent_day" 		=> $emp['sys_absent_day'],
			"balance_call" 			=> $emp['balance_call'],
			"no_days_absent" 		=> $emp['no_days_absent'],
			"calls_absent" 		    => $emp['calls_absent'],
			"absent_sunday" 		=> $emp['absent_sunday'],
			
			"total_holidays" 		=> $emp['total_holidays'],
			"adj_present_day" 		=> $emp['adj_present_day'],
			"adj_calls_done" 		=> $emp['adj_calls_done'],
			"is_hold" 				=> 1,
			"hold_date" 			=> $curr_date,
		  );
			 
		  /*echo json_encode($data_report);
	   exit();*/
		 $check = $this->db->query("SELECT id FROM emp_generated_salary WHERE emp_id='$emp_id' AND year='$year' AND month='$month' LIMIT 1");
   
 
		if($check->num_rows()>0){	
		  $report_id =$check->row()->id;	
		  $data_report['updated_at'] = date("Y-m-d H:i:s");
		  $this->db->where('id', $report_id);
		  $this->db->update('emp_generated_salary',$data_report);   
		}
		else{
		  $data_report['added_by_id']   = $this->session->userdata('super_user_id');
		  $data_report['added_by_name'] = $this->session->userdata('super_name');
		  $data_report['created_at']	= date("Y-m-d H:i:s");
		  $this->db->insert('emp_generated_salary', $data_report);
		  $report_id = $this->db->insert_id();				
		}
			
		if($report_id){
		  $data_report=array();			
		  $data_report= array(                                               
			"emp_id"         	    => $emp_id,                                      
			"emp_name"          	=> $emp['name'], 
			"salary_type"          	=> $emp['salary_type'], 
			"salary"   	   		    => $final_salary,   
			"month"          		=> $month,                  
			"year"          		=> $year,
			"month_name"          	=> $month_name,              
			"added_by_id"          	=> $this->session->userdata('super_user_id'),             
			"added_by_name"         => $this->session->userdata('super_name'),              
			"created_at"          	=> date("Y-m-d H:i:s"),    
		  );
		  $this->db->insert('emp_hold_salary', $data_report);
			
			
		  $curr_date=date("Y-m-d H:i:s");
		   if($type=='OTHERS'){
			  $data = array();
			  $data['is_hold'] = 1;
			  $data['hold_date'] = $curr_date;
			  $data['is_generated'] = 1;
			  $data['generated_date'] = $curr_date;
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('MONTH(punch_date)', $month);
			  $this->db->where('YEAR(punch_date)', $year);
			  $this->db->update('emp_attendance',$data);
			}
		  
		  
		  //advance_amt
		   if($advance_amt>0){	
			  $data_adv = array();
			  $data_adv['status'] = 'paid';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('advance',$data_adv);
		   }  
		   
		  //adjustment_amt
		   if($adjustment_amt>0){	
			  $data_adj = array();
			  $data_adj['status'] = 'paid';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('emp_adjustment',$data_adj);
		   }	

		   //tds_amt 
		   if($tds_amt>0){	
			  $data_tds = array();
			  $data_tds['status'] = 'paid';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('emp_tds',$data_tds);
		   }	   
		    
		   //pl_paidleave
		   if($pl_paidleave>0){	
			  $data_paidleave = array();
			  $data_paidleave['status'] = 'completed';
			  $this->db->where('emp_id', $emp_id);
			  $this->db->where('month', $month);
			  $this->db->where('year', $year);
			  $this->db->update('emp_paidleave',$data_paidleave);
		   }
		  
		  //paid_leave_history
		  if($paid_leave>0):
			  $data_pl_history= array();			
			  $data_pl_history= array(                  
				"emp_id"        => $emp_id,                      
				"emp_name"      => $emp['name'],                    
				"salary_type"   => $emp['salary_type'],                    
				"paid_leave"    => $paid_leave,                   
				"gen_id"        => $report_id,                   
				"month"         => $month,
				"month_name"    => $month_name,
				"year"          => $year,
				"created_at" 	=> $curr_date,
			  );			
			 $check_paid_leave = $this->db->query("SELECT id FROM paid_leave_history WHERE emp_id='$emp_id' AND year='$year' AND month='$month' LIMIT 1")->num_rows();
			 if ($check_paid_leave > 0) {
				$this->db->where('emp_id', $emp_id);
				$this->db->where('year', $year);
				$this->db->where('month', $month);
				$this->db->update('paid_leave_history', $data_pl_history);
			 } else {
				$data_pl_history['added_by_id']   = $this->session->userdata('super_user_id');
				$data_pl_history['added_by_name'] = $this->session->userdata('super_name');
				$this->db->insert('paid_leave_history', $data_pl_history);
			 }
		  endif;
		  
		  
		   //cash loan_repayments
		  if($loan_deduction>0):
			  $data_cash_loan= array();			
			  $data_cash_loan= array(                  
				"emp_id"        => $emp_id,                      
				"emp_name"      => $emp['name'],                    
				"salary_type"   => $emp['salary_type'],                
				"loan_id"  	    => $cash_id,                   
				"loan_type"     => 'cash_loan',                   
				"amount"        => $loan_deduction,
				"gen_id"        => $report_id,
				"month"    		=> $month,
				"month_name"    => $month_name,
				"year"          => $year,
				"added_by_id"   => $this->session->userdata('super_user_id'),
				"added_by_name" => $this->session->userdata('super_name'),
				"repayment_date"=> $curr_date,
			  );
			 $cash_insert=$this->db->insert('loan_repayments', $data_cash_loan);	

			if($cash_insert):
				//loans 
				$amount_paid=$diff=0;
				$amount_paid=$cash_amount_paid+$loan_deduction;
				//$status=($loans_advances==$amount_paid ? 'paid':'ongoing');
				
				$diff = abs($loans_advances - $amount_paid);
				if ($loans_advances == $amount_paid || $diff <=1) {
					$status = 'paid';
				} else {
					$status = 'ongoing';
				}
				
				
				$data_loans= array();			
				$data_loans['status']	   = $status;
				$data_loans['amount_paid'] = $amount_paid;
				$this->db->where('id', $cash_id);
				$this->db->where('loan_type', 'cash_loan');
				$this->db->update('loans',$data_loans);    
			endif;
						  
		  endif;
		  
		   //mobile loan_repayments
		  if($mobile_deduction>0):  
			  $data_cash_loan= array();			
			  $data_cash_loan= array(                  
				"emp_id"        => $emp_id,                      
				"emp_name"      => $emp['name'],           
				"salary_type"   => $emp['salary_type'],                    
				"loan_id"  	    => $mobile_id,                   
				"loan_type"     => 'mobile_loan',                   
				"amount"        => $mobile_deduction,
				"gen_id"        => $report_id,
				"month"    		=> $month,
				"month_name"    => $month_name,
				"year"          => $year,
				"added_by_id"   => $this->session->userdata('super_user_id'),
				"added_by_name" => $this->session->userdata('super_name'),
				"repayment_date"=> $curr_date,
			  );
			  $mobile_insert=$this->db->insert('loan_repayments', $data_cash_loan);

			  if($mobile_insert):
				//loans 
			    $amount_paid=$diff=0;
				$amount_paid=$mobile_amount_paid+$mobile_deduction;
				//$status=($mobile_loan==$amount_paid ? 'paid':'ongoing');
				
				$diff = abs($mobile_loan - $amount_paid);
				if ($mobile_loan == $amount_paid || $diff <=1) {
					$status = 'paid';
				} else {
					$status = 'ongoing';
				}
				
				$data_loans= array();			
				$data_loans['status']	   = $status;
				$data_loans['amount_paid'] = $amount_paid;
				$this->db->where('id', $mobile_id);
				$this->db->where('loan_type', 'mobile_loan');
				$this->db->update('loans',$data_loans); 
				
			endif;
		  endif;  
		  
		  
		}	
			
			
        $this->session->set_flashdata('flash_message', get_phrase('salary_holded_successfully'));       
	   }
	   }
	  }
      return simple_json_output($resultpost); 
    }
	/*Attendance Rule Ends*/
	
	
	public function get_generated_salary_report(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start  = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['month_id']    = $_REQUEST['month_id'];
       	$filter_data['year']   	    = $_REQUEST['year'];
        $filter_data['salary_type'] = $_REQUEST['salary_type'];
        $filter_data['keywords']	= $_REQUEST['keywords'];
        
        if(isset($filter_data['month_id']) && $filter_data['month_id']!="") :
          $month_id=$filter_data['month_id'];
          $year=$filter_data['year'];
          $sql_filter .=" AND (a.year='$year' AND a.month='$month_id')"; 
        endif;           
		
		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];
            $sql_filter .= " AND (a.staff_typeid='$salary_type')";
        endif;	

		if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
          $keyword=$filter_data['keywords'];
          $sql_filter .=" AND (e.name like '%".$keyword."%'  
            OR e.emp_id = '$keyword'
            OR e.phone like '%" . $keyword . "%')"; 
        endif;           
		
		
       $total_count=0;
	   if($filter_data['month_id']!=''){
		$total_count = $this->db->query("SELECT
			e.id
		FROM
			emp_generated_salary a
		RIGHT JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL 
		AND a.shift_type IS NOT NULL
		AND e.is_left=0
		AND e.status=1
		AND a.is_hold=0
		$sql_filter
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC")->num_rows();
     
		$query = $this->db->query("SELECT
			a.*,
			e.joining_date,
			e.bank,
			e.account_no,
			e.ifsc_code
		FROM
			emp_generated_salary a
		RIGHT JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL 
		AND a.shift_type IS NOT NULL
		AND e.is_left=0
		AND e.status=1
		AND a.is_hold=0
		$sql_filter
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC LIMIT $start, $length");
	
		//echo $this->db->last_query();exit();
		
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
				$adj_remark='';
				if($item['adj_remark']!=''){
					$adj_remark='<i class="feather icon-message-circle d-inline text-danger" data-toggle="tooltip" title="'.$item['adj_remark'].'"></i>';
				}
				
                $data[] = array(
                    "sr_no"         		=> ++$start, 
					"joining_date" 			=> ($item['joining_date']!=null ? date("d M, Y", strtotime($item['joining_date'])):'-'),
                    "emp_id"         		=> $item['emp_id'],                           
                    "name"         			=> $item['name'],                           
                    "gender"         		=> $item['gender'],                           
                    "state_name"         	=> $item['state_name'],                           
                    "day_of_month"          => $item['day_of_month'],
                    "shift_type"         	=> $item['shift_type'],
                    "salary_type"         	=> $item['salary_type'],
                    "paid_leave"         	=> $item['paid_leave'],                           
                    "present_day"         	=> $item['present_day'],
                    "absent_day"            => $item['absent_day'],
                    "basic_salary"          => $item['basic_salary'],
                    "hra"         		    => $item['hra'],                            
                    "gross_edu"         	=> $item['gross_edu'],                            
                    "gross_package"         => $item['gross_package'],
                    "gross_salary_earned"   => $item['gross_salary_earned'],
                    "loans_advances"        => $item['loans_advances'],
                    "mobile_loan"         	=> $item['mobile_loan'],
                    "advance_amt"         	=> $item['advance_amt'],
                    "adjustment"         	=> $item['adjustment'].' '.$adj_remark,                           
                    "loan_deduction"        => $item['loan_deduction'],
                    "mobile_deduction"      => $item['mobile_deduction'],
                    "tds"         		    => $item['tds'],                            
                    "pf"         		    => $item['pf'],                            
                    "p_tax"         		=> $item['p_tax'],                            
                    "esic"         		    => $item['esic'],                            
                    "total_deduction"       => $item['total_deduction'],
                    "final_salary"      	=> $item['final_salary'],
                    "bank"      			=> $item['bank'],
                    "account_no"      		=> $item['account_no'],
                    "ifsc_code"      		=> $item['ifsc_code'],
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
	
	
	
	public function get_bank_wise_salary_report(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start  = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['month_id']    = $_REQUEST['month_id'];
       	$filter_data['year']   	    = $_REQUEST['year'];
        $filter_data['bank_id'] 	= $_REQUEST['bank_id'];
        $filter_data['salary_type'] = $_REQUEST['salary_type'];
        
        if(isset($filter_data['salary_type']) && $filter_data['salary_type']!="") :
          $salary_type=$filter_data['salary_type'];
          $sql_filter .=" AND (e.salary_type='$salary_type')"; 
        endif; 
		
        if(isset($filter_data['month_id']) && $filter_data['month_id']!="") :
          $month_id=$filter_data['month_id'];
          $year=$filter_data['year'];
          $sql_filter .=" AND (a.year='$year' AND a.month='$month_id')"; 
        endif; 

		if(isset($filter_data['bank_id']) && $filter_data['bank_id']!="") :
          $bank_id=$filter_data['bank_id'];
		  if($bank_id=='other'){
           $sql_filter .=" AND (e.bank_id NOT IN (20, 21, 10))"; 			  
		  }
		  else{
            $sql_filter .=" AND (e.bank_id='$bank_id')"; 			  
		  }
        endif;  
		
       $total_count=0;
	   if($filter_data['month_id']!='' && $filter_data['bank_id']!=''){
		$total_count = $this->db->query("SELECT
			e.id
		FROM
			emp_generated_salary a
		RIGHT JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL 
		AND a.shift_type IS NOT NULL
		AND e.is_left=0
		AND e.status=1
		AND a.is_hold=0
		$sql_filter
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC")->num_rows();
     
		$query = $this->db->query("SELECT
			a.name,
			a.final_salary,
			e.bank,
			e.account_no,
			e.ifsc_code
		FROM
			emp_generated_salary a
		RIGHT JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL 
		AND a.shift_type IS NOT NULL
		AND e.is_left=0
		AND e.status=1
		AND a.is_hold=0
		$sql_filter
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
		
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $data[] = array(
                    "sr_no"         		=> ++$start,   
                    "name"         			=> $item['name'],   
                    "final_salary"      	=> $item['final_salary'],
                    "bank"      			=> $item['bank'],
                    "account_no"      		=> $item['account_no'],
                    "ifsc_code"      		=> $item['ifsc_code'],
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
	
	
	public function get_staff_salary_summary(){ 
        $params['draw'] = $_REQUEST['draw'];
        $start  = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		
		$year=date('Y', strtotime(date('Y-m-d') . ' -1 month'));
		$month=date('n', strtotime(date('Y-m-d') . ' -1 month'));
     
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
			  RIGHT JOIN candidate e ON a.emp_id = e.emp_id WHERE a.year='$year' AND a.month='$month' AND (e.bank_id NOT IN (20, 21, 10)) AND e.is_left=0 AND e.status=1 AND a.is_hold=0");		
			  if($get_salary->num_rows()>0){
			   $salary=$get_salary->row()->final_salary; 		
			   
			  }
			}
			if($item['bank_id']=='total'){
			 $get_salary = $this->db->query("SELECT COALESCE(SUM(a.final_salary), 0) as final_salary FROM `emp_generated_salary` a
			 RIGHT JOIN candidate e ON a.emp_id = e.emp_id WHERE a.year='$year' AND a.month='$month'  AND e.is_left=0 AND e.status=1 AND a.is_hold=0");		
			 if($get_salary->num_rows()>0){
			  $salary=$get_salary->row()->final_salary; 
			 }	
			}
			else{
			$get_salary = $this->db->query("SELECT COALESCE(SUM(a.final_salary), 0) as final_salary FROM `emp_generated_salary` a
			 RIGHT JOIN candidate e ON a.emp_id = e.emp_id WHERE a.year='$year' AND a.month='$month' AND e.bank_id='$bank_id' AND e.is_left=0 AND e.status=1 AND a.is_hold=0 GROUP BY e.bank_id LIMIT 1");		
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
		
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
	
	
	public function add_loans(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('loans_added_successfully'),
            "url" => base_url('hr/loans'),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));$emp_name=$gstaff['name'];
		
    	$loan_type= $this->input->post('loan_type');
    	$amount= $this->input->post('amount');
    	$instalment= $this->input->post('instalment');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$emi=get_emi($amount, $instalment);	
		
		  
		$check = $this->db->query("SELECT id FROM loans WHERE emp_id='$emp_id' AND loan_type='$loan_type' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_'.$loan_type.'_is_already_ongoing!')
			);        
        
		}
		else{	
			$year = $this->input->post('year');
			$month = $this->input->post('month');
			$month_name = date("F", mktime(0, 0, 0, $month, 1));	
				
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['loan_type']     = $loan_type;
			$data['instalment']    = $instalment;
			$data['amount']   	   = $amount;
			$data['emi']   		   = $emi;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['added_by_id']   = $this->session->userdata('super_user_id');
			$data['added_by_name'] = $this->session->userdata('super_name');
			$data['created_at']    = date("Y-m-d H:i:s");
			
	
		  if($_FILES['attachment']['tmp_name']!=''){
			 $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
			 $tmp_path = $_FILES['attachment']['tmp_name'];
			 if ($tmp_path != ""){ 
				$flag=0; 
				$year = date("Y");
				$month = date("m");
				$day = date("d");
				$directory = "uploads/loans/" . "$year/$month/$day/";
				$bill = date("Ymdhis").'_'. slugify($_FILES['attachment']['name'][$i]) . '.' . $ext;
				if(!is_dir($directory)){ mkdir($directory, 0755, true);}
				$file_url=$directory.$bill;

				if($ext=='pdf'){	
					$data['attachment'] = $file_url;
					move_uploaded_file($tmp_path, $file_url);
				}
				else{
					move_uploaded_file($tmp_path, $file_url);
					$temp_path = $file_url;
					$data['attachment'] = $this->upload_model->img_upload($temp_path, $directory);
					$this->upload_model->delete_temp_image($temp_path);					   
				}
			  }
			}
			
			
			
			$this->db->insert('loans', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('loans_added_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
    
    public function edit_loans($id){
           $resultpost = array(
            "status" => 200,
            "message" => get_phrase('loans_updated_successfully'),
            "url" => base_url('hr/loans/edit/'.$id),
        );   
        
		$row = $this->common_model->getRowById('loans','attachment',array('id'=>$id));
		$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));$emp_name=$gstaff['name'];
		
    	$loan_type= $this->input->post('loan_type');
    	$amount= $this->input->post('amount');
    	$instalment= $this->input->post('instalment');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$emi=get_emi($amount, $instalment);	
		  
		  
		 $check  = $this->db->query("SELECT id FROM loan_repayments WHERE emp_id='$emp_id' AND loan_id='$id' LIMIT 1")->num_rows();    

	  
		$check2 = $this->db->query("SELECT id FROM loans WHERE id!='$id' AND emp_id='$emp_id' AND loan_type='$loan_type' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check2>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_'.$loan_type.'_is_already_ongoing!')
			); 
		}

    	elseif ($check>0) {    		
    		$resultpost = array(
    		 "status" => 400,
    		 "message" => get_phrase('loan_repayments_is_already_started!')
    	   );    		
    	}    	
    	else{	    
			$year = $this->input->post('year');
			$month = $this->input->post('month');
			$month_name = date("F", mktime(0, 0, 0, $month, 1));	
					    
			$data=array();
			$data['emp_id']   	     = $emp_id;
			$data['emp_name']        = $emp_name;
			$data['loan_type']       = $loan_type;
			$data['instalment']      = $instalment;
			$data['amount']   	     = $amount;
			$data['emi']   		     = $emi;
			$data['applied_date']    = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	     = html_escape($this->input->post('remark'));		  
			$data['updated_by_id']   = $this->session->userdata('super_user_id');
			$data['updated_by_name'] = $this->session->userdata('super_name');
			$data['updated_at']      = date("Y-m-d H:i:s");
			
		    if($_FILES['attachment']['tmp_name']!=''){
			 $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
			 $tmp_path = $_FILES['attachment']['tmp_name'];
			 if ($tmp_path != ""){ 
				$flag=0; 
				$year = date("Y");
				$month = date("m");
				$day = date("d");
				$directory = "uploads/loans/" . "$year/$month/$day/";
				$bill = date("Ymdhis").'_'. slugify($_FILES['attachment']['name'][$i]) . '.' . $ext;
				if(!is_dir($directory)){ mkdir($directory, 0755, true);}
				$file_url=$directory.$bill;

				if($ext=='pdf'){	
					$data['attachment'] = $file_url;
					move_uploaded_file($tmp_path, $file_url);
				}
				else{
					move_uploaded_file($tmp_path, $file_url);
					$temp_path = $file_url;
					$data['attachment'] = $this->upload_model->img_upload($temp_path, $directory);
					$this->upload_model->delete_temp_image($temp_path);					   
				}
				$this->upload_model->delete_temp_image($row['attachment']);
			  }
			}
			
			
			$this->db->WHERE('id', $id);
			$this->db->update('loans', $data);
			$this->session->set_flashdata('flash_message', get_phrase('loans_updated_successfully'));
		} 
		
       
       return simple_json_output($resultpost); 
    }
    
	public function delete_loans($id){  
		  $resultpost = array(
            "status" => 200,
            "message" => get_phrase('loans_deleted_successfully'),
            "url" => base_url('hr/loans'),
        );        
        
        $data['is_deleted'] = '1';
        $this->db->where('id', $id);
        $this->db->update('loans',$data);       
        return simple_json_output($resultpost); 
    }
    
	
	public function get_loans(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		
		$data= array(); 
        $sql_filter="";
        $filter_data['keywords']    = $_REQUEST['keywords'];
        $filter_data['salary_type']  = $_REQUEST['salary_type'];
        
     
		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];
            $sql_filter .= " AND (s.salary_type='$salary_type')";
        endif;
        
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
          $keyword=$filter_data['keywords'];
          $sql_filter .=" AND (l.emp_id like '%".$keyword."%'  
            OR l.emp_name like '%" . $keyword . "%'          
            OR l.loan_type like '%" . $keyword . "%')"; 
        endif;  
        			
		$total_count = $this->db->query("SELECT l.id FROM loans AS l INNER JOIN candidate AS s ON l.emp_id=s.emp_id WHERE l.is_deleted=0 AND s.is_left=0 $sql_filter ORDER BY l.id DESC")->num_rows();
		
        $query = $this->db->query("SELECT l.id,l.emp_id,l.emp_name,l.loan_type,l.instalment,l.amount,l.emi,l.amount_paid,l.status,l.added_by_name,l.created_at,s.salary_type,s.staff_type FROM loans AS l INNER JOIN candidate AS s ON l.emp_id=s.emp_id WHERE l.is_deleted=0 AND s.is_left=0 $sql_filter ORDER BY l.id DESC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $emp_id=$item['emp_id'];
              $url=base_url().'hr/loans/edit/'.$item['id'];
              $delete_url="confirm_modal('".base_url()."attendance/loans/delete/".$item['id']."','Are you sure want to delete this!')";
			  
              $history_url=base_url().'hr/loans-history/'.$item['id'];
			  $modal_title=get_phrase($item['loan_type'])." - Repayment History";
			  $history_url = "showAjaxModal('".site_url('modal/popup/modal_loans_history/'.$id)."', '$modal_title')"; 
			  
      
			  
			 $action='<a href="#" onclick="'.$history_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="History"><button type="button" class="btn ml-1 mr-1 mb-1 icon-btn"><i class="fa fa-history" aria-hidden="true"></i> History</button></a>';  
			  
			 $action .='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
             
			 
			 $action .='<a href="#" onclick="'.$delete_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><button type="button" class="btn ml-1 mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
			  
			      
			if($item['status']=='ongoing'){
			 $status='<div class="chip chip-info"><div class="chip-body"><span class="chip-text">Ongoing</span></div></div>';
			}
			else{   
			 $status='<div class="chip chip-success"><div class="chip-body"><span class="chip-text">Paid</span></div></div>';
			}
			
			   $amount_paid=$item['amount_paid']??0;
			 
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "emp_name"		=> $item['emp_name'].' #'.$item['emp_id'], 
					"loan_type"  	=> get_phrase($item['loan_type']),      
					"salary_type"  	=> $item['salary_type'].'-'.$item['staff_type'],      
					"instalment"  	=> $item['instalment'],      
					"amount"  		=> $item['amount'],      
					"emi"  			=> $item['emi'],      
					"amount_paid"  	=> $item['amount_paid']??0,      
					"balance"  	    => $item['amount']-$amount_paid,      
					"status" 		=> $status,      
					"added_by_name" => $item['added_by_name'],      
				    "date" 			=> date("d M, Y h:i A", strtotime($item['created_at'])),
                    "action"       => $action,       
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
	
	
	public function get_hold_salary_report(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start  = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['month_id']    = $_REQUEST['month_id'];
       	$filter_data['year']    = $_REQUEST['year'];
        $filter_data['salary_type'] = $_REQUEST['salary_type'];
        
        if(isset($filter_data['month_id']) && $filter_data['month_id']!="") :
          $month_id=$filter_data['month_id'];
          $year=$filter_data['year'];
          $sql_filter .=" AND (a.year='$year' AND a.month='$month_id')"; 
        endif;           
		
		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];
            $sql_filter .= " AND (a.salary_type='$salary_type')";
        endif;
		
       $total_count=0;
	   if($filter_data['month_id']!='' && $filter_data['salary_type']!=''){
		$total_count = $this->db->query("SELECT
			e.id
		FROM
			emp_generated_salary a
		RIGHT JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL 
		AND a.shift_type IS NOT NULL
		AND e.is_left=0
		AND e.status=1
		AND a.is_hold=1
		$sql_filter
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC")->num_rows();
     
		$query = $this->db->query("SELECT
			a.*,
			e.bank,
			e.account_no,
			e.ifsc_code
		FROM
			emp_generated_salary a
		RIGHT JOIN candidate e ON a.emp_id = e.emp_id
		WHERE a.emp_id IS NOT NULL 
		AND a.shift_type IS NOT NULL
		AND e.is_left=0
		AND e.status=1
		AND a.is_hold=1
		$sql_filter
		GROUP BY
			e.emp_id, e.name
		ORDER BY
		e.emp_id ASC LIMIT $start, $length");
	
		//echo $this->db->last_query();exit();
		
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
			$adj_remark='';
				if($item['adj_remark']!=''){
					$adj_remark='<i class="feather icon-message-circle d-inline text-danger" data-toggle="tooltip" title="'.$item['adj_remark'].'"></i>';
				}
				
                $data[] = array(
                    "sr_no"         		=> ++$start,                                   
                    "emp_id"         		=> $item['emp_id'],                           
                    "name"         			=> $item['name'],                           
                    "gender"         		=> $item['gender'],                           
                    "state_name"         	=> $item['state_name'],                           
                    "day_of_month"          => $item['day_of_month'],
                    "shift_type"         	=> $item['shift_type'],
                    "salary_type"         	=> $item['salary_type'],
                    "paid_leave"         	=> $item['paid_leave'],                           
                    "present_day"         	=> $item['present_day'],
                    "absent_day"            => $item['absent_day'],
                    "basic_salary"          => $item['basic_salary'],
                    "hra"         		    => $item['hra'],                            
                    "gross_edu"         	=> $item['gross_edu'],                            
                    "gross_package"         => $item['gross_package'],
                    "gross_salary_earned"   => $item['gross_salary_earned'],
                    "loans_advances"        => $item['loans_advances'],
                    "mobile_loan"         	=> $item['mobile_loan'],
                    "advance_amt"         	=> $item['advance_amt'],
                    "adjustment"         	=> $item['adjustment'].' '.$adj_remark,                           
                    "loan_deduction"        => $item['loan_deduction'],
                    "mobile_deduction"      => $item['mobile_deduction'],
                    "tds"         		    => $item['tds'],                            
                    "pf"         		    => $item['pf'],                            
                    "p_tax"         		=> $item['p_tax'],                            
                    "esic"         		    => $item['esic'],                            
                    "total_deduction"       => $item['total_deduction'],
                    "final_salary"      	=> $item['final_salary'],
                    "bank"      			=> $item['bank'],
                    "account_no"      		=> $item['account_no'],
                    "ifsc_code"      		=> $item['ifsc_code'],
					
					//ff
                    "working_days"      	=> $item['working_days'],
                    "total_calls"      		=> $item['total_calls'],
                    "calls_done"      		=> $item['calls_done'],
                    "total_dss"      		=> $item['total_dss'],
                    "total_camp"      		=> $item['total_camp'],
                    "calls_after_dss_camp"  => $item['calls_after_dss_camp'],
                    "total_sundays" 		=> $item['total_sundays'],
                    "sys_present_day"  		=> $item['sys_present_day'],
                    "sys_absent_day"  		=> $item['sys_absent_day'],
                    "balance_call"  		=> $item['balance_call'],
                    "no_days_absent"  		=> $item['no_days_absent'],
                    "calls_absent"  		=> $item['calls_absent'],
                    "final_present_day"     => $item['present_day'],
					
                    "total_holidays"        => $item['total_holidays'],
                    "adj_calls_done"        => $item['adj_calls_done'],
                    "adj_present_day"       => $item['adj_present_day'],
					
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
	
	

	public function update_ajax_attendance_status(){ 
        $resultpost = array(
			"status"  => 200,
			"message" =>  "Attendance Updated Successfully !!",
			"url"     => $this->agent->referrer()
		);
	    date_default_timezone_set('Asia/Kolkata');
	    
	    $emp_id   = $this->input->post('emp_id', true);
	    $month_id = $this->input->post('month_id', true);
	    $year 	  = $this->input->post('year', true);
	
		
		$check = $this->db->query("SELECT * FROM emp_attendance WHERE emp_id='$emp_id' AND MONTH(punch_date)='$month_id' AND YEAR(punch_date)='$year'");
		 
		if($emp_id=='' || $month_id=='' || $year==''){
			$resultpost = array(
				"status"  => 400,
				"message" => "Need All details",				
			);
		}
		elseif($check->num_rows()==0){
			  $resultpost = array(
				"status"  => 400,
				"message" =>  "Employee attendance details not found!",				
			);
		}
		else{
			$id_arr = $this->input->post('id', true);
			$status_arr = $this->input->post('status', true);

			for ($i = 0; $i < count($id_arr); $i++) {
				if($id_arr[$i] > 0){				
					$id=$id_arr[$i];
					$status=$status_arr[$i];
					$attendance_arr = array();	
					$attendance_arr = array(			
						'status'     	  	 => $status,
						'status_updated_at'  => date("Y-m-d H:i:s"),
						'status_updated_by'  => $this->session->userdata('super_name'),
					);
					$this->db->where('id',$id);
					$this->db->where('emp_id',$emp_id);
					$this->db->where('MONTH(punch_date)',$month_id);
					$this->db->where('YEAR(punch_date)',$year);
					$this->db->update('emp_attendance', $attendance_arr);                 
				}
			}

		   $old_data=$check->result_array();
		   $logs = array();
		   $logs = array(
			 'parent_id'    => $emp_id,
			 'parent_table' => 'emp_attendance',
			 'json_data'    => json_encode($old_data),
			 'action'       => 'update_status',
		   );
		   $this->common_model->add_hr_logs($logs);			
		}            
       $this->session->set_flashdata('flash_message', "Atendance Updated Successfully !");
       return simple_json_output($resultpost); 
    }
	
	public function is_holiday($date,$staff_type) { 
	    $data=array();
	    $this->db->select('id');	
		$this->db->where('FIND_IN_SET("'.$staff_type.'", staff_typeid) > 0', null, false);
		$this->db->where('DATE(holiday_date)', $date);
		$this->db->where('is_deleted', 0);
		$holiday_list = $this->db->get('holidays'); 
	    return $holiday_list->num_rows();	  
	}
	
	/*Advance Starts*/
	
	public function add_advance(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('advance_added_successfully'),
            "url" => base_url('hr/advance'),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		
    	$amount= $this->input->post('amount');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
    	
				  
		$check=$this->db->query("SELECT id FROM advance WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_advance_is_already_ongoing!')
			);        
        
		}
		else{	
		    $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['amount']   	   = $amount;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['added_by_id']   = $this->session->userdata('super_user_id');
			$data['added_by_name'] = $this->session->userdata('super_name');
			$data['created_at']    = date("Y-m-d H:i:s");
		  
			$this->db->insert('advance', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('advance_added_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
    
     
    public function edit_advance($id){    
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('advance_updated_successfully'),
            "url" => base_url('hr/advance/edit/'.$id),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		
    	$amount= $this->input->post('amount');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
    	
				  
		$check=$this->db->query("SELECT id FROM advance WHERE id!='$id' AND emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_advance_is_already_ongoing!')
			);        
        
		}
		else{	
		   $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['amount']   	   = $amount;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['updated_by_id']   = $this->session->userdata('super_user_id');
			$data['updated_by_name'] = $this->session->userdata('super_name');
			$data['updated_at']      = date("Y-m-d H:i:s");
			
			$this->db->WHERE('id', $id);
			$this->db->update('advance', $data);
			$this->session->set_flashdata('flash_message', get_phrase('advance_updated_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
     
	public function delete_advance($id){ 
		  $row = $this->common_model->getRowById('advance','status',array('id'=>$id));
		  if($row['status']=='ongoing'){
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('advance_deleted_successfully'),
				"url" => base_url('hr/advance'),
			);        
			
			$data['is_deleted'] = '1';
			$this->db->where('id', $id);
			$this->db->update('advance',$data);    
		  } else{	
		     $resultpost = array(
				"status" => 400,
				"message" => get_phrase('advance_amount_is_already_paid!'),
			); 
		}	  
        return simple_json_output($resultpost); 
    }   
	
	public function get_advance(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array();  
        $sql_filter="";
       	$filter_data['month'] 	  = $_REQUEST['month'];
       	$filter_data['year'] 	  = $_REQUEST['year'];
        $filter_data['keywords']  = $_REQUEST['keywords'];
         
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
    	    $keyword=$filter_data['keywords'];
			$sql_filter .=" AND (emp_name like '%".$keyword."%'  
            OR emp_id like '%" . $keyword . "%')"; 
         endif;	
    
        if(isset($filter_data['month']) && isset($filter_data['year']) && $filter_data['month']!="" && $filter_data['year']!="") :
          $year=$filter_data['year'];
          $month=$filter_data['month'];
          $sql_filter .=" AND (year='$year' AND month='$month')"; 
         endif;	
		
        
		$total_count = $this->db->query("SELECT id FROM advance WHERE is_deleted=0  $sql_filter ORDER BY id desc")->num_rows();
     
        $query = $this->db->query("SELECT id,emp_id,emp_name,amount,year,month_name,status,added_by_name,applied_date,created_at FROM advance WHERE is_deleted=0 $sql_filter ORDER BY id DESC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $emp_id=$item['emp_id'];
              $url=base_url().'hr/advance/edit/'.$item['id'];
              $delete_url="confirm_modal('".base_url()."attendance/advance/delete/".$item['id']."','Are you sure want to delete this!')";
			  	  
			 $action='';  
			 $action .='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
           
		   if($item['status']=='ongoing'){			 
			 $action .='<a href="#" onclick="'.$delete_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><button type="button" class="btn ml-1 mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
			 }
			  			      
			if($item['status']=='ongoing'){
			 $status='<div class="chip chip-info"><div class="chip-body"><span class="chip-text">Ongoing</span></div></div>';
			}
			else{   
			 $status='<div class="chip chip-success"><div class="chip-body"><span class="chip-text">Paid</span></div></div>';
			}
			
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "emp_name"		=> $item['emp_name'].' #'.$item['emp_id'],   
					"amount"  		=> $item['amount'],      
					"emi"  			=> $item['emi'],           
					"status" 		=> $status,      
					"added_by_name" => $item['added_by_name'],      
				    "date" 			=> date("d M, Y h:i A", strtotime($item['created_at'])),
				    "applied_date" 	=> date("d M, Y h:i A", strtotime($item['applied_date'])),
				    "advance_deduction" 	=> $item['month_name'].'-'.$item['year'],
                    "action"       => $action,       
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
	
	public function get_advance_amount($emp_id,$month,$year){  
	   $sql_advance = $this->db->query("SELECT id, SUM(amount) as amount FROM advance WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND is_deleted=0  LIMIT 1");
	   if($sql_advance->num_rows()>0){
		  $amount_ = $sql_advance->row()->amount;
	   }
	   else{
		 $amount_=0;
	   }
	  $amount=$amount_ ?? 0;
	  return $amount;
	}  
	/*Advance Ends*/
	
	
	/*Adjustment Starts*/
	
	public function add_adjustment(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('adjustment_added_successfully'),
            "url" => base_url('hr/adjustment'),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		
    	$amount= $this->input->post('amount');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
    	
				  
		$check=$this->db->query("SELECT id FROM emp_adjustment WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_adjustment_is_already_ongoing!')
			);        
        
		}
		else{	
		   $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['amount']   	   = $amount;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['added_by_id']   = $this->session->userdata('super_user_id');
			$data['added_by_name'] = $this->session->userdata('super_name');
			$data['created_at']    = date("Y-m-d H:i:s");
		  
			$this->db->insert('emp_adjustment', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('adjustment_added_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
    
     
    public function edit_adjustment($id){    
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('adjustment_updated_successfully'),
            "url" => base_url('hr/adjustment/edit/'.$id),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		
    	$amount= $this->input->post('amount');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
    	
				  
		$check=$this->db->query("SELECT id FROM emp_adjustment WHERE id!='$id' AND emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_adjustment_is_already_ongoing!')
			);        
        
		}
		else{	
		   $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['amount']   	   = $amount;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['updated_by_id']   = $this->session->userdata('super_user_id');
			$data['updated_by_name'] = $this->session->userdata('super_name');
			$data['updated_at']      = date("Y-m-d H:i:s");
			
			$this->db->WHERE('id', $id);
			$this->db->update('emp_adjustment', $data);
			$this->session->set_flashdata('flash_message', get_phrase('adjustment_updated_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
     
	public function delete_adjustment($id){ 
		  $row = $this->common_model->getRowById('emp_adjustment','status',array('id'=>$id));
		  if($row['status']=='ongoing'){
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('adjustment_deleted_successfully'),
				"url" => base_url('hr/adjustment'),
			);        
			
			$data['is_deleted'] = '1';
			$this->db->where('id', $id);
			$this->db->update('emp_adjustment',$data);    
		  } else{	
		     $resultpost = array(
				"status" => 400,
				"message" => get_phrase('adjustment_amount_is_already_paid!'),
			); 
		}	  
        return simple_json_output($resultpost); 
    }   
	
	public function get_adjustment(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array();  
        $sql_filter="";
       	$filter_data['month'] 	  = $_REQUEST['month'];
       	$filter_data['year'] 	  = $_REQUEST['year'];
        $filter_data['keywords']  = $_REQUEST['keywords'];
         
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
    	    $keyword=$filter_data['keywords'];
			$sql_filter .=" AND (emp_name like '%".$keyword."%'  
            OR emp_id like '%" . $keyword . "%')"; 
         endif;	
    
        if(isset($filter_data['month']) && isset($filter_data['year']) && $filter_data['month']!="" && $filter_data['year']!="") :
          $year=$filter_data['year'];
          $month=$filter_data['month'];
          $sql_filter .=" AND (year='$year' AND month='$month')"; 
         endif;	
		
        
		$total_count = $this->db->query("SELECT id FROM emp_adjustment WHERE is_deleted=0  $sql_filter ORDER BY id desc")->num_rows();
     
        $query = $this->db->query("SELECT id,emp_id,emp_name,amount,year,month_name,status,added_by_name,created_at FROM emp_adjustment WHERE is_deleted=0 $sql_filter ORDER BY id DESC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $emp_id=$item['emp_id'];
              $url=base_url().'hr/adjustment/edit/'.$item['id'];
              $delete_url="confirm_modal('".base_url()."attendance/adjustment/delete/".$item['id']."','Are you sure want to delete this!')";
			  	  
			 $action='';  
			 $action .='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
           
		   if($item['status']=='ongoing'){			 
			 $action .='<a href="#" onclick="'.$delete_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><button type="button" class="btn ml-1 mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
			 }
			  			      
			if($item['status']=='ongoing'){
			 $status='<div class="chip chip-info"><div class="chip-body"><span class="chip-text">Ongoing</span></div></div>';
			}
			else{   
			 $status='<div class="chip chip-success"><div class="chip-body"><span class="chip-text">Paid</span></div></div>';
			}
			
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "emp_name"		=> $item['emp_name'].' #'.$item['emp_id'],   
					"amount"  		=> $item['amount'],      
					"emi"  			=> $item['emi'],           
					"status" 		=> $status,      
					"added_by_name" => $item['added_by_name'],      
				    "date" 			=> date("d M, Y h:i A", strtotime($item['created_at'])),
				    "adjustment_deduction" 	=> $item['month_name'].'-'.$item['year'],
                    "action"       => $action,       
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
	
	public function get_adjustment_amount($emp_id,$month,$year){  
	   $sql_adjustment = $this->db->query("SELECT id, SUM(amount) as amount FROM emp_adjustment WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND is_deleted=0 LIMIT 1");
	   if($sql_adjustment->num_rows()>0){
		  $amount_ = $sql_adjustment->row()->amount;
	   }
	   else{
		 $amount_=0;
	   }
	  $amount=$amount_ ?? 0;
	  return $amount;
	}  
	/*adjustment Ends*/
	
	 
    /*Staff Upcoming Bday Starts*/
	
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
        

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_pure = '1' AND is_doc = '1'  AND is_kyc = '1' AND is_left='0' AND status='1' AND dob IS NOT NULL AND MONTH(dob) = MONTH(CURDATE())  AND DAY(dob) >= DAY(CURDATE()) LIMIT 5")->num_rows();
     
        $query = $this->db->query("SELECT id, emp_id, name, dob, salary_type FROM candidate WHERE is_pure = '1' AND is_doc = '1'  AND is_kyc = '1' AND is_left='0' AND status='1' AND dob IS NOT NULL AND MONTH(dob) = MONTH(CURDATE())  AND DAY(dob) >= DAY(CURDATE()) ORDER BY DAY(dob) ASC  LIMIT 5");
		
		
		
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
         }
		
   
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }
  

    public function get_staff_upcoming_bday(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		
		$year=date('Y', strtotime(date('Y-m-d')));
		$month=date('n', strtotime(date('Y-m-d')));

        $search_value = $_REQUEST['search']['value'];
        $data= array();  
        $total_count=0;
        $sql_filter="";
       	$filter_data['month'] 	  = $_REQUEST['month'];
        $filter_data['keywords']  = $_REQUEST['keywords'];
         
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
    	    $keyword=$filter_data['keywords'];
			$sql_filter .=" AND (emp_name like '%".$keyword."%'  
            OR emp_id like '%" . $keyword . "%')"; 
         endif;	
    
        if(isset($filter_data['month'])  && $filter_data['month']!="") :         
          $month=$filter_data['month'];
          $sql_filter .=" AND (MONTH(dob)='$month')"; 
         endif;	

		if($sql_filter!=''){
		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_pure = '1' AND is_doc = '1' AND is_kyc = '1' AND is_left='0' AND status='1'  $sql_filter ORDER BY MONTH(dob), DAY(dob) ASC")->num_rows();
     
        $query = $this->db->query("SELECT id, emp_id, name, dob, salary_type FROM candidate WHERE is_pure = '1' AND is_doc = '1' AND is_kyc = '1' AND is_left='0' AND status='1'  $sql_filter ORDER BY MONTH(dob), DAY(dob) ASC LIMIT $start, $length");
		

        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $emp_id=$item['emp_id'];
			 
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "emp_name"		=> $item['name'].' #'.$item['emp_id'], 
                    "salary_type"	=> $item['salary_type'], 
				    "dob" 			=> date("d l M, Y", strtotime($item['dob'])),
                    "action"        => $action,       
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
	
	
    /*Staff Upcoming Bday Ends*/
	
	
 public function get_education_bonus(){
        $user_id = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $filter_data['keywords']  = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
        $filter_data['salary_type']  = $_REQUEST['salary_type'];
  

		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];			
            $sql_filter .= " AND (salary_type='$salary_type')";
			if($salary_type=='FIELD STAFF'){
			 $sql_filter .= " AND (doa IS NOT NULL AND TRIM(doa)<>'' AND doa < DATE_SUB(NOW(), INTERVAL 6 YEAR))";	
			}	
			if($salary_type=='OFFICE STAFF'){
			 $sql_filter .= " AND (doa IS NOT NULL AND TRIM(doa)<>'' AND doa < DATE_SUB(NOW(), INTERVAL 6 MONTH))";	
			}			
        endif;
		
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $sql_filter .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR email like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_pure='1' AND is_doc='1' AND is_kyc='1' AND is_left='0' AND status='1'  $sql_filter ORDER BY id desc")->num_rows();
   
        $query = $this->db->query("SELECT id,emp_id,name,phone,email,state_name,city_name,kyc_date,salary_type,doa FROM candidate WHERE  is_pure='1' AND is_doc='1' AND is_kyc='1' AND is_left='0' AND status='1'  $sql_filter ORDER BY DATE(kyc_date) DESC LIMIT $start, $length");
        //  echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id'];   
			   $action=''; 
              
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],                   
                    "emp_id"        => $item['emp_id'],                   
                    "name"			=> $item['name'].' <br/>'. $item['phone'], 
					"email"         => $item['email'],      
					"salary_type"   => $item['salary_type'],      
				    "state"    		=> $item['state_name'],
                    "city"     		=> $item['city_name'],                 
                    "doa"     		=>  date("d l M, Y", strtotime($item['doa'])),
                    "action"        => $action,
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
	
	
	
    public function get_total_edu_bonus_staff($salary_type){
        $user_id = $this->session->userdata('super_user_id');  
        $filter_data['salary_type']  = $salary_type;
		$total_count=0;
		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];			
            $sql_filter .= " AND (salary_type='$salary_type')";
			if($salary_type=='FIELD STAFF'){
			 $sql_filter .= " AND (doa IS NOT NULL AND TRIM(doa)<>'' AND doa < DATE_SUB(NOW(), INTERVAL 6 YEAR))";	
			}	
			if($salary_type=='OFFICE STAFF'){
			 $sql_filter .= " AND (doa IS NOT NULL AND TRIM(doa)<>'' AND doa < DATE_SUB(NOW(), INTERVAL 6 MONTH))";	
			}
			$total_count = $this->db->query("SELECT id FROM candidate WHERE is_pure='1' AND is_doc='1' AND is_kyc='1' AND is_left='0' AND status='1'  $sql_filter ORDER BY id desc")->num_rows();
        endif;

   
        return $total_count;
    }
	
	
	/*paidleave Starts*/
	public function add_paidleave(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('paidleave_added_successfully'),
            "url" => base_url('hr/paidleave'),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		
    	$leaves= $this->input->post('leaves');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
		 
	   $check_pl=$this->check_paid_leaves($emp_id,$leaves,$month,$year);	
		
	   if($check_pl['status']==400){        
		 $resultpost=$check_pl;
       }
	   else{				  
		$check=$this->db->query("SELECT id FROM emp_paidleave WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_paidleave_is_already_ongoing!')
			); 
		}
		else{	
		   $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['leaves']   	   = $leaves;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['added_by_id']   = $this->session->userdata('super_user_id');
			$data['added_by_name'] = $this->session->userdata('super_name');
			$data['created_at']    = date("Y-m-d H:i:s");
		  
			$this->db->insert('emp_paidleave', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('paidleave_added_successfully'));
		}
	   }      
      return simple_json_output($resultpost); 
    }
         
    public function edit_paidleave($id){    
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('paidleave_updated_successfully'),
            "url" => base_url('hr/paidleave/edit/'.$id),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		
    	$leaves= $this->input->post('leaves');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
    	
		$check_pl=$this->check_paid_leaves($emp_id,$leaves,$month,$year);	
				  
		$check=$this->db->query("SELECT id FROM emp_paidleave WHERE id!='$id' AND emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_paidleave_is_already_ongoing!')
			);        
        
		} 
		elseif($check_pl['status']==400){        
		 $resultpost=$check_pl;
        }
		else{	
		   $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['leaves']   	   = $leaves;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['updated_by_id']   = $this->session->userdata('super_user_id');
			$data['updated_by_name'] = $this->session->userdata('super_name');
			$data['updated_at']      = date("Y-m-d H:i:s");
			
			$this->db->WHERE('id', $id);
			$this->db->update('emp_paidleave', $data);
			$this->session->set_flashdata('flash_message', get_phrase('paidleave_updated_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
     
	public function delete_paidleave($id){ 
		  $row = $this->common_model->getRowById('emp_paidleave','status',array('id'=>$id));
		  if($row['status']=='ongoing'){
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('paidleave_deleted_successfully'),
				"url" => base_url('hr/paidleave'),
			);        
			
			$data['is_deleted'] = '1';
			$this->db->where('id', $id);
			$this->db->update('emp_paidleave',$data);    
		  } else{	
		     $resultpost = array(
				"status" => 400,
				"message" => get_phrase('paidleave_amount_is_already_paid!'),
			); 
		}	  
        return simple_json_output($resultpost); 
    }   
	
	public function get_paidleave(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array();  
        $sql_filter="";
       	$filter_data['month'] 	  = $_REQUEST['month'];
       	$filter_data['year'] 	  = $_REQUEST['year'];
        $filter_data['keywords']  = $_REQUEST['keywords'];
         
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
    	    $keyword=$filter_data['keywords'];
			$sql_filter .=" AND (emp_name like '%".$keyword."%'  
            OR emp_id like '%" . $keyword . "%')"; 
         endif;	
    
        if(isset($filter_data['month']) && isset($filter_data['year']) && $filter_data['month']!="" && $filter_data['year']!="") :
          $year=$filter_data['year'];
          $month=$filter_data['month'];
          $sql_filter .=" AND (year='$year' AND month='$month')"; 
         endif;	
		
        
		$total_count = $this->db->query("SELECT id FROM emp_paidleave WHERE is_deleted=0  $sql_filter ORDER BY id desc")->num_rows();
     
        $query = $this->db->query("SELECT id,emp_id,emp_name,leaves,year,month_name,status,added_by_name,created_at FROM emp_paidleave WHERE is_deleted=0 $sql_filter ORDER BY id DESC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $emp_id=$item['emp_id'];
              $url=base_url().'hr/paidleave/edit/'.$item['id'];
              $delete_url="confirm_modal('".base_url()."attendance/paidleave/delete/".$item['id']."','Are you sure want to delete this!')";
			  	  
			 $action='';  
			 $action .='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
           
		   if($item['status']=='ongoing'){			 
			 $action .='<a href="#" onclick="'.$delete_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><button type="button" class="btn ml-1 mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
			 }
			  			      
			if($item['status']=='ongoing'){
			 $status='<div class="chip chip-info"><div class="chip-body"><span class="chip-text">Ongoing</span></div></div>';
			}
			else{   
			 $status='<div class="chip chip-success"><div class="chip-body"><span class="chip-text">Paid</span></div></div>';
			}
			
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "emp_name"		=> $item['emp_name'].' #'.$item['emp_id'],   
					"leaves"  		=> $item['leaves'],                
					"status" 		=> $status,      
					"added_by_name" => $item['added_by_name'],      
				    "date" 			=> date("d M, Y h:i A", strtotime($item['created_at'])),
				    "paidleave_deduction" 	=> $item['month_name'].'-'.$item['year'],
                    "action"       => $action,       
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
	
	public function get_set_paidleave($emp_id,$month,$year){  
	   $sql_paidleave = $this->db->query("SELECT id, SUM(leaves) as leaves FROM emp_paidleave WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND is_deleted=0 LIMIT 1");
	   if($sql_paidleave->num_rows()>0){
		  $leaves_ = $sql_paidleave->row()->leaves;
	   }
	   else{
		 $leaves_=0;
	   }
	  $leaves=$leaves_ ?? 0;
	  return $leaves;
	}  
	/*paidleave Ends*/
	
	
	/*tds Starts*/
	
	public function add_tds(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('tds_added_successfully'),
            "url" => base_url('hr/tds'),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name,is_tds',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		$is_tds=$gstaff['is_tds'];
		
    	$amount= $this->input->post('amount');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
    	
				  
		$check=$this->db->query("SELECT id FROM emp_tds WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_tds_is_already_ongoing!')
			);        
        
		}	
		elseif($is_tds==0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('tds_not_activated_on_this_staff')
			);        
        
		}
		else{	
		   $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['amount']   	   = $amount;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['added_by_id']   = $this->session->userdata('super_user_id');
			$data['added_by_name'] = $this->session->userdata('super_name');
			$data['created_at']    = date("Y-m-d H:i:s");
		  
			$this->db->insert('emp_tds', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('tds_added_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
         
    public function edit_tds($id){    
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('tds_updated_successfully'),
            "url" => base_url('hr/tds/edit/'.$id),
        );        
        
    	$emp_id= $this->input->post('emp_id');		
		$gstaff=$this->common_model->getRowById('candidate','name,is_tds',array('emp_id'=>$emp_id));
		$emp_name=$gstaff['name'];
		$is_tds=$gstaff['is_tds'];
		
    	$amount= $this->input->post('amount');
    	$applied_date = date("Y-m-d", strtotime($this->input->post('applied_date')));
    	$year = $this->input->post('year');
    	$month = $this->input->post('month');
    	
				  
		$check=$this->db->query("SELECT id FROM emp_tds WHERE id!='$id' AND emp_id='$emp_id' AND month='$month' AND year='$year' AND status='ongoing' AND is_deleted=0  LIMIT 1")->num_rows();
		
		if($check>0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('previous_tds_is_already_ongoing!')
			);        
        
		}	
		elseif($is_tds==0){
			 $resultpost = array(
				"status" => 400,
				"message" => get_phrase('tds_not_activated_on_this_staff')
			);        
        
		}
		else{	
		   $month_name = date("F", mktime(0, 0, 0, $month, 1));	
			$data=array();
			$data['emp_id']   	   = $emp_id;
			$data['emp_name']      = $emp_name;
			$data['amount']   	   = $amount;
			$data['applied_date']  = $applied_date;
			$data['year']  		   = $year;
			$data['month']  	   = $month;
			$data['month_name']    = $month_name;
			$data['remark']  	   = html_escape($this->input->post('remark'));
			$data['updated_by_id']   = $this->session->userdata('super_user_id');
			$data['updated_by_name'] = $this->session->userdata('super_name');
			$data['updated_at']      = date("Y-m-d H:i:s");
			
			$this->db->WHERE('id', $id);
			$this->db->update('emp_tds', $data);
			$this->session->set_flashdata('flash_message', get_phrase('tds_updated_successfully'));
		}
       
       return simple_json_output($resultpost); 
    }
     
	public function delete_tds($id){ 
		  $row = $this->common_model->getRowById('emp_tds','status',array('id'=>$id));
		  if($row['status']=='ongoing'){
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('tds_deleted_successfully'),
				"url" => base_url('hr/tds'),
			);        
			
			$data['is_deleted'] = '1';
			$this->db->where('id', $id);
			$this->db->update('emp_tds',$data);    
		  } else{	
		     $resultpost = array(
				"status" => 400,
				"message" => get_phrase('tds_amount_is_already_paid!'),
			); 
		}	  
        return simple_json_output($resultpost); 
    }   
	
	public function get_tds(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array();  
        $sql_filter="";
       	$filter_data['month'] 	  = $_REQUEST['month'];
       	$filter_data['year'] 	  = $_REQUEST['year'];
        $filter_data['keywords']  = $_REQUEST['keywords'];
         
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
    	    $keyword=$filter_data['keywords'];
			$sql_filter .=" AND (emp_name like '%".$keyword."%'  
            OR emp_id like '%" . $keyword . "%')"; 
         endif;	
    
        if(isset($filter_data['month']) && isset($filter_data['year']) && $filter_data['month']!="" && $filter_data['year']!="") :
          $year=$filter_data['year'];
          $month=$filter_data['month'];
          $sql_filter .=" AND (year='$year' AND month='$month')"; 
         endif;	
		
        
		$total_count = $this->db->query("SELECT id FROM emp_tds WHERE is_deleted=0  $sql_filter ORDER BY id desc")->num_rows();
     
        $query = $this->db->query("SELECT id,emp_id,emp_name,amount,year,month_name,status,added_by_name,created_at FROM emp_tds WHERE is_deleted=0 $sql_filter ORDER BY id DESC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $emp_id=$item['emp_id'];
              $url=base_url().'hr/tds/edit/'.$item['id'];
              $delete_url="confirm_modal('".base_url()."attendance/tds/delete/".$item['id']."','Are you sure want to delete this!')";
			  	  
			 $action='';  
			 $action .='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
           
		   if($item['status']=='ongoing'){			 
			 $action .='<a href="#" onclick="'.$delete_url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><button type="button" class="btn ml-1 mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
			 }
			  			      
			if($item['status']=='ongoing'){
			 $status='<div class="chip chip-info"><div class="chip-body"><span class="chip-text">Ongoing</span></div></div>';
			}
			else{   
			 $status='<div class="chip chip-success"><div class="chip-body"><span class="chip-text">Paid</span></div></div>';
			}
			
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "emp_name"		=> $item['emp_name'].' #'.$item['emp_id'],   
					"amount"  		=> $item['amount'],      
					"emi"  			=> $item['emi'],           
					"status" 		=> $status,      
					"added_by_name" => $item['added_by_name'],      
				    "date" 			=> date("d M, Y h:i A", strtotime($item['created_at'])),
				    "tds_deduction" 	=> $item['month_name'].'-'.$item['year'],
                    "action"       => $action,       
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
	
	public function get_tds_amount($emp_id,$month,$year){  
	   $sql_tds = $this->db->query("SELECT id, SUM(amount) as amount FROM emp_tds WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND is_deleted=0 LIMIT 1");
	   if($sql_tds->num_rows()>0){
		  $amount_ = $sql_tds->row()->amount;
	   }
	   else{
		 $amount_=0;
	   }
	  $amount=$amount_ ?? 0;
	  return $amount;
	}  
	/*tds Ends*/
}