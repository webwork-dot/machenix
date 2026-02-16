<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quality_control extends CI_Controller{
    public function __construct()    {
        parent::__construct();
        
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('quality_control_model');  
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
        if ($this->session->userdata('quality_control_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }
    
    public function dashboard(){
        if ($this->session->userdata('quality_control_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('dashboard');
        $this->load->view('backend/index.php', $page_data);
    }
	
	public function raw_material($param1 = "", $param2 = "") {
        if ($this->session->userdata('quality_control_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        else {
            $this->session->set_userdata('previous_url', currentUrl()); 
			$page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
            $page_data['page_name']  = 'raw_material';
            $page_data['page_title'] = get_phrase('raw_material');
            $this->load->view('backend/index', $page_data);
        }
    }
	
	public function raw_material_done($param1 = "", $param2 = "") {
        if ($this->session->userdata('quality_control_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        else {
            $this->session->set_userdata('previous_url', currentUrl()); 
			$page_data['warehouse_list']     = $this->common_model->get_all_warehouse_list();
            $page_data['page_name']  = 'raw_material_done';
            $page_data['page_title'] = get_phrase('raw_material');
            $this->load->view('backend/index', $page_data);
        }
    }
	
	public function get_raw_material() {  
		if ($this->session->userdata('quality_control_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($this->input->is_ajax_request()) {
            $this->quality_control_model->get_raw_material();
        } 
    }
	
	public function get_raw_material_product(){
		$id = $this->input->post('id', true);
		$results = $this->quality_control_model->get_raw_material_product($id);
		$i = 1;
		foreach ($results as $item) {
			echo '<tr class="element-1 "><td><input type="hidden" name="id" id="id_'.$i.'" value="' . $item['id'] . '" >' . $item['date'] . '</td><td>' . $item['voucher_no'] . '</td><td>' . $item['supplier_name'].' </td><td>' . $item['product_name'] . '</td><td>' . $item['quantity'] . '</td><td>' . $item['invoice_no'] . '</td><td>' . $item['batch_no'] . '</td><td>' . $item['expiry_date'] . '</td><td><input type="date" class="form-control" name="approved_date" value="" max="'.date('Y-m-d').'" id="date_picker" required></td><td><input type="text" class="form-control" name="sample_qty" value="0" required></td></tr>';
			$i++;
		}
	}
	
	public function complete_raw_material_product() {  
		if ($this->session->userdata('quality_control_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->quality_control_model->complete_raw_material_product();
    }
	
}
?>
