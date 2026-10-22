<style>
  .purchase-dash-wrapper {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  /* Section Card (matches sales_dashboard.php) */
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

  /* Stat Card (matches sales_dashboard.php) */
  .cash-stat-card {
    background: #ffffff;
    border: 1px solid #ebe9f1;
    border-radius: 10px;
    padding: 16px 20px;
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
  .cash-stat-card.card-official {
    border-top: 4px solid #7367f0;
  }
  .cash-stat-card.card-unofficial {
    border-top: 4px solid #82868b;
  }
  .cash-stat-card.card-success-top {
    border-top: 4px solid #28c76f;
  }
  .cash-stat-card.card-warning-top {
    border-top: 4px solid #ff9f43;
  }
  .cash-stat-card.card-info-top {
    border-top: 4px solid #00cfe8;
  }
  .cash-stat-card.card-danger-top {
    border-top: 4px solid #ea5455;
  }

  .cash-stat-card .stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }
  .cash-stat-card .stat-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6e6b7b;
    margin: 0;
    display: flex;
    align-items: center;
  }
  .cash-stat-card .stat-value {
    font-size: 26px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
  }
  .cash-stat-card .stat-detail {
    font-size: 12px;
    color: #5e5873;
    padding-top: 10px;
    border-top: 1px dashed #ebe9f1;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
  }
  .cash-stat-card .stat-detail span b {
    font-weight: 700;
  }


  /* Modern Dashboard Tables */
  .dashboard-table-container {
    overflow-x: auto;
  }
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
  .dashboard-table tbody tr:hover {
    background-color: #f8fafc;
  }

  /* Tag Badges */
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
  .tag-cash {
    background-color: #dcfce7;
    color: #15803d;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
  }
  .tag-bank {
    background-color: #e0f2fe;
    color: #0369a1;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
  }
  .tag-import {
    background-color: #e0e7ff;
    color: #3730a3;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
  }
  .tag-local {
    background-color: #fef3c7;
    color: #92400e;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
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
  .curr-badge.curr-usd {
    background: #e0f2fe;
    color: #0369a1;
  }
  .curr-badge.curr-rmb {
    background: #fee2e2;
    color: #b91c1c;
  }
</style>

<div class="purchase-dash-wrapper content-body">
  
  <!-- ========================================== -->
  <!-- 1. DASHBOARD HEADER                        -->
  <!-- ========================================== -->
  <div class="card mb-2" style="border-radius: 10px;">
    <div class="card-body py-1 px-2">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="section-icon-badge" style="background: #ede9fe; color: #7367f0; width: 42px; height: 42px; font-size: 18px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
            <i class="feather icon-shopping-bag"></i>
          </div>
          <div>
            <h4 class="mb-0 fw-bolder text-dark" style="font-size: 18px;">Purchase Dashboard</h4>
            <div class="text-muted small d-flex flex-wrap align-items-center gap-2 mt-25">
              <span><i class="feather icon-briefcase text-primary me-25"></i> <b><?= htmlspecialchars($company_info['name'] ?? 'Company'); ?></b></span>
              <span class="text-secondary">•</span>
              <span><i class="feather icon-calendar text-info me-25"></i> Active Range: <b><?= htmlspecialchars($filters['date_range']); ?></b> <?= !empty($filters['is_filtered']) ? '<span class="badge badge-light-success ms-25">Filtered</span>' : '<span class="badge badge-light-secondary ms-25">Current Month</span>'; ?></span>
            </div>
          </div>
        </div>

        <!-- Dashboards Switcher Dropdown -->
        <div class="d-flex align-items-center ms-auto">
          <?php include(__DIR__ . '/partials/dashboard_switcher.php'); ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Component Include -->
  <?php include('filter/purchase_dashboard_filter.php'); ?>

  <!-- ========================================== -->
  <!-- 2. EXECUTIVE PROCUREMENT KPI CARDS         -->
  <!-- ========================================== -->
  <div class="dashboard-section-card">
    <div class="section-header">
      <div class="section-header-left">
        <div class="section-icon-badge" style="background-color: #ede9fe; color: #7367f0;">
          <i class="feather icon-layers"></i>
        </div>
        <h2 class="section-title">
          Purchase Overview (Import + Local)
          <!-- <span class="badge badge-light-primary small font-weight-normal" style="font-size: 11px;">Gross • White • Black</span> -->
        </h2>
      </div>
      <div>
        <span class="badge badge-light-info">
          Total Orders: <b id="val_overview_orders"><?= number_format($overview['total_orders']); ?></b>
        </span>
      </div>
    </div>

    <div class="row g-2">
      <!-- 1. Gross Purchase Amount -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-shopping-cart text-primary me-50"></i> Gross Purchase</span>
            <span class="badge badge-light-primary">All Channels</span>
          </div>
          <div class="stat-value text-primary" id="val_gross_purchase">
            ₹ <?= number_format($overview['gross_purchase_amount'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="val_gross_white">₹ <?= number_format($overview['white_purchase_amount'], 2); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="val_gross_black">₹ <?= number_format($overview['black_purchase_amount'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span title="Official: $<?= number_format($overview['white_usd'], 2); ?> | Unofficial: $<?= number_format($overview['black_usd'], 2); ?>"><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="val_gross_usd">$ <?= number_format($overview['total_usd'], 2); ?></b></span>
            <span title="Official: ¥<?= number_format($overview['white_rmb'], 2); ?> | Unofficial: ¥<?= number_format($overview['black_rmb'], 2); ?>"><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="val_gross_rmb">¥ <?= number_format($overview['total_rmb'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- 2. Import Purchase Share -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-info-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-globe text-info me-50"></i> Import Purchase</span>
            <span class="badge badge-light-info"><?= number_format($import_summary['total_orders']); ?> POs</span>
          </div>
          <div class="stat-value text-info" id="val_import_purchase">
            ₹ <?= number_format($import_summary['gross_purchase_amount'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Official: <b class="text-info" id="val_import_white">₹ <?= number_format($import_summary['white_purchase_amount'], 2); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="val_import_black">₹ <?= number_format($import_summary['black_purchase_amount'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span title="Official: $<?= number_format($import_summary['white_usd'], 2); ?> | Unofficial: $<?= number_format($import_summary['black_usd'], 2); ?>"><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="val_import_usd">$ <?= number_format($import_summary['total_usd'], 2); ?></b></span>
            <span title="Official: ¥<?= number_format($import_summary['white_rmb'], 2); ?> | Unofficial: ¥<?= number_format($import_summary['black_rmb'], 2); ?>"><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="val_import_rmb">¥ <?= number_format($import_summary['total_rmb'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- 3. Local Purchase Share -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-warning-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-truck text-warning me-50"></i> Local Purchase</span>
            <span class="badge badge-light-warning"><?= number_format($local_summary['total_orders']); ?> POs</span>
          </div>
          <div class="stat-value text-warning" id="val_local_purchase">
            ₹ <?= number_format($local_summary['gross_purchase_amount'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Official: <b class="text-warning" id="val_local_white">₹ <?= number_format($local_summary['white_purchase_amount'], 2); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="val_local_black">₹ <?= number_format($local_summary['black_purchase_amount'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="val_local_usd">$ 0.00</b></span>
            <span><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="val_local_rmb">¥ 0.00</b></span>
          </div>
        </div>
      </div>

      <!-- 4. PO Operational Expense -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-danger-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-file-minus text-danger me-50"></i> PO Expenses</span>
            <span class="badge badge-light-danger"><?= number_format($expense_summary['total_count']); ?> Entries</span>
          </div>
          <div class="stat-value text-danger" id="val_expense_purchase">
            ₹ <?= number_format($expense_summary['gross_expense_amount'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="val_expense_white">₹ <?= number_format($expense_summary['white_expense_amount'], 2); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="val_expense_black">₹ <?= number_format($expense_summary['black_expense_amount'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span title="Official: $<?= number_format($expense_summary['white_usd'], 2); ?> | Unofficial: $<?= number_format($expense_summary['black_usd'], 2); ?>"><span class="curr-badge curr-usd">USD</span> <b class="text-dark" id="val_expense_usd">$ <?= number_format($expense_summary['total_usd'], 2); ?></b></span>
            <span title="Official: ¥<?= number_format($expense_summary['white_rmb'], 2); ?> | Unofficial: ¥<?= number_format($expense_summary['black_rmb'], 2); ?>"><span class="curr-badge curr-rmb">RMB</span> <b class="text-dark" id="val_expense_rmb">¥ <?= number_format($expense_summary['total_rmb'], 2); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 3. SUPPLIER & VENDOR PAYMENTS & OUTSTANDINGS-->
  <!-- ========================================== -->
  <div class="dashboard-section-card">
    <div class="section-header">
      <div class="section-header-left">
        <div class="section-icon-badge" style="background-color: #dcfce7; color: #16a34a;">
          <i class="feather icon-dollar-sign"></i>
        </div>
        <div>
          <h2 class="section-title mb-0">Supplier & Vendor Payments & Outstandings</h2>
          <span class="text-muted" style="font-size: 11px;">Official & Unofficial settlements</span>
        </div>
      </div>
      <div class="d-flex align-items-center gap-1 flex-wrap">
        <span class="badge badge-light-success">Active Suppliers: <?= number_format($supplier_outstanding['supplier_count']); ?></span>
        <span class="badge badge-light-primary">Active Vendors: <?= number_format($vendor_outstanding['vendor_count']); ?></span>
      </div>
    </div>

    <div class="row g-2">
      <!-- 1. Supplier Payments (Period) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-success-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-check-circle text-success me-50"></i> Supplier Payments</span>
            <span class="badge badge-light-success"><?= number_format($supplier_payments['total_count']); ?> Paid</span>
          </div>
          <div class="stat-value text-success" id="val_supp_pay_total">
            ₹ <?= number_format($supplier_payments['gross_payment_amount'], 2); ?>
          </div>
          <div class="stat-detail">
            <span><i class="feather icon-credit-card text-primary me-25"></i> Official: <b class="text-primary" id="val_supp_pay_white">₹ <?= number_format($supplier_payments['white_payment_amount'], 2); ?></b></span>
            <span><i class="feather icon-inbox text-success me-25"></i> Unofficial: <b class="text-success" id="val_supp_pay_black">₹ <?= number_format($supplier_payments['black_payment_amount'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1">
            <span>Local: <b>₹ <?= number_format($supplier_payments['local_payments']['total'], 2); ?></b></span>
            <span>Import: <b>₹ <?= number_format($supplier_payments['import_payments']['total'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span class="text-muted small">
              <span class="curr-badge curr-usd">USD</span> $ <?= number_format($supplier_payments['gross_usd'], 2); ?> • 
              <span class="curr-badge curr-rmb">RMB</span> ¥ <?= number_format($supplier_payments['gross_rmb'], 2); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Vendor Payments (Period) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-info-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-truck text-info me-50"></i> Vendor Payments</span>
            <span class="badge badge-light-info"><?= number_format($vendor_payments['total_count']); ?> Paid</span>
          </div>
          <div class="stat-value text-info" id="val_vend_pay_total">
            ₹ <?= number_format($vendor_payments['gross_payment_amount'], 2); ?>
          </div>
          <div class="stat-detail">
            <span><i class="feather icon-credit-card text-primary me-25"></i> Official: <b class="text-primary" id="val_vend_pay_white">₹ <?= number_format($vendor_payments['white_payment_amount'], 2); ?></b></span>
            <span><i class="feather icon-inbox text-info me-25"></i> Unofficial: <b class="text-info" id="val_vend_pay_black">₹ <?= number_format($vendor_payments['black_payment_amount'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1">
            <span>Transactions: <b><?= number_format($vendor_payments['total_count']); ?></b></span>
            <span>Type: <b>Expense Vendors</b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span class="text-muted small">
              <span class="curr-badge curr-usd">USD</span> $ <?= number_format($vendor_payments['gross_usd'], 2); ?> • 
              <span class="curr-badge curr-rmb">RMB</span> ¥ <?= number_format($vendor_payments['gross_rmb'], 2); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- 3. Total Supplier Outstandings (Real-time Snapshot) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-warning-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-alert-circle text-warning me-50"></i> Supplier Outstanding</span>
            <!-- <span class="badge badge-light-warning">Real-time</span> -->
          </div>
          <div class="stat-value text-dark" id="val_supp_out_total">
            ₹ <?= number_format($supplier_outstanding['total_outstanding_inr'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="val_supp_out_white">₹ <?= number_format($supplier_outstanding['official_outstanding'], 2); ?></b></span>
            <span>Unofficial: <b class="text-secondary" id="val_supp_out_black">₹ <?= number_format($supplier_outstanding['unofficial_outstanding'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1">
            <span>Local Dues: <b class="text-dark">₹ <?= number_format($supplier_outstanding['local_outstanding_inr'], 2); ?></b></span>
            <span>Import Dues: <b class="text-dark">₹ <?= number_format($supplier_outstanding['import_outstanding_inr'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span class="text-muted small">
              <span class="curr-badge curr-usd">USD</span> $ <?= number_format($supplier_outstanding['total_outstanding_usd'], 2); ?> • 
              <span class="curr-badge curr-rmb">RMB</span> ¥ <?= number_format($supplier_outstanding['total_outstanding_rmb'], 2); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- 4. Total Vendors Outstanding (Real-time Snapshot) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="cash-stat-card card-danger-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-alert-triangle text-danger me-50"></i> Vendor Outstanding</span>
            <!-- <span class="badge badge-light-danger">Real-time</span> -->
          </div>
          <div class="stat-value text-dark" id="val_vend_out_total">
            ₹ <?= number_format($vendor_outstanding['total_outstanding_inr'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Official: <b class="text-primary" id="val_vend_out_white">₹ <?= number_format($vendor_outstanding['official_outstanding'], 2); ?></b></span>
            <span>Unofficial: <b class="text-danger" id="val_vend_out_black">₹ <?= number_format($vendor_outstanding['unofficial_outstanding'], 2); ?></b></span>
          </div>
          <div class="stat-detail mt-1">
            <span>Active Vendors: <b><?= number_format($vendor_outstanding['vendor_count']); ?></b></span>
            <span>Type: <b>Service Vendors</b></span>
          </div>
          <div class="stat-detail mt-1 pt-50">
            <span class="text-muted small">
              <span class="curr-badge curr-usd">USD</span> $ <?= number_format($vendor_outstanding['total_outstanding_usd'], 2); ?> • 
              <span class="curr-badge curr-rmb">RMB</span> ¥ <?= number_format($vendor_outstanding['total_outstanding_rmb'], 2); ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 4. ANALYTICS & VISUAL TRENDS (APEXCHARTS)   -->
  <!-- ========================================== -->
  <div class="row g-2 mb-2">
    <!-- Trend Chart: Daily Purchases White vs Black -->
    <div class="col-12 col-xl-8">
      <div class="dashboard-section-card h-100 mb-0">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #ede9fe; color: #7367f0;">
              <i class="feather icon-trending-up"></i>
            </div>
            <h2 class="section-title">
              Purchase Trend
              <span class="badge badge-light-secondary font-weight-normal" style="font-size: 11px;">White vs Black Time Series</span>
            </h2>
          </div>
          <span class="badge badge-light-primary"><?= $daily_trend['is_monthly'] ? 'Monthly View' : 'Daily View'; ?></span>
        </div>
        <div id="chart_purchase_trend" style="min-height: 280px;"></div>
      </div>
    </div>

    <!-- Donut Charts: Channel Mix & White vs Black -->
    <div class="col-12 col-xl-4">
      <div class="dashboard-section-card h-100 mb-0">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #fef3c7; color: #d97706;">
              <i class="feather icon-pie-chart"></i>
            </div>
            <h2 class="section-title">Purchase Distributions</h2>
          </div>
        </div>
        <div id="chart_channel_donut" style="min-height: 250px;"></div>
        <div class="d-flex justify-content-around mt-1 pt-1 border-top text-center">
          <div>
            <span class="tag-import">Import</span>
            <div class="fw-bolder mt-25">₹ <?= number_format($import_summary['gross_purchase_amount'], 2); ?></div>
          </div>
          <div>
            <span class="tag-local">Local</span>
            <div class="fw-bolder mt-25">₹ <?= number_format($local_summary['gross_purchase_amount'], 2); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 5. Purchase CHANNEL SUMMARY MATRIX      -->
  <!-- ========================================== -->
  <div class="dashboard-section-card">
    <div class="section-header">
      <div class="section-header-left">
        <div class="section-icon-badge" style="background-color: #e0f2fe; color: #0284c7;">
          <i class="feather icon-file-text"></i>
        </div>
        <h2 class="section-title">
          Purchase & Payment Summary
          <span class="badge badge-light-primary small font-weight-normal" style="font-size: 11px;"><?= htmlspecialchars($company_info['name'] ?? 'Active Company'); ?></span>
        </h2>
      </div>
      <div>
        <span class="badge badge-light-info">Audited Ledger View</span>
      </div>
    </div>

    <div class="dashboard-table-container">
      <table class="dashboard-table" id="table_procurement_matrix">
        <thead>
          <tr>
            <th>Channel / Category</th>
            <th class="text-center">Orders / Entries</th>
            <th class="text-end">Basic Value</th>
            <th class="text-end">Taxes (GST / Duty)</th>
            <th class="text-end">White (Official)</th>
            <th class="text-end">Black (Cash / Diff)</th>
            <th class="text-end">Gross Total</th>
            <th class="text-end">Payments Made</th>
            <th class="text-end">Net Outstanding</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($channel_matrix['rows'])): ?>
            <?php foreach ($channel_matrix['rows'] as $r): ?>
              <tr>
                <td>
                  <span class="<?= $r['type_tag']; ?>"><?= htmlspecialchars($r['channel']); ?></span>
                </td>
                <td class="text-center fw-bold"><?= number_format($r['orders_count']); ?></td>
                <td class="text-end text-dark">₹ <?= number_format($r['basic_value'], 2); ?></td>
                <td class="text-end text-info">₹ <?= number_format($r['tax_amount'], 2); ?></td>
                <td class="text-end text-primary fw-bold">₹ <?= number_format($r['white_amount'], 2); ?></td>
                <td class="text-end text-secondary fw-bold">₹ <?= number_format($r['black_amount'], 2); ?></td>
                <td class="text-end fw-bolder text-dark">₹ <?= number_format($r['gross_total'], 2); ?></td>
                <td class="text-end text-success fw-bold">₹ <?= number_format($r['paid_amount'], 2); ?></td>
                <td class="text-end fw-bold <?= ($r['outstanding'] > 0) ? 'text-warning' : 'text-muted'; ?>">
                  ₹ <?= number_format($r['outstanding'], 2); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot style="background: #f8f8fb; font-weight: 700;">
          <tr>
            <td>Total Procurement (Gross)</td>
            <td class="text-center"><?= number_format($channel_matrix['totals']['orders_count']); ?></td>
            <td class="text-end">₹ <?= number_format($channel_matrix['totals']['basic_value'], 2); ?></td>
            <td class="text-end text-info">₹ <?= number_format($channel_matrix['totals']['tax_amount'], 2); ?></td>
            <td class="text-end text-primary">₹ <?= number_format($channel_matrix['totals']['white_amount'], 2); ?></td>
            <td class="text-end text-secondary">₹ <?= number_format($channel_matrix['totals']['black_amount'], 2); ?></td>
            <td class="text-end text-dark">₹ <?= number_format($channel_matrix['totals']['gross_total'], 2); ?></td>
            <td class="text-end text-success">₹ <?= number_format($channel_matrix['totals']['paid_amount'], 2); ?></td>
            <td class="text-end text-warning">₹ <?= number_format($channel_matrix['totals']['outstanding'], 2); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 6. TOP SUPPLIERS & RECENT ACTIVITIES       -->
  <!-- ========================================== -->
  <div class="row g-2 mb-2">
    <!-- Top 5 Suppliers by Volume -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card h-100 mb-0">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #ede9fe; color: #7367f0;">
              <i class="feather icon-award"></i>
            </div>
            <h2 class="section-title">Top Suppliers by Volume</h2>
          </div>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Supplier</th>
                <th>Type</th>
                <th class="text-center">Orders</th>
                <th class="text-end">White Amt</th>
                <th class="text-end">Black Amt</th>
                <th class="text-end">Total Amt</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($top_suppliers)): ?>
                <?php foreach ($top_suppliers as $s): ?>
                  <tr>
                    <td>
                      <span class="fw-bold text-dark"><?= htmlspecialchars($s['supplier_name']); ?></span>
                    </td>
                    <td>
                      <span class="<?= ($s['method'] == 'import') ? 'tag-import' : 'tag-local'; ?>">
                        <?= strtoupper($s['method']); ?>
                      </span>
                    </td>
                    <td class="text-center"><?= number_format($s['order_count']); ?></td>
                    <td class="text-end text-primary">₹ <?= number_format($s['white_amt'], 2); ?></td>
                    <td class="text-end text-secondary">₹ <?= number_format($s['black_amt'], 2); ?></td>
                    <td class="text-end fw-bolder text-dark">₹ <?= number_format($s['total_amt'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-2">No supplier records found for this period.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Top 5 Outstanding Payables -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card h-100 mb-0">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #fee2e2; color: #dc2626;">
              <i class="feather icon-alert-triangle"></i>
            </div>
            <h2 class="section-title">Top Supplier Outstanding (Payables)</h2>
          </div>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Supplier</th>
                <th>Type</th>
                <th>Contact</th>
                <th class="text-end">Outstanding INR</th>
                <th class="text-end">USD / RMB</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($top_outstanding)): ?>
                <?php foreach ($top_outstanding as $o): ?>
                  <tr>
                    <td>
                      <span class="fw-bold text-dark"><?= htmlspecialchars($o['name']); ?></span>
                    </td>
                    <td>
                      <span class="<?= ($o['type'] == 'import') ? 'tag-import' : 'tag-local'; ?>">
                        <?= strtoupper($o['type']); ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($o['contact_no'] ?: '—'); ?></td>
                    <td class="text-end fw-bolder text-danger">₹ <?= number_format($o['outstanding_inr'], 2); ?></td>
                    <td class="text-end">
                      <?php if ($o['outstanding_usd'] > 0): ?>
                        <span class="curr-badge">USD</span> $ <?= number_format($o['outstanding_usd'], 2); ?>
                      <?php endif; ?>
                      <?php if ($o['outstanding_rmb'] > 0): ?>
                        <span class="curr-badge">RMB</span> ¥ <?= number_format($o['outstanding_rmb'], 2); ?>
                      <?php endif; ?>
                      <?php if ($o['outstanding_usd'] <= 0 && $o['outstanding_rmb'] <= 0): ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-2">No pending outstanding payables recorded.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 7. RECENT ORDERS & PAYMENTS                -->
  <!-- ========================================== -->
  <div class="row g-2 mb-2">
    <!-- Recent Purchase Orders -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card h-100 mb-0">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #ede9fe; color: #7367f0;">
              <i class="feather icon-file-text"></i>
            </div>
            <h2 class="section-title">Recent Purchase Orders</h2>
          </div>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Voucher #</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Method</th>
                <th class="text-end">White</th>
                <th class="text-end">Black</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_purchases)): ?>
                <?php foreach ($recent_purchases as $po): ?>
                  <tr>
                    <td><span class="fw-bold text-primary"><?= htmlspecialchars($po['voucher_no']); ?></span></td>
                    <td><?= date('d M, Y', strtotime($po['date'])); ?></td>
                    <td><?= htmlspecialchars($po['supplier_name']); ?></td>
                    <td>
                      <span class="<?= ($po['method'] == 'import') ? 'tag-import' : 'tag-local'; ?>">
                        <?= strtoupper($po['method']); ?>
                      </span>
                    </td>
                    <td class="text-end text-primary">₹ <?= number_format($po['white_amt'], 2); ?></td>
                    <td class="text-end text-secondary">₹ <?= number_format($po['black_amt'], 2); ?></td>
                    <td class="text-end fw-bolder text-dark">₹ <?= number_format($po['total_amt'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-2">No recent purchase orders.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent Supplier Payments -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card h-100 mb-0">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #dcfce7; color: #16a34a;">
              <i class="feather icon-check-square"></i>
            </div>
            <h2 class="section-title">Recent Supplier Payments</h2>
          </div>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Supplier</th>
                <th>Inv / Ref #</th>
                <th>Type</th>
                <th>Method</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_payments)): ?>
                <?php foreach ($recent_payments as $pay): ?>
                  <tr>
                    <td><?= date('d M, Y', strtotime($pay['date'])); ?></td>
                    <td><?= htmlspecialchars($pay['supplier_name']); ?></td>
                    <td><?= htmlspecialchars($pay['invoice_no'] ?: '—'); ?></td>
                    <td>
                      <span class="<?= ($pay['payment_type'] == 'official') ? 'tag-official' : 'tag-unofficial'; ?>">
                        <?= ucfirst($pay['payment_type']); ?>
                      </span>
                    </td>
                    <td>
                      <span class="<?= (strtolower($pay['payment_method']) == 'cash') ? 'tag-cash' : 'tag-bank'; ?>">
                        <?= ucfirst($pay['payment_method']); ?>
                      </span>
                    </td>
                    <td class="text-end fw-bolder text-success">₹ <?= number_format($pay['amount'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-2">No recent payment transactions.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ========================================== -->
<!-- 8. APEXCHARTS JAVASCRIPT INITIALIZATION    -->
<!-- ========================================== -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  var trendChart = null;
  var channelDonutChart = null;

  var trendData = <?= json_encode($daily_trend); ?>;
  var importSummary = <?= json_encode($import_summary); ?>;
  var localSummary  = <?= json_encode($local_summary); ?>;

  function initPurchaseCharts() {
    // 1. Time-Series Purchase Trend Chart
    var trendEl = document.querySelector("#chart_purchase_trend");
    if (trendEl && trendData && trendData.categories) {
      var trendOptions = {
        chart: {
          type: 'area',
          height: 290,
          toolbar: { show: false },
          fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif'
        },
        series: trendData.series,
        colors: ['#7367f0', '#82868b', '#28c76f'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0.05,
            stops: [0, 90, 100]
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          fontSize: '12px',
          markers: { radius: 12 }
        },
        xaxis: {
          categories: trendData.categories,
          labels: {
            style: { colors: '#6e6b7b', fontSize: '11px' }
          },
          axisBorder: { show: false },
          axisTicks: { show: false }
        },
        yaxis: {
          labels: {
            style: { colors: '#6e6b7b', fontSize: '11px' },
            formatter: function(val) {
              if (Math.abs(val) >= 10000000) return '₹ ' + (val / 10000000).toFixed(1) + 'Cr';
              if (Math.abs(val) >= 100000) return '₹ ' + (val / 100000).toFixed(1) + 'L';
              if (Math.abs(val) >= 1000) return '₹ ' + (val / 1000).toFixed(0) + 'k';
              return '₹ ' + val;
            }
          }
        },
        tooltip: {
          theme: 'light',
          y: {
            formatter: function(val) {
              return '₹ ' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2 });
            }
          }
        }
      };

      trendChart = new ApexCharts(trendEl, trendOptions);
      trendChart.render();
    }

    // 2. Channel Mix Donut Chart (Import vs Local)
    var donutEl = document.querySelector("#chart_channel_donut");
    var impTotal = Number(importSummary.gross_purchase_amount) || 0;
    var locTotal = Number(localSummary.gross_purchase_amount) || 0;

    if (donutEl && (impTotal + locTotal) > 0) {
      function formatDonutCenterINR(num) {
        num = Number(num);
        if (num >= 10000000) return '₹ ' + (num / 10000000).toFixed(2) + ' Cr';
        if (num >= 100000) return '₹ ' + (num / 100000).toFixed(2) + ' L';
        if (num >= 1000) return '₹ ' + (num / 1000).toFixed(1) + ' k';
        return '₹ ' + num.toLocaleString('en-IN');
      }

      var donutOptions = {
        series: [impTotal, locTotal],
        labels: ['Import Purchase', 'Local Purchase'],
        chart: {
          type: 'donut',
          height: 250,
          toolbar: { show: false }
        },
        colors: ['#7367f0', '#ff9f43'],
        stroke: { width: 2, colors: ['#ffffff'] },
        legend: { show: false },
        dataLabels: { enabled: false },
        plotOptions: {
          pie: {
            donut: {
              size: '75%',
              labels: {
                show: true,
                name: { show: true, fontSize: '11px', color: '#82868b', offsetY: -6 },
                value: {
                  show: true,
                  fontSize: '15px',
                  fontWeight: 700,
                  color: '#1e293b',
                  offsetY: 6,
                  formatter: function(val) { return formatDonutCenterINR(val); }
                },
                total: {
                  show: true,
                  label: 'Total Purchase',
                  fontSize: '11px',
                  fontWeight: 600,
                  color: '#82868b',
                  formatter: function(w) {
                    var total = w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                    return formatDonutCenterINR(total);
                  }
                }
              }
            }
          }
        },
        tooltip: {
          theme: 'light',
          y: {
            formatter: function(val, opts) {
              var total = opts.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
              var pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
              return '₹ ' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + ' (' + pct + '%)';
            }
          }
        }
      };

      channelDonutChart = new ApexCharts(donutEl, donutOptions);
      channelDonutChart.render();
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    initPurchaseCharts();
  });
</script>
