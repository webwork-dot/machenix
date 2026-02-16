<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quality_control_model extends CI_Model{
    
    function __construct() {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
    }
	
	public function get_raw_material(){  
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
		
		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
        $data= array(); 
        $keyword_filter="";
		
		if (isset($_REQUEST['warehouse_id']) && $_REQUEST['warehouse_id'] != ""):
		$warehouse_id        = $_REQUEST['warehouse_id'];
		if($warehouse_id !='All'){
			$keyword_filter .=" AND (warehouse_id='" . $warehouse_id . "')"; 
		}
		endif;
		
		if (isset($_REQUEST['type']) && $_REQUEST['type'] != ""):
		$type        = $_REQUEST['type'];
		$keyword_filter .=" AND (status='" . $type . "')"; 
		endif;
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            //$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%' OR supplier_name like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%')";
        endif;
		
		$total_count = $this->db->query("SELECT id FROM inventory_dupl_history WHERE (id!='') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,order_id,warehouse_name,product_id,product_name,quantity,batch_no,invoice_no,expiry_date ,added_date,ar_no,approved_date,sample_qty FROM inventory_dupl_history WHERE (id!='') $keyword_filter ORDER BY id DESC LIMIT $start, $length");
		
		if (!empty($query)) {
		   foreach ($query->result_array() as $item) {
			   $id=$item['id'];
			   $order_id=$item['order_id'];
			   $product_id=$item['product_id'];
			 
				$voucher_no = $this->common_model->selectByidParam($order_id,'purchase_order','voucher_no');
				$supplier_name = $this->common_model->selectByidParam($order_id,'purchase_order','supplier_name');
				
				
				$modal_url= "showCallsModal('".base_url()."modal/popup_qc/raw_material_modal/".$id."','Mark As Done')";
			 			 
				$action='<a href="#" href="javascript:void(0)" onclick="'.$modal_url.'" data-toggle="tooltip" data-bs-placement="top" title="Mark Delivery"><button type="button" class="btn mr-1 mb-1 icon-btn-edit" ><i class="fa fa-check" aria-hidden="true"></i></button></a>';
				
				$unit = $this->common_model->selectByidParam($product_id,'raw_products','unit');
				
				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"warehouse_name"        => $item['warehouse_name'],
					"product_name"        => $item['product_name'],
					"supplier_name"        => $supplier_name,
					"voucher_no"        => $voucher_no,
					"rcv_quantity"        => $item['quantity'].' - '.$unit,
					"quantity"        => $item['quantity'],
					"batch_no"        => $item['batch_no'],
					"ar_no"        => $item['ar_no'],
					"sample_qty"        => $item['sample_qty'],
					"invoice_no"      => ($item['invoice_no']!='' && $item['invoice_no']!=null) ? $item['invoice_no'] : '-',
					"expiry_date"        =>  ($item['expiry_date']!=null && $item['expiry_date']!='0000-00-00') ? date('d M, Y',strtotime($item['expiry_date'])) : '',
					"date"        =>  ($item['added_date']!=null && $item['added_date']!='0000-00-00') ? date('d M, Y',strtotime($item['added_date'])) : '',
					"approved_date"        =>  ($item['approved_date']!=null && $item['approved_date']!='0000-00-00') ? date('d M, Y',strtotime($item['approved_date'])) : '',
					"action"        =>  $action,
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
	
	public function get_raw_material_product($id){
		$data = array();
		$query = $this->db->query("SELECT id,order_id,warehouse_name,product_name,quantity,batch_no,invoice_no,expiry_date ,added_date FROM inventory_dupl_history WHERE (id='$id') limit 1");
		if($query->num_rows() > 0){
			$row = $query->row_array();
			$id=$row['id'];
		    $order_id=$row['order_id'];
		 
			$voucher_no = $this->common_model->selectByidParam($order_id,'purchase_order','voucher_no');
			$supplier_name = $this->common_model->selectByidParam($order_id,'purchase_order','supplier_name');
			
			$data[] = array(
				"id" => $row['id'],
				"voucher_no" => $voucher_no,
				"supplier_name" => $supplier_name,
				"warehouse_name" => $row['warehouse_name'],
				"product_name" => $row['product_name'],
				"quantity" => $row['quantity'],
				"batch_no" => $row['batch_no'],
				"invoice_no" => $row['invoice_no'],
				"expiry_date"        =>  ($row['expiry_date']!=null && $row['expiry_date']!='0000-00-00') ? date('d M, Y',strtotime($row['expiry_date'])) : '',
				"date"        =>  ($row['added_date']!=null && $row['added_date']!='0000-00-00') ? date('d M, Y',strtotime($row['added_date'])) : '',
			);
		}
		return $data;
	}
	
	public function complete_raw_material_product(){
		$resultpost = array(
    		"status" => 200,
    		"message" => get_phrase('mark_as_done_successfully'),
    	);
		
	    date_default_timezone_set('Asia/Kolkata');
	    $id = $this->input->post('id', true);
	    $approved_date = $this->input->post('approved_date', true);
	    $sample_qty = $this->input->post('sample_qty', true);
		
		$query = $this->db->query("SELECT id,parent_id,order_id,warehouse_name,product_name,quantity,batch_no,invoice_no,expiry_date,added_date,received_amount FROM inventory_dupl_history WHERE (id='$id') limit 1");
		if($query->num_rows() > 0){
			$row = $query->row_array();
			$parent_id = $row['parent_id'];
			$quantity = $row['quantity'];
			$order_id = $row['order_id'];
			$invoice_no = $row['invoice_no'];
			$received_amount = $row['received_amount'];
			
			$supplier_name = $this->common_model->selectByidParam($order_id,'purchase_order','supplier_name');
			
			$query_1 = $this->db->query("SELECT warehouse_id,warehouse_name,product_id,product_name,quantity,batch_no,expiry_date FROM inventory_dupl WHERE (id='$parent_id') limit 1");
			if($query_1->num_rows() > 0){
				$row_1 = $query_1->row_array();
				$warehouse_id = $row_1['warehouse_id'];
				$warehouse_name = $row_1['warehouse_name'];
				$product_id = $row_1['product_id'];
				$product_name = $row_1['product_name'];
				$quantity = $row_1['quantity'];
				$batch_no = $row_1['batch_no'];
				$expiry_date = $row_1['expiry_date'];
				
				$ar_no = $this->get_inv_ar_no($supplier_name,$product_id);
				//echo $ar_no;exit();
				
				$new_qty = intval($row_1['quantity']) - intval($quantity) ;
				$new_date = array();
				$new_date['quantity'] = $new_qty;
				$this->db->where('id',$parent_id);
				$this->db->update('inventory_dupl',$new_date);
				
				$data = array();
				$data['status'] = 'completed';
				$data['approved_date'] = $approved_date;
				$data['sample_qty'] = $sample_qty;
				$data['ar_no'] = $ar_no;
				$this->db->where('id',$id);
				$this->db->update('inventory_dupl_history',$data);
				
				$inv_data = array();
				$check = $this->db->query("SELECT id,quantity FROM inventory where product_id='$product_id' and warehouse_id='$warehouse_id' and batch_no='$batch_no'");
                if($check->num_rows() > 0){
                    $check_row = $check->row_array();
                    $check_quantity = $check_row['quantity'];
                    $check_id = $check_row['id'];
					
					$final_quantity = intval($check_quantity) + $quantity;
					
					$prod = array();
					$pro_de = array();
                    $prod['quantity'] = $final_quantity;
                    $this->db->where('id',$check_id);
                    $this->db->update('inventory',$prod);
					
					$pro_de['order_id'] = $order_id;
                    $pro_de['parent_id'] = $check_id;
                    $pro_de['warehouse_name'] = $warehouse_name;
                    $pro_de['warehouse_id'] = $warehouse_id;
                    $pro_de['product_id'] = $product_id;
                    $pro_de['product_name'] = $product_name;
                    $pro_de['quantity']    = $quantity;
                    $pro_de['status'] 	   = 'in';
                    $pro_de['received_date'] = $approved_date;
                    $pro_de['batch_no'] = $batch_no;
                    $pro_de['expiry_date'] = $expiry_date;
                    $pro_de['invoice_no'] = $invoice_no;
                    $pro_de['received_amount'] = $received_amount;
                    $pro_de['approved_date'] = $approved_date;
                    $pro_de['sample_qty'] = $sample_qty;
                    $pro_de['ar_no'] = $ar_no;
                    $pro_de['added_date']  = date("Y-m-d H:i:s");
					$pro_de['added_by_id']   = $this->session->userdata('super_user_id');
					$pro_de['added_by_name'] = $this->session->userdata('super_name');
                    $this->db->insert('inventory_history',$pro_de);
				}else{
					$prod = array();
					$pro_de = array();
					$prod['warehouse_name'] = $warehouse_name;
                    $prod['warehouse_id'] = $warehouse_id;
                    $prod['product_id'] = $product_id;
                    $prod['product_name'] = $product_name;
					$prod['batch_no'] = $batch_no;
                    $prod['expiry_date'] = $expiry_date;
                    $prod['quantity'] = $quantity;
                    $this->db->insert('inventory',$prod);
					$check_id = $this->db->insert_id();
					
					$pro_de['order_id'] = $order_id;
                    $pro_de['parent_id'] = $check_id;
                    $pro_de['warehouse_name'] = $warehouse_name;
                    $pro_de['warehouse_id'] = $warehouse_id;
                    $pro_de['product_id'] = $product_id;
                    $pro_de['product_name'] = $product_name;
                    $pro_de['quantity']    = $quantity;
                    $pro_de['status'] 	   = 'in';
                    $pro_de['received_date'] = $approved_date;
                    $pro_de['batch_no'] = $batch_no;
                    $pro_de['expiry_date'] = $expiry_date;
                    $pro_de['invoice_no'] = $invoice_no;
                    $pro_de['received_amount'] = $received_amount;
                    $pro_de['approved_date'] = $approved_date;
                    $pro_de['sample_qty'] = $sample_qty;
                    $pro_de['ar_no'] = $ar_no;
                    $pro_de['added_date']  = date("Y-m-d H:i:s");
					$pro_de['added_by_id']   = $this->session->userdata('super_user_id');
					$pro_de['added_by_name'] = $this->session->userdata('super_name');
                    $this->db->insert('inventory_history',$pro_de);
				}	
			}
		}
		
		$this->session->set_flashdata('flash_message', "Mark As Done Successfully !!");
        return simple_json_output($resultpost); 
		
	}
	
	public function get_inv_ar_no($supplier_name,$product_id){  
        // date("Y-m-d H:i:s");
        $year = date("y");
        $initials = strtoupper(substr($supplier_name, 0, 2));
		$type = $this->common_model->selectByidParam($product_id,'raw_products','type');
        $query = $this->db->query("SELECT id,number FROM inv_ar_no WHERE year='$year' ORDER BY id DESC LIMIT 1");
        if($query->num_rows() > 0){
            $row = $query->row_array();
			$id = $row['id'];
            $number = intval($row['number']) + 1 ;
			$data['number'] = $number;
            $this->db->where('id',$id);
            $this->db->update('inv_ar_no',$data);
			
        }else{
            $number = '1' ;
			$data['number'] = $number;
			$data['year'] = $year;
            $this->db->insert('inv_ar_no',$data);
        }
		
		$number = sprintf("%04d",$number);
		$voucher_no = $initials.'-'.$type.$year.$number;
        return $voucher_no;
    }
}
?>