<style>
	.text-right {
		text-align: right;
	}

	.dis-input {
		margin-top: -7px;
		width: 65px !important;
		float: right !important;
		margin-left: 5px !important;
	}

	.dis-input-1 {
		margin-top: 0px;
		width: 200px !important;
		float: right !important;
		margin-left: 5px !important;
	}

	.fx-border {
		border: 1px solid #e0e0e0;
		padding: 5px 5px;
		box-shadow: 0 4px 24px 0 rgb(34 41 47 / 10%);
		background: #f4f8ff;
		position: relative;
		margin-bottom: 10px;
	}

	.jsr-no {
		border: 1px dashed #4a4949;
		display: inline-block;
		padding: 0.3em 0.44em;
		font-weight: 700;
		line-height: 15px;
		padding-right: 0.7em;
		padding-left: 0.7em;
		border-radius: 10rem;
		position: absolute;
		left: -10px;
		top: -10px;
		background: #4a4949;
		color: #fff;
		font-size: 12px;
	}

	.select2-results__option[aria-selected] {
		cursor: pointer;
		font-weight: 800;
	}

	.pl-0 {
		padding-left: 0px !important;
	}

	.pr-0 {
		padding-right: 0px !important;
	}

	#requirement_area .flex-grow-1 .form-group label {
		font-size: 12px;
	}

	.mn-table td {
		padding: 0px 10px !important;
	}

	.mn-table td .td-blank {
		margin: 5px !important;
	}

	input {
		height: 30px;
	}

	#requirement_area .select2-container--default .select2-selection--single .select2-selection__rendered {
		color: #444;
		line-height: normal;
		font-weight: 800;
	}

	.select2-container--default .select2-selection--single {
		height: 30px;
		min-height: 30px;
		line-height: normal;
	}

	.select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 26px;
		position: absolute;
		top: -5px;
		right: 1px;
		width: 20px;
	}

	.f-title {
		border-bottom: 1px dashed #3d3d3d;
		width: max-content;
		margin-top: 10px;
	}

	.m-acc .m-stock-avl {
		position: absolute;
		right: 0;
	}

	.m-stock-avl label {
		border: 1px dashed #037e03;
		color: #037e03;
		padding: 2px 5px;
		margin-top: 5px;
	}

	.sales-line-item {
		background: #f8fbff;
		border: 1px solid #dbe6f5;
		border-radius: 10px;
		padding: 10px 0px;
	}

	.sales-line-item .jsr-no {
		width: 24px;
		height: 24px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: #2f3b52;
		color: #fff;
		font-size: 12px;
		font-weight: 700;
		margin-bottom: 8px;
	}

	.sales-line-item .form-group {
		margin-bottom: 6px;
	}

	.sales-line-item .form-group label {
		font-size: 12px;
		font-weight: 600;
		color: #2f3b52;
		margin-bottom: 4px;
		line-height: 1.2;
	}

	.sales-line-item .form-control,
	.sales-line-item .input-group-text {
		min-height: 34px;
		font-size: 13px;
	}

	.sales-line-item input[readonly] {
		background: #eef3fa;
	}

	.sales-line-item .input-group-text {
		background: #eef3fa;
		border-color: #d3deef;
	}

	.compact-table th, .compact-table td {
		padding: 4px !important;
		vertical-align: middle;
	}
	
	.compact-table .form-control {
		height: 32px;
		min-height: 32px;
		padding: 4px 8px;
		font-size: 13px;
		border-radius: 3px;
	}
	
	.compact-table .input-group-text {
		height: 32px;
		min-height: 32px;
		padding: 0 8px !important;
	}
	
	.compact-table select.form-control {
		width: 100%;
	}
	
	.compact-table th {
		font-size: 12px;
		white-space: nowrap;
	}
</style>

