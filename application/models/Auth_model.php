<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model{

    public function send_wati_sms($sender_mobile,$wati_array){
	    $sender_mobile = '+91'.$sender_mobile;		
		$payload=json_encode($wati_array);
		$curl = curl_init();
	
		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://live-server-12167.wati.io/api/v1/sendTemplateMessage?whatsappNumber='.$sender_mobile,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>$payload,
		CURLOPT_HTTPHEADER => array(
		'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI3MzY5NTgyZS1kODY5LTQzMTMtYjMzZC0yZmYzZmQ3OWVhMTQiLCJ1bmlxdWVfbmFtZSI6ImRpZ2l0YWwucmFwbEBnbWFpbC5jb20iLCJuYW1laWQiOiJkaWdpdGFsLnJhcGxAZ21haWwuY29tIiwiZW1haWwiOiJkaWdpdGFsLnJhcGxAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMTEvMTUvMjAyMiAxMTozMzozOSIsImRiX25hbWUiOiIxMjE2NyIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkFETUlOSVNUUkFUT1IiLCJleHAiOjI1MzQwMjMwMDgwMCwiaXNzIjoiQ2xhcmVfQUkiLCJhdWQiOiJDbGFyZV9BSSJ9.ztshGNdgKI5LyZleqs0TRGwiQiCxMD_z5LVhAgGxV3I',
		'Content-Type: application/json'
		),
		));

		$response = curl_exec($curl);
		 curl_close($curl);
    	//	echo $response;	  
        return true;
    }


   public function send_order_confirmation($message_user, $sender_mobile){
        $username = 'rajasthanT';
        $password = '9833666720';
        
        $senderid = 'RAPLGP';
        $template = '1207162937500186371';
        $type     = '1';
        $product  = '1';
        $number   = '91'.$sender_mobile;
        $message  = urlencode($message_user);

        $credentials = 'username=' . $username . '&password=' . $password;

        $data = '&sender=' . $senderid . '&mobile=' . $number . '&type=' . $type . '&product=' . $product . '&template=' . $template . '&message=' . $message;
        
        $url = 'http://makemysms.in/api/sendsms.php?' . $credentials . $data;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return true;
    }
    
     public function send_sms($message_user,$template_id, $sender_mobile){
        $username = 'rajasthanT';
        $password = '9833666720';
        
        $senderid = 'RAPLGP';
        $template = $template_id;
        $type     = '1';
        $product  = '1';
        $number   = '91'.$sender_mobile;
        $message  = urlencode($message_user);

        $credentials = 'username=' . $username . '&password=' . $password;

        $data = '&sender=' . $senderid . '&mobile=' . $number . '&type=' . $type . '&product=' . $product . '&template=' . $template . '&message=' . $message;
        
        $url = 'http://makemysms.in/api/sendsms.php?' . $credentials . $data;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $response;
        return true;
    }
    
    public function sent_mail($user_msg, $user_email, $email_subject){
        $this->load->library('email');
        $config['protocol']     = 'smtp';
        $config['smtp_host']    = 'zimbra.xmission.com';
        $config['smtp_port']    = '25';
        $config['smtp_timeout'] = '7';
        $config['smtp_user']    = 'noreply@raplgroup.in';
        $config['smtp_pass']    = 'Webwork@mailTest';
        $config['charset']      = 'utf-8';
        $config['newline']      = "\r\n";
        $config['mailtype']     = 'html';
        $config['validation']   = TRUE;
        $this->email->initialize($config);
        
        $this->email->to($user_email);
        $this->email->from('noreply@raplgroup.in', 'Rajasthan Aushadhalaya');
        $this->email->subject($email_subject);
        
        //Email content
        $message = $user_msg;
        
        $this->email->message($message);
        $this->email->send();
        return true;
      // echo $this->email->print_debugger();
    }
    
    public function manager_delete_mail($data,$delete_remark){
        
        
       $message='<style>
        table {
        caption-side: bottom;
        border-collapse: collapse;
        }
        table>tbody>tr>td {
        padding: 4px 8px;
        font-weight: 500;
        }
        table > th {
        background-color: #f1f5fa;
        }
        table > tr > td {
        border: 1px solid #e9e9e9;
        border-bottom-width: 1px;
        }
        </style>
        
        <h4 style="margin-bottom:5px;"><b>Doctor Details</b></h4>
        
        <label>Order Id: '.$data['id'].'</label> <br>
        <label>Source: '.get_phrase($data['source']).'</label> <br>
        <label>Doctor Type: '.get_phrase($data['doctor_type']).'</label> <br>
        <label>Doctor Name: '.$data['doctor_name'].'</label>  <br>
        <label>MR Name:'.$data['mr_name'].' </label>  <br>
        <label>Mobile No.: '.$data['mobile_no'].'</label>  <br>
        <label>Address:  '.$data['address'].' </label> 
        <label>State: '.$data['state_name'].'</label>  <br>
        <label>City: '.$data['city_name'].'</label>    <br>
        <label>Pincode: '.$data['pincode'].'</label>    <br>
        <label>Co-ordinator: '.$data['added_by_name'].'</label>   <br>
        <label>Price Type: '.get_phrase($data['price_type']).'</label>   <br>
        <label>Added Date: '.$data['added_date'].'</label>   <br>
        <label><b>Delete Remark</b>: '.$delete_remark.'</label>    <br>
        <label><b>Deleted By</b>: '.$this->session->userdata('super_name').'</label>   
        <br>
        <br>
        <div class="col-md-12">
        <table class="table table-bordered" width="100%" border="1" style="caption-side: bottom;border-collapse: collapse;text-align: center;">
          <thead>
        	<tr>
        		<th>#</th>
        		<th>Product Id</th>
        		<th>Product Name</th>
        		<th>Quantity</th>
        		<th>Free Quantity</th>
        		<th>Price</th>
        		<th>Total</th>
        	</tr>
          </thead>
          <tbody>';
          
       
        foreach($data['order_items'] as $key=> $list): 
         $message .='<tr>
    		 <td>'.($key+1).'</td>
    		 <td>'.$list['product_id'].'</td>
    		 <td>'.$list['product_name'].'</td>
    		 <td>'.$list['quantity'].'</td>
    		 <td>'.$list['free_quantity'].'</td>
    		 <td>INR '.$list['price'].'</td>
    		 <td>INR '.$list['price_total'].'</td>
    	  </tr>';
         endforeach;	     
        	
         $message .=' <tr>
        		  <td style="text-align:right;" colspan="6"><b>Total</b></td>
        		  <td><b>INR '.$data['price_total'].'/-</b></td>
        	  </tr>
          </tbody>
        </table>  
        </div>';
        
      return $message;
    }
    
    
	//get user by id
	public function get_user($id){   
	    $this->db->select('id, slug, first_name, last_name, email, phone, token, password, avatar, created_at, updated_at, otp');
		$this->db->where('id', $id);
		$query = $this->db->get('users');
		return $query->row_array();
	}
	

	public function get_user_by_id($id){    
	   $query = $this->db->query("SELECT id, type, first_name, last_name, email, phone, date_added, state_name, city_name, role_id FROM sys_users WHERE id = '$id' LIMIT 1");   
	  return $query->row_array();
	}
	

	//get user by email
	public function get_user_by_email($email){
		$this->db->select('id');
		$this->db->where('email', $email);
		$this->db->where('is_active', '1');
		$query = $this->db->get('users');
		return $query->row();
	}


	//get user by mobile
	public function get_user_by_mobile($phone_number){ 
     	$this->db->select('id');
		$this->db->where('phone', $phone_number);
		$this->db->where('is_active', '1');
		$query = $this->db->get('users');
		return $query->row();
	}
	//check if email is unique
	public function is_unique_email($email, $user_id = 0){
		$user = $this->auth_model->get_user_by_email($email);

		//if id doesnt exists
		if ($user_id == 0) {
			if (empty($user)) {
				return true;
			} else {
				return false;
			}
		}

		if ($user_id != 0) {
			if (!empty($user) && $user->id != $user_id) {
				//email taken
				return false;
			} else {
				return true;
			}
		}
	}
	
	
	
	//check if email is unique
	public function is_unique_mobile($phone_number, $user_id = 0){
		$user = $this->auth_model->get_user_by_mobile($phone_number);

		//if id doesnt exists
		if ($user_id == 0) {
			if (empty($user)) {
				return true;
			} else {
				return false;
			}
		}

		if ($user_id != 0) {
			if (!empty($user) && $user->id != $user_id) {
				//email taken
				return false;
			} else {
				return true;
			}
		}
	}


	//get user by slug
	public function get_user_by_slug($slug)
	{
		$this->db->where('slug', $slug);
		$query = $this->db->get('users');
		return $query->row();
	}	
		//generate uniqe slug
	public function generate_uniqe_slug($username)
	{
		$slug = str_slug($username);
		if (!empty($this->get_user_by_slug($slug))) {
			$slug = str_slug($username . "-1");
			if (!empty($this->get_user_by_slug($slug))) {
				$slug = str_slug($username . "-2");
				if (!empty($this->get_user_by_slug($slug))) {
					$slug = str_slug($username . "-3");
					if (!empty($this->get_user_by_slug($slug))) {
						$slug = str_slug($username . "-" . uniqid());
					}
				}
			}
		}
		return $slug;
	}


     
     
    public function get_user_address($id) {
        $resultdata = array();
        $query = $this->db->query("SELECT name,address,state,city,pincode,landmark,lattitude,longitude,phone,email FROM `address` WHERE id='$id' ORDER BY id desc");
        if (!empty($query)) {
               $item=$query->row_array(); 
                $resultdata = array(
                    "name" => $item['name'],
                    "address" => $item['address'],
                    "state" => $this->auth_model->get_state_name($item['state']),
                    "city" => $this->auth_model->get_city_name($item['city']),
                    "pincode" => $item['pincode'],
                    "landmark" => $item['landmark'],
                    "lattitude" => $item['lattitude'],
                    "longitude" => $item['longitude'],
                    "contact_number" => $item['phone'],
                    "email" => $item['email'],
                );
            
        }
        return $resultdata;
    }
    
    public function address_list($id)
	{   
	    $this->db->select('a.*');
	    //$this->db->select('a.*,cities.name as city_name,states.name as state_name');
		//$this->db->join('cities', 'cities.id = a.city');
		//$this->db->join('states', 'states.id = a.state');
		$this->db->where('a.user_id', $id);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get('address as a');
		return $query->result_array();
	}
       
   public function get_state_name($id){
        $this->db->where('state_list.id', $id);
        $query = $this->db->get('state_list');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->state;
        }
        else{
         return '';
      }
    }
    
    public function get_city_name($id)
    {
        $this->db->where('cities.id', $id);
        $query = $this->db->get('cities');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->name;
        }
        else{
         return '';
        }
    }


     
     public function state_list()
    {
        $query = $this->db->query("SELECT * FROM state_list order by state");
        $count = $query->num_rows();
        $data  = array();
        foreach ($query->result_array() as $row) {
            $id   = $row['id'];
            $name = $row['state'];
            
            $data[] = array(
                "id" => $id,
                "name" => $name
            );
        }
    
        return $data;
    }
      
     
     public function city_list($state_id)
    {
        $query = $this->db->query("SELECT * FROM cities WHERE state_id='$state_id' order by name");
        $count = $query->num_rows();
        $data  = array();
        foreach ($query->result_array() as $row) {
            $id   = $row['id'];
            $name = $row['name'];
            
            $data[] = array(
                "id" => $id,
                "name" => $name
            );
        }
    
        return $data;
    }
  
	
   public function get_doctor_name($id) {
        $this->db->select('name');
        $this->db->where('id', $id);
        $query = $this->db->get('doctor');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->name;
        }
        else{
         return '';
        }
    }    
		
   public function get_mr_name($id) {
        $this->db->select('first_name,last_name');
        $this->db->where('id', $id);
        $query = $this->db->get('asm_users');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->first_name.' '.$sql->last_name;
        }
        else{
         return '';
        }
    }    
    
    public function get_asm_($id) {
        $this->db->select('first_name,last_name');
        $this->db->where('id', $id);
        $query = $this->db->get('asm_users');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->first_name.' '.$sql->last_name;
        }
        else{
         return '';
        }
    } 
   
    public function get_asm_users_name($id) {
        $this->db->select('first_name,last_name');
        $this->db->where('id', $id);
        $query = $this->db->get('asm_users');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->first_name.' '.$sql->last_name;
        }
        else{
         return '';
        }
    }
    
    public function get_bulk_asm_users_name($id) {
        $query = $this->db->query("SELECT CONCAT_WS(' ', first_name, last_name) AS name FROM asm_users WHERE FIND_IN_SET(id, '$id')");
         $data  = array();
        foreach ($query->result_array()  as $key => $row) {
            $name = $row['name'];
            $data[$key] = $name;
        }
       
        return $data;
    }
    
    public function get_asm_doctor_by_id($id){
        $this->db->where('id', $id);
        return $this->db->get('asm_doctors');
    }  
    
    public function get_user_name($id){
        $this->db->select('first_name,last_name');
        $this->db->where('id', $id);
        $query = $this->db->get('sys_users');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->first_name.' '.$sql->last_name;
        }
        else{
         return '';
        }
    }  
	
    public function get_doctor_state_id($id){
        $this->db->select('state_id');
        $this->db->where('id', $id);
        $query = $this->db->get('doctor');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->state_id;
        }
        else{
         return '';
        }
    }
    
    
    public function get_doctor_city_state_id($id)  {
        $query = $this->db->query("SELECT state_id,city_id,state_name,city_name FROM doctor WHERE id='$id' LIMIT 1");
        $count = $query->num_rows();
        $data  = array();
        if($count>0){
           $row=$query->row_array();
           $data = array(
                "id" => $id,
                "state_id"  => $row['state_id'],
                "city_id"   => $row['city_id'],
                "state_name"=> $row['state_name'],
                "city_name" => $row['city_name'],
           );
       }
       return $data;
    }
   
    
    function encrypt_decrypt($action, $string) {
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$secret_key = SECRET_KEY;
		$secret_iv = SECRET_IV;
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else if( $action == 'decrypt' ) {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
		return $output;
	}  
	
	public function get_doctor_phone($id) {
        $this->db->select('phone');
        $this->db->where('id', $id);
        $query = $this->db->get('doctor');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->phone;
        }
        else{
         return '';
        }
    }  	
    
	public function get_user_role($id) {
        $this->db->select('role_id');
        $this->db->where('id', $id);
        $query = $this->db->get('sys_users');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->role_id;
        }
        else{
         return '';
        }
    }  
    public function get_user_type(){   
	    $this->db->select('id, name');
		$query = $this->db->get('user_type');
		return $query->result_array();
	}
	
	public function get_user_email($id) {
        $this->db->select('email');
        $this->db->where('id', $id);
        $query = $this->db->get('sys_users');
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->email;
        }
        else{
         return '';
        }
    }  
	
	public function get_manager_email_by_cid($id) {
	    $query = $this->db->query("SELECT usr.email FROM `coordinator_mapping` AS cm INNER JOIN sys_users AS usr ON usr.id=cm.mngt_id WHERE usr.is_deleted='0' AND usr.status='1' AND cm.co_id='$id'");
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->email;
        }
        else{
         return '';
        }
    } 

	public function get_manager_name_by_cid($id) {
	    $query = $this->db->query("SELECT usr.first_name,usr.last_name FROM `coordinator_mapping` AS cm INNER JOIN sys_users AS usr ON usr.id=cm.mngt_id WHERE usr.is_deleted='0' AND usr.status='1' AND cm.co_id='$id'");
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->first_name.' '.$sql->last_name;
        }
        else{
         return '';
        }
    } 
    
    public function get_drleads_user_type(){   
          $query  = $this->db->query("SELECT id,name FROM `user_type` WHERE  FIND_IN_SET(id,'2,11') order by id asc");
		return $query->result_array();
	} 
    
    public function get_ptleads_user_type(){   
        $query  = $this->db->query("SELECT id,name FROM `user_type` WHERE  FIND_IN_SET(id,'10') order by id asc");
		return $query->result_array();
	} 
	
	public function get_manager_name_by_state($state_id) {
	    $query = $this->db->query("SELECT GROUP_CONCAT( DISTINCT CONCAT_WS(' ', usr.first_name, usr.last_name)) as name FROM `manager_state_mapping` AS cm INNER JOIN sys_users AS usr ON usr.id=cm.mngt_id WHERE usr.is_deleted='0' AND usr.status='1' AND cm.`state_id` = '$state_id'");
        if($query->num_rows()>0){
         $sql= $query->row();
         return $sql->name;
        }
        else{
         return '';
        }
    } 
    
}
