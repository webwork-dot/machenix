<style>
  .coe-meta {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 16px;
  }
  .coe-label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: #94a3b8;
    font-weight: 700;
    margin-bottom: 2px;
  }
  .coe-value { font-size: 13px; font-weight: 700; color: #0f172a; }
  .coe-table th { font-size: 12px; white-space: nowrap; }
  .coe-table td { vertical-align: middle; font-size: 13px; }
  .coe-amount, .coe-qty { max-width: 120px; }
  .coe-locked { background: #fff7ed; }
  .coe-note { display: block; font-size: 11px; color: #64748b; margin-top: 3px; }
  .coe-lock-note { color: #c2410c; }
</style>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/conversion-order/edit_post/' . $invoice['id'], ['class' => 'add-ajax-redirect-form', 'onsubmit' => 'return validateConversionEdit(this);']); ?>
        <input type="hidden" id="coe_warehouse_id" value="<?php echo (int) $data['warehouse_id']; ?>">
        <div id="coe-removed"></div>

        <div class="d-flex justify-content-between align-items-center mb-1">
          <h5 class="mb-0"><b>Edit Conversion Order</b></h5>
          <a href="<?php echo site_url('inventory/conversion-order'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        <div class="coe-meta">
          <div class="row">
            <div class="col-md-2 mb-1">
              <span class="coe-label">Order No</span>
              <span class="coe-value"><?php echo htmlspecialchars($data['order_no']); ?></span>
            </div>
            <div class="col-md-2 mb-1">
              <span class="coe-label">Invoice No</span>
              <span class="coe-value"><?php echo htmlspecialchars($invoice['invoice_no']); ?></span>
            </div>
            <div class="col-md-2 mb-1">
              <span class="coe-label">Date</span>
              <span class="coe-value"><?php echo !empty($data['date']) ? date('d M Y', strtotime($data['date'])) : '-'; ?></span>
            </div>
            <div class="col-md-3 mb-1">
              <span class="coe-label">Customer</span>
              <span class="coe-value"><?php echo htmlspecialchars($data['customer_name']); ?></span>
            </div>
            <div class="col-md-3 mb-1">
              <span class="coe-label">Warehouse</span>
              <span class="coe-value"><?php echo htmlspecialchars($data['warehouse_name']); ?></span>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered coe-table">
            <thead class="table-light text-center">
              <tr>
                <th>Product</th>
                <th>Batch</th>
                <th>Qty</th>
                <th>GST %</th>
                <th>Per Qty Amount</th>
                <th>Total</th>
                <th>GST Amt</th>
                <th>Total Incl GST</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="coe-lines">
              <?php foreach ($products as $product): ?>
                <?php foreach ($product['batches'] as $batch): ?>
                  <?php
                    $qty = (float) $batch['qty'];
                    $gst = (float) $batch['gst'];
                    $amount = (float) $batch['amount'];
                    $locked = !empty($batch['qty_locked']);
                    $max_qty = (float) $batch['max_qty'];
                  ?>
                  <tr class="coe-row coe-existing <?php echo $locked ? 'coe-locked' : ''; ?>"
                    data-gst="<?php echo $gst; ?>"
                    data-locked="<?php echo $locked ? 1 : 0; ?>"
                    data-orig="<?php echo $qty; ?>"
                    data-max="<?php echo $max_qty; ?>"
                    data-inventory-id="<?php echo (int) $batch['inventory_id']; ?>"
                    data-batch-id="<?php echo (int) $batch['id']; ?>">
                    <td>
                      <?php echo htmlspecialchars($product['product_name']); ?>
                      <?php if ($locked): ?>
                        <span class="coe-note coe-lock-note"><?php echo htmlspecialchars($batch['lock_reason']); ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php echo htmlspecialchars($batch['batch_no']); ?>
                      <span class="coe-note">White: <?php echo number_format((float) $batch['avail_official_qty'], 2); ?> | Black: <?php echo number_format((float) $batch['current_black_qty'], 2); ?></span>
                    </td>
                    <td>
                      <input type="hidden" name="existing_batch_id[]" value="<?php echo (int) $batch['id']; ?>">
                      <input type="number" step="any" min="<?php echo $locked ? $qty : '0.01'; ?>" max="<?php echo $max_qty; ?>"
                        class="form-control coe-qty" name="existing_qty[]" value="<?php echo number_format($qty, 2, '.', ''); ?>"
                        <?php echo $locked ? 'readonly' : ''; ?> required>
                      <?php if (!$locked): ?>
                        <span class="coe-note">Max <?php echo number_format($max_qty, 2); ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo $gst; ?></td>
                    <td>
                      <input type="number" step="any" min="0" class="form-control coe-amount" name="existing_amount[]" value="<?php echo number_format($amount, 2, '.', ''); ?>" required>
                    </td>
                    <td class="text-end coe-total"><?php echo number_format($qty * $amount, 2); ?></td>
                    <td class="text-end coe-gst"><?php echo number_format((($qty * $amount) * $gst) / 100, 2); ?></td>
                    <td class="text-end coe-incl"><?php echo number_format(($qty * $amount) + ((($qty * $amount) * $gst) / 100), 2); ?></td>
                    <td class="text-center">
                      <?php if ($locked): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Quantity is locked"><i class="fa fa-lock"></i></button>
                      <?php else: ?>
                        <button type="button" class="btn btn-sm btn-outline-danger coe-remove" title="Remove"><i class="fa fa-times"></i></button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm mb-1" id="coe-add-product"><i class="fa fa-plus"></i> Add Product</button>

        <?php if (!empty($charges)): ?>
          <h6 class="mt-1">Other Charges</h6>
          <div class="table-responsive mb-1">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Type</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($charges as $charge): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($charge['charge_name']); ?></td>
                    <td class="text-end"><?php echo number_format((float) $charge['total_amount'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <div class="row justify-content-end">
          <div class="col-md-4">
            <table class="table table-bordered table-sm">
              <tr>
                <td>Total Bill (Exc GST)</td>
                <td class="text-end" id="coe_basic"><?php echo number_format((float) $data['basic_value'], 2); ?></td>
              </tr>
              <tr>
                <td>GST (<?php echo htmlspecialchars($data['gst_type'] ?: '-'); ?>)</td>
                <td class="text-end" id="coe_gst"><?php echo number_format((float) $data['gst_total'], 2); ?></td>
              </tr>
              <tr>
                <td>Total Bill (Incl GST)</td>
                <td class="text-end" id="coe_net"><?php echo number_format((float) $data['net_sales_value_1'], 2); ?></td>
              </tr>
              <tr>
                <td>Other Charges</td>
                <td class="text-end" id="coe_charges" data-value="<?php echo (float) ($data['other_charges_amount'] ?? 0); ?>"><?php echo number_format((float) ($data['other_charges_amount'] ?? 0), 2); ?></td>
              </tr>
              <tr>
                <td>Round Off</td>
                <td class="text-end" id="coe_round" data-value="<?php echo (float) ($data['round_of'] ?? 0); ?>"><?php echo number_format((float) ($data['round_of'] ?? 0), 2); ?></td>
              </tr>
              <tr>
                <td><b>Grand Total</b></td>
                <td class="text-end"><b id="coe_grand"><?php echo number_format((float) $data['grand_total'], 2); ?></b></td>
              </tr>
            </table>
          </div>
        </div>

        <button type="submit" class="btn btn-primary mt-1 btn_verify" name="btn_verify">Update</button>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script>
var coeProductOptions = <?php
  $options = '<option value="">Select Product</option>';
  foreach (($product_list ?? array()) as $product_option) {
    $options .= '<option value="' . (int) $product_option['id'] . '">' . htmlspecialchars($product_option['name'], ENT_QUOTES) . '</option>';
  }
  echo json_encode($options);
?>;
var coeNewIndex = 0;

function coeMoney(value) {
  return (Number(value) || 0).toFixed(2);
}

function coeWarn(text) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({ title: 'Not allowed', text: text, icon: 'warning' });
  } else {
    alert(text);
  }
}

function recalculateConversionAmounts() {
  var basic = 0;
  var gst = 0;
  $('.coe-row').each(function() {
    var qty = parseFloat($(this).find('.coe-qty').val()) || 0;
    var gstPer = parseFloat($(this).data('gst')) || parseFloat($(this).find('.coe-gst-input').val()) || 0;
    var amount = parseFloat($(this).find('.coe-amount').val()) || 0;
    var total = qty * amount;
    var gstAmt = (total * gstPer) / 100;
    var incl = total + gstAmt;
    $(this).find('.coe-total').text(coeMoney(total));
    $(this).find('.coe-gst').text(coeMoney(gstAmt));
    $(this).find('.coe-incl').text(coeMoney(incl));
    basic += total;
    gst += gstAmt;
  });
  var charges = parseFloat($('#coe_charges').data('value')) || 0;
  var roundOff = parseFloat($('#coe_round').data('value')) || 0;
  var net = basic + gst;
  $('#coe_basic').text(coeMoney(basic));
  $('#coe_gst').text(coeMoney(gst));
  $('#coe_net').text(coeMoney(net));
  $('#coe_grand').text(coeMoney(net + charges + roundOff));
}

function coeInventoryTaken(exceptRow) {
  var taken = {};
  $('.coe-row').each(function() {
    if (exceptRow && this === exceptRow[0]) return;
    var inventoryId = String($(this).data('inventory-id') || $(this).find('.coe-new-batch').val() || '');
    if (inventoryId && inventoryId !== '0') taken[inventoryId] = true;
  });
  return taken;
}

$('#coe-add-product').on('click', function() {
  coeNewIndex++;
  var row = $('<tr class="coe-row coe-new" data-gst="0" data-locked="0" data-max="0" data-inventory-id="">' +
    '<td><select class="form-control coe-new-product" name="new_product_id[]">' + coeProductOptions + '</select></td>' +
    '<td><select class="form-control coe-new-batch" name="new_inventory_id[]"><option value="">Select Batch</option></select><span class="coe-note coe-avail"></span></td>' +
    '<td><input type="number" step="any" min="0.01" class="form-control coe-qty" name="new_qty[]" value=""><span class="coe-note coe-max-note"></span></td>' +
    '<td><input type="number" step="any" min="0" class="form-control coe-gst-input" name="new_gst[]" value="0"></td>' +
    '<td><input type="number" step="any" min="0" class="form-control coe-amount" name="new_amount[]" value="0"></td>' +
    '<td class="text-end coe-total">0.00</td>' +
    '<td class="text-end coe-gst">0.00</td>' +
    '<td class="text-end coe-incl">0.00</td>' +
    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger coe-remove" title="Remove"><i class="fa fa-times"></i></button></td>' +
  '</tr>');
  $('#coe-lines').append(row);
  if ($.fn.select2) {
    row.find('.coe-new-product, .coe-new-batch').select2({ width: '100%' });
  }
});

$(document).on('change', '.coe-new-product', function() {
  var productId = $(this).val();
  var row = $(this).closest('.coe-row');
  var batchSelect = row.find('.coe-new-batch');
  batchSelect.html('<option value="">Select Batch</option>');
  row.find('.coe-avail').text('');
  row.data('max', 0).data('inventory-id', '');
  if (!productId) return;
  $.ajax({
    type: 'POST',
    url: '<?php echo base_url(); ?>inventory/get_batches_by_warehouse_product',
    data: { warehouse_id: $('#coe_warehouse_id').val(), product_id: productId },
    success: function(html) {
      batchSelect.html('<option value="">Select Batch</option>' + html).trigger('change');
    }
  });
  $.ajax({
    type: 'POST',
    url: '<?php echo base_url(); ?>inventory/get_raw_product_details',
    data: { product_id: productId },
    dataType: 'json',
    success: function(res) {
      if (res && res.status == 200) {
        row.find('.coe-gst-input').val(res.gst || 0);
        row.data('gst', res.gst || 0);
        if (!parseFloat(row.find('.coe-amount').val())) {
          row.find('.coe-amount').val(res.amount || 0);
        }
        recalculateConversionAmounts();
      }
    }
  });
});

$(document).on('change', '.coe-new-batch', function() {
  var batchId = $(this).val();
  var row = $(this).closest('.coe-row');
  row.data('inventory-id', batchId || '');
  row.find('.coe-avail').text('');
  row.data('max', 0);
  if (!batchId) return;
  if (coeInventoryTaken(row)[String(batchId)]) {
    coeWarn('This batch is already on the conversion. Change that row quantity instead.');
    $(this).val('').trigger('change');
    return;
  }
  $.ajax({
    type: 'POST',
    url: '<?php echo base_url(); ?>inventory/get_batch_qty_details',
    data: { batch_id: batchId },
    dataType: 'json',
    success: function(res) {
      var white = parseFloat(res.official_qty) || 0;
      var black = parseFloat(res.black_qty) || 0;
      if (white <= 0) {
        coeWarn('No white quantity is available in this batch.');
        row.find('.coe-new-batch').val('').trigger('change');
        return;
      }
      row.data('max', white);
      row.find('.coe-qty').attr('max', white);
      row.find('.coe-avail').text('White: ' + coeMoney(white) + ' | Black: ' + coeMoney(black));
      row.find('.coe-max-note').text('Max ' + coeMoney(white));
    }
  });
});

$(document).on('input', '.coe-qty, .coe-amount, .coe-gst-input', function() {
  var row = $(this).closest('.coe-row');
  if ($(this).hasClass('coe-qty')) {
    if (String(row.data('locked')) === '1') {
      $(this).val(row.data('orig'));
    } else {
      var max = parseFloat(row.data('max')) || 0;
      var qty = parseFloat($(this).val());
      if (max > 0 && qty > max) {
        $(this).val(max);
        coeWarn('Quantity cannot be more than available white plus the quantity already converted on this row.');
      }
    }
  }
  if ($(this).hasClass('coe-gst-input')) {
    row.data('gst', parseFloat($(this).val()) || 0);
  }
  recalculateConversionAmounts();
});

function validateConversionEdit(form) {
  $('#coe-lines .coe-new').each(function() {
    var product = $(this).find('.coe-new-product').val();
    var batch = $(this).find('.coe-new-batch').val();
    var qty = $.trim($(this).find('.coe-qty').val());
    if (!product && !batch && qty === '') {
      $(this).remove();
    }
  });

  var complete = 0;
  var incomplete = false;
  $('#coe-lines .coe-row').each(function() {
    if ($(this).hasClass('coe-existing')) {
      if ((parseFloat($(this).find('.coe-qty').val()) || 0) > 0) complete++;
      return;
    }
    var product = $(this).find('.coe-new-product').val();
    var batch = $(this).find('.coe-new-batch').val();
    var qty = parseFloat($(this).find('.coe-qty').val()) || 0;
    if (product && batch && qty > 0) complete++;
    else incomplete = true;
  });

  if (complete < 1) {
    coeWarn('At least one product row is required.');
    return false;
  }
  if (incomplete) {
    coeWarn('Complete the added product row, or remove it.');
    return false;
  }
  return checkForm(form);
}

$(document).on('click', '.coe-remove', function() {
  var row = $(this).closest('.coe-row');
  if ($('#coe-lines .coe-row').length <= 1) {
    coeWarn('At least one product row is required.');
    return;
  }
  if (String(row.data('locked')) === '1') {
    coeWarn(row.find('.coe-lock-note').text() || 'This quantity is locked.');
    return;
  }
  if (row.hasClass('coe-existing')) {
    $('#coe-removed').append('<input type="hidden" name="remove_batch_id[]" value="' + row.data('batch-id') + '">');
    row.find('input, select').prop('disabled', true);
  }
  row.remove();
  recalculateConversionAmounts();
});
</script>
