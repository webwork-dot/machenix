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

	#requirement_area .select2-container--default .select2-selection--single .select2-selection__rendered,
		{
		color: #444;
		line-height: normal;
		font-weight: 800;
	}

	.select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 30px;
		min-height: 30px;
		line-height: normal;
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

	.batch-section-box {
		border: 1px solid #ccd9ea;
		background: #ffffff;
		padding: 10px;
		border-radius: 8px;
		margin-top: 5px;
		box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
	}

	.batch-row {
		border-bottom: 1px dashed #dce5f0;
		padding-bottom: 10px;
		margin-bottom: 10px;
	}

	.batch-row:last-child {
		border-bottom: none;
		margin-bottom: 0;
		padding-bottom: 0;
	}

	.batch-row label {
		font-size: 11px !important;
		color: #5e6d82 !important;
	}
</style>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body py-1 my-0">

				<?php echo form_open('inventory/sales_order/edit_post/' . $id, ['class' => 'add-ajax-redirect-form', 'onsubmit' => 'return checkForm(this);']); ?>
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
						<label class="form-label" for="state">Customer <span class="required">*</span></label>
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

					<div class="col-12 col-sm-3 mb-1 ">
						<label class="form-label" for="warehouse_id">Warehouse <span class="required">*</span></label>
						<select class="form-select select2" name="warehouse_id" id="warehouse_id" onchange="clearAllBatches()">
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

					<div class="col-12 col-sm-12 mb-1">
						<div class="form-group">
							<label>Remark</label>
							<textarea class="form-control" placeholder="" rows="1" name="remark" id="remark"><?php echo $data['remark']; ?></textarea>
						</div>
					</div>

					<div class="col-12">
						<div id="requirement_area">
							<?php $i = 1; ?>
							<?php foreach ($data['products'] as $item) { ?>
								<?php
								$qty = (float) ($item['qty'] ?? 0);
								$amount = (float) ($item['amount'] ?? ($item['master_amount'] ?? 0));
								$total_amount = (float) ($item['total_amount'] ?? ($qty * $amount));
								$bill_amount = (float) ($item['bill_amount'] ?? ($item['white_amount'] ?? 0));
								$bill_total = (float) ($item['bill_total'] ?? ($item['white_total'] ?? 0));
								$gst = (float) ($item['gst'] ?? 0);
								$gst_amount = (float) ($item['gst_amount'] ?? 0);
								$total_bill_gst_amount = (float) ($item['total_bill_gst_amount'] ?? ($bill_total + $gst_amount));
								$black_amt = (float) ($item['black_amount'] ?? 0);
								$black_total = (float) ($item['black_total'] ?? ($item['black_amount'] ?? 0));
								$final_total = (float) ($item['final_total'] ?? ($total_bill_gst_amount + $black_total));
								?>
								<div class="d-block mt-2 element-1 fx-border sales-line-item" id="product_<?php echo $i; ?>" data-id="<?php echo $i; ?>">
									<b class="jsr-no"><?php echo $i; ?></b>
									<div class="flex-grow-1 ">
										<div class="row g-1 align-items-end">

											<div class="col-xl-3 col-lg-4 col-md-6 px-1">
												<input type="hidden" name="x_value[]" id="x_value_<?php echo $i; ?>" value="<?php echo $i; ?>">
												<input type="hidden" name="old_id[]" id="old_id_<?php echo $i; ?>" value="<?php echo $item['id']; ?>">
												<div class="form-group">
													<label>Select Product<span class="required">*</span></label>
													<select class="form-control select2 product_id" id="product_id_<?php echo $i; ?>" disabled>
														<option value="">Select Product</option>
														<?php foreach ($products_list as $p_item) { ?>
															<option value="<?php echo $p_item['id']; ?>" <?php echo (string) $p_item['id'] === (string) $item['product_id'] ? 'selected' : ''; ?>>
																<?php echo $p_item['name']; ?>
															</option>
														<?php } ?>
													</select>
													<input type="hidden" name="product_id[]" value="<?php echo $item['product_id']; ?>">
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Qty <span class="required">*</span></label>
													<input type="number" step="any" id="quantity_<?php echo $i; ?>" name="quantity[]" placeholder="Qty" onkeyup="calculate_amt('<?php echo $i; ?>')" value="<?php echo $qty; ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Per Qty Amount <span class="required">*</span></label>
													<div class="input-group">
														<input type="number" step="any" id="master_amount_<?php echo $i; ?>" name="master_amount[]" onkeyup="calculate_amt('<?php echo $i; ?>')" value="<?php echo number_format($amount, 2, '.', ''); ?>" class="form-control" readonly>
														<span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('<?php echo $i; ?>')"><i class="fa fa-history px-1"></i></span>
													</div>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>Total Amount</label>
													<input type="number" step="any" id="total_amount_<?php echo $i; ?>" name="total_amount[]" value="<?php echo number_format($total_amount, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>Bill Amt <span class="required">*</span></label>
													<input type="number" step="any" id="bill_amount_<?php echo $i; ?>" name="bill_amount[]" onkeyup="markManual('<?php echo $i; ?>'); calculate_amt('<?php echo $i; ?>')" value="<?php echo number_format($bill_amount, 2, '.', ''); ?>" class="form-control" data-manual="<?php echo $black_amt != 0 ? 'true' : 'false'; ?>">
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>Total Bill Amt</label>
													<input type="number" step="any" id="bill_total_<?php echo $i; ?>" name="bill_total[]" value="<?php echo number_format($bill_total, 2, '.', ''); ?>" class="form-control" onkeyup="calculate_amt_reverse('<?php echo $i; ?>')">
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>GST % <span class="required">*</span></label>
													<input type="number" step="any" id="gst_<?php echo $i; ?>" name="gst[]" onkeyup="calculate_amt('<?php echo $i; ?>')" value="<?php echo number_format($gst, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>GST Amt</label>
													<input type="number" step="any" id="gst_amount_<?php echo $i; ?>" name="gst_amount[]" value="<?php echo number_format($gst_amount, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>Total Bill GST Amount</label>
													<input type="number" step="any" id="total_bill_gst_amount_<?php echo $i; ?>" name="total_bill_gst_amount[]" value="<?php echo number_format($total_bill_gst_amount, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>Black Amt</label>
													<input type="number" step="any" id="black_amount_per_unit_<?php echo $i; ?>" name="black_amt[]" value="<?php echo number_format($black_amt, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>Total Black Amount</label>
													<input type="number" step="any" id="black_amount_<?php echo $i; ?>" name="black_total[]" value="<?php echo number_format($black_total, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1 d-none">
												<div class="form-group">
													<label>Final Total</label>
													<input type="number" step="any" id="final_total_<?php echo $i; ?>" name="final_total[]" value="<?php echo number_format($final_total, 2, '.', ''); ?>" class="form-control" readonly>
													<input type="hidden" id="available_<?php echo $i; ?>" name="available[]" value="<?php echo (float) ($item['available'] ?? 0); ?>">
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>&nbsp;</label><br>
													<button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light" onclick="addBatch('<?php echo $i; ?>')">
														<i class="fa fa-plus"></i> Add Batch
													</button>
												</div>
											</div>
										</div>

										<div id="batch_container_<?php echo $i; ?>" class="mt-1 mx-1 batch-section-box" style="display:none;">
											
										</div>
									</div>
								</div>
								<?php $i++; ?>
							<?php } ?>
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
													<select class="form-control" name="gst_type" id="gst_type" onchange="change_gst(this.value); recalculate();" style="width : 200px !important;float:right !important">
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
												<label>Add : Other Charges</label>
												<input type="text" step="any" name="other_charges_name" id="other_charges_name" value="<?php echo $data['other_charges_name']; ?>" placeholder="Charge Name" class="form-control dis-input-1">
											</td>
											<td colspan="1">
												<p class="td-blank"><input type="number" step="any" name="other_charges_amount" id="other_charges_amount" placeholder="Charge Amount" class="form-control" value="<?php echo number_format((float) ($data['other_charges_amount'] ?? 0), 2, '.', ''); ?>" onkeyup="recalculate()"></p>
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

	var other_charges_amount = parseFloat($("#other_charges_amount").val()) || 0;
	var round_of = parseFloat($("#round_of").val()) || 0;
	
	// Grand Total = Final Total + Add : Other Charges + Round Of
	var grand_total = final_total_sum + other_charges_amount + round_of;
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

	var container = $('#batch_container_' + index);
	var product_qty = parseFloat($('#quantity_' + index).val()) || 0;
	
	var white_qty = parseFloat(row.find('.batch_white_qty_input').val()) || 0;
	var black_qty = parseFloat(row.find('.batch_black_qty_input').val()) || 0;
	var rate = parseFloat(row.find('.batch_rate').val()) || 0;
	var bill_amt_el = row.find('.batch_bill_amount');
	var is_manual = bill_amt_el.attr('data-manual') === 'true';

	if (!is_manual && activeId !== bill_amt_el.attr('id')) {
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
	container.find('.batch-row').each(function() {
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

	if (activeId !== row.find('.batch_bill_total').attr('id')) {
		row.find('.batch_bill_total').val(bill_total.toFixed(2));
	}
	row.find('.batch_gst_amt').val(gst_amt.toFixed(2));
	row.find('.batch_total_bill_gst_amount').val(total_bill_gst_amt.toFixed(2));
	row.find('.batch_black_amt').val(black_amt_unit.toFixed(2));
	row.find('.batch_black_total_amt').val(black_total_amt.toFixed(2));
	row.find('.batch_final_total').val(final_total.toFixed(2));

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
	$('.batch-section-box').empty().hide();
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

	var batch_container = $('#batch_container_' + index);
	batch_container.show();
	var batch_index = 1;
	batch_container.find('.batch_id').each(function() {
		var existing_id = $(this).attr('id') || '';
		var split_id = existing_id.split('_');
		var current_index = parseInt(split_id[split_id.length - 1], 10);
		if (!isNaN(current_index) && current_index >= batch_index) {
			batch_index = current_index + 1;
		}
	});

	var batch_row = `
		<div class="row g-1 align-items-end mb-1 batch-row">
			<div class="col-xl-2 col-lg-3 col-md-4 px-1">
				<div class="form-group">
					<label>Select Batch</label>
					<select class="form-control select2 batch_id" name="batch_id[${index}][]" id="batch_id_${index}_${batch_index}" onchange="getBatchDetails(this, '${index}')">
						<option value="">Select Batch</option>
					</select>
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Avail. White Qty</label>
					<input type="number" class="form-control available_white_qty" name="available_white_qty[${index}][]" id="available_white_qty_${index}_${batch_index}" readonly>
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Avail. Black Qty</label>
					<input type="number" class="form-control available_black_qty" name="available_black_qty[${index}][]" id="available_black_qty_${index}_${batch_index}" readonly>
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>White Qty</label>
					<input type="number" class="form-control batch_white_qty_input" name="batch_white_qty[${index}][]" id="batch_white_qty_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')" value="0">
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Black Qty</label>
					<input type="number" class="form-control batch_black_qty_input" name="batch_black_qty[${index}][]" id="batch_black_qty_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')" value="0">
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Per Qty Amount</label>
					<input type="number" step="any" class="form-control batch_rate" name="batch_rate[${index}][]" id="batch_rate_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')">
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Per Qty Bill Amt</label>
					<input type="number" step="any" class="form-control batch_bill_amount" name="batch_bill_amount[${index}][]" id="batch_bill_amount_${index}_${batch_index}" onkeyup="markBatchManual(this); calculate_batch_amt(this, '${index}')" data-manual="false">
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Total Bill Amt</label>
					<input type="number" step="any" class="form-control batch_bill_total" name="batch_bill_total[${index}][]" id="batch_bill_total_${index}_${batch_index}" onkeyup="calculate_batch_amt_reverse(this, '${index}')">
				</div>
			</div>
			<div class="col-xl-1 col-lg-1 col-md-1 px-1">
				<div class="form-group">
					<label>GST %</label>
					<input type="number" step="any" class="form-control batch_gst_per" name="batch_gst_per[${index}][]" id="batch_gst_per_${index}_${batch_index}" onkeyup="calculate_batch_amt(this, '${index}')">
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>GST Amt</label>
					<input type="number" class="form-control batch_gst_amt" name="batch_gst_amt[${index}][]" id="batch_gst_amt_${index}_${batch_index}" readonly>
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Total Bill GST</label>
					<input type="number" class="form-control batch_total_bill_gst_amount" name="batch_total_bill_gst_amount[${index}][]" id="batch_total_bill_gst_amount_${index}_${batch_index}" readonly>
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Per Qty Black Amt</label>
					<input type="number" class="form-control batch_black_amt" name="batch_black_amt[${index}][]" id="batch_black_amt_${index}_${batch_index}" readonly>
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Total Black Amt</label>
					<input type="number" class="form-control batch_black_total_amt" name="batch_black_total_amt[${index}][]" id="batch_black_total_amt_${index}_${batch_index}" readonly>
				</div>
			</div>
			<div class="col-xl-1 col-lg-2 col-md-2 px-1">
				<div class="form-group">
					<label>Final Total</label>
					<input type="number" class="form-control batch_final_total" name="batch_final_total[${index}][]" id="batch_final_total_${index}_${batch_index}" readonly>
				</div>
			</div>
			<div class="col-xl-1 col-lg-1 col-md-1 px-1">
				<button type="button" class="btn btn-danger btn-sm mb-25" onclick="$(this).closest('.batch-row').remove(); recalculate();"><i class="fa fa-times"></i></button>
			</div>
		</div>
	`;

	batch_container.append(batch_row);
	var new_select = $('#batch_container_' + index + ' .batch-row:last .batch_id');
	
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
		}
	});
}

function getBatchDetails(element, index) {
	var batch_id = $(element).val();
	var row = $(element).closest('.batch-row');
	var container = $('#batch_container_' + index);

	if (batch_id == '') {
		row.find('.available_white_qty').val(0);
		row.find('.available_black_qty').val(0);
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
		return;
	}

	// Duplicate check within same product
	var is_duplicate = false;
	container.find('.batch_id').not(element).each(function() {
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

$(document).ready(function() {
	<?php if ($this->session->userdata('super_type_id') == 7) : ?>
		$('#date_picker').prop('readonly', true);
		$('#date_picker').on('mousedown', function(e) {
			e.preventDefault();
		});
	<?php endif; ?>

	change_gst($('#gst_type').val());
	recalculate();
	$('.select2').select2();
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