<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/sales_order/edit_salesman_post/' . $data['id'], ['class' => 'add-ajax-redirect-form','onsubmit' => 'return validateForm() && checkForm(this);']);?>
        <div class="row">
          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Order No <span class="required">*</span></label>
              <input type="text" class="form-control" placeholder="Order No" name="order_no"
                value="<?php echo $data['order_no'];?>" required="" readonly>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Refrence Order No </label>
              <input type="text" class="form-control" placeholder="Enter Order No" name="refrence_no" value="<?php echo htmlspecialchars($data['refrence_no'] ?? ''); ?>" readonly>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>"
                value="<?php echo $data['date'];?>" id="date_picker" readonly>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="customer_id">Customer <span class="required">*</span></label>
            <select class="form-select select2" id="customer_id" disabled>
              <option value="">Select Customer </option>
              <?php foreach($customer_list as $item){?>
              	<option value="<?php echo $item['id'];?>" <?php echo ($data['customer_id'] == $item['id']) ? 'selected' : ''; ?>><?php echo $item['company_name'];?></option>
              <?php }?>
            </select>
            <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($data['customer_id']); ?>">
          </div>

          <div class="col-6 mb-1">
            <div class="row">
              <h6 class="mb-1">Billing Address</h6>
              <div class="col-12 mb-1">
                <div class="form-group">
                  <label>Address</label>
                  <textarea class="form-control" placeholder="Billing Address" rows="2" name="billing_address" id="billing_address" readonly><?php echo htmlspecialchars($data['billing_address'] ?? ''); ?></textarea>
                </div>
              </div>
              <div class="col-4 mb-1">
                <label class="form-label" for="billing_state">Select State</label>
                <select class="form-select select2 billing_state_id" id="billing_state_id" disabled>
                  <option value="">Select State</option>
                  <?php foreach($states as $state){?>
                  <option value="<?php echo $state['id'];?>" <?php echo ($data['billing_state_id'] == $state['id']) ? 'selected' : ''; ?>><?php echo $state['name'];?></option>
                  <?php }?>
                </select>
                <input type="hidden" name="billing_state_id" value="<?php echo htmlspecialchars($data['billing_state_id']); ?>">
              </div>
              <div class="col-4 mb-1">
                <label class="form-label" for="billing_city">Select City</label>
                <select class="form-select select2 billing_city_id" id="billing_city_id" disabled>
                  <option value="">Select City</option>
                </select>
                <input type="hidden" name="billing_city_id" value="<?php echo htmlspecialchars($data['billing_city_id']); ?>">
              </div>
              <div class="col-4 mb-1">
                <div class="form-group">
                  <label>Pincode</label>
                  <input type="text" class="form-control" placeholder="Pincode" name="billing_pincode" id="billing_pincode" value="<?php echo htmlspecialchars($data['billing_pincode'] ?? ''); ?>" readonly>
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST Name</label>
                  <input type="text" class="form-control" placeholder="GST Name" name="billing_gst" id="billing_gst" value="<?php echo htmlspecialchars($data['billing_gst'] ?? ''); ?>" readonly>
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST No</label>
                  <input type="text" class="form-control" placeholder="GST No" name="billing_gst_no" id="billing_gst_no" value="<?php echo htmlspecialchars($data['billing_gst_no'] ?? ''); ?>" readonly>
                </div>
              </div>
            </div>
          </div>

          <div class="col-6 mb-1">
            <div class="row">
              <h6 class="mb-1">Shipping Address</h6>
              <div class="col-12 mb-1">
                <div class="form-group">
                  <label>Address</label>
                  <textarea class="form-control" placeholder="Shipping Address" rows="2" name="shipping_address" id="shipping_address" readonly><?php echo htmlspecialchars($data['shipping_address'] ?? ''); ?></textarea>
                </div>
              </div>
              <div class="col-4 mb-1">
                <label class="form-label" for="shipping_state">Select State</label>
                <select class="form-select select2 shipping_state_id" id="shipping_state_id" disabled>
                  <option value="">Select State</option>
                  <?php foreach($states as $state){?>
                  <option value="<?php echo $state['id'];?>" <?php echo ($data['shipping_state_id'] == $state['id']) ? 'selected' : ''; ?>><?php echo $state['name'];?></option>
                  <?php }?>
                </select>
                <input type="hidden" name="shipping_state_id" value="<?php echo htmlspecialchars($data['shipping_state_id']); ?>">
              </div>
              <div class="col-4 mb-1">
                <label class="form-label" for="shipping_city">Select City</label>
                <select class="form-select select2 shipping_city_id" id="shipping_city_id" disabled>
                  <option value="">Select City</option>
                </select>
                <input type="hidden" name="shipping_city_id" value="<?php echo htmlspecialchars($data['shipping_city_id']); ?>">
              </div>
              <div class="col-4 mb-1">
                <div class="form-group">
                  <label>Pincode</label>
                  <input type="text" class="form-control" placeholder="Pincode" name="shipping_pincode" id="shipping_pincode" value="<?php echo htmlspecialchars($data['shipping_pincode'] ?? ''); ?>" readonly>
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST Name</label>
                  <input type="text" class="form-control" placeholder="GST Name" name="shipping_gst" id="shipping_gst" value="<?php echo htmlspecialchars($data['shipping_gst'] ?? ''); ?>" readonly>
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST No</label>
                  <input type="text" class="form-control" placeholder="GST No" name="shipping_gst_no" id="shipping_gst_no" value="<?php echo htmlspecialchars($data['shipping_gst_no'] ?? ''); ?>" readonly>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="warehouse_id">Warehouse <span class="required">*</span></label>
            <select class="form-select select2" id="warehouse_id" disabled>
              <option value="0">Select Warehouse</option>
              <?php foreach ($warehouse_list as $warehouse) { ?>
                <option value="<?php echo $warehouse->id; ?>" <?php echo ($data['warehouse_id'] == $warehouse->id) ? 'selected' : ''; ?>><?php echo $warehouse->name; ?></option>
              <?php } ?>
            </select>
            <input type="hidden" name="warehouse_id" value="<?php echo htmlspecialchars($data['warehouse_id']); ?>">
          </div>

          <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['company_id']); ?>">
          <input type="hidden" name="narration" value="<?php echo htmlspecialchars($data['narration'] ?? ''); ?>">
          
          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Remark</label>
              <textarea class="form-control" placeholder="" rows="1" name="remark" id="remark" readonly><?php echo htmlspecialchars($data['remark'] ?? ''); ?></textarea>
            </div>
          </div>

          <div class="col-12">
            <h6 class="mb-1">Products</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-sm compact-table">
                <thead class="table-light text-center">
                  <tr>
                    <th style="min-width:100px;">Move Replace</th>
                    <th style="min-width:200px;">Product <span class="text-danger">*</span></th>
                    <th style="min-width:50px;">Qty <span class="text-danger">*</span></th>
                    <th style="min-width:140px;">Per Qty Amt <span class="text-danger">*</span></th>
                    <th style="min-width:170px;">Remark</th>
                    <th style="min-width:100px;">Total Amt</th>
                    <th style="min-width:120px;">Per Qty Bill <span class="text-danger">*</span></th>
                    <th style="min-width:100px;">Total Bill</th>
                    <th style="min-width:60px;">GST % <span class="text-danger">*</span></th>
                    <th style="min-width:100px;">GST Amt</th>
                    <th style="min-width:120px;">Total Bill GST</th>
                    <th style="min-width:110px;">Per Qty Black</th>
                    <th style="min-width:100px;">Total Black</th>
                    <th style="min-width:120px;">Final Total</th>
                  </tr>
                </thead>
                <tbody id="requirement_area">
                  <?php $k = 1; foreach($data['products'] as $product) { ?>
                  <tr class="element-1 sales-line-item" id="product_<?php echo $k; ?>" data-id="<?php echo $k; ?>">
                    <td class="text-center">
                      <?php
                        $chk_query = $this->db->get_where('replace_products', ['order_prod_id' => $product['id']]);
                        $is_checked = ($chk_query->num_rows() > 0) ? 'checked' : '';
                      ?>
                      <input type="checkbox" class="form-check-input" value="1" <?php echo $is_checked; ?> disabled>
                      <input type="hidden" name="replace_product_chk_<?php echo $k; ?>" value="<?php echo ($is_checked ? '1' : '0'); ?>">
                    </td>
                    <td>
                      <input type="hidden" name="x_value[]" id="x_value_<?php echo $k; ?>" value="<?php echo $k; ?>">
                      <input type="hidden" name="old_id[]" id="old_id_<?php echo $k; ?>" value="<?php echo $product['id']; ?>">
                      <select class="form-control select2 product_id" id="product_id_<?php echo $k; ?>" disabled>
                        <option value="">Select Product</option>
                        <?php foreach($products_list as $pl) { ?>
                          <option value="<?php echo $pl['id']; ?>" <?php echo ($product['product_id'] == $pl['id']) ? 'selected' : ''; ?>><?php echo $pl['name']; ?></option>
                        <?php } ?>
                      </select>
                      <input type="hidden" name="product_id[]" value="<?php echo $product['product_id']; ?>">
                    </td>
                    <td>
                      <input type="number" step="any" id="quantity_<?php echo $k; ?>" name="quantity[]" placeholder="Qty" value="<?php echo $product['qty']; ?>" class="form-control" readonly>
                    </td>
                    <td>
                      <input type="hidden" id="master_amount_<?php echo $k; ?>" name="master_amount[]" value="<?php echo $product['amount']; ?>">
                    </td>
                    <td></td>
                    <td><input type="hidden" id="total_amount_<?php echo $k; ?>" name="total_amount[]" value="<?php echo $product['total_amount']; ?>"></td>
                    <td><input type="hidden" id="bill_amount_<?php echo $k; ?>" name="bill_amount[]" value="<?php echo $product['bill_amount']; ?>" data-manual="false"></td>
                    <td><input type="hidden" id="bill_total_<?php echo $k; ?>" name="bill_total[]" value="<?php echo $product['bill_total']; ?>"></td>
                    <td><input type="hidden" id="gst_<?php echo $k; ?>" name="gst[]" value="<?php echo $product['gst']; ?>"></td>
                    <td><input type="hidden" id="gst_amount_<?php echo $k; ?>" name="gst_amount[]" value="<?php echo $product['gst_amount']; ?>"></td>
                    <td><input type="hidden" id="total_bill_gst_amount_<?php echo $k; ?>" name="total_bill_gst_amount[]" value="<?php echo $product['total_bill_gst_amount']; ?>"></td>
                    <td><input type="hidden" id="black_amount_per_unit_<?php echo $k; ?>" name="black_amt[]" value="<?php echo ($product['amount'] - $product['bill_amount']); ?>"></td>
                    <td><input type="hidden" id="black_amount_<?php echo $k; ?>" name="black_total[]" value="<?php echo $product['black_total']; ?>"></td>
                    <td>
                      <input type="hidden" id="final_total_<?php echo $k; ?>" name="final_total[]" value="<?php echo $product['final_total']; ?>">
                      <input type="hidden" id="available_<?php echo $k; ?>" name="available[]" value="<?php echo $product['available']; ?>">
                    </td>
                  </tr>
                  <?php 
                    $product_batches = $this->db->get_where('sales_order_product_batch', [
                      'order_id' => $data['id'],
                      'order_product_id' => $product['id']
                    ])->result_array();
                    
                    $batch_index = 1;
                    foreach ($product_batches as $batch) {
                      $all_batches = $this->db->query("SELECT id, batch_no, official_qty, black_qty FROM inventory WHERE warehouse_id = ? AND product_id = ? AND (quantity > 0 OR batch_no = ?) GROUP BY batch_no", [$data['warehouse_id'], $product['product_id'], $batch['batch_no']])->result_array();
                      
                      $inventory_batch = $this->db->get_where('inventory', [
                        'warehouse_id' => $data['warehouse_id'],
                        'product_id' => $product['product_id'],
                        'batch_no' => $batch['batch_no']
                      ])->row_array();

                      $avail_white = $batch['white_qty'];
                      $avail_black = $batch['black_qty'];
                      if ($inventory_batch) {
                        if($data['is_weird'] == 1) {
                          $avail_white = $inventory_batch['official_qty'] + $batch['white_qty'] + ($batch['black_qty'] - $batch['avail_black_qty']);
                          $avail_black = $inventory_batch['black_qty'];
                        } else {
                          $avail_white = $inventory_batch['official_qty'] + $batch['white_qty'];
                          $avail_black = $inventory_batch['black_qty'] + $batch['black_qty'];
                        }
                      }
                      
                      $selected_batch_id = $inventory_batch ? $inventory_batch['id'] : '';

                      $min_selling_price = 0;
                      $supplier_id = !empty($inventory_batch['supplier_id']) ? (int)$inventory_batch['supplier_id'] : 0;
                      $product_id_val = (int)$product['product_id'];
                      if ($supplier_id > 0 && $product_id_val > 0) {
                        $pv_row = $this->db->get_where('product_variations', ['product_id' => $product_id_val, 'supplier_id' => $supplier_id])->row_array();
                        if ($pv_row && isset($pv_row['costing_price'])) {
                          $min_selling_price = (float)$pv_row['costing_price'];
                        }
                      }
                      if ($min_selling_price == 0 && $product_id_val > 0) {
                        $rp_row = $this->db->get_where('raw_products', ['id' => $product_id_val])->row_array();
                        if ($rp_row && isset($rp_row['costing_price'])) {
                          $min_selling_price = (float)$rp_row['costing_price'];
                        }
                      }
                  ?>
                    <tr class="batch-row batch-row-<?php echo $k; ?>" data-min-price="<?php echo $min_selling_price; ?>">
                      <td></td>
                      <td style="padding-left: 20px !important;">
                        <select class="form-control select2 batch_id" id="batch_id_<?php echo $k; ?>_<?php echo $batch_index; ?>" disabled>
                          <option value="">Select Batch</option>
                          <?php foreach ($all_batches as $ab) { ?>
                            <option value="<?php echo $ab['id']; ?>" <?php echo ($ab['id'] == $selected_batch_id) ? 'selected' : ''; ?>>
                              <?php echo $ab['batch_no']; ?>
                            </option>
                          <?php } ?>
                        </select>
                        <input type="hidden" name="batch_id[<?php echo $k; ?>][]" value="<?php echo $selected_batch_id; ?>">
                      </td>
                      <td>
                        <div class="d-flex gap-25 align-items-center">
                          <div class="d-flex flex-column align-items-center" style="flex: 1;">
                            <input type="number" step="any" class="form-control form-control-sm text-center batch_white_qty_input" name="batch_white_qty[<?php echo $k; ?>][]" id="batch_white_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" value="<?php echo $batch['white_qty']; ?>" style="padding: 2px; height: 26px;" readonly>
                            <input type="hidden" class="available_white_qty" name="available_white_qty[<?php echo $k; ?>][]" id="available_white_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" value="<?php echo $avail_white; ?>">
                          </div>
                          <div class="d-flex flex-column align-items-center" style="flex: 1;">
                            <input type="number" step="any" class="form-control form-control-sm text-center batch_black_qty_input" name="batch_black_qty[<?php echo $k; ?>][]" id="batch_black_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" value="<?php echo $batch['black_qty']; ?>" style="padding: 2px; height: 26px;" readonly>
                            <input type="hidden" class="available_black_qty" name="available_black_qty[<?php echo $k; ?>][]" id="available_black_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" value="<?php echo $avail_black; ?>">
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="input-group">
                          <input type="number" step="any" class="form-control batch_rate text-center" name="batch_rate[<?php echo $k; ?>][]" id="batch_rate_<?php echo $k; ?>_<?php echo $batch_index; ?>" onkeyup="calculate_batch_amt(this, '<?php echo $k; ?>')" value="<?php echo number_format($batch['amount'], 2, '.', ''); ?>">
                          <span class="input-group-text p-0 price-history-btn" tabindex="0" style="cursor:pointer" data-row-index="<?php echo $k; ?>" data-batch-index="<?php echo $batch_index; ?>" onclick="showPriceHistory('<?php echo $k; ?>', '<?php echo $batch_index; ?>')"><i class="fa fa-history px-1"></i></span>
                        </div>
                      </td>
                      <td style="min-width: 170px;">
                        <div class="d-flex flex-column gap-25">
                          <input type="text" class="form-control batch_remark" name="batch_remark[<?php echo $k; ?>][]" id="batch_remark_<?php echo $k; ?>_<?php echo $batch_index; ?>" placeholder="Remark" value="<?php echo htmlspecialchars($batch['remark'] ?? ''); ?>">
                          <span class="badge bg-light-danger text-danger border border-danger batch_remark_indicator d-none mt-25" id="batch_remark_indicator_<?php echo $k; ?>_<?php echo $batch_index; ?>" style="font-size: 10px; padding: 3px 5px; font-weight: bold; text-align: left; align-items: center; gap: 4px;" title="Product amount is lower than required minimum selling price!">
                            <i class="fa fa-exclamation-triangle text-danger" style="font-size: 11px;"></i>
                            <span>Price Alert: Below Min Price (<span class="min-price-val">0</span>)</span>
                          </span>
                        </div>
                      </td>
                      <td>
                        <input type="number" step="any" class="form-control batch_total_amount text-center" id="batch_total_amount_<?php echo $k; ?>_<?php echo $batch_index; ?>" readonly tabindex="-1" value="<?php echo number_format(($batch['white_qty'] + $batch['black_qty']) * $batch['amount'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" step="any" class="form-control batch_bill_amount text-center" name="batch_bill_amount[<?php echo $k; ?>][]" id="batch_bill_amount_<?php echo $k; ?>_<?php echo $batch_index; ?>" onkeyup="markBatchManual(this); calculate_batch_amt(this, '<?php echo $k; ?>')" data-manual="<?php echo ($batch['amount'] != $batch['bill_amount']) ? 'true' : 'false'; ?>" value="<?php echo number_format($batch['bill_amount'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" step="any" class="form-control batch_bill_total text-center" name="batch_bill_total[<?php echo $k; ?>][]" id="batch_bill_total_<?php echo $k; ?>_<?php echo $batch_index; ?>" onkeyup="calculate_batch_amt_reverse(this, '<?php echo $k; ?>')" value="<?php echo number_format($batch['bill_total'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" step="any" class="form-control batch_gst_per text-center" name="batch_gst_per[<?php echo $k; ?>][]" id="batch_gst_per_<?php echo $k; ?>_<?php echo $batch_index; ?>" onkeyup="calculate_batch_amt(this, '<?php echo $k; ?>')" value="<?php echo number_format($batch['gst'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" class="form-control batch_gst_amt text-center" name="batch_gst_amt[<?php echo $k; ?>][]" id="batch_gst_amt_<?php echo $k; ?>_<?php echo $batch_index; ?>" readonly tabindex="-1" value="<?php echo number_format($batch['gst_amount'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" class="form-control batch_total_bill_gst_amount text-center" name="batch_total_bill_gst_amount[<?php echo $k; ?>][]" id="batch_total_bill_gst_amount_<?php echo $k; ?>_<?php echo $batch_index; ?>" readonly tabindex="-1" value="<?php echo number_format($batch['total_bill_gst_amount'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" class="form-control batch_black_amt text-center" name="batch_black_amt[<?php echo $k; ?>][]" id="batch_black_amt_<?php echo $k; ?>_<?php echo $batch_index; ?>" readonly tabindex="-1" value="<?php echo number_format($batch['black_amount'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" class="form-control batch_black_total_amt" name="batch_black_total_amt[<?php echo $k; ?>][]" id="batch_black_total_amt_<?php echo $k; ?>_<?php echo $batch_index; ?>" readonly tabindex="-1" value="<?php echo number_format($batch['black_total'], 2, '.', ''); ?>">
                      </td>
                      <td>
                        <input type="number" class="form-control batch_final_total text-center" name="batch_final_total[<?php echo $k; ?>][]" id="batch_final_total_<?php echo $k; ?>_<?php echo $batch_index; ?>" readonly tabindex="-1" value="<?php echo number_format($batch['final_total'], 2, '.', ''); ?>">
                      </td>
                    </tr>
                  <?php 
                      $batch_index++;
                    }
                  ?>
                  <?php $k++; } ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-12 mt-1">
            <h6 class="mb-1">Other Charges</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-sm compact-table">
                <thead class="table-light text-center">
                  <tr>
                    <th style="min-width:200px;">Type</th>
                    <th style="min-width:80px;">GST %</th>
                    <th style="min-width:120px;">Amount</th>
                    <th style="min-width:120px;">Total Amount</th>
                  </tr>
                </thead>
                <tbody id="charges_area">
                  <?php 
                  $c = 1; 
                  if (!empty($data['other_charges'])) {
                    foreach ($data['other_charges'] as $chg) { ?>
                      <tr class="element-charge-<?php echo $c; ?> charge-line-item" id="charge_<?php echo $c; ?>" data-id="<?php echo $c; ?>">
                        <td>
                          <select class="form-control select2 charge_id" id="charge_id_<?php echo $c; ?>" disabled>
                            <option value="">Select Charges</option>
                            <?php foreach ($other_charges as $charge) { ?>
                              <option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>" <?php echo ($chg['type_id'] == $charge['id']) ? 'selected' : ''; ?>>
                                <?php echo $charge['name']; ?>
                              </option>
                            <?php } ?>
                          </select>
                          <input type="hidden" name="charge_id[]" value="<?php echo $chg['type_id']; ?>">
                        </td>
                        <td><input type="number" step="any" id="charge_gst_<?php echo $c; ?>" name="charge_gst[]" placeholder="GST %" class="form-control charge-input" value="<?php echo $chg['gst']; ?>" readonly></td>
                        <td><input type="number" step="any" id="charge_price_<?php echo $c; ?>" name="charge_price[]" placeholder="Amount" class="form-control charge-input" value="<?php echo $chg['amount']; ?>" readonly></td>
                        <td><input type="number" step="any" id="charge_total_<?php echo $c; ?>" name="charge_total[]" placeholder="Total Amount" class="form-control" tabindex="-1" readonly value="<?php echo $chg['total_amt']; ?>"></td>
                      </tr>
                  <?php 
                      $c++;
                    }
                  } else { ?>
                      <tr class="element-charge-1 charge-line-item" id="charge_1" data-id="1">
                        <td>
                          <select class="form-control select2 charge_id" id="charge_id_1" disabled>
                            <option value="">Select Charges</option>
                            <?php foreach ($other_charges as $charge) { ?>
                              <option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>"><?php echo $charge['name']; ?></option>
                            <?php } ?>
                          </select>
                          <input type="hidden" name="charge_id[]" value="">
                        </td>
                        <td><input type="number" step="any" id="charge_gst_1" name="charge_gst[]" placeholder="GST %" class="form-control charge-input" value="0" readonly></td>
                        <td><input type="number" step="any" id="charge_price_1" name="charge_price[]" placeholder="Amount" class="form-control charge-input" value="0" readonly></td>
                        <td><input type="number" step="any" id="charge_total_1" name="charge_total[]" placeholder="Total Amount" class="form-control" tabindex="-1" readonly value="0"></td>
                      </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-12 col-sm-12 mb-1">
            <div class="table-responsive">
              <div class="col-lg-12 no-pad">

                <table class="table table-striped table-bordered mn-table mt-1">
                  <tbody>
                    <tr>
                      <td colspan="4" class="text-right" style="width:80%">
                        <label style="float:right;display: contents;">Total Bill Amt (Exc GST)</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="basic_value" id="basic_value"
                            value="<?php echo htmlspecialchars($data['basic_value']); ?>" placeholder="Total Bill Amt (Exc GST)" class="form-control" readonly></p>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <div class="d-flex flex-column align-items-end">
                          <span class="mb-0 text-capitalize">Select GST</span>
                          <select class="form-control " name="gst_type" id="gst_type" onchange="change_gst(this.value); recalculate();" style="width : 200px !important;float:right !important">
                            <option value="Central GST / State GST" <?php echo ($data['gst_type'] == 'Central GST / State GST') ? 'selected' : ''; ?>>Central GST / State GST</option>
                            <option value="IGST" <?php echo ($data['gst_type'] == 'IGST') ? 'selected' : ''; ?>>IGST</option>
                          </select>
                        </div>
                      </td>
                      <td colspan="1">
                        <div id="cgst_sgst_inputs">
                          <p class="td-blank mb-25">
                            <input type="number" step="any" name="central_gst" id="central_gst" value="<?php echo htmlspecialchars($data['central_gst']); ?>" placeholder="CGST Amount" class="form-control" readonly>
                          </p>
                          <p class="td-blank mb-0">
                            <input type="number" step="any" name="state_gst" id="state_gst" value="<?php echo htmlspecialchars($data['state_gst']); ?>" placeholder="SGST Amount" class="form-control" readonly>
                          </p>
                        </div>
                        <div id="igst_input" class="hidden">
                          <p class="td-blank mb-0">
                            <input type="number" step="any" name="igst" id="igst" value="<?php echo htmlspecialchars($data['igst']); ?>" placeholder="IGST Amount" class="form-control" readonly>
                          </p>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Total Bill Amt (Incl GST)</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="net_sales_value_1"
                            id="net_sales_value_1" value="<?php echo htmlspecialchars($data['net_sales_value_1']); ?>" placeholder="Total Bill Amt (Incl GST)"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Total Black Amt</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="total_black_amount_summary"
                            id="total_black_amount_summary" value="<?php echo htmlspecialchars($data['total_black_amt']); ?>" placeholder="Total Black Amt"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Final Total</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="net_sales_value_2"
                            id="net_sales_value_2" value="<?php echo htmlspecialchars($data['net_sales_value_2']); ?>" placeholder="Final Total"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Other Charges</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="other_charges_amount"
                            id="other_charges_amount" placeholder="Charge Amount" class="form-control" value="<?php echo htmlspecialchars($data['other_charges_amount']); ?>" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Round Of</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="round_of" id="round_of"
                            placeholder="Round Of" class="form-control" value="<?php echo htmlspecialchars($data['round_of']); ?>" onkeyup="recalculate()"></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Grand Total</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="grand_total" id="grand_total" placeholder="" class="form-control" value="<?php echo htmlspecialchars($data['grand_total']); ?>" readonly></p>
                      </td>
                    </tr>
                  </tbody>
                </table>

              </div>
            </div>
          </div>

          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
          </div>
        </div>
        <?php echo form_close(); ?>
        <!--/ form -->
      </div>
    </div>
  </div>
</div>

<script>
function get_per_total(amount, percent) {
  var final_amount = (amount * percent) / 100;
  return parseFloat(final_amount.toFixed(2));
}

function subtotal_cal() {
  var gst_type = $('#gst_type').val();
  var total_bill_amt_ex_gst = 0;
  var total_gst_amount = 0;
  var total_bill_amt_in_gst = 0;
  var total_black_amount = 0;
  var final_total_sum = 0;
  var grand_total = 0;

  let totalBillAmt = document.querySelectorAll('[name="bill_total[]"]');
  let gstAmt = document.querySelectorAll('[name="gst_amount[]"]');
  let totalBillGstAmt = document.querySelectorAll('[name="total_bill_gst_amount[]"]');
  let totalBlackAmt = document.querySelectorAll('[name="black_total[]"]');
  let finalTotalArr = document.querySelectorAll('[name="final_total[]"]');

  totalBillAmt.forEach((element, index)=> {
    var bill_total_val = Number(element.value) || 0;
    var gst_amount_val = Number(gstAmt[index] ? gstAmt[index].value : 0) || 0;
    total_bill_amt_ex_gst += bill_total_val;
    total_gst_amount += gst_amount_val;
  });

  totalBillGstAmt.forEach((element) => {
    total_bill_amt_in_gst += Number(element.value) || 0;
  });

  totalBlackAmt.forEach((element) => {
    total_black_amount += Number(element.value) || 0;
  });

  finalTotalArr.forEach((element) => {
    final_total_sum += Number(element.value) || 0;
  });

  $("#basic_value").val(total_bill_amt_ex_gst.toFixed(2));
  $("#net_sales_value_1").val(total_bill_amt_in_gst.toFixed(2));
  $("#total_black_amount_summary").val(total_black_amount.toFixed(2));
  $("#net_sales_value_2").val(final_total_sum.toFixed(2));

  if (gst_type === 'IGST') {
    $('#igst').val(total_gst_amount.toFixed(2));
    $('#central_gst').val('0.00');
    $('#state_gst').val('0.00');
  } else if (gst_type == 'Central GST / State GST') {
    $('#central_gst').val((total_gst_amount / 2).toFixed(2));
    $('#state_gst').val((total_gst_amount / 2).toFixed(2));
    $('#igst').val('0.00');
  } else {
    $('#central_gst').val('0.00');
    $('#state_gst').val('0.00');
    $('#igst').val('0.00');
  }

  var total_charge_amt = 0;
  let chargeTotalArr = document.querySelectorAll('[name="charge_total[]"]');
  chargeTotalArr.forEach((element) => {
    total_charge_amt += Number(element.value) || 0;
  });

  $("#other_charges_amount").val(total_charge_amt.toFixed(2));

  var round_of = parseFloat($("#round_of").val()) || 0;

  grand_total = final_total_sum + total_charge_amt + round_of;
  $('#grand_total').val(grand_total.toFixed(2));
}

function recalculate() {
  subtotal_cal();
}

function change_gst(value) {
  let cgstSgstInputs = document.querySelector("#cgst_sgst_inputs");
  let igstInput = document.querySelector("#igst_input");

  if (value == "Central GST / State GST") {
    cgstSgstInputs.classList.remove('hidden');
    igstInput.classList.add('hidden');
  } else if (value == "IGST") {
    cgstSgstInputs.classList.add('hidden');
    igstInput.classList.remove('hidden');
  } else {
    cgstSgstInputs.classList.add('hidden');
    igstInput.classList.add('hidden');
  }
}

function markManual(index) {
    $('#bill_amount_' + index).attr('data-manual', 'true');
}

function calculate_amt(index) {
    var activeId = document.activeElement.id;
    var qty = Number($('#quantity_' + index).val()) || 0;
    var amount = Number($('#master_amount_' + index).val()) || 0;
    var bill_amt_el = $('#bill_amount_' + index);
    var is_manual = bill_amt_el.attr('data-manual') === 'true';
    
    var total_amount = qty * amount;

    if (!is_manual && activeId !== 'bill_amount_' + index) {
        bill_amt_el.val(amount.toFixed(2));
    }

    var bill_amt = Number(bill_amt_el.val()) || 0;
    var gst_per = Number($('#gst_' + index).val()) || 0;
    var total_bill_amt = bill_amt * qty;
    var gst_amt = (total_bill_amt * gst_per) / 100;
    var total_bill_gst_amt = total_bill_amt + gst_amt;
    var black_amt = amount - bill_amt;
    var total_black_amt = total_amount - total_bill_amt;
    var final_total = total_black_amt + total_bill_gst_amt;

    $('#total_amount_' + index).val(total_amount.toFixed(2));
    if (activeId !== 'bill_total_' + index) {
        $('#bill_total_' + index).val(total_bill_amt.toFixed(2));
    }
    $('#black_amount_per_unit_' + index).val(black_amt.toFixed(2));
    $('#black_amount_' + index).val(total_black_amt.toFixed(2));
    $('#gst_amount_' + index).val(gst_amt.toFixed(2));
    $('#total_bill_gst_amount_' + index).val(total_bill_gst_amt.toFixed(2));
    $('#final_total_' + index).val(final_total.toFixed(2));

    recalculate();
}

function calculate_amt_reverse(index) {
    var activeId = document.activeElement.id;
    var qty = Number($('#quantity_' + index).val()) || 0;
    var bill_total = Number($('#bill_total_' + index).val()) || 0;
    
    markManual(index);

    if (qty > 0) {
        var bill_amt = bill_total / qty;
        if (activeId !== 'bill_amount_' + index) {
            $('#bill_amount_' + index).val(bill_amt.toFixed(2));
        }
    }

    calculate_amt(index);
}

function updateProductOverallQty(index) {
  var warehouse_id = $('#warehouse_id').val();
  var product_val = $('#product_id_' + index).val();
  var info_container = $('#product_qty_info_' + index);

  if (!warehouse_id || warehouse_id == '0' || !product_val) {
    info_container.hide();
    return;
  }

  var product_id = String(product_val).split('|')[0];

  $.ajax({
    type: "POST",
    url: "<?php echo base_url(); ?>inventory/get_warehouse_product_qty",
    data: { warehouse_id: warehouse_id, product_id: product_id },
    dataType: "json",
    success: function(res) {
      info_container.find('.avail-white-val').text(res.total_white);
      if (info_container.find('.avail-black-val').length > 0) {
        info_container.find('.avail-black-val').text(res.total_black);
      }
      info_container.show();
    }
  });
}

function showPriceHistory(index, batch_index) {
  var customer_id = $('#customer_id').val();
  var product_val = $('#product_id_' + index).val();

  if (!customer_id) {
    alert('Please select a customer first');
    return;
  }

  if (!product_val) {
    alert('Please select a product first');
    return;
  }

  var product_id = String(product_val || '').split('|')[0];

  $.ajax({
    type: "POST",
    url: "<?php echo base_url()?>inventory/get_last_selling_price",
    data: { customer_id: customer_id, product_id: product_id },
    success: function(res) {
        $('#priceHistoryModal').data('row-index', index);
        $('#priceHistoryModal').data('batch-index', batch_index || '');
        $('#priceHistoryModalContent').html(res);
        $('#priceHistoryModal').modal('show');
    }
  });
}

$(document).on('click', '#priceHistoryModal .apply-price-btn', function() {
  var price = $(this).data('price');
  var index = $('#priceHistoryModal').data('row-index');
  var batch_index = $('#priceHistoryModal').data('batch-index');
  if (index) {
    if (batch_index) {
      var rate_el = $('#batch_rate_' + index + '_' + batch_index);
      rate_el.val(price);
      calculate_batch_amt(rate_el, index);
    } else {
      $('#master_amount_' + index).val(price);
      calculate_amt(index);
    }
    $('#priceHistoryModal').modal('hide');
  }
});

// History button: open modal on Enter key
$(document).on('keydown', '.price-history-btn', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    var idx = $(this).data('row-index');
    var bidx = $(this).data('batch-index');
    showPriceHistory(idx, bidx);
  }
});

