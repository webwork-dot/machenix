<?php
  // Get Manual Payment ID from param2
  $payment_id = $param2;

  // Get Payment details
  $payment = $this->db->query("SELECT * FROM customer_payment WHERE id = '$payment_id'")->row_array();

  if (empty($payment)) {
    echo '<div class="alert alert-danger p-2">Manual payment record not found.</div>';
    return;
  }

  // Get Bank Account details if applicable
  $bank_info = null;
  if ($payment['payment_method'] == 'cheque' || $payment['payment_method'] == 'bank') {
    if (!empty($payment['company_bank'])) {
      $bank_info = $this->db->query("SELECT * FROM bank_accounts WHERE id = '{$payment['company_bank']}'")->row_array();
    } elseif (!empty($payment['company_bank_account']) && is_numeric($payment['company_bank_account'])) {
      $bank_info = $this->db->query("SELECT * FROM bank_accounts WHERE id = '{$payment['company_bank_account']}'")->row_array();
    }
  }

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

  .amount-hero-card {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
  }

  .amount-hero-title {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 4px;
  }

  .amount-hero-val {
    font-size: 1.85rem;
    font-weight: 800;
    color: #0f172a;
  }

  .badge-status {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .badge-approved {
    background-color: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
  }

  .badge-pending {
    background-color: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
  }

  .badge-type {
    display: inline-block;
    padding: 3px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
    text-transform: capitalize;
    background: #e2e8f0;
    color: #334155;
  }

  .badge-official {
    background: #dbeafe;
    color: #1e40af;
  }

  .badge-unofficial {
    background: #f1f5f9;
    color: #475569;
  }

  .narration-box {
    background: #f8fafc;
    border-left: 4px solid #6366f1;
    border-radius: 0 8px 8px 0;
    padding: 12px 16px;
    font-size: 0.88rem;
    margin-bottom: 16px;
    color: #334155;
  }
</style>

<div class="payment-view-modal">

  <!-- Amount Hero Card -->
  <div class="amount-hero-card">
    <div>
      <div class="amount-hero-title"><i class="feather icon-credit-card me-1"></i> Manual Payment Amount</div>
      <div class="amount-hero-val text-primary">
        ₹<?= number_format((float)$payment['amount'], 2); ?>
      </div>
    </div>
  </div>

  <!-- Meta Dashboard: Receipt & Transaction Info -->
  <div class="meta-dashboard">
    <div class="meta-dashboard-title">
      <span><i class="feather icon-file-text me-1 text-primary"></i> Payment Information</span>
      <span class="badge-type <?= ($payment['payment_type'] == 'official') ? 'badge-official' : 'badge-unofficial'; ?>">
        <?= ucfirst($payment['payment_type']); ?>
      </span>
    </div>

    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Receipt / Inv No</span>
        <span class="meta-value font-monospace text-primary"><?= htmlspecialchars($payment['inv_no']); ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Payment Date</span>
        <span class="meta-value"><?= $payment['date'] ? date('d M, Y', strtotime($payment['date'])) : '-'; ?></span>
      </div>


      <div class="meta-item">
        <span class="meta-label">Payment Method</span>
        <span class="meta-value">
          <?php if ($payment['payment_method'] == 'cheque' || $payment['payment_method'] == 'bank'): ?>
            <span class="badge-type"><i class="fa fa-university text-primary me-1"></i>Cheque</span>
          <?php else: ?>
            <span class="badge-type"><i class="fa fa-money text-success me-1"></i>Cash</span>
          <?php endif; ?>
        </span>
      </div>

      <?php if ($payment['payment_method'] == 'cheque' || $payment['payment_method'] == 'bank'): ?>
        <div class="meta-item">
          <span class="meta-label">Company Bank</span>
          <span class="meta-value">
            <?= htmlspecialchars($bank_info['bank_name'] ?? 'Bank Account'); ?>
            <?php if (!empty($payment['company_bank_account'])): ?>
              <span class="text-muted small">(<?= htmlspecialchars($payment['company_bank_account']); ?>)</span>
            <?php endif; ?>
          </span>
        </div>
      <?php endif; ?>

      <div class="meta-item">
        <span class="meta-label">Added By</span>
        <span class="meta-value"><?= htmlspecialchars($payment['added_by_name'] ?: '—'); ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Added On</span>
        <span class="meta-value"><?= !empty($payment['added_date']) ? date('d M, Y h:i A', strtotime($payment['added_date'])) : '-'; ?></span>
      </div>


    </div>
  </div>

  <!-- Narration Section -->
  <?php if (!empty($payment['narration'])): ?>
    <div class="meta-dashboard">
      <div class="meta-dashboard-title">
        <span><i class="feather icon-align-left me-1 text-primary"></i> Narration / Remarks</span>
      </div>
      <div class="narration-box mb-0">
        <?= nl2br(htmlspecialchars($payment['narration'])); ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="text-end mt-2">
    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
  </div>

</div>
