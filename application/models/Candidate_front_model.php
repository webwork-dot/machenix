<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Candidate_front_model extends CI_Model
{
    
    function __construct() {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }
    
    public function get_candidate_unique_by_id($id)  {
        $this->db->select('id');
        $this->db->where('unique_code', $id);
        $this->db->where('is_selected', '1');
        return $this->db->get('candidate');
    } 
    
    public function get_candidate_document_by_id($id)  {
        $this->db->select('*');
        $this->db->where('candidate_id', $id);
        return $this->db->get('candidate_document')->row_array();
    } 
    
    public function get_candidate_details_by_id($order_id){
        $resultdata = array();
        $query  = $this->db->query("SELECT id,name,phone,email,state_name,city_name,area_name,address,dob,doa FROM `candidate` WHERE (id='$order_id' and is_selected='1') limit 1");
		
		if($query->num_rows() > 0){
		    $row = $query->row_array();
			$resultdata  = array(
				"id" => $row['id'],
				"name"          => $row['name'],
				"phone"        => ($row['phone'] !='' && $row['phone'] !=null) ? $row['phone'] : '-',
				"email"        => ($row['email'] !='' && $row['email'] !=null) ? $row['email'] : '',
				"state_name"      => ($row['state_name'] !='' && $row['state_name'] !=null) ? $row['state_name'] : '-',
				"city_name"     => ($row['city_name'] !='' && $row['city_name'] !=null) ? $row['city_name'] : '-',
				"area_name" 			=> ($row['area_name'] !='' && $row['area_name'] !=null) ? $row['area_name'] : '-',
				"address"   			=> ($row['address'] !='' && $row['address'] !=null) ? $row['address'] : '-',
				"dob"   	=> ($row['dob'] !='' && $row['dob'] !=null) ? $row['dob'] : '-',
				"doa"        => ($row['doa'] !='' && $row['doa'] !=null) ? $row['doa'] : '-',
			);
		}
		
        return $resultdata;
    } 
    
    public function add_documentation($id){ 
        $resultpost = array(
            "status" => 200, 
            "message" => get_phrase('document_updated_successfully'),
            "url" => base_url('candidate/thank-you'),
        );
		
		$query_c = $this->db->query("SELECT id,is_doc  FROM `candidate` WHERE id='$id' limit 1");		
		$query = $this->db->query("SELECT id  FROM `candidate_document` where candidate_id='$id' limit 1");
				
		$year      = date("Y");
		$month     = date("m");
		$day       = date("d");
		$directory = "uploads/candidate_document/" . "$year/$month/$day/";	
		if (!is_dir($directory)) {
			mkdir($directory, 0755, true);
		}
		
		if($query_c->row()->is_doc==0){
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
			$this->form_validation->set_rules('marital_status', 'Marital Status', 'trim|required');
			$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required');
			//$this->form_validation->set_rules('doa', 'Date of Joining', 'trim|required');
			$this->form_validation->set_rules('address', 'Flat/ Building/ Street', 'required');
			$this->form_validation->set_rules('state_id', 'State', 'trim|required');
			$this->form_validation->set_rules('city_id', 'City', 'trim|required');
			$this->form_validation->set_rules('pincode', 'Pincode', 'trim|required');
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
					//'doa' 	 			 => form_error('doa'),					
					'address' 	 		 => form_error('address'),					
					'state_id' 	 		 => form_error('state_id'),					
					'city_id' 	 		 => form_error('city_id'),					
					'pincode' 	 		 => form_error('pincode'),						
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
			
			
			$dob=$this->input->post('dob');
			$doa='';
			
        	$bank_id = html_escape($this->input->post('bank_id'));
			$bank = $this->common_model->getNameById('emp_bank','name',$bank_id);
			
            $data=array();
        	$data['bank_id']      	   = $bank_id;
        	$data['bank']         	   = $bank;
        	$data['account_no']    	   = html_escape($this->input->post('account_no'));
        	$data['ifsc_code']     	   = html_escape($this->input->post('ifsc_code'));
			$data['email']        	   = html_escape($this->input->post('email'));
			$data['marital_status']    = html_escape($this->input->post('marital_status'));
			$data['dob']       		   = ($dob!='' ? date("Y-m-d", strtotime($dob)):NULL);
			$data['doa']       		   = ($doa!='' ? date("Y-m-d", strtotime($doa)):NULL);	
			
			$data['address']       	   = html_escape($this->input->post('address'));			
			$state_id                  = html_escape($this->input->post('state_id'));
            $state_name                = $this->crud_model->get_state_name($state_id); 
			$data['state_id']    	   = $state_id;
            $data['state_name'] 	   = $state_name;
            $city_id            	   = html_escape($this->input->post('city_id'));
            $city_name          	   = $this->crud_model->get_city_name($city_id);
            $data['city_id']   		   = $city_id;
            $data['city_name']   	   = $city_name;
            $data['pincode']    	   = html_escape($this->input->post('pincode'));
			
						
			$is_same= html_escape($this->input->post('is_same'));	
			$data['is_same']    	   = $is_same;
			 
			if($is_same==1){
				$data['p_address']         = html_escape($this->input->post('address'));		
				$data['p_state_id']    	   = $state_id;
				$data['p_state_name'] 	   = $state_name;
				$data['p_city_id']   	   = $city_id;
				$data['p_city_name']   	   = $city_name;
				$data['p_pincode']    	   = html_escape($this->input->post('pincode'));	
			}
			else{
				$data['p_address']         = html_escape($this->input->post('p_address'));			
				$p_state_id                = html_escape($this->input->post('p_state_id'));
				$p_state_name              = $this->crud_model->get_state_name($p_state_id); 
				$data['p_state_id']    	   = $p_state_id;
				$data['p_state_name'] 	   = $p_state_name;
				$p_city_id            	   = html_escape($this->input->post('p_city_id'));
				$p_city_name          	   = $this->crud_model->get_city_name($p_city_id);
				$data['p_city_id']   	   = $p_city_id;
				$data['p_city_name']   	   = $p_city_name;
				$data['p_pincode']    	   = html_escape($this->input->post('p_pincode'));		
			}	
			
            $data['is_doc']    			   = 1;	
            $data['doc_date']    	   	   =  date("Y-m-d H:i:s");
			
			$this->db->where('id',$id);
			$update=$this->db->update('candidate',$data);
			
			
			if($update){
			  $data=array();
				
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
					}
				}
			 }
            $data['pan_no']  = html_escape($this->input->post('pan_no'));	
								
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
					}
				}
			}
            $data['aadhar_no']  = html_escape($this->input->post('aadhar_no'));	
										
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
					}
				}
			 }

			
            $data['hr_no']    = html_escape($this->input->post('hr_no'));		
            $data['ref1_name']  = html_escape($this->input->post('ref1_name'));		
            $data['ref1_mobile']  = html_escape($this->input->post('ref1_mobile'));	
			
            $data['ref2_name']  = html_escape($this->input->post('ref2_name'));		
            $data['ref2_mobile']  = html_escape($this->input->post('ref2_mobile'));		
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
				"message" => get_phrase('document_is_already_updated!'),
			);	
		}
		
        return simple_json_output($resultpost);
    }
    
}