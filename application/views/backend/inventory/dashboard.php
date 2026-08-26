<style>
    .dashboard-wrapper {
        /* background-color: #f8fafc;
        padding: 10px; */
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    
    /* Section Container Card */
    .dashboard-section-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 14px 18px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);
        border: 1px solid #edf2f7;
        margin-bottom: 16px;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    
    .section-icon-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #ede9fe;
        color: #7c3aed;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    
    /* 5-Column Grid Layout */
    .stats-grid-5 {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }
    
    @media (max-width: 1200px) {
        .stats-grid-5 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .stats-grid-5 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid-5 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
    }
    
    /* Individual Stat Card */
    .stat-card-box {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 10px 14px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 80px;
    }
    
    .stat-card-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card-left {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .stat-card-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin: 0;
    }
    
    .stat-icon-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .stat-card-number {
        font-size: 20px;
        font-weight: 700;
        line-height: 1.1;
        margin: 3px 0 5px 0;
    }
    
    .stat-pill-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        width: fit-content;
    }
    
    /* Theme 1: Purple (0 to 30 Days) */
    .theme-purple .stat-icon-circle {
        background-color: #ede9fe;
        color: #7c3aed;
    }
    .theme-purple .stat-card-number {
        color: #6d28d9;
    }
    .theme-purple .stat-pill-badge {
        background-color: #f5f3ff;
        color: #6d28d9;
    }
    
    /* Theme 2: Blue (31 to 60 Days) */
    .theme-blue .stat-icon-circle {
        background-color: #e0f2fe;
        color: #0284c7;
    }
    .theme-blue .stat-card-number {
        color: #0284c7;
    }
    .theme-blue .stat-pill-badge {
        background-color: #f0f9ff;
        color: #0284c7;
    }
    
    /* Theme 3: Green (61 to 90 Days) */
    .theme-green .stat-icon-circle {
        background-color: #dcfce7;
        color: #16a34a;
    }
    .theme-green .stat-card-number {
        color: #16a34a;
    }
    .theme-green .stat-pill-badge {
        background-color: #f0fdf4;
        color: #16a34a;
    }
    
    /* Theme 4: Orange (90+ Days) */
    .theme-orange .stat-icon-circle {
        background-color: #ffedd5;
        color: #ea580c;
    }
    .theme-orange .stat-card-number {
        color: #ea580c;
    }
    .theme-orange .stat-pill-badge {
        background-color: #fff7ed;
        color: #ea580c;
    }
    
    /* Theme 5: Red (No orders / No calls) */
    .theme-red .stat-icon-circle {
        background-color: #fee2e2;
        color: #ef4444;
    }
    .theme-red .stat-card-number {
        color: #ef4444;
    }
    .theme-red .stat-pill-badge {
        background-color: #fef2f2;
        color: #ef4444;
    }
    .stat-card-box-link {
        text-decoration: none !important;
        color: inherit !important;
        display: block;
        cursor: pointer;
    }

    /* ========================================================
       NEW REPORTING SECTION STYLES
       ======================================================== */
    .reporting-divider {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 32px 0 20px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
    .reporting-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .reporting-title-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }
    .reporting-main-title {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .reporting-subtitle {
        font-size: 13px;
        color: #64748b;
        margin: 2px 0 0 0;
    }

    /* Filter Toolbar */
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .filter-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        align-items: flex-end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .filter-label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }
    .filter-select, .filter-input {
        height: 38px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 12px;
        font-size: 13px;
        color: #1e293b;
        background-color: #ffffff;
        width: 100%;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .filter-select:focus, .filter-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        outline: none;
    }
    .filter-actions {
        display: flex;
        gap: 8px;
    }
    .btn-filter-apply {
        background: #4f46e5;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 38px;
        transition: background-color 0.2s;
    }
    .btn-filter-apply:hover {
        background: #4338ca;
        color: #ffffff;
    }
    .btn-filter-reset {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 38px;
        text-decoration: none;
        transition: background-color 0.2s;
    }
    .btn-filter-reset:hover {
        background: #e2e8f0;
        color: #1e293b;
        text-decoration: none;
    }

    /* KPI Summary Row */
    .kpi-grid-8 {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
    @media (max-width: 1200px) {
        .kpi-grid-8 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (max-width: 992px) {
        .kpi-grid-8 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 480px) {
        .kpi-grid-8 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }
    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin: 0;
    }
    .kpi-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .kpi-number {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.1;
        margin: 8px 0 6px 0;
        color: #0f172a;
    }
    .kpi-sub {
        font-size: 11px;
        font-weight: 500;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Pipeline Progress Bar (used in Staff Performance) */
    .pipeline-progress-bar {
        height: 6px;
        border-radius: 3px;
        background-color: #e2e8f0;
        overflow: hidden;
        margin-bottom: 6px;
    }
    .pipeline-progress-fill {
        height: 100%;
        border-radius: 3px;
    }

    /* Charts Row */
    .charts-row-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 992px) {
        .charts-row-2 {
            grid-template-columns: 1fr;
        }
    }

    /* Follow-up / Attention Row */
    .attention-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 992px) {
        .attention-row {
            grid-template-columns: 1fr;
        }
    }
    .perf-counter-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 12px;
    }
    .perf-counter-card:last-child {
        margin-bottom: 0;
    }
    .perf-counter-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .perf-counter-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .perf-counter-desc {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }
    .perf-counter-badge {
        font-size: 18px;
        font-weight: 800;
        padding: 4px 14px;
        border-radius: 8px;
    }

    /* Tables */
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
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        padding: 10px 14px;
        border-bottom: 2px solid #e2e8f0;
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
    .badge-status {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }
    .badge-status-lead { background: #e0e7ff; color: #4338ca; }
    .badge-status-customer { background: #dcfce7; color: #15803d; }
    .badge-status-followup { background: #fef3c7; color: #b45309; }
    .badge-status-overdue { background: #fee2e2; color: #b91c1c; }
    .badge-status-lost { background: #f1f5f9; color: #64748b; }
</style>

<div class="dashboard-wrapper">
    <!-- Section 1: Orders -->
    <div class="dashboard-section-card">
        <div class="section-header">
            <div class="section-icon-badge">
                <i class="feather icon-shopping-cart"></i>
            </div>
            <h2 class="section-title">Orders</h2>
        </div>
        <div class="stats-grid-5">
            <!-- Card 1 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/0_30'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-purple">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_0_30']) ? $dashboard_stats['orders_0_30'] : 0; ?></div>
                        <div><span class="stat-pill-badge">0 to 30 Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/31_60'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-blue">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_31_60']) ? $dashboard_stats['orders_31_60'] : 0; ?></div>
                        <div><span class="stat-pill-badge">31 to 60 Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/61_90'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-green">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_61_90']) ? $dashboard_stats['orders_61_90'] : 0; ?></div>
                        <div><span class="stat-pill-badge">61 to 90 Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/90_plus'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-orange">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_90_plus']) ? $dashboard_stats['orders_90_plus'] : 0; ?></div>
                        <div><span class="stat-pill-badge">90+ Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 5 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/no_orders'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-red">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['no_orders']) ? $dashboard_stats['no_orders'] : 0; ?></div>
                        <div><span class="stat-pill-badge">No orders</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>
        </div>
    </div>

   
    <!-- ========================================================
         NEW REPORTING & ANALYTICS SECTION
         ======================================================== -->
    <?php
        $summary         = isset($reporting_data['summary']) ? $reporting_data['summary'] : array();
        $call_trends     = isset($reporting_data['call_trends']) ? $reporting_data['call_trends'] : array();
        $staff_perf      = isset($reporting_data['staff_performance']) ? $reporting_data['staff_performance'] : array();
        $cur_filters     = isset($filters) ? $filters : array();
    ?>

    <!-- Reporting Header & Divider -->
    <div class="reporting-divider">
        <div class="reporting-title-wrap">
            <div class="reporting-title-icon">
                <i class="feather icon-bar-chart-2"></i>
            </div>
            <div>
                <h2 class="reporting-main-title">Leads & Customer Calls Analytics</h2>
                <p class="reporting-subtitle">Detailed activity trends, pipeline conversion, and follow-up metrics</p>
            </div>
        </div>
    </div>

     <!-- Section 2: Calls and Leads -->
    <div class="dashboard-section-card">
        <div class="section-header">
            <div class="section-icon-badge">
                <i class="feather icon-phone-call"></i>
            </div>
            <h2 class="section-title">Calls and Leads</h2>
        </div>
        <div class="stats-grid-5">
            <!-- Card 1 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/0_30'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-purple">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_0_30']) ? $dashboard_stats['calls_0_30'] : 0; ?></div>
                        <div><span class="stat-pill-badge">0 to 30 Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/31_60'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-blue">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_31_60']) ? $dashboard_stats['calls_31_60'] : 0; ?></div>
                        <div><span class="stat-pill-badge">31 to 60 Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/61_90'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-green">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_61_90']) ? $dashboard_stats['calls_61_90'] : 0; ?></div>
                        <div><span class="stat-pill-badge">61 to 90 Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/90_plus'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-orange">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_90_plus']) ? $dashboard_stats['calls_90_plus'] : 0; ?></div>
                        <div><span class="stat-pill-badge">90+ Days</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>

            <!-- Card 5 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/no_calls'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-red">
                    <div class="stat-card-left">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-card-number"><?php echo isset($dashboard_stats['no_calls']) ? $dashboard_stats['no_calls'] : 0; ?></div>
                        <div><span class="stat-pill-badge">No calls</span></div>
                    </div>
                    <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Section 1: KPI Summary Row (8 cards) -->
    <div class="kpi-grid-8">
        <!-- 1. Total Leads -->
        <a href="<?php echo site_url('inventory/leads/all'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #6366f1;">
                <div class="kpi-top">
                    <span class="kpi-label">Total Leads</span>
                    <div class="kpi-icon" style="background-color: #ede9fe; color: #6366f1;"><i class="feather icon-users"></i></div>
                </div>
                <div class="kpi-number"><?php echo number_format($summary['total_leads'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-folder"></i> Current Company</div>
            </div>
        </a>

        <!-- 2. New Leads -->
        <a href="<?php echo site_url('inventory/leads/new'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #0ea5e9;">
                <div class="kpi-top">
                    <span class="kpi-label">New Leads</span>
                    <div class="kpi-icon" style="background-color: #e0f2fe; color: #0284c7;"><i class="feather icon-user-plus"></i></div>
                </div>
                <div class="kpi-number" style="color: #0284c7;"><?php echo number_format($summary['new_leads'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-user-check"></i> Overall New</div>
            </div>
        </a>

        <!-- 3. Today's Follow-up -->
        <a href="<?php echo site_url('inventory/leads/today'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #10b981;">
                <div class="kpi-top">
                    <span class="kpi-label">Today's Follow-up</span>
                    <div class="kpi-icon" style="background-color: #dcfce7; color: #16a34a;"><i class="feather icon-calendar"></i></div>
                </div>
                <div class="kpi-number" style="color: #16a34a;"><?php echo number_format($summary['todays_followups'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-check-circle"></i> Due Today</div>
            </div>
        </a>

        <!-- 4. Upcoming Follow-up -->
        <a href="<?php echo site_url('inventory/leads/upcoming'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #06b6d4;">
                <div class="kpi-top">
                    <span class="kpi-label">Upcoming Follow-up</span>
                    <div class="kpi-icon" style="background-color: #cffafe; color: #0891b2;"><i class="feather icon-clock"></i></div>
                </div>
                <div class="kpi-number" style="color: #0891b2;"><?php echo number_format($summary['upcoming_followups'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-calendar"></i> Scheduled Ahead</div>
            </div>
        </a>

        <!-- 5. Converted Leads -->
        <a href="<?php echo site_url('inventory/leads/moved'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #8b5cf6;">
                <div class="kpi-top">
                    <span class="kpi-label">Converted</span>
                    <div class="kpi-icon" style="background-color: #f3e8ff; color: #8b5cf6;"><i class="feather icon-award"></i></div>
                </div>
                <div class="kpi-number" style="color: #7c3aed;"><?php echo number_format($summary['converted_leads'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-trending-up"></i> To Customer</div>
            </div>
        </a>

        <!-- 6. Lost Leads -->
        <a href="<?php echo site_url('inventory/leads/lost'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #ef4444;">
                <div class="kpi-top">
                    <span class="kpi-label">Lost Leads</span>
                    <div class="kpi-icon" style="background-color: #fee2e2; color: #ef4444;"><i class="feather icon-user-x"></i></div>
                </div>
                <div class="kpi-number" style="color: #ef4444;"><?php echo number_format($summary['lost_leads'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-alert-circle"></i> Total Lost</div>
            </div>
        </a>

        <!-- 7. Calls Done (Today) -->
        <a href="<?php echo site_url('inventory/customer_calls'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #f59e0b;">
                <div class="kpi-top">
                    <span class="kpi-label">Today's Calls</span>
                    <div class="kpi-icon" style="background-color: #fef3c7; color: #d97706;"><i class="feather icon-phone-call"></i></div>
                </div>
                <div class="kpi-number" style="color: #d97706;"><?php echo number_format($summary['calls'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-phone"></i> Today's Calls Done</div>
            </div>
        </a>

        <!-- 8. Missed Leads -->
        <a href="<?php echo site_url('inventory/leads/missed'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #ec4899;">
                <div class="kpi-top">
                    <span class="kpi-label">Missed Leads</span>
                    <div class="kpi-icon" style="background-color: #fce7f3; color: #db2777;"><i class="feather icon-alert-triangle"></i></div>
                </div>
                <div class="kpi-number" style="color: #db2777;"><?php echo number_format($summary['missed_leads'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-clock"></i> Overdue Follow-up</div>
            </div>
        </a>
    </div>

    <!-- Section 2: Call Activity Trend (Full Width - Last 7 Days) -->
    <div class="dashboard-section-card">
        <div class="section-header">
            <div class="section-icon-badge" style="background-color: #ffedd5; color: #ea580c;">
                <i class="feather icon-phone-call"></i>
            </div>
            <h2 class="section-title">Call Activity Trend <span style="font-size: 13px; font-weight: 500; color: #64748b; margin-left: 6px;">(Last 7 Days)</span></h2>
        </div>
        <div id="callTrendChart" style="min-height: 280px;"></div>
    </div>

    <!-- Section 4: Staff Sales & Activity Performance (Admin Only) -->
    <?php if ($this->session->userdata('super_type_id') != 7 && $this->session->userdata('super_type') != 'staff'): ?>
    <div class="dashboard-section-card" style="margin-top: 24px; margin-bottom: 24px;">
        <div class="section-header">
            <div class="section-icon-badge" style="background-color: #ede9fe; color: #7c3aed;">
                <i class="feather icon-award"></i>
            </div>
            <h2 class="section-title">Staff Sales & Activity Performance</h2>
        </div>
        <div class="dashboard-table-container">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Leads Added</th>
                        <th>Active Leads</th>
                        <th>Calls Created</th>
                        <th>Converted</th>
                        <th>Lost</th>
                        <th>Conversion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staff_perf)): ?>
                        <tr><td colspan="7" class="text-center text-muted" style="padding: 24px;">No staff performance records found</td></tr>
                    <?php else: ?>
                        <?php foreach ($staff_perf as $sp): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="section-icon-badge" style="width: 28px; height: 28px; font-size: 12px; background: #e0e7ff; color: #4338ca;">
                                            <i class="feather icon-user"></i>
                                        </div>
                                        <strong><?php echo htmlspecialchars($sp['staff_name']); ?></strong>
                                    </div>
                                </td>
                                <td><span class="badge badge-light-primary"><?php echo number_format($sp['leads_added']); ?></span></td>
                                <td><?php echo number_format($sp['active_leads']); ?></td>
                                <td><span class="badge badge-light-warning"><?php echo number_format($sp['calls_created']); ?></span></td>
                                <td><strong style="color: #16a34a;"><?php echo number_format($sp['converted_leads']); ?></strong></td>
                                <td><span style="color: #ef4444;"><?php echo number_format($sp['lost_leads']); ?></span></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="pipeline-progress-bar" style="width: 80px; height: 6px; margin: 0;">
                                            <div class="pipeline-progress-fill" style="width: <?php echo min(100, $sp['conversion_rate']); ?>%; background-color: #10b981;"></div>
                                        </div>
                                        <strong style="font-size: 12px;"><?php echo $sp['conversion_rate']; ?>%</strong>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Multi-Bar Call Activity Trend Chart Data (Last 7 Days)
    var callDates = <?php echo json_encode(array_column($call_trends, 'formatted_date')); ?>;
    var customerCallCounts = <?php echo json_encode(array_map('intval', array_column($call_trends, 'customer_calls'))); ?>;
    var leadCallCounts = <?php echo json_encode(array_map('intval', array_column($call_trends, 'lead_calls'))); ?>;

    if (callDates.length === 0) {
        callDates = ['No Data'];
        customerCallCounts = [0];
        leadCallCounts = [0];
    }

    var callOptions = {
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 5,
                columnWidth: '45%'
            }
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '11px',
                colors: ['#fff']
            },
            formatter: function(val) {
                return val > 0 ? val : '';
            }
        },
        colors: ['#6366f1', '#f97316'],
        series: [
            {
                name: 'Customer Calls',
                data: customerCallCounts
            },
            {
                name: 'Lead Calls',
                data: leadCallCounts
            }
        ],
        xaxis: {
            categories: callDates,
            labels: { style: { colors: '#64748b', fontSize: '12px', fontWeight: 600 } }
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                style: { colors: '#64748b', fontSize: '11px' },
                formatter: function(val) {
                    return Math.floor(val);
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '13px',
            fontFamily: 'inherit',
            fontWeight: 500,
            markers: {
                radius: 12
            }
        },
        grid: { borderColor: '#f1f5f9' },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    return val + ' calls';
                }
            }
        }
    };

    var callChartEl = document.querySelector("#callTrendChart");
    if (callChartEl && typeof ApexCharts !== 'undefined') {
        var callChart = new ApexCharts(callChartEl, callOptions);
        callChart.render();
    }
});
</script>