// Modal opened: focus first Apply button
$('#priceHistoryModal').on('shown.bs.modal', function() {
  var firstBtn = $(this).find('.apply-price-btn').first();
  if (firstBtn.length) { firstBtn.focus(); }
});

// Modal closed: return focus to appropriate input
$('#priceHistoryModal').on('hidden.bs.modal', function() {
  var index = $(this).data('row-index');
  var batch_index = $(this).data('batch-index');
  if (index) {
    if (batch_index) {
      $('#batch_bill_amount_' + index + '_' + batch_index).focus();
    } else {
      $('#bill_amount_' + index).focus();
    }
  }
});

function validateForm() {
  var warehouse_id = $('#warehouse_id').val();
  if (warehouse_id == '0' || warehouse_id == '') {
    Swal.fire({
      title: "Error!",
      text: "Please select warehouse first",
      icon: "error"
    });
    return false;
  }

  var isValid = true;
  $('.sales-line-item').each(function() {
    var index = $(this).data('id');
    var product_val = $('#product_id_' + index).val();
    if (!product_val) return;

    var product_qty = parseFloat($('#quantity_' + index).val()) || 0;
    if (product_qty <= 0) {
      Swal.fire({
        title: "Error!",
        text: "Product quantity must be greater than zero.",
        icon: "error"
      });
      isValid = false;
      return false;
    }

    var total_batch_qty = 0;
    var has_batch = false;
    var batch_selected = true;

    $('.batch-row-' + index).each(function() {
      has_batch = true;
      var bid = $(this).find('.batch_id').siblings('input[type="hidden"]').val() || $(this).find('.batch_id').val();
      if (bid == '' || bid == null) {
        batch_selected = false;
        return false;
      }
      var w_qty = parseFloat($(this).find('.batch_white_qty_input').val()) || 0;
      var b_qty = parseFloat($(this).find('.batch_black_qty_input').val()) || 0;
      total_batch_qty += (w_qty + b_qty);
    });

    if (!has_batch) {
      Swal.fire({
        title: "Error!",
        text: "Please select at least one batch for the selected products.",
        icon: "error"
      });
      isValid = false;
      return false;
    }

    if (!batch_selected) {
      Swal.fire({
        title: "Error!",
        text: "Please select a valid batch number for all batch rows.",
        icon: "error"
      });
      isValid = false;
      return false;
    }

    if (Math.abs(total_batch_qty - product_qty) > 0.001) {
      Swal.fire({
        title: "Error!",
        text: "Total allocated batch quantity (" + total_batch_qty + ") does not match product quantity (" + product_qty + ") on line " + index + ".",
        icon: "error"
      });
      isValid = false;
      return false;
    }
  });

  return isValid;
}

