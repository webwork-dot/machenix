<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Local_products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
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
        WHERE (p.is_deleted='0' AND p.product_type='local') $keyword_filter group by p.id ORDER BY p.id ASC")->num_rows();
        $query = $this->db->query("SELECT p.id,p.alias,p.categories,p.group_id,p.color_name,p.item_code,p.is_variation,p.image,p.name,p.unit,p.amount,p.form,p.gst_type,p.gst,p.gst_amount,p.total_amount,p.hsn_code,p.sizes,p.cartoon_qty, (SELECT image FROM product_images WHERE product_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) AS product_image FROM raw_products as p
        LEFT JOIN product_variation as pv ON p.id = pv.product_id
        WHERE (p.is_deleted='0' AND p.product_type='local') $keyword_filter group by p.id ORDER BY p.id DESC LIMIT $start, $length");

        if (!empty($query)) {
            foreach ($query->result_array() as $item) {
                $id = $item['id'];
                $is_variation = $item['is_variation'];

                $delete_url = "confirm_modal('" . base_url() . "inventory/local-products/delete/" . $id . "','Are you sure want to delete!')";
                $edit_url = base_url() . 'inventory/local-products/edit/' . $id;
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
                    if ($size_id) {
                        $yrs[] = $size_id['color_code'];
                    }
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
                } elseif (count($yrs) > 1) {
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

    public function raw_products_delete_sku()
    {
        $id = $this->input->post('id');
        $this->db->where('id', $id)->delete('product_sku');
        echo json_encode([
            "status"     => 200,
            "message"     => "Deleted Successfully",
        ]);
    }

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

                $data['is_variation']   = 0;
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

                $data['duty_charge']    = 0;
                $data['supplier_id']    = 0;
                $data['supplier_name']  = '';

                $data['cartoon_qty']    = 1;
                $data['net_weight']     = 0;
                $data['gross_weight']   = 0;
                $data['length']         = 0;
                $data['width']          = 0;
                $data['height']         = 0;
                $data['cbm']            = 0;

                $data['rate']                   = 0;
                $data['usd_rate']               = 0;
                $data['actual_usd_rate']        = 0;
                $data['product_mrp']            = 0;
                $data['product_mrp']   = clean_and_escape($this->input->post('product_mrp'));
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
                $data['product_type']   = 'local';
                $data['added_date']     = date("Y-m-d H:i:s");
                $opening_stock = $this->input->post('opening_stock');
                $data['opening_stock']  = (!empty($opening_stock)) ? intval($opening_stock) : 0;

                $this->db->insert('raw_products', $data);
                $user_id = $this->db->insert_id();
                $this->file_model->add_product_images($user_id);

                // Insert single variation for Local Product (no variations in UI)
                $variation = [];
                $variation['product_id']    = $user_id;
                $variation['size_id']       = '';
                $variation['size_name']     = '';
                $variation['name']          = $name;
                $variation['sku_code']      = $item_code;
                $variation['cartoon_qty']    = 1;
                $variation['net_weight']     = 0;
                $variation['gross_weight']   = 0;
                $variation['length']         = 0;
                $variation['width']          = 0;
                $variation['height']         = 0;
                $variation['cbm']            = 0;
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

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $resultpost = array(
                        "status" => 400,
                        "message" => "Error occurred while adding Product",
                    );
                } else {
                    $this->db->trans_commit();

                    // Insert audit log
                    $product_data = $this->db->where('id', $user_id)->get('raw_products')->row_array();
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
            $this->load->model('upload_model');

            $gst = clean_and_escape($this->input->post('gst'));

            $is_variation = clean_and_escape($this->input->post('is_variation'));
            $data['type']           = $product_type;
            $data['name']           = $name;
            $data['alias']          = clean_and_escape($this->input->post('alias'));
            $data['is_variation']   = 0;
            $data['categories']     = $categories;
            $data['commission_id']  = clean_and_escape($this->input->post('commission_id'));
            $data['item_code']      = $item_code;
            $data['hsn_code']       = clean_and_escape($this->input->post('hsn_code'));
            $data['min_stock']      = clean_and_escape($this->input->post('intimation'));
            $data['intimation']     = clean_and_escape($this->input->post('intimation'));
            $data['product_mrp']    = 0;
            $data['product_mrp']  = clean_and_escape($this->input->post('product_mrp'));
            $data['costing_price']  = clean_and_escape($this->input->post('costing_price'));
            $data['gst']            = ($gst) ? $gst : 0;
            $data['unit']           = clean_and_escape($this->input->post('unit'));
            $is_gst_applicable      = $this->input->post('is_gst_applicable');
            $data['is_gst_applicable'] = isset($is_gst_applicable) ? intval($is_gst_applicable) : 1;

            $data['duty_charge']    = 0;
            $data['net_weight']     = 0;
            $data['gross_weight']   = 0;
            $data['length']         = 0;
            $data['width']          = 0;
            $data['height']         = 0;
            $data['cbm']            = 0;
            $data['rate']           = 0;
            $data['usd_rate']       = 0;
            $data['actual_usd_rate'] = 0;

            $data['supplier_id']    = 0;
            $data['supplier_name']  = '';

            $data['product_type'] = 'local';
            $opening_stock = $this->input->post('opening_stock');
            $data['opening_stock']  = (!empty($opening_stock)) ? intval($opening_stock) : 0;
            $old_product_data = $this->db->where('id', $id)->get('raw_products')->row_array();
            $this->db->where('id', $id);
            $this->db->update('raw_products', $data);

            $new_product_data = $this->db->where('id', $id)->get('raw_products')->row_array();
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

            $user_id = $id;

            // Update existing variation's name and sku_code to keep them synced
            $this->db->where('product_id', $user_id)->update('product_variation', [
                'name' => $name,
                'sku_code' => $item_code
            ]);

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

        // Insert audit log
        $product_data = $this->db->where('id', $id)->get('raw_products')->row_array();
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
            "url" => base_url() . 'inventory/local-products/edit/' . $product_id,
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
            "url" => base_url() . 'inventory/local-products/edit/' . $product_id,
        );
        $this->db->where('id', $id);
        $this->db->delete('product_variation_sku');

        return simple_json_output($resultpost);
    }
}
