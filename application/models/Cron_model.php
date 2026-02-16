<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Cron_model extends CI_Model
{
    
    function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        
    }
    
    public function hold_order_delete(){
       date_default_timezone_set('Asia/Kolkata'); 
       $date = date("Y-m-d H:i:s");
        
       $resultdata = array();
       $query = $this->db->query("SELECT id,added_by_id,added_date FROM orders WHERE order_type='hold_order' order by id asc LIMIT 10");
       if (!empty($query)) {
          foreach($query->result_array() as $item){
            $id= $item['id'];
            $check_in_date=date("Y-m-d H:i:s", strtotime($item['added_date']));
    
            $now = new DateTime();
            $future_date = new DateTime($check_in_date);
            $interval = $future_date->diff($now);
            $total_days=$interval->days;
            if($total_days>21){
                
            $order_details = $this->crud_model->get_orders_details_by_id($id);
            $delete_remark = 'Auto Delete After 21 Days';
        
            $manager_email=$this->auth_model->get_manager_email_by_cid($order_details['added_by_id']);
            $coordinator_email=$this->auth_model->get_user_email($order_details['added_by_id']);
            
            $user_email='delete@raplgroup.in,'.$manager_email.','.$coordinator_email;
            $email_subject='Account Hold Order Deletion | #'.$id;
            $message=$this->auth_model->manager_delete_mail($order_details,$delete_remark);
            
			if($this->auth_model->sent_mail($message, $user_email, $email_subject)){
            	$del_orders = $this->db->query("INSERT INTO del_orders SELECT * FROM orders WHERE id = '$id'"); 
            	$del_order_products = $this->db->query("INSERT INTO del_order_items SELECT * FROM order_items WHERE parent_id = '$id'"); 
                 
                $this->db->where('parent_id', $id);
                $this->db->delete('order_items');
                            
                $this->db->where('id', $id);
                $this->db->delete('orders');
                
                $this->db->where('order_id', $id);
                $this->db->delete('order_accounts');            
              
				$function_name='HoldDelete';
				$json_request='';
				$json_data=json_encode($order_details);
			  
				$this->add_cronjob_track($id, $function_name, $json_request, $json_data);	
           }			  
         }
       }
      }
    }

    public function auto_staff_attendance(){
        date_default_timezone_set('Asia/Kolkata');
        $curr_date = date("Y-m-d");
        $date = date("Y-m-d H:i:s");
        if (date('H') >= 22) {
         $check=$this->db->query("SELECT id,user_id,name,phone,check_in_date,role_id FROM staff_attendance WHERE DATE(date) ='$curr_date' AND check_out_date IS NULL LIMIT 100");
         if($check->num_rows()>0){
          foreach($check->result() as $item){
            $id=$item->id;
            $user_id=$item->user_id;
            $role_id=$item->role_id;
            $check_in_date=date("Y-m-d H:i:s", strtotime($item->check_in_date));
    
            $now = new DateTime();
            $future_date = new DateTime($check_in_date);
            $interval = $future_date->diff($now);
            $total_hrs=$interval->h;
      
           if($role_id=='2' || $role_id=='4' || $role_id=='11'){
             $calls=$this->db->query("SELECT id FROM doctor_followup WHERE added_by_id = '$user_id' AND DATE(added_date) ='$curr_date'")->num_rows();
           } 
           elseif($role_id=='10'){
             $calls=$this->db->query("SELECT id FROM patient_followup WHERE added_by_id = '$user_id' AND DATE(added_date) ='$curr_date'")->num_rows();
           }
           else{
              $calls=0; 
           }
           
           
           if($total_hrs>=6){
            //21-12-22  
            if($role_id=='4'){ 
             $target=20;
            }
            else{
             $target=40;  
            }
            $staff_halfday=0;
           }
           else{
             if($role_id=='4'){ 
              $target=10;
             }
             else{
              $target=20;  
             } 
             $staff_halfday=1;   
           }
            
            $chk_backlogs=$target-$calls;
            
            if($chk_backlogs < 0){
             $backlogs=$chk_backlogs;
            }
            else{
             $backlogs=$chk_backlogs; 
            }
            
            $data_attn = array();
            $data_attn = array(
    			'total_calls' => $calls,
    			'target_calls' => $target,
    			'check_out_date' => $date,
    			'backlogs' => $backlogs,
    			'total_hrs' =>$total_hrs,
    			'auto_in' => 1,
    			'staff_halfday' => $staff_halfday,
            );
			

            
            $this->db->where('id', $id);
            $insert=$this->db->update('staff_attendance', $data_attn);
            
            if($insert){
                $function_name='AutoAttendance';
                $json_request=json_encode($item);
                $json_data=json_encode($data_attn);
              
				$this->add_cronjob_track($id, $function_name, $json_request, $json_data);	 
            }
         }
        }
      }
    }


    public function cashfree_patient_payment_checker(){
        date_default_timezone_set('Asia/Kolkata');
        $query        = $this->db->query("SELECT id,mobile_no,patient_name,order_unique_id,order_token,patient_order_date FROM patient_orders WHERE order_type='online' AND payment_status='pending' AND payment_method='cash_free' AND order_token IS NOT NULL AND is_cron=0 order by id asc LIMIT 5");
        $count_school = $query->num_rows();
        $i            = 1;
        foreach ($query->result_array() as $row) {
            $id          = $row['id'];
            $order_id    = $row['order_unique_id'];
            $order_token = $row['order_token'];
            $order_date  = $row['patient_order_date'];
            $curr_date   = date("Y-m-d H:i:s");
            
            $order_date_ = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($order_date)));
            if ($curr_date > $order_date_) {
                $payment_url=PAYMENT_URL.'/'.$order_id;
                  
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $payment_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                		'x-client-id: '.CLIENT_ID,
                		'x-client-secret: '.CLIENT_SECRET,
                        'x-api-version: 2021-05-21'
                    )
                ));
                $result = curl_exec($curl);
                $err    = curl_error($curl);
                curl_close($curl);
                $response = json_decode($result, TRUE);
                $flag     = 0;  
                if (array_key_exists("cf_order_id", $response)) {
                    if (array_key_exists("order_status", $response)) {
                        $p_order_token = $response['order_token'];
                        $order_id      = $response['order_id'];
                        $order_status  = $response['order_status'];
                        $order_amount  = $response['order_amount'];
                
                        if ($p_order_token == $order_token) {
                            
                            if ($order_status == 'PAID') {
                                $get_order = $this->common_model->check_order($order_id, $order_amount, $p_order_token);
                                if ($get_order == true) {
                                    $flag        = 1;
                                    $data_update = array();
                                    $data_update = array(
                                        'payment_status' => 'success'
                                    );
                                    $this->common_model->updatePayment($data_update,$order_id,$p_order_token);
                                    $user_phone=$row['mobile_no'];
                                    $user_name=$row['patient_name'];
                                    $message_user = 'Confirmed: Hello '.$user_name.', your order #'.$order_id.' with Rajasthan Aushdhalaya Pvt. Ltd, has been successfully completed. Tracking details will be shared shortly. Thank you for choosing us for your well being.';
                                    $this->auth_model->send_order_confirmation($message_user,$user_phone);
                                } else {
                                    $flag = 0;
                                }
                            } else {
                                $data_cron=array();
                                $data_cron['is_cron'] = 1;
                                $this->db->where('id', $id);
                                $this->db->update('patient_orders ', $data_cron);
                                $flag = 0;
                                
                                $function_name='AutoPay';
                                $json_request=json_encode($response);
                                $json_data=json_encode($row);
                				$this->add_cronjob_track($id, $function_name, $json_request, $json_data);	 
                            }
                        } else {
                            $data_cron=array();
                            $data_cron['is_cron'] = 1;
                            $this->db->where('id', $id);
                            $this->db->update('patient_orders ', $data_cron);
                            $flag = 0;
                            
                            $function_name='AutoPay';
                            $json_request=json_encode($response);
                            $json_data=json_encode($row);
            				$this->add_cronjob_track($id, $function_name, $json_request, $json_data);	
                        }
                    } else {
                        $data_cron=array();
                        $data_cron['is_cron'] = 1;
                        $this->db->where('id', $id);
                        $this->db->update('patient_orders ', $data_cron);
                        $flag = 0;
                
                        $function_name='AutoPay';
                        $json_request=json_encode($response);
                        $json_data=json_encode($row);
        				$this->add_cronjob_track($id, $function_name, $json_request, $json_data);	
                    }
                } else {
                    $data_cron=array();
                    $data_cron['is_cron'] = 1;
                    $this->db->where('id', $id);
                    $this->db->update('patient_orders ', $data_cron);
                    $flag = 0;
                    
                    $function_name='AutoPay';
                    $json_request=json_encode($response);
                    $json_data=json_encode($row);
    				$this->add_cronjob_track($id, $function_name, $json_request, $json_data);	
                }
            }
        }
    }
    
        
    public function add_cronjob_track($order_id, $function_name,$json_request,$json_data){
        $added_date =  date('Y-m-d H:i:s');
        $data['order_id']        = $order_id;
        $data['function_name']   = $function_name;
        $data['json_request']    = $json_request;
        $data['json_data']       = $json_data;
        $data['created_date']    = $added_date;
        $this->db->insert('cronjob_track', $data);  
    }
    
    public function manual_camp($camp_id){
       date_default_timezone_set('Asia/Kolkata'); 
       $date = date("Y-m-d H:i:s");
        
	   $camp=$this->crud_model->get_camp_order_by_id($camp_id)->row_array();
	   $user_id=$camp['user_id'];
	   
       $resultdata = array();
       $query = $this->db->query("SELECT id, name, products, phone, age, is_check FROM manual_camp WHERE is_check=0 ORDER BY id asc");
       if (!empty($query)) {
          foreach($query->result_array() as $item){
            $id = $item['id'];
            $name = $item['name'];
            $products = explode(",",$item['products']);
            $phone = $item['phone'];
            $age = $item['age'];
		

     		$data_products = array();
			if(count($products)>0){
			foreach($products as $product){
              $data_products[] = array(
    			'id' => 0,
    			'product_id' => 0,
    			'product_name' => trim($product),
    			'quantity' => 1,
              );
			 }
			}
			
		  $json_products=json_encode($data_products);
	         
		  $camp_patients = array();
		  $camp_patients = array(
			'user_id' => $user_id,
			'camp_id' => $camp_id,
			'name' => $name,
			'phone' => $phone,
			'age' => $age,
			'products' => $json_products,
			'total_qty' => (is_array($data_products) ? count($data_products):0),              
			'created_at' => $date,
          );	
	     $this->db->insert('camp_patients', $camp_patients);		 
		 $this->db->query("UPDATE `manual_camp` SET `is_check`='1' WHERE id='$id'");			
       }
	   
	   $check_count = $this->db->query("SELECT id FROM check_in_out WHERE camp_id='$camp_id'")->num_rows(); 
	   if($check_count=='0'){
	    $check_in_stamp=strtotime($camp['camp_date'].' 10:00 AM');
	    $camp_checkout = array();
	    $camp_checkout = array(
			'user_id' => $user_id,
			'check_type' => "Medical Camp",
			'camp_id' => $camp_id,
			'latitude' => 0,
			'longitude' => 0,
			'date' => $camp['camp_date'],
			'check_in_time' => '10:00 AM',
			'check_out_time' => '08:00 PM',
			'check_in_stamp' => $check_in_stamp,
	     );	
	    $this->db->insert('check_in_out', $camp_checkout);   
	   }
      }
    }
    
    
    public function update_doctor_sale(){     	   
       $resultdata = array();
       $query = $this->db->query("SELECT id FROM `doctor` WHERE `is_pure`=1 AND (`sale_value`<=50 OR `sale_value` IS NULL) AND is_check='0' ORDER BY id ASC LIMIT 500");
       if (!empty($query)) {
          foreach($query->result_array() as $item){
            $doctor_id=$item['id'];
		    $check_sale  = $this->db->query("SELECT id,price_total,order_date FROM orders WHERE doctor_id='$doctor_id' AND approval_type='4' AND approval_status='1' AND price_total>50 ORDER BY DATE(order_date) DESC LIMIT 1"); 
            if($check_sale->num_rows()>0){
                 $check1=$check_sale->row_array();
                 $data_sale = array();
                 $data_sale = array(
        			'is_check'     => 1,
        			'sale_value'   => $check1['price_total'],
        			'last_date'    => date("Y-m-d", strtotime($check1['order_date'])),
                 );            
        		 $this->db->where('id', $doctor_id);
        	 	 $this->db->update('doctor', $data_sale);
            }
            else{
                 $data_sale = array();
                 $data_sale = array(
        			'is_check'     => 1,
                 );            
        		 $this->db->where('id', $doctor_id);
        	 	 $this->db->update('doctor', $data_sale);
            }
		 }
      }
    }
	
	
	
   public function update_einvoice_token() {
    date_default_timezone_set('Asia/Kolkata');
    $curr_date = date('Y-m-d');
    $update_date = date('Y-m-d H:i:s');
        
    $token=$this->db->get_where('api_authentication', array('id' => '1'))->row_array();
    $token_date_ = date('Y-m-d H:i:s',strtotime($token['last_updated']));
	$token_date = date('Y-m-d H:i:s', strtotime('+1 hours', strtotime($token_date_)));
	
		
    if($token_date < $update_date){
	   $ASPID=ASPID;  
       $ASPPASS=ASPPASS;
       $MUM_GSTIN=MUM_GSTIN;
       $RAJ_GSTIN=RAJ_GSTIN;
       $EINV_USER=EINV_USER;
       $EINV_PASS=EINV_PASS;
	  		
        $url='https://gstsandbox.charteredinfo.com/eivital/dec/v1.04/auth?aspid='.$ASPID.'&password='.$ASPPASS.'&Gstin='.$MUM_GSTIN.'&User_name='.$EINV_USER.'&eInvPwd='.$EINV_PASS.'';
     
      
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "$url",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 60,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
          ),
        ));
        
        $result = curl_exec($curl);
		//echo 'sdfs'.$result;
        $api_data = json_decode($result, TRUE);
	
        if($api_data['Status']=='1'){
            $AuthToken=$api_data['Data']['AuthToken'];
            $TokenExpiry=$api_data['Data']['TokenExpiry'];
			$data_update=array();
            $data_update['AuthToken'] = $AuthToken;
            $data_update['TokenExpiry'] = $TokenExpiry;
            $data_update['last_updated'] = $update_date;
            $this->db->where('id', '1');
            $this->db->update('api_authentication', $data_update);  
        }
      }
    }
	
	public function add_inventory(){
		$query=$this->db->query("SELECT id,name,is_variation,item_code FROM raw_products WHERE is_deleted ='0' order by name asc");
        if($query->num_rows()>0){
			foreach($query->result_array() as $item){
				$product_id =  $item['id'];
				$product_name =  $item['name'];
				$item_code =  $item['item_code'];
				$product_variation =  $item['is_variation'];
				$rcv_quantity = 1; 
				if($product_variation == 1){
					$query1 =$this->db->query("SELECT sku_code FROM product_variation WHERE product_id ='$product_id' order by id asc");
					if($query1->num_rows()>0){
						foreach($query1->result_array() as $item1){
							$item_code_1 =  ($item1['sku_code']!='' && $item1['sku_code']!='') ? $item1['sku_code'] : '';
							if($item_code_1!=''){
								$check = $this->db->query("SELECT id,quantity FROM inventory where product_id='$product_id' and warehouse_id='1' and item_code='$item_code_1'");
								if($check->num_rows() > 0){
									$check_row = $check->row_array();
									$check_quantity = $check_row['quantity'];
									$check_id = $check_row['id'];
									
									$final_quantity = intval($check_quantity) + $rcv_quantity;
									
									$prod = array();
									$prod['quantity'] = $final_quantity;
									$this->db->where('id',$check_id);
									$this->db->update('inventory',$prod);
									
									$pro_de['order_id'] = null;
									$pro_de['parent_id'] = $check_id;
									$pro_de['warehouse_name'] = 'MALAD - WAREHOUSE';
									$pro_de['warehouse_id'] = 1;
									$pro_de['product_id'] = $product_id;
									$pro_de['product_name'] = $product_name;
									$pro_de['item_code'] = $item_code_1;
									$pro_de['quantity']    = $rcv_quantity;
									$pro_de['status'] 	   = 'in';
									$pro_de['received_date'] =  date("Y-m-d H:i:s");
									$pro_de['batch_no'] = '';
									$pro_de['expiry_date'] = '';
									$pro_de['invoice_no'] = '';
									$pro_de['received_amount'] = 0;
									$pro_de['added_date']  = date("Y-m-d H:i:s");
									$pro_de['added_by_id']   = '4';
									$pro_de['added_by_name'] = 'Flash  Point';
									$this->db->insert('inventory_history',$pro_de);
								}else{
									$prod = array();
									$prod['warehouse_name'] = 'MALAD - WAREHOUSE';
									$prod['warehouse_id'] = 1;
									$prod['product_id'] = $product_id;
									$prod['product_name'] = $product_name;
									$prod['item_code'] = $item_code_1;
									$prod['quantity'] = $rcv_quantity;
									$prod['batch_no'] = '';
									$prod['expiry_date'] = '';
									$this->db->insert('inventory',$prod);
									$check_id = $this->db->insert_id();;
									 
									$pro_de['order_id'] = null;
									$pro_de['parent_id'] = $check_id;
									$pro_de['warehouse_name'] = 'MALAD - WAREHOUSE';
									$pro_de['warehouse_id'] = 1;
									$pro_de['product_id'] = $product_id;
									$pro_de['product_name'] = $product_name;
									$pro_de['item_code'] = $item_code_1;
									$pro_de['quantity']    = $rcv_quantity;
									$pro_de['status'] 	   = 'in';
									$pro_de['received_date'] =  date("Y-m-d H:i:s");
									$pro_de['batch_no'] = '';
									$pro_de['expiry_date'] = '';
									$pro_de['invoice_no'] = '';
									$pro_de['received_amount'] = 0;
									$pro_de['added_date']  = date("Y-m-d H:i:s");
									$pro_de['added_by_id']   = '4';
									$pro_de['added_by_name'] = 'Flash  Point';
									$this->db->insert('inventory_history',$pro_de);
								} 
							}
						}
					}
				}else{
					if($item_code!=''){
						$check = $this->db->query("SELECT id,quantity FROM inventory where product_id='$product_id' and warehouse_id='1' and item_code='$item_code'");
						if($check->num_rows() > 0){
							$check_row = $check->row_array();
							$check_quantity = $check_row['quantity'];
							$check_id = $check_row['id'];
							
							$final_quantity = intval($check_quantity) + $rcv_quantity;
							
							$prod = array();
							$prod['quantity'] = $final_quantity;
							$this->db->where('id',$check_id);
							$this->db->update('inventory',$prod);
							
							$pro_de['order_id'] = null;
							$pro_de['parent_id'] = $check_id;
							$pro_de['warehouse_name'] = 'MALAD - WAREHOUSE';
							$pro_de['warehouse_id'] = 1;
							$pro_de['product_id'] = $product_id;
							$pro_de['product_name'] = $product_name;
							$pro_de['item_code'] = $item_code;
							$pro_de['quantity']    = $rcv_quantity;
							$pro_de['status'] 	   = 'in';
							$pro_de['received_date'] =  date("Y-m-d H:i:s");
							$pro_de['batch_no'] = '';
							$pro_de['expiry_date'] = '';
							$pro_de['invoice_no'] = '';
							$pro_de['received_amount'] = 0;
							$pro_de['added_date']  = date("Y-m-d H:i:s");
							$pro_de['added_by_id']   = '4';
							$pro_de['added_by_name'] = 'Flash  Point';
							$this->db->insert('inventory_history',$pro_de);
						}else{
							$prod = array();
							$prod['warehouse_name'] = 'MALAD - WAREHOUSE';
							$prod['warehouse_id'] = 1;
							$prod['product_id'] = $product_id;
							$prod['product_name'] = $product_name;
							$prod['item_code'] = $item_code;
							$prod['quantity'] = $rcv_quantity;
							$prod['batch_no'] = '';
							$prod['expiry_date'] = '';
							$this->db->insert('inventory',$prod);
							$check_id = $this->db->insert_id();;
							 
							$pro_de['order_id'] = null;
							$pro_de['parent_id'] = $check_id;
							$pro_de['warehouse_name'] = 'MALAD - WAREHOUSE';
							$pro_de['warehouse_id'] = 1;
							$pro_de['product_id'] = $product_id;
							$pro_de['product_name'] = $product_name;
							$pro_de['item_code'] = $item_code;
							$pro_de['quantity']    = $rcv_quantity;
							$pro_de['status'] 	   = 'in';
							$pro_de['received_date'] =  date("Y-m-d H:i:s");
							$pro_de['batch_no'] = '';
							$pro_de['expiry_date'] = '';
							$pro_de['invoice_no'] = '';
							$pro_de['received_amount'] = 0;
							$pro_de['added_date']  = date("Y-m-d H:i:s");
							$pro_de['added_by_id']   = '4';
							$pro_de['added_by_name'] = 'Flash  Point';
							$this->db->insert('inventory_history',$pro_de);
						} 
					}
				}
			}
		}
		 
	}
	
	
	public function update_inventory(){
		$query=$this->db->query("SELECT id,name,code,qty FROM old_stock WHERE is_check ='0' order by id asc");
        if($query->num_rows()>0){
			foreach($query->result_array() as $item){
				$id =  $item['id'];
				$product_name =  $item['name'];
				$code =  $item['code'];
				$qty =  $item['qty'];
				$check = $this->db->query("SELECT id FROM inventory where warehouse_id='1' and item_code='$code'");
				if($check->num_rows() > 0){
					$check_row = $check->row_array();
					$check_id = $check_row['id'];
					
					$final_quantity = intval($qty);
					
					$prod = array();
					$prod['quantity'] = $final_quantity;
					$this->db->where('id',$check_id);
					$this->db->update('inventory',$prod);
					
					$this->db->where('parent_id',$check_id);
					$this->db->update('inventory_history',$prod);
					
					$x_data = array();
					$x_data['is_check'] = '1';
					$this->db->where('id',$id);
					$this->db->update('old_stock',$x_data);
				}
			}
		}
		 
	}

	
}