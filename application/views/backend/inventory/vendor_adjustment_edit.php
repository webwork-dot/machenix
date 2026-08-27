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
  .fs-16 { font-size: 16px; }
  .vendor-main-text { color: #111827; }
  .vendor-soft-text { color: #1f2937; }
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
</style>

<div class="row">
  <div class="col-12">
    <!-- Compact Edit Adjustment Form Card -->
    <div class="card mb-2">
      <div class="card-body py-2">
        <form action="<?= base_url('inventory/vendor-adjustment/edit_post/' . $id) ?>" method="post" id="adjustmentForm">
          
          <div class="row g-2">
            <!-- Select Vendor -->
            <div class="col-md-12 mb-2">
              <label for="vendor_id" class="form-label fw-semibold fs-12 mb-1">Select Vendor <span class="text-danger">*</span></label>
              <select name="vendor_id" id="vendor_id" class="form-select select2" required>
                <option value="">-- Select Vendor --</option>
                <?php if (!empty($vendors)): ?>
                  <?php foreach ($vendors as $v): 
                    $selected_vendor_id = $row['vendor_id'] ?? $row['supplier_id'] ?? 0;
                  ?>
                    <option value="<?= $v['id'] ?>" <?= ($selected_vendor_id == $v['id']) ? 'selected' : '' ?>>
                      <?= html_escape($v['name']) ?>
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

    <!-- Vendor Outstanding / Balance Summary Card -->
    <div id="vendor_summary_wrapper" style="display: none;">
      <div class="ledger-card-shell mb-4">
        
        <!-- Header with Summary Pills -->
        <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-bold vendor-main-text fs-13"><i class="feather icon-user me-1 text-primary"></i> Vendor Balance: <span id="lbl_vendor_name"></span></span>
          </div>
          
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="summary-pill" id="ven_opening_pill">Opening: <strong class="vendor-soft-text mono-amount" id="ven_hdr_open_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Expenses: <strong class="vendor-soft-text mono-amount" id="ven_hdr_exp_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Payments: <strong class="text-success mono-amount" id="ven_hdr_pay_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Adjustments: <strong class="text-info mono-amount" id="ven_hdr_adj_inr">₹ 0.00</strong></div>
            <div class="balance-pill balance-pill-due" id="ven_hdr_bal_pill">Outstanding: ₹ 0.00</div>
          </div>
        </div>

        <!-- Total Outstanding Balance Summary Boxes -->
        <div class="p-3 bg-white">
          <div class="row g-2">
            <div class="col-md-4 col-12" id="ven_bal_box_inr">
              <div class="p-2 rounded border" style="background-color: #fafbfc;">
                <div class="text-muted fs-11 fw-bold text-uppercase mb-1">Total Outstanding (INR)</div>
                <div class="fs-16 fw-bold mono-amount" id="ven_bal_inr">₹ 0.00</div>
              </div>
            </div>
            <div class="col-md-4 col-12" id="ven_bal_box_usd">
              <div class="p-2 rounded border" style="background-color: #fafbfc;">
                <div class="text-muted fs-11 fw-bold text-uppercase mb-1">Total Outstanding (USD)</div>
                <div class="fs-16 fw-bold mono-amount" id="ven_bal_usd">$ 0.00</div>
              </div>
            </div>
            <div class="col-md-4 col-12 ven-rmb-col" id="ven_bal_box_rmb">
              <div class="p-2 rounded border" style="background-color: #fafbfc;">
                <div class="text-muted fs-11 fw-bold text-uppercase mb-1">Total Outstanding (RMB)</div>
                <div class="fs-16 fw-bold mono-amount" id="ven_bal_rmb">¥ 0.00</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    var current_adj_id = <?= intval($id) ?>;

    // Initial load summary
    loadVendorSummary();

    // Vendor Change -> Load Summary
    $('#vendor_id').on('change', function() {
      loadVendorSummary();
    });

    function loadVendorSummary() {
      var vendor_id = $('#vendor_id').val();

      if (!vendor_id) {
        $('#vendor_summary_wrapper').slideUp();
        return;
      }

      $.ajax({
        url: '<?= base_url("inventory/get_vendor_ledger_summary_ajax") ?>',
        type: 'POST',
        data: {
          vendor_id: vendor_id,
          current_adj_id: current_adj_id
        },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data) {
            var d = res.data;

            $('#lbl_vendor_name').text(d.vendor_name);

            $('#ven_hdr_open_inr').text('₹ ' + formatMoney(d.opening.inr));
            $('#ven_hdr_exp_inr').text('₹ ' + formatMoney(d.totals.expenses.inr));
            $('#ven_hdr_pay_inr').text('₹ ' + formatMoney(d.totals.payment.inr));
            $('#ven_hdr_adj_inr').text('₹ ' + formatMoney(d.net_adj_inr));

            var isDueInr = (d.balance.inr > 0);
            var isDueUsd = (d.balance.usd > 0);
            var isDueRmb = (d.balance.rmb > 0);

            $('#ven_bal_inr')
              .attr('class', 'fs-16 fw-bold mono-amount ' + (isDueInr ? 'text-danger' : 'text-success'))
              .text('₹ ' + formatMoney(d.balance.inr));

            $('#ven_bal_usd')
              .attr('class', 'fs-16 fw-bold mono-amount ' + (isDueUsd ? 'text-danger' : 'text-success'))
              .text('$ ' + formatMoney(d.balance.usd));

            $('#ven_bal_rmb')
              .attr('class', 'fs-16 fw-bold mono-amount ' + (isDueRmb ? 'text-danger' : 'text-success'))
              .text('¥ ' + formatMoney(d.balance.rmb));

            var isOverallDue = (d.balance.inr > 0 || d.balance.usd > 0 || d.balance.rmb > 0);
            $('#ven_hdr_bal_pill')
              .removeClass('balance-pill-due balance-pill-credit')
              .addClass(isOverallDue ? 'balance-pill-due' : 'balance-pill-credit')
              .text('Outstanding: ₹ ' + formatMoney(d.balance.inr));

            $('#vendor_summary_wrapper').slideDown();
          }
        }
      });
    }

    function formatMoney(num) {
      var val = parseFloat(num);
      if (isNaN(val)) return '0.00';
      return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

  });
</script>
