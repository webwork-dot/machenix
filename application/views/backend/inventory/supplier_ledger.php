<?php
$supplier = $data ?? [];

// Build address (only non-empty lines)
$addrLines = array_filter([
  trim($supplier['address'] ?? ''),
  trim($supplier['address_2'] ?? ''),
  trim($supplier['address_3'] ?? ''),
]);

$cityState = trim(implode(', ', array_filter([
  trim($supplier['city_name'] ?? ''),
  trim($supplier['state_name'] ?? ''),
])));

$pincode = trim($supplier['pincode'] ?? '');
$locationLine = trim($cityState . ($pincode ? " – $pincode" : ''));
?>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap"
  rel="stylesheet">

<style>
  .supplier-card-shell,
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

  .fs-10 {
    font-size: 10px;
  }

  .fs-11 {
    font-size: 11px;
  }

  .fs-12 {
    font-size: 12px;
  }

  .fs-13 {
    font-size: 13px;
  }

  .fs-15 {
    font-size: 15px;
  }

  .fs-9 {
    font-size: 9px;
  }

  .track-1 {
    letter-spacing: 1px;
  }

  .supplier-main-text {
    color: #111827;
  }

  .supplier-soft-text {
    color: #1f2937;
  }

  .mono-amount {
    font-family: "DM Mono", monospace;
  }

  .gst-pill {
    font-size: 11px;
    font-weight: 500;
    color: #2563eb;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 4px 10px;
    letter-spacing: 0.5px;
  }

  .supplier-info-divider {
    border-right: 1px solid #f0f2f5;
  }

  .supplier-info-col {
    min-width: 220px;
  }

  .addr-lines {
    line-height: 1.7;
  }

  .key-info-table td {
    padding-top: 3px;
    padding-bottom: 3px;
  }

  .key-label-col {
    width: 40%;
  }

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
  }

  .balance-pill-due {
    color: #dc2626;
    background: #fef2f2;
    border: 1px solid #fecaca;
  }

  .balance-pill-credit {
    color: #16a34a;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
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
  }

  .ledger-row-payment {
    background: #f0fdf4;
  }

  .ledger-row-payment:hover {
    background: #dcfce7;
  }

  .ledger-row-purchase {
    background: #ffffff;
  }

  .ledger-row-purchase:hover {
    background: #f9fafb;
  }

  .type-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.6px;
    border-radius: 4px;
    padding: 2px 7px;
    text-transform: uppercase;
  }

  .type-badge-payment {
    color: #0891b2;
    background: #ecfeff;
    border: 1px solid #a5f3fc;
  }

  .type-badge-po {
    color: #7c3aed;
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
  }

  .amount-positive {
    color: #dc2626;
    font-weight: 600;
  }

  .amount-negative {
    color: #16a34a;
    font-weight: 600;
  }

  .tfoot-border-top {
    border-top: 2px solid #e8eaed;
  }

  .balance-row-due {
    background: #fff5f5;
  }

  .balance-row-credit {
    background: #f0fdf4;
  }

  .balance-text-due {
    color: #dc2626;
  }

  .balance-text-credit {
    color: #16a34a;
  }
</style>

