<?php
defined('BASEPATH') or exit('No direct script access allowed');

// PhpSpreadsheet imports
require_once APPPATH . 'third_party/phpspreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Inventory_model extends CI_Model
{

	function __construct()
	{
		parent::__construct();
		/*cache control*/
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		date_default_timezone_set('Asia/Calcutta');
	}

	public function get_ajax_dashboard_stats($filter_data)
	{
		$resultdata = array();
		$date_filter = '';
		$date_filter2 = '';

		if (isset($filter_data['date_range']) && $filter_data['date_range'] != "") :
			$order_date = explode(' - ', $filter_data['date_range']);
			$from =  date('Y-m-d', strtotime($order_date[0]));
			//   $from =  '2024-11-01'; 
			$to =  date('Y-m-d', strtotime($order_date[1]));
			$date_filter = " AND (DATE(s.date) BETWEEN '$from' AND '$to')";
			$date_filter2 = " AND (DATE(date) BETWEEN '$from' AND '$to')";
		endif;

		// Sales
		$sales_query = $this->db->query("SELECT 
                                            (SELECT SUM(grand_total) FROM sales_order WHERE (is_deleted='0') $date_filter2) as total_sales_amount,
                                            (SELECT SUM(total_qty) 
                                             FROM (
                                                SELECT SUM(sp.batch_qty) as total_qty
                                                FROM sales_order_product_batch as sp
                                                INNER JOIN sales_order as s ON sp.order_id = s.id
                                                WHERE (s.is_deleted = '0')
                                                $date_filter
                                                GROUP BY sp.order_id
                                             ) as grouped_batches) as total_sales_qty");

		$total_sales_amt = 0;
		$total_sales_qty = 0;
		if ($sales_query->num_rows() > 0) {
			$sales_query = $sales_query->row_array();

			$total_sales_amt = round($sales_query['total_sales_amount']);
			$total_sales_qty = round($sales_query['total_sales_qty']);
		}

		// Purchase
		$purchase_query = $this->db->query("SELECT 
		                                    (SELECT SUM(po.total_val) as grand_total FROM purchase_order_product as po INNER JOIN purchase_order as s ON po.parent_id = s.id WHERE (s.is_deleted = '0') $date_filter) as total_purchase_amount,
                                            (SELECT SUM(po.quantity) as total_qty FROM purchase_order_product as po INNER JOIN purchase_order as s ON po.parent_id = s.id WHERE (s.is_deleted = '0') $date_filter) as total_purchase_qty");

		$total_purchase_amt = 0;
		$total_purchase_qty = 0;
		if ($purchase_query->num_rows() > 0) {
			$purchase_query = $purchase_query->row_array();

			$total_purchase_amt = round($purchase_query['total_purchase_amount']);
			$total_purchase_qty = round($purchase_query['total_purchase_qty']);
		}

		// Purchase Return
		$purchase_return_query = $this->db->query("SELECT 
                                            (SELECT SUM(pr.quantity) as total_qty FROM purchase_return_product as pr INNER JOIN purchase_return as s ON pr.parent_id = s.id WHERE (s.is_deleted = '0') $date_filter) as total_preturn_qty,
                                            (SELECT SUM(pr.amount) as total_qty FROM purchase_return_product as pr INNER JOIN purchase_return as s ON pr.parent_id = s.id WHERE (s.is_deleted = '0') $date_filter) as total_preturn_amt,
                                            (SELECT COUNT(*) FROM purchase_return WHERE (is_deleted = '0') $date_filter2) as total_preturn_count");


		$total_purchase_return_qty = 0;
		$total_purchase_return_amt = 0;
		if ($purchase_return_query->num_rows() > 0) {
			$purchase_return_query = $purchase_return_query->row_array();

			$total_purchase_return_qty = round($purchase_return_query['total_preturn_qty']);
			$total_purchase_return_amt = round($purchase_return_query['total_preturn_amt']);
		}

		// Damage Stock
		$damage_stock_query = $this->db->query("SELECT 
                                            (SELECT SUM(pr.quantity) as total_qty FROM damage_stock_product as pr INNER JOIN damage_stock as s ON pr.parent_id = s.id WHERE pr.is_scrap='0' AND (s.is_deleted = '0') $date_filter) as total_damage_qty,
                                            (SELECT COUNT(*) FROM damage_stock WHERE (is_deleted = '0') $date_filter2) as total_damage_count");

		$total_damage_stock = 0;
		$total_damage_stock_qty = 0;
		if ($damage_stock_query->num_rows() > 0) {
			$damage_stock_query = $damage_stock_query->row_array();

			$total_damage_stock = round($damage_stock_query['total_damage_count']);
			$total_damage_stock_qty = round($damage_stock_query['total_damage_qty']);
		}

		$data = [
			"total_sales_amt"           => ind_currency($total_sales_amt),
			"total_sales_qty"           => $total_sales_qty,

			"total_purchase_amt"        => ind_currency($total_purchase_amt),
			"total_purchase_qty"        => $total_purchase_qty,

			"total_purchase_return_qty" => $total_purchase_return_qty,
			"total_purchase_return_amt" => ind_currency($total_purchase_return_amt),

			"total_damage_stock" => $total_damage_stock,
			"total_damage_stock_qty" => $total_damage_stock_qty,
		];

		// echo json_encode($data); exit();

		return $data;
	}

	public function get_no_stock_products()
	{
		$data = $this->db->query("SELECT pvar.sku_code FROM product_variation as pvar 
        INNER JOIN raw_products as rp ON rp.id = pvar.product_id
        LEFT JOIN inventory as inv ON pvar.sku_code = inv.item_code 
        WHERE (inv.item_code IS NULL OR inv.quantity='0') AND rp.is_deleted='0' ");

		$result = [];
		if ($data->num_rows() > 0) {
			foreach ($data->result_array() as $item) {
				$result[] = $item;
			}
		}

		return $result;
	}

	public function get_ajax_ranked_products()
	{
		$sales = $this->db->query("SELECT 
            s.id as sid, sp.id as pid, sp.item_code, sp.product_id, sb.batch_qty 
            FROM sales_order_product as sp 
            INNER JOIN sales_order as s ON s.id = sp.order_id
            INNER JOIN sales_order_product_batch as sb ON sb.order_product_id = sp.id 
            WHERE MONTH(s.date) = MONTH(CURDATE()) AND YEAR(s.date) = YEAR(CURDATE())");

		$result = [];
		if ($sales->num_rows() > 0) {
			foreach ($sales->result_array() as $sale) {
				if (isset($result[$sale['item_code']])) {
					$result[$sale['item_code']] = $result[$sale['item_code']] + $sale['batch_qty'];
				} else {
					$result[$sale['item_code']] = $sale['batch_qty'];
				}
			}
		}

		return $result;
	}

	// public function get_ajax_ranked_products(){
	//     $sales = $this->db->query("SELECT 
	//         s.id as sid, sp.id as pid, sp.item_code, sp.product_id 
	//         FROM sales_order_product as sp INNER JOIN sales_order as s ON s.id = sp.order_id 
	//         WHERE MONTH(s.date) = MONTH(CURDATE()) AND YEAR(s.date) = YEAR(CURDATE())");

	//     $result = [];

	//     if($sales->num_rows() > 0) {
	//         echo json_encode($sales->result_array()); exit();
	//         foreach($sales->result_array() as $sale) {
	//             $order_id = $sale['sid'];
	//             $product_id = $sale['pid'];
	//             $qtys = $this->db->query("SELECT SUM(batch_qty) as qty FROM sales_order_product_batch WHERE order_id='$order_id' AND order_product_id='$product_id'");

	//             if($qtys->num_rows() > 0){
	//                 $qtys = $qtys->row_array();
	//                 if(isset($result[$sale['item_code']])) {
	//                     $result[$sale['item_code']] = $result[$sale['item_code']] + $qtys['qty'];
	//                 } else {
	//                     $result[$sale['item_code']] = $qtys['qty'];
	//                 }
	//             }

	//         }
	//     }

	//     return $result;

	// }

	public function check_duplication($action = "", $field = "", $email = "", $table = "", $user_id = "")
	{
		$duplicate_email_check = $this->db->get_where($table, array(
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

	public function check_duplication_without_del($action = "", $field = "", $email = "", $table = "", $user_id = "")
	{
		$duplicate_email_check = $this->db->get_where($table, array(
			$field => $email,
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

	public function add_warehouse()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('warehouse_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_warehouse_name = $this->check_duplication('on_create', 'name', $name, 'warehouse');
		} else {
			$check_warehouse_name  = true;
		}

		if ($check_warehouse_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('warehouse_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Warehouse Name Duplication'
			);
		} else {
			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->selectByidParam($state_id, 'state_list', 'state');
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->selectByidParam($city_id, 'city_list', 'district');
			} else {
				$city_name = '';
			}


			$data['name']         = $name;
			$data['address']      = clean_and_escape($this->input->post('address'));
			$data['address_2']      = clean_and_escape($this->input->post('address_2'));
			$data['address_3']      = clean_and_escape($this->input->post('address_3'));
			$data['pincode']   = clean_and_escape($this->input->post('pincode'));
			$data['contact_name'] = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']   = clean_and_escape($this->input->post('contact_no'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$user_id                = $this->session->userdata('super_user_id');
			$user_name              = $this->session->userdata('super_name');
			$data['state_id']    = $state_id;
			$data['state_name']    = $state_name;
			$data['city_id']    = $city_id;
			$data['city_name']    = $city_name;
			$data['added_by_id']    = $user_id;
			$data['added_by_name']  = $user_name;
			$data['company_id']    = $this->session->userdata('company_id');
			$data['added_date']   = date("Y-m-d H:i:s");

			$this->db->insert('warehouse', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('warehouse_added_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function edit_warehouse($id = "")
	{

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('warehouse_updated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_warehouse_name = $this->check_duplication('on_update', 'name', $name, 'warehouse', $id);
		} else {
			$check_warehouse_name  = true;
		}

		if ($check_warehouse_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('warehouse_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Warehouse Name Duplication'
			);
		} else {

			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->selectByidParam($state_id, 'state_list', 'state');
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->selectByidParam($city_id, 'city_list', 'district');
			} else {
				$city_name = '';
			}


			$data['name']         = $name;
			$data['address']      = clean_and_escape($this->input->post('address'));
			$data['address_2']      = clean_and_escape($this->input->post('address_2'));
			$data['address_3']      = clean_and_escape($this->input->post('address_3'));
			$data['pincode']   = clean_and_escape($this->input->post('pincode'));
			$data['contact_name'] = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']   = clean_and_escape($this->input->post('contact_no'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$data['state_id']    = $state_id;
			$data['state_name']    = $state_name;
			$data['city_id']    = $city_id;
			$data['city_name']    = $city_name;
			$data['company_id']    = $this->session->userdata('company_id');
			$this->db->where('id', $id);
			$this->db->update('warehouse', $data);
			$this->session->set_flashdata('flash_message', get_phrase('warehouse_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_warehouse($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('warehouse_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('warehouse', $data);

		return simple_json_output($resultpost);
	}

	public function get_warehouse_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('warehouse');
	}

	public function get_warehouse()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (company_id='" . $company_id . "')";
		}

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (name like '%" . $keyword . "%' 
            OR contact_name like '%" . $keyword . "%')";
		endif;
			
		$total_count = $this->db->query("SELECT id FROM warehouse WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name,gst_no,contact_name,contact_no FROM warehouse WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/warehouse/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/warehouse/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
                         <a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
                         ';

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
					"gst_no"       => $item['gst_no'],
					"contact_name"        => $item['contact_name'],
					"contact_no"   => $item['contact_no'],
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


	public function add_supplier()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('supplier_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_supplier_name = $this->check_duplication('on_create', 'name', $name, 'supplier');
		} else {
			$check_supplier_name  = true;
		}

		if ($check_supplier_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('supplier_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'supplier Name Duplication'
			);
		} else {
			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->selectByidParam($state_id, 'state_list', 'state');
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->selectByidParam($city_id, 'city_list', 'district');
			} else {
				$city_name = '';
			}


			$data['name']         = $name;
			$data['address']      = clean_and_escape($this->input->post('address'));
			$data['address_2']      = clean_and_escape($this->input->post('address_2'));
			$data['address_3']      = clean_and_escape($this->input->post('address_3'));
			$data['pincode']   = clean_and_escape($this->input->post('pincode'));
			$data['contact_name'] = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']   = clean_and_escape($this->input->post('contact_no'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$data['beneficiary']       = clean_and_escape($this->input->post('beneficiary'));
			$data['account_no']       = clean_and_escape($this->input->post('account_no'));
			$data['advising_bank']       = clean_and_escape($this->input->post('advising_bank'));
			$data['bank_address']       = clean_and_escape($this->input->post('bank_address'));
			$data['swift_code']       = clean_and_escape($this->input->post('swift_code'));
			$user_id                = $this->session->userdata('super_user_id');
			$user_name              = $this->session->userdata('super_name');
			$data['state_id']    = $state_id;
			$data['state_name']    = $state_name;
			$data['city_id']    = $city_id;
			$data['city_name']    = $city_name;
			$data['company_id']    = $this->session->userdata('company_id');
			$data['added_by_id']    = $user_id;
			$data['added_by_name']  = $user_name;
			$data['added_date']   = date("Y-m-d H:i:s");

			$temp_path = $this->upload_model->upload_temp_image('signature_image');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/supplier/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['signature_image'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$this->db->insert('supplier', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('supplier_added_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function edit_supplier($id = "")
	{

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('supplier_updated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_supplier_name = $this->check_duplication('on_update', 'name', $name, 'supplier', $id);
		} else {
			$check_supplier_name  = true;
		}

		if ($check_supplier_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('supplier_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'supplier Name Duplication'
			);
		} else {

			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->selectByidParam($state_id, 'state_list', 'state');
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->selectByidParam($city_id, 'city_list', 'district');
			} else {
				$city_name = '';
			}

			$data['name']         = $name;
			$data['address']      = clean_and_escape($this->input->post('address'));
			$data['address_2']      = clean_and_escape($this->input->post('address_2'));
			$data['address_3']      = clean_and_escape($this->input->post('address_3'));
			$data['pincode']   = clean_and_escape($this->input->post('pincode'));
			$data['contact_name'] = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']   = clean_and_escape($this->input->post('contact_no'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$data['beneficiary']       = clean_and_escape($this->input->post('beneficiary'));
			$data['account_no']       = clean_and_escape($this->input->post('account_no'));
			$data['advising_bank']       = clean_and_escape($this->input->post('advising_bank'));
			$data['bank_address']       = clean_and_escape($this->input->post('bank_address'));
			$data['swift_code']       = clean_and_escape($this->input->post('swift_code'));
			$data['company_id']    = $this->session->userdata('company_id');
			$data['state_id']    = $state_id;
			$data['state_name']    = $state_name;
			$data['city_id']    = $city_id;
			$data['city_name']    = $city_name;

			$temp_path = $this->upload_model->upload_temp_image('signature_image');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/supplier/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['signature_image'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$this->db->where('id', $id);
			$this->db->update('supplier', $data);
			$this->session->set_flashdata('flash_message', get_phrase('supplier_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_supplier($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('supplier_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('supplier', $data);

		return simple_json_output($resultpost);
	}

	public function get_supplier_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('supplier');
	}

	public function replicate_supplier()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('supplier_replicated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$supplier_id = clean_and_escape($this->input->post('supplier_id'));
		$target_company_id = clean_and_escape($this->input->post('target_company_id'));

		if (empty($supplier_id) || empty($target_company_id)) {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('invalid_request'),
			);
			return simple_json_output($resultpost);
		}

		// Get original supplier data
		$original_supplier = $this->get_supplier_by_id($supplier_id)->row_array();
		
		if (empty($original_supplier)) {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('supplier_not_found'),
			);
			return simple_json_output($resultpost);
		}

		// Check if supplier already exists in target company
		$this->db->where('company_id', $target_company_id);
		$this->db->where('name', $original_supplier['name']);
		$this->db->where('is_deleted', 0);
		$existing_supplier = $this->db->get('supplier')->row_array();
		
		if (!empty($existing_supplier)) {
			$resultpost = array(
				"status" => 400,
				"message" => "Supplier '" . $original_supplier['name'] . "' already exists in the selected company.",
			);
			return simple_json_output($resultpost);
		}

		// Prepare data for new supplier
		$data = array();
		$data['company_id'] = $target_company_id;
		$data['name'] = $original_supplier['name'];
		$data['gst_name'] = $original_supplier['gst_name'];
		$data['gst_no'] = $original_supplier['gst_no'];
		$data['contact_name'] = $original_supplier['contact_name'];
		$data['contact_no'] = $original_supplier['contact_no'];
		$data['address'] = $original_supplier['address'];
		$data['address_2'] = $original_supplier['address_2'];
		$data['address_3'] = $original_supplier['address_3'];
		$data['pincode'] = $original_supplier['pincode'];
		$data['state_id'] = $original_supplier['state_id'];
		$data['state_name'] = $original_supplier['state_name'];
		$data['city_id'] = $original_supplier['city_id'];
		$data['city_name'] = $original_supplier['city_name'];
		$data['state_code'] = $original_supplier['state_code'];
		$data['beneficiary'] = $original_supplier['beneficiary'];
		$data['account_no'] = $original_supplier['account_no'];
		$data['advising_bank'] = $original_supplier['advising_bank'];
		$data['bank_address'] = $original_supplier['bank_address'];
		$data['swift_code'] = $original_supplier['swift_code'];
		
		$user_id = $this->session->userdata('super_user_id');
		$user_name = $this->session->userdata('super_name');
		$data['added_by_id'] = $user_id;
		$data['added_by_name'] = $user_name;
		$data['added_date'] = date("Y-m-d H:i:s");
		$data['is_deleted'] = 0;

		// Handle signature image replication
		if (!empty($original_supplier['signature_image']) && file_exists(FCPATH . $original_supplier['signature_image'])) {
			$original_image_path = FCPATH . $original_supplier['signature_image'];
			$image_extension = pathinfo($original_supplier['signature_image'], PATHINFO_EXTENSION);
			
			// Generate new unique filename
			$year = date("Y");
			$month = date("m");
			$day = date("d");
			$directory = "uploads/supplier/" . "$year/$month/$day/";
			
			if (!is_dir($directory)) {
				mkdir($directory, 0755, true);
			}
			
			// Generate unique filename
			$new_filename = 'supplier_' . time() . '_' . rand(1000, 9999) . '.' . $image_extension;
			$new_image_path = $directory . $new_filename;
			$full_new_path = FCPATH . $new_image_path;
			
			// Copy the image file
			if (copy($original_image_path, $full_new_path)) {
				$data['signature_image'] = $new_image_path;
			} else {
				// If copy fails, set to null
				$data['signature_image'] = null;
			}
		} else {
			$data['signature_image'] = null;
		}

		// Insert new supplier
		$this->db->insert('supplier', $data);
		$this->session->set_flashdata('flash_message', get_phrase('supplier_replicated_successfully'));
		
		return simple_json_output($resultpost);
	}

	public function get_supplier()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (name like '%" . $keyword . "%' 
            OR contact_name like '%" . $keyword . "%')";
		endif;

		$company_id = $this->session->userdata('company_id');
		if($company_id) {
			$keyword_filter .= " AND (company_id = '" . $company_id . "')";
		}

		$total_count = $this->db->query("SELECT id FROM supplier WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name,gst_no,contact_name,contact_no FROM supplier WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/supplier/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/supplier/edit/' . $id;
				$replicate_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/supplier_replicate_modal/" . $id . "','Replicate Supplier')";
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
             <a href="javascript:void(0);" onclick="' . $replicate_url . '" data-toggle="tooltip" data-bs-placement="top" title="Replicate to Other Company"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
             ';

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
					"gst_no"       => $item['gst_no'],
					"contact_name"        => ($item['contact_name'] != null && $item['contact_name'] != '') ? $item['contact_name'] : '-',
					"contact_no"   => ($item['contact_no'] != null && $item['contact_no'] != '') ? $item['contact_no'] : '-',
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

	public function add_expense_type()
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Expense Type added successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$name       = clean_and_escape($this->input->post('name'));
			$company_id = (int) $this->session->userdata('company_id');

			$check = $this->db->query(
					"SELECT id FROM expense_type 
					WHERE company_id = ? AND name = ? AND is_delete = '0' 
					LIMIT 1",
					array($company_id, $name)
			);

			if ($name == '' || $check->num_rows() > 0) {
					$this->session->set_flashdata('error_message', 'Duplicate or invalid name');
					$resultpost = array(
							"status"  => 400,
							"message" => "Duplicate or invalid name",
					);
					return simple_json_output($resultpost);
			}

			$data = array(
					'company_id' => $company_id,
					'name'       => $name,
					'added_by'   => (int) $this->session->userdata('super_user_id'),
					'created_at' => date("Y-m-d H:i:s"),
			);

			$this->db->insert('expense_type', $data);
			$this->session->set_flashdata('flash_message', 'Expense Type added successfully');

			return simple_json_output($resultpost);
	}

	public function edit_expense_type($id = "")
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Expense Type updated successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$name       = clean_and_escape($this->input->post('name'));
			$company_id = (int) $this->session->userdata('company_id');
			$id         = (int) $id;

			// ✅ Duplicate check for same company, excluding current id
			$check = $this->db->query(
					"SELECT id FROM expense_type
					WHERE company_id = ? AND name = ? AND is_delete = '0' AND id != ?
					LIMIT 1",
					array($company_id, $name, $id)
			);

			if ($name == '' || $check->num_rows() > 0) {
					$this->session->set_flashdata('error_message', 'Duplicate or invalid name');
					$resultpost = array(
							"status"  => 400,
							"message" => "Duplicate or invalid name",
					);
					return simple_json_output($resultpost);
			}

			$data = array('name' => $name);

			$this->db->where('id', $id);
			$this->db->where('company_id', $company_id); // extra safety
			$this->db->update('expense_type', $data);

			$this->session->set_flashdata('flash_message', 'Expense Type updated successfully');
			return simple_json_output($resultpost);
	}

	public function delete_expense_type($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => "Expense Type deleted successfully",
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_delete'] = '1';
		$this->db->where('id', $id);
		$this->db->update('expense_type', $data);

		return simple_json_output($resultpost);
	}

	public function get_expense_type_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('expense_type');
	}

	public function get_expense_type()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (name like '%" . $keyword . "%')";
		endif;

		$company_id = $this->session->userdata('company_id');

		$total_count = $this->db->query("SELECT id FROM expense_type WHERE (is_delete='0' AND company_id = '$company_id') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name FROM expense_type WHERE (is_delete='0' AND company_id = '$company_id') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/expense_type/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/expense-type/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				';

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
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

	public function raw_products_delete_sku()
	{
		$id = $this->input->post('id');
		$this->db->where('id', $id)->delete('product_sku');
		echo json_encode([
			"status"     => 200,
			"message"     => "Deleted Successfully",
		]);
	}

	/* Bank Accounts */
	public function add_bank_accounts()
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Bank Account added successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$name       = clean_and_escape($this->input->post('name'));
			$ifsc_code  = clean_and_escape($this->input->post('ifsc_code'));
			$bank_name  = clean_and_escape($this->input->post('bank_name'));
			$account_no = clean_and_escape($this->input->post('account_no'));

			if ($name == '' || $ifsc_code == '' || $bank_name == '' || $account_no == '') {
					$this->session->set_flashdata('error_message', 'All fields are required');
					$resultpost = array(
							"status"  => 400,
							"message" => "All fields are required",
					);
					return simple_json_output($resultpost);
			}

			$company_id = (int) $this->session->userdata('company_id');

			// ✅ Duplicate check (same company + same account)
			$exists = $this->db->select('id')
					->from('bank_accounts')
					->where('company_id', $company_id)
					->where('account_no', $account_no)
					// optional but recommended to avoid false dupes if account_no repeats across different banks:
					->where('ifsc_code', $ifsc_code)
					->limit(1)
					->get()
					->num_rows();

			if ($exists > 0) {
					$this->session->set_flashdata('error_message', 'This bank account already exists.');
					$resultpost = array(
							"status"  => 400,
							"message" => "Account already exists",
					);
					return simple_json_output($resultpost);
			}

			$data = array(
					'company_id' => $company_id,
					'name'       => $name,
					'ifsc_code'  => $ifsc_code,
					'bank_name'  => $bank_name,
					'account_no' => $account_no,
					'added_by'   => (int) $this->session->userdata('super_user_id'),
					'created_at' => date("Y-m-d H:i:s"),
			);

			$this->db->insert('bank_accounts', $data);
			$this->session->set_flashdata('flash_message', 'Bank Account added successfully');

			return simple_json_output($resultpost);
	}


	public function edit_bank_accounts($id = "")
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Bank Account updated successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$name       = clean_and_escape($this->input->post('name'));
			$ifsc_code  = clean_and_escape($this->input->post('ifsc_code'));
			$bank_name  = clean_and_escape($this->input->post('bank_name'));
			$account_no = clean_and_escape($this->input->post('account_no'));

			if ($name == '' || $ifsc_code == '' || $bank_name == '' || $account_no == '') {
					$this->session->set_flashdata('error_message', 'All fields are required');
					$resultpost = array(
							"status"  => 400,
							"message" => "All fields are required",
					);
					return simple_json_output($resultpost);
			}

			$company_id = (int) $this->session->userdata('company_id');
			$id         = (int) $id;

			// ✅ Duplicate check excluding current id
			$exists = $this->db->select('id')
					->from('bank_accounts')
					->where('company_id', $company_id)
					->where('account_no', $account_no)
					->where('ifsc_code', $ifsc_code) // remove this line if you want only account_no uniqueness
					->where('id !=', $id)
					->limit(1)
					->get()
					->num_rows();

			if ($exists > 0) {
					$this->session->set_flashdata('error_message', 'This bank account already exists.');
					$resultpost = array(
							"status"  => 400,
							"message" => "Account already exists",
					);
					return simple_json_output($resultpost);
			}

			$data = array(
					'name'       => $name,
					'ifsc_code'  => $ifsc_code,
					'bank_name'  => $bank_name,
					'account_no' => $account_no,
			);

			$this->db->where('id', $id);
			$this->db->where('company_id', $company_id); // extra safety so user can't edit other company's row
			$this->db->update('bank_accounts', $data);

			$this->session->set_flashdata('flash_message', 'Bank Account updated successfully');
			return simple_json_output($resultpost);
	}


	public function delete_bank_accounts($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => "Bank Account deleted successfully",
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_delete'] = '1';
		$this->db->where('id', $id);
		$this->db->update('bank_accounts', $data);

		return simple_json_output($resultpost);
	}

	public function get_bank_accounts_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('bank_accounts');
	}

	public function get_bank_accounts()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (company_id='" . $company_id . "')";
		}

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (name like '%" . $keyword . "%' OR bank_name like '%" . $keyword . "%' OR account_no like '%" . $keyword . "%' OR ifsc_code like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM bank_accounts WHERE (is_delete='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name, ifsc_code, bank_name, account_no FROM bank_accounts WHERE (is_delete='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/bank-accounts/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/bank-accounts/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
                         <a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
                         ';

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
					"ifsc_code"   => $item['ifsc_code'],
					"bank_name"   => $item['bank_name'],
					"account_no"  => $item['account_no'],
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
	/* Bank Accounts End */

	public function raw_products_delete_variation()
	{
		$id = $this->input->post('id');
		$this->db->where('id', $id)->delete('product_variation');
		echo json_encode([
			"status"     => 200,
			"message"     => "Variation Deleted Successfully",
		]);
	}

	public function add_raw_products()
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('products_added_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);

			$name = clean_and_escape($this->input->post('name'));
			$item_code = clean_and_escape($this->input->post('item_code'));

			// Check for duplicate item_code
			$checkProduct = $this->db->select('id')->where('item_code', $item_code)->get('raw_products');
			if ($checkProduct->num_rows() > 0) {
				$this->session->set_flashdata('error_message', get_phrase('sku_code_duplication'));
				$resultpost = array(
					"status" => 400,
					"message" => 'Duplicate SKU: ' . $item_code
				);
			} else {
				$checkProduct = $this->db->select('id')->where('sku_code', $item_code)->get('product_sku');
				if ($checkProduct->num_rows() > 0) {
					$this->session->set_flashdata('error_message', get_phrase('sku_code_duplication'));
					$resultpost = array(
						"status" => 400,
						"message" => 'Duplicate SKU: ' . $item_code
					);
				} else {
					$this->load->model('upload_model');
					$categories = $this->input->post('category_id');

					// Get category's parent_id to determine product type
					$category = $this->common_model->getRowById('categories', 'parent_id', ['id' => $categories]);
					
					// Validate category parent_id and set product type
					if (empty($category) || !isset($category['parent_id'])) {
						$this->db->trans_rollback();
						$resultpost = array(
							"status" => 400,
							"message" => "Invalid category selected. Please select a valid category."
						);
						return simple_json_output($resultpost);
					}
					
					$parent_id = $category['parent_id'];
					$product_type = '';
					
					if ($parent_id == 2) {
						$product_type = 'ready';
					} elseif ($parent_id == 3) {
						$product_type = 'spare';
					} else {
						$this->db->trans_rollback();
						$resultpost = array(
							"status" => 400,
							"message" => "Invalid category. Product must belong to either 'Ready Goods' or 'Spare Parts' category."
						);
						return simple_json_output($resultpost);
					}
					
					$gst = clean_and_escape($this->input->post('gst'));
					$is_variation = clean_and_escape($this->input->post('is_variation'));

					$data['is_variation']   = $is_variation;
					$data['group_id']       = '';
					$data['color_id']       = '';
					$data['color_name']     = '';
					$data['sizes']          = '';
					$data['unit']           = '';
					$data['type']           = $product_type;
					$data['name']           = $name;
					$data['alias']          = clean_and_escape($this->input->post('alias'));
					$data['categories']     = $categories;
					$data['item_code']      = $item_code;
					$data['hsn_code']       = clean_and_escape($this->input->post('hsn_code'));
					$data['gst']            = ($gst) ? $gst : 0;

					$supplier_id = $this->input->post('supplier_id');
					$supplier = $this->common_model->getRowById('supplier', 'name', ['id' => $supplier_id]);
					$data['supplier_id']   = $supplier_id;
					$data['supplier_name'] = ($supplier != '') ? $supplier['name'] : '';

					// Get variation data arrays
					$variation_net_weight = $this->input->post('variation_net_weight');
					$variation_gross_weight = $this->input->post('variation_gross_weight');
					$variation_length = $this->input->post('variation_length');
					$variation_width = $this->input->post('variation_width');
					$variation_height = $this->input->post('variation_height');
					$variation_cbm = $this->input->post('variation_cbm');

					// Count total variation rows
					$total_variations = !empty($variation_net_weight) ? count($variation_net_weight) : 1;
					
					// Calculate totals of all variation rows and store in raw_products
					if (!empty($variation_net_weight) && is_array($variation_net_weight)) {
						$data['cartoon_qty']    = $total_variations; // Total number of rows
						
						// Calculate sum of all variation values
						$total_net_weight = 0;
						$total_gross_weight = 0;
						$total_length = 0;
						$total_width = 0;
						$total_height = 0;
						$total_cbm = 0;
						
						foreach ($variation_net_weight as $index => $net_weight) {
							$total_net_weight += floatval($net_weight ?? 0);
							$total_gross_weight += floatval($variation_gross_weight[$index] ?? 0);
							$total_length += floatval($variation_length[$index] ?? 0);
							$total_width += floatval($variation_width[$index] ?? 0);
							$total_height += floatval($variation_height[$index] ?? 0);
							$total_cbm += floatval($variation_cbm[$index] ?? 0);
						}
						
						$data['net_weight']    	= clean_and_escape($total_net_weight);
						$data['gross_weight']  	= clean_and_escape($total_gross_weight);
						$data['length']			= clean_and_escape($total_length);
						$data['width']			= clean_and_escape($total_width);
						$data['height']  		= clean_and_escape($total_height);
						$data['cbm']			= clean_and_escape($total_cbm);
					} else {
						$data['cartoon_qty']    = 1;
						$data['net_weight']    	= 0;
						$data['gross_weight']  	= 0;
						$data['length']			= 0;
						$data['width']			= 0;
						$data['height']  		= 0;
						$data['cbm']			= 0;
					}

					$data['usd_rate']  		= clean_and_escape($this->input->post('usd_rate'));
					$data['product_mrp']     = clean_and_escape($this->input->post('product_mrp'));
					$data['costing_price']   = clean_and_escape($this->input->post('costing_price'));
					$data['status']          = clean_and_escape($this->input->post('status'));
					$data['min_stock']       = clean_and_escape($this->input->post('intimation'));
					$data['intimation']      = clean_and_escape($this->input->post('intimation'));
					$data['listed_1']        = clean_and_escape($this->input->post('p_listed_1'));
					$data['listed_2']        = clean_and_escape($this->input->post('p_listed_2'));
					$data['listed_3']        = clean_and_escape($this->input->post('p_listed_3'));
					$data['listed_4']        = clean_and_escape($this->input->post('p_listed_4'));
					$data['listed_5']        = clean_and_escape($this->input->post('p_listed_5'));
					$data['listed_6']       = 1;
					$data['listed_7']       = 1;
					$data['is_other_sku']   = 0;
					$data['added_date']     = date("Y-m-d H:i:s");

					// if ($is_variation == 1) {
					// 	$temp_path = $this->upload_model->upload_temp_image('image');
					// 	if (!empty($temp_path)) {
					// 		$year      = date("Y");
					// 		$month     = date("m");
					// 		$day       = date("d");
					// 		$directory = "uploads/products/" . "$year/$month/$day/";

					// 		if (!is_dir($directory)) {
					// 			mkdir($directory, 0755, true);
					// 		}

					// 		$data['image'] = $this->upload_model->flash_image_upload($temp_path, $directory);
					// 		$this->upload_model->delete_temp_image($temp_path);
					// 	}
					// }

					$this->db->insert('raw_products', $data);
					$user_id = $this->db->insert_id();
					$this->file_model->add_product_images($user_id);

					// Insert all variation rows (including first row) into product_variation
					if (!empty($variation_net_weight) && is_array($variation_net_weight)) {
						foreach ($variation_net_weight as $index => $net_weight) {
							$variation = [];
							$variation['product_id']     = $user_id;
							$variation['size_id']        = '';
							$variation['size_name']      = '';
							$variation['name']           = $name;
							$variation['sku_code']       = $item_code;
							$variation['cartoon_qty']    = 1; // Always 1 for each variation row
							$variation['net_weight']     = clean_and_escape($net_weight ?? 0);
							$variation['gross_weight']  = clean_and_escape($variation_gross_weight[$index] ?? 0);
							$variation['length']         = clean_and_escape($variation_length[$index] ?? 0);
							$variation['width']          = clean_and_escape($variation_width[$index] ?? 0);
							$variation['height']         = clean_and_escape($variation_height[$index] ?? 0);
							$variation['cbm']            = clean_and_escape($variation_cbm[$index] ?? 0);
							$variation['is_other']       = 0;
							$variation['listed_1']       = $this->input->post('p_listed_1');
							$variation['listed_2']       = $this->input->post('p_listed_2');
							$variation['listed_3']      = $this->input->post('p_listed_3');
							$variation['listed_4']       = $this->input->post('p_listed_4');
							$variation['listed_5']       = $this->input->post('p_listed_5');
							$variation['listed_6']      = 1;
							$variation['listed_7']       = 1;
							
							// Set variation image if product image exists
							if (isset($data['image']) && !empty($data['image'])) {
								$variation['image'] = $data['image'];
							}

							$this->db->insert('product_variation', $variation);
						}
					} else {
						// Fallback: Insert single variation if no array data
						$variation = [];
						$variation['product_id']    = $user_id;
						$variation['size_id']       = '';
						$variation['size_name']     = '';
						$variation['name']          = $name;
						$variation['sku_code']      = $item_code;
						$variation['cartoon_qty']    = 1; // Always 1 for each variation row
						$variation['net_weight']     = $data['net_weight'];
						$variation['gross_weight']   = $data['gross_weight'];
						$variation['length']         = $data['length'];
						$variation['width']          = $data['width'];
						$variation['height']         = $data['height'];
						$variation['cbm']            = $data['cbm'];
						$variation['is_other']      = 0;
						$variation['listed_1']      = $this->input->post('p_listed_1');
						$variation['listed_2']      = $this->input->post('p_listed_2');
						$variation['listed_3']      = $this->input->post('p_listed_3');
						$variation['listed_4']      = $this->input->post('p_listed_4');
						$variation['listed_5']      = $this->input->post('p_listed_5');
						$variation['listed_6']      = 1;
						$variation['listed_7']      = 1;
						
						if (isset($data['image']) && !empty($data['image'])) {
							$variation['image'] = $data['image'];
						}

						$this->db->insert('product_variation', $variation);
					}

					if ($this->db->trans_status() === FALSE) {
						$this->db->trans_rollback();
						$resultpost = array(
							"status" => 400,
							"message" => "Error occurred while adding Product",
						);
					} else {
						$this->db->trans_commit();
						$this->session->set_flashdata('flash_message', get_phrase('products_added_successfully'));
						$resultpost = array(
							"status" => 200,
							"message" => get_phrase('product_added_successfully'),
							"url" => $this->session->userdata('previous_url'),
						);
					}
				}
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" =>  "Exception occurred: " . $e->getMessage(),
			);
		}
		return simple_json_output($resultpost);
	}

	public function edit_raw_products($id = "")
	{

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('products_updated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));

		$item_code = clean_and_escape($this->input->post('item_code'));
		$is_other_sku = clean_and_escape($this->input->post('is_other_sku'));
		$other_skus = [];
		if ($is_other_sku == 1) {
			$other_skus = ($this->input->post('other_sku'));
			if (!isset($other_skus) || $other_skus == "" || $other_skus == NULL) {
				$other_skus = [];
			}
		}

		$other_skus[] = $item_code;
		$exist_sku = [];
		foreach ($other_skus as $sku) {
			$checkProduct = $this->db->select('id')->where('item_code', $sku)->where('id!=', $id)->get('raw_products');
			if ($checkProduct->num_rows() > 0) {
				$exist_sku[] = $sku;
			} else {
				$checkProduct = $this->db->select('id')->where('sku_code', $sku)->where('product_id!=', $id)->get('product_sku');
				if ($checkProduct->num_rows() > 0) {
					$exist_sku[] = $sku;
				}
			}
		}

		if (count($exist_sku) > 0) {
			$this->session->set_flashdata('error_message', get_phrase('sku_code_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Duplicate SKUs :- ' . implode(', ', $exist_sku)
			);
		} else {
			$this->load->model('upload_model');

			$item_code = clean_and_escape($this->input->post('item_code'));
			// $color_id = clean_and_escape($this->input->post('color_id'));
			// $color = $this->common_model->getRowById('colors', 'name', ['id' => $color_id]);
			// $group_id = clean_and_escape($this->input->post('group_id'));
			// $sizes = $this->input->post('sizes');
			$categories = $this->input->post('category_id');
			
			// Get category's parent_id to determine product type
			$category = $this->common_model->getRowById('categories', 'parent_id', ['id' => $categories]);
			
			// Validate category parent_id and set product type
			if (empty($category) || !isset($category['parent_id'])) {
				$resultpost = array(
					"status" => 400,
					"message" => "Invalid category selected. Please select a valid category."
				);
				return simple_json_output($resultpost);
			}
			
			$parent_id = $category['parent_id'];
			$product_type = '';
			
			if ($parent_id == 2) {
				$product_type = 'ready';
			} elseif ($parent_id == 3) {
				$product_type = 'spare';
			} else {
				$resultpost = array(
					"status" => 400,
					"message" => "Invalid category. Product must belong to either 'Ready Goods' or 'Spare Parts' category."
				);
				return simple_json_output($resultpost);
			}

			$gst = clean_and_escape($this->input->post('gst'));

			$is_variation = clean_and_escape($this->input->post('is_variation'));
			$data['type']           = $product_type;
			$data['name']           = $name;
			$data['alias']  = clean_and_escape($this->input->post('alias'));
			$data['is_variation']   = $is_variation;
			$data['categories']     = $categories;
			$data['item_code']      = $item_code;
			$data['hsn_code']       = clean_and_escape($this->input->post('hsn_code'));
			$data['min_stock']      = clean_and_escape($this->input->post('intimation'));
			$data['intimation']     = clean_and_escape($this->input->post('intimation'));
			$data['product_mrp']    = clean_and_escape($this->input->post('product_mrp'));
			$data['costing_price']  = clean_and_escape($this->input->post('costing_price'));
			$data['gst']            = ($gst) ? $gst : 0;
			$data['net_weight']    	= clean_and_escape($this->input->post('net_weight'));
			$data['gross_weight']  	= clean_and_escape($this->input->post('gross_weight'));
			$data['length']					= clean_and_escape($this->input->post('length'));
			$data['width']					= clean_and_escape($this->input->post('width'));
			$data['height']  				= clean_and_escape($this->input->post('height'));
			$data['cbm']						= clean_and_escape($this->input->post('cbm'));
			$data['usd_rate']  			= clean_and_escape($this->input->post('usd_rate'));

			$supplier_id = $this->input->post('supplier_id');
			$supplier = $this->common_model->getRowById('supplier', 'name', ['id' => $supplier_id]);
			$data['supplier_id']   = $supplier_id;
			$data['supplier_name'] = ($supplier != '') ? $supplier['name'] : '';
			// Get variation data arrays
			$variation_ids = $this->input->post('variation_id');
			$variation_net_weight = $this->input->post('variation_net_weight');
			$variation_gross_weight = $this->input->post('variation_gross_weight');
			$variation_length = $this->input->post('variation_length');
			$variation_width = $this->input->post('variation_width');
			$variation_height = $this->input->post('variation_height');
			$variation_cbm = $this->input->post('variation_cbm');

			// Count total variation rows
			$total_variations = !empty($variation_net_weight) ? count($variation_net_weight) : 1;

			// Calculate totals of all variation rows and store in raw_products
			if (!empty($variation_net_weight) && is_array($variation_net_weight)) {
				$data['cartoon_qty']    = $total_variations; // Total number of rows
				
				// Calculate sum of all variation values
				$total_net_weight = 0;
				$total_gross_weight = 0;
				$total_length = 0;
				$total_width = 0;
				$total_height = 0;
				$total_cbm = 0;
				
				foreach ($variation_net_weight as $index => $net_weight) {
					$total_net_weight += floatval($net_weight ?? 0);
					$total_gross_weight += floatval($variation_gross_weight[$index] ?? 0);
					$total_length += floatval($variation_length[$index] ?? 0);
					$total_width += floatval($variation_width[$index] ?? 0);
					$total_height += floatval($variation_height[$index] ?? 0);
					$total_cbm += floatval($variation_cbm[$index] ?? 0);
				}
				
				$data['net_weight']    	= clean_and_escape($total_net_weight);
				$data['gross_weight']  	= clean_and_escape($total_gross_weight);
				$data['length']			= clean_and_escape($total_length);
				$data['width']			= clean_and_escape($total_width);
				$data['height']  		= clean_and_escape($total_height);
				$data['cbm']			= clean_and_escape($total_cbm);
			} else {
				$data['cartoon_qty']    = 1;
				$data['net_weight']    	= clean_and_escape($this->input->post('net_weight'));
				$data['gross_weight']  	= clean_and_escape($this->input->post('gross_weight'));
				$data['length']			= clean_and_escape($this->input->post('length'));
				$data['width']			= clean_and_escape($this->input->post('width'));
				$data['height']  		= clean_and_escape($this->input->post('height'));
				$data['cbm']			= clean_and_escape($this->input->post('cbm'));
			}

			$data['status']         = clean_and_escape($this->input->post('status'));
			$data['listed_1']       = clean_and_escape($this->input->post('p_listed_1'));
			$data['listed_2']       = clean_and_escape($this->input->post('p_listed_2'));
			$data['listed_3']       = clean_and_escape($this->input->post('p_listed_3'));
			$data['listed_4']       = clean_and_escape($this->input->post('p_listed_4'));
			$data['listed_5']       = clean_and_escape($this->input->post('p_listed_5'));
			$data['listed_6']       = 1;
			$data['listed_7']       = 1;
			$data['is_other_sku']   = $is_other_sku;

			// if ($is_variation == 1) {
			// 	$temp_path = $this->upload_model->upload_temp_image('image');
			// 	if (!empty($temp_path)) {
			// 		$year      = date("Y");
			// 		$month     = date("m");
			// 		$day       = date("d");
			// 		$directory = "uploads/products/" . "$year/$month/$day/";

			// 		if (!is_dir($directory)) {
			// 			mkdir($directory, 0755, true);
			// 		}
			// 		$data['image'] = $this->upload_model->flash_image_upload($temp_path, $directory);
			// 		$this->upload_model->delete_temp_image($temp_path);
			// 	}
			// }

			$this->db->where('id', $id);
			$this->db->update('raw_products', $data);

			$user_id = $id;

			// Get existing variation IDs to track what should be deleted
			$existing_variations = $this->db->select('id')->where('product_id', $user_id)->get('product_variation')->result_array();
			$existing_ids = array_column($existing_variations, 'id');
			$submitted_ids = array_filter($variation_ids ?? [], function($id) { return $id != 0; });

			// Delete variations that were removed
			$ids_to_delete = array_diff($existing_ids, $submitted_ids);
			if (!empty($ids_to_delete)) {
				$this->db->where_in('id', $ids_to_delete)->where('product_id', $user_id)->delete('product_variation');
			}

			// Update or insert variations
			if (!empty($variation_net_weight) && is_array($variation_net_weight)) {
				foreach ($variation_net_weight as $index => $net_weight) {
					$variation = [];
					$variation['product_id']     = $user_id;
					$variation['size_id']        = '';
					$variation['size_name']      = '';
					$variation['name']           = $name;
					$variation['sku_code']       = $item_code;
					$variation['cartoon_qty']    = 1; // Always 1 for each variation row
					$variation['net_weight']     = clean_and_escape($net_weight ?? 0);
					$variation['gross_weight']  = clean_and_escape($variation_gross_weight[$index] ?? 0);
					$variation['length']         = clean_and_escape($variation_length[$index] ?? 0);
					$variation['width']          = clean_and_escape($variation_width[$index] ?? 0);
					$variation['height']         = clean_and_escape($variation_height[$index] ?? 0);
					$variation['cbm']            = clean_and_escape($variation_cbm[$index] ?? 0);
					$variation['is_other']       = 0;
					$variation['listed_1']       = $this->input->post('p_listed_1');
					$variation['listed_2']       = $this->input->post('p_listed_2');
					$variation['listed_3']      = $this->input->post('p_listed_3');
					$variation['listed_4']       = $this->input->post('p_listed_4');
					$variation['listed_5']       = $this->input->post('p_listed_5');
					$variation['listed_6']      = 1;
					$variation['listed_7']       = 1;
					
					// Set variation image if product image exists
					if (isset($data['image']) && !empty($data['image'])) {
						$variation['image'] = $data['image'];
					}

					$variation_id = isset($variation_ids[$index]) ? $variation_ids[$index] : 0;
					if ($variation_id != 0) {
						// Update existing variation
						$this->db->where('id', $variation_id)->where('product_id', $user_id)->update('product_variation', $variation);
					} else {
						// Insert new variation
						$this->db->insert('product_variation', $variation);
					}
				}
			}

			if ($is_other_sku == 1) {
				$other_skus = ($this->input->post('other_sku'));
				$other_skus_id = ($this->input->post('old_sku_id'));

				if (isset($other_skus)) {
					foreach ($other_skus as $index => $skus) {
						$sku_data = [
							"product_id" => $user_id,
							"sku_code" => $skus,
						];

						if ($other_skus_id[$index] != 0) {
							$this->db->where('id', $other_skus_id[$index])->update('product_sku', $sku_data);
						} else {
							$this->db->insert('product_sku', $sku_data);
						}
					}
				}
			}

			$this->session->set_flashdata('flash_message', get_phrase('products_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_raw_products($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('products_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('raw_products', $data);
		
		$inventory_prod = $this->db->where('product_id', $id)->get('inventory');
		if($inventory_prod->num_rows() > 0) {
		    foreach($inventory_prod->result_array() as $prod) {
		        $history = [
		            "parent_id" => $prod['id'],
		            "warehouse_id" => $prod['warehouse_id'],
		            "warehouse_name" => $prod['warehouse_name'],
		            "product_id" => $prod['product_id'],
		            "product_order_id" => null,
		            "product_name" => $prod['product_name'],
		            "size_id" => $prod['size_id'],
		            "size_name" => $prod['size_name'],
		            "categories" => $prod['categories'],
		            "group_id" => $prod['group_id'],
		            "color_id" => $prod['color_id'],
		            "color_name" => $prod['color_name'],
		            "sku" => $prod['sku'],
		            "item_code" => $prod['item_code'],
		            "quantity" => $prod['quantity'],
		            "status" => 'product_delete',
		            "received_date" => date("Y-m-d"),
		            "batch_no" => null,
		            "expiry_date" => null,
		            "invoice_no" => '',
		            "received_amount" => '0',
		            "approved_date" => null,
		            "sample_qty" => null,
		            "ar_no" => null,
		            "added_date" => date("Y-m-d H:i:s"),
                    "added_by_id" => $this->session->userdata('super_user_id'),
			        "added_by_name" => $this->session->userdata('super_name'),
		        ];
		        
		        $this->db->insert('inventory_history', $history);
		    }
		    
		    $this->db->where('product_id', $id)->update('inventory', ['quantity' => 0]);
		}

		return simple_json_output($resultpost);
	}

	public function delete_raw_products_variation($id, $product_id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('products_variation_deleted_successfully'),
			"url" => base_url() . 'inventory/raw-products/edit/' . $product_id,
		);

		$this->db->where('id', $id);
		$this->db->delete('product_variation');

		$this->db->where('variation_id', $id);
		$this->db->delete('product_variation_sku');

		return simple_json_output($resultpost);
	}

	public function delete_raw_products_variation_sku($id, $product_id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('products_variation_sku_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
			"url" => base_url() . 'inventory/raw-products/edit/' . $product_id,
		);
		$this->db->where('id', $id);
		$this->db->delete('product_variation_sku');

		return simple_json_output($resultpost);
	}

	public function update_product_price($id, $total_amount)
	{
		$product_details = $this->get_raw_products_by_id($id)->row_array();
		$gst = $product_details['gst'];
		$gst_amount = ($total_amount * $gst) / 100;
		$amount = $total_amount - $gst_amount;

		$data = array();
		$data['total_amount'] = $total_amount;
		$data['gst_amount'] = $gst_amount;
		$data['amount'] = $amount;
		$this->db->where('id', $id);
		if ($this->db->update('raw_products', $data)) {
			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 200,
				'message' => 'success',
			));
		} else {
			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 400,
				'message' => 'error',
			));
		}
	}

	public function get_raw_products_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('raw_products');
	}

	public function get_product_variation_by_id($id)
	{
		$this->db->where('product_id', $id);
		return $this->db->get('product_variation');
	}

	public function get_product_variation_sku_by_id($id, $variation_id)
	{
		$this->db->where('product_id', $id);
		$this->db->where('variation_id', $variation_id);
		return $this->db->get('product_variation_sku');
	}

	public function get_raw_products()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$user_type = $this->session->userdata('super_type');

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (pv.sku_code like '%" . $keyword . "%' OR p.name like '%" . $keyword . "%' OR p.item_code like '%" . $keyword . "%' OR p.hsn_code like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT  p.id FROM raw_products as p
		LEFT JOIN product_variation as pv ON p.id = pv.product_id
		WHERE (p.is_deleted='0') $keyword_filter group by p.id ORDER BY p.id ASC")->num_rows();
		$query = $this->db->query("SELECT p.id,p.alias,p.categories,p.group_id,p.color_name,p.item_code,p.is_variation,p.image,p.name,p.unit,p.amount,p.form,p.gst_type,p.gst,p.gst_amount,p.total_amount,p.hsn_code,p.sizes,p.cartoon_qty FROM raw_products as p
		LEFT JOIN product_variation as pv ON p.id = pv.product_id
		WHERE (p.is_deleted='0') $keyword_filter group by p.id ORDER BY p.id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$is_variation = $item['is_variation'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/raw_products/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/raw-products/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';

				$action .='<a href="#" onclick="'.$delete_url.'" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>'; 

				$total_amount = preg_replace('/\.?0+$/', '', $item['total_amount']);
				$amount = '<input type="number" class="form-control" placeholder="Enter Price" name="total_amount" id="' . $item['id'] . '" value="' . $total_amount . '" onchange="total_cal(this)" required="" >';

				// Category
				$category = $this->common_model->getRowById('categories', '*', ['id' => $item['categories']]);
        $category_name = $category['name'] ?? '-';

				$yrs = [];
				foreach (explode(',', $item['sizes']) as $size) {
					$size_id = $this->db->select('color_code')->where('id', $size)->get('oc_attribute_values')->row_array();
					$yrs[] = $size_id['color_code'];
				}

				usort($yrs, function ($a, $b) {
					$diff = intval($a) - intval($b);
					if ($diff === 0) {
						$lenDiff = strlen($b) - strlen($a);
						if ($lenDiff !== 0) {
							return $lenDiff;
						}

						return strcmp($a, $b);
					}
					return $diff;
				});

				$size_label = '';

				if (count($yrs) == 1) {
					$size_label = $yrs[0];
				} else {
					$size_label = $yrs[0] . ' - ' . $yrs[count($yrs) - 1];
				}

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
					"alias"        => $item['alias'],
					"unit"       => $item['unit'],
					"amount"        => $item['amount'],
					"form"        => $item['form'],
					"gst_type"        => $item['gst_type'],
					"gst"        => $item['gst'],
					"gst_amount"        => $item['gst_amount'],
					"category_name"        => $category_name,
					"total_amount"        => $amount,
					"hsn_code"        => $item['hsn_code'],
					"item_code"        => $item['item_code'],
					"group_id"        => $item['group_id'],
					"vatiation"        => $size_label,
					"color_name"        => $item['color_name'],
					"action"      => $action,
				);
			}
		}

		$json_data = array(
			"draw" => intval($params['draw']),
			"recordsTotal" => $total_count,
			"recordsFiltered" => $total_count,
			"data" => $data,
			"user_data" => $this->session->userdata('super_type'),
		);
		echo json_encode($json_data);
	}

	public function add_purchase_order()
	{
		$this->db->trans_begin();
		
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_order_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$voucher_no = clean_and_escape($this->input->post('voucher_no'));
		if ($voucher_no != '') {
			$check_voucher_no = $this->check_duplication('on_create', 'voucher_no', $voucher_no, 'purchase_order');
		} else {
			$check_voucher_no  = true;
		}

		if ($check_voucher_no == false) {
			$this->db->trans_rollback();
			$this->session->set_flashdata('error_message', get_phrase('voucher_no_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Voucher No Duplication'
			);
			return simple_json_output($resultpost);
		}

		// Get basic form data
		$method = clean_and_escape($this->input->post('input_method'));
		$warehouse_id = $this->input->post('warehouse_id');
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$company_id = $this->input->post('company_id');
		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
		$total_cbm = floatval($this->input->post('total_cbm')) ?: 0.00;

		// Collect all product rows from all suppliers
		$supplier_ids = $this->input->post('supplier_id');
		$all_product_rows = array();
		$has_valid_product = false;
		
		// Validate that at least one supplier is selected
		if (!is_array($supplier_ids) || empty($supplier_ids) || !$supplier_ids[0]) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Please select at least one supplier."
			);
			return simple_json_output($resultpost);
		}

		// Prepare purchase_order data
		$delivery_address = $this->input->post('delivery_address');
		$data = array(
			'method' => $method,
			'voucher_no' => $voucher_no,
			'date' => $this->input->post('date'),
			'delivery_date' => $this->input->post('delivery_date'),
			'company_id' => $company_id,
			'company_name' => $company_name,
			'warehouse_id' => $warehouse_id,
			'warehouse_name' => $warehouse_name,
			'billing_address' => $delivery_address, // Using delivery_address as billing_address is not in form
			'delivery_address' => $delivery_address,
			'mode_of_payment' => $this->input->post('mode_of_payment'),
			'dispatch' => $this->input->post('dispatch'),
			'destination' => $this->input->post('destination'),
			'other_refrence' => $this->input->post('other_refrence'),
			'terms_of_delivery' => $this->input->post('terms_of_delivery'),
			'narration' => $this->input->post('narration'),
			'total_cbm' => $total_cbm,
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'added_date' => date("Y-m-d H:i:s"),
		);

		// Get all product arrays
		$ready_product_ids = $this->input->post('ready_product_id');
		$ready_qtys = $this->input->post('ready_qty');
		$ready_cbms = $this->input->post('ready_cbm');
		$ready_total_cbms = $this->input->post('ready_total_cbm');
		$ready_model_nos = $this->input->post('ready_model_no');
		$ready_pending_po_qtys = $this->input->post('ready_pending_po_qty');
		$ready_loading_list_qtys = $this->input->post('ready_loading_list_qty');
		$ready_in_stock_qtys = $this->input->post('ready_in_stock_qty');
		$ready_company_stocks = $this->input->post('ready_company_stock');

		$spare_product_ids = $this->input->post('spare_product_id');
		$spare_qtys = $this->input->post('spare_qty');
		$spare_cbms = $this->input->post('spare_cbm');
		$spare_total_cbms = $this->input->post('spare_total_cbm');
		$spare_model_nos = $this->input->post('spare_model_no');
		$spare_pending_po_qtys = $this->input->post('spare_pending_po_qty');
		$spare_loading_list_qtys = $this->input->post('spare_loading_list_qty');
		$spare_in_stock_qtys = $this->input->post('spare_in_stock_qty');
		$spare_company_stocks = $this->input->post('spare_company_stock');

		// Process products by supplier row ID (form uses 1-indexed row IDs)
		// Find all supplier row IDs that have products
		$all_supplier_row_ids = array();
		if (is_array($ready_product_ids)) {
			$all_supplier_row_ids = array_merge($all_supplier_row_ids, array_keys($ready_product_ids));
		}
		if (is_array($spare_product_ids)) {
			$all_supplier_row_ids = array_merge($all_supplier_row_ids, array_keys($spare_product_ids));
		}
		$all_supplier_row_ids = array_unique($all_supplier_row_ids);

		// Process each supplier row
		foreach ($all_supplier_row_ids as $supplier_row_id) {
			$supplier_row_id = intval($supplier_row_id);
			// Get supplier_id from array index (supplier_row_id - 1 because form is 1-indexed but array is 0-indexed)
			$supplier_array_index = $supplier_row_id - 1;
			if (!isset($supplier_ids[$supplier_array_index]) || !$supplier_ids[$supplier_array_index]) {
				continue; // Skip if no supplier selected for this row
			}
			$supplier_id = intval($supplier_ids[$supplier_array_index]);

			// Process Ready Stock products for this supplier row
			if (isset($ready_product_ids[$supplier_row_id]) && is_array($ready_product_ids[$supplier_row_id])) {
				foreach ($ready_product_ids[$supplier_row_id] as $product_index => $product_id) {
					$product_id = intval($product_id);
					$qty = floatval($ready_qtys[$supplier_row_id][$product_index] ?? 0);
					
					// Skip if no product selected or quantity is 0
					if ($product_id > 0 && $qty > 0) {
						$has_valid_product = true;
						$all_product_rows[] = array(
							'supplier_id' => $supplier_id,
							'product_type' => 'ready',
							'product_id' => $product_id,
							'quantity' => $qty,
							'cbm' => floatval($ready_cbms[$supplier_row_id][$product_index] ?? 0),
							'total_cbm' => floatval($ready_total_cbms[$supplier_row_id][$product_index] ?? 0),
							'item_code' => $ready_model_nos[$supplier_row_id][$product_index] ?? '',
							'pending_po_qty' => intval($ready_pending_po_qtys[$supplier_row_id][$product_index] ?? 0),
							'loading_list_qty' => intval($ready_loading_list_qtys[$supplier_row_id][$product_index] ?? 0),
							'in_stock_qty' => intval($ready_in_stock_qtys[$supplier_row_id][$product_index] ?? 0),
							'company_stock' => intval($ready_company_stocks[$supplier_row_id][$product_index] ?? 0),
						);
					}
				}
			}

			// Process Spare Part products for this supplier row
			if (isset($spare_product_ids[$supplier_row_id]) && is_array($spare_product_ids[$supplier_row_id])) {
				foreach ($spare_product_ids[$supplier_row_id] as $product_index => $product_id) {
					$product_id = intval($product_id);
					$qty = floatval($spare_qtys[$supplier_row_id][$product_index] ?? 0);
					
					// Skip if no product selected or quantity is 0
					if ($product_id > 0 && $qty > 0) {
						$has_valid_product = true;
						$all_product_rows[] = array(
							'supplier_id' => $supplier_id,
							'product_type' => 'spare',
							'product_id' => $product_id,
							'quantity' => $qty,
							'cbm' => floatval($spare_cbms[$supplier_row_id][$product_index] ?? 0),
							'total_cbm' => floatval($spare_total_cbms[$supplier_row_id][$product_index] ?? 0),
							'item_code' => $spare_model_nos[$supplier_row_id][$product_index] ?? '',
							'pending_po_qty' => intval($spare_pending_po_qtys[$supplier_row_id][$product_index] ?? 0),
							'loading_list_qty' => intval($spare_loading_list_qtys[$supplier_row_id][$product_index] ?? 0),
							'in_stock_qty' => intval($spare_in_stock_qtys[$supplier_row_id][$product_index] ?? 0),
							'company_stock' => intval($spare_company_stocks[$supplier_row_id][$product_index] ?? 0),
						);
					}
				}
			}
		}

		// Validate that at least one product row exists
		if (!$has_valid_product) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Please add at least one product with quantity greater than 0."
			);
			return simple_json_output($resultpost);
		}

		// Insert purchase_order
		if (!$this->db->insert('purchase_order', $data)) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('something_went_wrong')
			);
			return simple_json_output($resultpost);
		}

		$insert_id = $this->db->insert_id();

		// Insert purchase_order_product rows
		foreach ($all_product_rows as $row) {
			// Get product details from raw_products table
			$product_details = $this->get_raw_products_by_id($row['product_id'])->row_array();
			
			if (!$product_details) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => "Product not found: ID " . $row['product_id']
				);
				return simple_json_output($resultpost);
			}

			$data_p = array(
				'parent_id' => $insert_id,
				'supplier_id' => $row['supplier_id'],
				'product_type' => $row['product_type'],
				'product_id' => $row['product_id'],
				'categories' => $product_details['categories'] ?? NULL,
				'group_id' => $product_details['group_id'] ?? NULL,
				'product_name' => $product_details['name'] ?? '',
				'hsn_code' => $product_details['hsn_code'] ?? NULL,
				'item_code' => $row['item_code'] ?: ($product_details['item_code'] ?? NULL),
				'quantity' => intval($row['quantity']),
				'pending' => intval($row['quantity']),
				'cbm' => $row['cbm'],
				'total_cbm' => $row['total_cbm'],
				'pending_po_qty' => $row['pending_po_qty'],
				'loading_list_qty' => $row['loading_list_qty'],
				'in_stock_qty' => $row['in_stock_qty'],
				'current_company_qty' => $row['company_stock'],
				'cartoon' => intval($product_details['cartoon_qty'] ?? 0),
				'rate' => floatval($product_details['product_mrp'] ?? 0),
				'basic_amount' => floatval($product_details['costing_price'] ?? 0),
			);

			if (!$this->db->insert('purchase_order_product', $data_p)) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => get_phrase('something_went_wrong')
				);
				return simple_json_output($resultpost);
			}
		}

		// Commit transaction
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('something_went_wrong')
			);
		} else {
			$this->db->trans_commit();
			$this->session->set_flashdata('flash_message', get_phrase('purchase_order_added_successfully'));
		}

		return simple_json_output($resultpost);
	}

	public function get_purchase_order_products_for_edit($po_id)
	{
		// Get all products grouped by supplier and product_type
		$products_query = $this->db->query("
			SELECT pop.*, 
				   s.name as supplier_name,
				   rp.name as raw_product_name,
				   (SELECT c.name FROM categories c WHERE FIND_IN_SET(c.id, pop.categories) > 0 LIMIT 1) as category_name
			FROM purchase_order_product pop
			LEFT JOIN supplier s ON s.id = pop.supplier_id
			LEFT JOIN raw_products rp ON rp.id = pop.product_id
			WHERE pop.parent_id = '$po_id'
			ORDER BY pop.supplier_id ASC, pop.product_type ASC, pop.id ASC
		")->result_array();

		// Group products by supplier and product_type
		$grouped_products = array();
		foreach ($products_query as $product) {
			$supplier_id = $product['supplier_id'];
			$supplier_name = $product['supplier_name'] ?? 'Unknown Supplier';
			$product_type = $product['product_type'] ?? 'ready';
			
			if (!isset($grouped_products[$supplier_id])) {
				$grouped_products[$supplier_id] = array(
					'supplier_id' => $supplier_id,
					'supplier_name' => $supplier_name,
					'ready' => array(),
					'spare' => array()
				);
			}
			
			$grouped_products[$supplier_id][$product_type][] = $product;
		}

		return $grouped_products;
	}

	public function edit_purchase_order()
	{
		$this->db->trans_begin();
		
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_order_updated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$po_id = $this->input->post('po_id');
		if (empty($po_id)) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Purchase Order ID is required."
			);
			return simple_json_output($resultpost);
		}

		// Check if PO exists
		$existing_po = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();
		if (empty($existing_po)) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Purchase Order not found."
			);
			return simple_json_output($resultpost);
		}

		$voucher_no = clean_and_escape($this->input->post('voucher_no'));
		if ($voucher_no != '') {
			$check_voucher_no = $this->check_duplication('on_update', 'voucher_no', $voucher_no, 'purchase_order', $po_id);
		} else {
			$check_voucher_no  = true;
		}

		if ($check_voucher_no == false) {
			$this->db->trans_rollback();
			$this->session->set_flashdata('error_message', get_phrase('voucher_no_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Voucher No Duplication'
			);
			return simple_json_output($resultpost);
		}

		// Get basic form data
		$method = clean_and_escape($this->input->post('input_method'));
		$warehouse_id = $this->input->post('warehouse_id');
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$company_id = $this->input->post('company_id');
		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
		$total_cbm = floatval($this->input->post('total_cbm')) ?: 0.00;

		// Collect all product rows from all suppliers
		$supplier_ids = $this->input->post('supplier_id');
		$all_product_rows = array();
		$has_valid_product = false;
		
		// Validate that at least one supplier is selected
		if (!is_array($supplier_ids) || empty($supplier_ids) || !$supplier_ids[0]) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Please select at least one supplier."
			);
			return simple_json_output($resultpost);
		}

		// Prepare purchase_order data
		$delivery_address = $this->input->post('delivery_address');
		$data = array(
			'method' => $method,
			'voucher_no' => $voucher_no,
			'date' => $this->input->post('date'),
			'delivery_date' => $this->input->post('delivery_date'),
			'company_id' => $company_id,
			'company_name' => $company_name,
			'warehouse_id' => $warehouse_id,
			'warehouse_name' => $warehouse_name,
			'billing_address' => $delivery_address,
			'delivery_address' => $delivery_address,
			'mode_of_payment' => $this->input->post('mode_of_payment'),
			'dispatch' => $this->input->post('dispatch'),
			'destination' => $this->input->post('destination'),
			'other_refrence' => $this->input->post('other_refrence'),
			'terms_of_delivery' => $this->input->post('terms_of_delivery'),
			'narration' => $this->input->post('narration'),
			'total_cbm' => $total_cbm,
		);

		// Get all product arrays (same as add)
		$ready_product_ids = $this->input->post('ready_product_id');
		$ready_qtys = $this->input->post('ready_qty');
		$ready_cbms = $this->input->post('ready_cbm');
		$ready_total_cbms = $this->input->post('ready_total_cbm');
		$ready_model_nos = $this->input->post('ready_model_no');
		$ready_pending_po_qtys = $this->input->post('ready_pending_po_qty');
		$ready_loading_list_qtys = $this->input->post('ready_loading_list_qty');
		$ready_in_stock_qtys = $this->input->post('ready_in_stock_qty');
		$ready_company_stocks = $this->input->post('ready_company_stock');

		$spare_product_ids = $this->input->post('spare_product_id');
		$spare_qtys = $this->input->post('spare_qty');
		$spare_cbms = $this->input->post('spare_cbm');
		$spare_total_cbms = $this->input->post('spare_total_cbm');
		$spare_model_nos = $this->input->post('spare_model_no');
		$spare_pending_po_qtys = $this->input->post('spare_pending_po_qty');
		$spare_loading_list_qtys = $this->input->post('spare_loading_list_qty');
		$spare_in_stock_qtys = $this->input->post('spare_in_stock_qty');
		$spare_company_stocks = $this->input->post('spare_company_stock');

		// Process products by supplier row ID (same logic as add)
		$all_supplier_row_ids = array();
		if (is_array($ready_product_ids)) {
			$all_supplier_row_ids = array_merge($all_supplier_row_ids, array_keys($ready_product_ids));
		}
		if (is_array($spare_product_ids)) {
			$all_supplier_row_ids = array_merge($all_supplier_row_ids, array_keys($spare_product_ids));
		}
		$all_supplier_row_ids = array_unique($all_supplier_row_ids);
		sort($all_supplier_row_ids); // Sort to ensure consistent mapping

		// Build mapping: supplier_row_id -> supplier_id
		// Filter out empty supplier_ids and re-index array
		$valid_supplier_ids = array();
		foreach ($supplier_ids as $sid) {
			if (!empty($sid)) {
				$valid_supplier_ids[] = intval($sid);
			}
		}

		// Create mapping: sorted supplier_row_ids map to valid_supplier_ids by position
		$supplier_row_to_id_map = array();
		$supplier_index = 0;
		foreach ($all_supplier_row_ids as $supplier_row_id) {
			if ($supplier_index < count($valid_supplier_ids)) {
				$supplier_row_to_id_map[intval($supplier_row_id)] = $valid_supplier_ids[$supplier_index];
				$supplier_index++;
			}
		}

		// Process each supplier row
		foreach ($all_supplier_row_ids as $supplier_row_id) {
			$supplier_row_id = intval($supplier_row_id);
			
			// Get supplier_id from mapping
			if (!isset($supplier_row_to_id_map[$supplier_row_id]) || !$supplier_row_to_id_map[$supplier_row_id]) {
				continue;
			}
			$supplier_id = $supplier_row_to_id_map[$supplier_row_id];

			// Process Ready Stock products
			if (isset($ready_product_ids[$supplier_row_id]) && is_array($ready_product_ids[$supplier_row_id])) {
				foreach ($ready_product_ids[$supplier_row_id] as $product_index => $product_id) {
					$product_id = intval($product_id);
					$qty = floatval($ready_qtys[$supplier_row_id][$product_index] ?? 0);
					
					if ($product_id > 0 && $qty > 0) {
						$has_valid_product = true;
						$all_product_rows[] = array(
							'supplier_id' => $supplier_id,
							'product_type' => 'ready',
							'product_id' => $product_id,
							'quantity' => $qty,
							'cbm' => floatval($ready_cbms[$supplier_row_id][$product_index] ?? 0),
							'total_cbm' => floatval($ready_total_cbms[$supplier_row_id][$product_index] ?? 0),
							'item_code' => $ready_model_nos[$supplier_row_id][$product_index] ?? '',
							'pending_po_qty' => intval($ready_pending_po_qtys[$supplier_row_id][$product_index] ?? 0),
							'loading_list_qty' => intval($ready_loading_list_qtys[$supplier_row_id][$product_index] ?? 0),
							'in_stock_qty' => intval($ready_in_stock_qtys[$supplier_row_id][$product_index] ?? 0),
							'company_stock' => intval($ready_company_stocks[$supplier_row_id][$product_index] ?? 0),
						);
					}
				}
			}

			// Process Spare Part products
			if (isset($spare_product_ids[$supplier_row_id]) && is_array($spare_product_ids[$supplier_row_id])) {
				foreach ($spare_product_ids[$supplier_row_id] as $product_index => $product_id) {
					$product_id = intval($product_id);
					$qty = floatval($spare_qtys[$supplier_row_id][$product_index] ?? 0);
					
					if ($product_id > 0 && $qty > 0) {
						$has_valid_product = true;
						$all_product_rows[] = array(
							'supplier_id' => $supplier_id,
							'product_type' => 'spare',
							'product_id' => $product_id,
							'quantity' => $qty,
							'cbm' => floatval($spare_cbms[$supplier_row_id][$product_index] ?? 0),
							'total_cbm' => floatval($spare_total_cbms[$supplier_row_id][$product_index] ?? 0),
							'item_code' => $spare_model_nos[$supplier_row_id][$product_index] ?? '',
							'pending_po_qty' => intval($spare_pending_po_qtys[$supplier_row_id][$product_index] ?? 0),
							'loading_list_qty' => intval($spare_loading_list_qtys[$supplier_row_id][$product_index] ?? 0),
							'in_stock_qty' => intval($spare_in_stock_qtys[$supplier_row_id][$product_index] ?? 0),
							'company_stock' => intval($spare_company_stocks[$supplier_row_id][$product_index] ?? 0),
						);
					}
				}
			}
		}

		// Validate that at least one product row exists
		if (!$has_valid_product) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Please add at least one product with quantity greater than 0."
			);
			return simple_json_output($resultpost);
		}

		// Update purchase_order
		$this->db->where('id', $po_id);
		if (!$this->db->update('purchase_order', $data)) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('something_went_wrong')
			);
			return simple_json_output($resultpost);
		}

		// Delete existing products
		$this->db->where('parent_id', $po_id);
		$this->db->delete('purchase_order_product');

		// Insert updated purchase_order_product rows
		foreach ($all_product_rows as $row) {
			// Get product details from raw_products table
			$product_details = $this->get_raw_products_by_id($row['product_id'])->row_array();
			
			if (!$product_details) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => "Product not found: ID " . $row['product_id']
				);
				return simple_json_output($resultpost);
			}

			$data_p = array(
				'parent_id' => $po_id,
				'supplier_id' => $row['supplier_id'],
				'product_type' => $row['product_type'],
				'product_id' => $row['product_id'],
				'categories' => $product_details['categories'] ?? NULL,
				'group_id' => $product_details['group_id'] ?? NULL,
				'product_name' => $product_details['name'] ?? '',
				'hsn_code' => $product_details['hsn_code'] ?? NULL,
				'item_code' => $row['item_code'] ?: ($product_details['item_code'] ?? NULL),
				'quantity' => intval($row['quantity']),
				'pending' => intval($row['quantity']),
				'cbm' => $row['cbm'],
				'total_cbm' => $row['total_cbm'],
				'pending_po_qty' => $row['pending_po_qty'],
				'loading_list_qty' => $row['loading_list_qty'],
				'in_stock_qty' => $row['in_stock_qty'],
				'current_company_qty' => $row['company_stock'],
				'cartoon' => intval($product_details['cartoon_qty'] ?? 0),
				'rate' => floatval($product_details['product_mrp'] ?? 0),
				'basic_amount' => floatval($product_details['costing_price'] ?? 0),
			);

			if (!$this->db->insert('purchase_order_product', $data_p)) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => get_phrase('something_went_wrong')
				);
				return simple_json_output($resultpost);
			}
		}

		// Commit transaction
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('something_went_wrong')
			);
		} else {
			$this->db->trans_commit();
			$this->session->set_flashdata('flash_message', get_phrase('purchase_order_updated_successfully'));
		}

		return simple_json_output($resultpost);
	}

	public function update_voucher_no($voucher_no)
	{
		$voucher_no = explode('/', $voucher_no);
		$pre = $voucher_no[0];
		$year = $voucher_no[1];
		$number = $voucher_no[2];
		$query = $this->db->query("SELECT id FROM purchase_order_voucher WHERE year='$year' ORDER BY id DESC LIMIT 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$id = $row['id'];
			$data = array();
			$data['prefix'] = $pre;
			$data['year'] = $year;
			$data['number'] = $number;
			$this->db->where('id', $id);
			$this->db->update('purchase_order_voucher', $data);
		} else {
			$data = array();
			$data['prefix'] = $pre;
			$data['year'] = $year;
			$data['number'] = $number;
			$this->db->insert('purchase_order_voucher', $data);
		}
	}

	public function delete_purchase_order($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_order_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('purchase_order', $data);

		return simple_json_output($resultpost);
	}

	public function delete_inv_purchase_order($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_order_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$check_del = $this->db->query("SELECT id,warehouse_id,warehouse_name FROM purchase_order WHERE id='$id' and is_deleted='0' limit 1");
		if ($check_del->num_rows() > 0) {
			$res = $check_del->row_array();
			$warehouse_id = $res['warehouse_id'];
			$warehouse_name = $res['warehouse_name'];


			$ord_pro = $this->db->query("SELECT product_id,item_code,product_name,quantity,total_val FROM purchase_order_product WHERE parent_id='$id'");
			foreach ($ord_pro->result_array() as $res_2) {
				$product_id = $res_2['product_id'];
				$item_code = $res_2['item_code'];
				$quantity = $res_2['quantity'];
				$product_name = $res_2['product_name'];
				$total_val = $res_2['total_val'];

				$check = $this->db->query("SELECT id,quantity FROM inventory where product_id='$product_id' and warehouse_id='$warehouse_id' and item_code='$item_code'");
				if ($check->num_rows() > 0) {
					$check_row = $check->row_array();
					$check_quantity = $check_row['quantity'];
					$check_id = $check_row['id'];

					$final_quantity = intval($check_quantity) - $quantity;

					$prod = array();
					$prod['quantity'] = $final_quantity;
					$this->db->where('id', $check_id);
					$this->db->update('inventory', $prod);

					$pro_de['order_id'] = $id;
					$pro_de['parent_id'] = $check_id;
					$pro_de['warehouse_name'] = $warehouse_name;
					$pro_de['warehouse_id'] = $warehouse_id;
					$pro_de['product_id'] = $product_id;
					$pro_de['product_name'] = $product_name;
					$pro_de['item_code'] = $item_code;
					$pro_de['quantity']    = $quantity;
					$pro_de['status'] 	   = 'purchase_delete';
					$pro_de['received_date'] = date("Y-m-d H:i:s");
					$pro_de['batch_no'] = NULL;
					$pro_de['expiry_date'] = NULL;
					$pro_de['invoice_no'] = NUll;
					$pro_de['received_amount'] = $total_val;
					$pro_de['added_date']  = date("Y-m-d H:i:s");
					$pro_de['added_by_id']   = $this->session->userdata('super_user_id');
					$pro_de['added_by_name'] = $this->session->userdata('super_name');
					$this->db->insert('inventory_history', $pro_de);
				}
			}

			$data['is_deleted'] = '1';
			$this->db->where('id', $id);
			$this->db->update('purchase_order', $data);
		}

		return simple_json_output($resultpost);
	}


	public function get_po_voucher_no()
	{
		// date("Y-m-d H:i:s");
		$year  = current_year();
		$voucher_no = '';
		$query = $this->db->query("SELECT number,year,prefix FROM purchase_order_voucher WHERE year='$year' ORDER BY id DESC LIMIT 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$number = $row['number'] + 1;
			$voucher_no = $row['prefix'] . '/' . $row['year'] . '/' . $number;
		} else {
			$voucher_no = 'GPS' . '/' . $year . '/' . '1';
		}
		return $voucher_no;
	}

	public function get_sales_order_no()
	{
		// date("Y-m-d H:i:s");
		$year  = current_year();
		$voucher_no = '';
		$query = $this->db->query("SELECT number,year,prefix FROM sales_order_no WHERE year='$year' ORDER BY id DESC LIMIT 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$number = $row['number'] + 1;
			$voucher_no = $row['prefix'] . '/' . $row['year'] . '/' . $number;
		} else {
			$voucher_no = 'MACH' . '/' . $year . '/' . '1';
		}
		return $voucher_no;
	}

	public function get_purchase_order($delivery_status = [])
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];
		$company_id = $this->session->userdata('company_id');

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%')";
		endif;

		if (count($delivery_status) > 0) {
			$keyword_filter .= " AND (delivery_status NOT IN ('" . implode("','", $delivery_status) . "'))";
		}

		$keyword_filter .= " AND (company_id = '$company_id')";
		// echo $keyword_filter; exit();
		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
			}
		}

		$total_count = $this->db->query("SELECT id FROM purchase_order WHERE (is_deleted='0') AND method = 'import' $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,delivery_status, voucher_no,date,delivery_date,warehouse_name,company_name  FROM purchase_order WHERE (is_deleted='0') AND method = 'import' $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delivery_status = $item['delivery_status'];
				$delivery_date = $item['delivery_date'];
				$action = '';

				// Purchase Order
				$po = [
					"ready"    => [],
					"spare"    => [],
					"supplier" => [],
				];

				$sql = "
					SELECT
						pop.supplier_id,
						COALESCE(s.name, '') AS supplier_name,
						SUM(CASE WHEN pop.product_type = 'spare' THEN pop.quantity ELSE 0 END) AS spare_qty,
						SUM(CASE WHEN pop.product_type = 'spare' THEN 0 ELSE pop.quantity END) AS ready_qty
					FROM purchase_order_product pop
					LEFT JOIN supplier s ON s.id = pop.supplier_id
					WHERE pop.parent_id = '$id'
					GROUP BY pop.supplier_id, s.name
					ORDER BY pop.supplier_id ASC
				";

				$rows = $this->db->query($sql)->result_array();
				foreach ($rows as $r) {
					$po['ready'][]    = $r['ready_qty'];
					$po['spare'][]    = $r['spare_qty'];
					$po['supplier'][] = $r['supplier_name'];
				}

				// Priority and Loading List
				$priority_loading = [
					"pl_ready"    			=> [],
					"pl_spare"    			=> [],

					"lo_loading_qty"		=> [],
					"lo_official_qty"		=> [],

					"lo_total_rmb"			=> [],
					"lo_total_usd"			=> [],

					"supplier" 					=> [],
				];

				$sql = "
					SELECT
						pop.supplier_id,
						COALESCE(s.name, '') AS supplier_name,
						SUM(CASE WHEN pop.product_type = 'spare' THEN pop.quantity ELSE 0 END) AS pl_spare_qty,
						SUM(CASE WHEN pop.product_type = 'spare' THEN 0 ELSE pop.quantity END) AS pl_ready_qty,
						SUM(pop.loading_qty) AS lo_loading_qty,
						SUM(pop.official_ci_qty) AS lo_official_qty,
						SUM(pop.total_amount_rmb) AS lo_total_rmb,
						SUM(pop.total_amount_usd) AS lo_total_usd
					FROM po_products pop
					LEFT JOIN supplier s ON s.id = pop.supplier_id
					WHERE pop.parent_id = '$id'
					GROUP BY pop.supplier_id, s.name
					ORDER BY pop.supplier_id ASC
				";

				$rows = $this->db->query($sql)->result_array();
				foreach ($rows as $r) {
					$priority_loading['pl_ready'][]    = $r['pl_ready_qty'];
					$priority_loading['pl_spare'][]    = $r['pl_spare_qty'];
					$priority_loading['supplier'][] = $r['supplier_name'];
					$priority_loading['lo_loading_qty'][] = $r['lo_loading_qty'];
					$priority_loading['lo_official_qty'][] = $r['lo_official_qty'];
					$priority_loading['lo_total_rmb'][] = number_format($r['lo_total_rmb'], 2);
					$priority_loading['lo_total_usd'][] = number_format($r['lo_total_usd'], 2);
				}

				$status = '';
				if ($delivery_status == 'pending') {
					$status = '<span class="badge badge-danger">Pending</span>';
				} else if ($delivery_status == 'priority') {
					$status = '<span class="badge badge-warning">Priority</span>';
				} else if ($delivery_status == 'loading') {
					$status = '<span class="badge badge-info">Loading List</span>';
				} else if ($delivery_status == 'complete') {
					$status = '<span class="badge badge-success">Complete</span>';
				} else if ($delivery_status == 'purchase_in') {
					$status = '<span class="badge badge-success">Purchase In</span>';
				}

				// PO Action
				$action ='-';
				$export_excel_url="generate_excel('".$id."')";
				$view_po_details_url = "showLargeModal('" . base_url() . "modal/popup_inventory/modal_purchase_order_details/" . $id . "','PO Details - " . $item['voucher_no'] . "')";
				$delete_po_url = "confirm_modal('" . base_url() . "inventory/purchase_order/delete/" . $id . "','Are you sure want to delete!')";
				$priority_list_url = "showLargeModal('" . base_url() . "modal/popup_inventory/purchase_order_priority_list_modal/" . $id . "','Priority List')";
				$edit_po_url = base_url() . "inventory/purchase-order/edit-import/" . $id;
				if ($delivery_status == 'pending') {
					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $export_excel_url . '"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export PO</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $priority_list_url . '"><i class="fa fa-list-ul" aria-hidden="true"></i> Priority List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_po_details_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View PO Details</a>
							<a class="dropdown-item" href="' . $edit_po_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit PO</a>
							<a class="dropdown-item" href="javascript:void(0)" onclick="' . $delete_po_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete PO</a>
						</div>
					</div>';
				} else {
					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $export_excel_url . '"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export PO</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_po_details_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View PO Details</a>
						</div>
					</div>';
				}

				// Priority List Action
				$priority_list_action ='-';
				$export_priority_list_excel_url="generate_excel('".$id."')";
				$loading_list_url = "showLargeModal('" . base_url() . "modal/popup_inventory/purchase_order_loading_list_modal/" . $id . "','Loading List')";
				$priority_list_view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/purchase_order_priority_list_view_modal/" . $id . "','View Priority List')";
				$priority_list_edit_url = "showLargeModal('" . base_url() . "modal/popup_inventory/purchase_order_priority_list_edit_modal/" . $id . "','Edit Priority List')";
				$delete_priority_list_url = "confirm_modal('" . base_url() . "inventory/purchase_order/delete_priority_list/" . $id . "','Are you sure want to delete the priority list!')";
				if ($delivery_status == 'priority') {
					$priority_list_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $export_priority_list_excel_url . '"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Priority List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $loading_list_url . '"><i class="fa fa-list-ul" aria-hidden="true"></i> Loading List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $priority_list_edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit Priority List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $priority_list_view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Priority List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $delete_priority_list_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete Priority List</a>
						</div>
					</div>';
				} else {
					$priority_list_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $export_priority_list_excel_url . '"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Priority List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $priority_list_view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Priority List</a>
						</div>
					</div>';
				}

				// Loading List Action
				$loading_list_action ='-';
				$loading_list_edit_url = "showLargeModal('" . base_url() . "modal/popup_inventory/purchase_order_loading_list_edit_modal/" . $id . "','Edit Loading List')";
				$loading_list_view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/purchase_order_loading_list_view_modal/" . $id . "','View Loading List')";
				$delete_loading_list_url = "confirm_modal('" . base_url() . "inventory/purchase_order/delete_loading_list/" . $id . "','Are you sure want to delete the loading list!')";
				$move_to_purchase_in_url = "confirm_modal('" . base_url() . "inventory/purchase_order/move_to_purchase_in/" . $id . "','Are you sure want to this PO to Purchase In & Customs!')";
				if ($delivery_status == 'loading') {
					$loading_list_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="' . base_url() . 'inventory/loading_list_po/download_invoice/' . $item['id'] . '" class="dropdown-item" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Invoice</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $loading_list_edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit Loading List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $loading_list_view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Loading List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $delete_loading_list_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete Loading List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $move_to_purchase_in_url . '"><i class="fa fa-check" aria-hidden="true"></i> Move to Purchase In</a>
						</div>
					</div>';
				} else {
					$loading_list_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="' . base_url() . 'inventory/loading_list_po/download_invoice/' . $item['id'] . '" class="dropdown-item" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Invoice</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $loading_list_view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Loading List</a>
						</div>
					</div>';
				}

				// Loading List Action
				$purchase_in_action ='-';
				$purchase_in_edit_url = "showLargeModal('" . base_url() . "modal/popup_inventory/po_purchase_in_modal/" . $id . "','Purchase In & Customs')";
				
				if ($delivery_status == 'purchase_in') {
					$purchase_in_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $purchase_in_edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i>Purchase In</a>
						</div>
					</div>';
				} else {
					$purchase_in_action ='';
				}

				$data[] = array(
					"sr_no"       						=> ++$start,
					"id"          						=> $item['id'],
					"date"       							=> date('d M, Y', strtotime($item['date'])) . ' - ' . $item['voucher_no'],
					"delivery_date"       		=> date('d M, Y', strtotime($delivery_date)),
					"suppliers"        				=> array_to_list($po['supplier']),
					"spare_parts_count"       => array_to_list($po['spare']),
					"ready_goods_count"       => array_to_list($po['ready']),
					"pl_suppliers"						=> array_to_list($priority_loading['supplier']),
					"pl_spare_parts_count"		=> array_to_list($priority_loading['pl_spare']),
					"pl_ready_goods_count"		=> array_to_list($priority_loading['pl_ready']),
					"loading_qty"							=> array_to_list($priority_loading['lo_loading_qty']),
					"official_qty"						=> array_to_list($priority_loading['lo_official_qty']),
					"total_rmb"								=> array_to_list($priority_loading['lo_total_rmb']),
					"total_usd"								=> array_to_list($priority_loading['lo_total_usd']),
					"status"        					=> $status,
					"action"      						=> $action,
					"priority_list_action"    => $priority_list_action,
					"loading_list_action"     => $loading_list_action,
					"purchase_in_action"      => $purchase_in_action,
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

	public function generate_priotity_purchase_order_excel($id)
	{
		$data = $this->db->query("
		SELECT p.notes, po.supplier_id, po.is_priority, po.item_code, po.categories, po.product_name, po.quantity, po.cbm, po.total_cbm FROM purchase_order as p INNER JOIN po_products as po ON p.id = po.parent_id WHERE p.id='$id' GROUP BY po.id ORDER BY po.is_priority DESC");

		$excel_data = [
			'priority' => [
				'title' => 'Priority List',
				'data' => []
			],
			'notes' => '',
			'loading_list' => [
				'title' => '2st Load List, If Space Left',
				'data' => []
			]
		];

		if ($data->num_rows() > 0) {
			foreach ($data->result_array() as $item) {
				$excel_data['notes'] = $item['notes'];

				if($item['is_priority'] == 1) {
					$excel_data['priority']['data'][] = [
						'product_name' => $item['product_name'],
						'model' => $item['item_code'],
						'qty' => $item['quantity'],
						'cbm' => $item['cbm'],
						'total_cbm' => $item['total_cbm'],
					];
				} else {
					$excel_data['loading_list']['data'][] = [
						'product_name' => $item['product_name'],
						'model' => $item['item_code'],
						'qty' => $item['quantity'],
						'cbm' => $item['cbm'],
						'total_cbm' => $item['total_cbm'],
					];
				}
			}

			// Generate Excel file Here
			// Initialize spreadsheet
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setTitle('Priority Purchase Order');
			
			// Style constants
			$alignCenter = Alignment::HORIZONTAL_CENTER;
			$alignLeft = Alignment::HORIZONTAL_LEFT;
			$alignRight = Alignment::HORIZONTAL_RIGHT;
			$alignVerticalCenter = Alignment::VERTICAL_CENTER;
			$borderThin = Border::BORDER_THIN;
			$fillSolid = Fill::FILL_SOLID;
			
			// Column definitions
			$columns = ['A', 'B', 'C', 'D', 'E', 'F'];
			$columnWidths = ['A' => 10, 'B' => 30, 'C' => 20, 'D' => 12, 'E' => 12, 'F' => 15];
			
			$currentRow = 1;
			
			// Header style
			$headerStyle = [
				'font' => ['bold' => true, 'size' => 11],
				'alignment' => ['horizontal' => $alignCenter, 'vertical' => $alignVerticalCenter],
				'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
				'fill' => ['fillType' => $fillSolid, 'startColor' => ['rgb' => 'E0E0E0']]
			];
			
			// Product row style
			$productStyle = [
				'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
				'alignment' => ['horizontal' => $alignLeft, 'vertical' => $alignVerticalCenter]
			];
			
			// Total row style
			$totalStyle = [
				'font' => ['bold' => true],
				'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
				'alignment' => ['horizontal' => $alignRight, 'vertical' => $alignVerticalCenter]
			];
			
			// Title style
			$titleStyle = [
				'font' => ['bold' => true, 'size' => 14],
				'alignment' => ['horizontal' => $alignCenter, 'vertical' => $alignVerticalCenter],
				'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
				'fill' => ['fillType' => $fillSolid, 'startColor' => ['rgb' => 'D3D3D3']]
			];
			
			// Notes style
			$notesStyle = [
				'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
				'alignment' => ['horizontal' => $alignLeft, 'vertical' => Alignment::VERTICAL_TOP],
				'wrapText' => true
			];
			
			// Priority List Section
			$sheet->setCellValue('A' . $currentRow, $excel_data['priority']['title']);
			$sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
			$sheet->getStyle('A' . $currentRow)->applyFromArray($titleStyle);
			$currentRow++;
			
			// Priority List Header Row
			$headers = ['Sr No.', 'Product', 'Model', 'Quantity', 'CBM', 'Total CBM'];
			foreach ($headers as $index => $header) {
				$sheet->setCellValue($columns[$index] . $currentRow, $header);
			}
			$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($headerStyle);
			$currentRow++;
			
			// Priority List Product Rows
			$srNo = 1;
			$priorityQty = 0;
			$priorityCbm = 0;
			$priorityTotalCbm = 0;
			
			foreach ($excel_data['priority']['data'] as $product) {
				$sheet->setCellValue('A' . $currentRow, $srNo);
				$sheet->setCellValue('B' . $currentRow, $product['product_name']);
				$sheet->setCellValue('C' . $currentRow, $product['model']);
				$sheet->setCellValue('D' . $currentRow, $product['qty']);
				$sheet->setCellValue('E' . $currentRow, $product['cbm']);
				$sheet->setCellValue('F' . $currentRow, $product['total_cbm']);
				
				$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($productStyle);
				$sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal($alignCenter);
				$sheet->getStyle('D' . $currentRow . ':F' . $currentRow)->getAlignment()->setHorizontal($alignRight);
				
				$priorityQty += $product['qty'];
				$priorityCbm += $product['cbm'];
				$priorityTotalCbm += $product['total_cbm'];
				
				$srNo++;
				$currentRow++;
			}
			
			// Priority List Total Row
			$sheet->setCellValue('A' . $currentRow, 'Total');
			$sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
			$sheet->setCellValue('D' . $currentRow, $priorityQty);
			$sheet->setCellValue('E' . $currentRow, $priorityCbm);
			$sheet->setCellValue('F' . $currentRow, $priorityTotalCbm);
			$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($totalStyle);
			$sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal($alignLeft);
			$currentRow++;
			
			// Notes Section
			$currentRow++; // Add spacing
			$sheet->setCellValue('A' . $currentRow, 'Notes');
			$sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
			$sheet->getStyle('A' . $currentRow)->applyFromArray($titleStyle);
			$currentRow++;
			
			$notesText = !empty($excel_data['notes']) ? strip_tags($excel_data['notes']) : '';
			$sheet->setCellValue('A' . $currentRow, $notesText);
			$sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
			$sheet->getStyle('A' . $currentRow)->applyFromArray($notesStyle);
			$sheet->getRowDimension($currentRow)->setRowHeight(-1); // Auto height
			$currentRow++;
			
			// Loading List Section
			$currentRow++; // Add spacing
			$sheet->setCellValue('A' . $currentRow, $excel_data['loading_list']['title']);
			$sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
			$sheet->getStyle('A' . $currentRow)->applyFromArray($titleStyle);
			$currentRow++;
			
			// Loading List Header Row
			foreach ($headers as $index => $header) {
				$sheet->setCellValue($columns[$index] . $currentRow, $header);
			}
			$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($headerStyle);
			$currentRow++;
			
			// Loading List Product Rows
			$srNo = 1;
			$loadingQty = 0;
			$loadingCbm = 0;
			$loadingTotalCbm = 0;
			
			foreach ($excel_data['loading_list']['data'] as $product) {
				$sheet->setCellValue('A' . $currentRow, $srNo);
				$sheet->setCellValue('B' . $currentRow, $product['product_name']);
				$sheet->setCellValue('C' . $currentRow, $product['model']);
				$sheet->setCellValue('D' . $currentRow, $product['qty']);
				$sheet->setCellValue('E' . $currentRow, $product['cbm']);
				$sheet->setCellValue('F' . $currentRow, $product['total_cbm']);
				
				$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($productStyle);
				$sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal($alignCenter);
				$sheet->getStyle('D' . $currentRow . ':F' . $currentRow)->getAlignment()->setHorizontal($alignRight);
				
				$loadingQty += $product['qty'];
				$loadingCbm += $product['cbm'];
				$loadingTotalCbm += $product['total_cbm'];
				
				$srNo++;
				$currentRow++;
			}
			
			// Loading List Total Row
			$sheet->setCellValue('A' . $currentRow, 'Total');
			$sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
			$sheet->setCellValue('D' . $currentRow, $loadingQty);
			$sheet->setCellValue('E' . $currentRow, $loadingCbm);
			$sheet->setCellValue('F' . $currentRow, $loadingTotalCbm);
			$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($totalStyle);
			$sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal($alignLeft);
			
			// Set column widths
			foreach ($columnWidths as $column => $width) {
				$sheet->getColumnDimension($column)->setWidth($width);
			}
			
			// Generate filename
			$filename = 'Priority_PO_' . date('Y-m-d') . '.xlsx';
			
			// Save and download file
			$spreadsheet->setActiveSheetIndex(0);
			$writer = new Xlsx($spreadsheet);
			$filePath = FCPATH . 'assets/' . $filename;
			$writer->save($filePath);
			
			// Download the file
			$this->load->helper('download');
			if (file_exists($filePath)) {
				$fileData = file_get_contents($filePath);
				force_download($filename, $fileData);
				@unlink($filePath); // Clean up
			} else {
				echo json_encode(['status' => 400, 'message' => 'Error generating Excel file', 'data' => []]);
			}
			
		} else {
			echo json_encode(['status' => 400, 'message' => 'No data found', 'data' => []]);
		}
	}

	public function generate_purchase_order_excel($id)
	{
		$data = $this->db->query("
		SELECT p.voucher_no, p.total_cbm, po.supplier_id, po.categories, po.product_name, po.quantity, po.cbm, po.total_cbm FROM purchase_order as p INNER JOIN purchase_order_product as po ON p.id = po.parent_id WHERE p.id='$id' GROUP BY po.supplier_id ORDER BY po.supplier_id ASC");

		$excel_data = [
			'qty' => 0,
			'cbm' => 0,
			'total_cbm' => 0,
			'data' => []
		];

		if ($data->num_rows() > 0) {
			// Prepare data for Excel
			foreach ($data->result_array() as $item) {
				$supplier_name = $this->common_model->selectByidParam($item['supplier_id'], 'supplier', 'name');
				$products = $this->db->query("SELECT categories, item_code, product_name, quantity, cbm, total_cbm FROM purchase_order_product WHERE parent_id='$id' AND supplier_id='" . $item['supplier_id'] . "' ORDER BY id ASC")->result_array();

				$supp_data = [];
				foreach ($products as $product) {
					$excel_data['qty'] += $product['quantity'];
					$excel_data['cbm'] += $product['cbm'];
					$excel_data['total_cbm'] += $product['total_cbm'];

					$supp_data[] = [
						'product_name' => $product['product_name'],
						'item_code' => $product['item_code'],
						'quantity' => $product['quantity'],
						'cbm' => $product['cbm'],
						'total_cbm' => $product['total_cbm'],
					];
				}

				$excel_data['data'][] = [
					'voucher_no' => $item['voucher_no'],
					'supplier_name' => $supplier_name,
					'products' => $supp_data,
				];
			}

			// Generate Excel file
			$this->_generate_excel_file($excel_data);
			
		} else {
			echo json_encode(['status' => 400, 'message' => 'No data found', 'data' => []]);
		}
	}

	/**
	 * Generate Excel file from purchase order data
	 * @param array $excel_data Data array containing supplier and product information
	 */
	private function _generate_excel_file($excel_data)
	{
		// Initialize spreadsheet
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Purchase Order');
		
		// Style constants for better readability
		$alignCenter = Alignment::HORIZONTAL_CENTER;
		$alignLeft = Alignment::HORIZONTAL_LEFT;
		$alignRight = Alignment::HORIZONTAL_RIGHT;
		$alignVerticalCenter = Alignment::VERTICAL_CENTER;
		$borderThin = Border::BORDER_THIN;
		$borderMedium = Border::BORDER_MEDIUM;
		$fillSolid = Fill::FILL_SOLID;
		
		// Column definitions
		$columns = ['A', 'B', 'C', 'D', 'E', 'F'];
		$columnWidths = ['A' => 10, 'B' => 30, 'C' => 20, 'D' => 12, 'E' => 12, 'F' => 15];
		
		$currentRow = 1;
		
		// Process each supplier
		foreach ($excel_data['data'] as $supplierData) {
			// Supplier Name Row
			$cellRef = 'A' . $currentRow;
			$sheet->setCellValue($cellRef, $supplierData['supplier_name']);
			$sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
			$sheet->getStyle($cellRef)->getFont()->setBold(true)->setSize(14);
			$sheet->getStyle($cellRef)->getAlignment()->setHorizontal($alignCenter);
			$currentRow++;
			
			// Batch No Row
			$cellRef = 'A' . $currentRow;
			$sheet->setCellValue($cellRef, $supplierData['voucher_no']);
			$sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
			$sheet->getStyle($cellRef)->getFont()->setBold(true)->setSize(14);
			$sheet->getStyle($cellRef)->getAlignment()->setHorizontal($alignCenter);
			$currentRow++;
			
			// Header Row
			$headers = ['Sr No.', 'Product', 'Model', 'Quantity', 'CBM', 'Total CBM'];
			foreach ($headers as $index => $header) {
				$sheet->setCellValue($columns[$index] . $currentRow, $header);
			}
			
			// Apply header style
			$headerStyle = [
				'font' => ['bold' => true, 'size' => 11],
				'alignment' => ['horizontal' => $alignCenter, 'vertical' => $alignVerticalCenter],
				'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
				'fill' => ['fillType' => $fillSolid, 'startColor' => ['rgb' => 'E0E0E0']]
			];
			$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($headerStyle);
			$currentRow++;
			
			// Product rows
			$srNo = 1;
			$supplierQty = 0;
			$supplierCbm = 0;
			$supplierTotalCbm = 0;
			
			foreach ($supplierData['products'] as $product) {
				$sheet->setCellValue('A' . $currentRow, $srNo);
				$sheet->setCellValue('B' . $currentRow, $product['product_name']);
				$sheet->setCellValue('C' . $currentRow, $product['item_code']);
				$sheet->setCellValue('D' . $currentRow, $product['quantity']);
				$sheet->setCellValue('E' . $currentRow, $product['cbm']);
				$sheet->setCellValue('F' . $currentRow, $product['total_cbm']);
				
				// Apply product row style
				$productStyle = [
					'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
					'alignment' => ['horizontal' => $alignLeft, 'vertical' => $alignVerticalCenter]
				];
				$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($productStyle);
				$sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal($alignCenter);
				$sheet->getStyle('D' . $currentRow . ':F' . $currentRow)->getAlignment()->setHorizontal($alignRight);
				
				$supplierQty += $product['quantity'];
				$supplierCbm += $product['cbm'];
				$supplierTotalCbm += $product['total_cbm'];
				
				$srNo++;
				$currentRow++;
			}
			
			// Supplier Total Row
			$sheet->setCellValue('A' . $currentRow, 'Total');
			$sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
			$sheet->setCellValue('D' . $currentRow, $supplierQty);
			$sheet->setCellValue('E' . $currentRow, $supplierCbm);
			$sheet->setCellValue('F' . $currentRow, $supplierTotalCbm);
			
			// Apply total row style
			$totalStyle = [
				'font' => ['bold' => true],
				'borders' => ['allBorders' => ['borderStyle' => $borderThin]],
				'alignment' => ['horizontal' => $alignRight, 'vertical' => $alignVerticalCenter]
			];
			$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($totalStyle);
			$sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal($alignLeft);
			$currentRow += 2; // Spacing between suppliers
		}
		
		// Grand Total Row
		$sheet->setCellValue('A' . $currentRow, 'Grand Total');
		$sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
		$sheet->setCellValue('D' . $currentRow, $excel_data['qty']);
		$sheet->setCellValue('E' . $currentRow, $excel_data['cbm']);
		$sheet->setCellValue('F' . $currentRow, $excel_data['total_cbm']);
		
		// Apply grand total style
		$grandTotalStyle = [
			'font' => ['bold' => true, 'size' => 12],
			'borders' => ['allBorders' => ['borderStyle' => $borderMedium]],
			'alignment' => ['horizontal' => $alignRight, 'vertical' => $alignVerticalCenter],
			'fill' => ['fillType' => $fillSolid, 'startColor' => ['rgb' => 'D3D3D3']]
		];
		$sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($grandTotalStyle);
		$sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal($alignLeft);
		
		// Set column widths
		foreach ($columnWidths as $column => $width) {
			$sheet->getColumnDimension($column)->setWidth($width);
		}
		
		// Generate filename
		$voucherNo = !empty($excel_data['data']) ? $excel_data['data'][0]['voucher_no'] : 'PO';
		$sanitizedVoucherNo = preg_replace('/[\/\\\\:*?"<>|]/', '_', $voucherNo);
		$filename = 'PO_' . $sanitizedVoucherNo . '_' . date('Y-m-d') . '.xlsx';
		
		// Save and download file
		$spreadsheet->setActiveSheetIndex(0);
		$writer = new Xlsx($spreadsheet);
		$filePath = FCPATH . 'assets/' . $filename;
		$writer->save($filePath);
		
		// Download the file
		$this->load->helper('download');
		if (file_exists($filePath)) {
			$fileData = file_get_contents($filePath);
			force_download($filename, $fileData);
			@unlink($filePath); // Clean up
		} else {
			echo json_encode(['status' => 400, 'message' => 'Error generating Excel file', 'data' => []]);
		}
	}

	public function get_purchase_order_entry()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (ih.voucher_no like '%" . $keyword . "%' OR ih.supplier_name like '%" . $keyword . "%' OR ih.warehouse_name like '%" . $keyword . "%')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(ih.received_date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(ih.received_date) BETWEEN '$from' AND '$to')";
			}
		}

		$total_count = $this->db->query("SELECT ih.id FROM inventory_history as ih
		INNER JOIN purchase_order as o ON o.id = ih.order_id
		WHERE (ih.status='in') and (o.is_deleted='0') $keyword_filter group by ih.order_id ORDER BY ih.id DESC")->num_rows();
		$query = $this->db->query("SELECT ih.id,ih.order_id,ih.warehouse_name,ih.item_code,SUM(ih.quantity) as quantity,SUM(ih.received_amount) as received_amount,ih.received_date,ih.invoice_no
		FROM inventory_history as ih
		INNER JOIN purchase_order as o ON o.id = ih.order_id
		WHERE (ih.status='in') and (o.is_deleted='0') $keyword_filter group by ih.order_id ORDER BY ih.id DESC LIMIT $start, $length");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$order_id = $item['order_id'];
				$view_url = base_url() . 'inventory/purchase-order-entry/view/' . $order_id;
				$action = '<a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
				$product_count = $this->db->query("SELECT id FROM purchase_order_product WHERE (parent_id='$order_id')")->num_rows();
				$supplier_name = $this->common_model->selectByidParam($order_id, 'purchase_order', 'supplier_name');

				$data[] = array(
					"sr_no"       		=> ++$start,
					"date"       		=> date('d M, Y', strtotime($item['received_date'])),
					"product_name"      => $product_count,
					"warehouse_name"    => $item['warehouse_name'],
					"received_amount"   => $item['received_amount'],
					"quantity"        	=> $item['quantity'],
					"supplier_name"        	=> $supplier_name,
					"po_no"        		=> $this->common_model->selectByidParam($order_id, 'purchase_order', 'voucher_no'),
					"invoice_no"        => ($item['invoice_no'] != '' && $item['invoice_no'] != null) ? $item['invoice_no'] : '-',
					"action"        	=> $action,
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

	public function get_purchase_reports()
{
    $params['draw'] = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];

    $filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
    $date_range = isset($_REQUEST['date_range']) ? $_REQUEST['date_range'] : '';
    $supplier_id = isset($_REQUEST['supplier_id']) ? $_REQUEST['supplier_id'] : '';

    $data = array();
    $keyword_filter = "";
    $date_filter = "";
    $supplier_filter = "";
    $is_date_filtered = false;

    if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
        $keyword = $filter_data['keywords'];
        $keyword_filter = " AND (po.supplier_name LIKE '%" . $keyword . "%' 
                      OR pop.item_code LIKE '%" . $keyword . "%'
                      OR pop.item_name LIKE '%" . $keyword . "%')";
    }

    if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
        $is_date_filtered = true;
        $added_date = explode(' - ', $_REQUEST['date_range']);
        $from =  date('Y-m-d', strtotime($added_date[0]));
        $to =  date('Y-m-d', strtotime($added_date[1]));
        if ($from == $to) {
            $keyword_filter .= " AND (DATE(date) = '$from')";
        } else {
            $keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
        }
    }

    if (!empty($supplier_id)) {
        $supplier_filter = " AND po.supplier_id = '" . $supplier_id . "'";
    }

    $total_count = $this->db->query("
        SELECT pop.id 
        FROM purchase_order_product pop
        JOIN purchase_order po ON pop.parent_id = po.id
        WHERE po.is_deleted='0' 
        $keyword_filter  $supplier_filter
    ")->num_rows();

    // If date filter is applied, remove pagination limit
    $limit_clause = "";
    if (!$is_date_filtered) {
        $limit_clause = "LIMIT $start, $length";
    }

    $query = $this->db->query("
        SELECT 
            po.id,
            po.supplier_name,
            po.date,
            pop.item_code as sku,
            pop.rate as cp,
            pop.quantity,
            pop.gst,
            (pop.rate * pop.quantity) as amount,
            ((pop.rate * pop.quantity) * (1 + pop.gst/100)) as total_amount
        FROM purchase_order po
        JOIN purchase_order_product pop ON po.id = pop.parent_id
        WHERE po.is_deleted='0' 
        $keyword_filter  $supplier_filter
        ORDER BY po.date DESC, po.id DESC, pop.id ASC
        $limit_clause
    ");

    if (!empty($query)) {
        $sr_no = $start;
        foreach ($query->result_array() as $item) {
            $data[] = array(
                "sr_no" => ++$sr_no,
                "id" => $item['id'],
                "supplier_name" => $item['supplier_name'],
                "sku" => $item['sku'],
                "cp" => number_format($item['cp'], 2),
                "quantity" => $item['quantity'],
                "gst" => $item['gst'] . '%',
                "amount" => number_format($item['amount'], 2),
                "total_amount" => number_format($item['total_amount'], 2),
                "date" => date('d M, Y', strtotime($item['date'])),
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


	public function add_purchase_entry()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_entry_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		if ($_FILES['image']['name'] != "") {
			$fileName        = $_FILES['image']['name'];
			$tmp             = explode('.', $fileName);
			$fileExtension   = end($tmp);
			$uploadable_file = md5(uniqid(rand(), true)) . '.' . $fileExtension;

			$year      = date("Y");
			$month     = date("m");
			$day       = date("d");
			//The folder path for our file should be YYYY/MM/DD
			$directory2 = "uploads/purchase_entry/" . "$year/$month/$day/";
			if (!is_dir($directory2)) {
				mkdir($directory2, 0755, true);
			}

			$data['image'] = $directory2 . $uploadable_file;
			move_uploaded_file($_FILES['image']['tmp_name'], $directory2 . $uploadable_file);
		}

		$supplier_id = $this->input->post('supplier_id');
		$supplier_name = $this->common_model->selectByidParam($supplier_id, 'supplier', 'name');

		$data['supplier_id']      	= $supplier_id;
		$data['supplier_name']      = $supplier_name;
		$data['invoice_number']     = ($this->input->post('invoice_number'));
		$data['invoice_date']      	= ($this->input->post('invoice_date'));
		$data['invoice_amount']     = ($this->input->post('invoice_amount'));
		$data['added_by_id']        = $this->session->userdata('super_user_id');
		$data['added_by_name']      = $this->session->userdata('super_name');
		$data['added_date']   		= date("Y-m-d H:i:s");
		if ($this->db->insert('purchase_entry', $data)) {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('purchase_entry_added_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);
			$this->session->set_flashdata('flash_message', get_phrase('purchase_entry_added_successfully'));
		} else {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('something_went_wrong')
			);
			$this->session->set_flashdata('error_message', get_phrase('something_went_wrong'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_purchase_entry($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_entry_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('purchase_entry', $data);

		return simple_json_output($resultpost);
	}

	public function get_purchase_entry()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (supplier_name like '%" . $keyword . "%' OR invoice_number like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM purchase_entry WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, supplier_name,invoice_number,invoice_date,invoice_amount,image  FROM purchase_entry WHERE (is_deleted='0') $keyword_filter ORDER BY invoice_date DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/purchase_entry/delete/" . $id . "','Are you sure want to delete!')";

				$action = '';
				//  $action .='<a href="'.$edit_url.'" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				//  <a href="#" onclick="'.$delete_url.'" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				//  '; 
				$action .= '
			 <a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
             ';

				$image = $item['image'];
				if ($image != null && $image != '') {
					$image = base_url() . $image;
					$img_url = '<a href="' . $image . '" target="_blank">View File</a>';
				} else {
					$img_url = '-';
				}


				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"img_url"          => $img_url,
					"supplier_name"        => $item['supplier_name'],
					"invoice_number"        => $item['invoice_number'],
					"invoice_date"        => date('d M, Y', strtotime($item['invoice_date'])),
					"invoice_amount"        => $item['invoice_amount'],
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

	public function get_purchase_order_entry_history($id)
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (item_code like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM inventory_history Where order_id = '$id'  and status='in'  $keyword_filter")->num_rows();
		// 		$query = $this->db->query("SELECT received_date as date,invoice_no,product_id,product_name,item_code,size_name,SUM(quantity) as quantity,SUM(received_amount) as received_amount FROM inventory_history Where order_id = '$id' and status='in' $keyword_filter group by item_code ORDER BY product_id desc");
		$query = $this->db->query("SELECT received_date as date,invoice_no,product_id,product_name,item_code,size_name,quantity,received_amount FROM inventory_history Where order_id = '$id' and status='in' $keyword_filter ORDER BY product_id desc");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$product_id = $item['product_id'];
				// $query_1 = $this->db->query("SELECT SUM(quantity) as quantity,SUM(received_amount) as received_amount FROM inventory_history Where order_id = '$id' and status='in' and product_id ='$product_id'  group by product_id ORDER BY  product_id desc limit 1")->row_array();
				$model_no = $this->common_model->selectByidParam($product_id, 'raw_products', 'item_code');
				$quantity = $item['quantity'];
				$received_amount = $item['received_amount'];

				$data[] = array(
					"sr_no"       => ++$start,
					"invoice_no"        => $item['invoice_no'],
					"product_id"        		=> $item['product_id'],
					"product_name"        		=> $model_no . ' - ' . $item['product_name'],
					"item_code"        		=> $item['size_name'],
					"product_qty"        => $item['quantity'],
					"basic_amount"        => $item['received_amount'],
					"total_qty"        => $quantity,
					"total_amt"        => $received_amount,
					// 	"total_qty"        => $query_1['quantity'],
					// 	"total_amt"        => $query_1['received_amount'],
					"date"        => date('d M, Y', strtotime($item['date'])),
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



	public function get_puchase_order_details($id)
	{
		// date("Y-m-d H:i:s");
		$year  = current_year();
		$result_data = array();
		$query = $this->db->query("SELECT company_id,company_name,voucher_no,refrence_no,date,supplier_id,warehouse_id,billing_address,delivery_address,mode_of_payment,dispatch,destination,other_refrence,terms_of_delivery,gst_type,cgst_amount,sgst_amount,igst_amount,net_sales_value_1,net_sales_value_2,transport_charge,transport_gst_type,transport_gst,transport_gst_amount,other_charges_name,other_charges_amount,round_of,grand_total FROM purchase_order WHERE id='$id' LIMIT 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$query_1 = $this->db->query("SELECT product_name,cartoon,hsn_code,item_code,basic_amount,gst,gst_amount,quantity,rate,unit,total_val FROM purchase_order_product WHERE parent_id='$id' order by id");
			$sr_no = 1;
			foreach ($query_1->result_array() as $item) {
				$product[] = array(
					"sr_no" => $sr_no,
					"name" => $item['item_code'] . ' - ' . $item['product_name'],
					"hsn_code" => $item['hsn_code'],
					"item_code" => $item['item_code'],
					"quantity" => $item['quantity'],
					"rate" => $item['rate'],
					"basic_amount" => $item['basic_amount'],
					"gst" => $item['gst'],
					"gst_amount" => $item['gst_amount'],
					"cartoon" => $item['cartoon'],
					"unit" => $item['unit'],
					"total_val" => $item['total_val'],
				);
				$sr_no++;
			}

			$check = 15 - count($product);
			for ($i = 1; $i <= $check; $i++) {
				$product[] = array(
					"sr_no" => '',
					"name" => '',
					"hsn_code" => '',
					"quantity" => '',
					"rate" => '',
					"basic_amount" => '',
					"gst" => '',
					"gst_amount" => '',
					"unit" => '',
					"total_val" => '&nbsp;',
				);
			}

			$supplier = $this->inventory_model->get_supplier_by_id($row['supplier_id'])->row_array();
			$warehouse = $this->inventory_model->get_warehouse_by_id($row['warehouse_id'])->row_array();

			$company = $this->common_model->getRowById('company', 'gst_name,address,address_2,address_3,city_name,pincode,state_name,state_code,gst_no', array('id' => $row['company_id']));
			$company_name = $company['gst_name'];
			$company_address = $company['address'] . ', ' . $company['address_2'] . ', ' . $company['address_3'] . ', ' . $company['city_name'] . ' - ' . $company['pincode'];
			$company_state = $company['state_name'];
			$company_gst_no = $company['gst_no'];
			$company_state_code = $company['state_code'];

			$company_name = ($company_name != '' && $company_name != null) ? $company_name : 'KIDSISLAND';
			$company_address = ($company_address != '' && $company_address != null) ? $company_address : '2ND FLOOR, SHOP NO.406, SUPER SHOPPING COMPLEX, 60/68 SARANG STREET, MANDVI, MUMBAI 400003';
			$company_state = ($company_state != '' && $company_state != null) ? $company_state : 'Maharashtra';
			$company_gst_no = ($company_gst_no != '' && $company_gst_no != null) ? $company_gst_no : '27AAHFF0163A1Z0';
			$company_state_code = ($company_state_code != '' && $company_state_code != null) ? $company_state_code : '27';

			$result_data = array(
				"company_name" => $company_name,
				"company_address" => $company_address,
				"company_state" => $company_state,
				"company_gst_no" => $company_gst_no,
				"company_state_code" => $company_state_code,
				"voucher_no" => $row['voucher_no'],
				"date" => date('d-M-Y', strtotime($row['date'])),
				"refrence_no" => ($row['refrence_no'] != '' && $row['refrence_no'] != null) ? $row['refrence_no'] : '&nbsp;',
				"mode_of_payment" => ($row['mode_of_payment'] != '' && $row['mode_of_payment'] != null) ? $row['mode_of_payment'] : '&nbsp;',
				"dispatch" => ($row['dispatch'] != '' && $row['dispatch'] != null) ? $row['dispatch'] : '&nbsp;',
				"destination" => ($row['destination'] != '' && $row['destination'] != null) ? $row['destination'] : '&nbsp;',
				"other_refrence" => ($row['other_refrence'] != '' && $row['other_refrence'] != null) ? $row['other_refrence'] : '&nbsp;',
				"terms_of_delivery" => ($row['terms_of_delivery'] != '' && $row['terms_of_delivery'] != null) ? $row['terms_of_delivery'] : '&nbsp;',
				"gst_type" => $row['gst_type'],
				"cgst_amount" => $row['cgst_amount'],
				"sgst_amount" => $row['sgst_amount'],
				"igst_amount" => $row['igst_amount'],
				"net_sales_value_1" => $row['net_sales_value_1'],
				"net_sales_value_2" => $row['net_sales_value_2'],
				"transport_charge" => $row['transport_charge'],
				"transport_gst_type" => $row['transport_gst_type'],
				"transport_gst" => $row['transport_gst'],
				"transport_gst_amount" => $row['transport_gst_amount'],
				"other_charges_name" => $row['other_charges_name'],
				"other_charges_amount" => $row['other_charges_amount'],
				"round_of" => $row['round_of'],
				"grand_total" => $row['grand_total'],
				"warehouse_gst_name" => $warehouse['name'],
				"warehouse_gst_no" => $warehouse['gst_no'],
				"warehouse_state_name" => $warehouse['state_name'],
				"warehouse_state_code" => $warehouse['state_code'],
				"delivery_address" => $row['delivery_address'],
				"supplier_gst_name" => $supplier['name'],
				"supplier_gst_no" => $supplier['gst_no'],
				"supplier_state_name" => $supplier['state_name'],
				"supplier_state_code" => $supplier['state_code'],
				"billing_address" => $row['billing_address'],
				"product" => $product,
			);
		}
		return $result_data;
	}

	public function get_purchase_order_product($id)
	{
		$product = array();
		$query_1 = $this->db->query("SELECT id,product_id,hsn_code,product_name,quantity,rate,basic_amount,gst_amount,total_val,unit,pending,received,is_complete,sizes,group_id,color_id,color_name,categories FROM purchase_order_product WHERE parent_id='$id' order by id");
		foreach ($query_1->result_array() as $item) {
			$pending = intval($item['quantity']) - intval($item['received']);

			$product_id = $item['product_id'];
			$po_id = $id;

			// Getting inserted qty and amt
			$recieved_amt = 0;
			$recieved_qty = 0;
			$inventory_data = $this->db->query("SELECT SUM(quantity) as total_qty, SUM(received_amount) as total_amt FROM inventory_history WHERE order_id='$po_id' AND status='in' AND product_id='$product_id'");
			if ($inventory_data->num_rows() > 0) {
				$inventory_data = $inventory_data->row_array();
				$recieved_amt = $inventory_data["total_amt"];
				$recieved_qty = $inventory_data["total_qty"];
			}

			$query_prod = $this->db->query("SELECT is_variation,item_code FROM raw_products WHERE id='$product_id' limit 1");
			if ($query_prod->num_rows() > 0) {

				$row_prod = $query_prod->row_array();
				$is_variation = $row_prod['is_variation'];
				$item_code = $row_prod['item_code'];
				if ($is_variation == 0) {
					$product[] = array(
						"id" => $item['id'],
						"item_code" => $item_code,
						"is_variation" => $is_variation,
						"product_id" => $item['product_id'],
						"name" => $item['product_name'],
						"hsn_code" => $item['hsn_code'],
						"quantity" => $item['quantity'],
						"rate" => $item['rate'],
						"basic_amount" => $item['basic_amount'],
						"gst_amount" => $item['gst_amount'],
						"total_val" => $item['total_val'],
						"unit" => $item['unit'],
						"pending" => $pending,
						"received" => $item['received'],
						"is_complete" => $item['is_complete'],
						"recieved_amt" => $recieved_amt,
						"recieved_qty" => $recieved_qty,
						"variation_data" => [],
					);
				} else {
					$variation_data = array();
					$query_var = $this->db->query("SELECT id,name,sku_code,size_id,size_name FROM product_variation WHERE product_id='$product_id'");
					if ($query_var->num_rows() > 0) {
						foreach ($query_var->result_array() as $item_var) {
							$sku_code = $item_var['sku_code'];
							$product_name = $item_var['name'];

							$variation_data[] = array(
								"variation_id" => $item_var['id'],
								"size_id" => $item_var['size_id'],
								"size_name" => $item_var['size_name'],
								"variation_name" => $product_name,
								"item_code" => $sku_code,
							);
						}
					}

					$product[] = array(
						"id" => $item['id'],
						"item_code" => $item_code,
						"is_variation" => $is_variation,
						"product_id" => $item['product_id'],
						"name" => $item['product_name'],
						"hsn_code" => $item['hsn_code'],
						"quantity" => $item['quantity'],
						"rate" => $item['rate'],

						"sizes" => $item['sizes'],
						"group_id" => $item['group_id'],
						"color_id" => $item['color_id'],
						"color_name" => $item['color_name'],
						"categories" => $item['categories'],

						"basic_amount" => $item['basic_amount'],
						"gst_amount" => $item['gst_amount'],
						"total_val" => $item['total_val'],
						"unit" => $item['unit'],
						"pending" => $pending,
						"received" => $item['received'],
						"recieved_amt" => $recieved_amt,
						"recieved_qty" => $recieved_qty,
						"is_complete" => $item['is_complete'],
						"variation_data" => $variation_data,
					);
				}
			}
		}
		return $product;
	}

	public function purchase_order_received_data($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_order_mark_successfully'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$id = $this->input->post('id', true);
		$variation = $this->input->post('variation', true);
		$rcv_quantity = $this->input->post('received', true);
		$rcv_date = $this->input->post('received_date', true);
		$parent_id = $this->input->post('parent_id', true);
		$product_id = $this->input->post('product_id', true);
		$product_name = $this->input->post('name', true);
		$batch_no = $this->input->post('batch_no', true);
		$expiry_date = $this->input->post('expiry_date', true);
		$invoice_no = $this->input->post('invoice_no', true);
		$received_amount = $this->input->post('received_amount', true);
		$item_codes = $this->input->post('item_code', true);

		$sizes = $this->input->post('sizes', true);
		$group_id = $this->input->post('group_id', true);
		$color_id = $this->input->post('color_id', true);
		$color_name = $this->input->post('color_name', true);
		$categories = $this->input->post('categories', true);
		$size_name = $this->input->post('size_name', true);
		$size_id = $this->input->post('size_id', true);

		for ($i = 0; $i < count($id); $i++) {
			if ($rcv_quantity[$i] > 0) {
				$res_2 = $this->db->query("SELECT received,quantity FROM purchase_order_product WHERE id='$id[$i]'")->row_array();
				$warehouse_rcv_quantity = $res_2['received'];
				$warehouse_quantity = $res_2['quantity'];
				$is_complete = 0;
				$final_qty = intval($warehouse_rcv_quantity) + $rcv_quantity[$i];
				if ($warehouse_quantity == $final_qty) {
					$is_complete = 1;
				}

				$data_product = array(
					'received'       => $final_qty,
					'is_complete'       => $is_complete,
					'received_date'       => $rcv_date[$i],
				);
				$this->db->where('id', $id[$i]);
				$this->db->where('parent_id', $parent_id);
				$this->db->update('purchase_order_product', $data_product);

				$this->update_delivery_status($parent_id);

				$res = $this->db->query("SELECT warehouse_id,warehouse_name FROM purchase_order WHERE id='$parent_id'")->row_array();
				$warehouse_id = $res['warehouse_id'];
				$warehouse_name = $res['warehouse_name'];

				//$item_code = $this->common_model->selectByidParam($product_id[$i],'raw_products','item_code');
				$item_code = $variation[$i];

				$check = $this->db->query("SELECT id,quantity FROM inventory where product_id='" . $product_id[$i] . "' and warehouse_id='" . $warehouse_id . "' and size_id='" . $size_id[$i] . "'");
				if ($check->num_rows() > 0) {
					$check_row = $check->row_array();
					$check_quantity = $check_row['quantity'];
					$check_id = $check_row['id'];

					$final_quantity = intval($check_quantity) + $rcv_quantity[$i];

					$prod = array();
					$prod['quantity'] = $final_quantity;
					$this->db->where('id', $check_id);
					$this->db->update('inventory', $prod);

					$pro_de['order_id'] = $parent_id;
					$pro_de['parent_id'] = $check_id;
					$pro_de['warehouse_name'] = $warehouse_name;
					$pro_de['warehouse_id'] = $warehouse_id;
					$pro_de['product_id'] = $product_id[$i];
					$pro_de['product_name'] = $product_name[$i];
					$pro_de['group_id'] = $group_id[$i];
					$pro_de['color_id'] = $color_id[$i];
					$pro_de['color_name'] = $color_name[$i];
					$pro_de['categories'] = $categories[$i];
					$pro_de['size_name'] = $size_name[$i];
					$pro_de['size_id'] = $size_id[$i];
					$pro_de['sku'] = $item_code;
					$pro_de['item_code'] = $item_codes[$i];
					$pro_de['quantity']    = $rcv_quantity[$i];
					$pro_de['status'] 	   = 'in';
					$pro_de['received_date'] = $rcv_date[$i];
					$pro_de['batch_no'] = NULL;
					$pro_de['expiry_date'] = NULL;
					$pro_de['invoice_no'] = $invoice_no[$i];
					$pro_de['received_amount'] = $received_amount[$i];
					$pro_de['added_date']  = date("Y-m-d H:i:s");
					$pro_de['added_by_id']   = $this->session->userdata('super_user_id');
					$pro_de['added_by_name'] = $this->session->userdata('super_name');
					$this->db->insert('inventory_history', $pro_de);
				} else {
					$prod = array();
					$prod['warehouse_name'] = $warehouse_name;
					$prod['warehouse_id'] = $warehouse_id;
					$prod['product_id'] = $product_id[$i];
					$prod['product_name'] = $product_name[$i];
					$prod['group_id'] = $group_id[$i];
					$prod['color_id'] = $color_id[$i];
					$prod['color_name'] = $color_name[$i];
					$prod['categories'] = $categories[$i];
					$prod['size_name'] = $size_name[$i];
					$prod['size_id'] = $size_id[$i];
					$prod['sku'] = $item_code;
					$prod['item_code'] = $item_codes[$i];
					$prod['quantity'] = $rcv_quantity[$i];
					$prod['batch_no'] = NULL;
					$prod['expiry_date'] = NULL;
					$this->db->insert('inventory', $prod);
					$check_id = $this->db->insert_id();;

					$pro_de['order_id'] = $parent_id;
					$pro_de['parent_id'] = $check_id;
					$pro_de['warehouse_name'] = $warehouse_name;
					$pro_de['warehouse_id'] = $warehouse_id;
					$pro_de['product_id'] = $product_id[$i];
					$pro_de['product_name'] = $product_name[$i];
					$pro_de['group_id'] = $group_id[$i];
					$pro_de['color_id'] = $color_id[$i];
					$pro_de['color_name'] = $color_name[$i];
					$pro_de['categories'] = $categories[$i];
					$pro_de['size_name'] = $size_name[$i];
					$pro_de['size_id'] = $size_id[$i];
					$pro_de['sku'] = $item_code;
					$pro_de['item_code'] = $item_codes[$i];
					$pro_de['quantity']    = $rcv_quantity[$i];
					$pro_de['status'] 	   = 'in';
					$pro_de['received_date'] = $rcv_date[$i];
					$pro_de['batch_no'] = NULL;
					$pro_de['expiry_date'] = NULL;
					$pro_de['invoice_no'] = $invoice_no[$i];
					$pro_de['received_amount'] = $received_amount[$i];
					$pro_de['added_date']  = date("Y-m-d H:i:s");
					$pro_de['added_by_id']   = $this->session->userdata('super_user_id');
					$pro_de['added_by_name'] = $this->session->userdata('super_name');

					$this->db->insert('inventory_history', $pro_de);
				}
			}
		}
		$this->session->set_flashdata('flash_message', "Mark Delivery Update Successfully !!");
		return simple_json_output($resultpost);
	}

	public function update_delivery_status($id)
	{
		$check_1 = $this->db->query("SELECT id FROM purchase_order_product WHERE parent_id='$id' and is_complete='1'")->num_rows();
		$check_2 = $this->db->query("SELECT id FROM purchase_order_product WHERE parent_id='$id'")->num_rows();
		if ($check_1 == $check_2) {
			$data_product = array(
				'delivery_status' => 'complete',
			);
			$this->db->where('id', $id);
			$this->db->update('purchase_order', $data_product);
		}
	}


    public function get_stock_totals()
    {
        
        $keyword_filter = '';
        $total_count = $this->db->query("
            SELECT id
            FROM inventory
            WHERE (id<>'') $keyword_filter GROUP BY categories ORDER BY categories ASC
        ")->num_rows();
    
        $query = $this->db->query("
            SELECT id, SUM(quantity) as total_qty, categories
            FROM inventory
            WHERE (id<>'') $keyword_filter GROUP BY categories
            ORDER BY categories ASC
        ");
    
        $total_stock_qty = 0;
        $total_cp_price = 0;
        $total_gst_amt = 0;
        $grand_total = 0;
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $total_qty = $item['total_qty'];
                
                $category = $this->common_model->getRowById('categories', '*', ['id' => $item['categories']]);
                $category_name = $category['name'] ?? '-';
                
                $product = $this->db->query("SELECT product_id, SUM(quantity) as total_sub_qty FROM inventory WHERE categories='" . $item['categories'] . "' GROUP BY product_id");
                $cp_price = 0;
                $gst_amt = 0;
                $total = 0;
                if($product->num_rows() > 0) {
                    foreach($product->result_array() as $prod) {
                        $details = $this->common_model->getRowById('raw_products', '*', ['id' => $prod['product_id']]);
                        $d_cp_price = $details['costing_price'] ?? 0;
                        $d_gst_per = $details['gst'] ?? 0;
                        $cp_price += $d_cp_price * $prod['total_sub_qty'];
                        $gst_amt += (($d_cp_price * $d_gst_per) / 100) * $prod['total_sub_qty'];
                        $total += ($d_cp_price * $prod['total_sub_qty']) + ((($d_cp_price * $d_gst_per) / 100) * $prod['total_sub_qty']);
                    }
                }
                
                $total_stock_qty += $total_qty;
                $total_cp_price += $cp_price;
                $total_gst_amt += $gst_amt;
                $grand_total += $total;
            }
        }
        
        $data = array(
            "sr_no" => '-',
            "id" => 0,
            "pcs" => "Total",
            "qty" => $total_stock_qty,
            "amt" => number_format($total_cp_price, 2),
            "gst" => number_format($total_gst_amt, 2),
            "total" => number_format($grand_total, 2),
        );
        
        return $data;
        
    }
    
// 	public function get_stock_totals()
// 	{
// 		$totals_query = $this->db->query("SELECT SUM(i.quantity) as total_quantity, SUM((rp.costing_price + (rp.costing_price * rp.gst / 100)) * i.quantity) as total_amount
//         FROM inventory as i LEFT JOIN raw_products as rp ON rp.id = i.product_id
//         WHERE (i.id!='')");

// 		$total_quantity = 0;
// 		$total_amount = 0;
// 		if ($totals_query->num_rows() > 0) {
// 			$totals_result = $totals_query->row_array();
// 			$total_quantity = (int)$totals_result['total_quantity'];
// 			$total_amount = number_format((float)$totals_result['total_amount'], 2);
// 		}

// 		$result = array(
// 			"total_quantity" => $total_quantity,
// 			"total_amount" => $total_amount
// 		);

// 		return $result;
// 	}

	public function update_purchase_order_priority_list() {
		if ($this->session->userdata('inventory_login') != true) {
				echo json_encode(['status' => 400, 'message' => 'Unauthorized']);
				return;
		}

		$po_id = $this->input->post('po_id');
		$notes = $this->input->post('notes');

		if (empty($po_id)) {
				echo json_encode(['status' => 400, 'message' => 'Purchase Order ID is required']);
				return;
		}

		// Start transaction
		$this->db->trans_start();

		// Delete existing po_products records for this PO (to allow updates)
		$this->db->where('parent_id', $po_id);
		$this->db->delete('po_products');

		// Update purchase_order table: delivery_status and notes
		$this->db->where('id', $po_id);
		$this->db->update('purchase_order', [
				'delivery_status' => 'priority',
				'notes' => $notes,
				'priority_date' => date('Y-m-d H:i:s')
		]);

		// Get Priority List products (loading_list = 0)
		$product_ids = $this->input->post('product_id');
		$supplier_ids = $this->input->post('supplier_id');
		$product_types = $this->input->post('product_type');
		$product_names = $this->input->post('product_name');
		$item_codes = $this->input->post('item_code');
		$quantities = $this->input->post('quantity');
		$cbms = $this->input->post('cbm');
		$total_cbms = $this->input->post('total_cbm');
		$pending_po_qtys = $this->input->post('pending_po_qty');
		$loading_list_qtys = $this->input->post('loading_list_qty');
		$in_stock_qtys = $this->input->post('in_stock_qty');
		$company_stocks = $this->input->post('company_stock');
		$loading_lists = $this->input->post('loading_list'); // 0 for Priority List

		// Get Loading Products (loading_list = 1)
		$loading_product_ids = $this->input->post('loading_product_id');
		$loading_supplier_ids = $this->input->post('loading_supplier_id');
		$loading_product_types = $this->input->post('loading_product_type');
		$loading_product_names = $this->input->post('loading_product_name');
		$loading_item_codes = $this->input->post('loading_item_code');
		$loading_quantities = $this->input->post('loading_quantity');
		$loading_cbms = $this->input->post('loading_cbm');
		$loading_total_cbms = $this->input->post('loading_total_cbm');
		$loading_pending_po_qtys = $this->input->post('loading_pending_po_qty');
		$loading_loading_list_qtys = $this->input->post('loading_loading_list_qty');
		$loading_in_stock_qtys = $this->input->post('loading_in_stock_qty');
		$loading_company_stocks = $this->input->post('loading_company_stock');
		$loading_lists_loading = $this->input->post('loading_list'); // 1 for Loading Products

		// Process Priority List products (loading_list = 0, is_priority = 1)
		if (!empty($product_ids)) {
				foreach ($product_ids as $row_key => $product_id) {
						if (empty($product_id)) continue;

						$supplier_id = isset($supplier_ids[$row_key]) ? $supplier_ids[$row_key] : 0;
						$product_type = isset($product_types[$row_key]) ? $product_types[$row_key] : '';
						$product_name = isset($product_names[$row_key]) ? $product_names[$row_key] : '';
						$item_code = isset($item_codes[$row_key]) ? $item_codes[$row_key] : '';
						$quantity = isset($quantities[$row_key]) ? intval($quantities[$row_key]) : 0;
						
						// Skip products with 0 quantity
						if ($quantity <= 0) continue;
						$cbm = isset($cbms[$row_key]) ? floatval($cbms[$row_key]) : 0;
						$total_cbm = isset($total_cbms[$row_key]) ? floatval($total_cbms[$row_key]) : 0;
						$pending_po_qty = isset($pending_po_qtys[$row_key]) ? intval($pending_po_qtys[$row_key]) : 0;
						$loading_list_qty = isset($loading_list_qtys[$row_key]) ? intval($loading_list_qtys[$row_key]) : 0;
						$in_stock_qty = isset($in_stock_qtys[$row_key]) ? intval($in_stock_qtys[$row_key]) : 0;
						$company_stock = isset($company_stocks[$row_key]) ? intval($company_stocks[$row_key]) : 0;

						// Get original product data from purchase_order_product
						$original_product = $this->db->query("SELECT * FROM purchase_order_product WHERE parent_id = '$po_id' AND product_id = '$product_id' LIMIT 1")->row_array();
						
						// If not found in purchase_order_product, get from raw_products
						if (!$original_product) {
								$raw_product = $this->db->query("SELECT * FROM raw_products WHERE id = '$product_id' LIMIT 1")->row_array();
								if ($raw_product) {
										$original_product = [
												'categories' => $raw_product['categories'] ?? NULL,
												'sizes' => NULL,
												'group_id' => $raw_product['group_id'] ?? NULL,
												'color_id' => NULL,
												'color_name' => NULL,
												'hsn_code' => $raw_product['hsn_code'] ?? NULL,
												'unit' => $raw_product['unit'] ?? NULL,
												'cartoon' => $raw_product['cartoon_qty'] ?? 0,
												'rate' => $raw_product['product_mrp'] ?? 0,
												'basic_amount' => $raw_product['costing_price'] ?? 0,
												'discount' => 0,
												'discount_amount' => 0,
												'gst' => 0,
												'gst_amount' => 0,
												'total_val' => 0,
												'pending' => $quantity,
												'received' => 0,
												'received_date' => NULL,
												'invoice_no' => NULL,
												'is_complete' => 0
										];
								}
						}
						
						if ($original_product) {
								// Insert into po_products with is_priority = 1
								$po_product_data = [
										'parent_id' => $po_id,
										'supplier_id' => $supplier_id,
										'product_type' => $product_type,
										'product_id' => $product_id,
										'categories' => $original_product['categories'],
										'sizes' => $original_product['sizes'],
										'group_id' => $original_product['group_id'],
										'color_id' => $original_product['color_id'],
										'color_name' => $original_product['color_name'],
										'product_name' => $product_name,
										'hsn_code' => $original_product['hsn_code'],
										'item_code' => $item_code,
										'unit' => $original_product['unit'],
										'cbm' => $cbm,
										'total_cbm' => $total_cbm,
										'pending_po_qty' => $pending_po_qty,
										'loading_list_qty' => $loading_list_qty,
										'in_stock_qty' => $in_stock_qty,
										'current_company_qty' => $company_stock,
										'quantity' => $quantity,
										'cartoon' => $original_product['cartoon'],
										'rate' => $original_product['rate'],
										'basic_amount' => $original_product['basic_amount'],
										'discount' => $original_product['discount'],
										'discount_amount' => $original_product['discount_amount'],
										'gst' => $original_product['gst'],
										'gst_amount' => $original_product['gst_amount'],
										'total_val' => $original_product['total_val'],
										'pending' => $quantity,
										'received' => $original_product['received'],
										'received_date' => $original_product['received_date'],
										'invoice_no' => $original_product['invoice_no'],
										'is_priority' => 1, // Priority List products
										'is_complete' => $original_product['is_complete']
								];

								$this->db->insert('po_products', $po_product_data);
						}
				}
		}

		// Process Loading Products (loading_list = 1, is_priority = 0)
		if (!empty($loading_product_ids)) {
			foreach ($loading_product_ids as $row_key => $product_id) {
				if (empty($product_id)) continue;

				$supplier_id = isset($loading_supplier_ids[$row_key]) ? $loading_supplier_ids[$row_key] : 0;
				$product_type = isset($loading_product_types[$row_key]) ? $loading_product_types[$row_key] : '';
				$product_name = isset($loading_product_names[$row_key]) ? $loading_product_names[$row_key] : '';
				$item_code = isset($loading_item_codes[$row_key]) ? $loading_item_codes[$row_key] : '';
				$quantity = isset($loading_quantities[$row_key]) ? intval($loading_quantities[$row_key]) : 0;
				
				// Skip products with 0 quantity
				if ($quantity <= 0) continue;
				$cbm = isset($loading_cbms[$row_key]) ? floatval($loading_cbms[$row_key]) : 0;
				$total_cbm = isset($loading_total_cbms[$row_key]) ? floatval($loading_total_cbms[$row_key]) : 0;
				$pending_po_qty = isset($loading_pending_po_qtys[$row_key]) ? intval($loading_pending_po_qtys[$row_key]) : 0;
				$loading_list_qty = isset($loading_loading_list_qtys[$row_key]) ? intval($loading_loading_list_qtys[$row_key]) : 0;
				$in_stock_qty = isset($loading_in_stock_qtys[$row_key]) ? intval($loading_in_stock_qtys[$row_key]) : 0;
				$company_stock = isset($loading_company_stocks[$row_key]) ? intval($loading_company_stocks[$row_key]) : 0;

				// Get original product data from purchase_order_product
				$original_product = $this->db->query("SELECT * FROM purchase_order_product WHERE parent_id = '$po_id' AND product_id = '$product_id' LIMIT 1")->row_array();
				
				// If not found in purchase_order_product, get from raw_products
				if (!$original_product) {
					$raw_product = $this->db->query("SELECT * FROM raw_products WHERE id = '$product_id' LIMIT 1")->row_array();
					if ($raw_product) {
						$original_product = [
							'categories' => $raw_product['categories'] ?? NULL,
							'sizes' => NULL,
							'group_id' => $raw_product['group_id'] ?? NULL,
							'color_id' => NULL,
							'color_name' => NULL,
							'hsn_code' => $raw_product['hsn_code'] ?? NULL,
							'unit' => $raw_product['unit'] ?? NULL,
							'cartoon' => $raw_product['cartoon_qty'] ?? 0,
							'rate' => $raw_product['product_mrp'] ?? 0,
							'basic_amount' => $raw_product['costing_price'] ?? 0,
							'discount' => 0,
							'discount_amount' => 0,
							'gst' => 0,
							'gst_amount' => 0,
							'total_val' => 0,
							'pending' => $quantity,
							'received' => 0,
							'received_date' => NULL,
							'invoice_no' => NULL,
							'is_complete' => 0
						];
					}
				}
				
				if ($original_product) {
					// Insert into po_products with is_priority = 0
					$po_product_data = [
						'parent_id' => $po_id,
						'supplier_id' => $supplier_id,
						'product_type' => $product_type,
						'product_id' => $product_id,
						'categories' => $original_product['categories'],
						'sizes' => $original_product['sizes'],
						'group_id' => $original_product['group_id'],
						'color_id' => $original_product['color_id'],
						'color_name' => $original_product['color_name'],
						'product_name' => $product_name,
						'hsn_code' => $original_product['hsn_code'],
						'item_code' => $item_code,
						'unit' => $original_product['unit'],
						'cbm' => $cbm,
						'total_cbm' => $total_cbm,
						'pending_po_qty' => $pending_po_qty,
						'loading_list_qty' => $loading_list_qty,
						'in_stock_qty' => $in_stock_qty,
						'current_company_qty' => $company_stock,
						'quantity' => $quantity,
						'cartoon' => $original_product['cartoon'],
						'rate' => $original_product['rate'],
						'basic_amount' => $original_product['basic_amount'],
						'discount' => $original_product['discount'],
						'discount_amount' => $original_product['discount_amount'],
						'gst' => $original_product['gst'],
						'gst_amount' => $original_product['gst_amount'],
						'total_val' => $original_product['total_val'],
						'pending' => $quantity, // For Loading Products, set pending = quantity
						'received' => $original_product['received'],
						'received_date' => $original_product['received_date'],
						'invoice_no' => $original_product['invoice_no'],
						'is_priority' => 0, // Loading Products
						'is_complete' => $original_product['is_complete']
					];

					$this->db->insert('po_products', $po_product_data);
				}
			}
		}

		// Complete transaction
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			echo json_encode(['status' => 400, 'message' => 'Error updating priority list']);
		} else {
			echo json_encode(['status' => 200, 'message' => 'Priority list updated successfully!']);
		}
	}

	public function delete_priority_list($po_id) {
		if ($this->session->userdata('inventory_login') != true) {
			echo json_encode(['status' => 400, 'message' => 'Unauthorized']);
			return;
		}

		if (empty($po_id)) {
			echo json_encode(['status' => 400, 'message' => 'Purchase Order ID is required']);
			return;
		}

		// Start transaction
		$this->db->trans_start();

		// Delete existing po_products records for this PO
		$this->db->where('parent_id', $po_id);
		$this->db->delete('po_products');

		// Update purchase_order table: delivery_status to 'pending'
		$this->db->where('id', $po_id);
		$this->db->update('purchase_order', [
			'delivery_status' => 'pending'
		]);

		// Complete transaction
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$resultpost = [
				'status' => 400,
				'message' => 'Error deleting priority list',
				'url' => $this->session->userdata('previous_url')
			];
		} else {
			$resultpost = [
				'status' => 200,
				'message' => 'Priority list deleted successfully!',
				'url' => $this->session->userdata('previous_url')
			];
		}

		return simple_json_output($resultpost);
	}

	public function delete_loading_list($po_id) {
		if ($this->session->userdata('inventory_login') != true) {
			echo json_encode(['status' => 400, 'message' => 'Unauthorized']);
			return;
		}

		if (empty($po_id)) {
			echo json_encode(['status' => 400, 'message' => 'Purchase Order ID is required']);
			return;
		}

		// Start transaction
		$this->db->trans_start();

		// Delete existing po_products records for this PO
		$this->db->where('po_id', $po_id);
		$this->db->delete('loading_product_total');

		// Update purchase_order table: delivery_status to 'pending'
		$this->db->where('id', $po_id);
		$this->db->update('purchase_order', [
			'delivery_status' => 'priority'
		]);

		// Complete transaction
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$resultpost = [
				'status' => 400,
				'message' => 'Error deleting loading list',
				'url' => $this->session->userdata('previous_url')
			];
		} else {
			$resultpost = [
				'status' => 200,
				'message' => 'Loading list deleted successfully!',
				'url' => $this->session->userdata('previous_url')
			];
		}

		return simple_json_output($resultpost);
	}

	public function get_my_stock()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($_REQUEST['warehouse_id']) && $_REQUEST['warehouse_id'] != ""):
			$warehouse_id        = $_REQUEST['warehouse_id'];
			if ($warehouse_id != 'All') {
				$keyword_filter .= " AND (warehouse_id='" . $warehouse_id . "')";
			}
		endif;

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (item_code like '%" . $keyword . "%' OR product_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM inventory WHERE (id!='') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,warehouse_name,product_name,item_code,size_name,group_id,color_name,product_id,SUM(quantity) as quantity,categories FROM inventory WHERE (id!='') $keyword_filter group by id ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$product_id = $item['product_id'];
				
				$size_label = '';
				$category = $this->common_model->getRowById('categories', 'name', ['id' => $item['categories']]);
        $size_label = $category['name'] ?? '-';
           

				$edit_url = base_url() . 'inventory/my-stock-batch/' . $id  . '/' . $warehouse_id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
				 
				// $modal_url = base_url() . 'modal/popup_inventory/inventory_update_modal/' . $id;
				// $action .= '<a href="javascript:void(0);" onclick="showLargeModal(\'' . $modal_url . '\',\'Update Stock\')" data-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Update Stock"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-stumbleupon" aria-hidden="true"></i></button></a>';
				

				$data[] = array(
					"sr_no"             => ++$start,
					"category"        => $size_label,
					"product_name"      => $item['product_name'],
					"quantity"          => $item['quantity'],
					"action"            => $action,
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

	public function update_inventory_product() {
	    $resultpost = array(
          "status" => 200,
          "message" => get_phrase('inventory_updated_successfully')
        );

	    $stock_id = $this->input->post('parent_id');
	    $manual = $this->input->post('manual');
	    $update_qty = $this->input->post('qty');
	    $curr_qty = $this->input->post('curr_qty');
	    
	    if($manual == "manual_in") {
	        $qty = intval($curr_qty) + intval($update_qty);
	    } else {
	        $qty = intval($curr_qty) - intval($update_qty);
	    }
	    
	    $this->db->where('id', $stock_id)->update('inventory', ['quantity' => $qty]);
	    $stock_detail = $this->common_model->getRowById('inventory', '*', ['id' => $stock_id]);
	    $history = [
            "parent_id" => $stock_detail['id'],
            "warehouse_id" => $stock_detail['warehouse_id'],
            "warehouse_name" => $stock_detail['warehouse_name'],
            "product_id" => $stock_detail['product_id'],
            "product_order_id" => null,
            "product_name" => $stock_detail['product_name'],
            "size_id" => $stock_detail['size_id'],
            "size_name" => $stock_detail['size_name'],
            "categories" => $stock_detail['categories'],
            "group_id" => $stock_detail['group_id'],
            "color_id" => $stock_detail['color_id'],
            "color_name" => $stock_detail['color_name'],
            "sku" => $stock_detail['sku'],
            "item_code" => $stock_detail['item_code'],
            "quantity" => $update_qty,
            "status" => $manual,
            "received_date" => date("Y-m-d"),
            "batch_no" => null,
            "expiry_date" => null,
            "invoice_no" => '',
            "received_amount" => '0',
            "approved_date" => null,
            "sample_qty" => null,
            "ar_no" => null,
            "added_date" => date("Y-m-d H:i:s"),
            "added_by_id" => $this->session->userdata('super_user_id'),
	        "added_by_name" => $this->session->userdata('super_name'),
        ];
        
        $this->db->insert('inventory_history', $history);
	    return simple_json_output($resultpost);
	}
	
	public function get_my_stock_batch()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($_REQUEST['warehouse_id']) && $_REQUEST['warehouse_id'] != ""):
			$warehouse_id        = $_REQUEST['warehouse_id'];
			if ($warehouse_id != 'All') {
				$keyword_filter .= " AND (warehouse_id='" . $warehouse_id . "')";
			}
		endif;

		if (isset($_REQUEST['product_id']) && $_REQUEST['product_id'] != ""):
			if ($_REQUEST['product_id'] != 'All') {
				$product_id        = $_REQUEST['product_id'];
				//$product_id = base64_decode($product_id);
				$result = $this->common_model->get_batch_product_1($product_id, $warehouse_id);
				//echo $this->db->last_query();exit();
				$product_id = $result['product_id'];
				$keyword_filter .= " AND (product_id='" . $product_id . "')";
			}
		endif;

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (item_code like '%" . $keyword . "%' OR product_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM inventory WHERE (id!='') $keyword_filter ORDER BY id ASC")->num_rows();
		//echo $this->db->last_query();
		$query = $this->db->query("SELECT id,warehouse_name,item_code,categories,product_name,product_id,quantity,batch_no FROM inventory WHERE (id!='') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$product_id = $item['product_id'];

				$size_label = '';
				$category = $this->common_model->getRowById('categories', 'name', ['id' => $item['categories']]);
				$size_label = $category['name'] ?? '-';

				$edit_url = base_url() . 'inventory/my-stock-history/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';

				$data[] = array(
					"sr_no"       		=> ++$start,
					"id"          		=> $item['id'],
					"warehouse_name"	=> $item['warehouse_name'],
					"category"				=> $size_label,
					"item_code"				=> $item['item_code'],
					"product_name"		=> $item['product_name'],
					"quantity"        => $item['quantity'],
					"batch_no"        => ($item['batch_no'] != '' && $item['batch_no'] != null) ? $item['batch_no'] : '-',
					"action"        	=> $action,
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

	public function get_my_stock_history()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($_REQUEST['id']) && $_REQUEST['id'] != ""):
			$id        = $_REQUEST['id'];
			if ($id != 'All') {
				$keyword_filter .= " AND (parent_id='" . $id . "')";
			}
		endif;

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
		//$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%' OR supplier_name like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(received_date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(received_date) BETWEEN '$from' AND '$to')";
			}
		}

		$total_count = $this->db->query("SELECT id FROM inventory_history WHERE (id!='') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,warehouse_name,product_name,quantity,order_id,status,received_date,added_by_name,added_date FROM inventory_history WHERE (id!='') $keyword_filter ORDER BY received_date DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_id = $item['order_id'];

				$voucher_no = '-';
				$supplier_name = '-';
				$to = '-';
				if ($item['status'] == 'manual_in') {
					$supplier_name = $item['added_by_name'];
					$status = '<span class="badge badge-success">Manual In</span>';
				} else if ($item['status'] == 'manual_out') {
					$supplier_name = $item['added_by_name'];
					$status = '<span class="badge badge-danger">Manual Out</span>';
				} else if ($item['status'] == 'product_delete') {
					$supplier_name = $item['added_by_name'];
					$status = '<span class="badge badge-danger">Product Delete</span>';
				} else if ($item['status'] == 'in') {
					$voucher_no = $this->common_model->selectByidParam($order_id, 'purchase_order', 'voucher_no');
					$supplier_name = $this->common_model->selectByidParam($order_id, 'purchase_order', 'supplier_name');
					$status = '<span class="badge badge-success">In</span>';
				} else if ($item['status'] == 'transfer_out') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'stock_transfer', 'id');
					$voucher_no = '<b>Transfer</b> <br/>GPS_ST_' . $voucher_id;
					$to = $this->common_model->selectByidParam($order_id, 'stock_transfer', 'to_name');
					$status = '<span class="badge badge badge-danger">Out</span>';
				} else if ($item['status'] == 'transfer_in') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'stock_transfer', 'id');
					$voucher_no = '<b>Transfer</b> <br/>GPS_ST_' . $voucher_id . '';
					$supplier_name = $this->common_model->selectByidParam($order_id, 'stock_transfer', 'from_name');
					$status = '<span class="badge badge-success">In</span>';
				} else if ($item['status'] == 'reserved_out') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'reserved_order', 'id');
					$voucher_no = '<b>Reserved </b> <br/>GPS_RS_' . $voucher_id . '';
					$supplier_name = '-';
					$status = '<span class="badge badge badge-danger">Out</span>';
				} else if ($item['status'] == 'reserved_in') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'reserved_order', 'id');
					$voucher_no = '<b>Reserved </b> <br/>GPS_RS_' . $voucher_id . '';
					$supplier_name = '-';
					$status = '<span class="badge badge-success">In</span>';
				} else if ($item['status'] == 'damage_out') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'damage_stock', 'id');
					$voucher_no = '<b>Damage </b> <br/>GPS_DM_' . $voucher_id . '';
					$supplier_name = '-';
					$status = '<span class="badge badge badge-danger">Out</span>';
					$supplier_name = $this->common_model->selectByidParam($order_id, 'damage_stock', 'customer_name');
					$to = $this->common_model->selectByidParam($order_id, 'damage_stock', 'company_name');
				} else if ($item['status'] == 'damage_in') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'damage_stock', 'id');
					$voucher_no = '<b>Damage </b> <br/>GPS_DM_' . $voucher_id . '';
					$supplier_name = '-';
					$status = '<span class="badge badge-warning">Damage Stock Delete</span>';
					$supplier_name = $this->common_model->selectByidParam($order_id, 'damage_stock', 'customer_name');
					$to = $this->common_model->selectByidParam($order_id, 'damage_stock', 'company_name');
				} else if ($item['status'] == 'return') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'goods_return', 'id');
					$voucher_no = '<b>Return </b> <br/>GPS_GR_' . $voucher_id . '';
					$supplier_name = '-';
					$status = '<span class="badge badge-success">In</span>';
					$supplier_name = $this->common_model->selectByidParam($order_id, 'goods_return', 'customer_name');
					$to = $this->common_model->selectByidParam($order_id, 'goods_return', 'company_name');
				} else if ($item['status'] == 'sales_return_delete') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'goods_return', 'id');
					$voucher_no = '<b>Return </b> <br/>GPS_GR_' . $voucher_id . '';
					$supplier_name = '-';
					$status = '<span class="badge badge-warning">Sales Return Delete</span>';
					$supplier_name = $this->common_model->selectByidParam($order_id, 'goods_return', 'customer_name');
					$to = $this->common_model->selectByidParam($order_id, 'goods_return', 'company_name');
				} else if ($item['status'] == 'sales_delete') {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'sales_order', 'order_no');
					$order_type = $this->common_model->selectByidParam($order_id, 'sales_order', 'order_type');
					$customer_id = $this->common_model->selectByidParam($order_id, 'sales_order', 'customer_id');
					$company_name = $this->common_model->selectByidParam($order_id, 'sales_order', 'company_name');
					$x_type = ($order_type == 'normal') ? 'Sales Orders' : 'Excel Orders';
					$voucher_no = '<b>' . $x_type . ' </b> <br/>' . $voucher_id . '';
					$status = '<span class="badge badge badge-warning">Sales Delete</span>';
					$supplier_name = $company_name;
					$to = $this->common_model->selectByidParam($customer_id, 'customer', 'name');
				} else {
					$voucher_id = $this->common_model->selectByidParam($order_id, 'sales_order', 'order_no');
					$order_type = $this->common_model->selectByidParam($order_id, 'sales_order', 'order_type');
					$customer_id = $this->common_model->selectByidParam($order_id, 'sales_order', 'customer_id');
					$company_name = $this->common_model->selectByidParam($order_id, 'sales_order', 'company_name');
					$x_type = ($order_type == 'normal') ? 'Sales Orders' : 'Excel Orders';
					$voucher_no = '<b>' . $x_type . ' </b> <br/>' . $voucher_id . '';
					$status = '<span class="badge badge badge-danger">Out</span>';
					$supplier_name = $company_name;
					$to = $this->common_model->selectByidParam($customer_id, 'customer', 'name');
				}

				$received_date = date('d M, Y', strtotime($item['received_date']));

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"date"        => $received_date,
					"voucher_no"        => $voucher_no,
					"product_name"        => $item['product_name'],
					"status"        => $status,
					"quantity"        => $item['quantity'],
					"added_by_name"        => $item['added_by_name'],
					"supplier_name"        => $supplier_name,
					"to"        => $to,
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


	public function get_qc_pending()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($_REQUEST['warehouse_id']) && $_REQUEST['warehouse_id'] != ""):
			$warehouse_id        = $_REQUEST['warehouse_id'];
			if ($warehouse_id != 'All') {
				$keyword_filter .= " AND (warehouse_id='" . $warehouse_id . "')";
			}
		endif;

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
		//$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%' OR supplier_name like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM inventory_dupl_history WHERE (status='pending') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,order_id,warehouse_name,product_name,quantity,batch_no,expiry_date FROM inventory_dupl_history WHERE (status='pending') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_id = $item['order_id'];

				$voucher_no = $this->common_model->selectByidParam($order_id, 'purchase_order', 'voucher_no');
				$supplier_name = $this->common_model->selectByidParam($order_id, 'purchase_order', 'supplier_name');

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"warehouse_name"        => $item['warehouse_name'],
					"product_name"        => $item['product_name'],
					"supplier_name"        => $supplier_name,
					"voucher_no"        => $voucher_no,
					"quantity"        => $item['quantity'],
					"batch_no"        => $item['batch_no'],
					"expiry_date"        => ($item['expiry_date'] != null && $item['expiry_date'] != '0000-00-00') ? date('d M, Y', strtotime($item['expiry_date'])) : '',
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

	public function get_product_by_warehouse($warehouse_id)
	{
		$query = $this->db->query("SELECT product_id,item_code,product_name FROM inventory WHERE warehouse_id='$warehouse_id' AND quantity>0 group by item_code,product_id order by product_name asc");
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$resultdata[] = array(
					"id" => $item['product_id'],
					"name" => $item['item_code'] . ' - ' . trim($item['product_name']),
				);
			}
		}
		return $resultdata;
	}


	public function get_product_id_by_warehouse($warehouse_id)
	{
		$query = $this->db->query("SELECT product_id,id,item_code,product_name,size_id,size_name FROM inventory WHERE warehouse_id='$warehouse_id' order by product_name asc");
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$resultdata[] = array(
					"id" => $item['product_id'] . '|' . $item['size_id'],
					// "name" => trim($item['item_code']),
					"name" => trim($item['item_code']) . ' - ' . trim($item['size_name']),
				);
			}
		}
		return $resultdata;
	}


	public function get_batch_by_itemcode($warehouse_id, $prod)
	{
		$pro = explode('|', $prod);
		$product_id = $pro[0];
		$item_code = $pro[1];

		$query = $this->db->query("SELECT id,batch_no FROM inventory WHERE warehouse_id='$warehouse_id' and product_id='$product_id' and item_code='$item_code' AND quantity>0 order by product_name asc");
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$resultdata[] = array(
					"id" => $item['id'],
					"name" => ($item['batch_no'] != '' && $item['batch_no'] != null) ? $item['batch_no'] : '-',
				);
			}
		}
		return $resultdata;
	}

	public function get_batch_by_product($warehouse_id, $product_id)
	{
		$query = $this->db->query("SELECT id,batch_no FROM inventory WHERE warehouse_id='$warehouse_id' and product_id='$product_id' AND quantity>0 order by product_name asc");
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$resultdata[] = array(
					"id" => $item['id'],
					"name" => ($item['batch_no'] != '' && $item['batch_no'] != null) ? $item['batch_no'] : '-',
				);
			}
		}
		return $resultdata;
	}

	public function get_qty_by_product($warehouse_id, $prod)
	{
		$pro = explode('|', $prod);
		$product_id = $pro[0];
		$size_id = $pro[1];
		$query = $this->db->query("SELECT SUM(quantity) as quantity FROM inventory WHERE warehouse_id='$warehouse_id' and product_id='$product_id' and size_id='$size_id' AND quantity>0 group by item_code order by product_name asc limit 1");
		if (!empty($query)) {
			$item = $query->row_array();
			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 200,
				'message' => 'success',
				"quantity" => $item['quantity'],
			));
		} else {
			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 400,
				'message' => 'success',
				"quantity" => '',
			));
		}
	}

	public function get_available_qty($warehouse_id, $product_id, $batch_no)
	{
		$batch_no = ($batch_no == '-') ? '' : $batch_no;
		$prod = explode('|', $product_id);
		$product_id = $prod[0];
		$item_code = $prod[1];
		$query = $this->db->query("SELECT quantity,product_id FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$product_id' and item_code='$item_code' and product_id='$product_id' limit 1");
		//echo $this->db->last_query();
		if (!empty($query)) {
			$item = $query->row_array();
			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 200,
				'message' => 'success',
				"id" => $item['product_id'],
				"quantity" => $item['quantity'],
			));
		} else {
			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 400,
				'message' => 'success',
				"id" => '',
				"quantity" => '',
			));
		}
	}

	public function add_stock_transfer($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('stock_transfer_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$from_warehouse_id = $this->input->post('from_warehouse_id', true);
		$from_warehouse_name = $this->common_model->selectByidParam($from_warehouse_id, 'warehouse', 'name');
		$to_warehouse_id = $this->input->post('to_warehouse_id', true);
		$to_warehouse_name = $this->common_model->selectByidParam($to_warehouse_id, 'warehouse', 'name');
		$product_id = $this->input->post('product_id', true);
		$batch_no_ = $this->input->post('batch_no', true);
		$quantity = $this->input->post('quantity', true);

		$data = array();
		$data['from_id']    		= $from_warehouse_id;
		$data['from_name']    		= $from_warehouse_name;
		$data['to_id']    			= $to_warehouse_id;
		$data['to_name']    		= $to_warehouse_name;
		$data['added_by_id']    	= $this->session->userdata('super_user_id');
		$data['added_by_name']    	= $this->session->userdata('super_name');
		$data['added_date']     	= date("Y-m-d H:i:s");
		$insert = $this->db->insert('stock_transfer', $data);
		$parent_id = $this->db->insert_id();

		for ($i = 0; $i < count($product_id); $i++) {
			if ($quantity[$i] > 0) {

				$prod = $product_id[$i];
				$pro = explode('|', $prod);
				$prod_id = $pro[0];
				$item_code = $pro[1];

				$batch_no = ($batch_no_[$i] == '-') ? '' : $batch_no_[$i];
				$product_name = $this->common_model->selectByidParam($prod_id, 'raw_products', 'name');

				$data_p = array();
				$data_p['parent_id']    	= $parent_id;
				$data_p['batch_no']    		= NULL;
				$data_p['product_id']    	= $prod_id;
				$data_p['product_name']    	= $product_name;
				$data_p['item_code']    	= $item_code;
				$data_p['quantity']    		= $quantity[$i];
				$insert_1 = $this->db->insert('stock_transfer_product', $data_p);

				// Stock Out
				$query_check = $this->db->query("SELECT id,quantity,expiry_date FROM inventory WHERE warehouse_id='$from_warehouse_id' AND product_id='$prod_id' and item_code='$item_code' limit 1");
				if ($query_check->num_rows() > 0) {
					$gstock       = $query_check->row_array();
					$stock_id     = $gstock['id'];
					$expiry_date     = $gstock['expiry_date'];
					$new_quantity = 0;
					$new_quantity = $gstock['quantity'] - $quantity[$i];

					$prod = array();
					$prod['quantity'] = $new_quantity;
					$this->db->where('id', $stock_id);
					$this->db->update('inventory', $prod);


					$stocks_data  = array();
					$stocks_data['order_id'] = $parent_id;
					$stocks_data['parent_id'] = $stock_id;
					$stocks_data['warehouse_name'] = $from_warehouse_name;
					$stocks_data['warehouse_id'] = $from_warehouse_id;
					$stocks_data['product_id'] = $prod_id;
					$stocks_data['product_name'] = $product_name;
					$stocks_data['item_code'] = $item_code;
					$stocks_data['batch_no'] = NULL;
					$stocks_data['expiry_date'] = NULL;
					$stocks_data['quantity']    = $quantity[$i];
					$stocks_data['status'] 	   = 'transfer_out';
					$stocks_data['received_date'] = date("Y-m-d H:i:s");
					$stocks_data['added_date']  = date("Y-m-d H:i:s");
					$stocks_data['added_by_id']   = $this->session->userdata('super_user_id');
					$stocks_data['added_by_name'] = $this->session->userdata('super_name');
					$this->db->insert('inventory_history', $stocks_data);

					//Stock In 
					$check = $this->db->query("SELECT id,quantity FROM inventory where product_id='$prod_id' and warehouse_id='$to_warehouse_id' and item_code='$item_code'");
					$prod = array();
					$pro_de = array();

					if ($check->num_rows() > 0) {
						$check_row = $check->row_array();
						$check_quantity = $check_row['quantity'];
						$check_id = $check_row['id'];

						$final_quantity = intval($check_quantity) + $quantity[$i];

						$prod['warehouse_name'] = $to_warehouse_name;
						$prod['warehouse_id'] = $to_warehouse_id;
						$prod['product_id'] = $prod_id;
						$prod['product_name'] = $product_name;
						$prod['item_code'] = $item_code;
						$prod['batch_no'] = NULL;
						$prod['expiry_date'] = NULL;
						$prod['quantity'] = $final_quantity;
						$this->db->where('id', $check_id);
						$this->db->update('inventory', $prod);

						$pro_de['order_id'] = $parent_id;
						$pro_de['parent_id'] = $check_id;
						$pro_de['warehouse_name'] = $to_warehouse_name;
						$pro_de['warehouse_id'] = $to_warehouse_id;
						$pro_de['product_id'] = $prod_id;
						$pro_de['product_name'] = $product_name;
						$pro_de['item_code'] = $item_code;
						$pro_de['batch_no'] = NULL;
						$pro_de['expiry_date'] = NULL;
						$pro_de['quantity']    = $quantity[$i];
						$pro_de['status'] 	   = 'transfer_in';
						$pro_de['received_date'] = date("Y-m-d H:i:s");
						$pro_de['added_date']  = date("Y-m-d H:i:s");
						$pro_de['added_by_id']   = $this->session->userdata('super_user_id');
						$pro_de['added_by_name'] = $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $pro_de);
					} else {
						$prod['warehouse_name'] = $to_warehouse_name;
						$prod['warehouse_id'] = $to_warehouse_id;
						$prod['product_id'] = $prod_id;
						$prod['product_name'] = $product_name;
						$prod['item_code'] = $item_code;
						$prod['batch_no'] = NULL;
						$prod['expiry_date'] = NULL;
						$prod['quantity'] = $quantity[$i];
						$this->db->insert('inventory', $prod);
						$check_id = $this->db->insert_id();;

						$pro_de['order_id'] = $parent_id;
						$pro_de['parent_id'] = $check_id;
						$pro_de['warehouse_name'] = $to_warehouse_name;
						$pro_de['warehouse_id'] = $to_warehouse_id;
						$pro_de['product_id'] = $prod_id;
						$pro_de['product_name'] = $product_name;
						$pro_de['item_code'] = $item_code;
						$pro_de['batch_no'] = NULL;
						$pro_de['expiry_date'] = NULL;
						$pro_de['quantity']    = $quantity[$i];
						$pro_de['status'] 	   = 'transfer_in';
						$pro_de['received_date'] = date("Y-m-d H:i:s");
						$pro_de['added_date']  = date("Y-m-d H:i:s");
						$pro_de['added_by_id']   = $this->session->userdata('super_user_id');
						$pro_de['added_by_name'] = $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $pro_de);
					}
				}
			}
		}
		$this->session->set_flashdata('flash_message', "Stock Transfer Update Successfully !!");
		return simple_json_output($resultpost);
	}

	public function update_purchase_order_loading_list(){
		if ($this->session->userdata('inventory_login') != true) {
			echo json_encode(['status' => 400, 'message' => 'Unauthorized']);
			return;
		}

		$po_id = $this->input->post('po_id');

		if (empty($po_id)) {
			echo json_encode(['status' => 400, 'message' => 'Purchase Order ID is required']);
			return;
		}

		// Start transaction
		$this->db->trans_start();

		// Update purchase_order table: delivery_status to 'loading'
		$this->db->where('id', $po_id);
		$this->db->update('purchase_order', [
			'delivery_status' => 'loading',
			'loading_date' => date('Y-m-d H:i:s'),
		]);

		// Get product data arrays from form
		$product_ids = $this->input->post('product_id'); // Array keys are po_products.id
		$loading_qtys = $this->input->post('loading_qty');
		$official_ci_qtys = $this->input->post('official_ci_qty');
		$black_qtys = $this->input->post('black_qty');
		$unit_price_rmbs = $this->input->post('unit_price_rmb');
		$total_amount_rmbs = $this->input->post('total_amount_rmb');
		$official_ci_unit_price_usds = $this->input->post('official_ci_unit_price_usd');
		$total_amount_usds = $this->input->post('total_amount_usd');
		$black_total_prices = $this->input->post('black_total_price');
		$invoice_nos = $this->input->post('invoice_no');
		$invoice_suppliers = $this->input->post('invoice_supplier');
		$invoice_infos = $this->input->post('invoice');
		$invoice_dates = $this->input->post('invoice_date');
		$invoice_termss = $this->input->post('invoice_terms');
		$invoice_price_termss = $this->input->post('invoice_price_terms');
		
		// Get metric data arrays (nested arrays: [po_product_id][variation_index])
		$pkg_ctns = $this->input->post('pkg_ctn');
		$nw_kgs = $this->input->post('nw_kg');
		$total_nws = $this->input->post('total_nw');
		$gw_kgs = $this->input->post('gw_kg');
		$total_gws = $this->input->post('total_gw');
		$lengths = $this->input->post('length');
		$widths = $this->input->post('width');
		$heights = $this->input->post('height');
		$total_cbms = $this->input->post('total_cbm');
		$variation_ids = $this->input->post('variation_id');

		// Delete existing loading_product_total entries for this PO
		$this->db->where('po_id', $po_id);
		$this->db->delete('loading_product_total');

		// Update each product in po_products table
		if (!empty($product_ids)) {
			foreach ($product_ids as $po_product_id => $product_id) {
				if (empty($po_product_id)) continue;

				$is_new = 0;
				$check_prefix = explode('_', $po_product_id);
				if ($check_prefix[0] == 'new') {
					$is_new = 1;
				}

				// Get values from arrays, default to 0 if not set
				$loading_qty = isset($loading_qtys[$po_product_id]) ? intval($loading_qtys[$po_product_id]) : 0;
				$official_ci_qty = isset($official_ci_qtys[$po_product_id]) ? intval($official_ci_qtys[$po_product_id]) : 0;
				$black_qty = isset($black_qtys[$po_product_id]) ? intval($black_qtys[$po_product_id]) : 0;
				$unit_price_rmb = isset($unit_price_rmbs[$po_product_id]) ? floatval($unit_price_rmbs[$po_product_id]) : 0.00;
				$total_amount_rmb = isset($total_amount_rmbs[$po_product_id]) ? floatval($total_amount_rmbs[$po_product_id]) : 0.00;
				$official_ci_unit_price_usd = isset($official_ci_unit_price_usds[$po_product_id]) ? floatval($official_ci_unit_price_usds[$po_product_id]) : 0.00;
				$total_amount_usd = isset($total_amount_usds[$po_product_id]) ? floatval($total_amount_usds[$po_product_id]) : 0.00;
				$black_total_price = isset($black_total_prices[$po_product_id]) ? floatval($black_total_prices[$po_product_id]) : 0.00;
				$pkg_ctn = isset($pkg_ctns[$po_product_id]) ? intval($pkg_ctns[$po_product_id]) : 0;
				
				// Get invoice number and supplier
				$invoice_no = isset($invoice_nos[$po_product_id]) ? intval($invoice_nos[$po_product_id]) : 0;
				$invoice_supplier_id = 0;
				$invoice_info = '';
				$invoice_date = null;
				$invoice_terms = '';
				$invoice_price_terms = '';
				
				if ($invoice_no > 0) {
					if (isset($invoice_suppliers[$invoice_no])) {
						$invoice_supplier_id = intval($invoice_suppliers[$invoice_no]);
					}
					// Get invoice info, date, terms, and price terms for this invoice number
					if (isset($invoice_infos[$invoice_no])) {
						$invoice_info = $this->db->escape_str(trim($invoice_infos[$invoice_no]));
					}
					if (isset($invoice_dates[$invoice_no]) && !empty($invoice_dates[$invoice_no])) {
						$invoice_date = $this->db->escape_str(trim($invoice_dates[$invoice_no]));
					}
					if (isset($invoice_termss[$invoice_no])) {
						$invoice_terms = $this->db->escape_str(trim($invoice_termss[$invoice_no]));
					}
					if (isset($invoice_price_termss[$invoice_no])) {
						$invoice_price_terms = $this->db->escape_str(trim($invoice_price_termss[$invoice_no]));
					}
				}

				// Initialize sums for metric fields
				$sum_nw_kg = 0.00;
				$sum_total_nw_kg = 0.00;
				$sum_gw_kg = 0.00;
				$sum_total_gw_kg = 0.00;
				$sum_length = 0.00;
				$sum_width = 0.00;
				$sum_height = 0.00;
				$sum_total_cbm_value = 0.00;

				// Collect all variation metric values and sum them
				if (isset($nw_kgs[$po_product_id]) && is_array($nw_kgs[$po_product_id])) {
					foreach ($nw_kgs[$po_product_id] as $var_index => $value) {
						// Get metric values for this variation
						$nw_kg = isset($nw_kgs[$po_product_id][$var_index]) ? floatval($nw_kgs[$po_product_id][$var_index]) : 0.00;
						$pkg_ctn = isset($pkg_ctns[$po_product_id][$var_index]) ? floatval($pkg_ctns[$po_product_id][$var_index]) : 0.00;
						$total_nw_kg = isset($total_nws[$po_product_id][$var_index]) ? floatval($total_nws[$po_product_id][$var_index]) : 0.00;
						$gw_kg = isset($gw_kgs[$po_product_id][$var_index]) ? floatval($gw_kgs[$po_product_id][$var_index]) : 0.00;
						$total_gw_kg = isset($total_gws[$po_product_id][$var_index]) ? floatval($total_gws[$po_product_id][$var_index]) : 0.00;
						$length = isset($lengths[$po_product_id][$var_index]) ? floatval($lengths[$po_product_id][$var_index]) : 0.00;
						$width = isset($widths[$po_product_id][$var_index]) ? floatval($widths[$po_product_id][$var_index]) : 0.00;
						$height = isset($heights[$po_product_id][$var_index]) ? floatval($heights[$po_product_id][$var_index]) : 0.00;
						$total_cbm_value = isset($total_cbms[$po_product_id][$var_index]) ? floatval($total_cbms[$po_product_id][$var_index]) : 0.00;
						$variation_id = isset($variation_ids[$po_product_id][$var_index]) ? intval($variation_ids[$po_product_id][$var_index]) : 0;

						// Sum up for po_products
						$sum_nw_kg += $nw_kg;
						$sum_total_nw_kg += $total_nw_kg;
						$sum_gw_kg += $gw_kg;
						$sum_total_gw_kg += $total_gw_kg;
						$sum_length += $length;
						$sum_width += $width;
						$sum_height += $height;
						$sum_total_cbm_value += $total_cbm_value;

						// Insert individual entry into loading_product_total
						$loading_total_data = [
							'po_id' => $po_id,
							'parent_id' => $po_product_id,
							'pkg_ctn' => $pkg_ctn,
							'nw_kg' => $nw_kg,
							'total_nw_kg' => $total_nw_kg,
							'gw_kg' => $gw_kg,
							'total_gw_kg' => $total_gw_kg,
							'length' => $length,
							'width' => $width,
							'height' => $height,
							'total_cbm_value' => $total_cbm_value
						];

						if($is_new == 0) {
							$this->db->insert('loading_product_total', $loading_total_data);
						}
					}
				}

				// Update po_products record with sums and invoice data
				$update_data = [
					'loading_qty' => $loading_qty,
					'official_ci_qty' => $official_ci_qty,
					'black_qty' => $black_qty,
					'unit_price_rmb' => $unit_price_rmb,
					'total_amount_rmb' => $total_amount_rmb,
					'official_ci_unit_price_usd' => $official_ci_unit_price_usd,
					'total_amount_usd' => $total_amount_usd,
					'black_total_price' => $black_total_price,
					'pkg_ctn' => count($pkg_ctns),
					'nw_kg' => $sum_nw_kg,
					'total_nw_kg' => $sum_total_nw_kg,
					'gw_kg' => $sum_gw_kg,
					'total_gw_kg' => $sum_total_gw_kg,
					'length' => $sum_length,
					'width' => $sum_width,
					'height' => $sum_height,
					'total_cbm_value' => $sum_total_cbm_value,
					'invoice_no' => $invoice_no,
					'invoice_supplier_id' => $invoice_supplier_id,
					'invoice' => $invoice_info,
					'invoice_date' => $invoice_date,
					'invoice_terms' => $invoice_terms,
					'invoice_price_terms' => $invoice_price_terms
				];

				if($is_new == 0) {
					$this->db->where('id', $po_product_id);
					$this->db->where('parent_id', $po_id);
					$this->db->update('po_products', $update_data);
				} elseif($is_new == 1 && $loading_qty > 0) {
					$product_details = $this->get_raw_products_by_id($product_id)->row_array();
					if (!$product_details) {
						$this->db->trans_rollback();
						$resultpost = array(
							"status" => 400,
							"message" => "Product not found: ID " . $product_id
						);
						return simple_json_output($resultpost);
					}
	
					$update_data['parent_id']            	= $po_id;
					$update_data['supplier_id']          	= $product_details['supplier_id'];
					$update_data['product_type']         	= $product_details['type'];
					$update_data['product_id']           	= $product_id;
					$update_data['product_name']         	= $product_details['name'] ?? '';
					$update_data['item_code']            	= $product_details['item_code'];
					$update_data['sizes']                	= null;
					$update_data['color_id']             	= null;
					$update_data['color_name']           	= null;
					$update_data['categories']           	= $product_details['categories'] ?? NULL;
					$update_data['group_id']             	= $product_details['group_id'] ?? NULL;
					$update_data['hsn_code']             	= $product_details['hsn_code'] ?? NULL;
					$update_data['unit']                 	= $product_details['unit'] ?? NULL;
					$update_data['cbm']               		= $product_details['cbm'];
					$update_data['total_cbm']         		= $product_details['cbm'] * 0;
					$update_data['pending_po_qty']       	= 0;
					$update_data['loading_list_qty']     	= 0;
					$update_data['in_stock_qty']         	= 0;
					$update_data['current_company_qty']  	= 0;
					$update_data['quantity']             	= 0;
					$update_data['cartoon']              	= intval($product_details['cartoon_qty'] ?? 0);
					$update_data['rate']                 	= $product_details['product_mrp'] ?? NULL;
					$update_data['basic_amount']         	= $product_details['costing_price'] ?? NULL;
					$update_data['discount']             	= 0;
					$update_data['discount_amount']      	= 0;
					$update_data['gst']                  	= 0;
					$update_data['gst_amount']           	= 0;
					$update_data['total_val']            	= 0;
					$update_data['pending']              	= 0;
					$update_data['received']             	= 0;
					$update_data['received_date']        	= null;
					$update_data['is_priority']          	= 1;
					$update_data['is_complete']          	= 0;

					$this->db->insert('po_products', $update_data);
					if (isset($nw_kgs[$po_product_id]) && is_array($nw_kgs[$po_product_id])) {
						$insert_id = $this->db->insert_id();
						foreach ($nw_kgs[$po_product_id] as $var_index => $value) {
							// Insert individual entry into loading_product_total
							$loading_total_data = [
								'po_id' => $po_id,
								'parent_id' => $insert_id,
								'pkg_ctn' => isset($pkg_ctns[$po_product_id][$var_index]) ? floatval($pkg_ctns[$po_product_id][$var_index]) : 0.00,
								'nw_kg' => isset($nw_kgs[$po_product_id][$var_index]) ? floatval($nw_kgs[$po_product_id][$var_index]) : 0.00,
								'total_nw_kg' => isset($total_nws[$po_product_id][$var_index]) ? floatval($total_nws[$po_product_id][$var_index]) : 0.00,
								'gw_kg' => isset($gw_kgs[$po_product_id][$var_index]) ? floatval($gw_kgs[$po_product_id][$var_index]) : 0.00,
								'total_gw_kg' => isset($total_gws[$po_product_id][$var_index]) ? floatval($total_gws[$po_product_id][$var_index]) : 0.00,
								'length' => isset($lengths[$po_product_id][$var_index]) ? floatval($lengths[$po_product_id][$var_index]) : 0.00,
								'width' => isset($widths[$po_product_id][$var_index]) ? floatval($widths[$po_product_id][$var_index]) : 0.00,
								'height' => isset($heights[$po_product_id][$var_index]) ? floatval($heights[$po_product_id][$var_index]) : 0.00,
								'total_cbm_value' => isset($total_cbms[$po_product_id][$var_index]) ? floatval($total_cbms[$po_product_id][$var_index]) : 0.00
							];

							$this->db->insert('loading_product_total', $loading_total_data);
						}
					}
				}
			}
		}

		// Complete transaction
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			echo json_encode(['status' => 400, 'message' => 'Error updating loading list']);
		} else {
			// Get the redirect URL (usually the priority PO page)
			$redirect_url = $this->agent->referrer();
			echo json_encode([
				'status' => 200, 
				'message' => 'Loading list updated successfully!',
				'url' => $redirect_url
			]);
		}
	}

	public function add_loading_list_po()
	{
		$this->db->trans_begin();
		
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('loading_list_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$voucher_no = clean_and_escape($this->input->post('voucher_no'));
		if ($voucher_no != '') {
			$check_voucher_no = $this->check_duplication('on_create', 'voucher_no', $voucher_no, 'purchase_order');
		} else {
			$check_voucher_no  = true;
		}

		if ($check_voucher_no == false) {
			$this->db->trans_rollback();
			$this->session->set_flashdata('error_message', get_phrase('voucher_no_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Voucher No Duplication'
			);
			return simple_json_output($resultpost);
		}

		// Get basic form data
		$method = clean_and_escape($this->input->post('input_method'));
		$warehouse_id = $this->input->post('warehouse_id');
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$company_id = $this->input->post('company_id');
		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
		$total_cbm = 0.00;

		// Collect all product rows from all suppliers
		$supplier_ids = $this->input->post('supplier_id');
		
		// Validate that at least one supplier is selected
		if (!is_array($supplier_ids) || empty($supplier_ids) || !$supplier_ids[0]) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Please select at least one supplier."
			);
			return simple_json_output($resultpost);
		}

		// Prepare purchase_order data
		$delivery_address = $this->input->post('delivery_address');
		$data = array(
			'method' => $method,
			'voucher_no' => $voucher_no,
			'date' => $this->input->post('date'),
			'delivery_date' => $this->input->post('delivery_date'),
			'company_id' => $company_id,
			'company_name' => $company_name,
			'warehouse_id' => $warehouse_id,
			'warehouse_name' => $warehouse_name,
			'billing_address' => $delivery_address, 
			'delivery_address' => $delivery_address,
			'mode_of_payment' => $this->input->post('mode_of_payment'),
			'dispatch' => $this->input->post('dispatch'),
			'destination' => $this->input->post('destination'),
			'other_refrence' => $this->input->post('other_refrence'),
			'terms_of_delivery' => $this->input->post('terms_of_delivery'),
			'narration' => $this->input->post('narration'),
			'total_cbm' => $total_cbm,
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'added_date' => date("Y-m-d H:i:s"),
			'delivery_status' => 'loading',
			'priority_date' => date('Y-m-d H:i:s'),
			'loading_date' => date('Y-m-d H:i:s'),
			'source' => 'loading',
		);

		// Insert purchase_order
		if (!$this->db->insert('purchase_order', $data)) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('something_went_wrong')
			);
			return simple_json_output($resultpost);
		}

		$insert_id = $this->db->insert_id();
		// $insert_id = 0;

		$all_products = [];
		// product Info
		$p_invoice = $this->input->post('product_invoice');
		$p_id = $this->input->post('product_id');
		$p_type = $this->input->post('product_type');
		$p_cbm = $this->input->post('product_cbm');
		$p_model_no = $this->input->post('product_model_no');
		$p_qty = $this->input->post('product_qty');
		$p_unit_price_rmb = $this->input->post('product_unit_price_rmb');
		$p_official_qty = $this->input->post('product_official_qty');
		$p_black_qty = $this->input->post('product_black_qty');
		$p_total_amount_rmb = $this->input->post('product_total_amount_rmb');
		$p_official_ci_unit_price_usd = $this->input->post('product_official_ci_unit_price_usd');
		$p_total_amount_usd = $this->input->post('product_total_amount_usd');
		$p_black_total_price = $this->input->post('product_black_total_price');

		// Variation Info
		$p_variation_id = $this->input->post('product_variation_id');
		$p_pkg_ctn = $this->input->post('product_pkg_ctn');
		$p_net_weight = $this->input->post('product_net_weight');
		$p_total_net_weight = $this->input->post('product_total_net_weight');
		$p_gross_weight = $this->input->post('product_gross_weight');
		$p_total_gross_weight = $this->input->post('product_total_gross_weight');
		$p_length = $this->input->post('product_length');
		$p_width = $this->input->post('product_width');
		$p_height = $this->input->post('product_height');
		$p_variation_total_cbm = $this->input->post('product_variation_total_cbm');
		
		// Supplier Info
		$invoice_supplier = $this->input->post('invoice_supplier');
		$invoice = $this->input->post('invoice');
		$invoice_date = $this->input->post('invoice_date');
		$invoice_terms = $this->input->post('invoice_terms');
		$invoice_price_terms = $this->input->post('invoice_price_terms');
		
		// Filtering Products array
		foreach($supplier_ids as $supplier) {
			if(isset($p_qty[$supplier])) {
				foreach($p_qty[$supplier] as $i => $pqty) {
					if($pqty > 0) {
						$var_array = [];
						if(isset($p_variation_id[$supplier][$p_id[$supplier][$i]])) {
							foreach($p_variation_id[$supplier][$p_id[$supplier][$i]] as $index => $var_id) {
								$var_array[] = [
									"variation_id" => $p_variation_id[$supplier][$p_id[$supplier][$i]][$index],
									"pkg_ctn" => $p_pkg_ctn[$supplier][$p_id[$supplier][$i]][$index],
									"net_weight" => $p_net_weight[$supplier][$p_id[$supplier][$i]][$index],
									"total_net_weight" => $p_total_net_weight[$supplier][$p_id[$supplier][$i]][$index],
									"gross_weight" => $p_gross_weight[$supplier][$p_id[$supplier][$i]][$index],
									"total_gross_weight" => $p_total_gross_weight[$supplier][$p_id[$supplier][$i]][$index],
									"length" => $p_length[$supplier][$p_id[$supplier][$i]][$index],
									"width" => $p_width[$supplier][$p_id[$supplier][$i]][$index],
									"height" => $p_height[$supplier][$p_id[$supplier][$i]][$index],
									"variation_total_cbm" => $p_variation_total_cbm[$supplier][$p_id[$supplier][$i]][$index],
								];
							}
						}

						$all_products[] = [
								"supplier_id" => $supplier,
								"parent_id" => $insert_id,
								"invoice_no" => $p_invoice[$supplier][$i],
								"product_id" => $p_id[$supplier][$i],
								"type" => $p_type[$supplier][$i],
								"cbm" => $p_cbm[$supplier][$i],
								"model_no" => $p_model_no[$supplier][$i],
								"qty" => $p_qty[$supplier][$i],
								"unit_price_rmb" => $p_unit_price_rmb[$supplier][$i],
								"official_qty" => $p_official_qty[$supplier][$i],
								"black_qty" => $p_black_qty[$supplier][$i],
								"total_amount_rmb" => $p_total_amount_rmb[$supplier][$i],
								"official_ci_unit_price_usd" => $p_official_ci_unit_price_usd[$supplier][$i],
								"total_amount_usd" => $p_total_amount_usd[$supplier][$i],
								"black_total_price" => $p_black_total_price[$supplier][$i],
								"invoice_supplier" => $invoice_supplier[$p_invoice[$supplier][$i]],
								"invoice" => $invoice[$p_invoice[$supplier][$i]],
								"invoice_date" => $invoice_date[$p_invoice[$supplier][$i]],
								"invoice_terms" => $invoice_terms[$p_invoice[$supplier][$i]],
								"invoice_price_terms" => $invoice_price_terms[$p_invoice[$supplier][$i]],
								"product_variation" => $var_array,
						];
					}
				}
			}
		}

		if(count($all_products) == 0) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('please_add_at_least_1_product_quantity')
			);

			return simple_json_output($resultpost);
		}

		// Inserting PO Products and Priority and Loading Products
		foreach ($all_products as $p) {
			// Get product details from raw_products table
			$product_details = $this->get_raw_products_by_id($p['product_id'])->row_array();
			if (!$product_details) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => "Product not found: ID " . $p['product_id']
				);
				return simple_json_output($resultpost);
			}
		
			$po_array = [
					'parent_id'     			=> $p['parent_id'] ?? $insert_id,
					'supplier_id'   			=> $p['supplier_id'] ?? null,
					'product_type'  			=> $p['type'] ?? null,
					'product_id'    			=> $p['product_id'] ?? null,
					'categories' 					=> $product_details['categories'] ?? NULL,
					'group_id' 						=> $product_details['group_id'] ?? NULL,
					'product_name' 				=> $product_details['name'] ?? '',
					'hsn_code' 						=> $product_details['hsn_code'] ?? NULL,
					'item_code'     			=> $p['model_no'] ?? $product_details['item_code'],
					'quantity'      			=> $p['qty'],
					'pending'       			=> $p['qty'],
					'cbm'               	=> $p['cbm'],
					'total_cbm'         	=> $p['cbm'] * $p['qty'],
					'pending_po_qty'    	=> 0,
					'loading_list_qty'  	=> 0,
					'in_stock_qty'      	=> 0,
					'current_company_qty'	=> 0,
					'cartoon' 						=> intval($product_details['cartoon_qty'] ?? 0),
					'rate'                => floatval($product_details['product_mrp'] ?? 0),
					'basic_amount' 				=> floatval($product_details['costing_price'] ?? 0),
			];

			if (!$this->db->insert('purchase_order_product', $po_array)) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => get_phrase('something_went_wrong')
				);
				return simple_json_output($resultpost);
			}

			$cols_to_sum = [
				'net_weight',
				'total_net_weight',
				'gross_weight',
				'total_gross_weight',
				'length',
				'width',
				'height',
			];

			$sums = array_fill_keys($cols_to_sum, 0);
			foreach ($p['product_variation'] as $row) {
				foreach ($cols_to_sum as $col) {
					$sums[$col] += ($row[$col] ?? 0);
				}
			}

			$po_product_data = [
					'parent_id'            			=> $p['parent_id'],
					'supplier_id'          			=> $p['supplier_id'],
					'product_type'         			=> $p['type'],
					'product_id'           			=> $p['product_id'],
					'product_name'         			=> $product_details['name'] ?? '',
					'item_code'            			=> $p['model_no'],
					'sizes'                			=> null,
					'color_id'             			=> null,
					'color_name'           			=> null,
					'categories'           			=> $product_details['categories'] ?? NULL,
					'group_id'             			=> $product_details['group_id'] ?? NULL,
					'hsn_code'             			=> $product_details['hsn_code'] ?? NULL,
					'unit'                 			=> $product_details['unit'] ?? NULL,
					'cbm'               				=> $p['cbm'],
					'total_cbm'         				=> $p['cbm'] * $p['qty'],
					'pending_po_qty'       			=> 0,
					'loading_list_qty'     			=> 0,
					'in_stock_qty'         			=> 0,
					'current_company_qty'  			=> 0,
					'quantity'             			=> $p['qty'],
					'cartoon'              			=> intval($product_details['cartoon_qty'] ?? 0),
					'rate'                 			=> $product_details['product_mrp'] ?? NULL,
					'basic_amount'         			=> $product_details['costing_price'] ?? NULL,
					'discount'             			=> 0,
					'discount_amount'      			=> 0,
					'gst'                  			=> 0,
					'gst_amount'           			=> 0,
					'total_val'            			=> 0,
					'pending'              			=> $p['qty'],
					'received'             			=> 0,
					'received_date'        			=> null,
					'is_priority'          			=> 1,
					'is_complete'          			=> 0,
					'loading_qty'               => $p['qty'] ?? '0',
					'official_ci_qty'           => $p['official_qty'] ?? '0',
					'black_qty'                 => $p['black_qty'] ?? '0',
					'unit_price_rmb'            => $p['unit_price_rmb'] ?? '0',
					'total_amount_rmb'          => $p['total_amount_rmb'] ?? '0',
					'official_ci_unit_price_usd'=> $p['official_ci_unit_price_usd'] ?? '0',
					'total_amount_usd'          => $p['total_amount_usd'] ?? '0',
					'black_total_price'         => $p['black_total_price'] ?? '0',
					'invoice_no'                => $p['invoice_no'] ?? '',
					'invoice_supplier_id'       => $p['invoice_supplier'] ?? '',
					'invoice'                   => $p['invoice'] ?? '',
					'invoice_date'              => $p['invoice_date'] ?? '',
					'invoice_terms'             => $p['invoice_terms'] ?? '',
					'invoice_price_terms'       => $p['invoice_price_terms'] ?? '',
					'pkg_ctn'                   => intval($product_details['cartoon_qty'] ?? 0),
					'nw_kg' 										=> $sums['net_weight'],
					'total_nw_kg' 							=> $sums['total_net_weight'],
					'gw_kg' 										=> $sums['gross_weight'],
					'total_gw_kg' 							=> $sums['total_gross_weight'],
					'length' 										=> $sums['length'],
					'width'		  								=> $sums['width'],
					'height' 										=> $sums['height'],
				];

				if (!$this->db->insert('po_products', $po_product_data)) {
					$this->db->trans_rollback();
					$resultpost = array(
						"status" => 400,
						"message" => get_phrase('something_went_wrong')
					);
					return simple_json_output($resultpost);
				} 

				$po_product_id = $this->db->insert_id();
				foreach($p['product_variation'] as $row) {
					$loading_total_data = [
						'po_id' => $p['parent_id'],
						'parent_id' => $po_product_id,
						'pkg_ctn' => $row['pkg_ctn'],
						'nw_kg' => $row['net_weight'],
						'total_nw_kg' => $row['total_net_weight'],
						'gw_kg' => $row['gross_weight'],
						'total_gw_kg' => $row['total_gross_weight'],
						'length' => $row['length'],
						'width' => $row['width'],
						'height' => $row['height'],
						'total_cbm_value' => $row['variation_total_cbm']
					];
					
					if (!$this->db->insert('loading_product_total', $loading_total_data)) {
						$this->db->trans_rollback();
						$resultpost = array(
							"status" => 400,
							"message" => get_phrase('something_went_wrong')
						);
						return simple_json_output($resultpost);
					} 
				}
		}

		// Commit transaction
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('something_went_wrong')
			);
		} else {
			$this->db->trans_commit();
			$this->session->set_flashdata('flash_message', get_phrase('loading_list_added_successfully'));
		}

		return simple_json_output($resultpost);
	}

	public function get_stock_transfer()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
		//$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%' OR supplier_name like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM stock_transfer WHERE (id!='') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,from_name,to_name,added_date FROM stock_transfer WHERE (id!='') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$action = '';

				$product_name = array();
				$product_qty = array();
				$batch_no = array();
				$query_pro = $this->db->query("SELECT product_name,item_code,quantity,batch_no FROM stock_transfer_product WHERE (parent_id='$id') order by id asc");
				foreach ($query_pro->result_array() as $item_1) {
					$batch_no_ = ($item_1['batch_no'] != '' && $item_1['batch_no'] != null) ? $item_1['batch_no'] : '-';
					$product_name[] = '<li>' . $item_1['product_name'] . ' - ' . $item_1['item_code'] . '</li>';
					$product_qty[] = '<li>' . $item_1['quantity'] . '</li>';
					$batch_no[] = '<li>' . $batch_no_ . '</li>';
				}

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"order_id"          => 'GPS_ST_' . $item['id'],
					"from_name"        => $item['from_name'],
					"to_name"        		=> $item['to_name'],
					"product_name"        => implode(' ', $product_name),
					"product_qty"        => implode(' ', $product_qty),
					"batch_no"        => implode(' ', $batch_no),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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

	public function get_reserved_order()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
		//$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%' OR supplier_name like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM reserved_order WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,warehouse_name,reason,added_date FROM reserved_order WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "showCallsModal('" . base_url() . "modal/popup_inventory/reserved_order_return_modal/" . $id . "','Back To Stock')";
				$action = '';
				$action .= '<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$product_name = array();
				$product_qty = array();
				$batch_no = array();
				$query_pro = $this->db->query("SELECT product_name,item_code,quantity,batch_no,return_qty FROM reserved_order_product WHERE (parent_id='$id') order by id asc");
				foreach ($query_pro->result_array() as $item_1) {
					$x_qty = $item_1['quantity'] - $item_1['return_qty'];
					$batch_no_ = ($item_1['batch_no'] != '' && $item_1['batch_no'] != null) ? $item_1['batch_no'] : '-';
					$product_name[] = '<li>' . $item_1['item_code'] . ' - ' . $item_1['product_name'] . '</li>';
					$product_qty[] = '<li>' . $x_qty . '</li>';
					$batch_no[] = '<li>' . $batch_no_ . '</li>';
				}

				/*
				if(count($product_name) > 0){
					$product_name = '<span>'.$product_name.'</span>';
				}
				*/

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"order_id"          => 'GPS_RS_' . $item['id'],
					"warehouse_name"        => $item['warehouse_name'],
					"reason"        		=> $item['reason'],
					"product_name"        => implode(' ', $product_name),
					"product_qty"        => implode(' ', $product_qty),
					"batch_no"        => implode(' ', $batch_no),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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

	public function get_reserved_order_product($id)
	{
		$product = array();
		$query_1 = $this->db->query("SELECT id,product_id,product_name,item_code,quantity,return_qty,batch_no FROM reserved_order_product WHERE parent_id='$id' order by id");
		foreach ($query_1->result_array() as $item) {
			$pending = intval($item['quantity']) - intval($item['return_qty']);
			$product_id = $item['product_id'];
			$product[] = array(
				"id" => $item['id'],
				"product_id" => $item['product_id'],
				"name" => $item['item_code'] . ' - ' . $item['product_name'],
				"batch_no" => ($item['batch_no'] != '' && $item['batch_no'] != null) ? $item['batch_no'] : '-',
				"quantity" => $item['quantity'],
				"pending" => $pending,
			);
		}
		return $product;
	}

	public function add_reserved_order($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('reserved_order_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$warehouse_id = $this->input->post('warehouse_id', true);
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$reason = $this->input->post('reason', true);
		$product_id = $this->input->post('product_id', true);
		$quantity = $this->input->post('quantity', true);
		$batch_no_ = $this->input->post('batch_no', true);

		$data = array();
		$data['warehouse_id']    		= $warehouse_id;
		$data['warehouse_name']    		= $warehouse_name;
		$data['reason']    		= $reason;
		$data['added_by_id']    	= $this->session->userdata('super_user_id');
		$data['added_by_name']    	= $this->session->userdata('super_name');
		$data['added_date']     	= date("Y-m-d H:i:s");
		$insert = $this->db->insert('reserved_order', $data);
		$parent_id = $this->db->insert_id();

		for ($i = 0; $i < count($product_id); $i++) {
			if ($quantity[$i] > 0) {
				$prod = $product_id[$i];
				$pro = explode('|', $prod);
				$prod_id = $pro[0];
				$item_code = $pro[1];
				$batch_no = ($batch_no_[$i] == '-') ? '' : $batch_no_[$i];
				$product_name = $this->common_model->selectByidParam($prod_id, 'raw_products', 'name');

				$data_p = array();
				$data_p['parent_id']    	= $parent_id;
				$data_p['product_id']    	= $prod_id;
				$data_p['product_name']    	= $product_name;
				$data_p['quantity']    		= $quantity[$i];
				$data_p['batch_no']    		= NUll;
				$data_p['item_code']    	= $item_code;
				$insert_1 = $this->db->insert('reserved_order_product', $data_p);

				if ($insert_1) {
					// Stock Out
					$query_check = $this->db->query("SELECT id,quantity,expiry_date FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$prod_id' and item_code='$item_code' limit 1");
					if ($query_check->num_rows() > 0) {
						$gstock       = $query_check->row_array();
						$stock_id     = $gstock['id'];
						$expiry_date     = $gstock['expiry_date'];
						$new_quantity = 0;
						$new_quantity = $gstock['quantity'] - $quantity[$i];

						$prod = array();
						$prod['quantity'] = $new_quantity;
						$this->db->where('id', $stock_id);
						$this->db->update('inventory', $prod);


						$stocks_data  = array();
						$stocks_data['order_id'] = $parent_id;
						$stocks_data['parent_id'] = $stock_id;
						$stocks_data['warehouse_name'] = $warehouse_name;
						$stocks_data['warehouse_id'] = $warehouse_id;
						$stocks_data['product_id'] = $prod_id;
						$stocks_data['product_name'] = $product_name;
						$stocks_data['quantity']    = $quantity[$i];
						$stocks_data['batch_no']    = NUll;
						$stocks_data['item_code']    = $item_code;
						$stocks_data['expiry_date']    = NUll;
						$stocks_data['status'] 	   = 'reserved_out';
						$stocks_data['received_date'] = date("Y-m-d H:i:s");
						$stocks_data['added_date']  = date("Y-m-d H:i:s");
						$stocks_data['added_by_id']   = $this->session->userdata('super_user_id');
						$stocks_data['added_by_name'] = $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $stocks_data);
					}
				}
			}
		}
		$this->session->set_flashdata('flash_message', "Reserved Order Added Successfully !!");
		return simple_json_output($resultpost);
	}

	public function delete_reserved_order($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('reserved_order_delete_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$parent_id = $this->input->post('parent_id', true);
		$id = $this->input->post('id', true);
		$rcv_quantity = $this->input->post('received', true);
		$rcv_date = $this->input->post('received_date', true);

		$row = $this->db->query("SELECT id,warehouse_id,warehouse_name FROM reserved_order WHERE id='$parent_id' limit 1")->row_array();
		//echo $this->db->last_query();exit();
		$warehouse_id = $row['warehouse_id'];
		$warehouse_name = $row['warehouse_name'];
		//echo json_encode($rcv_quantity);exit();
		for ($i = 0; $i < count($id); $i++) {
			if ($rcv_quantity[$i] > 0) {
				$query_1 = $this->db->query("SELECT id,product_id,product_name,item_code,quantity,batch_no,return_qty FROM reserved_order_product WHERE id='$id[$i]' order by id asc");
				foreach ($query_1->result_array() as $item_1) {
					//echo $this->db->last_query();exit();
					$prod_id = $item_1['product_id'];
					$batch_no = $item_1['batch_no'];
					$product_name = $item_1['product_name'];
					$item_code = $item_1['item_code'];
					$return_qty = $item_1['return_qty'];
					$quantity = $rcv_quantity[$i];

					// Stock Out
					$query_check = $this->db->query("SELECT id,quantity FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$prod_id'  AND item_code='$item_code' limit 1");
					if ($query_check->num_rows() > 0) {
						$gstock       = $query_check->row_array();
						$stock_id     = $gstock['id'];
						$new_quantity = 0;
						$new_quantity = $gstock['quantity'] + $quantity;

						$prod = array();
						$prod['quantity'] = $new_quantity;
						$this->db->where('id', $stock_id);
						$this->db->update('inventory', $prod);

						$stocks_data  = array();
						$stocks_data['order_id'] = $parent_id;
						$stocks_data['parent_id'] = $stock_id;
						$stocks_data['warehouse_name'] = $warehouse_name;
						$stocks_data['warehouse_id'] = $warehouse_id;
						$stocks_data['product_id'] = $prod_id;
						$stocks_data['product_name'] = $product_name;
						$stocks_data['item_code']    = $item_code;
						$stocks_data['quantity']    = $quantity;
						$stocks_data['batch_no']    = NUll;
						$stocks_data['status'] 	   = 'reserved_in';
						$stocks_data['received_date'] = $rcv_date[$i];
						$stocks_data['added_date']  = date("Y-m-d H:i:s");
						$stocks_data['added_by_id']   = $this->session->userdata('super_user_id');
						$stocks_data['added_by_name'] = $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $stocks_data);

						$data = array();
						$data['is_deleted'] = '1';
						$this->db->where('id', $id);
						$this->db->update('reserved_order', $data);
					}
					$x_qty = $return_qty + $quantity;
					$data1 = array();
					$data1['return_qty'] = $x_qty;
					$this->db->where('id', $id[$i]);
					$this->db->update('reserved_order_product', $data1);
				}
			}
		}

		$qry_1 = $this->db->query("SELECT SUM(quantity) as qty FROM reserved_order_product WHERE parent_id='$parent_id' group by parent_id limit 1")->row_array();
		$qty = $qry_1['qty'];

		$qry_2 = $this->db->query("SELECT SUM(return_qty) as return_qty FROM reserved_order_product WHERE parent_id='$parent_id' group by parent_id limit 1")->row_array();
		$return_qty = $qry_2['return_qty'];

		if ($qty == $return_qty) {
			$data2 = array();
			$data2['is_deleted'] = '1';
			$this->db->where('id', $parent_id);
			$this->db->update('reserved_order', $data2);
		}

		$this->session->set_flashdata('flash_message', "Reserved Order Delete Successfully !!");
		return simple_json_output($resultpost);
	}

	public function get_scrap_product_history()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		$filter = $_REQUEST;

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (dsp.item_code like '%" . $keyword . "%' OR dsp.product_name like '%" . $keyword . "%' OR dsp.batch_no like '%" . $keyword . "%' OR dsp.scrap_qty like '%" . $keyword . "%')";
		endif;

		if (isset($filter['warehouse']) && $filter['warehouse'] != ""):
			$keyword        = $filter['warehouse'];
			$keyword_filter .= " AND (ds.warehouse_id = '$keyword')";
		endif;

		$total_count = $this->db->query("SELECT dsp.id FROM damage_stock_product as dsp INNER JOIN damage_stock as ds ON dsp.parent_id = ds.id WHERE (dsp.is_scrap = '1')  $keyword_filter GROUP BY dsp.item_code")->num_rows();
		$query = $this->db->query("SELECT dsp.product_name,dsp.item_code,SUM(dsp.scrap_qty) as qty FROM damage_stock_product as dsp INNER JOIN damage_stock as ds ON dsp.parent_id = ds.id WHERE (dsp.is_scrap = '1') $keyword_filter GROUP BY dsp.item_code ORDER BY dsp.id DESC LIMIT $start, $length");
		// 		echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {

				$data[] = array(
					"sr_no"         => ++$start,
					"product_name"  => $item['product_name'],
					"sku"           => $item['item_code'],
					"product_qty"   => $item['qty'],
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

	public function move_to_scrap()
	{
		$skus = $this->input->post('sku');
		$warehouse = $this->input->post('warehouse');

		foreach ($skus as $sku) {
			$data = $this->db->query("SELECT dsp.item_code, dsp.id, dsp.quantity FROM damage_stock_product as dsp INNER JOIN damage_stock as ds ON dsp.parent_id = ds.id WHERE dsp.item_code='$sku' AND ds.warehouse_id='$warehouse' ");
			if ($data->num_rows() > 0) {
				foreach ($data->result_array() as $item) {
					$result = [
						'is_scrap' => 1,
						'quantity' => 0,
						'scrap_qty' => $item['quantity'],
						'updated_on' => date("Y-m-d H:i:s"),
					];

					$this->db->where('id', $item['id'])->update('damage_stock_product', $result);
				}
			}
		}

		echo json_encode([
			'status' => 200,
			'message' => 'Product Moved to Scrap Successfully',
		]);
	}

	public function get_damage_stock_product_history()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		$filter = $_REQUEST;

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (dsp.item_code like '%" . $keyword . "%' OR dsp.product_name like '%" . $keyword . "%' OR dsp.batch_no like '%" . $keyword . "%' OR dsp.quantity like '%" . $keyword . "%')";
		endif;

		if (isset($filter['warehouse']) && $filter['warehouse'] != ""):
			$keyword        = $filter['warehouse'];
			$keyword_filter .= " AND (ds.warehouse_id = '$keyword')";
		endif;

		$total_count = $this->db->query("SELECT dsp.id FROM damage_stock_product as dsp INNER JOIN damage_stock as ds ON dsp.parent_id = ds.id WHERE (dsp.quantity != '0')  $keyword_filter GROUP BY dsp.item_code")->num_rows();
		$query = $this->db->query("SELECT dsp.product_name,dsp.item_code,SUM(dsp.quantity) as qty FROM damage_stock_product as dsp INNER JOIN damage_stock as ds ON dsp.parent_id = ds.id WHERE (dsp.quantity != '0') $keyword_filter GROUP BY dsp.item_code ORDER BY dsp.id DESC LIMIT $start, $length");
		// 		echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {

				$action = '<input type="checkbox" name="check" class="scrapbox" data-sku="' . $item['item_code'] . '" onchange="counterScrap();">';

				$data[] = array(
					"sr_no"         => ++$start,
					"action"         => $start,
					// 	"action"         => $action,
					"product_name"  => $item['product_name'],
					"sku"           => $item['item_code'],
					"product_qty"   => $item['qty'],
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

	public function get_damage_stock()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
		//$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%' OR supplier_name like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND DATE(date) = '$from'";
			} else {
				$keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
			}
		}

		$total_count = $this->db->query("SELECT id FROM damage_stock WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,date,customer_name,company_name,warehouse_name,reason,added_date,reference_no FROM damage_stock WHERE (is_deleted='0') $keyword_filter ORDER BY date DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/damage_stock/delete_post/" . $id . "','Are you sure want to delete!')";
				$action = '';
				$view_url = base_url() . 'inventory/damage-stock/view/' . $id;

				$action = '<a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
				// $action .= '<a href="javascript:void(0);" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" aria-label="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$product_qty = 0;
				$query_pro = $this->db->query("SELECT SUM(quantity) as quantity FROM damage_stock_product WHERE (parent_id='$id') order by id asc");
				if ($query_pro->num_rows() > 0) {
					$item_1 = $query_pro->row_array();
					$product_qty = $item_1['quantity'];
				}

				/*
				if(count($product_name) > 0){
					$product_name = '<span>'.$product_name.'</span>';
				}
				*/

				$data[] = array(
					"sr_no"             => ++$start,
					"id"                => $item['id'],
					"order_id"          => 'GPS_DM_' . $item['id'],
					"warehouse_name"    => $item['warehouse_name'],
					"reason"            => $item['reason'],
					"reference_no"      => $item['reference_no'],
					"customer_name"     => $item['customer_name'],
					"company_name"      => $item['company_name'],
					"product_qty"       => $product_qty,
					"added_date"        => date('d M, Y', strtotime($item['date'])),
					"action"            => $action,
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

	public function get_damage_stock_history($id)
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		$added_date = $this->common_model->selectByidParam($id, 'damage_stock', 'added_date');
		$warehouse_name = $this->common_model->selectByidParam($id, 'damage_stock', 'warehouse_name');
		$reason = $this->common_model->selectByidParam($id, 'damage_stock', 'reason');
		$customer_name = $this->common_model->selectByidParam($id, 'damage_stock', 'customer_name');
		$date = $this->common_model->selectByidParam($id, 'damage_stock', 'date');
		$reference_no = $this->common_model->selectByidParam($id, 'damage_stock', 'reference_no');

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (item_code like '%" . $keyword . "%' OR product_name like '%" . $keyword . "%' OR batch_no like '%" . $keyword . "%' OR quantity like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM damage_stock_product Where parent_id = '$id'  $keyword_filter")->num_rows();
		$query = $this->db->query("SELECT product_name,item_code,quantity,batch_no FROM damage_stock_product Where parent_id = '$id'  $keyword_filter ORDER BY  id DESC LIMIT $start, $length");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {

				$data[] = array(
					"sr_no"       => ++$start,
					"order_id"          => 'GPS_DM_' . $id,
					"invoice_no"        => $item['invoice_no'],
					"product_name"        		=> $item['item_code'] . ' - ' . $item['product_name'],
					"product_qty"        => $item['quantity'],
					"batch_no"        => $item['batch_no'],
					"warehouse_name"        => $warehouse_name,
					"customer_name"        => $customer_name,
					"reason"        => $reason,
					"reference_no"        => $reference_no,
					"date"        => date('d M, Y', strtotime($date)),
					"added_date"        => date('d M, Y', strtotime($added_date)),
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

	public function add_damage_stock($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('damage_stock_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$warehouse_id = $this->input->post('warehouse_id', true);
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$customer_id = $this->input->post('customer_id', true);
		$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'name');
		$company_id = $this->input->post('company_id', true);
		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
		$reference_no = $this->input->post('reference_no', true);
		$date = $this->input->post('date', true);
		$reason = $this->input->post('reason', true);
		$product_id = $this->input->post('product_id', true);
		$quantity = $this->input->post('quantity', true);
		$batch_no_ = $this->input->post('batch_no', true);

		$data = array();
		$excel_id = $this->input->post('excel_id');
		$method = 'manually';
		if ($excel_id != '' && $excel_id != NULL) {
			$method = 'by_excel';
		}

		$data['method']      			= $method;
		$data['excel_id']      			= $excel_id;
		$data['warehouse_id']    		= $warehouse_id;
		$data['warehouse_name']    		= $warehouse_name;
		$data['customer_id']    		= $customer_id;
		$data['customer_name']    		= $customer_name;
		$data['company_id']    			= $company_id;
		$data['company_name']    		= $company_name;
		$data['reference_no']    		= $reference_no;
		$data['date']    		= $date;
		$data['reason']    		= $reason;
		$data['added_by_id']    	= $this->session->userdata('super_user_id');
		$data['added_by_name']    	= $this->session->userdata('super_name');
		$data['added_date']     	= date("Y-m-d H:i:s");
		$insert = $this->db->insert('damage_stock', $data);
		$parent_id = $this->db->insert_id();

		for ($i = 0; $i < count($product_id); $i++) {
			if ($quantity[$i] > 0 && $product_id != '') {
				$prod = $product_id[$i];
				$pro = explode('|', $prod);
				$prod_id = $pro[0];
				$size_id = $pro[1];

				$inv_prod = $this->db->where('product_id', $prod_id)->where('size_id', $size_id)->get('inventory')->row_array();

				$item_code = $inv_prod['item_code'];

				$batch_no = ($batch_no_[$i] == '-') ? '' : $batch_no_[$i];
				$product_name = $this->common_model->selectByidParam($prod_id, 'raw_products', 'name');

				$data_p = array();
				$data_p['parent_id']    	= $parent_id;
				$data_p['product_id']    	= $prod_id;
				$data_p['product_name']    	= $product_name;

				$data_p['size_id']          = $size_id;
				$data_p['size_name']        = $inv_prod['size_name'];
				$data_p['group_id']         = $inv_prod['group_id'];
				$data_p['color_id']         = $inv_prod['color_id'];
				$data_p['color_name']       = $inv_prod['color_name'];

				$data_p['quantity']    		= $quantity[$i];
				$data_p['batch_no']    		= NULL;
				$data_p['item_code']    	= $item_code;
				$insert_1 = $this->db->insert('damage_stock_product', $data_p);

				if ($insert_1) {
					// Stock Out
					$query_check = $this->db->query("SELECT id,quantity,expiry_date FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$prod_id' and item_code='$item_code' limit 1");
					if ($query_check->num_rows() > 0) {
						$gstock       = $query_check->row_array();
						$stock_id     = $gstock['id'];
						$expiry_date     = $gstock['expiry_date'];
						$new_quantity = 0;
						$new_quantity = $gstock['quantity'] - $quantity[$i];

						$prod = array();
						$prod['quantity'] = $new_quantity;
						$this->db->where('id', $stock_id);
						$this->db->update('inventory', $prod);


						$stocks_data  = array();
						$stocks_data['order_id'] = $parent_id;
						$stocks_data['parent_id'] = $stock_id;
						$stocks_data['warehouse_name'] = $warehouse_name;
						$stocks_data['warehouse_id'] = $warehouse_id;
						$stocks_data['product_id'] = $prod_id;
						$stocks_data['product_name'] = $product_name;

						$stocks_data['size_id']   	  	= $size_id;
						$stocks_data['size_name']       = $inv_prod['size_name'];
						$stocks_data['group_id']        = $inv_prod['group_id'];
						$stocks_data['color_id']        = $inv_prod['color_id'];
						$stocks_data['color_name']      = $inv_prod['color_name'];
						$stocks_data['sku']             = $inv_prod['sku'];
						$stocks_data['categories']      = $inv_prod['categories'];

						$stocks_data['quantity']    = $quantity[$i];
						$stocks_data['batch_no']    = NULL;
						$stocks_data['item_code']    = $item_code;
						$stocks_data['expiry_date']    = NULL;
						$stocks_data['status'] 	   = 'damage_out';
						$stocks_data['received_date'] = $date;
						$stocks_data['added_date']  = date("Y-m-d H:i:s");
						$stocks_data['added_by_id']   = $this->session->userdata('super_user_id');
						$stocks_data['added_by_name'] = $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $stocks_data);
					}
				}
			}
		}

		if ($method == 'by_excel') {
			$excelData = array();
			$excelData['is_move'] = 1;
			$excelData['is_complete'] = 1;
			$this->db->where('unique_id', $excel_id);
			$this->db->update('excel_return_stock', $excelData);
		}

		$this->session->set_flashdata('flash_message', "Damage Stock Added Successfully !!");
		return simple_json_output($resultpost);
	}

	public function delete_damage_stock($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('damage_stock_delete_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$query = $this->db->query("SELECT id,warehouse_id,warehouse_name FROM damage_stock WHERE id='$id' limit 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$warehouse_id = $row['warehouse_id'];
			$warehouse_name = $row['warehouse_name'];
			$parent_id = $id;

			$query_1 = $this->db->query("SELECT id,product_id,item_code,product_name,quantity,batch_no,size_id FROM damage_stock_product WHERE parent_id='$id' order by id asc");
			foreach ($query_1->result_array() as $item_1) {

				$prod_id = $item_1['product_id'];
				$batch_no = $item_1['batch_no'];
				$product_name = $item_1['product_name'];
				$item_code = $item_1['item_code'];
				$quantity = $item_1['quantity'];
				$size_id = $item_1['size_id'];

				// Stock Out
				$query_check = $this->db->query("SELECT * FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$prod_id'  AND size_id='$size_id' limit 1");
				if ($query_check->num_rows() > 0) {
					$gstock       = $query_check->row_array();
					$stock_id     = $gstock['id'];
					$new_quantity = 0;
					$new_quantity = $gstock['quantity'] + $quantity;

					$prod = array();
					$prod['quantity'] = $new_quantity;
					$this->db->where('id', $stock_id);
					$this->db->update('inventory', $prod);

					$stocks_data  = array();
					$stocks_data['order_id'] = $parent_id;
					$stocks_data['parent_id'] = $stock_id;
					$stocks_data['warehouse_name'] = $warehouse_name;
					$stocks_data['warehouse_id'] = $warehouse_id;
					$stocks_data['product_id'] = $prod_id;
					$stocks_data['product_name'] = $product_name;

					$stocks_data['size_id']   	  	= $size_id;
					$stocks_data['size_name']         = $gstock['size_name'];
					$stocks_data['group_id']          = $gstock['group_id'];
					$stocks_data['color_id']          = $gstock['color_id'];
					$stocks_data['color_name']        = $gstock['color_name'];
					$stocks_data['sku']               = $gstock['sku'];
					$stocks_data['categories']        = $gstock['categories'];

					$stocks_data['quantity']    = $quantity;
					$stocks_data['item_code']    = $item_code;
					$stocks_data['batch_no']    = NULL;
					$stocks_data['status'] 	   = 'damage_in';
					$stocks_data['received_date'] = date("Y-m-d H:i:s");
					$stocks_data['added_date']  = date("Y-m-d H:i:s");
					$stocks_data['added_by_id']   = $this->session->userdata('super_user_id');
					$stocks_data['added_by_name'] = $this->session->userdata('super_name');
					$this->db->insert('inventory_history', $stocks_data);

					$data = array();
					$data['is_deleted'] = '1';
					$this->db->where('id', $id);
					$this->db->update('damage_stock', $data);
				}
			}
		}

		$this->session->set_flashdata('flash_message', "Reserved Order Delete Successfully !!");
		return simple_json_output($resultpost);
	}

	public function get_purchase_order_1()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (name like '%" . $keyword . "%')";
		endif;

		$json_data = array(
			"draw" => intval($params['draw']),
			"recordsTotal" => 0,
			"recordsFiltered" => 0,
			"data" => $data
		);
		echo json_encode($json_data);
	}

	/* Customer Start */
	public function add_customer()
	{
		$company_id 				= $this->input->post('company_id');
		$company_id 				= isset($company_id) ? $company_id : [];
		$type 							= $this->input->post('type');
		$staff_id 				  = ($type == 'leads') ? 0 : $this->input->post('staff_id');
		
		$user_id            = (int) $this->session->userdata('super_user_id');
		$user_name          = (string) $this->session->userdata('super_name');

		// Form inputs
		$company_name   = clean_and_escape($this->input->post('company_name'));
		$address        = clean_and_escape($this->input->post('address'));
		$address_2      = clean_and_escape($this->input->post('address_2'));

		$state_id       = (int) $this->input->post('state_id');
		$city_id        = (int) $this->input->post('city_id');

		$pincode        = clean_and_escape($this->input->post('pincode'));
		$gst_name       = clean_and_escape($this->input->post('gst_name'));
		$gst_no         = clean_and_escape($this->input->post('gst_no'));

		$owner_name     = clean_and_escape($this->input->post('owner_name'));
		$owner_email		= clean_and_escape($this->input->post('owner_email'));
		$owner_mobile   = clean_and_escape($this->input->post('owner_mobile'));
		$owner_whatsapp = clean_and_escape($this->input->post('owner_whatsapp'));

		$pm_name        = clean_and_escape($this->input->post('pm_name'));
		$pm_email				= clean_and_escape($this->input->post('pm_email'));
		$pm_mobile      = clean_and_escape($this->input->post('pm_mobile'));
		$pm_whatsapp    = clean_and_escape($this->input->post('pm_whatsapp'));

		$other_name     = clean_and_escape($this->input->post('other_name'));
		$other_email		= clean_and_escape($this->input->post('other_email'));
		$other_mobile   = clean_and_escape($this->input->post('other_mobile'));
		$other_whatsapp = clean_and_escape($this->input->post('other_whatsapp'));

		// Digits only
		$pincode_digits        = preg_replace('/[^0-9]/', '', $pincode);

		$owner_mobile_digits   = preg_replace('/[^0-9]/', '', $owner_mobile);
		$owner_whatsapp_digits = preg_replace('/[^0-9]/', '', $owner_whatsapp);

		$pm_mobile_digits      = preg_replace('/[^0-9]/', '', $pm_mobile);
		$pm_whatsapp_digits    = preg_replace('/[^0-9]/', '', $pm_whatsapp);

		$other_mobile_digits   = preg_replace('/[^0-9]/', '', $other_mobile);
		$other_whatsapp_digits = preg_replace('/[^0-9]/', '', $other_whatsapp);

		// Get state/city names
		$state_name = ($state_id > 0) ? (string) $this->common_model->selectByidParam($state_id, 'state_list', 'state') : '';
		$city_name  = ($city_id > 0)  ? (string) $this->common_model->selectByidParam($city_id, 'city_list', 'district') : '';

		/**
		 * RULE #1 (within same customer form):
		 * - Owner mobile == Owner whatsapp is allowed
		 * - PM mobile == PM whatsapp is allowed
		 * - Other mobile == Other whatsapp is allowed
		 * BUT numbers must NOT match across groups (Owner vs PM vs Other).
		 */
		$ownerNums = array_values(array_unique(array_filter([$owner_mobile_digits, $owner_whatsapp_digits]))); 
		$pmNums    = array_values(array_unique(array_filter([$pm_mobile_digits, $pm_whatsapp_digits])));
		$otherNums = array_values(array_unique(array_filter([$other_mobile_digits, $other_whatsapp_digits])));

		// Owner vs PM
		$conflict = array_values(array_intersect($ownerNums, $pmNums));
		if (!empty($conflict)) {
			return simple_json_output([
				"status"  => 400,
				"message" => "Number {$conflict[0]} cannot be used in both Owner and Purchase Manager."
			]);
		}

		// Owner vs Other
		$conflict = array_values(array_intersect($ownerNums, $otherNums));
		if (!empty($conflict)) {
			return simple_json_output([
				"status"  => 400,
				"message" => "Number {$conflict[0]} cannot be used in both Owner and Other."
			]);
		}

		// PM vs Other
		$conflict = array_values(array_intersect($pmNums, $otherNums));
		if (!empty($conflict)) {
			return simple_json_output([
				"status"  => 400,
				"message" => "Number {$conflict[0]} cannot be used in both Purchase Manager and Other."
			]);
		}

		/**
		 * RULE #2 (database):
		 * All numbers used in this insert must NOT exist in ANY of the 6 columns
		 * for any other customer (is_deleted = 0).
		 */
		$allNums = array_values(array_unique(array_merge($ownerNums, $pmNums, $otherNums)));
		
		$this->db->from('customer');
		$this->db->where('is_deleted', 0);

		$this->db->group_start();
		$this->db->or_where_in('owner_mobile', $allNums);
		$this->db->or_where_in('owner_whatsapp', $allNums);
		$this->db->or_where_in('pm_mobile', $allNums);
		$this->db->or_where_in('pm_whatsapp', $allNums);
		$this->db->or_where_in('other_mobile', $allNums);
		$this->db->or_where_in('other_whatsapp', $allNums);
		$this->db->group_end();

		$exists_phone = $this->db->get();

		if ($exists_phone->num_rows() > 0) {
			$exists = $exists_phone->row_array();

			$matchedNumber = '';
			$dbNums = [
				(string) $exists['owner_mobile'],
				(string) $exists['owner_whatsapp'],
				(string) $exists['pm_mobile'],
				(string) $exists['pm_whatsapp'],
				(string) $exists['other_mobile'],
				(string) $exists['other_whatsapp'],
			];

			foreach ($allNums as $n) {
				if (in_array((string) $n, $dbNums, true)) {
					$matchedNumber = (string) $n;
					break;
				}
			}

			$existing_staff = $this->common_model->selectByidParam($exists['added_by'], 'sys_users', 'first_name');

			return simple_json_output([
				"status"  => 400,
				// "message" => "Phone/Whatsapp number {$matchedNumber} already exists in {$existing_staff}."
				"message" => "Phone/Whatsapp number {$matchedNumber} already exists in " . get_phrase($exists['type']) . "."
			]);
		}

		$staff_name = $this->common_model->selectByidParam($staff_id, 'sys_users', 'first_name');

		$data = array(
			"company_id"     => implode(',', $company_id),
			"type"   				 => $type,
			"company_name"   => $company_name,
			"address"        => $address,
			"address_2"      => $address_2,

			"state_id"       => $state_id,
			"state_name"     => $state_name,
			"city_id"        => $city_id,
			"city_name"      => $city_name,

			"pincode"        => $pincode_digits,
			"gst_name"       => $gst_name,
			"gst_no"         => $gst_no,

			"owner_name"     => $owner_name,
			"owner_email"		 => $owner_email,
			"owner_mobile"   => $owner_mobile_digits,
			"owner_whatsapp" => $owner_whatsapp_digits,

			"pm_name"        => $pm_name,
			"pm_email"			 => $pm_email,
			"pm_mobile"      => $pm_mobile_digits,
			"pm_whatsapp"    => $pm_whatsapp_digits,

			"other_name"     => $other_name,
			"other_email"		 => $other_email,
			"other_mobile"   => $other_mobile_digits,
			"other_whatsapp" => $other_whatsapp_digits,

			"added_by_id"    => $staff_id,
			"added_by_name"  => $staff_name,
			"added_date"     => date("Y-m-d H:i:s"),
			"is_deleted"     => 0,
		);

		if($this->db->insert('customer', $data)) {
			$customer_id = $this->db->insert_id();
			$logs = [
				"customer_id" 		=> $customer_id,
				"action"      		=> "create",
				"label"          => json_encode(["badge" => "success", "message" => get_phrase($type) . " Added"]),
				"message"     		=> get_phrase($type) . " Added By {$user_name}",
				"json"  					=> json_encode($data),
				"added_by"				=> $user_id,
				"added_by_name"		=> get_phrase($user_name),
				"added_date"			=> date("Y-m-d H:i:s"),
			];

			$this->db->insert('customer_log', $logs);
		};

		$this->session->set_flashdata('flash_message', get_phrase($type . '_added_successfully'));

		$resultpost = array(
			"status"  => 200,
			"message" => get_phrase($type . '_added_successfully'),
			"url"     => $this->agent->referrer(),
		);

		return simple_json_output($resultpost);
	}

	public function edit_customer($id = "")
	{
		
		$id = (int) $id;

		$user_id   = (int) $this->session->userdata('super_user_id');
		$user_name = (string) $this->session->userdata('super_name');
		$type			 = $this->input->post('type');

		// Current row (to detect changes)
		$old = $this->db->where('id', $id)->where('is_deleted', 0)->get('customer')->row_array();
		if (empty($old)) {
			return simple_json_output([
				"status"  => 404,
				"message" => "Customer not found."
			]);
		}

		// --- inputs (same as add) ---
		$company_id 				= $this->input->post('company_id');
		$company_id 				= isset($company_id) ? $company_id : [];
		$staff_id   = ($type == 'leads') ? 0 : $this->input->post('staff_id');

		$company_name = clean_and_escape($this->input->post('company_name'));
		$address      = clean_and_escape($this->input->post('address'));
		$address_2    = clean_and_escape($this->input->post('address_2'));

		$state_id = (int) $this->input->post('state_id');
		$city_id  = (int) $this->input->post('city_id');

		$pincode  = clean_and_escape($this->input->post('pincode'));
		$gst_name = clean_and_escape($this->input->post('gst_name'));
		$gst_no   = clean_and_escape($this->input->post('gst_no'));

		$owner_name     = clean_and_escape($this->input->post('owner_name'));
		$owner_email    = clean_and_escape($this->input->post('owner_email'));
		$owner_mobile   = clean_and_escape($this->input->post('owner_mobile'));
		$owner_whatsapp = clean_and_escape($this->input->post('owner_whatsapp'));

		$pm_name        = clean_and_escape($this->input->post('pm_name'));
		$pm_email       = clean_and_escape($this->input->post('pm_email'));
		$pm_mobile      = clean_and_escape($this->input->post('pm_mobile'));
		$pm_whatsapp    = clean_and_escape($this->input->post('pm_whatsapp'));

		$other_name     = clean_and_escape($this->input->post('other_name'));
		$other_email    = clean_and_escape($this->input->post('other_email'));
		$other_mobile   = clean_and_escape($this->input->post('other_mobile'));
		$other_whatsapp = clean_and_escape($this->input->post('other_whatsapp'));

		// digits only
		$pincode_digits = preg_replace('/[^0-9]/', '', $pincode);

		$owner_mobile_digits   = preg_replace('/[^0-9]/', '', $owner_mobile);
		$owner_whatsapp_digits = preg_replace('/[^0-9]/', '', $owner_whatsapp);

		$pm_mobile_digits      = preg_replace('/[^0-9]/', '', $pm_mobile);
		$pm_whatsapp_digits    = preg_replace('/[^0-9]/', '', $pm_whatsapp);

		$other_mobile_digits   = preg_replace('/[^0-9]/', '', $other_mobile);
		$other_whatsapp_digits = preg_replace('/[^0-9]/', '', $other_whatsapp);

		// names
		$state_name = ($state_id > 0) ? (string) $this->common_model->selectByidParam($state_id, 'state_list', 'state') : '';
		$city_name  = ($city_id > 0)  ? (string) $this->common_model->selectByidParam($city_id, 'city_list', 'district') : '';

		// --- phone rules (same as add) ---
		$ownerNums = array_values(array_unique(array_filter([$owner_mobile_digits, $owner_whatsapp_digits]))); // mandatory (as in add)
		$pmNums    = array_values(array_unique(array_filter([$pm_mobile_digits, $pm_whatsapp_digits]))); // optional
		$otherNums = array_values(array_unique(array_filter([$other_mobile_digits, $other_whatsapp_digits]))); // optional

		$conflict = array_values(array_intersect($ownerNums, $pmNums));
		if (!empty($conflict)) {
			return simple_json_output(["status" => 400, "message" => "Number {$conflict[0]} cannot be used in both Owner and Purchase Manager."]);
		}

		$conflict = array_values(array_intersect($ownerNums, $otherNums));
		if (!empty($conflict)) {
			return simple_json_output(["status" => 400, "message" => "Number {$conflict[0]} cannot be used in both Owner and Other."]);
		}

		$conflict = array_values(array_intersect($pmNums, $otherNums));
		if (!empty($conflict)) {
			return simple_json_output(["status" => 400, "message" => "Number {$conflict[0]} cannot be used in both Purchase Manager and Other."]);
		}

		$allNums = array_values(array_unique(array_merge($ownerNums, $pmNums, $otherNums)));

		$this->db->from('customer');
		$this->db->where('is_deleted', 0);
		$this->db->where('id !=', $id);

		$this->db->group_start();
		$this->db->or_where_in('owner_mobile', $allNums);
		$this->db->or_where_in('owner_whatsapp', $allNums);
		$this->db->or_where_in('pm_mobile', $allNums);
		$this->db->or_where_in('pm_whatsapp', $allNums);
		$this->db->or_where_in('other_mobile', $allNums);
		$this->db->or_where_in('other_whatsapp', $allNums);
		$this->db->group_end();

		$exists_phone = $this->db->get();
		if ($exists_phone->num_rows() > 0) {
			$exists = $exists_phone->row_array();

			$matchedNumber = '';
			$dbNums = [
				(string) $exists['owner_mobile'],
				(string) $exists['owner_whatsapp'],
				(string) $exists['pm_mobile'],
				(string) $exists['pm_whatsapp'],
				(string) $exists['other_mobile'],
				(string) $exists['other_whatsapp'],
			];

			foreach ($allNums as $n) {
				if (in_array((string) $n, $dbNums, true)) {
					$matchedNumber = (string) $n;
					break;
				}
			}

			$existing_staff = $this->common_model->selectByidParam($exists['added_by'], 'sys_users', 'first_name');

			return simple_json_output([
				"status"  => 400,
				"message" => "Phone/Whatsapp number {$matchedNumber} already exists in " . get_phrase($type) . "."
			]);
		}

		// staff name (same as add)
		$staff_name = $this->common_model->selectByidParam($staff_id, 'sys_users', 'first_name');

		// --- data (same as add) ---
		$data = array(
			"type"   				 => $type,
			"company_id"     => is_array($company_id) ? implode(',', $company_id) : (string) $company_id,
			"company_name"   => $company_name,
			"address"        => $address,
			"address_2"      => $address_2,

			"state_id"       => $state_id,
			"state_name"     => $state_name,
			"city_id"        => $city_id,
			"city_name"      => $city_name,

			"pincode"        => $pincode_digits,
			"gst_name"       => $gst_name,
			"gst_no"         => $gst_no,

			"owner_name"     => $owner_name,
			"owner_email"    => $owner_email,
			"owner_mobile"   => $owner_mobile_digits,
			"owner_whatsapp" => $owner_whatsapp_digits,

			"pm_name"        => $pm_name,
			"pm_email"       => $pm_email,
			"pm_mobile"      => $pm_mobile_digits,
			"pm_whatsapp"    => $pm_whatsapp_digits,

			"other_name"     => $other_name,
			"other_email"    => $other_email,
			"other_mobile"   => $other_mobile_digits,
			"other_whatsapp" => $other_whatsapp_digits,
		);

		if($type != 'leads') {
			$data["added_by_id"] = $staff_id;
			$data["added_by_name"] = $staff_name;
		}

		// changed fields for logs (only updated fields)
		$changed = [];
		foreach ($data as $key => $val) {
			$oldVal = isset($old[$key]) ? (string) $old[$key] : '';
			$newVal = (string) $val;

			if ($oldVal !== $newVal) {
				$changed[$key] = [
					"old" => $old[$key] ?? null,
					"new" => $val,
				];
			}
		}

		$this->db->where('id', $id);
		$updated = $this->db->update('customer', $data);

		if ($updated && !empty($changed)) {
			$logs = [
				"customer_id"    => $id,
				"action"         => "update",
				"label"          => json_encode(["badge" => "warning", "message" => get_phrase($type) . " Updated"]),
				"message"        => get_phrase($type) . " Updated By {$user_name}",
				"json"           => json_encode($changed),
				"added_by"       => $user_id,
				"added_by_name"  => get_phrase($user_name),
				"added_date"     => date("Y-m-d H:i:s"),
			];
			$this->db->insert('customer_log', $logs);
		}

		$this->session->set_flashdata('flash_message', get_phrase($type . '_updated_successfully'));
		$resultpost = array(
			"status"  => 200,
			"message" => get_phrase($type . '_updated_successfully'),
			"url"     => $this->agent->referrer(),
		);

		return simple_json_output($resultpost);
	}

	public function move_to_customer($id = "")
	{
		$id = (int) $id;

		$user_id   = (int) $this->session->userdata('super_user_id');
		$user_name = (string) $this->session->userdata('super_name');
		$type			 = $this->input->post('type');

		// Current row (to detect changes)
		$old = $this->db->where('id', $id)->where('is_deleted', 0)->get('customer')->row_array();
		if (empty($old)) {
			return simple_json_output([
				"status"  => 404,
				"message" => "Customer not found."
			]);
		}

		// --- inputs (same as add) ---
		$company_id = $this->input->post('company_id');
		$staff_id   = $this->input->post('staff_id');

		$company_name = clean_and_escape($this->input->post('company_name'));
		$address      = clean_and_escape($this->input->post('address'));
		$address_2    = clean_and_escape($this->input->post('address_2'));

		$state_id = (int) $this->input->post('state_id');
		$city_id  = (int) $this->input->post('city_id');

		$pincode  = clean_and_escape($this->input->post('pincode'));
		$gst_name = clean_and_escape($this->input->post('gst_name'));
		$gst_no   = clean_and_escape($this->input->post('gst_no'));

		$owner_name     = clean_and_escape($this->input->post('owner_name'));
		$owner_email    = clean_and_escape($this->input->post('owner_email'));
		$owner_mobile   = clean_and_escape($this->input->post('owner_mobile'));
		$owner_whatsapp = clean_and_escape($this->input->post('owner_whatsapp'));

		$pm_name        = clean_and_escape($this->input->post('pm_name'));
		$pm_email       = clean_and_escape($this->input->post('pm_email'));
		$pm_mobile      = clean_and_escape($this->input->post('pm_mobile'));
		$pm_whatsapp    = clean_and_escape($this->input->post('pm_whatsapp'));

		$other_name     = clean_and_escape($this->input->post('other_name'));
		$other_email    = clean_and_escape($this->input->post('other_email'));
		$other_mobile   = clean_and_escape($this->input->post('other_mobile'));
		$other_whatsapp = clean_and_escape($this->input->post('other_whatsapp'));

		// digits only
		$pincode_digits = preg_replace('/[^0-9]/', '', $pincode);

		$owner_mobile_digits   = preg_replace('/[^0-9]/', '', $owner_mobile);
		$owner_whatsapp_digits = preg_replace('/[^0-9]/', '', $owner_whatsapp);

		$pm_mobile_digits      = preg_replace('/[^0-9]/', '', $pm_mobile);
		$pm_whatsapp_digits    = preg_replace('/[^0-9]/', '', $pm_whatsapp);

		$other_mobile_digits   = preg_replace('/[^0-9]/', '', $other_mobile);
		$other_whatsapp_digits = preg_replace('/[^0-9]/', '', $other_whatsapp);

		// names
		$state_name = ($state_id > 0) ? (string) $this->common_model->selectByidParam($state_id, 'state_list', 'state') : '';
		$city_name  = ($city_id > 0)  ? (string) $this->common_model->selectByidParam($city_id, 'city_list', 'district') : '';

		// --- phone rules (same as add) ---
		$ownerNums = array_values(array_unique(array_filter([$owner_mobile_digits, $owner_whatsapp_digits]))); // mandatory (as in add)
		$pmNums    = array_values(array_unique(array_filter([$pm_mobile_digits, $pm_whatsapp_digits]))); // optional
		$otherNums = array_values(array_unique(array_filter([$other_mobile_digits, $other_whatsapp_digits]))); // optional

		$conflict = array_values(array_intersect($ownerNums, $pmNums));
		if (!empty($conflict)) {
			return simple_json_output(["status" => 400, "message" => "Number {$conflict[0]} cannot be used in both Owner and Purchase Manager."]);
		}

		$conflict = array_values(array_intersect($ownerNums, $otherNums));
		if (!empty($conflict)) {
			return simple_json_output(["status" => 400, "message" => "Number {$conflict[0]} cannot be used in both Owner and Other."]);
		}

		$conflict = array_values(array_intersect($pmNums, $otherNums));
		if (!empty($conflict)) {
			return simple_json_output(["status" => 400, "message" => "Number {$conflict[0]} cannot be used in both Purchase Manager and Other."]);
		}

		$allNums = array_values(array_unique(array_merge($ownerNums, $pmNums, $otherNums)));

		$this->db->from('customer');
		$this->db->where('is_deleted', 0);
		$this->db->where('id !=', $id);

		$this->db->group_start();
		$this->db->or_where_in('owner_mobile', $allNums);
		$this->db->or_where_in('owner_whatsapp', $allNums);
		$this->db->or_where_in('pm_mobile', $allNums);
		$this->db->or_where_in('pm_whatsapp', $allNums);
		$this->db->or_where_in('other_mobile', $allNums);
		$this->db->or_where_in('other_whatsapp', $allNums);
		$this->db->group_end();

		$exists_phone = $this->db->get();
		if ($exists_phone->num_rows() > 0) {
			$exists = $exists_phone->row_array();

			$matchedNumber = '';
			$dbNums = [
				(string) $exists['owner_mobile'],
				(string) $exists['owner_whatsapp'],
				(string) $exists['pm_mobile'],
				(string) $exists['pm_whatsapp'],
				(string) $exists['other_mobile'],
				(string) $exists['other_whatsapp'],
			];

			foreach ($allNums as $n) {
				if (in_array((string) $n, $dbNums, true)) {
					$matchedNumber = (string) $n;
					break;
				}
			}

			$existing_staff = $this->common_model->selectByidParam($exists['added_by'], 'sys_users', 'first_name');

			return simple_json_output([
				"status"  => 400,
				"message" => "Phone/Whatsapp number {$matchedNumber} already exists in " . get_phrase($type) . "."
			]);
		}

		// staff name (same as add)
		$staff_name = $this->common_model->selectByidParam($staff_id, 'sys_users', 'first_name');

		// --- data (same as add) ---
		$data = array(
			"type"   				 => $type,
			"company_id"     => is_array($company_id) ? implode(',', $company_id) : (string) $company_id,
			"company_name"   => $company_name,
			"address"        => $address,
			"address_2"      => $address_2,

			"state_id"       => $state_id,
			"state_name"     => $state_name,
			"city_id"        => $city_id,
			"city_name"      => $city_name,

			"pincode"        => $pincode_digits,
			"gst_name"       => $gst_name,
			"gst_no"         => $gst_no,

			"owner_name"     => $owner_name,
			"owner_email"    => $owner_email,
			"owner_mobile"   => $owner_mobile_digits,
			"owner_whatsapp" => $owner_whatsapp_digits,

			"pm_name"        => $pm_name,
			"pm_email"       => $pm_email,
			"pm_mobile"      => $pm_mobile_digits,
			"pm_whatsapp"    => $pm_whatsapp_digits,

			"other_name"     => $other_name,
			"other_email"    => $other_email,
			"other_mobile"   => $other_mobile_digits,
			"other_whatsapp" => $other_whatsapp_digits,

			"added_by_id" 	=> $staff_id,
			"added_by_name" => $staff_name,
			"is_move"			 	=> 1,
			"move_date"			=> date("Y-m-d H:i:s"),
		);

		// changed fields for logs (only updated fields)
		$changed = [];
		foreach ($data as $key => $val) {
			$oldVal = isset($old[$key]) ? (string) $old[$key] : '';
			$newVal = (string) $val;

			if ($oldVal !== $newVal) {
				$changed[$key] = [
					"old" => $old[$key] ?? null,
					"new" => $val,
				];
			}
		}

		$this->db->where('id', $id);
		$updated = $this->db->update('customer', $data);

		if ($updated && !empty($changed)) {
			$logs = [
				"customer_id"    => $id,
				"action"         => "move",
				"label"          => json_encode(["badge" => "success", "message" => "Moved to customer"]),
				"message"        => get_phrase($type) . " Updated By {$user_name}",
				"json"           => json_encode($changed),
				"added_by"       => $user_id,
				"added_by_name"  => get_phrase($user_name),
				"added_date"     => date("Y-m-d H:i:s"),
			];

			$this->db->insert('customer_log', $logs);
		}

		$this->session->set_flashdata('flash_message', get_phrase('successfully_moved_to_customer'));
		$resultpost = array(
			"status"  => 200,
			"message" => get_phrase('successfully_moved_to_customer'),
			"url"     => $this->agent->referrer(),
		);

		return simple_json_output($resultpost);
	}

	public function delete_customer($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('customer_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$updated = $this->db->update('customer', $data);

		if ($updated) {
			$user_id   = (int) $this->session->userdata('super_user_id');
			$user_name = (string) $this->session->userdata('super_name');

			$logs = [
				"customer_id"     => $id,
				"action"          => "delete",
				"label"          => json_encode(["badge" => "danger", "message" => "Customer Deleted"]),
				"message"         => "Customer Deleted By {$user_name}",
				"json"            => null,
				"added_by"        => $user_id,
				"added_by_name"   => get_phrase($user_name),
				"added_date"      => date("Y-m-d H:i:s"),
			];

			$this->db->insert('customer_log', $logs);
		}

		return simple_json_output($resultpost);
	}

	public function get_customer_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('customer');
	}

	public function replicate_customer()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('customer_replicated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$customer_id = clean_and_escape($this->input->post('customer_id'));
		$target_company_id = clean_and_escape($this->input->post('target_company_id'));

		if (empty($customer_id) || empty($target_company_id)) {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('invalid_request'),
			);
			return simple_json_output($resultpost);
		}

		// Get original customer data
		$original_customer = $this->get_customer_by_id($customer_id)->row_array();
		
		if (empty($original_customer)) {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('customer_not_found'),
			);
			return simple_json_output($resultpost);
		}

		// Check if customer already exists in target company
		$this->db->where('company_id', $target_company_id);
		$this->db->where('name', $original_customer['name']);
		$this->db->where('is_deleted', 0);
		$existing_customer = $this->db->get('customer')->row_array();
		
		if (!empty($existing_customer)) {
			$resultpost = array(
				"status" => 400,
				"message" => "Customer '" . $original_customer['name'] . "' already exists in the selected company.",
			);
			return simple_json_output($resultpost);
		}

		// Prepare data for new customer
		$data = array();
		$data['company_id'] = $target_company_id;
		$data['name'] = $original_customer['name'];
		$data['gst_name'] = $original_customer['gst_name'];
		$data['gst_no'] = $original_customer['gst_no'];
		$data['contact_name'] = $original_customer['contact_name'];
		$data['contact_no'] = $original_customer['contact_no'];
		$data['address'] = $original_customer['address'];
		$data['address_2'] = $original_customer['address_2'];
		$data['address_3'] = $original_customer['address_3'];
		$data['pincode'] = $original_customer['pincode'];
		$data['state_id'] = $original_customer['state_id'];
		$data['state_name'] = $original_customer['state_name'];
		$data['city_id'] = $original_customer['city_id'];
		$data['city_name'] = $original_customer['city_name'];
		$data['state_code'] = $original_customer['state_code'];
		
		$user_id = $this->session->userdata('super_user_id');
		$user_name = $this->session->userdata('super_name');
		$data['added_by_id'] = $user_id;
		$data['added_by_name'] = $user_name;
		$data['added_date'] = date("Y-m-d H:i:s");
		$data['is_deleted'] = 0;

		// Insert new customer
		$this->db->insert('customer', $data);
		$this->session->set_flashdata('flash_message', get_phrase('customer_replicated_successfully'));
		
		return simple_json_output($resultpost);
	}

	public function reassign_customer()
	{
		$user_id   = (int) $this->session->userdata('super_user_id');
		$user_name = (string) $this->session->userdata('super_name');

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('staff_assigned_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$customer_id = clean_and_escape($this->input->post('customer_id'));
		$target_company_id = clean_and_escape($this->input->post('target_company_id'));
		$target_staff_id = clean_and_escape($this->input->post('target_staff_id'));

		$original_customer = $this->get_customer_by_id($customer_id)->row_array();
		$staff_name = $this->common_model->selectByidParam($target_staff_id, 'sys_users', 'first_name');
		
		// Update customer company and staff
		$data = array(
			'added_by_id' => $target_staff_id,
			'added_by_name' => $staff_name,
		);

		if($original_customer['type'] == 'leads') {
			$data['status'] = 'fresh';
			$data['company_id'] = $target_company_id;
			$data['status_label'] = 'Fresh Lead';
			$action = "assign";
			$message = "Staff assign by {$user_name}";
			$json_data = [
				"status" 					=> 'fresh',
				"company_id" 					=>  $target_company_id,
				"status_label" 					=> 'Fresh Lead',
				"added_by_id" 		=> $target_staff_id,
				"added_by_name" => $staff_name,
			];
		} else {
			$action = "reassign";
			$message = "Customer Staff reassign by {$user_name}";
			$json_data = [
				"old_added_by_name" => $original_customer['added_by_name'],
				"added_by_name" => $staff_name,
				"old_added_by_id" => $original_customer['added_by_id'],
				"added_by_id" 		=> $target_staff_id,
			];
		}

		$this->db->where('id', $customer_id);
		$updated = $this->db->update('customer', $data);

		if ($updated) {
			$logs = [
				"customer_id"     => $customer_id,
				"action"          => $action,
				"label"          => json_encode(["badge" => "warning", "message" => ($action == "reassign") ? "Staff Reassign" : "Staff Assign"]),
				"message"         => $message,
				"json"            => json_encode($json_data),
				"added_by"        => $user_id,
				"added_by_name"   => get_phrase($user_name),
				"added_date"      => date("Y-m-d H:i:s"),
			];

			$this->db->insert('customer_log', $logs);
		}

		$this->session->set_flashdata('flash_message', get_phrase('staff_assigned_successfully'));
		return simple_json_output($resultpost);
	}

	public function follow_customer()
	{
		$user_id   = (int) $this->session->userdata('super_user_id');
		$user_name = (string) $this->session->userdata('super_name');

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('follow_up_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$customer_id = clean_and_escape($this->input->post('customer_id'));
		$status_date = clean_and_escape($this->input->post('status_date'));
		$status = clean_and_escape($this->input->post('status'));
		$status = explode(' | ', $status);
		$remark = clean_and_escape($this->input->post('remark'));
		
		// Update customer company and staff
		$data = array(
			'status_date' => ($status[0] == 'lost') ? date("Y-m-d H:i:s") : $status_date,
			'status' => $status[0],
			'status_label' => $status[1],
			'remark' => $remark,
		);

		$this->db->where('id', $customer_id);
		$updated = $this->db->update('customer', $data);

		if ($updated) {
			$action = $status[0];
			$message = "Leads Moved To " . get_phrase($status[0]) . " by {$user_name}";
			$json_data = [
				'status_date' => ($status[0] == 'lost') ? date("Y-m-d H:i:s") : $status_date,
				'status' => $status[0],
				'status_label' => $status[1],
				'remark' => $remark,
			];

			$logs = [
				"customer_id"     => $customer_id,
				"action"          => $action,
				"label"          => json_encode(["badge" => ($status[0] == 'lost') ? "danger" : "warning", "message" => ($status[0] == 'lost') ? "Lead Lost" : "Follow Up Added"]),
				"message"         => $message,
				"json"            => json_encode($json_data),
				"added_by"        => $user_id,
				"added_by_name"   => get_phrase($user_name),
				"added_date"      => date("Y-m-d H:i:s"),
			];

			$this->db->insert('customer_log', $logs);
		}

		$this->session->set_flashdata('flash_message', get_phrase('customer_reassigned_successfully'));
		return simple_json_output($resultpost);
	}

	public function get_customer()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (name like '%" . $keyword . "%' 
            OR contact_name like '%" . $keyword . "%')";
		endif;

		$data_type = $_REQUEST['type'];

		$user_id = $this->session->userdata('super_user_id');
		$type = $this->session->userdata('super_type');
		$company_id = $this->session->userdata('company_id');
		if($company_id && $type == 'staff') {
				if($data_type == 'leads') {
					$keyword_filter .= " AND added_by_id = '" . $user_id . "'";
				} else {
					$keyword_filter .= " AND FIND_IN_SET('" . $company_id . "', company_id) AND added_by_id = '" . $user_id . "'";
				}
		}
		
		if (isset($_REQUEST['status']) && $_REQUEST['status'] != ""):
			$status        = $_REQUEST['status'];
			$date =  date('Y-m-d');
			if($status == 'new') {
				$keyword_filter .= " AND status='fresh'";
			} elseif($status == 'today') {
				$keyword_filter .= " AND status='follow' AND (DATE(status_date) = '$date')";
			} elseif($status == 'upcoming') {
				$keyword_filter .= " AND status='follow' AND (DATE(status_date) > '$date')";
			} elseif($status == 'missed') {
				$keyword_filter .= " AND status='follow' AND (DATE(status_date) < '$date')";
			} elseif($status == 'lost') {
				$keyword_filter .= " AND status='lost'";
			} elseif($status == 'moved') {
				$data_type = 'customer';
				$keyword_filter .= " AND is_move = '1'";
			} else {
				$keyword_filter .= " AND (status='' OR status IS NULL)";
			}
		endif;

		$total_count = $this->db->query("SELECT id FROM customer WHERE (is_deleted='0') AND type='$data_type' $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, company_name, gst_name, gst_no, city_name, state_name, pincode, added_by_name, owner_name, owner_mobile, status, status_label, move_date FROM customer WHERE (is_deleted='0') AND type='$data_type' $keyword_filter ORDER BY id DESC LIMIT $start, $length");
		// echo $this->db->last_query(); exit();

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$badge        = '';
				if($item['status'] == 'fresh') {
					$badge        = '<span class="badge badge-primary">' . $item['status_label'] . '</span>';
				} elseif($item['status'] == 'follow') {
					$badge        = '<span class="badge badge-warning">' . $item['status_label'] . '</span>';
				} elseif($item['status'] == 'lost') {
					$badge        = '<span class="badge badge-danger">' . $item['status_label'] . '</span>';
				}

				$delete_url = "confirm_modal('" . base_url() . "inventory/customer/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/' . $data_type . '/edit/' . $id;
				$move_url = base_url() . 'inventory/' . $data_type . '/move/' . $id;
				$replicate_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/customer_replicate_modal/" . $id . "','Replicate Customer')";
				$reassign_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/customer_reinitiate_modal/" . $id . "','" . (($data_type == 'leads') ? "Assign" : "Reassign") . " Staff')";
				$followup_url = "smallAjaxModal('" . base_url() . "modal/popup_inventory/customer_followup_modal/" . $id . "','" . "Add Follow-Up')";
				$timeline_url = "showRightCanvas('" . base_url() . "modal/popup_inventory/canvas_customer_timeline/" . $id . "','Timeline')";

				$action = '';
				if($data_type == 'customer') {
					if($_REQUEST['status'] == 'moved') {
						$action .= '
							<a href="javascript:void(0);" onclick="' . $timeline_url . '" class=""  data-toggle="tooltip" data-bs-placement="top" title="Timeline"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" ><i class="fa fa-file" aria-hidden="true"></i></button></a>
						';
					} else {
						$action .= '
							<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
			
							<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
			
							<a href="javascript:void(0);" class="d-none" onclick="' . $replicate_url . '" data-toggle="tooltip" data-bs-placement="top" title="Replicate to Other Company"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
			
							<a href="javascript:void(0);" onclick="' . $reassign_url . '" data-toggle="tooltip" data-bs-placement="top" title="' . (($data_type == 'leads') ? "Assign" : "Reassign") . ' Staff"><button type="button" class="btn mr-1 mb-1 icon-btn-approved"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
			
							<a href="javascript:void(0);" onclick="' . $timeline_url . '" class=""  data-toggle="tooltip" data-bs-placement="top" title="Timeline"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" ><i class="fa fa-file" aria-hidden="true"></i></button></a>
						';
					}
				} else {
					if($_REQUEST['status'] == 'all') {
						$action .= '
							<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
			
							<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
			
							<a href="javascript:void(0);" onclick="' . $reassign_url . '" data-toggle="tooltip" data-bs-placement="top" title="' . (($data_type == 'leads') ? "Assign" : "Reassign") . ' Staff"><button type="button" class="btn mr-1 mb-1 icon-btn-approved"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
						';
					} else if ($_REQUEST['status'] == 'new') {
						$action .= '
							<a href="javascript:void(0);" onclick="' . $followup_url . '" data-toggle="tooltip" data-bs-placement="top" title="Follow-Up"><button type="button" class="btn mr-1 mb-1 icon-btn-approved"><i class="fa fa-list-alt" aria-hidden="true"></i></button></a>
						';
					} else if (in_array($_REQUEST['status'], ['today', 'upcoming', 'missed'])) {
						$action .= '
							<a href="' . $move_url . '" data-toggle="tooltip" data-bs-placement="top" title="Move To Customer"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-chevron-right" aria-hidden="true"></i></button></a>
						';
					}

					$action .= '
						<a href="javascript:void(0);" onclick="' . $timeline_url . '" class=""  data-toggle="tooltip" data-bs-placement="top" title="Timeline"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" ><i class="fa fa-file" aria-hidden="true"></i></button></a>
					';
				}

				$log = $this->common_model->getRowById('customer_log', 'added_by_name', ['customer_id' => $item['id'], 'action' => 'create']);

				$data[] = array(
					"sr_no"       		=> ++$start,
					"id"          		=> $item['id'],
					"name"        		=> $item['company_name'],
					"gst_name"				=> ($item['gst_name']) ? $item['gst_name'] : '-',
					"gst_no"					=> ($item['gst_no']) ? $item['gst_no'] : '-',
					"owner_name"			=> ($item['owner_name']) ? $item['owner_name'] : '-',
					"owner_no"				=> ($item['owner_mobile']) ? $item['owner_mobile'] : '-',
					"city_name"				=> ($item['city_name']) ? $item['city_name'] : '-',
					"state_name"			=> ($item['state_name']) ? $item['state_name'] : '-',
					"pincode"					=> ($item['pincode']) ? $item['pincode'] : '-',
					"staff"						=> ($item['added_by_name']) ? $item['added_by_name'] : '-',
					"move_date"				=> date('d-m-Y', strtotime($item['move_date'])),
					"status"					=> $badge,
					"added_by_name"		=> $log['added_by_name'] ?? '-',
					"action"      		=> $action,
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

	public function get_staff_by_company_id($company_id) {
		$result = ["status" => 200, "message" => "Staff found Successfully"];
		$query = $this->db->query("SELECT id, first_name as name FROM sys_users WHERE (is_deleted='0' AND type='staff' AND FIND_IN_SET('$company_id', company_id)) ORDER BY id ASC");
		if ($query->num_rows() > 0) {
			$result['data'] = $query->result_array();
			return simple_json_output($result);
		} else {
			$result['data'] = array();
			return simple_json_output($result);
		}
	}

	public function get_staff_by_company_ids($company_id, $res = 'json') {
		$result = ["status" => 200, "message" => "Staff found Successfully"];

		$parts = [];
		foreach ($company_id as $id) {
			$parts[] = "FIND_IN_SET(" . $this->db->escape((string)$id) . ", company_id)";
		}

		$whereCompany = !empty($parts) ? '(' . implode(' OR ', $parts) . ')' : '1=0';
		$query = $this->db->query("SELECT id, first_name as name FROM sys_users WHERE (is_deleted='0' AND type='staff' AND $whereCompany) ORDER BY id ASC");

		if ($query->num_rows() > 0) {
			$result['data'] = $query->result_array();
			return ($res == 'array') ? $result['data'] : simple_json_output($result);
		} else {
			$result['data'] = array();
			return ($res == 'array') ? $result['data'] : simple_json_output($result);
		}
	}
	/* Customer End */


	/* Sales Order Start*/

	public function update_order_no($order_no)
	{
		$order_no = explode('/', $order_no);
		$pre = $order_no[0];
		$year = $order_no[1];
		$number = $order_no[2];
		$query = $this->db->query("SELECT id FROM sales_order_no WHERE year='$year' ORDER BY id DESC LIMIT 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$id = $row['id'];
			$data = array();
			$data['prefix'] = $pre;
			$data['year'] = $year;
			$data['number'] = $number;
			$this->db->where('id', $id);
			$this->db->update('sales_order_no', $data);
		} else {
			$data = array();
			$data['prefix'] = $pre;
			$data['year'] = $year;
			$data['number'] = $number;
			$this->db->insert('sales_order_no', $data);
		}
	}

	public function add_sales_order()
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_order_added_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);

			$order_no = clean_and_escape($this->input->post('order_no'));
			if ($order_no != '') {
				$check_order_no = $this->check_duplication('on_create', 'order_no', $order_no, 'sales_order');
			} else {
				$check_order_no  = true;
			}

			if ($check_order_no == false) {
				$this->session->set_flashdata('error_message', get_phrase('order_no_duplication'));
				$resultpost = array(
					"status" => 400,
					"message" => 'Order No Duplication'
				);
			} else {
				$customer_id = $this->input->post('customer_id');
				if ($customer_id != '') {
					$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'contact_name');
				} else {
					$customer_name = '';
				}
				$warehouse_id = $this->input->post('warehouse_id');
				if ($warehouse_id != '') {
					$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
				} else {
					$warehouse_name = '';
				}

				$company_id = $this->input->post('company_id');
				$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

				$round_of       	= ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
				$gst_type       	= clean_and_escape($this->input->post('gst_type'));

				$other_charges_name   = clean_and_escape($this->input->post('other_charges_name'));
				$other_charges_amount = ($this->input->post('other_charges_amount') != '') ? $this->input->post('other_charges_amount') : 0;

				$data = array();
				$data['order_no']          = $order_no;
				$data['refrence_no']       = clean_and_escape($this->input->post('refrence_no'));
				$data['date']     		   = ($this->input->post('date'));
				$data['customer_id']       = $customer_id;
				$data['customer_name']     = $customer_name;
				$data['warehouse_id']      = $warehouse_id;
				$data['warehouse_name']    = $warehouse_name;
				$data['company_id']      = $company_id;
				$data['company_name']    = $company_name;
				$data['remark'] 		   = ($this->input->post('remark'));
				$data['narration']         = ($this->input->post('narration'));
				$data['gst_type']     	   	= $gst_type;
				$data['other_charges_name']   = $other_charges_name;
				$data['other_charges_amount'] = $other_charges_amount;;
				$data['added_by_id']          = $this->session->userdata('super_user_id');
				$data['added_by_name']        = $this->session->userdata('super_name');
				$data['added_date']   	      = date("Y-m-d H:i:s");
				if ($this->db->insert('sales_order', $data)) {

					$order_id = $this->db->insert_id();
					$this->update_order_no($order_no);

					$product_id_arr     = ($this->input->post('product_id'));
					$total_amount_arr   = ($this->input->post('total_amount'));
					$quantity_arr       = ($this->input->post('quantity'));
					$porder_id_arr      = ($this->input->post('porder_id'));
					$x_value_arr        = ($this->input->post('x_value'));
					$customer_name_arr        = ($this->input->post('customer_name'));
					$pincode_arr        = ($this->input->post('pincode'));
					$state_arr        = ($this->input->post('state'));

					$basic_value = $total_gst_amount = $net_sales_value_1 = $transport_gst_amount = $igst = $net_total = $other_tax = $price_after_discount = $gst_amt = $price_after_gst = $price_total = $grand_total = 0;
					$order_items_logs = array();
					//echo json_encode($quantity_arr);exit();
					for ($i = 0; $i < count($product_id_arr); $i++) {
						if ($quantity_arr[$i] > 0) {
							$data_p = array();
							$xpro 			=  explode('|', $product_id_arr[$i]);
							$product_id 	= $xpro[0];
							$size_id 	= $xpro[1];

							$inv_prod = $this->db->where('product_id', $product_id)->where('size_id', $size_id)->get('inventory');
							if ($inv_prod->num_rows() > 0) {
								$inv_prod = $inv_prod->row_array();
								$item_code 	= $inv_prod['item_code'];

								$total_amount 	= $total_amount_arr[$i];
								$quantity 	= $quantity_arr[$i];
								$x_value 	= $x_value_arr[$i];
								$product    	= $this->crud_model->get_raw_products_by_id($product_id)->row_array();
								$product_name = $product['name'];
								$data_product = array();
								$data_product = array(
									'order_id'          => $order_id,
									'product_id'        => $product_id,
									'item_code'         => $item_code,
									'product_name'      => $product['name'],
									'product_order_id'  => $porder_id_arr[$i],
									'customer_name'     => $customer_name_arr[$i],
									'pincode'           => $pincode_arr[$i],
									'state'             => $state_arr[$i],
									'qty'               => $quantity,
									'size_id'           => $size_id,
									'size_name'         => $inv_prod['size_name'],
									'group_id'          => $inv_prod['group_id'],
									'color_id'          => $inv_prod['color_id'],
									'color_name'        => $inv_prod['color_name'],
									'total_amount'      => $total_amount,
								);

								$this->db->insert('sales_order_product', $data_product);
								$order_product_id = $this->db->insert_id();
								$basic_value += $total_amount;

								$batch_no 	= NULL;
								$batch_qty 	= intval($quantity);
								if ($batch_qty > 0) {
									$data_product_bat = array();
									$data_product_bat = array(
										'order_id'          => $order_id,
										'order_product_id'  => $order_product_id,
										'batch_no'      		=> $batch_no,
										'batch_qty'       	=> $batch_qty,
									);

									$this->db->insert('sales_order_product_batch', $data_product_bat);

									// Inventory start
									$check_inv = $this->db->query("SELECT id,quantity FROM inventory WHERE product_id = '$product_id' and size_id = '$size_id' and warehouse_id = '$warehouse_id' and quantity > 0 limit 1");

									$stocks = 0;
									if ($check_inv->num_rows() > 0) {
										$stocks = $check_inv->row()->quantity ?? 0;
									}

									if ($stocks < $batch_qty || $stocks == 0) {
										//$product_name = $product_name;
										//$this->db->query("UNLOCK TABLES");
										throw new Exception('Insufficient stock for ' . $product_name . '. Available Live Qty: ' . $stocks . '.');
									} else {
										$row_inv = $check_inv->row_array();
										$old_id = $row_inv['id'];
										$old_quantity = intval($row_inv['quantity']);

										$final_qty = $old_quantity - $batch_qty;

										$data_history = array();
										$data_history = array(
											'quantity'       	=> $final_qty,
										);

										$this->db->where('id', $old_id);
										$this->db->update('inventory', $data_history);

										$limit_history = array();
										$limit_history['parent_id']   	  	= $old_id;
										$limit_history['warehouse_id']   	= $warehouse_id;
										$limit_history['warehouse_name']   	= $warehouse_name;
										$limit_history['product_id']   	  	= $product_id;
										$limit_history['product_order_id']  = $porder_id_arr[$i];
										$limit_history['product_name']   	= $product_name;
										$limit_history['item_code']   	  	= $item_code;
										$limit_history['size_id']   	  	= $size_id;
										$limit_history['size_name']         = $inv_prod['size_name'];
										$limit_history['group_id']          = $inv_prod['group_id'];
										$limit_history['color_id']          = $inv_prod['color_id'];
										$limit_history['color_name']        = $inv_prod['color_name'];
										$limit_history['sku']               = $inv_prod['sku'];
										$limit_history['categories']        = $inv_prod['categories'];
										$limit_history['order_id'] 	   		= $order_id;
										$limit_history['status'] 	   		= 'out';
										$limit_history['quantity'] 			= $batch_qty;
										$limit_history['received_date']     = $this->input->post('date');
										$limit_history['batch_no'] 			= NULL;
										$limit_history['added_by_id'] 		= $this->session->userdata('super_user_id');
										$limit_history['added_by_name'] 	= $this->session->userdata('super_name');
										$limit_history['added_date'] 		= date("Y-m-d H:i:s");
										$this->db->insert('inventory_history', $limit_history);
									}
									// Inventory End
								}
							} else {
								throw new Exception('No Product Found');
							}
						}
					}

					$net_sales_value_1 = floatval($basic_value);

					if ($gst_type == 'CGST/SGST') {
						$gst_total   = price_format_decimal($this->input->post('central_gst')) + price_format_decimal($this->input->post('state_gst'));
						$central_gst = price_format_decimal($this->input->post('central_gst'));
						$state_gst   = price_format_decimal($this->input->post('state_gst'));
						$igst 	     = 0;
						$igst_per 	     = 0;
						$cgst_per 	     = $this->input->post('cgst_per');
						$sgst_per 	     = $this->input->post('sgst_per');
					} else {
						$gst_total   = price_format_decimal($this->input->post('igst'));
						$central_gst = 0;
						$state_gst   = 0;
						$cgst_per   = 0;
						$sgst_per   = 0;
						$igst 	     = price_format_decimal($this->input->post('igst'));
						$igst_per 	     = $this->input->post('igst_per');
					}

					$net_sales_value_2 = $net_sales_value_1 + $total_gst_amount;
					$grand_total = $net_sales_value_2 + $round_of +  $other_charges_amount;
					$update_data = array();
					$update_data = array(
						'net_sales_value_1' => $net_sales_value_1,
						'igst_per' => $igst_per,
						'cgst_per' => $cgst_per,
						'sgst_per' => $sgst_per,
						'central_gst' => $central_gst,
						'state_gst' => $state_gst,
						'igst' => $igst,
						'gst_total' => $gst_total,
						'net_sales_value_2' => $net_sales_value_2,
						'round_of' 	  => $round_of,
						'grand_total' => $grand_total,
					);
					$this->db->where('id', $order_id);
					$this->db->update('sales_order', $update_data);

					$this->session->set_flashdata('flash_message', get_phrase('sales_order_added_successfully'));
				} else {
					$resultpost = array(
						"status" => 400,
						"message" => get_phrase('something_went_wrong')
					);
					$this->session->set_flashdata('error_message', get_phrase('something_went_wrong'));
				}

				if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					$resultpost = array(
						"status" => 400,
						"message" => "Error occurred while adding Sales Order",
					);
				} else {
					$this->db->trans_commit();
					$resultpost = array(
						"status" => 200,
						"message" => get_phrase('sales_order_added_successfully'),
						"url" => $this->session->userdata('previous_url'),
					);
				}
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" =>  "Exception occurred: " . $e->getMessage(),
			);
		}
		return simple_json_output($resultpost);
	}

	public function get_sales_order()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (company_name like '%" . $keyword . "%' 
            OR refrence_no like '%" . $keyword . "%'
            OR order_no like '%" . $keyword . "%')";
		endif;
		
		if (isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != ""):
			$keyword        = $_REQUEST['customer_id'];
			$keyword_filter .= " AND (customer_id = '" . $keyword . "')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
			}
		}

		$total_count = $this->db->query("SELECT id FROM sales_order WHERE (is_deleted='0') $keyword_filter ORDER BY date DESC")->num_rows();
		$query = $this->db->query("SELECT id,order_type,order_no,refrence_no,date,customer_id,warehouse_name,grand_total,company_name,remark FROM sales_order WHERE (is_deleted='0') $keyword_filter ORDER BY date DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_type = $item['order_type'];
				$customer_id = $item['customer_id'];

				$view_url = base_url() . 'inventory/sales-order/view/' . $id;
				$products_url = base_url() . 'inventory/sales-order/products/' . $id;
				$not_url = base_url() . 'inventory/sales-order/not-uploaded/' . $id;
				$action = '';

				$delete_url = base_url() . 'inventory/sales_order/delete/' . $id;

				if ($customer_id != '') {
					$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'name');
				} else {
					$customer_name = '';
				}

				$action .= '
    			 <a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
    			 ';
				//$action .='
				//<a href="'.$products_url.'" data-toggle="tooltip" data-bs-placement="top" title="Products"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-edit" aria-hidden="true"></i></button></a>
				//';


				if ($order_type == 'excel') {
					$action .= '<a href="' . $not_url . '" data-toggle="tooltip" data-bs-placement="top" title="Not Upload"><button type="button" class="btn mr-2 mb-1 icon-btn-edit"><i class="fa fa-times" aria-hidden="true"></i></button></a>';
				}
				// $action .= '<a href="#" onclick="confirm_modal(\'' . $delete_url . '\',\'Are you sure want to delete!\')" data-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Delete" aria-label="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';


				$qty = 0;
				$query2 = $this->db->query("SELECT SUM(batch_qty) as qty FROM sales_order_product_batch WHERE (order_id='$id') group by order_id limit 1");
				if ($query2->num_rows() > 0) {
					$row2 = $query2->row_array();
					$qty = $row2['qty'];
				}

				$total_pro = $this->db->query("SELECT id FROM sales_order_product WHERE (order_id='$id') ")->num_rows();
				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"order_no"        => $item['order_no'],
					"refrence_no"        => $item['refrence_no'],
					"customer_name"        => $customer_name,
					"warehouse_name"        => $item['warehouse_name'],
					"company_name"        => ($item['company_name'] != '' && $item['company_name'] != null) ? $item['company_name'] : '-',
					"grand_total"        => $item['grand_total'],
					"date"        => date('d M, Y', strtotime($item['date'])),
					"total_pro"      => $total_pro,
					"qty"      => $qty,
					"remark"      => $remark,
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

	public function get_sales_order_products($id)
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (product_name like '%" . $keyword . "%' 
            OR size_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM sales_order_product WHERE (order_id='$id') $keyword_filter group by item_code ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT GROUP_CONCAT(id) as id,product_id,product_name,product_order_id,group_id,size_name,color_name,item_code,SUM(total_amount) as  total_amount FROM sales_order_product WHERE (order_id='$id') $keyword_filter group by item_code ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$op_id          = $item['id'];
				$product_id     = $item['product_id'];
				$product_name   = $item['product_name'];
				$item_code      = $item['item_code'];
				$size_name      = $item['size_name'];
				$color_name     = $item['color_name'];
				$order_id       = $item['product_order_id'];
				$total_amount   = $item['total_amount'];
				$group_id       = $item['group_id'];

				$qty = 0;
				$query_1 = $this->db->query("SELECT SUM(batch_qty) as qty FROM sales_order_product_batch WHERE (order_id='$id') and FIND_IN_SET(order_product_id,'$op_id') group by order_id");

				if ($query_1->num_rows() > 0) {
					$row_1 = $query_1->row_array();
					$qty = $row_1['qty'];
				}

				$action = '<input type="checkbox" name="id[]" class="product-id" value="' . $op_id . '" onchange="getReturnId(this)">';
				$model_no = $this->common_model->selectByidParam($product_id, 'raw_products', 'item_code');

				$data[] = array(
					"sr_no"         => $action,
					// "sr_no"       => ++$start,
					"product_name"  => $product_name,
					"order_id"      => $order_id,
					"size_name"     => $size_name,
					"color_name"    => $color_name,
					"qty"           => $qty,
					"total_amount"  => $total_amount,
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



	function import_orders_excel_insert($fetchData)
	{
		//echo json_encode($fetchData);exit();
		$is_complete = 0;
		$curr_data = date("Y-m-d H:i:s");
		$count = 0;
		$returnData = array();
		$unique_id = generate_unique_id();

		$returnData = array();
		foreach ($fetchData as $item) {
			$order_date = date('Y-m-d', strtotime($item['dispense_date']));
			//echo $item['order_date'].'<br/>';
			//echo  $order_date;exit();
			$product_name = $item['sku_code'];
			$quantity = $item['quantity'];
			$amount = $item['amount'];
			$batch_no = $item['batch_no'];
			$order_no = $item['order_id'];
			$customer_id = $item['customer_id'];
			$warehouse_id = $item['warehouse_id'];
			$company_id = $item['company_id'];
			$refrence_no = $item['refrence_no'];
			$customer_name = $item['customer_name'];
			$pincode = $item['pincode'];
			$state = $item['state'];
			$size = $item['size'];

			$data = array();
			$data = array(
				'is_move' => '0',
				'unique_id' => $unique_id,
				'customer_id' => $customer_id,
				'company_id' => $company_id,
				'warehouse_id' => $warehouse_id,
				'order_date' => $order_date,
				'product_name' => $product_name,
				'quantity' => $quantity,
				'amount' => $amount,
				'batch_no' => $batch_no,
				'order_no' => $order_no,
				'refrence_no' => $refrence_no,
				'created_at' => $curr_data,
				'customer_name' => $customer_name,
				'pincode' => $pincode,
				'state' => $state,
				'size' => $size,
			);

			$data    = $this->security->xss_clean($data);
			if ($this->common_model->insert($data, 'excel_orders')) {
				$is_complete = 1;
			} else {
				$is_complete = 0;
				$returnData[] = array(
					'dispense_date' => $order_date,
					'sku_code'      => $product_name,
					'quantity'      => $quantity,
					'amount'        => $amount,
					'batch_no'      => $batch_no,
					'customer_name' => $customer_name,
					'pincode'       => $pincode,
					'state'         => $state,
					'size'          => $size,
					'order_id'      => $order_no,
				);
			}
		}

		if ($is_complete == '1') {
			$result = $this->add_inventory_data($unique_id);
			// 			echo json_encode($result);exit();
			if ($result['status'] == 200) {
				$returnData = array(
					'status' => '200',
					'message' => 'success',
					'message' => 'success',
					'returnData' => array(),
				);
			} else {
				$returnData = array(
					'status' => '400',
					'message' => 'error 1  | ' . $result['message'],
					'returnData' => $result['returnData'],
				);
			}
		} else {
			$returnData = array(
				'status' => '400',
				'message' => 'error 2',
				'returnData' => $returnData,
			);
		}

		return $returnData;
	}

	function import_purchase_order_items_excel_insert($fetchData)
	{
		$is_complete = 0;
		$curr_data = date("Y-m-d H:i:s");
		$count = 0;
		$Images_arr = array();
		$returnData = array();
		$unique_id = generate_unique_id();

		$total_leads = 0;
		foreach ($fetchData as $item) {
			$product_name = $item['product_name'];
			$rate = 0;
			$quantity = $item['quantity'];
			$cartoon = 0;
			$gst_percentage = $item['gst_percentage'];

			$data = array();
			$data = array(
				'unique_id' => $unique_id,
				'product_name' => $product_name,
				'rate' => $rate,
				'quantity' => $quantity,
				'cartoon' => $cartoon,
				'gst_percentage' => $gst_percentage,
				'created_at' => $curr_data
			);

			$data = $this->security->xss_clean($data);
			$this->common_model->insert($data, 'excel_po_items');
		}

		$final_data = array();
		$query = $this->db->query("SELECT * FROM excel_po_items WHERE (unique_id='$unique_id') ");
		foreach ($query->result_array() as $item) {
			$item_code = $item['product_name'];
			$quantity = $item['quantity'];
			$cartoon = $item['cartoon'];
			$gst_percentage = $item['gst_percentage'];

			$check = $this->db->query("SELECT id,name,costing_price,item_code FROM raw_products WHERE item_code='$item_code' limit 1");

			if ($check->num_rows() == 0) {
				$others = $this->db->query("SELECT id,product_id,sku_code FROM product_sku WHERE sku_code='$item_code' limit 1");
				if ($others->num_rows() > 0) {
					$row_oth = $others->row_array();
					$check = $this->db->query("SELECT id,name,costing_price,item_code FROM raw_products WHERE id='" . $row_oth['product_id'] . "' limit 1");
				}
			}

			if ($check->num_rows() > 0) {
				$row_c = $check->row_array();
				$rate = $row_c['costing_price'];
				$product_id = $row_c['id'];
				$sku_name = $row_c['item_code'];
				$product_name = $row_c['name'];
				$basic_amount = $rate * $quantity;
				$gst_amount = ($basic_amount * ($gst_percentage / 100));
				$total_amount = ($basic_amount + $gst_amount);

				$real_name = $sku_name . ' - ' . $product_name;
				$sku_ = $item_code;
				$final_data[] = array(
					"product_id" => $product_id,
					"product_name" => $real_name,
					"sku_name" => $sku_,
					"item_code" => $sku_name,
					"rate" => $rate,
					"quantity" => $quantity,
					"cartoon" => $cartoon,
					"basic_amount" => $basic_amount,
					"gst" => $gst_percentage,
					"gst_amount" => $gst_amount,
					"total_amount" => $total_amount,
				);
			}
		}

		$action = '';
		$where = array('is_deleted' => '0');
		$products_list     = $this->common_model->selectWhere('raw_products', $where, 'ASC', 'name');
		foreach ($final_data as $key => $f_data) {
			$key++;
			$x_id = "'" . $key . "'";

			$other_sku = ($f_data['sku_name'] == $f_data['item_code']) ? '-' : $f_data['sku_name'];

			$action .= '<div class="d-block mt-2 element-1 fx-border" id="product_' . $key . '" data-id="' . $key . '">
                                 <b class="jsr-no">' . $key . '</b>  
                                 <div class="flex-grow-1 px-0 ml-15">
                                    <div class="row">
                                       <div class="col-md-3">
                                          <input type="hidden" name="pr_gst[]" id="pr_gst_' . $key . '" value="0">
                                          <input type="hidden" name="pr_gst_amount[]" id="pr_gst_amount_' . $key . '" value="0">
                                          <div class="form-group">
                                             <label>Select SKU Code - Color<span class="required">*</span></label>
                                             <select class="form-control select2 product_id" readonly  name="product_id[]"  id="product_id_' . $key . '" data-toggle="select2" onchange="get_product_details(this.value,' . $x_id . ');"  required>
                                                <option value="">Select SKU Code - Color</option>';

			foreach ($products_list as $item) {
				$selected = ($item->id == $f_data['product_id']) ? 'selected' : '';
				$action .= ' <option value="' . $item->id . '" ' . $selected . '>' . $item->item_code . ' - ' . $item->color_name . '</option>';
			}

			$action .= '</select>
                                          </div>
                                       </div>
                                       <div class="col-md-1 pl-0">
                                          <div class="form-group">
                                             <label>Other SKU</label>
                                             <input type="text"  id="other_' . $key . '"   value="' . $other_sku . '" placeholder="Other SKU" class="form-control" readonly >
                                          </div>
                                       </div>
                                       <div class="col-md-1 pl-0">
                                          <div class="form-group">
                                             <label>Rate <span class="required">*</span></label>
                                             <input type="number" step="any" id="rate_' . $key . '" name="rate[]" readonly onkeyup="get_total_amount(this.value,' . $x_id . ')" value="' . $f_data['rate'] . '" placeholder="Unit Price" class="form-control" required="" >
                                          </div>
                                       </div>
                                       <div class="col-md-1 pl-0">
                                          <div class="form-group">
                                             <label>Qty <span class="required">*</span></label>
                                             <input type="number" step="any" id="quantity_' . $key . '" name="quantity[]" readonly placeholder="Qty" onkeyup="get_discount_amount(this.value,' . $x_id . ')" value="' . $f_data['quantity'] . '" class="form-control" required="">
                                          </div>
                                       </div>
                                       <div class="col-md-1 pl-0">
                                          <div class="form-group">
                                             <label>Amount <span class="required">*</span></label>
                                             <input type="number" step="any" id="basic_amount_' . $key . '" name="basic_amount[]"  readonly onkeyup="get_total_amount(this.value,' . $x_id . ')" value="' . $f_data['basic_amount'] . '" placeholder="Amount" class="form-control" required="" readonly>
                                          </div>
                                       </div>
                                       <div class="col-md-1 pl-0">
                                          <div class="form-group">
                                             <label>Gst(%) <span class="required">*</span></label>
                                             <input type="number" step="any" id="gst_' . $key . '" name="gst[]" readonly onkeyup="get_total_amount(this.value,' . $x_id . ')" value="' . $f_data['gst'] . '" class="form-control" >
                                          </div>
                                       </div>
                                       <div class="col-md-2 pl-0">
                                          <div class="form-group">
                                             <label>Gst Amount <span class="required">*</span></label>
                                             <input type="number" step="any" id="gst_amount_' . $key . '" name="gst_amount[]" readonly onkeyup="get_total_amount(this.value,' . $x_id . ')" value="' . $f_data['gst_amount'] . '" class="form-control" readonly>
                                          </div>
                                       </div>
                                       <div class="col-md-2 pl-0">
                                          <div class="form-group">
                                             <label>Total Amount <span class="required">*</span></label>
                                             <input type="number" step="any" id="total_amount_' . $key . '" name="total_amount[]"   value="' . $f_data['total_amount'] . '" class="form-control" readonly>
                                          </div>
                                       </div>
                                       <div class="col-md-1 m-stock-avl pl-0">
                                          <label>&nbsp;</label><br/>
                                          <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light" style="" name="button" onclick="removeRequirement(this,' . $x_id . ')"> <i class="fa fa-minus" aria-hidden="true"></i> </button>                      
                                       </div>
                                    </div>
                                 </div>
                              </div>';
		}

		$returnData = array(
			'status' => '200',
			'message' => 'Excel data inserted in database',
			'unique_id' => $unique_id,
			'action' => $action,
		);

		return $returnData;
	}

	function import_sales_payment_items_excel_insert($fetchData, $warehouse_id, $type)
	{
		$is_complete = 0;
		$curr_data = date("Y-m-d H:i:s");
		$count = 0;
		$Images_arr = array();
		$returnData = array();
		$unique_id = generate_unique_id();

		$total_leads = 0;
		foreach ($fetchData as $item) {
			$ord_id = $item['ord_id'];
			$product = $item['product'];
			$size = $item['size'];
			$amount = $item['amount'];

			$data = array();
			$data = array(
				'type' => $type,
				'unique_id' => $unique_id,
				'product' => $product,
				'batch_no' => NUll,
				'quantity' => 0,
				'amount' => $amount,
				'size' => $size,
				'ord_id' => $ord_id,
				'created_at' => $curr_data
			);

			$data = $this->security->xss_clean($data);
			$this->common_model->insert($data, 'excel_return_stock');
		}

		$final_data = array();
		$return_data = array();
		$query = $this->db->query("SELECT * FROM excel_return_stock WHERE (unique_id='$unique_id')");

		$order_ids = [];
		$skus = [];
		$sizes = [];

		foreach ($query->result_array() as $item) {
			$item_code = $item['product'];
			$quantity = $item['quantity'];
			$size = explode(' | ', $item['size']);
			$ord_id = $item['ord_id'];
			$batch_no = NULL;
			$amount = $item['amount'];

			$sop_id = 0;
			$customer = '';
			$sale_qty = 0;

			$sales = $this->db->where('product_order_id', $ord_id)->where('item_code', $item_code)->where('size_id', $size[0])->where('is_paid', 0)->get('sales_order_product');
			$flag = 0;
			$reason = "";
			if ($sales->num_rows() > 0) {
				$sales = $sales->row_array();

				$sop_id = $sales['id'];
				$customer = $sales['customer_name'];
				$sale_qty = $sales['qty'] - $sales['return_qty'];
				if ($sale_qty > 0) {
					if (in_array($ord_id, $order_ids)  && in_array($item_code, $skus) && in_array($size[0], $sizes)) {
						$flag = 1;
						$reason = "Order Id and Product cannot be the same";
					} else {
						$order_ids[] = $ord_id;
						$skus[] = $item_code;
						$sizes[] = $size[0];
					}
				} else {
					$flag = 1;
					$reason = "No Sale Qty available";
				}
			} else {
				$flag = 1;
				$reason = "No Sales Record Found";
			}

			if ($flag == 0) {
				$available_quantity = $row_c['quantity'];
				$final_data[] = array(
					"ord_id" => $ord_id,
					"product_id" => $sop_id,
					"product_name" => $item_code . ' - ' . $size[1],
					"customer" => $customer,

					"sale_quantity" => $sale_qty,
					"quantity" => $quantity,
					"amount" => $amount,
					"reason" => $reason,
				);
			} else {
				$return_data[] = array(
					"ord_id" => $ord_id,
					"product_id" => $sop_id,
					"product_name" => $item_code . ' - ' . $size[1],
					"customer" => $customer,

					"sale_quantity" => $sale_qty,
					"quantity" => $quantity,
					"amount" => $amount,
					"reason" => $reason,
				);
			}
		}

		$action = '';
		// 		$products_list = $this->get_product_id_by_warehouse($warehouse_id);
		// 		echo json_encode($final_data);exit();
		if (count($final_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">						
						<table class="table table-striped table-bordered mn-table" id="requirement_area">
						<thead>
						<tr>
							<th>
							    <p>Order ID </p>
							</th>
						    <th>
							    <p>Product </p>
						    </th>
							<th style="width: 95px">
							    <p>Customer</p>
							</th>
							<th style="width: 95px">
							    <p>Sale Quantity</p>
							</th>
						
							<th style="width: 95px">
							    <p>Amount</p>
							</th>
							<th style="width: 95px">
							    <p>Action</p>
							</th>
						 </tr>
						</thead>
						<tbody class="element-1 new-table" id="product_' . $key . '">';

			foreach ($final_data as $key => $f_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td style="width: 60px;text-align: center;">
						    <input type="text" step="any" id="porder_id_' . $key . '" name="porder_id[]" onkeyup="getProductsById(this, ' . $key . ')" value="' . $f_data['ord_id'] . '" class="form-control" readonly>
						</td>
						<td>
						    <input type="hidden" id="product_id_' . $key . '" name="product_id[]" value="' . $f_data['product_id'] . '">
						    <input type="text" value="' . $f_data['product_name'] . '" class="form-control" readonly>
						</td>
						<td style="width: 80px !important;">
    						<p class="td-blank">
                                <input type="text" id="customer_' . $key . '"  name="customer[]" value="' . $f_data['customer'] . '" class="form-control" readonly>
                            </p>
                        </td>
                        <td>
    						 <p class="td-blank"><input type="number" step="any" id="sale_quantity_' . $key . '"  name="sale_quantity[]" value="' . $f_data['sale_quantity'] . '" class="form-control" readonly></p>
                        </td>
                        
                        <td>
                            <p class="td-blank"><input type="number" step="any" id="amount_' . $key . '"  name="amount[]" value="' . $f_data['amount'] . '" class="form-control" readonly></p>
                        </td>
                        <td> - </td>
					</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		if (count($return_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">		
						<h2 style="color: red;text-align: center;margin-top: 25px;margin-bottom: 10px;border-top: 1px solid #ddd;padding-top: 10px;">Some Product Not Added. Check Below List</h2>		
						<table class="table table-striped table-bordered mn-table">
						<thead>
						<tr>
							<th>
							    <p>Order ID </p>
							</th>
						    <th>
							    <p>Product </p>
						    </th>
							<th style="width: 95px">
							    <p>Customer</p>
							</th>
							<th style="width: 95px">
							    <p>Sale Quantity</p>
							</th>
							
							<th style="width: 95px">
							    <p>Amount</p>
							</th>
							<th style="width: 95px">
							    <p>Reason</p>
							</th>
						 </tr>
						</thead>
						<tbody >';
			foreach ($return_data as $key => $r_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td style="width: 60px;text-align: center;">
						    <input type="text" value="' . $r_data['ord_id'] . '" class="form-control" readonly>
						</td>
						<td>
						    <input type="text" value="' . $r_data['product_name'] . '" class="form-control" readonly>
						</td>
						<td style="width: 80px !important;">
    						<p class="td-blank">
                                <input type="text" value="' . $r_data['customer'] . '" class="form-control" readonly>
                            </p>
                        </td>
                        <td>
    						 <p class="td-blank"><input type="number" step="any" value="' . $r_data['sale_quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
                            <p class="td-blank"><input type="number" step="any" value="' . $r_data['amount'] . '" class="form-control" readonly></p>
                        </td>
                        <td>' . $r_data['reason'] . '</td>
					</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		$returnData = array(
			'status' => '200',
			'message' => 'Excel data inserted in database',
			'unique_id' => $unique_id,
			'action' => $action,
		);

		return $returnData;
	}

	function import_sales_return_items_excel_insert($fetchData, $warehouse_id, $type)
	{
		$is_complete = 0;
		$curr_data = date("Y-m-d H:i:s");
		$count = 0;
		$Images_arr = array();
		$returnData = array();
		$unique_id = generate_unique_id();

		$total_leads = 0;
		foreach ($fetchData as $item) {
			$ord_id = $item['ord_id'];
			$product = $item['product'];
			$size = $item['size'];
			$quantity = $item['quantity'];
			$reason = $item['reason'];

			$data = array();
			$data = array(
				'type' => $type,
				'unique_id' => $unique_id,
				'product' => $product,
				'batch_no' => NUll,
				'quantity' => $quantity,
				'reason' => $reason,
				'amount' => 0,
				'size' => $size,
				'ord_id' => $ord_id,
				'created_at' => $curr_data
			);

			$data = $this->security->xss_clean($data);
			$this->common_model->insert($data, 'excel_return_stock');
		}

		$final_data = array();
		$return_data = array();
		$query = $this->db->query("SELECT * FROM excel_return_stock WHERE (unique_id='$unique_id')");

		$order_ids = [];
		$skus = [];
		$sizes = [];

		foreach ($query->result_array() as $item) {
			$item_code = $item['product'];
			$quantity = $item['quantity'];
			$size = explode(' | ', $item['size']);
			$ord_id = $item['ord_id'];
			$batch_no = NULL;
			$amount = $item['amount'];
			$reas = $item['reason'];

			$sop_id = 0;
			$customer = '';
			$sale_qty = 0;

			$sales = $this->db->where('product_order_id', $ord_id)->where('item_code', $item_code)->where('size_id', $size[0])->where('is_paid', 0)->get('sales_order_product');
			$flag = 0;
			$reason = "";
			if ($sales->num_rows() > 0) {
				$sales = $sales->row_array();

				$sop_id = $sales['id'];
				$customer = $sales['customer_name'];
				$sale_qty = $sales['qty'] - $sales['return_qty'];
				$amount = $sales['total_amount'];
				if ($sale_qty >= $quantity) {
					if (in_array($ord_id, $order_ids)  && in_array($item_code, $skus) && in_array($size[0], $sizes)) {
						$flag = 1;
						$reason = "Order Id and Product cannot be the same";
					} else {
						$order_ids[] = $ord_id;
						$skus[] = $item_code;
						$sizes[] = $size[0];
					}
				} else {
					$flag = 1;
					$reason = "Sale Qty is lower than return Qty";
				}
			} else {
				$flag = 1;
				$reason = "No Sales Record Found";
			}

			if ($flag == 0) {
				$available_quantity = $row_c['quantity'];
				$final_data[] = array(
					"ord_id" => $ord_id,
					"product_id" => $sop_id,
					"product_name" => $item_code . ' - ' . $size[1],
					"customer" => $customer,
					"reas" => $reas,

					"sale_quantity" => $sale_qty,
					"quantity" => $quantity,
					"amount" => $amount,
					"reason" => $reason,
				);
			} else {
				$return_data[] = array(
					"ord_id" => $ord_id,
					"product_id" => $sop_id,
					"product_name" => $item_code . ' - ' . $size[1],
					"customer" => $customer,
					"reas" => $reas,

					"sale_quantity" => $sale_qty,
					"quantity" => $quantity,
					"amount" => $amount,
					"reason" => $reason,
				);
			}
		}

		$action = '';
		// 		$products_list = $this->get_product_id_by_warehouse($warehouse_id);
		// 		echo json_encode($final_data);exit();
		if (count($final_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">						
						<table class="table table-striped table-bordered mn-table" id="requirement_area">
						<thead>
						<tr>
							<th>
							    <p>Order ID </p>
							</th>
						    <th>
							    <p>Product </p>
						    </th>
							<th style="width: 95px">
							    <p>Customer</p>
							</th>
							<th style="width: 95px">
							    <p>Sale Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Amount</p>
							</th>
							<th style="width: 180px">
							    <p>Reason</p>
							</th>
							<th style="width: 95px">
							    <p>Action</p>
							</th>
						 </tr>
						</thead>
						<tbody class="element-1 new-table" id="product_' . $key . '">';

			foreach ($final_data as $key => $f_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td style="width: 60px;text-align: center;">
						    <input type="text" step="any" id="porder_id_' . $key . '" name="porder_id[]" onkeyup="getProductsById(this, ' . $key . ')" value="' . $f_data['ord_id'] . '" class="form-control" readonly>
						</td>
						<td>
						    <input type="hidden" id="product_id_' . $key . '" name="product_id[]" value="' . $f_data['product_id'] . '">
						    <input type="text" value="' . $f_data['product_name'] . '" class="form-control" readonly>
						</td>
						<td style="width: 80px !important;">
    						<p class="td-blank">
                                <input type="text" id="customer_' . $key . '"  name="customer[]" value="' . $f_data['customer'] . '" class="form-control" readonly>
                            </p>
                        </td>
                        <td>
    						 <p class="td-blank"><input type="number" step="any" id="sale_quantity_' . $key . '"  name="sale_quantity[]" value="' . $f_data['sale_quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
                            <p class="td-blank"><input type="number" step="any" id="quantity_' . $key . '"  name="quantity[]" onkeyup="check_available_qty(this.value,' . $key . ')" value="' . $f_data['quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
                            <p class="td-blank"><input type="number" step="any" id="amount_' . $key . '"  name="amount[]" value="' . $f_data['amount'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
                            <p class="td-blank"><input type="text" id="reason_' . $key . '"  name="reason_id[]" value="' . $f_data['reas'] . '" class="form-control" readonly></p>
                        </td>
                        <td> - </td>
					</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		if (count($return_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">		
						<h2 style="color: red;text-align: center;margin-top: 25px;margin-bottom: 10px;border-top: 1px solid #ddd;padding-top: 10px;">Some Product Not Added. Check Below List</h2>		
						<table class="table table-striped table-bordered mn-table">
						<thead>
						<tr>
							<th>
							    <p>Order ID </p>
							</th>
						    <th>
							    <p>Product </p>
						    </th>
							<th style="width: 95px">
							    <p>Customer</p>
							</th>
							<th style="width: 95px">
							    <p>Sale Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Amount</p>
							</th>
							<th style="width: 95px">
							    <p>Reason</p>
							</th>
						 </tr>
						</thead>
						<tbody >';
			foreach ($return_data as $key => $r_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td style="width: 60px;text-align: center;">
						    <input type="text" value="' . $r_data['ord_id'] . '" class="form-control" readonly>
						</td>
						<td>
						    <input type="text" value="' . $r_data['product_name'] . '" class="form-control" readonly>
						</td>
						<td style="width: 80px !important;">
    						<p class="td-blank">
                                <input type="text" value="' . $r_data['customer'] . '" class="form-control" readonly>
                            </p>
                        </td>
                        <td>
    						 <p class="td-blank"><input type="number" step="any" value="' . $r_data['sale_quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
                            <p class="td-blank"><input type="number" step="any" value="' . $r_data['quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
                            <p class="td-blank"><input type="number" step="any" value="' . $r_data['amount'] . '" class="form-control" readonly></p>
                        </td>
                        <td>' . $r_data['reason'] . '</td>
					</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		$returnData = array(
			'status' => '200',
			'message' => 'Excel data inserted in database',
			'unique_id' => $unique_id,
			'action' => $action,
		);

		return $returnData;
	}

	function import_other_sku_items_excel_insert($fetchData)
	{

		$returnData = [];
		foreach ($fetchData as $fetch) {
			$products = $this->db->where('item_code', ltrim(rtrim($fetch['sku'])))->get('raw_products');
			if ($products->num_rows() > 0) {
				$products = $products->row_array();
				$other_skus = explode(', ', $fetch['other']);
				foreach ($other_skus as $other_sku) {
					$check_product = $this->db->where('item_code', ltrim(rtrim($other_sku)))->get('raw_products');
					if ($check_product->num_rows() > 0) {
						$returnData[] = [
							'sku' => $fetch['sku'],
							'other' => $other_sku,
							'reason' => 'SKU Already Exist',
						];
					} else {
						$check_product = $this->db->where('sku_code', ltrim(rtrim($other_sku)))->get('product_sku');
						if ($check_product->num_rows() > 0) {
							$returnData[] = [
								'sku' => $fetch['sku'],
								'other' => $other_sku,
								'reason' => 'SKU Already Exist',
							];
						} else {
							$insert = [
								"product_id" => $products['id'],
								"sku_code" => $other_sku,
								"is_delete" => 0,
							];

							$this->db->insert('product_sku', $insert);
							$this->db->where('id', $products['id'])->update('raw_products', ['is_other_sku' => 1]);
						}
					}
				}
			} else {
				$returnData[] = [
					'sku' => $fetch['sku'],
					'other' => $fetch['other'],
					'reason' => 'Not found',
				];
			}
		}

		return $returnData;
	}

	function import_damage_stock_items_excel_insert($fetchData, $warehouse_id, $type)
	{
		$is_complete = 0;
		$curr_data = date("Y-m-d H:i:s");
		$count = 0;
		$Images_arr = array();
		$returnData = array();
		$unique_id = generate_unique_id();

		$total_leads = 0;
		foreach ($fetchData as $item) {
			$product = $item['product'];
			$size = $item['size'];
			$quantity = $item['quantity'];

			$data = array();
			$data = array(
				'type' => $type,
				'unique_id' => $unique_id,
				'product' => $product,
				'batch_no' => NUll,
				'quantity' => $quantity,
				'amount' => 0,
				'size' => $size,
				'ord_id' => 0,
				'created_at' => $curr_data
			);

			$data = $this->security->xss_clean($data);
			$this->common_model->insert($data, 'excel_return_stock');
		}

		$final_data = array();
		$return_data = array();
		$query = $this->db->query("SELECT * FROM excel_return_stock WHERE (unique_id='$unique_id')");

		$skus = [];
		$sizes = [];

		foreach ($query->result_array() as $item) {
			$item_code = $item['product'];
			$quantity = $item['quantity'];
			$size = explode(' | ', $item['size']);
			$ord_id = $item['ord_id'];
			$batch_no = NULL;

			$sop_id = 0;
			$sale_qty = 0;
			$sales = $this->db->where('item_code', $item_code)->where('size_id', $size[0])->get('inventory');
			$flag = 0;
			$reason = "";
			if ($sales->num_rows() > 0) {
				$sales = $sales->row_array();

				$sop_id = $sales['id'];
				$sale_qty = $sales['quantity'];
				if ($sale_qty >= $quantity) {
					if (in_array($item_code, $skus) && in_array($size[0], $sizes)) {
						$flag = 1;
						$reason = "Order Id and Product cannot be the same";
					} else {
						$skus[] = $item_code;
						$sizes[] = $size[0];
					}
				} else {
					$flag = 1;
					$reason = "Stock Qty is lower than Damage Qty";
				}
			} else {
				$flag = 1;
				$reason = "No Stock Found";
			}

			if ($flag == 0) {
				$final_data[] = array(
					"product_id" => $sop_id,
					"product_name" => $item_code . ' - ' . $size[1],
					"sale_quantity" => $sale_qty,
					"quantity" => $quantity,
					"reason" => $reason,
				);
			} else {
				$return_data[] = array(
					"product_id" => $sop_id,
					"product_name" => $item_code . ' - ' . $size[1],
					"sale_quantity" => $sale_qty,
					"quantity" => $quantity,
					"reason" => $reason,
				);
			}
		}

		$action = '';
		// 		$products_list = $this->get_product_id_by_warehouse($warehouse_id);
		// 		echo json_encode($final_data);exit();
		if (count($final_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">						
						<table class="table table-striped table-bordered mn-table" id="requirement_area">
						<thead>
						<tr>
						    <th>
							    <p>Product </p>
						    </th>
							<th style="width: 95px">
							    <p>Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Available Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Action</p>
							</th>
						 </tr>
						</thead>
						<tbody class="element-1 new-table" id="product_' . $key . '">';

			foreach ($final_data as $key => $f_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td>
						    <input type="hidden" id="product_id_' . $key . '" name="product_id[]" value="' . $f_data['product_id'] . '">
						    <input type="text" value="' . $f_data['product_name'] . '" class="form-control" readonly>
						</td>
                        <td>
                            <p class="td-blank"><input type="number" step="any" id="quantity_' . $key . '"  name="quantity[]" onkeyup="check_available_qty(this.value,' . $key . ')" value="' . $f_data['quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
    						 <p class="td-blank"><input type="number" step="any" id="sale_quantity_' . $key . '"  name="available[]" value="' . $f_data['sale_quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td> - </td>
					</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		if (count($return_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">		
						<h2 style="color: red;text-align: center;margin-top: 25px;margin-bottom: 10px;border-top: 1px solid #ddd;padding-top: 10px;">Some Product Not Added. Check Below List</h2>		
						<table class="table table-striped table-bordered mn-table">
						<thead>
						<tr>
						    <th>
							    <p>Product </p>
						    </th>
							<th style="width: 95px">
							    <p>Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Available Quantity</p>
							</th>
							<th style="width: 95px">
							    <p>Reason</p>
							</th>
						 </tr>
						</thead>
						<tbody >';
			foreach ($return_data as $key => $r_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td style="width: 60px;text-align: center;">
						    <input type="text" value="' . $r_data['ord_id'] . '" class="form-control" readonly>
						</td>
						<td>
						    <input type="text" value="' . $r_data['product_name'] . '" class="form-control" readonly>
						</td>
                        <td>
                            <p class="td-blank"><input type="number" step="any" value="' . $r_data['quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>
    						 <p class="td-blank"><input type="number" step="any" value="' . $r_data['sale_quantity'] . '" class="form-control" readonly></p>
                        </td>
                        <td>' . $r_data['reason'] . '</td>
					</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		$returnData = array(
			'status' => '200',
			'message' => 'Excel data inserted in database',
			'unique_id' => $unique_id,
			'action' => $action,
		);

		return $returnData;
	}

	function import_retrun_stock_items_excel_insert($fetchData, $warehouse_id, $type)
	{
		$is_complete = 0;
		$curr_data = date("Y-m-d H:i:s");
		$count = 0;
		$Images_arr = array();
		$returnData = array();
		$unique_id = generate_unique_id();

		$total_leads = 0;
		foreach ($fetchData as $item) {
			$product = $item['product'];
			$quantity = $item['quantity'];
			$amount = $item['amount'] ? $item['amount'] : NULL;

			$data = array();

			$data = array(
				'type' => $type,
				'unique_id' => $unique_id,
				'product' => $product,
				'batch_no' => NUll,
				'quantity' => $quantity,
				'amount' => $amount,
				'created_at' => $curr_data
			);

			$data = $this->security->xss_clean($data);
			$this->common_model->insert($data, 'excel_return_stock');
		}

		$final_data = array();
		$return_data = array();
		$query = $this->db->query("SELECT * FROM excel_return_stock WHERE (unique_id='$unique_id')");
		foreach ($query->result_array() as $item) {
			$item_code = $item['product'];
			$batch_no = NUll;
			$quantity = $item['quantity'];
			$amount = $item['amount'];

			$result = $this->check_inv_for_sales_by_item_code($item_code, $batch_no, $warehouse_id);

			if ($result['status'] == 200) {
				$inv_id = $result['id'];

				$check = $this->db->query("SELECT product_id,product_name,batch_no,quantity,item_code FROM inventory WHERE id='$inv_id' LIMIT 1");
				//echo $this->db->last_query();

				if ($check->num_rows() > 0) {
					$row_c = $check->row_array();
					$product_id = $row_c['product_id'];
					$product_name = $row_c['product_name'];
					$product_item_code = $row_c['item_code'];

					$available_quantity = $row_c['quantity'];
					// 	if($quantity <= $available_quantity){
					$final_data[] = array(
						"product_id" => $product_id . '|' . $product_item_code,
						"product_name" => $product_name,
						"batch_no" => NUll,
						"quantity" => $quantity,
						"amount" => $amount,
						"available_quantity" => $available_quantity,
					);
					// 	}							
				}
			} else {
				$return_data[] = array(
					"product_name" => $item_code,
					"quantity" => $quantity,
					"amount" => $amount,
					"available_quantity" => 0,
				);
			}
		}

		$action = '';
		$products_list = $this->get_product_id_by_warehouse($warehouse_id);
		//echo json_encode($products_list);exit();
		if (count($final_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">						
						<table class="table table-striped table-bordered mn-table" id="requirement_area">
						<thead>
						<tr>
						<th style="width: 60px;text-align: center;"><p>Sr No. </p></th>
						<th><p>Product </p></th>
						<th><p>Quantity</p></th>';

			if ($type == 'purchase') {
				$action .= '<th><p>Amount</p></th>';
			}

			if ($type != 'sales') {
				$action .= '<th><p>Available Stock</p></th>';
			}

			$action .= '</tr>
						</thead>
						<tbody class="element-1 new-table" id="product_' . $key . '">';
			foreach ($final_data as $key => $f_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td style="width: 60px;text-align: center;">' . $key . '</td>
						<td><span class="new-td"><select class="form-control select2 product_id" name="product_id[]" id="product_id_' . $key . '" data-toggle="select2" onchange="get_batch_by_product(this.value,' . $x_id . ');"  required><option value="">Select Product</option>';

				if (count($products_list) > 0) {
					foreach ($products_list as $item) {
						$selected = ($item['id'] == $f_data['product_id']) ? 'selected' : '';
						$action .= '<option value="' . $item['id'] . '" ' . $selected . '>' . $item['name'] . '</option>';
					}
				}

				$action .= '</select></span></td>
						<td style="width: 80px !important;"><p class="td-blank"><input type="number" id="quantity_' . $key . '" name="quantity[]" value="' . $f_data['quantity'] . '" class="form-control"></p></td>';

				if ($type == 'purchase') {
					$action .= '<td style="width: 120px !important;"><p class="td-blank"><input type="number" id="amount_' . $key . '"  name="amount[]" value="' . $f_data['amount'] . '" class="form-control"></p></td>';
				}

				if ($type != 'sales') {
					$action .= '<td style="width: 120px !important;"><p class="td-blank"><input type="number" id="available_' . $key . '" name="available[]" value="' . $f_data['available_quantity'] . '" class="form-control" readonly></p></td>';
				}

				$action .= '</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		if (count($return_data) > 0) {
			$action .= '<div class="table-responsive">
						<div class="col-lg-12 no-pad">		
						<h2 style="color: red;text-align: center;margin-top: 25px;margin-bottom: 10px;border-top: 1px solid #ddd;padding-top: 10px;">Product Not Found In Inventory. Check Below List</h2>		
						<table class="table table-striped table-bordered mn-table">
						<thead>
						<tr>
						<th style="width: 60px;text-align: center;"><p>Sr No. </p></th>
						<th><p>Product </p></th>
						<th><p>Quantity</p></th>';

			if ($type == 'purchase') {
				$action .= '<th><p>Amount</p></th>';
			}

			if ($type != 'sales') {
				$action .= '<th><p>Available Stock</p></th>';
			}

			$action .= '</tr>
						</thead>
						<tbody >';
			foreach ($return_data as $key => $r_data) {
				$key++;
				$x_id = "'" . $key . "'";

				$action .= '<tr>
						<td style="width: 60px;text-align: center;">' . $key . '</td>
						<td><p class="td-blank"><input type="text" value="' . $r_data['product_name'] . '" class="form-control" readonly ></p></td>
						<td><p class="td-blank"><input type="text" value="' . $r_data['quantity'] . '" class="form-control" readonly ></p></td>';

				if ($type == 'purchase') {
					$action .= '<td style="width: 120px !important;"><p class="td-blank"><input type="number"  value="' . $r_data['amount'] . '" class="form-control" readonly></p></td>';
				}

				if ($type != 'sales') {
					$action .= '<td style="width: 120px !important;"><p class="td-blank"><input type="number" value="' . $r_data['available_quantity'] . '" class="form-control" readonly></p></td>';
				}

				$action .= '</tr>';
			}
			$action .= '</tbody>
						</table>';
		}

		$returnData = array(
			'status' => '200',
			'message' => 'Excel data inserted in database',
			'unique_id' => $unique_id,
			'action' => $action,
		);

		return $returnData;
	}

	function check_imported_product($fetchData, $warehouse_id)
	{

		$inventory = $this->db->where('warehouse_id', $warehouse_id)->get('inventory');
		$result = [];
		$notfound = [];
		$notenough = [];

		foreach ($fetchData as $item) {
			$size = $item['size'];
			$product = $this->db->query("SELECT id, name, item_code FROM raw_products WHERE is_deleted='0' AND item_code='" . $item['sku_code'] . "' limit 1");
			if ($product->num_rows() > 0) {
				$product = $product->row_array();
				$variation = $this->db->query("SELECT id, sku_code FROM product_variation WHERE product_id='" . $product['id'] . "' AND size_name='" . $size . "' limit 1");
				// echo $variation->num_rows();

				if ($variation->num_rows() > 0) {
					$variation = $variation->row_array();
					$result[] = [
						"id" => $product['id'],
						"var_id" => $variation['id'],
						"sku_code" => $product['item_code'],
						"size" => $size,
						"quantity" => $item['quantity'],
					];
				} else {
					$notfound[] = [
						"sku_code" => $item['sku_code'],
						"quantity" => $item['quantity'],
						"size" => $size,
					];
				}
			} else {
				$others = $this->db->query("SELECT product_id, sku_code FROM product_sku WHERE sku_code='" . $item['sku_code'] . "' AND is_delete='0' limit 1");
				if ($others->num_rows() > 0) {
					$others = $others->row_array();
					$product = $this->db->query("SELECT id, name, item_code FROM raw_products WHERE is_deleted='0' AND id='" . $others['product_id'] . "' limit 1");
					if ($product->num_rows() > 0) {
						$product = $product->row_array();
						$variation = $this->db->query("SELECT id, sku_code FROM product_variation WHERE product_id='" . $product['id'] . "' AND size_name='" . $size . "' limit 1");
						if ($variation->num_rows() > 0) {
							$variation = $variation->row_array();
							$result[] = [
								"id" => $product['id'],
								"var_id" => $variation['id'],
								"sku_code" => $product['item_code'],
								"size" => $size,
								"quantity" => $item['quantity'],
							];
						} else {
							$notfound[] = [
								"sku_code" => $item['sku_code'],
								"quantity" => $item['quantity'],
								"size" => $size,
							];
						}
					}
				} else {
					$notfound[] = [
						"sku_code" => $item['sku_code'],
						"quantity" => $item['quantity'],
						"size" => $size,
					];
				}
			}
		}

		if (count($result) > 0 && $inventory->num_rows() > 0) {
			$inventory = $inventory->result_array();
			foreach ($result as $res) {
				foreach ($inventory as $key => $inv) { // Use index $key to reference original array
					if ($inv['item_code'] == $res['sku_code'] && $inv['size_name'] == $res['size']) {
						$inventory[$key]['quantity'] -= $res['quantity']; // Modify original array

						if ($inventory[$key]['quantity'] < 0) {
							$notenough[] = $res;
						}
					}
				}
			}
		}

		return ["not_found" => $notfound, "not_enough" => $notenough];
	}

	function delete_sales_order($id)
	{
		$this->db->trans_start(); // Start transaction

		try {
			$sales = $this->common_model->getRowById('sales_order', 'warehouse_id', ['id' => $id]);
			if (!$sales) {
				throw new Exception(get_phrase('sales_order_not_found'));
			}

			$so_products = $this->common_model->getResultById('sales_order_product', 'id, product_id, product_name, item_code, size_id', ['order_id' => $id]);

			if ($so_products) {
				foreach ($so_products as $prod) {
					$so_batch = $this->common_model->getRowById('sales_order_product_batch', 'batch_qty', ['order_id' => $id, 'order_product_id' => $prod['id']]);
					if ($so_batch) {
						$inv = $this->common_model->getRowById('inventory', '*', [
							'size_id' => $prod['size_id'],
							'product_id' => $prod['product_id'],
							'warehouse_id' => $sales['warehouse_id']
						]);

						if ($inv) {
							$new_qty = $inv['quantity'] + $so_batch['batch_qty'];
							$this->db->where('id', $inv['id'])->update('inventory', ['quantity' => $new_qty]);

							$stocks_data = [
								'order_id'       => $id,
								'parent_id'      => $inv['id'],
								'warehouse_name' => $inv['warehouse_name'],
								'warehouse_id'   => $inv['warehouse_id'],
								'product_id'     => $prod['product_id'],
								'product_name'   => $inv['product_name'],
								'quantity'       => $so_batch['batch_qty'],
								'batch_no'       => NULL,
								'item_code'      => $prod['item_code'],
								'size_id'   	  	=> $inv['size_id'],
								'size_name'         => $inv['size_name'],
								'group_id'          => $inv['group_id'],
								'color_id'          => $inv['color_id'],
								'color_name'        => $inv['color_name'],
								'sku'               => $inv['sku'],
								'categories'        => $inv['categories'],
								'expiry_date'    => NULL,
								'status'         => 'sales_delete',
								'received_date'  => date("Y-m-d H:i:s"),
								'added_date'     => date("Y-m-d H:i:s"),
								'added_by_id'    => $this->session->userdata('super_user_id'),
								'added_by_name'  => $this->session->userdata('super_name')
							];

							$this->db->insert('inventory_history', $stocks_data);
						}
					}
				}
			}

			// Soft delete sales order
			$this->db->where('id', $id)->update('sales_order', ['is_deleted' => 1]);

			$this->db->trans_commit(); // Commit transaction

			$resultpost = [
				"status" => 200,
				"message" => get_phrase('sales_order_delete_successfully'),
				"url" => $this->session->userdata('previous_url'),
			];
		} catch (Exception $e) {
			$this->db->trans_rollback(); // Rollback on error
			$resultpost = [
				"status" => 400,
				"message" => $e->getMessage(),
			];
		}

		$this->session->set_flashdata('flash_message', $resultpost['message']);
		return simple_json_output($resultpost);
	}

	function add_inventory_data($unique_id)
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('order_added_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);
			$is_complete = 0;
			$returnData = array();
			$query = $this->db->query("SELECT customer_id,warehouse_id,company_id,order_date,customer_name,size,product_name,quantity,amount,batch_no,order_no,refrence_no FROM excel_orders WHERE (is_move='0') and unique_id='$unique_id' group by order_date");
			foreach ($query->result_array() as $item2) {
				$order_no = $item2['order_no'];
				$refrence_no = $item2['refrence_no'];
				$customer_id = $item2['customer_id'];
				$warehouse_id = $item2['warehouse_id'];
				$company_id = $item2['company_id'];
				$order_date = $item2['order_date'];

				$n_order_no  = $this->inventory_model->get_sales_order_no();
				$customer_name = '';
				if ($customer_id != '') {
					$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'contact_name');
				}

				$warehouse_name = '';
				if ($warehouse_id != '') {
					$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
				}

				$company_name = '';
				if ($company_id != '') {
					$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
				}

				$main_date = $order_date;
				$basic_value = 0;
				$data = array();
				$data['order_type']        = 'excel';
				$data['unique_id']          = $unique_id;
				$data['order_no']          = $n_order_no;
				$data['refrence_no']       = $refrence_no;
				$data['date']     		   = ($order_date);
				$data['customer_id']       = $customer_id;
				$data['customer_name']     = $customer_name;
				$data['warehouse_id']      = $warehouse_id;
				$data['warehouse_name']    = $warehouse_name;
				$data['company_id']        = $company_id;
				$data['company_name']      = $company_name;
				$data['remark'] 		   = '';
				$data['narration']         = '';
				$data['gst_type']     	   	= '';
				$data['other_charges_name']   = '';
				$data['other_charges_amount'] = 0;
				$data['added_by_id']          = $this->session->userdata('super_user_id');
				$data['added_by_name']        = $this->session->userdata('super_name');
				$data['added_date']   	      = date("Y-m-d H:i:s");
				$this->db->insert('sales_order', $data);
				$n_order_id = $this->db->insert_id();
				$this->update_order_no($n_order_no);
				$is_complete = 1;
				$query1 = $this->db->query("SELECT id,customer_id,warehouse_id,order_date,order_no,product_name,quantity,customer_name, pincode, state, size,amount,batch_no,order_no FROM excel_orders WHERE (is_move='0')  and unique_id='$unique_id' and order_date='$order_date'");
				foreach ($query1->result_array() as $item) {
					$excel_id = $item['id'];
					$product_name = $item['product_name'];
					$quantity = intval($item['quantity']);
					$amount = $item['amount'];
					$order_no = $item['order_no'];
					$batch_no = $item['batch_no'];
					$order_date = $item['order_date'];
					$customer_name = $item['customer_name'];
					$pincode = $item['pincode'];
					$state = $item['state'];
					$size = $item['size'];

					$result = $this->check_inv_by_item_code($product_name, $size, $warehouse_id);
					// 	echo json_encode($result);exit();
					if ($result['status'] == 200) {
						$inv_id = $result['id'];
						$inv_quantity = intval($result['quantity']);
						$new_qty = $quantity * $inv_quantity;
						//echo $new_qty;exit();
						$check_inv = $this->db->query("SELECT product_name,product_id,item_code,quantity,size_id,size_name, group_id, color_id, color_name, sku, categories FROM inventory WHERE id='$inv_id' limit 1")->row_array();
						//echo $this->db->last_query();exit();
						$x_product_id = $check_inv['product_id'];
						$x_product_name = $check_inv['product_name'];
						$x_item_code = $check_inv['item_code'];
						$x_size_id = $check_inv['size_id'];
						$x_size_name = $check_inv['size_name'];
						$x_group_id = $check_inv['group_id'];
						$x_color_id = $check_inv['color_id'];
						$x_color_name = $check_inv['color_name'];
						$x_sku = $check_inv['sku'];
						$x_categories = $check_inv['categories'];
						$old_quantity = intval($check_inv['quantity']);

                        $final_qty = $old_quantity - $new_qty;
                        if($final_qty < 0) {
                            $is_complete = 0;
    						$returnData[] = array(
    							'dispense_date' => $order_date,
    							'sku_code' => $product_name,
    							'quantity' => $quantity,
    							'amount' => $amount,
    							'pincode' => $pincode,
    							'batch_no' => $batch_no,
    							'order_id' => $order_no,
    							'customer_name' => $customer_name,
    							'customer_no' => $customer_no,
    							'size' => $size,
    
    						);
                        } else {
    						$data_product = array();
    						$data_product = array(
    							'order_id'         => $n_order_id,
    							'product_id'        => $x_product_id,
    							'product_order_id'  => $order_no,
    							'customer_name'       => $customer_name,
    							'pincode'       => $pincode,
    							'state'       => $state,
    							'size_name'         => $x_size_name,
    							'group_id'         => $x_group_id,
    							'color_id'         => $x_color_id,
    							'color_name'         => $x_color_name,
    							'qty'       => $quantity,
    							'size_id'           => $x_size_id,
    							'item_code'        => $x_item_code,
    							'product_name'      => $x_product_name,
    							'total_amount'       => $amount,
    						);
    
    						$this->db->insert('sales_order_product', $data_product);
    						$order_product_id = $this->db->insert_id();
    						$basic_value += $amount;
    
    						$data_product_bat = array();
    						$data_product_bat = array(
    							'order_id'          => $n_order_id,
    							'order_product_id'  => $order_product_id,
    							'batch_no'      	=> $batch_no,
    							'batch_qty'       	=> $new_qty,
    						);
    						$this->db->insert('sales_order_product_batch', $data_product_bat);
    
    						//echo $new_qty;exit();
    						//echo $final_qty;exit();
    						$data_history = array();
    						$data_history = array(
    							'quantity'       	=> $final_qty,
    						);
    						$this->db->where('id', $inv_id);
    						$this->db->update('inventory', $data_history);
    
    						$limit_history = array();
    						$limit_history['parent_id']   	  	= $inv_id;
    						$limit_history['warehouse_id']   	= $warehouse_id;
    						$limit_history['warehouse_name']   	= $warehouse_name;
    						$limit_history['product_id']   	  	= $x_product_id;
    						$limit_history['product_name']   	= $x_product_name;
    						$limit_history['item_code']   	  	= $x_item_code;
    						$limit_history['order_id'] 	   		= $n_order_id;
    						$limit_history['status'] 	   		= 'out';
    						$limit_history['product_order_id']  = $order_no;
    						$limit_history['size_id']   	  	= $x_size_id;
    						$limit_history['size_name']         = $x_size_name;
    						$limit_history['group_id']          = $x_group_id;
    						$limit_history['color_id']          = $x_color_id;
    						$limit_history['color_name']        = $x_color_name;
    						$limit_history['sku']               = $x_sku;
    						$limit_history['categories']        = $x_categories;
    						$limit_history['received_date']     = $main_date;
    						$limit_history['quantity'] 			= $new_qty;
    						$limit_history['batch_no'] 			= $batch_no;
    						$limit_history['added_by_id'] 		= $this->session->userdata('super_user_id');
    						$limit_history['added_by_name'] 	= $this->session->userdata('super_name');
    						$limit_history['added_date'] 		= date("Y-m-d H:i:s");
    						$this->db->insert('inventory_history', $limit_history);
    						//echo json_encode($limit_history);exit();
    
    						$excel_data = array();
    						$excel_data['is_move'] = '1';
    						$excel_data['is_complete'] = '1';
    						$this->db->where('id', $excel_id);
    						$this->db->update('excel_orders', $excel_data);
                        }

					} else {
						$is_complete = 0;
						$returnData[] = array(
							'dispense_date' => $order_date,
							'sku_code' => $product_name,
							'quantity' => $quantity,
							'amount' => $amount,
							'pincode' => $pincode,
							'batch_no' => $batch_no,
							'order_id' => $order_no,
							'customer_name' => $customer_name,
							'customer_no' => $customer_no,
							'size' => $size,

						);
					}
				}

				$final_history = array();
				$final_history = array(
					'basic_value'       	=> $basic_value,
					'net_sales_value_1'     => $basic_value,
					'net_sales_value_2'     => $basic_value,
					'grand_total'       	=> $basic_value,
				);
				$this->db->where('id', $n_order_id);
				$this->db->update('sales_order', $final_history);
				if ($is_complete == 1) {
					$resultpost = array(
						"status" => 200,
						"message" => get_phrase('order_added_successfully'),
						"url" => $this->session->userdata('previous_url'),
						"returnData" => $returnData,
					);
					
					$this->db->trans_commit();
				} else {
					$resultpost = array(
						"status" => 400,
						"message" => get_phrase('product_not_found'),
						"url" => $this->session->userdata('previous_url'),
						"returnData" => $returnData,
					);
					
					$this->db->trans_rollback();
				}
			}
			
		} catch (Exception $e) {
			
			$resultpost = array(
				"status" => 400,
				"message" =>  "Exception occurred: " . $e->getMessage(),
			);
		}
		return ($resultpost);
	}

	function check_inv_by_item_code($item_code, $size, $warehouse_id)
	{
		$check = 0;
		$product_id = '';
		$x_item_code = '';
		$quantity = '';
		$query = $this->db->query("SELECT p.id, p.item_code FROM raw_products as p INNER JOIN product_variation as pv ON p.id = pv.product_id WHERE p.item_code='" . trim($item_code) . "' AND pv.size_name = '" . trim($size) . "' limit 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$product_id = $row['id'];
			$x_item_code = $row['item_code'];

			$quantity = 1;
			$check = 1;
		} else {
			$checkSku = $this->db->where('sku_code', $item_code)->get('product_sku');
			if ($checkSku->num_rows() > 0) {
				$checkSku = $checkSku->row_array();
				// $checkSku = $query->row_array();
				$query = $this->db->query("SELECT p.id, p.item_code FROM raw_products as p INNER JOIN product_variation as pv ON p.id = pv.product_id WHERE p.id='" . trim($checkSku['product_id']) . "' AND pv.size_name = '" . trim($size) . "' limit 1");
				if ($query->num_rows() > 0) {
					$row = $query->row_array();
					$product_id = $row['id'];
					$x_item_code = $row['item_code'];

					$quantity = 1;
					$check = 1;
				} else {
					$check = 0;
				}
			} else {
				$check = 0;
			}
		}

		// echo $check; exit();

		if ($check == 0) {
			$resultpost = array(
				"status" => 400,
				"message" =>  "fail 1",
			);
		} else {
			if ($product_id != '' && $x_item_code != ''  && $quantity != '' && $size != '') {
				$query_ord = $this->db->query("SELECT id,quantity,product_name FROM inventory WHERE product_id='" . $product_id . "' and item_code='" . trim($x_item_code) . "' and size_name='" . trim($size) . "' and warehouse_id='" . $warehouse_id . "' limit 1");
				//echo $this->db->last_query();exit();
				$row_ord = $query_ord->row_array();
				if ($query_ord->num_rows() > 0) {
					$row_ord = $query_ord->row_array();
					$inv_id = $row_ord['id'];
					$stocks = $row_ord['quantity'];
					$product_name = $row_ord['product_name'];
					if ($stocks > 0) {
						$inv_id = $inv_id;
						$resultpost = array(
							"status" => 200,
							"message" =>  "Success",
							"id" =>  $inv_id,
							"quantity" =>  $quantity,
						);
					} else {
						$resultpost = array(
							"status" => 400,
							"message" =>  "fail 2",
						);
					}
				} else {
					$resultpost = array(
						"status" => 400,
						"message" =>  "fail 3",
						"a" =>  $product_id,
						"b" =>  $x_item_code,
						"c" =>  $quantity,

					);
				}
			} else {
				$resultpost = array(
					"status" => 400,
					"message" =>  "fail 3",
				);
			}
		}

		return $resultpost;
	}

	function check_inv_for_sales_by_item_code($item_code, $batch_no, $warehouse_id)
	{

		$check = 0;
		$product_id = '';
		$x_item_code = '';
		$quantity = '';
		$query = $this->db->query("SELECT id,item_code FROM raw_products WHERE item_code='$item_code' limit 1");
		// 		echo $this->db->last_query();exit();
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$product_id = $row['id'];
			$x_item_code = $row['item_code'];

			$row3 = $this->db->query("SELECT product_id,variation_id,quantity FROM product_variation_sku WHERE sku_code='$item_code' limit 1");
			if ($row3->num_rows() > 0) {
				$row3 = $row3->row_array();
				$variation_id = $row3['variation_id'];
				$row3 = $this->db->query("SELECT sku_code FROM product_variation WHERE id='$variation_id' limit 1")->row_array();
				$x_item_code = $row3['sku_code'];
			}

			$quantity = 1;
			$check = 1;
		} else {
			$query1 = $this->db->query("SELECT product_id,sku_code FROM product_variation WHERE sku_code='$item_code' limit 1");
			if ($query1->num_rows() > 0) {
				$row1 = $query1->row_array();
				$product_id = $row1['product_id'];
				$x_item_code = $row1['sku_code'];
				$quantity = 1;
				$check = 1;
			} else {
				$query2 = $this->db->query("SELECT product_id,variation_id,quantity FROM product_variation_sku WHERE sku_code='$item_code' limit 1");
				if ($query2->num_rows() > 0) {
					$row2 = $query2->row_array();
					$product_id = $row2['product_id'];
					$variation_id = $row2['variation_id'];
					$quantity = $row2['quantity'];
					$row3 = $this->db->query("SELECT sku_code FROM product_variation WHERE id='$variation_id' limit 1")->row_array();
					$x_item_code = $row3['sku_code'];
					$check = 1;
				} else {
					$check = 0;
				}
			}
		}
		if ($check == 0) {
			$resultpost = array(
				"status" => 400,
				"message" =>  "fail 1",
			);
		} else {
			if ($product_id != '' && $x_item_code != ''  && $quantity != '') {
				$query_ord = $this->db->query("SELECT id,quantity,product_name FROM inventory WHERE product_id='$product_id' and item_code='$x_item_code' and warehouse_id='$warehouse_id' limit 1");
				//echo $this->db->last_query();exit();
				$row_ord = $query_ord->row_array();
				if ($query_ord->num_rows() > 0) {
					$row_ord = $query_ord->row_array();
					$inv_id = $row_ord['id'];
					$stocks = $row_ord['quantity'];
					$product_name = $row_ord['product_name'];

					$inv_id = $inv_id;
					$resultpost = array(
						"status" => 200,
						"message" =>  "Success",
						"id" =>  $inv_id,
						"quantity" =>  $quantity,
					);
				} else {
					$resultpost = array(
						"status" => 400,
						"message" =>  "fail 3",
						"a" =>  $product_id,
						"b" =>  $x_item_code,
						"c" =>  $quantity,

					);
				}
			} else {
				$resultpost = array(
					"status" => 400,
					"message" =>  "fail 3",
				);
			}
		}

		return $resultpost;
	}

	public function get_sales_order_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('sales_order');
	}

	/* Sales Order End */

	/* Goods Return Start */
	public function get_sale_order_items()
	{
		$result = [
			"status" => 200,
			"message" => "success",
			"product" => []
		];

		$order_id = $this->input->post('value');
		if ($order_id != '') {
			$product = $this->db->where('product_order_id', $order_id)->where('is_paid', 0)->get('sales_order_product');
			if ($product->num_rows() > 0) {
				$all_prod = [];
				foreach ($product->result_array() as $prod) {
					if (($prod['qty'] - $prod['return_qty']) > 0) {
						$all_prod[] = $prod;
					}
				}

				$result['product'] = $all_prod;
			}
		}

		echo json_encode($result);
	}

	public function get_sale_order_product()
	{
		$result = [
			"status" => 200,
			"message" => "success",
			"product" => []
		];

		$order_id = $this->input->post('value');
		if ($order_id != '') {
			$product = $this->db->where('id', $order_id)->get('sales_order_product');
			if ($product->num_rows() > 0) {
				$product = $product->row_array();
				$product['sale_qty'] = $product['qty'] - $product['return_qty'];
				$result['product'] = $product;
			}
		}

		echo json_encode($result);
	}

	public function get_goods_return()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (customer_name like '%" . $keyword . "%' OR date like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%' OR order_no like '%" . $keyword . "%')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND DATE(date) = '$from'";
			} else {
				$keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
			}
		}
		//echo $keyword_filter;exit();

		$total_count = $this->db->query("SELECT id FROM goods_return WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,warehouse_name,company_id,customer_name,company_name,reason,added_date,order_no,date FROM goods_return WHERE (is_deleted='0') $keyword_filter ORDER BY date DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$company_id = $item['company_id'];

				$view_url = base_url() . 'inventory/goods-return/view/' . $id;
				$delete_url = base_url() . 'inventory/goods_return/delete_post/' . $id;
				$action = '<a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';
				// $action .= '<a href="#" onclick="confirm_modal(\'' . $delete_url . '\',\'Are you sure want to delete!\')" data-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Delete" aria-label="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$product_qty = 0;
				$query_pro = $this->db->query("SELECT SUM(quantity) as quantity FROM goods_return_product WHERE (parent_id='$id') group by parent_id");
				if ($query_pro->num_rows() > 0) {
					$item_1 = $query_pro->row_array();
					$product_qty  = $item_1['quantity'];
				}


				/*
				if(count($product_name) > 0){
					$product_name = '<span>'.$product_name.'</span>';
				}
				*/

				$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"order_id"          => 'GPS_GR_' . $item['id'],
					"order_no"        => $item['order_no'],
					"warehouse_name"        => $item['warehouse_name'],
					"customer_name"        => ($item['customer_name'] != '' && $item['customer_name'] != null) ? $item['customer_name'] : '-',
					"company_name"        => ($company_name != '' && $company_name != null) ? $company_name : '-',
					"reason"        		=> $item['reason'],
					"product_qty"        => $product_qty,
					"date"        => date('d M, Y', strtotime($item['date'])),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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

	public function get_goods_return_history($id)
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (grp.item_code like '%" . $keyword . "%' OR grp.product_name like '%" . $keyword . "%' OR grp.batch_no like '%" . $keyword . "%' OR grp.quantity like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT grp.id FROM goods_return as gr
		INNER JOIN goods_return_product as grp ON gr.id = grp.parent_id
		Where gr.id = '$id' and gr.is_deleted='0' $keyword_filter")->num_rows();
		$query = $this->db->query("SELECT gr.id,gr.added_date,gr.date,gr.warehouse_name,gr.customer_name,gr.company_name,gr.reason,gr.order_no,grp.product_name,grp.item_code,grp.quantity,grp.batch_no FROM goods_return as gr
		INNER JOIN goods_return_product as grp ON gr.id = grp.parent_id
		Where gr.id = '$id' and gr.is_deleted='0' $keyword_filter ORDER BY gr.date DESC LIMIT $start, $length");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {

				$data[] = array(
					"sr_no"       => ++$start,
					"order_id"          => 'GPS_GR_' . $item['id'],
					"order_no"        => $item['order_no'],
					"customer_name"        => $item['customer_name'],
					"company_name"        => ($item['company_name'] != '' && $item['company_name'] != null) ? $item['company_name'] : '-',
					"warehouse_name"        => $item['warehouse_name'],
					"reason"        		=> $item['reason'],
					"product_name"        		=> $item['item_code'] . ' - ' . $item['product_name'],
					"product_qty"        => $item['quantity'],
					"batch_no"        => $item['batch_no'],
					"date"        => date('d M, Y', strtotime($item['date'])),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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

	public function add_goods_return($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('goods_return_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$warehouse_id = $this->input->post('warehouse_id', true);
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$customer_id = $this->input->post('customer_id', true);
		$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'name');
		$company_id = $this->input->post('company_id', true);
		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
		$order_no = $this->input->post('order_no', true);
		$reasons = $this->input->post('reason_id', true);
		$date = $this->input->post('date', true);
		$reason = $this->input->post('reason', true);
		$product_id = $this->input->post('product_id', true);
		$quantity = $this->input->post('quantity', true);
		$porder_id = $this->input->post('porder_id', true);

		$data = array();
		$excel_id = $this->input->post('excel_id');
		$method = 'manually';
		if ($excel_id != '' && $excel_id != NULL) {
			$method = 'by_excel';
		}

		$data['method']      		= $method;
		$data['excel_id']      		= $excel_id;
		$data['warehouse_id']    	= $warehouse_id;
		$data['warehouse_name']    	= $warehouse_name;
		$data['customer_id']    	= $customer_id;
		$data['company_id']    		= $company_id;
		$data['customer_name']    	= $customer_name;
		$data['company_name']    	= $company_name;
		$data['order_no']    		= $order_no;
		$data['date']    			= date('Y-m-d', strtotime($date));
		$data['reason']    			= $reason;
		$data['added_by_id']    	= $this->session->userdata('super_user_id');
		$data['added_by_name']    	= $this->session->userdata('super_name');
		$data['added_date']     	= date("Y-m-d H:i:s");
		$insert = $this->db->insert('goods_return', $data);
		$parent_id = $this->db->insert_id();

		for ($i = 0; $i < count($product_id); $i++) {
			if ($quantity[$i] > 0 && $product_id != '') {
				$prod = $product_id[$i];

				$s_order_product = $this->db->where('id', $prod)->get('sales_order_product')->row_array();

				// Updating Sales Order Product
				$return_qty = $s_order_product['return_qty'] + $quantity[$i];
				$this->db->where('id', $prod)->update('sales_order_product', ['return_qty' => $return_qty]);

				$pro = explode('|', $prod);
				$prod_id = $s_order_product['product_id'];
				$size_id = $s_order_product['size_id'];

				$inv_prod = $this->db->where('product_id', $prod_id)->where('size_id', $size_id)->get('inventory')->row_array();
				$item_code = $inv_prod['item_code'];
				$product_name = $this->common_model->selectByidParam($prod_id, 'raw_products', 'name');

				$data_p = array();
				$data_p['parent_id']    	= $parent_id;
				$data_p['product_id']    	= $prod_id;
				$data_p['product_name']    	= $product_name;

				$data_p['sop_id']    		= $prod;
				$data_p['size_id']    		= $size_id;
				$data_p['size_name']        = $inv_prod['size_name'];
				$data_p['group_id']    		= $inv_prod['group_id'];
				$data_p['color_id']    		= $inv_prod['color_id'];
				$data_p['color_name']       = $inv_prod['color_name'];
				$data_p['quantity']    		= $quantity[$i];
				$data_p['product_order_id'] = $porder_id[$i];
				$data_p['reason']           = $reasons[$i];

				$data_p['batch_no']    		= NULL;
				$data_p['item_code']    	= $item_code;
				$insert_1 = $this->db->insert('goods_return_product', $data_p);

				if ($insert_1) {
					// Stock In
					$query_check = $this->db->query("SELECT id,quantity,expiry_date FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$prod_id' and size_id='$size_id' limit 1");
					if ($query_check->num_rows() > 0) {
						$gstock       = $query_check->row_array();
						$stock_id     = $gstock['id'];
						$expiry_date     = $gstock['expiry_date'];
						$new_quantity = 0;
						$new_quantity = $gstock['quantity'] + $quantity[$i];

						$prod = array();
						$prod['quantity'] = $new_quantity;
						$this->db->where('id', $stock_id);
						$this->db->update('inventory', $prod);

						$stocks_data  = array();
						$stocks_data['order_id'] = $parent_id;
						$stocks_data['parent_id'] = $stock_id;
						$stocks_data['warehouse_name'] = $warehouse_name;
						$stocks_data['warehouse_id'] = $warehouse_id;
						$stocks_data['product_id'] = $prod_id;
						$stocks_data['product_name'] = $product_name;
						$stocks_data['product_order_id'] = $porder_id[$i];

						$stocks_data['size_id']   	  	= $size_id;
						$stocks_data['size_name']       = $inv_prod['size_name'];
						$stocks_data['group_id']        = $inv_prod['group_id'];
						$stocks_data['color_id']        = $inv_prod['color_id'];
						$stocks_data['color_name']      = $inv_prod['color_name'];
						$stocks_data['sku']             = $inv_prod['sku'];
						$stocks_data['categories']      = $inv_prod['categories'];

						$stocks_data['quantity']    = $quantity[$i];
						$stocks_data['batch_no']    = NULL;
						$stocks_data['item_code']    = $item_code;
						$stocks_data['expiry_date']    = NULL;
						$stocks_data['status'] 	   = 'return';
						$stocks_data['received_date'] = date('Y-m-d', strtotime($date));
						$stocks_data['added_date']  = date("Y-m-d H:i:s");
						$stocks_data['added_by_id']   = $this->session->userdata('super_user_id');
						$stocks_data['added_by_name'] = $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $stocks_data);
					}
				}
			}

			if ($method == 'by_excel') {
				$excelData = array();
				$excelData['is_move'] = 1;
				$excelData['is_complete'] = 1;
				$this->db->where('unique_id', $excel_id);
				$this->db->update('excel_return_stock', $excelData);
			}
		}
		$this->session->set_flashdata('flash_message', "Goods Return Added Successfully !!");
		return simple_json_output($resultpost);
	}

	public function delete_goods_return($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('goods_return_delete_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$query = $this->db->query("SELECT id,warehouse_id,warehouse_name FROM goods_return WHERE id='$id' limit 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$warehouse_id = $row['warehouse_id'];
			$warehouse_name = $row['warehouse_name'];
			$parent_id = $id;

			$query_1 = $this->db->query("SELECT id,product_id,item_code,product_name,quantity,batch_no FROM goods_return_product WHERE parent_id='$id' order by id asc");
			foreach ($query_1->result_array() as $item_1) {

				$prod_id = $item_1['product_id'];
				$batch_no = $item_1['batch_no'];
				$product_name = $item_1['product_name'];
				$item_code = $item_1['item_code'];
				$quantity = $item_1['quantity'];

				// Stock Out
				$query_check = $this->db->query("SELECT id,quantity FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$prod_id' AND item_code='$item_code' limit 1");
				if ($query_check->num_rows() > 0) {
					$gstock       = $query_check->row_array();
					$stock_id     = $gstock['id'];
					$new_quantity = 0;
					$new_quantity = $gstock['quantity'] - $quantity;

					$prod = array();
					$prod['quantity'] = $new_quantity;
					$this->db->where('id', $stock_id);
					$this->db->update('inventory', $prod);

					$stocks_data  = array();
					$stocks_data['order_id'] = $parent_id;
					$stocks_data['parent_id'] = $stock_id;
					$stocks_data['warehouse_name'] = $warehouse_name;
					$stocks_data['warehouse_id'] = $warehouse_id;
					$stocks_data['product_id'] = $prod_id;
					$stocks_data['product_name'] = $product_name;
					$stocks_data['quantity']    = $quantity;
					$stocks_data['item_code']    = $item_code;
					$stocks_data['batch_no']    = $batch_no;
					$stocks_data['status'] 	   = 'sales_return_delete';
					$stocks_data['received_date'] = date("Y-m-d H:i:s");
					$stocks_data['added_date']  = date("Y-m-d H:i:s");
					$stocks_data['added_by_id']   = $this->session->userdata('super_user_id');
					$stocks_data['added_by_name'] = $this->session->userdata('super_name');
					$this->db->insert('inventory_history', $stocks_data);
				}
			}

			$data = array();
			$data['is_deleted'] = '1';
			$this->db->where('id', $id);
			$this->db->update('goods_return', $data);
		}

		$this->session->set_flashdata('flash_message', "Reserved Order Delete Successfully !!");
		return simple_json_output($resultpost);
	}
	/* Goods Return End */

	/* Payment Reconceliation */
	public function get_payment_reconceliation()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (customer_name like '%" . $keyword . "%' OR date like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%' OR order_no like '%" . $keyword . "%')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND DATE(date) = '$from'";
			} else {
				$keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
			}
		}
		//echo $keyword_filter;exit();

		$total_count = $this->db->query("SELECT id FROM payment_reconceliation WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,warehouse_name,company_id,customer_name,company_name,reason,added_date,order_no,date FROM payment_reconceliation WHERE (is_deleted='0') $keyword_filter ORDER BY date DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$company_id = $item['company_id'];

				$view_url = base_url() . 'inventory/payment-reconceliation/view/' . $id;
				$action = '<a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';

				$product_qty = 0;
				$query_pro = $this->db->query("SELECT SUM(quantity) as quantity FROM payment_reconceliation_product WHERE (parent_id='$id') group by parent_id");
				if ($query_pro->num_rows() > 0) {
					$item_1 = $query_pro->row_array();
					$product_qty  = $item_1['quantity'];
				}

				$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"order_id"          => 'GPS_GR_' . $item['id'],
					"order_no"        => $item['order_no'],
					"warehouse_name"        => $item['warehouse_name'],
					"customer_name"        => ($item['customer_name'] != '' && $item['customer_name'] != null) ? $item['customer_name'] : '-',
					"company_name"        => ($company_name != '' && $company_name != null) ? $company_name : '-',
					"reason"        		=> $item['reason'],
					"product_qty"        => $product_qty,
					"date"        => date('d M, Y', strtotime($item['date'])),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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

	public function add_payment_reconceliation($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('payment_reconceliation_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$warehouse_id = $this->input->post('warehouse_id', true);
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$customer_id = $this->input->post('customer_id', true);
		$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'name');
		$company_id = $this->input->post('company_id', true);
		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
		$order_no = $this->input->post('order_no', true);
		$date = $this->input->post('date', true);
		$reason = $this->input->post('reason', true);
		$product_id = $this->input->post('product_id', true);
		$quantity = $this->input->post('sale_quantity', true);
		$amount = $this->input->post('amount', true);
		$porder_id = $this->input->post('porder_id', true);

		$data = array();
		$excel_id = $this->input->post('excel_id');
		$method = 'manually';
		if ($excel_id != '' && $excel_id != NULL) {
			$method = 'by_excel';
		}

		$data['method']      		= $method;
		$data['excel_id']      		= $excel_id;
		$data['warehouse_id']    	= $warehouse_id;
		$data['warehouse_name']    	= $warehouse_name;
		$data['customer_id']    	= $customer_id;
		$data['company_id']    		= $company_id;
		$data['customer_name']    	= $customer_name;
		$data['company_name']    	= $company_name;
		$data['order_no']    		= $order_no;
		$data['date']    			= date('Y-m-d', strtotime($date));
		$data['reason']    			= $reason;
		$data['added_by_id']    	= $this->session->userdata('super_user_id');
		$data['added_by_name']    	= $this->session->userdata('super_name');
		$data['added_date']     	= date("Y-m-d H:i:s");
		$insert = $this->db->insert('payment_reconceliation', $data);
		$parent_id = $this->db->insert_id();

		for ($i = 0; $i < count($product_id); $i++) {
			if ($quantity[$i] > 0 && $product_id != '') {
				$prod = $product_id[$i];
				$sales_order_product = $this->db->where('id', $prod)->get('sales_order_product')->row_array();

				// Update Sales Order Product
				$update_data = [
					'paid_qty' => $quantity[$i],
					'is_paid' => 1,
					'paid_amt' => $amount[$i],
				];
				$this->db->where('id', $prod)->update('sales_order_product', $update_data);

				$prod_id = $sales_order_product['product_id'];
				$size_id = $sales_order_product['size_id'];

				$inv_prod = $this->db->where('product_id', $prod_id)->where('size_id', $size_id)->get('inventory')->row_array();
				$item_code 	= $inv_prod['item_code'];

				$product_name = $this->common_model->selectByidParam($prod_id, 'raw_products', 'name');

				$data_p = array();
				$data_p['parent_id']    	= $parent_id;
				$data_p['product_id']    	= $prod_id;
				$data_p['product_name']    	= $product_name;
				$data_p['sop_id']    	    = $prod;

				$data_p['size_id']    		= $size_id;
				$data_p['size_name']        = $inv_prod['size_name'];
				$data_p['group_id']    		= $inv_prod['group_id'];
				$data_p['color_id']    		= $inv_prod['color_id'];
				$data_p['color_name']       = $inv_prod['color_name'];
				$data_p['quantity']    		= $quantity[$i];
				$data_p['product_order_id'] = $porder_id[$i];
				$data_p['amount']    	    = $amount[$i];

				$data_p['batch_no']    		= NULL;
				$data_p['item_code']    	= $item_code;
				$insert_1 = $this->db->insert('payment_reconceliation_product', $data_p);
			}

			if ($method == 'by_excel') {
				$excelData = array();
				$excelData['is_move'] = 1;
				$excelData['is_complete'] = 1;
				$this->db->where('unique_id', $excel_id);
				$this->db->update('excel_payment_reconceliation_stock', $excelData);
			}
		}
		$this->session->set_flashdata('flash_message', "Payment Reconceliation Added Successfully !!");
		return simple_json_output($resultpost);
	}

	public function get_payment_reconceliation_history($id)
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (grp.item_code like '%" . $keyword . "%' OR grp.product_name like '%" . $keyword . "%' OR grp.batch_no like '%" . $keyword . "%' OR grp.quantity like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT grp.id FROM payment_reconceliation as gr
		INNER JOIN payment_reconceliation_product as grp ON gr.id = grp.parent_id
		Where gr.id = '$id' and gr.is_deleted='0' $keyword_filter")->num_rows();
		$query = $this->db->query("SELECT gr.id,gr.added_date,gr.date,gr.warehouse_name,gr.customer_name,gr.company_name,gr.reason,gr.order_no,grp.product_name,grp.item_code,grp.quantity,grp.batch_no FROM payment_reconceliation as gr
		INNER JOIN payment_reconceliation_product as grp ON gr.id = grp.parent_id
		Where gr.id = '$id' and gr.is_deleted='0' $keyword_filter ORDER BY gr.date DESC LIMIT $start, $length");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {

				$data[] = array(
					"sr_no"       => ++$start,
					"order_id"          => 'GPS_GR_' . $item['id'],
					"order_no"        => $item['order_no'],
					"customer_name"        => $item['customer_name'],
					"company_name"        => ($item['company_name'] != '' && $item['company_name'] != null) ? $item['company_name'] : '-',
					"warehouse_name"        => $item['warehouse_name'],
					"reason"        		=> $item['reason'],
					"product_name"        		=> $item['item_code'] . ' - ' . $item['product_name'],
					"product_qty"        => $item['quantity'],
					"batch_no"        => $item['batch_no'],
					"date"        => date('d M, Y', strtotime($item['date'])),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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
	/* Payment Reconceliation End */

	/* Company Start */

	public function add_company()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('company_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_company_name = $this->check_duplication('on_create', 'name', $name, 'company');
		} else {
			$check_company_name  = true;
		}

		if ($check_company_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('company_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'company Name Duplication'
			);
		} else {
			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->selectByidParam($state_id, 'state_list', 'state');
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->selectByidParam($city_id, 'city_list', 'district');
			} else {
				$city_name = '';
			}


			$data['name']         = $name;
			$data['address']      = clean_and_escape($this->input->post('address'));
			$data['address_2']      = clean_and_escape($this->input->post('address_2'));
			$data['address_3']      = clean_and_escape($this->input->post('address_3'));
			$data['pincode']   = clean_and_escape($this->input->post('pincode'));
			$data['contact_name'] = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']   = clean_and_escape($this->input->post('contact_no'));
			$data['email']   = clean_and_escape($this->input->post('email'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$user_id                = $this->session->userdata('super_user_id');
			$user_name              = $this->session->userdata('super_name');
			$data['state_id']    = $state_id;
			$data['state_name']    = $state_name;
			$data['city_id']    = $city_id;
			$data['city_name']    = $city_name;
			$data['added_by_id']    = $user_id;
			$data['added_by_name']  = $user_name;
			$data['added_date']   = date("Y-m-d H:i:s");

			$this->db->insert('company', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('company_added_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function edit_company($id = "")
	{

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('company_updated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));
		if ($name != '') {
			$check_company_name = $this->check_duplication('on_update', 'name', $name, 'company', $id);
		} else {
			$check_company_name  = true;
		}

		if ($check_company_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('company_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'company Name Duplication'
			);
		} else {

			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->selectByidParam($state_id, 'state_list', 'state');
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->selectByidParam($city_id, 'city_list', 'district');
			} else {
				$city_name = '';
			}


			$data['name']         = $name;
			$data['address']      = clean_and_escape($this->input->post('address'));
			$data['address_2']      = clean_and_escape($this->input->post('address_2'));
			$data['address_3']      = clean_and_escape($this->input->post('address_3'));
			$data['pincode']   = clean_and_escape($this->input->post('pincode'));
			$data['contact_name'] = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']   = clean_and_escape($this->input->post('contact_no'));
			$data['email']   = clean_and_escape($this->input->post('email'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$data['state_id']    = $state_id;
			$data['state_name']    = $state_name;
			$data['city_id']    = $city_id;
			$data['city_name']    = $city_name;

			$this->db->where('id', $id);
			$this->db->update('company', $data);
			$this->session->set_flashdata('flash_message', get_phrase('company_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_company($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('company_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('company', $data);

		return simple_json_output($resultpost);
	}

	public function get_company_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('company');
	}

	public function get_company()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (name like '%" . $keyword . "%' 
            OR contact_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM company WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name,gst_no,contact_name,contact_no,email FROM company WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/company/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/company/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
             ';

				$data[] = array(
					"sr_no"       	=> ++$start,
					"id"          	=> $item['id'],
					"name"        	=> $item['name'],
					"gst_no"       	=> $item['gst_no'],
					"contact_name"	=> ($item['contact_name'] != null && $item['contact_name'] != '') ? $item['contact_name'] : '-',
					"contact_no"		=> ($item['contact_no'] != null && $item['contact_no'] != '') ? $item['contact_no'] : '-',
					"email"   			=> ($item['email'] != null && $item['email'] != '') ? $item['email'] : '-',
					"action"      	=> $action,
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

	/* Company End */
	/* My Company Start */

	public function add_my_company()
	{
			$resultpost = array(
					"status"  => 200,
					"message" => get_phrase('vendor_added_successfully'),
					"url"     => $this->session->userdata('previous_url'),
			);

			$company_id = (int) $this->session->userdata('company_id');
			$name       = trim(clean_and_escape($this->input->post('name')));

			// ✅ Validate name
			if ($name === '') {
					$this->session->set_flashdata('error_message', 'Vendor name is required');
					$resultpost = array(
							"status"  => 400,
							"message" => "Vendor name is required",
					);
					return simple_json_output($resultpost);
			}

			// ✅ New duplication check (same company + same name)
			$exists = $this->db->select('id')
					->from('my_companies')
					->where('company_id', $company_id)
					->where('name', $name)
					->limit(1)
					->get()
					->num_rows();

			if ($exists > 0) {
					$this->session->set_flashdata('error_message', get_phrase('vendor_duplication'));
					$resultpost = array(
							"status"  => 400,
							"message" => 'Vendor Name Duplication'
					);
					return simple_json_output($resultpost);
			}

			// ---- rest of your code same ----
			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
					$state_name = $this->common_model->selectByidParam($state_id, 'state_list', 'state');
			} else {
					$state_name = '';
			}

			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
					$city_name = $this->common_model->selectByidParam($city_id, 'city_list', 'district');
			} else {
					$city_name = '';
			}

			$data['name']          = $name;
			$data['address']       = clean_and_escape($this->input->post('address'));
			$data['address_2']     = clean_and_escape($this->input->post('address_2'));
			$data['address_3']     = clean_and_escape($this->input->post('address_3'));
			$data['pincode']       = clean_and_escape($this->input->post('pincode'));
			$data['contact_name']  = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']    = clean_and_escape($this->input->post('contact_no'));
			$data['email']         = clean_and_escape($this->input->post('email'));
			$data['gst_no']        = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']      = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']    = clean_and_escape($this->input->post('state_code'));

			$user_id               = $this->session->userdata('super_user_id');
			$user_name             = $this->session->userdata('super_name');

			$data['state_id']      = $state_id;
			$data['state_name']    = $state_name;
			$data['city_id']       = $city_id;
			$data['city_name']     = $city_name;
			$data['added_by_id']   = $user_id;
			$data['added_by_name'] = $user_name;
			$data['company_id']    = $company_id;
			$data['added_date']    = date("Y-m-d H:i:s");

			$this->db->insert('my_companies', $data);

			$this->session->set_flashdata('flash_message', get_phrase('vendor_added_successfully'));
			return simple_json_output($resultpost);
	}

	public function edit_my_company($id = "")
	{
			$resultpost = array(
					"status"  => 200,
					"message" => get_phrase('vendor_updated_successfully'),
					"url"     => $this->session->userdata('previous_url'),
			);

			$company_id = (int) $this->session->userdata('company_id');
			$id         = (int) $id;
			$name       = trim(clean_and_escape($this->input->post('name')));

			// ✅ Validate name
			if ($name === '') {
					$this->session->set_flashdata('error_message', 'Vendor name is required');
					$resultpost = array(
							"status"  => 400,
							"message" => "Vendor name is required",
					);
					return simple_json_output($resultpost);
			}

			// ✅ Duplicate check (same company + same name, excluding current id)
			$exists = $this->db->select('id')
					->from('my_companies')
					->where('company_id', $company_id)
					->where('name', $name)
					->where('id !=', $id)
					->limit(1)
					->get()
					->num_rows();

			if ($exists > 0) {
					$this->session->set_flashdata('error_message', get_phrase('vendor_duplication'));
					$resultpost = array(
							"status"  => 400,
							"message" => 'Vendor Name Duplication'
					);
					return simple_json_output($resultpost);
			}

			// ---- rest of your code same ----
			$state_id = $this->input->post('state_id');
			$state_name = ($state_id != '')
					? $this->common_model->selectByidParam($state_id, 'state_list', 'state')
					: '';

			$city_id = $this->input->post('city_id');
			$city_name = ($city_id != '')
					? $this->common_model->selectByidParam($city_id, 'city_list', 'district')
					: '';

			$data['name']         = $name;
			$data['address']      = clean_and_escape($this->input->post('address'));
			$data['address_2']    = clean_and_escape($this->input->post('address_2'));
			$data['address_3']    = clean_and_escape($this->input->post('address_3'));
			$data['pincode']      = clean_and_escape($this->input->post('pincode'));
			$data['contact_name'] = clean_and_escape($this->input->post('contact_name'));
			$data['contact_no']   = clean_and_escape($this->input->post('contact_no'));
			$data['email']        = clean_and_escape($this->input->post('email'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']     = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']   = clean_and_escape($this->input->post('state_code'));
			$data['state_id']     = $state_id;
			$data['state_name']   = $state_name;
			$data['city_id']      = $city_id;
			$data['city_name']    = $city_name;
			$data['company_id']   = $company_id;

			$this->db->where('id', $id);
			$this->db->where('company_id', $company_id); // ✅ safety: can't update other company's vendor
			$this->db->update('my_companies', $data);

			$this->session->set_flashdata('flash_message', get_phrase('vendor_updated_successfully'));
			return simple_json_output($resultpost);
	}


	public function delete_my_company($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('vendor_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('my_companies', $data);

		return simple_json_output($resultpost);
	}

	public function get_my_company_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('my_companies');
	}

	public function get_my_company()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (name like '%" . $keyword . "%' 
            OR contact_name like '%" . $keyword . "%')";
		endif;

		$company_id = $this->session->userdata('company_id');

		$total_count = $this->db->query("SELECT id FROM my_companies WHERE (is_deleted='0') AND company_id='" . $company_id . "' $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name,gst_no,contact_name,contact_no,email FROM my_companies WHERE (is_deleted='0') AND company_id='" . $company_id . "' $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/my-company/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/my-company/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
             ';

				$data[] = array(
					"sr_no"       	=> ++$start,
					"id"          	=> $item['id'],
					"name"        	=> $item['name'],
					"gst_no"       	=> $item['gst_no'],
					"contact_name"	=> ($item['contact_name'] != null && $item['contact_name'] != '') ? $item['contact_name'] : '-',
					"contact_no"		=> ($item['contact_no'] != null && $item['contact_no'] != '') ? $item['contact_no'] : '-',
					"email"   			=> ($item['email'] != null && $item['email'] != '') ? $item['email'] : '-',
					"action"      	=> $action,
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

	public function add_payments()
	{
		$resultpost = array(
				"status"  => 200,
				"message" => "Payment added successfully",
				"url"     => $this->session->userdata('previous_url'),
		);

		// Company + added_by from session
		$company_id = (int) $this->session->userdata('company_id');
		$added_by   = (int) $this->session->userdata('super_user_id');

		// Basic form inputs
		$supplier_id  = (int) $this->input->post('supplier_id');
		$invoice_no   = clean_and_escape($this->input->post('invoice_no'));
		$batch_no     = clean_and_escape($this->input->post('batch_no'));

		$amount_dollar = (float) $this->input->post('amount_dollar');
		$amount_rs     = (float) $this->input->post('amount_rs');
		$amount_rmb    = (float) $this->input->post('amount_rmb');

		$payment_type = clean_and_escape($this->input->post('payment_type')); // official/unofficial
		$bank_account = (int) $this->input->post('bank_account');

		$payment_date = $this->input->post('payment_date');
		$payment_date = $payment_date ? $payment_date : null;

		$narration = clean_and_escape($this->input->post('narration'));

		// Validate supplier
		$supplier = $this->db->get_where('supplier', array('id' => $supplier_id))->row_array();
		if (empty($supplier)) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "Invalid supplier selected.";
				return simple_json_output($resultpost);
		}
		$supplier_name = $supplier['name'];

		// Bank account logic: only required for official
		$bank_accounts_name = null;

		if ($payment_type === 'official') {
				if ($bank_account <= 0) {
						$resultpost['status']  = 400;
						$resultpost['message'] = "Bank account is required for official payment type.";
						return simple_json_output($resultpost);
				}

				$bank = $this->db->get_where('bank_accounts', array('id' => $bank_account))->row_array();
				if (empty($bank)) {
						$resultpost['status']  = 400;
						$resultpost['message'] = "Invalid bank account selected.";
						return simple_json_output($resultpost);
				}
				$bank_accounts_name = $bank['bank_name'];
		} else {
				// unofficial => ignore bank account
				$bank_account = 0;
				$bank_accounts_name = null;
		}

		$data = array(
				'company_id'         => $company_id,
				'supplier_id'        => $supplier_id,
				'supplier_name'      => $supplier_name,
				'invoice_no'         => $invoice_no,
				'batch_no'           => $batch_no,
				'amount_dollar'      => number_format($amount_dollar, 5, '.', ''),
				'amount_rs'          => number_format($amount_rs, 5, '.', ''),
				'amount_rmb'         => number_format($amount_rmb, 5, '.', ''),
				'payment_type'       => $payment_type,
				'bank_account'       => $bank_account,
				'bank_account_name'  => $bank_accounts_name,
				'payment_date'       => $payment_date,
				'narration'          => $narration,
				'is_delete'          => 0,
				'added_by'           => $added_by,
		);

		$this->db->trans_begin();

		$this->db->insert('payments', $data);

		if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost['status']  = 400;
				$resultpost['message'] = "Failed to add payment. Please try again.";
				return simple_json_output($resultpost);
		}

		$this->db->trans_commit();

		$this->session->set_flashdata('flash_message', "Payment added successfully");
		return simple_json_output($resultpost);
	}

	public function edit_payments($param2)
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Payment updated successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$id = (int) $param2;

			// Check existing payment (and not deleted)
			$existing = $this->db->get_where('payments', array('id' => $id, 'is_delete' => 0))->row_array();
			if (empty($existing)) {
					$resultpost['status']  = 400;
					$resultpost['message'] = "Payment not found.";
					return simple_json_output($resultpost);
			}

			// Basic form inputs
			$supplier_id  = (int) $this->input->post('supplier_id');
			$invoice_no   = clean_and_escape($this->input->post('invoice_no'));
			$batch_no     = clean_and_escape($this->input->post('batch_no'));

			$amount_dollar = (float) $this->input->post('amount_dollar');
			$amount_rs     = (float) $this->input->post('amount_rs');
			$amount_rmb    = (float) $this->input->post('amount_rmb');

			$payment_type = clean_and_escape($this->input->post('payment_type')); // official/unofficial
			$bank_account = (int) $this->input->post('bank_account');

			$payment_date = $this->input->post('payment_date');
			$payment_date = $payment_date ? $payment_date : null;

			$narration = clean_and_escape($this->input->post('narration'));

			// Validate supplier
			$supplier = $this->db->get_where('supplier', array('id' => $supplier_id))->row_array();
			if (empty($supplier)) {
					$resultpost['status']  = 400;
					$resultpost['message'] = "Invalid supplier selected.";
					return simple_json_output($resultpost);
			}
			$supplier_name = $supplier['name'];

			// Bank account logic: only required for official
			$bank_accounts_name = null;

			if ($payment_type === 'official') {
					if ($bank_account <= 0) {
							$resultpost['status']  = 400;
							$resultpost['message'] = "Bank account is required for official payment type.";
							return simple_json_output($resultpost);
					}

					$bank = $this->db->get_where('bank_accounts', array('id' => $bank_account))->row_array();
					if (empty($bank)) {
							$resultpost['status']  = 400;
							$resultpost['message'] = "Invalid bank account selected.";
							return simple_json_output($resultpost);
					}
					$bank_accounts_name = $bank['bank_name'];
			} else {
					// unofficial => ignore bank account
					$bank_account = 0;
					$bank_accounts_name = null;
			}

			// Update data (ignore company_id, added_by, created_at, is_delete)
			$data = array(
					'supplier_id'        => $supplier_id,
					'supplier_name'      => $supplier_name,
					'invoice_no'         => $invoice_no,
					'batch_no'           => $batch_no,
					'amount_dollar'      => number_format($amount_dollar, 5, '.', ''),
					'amount_rs'          => number_format($amount_rs, 5, '.', ''),
					'amount_rmb'         => number_format($amount_rmb, 5, '.', ''),
					'payment_type'       => $payment_type,
					'bank_account'       => $bank_account,
					'bank_account_name'  => $bank_accounts_name, // <-- column name per your schema
					'payment_date'       => $payment_date,
					'narration'          => $narration,
			);

			$this->db->trans_begin();

			$this->db->where('id', $id);
			$this->db->update('payments', $data);

			if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					$resultpost['status']  = 400;
					$resultpost['message'] = "Failed to update payment. Please try again.";
					return simple_json_output($resultpost);
			}

			$this->db->trans_commit();

			$this->session->set_flashdata('flash_message', "Payment updated successfully");
			return simple_json_output($resultpost);
	}

	public function get_payments()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
			$keyword = $filter_data['keywords'];
			$keyword_filter = " AND (batch_no LIKE '%" . $keyword . "%' OR invoice_no LIKE '%" . $keyword . "%')";
		}

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$date_range = explode(' - ', $_REQUEST['date_range']);
			$from = date('Y-m-d', strtotime($date_range['0']));
			$to = date('Y-m-d', strtotime($date_range['1']));

			$keyword_filter .= " AND (DATE(payment_date) >= '" . $from . "' AND DATE(payment_date) <= '" . $to . "')";
		}

		$company_id = $this->session->userdata('company_id');
		$total_count = $this->db->query("SELECT id FROM payments WHERE is_delete = '0' AND company_id='" . $company_id . "'" . $keyword_filter)->num_rows();
		$query = $this->db->query("SELECT id, batch_no, supplier_name, payment_type, invoice_no, amount_dollar, amount_rs, amount_rmb, payment_type, payment_date FROM payments WHERE is_delete = '0' AND company_id='" . $company_id . "'" . $keyword_filter . " ORDER BY id DESC LIMIT $start, $length");
		
		// echo $this->db->last_query(); exit();
		if (!empty($query)) {
			$sr_no = $start;
			foreach ($query->result_array() as $item) {

				$actions = '';
				$actions .= '<a href="' . base_url() . 'inventory/payments/edit/'. $item['id'] . '" data-toggle="tooltip" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a> ';
				$actions .= '<a href="#" onclick="confirm_modal(\'' . base_url() . 'inventory/payments/delete/'. $item['id'] . '\',\'Are you sure want to delete!\')" data-toggle="tooltip" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$data[] = array(
					"sr_no"         	=> ++$sr_no,
					"batch_no"      	=> $item['batch_no'],
					"type"						=> get_phrase($item['payment_type']),
					"supplier_name"		=> $item['supplier_name'],
					"amount"        	=> number_format($item['grand_total'], 2),
					"payment_method"	=> $item['payment_type'],
					"invoice_no"			=> ($item['invoice_no']) ? $item['invoice_no'] : '-',
					"amount_dollar"		=> number_format($item['amount_dollar'], 2),
					"amount_inr"			=> number_format($item['amount_rs'], 2),
					"amount_rmb"			=> number_format($item['amount_rmb'], 2),
					"date"          	=> $item['payment_date'] ? date('d M, Y', strtotime($item['payment_date'])) : '-',
					"actions"        	=> $actions,
				);
			}
		}

		$json_data = array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => $total_count,
			"recordsFiltered" => $total_count,
			"data"            => $data
		);

		echo json_encode($json_data);
	}

	public function delete_payments($id)
	{
		$resultpost = array(
				"status"  => 200,
				"message" => "Payment deleted successfully",
				"url"     => $this->agent->referrer(),
		);

		$this->db->trans_begin();

		$this->db->where('id', $id);
		$this->db->update('payments', array('is_delete' => 1));

		if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost['status']  = 400;
				$resultpost['message'] = "Failed to delete payment. Please try again.";
				return simple_json_output($resultpost);
		}

		$this->db->trans_commit();

		$this->session->set_flashdata('flash_message', "Payment deleted successfully");
		return simple_json_output($resultpost);
	}

	/* My Company End */

	public function add_po_expense()
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Expense added successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$data = array();
			$data['input_method']      = $this->input->post('input_method');
			$data['company_id']        = $this->session->userdata('company_id');
			$data['vendor_id']        = (int) $this->input->post('company_id');
			$data['batch_no']          = clean_and_escape($this->input->post('batch_no'));
			$data['type']              = clean_and_escape($this->input->post('type')); // official/unofficial
			$data['expense_type']      = (int) $this->input->post('expense_type');
			$data['gst_type']          = clean_and_escape($this->input->post('gst_type')); // '', igst, cgst_sgst

			$data['payment_type']      = clean_and_escape($this->input->post('payment_type'));
			$data['cheque_no']         = clean_and_escape($this->input->post('cheque_no'));
			$data['company_bank_name'] = clean_and_escape($this->input->post('company_bank_name'));

			$cheque_recv_date         = $this->input->post('cheque_recv_date');
			$data['cheque_recv_date'] = $cheque_recv_date ? $cheque_recv_date : null;

			$cheque_date         = $this->input->post('cheque_date');
			$data['cheque_date'] = $cheque_date ? $cheque_date : null;

			$data['narration']   = clean_and_escape($this->input->post('narration'));
			$data['added_by_id'] = (int) $this->session->userdata('company_id');

			// Totals are already coming from frontend (readonly inputs)
			$sub_total   = (float) $this->input->post('sub_total');
			$gst_total   = (float) $this->input->post('gst_total');
			$grand_total = (float) $this->input->post('grand_total');

			$data['sub_total']   = number_format($sub_total, 5, '.', '');
			$data['gst_total']   = number_format($gst_total, 5, '.', '');
			$data['grand_total'] = number_format($grand_total, 5, '.', '');

			// cheque_amount: prefer posted hidden cheque_amount, else use grand_total
			$posted_cheque_amount = $this->input->post('cheque_amount');
			$cheque_amount = ($posted_cheque_amount !== null && $posted_cheque_amount !== '')
					? (float) $posted_cheque_amount
					: $grand_total;

			$data['cheque_amount'] = number_format($cheque_amount, 5, '.', '');

			// Detail arrays
			$expense_names = (array) $this->input->post('expense_name');
			$amounts       = (array) $this->input->post('amount');
			$gsts          = (array) $this->input->post('gst');
			$gst_amts      = (array) $this->input->post('gst_amt');
			$total_amts    = (array) $this->input->post('total_amt');

			$this->db->trans_begin();

			// Insert parent
			$this->db->insert('po_expense', $data);
			$parent_id = (int) $this->db->insert_id();

			if ($parent_id <= 0) {
					$this->db->trans_rollback();
					$resultpost['status']  = 500;
					$resultpost['message'] = "Failed to add expense. Please try again.";
					return simple_json_output($resultpost);
			}

			// Build & insert child rows
			$details = array();
			$rows = count($expense_names);

			for ($i = 0; $i < $rows; $i++) {
					$name = isset($expense_names[$i]) ? trim($expense_names[$i]) : '';
					$totalAmt = isset($total_amts[$i]) ? (float) $total_amts[$i] : 0;

					// Minimum required per your form: name + total
					if ($name === '' || $totalAmt <= 0) {
							continue;
					}

					$amt    = isset($amounts[$i]) ? (float) $amounts[$i] : 0;
					$gstP   = isset($gsts[$i]) ? (float) $gsts[$i] : 0;
					$gstAmt = isset($gst_amts[$i]) ? (float) $gst_amts[$i] : 0;

					$details[] = array(
							'parent_id'    => $parent_id,
							'expense_name' => clean_and_escape($name),
							'amount'       => number_format($amt, 5, '.', ''),
							'gst'          => number_format($gstP, 2, '.', ''),
							'gst_amt'      => number_format($gstAmt, 5, '.', ''),
							'total_amt'    => number_format($totalAmt, 5, '.', ''),
					);
			}

			if (empty($details)) {
					$this->db->trans_rollback();
					$resultpost['status']  = 400;
					$resultpost['message'] = "Please add at least one valid expense row.";
					return simple_json_output($resultpost);
			}

			$this->db->insert_batch('po_expense_details', $details);

			if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					$resultpost['status']  = 500;
					$resultpost['message'] = "Failed to add expense. Please try again.";
					return simple_json_output($resultpost);
			}

			$this->db->trans_commit();

			$this->session->set_flashdata('flash_message', "Expense added successfully");
			return simple_json_output($resultpost);
	}

	public function edit_po_expense($id)
	{
		$id = (int) $id;

		$resultpost = array(
				"status"  => 200,
				"message" => "Expense updated successfully",
				"url"     => $this->session->userdata('previous_url'),
		);

		// ---- Make sure record exists (and not deleted) ----
		$existing = $this->common_model->getRowById('po_expense', '*', ['is_delete' => '0', 'id' => $id]);
		if (empty($existing)) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "Expense not found.";
				return simple_json_output($resultpost);
		}

		// (Optional but recommended) restrict update to same company
		$session_company_id = (int) $this->session->userdata('company_id');
		if (isset($existing['company_id']) && (int)$existing['company_id'] !== $session_company_id) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "You are not allowed to update this expense.";
				return simple_json_output($resultpost);
		}

		// ---- Build child rows FIRST (so we don't delete old rows if new is invalid) ----
		$expense_names = (array) $this->input->post('expense_name');
		$amounts       = (array) $this->input->post('amount');
		$gsts          = (array) $this->input->post('gst');
		$gst_amts      = (array) $this->input->post('gst_amt');
		$total_amts    = (array) $this->input->post('total_amt');

		$details = array();
		$rows = count($expense_names);

		for ($i = 0; $i < $rows; $i++) {
				$name     = isset($expense_names[$i]) ? trim($expense_names[$i]) : '';
				$totalAmt = isset($total_amts[$i]) ? (float) $total_amts[$i] : 0;

				// minimum required: name + total
				if ($name === '' || $totalAmt <= 0) continue;

				$amt    = isset($amounts[$i]) ? (float) $amounts[$i] : 0;
				$gstP   = isset($gsts[$i]) ? (float) $gsts[$i] : 0;
				$gstAmt = isset($gst_amts[$i]) ? (float) $gst_amts[$i] : 0;

				$details[] = array(
						'parent_id'    => $id,
						'expense_name' => clean_and_escape($name),
						'amount'       => number_format($amt, 5, '.', ''),
						'gst'          => number_format($gstP, 2, '.', ''),
						'gst_amt'      => number_format($gstAmt, 5, '.', ''),
						'total_amt'    => number_format($totalAmt, 5, '.', ''),
				);
		}

		if (empty($details)) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "Please add at least one valid expense row.";
				return simple_json_output($resultpost);
		}

		// ---- Parent update data (IGNORE company_id, added_by_id, created_at) ----
		$data = array();
		$data['input_method']      = $this->input->post('input_method');
		$data['vendor_id']         = (int) $this->input->post('company_id'); // vendor dropdown uses name="company_id"
		$data['batch_no']          = clean_and_escape($this->input->post('batch_no'));
		$data['type']              = clean_and_escape($this->input->post('type'));          // official/unofficial
		$data['expense_type']      = (int) $this->input->post('expense_type');
		$data['gst_type']          = clean_and_escape($this->input->post('gst_type'));      // '', igst, cgst_sgst
		$data['payment_type']      = clean_and_escape($this->input->post('payment_type'));
		$data['cheque_no']         = clean_and_escape($this->input->post('cheque_no'));
		$data['company_bank_name'] = clean_and_escape($this->input->post('company_bank_name'));

		$cheque_recv_date = $this->input->post('cheque_recv_date');
		$data['cheque_recv_date'] = $cheque_recv_date ? $cheque_recv_date : null;

		$cheque_date = $this->input->post('cheque_date');
		$data['cheque_date'] = $cheque_date ? $cheque_date : null;

		$data['narration'] = clean_and_escape($this->input->post('narration'));

		// Totals from frontend (readonly)
		$sub_total   = (float) $this->input->post('sub_total');
		$gst_total   = (float) $this->input->post('gst_total');
		$grand_total = (float) $this->input->post('grand_total');

		$data['sub_total']   = number_format($sub_total, 5, '.', '');
		$data['gst_total']   = number_format($gst_total, 5, '.', '');
		$data['grand_total'] = number_format($grand_total, 5, '.', '');

		// cheque_amount: prefer posted hidden cheque_amount, else use grand_total
		$posted_cheque_amount = $this->input->post('cheque_amount');
		$cheque_amount = ($posted_cheque_amount !== null && $posted_cheque_amount !== '')
				? (float) $posted_cheque_amount
				: $grand_total;

		$data['cheque_amount'] = number_format($cheque_amount, 5, '.', '');

		// ---- Transaction: update parent, delete old children, insert new children ----
		$this->db->trans_begin();

		$this->db->where('id', $id);
		$this->db->where('is_delete', 0);
		$this->db->update('po_expense', $data);

		// Delete existing list items first (as you asked)
		$this->db->where('parent_id', $id)->delete('po_expense_details');

		// Insert new list items
		$this->db->insert_batch('po_expense_details', $details);

		if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost['status']  = 400;
				$resultpost['message'] = "Failed to update expense. Please try again.";
				return simple_json_output($resultpost);
		}

		$this->db->trans_commit();

		$this->session->set_flashdata('flash_message', "Expense updated successfully");
		return simple_json_output($resultpost);
	}

	public function delete_po_expense($id) {
		$resultpost = array("status" => 200, "message" => "Expense deleted successfully", "url" => site_url('inventory/po-expense'));

		$this->db->trans_begin();

		$this->db->where('id', $id);
		$this->db->where('is_delete', 0);
		$this->db->update('po_expense', ['is_delete' => 1]);

		if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost['status']  = 400;
				$resultpost['message'] = "Failed to delete expense. Please try again.";
				return simple_json_output($resultpost);
		}

		$this->db->trans_commit();

		$this->session->set_flashdata('flash_message', "Expense deleted successfully");
		return simple_json_output($resultpost);
	}

	public function get_po_expense()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
			$keyword = $filter_data['keywords'];
			$keyword_filter = " AND (batch_no LIKE '%" . $keyword . "%')";
		}

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$date_range = explode(' - ', $_REQUEST['date_range']);
			$from = date('Y-m-d', strtotime($date_range['0']));
			$to = date('Y-m-d', strtotime($date_range['1']));

			$keyword_filter .= " AND (DATE(cheque_date) >= '" . $from . "' AND DATE(cheque_date) <= '" . $to . "')";
		}

		$company_id = $this->session->userdata('company_id');

		$total_count = $this->db->query("SELECT id FROM po_expense WHERE company_id='" . $company_id . "' AND is_delete = '0' " . $keyword_filter)->num_rows();
		$query = $this->db->query("SELECT id, batch_no, type, expense_type, vendor_id, grand_total, payment_type, cheque_date FROM po_expense WHERE company_id='" . $company_id . "' AND is_delete = '0' " . $keyword_filter . " ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			$sr_no = $start;
			foreach ($query->result_array() as $item) {
				$company_name = $this->common_model->selectByidsParam(['id' => $item['vendor_id']], 'my_companies', 'name');
				$expense_type = $this->common_model->selectByidsParam(['id' => $item['expense_type']], 'expense_type', 'name');	

				$actions = '';
				$actions .= '<a href="' . base_url() . 'inventory/po-expense/edit/'. $item['id'] . '" data-toggle="tooltip" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a> ';
				$actions .= '<a href="#" onclick="confirm_modal(\'' . base_url() . 'inventory/po_expense/delete/'. $item['id'] . '\',\'Are you sure want to delete!\')" data-toggle="tooltip" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$data[] = array(
					"sr_no"         	=> ++$sr_no,
					"batch_no"      	=> $item['batch_no'],
					"company_name"  	=> ($company_name) ? $company_name : '-',
					"amount"        	=> number_format($item['grand_total'], 2),
					"payment_method"	=> $item['payment_type'],
					"type"						=> get_phrase($item['type']),
					"expense_type"		=> ($expense_type) ? $expense_type : '-',
					"date"          	=> $item['cheque_date'] ? date('d M, Y', strtotime($item['cheque_date'])) : '-',
					"action"					=> $actions,
				);
			}
		}

		$json_data = array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => $total_count,
			"recordsFiltered" => $total_count,
			"data"            => $data
		);

		echo json_encode($json_data);
	}

	/* Purchase Return Start */
	public function get_purchase_return()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (supplier_name like '%" . $keyword . "%' OR date like '%" . $keyword . "%' OR warehouse_name like '%" . $keyword . "%' OR invoice_no like '%" . $keyword . "%')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND DATE(date) = '$from'";
			} else {
				$keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
			}
		}

		$total_count = $this->db->query("SELECT id FROM purchase_return WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id,warehouse_name,supplier_name,reason,added_date,invoice_no,date FROM purchase_return WHERE (is_deleted='0') $keyword_filter ORDER BY date DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$view_url = base_url() . 'inventory/purchase-return/view/' . $id;
				$action = '<a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';

				$delete_url = base_url() . 'inventory/purchase_return/delete_post/' . $id;
				$action .= '<a href="#" onclick="confirm_modal(\'' . $delete_url . '\',\'Are you sure want to delete!\')" data-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Delete" aria-label="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$product_qty = 0;
				$product_amount = 0;
				$query_pro = $this->db->query("SELECT SUM(quantity) as quantity,SUM(amount) as amount FROM purchase_return_product WHERE (parent_id='$id') group by parent_id");
				if ($query_pro->num_rows() > 0) {
					$item_1 = $query_pro->row_array();
					$product_qty  = $item_1['quantity'];
					$product_amount  = $item_1['amount'];
				}


				/*
				if(count($product_name) > 0){
					$product_name = '<span>'.$product_name.'</span>';
				}
				*/

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"order_id"          => 'GPS_PR_' . $item['id'],
					"invoice_no"        => $item['invoice_no'],
					"warehouse_name"        => $item['warehouse_name'],
					"supplier_name"        => ($item['supplier_name'] != '' && $item['supplier_name'] != null) ? $item['supplier_name'] : '-',
					"reason"        		=> $item['reason'],
					"product_qty"        => $product_qty,
					"product_amount"        => $product_amount,
					"date"        => date('d M, Y', strtotime($item['date'])),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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

	public function add_purchase_return($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_return_added_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		date_default_timezone_set('Asia/Kolkata');
		$warehouse_id = $this->input->post('warehouse_id', true);
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$supplier_id = $this->input->post('supplier_id', true);
		$supplier_name = $this->common_model->selectByidParam($supplier_id, 'supplier', 'name');
		$date = $this->input->post('date', true);
		$invoice_no = $this->input->post('invoice_no', true);
		$reason = $this->input->post('reason', true);
		$product_id = $this->input->post('product_id', true);
		$quantity = $this->input->post('quantity', true);
		$amount = $this->input->post('amount', true);
		$batch_no_ = $this->input->post('batch_no', true);

		$data = array();

		$excel_id = $this->input->post('excel_id');
		$method = 'manually';
		if ($excel_id != '' && $excel_id != NULL) {
			$method = 'by_excel';
		}

		$data['method']      		= $method;
		$data['excel_id']      		= $excel_id;
		$data['warehouse_id']    	= $warehouse_id;
		$data['warehouse_name']    	= $warehouse_name;
		$data['reason']    			= $reason;
		$data['supplier_id']    	= $supplier_id;
		$data['supplier_name']    	= $supplier_name;
		$data['invoice_no']    		= $invoice_no;
		$data['date']    			= $date;
		$data['added_by_id']    	= $this->session->userdata('super_user_id');
		$data['added_by_name']    	= $this->session->userdata('super_name');
		$data['added_date']     	= date("Y-m-d H:i:s");
		$insert = $this->db->insert('purchase_return', $data);
		$parent_id = $this->db->insert_id();

		for ($i = 0; $i < count($product_id); $i++) {
			if ($quantity[$i] > 0 && $product_id != '') {
				$prod = $product_id[$i];
				$pro = explode('|', $prod);
				$prod_id = $pro[0];
				$size_id = $pro[1];

				$inv_prod = $this->db->where('product_id', $prod_id)->where('size_id', $size_id)->get('inventory')->row_array();
				$item_code = $inv_prod['item_code'];

				$batch_no = ($batch_no_[$i] == '-') ? '' : $batch_no_[$i];
				$product_name = $this->common_model->selectByidParam($prod_id, 'raw_products', 'name');

				$data_p = array();
				$data_p['parent_id']    	= $parent_id;
				$data_p['product_id']    	= $prod_id;
				$data_p['product_name']    	= $product_name;

				$data_p['size_id']          = $size_id;
				$data_p['size_name']        = $inv_prod['size_name'];
				$data_p['group_id']         = $inv_prod['group_id'];
				$data_p['color_id']         = $inv_prod['color_id'];
				$data_p['color_name']       = $inv_prod['color_name'];

				$data_p['quantity']    		= $quantity[$i];
				$data_p['amount']    		= $amount[$i];
				$data_p['batch_no']    		= NULL;
				$data_p['item_code']    	= $item_code;
				$insert_1 = $this->db->insert('purchase_return_product', $data_p);

				if ($insert_1) {
					// Stock Out
					$query_check = $this->db->query("SELECT id,quantity,expiry_date FROM inventory WHERE warehouse_id='$warehouse_id' AND product_id='$prod_id' and item_code='$item_code' limit 1");
					if ($query_check->num_rows() > 0) {
						$gstock       = $query_check->row_array();
						$stock_id     = $gstock['id'];
						$expiry_date     = $gstock['expiry_date'];
						$new_quantity = 0;
						$new_quantity = $gstock['quantity'] - $quantity[$i];

						$prod = array();
						$prod['quantity'] = $new_quantity;
						$this->db->where('id', $stock_id);
						$this->db->update('inventory', $prod);


						$stocks_data  = array();
						$stocks_data['order_id'] 		= $parent_id;
						$stocks_data['parent_id']		= $stock_id;
						$stocks_data['warehouse_name'] 	= $warehouse_name;
						$stocks_data['warehouse_id'] 	= $warehouse_id;
						$stocks_data['product_id'] 		= $prod_id;
						$stocks_data['product_name'] 	= $product_name;

						$stocks_data['size_id']   	  	= $size_id;
						$stocks_data['size_name']         = $inv_prod['size_name'];
						$stocks_data['group_id']          = $inv_prod['group_id'];
						$stocks_data['color_id']          = $inv_prod['color_id'];
						$stocks_data['color_name']        = $inv_prod['color_name'];
						$stocks_data['sku']               = $inv_prod['sku'];
						$stocks_data['categories']        = $inv_prod['categories'];

						$stocks_data['quantity']    	= $quantity[$i];
						$stocks_data['batch_no']    	= NULL;
						$stocks_data['item_code']    	= $item_code;
						$stocks_data['expiry_date']    	= NULL;
						$stocks_data['status'] 	   		= 'purchase_out';
						$stocks_data['received_date'] 	= $date;
						$stocks_data['added_date']  	= date("Y-m-d H:i:s");
						$stocks_data['added_by_id']		= $this->session->userdata('super_user_id');
						$stocks_data['added_by_name'] 	= $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $stocks_data);
					}
				}
			}
		}

		if ($method == 'by_excel') {
			$excelData = array();
			$excelData['is_move'] = 1;
			$excelData['is_complete'] = 1;
			$this->db->where('unique_id', $excel_id);
			$this->db->update('excel_return_stock', $excelData);
		}

		$this->session->set_flashdata('flash_message', "Purchase Return Added Successfully !!");
		return simple_json_output($resultpost);
	}

	public function delete_purchase_return($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_return_delete_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$pr = $this->common_model->getRowById('purchase_return', '*', array('id' => $id));
		if ($pr != '') {
			$pr_products = $this->common_model->getResultById('purchase_return_product', 'product_id, product_name, item_code, quantity, size_id', ['parent_id' => $id]);
			if ($pr_products != '') {
				foreach ($pr_products as $prod) {
					$size_id = $prod['size_id'];
					$item_code = $prod['item_code'];
					$product_id = $prod['product_id'];
					$quantity = $prod['quantity'];

					$inv = $this->common_model->getRowById('inventory', '*', array('size_id' => $size_id, 'product_id' => $product_id, 'warehouse_id' => $pr['warehouse_id']));
					if ($inv != '') {
						$new_qty = $inv['quantity'] + $quantity;
						$this->db->where('id', $inv['id'])->update('inventory', ['quantity' => $new_qty]);

						$stocks_data  = array();
						$stocks_data['order_id'] 		= $id;
						$stocks_data['parent_id']		= $inv['id'];
						$stocks_data['warehouse_name'] 	= $inv['warehouse_name'];
						$stocks_data['warehouse_id'] 	= $inv['warehouse_id'];
						$stocks_data['product_id'] 		= $product_id;
						$stocks_data['product_name'] 	= $inv['product_name'];

						$stocks_data['size_id']   	  	  = $size_id;
						$stocks_data['size_name']         = $inv['size_name'];
						$stocks_data['group_id']          = $inv['group_id'];
						$stocks_data['color_id']          = $inv['color_id'];
						$stocks_data['color_name']        = $inv['color_name'];
						$stocks_data['sku']               = $inv['sku'];
						$stocks_data['categories']        = $inv['categories'];

						$stocks_data['quantity']    	= $quantity;
						$stocks_data['batch_no']    	= NULL;
						$stocks_data['item_code']    	= $item_code;
						$stocks_data['expiry_date']    	= NULL;
						$stocks_data['status'] 	   		= 'purchase_return_delete';
						$stocks_data['received_date'] 	= date("Y-m-d H:i:s");
						$stocks_data['added_date']  	= date("Y-m-d H:i:s");
						$stocks_data['added_by_id']		= $this->session->userdata('super_user_id');
						$stocks_data['added_by_name'] 	= $this->session->userdata('super_name');
						$this->db->insert('inventory_history', $stocks_data);
					}
				}
			}
			$this->db->where('id', $id)->update('purchase_return', ['is_deleted' => 1]);
		} else {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('some_error_occured'),
			);
		}

		$this->session->set_flashdata('flash_message', "Purchase Return Delete Successfully !!");
		return simple_json_output($resultpost);
	}

	public function get_purchase_return_history($id)
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (grp.item_code like '%" . $keyword . "%' OR grp.product_name like '%" . $keyword . "%' OR grp.batch_no like '%" . $keyword . "%' OR grp.quantity like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT grp.id FROM purchase_return as gr
INNER JOIN purchase_return_product as grp ON gr.id = grp.parent_id
Where gr.id = '$id' and gr.is_deleted='0' $keyword_filter")->num_rows();
		$query = $this->db->query("SELECT gr.id,gr.added_date,gr.date,gr.warehouse_name,gr.supplier_name,gr.reason,gr.invoice_no,grp.product_name,grp.item_code,grp.quantity,grp.batch_no,grp.amount FROM purchase_return as gr
INNER JOIN purchase_return_product as grp ON gr.id = grp.parent_id
Where gr.id = '$id' and gr.is_deleted='0' $keyword_filter ORDER BY gr.date DESC LIMIT $start, $length");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {

				$data[] = array(
					"sr_no"       => ++$start,
					"order_id"          => 'GPS_GR_' . $item['id'],
					"invoice_no"        => $item['invoice_no'],
					"supplier_name"        => $item['supplier_name'],
					"warehouse_name"        => $item['warehouse_name'],
					"reason"        		=> $item['reason'],
					"product_name"        		=> $item['item_code'] . ' - ' . $item['product_name'],
					"product_qty"        => $item['quantity'],
					"batch_no"        => $item['batch_no'],
					"amount"        => $item['amount'],
					"date"        => date('d M, Y', strtotime($item['date'])),
					"added_date"        => date('d M, Y', strtotime($item['added_date'])),
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

	/* Purchase Return End */

	/* Manage Access Start */

	public function get_access_type()
	{
		$query = $this->db->query("SELECT id,name FROM access_manager order by id asc");
		return $query;
	}

	public function get_manage_access()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (name like '%" . $keyword . "%' 
            OR contact_name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM access WHERE (id<>'') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name,access_id FROM access WHERE (id<>'') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$access_name = $this->common_model->getBulkNameIds('access_manager', 'name', $item['access_id']);

				$delete_url = "confirm_modal('" . base_url() . "inventory/manage_access/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/access/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				';

				$data[] = array(
					"sr_no" => (++$start),
					"id" => $item['id'],
					"name"    => $item['name'],
					"access"    => $access_name,
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

	public function add_access()
	{
		$resultpost = array(
			"status" => 200,
			"message" => 'success'
		);

		$name = $this->input->post('name');
		$access_id = implode(',', $this->input->post('access_id'));
		$user = $this->session->userdata('super_user_id');
		$check_email = $this->db->query("SELECT id FROM access WHERE name='$name' and user_id='$user' limit 1")->num_rows();

		if ($check_email == 1) {
			$resultpost    = array(
				"status" => 400,
				"message" => 'Access Name Already Exists !!!',
			);
		} else {

			$data['name']        = $name;
			$data['user_id']        = $user;
			$data['access_id']            = $access_id;
			$this->db->insert('access', $data);
			$user_id = $this->db->insert_id();
			$this->session->set_flashdata('flash_message', get_phrase('access_added_successfully'));
		}

		return simple_json_output($resultpost);
	}

	public function get_access_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('access');
	}

	public function edit_access($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => 'success'
		);

		$user = $this->session->userdata('super_user_id');
		$name = $this->input->post('name');
		$access_id = implode(',', $this->input->post('access_id'));

		$check_email = $this->db->query("SELECT id FROM access WHERE  name='$name' and user_id='$user' and id!='$id' limit 1")->num_rows();

		if ($check_email == 1) {
			$resultpost    = array(
				"status" => 400,
				"message" => 'Email ID Already Exists !!!',
			);
		} else {
			$data['name']        = $name;
			$data['access_id']   = $access_id;
			$this->db->where('id', $id);
			$this->db->update('access', $data);
			$this->session->set_flashdata('flash_message', get_phrase('access_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_access($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('access');
		echo json_encode(array(
			'status' => 200,
			'message' => 'Access Deleted Successfully',
			'url' => base_url() . 'inventory/manage-access',
		));
	}

	/* Manage Access End */


	/* Manage Staff End */

	public function get_staff_access()
	{
		$query = $this->db->query("SELECT id,name FROM access order by name asc");
		return $query;
	}

	public function get_manage_staff()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($_REQUEST['keywords']) && $_REQUEST['keywords'] != ""):
			$keyword        = $_REQUEST['keywords'];
			$keyword_filter .= " AND (first_name like '%" . $keyword . "%')";
		endif;

		$keyword_filter .= " AND id!= 4 ";

		$total_count = $this->db->query("Select id,is_deleted FROM sys_users WHERE (id<>'') and is_deleted ='0' $keyword_filter ORDER BY id desc")->num_rows();
		$query = $this->db->query("SELECT id, first_name, last_name, email, phone, designation, status FROM sys_users WHERE (id<>'') and is_deleted='0' $keyword_filter ORDER BY id desc LIMIT $start,$length");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$edit_url = base_url() . 'inventory/staff/edit/' . $id;
				$delete_url = "confirm_modal('" . base_url() . "inventory/manage_staff/delete/" . $id . "','Are you sure want to delete!')";
				$pass_url = base_url() . 'inventory/staff_form/change_password/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				<a href="' . $pass_url . '" data-toggle="tooltip" data-bs-placement="top" title="Change Password"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
				';

				$data[] = array(
					"sr_no" => (++$start),
					"id" => $item['id'],
					"name"    => $item['first_name'] . ' ' . $item['last_name'],
					"phone"  => $item['phone'],
					"email"   => $item['email'],
					"designation"   => ($item['designation'] != '' || $item['designation'] != null) ? $item['designation'] : '-',
					"status"   => $item['status'],
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

	public function add_staff()
	{
		$resultpost = array(
			"status" => 200,
			"message" => 'success',
			"url" => base_url() . 'inventory/manage-staff',
		);

		$email = $this->input->post('email');
		$phone = $this->input->post('phone');

		$check_email = $this->db->query("SELECT id FROM sys_users WHERE  email='$email' and is_deleted='0' limit 1")->num_rows();
		$check_phone = $this->db->query("SELECT id FROM sys_users WHERE  phone='$phone' and is_deleted='0' limit 1")->num_rows();

		if ($check_email == 1) {
			$resultpost    = array(
				"status" => 400,
				"message" => 'Email ID Already Exists !!!',
			);
		} else if ($check_phone == 1) {
			$resultpost    = array(
				"status" => 400,
				"message" => 'Mobile No. Already Exists !!!',
			);
		} else {

			$company_id = $this->input->post('company_id');

			$temp_path = $this->upload_model->upload_temp_image('profile_img');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/staff/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['profile_img'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$temp_path = $this->upload_model->upload_temp_image('aadhar_photo');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/staff/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['aadhar_photo'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$temp_path = $this->upload_model->upload_temp_image('pan_photo');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/staff/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['pan_photo'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$data['first_name']       = html_escape($this->input->post('first_name'));
			$data['email']            = html_escape($this->input->post('email'));
			$data['phone']            = html_escape($this->input->post('phone'));
			$data['password']         = sha1(html_escape($this->input->post('password')));
			$data['status']           = 1;
			$data['staff_access']			= $this->input->post('staff_access');
			$data['type']							= 'staff';
			$data['role_id']					= '3';
			$data['company_id']					= implode(',', $company_id);
			$data['address']					= $this->input->post('address');
			$data['remark']						= $this->input->post('remark');
			$data['aadhar_no']					= $this->input->post('aadhar_no');
			$data['pan_no']					= $this->input->post('pan_no');
			$data['added_by']         = $this->session->userdata('super_user_id');
			$data['date_added']       = date("Y-m-d H:i:s");
			$this->db->insert('sys_users', $data);
			$this->session->set_flashdata('flash_message', get_phrase('added_staff_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function get_staff_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('sys_users');
	}

	public function edit_staff($id = "")
	{

		$resultpost = array(
			"status" => 200,
			"message" => 'success',
			"url" => base_url() . 'inventory/manage-staff'
		);

		$email = $this->input->post('email');
		$phone = $this->input->post('phone');

		$check_email = $this->db->query("SELECT id FROM sys_users WHERE  email='$email' and is_deleted='0' and id!='$id' limit 1")->num_rows();
		$check_phone = $this->db->query("SELECT id FROM sys_users WHERE  phone='$phone' and is_deleted='0' and id!='$id' limit 1")->num_rows();

		if ($check_email == 1) {
			$resultpost    = array(
				"status" => 400,
				"message" => 'Email ID Already Exists !!!',
			);
		} else if ($check_phone == 1) {
			$resultpost    = array(
				"status" => 400,
				"message" => 'Mobile No. Already Exists !!!',
			);
		} else {
			$company_id = $this->input->post('company_id');

			$temp_path = $this->upload_model->upload_temp_image('profile_img');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/staff/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['profile_img'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$temp_path = $this->upload_model->upload_temp_image('aadhar_photo');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/staff/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['aadhar_photo'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$temp_path = $this->upload_model->upload_temp_image('pan_photo');
			if (!empty($temp_path)) {
				$year      = date("Y");
				$month     = date("m");
				$day       = date("d");
				$directory = "uploads/staff/" . "$year/$month/$day/";

				if (!is_dir($directory)) {
					mkdir($directory, 0755, true);
				}

				$data['pan_photo'] = $this->upload_model->flash_image_upload($temp_path, $directory);
				$this->upload_model->delete_temp_image($temp_path);
			}

			$data['first_name'] = html_escape($this->input->post('first_name'));
			$data['email']      = html_escape($this->input->post('email'));
			$data['phone']      = html_escape($this->input->post('phone'));
			$data['staff_access'] = $this->input->post('staff_access');
			$data['company_id'] = is_array($company_id) ? implode(',', $company_id) : $company_id;
			$data['address']    = $this->input->post('address');
			$data['remark']						= $this->input->post('remark');
			$data['aadhar_no']  = $this->input->post('aadhar_no');
			$data['pan_no']     = $this->input->post('pan_no');
			$this->db->where('id', $id);
			$this->db->update('sys_users', $data);
			$this->session->set_flashdata('flash_message', get_phrase('staff_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_staff($id)
	{
		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('sys_users', $data);
		echo json_encode(array(
			"status" => 200,
			"message" => 'Staff Deleted Succesfully',
			"url" => base_url() . 'inventory/manage-staff',
		));
	}

	public function edit_change_password($id = "")
	{
		$resultpost = array(
			"status" => 200,
			"message" => 'success'
		);

		$new_password = $this->input->post('new_password');
		$confirm_password = $this->input->post('confirm_password');

		if ($new_password != $confirm_password) {
			$resultpost    = array(
				"status" => 400,
				"message" => 'Password Does Not Match !!!',
			);
		} else {
			$data['password'] = sha1($this->input->post('new_password'));
			$this->db->where('id', $id);
			$this->db->update('sys_users', $data);
			$this->session->set_flashdata('flash_message', get_phrase('password_change_successfully'));
		}
		return simple_json_output($resultpost);
	}

	/* Manage Staff End */

	// Size Start
	public function get_filter_attribute($attribute_id)
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$search_value = $_REQUEST['search']['value'];
		$data = array();
		$keyword_filter = "";
		if (!empty($search_value)) {
			$keyword = $search_value;
			$keyword_filter = " AND (name like '%" . $keyword . "%')";
			$total_count = $this->db->query("SELECT id FROM oc_attribute_values WHERE attribute_id='$attribute_id' $keyword_filter ORDER BY `sort` ASC")->num_rows();
			$query = $this->db->query("SELECT id,name,color_type,color_code,color_image,status FROM oc_attribute_values WHERE attribute_id='$attribute_id' $keyword_filter ORDER BY `sort` ASC LIMIT $start, $length");
		} else {
			$total_count = $this->db->query("SELECT id FROM oc_attribute_values WHERE attribute_id='$attribute_id' $keyword_filter ORDER BY `sort` ASC")->num_rows();
			$query = $this->db->query("SELECT id,name,color_type,color_code,color_image,status FROM oc_attribute_values WHERE attribute_id='$attribute_id' $keyword_filter ORDER BY `sort` ASC LIMIT $start, $length");
		}
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$status = '';
				if ($item['status'] == 1) {
					$status = '<div class="label label-success">Active</div>';
				} else {
					$status = '<div class="label label-danger">Inactive</div>';
				}

				$code = $item['color_code'];

				$edit_url = $delete_url = '';
				if ($attribute_id == 1) {
					$edit_url = base_url() . 'product-fabric/edit/' . $item['id'];
					$delete_url = base_url() . 'product_color/delete/' . $item['id'];
				} elseif ($attribute_id == 2) {
					$edit_url = base_url() . 'inventory/product-size/edit/' . $item['id'];
					$delete_url = base_url() . 'inventory/product_size/delete/' . $item['id'];
				} elseif ($attribute_id == 3) {
					$edit_url = base_url() . 'product-taper/edit/' . $item['id'];
					$delete_url = base_url() . 'product_taper/delete/' . $item['id'];
				} elseif ($attribute_id == 4) {
					$edit_url = base_url() . 'product-length/edit/' . $item['id'];
					$delete_url = base_url() . 'product_length/delete/' . $item['id'];
				}

				$confim_txt = "Confirm Delete";
				$action = '<a href="' . $edit_url . '" class="btn btn-warning btn_edit" data-toggle="tooltip" data-tooltip="Edit"><i class="fa fa-edit"></i></a>';

				$data[] = array(
					"sr_no"     => ++$start,
					"name"      => $item['name'],
					"color_code" => $code,
					"status"    => $status,
					"action"    => $action
				);
			}
		}

		$json_data = array(
			"draw"              => intval($params['draw']),
			"recordsTotal"      => $total_count,
			"recordsFiltered"   => $total_count,
			"data"              => $data
		);
		echo json_encode($json_data);
	}

	public function add_product_size()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('product_size_added_successfully'),
			"url" => base_url('inventory/product-size'),
		);

		$name = html_escape($this->input->post('name'));
		$check_name = true;
		if ($name != '') {
			$check_name = $this->common_model->check_attribute_duplication('on_create', 'oc_attribute_values', 'name', $name, '2');
		}

		if ($check_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('name_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Name Duplication'
			);
		} else {
			$color_code = html_escape($this->input->post('color_code'));
			$check_name = true;
			if ($color_code != '') {
				$check_name = $this->common_model->check_attribute_duplication('on_create', 'oc_attribute_values', 'color_code', $color_code, '2');
			}

			if ($check_name == false) {
				$this->session->set_flashdata('error_message', get_phrase('name_duplication'));
				$resultpost = array(
					"status" => 400,
					"message" => 'ID Duplication'
				);
			} else {
				$cname = 'size-' . $name;
				$slug = $this->common_model->create_unique_slug('oc_attribute_values', 'slug', $cname);

				$data['attribute_id']   = 2;
				$data['attr_name']      = 'Size';
				$data['name']           = $name;
				$data['color_code']     = $color_code;
				$data['slug']           = $slug;
				$data['status']         = html_escape($this->input->post('status'));
				$data['created_at']     = date("Y-m-d H:i:s");
				$this->db->insert('oc_attribute_values', $data);
				$this->session->set_flashdata('flash_message', get_phrase('product_size_added_successfully'));
			}
		}

		return simple_json_output($resultpost);
	}

	public function edit_product_size($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('product_size_updated_successfully'),
			"url" => base_url('inventory/product-size'),
		);

		$name = html_escape($this->input->post('name'));
		$check_name = true;
		if ($name != '') {
			$check_name = $this->common_model->check_attribute_duplication('on_update', 'oc_attribute_values', 'name', $name, '2', $id);
		}

		if ($check_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('name_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Name Duplication'
			);
		} else {
			$color_code = html_escape($this->input->post('color_code'));
			$check_name = true;
			if ($color_code != '') {
				$check_name = $this->common_model->check_attribute_duplication('on_update', 'oc_attribute_values', 'color_code', $color_code, '2', $id);
			}

			if ($check_name == false) {
				$this->session->set_flashdata('error_message', get_phrase('name_duplication'));
				$resultpost = array(
					"status" => 400,
					"message" => 'ID Duplication'
				);
			} else {
				$cname = 'size-' . $name;
				$slug = $this->common_model->create_unique_slug('oc_attribute_values', 'slug', $cname, $id);

				$data['attribute_id']   = 2;
				$data['attr_name']      = 'Size';
				$data['name']           = $name;
				$data['color_code']     = $color_code;
				$data['slug']           = $slug;
				$data['status']         = html_escape($this->input->post('status'));
				$this->db->where('id', $id);
				$this->db->update('oc_attribute_values', $data);
				$this->session->set_flashdata('flash_message', get_phrase('product_size_updated_successfully'));
			}
		}
		return simple_json_output($resultpost);
	}

	// Size Ends

	// Color Starts

	public function get_products_color()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];
		$search_value = $_REQUEST['search']['value'];
		$data = array();
		$keyword_filter = "";

		if (isset($search_value) && $search_value != ""):
			$keyword        = $search_value;
			$keyword_filter = " AND (name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT id FROM colors WHERE (id<>'') $keyword_filter ORDER BY id desc")->num_rows();
		$query = $this->db->query("SELECT id,name,status FROM colors WHERE (id<>'') $keyword_filter ORDER BY id desc LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				if ($item['status'] == 1) {
					$status = '<div class="label label-success">Active</div>';
				} else {
					$status = '<div class="label label-danger">Inactive</div>';
				}

				$url = base_url() . 'inventory/product-color/edit/' . $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/product_color/delete/" . $item['id'] . "', 'Are You Sure')";

				$action = '<a href="' . $url . '" class="btn btn-warning btn_edit" data-toggle="tooltip" data-tooltip="Edit"><i class="fa fa-edit"></i></a>';
				$action .= '<a href="#" class="btn btn-danger btn_edit mx-1" onclick="' . $delete_url . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete"><i class="fa fa-trash"></i></a>';

				$data[] = array(
					"sr_no"			=> ++$start,
					"name"		=> $item['name'],
					"status"       	=> $status,
					"action"       	=> $action,
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

	public function add_product_color()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('product_color_added_successfully'),
			"url" => base_url('inventory/product-color'),
		);

		$name = html_escape($this->input->post('name'));
		if ($name != '') {
			$check_name = $this->common_model->check_common_duplication('on_create', 'colors', 'name', $name);
		} else {
			$check_name = true;
		}

		if ($check_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('name_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Name Duplication'
			);
		} else {
			$data['name']           = $name;
			$data['color_code']     = html_escape($this->input->post('color_code'));
			$data['status']         = html_escape($this->input->post('status'));
			$this->db->insert('colors', $data);
			$this->session->set_flashdata('flash_message', get_phrase('color_added_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function edit_product_color($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('product_color_updated_successfully'),
			"url" => base_url('inventory/product-color'),
		);

		$name = html_escape($this->input->post('name'));
		if ($name != '') {
			$check_name = $this->common_model->check_common_duplication('on_update', 'oc_attribute_values', 'name', $name, $id);
		} else {
			$check_name = true;
		}

		if ($check_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('name_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Name Duplication'
			);
		} else {
			$data['name']           = $name;
			$data['color_code']     = html_escape($this->input->post('color_code'));
			$data['status']         = html_escape($this->input->post('status'));
			$this->db->where('id', $id);
			$this->db->update('colors', $data);
			$this->session->set_flashdata('flash_message', get_phrase('product_color_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function delete_product_color($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('colors');
		$this->session->set_flashdata('flash_message', get_phrase('product_color_deleted_successfully'));
	}

	// Color Ends

	// Category Start

	public function add_category()
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('category_added_successfully'),
			"url" => base_url() . 'inventory/category/add',
		);
		//set parent id
		$data["parent_id"] = 0;
		$data["parent_name"] = "";
		$category_ids_array = $this->input->post('parent_id', true);
		if (!empty($category_ids_array)) {
			foreach ($category_ids_array as $key => $value) {
				if (!empty($value)) {
					$data['parent_id']     = $value;
					$data['parent_name'] = $this->common_model->getNameById('categories', 'name', $value);
				}
			}
		}

		$data['tree_id'] = 0;
		$data['level'] = 1;
		$data['parent_tree'] = '';
		if (!empty($data['parent_id'])) {
			$parent_category = $this->category_model->get_category_by_id($data['parent_id']);
			if (!empty($parent_category)) {
				$data['tree_id'] = $parent_category->tree_id;
				$data['level'] = $parent_category->level + 1;
				if (!empty($parent_category->parent_tree)) {
					$data['parent_tree'] = $parent_category->parent_tree . ',' . $parent_category->id;
				} else {
					$data['parent_tree'] = $parent_category->id;
				}
			}
		}

		$this->load->model('upload_model');
		$temp_path = $this->upload_model->upload_temp_image('image');
		if (!empty($temp_path)) {
			$year      = date("Y");
			$month     = date("m");
			$day       = date("d");
			$directory = "uploads/category_image/" . "$year/$month/$day/";

			//If the directory doesn't already exists.
			if (!is_dir($directory)) {
				mkdir($directory, 0755, true);
			}
			$data["image"] = $this->upload_model->img_upload($temp_path, $directory);
			$this->upload_model->delete_temp_image($temp_path);
		}

		$name = $this->input->post('name');

		$data['name']          = $name;
		$data['slug']          = $this->common_model->create_unique_slug('categories', 'name', $name);
		$data['status']        = html_escape($this->input->post('status'));
		$data['created_at']    = date("Y-m-d H:i:s");

		$this->db->insert('categories', $data);
		$user_id = $this->db->insert_id();
		$this->session->set_flashdata('flash_message', get_phrase('category_added_successfully'));

		return simple_json_output($resultpost);
	}

	public function edit_category($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('category_updated_successfully'),
			"url" => base_url() . 'inventory/category/edit/' . $id,
		);

		$category = $this->category_model->get_category_by_id($id);

		$data["parent_id"] = 0;
		$data["parent_name"] = "";
		$category_ids_array = $this->input->post('parent_id', true);
		if (!empty($category_ids_array)) {
			foreach ($category_ids_array as $key => $value) {
				if (!empty($value)) {
					$data['parent_id']     = $value;
					$data['parent_name'] = $this->common_model->getNameById('categories', 'name', $value);
				}
			}
		}

		$data['tree_id'] = 0;
		$data['level'] = $category->level;
		if (!empty($data['parent_id'])) {
			$parent_category = $this->category_model->get_category_by_id($data['parent_id']);
			if (!empty($parent_category)) {
				$data['tree_id'] = $parent_category->tree_id;
				$data['level'] = $parent_category->level + 1;
			}
		}

		$this->load->model('upload_model');
		$temp_path = $this->upload_model->upload_temp_image('image');
		if (!empty($temp_path)) {
			$year      = date("Y");
			$month     = date("m");
			$day       = date("d");
			//The folder path for our file should be YYYY/MM/DD
			$directory = "uploads/category_image/" . "$year/$month/$day/";

			//If the directory doesn't already exists.
			if (!is_dir($directory)) {
				mkdir($directory, 0755, true);
			}
			$data["image"] = $this->upload_model->img_upload($temp_path, $directory);
			$this->upload_model->delete_temp_image($temp_path);
			delete_file_from_server($category->image);
		}

		$name = $this->input->post('name');
		if ($name != '') {
			$check_name = $this->common_model->check_common_duplication('on_update', 'categories', 'name', $name, $id);
		} else {
			$check_name = true;
		}

		if ($check_name == false) {
			$this->session->set_flashdata('error_message', get_phrase('name_duplication'));
			$resultpost = array(
				"status" => 400,
				"message" => 'Name Duplication'
			);
		} else {
			$data['name']          = $name;
			$data['slug']          = $this->common_model->create_unique_slug('categories', 'name', $name, $id);
			$data['status']        = html_escape($this->input->post('status'));

			$old_parent_id = $category->parent_id;
			$old_tree_id = $category->tree_id;
			$new_parent_id = $data['parent_id'];

			if (empty($data['tree_id'])) {
				$data['tree_id'] = $category->id;
			}

			$this->db->where('id', $id);
			if ($this->db->update('categories', $data)) {

				//update category tree
				if ($old_parent_id != $new_parent_id) {
					$this->update_categories_parent_tree($old_tree_id);
					if ($old_tree_id != $data['tree_id']) {
						$this->update_categories_parent_tree($data['tree_id']);
					}
				}
			}

			$this->session->set_flashdata('flash_message', get_phrase('category_updated_successfully'));
		}
		return simple_json_output($resultpost);
	}

	public function update_categories_parent_tree($tree_id = null)
	{
		if (!empty($tree_id)) {
			$category = $this->db->where('id', $tree_id)->get('categories')->row();
			if (!empty($category)) {
				//update parent
				$this->db->where('id', $category->id)->update('categories', ['tree_id' => $category->id, 'parent_tree' => '', 'level' => 1]);
				//update all subcategories
				$this->update_subcategories_parent_tree($category, $category->id);
			}
		} else {
			$categories = $this->db->where('parent_id', 0)->get('categories')->result();
			if (!empty($categories)) {
				foreach ($categories as $category) {
					//update parent
					$this->db->where('id', $category->id)->update('categories', ['tree_id' => $category->id, 'parent_tree' => '', 'level' => 1]);
					//update all subcategories
					$this->update_subcategories_parent_tree($category, $category->id);
				}
			}
		}
	}

	public function update_subcategories_parent_tree($category, $tree_id)
	{
		if (!empty($category)) {
			$this->db->select("categories.id, categories.parent_id AS parent_category_id, (SELECT parent_tree FROM categories WHERE id = parent_category_id) AS parent_category_tree");
			$categories = $this->db->where('parent_id', $category->id)->get('categories')->result();


			if (!empty($categories)) {
				foreach ($categories as $item) {
					$parent_tree = '';
					if ($item->parent_category_id != 0) {
						if (empty($item->parent_category_tree)) {
							$parent_tree = $item->parent_category_id;
						} else {
							$parent_tree = $item->parent_category_tree . "," . $item->parent_category_id;
						}
					}
					$level = 1;
					if (!empty($parent_tree)) {
						$array = explode(',', $parent_tree);
						$level = item_count($array) + 1;
					}
					$this->db->where('id', $item->id)->update('categories', ['tree_id' => $tree_id, 'parent_tree' => $parent_tree, 'level' => $level]);

					$this->update_subcategories_parent_tree($item, $tree_id);
				}
			}
		}
	}

	public function delete_category($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('categories');
		return true;
	}

	// Category Ends

	public function inventory_cron()
	{
		$no_data = $this->db->query("SELECT id, product_id, sku_code, name FROM product_variation");
		// $no_data = $this->db->query("SELECT pvar.id, pvar.product_id, pvar.sku_code, pvar.name FROM product_variation AS pvar LEFT JOIN inventory as inv ON pvar.sku_code=inv.item_code WHERE inv.item_code IS NULL");
		// $no_data = $this->db->query("SELECT pvar.id, pvar.product_id, pvar.sku_code, pvar.name, rp.name as pname FROM product_variation AS pvar INNER JOIN raw_products AS rp ON pvar.product_id = rp.id LEFT JOIN inventory as inv ON pvar.product_id=inv.product_id WHERE inv.item_code IS NULL AND rp.is_deleted='0'");

		if ($no_data->num_rows() > 0) {
			foreach ($no_data->result_array() as $arr) {
				$id = $arr['product_id'];

				$prod = $this->db->select('name')->where('id', $id)->where('is_deleted', '0')->get('raw_products');
				$name = '-';
				if ($prod->num_rows() > 0) {
					$pro = $prod->row_array();
					$name = $pro['name'];

					$data = array(
						'product_id' => $id,
						'product_name' => $name,
						'item_code' => $arr['sku_code'],
						'quantity' => 0,
						'warehouse_id' => 1,
						'warehouse_name' => 'MALAD - WAREHOUSE',
					);

					$sku_code = rtrim(ltrim($arr['sku_code']));

					$inventory = $this->db->query("SELECT id, item_code, quantity FROM inventory WHERE TRIM(item_code) = '$sku_code'");
					if ($inventory->num_rows() > 0) {
					} else {
						$this->db->insert('inventory', $data);
						$last_id = $this->db->insert_id();

						$data = [
							"parent_id" => $last_id,
							"warehouse_id" => 1,
							"warehouse_name" => "MALAD - WAREHOUSE",
							"product_id" => $id,
							"product_name" => $name,
							"item_code" => $sku_code,
							"order_id" => 0,
							"status" => 'in',
							"quantity" => 0,
							"received_date" => "2025-02-05",
							"received_amount" => 0,
							"added_date" => date("Y-m-d H:i:s"),
							"added_by_id" => 4,
							"added_by_name" => "Flash Point",
						];

						$this->db->insert('inventory_history', $data);
					}
				}
			}
		}

		echo json_encode($no_data->result_array());
	}

	public function inventory_manual_update()
	{
		// inventory_helper
		$this->load->helper('inventory_helper');
		$datas = getInventory();

		$not_found = [];

		foreach ($datas as $prod) {
			$code = ltrim(rtrim($prod['MODEL']));
			$row = $this->db->query("SELECT id,product_id FROM product_variation WHERE TRIM(sku_code) = '$code' LIMIT 1");

			if ($row->num_rows() == 0) {
				$not_found[] = [
					"MODEL" => $code,
					"QTY" => $prod['QTY'],
				];
			} else {
				$row = $row->row_array();
				$product_id = $row['product_id'];
				$product = $this->db->query("SELECT id, name FROM raw_products WHERE id = '$product_id'")->row_array();

				$last_id = 0;

				$inventory = $this->db->query("SELECT id, item_code, quantity FROM inventory WHERE TRIM(item_code) = '$code'");
				if ($inventory->num_rows() > 0) {
					$inventory = $inventory->row_array();
					$new_qty = intval($inventory['quantity']) + intval($prod['QTY']);
					$update = ['quantity' => $new_qty];
					$this->db->where('id', $inventory['id'])->update('inventory', $update);
					$last_id = $inventory['id'];
				} else {
					$data = [
						"warehouse_id" => 1,
						"warehouse_name" => "MALAD - WAREHOUSE",
						"product_id" => $product_id,
						"product_name" => $product['name'],
						"item_code" => $code,
						"quantity" => $prod['QTY'],
					];

					$inv = $this->db->insert('inventory', $data);
					$last_id = $this->db->insert_id();
				}

				$data = [
					"parent_id" => $last_id,
					"warehouse_id" => 1,
					"warehouse_name" => "MALAD - WAREHOUSE",
					"product_id" => $product_id,
					"product_name" => $product['name'],
					"item_code" => $code,
					"order_id" => 0,
					"status" => 'in',
					"quantity" => $prod['QTY'],
					"received_date" => "2025-02-05",
					"received_amount" => 0,
					"added_date" => date("Y-m-d H:i:s"),
					"added_by_id" => 4,
					"added_by_name" => "Flash Point",
				];

				$this->db->insert('inventory_history', $data);
			}
		}

		echo json_encode($not_found);
	}

	public function inventory_date_update()
	{
		$inv_data = $this->db->query("SELECT id, parent_id, order_id, received_date, quantity, status FROM inventory_history");

		$result = [];
		foreach ($inv_data->result_array() as $data) {
			if ($data['status'] == 'out') {
				$sales = $this->common_model->getRowById('sales_order', 'date', array('id' => $data['order_id']));
				if ($sales != "" && $sales != NULL) {
					$update = ["received_date" => $sales['date']];
					$this->db->where('id', $data['id'])->update('inventory_history', $update);
					$result[] = $update;
				}
			} elseif ($data['status'] == 'return') {
				$goods = $this->common_model->getRowById('goods_return', 'date', array('id' => $data['order_id']));
				if ($goods != "" && $goods != NULL) {
					$update = ["received_date" => $goods['date']];
					$this->db->where('id', $data['id'])->update('inventory_history', $update);
					$result[] = $update;
				}
			} elseif ($data['status'] == 'purchase_out') {
				$purchase = $this->common_model->getRowById('purchase_return', 'date', array('id' => $data['order_id']));
				if ($purchase != "" && $purchase != NULL) {
					$update = ["received_date" => $purchase['date']];
					$this->db->where('id', $data['id'])->update('inventory_history', $update);
					$result[] = $update;
				}
			} elseif ($data['status'] == 'damage_out') {
				$damage = $this->common_model->getRowById('damage_stock', 'date', array('id' => $data['order_id']));
				if ($damage != "" && $damage != NULL) {
					$update = ["received_date" => $damage['date']];
					$this->db->where('id', $data['id'])->update('inventory_history', $update);
					$result[] = $update;
				}
			}
		}
	}


	public function count_top_selling() {}


	public function get_sales_reports()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
			$keyword = $filter_data['keywords'];
			$keyword_filter = " AND (so.company_name LIKE '%" . $keyword . "%' OR 
                                so.customer_name LIKE '%" . $keyword . "%' OR 
                                sop.item_code LIKE '%" . $keyword . "%' OR 
                                sop.size_name LIKE '%" . $keyword . "%' OR 
                                sop.product_order_id LIKE '%" . $keyword . "%')";
		}

        $limit = " LIMIT $start, $length";
		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
			}
			$limit = '';
		}

		$total_count = $this->db->query("
        SELECT COUNT(DISTINCT so.id) as total 
        FROM sales_order so
        LEFT JOIN sales_order_product sop ON so.id = sop.order_id
        WHERE (so.is_deleted='0') $keyword_filter
    ")->row()->total;

		$query = $this->db->query("
        SELECT 
            so.id,
            so.company_name,
            sop.customer_name,
            so.date,
            sop.state,
            sop.pincode,
            sop.item_code,
            sop.size_name,
            sop.total_amount as sp,
            sop.qty,
            sop.product_order_id as poid,
            (sop.total_amount * sop.qty) as total_amount
        FROM sales_order so
        LEFT JOIN sales_order_product sop ON so.id = sop.order_id
        WHERE (so.is_deleted='0') $keyword_filter
        GROUP BY so.id
        ORDER BY so.date DESC
        $limit
    ");
    
    // echo $this->db->last_query(); exit();

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$data[] = array(
					"sr_no" => ++$start,
					"id" => $item['id'],
					"company_name" => $item['company_name'],
					"customer_name" => $item['customer_name'],
					"sku_size" => $item['item_code'] . ' ' . $item['size_name'],
					"sp" => $item['sp'],
					"qty" => $item['qty'],
					"pincode" => $item['pincode'],
					"state" => $item['state'],
					"product_order_id" => $item['poid'],
					"total_amount" => $item['total_amount'],
					"date" => date('d M, Y', strtotime($item['date'])),
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

public function get_sales_return_reports()
{
    $params['draw'] = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];

    $filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
    $date_range = isset($_REQUEST['date_range']) ? $_REQUEST['date_range'] : '';
    $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '';
    $order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
    
    $data = array();
    $keyword_filter = "";
    $is_date_filtered = false;

    if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
        $keyword = $filter_data['keywords'];
        $keyword_filter = " AND (gr.company_name LIKE '%" . $keyword . "%' OR 
                            gr.customer_name LIKE '%" . $keyword . "%' OR 
                            grp.item_code LIKE '%" . $keyword . "%' OR 
                            grp.size_name LIKE '%" . $keyword . "%' OR 
                            grp.product_order_id LIKE '%" . $keyword . "%')";
    }

    if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
        $is_date_filtered = true;
        $added_date = explode(' - ', $_REQUEST['date_range']);
        $from =  date('Y-m-d', strtotime($added_date[0]));
        $to =  date('Y-m-d', strtotime($added_date[1]));
        if ($from == $to) {
            $keyword_filter .= " AND (DATE(date) = '$from')";
        } else {
            $keyword_filter .= " AND (DATE(date) BETWEEN '$from' AND '$to')";
        }
    }

    if (!empty($company_id)) {
        $keyword_filter .= " AND gr.company_id = '" . $company_id . "'";
    }

    if (!empty($order_id)) {
        $keyword_filter .= " AND grp.product_order_id LIKE '%" . $order_id . "%'";
    }

    $total_count = $this->db->query("
        SELECT COUNT(DISTINCT gr.id) as total 
        FROM goods_return gr
        LEFT JOIN goods_return_product grp ON gr.id = grp.parent_id
        WHERE (gr.is_deleted='0') $keyword_filter
    ")->row()->total;

    // If date filter is applied, remove pagination limit
    $limit_clause = "";
    if (!$is_date_filtered) {
        $limit_clause = "LIMIT $start, $length";
    }

    $query = $this->db->query("
        SELECT 
            gr.id,
            gr.company_name,
            gr.customer_name,
            gr.date,
            grp.item_code,
            grp.size_name,
            grp.quantity,
            grp.product_order_id as poid,
            sop.total_amount as amount
        FROM goods_return gr
        LEFT JOIN goods_return_product grp ON gr.id = grp.parent_id
        LEFT JOIN sales_order_product sop ON grp.sop_id = sop.id
        WHERE (gr.is_deleted='0') $keyword_filter
        ORDER BY gr.date DESC
        $limit_clause
    ");

    if (!empty($query)) {
        $sr_no = $start;
        foreach ($query->result_array() as $item) {
            $data[] = array(
                "sr_no" => ++$sr_no,
                "id" => $item['id'],
                "company_name" => $item['customer_name'],
                "sku_size" => $item['item_code'] . ' ' . $item['size_name'],
                "qty" => $item['quantity'],
                "amount" => $item['amount'],
                "product_order_id" => $item['poid'],
                "date" => date('d M, Y', strtotime($item['date'])),
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
    
    public function get_stock_reports()
    {
        $params['draw'] = $_REQUEST['draw'];
        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
    
        $filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
        $data = array();
        $keyword_filter = "";
         
        $total_count = $this->db->query("
            SELECT id
            FROM inventory
            WHERE (id<>'') $keyword_filter GROUP BY categories ORDER BY categories ASC
        ")->num_rows();
    
        $query = $this->db->query("
            SELECT id, SUM(quantity) as total_qty, categories
            FROM inventory
            WHERE (id<>'') $keyword_filter GROUP BY categories
            ORDER BY categories ASC
            LIMIT $start, $length
        ");
    
        $total_stock_qty = 0;
        $total_cp_price = 0;
        $total_gst_amt = 0;
        $grand_total = 0;
        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $total_qty = $item['total_qty'];
                
                $category = $this->common_model->getRowById('categories', '*', ['id' => $item['categories']]);
                $category_name = $category['name'] ?? '-';
                 
                $product = $this->db->query("SELECT product_id, SUM(quantity) as total_sub_qty FROM inventory WHERE categories='" . $item['categories'] . "' GROUP BY product_id");
                $cp_price = 0;
                $gst_amt = 0;
                $total = 0;
                if($product->num_rows() > 0) {
                    foreach($product->result_array() as $prod) {
                        $details = $this->common_model->getRowById('raw_products', '*', ['id' => $prod['product_id']]);
                        $d_cp_price = $details['costing_price'] ?? 0;
                        $d_gst_per = $details['gst'] ?? 0;
                        $cp_price += $d_cp_price * $prod['total_sub_qty'];
                        $gst_amt += (($d_cp_price * $d_gst_per) / 100) * $prod['total_sub_qty'];
                        $total += ($d_cp_price * $prod['total_sub_qty']) + ((($d_cp_price * $d_gst_per) / 100) * $prod['total_sub_qty']);
                    }
                }
                
                $total_stock_qty += $total_qty;
                $total_cp_price += $cp_price;
                $total_gst_amt += $gst_amt;
                $grand_total += $total;
                
                $data[] = array(
                    "sr_no" => ++$start,
                    "id" => $item['id'],
                    "pcs" => $category_name,
                    "qty" => $total_qty,
                    "amt" => number_format($cp_price, 2),
                    "gst" => number_format($gst_amt, 2),
                    "total" => number_format($total, 2),
                );
            }
        }
        
        if(count($data) > 0) {
            $data[] = array(
                "sr_no" => '-',
                "id" => 0,
                "pcs" => "Total",
                "qty" => $total_stock_qty,
                "amt" => number_format($total_cp_price, 2),
                "gst" => number_format($total_gst_amt, 2),
                "total" => number_format($grand_total, 2),
            );
        }
    
        $json_data = array(
            "draw" => intval($params['draw']),
            "recordsTotal" => $total_count,
            "recordsFiltered" => $total_count,
            "data" => $data
        );
        echo json_encode($json_data);
    }

	public function add_prod()
	{
		$arr = product_arr();

		foreach($arr as $item) {
			$category = $this->common_model->getRowById('categories', '*', ['name' => $item['category']]);

			$type = '';
			if($category != '') {
				if($category['parent_id'] == '2') {
					$type = 'ready';
				} elseif($category['parent_id'] == '3') {
					$type = 'spare';
				} else {
					$type = '';
				}
			}

			$row = [
				'name' => $item['name'],
				'categories' => ($category['id'] ?? ''),
				'item_code' => $item['item_code'],
				'type' => $type,
				'alias' => $item['alias'],
				'supplier_id' => 10,
				'supplier_name' => 'GUANGZHOU WEI GE MACHINERY EQUIPMENT CO., LIMITED',
				'cartoon_qty' => ($item['cartoon_qty'] ?? 0),
				'net_weight' => ($item['net_weight'] ?? 0),
				'gross_weight' => ($item['gross_weight'] ?? 0),
				'length' => ($item['length'] ?? 0),
				'width' => ($item['width'] ?? 0),
				'height' => ($item['height'] ?? 0),
				'cbm' => ($item['cbm'] ?? 0),
				'usd_rate' => ($item['usd_rate'] ?? 0),
				'hsn_code' => $item['hsn_code'],
				'unit' => '',
			];

			$this->db->insert('raw_products', $row);
			$id = $this->db->insert_id();
			
			$variation = [
				'product_id' => $id,
				'name' => $item['name'],
				'sku_code' => $item['item_code'],
				'cartoon_qty' => ($item['cartoon_qty'] ?? 0),
				'net_weight' => ($item['net_weight'] ?? 0),
				'gross_weight' => ($item['gross_weight'] ?? 0),
				'length' => ($item['length'] ?? 0),
				'width' => ($item['width'] ?? 0),
				'height' => ($item['height'] ?? 0),
				'cbm' => ($item['cbm'] ?? 0),
			];

			$this->db->insert('product_variation', $variation);
		}
	}


	public function create_po_export_zip($id) {
		$export_data = [];
		// Unique invoice no
		$total_invoice = $this->db->query("SELECT invoice_no FROM po_products WHERE parent_id='$id' AND invoice_no IS NOT NULL GROUP BY invoice_no");
		if($total_invoice->num_rows() > 0) {
				$invoice_numbers = array_column($total_invoice->result_array(), 'invoice_no');
				foreach($invoice_numbers as $invoice) {
						// Fetching All product with invoice no
						$product_data = $this->db->query("SELECT * FROM po_products WHERE parent_id='$id' AND invoice_no='$invoice'");
						if($product_data->num_rows() > 0) {
								$single_row_prod = $product_data->row_array();
								// Fetching Supplier Info
								$supplier_info = $this->common_model->getRowByIdArr('supplier', '*', ['id' => $single_row_prod['invoice_supplier_id']]);
							  // Company Info
								$company_info = $this->common_model->getRowByIdArr('company', '*', ['id' => $this->session->userdata('company_id')]);

								if($company_info == '') {
									$company_info = [];
								} 

								$company_info['invoice'] = $single_row_prod['invoice'];
								$company_info['invoice_date'] = $single_row_prod['invoice_date'];
								$company_info['invoice_terms'] = $single_row_prod['invoice_terms'];
								
								$supplier_info['company_info'] = $company_info;

								if($supplier_info != '') {
										// Populate data under supplier
										$multi_row_prod = $product_data->result_array();
										foreach($multi_row_prod as $prod) {
												$single_prod = $prod;
												// Fetching Totals
												$totals_array = $this->common_model->getResultById('loading_product_total', '*', ['parent_id' => $prod['id']]);
												if($totals_array != '') {
														$single_prod['totals'] = $totals_array;
												}
												
												$supplier_info['products'][] = $single_prod;
										}
										
										$export_data[] = $supplier_info;
								}
						}
				}
		} else {
			$this->session->set_flashdata('error_message', 'No Invoice Number found');
			redirect(site_url('inventory/loading_list_po'));
			return;
		}

		if (!empty($export_data)) {
			$path_info = [];
			$path = FCPATH . 'uploads/invoices/';
			if (!is_dir($path)) {
					mkdir($path, 0777, true);
			}

			$this->load->library('pdf');
			foreach ($export_data as $item) {
				$receipt_no = sprintf('%02d', $item['id']) . rand(100, 999);

				/* ================= PACKING LIST ================= */
				ob_clean();
				$page_data['data'] = $item;
				$html = $this->load->view('invoice/po/packing_list', $page_data, true);
				$pdf = $this->pdf->create();
				$pdf->setPaper('A4', 'portrait');
				$pdf->loadHtml($html);
				$pdf->render();
				$pdfname = 'PL_' . $receipt_no . '.pdf';
				file_put_contents($path . $pdfname, $pdf->output());
				$path_info[] = 'uploads/invoices/' . $pdfname;
				unset($pdf);

				/* ================= COMMERCIAL INVOICE 1 ================= */
				ob_clean();
				$item['invoice_type'] = '1';
				$page_data['data'] = $item;
				$html = $this->load->view('invoice/po/commercial', $page_data, true);
				$pdf = $this->pdf->create();
				$pdf->setPaper('A4', 'portrait');
				$pdf->loadHtml($html);
				$pdf->render();
				$pdfname = 'CI_1_' . $receipt_no . '.pdf';
				file_put_contents($path . $pdfname, $pdf->output());
				$path_info[] = 'uploads/invoices/' . $pdfname;
				unset($pdf);

				/* ================= COMMERCIAL INVOICE 2 ================= */
				ob_clean();
				$item['invoice_type'] = '2';
				$page_data['data'] = $item;
				$html = $this->load->view('invoice/po/commercial', $page_data, true);
				$pdf = $this->pdf->create();
				$pdf->setPaper('A4', 'portrait');
				$pdf->loadHtml($html);
				$pdf->render();
				$pdfname = 'CI_2_' . $receipt_no . '.pdf';
				file_put_contents($path . $pdfname, $pdf->output());
				$path_info[] = 'uploads/invoices/' . $pdfname;
				unset($pdf);
			}

			// exit();
			/* ================= CREATE ZIP ================= */
			$this->load->library('zip');
			$zip_name = 'invoices_' . date('Ymd_His') . '_' . rand(1000,9999) . '.zip';
			$zip_path = FCPATH . 'uploads/invoices/' . $zip_name;

			foreach ($path_info as $file) {
				if (file_exists($file)) {
					$this->zip->read_file($file, false);
				}
			}

			$this->zip->archive($zip_path);
			$this->zip->clear_data();

			/* ================= FORCE DOWNLOAD ================= */
			if (file_exists($zip_path)) {
					// Set headers for download
					header('Content-Type: application/zip');
					header('Content-Disposition: attachment; filename="' . $zip_name . '"');
					header('Content-Length: ' . filesize($zip_path));
					header('Pragma: no-cache');
					header('Expires: 0');
					
					// Output the file
					readfile($zip_path);
					
					// Delete the zip file after download
					unlink($zip_path);
					
					// Delete individual PDFs
					foreach ($path_info as $file) {
						if (file_exists($file)) {
							unlink($file);
						}
					}
					
					exit(); // Stop further execution
			}
		} else {
			$this->session->set_flashdata('error_message', 'No data found');
			redirect(site_url('inventory/loading_list_po'));
			return;
		}
	}

}
