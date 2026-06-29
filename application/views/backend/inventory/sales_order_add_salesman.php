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

	#requirement_area .select2-container--default .select2-selection--single .select2-selection__rendered,
		{
		color: #444;
		line-height: normal;
		font-weight: 800;
	}

	.select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 30px;
		/* Adjust the line-height to change the height */
		min-height: 30px;
		line-height: normal;
	}

	.select2-container--default .select2-selection--single {
		height: 30px;
		/* Adjust the height as needed */
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

	.sales-line-item .btn-remove-line,
	.sales-line-item .btn-add-line,
	.charge-line-item .btn-remove-charge,
	.charge-line-item .btn-add-charge {
		width: 34px;
		height: 34px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}
	.sales-line-item .btn-remove-line:focus,
	.sales-line-item .btn-add-line:focus,
	.charge-line-item .btn-remove-charge:focus,
	.charge-line-item .btn-add-charge:focus {
		box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.4) !important;
		outline: none;
	}

	/* Compact Table Styles */
	#charges_area tr .btn-add-charge {
		display: none;
	}
	#charges_area tr:last-child .btn-add-charge {
		display: inline-flex;
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

        <?php echo form_open('inventory/sales_order/add_salesman_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return validateForm() && checkForm(this);']);?>
        <div class="row">
          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Order No <span class="required">*</span></label>
              <input type="text" class="form-control" placeholder="Order No" name="order_no"
                value="<?php echo $order_no;?>" required="" readonly>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Refrence Order No </label>
              <input type="text" class="form-control" placeholder="Enter Order No" name="refrence_no">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>"
                value="<?php echo date('Y-m-d');?>" id="date_picker">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Customer <span class="required">*</span></label>
            <select class=" form-select select2" name="customer_id" id="customer_id" required>
              <option value="">Select Customer </option>
              <?php foreach($customer_list as $item){?>
              	<option value="<?php echo $item['id'];?>"><?php echo $item['company_name'];?></option>
              <?php }?>
            </select>
          </div>

          <div class="col-6 mb-1">
            <div class="row">
              <h6 class="mb-1">Shipping Address</h6>
              <div class="col-4 mb-1">
                <label class="form-label" for="shipping_state">Select State</label>
                <select class="form-select select2 shipping_state_id" name="shipping_state_id" id="shipping_state_id" onchange="get_shipping_city(this.value);">
                  <option value="">Select State</option>
                  <?php foreach($states as $state){?>
                  <option value="<?php echo $state['id'];?>"><?php echo $state['name'];?></option>
                  <?php }?>
                </select>
              </div>
              <div class="col-4 mb-1">
                <label class="form-label" for="shipping_city">Select City</label>
                <select class="form-select select2 shipping_city_id" name="shipping_city_id" id="shipping_city_id">
                  <option value="">Select City</option>
                </select>
              </div>
              <div class="col-4 mb-1">
                <div class="form-group">
                  <label>Pincode</label>
                  <input type="text" class="form-control" placeholder="Pincode" name="shipping_pincode" id="shipping_pincode">
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST Name</label>
                  <input type="text" class="form-control" placeholder="GST Name" name="shipping_gst" id="shipping_gst">
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST No</label>
                  <input type="text" class="form-control" placeholder="GST No" name="shipping_gst_no" id="shipping_gst_no">
                </div>
              </div>
              <div class="col-12 mb-1">
                <div class="form-group">
                  <label>Address</label>
                  <textarea class="form-control" placeholder="Shipping Address" rows="2" name="shipping_address" id="shipping_address"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="col-6 mb-1">
            <div class="row">
              <h6 class="mb-1">Billing Address</h6>
              <div class="col-4 mb-1">
                <label class="form-label" for="billing_state">Select State</label>
                <select class="form-select select2 billing_state_id" name="billing_state_id" id="billing_state_id" onchange="get_billing_city(this.value);">
                  <option value="">Select State</option>
                  <?php foreach($states as $state){?>
                  <option value="<?php echo $state['id'];?>"><?php echo $state['name'];?></option>
                  <?php }?>
                </select>
              </div>
              <div class="col-4 mb-1">
                <label class="form-label" for="billing_city">Select City</label>
                <select class="form-select select2 billing_city_id" name="billing_city_id" id="billing_city_id">
                  <option value="">Select City</option>
                </select>
              </div>
              <div class="col-4 mb-1">
                <div class="form-group">
                  <label>Pincode</label>
                  <input type="text" class="form-control" placeholder="Pincode" name="billing_pincode" id="billing_pincode">
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST Name</label>
                  <input type="text" class="form-control" placeholder="GST Name" name="billing_gst" id="billing_gst">
                </div>
              </div>
              <div class="col-6 mb-1">
                <div class="form-group">
                  <label>GST No</label>
                  <input type="text" class="form-control" placeholder="GST No" name="billing_gst_no" id="billing_gst_no">
                </div>
              </div>
              <div class="col-12 mb-1">
                <div class="form-group">
                  <label>Address</label>
                  <textarea class="form-control" placeholder="Billing Address" rows="2" name="billing_address" id="billing_address"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="col-6 mb-1">
            
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="warehouse_id">Warehouse <span class="required">*</span></label>
            <select class="form-select select2" name="warehouse_id" id="warehouse_id" onchange="clearAllBatches()" required>
              <option value="0">Select Warehouse</option>
              <?php foreach ($warehouse_list as $warehouse) { ?>
                <option value="<?php echo $warehouse->id; ?>"><?php echo $warehouse->name; ?></option>
              <?php } ?>
            </select>
          </div>

					<input type="hidden" name="company_id" value="<?php echo $this->session->userdata('company_id'); ?>">
					<input type="hidden" name="narration" value="">
          <!-- <div class="col-12 col-sm-12 mb-1 mt-1">
            <div class="form-group">
              <label>Narration</label>
              <textarea class="form-control" placeholder="" rows="1" name="narration" id="narration"></textarea>
            </div>
          </div> -->

          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Remark</label>
              <textarea class="form-control" placeholder="" rows="1" name="remark" id="remark"></textarea>
            </div>
          </div>

          <div class="col-12">
            <h6 class="mb-1">Products</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-sm compact-table">
                <thead class="table-light text-center">
                  <tr>
                    <th style="min-width:200px;">Product <span class="text-danger">*</span></th>
                    <th style="min-width:50px;">Qty <span class="text-danger">*</span></th>
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
                    <th style="min-width:50px;">Act</th>
                  </tr>
                </thead>
                <tbody id="requirement_area">
                  <tr class="element-1 sales-line-item" id="product_1" data-id="1">
                    <td>
                      <input type="hidden" name="x_value[]" id="x_value_1" value="1">
                      <input type="hidden" name="old_id[]" id="old_id_1" value="0">
                      <select class="form-control select2 product_id" name="product_id[]" id="product_id_1" data-toggle="select2" onchange="get_details_by_product(this.value,'1');" required>
                        <option value="">Select Product</option>
                      </select>
                    </td>
                    <td><input type="number" step="any" id="quantity_1" name="quantity[]" placeholder="Qty" onkeyup="calculate_amt('1')" value="1" class="form-control" required=""></td>
                    <td>
                      <div class="input-group">
                        <input type="number" step="any" id="master_amount_1" name="master_amount[]" onkeyup="calculate_amt('1')" value="" class="form-control">
                        <span class="input-group-text p-0 price-history-btn" tabindex="0" style="cursor:pointer" data-row-index="1" onclick="showPriceHistory('1')"><i class="fa fa-history px-1"></i></span>
                      </div>
                    </td>
                    <td><input type="hidden" id="total_amount_1" name="total_amount[]" value="0"></td>
                    <td><input type="hidden" id="bill_amount_1" name="bill_amount[]" value="0" data-manual="false"></td>
                    <td><input type="hidden" id="bill_total_1" name="bill_total[]" value="0"></td>
                    <td><input type="number" step="any" id="gst_1" name="gst[]" onkeyup="calculate_amt('1')" value="" class="form-control"></td>
                    <td><input type="hidden" id="gst_amount_1" name="gst_amount[]" value="0"></td>
                    <td><input type="hidden" id="total_bill_gst_amount_1" name="total_bill_gst_amount[]" value="0"></td>
                    <td><input type="hidden" id="black_amount_per_unit_1" name="black_amt[]" value="0"></td>
                    <td><input type="hidden" id="black_amount_1" name="black_total[]" value="0"></td>
                    <td>
                      <input type="hidden" id="final_total_1" name="final_total[]" value="0">
                      <input type="hidden" id="available_1" name="available[]" value="0">
                    </td>
                    <td class="text-center align-middle" style="white-space:nowrap;">
                      <button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-batch" onclick="addBatch('1')"> <i class="fa fa-plus"></i> Add Batch </button>
                      <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-line" onclick="removeRequirement(this,1)"> <i class="fa fa-times" aria-hidden="true"></i> </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-50 mb-1">
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="appendRequirement()">
                <i class="fa fa-plus"></i> Add Product
              </button>
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
                  <tr class="element-charge-1 charge-line-item" id="charge_1" data-id="1">
                    <td>
                      <select class="form-control select2 charge_id" name="charge_id[]" id="charge_id_1" data-toggle="select2" onchange="get_charge_details(this.value, '1');">
                        <option value="">Select Charges</option>
                        <?php foreach($other_charges as $charge) { ?>
                          <option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>"><?php echo $charge['name']; ?></option>
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
                            value="0" placeholder="Total Bill Amt (Exc GST)" class="form-control" readonly></p>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <div class="d-flex flex-column align-items-end">
                          <span class="mb-0 text-capitalize">Select GST</span>
                          <select class="form-control " name="gst_type" id="gst_type" onchange="change_gst(this.value); recalculate();" style="width : 200px !important;float:right !important">
                            <option value="Central GST / State GST" selected>Central GST / State GST</option>
                            <option value="IGST">IGST</option>
                          </select>
                        </div>
                      </td>
                      <td colspan="1">
                        <div id="cgst_sgst_inputs">
                          <p class="td-blank mb-25">
                            <input type="number" step="any" name="central_gst" id="central_gst" value="0" placeholder="CGST Amount" class="form-control" readonly>
                          </p>
                          <p class="td-blank mb-0">
                            <input type="number" step="any" name="state_gst" id="state_gst" value="0" placeholder="SGST Amount" class="form-control" readonly>
                          </p>
                        </div>
                        <div id="igst_input" class="hidden">
                          <p class="td-blank mb-0">
                            <input type="number" step="any" name="igst" id="igst" value="0" placeholder="IGST Amount" class="form-control" readonly>
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
                            id="net_sales_value_1" value="0" placeholder="Total Bill Amt (Incl GST)"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Total Black Amt</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="total_black_amount_summary"
                            id="total_black_amount_summary" value="0" placeholder="Total Black Amt"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Final Total</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="net_sales_value_2"
                            id="net_sales_value_2" value="0" placeholder="Final Total"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Other Charges</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="other_charges_amount"
                            id="other_charges_amount" placeholder="Charge Amount" class="form-control" value="0" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Round Of</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="round_of" id="round_of"
                            placeholder="Round Of" class="form-control" value="0" onkeyup="recalculate()"></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Grand Total</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="grand_total" id="grand_total" placeholder="" class="form-control" readonly></p>
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
};

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