function markBatchManual(element) {
  $(element).attr('data-manual', 'true');
}

function rollup_product_totals(index) {
  var total_white = 0;
  var total_black = 0;
  var total_bill_amt = 0;
  var total_gst_amt = 0;
  var total_bill_gst = 0;
  var total_black_amt = 0;
  var total_final = 0;

  $('.batch-row-' + index).each(function() {
    total_white += parseFloat($(this).find('.batch_white_qty_input').val()) || 0;
    total_black += parseFloat($(this).find('.batch_black_qty_input').val()) || 0;
    total_bill_amt += parseFloat($(this).find('.batch_bill_total').val()) || 0;
    total_gst_amt += parseFloat($(this).find('.batch_gst_amt').val()) || 0;
    total_bill_gst += parseFloat($(this).find('.batch_total_bill_gst_amount').val()) || 0;
    total_black_amt += parseFloat($(this).find('.batch_black_total_amt').val()) || 0;
    total_final += parseFloat($(this).find('.batch_final_total').val()) || 0;
  });

  var total_allocated = total_white + total_black;

  $('#bill_total_' + index).val(total_bill_amt.toFixed(2));
  $('#gst_amount_' + index).val(total_gst_amt.toFixed(2));
  $('#total_bill_gst_amount_' + index).val(total_bill_gst.toFixed(2));
  $('#black_amount_' + index).val(total_black_amt.toFixed(2));
  $('#final_total_' + index).val(total_final.toFixed(2));

  if (total_allocated > 0) {
    $('#bill_amount_' + index).val((total_bill_amt / total_allocated).toFixed(2));
    $('#black_amount_per_unit_' + index).val((total_black_amt / total_allocated).toFixed(2));
  } else {
    $('#bill_amount_' + index).val('0.00');
    $('#black_amount_per_unit_' + index).val('0.00');
  }
}

