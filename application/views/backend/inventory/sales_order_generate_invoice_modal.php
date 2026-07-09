<?php
$customer_id = $param2;
$sales_order_id = $param3;

$clicked_sales_order = $this->inventory_model->get_sales_order_by_id($sales_order_id)->row_array();
$warehouse_id = $clicked_sales_order['warehouse_id'] ?? '0';

$product_query = $this->db->query("
  SELECT 
    sob.id, sob.order_id, sob.order_product_id, sob.batch_no, sob.white_qty, sob.recieved_qty, sob.bill_amount, sob.gst, sop.product_id, sop.product_name, sop.item_code, so.order_no 
  FROM sales_order_product_batch AS sob
    INNER JOIN sales_order_product AS sop ON sob.order_product_id = sop.id
    INNER JOIN sales_order AS so ON so.id = sob.order_id
  WHERE so.customer_id = $customer_id AND so.is_approved = '1' AND so.is_generated = '0' AND so.warehouse_id = $warehouse_id AND so.is_deleted = '0' AND (sob.white_qty > sob.recieved_qty) GROUP BY sob.id ORDER BY so.date DESC
");

$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
$products = !empty($product_query) ? $product_query->result_array() : [];
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/sales_order/gen_invoice_post', ['id' => 'sales_order_generate_invoice_form', 'onsubmit' => 'return submitGenerateInvoiceForm(event);']); ?>
    
    <div class="row align-items-center mb-2">
      <div class="col-md-6 col-12">
        <h5 class="mb-0"><strong><?= htmlspecialchars($customer_name); ?></strong>'s Sale Orders</h5>
      </div>
      <div class="col-md-6 col-12">
        <div class="row g-2 justify-content-end">
          <div class="col-md-6 col-12">
            <div class="form-group text-start">
              <label for="invoice_no" class="form-label mb-0">Invoice No</label>
              <input type="text" name="invoice_no" id="invoice_no" class="form-control form-control-sm" value="<?= htmlspecialchars($this->inventory_model->get_invoice_no()); ?>" required>
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group text-start">
              <label for="invoice_date" class="form-label mb-0">Invoice Date</label>
              <input type="date" name="invoice_date" id="invoice_date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped" id="modal-orders-table">
        <thead>
          <tr>
            <th class="text-center" style="width: 50px;">
              <div class="form-check justify-content-center">
                  <input type="checkbox" class="form-check-input" id="select_all_orders">
              </div>
            </th>
            <th>Order No</th>
            <th>Product Name</th>
            <th>Item Code</th>
            <th>Batch No</th>
            <th>Qty </th>
            <th>Received Qty</th>
            <th>Bill Amount</th>
            <th>Total Bill Amount</th>
            <th>GST (%)</th>
            <th>GST Amt</th>
            <th>Total Bill GST Amt</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): 
              $pending_qty = $product['white_qty'] - $product['recieved_qty'];
              $total_bill_amount = $pending_qty * $product['bill_amount'];
              $gst_amt = ($total_bill_amount * $product['gst']) / 100;
              $total_bill_gst_amt = $total_bill_amount + $gst_amt;
            ?>
              <tr>
                <td class="text-center">
                  <div class="form-check justify-content-center">
                    <input type="checkbox" name="sales_order_id[]" value="<?= $product['order_id']; ?>" class="form-check-input sales_order_checkbox" <?= ($product['order_id'] == $sales_order_id) ? 'checked' : ''; ?>>
                  </div>
                  <input type="hidden" name="id[]" value="<?= $product['id']; ?>" class="row-order">
                  <input type="hidden" name="batch_no[]" value="<?= $product['batch_no']; ?>" class="row-order">
                  <input type="hidden" name="product_id[]" value="<?= $product['product_id']; ?>" class="row-order">
                  <input type="hidden" name="product_name[]" value="<?= $product['product_name']; ?>" class="row-order">
                  <input type="hidden" name="item_code[]" value="<?= $product['item_code']; ?>" class="row-order">
                  <input type="hidden" name="order[]" value="<?= $product['order_id']; ?>" class="row-order">
                  <input type="hidden" name="order_id[]" value="<?= $product['order_id']; ?>" class="row-order-id">
                  <input type="hidden" name="order_product_id[]" value="<?= $product['order_product_id']; ?>" class="row-order-product-id">
                  <input type="hidden" name="is_valid[]" value="<?= ($product['order_id'] == $sales_order_id) ? '1' : '0'; ?>" class="row-is-valid">
                </td>
                <td><?= htmlspecialchars($product['order_no']); ?></td>
                <td><?= htmlspecialchars($product['product_name']); ?></td>
                <td><?= htmlspecialchars($product['item_code']); ?></td>
                <td><?= htmlspecialchars($product['batch_no']); ?></td>
                <td>
                  <input type="number" name="pending_qty[]" class="form-control form-control-sm pending-qty" value="<?= $pending_qty; ?>" readonly style="width: 80px;">
                </td>
                <td>
                  <input type="number" name="recieved_qty[]" class="form-control form-control-sm received-qty" value="<?= $pending_qty; ?>" min="0" max="<?= $pending_qty; ?>" step="any" required style="width: 90px;">
                </td>
                <td>
                  <input type="number" name="bill_amount[]" class="form-control form-control-sm bill-amount" value="<?= $product['bill_amount']; ?>" readonly style="width: 100px;">
                </td>
                <td>
                  <input type="number" name="total_bill_amount[]" class="form-control form-control-sm total-bill-amount" value="<?= number_format($total_bill_amount, 2, '.', ''); ?>" readonly style="width: 110px;">
                </td>
                <td>
                  <input type="number" name="gst[]" class="form-control form-control-sm gst-rate" value="<?= $product['gst']; ?>" readonly style="width: 80px;">
                </td>
                <td>
                  <input type="number" name="gst_amt[]" class="form-control form-control-sm gst-amount" value="<?= number_format($gst_amt, 2, '.', ''); ?>" readonly style="width: 100px;">
                </td>
                <td>
                  <input type="number" name="total_bill_gst_amt[]" class="form-control form-control-sm total-bill-gst-amount" value="<?= number_format($total_bill_gst_amt, 2, '.', ''); ?>" readonly style="width: 110px;">
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="12" class="text-center">No products found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="row mt-2">
      <div class="col-12">
        <button type="submit" id="generate_submit_btn" class="btn btn-primary waves-effect waves-float waves-light" <?= empty($products) ? 'disabled' : ''; ?>>
          <i class="fa fa-refresh"></i> Generate Invoice
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
function updateRowCalculations($row) {
  var pendingQty = parseFloat($row.find('.pending-qty').val()) || 0;
  var receivedQty = parseFloat($row.find('.received-qty').val()) || 0;
  
  // Validate: received qty cannot be more than pending qty
  if (receivedQty > pendingQty) {
    receivedQty = pendingQty;
    $row.find('.received-qty').val(receivedQty);
  }
  if (receivedQty < 0) {
    receivedQty = 0;
    $row.find('.received-qty').val(receivedQty);
  }
  
  var billAmount = parseFloat($row.find('.bill-amount').val()) || 0;
  var gstRate = parseFloat($row.find('.gst-rate').val()) || 0;
  
  var totalBillAmount = receivedQty * billAmount;
  var gstAmt = (totalBillAmount * gstRate) / 100;
  var totalBillGstAmt = totalBillAmount + gstAmt;
  
  $row.find('.total-bill-amount').val(totalBillAmount.toFixed(2));
  $row.find('.gst-amount').val(gstAmt.toFixed(2));
  $row.find('.total-bill-gst-amount').val(totalBillGstAmt.toFixed(2));
}

