<?php
$customer_id = $param2;
$sales_order_id = $param3;

$clicked_sales_order = $this->inventory_model->get_sales_order_by_id($sales_order_id)->row_array();
$is_weird = $clicked_sales_order['is_weird'] ?? '0';
$warehouse_id = $clicked_sales_order['warehouse_id'] ?? '0';

// Fetch all ungenerated approved sales orders for this customer of the same type
$orders = $this->db->where([
  'customer_id' => $customer_id,
  'is_approved' => '1',
  'is_generated' => '0',
  'warehouse_id' => $warehouse_id,
  'is_deleted' => '0'
])->order_by('date', 'desc')->get('sales_order')->result_array();

$customer_name = $this->common_model->selectByidParam($customer_id, 'customer', 'company_name');
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
            <th>Order Date</th>
            <th>Order No</th>
            <th>Ref No</th>
            <th>Warehouse</th>
            <th>Grand Total</th>
            <th>Remark</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td class="text-center">
                  <div class="form-check justify-content-center">
                    <input type="checkbox" name="sales_order_id[]" value="<?= $order['id']; ?>" class="form-check-input sales_order_checkbox" <?= ($order['id'] == $sales_order_id) ? 'checked' : ''; ?>>
                  </div>
                </td>
                <td><?= date('d M, Y', strtotime($order['date'])); ?></td>
                <td><?= htmlspecialchars($order['order_no']); ?></td>
                <td><?= htmlspecialchars($order['refrence_no'] ?: '-'); ?></td>
                <td><?= htmlspecialchars($order['warehouse_name'] ?: '-'); ?></td>
                <td><?= htmlspecialchars($order['grand_total']); ?></td>
                <td><?= htmlspecialchars($order['remark'] ?: '-'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center">No orders found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="row mt-2">
      <div class="col-12">
        <button type="submit" id="generate_submit_btn" class="btn btn-primary waves-effect waves-float waves-light" <?= empty($orders) ? 'disabled' : ''; ?>>
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
  var formData = $('#sales_order_generate_invoice_form').serialize();
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
  $('#select_all_orders').on('change', function() {
    $('.sales_order_checkbox').prop('checked', this.checked);
  });
  
  $('.sales_order_checkbox').on('change', function() {
    if ($('.sales_order_checkbox:checked').length === $('.sales_order_checkbox').length) {
      $('#select_all_orders').prop('checked', true);
    } else {
      $('#select_all_orders').prop('checked', false);
    }
  });
  
  // Set initial state of select_all checkbox if all visible rows are checked
  if ($('.sales_order_checkbox').length > 0 && $('.sales_order_checkbox:checked').length === $('.sales_order_checkbox').length) {
    $('#select_all_orders').prop('checked', true);
  }
});
</script>