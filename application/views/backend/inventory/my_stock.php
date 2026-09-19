<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>

<style>
	/* Global compact typography & baseline */
	.inventory-page-wrapper {
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	}

	/* Warehouse Tabs Navigation */
	.warehouse-nav-container {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 5px 8px;
		box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
	}
	.warehouse-nav-container .nav-pills {
		gap: 6px;
	}
	.warehouse-nav-container .nav-link {
		color: #475569;
		font-weight: 500;
		font-size: 12.5px;
		padding: 6px 14px;
		border-radius: 6px;
		transition: all 0.15s ease-in-out;
		border: 1px solid transparent;
		display: inline-flex;
		align-items: center;
		background: transparent;
	}
	.warehouse-nav-container .nav-link:hover {
		color: #0f172a;
		background: #f1f5f9;
	}
	.warehouse-nav-container .nav-link.active {
		color: #15803d !important;
		background: #f0fdf4 !important;
		border-color: #bbf7d0 !important;
		font-weight: 600;
		box-shadow: none !important;
	}

	/* Top Metric KPI Cards - Ultra Compact & Aesthetic */
	.kpi-card {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 8px 12px;
		display: flex;
		align-items: center;
		gap: 12px;
		box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}
	.kpi-card:hover {
		border-color: #cbd5e1;
		box-shadow: 0 3px 6px -1px rgba(0, 0, 0, 0.05);
	}
	.kpi-icon {
		width: 36px;
		height: 36px;
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 17px;
		flex-shrink: 0;
	}
	.kpi-icon-blue { background: #eff6ff; color: #2563eb; }
	.kpi-icon-emerald { background: #ecfdf5; color: #059669; }
	.kpi-icon-indigo { background: #eef2ff; color: #4f46e5; }
	.kpi-icon-slate { background: #f1f5f9; color: #475569; }
	.kpi-content { overflow: hidden; }
	.kpi-label {
		font-size: 10.5px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.4px;
		color: #64748b;
		margin-bottom: 1px;
	}
	.kpi-value {
		font-size: 17px;
		font-weight: 700;
		color: #0f172a;
		line-height: 1.2;
	}

	/* Main Table Card & DataTable Layout */
	.stock-table-card {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
		overflow: hidden;
	}
	/* Column & Data Filter Toolbar */
	.stock-filter-toolbar {
		background: #ffffff;
		padding: 8px 14px;
		border-bottom: 1px solid #f1f5f9;
		display: flex;
		align-items: center;
	}
	.filter-section-title {
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.4px;
		color: #64748b;
		margin-right: 4px;
		display: inline-flex;
		align-items: center;
	}
	.filter-chip {
		cursor: pointer;
		margin-bottom: 0;
		user-select: none;
		display: inline-flex;
		align-items: center;
		padding: 3px 9px;
		border-radius: 5px;
		font-size: 11.5px;
		font-weight: 500;
		color: #64748b;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		transition: all 0.15s ease-in-out;
		gap: 6px;
	}
	.filter-chip:hover {
		border-color: #cbd5e1;
		color: #1e293b;
		background: #f1f5f9;
	}
	.filter-chip.active {
		background: #f0fdf4;
		color: #15803d;
		border-color: #bbf7d0;
		font-weight: 600;
	}
	.filter-chip input[type="checkbox"] {
		display: none;
	}
	.filter-chip .chip-box {
		width: 13px;
		height: 13px;
		border-radius: 3px;
		border: 1px solid #cbd5e1;
		background: #ffffff;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 9px;
		color: transparent;
		transition: all 0.15s ease;
		flex-shrink: 0;
	}
	.filter-chip.active .chip-box {
		background: #16a34a;
		border-color: #16a34a;
		color: #ffffff;
	}
	.btn-reset-filters {
		background: transparent;
		border: 1px solid #e2e8f0;
		border-radius: 5px;
		color: #64748b;
		font-size: 11px;
		font-weight: 500;
		padding: 3px 8px;
		cursor: pointer;
		transition: all 0.15s ease;
		display: inline-flex;
		align-items: center;
	}
	.btn-reset-filters:hover {
		background: #f1f5f9;
		color: #0f172a;
		border-color: #cbd5e1;
	}
	.dt-toolbar {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		padding: 10px 14px;
		border-bottom: 1px solid #f1f5f9;
		gap: 10px;
	}
	.dataTables_length select {
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px !important;
		padding: 4px 22px 4px 8px !important;
		font-size: 12px !important;
		color: #334155 !important;
		background-color: #ffffff !important;
		outline: none !important;
	}
	.dataTables_filter input {
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px !important;
		padding: 5px 12px !important;
		font-size: 12px !important;
		color: #0f172a !important;
		background-color: #ffffff !important;
		outline: none !important;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
		min-width: 220px;
		margin-left: 6px !important;
	}
	.dataTables_filter input:focus {
		border-color: #6366f1 !important;
		box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
	}
	.btn-export-custom {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		padding: 4px 10px;
		font-size: 11.5px;
		font-weight: 600;
		border-radius: 5px;
		border: 1px solid #e2e8f0;
		background: #ffffff;
		color: #334155;
		transition: all 0.15s ease;
	}
	.btn-export-custom:hover {
		background: #f8fafc;
		border-color: #cbd5e1;
		color: #0f172a;
	}
	.btn-export-custom.btn-excel:hover {
		color: #15803d;
		border-color: #bbf7d0;
		background: #f0fdf4;
	}
	.btn-export-custom.btn-pdf:hover {
		color: #dc2626;
		border-color: #fecaca;
		background: #fef2f2;
	}
	.dt-buttons {
		display: none !important;
	}

	/* Fixed height scroll body — never show parent horizontal scroll */
	.dataTables_scroll {
		overflow: hidden;
		width: 100% !important;
	}
	.dataTables_scrollHead {
		overflow-x: hidden !important;
		overflow-y: hidden !important;
		width: 100% !important;
	}
	.dataTables_scrollBody {
		max-height: 475px !important;
		height: 475px !important;
		overflow-x: hidden !important;
		overflow-y: auto !important;
		width: 100% !important;
	}
	/* Kill DT scrollbar-gutter padding that leaves a gap before the scrollbar */
	#report-datatable_wrapper .dataTables_scrollHeadInner {
		padding-right: 0 !important;
		box-sizing: border-box !important;
		width: 100% !important;
		min-width: 100% !important;
		max-width: 100% !important;
	}
	#report-datatable_wrapper .dataTables_scrollHeadInner > table,
	#report-datatable_wrapper .dataTables_scrollBody > table {
		width: 100% !important;
		min-width: 100% !important;
		max-width: 100% !important;
		table-layout: fixed !important;
		box-sizing: border-box !important;
		margin: 0 !important;
	}
	#report-datatable_wrapper .dataTables_scrollHead table th,
	#report-datatable_wrapper .dataTables_scrollBody > table > tbody > tr:not(.child) > td {
		overflow: hidden;
		text-overflow: ellipsis;
	}
	#report-datatable_wrapper .dataTables_scrollHead table,
	#report-datatable_wrapper .dataTables_scrollBody table {
		margin-bottom: 0 !important;
	}
	table.dataTable tbody tr.child,
	table.dataTable tbody tr.child:hover {
		background: transparent !important;
	}
	/* Keep child td as a real table-cell so parent row layout stays intact */
	table.dataTable tbody tr.child > td {
		padding: 0 !important;
		border-top: none !important;
		background: transparent !important;
	}
	/*
	 * width:0 + min-width:100% makes the wrap take the parent table width
	 * without letting wide batch columns expand the DataTables body table.
	 */
	.child-scroll-wrap {
		display: block;
		width: 0;
		min-width: 100%;
		max-width: 100%;
		box-sizing: border-box;
		overflow-x: auto !important;
		overflow-y: hidden !important;
		-webkit-overflow-scrolling: touch;
		padding: 4px 2px;
	}
	.child-scroll-wrap .batch-breakdown-card,
	.child-scroll-wrap .company-breakdown-card {
		display: block;
		width: max-content;
		min-width: 100%;
		max-width: none !important;
		margin: 0;
		box-sizing: border-box;
		overflow: visible !important;
	}
	.child-scroll-wrap .table-responsive,
	.child-scroll-wrap .batch-breakdown-card > .table-responsive,
	.child-scroll-wrap .company-breakdown-card > .table-responsive {
		display: block !important;
		width: max-content !important;
		min-width: 100% !important;
		max-width: none !important;
		overflow: visible !important;
	}
	.child-scroll-wrap .sub-batch-table,
	.child-scroll-wrap .sub-company-table {
		width: max-content !important;
		min-width: 100%;
		margin-bottom: 0;
	}

	/* Main Table Head & Body */
	#report-datatable {
		width: 100% !important;
		border-collapse: separate !important;
		border-spacing: 0;
		margin-bottom: 0 !important;
	}
	#report-datatable thead th {
		background: #f8fafc !important;
		color: #475569 !important;
		font-size: 11px !important;
		font-weight: 700 !important;
		text-transform: uppercase !important;
		letter-spacing: 0.4px !important;
		padding: 0px 10px !important;
		border-bottom: 1px solid #cbd5e1 !important;
		border-top: none !important;
		white-space: nowrap !important;
		vertical-align: middle !important;
	}
	#report-datatable thead th.th-cost-wrap,
	#report-datatable_wrapper .dataTables_scrollHead table th.th-cost-wrap {
		white-space: normal !important;
		overflow: visible !important;
		text-overflow: clip !important;
		line-height: 1.25;
	}
	#report-datatable tbody td {
		padding: 6px 10px !important;
		font-size: 12.5px !important;
		color: #334155;
		border-bottom: 1px solid #f1f5f9 !important;
		border-top: none !important;
		vertical-align: middle !important;
	}
	#report-datatable tbody tr:hover {
		background-color: #f8fafc !important;
	}
	tr.shown {
		background-color: #f8faff !important;
	}
	tr.shown td {
		border-bottom: none !important;
	}

	/* Expand Button */
	.btn-expand-row {
		width: 20px;
		height: 20px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 4px;
		border: 1px solid #cbd5e1;
		background: #ffffff;
		color: #64748b;
		font-size: 10px;
		cursor: pointer;
		transition: all 0.15s ease-in-out;
	}
	.btn-expand-row:hover {
		background: #f1f5f9;
		color: #0f172a;
		border-color: #94a3b8;
	}
	tr.shown .btn-expand-row {
		background: #fee2e2;
		color: #dc2626;
		border-color: #fca5a5;
	}
	.btn-expand-row.disabled {
		opacity: 0.35;
		cursor: not-allowed;
		pointer-events: none;
	}
	.stk-sr-num {
		font-size: 11.5px;
		font-weight: 600;
		color: #475569;
		min-width: 18px;
		display: inline-block;
		text-align: center;
	}

	/* Unified Stock Badges */
	.stk-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 26px;
		padding: 2px 6px;
		font-size: 11.5px;
		font-weight: 600;
		line-height: 1.3;
		border-radius: 4px;
		text-decoration: none !important;
		font-variant-numeric: tabular-nums;
	}
	.stk-badge-zero {
		color: #94a3b8;
		font-weight: 400;
		font-size: 11.5px;
	}
	.stk-qty-main {
		font-weight: 700;
		color: #0f172a;
		font-size: 12.5px;
		font-variant-numeric: tabular-nums;
	}
	.stk-badge-black {
		background-color: #f1f5f9;
		color: #334155;
		border: 1px solid #e2e8f0;
	}
	.stk-badge-white {
		background-color: #eff6ff;
		color: #1d4ed8;
		border: 1px solid #dbeafe;
	}
	.stk-badge-pending {
		background-color: #fffbeb;
		color: #b45309;
		border: 1px solid #fef3c7;
	}
	.stk-badge-total-white {
		background-color: #e0e7ff;
		color: #4338ca;
		border: 1px solid #c7d2fe;
		font-weight: 700;
	}
	.stk-badge-booked {
		background-color: #fff1f2;
		color: #e11d48;
		border: 1px solid #ffe4e6;
		transition: all 0.15s ease-in-out;
		cursor: pointer;
	}
	.stk-badge-booked:hover {
		background-color: #ffe4e6;
		color: #be123c;
		transform: translateY(-1px);
	}
	.stk-badge-po {
		background-color: #f0fdf4;
		color: #15803d;
		border: 1px solid #dcfce7;
		transition: all 0.15s ease-in-out;
		cursor: pointer;
	}
	.stk-badge-po:hover {
		background-color: #dcfce7;
		color: #166534;
		transform: translateY(-1px);
	}
	.stk-badge-priority {
		background-color: #fff7ed;
		color: #c2410c;
		border: 1px solid #ffedd5;
		transition: all 0.15s ease-in-out;
		cursor: pointer;
	}
	.stk-badge-priority:hover {
		background-color: #ffedd5;
		color: #9a3412;
		transform: translateY(-1px);
	}
	.stk-badge-loading {
		background-color: #f0fdfa;
		color: #0f766e;
		border: 1px solid #ccfbf1;
		transition: all 0.15s ease-in-out;
		cursor: pointer;
	}
	.stk-badge-loading:hover {
		background-color: #ccfbf1;
		color: #115e59;
		transform: translateY(-1px);
	}

	/* Cost Amounts */
	.stk-cost {
		font-size: 12px;
		font-variant-numeric: tabular-nums;
		white-space: nowrap;
	}
	.stk-cost.stk-zero {
		color: #94a3b8;
		font-weight: 400;
	}
	.stk-cost-accent {
		font-weight: 600;
		color: #0f172a;
	}

	/* Table Action Buttons */
	.btn-table-action {
		width: 26px;
		height: 26px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 5px;
		font-size: 12px;
		border: 1px solid #e2e8f0;
		background: #ffffff;
		color: #475569;
		transition: all 0.15s ease;
		text-decoration: none !important;
		margin: 0 2px;
	}
	.btn-table-action:hover {
		background: #f8fafc;
		color: #0f172a;
		border-color: #cbd5e1;
		transform: translateY(-1px);
	}
	.btn-table-action.btn-action-view:hover {
		color: #4f46e5;
		border-color: #c7d2fe;
		background: #eef2ff;
	}
	.btn-table-action.btn-action-barcode:hover {
		color: #16a34a;
		border-color: #86efac;
		background: #f0fdf4;
	}

	/* Child Row Batch Breakdown Card */
	.batch-breakdown-card {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 8px 12px;
		margin: 0;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
		box-sizing: border-box;
	}
	.batch-breakdown-card > .table-responsive {
		overflow: visible;
		max-width: none;
	}
	.batch-card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 6px;
		padding-bottom: 6px;
		border-bottom: 1px solid #e2e8f0;
	}
	.batch-tag-icon {
		width: 20px;
		height: 20px;
		background: #eef2ff;
		color: #4f46e5;
		border-radius: 4px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 11px;
	}
	.batch-title {
		font-size: 12px;
		font-weight: 700;
		color: #1e293b;
	}
	.batch-prod-name {
		font-size: 12px;
		font-weight: 500;
		color: #64748b;
	}
	.badge-batch-count {
		background: #ffffff;
		border: 1px solid #cbd5e1;
		color: #475569;
		font-size: 11px;
		font-weight: 600;
		padding: 1px 7px;
		border-radius: 12px;
	}
	.sub-batch-table {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		overflow: hidden;
		margin-bottom: 0;
		width: max-content !important;
		min-width: 100%;
	}
	.sub-batch-table thead th {
		background: #f1f5f9 !important;
		color: #475569 !important;
		font-size: 10.5px !important;
		font-weight: 600 !important;
		text-transform: uppercase !important;
		letter-spacing: 0.3px !important;
		padding: 6px 8px !important;
		border-bottom: 1px solid #cbd5e1 !important;
		border-top: none !important;
		white-space: nowrap !important;
	}
	.sub-batch-table tbody td {
		padding: 5px 8px !important;
		font-size: 11.5px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		vertical-align: middle !important;
		white-space: nowrap !important;
	}
	.sub-batch-table tbody tr:last-child td {
		border-bottom: none !important;
	}
	.sub-batch-table tbody tr:hover {
		background-color: #f8fafc !important;
	}
	.batch-no-tag {
		font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
		font-size: 11px;
		font-weight: 600;
		color: #0284c7;
		background: #f0f9ff;
		border: 1px solid #e0f2fe;
		padding: 1px 6px;
		border-radius: 3px;
		display: inline-block;
	}
	.btn-micro-action {
		width: 22px;
		height: 22px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 4px;
		font-size: 11px;
		border: 1px solid #e2e8f0;
		background: #ffffff;
		color: #475569;
		transition: all 0.15s ease;
		text-decoration: none !important;
	}
	.btn-micro-action:hover {
		background: #f8fafc;
		color: #0f172a;
		border-color: #cbd5e1;
	}
	.btn-micro-action.btn-barcode:hover {
		color: #16a34a;
		border-color: #86efac;
		background: #f0fdf4;
	}