function calculate_batch_amt(element, index) {
  var row = $(element).closest('.batch-row');
  var activeId = document.activeElement.id;
  var batch_id = row.find('.batch_id').siblings('input[type="hidden"]').val() || row.find('.batch_id').val();

  if (batch_id == '' || batch_id == null) {
    Swal.fire({
      title: "Error!",
      text: "Please select a batch first!",
      icon: "error"
    });
    $(element).val(0);
    return;
  }

  var product_qty = parseFloat($('#quantity_' + index).val()) || 0;
  
  var white_qty = parseFloat(row.find('.batch_white_qty_input').val()) || 0;
  var black_qty = parseFloat(row.find('.batch_black_qty_input').val()) || 0;
  var rate_el = row.find('.batch_rate');
  var rate = parseFloat(rate_el.val()) || 0;
  var bill_amt_el = row.find('.batch_bill_amount');
  var is_manual = bill_amt_el.attr('data-manual') === 'true';

  if (activeId === rate_el.attr('id')) {
    bill_amt_el.val(rate.toFixed(2));
    bill_amt_el.attr('data-manual', 'false');
  } else if (!is_manual && activeId !== bill_amt_el.attr('id')) {
    bill_amt_el.val(rate.toFixed(2));
  }

  var bill_amt = parseFloat(bill_amt_el.val()) || 0;
  var gst_per = parseFloat(row.find('.batch_gst_per').val()) || 0;
  
  var available_white = parseFloat(row.find('.available_white_qty').val()) || 0;
  var available_black = parseFloat(row.find('.available_black_qty').val()) || 0;

  var bill_total = bill_amt * white_qty;
  var gst_amt = (bill_total * gst_per) / 100;
  var total_bill_gst_amt = bill_total + gst_amt;
  var black_total_amt = ((rate * black_qty) + ((rate - bill_amt) * white_qty));
  var black_amt_unit = black_qty > 0 ? (black_total_amt / black_qty) : 0;
  var final_total = total_bill_gst_amt + black_total_amt;
  var total_batch_qty = white_qty + black_qty;
  var total_batch_amount_val = total_batch_qty * rate;

  row.find('.batch_total_amount').val(total_batch_amount_val.toFixed(2));
  if (activeId !== row.find('.batch_bill_total').attr('id')) {
    row.find('.batch_bill_total').val(bill_total.toFixed(2));
  }
  row.find('.batch_gst_amt').val(gst_amt.toFixed(2));
  row.find('.batch_total_bill_gst_amount').val(total_bill_gst_amt.toFixed(2));
  row.find('.batch_black_amt').val(black_amt_unit.toFixed(2));
  row.find('.batch_black_total_amt').val(black_total_amt.toFixed(2));
  row.find('.batch_final_total').val(final_total.toFixed(2));

  checkBatchRemarkRequirement(element);
  rollup_product_totals(index);
  recalculate();
}

