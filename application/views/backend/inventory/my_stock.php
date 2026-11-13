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
		overflow-x: auto !important;
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
	table.dataTable tbody tr.child > td,
	#report-datatable tbody tr.child > td,
	#report-datatable_wrapper .dataTables_scrollBody table > tbody > tr.child > td {
		padding: 0 !important;
		border-top: none !important;
		border-left: none !important;
		border-right: none !important;
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
		padding: 0 !important;
		margin: 0 !important;
	}
	.child-scroll-wrap .batch-breakdown-card,
	.child-scroll-wrap .company-breakdown-card {
		display: block;
		width: max-content;
		min-width: 100%;
		max-width: none !important;
		margin-left: 0 !important;
		margin-right: 0 !important;
		padding-left: 0 !important;
		padding-right: 0 !important;
		box-sizing: border-box;
		overflow: visible !important;
		border-left: none !important;
		border-right: none !important;
		border-radius: 0 !important;
	}
	.child-scroll-wrap .table-responsive,
	.child-scroll-wrap .batch-breakdown-card > .table-responsive,
	.child-scroll-wrap .company-breakdown-card > .table-responsive {
		display: block !important;
		width: max-content !important;
		min-width: 100% !important;
		max-width: none !important;
		overflow: visible !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
	}
	.child-scroll-wrap .sub-batch-table,
	.child-scroll-wrap .sub-company-table {
		min-width: 100%;
		margin-bottom: 0;
		border-left: none !important;
		border-right: none !important;
		border-radius: 0 !important;
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
		letter-spacing: 0.3px !important;
		padding: 8px 6px !important;
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
		line-height: 1.2;
	}
	#report-datatable tbody td {
		padding: 6px 6px !important;
		font-size: 12.5px !important;
		color: #334155;
		border-bottom: 1px solid #f1f5f9 !important;
		border-top: none !important;
		vertical-align: middle !important;
	}
	#report-datatable thead th:first-child,
	#report-datatable tbody tr:not(.child) > td:first-child,
	#report-datatable_wrapper .dataTables_scrollHead table th:first-child,
	#report-datatable_wrapper .dataTables_scrollBody table tbody tr:not(.child) > td:first-child {
		padding-left: 2px !important;
		padding-right: 2px !important;
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

	/* Category Subheader Row */
	.category-subheader-row {
		background: #f1f5f9 !important;
		width: 100% !important;
	}
	.category-subheader-row td,
	#report-datatable_wrapper .dataTables_scrollBody > table > tbody > tr.category-subheader-row > td {
		padding: 7px 12px !important;
		background: linear-gradient(90deg, #eef2ff 0%, #f8fafc 100%) !important;
		border-top: 1px solid #cbd5e1 !important;
		border-bottom: 1px solid #cbd5e1 !important;
		border-left: 4px solid #6366f1 !important;
		color: #1e293b !important;
		overflow: visible !important;
		text-overflow: clip !important;
		white-space: nowrap !important;
	}
	.category-subheader-row:hover td {
		background: linear-gradient(90deg, #e0e7ff 0%, #f1f5f9 100%) !important;
	}
	.category-header-wrap {
		display: inline-flex;
		align-items: center;
		gap: 8px;
	}
	.category-header-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 22px;
		height: 22px;
		border-radius: 5px;
		background: #6366f1;
		color: #ffffff;
		font-size: 11px;
	}
	.category-header-title {
		font-weight: 700;
		color: #1e1b4b;
		font-size: 12.5px;
		letter-spacing: 0.4px;
		text-transform: uppercase;
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
		padding: 8px 0 0 0 !important;
		margin: 0 !important;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
		box-sizing: border-box;
	}
	.batch-breakdown-card > .table-responsive {
		overflow: visible;
		max-width: none;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
	}
	.batch-card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 6px;
		padding: 0 8px 6px 8px;
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
		table-layout: fixed !important;
		box-sizing: border-box !important;
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
		overflow: hidden !important;
		text-overflow: ellipsis !important;
		box-sizing: border-box !important;
	}
	.sub-batch-table tbody td {
		padding: 5px 8px !important;
		font-size: 11.5px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		vertical-align: middle !important;
		white-space: nowrap !important;
		overflow: hidden !important;
		text-overflow: ellipsis !important;
		box-sizing: border-box !important;
	}
	.sub-batch-table tbody tr:last-child td {
		border-bottom: none !important;
	}
	.sub-batch-table tbody tr:hover {
		background-color: #f8fafc !important;
	}
	.sub-batch-table thead th.col-sub-sr,
	.sub-batch-table tbody td.col-sub-sr {
		padding-left: 2px !important;
		padding-right: 2px !important;
	}
	.sub-batch-table .col-sub-name {
		box-sizing: border-box !important;
		overflow: hidden !important;
		text-overflow: ellipsis !important;
		white-space: nowrap !important;
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
		<div class="stock-table-card stock-mode-all">
			<!-- Column Visibility & Data Filter Toolbar -->
			<div class="stock-filter-toolbar">
				<div class="stock-pill-nav" role="tablist" aria-label="Stock Filter Modes">
					<button type="button" class="stock-pill-btn active" data-stock-mode="all" title="Show all stock columns">
						<span class="pill-dot dot-all"></span> All
					</button>
					<button type="button" class="stock-pill-btn" data-stock-mode="white" title="Show white stock only">
						<span class="pill-dot dot-white"></span> White
					</button>
					<button type="button" class="stock-pill-btn" data-stock-mode="black" title="Show black stock only">
						<span class="pill-dot dot-black"></span> Black
					</button>
				</div>
				<div class="d-flex align-items-center ms-auto gap-1">
				<button type="button" class="btn-filter-dropdown" id="btnStockAdjustment" title="Stock Adjustment">
					<i class="feather icon-sliders"></i>
					<span>Stock Adjustment</span>
				</button>
				<div class="dropdown stock-filter-dropdown">
					<button class="btn-filter-dropdown dropdown-toggle" type="button" id="stockFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
						<i class="feather icon-filter"></i>
						<span>Filters</span>
						<span class="filter-count-badge" id="filter-active-count">0</span>
					</button>
					<div class="dropdown-menu stock-filter-menu" aria-labelledby="stockFilterDropdown">
						<div class="filter-menu-header">
							<span class="title">Table Filters</span>
						</div>
						<div class="filter-menu-body" id="column-filters-container">
							<div class="filter-menu-label">Data Options</div>
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

							<div class="filter-menu-label">Columns</div>
							<label class="filter-chip active" for="toggle-product-col" title="Toggle Product Name column">
								<input type="checkbox" id="toggle-product-col" class="column-filter-checkbox" checked>
								<span class="chip-box"><i class="feather icon-check"></i></span>
								<span class="chip-text">Product</span>
							</label>
							<label class="filter-chip" for="toggle-model-col" title="Toggle Model No column">
								<input type="checkbox" id="toggle-model-col" class="column-filter-checkbox">
								<span class="chip-box"><i class="feather icon-check"></i></span>
								<span class="chip-text">Model</span>
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
							<label class="filter-chip active" for="toggle-act-cost" title="Toggle Actual Cost columns (Act Cost Exp, Act Cost Amt)">
								<input type="checkbox" id="toggle-act-cost" class="column-filter-checkbox" checked>
								<span class="chip-box"><i class="feather icon-check"></i></span>
								<span class="chip-text">Actual Cost</span>
							</label>
							<label class="filter-chip active" for="toggle-act-cost-pc" title="Toggle Actual Cost Per Pc columns (Act Cost / exp, Act Cost / Amt)">
								<input type="checkbox" id="toggle-act-cost-pc" class="column-filter-checkbox" checked>
								<span class="chip-box"><i class="feather icon-check"></i></span>
								<span class="chip-text">Actual Cost Per Pc</span>
							</label>
							<label class="filter-chip active" for="toggle-off-cost" title="Toggle Official Cost columns (Off Cost Exp, Off Cost Amt)">
								<input type="checkbox" id="toggle-off-cost" class="column-filter-checkbox" checked>
								<span class="chip-box"><i class="feather icon-check"></i></span>
								<span class="chip-text">Official Cost</span>
							</label>
							<label class="filter-chip active" for="toggle-off-cost-pc" title="Toggle Official Cost Per Pc columns (Off Cost / exp, Off Cost / Amt)">
								<input type="checkbox" id="toggle-off-cost-pc" class="column-filter-checkbox" checked>
								<span class="chip-box"><i class="feather icon-check"></i></span>
								<span class="chip-text">Official Cost Per Pc</span>
							</label>
						</div>
						<div class="filter-menu-footer">
							<button type="button" class="btn-reset-filters" id="btn-reset-col-filters" title="Reset column filters to default">
								<i class="feather icon-rotate-ccw"></i> Reset Filters
							</button>
						</div>
					</div>
				</div>
				</div>
			</div>

			<div class="stock-table-scroll">
				<table class="table leads-table w-100" id="report-datatable">
					<thead>
						<tr>
							<th class="text-center" style="width: 50px;">#</th>
							<th>Product Name</th>
							<th>Model No</th>
							<th class="text-end">Quantity</th>
							<th class="text-end">Black Qty</th>
							<th class="text-end">White Qty</th>
							<th class="text-end">Pending Qty</th>
							<th class="text-end">Total White Qty</th>
							<th class="text-end">Booked Qty</th>
							<th class="text-end">PO Qty</th>
							<th class="text-end">Priority Qty</th>
							<th class="text-end">Loading Qty</th>
							<th class="text-end th-cost-wrap">Act Cost Exp</th>
							<th class="text-end th-cost-wrap">Act Cost Amt</th>
							<th class="text-end th-cost-wrap">Off Cost Exp</th>
							<th class="text-end th-cost-wrap">Off Cost Amt</th>
							<th class="text-center" style="width: 55px;">Action</th>
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

<?php
$sa_products = $this->db->query("
	SELECT id, name, item_code, costing_price, gst
	FROM raw_products
	WHERE is_deleted = 0
	ORDER BY name ASC
")->result_array();
?>

<!-- Stock Adjustment Modal -->
<style>
	#stockAdjustmentModal .modal-dialog {
		max-width: min(1280px, calc(100vw - 1.5rem));
		max-height: calc(100vh - 1.5rem);
		margin: 0.75rem auto;
	}
	#stockAdjustmentModal .modal-content {
		max-height: calc(100vh - 1.5rem);
		display: flex;
		flex-direction: column;
		overflow: hidden;
	}
	#stockAdjustmentModal #stockAdjustmentForm {
		display: flex;
		flex-direction: column;
		min-height: 0;
		flex: 1 1 auto;
		overflow: hidden;
	}
	#stockAdjustmentModal .modal-body {
		display: flex;
		flex-direction: column;
		min-height: 0;
		flex: 1 1 auto;
		overflow: hidden;
	}
	#stockAdjustmentModal .sa-products-scroll {
		flex: 1 1 auto;
		min-height: 140px;
		max-height: calc(100vh - 260px);
		overflow: auto;
		-webkit-overflow-scrolling: touch;
	}
	#stockAdjustmentModal .sa-products-scroll thead th {
		position: sticky;
		top: 0;
		z-index: 2;
		background: #f8f8fb;
		white-space: nowrap;
		font-size: 11px;
	}
	#stockAdjustmentModal .sa-products-scroll .form-control-sm {
		min-width: 72px;
	}
	#stockAdjustmentModal .modal-footer {
		flex-shrink: 0;
	}
	#stockAdjustmentModal .sa-modal-close {
		background: transparent;
		border: 0;
		font-size: 1.5rem;
		line-height: 1;
		padding: 0.25rem 0.5rem;
		color: #6e6b7b;
		opacity: 0.85;
	}
	#stockAdjustmentModal .sa-modal-close:hover {
		color: #5e5873;
		opacity: 1;
	}
