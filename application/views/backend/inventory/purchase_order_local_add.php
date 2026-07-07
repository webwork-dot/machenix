<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/purchase_order/add_local_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">
          <input type="hidden" name="company_id" id="company_id"
            value="<?php echo $this->session->userdata('company_id'); ?>">
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Batch No <span class="required">*</span></label>
              <input type="text" class="form-control" placeholder="Batch No" name="voucher_no" value="" required>
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1 hidden">
            <div class="form-group">
              <label>Reference No </label>
              <input type="text" class="form-control" placeholder="Enter Refrence No" name="refrence_no">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>"
                value="<?php echo date('Y-m-d');?>" id="date_picker">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label> Loading Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="delivery_date" value="<?php echo date('Y-m-d');?>"
                id="date_picker">
            </div>
          </div>
          <input type="hidden" name="warehouse_state" id="warehouse_state" value="">
          <input type="hidden" name="gst_type_hidden" id="gst_type_hidden" value="">

          <div class="col-12 col-sm-4 mb-1">
            <label class="form-label" for="state">Warehouse <span class="required">*</span></label>
            <select class=" form-select select2" name="warehouse_id" id="warehouse_id"
              onchange="get_warehouse_details(this.value);" required>
              <option value="">Select Warehouse </option>
              <?php foreach($warehouse_list as $item){?>
              <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
              <?php }?>
            </select>
          </div>
          <div class="col-12 col-sm-8 mb-1">
            <div class="form-group">
              <label>Delivery Address<span class="required">*</span></label>
              <textarea class="form-control" placeholder="" rows="1" name="delivery_address"
                id="delivery_address"></textarea>
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <label class="form-label" for="supplier_id">Supplier <span class="required">*</span></label>
            <select class="form-select select2" name="supplier_id" id="supplier_id" required>
              <option value="">Select Supplier</option>
              <?php foreach($supplier_list as $item){?>
              <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
              <?php }?>
            </select>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Mode / Terms of Payment </label>
              <input type="text" class="form-control" placeholder="Enter Mode / Terms of Payment"
                name="mode_of_payment">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Dispatch Through </label>
              <input type="text" class="form-control" placeholder="Enter Dispatch Through" name="dispatch">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Destination </label>
              <input type="text" class="form-control" placeholder="Enter Destination" name="destination">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Other Refrence </label>
              <input type="text" class="form-control" placeholder="Enter Other Refrence" name="other_refrence">
            </div>
          </div>
          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Terms of Delivery </label>
              <input type="text" class="form-control" placeholder="Enter Terms of Delivery" name="terms_of_delivery">
            </div>
          </div>
          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Narration</label>
              <textarea class="form-control" placeholder="" rows="1" name="narration" id="narration"></textarea>
            </div>
          </div>
          <input type="hidden" name="input_method" value="<?php echo $type; ?>">

          <!-- Product Line Items Appendable Section -->
          <div class="col-12 mt-2">
            <h6 class="mb-1">Products</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-sm compact-table">
                <thead class="table-light text-center">
                  <tr>
                    <th style="min-width:200px;">Product <span class="text-danger">*</span></th>
                    <th style="min-width:100px;">Rate <span class="text-danger">*</span></th>
                    <th style="min-width:80px;">White Qty <span class="text-danger">*</span></th>
                    <th style="min-width:80px;">Black Qty <span class="text-danger">*</span></th>
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
                <tbody id="product_area">
                  <!-- Dynamic rows will be appended here -->
                </tbody>
              </table>
            </div>
            <div class="mt-50 mb-1">
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="appendProduct()">
                <i class="fa fa-plus"></i> Add Product
              </button>
            </div>
          </div>

          <!-- Summary Calculations Table -->
          <div class="col-12 col-sm-12 mb-1 mt-2">
            <div class="table-responsive">
              <div class="col-lg-12 no-pad">
                <table class="table table-striped table-bordered mn-table mt-1">
                  <tbody>
                    <tr>
                      <td colspan="4" class="text-right align-middle" style="width:80%">
                        <label style="float:right;display: contents;">Total Bill Amt (Exc GST)</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank mb-0"><input type="number" step="any" name="basic_value" id="basic_value"
                            value="0.00" placeholder="Total Bill Amt (Exc GST)" class="form-control" readonly></p>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <div class="d-flex flex-column align-items-end">
                          <span class="mb-0 text-capitalize">Select GST</span>
                          <select class="form-control" name="gst_type" id="gst_type" onchange="change_gst(this.value); recalculate();" style="width: 200px !important; float:right !important">
                            <option value="Central GST / State GST" selected>Central GST / State GST</option>
                            <option value="IGST">IGST</option>
                          </select>
                        </div>
                      </td>
                      <td colspan="1">
                        <div id="cgst_sgst_inputs">
                          <p class="td-blank mb-25">
                            <input type="number" step="any" name="central_gst" id="central_gst" value="0.00" placeholder="CGST Amount" class="form-control" readonly>
                          </p>
                          <p class="td-blank mb-0">
                            <input type="number" step="any" name="state_gst" id="state_gst" value="0.00" placeholder="SGST Amount" class="form-control" readonly>
                          </p>
                        </div>
                        <div id="igst_input" class="hidden">
                          <p class="td-blank mb-0">
                            <input type="number" step="any" name="igst" id="igst" value="0.00" placeholder="IGST Amount" class="form-control" readonly>
                          </p>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <label style="float:right;display: contents;">Total Bill Amt (Incl GST)</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank mb-0"><input type="number" step="any" name="net_sales_value_1"
                            id="net_sales_value_1" value="0.00" placeholder="Total Bill Amt (Incl GST)"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <label style="float:right;display: contents;">Total Black Amt</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank mb-0"><input type="number" step="any" name="total_black_amount_summary"
                            id="total_black_amount_summary" value="0.00" placeholder="Total Black Amt"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <label style="float:right;display: contents;">Final Total</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank mb-0"><input type="number" step="any" name="net_sales_value_2"
                            id="net_sales_value_2" value="0.00" placeholder="Final Total"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <label style="float:right;display: contents;">Other Charges</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank mb-0"><input type="number" step="any" name="other_charges_amount"
                            id="other_charges_amount" placeholder="Charge Amount" class="form-control" value="0.00" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <label style="float:right;display: contents;">Round Of</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank mb-0"><input type="number" step="any" name="round_of" id="round_of"
                            placeholder="Round Of" class="form-control" value="0.00" onkeyup="recalculate()"></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <label style="float:right;display: contents;">Grand Total</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank mb-0"><input type="number" step="any" name="grand_total" id="grand_total" placeholder="" class="form-control" readonly></p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-12 mt-2">
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
var nextindex = 0;

