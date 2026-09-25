<?php
$customer = $data ?? [];
$ledger   = $ledger ?? [];
$from_date = $from_date ?? ($ledger['from_date'] ?? date('Y-04-01'));
$to_date   = $to_date ?? ($ledger['to_date'] ?? date('Y-03-31'));
$date_range = $date_range ?? (date('d-m-Y', strtotime($from_date)) . ' - ' . date('d-m-Y', strtotime($to_date)));

$order_rows   = $ledger['order_rows'] ?? [];
$product_rows = $ledger['product_rows'] ?? [];
$summary      = $ledger['summary'] ?? [];
$opening      = $ledger['opening'] ?? ['final' => 0, 'bank' => 0, 'cash' => 0];
$closing      = $summary['closing'] ?? $opening;
$col_totals   = $summary['column_totals'] ?? [];
$salesperson  = $ledger['salesperson'] ?? ($customer['added_by_name'] ?? '—');
$outstanding_limit = (float) ($ledger['outstanding_limit'] ?? ($customer['outstanding_limit'] ?? 0));

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

$customer_id = (int) ($id ?? ($customer['id'] ?? 0));
$base_ledger_url = base_url('inventory/customer/ledger/' . $customer_id);

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
  $side = $n < 0 ? 'Cr' : 'Dr';
  return ['amt' => $abs, 'side' => $side, 'raw' => $n];
};
?>