</style>
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered">
		<div class="modal-content border-0 shadow">
			<div class="modal-header border-bottom py-2 flex-shrink-0">
				<h5 class="modal-title fw-bolder text-dark">
					<i class="feather icon-sliders me-1"></i>Stock Adjustment
				</h5>
				<button type="button" class="sa-modal-close" id="sa_modal_close_btn" aria-label="Close">&times;</button>
			</div>
			<form id="stockAdjustmentForm">
				<div class="modal-body p-3">
					<div class="d-flex flex-wrap align-items-end mb-2 flex-shrink-0" style="gap: 10px;">
						<div class="btn-group" role="group">
							<input type="radio" class="btn-check" name="mode" id="sa_mode_add" value="add" checked>
							<label class="btn btn-outline-success btn-sm" for="sa_mode_add">Add Stock</label>
							<input type="radio" class="btn-check" name="mode" id="sa_mode_deduct" value="deduct">
							<label class="btn btn-outline-danger btn-sm" for="sa_mode_deduct">Deduct Stock</label>
						</div>

						<div style="width: 220px; max-width: 100%; flex: 0 0 220px;">
							<label class="form-label mb-25">Warehouse <span class="text-danger">*</span></label>
							<select class="form-control form-control-sm" name="warehouse_id" id="sa_warehouse_id" required>
								<option value="">Select Warehouse</option>
								<?php if (!empty($warehouse_list)) {
									foreach ($warehouse_list as $wh) {
										$wh_id = (string)$wh['id'];
										if ($wh_id === '' || $wh_id === 'All') {
											continue;
										}
										$selected = ((string)$warehouse_id === $wh_id) ? ' selected' : '';
										?>
										<option value="<?php echo htmlspecialchars($wh_id); ?>"<?php echo $selected; ?>>
											<?php echo htmlspecialchars($wh['name']); ?>
										</option>
									<?php }
								} ?>
							</select>
						</div>

						<div id="sa_batch_input_wrap" style="width: 220px; max-width: 100%; flex: 0 0 220px;">
							<label class="form-label mb-25">Batch No <span class="text-danger">*</span></label>
							<input type="text" class="form-control form-control-sm" name="batch_no" id="sa_batch_no_input" placeholder="Enter new batch number">
						</div>
						<div class="d-none" id="sa_batch_select_wrap" style="width: 220px; max-width: 100%; flex: 0 0 220px;">
							<label class="form-label mb-25">Batch No <span class="text-danger">*</span></label>
							<select class="form-control form-control-sm" id="sa_batch_no_select">
								<option value="">Select Batch</option>
							</select>
						</div>

						<button type="button" class="btn btn-sm btn-outline-primary ms-auto" id="sa_add_row_btn">
							<i class="feather icon-plus"></i> Add Row
						</button>
					</div>

					<div class="mb-1 flex-shrink-0">
						<strong class="font-small-3">Products</strong>
					</div>
					<div class="table-responsive sa-products-scroll border rounded">
						<table class="table table-bordered table-sm mb-0" id="sa_products_table">
							<thead class="table-light text-center">
								<tr>
									<th style="min-width:200px;">Product</th>
									<th class="sa-col-avail" style="width:90px;">Avail White</th>
									<th class="sa-col-avail" style="width:90px;">Avail Black</th>
									<th style="width:90px;">White Qty</th>
									<th style="width:90px;">Black Qty</th>
									<th style="width:100px;">Rate</th>
									<th style="width:100px;">Per Qty Bill</th>
									<th style="width:100px;">Total Bill</th>
									<th style="width:80px;">GST %</th>
									<th style="width:100px;">GST Amt</th>
									<th style="width:110px;">Bill + GST</th>
									<th style="width:100px;">Per Qty Black</th>
									<th style="width:100px;">Total Black</th>
									<th style="width:100px;">Final Amt</th>
									<th style="width:50px;"></th>
								</tr>
							</thead>
							<tbody id="sa_products_tbody"></tbody>
						</table>
					</div>
					<small class="text-muted flex-shrink-0 mt-1 d-block" id="sa_help_text">Add mode creates a new batch. Enter white/black qty with rate, bill and GST.</small>
				</div>
				<div class="modal-footer py-2">
					<button type="button" class="btn btn-outline-secondary btn-sm" id="sa_modal_cancel_btn">Cancel</button>
					<button type="submit" class="btn btn-primary btn-sm" id="sa_submit_btn">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
