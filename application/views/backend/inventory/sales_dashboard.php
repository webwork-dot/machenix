<style>
  .sales-dash-wrapper {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  }

  /* Section Card (matches dashboard.php) */
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

  /* Filter Toolbar */
  .filter-bar-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #ebe9f1;
    padding: 14px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
  }

  .filter-btn-group .btn {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 14px;
    border-color: #d8d6de;
    color: #5e5873;
  }
  .filter-btn-group .btn.active {
    background-color: #7367f0 !important;
    border-color: #7367f0 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
  }

  /* Stat Card (matches payment_receipt.php) */
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

  /* Sales Executive Panel */
  .sales-panel {
    background: #ffffff;
    border: 1px solid #ebe9f1;
    border-radius: 12px;
    padding: 18px 22px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .sales-panel:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
  }

  .panel-header-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6e6b7b;
    margin: 0;
  }
  .panel-main-val {
    font-size: 28px;
    font-weight: 800;
    color: #2b2b2b;
    margin: 6px 0 2px 0;
  }
  .panel-sub-desc {
    font-size: 12px;
    color: #82868b;
  }

  /* Dual Progress Split Bar */
  .ratio-progress-bar {
    height: 8px;
    border-radius: 4px;
    background-color: #f1f5f9;
    overflow: hidden;
    display: flex;
    margin: 14px 0 16px 0;
  }
  .ratio-progress-fill-white {
    background: #7367f0;
    height: 100%;
    transition: width 0.4s ease;
  }
  .ratio-progress-fill-black {
    background: #82868b;
    height: 100%;
    transition: width 0.4s ease;
  }

  .stat-col-box {
    padding: 10px 14px;
    border-radius: 8px;
    background: #f8f8fb;
    border: 1px solid #ebe9f1;
  }
  .stat-col-box.box-white {
    border-left: 3px solid #7367f0;
  }
  .stat-col-box.box-black {
    border-left: 3px solid #82868b;
  }

  /* Modern Dashboard Tables (matches dashboard.php lines 480-515) */
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
    background-color: #ede9fe;
    color: #6d28d9;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
  }
</style>