function appendRequirement() {
  var customer_id = $('#customer_id').find(":selected").val();

  if (customer_id == '') {
    Swal.fire({
      title: "Error!",
      text: "Please Select Customer !!",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
  } else {
    var last_row = $("#requirement_area .element-1:last");
    var nextindex = 1;
    if (last_row.length > 0) {
      var lastid = last_row.attr("id");
      var split_id = lastid.split("_");
      nextindex = Number(split_id[1]) + 1;
      
      var prev_product = $('#product_id_' + split_id[1]).val();
      if (prev_product == '') {
        Swal.fire({
          title: "Error!",
          text: "Please Select Previous Product !!",
          icon: "error"
        });
        return;
      }
    }
    
    $(".loader").show();
    
    $('#requirement_area').append(`
      <tr class="element-1 sales-line-item" id="product_${nextindex}" data-id="${nextindex}">
        <td>
          <input type="hidden" name="x_value[]" id="x_value_${nextindex}" value="${nextindex}">
          <input type="hidden" name="old_id[]" id="old_id_${nextindex}" value="0">
          <select class="form-control select2 product_id" name="product_id[]" id="product_id_${nextindex}" onchange="get_details_by_product(this.value,'${nextindex}');" required>
            <option value="">Select Product</option>
          </select>
        </td>
        <td><input type="number" step="any" id="quantity_${nextindex}" name="quantity[]" placeholder="Qty" value="1" class="form-control" onkeyup="calculate_amt('${nextindex}')" required></td>
        <td>
          <div class="input-group">
            <input type="number" step="any" id="master_amount_${nextindex}" name="master_amount[]" class="form-control" onkeyup="calculate_amt('${nextindex}')">
            <span class="input-group-text p-0 price-history-btn" tabindex="0" style="cursor:pointer" data-row-index="${nextindex}" onclick="showPriceHistory('${nextindex}')"><i class="fa fa-history px-1"></i></span>
          </div>
        </td>
        <td><input type="hidden" id="total_amount_${nextindex}" name="total_amount[]" value="0"></td>
        <td><input type="hidden" id="bill_amount_${nextindex}" name="bill_amount[]" value="0" data-manual="false"></td>
        <td><input type="hidden" id="bill_total_${nextindex}" name="bill_total[]" value="0"></td>
        <td><input type="number" step="any" id="gst_${nextindex}" name="gst[]" class="form-control" onkeyup="calculate_amt('${nextindex}')"></td>
        <td><input type="hidden" id="gst_amount_${nextindex}" name="gst_amount[]" value="0"></td>
        <td><input type="hidden" id="total_bill_gst_amount_${nextindex}" name="total_bill_gst_amount[]" value="0"></td>
        <td><input type="hidden" id="black_amount_per_unit_${nextindex}" name="black_amt[]" value="0"></td>
        <td><input type="hidden" id="black_amount_${nextindex}" name="black_total[]" value="0"></td>
        <td>
          <input type="hidden" id="final_total_${nextindex}" name="final_total[]" value="0">
          <input type="hidden" id="available_${nextindex}" name="available[]" value="0">
        </td>
        <td class="text-center align-middle" style="white-space:nowrap;">
          <button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-batch" onclick="addBatch('${nextindex}')">
            <i class="fa fa-plus"></i> Add Batch
          </button>
          <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-line" onclick="removeRequirement(this,${nextindex})">
            <i class="fa fa-times"></i>
          </button>
        </td>
      </tr>
    `);
    
    $.ajax({
      type: "POST",
      url: "<?php echo base_url()?>inventory/get_product_by_company",
      data: {},
      success: function(res) {
        console.log("Products Loaded:", res);
        var select = $('#product_id_' + nextindex);
        select.html('<option value="">Select Product</option>' + res).trigger('change');
        select.select2({ dropdownParent: $('body') });
        select.select2('open');
        $(".loader").fadeOut("slow");
        
        $('html, body').animate({
          scrollTop: $("#product_" + nextindex).offset().top
        }, 300);
      },
      error: function(xhr) {
        console.log(xhr.responseText);
        alert("Error loading products");
        $(".loader").fadeOut("slow");
      }
    });
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

  function isDuplicateProductSelection(product_id, index) {
    var selectedId = String(product_id || '').split('|')[0];
    var duplicateFound = false;

    $('.product_id').each(function() {
      var thisId = this.id || '';
      if (thisId === ('product_id_' + index)) return;

      var otherId = String($(this).val() || '').split('|')[0];
      if (otherId !== '' && otherId === selectedId) {
        duplicateFound = true;
      }
    });

    return duplicateFound;
  }

  function resetLineItem(index) {
    $('#available_' + index).val(0);
    $('#master_amount_' + index).val('');
    $('#bill_amount_' + index).val('');
    $('#gst_' + index).val('');
    $('#total_amount_' + index).val('');
    $('#bill_total_' + index).val('');
    $('#gst_amount_' + index).val('');
    $('#total_bill_gst_amount_' + index).val('');
    $('#black_amount_per_unit_' + index).val('');
    $('#black_amount_' + index).val('');
    $('#final_total_' + index).val('');
    recalculate();
  }

  function get_details_by_product(product_id, index) {
    if(!product_id) return;
    
    // Clear any existing batches on product change
    $('.batch-row-' + index).remove();
    $('#product_' + index).find('.btn-add-batch').show();
    toggleMainRowReadonly(index, false);

    if (isDuplicateProductSelection(product_id, index)) {
      Swal.fire({
        title: "Error!",
        text: "Same product cannot be selected more than once.",
        icon: "warning"
      });
      $('#product_id_' + index).val('').trigger('change');
      resetLineItem(index);
      return;
    }

    var customer_id = $('#customer_id').val();

    $.ajax({
      type: "POST",
      url: "<?php echo base_url()?>inventory/get_qty_by_product_company",
      data: { product_id: product_id, customer_id: customer_id },
      dataType: "json",
      success: function(res) {
          if(res.status == 200) {
              $('#available_' + index).val(res.quantity);
              $('#gst_' + index).val(res.tax ? parseFloat(res.tax) : 0);
              $('#master_amount_' + index).val(res.rate);
              $('#bill_amount_' + index).attr('data-manual', 'false');
              
              $('#bill_amount_' + index).val(res.rate); // Bill Amt is per-unit and defaults to Amount
              calculate_amt(index);
          } else {
              alert(res.message);
          }
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

  function removeRequirement(requirementElem, index) {
    if(document.querySelector('#requirement_area').children.length > 1){
      if(index) {
        $('.batch-row-' + index).remove();
      }
      $(requirementElem).closest('tr').remove();
      recalculate();
    } else {
      alert('Atleast one line item is required');
    }
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

  $(document).ready(function ($) {
    // Re-init the first row select2 to attach to body and prevent clipping
    $('#product_id_1').select2({ dropdownParent: $('body') });
    $('#charge_id_1').select2({ dropdownParent: $('body') });

    // Init first product row
    $.ajax({
      type: "POST",
      url: "<?php echo base_url()?>inventory/get_product_by_company",
      data: {},
      success: function(res) {
          $('.product_id').append(res);
      }
    });

    // Restricted access check
    <?php if($this->session->userdata('super_type_id') == 7): // Salesman role ID ?>
      $('#date_picker').prop('readonly', true);
      $('#date_picker').on('mousedown', function(e){ e.preventDefault(); });
    <?php endif; ?>

    change_gst($('#gst_type').val());
    recalculate();

    // Excel-like Keyboard Navigation
    $(document).on('keydown', '.compact-table input, .compact-table select', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var $focusable = $('.compact-table').find('input:not([readonly]), select').filter(':visible');
        var index = $focusable.index(this);
        if (index > -1 && index < $focusable.length - 1) {
          var $next = $focusable.eq(index + 1);
          // If it's a select2, open it or focus it
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

    $('#customer_id').on('change', function() {
      var customer_id = $(this).val();
      if(customer_id) {
        $.ajax({
          type: "POST",
          url: "<?php echo base_url();?>inventory/get_customer_details_ajax",
          data: { customer_id: customer_id },
          dataType: "json",
          success: function(res) {
            if(res.status === 200) {
              var data = res.data;
              var cityHtml = res.city_html;

              var shipOnchange = $('#shipping_state_id').attr('onchange');
              $('#shipping_state_id').removeAttr('onchange');
              $('#shipping_state_id').val(data.state_id).trigger('change');
              if (shipOnchange) {
                  $('#shipping_state_id').attr('onchange', shipOnchange);
              }
              
              $('#shipping_city_id').html(cityHtml);
              $('#shipping_city_id').val(data.city_id).trigger('change');
              $('#shipping_pincode').val(data.pincode);
              $('#shipping_address').val(data.address);
              $('#shipping_gst').val(data.gst_name);
              $('#shipping_gst_no').val(data.gst_no);

              // Update Billing fields
              var billOnchange = $('#billing_state_id').attr('onchange');
              $('#billing_state_id').removeAttr('onchange');
              $('#billing_state_id').val(data.state_id).trigger('change');
              if (billOnchange) {
                  $('#billing_state_id').attr('onchange', billOnchange);
              }

              $('#billing_city_id').html(cityHtml);
              $('#billing_city_id').val(data.city_id).trigger('change');
              $('#billing_pincode').val(data.pincode);
              $('#billing_address').val(data.address);
              $('#billing_gst').val(data.gst_name);
              $('#billing_gst_no').val(data.gst_no);
            }
          }
        });
      }
    });
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
      if (!product_val) return; // skip empty/invalid rows

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
        var bid = $(this).find('.batch_id').val();
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

  function toggleMainRowReadonly(index, isReadonly) {
    $('#quantity_' + index).prop('readonly', isReadonly);
    $('#master_amount_' + index).prop('readonly', isReadonly);
    $('#bill_amount_' + index).prop('readonly', isReadonly);
    $('#bill_total_' + index).prop('readonly', isReadonly);
    $('#gst_' + index).prop('readonly', isReadonly);
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
    var batch_id = row.find('.batch_id').val();

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

    // Validation
    if (white_qty > available_white) {
      Swal.fire({
        title: "Warning!",
        text: "White Quantity (" + white_qty + ") cannot exceed Available White Quantity (" + available_white + ")",
        icon: "warning"
      });
      row.find('.batch_white_qty_input').val(0);
      white_qty = 0;
    }

    if ((white_qty + black_qty) > (available_white + available_black)) {
      Swal.fire({
        title: "Warning!",
        text: "Total Batch Quantity (" + (white_qty + black_qty) + ") cannot exceed Available Total Quantity (" + (available_white + available_black) + ")",
        icon: "warning"
      });
      row.find('.batch_black_qty_input').val(0);
      black_qty = 0;
    }

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
        icon: "warning"
      });
      $(element).val(0);
      white_qty = parseFloat(row.find('.batch_white_qty_input').val()) || 0;
      black_qty = parseFloat(row.find('.batch_black_qty_input').val()) || 0;
    }

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
    $('.sales-line-item').each(function() {
      var index = $(this).data('id');
      toggleMainRowReadonly(index, false);
    });
    recalculate();
  }

  function addBatch(index) {
    var warehouse_id = $('#warehouse_id').val();
    var product_val = $('#product_id_' + index).val();

    if (warehouse_id == '0' || warehouse_id == '') {
      Swal.fire({
        title: "Error!",
        text: "Please select warehouse first",
        icon: "error"
      });
      return;
    }

    if (!product_val) {
      Swal.fire({
        title: "Error!",
        text: "Please select product first",
        icon: "error"
      });
      return;
    }

    var product_id = String(product_val || '').split('|')[0];

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
            <span class="input-group-text p-0 price-history-btn" tabindex="0" style="cursor:pointer" data-row-index="${index}" data-batch-index="${batch_index}" onclick="showPriceHistory('${index}', '${batch_index}')"><i class="fa fa-history px-1"></i></span>
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

    $('#product_' + index).find('.btn-add-batch').hide();
    toggleMainRowReadonly(index, true);

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
      toggleMainRowReadonly(index, false);
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
        icon: "error"
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