</style>

<?php  
    if(empty($this->input->get('warehouse', true))){
       $warehouse_id = $warehouse_list[0]['id'] ?? 'All';
    }
    else{
       $warehouse_id = $this->input->get('warehouse', true);
    }
    if(empty($this->input->get('type', true))){
       $type = 'complete';
    }
    else{
       $type = $this->input->get('type', true);
    }
?>

<div class="row inventory-page-wrapper" id="table-bordered">
	<!-- Warehouse Filter Navigation -->
	<div class="col-md-12 mb-2">
		<div class="warehouse-nav-container">
			<ul class="nav nav-pills mb-0">
				<?php if(!empty($warehouse_list)) {
					foreach($warehouse_list as $warehouse){?>
					<li class="nav-item">
						<a href="<?php echo base_url();?>inventory/my-stock?warehouse=<?php echo $warehouse['id'];?>" class="nav-link <?php echo ($warehouse_id == $warehouse['id']) ? 'active' : ''; ?>">
							<i class="feather icon-home me-1"></i><?php echo htmlspecialchars($warehouse['name']);?>
						</a>
					</li>
					<?php }
				}?>	
			</ul>
		</div>
	</div>

	<!-- Top KPI Metric Cards -->
	<div class="col-12 mb-2">
		<div class="row g-1">
			<div class="col-xl-3 col-sm-6 col-12">
				<div class="kpi-card">
					<div class="kpi-icon kpi-icon-blue">
						<i class="feather icon-package"></i>
					</div>
					<div class="kpi-content">
						<div class="kpi-label">Total Products</div>
						<div class="kpi-value" id="total_count">0</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-sm-6 col-12">
				<div class="kpi-card">
					<div class="kpi-icon kpi-icon-emerald">
						<i class="feather icon-layers"></i>
					</div>
					<div class="kpi-content">
						<div class="kpi-label">Total Stock Quantity</div>
						<div class="kpi-value" id="total_qty"><?php echo number_format($total['qty'] ?? 0); ?></div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-sm-6 col-12">
				<div class="kpi-card">
					<div class="kpi-icon kpi-icon-indigo">
						<i class="feather icon-check-circle"></i>
					</div>
					<div class="kpi-content">
						<div class="kpi-label">Company White Qty</div>
						<div class="kpi-value" id="total_white_stat"><?php echo number_format($total['white_qty'] ?? 0); ?></div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-sm-6 col-12">
				<div class="kpi-card">
					<div class="kpi-icon kpi-icon-slate">
						<i class="feather icon-archive"></i>
					</div>
					<div class="kpi-content">
						<div class="kpi-label">Company Black Qty</div>
						<div class="kpi-value" id="total_black_stat"><?php echo number_format($total['black_qty'] ?? 0); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Main Data Table -->
    <div class="col-12">
		<div class="stock-table-card">
			<!-- Column Visibility & Data Filter Toolbar -->
			<div class="stock-filter-toolbar">
				<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
					<div class="d-flex align-items-center flex-wrap gap-1" id="column-filters-container">
						<span class="filter-section-title">
							<i class="feather icon-filter me-1"></i>Filters:
						</span>
						<label class="filter-chip" for="toggle-stock-batch" title="Expand or collapse all products with batches">
							<input type="checkbox" id="toggle-stock-batch">
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">Stock with Batch</span>
						</label>
						<label class="filter-chip" for="toggle-zero-qty" title="Show products and batches with zero quantity">
							<input type="checkbox" id="toggle-zero-qty">
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">Show Item/Batch with Zero Qty</span>
						</label>
						<label class="filter-chip active" for="toggle-booked-qty" title="Toggle Booked Quantity column">
							<input type="checkbox" id="toggle-booked-qty" class="column-filter-checkbox" checked>
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">Booked Qty</span>
						</label>
						<label class="filter-chip" for="toggle-po-qty" title="Toggle PO, Priority, Loading Quantity columns">
							<input type="checkbox" id="toggle-po-qty" class="column-filter-checkbox">
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">PO/Priority/Loading Qty</span>
						</label>
						<label class="filter-chip active" for="toggle-act-cost-exp" title="Toggle Actual Cost with Expense column">
							<input type="checkbox" id="toggle-act-cost-exp" class="column-filter-checkbox" checked>
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">Actual Cost Exp</span>
						</label>
						<label class="filter-chip active" for="toggle-act-cost-amt" title="Toggle Actual Cost Net Amount column">
							<input type="checkbox" id="toggle-act-cost-amt" class="column-filter-checkbox" checked>
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">Actual Cost Amt</span>
						</label>
						<label class="filter-chip active" for="toggle-off-cost-exp" title="Toggle Official Cost with Expense column">
							<input type="checkbox" id="toggle-off-cost-exp" class="column-filter-checkbox" checked>
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">Official Cost Exp</span>
						</label>
						<label class="filter-chip active" for="toggle-off-cost-amt" title="Toggle Official Cost Net Amount column">
							<input type="checkbox" id="toggle-off-cost-amt" class="column-filter-checkbox" checked>
							<span class="chip-box"><i class="feather icon-check"></i></span>
							<span class="chip-text">Official Cost Amt</span>
						</label>
					</div>
					<div class="d-flex align-items-center">
						<button type="button" class="btn-reset-filters" id="btn-reset-col-filters" title="Reset column filters to default">
							<i class="feather icon-rotate-ccw me-1"></i> Reset
						</button>
					</div>
				</div>
			</div>

			<div class="stock-table-scroll">
				<table class="table leads-table w-100" id="report-datatable">
					<thead>
						<tr>
							<th class="text-center" style="width: 50px;">#</th>
							<th>Product Name</th>
							<th class="text-end">Quantity</th>
							<th class="text-end">Black Qty</th>
							<th class="text-end">White Qty</th>
							<th class="text-end">Pending Qty</th>
							<th class="text-end">Total White Qty</th>
							<th class="text-end">Booked Qty</th>
							<th class="text-end">PO Qty</th>
							<th class="text-end">Priority Qty</th>
							<th class="text-end">Loading Qty</th>
							<th class="text-end th-cost-wrap">Actual Cost<br>with Exp</th>
							<th class="text-end th-cost-wrap">Actual Cost<br>Net Amt</th>
							<th class="text-end th-cost-wrap">Official Cost<br>with Exp</th>
							<th class="text-end th-cost-wrap">Official Cost<br>Net Amt</th>
							<th class="text-center" style="width: 75px;">Action</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
    </div>
