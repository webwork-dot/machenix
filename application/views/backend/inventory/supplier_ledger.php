<?php
$supplier = $data ?? [];

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

$opening = [
  'rmb' => (float) ($supplier['outstanding_rmb'] ?? 0),
  'usd' => (float) ($supplier['outstanding_usd'] ?? 0),
  'inr' => (float) ($supplier['outstanding_inr'] ?? 0),
];

$kinds = [
  'opening'   => ['row' => 'ledger-row-opening',   'badge' => 'type-badge-opening',   'label' => 'Opening',          'amt' => 'supplier-soft-text', 'sign' => ''],
  'payment'   => ['row' => 'ledger-row-payment',   'badge' => 'type-badge-payment',   'label' => 'Payment',          'amt' => 'amount-negative',    'sign' => '−'],
  'purchase'  => ['row' => 'ledger-row-purchase',  'badge' => 'type-badge-po',        'label' => 'Purchase Order',   'amt' => 'amount-positive',    'sign' => '+'],
  'adj_plus'  => ['row' => 'ledger-row-adj-plus',  'badge' => 'type-badge-adj-plus',  'label' => 'Adjustment (+)',   'amt' => 'amount-positive',    'sign' => '+'],
  'adj_minus' => ['row' => 'ledger-row-adj-minus', 'badge' => 'type-badge-adj-minus', 'label' => 'Adjustment (-)',   'amt' => 'amount-negative',    'sign' => '−'],
];

$build_ledger = function ($purchases, $payments, $adjustments, $usd_key, $inr_key, $rmb_key = null, $opening_bal = null) use ($supplier) {
  $ledger = [];
  foreach ($purchases ?? [] as $row) {
    $ledger[] = [
      'date'     => $row['date'],
      'batch'    => $row['voucher_no'],
      'kind'     => 'purchase',
      'rmb'      => $rmb_key ? (float) $row[$rmb_key] : 0,
      'usd'      => (float) $row[$usd_key],
      'inr'      => (float) $row[$inr_key],
      'added_by' => $row['added_by_name'] ?? '—',
    ];
  }
  foreach ($payments ?? [] as $pay) {
    $ledger[] = [
      'date'     => $pay['payment_date'],
      'batch'    => $pay['batch_no'],
      'kind'     => 'payment',
      'rmb'      => (float) $pay['amount_rmb'],
      'usd'      => (float) $pay['amount_dollar'],
      'inr'      => (float) $pay['amount_rs'],
      'added_by' => $pay['added_by_name'] ?? '—',
      'remark'   => $pay['invoice_no'] ? ('Inv: ' . $pay['invoice_no']) : '',
    ];
  }
  foreach ($adjustments ?? [] as $adj) {
    $kind = ($adj['amt_type'] === 'plus') ? 'adj_plus' : 'adj_minus';
    $batchText = !empty($adj['batch_no']) ? $adj['batch_no'] : 'General Adjustment';
    if (!empty($adj['remark'])) {
      $batchText .= ' (' . $adj['remark'] . ')';
    }
    $ledger[] = [
      'date'     => $adj['date'],
      'batch'    => $batchText,
      'kind'     => $kind,
      'rmb'      => (float) $adj['rmb'],
      'usd'      => (float) $adj['usd'],
      'inr'      => (float) $adj['inr'],
      'added_by' => !empty($adj['added_by_name']) ? $adj['added_by_name'] : (!empty($adj['added_by']) ? $adj['added_by'] : '—'),
    ];
  }

  usort($ledger, function ($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
  });

  $totals = [
    'purchase'  => ['rmb' => 0, 'usd' => 0, 'inr' => 0],
    'payment'   => ['rmb' => 0, 'usd' => 0, 'inr' => 0],
    'adj_plus'  => ['rmb' => 0, 'usd' => 0, 'inr' => 0],
    'adj_minus' => ['rmb' => 0, 'usd' => 0, 'inr' => 0],
  ];
  foreach ($ledger as $item) {
    $totals[$item['kind']]['rmb'] += $item['rmb'];
    $totals[$item['kind']]['usd'] += $item['usd'];
    $totals[$item['kind']]['inr'] += $item['inr'];
  }

  $open = $opening_bal ?? ['rmb' => 0, 'usd' => 0, 'inr' => 0];
  $balance = [
    'rmb' => $open['rmb'] + $totals['purchase']['rmb'] - $totals['payment']['rmb'] + $totals['adj_plus']['rmb'] - $totals['adj_minus']['rmb'],
    'usd' => $open['usd'] + $totals['purchase']['usd'] - $totals['payment']['usd'] + $totals['adj_plus']['usd'] - $totals['adj_minus']['usd'],
    'inr' => $open['inr'] + $totals['purchase']['inr'] - $totals['payment']['inr'] + $totals['adj_plus']['inr'] - $totals['adj_minus']['inr'],
  ];

  $rows = $ledger;
  if ($opening_bal !== null) {
    array_unshift($rows, [
      'date'     => $supplier['added_date'] ?? '',
      'batch'    => 'Opening Balance',
      'kind'     => 'opening',
      'rmb'      => $opening_bal['rmb'],
      'usd'      => $opening_bal['usd'],
      'inr'      => $opening_bal['inr'],
      'added_by' => $supplier['added_by_name'] ?? '—',
    ]);
  }

  $net_adj_inr = $totals['adj_plus']['inr'] - $totals['adj_minus']['inr'];

  return [
    'rows'        => $rows,
    'totals'      => $totals,
    'net_adj_inr' => $net_adj_inr,
    'balance'     => $balance,
    'isDue'       => $balance['inr'] > 0,
  ];
};

