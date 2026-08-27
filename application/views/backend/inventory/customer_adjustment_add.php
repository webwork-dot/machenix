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
  .customer-main-text { color: #111827; }
  .customer-soft-text { color: #1f2937; }
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
</style>

<div class="row">
  <div class="col-12">
    <!-- Compact Add Customer Adjustment Form Card -->
    <div class="card mb-2">
      <div class="card-body py-2">
        <form action="<?= base_url('inventory/customer-adjustment/add_post') ?>" method="post" id="adjustmentForm">
          
          <div class="row g-2">
            <!-- Select Customer -->
            <div class="col-md-12 mb-2">
              <label for="customer_id" class="form-label fw-semibold fs-12 mb-1">Select Customer <span class="text-danger">*</span></label>
              <select name="customer_id" id="customer_id" class="form-select select2" required>
                <option value="">-- Select Customer --</option>
                <?php if (!empty($customers)): ?>
                  <?php foreach ($customers as $c): 
                    $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
                  ?>
                    <option value="<?= $c['id'] ?>"><?= html_escape($displayName) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <div class="row g-2">
            <!-- Date -->
            <div class="col-md-4 mb-2">
              <label for="date" class="form-label fw-semibold fs-12 mb-1">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Amount Type -->
            <div class="col-md-4 mb-2">
              <label for="amt_type" class="form-label fw-semibold fs-12 mb-1">Amount Type <span class="text-danger">*</span></label>
              <select name="amt_type" id="amt_type" class="form-select" required>
                <option value="plus" selected>Plus (+)</option>
                <option value="minus">Minus (-)</option>
              </select>
            </div>

            <!-- Type -->
            <div class="col-md-4 mb-2">
              <label for="type" class="form-label fw-semibold fs-12 mb-1">Type <span class="text-danger">*</span></label>
              <select name="type" id="type" class="form-select" required>
                <option value="unofficial" selected>Unofficial</option>
                <option value="official">Official</option>
              </select>
            </div>
          </div>

          <div class="row g-2">
            <!-- Amount (INR) -->
            <div class="col-md-12 mb-2" id="inr_col">
              <label for="inr" class="form-label fw-semibold fs-12 mb-1">Amount (INR) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="number" step="0.01" name="inr" id="inr" class="form-control amount-field" placeholder="0.00" value="0.00" required>
              </div>
            </div>
          </div>

          <!-- Remark -->
          <div class="row g-2">
            <div class="col-12 mb-2">
              <label for="remark" class="form-label fw-semibold fs-12 mb-1">Remark</label>
              <textarea name="remark" id="remark" class="form-control" rows="2" placeholder="Enter remarks..."></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end mb-1">
            <button type="submit" class="btn btn-primary px-4 py-1">
              <i class="feather icon-check"></i> Submit
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Customer Outstanding / Balance Summary Card (Shown when Customer is selected) -->
    <div id="customer_summary_wrapper" style="display: none;">
      <div class="ledger-card-shell mb-4">
        
        <!-- Header with Summary Pills -->
        <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-bold customer-main-text fs-13"><i class="feather icon-user me-1 text-primary"></i> Customer Balance: <span id="lbl_customer_name"></span></span>
          </div>
          
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="summary-pill" id="cust_opening_pill">Opening: <strong class="customer-soft-text mono-amount" id="cust_hdr_open_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Sales: <strong class="customer-soft-text mono-amount" id="cust_hdr_sales_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Payments: <strong class="text-success mono-amount" id="cust_hdr_pay_inr">₹ 0.00</strong></div>
            <div class="summary-pill">Adjustments: <strong class="text-info mono-amount" id="cust_hdr_adj_inr">₹ 0.00</strong></div>
            <div class="balance-pill balance-pill-due" id="cust_hdr_bal_pill">Outstanding: ₹ 0.00</div>
          </div>
        </div>

        <!-- Total Outstanding Balance Summary Box -->
        <div class="p-3 bg-white">
          <div class="row g-2">
            <div class="col-12" id="cust_bal_box_inr">
              <div class="p-2 rounded border" style="background-color: #fafbfc;">
                <div class="text-muted fs-11 fw-bold text-uppercase mb-1">Total Outstanding (INR)</div>
                <div class="fs-16 fw-bold mono-amount" id="cust_bal_inr">₹ 0.00</div>
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

  // Customer Change -> Load Summary
  $('#customer_id').on('change', function() {
    loadCustomerSummary();
  });

  function loadCustomerSummary() {
    var customer_id = $('#customer_id').val();

    if (!customer_id) {
      $('#customer_summary_wrapper').slideUp();
      return;
    }

    $.ajax({
      url: '<?= base_url("inventory/get_customer_ledger_summary_ajax") ?>',
      type: 'POST',
      data: {
        customer_id: customer_id
      },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          var d = res.data;

          $('#lbl_customer_name').text(d.customer_name);

          $('#cust_hdr_open_inr').text('₹ ' + formatMoney(d.opening));
          $('#cust_hdr_sales_inr').text('₹ ' + formatMoney(d.totals.sales));
          $('#cust_hdr_pay_inr').text('₹ ' + formatMoney(d.totals.payment));
          $('#cust_hdr_adj_inr').text('₹ ' + formatMoney(d.net_adj));

          var isDue = (d.balance > 0);

          $('#cust_bal_inr')
            .attr('class', 'fs-16 fw-bold mono-amount ' + (isDue ? 'text-danger' : 'text-success'))
            .text('₹ ' + formatMoney(d.balance));

          $('#cust_hdr_bal_pill')
            .removeClass('balance-pill-due balance-pill-credit')
            .addClass(isDue ? 'balance-pill-due' : 'balance-pill-credit')
            .text('Outstanding: ₹ ' + formatMoney(d.balance));

          $('#customer_summary_wrapper').slideDown();
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
