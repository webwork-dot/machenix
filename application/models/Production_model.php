<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_model extends CI_Model{
    
    function __construct() {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }
	
	public function add_products() {
		$this->db->trans_begin(); // Start transaction
  
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('products_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_products_name = $this->common_model->check_duplication('on_create', 'products', 'name', $name);
		} else {
			$check_products_name = true;
		}

		if ($check_products_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('products_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'products Name Duplication'
			);

			$this->db->trans_rollback(); // Rollback transaction
		} else {
			$data=array();		
			$data['name']           = $name;
			$data['unit']           = clean_and_escape($this->input->post('unit'));
			$data['dept']     	    = clean_and_escape($this->input->post('dept'));
			$data['std_batch_size'] = clean_and_escape($this->input->post('std_batch_size'));
			$data['std_batch_size_unit'] = clean_and_escape($this->input->post('std_batch_size_unit'));
			$data['shelf_life']      = clean_and_escape($this->input->post('shelf_life'));
			$data['code']      	     = clean_and_escape($this->input->post('code'));
			$data['per_yield']       = clean_and_escape($this->input->post('per_yield'));
			$data['pack_size']       = clean_and_escape($this->input->post('pack_size'));
			$data['status']          = clean_and_escape($this->input->post('status'));
			$data['created_at']      = date("Y-m-d H:i:s");

			if ($this->db->insert('products', $data)) {
				$parent_id = $this->db->insert_id();
				 $parent_id = $this->db->insert_id();  
				  
				  //ACTIVE INGREDIENTS
				 $product_id_arr   = $this->input->post('product_id');
				 $form_arr 		   = $this->input->post('form');
				 $ref_arr   	   = $this->input->post('ref');
				 $part_used_arr    = $this->input->post('part_used');
				 $label_claim_arr  = $this->input->post('label_claim');
				 $item_other_id    = $this->input->post('item_other_id');
					
					 
				 //delete removed products
				 $product_ids = implode(",", array_filter($item_other_id));
				 if (!empty($product_ids)) {
					$delete_unw = $this->db->query("DELETE FROM `products_details` WHERE parent_id='$parent_id' AND type='active_ingredients' AND id NOT IN ($product_ids)"); 
				 }
				 
				  for ($i = 0; $i < count($product_id_arr); $i++) {
					$product_id=$product_id_arr[$i];
					$form=$form_arr[$i];
					$ref=$ref_arr[$i];
					$part_used=$part_used_arr[$i];
					$label_claim=$label_claim_arr[$i];
					
					$product = $this->crud_model->get_raw_products_by_id($product_id)->row_array();
				   
					
					$data_ingredients = array();
					$data_ingredients = array(
						'parent_id'         => $parent_id,
						'type'      		=> 'active_ingredients',
						'product_id'      	=> $product_id,
						'product_name'      => $product['name'],
						'form'      		=> $product['form'],
						'ref'        	   	=> $ref,
						'part_used'        	=> $part_used,
						'label_claim'       => $label_claim,
						'created_at'        => date("Y-m-d H:i:s"),
					);
					if(!empty($item_other_id[$i])){
						$this->db->where('id', $item_other_id[$i]);
						$this->db->update('products_details', $data_ingredients);
					}
					else{
						$this->db->insert('products_details', $data_ingredients);  
					 } 
				}
				
				
				  //EXCIPIENT/BASE
				 $ex_product_id_arr   = $this->input->post('ex_product_id');
				 $ex_form_arr 		  = $this->input->post('ex_form');
				 $ex_ref_arr   	      = $this->input->post('ex_ref');
				 $ex_part_used_arr    = $this->input->post('ex_part_used');
				 $ex_label_claim_arr  = $this->input->post('ex_label_claim');
				 $ex_item_other_id    = $this->input->post('ex_item_other_id');
					
					 
				  //delete removed products
				  $ex_product_ids = implode(",", array_filter($ex_item_other_id));
				  if (!empty($ex_product_ids)) {
					$delete_unw = $this->db->query("DELETE FROM `products_details` WHERE parent_id='$parent_id' AND type='excipient_base' AND id NOT IN ($ex_product_ids)"); 
				  }
				 
				  $product_id=$form=$ref=$part_used=$label_claim="";
				  for($i = 0; $i < count($ex_product_id_arr); $i++) {
					$product_id=$ex_product_id_arr[$i];
					$form=$ex_form_arr[$i];
					$ref=$ex_ref_arr[$i];
					$part_used=$ex_part_used_arr[$i];
					$label_claim=$ex_label_claim_arr[$i];
					
					$product = $this->crud_model->get_raw_products_by_id($product_id)->row_array();
				   
					$data_excipient = array();
					$data_excipient = array(
						'parent_id'         => $parent_id,
						'type'      		=> 'excipient_base',
						'product_id'      	=> $product_id,
						'product_name'      => $product['name'],
						'form'      		=> $product['form'],
						'ref'        	   	=> $ref,
						'part_used'        	=> $part_used,
						'label_claim'       => $label_claim,
						'created_at'        => date("Y-m-d H:i:s"),
					);
					if(!empty($ex_item_other_id[$i])){
						$this->db->where('id', $ex_item_other_id[$i]);
						$this->db->update('products_details', $data_excipient);
					}
					else{
						$this->db->insert('products_details', $data_excipient);  
					 } 
				 }

				$this->session->set_flashdata('flash_message', get_phrase('products_added_successfully'));

				$this->db->trans_commit(); // Commit transaction
			} else {
				$this->db->trans_rollback(); // Rollback transaction
				$resultpost = array(
					"status" => 400,
					"message" => 'Failed to add product'
				);
			}
		}

		return simple_json_output($resultpost);
	}
    
    public function edit_products($id = "")   {
		$this->db->trans_begin(); // Start transaction
  
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('products_updated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_products_name = $this->common_model->check_duplication('on_update', 'products', 'name', $name, $id);
		} else {
			$check_products_name = true;
		}

		if ($check_products_name == false) {
			$resultpost = array(
				"status" => 400,
				"message" => 'products Name Duplication'
			);

			$this->db->trans_rollback(); // Rollback transaction
		} else {
			$data=array();		
			$data['name']           = $name;
			$data['unit']           = clean_and_escape($this->input->post('unit'));
			$data['dept']     	    = clean_and_escape($this->input->post('dept'));
			$data['std_batch_size'] = clean_and_escape($this->input->post('std_batch_size'));
			$data['std_batch_size_unit'] = clean_and_escape($this->input->post('std_batch_size_unit'));
			$data['shelf_life']      = clean_and_escape($this->input->post('shelf_life'));
			$data['code']      	     = clean_and_escape($this->input->post('code'));
			$data['per_yield']       = clean_and_escape($this->input->post('per_yield'));
			$data['pack_size']       = clean_and_escape($this->input->post('pack_size'));
			$data['status']          = clean_and_escape($this->input->post('status'));
			$data['updated_at']      = date("Y-m-d H:i:s");
				
			$this->db->where('id', $id);
			if($this->db->update('products', $data)){
				 $parent_id = $id;  
				  
				  //ACTIVE INGREDIENTS
				 $product_id_arr   = $this->input->post('product_id');
				 $form_arr 		   = $this->input->post('form');
				 $ref_arr   	   = $this->input->post('ref');
				 $part_used_arr    = $this->input->post('part_used');
				 $label_claim_arr  = $this->input->post('label_claim');
				 $item_other_id    = $this->input->post('item_other_id');
					
					 
				 //delete removed products
				 $product_ids = implode(",", array_filter($item_other_id));
				 if (!empty($product_ids)) {
					$delete_unw = $this->db->query("DELETE FROM `products_details` WHERE parent_id='$parent_id' AND type='active_ingredients' AND id NOT IN ($product_ids)"); 
				 }
				 
				  for ($i = 0; $i < count($product_id_arr); $i++) {
					$product_id=$product_id_arr[$i];
					$form=$form_arr[$i];
					$ref=$ref_arr[$i];
					$part_used=$part_used_arr[$i];
					$label_claim=$label_claim_arr[$i];
					
					$product = $this->crud_model->get_raw_products_by_id($product_id)->row_array();
				   
					
					$data_ingredients = array();
					$data_ingredients = array(
						'parent_id'         => $parent_id,
						'type'      		=> 'active_ingredients',
						'product_id'      	=> $product_id,
						'product_name'      => $product['name'],
						'form'      		=> $product['form'],
						'ref'        	   	=> $ref,
						'part_used'        	=> $part_used,
						'label_claim'       => $label_claim,
						'created_at'        => date("Y-m-d H:i:s"),
					);
					if(!empty($item_other_id[$i])){
						$this->db->where('id', $item_other_id[$i]);
						$this->db->update('products_details', $data_ingredients);
					}
					else{
						$this->db->insert('products_details', $data_ingredients);  
					 } 
				}
				
				
				  //EXCIPIENT/BASE
				 $ex_product_id_arr   = $this->input->post('ex_product_id');
				 $ex_form_arr 		  = $this->input->post('ex_form');
				 $ex_ref_arr   	      = $this->input->post('ex_ref');
				 $ex_part_used_arr    = $this->input->post('ex_part_used');
				 $ex_label_claim_arr  = $this->input->post('ex_label_claim');
				 $ex_item_other_id    = $this->input->post('ex_item_other_id');
					
					 
				  //delete removed products
				  $ex_product_ids = implode(",", array_filter($ex_item_other_id));
				  if (!empty($ex_product_ids)) {
					$delete_unw = $this->db->query("DELETE FROM `products_details` WHERE parent_id='$parent_id' AND type='excipient_base' AND id NOT IN ($ex_product_ids)"); 
				  }
				 
				  $product_id=$form=$ref=$part_used=$label_claim="";
				  for($i = 0; $i < count($ex_product_id_arr); $i++) {
					$product_id=$ex_product_id_arr[$i];
					$form=$ex_form_arr[$i];
					$ref=$ex_ref_arr[$i];
					$part_used=$ex_part_used_arr[$i];
					$label_claim=$ex_label_claim_arr[$i];
					
					$product = $this->crud_model->get_raw_products_by_id($product_id)->row_array();
				   
					$data_excipient = array();
					$data_excipient = array(
						'parent_id'         => $parent_id,
						'type'      		=> 'excipient_base',
						'product_id'      	=> $product_id,
						'product_name'      => $product['name'],
						'form'      		=> $product['form'],
						'ref'        	   	=> $ref,
						'part_used'        	=> $part_used,
						'label_claim'       => $label_claim,
						'created_at'        => date("Y-m-d H:i:s"),
					);
					if(!empty($ex_item_other_id[$i])){
						$this->db->where('id', $ex_item_other_id[$i]);
						$this->db->update('products_details', $data_excipient);
					}
					else{
						$this->db->insert('products_details', $data_excipient);  
					 } 
				 }

				$this->session->set_flashdata('flash_message', get_phrase('products_updated_successfully'));

				$this->db->trans_commit(); // Commit transaction
			} else {
				$this->db->trans_rollback(); // Rollback transaction
				$resultpost = array(
					"status" => 400,
					"message" => 'Failed to update product'
				);
			}
		}

		return simple_json_output($resultpost);
    }
     
    public function delete_products($id)
    {
        $resultpost = array(
    		"status" => 200,
    		"message" => get_phrase('products_deleted_successfully'),
    		"url" => $this->session->userdata('previous_url'),
    	);
       
        $data['is_deleted'] = '1';
        $this->db->where('id', $id);
        $this->db->update('products',$data);
        
        return simple_json_output($resultpost); 
    }
    
    public function get_products_by_id($id)    {
        $this->db->where('id', $id);
        return $this->db->get('products');
    }
    
    public function get_products(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];

        $filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
        $data= array(); 
        $keyword_filter="";
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $keyword_filter = " AND (name like '%" . $keyword . "%')";
        endif;

        $total_count = $this->db->query("SELECT id FROM products WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
        $query = $this->db->query("SELECT id, name,unit,shelf_life,dept,std_batch_size,std_batch_size_unit,code,status,created_at FROM products WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");
        
        if (!empty($query)) {
           foreach ($query->result_array() as $item) {
              $id=$item['id'];
             
             $delete_url="confirm_modal('".base_url()."production_head/products/delete/".$id."','Are you sure want to delete!')";
             $edit_url=base_url().'production_head/products/edit/'.$id;           
             $action='';
             $action .='<a href="'.$edit_url.'" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="#" onclick="'.$delete_url.'" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
             ';     
			 
              if($item['status']== 1){
                $status='<span class="badge badge-success">Active</span>';   
              }else{
                $status='<span class="badge badge-danger">Inactive</span>';
              }
              
                $data[] = array(
                    "sr_no"       => ++$start,
                    "id"          => $item['id'],
                    "name"        => $item['name'],
                    "unit"        => $item['unit'],
                    "shelf_life"  => $item['shelf_life'],
                    "dept"        => $item['dept'],
                    "std_batch"   => $item['std_batch_size'].' '.$item['std_batch_size_unit'],
                    "code"        => $item['code'],
                    "status"      => $status,
                    "date"        => date("d M, Y H:i A", strtotime($item['created_at'])),
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
	
	
	public function get_raw_product_details_by_id($id) {        
        $query = $this->db->query("SELECT id,name,item_code,unit,form FROM `raw_products` WHERE id='$id' AND is_deleted='0' LIMIT 1");
      // echo $this->db->last_query();
        if (!empty($query)) {
            $item = $query->row_array();
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 200,
                'message' => 'success',
                "id" => $item['id'],
                "item_code" => $item['item_code'],
                "unit" => $item['unit'],
                "form" => $item['form'],
            ));
            
        }else{
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'success',
                "id" => '',
                "item_code" =>'',
                "unit" => '',
                "form" =>'',
            ));
        }
    }
	
    public function get_products_details_by_id($id){
        $resultdata = array();        
        $query = $this->db->query("SELECT id, name, unit, shelf_life, dept, std_batch_size, std_batch_size_unit, code, per_yield, pack_size, status, created_at FROM products WHERE id='$id' AND is_deleted=0 LIMIT 1");
		
        if (!empty($query)) {
            $item=$query->row_array();	          
            $id =  $item['id'];
            $date   = date("d M, Y", strtotime($item['created_at']));	  
       
           $high_side= array();	
           $sql_pdetails  = $this->db->query("SELECT id, type, product_id, product_name, form, ref, part_used, label_claim, created_at FROM products_details WHERE parent_id='$id' ");     
			$activeIngredients = array();
			$excipientBase = array();

			// Process the query results
			if ($sql_pdetails->num_rows() > 0) {
				foreach ($sql_pdetails->result_array() as $row) {
					if ($row['type'] === 'active_ingredients') {
					   $activeIngredients[] = array(	
							"id" 	   		  => $row['id'],				
							"product_id" 	  => $row['product_id'],				
							"product_name" 	  => $row['product_name'],				
							"form" 	 		  => $row['form'],				
							"ref" 	 		  => $row['ref'],				
							"part_used" 	  => $row['part_used'],				
							"label_claim" 	  => $row['label_claim'],				
					   );
					} elseif ($row['type'] === 'excipient_base') {						
					   $excipientBase[] = array(	
							"id" 	   		  => $row['id'],				
							"product_id" 	  => $row['product_id'],				
							"product_name" 	  => $row['product_name'],				
							"form" 	 		  => $row['form'],				
							"ref" 	 		  => $row['ref'],				
							"part_used" 	  => $row['part_used'],				
							"label_claim" 	  => $row['label_claim'],				
					   );
					}
				}
			}
				
                         
            $resultdata = array(
                "id" 				  => $item['id'],
                "name"   			  => $item['name'],
                "unit"   			  => $item['unit'],
                "shelf_life"   		  => $item['shelf_life'],
                "dept"   			  => $item['dept'],
                "std_batch_size"   	  => $item['std_batch_size'],
                "std_batch_size_unit" => $item['std_batch_size_unit'],
                "code"   			  => $item['code'],
                "per_yield"   		  => $item['per_yield'],
                "pack_size"   		  => $item['pack_size'],
                "status"   			  => $item['status'],
                "date"   			  => $date,
                "active_ingredients"  => $activeIngredients,     
                "excipient_base"   	  => $excipientBase,   
            );
        
      }
      return $resultdata;
    }      
    
}
?>