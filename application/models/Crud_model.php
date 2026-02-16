<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Crud_model extends CI_Model
{
    
    function __construct() {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }
    
    public function get_category_details() {
        $query= $this->get_categories_by_parent('0');
        $count = $query->num_rows();  
        $resultpost = array(); 
	    if($count>0){
            foreach ($query->result() as  $category) {	 
                $categoryFinal= $category->id.' | '.$category->name;   
                $resultpost[] = array(
                   "category_id" =>  $category->id,          
                   "type_id" =>  0,          
                   "name" =>  $categoryFinal,          
                );
                    
                $types= $this->get_categories_by_parent($category->id)->result();
                foreach ($types as  $type) {     
                    $categoryFinal= $type->id.' | '.$category->name.' > '.$type->name;    
                    $resultpost[] = array(
                       "category_id" =>  $category->id,          
                       "type_id" =>  $type->id,          
                       "name" =>  $categoryFinal,          
                    );	   
    	        }			 
	        }	  
        } else { 
            $resultpost = array();
        }
        return $resultpost;
    }
    
    public function check_excel_product_sku($sku) {
        $count=0;
        $sql = $this->db->query("SELECT id FROM raw_products WHERE item_code='$sku' AND is_deleted=0 LIMIT 1");
        $count=$sql->num_rows();
        
        return $count;
    } 
     
    public function import_products_excel_insert($fetchData){
	    $curr_data=date("Y-m-d H:i:s");
	    $count=0;
	    $returnData = array();
	    
    	$total_leads=0;
        foreach($fetchData as $item){
            
            $size_id = $item['sizes'];
            $check_sizes = $this->db->query("SELECT id FROM oc_attribute_values WHERE id='$size_id' LIMIT 1")->num_rows();
            
            if($check_sizes > 0){
                $is_variation = 1;  
               
                
               
				$validity = $this->check_excel_product_sku(trim($item['item_code']));  
				if($validity > 0) {
                    $getProduct = $this->db->where('item_code', trim($item['item_code']))->get('raw_products')->row_array();
                    $product_size = explode(',', $getProduct['sizes']);
                    if(!in_array($item['sizes'], $product_size)) {
                        // Updating Product Size
                        $product_size[] = $item['sizes'];
                        $update['sizes'] = implode(',', $product_size);
                        $this->db->where('id', $getProduct['id'])->update('raw_products', $update);
                        
                        // Adding Size Variation
                        $siz = $this->common_model->getRowById('oc_attribute_values', 'name', ['id' => $item['sizes']]);
                        $variation = [];
                        $variation['product_id']    = $getProduct['id'];
                        $variation['size_id']       = $item['sizes'];
                        $variation['size_name']     = $siz['name'];
                        $variation['name']          = $getProduct['name'];
                        $variation['sku_code']      = $getProduct['item_code'] . ' - ' . $siz['name'];
                        $variation['is_other']      = 0;
                        $variation['listed_1']      = $getProduct['listed_1'];
                        $variation['listed_2']      = $getProduct['listed_2'];
                        $variation['listed_3']      = $getProduct['listed_3'];
                        $variation['listed_4']      = $getProduct['listed_4'];
                        $variation['listed_5']      = $getProduct['listed_5'];
                        $variation['listed_6']      = $getProduct['listed_6'];
                        $variation['listed_7']      = $getProduct['listed_7'];
                        $this->db->insert('product_variation', $variation);
                    }
				} else {
				    $color = $this->common_model->getRowById('colors', 'name', ['id' => $item['color_id']]);
				    
				    // Adding New Product
                    $data = [];
                    $data['name']           = $item['name'];
                    $data['is_variation']   = $item['is_variation'];
                    $data['group_id']       = $item['group_id'];
                    $data['color_id']       = $item['color_id'];
                    $data['color_name']     = $color['name'];
                    $data['sizes']          = $item['sizes'];
                    $data['categories']     = $item['categories'];
                    $data['unit']           = $item['unit'];
                    $data['item_code']      = $item['item_code'];
                    $data['hsn_code']       = $item['hsn_code'];
                    $data['type']           = $item['type'];
                    $data['min_stock']      = $item['min_stock'];
                    $data['intimation']     = $item['intimation'];
                    $data['product_mrp']    = $item['product_mrp'];
                    $data['costing_price']  = $item['costing_price'];
                    $data['status']         = $item['status'];
                    $data['cartoon_qty']    = $item['cartoon_qty'];
                    $data['listed_1']       = $item['listed_1'];
                    $data['listed_2']       = $item['listed_2'];
                    $data['listed_3']       = $item['listed_3'];
                    $data['listed_4']       = $item['listed_4'];
                    $data['listed_5']       = $item['listed_5'];
                    $data['listed_6']       = $item['listed_6'];
                    $data['listed_7']       = $item['listed_7'];
                    $data['added_date']     = $item['added_date'];
                    
				    $this->db->insert('raw_products', $data);
                    $user_id = $this->db->insert_id();
                    
                    // Adding Product Variation
                    $siz = $this->common_model->getRowById('oc_attribute_values', 'name', ['id' => $item['sizes']]);
                    $variation = [];
                    $variation['product_id']    = $user_id;
                    $variation['size_id']       = $item['sizes'];
                    $variation['size_name']     = $siz['name'];
                    $variation['name']          = $item['name'];
                    $variation['sku_code']      = $item['item_code'] . ' - ' . $siz['name'];
                    $variation['is_other']      = 0;
                    $variation['listed_1']      = $item['listed_1'];
                    $variation['listed_2']      = $item['listed_2'];
                    $variation['listed_3']      = $item['listed_3'];
                    $variation['listed_4']      = $item['listed_4'];
                    $variation['listed_5']      = $item['listed_5'];
                    $variation['listed_6']      = $item['listed_6'];
                    $variation['listed_7']      = $item['listed_7'];
                    $this->db->insert('product_variation', $variation);
				}
				
                $validity = $this->check_excel_product_sku($item['item_code']);  
                
                if($validity>0){
                    $returnData[] = array(
                        'name'           => $item['name'],
                        'is_variation'   => $item['is_variation'],
                        'group_id'       => $item['group_id'],
                        'color_id'       => $item['color_id'],
                        'color_name'     => $color['name'],
                        'sizes'          => $item['sizes'],
                        'categories'     => $item['categories'],
                        'unit'           => $item['unit'],
                        'item_code'      => $item['item_code'],
                        'hsn_code'       => $item['hsn_code'],
                        'type'           => $item['type'],
                        'min_stock'      => $item['min_stock'],
                        'intimation'     => $item['intimation'],
                        'product_mrp'    => $item['product_mrp'],
                        'costing_price'  => $item['costing_price'],
                        'status'         => $item['status'],
                        'cartoon_qty'    => $item['cartoon_qty'],
                        'listed_1'       => $item['listed_1'],
                        'listed_2'       => $item['listed_2'],
                        'listed_3'       => $item['listed_3'],
                        'listed_4'       => $item['listed_4'],
                        'listed_5'       => $item['listed_5'],
                        'listed_6'       => $item['listed_6'],
                        'listed_7'       => $item['listed_7'],
                        'added_date'     => $item['added_date'],
                  );
                }
          } else {
                 $returnData[] = array(
                    'name'           => $item['name'],
                    'is_variation'   => $item['is_variation'],
                    'group_id'       => $item['group_id'],
                    'color_id'       => $item['color_id'],
                    'color_name'     => $item['color_name'],
                    'sizes'          => $item['sizes'],
                    'categories'     => $item['categories'],
                    'unit'           => $item['unit'],
                    'item_code'      => $item['item_code'],
                    'hsn_code'       => $item['hsn_code'],
                    'type'           => $item['type'],
                    'min_stock'      => $item['min_stock'],
                    'intimation'     => $item['intimation'],
                    'product_mrp'    => $item['product_mrp'],
                    'costing_price'  => $item['costing_price'],
                    'status'         => $item['status'],
                    'cartoon_qty'    => $item['cartoon_qty'],
                    'listed_1'       => $item['listed_1'],
                    'listed_2'       => $item['listed_2'],
                    'listed_3'       => $item['listed_3'],
                    'listed_4'       => $item['listed_4'],
                    'listed_5'       => $item['listed_5'],
                    'listed_6'       => $item['listed_6'],
                    'listed_7'       => $item['listed_7'],
                    'added_date'     => $item['added_date'],
               );   
          }
    	}
        return $returnData;
	}
	
    public function get_colors() {
        $this->db->order_by('name'); 
        return $this->db->get('colors');
    }
    
    public function get_warehouse() {
        $this->db->order_by('name'); 
        return $this->db->get('warehouse');
    }
    
    public function get_units() {
        $this->db->order_by('name'); 
        return $this->db->get('units');
    }
    
    public function get_sizes()
	{
		$this->db->order_by('name'); 
		$this->db->where('attribute_id', '2');
		$query = $this->db->get('oc_attribute_values');
		return $query;
	}
    
    public function get_categories_by_parent($parent_id) {
		$this->db->order_by('name'); 
		$this->db->where('parent_id', $parent_id);
		$query = $this->db->get('categories');
		return $query;
	}
    
    public function admin_change_password($user_id) {
        $data = array();
        if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $user_details     = $this->crud_model->get_user_by_id($user_id)->row_array();
            $current_password = $this->input->post('current_password');
            $new_password     = $this->input->post('new_password');
            $confirm_password = $this->input->post('confirm_password');
            
            if ($user_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
                $data['password'] = sha1($new_password);
            } else {
                $this->session->set_flashdata('error_message', get_phrase('mismatch_password'));
                return;
            }
        }
        
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
        $this->session->set_flashdata('flash_message', get_phrase('password_updated'));
    }
   
   public function get_all_user($user_id = 0) {
        if ($user_id > 0) {
            $this->db->where('id', $user_id);
        }
        return $this->db->get('sys_users');
    }
    
    public function get_state_name($id) {
        $this->db->select('state');
        $this->db->where('id', $id);
        $query = $this->db->get('state_list');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->state;
        }
        else{
         return '';
        }
    }
    
    public function get_city_name($id) {
        $this->db->select('district');
        $this->db->where('id', $id);
        $query = $this->db->get('city_list');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->district;
        }
        else{
         return '';
        }
    }
    
    public function get_area_name($id) {
        $this->db->select('area');
        $this->db->where('id', $id);
        $query = $this->db->get('area_list');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->area;
        }
        else{
         return '';
        }
    }
    
    public function get_user_type_name($id) {
        $this->db->select('name');
        $this->db->where('id', $id);
        $query = $this->db->get('user_type');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->name;
        }
        else{
         return '';
        }
    }
    

    
    public function get_states(){
        $resultdata=array();
        $query = $this->db->query("SELECT id, state  FROM `state_list` ORDER BY id asc");
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $item['state'],
              );
        
            }
        }
        
      return $resultdata;
    }

    public function get_city_by_state($state_id){
        $resultdata=array();
        $query = $this->db->query("SELECT id, district FROM `city_list` WHERE state_id='$state_id' ORDER BY district asc");
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $item['district'],
              );
        
            }
        }
      return $resultdata;
    }
    
    
    public function get_area_by_city($area_id){
        $resultdata=array();
        $query = $this->db->query("SELECT id, area FROM `area_list` WHERE district_id='$area_id' ORDER BY area asc");
        if (!empty($query)) { 
            $data=array();
            foreach ($query->result_array() as $item) {
                $resultdata[] = array(
                 "id"   => $item['id'],
                 "name" => $item['area'],
              );
        
            }
        }
        
      return $resultdata;
    }
    
    
    public function get_user_type(){
        $resultdata=array();
        $query = $this->db->query("SELECT id, name  FROM `user_type` ORDER BY id asc");
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

   public function get_staff(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
        $data= array(); 
        $keyword_filter="";
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $keyword_filter = " AND (CONCAT_WS(' ',first_name,last_name) like '%" . $keyword . "%' 
            OR city_name like '%" . $keyword . "%' 
            OR state_name like '%" . $keyword . "%' 
            OR email like '%" . $keyword . "%' 
            OR phone like '%" . $keyword . "%')";
        endif;

        $total_count = $this->db->query("SELECT id FROM sys_users WHERE (is_deleted='0') AND type!='admin' $keyword_filter ORDER BY first_name ASC")->num_rows();
        $query = $this->db->query("SELECT id, first_name, last_name, type, email, phone, city_name, state_name,role_id, status,date_added FROM sys_users WHERE (is_deleted='0') AND type!='admin' $keyword_filter ORDER BY id DESC LIMIT $start, $length");
        
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $id=$item['id'];
              $role_id=$item['role_id'];
              if($item['status']== 1){
                $status='<div class="chip chip-success"><div class="chip-body"><span class="chip-text">Active</span></div></div>';   
              }else{
                $status='<div class="chip chip-danger"><div class="chip-body"><span class="chip-text">Inactive</span></div></div>';
              }
              
             
             $delete_url="confirm_modal('".admin_url()."staff/delete/".$id."','Are you sure want to delete!')";
             $edit_url=base_url().'admin/staff/edit/'.$id;
             $change_password_url=base_url().'admin/staff/change-password/'.$id;             
              $action='';
             $action .='<a href="'.$edit_url.'" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="#" onclick="'.$delete_url.'" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
             <a href="'.$change_password_url.'"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" data-toggle="tooltip" data-bs-placement="top" title="Change Password"><i class="fa fa-key" aria-hidden="true"></i></button> </a>'; 
              
                $data[] = array(
                    "sr_no"       => ++$start,
                    "id"          => $item['id'],
                    "name"        => $item['first_name'].' '.$item['last_name'],
                    "email"       => $item['email'],
                    "phone"       => $item['phone'],
                    "type"        => $item['type'],
                    "city_name"   => $item['city_name'],
                    "state_name"  => $item['state_name'],
                    "status"      => $status,
                    "date"   	  => date("d M, Y h:i A", strtotime($item['date_added'])),
                    "action"      => $action,
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
    
    
    
    public function check_staff_duplication($action = "",$field="", $email = "",$role_id="", $user_id = ""){
        $duplicate_email_check = $this->db->get_where('sys_users', array(
           $field => $email,
           "is_deleted" => 0,
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
     
    
    public function add_staff(){
         $resultpost = array(
    		"status" => 200,
    		"message" => get_phrase('staff_added_successfully'),
    		"url" => $this->session->userdata('previous_url'),
    	);
        
    	$email           = html_escape($this->input->post('email'));
    	$phone           = html_escape($this->input->post('phone'));
        $user_type_id    = html_escape($this->input->post('user_type_id'));
        $role_id=$user_type_id;
        
    	if($email!=''){
    		$check_email = $this->check_staff_duplication('on_create','email', $email ,$role_id);
    	}
    	else{
    	   $check_email=true; 
    	}
    	
    	if($phone!=''){
    			$check_phone = $this->check_staff_duplication('on_create','phone', $phone, $role_id);
    	}
    	else{
    	   $check_phone=true; 
    	}

    	if ($check_email == false) {
    		$this->session->set_flashdata('error_message', get_phrase('email_duplication'));
    		$resultpost = array(
    		 "status" => 400,
    		 "message" => 'Email Duplication'
    	   );
    		
    	}
    	
    	elseif ($check_phone == false) {
    		$this->session->set_flashdata('error_message', get_phrase('phone_duplication')); 
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
	    } 
    	else {	
    	    
		$data=array();
    	$data['first_name'] = html_escape($this->input->post('first_name'));
    	$data['last_name']  = html_escape($this->input->post('last_name'));
    	$data['email']      = $email;
    	$data['phone']      = $phone;
    	
        $data['alt_phone']     = html_escape($this->input->post('alt_phone'));
        $state_id              = html_escape($this->input->post('state_id'));
        $state_name            = $this->crud_model->get_state_name($state_id);
        $data['state_id']      = $state_id;
        $data['state_name']    = $state_name;
        $city_id               = html_escape($this->input->post('city_id'));
        $city_name             = $this->crud_model->get_city_name($city_id);
        $data['city_id']       = $city_id;
        $data['city_name']     = $city_name;
        $data['address']       = html_escape($this->input->post('address'));
        $data['date_birth']    = html_escape($this->input->post('date_birth'));
        $data['join_date']     = html_escape($this->input->post('join_date'));       
    	$data['password']      = sha1(html_escape($this->input->post('password')));
        
        $query_user = $this->db->query("SELECT name FROM user_type WHERE id='$user_type_id'")->row_array();
        
        $data['role_id']       = $user_type_id;
        $data['type']          = $query_user['name'];
        //print_r($data);die;
        $data['status']        = html_escape($this->input->post('status'));
		$data['date_added']    = date("Y-m-d H:i:s");
    	
    	$this->db->insert('sys_users', $data);
    	$user_id = $this->db->insert_id();
    	$this->session->set_flashdata('flash_message', get_phrase('staff_added_successfully'));
       }
       return simple_json_output($resultpost); 
    }
    
    public function edit_staff($id){   
       $resultpost = array(
    		"status" => 200,
    		"message" => get_phrase('staff_updated_successfully'),
    		"url" => $this->session->userdata('previous_url'),
    	);
        
        $email     = html_escape($this->input->post('email'));
    	$phone     = html_escape($this->input->post('phone'));
        $user_type_id          = html_escape($this->input->post('user_type_id'));
        $role_id=$user_type_id;
        
    	if($email!=''){
    		$check_email = $this->check_staff_duplication('on_update','email', $email ,$role_id,$id);
    	}
    	else{
    	   $check_email=true; 
    	}
    	
    	if($phone!=''){
    			$check_phone = $this->check_staff_duplication('on_update','phone', $phone, $role_id,$id);
    	}
    	else{
    	   $check_phone=true; 
    	}
    	
    	$check_phone = $this->check_staff_duplication('on_update','phone', $phone, $role_id,$id);
    	if ($check_email == false) {
    		$this->session->set_flashdata('error_message', get_phrase('email_duplication'));
    		$resultpost = array(
    		 "status" => 400,
    		 "message" => 'Email Duplication'
    	   );
    		
    	}
    	elseif ($check_phone == false) {
    		$this->session->set_flashdata('error_message', get_phrase('phone_duplication')); 
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
	    } 
    	else {	
		$data=array();
        $data['first_name']    = html_escape($this->input->post('first_name'));
        $data['last_name']     = html_escape($this->input->post('last_name'));   
        $data['email']      = $email;
    	$data['phone']      = $phone;
        $data['alt_phone']     = html_escape($this->input->post('alt_phone'));     
        $state_id              = html_escape($this->input->post('state_id'));
        $state_name            = $this->crud_model->get_state_name($state_id);
        $data['state_id']      = $state_id;
        $data['state_name']    = $state_name;
        $city_id               = html_escape($this->input->post('city_id'));
        $city_name             = $this->crud_model->get_city_name($city_id);
        $data['city_id']       = $city_id;
        $data['city_name']     = $city_name;
        $data['address']       = html_escape($this->input->post('address'));
        $data['date_birth']    = html_escape($this->input->post('date_birth'));
        $data['join_date']     = html_escape($this->input->post('join_date'));
        $user_type_id          = html_escape($this->input->post('user_type_id'));
        
        $query_user = $this->db->query("SELECT name FROM user_type WHERE id='$user_type_id'")->row_array();
        
        $data['role_id']       = $user_type_id;
        $data['type']          = $query_user['name'];
        
        $data['status']        = html_escape($this->input->post('status'));
		$data['last_modified'] = date("Y-m-d H:i:s");

        $this->db->where('id', $id);
        $this->db->update('sys_users', $data);
        $this->session->set_flashdata('flash_message', get_phrase('staff_updated_successfully'));  
        }
       return simple_json_output($resultpost); 
    }
	
	 public function user_change_password($user_id) {
        $data = array();
        if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $user_details = $this->get_all_user($user_id)->row_array();
            //$current_password = $this->input->post('current_password');
            
            $new_password = $this->input->post('new_password');
            $confirm_password = $this->input->post('confirm_password');

            if ($user_details['password'] = $new_password == $confirm_password) {
                $data['password'] = sha1($new_password);
                // print_r($data);die;
                $this->db->where('id', $user_id);
                $this->db->update('sys_users', $data);
                $this->session->set_flashdata('flash_message', get_phrase('password_updated'));
				redirect($this->session->userdata('previous_url'));				
            } else {
               $this->session->set_flashdata('error_message', get_phrase('mismatch_password')); return;
            }
        }
    }
    
    public function get_staff_by_id($id){
        $this->db->where('id', $id);
        return $this->db->get('sys_users');
    }
    
    public function delete_staff($id) {  
	$resultpost = array(
    		"status" => 200,
    		"message" => get_phrase('staff_deleted_successfully'),
    		"url" => $this->session->userdata('previous_url'),
    	);
        
        $data['is_deleted'] = 1;
        $this->db->where('id', $id);
        $this->db->update('sys_users', $data);  
       
       return simple_json_output($resultpost); 
    }    
	
    public function get_raw_products_by_id($id){ 
        $this->db->select('id,name,item_code,unit,form');
        $this->db->where('id', $id);
        return $this->db->get('raw_products');
    }
	
    public function get_my_reminder_count(){
      $user_id = $this->session->userdata('super_user_id');
      $query = $this->db->query("SELECT COUNT(id) as count FROM reminder WHERE added_by_id = ? AND status = 'pending'", array($user_id));    
      if($query){
        return $query->row()->count;
      } else {
        return 0;
      } 
    }
	
	/*HR STARTS*/
		
     public function get_candidate_followup_by_id($id){
        $this->db->where('id', $id);
        return $this->db->get('candidate_followup');
    }    
	/*HR ENDS*/

 }
