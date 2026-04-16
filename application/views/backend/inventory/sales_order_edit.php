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
						<select class="form-select select2" name="warehouse_id" id="warehouse_id">
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
													<label>Amount <span class="required">*</span></label>
													<div class="input-group">
														<input type="number" step="any" id="master_amount_<?php echo $i; ?>" name="master_amount[]" onkeyup="calculate_amt('<?php echo $i; ?>')" value="<?php echo number_format($amount, 2, '.', ''); ?>" class="form-control">
														<span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('<?php echo $i; ?>')"><i class="fa fa-history px-1"></i></span>
													</div>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Total Amount</label>
													<input type="number" step="any" id="total_amount_<?php echo $i; ?>" name="total_amount[]" value="<?php echo number_format($total_amount, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Bill Amt <span class="required">*</span></label>
													<input type="number" step="any" id="bill_amount_<?php echo $i; ?>" name="bill_amount[]" onkeyup="markManual('<?php echo $i; ?>'); calculate_amt('<?php echo $i; ?>')" value="<?php echo number_format($bill_amount, 2, '.', ''); ?>" class="form-control" data-manual="<?php echo $black_amt != 0 ? 'true' : 'false'; ?>">
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Total Bill Amt</label>
													<input type="number" step="any" id="bill_total_<?php echo $i; ?>" name="bill_total[]" value="<?php echo number_format($bill_total, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>GST % <span class="required">*</span></label>
													<input type="number" step="any" id="gst_<?php echo $i; ?>" name="gst[]" onkeyup="calculate_amt('<?php echo $i; ?>')" value="<?php echo number_format($gst, 2, '.', ''); ?>" class="form-control">
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>GST Amt</label>
													<input type="number" step="any" id="gst_amount_<?php echo $i; ?>" name="gst_amount[]" value="<?php echo number_format($gst_amount, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Total Bill GST Amount</label>
													<input type="number" step="any" id="total_bill_gst_amount_<?php echo $i; ?>" name="total_bill_gst_amount[]" value="<?php echo number_format($total_bill_gst_amount, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Black Amt</label>
													<input type="number" step="any" id="black_amount_per_unit_<?php echo $i; ?>" name="black_amt[]" value="<?php echo number_format($black_amt, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Total Black Amount</label>
													<input type="number" step="any" id="black_amount_<?php echo $i; ?>" name="black_total[]" value="<?php echo number_format($black_total, 2, '.', ''); ?>" class="form-control" readonly>
												</div>
											</div>

											<div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
												<div class="form-group">
													<label>Final Total</label>
													<input type="number" step="any" id="final_total_<?php echo $i; ?>" name="final_total[]" value="<?php echo number_format($final_total, 2, '.', ''); ?>" class="form-control" readonly>
													<input type="hidden" id="available_<?php echo $i; ?>" name="available[]" value="<?php echo (float) ($item['available'] ?? 0); ?>">
												</div>
											</div>
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

	let totalBillAmt = document.querySelectorAll('[name="bill_total[]"]');
	let gstAmt = document.querySelectorAll('[name="gst_amount[]"]');
	let totalBillGstAmt = document.querySelectorAll('[name="total_bill_gst_amount[]"]');
	let totalBlackAmt = document.querySelectorAll('[name="black_total[]"]');
	let finalTotalArr = document.querySelectorAll('[name="final_total[]"]');

	totalBillAmt.forEach((element, index) => {
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
	} else {
		$('#central_gst').val((total_gst_amount / 2).toFixed(2));
		$('#state_gst').val((total_gst_amount / 2).toFixed(2));
		$('#igst').val('0.00');
	}

	var other_charges_amount = parseFloat($("#other_charges_amount").val()) || 0;
	var round_of = parseFloat($("#round_of").val()) || 0;
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
	var qty = Number($('#quantity_' + index).val()) || 0;
	var amount = Number($('#master_amount_' + index).val()) || 0;
	var bill_amt_el = $('#bill_amount_' + index);
	var is_manual = bill_amt_el.attr('data-manual') === 'true';

	var total_amount = qty * amount;
	if (!is_manual) {
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
	$('#bill_total_' + index).val(total_bill_amt.toFixed(2));
	$('#black_amount_per_unit_' + index).val(black_amt.toFixed(2));
	$('#black_amount_' + index).val(total_black_amt.toFixed(2));
	$('#gst_amount_' + index).val(gst_amt.toFixed(2));
	$('#total_bill_gst_amount_' + index).val(total_bill_gst_amt.toFixed(2));
	$('#final_total_' + index).val(final_total.toFixed(2));

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

$(document).ready(function() {
	<?php if ($this->session->userdata('super_type_id') == 7) : ?>
		$('#date_picker').prop('readonly', true);
		$('#date_picker').on('mousedown', function(e) {
			e.preventDefault();
		});
	<?php endif; ?>

	change_gst($('#gst_type').val());
	recalculate();
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
