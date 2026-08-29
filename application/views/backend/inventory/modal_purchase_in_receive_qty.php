<?php
  // Get PO ID from param2
  $po_id = $param2;

  // Get PO header details
  $po_data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();
  
  if (empty($po_data)) {
    echo '<div class="alert alert-danger">Purchase Order not found.</div>';
    return;
  }

  // Get warehouse details
  $has_warehouse = (!empty($po_data['warehouse_id']) && (int)$po_data['warehouse_id'] > 0);
  $warehouse_name = $po_data['warehouse_name'] ?? '';
  if ($has_warehouse) {
    $warehouse = $this->db->query("SELECT name FROM warehouse WHERE id = '" . $po_data['warehouse_id'] . "'")->row_array();
    $warehouse_name = $warehouse['name'] ?? $warehouse_name;
  }

  // Load session company warehouses if warehouse is not set
  $session_company_id = (int)$this->session->userdata('company_id');
  $session_warehouses = [];
  if (!$has_warehouse) {
    $session_warehouses = $this->db->get_where('warehouse', ['is_deleted' => 0, 'company_id' => $session_company_id])->result_array();
  }

  // Get supplier (company) details
  $supplier_name = $po_data['supplier_name'] ?? '';
  if (empty($supplier_name) && !empty($po_data['supplier_id'])) {
    $comp = $this->db->query("SELECT name FROM company WHERE id = '" . $po_data['supplier_id'] . "'")->row_array();
    $supplier_name = $comp['name'] ?? 'N/A';
  }

  // Get products for this PO
  $products = $this->db->query("
    SELECT pop.*, COALESCE(c.name, '') as sup_name
    FROM purchase_order_product pop
    LEFT JOIN company c ON c.id = pop.supplier_id
    WHERE pop.parent_id = '$po_id'
    ORDER BY pop.id ASC
  ")->result_array();

  $total_batch_qty = 0;
  $total_already_received = 0;
  $total_pending_qty = 0;

  foreach ($products as $p) {
    $tot = floatval($p['quantity'] ?? 0);
    $rcv = floatval($p['received'] ?? 0);
    $pnd = floatval($p['pending'] > 0 ? $p['pending'] : max(0, $tot - $rcv));
    if ($p['is_complete'] == 1) {
      $pnd = 0;
    }
    $total_batch_qty += $tot;
    $total_already_received += $rcv;
    $total_pending_qty += $pnd;
  }
?>

<style>
  .receive-qty-modal {
    padding: 6px;
  }

  .meta-dashboard-compact {
    background: #f8fafc;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 14px;
    border: 1px solid #e2e8f0;
  }

  .meta-grid-receive {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px 16px;
  }

  .receive-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .receive-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
    font-weight: 700;
  }

  .receive-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: #0f172a;
  }

  .table-responsive-container-receive {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    overflow: hidden;
    margin-bottom: 14px;
  }

  .receive-table {
    min-width: 1000px;
    width: 100%;
    margin-bottom: 0;
    font-size: 0.85rem;
    border-collapse: collapse;
  }

  .receive-table thead th {
    background: linear-gradient(135deg, #2f3b52 0%, #1e2533 100%) !important;
    color: #ffffff !important;
    font-weight: 600;
    text-align: center;
    padding: 10px 8px;
    border: none;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 0.82rem;
  }

  .receive-table tbody td {
    padding: 8px 10px;
    vertical-align: middle;
    border-color: #f1f5f9;
    white-space: nowrap;
    border-right: 1px solid #f1f5f9;
  }

  .receive-table tbody td:last-child {
    border-right: none;
  }

  .receive-table tbody tr:nth-child(even) {
    background-color: #f8fafc;
  }

  .receive-table tbody tr:hover {
    background-color: #f1f5f9;
  }

  .receive-input-box {
    max-width: 110px;
    margin: 0 auto;
    font-weight: 700;
  }

  .badge-ready {
    background-color: #e0f2fe;
    color: #0369a1;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 3px;
  }

  .badge-spare {
    background-color: #fef3c7;
    color: #92400e;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 3px;
  }

  .totals-row-bg {
    background-color: #e9ecef !important;
    font-weight: 700;
    color: #0f172a;
    border-top: 2px solid #cbd5e1;
  }

  .qty-invalid {
    border-color: #dc3545 !important;
    background-color: #ffe6e6 !important;
  }
</style>

<?php
  $existing_voucher_no = trim($po_data['voucher_no'] ?? '');
  $has_batch_no = !empty($existing_voucher_no);
?>

<div class="receive-qty-modal">
  <?php echo form_open('inventory/receive_company_purchase_qty', ['id' => 'receive_company_purchase_qty_form', 'onsubmit' => 'return submitReceiveQtyForm(event);']); ?>
  
  <input type="hidden" name="po_id" id="po_id" value="<?php echo $po_id; ?>">

  <!-- Batch Meta Information -->
  <div class="meta-dashboard-compact">
    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2 flex-grow-1">
        <i class="fa fa-truck text-primary"></i>
        <?php if ($has_batch_no) { ?>
          <span class="fw-bold">Batch / Voucher No: <span class="text-primary"><?php echo htmlspecialchars($existing_voucher_no); ?></span></span>
        <?php } else { ?>
          <span class="fw-bold text-nowrap">Batch No <span class="text-danger">*</span>:</span>
          <input type="text" name="voucher_no" id="voucher_no" class="form-control form-control-sm" placeholder="Enter Batch No" required style="max-width: 250px;">
        <?php } ?>
      </div>
      <div>
        <span class="badge bg-primary">Company Purchase In</span>
      </div>
    </div>

    <div class="meta-grid-receive mt-1">
      <div class="receive-item">
        <span class="receive-label">Purchase Date</span>
        <span class="receive-value"><?php echo !empty($po_data['date']) ? date('d M, Y', strtotime($po_data['date'])) : '-'; ?></span>
      </div>

      <div class="receive-item">
        <span class="receive-label">Seller Company</span>
        <span class="receive-value text-primary"><?php echo htmlspecialchars($supplier_name ?: '-'); ?></span>
      </div>

      <div class="receive-item">
        <span class="receive-label">Destination Warehouse <?php if (!$has_warehouse) { ?><span class="text-danger">*</span><?php } ?></span>
        <?php if ($has_warehouse) { ?>
          <span class="receive-value"><?php echo htmlspecialchars($warehouse_name ?: '-'); ?></span>
        <?php } else { ?>
          <select name="warehouse_id" id="receive_warehouse_id" class="form-select form-select-sm" required style="max-width: 180px;">
            <option value="">Select Warehouse</option>
            <?php foreach ($session_warehouses as $wh) { ?>
              <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
            <?php } ?>
          </select>
        <?php } ?>
      </div>

      <div class="receive-item">
        <span class="receive-label">Total Batch Units</span>
        <span class="receive-value"><?php echo number_format($total_batch_qty, 0); ?> (Pending: <span class="text-danger"><?php echo number_format($total_pending_qty, 0); ?></span>)</span>
      </div>

      <div class="receive-item">
        <span class="receive-label">Receiving Date <span class="text-danger">*</span></span>
        <input type="date" name="received_date" id="received_date" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required style="max-width: 160px;">
      </div>
    </div>
  </div>

  <!-- Table Top Helpers -->
  <div class="d-flex justify-content-between align-items-center mb-1">
    <div class="fw-bold text-dark">
      <i class="fa fa-cube text-primary me-1"></i> Products to Receive (<?php echo count($products); ?> items)
    </div>
    <div class="d-flex gap-1">
      <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillAllPending();">
        <i class="fa fa-check-square-o"></i> Receive All Pending
      </button>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllReceive();">
        <i class="fa fa-times"></i> Clear All
      </button>
    </div>
  </div>

  <!-- Products List Table -->
  <div class="table-responsive-container-receive">
    <div style="max-height: 400px; overflow-x: auto; overflow-y: auto;">
      <table class="table receive-table">
        <thead>
          <tr>
            <th style="width: 40px;" class="text-center">#</th>
            <th style="min-width: 200px;" class="text-left">Product Name</th>
            <th style="min-width: 110px;" class="text-left">Model / SKU</th>
            <th style="min-width: 80px;" class="text-center">Type</th>
            <th style="min-width: 90px;" class="text-right">Rate (₹)</th>
            <th style="min-width: 80px;" class="text-center">Total Qty</th>
            <th style="min-width: 90px;" class="text-center">Received Qty</th>
            <th style="min-width: 90px;" class="text-center">Pending Qty</th>
            <th style="min-width: 120px;" class="text-center">Receive Now <span class="text-danger">*</span></th>
            <th style="min-width: 90px;" class="text-center">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (!empty($products)) {
            $sr = 1;
            foreach ($products as $p) {
              $tot_qty = floatval($p['quantity'] ?? 0);
              $rcv_qty = floatval($p['received'] ?? 0);
              $pnd_qty = floatval($p['pending'] > 0 ? $p['pending'] : max(0, $tot_qty - $rcv_qty));
              if ($p['is_complete'] == 1) {
                $pnd_qty = 0;
              }
              $is_complete = ($pnd_qty <= 0 || $p['is_complete'] == 1);
              $is_ready = ($p['product_type'] == 'ready');
          ?>
          <tr>
            <td class="text-center"><?php echo $sr++; ?></td>
            <td class="text-left font-weight-bold">
              <?php echo htmlspecialchars($p['product_name'] ?? 'N/A'); ?>
              <?php if (!empty($p['color_name'])) { ?>
                <small class="text-muted d-block">Color: <?php echo htmlspecialchars($p['color_name']); ?></small>
              <?php } ?>
            </td>
            <td class="text-left font-monospace"><?php echo htmlspecialchars($p['item_code'] ?? '-'); ?></td>
            <td class="text-center">
              <?php if ($is_ready) { ?>
                <span class="badge-ready">Ready</span>
              <?php } else { ?>
                <span class="badge-spare">Spare</span>
              <?php } ?>
            </td>
            <td class="text-right">₹<?php echo number_format(floatval($p['rate'] ?? 0), 2); ?></td>
            <td class="text-center font-weight-bold"><?php echo number_format($tot_qty, 0); ?></td>
            <td class="text-center text-success font-weight-bold"><?php echo number_format($rcv_qty, 0); ?></td>
            <td class="text-center text-danger font-weight-bold" id="pending_label_<?php echo $p['id']; ?>">
              <?php echo number_format($pnd_qty, 0); ?>
            </td>
            <td class="text-center">
              <?php if ($is_complete) { ?>
                <input type="number" class="form-control form-control-sm text-center receive-input-box" value="0" disabled readonly>
              <?php } else { ?>
                <input type="number" step="any" min="0" max="<?php echo $pnd_qty; ?>" name="receive_qty[<?php echo $p['id']; ?>]" id="receive_qty_<?php echo $p['id']; ?>" class="form-control form-control-sm text-center receive-input-box receive-qty-field" value="<?php echo $pnd_qty; ?>" data-max="<?php echo $pnd_qty; ?>" data-id="<?php echo $p['id']; ?>" oninput="onQtyChange(this, <?php echo $pnd_qty; ?>)">
                <small class="text-danger d-none invalid-msg" id="error_<?php echo $p['id']; ?>">Max: <?php echo $pnd_qty; ?></small>
              <?php } ?>
            </td>
            <td class="text-center">
              <?php if ($is_complete) { ?>
                <span class="badge bg-success"><i class="fa fa-check"></i> Completed</span>
              <?php } else { ?>
                <span class="badge bg-warning text-dark"><i class="fa fa-clock-o"></i> Pending</span>
              <?php } ?>
            </td>
          </tr>
          <?php 
            }
          } else { 
          ?>
          <tr>
            <td colspan="10" class="text-center py-3 text-muted">No products found for this purchase order.</td>
          </tr>
          <?php } ?>

          <tr class="totals-row-bg">
            <td colspan="5" class="text-right"><strong>Total:</strong></td>
            <td class="text-center"><strong><?php echo number_format($total_batch_qty, 0); ?></strong></td>
            <td class="text-center text-success"><strong><?php echo number_format($total_already_received, 0); ?></strong></td>
            <td class="text-center text-danger"><strong><?php echo number_format($total_pending_qty, 0); ?></strong></td>
            <td class="text-center text-primary"><strong id="total_receiving_now"><?php echo number_format($total_pending_qty, 0); ?></strong></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="row mt-2">
    <div class="col-12 text-end">
      <button type="button" class="btn btn-secondary waves-effect waves-float waves-light me-1" data-bs-dismiss="modal">
        Cancel
      </button>
      <button type="submit" id="receive_submit_btn" class="btn btn-primary waves-effect waves-float waves-light" <?php echo ($total_pending_qty <= 0) ? 'disabled' : ''; ?>>
        <i class="fa fa-check"></i> Confirm & Receive Stock
      </button>
    </div>
  </div>

  <?php echo form_close(); ?>
</div>

<script>
function onQtyChange(inputElement, maxPending) {
  var val = parseFloat($(inputElement).val());
  var id = $(inputElement).data('id');
  var errorSpan = $('#error_' + id);

  if (isNaN(val) || val < 0) {
    $(inputElement).val(0);
    val = 0;
  }

  if (val > maxPending) {
    $(inputElement).addClass('qty-invalid');
    errorSpan.removeClass('d-none');
  } else {
    $(inputElement).removeClass('qty-invalid');
    errorSpan.addClass('d-none');
  }

  calculateTotalReceivingNow();
}

function fillAllPending() {
  $('.receive-qty-field').each(function() {
    var max = parseFloat($(this).data('max')) || 0;
    $(this).val(max);
    $(this).removeClass('qty-invalid');
    var id = $(this).data('id');
    $('#error_' + id).addClass('d-none');
  });
  calculateTotalReceivingNow();
}

function clearAllReceive() {
  $('.receive-qty-field').each(function() {
    $(this).val(0);
    $(this).removeClass('qty-invalid');
    var id = $(this).data('id');
    $('#error_' + id).addClass('d-none');
  });
  calculateTotalReceivingNow();
}

function calculateTotalReceivingNow() {
  var total = 0;
  $('.receive-qty-field').each(function() {
    var val = parseFloat($(this).val()) || 0;
    total += val;
  });
  $('#total_receiving_now').text(total.toLocaleString());
}

function submitReceiveQtyForm(event) {
  event.preventDefault();

  var voucherInput = $('#voucher_no');
  if (voucherInput.length > 0) {
    var voucherVal = $.trim(voucherInput.val());
    if (voucherVal === '') {
      Swal.fire({
        title: "Batch No Required",
        text: "Please enter a Batch No before confirming receipt.",
        icon: "warning",
        customClass: { confirmButton: "btn btn-primary" }
      });
      voucherInput.focus();
      return false;
    }
  }

  var warehouseSelect = $('#receive_warehouse_id');
  if (warehouseSelect.length > 0) {
    var whVal = $.trim(warehouseSelect.val());
    if (whVal === '' || whVal === '0') {
      Swal.fire({
        title: "Warehouse Required",
        text: "Please select a Destination Warehouse before confirming receipt.",
        icon: "warning",
        customClass: { confirmButton: "btn btn-primary" }
      });
      warehouseSelect.focus();
      return false;
    }
  }

  var hasInvalid = false;
  var totalReceiving = 0;

  $('.receive-qty-field').each(function() {
    var val = parseFloat($(this).val()) || 0;
    var max = parseFloat($(this).data('max')) || 0;
    if (val > max) {
      hasInvalid = true;
      $(this).addClass('qty-invalid');
    }
    totalReceiving += val;
  });

  if (hasInvalid) {
    Swal.fire({
      title: "Validation Error",
      text: "Receivable quantity cannot be greater than the pending quantity.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" }
    });
    return false;
  }

  if (totalReceiving <= 0) {
    Swal.fire({
      title: "No Quantity Entered",
      text: "Please enter a receivable quantity greater than 0 for at least one product.",
      icon: "warning",
      customClass: { confirmButton: "btn btn-primary" }
    });
    return false;
  }

  var $submitBtn = $('#receive_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Receiving...');

  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }

  var formData = $('#receive_company_purchase_qty_form').serialize();
  var formUrl = $('#receive_company_purchase_qty_form').attr('action');

  $.ajax({
    type: 'POST',
    url: formUrl,
    data: formData,
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }

      if (res.status == 200 || res.status == '200') {
        Swal.fire({
          title: "Success!",
          text: res.message || "Stock received into inventory successfully!",
          icon: "success",
          customClass: { confirmButton: "btn btn-primary" }
        }).then(() => {
          $('#large-modal').modal('hide');
          if (typeof dataTable !== 'undefined') {
            dataTable.ajax.reload(null, false);
          } else if (typeof $('#report-datatable').DataTable === 'function') {
            $('#report-datatable').DataTable().ajax.reload(null, false);
          } else {
            location.reload();
          }
        });
      } else {
        Swal.fire({
          title: "Error!",
          text: res.message || "An error occurred while receiving quantity.",
          icon: "error",
          customClass: { confirmButton: "btn btn-primary" }
        });
        $submitBtn.html(originalText);
        $submitBtn.attr("disabled", false);
      }
    },
    error: function() {
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
      Swal.fire({
        title: "Server Error",
        text: "Could not complete the receiving process. Please try again.",
        icon: "error",
        customClass: { confirmButton: "btn btn-primary" }
      });
      $submitBtn.html(originalText);
      $submitBtn.attr("disabled", false);
    }
  });

  return false;
}
</script>
