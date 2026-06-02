<style>
	.text-right {
		text-align: right;
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

	.select2-results__option[aria-selected] {
		cursor: pointer;
		font-weight: 800;
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

	.compact-table th, .compact-table td {
		padding: 4px !important;
		vertical-align: middle;
	}
	.compact-table thead th {
		background-color: #f3f6f9 !important;
		color: #3f4254 !important;
		font-weight: 700;
		text-transform: uppercase;
		font-size: 11px;
		letter-spacing: 0.5px;
		border-bottom: 2px solid #ebedf3 !important;
		padding: 10px 6px !important;
		white-space: nowrap;
	}
	.compact-table .form-control {
		height: 32px;
		min-height: 32px;
		padding: 4px 8px;
		font-size: 13px;
		border-radius: 4px;
		border: 1px solid #e4e6ef;
		transition: all 0.2s ease-in-out;
	}
	.compact-table .form-control:focus {
		border-color: #3699ff;
		box-shadow: 0 0 0 0.2rem rgba(54, 153, 255, 0.15);
	}
	.compact-table .form-control[readonly] {
		background-color: transparent !important;
		border-color: transparent !important;
		font-weight: 600;
		color: #3f4254;
		padding: 0;
		box-shadow: none;
	}
	.compact-table .input-group-text {
		height: 32px;
		min-height: 32px;
		padding: 0 8px !important;
		background: transparent;
		border: none;
		color: #a1a5b7;
	}
	.compact-table select.form-control {
		width: 100%;
	}
	.sales-line-item {
		background-color: #ffffff;
		border-bottom: 1px solid #ebedf3;
	}
	.sales-line-item td {
		padding: 8px 6px !important;
	}
	.batch-row {
		background-color: #f8f9fa !important;
		border-bottom: 1px dashed #e4e6ef;
	}
	.batch-row td {
		padding: 8px 6px !important;
	}
	.batch-row td:first-child {
		position: relative;
		padding-left: 28px !important;
	}
	.batch-row td:first-child::before {
		content: '';
		position: absolute;
		left: 12px;
		top: -1px;
		bottom: 50%;
		width: 12px;
		border-left: 2px solid #b5b5c3;
		border-bottom: 2px solid #b5b5c3;
		border-bottom-left-radius: 4px;
	}
	.btn-add-batch-row:focus {
		box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.5) !important;
		outline: none;
	}
	.btn-remove-batch-row:focus {
		box-shadow: 0 0 0 0.2rem rgba(234, 84, 85, 0.5) !important;
		outline: none;
	}

	/* Other charges adjustments */
	#charges_area tr .btn-add-charge {
		display: none;
	}
	#charges_area tr:last-child .btn-add-charge {
		display: inline-flex;
	}
	.gap-25 {
		gap: 0.25rem !important;
	}