$all = $build_ledger($outstanding ?? [], $payments ?? [], $adjustments ?? [], 'total_actual_usd', 'total_actual_inr', 'total_actual_rmb', $opening);
$official = $build_ledger($outstanding ?? [], $official_payments ?? [], $official_adjustments ?? [], 'official_usd', 'official_inr');
$unofficial = $build_ledger($outstanding ?? [], $unofficial_payments ?? [], $unofficial_adjustments ?? [], 'unofficial_usd', 'unofficial_inr');

$render_table = function ($ledger, $show_rmb) use ($kinds) {
  $rows = $ledger['rows'];
  $balance = $ledger['balance'];
  $isDue = $ledger['isDue'];
  $amtCls = $isDue ? 'balance-text-due' : 'balance-text-credit';
  ?>
  <div class="table-responsive">
    <table class="table table-borderless mb-0 align-middle ledger-table fs-12">
      <thead>
        <tr>
          <th class="text-start px-3 py-2 text-muted fw-semibold">Date</th>
          <th class="text-start px-2 py-2 text-muted fw-semibold">Type</th>
          <th class="text-start px-2 py-2 text-muted fw-semibold">Batch / Remark</th>
          <?php if ($show_rmb): ?>
            <th class="text-end px-2 py-2 text-muted fw-semibold">RMB</th>
          <?php endif; ?>
          <th class="text-end px-2 py-2 text-muted fw-semibold">USD</th>
          <th class="text-end px-2 py-2 text-muted fw-semibold">INR</th>
          <th class="text-start px-3 py-2 text-muted fw-semibold">Added By</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="<?= $show_rmb ? 7 : 6 ?>" class="py-5 text-center text-muted fs-13">No transactions found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $item):
            $k = $kinds[$item['kind']];
            $date = ($item['kind'] === 'opening' || empty($item['date'])) ? '' : date('d M y', strtotime($item['date']));
          ?>
            <tr class="ledger-row <?= $k['row'] ?>">
              <td class="px-3 py-2 text-secondary fs-11 text-nowrap"><?= $date ?></td>
              <td class="px-2 py-2"><span class="type-badge <?= $k['badge'] ?>"><?= $k['label'] ?></span></td>
              <td class="px-2 py-2"><div class="fw-semibold supplier-soft-text fs-11"><?= html_escape($item['batch']) ?></div></td>
              <?php if ($show_rmb): ?>
                <td class="px-2 py-2 text-end <?= $k['amt'] ?>"><?= $k['sign'] ?> <?= number_format($item['rmb'], 2) ?></td>
              <?php endif; ?>
              <td class="px-2 py-2 text-end <?= $k['amt'] ?>"><?= $k['sign'] ?> <?= number_format($item['usd'], 2) ?></td>
              <td class="px-2 py-2 text-end <?= $k['amt'] ?> fs-12"><?= $k['sign'] ?> <?= number_format($item['inr'], 2) ?></td>
              <td class="px-3 py-2 text-muted fs-10 text-nowrap"><?= html_escape($item['added_by'] ?: '—') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr class="<?= $isDue ? 'balance-row-due' : 'balance-row-credit' ?> tfoot-border-top">
          <td colspan="3" class="px-3 py-2 text-end fs-11 fw-bold supplier-main-text">Total Outstanding</td>
          <?php if ($show_rmb): ?>
            <td class="px-2 py-2 text-end fw-bold <?= $amtCls ?> fs-12"><?= number_format($balance['rmb'], 2) ?></td>
          <?php endif; ?>
          <td class="px-2 py-2 text-end fw-bold <?= $amtCls ?> fs-12"><?= number_format($balance['usd'], 2) ?></td>
          <td class="px-2 py-2 text-end fw-bold <?= $amtCls ?> fs-12"><?= number_format($balance['inr'], 2) ?></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php
};
?>

