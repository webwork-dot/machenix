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
    padding: 3px 0;
    vertical-align: top;
  }

  /* Status Colors */
  .type-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 500;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .type-badge-opening {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #e5e7eb;
  }

  .type-badge-payment {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
  }

  .type-badge-po {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fde68a;
  }

  /* Amount Colors */
  .amount-positive {
    color: #dc2626; /* We owe them more / purchase increases debt */
    font-weight: 600;
  }

  .amount-negative {
    color: #16a34a; /* We paid / decreases debt */
    font-weight: 600;
  }

  /* Row Backgrounds */
  .ledger-row-opening {
    background-color: #fafbfc;
  }

  .ledger-row-payment {
    background-color: #f6fdf9;
  }

  .ledger-row-purchase {
    background-color: #fffdf5;
  }

  .ledger-row:hover {
    background-color: #f1f5f9 !important;
  }

  /* Header Summary Pills */
  .summary-pill {
    font-size: 11px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 5px 12px;
    color: #64748b;
  }

  .balance-pill {
    font-size: 11px;
    font-weight: 600;
    border-radius: 8px;
    padding: 5px 12px;
  }

  .balance-pill-due {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
  }

  .balance-pill-credit {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
  }

  /* Back Button Styles */
  .btn-back-soft {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 500;
    color: #374151;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }

  .btn-back-soft:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #111827;
  }

  .btn-back-soft i {
    font-size: 14px;
  }
</style>

<!-- Header & Back Action -->
<div class="d-flex align-items-center justify-content-between mb-2">
  <div class="d-flex align-items-center gap-2">
    <h4 class="mb-0 fw-semibold supplier-main-text">Local Supplier Profile</h4>
  </div>
  <a href="<?php echo base_url('inventory/local-supplier'); ?>" class="btn-back-soft">
    <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to List
  </a>
</div>

