<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Inventory extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('inventory_model');
        $this->load->model('category_model');
        $this->load->model('pdf_model');
    }

    function paginate($url, $total_rows)
    {
        //initialize pagination
        $page     = $this->security->xss_clean($this->input->get('page'));
        $per_page = $this->input->get('show', true);
        if (empty($page)) {
            $page = 0;
        }

        if ($page != 0) {
            $page = $page - 1;
        }

        if (empty($per_page)) {
            $per_page = 20;
        }
        $config['num_links']          = 4;
        $config['base_url']           = $url;
        $config['total_rows']         = $total_rows;
        $config['per_page']           = $per_page;
        $config['reuse_query_string'] = true;
        $this->pagination->initialize($config);

        return array(
            'per_page' => $per_page,
            'offset' => $page * $per_page
        );
    }

    public function index()
    {
        if ($this->session->userdata('inventory_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }

    public function get_ajax_dashboard_data()
    {
        $date_range = $this->input->post('date_range', true);
        $card_name = $this->input->post('card_name', true);
        $filter_data['date_range']       = $date_range;
        $page_data['stats'] = $this->inventory_model->get_ajax_dashboard_stats($filter_data);

        if ($card_name == 'overall_card') {
            $this->load->view('backend/inventory/cards/_overall_dashbord.php', $page_data);
        }
    }

    public function get_ajax_ranked_products()
    {
        $this->inventory_model->get_ajax_ranked_products();
    }

    public function dashboard()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $filter_data['date_range'] = date('Y-m-d') . ' - ' . date('Y-m-d');
        // $page_data['stats'] = $this->inventory_model->get_ajax_dashboard_stats($filter_data);
        // $page_data['most_lowest'] = $this->inventory_model->get_ajax_ranked_products();
        // $page_data['no_stock'] = $this->inventory_model->get_no_stock_products();

        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('dashboard');
        $this->load->view('backend/index.php', $page_data);
    }

    public function set_company()
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode(array('status' => 'error', 'message' => 'Unauthorized'));
            return;
        }

        $company_id = $this->input->post('company_id');
        
        // If company_id is empty, set it to 0
        if (empty($company_id)) {
            $company_id = 0;
        }

        // Set company_id in session
        $this->session->set_userdata('company_id', $company_id);

        echo json_encode(array('status' => 'success', 'message' => 'Company set successfully'));
    }

    public function system_password($param1 = "", $param2 = "") {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
         
        $page_data['page_name']  = 'change_password';
        $page_data['id']         = $param1;
        $page_data['page_title'] = 'System Password';
        $this->load->view('backend/index', $page_data);
        
    }

    public function warehouse($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_warehouse($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_warehouse($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_warehouse($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'warehouse';
            $page_data['page_title'] = get_phrase('warehouse');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function warehouse_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['states']     = $this->crud_model->get_states();

        if ($param1 == 'warehouse_add') {
            $page_data['page_name']  = 'warehouse_add';
            $page_data['page_title'] = 'Add warehouse';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'warehouse_edit') {
            $data                    = $this->inventory_model->get_warehouse_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['page_name']  = 'warehouse_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit warehouse';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_warehouse()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_warehouse();
        }
    }

    // Staff Management
    public function manage_staff($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_staff($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_staff($param2);
        } elseif ($param1 == "change_password") {
            $this->inventory_model->edit_change_password($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_staff($param2);
            //   redirect(site_url('admin/manage-staff'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name'] = 'manage_staff';
            $page_data['page_title'] = get_phrase('staff_management');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function staff_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // $page_data['staff_type']  = $this->inventory_model->get_staff_type()->result_array();
        $page_data['staff_access']  = $this->inventory_model->get_staff_access()->result_array();
        $page_data['company_list']     = $this->common_model->selectWhere('company', array('is_deleted' => '0'), 'ASC', 'name');
        if ($param1 == 'staff_add') {
            $page_data['page_name'] = 'staff_add';
            $page_data['page_title'] = get_phrase('add_staff');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'staff_edit') {
            $page_data['data'] = $this->inventory_model->get_staff_by_id($param2)->row_array();
            $page_data['page_name'] = 'staff_edit';
            $page_data['id'] = $param2;
            $page_data['page_title'] = get_phrase('edit_staff');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'change_password') {
            $page_data['data'] = $this->inventory_model->get_staff_by_id($param2)->row_array();
            $page_data['page_name'] = 'staff_password';
            $page_data['id'] = $param2;
            $page_data['page_title'] = get_phrase('change_password');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_manage_staff()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->inventory_model->get_manage_staff();
    }

    // Access Management
    public function manage_access($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_access($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_access($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_access($param2);
            // redirect(site_url('admin/manage-access'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name'] = 'manage_access';
            $page_data['page_title'] = get_phrase('access_management');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function access_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['access_type']  = $this->inventory_model->get_access_type()->result_array();
        if ($param1 == 'add') {
            $page_data['page_name'] = 'manage_access_add';
            $page_data['page_title'] = get_phrase('add_staff');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
            $page_data['data'] = $this->inventory_model->get_access_by_id($param2)->row_array();
            $page_data['page_name'] = 'manage_access_edit';
            $page_data['id'] = $param2;
            $page_data['page_title'] = get_phrase('edit_staff');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_manage_access()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->inventory_model->get_manage_access();
    }

    // Supplier
    public function supplier($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_supplier($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_supplier($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_supplier($param2);
        } elseif ($param1 == "replicate_post") {
            $this->inventory_model->replicate_supplier();
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'supplier';
            $page_data['page_title'] = get_phrase('supplier');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function supplier_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['countries']     = $this->crud_model->get_countries();

        if ($param1 == 'supplier_add') {
            $page_data['states']     = array(); // Start with empty states for add mode
            $page_data['page_name']  = 'supplier_add';
            $page_data['page_title'] = 'Add Supplier';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'supplier_edit') {
            $data                    = $this->inventory_model->get_supplier_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['states']     = $this->crud_model->get_states_by_country($data['country_id']);
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['page_name']  = 'supplier_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Supplier';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'supplier_ledger') {
            $data                    = $this->inventory_model->get_supplier_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['id']         = $param2;
            $page_data['outstanding'] = $this->inventory_model->get_supplier_outstanding($param2);
            $page_data['payments'] = $this->inventory_model->get_supplier_payments($param2);
            $page_data['page_name']  = 'supplier_ledger';
            $page_data['page_title'] = 'Supplier Ledger';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_supplier()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_supplier();
        }
    }

    // Category Starts

    public function categories($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['parent_categories'] = $this->category_model->get_all_parent_categories_by_lang();
        $page_data['navigation']  = 'categories';
        $page_data['page_name']  = 'categories';
        $page_data['page_title'] = 'Manage Category';
        $this->load->view('backend/index', $page_data);
    }

    public function load_categories()
    {
        $vars = array(
            "parent_category_id" => $this->input->post('id', true),
        );
        $html_content = $this->load->view('backend/print_categories', $vars, true);
        $data = array(
            'result' => 1,
            'html_content' => $html_content,
        );
        echo json_encode($data);
    }

    public function category($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_category($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_category($param2);
        } elseif ($param1 == "delete") {
            if (!empty($this->category_model->get_subcategories_by_parent_id($param2))) {
                $this->session->set_flashdata('error_message', "Please delete subcategories belonging to this category first!");
            } else {
                if ($this->inventory_model->delete_category($param2)) {
                    $this->session->set_flashdata('flash_message', get_phrase('category_deleted_successfully'));
                } else {
                    $this->session->set_flashdata('error_message', 'An error occurred please try again!');
                }
            }

            redirect(site_url('inventory/category'), 'refresh');
        } else {

            $page_data['page_name']  = 'category';
            $page_data['page_title'] = 'Manage Category';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function category_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['category_list']  = $this->category_model->categoryTree();
        $page_data['parent_categories'] = $this->category_model->get_all_parent_categories();
        if ($param1 == 'add') {
            $page_data['navigation']  = 'categories';
            $page_data['page_name']  = 'category_add';
            $page_data['page_title'] = 'Add Category';
            $page_data['current_page'] = 'Add New Category';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {

            $page_data['category'] = $this->category_model->get_category_lists($param2);
            if (empty($page_data['category'])) {
                redirect($this->agent->referrer());
            }
            $page_data['parent_categories_array'] = $this->category_model->get_parent_categories_array_by_category_id($param2);

            $data                    = $this->common_model->getRowById('categories', '*', ['id' => $param2]);
            $page_data['data']       = $data;
            $page_data['navigation']  = 'categories';
            $page_data['page_name']  = 'category_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Category';
            $page_data['current_page'] = 'Edit Category';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_category_by_id(){
        $id = $this->input->post('id');
        $data = $this->common_model->getRowById('categories', '*', ['id' => $id]);
        if($data == '') {
            echo json_encode(["type" => "ready"]);
        } else {
            if($data['parent_name'] == 'Ready Goods') {
                echo json_encode(["type" => "ready"]);
            } elseif($data['name'] == 'Spare Parts') {
                echo json_encode(["type" => "spare"]);
            } else {
                echo json_encode(["type" => "spare"]);
            }
        }
    }

    public function get_subcategories()
    {
        $parent_id = $this->input->post('parent_id', true);
        $html_content = '';
        if (!empty($parent_id)) {
            $subcategories = $this->category_model->get_subcategories_by_parent_id($parent_id);
            foreach ($subcategories as $item) {
                $html_content .= "<option value='" . $item->id . "'>" . $item->name . "</option>";
            }
        }
        $data = array(
            'result' => 1,
            'html_content' => $html_content,
        );
        echo json_encode($data);
    }

    // Category Ends

    // Size starts

    public function product_size($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_product_size($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_product_size($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_product_size($param2);
            redirect(site_url('inventory/product-size'), 'refresh');
        } else {
            $page_data['page_name']     = 'product_size';
            $page_data['page_title']    = 'Product Size';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function product_size_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'add') {
            $page_data['page_name']     = 'product_size_add';
            $page_data['page_title']    = 'Product Size';
            $page_data['current_page']  = 'Add Product Size';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
            $data                       = $this->common_model->getRowByIdArr('oc_attribute_values', '*', array('attribute_id' => '2', 'id' => $param2));
            $page_data['data']          = $data;
            $page_data['page_name']     = 'product_size_edit';
            $page_data['id']            = $param2;
            $page_data['page_title']    = 'Product Size';
            $page_data['current_page']  = 'Edit Product Size';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_product_size()
    {
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_filter_attribute(2);
        }
    }

    // Size Ends

    // Color Start

    public function product_color($param1 = "", $param2 = "")
    {
        if ($param1 == "add_post") {
            $this->inventory_model->add_product_color($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_product_color($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_product_color($param2);
            redirect(site_url('inventory/product-color'), 'refresh');
        } else {
            $page_data['page_name']     = 'product_color';
            $page_data['page_title']    = 'Product Color';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function product_color_form($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            $page_data['page_name']     = 'product_color_add';
            $page_data['page_title']    = 'Product Color';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
            $data                       = $this->common_model->getRowByIdArr('colors', '*', array('id' => $param2));
            $page_data['data']          = $data;
            $page_data['page_name']     = 'product_color_edit';
            $page_data['id']            = $param2;
            $page_data['page_title']    = 'Product Color';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_product_color()
    {
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_products_color();
        }
    }

    // Color Ends

    public function raw_products_delete_sku()
    {
        $this->inventory_model->raw_products_delete_sku();
    }

    public function raw_products_delete_variation()
    {
        $this->inventory_model->raw_products_delete_variation();
    }

    public function raw_products($param1 = "", $param2 = "", $param3 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_raw_products($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_raw_products($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_raw_products($param2);
        } elseif ($param1 == "delete_variation") {
            $this->inventory_model->delete_raw_products_variation($param2, $param3);
        } elseif ($param1 == "delete_variation_sku") {
            $this->inventory_model->delete_raw_products_variation_sku($param2, $param3);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'raw_products';
            $page_data['page_title'] = get_phrase('products');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function raw_products_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $company_id = $this->session->userdata('company_id');

        $categories = $this->category_model->getCategories();
        $category_tree = $this->category_model->buildTree($categories);
        $page_data['category_tree'] = $category_tree;

        $page_data['units_list']     = $this->common_model->select('units');
        $page_data['product_units']  = $this->common_model->getResultById('product_unit', 'id, name', ['is_delete' => '0']);
        $page_data['suppliers']     = $this->common_model->getResultById('supplier', 'id, name', ['company_id' => $company_id, 'type' => 'import', 'is_deleted' => '0']);
        // $page_data['form_list']     = $this->common_model->select('product_form');
        // $page_data['colors']     = $this->common_model->select('colors');
        // $page_data['sizes']     = $this->common_model->select('oc_attribute_values');

        // $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();

        if ($param1 == 'add') {
            $page_data['modesy_images'] = $this->file_model->get_sess_product_images_array();
            $page_data['page_name']  = 'raw_products_add';
            $page_data['page_title'] = 'Add Products';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'import') {
            $page_data['page_name']  = 'raw_products_import';
            $page_data['page_title'] = 'Import Products';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
            $data                    = $this->inventory_model->get_raw_products_by_id($param2)->row_array();
            $sku_products            = $this->common_model->getResultById('product_sku', 'id, product_id, sku_code', ['product_id' => $param2]);
            $variations              = $this->common_model->getResultById('product_variation', '*', ['product_id' => $param2]);
            $page_data['modesy_images'] = $this->file_model->get_product_images_uncached($param2);

            $page_data['data']       = $data;
            $page_data['skus']       = $sku_products;
            $page_data['variations'] = ($variations != '') ? $variations : [];
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['page_name']  = 'raw_products_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Products';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_raw_products()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_raw_products();
        }
    }

    public function update_product_price()
    {
        $id = $this->input->post('id');
        $total_amount = $this->input->post('total_amount');
        $res = $this->inventory_model->update_product_price($id, $total_amount);
    }
    
    // PO Expense Starts
    public function po_expense($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_po_expense();
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_po_expense($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_po_expense($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation'] = 'import_purchase_order';
            $page_data['type']       = 'import';
            $page_data['page_name']  = 'po_expense';
            $page_data['page_title'] = 'Expense';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function po_expense_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $company_id = $this->session->userdata('company_id');
        $company_list = $this->common_model->getResultById('my_companies', 'id, name, state_id', ['is_deleted' => '0', 'company_id' => $company_id]);
        $page_data['company_list'] = ($company_list != '') ? $company_list : [];

        $company_info = $this->common_model->getRowById('company', 'state_id', ['id' => $company_id]);
        $page_data['company_state_id'] = $company_info ? (int)$company_info['state_id'] : 0;

        $pos = $this->common_model->getResultById('purchase_order', 'id, voucher_no', ['is_deleted' => '0', 'method' => 'import', 'company_id' => $company_id, 'delivery_status' => 'purchase_in']);
        $page_data['po'] = ($pos != '') ? $pos : [];
        
        $expenses = $this->common_model->getResultById('expense_type', 'id, name', ['is_delete' => '0', 'company_id' => $company_id]);
        $page_data['expenses'] = ($expenses != '') ? $expenses : [];

        $other_charges = $this->common_model->getResultById('other_charges', 'id, name, gst', ['is_delete' => '0']);
        $page_data['other_charges'] = ($other_charges != '') ? $other_charges : [];

        if ($param1 == 'add') {
            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';

            $page_data['page_name']  = 'po_expense_add';
            $page_data['page_title'] = 'Add Expense';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
            $data = $this->common_model->getRowById('po_expense', '*', ['is_delete' => '0', 'id' => $param2]);
            $page_data['data'] = ($data != '') ? $data : [];
            $data = $this->common_model->getResultById('po_expense_details', '*', ['parent_id' => $param2]);
            $page_data['lists'] = ($data != '') ? $data : [];
            $page_data['id'] = $param2;

            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';

            $page_data['page_name']  = 'po_expense_edit';
            $page_data['page_title'] = 'Add Expense';
            $this->load->view('backend/index', $page_data);
        } 
    }

    public function get_po_expense()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_po_expense();
        }
    }

    public function get_suppliers_by_batch()
    {
        $batch_no = $this->input->post('batch_no');
        $po = $this->common_model->getRowById('purchase_order', 'id', ['voucher_no' => $batch_no]);
        
        if (!$po) {
            echo json_encode([]);
            return;
        }

        $suppliers = $this->db->query("
            SELECT s.id, s.name 
            FROM purchase_in_product lpp
            JOIN supplier s ON lpp.supplier_id = s.id
            WHERE lpp.parent_id = " . (int)$po['id'] . " 
            AND lpp.is_deleted = 0 
            GROUP BY lpp.supplier_id
        ")->result_array();

        echo json_encode($suppliers);
    }

    // payment receipt Starts
    public function payment_receipt($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_customer_payment();
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_payments($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_payments($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation'] = 'payment_receipt';
            $page_data['page_name']  = 'payment_receipt';
            $page_data['page_title'] = 'Payment Receipt';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function payment_receipt_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $company_id = $this->session->userdata('company_id');
        $customer_list = $this->common_model->getResultById('customer', 'id, owner_name, company_name', ['is_deleted' => '0', 'company_id' => $company_id, 'type' => 'customer']);
        $page_data['customer_list'] = ($customer_list != '') ? $customer_list : [];

        $bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', ['is_delete' => '0', 'company_id' => $company_id]);
        $page_data['bank_accounts'] = ($bank_accounts != '') ? $bank_accounts : [];

        if ($param1 == 'add') {
            $page_data['navigation']  = 'payment_receipt';
            $page_data['page_name']  = 'payment_receipt_add';
            $page_data['page_title'] = 'Add Payment Receipt';
            $this->load->view('backend/index', $page_data);
        } elseif($param1 == 'edit') {
            $data = $this->common_model->getRowById('payments', '*', ['is_delete' => '0', 'id' => $param2]);
            $page_data['data'] = ($data != '') ? $data : [];
            $page_data['id'] = $param2;
            $page_data['navigation']  = 'payments';
            $page_data['page_name']  = 'payments_edit';
            $page_data['page_title'] = 'Edit Payment';
            $this->load->view('backend/index', $page_data);
        }
    }

    
    public function get_customer_payments_ajax()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_customer_payments();
        }
    }

    // payments Starts
    public function payments($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_payments();
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_payments($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_payments($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation'] = 'payments';
            $page_data['page_name']  = 'payments';
            $page_data['page_title'] = 'Payments';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function payments_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $company_id = $this->session->userdata('company_id');
        $supplier_list = $this->common_model->getResultById('supplier', 'id, name', ['is_deleted' => '0', 'company_id' => $company_id]);
        $page_data['supplier_list'] = ($supplier_list != '') ? $supplier_list : [];

        $bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', ['is_delete' => '0', 'company_id' => $company_id]);
        $page_data['bank_accounts'] = ($bank_accounts != '') ? $bank_accounts : [];

        $pos = $this->db->query("SELECT id, voucher_no FROM purchase_order 
                                WHERE is_deleted = '0' 
                                AND delivery_status IN ('loading', 'purchase_in') 
                                AND method = 'import' 
                                AND company_id = '$company_id'")->result_array();
        $page_data['po'] = ($pos != '') ? $pos : [];

        if ($param1 == 'add') {
            $page_data['navigation']  = 'payments';
            $page_data['page_name']  = 'payments_add';
            $page_data['page_title'] = 'Add Payment';
            $this->load->view('backend/index', $page_data);
        } elseif($param1 == 'edit') {
            $data = $this->common_model->getRowById('payments', '*', ['is_delete' => '0', 'id' => $param2]);
            $page_data['data'] = ($data != '') ? $data : [];
            $page_data['id'] = $param2;
            $page_data['navigation']  = 'payments';
            $page_data['page_name']  = 'payments_edit';
            $page_data['page_title'] = 'Edit Payment';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_payments()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_payments();
        }
    }

    // Vendor Payments Starts
    public function vendor_payments($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_vendor_payments();
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_vendor_payments($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_vendor_payments($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation'] = 'vendor_payments';
            $page_data['page_name']  = 'vendor_payments';
            $page_data['page_title'] = 'Vendor Payments';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function vendor_payments_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $company_id = $this->session->userdata('company_id');
        $vendor_list = $this->common_model->getResultById('my_companies', 'id, name', ['is_deleted' => '0', 'company_id' => $company_id]);
        $page_data['vendor_list'] = ($vendor_list != '') ? $vendor_list : [];

        $bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', ['is_delete' => '0', 'company_id' => $company_id]);
        $page_data['bank_accounts'] = ($bank_accounts != '') ? $bank_accounts : [];

        if ($param1 == 'add') {
            $page_data['navigation']  = 'vendor_payments';
            $page_data['page_name']  = 'vendor_payments_add';
            $page_data['page_title'] = 'Add Vendor Payment';
            $this->load->view('backend/index', $page_data);
        } elseif($param1 == 'edit') {
            $data = $this->common_model->getRowById('vendor_payments', '*', ['is_delete' => '0', 'id' => $param2]);
            $page_data['data'] = ($data != '') ? $data : [];
            $page_data['id'] = $param2;
            $page_data['navigation']  = 'vendor_payments';
            $page_data['page_name']  = 'vendor_payments_edit';
            $page_data['page_title'] = 'Edit Vendor Payment';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_vendor_payments()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_vendor_payments();
        }
    }

    public function vendor_ledger($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['data'] = $this->inventory_model->get_my_company_by_id($id)->row_array();
        $page_data['id'] = $id;
        $page_data['outstanding'] = $this->inventory_model->get_vendor_ledger($id);
        $page_data['payments'] = $this->inventory_model->get_vendor_payments_by_id($id);
        $page_data['page_name'] = 'vendor_ledger';
        $page_data['page_title'] = 'Vendor Ledger';
        $this->load->view('backend/index', $page_data);
    }
    // Vendor Payments Ends

    
    // Loading List PO Starts
    public function po_purchase_in($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';
            $page_data['page_name']  = 'po_purchase_in';
            $page_data['page_title'] = 'Purchase In';
            $this->load->view('backend/index', $page_data);
        }
    }
     
    public function get_po_purchase_in()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order(['pending', 'priority', 'loading']);
        }
    }

    public function revert_purchase_order_in($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $res = $this->inventory_model->revert_purchase_order_in($id);
            echo json_encode($res);
        }
    }

    // Loading List PO Starts
    public function loading_list_po($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_loading_list_po();
        } elseif ($param1 == "download_invoice") {
            $id = $param2;
            $this->inventory_model->create_po_export_zip($id);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';
            $page_data['page_name']  = 'loading_list_po';
            $page_data['page_title'] = 'Loading List';
            $this->load->view('backend/index', $page_data);
        }
    }
     
    public function get_loading_list_po()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order(['pending', 'priority']);
        }
    }

    
    public function loading_list_po_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $company_id = $this->session->userdata('company_id');

        $where = array('is_deleted' => '0');
        $page_data['warehouse_list']     = $this->common_model->selectWhere('warehouse', $where, 'ASC', 'name');
        $page_data['supplier_list']     = $this->common_model->selectWhere('supplier', array('is_deleted' => '0', 'company_id' => $company_id), 'ASC', 'name');
        $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');
       
        if ($param1 == 'add') {
            $page_data['voucher_no']  = $this->inventory_model->get_po_voucher_no();

            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';

            $page_data['page_name']  = 'loading_list_po_add';
            $page_data['page_title'] = 'Add Import Purchase Order';
            $this->load->view('backend/index', $page_data);
        } 
    }
    // Priority PO Starts
    public function priority_po($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';
            $page_data['page_name']  = 'priority_po';
            $page_data['page_title'] = 'Priority List';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function generate_priotity_purchase_order_excel($id)
    {
        // Disable output processing for file download
        $this->output->enable_profiler(FALSE);
        $this->inventory_model->generate_priotity_purchase_order_excel($id);
    }

    public function update_purchase_order_loading_list() {
        $this->inventory_model->update_purchase_order_loading_list();
    }

    public function delete_loading_list_item() {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
            return;
        }
        $id = $this->input->post('id');
        if ($id) {
            $this->inventory_model->soft_delete_loading_list_item($id);
            echo json_encode(['status' => 200, 'message' => 'Success']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Invalid ID']);
        }
    }

    public function get_priority_po()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order(['pending']);
        }
    }

    public function update_purchase_order_in() {
        $this->inventory_model->update_purchase_order_in();
    }

    // Priority PO Ends

    // Purchase Order Starts
    public function purchase_order($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_purchase_order($param2);
        } elseif ($param1 == "add_local_post") {
            $this->inventory_model->add_local_purchase_order($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_purchase_order($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_purchase_order($param2);
        } elseif ($param1 == "delete_inv") {
            $this->inventory_model->delete_inv_purchase_order($param2);
        } elseif ($param1 == "delete_priority_list") {
            $this->inventory_model->delete_priority_list($param2);
        } elseif ($param1 == "delete_loading_list") {
            $this->inventory_model->delete_loading_list($param2);
        } elseif ($param1 == "move_to_purchase_in") {
            $this->inventory_model->move_to_purchase_in($param2);
        } elseif ($param1 == "local") {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'purchase_order';
            $page_data['type']      = 'local';
            $page_data['page_name']  = 'purchase_order_local';
            $page_data['page_title'] = 'Local Purchase In';
            $this->load->view('backend/index', $page_data);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';
            $page_data['page_name']  = 'purchase_order';
            $page_data['page_title'] = 'Import Purchase Order';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function purchase_order_entry($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'purchase_order_entry';
            $page_data['page_title'] = get_phrase('purchase_entry');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function purchase_order_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $company_id = $this->session->userdata('company_id');

        $where = array('is_deleted' => '0');
        $page_data['warehouse_list']     = $this->common_model->selectWhere('warehouse', $where, 'ASC', 'name');
        $page_data['supplier_list']     = $this->common_model->selectWhere('supplier', array('is_deleted' => '0', 'company_id' => $company_id), 'ASC', 'name');
        $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');
        
        // Get ready products with category names
        $page_data['ready_products_list'] = [];
        
        // Get spare products with category names
        $page_data['spare_products_list'] = [];
        
        // Keep old products_list for backward compatibility
        $page_data['products_list']     = $this->common_model->selectWhere('raw_products', $where, 'ASC', 'name');
        // $page_data['products_list']     = [];

        // echo json_encode($page_data['products_list']); exit();
        if ($param1 == 'add_local') {
            $page_data['voucher_no']  = $this->inventory_model->get_po_voucher_no();

            $page_data['navigation']  = 'purchase_order';
            $page_data['type']      = 'local';
            $page_data['products_list'] = $this->common_model->selectWhere('raw_products', array('is_deleted' => '0'), 'ASC', 'name');
            $page_data['supplier_list'] = $this->common_model->selectWhere('supplier', array('is_deleted' => '0', 'company_id' => $company_id, 'type' => 'local'), 'ASC', 'name');
            $page_data['other_charges'] = $this->db->get_where('other_charges', ['is_delete' => 0])->result_array();

            $page_data['page_name']  = 'purchase_order_local_add';
            $page_data['page_title'] = 'Add Local Purchase In';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'add_import') {
            $page_data['voucher_no']  = $this->inventory_model->get_po_voucher_no();

            $page_data['navigation']  = 'import_purchase_order';
            $page_data['type']      = 'import';

            $page_data['page_name']  = 'purchase_order_add';
            $page_data['page_title'] = 'Add Import Purchase Order';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_import') {
            $po_id = $param2;
            $data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();
            
            if (empty($data)) {
                $this->session->set_flashdata('error_message', 'Purchase Order not found.');
                redirect(site_url('inventory/purchase-order'), 'refresh');
            }
            
            $page_data['data'] = $data;
            $page_data['id'] = $po_id;
            $page_data['navigation'] = 'import_purchase_order';
            $page_data['type'] = 'import';
            
            // Get warehouse and supplier lists
            $page_data['warehouse_list'] = $this->common_model->selectWhere('warehouse', $where, 'ASC', 'name');
            $page_data['supplier_list'] = $this->common_model->selectWhere('supplier', array('is_deleted' => '0', 'company_id' => $company_id), 'ASC', 'name');
            
            // Get PO products grouped by supplier
            $page_data['po_products'] = $this->inventory_model->get_purchase_order_products_for_edit($po_id);
            
            // Get ready and spare products lists for dropdowns (empty for edit mode, will be populated by supplier selection)
            $page_data['ready_products_list'] = [];
            $page_data['spare_products_list'] = [];
            $page_data['products_list'] = $this->common_model->selectWhere('raw_products', $where, 'ASC', 'name');
            
            $page_data['page_name'] = 'purchase_order_edit';
            $page_data['page_title'] = 'Edit Import Purchase Order';
            $this->load->view('backend/index', $page_data);
        } else if ($param1 == 'view_entry') {
            $page_data['id']  = $param2;
            $page_data['page_name']  = 'purchase_order_entry_view';
            $page_data['page_title'] = 'Purchase Order Entry Details ';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function generate_purchase_order_excel($id)
    {
        // Disable output processing for file download
        $this->output->enable_profiler(FALSE);
        $this->inventory_model->generate_purchase_order_excel($id);
    }

    public function get_purchase_order()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order();
        }
    }

    public function get_purchase_order_local()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order_local();
        }
    }

    public function formula_product_order($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'formula_product_order';
            $page_data['page_name']  = 'formula_product_in_list';
            $page_data['page_title'] = 'Formula Product In';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_formula_product_orders()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_formula_product_orders();
        }
    }

    public function formula_product_order_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'add') {
            $page_data['products'] = $this->db->where(array('product_type' => 'local', 'has_formula' => 1, 'is_deleted' => 0))
                                             ->order_by('name', 'ASC')
                                             ->get('raw_products')
                                             ->result_array();
            $page_data['warehouse_list'] = $this->common_model->getSessionWarehouse();

            $page_data['navigation']  = 'formula_product_order';
            $page_data['page_name']  = 'formula_product_in_add';
            $page_data['page_title'] = 'Add Formula Product In';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_formula_ingredients_details()
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode(array('status' => 'error', 'message' => 'Unauthorized'));
            return;
        }

        $parent_id = (int)$this->input->post('product_id');
        $warehouse_id = (int)$this->input->post('warehouse_id');
        $company_id = (int)$this->session->userdata('company_id');

        if (empty($parent_id) || empty($warehouse_id)) {
            echo json_encode(array('status' => 'error', 'message' => 'Missing product or warehouse'));
            return;
        }

        // Fetch ingredients for formula
        $formula_items = $this->db->query("SELECT pf.product_id, pf.quantity as req_qty, rp.name, rp.item_code 
            FROM product_formula pf
            JOIN raw_products rp ON pf.product_id = rp.id
            WHERE pf.parent_id = ? 
            ORDER BY pf.id ASC", array($parent_id))->result_array();

        $ingredients = array();
        foreach ($formula_items as $item) {
            $ing_id = (int)$item['product_id'];
            $req_qty_pc = (int)$item['req_qty'];

            // Get total available qty from inventory (white and black stocks)
            $inv_qty_row = $this->db->query("SELECT SUM(official_qty) as av_white, SUM(black_qty) as av_black 
                FROM inventory 
                WHERE product_id = ? AND warehouse_id = ? AND company_id = ?", 
                array($ing_id, $warehouse_id, $company_id))->row_array();

            $av_white = $inv_qty_row ? (int)$inv_qty_row['av_white'] : 0;
            $av_black = $inv_qty_row ? (int)$inv_qty_row['av_black'] : 0;

            // Fetch available batches for this ingredient
            $batches = $this->db->query("SELECT id, batch_no, official_qty as av_white, black_qty as av_black, official_rate_rs as official_rate, actual_inr as black_rate
                FROM inventory
                WHERE product_id = ? AND warehouse_id = ? AND company_id = ? AND (official_qty > 0 OR black_qty > 0)
                ORDER BY id ASC", array($ing_id, $warehouse_id, $company_id))->result_array();

            $ingredients[] = array(
                'product_id' => $ing_id,
                'name' => $item['name'],
                'item_code' => $item['item_code'],
                'qty_per_pc' => $req_qty_pc,
                'available_white' => $av_white,
                'available_black' => $av_black,
                'batches' => $batches
            );
        }

        // Fetch default expenses for the formula product
        $expenses = $this->db->query("SELECT name, amount FROM product_charges WHERE product_id = ? ORDER BY id ASC", array($parent_id))->result_array();

        echo json_encode(array(
            'status' => 200,
            'ingredients' => $ingredients,
            'expenses' => $expenses
        ));
    }

    public function formula_product_order_add_post()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->inventory_model->add_formula_product_order();
    }


    // Purchase Order Ends

    public function get_purchase_order_entry()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order_entry();
        }
    }

    public function purchase_order_received_data()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->inventory_model->purchase_order_received_data();
    }

    public function get_purchase_order_product()
    {
        $id = $this->input->post('id', true);
        $results = $this->inventory_model->get_purchase_order_product($id);
        $i = 1;
        foreach ($results as $item) {
            if ($item['pending'] > 0) {
                $pending_quantity = $item['pending'] . ' - ' . $item['unit'];
                $batch_no = '<input type="text" class="form-control batch_no" name="batch_no[]" value="" id="batch_no_' . $item['id'] . '" oninput="this.value = this.value.toUpperCase()">';
                $expiry_date = '<input type="date" class="form-control" name="expiry_date[]" value="" min="' . date('Y-m-d') . '" id="date_picker">';
                $received = '<input type="text" class="form-control" name="received[]" value="0" id="rcv_quantity_' . $item['id'] . '" onkeyup="get_check_rcv_qty();">';
                $invoice_no = '<input type="text" class="form-control" name="invoice_no[]" value="" id="invoice_no_' . $item['id'] . '" >';
                $received_amount = '<input type="text" class="form-control" name="received_amount[]" value="" id="received_amount_' . $item['id'] . '">';
                $received_date = '<input type="date" class="form-control" name="received_date[]" value="' . date('Y-m-d') . '" max="' . date('Y-m-d') . '" id="date_picker">';
            } else {
                $pending_quantity = '<span class="badge badge-success">Delivered</span>';
                $batch_no = '- <input type="hidden" class="form-control batch_no" name="batch_no[]" value="" id="batch_no_' . $item['id'] . '" oninput="this.value = this.value.toUpperCase()">';
                $expiry_date = '- <input type="hidden" class="form-control" name="expiry_date[]" value="" min="' . date('Y-m-d') . '" id="date_picker">';
                $received = '- <input type="hidden" class="form-control" name="received[]" value="0" id="rcv_quantity_' . $item['id'] . '" onkeyup="get_check_rcv_qty();">';
                $invoice_no = '- <input type="hidden" class="form-control" name="invoice_no[]" value="" id="invoice_no_' . $item['id'] . '" >';
                $received_amount = '- <input type="hidden" class="form-control" name="received_amount[]" value="" id="received_amount_' . $item['id'] . '">';
                $received_date = '- <input type="hidden" class="form-control" name="received_date[]" value="' . date('Y-m-d') . '" max="' . date('Y-m-d') . '" id="date_picker">';
            }

            if ($item['is_variation'] == 0) {
                echo '<tr class="element-1 "><td><input type="hidden" class="form-control" name="name[]" value="' . $item['name'] . '" ><input type="hidden" name="product_id[]" id="product_id_' . $i . '" value="' . $item['product_id'] . '" ><input type="hidden" name="id[]" id="id_' . $i . '" value="' . $item['id'] . '" ><input type="hidden" class="form-control" name="final_quantity[]" id="final_quantity_' . $item['id'] . '" value="' . $item['quantity'] . '" ><input type="hidden" class="form-control" name="quantity[]" id="quantity_' . $item['id'] . '" value="' . $item['pending'] . '" ><input type="hidden" class="form-control" name="total_amount[]" id="total_amount_' . $item['id'] . '" value="' . $item['total_val'] . '" ><input type="hidden" name="variation[]" id="variation_' . $i . '" value="' . $item['item_code'] . '" ><input type="hidden" name="is_variation[]" id="is_variation_' . $i . '" value="' . $item['is_variation'] . '" >' . $item['name'] . '</td><td>' . $item['item_code'] . '</td><td>' . $item['quantity'] . ' - ' . $item['unit'] . ' </td><td>' . $item['total_val'] . '</td><td>' . $pending_quantity . '</td><td>' . $received . '</td><td>' . $invoice_no . '</td><td>' . $received_amount . '</td><td> ' . $received_date . '</td></tr>';
                $i++;
            } else {
                $item_c = count($item['variation_data']) + 1;
                $html = '';
                //echo json_encode($item['variation_data']);exit();

                foreach ($item['variation_data'] as $index => $x_item) {
                    // Header showing SKU
                    $header = '';
                    if ($index == 0) {
                        $header = '<td rowspan="' . $item_c . '">' . $item['item_code'] . ' - ' . $item['color_name'] . '</td>';
                    }

                    // Showing Fields
                    if ($item['pending'] > 0) {
                        $received = '<input type="text" class="form-control multi-qty-' . $item['id'] . '" data-id="' . $x_item['variation_id'] . '" name="received[]" value="0" id="rcv_quantity_' . $item['id'] . '" onkeyup="get_check_rcv_multi_qty();">';
                        $received_amount = '<input type="text" class="form-control" name="received_amount[]" value="" id="received_amount_' . $x_item['variation_id'] . '">';
                    } else {
                        $received = '- <input type="hidden" class="form-control " data-id="' . $x_item['variation_id'] . '" name="received[]" value="0" id="rcv_quantity_' . $item['id'] . '" onkeyup="get_check_rcv_multi_qty();">';
                        $received_amount = '- <input type="hidden" class="form-control" name="received_amount[]" value="" id="received_amount_' . $x_item['variation_id'] . '">';
                    }

                    $html .= '<tr class="element-1 ">
					            ' . $header . '
					            <td>
					                <input type="hidden" name="item_code[]" id="item_code_' . $item['id'] . '" value="' . $item['item_code'] . '">
					                <input type="hidden" name="final_quantity[]" id="final_quantity_' . $item['id'] . '" value="' . $item['quantity'] . '">
					                <input type="hidden" name="quantity[]" id="quantity_' . $item['id'] . '" value="' . $item['pending'] . '">
					                <input type="hidden" name="total_amount[]" id="total_amount_' . $item['id'] . '" value="' . $item['total_val'] . '">
					                <input type="hidden" name="name[]" value="' . $item['name'] . '" >
					                <input type="hidden" name="product_id[]" id="product_id_' . $i . '" value="' . $item['product_id'] . '" >
					                <input type="hidden" name="sizes[]" id="sizes_' . $i . '" value="' . $item['sizes'] . '">
					                <input type="hidden" name="group_id[]" id="group_id_' . $i . '" value="' . $item['group_id'] . '">
					                <input type="hidden" name="color_id[]" id="color_id_' . $i . '" value="' . $item['color_id'] . '">
					                <input type="hidden" name="color_name[]" id="color_name_' . $i . '" value="' . $item['color_name'] . '">
					                <input type="hidden" name="categories[]" id="categories_' . $i . '" value="' . $item['categories'] . '">
					                <input type="hidden" name="variation_id[]" id="variation_id_' . $i . '" value="' . $x_item['variation_id'] . '" >
					                <input type="hidden" name="id[]" id="id_' . $i . '" value="' . $item['id'] . '" >
					                <input type="hidden" name="size_name[]" id="size_name_' . $i . '" value="' . $x_item['size_name'] . '" >
					                <input type="hidden" name="size_id[]" id="size_id_' . $i . '" value="' . $x_item['size_id'] . '" >
					                <input type="hidden" name="variation[]" id="variation_' . $i . '" value="' . $x_item['item_code'] . '" >' . $x_item['size_name'] . '
					            </td>
					            <td>-</td>
					            <td>-</td>
					            <td>-</td>
					            <td>' . $received . '</td>
					            <td>' . $invoice_no . '</td>
					            <td>' . $received_amount . '</td>
					            <td> ' . $received_date . '</td>
					          </tr>';
                    $i++;
                }

                // Showing Bottom Calculation
                $html .= '<tr class="">
				            <td> - </td>
				            <td>' . $item['quantity'] . ' - ' . $item['unit'] . ' </td>
				            <td>' . $item['total_val'] . '</td>
				            <td>' . $pending_quantity . '</td>
				            <td>
				                <input type="hidden" id="recieved_qty_' . $item['product_id'] . '" value="' . $x_item['recieved_qty'] . '" >
				                <span class="recieved_qty_' . $item['product_id'] . '">' . $item['recieved_qty'] . '</span>
				            </td>
				            <td> - </td>
				            <td colspan="4">
    				            <input type="hidden" id="recieved_amt_' . $item['product_id'] . '" value="' . $x_item['recieved_amt'] . '" >
    				            <span class="recieved_amt_' . $item['product_id'] . '">' . $item['recieved_amt'] . '</span>
				            </td>
				          </tr>';

                echo $html;
            }
        }
    }

    public function view_purchase_order($param1 = "", $param2 = "")
    {
        $id = $param1;
        $receipt_no = sprintf('%05d', $id);
        $page_data['data'] =  $this->inventory_model->get_puchase_order_details($param1);
        $this->load->library('pdf');
        $this->load->library('zip');

        $html_content = $this->load->view('invoice/purchase_order', $page_data, TRUE);
        $this->pdf->set_paper("A4", "portrait");
        $this->pdf->set_option('isHtml5ParserEnabled', TRUE);
        $this->pdf->load_html($html_content);

        $this->pdf->render();
        $pdfname = 'invoice_' . $receipt_no . '.pdf';
        $this->pdf->stream($pdfname, array("Attachment" => 0));
        $output = $this->pdf->output();
        /*
    	$this->load->library('pdf');
    	$html = $this->pdf_model->view_purchase_order($param1);
		//echo $html;exit();
        $this->createPDF($html, $param1, true , 'A4','portrait');
		*/
    }

    public function createPDF($html, $filename = '', $download, $paper = 'A4', $orientation = 'portrait')
    {
        $dompdf = new Dompdf\DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper($paper, $orientation);
        //$paper_size = array(0,0,750,1050);
        //$this->pdf->set_paper($paper_size);
        $dompdf->render();
        if ($download == true)
            $dompdf->stream($filename . '.pdf', array('Attachment' => 0));
        else
            $dompdf->stream($filename . '.pdf', array('Attachment' => 1));
    }

    public function purchase_entry($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_purchase_entry($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_purchase_entry($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_purchase_entry($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'purchase_entry';
            $page_data['page_title'] = get_phrase('purchase_entry');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function purchase_entry_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $where = array('is_deleted' => '0');
        $page_data['supplier_list']     = $this->common_model->selectWhere('supplier', $where, 'ASC', 'name');

        if ($param1 == 'add') {
            $page_data['page_name']  = 'purchase_entry_add';
            $page_data['page_title'] = 'Add Purchase Entry';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
            $data                    = '';
            $page_data['data']       = $data;
            $page_data['page_name']  = 'purchase_entry_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Purchase Entry';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_purchase_entry()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_entry();
        }
    }

    public function get_purchase_order_entry_history($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order_entry_history($id);
            // echo $this->db->last_query(); exit();
        }
    }

    public function get_raw_product_details()
    {
        $product_id = $this->input->post('product_id');
        $res = $this->inventory_model->get_raw_products_by_id($product_id)->row_array();
        if ($res) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 200,
                'message' => 'success',
                "unit" => $res['unit'],
                "amount" => $res['amount'],
                "costing_price" => $res['costing_price'],
                "hsn_code" => $res['hsn_code'],
                "gst" => $res['gst'],
                "gst_amount" => $res['gst_amount'],
                "total_amount" => $res['total_amount'],
            ));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'error',
            ));
        }
    }

    public function get_purchase_order_product_details()
    {
        $product_id = $this->input->post('product_id');
        $type = $this->input->post('type');
        $res = $this->inventory_model->get_raw_products_by_id($product_id)->row_array();
        if ($res) {
            // Get category name
            $category_name = '-';
            if (!empty($res['categories'])) {
                $category_ids = explode(',', $res['categories']);
                if (!empty($category_ids[0])) {
                    $category = $this->common_model->getRowById('categories', 'name', ['id' => trim($category_ids[0])]);
                    $category_name = $category['name'] ?? '-';
                }
            }
            
            // Calculate pending PO quantity for this product and sum all quantities from pending purchase orders for this product
            $pending_po_qty = 0;
            $query = $this->db->query("
                SELECT SUM(pop.quantity) AS total_qty
                FROM purchase_order_product pop
                INNER JOIN purchase_order po ON po.id = pop.parent_id
                WHERE po.delivery_status = 'pending' AND po.method = '$type'
                AND po.is_deleted = 0
                AND pop.product_id = ?
            ", array($product_id));
            if ($query->num_rows() > 0) {
                $result = $query->row();
                $pending_po_qty = intval($result->total_qty ?? 0);
            }
            
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 200,
                'message' => 'success',
                "item_code" => $res['item_code'] ?? '',
                "cbm" => $res['cbm'] ?? 0,
                "pending_po_qty" => $pending_po_qty,
                "loading_list_qty" => 0,
                "in_stock_qty" => 0,
                "company_stock" => 0,
            ));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'error',
            ));
        }
    }

    public function get_supplier_details()
    {
        $supplier_id = $this->input->post('supplier_id');
        $res = $this->inventory_model->get_supplier_by_id($supplier_id)->row_array();
        if ($res) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 200,
                'message' => 'success',
                "address" => $res['address'] . ', ' . $res['address_2'] . ', ' . $res['address_3'] . ' - ' . $res['pincode'],
                "state_id" => $res['state_id'],
            ));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'error',
            ));
        }
    }

    public function get_products_by_supplier()
    {
        $supplier_id = $this->input->post('supplier_id');
        $type = $this->input->post('type');
        
        if (empty($supplier_id)) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'Supplier ID is required',
                'ready_products' => [],
                'spare_products' => []
            ));
            return;
        }

        // Get ready products with category names
        $ready_products = $this->db->query("
            SELECT rp.*, 
                   (SELECT c.name FROM categories c WHERE FIND_IN_SET(c.id, rp.categories) > 0 LIMIT 1) as category_name
            FROM raw_products rp
            WHERE rp.is_deleted = '0' AND rp.type = 'ready' AND rp.status = '1' AND rp.supplier_id = ?
            ORDER BY rp.categories ASC
        ", array($supplier_id))->result();
        
        // Get spare products with category names
        $spare_products = $this->db->query("
            SELECT rp.*, 
                   (SELECT c.name FROM categories c WHERE FIND_IN_SET(c.id, rp.categories) > 0 LIMIT 1) as category_name
            FROM raw_products rp
            WHERE rp.is_deleted = '0' AND rp.type = 'spare' AND rp.status = '1' AND rp.supplier_id = ?
            ORDER BY rp.categories ASC
        ", array($supplier_id))->result();

        // Format products with additional details
        $ready_products_data = array();
        foreach ($ready_products as $product) {

            $variations = $this->db->where('product_id', $product->id)->get('product_variation');
            $variations = ($variations->num_rows() > 0) ? $variations->result_array() : [];

            // Calculate pending PO quantity
            $pending_po_qty = 0;
            $query = $this->db->query("
                SELECT SUM(pop.quantity) AS total_qty
                FROM purchase_order_product pop
                INNER JOIN purchase_order po ON po.id = pop.parent_id
                WHERE po.delivery_status = 'pending' AND po.method = ?
                AND po.is_deleted = 0
                AND pop.product_id = ?
            ", array($type, $product->id));
            if ($query->num_rows() > 0) {
                $result = $query->row();
                $pending_po_qty = intval($result->total_qty ?? 0);
            }

            $ready_products_data[] = array(
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'item_code' => $product->item_code ?? '',
                'category_name' => $product->category_name ?? '-',
                'rate' => ($product->rate > 0) ? $product->rate : 0,
                'usd_rate' => ($product->usd_rate > 0) ? $product->usd_rate : 0,
                'actual_usd_rate' => ($product->actual_usd_rate > 0) ? $product->actual_usd_rate : 0,
                'duty_charge' => ($product->duty_charge > 0) ? $product->duty_charge : 0,
                'cartoon_qty' => $product->cartoon_qty ?? 0,
                'cbm' => $product->cbm ?? 0,
                'pending_po_qty' => $pending_po_qty,
                'loading_list_qty' => 0,
                'in_stock_qty' => 0,
                'company_stock' => 0,
                'variations' => $variations,
            );
        }

        $spare_products_data = array();
        foreach ($spare_products as $product) {
            $variations = $this->db->where('product_id', $product->id)->get('product_variation');
            $variations = ($variations->num_rows() > 0) ? $variations->result_array() : [];
            
            // Calculate pending PO quantity
            $pending_po_qty = 0;
            $query = $this->db->query("
                SELECT SUM(pop.quantity) AS total_qty
                FROM purchase_order_product pop
                INNER JOIN purchase_order po ON po.id = pop.parent_id
                WHERE po.delivery_status = 'pending' AND po.method = ?
                AND po.is_deleted = 0
                AND pop.product_id = ?
            ", array($type, $product->id));
            if ($query->num_rows() > 0) {
                $result = $query->row();
                $pending_po_qty = intval($result->total_qty ?? 0);
            }

            $spare_products_data[] = array(
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'item_code' => $product->item_code ?? '',
                'category_name' => $product->category_name ?? '-',
                'rate' => ($product->rate > 0) ? $product->rate : 0,
                'usd_rate' => ($product->usd_rate > 0) ? $product->usd_rate : 0,
                'actual_usd_rate' => ($product->actual_usd_rate > 0) ? $product->actual_usd_rate : 0,
                'duty_charge' => ($product->duty_charge > 0) ? $product->duty_charge : 0,
                'cartoon_qty' => $product->cartoon_qty ?? 0,
                'cbm' => $product->cbm ?? 0,
                'pending_po_qty' => $pending_po_qty,
                'loading_list_qty' => 0,
                'in_stock_qty' => 0,
                'company_stock' => 0,
                'variations' => $variations,
            );
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 200,
            'message' => 'success',
            'ready_products' => $ready_products_data,
            'spare_products' => $spare_products_data
        ));
    }

    public function get_warehouse_details()
    {
        $supplier_id = $this->input->post('supplier_id');
        $res = $this->inventory_model->get_warehouse_by_id($supplier_id)->row_array();
        if ($res) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 200,
                'message' => 'success',
                "address" => $res['address'] . ', ' . $res['address_2'] . ', ' . $res['address_3'] . ' - ' . $res['pincode'],
                "state_id" => $res['state_id'],
            ));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'error',
            ));
        }
    }

    public function check_gst_type()
    {
        $supplier_id = intval($this->input->post('supplier_id'));
        $company_id = intval($this->session->userdata('company_id'));
        
        $supplier = $this->db->get_where('supplier', array('id' => $supplier_id))->row_array();
        $company = $this->db->get_where('company', array('id' => $company_id))->row_array();
        
        $supplier_state = $supplier ? intval($supplier['state_id'] ?? 0) : 0;
        $company_state = $company ? intval($company['state_id'] ?? 0) : 0;
        
        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 200,
            'supplier_state_id' => $supplier_state,
            'company_state_id' => $company_state,
            'match' => ($supplier_state > 0 && $supplier_state === $company_state)
        ));
    }

    public function batch_detail($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $company_id = $this->session->userdata('company_id');
            $pos = $this->common_model->getResultById('purchase_order', 'id, voucher_no', ['delivery_status' => 'purchase_in', 'is_deleted' => '0', 'method' => 'import', 'company_id' => $company_id]);
            $page_data['po'] = ($pos != '') ? $pos : [];

            $page_data['page_name']  = 'batch_detail_new';
            // $page_data['page_name']  = 'batch_detail';
            $page_data['page_title'] = get_phrase('batch_detail');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_batch_detail_new_data()
    {
        if ($this->session->userdata('inventory_login') != true) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 401,
                'message' => 'Unauthorized'
            ));
            return;
        }

        if (!$this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'Invalid request'
            ));
            return;
        }

        $batch_no = trim((string)$this->input->post('batch_no', true));
        $company_id = (int)$this->session->userdata('company_id');

        if ($batch_no === '') {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'Batch No is required'
            ));
            return;
        }

        $po = $this->db->query("
            SELECT
                id,
                voucher_no,
                boe_no,
                boe_date,
                completed_date,
                loading_date,
                date
            FROM purchase_order
            WHERE voucher_no = ?
              AND is_deleted = 0
              AND method = 'import'
              AND company_id = ?
            LIMIT 1
        ", array($batch_no, $company_id))->row_array();

        if (empty($po)) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 404,
                'message' => 'Batch not found'
            ));
            return;
        }

        $rows = $this->db->query("
            SELECT
                pp.id,
                pp.parent_id,
                pp.product_id,
                pp.supplier_id,
                COALESCE(s.name, 'Unknown Supplier') AS supplier_name,
                pp.product_name,
                pp.item_code,
                pp.hsn_code,
                pp.official_ci_qty,
                pp.black_qty,
                pp.actual_qty,
                pp.cbm,
                pp.unit_price_rmb,
                pp.actual_usd,
                pp.actual_inr,
                pp.official_ci_unit_price_usd,
                pp.official_rate_rs,
                pp.official_total_rs,
                pp.duty_percent,
                pp.duty_amt,
                pp.duty_surcharge,
                pp.taxable_value,
                pp.gst_amt,
                pp.invoice,
                pp.invoice_date,
                pp.invoice_supplier_id,
                pop.id AS purchase_order_product_id
            FROM purchase_in_product pp
            LEFT JOIN supplier s
                ON s.id = pp.supplier_id
            LEFT JOIN purchase_order_product pop
                ON pop.parent_id = pp.parent_id
               AND pop.product_id = pp.product_id
               AND pop.supplier_id = pp.supplier_id
            WHERE pp.parent_id = ?
              AND pp.is_deleted = 0
            ORDER BY pp.supplier_id ASC, pp.id ASC
        ", array($po['id']))->result_array();

        $sum_keys = array(
            'official_qty',
            'black_qty',
            'act_qty',
            'cbm_per_pc',
            'off_cbm_total',
            'actual_cbm_total',
            'rmb_per_pc',
            'rmb_total',
            'total_off_rmb',
            'usd_per_pc',
            'usd_total',
            'cost_without_expense_rs',
            'total_rs_without_expense',
            'off_usd_per_pc',
            'total_off_usd',
            'off_rs_per_pc',
            'total_off_rs',
            'off_duty_percent',
            'off_duty_amt',
            'off_surcharge',
            'off_taxable_value',
            'off_gst_percent',
            'off_gst_amt',
            'total_duty_gst'
        );

        $initTotals = function () use ($sum_keys) {
            $totals = array();
            foreach ($sum_keys as $k) {
                $totals[$k] = 0;
            }
            return $totals;
        };

        $suppliers = array();
        $grand_totals = $initTotals();

        foreach ($rows as $r) {
            $supplier_id = (int)($r['supplier_id'] ?? 0);
            if (!isset($suppliers[$supplier_id])) {
                $suppliers[$supplier_id] = array(
                    'supplier_id' => $supplier_id,
                    'supplier_name' => (string)($r['supplier_name'] ?? 'Unknown Supplier'),
                    'invoice' => '',
                    'invoice_date' => '',
                    'products' => array(),
                    'totals' => $initTotals()
                );
            }

            if (!empty($r['invoice']) && isset($r['invoice_supplier_id']) && (int)$r['invoice_supplier_id'] === $supplier_id) {
                $suppliers[$supplier_id]['invoice'] = (string)$r['invoice'];
                $suppliers[$supplier_id]['invoice_date'] = !empty($r['invoice_date']) ? date('Y-m-d', strtotime($r['invoice_date'])) : '';
            }

            $official_qty = (float)($r['official_ci_qty'] ?? 0);
            $black_qty = (float)($r['black_qty'] ?? 0);
            $act_qty = (float)($r['actual_qty'] ?? 0);
            $cbm_per_pc = (float)($r['cbm'] ?? 0);
            $off_cbm_total = $official_qty * $cbm_per_pc;
            $actual_cbm_total = $act_qty * $cbm_per_pc;

            $rmb_per_pc = (float)($r['unit_price_rmb'] ?? 0);
            $rmb_total = $act_qty * $rmb_per_pc;
            $total_off_rmb = $official_qty * $rmb_per_pc;
            $usd_per_pc = (float)($r['actual_usd'] ?? 0);
            $usd_total = $act_qty * $usd_per_pc;
            $cost_without_expense_rs = (float)($r['actual_inr'] ?? 0);
            $total_rs_without_expense = $cost_without_expense_rs * $act_qty;
            $off_usd_per_pc = (float)($r['official_ci_unit_price_usd'] ?? 0);
            $total_off_usd = $official_qty * $off_usd_per_pc;
            $off_rs_per_pc = (float)($r['official_rate_rs'] ?? 0);
            $total_off_rs = (float)($r['official_total_rs'] ?? 0);
            $off_duty_percent = (float)($r['duty_percent'] ?? 0);
            $off_duty_amt = (float)($r['duty_amt'] ?? 0);
            $off_surcharge = (float)($r['duty_surcharge'] ?? 0);
            $off_taxable_value = (float)($r['taxable_value'] ?? 0);
            $off_gst_amt = (float)($r['gst_amt'] ?? 0);
            $off_gst_percent = ($off_taxable_value > 0) ? (($off_gst_amt * 100) / $off_taxable_value) : 0;
            $total_duty_gst = $off_duty_amt + $off_gst_amt + $r['duty_percent'];

            $line = array(
                'id' => (int)($r['id'] ?? 0),
                'purchase_order_product_id' => (int)($r['purchase_order_product_id'] ?? 0),
                'product_id' => (int)($r['product_id'] ?? 0),
                'product_name' => (string)($r['product_name'] ?? ''),
                'model_no' => (string)($r['item_code'] ?? ''),
                'hsn_code' => (string)($r['hsn_code'] ?? ''),
                'official_qty' => $official_qty,
                'black_qty' => $black_qty,
                'act_qty' => $act_qty,
                'cbm_per_pc' => $cbm_per_pc,
                'off_cbm_total' => $off_cbm_total,
                'actual_cbm_total' => $actual_cbm_total,
                'rmb_per_pc' => $rmb_per_pc,
                'rmb_total' => $rmb_total,
                'total_off_rmb' => $total_off_rmb,
                'usd_per_pc' => $usd_per_pc,
                'usd_total' => $usd_total,
                'cost_without_expense_rs' => $cost_without_expense_rs,
                'total_rs_without_expense' => $total_rs_without_expense,
                'off_usd_per_pc' => $off_usd_per_pc,
                'total_off_usd' => $total_off_usd,
                'off_rs_per_pc' => $off_rs_per_pc,
                'total_off_rs' => $total_off_rs,
                'off_duty_percent' => $off_duty_percent,
                'off_duty_amt' => $off_duty_amt,
                'off_surcharge' => $off_surcharge,
                'off_taxable_value' => $off_taxable_value,
                'off_gst_percent' => $off_gst_percent,
                'off_gst_amt' => $off_gst_amt,
                'total_duty_gst' => $total_duty_gst
            );

            $suppliers[$supplier_id]['products'][] = $line;
            foreach ($sum_keys as $k) {
                $suppliers[$supplier_id]['totals'][$k] += (float)$line[$k];
                $grand_totals[$k] += (float)$line[$k];
            }
        }

        $all_batch_expenses = $this->db->query("
            SELECT supplier_id, sub_total, type 
            FROM po_expense 
            WHERE batch_no = ? AND is_delete = 0
        ", array($batch_no));

        $expenses = ($all_batch_expenses->num_rows() > 0) ? $all_batch_expenses->result_array() : [];
        // echo json_encode($suppliers); exit();
        // echo $this->db->last_query(); exit();

        $g_expense = 0;
        $g_total_expense = 0;
        $g_off_expense = 0;
        $g_off_total_expense = 0;
        $g_off_per_pc = 0;
        $g_cost_without_exp = 0;
        foreach ($suppliers as &$supplier) {
            // Actual Expense
            $act_exp = [];
            foreach ($expenses as $exp) {
                $exp_suppliers = explode(',', $exp["supplier_id"]);
                
                // Supplier Total
                $total_act_cbm = 0;
                // foreach ($suppliers as &$supp) {
                //     if(in_array($supp['supplier_id'], $exp_suppliers)) {
                //         $total_act_cbm += $supp['totals']['actual_cbm_total'];
                //     }
                // }

                if(in_array($supplier['supplier_id'], $exp_suppliers)) {
                    foreach($exp_suppliers as $exp_sup) {
                        if($suppliers[$exp_sup]['totals']['actual_cbm_total']) {
                            $total_act_cbm += $suppliers[$exp_sup]['totals']['actual_cbm_total'];
                        }
                    }
                }

                // avg expense
                if($total_act_cbm > 0) {
                    $act_exp[] = $exp['sub_total'] / $total_act_cbm;
                }
            }
           
            // Official Expense
            $off_exp = [];
            foreach ($expenses as $exp) {
                if($exp['type'] == 'official') {
                    $exp_suppliers = explode(',', $exp["supplier_id"]);
    
                    // Supplier Total
                    $total_off_cbm = 0;
                    // foreach ($suppliers as &$supp) {
                    //     if(in_array($supp['supplier_id'], $exp_suppliers)) {
                    //         $total_off_cbm += $supp['totals']['off_cbm_total'];
                    //     }
                    // }

                    if(in_array($supplier['supplier_id'], $exp_suppliers)) {
                        foreach($exp_suppliers as $exp_sup) {
                            if($suppliers[$exp_sup]['totals']['off_cbm_total']) {
                                $total_off_cbm += $suppliers[$exp_sup]['totals']['off_cbm_total'];
                            }
                        }
                    }
    
                    // avg expense
                    if($total_off_cbm > 0) {
                        $off_exp[] = $exp['sub_total'] / $total_off_cbm;
                    }
                }
            }

            $t_expense = 0;
            $t_total_expense = 0;
            $t_off_expense = 0;
            $t_off_total_expense = 0;
            $t_off_per_pc = 0;
            $t_cost_without_exp = 0;
            foreach ($supplier['products'] as &$product) {
                $p_expense = (count($act_exp) > 0) ? (array_sum($act_exp) * $product['actual_cbm_total']) : 0;
                $p_total_expense = $p_expense + $product['total_rs_without_expense'] + $product['off_duty_amt'] + $product['off_surcharge'];
                $p_off_expense = (count($off_exp) > 0) ? (array_sum($off_exp) * $product['off_cbm_total']) : 0;
                $p_off_total_expense = $p_off_expense + $product['total_off_rs'] + $product['off_duty_amt'] + $product['off_surcharge'];
                $p_off_per_pc = ($product['official_qty'] > 0) ? $p_off_total_expense / $product['official_qty'] : 0;
                $p_cost_without_exp = ($product['act_qty'] > 0) ? $p_total_expense / $product['act_qty'] : 0;
                
                // Updating Product Row
                $product['expense'] = $p_expense;
                $product['total_expense'] = $p_total_expense;
                $product['official_expense'] = $p_off_expense;
                $product['total_official_expense'] = $p_off_total_expense;
                $product['official_exp_per_pc'] = $p_off_per_pc;
                $product['actual_cost_with_exp'] = $p_cost_without_exp;

                // Updating supplier total
                $t_expense += $p_expense;
                $t_total_expense += $p_total_expense;
                $t_off_expense += $p_off_expense;
                $t_off_total_expense += $p_off_total_expense;
                $t_off_per_pc += $p_off_per_pc;
                $t_cost_without_exp += $p_cost_without_exp;
            }

            // Updating Supplier wise total
            $supplier['totals']['expense'] = $t_expense;
            $supplier['totals']['total_expense'] = $t_total_expense;
            $supplier['totals']['official_expense'] = $t_off_expense;
            $supplier['totals']['total_official_expense'] = $t_off_total_expense;
            $supplier['totals']['official_exp_per_pc'] = $t_off_per_pc;
            $supplier['totals']['actual_cost_with_exp'] = $t_cost_without_exp;

            // Updating grand total
            $g_expense += $t_expense;
            $g_total_expense += $t_total_expense;
            $g_off_expense += $t_off_expense;
            $g_off_total_expense += $t_off_total_expense;
            $g_off_per_pc += $t_off_per_pc;
            $g_cost_without_exp += $t_cost_without_exp;

            unset($product); 
        }

        // Updating Grand total
        $grand_totals['expense'] = $g_expense;
        $grand_totals['total_expense'] = $g_total_expense;
        $grand_totals['official_expense'] = $g_off_expense;
        $grand_totals['total_official_expense'] = $g_off_total_expense;
        $grand_totals['official_exp_per_pc'] = $g_off_per_pc;
        $grand_totals['actual_cost_with_exp'] = $g_cost_without_exp;

        unset($supplier);

        $suppliers = array_values($suppliers);
        $expense_items = $this->db->query("
            SELECT 
                ed.expense_name, 
                ed.amount, 
                ed.gst_amt, 
                ed.total_amt
            FROM po_expense e
            JOIN po_expense_details ed ON e.id = ed.parent_id
            WHERE e.batch_no = ? AND e.is_delete = 0
            ORDER BY ed.id ASC
        ", array($batch_no))->result_array();

        $supplier_accounts = [];
        $cutoff_date = !empty($po['completed_date']) ? $po['completed_date'] : $po['date'];

        foreach ($suppliers as $s) {
            $s_id = (int)$s['supplier_id'];
            $s_name = (string)$s['supplier_name'];

            $purchases = $this->db->query("
                SELECT 
                    SUM(pp.actual_qty * pp.unit_price_rmb) as rmb,
                    SUM(pp.actual_qty * pp.actual_usd) as usd,
                    SUM(pp.actual_qty * pp.actual_inr) as inr
                FROM purchase_order po
                JOIN purchase_in_product pp ON po.id = pp.parent_id
                WHERE pp.supplier_id = ?
                  AND po.delivery_status = 'purchase_in'
                  AND po.is_deleted = 0
                  AND po.date < ?
            ", array($s_id, $cutoff_date))->row_array();

            $old_payments = $this->db->query("
                SELECT 
                    SUM(amount_rmb) as rmb,
                    SUM(amount_dollar) as usd,
                    SUM(amount_rs) as inr
                FROM payments
                WHERE supplier_id = ?
                  AND is_delete = 0
                  AND payment_date < ?
            ", array($s_id, $cutoff_date))->row_array();

            $outstanding = [
                'date' => $cutoff_date,
                'rmb' => (float)($purchases['rmb'] ?? 0) - (float)($old_payments['rmb'] ?? 0),
                'usd' => (float)($purchases['usd'] ?? 0) - (float)($old_payments['usd'] ?? 0),
                'inr' => (float)($purchases['inr'] ?? 0) - (float)($old_payments['inr'] ?? 0)
            ];

            $batch_payments_raw = $this->db->query("
                SELECT payment_date as date, amount_rmb as rmb, amount_dollar as usd, amount_rs as inr
                FROM payments
                WHERE supplier_id = ?
                  AND batch_no = ?
                  AND is_delete = 0
                ORDER BY payment_date ASC
            ", array($s_id, $batch_no))->result_array();

            $batch_payments = [];
            $pay_total_rmb = 0; $pay_total_usd = 0; $pay_total_inr = 0;
            foreach ($batch_payments_raw as $p) {
                $batch_payments[] = [
                    'date' => $p['date'],
                    'rmb' => (float)$p['rmb'],
                    'usd' => (float)$p['usd'],
                    'inr' => (float)$p['inr']
                ];
                $pay_total_rmb += (float)$p['rmb'];
                $pay_total_usd += (float)$p['usd'];
                $pay_total_inr += (float)$p['inr'];
            }

            $total_row = [
                'rmb' => $outstanding['rmb'] + $pay_total_rmb,
                'usd' => $outstanding['usd'] + $pay_total_usd,
                'inr' => $outstanding['inr'] + $pay_total_inr
            ];

            $loaded_row = [
                'rmb' => (float)$s['totals']['total_off_rmb'],
                'usd' => (float)$s['totals']['total_off_usd'],
                'inr' => (float)$s['totals']['total_off_rs']
            ];

            $balance_row = [
                'rmb' => $total_row['rmb'] - $loaded_row['rmb'],
                'usd' => $total_row['usd'] - $loaded_row['usd'],
                'inr' => $total_row['inr'] - $loaded_row['inr']
            ];

            $supplier_accounts[] = [
                'supplier_id' => $s_id,
                'supplier_name' => $s_name,
                'outstanding' => $outstanding,
                'payments' => $batch_payments,
                'total' => $total_row,
                'loaded' => $loaded_row,
                'balance' => $balance_row
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 200,
            'message' => 'success',
            'batch_no' => $batch_no,
            'header' => array(
                'po_id' => (int)$po['id'],
                'boe_no' => (string)($po['boe_no'] ?? ''),
                'boe_date' => !empty($po['boe_date']) ? date('Y-m-d', strtotime($po['boe_date'])) : '',
                'received_date' => !empty($po['completed_date']) ? date('Y-m-d', strtotime($po['completed_date'])) : '',
                'loading_date' => !empty($po['loading_date']) ? date('Y-m-d', strtotime($po['loading_date'])) : '',
                'po_date' => !empty($po['date']) ? date('Y-m-d', strtotime($po['date'])) : ''
            ),
            'suppliers' => $suppliers,
            'grand_totals' => $grand_totals,
            'expense_items' => $expense_items,
            'supplier_accounts' => $supplier_accounts
        ));
    }

    public function get_batch_detail_data()
    {
        if ($this->session->userdata('inventory_login') != true) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 401,
                'message' => 'Unauthorized'
            ));
            return;
        }

        if (!$this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'Invalid request'
            ));
            return;
        }

        $batch_no = trim((string)$this->input->post('batch_no', true));
        $company_id = (int)$this->session->userdata('company_id');

        if ($batch_no === '') {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 400,
                'message' => 'Batch No is required'
            ));
            return;
        }

        $po = $this->db->query("
            SELECT
                id,
                voucher_no,
                boe_no,
                boe_date,
                completed_date,
                loading_date,
                date
            FROM purchase_order
            WHERE voucher_no = ?
              AND is_deleted = 0
              AND method = 'import'
              AND company_id = ?
            LIMIT 1
        ", array($batch_no, $company_id))->row_array();

        if (empty($po)) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 404,
                'message' => 'Batch not found'
            ));
            return;
        }

        $rows = $this->db->query("
            SELECT
                pp.id,
                pp.parent_id,
                pp.product_id,
                pp.supplier_id,
                COALESCE(s.name, 'Unknown Supplier') AS supplier_name,
                pp.product_name,
                pp.item_code,
                pp.hsn_code,
                pp.official_ci_qty,
                pp.black_qty,
                pp.actual_qty,
                pp.cbm,
                pp.unit_price_rmb,
                pp.actual_usd,
                pp.actual_inr,
                pp.official_ci_unit_price_usd,
                pp.official_rate_rs,
                pp.official_total_rs,
                pp.duty_percent,
                pp.duty_amt,
                pp.duty_surcharge,
                pp.taxable_value,
                pp.gst_amt,
                pp.invoice,
                pp.invoice_date,
                pp.invoice_supplier_id,
                pop.id AS purchase_order_product_id
            FROM purchase_in_product pp
            LEFT JOIN supplier s
                ON s.id = pp.supplier_id
            LEFT JOIN purchase_order_product pop
                ON pop.parent_id = pp.parent_id
               AND pop.product_id = pp.product_id
               AND pop.supplier_id = pp.supplier_id
            WHERE pp.parent_id = ?
              AND pp.is_deleted = 0
            ORDER BY pp.supplier_id ASC, pp.id ASC
        ", array($po['id']))->result_array();

        $sum_keys = array(
            'official_qty',
            'black_qty',
            'act_qty',
            'cbm_per_pc',
            'off_cbm_total',
            'actual_cbm_total',
            'rmb_per_pc',
            'rmb_total',
            'total_off_rmb',
            'usd_per_pc',
            'usd_total',
            'cost_without_expense_rs',
            'total_rs_without_expense',
            'off_usd_per_pc',
            'total_off_usd',
            'off_rs_per_pc',
            'total_off_rs',
            'off_duty_percent',
            'off_duty_amt',
            'off_surcharge',
            'off_taxable_value',
            'off_gst_percent',
            'off_gst_amt',
            'total_duty_gst'
        );

        $initTotals = function () use ($sum_keys) {
            $totals = array();
            foreach ($sum_keys as $k) {
                $totals[$k] = 0;
            }
            return $totals;
        };

        $suppliers = array();
        $grand_totals = $initTotals();

        foreach ($rows as $r) {
            $supplier_id = (int)($r['supplier_id'] ?? 0);
            if (!isset($suppliers[$supplier_id])) {
                $suppliers[$supplier_id] = array(
                    'supplier_id' => $supplier_id,
                    'supplier_name' => (string)($r['supplier_name'] ?? 'Unknown Supplier'),
                    'invoice' => '',
                    'invoice_date' => '',
                    'products' => array(),
                    'totals' => $initTotals()
                );
            }

            if (!empty($r['invoice']) && isset($r['invoice_supplier_id']) && (int)$r['invoice_supplier_id'] === $supplier_id) {
                $suppliers[$supplier_id]['invoice'] = (string)$r['invoice'];
                $suppliers[$supplier_id]['invoice_date'] = !empty($r['invoice_date']) ? date('Y-m-d', strtotime($r['invoice_date'])) : '';
            }

            $official_qty = (float)($r['official_ci_qty'] ?? 0);
            $black_qty = (float)($r['black_qty'] ?? 0);
            $act_qty = (float)($r['actual_qty'] ?? 0);
            $cbm_per_pc = (float)($r['cbm'] ?? 0);
            $off_cbm_total = $official_qty * $cbm_per_pc;
            $actual_cbm_total = $act_qty * $cbm_per_pc;

            $rmb_per_pc = (float)($r['unit_price_rmb'] ?? 0);
            $rmb_total = $act_qty * $rmb_per_pc;
            $total_off_rmb = $official_qty * $rmb_per_pc;
            $usd_per_pc = (float)($r['actual_usd'] ?? 0);
            $usd_total = $act_qty * $usd_per_pc;
            $cost_without_expense_rs = (float)($r['actual_inr'] ?? 0);
            $total_rs_without_expense = $cost_without_expense_rs * $act_qty;
            $off_usd_per_pc = (float)($r['official_ci_unit_price_usd'] ?? 0);
            $total_off_usd = $official_qty * $off_usd_per_pc;
            $off_rs_per_pc = (float)($r['official_rate_rs'] ?? 0);
            $total_off_rs = (float)($r['official_total_rs'] ?? 0);
            $off_duty_percent = (float)($r['duty_percent'] ?? 0);
            $off_duty_amt = (float)($r['duty_amt'] ?? 0);
            $off_surcharge = (float)($r['duty_surcharge'] ?? 0);
            $off_taxable_value = (float)($r['taxable_value'] ?? 0);
            $off_gst_amt = (float)($r['gst_amt'] ?? 0);
            $off_gst_percent = ($off_taxable_value > 0) ? (($off_gst_amt * 100) / $off_taxable_value) : 0;
            $total_duty_gst = $off_duty_amt + $off_gst_amt;

            $line = array(
                'id' => (int)($r['id'] ?? 0),
                'purchase_order_product_id' => (int)($r['purchase_order_product_id'] ?? 0),
                'product_id' => (int)($r['product_id'] ?? 0),
                'product_name' => (string)($r['product_name'] ?? ''),
                'model_no' => (string)($r['item_code'] ?? ''),
                'hsn_code' => (string)($r['hsn_code'] ?? ''),
                'official_qty' => $official_qty,
                'black_qty' => $black_qty,
                'act_qty' => $act_qty,
                'cbm_per_pc' => $cbm_per_pc,
                'off_cbm_total' => $off_cbm_total,
                'actual_cbm_total' => $actual_cbm_total,
                'rmb_per_pc' => $rmb_per_pc,
                'rmb_total' => $rmb_total,
                'total_off_rmb' => $total_off_rmb,
                'usd_per_pc' => $usd_per_pc,
                'usd_total' => $usd_total,
                'cost_without_expense_rs' => $cost_without_expense_rs,
                'total_rs_without_expense' => $total_rs_without_expense,
                'off_usd_per_pc' => $off_usd_per_pc,
                'total_off_usd' => $total_off_usd,
                'off_rs_per_pc' => $off_rs_per_pc,
                'total_off_rs' => $total_off_rs,
                'off_duty_percent' => $off_duty_percent,
                'off_duty_amt' => $off_duty_amt,
                'off_surcharge' => $off_surcharge,
                'off_taxable_value' => $off_taxable_value,
                'off_gst_percent' => $off_gst_percent,
                'off_gst_amt' => $off_gst_amt,
                'total_duty_gst' => $total_duty_gst
            );

            $suppliers[$supplier_id]['products'][] = $line;
            foreach ($sum_keys as $k) {
                $suppliers[$supplier_id]['totals'][$k] += (float)$line[$k];
                $grand_totals[$k] += (float)$line[$k];
            }
        }

        $all_batch_expenses = $this->db->query("
            SELECT supplier_id, sub_total 
            FROM po_expense 
            WHERE batch_no = ? AND is_delete = 0
        ", array($batch_no))->result_array();

        $expenses = [];
        foreach ($suppliers as $s_id => $s_data) {
            $supplier_total = 0;
            foreach ($all_batch_expenses as $exp) {
                $exp_supplier_ids = explode(',', (string)($exp['supplier_id'] ?? ''));
                $exp_supplier_ids = array_filter(array_map('trim', $exp_supplier_ids));
                
                if (in_array((string)$s_id, $exp_supplier_ids)) {
                    $count = count($exp_supplier_ids);
                    if ($count > 0) {
                        $supplier_total += (float)$exp['sub_total'] / $count;
                    }
                }
            }
            $expenses[] = array(
                'supplier_id' => $s_id,
                'sub_total' => $supplier_total
            );

            // $supplier_total = 0;
            // foreach ($all_batch_expenses as $exp) {
            //     $exp_supplier_ids = explode(',', (string)($exp['supplier_id'] ?? ''));
            //     $exp_supplier_ids = array_filter(array_map('trim', $exp_supplier_ids));
                
            //     if (in_array((string)$s_id, $exp_supplier_ids)) {
            //         $count = count($exp_supplier_ids);
            //         if ($count > 0) {
            //             $supplier_total += (float)$exp['sub_total'] / $count;
            //         }
            //     }
            // }
            // $expenses[] = array(
            //     'supplier_id' => $s_id,
            //     'sub_total' => $supplier_total
            // );
        }

        $suppliers = array_values($suppliers);
        $expense_items = $this->db->query("
            SELECT 
                ed.expense_name, 
                ed.amount, 
                ed.gst_amt, 
                ed.total_amt
            FROM po_expense e
            JOIN po_expense_details ed ON e.id = ed.parent_id
            WHERE e.batch_no = ? AND e.is_delete = 0
            ORDER BY ed.id ASC
        ", array($batch_no))->result_array();

        $supplier_accounts = [];
        $cutoff_date = !empty($po['completed_date']) ? $po['completed_date'] : $po['date'];

        foreach ($suppliers as $s) {
            $s_id = (int)$s['supplier_id'];
            $s_name = (string)$s['supplier_name'];

            $purchases = $this->db->query("
                SELECT 
                    SUM(pp.actual_qty * pp.unit_price_rmb) as rmb,
                    SUM(pp.actual_qty * pp.actual_usd) as usd,
                    SUM(pp.actual_qty * pp.actual_inr) as inr
                FROM purchase_order po
                JOIN purchase_in_product pp ON po.id = pp.parent_id
                WHERE pp.supplier_id = ?
                  AND po.delivery_status = 'purchase_in'
                  AND po.is_deleted = 0
                  AND po.date < ?
            ", array($s_id, $cutoff_date))->row_array();

            $old_payments = $this->db->query("
                SELECT 
                    SUM(amount_rmb) as rmb,
                    SUM(amount_dollar) as usd,
                    SUM(amount_rs) as inr
                FROM payments
                WHERE supplier_id = ?
                  AND is_delete = 0
                  AND payment_date < ?
            ", array($s_id, $cutoff_date))->row_array();

            $outstanding = [
                'date' => $cutoff_date,
                'rmb' => (float)($purchases['rmb'] ?? 0) - (float)($old_payments['rmb'] ?? 0),
                'usd' => (float)($purchases['usd'] ?? 0) - (float)($old_payments['usd'] ?? 0),
                'inr' => (float)($purchases['inr'] ?? 0) - (float)($old_payments['inr'] ?? 0)
            ];

            $batch_payments_raw = $this->db->query("
                SELECT payment_date as date, amount_rmb as rmb, amount_dollar as usd, amount_rs as inr
                FROM payments
                WHERE supplier_id = ?
                  AND batch_no = ?
                  AND is_delete = 0
                ORDER BY payment_date ASC
            ", array($s_id, $batch_no))->result_array();

            $batch_payments = [];
            $pay_total_rmb = 0; $pay_total_usd = 0; $pay_total_inr = 0;
            foreach ($batch_payments_raw as $p) {
                $batch_payments[] = [
                    'date' => $p['date'],
                    'rmb' => (float)$p['rmb'],
                    'usd' => (float)$p['usd'],
                    'inr' => (float)$p['inr']
                ];
                $pay_total_rmb += (float)$p['rmb'];
                $pay_total_usd += (float)$p['usd'];
                $pay_total_inr += (float)$p['inr'];
            }

            $total_row = [
                'rmb' => $outstanding['rmb'] + $pay_total_rmb,
                'usd' => $outstanding['usd'] + $pay_total_usd,
                'inr' => $outstanding['inr'] + $pay_total_inr
            ];

            $loaded_row = [
                'rmb' => (float)$s['totals']['total_off_rmb'],
                'usd' => (float)$s['totals']['total_off_usd'],
                'inr' => (float)$s['totals']['total_off_rs']
            ];

            $balance_row = [
                'rmb' => $total_row['rmb'] - $loaded_row['rmb'],
                'usd' => $total_row['usd'] - $loaded_row['usd'],
                'inr' => $total_row['inr'] - $loaded_row['inr']
            ];

            $supplier_accounts[] = [
                'supplier_id' => $s_id,
                'supplier_name' => $s_name,
                'outstanding' => $outstanding,
                'payments' => $batch_payments,
                'total' => $total_row,
                'loaded' => $loaded_row,
                'balance' => $balance_row
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 200,
            'message' => 'success',
            'batch_no' => $batch_no,
            'header' => array(
                'po_id' => (int)$po['id'],
                'boe_no' => (string)($po['boe_no'] ?? ''),
                'boe_date' => !empty($po['boe_date']) ? date('Y-m-d', strtotime($po['boe_date'])) : '',
                'received_date' => !empty($po['completed_date']) ? date('Y-m-d', strtotime($po['completed_date'])) : '',
                'loading_date' => !empty($po['loading_date']) ? date('Y-m-d', strtotime($po['loading_date'])) : '',
                'po_date' => !empty($po['date']) ? date('Y-m-d', strtotime($po['date'])) : ''
            ),
            'suppliers' => $suppliers,
            'grand_totals' => $grand_totals,
            'expenses' => $expenses,
            'expense_items' => $expense_items,
            'supplier_accounts' => $supplier_accounts
        ));
    }

    public function my_stock($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_purchase_order($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_purchase_order($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_purchase_order($param2);
        } elseif ($param1 == "delete_inv") {
            $this->inventory_model->delete_inv_purchase_order($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $page_data['warehouse_list']     = $this->common_model->getSessionWarehouse();
            $total = $this->inventory_model->get_stock_totals();
            $page_data['total']  = $total;

            $page_data['page_name']  = 'my_stock';
            $page_data['page_title'] = get_phrase('my_stock');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function my_stock_batch($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            //$param1= base64_decode($param1);
            $result = $this->common_model->get_batch_product_1($param1, $param2);
            $quantity = $result['quantity'];
            $quantity = $result['quantity'];
            $name = $result['warehouse_name'] . ' - ' . $result['category'] . ' - ' . $result['product_name'];
            $page_data['product_id']  = $param1;
            $page_data['warehouse_id']  = $param2;
            $page_data['page_name']  = 'my_stock_batch';
            $page_data['page_title'] = get_phrase($name) . ' (' . $quantity . ') ';

            // echo $name; exit();
            $this->load->view('backend/index', $page_data);
        }
    }

    public function my_stock_history($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $product_name = $this->common_model->selectByidParam($param1, 'inventory', 'item_code') . ' - ' . $this->common_model->selectByidParam($param1, 'inventory', 'product_name');
            $quantity = $this->common_model->selectByidParam($param1, 'inventory', 'quantity');

            $page_data['id']  = $param1;
            $page_data['page_name']  = 'my_stock_history';
            $page_data['page_title'] = get_phrase($product_name) . ' (' . $quantity . ') ';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_my_stock()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_my_stock();
        }
    }

    public function update_inventory_product(){
        $this->inventory_model->update_inventory_product();
    }
    
    public function get_my_stock_batch()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_my_stock_batch();
        }
    }

    public function get_my_stock_history()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_my_stock_history();
        }
    }

    public function overall_stock()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $page_data['page_name']  = 'overall_stock';
        $page_data['page_title'] = 'Overall Stock';
        $this->load->view('backend/index', $page_data);
    }

    public function get_overall_stock()
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_overall_stock();
        }
    }

    public function my_stock_company($product_id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['product_id'] = $product_id;
        $page_data['page_name']  = 'overall_stock_company';
        $page_data['page_title'] = 'Product Company Stock';
        $this->load->view('backend/index', $page_data);
    }

    public function get_overall_stock_company()
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_overall_stock_company();
        }
    }

    public function get_product_po_list()
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $product_id = $this->input->post('product_id');
        $company_id = $this->input->post('company_id');
        $status = $this->input->post('status');
        $warehouse_id = $this->input->post('warehouse_id');

        $data['pos'] = $this->inventory_model->get_product_po_list($product_id, $company_id, $status, $warehouse_id);
        $data['status'] = $status;
        
        $this->load->view('backend/inventory/modal_product_po_list', $data);
    }

    public function qc_pending($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();

            $page_data['page_name']  = 'qc_pending';
            $page_data['page_title'] = get_phrase('qc_pending');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_qc_pending()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_qc_pending();
        }
    }

    public function stock_transfer_list($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $page_data['page_name']  = 'stock_transfer_list';
            $page_data['page_title'] = get_phrase('stock_transfer_list');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function stock_transfer($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_stock_transfer($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();

            $page_data['page_name']  = 'stock_transfer';
            $page_data['page_title'] = get_phrase('stock_transfer');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_stock_transfer()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_stock_transfer();
        }
    }

    public function reserved_order($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_reserved_order($param2);
        } elseif ($param1 == "delete_reserved_order") {
            $this->inventory_model->delete_reserved_order();
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'reserved_order';
            $page_data['page_title'] = get_phrase('reserved_order');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function reserved_order_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
        if ($param1 == 'add') {
            $page_data['page_name']  = 'reserved_order_add';
            $page_data['page_title'] = 'Add Reserved Order';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_reserved_order()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_reserved_order();
        }
    }

    public function get_reserved_order_product()
    {
        $id = $this->input->post('id', true);
        $results = $this->inventory_model->get_reserved_order_product($id);
        $i = 1;
        foreach ($results as $item) {
            if ($item['pending'] > 0) {
                $received = '<input type="text" class="form-control" name="received[]" value="0" id="rcv_quantity_' . $item['id'] . '" onkeyup="get_check_rcv_qty();">';
                $received_date = '<input type="date" class="form-control" name="received_date[]" value="' . date('Y-m-d') . '" max="' . date('Y-m-d') . '" id="date_picker">';
                echo '<tr class="element-1 "><td>' . $i . '</td><td><input type="hidden" class="form-control" name="name[]" value="' . $item['name'] . '" ><input type="hidden" name="product_id[]" id="product_id_' . $i . '" value="' . $item['product_id'] . '" ><input type="hidden" name="id[]" id="id_' . $i . '" value="' . $item['id'] . '" ><input type="hidden" class="form-control" name="quantity[]" id="quantity_' . $item['id'] . '" value="' . $item['pending'] . '" >' . $item['name'] . '</td><td>' . $item['batch_no'] . '</td><td>' . $item['pending'] . ' </td><td>' . $received . '</td><td> ' . $received_date . '</td></tr>';
                $i++;
            }
        }
    }

    public function scrap_product($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'scrap_product';
            $page_data['page_title'] = get_phrase('scrap_product');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_scrap_product_history()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_scrap_product_history();
        }
    }

    public function damage_stock_product($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'damage_stock_product';
            $page_data['page_title'] = get_phrase('damage_stock_product');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function move_to_scrap()
    {
        $this->inventory_model->move_to_scrap();
    }

    public function get_damage_stock_product_history()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_damage_stock_product_history();
        }
    }

    public function damage_stock($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_damage_stock($param2);
        } elseif ($param1 == "delete_post") {
            $this->inventory_model->delete_damage_stock($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'damage_stock';
            $page_data['page_title'] = get_phrase('damage_stock');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function damage_stock_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $where = array('is_deleted' => '0');
        $page_data['customer_list']     = $this->common_model->selectWhere('customer', $where, 'ASC', 'name');
        $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');
        $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
        if ($param1 == 'add') {
            $page_data['page_name']  = 'damage_stock_add';
            $page_data['page_title'] = 'Add Damage Stock';
            $this->load->view('backend/index', $page_data);
        } else if ($param1 == 'view') {
            $page_data['id']  = $param2;
            $page_data['page_name']  = 'damage_stock_view';
            $page_data['page_title'] = 'Damage Stock Details ';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_damage_stock()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_damage_stock();
        }
    }

    public function get_damage_stock_history($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_damage_stock_history($id);
        }
    }

    public function get_product_by_warehouse()
    {
        $warehouse_id = $this->input->post('warehouse_id', true);
        $results = $this->inventory_model->get_product_id_by_warehouse($warehouse_id);
        foreach ($results as $item) {
            echo '<option value="' . $item['id'] . '">' . $item['name'] . '</option>';
        }
    }

    public function get_batches_by_warehouse()
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode([]);
            return;
        }
        $warehouse_id = $this->input->post('warehouse_id', true);
        $company_id = $this->session->userdata('company_id');
        $results = $this->inventory_model->get_batches_by_warehouse($warehouse_id, $company_id);
        header('Content-Type: application/json');
        echo json_encode($results);
    }

    public function get_products_by_batch()
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode([]);
            return;
        }
        $warehouse_id = $this->input->post('warehouse_id', true);
        $batch_no = $this->input->post('batch_no', true);
        $company_id = $this->session->userdata('company_id');
        $results = $this->inventory_model->get_products_by_batch($warehouse_id, $company_id, $batch_no);
        header('Content-Type: application/json');
        echo json_encode($results);
    }


    public function get_product_by_company()
    {
        $results = $this->inventory_model->get_product_id_by_company();
        foreach ($results as $item) {
            echo '<option value="' . $item['id'] . '">' . $item['name'] . '</option>';
        }
    }

    public function get_qty_by_product_company()
    {
        $product_id = $this->input->post('product_id', true);
        $customer_id = $this->input->post('customer_id', true);
        $results = $this->inventory_model->get_qty_by_product_company($product_id, $customer_id);
        header('Content-Type: application/json');
        echo json_encode($results);
    }

    public function get_last_selling_price()
    {
        $customer_id = $this->input->post('customer_id', true);
        $product_id = $this->input->post('product_id', true);
        $results = $this->inventory_model->get_last_price_history($customer_id, $product_id);
        
        $html = '<table class="table table-sm table-bordered"><thead><tr><th>Date</th><th>Qty</th><th>Price</th><th>Action</th></tr></thead><tbody>';
        if(empty($results)){
            $html .= '<tr><td colspan="4" class="text-center">No history found</td></tr>';
        } else {
            foreach($results as $row){
                $html .= '<tr><td>'.$row['order_date'].'</td><td>'.$row['qty'].'</td><td>'.number_format($row['last_price'], 2).'</td><td class="text-center"><button type="button" class="btn btn-sm btn-primary apply-price-btn" data-price="'.floatval($row['last_price']).'">Apply</button></td></tr>';
            }
        }
        $html .= '</tbody></table>';
        echo $html;
    }



    public function get_batch_by_product()
    {
        $warehouse_id = $this->input->post('warehouse_id', true);
        $product_id = $this->input->post('product_id', true);
        $results = $this->inventory_model->get_batch_by_itemcode($warehouse_id, $product_id);
        foreach ($results as $item) {
            echo '<option value="' . $item['name'] . '">' . $item['name'] . '</option>';
        }
    }
    public function get_qty_by_product()
    {
        $warehouse_id = $this->input->post('warehouse_id', true);
        $product_id = $this->input->post('product_id', true);
        $results = $this->inventory_model->get_qty_by_product($warehouse_id, $product_id);
    }

    public function get_available_qty()
    {
        $warehouse_id = $this->input->post('warehouse_id', true);
        $product_id = $this->input->post('product_id', true);
        $batch_no = $this->input->post('batch_no', true);
        $results = $this->inventory_model->get_available_qty($warehouse_id, $product_id, $batch_no);
    }

    public function get_purchase_order_1()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_order_1();
        }
    }


    /* Customer Start */
    public function customer($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_customer($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_customer($param2);
        } elseif ($param1 == "move_post") {
            $this->inventory_model->move_to_customer($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_customer($param2);
        } elseif ($param1 == "reassign") {
            $this->inventory_model->reassign_customer();
        } elseif ($param1 == "replicate_post") {
            $this->inventory_model->replicate_customer();
        } elseif ($param1 == "follow") {
            $this->inventory_model->follow_customer();
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'customer';
            $page_data['page_name']  = 'customer';
            $page_data['page_title'] = get_phrase('customer');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function customer_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['states']     = $this->crud_model->get_states_by_country(101);
        $page_data['companies'] = $this->common_model->getSessionCompanies();
        $page_data['navigation']  = 'customer';

        if ($param1 == 'customer_add') {
            $page_data['page_name']  = 'customer_add';
            $page_data['page_title'] = 'Add Customer';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'customer_edit') {
            $data                    = $this->inventory_model->get_customer_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['staffs']      =  $this->inventory_model->get_staff_by_company_ids(explode(',', $data['company_id']), 'array');
            $page_data['page_name']  = 'customer_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Customer';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'customer_ledger') {
            $data                    = $this->inventory_model->get_customer_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['id']         = $param2;
            $page_data['outstanding'] = $this->inventory_model->get_customer_ledger($param2);
            $page_data['payments']    = $this->inventory_model->get_customer_payments_by_id($param2);
            $page_data['page_name']  = 'customer_ledger';
            $page_data['page_title'] = 'Customer Ledger';
            $this->load->view('backend/index', $page_data);
        }
    }


    public function get_staff_by_company_id() {
        $company_id = $this->input->post('company_id', true);
        $this->inventory_model->get_staff_by_company_id($company_id);
    }

    public function get_staff_by_company_ids() {
        $company_id = $this->input->post('company_id', true);
        $this->inventory_model->get_staff_by_company_ids($company_id);
    }

    public function get_customer()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_customer();
        }
    }

    /* Customer End */
    
    /* Leads Start */
    public function leads($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'leads';
            $page_data['status']        = ($param1 == '') ? 'all' : $param1;
            $page_data['page_name']     = 'leads_data';
            // $page_data['page_name']  = 'leads';
            $page_data['page_title'] = get_phrase('leads');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function leads_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['states']    = $this->crud_model->get_states();
        $page_data['companies'] = $this->common_model->getSessionCompanies();
        $page_data['navigation']  = 'leads';

        if ($param1 == 'leads_add') {
            $page_data['page_name']  = 'customer_add';
            $page_data['page_title'] = 'Add Leads';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'leads_edit') {
            $data               = $this->inventory_model->get_customer_by_id($param2)->row_array();
            $page_data['data']  = $data;
            $page_data['citys'] = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['staffs'] = $this->inventory_model->get_staff_by_company_ids(explode(',', $data['company_id']), 'array');

            $page_data['page_name']  = 'customer_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Leads';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'leads_move') {
            $data               = $this->inventory_model->get_customer_by_id($param2)->row_array();
            $page_data['data']  = $data;
            $page_data['citys'] = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['staffs'] = $this->inventory_model->get_staff_by_company_ids(explode(',', $data['company_id']), 'array');

            $page_data['page_name']  = 'leads_move';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Move to Customer';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'leads') {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']    = 'leads';
            $page_data['page_name']     = 'leads_data';
            $page_data['status']        = $param2;
            $page_data['page_title']    = get_phrase('leads');
            $this->load->view('backend/index', $page_data);
        }
    }

    
    /* Leads End */

    /* Sales Order End */

    public function import_order($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $where = array('is_deleted' => '0');
            $page_data['warehouse_list']     = $this->common_model->selectWhere('warehouse', $where, 'ASC', 'name');
            $page_data['customer_list']     = $this->common_model->selectWhere('customer', $where, 'ASC', 'name');
            $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');

            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'import_order';
            $page_data['page_title'] = get_phrase('import_order');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function sales_order($param1 = "", $param2 = "", $param3 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_sales_order($param2);
        } elseif ($param1 == "add_salesman_post") {
            $this->inventory_model->add_sales_order_salesman();
        } elseif ($param1 == "approve_post") {
            $this->inventory_model->approve_sales_order($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_sales_order($param2);
        } elseif ($param1 == "invoice") {
            // $this->inventory_model->create_sales_invoice($param2);

            $type = $param2;
            $id = $param3;
            $receipt_no = sprintf('%05d', $id);
            $this->load->library('pdf');
            $this->load->library('zip');
            
            if($type == "white") {
                $page_data['data'] = $this->inventory_model->get_white_sales_order_details_by_id($id, $type);
                $html_content = $this->load->view('invoice/sales/sales_invoice', $page_data, TRUE);
            } else {
                $page_data['data'] = $this->inventory_model->get_black_sales_order_details_by_id($id, $type);
                $html_content = $this->load->view('invoice/sales/black_sales_invoice', $page_data, TRUE);
            }

            // echo json_encode($page_data['data']); exit();
            $this->pdf->set_paper("A4", "portrait");
            $this->pdf->set_option('isHtml5ParserEnabled', TRUE);
            $this->pdf->load_html($html_content);

            // echo $html_content; exit();
            $this->pdf->render();
            $pdfname = 'invoice_' . $receipt_no . '.pdf';
            $this->pdf->stream($pdfname, array("Attachment" => 0));
            // $output = $this->pdf->output();
            
            // $this->load->library('pdf');
            // $html = $this->pdf_model->view_purchase_order($param1);
            
            // $this->createPDF($html, $param1, true , 'A4','portrait');
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_sales_order($param2);
        } elseif ($param1 == "gen_invoice") {
            $this->inventory_model->gen_invoice_sales_order($param2);
        } elseif ($param1 == "gen_invoice_post") {
            $this->inventory_model->gen_invoice_sales_order_post();
        } elseif ($param1 == "generate_bill_post") {
            $this->inventory_model->generate_bill_sales_order_post();
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $page_data['navigation']  = 'sales_order';
            $page_data['page_name']  = 'sales_order';
            $page_data['page_title'] = get_phrase('sales');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function black_order($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'black_order';
            $page_data['page_title'] = get_phrase('black_order');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function conversion_order($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_conversion_order_post();
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'conversion_order';
            $page_data['page_title'] = get_phrase('sales_conversion');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function conversion_order_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $where = array('is_deleted' => '0');
        $page_data['warehouse_list']     = $this->common_model->selectWhere('warehouse', $where, 'ASC', 'name');
        $page_data['customer_list']     = $this->common_model->getSessionCustomers();
        $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');

        $page_data['other_charges'] = $this->db->get_where('other_charges', ['is_delete' => 0])->result_array();
        $page_data['states'] = $this->crud_model->get_states_by_country(101);
        if ($param1 == 'add') {
            $page_data['order_no']  = $this->inventory_model->get_sales_order_no();
            $page_data['page_name']  = 'conversion_order_add';
            $page_data['navigation']  = 'conversion_order';
            $page_data['page_title'] = 'Add Sales Conversion';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function sales_order_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $where = array('is_deleted' => '0');
        $page_data['warehouse_list']     = $this->common_model->selectWhere('warehouse', $where, 'ASC', 'name');
        $page_data['customer_list']     = $this->common_model->getSessionCustomers();
        $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');

        $page_data['other_charges'] = $this->db->get_where('other_charges', ['is_delete' => 0])->result_array();
        $page_data['states'] = $this->crud_model->get_states_by_country(101);
        if ($param1 == 'add') {
            $page_data['order_no']  = $this->inventory_model->get_sales_order_no();
            
            // Robust check for salesman role (staff_access == 7)
            $staff_access = (int)$this->session->userdata('super_type_id');
            if ($staff_access === 0) {
                $user_id = $this->session->userdata('super_user_id');
                $usr_det = $this->db->get_where('sys_users', array('id' => $user_id))->row();
                $staff_access = (int)($usr_det->staff_access ?? 0);
            }

            $page_data['page_name']  = 'sales_order_add';
            $page_data['navigation']  = 'sales_order';
            $page_data['page_title'] = 'Add Sales';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'invoice_add') {
            $page_data['order_no']  = $this->inventory_model->get_sales_order_no();
            
            // Robust check for salesman role (staff_access == 7)
            $staff_access = (int)$this->session->userdata('super_type_id');
            if ($staff_access === 0) {
                $user_id = $this->session->userdata('super_user_id');
                $usr_det = $this->db->get_where('sys_users', array('id' => $user_id))->row();
                $staff_access = (int)($usr_det->staff_access ?? 0);
            }

            $page_data['page_name']  = 'sales_order_add_salesman';
            $page_data['navigation']  = 'sales_order';
            $page_data['page_title'] = 'Add Sale Order';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'view') {
            $data                    = $this->inventory_model->get_sales_order_by_id($param2)->row_array();
            $page_data['data']       = $data;

            if ($data['customer_id'] != '') {
                $page_data['data']['customer_name'] = $this->common_model->selectByidParam($data['customer_id'], 'customer', 'name');
            }

            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['page_name']  = 'sales_order_view';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'View Sales';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'excel') {
            $data                    = $this->inventory_model->get_sales_order_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['page_name']  = 'sales_order_excel';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'View Not Uploaded Products';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'products') {
            $data                    = $this->inventory_model->get_sales_order_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['page_name']  = 'sales_order_products';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'View Sales Products';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'approve') {
            $data                    = $this->inventory_model->get_sales_order_details($param2);
            $page_data['data']       = $data;
            $page_data['products_list']   = $this->inventory_model->get_product_id_by_warehouse($data['warehouse_id']);
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            
            // Robust check for salesman role (staff_access == 7)
            $staff_access = (int)$this->session->userdata('super_type_id');
            if ($staff_access === 0) {
                $user_id = $this->session->userdata('super_user_id');
                $usr_det = $this->db->get_where('sys_users', array('id' => $user_id))->row();
                $staff_access = (int)($usr_det->staff_access ?? 0);
            }

            $page_data['page_name']  = 'sales_order_approve';
            $page_data['navigation']  = 'sales_order';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Approve Sales Order';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_order') {
            $data                    = $this->inventory_model->get_sales_order_details($param2);
            $page_data['data']       = $data;
            $page_data['products_list']   = $this->inventory_model->get_product_id_by_warehouse($data['warehouse_id']);
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            
            // Robust check for salesman role (staff_access == 7)
            $staff_access = (int)$this->session->userdata('super_type_id');
            if ($staff_access === 0) {
                $user_id = $this->session->userdata('super_user_id');
                $usr_det = $this->db->get_where('sys_users', array('id' => $user_id))->row();
                $staff_access = (int)($usr_det->staff_access ?? 0);
            }

            $page_data['page_name']  = 'sales_edit_approved_order';
            $page_data['navigation']  = 'sales_order';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Sales Order';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit') {
            $data                    = $this->inventory_model->get_sales_order_details($param2);
            $page_data['data']       = $data;
            $page_data['products_list']   = $this->inventory_model->get_product_id_by_warehouse($data['warehouse_id']);
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            
            // Robust check for salesman role (staff_access == 7)
            $staff_access = (int)$this->session->userdata('super_type_id');
            if ($staff_access === 0) {
                $user_id = $this->session->userdata('super_user_id');
                $usr_det = $this->db->get_where('sys_users', array('id' => $user_id))->row();
                $staff_access = (int)($usr_det->staff_access ?? 0);
            }

            // if ($staff_access === 7) {
            //     $page_data['page_name']  = 'sales_order_edit_salesman';
            // } else {
            $page_data['page_name']  = 'sales_order_edit';
            // }

            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Sales';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_product_batch()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_product_batch();
        }
    }

    public function get_batch_details()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_batch_details();
        }
    }

    public function get_batches_by_warehouse_product()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_batches_by_warehouse_product();
        }
    }

    public function get_batch_qty_details()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_batch_qty_details();
        }
    }

    public function get_sales_order()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_sales_order();
        }
    }

    public function get_completed_sales_order()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_completed_sales_order();
        }
    }

    public function invoice_order_print($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $receipt_no = sprintf('%05d', $id);
        $this->load->library('pdf');
        $this->load->library('zip');
        
        $page_data['data'] = $this->inventory_model->get_invoice_order_details_by_id($id);
        if (empty($page_data['data'])) {
            show_404();
        }

        $html_content = $this->load->view('invoice/sales/sales_invoice', $page_data, TRUE);

        $this->pdf->set_paper("A4", "portrait");
        $this->pdf->set_option('isHtml5ParserEnabled', TRUE);
        $this->pdf->load_html($html_content);
        $this->pdf->render();
        
        $pdfname = 'invoice_' . $receipt_no . '.pdf';
        $this->pdf->stream($pdfname, array("Attachment" => 0));
    }

    public function get_black_order()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_black_order();
        }
    }

    public function get_completed_black_order()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_completed_black_order();
        }
    }

    public function get_conversion_order()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_conversion_order();
        }
    }

    public function get_sales_order_products($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_sales_order_products($id);
        }
    }

    public function add_inventory_data()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->inventory_model->add_inventory_data();
    }

    /* Sales Order End */

    /* Payment Reconceliation Start */
    public function payment_reconceliation($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_payment_reconceliation($param2);
        } elseif ($param1 == "delete_post") {
            $this->inventory_model->delete_payment_reconceliation($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'payment_reconceliation';
            $page_data['page_title'] = get_phrase('payment_reconceliation');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function payment_reconceliation_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $where = array('is_deleted' => '0');
        $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
        $page_data['customer_list']     = $this->common_model->selectWhere('customer', $where, 'ASC', 'name');
        $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');

        if ($param1 == 'add') {
            $page_data['page_name']  = 'payment_reconceliation_add';
            $page_data['page_title'] = 'Add Payment Reconceliation';
            $this->load->view('backend/index', $page_data);
        } else if ($param1 == 'view') {
            $order_id = 'KIDS_GR_' . $this->common_model->selectByidParam($param2, 'payment_reconceliation', 'id');
            $page_data['id']  = $param2;
            $page_data['page_name']  = 'payment_reconceliation_view';
            $page_data['page_title'] = 'Order Id : ' . $order_id;
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_payment_reconceliation_history($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_payment_reconceliation_history($id);
        }
    }

    public function get_payment_reconceliation()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_payment_reconceliation();
        }
    }
    /* Payment Reconceliation End */

    /* Goods Return Start */
    public function get_sale_order_items()
    {
        $this->inventory_model->get_sale_order_items();
    }

    public function get_sale_order_product()
    {
        $this->inventory_model->get_sale_order_product();
    }

    public function goods_return($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_goods_return($param2);
        } elseif ($param1 == "delete_post") {
            $this->inventory_model->delete_goods_return($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'goods_return';
            $page_data['page_title'] = get_phrase('sales_return');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function goods_return_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $where = array('is_deleted' => '0');
        $page_data['warehouse_list']     = $this->common_model->getSessionWarehouse();
        $page_data['customer_list']     = $this->common_model->getSessionCustomers();
        $page_data['company_list']     = $this->common_model->selectWhere('company', $where, 'ASC', 'name');

        if ($param1 == 'add') {
            $page_data['page_name']  = 'goods_return_add';
            $page_data['page_title'] = 'Add Sales Return';
            $this->load->view('backend/index', $page_data);
        } else if ($param1 == 'view') {
            $order_id = 'KIDS_GR_' . $this->common_model->selectByidParam($param2, 'goods_return', 'id');
            $page_data['id']  = $param2;
            $page_data['page_name']  = 'goods_return_view';
            $page_data['page_title'] = 'Order Id : ' . $order_id;
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_goods_return()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_goods_return();
        }
    }

    public function get_goods_return_history($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_goods_return_history($id);
        }
    }
    /* Goods Return End */

    /* Company Start */

    public function company($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_company($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_company($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_company($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $page_data['navigation']  = 'company';
            $page_data['page_name']  = 'company';
            $page_data['page_title'] = get_phrase('company');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function company_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['states']     = $this->crud_model->get_states_by_country(101);

        if ($param1 == 'company_add') {
            $page_data['navigation']  = 'company';
            $page_data['page_name']  = 'company_add';
            $page_data['page_title'] = 'Add Supplier';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'company_edit') {
            $data                    = $this->inventory_model->get_company_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['navigation']  = 'company';
            $page_data['page_name']  = 'company_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Supplier';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_company()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_company();
        }
    }

    /* Company End */

    /* My Company Start */

    public function my_company($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_my_company($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_my_company($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_my_company($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());

            $page_data['navigation']  = 'my_company';
            $page_data['page_name']  = 'my_company';
            $page_data['page_title'] = 'My Vendor';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function my_company_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['countries']     = $this->crud_model->get_countries();

        if ($param1 == 'my_company_add') {
            $page_data['states']     = array(); // Start with empty states for add mode
            $page_data['navigation']  = 'my_company';
            $page_data['page_name']  = 'my_company_add';
            $page_data['page_title'] = 'Add Vendor';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'my_company_edit') {
            $data                    = $this->inventory_model->get_my_company_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['states']     = $this->crud_model->get_states_by_country($data['country_id']);
            $page_data['citys']      = $this->crud_model->get_city_by_state($data['state_id']);
            $page_data['navigation'] = 'my_company';
            $page_data['page_name']  = 'my_company_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Vendor';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_my_company()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_my_company();
        }
    }

 
    public function expense_type($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_expense_type($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_expense_type($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_expense_type($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'expense_type';
            $page_data['page_name']  = 'expense_type';
            $page_data['page_title'] = 'Expense Type';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function expense_type_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['navigation']  = 'expense_type';
        if ($param1 == 'expense_type_add') {
            $page_data['page_name']  = 'expense_type_add';
            $page_data['page_title'] = 'Add Expense Type';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'expense_type_edit') {
            $data                    = $this->inventory_model->get_expense_type_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['page_name']  = 'expense_type_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Expense Type';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_expense_type()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_expense_type();
        }
    }

    public function product_unit($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_product_unit($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_product_unit($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_product_unit($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'product_unit';
            $page_data['page_name']  = 'product_unit';
            $page_data['page_title'] = 'Product Unit';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function product_unit_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['navigation']  = 'product_unit';
        if ($param1 == 'product_unit_add') {
            $page_data['page_name']  = 'product_unit_add';
            $page_data['page_title'] = 'Add Product Unit';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'product_unit_edit') {
            $data                    = $this->inventory_model->get_product_unit_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['page_name']  = 'product_unit_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Product Unit';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_product_unit()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_product_unit();
        }
    }

    public function other_charges($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_other_charges($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_other_charges($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_other_charges($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'other_charges';
            $page_data['page_name']  = 'other_charges';
            $page_data['page_title'] = 'Charges';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function other_charges_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['navigation']  = 'other_charges';
        if ($param1 == 'other_charges_add') {
            $page_data['page_name']  = 'other_charges_add';
            $page_data['page_title'] = 'Add Charge';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'other_charges_edit') {
            $data                    = $this->inventory_model->get_other_charges_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['page_name']  = 'other_charges_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Charge';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_other_charges()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_other_charges();
        }
    }
 
    public function bank_accounts($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_bank_accounts($param2);
        } elseif ($param1 == "edit_post") {
            $this->inventory_model->edit_bank_accounts($param2);
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_bank_accounts($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['navigation']  = 'bank_accounts';
            $page_data['page_name']  = 'bank_accounts';
            $page_data['page_title'] = 'Bank Accounts';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function bank_accounts_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['navigation']  = 'bank_accounts';
        if ($param1 == 'bank_accounts_add') {
            $page_data['page_name']  = 'bank_accounts_add';
            $page_data['page_title'] = 'Add Bank Account';
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'bank_accounts_edit') {
            $data                    = $this->inventory_model->get_bank_accounts_by_id($param2)->row_array();
            $page_data['data']       = $data;
            $page_data['page_name']  = 'bank_accounts_edit';
            $page_data['id']         = $param2;
            $page_data['page_title'] = 'Edit Bank Account';
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_bank_accounts()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_bank_accounts();
        }
    }
 

    /* Purchase Return Start */
    public function purchase_return($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "add_post") {
            $this->inventory_model->add_purchase_return($param2);
        } elseif ($param1 == "delete_post") {
            $this->inventory_model->delete_purchase_return($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'purchase_return';
            $page_data['page_title'] = get_phrase('purchase_return');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function purchase_return_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $where = array('is_deleted' => '0');
        $page_data['supplier_list']     = $this->common_model->selectWhere('supplier', $where, 'ASC', 'name');
        $page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
        if ($param1 == 'add') {
            $page_data['page_name']  = 'purchase_return_add';
            $page_data['page_title'] = 'Add Purchase Return';
            $this->load->view('backend/index', $page_data);
        } else if ($param1 == 'view') {
            $order_id = 'GPS_PR_' . $this->common_model->selectByidParam($param2, 'purchase_return', 'id');
            $page_data['id']  = $param2;
            $page_data['page_name']  = 'purchase_return_view';
            $page_data['page_title'] = 'Order Id : ' . $order_id;
            $this->load->view('backend/index', $page_data);
        }
    }

    public function get_purchase_return()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_return();
        }
    }

    public function get_purchase_return_history($id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_return_history($id);
        }
    }
    /* Purchase Return End */


    public function import_purchase_order($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $where = array('is_deleted' => '0');
            $page_data['warehouse_list']     = $this->common_model->selectWhere('warehouse', $where, 'ASC', 'name');
            $page_data['customer_list']     = $this->common_model->selectWhere('customer', $where, 'ASC', 'name');
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'import_purchase_order';
            $page_data['page_title'] = get_phrase('import_purchase_order');
            $this->load->view('backend/index', $page_data);
        }
    }

    // public function inventory_cron($param1 = "", $param2 = "") {
    //     $this->inventory_model->inventory_cron();
    // }

    // public function inventory_manual_update() {
    //     $this->inventory_model->inventory_manual_update();
    // }

    public function inventory_date_update($param1 = "", $param2 = "")
    {
        $this->inventory_model->inventory_date_update();
    }

    // purchase reports

    public function purchase_reports($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->inventory_model->delete_purchase_report($param2);
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'purchase_reports';
            $page_data['page_title'] = get_phrase('purchase_reports');
            $this->load->view('backend/index', $page_data);
        }
    }


    public function get_purchase_reports()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_purchase_reports();
        }
    }

    // sales reports

    public function sales_reports($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'sales_reports';
            $page_data['page_title'] = get_phrase('sales_reports');
            $this->load->view('backend/index', $page_data);
        }
    }


    public function get_sales_reports()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_sales_reports();
        }
    }
    
    // sales_return_reports 

    public function stock_reports($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'stock_reports';
            $page_data['page_title'] = get_phrase('stock_reports');
            $this->load->view('backend/index', $page_data);
        }
    }
    
    public function get_stock_reports()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_stock_reports();
        }
    }

    // sales_return_reports 

    public function sales_return_reports($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        } else {
            $this->session->set_userdata('previous_url', currentUrl());
            $page_data['page_name']  = 'sales_return_reports';
            $page_data['page_title'] = get_phrase('sales_return_reports');
            $this->load->view('backend/index', $page_data);
        }
    }


    public function get_sales_return_reports()
    {
        if ($this->session->userdata('inventory_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->inventory_model->get_sales_return_reports();
        }
    }

    public function update_purchase_order_priority_list()
    {
        $this->inventory_model->update_purchase_order_priority_list();
    }

    public function get_supplier_batches($supplier_id)
    {
        if ($this->session->userdata('inventory_login') != true) {
            echo json_encode([]);
            return;
        }
        $batches = $this->inventory_model->get_batches_by_supplier($supplier_id);
        echo json_encode($batches);
    }
	public function get_customer_sales_orders()
	{
		if ($this->session->userdata('inventory_login') != true) {
			echo "Session expired";
			return;
		}

		$customer_id = $this->input->post('customer_id');
		$payment_type = $this->input->post('payment_type');

		if (!$customer_id || !$payment_type) {
			echo "";
			return;
		}

		$orders = $this->inventory_model->get_unpaid_sales_orders_by_customer($customer_id, $payment_type);

		if (empty($orders)) {
			echo '<div class="alert alert-info">No outstanding orders found for this customer and payment type.</div>';
			return;
		}

		// Render table
		$html = '
		<div class="row mb-1 mt-2">
			<div class="col-12 d-flex justify-content-between align-items-center">
				<h4 class="card-title mb-0">
					<i class="fa fa-list-alt text-warning"></i> Outstanding Invoices ('.count($orders).' Invoices)
				</h4>
				<div>
					<button type="button" class="btn btn-sm btn-outline-info" id="select_all_btn"><i class="fa fa-check-square-o"></i> Select All</button>
					<button type="button" class="btn btn-sm btn-outline-danger" id="clear_all_btn"><i class="fa fa-times-circle-o"></i> Clear All</button>
				</div>
			</div>
		</div>
		<div class="table-responsive">
					<table class="table table-bordered table-sm" id="outstanding_table">
						<thead class="thead-dark" style="background: #1e1e1e;">
							<tr>
								<th class="text-center" style="width: 40px;"><input type="checkbox" id="select_all_checkbox"></th>
								<th>DATE</th>
								<th>ITEM / NO</th>
								<th>REF / DUE</th>
								<th class="text-end">TOTAL</th>
								<th class="text-end">PAID</th>
								<th class="text-end">PENDING</th>
								<th style="width: 250px;">APPLY / PAY</th>
								<th class="text-end">REMAINING</th>
							</tr>
						</thead>
						<tbody>';

		foreach ($orders as $order) {
			$total = ($payment_type == 'official') ? $order['net_sales_value_1'] : $order['total_black_amt'];
			$paid = ($payment_type == 'official') ? $order['total_white_paid'] : $order['total_black_paid'];
			$pending = $total - $paid;

			$html .= '<tr>
						<td class="text-center align-middle">
							<input type="checkbox" class="order_checkbox" name="order_ids[]" value="'.$order['id'].'">
							<input type="hidden" name="order_date['.$order['id'].']" value="'.$order['date'].'">
							<input type="hidden" name="order_no['.$order['id'].']" value="'.$order['order_no'].'">
							<input type="hidden" name="refrence_no['.$order['id'].']" value="'.$order['refrence_no'].'">
							<input type="hidden" name="order_total['.$order['id'].']" value="'.$total.'">
							<input type="hidden" name="order_paid['.$order['id'].']" value="'.$paid.'">
							<input type="hidden" name="order_pending['.$order['id'].']" value="'.$pending.'">
						</td>
						<td class="align-middle">'.date('d M Y', strtotime($order['date'])).'</td>
						<td class="align-middle"><strong>'.$order['order_no'].'</strong></td>
						<td class="align-middle">'.($order['refrence_no'] ?: '-').'</td>
						<td class="text-end align-middle">₹'.number_format($total, 2).'</td>
						<td class="text-end align-middle text-success">₹'.number_format($paid, 2).'</td>
						<td class="text-end align-middle text-primary" data-pending="'.$pending.'">₹'.number_format($pending, 2).'</td>
						<td class="align-middle">
							<input type="number" class="form-control form-control-sm apply_amount" 
								name="apply_amount['.$order['id'].']" 
								data-pending="'.$pending.'"
								value="0.00" min="0" max="'.$pending.'" step="0.01" readonly>
						</td>
						<td class="text-end align-middle remaining_amount">₹'.number_format($pending, 2).'</td>
					</tr>';
		}

		$html .= '</tbody>
					<tfoot style="background: #f8f9fa;">
						<tr>
							<td colspan="7" class="text-end align-middle font-weight-bold">Net Allocation Total:</td>
							<td colspan="2" class="text-end align-middle"><h4 class="mb-0 font-weight-bold" style="color: #ff9f43;" id="net_allocation_total">₹0.00</h4></td>
						</tr>
					</tfoot>
				</table>
			  </div>
			  
			  <div class="row mt-3 p-2 border rounded shadow-sm mx-0 bg-white" style="border-left: 5px solid #ff9f43 !important;">
				<div class="col-md-2 border-end text-center">
					<small class="text-muted d-block font-weight-bold">Invoices Selected</small>
					<h4 class="mb-0" id="summary_selected_count">0</h4>
					<small class="text-muted d-block mt-1">Balance After</small>
					<h5 class="text-primary mb-0 font-weight-bold" id="summary_balance_after">₹0.00</h5>
				</div>
				<div class="col-md-2 border-end text-center">
					<small class="text-muted d-block font-weight-bold">Total Outstanding</small>
					<h4 class="text-danger mb-0 font-weight-bold" id="summary_total_outstanding">₹0.00</h4>
				</div>
				<div class="col-md-2 border-end text-center">
					<small class="text-muted d-block font-weight-bold">Allocated (Inv)</small>
					<h4 class="text-success mb-0 font-weight-bold" id="summary_allocated_inv">₹0.00</h4>
				</div>
				<div class="col-md-2 border-end text-center">
					<small class="text-muted d-block font-weight-bold">Adjustments</small>
					<h4 class="text-info mb-0 font-weight-bold" id="summary_adjustments">₹0.00</h4>
				</div>
				<div class="col-md-2 border-end text-center">
					<small class="text-muted d-block font-weight-bold">Total Tender</small>
					<h4 class="text-primary mb-0 font-weight-bold" id="summary_total_tender">₹0.00</h4>
				</div>
				<div class="col-md-2 text-center">
					<small class="text-muted d-block font-weight-bold">On Account</small>
					<h4 class="text-warning mb-0 font-weight-bold" id="summary_on_account">₹0.00</h4>
				</div>
			</div>
			
			<input type="hidden" name="invoices_selected_count" id="hidden_selected_count" value="0">
			<input type="hidden" name="balance_after" id="hidden_balance_after" value="0">
			<input type="hidden" name="total_outstanding" id="hidden_total_outstanding" value="0">
			<input type="hidden" name="allocated_inv" id="hidden_allocated_inv" value="0">
			<input type="hidden" name="adjustments" id="hidden_adjustments" value="0">
			<input type="hidden" name="total_tender" id="hidden_total_tender" value="0">
			<input type="hidden" name="on_account" id="hidden_on_account" value="0">
			';

		echo $html;
	}

	public function get_customer_credits()
	{
		if ($this->session->userdata('inventory_login') != true) {
			echo "Session expired";
			return;
		}

		$customer_id = $this->input->post('customer_id');

		if (!$customer_id) {
			echo "";
			return;
		}

		$credits = $this->inventory_model->get_customer_credits($customer_id);

		if (empty($credits)) {
			echo '';
			return;
		}

		// Render table
		$html = '
		<div class="row mb-1 mt-3">
			<div class="col-12 d-flex justify-content-between align-items-center">
				<h4 class="card-title mb-0">
					<i class="fa fa-credit-card text-success"></i> Customer Credits ('.count($credits).' Records)
				</h4>
				<div>
					<button type="button" class="btn btn-sm btn-outline-success" id="select_all_credits_btn"><i class="fa fa-check-square-o"></i> Select All</button>
					<button type="button" class="btn btn-sm btn-outline-danger" id="clear_all_credits_btn"><i class="fa fa-times-circle-o"></i> Clear All</button>
				</div>
			</div>
		</div>
		<div class="table-responsive">
					<table class="table table-bordered table-sm" id="credits_table">
						<thead class="thead-dark" style="background: #1e1e1e;">
							<tr>
								<th class="text-center" style="width: 40px;"><input type="checkbox" id="select_all_credits"></th>
								<th>DATE</th>
								<th>ITEM / NO</th>
								<th>REF / DUE</th>
								<th class="text-end">TOTAL</th>
								<th class="text-end">PAID</th>
								<th class="text-end">PENDING</th>
								<th style="width: 250px;">APPLY / PAY</th>
								<th class="text-end">REMAINING</th>
							</tr>
						</thead>
						<tbody>';

		foreach ($credits as $credit) {
			$total = $credit['credit_balance'] - $credit['debit_balance'];
			$pending = $total;

			$html .= '<tr>
						<td class="text-center align-middle">
							<input type="checkbox" class="credit_checkbox" name="credit_ids[]" value="'.$credit['id'].'">
						</td>
						<td class="align-middle">'.date('d M Y', strtotime($credit['date'])).'</td>
						<td class="align-middle"><strong>'.$credit['item_no'].'</strong></td>
						<td class="align-middle">Credit Note</td>
						<td class="text-end align-middle">₹'.number_format($total, 2).'</td>
						<td class="text-end align-middle">-</td>
						<td class="text-end align-middle text-primary" data-pending="'.$pending.'">₹'.number_format($pending, 2).'</td>
						<td class="align-middle">
							<input type="number" class="form-control form-control-sm apply_credit_amount" 
								name="apply_credit_amount['.$credit['id'].']" 
								data-pending="'.$pending.'"
								value="0.00" min="0" max="'.$pending.'" step="0.01" readonly>
						</td>
						<td class="text-end align-middle remaining_credit_amount">₹'.number_format($pending, 2).'</td>
					</tr>';
		}

		$html .= '</tbody>
				</table>
			  </div>';

		echo $html;
	}

	public function get_customer_details_ajax()
	{
		if ($this->session->userdata('inventory_login') != true) {
			echo json_encode(["status" => 401]);
			return;
		}

		$customer_id = $this->input->post('customer_id', true);
		if ($customer_id) {
			$customer = $this->db->get_where('customer', ['id' => $customer_id])->row_array();
			if ($customer) {
				$city_html = '<option value="">Select City</option>';
				if ($customer['state_id']) {
					$cities = $this->crud_model->get_city_by_state($customer['state_id']);
					foreach ($cities as $cit) {
						$selected = ($cit['id'] == $customer['city_id']) ? 'selected' : '';
						$city_html .= '<option value="' . $cit['id'] . '" ' . $selected . '>' . $cit['name'] . '</option>';
					}
				}

				echo json_encode([
					"status" => 200,
					"data" => [
						"state_id" => $customer['state_id'],
						"city_id"  => $customer['city_id'],
						"pincode"  => $customer['pincode'],
						"address"  => $customer['address'],
						"gst_name" => $customer['gst_name'],
						"gst_no"   => $customer['gst_no']
					],
					"city_html" => $city_html
				]);
			} else {
				echo json_encode(["status" => 404]);
			}
		}
	}

	public function product_formula($param1 = "", $param2 = "")
	{
		if ($this->session->userdata('inventory_login') != true) {
			redirect(site_url('login'), 'refresh');
		} elseif ($param1 == "add_post") {
			$this->inventory_model->add_product_formula();
		} elseif ($param1 == "edit_post") {
			$this->inventory_model->edit_product_formula($param2);
		} elseif ($param1 == "delete") {
			$this->inventory_model->delete_product_formula($param2);
		} else {
			$this->session->set_userdata('previous_url', currentUrl());
			$page_data['page_name']  = 'product_formula';
			$page_data['page_title'] = 'Product Formula';
			$this->load->view('backend/index', $page_data);
		}
	}

	public function product_formula_form($param1 = "", $param2 = "")
	{
		if ($this->session->userdata('inventory_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'product_formula_add') {
			// For parent product dropdown: only local products that do NOT have a formula yet and are not deleted
			$page_data['parent_products'] = $this->db->where(array('product_type' => 'local', 'has_formula' => 0, 'is_deleted' => 0))
													->order_by('name', 'ASC')
													->get('raw_products')
													->result_array();

			// For ingredients list: local products that are not deleted
			$page_data['ingredient_products'] = $this->db->where(array('product_type' => 'local', 'is_deleted' => 0))
														->order_by('name', 'ASC')
														->get('raw_products')
														->result_array();

			$page_data['page_name']  = 'product_formula_add';
			$page_data['page_title'] = 'Add Product Formula';
			$this->load->view('backend/index', $page_data);
		} elseif ($param1 == 'product_formula_edit') {
			$parent_id = (int)$param2;
			$parent_product = $this->db->where(array('id' => $parent_id, 'is_deleted' => '0'))->get('raw_products')->row_array();
			
			if (empty($parent_product)) {
				$this->session->set_flashdata('error_message', 'Parent product not found.');
				redirect(base_url('inventory/product-formula'), 'refresh');
			}

			// Get existing formula items
			$page_data['formula_items'] = $this->db->where('parent_id', $parent_id)->order_by('id', 'ASC')->get('product_formula')->result_array();

			// Get existing charges / expenses
			$page_data['product_charges'] = $this->db->where('product_id', $parent_id)->order_by('id', 'ASC')->get('product_charges')->result_array();

			// For ingredients: local products that are not deleted (and exclude this parent product)
			$page_data['ingredient_products'] = $this->db->where(array('product_type' => 'local', 'is_deleted' => 0, 'id !=' => $parent_id))
														->order_by('name', 'ASC')
														->get('raw_products')
														->result_array();

			$page_data['parent_product'] = $parent_product;
			$page_data['id']             = $parent_id;
			$page_data['page_name']      = 'product_formula_edit';
			$page_data['page_title']     = 'Edit Product Formula';
			$this->load->view('backend/index', $page_data);
		}
	}

	public function get_product_formula()
	{
		if ($this->session->userdata('inventory_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		if ($this->input->is_ajax_request()) {
			$this->inventory_model->get_product_formulas();
		}
	}

	public function get_formula_ingredients_status()
	{
		if ($this->session->userdata('inventory_login') != true) {
			echo json_encode(array('status' => 'error', 'message' => 'Unauthorized'));
			return;
		}

		$parent_id = (int)$this->input->post('parent_id');
		$qty = (int)$this->input->post('quantity');
		$type = $this->input->post('type'); // 'black' or 'white'
		$warehouse_id = (int)$this->input->post('warehouse_id');
		$company_id = (int)$this->session->userdata('company_id');

		if ($qty <= 0) {
			$qty = 1;
		}

		// Fetch ingredients for parent product
		$formula_items = $this->db->query("SELECT pf.product_id, pf.quantity as req_qty, rp.name, rp.item_code 
			FROM product_formula pf
			JOIN raw_products rp ON pf.product_id = rp.id
			WHERE pf.parent_id = ? 
			ORDER BY pf.id ASC", array($parent_id))->result_array();

		$ingredients = array();
		$all_sufficient = true;

		foreach ($formula_items as $item) {
			$ing_id = $item['product_id'];
			$req_qty_1 = (int)$item['req_qty'];
			$total_req = $req_qty_1 * $qty;

			// Get available qty from inventory
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
			$sufficient = ($available >= $total_req);
			if (!$sufficient) {
				$all_sufficient = false;
			}

			$ingredients[] = array(
				'product_id' => $ing_id,
				'name' => $item['name'],
				'item_code' => $item['item_code'],
				'req_qty_1' => $req_qty_1,
				'total_req' => $total_req,
				'available' => $available,
				'sufficient' => $sufficient
			);
		}

		echo json_encode(array(
			'status' => 200,
			'ingredients' => $ingredients,
			'all_sufficient' => $all_sufficient
		));
	}

	public function add_product_stock_post()
	{
		if ($this->session->userdata('inventory_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		$this->inventory_model->add_product_stock();
	}
}
