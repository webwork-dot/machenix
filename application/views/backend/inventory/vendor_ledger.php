<?php
$vendor   = $data ?? [];
$ledger   = $ledger ?? [];
$from_date = $from_date ?? ($ledger['from_date'] ?? date('Y-m-d', strtotime('-3 months')));
$to_date   = $to_date ?? ($ledger['to_date'] ?? date('Y-m-d'));
$date_range = $date_range ?? (date('d-m-Y', strtotime($from_date)) . ' - ' . date('d-m-Y', strtotime($to_date)));
$batches   = $batches ?? [];
$selected_batch = $selected_batch ?? ($batch_no ?? ($ledger['batch_no'] ?? ''));

$summary     = $ledger['summary'] ?? [];
$ledger_data = $ledger['ledger_data'] ?? [];

$addrLines = array_filter([
  trim($vendor['address'] ?? ''),
  trim($vendor['address_2'] ?? ''),
  trim($vendor['address_3'] ?? ''),
]);
$cityState = trim(implode(', ', array_filter([
  trim($vendor['city_name'] ?? ''),
  trim($vendor['state_name'] ?? ''),
])));
$pincode = trim($vendor['pincode'] ?? '');
$locationLine = trim($cityState . ($pincode ? " – $pincode" : ''));

$vendor_id = (int) ($id ?? ($vendor['id'] ?? 0));
$base_ledger_url = base_url('inventory/vendor-ledger/' . $vendor_id);

$fmt = function ($n, $show_zero = false) {
  $n = (float) $n;
  if (!$show_zero && abs($n) < 0.00001) {
    return '';
  }
  return number_format($n, 2);
};

$fmt_bal = function ($n) {
  $n = (float) $n;
  $abs = number_format(abs($n), 2);
  $side = ($n > 0.00001) ? 'Cr' : 'Dr';
  return ['amt' => $abs, 'side' => $side, 'raw' => $n];
};

$row_class = function ($row) {
  if (!empty($row['is_opening'])) return 'ledger-row-opening';
  if (!empty($row['is_product'])) return 'ledger-row-product';
  if (!empty($row['is_sub'])) return 'ledger-row-sub';
  $t = $row['entry_type'] ?? '';
  if ($t === 'payment') return 'ledger-row-payment';
  if ($t === 'expense') return 'ledger-row-expense';
  if ($t === 'adjustment') {
    return (stripos($row['vch_type'] ?? '', '+') !== false) ? 'ledger-row-adj-plus' : 'ledger-row-adj-minus';
  }
  return '';
};

$badge_class = function ($row) {
  if (!empty($row['is_opening'])) return 'type-badge-opening';
  $t = $row['entry_type'] ?? '';
  if ($t === 'payment') return 'type-badge-payment';
  if ($t === 'expense') return 'type-badge-expense';
  if ($t === 'adjustment') {
    return (stripos($row['vch_type'] ?? '', '+') !== false) ? 'type-badge-adj-plus' : 'type-badge-adj-minus';
  }
  return 'type-badge-default';
};
?>

