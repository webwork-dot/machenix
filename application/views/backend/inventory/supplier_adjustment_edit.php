<?php
$row = $data ?? [];
$id = $id ?? 0;
?>

<style>
  .ledger-card-shell {
    border: 1px solid #e8eaed;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    background: #ffffff;
  }
  .card-soft-header { background: #fafbfc; border-bottom: 1px solid #f0f2f5; }
  .fs-10 { font-size: 10px; }
  .fs-11 { font-size: 11px; }
  .fs-12 { font-size: 12px; }
  .fs-13 { font-size: 13px; }
  .fs-14 { font-size: 14px; }
  .fs-15 { font-size: 15px; }
  .supplier-main-text { color: #111827; }
  .supplier-soft-text { color: #1f2937; }
  .mono-amount { font-family: "DM Mono", monospace; }
  .summary-pill {
    font-size: 11px;
    font-weight: 500;
    color: #4b5563;
    padding: 3px 10px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
  }
  .balance-pill {
    font-size: 11px;
    font-weight: 700;
    border-radius: 20px;
    padding: 3px 12px;
  }
  .balance-pill-due { color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; }
  .balance-pill-credit { color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; }
  .ledger-table thead th {
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: #f9fafb;
    border-bottom: 1px solid #e8eaed;
    color: #374151;
    white-space: nowrap;
  }
  .ledger-row { border-bottom: 1px solid #f3f4f6; }
  .ledger-row-purchase { background: #ffffff; }
  .ledger-row-purchase:hover { background: #f9fafb; }
  .ledger-row-payment { background: #f0fdf4; }
  .ledger-row-payment:hover { background: #dcfce7; }
  .ledger-row-adj-plus { background: #eff6ff; }
  .ledger-row-adj-plus:hover { background: #dbeafe; }
  .ledger-row-adj-minus { background: #fff5f5; }
  .ledger-row-adj-minus:hover { background: #fee2e2; }
  .type-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.4px;
    border-radius: 4px;
    padding: 2px 7px;
    text-transform: uppercase;
  }
  .type-badge-po { color: #7c3aed; background: #f5f3ff; border: 1px solid #ddd6fe; }
  .type-badge-payment { color: #0891b2; background: #ecfeff; border: 1px solid #a5f3fc; }
  .type-badge-adj-plus { color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; }
  .type-badge-adj-minus { color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; }
  .amount-positive { color: #dc2626; font-weight: 600; }
  .amount-negative { color: #16a34a; font-weight: 600; }
  .tfoot-border-top { border-top: 2px solid #e8eaed; }
  .balance-row-due { background: #fff5f5; }
  .balance-row-credit { background: #f0fdf4; }
  .balance-text-due { color: #dc2626; }
  .balance-text-credit { color: #16a34a; }
</style>

<div class="row">
  <div class="col-12">
    <!-- Compact Edit Adjustment Form Card -->
    <div class="card mb-2">
      <div class="card-body py-2">
        <form action="<?= base_url('inventory/supplier-adjustment/edit_post/' . $id) ?>" method="post" id="adjustmentForm">
          
          <div class="row g-2">
            <!-- Select Supplier -->
            <div class="col-md-6 mb-2">
              <label for="supplier_id" class="form-label fw-semibold fs-12 mb-1">Select Supplier <span class="text-danger">*</span></label>
              <select name="supplier_id" id="supplier_id" class="form-select select2" required>
                <option value="">-- Select Supplier --</option>
                <?php if (!empty($suppliers)): ?>
                  <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= $sup['id'] ?>" <?= (isset($row['supplier_id']) && $row['supplier_id'] == $sup['id']) ? 'selected' : '' ?>>
                      <?= html_escape($sup['name']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <!-- Batch Number -->
            <div class="col-md-6 mb-2">
              <label for="batch_no" class="form-label fw-semibold fs-12 mb-1">Batch Number</label>
              <select name="batch_no" id="batch_no" class="form-select select2">
                <option value="">-- Select Batch Number --</option>
                <?php if (!empty($batches)): ?>
                  <?php foreach ($batches as $b): ?>
                    <option value="<?= html_escape($b['voucher_no']) ?>" <?= (isset($row['batch_no']) && $row['batch_no'] == $b['voucher_no']) ? 'selected' : '' ?>>
                      <?= html_escape($b['voucher_no']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <div class="row g-2">
            <!-- Date -->
            <div class="col-md-4 mb-2">
              <label for="date" class="form-label fw-semibold fs-12 mb-1">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" id="date" class="form-control" value="<?= html_escape($row['date'] ?? date('Y-m-d')) ?>" required>
            </div>

            <!-- Amount Type -->
            <div class="col-md-4 mb-2">
              <label for="amt_type" class="form-label fw-semibold fs-12 mb-1">Amount Type <span class="text-danger">*</span></label>
              <select name="amt_type" id="amt_type" class="form-select" required>
                <option value="plus" <?= (isset($row['amt_type']) && $row['amt_type'] == 'plus') ? 'selected' : '' ?>>Plus (+)</option>
                <option value="minus" <?= (isset($row['amt_type']) && $row['amt_type'] == 'minus') ? 'selected' : '' ?>>Minus (-)</option>
              </select>
            </div>

            <!-- Type -->
            <div class="col-md-4 mb-2">
              <label for="type" class="form-label fw-semibold fs-12 mb-1">Type <span class="text-danger">*</span></label>
              <select name="type" id="type" class="form-select" required>
                <option value="unofficial" <?= (isset($row['type']) && $row['type'] == 'unofficial') ? 'selected' : '' ?>>Unofficial</option>
                <option value="official" <?= (isset($row['type']) && $row['type'] == 'official') ? 'selected' : '' ?>>Official</option>
              </select>
            </div>
          </div>

          <div class="row g-2">
            <!-- Amounts (USD, RMB, INR) -->
            <div class="col-md-4 mb-2" id="usd_col">
              <label for="usd" class="form-label fw-semibold fs-12 mb-1">Amount (USD)</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.00001" name="usd" id="usd" class="form-control amount-field" placeholder="0.00" value="<?= html_escape($row['usd'] ?? '0.00') ?>">
              </div>
            </div>

            <div class="col-md-4 mb-2" id="rmb_col">
              <label for="rmb" class="form-label fw-semibold fs-12 mb-1">Amount (RMB)</label>
              <div class="input-group">
                <span class="input-group-text">¥</span>
                <input type="number" step="0.00001" name="rmb" id="rmb" class="form-control amount-field" placeholder="0.00" value="<?= html_escape($row['rmb'] ?? '0.00') ?>">
              </div>
            </div>

            <div class="col-md-4 mb-2" id="inr_col">
              <label for="inr" class="form-label fw-semibold fs-12 mb-1">Amount (INR)</label>
              <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="number" step="0.00001" name="inr" id="inr" class="form-control amount-field" placeholder="0.00" value="<?= html_escape($row['inr'] ?? '0.00') ?>">
              </div>
            </div>
          </div>

          <!-- Remark -->
          <div class="row g-2">
            <div class="col-12 mb-2">
              <label for="remark" class="form-label fw-semibold fs-12 mb-1">Remark</label>
              <textarea name="remark" id="remark" class="form-control" rows="2" placeholder="Enter remarks..."><?= html_escape($row['remark'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end mb-1">
            <button type="submit" class="btn btn-primary px-4 py-1">
              <i class="feather icon-check"></i> Update
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Unified Single Batch Ledger & Outstanding Difference Table -->
    <div id="batch_summary_wrapper" style="display: none;">
      <div class="ledger-card-shell mb-4">
        
        <!-- Header with Summary Pills -->
        <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-bold supplier-main-text fs-13"><i class="feather icon-layers me-1 text-primary"></i> Batch Ledger: <span id="lbl_batch_no"></span></span>
            <span id="selected_type_badge" class="type-badge type-badge-po"></span>
          </div>
          
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="summary-pill">Batch: <strong class="supplier-soft-text mono-amount" id="hdr_batch_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Payments: <strong class="text-success mono-amount" id="hdr_pay_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Adjustments: <strong class="text-info mono-amount" id="hdr_adj_inr">₹ 0.00</strong></div>
            <div class="balance-pill balance-pill-due" id="hdr_bal_pill">Bal: ₹ 0.00</div>
          </div>
        </div>

        <!-- Single Unified Table (Batch -> Payments -> Adjustments) -->
        <div class="table-responsive">
          <table class="table table-borderless mb-0 align-middle ledger-table fs-12">
            <thead>
              <tr>
                <th class="text-start px-3 py-2">Date</th>
                <th class="text-start px-2 py-2">Type</th>
                <th class="text-start px-2 py-2">Detail / Remark</th>
                <th class="text-end px-2 py-2 rmb-col">RMB</th>
                <th class="text-end px-2 py-2">USD</th>
                <th class="text-end px-2 py-2">INR</th>
                <th class="text-start px-3 py-2">Added By</th>
              </tr>
            </thead>
            <tbody id="tbl_ledger_body">
              <!-- Single Ledger Rows dynamically generated here -->
            </tbody>
            <tfoot>
              <tr class="balance-row-due tfoot-border-top fw-bold fs-12" id="tfoot_balance_row">
                <td colspan="3" class="px-3 py-2 text-end supplier-main-text">Net Batch Outstanding Difference</td>
                <td class="px-2 py-2 text-end mono-amount rmb-col" id="ftr_bal_rmb">¥ 0.00</td>
                <td class="px-2 py-2 text-end mono-amount" id="ftr_bal_usd">$ 0.00</td>
                <td class="px-2 py-2 text-end mono-amount" id="ftr_bal_inr">₹ 0.00</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
    </div>

  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
  var current_adj_id = <?= intval($id) ?>;

  handleTypeVisibility();

  // Initial load summary if supplier and batch are set
  loadBatchLedgerSummary();

  // Type change handler
  $('#type').on('change', function() {
    handleTypeVisibility();
    loadBatchLedgerSummary();
  });

  function handleTypeVisibility() {
    var type = $('#type').val();
    if (type === 'official') {
      $('#rmb_col').hide();
      $('#rmb').val('0.00');
      $('#usd_col').removeClass('col-md-4').addClass('col-md-6');
      $('#inr_col').removeClass('col-md-4').addClass('col-md-6');
      $('.rmb-col').hide();
    } else {
      $('#rmb_col').show();
      $('#usd_col').removeClass('col-md-6').addClass('col-md-4');
      $('#inr_col').removeClass('col-md-6').addClass('col-md-4');
      $('.rmb-col').show();
    }
  }

  // Supplier Change -> Fetch Batches
  $('#supplier_id').on('change', function() {
    var supplier_id = $(this).val();
    $('#batch_no').html('<option value="">-- Loading Batches... --</option>').prop('disabled', true);
    $('#batch_summary_wrapper').hide();

    if (supplier_id) {
      $.ajax({
        url: '<?= base_url("inventory/get_batches_by_supplier_ajax") ?>',
        type: 'POST',
        data: { supplier_id: supplier_id },
        dataType: 'json',
        success: function(batches) {
          var options = '<option value="">-- Select Batch Number (Optional) --</option>';
          if (batches && batches.length > 0) {
            $.each(batches, function(i, item) {
              options += '<option value="' + item.voucher_no + '">' + item.voucher_no + '</option>';
            });
            $('#batch_no').html(options).prop('disabled', false);
          } else {
            $('#batch_no').html('<option value="">-- No batches found (Optional) --</option>').prop('disabled', false);
          }
        }
      });
    }
  });

  // Batch Change -> Fetch & Update Calculation Summary
  $('#batch_no').on('change', function() {
    loadBatchLedgerSummary();
  });

  function loadBatchLedgerSummary() {
    var supplier_id = $('#supplier_id').val();
    var batch_no    = $('#batch_no').val();
    var type        = $('#type').val();

    if (supplier_id && batch_no) {
      $('#selected_type_badge').text(type.toUpperCase());
      $('#lbl_batch_no').text(batch_no);

      $.ajax({
        url: '<?= base_url("inventory/get_supplier_batch_adjustment_info") ?>',
        type: 'POST',
        data: {
          supplier_id: supplier_id,
          batch_no: batch_no,
          type: type,
          current_adj_id: current_adj_id
        },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data) {
            var d = res.data;
            var rowsHtml = '';
            var showRmb = (type !== 'official');

            // 1. Batch Amount Row
            var b = d.batch_info;
            var bDate = b && b.po_date ? formatDate(b.po_date) : '—';
            rowsHtml += '<tr class="ledger-row ledger-row-purchase">' +
              '<td class="px-3 py-2 text-secondary fs-11 text-nowrap">' + bDate + '</td>' +
              '<td class="px-2 py-2"><span class="type-badge type-badge-po">Batch Amount</span></td>' +
              '<td class="px-2 py-2"><div class="fw-semibold supplier-soft-text fs-11">Batch #' + escapeHtml(batch_no) + '</div></td>' +
              (showRmb ? '<td class="px-2 py-2 text-end amount-positive rmb-col">+ ' + formatMoney(d.batch_amount.rmb) + '</td>' : '') +
              '<td class="px-2 py-2 text-end amount-positive">+ ' + formatMoney(d.batch_amount.usd) + '</td>' +
              '<td class="px-2 py-2 text-end amount-positive">+ ' + formatMoney(d.batch_amount.inr) + '</td>' +
              '<td class="px-3 py-2 text-muted fs-10 text-nowrap">—</td>' +
            '</tr>';

            // 2. Payment Entries Rows (payments reduce balance: minus)
            if (d.payments && d.payments.length > 0) {
              $.each(d.payments, function(i, p) {
                var pDate = p.payment_date ? formatDate(p.payment_date) : '—';
                var pDetail = (p.invoice_no ? 'Inv: ' + p.invoice_no + ' ' : '') + (p.bank_account_name ? '(' + p.bank_account_name + ')' : '');
                rowsHtml += '<tr class="ledger-row ledger-row-payment">' +
                  '<td class="px-3 py-2 text-secondary fs-11 text-nowrap">' + pDate + '</td>' +
                  '<td class="px-2 py-2"><span class="type-badge type-badge-payment">Payment (' + escapeHtml(p.payment_type) + ')</span></td>' +
                  '<td class="px-2 py-2"><div class="supplier-soft-text fs-11">' + escapeHtml(pDetail) + '</div></td>' +
                  (showRmb ? '<td class="px-2 py-2 text-end amount-negative rmb-col">− ' + formatMoney(p.amount_rmb) + '</td>' : '') +
                  '<td class="px-2 py-2 text-end amount-negative">− ' + formatMoney(p.amount_dollar) + '</td>' +
                  '<td class="px-2 py-2 text-end amount-negative">− ' + formatMoney(p.amount_rs) + '</td>' +
                  '<td class="px-3 py-2 text-muted fs-10 text-nowrap">' + escapeHtml(p.added_by_name ? p.added_by_name : '—') + '</td>' +
                '</tr>';
              });
            }

            // 3. Adjustment Entries Rows (excluding current_adj_id)
            if (d.adjustments && d.adjustments.length > 0) {
              $.each(d.adjustments, function(i, a) {
                var aDate = a.date ? formatDate(a.date) : '—';
                var isPlus = (a.amt_type === 'plus');
                var rowCls = isPlus ? 'ledger-row-adj-plus' : 'ledger-row-adj-minus';
                var badgeCls = isPlus ? 'type-badge-adj-plus' : 'type-badge-adj-minus';
                var amtCls = isPlus ? 'amount-positive' : 'amount-negative';
                var sign = isPlus ? '+' : '−';

                rowsHtml += '<tr class="ledger-row ' + rowCls + '">' +
                  '<td class="px-3 py-2 text-secondary fs-11 text-nowrap">' + aDate + '</td>' +
                  '<td class="px-2 py-2"><span class="type-badge ' + badgeCls + '">Adjustment (' + a.amt_type.toUpperCase() + ')</span></td>' +
                  '<td class="px-2 py-2"><div class="supplier-soft-text fs-11">' + escapeHtml(a.remark ? a.remark : '—') + '</div></td>' +
                  (showRmb ? '<td class="px-2 py-2 text-end ' + amtCls + ' rmb-col">' + sign + ' ' + formatMoney(a.rmb) + '</td>' : '') +
                  '<td class="px-2 py-2 text-end ' + amtCls + '">' + sign + ' ' + formatMoney(a.usd) + '</td>' +
                  '<td class="px-2 py-2 text-end ' + amtCls + '">' + sign + ' ' + formatMoney(a.inr) + '</td>' +
                  '<td class="px-3 py-2 text-muted fs-10 text-nowrap">' + escapeHtml(a.added_by_name ? a.added_by_name : '—') + '</td>' +
                '</tr>';
              });
            }

            $('#tbl_ledger_body').html(rowsHtml);

            // Top Header Pills
            var netAdjInr = d.adj_totals.plus.inr - d.adj_totals.minus.inr;
            $('#hdr_batch_inr').text('₹ ' + formatMoney(d.batch_amount.inr));
            $('#hdr_pay_inr').text('₹ ' + formatMoney(d.payment_totals.inr));
            $('#hdr_adj_inr').text('₹ ' + formatMoney(netAdjInr));

            var isDue = (d.net_difference.inr > 0);
            $('#hdr_bal_pill')
              .removeClass('balance-pill-due balance-pill-credit')
              .addClass(isDue ? 'balance-pill-due' : 'balance-pill-credit')
              .text('Bal: ₹ ' + formatMoney(d.net_difference.inr));

            // Footer Net Difference
            $('#tfoot_balance_row')
              .removeClass('balance-row-due balance-row-credit')
              .addClass(isDue ? 'balance-row-due' : 'balance-row-credit');

            var amtCls = isDue ? 'balance-text-due' : 'balance-text-credit';
            $('#ftr_bal_rmb').attr('class', 'px-2 py-2 text-end mono-amount rmb-col ' + amtCls).text('¥ ' + formatMoney(d.net_difference.rmb));
            $('#ftr_bal_usd').attr('class', 'px-2 py-2 text-end mono-amount ' + amtCls).text('$ ' + formatMoney(d.net_difference.usd));
            $('#ftr_bal_inr').attr('class', 'px-2 py-2 text-end mono-amount ' + amtCls).text('₹ ' + formatMoney(d.net_difference.inr));

            $('.rmb-col').toggle(showRmb);

            $('#batch_summary_wrapper').slideDown();
          }
        }
      });
    } else {
      $('#batch_summary_wrapper').slideUp();
    }
  }

  function formatMoney(num) {
    var val = parseFloat(num);
    if (isNaN(val)) return '0.00';
    return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function formatDate(dStr) {
    if (!dStr) return '—';
    var dt = new Date(dStr);
    if (isNaN(dt.getTime())) return dStr;
    return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' });
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

});
</script>