$(document).ready(function() {
  $('.select2').select2();
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
  
  // Append first product row initially
  appendProduct();

  // Prevent form submission on Enter key press (except in textareas)
  $('form.add-ajax-redirect-form').on('keydown', 'input:not(textarea), select', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      return false;
    }
  });
  
  // Also prevent Enter key on the form level
  $('form.add-ajax-redirect-form').on('keypress', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      if ($(e.target).is('textarea')) {
        return true;
      }
      e.preventDefault();
      return false;
    }
  });
});

function appendProduct() {
  nextindex++;
  var productOptionsHtml = `
    <option value="">Select Product</option>
    <?php foreach($products_list as $product) { ?>
      <option value="<?php echo $product->id; ?>" data-rate="<?php echo $product->costing_price; ?>" data-gst="<?php echo $product->gst; ?>">
        <?php echo addslashes($product->name) . ' (' . addslashes($product->item_code) . ')'; ?>
      </option>
    <?php } ?>
  `;
  
  var newRowHtml = `
    <tr class="product-line-item" id="row_${nextindex}" data-id="${nextindex}">
      <td>
        <select class="form-control select2 select-product" name="product_id[]" id="product_id_${nextindex}" onchange="onProductChange(this, ${nextindex})" required style="width:100%">
          ${productOptionsHtml}
        </select>
      </td>
      <td>
        <input type="number" step="any" id="rate_${nextindex}" name="rate[]" class="form-control input-rate" onkeyup="calculateRow(${nextindex})" value="0" required>
      </td>
      <td>
        <input type="number" step="any" id="white_qty_${nextindex}" name="white_qty[]" class="form-control input-white-qty" onkeyup="calculateRow(${nextindex})" value="0" required>
      </td>
      <td>
        <input type="number" step="any" id="black_qty_${nextindex}" name="black_qty[]" class="form-control input-black-qty" onkeyup="calculateRow(${nextindex})" value="0" required>
      </td>
      <td>
        <input type="number" step="any" id="per_qty_bill_amt_${nextindex}" name="per_qty_bill_amt[]" class="form-control input-per-qty-bill-amt" onkeyup="calculateRow(${nextindex})" value="0" required>
      </td>
      <td>
        <input type="number" step="any" id="total_bill_amt_${nextindex}" name="total_bill_amt[]" class="form-control input-total-bill-amt" value="0" readonly tabindex="-1">
      </td>
      <td>
        <input type="number" step="any" id="gst_rate_${nextindex}" name="gst_rate[]" class="form-control input-gst-rate" onkeyup="calculateRow(${nextindex})" value="0" required>
      </td>
      <td>
        <input type="number" step="any" id="gst_amt_${nextindex}" name="gst_amt[]" class="form-control input-gst-amt" value="0.00" readonly tabindex="-1">
      </td>
      <td>
        <input type="number" step="any" id="total_bill_gst_amt_${nextindex}" name="total_bill_gst_amt[]" class="form-control input-total-bill-gst-amt" value="0.00" readonly tabindex="-1">
      </td>
      <td>
        <input type="number" step="any" id="per_qty_black_amt_${nextindex}" name="per_qty_black_amt[]" class="form-control input-per-qty-black-amt" value="0.00" readonly tabindex="-1">
      </td>
      <td>
        <input type="number" step="any" id="total_black_amt_${nextindex}" name="total_black_amt[]" class="form-control input-total-black-amt" value="0.00" readonly tabindex="-1">
      </td>
      <td>
        <input type="number" step="any" id="final_amt_${nextindex}" name="final_amt[]" class="form-control input-final-amt" value="0.00" readonly tabindex="-1">
      </td>
      <td class="text-center align-middle" style="white-space:nowrap;">
        <button type="button" class="btn btn-danger btn-sm btn-remove-line" onclick="removeProductRow(this)" style="min-width: 32px;"><i class="fa fa-times"></i></button>
      </td>
    </tr>
  `;
  
  $('#product_area').append(newRowHtml);
  $('#product_id_' + nextindex).select2();
}

