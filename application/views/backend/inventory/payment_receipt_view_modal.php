<?php
  // Get Customer Payment ID from param2
  $payment_id = $param2;

  // Get Payment details
  $payment = $this->db->query("SELECT * FROM customer_payment WHERE id = '$payment_id'")->row_array();

  if (empty($payment)) {
    echo '<div class="alert alert-danger p-2">Payment record not found.</div>';
    return;
  }

  // Get Customer details
  $customer = [];
  if (!empty($payment['customer_id'])) {
    $customer = $this->db->query("SELECT * FROM customer WHERE id = '{$payment['customer_id']}'")->row_array();
  }

  // Get Bank Account details if applicable
  $bank_info = null;
  if ($payment['payment_method'] == 'bank' && !empty($payment['company_bank_account'])) {
    $bank_info = $this->db->query("SELECT * FROM bank_accounts WHERE id = '{$payment['company_bank_account']}'")->row_array();
  }

  // Get Allocated Invoices
  $payment_records = $this->db->query("SELECT * FROM customer_payment_record WHERE payment_id = '$payment_id' AND is_deleted = 0 ORDER BY id ASC")->result_array();

  // Get Generated Credit if any
  $credit_record = $this->db->query("SELECT * FROM customer_credit WHERE payment_id = '$payment_id'")->row_array();

  // Status variables
  $is_approved = (!empty($payment['is_approved']) && $payment['is_approved'] == 1);
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

  .payment-view-modal {
    padding: 8px;
    font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #1e293b;
  }

  .meta-dashboard {
    background: #ffffff;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  }

  .meta-dashboard-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
  }

  .meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px 18px;
  }

  .meta-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }

  .meta-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.75px;
    color: #64748b;
    font-weight: 700;
  }

  .meta-value {
    font-size: 0.93rem;
    font-weight: 600;
    color: #0f172a;
  }

  .kpi-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
  }

  .kpi-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 14px 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  }

  .kpi-card .kpi-title {
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
  }

  .kpi-card .kpi-value {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
  }

  .kpi-card.highlight-primary {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #bfdbfe;
  }
  .kpi-card.highlight-primary .kpi-value {
    color: #1d4ed8;
  }

  .kpi-card.highlight-success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-color: #bbf7d0;
  }
  .kpi-card.highlight-success .kpi-value {
    color: #15803d;
  }

  .kpi-card.highlight-warning {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-color: #fde68a;
  }
  .kpi-card.highlight-warning .kpi-value {
    color: #b45309;
  }

  .table-responsive-container {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    overflow: hidden;
    margin-bottom: 16px;
    background: #ffffff;
  }

  .section-header {
    background: #f8fafc;
    padding: 12px 18px;
    font-size: 0.95rem;
    font-weight: 700;
    color: #334155;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .premium-table {
    width: 100%;
    margin-bottom: 0;
    font-size: 0.86rem;
  }

  .premium-table thead th {
    background: linear-gradient(135deg, #2f3b52 0%, #1e2533 100%) !important;
    color: #ffffff !important;
    font-weight: 600;
    text-align: center;
    padding: 10px 10px;
    border: none;
    white-space: nowrap;
  }

  .premium-table tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    border-color: #f1f5f9;
    white-space: nowrap;
    border-right: 1px solid #f1f5f9;
  }

  .totals-row {
    background: #f8fafc !important;
    font-weight: 700;
    color: #1e293b;
    border-top: 2px solid #cbd5e1 !important;
  }

  .narration-box {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 16px;
    font-size: 0.88rem;
    color: #475569;
  }

  .badge-approved {
    background-color: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .badge-pending {
    background-color: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .badge-type {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: capitalize;
  }
</style>

<div class="payment-view-modal">

  <!-- Header Meta -->
  <div class="meta-dashboard">
    <div class="meta-dashboard-title">
      <div>
        <i class="feather icon-file-text me-1 text-primary"></i>
        <span>Payment Receipt Details</span>
        <span class="badge bg-primary ms-1">#<?= htmlspecialchars($payment['inv_no']); ?></span>
      </div>
      <div>
        <?php if ($is_approved): ?>
          <span class="badge-approved">
            <i class="fa fa-check-circle"></i> Approved
          </span>
        <?php else: ?>
          <span class="badge-pending">
            <i class="fa fa-clock-o"></i> Pending Approval
          </span>
        <?php endif; ?>
      </div>
    </div>

    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Customer Name</span>
        <span class="meta-value">
          <?= htmlspecialchars($payment['customer_name'] ? $payment['customer_name'] : ($customer['company_name'] ?? '-')); ?>
          <?php if (!empty($customer['owner_name']) && $customer['owner_name'] != $payment['customer_name']): ?>
            <small class="text-muted d-block" style="font-size: 0.78rem; font-weight: normal;">(<?= htmlspecialchars($customer['owner_name']); ?>)</small>
          <?php endif; ?>
        </span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Payment Date</span>
        <span class="meta-value"><?= ($payment['date'] && $payment['date'] != '0000-00-00') ? date('d M, Y', strtotime($payment['date'])) : '-'; ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Invoice / Voucher No</span>
        <span class="meta-value font-monospace"><?= htmlspecialchars($payment['inv_no']); ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Payment Type</span>
        <span class="meta-value">
          <span class="badge-type"><?= ucfirst($payment['payment_type']); ?></span>
        </span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Payment Method</span>
        <span class="meta-value">
          <?php if ($payment['payment_method'] == 'bank'): ?>
            <span class="badge-type"><i class="fa fa-university text-primary me-1"></i>Bank & Cheque</span>
          <?php else: ?>
            <span class="badge-type"><i class="fa fa-money text-success me-1"></i>Cash</span>
          <?php endif; ?>
        </span>
      </div>

      <?php if ($payment['payment_method'] == 'bank' && !empty($bank_info)): ?>
        <div class="meta-item">
          <span class="meta-label">Company Bank Account</span>
          <span class="meta-value">
            <?= htmlspecialchars($bank_info['bank_name']); ?> (A/C: <?= htmlspecialchars($bank_info['account_no']); ?>)
          </span>
        </div>
      <?php endif; ?>

      <div class="meta-item">
        <span class="meta-label">Added By</span>
        <span class="meta-value"><?= htmlspecialchars($payment['added_by_name'] ?: '-'); ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Added Date</span>
        <span class="meta-value"><?= ($payment['added_date'] && $payment['added_date'] != '0000-00-00 00:00:00') ? date('d M, Y h:i A', strtotime($payment['added_date'])) : '-'; ?></span>
      </div>

      <?php if ($is_approved): ?>
        <div class="meta-item">
          <span class="meta-label">Approval Date</span>
          <span class="meta-value text-success">
            <i class="fa fa-calendar-check-o me-1"></i><?= ($payment['approval_date'] && $payment['approval_date'] != '0000-00-00 00:00:00') ? date('d M, Y h:i A', strtotime($payment['approval_date'])) : '-'; ?>
          </span>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- KPI / Amount Breakdown Cards -->
  <div class="kpi-cards-grid">
    <div class="kpi-card highlight-primary">
      <div class="kpi-title">Amount (INR)</div>
      <div class="kpi-value">₹<?= number_format((float)$payment['amount'], 2); ?></div>
    </div>

    <div class="kpi-card highlight-primary">
      <div class="kpi-title">Total Tender</div>
      <div class="kpi-value">₹<?= number_format((float)$payment['total_tender'], 2); ?></div>
    </div>

    <div class="kpi-card highlight-success">
      <div class="kpi-title">Allocated (Inv)</div>
      <div class="kpi-value">₹<?= number_format((float)$payment['allocated_inv'], 2); ?></div>
    </div>

    <div class="kpi-card <?= ($payment['on_account'] > 0) ? 'highlight-warning' : ''; ?>">
      <div class="kpi-title">On Account</div>
      <div class="kpi-value">₹<?= number_format((float)$payment['on_account'], 2); ?></div>
    </div>

    <div class="kpi-card">
      <div class="kpi-title">Adjustments</div>
      <div class="kpi-value">₹<?= number_format((float)$payment['adjustments'], 2); ?></div>
    </div>

    <div class="kpi-card">
      <div class="kpi-title">Total Outstanding</div>
      <div class="kpi-value">₹<?= number_format((float)$payment['total_outstanding'], 2); ?></div>
    </div>

    <div class="kpi-card">
      <div class="kpi-title">Balance After</div>
      <div class="kpi-value">₹<?= number_format((float)$payment['balance_after'], 2); ?></div>
    </div>
  </div>

  <!-- Narration if any -->
  <?php if (!empty($payment['narration'])): ?>
    <div class="narration-box">
      <strong><i class="feather icon-message-square me-1"></i> Narration:</strong>
      <span class="ms-1"><?= nl2br(htmlspecialchars($payment['narration'])); ?></span>
    </div>
  <?php endif; ?>

  <!-- Allocated Invoices Table -->
  <div class="table-responsive-container">
    <div class="section-header">
      <span><i class="feather icon-list me-1"></i> Allocated Sales Orders / Invoices (<?= count($payment_records); ?>)</span>
    </div>

    <?php if (!empty($payment_records)): ?>
      <div class="table-responsive">
        <table class="table premium-table table-bordered mb-0">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Order / Invoice Ref</th>
              <th>Order Date</th>
              <th class="text-end">Order Total (₹)</th>
              <th class="text-end">Allocated Paid (₹)</th>
              <th class="text-end">Remaining Balance (₹)</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $total_order_val = 0;
              $total_paid_val = 0;
              $total_pending_val = 0;
              foreach ($payment_records as $idx => $rec):
                $order_total = (float)$rec['order_total'];
                $order_paid = (float)$rec['order_paid'];
                $order_pending = (float)$rec['order_pending'];

                $total_order_val += $order_total;
                $total_paid_val += $order_paid;
                $total_pending_val += $order_pending;
            ?>
              <tr>
                <td class="text-center"><?= $idx + 1; ?></td>
                <td>
                  <strong><?= htmlspecialchars($rec['refrence_no'] ?: ('Order #' . $rec['order_id'])); ?></strong>
                </td>
                <td class="text-center">
                  <?= ($rec['order_date'] && $rec['order_date'] != '0000-00-00') ? date('d M, Y', strtotime($rec['order_date'])) : '-'; ?>
                </td>
                <td class="text-end font-monospace">₹<?= number_format($order_total, 2); ?></td>
                <td class="text-end font-monospace text-success fw-bold">₹<?= number_format($order_paid, 2); ?></td>
                <td class="text-end font-monospace">₹<?= number_format($order_pending, 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="totals-row">
              <td colspan="3" class="text-end text-uppercase">Total</td>
              <td class="text-end font-monospace">₹<?= number_format($total_order_val, 2); ?></td>
              <td class="text-end font-monospace text-success fw-bold">₹<?= number_format($total_paid_val, 2); ?></td>
              <td class="text-end font-monospace">₹<?= number_format($total_pending_val, 2); ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php else: ?>
      <div class="p-3 text-center text-muted">
        <i class="feather icon-info me-1"></i> No specific invoice allocations recorded. (Payment received On Account).
      </div>
    <?php endif; ?>
  </div>

  <!-- On Account Credit Generated Note if applicable -->
  <?php if (!empty($credit_record) || (float)$payment['on_account'] > 0): ?>
    <div class="alert alert-success d-flex align-items-center mb-2" role="alert" style="border-radius: 10px;">
      <i class="feather icon-check-circle fs-4 me-2"></i>
      <div>
        <strong>On Account Credit Generated:</strong> An advance/credit balance of
        <strong>₹<?= number_format((float)($credit_record['credit_balance'] ?? $payment['on_account']), 2); ?></strong>
        was credited to this customer's account for future invoice allocations.
      </div>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-end mt-2 pt-1 border-top">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
  </div>

</div>
