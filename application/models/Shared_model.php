<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shared_model extends CI_Model
{
    
    function __construct() {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }
    
	 
	public function get_reminder(){  
		$added_by_id = $this->session->userdata('super_user_id');
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $search_value = $_REQUEST['search']['value'];
        $data= array(); 
        $sql_filter="";
        
        $filter_data['date_range']  = $_REQUEST['date_range'];
	    $filter_data['keywords']     = $_REQUEST['keywords'];
	    $status    				     = $_REQUEST['status'];
        
		if(isset($filter_data['date_range']) && $filter_data['date_range']!="") :
          $order_date=explode(' to ',$filter_data['date_range']);
          $from =  date('Y-m-d',strtotime($order_date[0])); 
          $to =  date('Y-m-d',strtotime($order_date[1])); 
          $keyword_filter .=" AND (DATE(reminder_date) BETWEEN '$from' AND '$to')"; 
        endif; 
	    	
        if(isset($filter_data['keywords']) && $filter_data['keywords']!="") :
            $keyword        = $filter_data['keywords'];
            $sql_filter .= " AND (title like '%".$keyword."%'  
            OR description like '%" . $keyword . "%')";
        endif;
		
		if($status=='pending'){
			$total_count = $this->db->query("SELECT id FROM reminder WHERE added_by_id='$added_by_id' AND status='$status' $sql_filter ORDER BY id desc")->num_rows();
			$query = $this->db->query("SELECT id,title,description,done_date,reminder_date,status,created_at FROM reminder WHERE added_by_id='$added_by_id' AND status='$status' $sql_filter ORDER BY id desc LIMIT $start, $length");
		}
		elseif($status=='done'){
			$total_count = $this->db->query("SELECT id FROM reminder_done WHERE added_by_id='$added_by_id' AND status='$status' $sql_filter ORDER BY id desc")->num_rows();
			$query = $this->db->query("SELECT id,title,description,done_date,reminder_date,status,created_at FROM reminder_done WHERE added_by_id='$added_by_id' AND status='$status' $sql_filter ORDER BY id desc LIMIT $start, $length");			
		}		
		else{
			$total_count = 0;
			$query =array();			
		}
        //echo $this->db->last_query();exit();
        if (!empty($query)) {        
           foreach ($query->result_array() as $item) {
              $id=$item['id'];  	                  
				
               $delete_url="confirm_modal('".base_url()."common/reminder/delete/".$id."','Are you sure want to delete this!')";  
			   $edit_url=base_url().'common/reminder/edit/'.$id;
			   $action ='';		

		      if($status=='pending'){			   
			   $action .='<a href="'.$edit_url.'" data-toggle="tooltip" title="Edit reminder"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-edit" aria-hidden="true"></i></button></a>';              
			   $action .='<a href="#" onclick="'.$delete_url.'" data-toggle="tooltip" data-placement="bottom" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
			  }
			  else{
               $delete_url="confirm_modal('".base_url()."common/reminder_done/delete/".$id."','Are you sure want to delete this!')";  
			   $action .='<a href="#" onclick="'.$delete_url.'" data-toggle="tooltip" data-placement="bottom" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
				  
			  }	
			   
                $data[] = array(
                    "sr_no"         => ++$start,
                    "id"            => $id, 
                    "title"         => $item['title'],
                    "description"   => $item['description'],
                    "reminder_date" => date("d M, Y h:i A", strtotime($item['reminder_date'])),
                    "date"          => date("d M, Y h:i A", strtotime($item['created_at'])),
                    "done_date"     => ($item['done_date']!='' ? date("d M, Y h:i A", strtotime($item['done_date'])):'-'),
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
    
    public function add_reminder(){                 
           $resultpost = array(
            "status" => 200,
            "message" => get_phrase('reminder_added_successfully'),
            "url" => base_url('common/reminder'),
           );  
        
        $this->form_validation->set_rules('title', 'Title', 'trim|required|max_length[30]');
        $this->form_validation->set_rules('rem_date', 'Reminder Date', 'trim|required');
        $this->form_validation->set_rules('rem_time', 'Reminder Time', 'trim|required');
		
		
		$title = clean_and_escape($this->input->post('title'));
		$added_by_id=$this->session->userdata('super_user_id');
        $check_name = $this->common_model->check_dual_field_duplication('on_create','reminder','title',$title,'added_by_id',$added_by_id);  
		
		if ($this->form_validation->run() == FALSE){
		     $errors = array(
                'title'    => form_error('title'),
                'rem_date' => form_error('rem_date'),
                'rem_time' => form_error('rem_time'),
			);
			
			$errorString = implode("\n", $errors);
			  
           $resultpost = array(
                "status" => 400,
                "message" => $errorString,
                "errors" => $errors,
            );  
		}	
		else if ($check_name == false) {
            $resultpost = array(
                "status" => 400,
                "message" => 'Reminder title duplication'
            );
        } 
		else{  
			
			$rem_date= clean_and_escape($this->input->post('rem_date'));  
			$rem_time= clean_and_escape($this->input->post('rem_time'));  
			$reminder_date =  date('Y-m-d H:i',strtotime($rem_date.' '.$rem_time)); 
			 
			$data=array();			
        	$data['title']         = $title;
        	$data['description']   = clean_and_escape($this->input->post('description'));
        	$data['reminder_date'] = $reminder_date;
			$data['added_by_id']   = $this->session->userdata('super_user_id');
			$data['added_by_name'] = $this->session->userdata('super_name');
			$data['added_by_role'] = $this->session->userdata('super_role_id');
    		$data['created_at']    = date("Y-m-d H:i:s");
        	$insert=$this->db->insert('reminder', $data);
        	
			if($insert){			
        	  $this->session->set_flashdata('flash_message', get_phrase( 'reminder_added_successfully'));
			}
			else{
			   $resultpost = array(
				"status" => 200,
				"message" => get_phrase('issue_while_adding_reminder'),
			   );  
		 }      
       }
       return simple_json_output($resultpost); 
    }
	
	public function edit_reminder($id){                 
           $resultpost = array(
            "status" => 200,
            "message" => get_phrase('reminder_updated_successfully'),
            "url" => base_url('common/reminder'),
           );  
        
        $this->form_validation->set_rules('title', 'Title', 'trim|required|max_length[30]');
        $this->form_validation->set_rules('rem_date', 'Reminder Date', 'trim|required');
        $this->form_validation->set_rules('rem_time', 'Reminder Time', 'trim|required');
		
		
		$title = clean_and_escape($this->input->post('title'));
		$added_by_id=$this->session->userdata('super_user_id');
        $check_name = $this->common_model->check_dual_field_duplication('on_update','reminder','title',$title,'added_by_id',$added_by_id,$id);  
		
		if ($this->form_validation->run() == FALSE){
		     $errors = array(
                'title'    => form_error('title'),
                'rem_date' => form_error('rem_date'),
                'rem_time' => form_error('rem_time'),
			);
			
			$errorString = implode("\n", $errors);
			  
           $resultpost = array(
                "status" => 400,
                "message" => $errorString,
                "errors" => $errors,
            );  
		}	
		else if ($check_name == false) {
            $resultpost = array(
                "status" => 400,
                "message" => 'Reminder title duplication'
            );
        } 
		else{  
			
			$rem_date= clean_and_escape($this->input->post('rem_date'));  
			$rem_time= clean_and_escape($this->input->post('rem_time'));  
			$reminder_date =  date('Y-m-d H:i',strtotime($rem_date.' '.$rem_time)); 
			 
			$data=array();			
        	$data['title']         = $title;
        	$data['description']   = clean_and_escape($this->input->post('description'));
        	$data['reminder_date'] = $reminder_date;
			$data['added_by_id']   = $this->session->userdata('super_user_id');
			$data['added_by_name'] = $this->session->userdata('super_name');
			$data['added_by_role'] = $this->session->userdata('super_role_id');
    		$data['updated_at']    = date("Y-m-d H:i:s");
			$this->db->where('id', $id);
            $update=$this->db->update('reminder', $data);	
        	
			if($update){			
        	  $this->session->set_flashdata('flash_message', get_phrase( 'reminder_update_successfully'));
			}
			else{
			  $resultpost = array(
				"status" => 200,
				"message" => get_phrase('issue_while_updating_reminder'),
			 );  
		 }      
       }
       return simple_json_output($resultpost); 
    }
	
	
	public function delete_reminder($id)  {  	  
		$added_by_id = $this->session->userdata('super_user_id');
		$this->db->where('added_by_id', $added_by_id);
		$this->db->where('id', $id);
		$delete=$this->db->delete('reminder');      
		if($delete){
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('reminder_deleted_successfully'),
				"url" => base_url('common/reminder'),
		   ); 
		}
		else{	
			$resultpost = array(
			    "status" => 400,
				"message" => get_phrase('there_is_some_problem_while_deleting'),
		   );			
		}		
        return simple_json_output($resultpost);       
    }
	
	public function delete_reminder_done($id)  {  	  
		$added_by_id = $this->session->userdata('super_user_id');
		$this->db->where('added_by_id', $added_by_id);
		$this->db->where('id', $id);
		$delete=$this->db->delete('reminder_done');      
		if($delete){
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('reminder_deleted_successfully'),
				"url" => base_url('common/reminder-done'),
		   ); 
		}
		else{	
			$resultpost = array(
			    "status" => 400,
				"message" => get_phrase('there_is_some_problem_while_deleting'),
		   );			
		}		
        return simple_json_output($resultpost);       
    }
	
	 
	public function get_ajax_reminder_list(){ 
		$data= array();
		$added_by_id = $this->session->userdata('super_user_id');
		$curr_date=date("Y-m-d H:i:s");
        $query = $this->db->query("SELECT id,title,description,reminder_date FROM reminder WHERE added_by_id='$added_by_id' AND status='pending' AND  reminder_date<='$curr_date'  ORDER BY reminder_date ASC LIMIT 10");
		//echo $this->db->last_query();exit();
        if (!empty($query)) {        
           foreach ($query->result_array() as $item) {
			  $reminder_date = date("d M, h:i A", strtotime($item['reminder_date']));
			 $data[] = array(
                    "id"            => $item['id'],
                    "icon"          => 'warning',
                    "title"   		=> $item['title'],
                    "subtitle"   	=> $item['description'],
                    "reminder_date" => $reminder_date,
                    "actions"       => array("Done")
                );
            }
         }
        header('Content-Type: application/json');
        echo json_encode($data);
    }
	
	public function get_updated_reminder_list(){  
		$added_by_id = $this->session->userdata('super_user_id');
		$curr_date=date("Y-m-d H:i:s");
        $query = $this->db->query("SELECT id,title,description FROM reminder WHERE added_by_id='$added_by_id' AND status='pending' AND  reminder_date<='$curr_date' ORDER BY reminder_date ASC LIMIT 10");
     // echo $this->db->last_query();exit();
        if (!empty($query)) {        
           foreach ($query->result_array() as $item) {
			 $data[] = array(
                    "id"            =>$item['id'],
                    "icon"          => 'warning',
                    "title"   		=> $item['title'],
                    "subtitle"   	=> $item['description'],
                    "actions"       => array("Done")
                );
            }
         }
        return $data;
    }
	
	public function action_reminder_done(){  
		$id= clean_and_escape($this->input->post('id'));  
		$added_by_id = $this->session->userdata('super_user_id');

		$curr_date=date("Y-m-d H:i:s");
        $query = $this->db->query("SELECT * FROM reminder WHERE added_by_id='$added_by_id' AND status='pending' AND  reminder_date<='$curr_date' AND id='$id' LIMIT 1");
		
		if($query->num_rows()>0){
			$row=$query->row();
			$data=array();			
        	$data['id']            = $row->id;
        	$data['title']         = $row->title;
        	$data['description']   = $row->description;
        	$data['status']  	   =  'done';
    		$data['done_date']     = date("Y-m-d H:i:s");
        	$data['reminder_date'] = $row->reminder_date;
			$data['added_by_id']   = $row->added_by_id;
			$data['added_by_name'] = $row->added_by_name;
			$data['added_by_role'] = $row->added_by_role;
    		$data['created_at']    = $row->created_at;
        	$insert=$this->db->insert('reminder_done', $data);
        	//$insert=1;
        	if($insert){
			    $rem_id=$row->id;	
			    $delete = $this->db->query("DELETE FROM `reminder` WHERE id='$rem_id'"); 
				$updated_list=$this->get_updated_reminder_list();
				$resultpost = array(
				"status" => 200,
				"message" => 'Reminder done successfully',
				"data" => $updated_list,
			   );
			}
			else{
				$resultpost = array(
				"status" => 400,
				"message" => 'Alert, there some problem while adding!'
			   );
			}
		}
		else{  
		  $resultpost = array(
            "status" => 400,
            "message" => 'No Reminder Found!'
          );
			
		}
       return simple_json_output($resultpost); 
    }
	
	
     
    	
 }
