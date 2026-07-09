<?php
$customer_id = $param2;
$sales_order_id = $param3;
$company_id = $this->session->userdata("company_id");

$clicked_sales_order = $this->inventory_model->get_sales_order_by_id($sales_order_id)->row_array();
$warehouse_id = $clicked_sales_order['warehouse_id'] ?? '0';

// Parse selected batch IDs from query string
$selected_batch_ids_str = $_GET['batch_ids'] ?? '';
$selected_batch_ids = !empty($selected_batch_ids_str) ? explode(',', $selected_batch_ids_str) : [];

$batches = [];
if (!empty($selected_batch_ids)) {
    $batches = $this->db->query("
        SELECT 
            sopb.*, 
            sop.product_name,
            sop.product_id,
            sop.item_code,
            so.order_no,
            so.date as order_date
        FROM sales_order_product_batch AS sopb
        JOIN sales_order_product AS sop ON sop.id = sopb.order_product_id
        JOIN sales_order AS so ON so.id = sopb.order_id
        WHERE sopb.id IN (" . implode(",", array_map('intval', $selected_batch_ids)) . ")
    ")->result_array();
}

// Fetch all customers for the dropdown
$customer_list = $this->common_model->getSessionCustomers();
$states = $this->db->get_where('states', array('country_id' => 101))->result_array();
?>

<style>
	.text-right {
		text-align: right;
	}
	.mn-table td {
		padding: 4px 10px !important;
	}
	.mn-table td .td-blank {
		margin: 5px !important;
	}
	.compact-table th, .compact-table td {
		padding: 6px 8px !important;
		vertical-align: middle;
	}
	.compact-table thead th {
		background-color: #f3f6f9 !important;
		color: #3f4254 !important;
		font-weight: 700;
		font-size: 11px;
		text-transform: uppercase;
		border-bottom: 2px solid #ebedf3 !important;
	}
	.compact-table .form-control {
		height: 30px;
		padding: 2px 6px;
		font-size: 13px;
	}
	.hidden {
		display: none !important;
	}
</style>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/sales_order/generate_bill_post', ['id' => 'sales_order_generate_bill_form', 'onsubmit' => 'return submitGenerateBillForm(event);']); ?>
    
    <input type="hidden" name="warehouse_id" value="<?= $warehouse_id; ?>">

    <!-- Order Header Info -->
    <div class="row mb-2">
      <div class="col-md-2 mb-1">
        <label class="form-label">Order No <span class="text-danger">*</span></label>
        <input type="text" name="order_no" class="form-control form-control-sm" value="<?= htmlspecialchars($this->inventory_model->get_sales_order_no()); ?>" readonly required>
      </div>
      <div class="col-md-2 mb-1">
        <label class="form-label">Reference No</label>
        <input type="text" name="refrence_no" class="form-control form-control-sm" placeholder="Reference No">
      </div>
      <div class="col-md-2 mb-1">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d'); ?>" required>
      </div>
      <div class="col-md-2 mb-1">
        <label class="form-label">Invoice No <span class="text-danger">*</span></label>
        <input type="text" name="invoice_no" id="invoice_no" class="form-control form-control-sm" value="<?= htmlspecialchars($this->inventory_model->get_invoice_no()); ?>" required>
      </div>
      <div class="col-md-2 mb-1">
        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
        <input type="date" name="invoice_date" id="invoice_date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
      </div>
      <div class="col-md-2 mb-1">
        <label class="form-label">Customer <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm" name="customer_id" id="modal_customer_id" required>
          <option value="">Select Customer</option>
          <?php foreach($customer_list as $item){?>
            <option value="<?php echo $item['id'];?>" <?php if($item['id'] == $customer_id) echo 'selected'; ?>><?php echo $item['company_name'];?></option>
          <?php }?>
        </select>
      </div>
      <div class="col-md-6 mb-1">
        <label class="form-label">Remark</label>
        <textarea name="remark" class="form-control form-control-sm" rows="1" placeholder="Remark"></textarea>
      </div>
      <div class="col-md-6 mb-1">
        <label class="form-label">Narration</label>
        <textarea name="narration" class="form-control form-control-sm" rows="1" placeholder="Narration"></textarea>
      </div>
    </div>

    <!-- Addresses Section -->
    <div class="row mb-2">
      <!-- Shipping Address -->
      <div class="col-md-6">
        <div class="card border p-2 mb-1">
          <h6 class="fw-bold mb-2">Shipping Address</h6>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small mb-0">State</label>
              <select class="form-select form-select-sm" name="shipping_state_id" id="modal_shipping_state_id" onchange="get_modal_shipping_city(this.value);">
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id'];?>" <?php if(($clicked_sales_order['shipping_state_id'] ?? '') == $state['id']) echo 'selected'; ?>><?php echo $state['name'];?></option>
                <?php }?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small mb-0">City</label>
              <select class="form-select form-select-sm" name="shipping_city_id" id="modal_shipping_city_id">
                <option value="">Select City</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small mb-0">Pincode</label>
              <input type="text" class="form-control form-control-sm" name="shipping_pincode" id="modal_shipping_pincode" value="<?= htmlspecialchars($clicked_sales_order['shipping_pincode'] ?? ''); ?>">
            </div>
            <div class="col-6">
              <label class="form-label small mb-0">GST Name</label>
              <input type="text" class="form-control form-control-sm" name="shipping_gst" id="modal_shipping_gst" value="<?= htmlspecialchars($clicked_sales_order['shipping_gst'] ?? ''); ?>">
            </div>
            <div class="col-12">
              <label class="form-label small mb-0">GST No</label>
              <input type="text" class="form-control form-control-sm" name="shipping_gst_no" id="modal_shipping_gst_no" value="<?= htmlspecialchars($clicked_sales_order['shipping_gst_no'] ?? ''); ?>">
            </div>
            <div class="col-12">
              <label class="form-label small mb-0">Address</label>
              <textarea class="form-control form-control-sm" name="shipping_address" id="modal_shipping_address" rows="2"><?= htmlspecialchars($clicked_sales_order['shipping_address'] ?? ''); ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Billing Address -->
      <div class="col-md-6">
        <div class="card border p-2 mb-1">
          <h6 class="fw-bold mb-2">Billing Address</h6>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small mb-0">State</label>
              <select class="form-select form-select-sm" name="billing_state_id" id="modal_billing_state_id" onchange="get_modal_billing_city(this.value);">
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id'];?>" <?php if(($clicked_sales_order['billing_state_id'] ?? '') == $state['id']) echo 'selected'; ?>><?php echo $state['name'];?></option>
                <?php }?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small mb-0">City</label>
              <select class="form-select form-select-sm" name="billing_city_id" id="modal_billing_city_id">
                <option value="">Select City</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small mb-0">Pincode</label>
              <input type="text" class="form-control form-control-sm" name="billing_pincode" id="modal_billing_pincode" value="<?= htmlspecialchars($clicked_sales_order['billing_pincode'] ?? ''); ?>">
            </div>
            <div class="col-6">
              <label class="form-label small mb-0">GST Name</label>
              <input type="text" class="form-control form-control-sm" name="billing_gst" id="modal_billing_gst" value="<?= htmlspecialchars($clicked_sales_order['billing_gst'] ?? ''); ?>">
            </div>
            <div class="col-12">
              <label class="form-label small mb-0">GST No</label>
              <input type="text" class="form-control form-control-sm" name="billing_gst_no" id="modal_billing_gst_no" value="<?= htmlspecialchars($clicked_sales_order['billing_gst_no'] ?? ''); ?>">
            </div>
            <div class="col-12">
              <label class="form-label small mb-0">Address</label>
              <textarea class="form-control form-control-sm" name="billing_address" id="modal_billing_address" rows="2"><?= htmlspecialchars($clicked_sales_order['billing_address'] ?? ''); ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Products & Batches Table -->
    <div class="row mb-2">
      <div class="col-12">
        <h6 class="fw-bold mb-2">Products with Pending Black Quantities</h6>
        <div class="table-responsive border rounded bg-white">
          <table class="table table-bordered mb-0 compact-table" id="modal_products_table">
            <thead>
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="modal_select_all_batches" checked>
                </th>
                <th>Original Order</th>
                <th>Product Name</th>
                <th>Batch No</th>
                <th class="text-center" style="width: 100px;">Qty</th>
                <th class="text-center" style="width: 120px;">Received Qty</th>
                <th class="text-center" style="width: 120px;">Price</th>
                <th class="text-center" style="width: 100px;">GST %</th>
                <th class="text-center" style="width: 120px;">Total Exc GST</th>
                <th class="text-center" style="width: 120px;">GST Amt</th>
                <th class="text-center" style="width: 130px;">Total Incl GST</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($batches)): ?>
                <?php foreach ($batches as $batch): ?>
                  <?php 
                    $qty = $batch['black_qty'] - $batch['recieved_black_qty']; 
                  ?>
                  <tr class="modal-batch-row" data-batch-id="<?= $batch['id']; ?>">
                    <td class="text-center">
                      <input type="checkbox" name="checked_batches[]" value="<?= $batch['id']; ?>" class="form-check-input modal_batch_checkbox" checked>
                      
                      <!-- Hidden inputs passed on check -->
                      <input type="hidden" name="batch_product_id[<?= $batch['id']; ?>]" value="<?= $batch['product_id']; ?>">
                      <input type="hidden" name="batch_no[<?= $batch['id']; ?>]" value="<?= htmlspecialchars($batch['batch_no']); ?>">
                      <input type="hidden" name="batch_order_id[<?= $batch['id']; ?>]" value="<?= $batch['order_id']; ?>">
                    </td>
                    <td><?= htmlspecialchars($batch['order_no']); ?></td>
                    <td><?= htmlspecialchars($batch['product_name']); ?></td>
                    <td><?= htmlspecialchars($batch['batch_no']); ?></td>
                    <td>
                      <input type="number" class="form-control form-control-sm pending-qty" value="<?= $qty; ?>" readonly style="width: 80px;">
                    </td>
                    <td>
                      <input type="number" step="any" min="0" max="<?= $qty; ?>" name="batch_qty[<?= $batch['id']; ?>]" value="<?= $qty; ?>" class="form-control form-control-sm text-center row-qty-input row-qty-val" onkeyup="recalculateBill();" onchange="recalculateBill();" required style="width: 90px;">
                    </td>
                    <td>
                      <input type="number" step="any" min="0" name="batch_rate[<?= $batch['id']; ?>]" value="<?= number_format($batch['bill_amount'], 2, '.', ''); ?>" class="form-control form-control-sm text-center row-price-input" readonly required style="width: 100px;">
                    </td>
                    <td>
                      <input type="number" step="any" min="0" name="batch_gst[<?= $batch['id']; ?>]" value="<?= number_format($batch['gst'], 2, '.', ''); ?>" class="form-control form-control-sm text-center row-gst-input" readonly required style="width: 80px;">
                    </td>
                    <td class="text-end fw-semibold row-basic-val-text">0.00</td>
                    <td class="text-end row-gst-val-text">0.00</td>
                    <td class="text-end fw-bold text-primary row-total-val-text">0.00</td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="11" class="text-center text-muted">No pending product batches to bill.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Summary Details -->
    <div class="row">
      <div class="col-md-7"></div>
      <div class="col-md-5">
        <table class="table table-bordered table-striped mn-table mt-1 bg-white">
          <tbody>
            <tr>
              <td class="text-right fw-bold" style="width: 60%;">Total Bill Amt (Exc GST)</td>
              <td>
                <input type="number" step="any" name="basic_value" id="modal_basic_value" value="0.00" class="form-control text-end fw-bold" readonly>
              </td>
            </tr>
            <tr>
              <td class="text-right align-middle">
                <div class="d-flex flex-column align-items-end">
                  <span class="fw-bold mb-1">Select GST</span>
                  <select class="form-select form-select-sm" name="gst_type" id="modal_gst_type" onchange="change_modal_gst(this.value); recalculateBill();" style="width: 200px;">
                    <option value="Central GST / State GST" selected>Central GST / State GST</option>
                    <option value="IGST">IGST</option>
                  </select>
                </div>
              </td>
              <td class="align-middle">
                <div id="modal_cgst_sgst_inputs">
                  <p class="td-blank mb-1">
                    <input type="number" step="any" name="central_gst" id="modal_central_gst" value="0.00" placeholder="CGST Amount" class="form-control text-end" readonly>
                  </p>
                  <p class="td-blank mb-0">
                    <input type="number" step="any" name="state_gst" id="modal_state_gst" value="0.00" placeholder="SGST Amount" class="form-control text-end" readonly>
                  </p>
                </div>
                <div id="modal_igst_input" class="hidden">
                  <p class="td-blank mb-0">
                    <input type="number" step="any" name="igst" id="modal_igst" value="0.00" placeholder="IGST Amount" class="form-control text-end" readonly>
                  </p>
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-right fw-bold">Total Bill Amt (Incl GST)</td>
              <td>
                <input type="number" step="any" name="net_sales_value_1" id="modal_net_sales_value_1" value="0.00" class="form-control text-end fw-bold" readonly>
              </td>
            </tr>
            <tr>
              <td class="text-right align-middle">Round Of</td>
              <td>
                <input type="number" step="any" name="round_of" id="modal_round_of" value="0.00" class="form-control text-end" onkeyup="recalculateBill();">
              </td>
            </tr>
            <tr>
              <td class="text-right fw-bold bg-primary text-white">Grand Total</td>
              <td class="bg-primary text-white">
                <input type="number" step="any" name="grand_total" id="modal_grand_total" value="0.00" class="form-control text-end fw-bold text-primary" readonly>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Submit buttons -->
    <div class="row mt-3">
      <div class="col-12">
        <button type="submit" id="generate_bill_submit_btn" class="btn btn-primary waves-effect waves-float waves-light me-1" <?= empty($batches) ? 'disabled' : ''; ?>>
          <i class="fa fa-refresh"></i> Generate Bill
        </button>
        <button type="button" class="btn btn-secondary waves-effect waves-float waves-light" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </div>

    <?php echo form_close(); ?>
  </div>
