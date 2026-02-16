<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hr_model extends CI_Model{
     
    function __construct() {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }

     public function get_staff_category(){
        $resultdata=array();
        $query = $this->db->query("SELECT id, name  FROM em_staff_category WHERE status=1 ORDER BY name asc");
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $item['name'],
              );
        
            }
        }
       return $resultdata;
     }

    public function get_staff_type($category_id){
        $resultdata=array();
        $query = $this->db->query("SELECT id, name FROM `em_staff_type` WHERE parent_id='$category_id' ORDER BY name asc");
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $item['name'],
              );
        
            }
        }
      return $resultdata;
    }
	
    public function get_filter_staff_type(){
        $resultdata=array();
        $query = $this->db->query("SELECT c.name as category,st.id, st.name
		FROM em_staff_category c INNER JOIN em_staff_type st ON c.id = st.parent_id
		ORDER BY c.id,st.name ASC");
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $item['category'].' - '.$item['name'],
              );
        
            }
        }
      return $resultdata;
    }



    public function get_paginated_calls_count($filter_data){
        $keyword_filter         = "";
        $order_type_filter      = "";
        $attendance_date_filter = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword = $filter_data['keywords'];
            $keyword_filter .= " AND (doc.name like '%" . $keyword . "%'
            OR doc.phone like '%" . $keyword . "%'
            OR doc.state_name like '%" . $keyword . "%'
            OR follow.type like '%" . $keyword . "%')";
        endif;
        
        
        
        if (isset($filter_data['c_date']) && $filter_data['c_date'] != "") {
            $c_date                 = date("Y-m-d", strtotime($filter_data['c_date']));
            $c_year                 = date("Y", strtotime($filter_data['c_date']));
            $c_month                = date("n", strtotime($filter_data['c_date']));
            $c_new_date             = date("d F Y, D", strtotime($filter_data['c_date']));
            $attendance_date_filter = " AND (DATE(follow.added_date) = '$c_date')";
        } else {
            $c_date                 = date("Y-m-d", strtotime(date("Y-m-d")));
            $c_year                 = date("Y", strtotime(date("Y-m-d")));
            $c_month                = date("n", strtotime(date("Y-m-d")));
            $c_new_date             = date("d F Y, D", strtotime(date("Y-m-d")));
            $attendance_date_filter = " AND (DATE(follow.added_date) = '$c_date')";
        }
         
        $query = $this->db->query("SELECT doc.id FROM candidate as doc 
                                   INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id 
                                   WHERE doc.is_left='0' AND follow.added_by_id='$user_id'  $keyword_filter $attendance_date_filter ORDER BY follow.id desc");
                                 
        return $query->num_rows();
    }
    
    
    public function get_paginated_calls($filter_data, $per_page, $offset) {
        $resultdata             = array();
        $keyword_filter         = "";
        $attendance_date_filter = "";
        $order_type_filter      = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (doc.name like '%" . $keyword . "%'
            OR doc.phone like '%" . $keyword . "%'
            OR doc.state_name like '%" . $keyword . "%'
            OR follow.type like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['c_date']) && $filter_data['c_date'] != "") {
            $c_date                 = date("Y-m-d", strtotime($filter_data['c_date']));
            $c_year                 = date("Y", strtotime($filter_data['c_date']));
            $c_month                = date("n", strtotime($filter_data['c_date']));
            $c_new_date             = date("d F Y, D", strtotime($filter_data['c_date']));
            $attendance_date_filter = " AND (DATE(follow.added_date) = '$c_date')";
        } else {
            $c_date                 = date("Y-m-d", strtotime(date("Y-m-d")));
            $c_year                 = date("Y", strtotime(date("Y-m-d")));
            $c_month                = date("n", strtotime(date("Y-m-d")));
            $c_new_date             = date("d F Y, D", strtotime(date("Y-m-d")));
            $attendance_date_filter = " AND (DATE(follow.added_date) = '$c_date')";
        }
        
        $query = $this->db->query("SELECT follow.id as follow_id,doc.id,doc.name,doc.phone,doc.state_name,doc.city_name,doc.area_name,follow.remark,follow.type,follow.follow_up_date,follow.follow_up_time,follow.user_type,follow.added_date FROM candidate as doc 
	    INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id 
	    WHERE doc.is_left='0' AND follow.added_by_id='$user_id'  $keyword_filter $attendance_date_filter ORDER BY follow.id desc LIMIT $offset,$per_page");
        // echo $this->db->last_query();
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                
                $resultdata[] = array(
                    "id"             => $item['id'],
                    "follow_id"      => $item['follow_id'],
                    "name"           => $item['name'],
                    "type"           => $item['type'],
                    "phone"          => $item['phone'],
                    "state_name"     => $item['state_name'],
                    "city_name"      => $item['city_name'],
                    "area_name"      => $item['area_name'],
                    "user_type"      => $item['user_type'],
                    "remark"         => $item['remark'],
                    "follow_up_date" => $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-',
                    "follow_up_time" => $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-',
                    "added_date"     => date("h:i A", strtotime($item['added_date']))
                );
            }
        }
        return $resultdata;
    }
    
    public function add_old_calls(){
        $super_type=$this->session->userdata('super_type');
        if($super_type=='HR'){
            $url=base_url('hr/calls');
        }
        else{
            $url=base_url('staff/calls');
        }
        
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('calls_added_successfully'),
            "url" =>$url,
        );
        
        $dob=clean_and_escape($this->input->post('old_date_birth'));
        $doa=clean_and_escape($this->input->post('old_date_anniversary'));
        
        $data=$data_follow=array(); 
        
        if ($_FILES['old_resume']['name'] != "") {
            $fileName        = $_FILES['old_resume']['name'];
            $tmp             = explode('.', $fileName);
            $fileExtension   = end($tmp);
            $uploadable_file = md5(uniqid(rand(), true)) . '.' . $fileExtension;
            
            $year      = date("Y");
            $month     = date("m");
            $day       = date("d");
            //The folder path for our file should be YYYY/MM/DD
            $directory2 = "uploads/resume/" . "$year/$month/$day/";
            if (!is_dir($directory2)) {
                mkdir($directory2, 0755, true);
            }
            
            $data['resume'] = $directory2 . $uploadable_file;
            move_uploaded_file($_FILES['old_resume']['tmp_name'], $directory2 . $uploadable_file);
        }
        	
        $candidate_id     	 = clean_and_escape($this->input->post('candidate_id'));
        $data['is_short']    = clean_and_escape($this->input->post('old_is_short'));
        $data['dob']         = ($dob!=''? $dob:NULL);
        $data['doa']         = ($doa!=''? $doa:NULL);
        $this->db->where('id', $candidate_id);
        $this->db->update('candidate', $data);
        
        if($data['is_short'] == 1){
            $data_follow['action']   = 'short';
        }
        
        $data_follow['candidate_id']   = $candidate_id;
        $data_follow['candidate_type'] = clean_and_escape($this->input->post('old_candidate_type'));
        $data_follow['type']           = clean_and_escape($this->input->post('old_called_type'));
        $data_follow['follow_up_date'] = clean_and_escape($this->input->post('old_followup_date'));
        $data_follow['follow_up_time'] = clean_and_escape($this->input->post('old_followup_time'));
        $data_follow['remark']         = clean_and_escape($this->input->post('old_remark'));
        $data_follow['added_date']     = date("Y-m-d H:i:s");
        $data_follow['added_by_id']    = $this->session->userdata('super_user_id');
        $data_follow['added_by_name']  = $this->session->userdata('super_name');
        $data_follow['user_type']      = $super_type;
        $this->db->insert('candidate_followup', $data_follow);
        
        $this->session->set_flashdata('flash_message', get_phrase('added_calls_successfully'));
        return simple_json_output($resultpost);
    }
    
    public function add_new_calls(){
        $super_type=$this->session->userdata('super_type');
        if($super_type=='HR'){
            $url=base_url('hr/calls');
        }
        else{
            $url=base_url('hr/calls');
        }
        
        $resultpost = array(
            "status" => 200, 
            "message" => get_phrase('calls_added_successfully'),
            "url" => $url,
        );
        
        
        if ($_FILES['resume']['name'] != "") {
            $fileName        = $_FILES['resume']['name'];
            $tmp             = explode('.', $fileName);
            $fileExtension   = end($tmp);
            $uploadable_file = md5(uniqid(rand(), true)) . '.' . $fileExtension;
            
            $year      = date("Y");
            $month     = date("m");
            $day       = date("d");
            //The folder path for our file should be YYYY/MM/DD
            $directory2 = "uploads/resume/" . "$year/$month/$day/";
            if (!is_dir($directory2)) {
                mkdir($directory2, 0755, true);
            }
            
            $data['resume'] = $directory2 . $uploadable_file;
            move_uploaded_file($_FILES['resume']['tmp_name'], $directory2 . $uploadable_file);
        }
            
        $phone = clean_and_escape($this->input->post('new_mobile_no'));
        
        if ($phone != '') {
            $check_phone = $this->check_new_calls_duplication('on_create', 'phone', $phone);
        } else {
            $check_phone = true;
        }
        
        if ($check_phone == false) {
            $resultpost = array(
                "status" => 400,
                "message" => 'Phone Duplication'
            );
        }
		else if(!isValidPhoneNumber($phone)){
			$resultpost = array(
			 "status" => 400,
			 "message" => 'Enter 10 Digit Mobile Number',
			);	
	    }  else {
			
            $dob=clean_and_escape($this->input->post('new_date_birth'));
            $doa=clean_and_escape($this->input->post('new_date_anniversary'));
         
			$unique_code		 =	$this->common_model->candidate_unique_code_manager();			
			       
            $salary_type = clean_and_escape($this->input->post('new_salary_type'));
            $staff_type  = clean_and_escape($this->input->post('new_staff_type'));
			
            $data['staff_catid']  = $salary_type;
            $data['staff_typeid'] = $staff_type;
            $data['salary_type']  = $this->common_model->getNameById('em_staff_category','name',$salary_type);
            $data['staff_type']  = $this->common_model->getNameById('em_staff_type','name',$staff_type);
			
            $data['is_short']    = clean_and_escape($this->input->post('new_is_short'));
            $data['name']        = clean_and_escape($this->input->post('new_candidate_name'));
            $data['phone']       = $phone;
            $state_id            = clean_and_escape($this->input->post('new_state_id'));
            $state_name          = $this->crud_model->get_state_name($state_id);
            $data['state_id']    = $state_id;
            $data['state_name']  = $state_name;
            $city_id             = clean_and_escape($this->input->post('new_city_id'));
            $city_name           = $this->crud_model->get_city_name($city_id);
            $data['city_id']     = $city_id;
            $data['city_name']   = $city_name;
            $area_id             = clean_and_escape($this->input->post('new_area_id'));
            $area_name           = $this->crud_model->get_area_name($area_id);
            $data['area_id']     = $area_id;
            $data['area_name']   = $area_name;
            $data['pincode']     = clean_and_escape($this->input->post('new_pincode'));
            $data['address']     = clean_and_escape($this->input->post('new_address'));
            $data['dob']         = ($dob!=''? $dob:NULL);
            $data['doa']         = ($doa!=''? $doa:NULL);
            $data['date_added']  = date("Y-m-d H:i:s");
            $data['added_by_id']   = $this->session->userdata('super_user_id');
            $data['added_by_name'] = $this->session->userdata('super_name');
            $data['status']        = 1;
            $data['unique_code']   = $unique_code;
           
            $this->db->insert('candidate', $data);
            $insert_id = $this->db->insert_id();
            if($data['is_short'] == 1){
                $data_follow['action']   = 'short';
            }
            $data_follow['candidate_id']   = $insert_id;
            $data_follow['candidate_type'] = clean_and_escape($this->input->post('candidate_type'));
            $data_follow['type']           = clean_and_escape($this->input->post('new_called_type'));
            $data_follow['follow_up_date'] = clean_and_escape($this->input->post('new_followup_date'));
            $data_follow['follow_up_time'] = clean_and_escape($this->input->post('new_followup_time'));
            $data_follow['remark']         = clean_and_escape($this->input->post('new_remark'));
            $data_follow['added_date']     = date("Y-m-d H:i:s");
            $data_follow['added_by_id']    = $this->session->userdata('super_user_id');
            $data_follow['added_by_name']  = $this->session->userdata('super_name');
            $data_follow['user_type']      = $super_type;
            $this->db->insert('candidate_followup', $data_follow);
            
            $this->session->set_flashdata('flash_message', get_phrase('added_calls_successfully'));
        }
        
        return simple_json_output($resultpost);
    }
     
    public function update_calls($id){
		 $resultpost = array(
            "status" => 200,
            "message" => get_phrase('followup_added_successfully'),
            "url" => $this->agent->referrer(),
        );
		
        $super_type=$this->session->userdata('super_type');
		
		if($this->input->post('followup_date')!=''){			
		    $check = $this->db->query("SELECT type FROM candidate_followup WHERE candidate_id=' $id' ORDER BY id DESC LIMIT 1");
			if($check->num_rows()>0){
				$type=$check->row()->type;
			}
			else{
				$type='';				
			}
			
			$data_follow=array();
			$data_follow['action']   = 'followup';
			$data_follow['candidate_id']   = $id;
			$data_follow['candidate_type'] = '';
			$data_follow['type']           = $type;
			$data_follow['follow_up_date'] = clean_and_escape($this->input->post('followup_date'));
			$data_follow['follow_up_time'] = clean_and_escape($this->input->post('followup_time'));
			$data_follow['remark']         = $this->input->post('remark');
			$data_follow['added_date']     = date("Y-m-d H:i:s");
			$data_follow['added_by_id']    = $this->session->userdata('super_user_id');
			$data_follow['added_by_name']  = $this->session->userdata('super_name');
			$data_follow['user_type']      = $super_type;
			$insert=$this->db->insert('candidate_followup', $data_follow); 
			$this->session->set_flashdata('flash_message', get_phrase('followup_added_successfully'));  
		}
		else{
		   $resultpost = array(
			"status" => 400,
			"message" => get_phrase('add_followup_date!'),
		   );		
	  }
          
       return simple_json_output($resultpost); 
    }
    
    public function edit_calls($id){
        $data_follow=array();
        $data_follow['remark']  = clean_and_escape($this->input->post('remark'));
        $this->db->where('id', $id);
        $this->db->update('candidate_followup', $data_follow); 	
        $this->session->set_flashdata('flash_message', get_phrase('remark_updated_successfully'));
    }
    
    public function check_new_calls_duplication($action = "", $field = "", $phone = "", $user_id = "") {
        $duplicate_phone_check = $this->db->get_where('candidate', array(
            $field => $phone
        ));
        
        if ($action == 'on_create') {
            if ($duplicate_phone_check->num_rows() > 0) {
                return false;
            } else {
                return true;
            }
        } elseif ($action == 'on_update') {
            if ($duplicate_phone_check->num_rows() > 0) {
                if ($duplicate_phone_check->row()->id == $user_id) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }
  
  public function get_paginated_today_followup_count($filter_data)    {
        $asm_present_filter = "";
        $keyword_filter     = "";
        $resultdata         = array();
        $current_date       = date('Y-m-d');
        $current_time       = date('h:i a');

        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $keyword_filter = " AND (doc.name like '%" . $keyword . "%'
            OR doc.phone like '%" . $keyword . "%'
            OR doc.state_name like '%" . $keyword . "%'
            OR follow.type like '%" . $keyword . "%')";
        endif;

        $coordinator_id = $this->session->userdata('super_user_id');
        $query          = $this->db->query("SELECT doc.id,follow.follow_up_date,follow.follow_up_time,follow.candidate_id as doc_id
	    FROM candidate as doc
	    INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id
	    WHERE doc.is_left='0' AND follow.added_by_id='$coordinator_id' $keyword_filter ORDER BY follow.id DESC, follow.follow_up_time DESC");
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
        return count($resultdata);
    }

   public function get_paginated_today_followup($filter_data, $per_page, $offset){
        $asm_present_filter = "";
        $keyword_filter     = "";
        $resultdata         = array();
        $current_time       = date('h:i a');
        $current_date       = date('Y-m-d');

        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $keyword_filter = " AND (doc.name like '%" . $keyword . "%'
            OR doc.phone like '%" . $keyword . "%'
            OR doc.state_name like '%" . $keyword . "%'
            OR follow.type like '%" . $keyword . "%')";
        endif;

        $coordinator_id = $this->session->userdata('super_user_id');
        $query= $this->db->query("SELECT doc.id,doc.name,follow.candidate_id as doc_id,doc.phone,follow.type,follow.follow_up_date,follow.follow_up_time
	    FROM candidate as doc
	    INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id
	    WHERE doc.is_left='0' AND follow.added_by_id='$coordinator_id'  $keyword_filter  ORDER BY follow.id  DESC, follow.follow_up_time DESC LIMIT $offset,$per_page");

        $master_id = array();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                if (!in_array($item['doc_id'], $master_id)) {
                    $master_id[] = $item['doc_id'];
                    if ($item['follow_up_date'] == $current_date) {
                        $resultdata[] = array(
                            "id" => $item['id'],
                            "doctor_id" => $item['doc_id'],
                            "name" => $item['name'],
                            "type" => $item['type'],
                            "phone" => $item['phone'],
                            "follow_up_date" => $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-',
                            "follow_up_time" => $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-'
                        );
                    }

                }
            }
        }
        return $resultdata;
    }
	
    public function get_paginated_other_followup_count($filter_data){
        $asm_present_filter = "";
        $resultdata         = array();
        $current_date       = date('Y-m-d');
        $keyword_filter     = "";
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword = $filter_data['keywords'];
            $keyword_filter .= " AND (doc.name like '%" . $keyword . "%' or doc.phone like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['user_type']) && $filter_data['user_type'] != ""):
            $user_type = $filter_data['user_type'];
            $keyword_filter = " AND (doc.name like '%" . $keyword . "%'
            OR doc.phone like '%" . $keyword . "%'
            OR doc.state_name like '%" . $keyword . "%'
            OR follow.type like '%" . $keyword . "%')";
        endif;
        
        $coordinator_id = $this->session->userdata('super_user_id');
        $query = $this->db->query("SELECT doc.id
	    FROM candidate as doc 
	    INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id 
	    WHERE doc.is_left='0' AND follow.added_by_id='$coordinator_id' and DATE(follow.follow_up_date) >'$current_date'  $keyword_filter ");
        return $query->num_rows();
    }
    
    public function get_paginated_other_followup($filter_data, $per_page, $offset)    {
        $resultdata     = array();
        $current_time   = date('h:i a');
        $current_date   = date('Y-m-d');
        $keyword_filter = "";
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword = $filter_data['keywords'];
            $keyword_filter = " AND (doc.name like '%" . $keyword . "%'
            OR doc.phone like '%" . $keyword . "%'
            OR doc.state_name like '%" . $keyword . "%'
            OR follow.type like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['user_type']) && $filter_data['user_type'] != ""):
            $user_type = $filter_data['user_type'];
            $keyword_filter .= " AND (follow.user_type like '" . $user_type . "')";
        endif;
        
        $coordinator_id = $this->session->userdata('super_user_id');
        $query = $this->db->query("SELECT doc.id,doc.name,doc.phone,follow.type,follow.follow_up_date,follow.follow_up_time,follow.added_date 
	    FROM candidate as doc 
	    INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id 
	    WHERE doc.is_left='0' AND follow.added_by_id='$coordinator_id' and DATE(follow.follow_up_date) >'$current_date' $keyword_filter ORDER BY follow.follow_up_date ASC, follow.follow_up_time ASC  LIMIT $offset,$per_page");
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                    "id" => $item['id'],
                    "name" => $item['name'],
                    "type" => $item['type'],
                    "phone" => $item['phone'],
                    "follow_up_date" => $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-',
                    "follow_up_time" => $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-',
                    "added_date" => date("m-d-Y h:i A", strtotime($item['added_date']))
                );
            }
        }
        return $resultdata;
    }
    
    
    public function ajax_candidate_list(){
    if(!isset($_POST['searchTerm'])){
       $query = $this->db->query("SELECT id, name, phone  FROM `candidate` WHERE is_left='0' AND is_kyc='0' ORDER BY name asc LIMIT 10");
       $doctors_list=$query->result_array();
    
    }else{
    	$search = $_POST['searchTerm'];
    	$query = $this->db->query("SELECT id, name, phone  FROM `candidate` WHERE is_left='0' AND is_kyc='0' AND CONCAT(name, phone) LIKE '%".$search."%' ORDER BY name asc LIMIT 10");
        $doctors_list=$query->result_array();
    }
    $response = array();
    foreach($doctors_list as $item){
    	$response[] = array(
    		"id" => $item['id'],
    		"text" => $item['name'].' '.$item['phone']
    	);
    }
     echo json_encode($response);
   } 

    public function ajax_pure_candidate_list(){
    if(!isset($_POST['searchTerm'])){
       $query = $this->db->query("SELECT emp_id, name, phone  FROM `candidate` WHERE is_left='0' AND emp_id IS NOT NULL AND is_kyc=1 ORDER BY name asc LIMIT 10");
       $doctors_list=$query->result_array();
    
    }else{
    	$search = $_POST['searchTerm'];
    	$query = $this->db->query("SELECT emp_id, name, phone  FROM `candidate` WHERE is_left='0' AND emp_id IS NOT NULL AND is_kyc=1 AND CONCAT(name, phone) LIKE '%".$search."%' ORDER BY name asc LIMIT 10");
        $doctors_list=$query->result_array();
    }
    $response = array();
    foreach($doctors_list as $item){
    	$response[] = array(
    		"id" => $item['emp_id'],
    		"text" => $item['name'].' '.$item['phone']
    	);
    }
     echo json_encode($response);
   } 
   
   public function get_ajax_candidate_id($doctor_id){
		if($doctor_id==''){ 
		  header('Content-Type: application/json');
		  echo json_encode(array('status' => 400, 'message' => 'Error! Doctor details not found, try later!')); 
		}
		else{
	    $query = $this->db->query("SELECT id,is_short,resume,staff_type,phone,state_id,state_name,city_id,city_name,area_id,area_name,pincode,address,dob,doa,salary_type FROM candidate WHERE id='$doctor_id' ORDER BY id desc");
        if(!empty($query)){   
            $item=$query->row_array();	
            $dob_=$item['dob'];
            $doa_=$item['doa'];
            if($dob_!='' && $dob_!='0000-00-00'){
              $dob=date("Y-m-d", strtotime($dob_));
            }
            else{
              $dob='';   
            }  
            
            if($doa_!='' && $doa_!='0000-00-00'){
              $doa=date("Y-m-d", strtotime($doa_));
            }
            else{
              $doa='';   
            }
            
            $resultdata = array(	
                "id" => $item['id'],
                "phone" => $item['phone'],
                "state_id" => $item['state_id'],
                "state_name" => $item['state_name'],
                "city_id" => $item['city_id'],			
                "city_name" => $item['city_name'],			
                "address" => $item['address'],	
                "area_id" => $item['area_id'],	
                "area_name" => $item['area_name'],
                "pincode" => $item['pincode'],
                "staff_type" => $item['staff_type'],
                "salary_type" => $item['salary_type'],
                "is_short" => $item['is_short'],
                "resume" => ($item['resume'] != '' && $item['resume']  != NULL) ? base_url().$item['resume']  : (''),
                "birthday"    => $dob,
                "anniversary" => $doa,
            );
        
     
            header('Content-Type: application/json');
            echo json_encode(array('status' => 200,'message' => 'success','data' => $resultdata)); 
		}
		else{
		  header('Content-Type: application/json');
		  echo json_encode(array('status' => 400, 'message' => 'Error! Vessal details not found, try later!')); 
		}			
     }
	}
	
	
	public function get_timeline($doctor_id){
        $resultdata = array();
        $logs_data  = array();
        $user_id    = $this->session->userdata('super_user_id');
        $query      = $this->db->query("SELECT id,follow_up_date, follow_up_time,remark,added_by_name,added_date,action FROM `candidate_followup` 
        WHERE candidate_id='$doctor_id' order by id desc");
		/*echo $this->db->last_query();
		exit();*/
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $date = date("d M, Y", strtotime($item['follow_up_date'])) . ' ' . date("h:i A", strtotime($item['follow_up_time']));
                $action = $item['action'];
                $title = '';
                if($action == 'short'){
                    $title = ' - Shortlisted';
                }elseif($action == 'reject'){
                    $title = ' - Rejected';
                }elseif($action == 'schedule'){
                    $title = ' - Interview Schedule';
                }elseif($action == 're-schedule'){
                    $title = ' - Interview Re-Schedule';
                }elseif($action == 'selected'){
                    $title = ' - Selected';
                }else{
                    $title = '';
                }
                
                $logs_data[] = array(
                    "id"             => $item['id'],
                    "type"           => "",
                    "action"        => $action,
                    "remark"         => $item['remark'] != null ? $item['remark'] : '-',
                    "follow_up_date" => $item['follow_up_date'] != "0000-00-00" ? date('d-m-Y', strtotime($item['follow_up_date'])) : '-',
                    "follow_up_time" => $item['follow_up_time'] != "00:00:00" ? date('h:i A', strtotime($item['follow_up_time'])) : '-',
                    "date"           => $date,
                    "name"           => ($item['added_by_name']!=''?$item['added_by_name']:'-'),
                    "added_date"     => date('d-m-Y h:i A', strtotime($item['added_date'])).$title
                );
            }
        }
        $data=array_merge($logs_data);
        return $data;
    }
    
    
    public function get_paginated_candidate_count($filter_data){
        $keyword_filter         = "";
        $order_type_filter      = "";
        $attendance_date_filter = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
         
        $query = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_short='0' $keyword_filter ORDER BY id desc");
                                 
        return $query->num_rows();
    }
    
    
    public function get_paginated_candidate($filter_data, $per_page, $offset) {
        $resultdata             = array();
        $keyword_filter         = "";
        $attendance_date_filter = "";
        $order_type_filter      = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
        
        $query = $this->db->query("SELECT id,resume,staff_type,salary_type,name,phone,state_name,city_name,area_name,added_by_name,date_added FROM candidate WHERE is_left='0' AND is_short='0' $keyword_filter ORDER BY id desc LIMIT $offset,$per_page");
        //echo $this->db->last_query();
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                
                $resultdata[] = array(
                    "id"             => $item['id'],
                    "resume"		 => ($item['resume'] != '' && $item['resume']  != NULL) ? base_url().$item['resume']  : (''),
                    "staff_type"      => $item['salary_type'].'-'.$item['staff_type'],
                    "salary_type"      => $item['salary_type'],
                    "name"           => $item['name'],
                    "phone"          => $item['phone'],
                    "state_name"     => $item['state_name'],
                    "city_name"      => $item['city_name'],
                    "area_name"      => $item['area_name'],
                    "added_by_name"  => $item['added_by_name'],
                    "added_date"     => date("D d, F Y h:i A", strtotime($item['date_added']))
                );
            }
        }
        return $resultdata;
    }
    
    public function get_candidate_by_id($id){
        $this->db->where('id', $id);
        return $this->db->get('candidate');
    }
    
    public function get_candidate_document_by_id($id){
        $this->db->where('candidate_id', $id);
        return $this->db->get('candidate_document');
    }
    
    public function edit_candidate($id){
        $super_type=$this->session->userdata('super_type');
        if($super_type=='HR'){
            $url=base_url('hr/candidate');
        }
        else{
            $url=base_url('hr/candidate');
        }
        
        $resultpost = array(
            "status" => 200, 
            "message" => get_phrase('candidate_updated_successfully'),
            "url" => $url,
        );
        
        $data=array();
        if ($_FILES['resume']['name'] != "") {
            $fileName        = $_FILES['resume']['name'];
            $tmp             = explode('.', $fileName);
            $fileExtension   = end($tmp);
            $uploadable_file = md5(uniqid(rand(), true)) . '.' . $fileExtension;
            
            $year      = date("Y");
            $month     = date("m");
            $day       = date("d");
            //The folder path for our file should be YYYY/MM/DD
            $directory2 = "uploads/resume/" . "$year/$month/$day/";
            if (!is_dir($directory2)) {
                mkdir($directory2, 0755, true);
            }
            
            $data['resume'] = $directory2 . $uploadable_file;
            move_uploaded_file($_FILES['resume']['tmp_name'], $directory2 . $uploadable_file);
        }
            
        $phone = clean_and_escape($this->input->post('mobile_no'));
        $user_id = $this->session->userdata('super_user_id');
        if ($phone != '') {
            $check_phone = $this->check_new_calls_duplication('on_update', 'phone', $phone,$id);
        } else {
            $check_phone = true;
        }
        
        if ($check_phone == false) {
            $resultpost = array(
                "status" => 400,
                "message" => 'Phone Duplication'
            );
        }
		else if(!isValidPhoneNumber($phone)){
			$resultpost = array(
			 "status" => 400,
			 "message" => 'Enter 10 Digit Mobile Number',
			);	
	    }  else {
            
            $dob=clean_and_escape($this->input->post('date_birth'));
            $doa=clean_and_escape($this->input->post('date_anniversary'));
         
            $salary_type = clean_and_escape($this->input->post('salary_type'));
            $staff_type  = clean_and_escape($this->input->post('staff_type'));
			
            $data['staff_catid']  = $salary_type;
            $data['staff_typeid'] = $staff_type;
            $data['salary_type']  = $this->common_model->getNameById('em_staff_category','name',$salary_type);
            $data['staff_type']  = $this->common_model->getNameById('em_staff_type','name',$staff_type);
            $data['name']        = clean_and_escape($this->input->post('candidate_name'));
            $data['phone']       = $phone;
            $state_id            = clean_and_escape($this->input->post('state_id'));
            $state_name          = $this->crud_model->get_state_name($state_id);
            $data['state_id']    = $state_id;
            $data['state_name']  = $state_name;
            $city_id             = clean_and_escape($this->input->post('city_id'));
            $city_name           = $this->crud_model->get_city_name($city_id);
            $data['city_id']     = $city_id;
            $data['city_name']   = $city_name;
            $area_id             = clean_and_escape($this->input->post('area_id'));
            $area_name           = $this->crud_model->get_area_name($area_id);
            $data['area_id']     = $area_id;
            $data['area_name']   = $area_name;
            $data['pincode']     = clean_and_escape($this->input->post('pincode'));
            $data['address']     = clean_and_escape($this->input->post('address'));
            $data['dob']         = ($dob!=''? $dob:NULL);
            $data['doa']         = ($doa!=''? $doa:NULL);
			$data['updated_by_id']    = $this->session->userdata('super_user_id');
			$data['updated_by_name']  = $this->session->userdata('super_name');
           
            $this->db->where('id',$id);
            $this->db->update('candidate', $data);
            
            $this->session->set_flashdata('flash_message', get_phrase('candidate_updated_successfully'));
        }
        
        return simple_json_output($resultpost);
    }
    
    public function get_paginated_shortlist_count($filter_data){
        $keyword_filter         = "";
        $order_type_filter      = "";
        $attendance_date_filter = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
         
        $query = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_short='1' and is_selected='0' AND interview_date IS NULL $keyword_filter ORDER BY id desc");
                                 
        return $query->num_rows();
    }
    
    
    public function get_paginated_shortlist($filter_data, $per_page, $offset) {
        $resultdata             = array();
        $keyword_filter         = "";
        $attendance_date_filter = "";
        $order_type_filter      = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
        
        $query = $this->db->query("SELECT id,resume,staff_type,name,phone,state_name,city_name,area_name,added_by_name,date_added FROM candidate WHERE is_left='0' AND is_short='1' and is_selected='0' AND interview_date IS NULL $keyword_filter ORDER BY id desc LIMIT $offset,$per_page");
        // echo $this->db->last_query();
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                
                $resultdata[] = array(
                    "id"             => $item['id'],
                    "resume" => ($item['resume'] != '' && $item['resume']  != NULL) ? base_url().$item['resume']  : (''),
                    "staff_type"      =>  $item['salary_type'].'-'.$item['staff_type'],
                    "name"           => $item['name'],
                    "phone"          => $item['phone'],
                    "state_name"     => $item['state_name'],
                    "city_name"      => $item['city_name'],
                    "area_name"      => $item['area_name'],
                    "added_by_name"  => $item['added_by_name'],
                    "added_date"     => date("D d, F Y h:i A", strtotime($item['date_added']))
                );
            }
        }
        return $resultdata;
    }
    
    public function schedule_shortlist($id){
        $super_type=$this->session->userdata('super_type');
        $candidate_id      = $id;
        $data['is_short']        = '1';
        $this->db->where('id', $candidate_id);
        $this->db->update('candidate', $data);
        
		$data_follow = array();
		$data_follow = array(
			'action' => 'short',
			'candidate_id' => $candidate_id,
			'remark' => $this->input->post('remark'),
			'added_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'user_type' => $super_type
		);

		$this->db->insert('candidate_followup', $data_follow);

		
        $this->session->set_flashdata('flash_message', get_phrase('shortlisted_successfully'));
    }
    
    public function schedule_interview($id){
        $super_type=$this->session->userdata('super_type');
        $candidate_id      = $id;
        $data['interview_date']        = clean_and_escape($this->input->post('interview_date'));
        $data['interview_time']        = clean_and_escape($this->input->post('interview_time'));
        $this->db->where('id', $candidate_id);
        $this->db->update('candidate', $data);
        
       
		$data_follow = array();
		$data_follow = array(
			'action' => 'schedule',
			'candidate_id' => $candidate_id,
			'follow_up_date' => clean_and_escape($this->input->post('interview_date')),
			'follow_up_time' => clean_and_escape($this->input->post('interview_time')),
			'remark' => $this->input->post('remark'),
			'added_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'user_type' => $super_type
		);
		$this->db->insert('candidate_followup', $data_follow);

		
        $this->session->set_flashdata('flash_message', get_phrase('interview_schedule_successfully'));
    }
    
    
    public function reject_interview($id){
        $super_type=$this->session->userdata('super_type');
        $candidate_id      			   = $id;
        $data['is_short']        	   = '0';
        $data['is_selected']       	   = '0';
        $data['interview_date']        = null;
        $data['interview_time']        = null;
        $this->db->where('id', $candidate_id);
        $this->db->update('candidate', $data);
        

		$data_follow = array();
		$data_follow = array(
			'action' => 'reject',
			'candidate_id' => $candidate_id,
			'remark' => $this->input->post('remark'),
			'added_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'user_type' => $super_type
		);
		$this->db->insert('candidate_followup', $data_follow);

		
        $this->session->set_flashdata('flash_message', get_phrase('interview_reject_successfully'));
    }
    
    public function get_paginated_interview_schedule_count($filter_data){
        $keyword_filter         = "";
        $order_type_filter      = "";
        $attendance_date_filter = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
         
        $query = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_short='1' AND is_selected='0' AND interview_date IS NOT NULL $keyword_filter ORDER BY id desc");
                                 
        return $query->num_rows();
    }
    
    
    public function get_paginated_interview_schedule($filter_data, $per_page, $offset) {
        $resultdata             = array();
        $keyword_filter         = "";
        $attendance_date_filter = "";
        $order_type_filter      = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
        
        $query = $this->db->query("SELECT id,interview_date,interview_time,resume,salary_type,staff_type,name,phone,state_name,city_name,area_name,added_by_name,date_added FROM candidate WHERE is_left='0' AND is_short='1' AND is_selected='0' AND interview_date IS NOT NULL $keyword_filter ORDER BY id desc LIMIT $offset,$per_page");
        //echo $this->db->last_query();
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                
                $resultdata[] = array(
                    "id"             => $item['id'],
                    "resume" => ($item['resume'] != '' && $item['resume']  != NULL) ? base_url().$item['resume']  : (''),
                    "staff_type"      =>  $item['salary_type'].'-'.$item['staff_type'],
                    "name"           => $item['name'],
                    "phone"          => $item['phone'],
                    "state_name"     => $item['state_name'],
                    "city_name"      => $item['city_name'],
                    "area_name"      => $item['area_name'],
                    "added_by_name"  => $item['added_by_name'],
                    "added_date"     => date("D d, F Y h:i A", strtotime($item['date_added'])),
                    "interview_date"     => date("D d, F Y", strtotime($item['interview_date'])),
                    "interview_time"     => date("h:i A", strtotime($item['interview_time'])),
                );
            }
        }
        return $resultdata;
    }
    
    public function re_schedule_interview($id){
        $super_type=$this->session->userdata('super_type');
        $candidate_id      		= $id;
        $data['interview_date'] = clean_and_escape($this->input->post('interview_date'));
        $data['interview_time'] = clean_and_escape($this->input->post('interview_time'));
        $this->db->where('id', $candidate_id);
        $this->db->update('candidate', $data);
        

		$data_follow = array();
		$data_follow = array(
			'action' => 're-schedule',
			'candidate_id' => $candidate_id,
			'follow_up_date' => clean_and_escape($this->input->post('interview_date')),
			'follow_up_time' => clean_and_escape($this->input->post('interview_time')),
			'remark' => $this->input->post('remark'),
			'added_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'user_type' => $super_type
		);

		$this->db->insert('candidate_followup', $data_follow);
		
        $this->session->set_flashdata('flash_message', get_phrase('interview_schedule_successfully'));
    }
    
    public function accept_interview($id){
        $super_type=$this->session->userdata('super_type');
        $candidate_id      = $id;
        $data['interview_date']        = null;
        $data['interview_time']        = null;
        $data['is_selected']        = '1';
        $this->db->where('id', $candidate_id);
        $this->db->update('candidate', $data);
            	
		$data_follow = array();
		$data_follow = array(
			'action' => 'selected',
			'candidate_id' => $candidate_id,
			'remark' => $this->input->post('remark'),
			'added_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'user_type' => $super_type
		);
		$insert = $this->db->insert('candidate_followup', $data_follow);
		
		if($insert){            
          $query = $this->db->query("SELECT phone,unique_code FROM candidate WHERE id='$id' limit 1");        
          if($query->num_rows()>0){
			$item = $query->row_array();			
			$unique_code    = $item['unique_code'];	
			$mobile = $item['phone'];
			$template_id = "1207168666286125547";		
			$link  = 'crm.rhipl.in/cd/'.$unique_code.'';
			$message="We are pleased to welcome you in Rajasthan Aushdhalaya Pvt. Ltd.
			Please use the below link to upload the necessary Documents for the further process.
			".$link."
			Regards,
			HR Department.";		
			$this->auth_model->send_sms($message,$template_id,$mobile);			
			
			
			 $template_name="documentation";
			 $sender_mobile=$mobile;
			 $wati_parameters = array();	
			 $wati_parameters[] = array(
				'name' => "tracking_url",
				'value' => $link,
			  );	
			  
			  $wati_array = array();
			  $wati_array = array(
				'template_name' => $template_name,
				'broadcast_name' => $template_name,
				'parameters' => $wati_parameters,
			  );

			 $this->auth_model->send_wati_sms($sender_mobile,$wati_array);
			
		 }			
		}
		
        $this->session->set_flashdata('flash_message', get_phrase('candidate_selected_successfully'));
    }
    
    public function get_paginated_documentation_count($filter_data){
        $keyword_filter         = "";
        $order_type_filter      = "";
        $attendance_date_filter = "";
        $user_id                = $this->session->userdata('super_user_id');
        $is_pure=$filter_data['is_pure'];
        $is_doc=$filter_data['is_doc'];
		
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
        $keyword_filter  .=" AND is_pure='$is_pure'";
        $keyword_filter  .=" AND is_doc='$is_doc' AND is_kyc='0'";
		 
        $query = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_short='1' AND is_selected='1' AND is_traning='0' $keyword_filter ORDER BY id desc");
                                 
        return $query->num_rows();
    }
    
    
    public function get_paginated_documentation($filter_data, $per_page, $offset) {
        $resultdata             = array();
        $keyword_filter         = "";
        $attendance_date_filter = "";
        $order_type_filter      = "";
        $user_id                = $this->session->userdata('super_user_id');
        $is_pure=$filter_data['is_pure'];
        $is_doc=$filter_data['is_doc'];
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
		
        $keyword_filter  .=" AND is_pure='$is_pure'";
        $keyword_filter  .=" AND is_doc='$is_doc'";
		 
        
        $query = $this->db->query("SELECT id,interview_date,interview_time,resume,salary_type,staff_type,name,phone,state_name,city_name,area_name,date_added,added_by_name,unique_code FROM candidate WHERE is_left='0' AND is_short='1' AND is_selected='1' AND is_traning='0' $keyword_filter ORDER BY id desc LIMIT $offset,$per_page");
        //echo $this->db->last_query();
        
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {	
				$unique_code = $item['unique_code'];	
                $link='';
				$link  = 'https://crm.rhipl.in/cd/'.$unique_code.'';
                $resultdata[] = array(
                    "id"             => $item['id'],
                    "resume" 		 => ($item['resume'] != '' && $item['resume']  != NULL) ? base_url().$item['resume']  : (''),
                    "staff_type"     => $item['salary_type'].'-'.$item['staff_type'],
                    "name"           => $item['name'],
                    "phone"          => $item['phone'],
                    "state_name"     => $item['state_name'],
                    "city_name"      => $item['city_name'],
                    "area_name"      => $item['area_name'],
                    "added_by_name"  => $item['added_by_name'],
                    "link"  		 => $link,
                    "added_date"     => date("D d, F Y h:i A", strtotime($item['date_added'])),
                    "interview_date" => date("D d, F Y", strtotime($item['interview_date'])),
                    "interview_time" => date("h:i A", strtotime($item['interview_time'])),
                );
            }
        }
        return $resultdata;
    }



   public function send_sms_link($id){
        $resultpost = array(
          'status' => 200,
          'message' => 'SMS Sent Successfully!',
          'url' => $this->agent->referrer(),
        );
        
        date_default_timezone_set('Asia/Kolkata');
        $date = date('Y-m-d');
        $query = $this->db->query("SELECT name,phone,email,unique_code FROM candidate WHERE id='$id' limit 1");
        
        if($query->num_rows()>0){
        $item = $query->row_array();
        
		$unique_code    = $item['unique_code'];	
		$mobile = $item['phone'];
		$template_id = "1207168666286125547";
	
		$link  = 'crm.rhipl.in/cd/'.$unique_code.'';
		$message="We are pleased to welcome you in Rajasthan Aushdhalaya Pvt. Ltd.
		Please use the below link to upload the necessary Documents for the further process.
		".$link."
		Regards,
		HR Department.";
		
		 $template_name="documentation";
		 $sender_mobile=$mobile;
		 $wati_parameters = array();	
		 $wati_parameters[] = array(
			'name' => "tracking_url",
			'value' => $link,
		  );	
		  
		  $wati_array = array();
		  $wati_array = array(
			'template_name' => $template_name,
			'broadcast_name' => $template_name,
			'parameters' => $wati_parameters,
		  );

		 $this->auth_model->send_wati_sms($sender_mobile,$wati_array);

	
		if($this->auth_model->send_sms($message,$template_id,$mobile)){			
			$data_up=array();
			$data_up['is_doc'] = 0;
			$this->db->where('id', $id);
			$this->db->update('candidate', $data_up);			
		    $resultpost = array(
                'status' => 200,
                'message' => 'SMS Sent Successfully!',
                'url' => $this->agent->referrer(),
            );
		}
		else{
		    $resultpost = array(
                'status' => 400,
                'message' => 'Alert, There are some issue while sending sms!',
            );
	    	}
        }
        else{
            $resultpost = array(
                'status' => 400,
                'message' => 'Alert, Data bot found!',
            );
        }
      return simple_json_output($resultpost);
    }
    
	
	public function get_candidate_details_by_id($id){
        $resultdata = array();
    
        $query = $this->db->query("SELECT id, staff_catid,staff_typeid,resume, staff_type, name, email, phone, marital_status, state_id, state_name, city_id, city_name, area_id, area_name, address, pincode,is_same, p_address, p_state_id, p_state_name, p_city_id, p_city_name, p_pincode, whatsapp, alt_phone, dob, doa, date_added,is_doc, doc_date,is_pure,salary,shift_type,paid_leaves,is_pf,is_esic,is_tds,salary_type,basic_salary,hra,gross_edu,gender,emp_id,bank_id,bank,account_no,ifsc_code,status,joining_date,is_ptax FROM candidate WHERE id='$id' LIMIT 1");
     
        if (!empty($query)) {
           foreach ($query->result_array() as $item) { 
             $date_added = date("Y-m-d H:i:s", strtotime($item['date_added']));
		                
  	        $documents = array();
            $documents_sql = $this->db->query("SELECT id, passport_pic, pan_card, pan_no, aadhar_card, aadhar_no, bank_details, educational, salary_slip, electricty_bill, rent_agreement, hr_no, police_verification, ref1_name, ref1_mobile, ref2_name, ref2_mobile FROM candidate_document WHERE candidate_id='$id' LIMIT 1");
            if($documents_sql->num_rows() > 0){
                $row = $documents_sql->row_array();
                $documents = array(
                    "passport_pic" => ($row['passport_pic']!=null ?  base_url().$row['passport_pic']:NULL),
                    "pan_card" => ($row['pan_card']!=NULL ?  base_url().$row['pan_card']:NULL),					
                    "pan_no" => $row['pan_no'],
                    "aadhar_card" => ($row['aadhar_card']!=NULL ?  base_url().$row['aadhar_card']:NULL),	
                    "aadhar_no" => $row['aadhar_no'],             
                    "bank_details" =>($row['bank_details']!=NULL ?  base_url().$row['bank_details']:NULL),
                    "educational" =>($row['educational']!=NULL ?  base_url().$row['educational']:NULL),
                    "salary_slip" =>($row['salary_slip']!=NULL ?  base_url().$row['salary_slip']:NULL),
                    "electricty_bill" =>($row['electricty_bill']!=NULL ?  base_url().$row['electricty_bill']:NULL),
                    "rent_agreement" =>($row['rent_agreement']!=NULL ?  base_url().$row['rent_agreement']:NULL),
                    "police_verification" =>($row['police_verification']!=NULL ?  base_url().$row['police_verification']:NULL),
                    "hr_no" =>($row['hr_no']!=NULL ?  $row['hr_no']:'-'),
                    "ref1_name" => $row['ref1_name'],
                    "ref1_mobile" => $row['ref1_mobile'],
                    "ref2_name" => $row['ref2_name'],
                    "ref2_mobile" => $row['ref2_mobile'],
                );
            }
			
            $resultdata = array(
                "bank_id"	  => $item['bank_id'],
                "bank"	  	  => $item['bank'],
                "account_no"  => $item['account_no'],
                "ifsc_code"	  => $item['ifsc_code'],				
				
                "emp_id"	   => $item['emp_id'],
                "salary"	   => $item['salary'],
                "basic_salary" => $item['basic_salary'],
                "hra"	  	   => $item['hra'],
                "gross_edu"	   => $item['gross_edu'],
                "gender"	   => $item['gender'],
                "shift_type"   => $item['shift_type'],
                "paid_leaves"  => $item['paid_leaves'],
                "is_pf"	  	   => $item['is_pf'],
                "is_esic"	   => $item['is_esic'],
                "is_tds"	   => $item['is_tds'],
                "staff_catid"  => $item['staff_catid'],
                "staff_typeid" => $item['staff_typeid'],
                "salary_type"  => $item['salary_type'],
                "staff_type"   => $item['staff_type'],
                "is_ptax"  		=> $item['is_ptax'],
				  
                "id"	  => $item['id'],
                "resume"  => ($item['resume']!=null ?  base_url().$item['resume']:NULL),
                "name"    => $item['name'],
                "email"   => $item['email'],
                "phone"   => $item['phone'],  				
                "marital_status"  => $item['marital_status'],     
                "state_id"   => $item['state_id'],    
                "state_name" => $item['state_name'],    
                "city_id"    => $item['city_id'],    
                "city_name"  => $item['city_name'],  
                "address"    => $item['address'],  
                "pincode"    => $item['pincode'], 				
                "is_same_check" => $item['is_same'],  
                "is_same"    => ($item['is_same']==1 ? 'Yes':'No'),  
                "p_state_id"   => ($item['p_state_id']!=null ?  $item['p_state_id']:'-'), 
                "p_state_name" => ($item['p_state_name']!=null ?  $item['p_state_name']:'-'), 
                "p_city_id"    => ($item['p_city_id']!=null ?  $item['p_city_id']:'-'), 
                "p_city_name"  => ($item['p_city_name']!=null ?  $item['p_city_name']:'-'), 
                "p_address"    => ($item['p_address']!=null ?  $item['p_address']:'-'), 
                "p_pincode"    => ($item['p_pincode']!=null ?  $item['p_pincode']:'-'), 
				
                "dob"  		    => ($item['dob']!=null ? date("d M, Y", strtotime($item['dob'])):'-'),  
                "doa"  		    => ($item['joining_date']!=null ? date("d M, Y", strtotime($item['joining_date'])):'-'), 
                "dob_input"  	=> ($item['dob']!=null ? date("Y-m-d", strtotime($item['dob'])):''),  
                "doa_input"  	=> ($item['doa']!=null ? date("Y-m-d", strtotime($item['doa'])):''), 
                "joining_date" => ($item['joining_date']!=null ? date("d M, Y", strtotime($item['joining_date'])):'-'), 
                "joining_date_input" => ($item['joining_date']!=null ? date("Y-m-d", strtotime($item['joining_date'])):''), 
                "is_pure"       => $item['is_pure'], 
                "is_doc"        => $item['is_doc'], 
                "status"        => $item['status'], 
                "doc_date"  	=> ($item['doc_date']!=null ? date("d M, Y h:i A", strtotime($item['doc_date'])):'-'),  
                "added_date"    => date("d M, Y h:i A", strtotime($item['added_date'])),
                "documents"     => $documents,  
            );
        }
      }
      return $resultdata;
    }  
	
	
	public function get_pending_doc_count(){
        $user_id = $this->session->userdata('super_user_id');
        $query = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_pure='0' AND is_short='1' AND is_selected='1' AND is_traning='0' AND is_doc='0'");
        return $query->num_rows();
    }
    	
	public function get_verified_doc_count(){
        $user_id = $this->session->userdata('super_user_id');
        $query = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_pure='0' AND is_short='1' AND is_selected='1' AND is_traning='0' AND is_doc='1'");
        return $query->num_rows();
    }
	
   public function approved_candidate($id) {  
        $check = $this->db->query("SELECT is_pure,is_doc FROM candidate WHERE id='$id' LIMIT 1")->row();
        if($check->is_pure==1){  
          $resultpost = array(
            "status" => 400,
            "message" => 'Candidate already approved!' ,			
          );			
		}  
		else if($check->is_doc==0){  
          $resultpost = array(
            "status" => 400,
            "message" => 'Candidate documentation is not updated!' ,			
			);			
		}
		else{   
          $resultpost = array(
            "status" => 200,
            "message" =>  get_phrase('candidate_approved_successfully'),
			"url" => $this->agent->referrer(),	
          );
		
         $update = array();
         $update = array( 
           'is_pure' => 1,
           'pure_date' =>  date("Y-m-d H:i:s"),    
           'verified_date' =>  date("Y-m-d H:i:s"),    
		   'approval_by_id'   => $this->session->userdata('super_user_id'),
		   'approval_by_name' =>  $this->session->userdata('super_name'),
         );
         $this->db->where('id', $id);
         $this->db->update('candidate', $update);  
		 
		$data_follow = array();
		$data_follow = array(
			'action' => 'timeline',
			'candidate_id' => $id,
			'remark' => 'Candidate verified by '.$this->session->userdata('super_name'),
			'added_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'user_type' => $super_type
		);

		$this->db->insert('candidate_followup', $data_follow);
		 
         $this->session->set_flashdata('flash_message', get_phrase('candidate_approved_successfully'));	   
		}
       return simple_json_output($resultpost); 
    }
	
	
	public function get_my_staff_list(){
        $user_id = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['date_range']  = $_REQUEST['date_range'];
        $filter_data['keywords']    = $_REQUEST['keywords'];
        $filter_data['salary_type']  = $_REQUEST['salary_type'];
        
        if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
         $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  ($order_date[1]==NULL ? $from:date('Y-m-d',strtotime($order_date[1]))); 
          $sql_filter .=" AND (DATE(kyc_date) BETWEEN '$from' AND '$to')"; 
        endif;  

		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type        = $filter_data['salary_type'];
            $sql_filter .= " AND (salary_type='$salary_type')";
        endif;
		
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $sql_filter .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR email like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_pure='1' AND is_doc='1' AND is_kyc='1' $sql_filter ORDER BY id desc")->num_rows();
   
        $query = $this->db->query("SELECT id,name,phone,email,state_name,city_name,kyc_date,salary_type FROM candidate WHERE  is_left='0' AND is_pure='1' AND is_doc='1' AND is_kyc='1' $sql_filter ORDER BY DATE(kyc_date) DESC LIMIT $start, $length");
        //  echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id'];                
               $details_url=base_url().'hr/candidate-details/'.$id;
               $assign_salary_url=base_url().'hr/update-salary/'.$id;                
			  
			   $action='';  

			   $update_staff_url=base_url().'hr/update-staff/'.$id;                
			   $action ='<a href="'.$update_staff_url.'" target="_blank" data-toggle="tooltip" title="Update Staff"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
			   
			   
			   $action .='<a href="'.$details_url.'" data-toggle="tooltip" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';              
			   $action .='<a href="#" onclick="get_timeline_('.$id.');" data-toggle="tooltip" title="Timeline" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd"><button type="button" class="btn mr-1 mb-1 icon-btn">View Timeline</button></a>';
                
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],                   
                    "name"			=> $item['name'].' <br/>'. $item['phone'], 
					"email"         => $item['email'],      
					"salary_type"   => $item['salary_type'],      
				    "state"    		=> $item['state_name'],
                    "city"     		=> $item['city_name'],                 
                    "date"          => date("d M, Y h:i A", strtotime($item['kyc_date'])),
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
	 
	public function update_staff($id){   
        $resultpost = array(
            "status" => 200, 
            "message" => get_phrase('bank_details_updated_successfully'),
            "url" => base_url('hr/my-staff'),
        );
        
        $this->form_validation->set_rules('bank_id', 'Bank', 'trim|required');
        $this->form_validation->set_rules('account_no', 'Account No', 'trim|required');		
        $this->form_validation->set_rules('confirm_account_no', 'Confirm Account No', 'required|matches[account_no]');
        $this->form_validation->set_rules('ifsc_code', 'IFSC Code', 'trim|required');
	
		
		if ($this->form_validation->run() == FALSE){
		     $errors = array(
                'bank_id' 	   		 => form_error('bank_id'),
                'account_no' 		 => form_error('account_no'),
                'confirm_account_no' => form_error('confirm_account_no'),
                'ifsc_code' 		 => form_error('ifsc_code'),
			);
			
			$allErrors = implode("\n", $errors);
            $resultpost = array(
                "status" => 400,
                "message" => $allErrors,
                "errors" => $errors,
            );  
		}
		else{
        	$bank_id = clean_and_escape($this->input->post('bank_id'));
			$bank = $this->common_model->getNameById('emp_bank','name',$bank_id);
			
			$data=array();
        	$data['bank_id']       = $bank_id;
        	$data['bank']          = $bank;
        	$data['account_no']    = clean_and_escape($this->input->post('account_no'));
        	$data['ifsc_code']     = clean_and_escape($this->input->post('ifsc_code'));
            $this->db->where('id', $id);
            $this->db->update('candidate', $data);
        	$this->session->set_flashdata('flash_message', get_phrase('bank_details_updated_successfully'));
       }
       return simple_json_output($resultpost); 
    }  
	
	
	
	 
    public function get_paginated_candidate_unshortlist_count($filter_data){
        $keyword_filter         = "";
        $order_type_filter      = "";
        $attendance_date_filter = "";
        $user_id                = $this->session->userdata('super_user_id');
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword         = $filter_data['keywords'];
            $keyword_filter  .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR area_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        
        if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
         
        $query = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_short='0' $keyword_filter ORDER BY id desc");
                                 
        return $query->num_rows();
    }
    
    
  public function count_today_calls() {
       $current_date = date('Y-m-d');
       $user_id = $this->session->userdata('super_user_id');	
	   
       $query = $this->db->query("SELECT doc.id FROM candidate as doc 
	   INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id 
	   WHERE doc.is_left='0' AND  DATE(follow.added_date)='$current_date' AND follow.added_by_id='$user_id'");
	   return $query->num_rows();
    }
    
	public function count_today_followup() {
        $asm_present_filter = "";
        $keyword_filter     = "";
        $resultdata         = array();
        $current_date       = date('Y-m-d');
        $current_time       = date('h:i a');

   
        $coordinator_id = $this->session->userdata('super_user_id');
        $query          = $this->db->query("SELECT doc.id,follow.follow_up_date,follow.follow_up_time,follow.candidate_id as doc_id
	   FROM candidate as doc
	   INNER JOIN candidate_followup as follow ON doc.id = follow.candidate_id
	   WHERE doc.is_left='0' AND follow.added_by_id='$coordinator_id' ORDER BY follow.id DESC, follow.follow_up_time DESC");
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
        return count($resultdata);
    }
	

    /*HR Head Starts*/	
	 public function get_assign_salary(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['date_range']  = $_REQUEST['date_range'];
        $filter_data['keywords']    = $_REQUEST['keywords'];
        $filter_data['staff_type']  = $_REQUEST['staff_type'];
        
        if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
         $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  ($order_date[1]==NULL ? $from:date('Y-m-d',strtotime($order_date[1]))); 
          $sql_filter .=" AND (DATE(pure_date) BETWEEN '$from' AND '$to')"; 
        endif;  

		if (isset($filter_data['staff_type']) && $filter_data['staff_type'] != ""){
            $staff_type = $filter_data['staff_type'];
            $keyword_filter  .= " AND staff_typeid='$staff_type' ";
        }
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $sql_filter .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR email like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_pure='1' AND is_doc='1' AND is_kyc=0  $sql_filter ORDER BY id desc")->num_rows();
   
        $query = $this->db->query("SELECT id,name,phone,email,state_name,city_name,pure_date,staff_type,salary_type FROM candidate WHERE is_left='0' AND is_pure='1' AND is_doc='1' AND is_kyc=0 $sql_filter ORDER BY DATE(pure_date) DESC LIMIT $start, $length");

        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id'];                
               $details_url=base_url().'hr/candidate-details/'.$id;
               $assign_salary_url=base_url().'hr/update-salary/'.$id;                
			  
			  $move_url="confirm_modal('".base_url()."hr/staff_list/move_to_candidate/".$id."','Are you sure want to move this staff to initial stage of verification!')";
			  $action ='';
			  $action .='<a  href="#" onclick="'.$move_url.'"  data-toggle="tooltip" title="Move to Candidate Stage of HR Panel"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-reply" aria-hidden="true"></i></button></a>';
			  
			   $action .='<a href="'.$assign_salary_url.'" target="_blank" data-toggle="tooltip" title="Assign Salary"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-briefcase" aria-hidden="true"></i></button></a>';              
			   $action .='<a href="'.$details_url.'" data-toggle="tooltip" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';              
			   $action .='<a href="#" onclick="get_timeline_('.$id.');" data-toggle="tooltip" title="Timeline" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd"><button type="button" class="btn mr-1 mb-1 icon-btn">View Timeline</button></a>';
                
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'], 
					"staff_type"    => $item['salary_type'].'-'.$item['staff_type'],
                    "name"			=> $item['name'].' <br/>'. $item['phone'], 
					"email"         => $item['email'],      
				    "state"    		=> $item['state_name'],
                    "city"     		=> $item['city_name'],                 
                    "date"          => date("d M, Y h:i A", strtotime($item['pure_date'])),
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
	
	
	public function update_candidate_salary($id){   
        $resultpost = array(
            "status" => 200,
            "message" => 'Salary Updated Successfully!',
            "url" => $this->agent->referrer(),
        );		
		
		$emp_id=clean_and_escape($this->input->post('emp_id'));		
		  
        $check=$this->db->query("SELECT name FROM candidate WHERE emp_id='$emp_id' AND id!='$id' LIMIT 1");
		if($check->num_rows()>0){
		  $resultpost = array(
			"status" => 400,
			"message" => 'Emp Code is already used by '.$check->row()->name,			
          );
		}
		else{
			$data=array();  
			$joining_date=$this->input->post('joining_date');   
			$data['joining_date'] = ($joining_date!='' ? date("Y-m-d", strtotime($joining_date)):NULL);	
			       	   
			$is_ptax= ($this->input->post('is_ptax')==1 ? 1:0);
				   
            $salary_type = clean_and_escape($this->input->post('salary_type'));
            $staff_type  = clean_and_escape($this->input->post('staff_type'));			
            $data['staff_catid']  = $salary_type;
            $data['staff_typeid'] = $staff_type;   
			$data['salary_type']  = $this->common_model->getNameById('em_staff_category','name',$salary_type);
            $data['staff_type']   = $this->common_model->getNameById('em_staff_type','name',$staff_type);
			$data['emp_id']       = $emp_id;
			$data['salary']       = clean_and_escape($this->input->post('salary'));
			$data['basic_salary'] = clean_and_escape($this->input->post('basic_salary'));
			$data['hra'] 		  = clean_and_escape($this->input->post('hra'));
			$data['gross_edu'] 	  = clean_and_escape($this->input->post('gross_edu'));
			$data['gender'] 	  = clean_and_escape($this->input->post('gender'));
			$data['shift_type']   = clean_and_escape($this->input->post('shift_type'));
			$data['paid_leaves']  = clean_and_escape($this->input->post('paid_leaves'));       
			$data['status']  	  = clean_and_escape($this->input->post('status'));       
			$data['is_pf']  	  = ($this->input->post('is_pf')==1 ? 1:0);
			$data['is_esic']  	  = ($this->input->post('is_esic')==1 ? 1:0);
			$data['is_tds']  	  = ($this->input->post('is_tds')==1 ? 1:0);
			$data['is_ptax']  	  = $is_ptax;
			$data['is_kyc'] 	  = 1;
			$data['kyc_date'] 	  = date("Y-m-d H:i:s");
			$this->db->where('id', $id);
			$update=$this->db->update('candidate', $data);
			
			if($update){
				 $check=$this->db->query("UPDATE candidate	SET probation_date = 
				 CASE 
				 WHEN DAYOFMONTH(joining_date) BETWEEN 1 AND 15 THEN DATE_ADD(joining_date, INTERVAL 6 MONTH)
				 WHEN DAYOFMONTH(joining_date) BETWEEN 16 AND 31 THEN DATE_ADD(joining_date, INTERVAL 7 MONTH)
				 ELSE NULL END
				 WHERE id = '$id'");				
				
				 $data_attn=array();    
		
				 $data_follow = array();
				 $data_follow = array(
					'action' => 'timeline',
					'candidate_id' => $id,
					'remark' => 'Salary Details Updated by '.$this->session->userdata('super_name'),
					'added_date' => date("Y-m-d H:i:s"),
					'added_by_id' => $this->session->userdata('super_user_id'),
					'added_by_name' => $this->session->userdata('super_name'),
					'user_type' => $super_type
				 );
				 $this->db->insert('candidate_followup', $data_follow);
			
			   $logs = array();
			   $logs = array(
				 'parent_id'    => $id,
				 'parent_table' => 'candidate',
				 'json_data'    => json_encode($data),
				 'action'       => 'update',
			   );
			   $this->common_model->add_hr_logs($logs);
			}   
			
			$this->session->set_flashdata('flash_message', get_phrase('salary_updated_successfully'));  
		}
       return simple_json_output($resultpost); 
    }


	public function get_staff_list(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['date_range']   = $_REQUEST['date_range'];
        $filter_data['keywords']     = $_REQUEST['keywords'];
        $filter_data['salary_type']  = $_REQUEST['salary_type'];
        
        if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
         $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  ($order_date[1]==NULL ? $from:date('Y-m-d',strtotime($order_date[1]))); 
          $sql_filter .=" AND (DATE(kyc_date) BETWEEN '$from' AND '$to')"; 
        endif;
		
		if (isset($filter_data['salary_type']) && $filter_data['salary_type'] != ""):
            $salary_type = $filter_data['salary_type'];
            $sql_filter .= " AND (salary_type='$salary_type')";
        endif;
		

        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $sql_filter .= " AND (name like '%" . $keyword . "%'
            OR emp_id like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR email like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_left='0' AND is_pure='1' AND is_doc='1' AND is_kyc='1'  $sql_filter ORDER BY id desc")->num_rows();
   
        $query = $this->db->query("SELECT id,emp_id,name,phone,email,state_name,city_name,salary_type,staff_type,kyc_date,paid_leaves,status,is_left FROM candidate WHERE is_left='0' AND is_pure='1' AND is_doc='1' AND is_kyc='1' $sql_filter ORDER BY DATE(kyc_date) DESC LIMIT $start, $length");
        //echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id'];  
               $emp_id=$item['emp_id'];                
               $paid_leaves=$item['paid_leaves'];                   
               $details_url=base_url().'hr/candidate-details/'.$id;
               $update_salary_url=base_url().'hr/update-salary/'.$id;                
			   $left_url="confirm_modal('".base_url()."hr/staff_list/left_staff/".$id."','Are you sure want to move this staff to left staff!')";
						   
			  $action='';   
			  $action .='<a  href="#" onclick="'.$left_url.'"  data-toggle="tooltip" title="Left Staff"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-blind" aria-hidden="true"></i></button></a>';
			   
			   
			  $action .='<a href="'.$update_salary_url.'" target="_blank" data-toggle="tooltip" title="Assign Salary"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';			   
			   
			  $action .='<a href="'.$details_url.'" data-toggle="tooltip" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';              
			  
			  $action .='<a href="#" onclick="get_timeline_('.$id.');" data-toggle="tooltip" title="Timeline" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd"><button type="button" class="btn mr-1 mb-1 icon-btn">View Timeline</button></a>';
                       
			  if($item['status']== 1){
				$status='<div class="chip chip-success"><div class="chip-body"><span class="chip-text">Active</span></div></div>';   
			  }else{
				$status='<div class="chip chip-danger"><div class="chip-body"><span class="chip-text">Inactive</span></div></div>';
			  }
			 
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],                   
                    "emp_id"        => $item['emp_id'],                   
                    "name"			=> $item['name'].' <br/>'. $item['phone'], 
					"email"         => $item['email'],      
				    "state"    		=> $item['state_name'],
                    "city"     		=> $item['city_name'],                 
                    "salary_type"   => $item['salary_type'].'-'.$item['staff_type'],   
                    "paid_leaves"   => $paid_leaves,                 
                    "balance_leaves"=> $paid_leaves-$used_pl,                 
                    "status"		=> $status,  
                    "date"          => date("d M, Y h:i A", strtotime($item['kyc_date'])),
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
   
   public function add_holidays(){       
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('holidays_added_successfully'),
            "url" => base_url('hr/holidays'),
        );        
        
    	$salary_types  = $this->input->post('salary_type');
    	$holiday_date = date("Y-m-d", strtotime($this->input->post('holiday_date')));
    	
		$conditions = [];	
        foreach($salary_types as $salary_type)	{	
		  $conditions[] = " FIND_IN_SET('$salary_type',staff_typeid)"; 	
		}         		
		$conditions_str = implode(' AND ', $conditions);

        $check = $this->db->query("SELECT id FROM holidays WHERE is_deleted='0' AND $conditions_str  AND holiday_date='$holiday_date' LIMIT 1")->num_rows();
     
    	if ($check>0) {
    		$resultpost = array(
    		 "status" => 400,
    		 "message" => get_phrase('holiday_already_added')
    	   );    		
    	}
    	
    	else{
			$staff_typeid=implode(",",$salary_types);
		    $salary_type=$this->common_model->getBulkNameIds('em_staff_category','name',$staff_typeid);
			
		    $data=array();
        	$data['staff_typeid']  = $staff_typeid;
        	$data['salary_type']   = $salary_type;
        	$data['holiday_name']  = clean_and_escape($this->input->post('holiday_name'));
        	$data['holiday_date']  = $holiday_date; 
            $data['added_by_id']   = $this->session->userdata('super_user_id');
            $data['added_by_name'] = $this->session->userdata('super_name');
    		$data['created_at']    = date("Y-m-d H:i:s");
        	
        	$this->db->insert('holidays', $data);
        	$user_id = $this->db->insert_id();
        	$this->session->set_flashdata('flash_message', get_phrase('holidays_added_successfully'));
       }
       return simple_json_output($resultpost); 
    }
    
    public function edit_holidays($id){
           $resultpost = array(
            "status" => 200,
            "message" => get_phrase('holidays_updated_successfully'),
            "url" => base_url('hr/holiday/edit/'.$id),
        ); 
		
      	$salary_types  = $this->input->post('salary_type');
    	$holiday_date = date("Y-m-d", strtotime($this->input->post('holiday_date')));
		$conditions = [];	
        foreach($salary_types as $salary_type)	{	
		  $conditions[] = " FIND_IN_SET('$salary_type',staff_typeid)"; 	
		}         		
		$conditions_str = implode(' AND ', $conditions);

        $check = $this->db->query("SELECT id FROM holidays WHERE is_deleted='0' AND $conditions_str  AND holiday_date='$holiday_date' AND id!='$id' LIMIT 1")->num_rows();
     
		
    	if ($check>0) {    		
    		$resultpost = array(
    		 "status" => 400,
    		 "message" => get_phrase('holiday_already_added')
    	   );
    		
    	}
    	
    	else{
			$staff_typeid=implode(",",$salary_types);
		    $salary_type=$this->common_model->getBulkNameIds('em_staff_category','name',$staff_typeid);
			
		    $data=array();
        	$data['staff_typeid']    = $staff_typeid;
        	$data['salary_type']     = $salary_type;
        	$data['holiday_name']    = clean_and_escape($this->input->post('holiday_name'));
        	$data['holiday_date']    = $holiday_date; 
            $data['updated_by_id']   = $this->session->userdata('super_user_id');
            $data['updated_by_name'] = $this->session->userdata('super_name');
    		$data['updated_at']      = date("Y-m-d H:i:s");
			$this->db->WHERE('id', $id);
			$this->db->update('holidays', $data);
        	$this->session->set_flashdata('flash_message', get_phrase('holidays_updated_successfully'));
       }
       return simple_json_output($resultpost); 
    }
    
	public function delete_holidays($id){  
		  $resultpost = array(
            "status" => 200,
            "message" => get_phrase('holidays_deleted_successfully'),
            "url" => base_url('hr/holidays'),
        );        
        
        $data['is_deleted'] = '1';
        $this->db->where('id', $id);
        $this->db->update('holidays',$data);       
        return simple_json_output($resultpost); 
    }    
	
	public function get_holidays(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['date_range']  = $_REQUEST['date_range'];
        $filter_data['keywords']    = $_REQUEST['keywords'];
         
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
    	    $keyword=$filter_data['keywords'];
			$sql_filter .=" AND (holiday_name like '%".$keyword."%'  
            OR salary_type like '%" . $keyword . "%')"; 
         endif;	
    
        if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
          $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  date('Y-m-d',strtotime($order_date[1])); 
          $sql_filter =" AND (DATE(holiday_date) BETWEEN '$from' AND '$to')"; 
         endif;	
        
		$total_count = $this->db->query("SELECT id FROM holidays WHERE is_deleted=0  $sql_filter ORDER BY id desc")->num_rows();
     
        $query = $this->db->query("SELECT id,state_name,salary_type,holiday_name,holiday_date,created_at FROM holidays WHERE is_deleted=0 $sql_filter ORDER BY id DESC LIMIT $start, $length");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $url=base_url().'hr/holiday/edit/'.$item['id'];
              $delete_url="confirm_modal('".base_url()."hr/holidays/delete/".$item['id']."','Are you sure want to delete this!')";
              $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
              $action .='<a href="#"  onclick="'.$delete_url.'" title="Edit"><button type="button" class="btn ml-1 mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button></a>';
			  
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],             
                    "salary_type"	=> $item['salary_type'], 
                    "state_name"	=> $item['state_name'], 
					"holiday_name"  => $item['holiday_name'],      
				    "punch_date"    => $punch_date,
				    "holiday_date"  => date("d M, Y", strtotime($item['holiday_date'])),
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
	
	public function move_to_left($id){   
        $resultpost = array(
            "status" => 200,
            "message" => 'Staff moved to left company!',
            "url" => $this->agent->referrer(),
        );
		  
        $check=$this->db->query("SELECT name FROM candidate WHERE is_left='0' AND id='$id' LIMIT 1");
		if($check->num_rows()>0){
			$data=array();    
			$data['is_left']      = 1;			
			$data['left_date'] 	  = date("Y-m-d H:i:s");
			$this->db->where('id', $id);
			$update=$this->db->update('candidate', $data);
			
			if($update){
				$data_attn=array();  
						
				$data_follow = array();
				$data_follow = array(
					'action' => 'timeline',
					'candidate_id' => $id,
					'remark' => 'Staff Left the Company Updated by '.$this->session->userdata('super_name'),
					'added_date' => date("Y-m-d H:i:s"),
					'added_by_id' => $this->session->userdata('super_user_id'),
					'added_by_name' => $this->session->userdata('super_name'),
					'user_type' => $super_type
				);
				$this->db->insert('candidate_followup', $data_follow);
				
			   $logs = array();
			   $logs = array(
				 'parent_id'    => $id,
				 'parent_table' => 'candidate',
				 'json_data'    => json_encode($data),
				 'action'       => 'move_to_left',
			   );
			   $this->common_model->add_hr_logs($logs);
			}  
			
			$this->session->set_flashdata('flash_message','Staff moved to left company!');
		}
		else{
		  $resultpost = array(
			"status" => 400,
			"message" => 'Staff already moved to left company',
          );
		}
		
       return simple_json_output($resultpost); 
    } 
	
	public function move_to_candidate($id){   
        $resultpost = array(
            "status" => 200,
            "message" => 'Staff moved to candidate stage!',
            "url" => $this->agent->referrer(),
        );
		  
        $check=$this->db->query("SELECT name FROM candidate WHERE is_short='1' AND id='$id' LIMIT 1");
		if($check->num_rows()>0){ 
		   $json_data=$this->db->query("SELECT * FROM candidate WHERE id='$id' LIMIT 1")->row_array();
			$data=array();    
			$data['is_pure']      = 0;			
			$data['is_short']     = 0;			
			$data['is_selected']  = 0;			
			$data['is_traning']   = 0;			
			$data['is_kyc']  	  = 0;			
			$data['status']       = 1;			
			$this->db->where('id', $id);
			$update=$this->db->update('candidate', $data);
				  	
			$data_follow = array();
			$data_follow = array(
				'action' => 'timeline',
				'candidate_id' => $id,
				'remark' => 'Staff moved to initial stage of verification by '.$this->session->userdata('super_name'),
				'added_date' => date("Y-m-d H:i:s"),
				'added_by_id' => $this->session->userdata('super_user_id'),
				'added_by_name' => $this->session->userdata('super_name'),
				'user_type' => $super_type
			);

			$this->db->insert('candidate_followup', $data_follow);
			
			if($update){
			   $logs = array();
			   $logs = array(
				 'parent_id'    => $id,
				 'parent_table' => 'candidate',
				 'json_data'    => json_encode($json_data),
				 'action'       => 'move_to_candidate',
			   );
			   $this->common_model->add_hr_logs($logs);
			}  
			
			$this->session->set_flashdata('flash_message','Staff moved to candidate stage!');
		}
		else{
		  $resultpost = array(
			"status" => 400,
			"message" => 'Staff already moved to candidate stage in HR Panel',
          );
		}
		
       return simple_json_output($resultpost); 
    }
			
	public function get_left_staff_list(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['date_range']  = $_REQUEST['date_range'];
        $filter_data['keywords']    = $_REQUEST['keywords'];
        $salary_type   				= $_REQUEST['salary_type'];
        
        if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
         $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  ($order_date[1]==NULL ? $from:date('Y-m-d',strtotime($order_date[1]))); 
          $sql_filter .=" AND (DATE(left_date) BETWEEN '$from' AND '$to')"; 
        endif;  
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $sql_filter .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR email like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_left='1' AND is_pure='1' $sql_filter ORDER BY id desc")->num_rows();
   
        $query = $this->db->query("SELECT id,name,phone,email,state_name,city_name,salary_type,left_date FROM candidate WHERE is_left='1' AND is_pure='1'  $sql_filter ORDER BY DATE(left_date) DESC LIMIT $start, $length");
        //      echo $this->db->last_query();exit();
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id'];                
               $details_url=base_url().'hr/candidate-details/'.$id;
            						   
			  $action=''; 
			  $action .='<a href="'.$details_url.'" data-toggle="tooltip" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';              
			  
			  $action .='<a href="#" onclick="get_timeline_('.$id.');" data-toggle="tooltip" title="Timeline" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd"><button type="button" class="btn mr-1 mb-1 icon-btn">View Timeline</button></a>';
                
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],                   
                    "name"			=> $item['name'].' <br/>'. $item['phone'], 
					"email"         => $item['email'],      
				    "state"    		=> $item['state_name'],
                    "city"     		=> $item['city_name'],                 
                    "salary_type"   => $item['salary_type'],                 
                    "date"          => date("d M, Y h:i A", strtotime($item['left_date'])),
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
	
	
	
   public function update_staff_details($id){ 
		$resultpost = array(
            "status" => 200,
            "message" => 'Salary Updated Successfully!',
            "url" => $this->agent->referrer(),
        );
		
		$query_c = $this->db->query("SELECT id,is_pure  FROM `candidate` WHERE id='$id' limit 1");		
		$query = $this->db->query("SELECT id,passport_pic,pan_card,aadhar_card,bank_details,educational,salary_slip,electricty_bill,rent_agreement,police_verification  FROM `candidate_document` where candidate_id='$id' limit 1");
		
				
		$year      = date("Y");
		$month     = date("m");
		$day       = date("d");
		$directory = "uploads/candidate_document/" . "$year/$month/$day/";	
		if (!is_dir($directory)) {
			mkdir($directory, 0755, true);
		}
		
		if($query_c->row()->is_pure==1){
			$this->form_validation->set_rules('address', 'Flat/ Building/ Street', 'required');
			$this->form_validation->set_rules('state_id', 'State', 'trim|required');
			$this->form_validation->set_rules('city_id', 'City', 'trim|required');
			$this->form_validation->set_rules('pincode', 'Pincode', 'trim|required');
			$this->form_validation->set_rules('pan_no', 'Pan No', 'trim|required');
			$this->form_validation->set_rules('aadhar_no', 'Aadhar No', 'trim|required');

			$this->form_validation->set_rules('bank_id', 'Bank', 'trim|required');
			$this->form_validation->set_rules('account_no', 'Account No', 'trim|required');		
			$this->form_validation->set_rules('confirm_account_no', 'Confirm Account No', 'required|matches[account_no]');
			$this->form_validation->set_rules('ifsc_code', 'IFSC Code', 'trim|required');
			
			if ($this->form_validation->run() == FALSE){
				 $errors = array(
					'email' 	   		 => form_error('email'),					
					'marital_status' 	 => form_error('marital_status'),					
					'dob' 	 			 => form_error('dob'),					
					'doa' 	 			 => form_error('doa'),					
					'address' 	 		 => form_error('address'),					
					'state_id' 	 		 => form_error('state_id'),					
					'city_id' 	 		 => form_error('city_id'),					
					'pincode' 	 		 => form_error('pincode'),					
					'pan_no' 	 		 => form_error('pan_no'),					
					'aadhar_no' 	 	 => form_error('aadhar_no'),					
					
					'bank_id' 	   		 => form_error('bank_id'),
					'account_no' 		 => form_error('account_no'),
					'confirm_account_no' => form_error('confirm_account_no'),
					'ifsc_code' 		 => form_error('ifsc_code'),
				);
				
				$errors_ = array_map('strip_tags', array_filter($errors));
				$allErrors = implode('<br> ', $errors_);
			
				$resultpost = array(
					"status" => 400,
					"message" => $allErrors,
					"errors" => $errors,
				);  
			}
			else{
			
        	$bank_id = clean_and_escape($this->input->post('bank_id'));
			$bank = $this->common_model->getNameById('emp_bank','name',$bank_id);
			
            $data=array();
        	$data['bank_id']      	   = $bank_id;
        	$data['bank']         	   = $bank;
        	$data['account_no']    	   = clean_and_escape($this->input->post('account_no'));
        	$data['ifsc_code']     	   = clean_and_escape($this->input->post('ifsc_code'));
			$data['email']        	   = clean_and_escape($this->input->post('email'));
			$data['marital_status']    = clean_and_escape($this->input->post('marital_status'));
		
			
			$data['address']       	   = clean_and_escape($this->input->post('address'));
			$state_id                  = clean_and_escape($this->input->post('state_id'));
            $state_name                = $this->crud_model->get_state_name($state_id); 
			$data['state_id']    	   = $state_id;
            $data['state_name'] 	   = $state_name;
            $city_id            	   = clean_and_escape($this->input->post('city_id'));
            $city_name          	   = $this->crud_model->get_city_name($city_id);
            $data['city_id']   		   = $city_id;
            $data['city_name']   	   = $city_name;
            $data['pincode']    	   = clean_and_escape($this->input->post('pincode'));
			
						
			$is_same= clean_and_escape($this->input->post('is_same'));	
			$data['is_same']    	   = $is_same;
			 
			if($is_same==1){
				$data['p_address']         = clean_and_escape($this->input->post('address'));		
				$data['p_state_id']    	   = $state_id;
				$data['p_state_name'] 	   = $state_name;
				$data['p_city_id']   	   = $city_id;
				$data['p_city_name']   	   = $city_name;
				$data['p_pincode']    	   = clean_and_escape($this->input->post('pincode'));	
			}
			else{
				$data['p_address']         = clean_and_escape($this->input->post('p_address'));			
				$p_state_id                = clean_and_escape($this->input->post('p_state_id'));
				$p_state_name              = $this->crud_model->get_state_name($p_state_id); 
				$data['p_state_id']    	   = $p_state_id;
				$data['p_state_name'] 	   = $p_state_name;
				$p_city_id            	   = clean_and_escape($this->input->post('p_city_id'));
				$p_city_name          	   = $this->crud_model->get_city_name($p_city_id);
				$data['p_city_id']   	   = $p_city_id;
				$data['p_city_name']   	   = $p_city_name;
				$data['p_pincode']    	   = clean_and_escape($this->input->post('p_pincode'));		
			}	
            $data['last_modified']    	   	   =  date("Y-m-d H:i:s");			
			$this->db->where('id',$id);
			$update=$this->db->update('candidate',$data);			
			if($update){
			  $data=array();
			$is_old=0;
			  if($query->num_rows()>0){
				 $is_old=1;
				$old_doc=$query->row(); 
			  }
				
			  if ($_FILES['passport_pic']['name'] != "") {
				$fileName1           = $_FILES['passport_pic']['name'];
				$tmp1                = explode('.', $fileName1);
				$fileExtension1      = strtolower(end($tmp1));

				if($fileExtension1=='pdf'){
					$uploadable_file1    =  md5(uniqid(rand(), true)) . '.' . $fileExtension1;				         
					$data['passport_pic'] = $directory.$uploadable_file1;
					move_uploaded_file($_FILES['passport_pic']['tmp_name'], $directory.$uploadable_file1);
				}
				else{
					 $temp_path1 = $this->upload_model->upload_temp_image('passport_pic');
					if (!empty($temp_path1)) {				 
						$data['passport_pic']= $this->upload_model->new_image_upload($temp_path1, $directory);            
						$this->upload_model->delete_temp_image($temp_path1);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->passport_pic);
						}
					
					}
				}
			 }	


			 if ($_FILES['pan_card']['name'] != "") {
				$fileName2           = $_FILES['pan_card']['name'];
				$tmp2                = explode('.', $fileName2);
				$fileExtension2      = strtolower(end($tmp2));

				if($fileExtension2=='pdf'){
					$uploadable_file2    =  md5(uniqid(rand(), true)) . '.' . $fileExtension2;				         
					$data['pan_card'] = $directory.$uploadable_file2;
					move_uploaded_file($_FILES['pan_card']['tmp_name'], $directory.$uploadable_file2);
				}
				else{
					 $temp_path2 = $this->upload_model->upload_temp_image('pan_card');
					if (!empty($temp_path2)) {				 
						$data['pan_card']= $this->upload_model->new_image_upload($temp_path2, $directory);            
						$this->upload_model->delete_temp_image($temp_path2);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->pan_card);
						}
					}
				}
			 }
            $data['pan_no']  = clean_and_escape($this->input->post('pan_no'));	
								
			if ($_FILES['aadhar_card']['name'] != "") {
				$fileName3           = $_FILES['aadhar_card']['name'];
				$tmp3                = explode('.', $fileName3);
				$fileExtension3      = strtolower(end($tmp3));

				if($fileExtension3=='pdf'){
					$uploadable_file3    =  md5(uniqid(rand(), true)) . '.' . $fileExtension3;				         
					$data['aadhar_card'] = $directory.$uploadable_file3;
					move_uploaded_file($_FILES['aadhar_card']['tmp_name'], $directory.$uploadable_file3);
				}
				else{
					 $temp_path3 = $this->upload_model->upload_temp_image('aadhar_card');
					if (!empty($temp_path3)) {				 
						$data['aadhar_card']= $this->upload_model->new_image_upload($temp_path3, $directory);            
						$this->upload_model->delete_temp_image($temp_path3);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->aadhar_card);
						}
					}
				}
			}
            $data['aadhar_no']  = clean_and_escape($this->input->post('aadhar_no'));	
										
			if ($_FILES['bank_details']['name'] != "") {
				$fileName4           = $_FILES['bank_details']['name'];
				$tmp4                = explode('.', $fileName4);
				$fileExtension4      = strtolower(end($tmp4));

				if($fileExtension4=='pdf'){
					$uploadable_file4    =  md5(uniqid(rand(), true)) . '.' . $fileExtension4;				         
					$data['bank_details'] = $directory.$uploadable_file4;
					move_uploaded_file($_FILES['bank_details']['tmp_name'], $directory.$uploadable_file4);
				}
				else{
					 $temp_path4 = $this->upload_model->upload_temp_image('bank_details');
					if (!empty($temp_path4)) {				 
						$data['bank_details']= $this->upload_model->new_image_upload($temp_path4, $directory);            
						$this->upload_model->delete_temp_image($temp_path4);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->bank_details);
						}
					}
				}
			 }
			 
			 				
			if ($_FILES['educational']['name'] != "") {
				$fileName5           = $_FILES['educational']['name'];
				$tmp5                = explode('.', $fileName5);
				$fileExtension5      = strtolower(end($tmp5));

				if($fileExtension5=='pdf'){
					$uploadable_file5    =  md5(uniqid(rand(), true)) . '.' . $fileExtension5;				         
					$data['educational'] = $directory.$uploadable_file5;
					move_uploaded_file($_FILES['educational']['tmp_name'], $directory.$uploadable_file5);
				}
				else{
					 $temp_path5 = $this->upload_model->upload_temp_image('educational');
					if (!empty($temp_path5)) {				 
						$data['educational']= $this->upload_model->new_image_upload($temp_path5, $directory);            
						$this->upload_model->delete_temp_image($temp_path5);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->educational);
						}
					}
				}
			 }
			 
			if ($_FILES['salary_slip']['name'] != "") {
				$fileName6           = $_FILES['salary_slip']['name'];
				$tmp6                = explode('.', $fileName6);
				$fileExtension6      = strtolower(end($tmp6));

				if($fileExtension6=='pdf'){
					$uploadable_file6    =  md5(uniqid(rand(), true)) . '.' . $fileExtension6;				         
					$data['salary_slip'] = $directory.$uploadable_file6;
					move_uploaded_file($_FILES['salary_slip']['tmp_name'], $directory.$uploadable_file6);
				}
				else{
				 $temp_path6 = $this->upload_model->upload_temp_image('salary_slip');
				 if (!empty($temp_path6)) {				 
					$data['salary_slip']= $this->upload_model->new_image_upload($temp_path6, $directory);            
					$this->upload_model->delete_temp_image($temp_path6);
					if($is_old==1){			
					 $this->upload_model->delete_temp_image($old_doc->salary_slip);
					}
				 }
			   }
			 }	
			 
			 
			if ($_FILES['electricty_bill']['name'] != "") {
				$fileName7           = $_FILES['electricty_bill']['name'];
				$tmp7                = explode('.', $fileName7);
				$fileExtension7      = strtolower(end($tmp7));

				if($fileExtension7=='pdf'){
					$uploadable_file7    =  md5(uniqid(rand(), true)) . '.' . $fileExtension7;				         
					$data['electricty_bill'] = $directory.$uploadable_file7;
					move_uploaded_file($_FILES['electricty_bill']['tmp_name'], $directory.$uploadable_file7);
				}
				else{
					 $temp_path7 = $this->upload_model->upload_temp_image('electricty_bill');
					if (!empty($temp_path7)) {				 
						$data['electricty_bill']= $this->upload_model->new_image_upload($temp_path7, $directory);            
						$this->upload_model->delete_temp_image($temp_path7);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->electricty_bill);
						}
					}
				}
			 }	
			 
			if ($_FILES['rent_agreement']['name'] != "") {
				$fileName8           = $_FILES['rent_agreement']['name'];
				$tmp8                = explode('.', $fileName8);
				$fileExtension8      = strtolower(end($tmp8));

				if($fileExtension8=='pdf'){
					$uploadable_file8    =  md5(uniqid(rand(), true)) . '.' . $fileExtension8;				         
					$data['rent_agreement'] = $directory.$uploadable_file8;
					move_uploaded_file($_FILES['rent_agreement']['tmp_name'], $directory.$uploadable_file8);
				}
				else{
					 $temp_path8 = $this->upload_model->upload_temp_image('rent_agreement');
					if (!empty($temp_path8)) {				 
						$data['rent_agreement']= $this->upload_model->new_image_upload($temp_path8, $directory);            
						$this->upload_model->delete_temp_image($temp_path8);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->rent_agreement);
						}
					}
				}
			 }	
			 
			 				
			if ($_FILES['police_verification']['name'] != "") {
				$fileName10           = $_FILES['police_verification']['name'];
				$tmp10                = explode('.', $fileName10);
				$fileExtension10      = strtolower(end($tmp10));

				if($fileExtension10=='pdf'){
					$uploadable_file10    =  md5(uniqid(rand(), true)) . '.' . $fileExtension10;				         
					$data['police_verification'] = $directory.$uploadable_file10;
					move_uploaded_file($_FILES['police_verification']['tmp_name'], $directory.$uploadable_file10);
				}
				else{
					 $temp_path10 = $this->upload_model->upload_temp_image('police_verification');
					if (!empty($temp_path10)) {				 
						$data['police_verification']= $this->upload_model->new_image_upload($temp_path10, $directory);            
						$this->upload_model->delete_temp_image($temp_path10);
						if($is_old==1){			
						 $this->upload_model->delete_temp_image($old_doc->police_verification);
						}
					}
				}
			 }

			
            $data['hr_no']    = clean_and_escape($this->input->post('hr_no'));		
            $data['ref1_name']  = clean_and_escape($this->input->post('ref1_name'));		
            $data['ref1_mobile']  = clean_and_escape($this->input->post('ref1_mobile'));	
			
            $data['ref2_name']  = clean_and_escape($this->input->post('ref2_name'));		
            $data['ref2_mobile']  = clean_and_escape($this->input->post('ref2_mobile'));		
			 if($query->num_rows() > 0){
				$row = $query->row_array();
				$new_id = $row['id'];
				$data['updated_date'] = date("Y-m-d H:i:s");
				$this->db->where('id',$new_id);
				$this->db->where('candidate_id',$id);
				$this->db->update('candidate_document',$data);
				
				$data_follow = array();
				$data_follow = array(
					'action' 		=> 'timeline',
					'candidate_id'  => $id,
					'remark' 		=> 'Staff updated the document',
					'added_date' 	=> date("Y-m-d H:i:s"),
					'added_by_id' 	=> '',
					'added_by_name' => 'Documents Verification',
					'user_type' 	=> ''
				);
				$this->db->insert('candidate_followup', $data_follow);
				
				
				$this->session->set_flashdata('flash_message', get_phrase('document_updated_successfully'));
			 } else{
				$data['added_date'] = date("Y-m-d H:i:s");
				$data['candidate_id'] = $id;
				$this->db->insert('candidate_document',$data);
				
			    $data_follow = array();
				$data_follow = array(
					'action' 		=> 'timeline',
					'candidate_id'  => $id,
					'remark' 		=> 'Staff updated the document',
					'added_date' 	=> date("Y-m-d H:i:s"),
					'added_by_id' 	=> '',
					'added_by_name' => 'Documents Verification',
					'user_type' 	=> ''
				);
				$this->db->insert('candidate_followup', $data_follow);
				
				$this->session->set_flashdata('flash_message', get_phrase('document_added_successfully'));
			}
		  }
		  else{
			  $resultpost = array(
				"status" => 400, 
				"message" => get_phrase('data_not_updated_pls_try_later!'),
			);			  
		  }
		 }
		}
		else{		        
			$resultpost = array(
				"status" => 400, 
				"message" => get_phrase('only_verified_account_can_be_updated!'),
			);	
		}
		
        return simple_json_output($resultpost);
    }
    /*HR Head Ends*/	
	

}