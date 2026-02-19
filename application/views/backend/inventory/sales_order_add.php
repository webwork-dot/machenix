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

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Warehouse <span class="required">*</span></label>
            <select class=" form-select select2" name="warehouse_id" id="warehouse_id"
              onchange="get_product_by_warehouse(this.value,'1');" required>
              <option value="">Select Warehouse </option>
              <?php foreach($warehouse_list as $item){?>
              <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
              <?php }?>
            </select>
          </div>

          <!-- <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Company <span class="required">*</span></label>
            <select class=" form-select select2" name="company_id" id="company_id" required>
              <option value="">Select Company </option>
              <?php foreach($company_list as $item){?>
              <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
              <?php }?>
            </select>
          </div> -->
					<input type="hidden" name="company_id" name="<?php echo $this->session->userdata('company_id'); ?>">
          
          <div class="col-12 col-sm-12 mb-1 mt-1">
            <div class="form-group">
              <label>Narration</label>
              <textarea class="form-control" placeholder="" rows="1" name="narration" id="narration"></textarea>
            </div>
          </div>

          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Remark</label>
              <textarea class="form-control" placeholder="" rows="1" name="remark" id="remark"></textarea>
            </div>
          </div>

          <div class="col-12">
            <div id="requirement_area">
              <div class="d-block mt-2 element-1 fx-border" id="product_1" data-id="1">
                <b class="jsr-no">1</b>
                <div class="flex-grow-1 px-0 ml-15">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="row">

                        <!-- <div class="col-md-1 pl-0">
                          <div class="form-group">
                            <label>Order Id <span class="required">*</span></label>
                            <input type="text" step="any" id="porder_id_1" name="porder_id[]" class="form-control"
                              required>
                          </div>
                        </div>

                        <div class="col-md-2 pl-0">
                          <div class="form-group">
                            <label>Customer Name <span class="required">*</span></label>
                            <input type="text" step="any" id="customer_name_1" name="customer_name[]"
                              class="form-control" required>
                          </div>
                        </div>

                        <div class="col-md-2 pl-0">
                          <div class="form-group">
                            <label>Pincode <span class="required">*</span></label>
                            <input type="number" step="any" id="pincode_1" name="pincode[]" class="form-control"
                              required>
                          </div>
                        </div>

                        <div class="col-md-2 pl-0">
                          <div class="form-group">
                            <label>State <span class="required">*</span></label>
                            <input type="text" id="state_1" name="state[]" class="form-control" required>
                          </div>
                        </div> -->

                        <div class="col-md-5 pl-0">
                          <input type="hidden" name="x_value[]" id="x_value_1" value="1">
                          <div class="form-group">
                            <label>Select Product - SKU<span class="required">*</span></label>
                            <select class="form-control select2 product_id" name="product_id[]" id="product_id_1"
                              data-toggle="select2" onchange="get_batch_by_product(this.value,'1_1');" required>
                              <option value="">Select Product - SKU</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-md-2 pl-0">
                          <div class="form-group">
                            <label> GST <span class="required">*</span></label>
                            <input type="number" step="any" id="total_amount_1" name="total_amount[]"
                              onkeyup="recalculate()" value="" class="form-control">
                          </div>
                        </div>

                        <div class="col-md-2 pl-0">
                          <div class="form-group">
                            <label> Amount <span class="required">*</span></label>
                            <input type="number" step="any" id="total_amount_1" name="total_amount[]"
                              onkeyup="recalculate()" value="" class="form-control">
                          </div>
                        </div>

                        <div class="col-md-2 pl-0">
                          <div class="form-group">
                            <label>Qty <span class="required">*</span></label>
                            <input type="number" step="any" id="quantity_1_1" name="quantity[]" placeholder="Qty"
                              onkeyup="check_available_qty(this.value,'1')" value="1" class="form-control quantity_1"
                              required="">
                          </div>
                        </div>

                        <div class="col-md-1 pl-0">
                          <div class="form-group">
                            <label>&nbsp;</label><br />
                            <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light"
                              style="" name="button" onclick="removeRequirement(this,1)"> <i class="fa fa-times"
                                aria-hidden="true"></i> </button>
                          </div>
                        </div>

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
                        <label style="float:right;display: contents;">Total Basic Amount</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="basic_value" id="basic_value"
                            value="0" placeholder="Basic Value" class="form-control" readonly></p>

                      </td>

                    </tr>

                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <div class="d-flex flex-column align-items-end">
                          <span class="mb-0 text-capitalize">Basic Net Sales Value (Exclu. GST)</span>
                          <select class="form-control " name="gst_type" id="gst_type" onchange="change_gst(this.value)"
                            style="width : 200px !important;float:right !important">
                            <option value="">Select GST</option>
                            <option value="Central GST / State GST">Central GST / State GST</option>
                            <option value="IGST">IGST</option>
                          </select>
                        </div>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="net_sales_value_1"
                            id="net_sales_value_1" value="0" placeholder="Basic Net Sales Value (Excl. GST)"
                            class="form-control" readonly></p>
                      </td>
                    </tr>

                    <tr class="hidden" id="cgst_gst">
                      <th colspan="4" class="text-right align-middle">
                        <b class="mb-0 text-capitalize">Add : IN: Central GST</b>
                        <input type="text"
                          oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                          name="cgst_per" id="cgst_per" onkeyup="recalculate()" placeholder="in(%)" value="0"
                          class="form-control dis-input">
                      </th>
                      <th colspan="1">
                        <p class="td-blank"><b><input type="number" step="any" name="central_gst" id="central_gst"
                              value="0" placeholder="Add : IN: Central GST" class="form-control" readonly></b></p>
                      </th>
                    </tr>

                    <tr class="hidden" id="sgst_gst">
                      <th colspan="4" class="text-right align-middle">
                        <b class="mb-0 text-capitalize">Add : IN: State GST</b>
                        <input type="text"
                          oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                          name="sgst_per" id="sgst_per" onkeyup="recalculate()" placeholder="in(%)" value="0"
                          class="form-control dis-input" readonly>
                      </th>
                      <th colspan="1">
                        <p class="td-blank"><b><input type="number" step="any" name="state_gst" id="state_gst" value="0"
                              placeholder="Add : IN: State GST" class="form-control" readonly></b></p>
                      </th>
                    </tr>

                    <tr class="hidden" id="igst_gst">
                      <th colspan="4" class="text-right align-middle">
                        <b class="mb-0 text-capitalize">Add : IN: IGST</b>
                        <input type="text"
                          oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                          name="igst_per" id="igst_per" onkeyup="recalculate()" placeholder="in(%)" value="0"
                          class="form-control dis-input">
                      </th>
                      <th colspan="1">
                        <p class="td-blank"><b><input type="number" step="any" name="igst" id="igst" value="0"
                              placeholder="Add : IN: IGST" class="form-control" readonly></b></p>
                      </th>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Net Sales Value (Inc. GST)</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="net_sales_value_2"
                            id="net_sales_value_2" value="0" placeholder="Basic Net Sales Value (Inc. GST)"
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
                        <label>Total Order Value</label>
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
  var total_element = $(".element-1").length;
  var base_total = 0;
  var total_gst_amount = 0;
  var igst_per = 0;
  var cgst_per = 0;
  var cgst_amt = 0;
  var sgst_amt = 0;
  var igst_amt = 0;
  var net_sales_value_1 = 0;
  var discount_per = 0;
  var discount = 0;
  var other_tax_per = 0;
  var other_tax = 0;
  var tcs_per = 0;
  var tcs = 0;
  var grand_total = 0;

  if (gst_type == 'IGST') {
    igst_per = parseFloat($("#igst_per").val()) || 0;
  } else if (gst_type == 'Central GST / State GST') {
    cgst_per = parseFloat($("#cgst_per").val()) || 0;
    sgst_per = cgst_per;
    $("#sgst_per").val(isNaN(cgst_per) ? 0 : Math.round(cgst_per));
  }

  for (let i = 1; i <= total_element; i++) {
    if ($("#total_amount_" + i).val()) {
      var master_price = parseFloat($("#total_amount_" + i).val());
      var quantity = parseFloat($("#quantity_" + i + "_1").val());
      master_price = isNaN(master_price) ? 0 : master_price;
      quantity = isNaN(quantity) ? 0 : quantity;
      var total_amount = master_price * quantity;
      base_total += total_amount;

      //$("#total_val_" + i).val(isNaN(total_amount) ? 0 : total_amount.toFixed(2));
    }
  }

  $("#basic_value").val(isNaN(base_total) ? 0 : base_total.toFixed(2));

  net_sales_value_1 = base_total;
  $("#net_sales_value_1").val(isNaN(net_sales_value_1) ? 0 : net_sales_value_1.toFixed(2));

  if (gst_type == 'IGST') {
    igst_amt = get_per_total(net_sales_value_1, igst_per);
    total_gst_amount = igst_amt;
    $('#igst').val(igst_amt.toFixed(2));
  } else if (gst_type == 'Central GST / State GST') {
    cgst_amt = get_per_total(net_sales_value_1, cgst_per);
    sgst_amt = cgst_amt;
    total_gst_amount = cgst_amt + sgst_amt;
    $('#central_gst').val(cgst_amt.toFixed(2));
    $('#state_gst').val(sgst_amt.toFixed(2));
  }

  net_sales_value_2 = net_sales_value_1 + total_gst_amount;
  $("#net_sales_value_2").val(isNaN(net_sales_value_2) ? 0 : net_sales_value_2.toFixed(2));

  var other_charges_amount = parseFloat($("#other_charges_amount").val()) || 0;
  var round_of = parseFloat($("#round_of").val()) || 0;

  grand_total = net_sales_value_2 + round_of + other_charges_amount;
  //console.log('grand_total: ' + grand_total);
  $('#grand_total').val(grand_total.toFixed(2));
}