<!-- ───── Section 1: Supplier Profile Card ───── -->
<div class="card mb-2 supplier-card-shell">
  <div class="card-body p-0">
    <div class="row g-0">

      <!-- Column 1: Core details (Name & GST) -->
      <div class="col-12 col-md-4 p-3 supplier-info-divider supplier-info-col">
        <div class="d-flex align-items-start gap-2 mb-2">
          <div>
            <h5 class="fw-bold supplier-main-text mb-1" style="font-size: 16px;">
              <?= html_escape($supplier['name'] ?? '—') ?>
            </h5>
            <span class="fs-11 text-muted text-uppercase track-1">LOCAL SUPPLIER ID: #<?= $supplier['id'] ?></span>
          </div>
        </div>

        <?php if (!empty($supplier['gst_no'])): ?>
          <div class="mt-2">
            <span class="gst-pill">
              GST: <?= html_escape($supplier['gst_no']) ?>
            </span>
          </div>
        <?php endif; ?>

        <!-- Address details below GST name -->
        <div class="mt-3 fs-11 supplier-soft-text addr-lines">
          <div class="fw-semibold text-muted text-uppercase track-1 fs-9 mb-1">Billing Address</div>
          <?php if (!empty($supplier['gst_name'])): ?>
            <div class="fw-semibold supplier-main-text"><?= html_escape($supplier['gst_name']) ?></div>
          <?php endif; ?>
          <?php foreach ($addrLines as $line): ?>
            <div><?= html_escape($line) ?></div>
          <?php endforeach; ?>
          <?php if ($locationLine): ?>
            <div class="fw-semibold"><?= html_escape($locationLine) ?></div>
          <?php endif; ?>
          <?php if (!empty($supplier['country_name'])): ?>
            <div class="text-muted"><?= html_escape($supplier['country_name']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Column 2: Contact Info -->
      <div class="col-12 col-md-4 p-3 supplier-info-divider supplier-info-col">
        <div class="fw-semibold text-muted text-uppercase track-1 fs-9 mb-2">Contact Information</div>
        <table class="w-100 key-info-table fs-12">
          <tr>
            <td class="text-muted w-40">Contact Person</td>
            <td class="supplier-main-text fw-semibold">: <?= html_escape($supplier['contact_name'] ?: '—') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Contact No</td>
            <td class="supplier-main-text">: <?= html_escape($supplier['contact_no'] ?: '—') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Telephone</td>
            <td class="supplier-main-text">: <?= html_escape($supplier['tel_no'] ?: '—') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Email Address</td>
            <td class="supplier-main-text">: <a href="mailto:<?= html_escape($supplier['email'] ?? '') ?>" class="text-decoration-none"><?= html_escape($supplier['email'] ?: '—') ?></a></td>
          </tr>
        </table>
      </div>

      <!-- Column 3: Bank Details -->
      <div class="col-12 col-md-4 p-3 supplier-info-col">
        <div class="fw-semibold text-muted text-uppercase track-1 fs-9 mb-2">Banking details</div>
        <table class="w-100 key-info-table fs-12">
          <tr>
            <td class="text-muted w-40">Beneficiary</td>
            <td class="supplier-main-text fw-semibold">: <?= html_escape($supplier['beneficiary'] ?: '—') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Account No</td>
            <td class="supplier-main-text">: <?= html_escape($supplier['account_no'] ?: '—') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Bank Name</td>
            <td class="supplier-main-text">: <?= html_escape($supplier['advising_bank'] ?: '—') ?></td>
          </tr>
          <tr>
            <td class="text-muted">SWIFT Code</td>
            <td class="supplier-main-text">: <?= html_escape($supplier['swift_code'] ?: '—') ?></td>
          </tr>
        </table>
      </div>

    </div>
  </div>
</div>


<!-- ───── Section 2: Ledger Processing & Display ───── -->
<?php
$ledger = [];

if (!empty($outstanding)) {
  foreach ($outstanding as $out) {
    $ledger[] = [
      'date' => $out['received_date'],
      'ref' => $out['invoice_no'],
      'batch' => $out['batch_no'],
      'type' => 'PURCHASE',
      'status' => 'APPROVED',
      'rmb' => 0.0,
      'usd' => 0.0,
      'inr' => (float) $out['received_amount'],
      'added_by' => $out['added_by_name'],
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
      'rmb' => 0.0,
      'usd' => 0.0,
      'inr' => (float) $pay['amount_rs'],
      'added_by' => $pay['added_by_name'],
      'is_payment' => true
    ];
  }
}

$opening_inr = (float)($supplier['outstanding_inr'] ?? 0.00);

usort($ledger, function ($a, $b) {
  return strtotime($a['date']) - strtotime($b['date']);
});

$totals = [
  'purchase' => ['inr' => 0],
  'payment' => ['inr' => 0]
];

foreach ($ledger as $item) {
  $tKey = $item['is_payment'] ? 'payment' : 'purchase';
  $totals[$tKey]['inr'] += $item['inr'];
}

$balance = [
  'inr' => $opening_inr + $totals['purchase']['inr'] - $totals['payment']['inr']
];

$balanceIsDue = $balance['inr'] > 0;
$balancePillClass = $balanceIsDue ? 'balance-pill-due' : 'balance-pill-credit';
$balanceRowClass = $balanceIsDue ? 'balance-row-due' : 'balance-row-credit';
$balanceTextClass = $balanceIsDue ? 'balance-text-due' : 'balance-text-credit';

$display_rows = [];
$display_rows[] = [
  'date' => !empty($supplier['added_date']) ? $supplier['added_date'] : '',
  'ref' => 'Opening Balance',
  'batch' => 'Opening Balance',
  'type' => 'OPENING',
  'inr' => $opening_inr,
  'added_by' => $supplier['added_by_name'] ?? '—',
  'is_payment' => false,
  'is_opening' => true
];

foreach ($ledger as $item) {
  $item['is_opening'] = false;
  $display_rows[] = $item;
}
?>


<!-- ───── Unified Ledger ───── -->
<div>
  <div class="bg-white ledger-card-shell">

    <!-- Ledger Header -->
    <div class="d-flex align-items-center justify-content-between px-2 py-2 card-soft-header">
      <div class="fw-semibold supplier-main-text fs-13">
        Local Supplier Ledger
      </div>

      <!-- Balance Summary Pills -->
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="summary-pill">
          Opening Balance &nbsp;<strong class="supplier-soft-text mono-amount">
            ₹ <?= number_format($opening_inr, 2) ?>
          </strong>
        </div>
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
              INR</th>
            <th class="text-start px-3 py-2 text-muted fw-semibold">
              Added By</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($display_rows)): ?>
            <?php foreach ($display_rows as $i => $item): ?>
              <?php
              if ($item['is_opening']) {
                $rowClass = 'ledger-row-opening';
                $amtClass = 'supplier-soft-text';
                $sign = '';
              } else {
                $rowClass = $item['is_payment'] ? 'ledger-row-payment' : 'ledger-row-purchase';
                $amtClass = $item['is_payment'] ? 'amount-negative' : 'amount-positive';
                $sign = $item['is_payment'] ? '−' : '+';
              }
              ?>
              <tr class="ledger-row <?= $rowClass ?>">

                <!-- Date -->
                <td class="px-3 py-2 text-secondary fs-11 text-nowrap">
                  <?= ($item['is_opening'] || empty($item['date'])) ? '' : date('d M y', strtotime($item['date'])) ?>
                </td>

                <!-- Type Badge -->
                <td class="px-2 py-2">
                  <?php if ($item['is_opening']): ?>
                    <span class="type-badge type-badge-opening">Opening</span>
                  <?php elseif ($item['is_payment']): ?>
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
              <td colspan="5" class="text-center py-4 text-muted">
                No ledger records found for this local supplier.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
