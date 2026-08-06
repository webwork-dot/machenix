<style>
    .dashboard-wrapper {
        background-color: #f8fafc;
        padding: 10px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    
    /* Section Container Card */
    .dashboard-section-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 20px 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);
        border: 1px solid #edf2f7;
        margin-bottom: 24px;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .section-icon-badge {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background-color: #ede9fe;
        color: #7c3aed;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    
    .section-title {
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    
    /* 5-Column Grid Layout */
    .stats-grid-5 {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
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
        border-radius: 12px;
        padding: 16px 18px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 130px;
    }
    
    .stat-card-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }
    
    .stat-card-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin: 0;
    }
    
    .stat-icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    
    .stat-card-number {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.1;
        margin: 8px 0 12px 0;
    }
    
    .stat-pill-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
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
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_0_30']) ? $dashboard_stats['orders_0_30'] : 0; ?></div>
                    <div><span class="stat-pill-badge">0 to 30 Days</span></div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/31_60'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-blue">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_31_60']) ? $dashboard_stats['orders_31_60'] : 0; ?></div>
                    <div><span class="stat-pill-badge">31 to 60 Days</span></div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/61_90'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-green">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_61_90']) ? $dashboard_stats['orders_61_90'] : 0; ?></div>
                    <div><span class="stat-pill-badge">61 to 90 Days</span></div>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/90_plus'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-orange">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['orders_90_plus']) ? $dashboard_stats['orders_90_plus'] : 0; ?></div>
                    <div><span class="stat-pill-badge">90+ Days</span></div>
                </div>
            </a>

            <!-- Card 5 -->
            <a href="<?php echo site_url('inventory/customer_report/orders/no_orders'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-red">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['no_orders']) ? $dashboard_stats['no_orders'] : 0; ?></div>
                    <div><span class="stat-pill-badge">No orders</span></div>
                </div>
            </a>
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
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_0_30']) ? $dashboard_stats['calls_0_30'] : 0; ?></div>
                    <div><span class="stat-pill-badge">0 to 30 Days</span></div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/31_60'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-blue">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_31_60']) ? $dashboard_stats['calls_31_60'] : 0; ?></div>
                    <div><span class="stat-pill-badge">31 to 60 Days</span></div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/61_90'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-green">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_61_90']) ? $dashboard_stats['calls_61_90'] : 0; ?></div>
                    <div><span class="stat-pill-badge">61 to 90 Days</span></div>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/90_plus'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-orange">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['calls_90_plus']) ? $dashboard_stats['calls_90_plus'] : 0; ?></div>
                    <div><span class="stat-pill-badge">90+ Days</span></div>
                </div>
            </a>

            <!-- Card 5 -->
            <a href="<?php echo site_url('inventory/customer_report/calls/no_calls'); ?>" class="stat-card-box-link">
                <div class="stat-card-box theme-red">
                    <div class="stat-card-top">
                        <span class="stat-card-label">Total Customers</span>
                        <div class="stat-icon-circle"><i class="feather icon-user"></i></div>
                    </div>
                    <div class="stat-card-number"><?php echo isset($dashboard_stats['no_calls']) ? $dashboard_stats['no_calls'] : 0; ?></div>
                    <div><span class="stat-pill-badge">No calls</span></div>
                </div>
            </a>
        </div>
    </div>
</div>