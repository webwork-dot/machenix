<style>
  .boe-meta {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 16px;
  }
  .boe-label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: #94a3b8;
    font-weight: 700;
    margin-bottom: 2px;
  }
  .boe-value { font-size: 13px; font-weight: 700; color: #0f172a; }
  .boe-table th { font-size: 12px; white-space: nowrap; }
  .boe-table td { vertical-align: middle; font-size: 13px; }
  .boe-amount { max-width: 140px; }
</style>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/black-order/edit_post/' . $invoice['id'], ['class' => 'add-ajax-redirect-form', 'onsubmit' => 'return checkForm(this);']); ?>

        <div class="d-flex justify-content-between align-items-center mb-1">
          <h5 class="mb-0"><b>Edit Black Order Amount</b></h5>
          <a href="<?php echo site_url('inventory/black-order?status=completed'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        <div class="boe-meta">
          <div class="row">
            <div class="col-md-2 mb-1">
              <span class="boe-label">Invoice No</span>
              <span class="boe-value"><?php echo htmlspecialchars($invoice['invoice_no']); ?></span>
            </div>
            <div class="col-md-2 mb-1">
              <span class="boe-label">Order No</span>
              <span class="boe-value"><?php echo htmlspecialchars($invoice['order_no']); ?></span>
            </div>
            <div class="col-md-2 mb-1">
              <span class="boe-label">Date</span>
              <span class="boe-value"><?php echo !empty($invoice['date']) ? date('d M Y', strtotime($invoice['date'])) : '-'; ?></span>
            </div>
            <div class="col-md-3 mb-1">
              <span class="boe-label">Customer</span>
              <span class="boe-value"><?php echo htmlspecialchars($invoice['customer_name']); ?></span>
            </div>
            <div class="col-md-3 mb-1">
              <span class="boe-label">Warehouse</span>
              <span class="boe-value"><?php echo htmlspecialchars($invoice['warehouse_name']); ?></span>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered boe-table">
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
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product): ?>
                <?php $qty = (float) $product['qty']; $gst = (float) $product['gst']; $amount = (float) $product['amount']; ?>
                <tr class="boe-row" data-qty="<?php echo $qty; ?>" data-gst="<?php echo $gst; ?>">
                  <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                  <td><?php echo htmlspecialchars($product['batch_no']); ?></td>
                  <td class="text-center"><?php echo $qty; ?></td>
                  <td class="text-center"><?php echo $gst; ?></td>
                  <td>
                    <input type="hidden" name="product_id[]" value="<?php echo (int) $product['id']; ?>">
                    <input type="number" step="any" min="0" class="form-control boe-amount" name="amount[]" value="<?php echo number_format($amount, 2, '.', ''); ?>" required>
                  </td>
                  <td class="text-end boe-total"><?php echo number_format($qty * $amount, 2); ?></td>
                  <td class="text-end boe-gst"><?php echo number_format((($qty * $amount) * $gst) / 100, 2); ?></td>
                  <td class="text-end boe-incl"><?php echo number_format(($qty * $amount) + ((($qty * $amount) * $gst) / 100), 2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="row justify-content-end">
          <div class="col-md-4">
            <table class="table table-bordered table-sm">
              <tr>
                <td>Total Bill (Exc GST)</td>
                <td class="text-end" id="boe_basic"><?php echo number_format((float) $invoice['basic_value'], 2); ?></td>
              </tr>
              <tr>
                <td>GST (<?php echo htmlspecialchars($invoice['gst_type'] ?: '-'); ?>)</td>
                <td class="text-end" id="boe_gst"><?php echo number_format((float) $invoice['gst_total'], 2); ?></td>
              </tr>
              <tr>
                <td>Total Bill (Incl GST)</td>
                <td class="text-end" id="boe_net"><?php echo number_format((float) $invoice['net_sales_value_1'], 2); ?></td>
              </tr>
              <tr>
                <td>Other Charges</td>
                <td class="text-end" id="boe_charges" data-value="<?php echo (float) ($invoice['other_charges_amount'] ?? 0); ?>"><?php echo number_format((float) ($invoice['other_charges_amount'] ?? 0), 2); ?></td>
              </tr>
              <tr>
                <td>Round Off</td>
                <td class="text-end" id="boe_round" data-value="<?php echo (float) ($invoice['round_of'] ?? 0); ?>"><?php echo number_format((float) ($invoice['round_of'] ?? 0), 2); ?></td>
              </tr>
              <tr>
                <td><b>Grand Total</b></td>
                <td class="text-end"><b id="boe_grand"><?php echo number_format((float) $invoice['grand_total'], 2); ?></b></td>
              </tr>
            </table>
          </div>
        </div>

        <button type="submit" class="btn btn-primary mt-1 btn_verify" name="btn_verify">Update Amount</button>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script>
function boeMoney(value) {
  return (Number(value) || 0).toFixed(2);
}

function recalculateBlackOrderAmounts() {
  var basic = 0;
  var gst = 0;
  $('.boe-row').each(function() {
    var qty = parseFloat($(this).data('qty')) || 0;
    var gstPer = parseFloat($(this).data('gst')) || 0;
    var amount = parseFloat($(this).find('.boe-amount').val()) || 0;
    var total = qty * amount;
    var gstAmt = (total * gstPer) / 100;
    $(this).find('.boe-total').text(boeMoney(total));
    $(this).find('.boe-gst').text(boeMoney(gstAmt));
    $(this).find('.boe-incl').text(boeMoney(total + gstAmt));
    basic += total;
    gst += gstAmt;
  });
  var charges = parseFloat($('#boe_charges').data('value')) || 0;
  var roundOff = parseFloat($('#boe_round').data('value')) || 0;
  var net = basic + gst;
  $('#boe_basic').text(boeMoney(basic));
  $('#boe_gst').text(boeMoney(gst));
  $('#boe_net').text(boeMoney(net));
  $('#boe_grand').text(boeMoney(net + charges + roundOff));
}

$(document).on('input', '.boe-amount', recalculateBlackOrderAmounts);
</script>