</style>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body py-1 my-0">

				<?php echo form_open('inventory/sales_order/approve_post/' . $id, ['class' => 'add-ajax-redirect-form', 'onsubmit' => 'return checkForm(this);']); ?>
				<div class="row">
					<div class="col-12 col-sm-3 mb-1">
						<div class="form-group">
							<label>Order No <span class="required">*</span></label>
							<input type="text" class="form-control" placeholder="Order No" name="order_no" value="<?php echo $data['order_no']; ?>" readonly>
						</div>
					</div>

					<div class="col-12 col-sm-3 mb-1">
						<div class="form-group">
							<label>Refrence Order No </label>
							<input type="text" class="form-control" placeholder="Enter Order No" name="refrence_no" value="<?php echo $data['refrence_no']; ?>">
						</div>
					</div>

					<div class="col-12 col-sm-3 mb-1">
						<div class="form-group">
							<label>Date <span class="required">*</span></label>
							<input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $data['date']; ?>" id="date_picker">
						</div>
					</div>

					<div class="col-12 col-sm-3 mb-1">
						<label class="form-label" for="customer_id">Customer <span class="required">*</span></label>
						<select class="form-select select2" id="customer_id" disabled>
							<option value="">Select Customer </option>
							<?php foreach ($customer_list as $item) { ?>
								<option value="<?php echo $item['id']; ?>" <?php echo $data['customer_id'] == $item['id'] ? 'selected' : ''; ?>>
									<?php echo $item['owner_name']; ?>
								</option>
							<?php } ?>
						</select>
						<input type="hidden" name="customer_id" value="<?php echo $data['customer_id']; ?>">
					</div>

					<div class="col-12 col-sm-3 mb-1">
						<label class="form-label" for="warehouse_id">Warehouse <span class="required">*</span></label>
						<select class="form-select select2" name="warehouse_id" id="warehouse_id" onchange="clearAllBatches()" required>
							<option value="0">Select Warehouse</option>
							<?php foreach ($warehouse_list as $warehouse) { ?>
								<option value="<?php echo $warehouse->id; ?>" <?php echo $data['warehouse_id'] == $warehouse->id ? 'selected' : ''; ?>>
									<?php echo $warehouse->name; ?>
								</option>
							<?php } ?>
						</select>
					</div>

					<input type="hidden" name="company_id" value="<?php echo $data['company_id']; ?>">
					<input type="hidden" name="narration" value="<?php echo htmlspecialchars($data['narration'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

					<div class="col-12 col-sm-9 mb-1">
						<div class="form-group">
							<label>Remark</label>
							<textarea class="form-control" placeholder="" rows="1" name="remark" id="remark"><?php echo $data['remark']; ?></textarea>
						</div>
					</div>

					<div class="col-6 mb-1">
						<div class="row">
							<h6 class="mb-1">Shipping Address</h6>
							<div class="col-4 mb-1">
								<label class="form-label" for="shipping_state_id">Select State <span class="required">*</span></label>
								<select class="form-select select2 shipping_state_id" name="shipping_state_id" id="shipping_state_id" onchange="get_shipping_city(this.value);" required>
									<option value="">Select State</option>
									<?php foreach($states as $state){?>
									<option value="<?php echo $state['id'];?>" <?php if($data['shipping_state_id'] == $state['id']) echo 'selected'; ?>><?php echo $state['name'];?></option>
									<?php }?>
								</select>
							</div>
							<div class="col-4 mb-1">
								<label class="form-label" for="shipping_city_id">Select City <span class="required">*</span></label>
								<select class="form-select select2 shipping_city_id" name="shipping_city_id" id="shipping_city_id" required>
									<option value="">Select City</option>
								</select>
							</div>
							<div class="col-4 mb-1">
								<div class="form-group">
									<label>Pincode <span class="required">*</span></label>
									<input type="text" class="form-control" placeholder="Pincode" name="shipping_pincode" id="shipping_pincode" value="<?php echo $data['shipping_pincode'];?>" required>
								</div>
							</div>
							<div class="col-12 mb-1">
								<div class="form-group">
									<label>Address <span class="required">*</span></label>
									<textarea class="form-control" placeholder="Shipping Address" rows="2" name="shipping_address" id="shipping_address" required><?php echo $data['shipping_address'];?></textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="col-6 mb-1">
						<div class="row">
							<h6 class="mb-1">Billing Address</h6>
							<div class="col-4 mb-1">
								<label class="form-label" for="billing_state_id">Select State <span class="required">*</span></label>
								<select class="form-select select2 billing_state_id" name="billing_state_id" id="billing_state_id" onchange="get_billing_city(this.value);" required>
									<option value="">Select State</option>
									<?php foreach($states as $state){?>
									<option value="<?php echo $state['id'];?>" <?php if($data['billing_state_id'] == $state['id']) echo 'selected'; ?>><?php echo $state['name'];?></option>
									<?php }?>
								</select>
							</div>
							<div class="col-4 mb-1">
								<label class="form-label" for="billing_city_id">Select City <span class="required">*</span></label>
								<select class="form-select select2 billing_city_id" name="billing_city_id" id="billing_city_id" required>
									<option value="">Select City</option>
								</select>
							</div>
							<div class="col-4 mb-1">
								<div class="form-group">
									<label>Pincode <span class="required">*</span></label>
									<input type="text" class="form-control" placeholder="Pincode" name="billing_pincode" id="billing_pincode" value="<?php echo $data['billing_pincode'];?>" required>
								</div>
							</div>
							<div class="col-12 mb-1">
								<div class="form-group">
									<label>Address <span class="required">*</span></label>
									<textarea class="form-control" placeholder="Billing Address" rows="2" name="billing_address" id="billing_address" required><?php echo $data['billing_address'];?></textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12">
						<h6 class="mb-1">Products</h6>
						<div class="table-responsive">
							<table class="table table-bordered table-sm compact-table">
								<thead class="table-light text-center">
									<tr>
										<th style="min-width:200px;">Product <span class="text-danger">*</span></th>
										<th style="min-width:120px;">Qty <span class="text-danger">*</span></th>
										<th style="min-width:140px;">Per Qty Amt <span class="text-danger">*</span></th>
										<th style="min-width:100px;">Total Amt</th>
										<th style="min-width:120px;">Per Qty Bill <span class="text-danger">*</span></th>
										<th style="min-width:100px;">Total Bill</th>
										<th style="min-width:60px;">GST % <span class="text-danger">*</span></th>
										<th style="min-width:100px;">GST Amt</th>
										<th style="min-width:120px;">Total Bill GST</th>
										<th style="min-width:110px;">Per Qty Black</th>
										<th style="min-width:100px;">Total Black</th>
										<th style="min-width:120px;">Final Total</th>
										<th style="min-width:100px;">Act</th>
									</tr>
								</thead>
								<tbody id="requirement_area">
									<?php $k = 1; foreach ($data['products'] as $product) { ?>
									<?php
										$qty = (float) ($product['qty'] ?? 0);
										$amount = (float) ($product['amount'] ?? ($product['master_amount'] ?? 0));
										$total_amount = (float) ($product['total_amount'] ?? ($qty * $amount));
										$bill_amount = (float) ($product['bill_amount'] ?? ($product['white_amount'] ?? 0));
										$bill_total = (float) ($product['bill_total'] ?? ($product['white_total'] ?? 0));
										$gst = (float) ($product['gst'] ?? 0);
										$gst_amount = (float) ($product['gst_amount'] ?? 0);
										$total_bill_gst_amount = (float) ($product['total_bill_gst_amount'] ?? ($bill_total + $gst_amount));
										$black_amt = (float) ($product['black_amount'] ?? 0);
										$black_total = (float) ($product['black_total'] ?? ($product['black_amount'] ?? 0));
										$final_total = (float) ($product['final_total'] ?? ($total_bill_gst_amount + $black_total));
									?>
									<tr class="element-1 sales-line-item" id="product_<?php echo $k; ?>" data-id="<?php echo $k; ?>">
										<td>
											<input type="hidden" name="x_value[]" id="x_value_<?php echo $k; ?>" value="<?php echo $k; ?>">
											<input type="hidden" name="old_id[]" id="old_id_<?php echo $k; ?>" value="<?php echo $product['id']; ?>">
											<select class="form-control select2 product_id" id="product_id_<?php echo $k; ?>" disabled>
												<option value="">Select Product</option>
												<?php foreach ($products_list as $pl) { ?>
													<option value="<?php echo $pl['id']; ?>" <?php echo (string) $pl['id'] === (string) $product['product_id'] ? 'selected' : ''; ?>>
														<?php echo $pl['name']; ?>
													</option>
												<?php } ?>
											</select>
											<input type="hidden" name="product_id[]" value="<?php echo $product['product_id']; ?>">
										</td>
										<td>
											<input type="number" step="any" id="quantity_<?php echo $k; ?>" name="quantity[]" value="<?php echo $qty; ?>" class="form-control text-center" readonly>
										</td>
										<td>
											<input type="number" step="any" id="master_amount_<?php echo $k; ?>" name="master_amount[]" value="<?php echo number_format($amount, 2, '.', ''); ?>" class="form-control text-center" readonly>
										</td>
										<td>
											<input type="hidden" id="total_amount_<?php echo $k; ?>" name="total_amount[]" value="<?php echo number_format($total_amount, 2, '.', ''); ?>">
										</td>
										<td>
											<input type="hidden" id="bill_amount_<?php echo $k; ?>" name="bill_amount[]" value="<?php echo number_format($bill_amount, 2, '.', ''); ?>" data-manual="<?php echo $black_amt != 0 ? 'true' : 'false'; ?>">
										</td>
										<td>
											<input type="hidden" id="bill_total_<?php echo $k; ?>" name="bill_total[]" value="<?php echo number_format($bill_total, 2, '.', ''); ?>">
										</td>
										<td>
											<input type="number" step="any" id="gst_<?php echo $k; ?>" name="gst[]" value="<?php echo number_format($gst, 2, '.', ''); ?>" class="form-control text-center" readonly>
										</td>
										<td>
											<input type="hidden" id="gst_amount_<?php echo $k; ?>" name="gst_amount[]" value="<?php echo number_format($gst_amount, 2, '.', ''); ?>">
										</td>
										<td>
											<input type="hidden" id="total_bill_gst_amount_<?php echo $k; ?>" name="total_bill_gst_amount[]" value="<?php echo number_format($total_bill_gst_amount, 2, '.', ''); ?>">
										</td>
										<td>
											<input type="hidden" id="black_amount_per_unit_<?php echo $k; ?>" name="black_amt[]" value="<?php echo number_format($black_amt, 2, '.', ''); ?>">
										</td>
										<td>
											<input type="hidden" id="black_amount_<?php echo $k; ?>" name="black_total[]" value="<?php echo number_format($black_total, 2, '.', ''); ?>">
										</td>
										<td>
											<input type="hidden" id="final_total_<?php echo $k; ?>" name="final_total[]" value="<?php echo number_format($final_total, 2, '.', ''); ?>">
											<input type="hidden" id="available_<?php echo $k; ?>" name="available[]" value="<?php echo (float) ($product['available'] ?? 0); ?>">
										</td>
										<td class="text-center align-middle" style="white-space:nowrap;">
											<?php 
												$product_batches = $this->db->get_where('sales_order_product_batch', [
													'order_id' => $id,
													'order_product_id' => $product['id']
												])->result_array();
											?>
											<button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-batch" onclick="addBatch('<?php echo $k; ?>')" style="<?php echo !empty($product_batches) ? 'display: none;' : ''; ?>">
												<i class="fa fa-plus"></i> Add Batch
											</button>
										</td>
									</tr>
									<?php 
										$batch_index = 1;
										foreach ($product_batches as $batch) {
											// Get all batches of this product in this warehouse
											$all_batches = $this->db->query("SELECT id, batch_no, official_qty, black_qty FROM inventory WHERE warehouse_id = ? AND product_id = ? AND (batch_no = ?) GROUP BY batch_no", [$data['warehouse_id'], $product['product_id'], $batch['batch_no']])->result_array();
											
											// Find matching inventory batch
											$inventory_batch = $this->db->get_where('inventory', [
												'warehouse_id' => $data['warehouse_id'],
												'product_id' => $product['product_id'],
												'batch_no' => $batch['batch_no']
											])->row_array();

                      if($data['is_weird'] == 1) {
                        $avail_white = $inventory_batch['official_qty'] + $batch['white_qty'] + ($batch['black_qty'] - $batch['avail_black_qty']);
                        $avail_black = $inventory_batch['black_qty'];
                      } else {
                        $avail_white = $inventory_batch['official_qty'] + $batch['white_qty'];
                        $avail_black = $inventory_batch['black_qty'] + $batch['black_qty'];
                      }
											
											$selected_batch_id = $inventory_batch ? $inventory_batch['id'] : '';
									?>
										<tr class="batch-row batch-row-<?php echo $k; ?>">
											<td style="padding-left: 20px !important;">
												<select class="form-control select2 batch_id" readonly name="batch_id[<?php echo $k; ?>][]" id="batch_id_<?php echo $k; ?>_<?php echo $batch_index; ?>" onchange="getBatchDetails(this, '<?php echo $k; ?>')" >
													<!-- <option value="">Select Batch</option> -->
													<?php
                           foreach ($all_batches as $ab) { 
                            if($ab['id'] == $selected_batch_id) {
                          ?>
														<option value="<?php echo $ab['id']; ?>" <?php echo ($ab['id'] == $selected_batch_id) ? 'selected' : ''; ?>>
															<?php echo $ab['batch_no']; ?>
														</option>
													<?php }
                          } ?>
												</select>
											</td>
											<td>
												<div class="d-flex gap-25 align-items-center">
													<div class="d-flex flex-column align-items-center" style="flex: 1;">
														<span class="badge mb-25" style="font-size: 9px; padding: 2px 4px; background-color: #28c76f !important; color: #ffffff !important; font-weight: bold; display: inline-block;">Avail.<br> White: <span class="avail-white-text"><?php echo $avail_white; ?></span></span>
														<input type="number" step="any" class="form-control form-control-sm text-center batch_white_qty_input" name="batch_white_qty[<?php echo $k; ?>][]" id="batch_white_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" onkeyup="calculate_batch_amt(this, '<?php echo $k; ?>')" value="<?php echo $batch['white_qty']; ?>" style="padding: 2px; height: 26px;">
														<input type="hidden" class="available_white_qty" name="available_white_qty[<?php echo $k; ?>][]" id="available_white_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" value="<?php echo $avail_white; ?>">
													</div>
													<div class="d-flex flex-column align-items-center" style="flex: 1;">
														<span class="badge mb-25" style="font-size: 9px; padding: 2px 4px; background-color: #82868b !important; color: #ffffff !important; font-weight: bold; display: inline-block;">Avail.<br> Black: <span class="avail-black-text"><?php echo $avail_black; ?></span></span>
														<input type="number" step="any" class="form-control form-control-sm text-center batch_black_qty_input" name="batch_black_qty[<?php echo $k; ?>][]" id="batch_black_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" onkeyup="calculate_batch_amt(this, '<?php echo $k; ?>')" value="<?php echo $batch['black_qty']; ?>" style="padding: 2px; height: 26px;">
														<input type="hidden" class="available_black_qty" name="available_black_qty[<?php echo $k; ?>][]" id="available_black_qty_<?php echo $k; ?>_<?php echo $batch_index; ?>" value="<?php echo $avail_black; ?>">
													</div>
												</div>
											</td>
											<td>
												<div class="input-group">
													<input type="number" step="any" class="form-control batch_rate text-center" name="batch_rate[<?php echo $k; ?>][]" id="batch_rate_<?php echo $k; ?>_<?php echo $batch_index; ?>" onkeyup="calculate_batch_amt(this, '<?php echo $k; ?>')" value="<?php echo number_format($batch['amount'], 2, '.', ''); ?>">
													<span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('<?php echo $k; ?>')"><i class="fa fa-history px-1"></i></span>
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
											<td class="text-center align-middle" style="white-space:nowrap;">
												<button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-batch-row" onclick="addBatch('<?php echo $k; ?>')" title="Add another batch"><i class="fa fa-plus"></i></button>
												<!-- <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-batch-row" onclick="removeBatchRow(this, '<?php echo $k; ?>')" title="Remove batch"><i class="fa fa-times"></i></button> -->
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
										<th style="min-width:50px;">Act</th>
									</tr>
								</thead>
								<tbody id="charges_area">
									<?php 
									$c = 1; 
									if (!empty($data['other_charges'])) {
										foreach ($data['other_charges'] as $chg) { ?>
										<tr class="element-charge-<?php echo $c; ?> charge-line-item" id="charge_<?php echo $c; ?>" data-id="<?php echo $c; ?>">
											<td>
												<select class="form-control select2 charge_id" name="charge_id[]" id="charge_id_<?php echo $c; ?>" data-toggle="select2" onchange="get_charge_details(this.value, '<?php echo $c; ?>');">
													<option value="">Select Charges</option>
													<?php foreach ($other_charges as $charge) { ?>
														<option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>" <?php echo $chg['type_id'] == $charge['id'] ? 'selected' : ''; ?>>
															<?php echo $charge['name']; ?>
														</option>
													<?php } ?>
												</select>
											</td>
											<td><input type="number" step="any" id="charge_gst_<?php echo $c; ?>" name="charge_gst[]" placeholder="GST %" class="form-control charge-input" onkeyup="calculate_charge('<?php echo $c; ?>')" value="<?php echo $chg['gst']; ?>"></td>
											<td><input type="number" step="any" id="charge_price_<?php echo $c; ?>" name="charge_price[]" placeholder="Amount" class="form-control charge-input" onkeyup="calculate_charge('<?php echo $c; ?>')" value="<?php echo $chg['amount']; ?>"></td>
											<td><input type="number" step="any" id="charge_total_<?php echo $c; ?>" name="charge_total[]" placeholder="Total Amount" class="form-control" tabindex="-1" readonly value="<?php echo $chg['total_amt']; ?>"></td>
											<td class="text-center align-middle" style="white-space:nowrap;">
												<button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-charge" onclick="appendCharge()"> <i class="fa fa-plus" aria-hidden="true"></i> </button>
												<button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-charge" onclick="removeCharge(this, <?php echo $c; ?>)"> <i class="fa fa-times" aria-hidden="true"></i> </button>
											</td>
										</tr>
										<?php $c++; }
									} else { ?>
										<tr class="element-charge-1 charge-line-item" id="charge_1" data-id="1">
											<td>
												<select class="form-control select2 charge_id" name="charge_id[]" id="charge_id_1" data-toggle="select2" onchange="get_charge_details(this.value, '1');">
													<option value="">Select Charges</option>
													<?php foreach ($other_charges as $charge) { ?>
														<option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>">
															<?php echo $charge['name']; ?>
														</option>
													<?php } ?>
												</select>
											</td>
											<td><input type="number" step="any" id="charge_gst_1" name="charge_gst[]" placeholder="GST %" class="form-control charge-input" onkeyup="calculate_charge('1')" value="0"></td>
											<td><input type="number" step="any" id="charge_price_1" name="charge_price[]" placeholder="Amount" class="form-control charge-input" onkeyup="calculate_charge('1')" value="0"></td>
											<td><input type="number" step="any" id="charge_total_1" name="charge_total[]" placeholder="Total Amount" class="form-control" tabindex="-1" readonly value="0"></td>
											<td class="text-center align-middle" style="white-space:nowrap;">
												<button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-charge" onclick="appendCharge()"> <i class="fa fa-plus" aria-hidden="true"></i> </button>
												<button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-charge" onclick="removeCharge(this, 1)"> <i class="fa fa-times" aria-hidden="true"></i> </button>
											</td>
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
												<p class="td-blank"><input type="number" step="any" name="basic_value" id="basic_value" value="<?php echo number_format((float) ($data['basic_value'] ?? 0), 2, '.', ''); ?>" placeholder="Total Bill Amt (Exc GST)" class="form-control" readonly></p>
											</td>
										</tr>

										<tr>
											<td colspan="4" class="text-right align-middle">
												<div class="d-flex flex-column align-items-end">
													<span class="mb-0 text-capitalize">Select GST</span>
													<select class="form-control" name="gst_type" id="gst_type" onchange="change_gst(this.value); recalculate();" style="width: 200px !important; float: right !important">
														<option value="Central GST / State GST" <?php echo (($data['gst_type'] ?? '') == 'IGST') ? '' : 'selected'; ?>>Central GST / State GST</option>
														<option value="IGST" <?php echo (($data['gst_type'] ?? '') == 'IGST') ? 'selected' : ''; ?>>IGST</option>
													</select>
												</div>
											</td>
											<td colspan="1">
												<div id="cgst_sgst_inputs">
													<p class="td-blank mb-25">
														<input type="number" step="any" name="central_gst" id="central_gst" value="<?php echo number_format((float) ($data['central_gst'] ?? 0), 2, '.', ''); ?>" placeholder="CGST Amount" class="form-control" readonly>
													</p>
													<p class="td-blank mb-0">
														<input type="number" step="any" name="state_gst" id="state_gst" value="<?php echo number_format((float) ($data['state_gst'] ?? 0), 2, '.', ''); ?>" placeholder="SGST Amount" class="form-control" readonly>
													</p>
												</div>
												<div id="igst_input" class="hidden">
													<p class="td-blank mb-0">
														<input type="number" step="any" name="igst" id="igst" value="<?php echo number_format((float) ($data['igst'] ?? 0), 2, '.', ''); ?>" placeholder="IGST Amount" class="form-control" readonly>
													</p>
												</div>
											</td>
										</tr>

										<tr>
											<td colspan="4" class="text-right">
												<label>Total Bill Amt (Incl GST)</label>
											</td>
											<td colspan="1">
												<p class="td-blank"><input type="number" step="any" name="net_sales_value_1" id="net_sales_value_1" value="<?php echo number_format((float) ($data['net_sales_value_1'] ?? 0), 2, '.', ''); ?>" placeholder="Total Bill Amt (Incl GST)" class="form-control" readonly></p>
											</td>
										</tr>
										<tr>
											<td colspan="4" class="text-right">
												<label>Total Black Amt</label>
											</td>
											<td colspan="1">
												<p class="td-blank"><input type="number" step="any" name="total_black_amount_summary" id="total_black_amount_summary" value="<?php echo number_format((float) ($data['total_black_amt'] ?? 0), 2, '.', ''); ?>" placeholder="Total Black Amt" class="form-control" readonly></p>
											</td>
										</tr>
										<tr>
											<td colspan="4" class="text-right">
												<label>Final Total</label>
											</td>
											<td colspan="1">
												<p class="td-blank"><input type="number" step="any" name="net_sales_value_2" id="net_sales_value_2" value="<?php echo number_format((float) ($data['net_sales_value_2'] ?? 0), 2, '.', ''); ?>" placeholder="Final Total" class="form-control" readonly></p>
											</td>
										</tr>
										<tr>
											<td colspan="4" class="text-right">
												<label>Other Charges</label>
											</td>
											<td colspan="1">
												<p class="td-blank"><input type="number" step="any" name="other_charges_amount" id="other_charges_amount" placeholder="Charge Amount" class="form-control" value="<?php echo number_format((float) ($data['other_charges_amount'] ?? 0), 2, '.', ''); ?>" readonly></p>
											</td>
										</tr>
										<tr>
											<td colspan="4" class="text-right">
												<label>Round Of</label>
											</td>
											<td colspan="1">
												<p class="td-blank"><input type="number" step="any" name="round_of" id="round_of" placeholder="Round Of" class="form-control" value="<?php echo number_format((float) ($data['round_of'] ?? 0), 2, '.', ''); ?>" onkeyup="recalculate()"></p>
											</td>
										</tr>
										<tr>
											<td colspan="4" class="text-right">
												<label>Grand Total</label>
											</td>
											<td colspan="1">
												<p class="td-blank"><input type="number" step="any" name="grand_total" id="grand_total" value="<?php echo number_format((float) ($data['grand_total'] ?? 0), 2, '.', ''); ?>" placeholder="" class="form-control" readonly></p>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="col-12">
						<button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name="btn_verify"><?php echo get_phrase('submit'); ?></button>
					</div>
				</div>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>

<script>
function subtotal_cal() {
	var gst_type = $('#gst_type').val();
	var total_bill_amt_ex_gst = 0;
	var total_gst_amount = 0;
	var total_bill_amt_in_gst = 0;
	var total_black_amount = 0;
	var final_total_sum = 0;

	// Total calculations coming from batch module fields
	$('.batch_bill_total').each(function() {
		total_bill_amt_ex_gst += parseFloat($(this).val()) || 0;
	});

	$('.batch_gst_amt').each(function() {
		total_gst_amount += parseFloat($(this).val()) || 0;
	});

	$('.batch_total_bill_gst_amount').each(function() {
		total_bill_amt_in_gst += parseFloat($(this).val()) || 0;
	});

	$('.batch_black_total_amt').each(function() {
		total_black_amount += parseFloat($(this).val()) || 0;
	});

	$('.batch_final_total').each(function() {
		final_total_sum += parseFloat($(this).val()) || 0;
	});

	$("#basic_value").val(total_bill_amt_ex_gst.toFixed(2));
	$("#net_sales_value_1").val(total_bill_amt_in_gst.toFixed(2));
	$("#total_black_amount_summary").val(total_black_amount.toFixed(2));
	$("#net_sales_value_2").val(final_total_sum.toFixed(2));

	if (gst_type === 'IGST') {
		$('#igst').val(total_gst_amount.toFixed(2));
		$('#central_gst').val('0.00');
		$('#state_gst').val('0.00');
	} else {
		// Central GST / State GST: divide by 2
		$('#central_gst').val((total_gst_amount / 2).toFixed(2));
		$('#state_gst').val((total_gst_amount / 2).toFixed(2));
		$('#igst').val('0.00');
	}

	var total_charge_amt = 0;
	let chargeTotalArr = document.querySelectorAll('[name="charge_total[]"]');
	chargeTotalArr.forEach((element) => {
		total_charge_amt += Number(element.value) || 0;
	});

	$("#other_charges_amount").val(total_charge_amt.toFixed(2));

	var round_of = parseFloat($("#round_of").val()) || 0;
	
	// Grand Total = Final Total + Add : Other Charges + Round Of
	var grand_total = final_total_sum + total_charge_amt + round_of;
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
		// Reset if no allocation
		$('#bill_amount_' + index).val('0.00');
		$('#black_amount_per_unit_' + index).val('0.00');
	}
}

function calculate_batch_amt(element, index) {
	var row = $(element).closest('.batch-row');
	var activeId = document.activeElement.id;
	var batch_id = row.find('.batch_id').val();

	if (batch_id == '' || batch_id == null) {
		Swal.fire({
			title: "Error!",
			text: "Please select a batch first!",
			icon: "error",
			customClass: { confirmButton: "btn btn-primary" },
			buttonsStyling: !1
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

	// Validation: White Qty vs Available White
	if (white_qty > available_white) {
		Swal.fire({
			title: "Warning!",
			text: "White Quantity (" + white_qty + ") cannot exceed Available White Quantity (" + available_white + ")",
			icon: "warning",
			customClass: { confirmButton: "btn btn-primary" },
			buttonsStyling: !1
		});
		row.find('.batch_white_qty_input').val(0);
		white_qty = 0;
	}
	// Validation: Total Batch Qty vs Total Available in Batch (Allowing Black to take from White)
	if ((white_qty + black_qty) > (available_white + available_black)) {
		Swal.fire({
			title: "Warning!",
			text: "Total Batch Quantity (" + (white_qty + black_qty) + ") cannot exceed Available Total Quantity (" + (available_white + available_black) + ")",
			icon: "warning",
			customClass: { confirmButton: "btn btn-primary" },
			buttonsStyling: !1
		});
		row.find('.batch_black_qty_input').val(0);
		black_qty = 0;
	}

	// Validation: Total Batches White + Black Qty vs Product Qty
	var total_white_across_batches = 0;
	var total_black_across_batches = 0;
	$('.batch-row-' + index).each(function() {
		total_white_across_batches += parseFloat($(this).find('.batch_white_qty_input').val()) || 0;
		total_black_across_batches += parseFloat($(this).find('.batch_black_qty_input').val()) || 0;
	});

	if ((total_white_across_batches + total_black_across_batches) > product_qty) {
		Swal.fire({
			title: "Warning!",
			text: "Total Batch Quantity (" + (total_white_across_batches + total_black_across_batches) + ") cannot exceed Product Quantity (" + product_qty + ")",
			icon: "warning",
			customClass: { confirmButton: "btn btn-primary" },
			buttonsStyling: !1
		});
		$(element).val(0);
		white_qty = parseFloat(row.find('.batch_white_qty_input').val()) || 0;
		black_qty = parseFloat(row.find('.batch_black_qty_input').val()) || 0;
	}

	// Calculations
	var total_batch_qty = white_qty + black_qty;
	var bill_total = total_batch_qty * bill_amt;
	var gst_amt = (bill_total * gst_per) / 100;
	var total_bill_gst_amt = bill_total + gst_amt;
	var black_amt_unit = rate - bill_amt;
	var black_total_amt = total_batch_qty * black_amt_unit;
	var final_total = total_bill_gst_amt + black_total_amt;
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

function clearAllBatches() {
	$('.batch-row').remove();
	$('.btn-add-batch').show();
	recalculate();
}

function showPriceHistory(index) {
	var customer_id = $('[name="customer_id"]').val();
	var product_id = $('[name="product_id[]"]').eq(index - 1).val();

	if (!customer_id || !product_id) {
		alert('Customer and product must be selected.');
		return;
	}

	$.ajax({
		type: "POST",
		url: "<?php echo base_url() ?>inventory/get_last_selling_price",
		data: {
			customer_id: customer_id,
			product_id: product_id
		},
		success: function(res) {
			$('#priceHistoryModalContent').html(res);
			$('#priceHistoryModal').modal('show');
		}
	});
}

function addBatch(index) {
	var warehouse_id = $('#warehouse_id').val();
	var product_id = $('#product_' + index).find('input[name="product_id[]"]').val();

	if (warehouse_id == '0' || warehouse_id == '') {
		Swal.fire({
			title: "Error!",
			text: "Please select warehouse first",
			icon: "error",
			customClass: {
				confirmButton: "btn btn-primary"
			},
			buttonsStyling: !1
		});
		return;
	}

	var batch_index = 1;
	$('.batch-row-' + index).each(function() {
		var existing_id = $(this).find('.batch_id').attr('id') || '';
		var split_id = existing_id.split('_');
		var current_index = parseInt(split_id[split_id.length - 1], 10);
		if (!isNaN(current_index) && current_index >= batch_index) {
			batch_index = current_index + 1;
		}
	});

	var batch_row = `
		<tr class="batch-row batch-row-${index}">
			<td style="padding-left: 20px !important;">
				<select class="form-control select2 batch_id" name="batch_id[${index}][]" id="batch_id_${index}_${batch_index}" onchange="getBatchDetails(this, '${index}')">
					<option value="">Select Batch</option>
				</select>
			</td>
			<td>
				<div class="d-flex gap-25 align-items-center">
					<div class="d-flex flex-column align-items-center" style="flex: 1;">
						<span class="badge mb-25" style="font-size: 9px; padding: 2px 4px; background-color: #28c76f !important; color: #ffffff !important; font-weight: bold; display: inline-block;">Avail.<br> White: <span class="avail-white-text">0</span></span>
						<input type="number" step="any" class="form-control form-control-sm text-center batch_white_qty_input" name="batch_white_qty[${index}][]" id="batch_white_qty_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')" value="0" style="padding: 2px; height: 26px;">
						<input type="hidden" class="available_white_qty" name="available_white_qty[${index}][]" id="available_white_qty_${index}_${batch_index}" value="0">
					</div>
					<div class="d-flex flex-column align-items-center" style="flex: 1;">
						<span class="badge mb-25" style="font-size: 9px; padding: 2px 4px; background-color: #82868b !important; color: #ffffff !important; font-weight: bold; display: inline-block;">Avail.<br> Black: <span class="avail-black-text">0</span></span>
						<input type="number" step="any" class="form-control form-control-sm text-center batch_black_qty_input" name="batch_black_qty[${index}][]" id="batch_black_qty_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')" value="0" style="padding: 2px; height: 26px;">
						<input type="hidden" class="available_black_qty" name="available_black_qty[${index}][]" id="available_black_qty_${index}_${batch_index}" value="0">
					</div>
				</div>
			</td>
			<td>
				<div class="input-group">
					<input type="number" step="any" class="form-control batch_rate text-center" name="batch_rate[${index}][]" id="batch_rate_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')">
					<span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('${index}')"><i class="fa fa-history px-1"></i></span>
				</div>
			</td>
			<td>
				<input type="number" step="any" class="form-control batch_total_amount text-center" id="batch_total_amount_${index}_${batch_index}" readonly tabindex="-1">
			</td>
			<td>
				<input type="number" step="any" class="form-control batch_bill_amount text-center" name="batch_bill_amount[${index}][]" id="batch_bill_amount_${index}_${batch_index}" onkeyup="markBatchManual(this); calculate_batch_amt(this, '${index}')" data-manual="false">
			</td>
			<td>
				<input type="number" step="any" class="form-control batch_bill_total text-center" name="batch_bill_total[${index}][]" id="batch_bill_total_${index}_${batch_index}" onkeyup="calculate_batch_amt_reverse(this, '${index}')">
			</td>
			<td>
				<input type="number" step="any" class="form-control batch_gst_per text-center" name="batch_gst_per[${index}][]" id="batch_gst_per_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')">
			</td>
			<td>
				<input type="number" class="form-control batch_gst_amt text-center" name="batch_gst_amt[${index}][]" id="batch_gst_amt_${index}_${batch_index}" readonly tabindex="-1">
			</td>
			<td>
				<input type="number" class="form-control batch_total_bill_gst_amount text-center" name="batch_total_bill_gst_amount[${index}][]" id="batch_total_bill_gst_amount_${index}_${batch_index}" readonly tabindex="-1">
			</td>
			<td>
				<input type="number" class="form-control batch_black_amt text-center" name="batch_black_amt[${index}][]" id="batch_black_amt_${index}_${batch_index}" readonly tabindex="-1">
			</td>
			<td>
				<input type="number" class="form-control batch_black_total_amt" name="batch_black_total_amt[${index}][]" id="batch_black_total_amt_${index}_${batch_index}" readonly tabindex="-1">
			</td>
			<td>
				<input type="number" class="form-control batch_final_total text-center" name="batch_final_total[${index}][]" id="batch_final_total_${index}_${batch_index}" readonly tabindex="-1">
			</td>
			<td class="text-center align-middle" style="white-space:nowrap;">
				<button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-batch-row" onclick="addBatch('${index}')" title="Add another batch"><i class="fa fa-plus"></i></button>
				<button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-batch-row" onclick="removeBatchRow(this, '${index}')" title="Remove batch"><i class="fa fa-times"></i></button>
			</td>
		</tr>
	`;

	var last_element = $('#product_' + index);
	var existing_batches = $('.batch-row-' + index);
	if (existing_batches.length > 0) {
		last_element = existing_batches.last();
	}
	last_element.after(batch_row);

	// Hide the main "Add Batch" button
	$('#product_' + index).find('.btn-add-batch').hide();

	var new_select = $('.batch-row-' + index + ':last .batch_id');
	
	$.ajax({
		type: "POST",
		url: "<?php echo base_url() ?>inventory/get_batches_by_warehouse_product",
		data: {
			warehouse_id: warehouse_id,
			product_id: product_id
		},
		success: function(res) {
			new_select.append(res);
			new_select.select2();
			new_select.select2('open');
		}
	});
}

function removeBatchRow(element, index) {
	$(element).closest('.batch-row').remove();
	
	var remaining = $('.batch-row-' + index);
	if (remaining.length === 0) {
		$('#product_' + index).find('.btn-add-batch').show();
	}
	
	rollup_product_totals(index);
	recalculate();
}

function getBatchDetails(element, index) {
	var batch_id = $(element).val();
	var row = $(element).closest('.batch-row');

	if (batch_id == '') {
		row.find('.available_white_qty').val(0);
		row.find('.available_black_qty').val(0);
		row.find('.avail-white-text').text(0);
		row.find('.avail-black-text').text(0);
		row.find('.batch_white_qty_input').val(0);
		row.find('.batch_black_qty_input').val(0);
		row.find('.batch_rate').val(0);
		row.find('.batch_bill_amount').val(0).attr('data-manual', 'false');
		row.find('.batch_bill_total').val(0);
		row.find('.batch_gst_per').val(0);
		row.find('.batch_gst_amt').val(0);
		row.find('.batch_total_bill_gst_amount').val(0);
		row.find('.batch_black_amt').val(0);
		row.find('.batch_black_total_amt').val(0);
		row.find('.batch_final_total').val(0);
		row.find('.batch_total_amount').val(0);
		rollup_product_totals(index);
		recalculate();
		return;
	}

	// Duplicate check within same product
	var is_duplicate = false;
	$('.batch-row-' + index + ' .batch_id').not(element).each(function() {
		if ($(this).val() == batch_id) {
			is_duplicate = true;
			return false;
		}
	});

	if (is_duplicate) {
		Swal.fire({
			title: "Error!",
			text: "Batch already selected for this product!",
			icon: "error",
			customClass: {
				confirmButton: "btn btn-primary"
			},
			buttonsStyling: !1
		});
		$(element).val('').trigger('change.select2');
		row.find('.available_white_qty').val(0);
		row.find('.available_black_qty').val(0);
		row.find('.avail-white-text').text(0);
		row.find('.avail-black-text').text(0);
		row.find('.batch_white_qty_input').val(0);
		row.find('.batch_black_qty_input').val(0);
		row.find('.batch_rate').val(0);
		row.find('.batch_bill_amount').val(0).attr('data-manual', 'false');
		row.find('.batch_bill_total').val(0);
		row.find('.batch_gst_per').val(0);
		row.find('.batch_gst_amt').val(0);
		row.find('.batch_total_bill_gst_amount').val(0);
		row.find('.batch_black_amt').val(0);
		row.find('.batch_black_total_amt').val(0);
		row.find('.batch_final_total').val(0);
		row.find('.batch_total_amount').val(0);
		rollup_product_totals(index);
		recalculate();
		return;
	}

	$.ajax({
		type: "POST",
		url: "<?php echo base_url() ?>inventory/get_batch_qty_details",
		data: {
			batch_id: batch_id
		},
		dataType: 'json',
		success: function(res) {
			row.find('.available_white_qty').val(res.official_qty);
			row.find('.available_black_qty').val(res.black_qty);
			row.find('.avail-white-text').text(res.official_qty);
			row.find('.avail-black-text').text(res.black_qty);
			
			// Initialize Rate and GST from main row
			var main_rate = $('#master_amount_' + index).val();
			var main_gst = $('#gst_' + index).val();
			var main_bill_amt = $('#bill_amount_' + index).val();
			var is_main_manual = $('#bill_amount_' + index).attr('data-manual');

			row.find('.batch_rate').val(main_rate);
			row.find('.batch_bill_amount').val(main_bill_amt).attr('data-manual', is_main_manual);
			row.find('.batch_gst_per').val(main_gst);
			
			calculate_batch_amt(row.find('.batch_white_qty_input'), index);
		}
	});
}

function get_charge_details(val, index) {
	if (val == "") {
		$('#charge_gst_' + index).val(0);
		$('#charge_price_' + index).val(0);
		calculate_charge(index);
	} else {
		var option = $('#charge_id_' + index).find('option:selected');
		var gst = option.data('gst') || 0;
		var price = option.data('price') || 0;
		$('#charge_gst_' + index).val(gst);
		$('#charge_price_' + index).val(price);
		calculate_charge(index);
	}
}

function calculate_charge(index) {
	var charge_id = $('#charge_id_' + index).val();
	var gst = parseFloat($('#charge_gst_' + index).val()) || 0;
	var price = parseFloat($('#charge_price_' + index).val()) || 0;
	
	if (charge_id == "" && (gst > 0 || price > 0)) {
		Swal.fire({
			title: "Error!",
			text: "select the charges first",
			icon: "error"
		});
		$('#charge_gst_' + index).val(0);
		$('#charge_price_' + index).val(0);
		$('#charge_total_' + index).val(0);
		recalculate();
		return;
	}
	
	var total = price + (price * gst / 100);
	$('#charge_total_' + index).val(total.toFixed(2));
	recalculate();
}

function appendCharge() {
	var last_row = $("#charges_area .charge-line-item:last");
	var nextindex = 1;
	if (last_row.length > 0) {
		var currentId = last_row.data("id") || 0;
		nextindex = parseInt(currentId) + 1;
		
		var prev_charge = $('#charge_id_' + currentId).val();
		if (prev_charge == '') {
			Swal.fire({
				title: "Error!",
				text: "Please select previous charge !!",
				icon: "error"
			});
			return;
		}
	}
	
	$('#charges_area').append(`
		<tr class="element-charge-${nextindex} charge-line-item" id="charge_${nextindex}" data-id="${nextindex}">
			<td>
				<select class="form-control select2 charge_id" name="charge_id[]" id="charge_id_${nextindex}" data-toggle="select2" onchange="get_charge_details(this.value, '${nextindex}');">
					<option value="">Select Charges</option>
					<?php foreach($other_charges as $charge){ ?>
						<option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>"><?php echo $charge['name']; ?></option>
					<?php } ?>
				</select>
			</td>
			<td><input type="number" step="any" id="charge_gst_${nextindex}" name="charge_gst[]" placeholder="GST %" class="form-control charge-input" onkeyup="calculate_charge('${nextindex}')" value="0"></td>
			<td><input type="number" step="any" id="charge_price_${nextindex}" name="charge_price[]" placeholder="Amount" class="form-control charge-input" onkeyup="calculate_charge('${nextindex}')" value="0"></td>
			<td><input type="number" step="any" id="charge_total_${nextindex}" name="charge_total[]" placeholder="Total Amount" class="form-control" tabindex="-1" readonly value="0"></td>
			<td class="text-center align-middle" style="white-space:nowrap;">
				<button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-charge" onclick="appendCharge()"> <i class="fa fa-plus" aria-hidden="true"></i> </button>
				<button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-charge" onclick="removeCharge(this, ${nextindex})"> <i class="fa fa-times" aria-hidden="true"></i> </button>
			</td>
		</tr>
	`);
	
	$('#charge_id_' + nextindex).select2({ dropdownParent: $('body') });
	$('#charge_id_' + nextindex).select2('open');
}

function removeCharge(element, index) {
	if(document.querySelector('#charges_area').children.length > 1){
		$(element).closest('tr').remove();
		recalculate();
	} else {
		$('#charge_id_' + index).val("").trigger('change');
		$('#charge_gst_' + index).val(0);
		$('#charge_price_' + index).val(0);
		$('#charge_total_' + index).val(0);
		recalculate();
	}
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

$(document).ready(function() {
	var s_state_id = "<?php echo $data['shipping_state_id']; ?>";
	var s_city_id = "<?php echo $data['shipping_city_id']; ?>";
	var b_state_id = "<?php echo $data['billing_state_id']; ?>";
	var b_city_id = "<?php echo $data['billing_city_id']; ?>";

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

	<?php if ($this->session->userdata('super_type_id') == 7) : ?>
		$('#date_picker').prop('readonly', true);
		$('#date_picker').on('mousedown', function(e) {
			e.preventDefault();
		});
	<?php endif; ?>

	change_gst($('#gst_type').val());
	recalculate();
	$('.select2').select2();
	$('#charge_id_1').select2({ dropdownParent: $('body') });
});
</script>

<div class="modal fade" id="priceHistoryModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Last Selling Prices</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="priceHistoryModalContent"></div>
		</div>
	</div>
</div>
