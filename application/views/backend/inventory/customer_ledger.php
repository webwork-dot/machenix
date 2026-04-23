<?php
$customer = $data ?? [];

// Build address (only non-empty lines)
$addrLines = array_filter([
  trim($customer['address'] ?? ''),
  trim($customer['address_2'] ?? ''),
]);

$cityState = trim(implode(', ', array_filter([
  trim($customer['city_name'] ?? ''),
  trim($customer['state_name'] ?? ''),
])));

$pincode = trim($customer['pincode'] ?? '');
$locationLine = trim($cityState . ($pincode ? " – $pincode" : ''));
?>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap"
  rel="stylesheet">

<style>
  .customer-card-shell,
  .ledger-card-shell {
    border: 1px solid #e8eaed;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
  }

  .card-soft-header {
    background: #fafbfc;
    border-bottom: 1px solid #f0f2f5;
  }

  .fs-10 { font-size: 10px; }
  .fs-11 { font-size: 11px; }
  .fs-12 { font-size: 12px; }
  .fs-13 { font-size: 13px; }
  .fs-15 { font-size: 15px; }
  .fs-9 { font-size: 9px; }

  .track-1 { letter-spacing: 1px; }

  .customer-main-text { color: #111827; }
  .customer-soft-text { color: #1f2937; }

  .mono-amount { font-family: "DM Mono", monospace; }

  .customer-info-divider { border-right: 1px solid #f0f2f5; }
  .customer-info-col { min-width: 220px; }

  .addr-lines { line-height: 1.7; }

  .key-info-table td { padding-top: 3px; padding-bottom: 3px; }
  .key-label-col { width: 40%; }

  .summary-pill {
    font-size: 10px;
    font-weight: 500;
    color: #6b7280;
    padding: 4px 10px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
  }

  .balance-pill {
    font-size: 11px;
    font-weight: 700;
    border-radius: 20px;
    padding: 4px 12px;
    color: #dc2626;
    background: #fef2f2;
    border: 1px solid #fecaca;
  }

  .ledger-table thead th {
    font-size: 10px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    background: #f9fafb;
    border-bottom: 1px solid #e8eaed;
    white-space: nowrap;
  }

  .ledger-row {
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.12s;
    background: #ffffff;
  }

  .ledger-row:hover { background: #f9fafb; }

  .type-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.6px;
    border-radius: 4px;
    padding: 2px 7px;
    text-transform: uppercase;
    color: #7c3aed;
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
  }

  .amount-positive { color: #dc2626; font-weight: 600; }

  .tfoot-border-top { border-top: 2px solid #e8eaed; }
  .balance-row-due { background: #fff5f5; }
  .balance-text-due { color: #dc2626; }
</style>

<!-- ───── Customer Info Card ───── -->
<div class="bg-white customer-card-shell mb-3">
  <!-- Card Header -->
  <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header">
    <div>
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-1">Customer</div>
      <div class="fw-semibold customer-main-text fs-15"><?= html_escape($customer['company_name'] ?? '—') ?></div>
    </div>
  </div>

  <!-- Card Body -->
  <div class="d-flex flex-wrap">
    <!-- Address -->
    <div class="flex-fill px-3 py-3 customer-info-divider customer-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">Address</div>
      <?php if (!empty($addrLines)): ?>
        <div class="fs-13 customer-soft-text addr-lines"><?= implode("<br>", array_map('html_escape', $addrLines)) ?></div>
      <?php else: ?>
        <div class="fs-13 text-muted">—</div>
      <?php endif; ?>
      <?php if (!empty($locationLine)): ?>
        <div class="fs-11 text-secondary fw-medium mt-1"><?= html_escape($locationLine) ?></div>
      <?php endif; ?>
    </div>

    <!-- Key Info -->
    <div class="flex-fill px-3 py-3 customer-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">Key Info</div>
      <table class="w-100 key-info-table fs-12">
        <tr>
          <td class="text-secondary fw-medium key-label-col">Contact Person</td>
          <td class="customer-main-text fw-semibold"><?= html_escape($customer['owner_name'] ?? '—') ?></td>
        </tr>
        <tr>
          <td class="text-secondary fw-medium">Phone</td>
          <td>
            <?php if (!empty($customer['owner_mobile'])): ?>
              <a href="tel:<?= html_escape($customer['owner_mobile']) ?>" class="text-decoration-none fw-semibold fs-11 text-primary"><?= html_escape($customer['owner_mobile']) ?></a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if (!empty($customer['gst_name'])): ?>
          <tr>
            <td class="text-secondary fw-medium">GST Name</td>
            <td class="customer-main-text fw-semibold"><?= html_escape($customer['gst_name']) ?></td>
          </tr>
        <?php endif; ?>
        <?php if (!empty($customer['gst_no'])): ?>
          <tr>
            <td class="text-secondary fw-medium">GST No</td>
            <td class="customer-main-text fw-semibold"><?= html_escape($customer['gst_no']) ?></td>
          </tr>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>

<?php
$total_outstanding = 0;
foreach ($ledger as $item) {
    $total_outstanding += (float)$item['grand_total'];
}
?>

<!-- ───── Unified Ledger ───── -->
<div>
  <div class="bg-white ledger-card-shell">
    <!-- Ledger Header -->
    <div class="d-flex align-items-center justify-content-between px-2 py-2 card-soft-header">
      <div class="fw-semibold customer-main-text fs-13">Customer Ledger</div>
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="summary-pill">Total Sales &nbsp;<strong class="customer-soft-text mono-amount">₹ <?= number_format($total_outstanding, 2) ?></strong></div>
        <div class="balance-pill">Balance &nbsp;₹ <?= number_format($total_outstanding, 2) ?></div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-borderless mb-0 align-middle ledger-table fs-12">
        <thead>
          <tr>
            <th class="text-start px-3 py-2 text-muted fw-semibold">Date</th>
            <th class="text-start px-2 py-2 text-muted fw-semibold">Type</th>
            <th class="text-start px-2 py-2 text-muted fw-semibold">Reference</th>
            <th class="text-end px-2 py-2 text-muted fw-semibold">Total Amount</th>
            <th class="text-start px-3 py-2 text-muted fw-semibold">Added By</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($ledger)): ?>
            <?php foreach ($ledger as $item): ?>
              <tr class="ledger-row">
                <td class="px-3 py-2 text-secondary fs-11 text-nowrap"><?= date('d M y', strtotime($item['date'])) ?></td>
                <td class="px-2 py-2"><span class="type-badge">Sales Order</span></td>
                <td class="px-2 py-2"><div class="fw-semibold customer-soft-text fs-11"><?= html_escape($item['voucher_no']) ?></div></td>
                <td class="px-2 py-2 text-end amount-positive">₹ <?= number_format($item['grand_total'], 2) ?></td>
                <td class="px-3 py-2 text-muted fs-10 text-nowrap"><?= html_escape($item['added_by_name'] ?: '—') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="py-5 text-center text-muted fs-13">No approved sales orders found for this customer.</td></tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="balance-row-due tfoot-border-top">
            <td colspan="3" class="px-3 py-2 text-end fs-11 fw-bold customer-main-text">Total Outstanding</td>
            <td class="px-2 py-2 text-end fw-bold balance-text-due fs-12">₹ <?= number_format($total_outstanding, 2) ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