</div>

<!-- PO List Modal -->
<div class="modal fade" id="poListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-2">
				<h5 class="modal-title fw-bolder text-dark" id="poListModalTitle">PO List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-muted font-small-2 mb-2" id="poListModalSubTitle">Details of Purchase Orders</p>
                <div id="poListContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booked Orders Modal -->
<div class="modal fade" id="bookedListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-2">
                <h5 class="modal-title fw-bolder text-danger" id="bookedListModalTitle"><i class="feather icon-shopping-cart me-1"></i>Booked Sales Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-muted font-small-2 mb-2">Unapproved Sales Orders currently booking stock for this product</p>
                <div id="bookedListContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">       
    function showProductPOList(productId, companyId, status, warehouseId = '') {
        $('#poListModal').modal('show');
        $('#poListContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');

        let statusTitle = status.charAt(0).toUpperCase() + status.slice(1);
        $('#poListModalTitle').text(statusTitle + ' Purchase Orders');

        $.ajax({
            url: "<?php echo base_url('inventory/get_product_po_list'); ?>",
            type: "POST",
            data: {
                product_id: productId,
                company_id: companyId,
                status: status,
                warehouse_id: warehouseId
            },
            success: function (response) {
                $('#poListContent').html(response);
            },
            error: function () {
                $('#poListContent').html('<div class="alert alert-danger font-small-2">Failed to load data. Please try again.</div>');
            }
        });
    }

    function showProductBookedList(productId, companyId) {
        $('#bookedListModal').modal('show');
        $('#bookedListContent').html('<div class="text-center py-4"><div class="spinner-border text-danger" role="status"></div></div>');

        $.ajax({
            url: "<?php echo base_url('inventory/get_product_booked_list'); ?>",
            type: "POST",
            data: {
                product_id: productId,
                company_id: companyId
            },
            success: function (response) {
                $('#bookedListContent').html(response);
            },
            error: function () {
                $('#bookedListContent').html('<div class="alert alert-danger font-small-2">Failed to load data. Please try again.</div>');
            }
        });
    }

    function formatChildRow(rowData) {
        var showZeroQty = $('#toggle-zero-qty').is(':checked');
        var allBatches = rowData.batches || [];
        var batches = showZeroQty ? allBatches : allBatches.filter(function (b) {
            return Number(b.quantity) > 0;
        });

        var html = '<div class="child-scroll-wrap">';
        html += '<div class="batch-breakdown-card">';
        html += '<div class="batch-card-header">';
        html += '  <div class="d-flex align-items-center gap-1">';
        html += '    <span class="batch-tag-icon"><i class="feather icon-layers"></i></span>';
        html += '    <span class="batch-title">Batch Breakdown</span>';
        html += '    <span class="batch-prod-name ms-1">— ' + (rowData.raw_product_name || '') + '</span>';
        html += '  </div>';
        html += '  <span class="badge-batch-count">' + batches.length + ' ' + (batches.length === 1 ? 'batch' : 'batches') + '</span>';
        html += '</div>';

        if (batches.length === 0) {
            html += '<div class="text-center py-2 text-muted font-small-2">' + (showZeroQty ? 'No individual batches recorded for this product.' : 'No active batches with stock for this product.') + '</div>';
        } else {
            var showBooked = $('#toggle-booked-qty').is(':checked');
            var showPo = $('#toggle-po-qty').is(':checked');
            var showActExp = $('#toggle-act-cost-exp').is(':checked');
            var showActAmt = $('#toggle-act-cost-amt').is(':checked');
            var showOffExp = $('#toggle-off-cost-exp').is(':checked');
            var showOffAmt = $('#toggle-off-cost-amt').is(':checked');

            var styleBooked = showBooked ? '' : 'style="display:none;"';
            var stylePo = showPo ? '' : 'style="display:none;"';
            var styleActExp = showActExp ? '' : 'style="display:none;"';
            var styleActAmt = showActAmt ? '' : 'style="display:none;"';
            var styleOffExp = showOffExp ? '' : 'style="display:none;"';
            var styleOffAmt = showOffAmt ? '' : 'style="display:none;"';

            html += '<div class="table-responsive">';
            html += '  <table class="table sub-batch-table align-middle">';
            html += '    <thead>';
            html += '      <tr>';
            html += '        <th class="text-center" style="width: 35px;">#</th>';
            html += '        <th>Batch No</th>';
            html += '        <th class="text-end">Quantity</th>';
            html += '        <th class="text-end">Black Qty</th>';
            html += '        <th class="text-end">White Qty</th>';
            html += '        <th class="text-end">Pending Qty</th>';
            html += '        <th class="text-end">Total White Qty</th>';
            html += '        <th class="text-end col-batch-booked" ' + styleBooked + '>Booked Qty</th>';
            html += '        <th class="text-end col-batch-po" ' + stylePo + '>PO Qty</th>';
            html += '        <th class="text-end col-batch-po" ' + stylePo + '>Priority Qty</th>';
            html += '        <th class="text-end col-batch-po" ' + stylePo + '>Loading Qty</th>';
            html += '        <th class="text-end col-batch-act-exp" ' + styleActExp + '>Actual Cost Per Pc with Exp</th>';
            html += '        <th class="text-end col-batch-act-exp" ' + styleActExp + '>Actual Cost with Exp</th>';
            html += '        <th class="text-end col-batch-act-amt" ' + styleActAmt + '>Actual Cost Per Pc Net Amt</th>';
            html += '        <th class="text-end col-batch-act-amt" ' + styleActAmt + '>Actual Cost Net Amt</th>';
            html += '        <th class="text-end col-batch-off-exp" ' + styleOffExp + '>Official Cost Per Pc with Exp</th>';
            html += '        <th class="text-end col-batch-off-exp" ' + styleOffExp + '>Official Cost with Exp</th>';
            html += '        <th class="text-end col-batch-off-amt" ' + styleOffAmt + '>Official Cost Per Pc Net Amt</th>';
            html += '        <th class="text-end col-batch-off-amt" ' + styleOffAmt + '>Official Cost Net Amt</th>';
            html += '        <th class="text-center" style="width: 70px;">Action</th>';
            html += '      </tr>';
            html += '    </thead>';
            html += '    <tbody>';

            for (var i = 0; i < batches.length; i++) {
                var b = batches[i];
                var bQty = Number(b.quantity);
                var bBlack = Number(b.black_qty);
                var bWhite = Number(b.white_qty);
                var bPending = Number(b.pending_qty);
                var bTotWhite = Number(b.total_white_qty);
                var bBooked = Number(b.booked_qty);

                var badgeBlack = bBlack > 0 ? '<span class="stk-badge stk-badge-black">' + bBlack.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgeWhite = bWhite > 0 ? '<span class="stk-badge stk-badge-white">' + bWhite.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgePending = bPending > 0 ? '<span class="stk-badge stk-badge-pending">' + bPending.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgeTotWhite = bTotWhite > 0 ? '<span class="stk-badge stk-badge-total-white">' + bTotWhite.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgeBooked = bBooked > 0 ? '<span class="stk-badge stk-badge-booked">' + bBooked.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';

                html += '      <tr>';
                html += '        <td class="text-center fw-bold text-muted">' + b.sr_no + '</td>';
                html += '        <td><span class="batch-no-tag">' + b.batch_no + '</span></td>';
                html += '        <td class="text-end stk-qty-main">' + bQty.toLocaleString() + '</td>';
                html += '        <td class="text-end">' + badgeBlack + '</td>';
                html += '        <td class="text-end">' + badgeWhite + '</td>';
                html += '        <td class="text-end">' + badgePending + '</td>';
                html += '        <td class="text-end">' + badgeTotWhite + '</td>';
                html += '        <td class="text-end col-batch-booked" ' + styleBooked + '>' + badgeBooked + '</td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                html += '        <td class="text-end stk-cost col-batch-act-exp ' + (parseFloat(b.actual_cost_per_pc_with_exp) > 0 ? '' : 'stk-zero') + '" ' + styleActExp + '>₹' + b.actual_cost_per_pc_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-exp ' + (parseFloat(b.actual_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActExp + '>₹' + b.actual_cost_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-amt ' + (parseFloat(b.actual_cost_per_pc_net) > 0 ? '' : 'stk-zero') + '" ' + styleActAmt + '>₹' + b.actual_cost_per_pc_net + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-amt ' + (parseFloat(b.actual_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActAmt + '>₹' + b.actual_cost_net + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-exp ' + (parseFloat(b.official_cost_per_pc_with_exp) > 0 ? '' : 'stk-zero') + '" ' + styleOffExp + '>₹' + b.official_cost_per_pc_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-exp ' + (parseFloat(b.official_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffExp + '>₹' + b.official_cost_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-amt ' + (parseFloat(b.official_cost_per_pc_net) > 0 ? '' : 'stk-zero') + '" ' + styleOffAmt + '>₹' + b.official_cost_per_pc_net + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-amt ' + (parseFloat(b.official_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffAmt + '>₹' + b.official_cost_net + '</td>';
                html += '        <td class="text-center">' + b.action + '</td>';
                html += '      </tr>';
            }

            html += '    </tbody>';
            html += '  </table>';
            html += '</div>';
        }

        html += '</div></div>';
        return html;
    }

    $(document).ready(function($) {
    	var dataTable = $('#report-datatable').DataTable({ 
    	    "dom": '<"dt-toolbar"<"d-flex align-items-center"l><"dt-search"f>>t<"d-flex justify-content-between align-items-center p-1"ip>',
            "ordering": false,
            "pagingType": "simple_numbers",
            "processing": true,
            "autoWidth": false,
            "scrollX": false,
            "scrollY": "475px",
            "scrollCollapse": false,
            "serverSide": true, 
			"pageLength": 25,
            "lengthChange": true,
			"lengthMenu": [10, 25, 50, 100, 250, 500, 1000],
            "language" :{
                sLengthMenu: "Show _MENU_ entries",
                search: "",
                searchPlaceholder: "Search product name, code...",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },	
            "drawCallback": function (settings) {
                $('[data-toggle="tooltip"]').tooltip('update');

                // Update overall company totals from server response
                if (settings.json) {
                    if (settings.json.total_white_qty !== undefined) {
                        $('#total_white_stat').text(settings.json.total_white_qty);
                    }
                    if (settings.json.total_black_qty !== undefined) {
                        $('#total_black_stat').text(settings.json.total_black_qty);
                    }
                    if (settings.json.total_qty !== undefined) {
                        $('#total_qty').text(settings.json.total_qty);
                    }
                }

                // Auto-expand batches if Stock with Batch filter is active
                if ($('#toggle-stock-batch').is(':checked')) {
                    expandAllBatches();
                }
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_my_stock'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                       data.warehouse_id = '<?php echo $warehouse_id; ?>';			
                       data.type = '<?php echo $type; ?>';			
                       data.show_zero_qty = $('#toggle-zero-qty').is(':checked') ? 1 : 0;
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                { "data": "sr_no", "className": "text-center text-nowrap align-middle" },
                { "data": "product_name", "className": "text-start align-middle" },
                { "data": "quantity", "className": "text-end text-nowrap align-middle" },
                { "data": "black_qty", "className": "text-end text-nowrap align-middle" },
                { "data": "white_qty", "className": "text-end text-nowrap align-middle" },
                { "data": "pending_qty", "className": "text-end text-nowrap align-middle" },
                { "data": "total_white_qty", "className": "text-end text-nowrap align-middle" },
                { "data": "booked_qty", "className": "text-end text-nowrap align-middle" },
                { "data": "po_qty", "className": "text-end text-nowrap align-middle", "visible": false },
                { "data": "priority_qty", "className": "text-end text-nowrap align-middle", "visible": false },
                { "data": "loading_qty", "className": "text-end text-nowrap align-middle", "visible": false },
                { "data": "actual_cost_with_exp", "className": "text-end text-nowrap align-middle" },
                { "data": "actual_cost_net", "className": "text-end text-nowrap align-middle" },
                { "data": "official_cost_with_exp", "className": "text-end text-nowrap align-middle" },
                { "data": "official_cost_net", "className": "text-end text-nowrap align-middle" },
                { "data": "action", "className": "text-center text-nowrap align-middle" }
            ], 
           
            "buttons": [], 
           
            "infoCallback": function( settings, start, end, max, total, pre ) {
                $(".loader").fadeOut("slow"); 
                $('#total_count').html(total.toLocaleString());
                return 'Showing ' + start + ' to ' + end + ' of ' + total + ' products';
            }
            
        }).on('draw.dt column-sizing.dt', function () { 
            $(".loader").fadeOut("slow");
            fitMainTableWidth();
            requestAnimationFrame(function () {
                fitMainTableWidth();
            });
        });

        // Stretch parent table flush to the vertical scrollbar edge
        function fitMainTableWidth() {
            var scrollBody = document.querySelector('#report-datatable_wrapper .dataTables_scrollBody');
            if (!scrollBody) return;
            var w = Math.floor(scrollBody.clientWidth);
            if (w < 200) return;

            scrollBody.style.setProperty('overflow-x', 'hidden', 'important');

            var head = document.querySelector('#report-datatable_wrapper .dataTables_scrollHead');
            if (head) {
                head.style.setProperty('overflow-x', 'hidden', 'important');
                head.style.setProperty('overflow-y', 'hidden', 'important');
                head.style.setProperty('width', '100%', 'important');
            }

            var nodes = [
                document.querySelector('#report-datatable_wrapper .dataTables_scrollHeadInner'),
                document.querySelector('#report-datatable_wrapper .dataTables_scrollHeadInner > table'),
                document.querySelector('#report-datatable_wrapper .dataTables_scrollBody > table')
            ];
            nodes.forEach(function (node) {
                if (!node) return;
                node.style.setProperty('width', w + 'px', 'important');
                node.style.setProperty('min-width', w + 'px', 'important');
                node.style.setProperty('max-width', w + 'px', 'important');
                node.style.setProperty('padding-right', '0px', 'important');
                node.style.setProperty('box-sizing', 'border-box', 'important');
                node.style.setProperty('margin', '0px', 'important');
            });

            // Drop DT col widths so fixed layout can fill the full width
            $('#report-datatable_wrapper colgroup col').each(function () {
                this.style.setProperty('width', 'auto', 'important');
            });
        }

        // Keep appendable scroll inside child without breaking parent table layout
        function syncParentChildOverflow() {
            fitMainTableWidth();
        }

        function lockChildRow($childTr) {
            if (!$childTr || !$childTr.length) return;
            var $td = $childTr.children('td').first();
            var $wrap = $td.find('.child-scroll-wrap').first();
            if (!$wrap.length) return;

            // Restore normal table-cell behavior (undo any prior display:block breakage)
            $childTr.css('height', '');
            $td.attr('style', 'padding:0!important;border-top:none!important;background:transparent!important;');
            $wrap.attr('style',
                'display:block;width:0;min-width:100%;max-width:100%;' +
                'overflow-x:auto;overflow-y:hidden;box-sizing:border-box;padding:4px 2px;-webkit-overflow-scrolling:touch;'
            );
            fitMainTableWidth();
        }

        function fitChildScrollPanels() {
            $('#report-datatable_wrapper .dataTables_scrollBody tbody tr.child').each(function () {
                lockChildRow($(this));
            });
            $('#report-datatable_wrapper .dataTables_scrollBody tbody tr.shown').each(function () {
                var $next = $(this).next('tr');
                if ($next.length) {
                    lockChildRow($next);
                }
            });
            fitMainTableWidth();
        }

        function openChildRow(row, tr, rowData) {
            row.child(formatChildRow(rowData)).show();
            tr.addClass('shown');
            var $childTr = tr.next('tr');
            lockChildRow($childTr);
            setTimeout(function () {
                lockChildRow($childTr);
                fitMainTableWidth();
            }, 0);
            return $childTr;
        }

        $(window).on('resize', function () {
            fitChildScrollPanels();
            fitMainTableWidth();
        });

        // Stock with Batch expand/collapse functions
        function expandAllBatches() {
            var showZeroQty = $('#toggle-zero-qty').is(':checked');
            dataTable.rows({ page: 'current' }).every(function () {
                var row = this;
                var rowData = row.data();
                if (rowData && rowData.batches) {
                    var activeBatches = showZeroQty ? rowData.batches : rowData.batches.filter(function (b) {
                        return Number(b.quantity) > 0;
                    });
                    if (activeBatches.length > 0) {
                        if (!row.child.isShown()) {
                            var tr = $(row.node());
                            openChildRow(row, tr, rowData);
                            tr.find('.btn-expand-row i').removeClass('icon-plus').addClass('icon-minus');
                        }
                    }
                }
            });
            $('[data-toggle="tooltip"]').tooltip();
            fitChildScrollPanels();
        }

        function collapseAllBatches() {
            dataTable.rows().every(function () {
                var row = this;
                if (row.child.isShown()) {
                    row.child.hide();
                    var tr = $(row.node());
                    tr.removeClass('shown');
                    tr.find('.btn-expand-row i').removeClass('icon-minus').addClass('icon-plus');
                }
            });
            syncParentChildOverflow();
        }

        // Dynamic Column Visibility Filters Function
        function applyColumnFilters() {
            var showBooked = $('#toggle-booked-qty').is(':checked');
            var showPo = $('#toggle-po-qty').is(':checked');
            var showActExp = $('#toggle-act-cost-exp').is(':checked');
            var showActAmt = $('#toggle-act-cost-amt').is(':checked');
            var showOffExp = $('#toggle-off-cost-exp').is(':checked');
            var showOffAmt = $('#toggle-off-cost-amt').is(':checked');

            // Toggle main table columns without reload
            dataTable.column(7).visible(showBooked, false);
            dataTable.column(8).visible(showPo, false);
            dataTable.column(9).visible(showPo, false);
            dataTable.column(10).visible(showPo, false);
            dataTable.column(11).visible(showActExp, false);
            dataTable.column(12).visible(showActAmt, false);
            dataTable.column(13).visible(showOffExp, false);
            dataTable.column(14).visible(showOffAmt, false);
            dataTable.columns.adjust();
            fitMainTableWidth();
            requestAnimationFrame(function () {
                fitMainTableWidth();
            });

            // Toggle columns in any currently expanded sub-batch tables
            $('.sub-batch-table .col-batch-booked').toggle(showBooked);
            $('.sub-batch-table .col-batch-po').toggle(showPo);
            $('.sub-batch-table .col-batch-act-exp').toggle(showActExp);
            $('.sub-batch-table .col-batch-act-amt').toggle(showActAmt);
            $('.sub-batch-table .col-batch-off-exp').toggle(showOffExp);
            $('.sub-batch-table .col-batch-off-amt').toggle(showOffAmt);
            fitChildScrollPanels();
        }

        // Stock with Batch Toggle Handler
        $('#toggle-stock-batch').on('change', function () {
            var isChecked = $(this).is(':checked');
            $(this).closest('.filter-chip').toggleClass('active', isChecked);
            if (isChecked) {
                expandAllBatches();
            } else {
                collapseAllBatches();
            }
        });

        // Show Item with Zero Qty Toggle Handler
        $('#toggle-zero-qty').on('change', function () {
            var isChecked = $(this).is(':checked');
            $(this).closest('.filter-chip').toggleClass('active', isChecked);
            dataTable.ajax.reload();
        });

        // Column Filter Checkbox Change Handler
        $('.column-filter-checkbox').on('change', function() {
            var isChecked = $(this).is(':checked');
            $(this).closest('.filter-chip').toggleClass('active', isChecked);
            applyColumnFilters();
        });

        // Reset Filter Handler
        $('#btn-reset-col-filters').on('click', function(e) {
            e.preventDefault();
            $('#toggle-stock-batch').prop('checked', false).closest('.filter-chip').removeClass('active');
            collapseAllBatches();

            var zeroWasChecked = $('#toggle-zero-qty').is(':checked');
            $('#toggle-zero-qty').prop('checked', false).closest('.filter-chip').removeClass('active');

            $('#toggle-booked-qty').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-po-qty').prop('checked', false).closest('.filter-chip').removeClass('active');
            $('#toggle-act-cost-exp').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-act-cost-amt').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-off-cost-exp').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-off-cost-amt').prop('checked', true).closest('.filter-chip').addClass('active');
            applyColumnFilters();

            if (zeroWasChecked) {
                dataTable.ajax.reload();
            }
        });

        // Row Child Details Expansion Listener
        $('#report-datatable tbody').on('click', '.btn-expand-row', function (e) {
            e.stopPropagation();
            var tr = $(this).closest('tr');
            var row = dataTable.row(tr);
            var icon = $(this).find('i');

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('icon-minus').addClass('icon-plus');
                syncParentChildOverflow();
            } else {
                var rowData = row.data();
                var showZeroQty = $('#toggle-zero-qty').is(':checked');
                var allBatches = rowData ? (rowData.batches || []) : [];
                var activeBatches = showZeroQty ? allBatches : allBatches.filter(function (b) {
                    return Number(b.quantity) > 0;
                });
                if (activeBatches.length > 0) {
                    openChildRow(row, tr, rowData);
                    icon.removeClass('icon-plus').addClass('icon-minus');
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }
        });
    });
</script>