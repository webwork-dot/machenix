<style>
  .invoice-edit-card {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
  }

  .meta-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
  }

  .meta-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .meta-val {
    font-size: 0.95rem;
    color: #1e293b;
    font-weight: 600;
  }

  .table-edit-invoice th {
    background: #f1f5f9;
    color: #334155;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 12px 10px;
    vertical-align: middle;
  }

  .table-edit-invoice td {
    padding: 10px 8px;
    vertical-align: middle;
  }

  .qty-input, .amount-input {
    min-width: 90px;
    font-weight: 600;
  }

  .qty-max-badge {
    font-size: 0.72rem;
    padding: 2px 6px;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 4px;
    display: inline-block;
    margin-top: 3px;
  }

  .summary-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
  }

  .summary-card-header {
    background: #f8fafc;
    padding: 12px 18px;
    font-weight: 700;
    font-size: 0.95rem;
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
  }

  .summary-card-body {
    padding: 16px 18px;
  }

  .summary-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 0.9rem;
    color: #475569;
  }

  .summary-line.grand-total {
    border-top: 2px dashed #cbd5e1;
    margin-top: 10px;
    padding-top: 12px;
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
  }

  .btn-remove-row {
    color: #ef4444;
    border: 1px solid #fecaca;
    background: #fef2f2;
    padding: 5px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  .btn-remove-row:hover {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card invoice-edit-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="card-title mb-0">
            <i class="feather icon-edit text-primary me-1"></i> Edit Sales Invoice: 
            <span class="text-primary"><?php echo htmlspecialchars($invoice_order['invoice_no'] ?? ''); ?></span>
          </h4>
          <small class="text-muted">Order Date: <?php echo date('d M, Y', strtotime($invoice_order['date'])); ?> | Invoice Date: <?php echo !empty($invoice_order['invoice_date']) ? date('d M, Y', strtotime($invoice_order['invoice_date'])) : '-'; ?></small>
        </div>
        <div>
          <a href="<?php echo base_url('inventory/sales-order?status=complete'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="feather icon-arrow-left"></i> Back to Complete Orders
          </a>
        </div>
      </div>

      <div class="card-body">
        <!-- Invoice Metadata Row -->
        <div class="meta-box">
          <div class="row">
            <div class="col-md-3 col-sm-6 mb-2">
              <div class="meta-title">Customer Name</div>
              <div class="meta-val"><?php echo htmlspecialchars($invoice_order['customer_name'] ?? '-'); ?></div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
              <div class="meta-title">Order Number</div>
              <div class="meta-val"><?php echo htmlspecialchars($invoice_order['order_no'] ?? '-'); ?></div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
              <div class="meta-title">Warehouse</div>
              <div class="meta-val"><?php echo htmlspecialchars($invoice_order['warehouse_name'] ?? '-'); ?></div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
              <div class="meta-title">GST Type</div>
              <div class="meta-val">
                <span class="badge bg-light-primary text-primary"><?php echo htmlspecialchars($invoice_order['gst_type'] ?? 'CGST/SGST'); ?></span>
              </div>
            </div>
          </div>
        </div>

        <div class="alert alert-primary mb-3" role="alert">
          <div class="alert-body d-flex align-items-center">
            <i class="feather icon-info me-2 font-medium-3"></i>
            <span>
              <strong>Note:</strong> You can modify product quantities and amounts or remove items. <strong>Quantities cannot be increased</strong> beyond the original invoice quantity. Any reduced quantity or removed product will automatically revert its allocated batch received quantity.
            </span>
          </div>
        </div>

        <form id="edit_sales_invoice_form" method="POST" action="<?php echo base_url('inventory/sales-invoice/edit_post/' . $invoice_order['id']); ?>">
          <input type="hidden" name="invoice_order_id" value="<?php echo $invoice_order['id']; ?>">
          <input type="hidden" id="gst_type" value="<?php echo htmlspecialchars($invoice_order['gst_type'] ?? 'CGST/SGST'); ?>">

          <div class="table-responsive mb-3">
            <table class="table table-bordered table-hover table-edit-invoice" id="invoice_items_table">
              <thead>
                <tr>
                  <th style="width: 40px;" class="text-center">#</th>
                  <th>Product Name</th>
                  <th style="width: 130px;" class="text-center">Batch No</th>
                  <th style="width: 100px;" class="text-center">Original Qty</th>
                  <th style="width: 130px;" class="text-center">Invoice Qty <span class="text-danger">*</span></th>
                  <th style="width: 130px;" class="text-center">Rate / Amount <span class="text-danger">*</span></th>
                  <th style="width: 120px;" class="text-end">Line Total (₹)</th>
                  <th style="width: 80px;" class="text-center">GST %</th>
                  <th style="width: 110px;" class="text-end">GST Amt (₹)</th>
                  <th style="width: 130px;" class="text-end">Final Total (₹)</th>
                  <th style="width: 60px;" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody id="invoice_items_tbody">
                <?php 
                  $row_index = 1;
                  foreach ($products as $p) { 
                    $iop_id   = $p['id'];
                    $orig_qty = (float)$p['qty'];
                    $amount   = (float)$p['amount'];
                    $gst_per  = (float)($p['gst'] ?? 0);
                    $line_total = $orig_qty * $amount;
                    $gst_amt  = ($line_total * $gst_per) / 100;
                    $final_total = $line_total + $gst_amt;
                ?>
                  <tr class="item-row" id="row_<?php echo $iop_id; ?>" data-row-id="<?php echo $iop_id; ?>">
                    <td class="text-center row-num"><?php echo $row_index++; ?></td>
                    <td>
                      <input type="hidden" name="invoice_product_id[]" value="<?php echo $iop_id; ?>">
                      <input type="hidden" name="product_id[]" value="<?php echo $p['product_id']; ?>">
                      <input type="hidden" name="batch_id[]" value="<?php echo $p['batch_id']; ?>">
                      <input type="hidden" class="orig-qty-hidden" value="<?php echo $orig_qty; ?>">
                      <input type="hidden" class="item-gst-per" value="<?php echo $gst_per; ?>">
                      
                      <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['product_name']); ?></div>
                      <?php if (!empty($p['item_code'])) { ?>
                        <small class="text-muted"><i class="feather icon-tag"></i> <?php echo htmlspecialchars($p['item_code']); ?></small>
                      <?php } ?>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-light-secondary text-dark"><?php echo !empty($p['batch_no']) ? htmlspecialchars($p['batch_no']) : '-'; ?></span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-secondary"><?php echo $orig_qty; ?></span>
                    </td>
                    <td class="text-center">
                      <input type="number" 
                             name="qty[]" 
                             class="form-control form-control-sm text-center qty-input" 
                             value="<?php echo $orig_qty; ?>" 
                             min="1" 
                             max="<?php echo $orig_qty; ?>" 
                             step="1" 
                             required
                             data-orig="<?php echo $orig_qty; ?>"
                             oninput="validateAndRecalculate(this)"
                             onchange="validateAndRecalculate(this)">
                      <div class="qty-max-badge">Max: <?php echo $orig_qty; ?></div>
                    </td>
                    <td class="text-center">
                      <input type="number" 
                             name="amount[]" 
                             class="form-control form-control-sm text-end amount-input" 
                             value="<?php echo number_format($amount, 2, '.', ''); ?>" 
                             min="0" 
                             step="0.01" 
                             required
                             oninput="recalculateRow(this)"
                             onchange="recalculateRow(this)">
                    </td>
                    <td class="text-end fw-semibold line-total-display">
                      <?php echo number_format($line_total, 2); ?>
                    </td>
                    <td class="text-center">
                      <?php echo $gst_per; ?>%
                    </td>
                    <td class="text-end line-gst-display">
                      <?php echo number_format($gst_amt, 2); ?>
                    </td>
                    <td class="text-end fw-bold text-dark line-final-display">
                      <?php echo number_format($final_total, 2); ?>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row" title="Remove Product" onclick="removeRow('<?php echo $iop_id; ?>')">
                        <i class="feather icon-trash-2"></i>
                      </button>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

          <!-- Bottom Summary and Notes Section -->
          <div class="row mt-3">
            <div class="col-md-7 mb-3">
              <div class="form-group mb-2">
                <label class="form-label fw-bold" for="narration">Narration</label>
                <textarea name="narration" id="narration" rows="2" class="form-control" placeholder="Add narration if any..."><?php echo htmlspecialchars($invoice_order['narration'] ?? ''); ?></textarea>
              </div>
              <div class="form-group mb-2">
                <label class="form-label fw-bold" for="remark">Remark</label>
                <textarea name="remark" id="remark" rows="2" class="form-control" placeholder="Add remark if any..."><?php echo htmlspecialchars($invoice_order['remark'] ?? ''); ?></textarea>
              </div>
            </div>

            <div class="col-md-5 mb-3">
              <div class="summary-card">
                <div class="summary-card-header d-flex justify-content-between align-items-center">
                  <span>Invoice Financial Summary</span>
                  <span class="badge bg-primary" id="items_count_badge"><?php echo count($products); ?> Items</span>
                </div>
                <div class="summary-card-body">
                  <div class="summary-line">
                    <span>Basic Value (Taxable):</span>
                    <strong id="display_basic_value">₹<?php echo number_format((float)($invoice_order['basic_value'] ?? 0), 2); ?></strong>
                  </div>

                  <?php if (($invoice_order['gst_type'] ?? '') == 'IGST') { ?>
                    <div class="summary-line">
                      <span>IGST Total:</span>
                      <strong id="display_igst">₹<?php echo number_format((float)($invoice_order['gst_total'] ?? 0), 2); ?></strong>
                    </div>
                  <?php } else { ?>
                    <div class="summary-line">
                      <span>CGST:</span>
                      <strong id="display_cgst">₹<?php echo number_format((float)($invoice_order['central_gst'] ?? 0), 2); ?></strong>
                    </div>
                    <div class="summary-line">
                      <span>SGST:</span>
                      <strong id="display_sgst">₹<?php echo number_format((float)($invoice_order['state_gst'] ?? 0), 2); ?></strong>
                    </div>
                  <?php } ?>

                  <div class="summary-line">
                    <span>GST Total:</span>
                    <strong id="display_gst_total">₹<?php echo number_format((float)($invoice_order['gst_total'] ?? 0), 2); ?></strong>
                  </div>

                  <div class="summary-line align-items-center">
                    <span>Round Off:</span>
                    <div style="width: 110px;">
                      <input type="number" 
                             name="round_of" 
                             id="round_of" 
                             class="form-control form-control-sm text-end" 
                             value="<?php echo number_format((float)($invoice_order['round_of'] ?? 0), 2, '.', ''); ?>" 
                             step="0.01" 
                             oninput="recalculateAll()" 
                             onchange="recalculateAll()">
                    </div>
                  </div>

                  <div class="summary-line grand-total">
                    <span>Grand Total:</span>
                    <span class="text-primary" id="display_grand_total">₹<?php echo number_format((float)($invoice_order['grand_total'] ?? 0), 2); ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-2">

          <div class="d-flex justify-content-end gap-2">
            <a href="<?php echo base_url('inventory/sales-order?status=complete'); ?>" class="btn btn-outline-secondary">
              <i class="feather icon-x me-1"></i> Cancel
            </a>
            <button type="submit" id="btn_submit_invoice" class="btn btn-primary">
              <i class="feather icon-check-circle me-1"></i> Update Sales Invoice
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function validateAndRecalculate(input) {
    var maxVal = parseFloat($(input).attr('max'));
    var minVal = parseFloat($(input).attr('min')) || 1;
    var currentVal = parseFloat($(input).val());

    if (isNaN(currentVal)) {
      return;
    }

    if (currentVal > maxVal) {
      Swal.fire({
        title: "Quantity Exceeded!",
        text: "You cannot increase the quantity above the original invoice quantity (" + maxVal + ").",
        icon: "warning",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: false
      });
      $(input).val(maxVal);
    } else if (currentVal < minVal) {
      Swal.fire({
        title: "Invalid Quantity!",
        text: "Quantity must be at least " + minVal + ". If you wish to remove this product, click the delete button.",
        icon: "warning",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: false
      });
      $(input).val(minVal);
    }

    recalculateRow(input);
  }

  function recalculateRow(input) {
    var row = $(input).closest('tr');
    var qty = parseFloat(row.find('.qty-input').val()) || 0;
    var amount = parseFloat(row.find('.amount-input').val()) || 0;
    var gstPer = parseFloat(row.find('.item-gst-per').val()) || 0;

    var lineTotal = qty * amount;
    var gstAmt = (lineTotal * gstPer) / 100;
    var finalTotal = lineTotal + gstAmt;

    row.find('.line-total-display').text(lineTotal.toFixed(2));
    row.find('.line-gst-display').text(gstAmt.toFixed(2));
    row.find('.line-final-display').text(finalTotal.toFixed(2));

    recalculateAll();
  }

  function removeRow(rowId) {
    var totalRows = $('#invoice_items_tbody tr.item-row').length;
    if (totalRows <= 1) {
      Swal.fire({
        title: "Cannot Remove Last Item!",
        text: "An invoice must contain at least one product. If you want to delete this invoice entirely, please use the Delete option from the Complete orders list.",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: false
      });
      return;
    }

    Swal.fire({
      title: "Remove Product?",
      text: "This product line will be removed from the invoice and its quantity will be reverted to the batch.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, remove it!",
      customClass: {
        confirmButton: "btn btn-primary",
        cancelButton: "btn btn-outline-danger ms-1"
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $('#row_' + rowId).remove();
        updateRowNumbers();
        recalculateAll();
      }
    });
  }

  function updateRowNumbers() {
    var idx = 1;
    $('#invoice_items_tbody tr.item-row').each(function() {
      $(this).find('.row-num').text(idx++);
    });
    $('#items_count_badge').text((idx - 1) + ' Items');
  }

  function recalculateAll() {
    var totalBasic = 0;
    var totalGst = 0;
    var totalGrand = 0;

    $('#invoice_items_tbody tr.item-row').each(function() {
      var qty = parseFloat($(this).find('.qty-input').val()) || 0;
      var amount = parseFloat($(this).find('.amount-input').val()) || 0;
      var gstPer = parseFloat($(this).find('.item-gst-per').val()) || 0;

      var lineTotal = qty * amount;
      var gstAmt = (lineTotal * gstPer) / 100;
      var finalTotal = lineTotal + gstAmt;

      totalBasic += lineTotal;
      totalGst += gstAmt;
      totalGrand += finalTotal;
    });

    var roundOf = parseFloat($('#round_of').val()) || 0;
    var grandTotal = totalGrand + roundOf;

    var gstType = $('#gst_type').val();

    $('#display_basic_value').text('₹' + totalBasic.toFixed(2));
    $('#display_gst_total').text('₹' + totalGst.toFixed(2));

    if (gstType === 'IGST') {
      $('#display_igst').text('₹' + totalGst.toFixed(2));
    } else {
      var halfGst = (totalGst / 2).toFixed(2);
      $('#display_cgst').text('₹' + halfGst);
      $('#display_sgst').text('₹' + halfGst);
    }

    $('#display_grand_total').text('₹' + grandTotal.toFixed(2));
  }

  $(document).ready(function() {
    $('#edit_sales_invoice_form').on('submit', function(e) {
      e.preventDefault();

      var rowCount = $('#invoice_items_tbody tr.item-row').length;
      if (rowCount === 0) {
        Swal.fire({
          title: "Error!",
          text: "Please keep at least one product in the invoice.",
          icon: "error",
          customClass: { confirmButton: "btn btn-primary" },
          buttonsStyling: false
        });
        return;
      }

      var hasInvalidQty = false;
      var invalidMsg = '';
      $('#invoice_items_tbody tr.item-row').each(function() {
        var input = $(this).find('.qty-input');
        var val = parseFloat(input.val());
        var max = parseFloat(input.attr('max'));
        var min = parseFloat(input.attr('min')) || 1;

        if (isNaN(val) || val < min) {
          hasInvalidQty = true;
          invalidMsg = 'Quantity must be at least ' + min;
          return false;
        }
        if (val > max) {
          hasInvalidQty = true;
          invalidMsg = 'Quantity cannot exceed ' + max;
          return false;
        }
      });

      if (hasInvalidQty) {
        Swal.fire({
          title: "Invalid Quantity!",
          text: invalidMsg,
          icon: "error",
          customClass: { confirmButton: "btn btn-primary" },
          buttonsStyling: false
        });
        return;
      }

      $('.loader').show();
      $('#btn_submit_invoice').prop('disabled', true);

      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
          $('.loader').hide();
          $('#btn_submit_invoice').prop('disabled', false);
          if (res.status == 200 || res.status == '200') {
            Swal.fire({
              title: "Success!",
              text: res.message,
              icon: "success",
              customClass: { confirmButton: "btn btn-primary" },
              buttonsStyling: false
            }).then(() => {
              window.location.href = res.url || "<?php echo base_url('inventory/sales-order?status=complete'); ?>";
            });
          } else {
            Swal.fire({
              title: "Error!",
              text: res.message,
              icon: "error",
              customClass: { confirmButton: "btn btn-primary" },
              buttonsStyling: false
            });
          }
        },
        error: function() {
          $('.loader').hide();
          $('#btn_submit_invoice').prop('disabled', false);
          Swal.fire({
            title: "Error!",
            text: "An error occurred while saving the invoice changes.",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
          });
        }
      });
    });
  });
</script>