function calculate_batch_amt_reverse(element, index) {
  var row = $(element).closest('.batch-row');
  var activeId = document.activeElement.id;
  var white_qty = parseFloat(row.find('.batch_white_qty_input').val()) || 0;
  var black_qty = parseFloat(row.find('.batch_black_qty_input').val()) || 0;
  var total_qty = white_qty + black_qty;
  var bill_total = parseFloat($(element).val()) || 0;

  markBatchManual(row.find('.batch_bill_amount'));

  if (total_qty > 0) {
    var bill_amt = bill_total / total_qty;
    if (activeId !== row.find('.batch_bill_amount').attr('id')) {
      row.find('.batch_bill_amount').val(bill_amt.toFixed(2));
    }
  }

  calculate_batch_amt(element, index);
}

function get_shipping_city(stateId) {
  $.ajax({
    type: "POST",
    url: "<?php echo base_url();?>admin/get_cities",
    data: { state_id: stateId },
    success: function (html) {
      $("#shipping_city_id").html(html);
    }
  });
}

function get_billing_city(stateId) {
  $.ajax({
    type: "POST",
    url: "<?php echo base_url();?>admin/get_cities",
    data: { state_id: stateId },
    success: function (html) {
      $("#billing_city_id").html(html);
    }
  });
}