var saAddProducts = <?php echo json_encode(array_map(function ($p) {
	return array(
		'id' => (int)$p['id'],
		'label' => trim(($p['item_code'] ? $p['item_code'] . ' - ' : '') . $p['name']),
		'rate' => (float)($p['costing_price'] ?? 0),
		'gst' => (float)($p['gst'] ?? 0),
	);
}, $sa_products)); ?>;
var saDeductProducts = [];
var saDefaultWarehouseId = '<?php echo htmlspecialchars(($warehouse_id !== 'All') ? $warehouse_id : ''); ?>';

function saGetMode() {
	return $('#stockAdjustmentForm input[name="mode"]:checked').val() || 'add';
}

function saGetWarehouseId() {
	return String($('#sa_warehouse_id').val() || '').trim();
}

function saCloseModal() {
	$('#stockAdjustmentModal').modal('hide');
}

function saOnWarehouseChange() {
	saDeductProducts = [];
	$('#sa_batch_no_select').html('<option value="">Select Batch</option>');
	$('#sa_products_tbody').empty();
	if (saGetMode() === 'deduct') {
		if (saGetWarehouseId()) {
			saLoadBatches();
		}
	} else {
		saAddProductRow();
	}
}

function saEsc(str) {
	return String(str == null ? '' : str)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/"/g, '&quot;');
}

function saNum(val) {
	var n = parseFloat(val);
	return isNaN(n) ? 0 : n;
}

function saFixed(val) {
	return saNum(val).toFixed(2);
}

function saBuildProductOptions(list, selectedId) {
	var html = '<option value="">Select Product</option>';
	(list || []).forEach(function (p) {
		var id = p.id;
		var selected = String(selectedId) === String(id) ? ' selected' : '';
		html += '<option value="' + id + '"' + selected
			+ ' data-rate="' + saEsc(saFixed(p.rate)) + '"'
			+ ' data-gst="' + saEsc(saFixed(p.gst)) + '">'
			+ saEsc(p.label) + '</option>';
	});
	return html;
}

function saAmountFieldsHtml(rate, bill, gst) {
	rate = saFixed(rate);
	bill = saFixed(bill);
	gst = saFixed(gst);
	return `
		<td><input type="number" min="0" step="any" class="form-control form-control-sm text-right sa-rate" name="rate[]" value="${rate}"></td>
		<td><input type="number" min="0" step="any" class="form-control form-control-sm text-right sa-per-qty-bill" name="per_qty_bill_amt[]" value="${bill}"></td>
		<td><input type="number" min="0" step="any" class="form-control form-control-sm text-right sa-total-bill" name="total_bill_amt[]" value="0.00" readonly tabindex="-1"></td>
		<td><input type="number" min="0" step="any" class="form-control form-control-sm text-right sa-gst-rate" name="gst_rate[]" value="${gst}"></td>
		<td><input type="number" min="0" step="any" class="form-control form-control-sm text-right sa-gst-amt" name="gst_amt[]" value="0.00" readonly tabindex="-1"></td>
		<td><input type="number" min="0" step="any" class="form-control form-control-sm text-right sa-total-bill-gst" name="total_bill_gst_amt[]" value="0.00" readonly tabindex="-1"></td>
		<td><input type="number" step="any" class="form-control form-control-sm text-right sa-per-qty-black" name="per_qty_black_amt[]" value="0.00" readonly tabindex="-1"></td>
		<td><input type="number" step="any" class="form-control form-control-sm text-right sa-total-black" name="total_black_amt[]" value="0.00" readonly tabindex="-1"></td>
		<td><input type="number" min="0" step="any" class="form-control form-control-sm text-right sa-final-amt" name="final_amt[]" value="0.00" readonly tabindex="-1"></td>
	`;
}

function saCalculateRow($row) {
	if (!$row || !$row.length) return;
	var rate = saNum($row.find('.sa-rate').val());
	var whiteQty = saNum($row.find('.sa-white-qty').val());
	var blackQty = saNum($row.find('.sa-black-qty').val());
	var perQtyBill = saNum($row.find('.sa-per-qty-bill').val());
	var gstRate = saNum($row.find('.sa-gst-rate').val());

	var totalBill = perQtyBill * whiteQty;
	var gstAmt = (totalBill * gstRate) / 100;
	var totalBillGst = totalBill + gstAmt;
	var perQtyBlack = rate - perQtyBill;
	var totalBlack = (rate * blackQty) + ((rate - perQtyBill) * whiteQty);
	var finalAmt = totalBillGst + totalBlack;

	$row.find('.sa-total-bill').val(saFixed(totalBill));
	$row.find('.sa-gst-amt').val(saFixed(gstAmt));
	$row.find('.sa-total-bill-gst').val(saFixed(totalBillGst));
	$row.find('.sa-per-qty-black').val(saFixed(perQtyBlack));
	$row.find('.sa-total-black').val(saFixed(totalBlack));
	$row.find('.sa-final-amt').val(saFixed(finalAmt));
}

function saApplyProductDefaults($row) {
	var $opt = $row.find('.sa-product-select option:selected');
	if (!$opt.length || !$opt.val()) {
		return;
	}
	var rate = saFixed($opt.data('rate'));
	var gst = saFixed($opt.data('gst'));
	$row.find('.sa-rate').val(rate);
	$row.find('.sa-per-qty-bill').val(rate);
	$row.find('.sa-gst-rate').val(gst);
	saCalculateRow($row);
}

function saToggleModeUI() {
	var mode = saGetMode();
	$('#sa_products_tbody').empty();
	saDeductProducts = [];

	if (mode === 'deduct') {
		$('#sa_batch_input_wrap').addClass('d-none');
		$('#sa_batch_select_wrap').removeClass('d-none');
		$('#sa_batch_no_input').prop('disabled', true).val('');
		$('#sa_batch_no_select').prop('disabled', false);
		$('#sa_add_row_btn').addClass('d-none');
		$('#sa_help_text').text('Select warehouse and batch to load products. Enter qty, rate, bill and GST for lines you want to deduct.');
		$('#sa_products_table thead th.sa-col-avail').show();
		if (saGetWarehouseId()) {
			saLoadBatches();
		} else {
			$('#sa_batch_no_select').html('<option value="">Select warehouse first</option>');
		}
	} else {
		$('#sa_batch_select_wrap').addClass('d-none');
		$('#sa_batch_input_wrap').removeClass('d-none');
		$('#sa_batch_no_select').prop('disabled', true).val('');
		$('#sa_batch_no_input').prop('disabled', false);
		$('#sa_add_row_btn').removeClass('d-none');
		$('#sa_help_text').text('Select warehouse, enter a new batch, then white/black qty with rate, bill and GST.');
		$('#sa_products_table thead th.sa-col-avail').hide();
		saAddProductRow();
	}
}

