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

	.sales-line-item .btn-remove-line {
		width: 34px;
		height: 34px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}
</style>

<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/sales_order/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
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
              	<option value="<?php echo $item['id'];?>"><?php echo $item['owner_name'];?></option>
              <?php }?>
            </select>
          </div>

          <div class="col-12 col-sm-3 mb-1 d-none">
            <label class="form-label" for="warehouse_id">Warehouse <span class="required">*</span></label>
            <select class=" form-select select2" name="warehouse_id" id="warehouse_id">
              <option value="0">Select Warehouse</option>
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
            <div id="requirement_area">

              <div class="d-block mt-2 element-1 fx-border sales-line-item" id="product_1" data-id="1">
                <b class="jsr-no">1</b>
                <div class="flex-grow-1 ">
                  <div class="row g-1 align-items-end">

                      <div class="col-xl-3 col-lg-4 col-md-6 px-1">
                        <input type="hidden" name="x_value[]" id="x_value_1" value="1">
                        <div class="form-group">
                          <label>Select Product<span class="required">*</span></label>
                          <select class="form-control select2 product_id" name="product_id[]" id="product_id_1"
                            data-toggle="select2" onchange="get_details_by_product(this.value,'1');" required>
                            <option value="">Select Product</option>
                          </select>
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Qty <span class="required">*</span></label>
                          <input type="number" step="any" id="quantity_1" name="quantity[]" placeholder="Qty"
                            onkeyup="calculate_amt('1')" value="1" class="form-control" required="">
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Per Qty Amount <span class="required">*</span></label>
                          <div class="input-group">
                            <input type="number" step="any" id="master_amount_1" name="master_amount[]"
                              onkeyup="calculate_amt('1')" value="" class="form-control">
                            <span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('1')"><i class="fa fa-history px-1"></i></span>
                          </div>
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Total Amount</label>
                          <input type="number" step="any" id="total_amount_1" name="total_amount[]" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Per Qty Bill Amt <span class="required">*</span></label>
                          <input type="number" step="any" id="bill_amount_1" name="bill_amount[]"
                            onkeyup="markManual('1'); calculate_amt('1')" value="" class="form-control" data-manual="false">
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Total Bill Amt</label>
                          <input type="number" step="any" id="bill_total_1" name="bill_total[]" class="form-control" onkeyup="calculate_amt_reverse('1')">
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>GST % <span class="required">*</span></label>
                          <input type="number" step="any" id="gst_1" name="gst[]" onkeyup="calculate_amt('1')" value="" class="form-control">
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>GST Amt</label>
                          <input type="number" step="any" id="gst_amount_1" name="gst_amount[]" value="" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Total Bill GST Amount</label>
                          <input type="number" step="any" id="total_bill_gst_amount_1" name="total_bill_gst_amount[]" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Per Qty Black Amt</label>
                          <input type="number" step="any" id="black_amount_per_unit_1" name="black_amt[]" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Total Black Amount</label>
                          <input type="number" step="any" id="black_amount_1" name="black_total[]" value="" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>Final Total</label>
                          <input type="number" step="any" id="final_total_1" name="final_total[]" class="form-control" readonly>
                          <input type="hidden" id="available_1" name="available[]" value="0">
                        </div>
                      </div>

                      <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                        <div class="form-group">
                          <label>&nbsp;</label><br />
                          <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-line"
                            onclick="removeRequirement(this,1)"> <i class="fa fa-times" aria-hidden="true"></i> </button>
                        </div>
                      </div>

                  </div>
                </div>
              </div>

            </div>
          </div>

          <center>
            <div class="col-md-12  pl-0 m-auto">
              <button type="button" class="btn btn-outline-primary waves-effect" onclick="appendRequirement()"> <i
                  class="fa fa-plus" aria-hidden="true"></i> Add New Product</button>
            </div>
          </center>

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
                        <label>Add : Other Charges</label>
                        <input type="text" step="any" name="other_charges_name" id="other_charges_name" value=""
                          placeholder="Charge Name" class="form-control dis-input-1">
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="other_charges_amount"
                            id="other_charges_amount" placeholder="Charge Amount" class="form-control" value="0"
                            onkeyup="recalculate()"></p>
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
                        <p class="td-blank"><input type="number" step="any" name="grand_total" id="grand_total"
                            placeholder="" class="form-control" readonly></p>
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

  var other_charges_amount = parseFloat($("#other_charges_amount").val()) || 0;
  var round_of = parseFloat($("#round_of").val()) || 0;

  grand_total = final_total_sum + other_charges_amount + round_of;
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
        <div class="d-block mt-2 element-1 fx-border sales-line-item" id="product_${nextindex}" data-id="${nextindex}">
          <b class="jsr-no">${nextindex}</b>

          <div class="flex-grow-1 px-0 ml-15">
            <div class="row g-1 align-items-end">

              <div class="col-xl-3 col-lg-4 col-md-6 px-1">
                <input type="hidden" name="x_value[]" id="x_value_${nextindex}" value="${nextindex}">
                <div class="form-group">
                  <label>Select Product<span class="required">*</span></label>
                  <select class="form-control select2 product_id" name="product_id[]" id="product_id_${nextindex}" onchange="get_details_by_product(this.value,'${nextindex}');" required>
                    <option value="">Select Product</option>
                  </select>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Qty <span class="required">*</span></label>
                  <input type="number" step="any" id="quantity_${nextindex}" name="quantity[]" placeholder="Qty" value="1"
                    class="form-control" onkeyup="calculate_amt('${nextindex}')" required>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Per Qty Amount <span class="required">*</span></label>
                  <div class="input-group">
                    <input type="number" step="any" id="master_amount_${nextindex}" name="master_amount[]" class="form-control" onkeyup="calculate_amt('${nextindex}')">
                    <span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('${nextindex}')"><i class="fa fa-history px-1"></i></span>
                  </div>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Total Amount</label>
                  <input type="number" step="any" id="total_amount_${nextindex}" name="total_amount[]" class="form-control" readonly>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Per Qty Bill Amt <span class="required">*</span></label>
                  <input type="number" step="any" id="bill_amount_${nextindex}" name="bill_amount[]" class="form-control" 
                    onkeyup="markManual('${nextindex}'); calculate_amt('${nextindex}')" data-manual="false">
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Total Bill Amt</label>
                  <input type="number" step="any" id="bill_total_${nextindex}" name="bill_total[]" class="form-control" 
                    onkeyup="calculate_amt_reverse('${nextindex}')">
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>GST % <span class="required">*</span></label>
                  <input type="number" step="any" id="gst_${nextindex}" name="gst[]" class="form-control"
                    onkeyup="calculate_amt('${nextindex}')">
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>GST Amt</label>
                  <input type="number" step="any" id="gst_amount_${nextindex}" name="gst_amount[]" class="form-control" readonly>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Total Bill GST Amount</label>
                  <input type="number" step="any" id="total_bill_gst_amount_${nextindex}" name="total_bill_gst_amount[]" class="form-control" readonly>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Per Qty Black Amt</label>
                  <input type="number" step="any" id="black_amount_per_unit_${nextindex}" name="black_amt[]" class="form-control" readonly>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Total Black Amount</label>
                  <input type="number" step="any" id="black_amount_${nextindex}" name="black_total[]" class="form-control" readonly>
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>Final Total</label>
                  <input type="number" step="any" id="final_total_${nextindex}" name="final_total[]" class="form-control" readonly>
                  <input type="hidden" id="available_${nextindex}" name="available[]" value="0">
                </div>
              </div>

              <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6 px-1">
                <div class="form-group">
                  <label>&nbsp;</label><br>
                  <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-line"
                    onclick="removeRequirement(this,${nextindex})">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
      `);
      
      $.ajax({
        type: "POST",
        url: "<?php echo base_url()?>inventory/get_product_by_company",
        data: {},
        success: function(res) {
          console.log("Products Loaded:", res);
          var select = $('#product_id_' + nextindex);
          select.html('<option value="">Select Product</option>' + res).trigger('change');
          select.select2();
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

    $.ajax({
      type: "POST",
      url: "<?php echo base_url()?>inventory/get_qty_by_product_company",
      data: { product_id: product_id },
      success: function(res) {
          if(res.status == 200) {
              $('#available_' + index).val(res.quantity);
              $('#gst_' + index).val(res.tax);
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

  function showPriceHistory(index) {
    var customer_id = $('#customer_id').val();
    var product_id = $('#product_id_' + index).val();

    if (!customer_id) {
      alert('Please select a customer first');
      return;
    }

    if (!product_id) {
      alert('Please select a product first');
      return;
    }

    $.ajax({
      type: "POST",
      url: "<?php echo base_url()?>inventory/get_last_selling_price",
      data: { customer_id: customer_id, product_id: product_id },
      success: function(res) {
          $('#priceHistoryModalContent').html(res);
          $('#priceHistoryModal').modal('show');
      }
    });
  }

  function removeRequirement(requirementElem) {
    if(document.querySelector('#requirement_area').children.length > 1){
      $(requirementElem).parent().parent().parent().parent().parent().remove();
      recalculate();
    } else {
      alert('Atleast one line item is required');
    }
  }

  $(document).ready(function ($) {
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