<style>
  .supplier-card-shell,
  .ledger-card-shell {
    border: 1px solid #e8eaed;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
  }
  .card-soft-header { background: #fafbfc; border-bottom: 1px solid #f0f2f5; }
  .fs-10 { font-size: 10px; }
  .fs-11 { font-size: 11px; }
  .fs-12 { font-size: 12px; }
  .fs-13 { font-size: 13px; }
  .fs-15 { font-size: 15px; }
  .track-1 { letter-spacing: 1px; }
  .supplier-main-text { color: #111827; }
  .supplier-soft-text { color: #1f2937; }
  .mono-amount { font-family: "DM Mono", monospace; }
  .supplier-info-divider { border-right: 1px solid #f0f2f5; }
  .supplier-info-col { min-width: 220px; }
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
  }
  .balance-pill-due { color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; }
  .balance-pill-credit { color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; }
  .ledger-table thead th {
    font-size: 10px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    background: #f9fafb;
    border-bottom: 1px solid #e8eaed;
    white-space: nowrap;
  }
  .ledger-row { border-bottom: 1px solid #f3f4f6; }
  .ledger-row-payment { background: #f0fdf4; }
  .ledger-row-payment:hover { background: #dcfce7; }
  .ledger-row-purchase { background: #ffffff; }
  .ledger-row-purchase:hover { background: #f9fafb; }
  .ledger-row-opening { background: #fafbfc; }
  .ledger-row-opening:hover { background: #f3f4f6; }
  .ledger-row-adj-plus { background: #eff6ff; }
  .ledger-row-adj-plus:hover { background: #dbeafe; }
  .ledger-row-adj-minus { background: #fff5f5; }
  .ledger-row-adj-minus:hover { background: #fee2e2; }
  .type-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.6px;
    border-radius: 4px;
    padding: 2px 7px;
    text-transform: uppercase;
  }
  .type-badge-payment { color: #0891b2; background: #ecfeff; border: 1px solid #a5f3fc; }
  .type-badge-po { color: #7c3aed; background: #f5f3ff; border: 1px solid #ddd6fe; }
  .type-badge-opening { color: #4b5563; background: #f3f4f6; border: 1px solid #e5e7eb; }
  .type-badge-adj-plus { color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; }
  .type-badge-adj-minus { color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; }
  .amount-positive { color: #dc2626; font-weight: 600; }
  .amount-negative { color: #16a34a; font-weight: 600; }
  .tfoot-border-top { border-top: 2px solid #e8eaed; }
  .balance-row-due { background: #fff5f5; }
  .balance-row-credit { background: #f0fdf4; }
  .balance-text-due { color: #dc2626; }
  .balance-text-credit { color: #16a34a; }
  .ledger-tab {
    border: 0;
    background: transparent;
    padding: 2px 10px 6px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 2px solid transparent;
  }
  .ledger-tab.active {
    color: #111827;
    border-bottom-color: #111827;
  }
</style>

<div class="bg-white supplier-card-shell mb-2">
  <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header">
    <div>
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-1">Supplier</div>
      <div class="fw-semibold supplier-main-text fs-15"><?= html_escape($supplier['name'] ?? '—') ?></div>
    </div>
  </div>

  <div class="d-flex flex-wrap">
    <div class="flex-fill px-3 py-3 supplier-info-divider supplier-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">Address</div>
      <?php if (!empty($addrLines)): ?>
        <div class="fs-13 supplier-soft-text addr-lines"><?= implode('<br>', array_map('html_escape', $addrLines)) ?></div>
      <?php else: ?>
        <div class="fs-13 text-muted">—</div>
      <?php endif; ?>
      <?php if (!empty($locationLine)): ?>
        <div class="fs-11 text-secondary fw-medium mt-1"><?= html_escape($locationLine) ?></div>
      <?php endif; ?>
    </div>

    <div class="flex-fill px-3 py-3 supplier-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">Key Info</div>
      <table class="w-100 key-info-table fs-12">
        <tr>
          <td class="text-secondary fw-medium key-label-col">Contact Person</td>
          <td class="supplier-main-text fw-semibold"><?= html_escape($supplier['contact_name'] ?? '—') ?></td>
        </tr>
        <tr>
          <td class="text-secondary fw-medium">Phone</td>
          <td>
            <?php if (!empty($supplier['contact_no'])): ?>
              <a href="tel:<?= html_escape($supplier['contact_no']) ?>" class="text-decoration-none fw-semibold fs-11 text-primary"><?= html_escape($supplier['contact_no']) ?></a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if (!empty($supplier['gst_name'])): ?>
          <tr>
            <td class="text-secondary fw-medium">GST Name</td>
            <td class="supplier-main-text fw-semibold"><?= html_escape($supplier['gst_name']) ?></td>
          </tr>
        <?php endif; ?>
        <?php if (!empty($supplier['gst_no'])): ?>
          <tr>
            <td class="text-secondary fw-medium">GST No</td>
            <td class="supplier-main-text fw-semibold"><?= html_escape($supplier['gst_no']) ?></td>
          </tr>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>

<div class="bg-white ledger-card-shell mb-5">
  <div class="d-flex align-items-center justify-content-between px-2 py-2 card-soft-header">
    <div class="d-flex align-items-center gap-1">
      <button type="button" class="ledger-tab active" data-tab="all">All</button>
      <button type="button" class="ledger-tab" data-tab="official">Official</button>
      <button type="button" class="ledger-tab" data-tab="unofficial">Unofficial</button>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2" data-tab-panel="all">
      <div class="summary-pill">Opening Balance &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($opening['inr'], 2) ?></strong></div>
      <div class="summary-pill">Purchases &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($all['totals']['purchase']['inr'], 2) ?></strong></div>
      <div class="summary-pill">Payments &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($all['totals']['payment']['inr'], 2) ?></strong></div>
      <div class="summary-pill">Adjustments &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($all['net_adj_inr'], 2) ?></strong></div>
      <div class="balance-pill <?= $all['isDue'] ? 'balance-pill-due' : 'balance-pill-credit' ?>">Balance &nbsp;₹ <?= number_format($all['balance']['inr'], 2) ?></div>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2 d-none" data-tab-panel="official">
      <div class="summary-pill">Purchases &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($official['totals']['purchase']['inr'], 2) ?></strong></div>
      <div class="summary-pill">Payments &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($official['totals']['payment']['inr'], 2) ?></strong></div>
      <div class="summary-pill">Adjustments &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($official['net_adj_inr'], 2) ?></strong></div>
      <div class="balance-pill <?= $official['isDue'] ? 'balance-pill-due' : 'balance-pill-credit' ?>">Balance &nbsp;₹ <?= number_format($official['balance']['inr'], 2) ?></div>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2 d-none" data-tab-panel="unofficial">
      <div class="summary-pill">Purchases &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($unofficial['totals']['purchase']['inr'], 2) ?></strong></div>
      <div class="summary-pill">Payments &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($unofficial['totals']['payment']['inr'], 2) ?></strong></div>
      <div class="summary-pill">Adjustments &nbsp;<strong class="supplier-soft-text mono-amount">₹ <?= number_format($unofficial['net_adj_inr'], 2) ?></strong></div>
      <div class="balance-pill <?= $unofficial['isDue'] ? 'balance-pill-due' : 'balance-pill-credit' ?>">Balance &nbsp;₹ <?= number_format($unofficial['balance']['inr'], 2) ?></div>
    </div>
  </div>

  <div data-tab-panel="all">
    <?php $render_table($all, true); ?>
  </div>
  <div class="d-none" data-tab-panel="official">
    <?php $render_table($official, false); ?>
  </div>
  <div class="d-none" data-tab-panel="unofficial">
    <?php $render_table($unofficial, false); ?>
  </div>
</div>

<script>
  document.querySelectorAll('.ledger-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var tab = this.getAttribute('data-tab');
      document.querySelectorAll('.ledger-tab').forEach(function (b) {
        b.classList.toggle('active', b === btn);
      });
      document.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
        panel.classList.toggle('d-none', panel.getAttribute('data-tab-panel') !== tab);
      });
    });
  });
</script>