function removeProductRow(button) {
  var row = $(button).closest('tr');
  row.remove();
  recalculate();
}

function onProductChange(selectElement, index) {
  var selectedOption = $(selectElement).find('option:selected');
  var rate = parseFloat(selectedOption.data('rate')) || 0;
  var gst = parseFloat(selectedOption.data('gst')) || 0;
  
  $('#rate_' + index).val(rate.toFixed(2));
  $('#gst_rate_' + index).val(gst.toFixed(2));
  
  var billAmtField = $('#per_qty_bill_amt_' + index);
  if (parseFloat(billAmtField.val()) || 0 === 0) {
    billAmtField.val(rate.toFixed(2));
  }
  
  calculateRow(index);
}

function calculateRow(index) {
  var rate = parseFloat($('#rate_' + index).val()) || 0;
  var whiteQty = parseFloat($('#white_qty_' + index).val()) || 0;
  var blackQty = parseFloat($('#black_qty_' + index).val()) || 0;
  var perQtyBillAmt = parseFloat($('#per_qty_bill_amt_' + index).val()) || 0;
  var gstRate = parseFloat($('#gst_rate_' + index).val()) || 0;
  
  var totalQty = whiteQty + blackQty;
  
  // 6). Total Bill Amt (Per Qty Bill Amt * (White Qty + Black Qty))
  var totalBillAmt = perQtyBillAmt * totalQty;
  $('#total_bill_amt_' + index).val(totalBillAmt.toFixed(2));
  
  // 8). GST Amt (Total Bill Amt * GST % / 100)
  var gstAmt = (totalBillAmt * gstRate) / 100;
  $('#gst_amt_' + index).val(gstAmt.toFixed(2));
  
  // 9). Total Bill GST Amt (Total Bill Amt + GST Amt)
  var totalBillGstAmt = totalBillAmt + gstAmt;
  $('#total_bill_gst_amt_' + index).val(totalBillGstAmt.toFixed(2));
  
  // 10). Per Qty Black Amt (Rate - Per Qty Bill Amt)
  var perQtyBlackAmt = rate - perQtyBillAmt;
  $('#per_qty_black_amt_' + index).val(perQtyBlackAmt.toFixed(2));
  
  // 11). Total Black Amt (Per Qty Black Amt * (White Qty + Black Qty))
  var totalBlackAmt = perQtyBlackAmt * totalQty;
  $('#total_black_amt_' + index).val(totalBlackAmt.toFixed(2));
  
  // 12). Final Amt (Total Bill GST Amt + Total Black Amt)
  var finalAmt = totalBillGstAmt + totalBlackAmt;
  $('#final_amt_' + index).val(finalAmt.toFixed(2));
  
  recalculate();
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

function recalculate() {
  var total_bill_amt_ex_gst = 0;
  var total_gst_amount = 0;
  var total_bill_amt_in_gst = 0;
  var total_black_amount = 0;
  var final_total_sum = 0;
  var grand_total = 0;
  
  var gst_type = $('#gst_type').val();

  $('.product-line-item').each(function() {
    var index = $(this).attr('data-id');
    
    total_bill_amt_ex_gst += parseFloat($('#total_bill_amt_' + index).val()) || 0;
    total_gst_amount += parseFloat($('#gst_amt_' + index).val()) || 0;
    total_bill_amt_in_gst += parseFloat($('#total_bill_gst_amt_' + index).val()) || 0;
    total_black_amount += parseFloat($('#total_black_amt_' + index).val()) || 0;
    final_total_sum += parseFloat($('#final_amt_' + index).val()) || 0;
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

  var total_charge_amt = parseFloat($("#other_charges_amount").val()) || 0;
  var round_of = parseFloat($("#round_of").val()) || 0;

  grand_total = final_total_sum + total_charge_amt + round_of;
  $('#grand_total').val(grand_total.toFixed(2));
}

// Get warehouse details and populate delivery address
function get_warehouse_details(warehouseId) {
  $(".loader").show();
  var a = {
    supplier_id: warehouseId,
  };
  $.ajax({
    type: "POST",
    url: "<?php echo base_url()?>inventory/get_warehouse_details",
    data: a,
    success: function(res) {
      if (res.status == 200) {
        $('#delivery_address').val(res.address);
        $('#warehouse_state').val(res.state_id);
        $(".loader").fadeOut("slow");
      } else {
        $('#delivery_address').val('');
        $(".loader").fadeOut("slow");
      }
    }
  })
}

$(document).ready(function () {
  $(document).on('focus', '#warehouse_id + .select2 .select2-selection', function () {
      $('#warehouse_id').select2('open');
  });
  $(document).on('focus', '#supplier_id + .select2 .select2-selection', function () {
      $('#supplier_id').select2('open');
  });
});
</script>
