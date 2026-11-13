<?php
$purchase   = isset($purchase_summary) ? $purchase_summary : [];
$sales      = isset($sales_summary) ? $sales_summary : [];
$expense    = isset($expense_summary) ? $expense_summary : [];
$returns    = isset($return_summary) ? $return_summary : [];
$profit     = isset($profit_summary) ? $profit_summary : [];
$outstanding= isset($outstanding_summary) ? $outstanding_summary : [];
$payments   = isset($payments_summary) ? $payments_summary : [];

$fmt = function ($n, $d = 2) {
    return number_format((float)$n, $d);
};
?>
<style>
  .pnl-dash-wrapper {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }
  .dashboard-section-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    border: 1px solid #ebe9f1;
    margin-bottom: 20px;
  }
  .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
  }
  .section-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .section-icon-badge {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }
  .section-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .cash-stat-card {
    background: #ffffff;
    border: 1px solid #ebe9f1;
    border-radius: 10px;
    padding: 12px 14px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
  }
  .cash-stat-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
  }
  .cash-stat-card.card-official { border-top: 3px solid #7367f0; }
  .cash-stat-card.card-success-top { border-top: 3px solid #28c76f; }
  .cash-stat-card.card-warning-top { border-top: 3px solid #ff9f43; }
  .cash-stat-card.card-info-top { border-top: 3px solid #00cfe8; }
  .cash-stat-card.card-danger-top { border-top: 3px solid #ea5455; }
  .cash-stat-card.card-secondary-top { border-top: 3px solid #82868b; }
  .cash-stat-card .stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
  }
  .cash-stat-card .stat-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #6e6b7b;
    margin: 0;
    display: flex;
    align-items: center;
  }
  .cash-stat-card .stat-value {
    font-size: 20px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 8px;
    letter-spacing: -0.4px;
  }
  .cash-stat-card .stat-detail {
    font-size: 11px;
    color: #5e5873;
    padding-top: 8px;
    border-top: 1px dashed #ebe9f1;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
  }
  .cash-stat-card .stat-detail span b { font-weight: 700; }
  .cash-stat-card .badge {
    font-size: 10px;
    padding: 2px 6px;
  }
  .curr-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    background: #f1f5f9;
    color: #475569;
    margin-right: 3px;
    letter-spacing: 0.3px;
  }
  .curr-badge.curr-usd { background: #e0f2fe; color: #0369a1; }
  .curr-badge.curr-rmb { background: #fee2e2; color: #b91c1c; }
  .dashboard-table-container { overflow-x: auto; }
  .dashboard-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
  }
  .dashboard-table th {
    background-color: #f8f8fb;
    color: #475569;
    font-weight: 700;
    padding: 11px 14px;
    border-bottom: 2px solid #ebe9f1;
    font-size: 11.5px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .dashboard-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
  }
  .dashboard-table tbody tr:hover { background-color: #f8fafc; }
  .tag-official {
    background-color: #ede9fe;
    color: #7367f0;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
  }
  .tag-unofficial {
    background-color: #eceff1;
    color: #546e7a;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
  }
</style>

<div class="content-body pnl-dash-wrapper">

  <div class="card mb-2" style="border-radius: 10px;">
    <div class="card-body py-1 px-2">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="section-icon-badge" style="background: #e8f8f0; color: #28c76f; width: 42px; height: 42px; font-size: 18px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
            <i class="feather icon-pie-chart"></i>
          </div>
          <div>
            <h4 class="mb-0 fw-bolder text-dark" style="font-size: 18px;">P&amp;L Report</h4>
            <div class="text-muted small d-flex flex-wrap align-items-center gap-2 mt-25">
              <span><i class="feather icon-briefcase text-primary me-25"></i> <b><?= htmlspecialchars($company_info['name'] ?? 'Company'); ?></b></span>
              <span class="text-secondary">•</span>
              <span><i class="feather icon-calendar text-info me-25"></i> Active Range: <b id="pnl_date_range_label"><?= htmlspecialchars($filters['date_range']); ?></b></span>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center ms-auto">
          <?php include(__DIR__ . '/partials/dashboard_switcher.php'); ?>
        </div>
      </div>
    </div>
  </div>

  <?php include('filter/pnl_report_filter.php'); ?>

  <?php $profit_val = (float)($profit['gross_profit'] ?? 0); ?>
  <!-- KPI CARDS -->
  <div class="dashboard-section-card">
    <div class="section-header">
      <div class="section-header-left">
        <div class="section-icon-badge" style="background-color: #e8f8f0; color: #28c76f;">
          <i class="feather icon-activity"></i>
        </div>
        <h2 class="section-title">Profit &amp; Loss Overview</h2>
      </div>
    </div>

    <div class="row g-2">
      <!-- Total Purchase -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-warning-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-shopping-bag text-warning me-50"></i> Total Purchase</span>
            <span class="badge badge-light-warning" id="kpi_purchase_orders"><?= number_format((int)($purchase['total_orders'] ?? 0)); ?> In</span>
          </div>
          <div class="stat-value text-warning" id="kpi_purchase_gross">₹ <?= $fmt($purchase['gross_purchase_amount'] ?? 0); ?></div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="kpi_purchase_white">₹ <?= $fmt($purchase['white_purchase_amount'] ?? 0); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="kpi_purchase_black">₹ <?= $fmt($purchase['black_purchase_amount'] ?? 0); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="kpi_purchase_usd">$ <?= $fmt($purchase['total_usd'] ?? 0); ?></b></span>
            <span><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="kpi_purchase_rmb">¥ <?= $fmt($purchase['total_rmb'] ?? 0); ?></b></span>
          </div>
        </div>
      </div>

      <!-- Sales Orders -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-file-text text-primary me-50"></i> Sales Orders</span>
            <span class="badge badge-light-primary" id="kpi_sales_orders"><?= number_format((int)($sales['total_orders'] ?? 0)); ?> Orders</span>
          </div>
          <div class="stat-value text-primary" id="kpi_sales_gross">₹ <?= $fmt($sales['gross_sales_amount'] ?? 0); ?></div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="kpi_sales_white">₹ <?= $fmt($sales['white_sales_amount'] ?? 0); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="kpi_sales_black">₹ <?= $fmt($sales['black_sales_amount'] ?? 0); ?></b></span>
          </div>
        </div>
      </div>

      <!-- PO Expense -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-danger-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-credit-card text-danger me-50"></i> PO Expense</span>
            <span class="badge badge-light-danger" id="kpi_expense_count"><?= number_format((int)($expense['total_count'] ?? 0)); ?></span>
          </div>
          <div class="stat-value text-danger" id="kpi_expense_gross">₹ <?= $fmt($expense['gross_expense_amount'] ?? 0); ?></div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="kpi_expense_white">₹ <?= $fmt($expense['white_expense_amount'] ?? 0); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="kpi_expense_black">₹ <?= $fmt($expense['black_expense_amount'] ?? 0); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="kpi_expense_usd">$ <?= $fmt($expense['total_usd'] ?? 0); ?></b></span>
            <span><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="kpi_expense_rmb">¥ <?= $fmt($expense['total_rmb'] ?? 0); ?></b></span>
          </div>
        </div>
      </div>

      <!-- Sales Return -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-info-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-rotate-ccw text-info me-50"></i> Sales Return</span>
            <span class="badge badge-light-info" id="kpi_return_count"><?= number_format((int)($returns['total_count'] ?? 0)); ?></span>
          </div>
          <div class="stat-value text-info" id="kpi_return_gross">₹ <?= $fmt($returns['total_return_amount'] ?? 0); ?></div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="kpi_return_white">₹ <?= $fmt($returns['white_return_amount'] ?? 0); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="kpi_return_black">₹ <?= $fmt($returns['black_return_amount'] ?? 0); ?></b></span>
          </div>
        </div>
      </div>

      <!-- Profit -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-success-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-trending-up text-success me-50"></i> Profit</span>
          </div>
          <div class="stat-value <?= $profit_val >= 0 ? 'text-success' : 'text-danger'; ?>" id="kpi_profit_gross">₹ <?= $fmt($profit_val); ?></div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="kpi_profit_white">₹ <?= $fmt($profit['white_profit'] ?? 0); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="kpi_profit_black">₹ <?= $fmt($profit['black_profit'] ?? 0); ?></b></span>
          </div>
        </div>
      </div>

      <!-- Outstanding -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-secondary-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-alert-circle text-secondary me-50"></i> Outstanding</span>
          </div>
          <div class="stat-value text-dark" id="kpi_out_gross">₹ <?= $fmt($outstanding['total_outstanding_inr'] ?? 0); ?></div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="kpi_out_white">₹ <?= $fmt($outstanding['official_outstanding'] ?? 0); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="kpi_out_black">₹ <?= $fmt($outstanding['unofficial_outstanding'] ?? 0); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="kpi_out_usd">$ <?= $fmt($outstanding['total_outstanding_usd'] ?? 0); ?></b></span>
            <span><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="kpi_out_rmb">¥ <?= $fmt($outstanding['total_outstanding_rmb'] ?? 0); ?></b></span>
          </div>
        </div>
      </div>

      <!-- Payments -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-success-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-dollar-sign text-success me-50"></i> Payments</span>
            <span class="badge badge-light-success" id="kpi_pay_count"><?= number_format((int)($payments['total_count'] ?? 0)); ?></span>
          </div>
          <div class="stat-value text-success" id="kpi_pay_gross">₹ <?= $fmt($payments['gross_payment_amount'] ?? 0); ?></div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="kpi_pay_white">₹ <?= $fmt($payments['white_payment_amount'] ?? 0); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="kpi_pay_black">₹ <?= $fmt($payments['black_payment_amount'] ?? 0); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="kpi_pay_usd">$ <?= $fmt($payments['gross_usd'] ?? 0); ?></b></span>
            <span><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="kpi_pay_rmb">¥ <?= $fmt($payments['gross_rmb'] ?? 0); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- RECENT ACTIVITY -->
  <div class="row">
    <div class="col-12 col-xl-6">
      <div class="dashboard-section-card">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background:#ede9fe;color:#7367f0;"><i class="feather icon-shopping-cart"></i></div>
            <h2 class="section-title">Recent Sales Orders</h2>
          </div>
        </div>
        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Order / Invoice</th>
                <th>Customer</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_sales)): foreach ($recent_sales as $row): ?>
              <tr>
                <td><?= !empty($row['date']) ? date('d M Y', strtotime($row['date'])) : '—'; ?></td>
                <td><span class="fw-bold text-primary"><?= htmlspecialchars($row['order_no'] ?: ($row['invoice_no'] ?? '—')); ?></span></td>
                <td><?= htmlspecialchars($row['customer_name'] ?? '—'); ?></td>
                <td class="text-end fw-bold">₹ <?= $fmt($row['grand_total'] ?? 0); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center text-muted py-2">No recent sales orders</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="dashboard-section-card">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background:#fff4e6;color:#ff9f43;"><i class="feather icon-package"></i></div>
            <h2 class="section-title">Recent Purchase In</h2>
          </div>
        </div>
        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Voucher</th>
                <th>Supplier</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_purchase_in)): foreach ($recent_purchase_in as $row): ?>
              <tr>
                <td><?= !empty($row['date']) ? date('d M Y', strtotime($row['date'])) : '—'; ?></td>
                <td><span class="fw-bold text-warning"><?= htmlspecialchars($row['voucher_no'] ?? '—'); ?></span>
                  <span class="badge badge-light-secondary ms-25"><?= htmlspecialchars($row['method'] ?? ''); ?></span>
                </td>
                <td><?= htmlspecialchars($row['supplier_name'] ?? '—'); ?></td>
                <td class="text-end fw-bold">₹ <?= $fmt($row['total_amt'] ?? 0); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center text-muted py-2">No recent purchase-in</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="dashboard-section-card">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background:#fce4ec;color:#ea5455;"><i class="feather icon-credit-card"></i></div>
            <h2 class="section-title">Recent PO Expenses</h2>
          </div>
        </div>
        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Batch</th>
                <th>Vendor</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_expenses)): foreach ($recent_expenses as $row): ?>
              <tr>
                <td><?= !empty($row['date']) ? date('d M Y', strtotime($row['date'])) : '—'; ?></td>
                <td>
                  <span class="fw-bold"><?= htmlspecialchars($row['voucher_no'] ?? '—'); ?></span>
                  <?php if (($row['type'] ?? '') === 'official'): ?>
                    <span class="tag-official">White</span>
                  <?php else: ?>
                    <span class="tag-unofficial">Black</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['vendor_name'] ?? '—'); ?></td>
                <td class="text-end fw-bold">₹ <?= $fmt($row['grand_total'] ?? 0); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center text-muted py-2">No recent expenses</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="dashboard-section-card">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background:#e3f2fd;color:#00cfe8;"><i class="feather icon-rotate-ccw"></i></div>
            <h2 class="section-title">Recent Sales Returns</h2>
          </div>
        </div>
        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Ref</th>
                <th>Customer</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_returns)): foreach ($recent_returns as $row): ?>
              <tr>
                <td><?= !empty($row['date']) ? date('d M Y', strtotime($row['date'])) : '—'; ?></td>
                <td class="fw-bold text-info"><?= htmlspecialchars($row['return_no'] ?? '—'); ?></td>
                <td><?= htmlspecialchars($row['customer_name'] ?? '—'); ?></td>
                <td class="text-end fw-bold">₹ <?= $fmt($row['grand_total'] ?? 0); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center text-muted py-2">No recent returns</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="dashboard-section-card">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background:#e8f5e9;color:#28c76f;"><i class="feather icon-dollar-sign"></i></div>
            <h2 class="section-title">Recent Supplier / Vendor Payments</h2>
          </div>
        </div>
        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Party</th>
                <th>Type</th>
                <th>Ref</th>
                <th class="text-end">INR</th>
                <th class="text-end">USD</th>
                <th class="text-end">RMB</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_payments)): foreach ($recent_payments as $row): ?>
              <tr>
                <td><?= !empty($row['date']) ? date('d M Y', strtotime($row['date'])) : '—'; ?></td>
                <td><?= htmlspecialchars($row['party_name'] ?? '—'); ?></td>
                <td>
                  <span class="badge badge-light-secondary"><?= htmlspecialchars($row['party_type'] ?? ''); ?></span>
                  <?php if (($row['payment_type'] ?? '') === 'official'): ?>
                    <span class="tag-official">White</span>
                  <?php else: ?>
                    <span class="tag-unofficial">Black</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['ref_no'] ?? '—'); ?></td>
                <td class="text-end fw-bold">₹ <?= $fmt($row['amount'] ?? 0); ?></td>
                <td class="text-end"><?= $fmt($row['usd'] ?? 0); ?></td>
                <td class="text-end"><?= $fmt($row['rmb'] ?? 0); ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="7" class="text-center text-muted py-2">No recent payments</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  function inr(n) {
    n = parseFloat(n || 0);
    return '₹ ' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function num(n) {
    n = parseFloat(n || 0);
    return n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function intFmt(n) {
    return parseInt(n || 0, 10).toLocaleString('en-IN');
  }

  function applyPnlData(data) {
    if (!data) return;
    var p = data.purchase_summary || {};
    var s = data.sales_summary || {};
    var e = data.expense_summary || {};
    var r = data.return_summary || {};
    var pr = data.profit_summary || {};
    var o = data.outstanding_summary || {};
    var pay = data.payments_summary || {};
    var f = data.filters || {};

    if (f.date_range) {
      $('#pnl_date_range_label').text(f.date_range);
    }

    $('#kpi_purchase_orders').text(intFmt(p.total_orders) + ' In');
    $('#kpi_purchase_gross').text(inr(p.gross_purchase_amount));
    $('#kpi_purchase_white').text(inr(p.white_purchase_amount));
    $('#kpi_purchase_black').text(inr(p.black_purchase_amount));
    $('#kpi_purchase_usd').text('$ ' + num(p.total_usd));
    $('#kpi_purchase_rmb').text('¥ ' + num(p.total_rmb));

    $('#kpi_sales_orders').text(intFmt(s.total_orders) + ' Orders');
    $('#kpi_sales_gross').text(inr(s.gross_sales_amount));
    $('#kpi_sales_white').text(inr(s.white_sales_amount));
    $('#kpi_sales_black').text(inr(s.black_sales_amount));

    $('#kpi_expense_count').text(intFmt(e.total_count));
    $('#kpi_expense_gross').text(inr(e.gross_expense_amount));
    $('#kpi_expense_white').text(inr(e.white_expense_amount));
    $('#kpi_expense_black').text(inr(e.black_expense_amount));
    $('#kpi_expense_usd').text('$ ' + num(e.total_usd));
    $('#kpi_expense_rmb').text('¥ ' + num(e.total_rmb));

    $('#kpi_return_count').text(intFmt(r.total_count));
    $('#kpi_return_gross').text(inr(r.total_return_amount));
    $('#kpi_return_white').text(inr(r.white_return_amount));
    $('#kpi_return_black').text(inr(r.black_return_amount));

    var pg = parseFloat(pr.gross_profit || 0);
    $('#kpi_profit_gross').text(inr(pg)).toggleClass('text-success', pg >= 0).toggleClass('text-danger', pg < 0);
    $('#kpi_profit_white').text(inr(pr.white_profit));
    $('#kpi_profit_black').text(inr(pr.black_profit));

    $('#kpi_out_gross').text(inr(o.total_outstanding_inr));
    $('#kpi_out_white').text(inr(o.official_outstanding));
    $('#kpi_out_black').text(inr(o.unofficial_outstanding));
    $('#kpi_out_usd').text('$ ' + num(o.total_outstanding_usd));
    $('#kpi_out_rmb').text('¥ ' + num(o.total_outstanding_rmb));

    $('#kpi_pay_count').text(intFmt(pay.total_count));
    $('#kpi_pay_gross').text(inr(pay.gross_payment_amount));
    $('#kpi_pay_white').text(inr(pay.white_payment_amount));
    $('#kpi_pay_black').text(inr(pay.black_payment_amount));
    $('#kpi_pay_usd').text('$ ' + num(pay.gross_usd));
    $('#kpi_pay_rmb').text('¥ ' + num(pay.gross_rmb));
  }

  window.refreshPnlDashboard = function () {
    var range = $('.datepicker_report').val() || '';
    $.ajax({
      url: '<?= site_url('pnl-report/get_data_ajax'); ?>',
      type: 'GET',
      dataType: 'json',
      data: { date_range: range, search: 'true' },
      success: function (res) {
        if (res && res.success) {
          applyPnlData(res);
        }
      }
    });
  };

  // Soft AJAX refresh after filter apply is handled by full GET submit;
  // expose helper for any future in-page refresh buttons.
})();
</script>
