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
    .kpi-grid-7 {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
    @media (max-width: 1400px) {
        .kpi-grid-7 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (max-width: 992px) {
        .kpi-grid-7 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .kpi-grid-7 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 480px) {
        .kpi-grid-7 {
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

    /* Pipeline Card */
    .pipeline-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    @media (max-width: 992px) {
        .pipeline-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .pipeline-grid {
            grid-template-columns: repeat(1, 1fr);
        }
    }
    .pipeline-step-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        position: relative;
    }
    .pipeline-step-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .pipeline-step-name {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
    }
    .pipeline-step-count {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }
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
    .pipeline-step-pct {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-align: right;
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

    /* Recent Activity Feed */
    .activity-feed-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .activity-feed-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 12px;
        border-radius: 10px;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
    }
    .activity-feed-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background-color: #ede9fe;
        color: #7c3aed;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .activity-feed-content {
        flex-grow: 1;
    }
    .activity-feed-text {
        font-size: 13px;
        color: #1e293b;
        margin: 0 0 4px 0;
        font-weight: 600;
    }
    .activity-feed-meta {
        font-size: 11px;
        color: #94a3b8;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .custom-date-container {
        display: <?php echo (isset($filters['period']) && $filters['period'] == 'custom') ? 'flex' : 'none'; ?>;
        gap: 10px;
    }
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
        $pipeline        = isset($reporting_data['pipeline']) ? $reporting_data['pipeline'] : array();
        $lead_trends     = isset($reporting_data['lead_trends']) ? $reporting_data['lead_trends'] : array();
        $call_trends     = isset($reporting_data['call_trends']) ? $reporting_data['call_trends'] : array();
        $followup_perf   = isset($reporting_data['followup_perf']) ? $reporting_data['followup_perf'] : array();
        $todays_flw      = isset($reporting_data['todays_followups']) ? $reporting_data['todays_followups'] : array();
        $upcoming_flw    = isset($reporting_data['upcoming_followups']) ? $reporting_data['upcoming_followups'] : array();
        $overdue_flw     = isset($reporting_data['overdue_followups']) ? $reporting_data['overdue_followups'] : array();
        $staff_perf      = isset($reporting_data['staff_performance']) ? $reporting_data['staff_performance'] : array();
        $needs_attention = isset($reporting_data['needs_attention']) ? $reporting_data['needs_attention'] : array();
        $recent_activity = isset($reporting_data['recent_activity']) ? $reporting_data['recent_activity'] : array();
        $cur_filters     = isset($filters) ? $filters : array();

        $tot_pipeline = (int)($pipeline['fresh'] ?? 0) + (int)($pipeline['followup'] ?? 0) + (int)($pipeline['lost'] ?? 0) + (int)($pipeline['converted'] ?? 0);
        $fresh_pct     = $tot_pipeline > 0 ? round(((int)($pipeline['fresh'] ?? 0) / $tot_pipeline) * 100, 1) : 0;
        $follow_pct    = $tot_pipeline > 0 ? round(((int)($pipeline['followup'] ?? 0) / $tot_pipeline) * 100, 1) : 0;
        $lost_pct      = $tot_pipeline > 0 ? round(((int)($pipeline['lost'] ?? 0) / $tot_pipeline) * 100, 1) : 0;
        $converted_pct = $tot_pipeline > 0 ? round(((int)($pipeline['converted'] ?? 0) / $tot_pipeline) * 100, 1) : 0;
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
    
    <!-- Filter Bar Form -->
    <div class="filter-card">
        <form action="<?php echo site_url('inventory/dashboard'); ?>" method="GET" id="dashboardFilterForm">
            <div class="filter-form-grid">
                <!-- Period Filter -->
                <div class="filter-group">
                    <label class="filter-label"><i class="feather icon-calendar"></i> Period</label>
                    <select name="period" id="periodSelect" class="filter-select" onchange="toggleCustomDate(this.value)">
                        <option value="this_month" <?php echo ($cur_filters['period'] ?? '') == 'this_month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="today" <?php echo ($cur_filters['period'] ?? '') == 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="this_week" <?php echo ($cur_filters['period'] ?? '') == 'this_week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="last_month" <?php echo ($cur_filters['period'] ?? '') == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                        <option value="last_30_days" <?php echo ($cur_filters['period'] ?? '') == 'last_30_days' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="custom" <?php echo ($cur_filters['period'] ?? '') == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                    </select>
                </div>

                <!-- Custom Start & End Date -->
                <div class="filter-group custom-date-container" id="customDateWrap">
                    <div>
                        <label class="filter-label">From</label>
                        <input type="date" name="start_date" class="filter-input" value="<?php echo htmlspecialchars($cur_filters['start_date'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="filter-label">To</label>
                        <input type="date" name="end_date" class="filter-input" value="<?php echo htmlspecialchars($cur_filters['end_date'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Staff Filter (Admin only) -->
                <?php if ($this->session->userdata('super_type_id') != 7 && !empty($dashboard_staff_list)): ?>
                <div class="filter-group">
                    <label class="filter-label"><i class="feather icon-user"></i> Staff</label>
                    <select name="staff_id" class="filter-select">
                        <option value="all">All Staff</option>
                        <?php foreach ($dashboard_staff_list as $st): ?>
                            <option value="<?php echo htmlspecialchars($st['name']); ?>" <?php echo ($cur_filters['staff_id'] ?? '') == $st['name'] || ($cur_filters['staff_id'] ?? '') == $st['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($st['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Customer Type Filter -->
                <div class="filter-group">
                    <label class="filter-label"><i class="feather icon-filter"></i> Record Type</label>
                    <select name="type" class="filter-select">
                        <option value="all" <?php echo ($cur_filters['type'] ?? '') == 'all' ? 'selected' : ''; ?>>All (Leads & Customers)</option>
                        <option value="leads" <?php echo ($cur_filters['type'] ?? '') == 'leads' ? 'selected' : ''; ?>>Leads Only</option>
                        <option value="customer" <?php echo ($cur_filters['type'] ?? '') == 'customer' ? 'selected' : ''; ?>>Customers Only</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <label class="filter-label"><i class="feather icon-tag"></i> Status</label>
                    <select name="status" class="filter-select">
                        <option value="all" <?php echo ($cur_filters['status'] ?? '') == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="fresh" <?php echo ($cur_filters['status'] ?? '') == 'fresh' ? 'selected' : ''; ?>>New Lead</option>
                        <option value="follow" <?php echo ($cur_filters['status'] ?? '') == 'follow' ? 'selected' : ''; ?>>Follow-up</option>
                        <option value="lost" <?php echo ($cur_filters['status'] ?? '') == 'lost' ? 'selected' : ''; ?>>Lost</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="filter-actions">
                    <button type="submit" class="btn-filter-apply">
                        <i class="feather icon-check"></i> Apply
                    </button>
                    <a href="<?php echo site_url('inventory/dashboard'); ?>" class="btn-filter-reset" title="Reset Filters">
                        <i class="feather icon-rotate-ccw"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Section 1: KPI Summary Row (7 cards) -->
    <div class="kpi-grid-7">
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
                <div class="kpi-sub"><i class="feather icon-clock"></i> In Period</div>
            </div>
        </a>

        <!-- 3. Today & Upcoming Follow-ups -->
        <a href="<?php echo site_url('inventory/leads/today'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #10b981;">
                <div class="kpi-top">
                    <span class="kpi-label">Today & Upcoming</span>
                    <div class="kpi-icon" style="background-color: #dcfce7; color: #16a34a;"><i class="feather icon-calendar"></i></div>
                </div>
                <div class="kpi-number" style="color: #16a34a;"><?php echo number_format($summary['active_followups'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-check-circle"></i> Active Follow-ups</div>
            </div>
        </a>

        <!-- 4. Converted Leads -->
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

        <!-- 5. Lost Leads -->
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

        <!-- 6. Total Calls -->
        <a href="<?php echo site_url('inventory/customer_calls'); ?>" class="stat-card-box-link">
            <div class="kpi-card" style="border-top: 3px solid #f59e0b;">
                <div class="kpi-top">
                    <span class="kpi-label">Calls Attended</span>
                    <div class="kpi-icon" style="background-color: #fef3c7; color: #d97706;"><i class="feather icon-phone-call"></i></div>
                </div>
                <div class="kpi-number" style="color: #d97706;"><?php echo number_format($summary['calls'] ?? 0); ?></div>
                <div class="kpi-sub"><i class="feather icon-phone"></i> In Period</div>
            </div>
        </a>

        <!-- 7. Missed Leads -->
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

    <!-- Section 2: Lead Pipeline Funnel -->
    <div class="dashboard-section-card">
        <div class="section-header">
            <div class="section-icon-badge" style="background-color: #e0f2fe; color: #0284c7;">
                <i class="feather icon-git-commit"></i>
            </div>
            <h2 class="section-title">Lead Pipeline Breakdown</h2>
        </div>
        <div class="pipeline-grid">
            <!-- Stage 1: Fresh / New -->
            <div class="pipeline-step-box" style="border-left: 4px solid #3b82f6;">
                <div class="pipeline-step-header">
                    <span class="pipeline-step-name">New Leads (Fresh)</span>
                    <i class="feather icon-sparkles" style="color: #3b82f6;"></i>
                </div>
                <div class="pipeline-step-count"><?php echo number_format($pipeline['fresh'] ?? 0); ?></div>
                <div class="pipeline-progress-bar">
                    <div class="pipeline-progress-fill" style="width: <?php echo $fresh_pct; ?>%; background-color: #3b82f6;"></div>
                </div>
                <div class="pipeline-step-pct"><?php echo $fresh_pct; ?>% of total</div>
            </div>

            <!-- Stage 2: In Follow-up -->
            <div class="pipeline-step-box" style="border-left: 4px solid #f59e0b;">
                <div class="pipeline-step-header">
                    <span class="pipeline-step-name">Needs / In Follow-up</span>
                    <i class="feather icon-phone-forwarded" style="color: #f59e0b;"></i>
                </div>
                <div class="pipeline-step-count"><?php echo number_format($pipeline['followup'] ?? 0); ?></div>
                <div class="pipeline-progress-bar">
                    <div class="pipeline-progress-fill" style="width: <?php echo $follow_pct; ?>%; background-color: #f59e0b;"></div>
                </div>
                <div class="pipeline-step-pct"><?php echo $follow_pct; ?>% of total</div>
            </div>

            <!-- Stage 3: Converted -->
            <div class="pipeline-step-box" style="border-left: 4px solid #10b981;">
                <div class="pipeline-step-header">
                    <span class="pipeline-step-name">Converted to Customer</span>
                    <i class="feather icon-check-circle" style="color: #10b981;"></i>
                </div>
                <div class="pipeline-step-count"><?php echo number_format($pipeline['converted'] ?? 0); ?></div>
                <div class="pipeline-progress-bar">
                    <div class="pipeline-progress-fill" style="width: <?php echo $converted_pct; ?>%; background-color: #10b981;"></div>
                </div>
                <div class="pipeline-step-pct"><?php echo $converted_pct; ?>% conversion</div>
            </div>

            <!-- Stage 4: Lost -->
            <div class="pipeline-step-box" style="border-left: 4px solid #ef4444;">
                <div class="pipeline-step-header">
                    <span class="pipeline-step-name">Lost Leads</span>
                    <i class="feather icon-x-circle" style="color: #ef4444;"></i>
                </div>
                <div class="pipeline-step-count"><?php echo number_format($pipeline['lost'] ?? 0); ?></div>
                <div class="pipeline-progress-bar">
                    <div class="pipeline-progress-fill" style="width: <?php echo $lost_pct; ?>%; background-color: #ef4444;"></div>
                </div>
                <div class="pipeline-step-pct"><?php echo $lost_pct; ?>% lost rate</div>
            </div>
        </div>
    </div>

    <!-- Section 3: Trends Charts (ApexCharts) -->
    <div class="charts-row-2">
        <!-- Lead Creation Trend -->
        <div class="dashboard-section-card" style="margin-bottom: 0;">
            <div class="section-header">
                <div class="section-icon-badge" style="background-color: #ede9fe; color: #7c3aed;">
                    <i class="feather icon-trending-up"></i>
                </div>
                <h2 class="section-title">Lead Creation Trend</h2>
            </div>
            <div id="leadTrendChart" style="min-height: 280px;"></div>
        </div>

        <!-- Call Activity Trend -->
        <div class="dashboard-section-card" style="margin-bottom: 0;">
            <div class="section-header">
                <div class="section-icon-badge" style="background-color: #ffedd5; color: #ea580c;">
                    <i class="feather icon-phone-call"></i>
                </div>
                <h2 class="section-title">Call Activity Trend</h2>
            </div>
            <div id="callTrendChart" style="min-height: 280px;"></div>
        </div>
    </div>

    <!-- Section 4: Follow-up Scheduling & Needs Attention -->
    <div class="attention-row">
        <!-- Follow-up Overview -->
        <div class="dashboard-section-card" style="margin-bottom: 0;">
            <div class="section-header">
                <div class="section-icon-badge" style="background-color: #dcfce7; color: #16a34a;">
                    <i class="feather icon-check-square"></i>
                </div>
                <h2 class="section-title">Follow-up Performance</h2>
            </div>
            <div class="perf-counter-card">
                <div class="perf-counter-left">
                    <div class="stat-icon-circle" style="background: #dcfce7; color: #16a34a;"><i class="feather icon-sun"></i></div>
                    <div>
                        <p class="perf-counter-title">Due Today</p>
                        <p class="perf-counter-desc">Scheduled for action today</p>
                    </div>
                </div>
                <span class="perf-counter-badge" style="background: #dcfce7; color: #15803d;"><?php echo number_format($followup_perf['due_today'] ?? 0); ?></span>
            </div>

            <div class="perf-counter-card">
                <div class="perf-counter-left">
                    <div class="stat-icon-circle" style="background: #e0f2fe; color: #0284c7;"><i class="feather icon-clock"></i></div>
                    <div>
                        <p class="perf-counter-title">Upcoming</p>
                        <p class="perf-counter-desc">Scheduled for future dates</p>
                    </div>
                </div>
                <span class="perf-counter-badge" style="background: #e0f2fe; color: #0369a1;"><?php echo number_format($followup_perf['upcoming'] ?? 0); ?></span>
            </div>

            <div class="perf-counter-card">
                <div class="perf-counter-left">
                    <div class="stat-icon-circle" style="background: #fee2e2; color: #ef4444;"><i class="feather icon-alert-triangle"></i></div>
                    <div>
                        <p class="perf-counter-title">Overdue Follow-ups</p>
                        <p class="perf-counter-desc">Missed scheduled date</p>
                    </div>
                </div>
                <span class="perf-counter-badge" style="background: #fee2e2; color: #b91c1c;"><?php echo number_format($followup_perf['overdue'] ?? 0); ?></span>
            </div>
        </div>

        <!-- Needs Attention Widget -->
        <div class="dashboard-section-card" style="margin-bottom: 0;">
            <div class="section-header">
                <div class="section-icon-badge" style="background-color: #fee2e2; color: #ef4444;">
                    <i class="feather icon-alert-circle"></i>
                </div>
                <h2 class="section-title">Needs Attention</h2>
            </div>
            
            <div class="perf-counter-card" style="border-left: 4px solid #ef4444;">
                <div class="perf-counter-left">
                    <div>
                        <p class="perf-counter-title" style="color: #b91c1c;">Overdue Follow-ups</p>
                        <p class="perf-counter-desc">Leads needing immediate contact</p>
                    </div>
                </div>
                <span class="perf-counter-badge" style="background: #fee2e2; color: #b91c1c;"><?php echo number_format($needs_attention['overdue_followups'] ?? 0); ?></span>
            </div>

            <div class="perf-counter-card" style="border-left: 4px solid #f59e0b;">
                <div class="perf-counter-left">
                    <div>
                        <p class="perf-counter-title" style="color: #b45309;">Old Active Leads (>30 Days)</p>
                        <p class="perf-counter-desc">Active leads sitting over 30 days</p>
                    </div>
                </div>
                <span class="perf-counter-badge" style="background: #fef3c7; color: #b45309;"><?php echo number_format($needs_attention['old_active_leads'] ?? 0); ?></span>
            </div>

            <div class="perf-counter-card" style="border-left: 4px solid #6366f1;">
                <div class="perf-counter-left">
                    <div>
                        <p class="perf-counter-title" style="color: #4338ca;">Fresh Without Follow-up Date</p>
                        <p class="perf-counter-desc">New leads missing scheduled action</p>
                    </div>
                </div>
                <span class="perf-counter-badge" style="background: #ede9fe; color: #4338ca;"><?php echo number_format($needs_attention['fresh_without_followup'] ?? 0); ?></span>
            </div>
        </div>
    </div>

    <!-- Section 5: Overdue & Upcoming Follow-ups Preview Tables -->
    <div class="charts-row-2" style="margin-top: 24px;">
        <!-- Overdue Follow-ups Table -->
        <div class="dashboard-section-card" style="margin-bottom: 0;">
            <div class="section-header" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="section-icon-badge" style="background-color: #fee2e2; color: #ef4444;">
                        <i class="feather icon-alert-triangle"></i>
                    </div>
                    <h2 class="section-title">Missed / Overdue Follow-ups</h2>
                </div>
                <span class="badge badge-danger"><?php echo count($overdue_flw); ?> items</span>
            </div>
            <div class="dashboard-table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Customer / Lead</th>
                            <th>Staff</th>
                            <th>Due Date</th>
                            <th>Overdue By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($overdue_flw)): ?>
                            <tr><td colspan="4" class="text-center text-muted" style="padding: 24px;">No overdue follow-ups! Great job 🎉</td></tr>
                        <?php else: ?>
                            <?php foreach ($overdue_flw as $of): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($of['company_name'] ?? 'N/A'); ?></strong>
                                        <br><span class="badge-status badge-status-lead" style="font-size: 10px;"><?php echo htmlspecialchars($of['status_display'] ?? 'Follow-up'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($of['added_by_name'] ?? 'Unassigned'); ?></td>
                                    <td><?php echo !empty($of['status_date']) ? date('d M Y', strtotime($of['status_date'])) : '-'; ?></td>
                                    <td>
                                        <span class="badge-status badge-status-overdue">
                                            <?php echo (int)($of['days_overdue'] ?? 0); ?> days overdue
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Follow-ups Table -->
        <div class="dashboard-section-card" style="margin-bottom: 0;">
            <div class="section-header" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="section-icon-badge" style="background-color: #e0f2fe; color: #0284c7;">
                        <i class="feather icon-calendar"></i>
                    </div>
                    <h2 class="section-title">Upcoming Follow-ups</h2>
                </div>
                <span class="badge badge-primary"><?php echo count($upcoming_flw); ?> items</span>
            </div>
            <div class="dashboard-table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Customer / Lead</th>
                            <th>Staff</th>
                            <th>Follow-up Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($upcoming_flw)): ?>
                            <tr><td colspan="4" class="text-center text-muted" style="padding: 24px;">No upcoming follow-ups scheduled</td></tr>
                        <?php else: ?>
                            <?php foreach ($upcoming_flw as $uf): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($uf['company_name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($uf['added_by_name'] ?? 'Unassigned'); ?></td>
                                    <td><?php echo !empty($uf['status_date']) ? date('d M Y, h:i A', strtotime($uf['status_date'])) : '-'; ?></td>
                                    <td><span class="badge-status badge-status-followup"><?php echo htmlspecialchars($uf['status_display'] ?? 'Follow-up'); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 6 & 7: Staff Performance & Recent Activity (7 - 5 Grid) -->
    <div class="row" style="margin-top: 24px;">
        <!-- Left: Staff Performance (col-lg-7) -->
        <div class="col-lg-7 col-md-12">
            <div class="dashboard-section-card" style="height: 100%; margin-bottom: 24px;">
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
                                                <div class="pipeline-progress-bar" style="width: 60px; height: 6px; margin: 0;">
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
        </div>

        <!-- Right: Recent CRM Activity (col-lg-5) -->
        <div class="col-lg-5 col-md-12">
            <div class="dashboard-section-card" style="height: 100%; margin-bottom: 24px;">
                <div class="section-header">
                    <div class="section-icon-badge" style="background-color: #f1f5f9; color: #475569;">
                        <i class="feather icon-list"></i>
                    </div>
                    <h2 class="section-title">Recent CRM Activity</h2>
                </div>
                <div class="activity-feed-list" style="max-height: 480px; overflow-y: auto; padding-right: 4px;">
                    <?php if (empty($recent_activity)): ?>
                        <p class="text-center text-muted" style="padding: 20px;">No recent activity logs recorded</p>
                    <?php else: ?>
                        <?php foreach ($recent_activity as $act): 
                            $badge_class = !empty($act['badge_type']) ? $act['badge_type'] : 'primary';
                            $icon_bg = ($badge_class == 'danger') ? '#fee2e2' : (($badge_class == 'warning') ? '#fef3c7' : '#ede9fe');
                            $icon_color = ($badge_class == 'danger') ? '#ef4444' : (($badge_class == 'warning') ? '#d97706' : '#7c3aed');
                            $icon_name = ($badge_class == 'danger') ? 'icon-alert-circle' : (($badge_class == 'warning') ? 'icon-phone-call' : 'icon-bell');
                        ?>
                            <div class="activity-feed-item">
                                <div class="activity-feed-icon" style="background-color: <?php echo $icon_bg; ?>; color: <?php echo $icon_color; ?>;">
                                    <i class="feather <?php echo $icon_name; ?>"></i>
                                </div>
                                <div class="activity-feed-content">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
                                        <?php if (!empty($act['badge_label'])): ?>
                                            <span class="badge badge-light-<?php echo $badge_class; ?>" style="font-size: 11px; padding: 3px 8px;">
                                                <?php echo htmlspecialchars($act['badge_label']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <p class="activity-feed-text" style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($act['display_message'] ?? $act['display_text'] ?? ''); ?></p>
                                    </div>
                                    <div class="activity-feed-meta">
                                        <?php if (!empty($act['company_name'])): ?>
                                            <span><i class="feather icon-briefcase"></i> <?php echo htmlspecialchars($act['company_name']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($act['added_by_name'])): ?>
                                            <span><i class="feather icon-user"></i> <?php echo htmlspecialchars($act['added_by_name']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($act['added_date'])): ?>
                                            <span><i class="feather icon-clock"></i> <?php echo date('d M Y, h:i A', strtotime($act['added_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCustomDate(val) {
    var wrap = document.getElementById('customDateWrap');
    if (wrap) {
        wrap.style.display = (val === 'custom') ? 'flex' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Lead Trend Chart Data
    var leadDates = <?php echo json_encode(array_column($lead_trends, 'date_val')); ?>;
    var leadCounts = <?php echo json_encode(array_map('intval', array_column($lead_trends, 'total'))); ?>;

    if (leadDates.length === 0) {
        leadDates = ['No Data'];
        leadCounts = [0];
    }

    var leadOptions = {
        chart: {
            type: 'area',
            height: 280,
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#6366f1'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        series: [{
            name: 'New Leads',
            data: leadCounts
        }],
        xaxis: {
            categories: leadDates,
            labels: { style: { colors: '#64748b', fontSize: '11px' } }
        },
        yaxis: {
            labels: { style: { colors: '#64748b', fontSize: '11px' } }
        },
        grid: { borderColor: '#f1f5f9' },
        tooltip: {
            theme: 'light'
        }
    };

    var leadChartEl = document.querySelector("#leadTrendChart");
    if (leadChartEl && typeof ApexCharts !== 'undefined') {
        var leadChart = new ApexCharts(leadChartEl, leadOptions);
        leadChart.render();
    }

    // 2. Call Activity Trend Chart Data
    var callDates = <?php echo json_encode(array_column($call_trends, 'date_val')); ?>;
    var callCounts = <?php echo json_encode(array_map('intval', array_column($call_trends, 'total'))); ?>;

    if (callDates.length === 0) {
        callDates = ['No Data'];
        callCounts = [0];
    }

    var callOptions = {
        chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '45%'
            }
        },
        dataLabels: { enabled: false },
        colors: ['#ea580c'],
        series: [{
            name: 'Calls Created',
            data: callCounts
        }],
        xaxis: {
            categories: callDates,
            labels: { style: { colors: '#64748b', fontSize: '11px' } }
        },
        yaxis: {
            labels: { style: { colors: '#64748b', fontSize: '11px' } }
        },
        grid: { borderColor: '#f1f5f9' },
        tooltip: {
            theme: 'light'
        }
    };

    var callChartEl = document.querySelector("#callTrendChart");
    if (callChartEl && typeof ApexCharts !== 'undefined') {
        var callChart = new ApexCharts(callChartEl, callOptions);
        callChart.render();
    }
});
</script>