function checkBatchRemarkRequirement(element) {
  var row = $(element).closest('.batch-row');
  var min_price = parseFloat(row.attr('data-min-price')) || 0;
  var rate = parseFloat(row.find('.batch_rate').val()) || 0;
  var remark_input = row.find('.batch_remark');
  var indicator = row.find('.batch_remark_indicator');
  var min_price_span = row.find('.min-price-val');

  if (min_price > 0 && rate < min_price) {
    indicator.removeClass('d-none').addClass('d-inline-flex');
    min_price_span.text(min_price.toFixed(2));
    remark_input.attr('required', 'required').addClass('border-danger');
  } else {
    indicator.addClass('d-none').removeClass('d-inline-flex');
    remark_input.removeAttr('required').removeClass('border-danger');
  }
}

$(document).ready(function() {
  $('.product_id').select2({ dropdownParent: $('body') });
  $('.batch_id').select2({ dropdownParent: $('body') });
  $('.charge_id').select2({ dropdownParent: $('body') });

  $('.batch-row').each(function() {
    checkBatchRemarkRequirement(this);
  });

  var s_state_id = "<?php echo $data['shipping_state_id'] ?? ''; ?>";
  var s_city_id = "<?php echo $data['shipping_city_id'] ?? ''; ?>";
  var b_state_id = "<?php echo $data['billing_state_id'] ?? ''; ?>";
  var b_city_id = "<?php echo $data['billing_city_id'] ?? ''; ?>";

  if (s_state_id) {
    $.ajax({
      type: "POST",
      url: "<?php echo base_url(); ?>admin/get_cities",
      data: { state_id: s_state_id },
      success: function(response) {
        $("#shipping_city_id").html(response).val(s_city_id).trigger("change");
      }
    });
  }

  if (b_state_id) {
    $.ajax({
      type: "POST",
      url: "<?php echo base_url(); ?>admin/get_cities",
      data: { state_id: b_state_id },
      success: function(response) {
        $("#billing_city_id").html(response).val(b_city_id).trigger("change");
      }
    });
  }

  $('.sales-line-item').each(function() {
    var index = $(this).data('id');
    updateProductOverallQty(index);
  });

  // Excel-like Keyboard Navigation
  $(document).on('keydown', '.compact-table input, .compact-table select', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      var $focusable = $('.compact-table').find('input:not([readonly]), select').filter(':visible');
      var index = $focusable.index(this);
      if (index > -1 && index < $focusable.length - 1) {
        var $next = $focusable.eq(index + 1);
        if ($next.hasClass('select2-hidden-accessible')) {
           $next.select2('focus');
           $next.select2('open');
        } else {
           $next.focus();
           $next.select();
        }
      }
    }
  });

  // Auto-open Select2 dropdown on focus (e.g. via Tab key)
  $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
    $(this).closest(".select2-container").siblings('select:enabled').select2('open');
  });

  // To prevent infinite loop if dropdown closes and focuses back on selection
  $('select.select2').on('select2:closing', function (e) {
    $(e.target).data('select2').$selection.one('focus focusin', function (e) {
      e.stopPropagation();
    });
  });

  change_gst($('#gst_type').val());
  recalculate();
});
</script>

<!-- Price History Modal -->
<div class="modal fade" id="priceHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Last Selling Prices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="priceHistoryModalContent">
                <!-- Content via AJAX -->
            </div>
        </div>
    </div>
</div>