function saLoadBatches() {
	var $select = $('#sa_batch_no_select');
	var warehouseId = saGetWarehouseId();
	if (!warehouseId) {
		$select.html('<option value="">Select warehouse first</option>');
		return;
	}
	$select.html('<option value="">Loading...</option>');
	$.post('<?php echo base_url('inventory/get_stock_adjustment_batches'); ?>', {
		warehouse_id: warehouseId
	}, function (res) {
		var rows = [];
		try { rows = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { rows = []; }
		var html = '<option value="">Select Batch</option>';
		(rows || []).forEach(function (r) {
			if (!r.batch_no) return;
			html += '<option value="' + saEsc(r.batch_no) + '">' + saEsc(r.batch_no) + '</option>';
		});
		$select.html(html);
	}).fail(function () {
		$select.html('<option value="">Failed to load batches</option>');
	});
}

function saRenderDeductRows(list) {
	$('#sa_products_tbody').empty();
	(list || []).forEach(function (item) {
		var availWhite = parseInt(item.official_qty, 10) || 0;
		var availBlack = parseInt(item.black_qty, 10) || 0;
		var whiteDisabled = availWhite <= 0 ? ' disabled readonly' : '';
		var blackDisabled = availBlack <= 0 ? ' disabled readonly' : '';
		var whiteTitle = availWhite <= 0 ? ' title="No white stock available"' : '';
		var blackTitle = availBlack <= 0 ? ' title="No black stock available"' : '';
		var rate = saFixed(item.actual_inr);
		var bill = saFixed(item.official_rate_rs);
		var gst = saFixed(item.gst);
		var row = `
			<tr class="sa-product-row" data-avail-white="${availWhite}" data-avail-black="${availBlack}">
				<td>
					<input type="hidden" name="inventory_id[]" class="sa-product-select" value="${item.id}">
					<span class="font-small-3">${saEsc(item.label || item.product_name || '')}</span>
				</td>
				<td class="text-center sa-avail-white">${availWhite}</td>
				<td class="text-center sa-avail-black">${availBlack}</td>
				<td><input type="number" min="0" max="${availWhite}" step="1" class="form-control form-control-sm text-right sa-white-qty" name="white_qty[]" value="0"${whiteDisabled}${whiteTitle}></td>
				<td><input type="number" min="0" max="${availBlack}" step="1" class="form-control form-control-sm text-right sa-black-qty" name="black_qty[]" value="0"${blackDisabled}${blackTitle}></td>
				${saAmountFieldsHtml(rate, bill, gst)}
				<td class="text-center">-</td>
			</tr>
		`;
		var $row = $(row);
		$('#sa_products_tbody').append($row);
		saCalculateRow($row);
	});
}

function saClampDeductQty($input) {
	if (saGetMode() !== 'deduct' || !$input.length || $input.prop('disabled')) {
		return;
	}
	var $row = $input.closest('tr.sa-product-row');
	var isWhite = $input.hasClass('sa-white-qty');
	var max = parseInt($input.attr('max'), 10);
	if (isNaN(max)) {
		max = parseInt($row.data(isWhite ? 'avail-white' : 'avail-black'), 10) || 0;
	}
	var val = parseInt($input.val(), 10);
	if (isNaN(val) || val < 0) {
		$input.val(0);
		return;
	}
	if (val > max) {
		$input.val(max);
		Swal.fire({
			toast: true,
			position: 'top-end',
			icon: 'warning',
			title: 'Cannot deduct more than available (' + max + ')',
			showConfirmButton: false,
			timer: 1800
		});
	}
}

function saLoadBatchProducts(batchNo) {
	saDeductProducts = [];
	$('#sa_products_tbody').empty();
	var warehouseId = saGetWarehouseId();
	if (!batchNo || !warehouseId) return;

	$.post('<?php echo base_url('inventory/get_stock_adjustment_batch_products'); ?>', {
		warehouse_id: warehouseId,
		batch_no: batchNo
	}, function (res) {
		try { saDeductProducts = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) { saDeductProducts = []; }
		if (!saDeductProducts.length) {
			Swal.fire({ icon: 'info', title: 'No Products', text: 'No products with available qty found in this batch.' });
			return;
		}
		saRenderDeductRows(saDeductProducts);
	}).fail(function () {
		Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load batch products.' });
	});
}

function saAddProductRow() {
	var mode = saGetMode();
	if (mode === 'deduct') {
		return;
	}

	var list = saAddProducts;
	var row = `
		<tr class="sa-product-row">
			<td>
				<select class="form-control form-control-sm sa-product-select" name="product_id[]" required>
					${saBuildProductOptions(list, '')}
				</select>
			</td>
			<td class="text-center sa-avail-white d-none">-</td>
			<td class="text-center sa-avail-black d-none">-</td>
			<td><input type="number" min="0" step="1" class="form-control form-control-sm text-right sa-white-qty" name="white_qty[]" value="0"></td>
			<td><input type="number" min="0" step="1" class="form-control form-control-sm text-right sa-black-qty" name="black_qty[]" value="0"></td>
			${saAmountFieldsHtml(0, 0, 0)}
			<td class="text-center">
				<button type="button" class="btn btn-sm btn-outline-danger sa-remove-row" title="Remove">&times;</button>
			</td>
		</tr>
	`;
	$('#sa_products_tbody').append(row);
	var $row = $('#sa_products_tbody tr.sa-product-row').last();
	if ($.fn.select2) {
		$row.find('.sa-product-select').select2({
			dropdownParent: $('#stockAdjustmentModal'),
			width: '100%'
		});
	}
	saCalculateRow($row);
}

$(document).on('click', '#btnStockAdjustment', function () {
	if (!$('#sa_warehouse_id option').filter(function () { return $(this).val(); }).length) {
		Swal.fire({ icon: 'warning', title: 'No Warehouse', text: 'No warehouse available for this company.' });
		return;
	}
	$('#stockAdjustmentForm')[0].reset();
	$('#sa_mode_add').prop('checked', true);
	if (saDefaultWarehouseId) {
		$('#sa_warehouse_id').val(saDefaultWarehouseId);
	}
	saToggleModeUI();
	$('#stockAdjustmentModal').modal('show');
});

$(document).on('click', '#sa_modal_close_btn, #sa_modal_cancel_btn', function (e) {
	e.preventDefault();
	saCloseModal();
});

$(document).on('change', '#stockAdjustmentForm input[name="mode"]', function () {
	saToggleModeUI();
});

$(document).on('change', '#sa_warehouse_id', function () {
	saOnWarehouseChange();
});

$(document).on('change', '#sa_batch_no_select', function () {
	saLoadBatchProducts($(this).val());
});

$(document).on('click', '#sa_add_row_btn', function () {
	if (saGetMode() === 'add') {
		saAddProductRow();
	}
});

$(document).on('click', '.sa-remove-row', function () {
	$(this).closest('tr').remove();
});

$(document).on('change select2:select', '#sa_products_tbody .sa-product-select', function () {
	if (saGetMode() !== 'add') return;
	saApplyProductDefaults($(this).closest('tr.sa-product-row'));
});

$(document).on('input change', '#sa_products_tbody .sa-rate', function () {
	var $row = $(this).closest('tr.sa-product-row');
	$row.find('.sa-per-qty-bill').val(saFixed($(this).val()));
	saCalculateRow($row);
});

$(document).on('input change blur', '#sa_products_tbody .sa-white-qty, #sa_products_tbody .sa-black-qty', function () {
	saClampDeductQty($(this));
	saCalculateRow($(this).closest('tr.sa-product-row'));
});

$(document).on('input change', '#sa_products_tbody .sa-per-qty-bill, #sa_products_tbody .sa-gst-rate', function () {
	saCalculateRow($(this).closest('tr.sa-product-row'));
});

$(document).on('submit', '#stockAdjustmentForm', function (e) {
	e.preventDefault();
	var mode = saGetMode();
	var warehouseId = saGetWarehouseId();
	var batchNo = mode === 'deduct' ? $('#sa_batch_no_select').val() : $('#sa_batch_no_input').val();
	batchNo = $.trim(batchNo || '');

	if (!warehouseId) {
		Swal.fire({ icon: 'warning', title: 'Warehouse Required', text: 'Please select a warehouse.' });
		return;
	}
	if (!batchNo) {
		Swal.fire({ icon: 'warning', title: 'Batch Required', text: 'Please enter/select a batch number.' });
		return;
	}
	if (!$('#sa_products_tbody tr.sa-product-row').length) {
		Swal.fire({ icon: 'warning', title: 'No Products', text: mode === 'deduct' ? 'Select a batch to load products.' : 'Please add at least one product row.' });
		return;
	}

	var hasQty = false;
	var invalid = false;
	var selectedIds = [];
	$('#sa_products_tbody tr.sa-product-row').each(function () {
		var $row = $(this);
		saCalculateRow($row);
		var id = $row.find('.sa-product-select').val();
		var whiteQty = parseInt($row.find('.sa-white-qty').val(), 10) || 0;
		var blackQty = parseInt($row.find('.sa-black-qty').val(), 10) || 0;

		if (mode === 'add') {
			if (!id) {
				invalid = true;
				Swal.fire({ icon: 'warning', title: 'Product Required', text: 'Please select product in every row.' });
				return false;
			}
			if (selectedIds.indexOf(String(id)) !== -1) {
				invalid = true;
				Swal.fire({ icon: 'warning', title: 'Duplicate', text: 'Same product selected more than once.' });
				return false;
			}
			selectedIds.push(String(id));
		}

		if (whiteQty < 0 || blackQty < 0) {
			invalid = true;
			Swal.fire({ icon: 'warning', title: 'Invalid Qty', text: 'Quantity cannot be negative.' });
			return false;
		}
		if (mode === 'deduct') {
			var maxW = parseInt($row.attr('data-avail-white'), 10);
			var maxB = parseInt($row.attr('data-avail-black'), 10);
			if (isNaN(maxW)) maxW = parseInt($row.find('.sa-white-qty').attr('max'), 10) || 0;
			if (isNaN(maxB)) maxB = parseInt($row.find('.sa-black-qty').attr('max'), 10) || 0;

			if (maxW <= 0 && whiteQty > 0) {
				invalid = true;
				Swal.fire({ icon: 'warning', title: 'Invalid Qty', text: 'No white stock available to deduct for this product.' });
				return false;
			}
			if (maxB <= 0 && blackQty > 0) {
				invalid = true;
				Swal.fire({ icon: 'warning', title: 'Invalid Qty', text: 'No black stock available to deduct for this product.' });
				return false;
			}
			if (whiteQty > maxW || blackQty > maxB) {
				invalid = true;
				Swal.fire({ icon: 'warning', title: 'Invalid Qty', text: 'Deduct qty cannot exceed available white/black stock.' });
				return false;
			}
		}
		if (whiteQty > 0 || blackQty > 0) hasQty = true;
	});
	if (invalid) {
		return;
	}
	if (!hasQty) {
		Swal.fire({ icon: 'warning', title: 'Quantity Required', text: 'Enter white or black quantity for at least one product.' });
		return;
	}

	var formData = [];
	formData.push({ name: 'warehouse_id', value: warehouseId });
	formData.push({ name: 'batch_no', value: batchNo });
	formData.push({ name: 'mode', value: mode });

	$('#sa_products_tbody tr.sa-product-row').each(function () {
		var $row = $(this);
		saCalculateRow($row);
		var id = $row.find('.sa-product-select').val();
		var whiteQty = parseInt($row.find('.sa-white-qty').val(), 10) || 0;
		var blackQty = parseInt($row.find('.sa-black-qty').val(), 10) || 0;
		if (!id || (whiteQty <= 0 && blackQty <= 0)) {
			return;
		}
		if (mode === 'deduct') {
			formData.push({ name: 'inventory_id[]', value: id });
			if ($row.find('.sa-white-qty').prop('disabled')) whiteQty = 0;
			if ($row.find('.sa-black-qty').prop('disabled')) blackQty = 0;
		} else {
			formData.push({ name: 'product_id[]', value: id });
		}
		formData.push({ name: 'white_qty[]', value: whiteQty });
		formData.push({ name: 'black_qty[]', value: blackQty });
		formData.push({ name: 'rate[]', value: $row.find('.sa-rate').val() || 0 });
		formData.push({ name: 'per_qty_bill_amt[]', value: $row.find('.sa-per-qty-bill').val() || 0 });
		formData.push({ name: 'total_bill_amt[]', value: $row.find('.sa-total-bill').val() || 0 });
		formData.push({ name: 'gst_rate[]', value: $row.find('.sa-gst-rate').val() || 0 });
		formData.push({ name: 'gst_amt[]', value: $row.find('.sa-gst-amt').val() || 0 });
		formData.push({ name: 'total_bill_gst_amt[]', value: $row.find('.sa-total-bill-gst').val() || 0 });
		formData.push({ name: 'per_qty_black_amt[]', value: $row.find('.sa-per-qty-black').val() || 0 });
		formData.push({ name: 'total_black_amt[]', value: $row.find('.sa-total-black').val() || 0 });
		formData.push({ name: 'final_amt[]', value: $row.find('.sa-final-amt').val() || 0 });
	});

	var $btn = $('#sa_submit_btn');
	$btn.prop('disabled', true).text('Saving...');
	$.ajax({
		url: '<?php echo base_url('inventory/stock_adjustment_post'); ?>',
		type: 'POST',
		dataType: 'json',
		data: $.param(formData),
		success: function (res) {
			$btn.prop('disabled', false).text('Submit');
			if (res && res.status == 200) {
				saCloseModal();
				Swal.fire({ icon: 'success', title: 'Success', text: res.message || 'Stock adjusted successfully' });
				if (typeof dataTable !== 'undefined') {
					dataTable.ajax.reload(null, false);
				} else if ($('#report-datatable').length) {
					$('#report-datatable').DataTable().ajax.reload(null, false);
				}
			} else {
				Swal.fire({ icon: 'error', title: 'Error', text: (res && res.message) ? res.message : 'Failed to adjust stock' });
			}
		},
		error: function () {
			$btn.prop('disabled', false).text('Submit');
			Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed. Please try again.' });
		}
	});
});
</script>

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

    function getMainProductColWidth() {
        var $firstDataRow = $('#report-datatable_wrapper .dataTables_scrollBody table > tbody > tr:not(.child):not(.category-subheader-row)').first();
        if ($firstDataRow.length) {
            var $prodTd = $firstDataRow.children('td').eq(1);
            if ($prodTd.length && $prodTd.outerWidth() > 50) {
                return Math.floor($prodTd.outerWidth());
            }
        }
        var $prodTh = $('#report-datatable_wrapper .dataTables_scrollHead thead th').filter(function() {
            return $(this).text().trim().toLowerCase().indexOf('product') !== -1;
        });
        if ($prodTh.length && $prodTh.outerWidth() > 50) {
            return Math.floor($prodTh.outerWidth());
        }
        var scrollBody = document.querySelector('#report-datatable_wrapper .dataTables_scrollBody');
        var w = scrollBody ? Math.floor(scrollBody.clientWidth) : 1000;
        var fixedTotal = 0;
        var colWidthsMap = {
            0: 50, 2: 100, 3: 70, 4: 75, 5: 75, 6: 80, 7: 100, 8: 80,
            9: 70, 10: 80, 11: 80, 12: 100, 13: 100, 14: 100, 15: 100, 16: 55
        };
        try {
            if (typeof dataTable !== 'undefined' && dataTable) {
                dataTable.columns().every(function (idx) {
                    if (this.visible() && idx !== 1 && colWidthsMap[idx]) {
                        fixedTotal += colWidthsMap[idx];
                    }
                });
            } else {
                fixedTotal = 50 + 70 + 75 + 75 + 80 + 100 + 80 + 100 + 100 + 100 + 100 + 55;
            }
        } catch (e) {
            fixedTotal = 50 + 70 + 75 + 75 + 80 + 100 + 80 + 100 + 100 + 100 + 100 + 55;
        }
        var minProductWidth = 200;
        var tableWidth = Math.max(w, fixedTotal + minProductWidth);
        return Math.max(minProductWidth, tableWidth - fixedTotal);
    }

    function getChildNameColWidth() {
        var w = getMainProductColWidth();
        try {
            if (typeof dataTable !== 'undefined' && dataTable && dataTable.column(2).visible()) {
                var modelW = dataTable.column(2).header() ? $(dataTable.column(2).header()).outerWidth() : 100;
                w += (modelW || 100);
            }
        } catch (e) {}
        return w;
    }

    var currentStockMode = 'all';

    function formatChildRow(rowData) {
        var showZeroQty = $('#toggle-zero-qty').is(':checked');
        var allBatches = rowData.batches || [];
        var batches = showZeroQty ? allBatches : allBatches.filter(function (b) {
            if (currentStockMode === 'white') {
                return (Number(b.white_qty) > 0 || Number(b.pending_qty) > 0);
            } else if (currentStockMode === 'black') {
                return Number(b.black_qty) > 0;
            }
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
            var isWhiteMode = (currentStockMode === 'white');
            var isBlackMode = (currentStockMode === 'black');

            var showQuantity = (!isWhiteMode && !isBlackMode);
            var showBlackQty = !isWhiteMode;
            var showWhiteQty = !isBlackMode;
            var showPendingQty = !isBlackMode;
            var showTotalWhiteQty = !isBlackMode;

            var showBooked = (!isWhiteMode && !isBlackMode) && $('#toggle-booked-qty').is(':checked');
            var showPo = (!isWhiteMode && !isBlackMode) && $('#toggle-po-qty').is(':checked');
            var showActCost = $('#toggle-act-cost').is(':checked');
            var showActPc = $('#toggle-act-cost-pc').is(':checked');
            var showOffCost = $('#toggle-off-cost').is(':checked');
            var showOffPc = $('#toggle-off-cost-pc').is(':checked');

            var styleQty = showQuantity ? '' : 'style="display:none;"';
            var styleBlack = showBlackQty ? '' : 'style="display:none;"';
            var styleWhite = showWhiteQty ? '' : 'style="display:none;"';
            var stylePending = showPendingQty ? '' : 'style="display:none;"';
            var styleTotWhite = showTotalWhiteQty ? '' : 'style="display:none;"';
            var styleBooked = showBooked ? '' : 'style="display:none;"';
            var stylePo = showPo ? '' : 'style="display:none;"';
            var styleActCost = showActCost ? '' : 'style="display:none;"';
            var styleActPc = showActPc ? '' : 'style="display:none;"';
            var styleOffCost = showOffCost ? '' : 'style="display:none;"';
            var styleOffPc = showOffPc ? '' : 'style="display:none;"';

            var nameWidth = getChildNameColWidth();
            var totalTableWidth = 50 + nameWidth
                + (showQuantity ? 70 : 0)
                + (showBlackQty ? 75 : 0)
                + (showWhiteQty ? 75 : 0)
                + (showPendingQty ? 80 : 0)
                + (showTotalWhiteQty ? 100 : 0)
                + (showBooked ? 80 : 0)
                + (showPo ? 230 : 0)
                + (showActPc ? 200 : 0)
                + (showActCost ? 200 : 0)
                + (showOffPc ? 200 : 0)
                + (showOffCost ? 200 : 0);

            html += '<div class="table-responsive">';
            html += '  <table class="table sub-batch-table align-middle" style="width: ' + totalTableWidth + 'px; min-width: 100%; table-layout: fixed;">';
            html += '    <colgroup>';
            html += '      <col class="col-sub-sr" style="width: 50px;">';
            html += '      <col class="col-sub-name" style="width: ' + nameWidth + 'px;">';
            html += '      <col class="col-sub-qty" style="width: 70px;' + (showQuantity ? '' : 'display:none;') + '">';
            html += '      <col class="col-sub-black" style="width: 75px;' + (showBlackQty ? '' : 'display:none;') + '">';
            html += '      <col class="col-sub-white" style="width: 75px;' + (showWhiteQty ? '' : 'display:none;') + '">';
            html += '      <col class="col-sub-pending" style="width: 80px;' + (showPendingQty ? '' : 'display:none;') + '">';
            html += '      <col class="col-sub-totwhite" style="width: 100px;' + (showTotalWhiteQty ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-booked col-sub-booked" style="width: 80px;' + (showBooked ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-po" style="width: 70px;' + (showPo ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-po" style="width: 80px;' + (showPo ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-po" style="width: 80px;' + (showPo ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-act-pc" style="width: 100px;' + (showActPc ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-act-cost" style="width: 100px;' + (showActCost ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-act-pc" style="width: 100px;' + (showActPc ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-act-cost" style="width: 100px;' + (showActCost ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-off-pc" style="width: 100px;' + (showOffPc ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-off-cost" style="width: 100px;' + (showOffCost ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-off-pc" style="width: 100px;' + (showOffPc ? '' : 'display:none;') + '">';
            html += '      <col class="col-batch-off-cost" style="width: 100px;' + (showOffCost ? '' : 'display:none;') + '">';
            html += '    </colgroup>';
            html += '    <thead>';
            html += '      <tr>';
            html += '        <th class="text-center col-sub-sr" style="width: 50px;">#</th>';
            html += '        <th class="col-sub-name" style="width: ' + nameWidth + 'px;">Batch No</th>';
            html += '        <th class="text-end col-sub-qty" style="width: 70px;' + (showQuantity ? '' : 'display:none;') + '" ' + styleQty + '>Quantity</th>';
            html += '        <th class="text-end col-sub-black" style="width: 75px;' + (showBlackQty ? '' : 'display:none;') + '" ' + styleBlack + '>Black Qty</th>';
            html += '        <th class="text-end col-sub-white" style="width: 75px;' + (showWhiteQty ? '' : 'display:none;') + '" ' + styleWhite + '>White Qty</th>';
            html += '        <th class="text-end col-sub-pending" style="width: 80px;' + (showPendingQty ? '' : 'display:none;') + '" ' + stylePending + '>Pending Qty</th>';
            html += '        <th class="text-end col-sub-totwhite" style="width: 100px;' + (showTotalWhiteQty ? '' : 'display:none;') + '" ' + styleTotWhite + '>Total White Qty</th>';
            html += '        <th class="text-end col-batch-booked col-sub-booked" style="width: 80px;' + (showBooked ? '' : 'display:none;') + '" ' + styleBooked + '>Booked Qty</th>';
            html += '        <th class="text-end col-batch-po" style="width: 70px;' + (showPo ? '' : 'display:none;') + '" ' + stylePo + '>PO Qty</th>';
            html += '        <th class="text-end col-batch-po" style="width: 80px;' + (showPo ? '' : 'display:none;') + '" ' + stylePo + '>Priority Qty</th>';
            html += '        <th class="text-end col-batch-po" style="width: 80px;' + (showPo ? '' : 'display:none;') + '" ' + stylePo + '>Loading Qty</th>';
            html += '        <th class="text-end col-batch-act-pc" style="width: 100px;' + (showActPc ? '' : 'display:none;') + '" ' + styleActPc + '>Act Cost / exp</th>';
            html += '        <th class="text-end col-batch-act-cost" style="width: 100px;' + (showActCost ? '' : 'display:none;') + '" ' + styleActCost + '>Act Cost Exp</th>';
            html += '        <th class="text-end col-batch-act-pc" style="width: 100px;' + (showActPc ? '' : 'display:none;') + '" ' + styleActPc + '>Act Cost / Amt</th>';
            html += '        <th class="text-end col-batch-act-cost" style="width: 100px;' + (showActCost ? '' : 'display:none;') + '" ' + styleActCost + '>Act Cost Amt</th>';
            html += '        <th class="text-end col-batch-off-pc" style="width: 100px;' + (showOffPc ? '' : 'display:none;') + '" ' + styleOffPc + '>Off Cost / exp</th>';
            html += '        <th class="text-end col-batch-off-cost" style="width: 100px;' + (showOffCost ? '' : 'display:none;') + '" ' + styleOffCost + '>Off Cost Exp</th>';
            html += '        <th class="text-end col-batch-off-pc" style="width: 100px;' + (showOffPc ? '' : 'display:none;') + '" ' + styleOffPc + '>Off Cost / Amt</th>';
            html += '        <th class="text-end col-batch-off-cost" style="width: 100px;' + (showOffCost ? '' : 'display:none;') + '" ' + styleOffCost + '>Off Cost Amt</th>';
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
                html += '        <td class="text-center fw-bold text-muted col-sub-sr">' + b.sr_no + '</td>';
                html += '        <td class="col-sub-name"><span class="batch-no-tag">' + b.batch_no + '</span></td>';
                html += '        <td class="text-end stk-qty-main col-sub-qty" ' + styleQty + '>' + bQty.toLocaleString() + '</td>';
                html += '        <td class="text-end col-sub-black" ' + styleBlack + '>' + badgeBlack + '</td>';
                html += '        <td class="text-end col-sub-white" ' + styleWhite + '>' + badgeWhite + '</td>';
                html += '        <td class="text-end col-sub-pending" ' + stylePending + '>' + badgePending + '</td>';
                html += '        <td class="text-end col-sub-totwhite" ' + styleTotWhite + '>' + badgeTotWhite + '</td>';
                html += '        <td class="text-end col-batch-booked col-sub-booked" ' + styleBooked + '>' + badgeBooked + '</td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                html += '        <td class="text-end stk-cost col-batch-act-pc ' + (parseFloat(b.actual_cost_per_pc_with_exp) > 0 ? '' : 'stk-zero') + '" ' + styleActPc + '>₹' + b.actual_cost_per_pc_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-cost ' + (parseFloat(b.actual_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActCost + '>₹' + b.actual_cost_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-pc ' + (parseFloat(b.actual_cost_per_pc_net) > 0 ? '' : 'stk-zero') + '" ' + styleActPc + '>₹' + b.actual_cost_per_pc_net + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-cost ' + (parseFloat(b.actual_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActCost + '>₹' + b.actual_cost_net + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-pc ' + (parseFloat(b.official_cost_per_pc_with_exp) > 0 ? '' : 'stk-zero') + '" ' + styleOffPc + '>₹' + b.official_cost_per_pc_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-cost ' + (parseFloat(b.official_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffCost + '>₹' + b.official_cost_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-pc ' + (parseFloat(b.official_cost_per_pc_net) > 0 ? '' : 'stk-zero') + '" ' + styleOffPc + '>₹' + b.official_cost_per_pc_net + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-cost ' + (parseFloat(b.official_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffCost + '>₹' + b.official_cost_net + '</td>';
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
        function renderCategorySubheaders(api) {
            var dt = api || dataTable;
            if (!dt) {
                try {
                    dt = $('#report-datatable').DataTable();
                } catch (e) {
                    return;
                }
            }
            var rows = dt.rows({ page: 'current' }).nodes();
            if (!rows || !rows.length) return;
            var lastCategory = null;
            var visibleCols = 0;
            dt.columns().every(function () {
                if (this.visible()) visibleCols++;
            });
            if (visibleCols === 0) visibleCols = 17;

            $('#report-datatable_wrapper .dataTables_scrollBody tbody tr.category-subheader-row, #report-datatable tbody tr.category-subheader-row, tr.category-subheader-row').remove();

            dt.rows({ page: 'current' }).every(function (rowIdx, tableLoop, rowLoop) {
                var rowData = this.data();
                if (!rowData) return;
                var categoryName = rowData.category_name || 'Uncategorized';

                if (lastCategory !== categoryName) {
                    var safeCategoryName = $('<div>').text(categoryName).html();
                    var subheaderHtml = '<tr class="category-subheader-row">' +
                        '<td colspan="' + visibleCols + '" class="category-header-cell">' +
                            '<div class="category-header-wrap">' +
                                '<span class="category-header-icon"><i class="feather icon-folder"></i></span>' +
                                '<span class="category-header-title">' + safeCategoryName + '</span>' +
                            '</div>' +
                        '</td>' +
                    '</tr>';
                    $(rows).eq(rowLoop).before(subheaderHtml);
                    lastCategory = categoryName;
                }
            });
        }

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
                searchPlaceholder: "Search product name, category, batch no...",
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

                // Render Category Subheaders
                renderCategorySubheaders(this.api());

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
                       data.stock_view_mode = currentStockMode;
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                { "data": "sr_no", "className": "text-center text-nowrap align-middle", "width": "50px" },
                { "data": "product_name", "className": "text-start align-middle" },
                { "data": "model_no", "className": "text-start text-nowrap align-middle", "visible": false, "width": "100px" },
                { "data": "quantity", "className": "text-end text-nowrap align-middle", "width": "70px" },
                { "data": "black_qty", "className": "text-end text-nowrap align-middle", "width": "75px" },
                { "data": "white_qty", "className": "text-end text-nowrap align-middle", "width": "75px" },
                { "data": "pending_qty", "className": "text-end text-nowrap align-middle", "width": "80px" },
                { "data": "total_white_qty", "className": "text-end text-nowrap align-middle", "width": "100px" },
                { "data": "booked_qty", "className": "text-end text-nowrap align-middle", "width": "80px" },
                { "data": "po_qty", "className": "text-end text-nowrap align-middle", "visible": false, "width": "70px" },
                { "data": "priority_qty", "className": "text-end text-nowrap align-middle", "visible": false, "width": "80px" },
                { "data": "loading_qty", "className": "text-end text-nowrap align-middle", "visible": false, "width": "80px" },
                { "data": "actual_cost_with_exp", "className": "text-end text-nowrap align-middle", "width": "100px" },
                { "data": "actual_cost_net", "className": "text-end text-nowrap align-middle", "width": "100px" },
                { "data": "official_cost_with_exp", "className": "text-end text-nowrap align-middle", "width": "100px" },
                { "data": "official_cost_net", "className": "text-end text-nowrap align-middle", "width": "100px" },
                { "data": "action", "className": "text-center text-nowrap align-middle", "width": "55px" }
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

        $('#report-datatable_wrapper .dataTables_scrollBody').on('scroll', function () {
            var head = document.querySelector('#report-datatable_wrapper .dataTables_scrollHead');
            if (head) {
                head.scrollLeft = this.scrollLeft;
            }
        });

        var COLUMN_WIDTHS = {
            0: 50,   // # (sr_no)
            1: null, // Product Name (flex - takes all remaining space)
            2: 100,  // Model No
            3: 70,   // Quantity
            4: 75,   // Black Qty
            5: 75,   // White Qty
            6: 80,   // Pending Qty
            7: 100,  // Total White Qty
            8: 80,   // Booked Qty
            9: 70,   // PO Qty
            10: 80,  // Priority Qty
            11: 80,  // Loading Qty
            12: 100, // Act Cost Exp
            13: 100, // Act Cost Amt
            14: 100, // Off Cost Exp
            15: 100, // Off Cost Amt
            16: 55   // Action
        };

        // Stretch parent table flush to the vertical scrollbar edge with compact columns
        function fitMainTableWidth() {
            var scrollBody = document.querySelector('#report-datatable_wrapper .dataTables_scrollBody');
            if (!scrollBody) return;
            var w = Math.floor(scrollBody.clientWidth);
            if (w < 200) return;

            // Determine visible column indices
            var visibleColIdxs = [];
            dataTable.columns().every(function (idx) {
                if (this.visible()) {
                    visibleColIdxs.push(idx);
                }
            });

            // Calculate total fixed width required by all visible non-product columns
            var fixedTotal = 0;
            visibleColIdxs.forEach(function (idx) {
                if (COLUMN_WIDTHS[idx]) {
                    fixedTotal += COLUMN_WIDTHS[idx];
                }
            });

            var minProductWidth = 200;
            var minTableWidth = fixedTotal + minProductWidth;
            var tableWidth = Math.max(w, minTableWidth);

            var needsHScroll = minTableWidth > w;
            scrollBody.style.setProperty('overflow-x', needsHScroll ? 'auto' : 'hidden', 'important');

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
                node.style.setProperty('width', tableWidth + 'px', 'important');
                node.style.setProperty('min-width', tableWidth + 'px', 'important');
                node.style.setProperty('max-width', tableWidth + 'px', 'important');
                node.style.setProperty('padding-right', '0px', 'important');
                node.style.setProperty('box-sizing', 'border-box', 'important');
                node.style.setProperty('margin', '0px', 'important');
            });

            // Set explicit compact widths on each <col>, letting product_name fill the remainder
            $('#report-datatable_wrapper colgroup').each(function () {
                $(this).find('col').each(function (i) {
                    var colIdx = visibleColIdxs[i];
                    var pxWidth = (colIdx !== undefined && COLUMN_WIDTHS[colIdx]) ? COLUMN_WIDTHS[colIdx] : null;
                    if (pxWidth) {
                        this.style.setProperty('width', pxWidth + 'px', 'important');
                    } else {
                        this.style.setProperty('width', 'auto', 'important');
                    }
                });
            });

            syncChildColumnWidths();
        }

        function syncChildColumnWidths() {
            var nameWidth = getChildNameColWidth();
            $('.sub-batch-table, .sub-company-table').each(function () {
                var $tbl = $(this);
                $tbl.find('col.col-sub-name').css('width', nameWidth + 'px');
                $tbl.find('thead tr').first().children('.col-sub-name').css('width', nameWidth + 'px');
                var tot = 0;
                $tbl.find('thead tr').first().children('th').each(function () {
                    if ($(this).is(':visible') && $(this).css('display') !== 'none') {
                        var w = parseFloat($(this).css('width')) || parseFloat(this.style.width) || $(this).outerWidth();
                        if (w > 0) tot += w;
                    }
                });
                if (tot > 0) {
                    $tbl.css('width', tot + 'px');
                }
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
            $td.attr('style', 'padding:0!important;border:none!important;background:transparent!important;');
            $wrap.attr('style',
                'display:block;width:0;min-width:100%;max-width:100%;' +
                'overflow-x:auto;overflow-y:hidden;box-sizing:border-box;padding:0;margin:0;-webkit-overflow-scrolling:touch;'
            );
            syncChildColumnWidths();
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
                        if (currentStockMode === 'white') {
                            return (Number(b.white_qty) > 0 || Number(b.pending_qty) > 0);
                        } else if (currentStockMode === 'black') {
                            return Number(b.black_qty) > 0;
                        }
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
            applyColumnFilters();
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
        function updateFilterCount() {
            // Count only deviations from default so the badge stays hidden until filters change
            var defaults = {
                'toggle-stock-batch': false,
                'toggle-zero-qty': false,
                'toggle-product-col': true,
                'toggle-model-col': false,
                'toggle-booked-qty': true,
                'toggle-po-qty': false,
                'toggle-act-cost': true,
                'toggle-act-cost-pc': true,
                'toggle-off-cost': true,
                'toggle-off-cost-pc': true
            };
            var count = 0;
            $.each(defaults, function (id, defaultOn) {
                if ($('#' + id).is(':checked') !== defaultOn) {
                    count++;
                }
            });
            var $badge = $('#filter-active-count');
            $badge.text(count);
            $badge.toggleClass('has-count', count > 0);
        }

        function applyColumnFilters() {
            var isWhiteMode = (currentStockMode === 'white');
            var isBlackMode = (currentStockMode === 'black');

            var showProduct = $('#toggle-product-col').is(':checked');
            var showModel = $('#toggle-model-col').is(':checked');

            var showQuantity = (!isWhiteMode && !isBlackMode);
            var showBlackQty = !isWhiteMode;
            var showWhiteQty = !isBlackMode;
            var showPendingQty = !isBlackMode;
            var showTotalWhiteQty = !isBlackMode;

            var showBooked = (!isWhiteMode && !isBlackMode) && $('#toggle-booked-qty').is(':checked');
            var showPo = (!isWhiteMode && !isBlackMode) && $('#toggle-po-qty').is(':checked');
            var showActCost = $('#toggle-act-cost').is(':checked');
            var showActPc = $('#toggle-act-cost-pc').is(':checked');
            var showOffCost = $('#toggle-off-cost').is(':checked');
            var showOffPc = $('#toggle-off-cost-pc').is(':checked');

            // Disable Booked & PO toggles in dropdown when on White or Black tab
            var disableBookedPo = (isWhiteMode || isBlackMode);
            $('#toggle-booked-qty').prop('disabled', disableBookedPo).closest('.filter-chip').toggleClass('disabled-chip', disableBookedPo);
            $('#toggle-po-qty').prop('disabled', disableBookedPo).closest('.filter-chip').toggleClass('disabled-chip', disableBookedPo);

            // Toggle main table columns without reload
            dataTable.column(1).visible(showProduct, false);
            dataTable.column(2).visible(showModel, false);
            dataTable.column(3).visible(showQuantity, false);
            dataTable.column(4).visible(showBlackQty, false);
            dataTable.column(5).visible(showWhiteQty, false);
            dataTable.column(6).visible(showPendingQty, false);
            dataTable.column(7).visible(showTotalWhiteQty, false);
            dataTable.column(8).visible(showBooked, false);
            dataTable.column(9).visible(showPo, false);
            dataTable.column(10).visible(showPo, false);
            dataTable.column(11).visible(showPo, false);
            dataTable.column(12).visible(showActCost, false);
            dataTable.column(13).visible(showActCost, false);
            dataTable.column(14).visible(showOffCost, false);
            dataTable.column(15).visible(showOffCost, false);
            dataTable.columns.adjust();
            renderCategorySubheaders(dataTable);
            fitMainTableWidth();
            requestAnimationFrame(function () {
                fitMainTableWidth();
            });

            // Toggle columns in any currently expanded sub-batch tables
            $('.sub-batch-table .col-sub-qty, .sub-batch-table col.col-sub-qty').toggle(showQuantity);
            $('.sub-batch-table .col-sub-black, .sub-batch-table col.col-sub-black').toggle(showBlackQty);
            $('.sub-batch-table .col-sub-white, .sub-batch-table col.col-sub-white').toggle(showWhiteQty);
            $('.sub-batch-table .col-sub-pending, .sub-batch-table col.col-sub-pending').toggle(showPendingQty);
            $('.sub-batch-table .col-sub-totwhite, .sub-batch-table col.col-sub-totwhite').toggle(showTotalWhiteQty);

            $('.sub-batch-table .col-batch-booked, .sub-batch-table col.col-batch-booked').toggle(showBooked);
            $('.sub-batch-table .col-batch-po, .sub-batch-table col.col-batch-po').toggle(showPo);
            $('.sub-batch-table .col-batch-act-cost, .sub-batch-table col.col-batch-act-cost').toggle(showActCost);
            $('.sub-batch-table .col-batch-act-pc, .sub-batch-table col.col-batch-act-pc').toggle(showActPc);
            $('.sub-batch-table .col-batch-off-cost, .sub-batch-table col.col-batch-off-cost').toggle(showOffCost);
            $('.sub-batch-table .col-batch-off-pc, .sub-batch-table col.col-batch-off-pc').toggle(showOffPc);
            syncChildColumnWidths();
            fitChildScrollPanels();

            updateFilterCount();
        }

        // Pill Navigation Handler
        $('.stock-pill-btn').on('click', function () {
            var mode = $(this).attr('data-stock-mode');
            if (mode === currentStockMode) return;

            currentStockMode = mode;
            $('.stock-pill-btn').removeClass('active');
            $(this).addClass('active');

            $('.stock-table-card').removeClass('stock-mode-all stock-mode-white stock-mode-black').addClass('stock-mode-' + mode);

            applyColumnFilters();
            dataTable.ajax.reload(null, false);
        });

        // Stock with Batch Toggle Handler
        $('#toggle-stock-batch').on('change', function () {
            var isChecked = $(this).is(':checked');
            $(this).closest('.filter-chip').toggleClass('active', isChecked);
            updateFilterCount();
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
            updateFilterCount();
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
            e.stopPropagation();
            $('#toggle-stock-batch').prop('checked', false).closest('.filter-chip').removeClass('active');
            collapseAllBatches();

            var zeroWasChecked = $('#toggle-zero-qty').is(':checked');
            $('#toggle-zero-qty').prop('checked', false).closest('.filter-chip').removeClass('active');

            $('#toggle-product-col').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-model-col').prop('checked', false).closest('.filter-chip').removeClass('active');
            $('#toggle-booked-qty').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-po-qty').prop('checked', false).closest('.filter-chip').removeClass('active');
            $('#toggle-act-cost').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-act-cost-pc').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-off-cost').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-off-cost-pc').prop('checked', true).closest('.filter-chip').addClass('active');
            applyColumnFilters();

            if (zeroWasChecked) {
                dataTable.ajax.reload();
            }
        });

        updateFilterCount();

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
                    if (currentStockMode === 'white') {
                        return (Number(b.white_qty) > 0 || Number(b.pending_qty) > 0);
                    } else if (currentStockMode === 'black') {
                        return Number(b.black_qty) > 0;
                    }
                    return Number(b.quantity) > 0;
                });
                if (activeBatches.length > 0) {
                    openChildRow(row, tr, rowData);
                    icon.removeClass('icon-plus').addClass('icon-minus');
                    $('[data-toggle="tooltip"]').tooltip();
                    applyColumnFilters();
                }
            }
        });
    });
</script>