<!-- ───── Supplier Info Card ───── -->
<div class="bg-white supplier-card-shell mb-3">

  <!-- Card Header -->
  <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header">
    <div>
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-1">
        Supplier
      </div>
      <div class="fw-semibold supplier-main-text fs-15">
        <?= html_escape($supplier['name'] ?? '—') ?>
      </div>
    </div>

  </div>

  <!-- Card Body -->
  <div class="d-flex flex-wrap">

    <!-- Address -->
    <div class="flex-fill px-3 py-3 supplier-info-divider supplier-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">
        Address
      </div>
      <?php if (!empty($addrLines)): ?>
        <div class="fs-13 supplier-soft-text addr-lines">
          <?= implode("<br>", array_map('html_escape', $addrLines)) ?>
        </div>
      <?php else: ?>
        <div class="fs-13 text-muted">—</div>
      <?php endif; ?>
      <?php if (!empty($locationLine)): ?>
        <div class="fs-11 text-secondary fw-medium mt-1">
          <?= html_escape($locationLine) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Key Info -->
    <div class="flex-fill px-3 py-3 supplier-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">
        Key Info
      </div>
      <table class="w-100 key-info-table fs-12">
        <tr>
          <td class="text-secondary fw-medium key-label-col">Contact Person</td>
          <td class="supplier-main-text fw-semibold">
            <?= html_escape($supplier['contact_name'] ?? '—') ?>
          </td>
        </tr>
        <tr>
          <td class="text-secondary fw-medium">Phone</td>
          <td>
            <?php if (!empty($supplier['contact_no'])): ?>
              <a href="tel:<?= html_escape($supplier['contact_no']) ?>"
                class="text-decoration-none fw-semibold fs-11 text-primary">
                <?= html_escape($supplier['contact_no']) ?>
              </a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if (!empty($supplier['gst_name'])): ?>
          <tr>
            <td class="text-secondary fw-medium">GST Name</td>
            <td class="supplier-main-text fw-semibold">
              <?= html_escape($supplier['gst_name']) ?>
            </td>
          </tr>
        <?php endif; ?>
        <?php if (!empty($supplier['gst_no'])): ?>
          <tr>
            <td class="text-secondary fw-medium">GST No</td>
            <td class="supplier-main-text fw-semibold">
              <?= html_escape($supplier['gst_no']) ?>
            </td>
          </tr>
        <?php endif; ?>
      </table>
    </div>

  </div>
</div>


<?php
// --- 1. DATA MERGE & SORTING ---
$ledger = [];

if (!empty($outstanding)) {
  foreach ($outstanding as $row) {
    $ledger[] = [
      'date' => $row['date'],
      'ref' => $row['voucher_no'],
      'batch' => $row['voucher_no'],
      'type' => 'PURCHASE',
      'status' => $row['delivery_status'],
      'rmb' => (float) $row['total_actual_rmb'],
      'usd' => (float) $row['total_actual_usd'],
      'inr' => (float) $row['total_actual_inr'],
      'added_by' => $row['added_by_name'],
      'is_payment' => false
    ];
  }
}

if (!empty($payments)) {
  foreach ($payments as $pay) {
    $ledger[] = [
      'date' => $pay['payment_date'],
      'ref' => $pay['invoice_no'],
      'batch' => $pay['batch_no'],
      'type' => 'PAYMENT',
      'status' => $pay['payment_type'],
      'rmb' => (float) $pay['amount_rmb'],
      'usd' => (float) $pay['amount_dollar'],
      'inr' => (float) $pay['amount_rs'],
      'added_by' => $pay['added_by_name'],
      'is_payment' => true
    ];
  }
}

usort($ledger, function ($a, $b) {
  return strtotime($b['date']) - strtotime($a['date']);
});

$totals = [
  'purchase' => ['rmb' => 0, 'usd' => 0, 'inr' => 0],
  'payment' => ['rmb' => 0, 'usd' => 0, 'inr' => 0]
];

foreach ($ledger as $item) {
  $tKey = $item['is_payment'] ? 'payment' : 'purchase';
  $totals[$tKey]['rmb'] += $item['rmb'];
  $totals[$tKey]['usd'] += $item['usd'];
  $totals[$tKey]['inr'] += $item['inr'];
}

$balance = [
  'rmb' => $totals['purchase']['rmb'] - $totals['payment']['rmb'],
  'usd' => $totals['purchase']['usd'] - $totals['payment']['usd'],
  'inr' => $totals['purchase']['inr'] - $totals['payment']['inr']
];

$balanceIsDue = $balance['inr'] > 0;
$balancePillClass = $balanceIsDue ? 'balance-pill-due' : 'balance-pill-credit';
$balanceRowClass = $balanceIsDue ? 'balance-row-due' : 'balance-row-credit';
$balanceTextClass = $balanceIsDue ? 'balance-text-due' : 'balance-text-credit';
?>