function submitGenerateInvoiceForm(event) {
  event.preventDefault();
  
  // Validate checkboxes
  var checkedCount = $('.sales_order_checkbox:checked').length;
  if (checkedCount === 0) {
    Swal.fire({
      title: "Error!",
      text: "Please select at least one sales order to generate invoice.",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
    return false;
  }
  
  // Disable submit button and show loading
  var $submitBtn = $('#generate_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  
  // Show loader if available
  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }
  
  // Get form data
  var formArray = $('#sales_order_generate_invoice_form').serializeArray();
  // Filter/deduplicate sales_order_id values
  var seenOrders = {};
  var filteredArray = formArray.filter(function(item) {
    if (item.name === 'sales_order_id[]') {
      if (seenOrders[item.value]) {
        return false; // Skip duplicate
      }
      seenOrders[item.value] = true;
    }
    return true;
  });
  var formData = $.param(filteredArray);
  var formUrl = $('#sales_order_generate_invoice_form').attr('action');
  
  // Submit via AJAX
  $.ajax({
    type: 'POST',
    url: formUrl,
    data: formData,
    dataType: 'json',
    success: function(res) {
      if (res.status == '200' || res.status == 200) {
        // Hide loader
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
        
        // Show success message
        Swal.fire({
          title: "Success!",
          text: res.message || "Invoices generated successfully",
          icon: "success",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        }).then(() => {
          // Close modal
          $('#large-modal').modal('hide');
          
          // Reload page or refresh list
          if (res.url) {
            window.location.href = res.url;
          } else {
            // Reload current page
            location.reload();
          }
        });
      } else {
        // Show error message
        Swal.fire({
          title: "Error!",
          text: res.message || "An error occurred while generating invoices",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        });
        
        // Re-enable submit button
        $submitBtn.html(originalText);
        $submitBtn.attr("disabled", false);
        
        // Hide loader
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
      }
    },
    error: function(xhr, status, error) {
      // Show error message
      Swal.fire({
        title: "Error!",
        text: "An error occurred while processing your request. Please try again.",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });
      
      // Re-enable submit button
      $submitBtn.html(originalText);
      $submitBtn.attr("disabled", false);
      
      // Hide loader
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
    }
  });
  
  return false;
}

$(document).ready(function() {
  function updateRowValidState($row, isChecked) {
    $row.find('.row-is-valid').val(isChecked ? '1' : '0');
  }

  $('#select_all_orders').on('change', function() {
    var isChecked = this.checked;
    $('.sales_order_checkbox').prop('checked', isChecked);
    $('.sales_order_checkbox').each(function() {
      updateRowValidState($(this).closest('tr'), isChecked);
    });
  });
  
  $('.sales_order_checkbox').on('change', function() {
    updateRowValidState($(this).closest('tr'), this.checked);
    
    if ($('.sales_order_checkbox:checked').length === $('.sales_order_checkbox').length) {
      $('#select_all_orders').prop('checked', true);
    } else {
      $('#select_all_orders').prop('checked', false);
    }
  });
  
  // Update calculations on input change
  $(document).on('input change', '.received-qty', function() {
    updateRowCalculations($(this).closest('tr'));
  });
  
  // Set initial state of select_all checkbox and row validity
  $('.sales_order_checkbox').each(function() {
    var isChecked = this.checked;
    updateRowValidState($(this).closest('tr'), isChecked);
    updateRowCalculations($(this).closest('tr'));
  });
  
  if ($('.sales_order_checkbox').length > 0 && $('.sales_order_checkbox:checked').length === $('.sales_order_checkbox').length) {
    $('#select_all_orders').prop('checked', true);
  }
});
</script>