<style>
  .vendor-card-shell,
  .ledger-card-shell {
    border: 1px solid #e8eaed;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
  }
  .card-soft-header { background: #fafbfc; border-bottom: 1px solid #f0f2f5; }
  .fs-9 { font-size: 9px; }
  .fs-10 { font-size: 10px; }
  .fs-11 { font-size: 11px; }
  .fs-12 { font-size: 12px; }
  .fs-13 { font-size: 13px; }
  .fs-14 { font-size: 14px; }
  .fs-15 { font-size: 15px; }
  .track-1 { letter-spacing: 1px; }
  .vendor-main-text { color: #111827; }
  .vendor-soft-text { color: #1f2937; }
  .mono-amount { font-family: "DM Mono", monospace; }
  .vendor-info-divider { border-right: 1px solid #f0f2f5; }
  .vendor-info-col { min-width: 220px; }
  .addr-lines { line-height: 1.7; }
  .key-info-table td { padding-top: 3px; padding-bottom: 3px; }
  .key-label-col { width: 38%; }

  /* Summary cards */
  .ledger-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 12px;
  }
  .ledger-summary-card {
    background: #fff;
    border: 1px solid #e8eaed;
    border-radius: 10px;
    padding: 12px 14px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    position: relative;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .ledger-summary-card:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
  }
  .ledger-summary-card .lbl {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #6b7280;
    font-weight: 700;
    margin-bottom: 4px;
  }
  .ledger-summary-card .val {
    font-size: 15px;
    font-weight: 800;
    color: #111827;
  }
  .ledger-summary-card .sub-row {
    font-size: 11px;
    color: #4b5563;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px dashed #f0f2f5;
    line-height: 1.5;
  }
  .curr-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 1px 4px;
    border-radius: 4px;
    background: #eef2f6;
    color: #4b5563;
    margin-right: 2px;
  }

  /* Filters */
  .filter-bar-shell {
    background: #fff;
    border: 1px solid #e8eaed;
    border-radius: 10px;
    padding: 10px 14px;
  }
  .filter-ctrl-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6b7280;
    font-weight: 600;
    display: block;
    margin-bottom: 3px;
  }

  /* Tabs Bar */
  .ledger-tabs-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #f0f2f5;
  }
  .ledger-seg {
    display: inline-flex;
    align-items: center;
    padding: 3px;
    background: #eef0f3;
    border-radius: 8px;
    gap: 2px;
  }
  .ledger-seg .seg-btn {
    appearance: none;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.3px;
    padding: 6px 16px;
    border-radius: 6px;
    line-height: 1.3;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
  }
  .ledger-seg .seg-btn:hover { color: #111827; }
  .ledger-seg .seg-btn.active {
    background: #ffffff;
    color: #111827;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  .ledger-seg.ledger-seg-sub .seg-btn {
    font-weight: 600;
    padding: 5px 14px;
    font-size: 11.5px;
  }

  /* Tables */
  .ledger-table thead th {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: #4b5563;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
    vertical-align: middle;
    padding: 8px 8px !important;
  }
  .ledger-table tbody td {
    font-size: 11px;
    padding: 5px 8px !important;
    white-space: nowrap;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
  }
  .ledger-row { background: #fff; transition: background 0.1s ease; }
  .ledger-row:hover { background: #f8fafc; }
  .ledger-row-opening { background: #f8fafc; font-weight: 700; }
  .ledger-row-product { background: #ffffff; }
  .ledger-row-product td.particulars { padding-left: 24px !important; color: #374151; font-weight: 500; }
  .ledger-row-sub { background: #fafbfc; font-style: italic; color: #64748b; }
  .ledger-row-sub td.particulars { padding-left: 20px !important; }
  .ledger-row-payment { background: #f0fdf4; }
  .ledger-row-payment:hover { background: #dcfce7; }
  .ledger-row-expense { background: #ffffff; }
  .ledger-row-expense:hover { background: #f8fafc; }
  .ledger-row-adj-plus { background: #eff6ff; }
  .ledger-row-adj-plus:hover { background: #dbeafe; }
  .ledger-row-adj-minus { background: #fff5f5; }
  .ledger-row-adj-minus:hover { background: #fee2e2; }

  /* Badges & Balances */
  .type-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.5px;
    border-radius: 4px;
    padding: 2px 7px;
    text-transform: uppercase;
  }
  .type-badge-payment { color: #0891b2; background: #ecfeff; border: 1px solid #a5f3fc; }
  .type-badge-expense { color: #7c3aed; background: #f5f3ff; border: 1px solid #ddd6fe; }
  .type-badge-opening { color: #4b5563; background: #f3f4f6; border: 1px solid #e5e7eb; }
  .type-badge-adj-plus { color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; }
  .type-badge-adj-minus { color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; }
  .type-badge-default { color: #4b5563; background: #f3f4f6; }

  .amount-positive { color: #dc2626; font-weight: 600; }
  .amount-negative { color: #16a34a; font-weight: 600; }
  .bal-side { font-size: 9px; font-weight: 700; margin-left: 3px; }
  .bal-dr { color: #dc2626; }
  .bal-cr { color: #16a34a; }

  .tfoot-strong { background: #f1f5f9; font-weight: 700; border-top: 2px solid #cbd5e1; }
  .tfoot-closing { background: #fef2f2; font-weight: 800; border-top: 1px solid #fecaca; }

  /* Currency visibility toggles */
  body.hide-curr-rmb .col-curr-rmb { display: none !important; }
  body.hide-curr-usd .col-curr-usd { display: none !important; }
  body.hide-curr-inr .col-curr-inr { display: none !important; }

</style>

<!-- Vendor Card -->
<div class="bg-white vendor-card-shell mb-2">
  <div class="d-flex align-items-center justify-content-between p-2 card-soft-header flex-wrap gap-2">
    <div>
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-1">Vendor Ledger</div>
      <div class="fw-bold vendor-main-text fs-15"><?= html_escape($vendor['name'] ?? '—') ?></div>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
      <a href="<?= base_url('inventory/my_company') ?>" class="btn btn-sm btn-outline-secondary fs-11">
        <i class="feather icon-arrow-left me-1"></i> Back to Vendors
      </a>
    </div>
  </div>

  <div class="d-flex flex-wrap">
    <div class="flex-fill p-2 vendor-info-divider vendor-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">Address</div>
      <?php if (!empty($vendor['gst_name'])): ?>
        <div class="fs-13 fw-semibold vendor-main-text mb-1"><?= html_escape($vendor['gst_name']) ?></div>
      <?php endif; ?>
      <?php if (!empty($addrLines)): ?>
        <div class="fs-13 vendor-soft-text addr-lines"><?= implode('<br>', array_map('html_escape', $addrLines)) ?></div>
      <?php else: ?>
        <div class="fs-13 text-muted">—</div>
      <?php endif; ?>
      <?php if (!empty($locationLine)): ?>
        <div class="fs-11 text-secondary fw-medium mt-1"><?= html_escape($locationLine) ?></div>
      <?php endif; ?>
      <?php if (!empty($vendor['country_name'])): ?>
        <div class="fs-11 text-muted"><?= html_escape($vendor['country_name']) ?></div>
      <?php endif; ?>
    </div>

    <div class="flex-fill p-2 vendor-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 text-muted mb-2">Key Info</div>
      <table class="w-100 key-info-table fs-12">
        <tr>
          <td class="text-secondary fw-medium key-label-col">Contact Person</td>
          <td class="vendor-main-text fw-semibold"><?= html_escape($vendor['contact_name'] ?? '—') ?></td>
        </tr>
        <tr>
          <td class="text-secondary fw-medium">Phone</td>
          <td>
            <?php if (!empty($vendor['contact_no'])): ?>
              <a href="tel:<?= html_escape($vendor['contact_no']) ?>" class="text-decoration-none fw-semibold fs-11 text-primary"><?= html_escape($vendor['contact_no']) ?></a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if (!empty($vendor['gst_no'])): ?>
          <tr>
            <td class="text-secondary fw-medium">GST No</td>
            <td class="vendor-main-text fw-semibold"><?= html_escape($vendor['gst_no']) ?></td>
          </tr>
        <?php endif; ?>
        <?php if (!empty($vendor['email'])): ?>
          <tr>
            <td class="text-secondary fw-medium">Email</td>
            <td class="vendor-main-text fw-semibold"><?= html_escape($vendor['email']) ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td class="text-secondary fw-medium">Initial Outstanding</td>
          <td class="vendor-main-text fw-semibold">
            <span class="curr-badge">INR</span> ₹ <?= number_format((float)($vendor['outstanding_inr'] ?? $vendor['outstanding'] ?? 0), 2) ?>
            &nbsp; <span class="curr-badge">USD</span> $ <?= number_format((float)($vendor['outstanding_usd'] ?? 0), 2) ?>
            &nbsp; <span class="curr-badge">RMB</span> ¥ <?= number_format((float)($vendor['outstanding_rmb'] ?? 0), 2) ?>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>

<!-- Filters Bar -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<div class="filter-bar-shell mb-2">
  <form method="get" action="<?= html_escape($base_ledger_url) ?>" class="d-flex flex-wrap align-items-end justify-content-between gap-2" id="ledgerFilterForm">
    <div class="d-flex flex-wrap align-items-end gap-2">
      <!-- Date Range -->
      <div style="min-width: 250px;">
        <label class="filter-ctrl-label"><i class="feather icon-calendar me-1"></i> Date Range</label>
        <input type="text" autocomplete="off" class="form-control form-control-sm bg-white datepicker_report"
               name="date_range" value="<?= html_escape($date_range) ?>" placeholder="Select date range" readonly>
      </div>

      <!-- Batch Filter -->
      <div style="min-width: 190px;">
        <label class="filter-ctrl-label"><i class="feather icon-layers me-1"></i> Batch No</label>
        <select name="batch_no" class="form-select form-select-sm" id="batchSelect" onchange="$('#ledgerFilterForm').submit();">
          <option value="">All Batches</option>
          <?php foreach ($batches as $b):
            $bval = $b['voucher_no'] ?? '';
            if (empty($bval)) continue;
          ?>
            <option value="<?= html_escape($bval) ?>" <?= ($selected_batch === $bval) ? 'selected' : '' ?>>
              <?= html_escape($bval) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Vch Type Filter (client-side) -->
      <div style="min-width: 150px;">
        <label class="filter-ctrl-label"><i class="feather icon-filter me-1"></i> Vch Type</label>
        <select class="form-select form-select-sm" id="vchTypeFilter">
          <option value="all">All Types</option>
          <option value="Expense">Expense</option>
          <option value="Payment">Payment</option>
          <option value="Adjustment">Adjustment</option>
        </select>
      </div>

      <button type="submit" class="btn btn-sm btn-primary fs-12 px-3 fw-semibold">Apply</button>
      <?php if (!empty($selected_batch)): ?>
        <a href="<?= html_escape($base_ledger_url) ?>" class="btn btn-sm btn-outline-secondary fs-12">Clear Batch</a>
      <?php endif; ?>
    </div>

    <!-- Right Controls: Currency Checkboxes -->
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="d-flex align-items-center gap-2">
        <span class="fs-10 text-uppercase fw-bold text-muted">Currencies:</span>
        <label class="form-check-label fs-11 fw-semibold text-secondary d-flex align-items-center gap-1 cursor-pointer">
          <input type="checkbox" class="form-check-input mt-0 curr-toggle" data-curr="rmb" checked> RMB
        </label>
        <label class="form-check-label fs-11 fw-semibold text-secondary d-flex align-items-center gap-1 cursor-pointer">
          <input type="checkbox" class="form-check-input mt-0 curr-toggle" data-curr="usd" checked> USD
        </label>
        <label class="form-check-label fs-11 fw-semibold text-secondary d-flex align-items-center gap-1 cursor-pointer">
          <input type="checkbox" class="form-check-input mt-0 curr-toggle" data-curr="inr" checked> INR
        </label>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  var $input = $('.datepicker_report');
  var $form = $('#ledgerFilterForm');

  $input.daterangepicker({
    autoUpdateInput: true,
    autoApply: false,
    alwaysShowCalendars: false,
    showCustomRangeLabel: true,
    linkedCalendars: false,
    opens: 'left',
    startDate: moment('<?= html_escape($from_date) ?>', 'YYYY-MM-DD'),
    endDate: moment('<?= html_escape($to_date) ?>', 'YYYY-MM-DD'),
    ranges: {
      '3 months': [moment().subtract(3, 'months'), moment()],
      '6 months': [moment().subtract(6, 'months'), moment()],
      '12 months': [moment().subtract(12, 'months'), moment()]
    },
    locale: {
      format: 'DD-MM-YYYY',
      customRangeLabel: 'Custom Range',
      applyLabel: 'Apply',
      cancelLabel: 'Cancel'
    }
  });

  $input.on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    $form.trigger('submit');
  });
})();
</script>

<!-- Summary Cards (5 Cards) -->
<?php
$s_open = $summary['opening'] ?? ['total' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'white' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'black' => ['rmb'=>0,'usd'=>0,'inr'=>0]];
$s_exp  = $summary['expenses'] ?? ($summary['purchases'] ?? ['total' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'white' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'black' => ['rmb'=>0,'usd'=>0,'inr'=>0]]);
$s_pay  = $summary['payments'] ?? ['total' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'official' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'unofficial' => ['rmb'=>0,'usd'=>0,'inr'=>0]];
$s_adj  = $summary['adjustments'] ?? ['total' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'official' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'unofficial' => ['rmb'=>0,'usd'=>0,'inr'=>0]];
$s_out  = $summary['outstanding'] ?? ['total' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'official' => ['rmb'=>0,'usd'=>0,'inr'=>0], 'unofficial' => ['rmb'=>0,'usd'=>0,'inr'=>0]];
?>
<div class="ledger-summary-grid mb-2">
  <!-- Card 1: Opening Balance -->
  <div class="ledger-summary-card">
    <div class="lbl">Opening Balance</div>
    <div class="val text-dark">₹ <?= number_format((float)($s_open['total']['inr'] ?? 0), 2) ?></div>
    <div class="fs-10 text-muted mt-1">
      <span class="curr-badge">USD</span> $ <?= number_format((float)($s_open['total']['usd'] ?? 0), 2) ?> &nbsp;
      <span class="curr-badge">RMB</span> ¥ <?= number_format((float)($s_open['total']['rmb'] ?? 0), 2) ?>
    </div>
    <div class="sub-row">
      <strong>White:</strong> ₹ <?= number_format((float)($s_open['white']['inr'] ?? 0), 2) ?> &nbsp;|&nbsp; $ <?= number_format((float)($s_open['white']['usd'] ?? 0), 2) ?><br>
      <strong>Black:</strong> ₹ <?= number_format((float)($s_open['black']['inr'] ?? 0), 2) ?> &nbsp;|&nbsp; $ <?= number_format((float)($s_open['black']['usd'] ?? 0), 2) ?>
    </div>
  </div>

  <!-- Card 2: Expenses -->
  <div class="ledger-summary-card">
    <div class="lbl">Expenses</div>
    <div class="val text-danger">₹ <?= number_format((float)($s_exp['total']['inr'] ?? 0), 2) ?></div>
    <div class="fs-10 text-muted mt-1">
      <span class="curr-badge">USD</span> $ <?= number_format((float)($s_exp['total']['usd'] ?? 0), 2) ?> &nbsp;
      <span class="curr-badge">RMB</span> ¥ <?= number_format((float)($s_exp['total']['rmb'] ?? 0), 2) ?>
    </div>
    <div class="sub-row">
      <strong>White:</strong> ₹ <?= number_format((float)($s_exp['white']['inr'] ?? 0), 2) ?> &nbsp;|&nbsp; $ <?= number_format((float)($s_exp['white']['usd'] ?? 0), 2) ?><br>
      <strong>Black:</strong> ₹ <?= number_format((float)($s_exp['black']['inr'] ?? 0), 2) ?> &nbsp;|&nbsp; $ <?= number_format((float)($s_exp['black']['usd'] ?? 0), 2) ?>
    </div>
  </div>

  <!-- Card 3: Payments -->
  <div class="ledger-summary-card">
    <div class="lbl">Payments</div>
    <div class="val text-success">₹ <?= number_format((float)($s_pay['total']['inr'] ?? 0), 2) ?></div>
    <div class="fs-10 text-muted mt-1">
      <span class="curr-badge">USD</span> $ <?= number_format((float)($s_pay['total']['usd'] ?? 0), 2) ?> &nbsp;
      <span class="curr-badge">RMB</span> ¥ <?= number_format((float)($s_pay['total']['rmb'] ?? 0), 2) ?>
    </div>
    <div class="sub-row">
      <strong>Official:</strong> ₹ <?= number_format((float)($s_pay['official']['inr'] ?? 0), 2) ?> &nbsp;|&nbsp; $ <?= number_format((float)($s_pay['official']['usd'] ?? 0), 2) ?><br>
      <strong>Unofficial:</strong> ₹ <?= number_format((float)($s_pay['unofficial']['inr'] ?? 0), 2) ?> &nbsp;|&nbsp; $ <?= number_format((float)($s_pay['unofficial']['usd'] ?? 0), 2) ?>
    </div>
  </div>

  <!-- Card 4: Adjustments -->
  <div class="ledger-summary-card">
    <div class="lbl">Adjustments</div>
    <?php $adj_tot_inr = (float)($s_adj['total']['inr'] ?? 0); ?>
    <div class="val text-primary"><?= ($adj_tot_inr >= 0 ? '+' : '−') ?> ₹ <?= number_format(abs($adj_tot_inr), 2) ?></div>
    <div class="fs-10 text-muted mt-1">
      <span class="curr-badge">USD</span> <?= ((float)($s_adj['total']['usd'] ?? 0) >= 0 ? '+' : '−') ?> $ <?= number_format(abs((float)($s_adj['total']['usd'] ?? 0)), 2) ?> &nbsp;
      <span class="curr-badge">RMB</span> <?= ((float)($s_adj['total']['rmb'] ?? 0) >= 0 ? '+' : '−') ?> ¥ <?= number_format(abs((float)($s_adj['total']['rmb'] ?? 0)), 2) ?>
    </div>
    <div class="sub-row">
      <strong>Official:</strong> <?= ((float)($s_adj['official']['inr'] ?? 0) >= 0 ? '+' : '−') ?> ₹ <?= number_format(abs((float)($s_adj['official']['inr'] ?? 0)), 2) ?><br>
      <strong>Unofficial:</strong> <?= ((float)($s_adj['unofficial']['inr'] ?? 0) >= 0 ? '+' : '−') ?> ₹ <?= number_format(abs((float)($s_adj['unofficial']['inr'] ?? 0)), 2) ?>
    </div>
  </div>

  <!-- Card 5: Outstanding -->
  <div class="ledger-summary-card">
    <div class="lbl">Outstanding</div>
    <?php
      $out_tot_inr = (float)($s_out['total']['inr'] ?? 0);
      $out_tot_side = ($out_tot_inr > 0.00001) ? 'Cr' : 'Dr';
    ?>
    <div class="val <?= ($out_tot_inr > 0.00001) ? 'text-danger' : 'text-success' ?>">
      ₹ <?= number_format(abs($out_tot_inr), 2) ?> <span class="fs-11 fw-bold"><?= $out_tot_side ?></span>
    </div>
    <div class="fs-10 text-muted mt-1">
      <span class="curr-badge">USD</span> $ <?= number_format(abs((float)($s_out['total']['usd'] ?? 0)), 2) ?> <?= ((float)($s_out['total']['usd'] ?? 0) > 0.00001) ? 'Cr' : 'Dr' ?> &nbsp;
      <span class="curr-badge">RMB</span> ¥ <?= number_format(abs((float)($s_out['total']['rmb'] ?? 0)), 2) ?> <?= ((float)($s_out['total']['rmb'] ?? 0) > 0.00001) ? 'Cr' : 'Dr' ?>
    </div>
    <div class="sub-row">
      <strong>Official:</strong> ₹ <?= number_format(abs((float)($s_out['official']['inr'] ?? 0)), 2) ?> <?= ((float)($s_out['official']['inr'] ?? 0) > 0.00001) ? 'Cr' : 'Dr' ?><br>
      <strong>Unofficial:</strong> ₹ <?= number_format(abs((float)($s_out['unofficial']['inr'] ?? 0)), 2) ?> <?= ((float)($s_out['unofficial']['inr'] ?? 0) > 0.00001) ? 'Cr' : 'Dr' ?>
    </div>
  </div>
</div>

<!-- Ledger Shell -->
<div class="bg-white ledger-card-shell mb-5" id="ledgerShell">
  <!-- Tabs Navigation -->
  <div class="ledger-tabs-bar">
    <!-- Subtabs: ALL, WHITE, BLACK -->
    <div class="ledger-seg" id="ledgerModeTabs" role="tablist">
      <button type="button" class="seg-btn active" data-mode="all">ALL</button>
      <button type="button" class="seg-btn" data-mode="white">WHITE</button>
      <button type="button" class="seg-btn" data-mode="black">BLACK</button>
    </div>

    <!-- Main View Tabs: Bill Wise, Product Wise -->
    <div class="ledger-seg ledger-seg-sub" id="ledgerViewTabs" role="tablist">
      <button type="button" class="seg-btn active" data-view="bill">Bill Wise</button>
      <button type="button" class="seg-btn" data-view="product">Product Wise</button>
    </div>
  </div>

  <?php foreach (['all', 'white', 'black'] as $m):
    $m_data = $ledger_data[$m] ?? [];
    $b_rows = $m_data['bill_rows'] ?? [];
    $p_rows = $m_data['prod_rows'] ?? [];
    $totals = $m_data['col_totals'] ?? ['rmb'=>0,'usd'=>0,'inr'=>0];
    $closing = $m_data['closing'] ?? ['rmb'=>0,'usd'=>0,'inr'=>0];
    $is_active_mode = ($m === 'all');
  ?>
    <div class="ledger-mode-panel <?= $is_active_mode ? '' : 'd-none' ?>" data-mode-panel="<?= $m ?>">
      <!-- 1. BILL WISE VIEW -->
      <div class="table-responsive ledger-view-panel" data-view-panel="bill">
        <table class="table table-borderless mb-0 align-middle ledger-table">
          <thead>
            <tr>
              <th class="ps-3">Date</th>
              <th>Particulars</th>
              <th>Vch Type</th>
              <th>Batch No / Vch No</th>
              <th class="col-curr col-curr-rmb text-end">RMB</th>
              <th class="col-curr col-curr-usd text-end">USD</th>
              <th class="col-curr col-curr-inr text-end">INR</th>
              <th class="col-curr col-curr-rmb text-end">Bal RMB</th>
              <th class="col-curr col-curr-rmb text-start ps-1"></th>
              <th class="col-curr col-curr-usd text-end">Bal USD</th>
              <th class="col-curr col-curr-usd text-start ps-1"></th>
              <th class="col-curr col-curr-inr text-end">Bal INR</th>
              <th class="col-curr col-curr-inr text-start ps-1"></th>
              <th class="pe-3">Added By</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($b_rows)): ?>
              <tr><td colspan="14" class="text-center py-4 text-muted">No transactions in this period.</td></tr>
            <?php else: ?>
              <?php foreach ($b_rows as $row):
                $is_op = !empty($row['is_opening']);
                $date_disp = ($is_op || empty($row['date'])) ? '' : date('d M Y', strtotime($row['date']));
                $b_rmb = $fmt_bal($row['bal_rmb'] ?? 0);
                $b_usd = $fmt_bal($row['bal_usd'] ?? 0);
                $b_inr = $fmt_bal($row['bal_inr'] ?? 0);
                $amt_cls = function ($val) {
                  $val = (float)$val;
                  if (abs($val) < 0.00001) return 'text-muted';
                  return $val < 0 ? 'amount-negative' : 'amount-positive';
                };
              ?>
                <tr class="ledger-row <?= $row_class($row) ?>" data-vch-type="<?= html_escape($row['entry_type'] ?? '') ?>">
                  <td class="ps-3 text-secondary text-nowrap fw-medium"><?= html_escape($date_disp) ?></td>
                  <td class="fw-semibold vendor-soft-text"><?= html_escape($row['particulars'] ?? '') ?></td>
                  <td>
                    <?php if (!$is_op && !empty($row['vch_type'])): ?>
                      <span class="type-badge <?= $badge_class($row) ?>"><?= html_escape($row['vch_type']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td><div class="fw-semibold vendor-main-text fs-11"><?= html_escape($row['batch_vch'] ?? '') ?></div></td>
                  
                  <!-- RMB -->
                  <td class="col-curr col-curr-rmb text-end <?= $amt_cls($row['rmb'] ?? 0) ?>">
                    <?= $fmt($row['rmb'] ?? 0) ?>
                  </td>
                  <!-- USD -->
                  <td class="col-curr col-curr-usd text-end <?= $amt_cls($row['usd'] ?? 0) ?>">
                    <?= $fmt($row['usd'] ?? 0) ?>
                  </td>
                  <!-- INR -->
                  <td class="col-curr col-curr-inr text-end <?= $amt_cls($row['inr'] ?? 0) ?>">
                    <?= $fmt($row['inr'] ?? 0) ?>
                  </td>

                  <!-- Bal RMB -->
                  <td class="col-curr col-curr-rmb text-end fw-semibold <?= ($b_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                    <?= $b_rmb['amt'] ?>
                  </td>
                  <td class="col-curr col-curr-rmb text-start ps-1 fs-9 fw-bold <?= ($b_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                    <?= $b_rmb['side'] ?>
                  </td>

                  <!-- Bal USD -->
                  <td class="col-curr col-curr-usd text-end fw-semibold <?= ($b_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                    <?= $b_usd['amt'] ?>
                  </td>
                  <td class="col-curr col-curr-usd text-start ps-1 fs-9 fw-bold <?= ($b_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                    <?= $b_usd['side'] ?>
                  </td>

                  <!-- Bal INR -->
                  <td class="col-curr col-curr-inr text-end fw-semibold <?= ($b_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                    <?= $b_inr['amt'] ?>
                  </td>
                  <td class="col-curr col-curr-inr text-start ps-1 fs-9 fw-bold <?= ($b_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                    <?= $b_inr['side'] ?>
                  </td>

                  <td class="pe-3 fs-10 text-muted text-nowrap"><?= html_escape($row['added_by'] ?? '—') ?></td>
                </tr>


              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <?php
              $cl_rmb = $fmt_bal($closing['rmb'] ?? 0);
              $cl_usd = $fmt_bal($closing['usd'] ?? 0);
              $cl_inr = $fmt_bal($closing['inr'] ?? 0);
            ?>
            <tr class="tfoot-strong">
              <td colspan="4" class="ps-3 text-end fw-bold vendor-main-text">Current Total :</td>
              <td class="col-curr col-curr-rmb text-end fw-bold"><?= number_format((float)($totals['rmb'] ?? 0), 2) ?></td>
              <td class="col-curr col-curr-usd text-end fw-bold"><?= number_format((float)($totals['usd'] ?? 0), 2) ?></td>
              <td class="col-curr col-curr-inr text-end fw-bold"><?= number_format((float)($totals['inr'] ?? 0), 2) ?></td>
              <td class="col-curr col-curr-rmb" colspan="2"></td>
              <td class="col-curr col-curr-usd" colspan="2"></td>
              <td class="col-curr col-curr-inr" colspan="2"></td>
              <td class="pe-3"></td>
            </tr>
            <tr class="tfoot-closing">
              <td colspan="4" class="ps-3 text-end fw-bold text-danger">Closing Balance :</td>
              <td class="col-curr col-curr-rmb text-end"></td>
              <td class="col-curr col-curr-usd text-end"></td>
              <td class="col-curr col-curr-inr text-end"></td>
              
              <!-- Closing Bal RMB -->
              <td class="col-curr col-curr-rmb text-end fw-bold <?= ($cl_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_rmb['amt'] ?>
              </td>
              <td class="col-curr col-curr-rmb text-start ps-1 fs-9 fw-bold <?= ($cl_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_rmb['side'] ?>
              </td>

              <!-- Closing Bal USD -->
              <td class="col-curr col-curr-usd text-end fw-bold <?= ($cl_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_usd['amt'] ?>
              </td>
              <td class="col-curr col-curr-usd text-start ps-1 fs-9 fw-bold <?= ($cl_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_usd['side'] ?>
              </td>

              <!-- Closing Bal INR -->
              <td class="col-curr col-curr-inr text-end fw-bold <?= ($cl_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_inr['amt'] ?>
              </td>
              <td class="col-curr col-curr-inr text-start ps-1 fs-9 fw-bold <?= ($cl_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_inr['side'] ?>
              </td>

              <td class="pe-3"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- 2. PRODUCT WISE VIEW -->
      <div class="table-responsive ledger-view-panel d-none" data-view-panel="product">
        <table class="table table-borderless mb-0 align-middle ledger-table">
          <thead>
            <tr>
              <th class="ps-3">Date</th>
              <th>Particulars</th>
              <th>Vch Type</th>
              <th>Batch No / Vch No</th>
              <th class="text-end">Qty</th>
              <th class="col-curr col-curr-rmb text-end">Per Pc RMB</th>
              <th class="col-curr col-curr-rmb text-end">RMB</th>
              <th class="col-curr col-curr-usd text-end">Per Pc USD</th>
              <th class="col-curr col-curr-usd text-end">USD</th>
              <th class="col-curr col-curr-inr text-end">Per Pc INR</th>
              <th class="col-curr col-curr-inr text-end">INR</th>
              <th class="col-curr col-curr-rmb text-end">Bal RMB</th>
              <th class="col-curr col-curr-rmb text-start ps-1"></th>
              <th class="col-curr col-curr-usd text-end">Bal USD</th>
              <th class="col-curr col-curr-usd text-start ps-1"></th>
              <th class="col-curr col-curr-inr text-end">Bal INR</th>
              <th class="col-curr col-curr-inr text-start ps-1"></th>
              <th class="pe-3">Added By</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($p_rows)): ?>
              <tr><td colspan="18" class="text-center py-4 text-muted">No transactions in this period.</td></tr>
            <?php else: ?>
              <?php foreach ($p_rows as $row):
                $is_op = !empty($row['is_opening']);
                $is_pr = !empty($row['is_product']);
                $is_sb = !empty($row['is_sub']);
                $is_hd = !empty($row['is_header']);

                $date_disp = ($is_op || $is_pr || $is_sb || empty($row['date'])) ? '' : date('d M Y', strtotime($row['date']));
                $has_bal = array_key_exists('bal_rmb', $row) && $row['bal_rmb'] !== null;
                $b_rmb = $has_bal ? $fmt_bal($row['bal_rmb']) : null;
                $b_usd = $has_bal ? $fmt_bal($row['bal_usd']) : null;
                $b_inr = $has_bal ? $fmt_bal($row['bal_inr']) : null;

                $amt_cls = function ($val) {
                  $val = (float)$val;
                  if (abs($val) < 0.00001) return 'text-muted';
                  return $val < 0 ? 'amount-negative' : 'amount-positive';
                };
              ?>
                <tr class="ledger-row <?= $row_class($row) ?>" data-vch-type="<?= html_escape($row['entry_type'] ?? '') ?>">
                  <td class="ps-3 text-secondary text-nowrap fw-medium"><?= html_escape($date_disp) ?></td>
                  <td class="particulars <?= $is_hd ? 'fw-bold vendor-main-text' : ($is_op ? 'fw-bold' : '') ?>">
                    <?php if ($is_pr): ?>
                      <i class="feather icon-corner-down-right text-muted me-1 fs-10"></i>
                    <?php endif; ?>
                    <?= html_escape($row['particulars'] ?? '') ?>
                  </td>
                  <td>
                    <?php if (!$is_op && !$is_pr && !$is_sb && !empty($row['vch_type'])): ?>
                      <span class="type-badge <?= $badge_class($row) ?>"><?= html_escape($row['vch_type']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!$is_pr && !$is_sb): ?>
                      <div class="fw-semibold vendor-main-text fs-11"><?= html_escape($row['batch_vch'] ?? '') ?></div>
                    <?php endif; ?>
                  </td>

                  <!-- Qty -->
                  <td class="text-end fw-semibold text-dark">
                    <?= (isset($row['qty']) && $row['qty'] !== null) ? number_format((float)$row['qty'], 0) : '' ?>
                  </td>

                  <!-- RMB Rates & Amount -->
                  <td class="col-curr col-curr-rmb text-end text-muted">
                    <?= $is_pr ? $fmt($row['rate_rmb'] ?? 0) : '' ?>
                  </td>
                  <td class="col-curr col-curr-rmb text-end <?= $amt_cls($row['rmb'] ?? 0) ?>">
                    <?= $fmt($row['rmb'] ?? 0) ?>
                  </td>

                  <!-- USD Rates & Amount -->
                  <td class="col-curr col-curr-usd text-end text-muted">
                    <?= $is_pr ? $fmt($row['rate_usd'] ?? 0) : '' ?>
                  </td>
                  <td class="col-curr col-curr-usd text-end <?= $amt_cls($row['usd'] ?? 0) ?>">
                    <?= $fmt($row['usd'] ?? 0) ?>
                  </td>

                  <!-- INR Rates & Amount -->
                  <td class="col-curr col-curr-inr text-end text-muted">
                    <?= $is_pr ? $fmt($row['rate_inr'] ?? 0) : '' ?>
                  </td>
                  <td class="col-curr col-curr-inr text-end <?= $amt_cls($row['inr'] ?? 0) ?>">
                    <?= $fmt($row['inr'] ?? 0) ?>
                  </td>

                  <!-- Bal RMB -->
                  <?php if ($b_rmb): ?>
                    <td class="col-curr col-curr-rmb text-end fw-semibold <?= ($b_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                      <?= $b_rmb['amt'] ?>
                    </td>
                    <td class="col-curr col-curr-rmb text-start ps-1 fs-9 fw-bold <?= ($b_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                      <?= $b_rmb['side'] ?>
                    </td>
                  <?php else: ?>
                    <td class="col-curr col-curr-rmb"></td>
                    <td class="col-curr col-curr-rmb"></td>
                  <?php endif; ?>

                  <!-- Bal USD -->
                  <?php if ($b_usd): ?>
                    <td class="col-curr col-curr-usd text-end fw-semibold <?= ($b_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                      <?= $b_usd['amt'] ?>
                    </td>
                    <td class="col-curr col-curr-usd text-start ps-1 fs-9 fw-bold <?= ($b_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                      <?= $b_usd['side'] ?>
                    </td>
                  <?php else: ?>
                    <td class="col-curr col-curr-usd"></td>
                    <td class="col-curr col-curr-usd"></td>
                  <?php endif; ?>

                  <!-- Bal INR -->
                  <?php if ($b_inr): ?>
                    <td class="col-curr col-curr-inr text-end fw-semibold <?= ($b_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                      <?= $b_inr['amt'] ?>
                    </td>
                    <td class="col-curr col-curr-inr text-start ps-1 fs-9 fw-bold <?= ($b_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                      <?= $b_inr['side'] ?>
                    </td>
                  <?php else: ?>
                    <td class="col-curr col-curr-inr"></td>
                    <td class="col-curr col-curr-inr"></td>
                  <?php endif; ?>

                  <td class="pe-3 fs-10 text-muted text-nowrap"><?= (!$is_pr && !$is_sb) ? html_escape($row['added_by'] ?? '—') : '' ?></td>
                </tr>


              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <?php
              $cl_rmb = $fmt_bal($closing['rmb'] ?? 0);
              $cl_usd = $fmt_bal($closing['usd'] ?? 0);
              $cl_inr = $fmt_bal($closing['inr'] ?? 0);
            ?>
            <tr class="tfoot-strong">
              <td colspan="4" class="ps-3 text-end fw-bold vendor-main-text">Current Total :</td>
              <td></td>
              <td class="col-curr col-curr-rmb"></td>
              <td class="col-curr col-curr-rmb text-end fw-bold"><?= number_format((float)($totals['rmb'] ?? 0), 2) ?></td>
              <td class="col-curr col-curr-usd"></td>
              <td class="col-curr col-curr-usd text-end fw-bold"><?= number_format((float)($totals['usd'] ?? 0), 2) ?></td>
              <td class="col-curr col-curr-inr"></td>
              <td class="col-curr col-curr-inr text-end fw-bold"><?= number_format((float)($totals['inr'] ?? 0), 2) ?></td>
              <td class="col-curr col-curr-rmb" colspan="2"></td>
              <td class="col-curr col-curr-usd" colspan="2"></td>
              <td class="col-curr col-curr-inr" colspan="2"></td>
              <td class="pe-3"></td>
            </tr>
            <tr class="tfoot-closing">
              <td colspan="4" class="ps-3 text-end fw-bold text-danger">Closing Balance :</td>
              <td></td>
              <td class="col-curr col-curr-rmb"></td>
              <td class="col-curr col-curr-rmb text-end"></td>
              <td class="col-curr col-curr-usd"></td>
              <td class="col-curr col-curr-usd text-end"></td>
              <td class="col-curr col-curr-inr"></td>
              <td class="col-curr col-curr-inr text-end"></td>

              <!-- Closing Bal RMB -->
              <td class="col-curr col-curr-rmb text-end fw-bold <?= ($cl_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_rmb['amt'] ?>
              </td>
              <td class="col-curr col-curr-rmb text-start ps-1 fs-9 fw-bold <?= ($cl_rmb['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_rmb['side'] ?>
              </td>

              <!-- Closing Bal USD -->
              <td class="col-curr col-curr-usd text-end fw-bold <?= ($cl_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_usd['amt'] ?>
              </td>
              <td class="col-curr col-curr-usd text-start ps-1 fs-9 fw-bold <?= ($cl_usd['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_usd['side'] ?>
              </td>

              <!-- Closing Bal INR -->
              <td class="col-curr col-curr-inr text-end fw-bold <?= ($cl_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_inr['amt'] ?>
              </td>
              <td class="col-curr col-curr-inr text-start ps-1 fs-9 fw-bold <?= ($cl_inr['raw'] > 0.00001) ? 'bal-cr' : 'bal-dr' ?>">
                <?= $cl_inr['side'] ?>
              </td>

              <td class="pe-3"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
(function () {
  var currentMode = 'all';
  var currentView = 'bill';

  // 1. Mode Tabs (ALL, WHITE, BLACK)
  document.querySelectorAll('#ledgerModeTabs .seg-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('#ledgerModeTabs .seg-btn').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      currentMode = btn.getAttribute('data-mode') || 'all';

      document.querySelectorAll('.ledger-mode-panel').forEach(function (p) {
        if (p.getAttribute('data-mode-panel') === currentMode) {
          p.classList.remove('d-none');
        } else {
          p.classList.add('d-none');
        }
      });
    });
  });

  // 2. View Tabs (Bill Wise, Product Wise)
  document.querySelectorAll('#ledgerViewTabs .seg-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('#ledgerViewTabs .seg-btn').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      currentView = btn.getAttribute('data-view') || 'bill';

      document.querySelectorAll('.ledger-view-panel').forEach(function (panel) {
        if (panel.getAttribute('data-view-panel') === currentView) {
          panel.classList.remove('d-none');
        } else {
          panel.classList.add('d-none');
        }
      });
    });
  });

  // 3. Currency Column Checkboxes
  document.querySelectorAll('.curr-toggle').forEach(function (cb) {
    cb.addEventListener('change', function () {
      var curr = this.getAttribute('data-curr');
      if (this.checked) {
        document.body.classList.remove('hide-curr-' + curr);
      } else {
        document.body.classList.add('hide-curr-' + curr);
      }
    });
  });

  // 4. Client-side Voucher Type Filter
  var vchSelect = document.getElementById('vchTypeFilter');
  if (vchSelect) {
    vchSelect.addEventListener('change', function () {
      var filter = this.value.toLowerCase();
      document.querySelectorAll('.ledger-table tbody tr.ledger-row').forEach(function (tr) {
        if (tr.classList.contains('ledger-row-opening')) return; // always keep opening
        var vtype = (tr.getAttribute('data-vch-type') || '').toLowerCase();
        var match = (filter === 'all' || vtype === filter || (filter === 'expense' && vtype === 'product'));
        tr.style.display = match ? '' : 'none';
      });
    });
  }

  if (typeof feather !== 'undefined') {
    feather.replace();
  }
})();
</script>