</div>

<script>
function change_modal_gst(value) {
	let cgstSgstInputs = document.querySelector("#modal_cgst_sgst_inputs");
	let igstInput = document.querySelector("#modal_igst_input");

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

function recalculateBill() {
	var total_basic = 0;
	var total_gst = 0;
	
	// Loop over each row
	$('.modal-batch-row').each(function() {
		var $row = $(this);
		var isChecked = $row.find('.modal_batch_checkbox').is(':checked');
		
		$row.find('input:not(.modal_batch_checkbox)').prop('disabled', !isChecked);
		
		if (isChecked) {
			var maxQty = parseFloat($row.find('.row-qty-input').attr('max')) || 0;
			var qty = parseFloat($row.find('.row-qty-val').val()) || 0;
			if (qty > maxQty) {
				qty = maxQty;
				$row.find('.row-qty-val').val(qty);
			}
			if (qty < 0) {
				qty = 0;
				$row.find('.row-qty-val').val(qty);
			}

			var price = parseFloat($row.find('.row-price-input').val()) || 0;
			var gst_per = parseFloat($row.find('.row-gst-input').val()) || 0;
			
			var basic = qty * price;
			var gst_amt = (basic * gst_per) / 100;
			var total = basic + gst_amt;
			
			$row.find('.row-basic-val-text').text(basic.toFixed(2));
			$row.find('.row-gst-val-text').text(gst_amt.toFixed(2));
			$row.find('.row-total-val-text').text(total.toFixed(2));
			
			total_basic += basic;
			total_gst += gst_amt;
		} else {
			$row.find('.row-basic-val-text').text('0.00');
			$row.find('.row-gst-val-text').text('0.00');
			$row.find('.row-total-val-text').text('0.00');
		}
	});
	
	var gst_type = $('#modal_gst_type').val();
	var total_incl_gst = total_basic + total_gst;
	
	$('#modal_basic_value').val(total_basic.toFixed(2));
	$('#modal_net_sales_value_1').val(total_incl_gst.toFixed(2));
	
	if (gst_type === 'IGST') {
		$('#modal_igst').val(total_gst.toFixed(2));
		$('#modal_central_gst').val('0.00');
		$('#modal_state_gst').val('0.00');
	} else {
		$('#modal_central_gst').val((total_gst / 2).toFixed(2));
		$('#modal_state_gst').val((total_gst / 2).toFixed(2));
		$('#modal_igst').val('0.00');
	}
	
	var round_of = parseFloat($('#modal_round_of').val()) || 0;
	var grand_total = total_incl_gst + round_of;
	$('#modal_grand_total').val(grand_total.toFixed(2));
}

function submitGenerateBillForm(event) {
  event.preventDefault();
  
  var checkedCount = $('.modal_batch_checkbox:checked').length;
  if (checkedCount === 0) {
    Swal.fire({
      title: "Error!",
      text: "Please select at least one item to generate the bill.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: !1
    });
    return false;
  }
  
  var $submitBtn = $('#generate_bill_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  
  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }
  
  var formData = $('#sales_order_generate_bill_form').serialize();
  var formUrl = $('#sales_order_generate_bill_form').attr('action');
  
  $.ajax({
    type: 'POST',
    url: formUrl,
    data: formData,
    dataType: 'json',
    success: function(res) {
      if (res.status == '200' || res.status == 200) {
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
        
        Swal.fire({
          title: "Success!",
          text: res.message || "Invoice bill generated successfully",
          icon: "success",
          customClass: { confirmButton: "btn btn-primary" },
          buttonsStyling: !1
        }).then(() => {
          $('#large-modal').modal('hide');
          if (res.url) {
            window.location.href = res.url;
          } else {
            location.reload();
          }
        });
      } else {
        Swal.fire({
          title: "Error!",
          text: res.message || "An error occurred while generating the bill",
          icon: "error",
          customClass: { confirmButton: "btn btn-primary" },
          buttonsStyling: !1
        });
        
        $submitBtn.html(originalText);
        $submitBtn.attr("disabled", false);
        
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
      }
    },
    error: function(xhr, status, error) {
      Swal.fire({
        title: "Error!",
        text: "An error occurred while processing your request. Please try again.",
        icon: "error",
        customClass: { confirmButton: "btn btn-primary" },
        buttonsStyling: !1
      });
      
      $submitBtn.html(originalText);
      $submitBtn.attr("disabled", false);
      
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
    }
  });
  
  return false;
}

function get_modal_shipping_city(stateId) {
	$.ajax({
		type: "POST",
		url: "<?php echo base_url();?>admin/get_cities",
		data: { state_id: stateId },
		success: function (html) {
			$("#modal_shipping_city_id").html(html);
		}
	});
}

function get_modal_billing_city(stateId) {
	$.ajax({
		type: "POST",
		url: "<?php echo base_url();?>admin/get_cities",
		data: { state_id: stateId },
		success: function (html) {
			$("#modal_billing_city_id").html(html);
		}
	});
}

$(document).ready(function() {
  $('#modal_select_all_batches').on('change', function() {
    $('.modal_batch_checkbox').prop('checked', this.checked);
    recalculateBill();
  });
  
  $(document).on('change', '.modal_batch_checkbox', function() {
    if ($('.modal_batch_checkbox:checked').length === $('.modal_batch_checkbox').length) {
      $('#modal_select_all_batches').prop('checked', true);
    } else {
      $('#modal_select_all_batches').prop('checked', false);
    }
    recalculateBill();
  });

  // Customer dropdown change to dynamically fill address details
  $('#modal_customer_id').on('change', function() {
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

            var shipOnchange = $('#modal_shipping_state_id').attr('onchange');
            $('#modal_shipping_state_id').removeAttr('onchange');
            $('#modal_shipping_state_id').val(data.state_id).trigger('change');
            if (shipOnchange) {
                $('#modal_shipping_state_id').attr('onchange', shipOnchange);
            }
            
            $('#modal_shipping_city_id').html(cityHtml);
            $('#modal_shipping_city_id').val(data.city_id).trigger('change');
            $('#modal_shipping_pincode').val(data.pincode);
            $('#modal_shipping_address').val(data.address);
            $('#modal_shipping_gst').val(data.gst_name);
            $('#modal_shipping_gst_no').val(data.gst_no);

            // Update Billing fields
            var billOnchange = $('#modal_billing_state_id').attr('onchange');
            $('#modal_billing_state_id').removeAttr('onchange');
            $('#modal_billing_state_id').val(data.state_id).trigger('change');
            if (billOnchange) {
                $('#modal_billing_state_id').attr('onchange', billOnchange);
            }

            $('#modal_billing_city_id').html(cityHtml);
            $('#modal_billing_city_id').val(data.city_id).trigger('change');
            $('#modal_billing_pincode').val(data.pincode);
            $('#modal_billing_address').val(data.address);
            $('#modal_billing_gst').val(data.gst_name);
            $('#modal_billing_gst_no').val(data.gst_no);
          }
        }
      });
    }
  });

  // Cities initialization
  var s_state_id = "<?php echo $clicked_sales_order['shipping_state_id'] ?? ''; ?>";
  var s_city_id = "<?php echo $clicked_sales_order['shipping_city_id'] ?? ''; ?>";
  var b_state_id = "<?php echo $clicked_sales_order['billing_state_id'] ?? ''; ?>";
  var b_city_id = "<?php echo $clicked_sales_order['billing_city_id'] ?? ''; ?>";

  if (s_state_id) {
    $.ajax({
      type: "POST",
      url: "<?php echo base_url(); ?>admin/get_cities",
      data: { state_id: s_state_id },
      success: function(response) {
        $("#modal_shipping_city_id").html(response).val(s_city_id).trigger("change");
      }
    });
  }

  if (b_state_id) {
    $.ajax({
      type: "POST",
      url: "<?php echo base_url(); ?>admin/get_cities",
      data: { state_id: b_state_id },
      success: function(response) {
        $("#modal_billing_city_id").html(response).val(b_city_id).trigger("change");
      }
    });
  }
  
  change_modal_gst($('#modal_gst_type').val());
  recalculateBill();
});
</script>
