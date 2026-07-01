<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Local_supplier_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_supplier_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('supplier');
    }

    public function get_supplier_outstanding($supplier_id)
    {
        return $this->inventory_model->get_supplier_outstanding($supplier_id);
    }

    public function get_supplier_payments($supplier_id)
    {
        return $this->inventory_model->get_supplier_payments($supplier_id);
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

        $total_count = $this->db->query("SELECT id FROM supplier WHERE (is_deleted='0' AND type='local') $keyword_filter ORDER BY id ASC")->num_rows();
        $query = $this->db->query("SELECT id, name,gst_no,contact_name,contact_no FROM supplier WHERE (is_deleted='0' AND type='local') $keyword_filter ORDER BY id DESC LIMIT $start, $length");

        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $id = $item['id'];

                $delete_url = "confirm_modal('" . base_url() . "inventory/local-supplier/delete/" . $id . "','Are you sure want to delete!')";
                $edit_url = base_url() . 'inventory/local-supplier/edit/' . $id;
                $replicate_url = "showAjaxModal('" . base_url() . "modal/popup_inventory/supplier_replicate_modal/" . $id . "','Replicate Supplier')";
                $action = '';
                $action .= '<a href="' . $edit_url . '" data-toggle="tooltip" data-bs-placement="top" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             <a href="' . base_url() . 'inventory/local-supplier/ledger/' . $id . '" data-toggle="tooltip" data-bs-placement="top" title="Ledger"><button type="button" class="btn mr-1 mb-1 btn-outline-primary" style="padding: 4px 8px;"><i class="fa fa-book" aria-hidden="true"></i></button></a>
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

    public function add_supplier()
    {
        $resultpost = array(
            "status" => 200,
            "message" => get_phrase('supplier_added_successfully'),
            "url" => $this->session->userdata('previous_url'),
        );

        $name = clean_and_escape($this->input->post('name'));
        if ($name != '') {
            $check_supplier_name = $this->inventory_model->check_duplication('on_create', 'name', $name, 'supplier');
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
            $data['outstanding_rmb']   = 0.00;
            $data['outstanding_inr']   = clean_and_escape($this->input->post('outstanding_inr'));
            $data['outstanding_usd']   = 0.00;
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
            $data['type']           = 'local';

            $this->load->model('upload_model');
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
            $check_supplier_name = $this->inventory_model->check_duplication('on_update', 'name', $name, 'supplier', $id);
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
            $data['outstanding_rmb']   = 0.00;
            $data['outstanding_inr']   = clean_and_escape($this->input->post('outstanding_inr'));
            $data['outstanding_usd']   = 0.00;
            $data['company_id']    = $this->session->userdata('company_id');
            $data['country_id']    = $country_id;
            $data['country_name']    = $country_name;
            $data['state_id']    = $state_id;
            $data['state_name']    = $state_name;
            $data['city_id']    = $city_id;
            $data['city_name']    = $city_name;
            $data['type']           = 'local';

            $this->load->model('upload_model');
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
}