function recalculate() {
  subtotal_cal();
};

function change_gst(value) {
  let cgst_gst = document.querySelector("#cgst_gst");
  let sgst_gst = document.querySelector("#sgst_gst");
  let igst_gst = document.querySelector("#igst_gst");

  if (value == "Central GST / State GST") {
    sgst_gst.classList.remove('hidden')
    cgst_gst.classList.remove('hidden')
    igst_gst.classList.add('hidden')
  } else if (value == "IGST") {
    sgst_gst.classList.add('hidden')
    cgst_gst.classList.add('hidden')
    igst_gst.classList.remove('hidden')
  } else {
    sgst_gst.classList.add('hidden')
    cgst_gst.classList.add('hidden')
    igst_gst.classList.add('hidden')
  }
}


function appendRequirement() {
  var warehouse_id = $('#warehouse_id').find(":selected").val();
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
  } else if (warehouse_id == '') {
    Swal.fire({
      title: "Error!",
      text: "Please Select Warehouse !!",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
  } else {
    var total_element = $(".element-1").length;
    var lastid = $(".element-1:last").attr("id");
    var split_id = lastid.split("_");
    var nextindex = Number(split_id[1]) + 1;
    if ($('#product_id_' + split_id[1]).find(":selected").val() == '') {
      Swal.fire({
        title: "Error!",
        text: "Please Select Previous Product !!",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });
    } else {
      $(".loader").show();
      var extra_val = "'" + nextindex + "_1'"
      // $('#requirement_area').append('<div class="d-block mt-2 element-1 fx-border" id="product_'+nextindex+'" data-id="'+nextindex+'"><b class="jsr-no">'+nextindex+'</b><div class="flex-grow-1 px-0 ml-15"><div class="row"><div class="col-md-12"><div class="row"><div class="col-md-4"><input type="hidden" name="x_value[]" id="x_value_'+nextindex+'" value="'+nextindex+'"><div class="form-group"><label>Select Product Name - Size<span class="required">*</span></label><select class="form-control select2 product_id" name="product_id[]" id="product_id_'+nextindex+'" data-toggle="select2" onchange="get_batch_by_product(this.value,'+extra_val+');" required><option value="">Select Product Name - Size</option><?php foreach($products_list as $item){?><option value="<?php echo $item->id; ?>"><?php echo $item->item_code.' - '.$item->name; ?></option><?php } ?></select></div></div><div class="col-md-2 pl-0"><div class="form-group"><label>Order Id <span class="required">*</span></label><input type="text" step="any" id="porder_id_'+nextindex+'" name="porder_id[]" class="form-control" ></div></div><div class="col-md-2 pl-0"><div class="form-group"><label>Total Amount <span class="required">*</span></label><input type="number" step="any" id="total_amount_'+nextindex+'" name="total_amount[]" value="" class="form-control" onkeyup="recalculate()"></div></div><div class="col-md-1 pl-0"><div class="form-group"><label>Qty <span class="required">*</span></label><input type="number" step="any" id="quantity_'+nextindex+'_1" name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,'+nextindex+')" value="1" class="form-control quantity_'+nextindex+'" required=""></div></div><div class="col-md-2 pl-0"><div class="form-group"><label>Available Qty <span class="required">*</span></label><input type="number" step="any" id="available_'+nextindex+'_1" name="available[]" placeholder="Available Qty" value="" class="form-control" required="" readonly></div></div><div class="col-md-1 pl-0"><div class="form-group"><label>&nbsp;</label><br/><button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light" name="button" onclick="removeRequirement(this,'+nextindex+')"><i class="fa fa-times" aria-hidden="true"></i></button></div></div></div></div></div></div></div></div>');
      $('#requirement_area').append(`<div class="d-block mt-2 element-1 fx-border" id="product_${nextindex}" data-id="${nextindex}">
                                                  <b class="jsr-no">${nextindex}</b>
                                                  <div class="flex-grow-1 px-0 ml-15">
                                                    <div class="row">
                                                      <div class="col-md-12">
                                                        <div class="row">
                                                
                                                          <div class="col-md-1 pl-0">
                                                            <div class="form-group">
                                                              <label>Order Id <span class="required">*</span></label>
                                                              <input type="text" step="any" id="porder_id_${nextindex}" name="porder_id[]" class="form-control" required>
                                                            </div>
                                                          </div>
                                                          
                                                          <div class="col-md-2 pl-0">
                                                            <div class="form-group">
                                                              <label>Customer Name <span class="required">*</span></label>
                                                              <input type="text" step="any" id="customer_name_${nextindex}" name="customer_name[]" class="form-control" required>
                                                            </div>
                                                          </div>
                                                			
                                                          <div class="col-md-2 pl-0">
                            									<div class="form-group">
                            										<label>Pincode <span class="required">*</span></label>
                            										<input type="number" step="any" id="pincode_${nextindex}" name="pincode[]" class="form-control" required>
                            									</div>
                            								</div>
                            								    
                            								<div class="col-md-2 pl-0">
                            									<div class="form-group">
                            										<label>State <span class="required">*</span></label>
                            										<input type="text" id="state_${nextindex}" name="state[]" class="form-control" required>
                            									</div>
                            								</div>
                                                
                                                          <div class="col-md-2 pl-0">
                                                            <input type="hidden" name="x_value[]" id="x_value_${nextindex}" value="${nextindex}">
                                                            <div class="form-group">
                                                              <label>Select Product Name - Size<span class="required">*</span></label>
                                                              <select class="form-control select2 product_id" name="product_id[]" id="product_id_${nextindex}" onchange="get_batch_by_product(this.value, ${extra_val});" required>
                                                                <option value="">Select Product Name - Size</option>
                                                              </select>
                                                            </div>
                                                          </div>
                                                          
                                                          <div class="col-md-1 pl-0">
                                                            <div class="form-group">
                                                              <label> Amount <span class="required">*</span></label>
                                                              <input type="number" step="any" id="total_amount_${nextindex}" name="total_amount[]" value="" class="form-control"
                                                                onkeyup="recalculate()">
                                                            </div>
                                                          </div>
                                                          <div class="col-md-1 pl-0">
                                                            <div class="form-group">
                                                              <label>Qty <span class="required">*</span></label>
                                                              <input type="number" step="any" id="quantity_${nextindex}_1" name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,${nextindex})" value="1" class="form-control quantity_${nextindex}" required="">
                                                            </div>
                                                          </div>
                                                          
                                                          <div class="col-md-1 pl-0">
                                                            <div class="form-group">
                                                              <label>&nbsp;</label><br />
                                                              <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light" name="button"
                                                                onclick="removeRequirement(this,${nextindex})"><i class="fa fa-times" aria-hidden="true"></i></button>
                                                            </div>
                                                          </div>
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>`);

      $(".loader").fadeOut("slow");
      $(".select2").select2();
      var warehouse_id = $('#warehouse_id').val();
      get_product_by_warehouse(warehouse_id, nextindex);
    }
  }
}

function removeRequirement(requirementElem) {
  $(requirementElem).parent().parent().parent().parent().remove();
}

function appendRequirement1(id) {
  var total_element = $(".newelement-1").length;
  var lastid = $(".newelement-1:last").attr("id");
  var split_id = lastid.split("_");
  console.log('split_id : ', split_id);
  var nextindex = Number(split_id[2]) + 1;
  if ($('#batch_no_' + id + '_' + split_id[2]).val() == '') {
    Swal.fire({
      title: "Error!",
      text: "Please Select Previous Batch No. !!",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
  } else {
    $(".loader").show();
    var x_net = "'" + id + "_" + nextindex + "'";
    $('#batch_tab_' + id).append('<div class="row newelement-1" id="batch_' + id + '_' + nextindex + '" data-id="' +
      id + '_' + nextindex +
      '" ><div class="col-md-3 "><div class="form-group"><label>Batch <span class="required">*</span></label><select class="form-control select2 batch_no_' +
      id + '"  name="batch_no_' + id + '[]"  id="batch_no_' + id + '_' + nextindex +
      '" data-toggle="select2" onchange="get_product_details(this.value,' + x_net +
      ');"  required><option value="">Select Batch</option><?php foreach($products_list as $item){?><option value="<?php echo $item->name; ?>"><?php echo $item->name; ?>/option><?php } ?></select></div></div><div class="col-md-1 pl-0"><div class="form-group"><label>Qty <span class="required">*</span></label><input type="number" step="any" id="quantity_' +
      id + '_' + nextindex + '" name="quantity_' + id +
      '[]" placeholder="Qty" onkeyup="check_available_qty(this.value,' + id +
      ')" value="1" class="form-control quantity_' + id +
      '" required=""></div></div><div class="col-md-2 pl-0"><div class="form-group"><label>Available Qty <span class="required">*</span></label><input type="number" step="any" id="available_' +
      id + '_' + nextindex + '" name="available_' + id +
      '[]" placeholder="Available Qty"  value="" class="form-control" required="" readonly></div></div><div class="col-md-1  pl-0"><label>&nbsp;</label><br/><button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light" style="" name="button" onclick="removeRequirement1(this,' +
      id + '_' + nextindex + ')"> <i class="fa fa-minus" aria-hidden="true"></i> </button></div></div>');
    $(".loader").fadeOut("slow");
    $(".select2").select2();
    var product_id = $('#product_id_' + id).val();
    var new_net = id + '_' + nextindex;
    console.log('product_id : ', product_id);
    console.log('new_net : ', nextindex);
    get_batch_by_product(product_id, new_net);
  }
}


function removeRequirement1(requirementElem) {
  $(requirementElem).parent().parent().parent().remove();
  //get_total_amount(this.value,'1')
}

function check_available_qty(value, id) {
  //         var is_disabled = 0 ;
  //         var total_element = $(".quantity_"+id).length + 1; 
  // 		console.log('total_element:',total_element);
  //         for (let i = 1; i < total_element ; i++) {
  //             if($("#quantity_"+id+"_"+i).val()){
  //                 var quantity = parseInt($("#quantity_"+id+"_"+i).val());
  // 				var available = parseInt($("#available_"+id+"_"+i).val());
  // 				if(quantity > available){
  // 					is_disabled = 1 ;
  // 				}
  //             }
  //         }
  //         if(is_disabled == 1){
  //             alert('Quantity cannot greater than Available Quantity')
  //             $(':input[type="submit"]').prop('disabled', true);
  //         }else{
  //             $(':input[type="submit"]').prop('disabled', false);
  //         }
  subtotal_cal();
}

function get_product_by_warehouse(b, nextindex) {
  var warehouse_id = $('#warehouse_id').find(":selected").val();
  var a = {
    warehouse_id: b
  };
  $.ajax({
    type: "POST",
    url: "<?php echo base_url()?>inventory/get_product_by_warehouse",
    data: a,
    success: function(res) {
      $('#product_id_' + nextindex).children("option:not(:first)").remove();
      $('#product_id_' + nextindex).append(res);
    }
  });
}

function get_batch_by_product(b, nextindex) {
  var new_value = nextindex.split('_');
  var warehouse_id = $('#warehouse_id').find(":selected").val();
  var product_id = $('#product_id_' + new_value[0]).find(":selected").val();
  var is_disabled = 0;
  var total_element = $(".element-1").length + 1;

  // Check Same Selected Product
  //         for (let i = 1; i < total_element ; i++) {
  // 			var old_product_id = $("#product_id_"+i).val();
  // 			if(old_product_id == product_id && i != new_value[0]){
  // 				$('#product_id_'+new_value[0]).prop("selected", false);
  // 				$(".select2").select2();
  // 				is_disabled = 1 ;
  // 			}
  // 		}

  if (is_disabled == 0) {
    $('#live_qty_' + new_value[0]).html('Total Available - 0 QTY');
    $("#batch_no_" + nextindex + " option").prop("selected", false);
    $(".select2").select2();
    $('#available_' + nextindex).val(0);
    var a = {
      warehouse_id: warehouse_id,
      product_id: product_id,
    };

    $.ajax({
      type: "POST",
      url: "<?php echo base_url()?>inventory/get_qty_by_product",
      data: a,
      success: function(res) {
        $('#available_' + nextindex).val(res.quantity);
        //$('#live_qty_'+new_value[0]).html('Total Available - ' + res.quantity + ' QTY');
      }
    });
    $(':input[type="submit"]').prop('disabled', false);
  } else {
    Swal.fire({
      title: "Error!",
      text: "Product Can't Be Same!!!",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
    $(':input[type="submit"]').prop('disabled', true);
    //$('#product_id_'+new_value[0]).prop("selected", false);
    $("#product_id_" + new_value[0] + " option").prop("selected", false);
    $(".select2").select2();
  }
}

function get_product_details(b, nextindex) {
  var new_value = nextindex.split('_');
  var warehouse_id = $('#warehouse_id').find(":selected").val();
  var product_id = $('#product_id_' + new_value[0]).find(":selected").val();
  var batch_no = $('#batch_no_' + nextindex).find(":selected").val();
  is_disabled = 0;
  var total_element = $(".batch_no_" + new_value[0]).length + 1;
  for (let i = 1; i < total_element; i++) {
    if ($("#product_id_" + new_value[0]).val() && new_value[1] != i) {
      var old_product_id = $("#product_id_" + new_value[0]).val();
      var old_batch_no = $("#batch_no_" + new_value[0] + "_" + i).val();
      if (old_product_id == product_id && batch_no == old_batch_no) {
        $("#batch_no_" + new_value[0] + "_" + i + " option").prop("selected", false);
        $(".select2").select2();
        $('#available_' + nextindex).val(0);
        is_disabled = 1;
      }
    }
  }

  if (is_disabled == 0) {
    $(':input[type="submit"]').prop('disabled', false);
    var a = {
      warehouse_id: warehouse_id,
      product_id: product_id,
      batch_no: batch_no,
    };
    $.ajax({
      type: "POST",
      url: "<?php echo base_url()?>inventory/get_available_qty",
      data: a,
      success: function(res) {
        $('#available_' + nextindex).val(res.quantity);
      }
    });
  } else {
    Swal.fire({
      title: "Error!",
      text: "Product Batch No. Can't Be Same!!!",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
    $(':input[type="submit"]').prop('disabled', true);
    //$('#product_id_'+nextindex).prop("selected", false);
    //$(".select2").select2();

  }

}
</script>