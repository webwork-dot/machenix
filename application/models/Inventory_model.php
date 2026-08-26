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
				$state_name = $this->common_model->get_state_name($state_id);
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->get_city_name($city_id);
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
				$state_name = $this->common_model->get_state_name($state_id);
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->get_city_name($city_id);
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
			$country_id = $this->input->post('country_id');
			if ($country_id != '') {
				$country_name = $this->common_model->selectByidParam($country_id, 'countries', 'name');
			} else {
				$country_name = '';
			}
			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->get_state_name($state_id);
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->get_city_name($city_id);
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
			$data['t_code']       = clean_and_escape($this->input->post('t_code'));
			$data['email']        = clean_and_escape($this->input->post('email'));
			$data['tel_no']       = clean_and_escape($this->input->post('tel_no'));
			$data['c_code']       = clean_and_escape($this->input->post('c_code'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$data['beneficiary']       = clean_and_escape($this->input->post('beneficiary'));
			$data['account_no']       = clean_and_escape($this->input->post('account_no'));
			$data['advising_bank']       = clean_and_escape($this->input->post('advising_bank'));
			$data['bank_address']       = clean_and_escape($this->input->post('bank_address'));
			$data['swift_code']       = clean_and_escape($this->input->post('swift_code'));
			$data['outstanding_rmb']   = clean_and_escape($this->input->post('outstanding_rmb'));
			$data['outstanding_inr']   = clean_and_escape($this->input->post('outstanding_inr'));
			$data['outstanding_usd']   = clean_and_escape($this->input->post('outstanding_usd'));
			$user_id                = $this->session->userdata('super_user_id');
			$user_name              = $this->session->userdata('super_name');
			$data['country_id']    = $country_id;
			$data['country_name']    = $country_name;
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

			$data['type']   = 'import';
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

			$country_id = $this->input->post('country_id');
			if ($country_id != '') {
				$country_name = $this->common_model->selectByidParam($country_id, 'countries', 'name');
			} else {
				$country_name = '';
			}
			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
				$state_name = $this->common_model->get_state_name($state_id);
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->get_city_name($city_id);
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
			$data['c_code']       = clean_and_escape($this->input->post('c_code'));
			$data['email']        = clean_and_escape($this->input->post('email'));
			$data['tel_no']       = clean_and_escape($this->input->post('tel_no'));
			$data['t_code']       = clean_and_escape($this->input->post('t_code'));
			$data['gst_no']       = clean_and_escape($this->input->post('gst_no'));
			$data['gst_name']       = clean_and_escape($this->input->post('gst_name'));
			$data['state_code']       = clean_and_escape($this->input->post('state_code'));
			$data['beneficiary']       = clean_and_escape($this->input->post('beneficiary'));
			$data['account_no']       = clean_and_escape($this->input->post('account_no'));
			$data['advising_bank']       = clean_and_escape($this->input->post('advising_bank'));
			$data['bank_address']       = clean_and_escape($this->input->post('bank_address'));
			$data['swift_code']       = clean_and_escape($this->input->post('swift_code'));
			$data['outstanding_rmb']   = clean_and_escape($this->input->post('outstanding_rmb'));
			$data['outstanding_inr']   = clean_and_escape($this->input->post('outstanding_inr'));
			$data['outstanding_usd']   = clean_and_escape($this->input->post('outstanding_usd'));
			$data['company_id']    = $this->session->userdata('company_id');
			$data['country_id']    = $country_id;
			$data['country_name']    = $country_name;
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

			$data['type'] = 'import';
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
		$data['outstanding_rmb'] = $original_supplier['outstanding_rmb'];
		$data['outstanding_inr'] = $original_supplier['outstanding_inr'];
		$data['outstanding_usd'] = $original_supplier['outstanding_usd'];
		
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

		$total_count = $this->db->query("SELECT id FROM supplier WHERE (is_deleted='0' AND type='import') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name,gst_no,contact_name,contact_no FROM supplier WHERE (is_deleted='0' AND type='import') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/supplier/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/supplier/edit/' . $id;
				$replicate_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/supplier_replicate_modal/" . $id . "','Replicate Supplier')";
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="' . base_url() . 'inventory/supplier/ledger/' . $id . '" data-toggle="tooltip" data-bs-placement="top" title="Ledger"><button type="button" class="btn mr-1 mb-1 btn-outline-primary" style="padding: 4px 8px;"><i class="fa fa-book" aria-hidden="true"></i></button></a>
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

	public function add_product_unit()
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Product Unit added successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$name       = clean_and_escape($this->input->post('name'));

			$check = $this->db->query(
					"SELECT id FROM product_unit 
					WHERE name = ? AND is_delete = '0' 
					LIMIT 1",
					array($name)
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
					'name'       => $name,
					'added_by'   => (int) $this->session->userdata('super_user_id'),
					'created_at' => date("Y-m-d H:i:s"),
			);

			$this->db->insert('product_unit', $data);
			$this->session->set_flashdata('flash_message', 'Product Unit added successfully');

			return simple_json_output($resultpost);
	}

	public function edit_product_unit($id = "")
	{
			$resultpost = array(
					"status"  => 200,
					"message" => "Product Unit updated successfully",
					"url"     => $this->session->userdata('previous_url'),
			);

			$name       = clean_and_escape($this->input->post('name'));
			$id         = (int) $id;

			$check = $this->db->query(
					"SELECT id FROM product_unit
					WHERE name = ? AND is_delete = '0' AND id != ?
					LIMIT 1",
					array($name, $id)
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
			$this->db->update('product_unit', $data);

			$this->session->set_flashdata('flash_message', 'Product Unit updated successfully');
			return simple_json_output($resultpost);
	}

	public function delete_product_unit($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => "Product Unit deleted successfully",
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_delete'] = '1';
		$this->db->where('id', $id);
		$this->db->update('product_unit', $data);

		return simple_json_output($resultpost);
	}

	public function get_product_unit_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('product_unit');
	}

	public function get_product_unit()
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

		$total_count = $this->db->query("SELECT id FROM product_unit WHERE is_delete='0' $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name FROM product_unit WHERE is_delete='0' $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/product_unit/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/product-unit/edit/' . $id;
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

	public function add_other_charges()
	{
		$resultpost = array(
			"status"  => 200,
			"message" => "Charge added successfully",
			"url"     => $this->session->userdata('previous_url'),
		);

		$name   = clean_and_escape($this->input->post('name'));
		$gst    = clean_and_escape($this->input->post('gst'));
		$price  = 0;

		if ($name == '' || $gst == '') {
			$this->session->set_flashdata('error_message', 'All fields are required');
			$resultpost = array(
				"status"  => 400,
				"message" => "All fields are required",
			);
			return simple_json_output($resultpost);
		}

		$check = $this->db->query(
			"SELECT id FROM other_charges 
			WHERE name = ? AND is_delete = '0' 
			LIMIT 1",
			array($name)
		);

		if ($check->num_rows() > 0) {
			$this->session->set_flashdata('error_message', 'Duplicate name not allowed');
			$resultpost = array(
				"status"  => 400,
				"message" => "Duplicate name not allowed",
			);
			return simple_json_output($resultpost);
		}

		$data = array(
			'name'       => $name,
			'gst'        => (float) $gst,
			'price'      => (float) $price,
			'added_by'   => (int) $this->session->userdata('super_user_id'),
			'created_at' => date("Y-m-d H:i:s"),
		);

		$this->db->insert('other_charges', $data);
		$this->session->set_flashdata('flash_message', 'Charge added successfully');

		return simple_json_output($resultpost);
	}

	public function edit_other_charges($id = "")
	{
		$resultpost = array(
			"status"  => 200,
			"message" => "Charge updated successfully",
			"url"     => $this->session->userdata('previous_url'),
		);

		$name   = clean_and_escape($this->input->post('name'));
		$gst    = clean_and_escape($this->input->post('gst'));
		$price  = 0;
		$id     = (int) $id;

		if ($name == '' || $gst == '') {
			$this->session->set_flashdata('error_message', 'All fields are required');
			$resultpost = array(
				"status"  => 400,
				"message" => "All fields are required",
			);
			return simple_json_output($resultpost);
		}

		$check = $this->db->query(
			"SELECT id FROM other_charges 
			WHERE name = ? AND is_delete = '0' AND id != ?
			LIMIT 1",
			array($name, $id)
		);

		if ($check->num_rows() > 0) {
			$this->session->set_flashdata('error_message', 'Duplicate name not allowed');
			$resultpost = array(
				"status"  => 400,
				"message" => "Duplicate name not allowed",
			);
			return simple_json_output($resultpost);
		}

		$data = array(
			'name'  => $name,
			'gst'   => (float) $gst,
			'price' => (float) $price,
		);

		$this->db->where('id', $id);
		$this->db->update('other_charges', $data);

		$this->session->set_flashdata('flash_message', 'Charge updated successfully');
		return simple_json_output($resultpost);
	}

	public function delete_other_charges($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => "Charge deleted successfully",
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_delete'] = '1';
		$this->db->where('id', $id);
		$this->db->update('other_charges', $data);

		return simple_json_output($resultpost);
	}

	public function get_other_charges_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('other_charges');
	}

	public function get_other_charges()
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

		$total_count = $this->db->query("SELECT id FROM other_charges WHERE (is_delete='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name, gst, price FROM other_charges WHERE (is_delete='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/charges/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/charges/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				';

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
					"gst"         => $item['gst'],
					"price"       => $item['price'],
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

	public function add_commission_slab()
	{
		$resultpost = array(
			"status"  => 200,
			"message" => "Commission slab added successfully",
			"url"     => $this->session->userdata('previous_url'),
		);

		$commission  = clean_and_escape($this->input->post('commission'));
		$name        = (float)$commission . '%';

		if ($commission == '') {
			$this->session->set_flashdata('error_message', 'All fields are required');
			$resultpost = array(
				"status"  => 400,
				"message" => "All fields are required",
			);
			return simple_json_output($resultpost);
		}

		$check = $this->db->query(
			"SELECT id FROM product_commission_slab 
			WHERE name = ? AND is_deleted = '0' 
			LIMIT 1",
			array($name)
		);

		if ($check->num_rows() > 0) {
			$this->session->set_flashdata('error_message', 'Duplicate name not allowed');
			$resultpost = array(
				"status"  => 400,
				"message" => "Duplicate name not allowed",
			);
			return simple_json_output($resultpost);
		}

		$data = array(
			'name'       => $name,
			'commission' => (float) $commission,
			'created_at' => date("Y-m-d H:i:s"),
		);

		$this->db->insert('product_commission_slab', $data);
		$this->session->set_flashdata('flash_message', 'Commission slab added successfully');

		return simple_json_output($resultpost);
	}

	public function edit_commission_slab($id = "")
	{
		$resultpost = array(
			"status"  => 200,
			"message" => "Commission slab updated successfully",
			"url"     => $this->session->userdata('previous_url'),
		);

		$commission  = clean_and_escape($this->input->post('commission'));
		$name        = (float)$commission . '%';
		$id          = (int) $id;

		if ($commission == '') {
			$this->session->set_flashdata('error_message', 'All fields are required');
			$resultpost = array(
				"status"  => 400,
				"message" => "All fields are required",
			);
			return simple_json_output($resultpost);
		}

		$check = $this->db->query(
			"SELECT id FROM product_commission_slab 
			WHERE name = ? AND is_deleted = '0' AND id != ?
			LIMIT 1",
			array($name, $id)
		);

		if ($check->num_rows() > 0) {
			$this->session->set_flashdata('error_message', 'Duplicate name not allowed');
			$resultpost = array(
				"status"  => 400,
				"message" => "Duplicate name not allowed",
			);
			return simple_json_output($resultpost);
		}

		$data = array(
			'name'       => $name,
			'commission' => (float) $commission,
		);

		$this->db->where('id', $id);
		$this->db->update('product_commission_slab', $data);

		$this->session->set_flashdata('flash_message', 'Commission slab updated successfully');
		return simple_json_output($resultpost);
	}

	public function delete_commission_slab($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => "Commission slab deleted successfully",
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('product_commission_slab', $data);

		return simple_json_output($resultpost);
	}

	public function get_commission_slab_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('product_commission_slab');
	}

	public function get_commission_slab()
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

		$total_count = $this->db->query("SELECT id FROM product_commission_slab WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name, commission FROM product_commission_slab WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/commission-slab/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/commission-slab/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				';

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
					"commission"  => $item['commission'],
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

	public function add_profit_commission_slab()
	{
		$resultpost = array(
			"status"  => 200,
			"message" => "Profit commission slab added successfully",
			"url"     => $this->session->userdata('previous_url'),
		);

		$comm_from = $this->input->post('comm_from');
		$comm_to   = $this->input->post('comm_to');

		if ($comm_from === '' || $comm_to === '') {
			$this->session->set_flashdata('error_message', 'All fields are required');
			$resultpost = array(
				"status"  => 400,
				"message" => "All fields are required",
			);
			return simple_json_output($resultpost);
		}

		$comm_from = (float)$comm_from;
		$comm_to   = (float)$comm_to;

		if ($comm_from > $comm_to) {
			$this->session->set_flashdata('error_message', 'Commission From cannot be greater than Commission To');
			$resultpost = array(
				"status"  => 400,
				"message" => "Commission From cannot be greater than Commission To",
			);
			return simple_json_output($resultpost);
		}

		// Overlapping check
		$check = $this->db->query(
			"SELECT id FROM profit_commission_slab 
			WHERE is_deleted = '0' 
			  AND comm_from <= ? 
			  AND comm_to >= ? 
			LIMIT 1",
			array($comm_to, $comm_from)
		);

		if ($check->num_rows() > 0) {
			$this->session->set_flashdata('error_message', 'Overlapping commission range is not allowed');
			$resultpost = array(
				"status"  => 400,
				"message" => "Overlapping commission range is not allowed",
			);
			return simple_json_output($resultpost);
		}

		$name = $comm_from . ' - ' . $comm_to . '%';
		$data = array(
			'name'       => $name,
			'comm_from'  => $comm_from,
			'comm_to'    => $comm_to,
			'created_at' => date("Y-m-d H:i:s"),
		);

		$this->db->insert('profit_commission_slab', $data);
		$this->session->set_flashdata('flash_message', 'Profit commission slab added successfully');

		return simple_json_output($resultpost);
	}

	public function edit_profit_commission_slab($id = "")
	{
		$resultpost = array(
			"status"  => 200,
			"message" => "Profit commission slab updated successfully",
			"url"     => $this->session->userdata('previous_url'),
		);

		$comm_from = $this->input->post('comm_from');
		$comm_to   = $this->input->post('comm_to');
		$id        = (int)$id;

		if ($comm_from === '' || $comm_to === '') {
			$this->session->set_flashdata('error_message', 'All fields are required');
			$resultpost = array(
				"status"  => 400,
				"message" => "All fields are required",
			);
			return simple_json_output($resultpost);
		}

		$comm_from = (float)$comm_from;
		$comm_to   = (float)$comm_to;

		if ($comm_from > $comm_to) {
			$this->session->set_flashdata('error_message', 'Commission From cannot be greater than Commission To');
			$resultpost = array(
				"status"  => 400,
				"message" => "Commission From cannot be greater than Commission To",
			);
			return simple_json_output($resultpost);
		}

		// Overlapping check excluding current ID
		$check = $this->db->query(
			"SELECT id FROM profit_commission_slab 
			WHERE is_deleted = '0' 
			  AND id != ? 
			  AND comm_from <= ? 
			  AND comm_to >= ? 
			LIMIT 1",
			array($id, $comm_to, $comm_from)
		);

		if ($check->num_rows() > 0) {
			$this->session->set_flashdata('error_message', 'Overlapping commission range is not allowed');
			$resultpost = array(
				"status"  => 400,
				"message" => "Overlapping commission range is not allowed",
			);
			return simple_json_output($resultpost);
		}

		$name = $comm_from . ' - ' . $comm_to . '%';
		$data = array(
			'name'       => $name,
			'comm_from'  => $comm_from,
			'comm_to'    => $comm_to,
		);

		$this->db->where('id', $id);
		$this->db->update('profit_commission_slab', $data);

		$this->session->set_flashdata('flash_message', 'Profit commission slab updated successfully');
		return simple_json_output($resultpost);
	}

	public function delete_profit_commission_slab($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => "Profit commission slab deleted successfully",
			"url" => $this->session->userdata('previous_url'),
		);

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('profit_commission_slab', $data);

		return simple_json_output($resultpost);
	}

	public function get_profit_commission_slab_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('profit_commission_slab');
	}

	public function get_profit_commission_slab()
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

		$total_count = $this->db->query("SELECT id FROM profit_commission_slab WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, name, comm_from, comm_to FROM profit_commission_slab WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delete_url = "confirm_modal('" . base_url() . "inventory/profit_commission_slab/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/profit-commission-slab/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				';

				$data[] = array(
					"sr_no"       => ++$start,
					"id"          => $item['id'],
					"name"        => $item['name'],
					"comm_from"   => $item['comm_from'],
					"comm_to"     => $item['comm_to'],
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

	public function get_complete_product_log_data($product_id)
	{
		$raw_product = $this->db->where('id', $product_id)->get('raw_products')->row_array();
		if (empty($raw_product)) {
			return null;
		}
		$raw_product['package_variations'] = $this->db->where('product_id', $product_id)->get('product_variation')->result_array();
		$raw_product['supplier_pricing'] = $this->db->where('product_id', $product_id)->get('product_variations')->result_array();
		$raw_product['images'] = $this->db->where('product_id', $product_id)->get('product_images')->result_array();
		return $raw_product;
	}

	public function get_complete_purchase_order_log_data($po_id)
	{
		$purchase_order = $this->db->where('id', $po_id)->get('purchase_order')->row_array();
		if (empty($purchase_order)) {
			return null;
		}
		$purchase_order['products'] = $this->db->where('parent_id', $po_id)->get('purchase_order_product')->result_array();
		return $purchase_order;
	}

	public function is_product_low_stock($product_id, $supplier_id)
	{
		if (empty($product_id) || empty($supplier_id)) {
			return 0;
		}

		$low_stock_query = $this->db->query("
			SELECT SUM(i.quantity) as total_qty
			FROM inventory i
			INNER JOIN product_variations pv ON pv.product_id = i.product_id AND pv.supplier_id = i.supplier_id
			WHERE i.product_id = ? AND i.supplier_id = ?
			GROUP BY i.product_id, i.supplier_id, pv.intimation
			HAVING SUM(i.quantity) > 0
			  AND (SUM(i.quantity) - COALESCE((
					SELECT SUM(sop.qty)
					FROM sales_order_product sop
					INNER JOIN sales_order so ON so.id = sop.order_id
					WHERE sop.product_id = ?
					  AND so.type = 'normal'
					  AND so.is_approved = 0
					  AND so.is_deleted = 0
				  ), 0)) <= pv.intimation
			LIMIT 1
		", array($product_id, $supplier_id, $product_id));

		return ($low_stock_query->num_rows() > 0) ? 1 : 0;
	}

	public function get_po_product_qty_info($product_id, $supplier_id, $method = '', $company_id = null)
	{
		$product_id = intval($product_id);
		$supplier_id = intval($supplier_id);
		if ($company_id === null) {
			$company_id = $this->session->userdata('company_id');
		}
		$company_id = intval($company_id);

		$pending_sql = "
			SELECT COALESCE(SUM(pop.quantity), 0) AS total_qty
			FROM purchase_order_product pop
			INNER JOIN purchase_order po ON po.id = pop.parent_id
			WHERE po.delivery_status = 'pending'
			  AND po.is_deleted = 0
			  AND pop.product_id = ?
			  AND pop.supplier_id = ?
		";
		$pending_params = array($product_id, $supplier_id);
		if ($method != '') {
			$pending_sql .= " AND po.method = ?";
			$pending_params[] = $method;
		}
		$pending_po_qty = intval($this->db->query($pending_sql, $pending_params)->row()->total_qty ?? 0);

		$priority_sql = "
			SELECT COALESCE(SUM(pp.quantity), 0) AS total_qty
			FROM po_products pp
			INNER JOIN purchase_order po ON po.id = pp.parent_id
			WHERE po.delivery_status = 'priority'
			  AND po.is_deleted = 0
			  AND pp.is_deleted = 0
			  AND pp.is_priority = 1
			  AND pp.product_id = ?
			  AND pp.supplier_id = ?
		";
		$priority_params = array($product_id, $supplier_id);
		if ($method != '') {
			$priority_sql .= " AND po.method = ?";
			$priority_params[] = $method;
		}
		$priority_qty = intval($this->db->query($priority_sql, $priority_params)->row()->total_qty ?? 0);

		$loading_sql = "
			SELECT COALESCE(SUM(lpp.loading_qty), 0) AS total_qty
			FROM loading_po_product lpp
			INNER JOIN purchase_order po ON po.id = lpp.parent_id
			WHERE po.delivery_status = 'loading'
			  AND po.is_deleted = 0
			  AND lpp.is_deleted = 0
			  AND lpp.product_id = ?
			  AND lpp.supplier_id = ?
		";
		$loading_params = array($product_id, $supplier_id);
		if ($method != '') {
			$loading_sql .= " AND po.method = ?";
			$loading_params[] = $method;
		}
		$loading_list_qty = intval($this->db->query($loading_sql, $loading_params)->row()->total_qty ?? 0);

		$in_stock_qty = intval($this->db->query("
			SELECT COALESCE(SUM(quantity), 0) AS total_qty
			FROM inventory
			WHERE product_id = ?
		", array($product_id))->row()->total_qty ?? 0);

		$company_stock = intval($this->db->query("
			SELECT COALESCE(SUM(quantity), 0) AS total_qty
			FROM inventory
			WHERE product_id = ? AND company_id = ?
		", array($product_id, $company_id))->row()->total_qty ?? 0);

		return array(
			'pending_po_qty' => $pending_po_qty,
			'priority_qty' => $priority_qty,
			'loading_list_qty' => $loading_list_qty,
			'in_stock_qty' => $in_stock_qty,
			'company_stock' => $company_stock,
		);
	}

	public function get_po_product_qty_details($product_id, $supplier_id, $status, $method = '')
	{
		$product_id = intval($product_id);
		$supplier_id = intval($supplier_id);
		if (!in_array($status, array('pending', 'priority', 'loading'), true)) {
			$status = 'pending';
		}

		if ($status === 'loading') {
			$sql = "
				SELECT
					po.id,
					po.voucher_no,
					po.date,
					s.name AS supplier_name,
					SUM(lpp.loading_qty) AS quantity
				FROM loading_po_product lpp
				INNER JOIN purchase_order po ON po.id = lpp.parent_id
				LEFT JOIN supplier s ON s.id = lpp.supplier_id
				WHERE po.delivery_status = 'loading'
				  AND po.is_deleted = 0
				  AND lpp.is_deleted = 0
				  AND lpp.product_id = ?
				  AND lpp.supplier_id = ?
			";
			$params = array($product_id, $supplier_id);
			if ($method != '') {
				$sql .= " AND po.method = ?";
				$params[] = $method;
			}
			$sql .= " GROUP BY po.id, po.voucher_no, po.date, s.name
				HAVING SUM(lpp.loading_qty) > 0
				ORDER BY po.date DESC, po.id DESC";
			return $this->db->query($sql, $params)->result_array();
		}

		if ($status === 'priority') {
			$sql = "
				SELECT
					po.id,
					po.voucher_no,
					po.date,
					s.name AS supplier_name,
					SUM(pp.quantity) AS quantity
				FROM po_products pp
				INNER JOIN purchase_order po ON po.id = pp.parent_id
				LEFT JOIN supplier s ON s.id = pp.supplier_id
				WHERE po.delivery_status = 'priority'
				  AND po.is_deleted = 0
				  AND pp.is_deleted = 0
				  AND pp.is_priority = 1
				  AND pp.product_id = ?
				  AND pp.supplier_id = ?
			";
			$params = array($product_id, $supplier_id);
			if ($method != '') {
				$sql .= " AND po.method = ?";
				$params[] = $method;
			}
			$sql .= " GROUP BY po.id, po.voucher_no, po.date, s.name
				HAVING SUM(pp.quantity) > 0
				ORDER BY po.date DESC, po.id DESC";
			return $this->db->query($sql, $params)->result_array();
		}

		$sql = "
			SELECT
				po.id,
				po.voucher_no,
				po.date,
				s.name AS supplier_name,
				SUM(pop.quantity) AS quantity
			FROM purchase_order_product pop
			INNER JOIN purchase_order po ON po.id = pop.parent_id
			LEFT JOIN supplier s ON s.id = pop.supplier_id
			WHERE po.delivery_status = 'pending'
			  AND po.is_deleted = 0
			  AND pop.product_id = ?
			  AND pop.supplier_id = ?
		";
		$params = array($product_id, $supplier_id);
		if ($method != '') {
			$sql .= " AND po.method = ?";
			$params[] = $method;
		}
		$sql .= " GROUP BY po.id, po.voucher_no, po.date, s.name
			HAVING SUM(pop.quantity) > 0
			ORDER BY po.date DESC, po.id DESC";
		return $this->db->query($sql, $params)->result_array();
	}

	public function get_product_stock_qty_details($product_id, $company_id = null)
	{
		$product_id = intval($product_id);
		$sql = "
			SELECT
				i.batch_no,
				i.quantity,
				i.official_qty,
				i.black_qty,
				i.warehouse_name,
				s.name AS supplier_name,
				c.name AS company_name,
				i.company_id,
				i.supplier_id
			FROM inventory i
			LEFT JOIN supplier s ON s.id = i.supplier_id
			LEFT JOIN company c ON c.id = i.company_id
			WHERE i.product_id = ?
			  AND i.quantity > 0
		";
		$params = array($product_id);
		if ($company_id !== null && $company_id !== '' && $company_id !== 'all') {
			$sql .= " AND i.company_id = ?";
			$params[] = intval($company_id);
		}
		$sql .= " ORDER BY c.name ASC, s.name ASC, i.batch_no ASC, i.id DESC";
		return $this->db->query($sql, $params)->result_array();
	}

	public function get_complete_priority_list_log_data($po_id)
	{
		$purchase_order = $this->db->where('id', $po_id)->get('purchase_order')->row_array();
		if (empty($purchase_order)) {
			return null;
		}
		$purchase_order['products'] = $this->db->where('parent_id', $po_id)->get('po_products')->result_array();
		return $purchase_order;
	}

	public function get_complete_loading_list_log_data($po_id)
	{
		$purchase_order = $this->db->where('id', $po_id)->get('purchase_order')->row_array();
		if (empty($purchase_order)) {
			return null;
		}
		$products = $this->db->where('parent_id', $po_id)->get('loading_po_product')->result_array();
		foreach ($products as &$product) {
			$product['variations'] = $this->db->where('parent_id', $product['id'])->get('loading_product_total')->result_array();
		}
		unset($product);
		$purchase_order['products'] = $products;
		return $purchase_order;
	}

	public function get_complete_purchase_in_log_data($po_id)
	{
		$purchase_order = $this->db->where('id', $po_id)->get('purchase_order')->row_array();
		if (empty($purchase_order)) {
			return null;
		}
		$products = $this->db->where('parent_id', $po_id)->get('purchase_in_product')->result_array();
		foreach ($products as &$product) {
			$product['overflow'] = $this->db->where('parent_id', $product['id'])->get('purchase_overflow_product')->row_array();
		}
		unset($product);
		$purchase_order['products'] = $products;
		return $purchase_order;
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

			// Check for duplicate item_code only for 'ready' goods
			if ($product_type == 'ready') {
				$checkProduct = $this->db->select('id')->where('item_code', $item_code)->get('raw_products');
				if ($checkProduct->num_rows() > 0) {
					$this->session->set_flashdata('error_message', get_phrase('sku_code_duplication'));
					$resultpost = array(
						"status" => 400,
						"message" => 'Duplicate SKU: ' . $item_code
					);
					return simple_json_output($resultpost);
				} else {
					$checkProduct = $this->db->select('id')->where('sku_code', $item_code)->where('sku_code!=', '')->get('product_sku');
					if ($checkProduct->num_rows() > 0) {
						$this->session->set_flashdata('error_message', get_phrase('sku_code_duplication'));
						$resultpost = array(
							"status" => 400,
							"message" => 'Duplicate SKU: ' . $item_code
						);
						return simple_json_output($resultpost);
					}
				}
			}

			if ($resultpost['status'] == 200) {
				$this->load->model('upload_model');
				$gst = clean_and_escape($this->input->post('gst'));
				$is_variation = clean_and_escape($this->input->post('is_variation'));

				$data['is_variation']   = $is_variation;
				$data['group_id']       = '';
				$data['color_id']       = '';
				$data['color_name']     = '';
				$data['sizes']          = '';
				$data['unit']           = clean_and_escape($this->input->post('unit'));
				$data['type']           = $product_type;
				$data['name']           = $name;
				$data['alias']          = clean_and_escape($this->input->post('alias'));
				$data['categories']     = $categories;
				$data['commission_id']  = clean_and_escape($this->input->post('commission_id'));
				$data['item_code']      = $item_code;
				$data['hsn_code']       = clean_and_escape($this->input->post('hsn_code'));
				$data['gst']            = ($gst) ? $gst : 0;
				$is_gst_applicable      = $this->input->post('is_gst_applicable');
				$data['is_gst_applicable'] = isset($is_gst_applicable) ? intval($is_gst_applicable) : 1;

				$duty_charge = clean_and_escape($this->input->post('duty_charge') ?? 0);
				$data['duty_charge']    = $duty_charge;

				$supplier_ids = $this->input->post('supplier_id');
				if (!empty($supplier_ids)) {
					if (!is_array($supplier_ids)) {
						$supplier_ids = explode(',', $supplier_ids);
					}
					$supplier_ids = array_filter($supplier_ids);
					if (!empty($supplier_ids)) {
						$data['supplier_id'] = implode(',', $supplier_ids);
						$this->db->select('name');
						$this->db->where_in('id', $supplier_ids);
						$query = $this->db->get('supplier');
						$supplier_names = [];
						foreach ($query->result_array() as $row) {
							$supplier_names[] = $row['name'];
						}
						$data['supplier_name'] = implode(',', $supplier_names);
					} else {
						$data['supplier_id'] = '';
						$data['supplier_name'] = '';
					}
				} else {
					$data['supplier_id'] = '';
					$data['supplier_name'] = '';
				}

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

				// usd_rate
				// actual_usd_rate
				// rate
				// product_mrp
				// costing_price
				// intimation

				$supplier_usd_rates = $this->input->post('supplier_usd_rate');
				$supplier_actual_usd_rates = $this->input->post('supplier_actual_usd_rate');
				$supplier_rates = $this->input->post('supplier_rate');
				$supplier_product_mrps = $this->input->post('supplier_product_mrp');
				$supplier_costing_prices = $this->input->post('supplier_costing_price');
				$supplier_intimations = $this->input->post('supplier_intimation');

				$first_supplier_id = !empty($supplier_ids) ? reset($supplier_ids) : null;
				if ($first_supplier_id) {
					$first_usd_rate = isset($supplier_usd_rates[$first_supplier_id]) ? $supplier_usd_rates[$first_supplier_id] : 0;
					$first_actual_usd_rate = isset($supplier_actual_usd_rates[$first_supplier_id]) ? $supplier_actual_usd_rates[$first_supplier_id] : 0;
					$first_rate = isset($supplier_rates[$first_supplier_id]) ? $supplier_rates[$first_supplier_id] : 0;
					$first_product_mrp = isset($supplier_product_mrps[$first_supplier_id]) ? $supplier_product_mrps[$first_supplier_id] : 0;
					$first_costing_price = isset($supplier_costing_prices[$first_supplier_id]) ? $supplier_costing_prices[$first_supplier_id] : 0;
					$first_intimation = isset($supplier_intimations[$first_supplier_id]) ? $supplier_intimations[$first_supplier_id] : 0;
				} else {
					$first_usd_rate = 0;
					$first_actual_usd_rate = 0;
					$first_rate = 0;
					$first_product_mrp = 0;
					$first_costing_price = 0;
					$first_intimation = 0;
				}

				$data['usd_rate']  		= clean_and_escape($first_usd_rate);
				$data['actual_usd_rate']  = clean_and_escape($first_actual_usd_rate);
				$data['rate']  					= clean_and_escape($first_rate);
				$data['product_mrp']     = clean_and_escape($first_product_mrp);
				$data['costing_price']   = clean_and_escape($first_costing_price);
				$data['intimation']      = clean_and_escape($first_intimation);
				$data['off_sale_price']  = clean_and_escape($this->input->post('off_sale_price') ?? 0);
				$data['status']          = clean_and_escape($this->input->post('status'));
				$data['min_stock']       = clean_and_escape($first_intimation);
				$data['listed_1']        = clean_and_escape($this->input->post('p_listed_1'));
				$data['listed_2']        = clean_and_escape($this->input->post('p_listed_2'));
				$data['listed_3']        = clean_and_escape($this->input->post('p_listed_3'));
				$data['listed_4']        = clean_and_escape($this->input->post('p_listed_4'));
				$data['listed_5']        = clean_and_escape($this->input->post('p_listed_5'));
				$data['listed_6']       = 1;
				$data['listed_7']       = 1;
				$data['is_other_sku']   = 0;
				$data['product_type']   = 'import';
				$data['added_date']     = date("Y-m-d H:i:s");
				$opening_stock = $this->input->post('opening_stock');
				$data['opening_stock']  = (!empty($opening_stock)) ? intval($opening_stock) : 0;

				$this->db->insert('raw_products', $data);
				$user_id = $this->db->insert_id();
				$this->file_model->add_product_images($user_id);

				// Insert supplier-wise pricing into product_variations
				if (!empty($supplier_ids)) {
					foreach ($supplier_ids as $s_id) {
						$p_var = [];
						$p_var['product_id']      = $user_id;
						$p_var['supplier_id']     = $s_id;
						$p_var['usd_rate']        = clean_and_escape($supplier_usd_rates[$s_id] ?? 0);
						$p_var['actual_usd_rate'] = clean_and_escape($supplier_actual_usd_rates[$s_id] ?? 0);
						$p_var['rate']            = clean_and_escape($supplier_rates[$s_id] ?? 0);
						$p_var['product_mrp']     = clean_and_escape($supplier_product_mrps[$s_id] ?? 0);
						$p_var['costing_price']   = clean_and_escape($supplier_costing_prices[$s_id] ?? 0);
						$p_var['intimation']      = clean_and_escape($supplier_intimations[$s_id] ?? 0);
						
						$this->db->insert('product_variations', $p_var);
					}
				}

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

					// Insert audit log
					$product_data = $this->get_complete_product_log_data($user_id);
					$log_data = array(
						'parent_id'      => NULL,
						'ref_id'         => $user_id,
						'module'         => 'product',
						'action'         => 'add',
						'message'        => 'Product added by ' . $this->session->userdata('super_name'),
						'json'           => json_encode($product_data),
						'table_name'     => 'raw_products',
						'added_by'       => $this->session->userdata('super_user_id'),
						'added_by_email' => $this->session->userdata('super_email'),
						'added_by_name'  => $this->session->userdata('super_name'),
						'added_by_type'  => $this->session->userdata('super_type')
					);
					$this->db->insert('sys_logs', $log_data);

					$this->session->set_flashdata('flash_message', get_phrase('products_added_successfully'));
					$resultpost = array(
						"status" => 200,
						"message" => get_phrase('product_added_successfully'),
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

	public function edit_raw_products($id = "")
	{

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('products_updated_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$name = clean_and_escape($this->input->post('name'));

		$item_code = clean_and_escape($this->input->post('item_code'));
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

		// SKU Duplication check only for 'ready' goods
		if ($product_type == 'ready') {
			foreach ($other_skus as $sku) {
				$checkProduct = $this->db->select('id')->where('item_code', $sku)->where('item_code!=', '')->where('id!=', $id)->get('raw_products');
				if ($checkProduct->num_rows() > 0) {
					$exist_sku[] = $sku;
				} else {
					$checkProduct = $this->db->select('id')->where('sku_code', $sku)->where('sku_code!=', '')->where('product_id!=', $id)->get('product_sku');
					if ($checkProduct->num_rows() > 0) {
						$exist_sku[] = $sku;
					}
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
			$old_product_data = $this->get_complete_product_log_data($id);
			$gst = clean_and_escape($this->input->post('gst'));
			$this->load->model('upload_model');

			$supplier_ids = $this->input->post('supplier_id');
			if (!empty($supplier_ids)) {
				if (!is_array($supplier_ids)) {
					$supplier_ids = explode(',', $supplier_ids);
				}
				$supplier_ids = array_filter($supplier_ids);
				if (!empty($supplier_ids)) {
					$data['supplier_id'] = implode(',', $supplier_ids);
					$this->db->select('name');
					$this->db->where_in('id', $supplier_ids);
					$query = $this->db->get('supplier');
					$supplier_names = [];
					foreach ($query->result_array() as $row) {
						$supplier_names[] = $row['name'];
					}
					$data['supplier_name'] = implode(',', $supplier_names);
				} else {
					$data['supplier_id'] = '';
					$data['supplier_name'] = '';
				}
			} else {
				$data['supplier_id'] = '';
				$data['supplier_name'] = '';
			}

			// Parse supplier-wise pricing fields
			$supplier_usd_rates = $this->input->post('supplier_usd_rate');
			$supplier_actual_usd_rates = $this->input->post('supplier_actual_usd_rate');
			$supplier_rates = $this->input->post('supplier_rate');
			$supplier_product_mrps = $this->input->post('supplier_product_mrp');
			$supplier_costing_prices = $this->input->post('supplier_costing_price');
			$supplier_intimations = $this->input->post('supplier_intimation');

			$first_supplier_id = !empty($supplier_ids) ? reset($supplier_ids) : null;
			if ($first_supplier_id) {
				$first_usd_rate = isset($supplier_usd_rates[$first_supplier_id]) ? $supplier_usd_rates[$first_supplier_id] : 0;
				$first_actual_usd_rate = isset($supplier_actual_usd_rates[$first_supplier_id]) ? $supplier_actual_usd_rates[$first_supplier_id] : 0;
				$first_rate = isset($supplier_rates[$first_supplier_id]) ? $supplier_rates[$first_supplier_id] : 0;
				$first_product_mrp = isset($supplier_product_mrps[$first_supplier_id]) ? $supplier_product_mrps[$first_supplier_id] : 0;
				$first_costing_price = isset($supplier_costing_prices[$first_supplier_id]) ? $supplier_costing_prices[$first_supplier_id] : 0;
				$first_intimation = isset($supplier_intimations[$first_supplier_id]) ? $supplier_intimations[$first_supplier_id] : 0;
			} else {
				$first_usd_rate = 0;
				$first_actual_usd_rate = 0;
				$first_rate = 0;
				$first_product_mrp = 0;
				$first_costing_price = 0;
				$first_intimation = 0;
			}

			$is_variation = clean_and_escape($this->input->post('is_variation'));
			$data['type']           = $product_type;
			$data['name']           = $name;
			$data['alias']  = clean_and_escape($this->input->post('alias'));
			$data['is_variation']   	= $is_variation;
			$data['categories']     	= $categories;
			$data['commission_id']  	= clean_and_escape($this->input->post('commission_id'));
			$data['item_code']      	= $item_code;
			$data['hsn_code']       	= clean_and_escape($this->input->post('hsn_code'));
			$data['min_stock']      	= clean_and_escape($first_intimation);
			$data['intimation']     	= clean_and_escape($first_intimation);
			$data['product_mrp']    	= clean_and_escape($first_product_mrp);
			$data['costing_price']  	= clean_and_escape($first_costing_price);
			$data['gst']            	= ($gst) ? $gst : 0;
			$data['unit']           	= clean_and_escape($this->input->post('unit'));
			$is_gst_applicable      	= $this->input->post('is_gst_applicable');
			$data['is_gst_applicable'] 	= isset($is_gst_applicable) ? intval($is_gst_applicable) : 1;

			$duty_charge = clean_and_escape($this->input->post('duty_charge') ?? 0);
			$data['duty_charge']    = $duty_charge;
			$data['net_weight']    		= clean_and_escape($this->input->post('net_weight'));
			$data['gross_weight']  		= clean_and_escape($this->input->post('gross_weight'));
			$data['length']						= clean_and_escape($this->input->post('length'));
			$data['width']						= clean_and_escape($this->input->post('width'));
			$data['height']  					= clean_and_escape($this->input->post('height'));
			$data['cbm']							= clean_and_escape($this->input->post('cbm'));
			$data['rate']  						= clean_and_escape($first_rate);
			$data['usd_rate']  				= clean_and_escape($first_usd_rate);
			$data['actual_usd_rate']	= clean_and_escape($first_actual_usd_rate);
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

			$data['off_sale_price'] = clean_and_escape($this->input->post('off_sale_price') ?? 0);
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

			$data['product_type'] = 'import';
			$opening_stock = $this->input->post('opening_stock');
			$data['opening_stock']  = (!empty($opening_stock)) ? intval($opening_stock) : 0;
			$this->db->where('id', $id);
			$this->db->update('raw_products', $data);

			$user_id = $id;

			// Update supplier-wise pricing into product_variations
			$this->db->where('product_id', $user_id)->delete('product_variations');

			if (!empty($supplier_ids)) {
				foreach ($supplier_ids as $s_id) {
					$p_var = [];
					$p_var['product_id']      = $user_id;
					$p_var['supplier_id']     = $s_id;
					$p_var['usd_rate']        = clean_and_escape($supplier_usd_rates[$s_id] ?? 0);
					$p_var['actual_usd_rate'] = clean_and_escape($supplier_actual_usd_rates[$s_id] ?? 0);
					$p_var['rate']            = clean_and_escape($supplier_rates[$s_id] ?? 0);
					$p_var['product_mrp']     = clean_and_escape($supplier_product_mrps[$s_id] ?? 0);
					$p_var['costing_price']   = clean_and_escape($supplier_costing_prices[$s_id] ?? 0);
					$p_var['intimation']      = clean_and_escape($supplier_intimations[$s_id] ?? 0);
					
					$this->db->insert('product_variations', $p_var);
				}
			}

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

			$new_product_data = $this->get_complete_product_log_data($id);
			$log_json = array(
				'old_data' => $old_product_data,
				'new_data' => $new_product_data
			);
			$log_data = array(
				'parent_id'      => NULL,
				'ref_id'         => $id,
				'module'         => 'product',
				'action'         => 'update',
				'message'        => 'Product updated by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($log_json),
				'table_name'     => 'raw_products',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

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

		// Insert audit log
		$product_data = $this->get_complete_product_log_data($id);
		$log_data = array(
			'parent_id'      => NULL,
			'ref_id'         => $id,
			'module'         => 'product',
			'action'         => 'delete',
			'message'        => 'Product deleted by ' . $this->session->userdata('super_name'),
			'json'           => json_encode($product_data),
			'table_name'     => 'raw_products',
			'added_by'       => $this->session->userdata('super_user_id'),
			'added_by_email' => $this->session->userdata('super_email'),
			'added_by_name'  => $this->session->userdata('super_name'),
			'added_by_type'  => $this->session->userdata('super_type')
		);
		$this->db->insert('sys_logs', $log_data);
		
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
		WHERE (p.is_deleted='0' AND p.product_type='import') $keyword_filter group by p.id ORDER BY p.id ASC")->num_rows();
		$query = $this->db->query("SELECT p.id,p.alias,p.categories,p.group_id,p.color_name,p.item_code,p.is_variation,p.image,p.name,p.unit,p.amount,p.form,p.gst_type,p.gst,p.gst_amount,p.total_amount,p.hsn_code,p.sizes,p.cartoon_qty, (SELECT image FROM product_images WHERE product_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) AS product_image FROM raw_products as p
		LEFT JOIN product_variation as pv ON p.id = pv.product_id
		WHERE (p.is_deleted='0' AND p.product_type='import') $keyword_filter group by p.id ORDER BY p.id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$is_variation = $item['is_variation'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/raw_products/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/raw-products/edit/' . $id;
				$history_url = "showRightCanvas('" . base_url() . "modal/popup_inventory/canvas_product_history/" . $id . "', 'Product History')";
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';

				$action .= '<a href="javascript:void(0);" onclick="' . $history_url . '" data-toggle="tooltip" data-bs-placement="top" title="History"><button type="button" class="btn mr-1 mb-1 icon-btn-history" style="background-color: #7367f0; color: #fff; border-color: #7367f0;"><i class="fa fa-history" aria-hidden="true"></i></button></a>';

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
					"image"       => !empty($item['product_image']) ? '<img src="' . base_url() . $item['product_image'] . '" width="40" height="40" style="object-fit: cover; border-radius: 4px;">' : '-',
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
		$ready_is_applieds = $this->input->post('ready_is_applied');

		$spare_product_ids = $this->input->post('spare_product_id');
		$spare_qtys = $this->input->post('spare_qty');
		$spare_cbms = $this->input->post('spare_cbm');
		$spare_total_cbms = $this->input->post('spare_total_cbm');
		$spare_model_nos = $this->input->post('spare_model_no');
		$spare_pending_po_qtys = $this->input->post('spare_pending_po_qty');
		$spare_loading_list_qtys = $this->input->post('spare_loading_list_qty');
		$spare_in_stock_qtys = $this->input->post('spare_in_stock_qty');
		$spare_company_stocks = $this->input->post('spare_company_stock');
		$spare_is_applieds = $this->input->post('spare_is_applied');

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
					$is_applied = intval($ready_is_applieds[$supplier_row_id][$product_index] ?? 0);
					
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
							'is_applied' => $is_applied,
						);
					}
				}
			}

			// Process Spare Part products for this supplier row
			if (isset($spare_product_ids[$supplier_row_id]) && is_array($spare_product_ids[$supplier_row_id])) {
				foreach ($spare_product_ids[$supplier_row_id] as $product_index => $product_id) {
					$product_id = intval($product_id);
					$qty = floatval($spare_qtys[$supplier_row_id][$product_index] ?? 0);
					$is_applied = intval($spare_is_applieds[$supplier_row_id][$product_index] ?? 0);
					
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
							'is_applied' => $is_applied,
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
				'is_replace' => (isset($row['is_applied']) && $row['is_applied'] == 1) ? 1 : 0,
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

			// If is_applied = 1, call helper function update_replace_product
			if (isset($row['is_applied']) && $row['is_applied'] == 1) {
				$this->common_model->update_replace_product('pending', $insert_id, $row['product_id'], $row['quantity']);
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

			// Insert audit log
			$po_log_data = $this->get_complete_purchase_order_log_data($insert_id);
			$log_data = array(
				'parent_id'      => $insert_id,
				'ref_id'         => NULL,
				'module'         => 'purchase_order',
				'action'         => 'add',
				'message'        => 'Purchase Order added by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($po_log_data),
				'table_name'     => 'purchase_order',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			$this->session->set_flashdata('flash_message', get_phrase('purchase_order_added_successfully'));
		}

		return simple_json_output($resultpost);
	}

	public function add_local_purchase_order()
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
		$method = clean_and_escape($this->input->post('input_method')) ?: 'local';
		$warehouse_id = $this->input->post('warehouse_id');
		$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
		$company_id = $this->input->post('company_id');
		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');
		$supplier_id = $this->input->post('supplier_id');
		$supplier_name = $this->common_model->selectByidParam($supplier_id, 'supplier', 'name');

		$product_ids = $this->input->post('product_id');
		$white_qtys = $this->input->post('white_qty');
		$black_qtys = $this->input->post('black_qty');

		// Validate products exist
		$has_valid_product = false;
		if (is_array($product_ids)) {
			for ($i = 0; $i < count($product_ids); $i++) {
				$product_id = intval($product_ids[$i]);
				$white_qty = floatval($white_qtys[$i] ?? 0);
				$black_qty = floatval($black_qtys[$i] ?? 0);
				$quantity = $white_qty + $black_qty;
				if ($product_id > 0 && $quantity > 0) {
					$has_valid_product = true;
				}
			}
		}

		$charge_ids = $this->input->post('charge_id');
		$has_valid_charge = false;
		if (is_array($charge_ids)) {
			foreach ($charge_ids as $cid) {
				if (intval($cid) > 0) {
					$has_valid_charge = true;
				}
			}
		}

		if (!$has_valid_product && !$has_valid_charge) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Please add at least one product or one expense."
			);
			return simple_json_output($resultpost);
		}

		// Pre-compute CBM totals
		$total_cbm = 0.00;
		for ($i = 0; $i < count($product_ids); $i++) {
			$product_id = intval($product_ids[$i]);
			$white_qty = floatval($white_qtys[$i] ?? 0);
			$black_qty = floatval($black_qtys[$i] ?? 0);
			$quantity = $white_qty + $black_qty;
			if ($product_id > 0 && $quantity > 0) {
				$product_details = $this->get_raw_products_by_id($product_id)->row_array();
				if ($product_details) {
					$cbm = floatval($product_details['cbm'] ?? 0);
					$total_cbm += $cbm * $quantity;
				}
			}
		}

		// Prepare purchase_order data
		$delivery_address = $this->input->post('delivery_address');
		$data = array(
			'method' => $method,
			'voucher_no' => $voucher_no,
			'refrence_no' => $this->input->post('refrence_no'),
			'date' => $this->input->post('date'),
			'delivery_date' => $this->input->post('delivery_date'),
			'company_id' => $company_id,
			'company_name' => $company_name,
			'supplier_id' => $supplier_id,
			'supplier_name' => $supplier_name,
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
			'net_sales_value_1' => floatval($this->input->post('net_sales_value_1')),
			'discount_type' => 'product',
			'discount' => 0,
			'discount_amount' => 0.00000,
			'gst_type' => $this->input->post('gst_type'),
			'cgst_amount' => floatval($this->input->post('central_gst')),
			'sgst_amount' => floatval($this->input->post('state_gst')),
			'igst_amount' => floatval($this->input->post('igst')),
			'net_sales_value_2' => floatval($this->input->post('net_sales_value_2')),
			'other_charges_amount' => floatval($this->input->post('other_charges_amount')),
			'round_of' => floatval($this->input->post('round_of')),
			'grand_total' => floatval($this->input->post('grand_total')),
			'inr_rate' => 1.00000,
			'delivery_status' => 'purchase_in',
			'is_deleted' => 0,
			'added_date' => date("Y-m-d H:i:s"),
			'completed_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
			'basic_value' => floatval($this->input->post('basic_value')),
			'total_black_amount_summary' => floatval($this->input->post('total_black_amount_summary')),
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

		// Insert items into purchase_order_product, inventory, and inventory_history
		$rates = $this->input->post('rate');
		$per_qty_bill_amts = $this->input->post('per_qty_bill_amt');
		$total_bill_amts = $this->input->post('total_bill_amt');
		$gst_rates = $this->input->post('gst_rate');
		$gst_amts = $this->input->post('gst_amt');
		$total_bill_gst_amts = $this->input->post('total_bill_gst_amt');
		$per_qty_black_amts = $this->input->post('per_qty_black_amt');
		$total_black_amts = $this->input->post('total_black_amt');
		$final_amts = $this->input->post('final_amt');

		for ($i = 0; $i < count($product_ids); $i++) {
			$product_id = intval($product_ids[$i]);
			$white_qty = floatval($white_qtys[$i] ?? 0);
			$black_qty = floatval($black_qtys[$i] ?? 0);
			$quantity = $white_qty + $black_qty;

			if ($product_id > 0 && $quantity > 0) {
				$product_details = $this->get_raw_products_by_id($product_id)->row_array();
				if (!$product_details) {
					$this->db->trans_rollback();
					$resultpost = array(
						"status" => 400,
						"message" => "Product not found: ID " . $product_id
					);
					return simple_json_output($resultpost);
				}

				$rate = floatval($rates[$i] ?? 0);
				$per_qty_bill_amt = floatval($per_qty_bill_amts[$i] ?? 0);
				$total_bill_amt = floatval($total_bill_amts[$i] ?? 0);
				$gst_rate = floatval($gst_rates[$i] ?? 0);
				$gst_amt = floatval($gst_amts[$i] ?? 0);
				$total_bill_gst_amt = floatval($total_bill_gst_amts[$i] ?? 0);
				$per_qty_black_amt = floatval($per_qty_black_amts[$i] ?? 0);
				$total_black_amt = floatval($total_black_amts[$i] ?? 0);
				$final_amt = floatval($final_amts[$i] ?? 0);

				$cbm = floatval($product_details['cbm'] ?? 0);
				$total_cbm_item = $cbm * $quantity;

				// 1. Insert into purchase_order_product
				$data_p = array(
					'parent_id' => $insert_id,
					'supplier_id' => $supplier_id,
					'product_type' => $product_details['type'] ?: 'ready',
					'product_id' => $product_id,
					'categories' => $product_details['categories'] ?? NULL,
					'sizes' => $product_details['sizes'] ?? NULL,
					'group_id' => $product_details['group_id'] ?? NULL,
					'color_id' => $product_details['color_id'] ?? NULL,
					'color_name' => $product_details['color_name'] ?? NULL,
					'product_name' => $product_details['name'] ?? '',
					'hsn_code' => $product_details['hsn_code'] ?? NULL,
					'item_code' => $product_details['item_code'] ?? NULL,
					'unit' => $product_details['unit'] ?? NULL,
					'quantity' => $quantity,
					'white_qty' => $white_qty,
					'black_qty' => $black_qty,
					'cbm' => $cbm,
					'total_cbm' => $total_cbm_item,
					'pending_po_qty' => 0,
					'loading_list_qty' => 0,
					'in_stock_qty' => $quantity,
					'current_company_qty' => 0,
					'cartoon' => intval($product_details['cartoon_qty'] ?? 0),
					'rate' => $rate,
					'basic_amount' => $per_qty_bill_amt,
					'discount' => 0,
					'discount_amount' => 0.00000,
					'gst' => $gst_rate,
					'gst_amount' => $gst_amt,
					'total_val' => $total_bill_gst_amt,
					'black_amt' => $per_qty_black_amt,
					'black_amt_total' => $total_black_amt,
					'grand_total' => $final_amt,
					'pending' => 0,
					'received' => $quantity,
					'received_date' => $data['date'],
					'invoice_no' => $voucher_no,
					'is_complete' => 1,
				);

				if (!$this->db->insert('purchase_order_product', $data_p)) {
					$this->db->trans_rollback();
					$resultpost = array(
						"status" => 400,
						"message" => get_phrase('something_went_wrong')
					);
					return simple_json_output($resultpost);
				}

				$po_prod_id = $this->db->insert_id();

				// 2. Insert into inventory
				$inv = array(
					'supplier_id' => $supplier_id,
					'company_id' => $company_id,
					'warehouse_id' => $warehouse_id,
					'warehouse_name' => $warehouse_name,
					'product_id' => $product_id,
					'product_name' => $product_details['name'] ?? '',
					'categories' => $product_details['categories'] ?? '',
					'sku' => $product_details['item_code'] ?? '',
					'item_code' => $product_details['item_code'] ?? '',
					'quantity' => $quantity,
					'actual_rmb' => 0.00,
					'total_rmb' => 0.00,
					'actual_usd' => 0.00,
					'official_qty' => $white_qty,
					'official_rate_rs' => $per_qty_bill_amt,
					'official_total_rs' => $total_bill_amt,
					'actual_inr' => $rate,
					'black_qty' => $black_qty,
					'pending_qty' => 0,
					'duty_percent' => 0.00,
					'duty_amt' => 0.00,
					'duty_surcharge' => 0.00,
					'taxable_value' => $total_bill_amt,
					'gst_amt' => $gst_amt,
					'total_amt' => $final_amt,
					'batch_no' => $voucher_no,
					'po_row_id' => $po_prod_id,
					'expiry_date' => NULL,
				);

				if (!$this->db->insert('inventory', $inv)) {
					$this->db->trans_rollback();
					$resultpost = array(
						"status" => 400,
						"message" => get_phrase('something_went_wrong')
					);
					return simple_json_output($resultpost);
				}

				$inventory_id = $this->db->insert_id();

				// 3. Insert into inventory_history
				$history = array(
					'supplier_id' => $supplier_id,
					'company_id' => $company_id,
					'parent_id' => $inventory_id,
					'warehouse_id' => $warehouse_id,
					'warehouse_name' => $warehouse_name,
					'product_id' => $product_id,
					'product_name' => $product_details['name'] ?? '',
					'categories' => $product_details['categories'] ?? '',
					'sku' => $product_details['item_code'] ?? '',
					'item_code' => $product_details['item_code'] ?? '',
					'order_id' => $insert_id,
					'status' => 'in',
					'quantity' => $quantity,
					'actual_rmb' => 0.00,
					'total_rmb' => 0.00,
					'actual_usd' => 0.00,
					'official_qty' => $white_qty,
					'official_rate_rs' => $per_qty_bill_amt,
					'official_total_rs' => $total_bill_amt,
					'actual_inr' => $rate,
					'black_qty' => $black_qty,
					'pending_qty' => 0,
					'black_rate_rs' => $per_qty_black_amt,
					'black_total_rs' => $total_black_amt,
					'duty_percent' => 0.00,
					'duty_amt' => 0.00,
					'duty_surcharge' => 0.00,
					'taxable_value' => $total_bill_amt,
					'gst_amt' => $gst_amt,
					'total_amt' => $final_amt,
					'received_date' => $data['date'],
					'batch_no' => $voucher_no,
					'expiry_date' => NULL,
					'invoice_no' => $voucher_no,
					'is_deleted' => 0,
					'added_date' => date("Y-m-d H:i:s"),
					'added_by_id' => $this->session->userdata('super_user_id'),
					'added_by_name' => $this->session->userdata('super_name'),
				);

				if (!$this->db->insert('inventory_history', $history)) {
					$this->db->trans_rollback();
					$resultpost = array(
						"status" => 400,
						"message" => get_phrase('something_went_wrong')
					);
					return simple_json_output($resultpost);
				}
			}
		}

		// Insert charges into purchase_order_charges
		$charge_id_arr = $this->input->post('charge_id');
		$charge_gst_arr = $this->input->post('charge_gst');
		$charge_price_arr = $this->input->post('charge_price');
		$charge_total_arr = $this->input->post('charge_total');

		if(!empty($charge_id_arr)) {
			for ($i = 0; $i < count($charge_id_arr); $i++) {
				if (!empty($charge_id_arr[$i])) {
					$type_id = $charge_id_arr[$i];
					
					$other_charge = $this->db->get_where('other_charges', ['id' => $type_id])->row_array();
					$type_name = $other_charge ? $other_charge['name'] : '';

					$data_charge = array(
						'order_id'   => $insert_id,
						'type_id'    => $type_id,
						'type'       => $type_name,
						'gst'        => (float) ($charge_gst_arr[$i] ?? 0),
						'amount'     => (float) ($charge_price_arr[$i] ?? 0),
						'total_amt'  => (float) ($charge_total_arr[$i] ?? 0),
					);
					if (!$this->db->insert('purchase_order_charges', $data_charge)) {
						$this->db->trans_rollback();
						$resultpost = array(
							"status" => 400,
							"message" => get_phrase('something_went_wrong')
						);
						return simple_json_output($resultpost);
					}
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
		")->result_array();

		// Group products by supplier and product_type
		$grouped_products = array();
		foreach ($products_query as $product) {
			$supplier_id = $product['supplier_id'];
			$supplier_name = $product['supplier_name'] ?? 'Unknown Supplier';
			$product_type = $product['product_type'] ?? 'ready';
			
			// Check if this product is low stock for this supplier
			// Available qty = inventory qty - booked qty from unapproved normal sales orders
			$is_low_stock = 0;
			$low_stock_query = $this->db->query("
				SELECT SUM(i.quantity) as total_qty
				FROM inventory i
				INNER JOIN product_variations pv ON pv.product_id = i.product_id AND pv.supplier_id = i.supplier_id
				WHERE i.product_id = ? AND i.supplier_id = ?
				GROUP BY i.product_id, i.supplier_id, pv.intimation
				HAVING SUM(i.quantity) > 0
				  AND (SUM(i.quantity) - COALESCE((
						SELECT SUM(sop.qty)
						FROM sales_order_product sop
						INNER JOIN sales_order so ON so.id = sop.order_id
						WHERE sop.product_id = ?
						  AND so.type = 'normal'
						  AND so.is_approved = 0
						  AND so.is_deleted = 0
					  ), 0)) <= pv.intimation
				LIMIT 1
			", array($product['product_id'], $supplier_id, $product['product_id']));

			if ($low_stock_query->num_rows() > 0) {
				$is_low_stock = 1;
			}
			$product['is_low_stock'] = $is_low_stock;

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

		$old_po_log_data = $this->get_complete_purchase_order_log_data($po_id);

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
		$ready_is_applieds = $this->input->post('ready_is_applied');

		$spare_product_ids = $this->input->post('spare_product_id');
		$spare_qtys = $this->input->post('spare_qty');
		$spare_cbms = $this->input->post('spare_cbm');
		$spare_total_cbms = $this->input->post('spare_total_cbm');
		$spare_model_nos = $this->input->post('spare_model_no');
		$spare_pending_po_qtys = $this->input->post('spare_pending_po_qty');
		$spare_loading_list_qtys = $this->input->post('spare_loading_list_qty');
		$spare_in_stock_qtys = $this->input->post('spare_in_stock_qty');
		$spare_company_stocks = $this->input->post('spare_company_stock');
		$spare_is_applieds = $this->input->post('spare_is_applied');

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
							'is_applied' => intval($ready_is_applieds[$supplier_row_id][$product_index] ?? 0),
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
							'is_applied' => intval($spare_is_applieds[$supplier_row_id][$product_index] ?? 0),
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

		// Fetch existing replace products for this PO to revert them first
		$existing_replaced_products = $this->db->get_where('purchase_order_product', array(
			'parent_id' => $po_id,
			'is_replace' => 1
		))->result_array();

		foreach ($existing_replaced_products as $erp) {
			$this->common_model->revert_replace_products($po_id, $erp['product_id']);
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

			$is_replace = (isset($row['is_applied']) && $row['is_applied'] == 1) ? 1 : 0;

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
				'is_replace' => $is_replace,
			);

			if (!$this->db->insert('purchase_order_product', $data_p)) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => get_phrase('something_went_wrong')
				);
				return simple_json_output($resultpost);
			}

			// If replacement is applied, trigger update_replace_product helper logic
			if ($is_replace === 1) {
				$this->common_model->update_replace_product('pending', $po_id, $row['product_id'], $row['quantity']);
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

			// Insert audit log
			$new_po_log_data = $this->get_complete_purchase_order_log_data($po_id);
			$log_json = array(
				'old_data' => $old_po_log_data,
				'new_data' => $new_po_log_data
			);
			$log_data = array(
				'parent_id'      => $po_id,
				'ref_id'         => NULL,
				'module'         => 'purchase_order',
				'action'         => 'edit',
				'message'        => 'Purchase Order edited by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($log_json),
				'table_name'     => 'purchase_order',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

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

		// Capture data before soft delete for audit log
		$po_log_data = $this->get_complete_purchase_order_log_data($id);

		// Find any replaced products and revert them before deletion
		$replaced_products = $this->db->get_where('purchase_order_product', array(
			'parent_id' => $id,
			'is_replace' => 1
		))->result_array();

		foreach ($replaced_products as $rp) {
			$this->common_model->revert_replace_products($id, $rp['product_id']);
		}

		$data['is_deleted'] = '1';
		$this->db->where('id', $id);
		$this->db->update('purchase_order', $data);

		// Insert audit log
		$log_data = array(
			'parent_id'      => $id,
			'ref_id'         => NULL,
			'module'         => 'purchase_order',
			'action'         => 'delete',
			'message'        => 'Purchase Order deleted by ' . $this->session->userdata('super_name'),
			'json'           => json_encode($po_log_data),
			'table_name'     => 'purchase_order',
			'added_by'       => $this->session->userdata('super_user_id'),
			'added_by_email' => $this->session->userdata('super_email'),
			'added_by_name'  => $this->session->userdata('super_name'),
			'added_by_type'  => $this->session->userdata('super_type')
		);
		$this->db->insert('sys_logs', $log_data);

		return simple_json_output($resultpost);
	}

	public function delete_inv_purchase_order($id)
	{
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('purchase_order_deleted_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		// Find any replaced products and revert them before deletion
		$replaced_products = $this->db->get_where('purchase_order_product', array(
			'parent_id' => $id,
			'is_replace' => 1
		))->result_array();

		foreach ($replaced_products as $rp) {
			$this->common_model->revert_replace_products($id, $rp['product_id']);
		}

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

	public function get_invoice_no()
	{
		// date("Y-m-d H:i:s");
		$year  = current_year();
		$voucher_no = '';
		$query = $this->db->query("SELECT number,year,prefix FROM sales_invoice_no WHERE year='$year' ORDER BY id DESC LIMIT 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$number = $row['number'];
			$voucher_no = $row['prefix'] . '/' . $row['year'] . '/' . $number;
		} else {
			$number = 1;
			$voucher_no = 'SO' . '/' . $year . '/' . $number;
			$this->db->insert('sales_invoice_no', array('number' => $number, 'year' => $year, 'prefix' => 'SO'));
		}

		$check_order = $this->db->where('invoice_no',$voucher_no)->get('invoice_order');
		if($check_order->num_rows() > 0){
			$number = $number + 1;
			$this->db->where('year', $year)->update('sales_invoice_no', array('number' => $number));
			return $this->get_invoice_no();
		} else {
			return $voucher_no;
		}
	}

	// public function update_invoice_no($order_no)
	// {
	// 	$order_no = explode('/', $order_no);
	// 	$pre = $order_no[0];
	// 	$year = $order_no[1];
	// 	$number = $order_no[2];
	// 	$query = $this->db->query("SELECT id FROM sales_order_no WHERE year='$year' ORDER BY id DESC LIMIT 1");
	// 	if ($query->num_rows() > 0) {
	// 		$row = $query->row_array();
	// 		$id = $row['id'];
	// 		$data = array();
	// 		$data['prefix'] = $pre;
	// 		$data['year'] = $year;
	// 		$data['number'] = $number;
	// 		$this->db->where('id', $id);
	// 		$this->db->update('sales_order_no', $data);
	// 	} else {
	// 		$data = array();
	// 		$data['prefix'] = $pre;
	// 		$data['year'] = $year;
	// 		$data['number'] = $number;
	// 		$this->db->insert('sales_order_no', $data);
	// 	}
	// }

	public function get_purchase_order_local($delivery_status = [])
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

		$total_count = $this->db->query("SELECT id FROM purchase_order WHERE (is_deleted='0') AND method = 'local' $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, delivery_status, voucher_no, date, warehouse_name, company_name FROM purchase_order WHERE (is_deleted='0') AND method = 'local' $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$delivery_status = $item['delivery_status'];

				// Get totals and supplier list
				$sql = "
					SELECT
						pop.supplier_id,
						COALESCE(s.name, '') AS supplier_name,
						COUNT(DISTINCT pop.product_id) AS total_products,
						SUM(pop.quantity) AS total_qty
					FROM purchase_order_product pop
					LEFT JOIN supplier s ON s.id = pop.supplier_id
					WHERE pop.parent_id = '$id'
					GROUP BY pop.supplier_id, s.name
					ORDER BY pop.id
				";

				$rows = $this->db->query($sql)->result_array();
				$suppliers = array();
				$total_products = 0;
				$total_qty = 0;

				foreach ($rows as $r) {
					if (!empty($r['supplier_name'])) {
						$suppliers[] = $r['supplier_name'];
					}
					$total_products += (int)$r['total_products'];
					$total_qty += (int)$r['total_qty'];
				}

				// Actions
				$action ='-';
				$export_excel_url = "generate_excel('".$id."')";
				$view_po_details_url = "showLargeModal('" . base_url() . "modal/popup_inventory/modal_purchase_order_details/" . $id . "','PO Details - " . $item['voucher_no'] . "')";
				$delete_po_url = "confirm_modal('" . base_url() . "inventory/purchase_order/delete/" . $id . "','Are you sure want to delete!')";
				
				// $action = '<div class="btn-group">
				// 	<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
				// 		<i class="mdi mdi-dots-vertical"></i></button>
				// 	<div class="dropdown-menu">
				// 		<a href="javascript:void(0)" class="dropdown-item" onclick="' . $export_excel_url . '"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export PO</a>
				// 		<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_po_details_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View PO Details</a>
				// 		<a class="dropdown-item" href="javascript:void(0)" onclick="' . $delete_po_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete PO</a>
				// 	</div>
				// </div>';

				$data[] = array(
					"sr_no"             => ++$start,
					"id"                => $item['id'],
					"date"              => date('d M, Y', strtotime($item['date'])) . ' - ' . $item['voucher_no'],
					"suppliers"         => !empty($suppliers) ? array_to_list($suppliers) : '-',
					"total_products"    => $total_products,
					"total_quantity"    => $total_qty,
					"po_date"           => date('d M, Y', strtotime($item['date'])),
					"action"            => $action
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

		if (isset($_REQUEST['status']) && $_REQUEST['status'] != ""){
			$keyword_filter .= " AND (delivery_status = '" . $_REQUEST['status'] . "')";
		} elseif (count($delivery_status) > 0) {
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
		$query = $this->db->query("SELECT id,delivery_status, voucher_no,date,delivery_date,warehouse_name,company_name, boe_no, boe_date, arrival_date, expected_date, is_locked FROM purchase_order WHERE (is_deleted='0') AND method = 'import' $keyword_filter ORDER BY id DESC LIMIT $start, $length");

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
					ORDER BY pop.id
				";

				$rows = $this->db->query($sql)->result_array();
				foreach ($rows as $r) {
					$po['ready'][]    = $r['ready_qty'];
					$po['spare'][]    = $r['spare_qty'];
					$po['supplier'][] = $r['supplier_name'];
				}

				// Priority List
				$priority_loading = [
					"pl_ready"    			=> [],
					"pl_spare"    			=> [],

					"supplier" 					=> [],
				];

				$sql = "
					SELECT
						pop.supplier_id,
						COALESCE(s.name, '') AS supplier_name,
						SUM(CASE WHEN pop.product_type = 'spare' THEN pop.quantity ELSE 0 END) AS pl_spare_qty,
						SUM(CASE WHEN pop.product_type = 'spare' THEN 0 ELSE pop.quantity END) AS pl_ready_qty
					FROM po_products pop
					LEFT JOIN supplier s ON s.id = pop.supplier_id
					WHERE pop.parent_id = '$id'
					GROUP BY pop.supplier_id, s.name
					ORDER BY pop.id
				";

				$rows = $this->db->query($sql)->result_array();
				foreach ($rows as $r) {
					$priority_loading['pl_ready'][]    = $r['pl_ready_qty'];
					$priority_loading['pl_spare'][]    = $r['pl_spare_qty'];
					$priority_loading['supplier'][]    = $r['supplier_name'];
				}

				// Loading List
				$loading_list = [
					"ll_ready"    			=> [],
					"ll_spare"    			=> [],

					"lo_loading_qty"		=> [],
					"lo_official_qty"		=> [],

					"lo_total_rmb"			=> [],
					"lo_total_usd"			=> [],

					"supplier" 					=> [],
					"supplier_id"				=> [],
				];

				$sql = "
					SELECT
						pop.supplier_id,
						COALESCE(s.name, '') AS supplier_name,
						SUM(pop.loading_qty) AS lo_loading_qty,
						SUM(pop.official_ci_qty) AS lo_official_qty,
						SUM(pop.total_amount_rmb) AS lo_total_rmb,
						SUM(pop.total_amount_usd) AS lo_total_usd,
						SUM(CASE WHEN pop.product_type = 'spare' THEN pop.loading_qty ELSE 0 END) AS ll_spare_qty,
						SUM(CASE WHEN pop.product_type = 'spare' THEN 0 ELSE pop.loading_qty END) AS ll_ready_qty
					FROM loading_po_product pop
					LEFT JOIN supplier s ON s.id = pop.supplier_id
					WHERE pop.parent_id = '$id'
					GROUP BY pop.supplier_id, s.name
					ORDER BY pop.id
				";

				$rows = $this->db->query($sql)->result_array();
				foreach ($rows as $r) {
					$loading_list['ll_ready'][]    = $r['ll_ready_qty'];
					$loading_list['ll_spare'][]    = $r['ll_spare_qty'];
					$loading_list['supplier'][] = $r['supplier_name'];
					$loading_list['lo_loading_qty'][] = $r['lo_loading_qty'];
					// $loading_list['lo_official_qty'][] = $r['lo_official_qty'];
					$loading_list['lo_total_rmb'][] = number_format($r['lo_total_rmb'], 2);
					// $loading_list['lo_total_usd'][] = number_format($r['lo_total_usd'], 2);
					$loading_list['supplier_id'][] = $r['supplier_id'];
				}

				foreach ($loading_list["supplier_id"] as $l) {
					$sql = "
						SELECT
							pop.supplier_id,
							SUM(pop.total_amount_usd) AS lo_total_usd,
							SUM(pop.official_ci_qty) AS lo_official_qty
						FROM loading_po_product pop
						WHERE pop.parent_id = '$id' AND pop.invoice_supplier_id = '$l'
						GROUP BY pop.parent_id
						ORDER BY pop.id
					";

					$rows = $this->db->query($sql);
					if($rows->num_rows() > 0){
						$row = $rows->row_array();
						$loading_list['lo_total_usd'][] = number_format($row['lo_total_usd'], 2);
						$loading_list['lo_official_qty'][] = $row['lo_official_qty'];
					} else {
						$loading_list['lo_total_usd'][] = '-';
						$loading_list['lo_official_qty'][] = '-';
					}
				}

				// Status
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
				$purchase_in_edit_url = "showLargeModal('" . base_url() . "modal/popup_inventory/po_purchase_in_modal/" . $id . "','Purchase In & Customs - " . $item['voucher_no'] . "')";
				$purchase_in_view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/po_purchase_in_view_modal/" . $id . "','View Purchase In - " . $item['voucher_no'] . "')";
				if ($delivery_status == 'loading') {
					$loading_list_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="' . base_url() . 'inventory/loading_list_po/download_invoice/' . $item['id'] . '" class="dropdown-item" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Invoice</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $loading_list_edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit Loading List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $loading_list_view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Loading List</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $delete_loading_list_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete Loading List</a>
							<a href="javascript:void(0)" class="dropdown-item d-none" onclick="' . $move_to_purchase_in_url . '"><i class="fa fa-check" aria-hidden="true"></i> Move to Purchase In</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $purchase_in_edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i>Purchase In</a>
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

				// Purchase In Action
				$purchase_in_action ='-';
				$is_locked = !empty($item['is_locked']);
				$lock_po_url = "confirm_modal('" . base_url() . "inventory/purchase_order/lock_po/" . $id . "','Are you sure want to lock this PO!')";
				if ($delivery_status == 'purchase_in' && !$is_locked) {
					$purchase_in_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $purchase_in_view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Purchase In</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $purchase_in_edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit Purchase In</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="revertPurchaseIn(' . $id . ')"><i class="fa fa-undo" aria-hidden="true"></i> Revert Stock In</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $lock_po_url . '"><i class="fa fa-lock" aria-hidden="true"></i> Lock PO</a>
						</div>
					</div>';
				} elseif ($delivery_status == 'purchase_in' && $is_locked) {
					$purchase_in_action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $purchase_in_view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Purchase In</a>
						</div>
					</div>';
				} else {
					$purchase_in_action ='';
				}

				$data[] = array(
					"sr_no"       						=> ++$start,
					"id"          						=> $item['id'],
					"date"       							=> date('d M, Y', strtotime($item['date'])) . ' - ' . $item['voucher_no'],
					"boe_no"									=> $item['boe_no'],
					"boe_date"								=> date('d M, Y', strtotime($item['boe_date'])),
					"arrival_date"								=> date('d M, Y', strtotime($item['expected_date'])),
					"expected_arrival_date"								=> date('d M, Y', strtotime($item['arrival_date'])),
					"delivery_date"       		=> date('d M, Y', strtotime($delivery_date)),
					"suppliers"        				=> array_to_list($po['supplier']),
					"spare_parts_count"       => array_to_list($po['spare']),
					"ready_goods_count"       => array_to_list($po['ready']),
					"pl_suppliers"						=> array_to_list($priority_loading['supplier']),
					"pl_spare_parts_count"		=> array_to_list($priority_loading['pl_spare']),
					"pl_ready_goods_count"		=> array_to_list($priority_loading['pl_ready']),
					"ll_spare_parts_count"		=> array_to_list($loading_list['ll_ready']),
					"ll_ready_goods_count"		=> array_to_list($loading_list['ll_spare']),
					"lo_suppliers"						=> array_to_list($loading_list['supplier']),
					"loading_qty"							=> array_to_list($loading_list['lo_loading_qty']),
					"official_qty"						=> array_to_list($loading_list['lo_official_qty']),
					"total_rmb"								=> array_to_list($loading_list['lo_total_rmb']),
					"total_usd"								=> array_to_list($loading_list['lo_total_usd']),
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

	public function get_pending_purchase_order()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];
		$company_id = $this->session->userdata('company_id');
		$type = $_REQUEST['type'] ?? 'order';

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if ($type === 'product') {
			if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
				$keyword        = $filter_data['keywords'];
				$keyword_filter .= " AND (po.voucher_no like '%" . $keyword . "%' OR pop.product_name like '%" . $keyword . "%' OR pop.item_code like '%" . $keyword . "%')";
			endif;

			$keyword_filter .= " AND (po.delivery_status = 'purchase_in')";
			$keyword_filter .= " AND (po.company_id = '$company_id')";

			if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
				$added_date = explode(' - ', $_REQUEST['date_range']);
				$from =  date('Y-m-d', strtotime($added_date[0]));
				$to =  date('Y-m-d', strtotime($added_date[1]));
				if ($from == $to) {
					$keyword_filter .= " AND (DATE(po.date) = '$from')";
				} else {
					$keyword_filter .= " AND (DATE(po.date) BETWEEN '$from' AND '$to')";
				}
			}

			$sql_base = "FROM purchase_order_product pop
			INNER JOIN purchase_order po ON po.id = pop.parent_id
			WHERE po.is_deleted = '0' AND po.method = 'import' $keyword_filter 
			AND (
				SELECT COALESCE(SUM(pip.actual_qty), 0) 
				FROM purchase_in_product pip 
				WHERE pip.parent_id = pop.parent_id 
				AND pip.product_id = pop.product_id 
				AND pip.supplier_id = pop.supplier_id 
				AND pip.is_deleted = '0'
			) < pop.quantity";

			$total_count = $this->db->query("SELECT pop.id $sql_base")->num_rows();
			$query = $this->db->query("
				SELECT 
					pop.id,
					pop.parent_id,
					pop.product_name,
					pop.item_code,
					po.voucher_no,
					po.date AS po_date,
					pop.quantity AS total_qty,
					(
						SELECT COALESCE(SUM(pip.actual_qty), 0)
						FROM purchase_in_product pip
						WHERE pip.parent_id = pop.parent_id
						  AND pip.product_id = pop.product_id
						  AND pip.supplier_id = pop.supplier_id
						  AND pip.is_deleted = '0'
					) AS received_qty
				$sql_base 
				ORDER BY pop.id DESC 
				LIMIT $start, $length
			");

			if (!empty($query)) {
				foreach ($query->result_array() as $item) {
					$total_qty = (int)$item['total_qty'];
					$received_qty = (int)$item['received_qty'];
					$remaining_qty = $total_qty - $received_qty;

					$data[] = array(
						"sr_no"          => ++$start,
						"batch_no"       => date('d M, Y', strtotime($item['po_date'])) . ' - ' . $item['voucher_no'],
						"product_name"   => $item['product_name'] . ' (' . $item['item_code'] . ')',
						"total_qty"      => $total_qty,
						"received_qty"   => $received_qty,
						"remaining_qty"  => $remaining_qty,
					);
				}
			}
		} else {
			if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
				$keyword        = $filter_data['keywords'];
				$keyword_filter .= " AND (voucher_no like '%" . $keyword . "%')";
			endif;

			$keyword_filter .= " AND (delivery_status = 'purchase_in')";
			$keyword_filter .= " AND (company_id = '$company_id')";

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

			$sql_base = "FROM purchase_order WHERE (is_deleted='0') AND method = 'import' $keyword_filter 
			AND EXISTS (
				SELECT 1 FROM purchase_order_product pop 
				WHERE pop.parent_id = purchase_order.id 
				AND (
					SELECT COALESCE(SUM(pip.actual_qty), 0) 
					FROM purchase_in_product pip 
					WHERE pip.parent_id = purchase_order.id 
					AND pip.product_id = pop.product_id 
					AND pip.supplier_id = pop.supplier_id 
					AND pip.is_deleted = '0'
				) < pop.quantity
			)";

			$total_count = $this->db->query("SELECT id $sql_base ORDER BY id ASC")->num_rows();
			$query = $this->db->query("SELECT id, delivery_status, voucher_no, date, delivery_date $sql_base ORDER BY id DESC LIMIT $start, $length");

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
						ORDER BY pop.id
					";

					$rows = $this->db->query($sql)->result_array();
					foreach ($rows as $r) {
						$po['ready'][]    = $r['ready_qty'];
						$po['spare'][]    = $r['spare_qty'];
						$po['supplier'][] = $r['supplier_name'];
					}

					// PO Action
					$action ='-';
					$export_excel_url="generate_excel('".$id."')";
					$view_po_details_url = "showLargeModal('" . base_url() . "modal/popup_inventory/modal_purchase_order_details/" . $id . "','PO Details - " . $item['voucher_no'] . "')";
					$show_pending_product_url = "showLargeModal('" . base_url() . "modal/popup_inventory/modal_pending_products/" . $id . "','Pending Products - " . $item['voucher_no'] . "')";
					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $export_excel_url . '"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export PO</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_po_details_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View PO Details</a>
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $show_pending_product_url . '"><i class="fa fa-list-ul" aria-hidden="true"></i> Show Pending Product</a>
						</div>
					</div>';

					$data[] = array(
						"sr_no"             => ++$start,
						"id"                => $item['id'],
						"date"              => date('d M, Y', strtotime($item['date'])) . ' - ' . $item['voucher_no'],
						"delivery_date"     => date('d M, Y', strtotime($delivery_date)),
						"suppliers"         => array_to_list($po['supplier']),
						"spare_parts_count" => array_to_list($po['spare']),
						"ready_goods_count" => array_to_list($po['ready']),
						"action"            => $action,
					);
				}
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
		SELECT p.notes, po.supplier_id, po.is_priority, po.item_code, po.categories, po.product_name, po.quantity, po.cbm, po.total_cbm FROM purchase_order as p INNER JOIN po_products as po ON p.id = po.parent_id WHERE p.id='$id' GROUP BY po.id ORDER BY po.sort ASC, po.is_priority DESC");

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
		$company_id = $this->session->userdata('company_id');

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";
		$keyword_filter1 = "";
		$keyword_filter2 = "";
		$supplier_id = isset($_REQUEST['supplier_id']) ? $_REQUEST['supplier_id'] : '';

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter1 .= " AND (voucher_no like '%" . $keyword . "%')";
			$keyword_filter2 .= " AND (batch_no like '%" . $keyword . "%')";
		endif;

		$keyword_filter .= " AND (company_id = '$company_id')";

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

		// if (!empty($supplier_id)) {
		// 	$keyword_filter .= " AND EXISTS (SELECT 1 FROM purchase_order_product WHERE parent_id = purchase_order.id AND supplier_id = '" . $supplier_id . "')";
		// }

		$total_count = $this->db->query("
			SELECT * FROM (
				(
					SELECT 
						id
					FROM purchase_order 
					WHERE (is_deleted='0') AND method = 'local' " . $keyword_filter . "
				)
				UNION ALL 
			 	(
					SELECT 
						id
					FROM po_expense 
					WHERE is_delete = '0' " . $keyword_filter . "
				)
			) AS u 
			ORDER BY id DESC")->num_rows();

		$query = $this->db->query("
			SELECT * FROM (
				(
					SELECT 
						id, grand_total, voucher_no, date, method as type, supplier_id as vendor_id, 'po' as query
					FROM purchase_order 
					WHERE (is_deleted='0') AND method = 'local' " . $keyword_filter . $keyword_filter1 . "
				)
				UNION ALL 
			 	(
					SELECT 
						id, grand_total, batch_no as voucher_no, expense_date as date, type, vendor_id, 'expense' as query 
					FROM po_expense 
					WHERE is_delete = '0' " . $keyword_filter . $keyword_filter2 . "
				)
			) AS u 
			ORDER BY id DESC 
			LIMIT $start, $length");

		

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				if($item['query'] == 'po') {
					// Get totals and supplier list
					$sql = "
						SELECT
							pop.supplier_id,
							COALESCE(s.name, '') AS supplier_name,
							COUNT(DISTINCT pop.product_id) AS total_products,
							SUM(pop.quantity) AS total_qty
						FROM purchase_order_product pop
						LEFT JOIN supplier s ON s.id = pop.supplier_id
						WHERE pop.parent_id = '$id'
						GROUP BY pop.supplier_id, s.name
						ORDER BY pop.id
					";

					$rows = $this->db->query($sql)->result_array();
					$suppliers = array();
					$total_products = 0;
					$total_qty = 0;

					foreach ($rows as $r) {
						if (!empty($r['supplier_name'])) {
							$suppliers[] = $r['supplier_name'];
						}
						$total_products += (int)$r['total_products'];
						$total_qty += (int)$r['total_qty'];
					}

					$type = 'Local PO';

					// Actions
					$view_po_details_url = "showLargeModal('" . base_url() . "modal/popup_inventory/modal_purchase_order_details/" . $id . "','PO Details - " . $item['voucher_no'] . "')";

					$action = '<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
							<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_po_details_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View PO Details</a>
						</div>
					</div>';
				} else {
					$suppliers = [$this->common_model->selectByidParam($item['vendor_id'], 'my_companies', 'name') ?? ''];
					$total_products = 0;
					$total_qty = 0;
					
					$type = 'Expense';
					$action = '-';
				} 

				$data[] = array(
					"sr_no"             => ++$start,
					"id"                => $item['id'],
					"type"              => $type,
					"date"              => date('d M, Y', strtotime($item['date'])) . ' - ' . $item['voucher_no'],
					"suppliers"         => !empty($suppliers) ? array_to_list($suppliers) : '-',
					"total_products"    => $total_products,
					"total_quantity"    => $total_qty,
					"total_amount"    	=> number_format($item['grand_total'], 2),
					"po_date"           => date('d M, Y', strtotime($item['date'])),
					"action"            => $action
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

	public function delete_purchase_report($id)
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

	public function update_purchase_order_in(){
		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('stock_in_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		// Start transaction
		$this->db->trans_start();

		$po_id = $this->input->post('po_id');
		$po_row = $this->common_model->getRowById('purchase_order', '*', ['id' => $po_id]);

		if (!empty($po_row['is_locked'])) {
			$resultpost = array(
				"status" => 400,
				"message" => 'This PO is locked and cannot be edited.',
				"url" => $this->session->userdata('previous_url'),
			);
			return simple_json_output($resultpost);
		}

		$inr_rate = $this->input->post('inr_rate');
		$boe_no = $this->input->post('boe_no');
		$boe_date = $this->input->post('boe_date');

		$is_edit = ($po_row['delivery_status'] == 'purchase_in');
		$old_log_data = $is_edit ? $this->get_complete_purchase_in_log_data($po_id) : null;
		
		$po = [
			"inr_rate" => $inr_rate,
			"boe_no" => $boe_no,
			"boe_date" => $boe_date,
			"delivery_status" => 'purchase_in',
			"net_sales_value_1" => 0,
			"transport_gst_amount" => 0,
			"grand_total" => 0,
		];

		if (!$is_edit) {
			$po["completed_date"] = date("Y-m-d H:i:s");
		}
		
		$stock_in_date = ($is_edit && !empty($po_row['completed_date'])) ? $po_row['completed_date'] : date("Y-m-d H:i:s");
		
		// base key list (must exist)
		$row_ids = $this->input->post('row_id'); // array
		$product_name      = $this->input->post('product_name');
		$item_code         = $this->input->post('item_code');
		$actual_qty        = $this->input->post('actual_qty');
		$actual_rmb        = $this->input->post('actual_rmb');
		$total_rmb         = $this->input->post('total_rmb');
		$actual_usd        = $this->input->post('actual_usd');
		$actual_inr        = $this->input->post('actual_inr');
		$official_qty      = $this->input->post('official_qty');
		$official_rate_rs  = $this->input->post('official_rate_rs');
		$official_total_rs = $this->input->post('official_total_rs');
		$duty_percent      = $this->input->post('duty_percent');
		$duty_amt          = $this->input->post('duty_amt');
		$duty_surcharge    = $this->input->post('duty_surcharge');
		$taxable_value     = $this->input->post('taxable_value');
		$gst_amt           = $this->input->post('gst_amt');
		$total_amt         = $this->input->post('total_amt');
		$invoice_no        = $this->input->post('invoice_no');

		$product_ids       = $this->input->post('product_id');
		$supplier_ids      = $this->input->post('supplier_id_row');

		$supplier_currency_type    = $this->input->post('supplier_currency_type');
		$supplier_rmb_usd_con_rate = $this->input->post('supplier_rmb_usd_con_rate');
		$supplier_inr_con_rate     = $this->input->post('supplier_inr_con_rate');

		$replace_qty       = $this->input->post('replace_qty');
		$replace_recv_qty  = $this->input->post('replace_recv_qty');

		$rows = [];
		$reverted_products = [];

		if (is_array($row_ids)) {
				foreach ($row_ids as $i => $row_id) {
						$row_id = (int) $row_id;
						
						if ($row_id == 0) {
							// New product added from Purchase In modal
							$p_id = $product_ids[$i];
							$s_id = $supplier_ids[$i];
							$raw_p = $this->common_model->getRowById('raw_products', '*', ['id' => $p_id]);
							
							$po_prod_row = [
								'parent_id' => $po_id,
								'supplier_id' => $s_id,
								'product_id' => $p_id,
								'categories' => $raw_p['categories'] ?? '',
								'product_name' => $product_name[$i] ?? '',
								'item_code' => $item_code[$i] ?? '',
								'hsn_code' => $raw_p['hsn_code'] ?? '',
								'unit' => $raw_p['unit'] ?? '',
								'cartoon' => $raw_p['cartoon_qty'] ?? 0,
								'rate' => $raw_p['product_mrp'] ?? 0,
								'basic_amount' => $raw_p['costing_price'] ?? 0,
								'unit_price_rmb' => $raw_p['rate'] ?? 0,
								'actual_usd' => $raw_p['actual_usd_rate'] ?? 0,
								'official_ci_unit_price_usd' => $raw_p['usd_rate'] ?? 0,
								'is_priority' => 0,
								'loading_list_qty' => 0,
								'quantity' => 0,
								'pending' => 0,
								'black_qty' => 0,
								'actual_qty' => 0
							];
						} else {
							$source_table = $is_edit ? 'purchase_in_product' : 'loading_po_product';
							$po_prod_row = $this->common_model->getRowById($source_table, '*', ['id' => $row_id]);
						}

						// PO total
						$po["net_sales_value_1"]  = $po["net_sales_value_1"] + ($taxable_value[$i] ?? 0);
						$po["transport_gst_amount"]  = $po["transport_gst_amount"] + ($gst_amt[$i] ?? 0);
						$po["grand_total"]  = $po["grand_total"] + ($total_amt[$i] ?? 0);

						$act_q = (int) ($actual_qty[$i] ?? 0);
						$off_q = (int) ($official_qty[$i] ?? 0);
						$black_qty_val = ($act_q > $off_q) ? ($act_q - $off_q) : 0;

						$rep_q = (int) ($replace_qty[$i] ?? 0);
						$rep_recv_q = (int) ($replace_recv_qty[$i] ?? 0);

						if ($rep_recv_q > $act_q) {
							$rep_recv_q = $act_q;
						}
						if ($rep_recv_q > $rep_q) {
							$rep_recv_q = $rep_q;
						}
						if ($rep_recv_q < 0) {
							$rep_recv_q = 0;
						}

						$row_supplier_id = (int) ($supplier_ids[$i] ?? ($po_prod_row['supplier_id'] ?? 0));
						$row_currency_type = 'usd';
						if (is_array($supplier_currency_type) && isset($supplier_currency_type[$row_supplier_id]) && in_array($supplier_currency_type[$row_supplier_id], ['usd', 'rmb'], true)) {
							$row_currency_type = $supplier_currency_type[$row_supplier_id];
						}
						$row_rmb_usd_con_rate = (is_array($supplier_rmb_usd_con_rate) && isset($supplier_rmb_usd_con_rate[$row_supplier_id]))
							? (float) $supplier_rmb_usd_con_rate[$row_supplier_id]
							: 0;
						$row_inr_con_rate = (is_array($supplier_inr_con_rate) && isset($supplier_inr_con_rate[$row_supplier_id]))
							? (float) $supplier_inr_con_rate[$row_supplier_id]
							: 0;

						// PO Prod Update
						$po_prods = [
								'actual_qty'        => $act_q,
								'currency_type'     => $row_currency_type,
								'rmb_usd_con_rate'  => $row_rmb_usd_con_rate,
								'inr_con_rate'      => $row_inr_con_rate,
								'actual_rmb'        => (float) ($actual_rmb[$i] ?? 0),
								'total_rmb'         => (float) ($total_rmb[$i] ?? 0),
								'actual_usd'        => (float) ($actual_usd[$i] ?? 0),
								'actual_inr'        => (float) ($actual_inr[$i] ?? 0),
								'official_rate_rs'  => (float) ($official_rate_rs[$i] ?? 0),
								'official_total_rs' => (float) ($official_total_rs[$i] ?? 0),
								'duty_percent'      => (float) ($duty_percent[$i] ?? 0),
								'duty_amt'          => (float) ($duty_amt[$i] ?? 0),
								'duty_surcharge'    => (float) ($duty_surcharge[$i] ?? 0),
								'taxable_value'     => (float) ($taxable_value[$i] ?? 0),
								'gst_amt'           => (float) ($gst_amt[$i] ?? 0),
								'total_amt'         => (float) ($total_amt[$i] ?? 0),
								'invoice_no'        => $invoice_no[$i] ?? 1,
								'black_qty'         => $black_qty_val,
								'receivable_qty'    => $rep_q,
								'received_qty'      => $rep_recv_q,
								'is_replace'        => ($rep_q > 0) ? 1 : 0,
						];

						if ($is_edit && $row_id != 0) {
							$this->db->where('id', $row_id)->update('purchase_in_product', $po_prods);
							$pip_id = $row_id;
						} else {
							$insert_data = array_merge($po_prod_row, $po_prods);
							if (isset($insert_data['id'])) unset($insert_data['id']);
							$this->db->insert('purchase_in_product', $insert_data);
							$pip_id = $this->db->insert_id();
						}

						if ($is_edit && !in_array($product_ids[$i], $reverted_products)) {
							// Revert any previously received replacement products for this PO and product
							$this->common_model->revert_replace_products($po_id, $product_ids[$i], 'received');
							$reverted_products[] = $product_ids[$i];
						}

						if ($rep_recv_q > 0) {
							$this->common_model->update_replace_product('loading', $po_id, $product_ids[$i], $rep_recv_q);
						}

						// Overflow management
						$existing_overflow = $this->db->get_where('purchase_overflow_product', ['parent_id' => $pip_id])->row_array();

						if ($act_q >= $off_q) {
							if ($existing_overflow) {
								$this->db->where('id', $existing_overflow['id'])->delete('purchase_overflow_product');
							}
						} else {
							$overflow_qty = $off_q - $act_q;
							$pip_row = $this->db->get_where('purchase_in_product', ['id' => $pip_id])->row_array();

							if ($existing_overflow) {
								$update_overflow_data = $pip_row;
								unset($update_overflow_data['id']);
								unset($update_overflow_data['parent_id']);
								unset($update_overflow_data['is_complete']);
								unset($update_overflow_data['is_deleted']);
								$update_overflow_data['quantity'] = $overflow_qty;

								$this->db->where('id', $existing_overflow['id'])->update('purchase_overflow_product', $update_overflow_data);
							} else {
								$overflow_data = $pip_row;
								unset($overflow_data['id']);
								$overflow_data['parent_id'] = $pip_id;
								$overflow_data['quantity'] = $overflow_qty;

								$this->db->insert('purchase_overflow_product', $overflow_data);
							}
						}

						// Inventory In
						$inv = [
							'company_id' 				=> $po_row["company_id"],
							'warehouse_id' 			=> $po_row["warehouse_id"],
							'supplier_id' 			=> $po_prod_row["supplier_id"],
							'warehouse_name' 		=> $po_row["warehouse_name"],
							'product_id' 				=> $po_prod_row["product_id"],
							'categories' 				=> $po_prod_row["categories"],
							'batch_no' 					=> $po_row["voucher_no"],
							'po_row_id'					=> $pip_id,
							'product_name'			=> $product_name[$i]      ?? '',
							'item_code'					=> $item_code[$i]         ?? '',
							'sku'         			=> $item_code[$i]         ?? '',
							'quantity'        	=> (int) ($actual_qty[$i] ?? 0),

							'actual_rmb'        => (float) ($actual_rmb[$i] ?? 0),
							'total_rmb'         => (float) ($total_rmb[$i] ?? 0),
							'actual_usd'        => (float) ($actual_usd[$i] ?? 0),
							'actual_inr'        => (float) ($actual_inr[$i] ?? 0),
							'official_qty'      => (int) ($official_qty[$i] ?? 0),
							'official_rate_rs'  => (float) ($official_rate_rs[$i] ?? 0),
							'official_total_rs' => (float) ($official_total_rs[$i] ?? 0),
							'black_qty'         => $black_qty_val,
							'duty_percent'      => (float) ($duty_percent[$i] ?? 0),
							'duty_amt'          => (float) ($duty_amt[$i] ?? 0),
							'duty_surcharge'    => (float) ($duty_surcharge[$i] ?? 0),
							'taxable_value'     => (float) ($taxable_value[$i] ?? 0),
							'gst_amt'           => (float) ($gst_amt[$i] ?? 0),
							'total_amt'         => (float) ($total_amt[$i] ?? 0),	
						];

						// Usage Check for Edit Mode
						$skip_inventory_update = false;
						if ($is_edit) {
							$current_inv_row = $this->db->get_where('inventory', ['po_row_id' => $pip_id])->row_array();
							if (!$current_inv_row) {
								$current_inv_row = $this->db->get_where('inventory', [
									'product_id' => $po_prod_row["product_id"],
									'warehouse_id' => $po_row["warehouse_id"],
									'company_id' => $po_row["company_id"],
									'batch_no' => $po_row["voucher_no"]
								])->row_array();
							}

							if ($current_inv_row && (int)$current_inv_row['quantity'] != (int)$po_prod_row['actual_qty']) {
								$skip_inventory_update = true;
							}
						}

						if (!$skip_inventory_update) {
							if ($is_edit) {
								$check_inv = $this->db->get_where('inventory', ['po_row_id' => $pip_id])->row_array();
								if (!$check_inv) {
									$check_inv = $this->db->get_where('inventory', [
										'product_id' => $po_prod_row["product_id"],
										'warehouse_id' => $po_row["warehouse_id"],
										'company_id' => $po_row["company_id"],
										'batch_no' => $po_row["voucher_no"]
									])->row_array();
								}
							} else {
								$check_inv = "";
							}

							if(empty($check_inv)) {
								$this->db->insert('inventory', $inv);
								$inventory_id = $this->db->insert_id();

								// Inventory History
								$inv_his = [
									'supplier_id' 			=> $po_prod_row["supplier_id"],
									'parent_id' 				=> $inventory_id,
									'company_id' 				=> $po_row["company_id"],
									'warehouse_id' 			=> $po_row["warehouse_id"],
									'warehouse_name' 		=> $po_row["warehouse_name"],
									'product_id' 				=> $po_prod_row["product_id"],
									'categories' 				=> $po_prod_row["categories"],
									'batch_no' 					=> $po_row["voucher_no"],
									'product_name'			=> $product_name[$i] ?? '',
									'item_code'					=> $item_code[$i] ?? '',
									'sku'         			=> $item_code[$i] ?? '',
									'order_id'        	=> $po_id,
									'status'        		=> $is_edit ? 'in' : 'in',
									'quantity'        	=> (int) ($actual_qty[$i] ?? 0),

									'actual_rmb'        => (float) ($actual_rmb[$i] ?? 0),
									'total_rmb'         => (float) ($total_rmb[$i] ?? 0),
									'actual_usd'        => (float) ($actual_usd[$i] ?? 0),
									'actual_inr'        => (float) ($actual_inr[$i] ?? 0),
									'official_qty'      => (int) ($official_qty[$i] ?? 0),
									'official_rate_rs'  => (float) ($official_rate_rs[$i] ?? 0),
									'official_total_rs' => (float) ($official_total_rs[$i] ?? 0),
									'black_qty'         => $black_qty_val,
									'duty_percent'      => (float) ($duty_percent[$i] ?? 0),
									'duty_amt'          => (float) ($duty_amt[$i] ?? 0),
									'duty_surcharge'    => (float) ($duty_surcharge[$i] ?? 0),
									'taxable_value'     => (float) ($taxable_value[$i] ?? 0),
									'gst_amt'           => (float) ($gst_amt[$i] ?? 0),
									'total_amt'         => (float) ($total_amt[$i] ?? 0),
									
									'received_date'       => date('Y-m-d', strtotime($stock_in_date)),
									'invoice_no'         	=> $invoice_no[$i] ?? 1,
									'added_date'         	=> $stock_in_date,
									"added_by_id"         => $this->session->userdata('super_user_id'),
									"added_by_name"       => $this->session->userdata('super_name'),
								];

								$this->db->insert('inventory_history', $inv_his);
							} else {
								// In Edit Mode, we OVERWRITE the existing batch quantity/costs
								$updated_inv = $inv;
								unset($updated_inv['company_id'], $updated_inv['warehouse_id'], $updated_inv['product_id'], $updated_inv['batch_no']);

								$this->db->where('id', $check_inv['id'])->update('inventory', $updated_inv);
								$inventory_id = $check_inv['id'];

								// Inventory History
								$inv_his = [
									'supplier_id' 				=> $po_prod_row["supplier_id"],
									'parent_id' 					=> $inventory_id,
									'company_id' 					=> $po_row["company_id"],
									'warehouse_id' 				=> $po_row["warehouse_id"],
									'warehouse_name' 			=> $po_row["warehouse_name"],
									'product_id' 					=> $po_prod_row["product_id"],
									'categories' 					=> $po_prod_row["categories"],
									'batch_no' 						=> $po_row["voucher_no"],
									'product_name'				=> $product_name[$i] ?? '',
									'item_code'						=> $item_code[$i] ?? '',
									'sku'         				=> $item_code[$i] ?? '',
									'order_id'        		=> $po_id,
									'status'        			=> $is_edit ? 'in' : 'in',
									'quantity'        		=> (int) ($actual_qty[$i] ?? 0),

									'actual_rmb'        	=> (float) ($actual_rmb[$i] ?? 0),
									'total_rmb'         	=> (float) ($total_rmb[$i] ?? 0),
									'actual_usd'        	=> (float) ($actual_usd[$i] ?? 0),
									'actual_inr'        	=> (float) ($actual_inr[$i] ?? 0),
									'official_qty'      	=> (int) ($official_qty[$i] ?? 0),
									'official_rate_rs'  	=> (float) ($official_rate_rs[$i] ?? 0),
									'official_total_rs' 	=> (float) ($official_total_rs[$i] ?? 0),
									'black_qty'         	=> $black_qty_val,
									'duty_percent'      	=> (float) ($duty_percent[$i] ?? 0),
									'duty_amt'          	=> (float) ($duty_amt[$i] ?? 0),
									'duty_surcharge'    	=> (float) ($duty_surcharge[$i] ?? 0),
									'taxable_value'     	=> (float) ($taxable_value[$i] ?? 0),
									'gst_amt'           	=> (float) ($gst_amt[$i] ?? 0),
									'total_amt'         	=> (float) ($total_amt[$i] ?? 0),	

									'invoice_no'         	=> $invoice_no[$i] ?? 1,	
								];

								$this->db->where('parent_id', $check_inv['id'])->update('inventory_history', $inv_his);
							}
						}
				}
		}

		// Process supplier expense and extra amount (payments)
		$session_company_id = $this->session->userdata('company_id');
		$session_user_id    = $this->session->userdata('super_user_id');
		$batch_no           = $po_row['voucher_no'] ?? '';

		$supplier_expense_usd = (array) $this->input->post('supplier_expense_usd');
		$supplier_expense_rmb = (array) $this->input->post('supplier_expense_rmb');
		$supplier_expense_inr = (array) $this->input->post('supplier_expense_inr');

		$supplier_extra_usd   = (array) $this->input->post('supplier_extra_usd');
		$supplier_extra_rmb   = (array) $this->input->post('supplier_extra_rmb');
		$supplier_extra_inr   = (array) $this->input->post('supplier_extra_inr');

		$all_suppliers = array_unique(array_filter(array_merge(
			array_keys($supplier_expense_usd),
			array_keys($supplier_expense_rmb),
			array_keys($supplier_expense_inr),
			array_keys($supplier_extra_usd),
			array_keys($supplier_extra_rmb),
			array_keys($supplier_extra_inr)
		)));

		foreach ($all_suppliers as $supplier_id) {
			if (empty($supplier_id)) continue;

			$exp_usd = (float) ($supplier_expense_usd[$supplier_id] ?? 0);
			$exp_rmb = (float) ($supplier_expense_rmb[$supplier_id] ?? 0);
			$exp_inr = (float) ($supplier_expense_inr[$supplier_id] ?? 0);

			$extra_usd = (float) ($supplier_extra_usd[$supplier_id] ?? 0);
			$extra_rmb = (float) ($supplier_extra_rmb[$supplier_id] ?? 0);
			$extra_inr = (float) ($supplier_extra_inr[$supplier_id] ?? 0);

			// 1. Handle po_expense table
			$existing_exp = $this->db->get_where('po_expense', [
				'type'        => 'extras',
				'batch_no'    => $batch_no,
				'supplier_id' => $supplier_id,
				'is_delete'   => 0
			])->row_array();

			if ($existing_exp) {
				$exp_update_data = [
					'usd'          => $exp_usd,
					'rmb'          => $exp_rmb,
					'sub_total'    => $exp_inr,
					'grand_total'  => $exp_inr,
					'expense_date' => date('Y-m-d'),
				];
				$this->db->where('id', $existing_exp['id'])->update('po_expense', $exp_update_data);
			} else {
				if ($exp_usd != 0 || $exp_rmb != 0 || $exp_inr != 0) {
					$exp_insert_data = [
						'input_method'  => 'import',
						'company_id'    => $session_company_id,
						'type'          => 'extras',
						'expense_type'  => 0,
						'vendor_id'     => 0,
						'supplier_id'   => $supplier_id,
						'purchase_date' => date('Y-m-d H:i:s'),
						'usd'           => $exp_usd,
						'rmb'           => $exp_rmb,
						'batch_no'      => $batch_no,
						'expense_date'  => date('Y-m-d'),
						'gst_type'      => '',
						'sub_total'     => $exp_inr,
						'grand_total'   => $exp_inr,
						'added_by_id'   => $session_user_id,
					];
					$this->db->insert('po_expense', $exp_insert_data);
				}
			}

			// 2. Handle payments table
			$existing_pay = $this->db->get_where('payments', [
				'payment_type' => 'extras',
				'batch_no'     => $batch_no,
				'supplier_id'  => $supplier_id,
				'is_delete'    => 0
			])->row_array();

			if ($existing_pay) {
				$pay_update_data = [
					'amount_dollar' => $extra_usd,
					'amount_rs'     => $extra_inr,
					'amount_rmb'    => $extra_rmb,
					'payment_date'  => date('Y-m-d'),
				];
				$this->db->where('id', $existing_pay['id'])->update('payments', $pay_update_data);
			} else {
				if ($extra_usd != 0 || $extra_rmb != 0 || $extra_inr != 0) {
					$sup_row = $this->db->get_where('supplier', ['id' => $supplier_id])->row_array();
					$supplier_name = $sup_row['name'] ?? '';

					$pay_insert_data = [
						'company_id'    => $session_company_id,
						'supplier_id'   => $supplier_id,
						'supplier_name' => $supplier_name,
						'invoice_no'    => 'extra income',
						'batch_no'      => $batch_no,
						'amount_dollar' => $extra_usd,
						'amount_rs'     => $extra_inr,
						'amount_rmb'    => $extra_rmb,
						'payment_type'  => 'extras',
						'payment_date'  => date('Y-m-d'),
						'added_by'      => $session_user_id,
					];
					$this->db->insert('payments', $pay_insert_data);
				}
			}
		}

		$this->db->where('id', $po_id)->update('purchase_order', $po);
		if ($this->db->trans_status() === FALSE) {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('some_error_occured'),
			);

			return simple_json_output($resultpost);
		} else {
			$this->db->trans_complete();

			// Insert audit log
			$new_log_data = $this->get_complete_purchase_in_log_data($po_id);
			if ($is_edit) {
				$log_json = array(
					'old_data' => $old_log_data,
					'new_data' => $new_log_data
				);
				$action = 'edit';
				$message = 'Purchase In edited by ' . $this->session->userdata('super_name');
			} else {
				$log_json = $new_log_data;
				$action = 'add';
				$message = 'Purchase In added by ' . $this->session->userdata('super_name');
			}
			$log_data = array(
				'parent_id'      => $po_id,
				'ref_id'         => NULL,
				'module'         => 'purchase_in',
				'action'         => $action,
				'message'        => $message,
				'json'           => json_encode($log_json),
				'table_name'     => 'purchase_in_product',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			return simple_json_output($resultpost);
		}
	}

	public function lock_purchase_order($id)
	{
		if ($this->session->userdata('inventory_login') != true) {
			echo json_encode(['status' => 400, 'message' => 'Unauthorized']);
			return;
		}

		if (empty($id)) {
			$resultpost = [
				'status' => 400,
				'message' => 'Purchase Order ID is required',
				'url' => $this->session->userdata('previous_url')
			];
			return simple_json_output($resultpost);
		}

		$po = $this->db->get_where('purchase_order', ['id' => $id, 'is_deleted' => 0])->row_array();
		if (!$po) {
			$resultpost = [
				'status' => 400,
				'message' => 'Purchase Order not found',
				'url' => $this->session->userdata('previous_url')
			];
			return simple_json_output($resultpost);
		}

		if (!empty($po['is_locked'])) {
			$resultpost = [
				'status' => 400,
				'message' => 'Purchase Order is already locked',
				'url' => $this->session->userdata('previous_url')
			];
			return simple_json_output($resultpost);
		}

		$this->db->where('id', $id);
		$this->db->update('purchase_order', ['is_locked' => 1]);

		$log_data = array(
			'parent_id'      => $id,
			'ref_id'         => NULL,
			'module'         => 'purchase_order',
			'action'         => 'lock',
			'message'        => 'Purchase Order locked by ' . $this->session->userdata('super_name'),
			'json'           => json_encode($po),
			'table_name'     => 'purchase_order',
			'added_by'       => $this->session->userdata('super_user_id'),
			'added_by_email' => $this->session->userdata('super_email'),
			'added_by_name'  => $this->session->userdata('super_name'),
			'added_by_type'  => $this->session->userdata('super_type')
		);
		$this->db->insert('sys_logs', $log_data);

		$resultpost = [
			'status' => 200,
			'message' => 'Purchase Order locked successfully!',
			'url' => $this->session->userdata('previous_url')
		];

		return simple_json_output($resultpost);
	}

	public function revert_purchase_order_in($po_id) {
		$this->db->trans_start();
		
		$po = $this->db->get_where('purchase_order', ['id' => $po_id])->row_array();
		if (!$po || $po['delivery_status'] != 'purchase_in') {
			return ['status' => 400, 'message' => 'Invalid PO or PO is not in Stock In status.'];
		}

		if (!empty($po['is_locked'])) {
			return ['status' => 400, 'message' => 'This PO is locked and cannot be reverted.'];
		}

		// Capture data before revert for audit log
		$purchase_in_log_data = $this->get_complete_purchase_in_log_data($po_id);

		$batch_no = $po['voucher_no'];
		$warehouse_id = $po['warehouse_id'];
		$company_id = $po['company_id'];

		// 1. Fetch matching products
		$po_products = $this->db->get_where('purchase_in_product', ['parent_id' => $po_id, 'actual_qty >' => 0])->result_array();
		
		if (empty($po_products)) {
			return ['status' => 400, 'message' => 'No products found with actual quantity in this PO.'];
		}

		// Delete related overflow records first
		$all_po_products = $this->db->get_where('purchase_in_product', ['parent_id' => $po_id])->result_array();
		if (!empty($all_po_products)) {
			$pip_ids = array_column($all_po_products, 'id');
			$this->db->where_in('parent_id', $pip_ids)->delete('purchase_overflow_product');
		}

		// 2. Strict validation loop
		foreach ($po_products as $product) {
			$product_id = $product['product_id'];
			$stocked_qty = (int)$product['actual_qty'];

			// Check current inventory for this batch
			$inv = $this->db->get_where('inventory', ['po_row_id' => $product['id']])->row_array();
			if (!$inv) {
				$inv = $this->db->get_where('inventory', [
					'product_id' => $product_id,
					'warehouse_id' => $warehouse_id,
					'company_id' => $company_id,
					'batch_no' => $batch_no
				])->row_array();
			}

			if (!$inv) {
				return [
					'status' => 400, 
					'message' => "Validation Failed: Product [{$product['product_name']}] not found in inventory for batch {$batch_no}."
				];
			}

			if ((int)$inv['quantity'] != $stocked_qty) {
				return [
					'status' => 400, 
					'message' => "Validation Failed: Product [{$product['product_name']}] quantity mismatch. Stocked: {$stocked_qty}, Current Inventory: {$inv['quantity']}. Reversal blocked."
				];
			}
		}

		$reverted_products = [];
		// 3. Execution (If we reach here, all items matched)
		foreach ($po_products as $product) {
			$product_id = $product['product_id'];
			$stocked_qty = (int)$product['actual_qty'];

			// Log History (Stock Out / Reversion)
			$inv_his = [
				'supplier_id' 			=> $product["supplier_id"],
				'company_id' 				=> $company_id,
				'warehouse_id' 			=> $warehouse_id,
				'warehouse_name' 		=> $po["warehouse_name"],
				'product_id' 				=> $product_id,
				'categories' 				=> $product["categories"],
				'batch_no' 					=> $batch_no,
				'product_name'			=> $product["product_name"],
				'item_code'					=> $product["item_code"],
				'sku'         			=> $product["item_code"],
				'order_id'        	=> $po_id,
				'status'        		=> 'Purchase In Reverted', 
				'quantity'        	=> $stocked_qty,
				'actual_rmb'        => $product['actual_rmb'],
				'total_rmb'         => $product['total_rmb'],
				'actual_usd'        => $product['actual_usd'],
				'actual_inr'        => $product['actual_inr'],
				'received_date'			=> date('Y-m-d'),
				'added_date'         	=> date('Y-m-d H:i:s'),
				"added_by_id"       => $this->session->userdata('super_user_id'),
				"added_by_name"     => $this->session->userdata('super_name'),
			];
			$this->db->insert('inventory_history', $inv_his);

			// Delete from Inventory
			$inv_to_delete = $this->db->get_where('inventory', ['po_row_id' => $product['id']])->row_array();
			if (!$inv_to_delete) {
				$inv_to_delete = $this->db->get_where('inventory', [
					'product_id' => $product_id,
					'warehouse_id' => $warehouse_id,
					'company_id' => $company_id,
					'batch_no' => $batch_no
				])->row_array();
			}
			if ($inv_to_delete) {
				$this->db->where('id', $inv_to_delete['id'])->delete('inventory');
			}

			// Revert replacement products
			if (!in_array($product_id, $reverted_products)) {
				$this->common_model->revert_replace_products($po_id, $product_id, 'received');
				$reverted_products[] = $product_id;
			}
		}

		// Delete Purchase In Products snapshot
		$this->db->where('parent_id', $po_id)->delete('purchase_in_product');

		// Update PO status back to Loading List and clear BOE
		$this->db->where('id', $po_id)->update('purchase_order', [
			'delivery_status' => 'loading',
			'inr_rate' => '0',
			'boe_no' => '',
			'boe_date' => NULL,
			'completed_date' => NULL,
			'net_sales_value_1' => 0,
			'transport_gst_amount' => 0,
			'grand_total' => 0
		]);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return ['status' => 400, 'message' => 'Transaction failed during reversal.'];
		} else {
			// Insert audit log
			$log_data = array(
				'parent_id'      => $po_id,
				'ref_id'         => NULL,
				'module'         => 'purchase_in',
				'action'         => 'delete',
				'message'        => 'Purchase In deleted by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($purchase_in_log_data),
				'table_name'     => 'purchase_in_product',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			return ['status' => 200, 'message' => 'Purchase Order successfully moved back to Loading List and Inventory cleared.'];
		}
	}

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

		$po_row = $this->db->where('id', $po_id)->get('purchase_order')->row_array();
		$is_edit = (!empty($po_row) && $po_row['delivery_status'] == 'priority');
		$old_log_data = $is_edit ? $this->get_complete_priority_list_log_data($po_id) : null;

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
		$is_replaces = $this->input->post('is_replace');
		$loading_is_replaces = $this->input->post('loading_is_replace');
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
		$sorts = $this->input->post('sort');
		$loading_sorts = $this->input->post('loading_sort');

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
										'is_replace' => isset($is_replaces[$row_key]) ? intval($is_replaces[$row_key]) : 0,
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
										'sort' => isset($sorts[$row_key]) ? intval($sorts[$row_key]) : 0,
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
						'is_replace' => isset($loading_is_replaces[$row_key]) ? intval($loading_is_replaces[$row_key]) : 0,
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
						'sort' => isset($loading_sorts[$row_key]) ? intval($loading_sorts[$row_key]) : 0,
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
			// Insert audit log
			$new_log_data = $this->get_complete_priority_list_log_data($po_id);
			if ($is_edit) {
				$log_json = array(
					'old_data' => $old_log_data,
					'new_data' => $new_log_data
				);
				$action = 'edit';
				$message = 'Priority List edited by ' . $this->session->userdata('super_name');
			} else {
				$log_json = $new_log_data;
				$action = 'add';
				$message = 'Priority List added by ' . $this->session->userdata('super_name');
			}
			$log_data = array(
				'parent_id'      => $po_id,
				'ref_id'         => NULL,
				'module'         => 'priority_list',
				'action'         => $action,
				'message'        => $message,
				'json'           => json_encode($log_json),
				'table_name'     => 'po_products',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

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

		// Capture data before delete for audit log
		$priority_log_data = $this->get_complete_priority_list_log_data($po_id);

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
			// Insert audit log
			$log_data = array(
				'parent_id'      => $po_id,
				'ref_id'         => NULL,
				'module'         => 'priority_list',
				'action'         => 'delete',
				'message'        => 'Priority List deleted by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($priority_log_data),
				'table_name'     => 'po_products',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

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

		// Capture data before delete for audit log
		$loading_log_data = $this->get_complete_loading_list_log_data($po_id);

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
			// Insert audit log
			$log_data = array(
				'parent_id'      => $po_id,
				'ref_id'         => NULL,
				'module'         => 'loading_list',
				'action'         => 'delete',
				'message'        => 'Loading List deleted by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($loading_log_data),
				'table_name'     => 'loading_po_product',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

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
		
		$company_id        = $this->session->userdata('company_id');
		$keyword_filter .= " AND (company_id='" . $company_id . "')";

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
		$query = $this->db->query("SELECT id, warehouse_name, product_name, item_code, product_id, SUM(quantity) as quantity, SUM(official_qty) as white_qty, SUM(black_qty) as black_qty, categories FROM inventory WHERE (id!='') $keyword_filter group by product_id ORDER BY id DESC LIMIT $start, $length");
		
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$product_id = $item['product_id'];
				
				$size_label = '';
				$category = $this->common_model->getRowById('categories', 'name', ['id' => $item['categories']]);
        $size_label = $category['name'] ?? '-';
				
				$action = '';
				$wid_for_po = (isset($warehouse_id) && $warehouse_id != '' && $warehouse_id != 'All') ? $warehouse_id : '';
				$view_url = base_url() . 'inventory/my-stock-batch/' . $id  . '/' . (isset($warehouse_id) ? $warehouse_id : '');
				$action .= '<a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>';

				$latest_batch = $this->db->select('batch_no')->where('product_id', $product_id)->where('batch_no!=', '')->where('batch_no!=', null)->order_by('id', 'DESC')->limit(1)->get('inventory')->row_array();
				$batch_no_val = $latest_batch ? $latest_batch['batch_no'] : '';
				if ($batch_no_val != '') {
					$action .= '<a href="javascript:void(0);" onclick="showAjaxModal(\'' . base_url() . 'modal/popup_inventory/modal_batch_barcode/' . urlencode($batch_no_val) . '\', \'Generate Barcode\')" data-toggle="tooltip" data-bs-placement="top" title="Generate Barcode"><button type="button" class="btn mr-1 mb-1 btn-outline-success" style="padding: 4px 8px;"><i class="fa fa-barcode" aria-hidden="true"></i></button></a>';
				}
				
				$po_qty_arr = $this->get_product_po_list($product_id, $company_id, 'po', $wid_for_po);
				$po_qty = array_sum(array_column($po_qty_arr, 'quantity'));
				$po_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $product_id. "," . $company_id. ",\"po\",\"" . $wid_for_po . "\")'>" . $po_qty . "</a>";
				
				$priority_qty_arr = $this->get_product_po_list($product_id, $company_id, 'priority', $wid_for_po);
				$priority_qty = array_sum(array_column($priority_qty_arr, 'quantity'));
				$priority_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $product_id. "," . $company_id. ",\"priority\",\"" . $wid_for_po . "\")'>" . $priority_qty . "</a>";

				$loading_qty_arr = $this->get_product_po_list($product_id, $company_id, 'loading', $wid_for_po);
				$loading_qty = array_sum(array_column($loading_qty_arr, 'quantity'));
				$loading_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $product_id. "," . $company_id. ",\"loading\",\"" . $wid_for_po . "\")'>" . $loading_qty . "</a>";

				$no_expense_amt_arr = $this->get_product_po_list($product_id, $company_id, 'no_expense', $wid_for_po);
				$no_expense_amt = array_sum(array_column($no_expense_amt_arr, 'amount'));
				$no_expense_amt_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $product_id. "," . $company_id. ",\"no_expense\",\"" . $wid_for_po . "\")'>" . $no_expense_amt . "</a>";

				$expense_amt_arr = $this->get_product_po_list($product_id, $company_id, 'expense', $wid_for_po);
				$expense_amt = array_sum(array_column($expense_amt_arr, 'amount'));
				$expense_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $product_id. "," . $company_id. ",\"expense\",\"" . $wid_for_po . "\")'>" . $expense_amt . "</a>";
				
				$data[] = array(
					"sr_no"             => ++$start,
					"category"        => $size_label,
					"product_name"      => $item['product_name'],
					"quantity"          => $item['quantity'],
					"black_qty"         => $item['black_qty'],
					"white_qty"         => $item['white_qty'],
					"po_qty"            => $po_qty_btn,
					"priority_qty"      => $priority_qty_btn,
					"loading_qty"       => $loading_qty_btn,
					"no_expense_amt"    => $no_expense_amt_btn,
					"expense_amt"       => $expense_qty_btn,
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
				// $product_id = base64_decode($product_id);
				$result = $this->common_model->get_batch_product_1($product_id, $warehouse_id);
				// echo $this->db->last_query(); exit();
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
		$query = $this->db->query("SELECT id,warehouse_name,item_code,categories,black_qty,official_qty,product_name,product_id,quantity,batch_no,official_total_rs,total_amt FROM inventory WHERE (id!='') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

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
				if ($item['batch_no'] != '' && $item['batch_no'] != '-') {
					$action .= '<a href="javascript:void(0);" onclick="showLargeModal(\'' . base_url() . 'modal/popup_inventory/modal_batch_barcode/' . urlencode($item['batch_no']) . '\', \'Generate Barcode\')" data-toggle="tooltip" data-bs-placement="top" title="Generate Barcode"><button type="button" class="btn mr-1 mb-1 btn-outline-success" style="padding: 4px 8px;"><i class="fa fa-barcode" aria-hidden="true"></i></button></a>';
				}

				$data[] = array(
					"sr_no"       		=> ++$start,
					"id"          		=> $item['id'],
					"warehouse_name"	=> $item['warehouse_name'],
					"category"				=> $size_label,
					"item_code"				=> $item['item_code'],
					"product_name"		=> $item['product_name'],
					"without_exp"			=> $item['official_total_rs'],
					"with_exp"				=> $item['total_amt'],
					"quantity"        => $item['quantity'],
					"black_qty"				=> $item['black_qty'],
					"official_qty"		=> $item['official_qty'],
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

	public function get_low_stock()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (i.item_code like '%" . $keyword . "%' OR i.product_name like '%" . $keyword . "%' OR s.name like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("
			SELECT i.product_id
			FROM inventory i
			LEFT JOIN supplier s ON s.id = i.supplier_id
			INNER JOIN product_variations pv ON pv.product_id = i.product_id AND pv.supplier_id = i.supplier_id
			WHERE 1=1 $keyword_filter
			GROUP BY i.product_id, i.supplier_id, s.name, pv.intimation
			HAVING SUM(i.quantity) > 0 AND SUM(i.quantity) <= pv.intimation
		")->num_rows();

		$query = $this->db->query("
			SELECT 
				MAX(i.id) as id,
				MAX(i.warehouse_name) as warehouse_name,
				MAX(i.item_code) as item_code,
				MAX(i.categories) as categories,
				SUM(i.black_qty) as black_qty,
				SUM(i.official_qty) as official_qty,
				MAX(i.product_name) as product_name,
				i.product_id,
				SUM(i.quantity) as quantity,
				'Merged' as batch_no,
				SUM(i.official_total_rs) as official_total_rs,
				SUM(i.total_amt) as total_amt,
				s.name AS supplier_name
			FROM inventory i
			LEFT JOIN supplier s ON s.id = i.supplier_id
			INNER JOIN product_variations pv ON pv.product_id = i.product_id AND pv.supplier_id = i.supplier_id
			WHERE 1=1 $keyword_filter
			GROUP BY i.product_id, i.supplier_id, s.name, pv.intimation
			HAVING SUM(i.quantity) > 0 AND SUM(i.quantity) <= pv.intimation
			ORDER BY id DESC 
			LIMIT $start, $length
		");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$product_id = $item['product_id'];

				$size_label = '';
				$category = $this->common_model->getRowById('categories', 'name', ['id' => $item['categories']]);
				$size_label = $category['name'] ?? '-';

				$data[] = array(
					"sr_no"       		=> ++$start,
					"id"          		=> $item['id'],
					"warehouse_name"	=> $item['warehouse_name'],
					"category"			=> $size_label,
					"item_code"			=> $item['item_code'],
					"product_name"		=> $item['product_name'],
					"supplier_name"		=> $item['supplier_name'],
					"without_exp"		=> $item['official_total_rs'],
					"with_exp"			=> $item['total_amt'],
					"quantity"        	=> $item['quantity'],
					"black_qty"			=> $item['black_qty'],
					"official_qty"		=> $item['official_qty'],
					"batch_no"        	=> ($item['batch_no'] != '' && $item['batch_no'] != null) ? $item['batch_no'] : '-',
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
		$query = $this->db->query("SELECT id,warehouse_name,product_name,quantity,official_qty,black_qty,order_id,status,received_date,added_by_name,added_date FROM inventory_history WHERE (id!='') $keyword_filter ORDER BY id ASC LIMIT $start, $length");
		// $query = $this->db->query("SELECT id,warehouse_name,product_name,quantity,official_qty,black_qty,order_id,status,received_date,added_by_name,added_date FROM inventory_history WHERE (id!='') $keyword_filter ORDER BY received_date DESC LIMIT $start, $length");

		if (!empty($query)) {
			$total_qty = 0;
			$total_white = 0;
			$total_black = 0;
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_id = $item['order_id'];

				$total_qty += intval($item['quantity'] ?? 0);
				$total_white += intval($item['official_qty'] ?? 0);
				$total_black += intval($item['black_qty'] ?? 0);

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
					"white_qty"       => $item['official_qty'],
					"black_qty"       => $item['black_qty'],
					"added_by_name"        => $item['added_by_name'],
					"supplier_name"        => $supplier_name,
					"to"        => $to,
				);
			}

			// Append Total Row
			$data[] = array(
				"sr_no"       => "",
				"id"          => "",
				"date"        => "<b>Total</b>",
				"voucher_no"        => "",
				"product_name"        => "",
				"status"        => "",
				"quantity"        => "<b>" . $total_qty . "</b>",
				"white_qty"       => "<b>" . $total_white . "</b>",
				"black_qty"       => "<b>" . $total_black . "</b>",
				"added_by_name"        => "",
				"supplier_name"        => "",
				"to"        => "",
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

	public function get_batches_by_warehouse($warehouse_id, $company_id)
	{
		$this->db->select('DISTINCT(batch_no) as batch_no');
		$this->db->from('inventory');
		$this->db->where('warehouse_id', $warehouse_id);
		if (!empty($company_id)) {
			$this->db->where('company_id', $company_id);
		}
		$this->db->where('batch_no IS NOT NULL');
		$this->db->where('batch_no !=', '');
		$this->db->order_by('batch_no', 'ASC');
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_products_by_batch($warehouse_id, $company_id, $batch_no)
	{
		$this->db->select('inventory.product_id, inventory.product_name, inventory.item_code');
		$this->db->from('inventory');
		$this->db->where('warehouse_id', $warehouse_id);
		$this->db->where('batch_no', $batch_no);
		if (!empty($company_id)) {
			$this->db->where('company_id', $company_id);
		}
		$this->db->group_by('inventory.product_id');
		$this->db->order_by('inventory.product_name', 'ASC');
		$query = $this->db->get();
		
		$resultdata = array();
		if ($query->num_rows() > 0) {
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
		$query = $this->db->query("SELECT p.id as product_id, p.item_code, p.name as product_name, IFNULL(SUM(i.quantity),0) as total_qty 
                                   FROM raw_products p 
                                   LEFT JOIN inventory i ON p.id = i.product_id AND i.warehouse_id='$warehouse_id' 
                                   WHERE p.is_deleted='0'
                                   GROUP BY p.id order by p.name asc");
		$resultdata = array();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$resultdata[] = array(
					"id" => $item['product_id'],
					"name" => trim($item['product_name']) . ' - ' . trim($item['item_code']),
				);
			}
		}
		return $resultdata;
	}

	public function get_product_id_by_company()
	{
		$query = $this->db->query("
			SELECT 
				p.id as product_id, p.item_code, p.name as product_name, IFNULL(SUM(i.quantity),0) as total_qty 
			FROM raw_products p 
				LEFT JOIN inventory i ON p.id = i.product_id
			WHERE p.is_deleted='0'
			GROUP BY p.id order by p.name asc");

		$resultdata = array();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $item) {
				$resultdata[] = array(
					"id" => $item['product_id'],
					"name" => trim($item['product_name']) . ' - ' . trim($item['item_code']),
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
		$query = $this->db->query("SELECT SUM(quantity) as quantity, product_id FROM inventory WHERE warehouse_id='$warehouse_id' and product_id='$product_id' AND quantity > 0 group by product_id order by product_name asc limit 1");

		if (!empty($query)) {
			$item = $query->row_array();
			$product = $this->common_model->getRowById('raw_products', '*', ['id' => $item['product_id']]);
			if($product != ""){
				$rate = $product['rate'];
				$gst = $product['gst'];
			}else{
				$rate = $product['rate'];
				$gst = $product['gst'];
			}

			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 200,
				'message' => 'success',
				"quantity" => $item['quantity'],
				"tax" => $gst,
				"rate" => $rate,
			));
		} else {
			header('Content-Type: application/json');
			echo json_encode(array(
				'status' => 400,
				'message' => 'success',
				"quantity" => '0',
				"tax" => '0',
				"rate" => '0',
			));
		}
	}

	public function get_qty_by_product_company($product_id, $customer_id = '')
	{
		$product = $this->common_model->getRowById('raw_products', '*', ['id' => $product_id]);
		if (empty($product)) {
			return array(
				'status' => 400,
				'message' => 'Product not found'
			);
		}

		$query = $this->db->query("SELECT IFNULL(SUM(quantity), 0) as total_qty FROM inventory WHERE product_id='$product_id'");
		
		$item = $query->row_array();
		$gst = $product['gst'] ?? 0;

		$latest_price = 0;
		if ($customer_id != '') {
			$price_query = $this->db->query("SELECT amount FROM sales_order_product sop JOIN sales_order so ON so.id = sop.order_id WHERE so.customer_id = '$customer_id' AND sop.product_id = '$product_id' ORDER BY sop.id DESC LIMIT 1");
			if ($price_query->num_rows() > 0) {
				$latest_price = $price_query->row()->amount;
			}
		}

		return array(
			'status' => 200,
			'message' => 'success',
			"quantity" => $item['total_qty'] ?? 0,
			"tax" => $gst,
			"rate" => $latest_price,
		);
	}

	public function get_last_price_history($customer_id, $product_id)
	{
		$query = $this->db->query("SELECT amount as last_price, order_id, qty, (SELECT date FROM sales_order WHERE id = order_id) as order_date FROM sales_order_product sop JOIN sales_order so ON so.id = sop.order_id WHERE so.customer_id = '$customer_id' AND sop.product_id = '$product_id' ORDER BY sop.id DESC LIMIT 5");
		return $query->result_array();
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

		$po_row = $this->db->where('id', $po_id)->get('purchase_order')->row_array();
		$is_edit = (!empty($po_row) && $po_row['delivery_status'] == 'loading');
		$old_log_data = $is_edit ? $this->get_complete_loading_list_log_data($po_id) : null;

		// Start transaction
		$this->db->trans_start();

		// Revert any existing loading replacements for this PO before updating
		$replaced_products = $this->db->get_where('po_products', array(
			'parent_id' => $po_id,
			'is_replace' => 1
		))->result_array();

		foreach ($replaced_products as $rp) {
			$this->common_model->revert_replace_products($po_id, $rp['product_id'], 'loading');
		}

		// Update purchase_order table: delivery_status to 'loading'
		$this->db->where('id', $po_id);
		$this->db->update('purchase_order', [
			'delivery_status' => 'loading',
			'loading_date' => date('Y-m-d H:i:s'),
			'expected_date' => $this->input->post('expected_date'),
			'arrival_date' => $this->input->post('arrival_date'),
		]);

		// Delete existing loading list entries for this PO
		$this->db->where('parent_id', $po_id);
		$this->db->delete('loading_po_product');

		$this->db->where('po_id', $po_id);
		$this->db->delete('loading_product_total');

		// Get product data arrays from form
		$product_ids = $this->input->post('product_id'); // Array keys are loading_po_product.id or 'new_...'
		$supplier_ids_post = $this->input->post('supplier_id');
		$quantities = $this->input->post('quantity');
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
		$sorts = $this->input->post('sort');
		
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

		// Process each product
		if (!empty($product_ids)) {
			foreach ($product_ids as $form_product_key => $product_id) {
				if (empty($form_product_key)) continue;

				// Get values from arrays, default to 0 if not set
				$loading_qty = isset($loading_qtys[$form_product_key]) ? intval($loading_qtys[$form_product_key]) : 0;
				$priority_qty = isset($quantities[$form_product_key]) ? intval($quantities[$form_product_key]) : 0;
				$sort = isset($sorts[$form_product_key]) ? intval($sorts[$form_product_key]) : 0;
				
				// Only process if loading quantity is greater than 0
				if ($loading_qty <= 0) continue;

				// Apply replacement if this product is a replacement product in this PO
				$check_replace = $this->db->get_where('po_products', array(
					'parent_id' => $po_id,
					'product_id' => $product_id,
					'is_replace' => 1
				))->row_array();

				if ($check_replace) {
					$this->common_model->update_replace_product('po', $po_id, $product_id, $loading_qty);
				}

				$official_ci_qty = isset($official_ci_qtys[$form_product_key]) ? intval($official_ci_qtys[$form_product_key]) : 0;
				$black_qty = isset($black_qtys[$form_product_key]) ? intval($black_qtys[$form_product_key]) : 0;
				$unit_price_rmb = isset($unit_price_rmbs[$form_product_key]) ? floatval($unit_price_rmbs[$form_product_key]) : 0.00;
				$total_amount_rmb = isset($total_amount_rmbs[$form_product_key]) ? floatval($total_amount_rmbs[$form_product_key]) : 0.00;
				$official_ci_unit_price_usd = isset($official_ci_unit_price_usds[$form_product_key]) ? floatval($official_ci_unit_price_usds[$form_product_key]) : 0.00;
				$total_amount_usd = isset($total_amount_usds[$form_product_key]) ? floatval($total_amount_usds[$form_product_key]) : 0.00;
				$black_total_price = isset($black_total_prices[$form_product_key]) ? floatval($black_total_prices[$form_product_key]) : 0.00;
				
				// Get invoice details
				$invoice_no = isset($invoice_nos[$form_product_key]) ? intval($invoice_nos[$form_product_key]) : 0;
				$invoice_supplier_id = 0;
				$invoice_info = '';
				$invoice_date = null;
				$invoice_terms = '';
				$invoice_price_terms = '';
				
				if ($invoice_no > 0) {
					if (isset($invoice_suppliers[$invoice_no])) {
						$invoice_supplier_id = intval($invoice_suppliers[$invoice_no]);
					}
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
				$sum_pkg_ctn = 0.00;
				$sum_nw_kg = 0.00;
				$sum_total_nw_kg = 0.00;
				$sum_gw_kg = 0.00;
				$sum_total_gw_kg = 0.00;
				$sum_length = 0.00;
				$sum_width = 0.00;
				$sum_height = 0.00;
				$sum_total_cbm_value = 0.00;

				$variation_data_list = [];

				// Collect variation metrics
				if (isset($nw_kgs[$form_product_key]) && is_array($nw_kgs[$form_product_key])) {
					foreach ($nw_kgs[$form_product_key] as $var_index => $value) {
						$pkg_ctn_val = isset($pkg_ctns[$form_product_key][$var_index]) ? floatval($pkg_ctns[$form_product_key][$var_index]) : 0.00;
						$nw_kg_val = isset($nw_kgs[$form_product_key][$var_index]) ? floatval($nw_kgs[$form_product_key][$var_index]) : 0.00;
						$total_nw_kg_val = isset($total_nws[$form_product_key][$var_index]) ? floatval($total_nws[$form_product_key][$var_index]) : 0.00;
						$gw_kg_val = isset($gw_kgs[$form_product_key][$var_index]) ? floatval($gw_kgs[$form_product_key][$var_index]) : 0.00;
						$total_gw_kg_val = isset($total_gws[$form_product_key][$var_index]) ? floatval($total_gws[$form_product_key][$var_index]) : 0.00;
						$length_val = isset($lengths[$form_product_key][$var_index]) ? floatval($lengths[$form_product_key][$var_index]) : 0.00;
						$width_val = isset($widths[$form_product_key][$var_index]) ? floatval($widths[$form_product_key][$var_index]) : 0.00;
						$height_val = isset($heights[$form_product_key][$var_index]) ? floatval($heights[$form_product_key][$var_index]) : 0.00;
						$total_cbm_val = isset($total_cbms[$form_product_key][$var_index]) ? floatval($total_cbms[$form_product_key][$var_index]) : 0.00;

						$sum_pkg_ctn += $pkg_ctn_val;
						$sum_nw_kg += $nw_kg_val;
						$sum_total_nw_kg += $total_nw_kg_val;
						$sum_gw_kg += $gw_kg_val;
						$sum_total_gw_kg += $total_gw_kg_val;
						$sum_length += $length_val;
						$sum_width += $width_val;
						$sum_height += $height_val;
						$sum_total_cbm_value += $total_cbm_val;

						$variation_data_list[] = [
							'po_id' => $po_id,
							'pkg_ctn' => $pkg_ctn_val,
							'nw_kg' => $nw_kg_val,
							'total_nw_kg' => $total_nw_kg_val,
							'gw_kg' => $gw_kg_val,
							'total_gw_kg' => $total_gw_kg_val,
							'length' => $length_val,
							'width' => $width_val,
							'height' => $height_val,
							'total_cbm_value' => $total_cbm_val
						];
					}
				}

				// Fetch product details from raw_products
				$product_details = $this->get_raw_products_by_id($product_id)->row_array();
				if (!$product_details) {
					$this->db->trans_rollback();
					echo json_encode(["status" => 400, "message" => "Product not found: ID " . $product_id]);
					return;
				}

				// Prepare data for loading_po_product
				$insert_data = [
					'parent_id'            	=> $po_id,
					'supplier_id'          	=> isset($supplier_ids_post[$form_product_key]) ? intval($supplier_ids_post[$form_product_key]) : $product_details['supplier_id'],
					'product_type'         	=> $product_details['type'],
					'product_id'           	=> $product_id,
					'is_replace'           	=> $check_replace ? 1 : 0,
					'product_name'         	=> $product_details['name'] ?? '',
					'item_code'            	=> $product_details['item_code'],
					'categories'           	=> $product_details['categories'] ?? NULL,
					'group_id'             	=> $product_details['group_id'] ?? NULL,
					'hsn_code'             	=> $product_details['hsn_code'] ?? NULL,
					'unit'                 	=> $product_details['unit'] ?? NULL,
					'cbm'               		=> $product_details['cbm'],
					'total_cbm'         		=> $product_details['cbm'] * $loading_qty,
					'loading_qty' => $loading_qty,
					'official_ci_qty' => $official_ci_qty,
					'black_qty' => $black_qty,
					'unit_price_rmb' => $unit_price_rmb,
					'total_amount_rmb' => $total_amount_rmb,
					'official_ci_unit_price_usd' => $official_ci_unit_price_usd,
					'total_amount_usd' => $total_amount_usd,
					'black_total_price' => $black_total_price,
					'pkg_ctn' => $sum_pkg_ctn,
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
					'invoice_price_terms' => $invoice_price_terms,
					'cartoon' => intval($product_details['cartoon_qty'] ?? 0),
					'rate' => $product_details['product_mrp'] ?? NULL,
					'basic_amount' => $product_details['costing_price'] ?? NULL,
					'quantity' => $priority_qty,
					'pending' => 0,
					'received' => 0,
					'received_date' => null,
					'is_priority' => 1,
					'is_complete' => 0,
					'sort' => $sort
				];

				// Insert into loading_po_product
				$this->db->insert('loading_po_product', $insert_data);
				$new_parent_id = $this->db->insert_id();

				// Insert variation records into loading_product_total
				foreach ($variation_data_list as $var_data) {
					$var_data['parent_id'] = $new_parent_id;
					$this->db->insert('loading_product_total', $var_data);
				}
			}
		}

		// Complete transaction
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			echo json_encode(['status' => 400, 'message' => 'Error updating loading list']);
		} else {
			// Insert audit log
			$new_log_data = $this->get_complete_loading_list_log_data($po_id);
			if ($is_edit) {
				$log_json = array(
					'old_data' => $old_log_data,
					'new_data' => $new_log_data
				);
				$action = 'edit';
				$message = 'Loading List edited by ' . $this->session->userdata('super_name');
			} else {
				$log_json = $new_log_data;
				$action = 'add';
				$message = 'Loading List added by ' . $this->session->userdata('super_name');
			}
			$log_data = array(
				'parent_id'      => $po_id,
				'ref_id'         => NULL,
				'module'         => 'loading_list',
				'action'         => $action,
				'message'        => $message,
				'json'           => json_encode($log_json),
				'table_name'     => 'loading_po_product',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			$redirect_url = $this->agent->referrer();
			echo json_encode([
				'status' => 200, 
				'message' => 'Loading list updated successfully!',
				'url' => $redirect_url
			]);
		}
	}

	public function soft_delete_loading_list_item($id)
	{
		$this->db->where('id', $id);
		return $this->db->update('loading_po_product', ['is_deleted' => 1]);
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
			'expected_date' => $this->input->post('expected_date'),
			'arrival_date' => $this->input->post('arrival_date'),
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

				if (!$this->db->insert('loading_po_product', $po_product_data)) {
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
		$company_id 				= ($this->session->userdata('super_type_id') == 7) ? [$this->session->userdata('company_id')] : isset($company_id) ? $company_id : [];
		$type 							= $this->input->post('type');

		if($type == 'leads') {
			$staff_id 				  = ($this->session->userdata('super_type_id') == 7) ? $this->session->userdata('super_user_id') : 0;
		} else {
			$staff_id 				  =  $this->input->post('staff_id');
		}
		
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
		$state_name = ($state_id > 0) ? (string) $this->common_model->get_state_name($state_id) : '';
		$city_name  = ($city_id > 0)  ? (string) $this->common_model->get_city_name($city_id) : '';

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

		$is_distributor = ($this->input->post('is_distributor') == 1) ? 1 : 0;

		$data = array(
			"company_id"     => implode(',', $company_id),
			"type"   				 => $type,
			"is_distributor" => $is_distributor,
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
			"outstanding"    => ($this->input->post('outstanding') != '') ? clean_and_escape($this->input->post('outstanding')) : 0.00,

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

		if($type == 'leads' && $this->session->userdata('super_type_id') == 7) {
			$data['status'] = 'fresh';
			$data['status_label'] = 'Fresh Lead';
		}

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
		$company_id 				= ($this->session->userdata('super_type_id') == 7) ? [$this->session->userdata('company_id')] : isset($company_id) ? $company_id : [];

		if($type == 'leads') {
			$staff_id 				  = ($this->session->userdata('super_type_id') == 7) ? $this->session->userdata('super_user_id') : 0;
		} else {
			$staff_id 				  =  $this->input->post('staff_id');
		}

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
		$state_name = ($state_id > 0) ? (string) $this->common_model->get_state_name($state_id) : '';
		$city_name  = ($city_id > 0)  ? (string) $this->common_model->get_city_name($city_id) : '';

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
		$is_distributor = ($this->input->post('is_distributor') == 1) ? 1 : 0;

		$data = array(
			"type"   				 => $type,
			"company_id"     => is_array($company_id) ? implode(',', $company_id) : (string) $company_id,
			"is_distributor" => $is_distributor,
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
			"outstanding"    => ($this->input->post('outstanding') != '') ? clean_and_escape($this->input->post('outstanding')) : 0.00,

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
		} elseif($type == 'leads' && $this->session->userdata('super_type_id') == 7) {
			$data['status'] = 'fresh';
			$data['status_label'] = 'Fresh Lead';
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
			"url"     => base_url('inventory/customer'),
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

	public function share_customer()
	{
		$user_id   = (int) $this->session->userdata('super_user_id');
		$user_name = (string) $this->session->userdata('super_name');

		$resultpost = array(
			"status" => 200,
			"message" => get_phrase('customer_shared_successfully'),
			"url" => $this->session->userdata('previous_url'),
		);

		$customer_id = clean_and_escape($this->input->post('customer_id'));
		$shared_id = clean_and_escape($this->input->post('shared_id'));

		$original_customer = $this->get_customer_by_id($customer_id)->row_array();
		if (empty($original_customer)) {
			$resultpost = array(
				"status" => 400,
				"message" => get_phrase('customer_not_found'),
			);
			return simple_json_output($resultpost);
		}

		$current_staff_id = $original_customer['added_by_id'];

		// Update shared_id in customer table
		$data_update = array(
			'shared_id' => $shared_id
		);
		$this->db->where('id', $customer_id);
		$updated = $this->db->update('customer', $data_update);

		if ($updated) {
			// Delete existing customer commissions
			$this->db->where('customer_id', $customer_id)->delete('customer_commission');

			$share_comm_inputs = $this->input->post('share_comm');
			$my_comm_inputs = $this->input->post('my_comm');

			$all_commissions = $this->db->where('is_deleted', '0')->get('product_commission_slab')->result_array();
			$all_profits = $this->db->where('is_deleted', '0')->get('profit_commission_slab')->result_array();

			foreach ($all_commissions as $comm) {
				$comm_id = $comm['id'];
				foreach ($all_profits as $profit) {
					$profit_id = $profit['id'];
					$share_val = isset($share_comm_inputs[$comm_id][$profit_id]) && $share_comm_inputs[$comm_id][$profit_id] !== '' ? (float)$share_comm_inputs[$comm_id][$profit_id] : 0.00;
					$my_val = isset($my_comm_inputs[$comm_id][$profit_id]) && $my_comm_inputs[$comm_id][$profit_id] !== '' ? (float)$my_comm_inputs[$comm_id][$profit_id] : 0.00;

					// Insert row for customer commission mapping
					$this->db->insert('customer_commission', [
						'customer_id'       => $customer_id,
						'staff_id'          => $current_staff_id,
						'shared_staff_id'   => $shared_id,
						'commission_id'     => $comm_id,
						'profit_id'         => $profit_id,
						'my_commission'     => $my_val,
						'shared_commission' => $share_val,
						'created_at'        => date("Y-m-d H:i:s")
					]);
				}
			}

			// Add log entry
			$logs = [
				"customer_id"     => $customer_id,
				"action"          => "share",
				"label"          => json_encode(["badge" => "success", "message" => "Customer Shared"]),
				"message"         => "Customer shared with " . $this->common_model->selectByidParam($shared_id, 'sys_users', 'first_name') . " by {$user_name}",
				"json"            => json_encode([
					"old_shared_id" => $original_customer['shared_id'],
					"new_shared_id" => $shared_id
				]),
				"added_by"        => $user_id,
				"added_by_name"   => get_phrase($user_name),
				"added_date"      => date("Y-m-d H:i:s"),
			];
			$this->db->insert('customer_log', $logs);
		}

		$this->session->set_flashdata('flash_message', get_phrase('customer_shared_successfully'));
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
		$status_input = clean_and_escape($this->input->post('status'));
		$status = explode(' | ', $status_input);
		$remark = clean_and_escape($this->input->post('remark'));

		$existing_customer = $this->db->where('id', $customer_id)->get('customer')->row_array();

		// if (!empty($existing_customer) && $existing_customer['status'] == 'stalking') {
		// 	$final_status       = 'stalking';
		// 	$final_status_label = isset($status[1]) ? $status[1] : '';
		// } else {
			$final_status       = isset($status[0]) ? $status[0] : '';
			$final_status_label = isset($status[1]) ? $status[1] : '';
		// }

		// Update customer company and staff
		$data = array(
			'status_date'  => ($final_status == 'lost') ? date("Y-m-d H:i:s") : $status_date,
			'status'       => $final_status,
			'status_label' => $final_status_label,
			'remark'       => $remark,
		);

		$this->db->where('id', $customer_id);
		$updated = $this->db->update('customer', $data);

		if ($updated) {
			$customer_row   = $existing_customer ? $existing_customer : $this->db->where('id', $customer_id)->get('customer')->row_array();
			$customer_name  = $customer_row ? $customer_row['company_name'] : '';
			$is_distributor = $customer_row ? (int)$customer_row['is_distributor'] : 0;
			$status_label_val = $final_status_label;

			$call_date_val = ($final_status == 'lost') ? date("Y-m-d H:i:s") : date('Y-m-d H:i:s', strtotime($status_date));

			$call_data = array(
				'customer_id'    => $customer_id,
				'customer_name'  => $customer_name,
				'is_distributor' => $is_distributor,
				'is_lead'        => ($final_status == 'stalking') ? 0 : 1,
				'status'         => $status_label_val,
				'date'           => $call_date_val,
				'remark'         => $remark,
				'added_by'       => $user_id,
				'added_by_name'  => $user_name,
				'created_at'     => date("Y-m-d H:i:s")
			);
			$this->db->insert('customer_calls', $call_data);

			$action = $final_status;
			$message = "Leads Moved To " . get_phrase(($final_status == 'stalking') ? 'follow_up': $final_status) . " by {$user_name}";
			$json_data = [
				'status_date'  => ($final_status == 'lost') ? date("Y-m-d H:i:s") : $status_date,
				'status'       => $final_status,
				'status_label' => $final_status_label,
				'remark'       => $remark,
			];

			$logs = [
				"customer_id"     => $customer_id,
				"action"          => $action,
				"label"           => json_encode(["badge" => ($final_status == 'lost') ? "danger" : "warning", "message" => ($final_status == 'lost') ? "Lead Lost" : "Follow Up Added"]),
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
		
		$status = '';
		if (isset($_REQUEST['status']) && $_REQUEST['status'] != ""):
			$status        = $_REQUEST['status'];
			$date =  date('Y-m-d');
			if($status == 'new') {
				$keyword_filter .= " AND (status='fresh' OR status='follow') AND type='leads'";
			} elseif($status == 'today') {
				$keyword_filter .= " AND ((status='follow' AND type='leads') OR (status='stalking' AND type='customer')) AND (DATE(status_date) = '$date')";
			} elseif($status == 'upcoming') {
				$keyword_filter .= " AND ((status='follow' AND type='leads') OR (status='stalking' AND type='customer')) AND (DATE(status_date) > '$date')";
			} elseif($status == 'missed') {
				$keyword_filter .= " AND ((status='follow' AND type='leads') OR (status='stalking' AND type='customer')) AND (DATE(status_date) < '$date')";
			} elseif($status == 'lost') {
				$keyword_filter .= " AND status='lost' AND type='leads'";
			} elseif($status == 'moved') {
				$keyword_filter .= " AND is_move = '1' AND type='customer'";
			} else {
				$keyword_filter .= " AND (status='' OR status IS NULL) AND type='$data_type'";
			}
		endif;

		$total_count = $this->db->query("SELECT id FROM customer WHERE (is_deleted='0') $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT id, company_name, gst_name, gst_no, city_name, state_name, pincode, added_by_name, owner_name, owner_mobile, status, status_date, status_label, move_date, is_move, type FROM customer WHERE (is_deleted='0') $keyword_filter ORDER BY id DESC LIMIT $start, $length");
		// echo $this->db->last_query(); exit();

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$data_type = $item['type'];

				$id = $item['id'];
				$badge        = '';
				if($item['status'] == 'fresh') {
					$badge        = '<span class="badge badge-primary">' . $item['status_label'] . '</span>';
				} elseif($item['status'] == 'follow' || $item['status'] == 'stalking') {
					$badge_class = ($item['status'] == 'stalking') ? 'badge-secondary' : 'badge-warning';
					if(!empty($item['status_date']) && $item['status_date'] != '0000-00-00 00:00:00') {
						$s_date     = date('Y-m-d', strtotime($item['status_date']));
						$today_date = date('Y-m-d');
						if($s_date == $today_date) {
							$badge_class = 'badge-warning';
						} elseif($s_date > $today_date) {
							$badge_class = 'badge-info';
						} elseif($s_date < $today_date) {
							$badge_class = 'badge-danger';
						}
					}
					$label_text = !empty($item['status_label']) ? $item['status_label'] : ($item['status'] == 'stalking' ? 'Stalking' : 'Follow Up');
					$badge      = '<span class="badge ' . $badge_class . '">' . $label_text . '</span>';
				} elseif($item['status'] == 'lost') {
					$badge        = '<span class="badge badge-danger">' . $item['status_label'] . '</span>';
				}

				$delete_url = "confirm_modal('" . base_url() . "inventory/customer/delete/" . $id . "','Are you sure want to delete!')";
				$edit_url = base_url() . 'inventory/' . $data_type . '/edit/' . $id;
				$move_url = base_url() . 'inventory/' . $data_type . '/move/' . $id;
				$replicate_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/customer_replicate_modal/" . $id . "','Replicate Customer')";
				$reassign_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/customer_reinitiate_modal/" . $id . "','" . (($data_type == 'leads') ? "Assign" : "Reassign") . " Staff')";
				$followup_url = "smallAjaxModal('" . base_url() . "modal/popup_inventory/customer_followup_modal/" . $id . "','" . "Add Follow-Up')";
				$add_call_url = "smallAjaxModal('" . base_url() . "modal/popup_inventory/customer_add_call_modal/" . $id . "','" . "Add Call')";
				$timeline_url = "showRightCanvas('" . base_url() . "modal/popup_inventory/canvas_customer_timeline/" . $id . "','Timeline')";
				$share_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/customer_share_modal/" . $id . "','Share Customer')";

				$action = '';
				if($data_type == 'customer') {
					if($status == 'moved') {
						$action .= '
							<a href="javascript:void(0);" onclick="' . $timeline_url . '" class=""  data-toggle="tooltip" data-bs-placement="top" title="Timeline"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" ><i class="fa fa-file" aria-hidden="true"></i></button></a>
						';
					} else {
						if(in_array($status, ['today', 'upcoming', 'missed'])) {
							$action .= '
								<a href="javascript:void(0);" onclick="' . $followup_url . '" data-toggle="tooltip" data-bs-placement="top" title="Add Follow-Up"><button type="button" class="btn mr-1 mb-1 icon-btn-approved"><i class="fa fa-list-alt" aria-hidden="true"></i></button></a>
							';

							$action .= '
								<a href="javascript:void(0);" onclick="' . $add_call_url . '" data-toggle="tooltip" data-bs-placement="top" title="Add Call"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-phone" aria-hidden="true"></i></button></a>
							';

							$action .= '
								<a href="javascript:void(0);" onclick="' . $timeline_url . '" class=""  data-toggle="tooltip" data-bs-placement="top" title="Timeline"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" ><i class="fa fa-file" aria-hidden="true"></i></button></a>
							';
						} else {
							$action ='<div class="btn-group">
								<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
								<i class="mdi mdi-dots-vertical"></i></button>
								<div class="dropdown-menu">
									<a class="dropdown-item" href="' . $edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit</a>
									<a class="dropdown-item" href="javascript:void(0)" onclick="' . $delete_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Cancel</a>
									<a class="dropdown-item d-none" href="javascript:void(0)" onclick="' . $replicate_url . '"><i class="fa fa-refresh" aria-hidden="true"></i> Replicate</a>
									<a class="dropdown-item" href="javascript:void(0)" onclick="' . $reassign_url . '"><i class="fa fa-refresh" aria-hidden="true"></i> ' . (($data_type == 'leads') ? "Assign" : "Reassign") . '</a>
									<a class="dropdown-item" href="javascript:void(0)" onclick="' . $timeline_url . '"><i class="fa fa-file" aria-hidden="true"></i> Timeline</a>
									<a class="dropdown-item" href="javascript:void(0)" onclick="' . $share_url . '"><i class="fa fa-share" aria-hidden="true"></i> Share Customer</a>
									<a class="dropdown-item" href="' . base_url() . 'inventory/customer/ledger/' . $id . '"><i class="fa fa-book" aria-hidden="true"></i> Ledger</a>
								</div>
							</div>';
						}
					}
				} else {
					if($_REQUEST['status'] == 'all') {
						$action .= '
							<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
			
							<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
			
							<a href="javascript:void(0);" onclick="' . $reassign_url . '" data-toggle="tooltip" data-bs-placement="top" title="' . (($data_type == 'leads') ? "Assign" : "Reassign") . ' Staff"><button type="button" class="btn mr-1 mb-1 icon-btn-approved"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
						';
					} else{

						if($_REQUEST['status'] != 'lost') {
							$action .= '
								<a href="javascript:void(0);" onclick="' . $followup_url . '" data-toggle="tooltip" data-bs-placement="top" title="Add Follow-Up"><button type="button" class="btn mr-1 mb-1 icon-btn-approved"><i class="fa fa-list-alt" aria-hidden="true"></i></button></a>
							';
	
							if($_REQUEST['status'] != 'moved') {
								$action .= '
								<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
								';
							}
								
							$action .= '
								<a href="' . $move_url . '" data-toggle="tooltip" data-bs-placement="top" title="Move To Customer"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-chevron-right" aria-hidden="true"></i></button></a>
							';
						}

						
						$action .= '
							<a href="javascript:void(0);" onclick="' . $timeline_url . '" class=""  data-toggle="tooltip" data-bs-placement="top" title="Timeline"><button type="button" class="btn mr-1 mb-1 icon-btn-pass" ><i class="fa fa-file" aria-hidden="true"></i></button></a>
						';
					} 
				}

				$log = $this->common_model->getRowById('customer_log', 'added_by_name', ['customer_id' => $item['id'], 'action' => 'create']);

				$type_badge = ($item['type'] == 'leads') 
					? '<span class="badge bg-light-warning text-warning" style="background: #ff9f4330 !important;">Leads</span>' 
					: '<span class="badge bg-light-primary text-primary">Customer</span>';

				$data[] = array(
					"sr_no"       		=> ++$start,
					"id"          		=> $item['id'],
					"name"        		=> $item['company_name'],
					"gst_name"				=> ($item['gst_name']) ? $item['gst_name'] : '-',
					"gst_no"					=> ($item['gst_no']) ? $item['gst_no'] : '-',
					"owner_name"			=> ($item['owner_name']) ? $item['owner_name'] : '-',
					"owner_no"				=> ($item['owner_mobile']) ? $item['owner_mobile'] : '-',
					"type"					=> $type_badge,
					"city_name"				=> ($item['city_name']) ? $item['city_name'] : '-',
					"state_name"			=> ($item['state_name']) ? $item['state_name'] : '-',
					"pincode"					=> ($item['pincode']) ? $item['pincode'] : '-',
					"staff"						=> ($item['added_by_name']) ? $item['added_by_name'] : '-',
					"move_date"				=> date('d-m-Y', strtotime($item['move_date'])),
					"status_date"				=> (!empty($item['status_date']) && $item['status_date'] != '0000-00-00 00:00:00') ? date('d-m-Y h:i A', strtotime($item['status_date'])) : '-',
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

	public function get_sales_order_details($id)
	{
		$data = [];
		$this->db->where('id', $id);
		$data = $this->db->get('sales_order')->row_array();

		$this->db->where('order_id', $id);
		$data['products'] = $this->db->get('sales_order_product')->result_array();

		$this->db->where('order_id', $id);
		$data['other_charges'] = $this->db->get('sales_order_charges')->result_array();
		
		return $data;
	}

	public function approve_sales_order($id)
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_order_updated_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);

			$sales_record = $this->common_model->getRowById('sales_order', '*', ['id' => $id]);
			
			$customer_id = (int)$sales_record['customer_id'];
			$cust_record = $this->db->select('added_by_id, is_distributor')->where('id', $customer_id)->get('customer')->row_array();
			$sale_person_id = isset($cust_record['added_by_id']) ? (int)$cust_record['added_by_id'] : 0;
			$is_distributor = isset($cust_record['is_distributor']) ? (int)$cust_record['is_distributor'] : 0;
			$sales_products = $this->common_model->getResultById('sales_order_product', '*', ['order_id' => $id]);
			$sales_batches = $this->common_model->getResultById('sales_order_product_batch', '*', ['order_id' => $id]);

			$this->db->insert('sales_history', [
				'parent_id' => $id,
				'json' => json_encode([
					'sales' => $sales_record,
					'products' => $sales_products,
					'batches' => $sales_batches,
				])
			]);

			$warehouse_id = $this->input->post('warehouse_id');
			$warehouse_name = '';
			if ($warehouse_id != '') {
				$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
			}

			$round_of       	= ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
			$gst_type       	= clean_and_escape($this->input->post('gst_type'));

			$other_charges_name   = clean_and_escape($this->input->post('other_charges_name'));
			$other_charges_amount = ($this->input->post('other_charges_amount') != '') ? $this->input->post('other_charges_amount') : 0;
			$basic_value          = price_format_decimal($this->input->post('basic_value'));
			$net_sales_value_1    = price_format_decimal($this->input->post('net_sales_value_1'));
			$total_black_amt      = price_format_decimal($this->input->post('total_black_amount_summary'));
			$net_sales_value_2    = price_format_decimal($this->input->post('net_sales_value_2'));
			$grand_total          = price_format_decimal($this->input->post('grand_total'));
			$central_gst          = price_format_decimal($this->input->post('central_gst'));
			$state_gst            = price_format_decimal($this->input->post('state_gst'));
			$igst                 = price_format_decimal($this->input->post('igst'));
			$gst_total            = ($gst_type == 'IGST') ? $igst : ($central_gst + $state_gst);

			$data = array();
			$data['sale_person_id']         = $sale_person_id;
			$data['is_distributor']         = $is_distributor;
			$data['refrence_no']       		= clean_and_escape($this->input->post('refrence_no'));
			$data['date']     		   	 		= ($this->input->post('date'));
			$data['warehouse_id']      		= $warehouse_id;
			$data['warehouse_name']    		= $warehouse_name;
			$data['remark'] 		   		 		= ($this->input->post('remark'));
			$data['narration']         		= ($this->input->post('narration'));
			$data['is_approved']					= 1;
			$data['gst_type']     	   		= $gst_type;
			$data['igst_per']     	   		= 0;
			$data['cgst_per']     	   		= 0;
			$data['sgst_per']     	   		= 0;
			$data['basic_value']          = $basic_value;
			$data['net_sales_value_1']    = $net_sales_value_1;
			$data['total_black_amt']      = $total_black_amt;
			$data['central_gst']          = $central_gst;
			$data['state_gst']            = $state_gst;
			$data['igst']                 = $igst;
			$data['gst_total']            = $gst_total;
			$data['net_sales_value_2']    = $net_sales_value_2;
			$data['round_of']             = $round_of;
			$data['grand_total']          = $grand_total;
			$data['other_charges_name']   = $other_charges_name;
			$data['other_charges_amount'] = $other_charges_amount;

			$shipping_state_id = $this->input->post('shipping_state_id');
			$shipping_city_id  = $this->input->post('shipping_city_id');
			$shipping_pincode  = clean_and_escape($this->input->post('shipping_pincode'));
			$shipping_gst      = clean_and_escape($this->input->post('shipping_gst'));
			$shipping_gst_no   = clean_and_escape($this->input->post('shipping_gst_no'));
			$shipping_address  = clean_and_escape($this->input->post('shipping_address'));

			$billing_state_id  = $this->input->post('billing_state_id');
			$billing_city_id   = $this->input->post('billing_city_id');
			$billing_pincode   = clean_and_escape($this->input->post('billing_pincode'));
			$billing_gst       = clean_and_escape($this->input->post('billing_gst'));
			$billing_gst_no    = clean_and_escape($this->input->post('billing_gst_no'));
			$billing_address   = clean_and_escape($this->input->post('billing_address'));

			$data['shipping_state_id']   = $shipping_state_id;
			$data['shipping_state_name'] = ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '';
			$data['shipping_city_id']    = $shipping_city_id;
			$data['shipping_city_name']  = ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '';
			$data['shipping_pincode']    = $shipping_pincode;
			$data['shipping_gst']        = $shipping_gst;
			$data['shipping_gst_no']     = $shipping_gst_no;
			$data['shipping_address']    = $shipping_address;

			$data['billing_state_id']    = $billing_state_id;
			$data['billing_state_name']  = ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '';
			$data['billing_city_id']     = $billing_city_id;
			$data['billing_city_name']   = ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '';
			$data['billing_pincode']     = $billing_pincode;
			$data['billing_gst']         = $billing_gst;
			$data['billing_gst_no']      = $billing_gst_no;
			$data['billing_address']     = $billing_address;

			$this->db->where('id', $id)->update('sales_order', $data);
			$order_id = $id;

			$old_sales_charges = $this->common_model->getResultById('sales_order_charges', '*', ['order_id' => $id]);

			$log_json = array(
				'old_sale_order' => array(
					'sale_order'    => $sales_record,
					'products'      => $sales_products,
					'other_charges' => $old_sales_charges,
					'batches'       => $sales_batches
				),
				'new_sale_order' => array(
					'sale_order'    => $data,
					'products'      => array(),
					'other_charges' => array()
				)
			);

			// Delete existing charges
			$this->db->where('order_id', $order_id)->delete('sales_order_charges');

			$charge_id_arr = $this->input->post('charge_id');
			$charge_gst_arr = $this->input->post('charge_gst');
			$charge_price_arr = $this->input->post('charge_price');
			$charge_total_arr = $this->input->post('charge_total');

			if(!empty($charge_id_arr)) {
				for ($i = 0; $i < count($charge_id_arr); $i++) {
					if (!empty($charge_id_arr[$i])) {
						$type_id = $charge_id_arr[$i];
						
						$other_charge = $this->db->get_where('other_charges', ['id' => $type_id])->row_array();
						$type_name = $other_charge ? $other_charge['name'] : '';

						$data_charge = array(
							'order_id'   => $order_id,
							'type_id'    => $type_id,
							'type'       => $type_name,
							'gst'        => (float) ($charge_gst_arr[$i] ?? 0),
							'amount'     => (float) ($charge_price_arr[$i] ?? 0),
							'total_amt'  => (float) ($charge_total_arr[$i] ?? 0),
						);
						$this->db->insert('sales_order_charges', $data_charge);
						$log_json['new_sale_order']['other_charges'][] = $data_charge;
					}
				}
			}

			$x_value_arr = ($this->input->post('x_value'));
			$old_id_arr = ($this->input->post('old_id'));
			$quantity_arr = ($this->input->post('quantity'));

			$batch_id                 = $this->input->post('batch_id');
			$available_white_qty      = $this->input->post('available_white_qty');
			$available_black_qty      = $this->input->post('available_black_qty');
			$batch_white_qty          = $this->input->post('batch_white_qty');
			$batch_black_qty          = $this->input->post('batch_black_qty');
			$batch_rate               = $this->input->post('batch_rate');
			$batch_remark             = $this->input->post('batch_remark');
			$batch_bill_amount        = $this->input->post('batch_bill_amount');
			$batch_bill_remark        = $this->input->post('batch_bill_remark');
			$batch_bill_total         = $this->input->post('batch_bill_total');
			$batch_gst_per            = $this->input->post('batch_gst_per');
			$batch_gst_amt            = $this->input->post('batch_gst_amt');
			$batch_total_bill_gst     = $this->input->post('batch_total_bill_gst_amount');
			$batch_black_amt          = $this->input->post('batch_black_amt');
			$batch_black_total_amt    = $this->input->post('batch_black_total_amt');
			$batch_final_total        = $this->input->post('batch_final_total');
			
			// Delete existing sales commission records if any (e.g. for re-approval)
			$this->db->where('order_id', $id)->delete('sales_commission');

			// Delete existing replacement products if any (e.g. for re-approval)
			$this->db->where('order_id', $id)->delete('replace_products');

			$total_white_qty_sum = 0;
			for ($in = 0; $in < count($x_value_arr); $in++) {
				$i = $x_value_arr[$in];
				
				$order_product_id = $old_id_arr[$in];
				$product_log_data = $this->db->where('id', $order_product_id)->get('sales_order_product')->row_array();
				$product_log_data['batches'] = array();

				$product_id = (int)$product_log_data['product_id'];

				$product_total_amt = 0;
				foreach($batch_id[$i] as $index => $bid) {
					$batch_detail = $this->db->where('id', $bid)->get('inventory')->row_array();

					$data_product_bat = array();
					$data_product_bat = array(
						'order_id'          => $order_id,
						'order_product_id'  => $old_id_arr[$in],
						'batch_no'      		=> $batch_detail['batch_no'],
						'batch_qty'       	=> $batch_detail['quantity'],

						'avail_white_qty'		=> $batch_detail['official_qty'],
						'avail_black_qty'		=> $batch_detail['black_qty'],
						'qty'								=> $quantity_arr[$in],
						'white_qty'					=> $batch_white_qty[$i][$index],
						'black_qty'					=> $batch_black_qty[$i][$index],
						'recieved_black_qty'=> ($batch_detail['black_qty'] < $batch_black_qty[$i][$index]) ? $batch_detail['black_qty'] : $batch_black_qty[$i][$index],

						'amount'						=> $batch_rate[$i][$index],
						'remark'						=> isset($batch_remark[$i][$index]) ? clean_and_escape($batch_remark[$i][$index]) : null,
						'bill_amount'				=> $batch_bill_amount[$i][$index],
						'bill_remark'				=> isset($batch_bill_remark[$i][$index]) ? clean_and_escape($batch_bill_remark[$i][$index]) : null,
						'bill_total'				=> $batch_bill_total[$i][$index],
						'gst'								=> $batch_gst_per[$i][$index],
						'gst_amount'				=> $batch_gst_amt[$i][$index],
						'total_bill_gst_amount'	=> $batch_total_bill_gst[$i][$index],
						'black_amount'			=> $batch_black_amt[$i][$index],
						'black_total'				=> $batch_black_total_amt[$i][$index],
						'final_total'				=> $batch_final_total[$i][$index],
						'added_date'        => date('Y-m-d H:i:s'),
					);

					$this->db->insert('sales_order_product_batch', $data_product_bat);
					$product_log_data['batches'][] = $data_product_bat;
					$total_white_qty_sum += (float) ($batch_white_qty[$i][$index] ?? 0);

					$allocated_qty = (float)($batch_white_qty[$i][$index] + $batch_black_qty[$i][$index]);
					$product_total_amt = $product_total_amt + ($allocated_qty * (float) $batch_rate[$i][$index]);

					if($batch_detail['quantity'] < ($batch_white_qty[$i][$index] + $batch_black_qty[$i][$index]) || $batch_detail['quantity'] == 0) {
						throw new Exception('Insufficient stock for ' . $product_name . '. Available Live Qty: ' . $stocks . '.');
					} else {
						
						$pending_qty = 0;
						if($batch_detail['black_qty'] < $batch_black_qty[$i][$index]){
							$this->db->where('id', $order_id)->update('sales_order', ["is_weird" => 1]);
							$pending_qty = ($batch_black_qty[$i][$index] - $batch_detail['black_qty']);
						}

						$this->db->where('id', $bid)->update('inventory', array(
							'quantity' => $batch_detail['quantity'] - ($batch_white_qty[$i][$index] + $batch_black_qty[$i][$index]),
							'black_qty' => ($batch_detail['black_qty'] >= $batch_black_qty[$i][$index]) ? $batch_detail['black_qty'] - $batch_black_qty[$i][$index] : 0,
							'official_qty' => ($batch_detail['black_qty'] >= $batch_black_qty[$i][$index]) ? $batch_detail['official_qty'] - $batch_white_qty[$i][$index] : $batch_detail['official_qty'] - $batch_white_qty[$i][$index] - ($batch_black_qty[$i][$index] - $batch_detail['black_qty']),
							'pending_qty' => $batch_detail['pending_qty'] + $pending_qty
						));

						$inv_his = [
							'supplier_id' 			=> $batch_detail["supplier_id"],
							'parent_id' 				=> $bid,
							'company_id' 				=> $this->session->userdata('company_id'),
							'warehouse_id' 			=> $batch_detail["warehouse_id"],
							'warehouse_name' 		=> $batch_detail["warehouse_name"],
							'product_id' 				=> $batch_detail["product_id"],
							'categories' 				=> $batch_detail["categories"],
							'batch_no' 					=> $batch_detail["voucher_no"],
							'product_name'			=> $batch_detail['product_name'] ?? '',
							'item_code'					=> $batch_detail['item_code'] ?? '',
							'sku'         			=> $batch_detail['sku'] ?? '',
							'order_id'        	=> $order_id,
							'status'        		=> 'out',
							'quantity'        	=> ($batch_white_qty[$i][$index] + $batch_black_qty[$i][$index]),

							'actual_rmb'        => 0,
							'total_rmb'         => 0,
							'actual_usd'        => 0,
							'actual_inr'        => 0,
							'official_qty'      => $batch_white_qty[$i][$index],
							'official_rate_rs'  => $batch_bill_amount[$i][$index],
							'official_total_rs' => $batch_bill_total[$i][$index],
							'black_qty'         => $batch_black_qty[$i][$index],
							'pending_qty' 			=> $pending_qty,
							'black_rate_rs'  		=> $batch_black_amt[$i][$index],
							'black_total_rs' 		=> $batch_black_total_amt[$i][$index],
							'duty_percent'      => 0,
							'duty_amt'          => 0,
							'duty_surcharge'    => 0,
							'taxable_value'     => $batch_bill_total[$i][$index],
							'gst_amt'           => $batch_gst_amt[$i][$index],
							'total_amt'         => $batch_final_total[$i][$index],	
							
							'received_date'     => date('Y-m-d'),
							'invoice_no'        => 1,	
							'added_date'        => date('Y-m-d H:i:s'),
							"added_by_id"       => $this->session->userdata('super_user_id'),
							"added_by_name"     => $this->session->userdata('super_name'),
						];

						$this->db->insert('inventory_history', $inv_his);
					}
				}

				$is_replace = ($this->input->post('replace_product_chk_' . $i) == 1) ? 1 : 0;
				if ($is_replace) {
					// Retrieve product details
					$raw_prod = $this->db->select('name, item_code')->where('id', $product_id)->get('raw_products')->row_array();
					$item_name = $raw_prod['name'] ?? '';
					$item_code = $raw_prod['item_code'] ?? '';
					if ($item_code == '') {
						$inv_prod = $this->db->where('product_id', $product_id)->get('inventory')->row_array();
						$item_code = $inv_prod['item_code'] ?? '';
					}

					$replace_data = array(
						'order_id' => $order_id,
						'order_prod_id' => $order_product_id,
						'type' => 'pending',
						'product_id' => $product_id,
						'product_name' => $item_name,
						'item_code' => $item_code,
						'qty' => (int) ($quantity_arr[$in] ?? 0),
						'added_by' => $this->session->userdata('super_user_id')
					);
					$this->db->insert('replace_products', $replace_data);
				}

				$log_json['new_sale_order']['products'][] = $product_log_data;
			}

			// Save commissions using batch-wise logic helper function
			$this->save_sales_order_commissions($order_id);

			if ($total_white_qty_sum == 0) {
				$this->db->where('id', $order_id)->update('sales_order', ['is_generated' => 1]);
			}

			$log_data = array(
				'parent_id'      => $order_id,
				'ref_id'         => NULL,
				'module'         => 'sales',
				'action'         => 'approve',
				'message'        => 'Sale Order approved by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($log_json),
				'table_name'     => 'sales_order',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			$this->session->set_flashdata('flash_message', get_phrase('sales_order_added_successfully'));

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
			
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" =>  "Exception occurred: " . $e->getMessage(),
			);
		}

		return simple_json_output($resultpost);
	}

	public function edit_sales_order($id)
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_order_updated_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);

			$sales_record = $this->common_model->getRowById('sales_order', '*', ['id' => $id]);
			$sales_products = $this->common_model->getResultById('sales_order_product', '*', ['order_id' => $id]);
			$sales_batches = $this->common_model->getResultById('sales_order_product_batch', '*', ['order_id' => $id]);

			$this->db->insert('sales_history', [
				'parent_id' => $id,
				'json' => json_encode([
					'sales' => $sales_record,
					'products' => $sales_products,
					'batches' => $sales_batches,
				])
			]);

			$old_sales_record = $this->common_model->getRowById('sales_order', '*', ['id' => $id]);
			$old_sales_products = $this->common_model->getResultById('sales_order_product', '*', ['order_id' => $id]);
			$old_sales_charges = $this->common_model->getResultById('sales_order_charges', '*', ['order_id' => $id]);

			$customer_id = $this->input->post('customer_id');
			if ($customer_id != '') {
				$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
			} else {
				$customer_name = '';
			}

			$sale_person_id = 0;
			$is_distributor = 0;
			if ($customer_id != '') {
				$cust_record = $this->db->select('added_by_id, is_distributor')->where('id', $customer_id)->get('customer')->row_array();
				$sale_person_id = isset($cust_record['added_by_id']) ? (int)$cust_record['added_by_id'] : 0;
				$is_distributor = isset($cust_record['is_distributor']) ? (int)$cust_record['is_distributor'] : 0;
			}

			$warehouse_id = $this->input->post('warehouse_id');
			$warehouse_name = '';
			if ($warehouse_id != '') {
				$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
			}

			$round_of       	= ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
			$gst_type       	= clean_and_escape($this->input->post('gst_type'));

			$other_charges_name   = clean_and_escape($this->input->post('other_charges_name'));
			$other_charges_amount = ($this->input->post('other_charges_amount') != '') ? $this->input->post('other_charges_amount') : 0;
			$basic_value          = price_format_decimal($this->input->post('basic_value'));
			$net_sales_value_1    = price_format_decimal($this->input->post('net_sales_value_1'));
			$total_black_amt      = price_format_decimal($this->input->post('total_black_amount_summary'));
			$net_sales_value_2    = price_format_decimal($this->input->post('net_sales_value_2'));
			$grand_total          = price_format_decimal($this->input->post('grand_total'));
			$central_gst          = price_format_decimal($this->input->post('central_gst'));
			$state_gst            = price_format_decimal($this->input->post('state_gst'));
			$igst                 = price_format_decimal($this->input->post('igst'));
			$gst_total            = ($gst_type == 'IGST') ? $igst : ($central_gst + $state_gst);

			$data = array();
			$data['sale_person_id']         = $sale_person_id;
			$data['is_distributor']         = $is_distributor;
			$data['refrence_no']       		= clean_and_escape($this->input->post('refrence_no'));
			$data['date']     		   	 		= ($this->input->post('date'));
			$data['customer_id']       		= $customer_id;
			$data['customer_name']     		= $customer_name;
			$data['warehouse_id']      		= $warehouse_id;
			$data['warehouse_name']    		= $warehouse_name;
			$data['remark'] 		   		 		= ($this->input->post('remark'));
			$data['narration']         		= ($this->input->post('narration'));
			$data['gst_type']     	   		= $gst_type;
			$data['igst_per']     	   		= 0;
			$data['cgst_per']     	   		= 0;
			$data['sgst_per']     	   		= 0;
			$data['basic_value']          = $basic_value;
			$data['net_sales_value_1']    = $net_sales_value_1;
			$data['total_black_amt']      = $total_black_amt;
			$data['central_gst']          = $central_gst;
			$data['state_gst']            = $state_gst;
			$data['igst']                 = $igst;
			$data['gst_total']            = $gst_total;
			$data['net_sales_value_2']    = $net_sales_value_2;
			$data['round_of']             = $round_of;
			$data['grand_total']          = $grand_total;
			$data['other_charges_name']   = $other_charges_name;
			$data['other_charges_amount'] = $other_charges_amount;

			$shipping_state_id = $this->input->post('shipping_state_id');
			$shipping_city_id  = $this->input->post('shipping_city_id');
			$shipping_pincode  = clean_and_escape($this->input->post('shipping_pincode'));
			$shipping_gst      = clean_and_escape($this->input->post('shipping_gst'));
			$shipping_gst_no   = clean_and_escape($this->input->post('shipping_gst_no'));
			$shipping_address  = clean_and_escape($this->input->post('shipping_address'));

			$billing_state_id  = $this->input->post('billing_state_id');
			$billing_city_id   = $this->input->post('billing_city_id');
			$billing_pincode   = clean_and_escape($this->input->post('billing_pincode'));
			$billing_gst       = clean_and_escape($this->input->post('billing_gst'));
			$billing_gst_no    = clean_and_escape($this->input->post('billing_gst_no'));
			$billing_address   = clean_and_escape($this->input->post('billing_address'));

			$data['shipping_state_id']   = $shipping_state_id;
			$data['shipping_state_name'] = ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '';
			$data['shipping_city_id']    = $shipping_city_id;
			$data['shipping_city_name']  = ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '';
			$data['shipping_pincode']    = $shipping_pincode;
			$data['shipping_gst']        = $shipping_gst;
			$data['shipping_gst_no']     = $shipping_gst_no;
			$data['shipping_address']    = $shipping_address;

			$data['billing_state_id']    = $billing_state_id;
			$data['billing_state_name']  = ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '';
			$data['billing_city_id']     = $billing_city_id;
			$data['billing_city_name']   = ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '';
			$data['billing_pincode']     = $billing_pincode;
			$data['billing_gst']         = $billing_gst;
			$data['billing_gst_no']      = $billing_gst_no;
			$data['billing_address']     = $billing_address;

			$this->db->where('id', $id)->update('sales_order', $data);
			$order_id = $id;

			// Delete existing products for this order
			$this->db->where('order_id', $order_id)->delete('sales_order_product');

			// Delete existing replacement products for this order
			$this->db->where('order_id', $order_id)->delete('replace_products');

			$added_by_id = isset($old_sales_record['added_by_id']) ? (int)$old_sales_record['added_by_id'] : (int)$this->session->userdata('super_user_id');

			$product_id_arr     = ($this->input->post('product_id'));
			$quantity_arr       = ($this->input->post('quantity'));
			$master_amount_arr  = ($this->input->post('master_amount'));
			$total_amount_arr   = ($this->input->post('total_amount'));
			$bill_amount_arr    = ($this->input->post('bill_amount'));
			$gst_arr       			= ($this->input->post('gst'));
			$gst_amount_arr     = ($this->input->post('gst_amount'));
			$bill_total_arr     = ($this->input->post('bill_total'));
			$total_bill_gst_amount_arr = ($this->input->post('total_bill_gst_amount'));
			$black_amt_arr       = ($this->input->post('black_amt'));
			$black_total_arr		 = ($this->input->post('black_total'));
			$final_total_arr    = ($this->input->post('final_total'));
			$available_arr			= ($this->input->post('available'));
			$x_value_arr        = ($this->input->post('x_value'));

			$log_json = array(
				'old_sale_order' => array(
					'sale_order'    => $old_sales_record,
					'products'      => $old_sales_products,
					'other_charges' => $old_sales_charges
				),
				'new_sale_order' => array(
					'sale_order'    => $data,
					'products'      => array(),
					'other_charges' => array()
				)
			);

			for ($i = 0; $i < count($product_id_arr); $i++) {
				if ($quantity_arr[$i] > 0) {
					$xpro 			=  explode('|', $product_id_arr[$i]);
					$product_id 	= $xpro[0];

					$product    	= $this->crud_model->get_raw_products_by_id($product_id)->row_array();
					if (empty($product)) {
						throw new Exception('No Product Found');
					}

					$item_code = $product['item_code'] ?? '';
					if ($item_code == '') {
						$inv_prod = $this->db->where('product_id', $product_id)->get('inventory')->row_array();
						$item_code = $inv_prod['item_code'] ?? '';
					}

					$data_product = array(
						'order_id'                => $order_id,
						'product_id'              => $product_id,
						'item_code'               => $item_code,
						'product_name'            => $product['name'],
						'qty'                     => (float) ($quantity_arr[$i] ?? 0),
						'amount'                  => (float) ($master_amount_arr[$i] ?? 0),
						'total_amount'            => (float) ($total_amount_arr[$i] ?? 0),
						'bill_amount'             => (float) ($bill_amount_arr[$i] ?? 0),
						'bill_total'              => (float) ($bill_total_arr[$i] ?? 0),
						'available'               => (float) ($available_arr[$i] ?? 0),
						'gst'                     => (float) ($gst_arr[$i] ?? 0),
						'gst_amount'              => (float) ($gst_amount_arr[$i] ?? 0),
						'total_bill_gst_amount'   => (float) ($total_bill_gst_amount_arr[$i] ?? 0),
						'black_amount'            => (float) ($black_amt_arr[$i] ?? 0),
						'black_total'             => (float) ($black_total_arr[$i] ?? 0),
						'final_total'             => (float) ($final_total_arr[$i] ?? 0),
					);
					$this->db->insert('sales_order_product', $data_product);
					$order_product_id = $this->db->insert_id();

					$row_index = $x_value_arr[$i];
					$is_replace = ($this->input->post('replace_product_chk_' . $row_index) == 1) ? 1 : 0;
					if ($is_replace) {
						$replace_data = array(
							'order_id' => $order_id,
							'order_prod_id' => $order_product_id,
							'type' => 'pending',
							'product_id' => $product_id,
							'product_name' => $product['name'],
							'item_code' => $item_code,
							'qty' => (int) ($quantity_arr[$i] ?? 0),
							'added_by' => $this->session->userdata('super_user_id')
						);
						$this->db->insert('replace_products', $replace_data);
					}

					$log_json['new_sale_order']['products'][] = $data_product;
				}
			}

			// Delete existing charges
			$this->db->where('order_id', $order_id)->delete('sales_order_charges');

			$charge_id_arr = $this->input->post('charge_id');
			$charge_gst_arr = $this->input->post('charge_gst');
			$charge_price_arr = $this->input->post('charge_price');
			$charge_total_arr = $this->input->post('charge_total');

			if(!empty($charge_id_arr)) {
				for ($i = 0; $i < count($charge_id_arr); $i++) {
					if (!empty($charge_id_arr[$i])) {
						$type_id = $charge_id_arr[$i];
						
						$other_charge = $this->db->get_where('other_charges', ['id' => $type_id])->row_array();
						$type_name = $other_charge ? $other_charge['name'] : '';

						$data_charge = array(
							'order_id'   => $order_id,
							'type_id'    => $type_id,
							'type'       => $type_name,
							'gst'        => (float) ($charge_gst_arr[$i] ?? 0),
							'amount'     => (float) ($charge_price_arr[$i] ?? 0),
							'total_amt'  => (float) ($charge_total_arr[$i] ?? 0),
						);
						$this->db->insert('sales_order_charges', $data_charge);
						$log_json['new_sale_order']['other_charges'][] = $data_charge;
					}
				}
			}

			$log_data = array(
				'parent_id'      => $order_id,
				'ref_id'         => NULL,
				'module'         => 'sales',
				'action'         => 'edit',
				'message'        => 'Sale Order edited by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($log_json),
				'table_name'     => 'sales_order',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			$this->session->set_flashdata('flash_message', get_phrase('sales_order_updated_successfully'));

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost = array(
					"status" => 400,
					"message" => "Error occurred while editing Sales Order",
				);
			} else {
				$this->db->trans_commit();
				$resultpost = array(
					"status" => 200,
					"message" => get_phrase('sales_order_updated_successfully'),
					"url" => $this->session->userdata('previous_url'),
				);
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

	public function edit_sales_order_salesman($id)
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_order_updated_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);

			$sales_record = $this->common_model->getRowById('sales_order', '*', ['id' => $id]);
			
			$customer_id = $this->input->post('customer_id');
			$warehouse_id = $this->input->post('warehouse_id');
			
			if (empty($customer_id)) {
				$customer_id = $sales_record['customer_id'];
			}
			if (empty($warehouse_id)) {
				$warehouse_id = $sales_record['warehouse_id'];
			}

			if ($customer_id != '') {
				$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
			} else {
				$customer_name = '';
			}

			$sale_person_id = 0;
			$is_distributor = 0;
			if ($customer_id != '') {
				$cust_record = $this->db->select('added_by_id, is_distributor')->where('id', $customer_id)->get('customer')->row_array();
				$sale_person_id = isset($cust_record['added_by_id']) ? (int)$cust_record['added_by_id'] : 0;
				$is_distributor = isset($cust_record['is_distributor']) ? (int)$cust_record['is_distributor'] : 0;
			}

			$warehouse_name = '';
			if ($warehouse_id != '') {
				$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
			}

			$company_id = $this->session->userdata('company_id');
			if (empty($company_id)) {
				$company_id = $sales_record['company_id'];
			}
			$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

			$round_of       	= ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
			$gst_type       	= clean_and_escape($this->input->post('gst_type'));

			$other_charges_name   = clean_and_escape($this->input->post('other_charges_name'));
			$other_charges_amount = ($this->input->post('other_charges_amount') != '') ? $this->input->post('other_charges_amount') : 0;
			
			$basic_value          = price_format_decimal($this->input->post('basic_value'));
			$net_sales_value_1    = price_format_decimal($this->input->post('net_sales_value_1'));
			$total_black_amt      = price_format_decimal($this->input->post('total_black_amount_summary'));
			$net_sales_value_2    = price_format_decimal($this->input->post('net_sales_value_2'));
			$grand_total          = price_format_decimal($this->input->post('grand_total'));
			$central_gst          = price_format_decimal($this->input->post('central_gst'));
			$state_gst            = price_format_decimal($this->input->post('state_gst'));
			$igst                 = price_format_decimal($this->input->post('igst'));
			$gst_total            = ($gst_type == 'IGST') ? $igst : ($central_gst + $state_gst);

			$data = array();
			$data['sale_person_id']         = $sale_person_id;
			$data['is_distributor']         = $is_distributor;
			$data['refrence_no']       		= clean_and_escape($this->input->post('refrence_no'));
			$data['date']     		   	 		= ($this->input->post('date'));
			$data['customer_id']       		= $customer_id;
			$data['customer_name']     		= $customer_name;
			$data['warehouse_id']      		= $warehouse_id;
			$data['warehouse_name']    		= $warehouse_name;
			$data['remark'] 		   		 		= ($this->input->post('remark'));
			$data['narration']         		= ($this->input->post('narration'));
			$data['gst_type']     	   		= $gst_type;
			$data['igst_per']     	   		= 0;
			$data['cgst_per']     	   		= 0;
			$data['sgst_per']     	   		= 0;
			$data['basic_value']          = $basic_value;
			$data['net_sales_value_1']    = $net_sales_value_1;
			$data['total_black_amt']      = $total_black_amt;
			$data['central_gst']          = $central_gst;
			$data['state_gst']            = $state_gst;
			$data['igst']                 = $igst;
			$data['gst_total']            = $gst_total;
			$data['net_sales_value_2']    = $net_sales_value_2;
			$data['round_of']             = $round_of;
			$data['grand_total']          = $grand_total;
			$data['other_charges_name']   = $other_charges_name;
			$data['other_charges_amount'] = $other_charges_amount;

			$shipping_state_id = $this->input->post('shipping_state_id');
			$shipping_city_id  = $this->input->post('shipping_city_id');
			$shipping_pincode  = clean_and_escape($this->input->post('shipping_pincode'));
			$shipping_gst      = clean_and_escape($this->input->post('shipping_gst'));
			$shipping_gst_no   = clean_and_escape($this->input->post('shipping_gst_no'));
			$shipping_address  = clean_and_escape($this->input->post('shipping_address'));

			$billing_state_id  = $this->input->post('billing_state_id');
			$billing_city_id   = $this->input->post('billing_city_id');
			$billing_pincode   = clean_and_escape($this->input->post('billing_pincode'));
			$billing_gst       = clean_and_escape($this->input->post('billing_gst'));
			$billing_gst_no    = clean_and_escape($this->input->post('billing_gst_no'));
			$billing_address   = clean_and_escape($this->input->post('billing_address'));

			$data['shipping_state_id']   = $shipping_state_id;
			$data['shipping_state_name'] = ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '';
			$data['shipping_city_id']    = $shipping_city_id;
			$data['shipping_city_name']  = ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '';
			$data['shipping_pincode']    = $shipping_pincode;
			$data['shipping_gst']        = $shipping_gst;
			$data['shipping_gst_no']     = $shipping_gst_no;
			$data['shipping_address']    = $shipping_address;

			$data['billing_state_id']    = $billing_state_id;
			$data['billing_state_name']  = ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '';
			$data['billing_city_id']     = $billing_city_id;
			$data['billing_city_name']   = ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '';
			$data['billing_pincode']     = $billing_pincode;
			$data['billing_gst']         = $billing_gst;
			$data['billing_gst_no']      = $billing_gst_no;
			$data['billing_address']     = $billing_address;

			$this->db->where('id', $id)->update('sales_order', $data);
			$order_id = $id;

			// Delete existing products for this order
			$this->db->where('order_id', $order_id)->delete('sales_order_product');
			// Delete existing batches for this order
			$this->db->where('order_id', $order_id)->delete('sales_order_product_batch');
			// Delete existing replacement products for this order
			$this->db->where('order_id', $order_id)->delete('replace_products');
			// Delete existing commissions for this order
			$this->db->where('order_id', $order_id)->delete('sales_commission');
			// Delete existing inventory history status = out for this order
			$this->db->where('order_id', $order_id)->where('status', 'out')->delete('inventory_history');

			$product_id_arr     = ($this->input->post('product_id'));
			$quantity_arr       = ($this->input->post('quantity'));
			$master_amount_arr  = ($this->input->post('master_amount'));
			$total_amount_arr   = ($this->input->post('total_amount'));
			$bill_amount_arr    = ($this->input->post('bill_amount'));
			$gst_arr       			= ($this->input->post('gst'));
			$gst_amount_arr     = ($this->input->post('gst_amount'));
			$bill_total_arr     = ($this->input->post('bill_total'));
			$total_bill_gst_amount_arr = ($this->input->post('total_bill_gst_amount'));
			$black_amt_arr       = ($this->input->post('black_amt'));
			$black_total_arr		 = ($this->input->post('black_total'));
			$final_total_arr    = ($this->input->post('final_total'));
			$available_arr			= ($this->input->post('available'));
			$x_value_arr        = ($this->input->post('x_value'));

			// Batch fields
			$batch_id                 = $this->input->post('batch_id');
			$available_white_qty      = $this->input->post('available_white_qty');
			$available_black_qty      = $this->input->post('available_black_qty');
			$batch_white_qty          = $this->input->post('batch_white_qty');
			$batch_black_qty          = $this->input->post('batch_black_qty');
			$batch_rate               = $this->input->post('batch_rate');
			$batch_remark             = $this->input->post('batch_remark');
			$batch_bill_amount        = $this->input->post('batch_bill_amount');
			$batch_bill_remark        = $this->input->post('batch_bill_remark');
			$batch_bill_total         = $this->input->post('batch_bill_total');
			$batch_gst_per            = $this->input->post('batch_gst_per');
			$batch_gst_amt            = $this->input->post('batch_gst_amt');
			$batch_total_bill_gst     = $this->input->post('batch_total_bill_gst_amount');
			$batch_black_amt          = $this->input->post('batch_black_amt');
			$batch_black_total_amt    = $this->input->post('batch_black_total_amt');
			$batch_final_total        = $this->input->post('batch_final_total');

			$total_white_qty_sum = 0;
			for ($i = 0; $i < count($product_id_arr); $i++) {
				if ($quantity_arr[$i] > 0) {
					$xpro 			=  explode('|', $product_id_arr[$i]);
					$product_id 	= $xpro[0];

					$product    	= $this->crud_model->get_raw_products_by_id($product_id)->row_array();
					if (empty($product)) {
						throw new Exception('No Product Found');
					}

					$item_code = $product['item_code'] ?? '';
					if ($item_code == '') {
						$inv_prod = $this->db->where('product_id', $product_id)->get('inventory')->row_array();
						$item_code = $inv_prod['item_code'] ?? '';
					}

					$data_product = array(
						'order_id'                => $order_id,
						'product_id'              => $product_id,
						'item_code'               => $item_code,
						'product_name'            => $product['name'],
						'qty'                     => (float) ($quantity_arr[$i] ?? 0),
						'amount'                  => (float) ($master_amount_arr[$i] ?? 0),
						'total_amount'            => (float) ($total_amount_arr[$i] ?? 0),
						'bill_amount'             => (float) ($bill_amount_arr[$i] ?? 0),
						'bill_total'              => (float) ($bill_total_arr[$i] ?? 0),
						'available'               => (float) ($available_arr[$i] ?? 0),
						'gst'                     => (float) ($gst_arr[$i] ?? 0),
						'gst_amount'              => (float) ($gst_amount_arr[$i] ?? 0),
						'total_bill_gst_amount'   => (float) ($total_bill_gst_amount_arr[$i] ?? 0),
						'black_amount'            => (float) ($black_amt_arr[$i] ?? 0),
						'black_total'             => (float) ($black_total_arr[$i] ?? 0),
						'final_total'             => (float) ($final_total_arr[$i] ?? 0),
					);

					$this->db->insert('sales_order_product', $data_product);
					$order_product_id = $this->db->insert_id();

					$row_index = $x_value_arr[$i];
					$is_replace = ($this->input->post('replace_product_chk_' . $row_index) == 1) ? 1 : 0;
					if ($is_replace) {
						$replace_data = array(
							'order_id' => $order_id,
							'order_prod_id' => $order_product_id,
							'type' => 'pending',
							'product_id' => $product_id,
							'product_name' => $product['name'],
							'item_code' => $item_code,
							'qty' => (int) ($quantity_arr[$i] ?? 0),
							'added_by' => $this->session->userdata('super_user_id')
						);
						$this->db->insert('replace_products', $replace_data);
					}

					$product_total_amt = 0;
					if (!empty($batch_id[$row_index])) {
						foreach ($batch_id[$row_index] as $index => $bid) {
							if (empty($bid)) continue;

							$batch_detail = $this->db->where('id', $bid)->get('inventory')->row_array();
							if (empty($batch_detail)) {
								throw new Exception('Batch details not found for batch ID: ' . $bid);
							}

							$allocated_qty = (float)($batch_white_qty[$row_index][$index] + $batch_black_qty[$row_index][$index]);

							$product_total_amt = $product_total_amt + ($allocated_qty * (float) $batch_rate[$row_index][$index]);
							$data_product_bat = array(
								'order_id'          => $order_id,
								'order_product_id'  => $order_product_id,
								'batch_no'      		=> $batch_detail['batch_no'],
								'batch_qty'       	=> $batch_detail['quantity'],

								'avail_white_qty'		=> $batch_detail['official_qty'],
								'avail_black_qty'		=> $batch_detail['black_qty'],
								'qty'								=> $allocated_qty,
								'white_qty'					=> (float) $batch_white_qty[$row_index][$index],
								'black_qty'					=> (float) $batch_black_qty[$row_index][$index],
								'recieved_black_qty'=> ($batch_detail['black_qty'] < $batch_black_qty[$row_index][$index]) ? $batch_detail['black_qty'] : $batch_black_qty[$row_index][$index],

								'amount'						=> (float) $batch_rate[$row_index][$index],
								'remark'						=> isset($batch_remark[$row_index][$index]) ? clean_and_escape($batch_remark[$row_index][$index]) : null,
								'bill_amount'				=> (float) $batch_bill_amount[$row_index][$index],
								'bill_remark'				=> isset($batch_bill_remark[$row_index][$index]) ? clean_and_escape($batch_bill_remark[$row_index][$index]) : null,
								'bill_total'				=> (float) $batch_bill_total[$row_index][$index],
								'gst'								=> (float) $batch_gst_per[$row_index][$index],
								'gst_amount'				=> (float) $batch_gst_amt[$row_index][$index],
								'total_bill_gst_amount'	=> (float) $batch_total_bill_gst[$row_index][$index],
								'black_amount'			=> (float) $batch_black_amt[$row_index][$index],
								'black_total'				=> (float) $batch_black_total_amt[$row_index][$index],
								'final_total'				=> (float) $batch_final_total[$row_index][$index],
								'added_date'        => date('Y-m-d H:i:s'),
							);

							$this->db->insert('sales_order_product_batch', $data_product_bat);
							$total_white_qty_sum += (float) ($batch_white_qty[$row_index][$index] ?? 0);

							// Insert into inventory history (do NOT modify inventory stock quantities!)
							$pending_qty = 0;
							if ($batch_detail['black_qty'] < $batch_black_qty[$row_index][$index]){
								$pending_qty = ($batch_black_qty[$row_index][$index] - $batch_detail['black_qty']);
							}

							$inv_his = [
								'supplier_id' 			=> $batch_detail["supplier_id"],
								'parent_id' 				=> $bid,
								'company_id' 				=> $company_id,
								'warehouse_id' 			=> $batch_detail["warehouse_id"],
								'warehouse_name' 		=> $batch_detail["warehouse_name"],
								'product_id' 				=> $batch_detail["product_id"],
								'categories' 				=> $batch_detail["categories"],
								'batch_no' 					=> $batch_detail["voucher_no"],
								'product_name'			=> $batch_detail['product_name'] ?? '',
								'item_code'					=> $batch_detail['item_code'] ?? '',
								'sku'         			=> $batch_detail['sku'] ?? '',
								'order_id'        	=> $order_id,
								'status'        		=> 'out',
								'quantity'        	=> $allocated_qty,

								'actual_rmb'        => 0,
								'total_rmb'         => 0,
								'actual_usd'        => 0,
								'actual_inr'        => 0,
								'official_qty'      => $batch_white_qty[$row_index][$index],
								'official_rate_rs'  => $batch_bill_amount[$row_index][$index],
								'official_total_rs' => $batch_bill_total[$row_index][$index],
								'black_qty'         => $batch_black_qty[$row_index][$index],
								'pending_qty'       => $pending_qty,
								'black_rate_rs'  		=> $batch_black_amt[$row_index][$index],
								'black_total_rs' 		=> $batch_black_total_amt[$row_index][$index],
								'duty_percent'      => 0,
								'duty_amt'          => 0,
								'duty_surcharge'    => 0,
								'taxable_value'     => $batch_bill_total[$row_index][$index],
								'gst_amt'           => $batch_gst_amt[$row_index][$index],
								'total_amt'         => $batch_final_total[$row_index][$index],	
								
								'received_date'     => date('Y-m-d'),
								'invoice_no'        => 1,	
								'added_date'        => date('Y-m-d H:i:s'),
								"added_by_id"       => $this->session->userdata('super_user_id'),
								"added_by_name"     => $this->session->userdata('super_name'),
							];

							$this->db->insert('inventory_history', $inv_his);
						}
					}
				}
			}

			// Save commissions using batch-wise logic helper function
			$this->save_sales_order_commissions($order_id);

			if ($total_white_qty_sum == 0) {
				$this->db->where('id', $order_id)->update('sales_order', ['is_generated' => 1]);
			} else {
				$this->db->where('id', $order_id)->update('sales_order', ['is_generated' => 0]);
			}

			// Delete existing charges
			$this->db->where('order_id', $order_id)->delete('sales_order_charges');

			$charge_id_arr = $this->input->post('charge_id');
			$charge_gst_arr = $this->input->post('charge_gst');
			$charge_price_arr = $this->input->post('charge_price');
			$charge_total_arr = $this->input->post('charge_total');

			if(!empty($charge_id_arr)) {
				for ($i = 0; $i < count($charge_id_arr); $i++) {
					if (!empty($charge_id_arr[$i])) {
						$type_id = $charge_id_arr[$i];
						
						$other_charge = $this->db->get_where('other_charges', ['id' => $type_id])->row_array();
						$type_name = $other_charge ? $other_charge['name'] : '';

						$data_charge = array(
							'order_id'   => $order_id,
							'type_id'    => $type_id,
							'type'       => $type_name,
							'gst'        => (float) ($charge_gst_arr[$i] ?? 0),
							'amount'     => (float) ($charge_price_arr[$i] ?? 0),
							'total_amt'  => (float) ($charge_total_arr[$i] ?? 0),
						);
						$this->db->insert('sales_order_charges', $data_charge);
					}
				}
			}

			$this->db->trans_commit();
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$resultpost = array(
				"status" => 400,
				"message" => "Exception occurred: " . $e->getMessage(),
			);
		}
		return simple_json_output($resultpost);
	}

	public function add_sales_order()
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_order_added_successfully'),
				"url" => base_url("inventory/sales-order"),
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
					$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
				} else {
					$customer_name = '';
				}

				$sale_person_id = 0;
				$is_distributor = 0;
				if ($customer_id != '') {
					$cust_record = $this->db->select('added_by_id, is_distributor')->where('id', $customer_id)->get('customer')->row_array();
					$sale_person_id = isset($cust_record['added_by_id']) ? (int)$cust_record['added_by_id'] : 0;
					$is_distributor = isset($cust_record['is_distributor']) ? (int)$cust_record['is_distributor'] : 0;
				}
				
				$warehouse_id = $this->input->post('warehouse_id');
				if ($warehouse_id != '') {
					$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
				} else {
					$warehouse_name = '';
				}

				// $company_id = $this->input->post('company_id');
				$company_id = $this->session->userdata('company_id');
				$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

				$round_of       	= ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
				$gst_type       	= clean_and_escape($this->input->post('gst_type'));

				$other_charges_name   = clean_and_escape($this->input->post('other_charges_name'));
				$other_charges_amount = ($this->input->post('other_charges_amount') != '') ? $this->input->post('other_charges_amount') : 0;
				$basic_value          = price_format_decimal($this->input->post('basic_value'));
				$net_sales_value_1    = price_format_decimal($this->input->post('net_sales_value_1'));
				$total_black_amt      = price_format_decimal($this->input->post('total_black_amount_summary'));
				$net_sales_value_2    = price_format_decimal($this->input->post('net_sales_value_2'));
				$grand_total          = price_format_decimal($this->input->post('grand_total'));
				$central_gst          = price_format_decimal($this->input->post('central_gst'));
				$state_gst            = price_format_decimal($this->input->post('state_gst'));
				$igst                 = price_format_decimal($this->input->post('igst'));
				$gst_total            = ($gst_type == 'IGST') ? $igst : ($central_gst + $state_gst);

				$data = array();
				$data['sale_person_id']         = $sale_person_id;
				$data['is_distributor']         = $is_distributor;
				$data['order_no']          		= $order_no;
				$data['refrence_no']       		= clean_and_escape($this->input->post('refrence_no'));
				$data['date']     		   	 		= ($this->input->post('date'));
				$data['customer_id']       		= $customer_id;
				$data['customer_name']     		= $customer_name;
				$data['warehouse_id']      		= $warehouse_id;
				$data['warehouse_name']    		= $warehouse_name;
				$data['company_id']        		= $company_id;
				$data['company_name']      		= $company_name;
				$data['remark'] 		   		 		= ($this->input->post('remark'));
				$data['narration']         		= ($this->input->post('narration'));
				$data['gst_type']     	   		= $gst_type;
				$data['igst_per']     	   		= 0;
				$data['cgst_per']     	   		= 0;
				$data['sgst_per']     	   		= 0;
				$data['basic_value']          = $basic_value;
				$data['net_sales_value_1']    = $net_sales_value_1;
				$data['total_black_amt']      = $total_black_amt;
				$data['central_gst']          = $central_gst;
				$data['state_gst']            = $state_gst;
				$data['igst']                 = $igst;
				$data['gst_total']            = $gst_total;
				$data['net_sales_value_2']    = $net_sales_value_2;
				$data['round_of']             = $round_of;
				$data['grand_total']          = $grand_total;
				$data['other_charges_name']   = $other_charges_name;
				$data['other_charges_amount'] = $other_charges_amount;

				$shipping_state_id = $this->input->post('shipping_state_id');
				$shipping_city_id  = $this->input->post('shipping_city_id');
				$shipping_pincode  = clean_and_escape($this->input->post('shipping_pincode'));
				$shipping_gst      = clean_and_escape($this->input->post('shipping_gst'));
				$shipping_gst_no   = clean_and_escape($this->input->post('shipping_gst_no'));
				$shipping_address  = clean_and_escape($this->input->post('shipping_address'));

				$billing_state_id  = $this->input->post('billing_state_id');
				$billing_city_id   = $this->input->post('billing_city_id');
				$billing_pincode   = clean_and_escape($this->input->post('billing_pincode'));
				$billing_gst       = clean_and_escape($this->input->post('billing_gst'));
				$billing_gst_no    = clean_and_escape($this->input->post('billing_gst_no'));
				$billing_address   = clean_and_escape($this->input->post('billing_address'));

				$data['shipping_state_id']   = $shipping_state_id;
				$data['shipping_state_name'] = ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '';
				$data['shipping_city_id']    = $shipping_city_id;
				$data['shipping_city_name']  = ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '';
				$data['shipping_pincode']    = $shipping_pincode;
				$data['shipping_gst']        = $shipping_gst;
				$data['shipping_gst_no']     = $shipping_gst_no;
				$data['shipping_address']    = $shipping_address;

				$data['billing_state_id']    = $billing_state_id;
				$data['billing_state_name']  = ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '';
				$data['billing_city_id']     = $billing_city_id;
				$data['billing_city_name']   = ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '';
				$data['billing_pincode']     = $billing_pincode;
				$data['billing_gst']         = $billing_gst;
				$data['billing_gst_no']      = $billing_gst_no;
				$data['billing_address']     = $billing_address;

				$data['added_by_id']          = $this->session->userdata('super_user_id');
				$data['added_by_name']        = $this->session->userdata('super_name');
				$data['added_date']   	      = date("Y-m-d H:i:s");

				$log_json = array(
					'sale_order'    => $data,
					'products'      => array(),
					'other_charges' => array()
				);

				if ($this->db->insert('sales_order', $data)) {
					$order_id = $this->db->insert_id();
					$this->update_order_no($order_no);

					$product_id_arr     = ($this->input->post('product_id'));
					$quantity_arr       = ($this->input->post('quantity'));
					$master_amount_arr  = ($this->input->post('master_amount'));
					$total_amount_arr   = ($this->input->post('total_amount'));
					$bill_amount_arr    = ($this->input->post('bill_amount'));
					$gst_arr       			= ($this->input->post('gst'));
					$gst_amount_arr     = ($this->input->post('gst_amount'));
					$bill_total_arr     = ($this->input->post('bill_total'));
					$total_bill_gst_amount_arr = ($this->input->post('total_bill_gst_amount'));
					$black_amt_arr       = ($this->input->post('black_amt'));
					$black_total_arr		 = ($this->input->post('black_total'));
					$final_total_arr    = ($this->input->post('final_total'));
					$available_arr			= ($this->input->post('available'));
					$x_value_arr        = ($this->input->post('x_value'));

					// echo json_encode($quantity_arr); exit();
					for ($i = 0; $i < count($product_id_arr); $i++) {
						if ($quantity_arr[$i] > 0) {
							$xpro 			=  explode('|', $product_id_arr[$i]);
							$product_id 	= $xpro[0];

							$product    	= $this->crud_model->get_raw_products_by_id($product_id)->row_array();
							if (empty($product)) {
								throw new Exception('No Product Found');
							}

							$item_code = $product['item_code'] ?? '';
							if ($item_code == '') {
								$inv_prod = $this->db->where('product_id', $product_id)->get('inventory')->row_array();
								$item_code = $inv_prod['item_code'] ?? '';
							}

							$data_product = array(
								'order_id'                => $order_id,
								'product_id'              => $product_id,
								'item_code'               => $item_code,
								'product_name'            => $product['name'],
								'qty'                     => (float) ($quantity_arr[$i] ?? 0),
								'amount'                  => (float) ($master_amount_arr[$i] ?? 0),
								'total_amount'            => (float) ($total_amount_arr[$i] ?? 0),
								'bill_amount'             => (float) ($bill_amount_arr[$i] ?? 0),
								'bill_total'              => (float) ($bill_total_arr[$i] ?? 0),
								'available'               => (float) ($available_arr[$i] ?? 0),
								'gst'                     => (float) ($gst_arr[$i] ?? 0),
								'gst_amount'              => (float) ($gst_amount_arr[$i] ?? 0),
								'total_bill_gst_amount'   => (float) ($total_bill_gst_amount_arr[$i] ?? 0),
								'black_amount'            => (float) ($black_amt_arr[$i] ?? 0),
								'black_total'             => (float) ($black_total_arr[$i] ?? 0),
								'final_total'             => (float) ($final_total_arr[$i] ?? 0),
							);

							$this->db->insert('sales_order_product', $data_product);
							$order_product_id = $this->db->insert_id();

							$row_index = $x_value_arr[$i];
							$is_replace = ($this->input->post('replace_product_chk_' . $row_index) == 1) ? 1 : 0;
							if ($is_replace) {
								$replace_data = array(
									'order_id' => $order_id,
									'order_prod_id' => $order_product_id,
									'type' => 'pending',
									'product_id' => $product_id,
									'product_name' => $product['name'],
									'item_code' => $item_code,
									'qty' => (int) ($quantity_arr[$i] ?? 0),
									'added_by' => $this->session->userdata('super_user_id')
								);
								$this->db->insert('replace_products', $replace_data);
							}

							$log_json['products'][] = $data_product;
						}
					}

					$charge_id_arr = $this->input->post('charge_id');
					$charge_gst_arr = $this->input->post('charge_gst');
					$charge_price_arr = $this->input->post('charge_price');
					$charge_total_arr = $this->input->post('charge_total');

					if(!empty($charge_id_arr)) {
						for ($i = 0; $i < count($charge_id_arr); $i++) {
							if (!empty($charge_id_arr[$i])) {
								$type_id = $charge_id_arr[$i];
								
								$other_charge = $this->db->get_where('other_charges', ['id' => $type_id])->row_array();
								$type_name = $other_charge ? $other_charge['name'] : '';

								$data_charge = array(
									'order_id'   => $order_id,
									'type_id'    => $type_id,
									'type'       => $type_name,
									'gst'        => (float) ($charge_gst_arr[$i] ?? 0),
									'amount'     => (float) ($charge_price_arr[$i] ?? 0),
									'total_amt'  => (float) ($charge_total_arr[$i] ?? 0),
								);
								$this->db->insert('sales_order_charges', $data_charge);
								$log_json['other_charges'][] = $data_charge;
							}
						}
					}

					$log_data = array(
						'parent_id'      => $order_id,
						'ref_id'         => NULL,
						'module'         => 'sales',
						'action'         => 'add',
						'message'        => 'Sale Order added by ' . $this->session->userdata('super_name'),
						'json'           => json_encode($log_json),
						'table_name'     => 'sales_order',
						'added_by'       => $this->session->userdata('super_user_id'),
						'added_by_email' => $this->session->userdata('super_email'),
						'added_by_name'  => $this->session->userdata('super_name'),
						'added_by_type'  => $this->session->userdata('super_type')
					);
					$this->db->insert('sys_logs', $log_data);

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

	public function add_sales_order_salesman()
	{
		$this->db->trans_begin();
		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_order_added_successfully'),
				"url" => base_url("inventory/sales-order"),
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
					$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
				} else {
					$customer_name = '';
				}

				$sale_person_id = 0;
				$is_distributor = 0;
				if ($customer_id != '') {
					$cust_record = $this->db->select('added_by_id, is_distributor')->where('id', $customer_id)->get('customer')->row_array();
					$sale_person_id = isset($cust_record['added_by_id']) ? (int)$cust_record['added_by_id'] : 0;
					$is_distributor = isset($cust_record['is_distributor']) ? (int)$cust_record['is_distributor'] : 0;
				}
				
				$warehouse_id = $this->input->post('warehouse_id');
				if ($warehouse_id != '') {
					$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
				} else {
					$warehouse_name = '';
				}

				$company_id = $this->session->userdata('company_id');
				$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

				$round_of       	= ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
				$gst_type       	= clean_and_escape($this->input->post('gst_type'));

				$other_charges_name   = clean_and_escape($this->input->post('other_charges_name'));
				$other_charges_amount = ($this->input->post('other_charges_amount') != '') ? $this->input->post('other_charges_amount') : 0;
				$basic_value          = price_format_decimal($this->input->post('basic_value'));
				$net_sales_value_1    = price_format_decimal($this->input->post('net_sales_value_1'));
				$total_black_amt      = price_format_decimal($this->input->post('total_black_amount_summary'));
				$net_sales_value_2    = price_format_decimal($this->input->post('net_sales_value_2'));
				$grand_total          = price_format_decimal($this->input->post('grand_total'));
				$central_gst          = price_format_decimal($this->input->post('central_gst'));
				$state_gst            = price_format_decimal($this->input->post('state_gst'));
				$igst                 = price_format_decimal($this->input->post('igst'));
				$gst_total            = ($gst_type == 'IGST') ? $igst : ($central_gst + $state_gst);

				$data = array();
				$data['sale_person_id']         = $sale_person_id;
				$data['is_distributor']         = $is_distributor;
				$data['order_no']          		= $order_no;
				$data['refrence_no']       		= clean_and_escape($this->input->post('refrence_no'));
				$data['date']     		   	 		= ($this->input->post('date'));
				$data['customer_id']       		= $customer_id;
				$data['customer_name']     		= $customer_name;
				$data['warehouse_id']      		= $warehouse_id;
				$data['warehouse_name']    		= $warehouse_name;
				$data['company_id']        		= $company_id;
				$data['company_name']      		= $company_name;
				$data['remark'] 		   		 		= ($this->input->post('remark'));
				$data['narration']         		= ($this->input->post('narration'));
				$data['gst_type']     	   		= $gst_type;
				$data['is_approved']					= 1;
				$data['igst_per']     	   		= 0;
				$data['cgst_per']     	   		= 0;
				$data['sgst_per']     	   		= 0;
				$data['basic_value']          = $basic_value;
				$data['net_sales_value_1']    = $net_sales_value_1;
				$data['total_black_amt']      = $total_black_amt;
				$data['central_gst']          = $central_gst;
				$data['state_gst']            = $state_gst;
				$data['igst']                 = $igst;
				$data['gst_total']            = $gst_total;
				$data['net_sales_value_2']    = $net_sales_value_2;
				$data['round_of']             = $round_of;
				$data['grand_total']          = $grand_total;
				$data['other_charges_name']   = $other_charges_name;
				$data['other_charges_amount'] = $other_charges_amount;

				$shipping_state_id = $this->input->post('shipping_state_id');
				$shipping_city_id  = $this->input->post('shipping_city_id');
				$shipping_pincode  = clean_and_escape($this->input->post('shipping_pincode'));
				$shipping_gst      = clean_and_escape($this->input->post('shipping_gst'));
				$shipping_gst_no   = clean_and_escape($this->input->post('shipping_gst_no'));
				$shipping_address  = clean_and_escape($this->input->post('shipping_address'));

				$billing_state_id  = $this->input->post('billing_state_id');
				$billing_city_id   = $this->input->post('billing_city_id');
				$billing_pincode   = clean_and_escape($this->input->post('billing_pincode'));
				$billing_gst       = clean_and_escape($this->input->post('billing_gst'));
				$billing_gst_no    = clean_and_escape($this->input->post('billing_gst_no'));
				$billing_address   = clean_and_escape($this->input->post('billing_address'));

				$data['shipping_state_id']   = $shipping_state_id;
				$data['shipping_state_name'] = ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '';
				$data['shipping_city_id']    = $shipping_city_id;
				$data['shipping_city_name']  = ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '';
				$data['shipping_pincode']    = $shipping_pincode;
				$data['shipping_gst']        = $shipping_gst;
				$data['shipping_gst_no']     = $shipping_gst_no;
				$data['shipping_address']    = $shipping_address;

				$data['billing_state_id']    = $billing_state_id;
				$data['billing_state_name']  = ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '';
				$data['billing_city_id']     = $billing_city_id;
				$data['billing_city_name']   = ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '';
				$data['billing_pincode']     = $billing_pincode;
				$data['billing_gst']         = $billing_gst;
				$data['billing_gst_no']      = $billing_gst_no;
				$data['billing_address']     = $billing_address;

				$data['added_by_id']          = $this->session->userdata('super_user_id');
				$data['added_by_name']        = $this->session->userdata('super_name');
				$data['added_date']   	      = date("Y-m-d H:i:s");

				$log_json = array(
					'sale_order'    => $data,
					'products'      => array(),
					'other_charges' => array()
				);

				if ($this->db->insert('sales_order', $data)) {
					$order_id = $this->db->insert_id();
					$this->update_order_no($order_no);

					$product_id_arr     = ($this->input->post('product_id'));
					$quantity_arr       = ($this->input->post('quantity'));
					$master_amount_arr  = ($this->input->post('master_amount'));
					$total_amount_arr   = ($this->input->post('total_amount'));
					$bill_amount_arr    = ($this->input->post('bill_amount'));
					$gst_arr       			= ($this->input->post('gst'));
					$gst_amount_arr     = ($this->input->post('gst_amount'));
					$bill_total_arr     = ($this->input->post('bill_total'));
					$total_bill_gst_amount_arr = ($this->input->post('total_bill_gst_amount'));
					$black_amt_arr       = ($this->input->post('black_amt'));
					$black_total_arr		 = ($this->input->post('black_total'));
					$final_total_arr    = ($this->input->post('final_total'));
					$available_arr			= ($this->input->post('available'));
					$x_value_arr        = ($this->input->post('x_value'));

					// Batch fields
					$batch_id                 = $this->input->post('batch_id');
					$available_white_qty      = $this->input->post('available_white_qty');
					$available_black_qty      = $this->input->post('available_black_qty');
					$batch_white_qty          = $this->input->post('batch_white_qty');
					$batch_black_qty          = $this->input->post('batch_black_qty');
					$batch_rate               = $this->input->post('batch_rate');
					$batch_remark             = $this->input->post('batch_remark');
					$batch_bill_amount        = $this->input->post('batch_bill_amount');
					$batch_bill_remark        = $this->input->post('batch_bill_remark');
					$batch_bill_total         = $this->input->post('batch_bill_total');
					$batch_gst_per            = $this->input->post('batch_gst_per');
					$batch_gst_amt            = $this->input->post('batch_gst_amt');
					$batch_total_bill_gst     = $this->input->post('batch_total_bill_gst_amount');
					$batch_black_amt          = $this->input->post('batch_black_amt');
					$batch_black_total_amt    = $this->input->post('batch_black_total_amt');
					$batch_final_total        = $this->input->post('batch_final_total');

					$total_white_qty_sum = 0;
					for ($i = 0; $i < count($product_id_arr); $i++) {
						if ($quantity_arr[$i] > 0) {
							$xpro 			=  explode('|', $product_id_arr[$i]);
							$product_id 	= $xpro[0];

							$product    	= $this->crud_model->get_raw_products_by_id($product_id)->row_array();
							if (empty($product)) {
								throw new Exception('No Product Found');
							}

							$item_code = $product['item_code'] ?? '';
							if ($item_code == '') {
								$inv_prod = $this->db->where('product_id', $product_id)->get('inventory')->row_array();
								$item_code = $inv_prod['item_code'] ?? '';
							}

							$data_product = array(
								'order_id'                => $order_id,
								'product_id'              => $product_id,
								'item_code'               => $item_code,
								'product_name'            => $product['name'],
								'qty'                     => (float) ($quantity_arr[$i] ?? 0),
								'amount'                  => (float) ($master_amount_arr[$i] ?? 0),
								'total_amount'            => (float) ($total_amount_arr[$i] ?? 0),
								'bill_amount'             => (float) ($bill_amount_arr[$i] ?? 0),
								'bill_total'              => (float) ($bill_total_arr[$i] ?? 0),
								'available'               => (float) ($available_arr[$i] ?? 0),
								'gst'                     => (float) ($gst_arr[$i] ?? 0),
								'gst_amount'              => (float) ($gst_amount_arr[$i] ?? 0),
								'total_bill_gst_amount'   => (float) ($total_bill_gst_amount_arr[$i] ?? 0),
								'black_amount'            => (float) ($black_amt_arr[$i] ?? 0),
								'black_total'             => (float) ($black_total_arr[$i] ?? 0),
								'final_total'             => (float) ($final_total_arr[$i] ?? 0),
							);

							$this->db->insert('sales_order_product', $data_product);
							$order_product_id = $this->db->insert_id();

							$product_log_data = $data_product;
							$product_log_data['id'] = $order_product_id;
							$product_log_data['batches'] = array();

							$row_index = $x_value_arr[$i];
							$is_replace = ($this->input->post('replace_product_chk_' . $row_index) == 1) ? 1 : 0;
							if ($is_replace) {
								$replace_data = array(
									'order_id' => $order_id,
									'order_prod_id' => $order_product_id,
									'type' => 'pending',
									'product_id' => $product_id,
									'product_name' => $product['name'],
									'item_code' => $item_code,
									'qty' => (int) ($quantity_arr[$i] ?? 0),
									'added_by' => $this->session->userdata('super_user_id')
								);
								$this->db->insert('replace_products', $replace_data);
							}

							$row_index = $x_value_arr[$i];

							$product_total_amt = 0;
							if (!empty($batch_id[$row_index])) {
								foreach ($batch_id[$row_index] as $index => $bid) {
									if (empty($bid)) continue;

									$batch_detail = $this->db->where('id', $bid)->get('inventory')->row_array();
									if (empty($batch_detail)) {
										throw new Exception('Batch details not found for batch ID: ' . $bid);
									}

									$allocated_qty = (float)($batch_white_qty[$row_index][$index] + $batch_black_qty[$row_index][$index]);

									$product_total_amt = $product_total_amt + ($allocated_qty * (float) $batch_rate[$row_index][$index]);
									$data_product_bat = array(
										'order_id'          => $order_id,
										'order_product_id'  => $order_product_id,
										'batch_no'      		=> $batch_detail['batch_no'],
										'batch_qty'       	=> $batch_detail['quantity'],

										'avail_white_qty'		=> $batch_detail['official_qty'],
										'avail_black_qty'		=> $batch_detail['black_qty'],
										'qty'								=> $allocated_qty,
										'white_qty'					=> (float) $batch_white_qty[$row_index][$index],
										'black_qty'					=> (float) $batch_black_qty[$row_index][$index],
										'recieved_black_qty'=> ($batch_detail['black_qty'] < $batch_black_qty[$row_index][$index]) ? $batch_detail['black_qty'] : $batch_black_qty[$row_index][$index],

										'amount'						=> (float) $batch_rate[$row_index][$index],
										'remark'						=> isset($batch_remark[$row_index][$index]) ? clean_and_escape($batch_remark[$row_index][$index]) : null,
										'bill_amount'				=> (float) $batch_bill_amount[$row_index][$index],
										'bill_remark'				=> isset($batch_bill_remark[$row_index][$index]) ? clean_and_escape($batch_bill_remark[$row_index][$index]) : null,
										'bill_total'				=> (float) $batch_bill_total[$row_index][$index],
										'gst'								=> (float) $batch_gst_per[$row_index][$index],
										'gst_amount'				=> (float) $batch_gst_amt[$row_index][$index],
										'total_bill_gst_amount'	=> (float) $batch_total_bill_gst[$row_index][$index],
										'black_amount'			=> (float) $batch_black_amt[$row_index][$index],
										'black_total'				=> (float) $batch_black_total_amt[$row_index][$index],
										'final_total'				=> (float) $batch_final_total[$row_index][$index],
										'added_date'        => date('Y-m-d H:i:s'),
									);

									$this->db->insert('sales_order_product_batch', $data_product_bat);
									$product_log_data['batches'][] = $data_product_bat;
									$total_white_qty_sum += (float) ($batch_white_qty[$row_index][$index] ?? 0);

									if ($batch_detail['quantity'] < $allocated_qty || $batch_detail['quantity'] == 0) {
										throw new Exception('Insufficient stock for ' . $product['name'] . ' in batch ' . $batch_detail['batch_no'] . '. Available Live Qty: ' . $batch_detail['quantity'] . '.');
									} else {
										// Update batch quantities
										$new_qty = $batch_detail['quantity'] - $allocated_qty;
										$new_black_qty = ($batch_detail['black_qty'] >= $batch_black_qty[$row_index][$index]) ? $batch_detail['black_qty'] - $batch_black_qty[$row_index][$index] : 0;
										
										$deducted_official = $batch_white_qty[$row_index][$index];
										if ($batch_detail['black_qty'] < $batch_black_qty[$row_index][$index]) {
											$deducted_official += ($batch_black_qty[$row_index][$index] - $batch_detail['black_qty']);
										}
										$new_official_qty = $batch_detail['official_qty'] - $deducted_official;
										if ($new_official_qty < 0) $new_official_qty = 0;

										$pending_qty = 0;
										if ($batch_detail['black_qty'] < $batch_black_qty[$row_index][$index]){
											$this->db->where('id', $order_id)->update('sales_order', ["is_weird" => 1]);

											$pending_qty = ($batch_black_qty[$row_index][$index] - $batch_detail['black_qty']);
										}

										$this->db->where('id', $bid)->update('inventory', array(
											'quantity' => $new_qty,
											'black_qty' => $new_black_qty,
											'official_qty' => $new_official_qty,
											'pending_qty' => $batch_detail['pending_qty'] + $pending_qty
										));

										// Insert into inventory history
										$inv_his = [
											'supplier_id' 			=> $batch_detail["supplier_id"],
											'parent_id' 				=> $bid,
											'company_id' 				=> $company_id,
											'warehouse_id' 			=> $batch_detail["warehouse_id"],
											'warehouse_name' 		=> $batch_detail["warehouse_name"],
											'product_id' 				=> $batch_detail["product_id"],
											'categories' 				=> $batch_detail["categories"],
											'batch_no' 					=> $batch_detail["voucher_no"],
											'product_name'			=> $batch_detail['product_name'] ?? '',
											'item_code'					=> $batch_detail['item_code'] ?? '',
											'sku'         			=> $batch_detail['sku'] ?? '',
											'order_id'        	=> $order_id,
											'status'        		=> 'out',
											'quantity'        	=> $allocated_qty,

											'actual_rmb'        => 0,
											'total_rmb'         => 0,
											'actual_usd'        => 0,
											'actual_inr'        => 0,
											'official_qty'      => $batch_white_qty[$row_index][$index],
											'official_rate_rs'  => $batch_bill_amount[$row_index][$index],
											'official_total_rs' => $batch_bill_total[$row_index][$index],
											'black_qty'         => $batch_black_qty[$row_index][$index],
											'pending_qty'       => $pending_qty,
											'black_rate_rs'  		=> $batch_black_amt[$row_index][$index],
											'black_total_rs' 		=> $batch_black_total_amt[$row_index][$index],
											'duty_percent'      => 0,
											'duty_amt'          => 0,
											'duty_surcharge'    => 0,
											'taxable_value'     => $batch_bill_total[$row_index][$index],
											'gst_amt'           => $batch_gst_amt[$row_index][$index],
											'total_amt'         => $batch_final_total[$row_index][$index],	
											
											'received_date'     => date('Y-m-d'),
											'invoice_no'        => 1,	
											'added_date'        => date('Y-m-d H:i:s'),
											"added_by_id"       => $this->session->userdata('super_user_id'),
											"added_by_name"     => $this->session->userdata('super_name'),
										];

										$this->db->insert('inventory_history', $inv_his);
									}
								}
							} else {
								throw new Exception('No batch allocated for product: ' . $product['name']);
							}

							$log_json['products'][] = $product_log_data;
						}
					}

					// Save commissions using batch-wise logic helper function
					$this->save_sales_order_commissions($order_id);

					if ($total_white_qty_sum == 0) {
						$this->db->where('id', $order_id)->update('sales_order', ['is_generated' => 1]);
					}

					$charge_id_arr = $this->input->post('charge_id');
					$charge_gst_arr = $this->input->post('charge_gst');
					$charge_price_arr = $this->input->post('charge_price');
					$charge_total_arr = $this->input->post('charge_total');

					if(!empty($charge_id_arr)) {
						for ($i = 0; $i < count($charge_id_arr); $i++) {
							if (!empty($charge_id_arr[$i])) {
								$type_id = $charge_id_arr[$i];
								
								$other_charge = $this->db->get_where('other_charges', ['id' => $type_id])->row_array();
								$type_name = $other_charge ? $other_charge['name'] : '';

								$data_charge = array(
									'order_id'   => $order_id,
									'type_id'    => $type_id,
									'type'       => $type_name,
									'gst'        => (float) ($charge_gst_arr[$i] ?? 0),
									'amount'     => (float) ($charge_price_arr[$i] ?? 0),
									'total_amt'  => (float) ($charge_total_arr[$i] ?? 0),
								);
								$this->db->insert('sales_order_charges', $data_charge);
								$log_json['other_charges'][] = $data_charge;
							}
						}
					}

					$log_data = array(
						'parent_id'      => $order_id,
						'ref_id'         => NULL,
						'module'         => 'sales',
						'action'         => 'add_salesman_preapproved',
						'message'        => 'Sale Order added with batch deduction by ' . $this->session->userdata('super_name'),
						'json'           => json_encode($log_json),
						'table_name'     => 'sales_order',
						'added_by'       => $this->session->userdata('super_user_id'),
						'added_by_email' => $this->session->userdata('super_email'),
						'added_by_name'  => $this->session->userdata('super_name'),
						'added_by_type'  => $this->session->userdata('super_type')
					);
					$this->db->insert('sys_logs', $log_data);

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
			$keyword_filter .= " AND (so.company_name like '%" . $keyword . "%' 
            OR so.refrence_no like '%" . $keyword . "%'
            OR so.order_no like '%" . $keyword . "%')";
		endif;
		
		if (isset($_REQUEST['status']) && $_REQUEST['status'] != ""){
			$status        = $_REQUEST['status'];
			if ($status == 'pending') {
				$keyword_filter .= " AND (so.is_approved = '0')";
			} elseif ($status == 'invoice') {
				$keyword_filter .= " AND (so.is_approved = '1' AND so.is_generated = '0')";
			} elseif ($status == 'complete') {
				$keyword_filter .= " AND (so.is_approved = '1' AND so.is_generated = '1')";
			} 
		} else {
			$status = 'pending';
		}
		
		if (isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != ""):
			$keyword        = $_REQUEST['customer_id'];
			$keyword_filter .= " AND (so.customer_id = '" . $keyword . "')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(so.date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(so.date) BETWEEN '$from' AND '$to')";
			}
		}

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (so.company_id='" . $company_id . "')";
			if($this->session->userdata('super_type_id') == 7) {
				$keyword_filter .= " AND (so.added_by_id = '" . $this->session->userdata('super_user_id') . "')";
			}
		}

		if($status == 'pending' || $status == 'all') {
			$total_count = $this->db->query("
				SELECT 
					so.id 
				FROM sales_order AS so
				WHERE (so.is_deleted='0') $keyword_filter 
				ORDER BY so.date DESC
			")->num_rows();
	
			$query = $this->db->query("
				SELECT 
					so.id, so.order_type, so.order_no, so.refrence_no, so.is_generated, so.is_approved, so.date, so.customer_id, so.customer_name, so.warehouse_name, so.grand_total, so.company_name, so.remark, so.invoice_no, so.invoice_date, so.added_by_name 
				FROM sales_order AS so
				WHERE (so.is_deleted='0') $keyword_filter  
				ORDER BY so.date DESC LIMIT $start, $length
			");
		} else {
			$total_count = $this->db->query("
				SELECT 
					so.id 
				FROM sales_order AS so
				WHERE 
					EXISTS (
							SELECT 1
							FROM sales_order_product_batch sopb
							WHERE sopb.order_id = so.id
								AND sopb.white_qty <> sopb.recieved_qty
					) AND (so.is_deleted='0') $keyword_filter 
				ORDER BY so.date DESC
			")->num_rows();
	
			$query = $this->db->query("
					SELECT
							so.id,
							so.order_type,
							so.order_no,
							so.refrence_no,
							so.is_generated,
							so.is_approved,
							so.date,
							so.customer_id,
							so.customer_name,
							so.warehouse_name,
							so.grand_total,
							so.company_name,
							so.remark,
							so.invoice_no,
							so.invoice_date,
							so.added_by_name
					FROM sales_order AS so
					WHERE EXISTS (
							SELECT 1
							FROM sales_order_product_batch sopb
							WHERE sopb.order_id = so.id
								AND sopb.white_qty <> sopb.recieved_qty
					)
					AND so.is_deleted = '0'
					$keyword_filter
					ORDER BY so.date DESC
					LIMIT $start, $length
			");
		}

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_type = $item['order_type'];
				$customer_id = $item['customer_id'];

				$products_url = base_url() . 'inventory/sales-order/products/' . $id;
				$not_url = base_url() . 'inventory/sales-order/not-uploaded/' . $id;
				
				// $view_url = base_url() . 'inventory/sales-order/view/' . $id;
				// $action .= '
    		// 	 <a href="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
    		// 	 ';

				$action = '';
				$view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/sales_order_view_modal/" . $id . "','Sales Order View')";

				$delete_html = '';
				if ($this->session->userdata('super_type_id') != 7) {
					$delete_url = "confirm_modal('" . base_url() . "inventory/sales_order/delete/" . $id . "','Are you sure want to delete!')";
					$delete_html = '<a class="dropdown-item" href="javascript:void(0)" onclick="' . $delete_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Cancel</a>';
				}

				$edit_order_html = '';
				if ($this->session->userdata('super_type_id') !== 7) {
					$edit_order_url = base_url() . 'inventory/sales-order/edit-order/' . $id;
					$edit_order_html = '<a class="dropdown-item" href="' . $edit_order_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit</a>';
				}

				// if($this->session->userdata('super_type_id') != 4 && $item['is_approved'] == 0) {
				if($status == "all") {
					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
						<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
						</div>
					</div>';
				} else if($item['is_approved'] == 0 && $item['is_generated'] == 0) {
					$edit_url = base_url() . 'inventory/sales-order/edit/' . $id;
					
					$approve_html = '';
					if ($this->session->userdata('super_type_id') != 7) {
						$approve_url = base_url() . 'inventory/sales-order/approve/' . $id;
						$approve_html = '<a class="dropdown-item" href="' . $approve_url . '"><i class="fa fa-check" aria-hidden="true"></i> Approve</a>';
					}

					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
						<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
						   ' . $approve_html . '
							<a class="dropdown-item" href="' . $edit_url . '"><i class="fa fa-edit" aria-hidden="true"></i> Edit</a>
							' . $delete_html . '
						</div>
					</div>';
				} else if($item['is_generated'] == 0) {
					$gen_invoice_modal_url = "showLargeModal('" . base_url() . "modal/popup_inventory/sales_order_generate_invoice_modal/" . $customer_id . "/" . $id . "', 'Generate Invoice')";
					$gen_invoice_html = '<a class="dropdown-item" href="javascript:void(0)" onclick="' . $gen_invoice_modal_url . '"><i class="fa fa-refresh" aria-hidden="true"></i> Generate Invoice</a>';

					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
						<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
							' . $gen_invoice_html . '
							' . $delete_html . '
							' . $edit_order_html . '
						</div>
					</div>';
				} else if($item['is_generated'] == 1 && $item['is_approved'] == 1) {
					$invoice_white_url = base_url() . 'inventory/sales_order/invoice/white/' . $id;
					$invoice_black_url = base_url() . 'inventory/sales_order/invoice/black/' . $id;

					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
						<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
							<a class="dropdown-item" href="' . $invoice_white_url . '" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> View White Invoice</a>
							<a class="dropdown-item" href="' . $invoice_black_url . '" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> View Invoice</a>
							' . $delete_html . '
							' . $edit_order_html . '
						</div>
					</div>';
				} 

				// $action .='
				// <a href="'.$products_url.'" data-toggle="tooltip" data-bs-placement="top" title="Products"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-edit" aria-hidden="true"></i></button></a>
				// ';

				// if ($order_type == 'excel') {
				// 	$action .= '<a href="' . $not_url . '" data-toggle="tooltip" data-bs-placement="top" title="Not Upload"><button type="button" class="btn mr-2 mb-1 icon-btn-edit"><i class="fa fa-times" aria-hidden="true"></i></button></a>';
				// }

				$qty = 0;
				if($item['is_approved'] == 0 && $item['is_generated'] == 0) {
					$query2 = $this->db->query("SELECT SUM(qty) as qty FROM sales_order_product WHERE (order_id='$id') group by order_id limit 1");
					if ($query2->num_rows() > 0) {
						$row2 = $query2->row_array();
						$qty = $row2['qty'];
					}
				} else {
					$query2 = $this->db->query("SELECT avail_white_qty, avail_black_qty, white_qty, black_qty FROM sales_order_product_batch WHERE (order_id='$id')");
					if ($query2->num_rows() > 0) {
						foreach($query2->result_array() as $row2) {
							$qty += $row2['white_qty'];
							$qty += ($row2['avail_black_qty'] < $row2['black_qty']) ? $row2['avail_black_qty'] : $row2['black_qty'];
						}
					}
				}

				$total_pro = $this->db->query("SELECT id FROM sales_order_product WHERE (order_id='$id') ")->num_rows();
				$customer_name = $item['customer_name'];

				$data[] = array(
					"sr_no"						=> ++$start,
					"id"          		=> $item['id'],
					"order_no"      	=> $item['order_no'],
					"refrence_no"			=> $item['refrence_no'],
					"customer_name"		=> $customer_name,
					"warehouse_name"	=> ($item['warehouse_name']) ? $item['warehouse_name'] : '-',
					"company_name"		=> ($item['company_name'] != '' && $item['company_name'] != null) ? $item['company_name'] : '-',
					"grand_total"   	=> $item['grand_total'],
					"date"          	=> date('d M, Y', strtotime($item['date'])),
					"total_pro"     	=> $total_pro,
					"qty"           	=> $qty,
					"remark"        	=> $item['remark'],
					"invoice_no"		=> $item['invoice_no'],
					"invoice_date"		=> ($item['invoice_date'] != '0000-00-00' && $item['invoice_date'] != null) ? date('d M, Y', strtotime($item['invoice_date'])) : '-',
					"added_by"          => ($item['added_by_name'] != '' && $item['added_by_name'] != null) ? $item['added_by_name'] : '-',
					"action"          => $action,
				);
			}
		}

		$total_amount_formatted = '0.00';
		if (isset($_REQUEST['status']) && $_REQUEST['status'] == 'complete' && $this->session->userdata('super_type_id') != 7) {
			$sum_query = $this->db->query("SELECT SUM(so.grand_total) as total_amount FROM sales_order AS so WHERE (so.is_deleted='0') $keyword_filter");
			if ($sum_query->num_rows() > 0) {
				$row = $sum_query->row_array();
				$total_val = $row['total_amount'];
				$total_amount_formatted = number_format((float)($total_val ?? 0), 2, '.', ',');
			}
		}

		$json_data = array(
			"draw" => intval($params['draw']),
			"recordsTotal" => $total_count,
			"recordsFiltered" => $total_count,
			"data" => $data,
			"total_amount" => $total_amount_formatted
		);
		echo json_encode($json_data);
	}

	public function get_completed_sales_order()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (io.customer_name like '%" . $keyword . "%' 
            OR io.refrence_no like '%" . $keyword . "%'
            OR io.order_no like '%" . $keyword . "%'
            OR io.invoice_no like '%" . $keyword . "%')";
		endif;
		
		if (isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != ""):
			$keyword        = $_REQUEST['customer_id'];
			$keyword_filter .= " AND (io.customer_id = '" . $keyword . "')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(io.date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(io.date) BETWEEN '$from' AND '$to')";
			}
		}

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (io.company_id='" . $company_id . "')";
			if($this->session->userdata('super_type_id') == 7) {
				$keyword_filter .= " AND (io.added_by_id = '" . $this->session->userdata('super_user_id') . "')";
			}
		}

		$total_count = $this->db->query("
			SELECT io.id 
			FROM invoice_order AS io
			WHERE io.is_deleted = '0' AND io.type = 'normal' $keyword_filter
		")->num_rows();

		$query = $this->db->query("
			SELECT io.*,
				(SELECT COUNT(DISTINCT product_id) FROM invoice_order_products WHERE parent_id = io.id) AS product_count,
				(SELECT SUM(qty) FROM invoice_order_products WHERE parent_id = io.id) AS qty_count
			FROM invoice_order AS io
			WHERE io.is_deleted = '0' AND io.type = 'normal' $keyword_filter 
			ORDER BY io.date DESC 
			LIMIT $start, $length
		");

		if (!empty($query)) {
			$i = 0;
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_ids = explode(',', $item['unique_id']);
				$first_order_id = !empty($order_ids[0]) ? $order_ids[0] : 0;

				$view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/invoice_order_view_modal/" . $id . "','Invoice Order View')";
				$invoice_bill_url = base_url() . 'inventory/invoice_order_print/' . $id;
				$invoice_black_url = base_url() . 'inventory/sales_order/invoice/black/' . $first_order_id;

				$action = '<div class="btn-group">
					<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
					<i class="mdi mdi-dots-vertical"></i></button>
					<div class="dropdown-menu">
						<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
						<a class="dropdown-item" href="' . $invoice_bill_url . '" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Invoice Bill</a>
						<!-- <a class="dropdown-item" href="' . $invoice_black_url . '" target="_blank" style="display: none;"><i class="fa fa-file-excel-o" aria-hidden="true"></i> View Invoice</a> -->
					</div>
				</div>';

				$data[] = array(
					"sr_no"          => $start + $i + 1,
					"id"             => $id,
					"order_no"       => $item['order_no'],
					"invoice_no"     => $item['invoice_no'] ? $item['invoice_no'] : '-',
					"invoice_date"   => ($item['invoice_date'] != '0000-00-00' && $item['invoice_date'] != null) ? date('d M, Y', strtotime($item['invoice_date'])) : '-',
					"refrence_no"    => $item['refrence_no'],
					"customer_name"  => $item['customer_name'],
					"warehouse_name" => $item['warehouse_name'] ? $item['warehouse_name'] : '-',
					"total_pro"      => $item['product_count'],
					"qty"            => $item['qty_count'],
					"grand_total"    => $item['grand_total'],
					"date"           => date('d M, Y', strtotime($item['date'])),
					"added_by"       => ($item['added_by_name'] != '' && $item['added_by_name'] != null) ? $item['added_by_name'] : '-',
					"action"         => $action,
				);
				$i++;
			}
		}

		$total_amount_formatted = '0.00';
		if ($this->session->userdata('super_type_id') != 7) {
			$sum_query = $this->db->query("SELECT SUM(io.grand_total) as total_amount FROM invoice_order AS io WHERE (io.is_deleted='0' AND io.type = 'normal') $keyword_filter");
			if ($sum_query->num_rows() > 0) {
				$row = $sum_query->row_array();
				$total_val = $row['total_amount'];
				$total_amount_formatted = number_format((float)($total_val ?? 0), 2, '.', ',');
			}
		}

		$json_data = array(
			"draw" => intval($params['draw']),
			"recordsTotal" => $total_count,
			"recordsFiltered" => $total_count,
			"data" => $data,
			"total_amount" => $total_amount_formatted
		);
		echo json_encode($json_data);
	}

	public function get_sales_commission_totals()
	{
		$company_id = $this->session->userdata('company_id');
		$company_filter = "";
		if ($company_id) {
			$company_filter .= " AND (so.company_id='" . $company_id . "')";
			if ($this->session->userdata('super_type_id') == 7) {
				$company_filter .= " AND (so.added_by_id = '" . $this->session->userdata('super_user_id') . "')";
			}
		}

		$pending_total = 0.00;
		$pending_query = $this->db->query("
			SELECT SUM(sc.commission_amount) as total_comm_amount
			FROM sales_commission AS sc
			JOIN sales_order AS so ON sc.order_id = so.id
			WHERE (so.is_deleted='0' AND so.is_approved='1') $company_filter
			AND EXISTS (
				SELECT 1
				FROM sales_commission sc2
				WHERE sc2.order_id = so.id
				GROUP BY sc2.order_id
				HAVING
					SUM(CASE WHEN sc2.is_paid = 1 THEN 1 ELSE 0 END) = 0 
					AND SUM(sc2.commission_amount) > 0 
			)
		");
		if ($pending_query->num_rows() > 0) {
			$pending_total = (float)$pending_query->row()->total_comm_amount;
		}

		$complete_total = 0.00;
		$complete_query = $this->db->query("
			SELECT SUM(sc.commission_amount) as total_comm_amount
			FROM sales_commission AS sc
			JOIN sales_order AS so ON sc.order_id = so.id
			WHERE (so.is_deleted='0' AND so.is_approved='1') $company_filter
			AND EXISTS (
				SELECT 1
				FROM sales_commission sc2
				WHERE sc2.order_id = so.id
				GROUP BY sc2.order_id
				HAVING
					SUM(CASE WHEN sc2.is_paid = 1 THEN 1 ELSE 0 END) > 0 
					AND SUM(sc2.commission_amount) > 0 
			)
		");
		if ($complete_query->num_rows() > 0) {
			$complete_total = (float)$complete_query->row()->total_comm_amount;
		}

		return array(
			'pending' => $pending_total,
			'complete' => $complete_total
		);
	}

	public function get_sales_commission()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (so.company_name like '%" . $keyword . "%' 
            OR so.refrence_no like '%" . $keyword . "%'
            OR so.order_no like '%" . $keyword . "%')";
		endif;
		
		if (isset($_REQUEST['status']) && $_REQUEST['status'] != ""){
			$status        = $_REQUEST['status'];
			$keyword_filter .= " AND (so.is_approved = '1')";
			if($status == 'pending') {
				$keyword_filter .= " AND EXISTS (
					SELECT 1
					FROM sales_commission sc
					WHERE sc.order_id = so.id
					GROUP BY sc.order_id
					HAVING
						SUM(CASE WHEN sc.is_paid = 1 THEN 1 ELSE 0 END) = 0 
						AND SUM(sc.commission_amount) > 0 
				)";
			} else if($status == 'complete') {
				$keyword_filter .= " AND EXISTS (
					SELECT 1
					FROM sales_commission sc
					WHERE sc.order_id = so.id
					GROUP BY sc.order_id
					HAVING
						SUM(CASE WHEN sc.is_paid = 1 THEN 1 ELSE 0 END) > 0 
						AND SUM(sc.commission_amount) > 0 
				)";
			}
		} else {
			$status = 'pending';
		}
		
		if (isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != ""):
			$keyword        = $_REQUEST['customer_id'];
			$keyword_filter .= " AND (so.customer_id = '" . $keyword . "')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(so.date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(so.date) BETWEEN '$from' AND '$to')";
			}
		}

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (so.company_id='" . $company_id . "')";
			if($this->session->userdata('super_type_id') == 7) {
				$keyword_filter .= " AND (so.added_by_id = '" . $this->session->userdata('super_user_id') . "')";
			}
		}

		$total_count = $this->db->query("
			SELECT 
				so.id 
			FROM sales_order AS so
			WHERE (so.is_deleted='0') $keyword_filter 
			ORDER BY so.date DESC
		")->num_rows();

		$query = $this->db->query("
			SELECT 
				so.id, so.order_type, so.order_no, so.is_generated, so.is_approved, so.date, so.customer_id, so.customer_name, so.warehouse_name, so.grand_total 
			FROM sales_order AS so
			WHERE (so.is_deleted='0') $keyword_filter  
			ORDER BY so.date DESC LIMIT $start, $length
		");
		
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_type = $item['order_type'];
				$customer_id = $item['customer_id'];

				$comm_amt_query = $this->db->query("SELECT SUM(commission_amount) as comm_amt FROM sales_commission WHERE order_id = '$id' GROUP BY order_id");
				$comm_amt = ($comm_amt_query->num_rows() > 0) ? $comm_amt_query->row()->comm_amt : 0;
				
				$action = '';
				$view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/sales_order_commission_view_modal/" . $id . "','Sales Order View')";

				$action ='<div class="btn-group">
					<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
					<i class="mdi mdi-dots-vertical"></i></button>
					<div class="dropdown-menu">
						<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
					</div>
				</div>';
				
				$qty = 0;
				if($item['is_approved'] == 0 && $item['is_generated'] == 0) {
					$query2 = $this->db->query("SELECT SUM(qty) as qty FROM sales_order_product WHERE (order_id='$id') group by order_id limit 1");
					if ($query2->num_rows() > 0) {
						$row2 = $query2->row_array();
						$qty = $row2['qty'];
					}
				} else {
					$query2 = $this->db->query("SELECT avail_white_qty, avail_black_qty, white_qty, black_qty FROM sales_order_product_batch WHERE (order_id='$id')");
					if ($query2->num_rows() > 0) {
						foreach($query2->result_array() as $row2) {
							$qty += $row2['white_qty'];
							$qty += ($row2['avail_black_qty'] < $row2['black_qty']) ? $row2['avail_black_qty'] : $row2['black_qty'];
						}
					}
				}

				$total_pro = $this->db->query("SELECT id FROM sales_order_product WHERE (order_id='$id') ")->num_rows();
				$customer_name = $item['customer_name'];


				$sr_no_val = ++$start;
				if (isset($_REQUEST['status']) && $_REQUEST['status'] == 'pending') {
					$sr_no_val = '<input type="checkbox" class="order-chk" value="' . $id . '" data-amount="' . $comm_amt . '">';
				}

				$data[] = array(
					"sr_no"						=> $sr_no_val,
					"id"          		=> $item['id'],
					"date"          	=> date('d M, Y', strtotime($item['date'])),
					"customer_name"		=> $customer_name,
					"order_no"      	=> $item['order_no'],
					"warehouse_name"	=> ($item['warehouse_name']) ? $item['warehouse_name'] : '-',
					"qty"           	=> $qty,
					"total_pro"     	=> $total_pro,
					"grand_total"   	=> $item['grand_total'],
					"total_comm"			=> $comm_amt,
					"action"          => $action,
				);
			}
		}

		$total_comm_amount = 0.00;
		$sum_query = $this->db->query("
			SELECT SUM(sc_outer.commission_amount) as total_comm_amount
			FROM sales_commission AS sc_outer
			JOIN sales_order AS so ON sc_outer.order_id = so.id
			WHERE (so.is_deleted='0') $keyword_filter
		");
		if ($sum_query->num_rows() > 0) {
			$total_comm_amount = (float) $sum_query->row()->total_comm_amount;
		}

		$json_data = array(
			"draw" => intval($params['draw']),
			"recordsTotal" => $total_count,
			"recordsFiltered" => $total_count,
			"data" => $data,
			"total_amount" => number_format($total_comm_amount, 2, '.', ',')
		);
		echo json_encode($json_data);
	}

	public function make_sales_commission_payment()
	{
		$this->db->trans_begin();
		try {
			$order_ids_str = $this->input->post('order_ids');
			$payment_date = $this->input->post('payment_date');
			$remark = $this->input->post('remark');

			if (empty($order_ids_str) || empty($payment_date) || empty($remark)) {
				throw new Exception('All fields are mandatory');
			}

			$order_ids = explode(',', $order_ids_str);
			if (empty($order_ids)) {
				throw new Exception('No orders selected');
			}

			$update_data = array(
				'is_paid' => 1,
				'payment_date' => $payment_date,
				'remark' => $remark
			);

			$this->db->where_in('order_id', $order_ids);
			$this->db->where('is_paid', 0);
			$this->db->update('sales_commission', $update_data);

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$result = array(
					"status" => 400,
					"message" => "Error occurred while processing payment",
				);
			} else {
				$this->db->trans_commit();
				$result = array(
					"status" => 200,
					"message" => "Payment processed successfully",
				);
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$result = array(
				"status" => 400,
				"message" => "Exception occurred: " . $e->getMessage(),
			);
		}
		return simple_json_output($result);
	}

	public function get_invoice_order_details_by_id($id)
	{
		$invoice = $this->db->where('id', $id)->get('invoice_order')->row_array();
		if (empty($invoice)) {
			return [];
		}

		$products_query = $this->db->query("
			SELECT 
				iop.product_name, p.hsn_code, iop.qty as qtys,
				iop.bill_total as amount, iop.gst_amount,
				iop.gst, iop.final_total as total 
			FROM invoice_order_products as iop
			INNER JOIN raw_products as p ON p.id = iop.product_id
			WHERE iop.parent_id = $id
		");

		$invoice['products'] = ($products_query->num_rows() > 0) ? $products_query->result_array() : [];

		$company = $this->common_model->getRowById('company', '*', ['id' => $invoice['company_id']]);
		$invoice['company'] = ($company) ? $company : [];
		
		$customer = $this->common_model->getRowById('customer', '*', ['id' => $invoice['customer_id']]);
		$invoice['customer'] = [
			'company_name' => $invoice['customer_name'],
			'address' => $invoice['billing_address'] ? $invoice['billing_address'] : ($customer['address'] ?? ''),
			'city_name' => $invoice['billing_city_name'] ? $invoice['billing_city_name'] : ($customer['city_name'] ?? ''),
			'pincode' => $invoice['billing_pincode'] ? $invoice['billing_pincode'] : ($customer['pincode'] ?? ''),
			'owner_mobile' => $customer['owner_mobile'] ?? '',
			'gst_no' => $invoice['billing_gst_no'] ? $invoice['billing_gst_no'] : ($customer['gst_no'] ?? ''),
			'state_name' => $invoice['billing_state_name'] ? $invoice['billing_state_name'] : ($customer['state_name'] ?? ''),
			'state_id' => $invoice['billing_state_id'] ? $invoice['billing_state_id'] : ($customer['state_id'] ?? '')
		];

		// Map fields for sales_invoice.php compatibility
		$invoice['order_no'] = $invoice['invoice_no'];
		$invoice['date'] = $invoice['invoice_date'] ? $invoice['invoice_date'] : $invoice['date'];

		return $invoice;
	}

	public function add_conversion_order_post()
	{
		$this->db->trans_start(); // Start transaction

		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_conversion_added_successfully'),
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
					$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
				} else {
					$customer_name = '';
				}

				$sale_person_id = 0;
				$is_distributor = 0;
				if ($customer_id != '') {
					$cust_record = $this->db->select('added_by_id, is_distributor')->where('id', $customer_id)->get('customer')->row_array();
					$sale_person_id = isset($cust_record['added_by_id']) ? (int)$cust_record['added_by_id'] : 0;
					$is_distributor = isset($cust_record['is_distributor']) ? (int)$cust_record['is_distributor'] : 0;
				}
				
				$warehouse_id = $this->input->post('warehouse_id');
				if ($warehouse_id != '') {
					$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');
				} else {
					$warehouse_name = '';
				}

				$company_id = $this->session->userdata('company_id');
				$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

				$round_of       	= ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
				$gst_type       	= clean_and_escape($this->input->post('gst_type'));

				$other_charges_name   = clean_and_escape($this->input->post('other_charges_name'));
				$other_charges_amount = ($this->input->post('other_charges_amount') != '') ? $this->input->post('other_charges_amount') : 0;
				$basic_value          = price_format_decimal($this->input->post('basic_value'));
				$net_sales_value_1    = price_format_decimal($this->input->post('net_sales_value_1'));
				$total_black_amt      = 0.00;
				$net_sales_value_2    = $net_sales_value_1;
				$grand_total          = price_format_decimal($this->input->post('grand_total'));
				$central_gst          = price_format_decimal($this->input->post('central_gst'));
				$state_gst            = price_format_decimal($this->input->post('state_gst'));
				$igst                 = price_format_decimal($this->input->post('igst'));
				$gst_total            = ($gst_type == 'IGST') ? $igst : ($central_gst + $state_gst);

				$data = array();
				$data['sale_person_id']         = $sale_person_id;
				$data['is_distributor']         = $is_distributor;
				$data['order_no']          		= $order_no;
				$data['refrence_no']       		= clean_and_escape($this->input->post('refrence_no'));
				$data['date']     		   	 		= ($this->input->post('date'));
				$data['customer_id']       		= $customer_id;
				$data['customer_name']     		= $customer_name;
				$data['warehouse_id']      		= $warehouse_id;
				$data['warehouse_name']    		= $warehouse_name;
				$data['company_id']        		= $company_id;
				$data['company_name']      		= $company_name;
				$data['remark'] 		   		 		= ($this->input->post('remark'));
				$data['narration']         		= ($this->input->post('narration'));
				$data['gst_type']     	   		= $gst_type;
				$data['is_approved']					= 1;
				$data['igst_per']     	   		= 0;
				$data['cgst_per']     	   		= 0;
				$data['sgst_per']     	   		= 0;
				$data['basic_value']          = $basic_value;
				$data['net_sales_value_1']    = $net_sales_value_1;
				$data['total_black_amt']      = $total_black_amt;
				$data['central_gst']          = $central_gst;
				$data['state_gst']            = $state_gst;
				$data['igst']                 = $igst;
				$data['gst_total']            = $gst_total;
				$data['net_sales_value_2']    = $net_sales_value_2;
				$data['round_of']             = $round_of;
				$data['grand_total']          = $grand_total;
				$data['other_charges_name']   = $other_charges_name;
				$data['other_charges_amount'] = $other_charges_amount;
				$data['type']                 = 'conversion';

				$shipping_state_id = $this->input->post('shipping_state_id');
				$shipping_city_id  = $this->input->post('shipping_city_id');
				$shipping_pincode  = clean_and_escape($this->input->post('shipping_pincode'));
				$shipping_gst      = clean_and_escape($this->input->post('shipping_gst'));
				$shipping_gst_no   = clean_and_escape($this->input->post('shipping_gst_no'));
				$shipping_address  = clean_and_escape($this->input->post('shipping_address'));

				$billing_state_id  = $this->input->post('billing_state_id');
				$billing_city_id   = $this->input->post('billing_city_id');
				$billing_pincode   = clean_and_escape($this->input->post('billing_pincode'));
				$billing_gst       = clean_and_escape($this->input->post('billing_gst'));
				$billing_gst_no    = clean_and_escape($this->input->post('billing_gst_no'));
				$billing_address   = clean_and_escape($this->input->post('billing_address'));

				$data['shipping_state_id']   = $shipping_state_id;
				$data['shipping_state_name'] = ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '';
				$data['shipping_city_id']    = $shipping_city_id;
				$data['shipping_city_name']  = ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '';
				$data['shipping_pincode']    = $shipping_pincode;
				$data['shipping_gst']        = $shipping_gst;
				$data['shipping_gst_no']     = $shipping_gst_no;
				$data['shipping_address']    = $shipping_address;

				$data['billing_state_id']    = $billing_state_id;
				$data['billing_state_name']  = ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '';
				$data['billing_city_id']     = $billing_city_id;
				$data['billing_city_name']   = ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '';
				$data['billing_pincode']     = $billing_pincode;
				$data['billing_gst']         = $billing_gst;
				$data['billing_gst_no']      = $billing_gst_no;
				$data['billing_address']     = $billing_address;

				$data['added_by_id']          = $this->session->userdata('super_user_id');
				$data['added_by_name']        = $this->session->userdata('super_name');
				$data['added_date']   	      = date("Y-m-d H:i:s");

				$log_json = array(
					'sale_order'    => $data,
					'products'      => array(),
					'other_charges' => array()
				);

				if ($this->db->insert('sales_order', $data)) {
					$order_id = $this->db->insert_id();
					$this->update_order_no($order_no);

					$invoice_no = $this->get_invoice_no();
					$invoice_date = $this->input->post('date');

					$inv_order = [
						"type" => "conversion",
						"unique_id" => $order_id,
						"order_no" => $order_no,
						"invoice_no" => $invoice_no,
						"invoice_date" => $invoice_date,
						"refrence_no" => clean_and_escape($this->input->post('refrence_no')),
						"date" => ($this->input->post('date')),
						"customer_id" => $customer_id,
						"customer_name" => $customer_name,
						"shipping_state_id" => $shipping_state_id,
						"shipping_state_name" => ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '',
						"shipping_city_id" => $shipping_city_id,
						"shipping_city_name" => ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '',
						"shipping_pincode" => $shipping_pincode,
						"shipping_gst" => $shipping_gst,
						"shipping_gst_no" => $shipping_gst_no,
						"shipping_address" => $shipping_address,
						"billing_state_id" => $billing_state_id,
						"billing_state_name" => ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '',
						"billing_city_id" => $billing_city_id,
						"billing_city_name" => ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '',
						"billing_pincode" => $billing_pincode,
						"billing_gst" => $billing_gst,
						"billing_gst_no" => $billing_gst_no,
						"billing_address" => $billing_address,
						"warehouse_id" => $warehouse_id,
						"warehouse_name" => $warehouse_name,
						"company_id" => $company_id,
						"company_name" => $company_name,
						"narration" => clean_and_escape($this->input->post('narration')),
						"remark" => clean_and_escape($this->input->post('remark')),
						"gst_type" => $gst_type,
						"cgst_per" => 0,
						"sgst_per" => 0,
						"igst_per" => 0,
						"basic_value" => $basic_value,
						"net_sales_value_1" => $net_sales_value_1,
						"gst_total" => $gst_total,
						"net_sales_value_2" => $net_sales_value_2,
						"round_of" => $round_of,
						"grand_total" => $grand_total,
						"added_by_id" => $this->session->userdata('super_user_id'),
						"added_by_name" => $this->session->userdata('super_name'),
						"added_date" => date("Y-m-d H:i:s"),
					];

					$this->db->insert("invoice_order", $inv_order);
					$invoice_order_id = $this->db->insert_id();

					$product_id_arr     = ($this->input->post('product_id'));
					$quantity_arr       = ($this->input->post('quantity'));
					$master_amount_arr  = ($this->input->post('master_amount'));
					$total_amount_arr   = ($this->input->post('total_amount'));
					$bill_amount_arr    = ($this->input->post('bill_amount'));
					$gst_arr       			= ($this->input->post('gst'));
					$gst_amount_arr     = ($this->input->post('gst_amount'));
					$bill_total_arr     = ($this->input->post('bill_total'));
					$total_bill_gst_amount_arr = ($this->input->post('total_bill_gst_amount'));
					$final_total_arr    = ($this->input->post('final_total'));
					$available_arr			= ($this->input->post('available'));
					$x_value_arr        = ($this->input->post('x_value'));

					// Batch fields
					$batch_id                 = $this->input->post('batch_id');
					$batch_white_qty          = $this->input->post('batch_white_qty');
					$batch_rate               = $this->input->post('batch_rate');
					$batch_bill_amount        = $this->input->post('batch_bill_amount');
					$batch_bill_total         = $this->input->post('batch_bill_total');
					$batch_gst_per            = $this->input->post('batch_gst_per');
					$batch_gst_amt            = $this->input->post('batch_gst_amt');
					$batch_total_bill_gst     = $this->input->post('batch_total_bill_gst_amount');
					$batch_final_total        = $this->input->post('batch_final_total');

					$total_white_qty_sum = 0;
					for ($i = 0; $i < count($product_id_arr); $i++) {
						if ($quantity_arr[$i] > 0) {
							$xpro 			=  explode('|', $product_id_arr[$i]);
							$product_id 	= $xpro[0];

							$product    	= $this->crud_model->get_raw_products_by_id($product_id)->row_array();
							if (empty($product)) {
								throw new Exception('No Product Found');
							}

							$item_code = $product['item_code'] ?? '';
							if ($item_code == '') {
								$inv_prod = $this->db->where('product_id', $product_id)->get('inventory')->row_array();
								$item_code = $inv_prod['item_code'] ?? '';
							}

							$data_product = array(
								'order_id'                => $order_id,
								'product_id'              => $product_id,
								'item_code'               => $item_code,
								'product_name'            => $product['name'],
								'qty'                     => (float) ($quantity_arr[$i] ?? 0),
								'amount'                  => (float) ($master_amount_arr[$i] ?? 0),
								'total_amount'            => (float) ($total_amount_arr[$i] ?? 0),
								'bill_amount'             => (float) ($bill_amount_arr[$i] ?? 0),
								'bill_total'              => (float) ($bill_total_arr[$i] ?? 0),
								'available'               => (float) ($available_arr[$i] ?? 0),
								'gst'                     => (float) ($gst_arr[$i] ?? 0),
								'gst_amount'              => (float) ($gst_amount_arr[$i] ?? 0),
								'total_bill_gst_amount'   => (float) ($total_bill_gst_amount_arr[$i] ?? 0),
								'black_amount'            => 0.00,
								'black_total'             => 0.00,
								'final_total'             => (float) ($final_total_arr[$i] ?? 0),
							);

							$this->db->insert('sales_order_product', $data_product);
							$order_product_id = $this->db->insert_id();

							$product_log_data = $data_product;
							$product_log_data['id'] = $order_product_id;
							$product_log_data['batches'] = array();

							$row_index = $x_value_arr[$i];

							if (!empty($batch_id[$row_index])) {
								foreach ($batch_id[$row_index] as $index => $bid) {
									if (empty($bid)) continue;

									$batch_detail = $this->db->where('id', $bid)->get('inventory')->row_array();
									if (empty($batch_detail)) {
										throw new Exception('Batch details not found for batch ID: ' . $bid);
									}

									$allocated_qty = (float)($batch_white_qty[$row_index][$index] ?? 0);

									$data_product_bat = array(
										'order_id'          => $order_id,
										'order_product_id'  => $order_product_id,
										'batch_no'      		=> $batch_detail['batch_no'],
										'batch_qty'       	=> $batch_detail['quantity'],

										'avail_white_qty'		=> $batch_detail['official_qty'],
										'avail_black_qty'		=> $batch_detail['black_qty'],
										'qty'								=> $allocated_qty,
										'white_qty'					=> $allocated_qty,
										'black_qty'					=> 0,
										'recieved_qty'			=> $allocated_qty,

										'amount'						=> (float) $batch_rate[$row_index][$index],
										'bill_amount'				=> (float) $batch_bill_amount[$row_index][$index],
										'bill_total'				=> (float) $batch_bill_total[$row_index][$index],
										'gst'								=> (float) $batch_gst_per[$row_index][$index],
										'gst_amount'				=> (float) $batch_gst_amt[$row_index][$index],
										'total_bill_gst_amount'	=> (float) $batch_total_bill_gst[$row_index][$index],
										'black_amount'			=> 0.00,
										'black_total'				=> 0.00,
										'final_total'				=> (float) $batch_final_total[$row_index][$index],
										'added_date'        => date('Y-m-d H:i:s'),
									);

									$this->db->insert('sales_order_product_batch', $data_product_bat);
									$new_batch_id = $this->db->insert_id();

									$total_amt_bat = $allocated_qty * $batch_rate[$row_index][$index];
									$gst_amt_bat = ($total_amt_bat * $batch_gst_per[$row_index][$index]) / 100;
									$total_bill_gst_bat = $total_amt_bat + $gst_amt_bat;

									$inv_products = [
										"parent_id" => $invoice_order_id,
										"batch_id" => $new_batch_id,
										"order_id" => $order_id,
										"product_id" => $product_id,
										"product_name" => $product['name'],
										"qty" => $allocated_qty,
										"item_code" => $item_code,
										"amount" => (float) $batch_rate[$row_index][$index],
										"total_amount" => $total_amt_bat,
										"bill_amount" => (float) $batch_bill_amount[$row_index][$index],
										"bill_total" => (float) $batch_bill_total[$row_index][$index],
										"gst" => (float) $batch_gst_per[$row_index][$index],
										"gst_amount" => $gst_amt_bat,
										"total_bill_gst_amount" => $total_bill_gst_bat,
										"final_total" => (float) $batch_final_total[$row_index][$index],
									];
									$this->db->insert("invoice_order_products", $inv_products);

									$product_log_data['batches'][] = $data_product_bat;
									$total_white_qty_sum += $allocated_qty;

									if ($batch_detail['official_qty'] < $allocated_qty || $batch_detail['official_qty'] == 0) {
										throw new Exception('Insufficient white stock for ' . $product['name'] . ' in batch ' . $batch_detail['batch_no'] . '. Available White Qty: ' . $batch_detail['official_qty'] . '.');
									} else {
										// Update batch quantities: convert white (official) quantity to black
										$new_qty = $batch_detail['quantity']; // Total quantity remains unchanged
										$new_black_qty = $batch_detail['black_qty'] + $allocated_qty;
										$new_official_qty = $batch_detail['official_qty'] - $allocated_qty;
										if ($new_official_qty < 0) $new_official_qty = 0;

										$pending_qty = 0;

										$this->db->where('id', $bid)->update('inventory', array(
											'quantity' => $new_qty,
											'black_qty' => $new_black_qty,
											'official_qty' => $new_official_qty,
											'pending_qty' => $batch_detail['pending_qty'] + $pending_qty
										));

										// Insert into inventory history: Outflow of White (Official) quantity
										$inv_his_out = [
											'supplier_id' 			=> $batch_detail["supplier_id"],
											'parent_id' 				=> $bid,
											'company_id' 				=> $company_id,
											'warehouse_id' 			=> $batch_detail["warehouse_id"],
											'warehouse_name' 		=> $batch_detail["warehouse_name"],
											'product_id' 				=> $batch_detail["product_id"],
											'categories' 				=> $batch_detail["categories"],
											'batch_no' 					=> $batch_detail["voucher_no"],
											'product_name'			=> $batch_detail['product_name'] ?? '',
											'item_code'					=> $batch_detail['item_code'] ?? '',
											'sku'         			=> $batch_detail['sku'] ?? '',
											'order_id'        	=> $order_id,
											'status'        		=> 'out',
											'quantity'        	=> $allocated_qty,

											'actual_rmb'        => 0,
											'total_rmb'         => 0,
											'actual_usd'        => 0,
											'actual_inr'        => 0,
											'official_qty'      => $allocated_qty,
											'official_rate_rs'  => $batch_bill_amount[$row_index][$index],
											'official_total_rs' => $batch_bill_total[$row_index][$index],
											'black_qty'         => 0.00,
											'pending_qty'       => 0.00,
											'black_rate_rs'  		=> 0.00,
											'black_total_rs' 		=> 0.00,
											'duty_percent'      => 0,
											'duty_amt'          => 0,
											'duty_surcharge'    => 0,
											'taxable_value'     => $batch_bill_total[$row_index][$index],
											'gst_amt'           => $batch_gst_amt[$row_index][$index],
											'total_amt'         => $batch_final_total[$row_index][$index],	
											
											'received_date'     => date('Y-m-d'),
											'invoice_no'        => 1,	
											'added_date'        => date('Y-m-d H:i:s'),
											"added_by_id"       => $this->session->userdata('super_user_id'),
											"added_by_name"     => $this->session->userdata('super_name'),
										];
										$this->db->insert('inventory_history', $inv_his_out);

										// Insert into inventory history: Inflow of Black quantity
										$inv_his_in = [
											'supplier_id' 			=> $batch_detail["supplier_id"],
											'parent_id' 				=> $bid,
											'company_id' 				=> $company_id,
											'warehouse_id' 			=> $batch_detail["warehouse_id"],
											'warehouse_name' 		=> $batch_detail["warehouse_name"],
											'product_id' 				=> $batch_detail["product_id"],
											'categories' 				=> $batch_detail["categories"],
											'batch_no' 					=> $batch_detail["voucher_no"],
											'product_name'			=> $batch_detail['product_name'] ?? '',
											'item_code'					=> $batch_detail['item_code'] ?? '',
											'sku'         			=> $batch_detail['sku'] ?? '',
											'order_id'        	=> $order_id,
											'status'        		=> 'in',
											'quantity'        	=> $allocated_qty,

											'actual_rmb'        => 0,
											'total_rmb'         => 0,
											'actual_usd'        => 0,
											'actual_inr'        => 0,
											'official_qty'      => 0.00,
											'official_rate_rs'  => 0.00,
											'official_total_rs' => 0.00,
											'black_qty'         => $allocated_qty,
											'pending_qty'       => 0.00,
											'black_rate_rs'  		=> $batch_bill_amount[$row_index][$index],
											'black_total_rs' 		=> $batch_bill_total[$row_index][$index],
											'duty_percent'      => 0,
											'duty_amt'          => 0,
											'duty_surcharge'    => 0,
											'taxable_value'     => 0.00,
											'gst_amt'           => 0.00,
											'total_amt'         => 0.00,	
											
											'received_date'     => date('Y-m-d'),
											'invoice_no'        => 1,	
											'added_date'        => date('Y-m-d H:i:s'),
											"added_by_id"       => $this->session->userdata('super_user_id'),
											"added_by_name"     => $this->session->userdata('super_name'),
										];
										$this->db->insert('inventory_history', $inv_his_in);
									}
								}
							}
							$log_json['products'][] = $product_log_data;
						}
					}

					// Insert other charges
					$other_charge_name_arr 	= $this->input->post('other_charge_name');
					$other_charge_gst_arr 	= $this->input->post('other_charge_gst');
					$other_charge_price_arr = $this->input->post('other_charge_price');
					$other_charge_total_arr = $this->input->post('other_charge_total');

					if(!empty($other_charge_name_arr)){
						for($c = 0; $c < count($other_charge_name_arr); $c++){
							if($other_charge_name_arr[$c] != ''){
								$data_charge = [
									'order_id' 		=> $order_id,
									'charge_name' 	=> $other_charge_name_arr[$c],
									'gst' 			=> (float)$other_charge_gst_arr[$c],
									'price' 		=> (float)$other_charge_price_arr[$c],
									'total_amount' 	=> (float)$other_charge_total_arr[$c],
									'added_date' 	=> date('Y-m-d H:i:s'),
								];
								$this->db->insert('sales_order_charges', $data_charge);
								$log_json['other_charges'][] = $data_charge;
							}
						}
					}

					// Log action
					$log_data = array(
						'parent_id'      => $order_id,
						'ref_id'         => NULL,
						'module'         => 'sales',
						'action'         => 'add_conversion',
						'message'        => 'Conversion Order added by ' . $this->session->userdata('super_name'),
						'json'           => json_encode($log_json),
						'table_name'     => 'sales_order',
						'added_by'       => $this->session->userdata('super_user_id'),
						'added_by_email' => $this->session->userdata('super_email'),
						'added_by_name'  => $this->session->userdata('super_name'),
						'added_by_type'  => $this->session->userdata('super_type')
					);
					$this->db->insert('sys_logs', $log_data);
				}
			}

			$this->db->trans_commit(); // Commit transaction
		} catch (Exception $e) {
			$this->db->trans_rollback(); // Rollback transaction
			$resultpost = array(
				"status" => 400,
				"message" => $e->getMessage(),
			);
		}

		$this->session->set_flashdata('flash_message', $resultpost['message']);
		return simple_json_output($resultpost);
	}

	public function delete_conversion_order($invoice_order_id)
	{
		$this->db->trans_start(); // Start transaction

		try {
			$resultpost = array(
				"status" => 200,
				"message" => get_phrase('sales_conversion_deleted_successfully'),
				"url" => $this->session->userdata('previous_url'),
			);

			// 1. Fetch the invoice_order record
			$invoice_order = $this->db->where('id', $invoice_order_id)
									 ->where('type', 'conversion')
									 ->where('is_deleted', '0')
									 ->get('invoice_order')
									 ->row_array();
			if (empty($invoice_order)) {
				throw new Exception('Sales conversion order not found.');
			}

			// 2. Fetch the corresponding sales_order record
			$sales_order_id = $invoice_order['unique_id'];
			$sales_order = $this->db->where('id', $sales_order_id)
								   ->where('type', 'conversion')
								   ->where('is_deleted', '0')
								   ->get('sales_order')
								   ->row_array();
			if (empty($sales_order)) {
				throw new Exception('Corresponding sales order not found.');
			}

			// 3. Fetch all products associated with this sales order
			$order_products = $this->db->where('order_id', $sales_order_id)
									   ->get('sales_order_product')
									   ->result_array();

			// 4. Check eligibility of all batch rows
			// We need to verify that each inventory record has sufficient black_qty to subtract the allocated qty
			$inventory_updates = array();
			foreach ($order_products as $op) {
				$product_id = $op['product_id'];
				$op_batches = $this->db->where('order_product_id', $op['id'])
									   ->get('sales_order_product_batch')
									   ->result_array();

				foreach ($op_batches as $op_batch) {
					$batch_no = $op_batch['batch_no'];
					$allocated_qty = (float) $op_batch['qty'];

					if ($allocated_qty <= 0) {
						continue;
					}

					// Find the inventory item by product_id, batch_no, and warehouse_id
					$inventory_item = $this->db->where('product_id', $product_id)
											   ->where('batch_no', $batch_no)
											   ->where('warehouse_id', $sales_order['warehouse_id'])
											   ->get('inventory')
											   ->row_array();

					if (empty($inventory_item)) {
						throw new Exception('Inventory record not found for product: ' . $op['product_name'] . ' and batch: ' . $batch_no);
					}

					$current_black_qty = (float) $inventory_item['black_qty'];
					if ($current_black_qty < $allocated_qty) {
						throw new Exception('Cannot delete. Insufficient black stock in batch: ' . $batch_no . ' for product: ' . $op['product_name'] . '. (Allocated: ' . $allocated_qty . ', Available Black: ' . $current_black_qty . ')');
					}

					// Store details for database update
					$inventory_updates[] = array(
						'inventory_item' => $inventory_item,
						'allocated_qty'  => $allocated_qty,
						'op_batch'       => $op_batch
					);
				}
			}

			// 5. Apply updates to database and insert history logs
			foreach ($inventory_updates as $update) {
				$item = $update['inventory_item'];
				$allocated_qty = $update['allocated_qty'];
				$op_batch = $update['op_batch'];

				$new_black_qty = $item['black_qty'] - $allocated_qty;
				$new_official_qty = $item['official_qty'] + $allocated_qty;

				// Update inventory table
				$this->db->where('id', $item['id'])->update('inventory', array(
					'black_qty'    => $new_black_qty,
					'official_qty' => $new_official_qty,
				));

				// Insert into inventory history: Reversal (Inflow of white qty)
				$history = array(
					'supplier_id'    => $item['supplier_id'],
					'parent_id'      => $item['id'],
					'company_id'     => $sales_order['company_id'],
					'warehouse_id'   => $item['warehouse_id'],
					'warehouse_name' => $item['warehouse_name'],
					'product_id'     => $item['product_id'],
					'categories'     => $item['categories'],
					'batch_no'       => $item['voucher_no'],
					'product_name'   => $item['product_name'] ?? '',
					'item_code'      => $item['item_code'] ?? '',
					'sku'            => $item['sku'] ?? '',
					'order_id'       => $sales_order_id,
					'status'         => 'conversion_delete',
					'quantity'       => $allocated_qty,
					'received_date'  => date('Y-m-d'),
					'added_date'     => date('Y-m-d H:i:s'),
					'added_by_id'    => $this->session->userdata('super_user_id'),
					'added_by_name'  => $this->session->userdata('super_name'),
				);
				$this->db->insert('inventory_history', $history);
			}

			// 6. Set is_deleted = '1' for sales_order and invoice_order
			$this->db->where('id', $sales_order_id)->update('sales_order', array('is_deleted' => '1'));
			$this->db->where('id', $invoice_order_id)->update('invoice_order', array('is_deleted' => '1'));

			// 7. Insert system log for deleting conversion
			$log_data = array(
				'title'          => 'Conversion Order Deleted',
				'detail'         => 'Conversion Order No ' . $sales_order['order_no'] . ' has been deleted.',
				'added_date'     => date("Y-m-d H:i:s"),
				'added_by_id'    => $this->session->userdata('super_user_id'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			$this->db->trans_commit(); // Commit transaction
		} catch (Exception $e) {
			$this->db->trans_rollback(); // Rollback transaction
			$resultpost = array(
				"status" => 400,
				"message" => $e->getMessage(),
			);
		}

		$this->session->set_flashdata('flash_message', $resultpost['message']);
		return simple_json_output($resultpost);
	}

	public function get_conversion_order()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (io.customer_name like '%" . $keyword . "%' 
            OR io.refrence_no like '%" . $keyword . "%'
            OR io.order_no like '%" . $keyword . "%'
            OR io.invoice_no like '%" . $keyword . "%')";
		endif;
		
		if (isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != ""):
			$keyword        = $_REQUEST['customer_id'];
			$keyword_filter .= " AND (io.customer_id = '" . $keyword . "')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(io.date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(io.date) BETWEEN '$from' AND '$to')";
			}
		}

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (io.company_id='" . $company_id . "')";
			if($this->session->userdata('super_type_id') == 7) {
				$keyword_filter .= " AND (io.added_by_id = '" . $this->session->userdata('super_user_id') . "')";
			}
		}

		$total_count = $this->db->query("
			SELECT io.id 
			FROM invoice_order AS io
			WHERE io.is_deleted = '0' AND io.type = 'conversion' $keyword_filter
		")->num_rows();

		$query = $this->db->query("
			SELECT io.*,
				(SELECT COUNT(DISTINCT product_id) FROM invoice_order_products WHERE parent_id = io.id) AS product_count,
				(SELECT SUM(qty) FROM invoice_order_products WHERE parent_id = io.id) AS qty_count
			FROM invoice_order AS io
			WHERE io.is_deleted = '0' AND io.type = 'conversion' $keyword_filter 
			ORDER BY io.date DESC 
			LIMIT $start, $length
		");

		if (!empty($query)) {
			$i = 0;
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_ids = explode(',', $item['unique_id']);
				$first_order_id = !empty($order_ids[0]) ? $order_ids[0] : 0;

				$view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/invoice_order_view_modal/" . $id . "','Invoice Order View')";
				$invoice_bill_url = base_url() . 'inventory/invoice_order_print/' . $id;
				$delete_url = "confirm_modal('" . base_url() . "inventory/conversion_order/delete/" . $id . "','Are you sure want to delete!')";

				$action = '<div class="btn-group">
					<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
					<i class="mdi mdi-dots-vertical"></i></button>
					<div class="dropdown-menu">
						<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
						<a class="dropdown-item" href="' . $invoice_bill_url . '" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Invoice Bill</a>
						<a href="javascript:void(0)" class="dropdown-item text-danger" onclick="' . $delete_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
					</div>
				</div>';

				$data[] = array(
					"sr_no"          => $start + $i + 1,
					"id"             => $id,
					"order_no"       => $item['order_no'],
					"invoice_no"     => $item['invoice_no'] ? $item['invoice_no'] : '-',
					"invoice_date"   => ($item['invoice_date'] != '0000-00-00' && $item['invoice_date'] != null) ? date('d M, Y', strtotime($item['invoice_date'])) : '-',
					"refrence_no"    => $item['refrence_no'],
					"customer_name"  => $item['customer_name'],
					"warehouse_name" => $item['warehouse_name'] ? $item['warehouse_name'] : '-',
					"total_pro"      => $item['product_count'],
					"qty"            => (int)$item['qty_count'],
					"grand_total"    => $item['grand_total'],
					"date"           => date('d M, Y', strtotime($item['date'])),
					"action"         => $action,
				);
				$i++;
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

	public function get_black_order()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (so.company_name like '%" . $keyword . "%' 
            OR so.refrence_no like '%" . $keyword . "%'
            OR so.order_no like '%" . $keyword . "%'
            OR sop.product_name like '%" . $keyword . "%'
            OR sop.item_code like '%" . $keyword . "%'
            OR sob.batch_no like '%" . $keyword . "%')";
		endif;
		
		if (isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != ""):
			$keyword        = $_REQUEST['customer_id'];
			$keyword_filter .= " AND (so.customer_id = '" . $keyword . "')";
		endif;
		
		$status = 'pending';
		if (isset($_REQUEST['status']) && $_REQUEST['status'] != ""):
			$status        = $_REQUEST['status'];
		endif;
		$keyword_filter .= " AND so.is_weird='1'";

		$batch_condition = "AND sob.black_qty > sob.recieved_black_qty";
		if ($status == 'completed') {
			$batch_condition = "AND sob.black_qty = sob.recieved_black_qty AND sob.black_qty > 0";
		}

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(so.date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(so.date) BETWEEN '$from' AND '$to')";
			}
		}

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (so.company_id='" . $company_id . "')";
			if($this->session->userdata('super_type_id') == 7) {
				$keyword_filter .= " AND (so.added_by_id = '" . $this->session->userdata('super_user_id') . "')";
			}
		}

		$total_count = $this->db->query("
			SELECT sob.id 
			FROM sales_order_product_batch AS sob
			INNER JOIN sales_order_product AS sop ON sob.order_product_id = sop.id
			INNER JOIN sales_order AS so ON so.id = sob.order_id
			WHERE (so.is_deleted='0') $batch_condition $keyword_filter
		")->num_rows();

		$query = $this->db->query("
			SELECT 
				sob.id AS batch_id, sob.batch_no, sob.black_qty, sob.recieved_black_qty,
				sop.product_name, sop.item_code,
				so.id AS order_id, so.order_type, so.order_no, so.refrence_no, so.is_generated, so.is_approved, so.date, so.customer_id, so.customer_name, so.warehouse_id, so.warehouse_name, so.grand_total, so.company_name, so.remark
			FROM sales_order_product_batch AS sob
			INNER JOIN sales_order_product AS sop ON sob.order_product_id = sop.id
			INNER JOIN sales_order AS so ON so.id = sob.order_id
			WHERE (so.is_deleted='0') $batch_condition $keyword_filter 
			ORDER BY so.date DESC 
			LIMIT $start, $length
		");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['order_id'];
				$order_type = $item['order_type'];
				$customer_id = $item['customer_id'];

				$edit_url = base_url() . 'inventory/sales-order/edit/' . $id;
				$invoice_url = base_url() . 'inventory/sales_order/invoice/' . $id;
				$gen_invoice_url = base_url() . 'inventory/sales_order/gen_invoice/' . $id;

				$action = '';

				$customer_name = $item['customer_name'];
				$gen_bill_modal_url = "showLargeModal('" . base_url() . "modal/popup_inventory/sales_order_generate_bill_modal/" . $customer_id . "/" . $id . "', 'Generate Bill')";
				$view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/sales_order_view_modal/" . $id . "','Sales Order View')";
				$invoice_white_url = base_url() . 'inventory/sales_order/invoice/white/' . $id;

				if($status == 'pending') {
					// $action .= '
					// <a href="#" onclick="' . $gen_bill_modal_url . '" data-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Generate Bill"><button type="button" class="btn mr-1 mb-1 btn-outline-primary"><i class="fa fa-file-text-o" aria-hidden="true"></i></button></a>';
					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
						<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
						</div>
					</div>';
				} else {
					$action ='<div class="btn-group">
						<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
						<i class="mdi mdi-dots-vertical"></i></button>
						<div class="dropdown-menu">
							<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
							<a class="dropdown-item" href="' . $invoice_white_url . '" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> View Invoice</a>
						</div>
					</div>';
				}

				$data[] = array(
					"sr_no"          => '<div class="justify-content-center"><input type="checkbox" class="form-check-input batch-checkbox" value="' . $item['batch_id'] . '" data-warehouse-id="' . $item['warehouse_id'] . '" data-customer-id="' . $item['customer_id'] . '" data-order-id="' . $item['order_id'] . '"></div>',
					"id"             => $item['order_id'],
					"order_no"       => $item['order_no'],
					"refrence_no"    => $item['refrence_no'],
					"customer_name"  => $customer_name,
					"product_name"   => $item['product_name'],
					"item_code"      => $item['item_code'],
					"batch_no"       => $item['batch_no'] ? $item['batch_no'] : '-',
					"warehouse_name" => ($item['warehouse_name']) ? $item['warehouse_name'] : '-',
					"company_name"   => ($item['company_name'] != '' && $item['company_name'] != null) ? $item['company_name'] : '-',
					"grand_total"    => $item['grand_total'],
					"date"           => date('d M, Y', strtotime($item['date'])),
					"black_qty"      => $item['black_qty'] - $item['recieved_black_qty'],
					"action"         => $action,
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

	public function get_completed_black_order()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (io.customer_name like '%" . $keyword . "%' 
            OR io.refrence_no like '%" . $keyword . "%'
            OR io.order_no like '%" . $keyword . "%'
            OR io.invoice_no like '%" . $keyword . "%')";
		endif;
		
		if (isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] != ""):
			$keyword        = $_REQUEST['customer_id'];
			$keyword_filter .= " AND (io.customer_id = '" . $keyword . "')";
		endif;

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(io.date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(io.date) BETWEEN '$from' AND '$to')";
			}
		}

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$keyword_filter .= " AND (io.company_id='" . $company_id . "')";
			if($this->session->userdata('super_type_id') == 7) {
				$keyword_filter .= " AND (io.added_by_id = '" . $this->session->userdata('super_user_id') . "')";
			}
		}

		$total_count = $this->db->query("
			SELECT io.id 
			FROM invoice_order AS io
			WHERE io.is_deleted = '0' AND io.type = 'bill' $keyword_filter
		")->num_rows();

		$query = $this->db->query("
			SELECT io.*,
				(SELECT COUNT(DISTINCT product_id) FROM invoice_order_products WHERE parent_id = io.id) AS product_count,
				(SELECT SUM(qty) FROM invoice_order_products WHERE parent_id = io.id) AS qty_count
			FROM invoice_order AS io
			WHERE io.is_deleted = '0' AND io.type = 'bill' $keyword_filter 
			ORDER BY io.date DESC 
			LIMIT $start, $length
		");

		if (!empty($query)) {
			$i = 0;
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$order_ids = explode(',', $item['unique_id']);
				$first_order_id = !empty($order_ids[0]) ? $order_ids[0] : 0;

				$view_url = "showLargeModal('" . base_url() . "modal/popup_inventory/invoice_order_view_modal/" . $id . "','Invoice Order View')";
				$invoice_bill_url = base_url() . 'inventory/invoice_order_print/' . $id;

				$action = '<div class="btn-group">
					<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
					<i class="mdi mdi-dots-vertical"></i></button>
					<div class="dropdown-menu">
						<a href="javascript:void(0)" class="dropdown-item" onclick="' . $view_url . '"><i class="fa fa-eye" aria-hidden="true"></i> View Order</a>
						<a class="dropdown-item" href="' . $invoice_bill_url . '" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Invoice Bill</a>
					</div>
				</div>';

				$data[] = array(
					"sr_no"          => $start + $i + 1,
					"id"             => $id,
					"order_no"       => $item['order_no'],
					"invoice_no"     => $item['invoice_no'] ? $item['invoice_no'] : '-',
					"refrence_no"    => $item['refrence_no'],
					"customer_name"  => $item['customer_name'],
					"warehouse_name" => $item['warehouse_name'] ? $item['warehouse_name'] : '-',
					"product_count"  => $item['product_count'] . ' Products',
					"qty_count"      => $item['qty_count'] . ' Qty',
					"grand_total"    => $item['grand_total'],
					"date"           => date('d M, Y', strtotime($item['date'])),
					"action"         => $action,
				);
				$i++;
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

	public function get_batch_details() {
		$batch_id = $this->input->post('batch_id');

		$batch = $this->db->query("SELECT id, quantity FROM inventory WHERE id='$batch_id'");
		$res = array('quantity' => 0);
		if($batch->num_rows() > 0){
			$row = $batch->row_array();
			$res['quantity'] = $row['quantity'];
		}

		echo json_encode($res);
	}

	public function get_batches_by_warehouse_product()
	{
		$warehouse_id = $this->input->post('warehouse_id');
		$product_id = $this->input->post('product_id');

		$query = $this->db->query("SELECT id, batch_no FROM inventory WHERE warehouse_id = '$warehouse_id' AND product_id = '$product_id' AND quantity > 0 GROUP BY batch_no");
		$html = '';
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				$html .= '<option value="' . $row['id'] . '">' . $row['batch_no'] . '</option>';
			}
		}
		echo $html;
	}

	public function get_batch_qty_details()
	{
		$batch_id = $this->input->post('batch_id');
		$query = $this->db->query("SELECT id, supplier_id, product_id, quantity, official_qty, black_qty FROM inventory WHERE id = '$batch_id'");
		$res = array('quantity' => 0, 'official_qty' => 0, 'black_qty' => 0, 'supplier_id' => null, 'product_id' => 0, 'min_selling_price' => 0, 'min_billing_price' => 0);
		if ($query->num_rows() > 0) {
			$res = $query->row_array();
			$supplier_id = !empty($res['supplier_id']) ? intval($res['supplier_id']) : 0;
			$product_id = !empty($res['product_id']) ? intval($res['product_id']) : 0;

			$min_selling_price = 0;
			$min_billing_price = 0;
			$found_variation = false;

			if ($supplier_id > 0 && $product_id > 0) {
				$pv_row = $this->db->get_where('product_variations', [
					'product_id' => $product_id,
					'supplier_id' => $supplier_id
				])->row_array();

				if ($pv_row) {
					if (isset($pv_row['costing_price'])) {
						$min_selling_price = (float)$pv_row['costing_price'];
					}
					if (isset($pv_row['product_mrp'])) {
						$min_billing_price = (float)$pv_row['product_mrp'];
					}
					$found_variation = true;
				}
			}

			if (!$found_variation && $product_id > 0) {
				$rp_row = $this->db->get_where('raw_products', [
					'id' => $product_id
				])->row_array();

				if ($rp_row) {
					if (isset($rp_row['costing_price'])) {
						$min_selling_price = (float)$rp_row['costing_price'];
					}
					if (isset($rp_row['product_mrp'])) {
						$min_billing_price = (float)$rp_row['product_mrp'];
					}
				}
			}

			$res['min_selling_price'] = $min_selling_price;
			$res['min_billing_price'] = $min_billing_price;
		}
		echo json_encode($res);
	}

	public function get_warehouse_product_qty()
	{
		$warehouse_id = $this->input->post('warehouse_id');
		$product_id = $this->input->post('product_id');

		$query = $this->db->query("SELECT SUM(official_qty) as total_white, SUM(black_qty) as total_black FROM inventory WHERE warehouse_id = '$warehouse_id' AND product_id = '$product_id'");
		$res = array('total_white' => 0, 'total_black' => 0);
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$res['total_white'] = $row['total_white'] ? (float)$row['total_white'] : 0;
			$res['total_black'] = $row['total_black'] ? (float)$row['total_black'] : 0;
		}
		echo json_encode($res);
	}

	public function get_product_batch()
	{
		$prod_id = $this->input->post('product_id');

		$batch = $this->db->query("SELECT id, batch_no FROM inventory WHERE quantity > 0 AND product_id='$prod_id'");
		
		$data = array();
		if($batch->num_rows() > 0){
			foreach($batch->result_array() as $item){
				$data[] = array(
					"id" => $item['id'],
					"batch_no" => $item['batch_no'],
				);
			}
		}
		echo json_encode($data);
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

	function gen_invoice_sales_order($id)
	{
		$this->db->trans_start(); // Start transaction

		try {
			$sales = $this->common_model->getRowById('sales_order', 'warehouse_id', ['id' => $id]);
			if (!$sales) {
				throw new Exception(get_phrase('sales_order_not_found'));
			}

			// Soft update sales order
			$this->db->where('id', $id)->update('sales_order', ['is_generated' => 1]);

			$this->db->trans_commit(); // Commit transaction

			$resultpost = [
				"status" => 200,
				"message" => get_phrase('invoice_generated_successfully'),
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

	function gen_invoice_sales_order_post()
	{
		$this->db->trans_start(); // Start transaction

		try {
			$sales_order_ids = $this->input->post('sales_order_id');
			if (empty($sales_order_ids) || !is_array($sales_order_ids)) {
				throw new Exception(get_phrase('please_select_at_least_one_product'));
			}

			$invoice_no = clean_and_escape($this->input->post('invoice_no'));
			$invoice_date = clean_and_escape($this->input->post('invoice_date'));
			
			if (empty($invoice_no)) {
				throw new Exception(get_phrase('invoice_number_cannot_be_empty'));
			}
			if (empty($invoice_date)) {
				throw new Exception(get_phrase('invoice_date_cannot_be_empty'));
			}

			// Check if invoice_no already exists in non-deleted sales orders
			$check_exists = $this->db->where('invoice_no', $invoice_no)
									 ->where('is_deleted', '0')
									 ->get('invoice_order');
			if ($check_exists->num_rows() > 0) {
				throw new Exception(get_phrase('invoice_no_has_already_been_used'));
			}

			$sales_order_id = $this->input->post('sales_order_id');
			$batch_id = $this->input->post('id');
			$batch_no = $this->input->post('batch_no');
			$product_id = $this->input->post('product_id');
			$product_name = $this->input->post('product_name');
			$item_code = $this->input->post('item_code');

			$order = $this->input->post('order');
			$order_id = $this->input->post('order_id');
			$order_product_id = $this->input->post('order_product_id');
			$is_valid = $this->input->post('is_valid');
			$pending_qty = $this->input->post('pending_qty');
			$recieved_qty = $this->input->post('recieved_qty');
			$bill_amount = $this->input->post('bill_amount');
			$gst = $this->input->post('gst');

			$order_det = $this->db->where('id', $sales_order_id[0])->get('sales_order')->row_array();

			if($order_det){
				$inv_order = [
					"type" => "normal",
					"unique_id" => implode(',', $sales_order_id),
					"invoice_no" => $invoice_no,
					"invoice_date" => $invoice_date,
					"refrence_no" => $order_det["refrence_no"],
					"date" => $order_det["date"],
					"customer_id" => $order_det["customer_id"],
					"customer_name" => $order_det["customer_name"],
					"shipping_state_id" => $order_det["shipping_state_id"],
					"shipping_state_name" => $order_det["shipping_state_name"],
					"shipping_city_id" => $order_det["shipping_city_id"],
					"shipping_city_name" => $order_det["shipping_city_name"],
					"shipping_pincode" => $order_det["shipping_pincode"],
					"shipping_gst" => $order_det["shipping_gst"],
					"shipping_gst_no" => $order_det["shipping_gst_no"],
					"shipping_address" => $order_det["shipping_address"],
					"billing_state_id" => $order_det["billing_state_id"],
					"billing_state_name" => $order_det["billing_state_name"],
					"billing_city_id" => $order_det["billing_city_id"],
					"billing_city_name" => $order_det["billing_city_name"],
					"billing_pincode" => $order_det["billing_pincode"],
					"billing_gst" => $order_det["billing_gst"],
					"billing_gst_no" => $order_det["billing_gst_no"],
					"billing_address" => $order_det["billing_address"],
					"warehouse_id" => $order_det["warehouse_id"],
					"warehouse_name" => $order_det["warehouse_name"],
					"company_id" => $order_det["company_id"],
					"company_name" => $order_det["company_name"],
					"narration" => $order_det["narration"],
					"remark" => $order_det["remark"],
					"gst_type" => $order_det["gst_type"],
					"cgst_per" => $order_det["cgst_per"],
					"sgst_per" => $order_det["sgst_per"],
					"igst_per" => $order_det["igst_per"],
					"added_by_id" => $this->session->userdata('super_user_id'),
					"added_by_name" => $this->session->userdata('super_name'),
					"added_date" => date("Y-m-d H:i:s"),
				];

				$invoice_order = $this->db->insert("invoice_order", $inv_order);
				$last_id = $this->db->insert_id();

				$total_basic_amt = 0;
				$total_gst_amt = 0;
				$total_final_amt = 0;

				foreach($is_valid as $in => $is_valid) {
					if($is_valid == 1) {
						if($recieved_qty[$in] > $pending_qty[$in]) {
							throw new Exception(get_phrase('received_qty_cannot_be_greater_than_pending_qty'));
						} elseif($recieved_qty[$in] == 0) {
							throw new Exception(get_phrase('received_qty_cannot_be_0'));
						} else {

							$bill_amt = $bill_amount[$in];
							$gst_amt = ($bill_amount[$in] * $recieved_qty[$in]) * ($gst[$in] / 100);
							$total_bill_gst_amount = $gst_amt + ($bill_amount[$in] * $recieved_qty[$in]);

							$total_basic_amt += $bill_amt;
							$total_gst_amt += $gst_amt;
							$total_final_amt += $total_bill_gst_amount;

							$inv_products = [
								"parent_id" => $last_id,
								"batch_id" => $batch_id[$in],
								"order_id" => $order_id[$in],
								"product_id" => $product_id[$in],
								"product_name" => $product_name[$in],
								"qty" => $recieved_qty[$in],
								"item_code" => $item_code[$in],
								"amount" => $bill_amount[$in],
								"total_amount" => $bill_amount[$in] * $recieved_qty[$in],
								"bill_amount" => $bill_amt,
								"bill_total" => $bill_amt * $recieved_qty[$in],
								"gst" => $gst[$in],
								"gst_amount" => $gst_amt,
								"total_bill_gst_amount" => $total_bill_gst_amount,
								"final_total" => $total_bill_gst_amount,
							];

							$this->db->insert("invoice_order_products", $inv_products);

							$current_batch = $this->db->where('id', $batch_id[$in])->get('sales_order_product_batch')->row_array();
							$this->db->where('id', $batch_id[$in])->update('sales_order_product_batch', [
								"recieved_qty" => $current_batch['recieved_qty'] + $recieved_qty[$in]
							]);

							$this->common_model->markInvoiceGenerated($order_id[$in]);
						}
					}
				}

				$this->db->where('id', $last_id)->update('invoice_order', [
					'basic_value' => $total_basic_amt,
					'net_sales_value_1' => $total_basic_amt,
					'gst_total' => $total_gst_amt,
					'net_sales_value_2' => $total_final_amt,
					'grand_total' => $total_final_amt
				]);

				// $logs = [
				// 	"parent_id" => $last_id,
				// 	"ref_id" => NULL,
				// 	"module" => "sales",
				// 	"action" => "generate_invoice",
				// 	"message" => "Invoice generated by " . $this->session->userdata('super_name'),
				// 	"json" => json_encode($invoice),
				// 	"table_name" => "invoice_order",
				// 	"added_by" => $this->session->userdata('super_user_id'),
				// 	"added_by_email" => $this->session->userdata('super_email'),
				// 	"added_by_name" => $this->session->userdata('super_name'),
				// 	"added_by_type" => $this->session->userdata('super_type')
				// ];

				// $this->db->insert('sys_logs', $logs);

			} else {
				throw new Exception(get_phrase('no_order_found'));
			}

			$this->db->trans_commit(); // Commit transaction

			$resultpost = [
				"status" => 200,
				"message" => get_phrase('invoice_generated_successfully'),
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

	function generate_bill_sales_order_post()
	{
		$this->db->trans_start(); // Start transaction

		try {
			$checked_batches = $this->input->post('checked_batches');
			if (empty($checked_batches)) {
				throw new Exception(get_phrase('please_select_at_least_one_item'));
			}

			$customer_id = $this->input->post('customer_id');
			$warehouse_id = $this->input->post('warehouse_id');

			if (empty($customer_id) || empty($warehouse_id)) {
				throw new Exception(get_phrase('invalid_customer_or_warehouse'));
			}

			$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
			$warehouse_name = $this->common_model->selectByidParam($warehouse_id, 'warehouse', 'name');

			$invoice_no = clean_and_escape($this->input->post('invoice_no'));
			$invoice_date = clean_and_escape($this->input->post('invoice_date'));

			if (empty($invoice_no)) {
				throw new Exception(get_phrase('invoice_number_cannot_be_empty'));
			}
			if (empty($invoice_date)) {
				throw new Exception(get_phrase('invoice_date_cannot_be_empty'));
			}

			// Check if invoice_no already exists in non-deleted invoice orders
			$check_exists = $this->db->where('invoice_no', $invoice_no)
									 ->where('is_deleted', '0')
									 ->get('invoice_order');
			if ($check_exists->num_rows() > 0) {
				throw new Exception(get_phrase('invoice_no_has_already_been_used'));
			}

			$company_id = $this->session->userdata('company_id');
			$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

			$round_of = ($this->input->post('round_of') != '') ? $this->input->post('round_of') : 0;
			$gst_type = clean_and_escape($this->input->post('gst_type'));

			$basic_value = price_format_decimal($this->input->post('basic_value'));
			$net_sales_value_1 = price_format_decimal($this->input->post('net_sales_value_1'));
			$net_sales_value_2 = $net_sales_value_1;
			$grand_total = price_format_decimal($this->input->post('grand_total'));
			$central_gst = price_format_decimal($this->input->post('central_gst'));
			$state_gst = price_format_decimal($this->input->post('state_gst'));
			$igst = price_format_decimal($this->input->post('igst'));
			$gst_total = ($gst_type == 'IGST') ? $igst : ($central_gst + $state_gst);

			$order_no = $this->input->post('order_no');

			$shipping_state_id = $this->input->post('shipping_state_id');
			$shipping_city_id  = $this->input->post('shipping_city_id');
			$shipping_pincode  = clean_and_escape($this->input->post('shipping_pincode'));
			$shipping_gst      = clean_and_escape($this->input->post('shipping_gst'));
			$shipping_gst_no   = clean_and_escape($this->input->post('shipping_gst_no'));
			$shipping_address  = clean_and_escape($this->input->post('shipping_address'));

			$billing_state_id  = $this->input->post('billing_state_id');
			$billing_city_id   = $this->input->post('billing_city_id');
			$billing_pincode   = clean_and_escape($this->input->post('billing_pincode'));
			$billing_gst       = clean_and_escape($this->input->post('billing_gst'));
			$billing_gst_no    = clean_and_escape($this->input->post('billing_gst_no'));
			$billing_address   = clean_and_escape($this->input->post('billing_address'));

			// Find all unique sales order IDs affected by these batches
			$affected_order_ids = [];
			foreach ($checked_batches as $batch_id) {
				$batch_rec = $this->db->get_where('sales_order_product_batch', ['id' => $batch_id])->row_array();
				if ($batch_rec) {
					$affected_order_ids[] = $batch_rec['order_id'];
				}
			}
			$affected_order_ids = array_unique($affected_order_ids);

			$inv_order = [
				"type" => "bill",
				"unique_id" => implode(',', $affected_order_ids),
				"order_no" => $order_no,
				"invoice_no" => $invoice_no,
				"invoice_date" => $invoice_date,
				"refrence_no" => clean_and_escape($this->input->post('refrence_no')),
				"date" => ($this->input->post('date')),
				"customer_id" => $customer_id,
				"customer_name" => $customer_name,
				"shipping_state_id" => $shipping_state_id,
				"shipping_state_name" => ($shipping_state_id > 0) ? (string) $this->common_model->get_state_name($shipping_state_id) : '',
				"shipping_city_id" => $shipping_city_id,
				"shipping_city_name" => ($shipping_city_id > 0) ? (string) $this->common_model->get_city_name($shipping_city_id) : '',
				"shipping_pincode" => $shipping_pincode,
				"shipping_gst" => $shipping_gst,
				"shipping_gst_no" => $shipping_gst_no,
				"shipping_address" => $shipping_address,
				"billing_state_id" => $billing_state_id,
				"billing_state_name" => ($billing_state_id > 0) ? (string) $this->common_model->get_state_name($billing_state_id) : '',
				"billing_city_id" => $billing_city_id,
				"billing_city_name" => ($billing_city_id > 0) ? (string) $this->common_model->get_city_name($billing_city_id) : '',
				"billing_pincode" => $billing_pincode,
				"billing_gst" => $billing_gst,
				"billing_gst_no" => $billing_gst_no,
				"billing_address" => $billing_address,
				"warehouse_id" => $warehouse_id,
				"warehouse_name" => $warehouse_name,
				"company_id" => $company_id,
				"company_name" => $company_name,
				"narration" => clean_and_escape($this->input->post('narration')),
				"remark" => clean_and_escape($this->input->post('remark')),
				"gst_type" => $gst_type,
				"cgst_per" => 0,
				"sgst_per" => 0,
				"igst_per" => 0,
				"basic_value" => $basic_value,
				"net_sales_value_1" => $net_sales_value_1,
				"gst_total" => $gst_total,
				"net_sales_value_2" => $net_sales_value_2,
				"round_of" => $round_of,
				"grand_total" => $grand_total,
				"added_by_id" => $this->session->userdata('super_user_id'),
				"added_by_name" => $this->session->userdata('super_name'),
				"added_date" => date("Y-m-d H:i:s"),
			];

			$this->db->insert("invoice_order", $inv_order);
			$invoice_order_id = $this->db->insert_id();

			foreach ($checked_batches as $orig_batch_id) {
				$qty = (float) $this->input->post("batch_qty")[$orig_batch_id];
				$rate = (float) $this->input->post("batch_rate")[$orig_batch_id];
				$gst_per = (float) $this->input->post("batch_gst")[$orig_batch_id];
				$batch_no = $this->input->post("batch_no")[$orig_batch_id];
				$prod_id = $this->input->post("batch_product_id")[$orig_batch_id];
				$orig_order_id = $this->input->post("batch_order_id")[$orig_batch_id];

				$total_amt = $qty * $rate;
				$gst_amt = ($total_amt * $gst_per) / 100;
				$total_bill_gst_amount = $total_amt + $gst_amt;

				// Fetch product name and item code
				$orig_batch_record = $this->db->get_where('sales_order_product_batch', ['id' => $orig_batch_id])->row_array();
				$prod_name = '';
				$item_code = '';
				if ($orig_batch_record) {
					$orig_prod = $this->db->get_where('sales_order_product', ['id' => $orig_batch_record['order_product_id']])->row_array();
					if ($orig_prod) {
						$prod_name = $orig_prod['product_name'];
						$item_code = $orig_prod['item_code'];
					}
				}
				if (empty($prod_name)) {
					$product_details = $this->db->get_where('products', ['id' => $prod_id])->row_array();
					$prod_name = $product_details['name'] ?? '';
					$item_code = $product_details['item_code'] ?? '';
				}

				$inv_products = [
					"parent_id" => $invoice_order_id,
					"batch_id" => $orig_batch_id,
					"order_id" => $orig_order_id,
					"product_id" => $prod_id,
					"product_name" => $prod_name,
					"qty" => $qty,
					"item_code" => $item_code,
					"amount" => $rate,
					"total_amount" => $total_amt,
					"bill_amount" => $rate,
					"bill_total" => $total_amt,
					"gst" => $gst_per,
					"gst_amount" => $gst_amt,
					"total_bill_gst_amount" => $total_bill_gst_amount,
					"final_total" => $total_bill_gst_amount,
				];

				$this->db->insert("invoice_order_products", $inv_products);

				// Update original batch: adjust recieved_black_qty and avail_black_qty
				if ($orig_batch_record) {
					$this->db->where('id', $orig_batch_id)->update('sales_order_product_batch', [
						'recieved_black_qty' => $orig_batch_record['recieved_black_qty'] + $qty,
					]);
				}

				// Decrement pending_qty of batch in inventory table
				$inv_batch = $this->db->get_where('inventory', [
					'product_id' => $prod_id,
					'warehouse_id' => $warehouse_id,
					'batch_no' => $batch_no
				])->row_array();
				if ($inv_batch) {
					$new_pending_qty = max(0, $inv_batch['pending_qty'] - $qty);
					$this->db->where('id', $inv_batch['id'])->update('inventory', [
						'pending_qty' => $new_pending_qty
					]);

					// Decrement pending_qty of batch in inventory_history table as well
					$hist_record = $this->db->get_where('inventory_history', [
						'order_id' => $orig_order_id,
						'parent_id' => $inv_batch['id'],
						'status' => 'out'
					])->row_array();
					if ($hist_record) {
						$new_hist_pending = max(0, $hist_record['pending_qty'] - $qty);
						$this->db->where('id', $hist_record['id'])->update('inventory_history', [
							'pending_qty' => $new_hist_pending
						]);
					}
				}

				$this->common_model->markInvoiceGenerated($orig_order_id);
			}

			$this->db->trans_commit(); // Commit transaction

			$resultpost = [
				"status" => 200,
				"message" => get_phrase('invoice_generated_successfully'),
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

	function delete_sales_order($id)
	{
		$this->db->trans_begin(); // Start transaction

		try {
			$sales = $this->common_model->getRowById('sales_order', '*', ['id' => $id]);
			if (!$sales) {
				throw new Exception(get_phrase('sales_order_not_found'));
			}

			$reverted_data = [];
			$history_data = [];

			if ($sales['is_approved'] != 0) {
				// Retrieve stock history records associated with this sales order that are not deleted and have status 'out'
				$history_records = $this->common_model->getResultById('inventory_history', '*', [
					'order_id' => $id,
					'status' => 'out',
					'is_deleted' => 0
				]);

				if (!empty($history_records)) {
					foreach ($history_records as $his) {
						$inv_id = $his['parent_id'];
						$inv = $this->common_model->getRowById('inventory', '*', ['id' => $inv_id]);
						if ($inv) {
							$new_qty = $inv['quantity'] + $his['quantity'];
							$new_official = $inv['official_qty'] + $his['official_qty'];
							$new_black = $inv['black_qty'] + $his['black_qty'];

							// Revert stock in inventory batch
							$this->db->where('id', $inv_id)->update('inventory', [
								'quantity' => $new_qty,
								'official_qty' => $new_official,
								'black_qty' => $new_black
							]);

							$reverted_data[] = [
								'inventory_id' => $inv_id,
								'product_id'   => $inv['product_id'],
								'batch_no'     => $inv['batch_no'],
								'old_qty'      => $inv['quantity'],
								'new_qty'      => $new_qty,
								'old_official' => $inv['official_qty'],
								'new_official' => $new_official,
								'old_black'    => $inv['black_qty'],
								'new_black'    => $new_black
							];
						}

						// Soft delete stock history entry
						$this->db->where('id', $his['id'])->update('inventory_history', ['is_deleted' => 1]);
						$history_data[] = $his;
					}
				}
			}

			// Soft delete the sales order itself
			$this->db->where('id', $id)->update('sales_order', ['is_deleted' => 1]);

			// Reverting Payment Entries
			$payment_log = [];
			$payment_records = $this->db->where('order_id', $id)->get('customer_payment_record');
			if ($payment_records->num_rows() > 0) {
				foreach ($payment_records->result_array() as $record) {
					$this->db->where('id', $record['id'])->update('customer_payment_record', ['is_deleted' => 1]);

					$payment = $this->db->where('id', $record['payment_id'])->get('customer_payment');
					if ($payment->num_rows() > 0) {
						$payment = $payment->row_array();
						$updated_record = [
							"allocated_inv" => $payment['allocated_inv'] - $record['order_paid'],
							"total_outstanding" => $payment['total_outstanding'] - $record['order_paid'],
							"on_account" => $payment['on_account'] + $record['order_paid'],
						];

						$this->db->where('id', $record['payment_id'])->update('customer_payment', $updated_record);

						$customer_credit = [
							'payment_id' => $record['payment_id'],
							'customer_id' => $payment['customer_id'],
							'item_no' => "Sales Deleted - " . $id,
							'date' => date('Y-m-d'),
							'credit_balance' => $record['order_paid'],
							'debit_balance' => "0",
							'created_at' => date('Y-m-d H:i:s'),
						];

						$this->db->insert('customer_credit',$customer_credit);

						$payment_log[] = [
							"record" => $record,
							"credit" => $customer_credit,
							"updated_payment" => $updated_record,
							"payment" => $payment
						];
					}
				}
			}

			// Create JSON log details
			$log_json = [
				'sale_order'    => $sales,
				'reverted_data' => $reverted_data,
				'history_data'  => $history_data,
				'payment_log'   => $payment_log
			];

			$log_data = array(
				'parent_id'      => $id,
				'ref_id'         => NULL,
				'module'         => 'sales',
				'action'         => 'delete',
				'message'        => 'Sale Order deleted by ' . $this->session->userdata('super_name'),
				'json'           => json_encode($log_json),
				'table_name'     => 'sales_order',
				'added_by'       => $this->session->userdata('super_user_id'),
				'added_by_email' => $this->session->userdata('super_email'),
				'added_by_name'  => $this->session->userdata('super_name'),
				'added_by_type'  => $this->session->userdata('super_type')
			);
			$this->db->insert('sys_logs', $log_data);

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost = [
					"status" => 400,
					"message" => "Error occurred while deleting Sales Order",
				];
			} else {
				$this->db->trans_commit();
				$resultpost = [
					"status" => 200,
					"message" => get_phrase('sales_order_delete_successfully'),
					"url" => $this->session->userdata('previous_url'),
				];
			}
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

	public function get_white_sales_order_details_by_id($id, $type = 'white')
	{
		$sales = $this->db->where('id', $id)->get('sales_order')->row_array();
		
		$white_products = $this->db->query("
			SELECT 
				sp.product_name, p.hsn_code, SUM(sb.white_qty + sb.black_qty) as qtys,
				SUM(sb.bill_amount) as amount, SUM(sb.gst_amount) as gst_amount,
				sb.gst, (SUM(sb.bill_amount) + SUM(sb.gst_amount)) as total 
			FROM sales_order_product as sp
			INNER JOIN sales_order_product_batch as sb ON sb.order_product_id = sp.id
			INNER JOIN raw_products as p ON p.id = sp.product_id
			WHERE sp.order_id = $id AND sb.bill_amount > 0
			GROUP BY sp.id
		");

		$sales['products'] = ($white_products->num_rows() > 0) ? $white_products->result_array() : [];

		$company = $this->common_model->getRowById('company', '*', ['id' => $sales['company_id']]);
		$sales['company'] = ($company) ? $company : [];
		
		$customer = $this->common_model->getRowById('customer', '*', ['id' => $sales['customer_id']]);
		$sales['customer'] = ($customer) ? $customer : [];

		return $sales;
	}

	public function get_black_sales_order_details_by_id($id, $type = 'white')
	{
		$sales = $this->db->where('id', $id)->get('sales_order')->row_array();
		
		$black_products = $this->db->query("
			SELECT 
				sp.product_name, p.hsn_code, SUM(sb.white_qty + sb.black_qty) as qtys,
				SUM(sb.bill_amount) as white_amt, SUM(sb.black_amount) as black_amt, SUM(sb.gst_amount) as gst_amount,
				sb.gst, (SUM(sb.bill_amount) + SUM(sb.gst_amount) + SUM(sb.black_total)) as total 
			FROM sales_order_product as sp
			INNER JOIN sales_order_product_batch as sb ON sb.order_product_id = sp.id
			INNER JOIN raw_products as p ON p.id = sp.product_id
			WHERE sp.order_id = $id 
			GROUP BY sp.id
		");

		// echo json_encode($black_products);exit();
		$sales['products'] = ($black_products->num_rows() > 0) ? $black_products->result_array() : [];
		
		$company = $this->common_model->getRowById('company', '*', ['id' => $sales['company_id']]);
		$sales['company'] = ($company) ? $company : [];
		
		$customer = $this->common_model->getRowById('customer', '*', ['id' => $sales['customer_id']]);
		$sales['customer'] = ($customer) ? $customer : [];

		return $sales;
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
		$query = $this->db->query("SELECT gr.id,gr.added_date,gr.date,gr.warehouse_name,gr.customer_name,gr.company_name,gr.reason,gr.order_no,grp.product_name,grp.item_code,grp.quantity,grp.batch_no,grp.white_qty,grp.black_qty,grp.white_amt,grp.white_total,grp.black_amt,grp.black_total,grp.final_total FROM goods_return as gr
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
					"white_qty"       => $item['white_qty'],
					"black_qty"       => $item['black_qty'],
					"white_amt"       => number_format($item['white_amt'], 2),
					"white_total"     => number_format($item['white_total'], 2),
					"black_amt"       => number_format($item['black_amt'], 2),
					"black_total"     => number_format($item['black_total'], 2),
					"final_total"     => number_format($item['final_total'], 2),
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
		
		$type = $this->input->post('type', true); // 'official' or 'unofficial'
		$customer_id = $this->input->post('customer_id', true);
		$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
		
		$company_id = $this->session->userdata('company_id');
		if (empty($company_id)) {
			$company_id = $this->input->post('company_id', true);
		}
		if (empty($company_id)) {
			$company_id = 0;
		}
		
		$order_no = $this->input->post('order_no', true);
		$date = $this->input->post('date', true);
		$reason = $this->input->post('reason', true);

		// Array inputs
		$batch_nos = $this->input->post('batch_no', true);
		$product_ids = $this->input->post('product_id', true);
		$product_batch_ids = $this->input->post('product_batch_id', true);
		$amounts = $this->input->post('amount', true);
		$gsts = $this->input->post('gst', true);
		$gst_amts = $this->input->post('gst_amt', true);
		$white_qtys = $this->input->post('white_qty', true);
		$black_qtys = $this->input->post('black_qty', true);
		$white_amts = $this->input->post('white_amt', true);
		$white_totals = $this->input->post('white_total_row', true);
		$black_amts = $this->input->post('black_amt', true);
		$black_totals = $this->input->post('black_total_row', true);
		$final_totals = $this->input->post('final_total_row', true);

		$white_total = $this->input->post('white_total', true);
		$gst_total_amt = $this->input->post('gst_total_amt', true);
		$black_total = $this->input->post('black_total', true);
		$final_total = $this->input->post('final_total', true);

		// Find the database ID of the sales_order or invoice_order and populate company_id if not present
		$order_db_id = 0;
		if ($type === 'official') {
			$this->db->where('invoice_no', $order_no);
			$this->db->where('is_deleted', 0);
			if (!empty($company_id)) {
				$this->db->where('company_id', $company_id);
			}
			$order_row = $this->db->get('invoice_order')->row_array();
			if ($order_row) {
				$order_db_id = (int)$order_row['id'];
				$company_id = (int)$order_row['company_id'];
			}
		} else {
			$this->db->where('order_no', $order_no);
			$this->db->where('is_deleted', 0);
			if (!empty($company_id)) {
				$this->db->where('company_id', $company_id);
			}
			$order_row = $this->db->get('sales_order')->row_array();
			if ($order_row) {
				$order_db_id = (int)$order_row['id'];
				$company_id = (int)$order_row['company_id'];
			}
		}

		$company_name = $this->common_model->selectByidParam($company_id, 'company', 'name');

		$is_white_to_black = $this->input->post('is_white_to_black', true) ? 1 : 0;
		$gst_type          = $this->input->post('gst_type', true);
		$central_gst       = $this->input->post('central_gst', true);
		$state_gst         = $this->input->post('state_gst', true);
		$igst              = $this->input->post('igst', true);
		$other_charges_amt = $this->input->post('other_charges_amount', true);
		$round_of          = $this->input->post('round_of', true);
		$grand_total       = $this->input->post('grand_total', true);

		$data = array();
		$excel_id = $this->input->post('excel_id');
		$method = $type; // "store the type here goods_return.sql:L3 [method]"

		$data['method']            = $method;
		$data['excel_id']          = $excel_id;
		$data['is_white_to_black'] = $is_white_to_black;
		$data['warehouse_id']      = NULL; // "keep warehouse empty goods_return.sql:L6-L7"
		$data['warehouse_name']    = '';
		$data['customer_id']       = $customer_id;
		$data['company_id']        = $company_id;
		$data['customer_name']     = $customer_name;
		$data['company_name']      = $company_name;
		$data['order_no']          = $order_no;
		$data['date']              = date('Y-m-d', strtotime($date));
		$data['reason']            = $reason;
		$data['white_total']       = !empty($white_total) ? (float)$white_total : 0.00;
		$data['gst_type']          = !empty($gst_type) ? $gst_type : '';
		$data['cgst_amt']          = !empty($central_gst) ? (float)$central_gst : 0.00;
		$data['sgst_amt']          = !empty($state_gst) ? (float)$state_gst : 0.00;
		$data['igst_amt']          = !empty($igst) ? (float)$igst : 0.00;
		$data['gst_total_amt']     = !empty($gst_total_amt) ? (float)$gst_total_amt : 0.00;
		$data['black_total']       = !empty($black_total) ? (float)$black_total : 0.00;
		$data['final_total']       = !empty($final_total) ? (float)$final_total : 0.00;
		$data['other_charges']     = !empty($other_charges_amt) ? (float)$other_charges_amt : 0.00;
		$data['round_of']          = !empty($round_of) ? (float)$round_of : 0.00;
		$data['grand_total']       = !empty($grand_total) ? (float)$grand_total : 0.00;
		$data['added_by_id']       = $this->session->userdata('super_user_id');
		$data['added_by_name']     = $this->session->userdata('super_name');
		$data['added_date']        = date("Y-m-d H:i:s");
		
		$insert = $this->db->insert('goods_return', $data);
		$parent_id = $this->db->insert_id();

		// Insert Other Charges into goods_return_charges table
		$charge_ids    = $this->input->post('charge_id', true);
		$charge_gsts   = $this->input->post('charge_gst', true);
		$charge_prices = $this->input->post('charge_price', true);
		$charge_totals = $this->input->post('charge_total', true);

		if (!empty($charge_ids) && is_array($charge_ids)) {
			for ($c = 0; $c < count($charge_ids); $c++) {
				$chg_id = (int)$charge_ids[$c];
				if ($chg_id > 0) {
					$chg_row  = $this->db->where('id', $chg_id)->get('other_charges')->row_array();
					$chg_name = $chg_row ? $chg_row['name'] : '';
					$chg_gst   = isset($charge_gsts[$c]) ? (float)$charge_gsts[$c] : 0.00;
					$chg_price = isset($charge_prices[$c]) ? (float)$charge_prices[$c] : 0.00;
					$chg_total = isset($charge_totals[$c]) ? (float)$charge_totals[$c] : 0.00;

					$data_charge = array(
						'order_id'   => $parent_id,
						'type_id'    => $chg_id,
						'type'       => $chg_name,
						'gst'        => $chg_gst,
						'amount'     => $chg_price,
						'total_amt'  => $chg_total,
						'created_at' => date("Y-m-d H:i:s")
					);
					$this->db->insert('goods_return_charges', $data_charge);
				}
			}
		}

		if (!empty($product_ids) && is_array($product_ids)) {
			for ($i = 0; $i < count($product_ids); $i++) {
				$prod_id = $product_ids[$i];
				$b_no = $batch_nos[$i];
				$w_qty = (int)$white_qtys[$i];
				$b_qty = (int)$black_qtys[$i];
				$amt = isset($amounts[$i]) ? (float)$amounts[$i] : 0.00;
				$w_amt = (float)$white_amts[$i];
				$w_total = isset($white_totals[$i]) ? (float)$white_totals[$i] : ($w_qty * $w_amt);
				$b_amt = (float)$black_amts[$i];
				$b_total = isset($black_totals[$i]) ? (float)$black_totals[$i] : ($b_qty * $b_amt);
				$batch_prod_id = isset($product_batch_ids[$i]) ? (int)$product_batch_ids[$i] : 0;
				$gst = isset($gsts[$i]) ? (float)$gsts[$i] : 0.00;
				$gst_amt = isset($gst_amts[$i]) ? (float)$gst_amts[$i] : ($type === 'official' ? ($w_total * ($gst / 100)) : 0.00);
				$final_row_total = isset($final_totals[$i]) ? (float)$final_totals[$i] : ($w_total + $b_total + $gst_amt);
				$tot_qty = $w_qty + $b_qty;

				if ($tot_qty > 0 && !empty($prod_id)) {
					// Get product details
					$product_name = 'Unknown Product';
					$item_code = '';
					
					if ($type === 'official' && $batch_prod_id > 0) {
						$invoice_prod = $this->db->where('id', $batch_prod_id)->get('invoice_order_products')->row_array();
						if ($invoice_prod) {
							$product_name = $invoice_prod['product_name'];
							$item_code = $invoice_prod['item_code'];
						}
					} else if ($type === 'unofficial' && $batch_prod_id > 0) {
						$batch_row = $this->db->where('id', $batch_prod_id)->get('sales_order_product_batch')->row_array();
						if ($batch_row) {
							$sales_prod = $this->db->where('id', $batch_row['order_product_id'])->get('sales_order_product')->row_array();
							if ($sales_prod) {
								$product_name = $sales_prod['product_name'];
								$item_code = $sales_prod['item_code'];
							}
						}
					}

					if (empty($product_name) || $product_name === 'Unknown Product') {
						$product_info = $this->db->where('id', $prod_id)->get('raw_products')->row_array();
						if ($product_info) {
							$product_name = $product_info['name'];
							$item_code = isset($product_info['item_code']) ? $product_info['item_code'] : (isset($product_info['code']) ? $product_info['code'] : '');
						}
					}

					$data_p = array(
						'parent_id' => $parent_id,
						'order_id' => $order_db_id,
						'product_batch_id' => $batch_prod_id,
						'product_id' => $prod_id,
						'product_name' => $product_name,
						'white_qty' => $w_qty,
						'black_qty' => $b_qty,
						'amount' => $amt,
						'white_amt' => $w_amt,
						'white_total' => $w_total,
						'black_amt' => $b_amt,
						'black_total' => $b_total,
						'gst' => $gst,
						'gst_amt' => $gst_amt,
						'final_total' => $final_row_total,
						'item_code' => $item_code,
						'quantity' => $tot_qty,
						'batch_no' => $b_no
					);
					$insert_1 = $this->db->insert('goods_return_product', $data_p);

					if ($insert_1) {
						// 1. Insert/Update Stock (inventory table)
						// Query inventory for this product, batch, and company at warehouse_id = 0
						$this->db->where('product_id', $prod_id);
						$this->db->where('batch_no', $b_no);
						$this->db->where('company_id', $company_id);
						$this->db->where('warehouse_id', 0);
						$inv_prod = $this->db->get('inventory')->row_array();

						// Query any existing inventory to copy supplier_id, sku, categories, pricing, duty details etc.
						$existing_any_inv = $this->db->where('product_id', $prod_id)
													 ->where('batch_no', $b_no)
													 ->get('inventory')->row_array();

						$product_info = $this->db->where('id', $prod_id)->get('raw_products')->row_array();
						$categories = $product_info ? (isset($product_info['categories']) ? $product_info['categories'] : (isset($product_info['category_id']) ? $product_info['category_id'] : '')) : '';
						$sku = $product_info ? $product_info['sku'] : '';

						if ($inv_prod) {
							$new_qty = (int)$inv_prod['quantity'] + $tot_qty;
							$new_white = (int)$inv_prod['official_qty'] + $w_qty;
							$new_black = (int)$inv_prod['black_qty'] + $b_qty;

							$prod_update = array(
								'quantity' => $new_qty,
								'official_qty' => $new_white,
								'black_qty' => $new_black,
								'total_amt' => $inv_prod['total_amt'] + $final_row_total,
								'gst_amt' => $inv_prod['gst_amt'] + $gst_amt
							);
							$this->db->where('id', $inv_prod['id'])->update('inventory', $prod_update);
							$inventory_id = $inv_prod['id'];
						} else {
							$new_inv_data = array(
								'supplier_id' => $existing_any_inv ? $existing_any_inv['supplier_id'] : 0,
								'company_id' => $company_id,
								'warehouse_id' => 0,
								'warehouse_name' => '',
								'product_id' => $prod_id,
								'product_name' => $product_name,
								'categories' => $existing_any_inv ? $existing_any_inv['categories'] : $categories,
								'sku' => $existing_any_inv ? $existing_any_inv['sku'] : $sku,
								'item_code' => $item_code,
								'quantity' => $tot_qty,
								'official_qty' => $w_qty,
								'official_rate_rs' => $w_amt,
								'official_total_rs' => $w_total,
								'black_qty' => $b_qty,
								'batch_no' => $b_no,
								'total_amt' => $final_row_total,
								'gst_amt' => $gst_amt,
								'actual_rmb' => $existing_any_inv ? $existing_any_inv['actual_rmb'] : 0.00,
								'total_rmb' => $existing_any_inv ? $existing_any_inv['actual_rmb'] * $tot_qty : 0.00,
								'actual_usd' => $existing_any_inv ? $existing_any_inv['actual_usd'] : 0.00,
								'actual_inr' => $existing_any_inv ? $existing_any_inv['actual_inr'] : 0.00,
								'duty_percent' => $existing_any_inv ? $existing_any_inv['duty_percent'] : 0.00,
								'duty_amt' => $existing_any_inv ? $existing_any_inv['duty_amt'] : 0.00,
								'duty_surcharge' => $existing_any_inv ? $existing_any_inv['duty_surcharge'] : 0.00,
								'taxable_value' => $existing_any_inv ? $existing_any_inv['taxable_value'] : 0.00
							);
							$this->db->insert('inventory', $new_inv_data);
							$inventory_id = $this->db->insert_id();
						}

						// 2. Insert inventory history
						$stocks_data = array(
							'supplier_id' => $existing_any_inv ? $existing_any_inv['supplier_id'] : 0,
							'company_id' => $company_id,
							'parent_id' => $inventory_id,
							'warehouse_id' => 0,
							'warehouse_name' => '',
							'product_id' => $prod_id,
							'product_name' => $product_name,
							'categories' => $existing_any_inv ? $existing_any_inv['categories'] : $categories,
							'sku' => $existing_any_inv ? $existing_any_inv['sku'] : $sku,
							'item_code' => $item_code,
							'order_id' => $parent_id,
							'status' => 'return',
							'quantity' => $tot_qty,
							'official_qty' => $w_qty,
							'official_rate_rs' => $w_amt,
							'official_total_rs' => $w_total,
							'black_qty' => $b_qty,
							'black_rate_rs' => $b_amt,
							'black_total_rs' => $b_total,
							'total_amt' => $final_row_total,
							'gst_amt' => $gst_amt,
							'received_date' => date('Y-m-d', strtotime($date)),
							'batch_no' => $b_no,
							'added_date' => date("Y-m-d H:i:s"),
							'added_by_id' => $this->session->userdata('super_user_id'),
							'added_by_name' => $this->session->userdata('super_name'),
							'actual_rmb' => $existing_any_inv ? $existing_any_inv['actual_rmb'] : 0.00,
							'total_rmb' => $existing_any_inv ? $existing_any_inv['actual_rmb'] * $tot_qty : 0.00,
							'actual_usd' => $existing_any_inv ? $existing_any_inv['actual_usd'] : 0.00,
							'actual_inr' => $existing_any_inv ? $existing_any_inv['actual_inr'] : 0.00,
							'duty_percent' => $existing_any_inv ? $existing_any_inv['duty_percent'] : 0.00,
							'duty_amt' => $existing_any_inv ? $existing_any_inv['duty_amt'] : 0.00,
							'duty_surcharge' => $existing_any_inv ? $existing_any_inv['duty_surcharge'] : 0.00,
							'taxable_value' => $existing_any_inv ? $existing_any_inv['taxable_value'] : 0.00
						);
						$this->db->insert('inventory_history', $stocks_data);

						// 3. Update the return/received qty of sales order and invoice order products
						if ($type === 'unofficial' && $batch_prod_id > 0) {
							// update return_black_qty in sales_order_product_batch
							$this->db->set('return_black_qty', 'return_black_qty + ' . $b_qty, FALSE);
							$this->db->where('id', $batch_prod_id);
							$this->db->update('sales_order_product_batch');
						} else if ($type === 'official' && $batch_prod_id > 0) {
							// update return_qty in invoice_order_products
							$this->db->set('return_qty', 'return_qty + ' . $tot_qty, FALSE);
							$this->db->where('id', $batch_prod_id);
							$this->db->update('invoice_order_products');

							// get batch_id from invoice_order_products
							$invoice_prod = $this->db->where('id', $batch_prod_id)->get('invoice_order_products')->row_array();
							$batch_id = $invoice_prod ? (int)$invoice_prod['batch_id'] : 0;
							
							if ($batch_id > 0) {
								// update return_qty in sales_order_product_batch based on batch_id
								$this->db->set('return_qty', 'return_qty + ' . $tot_qty, FALSE);
								$this->db->where('id', $batch_id);
								$this->db->update('sales_order_product_batch');
							}
						}
					}
				}
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

		$query = $this->db->query("SELECT id,warehouse_id,warehouse_name,company_id FROM goods_return WHERE id='$id' limit 1");
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$warehouse_id = $row['warehouse_id'];
			$warehouse_name = $row['warehouse_name'];
			$company_id = $row['company_id'];
			$parent_id = $id;

			$query_1 = $this->db->query("SELECT id,product_id,item_code,product_name,quantity,batch_no,white_qty,black_qty,white_amt,black_amt FROM goods_return_product WHERE parent_id='$id' order by id asc");
			foreach ($query_1->result_array() as $item_1) {
				$prod_id = $item_1['product_id'];
				$batch_no = $item_1['batch_no'];
				$product_name = $item_1['product_name'];
				$item_code = $item_1['item_code'];
				$quantity = (int)$item_1['quantity'];
				$white_qty = (int)$item_1['white_qty'];
				$black_qty = (int)$item_1['black_qty'];
				$white_amt = (float)$item_1['white_amt'];
				$black_amt = (float)$item_1['black_amt'];

				// Stock Out (reverting return)
				$this->db->where('warehouse_id', $warehouse_id);
				$this->db->where('product_id', $prod_id);
				$this->db->where('batch_no', $batch_no);
				if (!empty($company_id)) {
					$this->db->where('company_id', $company_id);
				}
				$query_check = $this->db->get('inventory');
				
				if ($query_check->num_rows() > 0) {
					$gstock       = $query_check->row_array();
					$stock_id     = $gstock['id'];
					$new_quantity = $gstock['quantity'] - $quantity;
					$new_white = $gstock['official_qty'] - $white_qty;
					$new_black = $gstock['black_qty'] - $black_qty;

					$prod = array(
						'quantity' => $new_quantity,
						'official_qty' => $new_white,
						'black_qty' => $new_black
					);
					$this->db->where('id', $stock_id)->update('inventory', $prod);

					$stocks_data  = array();
					$stocks_data['order_id'] = $parent_id;
					$stocks_data['parent_id'] = $stock_id;
					$stocks_data['warehouse_name'] = $warehouse_name;
					$stocks_data['warehouse_id'] = $warehouse_id;
					$stocks_data['product_id'] = $prod_id;
					$stocks_data['product_name'] = $product_name;
					$stocks_data['categories'] = $gstock['categories'];
					$stocks_data['sku'] = $gstock['sku'];
					$stocks_data['item_code'] = $item_code;
					$stocks_data['supplier_id'] = $gstock['supplier_id'];
					$stocks_data['company_id'] = $company_id;
					$stocks_data['quantity']    = $quantity;
					$stocks_data['official_qty'] = $white_qty;
					$stocks_data['official_rate_rs'] = $white_amt;
					$stocks_data['official_total_rs'] = $white_qty * $white_amt;
					$stocks_data['black_qty'] = $black_qty;
					$stocks_data['black_rate_rs'] = $black_amt;
					$stocks_data['black_total_rs'] = $black_qty * $black_amt;
					$stocks_data['total_amt'] = ($white_qty * $white_amt) + ($black_qty * $black_amt);
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

		$this->session->set_flashdata('flash_message', "Goods Return Delete Successfully !!");
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
				$state_name = $this->common_model->get_state_name($state_id);
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->get_city_name($city_id);
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
			$data['c_code']       = clean_and_escape($this->input->post('c_code'));
			$data['email']        = clean_and_escape($this->input->post('email'));
			$data['tel_no']       = clean_and_escape($this->input->post('tel_no'));
			$data['t_code']       = clean_and_escape($this->input->post('t_code'));
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
				$state_name = $this->common_model->get_state_name($state_id);
			} else {
				$state_name = '';
			}
			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
				$city_name = $this->common_model->get_city_name($city_id);
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
			$data['c_code']       = clean_and_escape($this->input->post('c_code'));
			$data['email']        = clean_and_escape($this->input->post('email'));
			$data['tel_no']       = clean_and_escape($this->input->post('tel_no'));
			$data['t_code']       = clean_and_escape($this->input->post('t_code'));
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
			$country_id = $this->input->post('country_id');
			if ($country_id != '') {
					$country_name = $this->common_model->selectByidParam($country_id, 'countries', 'name');
			} else {
					$country_name = '';
			}

			$state_id = $this->input->post('state_id');
			if ($state_id != '') {
					$state_name = $this->common_model->get_state_name($state_id);
			} else {
					$state_name = '';
			}

			$city_id = $this->input->post('city_id');
			if ($city_id != '') {
					$city_name = $this->common_model->get_city_name($city_id);
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
			$outstanding           = trim($this->input->post('outstanding'));
			$data['outstanding']   = ($outstanding != '') ? clean_and_escape($outstanding) : 0.00;

			$user_id               = $this->session->userdata('super_user_id');
			$user_name             = $this->session->userdata('super_name');

			$data['country_id']    = $country_id;
			$data['country_name']  = $country_name;
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

			$country_id = $this->input->post('country_id');
			if ($country_id != '') {
					$country_name = $this->common_model->selectByidParam($country_id, 'countries', 'name');
			} else {
					$country_name = '';
			}

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
			$outstanding          = trim($this->input->post('outstanding'));
			$data['outstanding']  = ($outstanding != '') ? clean_and_escape($outstanding) : 0.00;
			$data['country_id']   = $country_id;
			$data['country_name'] = $country_name;
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
				$ledger_url = base_url() . 'inventory/vendor-ledger/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="' . $ledger_url . '" data-toggle="tooltip" data-bs-placement="top" title="Ledger"><button type="button" class="btn mr-1 mb-1 icon-btn-view"><i class="fa fa-list" aria-hidden="true"></i></button></a>
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

	// Vendor Payments Starts
	public function add_vendor_payments()
	{
		$resultpost = array(
				"status"  => 200,
				"message" => "Vendor Payment added successfully",
				"url"     => $this->session->userdata('previous_url'),
		);

		$company_id = (int) $this->session->userdata('company_id');
		$added_by   = (int) $this->session->userdata('super_user_id');

		$supplier_id  = (int) $this->input->post('vendor_id'); // Using vendor_id from form
		$invoice_no   = clean_and_escape($this->input->post('invoice_no'));
		$usd          = (float) $this->input->post('usd');
		$rmb          = (float) $this->input->post('rmb');
		$inr          = (float) $this->input->post('inr');

		$payment_type = clean_and_escape($this->input->post('payment_type')); // official/unofficial
		$bank_account = (int) $this->input->post('bank_account');

		$payment_date = $this->input->post('payment_date');
		$payment_date = $payment_date ? $payment_date : null;

		$narration = clean_and_escape($this->input->post('narration'));

		// Validate vendor (using my_companies table)
		$vendor = $this->db->get_where('my_companies', array('id' => $supplier_id))->row_array();
		if (empty($vendor)) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "Invalid vendor selected.";
				return simple_json_output($resultpost);
		}
		$supplier_name = $vendor['name'];

		// Bank account logic
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
				$bank_account = 0;
				$bank_accounts_name = null;
		}

		$data = array(
				'company_id'         => $company_id,
				'vendor_id'          => $supplier_id, // storing vendor id in vendor_id
				'vendor_name'        => $supplier_name, // storing vendor name in vendor_name
				'invoice_no'         => $invoice_no,
				'usd'                => number_format($usd, 5, '.', ''),
				'rmb'                => number_format($rmb, 5, '.', ''),
				'inr'                => number_format($inr, 5, '.', ''),
				'payment_type'       => $payment_type,
				'bank_account'       => $bank_account,
				'bank_account_name'  => $bank_accounts_name,
				'payment_date'       => $payment_date,
				'narration'          => $narration,
				'is_delete'          => 0,
				'added_by'           => $added_by,
		);

		$this->db->trans_begin();
		$this->db->insert('vendor_payments', $data);

		if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost['status']  = 400;
				$resultpost['message'] = "Failed to add vendor payment. Please try again.";
				return simple_json_output($resultpost);
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', "Vendor Payment added successfully");
		return simple_json_output($resultpost);
	}

	public function edit_vendor_payments($param2)
	{
		$resultpost = array(
				"status"  => 200,
				"message" => "Vendor Payment updated successfully",
				"url"     => $this->session->userdata('previous_url'),
		);

		$id = (int) $param2;
		$existing = $this->db->get_where('vendor_payments', array('id' => $id, 'is_delete' => 0))->row_array();
		if (empty($existing)) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "Vendor Payment not found.";
				return simple_json_output($resultpost);
		}

		$supplier_id  = (int) $this->input->post('vendor_id');
		$invoice_no   = clean_and_escape($this->input->post('invoice_no'));
		$usd          = (float) $this->input->post('usd');
		$rmb          = (float) $this->input->post('rmb');
		$inr          = (float) $this->input->post('inr');

		$payment_type = clean_and_escape($this->input->post('payment_type'));
		$bank_account = (int) $this->input->post('bank_account');

		$payment_date = $this->input->post('payment_date');
		$payment_date = $payment_date ? $payment_date : null;

		$narration = clean_and_escape($this->input->post('narration'));

		$vendor = $this->db->get_where('my_companies', array('id' => $supplier_id))->row_array();
		if (empty($vendor)) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "Invalid vendor selected.";
				return simple_json_output($resultpost);
		}
		$supplier_name = $vendor['name'];

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
				$bank_account = 0;
				$bank_accounts_name = null;
		}

		$data = array(
				'vendor_id'          => $supplier_id,
				'vendor_name'        => $supplier_name,
				'invoice_no'         => $invoice_no,
				'usd'                => number_format($usd, 5, '.', ''),
				'rmb'                => number_format($rmb, 5, '.', ''),
				'inr'                => number_format($inr, 5, '.', ''),
				'payment_type'       => $payment_type,
				'bank_account'       => $bank_account,
				'bank_account_name'  => $bank_accounts_name,
				'payment_date'       => $payment_date,
				'narration'          => $narration,
		);

		$this->db->trans_begin();
		$this->db->where('id', $id);
		$this->db->update('vendor_payments', $data);

		if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost['status']  = 400;
				$resultpost['message'] = "Failed to update vendor payment. Please try again.";
				return simple_json_output($resultpost);
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', "Vendor Payment updated successfully");
		return simple_json_output($resultpost);
	}

	public function get_vendor_payments()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
			$keyword = $filter_data['keywords'];
			$keyword_filter = " AND (vendor_name LIKE '%" . $keyword . "%' OR invoice_no LIKE '%" . $keyword . "%')";
		}

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$date_range = explode(' - ', $_REQUEST['date_range']);
			$from = date('Y-m-d', strtotime($date_range['0']));
			$to = date('Y-m-d', strtotime($date_range['1']));

			$keyword_filter .= " AND (DATE(payment_date) >= '" . $from . "' AND DATE(payment_date) <= '" . $to . "')";
		}

		$company_id = $this->session->userdata('company_id');
		$total_count = $this->db->query("SELECT id FROM vendor_payments WHERE is_delete = '0' AND company_id='" . $company_id . "'" . $keyword_filter)->num_rows();
		$query = $this->db->query("SELECT id, vendor_name, payment_type, invoice_no, usd, rmb, inr, payment_date FROM vendor_payments WHERE is_delete = '0' AND company_id='" . $company_id . "'" . $keyword_filter . " ORDER BY id DESC LIMIT $start, $length");
		
		if (!empty($query)) {
			$sr_no = $start;
			foreach ($query->result_array() as $item) {

				$actions = '';
				$actions .= '<a href="' . base_url() . 'inventory/vendor-payments/edit/'. $item['id'] . '" data-toggle="tooltip" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a> ';
				$actions .= '<a href="#" onclick="confirm_modal(\'' . base_url() . 'inventory/vendor-payments/delete/'. $item['id'] . '\',\'Are you sure want to delete!\')" data-toggle="tooltip" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$data[] = array(
					"sr_no"         	=> ++$sr_no,
					"type"						=> get_phrase($item['payment_type']),
					"vendor_name"		=> $item['vendor_name'],
					"usd"           	=> number_format((float)$item['usd'], 2),
					"rmb"           	=> number_format((float)$item['rmb'], 2),
					"inr"           	=> number_format((float)$item['inr'], 2),
					"invoice_no"			=> ($item['invoice_no']) ? $item['invoice_no'] : '-',
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

	public function delete_vendor_payments($id)
	{
		$resultpost = array(
				"status"  => 200,
				"message" => "Vendor Payment deleted successfully",
				"url"     => $this->agent->referrer(),
		);

		$this->db->trans_begin();
		$this->db->where('id', $id);
		$this->db->update('vendor_payments', array('is_delete' => 1));

		if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$resultpost['status']  = 400;
				$resultpost['message'] = "Failed to delete vendor payment. Please try again.";
				return simple_json_output($resultpost);
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', "Vendor Payment deleted successfully");
		return simple_json_output($resultpost);
	}

	public function get_vendor_ledger($vendor_id, $type = null)
	{
		$where = "vendor_id = '$vendor_id' AND is_delete = '0'";
		if ($type) {
			$where .= " AND type = " . $this->db->escape($type);
		}
		$query = $this->db->query("SELECT 
										id, batch_no as voucher_no, expense_date as date, grand_total, usd, rmb, added_by_id, narration, type
									FROM po_expense
									WHERE $where
									ORDER BY expense_date DESC, id DESC");
		
		$result = $query->result_array();
		foreach ($result as $key => $row) {
			$user = $this->db->get_where('sys_users', array('id' => $row['added_by_id']))->row_array();
			$result[$key]['added_by_name'] = $user ? $user['first_name'] : '-';
		}
		return $result;
	}

	public function get_vendor_payments_by_id($vendor_id, $type = null)
	{
		$where = "vendor_id = '$vendor_id' AND is_delete = '0'";
		if ($type) {
			$where .= " AND payment_type = " . $this->db->escape($type);
		}
		$query = $this->db->query("SELECT 
										id, invoice_no as inv_no, payment_date as date, usd, rmb, inr, added_by, narration, payment_type
									FROM vendor_payments
									WHERE $where
									ORDER BY payment_date DESC, id DESC");
		
		$result = $query->result_array();
		foreach ($result as $key => $row) {
			$user = $this->db->get_where('sys_users', array('id' => $row['added_by']))->row_array();
			$result[$key]['added_by_name'] = $user ? $user['first_name'] : '-';
		}
		return $result;
	}
	// Vendor Payments Ends

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
			$supplier_ids              = $this->input->post('supplier_id');
			$data['supplier_id']       = !empty($supplier_ids) ? implode(',', $supplier_ids) : null;
			$data['type']              = clean_and_escape($this->input->post('type')); // official/unofficial
			$data['expense_type']      = (int) $this->input->post('expense_type');
			$data['gst_type']          = clean_and_escape($this->input->post('gst_type')); // '', igst, cgst_sgst
			$data['purchase_no']       = clean_and_escape($this->input->post('purchase_no'));
			$data['purchase_date']     = $this->input->post('purchase_date') ? $this->input->post('purchase_date') : null;
			$data['usd']               = number_format((float) $this->input->post('usd'), 5, '.', '');
			$data['rmb']               = number_format((float) $this->input->post('rmb'), 5, '.', '');

			$data['narration']   = clean_and_escape($this->input->post('narration'));

			$data['expense_date'] = $this->input->post('expense_date') ? $this->input->post('expense_date') : null;

			$data['added_by_id'] = (int) $this->session->userdata('super_user_id');

			// Totals are already coming from frontend (readonly inputs)
			$sub_total   = (float) $this->input->post('sub_total');
			$gst_total   = (float) $this->input->post('gst_total');
			$grand_total = (float) $this->input->post('grand_total');

			$data['sub_total']   = number_format($sub_total, 5, '.', '');
			$data['gst_total']   = number_format($gst_total, 5, '.', '');
			$data['grand_total'] = number_format($grand_total, 5, '.', '');

			// Detail arrays
			$charges_ids   = (array) $this->input->post('charges_id');
			$expense_names = (array) $this->input->post('expense_name');
			$usd_amts      = (array) $this->input->post('usd_amt');
			$rmb_amts      = (array) $this->input->post('rmb_amt');
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

					$usdVal = isset($usd_amts[$i]) ? (float) $usd_amts[$i] : 0;
					$rmbVal = isset($rmb_amts[$i]) ? (float) $rmb_amts[$i] : 0;
					$amt    = isset($amounts[$i]) ? (float) $amounts[$i] : 0;
					$gstP   = isset($gsts[$i]) ? (float) $gsts[$i] : 0;
					$gstAmt = isset($gst_amts[$i]) ? (float) $gst_amts[$i] : 0;
					$charges_id = isset($charges_ids[$i]) && $charges_ids[$i] !== '' ? (int) $charges_ids[$i] : null;

					$details[] = array(
							'parent_id'    => $parent_id,
							'charges_id'   => $charges_id,
							'expense_name' => clean_and_escape($name),
							'usd'          => number_format($usdVal, 5, '.', ''),
							'rmb'          => number_format($rmbVal, 5, '.', ''),
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

			$this->get_batch_detail_data($this->input->post('batch_no'));

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

		if ($this->is_purchase_order_locked_by_batch($existing['batch_no'], $session_company_id)) {
				$resultpost['status']  = 400;
				$resultpost['message'] = "This PO is locked and cannot be edited.";
				return simple_json_output($resultpost);
		}

		// ---- Build child rows FIRST (so we don't delete old rows if new is invalid) ----
		$charges_ids   = (array) $this->input->post('charges_id');
		$expense_names = (array) $this->input->post('expense_name');
		$usd_amts      = (array) $this->input->post('usd_amt');
		$rmb_amts      = (array) $this->input->post('rmb_amt');
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

				$usdVal = isset($usd_amts[$i]) ? (float) $usd_amts[$i] : 0;
				$rmbVal = isset($rmb_amts[$i]) ? (float) $rmb_amts[$i] : 0;
				$amt    = isset($amounts[$i]) ? (float) $amounts[$i] : 0;
				$gstP   = isset($gsts[$i]) ? (float) $gsts[$i] : 0;
				$gstAmt = isset($gst_amts[$i]) ? (float) $gst_amts[$i] : 0;
				$charges_id = isset($charges_ids[$i]) && $charges_ids[$i] !== '' ? (int) $charges_ids[$i] : null;

				$details[] = array(
						'parent_id'    => $id,
						'charges_id'   => $charges_id,
						'expense_name' => clean_and_escape($name),
						'usd'          => number_format($usdVal, 5, '.', ''),
						'rmb'          => number_format($rmbVal, 5, '.', ''),
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
		$supplier_ids              = $this->input->post('supplier_id');
		$data['supplier_id']       = !empty($supplier_ids) ? implode(',', $supplier_ids) : null;
		$data['type']              = clean_and_escape($this->input->post('type'));          // official/unofficial
		$data['expense_type']      = (int) $this->input->post('expense_type');
		$data['gst_type']          = clean_and_escape($this->input->post('gst_type'));      // '', igst, cgst_sgst
		$data['purchase_no']       = clean_and_escape($this->input->post('purchase_no'));
		$data['purchase_date']     = $this->input->post('purchase_date') ? $this->input->post('purchase_date') : null;
		$data['usd']               = number_format((float) $this->input->post('usd'), 5, '.', '');
		$data['rmb']               = number_format((float) $this->input->post('rmb'), 5, '.', '');
		$data['narration'] = clean_and_escape($this->input->post('narration'));

		$data['expense_date'] = $this->input->post('expense_date') ? $this->input->post('expense_date') : null;

		// Totals from frontend (readonly)
		$sub_total   = (float) $this->input->post('sub_total');
		$gst_total   = (float) $this->input->post('gst_total');
		$grand_total = (float) $this->input->post('grand_total');

		$data['sub_total']   = number_format($sub_total, 5, '.', '');
		$data['gst_total']   = number_format($gst_total, 5, '.', '');
		$data['grand_total'] = number_format($grand_total, 5, '.', '');

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

		$this->get_batch_detail_data($this->input->post('batch_no'));

		$this->db->trans_commit();

		$this->session->set_flashdata('flash_message', "Expense updated successfully");
		return simple_json_output($resultpost);
	}

	public function delete_po_expense($id) {
		$resultpost = array("status" => 200, "message" => "Expense deleted successfully", "url" => site_url('inventory/po-expense'));

		$existing = $this->common_model->getRowById('po_expense', '*', ['is_delete' => '0', 'id' => $id]);
		if (empty($existing)) {
			$resultpost['status']  = 400;
			$resultpost['message'] = "Expense not found.";
			return simple_json_output($resultpost);
		}

		if ($this->is_purchase_order_locked_by_batch($existing['batch_no'])) {
			$resultpost['status']  = 400;
			$resultpost['message'] = "This PO is locked and cannot be deleted.";
			return simple_json_output($resultpost);
		}

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

	public function is_purchase_order_locked_by_batch($batch_no, $company_id = null)
	{
		if (empty($batch_no)) {
			return false;
		}

		if ($company_id === null) {
			$company_id = $this->session->userdata('company_id');
		}

		$po = $this->db->get_where('purchase_order', [
			'voucher_no' => $batch_no,
			'company_id' => $company_id,
			'is_deleted' => 0
		])->row_array();

		return !empty($po['is_locked']);
	}

	public function get_po_expense()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
		$length = isset($_REQUEST['length']) ? intval($_REQUEST['length']) : 10;

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		// if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
		// 	$keyword = $filter_data['keywords'];
		// 	$keyword_filter = " AND (batch_no LIKE '%" . $keyword . "%')";
		// }

		if (isset($_REQUEST['keywords']) && $_REQUEST['keywords'] != "") {
			$keyword = $_REQUEST['keywords'];
			$keyword_filter = " AND (batch_no LIKE '%" . $keyword . "%')";
		}

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$date_range = explode(' - ', $_REQUEST['date_range']);
			$from = date('Y-m-d', strtotime($date_range['0']));
			$to = date('Y-m-d', strtotime($date_range['1']));

			$keyword_filter .= " AND (DATE(expense_date) >= '" . $from . "' AND DATE(expense_date) <= '" . $to . "')";
		}

		$company_id = $this->session->userdata('company_id');

		$total_count = $this->db->query("SELECT id FROM po_expense WHERE company_id='" . $company_id . "' AND is_delete = '0' " . $keyword_filter)->num_rows();
		
		$is_filtered = (isset($_REQUEST['keywords']) && $_REQUEST['keywords'] != "") || (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "");
		$limit_clause = $is_filtered ? "" : " LIMIT $start, $length";

		$query = $this->db->query("SELECT e.id, e.batch_no, e.type, e.expense_type, e.vendor_id, e.sub_total, e.gst_total, e.grand_total, e.expense_date, po.is_locked FROM po_expense e LEFT JOIN purchase_order po ON po.voucher_no = e.batch_no AND po.company_id = e.company_id AND po.is_deleted = '0' WHERE e.company_id='" . $company_id . "' AND e.is_delete = '0' " . $keyword_filter . " ORDER BY e.id DESC" . $limit_clause);

		if (!empty($query)) {
			$sr_no = $start;
			$total_expense = 0;
			$total_sub_total = 0;
			$total_gst_total = 0;
			$has_rows = false;
			foreach ($query->result_array() as $item) {
				$has_rows = true;
				$company_name = $this->common_model->selectByidsParam(['id' => $item['vendor_id']], 'my_companies', 'name');
				$expense_type = $this->common_model->selectByidsParam(['id' => $item['expense_type']], 'expense_type', 'name');

				$total_expense += (float)$item['grand_total'];
				$total_sub_total += (float)$item['sub_total'];
				$total_gst_total += (float)$item['gst_total'];

				$actions = '';
				if (empty($item['is_locked'])) {
					$actions .= '<a href="' . base_url() . 'inventory/po-expense/edit/'. $item['id'] . '" data-toggle="tooltip" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a> ';
					$actions .= '<a href="#" onclick="confirm_modal(\'' . base_url() . 'inventory/po_expense/delete/'. $item['id'] . '\',\'Are you sure want to delete!\')" data-toggle="tooltip" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';
				}

				$data[] = array(
					"sr_no"         	=> ++$sr_no,
					"batch_no"      	=> $item['batch_no'],
					"company_name"  	=> ($company_name) ? $company_name : '-',
					"sub_total"       => number_format($item['sub_total'], 2),
					"gst_total"       => number_format($item['gst_total'], 2),
					"amount"        	=> number_format($item['grand_total'], 2),
					"type"						=> get_phrase($item['type']),
					"expense_type"		=> ($expense_type) ? $expense_type : '-',
					"date"          	=> $item['expense_date'] ? date('d M, Y', strtotime($item['expense_date'])) : '-',
					"action"					=> $actions,
				);
			}

			if ($has_rows) {
				$data[] = array(
					"sr_no"         	=> "<b>Total</b>",
					"batch_no"      	=> "",
					"company_name"  	=> "",
					"sub_total"       => "<b>" . number_format($total_sub_total, 2) . "</b>",
					"gst_total"       => "<b>" . number_format($total_gst_total, 2) . "</b>",
					"amount"        	=> "<b>" . number_format($total_expense, 2) . "</b>",
					"type"						=> "",
					"expense_type"		=> "",
					"date"          	=> "",
					"action"					=> "",
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

	public function get_batch_detail_data($batch_no)
	{
		$po = $this->db->query("
			SELECT id
			FROM purchase_order
			WHERE voucher_no = ?
				AND is_deleted = 0
				AND method = 'import'
			LIMIT 1
		", array($batch_no))->row_array();

		$rows = $this->db->query("
			SELECT
				pp.id,
				pp.product_id,
				pp.supplier_id,
				pp.official_ci_qty,
				pp.actual_qty,
				pp.cbm,
				pp.actual_inr,
				pp.official_total_rs,
				pp.duty_amt,
				pp.duty_surcharge
			FROM purchase_in_product pp
			WHERE pp.parent_id = ?
				AND pp.is_deleted = 0
			ORDER BY pp.supplier_id ASC, pp.id ASC
		", array($po['id']))->result_array();

		$suppliers = array();
		$supplier_actual_cbm = array();
		$supplier_off_cbm = array();

		foreach ($rows as $r) {
			$supplier_id = (int)($r['supplier_id'] ?? 0);
			if (!isset($suppliers[$supplier_id])) {
				$suppliers[$supplier_id] = array(
					'supplier_id' => $supplier_id,
					'products' => array()
				);
			}

			$official_qty = (float)($r['official_ci_qty'] ?? 0);
			$act_qty = (float)($r['actual_qty'] ?? 0);
			$cbm_per_pc = (float)($r['cbm'] ?? 0);
			$off_cbm_total = $official_qty * $cbm_per_pc;
			$actual_cbm_total = $act_qty * $cbm_per_pc;

			$cost_without_expense_rs = (float)($r['actual_inr'] ?? 0);
			$total_rs_without_expense = $cost_without_expense_rs * $act_qty;
			$total_off_rs = (float)($r['official_total_rs'] ?? 0);
			$off_duty_amt = (float)($r['duty_amt'] ?? 0);
			$off_surcharge = (float)($r['duty_surcharge'] ?? 0);

			$line = array(
				'id' => (int)($r['id'] ?? 0),
				'product_id' => (int)($r['product_id'] ?? 0),
				'official_qty' => $official_qty,
				'act_qty' => $act_qty,
				'off_cbm_total' => $off_cbm_total,
				'actual_cbm_total' => $actual_cbm_total,
				'total_rs_without_expense' => $total_rs_without_expense,
				'total_off_rs' => $total_off_rs,
				'off_duty_amt' => $off_duty_amt,
				'off_surcharge' => $off_surcharge
			);

			$suppliers[$supplier_id]['products'][] = $line;

			if (!isset($supplier_actual_cbm[$supplier_id])) {
				$supplier_actual_cbm[$supplier_id] = 0;
				$supplier_off_cbm[$supplier_id] = 0;
			}
			$supplier_actual_cbm[$supplier_id] += $actual_cbm_total;
			$supplier_off_cbm[$supplier_id] += $off_cbm_total;
		}

		$all_batch_expenses = $this->db->query("
			SELECT supplier_id, sub_total, type 
			FROM po_expense 
			WHERE batch_no = ? AND is_delete = 0
		", array($batch_no));

		$expenses = ($all_batch_expenses->num_rows() > 0) ? $all_batch_expenses->result_array() : [];

		foreach ($suppliers as &$supplier) {
			// Actual Expense
			$act_exp = [];
			foreach ($expenses as $exp) {
				$exp_suppliers = explode(',', $exp["supplier_id"]);

				$total_act_cbm = 0;
				if (in_array($supplier['supplier_id'], $exp_suppliers)) {
					foreach ($exp_suppliers as $exp_sup) {
						if (isset($supplier_actual_cbm[$exp_sup])) {
							$total_act_cbm += $supplier_actual_cbm[$exp_sup];
						}
					}
				}

				if ($total_act_cbm > 0) {
					$act_exp[] = $exp['sub_total'] / $total_act_cbm;
				}
			}

				// Official Expense
			$off_exp = [];
			foreach ($expenses as $exp) {
				if ($exp['type'] == 'official') {
					$exp_suppliers = explode(',', $exp["supplier_id"]);

					$total_off_cbm = 0;
					if (in_array($supplier['supplier_id'], $exp_suppliers)) {
						foreach ($exp_suppliers as $exp_sup) {
							if (isset($supplier_off_cbm[$exp_sup])) {
								$total_off_cbm += $supplier_off_cbm[$exp_sup];
							}
						}
					}

					if ($total_off_cbm > 0) {
						$off_exp[] = $exp['sub_total'] / $total_off_cbm;
					}
				}
			}

			foreach ($supplier['products'] as &$product) {
				$p_expense = (count($act_exp) > 0) ? (array_sum($act_exp) * $product['actual_cbm_total']) : 0;
				$p_total_expense = $p_expense + $product['total_rs_without_expense'] + $product['off_duty_amt'] + $product['off_surcharge'];
				$p_off_expense = (count($off_exp) > 0) ? (array_sum($off_exp) * $product['off_cbm_total']) : 0;
				$p_off_total_expense = $p_off_expense + $product['total_off_rs'] + $product['off_duty_amt'] + $product['off_surcharge'];
				$p_off_per_pc = ($product['official_qty'] > 0) ? $p_off_total_expense / $product['official_qty'] : 0;
				$p_cost_without_exp = ($product['act_qty'] > 0) ? $p_total_expense / $product['act_qty'] : 0;

				$product['expense'] = $p_expense;
				$product['total_expense'] = $p_total_expense;
				$product['official_expense'] = $p_off_expense;
				$product['total_official_expense'] = $p_off_total_expense;
				$product['official_exp_per_pc'] = $p_off_per_pc;
				$product['actual_cost_with_exp'] = $p_cost_without_exp;
			}
			unset($product);
		}
		unset($supplier);

		// Persist expense details to DB and inventory
		foreach ($suppliers as $supplier) {
			foreach ($supplier['products'] as $product) {
				$update_data = [
					'expense' => $product['expense'],
					'total_expense' => $product['total_expense'],
					'official_expense' => $product['official_expense'],
					'total_official_expense' => $product['total_official_expense'],
					'official_exp_per_pc' => $product['official_exp_per_pc'],
					'actual_cost_with_exp' => $product['actual_cost_with_exp'],
				];

				$this->db->where('id', $product['id'])->update('purchase_in_product', $update_data);

				$this->db->where([
					'product_id' => $product['product_id'],
					'supplier_id' => $supplier['supplier_id'],
					'batch_no' => $batch_no
				])->update('inventory', $update_data);
			}
		}
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
		$query = $this->db->query("SELECT id, first_name, last_name, email, phone, staff_access, status FROM sys_users WHERE (id<>'') and is_deleted='0' $keyword_filter ORDER BY id desc LIMIT $start,$length");
		//echo $this->db->last_query();
		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$edit_url = base_url() . 'inventory/staff/edit/' . $id;
				$delete_url = "confirm_modal('" . base_url() . "inventory/manage_staff/delete/" . $id . "','Are you sure want to delete!')";
				$pass_url = base_url() . 'inventory/staff_form/change_password/' . $id;
				$view_url = "showAjaxModal('" . base_url() . "modal/popup/modal_view_staff/" . $id . "', 'Staff Details')";
				$action = '';
				$action .= '<a href="#" onclick="' . $view_url . '" data-toggle="tooltip" data-bs-placement="top" title="View"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
				<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
				<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>
				<a href="' . $pass_url . '" data-toggle="tooltip" data-bs-placement="top" title="Change Password"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
				';

				$staff_access_id = $item['staff_access'];
				$staff_type_name = '-';
				if (!empty($staff_access_id)) {
					$access = $this->db->get_where('access', ['id' => $staff_access_id])->row_array();
					$staff_type_name = $access['name'] ?? '-';
				}

				$data[] = array(
					"sr_no" => (++$start),
					"id" => $item['id'],
					"name"    => $item['first_name'] . ' ' . $item['last_name'],
					"phone"  => $item['phone'],
					"email"   => $item['email'],
					"staff_type"   => $staff_type_name,
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
			$staff_id = $this->db->insert_id();

			$customer_comm_inputs = $this->input->post('customer_comm');
			$distributer_comm_inputs = $this->input->post('distributer_comm');
			$all_commissions = $this->db->where('is_deleted', '0')->get('product_commission_slab')->result_array();
			$all_profits = $this->db->where('is_deleted', '0')->get('profit_commission_slab')->result_array();
			foreach ($all_commissions as $comm) {
				$comm_id = $comm['id'];
				foreach ($all_profits as $profit) {
					$profit_id = $profit['id'];
					$cust_val = isset($customer_comm_inputs[$comm_id][$profit_id]) && $customer_comm_inputs[$comm_id][$profit_id] !== '' ? (float)$customer_comm_inputs[$comm_id][$profit_id] : 0.00;
					$dist_val = isset($distributer_comm_inputs[$comm_id][$profit_id]) && $distributer_comm_inputs[$comm_id][$profit_id] !== '' ? (float)$distributer_comm_inputs[$comm_id][$profit_id] : 0.00;
					$this->db->insert('staff_commission', [
						'staff_id' => $staff_id,
						'commission_id' => $comm_id,
						'profit_id' => $profit_id,
						'customer_comm' => $cust_val,
						'distributer_comm' => $dist_val,
						'created_at' => date("Y-m-d H:i:s")
					]);
				}
			}

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

			$customer_comm_inputs = $this->input->post('customer_comm');
			$distributer_comm_inputs = $this->input->post('distributer_comm');

			$existing_records = $this->db->where('staff_id', $id)->get('staff_commission')->result_array();
			$existing_map = [];
			foreach ($existing_records as $rec) {
				$existing_map[$rec['commission_id'] . '_' . $rec['profit_id']] = $rec;
			}

			$all_commissions = $this->db->where('is_deleted', '0')->get('product_commission_slab')->result_array();
			$all_profits = $this->db->where('is_deleted', '0')->get('profit_commission_slab')->result_array();

			$new_keys = [];

			foreach ($all_commissions as $comm) {
				$comm_id = $comm['id'];
				foreach ($all_profits as $profit) {
					$profit_id = $profit['id'];
					$key = $comm_id . '_' . $profit_id;
					$new_keys[$key] = true;

					$cust_val = isset($customer_comm_inputs[$comm_id][$profit_id]) && $customer_comm_inputs[$comm_id][$profit_id] !== '' ? (float)$customer_comm_inputs[$comm_id][$profit_id] : 0.00;
					$dist_val = isset($distributer_comm_inputs[$comm_id][$profit_id]) && $distributer_comm_inputs[$comm_id][$profit_id] !== '' ? (float)$distributer_comm_inputs[$comm_id][$profit_id] : 0.00;

					if (isset($existing_map[$key])) {
						// Update existing entry if anything changed
						$db_cust = (float)$existing_map[$key]['customer_comm'];
						$db_dist = (float)$existing_map[$key]['distributer_comm'];
						if ($db_cust !== $cust_val || $db_dist !== $dist_val) {
							$this->db->where('id', $existing_map[$key]['id'])->update('staff_commission', [
								'customer_comm' => $cust_val,
								'distributer_comm' => $dist_val
							]);
						}
					} else {
						// Insert new entry
						$this->db->insert('staff_commission', [
							'staff_id' => $id,
							'commission_id' => $comm_id,
							'profit_id' => $profit_id,
							'customer_comm' => $cust_val,
							'distributer_comm' => $dist_val,
							'created_at' => date("Y-m-d H:i:s")
						]);
					}
				}
			}

			// Clean up removed active slabs
			foreach ($existing_map as $key => $rec) {
				if (!isset($new_keys[$key])) {
					$this->db->where('id', $rec['id'])->delete('staff_commission');
				}
			}

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
		$total_invoice = $this->db->query("SELECT invoice_no FROM loading_po_product WHERE official_ci_qty > 0 AND parent_id='$id' AND invoice_no IS NOT NULL GROUP BY invoice_no");
		if($total_invoice->num_rows() > 0) {
				$invoice_numbers = array_column($total_invoice->result_array(), 'invoice_no');
				foreach($invoice_numbers as $invoice) {
						// Fetching All product with invoice no
						$product_data = $this->db->query("SELECT * FROM loading_po_product WHERE official_ci_qty > 0 AND parent_id='$id' AND invoice_no='$invoice' ORDER BY supplier_id DESC, id ASC");
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

				// Test PDF - Uncomment the code below and change the pdf file name if needed
			  // $receipt_no = sprintf('%05d', $id);
				// $page_data['data'] = $item;
				// $html_content = $this->load->view('invoice/po/commercial', $page_data, TRUE);
				// $this->pdf->set_paper("A4", "portrait");
				// $this->pdf->set_option('isHtml5ParserEnabled', TRUE);
				// $this->pdf->load_html($html_content);
				// // echo $html_content; exit();
				// $this->pdf->render();
				// $pdfname = 'invoice_' . $receipt_no . '.pdf';
				// $this->pdf->stream($pdfname, array("Attachment" => 0));
				// exit();

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

	public function create_sales_invoice($id) {
		$export_data = [];
		
		$path = FCPATH . 'uploads/sales/';
		if (!is_dir($path)) {
				mkdir($path, 0777, true);
		}

		$this->load->library('pdf');
		
		/* ================= PACKING LIST ================= */
		// ob_clean();
		$page_data['data'] = [];
		$html = $this->load->view('invoice/sales/sales_invoice', $page_data, true);
		// echo $html;
		// exit;
		$pdf = $this->pdf->create();
		$pdf->setPaper('A4', 'portrait');
		$pdf->loadHtml($html);
		$pdf->render();

		// $pdfname = 'PL_12.pdf';
		// file_put_contents($path . $pdfname, $pdf->output());
		// return $pdf->output();
		// unset($pdf);

		// $pdfname = 'PL_' . $receipt_no . '.pdf';
		// file_put_contents($path . $pdfname, $pdf->output());
		// $path_info[] = 'uploads/invoices/' . $pdfname;
		// unset($pdf);
	}

	public function get_overall_stock()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (p.name like '%" . $keyword . "%')";
		endif;

		$total_count_query = "SELECT p.id
							  FROM raw_products p
							  JOIN inventory inv ON inv.product_id = p.id
							  WHERE 1=1 $keyword_filter
							  GROUP BY p.id
							  HAVING SUM(inv.quantity) > 0";
		$total_count = $this->db->query($total_count_query)->num_rows();

		$data_query = "SELECT 
							  p.id as product_id,
							  p.name as product_name,
							  SUM(inv.quantity) as current_qty,
							  SUM(inv.official_qty) as white_qty,
							  SUM(inv.black_qty) as black_qty
					   FROM raw_products p
					   JOIN inventory inv ON inv.product_id = p.id
					   WHERE 1=1 $keyword_filter
					   GROUP BY p.id
					   HAVING current_qty > 0 
					   ORDER BY p.name ASC LIMIT $start, $length";
		$query = $this->db->query($data_query);

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$pid = $item['product_id'];

				$batch_url = base_url() . 'inventory/my-stock-company/' . $pid;
				$action = '<a href="' . $batch_url . '" data-toggle="tooltip" data-bs-placement="top" title="View Company Stock"><button type="button" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button></a>';

				$url = base_url() . 'modal/popup_inventory/modal_company_stock/' . $pid;
				$product_name_link = '<a href="javascript:void(0);" onclick="showAjaxModal(\'' . $url . '\', \'' . htmlspecialchars($item['product_name'] ?? '', ENT_QUOTES) . '\')" class="text-primary fw-bold">' . htmlspecialchars($item['product_name'] ?? 'Unknown Product (ID: '.$pid.')') . '</a>';

				$data[] = array(
					"sr_no"       => ++$start,
					"product_name"=> $product_name_link,
					"quantity"    => $item['current_qty'],
					"white_qty"   => $item['white_qty'] ?? 0,
					"black_qty"   => $item['black_qty'] ?? 0,
					"action"      => $action
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

	public function get_overall_stock_company()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];
		$product_id = $_REQUEST['product_id'] ?? null;

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if ($product_id) {
			$keyword_filter .= " AND inv.product_id = " . $product_id;
		}

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter .= " AND (p.name like '%" . $keyword . "%' OR w.name like '%" . $keyword . "%' OR c.name like '%" . $keyword . "%')";
		endif;

		$total_count_query = "SELECT inv.product_id
							  FROM inventory inv
							  LEFT JOIN raw_products p ON p.id = inv.product_id
							  LEFT JOIN warehouse w ON w.id = inv.warehouse_id
							  LEFT JOIN company c ON c.id = inv.company_id
							  WHERE 1=1 $keyword_filter
							  GROUP BY inv.company_id, inv.warehouse_id, inv.product_id
							  HAVING SUM(inv.quantity) > 0";
		$total_count = $this->db->query($total_count_query)->num_rows();

		$data_query = "SELECT 
							  inv.company_id, inv.warehouse_id, inv.product_id,
							  MIN(inv.id) as inventory_id,
							  SUM(inv.quantity) AS current_qty,
							  SUM(inv.official_qty) AS white_qty,
							  SUM(inv.black_qty) AS black_qty,
							  p.name as product_name, w.name as warehouse_name, c.name as company_name
					   FROM inventory inv
					   LEFT JOIN raw_products p ON p.id = inv.product_id
					   LEFT JOIN warehouse w ON w.id = inv.warehouse_id
					   LEFT JOIN company c ON c.id = inv.company_id
					   WHERE 1=1 $keyword_filter
					   GROUP BY inv.company_id, inv.warehouse_id, inv.product_id 
					   HAVING current_qty > 0 
					   ORDER BY p.name ASC LIMIT $start, $length";
		$query = $this->db->query($data_query);

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$pid = $item['product_id'];
				$wid = $item['warehouse_id'];
				$cid = $item['company_id'];
				$inv_id = $item['inventory_id'];

				$batch_url = base_url() . 'inventory/my-stock-batch/' . $inv_id . '/' . $wid;
				$action = '<a href="' . $batch_url . '" data-toggle="tooltip" data-bs-placement="top" title="View Batches"><button type="button" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button></a>';
				
				$po_qty_arr = $this->get_product_po_list($pid, $cid, 'po', $wid);
				$po_qty = array_sum(array_column($po_qty_arr, 'quantity'));
				$po_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"po\")'>" . $po_qty . "</a>";
				
				$priority_qty_arr = $this->get_product_po_list($pid, $cid, 'priority', $wid);
				$priority_qty = array_sum(array_column($priority_qty_arr, 'quantity'));
				$priority_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"priority\")'>" . $priority_qty . "</a>";

				$loading_qty_arr = $this->get_product_po_list($pid, $cid, 'loading', $wid);
				$loading_qty = array_sum(array_column($loading_qty_arr, 'quantity'));
				$loading_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"loading\")'>" . $loading_qty . "</a>";

				$no_expense_amt_arr = $this->get_product_po_list($pid, $cid, 'no_expense', $wid);
				$no_expense_amt = array_sum(array_column($no_expense_amt_arr, 'amount'));
				$no_expense_amt_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"no_expense\",\"" . $wid . "\")'>" . $no_expense_amt . "</a>";

				$expense_amt_arr = $this->get_product_po_list($pid, $cid, 'expense', $wid);
				$expense_amt = array_sum(array_column($expense_amt_arr, 'amount'));
				$expense_qty_btn = "<a href='javascript:void(0)' onclick='showProductPOList(" . $pid. "," . $cid. ",\"expense\",\"" . $wid . "\")'>" . $expense_amt . "</a>";

				$data[] = array(
					"sr_no"       => ++$start,
					"company"     => $item['company_name'] ?? '-',
					"warehouse"   => $item['warehouse_name'] ?? '-',
					"product_name"=> $item['product_name'] ?? 'Unknown Product (ID: '.$pid.')',
					"quantity"    => $item['current_qty'],
					"white_qty"   => $item['white_qty'] ?? 0,
					"black_qty"   => $item['black_qty'] ?? 0,
					"po_qty"      => $po_qty_btn,
					"priority_qty"=> $priority_qty_btn,
					"loading_qty" => $loading_qty_btn,
					"no_expense_amt" => $no_expense_amt_btn,
					"expense_amt"   => $expense_qty_btn,
					"action"      => $action
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

	public function get_company_wise_product_stock($product_id)
	{
		$data_query = "SELECT 
							  inv.company_id, inv.warehouse_id, inv.product_id,
							  MIN(inv.id) as inventory_id,
							  SUM(inv.quantity) AS current_qty,
							  SUM(inv.official_qty) AS white_qty,
							  SUM(inv.black_qty) AS black_qty,
							  p.name as product_name, w.name as warehouse_name, c.name as company_name
					   FROM inventory inv
					   LEFT JOIN raw_products p ON p.id = inv.product_id
					   LEFT JOIN warehouse w ON w.id = inv.warehouse_id
					   LEFT JOIN company c ON c.id = inv.company_id
					   WHERE inv.product_id = ?
					   GROUP BY inv.company_id, inv.warehouse_id, inv.product_id 
					   HAVING current_qty > 0 
					   ORDER BY p.name ASC";
		return $this->db->query($data_query, array($product_id))->result_array();
	}

	public function get_batches_by_product_warehouse($product_id, $warehouse_id)
	{
		$data_query = "SELECT 
							  inv.id, inv.warehouse_name, inv.item_code, inv.categories, 
							  inv.black_qty, inv.official_qty, inv.product_name, inv.product_id, 
							  inv.quantity, inv.batch_no, inv.official_total_rs, inv.total_amt
					   FROM inventory inv
					   WHERE inv.product_id = ? AND inv.warehouse_id = ? AND inv.quantity > 0
					   ORDER BY inv.id DESC";
		return $this->db->query($data_query, array($product_id, $warehouse_id))->result_array();
	}

	public function get_product_po_list($product_id, $company_id, $status, $warehouse_id = '')
	{
		$where_wid = ($warehouse_id != '') ? " AND po.warehouse_id = '$warehouse_id'" : "";
		if ($status == 'po') {
			// PO list = all orders except purchase_in, using quantities from purchase_order_product
			$query = $this->db->query("SELECT
											po.id,
											po.voucher_no,
											po.date,
											po.supplier_name,
											SUM(pop.quantity) as quantity
										FROM purchase_order po
										JOIN purchase_order_product pop ON po.id = pop.parent_id
										WHERE pop.product_id = '$product_id'
										AND po.company_id = '$company_id'
										AND po.delivery_status != 'purchase_in'
										AND po.is_deleted = '0'
										$where_wid
										GROUP BY po.id, po.voucher_no, po.date, po.supplier_name
										ORDER BY po.date DESC, po.id DESC");
		} elseif ($status == 'loading') {
			// Loading list = all orders except purchase_in, using loading_qty from po_products
			$query = $this->db->query("SELECT
											po.id,
											po.voucher_no,
											po.date,
											po.supplier_name,
											SUM(pp.loading_qty) as quantity
										FROM purchase_order po
										JOIN loading_po_product pp ON po.id = pp.parent_id
										WHERE pp.product_id = '$product_id'
										AND po.company_id = '$company_id'
										AND po.delivery_status != 'purchase_in'
										AND po.is_deleted = '0'
										AND pp.loading_qty > 0
										$where_wid
										GROUP BY po.id, po.voucher_no, po.date, po.supplier_name
										ORDER BY po.date DESC, po.id DESC");
		} elseif($status == 'no_expense') {
			// No Expense list = all orders except purchase_in, using quantity from po_products
			$query = $this->db->query("SELECT
											po.batch_no as voucher_no,
											pp.completed_date as date,
											SUM(po.official_total_rs) as amount
										FROM inventory po
										JOIN purchase_order pp ON po.batch_no = pp.voucher_no
										WHERE po.product_id = '$product_id'
										AND po.company_id = '$company_id'
										AND po.quantity > 0
										$where_wid
										GROUP BY po.product_id, po.batch_no
										ORDER BY po.id DESC");
		} elseif($status == 'expense') {
			// Expense list = all orders except purchase_in, using quantity from po_products
			$query = $this->db->query("SELECT
											po.batch_no as voucher_no,
											pp.completed_date as date,
											SUM(po.total_amt) as amount
										FROM inventory po
										JOIN purchase_order pp ON po.batch_no = pp.voucher_no
										WHERE po.product_id = '$product_id'
										AND po.company_id = '$company_id'
										AND po.quantity > 0
										$where_wid
										GROUP BY po.product_id, po.batch_no
										ORDER BY po.id DESC");
		} else {
			// Priority list = all orders except purchase_in, using quantity from po_products
			$query = $this->db->query("SELECT
											po.id,
											po.voucher_no,
											po.date,
											po.supplier_name,
											SUM(pp.quantity) as quantity
										FROM purchase_order po
										JOIN po_products pp ON po.id = pp.parent_id
										WHERE pp.product_id = '$product_id'
										AND po.company_id = '$company_id'
										AND po.delivery_status != 'purchase_in'
										AND po.is_deleted = '0'
										AND pp.quantity > 0
										$where_wid
										GROUP BY po.id, po.voucher_no, po.date, po.supplier_name
										ORDER BY po.date DESC, po.id DESC");
		}
		return $query->result_array();
	}

	public function get_supplier_outstanding($supplier_id)
	{
		$query = $this->db->query("SELECT 
										po.id,
										po.voucher_no,
										po.date,
										po.delivery_status,
										SUM(pp.actual_qty * pp.unit_price_rmb) as total_actual_rmb,
										SUM(pp.actual_qty * pp.actual_usd) as total_actual_usd,
										SUM(pp.actual_qty * pp.actual_inr) as total_actual_inr,
										SUM(pp.total_amount_usd) as official_usd,
										SUM(pp.official_total_rs) as official_inr,
										(SUM(pp.actual_qty * pp.actual_usd) - SUM(pp.total_amount_usd)) as unofficial_usd,
										(SUM(pp.actual_qty * pp.actual_inr) - SUM(pp.official_total_rs)) as unofficial_inr,
										(SELECT COALESCE(CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')), h.added_by_name)
										 FROM inventory_history h
										 LEFT JOIN sys_users u ON h.added_by_id = u.id
										 WHERE h.order_id = po.id AND h.status IN ('in', 'Purchase In Updated')
										 LIMIT 1) as added_by_name
									FROM purchase_order po
									JOIN purchase_in_product pp ON po.id = pp.parent_id
									WHERE pp.supplier_id = '$supplier_id'
									AND po.delivery_status = 'purchase_in'
									AND po.is_deleted = '0'
									GROUP BY po.id, po.voucher_no, po.date, po.delivery_status
									ORDER BY po.date DESC, po.id DESC");
		return $query->result_array();
	}

	public function get_supplier_payments($supplier_id, $payment_type = null)
	{
		$typeWhere = '';
		if ($payment_type !== null && $payment_type !== '') {
			$typeWhere = "AND p.payment_type = " . $this->db->escape($payment_type);
		}

		$query = $this->db->query("SELECT 
										p.*,
										CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
									FROM payments p
									LEFT JOIN sys_users u ON p.added_by = u.id
									WHERE p.supplier_id = '$supplier_id'
									AND p.is_delete = 0
									$typeWhere
									ORDER BY p.payment_date DESC, p.id DESC");
		return $query->result_array();
	}

	public function get_batches_by_supplier($supplier_id)
	{
		$query = $this->db->query("SELECT DISTINCT po.voucher_no
									FROM purchase_order po
									JOIN po_products pp ON po.id = pp.parent_id
									WHERE pp.supplier_id = '$supplier_id'
									AND po.delivery_status = 'purchase_in'
									AND po.is_deleted = '0'
									ORDER BY po.voucher_no ASC");
		return $query->result_array();
	}
	public function get_customer_ledger($customer_id)
	{
		$query = $this->db->query("SELECT 
										*
									FROM sales_order
									WHERE customer_id = '$customer_id'
									AND is_deleted = '0'
									AND is_approved = '1'
									ORDER BY date DESC, id DESC");
		return $query->result_array();
	}

	public function get_unpaid_sales_orders_by_customer($customer_id, $payment_type)
	{
		$company_id = $this->session->userdata('company_id');
		
		$this->db->select('*');
		$this->db->from('sales_order');
		$this->db->where('customer_id', $customer_id);
		$this->db->where('company_id', $company_id);
		$this->db->where('is_paid', 0);
		$this->db->where('is_generated', 1);
		$this->db->where('is_deleted', 0);
		
		if ($payment_type == 'official') {
			$this->db->where('net_sales_value_1 > total_white_paid', NULL, FALSE);
		} else if ($payment_type == 'unofficial') {
			$this->db->where('total_black_amt > total_black_paid', NULL, FALSE);
		}
		
		$this->db->order_by('date', 'ASC');
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_customer_credits($customer_id)
	{
		$this->db->select('*');
		$this->db->from('customer_credit');
		$this->db->where('customer_id', $customer_id);
		$this->db->where('credit_balance > debit_balance', NULL, FALSE);
		$this->db->order_by('date', 'ASC');
		$query = $this->db->get();
		return $query->result_array();
	}

	public function add_customer_payment()
	{
		$data['customer_id'] = $this->input->post('customer_id');
		$data['date'] = $this->input->post('payment_date');
		$data['inv_no'] = $this->input->post('invoice_no');
		$data['amount'] = $this->input->post('amount_rs');
		$data['payment_type'] = $this->input->post('payment_type');
		$data['payment_method'] = $this->input->post('payment_method');
		$data['company_bank_account'] = $this->input->post('bank_account');
		$data['narration'] = $this->input->post('narration');
		
		$data['invoices_selected_count'] = $this->input->post('invoices_selected_count');
		$data['balance_after'] = $this->input->post('balance_after');
		$data['total_outstanding'] = $this->input->post('total_outstanding');
		$data['allocated_inv'] = $this->input->post('allocated_inv');
		$data['total_tender'] = $this->input->post('total_tender');
		$data['on_account'] = $this->input->post('on_account');
		$data['adjustments'] = $this->input->post('adjustments');
		
		$data['added_by'] = $this->session->userdata('super_user_id');
		$data['added_by_name'] = $this->session->userdata('super_name');
		
		// Get customer name
		$customer = $this->db->get_where('customer', ['id' => $data['customer_id']])->row_array();
		$data['customer_name'] = $customer['owner_name'];

		$this->db->insert('customer_payment', $data);
		$payment_id = $this->db->insert_id();

		// Insert Records (Allocations)
		$order_ids = $this->input->post('order_ids');
		if (!empty($order_ids)) {
			$apply_amounts = $this->input->post('apply_amount');
			$order_dates = $this->input->post('order_date');
			$ref_nos = $this->input->post('refrence_no');
			$order_totals = $this->input->post('order_total');
			$order_pendings = $this->input->post('order_pending'); // This now stores the remaining amount after payment

			foreach ($order_ids as $id) {
				$paid_now = $apply_amounts[$id];
				if ($paid_now <= 0) continue;

				$record_data = [
					'payment_id' => $payment_id,
					'order_id' => $id,
					'order_date' => $order_dates[$id],
					'refrence_no' => $ref_nos[$id],
					'order_total' => $order_totals[$id],
					'order_paid' => $paid_now, // Amount paid in this transaction
					'order_pending' => $order_pendings[$id] // Remaining balance
				];
				$this->db->insert('customer_payment_record', $record_data);

				// Update Sales Order
				$order = $this->db->get_where('sales_order', ['id' => $id])->row_array();
				if ($data['payment_type'] == 'official') {
					$new_paid = $order['total_white_paid'] + $paid_now;
					$this->db->where('id', $id)->update('sales_order', ['total_white_paid' => $new_paid]);
				} else {
					$new_paid = $order['total_black_paid'] + $paid_now;
					$this->db->where('id', $id)->update('sales_order', ['total_black_paid' => $new_paid]);
				}

				// Check if fully paid
				$updated_order = $this->db->get_where('sales_order', ['id' => $id])->row_array();
				if ($updated_order['net_sales_value_1'] <= $updated_order['total_white_paid'] && 
					$updated_order['total_black_amt'] <= $updated_order['total_black_paid']) {
					$this->db->where('id', $id)->update('sales_order', ['is_paid' => 1]);
				}
			}
		}

		// Update Credits Used (Debit existing credits)
		$credit_ids = $this->input->post('credit_ids');
		if (!empty($credit_ids)) {
			$apply_credit_amounts = $this->input->post('apply_credit_amount');
			foreach ($credit_ids as $cid) {
				$credit_used = $apply_credit_amounts[$cid];
				if ($credit_used <= 0) continue;

				$this->db->set('debit_balance', 'debit_balance + ' . (float)$credit_used, FALSE);
				$this->db->where('id', $cid);
				$this->db->update('customer_credit');
			}
		}

		// Handle On Account (Create new credit for future use)
		if ($data['on_account'] > 0) {
			$credit_data = [
				'customer_id' => $data['customer_id'],
				'payment_id' => $payment_id,
				'item_no' => $data['inv_no'],
				'date' => $data['date'],
				'credit_balance' => $data['on_account'],
				'debit_balance' => 0
			];
			$this->db->insert('customer_credit', $credit_data);
		}

		$resultpost = array(
			"status"  => 200,
			"message" => "Payment receipt added successfully",
			"url"     => base_url() . 'inventory/payment_receipt',
		);
		echo json_encode($resultpost);
		exit();
	}

	public function get_customer_payments()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != "") {
			$keyword = $filter_data['keywords'];
			$keyword_filter = " AND (customer_name LIKE '%" . $keyword . "%' OR inv_no LIKE '%" . $keyword . "%')";
		}

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$date_range = explode(' - ', $_REQUEST['date_range']);
			$from = date('Y-m-d', strtotime($date_range['0']));
			$to = date('Y-m-d', strtotime($date_range['1']));

			$keyword_filter .= " AND (DATE(date) >= '" . $from . "' AND DATE(date) <= '" . $to . "')";
		}

		// $company_id = $this->session->userdata('company_id');
		$total_count = $this->db->query("SELECT id FROM customer_payment WHERE 1=1" . $keyword_filter)->num_rows();
		$query = $this->db->query("SELECT * FROM customer_payment WHERE 1=1" . $keyword_filter . " ORDER BY id DESC LIMIT $start, $length");
		
		if (!empty($query)) {
			$sr_no = $start;
			foreach ($query->result_array() as $item) {
				$data[] = array(
					"sr_no"         => ++$sr_no,
					"date"          => $item['date'] ? date('d M, Y', strtotime($item['date'])) : '-',
					"inv_no"        => $item['inv_no'],
					"customer_name" => $item['customer_name'],
					"total_tender"  => '₹' . number_format($item['total_tender'], 2),
					"allocated_inv" => '₹' . number_format($item['allocated_inv'], 2),
					"on_account"    => '₹' . number_format($item['on_account'], 2),
					"adjustments"   => '₹' . number_format($item['adjustments'], 2),
					"payment_type"  => ucfirst($item['payment_type']),
					"payment_method" => ucfirst($item['payment_method']),
					"added_by_name" => $item['added_by_name'],
					"actions"       => '' // Hidden for now as per request
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

	public function get_customer_payments_by_id($customer_id)
	{
		$query = $this->db->query("SELECT 
										p.*,
										CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
									FROM customer_payment p
									LEFT JOIN sys_users u ON p.added_by = u.id
									WHERE p.customer_id = '$customer_id'
									ORDER BY p.date DESC, p.id DESC");
		return $query->result_array();
	}

	public function get_product_formulas()
	{
		$params['draw'] = $_REQUEST['draw'];
		$start = $_REQUEST['start'];
		$length = $_REQUEST['length'];

		$filter_data['keywords'] = clean_and_escape($_REQUEST['search']['value']);
		$data = array();
		$keyword_filter = "";

		if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
			$keyword        = $filter_data['keywords'];
			$keyword_filter = " AND (p.name like '%" . $keyword . "%' OR p.item_code like '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("SELECT p.id FROM raw_products as p
		WHERE p.is_deleted='0' AND p.product_type='local' AND p.has_formula='1' $keyword_filter ORDER BY p.id ASC")->num_rows();

		$query = $this->db->query("SELECT p.id, p.name, p.item_code, p.categories FROM raw_products as p
		WHERE p.is_deleted='0' AND p.product_type='local' AND p.has_formula='1' $keyword_filter ORDER BY p.id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				$delete_url = "confirm_modal('" . base_url() . "inventory/product_formula/delete/" . $id . "','Are you sure want to delete this formula!')";
				$edit_url = base_url() . 'inventory/product-formula/edit/' . $id;
				$action = '';
				$action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';

				$action .='<a href="#" onclick="'.$delete_url.'" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del" ><i class="fa fa-trash" aria-hidden="true"></i></button></a>'; 

				// Category
				$category = $this->common_model->getRowById('categories', '*', ['id' => $item['categories']]);
				$category_name = $category['name'] ?? '-';

				// Fetch formula ingredients for this parent product
				$formula_query = $this->db->query("SELECT pf.quantity, rp.name 
					FROM product_formula pf 
					JOIN raw_products rp ON pf.product_id = rp.id 
					WHERE pf.parent_id = ? 
					ORDER BY pf.id ASC", array($id));

				$formula_list = "<ul>";
				if ($formula_query->num_rows() > 0) {
					foreach ($formula_query->result_array() as $f_item) {
						$formula_list .= "<li>" . htmlspecialchars($f_item['name']) . " (" . intval($f_item['quantity']) . ")</li>";
					}
				} else {
					$formula_list .= "<li>No ingredients</li>";
				}
				$formula_list .= "</ul>";

				$data[] = array(
					"sr_no"       => ++$start,
					"name"        => $item['name'],
					"item_code"   => $item['item_code'],
					"category_name" => $category_name,
					"formula"     => $formula_list,
					"action"      => $action,
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

	public function add_product_formula()
	{
		$resultpost = array(
			"status"  => 200,
			"message" => "Product Formula added successfully",
			"url"     => base_url('inventory/product-formula'),
		);

		$parent_id = (int)$this->input->post('parent_id');
		$ingredient_ids = $this->input->post('product_id');
		$quantities = $this->input->post('quantity');

		// 1. Check parent product
		if (empty($parent_id)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please select a product."
			));
		}

		$parent_product = $this->db->where(array('id' => $parent_id, 'is_deleted' => '0'))->get('raw_products')->row_array();
		if (empty($parent_product)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Selected parent product does not exist."
			));
		}

		if ($parent_product['product_type'] !== 'local') {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Formula can only be created for local products."
			));
		}

		if ($parent_product['has_formula'] == 1) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Selected product already has a formula."
			));
		}

		// 2. Validate ingredients
		if (empty($ingredient_ids) || !is_array($ingredient_ids) || count($ingredient_ids) < 2) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please select at least 2 ingredients."
			));
		}

		$seen_products = array();
		$validated_ingredients = array();

		for ($i = 0; $i < count($ingredient_ids); $i++) {
			$ing_id = (int)$ingredient_ids[$i];
			$qty = (int)$quantities[$i];

			if (empty($ing_id)) {
				continue; // Skip empty rows if any
			}

			if ($ing_id === $parent_id) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Parent product cannot be added as its own ingredient."
				));
			}

			if (in_array($ing_id, $seen_products)) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Duplicate ingredients are not allowed."
				));
			}

			if ($qty <= 0) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Quantity must be greater than 0."
				));
			}

			// Validate child product type is local and exists
			$child_product = $this->db->where(array('id' => $ing_id, 'is_deleted' => '0'))->get('raw_products')->row_array();
			if (empty($child_product)) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Selected ingredient product does not exist."
				));
			}

			if ($child_product['product_type'] !== 'local') {
				return simple_json_output(array(
					"status" => 400,
					"message" => "All ingredients must be local products."
				));
			}

			$seen_products[] = $ing_id;
			$validated_ingredients[] = array(
				'parent_id' => $parent_id,
				'product_id' => $ing_id,
				'quantity' => $qty,
				'added_by' => $this->session->userdata('super_user_id'),
				'added_date' => date("Y-m-d H:i:s")
			);
		}

		if (count($validated_ingredients) < 2) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please select at least 2 valid ingredients."
			));
		}

		$this->db->trans_begin();

		// Insert ingredients into product_formula
		foreach ($validated_ingredients as $row) {
			$this->db->insert('product_formula', $row);
		}

		// Update parent has_formula flag, expense, and remark
		$expense = floatval($this->input->post('expense'));
		$remark = clean_and_escape($this->input->post('remark'));
		$this->db->where('id', $parent_id);
		$this->db->update('raw_products', array('has_formula' => 1, 'expense' => $expense, 'remark' => $remark));

		// Insert product charges / expenses
		$charge_expense_ids = $this->input->post('charge_expense_id');
		$charge_amounts = $this->input->post('charge_amount');
		if (is_array($charge_expense_ids)) {
			for ($i = 0; $i < count($charge_expense_ids); $i++) {
				$expense_id = (int)($charge_expense_ids[$i] ?? 0);
				$amount = floatval($charge_amounts[$i] ?? 0);
				if ($expense_id > 0) {
					$expense = $this->common_model->getRowById('expense_type', 'name', ['id' => $expense_id]);
					$charge_data = array(
						'product_id' => $parent_id,
						'expense_id' => $expense_id,
						'name' => $expense ? $expense['name'] : '',
						'amount' => $amount,
						'created_at' => date("Y-m-d H:i:s")
					);
					$this->db->insert('product_charges', $charge_data);
				}
			}
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return simple_json_output(array(
				"status" => 400,
				"message" => "Failed to save product formula. Please try again."
			));
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', 'Product Formula added successfully');
		return simple_json_output($resultpost);
	}

	public function edit_product_formula($parent_id = "")
	{
		$parent_id = (int)$parent_id;
		$resultpost = array(
			"status"  => 200,
			"message" => "Product Formula updated successfully",
			"url"     => base_url('inventory/product-formula'),
		);

		$ingredient_ids = $this->input->post('product_id');
		$quantities = $this->input->post('quantity');

		if (empty($parent_id)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Invalid parent product ID."
			));
		}

		$parent_product = $this->db->where(array('id' => $parent_id, 'is_deleted' => '0'))->get('raw_products')->row_array();
		if (empty($parent_product)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Selected parent product does not exist."
			));
		}

		if ($parent_product['product_type'] !== 'local') {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Formula can only be edited for local products."
			));
		}

		// Validate ingredients
		if (empty($ingredient_ids) || !is_array($ingredient_ids) || count($ingredient_ids) < 2) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please select at least 2 ingredients."
			));
		}

		$seen_products = array();
		$validated_ingredients = array();

		for ($i = 0; $i < count($ingredient_ids); $i++) {
			$ing_id = (int)$ingredient_ids[$i];
			$qty = (int)$quantities[$i];

			if (empty($ing_id)) {
				continue;
			}

			if ($ing_id === $parent_id) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Parent product cannot be added as its own ingredient."
				));
			}

			if (in_array($ing_id, $seen_products)) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Duplicate ingredients are not allowed."
				));
			}

			if ($qty <= 0) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Quantity must be greater than 0."
				));
			}

			// Validate child product is local and exists
			$child_product = $this->db->where(array('id' => $ing_id, 'is_deleted' => '0'))->get('raw_products')->row_array();
			if (empty($child_product)) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Selected ingredient product does not exist."
				));
			}

			if ($child_product['product_type'] !== 'local') {
				return simple_json_output(array(
					"status" => 400,
					"message" => "All ingredients must be local products."
				));
			}

			$seen_products[] = $ing_id;
			$validated_ingredients[] = array(
				'parent_id' => $parent_id,
				'product_id' => $ing_id,
				'quantity' => $qty,
				'added_by' => $this->session->userdata('super_user_id'),
				'added_date' => date("Y-m-d H:i:s")
			);
		}

		if (count($validated_ingredients) < 2) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please select at least 2 valid ingredients."
			));
		}

		$this->db->trans_begin();

		// Delete old ingredients
		$this->db->where('parent_id', $parent_id);
		$this->db->delete('product_formula');

		// Insert new ingredients
		foreach ($validated_ingredients as $row) {
			$this->db->insert('product_formula', $row);
		}

		// Ensure has_formula is set to 1 and update expense and remark
		$expense = floatval($this->input->post('expense'));
		$remark = clean_and_escape($this->input->post('remark'));
		$this->db->where('id', $parent_id);
		$this->db->update('raw_products', array('has_formula' => 1, 'expense' => $expense, 'remark' => $remark));

		// Delete old charges and insert new ones
		$this->db->where('product_id', $parent_id);
		$this->db->delete('product_charges');

		$charge_expense_ids = $this->input->post('charge_expense_id');
		$charge_amounts = $this->input->post('charge_amount');
		if (is_array($charge_expense_ids)) {
			for ($i = 0; $i < count($charge_expense_ids); $i++) {
				$expense_id = (int)($charge_expense_ids[$i] ?? 0);
				$amount = floatval($charge_amounts[$i] ?? 0);
				if ($expense_id > 0) {
					$expense = $this->common_model->getRowById('expense_type', 'name', ['id' => $expense_id]);
					$charge_data = array(
						'product_id' => $parent_id,
						'expense_id' => $expense_id,
						'name' => $expense ? $expense['name'] : '',
						'amount' => $amount,
						'created_at' => date("Y-m-d H:i:s")
					);
					$this->db->insert('product_charges', $charge_data);
				}
			}
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return simple_json_output(array(
				"status" => 400,
				"message" => "Failed to update product formula. Please try again."
			));
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', 'Product Formula updated successfully');
		return simple_json_output($resultpost);
	}

	public function delete_product_formula($parent_id = "")
	{
		$parent_id = (int)$parent_id;
		$resultpost = array(
			"status" => 200,
			"message" => "Product Formula deleted successfully",
			"url" => $this->session->userdata('previous_url'),
		);

		if (empty($parent_id)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Invalid parent product ID."
			));
		}

		$this->db->trans_begin();

		// Delete formula rows
		$this->db->where('parent_id', $parent_id);
		$this->db->delete('product_formula');

		// Delete product charges
		$this->db->where('product_id', $parent_id);
		$this->db->delete('product_charges');

		// Set has_formula to 0 and reset expense
		$this->db->where('id', $parent_id);
		$this->db->update('raw_products', array('has_formula' => 0, 'expense' => 0.00000));

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return simple_json_output(array(
				"status" => 400,
				"message" => "Failed to delete product formula. Please try again."
			));
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', 'Product Formula deleted successfully');
		return simple_json_output($resultpost);
	}

	public function add_product_stock()
	{
		$warehouse_id = (int)$this->input->post('warehouse_id');
		$parent_id = (int)$this->input->post('product_id');
		$qty = (int)$this->input->post('quantity');
		$expense = floatval($this->input->post('expense'));
		$type = $this->input->post('type'); // 'black' or 'white'
		$batch_no = trim($this->input->post('batch_no'));
		$company_id = (int)$this->session->userdata('company_id');

		$resultpost = array(
			"status"  => 200,
			"message" => "Product Stock produced successfully",
			"url"     => base_url('inventory/my-stock?warehouse=' . $warehouse_id),
		);

		if (empty($batch_no)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please enter a batch number."
			));
		}

		if (empty($parent_id) || $qty <= 0) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Invalid parent product or quantity."
			));
		}

		if ($type !== 'black' && $type !== 'white') {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Invalid type selected."
			));
		}

		// 1. Fetch parent product details
		$parent_product = $this->db->where(array('id' => $parent_id, 'is_deleted' => 0))->get('raw_products')->row_array();
		if (empty($parent_product)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Parent product does not exist."
			));
		}

		if ($parent_product['has_formula'] != 1) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Parent product does not have a formula."
			));
		}

		// 2. Fetch formula ingredients
		$formula_items = $this->db->query("SELECT pf.product_id, pf.quantity as req_qty, rp.name
			FROM product_formula pf
			JOIN raw_products rp ON pf.product_id = rp.id
			WHERE pf.parent_id = ? 
			ORDER BY pf.id ASC", array($parent_id))->result_array();

		if (empty($formula_items)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Formula has no ingredients."
			));
		}

		// 3. Check ingredient sufficiency on the server side
		foreach ($formula_items as $item) {
			$ing_id = $item['product_id'];
			$total_req = (int)$item['req_qty'] * $qty;

			if ($type == 'black') {
				$inv_qty_row = $this->db->query("SELECT SUM(black_qty) as av_qty 
					FROM inventory 
					WHERE product_id = ? AND warehouse_id = ? AND company_id = ?", 
					array($ing_id, $warehouse_id, $company_id))->row_array();
			} else {
				$inv_qty_row = $this->db->query("SELECT SUM(official_qty) as av_qty 
					FROM inventory 
					WHERE product_id = ? AND warehouse_id = ? AND company_id = ?", 
					array($ing_id, $warehouse_id, $company_id))->row_array();
			}

			$available = $inv_qty_row ? (int)$inv_qty_row['av_qty'] : 0;
			if ($available < $total_req) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Insufficient stock for ingredient: " . $item['name']
				));
			}
		}

		$this->db->trans_begin();

		// 4. Update expense in master product
		$this->db->where('id', $parent_id)->update('raw_products', array('expense' => $expense));

		// 5. Deduct ingredients FIFO
		foreach ($formula_items as $item) {
			$ing_id = $item['product_id'];
			$total_req = (int)$item['req_qty'] * $qty;

			$ing_product = $this->db->where(array('id' => $ing_id, 'is_deleted' => 0))->get('raw_products')->row_array();
			$ing_name = $ing_product ? $ing_product['name'] : '';
			$ing_cats = $ing_product ? $ing_product['categories'] : '';
			$ing_sku  = $ing_product ? $ing_product['item_code'] : '';

			// Get all inventory batches with quantity > 0
			if ($type == 'black') {
				$batches = $this->db->query("SELECT * FROM inventory 
					WHERE product_id = ? AND warehouse_id = ? AND company_id = ? AND black_qty > 0 
					ORDER BY id ASC", array($ing_id, $warehouse_id, $company_id))->result_array();
			} else {
				$batches = $this->db->query("SELECT * FROM inventory 
					WHERE product_id = ? AND warehouse_id = ? AND company_id = ? AND official_qty > 0 
					ORDER BY id ASC", array($ing_id, $warehouse_id, $company_id))->result_array();
			}

			$remaining_req = $total_req;
			foreach ($batches as $batch) {
				if ($remaining_req <= 0) break;

				$available_batch_qty = ($type == 'black') ? (int)$batch['black_qty'] : (int)$batch['official_qty'];
				$deduct = min($remaining_req, $available_batch_qty);

				$new_qty = (int)$batch['quantity'] - $deduct;
				if ($type == 'black') {
					$new_black = (int)$batch['black_qty'] - $deduct;
					$new_official = (int)$batch['official_qty'];
				} else {
					$new_black = (int)$batch['black_qty'];
					$new_official = (int)$batch['official_qty'] - $deduct;
				}

				// Update inventory row
				$this->db->where('id', $batch['id'])->update('inventory', array(
					'quantity' => $new_qty,
					'black_qty' => $new_black,
					'official_qty' => $new_official
				));

				// Insert deduction history (stock out)
				$history = array(
					'supplier_id' => $batch['supplier_id'],
					'company_id' => $company_id,
					'parent_id' => $batch['id'],
					'warehouse_id' => $warehouse_id,
					'warehouse_name' => $batch['warehouse_name'],
					'product_id' => $ing_id,
					'product_name' => $ing_name,
					'categories' => $ing_cats,
					'sku' => $ing_sku,
					'item_code' => $ing_sku,
					'order_id' => NULL,
					'status' => 'out',
					'quantity' => $deduct,
					'actual_rmb' => 0.00,
					'total_rmb' => 0.00,
					'actual_usd' => 0.00,
					'official_qty' => ($type == 'white') ? $deduct : 0,
					'official_rate_rs' => 0.00,
					'official_total_rs' => 0.00,
					'actual_inr' => 0.00,
					'black_qty' => ($type == 'black') ? $deduct : 0,
					'pending_qty' => 0,
					'black_rate_rs' => 0.00,
					'black_total_rs' => 0.00,
					'duty_percent' => 0.00,
					'duty_amt' => 0.00,
					'duty_surcharge' => 0.00,
					'taxable_value' => 0.00,
					'gst_amt' => 0.00,
					'total_amt' => 0.00,
					'received_date' => date("Y-m-d"),
					'batch_no' => $batch['batch_no'],
					'expiry_date' => NULL,
					'invoice_no' => 'Formula Deduction',
					'is_deleted' => 0,
					'added_date' => date("Y-m-d H:i:s"),
					'added_by_id' => $this->session->userdata('super_user_id'),
					'added_by_name' => $this->session->userdata('super_name'),
				);
				$this->db->insert('inventory_history', $history);

				$remaining_req -= $deduct;
			}
		}

		// 6. Insert produced parent product into inventory
		$warehouse = $this->db->where('id', $warehouse_id)->get('warehouse')->row_array();
		$warehouse_name = $warehouse ? $warehouse['name'] : '';

		$parent_name = $parent_product['name'];
		$parent_cats = $parent_product['categories'];
		$parent_sku  = $parent_product['item_code'];

		$inv_parent = array(
			'supplier_id' => NULL,
			'company_id' => $company_id,
			'warehouse_id' => $warehouse_id,
			'warehouse_name' => $warehouse_name,
			'product_id' => $parent_id,
			'product_name' => $parent_name,
			'categories' => $parent_cats,
			'sku' => $parent_sku,
			'item_code' => $parent_sku,
			'quantity' => $qty,
			'actual_rmb' => 0.00,
			'total_rmb' => 0.00,
			'actual_usd' => 0.00,
			'official_qty' => ($type == 'white') ? $qty : 0,
			'official_rate_rs' => 0.00,
			'official_total_rs' => 0.00,
			'actual_inr' => 0.00,
			'black_qty' => ($type == 'black') ? $qty : 0,
			'pending_qty' => 0,
			'duty_percent' => 0.00,
			'duty_amt' => 0.00,
			'duty_surcharge' => 0.00,
			'taxable_value' => 0.00,
			'gst_amt' => 0.00,
			'total_amt' => 0.00,
			'batch_no' => $batch_no,
			'po_row_id' => 0,
			'expiry_date' => NULL,
		);

		if (!$this->db->insert('inventory', $inv_parent)) {
			$this->db->trans_rollback();
			return simple_json_output(array(
				"status" => 400,
				"message" => "Failed to insert product in inventory."
			));
		}
		$new_parent_inv_id = $this->db->insert_id();

		// 7. Insert production history (stock in)
		$history_parent = array(
			'supplier_id' => NULL,
			'company_id' => $company_id,
			'parent_id' => $new_parent_inv_id,
			'warehouse_id' => $warehouse_id,
			'warehouse_name' => $warehouse_name,
			'product_id' => $parent_id,
			'product_name' => $parent_name,
			'categories' => $parent_cats,
			'sku' => $parent_sku,
			'item_code' => $parent_sku,
			'order_id' => NULL,
			'status' => 'in',
			'quantity' => $qty,
			'actual_rmb' => 0.00,
			'total_rmb' => 0.00,
			'actual_usd' => 0.00,
			'official_qty' => ($type == 'white') ? $qty : 0,
			'official_rate_rs' => 0.00,
			'official_total_rs' => 0.00,
			'actual_inr' => 0.00,
			'black_qty' => ($type == 'black') ? $qty : 0,
			'pending_qty' => 0,
			'black_rate_rs' => 0.00,
			'black_total_rs' => 0.00,
			'duty_percent' => 0.00,
			'duty_amt' => 0.00,
			'duty_surcharge' => 0.00,
			'taxable_value' => 0.00,
			'gst_amt' => 0.00,
			'total_amt' => 0.00,
			'received_date' => date("Y-m-d"),
			'batch_no' => $batch_no,
			'expiry_date' => NULL,
			'invoice_no' => 'Formula Production',
			'is_deleted' => 0,
			'added_date' => date("Y-m-d H:i:s"),
			'added_by_id' => $this->session->userdata('super_user_id'),
			'added_by_name' => $this->session->userdata('super_name'),
		);
		$this->db->insert('inventory_history', $history_parent);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return simple_json_output(array(
				"status" => 400,
				"message" => "Transaction failed. Rolled back."
			));
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', 'Product Stock production successfully completed.');
		return simple_json_output($resultpost);
	}

	public function get_formula_product_orders()
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
			$keyword_filter .= " AND (batch_no like '%" . $keyword . "%' OR name like '%" . $keyword . "%')";
		endif;

		$keyword_filter .= " AND (company_id = '$company_id')";

		if (isset($_REQUEST['date_range']) && $_REQUEST['date_range'] != "") {
			$added_date = explode(' - ', $_REQUEST['date_range']);
			$from =  date('Y-m-d', strtotime($added_date[0]));
			$to =  date('Y-m-d', strtotime($added_date[1]));
			if ($from == $to) {
				$keyword_filter .= " AND (DATE(added_date) = '$from')";
			} else {
				$keyword_filter .= " AND (DATE(added_date) BETWEEN '$from' AND '$to')";
			}
		}

		$total_count = $this->db->query("SELECT id FROM formula_product_in WHERE 1=1 $keyword_filter ORDER BY id ASC")->num_rows();
		$query = $this->db->query("SELECT * FROM formula_product_in WHERE 1=1 $keyword_filter ORDER BY id DESC LIMIT $start, $length");

		if (!empty($query)) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];

				// Actions
				$action ='-';
				// $delete_url = "confirm_modal('" . base_url() . "inventory/formula-product-order/delete/" . $id . "','Are you sure want to delete!')";
				
				// $action = '<div class="btn-group">
				// 	<button type="button" class="btn btn-md btn-outline-dark mj-action btn-rounded btn-icon " data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px !important;">
				// 		<i class="mdi mdi-dots-vertical"></i></button>
				// 	<div class="dropdown-menu">
				// 		<a class="dropdown-item" href="javascript:void(0)" onclick="' . $delete_url . '"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
				// 	</div>
				// </div>';

				$data[] = array(
					"sr_no"             => ++$start,
					"id"                => $item['id'],
					"date"              => date('d M, Y', strtotime($item['added_date'])),
					"batch_no"          => $item['batch_no'],
					"product_name"      => $item['name'],
					"qty"               => $item['qty'],
					"total_off_cost"    => floatval($item['total_off_cost']),
					"total_actual_cost" => floatval($item['total_actual_cost']),
					"total_expense"     => floatval($item['total_expense']),
					"final_total"       => floatval($item['final_total']),
					"action"            => $action
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

	public function add_formula_product_order()
	{
		$company_id   = (int)$this->session->userdata('company_id');
		$parent_id    = (int)$this->input->post('product_id');
		$batch_no     = trim($this->input->post('batch_no'));
		$qty          = (int)$this->input->post('quantity');
		$warehouse_id = (int)$this->input->post('warehouse_id');
		$type         = $this->input->post('type'); // 'white' or 'black'
		$date         = $this->input->post('date') ? date("Y-m-d", strtotime($this->input->post('date'))) : date("Y-m-d");

		$batch_ids    = $this->input->post('batch_id');
		$white_qtys   = $this->input->post('white_qty');
		$black_qtys   = $this->input->post('black_qty');

		if (strtolower($type) == 'official' || strtolower($type) == 'white') {
			$type = 'white';
		} else {
			$type = 'black';
		}

		if (empty($parent_id) || $qty <= 0) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please select a valid product and enter a quantity greater than 0."
			));
		}

		if (empty($batch_no)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please enter a batch number."
			));
		}

		if (empty($warehouse_id)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Please select a warehouse."
			));
		}

		// 1. Check parent product exists, is local, and has formula
		$parent_product = $this->db->where(array('id' => $parent_id, 'is_deleted' => 0))->get('raw_products')->row_array();
		if (empty($parent_product) || $parent_product['has_formula'] != 1) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Selected product is invalid or does not have a formula."
			));
		}

		// 2. Check warehouse exists
		$warehouse = $this->db->where(array('id' => $warehouse_id, 'is_deleted' => 0))->get('warehouse')->row_array();
		if (empty($warehouse)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Selected warehouse does not exist."
			));
		}

		// 3. Rule 5: Check that parent batch number is unique for this product in company
		$existing_batch = $this->db->where(array(
			'product_id' => $parent_id,
			'batch_no' => $batch_no,
			'company_id' => $company_id
		))->get('inventory')->row_array();
		if (!empty($existing_batch)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Batch number '" . htmlspecialchars($batch_no) . "' already exists in inventory for this product."
			));
		}

		// 4. Fetch formula ingredients
		$formula_items = $this->db->query("SELECT pf.product_id, pf.quantity as req_qty_1, rp.name, rp.categories, rp.item_code
			FROM product_formula pf
			JOIN raw_products rp ON pf.product_id = rp.id
			WHERE pf.parent_id = ? 
			ORDER BY pf.id ASC", array($parent_id))->result_array();
		if (empty($formula_items)) {
			return simple_json_output(array(
				"status" => 400,
				"message" => "Formula has no ingredients."
			));
		}

		// 5. Validate batches and quantities allocated for each ingredient
		$allocated_ingredients = array();
		$total_off_cost = 0;
		$total_black_cost = 0;
		$total_actual_cost = 0;

		foreach ($formula_items as $item) {
			$ing_id = (int)$item['product_id'];
			$required_total = (int)$item['req_qty_1'] * $qty;

			$ing_batches = isset($batch_ids[$ing_id]) ? $batch_ids[$ing_id] : array();
			$ing_white = isset($white_qtys[$ing_id]) ? $white_qtys[$ing_id] : array();
			$ing_black = isset($black_qtys[$ing_id]) ? $black_qtys[$ing_id] : array();

			if (empty($ing_batches)) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Please select and allocate stock batches for ingredient: " . $item['name']
				));
			}

			$allocated_total = 0;
			$seen_batches = array();
			$batches_to_process = array();

			for ($i = 0; $i < count($ing_batches); $i++) {
				$bid = (int)$ing_batches[$i];
				$w_qty = (int)$ing_white[$i];
				$b_qty = (int)$ing_black[$i];

				if (empty($bid)) {
					return simple_json_output(array(
						"status" => 400,
						"message" => "Please select a valid batch for ingredient: " . $item['name']
					));
				}

				if ($w_qty < 0 || $b_qty < 0) {
					return simple_json_output(array(
						"status" => 400,
						"message" => "Negative quantity is not allowed."
					));
				}

				if ($w_qty == 0 && $b_qty == 0) {
					continue; // Skip empty rows
				}

				// Rule 2: Can't select same batch again in ingredient
				if (in_array($bid, $seen_batches)) {
					return simple_json_output(array(
						"status" => 400,
						"message" => "Duplicate batch selected for ingredient: " . $item['name']
					));
				}
				$seen_batches[] = $bid;

				// Fetch batch detail from inventory
				$batch_detail = $this->db->where(array(
					'id' => $bid,
					'product_id' => $ing_id,
					'warehouse_id' => $warehouse_id,
					'company_id' => $company_id
				))->get('inventory')->row_array();

				if (empty($batch_detail)) {
					return simple_json_output(array(
						"status" => 400,
						"message" => "Selected batch does not exist for ingredient " . $item['name'] . " in this warehouse."
					));
				}

				// Rule 1: The batch white and black quantity cannot exceed more than available qty
				if ($w_qty > (int)$batch_detail['official_qty']) {
					return simple_json_output(array(
						"status" => 400,
						"message" => "White quantity allocated (" . $w_qty . ") exceeds available official qty (" . $batch_detail['official_qty'] . ") in batch " . $batch_detail['batch_no'] . " for " . $item['name']
					));
				}
				if ($b_qty > (int)$batch_detail['black_qty']) {
					return simple_json_output(array(
						"status" => 400,
						"message" => "Black quantity allocated (" . $b_qty . ") exceeds available black qty (" . $batch_detail['black_qty'] . ") in batch " . $batch_detail['batch_no'] . " for " . $item['name']
					));
				}

				$allocated_total += ($w_qty + $b_qty);

				// Calculate costs
				$total_off_cost += ($w_qty * floatval($batch_detail['official_rate_rs']));
				$total_black_cost += ($b_qty * floatval($batch_detail['actual_inr']));

				$batch_actual_cost = floatval($batch_detail['actual_inr']) * ($w_qty + $b_qty);
				$total_actual_cost += $batch_actual_cost;

				$batches_to_process[] = array(
					'batch_detail' => $batch_detail,
					'white_qty' => $w_qty,
					'black_qty' => $b_qty,
					'total_qty' => $w_qty + $b_qty,
					'actual_cost' => $batch_actual_cost
				);
			}

			// Rule 3: Don't allow form to submit if all the required qty condition is not completed
			if ($allocated_total != $required_total) {
				return simple_json_output(array(
					"status" => 400,
					"message" => "Total allocated quantity (" . $allocated_total . ") for ingredient " . $item['name'] . " must be exactly equal to required quantity (" . $required_total . ")."
				));
			}

			$allocated_ingredients[$ing_id] = $batches_to_process;
		}

		// Calculate expenses
		$total_expense = 0;
		$expense_ids = $this->input->post('charge_expense_id');
		$expense_amounts = $this->input->post('charge_amount');
		if (!empty($expense_ids) && is_array($expense_ids)) {
			for ($j = 0; $j < count($expense_ids); $j++) {
				$exp_id = (int)$expense_ids[$j];
				$exp_amt = floatval($expense_amounts[$j]);
				if ($exp_id > 0 && $exp_amt > 0) {
					$total_expense += $exp_amt;
				}
			}
		}

		// $total_actual_cost is already accumulated in the loop
		$off_cost_pc = ($qty > 0) ? $total_off_cost / $qty : 0.00;
		$black_cost_pc = ($qty > 0) ? $total_black_cost / $qty : 0.00;
		$actual_cost_pc = ($qty > 0) ? $total_actual_cost / $qty : 0.00;
		$final_total = $total_actual_cost + $total_expense;

		$this->db->trans_begin();

		// 6. Insert master record into formula_product_in
		$master_data = array(
			'company_id'        => $company_id,
			'batch_no'          => $batch_no,
			'date'              => $date,
			'product_id'        => $parent_id,
			'name'              => $parent_product['name'],
			'qty'               => $qty,
			'total_off_cost'    => $total_off_cost,
			'off_cost_pc'       => $off_cost_pc,
			'total_black_cost'  => $total_black_cost,
			'black_cost_pc'     => $black_cost_pc,
			'total_actual_cost' => $total_actual_cost,
			'actual_cost_pc'    => $actual_cost_pc,
			'total_expense'     => $total_expense,
			'final_total'       => $final_total,
			'added_by'          => $this->session->userdata('super_user_id'),
			'added_by_name'     => $this->session->userdata('super_name'),
			'added_date'        => date("Y-m-d H:i:s"),
			'remark'            => $this->input->post('remark', true) ? trim($this->input->post('remark', true)) : null
		);
		$this->db->insert('formula_product_in', $master_data);
		$parent_order_id = $this->db->insert_id();

		// 7. Loop through ingredients and batches, deduct stock and log history
		foreach ($allocated_ingredients as $ing_id => $alloc_batches) {
			foreach ($alloc_batches as $alloc) {
				$batch_detail = $alloc['batch_detail'];
				$w_qty = $alloc['white_qty'];
				$b_qty = $alloc['black_qty'];
				$total_qty = $alloc['total_qty'];

				// Update inventory row
				$new_qty = (int)$batch_detail['quantity'] - $total_qty;
				$new_white = (int)$batch_detail['official_qty'] - $w_qty;
				$new_black = (int)$batch_detail['black_qty'] - $b_qty;

				$this->db->where('id', $batch_detail['id'])->update('inventory', array(
					'quantity' => $new_qty,
					'official_qty' => $new_white,
					'black_qty' => $new_black
				));

				$sub_total_off = $w_qty * floatval($batch_detail['official_rate_rs']);
				$sub_total_black = $b_qty * floatval($batch_detail['actual_inr']);
				$sub_actual_cost = floatval($batch_detail['actual_inr']) * ($w_qty + $b_qty);

				// Save items into formula_ingredients_batch_products
				$batch_item = array(
					'parent_id'        => $parent_order_id,
					'product_id'       => $ing_id,
					'product_name'     => $batch_detail['product_name'],
					'batch_id'         => $batch_detail['id'],
					'batch_no'         => $batch_detail['batch_no'],
					'white_qty'        => $w_qty,
					'black_qty'        => $b_qty,
					'total_qty'        => $total_qty,
					'off_cost'         => floatval($batch_detail['official_rate_rs']),
					'total_off_cost'   => $sub_total_off,
					'black_cost'       => floatval($batch_detail['actual_inr']),
					'total_black_cost' => $sub_total_black,
					'actual_cost'      => $sub_actual_cost,
					'created_at'       => date("Y-m-d H:i:s")
				);
				$this->db->insert('formula_ingredients_batch_products', $batch_item);

				// Insert into inventory_history (stock out)
				$history_out = array(
					'supplier_id'       => $batch_detail['supplier_id'],
					'company_id'        => $company_id,
					'parent_id'         => $batch_detail['id'],
					'warehouse_id'      => $warehouse_id,
					'warehouse_name'    => $batch_detail['warehouse_name'],
					'product_id'        => $ing_id,
					'product_name'      => $batch_detail['product_name'],
					'categories'        => $batch_detail['categories'],
					'sku'               => $batch_detail['sku'],
					'item_code'         => $batch_detail['item_code'],
					'order_id'          => $parent_order_id,
					'status'            => 'out',
					'quantity'          => $total_qty,
					'official_qty'      => $w_qty,
					'official_rate_rs'  => floatval($batch_detail['official_rate_rs']),
					'official_total_rs' => $sub_total_off,
					'black_qty'         => $b_qty,
					'black_rate_rs'     => floatval($batch_detail['actual_inr']),
					'black_total_rs'    => $sub_total_black,
					'total_amt'         => $sub_actual_cost,
					'received_date'     => date("Y-m-d"),
					'batch_no'          => $batch_detail['batch_no'],
					'invoice_no'        => $batch_no,
					'is_deleted'        => 0,
					'added_date'        => date("Y-m-d H:i:s"),
					'added_by_id'       => $this->session->userdata('super_user_id'),
					'added_by_name'     => $this->session->userdata('super_name')
				);
				$this->db->insert('inventory_history', $history_out);
			}
		}

		// 8. Save expenses/charges in formula_product_expense
		if (!empty($expense_ids) && is_array($expense_ids)) {
			for ($j = 0; $j < count($expense_ids); $j++) {
				$exp_id = (int)$expense_ids[$j];
				$exp_amt = floatval($expense_amounts[$j]);
				if ($exp_id > 0) {
					$expense = $this->common_model->getRowById('expense_type', 'name', ['id' => $exp_id]);
					$expense_item = array(
						'parent_id'  => $parent_order_id,
						'expense_id' => $exp_id,
						'name'       => $expense ? $expense['name'] : '',
						'amount'     => $exp_amt,
						'created_at' => date("Y-m-d H:i:s")
					);
					$this->db->insert('formula_product_expense', $expense_item);
				}
			}
		}

		// 9. Add stock of produced formula product in inventory
		$parent_inv = array(
			'supplier_id'       => NULL,
			'company_id'        => $company_id,
			'warehouse_id'      => $warehouse_id,
			'warehouse_name'    => $warehouse['name'],
			'product_id'        => $parent_id,
			'product_name'      => $parent_product['name'],
			'categories'        => $parent_product['categories'],
			'sku'               => $parent_product['item_code'],
			'item_code'         => $parent_product['item_code'],
			'quantity'          => $qty,
			'official_qty'      => ($type == 'white') ? $qty : 0,
			'official_rate_rs'  => $off_cost_pc,
			'official_total_rs' => $total_off_cost,
			'actual_inr'        => $actual_cost_pc,
			'black_qty'         => ($type == 'black') ? $qty : 0,
			'total_amt'         => $final_total,
			'batch_no'          => $batch_no
		);
		$this->db->insert('inventory', $parent_inv);
		$parent_inv_id = $this->db->insert_id();

		// 10. Save stock in history for parent product
		$parent_history = array(
			'supplier_id'       => NULL,
			'company_id'        => $company_id,
			'parent_id'         => $parent_inv_id,
			'warehouse_id'      => $warehouse_id,
			'warehouse_name'    => $warehouse['name'],
			'product_id'        => $parent_id,
			'product_name'      => $parent_product['name'],
			'categories'        => $parent_product['categories'],
			'sku'               => $parent_product['item_code'],
			'item_code'         => $parent_product['item_code'],
			'order_id'          => $parent_order_id,
			'status'            => 'in',
			'quantity'          => $qty,
			'official_qty'      => ($type == 'white') ? $qty : 0,
			'official_rate_rs'  => $off_cost_pc,
			'official_total_rs' => $total_off_cost,
			'black_qty'         => ($type == 'black') ? $qty : 0,
			'black_rate_rs'     => $black_cost_pc,
			'black_total_rs'    => $total_black_cost,
			'total_amt'         => $final_total,
			'received_date'     => date("Y-m-d"),
			'batch_no'          => $batch_no,
			'invoice_no'        => $batch_no,
			'is_deleted'        => 0,
			'added_date'        => date("Y-m-d H:i:s"),
			'added_by_id'       => $this->session->userdata('super_user_id'),
			'added_by_name'     => $this->session->userdata('super_name')
		);
		$this->db->insert('inventory_history', $parent_history);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return simple_json_output(array(
				"status" => 400,
				"message" => "Transaction failed. Please try again."
			));
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('flash_message', 'Formula Product stock added successfully.');
		return simple_json_output(array(
			"status" => 200,
			"message" => "Formula Product stock added successfully.",
			"url" => base_url('inventory/formula-product-order')
		));
	}

	public function get_replace_products()
	{
		$draw = intval($this->input->post('draw'));
		$start = intval($this->input->post('start'));
		$length = intval($this->input->post('length'));
		
		$status = $this->input->post('status') ? $this->input->post('status') : 'pending';
		
		$keyword_filter = " AND (rp.type = '" . $status . "')";
		if (isset($_POST['search']['value']) && $_POST['search']['value'] != ""):
			$keyword        = $_POST['search']['value'];
			$keyword_filter .= " AND (rp.product_name LIKE '%" . $keyword . "%'
            OR rp.item_code LIKE '%" . $keyword . "%'
            OR so.order_no LIKE '%" . $keyword . "%'
            OR so.customer_name LIKE '%" . $keyword . "%'
            OR rp2.supplier_name LIKE '%" . $keyword . "%'
            OR su.first_name LIKE '%" . $keyword . "%')";
		endif;

		$total_count = $this->db->query("
			SELECT rp.id 
			FROM replace_products AS rp
			LEFT JOIN sales_order AS so ON so.id = rp.order_id
			LEFT JOIN raw_products AS rp2 ON rp2.id = rp.product_id
			LEFT JOIN sys_users AS su ON su.id = so.sale_person_id
			WHERE 1=1 AND rp.is_deleted = '0' $keyword_filter
			GROUP BY rp.order_id, rp.order_prod_id, rp.type
		")->num_rows();

		$query = $this->db->query("
			SELECT rp.*, so.order_no, so.customer_name, so.date AS order_date, rp2.supplier_name, su.first_name AS salesperson_name
			FROM replace_products AS rp
			LEFT JOIN sales_order AS so ON so.id = rp.order_id
			LEFT JOIN raw_products AS rp2 ON rp2.id = rp.product_id
			LEFT JOIN sys_users AS su ON su.id = so.sale_person_id
			WHERE 1=1 AND rp.is_deleted = '0' $keyword_filter 
			GROUP BY rp.order_id, rp.order_prod_id, rp.type
			ORDER BY rp.created_at DESC 
			LIMIT $start, $length
		");

		$data = array();
		if (!empty($query)) {
			$i = 0;
			foreach ($query->result_array() as $item) {
				$data[] = array(
					"sr_no"          => $start + $i + 1,
					"order_date"     => ($item['order_date'] != '0000-00-00' && $item['order_date'] != null) ? date('d M, Y', strtotime($item['order_date'])) : '-',
					"order_no"       => $item['order_no'] ? $item['order_no'] : '-',
					"customer_name"  => $item['customer_name'] ? $item['customer_name'] : '-',
					"product_name"   => $item['product_name'] ? $item['product_name'] : '-',
					"item_code"      => $item['item_code'] ? $item['item_code'] : '-',
					"qty"            => $item['qty'],
					"supplier_name"  => $item['supplier_name'] ? $item['supplier_name'] : '-',
					"salesperson"    => $item['salesperson_name'] ? $item['salesperson_name'] : '-',
				);
				$i++;
			}
		}

		$json_data = array(
			"draw" => $draw,
			"recordsTotal" => $total_count,
			"recordsFiltered" => $total_count,
			"data" => $data
		);
		echo json_encode($json_data);
	}

	public function get_pending_replace_products_by_supplier()
	{
		$supplier_id = $this->input->post('supplier_id');
		$type = $this->input->post('type'); // normal, bill, etc.
		$po_id = intval($this->input->post('po_id') ?? 0);
		
		$po_condition = "";
		if ($po_id > 0) {
			$po_condition = " OR (rp.type = 'po' AND rp.po_id = '$po_id' AND rp.is_deleted = 0)";
		}
		
		$query = $this->db->query("
			SELECT rp.product_id, SUM(rp.qty) AS total_qty, p.type AS category_type, p.cbm, p.alias, p.hsn_code, p.gst, p.name AS product_name, p.item_code AS product_item_code,
			(SELECT c.name FROM categories c WHERE FIND_IN_SET(c.id, p.categories) > 0 LIMIT 1) as category_name
			FROM replace_products rp
			INNER JOIN raw_products p ON p.id = rp.product_id
			WHERE ((rp.type = 'pending' AND rp.is_deleted = 0) $po_condition) 
			AND (p.supplier_id = '$supplier_id' OR FIND_IN_SET('$supplier_id', p.supplier_id))
			GROUP BY rp.product_id
			ORDER BY p.name ASC
		");
		
		$data = array();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				// Calculate pending PO quantity
				$pending_po_qty = 0;
				$q = $this->db->query("
					SELECT SUM(pop.quantity) AS total_qty
					FROM purchase_order_product pop
					INNER JOIN purchase_order po ON po.id = pop.parent_id
					WHERE po.delivery_status = 'pending' AND po.method = ?
					AND po.is_deleted = 0
					AND pop.product_id = ?
				", array($type, $row['product_id']));
				if ($q->num_rows() > 0) {
					$result = $q->row();
					$pending_po_qty = intval($result->total_qty ?? 0);
				}
				
				$data[] = array(
					'id' => $row['product_id'],
					'name' => $row['product_name'],
					'type' => $row['category_type'],
					'item_code' => $row['product_item_code'] ?? '',
					'category_name' => $row['category_name'] ?? '-',
					'cbm' => $row['cbm'] ?? 0,
					'pending_po_qty' => $pending_po_qty,
					'loading_list_qty' => 0,
					'in_stock_qty' => 0,
					'company_stock' => 0,
					'replace_qty' => $row['total_qty'],
					'replace_id' => $row['product_id']
				);
			}
			
			$res = array(
				"status" => 200,
				"data" => $data
			);
		} else {
			$res = array(
				"status" => 400,
				"data" => array()
			);
		}
		
		echo json_encode($res);
	}

	public function save_sales_order_commissions($order_id)
	{
		$sales_order = $this->db->where('id', $order_id)->get('sales_order')->row_array();
		if (empty($sales_order)) {
			return;
		}

		$customer_id = (int)$sales_order['customer_id'];
		$sale_person_id = (int)$sales_order['sale_person_id'];
		$is_distributor = (int)$sales_order['is_distributor'];

		$customer = $this->db->select('shared_id')->where('id', $customer_id)->get('customer')->row_array();
		$shared_staff_id = isset($customer['shared_id']) ? (int)$customer['shared_id'] : 0;

		$batches = $this->db->where('order_id', $order_id)->get('sales_order_product_batch')->result_array();

		$this->db->where('order_id', $order_id)->delete('sales_commission');

		foreach ($batches as $batch) {
			$order_product_id = (int)$batch['order_product_id'];
			$order_product_batch_id = (int)$batch['id'];

			$sop = $this->db->select('product_id')->where('id', $order_product_id)->get('sales_order_product')->row_array();
			if (empty($sop)) {
				continue;
			}
			$product_id = (int)$sop['product_id'];

			$inventory = $this->db->where('product_id', $product_id)
								  ->where('batch_no', $batch['batch_no'])
								  ->get('inventory')
								  ->row_array();
			if (empty($inventory)) {
				continue;
			}

			$actual_cost_with_exp = (float)$inventory['actual_cost_with_exp'];
			$sale_product_price = (float)$batch['amount'];

			if ($actual_cost_with_exp <= 0 || $sale_product_price < $actual_cost_with_exp) {
				continue;
			}

			$profit_percentage = ($sale_product_price / $actual_cost_with_exp) * 100;

			$raw_prod = $this->db->select('commission_id')->where('id', $product_id)->get('raw_products')->row_array();
			$commission_id = isset($raw_prod['commission_id']) ? (int)$raw_prod['commission_id'] : 0;

			if ($commission_id <= 0) {
				continue;
			}

			$prod_slab = $this->db->where('id', $commission_id)->get('product_commission_slab')->row_array();
			if (empty($prod_slab)) {
				continue;
			}
			$product_comm = (float)$prod_slab['commission'];

			$product_comm_amt = ($batch['amount'] * $batch['qty']) / (1 + ($product_comm / 100));

			$profit_slab = $this->db->where('is_deleted', '0')
									->where('comm_from <=', $profit_percentage)
									->where('comm_to >=', $profit_percentage)
									->get('profit_commission_slab')
									->row_array();
			if (empty($profit_slab)) {
				continue;
			}
			$profit_id = (int)$profit_slab['id'];
			$customer_range = $profit_slab['name'];

			$staff_comm = $this->db->where('staff_id', $sale_person_id)
								   ->where('profit_id', $profit_id)
								   ->where('commission_id', $commission_id)
								   ->get('staff_commission')
								   ->row_array();
			if (empty($staff_comm)) {
				continue;
			}

			$staff_comm_id = (int)$staff_comm['id'];
			$staff_customer_comm = (float)$staff_comm['customer_comm'];
			$staff_distributer_comm = (float)$staff_comm['distributer_comm'];

			$chosen_percentage = ($is_distributor == 1) ? $staff_distributer_comm : $staff_customer_comm;
			$commission_amount = ($batch['amount'] * $batch['qty']) * ($chosen_percentage / (100 + $chosen_percentage));

			if ($commission_amount == 0.00) {
				continue;
			}

			$shared_commission = 0.00;
			$shared_staff_comm_id = 0;
			$my_commission = 100.00;

			if ($shared_staff_id > 0) {
				$cust_comm = $this->db->where('customer_id', $customer_id)
									   ->where('staff_id', $sale_person_id)
									   ->where('shared_staff_id', $shared_staff_id)
									   ->where('profit_id', $profit_id)
									   ->where('commission_id', $commission_id)
									   ->get('customer_commission')
									   ->row_array();
				if (!empty($cust_comm)) {
					$shared_commission = (float)$cust_comm['shared_commission'];
					$my_commission = (float)$cust_comm['my_commission'];

					$shared_staff_comm = $this->db->where('staff_id', $shared_staff_id)
												   ->where('profit_id', $profit_id)
												   ->where('commission_id', $commission_id)
												   ->get('staff_commission')
												   ->row_array();
					if (!empty($shared_staff_comm)) {
						$shared_staff_comm_id = (int)$shared_staff_comm['id'];
					}
				}
			}

			$sales_comm_data = array(
				'order_id'               => $order_id,
				'order_product_id'       => $order_product_id,
				'order_product_batch_id' => $order_product_batch_id,
				'product_id'             => $product_id,
				'commission_id'          => $commission_id,
				'product_comm'           => $product_comm,
				'product_comm_amt'       => $product_comm_amt,
				'staff_id'               => $sale_person_id,
				'staff_comm_id'          => $staff_comm_id,
				'customer_range'         => $customer_range,
				'customer_comm'          => $staff_customer_comm,
				'distributer_comm'       => $staff_distributer_comm,
				'commission_amount'      => $commission_amount,
				'share_staff_id'         => $shared_staff_id,
				'shared_staff_comm_id'   => $shared_staff_comm_id,
				'shared_commission'      => $shared_commission,
				'my_commission'          => $my_commission,
				'is_paid'                => 0,
				'created_at'             => date("Y-m-d H:i:s")
			);

			$this->db->insert('sales_commission', $sales_comm_data);
		}
	}

	public function add_customer_call()
	{
		$resultpost = array(
			"status" => 200,
			"message" => "Customer Call Added Successfully",
			"url" => $this->session->userdata('previous_url') ? $this->session->userdata('previous_url') : site_url('inventory/customer_calls'),
		);

		$customer_id = $this->input->post('customer_id', true);
		$date        = $this->input->post('date', true);
		$remark      = $this->input->post('remark', true);

		if (empty($customer_id) || empty($date)) {
			$resultpost['status'] = 400;
			$resultpost['message'] = "Please select customer and follow up date.";
			echo json_encode($resultpost);
			exit;
		}

		$customer_row = $this->db->where('id', $customer_id)->get('customer')->row_array();
		$customer_name = $customer_row ? $customer_row['company_name'] : '';
		$is_distributor = $customer_row ? (int)$customer_row['is_distributor'] : 0;

		$status_date_val = date('Y-m-d H:i:s', strtotime($date));

		$data = array(
			'customer_id'    => $customer_id,
			'customer_name'  => $customer_name,
			'is_distributor' => $is_distributor,
			'is_lead'        => 0,
			'date'           => $status_date_val,
			'status'				 => "Needs Follow Up",
			'remark'         => $remark,
			'added_by'       => $this->session->userdata('super_user_id'),
			'added_by_name'  => $this->session->userdata('super_name'),
			'created_at'     => date("Y-m-d H:i:s")
		);

		$this->db->insert('customer_calls', $data);

		// Update customer data
		$cust_update = array(
			'status'       => 'stalking',
			'status_label' => 'Follow up',
			'status_date'  => $status_date_val,
			'remark'       => $remark
		);
		$this->db->where('id', $customer_id)->update('customer', $cust_update);

		$user_id   = $this->session->userdata('super_user_id');
		$user_name = $this->session->userdata('super_name');

		$json_data = [
			'status_date'  => $status_date_val,
			'status'       => 'stalking',
			'status_label' => 'Follow up',
			'remark'       => $remark,
		];

		$logs = [
			"customer_id"     => $customer_id,
			"action"          => 'stalking',
			"label"           => json_encode(["badge" => "warning", "message" => "Follow Up Added"]),
			"message"         => "Follow Up Added by {$user_name}",
			"json"            => json_encode($json_data),
			"added_by"        => $user_id,
			"added_by_name"   => get_phrase($user_name),
			"added_date"      => date("Y-m-d H:i:s"),
		];

		$this->db->insert('customer_log', $logs);

		$this->session->set_flashdata('flash_message', "Customer Call Added Successfully !!");
		echo json_encode($resultpost);
		exit;
	}

	public function get_customer_calls()
	{
		$draw   = isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 1;
		$start  = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
		$length = isset($_REQUEST['length']) ? (int)$_REQUEST['length'] : 10;

		$search_value = isset($_REQUEST['search']['value']) ? clean_and_escape($_REQUEST['search']['value']) : '';
		$filter_date  = isset($_REQUEST['date']) ? clean_and_escape($_REQUEST['date']) : '';

		if (!empty($filter_date) && strtotime($filter_date) !== false) {
			$date_formatted = date('Y-m-d', strtotime($filter_date));
			$where_sql = "WHERE DATE(cc.created_at) = '" . $date_formatted . "'";
		} else {
			$where_sql = "WHERE DATE(cc.created_at) = CURDATE()";
		}

		$user_id = $this->session->userdata('super_user_id');
		$staff_access = (int)$this->session->userdata('super_type_id');
		if ($staff_access == 7) {
			$where_sql .= " AND cc.added_by = '" . $user_id . "'";
		}

		if (!empty($search_value)) {
			$where_sql .= " AND (cc.customer_name LIKE '%" . $search_value . "%' OR cc.remark LIKE '%" . $search_value . "%' OR cc.status LIKE '%" . $search_value . "%' OR cc.added_by_name LIKE '%" . $search_value . "%' OR c.company_name LIKE '%" . $search_value . "%' OR c.owner_mobile LIKE '%" . $search_value . "%')";
		}

		$total_count = $this->db->query("SELECT cc.id FROM customer_calls AS cc LEFT JOIN customer c ON cc.customer_id = c.id $where_sql")->num_rows();

		$limit_sql = "";
		if ($length != -1) {
			$limit_sql = " LIMIT $start, $length";
		}

		$query = $this->db->query("SELECT cc.*, c.company_name, c.owner_mobile FROM customer_calls AS cc LEFT JOIN customer c ON cc.customer_id = c.id $where_sql ORDER BY cc.id DESC $limit_sql");
		$result = $query->result_array();

		$data = array();
		foreach ($result as $row) {
			$type_badge = ($row['is_lead'] == 1) 
				? '<span class="badge bg-light-warning text-warning" style="background: #ff9f4330 !important;">Leads</span>' 
				: '<span class="badge bg-light-primary text-primary">Customer</span>';

			$nested_data = array();
			$nested_data['added_by_name'] = !empty($row['added_by_name']) ? htmlspecialchars($row['added_by_name']) : '-';
			$nested_data['company_name']  = !empty($row['company_name']) ? htmlspecialchars($row['company_name']) : '-';
			$nested_data['customer_name'] = htmlspecialchars($row['customer_name']);
			$nested_data['phone_number']  = !empty($row['owner_mobile']) ? htmlspecialchars($row['owner_mobile']) : '-';
			$nested_data['type']          = $type_badge;
			$nested_data['status']        = !empty($row['status']) ? htmlspecialchars($row['status']) : '-';
			$nested_data['remark']        = !empty($row['remark']) ? nl2br(htmlspecialchars($row['remark'])) : '-';
			$nested_data['date']          = !empty($row['date']) ? date('d M, Y h:i A', strtotime($row['date'])) : '-';
			$nested_data['created_at']    = !empty($row['created_at']) ? date('d M, Y h:i A', strtotime($row['created_at'])) : '-';
			$data[] = $nested_data;
		}

		$json_data = array(
			"draw"            => intval($draw),
			"recordsTotal"    => intval($total_count),
			"recordsFiltered" => intval($total_count),
			"data"            => $data
		);

		echo json_encode($json_data);
		exit;
	}

	public function get_dashboard_stats()
	{
		$user_id      = $this->session->userdata('super_user_id');
		$type         = $this->session->userdata('super_type');
		$company_id   = $this->session->userdata('company_id');
		$staff_access = (int)$this->session->userdata('super_type_id');

		$staff_where = "";
		if ($company_id && $type == 'staff') {
			$staff_where = " AND FIND_IN_SET('" . $company_id . "', c.company_id) AND c.added_by_id = '" . $user_id . "'";
		} elseif ($staff_access == 7) {
			$staff_where = " AND c.added_by_id = '" . $user_id . "'";
		}

		// Orders stats based on latest sales_order date per customer
		$order_sql = "SELECT 
			COUNT(CASE WHEN days IS NOT NULL AND days BETWEEN 0 AND 30 THEN 1 END) as orders_0_30,
			COUNT(CASE WHEN days IS NOT NULL AND days BETWEEN 31 AND 60 THEN 1 END) as orders_31_60,
			COUNT(CASE WHEN days IS NOT NULL AND days BETWEEN 61 AND 90 THEN 1 END) as orders_61_90,
			COUNT(CASE WHEN days IS NOT NULL AND days > 90 THEN 1 END) as orders_90_plus,
			COUNT(CASE WHEN days IS NULL THEN 1 END) as no_orders
		FROM (
			SELECT 
				c.id,
				DATEDIFF(CURDATE(), MAX(DATE(so.date))) as days
			FROM customer c
			LEFT JOIN sales_order so ON so.customer_id = c.id AND (so.is_deleted = '0' OR so.is_deleted IS NULL)
			WHERE (c.is_deleted = '0' OR c.is_deleted IS NULL) AND c.type = 'customer' $staff_where
			GROUP BY c.id
		) as customer_orders";

		$orders_res = $this->db->query($order_sql)->row_array();

		// Calls stats based on latest customer_calls date per customer
		$call_sql = "SELECT 
			COUNT(CASE WHEN days IS NOT NULL AND days BETWEEN 0 AND 30 THEN 1 END) as calls_0_30,
			COUNT(CASE WHEN days IS NOT NULL AND days BETWEEN 31 AND 60 THEN 1 END) as calls_31_60,
			COUNT(CASE WHEN days IS NOT NULL AND days BETWEEN 61 AND 90 THEN 1 END) as calls_61_90,
			COUNT(CASE WHEN days IS NOT NULL AND days > 90 THEN 1 END) as calls_90_plus,
			COUNT(CASE WHEN days IS NULL THEN 1 END) as no_calls
		FROM (
			SELECT 
				c.id,
				DATEDIFF(CURDATE(), MAX(DATE(cc.date))) as days
			FROM customer c
			LEFT JOIN customer_calls cc ON cc.customer_id = c.id
			WHERE (c.is_deleted = '0' OR c.is_deleted IS NULL) AND c.type = 'customer' $staff_where
			GROUP BY c.id
		) as customer_calls_summary";

		$calls_res = $this->db->query($call_sql)->row_array();

		return array(
			'orders_0_30'   => isset($orders_res['orders_0_30']) ? (int)$orders_res['orders_0_30'] : 0,
			'orders_31_60'  => isset($orders_res['orders_31_60']) ? (int)$orders_res['orders_31_60'] : 0,
			'orders_61_90'  => isset($orders_res['orders_61_90']) ? (int)$orders_res['orders_61_90'] : 0,
			'orders_90_plus' => isset($orders_res['orders_90_plus']) ? (int)$orders_res['orders_90_plus'] : 0,
			'no_orders'     => isset($orders_res['no_orders']) ? (int)$orders_res['no_orders'] : 0,
			'calls_0_30'    => isset($calls_res['calls_0_30']) ? (int)$calls_res['calls_0_30'] : 0,
			'calls_31_60'   => isset($calls_res['calls_31_60']) ? (int)$calls_res['calls_31_60'] : 0,
			'calls_61_90'   => isset($calls_res['calls_61_90']) ? (int)$calls_res['calls_61_90'] : 0,
			'calls_90_plus'  => isset($calls_res['calls_90_plus']) ? (int)$calls_res['calls_90_plus'] : 0,
			'no_calls'      => isset($calls_res['no_calls']) ? (int)$calls_res['no_calls'] : 0,
		);
	}

	public function get_customer_history_ajax()
	{
		$customer_id = (int)$this->input->post('customer_id');
		if (empty($customer_id)) {
			echo '';
			exit;
		}

		$customer_history = $this->db->where('customer_id', $customer_id)
			->order_by('id', 'DESC')
			->limit(50)
			->get('customer_log')
			->result_array();

		if (empty($customer_history)) {
			echo '<div class="text-center text-muted p-1"><small>No history records found.</small></div>';
			exit;
		}

		$output = '<style>
		  .history-item:last-child{ margin-bottom: 0; }
		  .history-card{
			border: 1px solid #edf0f2;
			border-radius: 6px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.03);
			overflow: hidden;
			margin-bottom: 8px !important;
		  }
		  .history-card .card-body{ padding: 10px 12px; }
		  .history-meta{ font-size: 11px; color: #6c757d; }
		  .history-title{ font-weight: 600; color: #111827; font-size: 12px; margin: 2px 0 4px; }
		  .history-pill{ font-size: 10px; padding: 3px 7px; border-radius: 999px; }
		</style>';

		foreach ($customer_history as $history) {
			$json = [];
			$label = [];
			if ($history['json']) {
				$json = json_decode($history['json'], true);
			}
			if ($history['label']) {
				$label = json_decode($history['label'], true);
			}

			$badge = isset($label['badge']) ? $label['badge'] : 'primary';
			$message = isset($label['message']) ? $label['message'] : '';
			$time_formatted = function_exists('formatHistoryTime') ? formatHistoryTime($history['added_date']) : $history['added_date'];

			$output .= '<div class="history-item">';
			$output .= '<div class="card history-card">';
			$output .= '<div class="card-body">';
			$output .= '<div class="d-flex justify-content-between align-items-center mb-1">';
			$output .= '<span class="badge bg-' . $badge . ' history-pill">' . htmlspecialchars($message) . '</span>';
			$output .= '<small class="history-meta">' . $time_formatted . '</small>';
			$output .= '</div>';

			if ($history['action'] == "create" || $history['action'] == "follow" || $history['action'] == "lost") {
				$output .= '<div class="history-title">Added By: <span class="text-primary">' . htmlspecialchars($history['added_by_name']) . '</span></div>';
			} elseif ($history['action'] == "reassign" || $history['action'] == "update") {
				$output .= '<div class="history-title">Updated By: <span class="text-primary">' . htmlspecialchars($history['added_by_name']) . '</span></div>';
			} elseif ($history['action'] == "assign") {
				$assigned_name = isset($json['added_by_name']) ? $json['added_by_name'] : '';
				$output .= '<div class="history-title">Assigned To: <span class="text-primary">' . htmlspecialchars($assigned_name) . '</span></div>';
			} elseif ($history['action'] == "move") {
				$output .= '<div class="history-title">Moved By: <span class="text-primary">' . htmlspecialchars($history['added_by_name']) . '</span></div>';
			}

			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';
		}

		echo $output;
		exit;
	}

	public function get_customer_report()
	{
		$draw        = isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 1;
		$start       = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
		$length      = isset($_REQUEST['length']) ? (int)$_REQUEST['length'] : 10;
		$search_val  = isset($_REQUEST['search']['value']) ? clean_and_escape($_REQUEST['search']['value']) : '';

		$report_type = isset($_REQUEST['report_type']) ? clean_and_escape($_REQUEST['report_type']) : 'orders';
		$duration    = isset($_REQUEST['duration']) ? clean_and_escape($_REQUEST['duration']) : '0_30';

		$user_id      = $this->session->userdata('super_user_id');
		$type         = $this->session->userdata('super_type');
		$company_id   = $this->session->userdata('company_id');
		$staff_access = (int)$this->session->userdata('super_type_id');

		$staff_where = "";
		if ($company_id && $type == 'staff') {
			$staff_where = " AND FIND_IN_SET('" . $company_id . "', c.company_id) AND c.added_by_id = '" . $user_id . "'";
		} elseif ($staff_access == 7) {
			$staff_where = " AND c.added_by_id = '" . $user_id . "'";
		}

		if ($report_type == 'calls') {
			$having_clause = "";
			switch ($duration) {
				case '0_30':
					$having_clause = "HAVING days IS NOT NULL AND days BETWEEN 0 AND 30";
					break;
				case '31_60':
					$having_clause = "HAVING days IS NOT NULL AND days BETWEEN 31 AND 60";
					break;
				case '61_90':
					$having_clause = "HAVING days IS NOT NULL AND days BETWEEN 61 AND 90";
					break;
				case '90_plus':
					$having_clause = "HAVING days IS NOT NULL AND days > 90";
					break;
				case 'no_calls':
				default:
					$having_clause = "HAVING days IS NULL";
					break;
			}

			$search_where = "";
			if (!empty($search_val)) {
				$search_where = " AND (c.company_name LIKE '%{$search_val}%' OR c.owner_name LIKE '%{$search_val}%' OR c.owner_mobile LIKE '%{$search_val}%' OR c.gst_name LIKE '%{$search_val}%' OR c.pincode LIKE '%{$search_val}%')";
			}

			$base_sql = "FROM (
				SELECT 
					c.id, c.company_name, c.owner_name, c.owner_mobile, c.owner_email, c.gst_name, c.gst_no, c.pincode, c.added_by_name,
					MAX(DATE(cc.date)) as last_date,
					DATEDIFF(CURDATE(), MAX(DATE(cc.date))) as days
				FROM customer c
				LEFT JOIN customer_calls cc ON cc.customer_id = c.id
				WHERE (c.is_deleted = '0' OR c.is_deleted IS NULL) AND c.type = 'customer' {$staff_where} {$search_where}
				GROUP BY c.id
				{$having_clause}
			) as report_data";
		} else {
			// Orders
			$having_clause = "";
			switch ($duration) {
				case '0_30':
					$having_clause = "HAVING days IS NOT NULL AND days BETWEEN 0 AND 30";
					break;
				case '31_60':
					$having_clause = "HAVING days IS NOT NULL AND days BETWEEN 31 AND 60";
					break;
				case '61_90':
					$having_clause = "HAVING days IS NOT NULL AND days BETWEEN 61 AND 90";
					break;
				case '90_plus':
					$having_clause = "HAVING days IS NOT NULL AND days > 90";
					break;
				case 'no_orders':
				default:
					$having_clause = "HAVING days IS NULL";
					break;
			}

			$search_where = "";
			if (!empty($search_val)) {
				$search_where = " AND (c.company_name LIKE '%{$search_val}%' OR c.owner_name LIKE '%{$search_val}%' OR c.owner_mobile LIKE '%{$search_val}%' OR c.gst_name LIKE '%{$search_val}%' OR c.pincode LIKE '%{$search_val}%')";
			}

			$base_sql = "FROM (
				SELECT 
					c.id, c.company_name, c.owner_name, c.owner_mobile, c.owner_email, c.gst_name, c.gst_no, c.pincode, c.added_by_name,
					MAX(DATE(so.date)) as last_date,
					DATEDIFF(CURDATE(), MAX(DATE(so.date))) as days
				FROM customer c
				LEFT JOIN sales_order so ON so.customer_id = c.id AND (so.is_deleted = '0' OR so.is_deleted IS NULL)
				WHERE (c.is_deleted = '0' OR c.is_deleted IS NULL) AND c.type = 'customer' {$staff_where} {$search_where}
				GROUP BY c.id
				{$having_clause}
			) as report_data";
		}

		$total_count_res = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row_array();
		$total_count     = isset($total_count_res['cnt']) ? (int)$total_count_res['cnt'] : 0;

		$limit_sql = "";
		if ($length != -1) {
			$limit_sql = " LIMIT {$start}, {$length}";
		}

		$query  = $this->db->query("SELECT * {$base_sql} ORDER BY id DESC {$limit_sql}");
		$result = $query->result_array();

		$data = array();
		$sr   = $start + 1;
		foreach ($result as $row) {
			$nested_data = array();
			$nested_data['sr_no']          = $sr++;
			$nested_data['company_name']   = htmlspecialchars($row['company_name']);
			$nested_data['contact_person'] = !empty($row['owner_name']) ? htmlspecialchars($row['owner_name']) : '-';
			$nested_data['mobile']         = !empty($row['owner_mobile']) ? htmlspecialchars($row['owner_mobile']) : '-';
			$nested_data['last_date']      = !empty($row['last_date']) ? date('d M, Y', strtotime($row['last_date'])) : '-';
			$nested_data['days']           = ($row['days'] !== null) ? $row['days'] . ' Days' : '-';
			$nested_data['gst_name']       = !empty($row['gst_name']) ? htmlspecialchars($row['gst_name']) : '-';
			$nested_data['pincode']        = !empty($row['pincode']) ? htmlspecialchars($row['pincode']) : '-';
			$nested_data['added_by_name']  = !empty($row['added_by_name']) ? htmlspecialchars($row['added_by_name']) : '-';
			
			$data[] = $nested_data;
		}

		$json_data = array(
			"draw"            => intval($draw),
			"recordsTotal"    => intval($total_count),
			"recordsFiltered" => intval($total_count),
			"data"            => $data
		);

		echo json_encode($json_data);
		exit;
	}

	public function add_customer_direct_call()
	{
		$user_id   = (int) $this->session->userdata('super_user_id');
		$user_name = (string) $this->session->userdata('super_name');

		$resultpost = array(
			"status"  => 200,
			"message" => get_phrase('call_added_successfully'),
			"url"     => $this->session->userdata('previous_url'),
		);

		$customer_id = clean_and_escape($this->input->post('customer_id'));
		$remark      = clean_and_escape($this->input->post('remark'));

		if (empty($customer_id)) {
			$resultpost['status']  = 400;
			$resultpost['message'] = "Invalid customer selected.";
			echo json_encode($resultpost);
			exit;
		}

		$customer_row   = $this->db->where('id', $customer_id)->get('customer')->row_array();
		$customer_name  = $customer_row ? $customer_row['company_name'] : '';
		$is_distributor = $customer_row ? (int)$customer_row['is_distributor'] : 0;
		$is_lead        = ($customer_row && isset($customer_row['type']) && $customer_row['type'] == 'leads') ? 1 : 0;

		$call_data = array(
			'customer_id'    => $customer_id,
			'customer_name'  => $customer_name,
			'is_distributor' => $is_distributor,
			'is_lead'        => $is_lead,
			'status'         => 'Call Added',
			'date'           => date('Y-m-d H:i:s'),
			'remark'         => $remark,
			'added_by'       => $user_id,
			'added_by_name'  => $user_name,
			'created_at'     => date("Y-m-d H:i:s")
		);

		$this->db->insert('customer_calls', $call_data);

		// Update customer status to ""
		$data = array(
			'status'       => '',
			'status_label' => '',
			'status_date'  => NULL,
			'remark'       => $remark,
		);

		$this->db->where('id', $customer_id);
		$updated = $this->db->update('customer', $data);

		if ($updated) {
			$action  = 'call';
			$message = "Call Added by {$user_name}";
			$json_data = [
				'status_date'  => '',
				'status'       => '',
				'status_label' => '',
				'remark'       => $remark,
			];

			$logs = [
				"customer_id"     => $customer_id,
				"action"          => $action,
				"label"           => json_encode(["badge" => "info", "message" => "Call Added"]),
				"message"         => $message,
				"json"            => json_encode($json_data),
				"added_by"        => $user_id,
				"added_by_name"   => get_phrase($user_name),
				"added_date"      => date("Y-m-d H:i:s"),
			];

			$this->db->insert('customer_log', $logs);
		}

		$this->session->set_flashdata('flash_message', get_phrase('call_added_successfully'));
		return simple_json_output($resultpost);
	}

	/* Supplier Adjustment Methods */

	public function get_import_suppliers()
	{
		$company_id = $this->session->userdata('company_id');
		$this->db->where('is_deleted', '0');
		$this->db->where('type', 'import');
		if ($company_id) {
			$this->db->where('company_id', $company_id);
		}
		$this->db->order_by('name', 'ASC');
		return $this->db->get('supplier')->result_array();
	}

	public function add_supplier_adjustment()
	{
		$company_id = $this->session->userdata('company_id');
		$user_id = $this->session->userdata('super_user_id');
		$user_name = $this->session->userdata('super_name');

		$supplier_id = clean_and_escape($this->input->post('supplier_id'));
		$batch_no = clean_and_escape($this->input->post('batch_no'));
		$date = clean_and_escape($this->input->post('date'));
		$rmb = clean_and_escape($this->input->post('rmb'));
		$usd = clean_and_escape($this->input->post('usd'));
		$inr = clean_and_escape($this->input->post('inr'));
		$amt_type = clean_and_escape($this->input->post('amt_type'));
		$type = clean_and_escape($this->input->post('type'));
		$remark = clean_and_escape($this->input->post('remark'));

		$supplier = $this->db->get_where('supplier', array('id' => $supplier_id))->row_array();
		$supplier_name = isset($supplier['name']) ? $supplier['name'] : '';

		$po = $this->db->get_where('purchase_order', array('voucher_no' => $batch_no, 'is_deleted' => 0))->row_array();
		$batch_id = isset($po['id']) ? $po['id'] : NULL;

		$data = array(
			'company_id'    => $company_id ? $company_id : 0,
			'supplier_id'   => $supplier_id,
			'supplier_name' => $supplier_name,
			'batch_id'      => $batch_id,
			'batch_no'      => $batch_no,
			'date'          => $date ? $date : date('Y-m-d'),
			'rmb'           => !empty($rmb) ? $rmb : 0.00,
			'usd'           => !empty($usd) ? $usd : 0.00,
			'inr'           => !empty($inr) ? $inr : 0.00,
			'amt_type'      => $amt_type,
			'type'          => $type,
			'remark'        => $remark,
			'is_deleted'    => 0,
			'added_by'      => $user_name,
			'added_by_id'   => $user_id,
			'created_at'    => date('Y-m-d H:i:s'),
			'updated_at'    => date('Y-m-d H:i:s')
		);

		$this->db->insert('supplier_adjustments', $data);
		$this->session->set_flashdata('flash_message', 'Supplier Adjustment Added Successfully');
		redirect(site_url('inventory/supplier-adjustment'), 'refresh');
	}

	public function edit_supplier_adjustment($id)
	{
		$supplier_id = clean_and_escape($this->input->post('supplier_id'));
		$batch_no = clean_and_escape($this->input->post('batch_no'));
		$date = clean_and_escape($this->input->post('date'));
		$rmb = clean_and_escape($this->input->post('rmb'));
		$usd = clean_and_escape($this->input->post('usd'));
		$inr = clean_and_escape($this->input->post('inr'));
		$amt_type = clean_and_escape($this->input->post('amt_type'));
		$type = clean_and_escape($this->input->post('type'));
		$remark = clean_and_escape($this->input->post('remark'));

		$supplier = $this->db->get_where('supplier', array('id' => $supplier_id))->row_array();
		$supplier_name = isset($supplier['name']) ? $supplier['name'] : '';

		$po = $this->db->get_where('purchase_order', array('voucher_no' => $batch_no, 'is_deleted' => 0))->row_array();
		$batch_id = isset($po['id']) ? $po['id'] : NULL;

		$data = array(
			'supplier_id'   => $supplier_id,
			'supplier_name' => $supplier_name,
			'batch_id'      => $batch_id,
			'batch_no'      => $batch_no,
			'date'          => $date,
			'rmb'           => !empty($rmb) ? $rmb : 0.00,
			'usd'           => !empty($usd) ? $usd : 0.00,
			'inr'           => !empty($inr) ? $inr : 0.00,
			'amt_type'      => $amt_type,
			'type'          => $type,
			'remark'        => $remark,
			'updated_at'    => date('Y-m-d H:i:s')
		);

		$this->db->where('id', $id);
		$this->db->update('supplier_adjustments', $data);
		$this->session->set_flashdata('flash_message', 'Supplier Adjustment Updated Successfully');
		redirect(site_url('inventory/supplier-adjustment'), 'refresh');
	}

	public function delete_supplier_adjustment($id)
	{
		$this->db->where('id', $id);
		$this->db->update('supplier_adjustments', array('is_deleted' => 1));
		$this->session->set_flashdata('flash_message', 'Supplier Adjustment Deleted Successfully');
		redirect(site_url('inventory/supplier-adjustment'), 'refresh');
	}

	public function get_supplier_adjustment_by_id($id)
	{
		return $this->db->get_where('supplier_adjustments', array('id' => $id, 'is_deleted' => 0))->row_array();
	}

	public function get_supplier_adjustment_datatable()
	{
		$draw = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 1;
		$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
		$length = isset($_REQUEST['length']) ? intval($_REQUEST['length']) : 10;

		$search_val = isset($_REQUEST['search']['value']) ? clean_and_escape($_REQUEST['search']['value']) : '';
		$where = "sa.is_deleted = '0'";

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$where .= " AND sa.company_id = '" . $company_id . "'";
		}

		if ($search_val != '') {
			$where .= " AND (sa.supplier_name LIKE '%" . $search_val . "%' OR sa.batch_no LIKE '%" . $search_val . "%' OR sa.remark LIKE '%" . $search_val . "%' OR sa.amt_type LIKE '%" . $search_val . "%' OR sa.type LIKE '%" . $search_val . "%')";
		}

		$total_count = $this->db->query("SELECT sa.id FROM supplier_adjustments sa WHERE $where")->num_rows();

		$query = $this->db->query("SELECT 
										sa.*,
										CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
									FROM supplier_adjustments sa
									LEFT JOIN sys_users u ON sa.added_by = u.id
									WHERE $where
									ORDER BY sa.id DESC
									LIMIT $start, $length");

		$data = array();
		if ($query) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$edit_url = base_url() . 'inventory/edit-supplier-adjustment/' . $id;
				$delete_url = "confirm_modal('" . base_url() . "inventory/supplier-adjustment/delete/" . $id . "','Are you sure want to delete this adjustment!')";

				$action = '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
				$action .= '<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$amt_type_badge = ($item['amt_type'] == 'plus') 
					? '<span class="badge bg-success" style="font-size:11px;">Plus (+)</span>' 
					: '<span class="badge bg-danger" style="font-size:11px;">Minus (-)</span>';

				$type_badge = ($item['type'] == 'official')
					? '<span class="badge bg-info" style="font-size:11px;">Official</span>'
					: '<span class="badge bg-secondary" style="font-size:11px;">Unofficial</span>';

				$data[] = array(
					"sr_no"         => ++$start,
					"id"            => $item['id'],
					"date"          => date('d M Y', strtotime($item['date'])),
					"supplier_name" => html_escape($item['supplier_name']),
					"batch_no"      => html_escape($item['batch_no'] ? $item['batch_no'] : '—'),
					"rmb"           => number_format((float)$item['rmb'], 2),
					"usd"           => number_format((float)$item['usd'], 2),
					"inr"           => number_format((float)$item['inr'], 2),
					"amt_type"      => $amt_type_badge,
					"type"          => $type_badge,
					"remark"        => html_escape($item['remark'] ? $item['remark'] : '—'),
					"added_by"      => html_escape($item['added_by_name'] ? $item['added_by_name'] : '—'),
					"action"        => $action,
				);
			}
		}

		$json_data = array(
			"draw"            => $draw,
			"recordsTotal"    => $total_count,
			"recordsFiltered" => $total_count,
			"data"            => $data
		);
		echo json_encode($json_data);
	}

	public function get_batches_by_supplier_for_select($supplier_id)
	{
		$query = $this->db->query("SELECT DISTINCT po.voucher_no
									FROM purchase_order po
									LEFT JOIN purchase_in_product pp ON po.id = pp.parent_id
									LEFT JOIN po_products pop ON po.id = pop.parent_id
									WHERE (pp.supplier_id = '$supplier_id' OR pop.supplier_id = '$supplier_id' OR po.supplier_id = '$supplier_id')
									AND po.delivery_status = 'purchase_in'
									AND po.is_deleted = '0'
									AND po.voucher_no IS NOT NULL AND po.voucher_no != ''
									ORDER BY po.voucher_no ASC");
		return $query->result_array();
	}

	public function get_supplier_batch_ledger_details($supplier_id, $batch_no, $type = 'unofficial', $current_adj_id = null)
	{
		$query_batch = $this->db->query("SELECT 
											po.id as po_id,
											po.voucher_no,
											po.date as po_date,
											SUM(pp.actual_qty * pp.unit_price_rmb) as total_actual_rmb,
											SUM(pp.actual_qty * pp.actual_usd) as total_actual_usd,
											SUM(pp.actual_qty * pp.actual_inr) as total_actual_inr,
											SUM(pp.total_amount_usd) as official_usd,
											SUM(pp.official_total_rs) as official_inr
										FROM purchase_order po
										JOIN purchase_in_product pp ON po.id = pp.parent_id
										WHERE pp.supplier_id = '$supplier_id'
										AND po.voucher_no = " . $this->db->escape($batch_no) . "
										AND po.delivery_status = 'purchase_in'
										AND po.is_deleted = '0'
										GROUP BY po.id, po.voucher_no, po.date");

		$batch_row = $query_batch->row_array();

		$batch_amount = array(
			'rmb' => 0.00,
			'usd' => 0.00,
			'inr' => 0.00
		);

		if (!empty($batch_row)) {
			if ($type == 'official') {
				$batch_amount['rmb'] = 0.00;
				$batch_amount['usd'] = (float)$batch_row['official_usd'];
				$batch_amount['inr'] = (float)$batch_row['official_inr'];
			} else {
				$batch_amount['rmb'] = (float)$batch_row['total_actual_rmb'];
				$batch_amount['usd'] = (float)$batch_row['total_actual_usd'];
				$batch_amount['inr'] = (float)$batch_row['total_actual_inr'];
			}
		}

		$payment_where = "p.supplier_id = '$supplier_id' AND p.batch_no = " . $this->db->escape($batch_no) . " AND p.is_delete = 0";
		if ($type == 'official') {
			$payment_where .= " AND p.payment_type = 'official'";
		}

		$query_payments = $this->db->query("SELECT 
												p.*,
												CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
											FROM payments p
											LEFT JOIN sys_users u ON p.added_by = u.id
											WHERE $payment_where
											ORDER BY p.payment_date ASC, p.id ASC");

		$payments = $query_payments->result_array();

		$payment_totals = array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00);
		foreach ($payments as $pay) {
			$payment_totals['rmb'] += (float)$pay['amount_rmb'];
			$payment_totals['usd'] += (float)$pay['amount_dollar'];
			$payment_totals['inr'] += (float)$pay['amount_rs'];
		}

		$adj_where = "sa.supplier_id = '$supplier_id' AND sa.batch_no = " . $this->db->escape($batch_no) . " AND sa.is_deleted = 0";
		if ($current_adj_id) {
			$adj_where .= " AND sa.id != " . intval($current_adj_id);
		}

		$query_adjustments = $this->db->query("SELECT 
													sa.*,
													CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
												FROM supplier_adjustments sa
												LEFT JOIN sys_users u ON sa.added_by = u.id
												WHERE $adj_where
												ORDER BY sa.date ASC, sa.id ASC");

		$adjustments = $query_adjustments->result_array();

		$adj_totals = array(
			'plus'  => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
			'minus' => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
		);

		foreach ($adjustments as $adj) {
			$amt_t = $adj['amt_type'];
			if ($amt_t == 'plus' || $amt_t == 'minus') {
				$adj_totals[$amt_t]['rmb'] += (float)$adj['rmb'];
				$adj_totals[$amt_t]['usd'] += (float)$adj['usd'];
				$adj_totals[$amt_t]['inr'] += (float)$adj['inr'];
			}
		}

		$net_difference = array(
			'rmb' => $batch_amount['rmb'] - $payment_totals['rmb'] + $adj_totals['plus']['rmb'] - $adj_totals['minus']['rmb'],
			'usd' => $batch_amount['usd'] - $payment_totals['usd'] + $adj_totals['plus']['usd'] - $adj_totals['minus']['usd'],
			'inr' => $batch_amount['inr'] - $payment_totals['inr'] + $adj_totals['plus']['inr'] - $adj_totals['minus']['inr'],
		);

		return array(
			'batch_info'     => $batch_row,
			'batch_amount'   => $batch_amount,
			'payments'       => $payments,
			'payment_totals' => $payment_totals,
			'adjustments'    => $adjustments,
			'adj_totals'     => $adj_totals,
			'net_difference' => $net_difference
		);
	}

	public function get_supplier_ledger_summary($supplier_id, $type = 'unofficial', $current_adj_id = null)
	{
		$supplier = $this->get_supplier_by_id($supplier_id)->row_array();
		if (empty($supplier)) {
			return null;
		}

		$is_official = ($type === 'official');
		$show_rmb    = !$is_official;

		$opening = array(
			'rmb' => (float)($supplier['outstanding_rmb'] ?? 0),
			'usd' => (float)($supplier['outstanding_usd'] ?? 0),
			'inr' => (float)($supplier['outstanding_inr'] ?? 0),
		);

		$outstanding = $this->get_supplier_outstanding($supplier_id);
		$payments    = $is_official ? $this->get_supplier_payments($supplier_id, 'official') : $this->get_supplier_payments($supplier_id);

		$adj_where = "sa.supplier_id = '$supplier_id' AND sa.is_deleted = '0'";
		if ($is_official) {
			$adj_where .= " AND sa.type = 'official'";
		}
		if ($current_adj_id) {
			$adj_where .= " AND sa.id != " . intval($current_adj_id);
		}
		$adjustments = $this->db->query("SELECT sa.* FROM supplier_adjustments sa WHERE $adj_where")->result_array();

		$totals = array(
			'purchase'  => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
			'payment'   => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
			'adj_plus'  => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
			'adj_minus' => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
		);

		foreach ($outstanding as $row) {
			if ($is_official) {
				$totals['purchase']['usd'] += (float)$row['official_usd'];
				$totals['purchase']['inr'] += (float)$row['official_inr'];
			} else {
				$totals['purchase']['rmb'] += (float)$row['total_actual_rmb'];
				$totals['purchase']['usd'] += (float)$row['total_actual_usd'];
				$totals['purchase']['inr'] += (float)$row['total_actual_inr'];
			}
		}

		foreach ($payments as $pay) {
			$totals['payment']['rmb'] += (float)$pay['amount_rmb'];
			$totals['payment']['usd'] += (float)$pay['amount_dollar'];
			$totals['payment']['inr'] += (float)$pay['amount_rs'];
		}

		foreach ($adjustments as $adj) {
			$amt_type = ($adj['amt_type'] === 'plus') ? 'adj_plus' : 'adj_minus';
			$totals[$amt_type]['rmb'] += (float)$adj['rmb'];
			$totals[$amt_type]['usd'] += (float)$adj['usd'];
			$totals[$amt_type]['inr'] += (float)$adj['inr'];
		}

		$open = $is_official ? array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00) : $opening;

		$balance = array(
			'rmb' => $open['rmb'] + $totals['purchase']['rmb'] - $totals['payment']['rmb'] + $totals['adj_plus']['rmb'] - $totals['adj_minus']['rmb'],
			'usd' => $open['usd'] + $totals['purchase']['usd'] - $totals['payment']['usd'] + $totals['adj_plus']['usd'] - $totals['adj_minus']['usd'],
			'inr' => $open['inr'] + $totals['purchase']['inr'] - $totals['payment']['inr'] + $totals['adj_plus']['inr'] - $totals['adj_minus']['inr'],
		);

		$net_adj_inr = $totals['adj_plus']['inr'] - $totals['adj_minus']['inr'];

		return array(
			'supplier_name' => $supplier['name'],
			'type'          => $type,
			'show_rmb'      => $show_rmb,
			'opening'       => $open,
			'totals'        => $totals,
			'net_adj_inr'   => $net_adj_inr,
			'balance'       => $balance,
			'is_due'        => ($balance['inr'] > 0),
		);
	}

	public function get_supplier_adjustments($supplier_id, $type = null)
	{
		$where = "sa.supplier_id = '$supplier_id' AND sa.is_deleted = '0'";
		if ($type) {
			$where .= " AND sa.type = " . $this->db->escape($type);
		}
		$query = $this->db->query("SELECT 
										sa.*,
										CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
									FROM supplier_adjustments sa
									LEFT JOIN sys_users u ON sa.added_by = u.id
									WHERE $where
									ORDER BY sa.date ASC, sa.id ASC");
		return $query->result_array();
	}

	/* Vendor Adjustments Starts */
	public function get_company_vendors()
	{
		$company_id = $this->session->userdata('company_id');
		$where = "is_deleted = '0'";
		if ($company_id) {
			$where .= " AND company_id = '" . $company_id . "'";
		}
		$query = $this->db->query("SELECT id, name FROM my_companies WHERE $where ORDER BY name ASC");
		return $query ? $query->result_array() : [];
	}

	public function add_vendor_adjustment()
	{
		$company_id = $this->session->userdata('company_id');
		$user_id = $this->session->userdata('super_user_id');
		$user_name = $this->session->userdata('super_name');

		$supplier_id = clean_and_escape($this->input->post('supplier_id'));
		$date = clean_and_escape($this->input->post('date'));
		$rmb = clean_and_escape($this->input->post('rmb'));
		$usd = clean_and_escape($this->input->post('usd'));
		$inr = clean_and_escape($this->input->post('inr'));
		$amt_type = clean_and_escape($this->input->post('amt_type'));
		$type = clean_and_escape($this->input->post('type'));
		$remark = clean_and_escape($this->input->post('remark'));

		$vendor = $this->db->get_where('my_companies', array('id' => $supplier_id))->row_array();
		$supplier_name = isset($vendor['name']) ? $vendor['name'] : '';

		$data = array(
			'company_id'    => $company_id ? $company_id : 0,
			'supplier_id'   => $supplier_id,
			'supplier_name' => $supplier_name,
			'batch_id'      => NULL,
			'batch_no'      => NULL,
			'date'          => $date ? $date : date('Y-m-d'),
			'rmb'           => !empty($rmb) ? $rmb : 0.00,
			'usd'           => !empty($usd) ? $usd : 0.00,
			'inr'           => !empty($inr) ? $inr : 0.00,
			'amt_type'      => $amt_type,
			'type'          => $type,
			'remark'        => $remark,
			'is_deleted'    => 0,
			'added_by'      => $user_name,
			'added_by_id'   => $user_id,
			'created_at'    => date('Y-m-d H:i:s'),
			'updated_at'    => date('Y-m-d H:i:s')
		);

		$this->db->insert('vendor_adjustments', $data);
		$this->session->set_flashdata('flash_message', 'Vendor Adjustment Added Successfully');
		redirect(site_url('inventory/vendor-adjustment'), 'refresh');
	}

	public function edit_vendor_adjustment($id)
	{
		$supplier_id = clean_and_escape($this->input->post('supplier_id'));
		$date = clean_and_escape($this->input->post('date'));
		$rmb = clean_and_escape($this->input->post('rmb'));
		$usd = clean_and_escape($this->input->post('usd'));
		$inr = clean_and_escape($this->input->post('inr'));
		$amt_type = clean_and_escape($this->input->post('amt_type'));
		$type = clean_and_escape($this->input->post('type'));
		$remark = clean_and_escape($this->input->post('remark'));

		$vendor = $this->db->get_where('my_companies', array('id' => $supplier_id))->row_array();
		$supplier_name = isset($vendor['name']) ? $vendor['name'] : '';

		$data = array(
			'supplier_id'   => $supplier_id,
			'supplier_name' => $supplier_name,
			'date'          => $date ? $date : date('Y-m-d'),
			'rmb'           => !empty($rmb) ? $rmb : 0.00,
			'usd'           => !empty($usd) ? $usd : 0.00,
			'inr'           => !empty($inr) ? $inr : 0.00,
			'amt_type'      => $amt_type,
			'type'          => $type,
			'remark'        => $remark,
			'updated_at'    => date('Y-m-d H:i:s')
		);

		$this->db->where('id', $id);
		$this->db->update('vendor_adjustments', $data);
		$this->session->set_flashdata('flash_message', 'Vendor Adjustment Updated Successfully');
		redirect(site_url('inventory/vendor-adjustment'), 'refresh');
	}

	public function delete_vendor_adjustment($id)
	{
		$this->db->where('id', $id);
		$this->db->update('vendor_adjustments', array('is_deleted' => 1));
		$this->session->set_flashdata('flash_message', 'Vendor Adjustment Deleted Successfully');
		redirect(site_url('inventory/vendor-adjustment'), 'refresh');
	}

	public function get_vendor_adjustment_by_id($id)
	{
		return $this->db->get_where('vendor_adjustments', array('id' => $id, 'is_deleted' => 0))->row_array();
	}

	public function get_vendor_adjustment_datatable()
	{
		$draw = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 1;
		$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
		$length = isset($_REQUEST['length']) ? intval($_REQUEST['length']) : 10;

		$search_val = isset($_REQUEST['search']['value']) ? clean_and_escape($_REQUEST['search']['value']) : '';
		$where = "va.is_deleted = '0'";

		$company_id = $this->session->userdata('company_id');
		if ($company_id) {
			$where .= " AND va.company_id = '" . $company_id . "'";
		}

		if ($search_val != '') {
			$where .= " AND (va.supplier_name LIKE '%" . $search_val . "%' OR va.remark LIKE '%" . $search_val . "%' OR va.amt_type LIKE '%" . $search_val . "%' OR va.type LIKE '%" . $search_val . "%')";
		}

		$total_count = $this->db->query("SELECT va.id FROM vendor_adjustments va WHERE $where")->num_rows();

		$query = $this->db->query("SELECT 
										va.*,
										CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
									FROM vendor_adjustments va
									LEFT JOIN sys_users u ON va.added_by_id = u.id
									WHERE $where
									ORDER BY va.id DESC
									LIMIT $start, $length");

		$data = array();
		if ($query) {
			foreach ($query->result_array() as $item) {
				$id = $item['id'];
				$edit_url = base_url() . 'inventory/edit-vendor-adjustment/' . $id;
				$delete_url = "confirm_modal('" . base_url() . "inventory/vendor-adjustment/delete/" . $id . "','Are you sure want to delete this adjustment!')";

				$action = '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>';
				$action .= '<a href="#" onclick="' . $delete_url . '" data-toggle="tooltip" data-bs-placement="top" title="Delete"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><i class="fa fa-trash" aria-hidden="true"></i></button></a>';

				$amt_type_badge = ($item['amt_type'] == 'plus') 
					? '<span class="badge bg-success" style="font-size:11px;">Plus (+)</span>' 
					: '<span class="badge bg-danger" style="font-size:11px;">Minus (-)</span>';

				$type_badge = ($item['type'] == 'official')
					? '<span class="badge bg-info" style="font-size:11px;">Official</span>'
					: '<span class="badge bg-secondary" style="font-size:11px;">Unofficial</span>';

				$data[] = array(
					"sr_no"         => ++$start,
					"id"            => $item['id'],
					"date"          => date('d M Y', strtotime($item['date'])),
					"vendor_name"   => html_escape($item['supplier_name']),
					"usd"           => number_format((float)$item['usd'], 2),
					"rmb"           => number_format((float)$item['rmb'], 2),
					"inr"           => number_format((float)$item['inr'], 2),
					"amt_type"      => $amt_type_badge,
					"type"          => $type_badge,
					"remark"        => html_escape($item['remark'] ? $item['remark'] : '—'),
					"added_by"      => html_escape($item['added_by_name'] ? $item['added_by_name'] : ($item['added_by'] ? $item['added_by'] : '—')),
					"action"        => $action,
				);
			}
		}

		$json_data = array(
			"draw"            => $draw,
			"recordsTotal"    => $total_count,
			"recordsFiltered" => $total_count,
			"data"            => $data
		);
		echo json_encode($json_data);
	}

	public function get_vendor_adjustments($vendor_id, $type = null)
	{
		$where = "va.supplier_id = '$vendor_id' AND va.is_deleted = '0'";
		if ($type) {
			$where .= " AND va.type = " . $this->db->escape($type);
		}
		$query = $this->db->query("SELECT 
										va.*,
										CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as added_by_name
									FROM vendor_adjustments va
									LEFT JOIN sys_users u ON va.added_by_id = u.id
									WHERE $where
									ORDER BY va.date ASC, va.id ASC");
		return $query ? $query->result_array() : [];
	}

	public function get_vendor_ledger_summary($vendor_id, $current_adj_id = null)
	{
		$vendor = $this->db->get_where('my_companies', array('id' => $vendor_id))->row_array();
		if (!$vendor) {
			return null;
		}

		$opening = array(
			'rmb' => (float)($vendor['outstanding_rmb'] ?? 0.00),
			'usd' => (float)($vendor['outstanding_usd'] ?? 0.00),
			'inr' => (float)($vendor['outstanding_inr'] ?? $vendor['outstanding'] ?? 0.00),
		);

		$outstanding = $this->get_vendor_ledger($vendor_id);
		$payments = $this->get_vendor_payments_by_id($vendor_id);

		$adj_where = "supplier_id = '$vendor_id' AND is_deleted = 0";
		if ($current_adj_id) {
			$adj_where .= " AND id != " . intval($current_adj_id);
		}
		$adjustments = $this->db->query("SELECT * FROM vendor_adjustments WHERE $adj_where")->result_array();

		$totals = array(
			'expenses'  => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
			'payment'   => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
			'adj_plus'  => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
			'adj_minus' => array('rmb' => 0.00, 'usd' => 0.00, 'inr' => 0.00),
		);

		foreach ($outstanding as $row) {
			$totals['expenses']['rmb'] += (float)($row['rmb'] ?? 0);
			$totals['expenses']['usd'] += (float)($row['usd'] ?? 0);
			$totals['expenses']['inr'] += (float)($row['grand_total'] ?? 0);
		}

		foreach ($payments as $pay) {
			$totals['payment']['rmb'] += (float)($pay['rmb'] ?? 0);
			$totals['payment']['usd'] += (float)($pay['usd'] ?? 0);
			$totals['payment']['inr'] += (float)($pay['inr'] ?? 0);
		}

		foreach ($adjustments as $adj) {
			$amt_type = ($adj['amt_type'] === 'plus') ? 'adj_plus' : 'adj_minus';
			$totals[$amt_type]['rmb'] += (float)$adj['rmb'];
			$totals[$amt_type]['usd'] += (float)$adj['usd'];
			$totals[$amt_type]['inr'] += (float)$adj['inr'];
		}

		$balance = array(
			'rmb' => $opening['rmb'] + $totals['expenses']['rmb'] - $totals['payment']['rmb'] + $totals['adj_plus']['rmb'] - $totals['adj_minus']['rmb'],
			'usd' => $opening['usd'] + $totals['expenses']['usd'] - $totals['payment']['usd'] + $totals['adj_plus']['usd'] - $totals['adj_minus']['usd'],
			'inr' => $opening['inr'] + $totals['expenses']['inr'] - $totals['payment']['inr'] + $totals['adj_plus']['inr'] - $totals['adj_minus']['inr'],
		);

		$net_adj_inr = $totals['adj_plus']['inr'] - $totals['adj_minus']['inr'];

		return array(
			'vendor_name' => $vendor['name'],
			'opening'     => $opening,
			'totals'      => $totals,
			'net_adj_inr' => $net_adj_inr,
			'balance'     => $balance,
			'is_due'      => ($balance['inr'] > 0 || $balance['usd'] > 0 || $balance['rmb'] > 0),
		);
	}
	/* Vendor Adjustments Ends */

	public function get_dashboard_company_list()
	{
		$query = $this->db->query("SELECT id, name AS company_name FROM company WHERE (is_deleted = '0' OR is_deleted IS NULL) ORDER BY name ASC");
		return $query->result_array();
	}

	public function get_dashboard_staff_list()
	{
		$session_company_id = $this->session->userdata('company_id');
		$where = "(added_by_name IS NOT NULL AND added_by_name != '' AND (is_deleted = '0' OR is_deleted IS NULL))";
		if ($session_company_id) {
			$where .= " AND FIND_IN_SET('" . $this->db->escape_str($session_company_id) . "', company_id)";
		}
		$query = $this->db->query("SELECT DISTINCT added_by_id AS id, added_by_name AS name FROM customer WHERE $where ORDER BY added_by_name ASC");
		return $query->result_array();
	}

	public function get_leads_calls_dashboard_data($filters = array())
	{
		$user_id            = $this->session->userdata('super_user_id');
		$type               = $this->session->userdata('super_type');
		$session_company_id = $this->session->userdata('company_id');
		$staff_access       = (int)$this->session->userdata('super_type_id');

		$staff_filter   = isset($filters['staff_id']) && $filters['staff_id'] !== '' && $filters['staff_id'] !== 'all' ? $filters['staff_id'] : '';
		$period         = isset($filters['period']) && !empty($filters['period']) ? $filters['period'] : 'this_month';
		$cust_type      = isset($filters['type']) && !empty($filters['type']) ? $filters['type'] : 'all';
		$cust_status    = isset($filters['status']) && !empty($filters['status']) ? $filters['status'] : 'all';

		// Determine date range
		if ($period == 'today') {
			$start_date = date('Y-m-d');
			$end_date   = date('Y-m-d');
		} elseif ($period == 'this_week') {
			$start_date = date('Y-m-d', strtotime('monday this week'));
			$end_date   = date('Y-m-d');
		} elseif ($period == 'this_month') {
			$start_date = date('Y-m-01');
			$end_date   = date('Y-m-t');
		} elseif ($period == 'last_month') {
			$start_date = date('Y-m-01', strtotime('-1 month'));
			$end_date   = date('Y-m-t', strtotime('-1 month'));
		} elseif ($period == 'last_30_days') {
			$start_date = date('Y-m-d', strtotime('-30 days'));
			$end_date   = date('Y-m-d');
		} elseif ($period == 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
			$start_date = $filters['start_date'];
			$end_date   = $filters['end_date'];
		} else {
			$start_date = date('Y-m-01');
			$end_date   = date('Y-m-t');
		}

		$start_dt = $start_date . ' 00:00:00';
		$end_dt   = $end_date . ' 23:59:59';

		// Build base customer WHERE clause matching get_customer() logic
		$c_where = "WHERE (c.is_deleted = '0' OR c.is_deleted IS NULL)";

		// Staff restriction (staff user sees their assigned leads & customer follow-ups)
		if ($staff_access == 7 || $type == 'staff') {
			$c_where .= " AND c.added_by_id = '" . $this->db->escape_str($user_id) . "'";
		} elseif ($session_company_id) {
			$esc_comp = $this->db->escape_str($session_company_id);
			$c_where .= " AND ((c.type = 'leads' AND (c.company_id IS NULL OR c.company_id = '' OR c.company_id = '0' OR FIND_IN_SET('$esc_comp', c.company_id))) OR (c.type = 'customer' AND FIND_IN_SET('$esc_comp', c.company_id)))";
		}

		// 1. KPI Summary Counts (Overall Data for Current Company / Staff)
		$today_date = date('Y-m-d');
		$summary_sql = "SELECT 
			-- 1. Total Leads in current company (matching leads_data.php 'all')
			COUNT(CASE WHEN c.type = 'leads' THEN 1 END) as total_leads,
			-- 2. Overall New Leads (matching leads_data.php 'new': fresh or follow in leads)
			COUNT(CASE WHEN (c.status = 'fresh' OR c.status = 'follow') AND c.type = 'leads' THEN 1 END) as new_leads,
			-- 3. Today's Follow-up (matching leads_data.php status='today')
			COUNT(CASE WHEN ((c.status = 'follow' AND c.type = 'leads') OR (c.status = 'stalking' AND c.type = 'customer')) AND DATE(c.status_date) = '$today_date' THEN 1 END) as todays_followups,
			-- 4. Upcoming Follow-up (matching leads_data.php status='upcoming')
			COUNT(CASE WHEN ((c.status = 'follow' AND c.type = 'leads') OR (c.status = 'stalking' AND c.type = 'customer')) AND DATE(c.status_date) > '$today_date' THEN 1 END) as upcoming_followups,
			-- 5. Converted to Customer (matching leads_data.php 'moved')
			COUNT(CASE WHEN c.type = 'customer' AND c.is_move = '1' THEN 1 END) as converted_leads,
			-- 6. Lost Leads (matching leads_data.php status='lost' AND type='leads')
			COUNT(CASE WHEN c.type = 'leads' AND c.status = 'lost' THEN 1 END) as lost_leads,
			-- 7. Missed Leads (leads follow + customer stalking scheduled before today)
			COUNT(CASE WHEN (c.status = 'follow' OR c.status = 'stalking') AND DATE(c.status_date) < '$today_date' THEN 1 END) as missed_leads
		FROM customer c $c_where";
		$summary_res = $this->db->query($summary_sql)->row_array();

		// Calls attended for current company / staff on current date (matching customer_calls_data.php / get_customer_calls default logic)
		$call_where = "WHERE (c.is_deleted = '0' OR c.is_deleted IS NULL)";
		if ($staff_access == 7 || $type == 'staff') {
			$call_where .= " AND cc.added_by = '" . $this->db->escape_str($user_id) . "'";
		} elseif ($session_company_id) {
			$esc_comp = $this->db->escape_str($session_company_id);
			$call_where .= " AND ((c.type = 'leads' AND (c.company_id IS NULL OR c.company_id = '' OR c.company_id = '0' OR FIND_IN_SET('$esc_comp', c.company_id))) OR (c.type = 'customer' AND FIND_IN_SET('$esc_comp', c.company_id)) OR c.company_id IS NULL)";
		}
		$today_calls_where = $call_where . " AND DATE(cc.created_at) = '$today_date'";
		$calls_sql = "SELECT COUNT(cc.id) as calls_count FROM customer_calls cc LEFT JOIN customer c ON cc.customer_id = c.id $today_calls_where";
		$calls_res = $this->db->query($calls_sql)->row_array();

		$summary = array(
			'total_leads'        => (int)($summary_res['total_leads'] ?? 0),
			'new_leads'          => (int)($summary_res['new_leads'] ?? 0),
			'todays_followups'   => (int)($summary_res['todays_followups'] ?? 0),
			'upcoming_followups' => (int)($summary_res['upcoming_followups'] ?? 0),
			'converted_leads'    => (int)($summary_res['converted_leads'] ?? 0),
			'lost_leads'         => (int)($summary_res['lost_leads'] ?? 0),
			'calls'              => (int)($calls_res['calls_count'] ?? 0),
			'missed_leads'       => (int)($summary_res['missed_leads'] ?? 0),
		);

		// 2. Daily Call Trend (Last 7 Days - Customer vs Lead Calls)
		$days_map = array();
		for ($i = 6; $i >= 0; $i--) {
			$d_key = date('Y-m-d', strtotime("-$i days"));
			$days_map[$d_key] = array(
				'total'          => 0,
				'customer_calls' => 0,
				'lead_calls'     => 0,
			);
		}

		$trend_start_dt = date('Y-m-d 00:00:00', strtotime('-6 days'));
		$call_trend_sql = "SELECT 
			DATE(cc.created_at) as date_val,
			COUNT(cc.id) as total,
			COUNT(CASE WHEN c.type = 'customer' THEN 1 END) as customer_calls,
			COUNT(CASE WHEN c.type = 'leads' OR c.type IS NULL OR c.type = '' THEN 1 END) as lead_calls
		FROM customer_calls cc
		LEFT JOIN customer c ON cc.customer_id = c.id
		$call_where AND cc.created_at >= '$trend_start_dt'
		GROUP BY DATE(cc.created_at)
		ORDER BY date_val ASC";
		$call_trend_raw = $this->db->query($call_trend_sql)->result_array();

		foreach ($call_trend_raw as $ctr) {
			if (isset($days_map[$ctr['date_val']])) {
				$days_map[$ctr['date_val']]['total']          = (int)$ctr['total'];
				$days_map[$ctr['date_val']]['customer_calls'] = (int)$ctr['customer_calls'];
				$days_map[$ctr['date_val']]['lead_calls']     = (int)$ctr['lead_calls'];
			}
		}

		$call_trends = array();
		foreach ($days_map as $d_val => $row) {
			$call_trends[] = array(
				'date_val'       => $d_val,
				'formatted_date' => date('d M', strtotime($d_val)),
				'total'          => $row['total'],
				'customer_calls' => $row['customer_calls'],
				'lead_calls'     => $row['lead_calls'],
			);
		}

		// 3. Staff Performance Table (Admin only)
		$staff_perf = array();
		if ($staff_access != 7 && $type != 'staff') {
			$staff_perf_sql = "SELECT 
				IFNULL(NULLIF(c.added_by_name, ''), 'Unassigned') as staff_name,
				COUNT(CASE WHEN c.type = 'leads' THEN 1 END) as leads_added,
				COUNT(CASE WHEN c.type = 'leads' AND (c.status != 'lost' OR c.status IS NULL) THEN 1 END) as active_leads,
				COUNT(CASE WHEN c.type = 'leads' AND c.status = 'lost' THEN 1 END) as lost_leads,
				COUNT(CASE WHEN c.type = 'customer' AND c.is_move = '1' THEN 1 END) as converted_leads
			FROM customer c $c_where
			GROUP BY IFNULL(NULLIF(c.added_by_name, ''), 'Unassigned')
			ORDER BY leads_added DESC";
			$staff_perf = $this->db->query($staff_perf_sql)->result_array();

			// Add Call count per staff
			foreach ($staff_perf as &$sp) {
				$s_name = $sp['staff_name'];
				$staff_call_sql = "SELECT COUNT(cc.id) as call_cnt 
				FROM customer_calls cc 
				LEFT JOIN customer c ON cc.customer_id = c.id
				$call_where AND cc.added_by_name = " . $this->db->escape($s_name);
				$sp_call_res = $this->db->query($staff_call_sql)->row_array();
				$sp['calls_created'] = (int)($sp_call_res['call_cnt'] ?? 0);

				$total_leads_for_staff = $sp['leads_added'];
				$sp['conversion_rate'] = $total_leads_for_staff > 0 ? round(($sp['converted_leads'] / $total_leads_for_staff) * 100, 1) : 0;
			}
		}

		return array(
			'summary'            => $summary,
			'call_trends'        => $call_trends,
			'staff_performance'  => $staff_perf,
			'filters_used'       => array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'period'     => $period,
				'company_id' => $session_company_id,
				'staff_id'   => $staff_filter,
				'type'       => $cust_type,
				'status'     => $cust_status,
			)
		);
	}
}