<div class="content-body sales-dash-wrapper">
  
  <!-- ========================================== -->
  <!-- 1. HEADER & FILTER                         -->
  <!-- ========================================== -->
  <div class="card mb-2" style="border-radius: 10px;">
    <div class="card-body py-1 px-2">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="section-icon-badge" style="background: #ede9fe; color: #7367f0; width: 42px; height: 42px; font-size: 18px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
            <i class="feather icon-trending-up"></i>
          </div>
          <div>
            <h4 class="mb-0 fw-bolder text-dark" style="font-size: 18px;">Sales & Financial Dashboard</h4>
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

  <?php include('filter/sales_dashboard_filter.php'); ?>

  <!-- ========================================== -->
  <!-- 2. SALES, STOCK, EXPENSES & RETURNS        -->
  <!-- ========================================== -->
  <div class="dashboard-section-card">
    <div class="section-header">
      <div class="section-header-left">
        <div class="section-icon-badge" style="background-color: #ede9fe; color: #7367f0;">
          <i class="feather icon-shopping-cart"></i>
        </div>
        <h2 class="section-title">
          Sales, Stock, Expenses & Returns
          <span class="badge badge-light-primary small font-weight-normal" style="font-size: 11px;">Revenue • Stock • Expenses • Returns</span>
        </h2>
      </div>
    </div>

    <div class="row g-2">
      <!-- PANEL 1: SALES REVENUE (AMOUNT) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="sales-panel">
          <div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="panel-header-title"><i class="feather icon-file-text text-primary me-25"></i> Sales Revenue</span>
              <span class="badge badge-light-primary"><?= number_format($sales_summary['total_orders']); ?> Orders</span>
            </div>
            
            <div class="panel-main-val text-primary" id="val_sales_gross">
              ₹ <?= number_format($sales_summary['gross_sales_amount'], 2); ?>
            </div>
            <div class="panel-sub-desc">
              Gross Sales across all orders in this period
            </div>
          </div>

          <?php 
            $s_gross = (float)$sales_summary['gross_sales_amount'];
            $s_white = (float)$sales_summary['white_sales_amount'];
            $s_black = (float)$sales_summary['black_sales_amount'];
          ?>

          <!-- 2-Col Stat Boxes -->
          <div class="row g-2 mt-1">
            <div class="col-6 mt-0">
              <div class="stat-col-box box-white">
                <div class="mb-25">
                  <span class="tag-official">White Sales</span>
                </div>
                <h5 class="mb-0 fw-bolder text-primary" id="val_sales_white">₹ <?= number_format($s_white, 2); ?></h5>
              </div>
            </div>

            <div class="col-6 mt-0">
              <div class="stat-col-box box-black">
                <div class="mb-25">
                  <span class="tag-unofficial">Black Sales</span>
                </div>
                <h5 class="mb-0 fw-bolder text-dark" id="val_sales_black">₹ <?= number_format($s_black, 2); ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PANEL 2: SALES STOCK (QUANTITY) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="sales-panel">
          <div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="panel-header-title"><i class="feather icon-box text-info me-25"></i> Sales Stock Volume</span>
              <?php 
                $avg_rate = ($stock_summary['total_stock_qty'] > 0) ? ($sales_summary['gross_sales_amount'] / $stock_summary['total_stock_qty']) : 0;
              ?>
              <span class="badge badge-light-info">Avg: ₹ <?= number_format($avg_rate, 2); ?> / Unit</span>
            </div>

            <div class="panel-main-val text-dark" id="val_stock_gross">
              <?= number_format($stock_summary['total_stock_qty']); ?> <small style="font-size: 16px; font-weight: 600; color: #82868b;">Units</small>
            </div>
            <div class="panel-sub-desc">
              Gross physical stock dispatched across batches
            </div>
          </div>

          <?php 
            $q_tot = (float)$stock_summary['total_stock_qty'];
            $q_white = (float)$stock_summary['white_stock_qty'];
            $q_black = (float)$stock_summary['black_stock_qty'];
          ?>

          <!-- 2-Col Stat Boxes -->
          <div class="row g-2 mt-1">
            <div class="col-6 mt-0">
              <div class="stat-col-box box-white">
                <div class="mb-25">
                  <span class="tag-official">White Stock</span>
                </div>
                <h5 class="mb-0 fw-bolder text-primary" id="val_stock_white"><?= number_format($q_white); ?> <small style="font-size: 11px;">Units</small></h5>
              </div>
            </div>

            <div class="col-6 mt-0">
              <div class="stat-col-box box-black">
                <div class="mb-25">
                  <span class="tag-unofficial">Black Stock</span>
                </div>
                <h5 class="mb-0 fw-bolder text-dark" id="val_stock_black"><?= number_format($q_black); ?> <small style="font-size: 11px;">Units</small></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PANEL 3: CASH BOOK EXPENSES (BLACK & WHITE) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="sales-panel">
          <div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="panel-header-title"><i class="feather icon-trending-down text-danger me-25"></i> Cash Book Expenses</span>
              <span class="badge badge-light-danger" id="val_expense_count"><?= number_format($expense_summary['total_count'] ?? 0); ?> Records</span>
            </div>

            <?php 
              $e_gross = (float)($expense_summary['gross_expense_amount'] ?? 0);
              $e_white = (float)($expense_summary['white_expense_amount'] ?? 0);
              $e_black = (float)($expense_summary['black_expense_amount'] ?? 0);
            ?>

            <div class="panel-main-val text-danger" id="val_expense_gross">
              ₹ <?= number_format($e_gross, 2); ?>
            </div>
            <div class="panel-sub-desc">
              Cash book expenses for <?= htmlspecialchars($company_info['name'] ?? 'current company'); ?>
            </div>
          </div>

          <!-- 2-Col Stat Boxes -->
          <div class="row g-2 mt-1">
            <div class="col-6 mt-0">
              <div class="stat-col-box box-white">
                <div class="mb-25">
                  <span class="tag-official">White Expense</span>
                </div>
                <h5 class="mb-0 fw-bolder text-primary" id="val_expense_white">₹ <?= number_format($e_white, 2); ?></h5>
              </div>
            </div>

            <div class="col-6 mt-0">
              <div class="stat-col-box box-black">
                <div class="mb-25">
                  <span class="tag-unofficial">Black Expense</span>
                </div>
                <h5 class="mb-0 fw-bolder text-dark" id="val_expense_black">₹ <?= number_format($e_black, 2); ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PANEL 4: SALES RETURNS (BLACK & WHITE) -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="sales-panel">
          <div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="panel-header-title"><i class="feather icon-rotate-ccw text-warning me-25"></i> Sales Returns</span>
              <span class="badge badge-light-warning" id="val_returns_count"><?= number_format($return_summary['total_count'] ?? ($sales_summary['total_returns'] ?? 0)); ?> Returns</span>
            </div>

            <?php 
              $r_gross = (float)($return_summary['total_return_amount'] ?? ($sales_summary['total_return_amount'] ?? 0));
              $r_white = (float)($return_summary['white_return_amount'] ?? ($sales_summary['white_return_amount'] ?? 0));
              $r_black = (float)($return_summary['black_return_amount'] ?? ($sales_summary['black_return_amount'] ?? 0));
            ?>

            <div class="panel-main-val text-warning" id="val_returns_total">
              ₹ <?= number_format($r_gross, 2); ?>
            </div>
            <div class="panel-sub-desc">
              Sales returns for <?= htmlspecialchars($company_info['name'] ?? 'current company'); ?>
            </div>
          </div>

          <!-- 2-Col Stat Boxes -->
          <div class="row g-2 mt-1">
            <div class="col-6 mt-0">
              <div class="stat-col-box box-white">
                <div class="mb-25">
                  <span class="tag-official">White Return</span>
                </div>
                <h5 class="mb-0 fw-bolder text-primary" id="val_returns_white">₹ <?= number_format($r_white, 2); ?></h5>
              </div>
            </div>

            <div class="col-6 mt-0">
              <div class="stat-col-box box-black">
                <div class="mb-25">
                  <span class="tag-unofficial">Black Return</span>
                </div>
                <h5 class="mb-0 fw-bolder text-dark" id="val_returns_black">₹ <?= number_format($r_black, 2); ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 3. CURRENT FUNDS ON HAND                   -->
  <!-- ========================================== -->
  <div class="dashboard-section-card">
    <div class="section-header">
      <div class="section-header-left">
        <div class="section-icon-badge" style="background-color: #dcfce7; color: #16a34a;">
          <i class="feather icon-dollar-sign"></i>
        </div>
        <h2 class="section-title">
          Current Funds on Hand
          <span class="badge badge-light-success small font-weight-normal" style="font-size: 11px;">Real-time Ledger</span>
        </h2>
      </div>
    </div>

    <div class="row g-2">
      <!-- 1. Cash in Hand -->
      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-success-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-inbox text-success me-50"></i> Cash on Hand</span>
            <span class="badge badge-light-success">Cash</span>
          </div>
          <?php $cash_tot = (float)$funds_summary['cash']['total']; ?>
          <div class="stat-value <?= ($cash_tot < 0) ? 'text-danger' : 'text-success'; ?>" id="val_cash_total">
            <?= ($cash_tot < 0 ? '- ₹ ' : '₹ ') . number_format(abs($cash_tot), 2); ?>
          </div>
          <div class="stat-detail">
            <?php 
              $c_off = (float)$funds_summary['cash']['official']; 
              $c_unoff = (float)$funds_summary['cash']['unofficial'];
            ?>
            <span>Official (White): <b class="text-primary"><?= ($c_off < 0 ? '- ₹ ' : '₹ ') . number_format(abs($c_off), 2); ?></b></span>
            <span>Unofficial (Black): <b class="text-secondary"><?= ($c_unoff < 0 ? '- ₹ ' : '₹ ') . number_format(abs($c_unoff), 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- 2. Bank in Hand -->
      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-credit-card text-primary me-50"></i> Bank Balance</span>
            <span class="badge badge-light-primary">Accounts</span>
          </div>
          <?php $bank_tot = (float)$funds_summary['bank']['total']; ?>
          <div class="stat-value" style="color: #7367f0;" id="val_bank_total">
            <?= ($bank_tot < 0 ? '- ₹ ' : '₹ ') . number_format(abs($bank_tot), 2); ?>
          </div>
          <div class="stat-detail">
            <?php 
              $b_off = (float)$funds_summary['bank']['official']; 
              $b_unoff = (float)$funds_summary['bank']['unofficial'];
            ?>
            <span>Official (White): <b class="text-primary"><?= ($b_off < 0 ? '- ₹ ' : '₹ ') . number_format(abs($b_off), 2); ?></b></span>
            <span>Unofficial (Black): <b class="text-secondary"><?= ($b_unoff < 0 ? '- ₹ ' : '₹ ') . number_format(abs($b_unoff), 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- 3. Total Amount -->
      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-warning-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-award text-warning me-50"></i> Total Amount</span>
            <span class="badge badge-light-warning">Cash + Bank</span>
          </div>
          <?php $tot_funds = (float)($funds_summary['total_amount'] ?? ($cash_tot + $bank_tot)); ?>
          <div class="stat-value <?= ($tot_funds < 0) ? 'text-danger' : 'text-dark'; ?>" id="val_funds_total">
            <?= ($tot_funds < 0 ? '- ₹ ' : '₹ ') . number_format(abs($tot_funds), 2); ?>
          </div>
          <div class="stat-detail">
            <span>Cash Share: <b class="text-success">₹ <?= number_format($cash_tot, 2); ?></b></span>
            <span>Bank Share: <b style="color: #7367f0;">₹ <?= number_format($bank_tot, 2); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 4. PAYMENT COLLECTION MATRIX (4-WAY)       -->
  <!-- ========================================== -->
  <div class="dashboard-section-card">
    <div class="section-header">
      <div class="section-header-left">
        <div class="section-icon-badge" style="background-color: #dcfce7; color: #16a34a;">
          <i class="feather icon-check-circle"></i>
        </div>
        <h2 class="section-title">
          Payment Collections
          <span class="badge badge-light-success small font-weight-normal" style="font-size: 11px;">White & Black • Cash & Bank</span>
        </h2>
      </div>
    </div>

    <div class="row g-2">
      <!-- 1. White Collections -->
      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-shield text-primary me-50"></i> Official (White)</span>
            <span class="badge badge-light-primary">Tax Received</span>
          </div>
          <div class="stat-value text-primary" id="val_coll_white_net">
            ₹ <?= number_format($collection_summary['official']['total']['net'], 2); ?>
          </div>
          <div class="stat-detail">
            <span><i class="feather icon-inbox text-success me-25"></i> Cash: <b class="text-success">₹ <?= number_format($collection_summary['official']['cash']['net'], 2); ?></b></span>
            <span><i class="feather icon-credit-card text-primary me-25"></i> Bank: <b style="color: #7367f0;">₹ <?= number_format($collection_summary['official']['cheque']['net'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- 2. Black Collections -->
      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-unofficial">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial (Black)</span>
            <span class="badge badge-light-secondary">Cash Received</span>
          </div>
          <div class="stat-value text-dark" id="val_coll_black_net">
            ₹ <?= number_format($collection_summary['unofficial']['total']['net'], 2); ?>
          </div>
          <div class="stat-detail">
            <span><i class="feather icon-inbox text-success me-25"></i> Cash: <b class="text-success">₹ <?= number_format($collection_summary['unofficial']['cash']['net'], 2); ?></b></span>
            <span><i class="feather icon-credit-card text-primary me-25"></i> Bank: <b style="color: #7367f0;">₹ <?= number_format($collection_summary['unofficial']['cheque']['net'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- 3. Total Net Collections -->
      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-success-top">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-check-square text-success me-50"></i> Total Net Inflow</span>
            <span class="badge badge-light-success"><?= $collection_summary['totals']['total_tx_count']; ?> Receipts</span>
          </div>
          <div class="stat-value text-success" id="val_coll_total_net">
            ₹ <?= number_format($collection_summary['totals']['net_collection'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Total Cash: <b class="text-success">₹ <?= number_format($collection_summary['totals']['total_cash_net'], 2); ?></b></span>
            <span>Total Bank: <b style="color: #7367f0;">₹ <?= number_format($collection_summary['totals']['total_bank_net'], 2); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 5. CHARTS & ANALYTICS                      -->
  <!-- ========================================== -->
  <div class="row g-2 mb-2">
    <!-- Area Trend Chart -->
    <div class="col-12 col-xl-8">
      <div class="dashboard-section-card mb-0 h-100">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #ede9fe; color: #7367f0;">
              <i class="feather icon-activity"></i>
            </div>
            <h2 class="section-title">Sales vs. Collection Timeline</h2>
          </div>
          <span class="badge badge-light-secondary font-weight-normal" style="font-size: 11px;">Revenue Trend</span>
        </div>
        <div id="chart_sales_trend" style="min-height: 290px;"></div>
      </div>
    </div>

    <!-- White vs Black Distribution Chart -->
    <div class="col-12 col-xl-4">
      <div class="dashboard-section-card mb-0 h-100 d-flex flex-column justify-content-between">
        <div>
          <div class="section-header">
            <div class="section-header-left">
              <div class="section-icon-badge" style="background-color: #e0f2fe; color: #0284c7;">
                <i class="feather icon-pie-chart"></i>
              </div>
              <h2 class="section-title">Sales Distribution</h2>
            </div>
            <span class="badge badge-light-primary" style="font-size: 11px;">White vs. Black</span>
          </div>

          <div id="chart_distribution_wrap">
            <?php if ($s_gross > 0): ?>
              <div id="chart_revenue_donut" style="min-height: 220px;"></div>
            <?php else: ?>
              <div class="text-center py-4">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px;">
                  <i class="feather icon-pie-chart"></i>
                </div>
                <h6 class="text-dark fw-bold mb-50">No Sales in Selected Period</h6>
                <p class="text-muted small mb-2" style="font-size: 12px;">Switch period to "This Year" or "All Time" to analyze historical distribution.</p>
                <button type="button" onclick="$('.date-shortcut-btn[data-range=\'this_year\']').click();" class="btn btn-sm btn-outline-primary waves-effect">
                  <i class="feather icon-calendar me-25"></i> View This Year
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($s_gross > 0): ?>
        <div class="distribution-breakdown mt-1 pt-1" style="border-top: 1px solid #f1f5f9;">
          <div class="d-flex align-items-center justify-content-between mb-1" style="background: #f8f8fb; border-radius: 8px; border-left: 3px solid #7367f0; padding: 10px 14px;">
            <div class="d-flex align-items-center">
              <span class="badge" style="background: #ede9fe; color: #7367f0; font-weight: 700; font-size: 11px;">White Sales</span>
            </div>
            <strong class="text-primary" style="font-size: 13.5px;">₹ <?= number_format($s_white, 2); ?></strong>
          </div>

          <div class="d-flex align-items-center justify-content-between" style="background: #f8f8fb; border-radius: 8px; border-left: 3px solid #82868b; padding: 10px 14px;">
            <div class="d-flex align-items-center">
              <span class="badge" style="background: #eceff1; color: #546e7a; font-weight: 700; font-size: 11px;">Black Sales</span>
            </div>
            <strong class="text-dark" style="font-size: 13.5px;">₹ <?= number_format($s_black, 2); ?></strong>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 6. TOP PERFORMERS (PRODUCTS & CUSTOMERS)   -->
  <!-- ========================================== -->
  <div class="row g-2 mb-2">
    <!-- Top 5 Products -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card mb-0 h-100">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #fef3c7; color: #d97706;">
              <i class="feather icon-award"></i>
            </div>
            <h2 class="section-title">Top 5 Selling Products</h2>
          </div>
          <span class="badge badge-light-warning" style="font-size: 11px;">By Revenue</span>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Product</th>
                <th class="text-center">White Qty</th>
                <th class="text-center">Black Qty</th>
                <th class="text-center">Total Qty</th>
                <th class="text-end">Total Amount</th>
              </tr>
            </thead>
            <tbody id="tbody_top_products">
              <?php if (!empty($top_products)): ?>
                <?php foreach ($top_products as $p): ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= htmlspecialchars($p['product_name']); ?></strong>
                      <small class="text-muted d-block" style="font-size: 11px;"><?= htmlspecialchars($p['item_code']); ?></small>
                    </td>
                    <td class="text-center"><span class="tag-official"><?= number_format($p['white_qty']); ?></span></td>
                    <td class="text-center"><span class="tag-unofficial"><?= number_format($p['black_qty']); ?></span></td>
                    <td class="text-center fw-bold"><?= number_format($p['total_qty']); ?></td>
                    <td class="text-end fw-bolder text-primary">₹ <?= number_format($p['total_amount'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="feather icon-inbox d-block mb-50" style="font-size: 20px;"></i>
                    No product sales found for this period
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Top 5 Customers -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card mb-0 h-100">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #ede9fe; color: #7367f0;">
              <i class="feather icon-users"></i>
            </div>
            <h2 class="section-title">Top 5 Customers by Value</h2>
          </div>
          <span class="badge badge-light-primary" style="font-size: 11px;">By Revenue</span>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Customer</th>
                <th class="text-center">Orders</th>
                <th class="text-end">White Sales</th>
                <th class="text-end">Black Sales</th>
                <th class="text-end">Total Sales</th>
              </tr>
            </thead>
            <tbody id="tbody_top_customers">
              <?php if (!empty($top_customers)): ?>
                <?php foreach ($top_customers as $c): ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= htmlspecialchars($c['customer_name']); ?></strong>
                    </td>
                    <td class="text-center"><span class="badge badge-light-secondary"><?= $c['order_count']; ?></span></td>
                    <td class="text-end text-primary fw-bold">₹ <?= number_format($c['white_sales'], 2); ?></td>
                    <td class="text-end text-dark fw-bold">₹ <?= number_format($c['black_sales'], 2); ?></td>
                    <td class="text-end fw-bolder text-success">₹ <?= number_format($c['total_sales'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="feather icon-user-x d-block mb-50" style="font-size: 20px;"></i>
                    No customer sales found for this period
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- 7. RECENT TRANSACTIONS                     -->
  <!-- ========================================== -->
  <div class="row g-2">
    <!-- Recent Sales Orders -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card mb-0 h-100">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #e0f2fe; color: #0284c7;">
              <i class="feather icon-clock"></i>
            </div>
            <h2 class="section-title">Recent Sales Orders</h2>
          </div>
          <a href="<?= site_url('inventory/sales-order'); ?>" class="btn btn-sm btn-outline-primary py-25 px-50" style="font-size: 11px;">View All Orders</a>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Order / Date</th>
                <th>Customer</th>
                <th class="text-end">White</th>
                <th class="text-end">Black</th>
                <th class="text-end">Grand Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_sales)): ?>
                <?php foreach ($recent_sales as $so): ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= htmlspecialchars($so['order_no']); ?></strong>
                      <small class="text-muted d-block" style="font-size: 11px;"><?= date('d M, Y', strtotime($so['date'])); ?></small>
                    </td>
                    <td><?= htmlspecialchars($so['customer_name']); ?></td>
                    <td class="text-end text-primary fw-bold">₹ <?= number_format($so['white_amt'], 2); ?></td>
                    <td class="text-end text-dark fw-bold">₹ <?= number_format($so['black_amt'], 2); ?></td>
                    <td class="text-end fw-bolder text-success">₹ <?= number_format($so['grand_total'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No recent orders recorded</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent Collections -->
    <div class="col-12 col-lg-6">
      <div class="dashboard-section-card mb-0 h-100">
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-badge" style="background-color: #dcfce7; color: #16a34a;">
              <i class="feather icon-dollar-sign"></i>
            </div>
            <h2 class="section-title">Recent Payment Collections</h2>
          </div>
          <a href="<?= site_url('inventory/payment-receipt'); ?>" class="btn btn-sm btn-outline-success py-25 px-50" style="font-size: 11px;">View All Receipts</a>
        </div>

        <div class="dashboard-table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Receipt / Date</th>
                <th>Customer</th>
                <th class="text-center">Type</th>
                <th class="text-center">Method</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_collections)): ?>
                <?php foreach ($recent_collections as $cp): ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= !empty($cp['inv_no']) ? htmlspecialchars($cp['inv_no']) : '#' . $cp['id']; ?></strong>
                      <small class="text-muted d-block" style="font-size: 11px;"><?= date('d M, Y', strtotime($cp['date'])); ?></small>
                    </td>
                    <td><?= htmlspecialchars($cp['customer_name']); ?></td>
                    <td class="text-center">
                      <span class="<?= ($cp['payment_type'] == 'official') ? 'tag-official' : 'tag-unofficial'; ?>">
                        <?= ucfirst($cp['payment_type']); ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="<?= (strtolower($cp['payment_method']) == 'cash') ? 'tag-cash' : 'tag-bank'; ?>">
                        <?= (strtolower($cp['payment_method']) == 'cash') ? 'Cash' : 'Bank'; ?>
                      </span>
                    </td>
                    <td class="text-end fw-bolder <?= ($cp['payment_mode'] == 'return') ? 'text-danger' : 'text-success'; ?>">
                      <?= ($cp['payment_mode'] == 'return') ? '- ₹ ' : '₹ '; ?><?= number_format($cp['amount'], 2); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No recent collections recorded</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ApexCharts Script Initialization -->
<script>
  var trendChart = null;
  var revDonutChart = null;

  var dailyData = <?= json_encode($daily_trend); ?>;
  var salesSummary = <?= json_encode($sales_summary); ?>;
  var expenseSummary = <?= json_encode($expense_summary ?? []); ?>;
  var returnSummary = <?= json_encode($return_summary ?? []); ?>;

  function initSalesCharts() {
    // 1. Sales vs Collection Timeline Chart
    var categories = dailyData.map(function(d) { return d.date; });
    var salesSeries = dailyData.map(function(d) { return d.total_sales; });
    var collSeries  = dailyData.map(function(d) { return d.total_collection; });

    var trendOptions = {
      series: [
        { name: 'Total Sales (₹)', data: salesSeries },
        { name: 'Collections (₹)', data: collSeries }
      ],
      chart: {
        height: 290,
        type: 'area',
        toolbar: { show: false },
        fontFamily: 'inherit'
      },
      colors: ['#7367f0', '#28c76f'],
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
        categories: categories,
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

    var trendEl = document.querySelector("#chart_sales_trend");
    if (trendEl) {
      trendChart = new ApexCharts(trendEl, trendOptions);
      trendChart.render();
    }

    // 2. Revenue Donut Chart (White vs Black)
    var whiteAmt = Number(salesSummary.white_sales_amount) || 0;
    var blackAmt = Number(salesSummary.black_sales_amount) || 0;
    
    if ((whiteAmt + blackAmt) > 0) {
      function formatDonutCenterINR(num) {
        num = Number(num);
        if (num >= 10000000) return '₹ ' + (num / 10000000).toFixed(2) + ' Cr';
        if (num >= 100000) return '₹ ' + (num / 100000).toFixed(2) + ' L';
        if (num >= 1000) return '₹ ' + (num / 1000).toFixed(1) + ' k';
        return '₹ ' + num.toLocaleString('en-IN');
      }

      var revDonutOptions = {
        series: [whiteAmt, blackAmt],
        labels: ['White Sales', 'Black Sales'],
        chart: {
          type: 'donut',
          height: 230,
          toolbar: { show: false }
        },
        colors: ['#7367f0', '#82868b'],
        stroke: {
          width: 2,
          colors: ['#ffffff']
        },
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        plotOptions: {
          pie: {
            donut: {
              size: '76%',
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: '11px',
                  fontWeight: 600,
                  color: '#82868b',
                  offsetY: -6
                },
                value: {
                  show: true,
                  fontSize: '15px',
                  fontWeight: 700,
                  color: '#1e293b',
                  offsetY: 6,
                  formatter: function(val) {
                    return formatDonutCenterINR(val);
                  }
                },
                total: {
                  show: true,
                  label: 'Total Sales',
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
      var revEl = document.querySelector("#chart_revenue_donut");
      if (revEl) {
        revDonutChart = new ApexCharts(revEl, revDonutOptions);
        revDonutChart.render();
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    initSalesCharts();
  });
</script>