<!-- ───── Unified Ledger ───── -->
<div>
  <div class="bg-white ledger-card-shell">

    <!-- Ledger Header -->
    <div class="d-flex align-items-center justify-content-between px-2 py-2 card-soft-header">
      <div class="fw-semibold supplier-main-text fs-13">
        Supplier Ledger
      </div>

      <!-- Balance Summary Pills -->
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="summary-pill">
          Purchases &nbsp;<strong class="supplier-soft-text mono-amount">
            ₹ <?= number_format($totals['purchase']['inr'], 2) ?>
          </strong>
        </div>
        <div class="summary-pill">
          Payments &nbsp;<strong class="supplier-soft-text mono-amount">
            ₹ <?= number_format($totals['payment']['inr'], 2) ?>
          </strong>
        </div>
        <div class="balance-pill <?= $balancePillClass ?>">
          Balance &nbsp;₹ <?= number_format($balance['inr'], 2) ?>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-borderless mb-0 align-middle ledger-table fs-12">
        <thead>
          <tr>
            <th class="text-start px-3 py-2 text-muted fw-semibold">
              Date</th>
            <th class="text-start px-2 py-2 text-muted fw-semibold">
              Type</th>
            <th class="text-start px-2 py-2 text-muted fw-semibold">
              Batch</th>
            <th class="text-end px-2 py-2 text-muted fw-semibold">
              RMB</th>
            <th class="text-end px-2 py-2 text-muted fw-semibold">
              USD</th>
            <th class="text-end px-2 py-2 text-muted fw-semibold">
              INR</th>
            <th class="text-start px-3 py-2 text-muted fw-semibold">
              Added By</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($ledger)): ?>
            <?php foreach ($ledger as $i => $item): ?>
              <?php
              $rowClass = $item['is_payment'] ? 'ledger-row-payment' : 'ledger-row-purchase';
              $amtClass = $item['is_payment'] ? 'amount-negative' : 'amount-positive';
              $sign = $item['is_payment'] ? '−' : '+';
              ?>
              <tr class="ledger-row <?= $rowClass ?>">

                <!-- Date -->
                <td class="px-3 py-2 text-secondary fs-11 text-nowrap">
                  <?= date('d M y', strtotime($item['date'])) ?>
                </td>

                <!-- Type Badge -->
                <td class="px-2 py-2">
                  <?php if ($item['is_payment']): ?>
                    <span class="type-badge type-badge-payment">Payment</span>
                  <?php else: ?>
                    <span class="type-badge type-badge-po">Purchase Order</span>
                  <?php endif; ?>
                </td>

                <!-- Ref -->
                <td class="px-2 py-2">
                  <div class="fw-semibold supplier-soft-text fs-11">
                    <?= html_escape($item['batch']) ?>
                  </div>
                </td>

                <!-- Amounts -->
                <td class="px-2 py-2 text-end <?= $amtClass ?>">
                  <?= $sign ?>     <?= number_format($item['rmb'], 2) ?>
                </td>
                <td class="px-2 py-2 text-end <?= $amtClass ?>">
                  <?= $sign ?>     <?= number_format($item['usd'], 2) ?>
                </td>
                <td class="px-2 py-2 text-end <?= $amtClass ?> fs-12">
                  <?= $sign ?>     <?= number_format($item['inr'], 2) ?>
                </td>

                <!-- Added By -->
                <td class="px-3 py-2 text-muted fs-10 text-nowrap">
                  <?= html_escape($item['added_by'] ?: '—') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="py-5 text-center text-muted fs-13">
                No transactions found for this supplier.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>

        <!-- Footer Totals -->
        <tfoot>
          <tr class="<?= $balanceRowClass ?> tfoot-border-top">
            <td colspan="3"
              class="px-3 py-2 text-end fs-11 fw-bold supplier-main-text">
              Total Outstanding
            </td>
            <td class="px-2 py-2 text-end fw-bold <?= $balanceTextClass ?> fs-12">
              <?= number_format($balance['rmb'], 2) ?>
            </td>
            <td class="px-2 py-2 text-end fw-bold <?= $balanceTextClass ?> fs-12">
              <?= number_format($balance['usd'], 2) ?>
            </td>
            <td class="px-2 py-2 text-end fw-bold <?= $balanceTextClass ?> fs-12">
              <?= number_format($balance['inr'], 2) ?>
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

  </div>
</div>

<script>
  if (typeof feather !== 'undefined') feather.replace();
</script>