<style>
  .customer-card-shell,
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
  .fs-9 { font-size: 9px; }
  .track-1 { letter-spacing: 1px; }
  .customer-main-text { color: #111827; }
  .customer-soft-text { color: #1f2937; }
  .customer-info-divider { border-right: 1px solid #f0f2f5; }
  .customer-info-col { min-width: 200px; }
  .addr-lines { line-height: 1.7; }
  .key-info-table td { padding-top: 3px; padding-bottom: 3px; }
  .key-label-col { width: 42%; }

  .ledger-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
  }
  .ledger-summary-card {
    background: #fff;
    border: 1px solid #e8eaed;
    border-radius: 10px;
    padding: 10px 12px;
  }
  .ledger-summary-card .lbl {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6b7280;
    font-weight: 600;
  }
  .ledger-summary-card .val {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-top: 2px;
  }
  .ledger-summary-card .sub {
    font-size: 11px;
    color: #4b5563;
    margin-top: 4px;
    line-height: 1.45;
  }

  .ledger-tabs-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
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
    font-weight: 600;
    letter-spacing: 0.2px;
    padding: 7px 14px;
    border-radius: 6px;
    line-height: 1.2;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
  }
  .ledger-seg .seg-btn:hover {
    color: #111827;
  }
  .ledger-seg .seg-btn.active {
    background: #ffffff;
    color: #111827;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
  }
  .ledger-seg.ledger-seg-sub .seg-btn {
    font-weight: 500;
    padding: 6px 12px;
  }

  .ledger-table thead th {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #6b7280;
    background: #f9fafb;
    border-bottom: 1px solid #e8eaed;
    white-space: nowrap;
    vertical-align: middle;
    padding: 10px 8px !important;
  }
  .ledger-table tbody td {
    font-size: 11px;
    padding: 5px 8px !important;
    white-space: nowrap;
    vertical-align: middle;
  }
  .ledger-row { border-bottom: 1px solid #f3f4f6; background: #fff; }
  .ledger-row:hover { background: #f9fafb; }
  .ledger-row-opening { background: #fafbfc; font-weight: 600; }
  .ledger-row-product { background: #fcfcfd; }
  .ledger-row-product td.particulars { padding-left: 22px !important; color: #4b5563; }
  .ledger-row-payment { background: #f0fdf4; }
  .ledger-row-adj-plus { background: #eff6ff; }
  .ledger-row-adj-minus { background: #fff5f5; }
  .ledger-row-return { background: #fff7ed; }

  .bal-dr { color: #dc2626; }
  .bal-cr { color: #16a34a; }
  .tfoot-strong { background: #f3f4f6; font-weight: 700; }
  .tfoot-closing { background: #fff5f5; font-weight: 700; }

  /* Tab column visibility — default All shows everything */
  .ledger-mode-white .col-black-only,
  .ledger-mode-white .col-final-only { display: none !important; }
  .ledger-mode-black .col-white-only,
  .ledger-mode-black .col-final-only { display: none !important; }
  .ledger-mode-white .col-black-product-extra { display: none !important; }

  .filter-bar-shell {
    background: #fff;
    border: 1px solid #e8eaed;
    border-radius: 10px;
    padding: 10px 12px;
  }
</style>

<!-- Customer Info -->
<div class="bg-white customer-card-shell mb-2">
  <div class="d-flex align-items-center justify-content-between px-3 py-2 card-soft-header flex-wrap gap-2">
    <div>
      <div class="text-uppercase fw-semibold fs-10 track-1 mb-1">Customer Ledger</div>
      <div class="fw-semibold customer-main-text fs-15"><?= html_escape($customer['company_name'] ?? '—') ?></div>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
      <a href="<?= base_url('inventory/customer') ?>" class="btn btn-sm btn-outline-secondary fs-11">Back</a>
    </div>
  </div>
  <div class="d-flex flex-wrap">
    <div class="flex-fill px-3 py-2 customer-info-divider customer-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 mb-2">Address</div>
      <?php if (!empty($addrLines)): ?>
        <div class="fs-13 customer-soft-text addr-lines"><?= implode('<br>', array_map('html_escape', $addrLines)) ?></div>
      <?php else: ?>
        <div class="fs-13">—</div>
      <?php endif; ?>
      <?php if (!empty($locationLine)): ?>
        <div class="fs-11 text-secondary fw-medium mt-1"><?= html_escape($locationLine) ?></div>
      <?php endif; ?>
    </div>
    <div class="flex-fill px-3 py-2 customer-info-col">
      <div class="text-uppercase fw-semibold fs-10 track-1 mb-2">Key Info</div>
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
        <tr>
          <td class="text-secondary fw-medium">Salesperson</td>
          <td class="customer-main-text fw-semibold"><?= html_escape($salesperson ?: '—') ?></td>
        </tr>
        <tr>
          <td class="text-secondary fw-medium">Outstanding Limit</td>
          <td class="customer-main-text fw-semibold">₹ <?= number_format($outstanding_limit, 2) ?></td>
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

<!-- Date filter -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<div class="filter-bar-shell mb-2">
  <form method="get" action="<?= html_escape($base_ledger_url) ?>" class="d-flex flex-wrap align-items-end gap-2" id="ledgerFilterForm">
    <div style="min-width: 260px;">
      <label class="fs-10 text-uppercase text-secondary fw-semibold d-block mb-1">Date</label>
      <input type="text" autocomplete="off" class="form-control form-control-sm bg-white datepicker_report"
             name="date_range" value="<?= html_escape($date_range) ?>" placeholder="Select date range">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Apply</button>
  </form>
</div>
<script>
  $('.datepicker_report').daterangepicker({
    autoUpdateInput: false,
    autoApply: false,
    startDate: moment('<?= html_escape($from_date) ?>', 'YYYY-MM-DD'),
    endDate: moment('<?= html_escape($to_date) ?>', 'YYYY-MM-DD'),
    locale: {
      format: 'DD-MM-YYYY'
    }
  });
  $('.datepicker_report').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
  });
</script>

<!-- Summary cards -->
<?php
$s_open = $summary['opening'] ?? ['final' => 0, 'white' => 0, 'black' => 0];
$s_sales = $summary['sales'] ?? ['total' => 0, 'white' => 0, 'black' => 0, 'by_type' => []];
$s_recv = $summary['receipts'] ?? ['total' => 0, 'official' => 0, 'unofficial' => 0];
$s_adj = $summary['adjustments'] ?? ['total' => 0, 'official' => 0, 'unofficial' => 0];
$s_out = $summary['outstanding'] ?? ['final' => 0, 'official' => 0, 'unofficial' => 0];
$extra_types = $s_sales['by_type'] ?? [];
?>
<div class="ledger-summary-grid mb-2">
  <div class="ledger-summary-card">
    <div class="lbl">Opening Balance</div>
    <div class="val">₹ <?= number_format((float)$s_open['final'], 2) ?></div>
    <div class="sub">White: ₹ <?= number_format((float)$s_open['white'], 2) ?><br>Black: ₹ <?= number_format((float)$s_open['black'], 2) ?></div>
  </div>
  <div class="ledger-summary-card">
    <div class="lbl">Sales</div>
    <div class="val">₹ <?= number_format((float)$s_sales['total'], 2) ?></div>
    <div class="sub">
      White: ₹ <?= number_format((float)$s_sales['white'], 2) ?><br>
      Black: ₹ <?= number_format((float)$s_sales['black'], 2) ?>
      <?php foreach ($extra_types as $vt => $amt): ?>
        <?php if (!in_array($vt, ['Sales'], true)): ?>
          <br><?= html_escape($vt) ?>: ₹ <?= number_format((float)$amt, 2) ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="ledger-summary-card">
    <div class="lbl">Receipts</div>
    <div class="val">₹ <?= number_format((float)$s_recv['total'], 2) ?></div>
    <div class="sub">Official: ₹ <?= number_format((float)$s_recv['official'], 2) ?><br>Unofficial: ₹ <?= number_format((float)$s_recv['unofficial'], 2) ?></div>
  </div>
  <div class="ledger-summary-card">
    <div class="lbl">Adjustments</div>
    <div class="val"><?= ((float)$s_adj['total'] >= 0 ? '+' : '−') ?> ₹ <?= number_format(abs((float)$s_adj['total']), 2) ?></div>
    <div class="sub">
      Official: <?= ((float)$s_adj['official'] >= 0 ? '+' : '−') ?> ₹ <?= number_format(abs((float)$s_adj['official']), 2) ?><br>
      Unofficial: <?= ((float)$s_adj['unofficial'] >= 0 ? '+' : '−') ?> ₹ <?= number_format(abs((float)$s_adj['unofficial']), 2) ?>
    </div>
  </div>
  <?php if (!empty($summary['returns']['total'])): ?>
  <div class="ledger-summary-card">
    <div class="lbl">Sales Return</div>
    <div class="val">₹ <?= number_format((float)$summary['returns']['total'], 2) ?></div>
    <div class="sub">White: ₹ <?= number_format((float)$summary['returns']['white'], 2) ?><br>Black: ₹ <?= number_format((float)$summary['returns']['black'], 2) ?></div>
  </div>
  <?php endif; ?>
  <div class="ledger-summary-card">
    <div class="lbl">Outstanding</div>
    <div class="val">₹ <?= number_format((float)$s_out['final'], 2) ?></div>
    <div class="sub">Official: ₹ <?= number_format((float)$s_out['official'], 2) ?><br>Unofficial: ₹ <?= number_format((float)$s_out['unofficial'], 2) ?></div>
  </div>
</div>

<!-- Ledger table card -->
<div class="bg-white ledger-card-shell" id="ledgerShell" data-mode="all">
  <div class="ledger-tabs-bar">
    <div class="ledger-seg" id="ledgerMainTabs" role="tablist">
      <button type="button" class="seg-btn active" data-mode="all">All</button>
      <button type="button" class="seg-btn" data-mode="white">White</button>
      <button type="button" class="seg-btn" data-mode="black">Black</button>
    </div>
    <div class="ledger-seg ledger-seg-sub" id="ledgerSubTabs" role="tablist">
      <button type="button" class="seg-btn active" data-view="order">Order Wise</button>
      <button type="button" class="seg-btn" data-view="product">Product Wise</button>
    </div>
  </div>

  <?php
  $row_class = function ($row) {
    if (!empty($row['is_opening'])) return 'ledger-row-opening';
    if (!empty($row['is_product'])) return 'ledger-row-product';
    $t = $row['entry_type'] ?? '';
    if ($t === 'payment') return 'ledger-row-payment';
    if ($t === 'sales_return') return 'ledger-row-return';
    if ($t === 'adjustment') {
      return (($row['vch_type'] ?? '') === 'Adjustment (+)') ? 'ledger-row-adj-plus' : 'ledger-row-adj-minus';
    }
    return '';
  };
  ?>

  <!-- ORDER WISE -->
  <div class="table-responsive ledger-view" id="viewOrder" data-view-panel="order">
    <table class="table table-borderless mb-0 align-middle ledger-table" id="orderTable">
      <thead>
        <tr>
          <th class="col-base">Date</th>
          <th class="col-base">Particulars</th>
          <th class="col-base">Vch Type</th>
          <th class="col-base">Vch No</th>
          <th class="col-base col-black-product-extra text-end">Rate</th>
          <th class="col-white-only text-end">Bill Amt</th>
          <th class="col-white-only text-end">CGST</th>
          <th class="col-white-only text-end">SGST</th>
          <th class="col-white-only text-end">IGST</th>
          <th class="col-white-only text-end">Final Bill</th>
          <th class="col-black-only text-end">Cash</th>
          <th class="col-final-only text-end">Final Sale</th>
          <th class="col-white-only text-end">Bank Amt</th>
          <th class="col-black-only text-end">Cash Amt</th>
          <th class="col-final-only text-end">Final Balance</th>
          <th class="col-final-only"></th>
          <th class="col-white-only text-end">Bank Balance</th>
          <th class="col-white-only"></th>
          <th class="col-black-only text-end">Cash Balance</th>
          <th class="col-black-only"></th>
          <th class="col-base">Added By</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($order_rows)): ?>
          <tr><td colspan="21" class="text-center py-4 text-muted">No transactions in this period.</td></tr>
        <?php else: ?>
          <?php foreach ($order_rows as $row): ?>
            <?php
              $fb = $fmt_bal($row['final_balance'] ?? 0);
              $bb = $fmt_bal($row['bank_balance'] ?? 0);
              $cb = $fmt_bal($row['cash_balance'] ?? 0);
              $date_disp = (!empty($row['is_opening']) || empty($row['date'])) ? '' : date('d-M-Y', strtotime($row['date']));
            ?>
            <tr class="ledger-row <?= $row_class($row) ?>"
                data-date="<?= html_escape($date_disp) ?>"
                data-particulars="<?= html_escape($row['particulars'] ?? '') ?>"
                data-vch_type="<?= html_escape($row['vch_type'] ?? '') ?>"
                data-vch_no="<?= html_escape($row['vch_no'] ?? '') ?>"
                data-rate="<?= html_escape($fmt($row['rate'] ?? 0)) ?>"
                data-bill_amt="<?= html_escape($fmt($row['bill_amt'] ?? 0)) ?>"
                data-cgst="<?= html_escape($fmt($row['cgst'] ?? 0)) ?>"
                data-sgst="<?= html_escape($fmt($row['sgst'] ?? 0)) ?>"
                data-igst="<?= html_escape($fmt($row['igst'] ?? 0)) ?>"
                data-final_bill="<?= html_escape($fmt($row['final_bill'] ?? 0)) ?>"
                data-cash="<?= html_escape($fmt($row['cash'] ?? 0)) ?>"
                data-final_sale="<?= html_escape($fmt($row['final_sale'] ?? 0)) ?>"
                data-bank_amt="<?= html_escape($fmt($row['bank_amt'] ?? 0)) ?>"
                data-cash_amt="<?= html_escape($fmt($row['cash_amt'] ?? 0)) ?>"
                data-added_by="<?= html_escape($row['added_by'] ?? '') ?>">
              <td class="col-base text-secondary"><?= html_escape($date_disp) ?></td>
              <td class="col-base fw-semibold customer-soft-text"><?= html_escape($row['particulars'] ?? '') ?></td>
              <td class="col-base"><?= html_escape($row['vch_type'] ?? '') ?></td>
              <td class="col-base"><?= html_escape($row['vch_no'] ?? '') ?></td>
              <td class="col-base col-black-product-extra text-end"><?= $fmt($row['rate'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['bill_amt'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['cgst'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['sgst'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['igst'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['final_bill'] ?? 0) ?></td>
              <td class="col-black-only text-end"><?= $fmt($row['cash'] ?? 0) ?></td>
              <td class="col-final-only text-end"><?= $fmt($row['final_sale'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['bank_amt'] ?? 0) ?></td>
              <td class="col-black-only text-end"><?= $fmt($row['cash_amt'] ?? 0) ?></td>
              <td class="col-final-only text-end <?= $fb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $fb['amt'] ?></td>
              <td class="col-final-only fs-10 <?= $fb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= !empty($row['is_opening']) || isset($row['final_balance']) ? $fb['side'] : '' ?></td>
              <td class="col-white-only text-end <?= $bb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $bb['amt'] ?></td>
              <td class="col-white-only fs-10 <?= $bb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $bb['side'] ?></td>
              <td class="col-black-only text-end <?= $cb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cb['amt'] ?></td>
              <td class="col-black-only fs-10 <?= $cb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cb['side'] ?></td>
              <td class="col-base fs-10"><?= html_escape($row['added_by'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <?php
          $cf = $fmt_bal($closing['final'] ?? 0);
          $cbank = $fmt_bal($closing['bank'] ?? 0);
          $ccash = $fmt_bal($closing['cash'] ?? 0);
        ?>
        <tr class="tfoot-strong">
          <td class="col-base" colspan="4">Current Total</td>
          <td class="col-base col-black-product-extra text-end"><?= number_format((float)($col_totals['rate'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['bill_amt'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['cgst'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['sgst'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['igst'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['final_bill'] ?? 0), 2) ?></td>
          <td class="col-black-only text-end"><?= number_format((float)($col_totals['cash'] ?? 0), 2) ?></td>
          <td class="col-final-only text-end"><?= number_format((float)($col_totals['final_sale'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['bank_amt'] ?? 0), 2) ?></td>
          <td class="col-black-only text-end"><?= number_format((float)($col_totals['cash_amt'] ?? 0), 2) ?></td>
          <td class="col-final-only" colspan="2"></td>
          <td class="col-white-only" colspan="2"></td>
          <td class="col-black-only" colspan="2"></td>
          <td class="col-base"></td>
        </tr>
        <tr class="tfoot-closing">
          <td class="col-base" colspan="4">Closing Balance</td>
          <td class="col-base col-black-product-extra"></td>
          <td class="col-white-only" colspan="5"></td>
          <td class="col-black-only"></td>
          <td class="col-final-only"></td>
          <td class="col-white-only" colspan="1"></td>
          <td class="col-black-only"></td>
          <td class="col-final-only text-end <?= $cf['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cf['amt'] ?></td>
          <td class="col-final-only <?= $cf['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cf['side'] ?></td>
          <td class="col-white-only text-end <?= $cbank['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cbank['amt'] ?></td>
          <td class="col-white-only <?= $cbank['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cbank['side'] ?></td>
          <td class="col-black-only text-end <?= $ccash['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $ccash['amt'] ?></td>
          <td class="col-black-only <?= $ccash['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $ccash['side'] ?></td>
          <td class="col-base"></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- PRODUCT WISE -->
  <div class="table-responsive ledger-view d-none" id="viewProduct" data-view-panel="product">
    <table class="table table-borderless mb-0 align-middle ledger-table" id="productTable">
      <thead>
        <tr>
          <th class="col-base">Date</th>
          <th class="col-base">Particulars</th>
          <th class="col-base">Vch Type</th>
          <th class="col-base">Vch No</th>
          <th class="col-base text-end">Qty</th>
          <th class="col-base col-black-product-extra text-end">Rate/Pc</th>
          <th class="col-base col-black-product-extra text-end">Rate</th>
          <th class="col-white-only text-end">Bill Amt/Pc</th>
          <th class="col-white-only text-end">Bill Amt</th>
          <th class="col-white-only text-end">CGST</th>
          <th class="col-white-only text-end">SGST</th>
          <th class="col-white-only text-end">IGST</th>
          <th class="col-white-only text-end">Final Bill</th>
          <th class="col-black-only text-end">Cash</th>
          <th class="col-final-only text-end">Final Sale</th>
          <th class="col-white-only text-end">Bank Amt</th>
          <th class="col-black-only text-end">Cash Amt</th>
          <th class="col-final-only text-end">Final Balance</th>
          <th class="col-final-only"></th>
          <th class="col-white-only text-end">Bank Balance</th>
          <th class="col-white-only"></th>
          <th class="col-black-only text-end">Cash Balance</th>
          <th class="col-black-only"></th>
          <th class="col-base">Added By</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($product_rows)): ?>
          <tr><td colspan="24" class="text-center py-4 text-muted">No transactions in this period.</td></tr>
        <?php else: ?>
          <?php foreach ($product_rows as $row): ?>
            <?php
              $is_prod = !empty($row['is_product']);
              $has_bal = array_key_exists('final_balance', $row) && $row['final_balance'] !== null;
              $fb = $has_bal ? $fmt_bal($row['final_balance']) : null;
              $bb = $has_bal ? $fmt_bal($row['bank_balance']) : null;
              $cb = $has_bal ? $fmt_bal($row['cash_balance']) : null;
              $date_disp = ($is_prod || !empty($row['is_opening']) || empty($row['date'])) ? '' : date('d-M-Y', strtotime($row['date']));
            ?>
            <tr class="ledger-row <?= $row_class($row) ?>"
                data-date="<?= html_escape($date_disp) ?>"
                data-particulars="<?= html_escape($row['particulars'] ?? '') ?>"
                data-vch_type="<?= html_escape($row['vch_type'] ?? '') ?>"
                data-vch_no="<?= html_escape($row['vch_no'] ?? '') ?>"
                data-qty="<?= html_escape($fmt($row['qty'] ?? 0)) ?>"
                data-rate_pc="<?= html_escape($fmt($row['rate_pc'] ?? 0)) ?>"
                data-rate="<?= html_escape($fmt($row['rate'] ?? 0)) ?>"
                data-bill_amt_pc="<?= html_escape($fmt($row['bill_amt_pc'] ?? 0)) ?>"
                data-bill_amt="<?= html_escape($fmt($row['bill_amt'] ?? 0)) ?>"
                data-cgst="<?= html_escape($fmt($row['cgst'] ?? 0)) ?>"
                data-sgst="<?= html_escape($fmt($row['sgst'] ?? 0)) ?>"
                data-igst="<?= html_escape($fmt($row['igst'] ?? 0)) ?>"
                data-final_bill="<?= html_escape($fmt($row['final_bill'] ?? 0)) ?>"
                data-cash="<?= html_escape($fmt($row['cash'] ?? 0)) ?>"
                data-final_sale="<?= html_escape($fmt($row['final_sale'] ?? 0)) ?>"
                data-bank_amt="<?= html_escape($fmt($row['bank_amt'] ?? 0)) ?>"
                data-cash_amt="<?= html_escape($fmt($row['cash_amt'] ?? 0)) ?>"
                data-added_by="<?= html_escape($row['added_by'] ?? '') ?>">
              <td class="col-base text-secondary"><?= html_escape($date_disp) ?></td>
              <td class="col-base fw-semibold customer-soft-text particulars"><?= html_escape($row['particulars'] ?? '') ?></td>
              <td class="col-base"><?= html_escape($row['vch_type'] ?? '') ?></td>
              <td class="col-base"><?= html_escape($row['vch_no'] ?? '') ?></td>
              <td class="col-base text-end"><?= isset($row['qty']) && $row['qty'] !== null && $row['qty'] !== '' ? $fmt($row['qty'], true) : '' ?></td>
              <td class="col-base col-black-product-extra text-end"><?= $is_prod ? $fmt($row['rate_pc'] ?? 0) : '' ?></td>
              <td class="col-base col-black-product-extra text-end"><?= $fmt($row['rate'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $is_prod ? $fmt($row['bill_amt_pc'] ?? 0) : '' ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['bill_amt'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['cgst'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['sgst'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['igst'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= $fmt($row['final_bill'] ?? 0) ?></td>
              <td class="col-black-only text-end"><?= $fmt($row['cash'] ?? 0) ?></td>
              <td class="col-final-only text-end"><?= $fmt($row['final_sale'] ?? 0) ?></td>
              <td class="col-white-only text-end"><?= !$is_prod ? $fmt($row['bank_amt'] ?? 0) : '' ?></td>
              <td class="col-black-only text-end"><?= !$is_prod ? $fmt($row['cash_amt'] ?? 0) : '' ?></td>
              <?php if ($fb): ?>
                <td class="col-final-only text-end <?= $fb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $fb['amt'] ?></td>
                <td class="col-final-only fs-10 <?= $fb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $fb['side'] ?></td>
                <td class="col-white-only text-end <?= $bb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $bb['amt'] ?></td>
                <td class="col-white-only fs-10 <?= $bb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $bb['side'] ?></td>
                <td class="col-black-only text-end <?= $cb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cb['amt'] ?></td>
                <td class="col-black-only fs-10 <?= $cb['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cb['side'] ?></td>
              <?php else: ?>
                <td class="col-final-only"></td><td class="col-final-only"></td>
                <td class="col-white-only"></td><td class="col-white-only"></td>
                <td class="col-black-only"></td><td class="col-black-only"></td>
              <?php endif; ?>
              <td class="col-base fs-10"><?= !$is_prod ? html_escape($row['added_by'] ?? '—') : '' ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <?php
          $cf = $fmt_bal($closing['final'] ?? 0);
          $cbank = $fmt_bal($closing['bank'] ?? 0);
          $ccash = $fmt_bal($closing['cash'] ?? 0);
        ?>
        <tr class="tfoot-strong">
          <td class="col-base" colspan="4">Current Total</td>
          <td class="col-base"></td>
          <td class="col-base col-black-product-extra"></td>
          <td class="col-base col-black-product-extra text-end"><?= number_format((float)($col_totals['rate'] ?? 0), 2) ?></td>
          <td class="col-white-only"></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['bill_amt'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['cgst'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['sgst'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['igst'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['final_bill'] ?? 0), 2) ?></td>
          <td class="col-black-only text-end"><?= number_format((float)($col_totals['cash'] ?? 0), 2) ?></td>
          <td class="col-final-only text-end"><?= number_format((float)($col_totals['final_sale'] ?? 0), 2) ?></td>
          <td class="col-white-only text-end"><?= number_format((float)($col_totals['bank_amt'] ?? 0), 2) ?></td>
          <td class="col-black-only text-end"><?= number_format((float)($col_totals['cash_amt'] ?? 0), 2) ?></td>
          <td class="col-final-only" colspan="2"></td>
          <td class="col-white-only" colspan="2"></td>
          <td class="col-black-only" colspan="2"></td>
          <td class="col-base"></td>
        </tr>
        <tr class="tfoot-closing">
          <td class="col-base" colspan="4">Closing Balance</td>
          <td class="col-base"></td>
          <td class="col-base col-black-product-extra" colspan="2"></td>
          <td class="col-white-only" colspan="5"></td>
          <td class="col-black-only"></td>
          <td class="col-final-only"></td>
          <td class="col-white-only"></td>
          <td class="col-black-only"></td>
          <td class="col-final-only text-end <?= $cf['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cf['amt'] ?></td>
          <td class="col-final-only <?= $cf['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cf['side'] ?></td>
          <td class="col-white-only text-end <?= $cbank['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cbank['amt'] ?></td>
          <td class="col-white-only <?= $cbank['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $cbank['side'] ?></td>
          <td class="col-black-only text-end <?= $ccash['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $ccash['amt'] ?></td>
          <td class="col-black-only <?= $ccash['raw'] < 0 ? 'bal-cr' : 'bal-dr' ?>"><?= $ccash['side'] ?></td>
          <td class="col-base"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script>
(function () {
  var shell = document.getElementById('ledgerShell');
  if (!shell) return;

  // Main tabs: All / White / Black
  document.querySelectorAll('#ledgerMainTabs [data-mode]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('#ledgerMainTabs .seg-btn').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      var mode = btn.getAttribute('data-mode') || 'all';
      shell.classList.remove('ledger-mode-all', 'ledger-mode-white', 'ledger-mode-black');
      shell.classList.add('ledger-mode-' + mode);
      shell.setAttribute('data-mode', mode);
    });
  });
  shell.classList.add('ledger-mode-all');

  // Sub tabs: Order / Product
  document.querySelectorAll('#ledgerSubTabs [data-view]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('#ledgerSubTabs .seg-btn').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      var view = btn.getAttribute('data-view');
      document.querySelectorAll('.ledger-view').forEach(function (panel) {
        if (panel.getAttribute('data-view-panel') === view) {
          panel.classList.remove('d-none');
        } else {
          panel.classList.add('d-none');
        }
      });
    });
  });

  if (typeof feather !== 'undefined') feather.replace();
})();
</script>
