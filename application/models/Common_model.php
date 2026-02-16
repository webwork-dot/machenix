<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common_model extends CI_Model{    
    
    // get table count
    public function get_count($table){

        return $this->db->count_all($table);
    }

    // get id by slug
    public function getIdBySlug($where,$table){
        $this->db->select('id');
        $this->db->from($table);
        $this->db->where($where);
        $query = $this->db->get();
        $row=$query->result();
        return $row[0]->id;
    }

    public function getRowByIdArr($table,$field,$where) {
        $this->db->select($field);
        $this->db->where($where);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
         $sql= $query->row_array();
         return $sql;
        }
        else{
         return '';
        }
    }
    
    public function getMaxId($table,$where=''){
        if($where==''){
            $this->db->select_max('id');
            $this->db->from($table);
            $query = $this->db->get();
            $row=$query->result();
            return $row[0]->id; 
        }
        else{
            $this->db->select_max('id');
            $this->db->from($table);
            $this->db->where($where);
            $query = $this->db->get();
            $row=$query->result();
            return $row[0]->id; 
        }
        
    }

    // get table count
    public function get_count_by_ids($where,$table){
        $this->db->from($table);
        $this->db->where($where);
        return $num_rows = $this->db->count_all_results();
    }


    //-- insert function
	public function insert($data,$table){
        $this->db->insert($table,$data);        
        return $this->db->insert_id();
    }

    //-- edit function
    function edit_option($action, $id, $table){
        $this->db->where('id',$id);
        $this->db->update($table,$action);
        return;
    } 

    //-- update function
    function update($action, $id, $table){
        $this->db->where('id',$id);
        $this->db->update($table,$action);
        return true;
    }  
    


    // update by multiple ids
    function updateByids($data, $ids, $table){
        $this->db->where($ids);
        $this->db->update($table,$data);
        return true;
    } 

    // update by in Operator
    function updateByIn($data, $ids, $table){
        $this->db->where_in('id', $ids);
        $this->db->update($table,$data);
        // echo $this->db->last_query();
        return true;
    } 

    //-- delete function
    function delete($id,$table){
        $this->db->delete($table, array('id' => $id));
        return true;
    }

    //-- delete by ids
    function deleteByids($where,$table){
        $this->db->delete($table, $where);
        return true;
    }

    //-- select function
    function select($table,$sort='ASC'){
        $this->db->select('*');
        $this->db->from($table);
        $this->db->order_by('id',$sort);
        $query = $this->db->get();
        $row=$query->result();  
        return $row;
    }

    //-- select function
    function selectWhere($table,$where,$sort='ASC',$sort_by='id'){
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where($where);
        $this->db->order_by($sort_by,$sort);
        $query = $this->db->get();
        $row=$query->result();  
        return $row;
    }

    //-- select by id
    function selectByorderId($id,$table){

        $this->db->select();
        $this->db->from($table);
        $this->db->where('order_unique_id', $id);
        $query = $this->db->get();
        $row=$query->result();
        if(!empty($row))
            return $row[0];
        else
            return false;
    }
    
    
    function selectByid($id,$table){

        $this->db->select();
        $this->db->from($table);
        $this->db->where('id', $id);
        $query = $this->db->get();
        $row=$query->result();
        if(!empty($row))
            return $row[0];
        else
            return false;
    }

    // select by in operator
    function selectByidsIN($ids,$table, $limit='', $start='', $brands='',$order_by=''){
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where_in('status', '1');
        $this->db->where_in('id', $ids);
        if($brands!=''){
            $ids=explode(',', $brands);
            $this->db->where_in('brand_id', $ids);
        }
        if($limit!=0 OR $limit!=''){
          $this->db->limit($limit, $start);
        }
        
        $query = $this->db->get();

        // echo $this->db->last_query();

        return $row=$query->result();
    }

    // select by in operator
    function selectByidsINWhere($ids,$table, $limit='', $start=''){
        $this->db->select('*');
        $this->db->from($table);
        if($limit!=0){
          $this->db->limit($limit, $start);
        }
        $query = $this->db->get();
        
        // echo $this->db->last_query();

        return $row=$query->result();
    }

     //-- select by id with parametes
    function selectByidParam($id,$table,$param){
        $this->db->select();
        $this->db->from($table);
        $this->db->where('id', $id);
        $query = $this->db->get();
        $row=$query->result();
        return $row[0]->$param;
    }

    //-- select by ids with parametes
    function selectByidsParam($ids,$table,$param){
        $this->db->select();
        $this->db->from($table);
        $this->db->where($ids);
        $query = $this->db->get();
        $row=$query->result();
        if($row)
            return $row[0]->$param;
        else
            return '';
        
    }

    //-- select by ids
    function selectByids($ids,$table,$sort_by='',$sort='DESC'){
        $this->db->select();
        $this->db->from($table);
        $this->db->where($ids);
        if($sort_by!=''){
            $this->db->order_by($sort_by,$sort);
        }
        $query = $this->db->get();
        $query = $query->result(); 

        // echo $this->db->last_query();

        return $query;
    } 

    
  public function get_state_name($id)
    {
        $id = clean_number($id);
        $this->db->where('states.id', $id);
        $query = $this->db->get('states');
        $sql=$query->row();
        return $sql->name;
    }
    
    public function get_city_name($id)
    {
        $id = clean_number($id);
        $this->db->where('cities.id', $id);
        $query = $this->db->get('cities');
        $sql= $query->row();
        return $sql->name;
    }




  public function state_list()
    {
        $query = $this->db->query("SELECT * FROM states WHERE id='1568' order by name");
        $count = $query->num_rows();
        $data  = array();
        foreach ($query->result_array() as $row) {
            $id   = $row['id'];
            $name = $row['name'];
            
            $resultpost[] = array(
                "id" => $id,
                "name" => $name
            );
        }
 
        return $resultpost;
    }
    
    public function city_list($state_id='1568')    {
        $query = $this->db->query("SELECT * FROM cities WHERE state_id='$state_id' AND id='17423' order by name");
        $count = $query->num_rows();
        $data  = array();
        foreach ($query->result_array() as $row) {
            $id   = $row['id'];
            $name = $row['name'];
            
            $resultpost[] = array(
                "id" => $id,
                "name" => $name
            );
        }
 
        return $resultpost;
    }
    
    
    //Unique Code Starts
      //Unique Code Starts
    public function unique_code_manager(){	
		$unique_code = $this->generateuniquecode();
		$check_inv_exist = $this->db->query("SELECT unique_code FROM `patient_orders` WHERE unique_code='$unique_code' LIMIT 1");
        if ($check_inv_exist->num_rows() == 0) {
            return $unique_code;
        } 
        else {
		    $unique_code_new = $this->generateuniquecode();
            $count = $this->db->query("SELECT unique_code FROM `patient_orders` WHERE unique_code='$unique_code_new' LIMIT 1");

            if ($count > 0) {
                // if new_invoice_id already exists
                $this->unique_code_manager($unique_code_new);
            } else {   
                return $unique_code_new;
            }
        }
    }
     
     public function candidate_unique_code_manager(){	
		$unique_code = $this->generateuniquecode();
		$check_inv_exist = $this->db->query("SELECT unique_code FROM `candidate` WHERE unique_code='$unique_code' LIMIT 1");
        if ($check_inv_exist->num_rows() == 0) {
            return $unique_code;
        } 
        else {
		    $unique_code_new = $this->generateuniquecode();
            $count = $this->db->query("SELECT unique_code FROM `candidate` WHERE unique_code='$unique_code_new' LIMIT 1");

            if ($count > 0) {
                // if new_invoice_id already exists
                $this->candidate_unique_code_manager($unique_code_new);
            } else {   
                return $unique_code_new;
            }
        }
    }


    public function generateuniquecode($digits = 8){
        $i = 0; //counter
        $pin = ""; 
        while($i < $digits){
            $pin .= mt_rand(1, 9);
            $i++;
        }
        return $pin;
    }
    
      
    public function getNameById($table,$field,$id) {
        $this->db->select($field);
        $this->db->where('id', $id);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->$field;
        }
        else{
         return '';
        }
    } 

    public function getNameByIdArr($table,$field,$where) {
        $this->db->select($field);
        $this->db->where($where);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->$field;
        }
        else{
         return '';
        }
    }    
    
    public function getSingleById($table,$field,$id) {
        $this->db->select($field);
        $this->db->where('id', $id);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql;
        }
        else{
         return array();
        }
    }
     

    
    public function getBulkNameIds($table,$field,$id) {
        $query = $this->db->query("SELECT $field FROM $table WHERE FIND_IN_SET(id, '$id')");
         $data  = array();
        foreach ($query->result_array() as $key => $row) {
            $name = $row[$field];
            $data[$key] = $name;
        }
       $new_data=implode(",",$data);
       return $new_data;
    }
   //Unique Code Ends 
   
 
 
    public function get_staff_access_by_id($access_code) {
        $access_id = '';
        $page_name = array();
        $resultdata = array();
        $access_id = explode(',',$access_code);
        foreach($access_id as $acc_id){
            $acc_name = $this->db->query("SELECT page_name FROM `oc_access_list` WHERE id='$acc_id' limit 1")->row_array();
                    
            if( strpos($acc_name['page_name'], ',') !== false ) {
               $page_arr=explode(",",$acc_name['page_name']);
               foreach($page_arr as $page){
                $page_name [] = $page;
               }
            }
            
            else{
             $page_name [] = $acc_name['page_name'];
            }
        }
        
        $resultdata = array(
            "access_id" => $access_id,
            "page_name" => $page_name,
        );
        return $resultdata;
    }
    
    
    
     public function getRowById($table,$field,$where) {
        $this->db->select($field);
        $this->db->where($where);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
         $sql= $query->row_array();
         return $sql;
        }
        else{
         return '';
        }
    }  
    
    public function getResultById($table,$field,$where) {
        $this->db->select($field);
        $this->db->where($where);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
         $sql= $query->result_array();
         return $sql;
        }
        else{
         return '';
        }
    }
    
    public function getSessionCompanies() {
        $user_id = $this->session->userdata('super_user_id');
        $type = $this->session->userdata('super_type');

        $user = $this->getRowById('sys_users', 'company_id', array('id' => $user_id));
        if($user != ''){
            $company_id = $user['company_id'];

            $keyword = '';
            if($type == 'staff') {
                $keyword = " AND id IN ('".$company_id."')";
            }

            $query = $this->db->query("SELECT * FROM company WHERE is_deleted = '0' $keyword");
            if($query->num_rows()>0){
                $sql = $query->result_array();
                return $sql;
            } else {
                return '';
            }
        } else{
           return '';
        }
    }

    public function getCountsById($table,$where) {
        $this->db->select('id');
        $this->db->where($where);
        $query = $this->db->get($table);
        return $query->num_rows();
    }
	
    function get_state_code($state){
        $this->db->select('code');
        $this->db->from('state_list');
        $this->db->where('state', trim($state));
        $query = $this->db->get();
        if($query->num_rows()>0){
         $code= $query->row()->code;
         return $code;
        }
        else{
         return '';
       }
    }

    /*CRM-BILL STARTS*/ 
           
	
	 public function add_hr_logs($logs){
		$data=array();
        $data['parent_id']     = $logs['parent_id'];
        $data['parent_table']  = $logs['parent_table'];
        $data['json_data']     = $logs['json_data'];
        $data['action']        = $logs['action'];
        $data['user_id']       = $this->session->userdata('super_user_id');
        $data['user_name']     = $this->session->userdata('super_name');
        $data['role_id']       = $this->session->userdata('super_role_id');
        $data['created_date']  = date('Y-m-d H:i:s');
        $this->db->insert('hr_log', $data);  
    }
	
	
	 public function get_leaves(){
        $added_by = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
       	$filter_data['date_range']  = $_REQUEST['date_range'];
        $filter_data['keywords']    = $_REQUEST['keywords'];
        
        if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
         $order_date=explode(' - ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  ($order_date[1]==NULL ? $from:date('Y-m-d',strtotime($order_date[1]))); 
          $sql_filter .=" AND (DATE(pure_date) BETWEEN '$from' AND '$to')"; 
        endif;  
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $sql_filter .= " AND (name like '%" . $keyword . "%'
            OR phone like '%" . $keyword . "%'
            OR email like '%" . $keyword . "%'
            OR state_name like '%" . $keyword . "%'
            OR city_name like '%" . $keyword . "%')";
        endif;
        

		$total_count = $this->db->query("SELECT id FROM candidate WHERE is_pure='1' AND is_doc='1' $sql_filter ORDER BY id desc")->num_rows();
   
        $query = $this->db->query("SELECT id,name,phone,email,state_name,city_name,pure_date FROM candidate WHERE is_pure='1' AND is_doc='1' $sql_filter ORDER BY DATE(pure_date) DESC LIMIT $start, $length");
       
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
               $id=$item['id'];                
               $details_url=base_url().'hr_head/candidate-details/'.$id;
               $assign_salary_url=base_url().'hr_head/update-salary/'.$id;                
			  
			   $action ='<a href="'.$assign_salary_url.'" target="_blank" data-toggle="tooltip" title="Assign Salary"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-briefcase" aria-hidden="true"></i></button></a>';              
			   $action .='<a href="'.$details_url.'" data-toggle="tooltip" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';              
			   $action .='<a href="#" onclick="get_timeline_('.$id.');" data-toggle="tooltip" title="Timeline" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd"><button type="button" class="btn mr-1 mb-1 icon-btn">View Timeline</button></a>';
                
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $item['id'],                   
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
	
	 public function get_count_staff_stype($salary_type) {
        $this->db->select('id');
        $this->db->where('salary_type',$salary_type);
        $this->db->where('is_kyc',1);
        $query = $this->db->get('candidate');
        return $query->num_rows();
    }
		
	 public function get_count_unread_task() {
		$user_id=$this->session->userdata('super_user_id');
        $this->db->select('id');
		$this->db->where("FIND_IN_SET($user_id, assigned_to) > 0");
        $this->db->where('status','Unread');
        $query = $this->db->get('hr_task');
        return $query->num_rows();
    }
	    
    public function getUsedPaidLeave($emp_id,$year) { 
		$total_paid_leave_ = $this->db->query("SELECT IFNULL(SUM(paid_leave), 0) AS total_paid_leave FROM paid_leave_history WHERE emp_id='$emp_id' AND year='$year'")->row()->total_paid_leave;
        return $total_paid_leave_;
    }
	
	    
    public function check_common_duplication($action = "", $table="", $field="", $field_name = "", $user_id = ""){
        $duplicate_email_check = $this->db->select('id')->get_where($table, array(
           $field => $field_name,
        ));
        
        if ($action == 'on_create') {
            if ($duplicate_email_check->num_rows() > 0) {
                return false;
            } else {
                return true;
            }
        } elseif ($action == 'on_update') {
            if ($duplicate_email_check->num_rows() > 0) {
                if ($duplicate_email_check->row()->id == $user_id) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }
	
   
     public function check_duplication($action = "", $table="", $field="", $field_name = "", $user_id = ""){
        $duplicate_email_check = $this->db->select('id')->get_where($table, array(
           $field => $field_name,
           'is_deleted' => 0,
        ));
        
        if ($action == 'on_create') {
            if ($duplicate_email_check->num_rows() > 0) {
                return false;
            } else {
                return true;
            }
        } elseif ($action == 'on_update') {
            if ($duplicate_email_check->num_rows() > 0) {
                if ($duplicate_email_check->row()->id == $user_id) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }
	
	public function check_dual_field_duplication($action = "", $table="", $field1="", $field1_value="", $field2="", $field2_value="", $user_id = ""){
			$this->db->select('id')->from($table)->where($field1, $field1_value)->where($field2, $field2_value);
			$query = $this->db->get();

			if ($action == 'on_create') {
				if ($query->num_rows() > 0) {
					return false; // Duplicate exists
				} else {
					return true; // No duplicate
				}
			} elseif ($action == 'on_update') {
				if ($query->num_rows() > 0) {
					// Check if the found record's ID matches the provided user_id
					if ($query->row()->id == $user_id) {
						return true; // Duplicate exists, but it's the same record being updated
					} else {
						return false; // Duplicate exists and it's a different record
					}
				} else {
					return true; // No duplicate
				}
			}
		 }
     
			 
		 public function check_triple_field_duplication($action = "", $table="", $field1="", $field1_value="", $field2="", $field2_value="", $field3="", $field3_value="", $user_id = ""){
			$this->db->select('id')->from($table)
					 ->where($field1, $field1_value)
					 ->where($field2, $field2_value)
					 ->where($field3, $field3_value);
			$query = $this->db->get();

			if ($action == 'on_create') {
				if ($query->num_rows() > 0) {
					return false; // Duplicate exists
				} else {
					return true; // No duplicate
				}
			} elseif ($action == 'on_update') {
				if ($query->num_rows() > 0) {
					// Check if the found record's ID matches the provided user_id
					if ($query->row()->id == $user_id) {
						return true; // Duplicate exists, but it's the same record being updated
					} else {
						return false; // Duplicate exists and it's a different record
					}
				} else {
					return true; // No duplicate
				}
			}
		}
		
   public function create_unique_slug($table,$field,$title,$id="") {
        $slug_ = preg_replace("/-$/","",preg_replace('/[^a-z0-9]+/i', "-", strtolower($title)));
        $slug =slugify($slug_);
        $this->db->select('COUNT(*) AS NumHits');
        $this->db->from($table);
        $this->db->where($field, $slug);
		if($id!=''){
		  $this->db->where('id!=', $id);	
		}
        $row =  $this->db->get()->row_array(); 
        $numHits = $row['NumHits'];
        $slug_final=($numHits > 0) ? ($slug . '-' . $numHits) : $slug;    
        return $slug_final;
    }
    
    
	public function get_all_warehouse_list()   {
        $resultdata = array();
        $query = $this->db->query("SELECT id,name FROM warehouse where is_deleted='0' order by sort asc");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                    "id" => $item['id'],
                    "name" => $item['name']
                );
            }
        }
        return $resultdata;
    }
	
	public function get_batch_product($product_id,$warehouse_id)   {
        $resultdata = array();
       $query = $this->db->query("SELECT warehouse_name,product_name,item_code,SUM(quantity) as quantity FROM inventory WHERE warehouse_id='$warehouse_id' and item_code='$product_id' group by product_id LIMIT 1");
	   //echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $item = $query->row_array();
                $resultdata= array(
                    "warehouse_name" => $item['warehouse_name'],
                    "product_name" => $item['product_name'],
                    "item_code" => $item['item_code'],
                    "quantity" => $item['quantity']
                );
            
        }
        return $resultdata;
    }
	
	public function get_batch_product_1($product_id,$warehouse_id)   {
        $resultdata = array();
       $query = $this->db->query("SELECT warehouse_name,product_name,item_code,SUM(quantity) as quantity FROM inventory WHERE warehouse_id='$warehouse_id' and id='$product_id' group by product_id LIMIT 1");
	   //echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $item = $query->row_array();
                $resultdata= array(
                    "warehouse_name" => $item['warehouse_name'],
                    "product_name" => $item['product_name'],
                    "item_code" => $item['item_code'],
                    "quantity" => $item['quantity']
                );
            
        }
        return $resultdata;
    }
    
    public function check_attribute_duplication($action = "", $table="", $field="", $field_name = "",$attr_id="", $user_id = ""){
        $duplicate_email_check = $this->db->select('id')->get_where($table, array(
           'attribute_id' => $attr_id,
           $field => $field_name,
        ));
        
        if ($action == 'on_create') {
            if ($duplicate_email_check->num_rows() > 0) {
                return false;
            } else {
                return true;
            }
        } elseif ($action == 'on_update') {
            if ($duplicate_email_check->num_rows() > 0) {
                if ($duplicate_email_check->row()->id == $user_id) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
     }
     
    function displayTreeOptions($tree, $selectedValues, $parentName = '', $level = 0) {
      foreach ($tree as $category) {
			$name = ($parentName !== '') ? $parentName . ' > ' . $category['name'] : $category['name'];
			
			$name = ucwords(strtolower($name));

            if($category['parent_id'] != 0) {
                $selected = (in_array($category['id'], $selectedValues)) ? 'selected' : '';
                echo '<option value="' . $category['id'] . '" ' . $selected . '>' . str_repeat('-', $level) . $name . "</option>";
            }

			if (isset($category['children'])) {
			   $this->displayTreeOptions($category['children'], $selectedValues, $name, $level + 1);
			}
		}
    }
    
}


if (!function_exists('clean_and_escape')) {
  function clean_and_escape($str){
        $CI =& get_instance();
        $CI->load->helper('security');

        // Remove white spaces and escape the string
        $cleaned_str = html_escape(trim($str));

        return $cleaned_str;
    }
}
