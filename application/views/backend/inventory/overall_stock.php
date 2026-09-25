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

	/* Column & Data Filter Toolbar — Dropdown */
	.stock-filter-toolbar {
		background: #ffffff;
		padding: 8px 14px;
		border-bottom: 1px solid #f1f5f9;
		display: flex;
		align-items: center;
		justify-content: flex-start;
	}
	.btn-filter-dropdown {
		display: inline-flex;
		align-items: center;
		gap: 7px;
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 7px;
		color: #334155;
		font-size: 12.5px;
		font-weight: 600;
		padding: 6px 12px;
		cursor: pointer;
		transition: all 0.15s ease;
		box-shadow: 0 1px 2px rgba(0,0,0,0.03);
	}
	.btn-filter-dropdown:hover,
	.btn-filter-dropdown:focus,
	.btn-filter-dropdown.show {
		background: #f8fafc;
		border-color: #cbd5e1;
		color: #0f172a;
		outline: none;
		box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08);
	}
	.btn-filter-dropdown .feather {
		font-size: 14px;
		color: #64748b;
	}
	.btn-filter-dropdown::after {
		margin-left: 2px;
		border-top-color: #94a3b8;
	}
	.filter-count-badge {
		display: none;
		min-width: 18px;
		height: 18px;
		padding: 0 5px;
		border-radius: 999px;
		background: #16a34a;
		color: #ffffff;
		font-size: 10px;
		font-weight: 700;
		line-height: 18px;
		text-align: center;
	}
	.filter-count-badge.has-count {
		display: inline-block;
	}
	.stock-filter-menu {
		min-width: 280px;
		max-width: 320px;
		padding: 0;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		box-shadow: 0 10px 30px -8px rgba(15, 23, 42, 0.18);
		overflow: hidden;
		margin-top: 6px !important;
	}
	.filter-menu-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 14px;
		background: #f8fafc;
		border-bottom: 1px solid #f1f5f9;
	}
	.filter-menu-header .title {
		font-size: 12px;
		font-weight: 700;
		color: #0f172a;
		letter-spacing: 0.2px;
	}
	.filter-menu-body {
		max-height: 340px;
		overflow-y: auto;
		padding: 8px 0;
	}
	.filter-menu-label {
		font-size: 10px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		color: #94a3b8;
		padding: 6px 14px 4px;
	}
	.filter-chip {
		cursor: pointer;
		margin: 0;
		user-select: none;
		display: flex;
		align-items: center;
		width: 100%;
		padding: 8px 14px;
		border-radius: 0;
		font-size: 12.5px;
		font-weight: 500;
		color: #475569;
		background: transparent;
		border: none;
		transition: background 0.12s ease, color 0.12s ease;
		gap: 10px;
	}
	.filter-chip:hover {
		background: #f8fafc;
		color: #0f172a;
	}
	.filter-chip.active {
		background: #f0fdf4;
		color: #15803d;
		font-weight: 600;
	}
	.filter-chip input[type="checkbox"] {
		display: none;
	}
	.filter-chip .chip-box {
		width: 15px;
		height: 15px;
		border-radius: 4px;
		border: 1.5px solid #cbd5e1;
		background: #ffffff;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 10px;
		color: transparent;
		transition: all 0.15s ease;
		flex-shrink: 0;
	}
	.filter-chip.active .chip-box {
		background: #16a34a;
		border-color: #16a34a;
		color: #ffffff;
	}
	.filter-chip .chip-text {
		flex: 1;
		line-height: 1.3;
	}
	.filter-menu-footer {
		padding: 8px 12px;
		border-top: 1px solid #f1f5f9;
		background: #ffffff;
	}
	.btn-reset-filters {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		color: #64748b;
		font-size: 12px;
		font-weight: 600;
		padding: 6px 10px;
		cursor: pointer;
		transition: all 0.15s ease;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 100%;
		gap: 6px;
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
	.dataTables_scrollBody.has-open-child {
		overflow-x: hidden !important;
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
	 * width:0 + min-width:100% takes parent table width without letting
	 * wide nested tables expand the DataTables body table.
	 */
	.child-scroll-wrap,
	.nested-child-scroll {
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
	.nested-child-scroll {
		padding: 2px 0 4px;
	}
	.child-scroll-wrap .company-breakdown-card {
		display: block;
		width: max-content;
		min-width: 100%;
		max-width: none !important;
		margin: 0;
		box-sizing: border-box;
		overflow: visible !important;
	}
	.child-scroll-wrap .company-breakdown-card > .table-responsive {
		display: block !important;
		width: max-content !important;
		min-width: 100% !important;
		max-width: none !important;
		overflow: visible !important;
	}
	/* Company table sizes to its own columns; nested batch scrolls separately */
	.child-scroll-wrap .sub-company-table {
		width: max-content !important;
		min-width: 100%;
		margin-bottom: 0;
		table-layout: auto;
	}
	.nested-child-scroll .batch-breakdown-card {
		display: block;
		width: max-content;
		min-width: 100%;
		max-width: none !important;
		margin: 0;
		box-sizing: border-box;
		overflow: visible !important;
	}
	.nested-child-scroll .table-responsive,
	.nested-child-scroll .batch-breakdown-card > .table-responsive {
		display: block !important;
		width: max-content !important;
		min-width: 100% !important;
		max-width: none !important;
		overflow: visible !important;
	}
	.nested-child-scroll .sub-batch-table {
		width: max-content !important;
		min-width: 100%;
		margin-bottom: 0;
	}
	tr.company-batches-row > td {
		padding: 0 !important;
		border: none !important;
		background: transparent !important;
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
	.btn-expand-row, .btn-expand-company-row {
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
	.btn-expand-row:hover, .btn-expand-company-row:hover {
		background: #f1f5f9;
		color: #0f172a;
		border-color: #94a3b8;
	}
	tr.shown .btn-expand-row, tr.company-shown .btn-expand-company-row {
		background: #fee2e2;
		color: #dc2626;
		border-color: #fca5a5;
	}
	.btn-expand-row.disabled, .btn-expand-company-row.disabled {
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

	/* Layer 2: Company Breakdown Card */
	.company-breakdown-card {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 8px 12px;
		margin: 0;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
		box-sizing: border-box;
	}
	.company-breakdown-card > .table-responsive {
		overflow: visible;
		max-width: none;
	}
	.company-card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 6px;
		padding-bottom: 6px;
		border-bottom: 1px solid #e2e8f0;
	}
	.company-tag-icon {
		width: 20px;
		height: 20px;
		background: #f0fdf4;
		color: #15803d;
		border-radius: 4px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 11px;
	}
	.company-title {
		font-size: 12px;
		font-weight: 700;
		color: #1e293b;
	}
	.company-prod-name {
		font-size: 12px;
		font-weight: 500;
		color: #64748b;
	}
	.badge-company-count {
		background: #ffffff;
		border: 1px solid #cbd5e1;
		color: #475569;
		font-size: 11px;
		font-weight: 600;
		padding: 1px 7px;
		border-radius: 12px;
	}
	.sub-company-table {
		background: #ffffff;
		/* border: 1px solid #e2e8f0; */
		border-radius: 6px;
		overflow: hidden;
		margin-bottom: 0;
		width: max-content !important;
		min-width: 100%;
	}
	.sub-company-table thead th {
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
	.sub-company-table tbody td {
		padding: 5px 8px !important;
		font-size: 11.5px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		vertical-align: middle !important;
		white-space: nowrap !important;
	}
	.sub-company-table tbody tr.company-row:hover {
		background-color: #f8fafc !important;
	}
	.company-name-tag {
		font-weight: 600;
		color: #1e293b;
		font-size: 12px;
	}
	.warehouse-name-tag {
		color: #64748b;
		font-size: 11px;
	}

	/* Layer 3: Batch Breakdown Card */
	.batch-breakdown-card {
		background: #f1f5f9;
		border: 1px solid #cbd5e1;
		border-radius: 6px;
		padding: 8px 10px;
		margin: 4px 6px;
		box-sizing: border-box;
		overflow: visible;
	}
	.batch-breakdown-card > .table-responsive {
		overflow: visible;
		max-width: none;
	}
	.batch-card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 5px;
		padding-bottom: 4px;
		border-bottom: 1px solid #cbd5e1;
	}
	.batch-tag-icon {
		width: 18px;
		height: 18px;
		background: #eef2ff;
		color: #4f46e5;
		border-radius: 4px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 10px;
	}
	.batch-title {
		font-size: 11.5px;
		font-weight: 700;
		color: #334155;
	}
	.badge-batch-count {
		background: #ffffff;
		border: 1px solid #cbd5e1;
		color: #475569;
		font-size: 10.5px;
		font-weight: 600;
		padding: 1px 6px;
		border-radius: 10px;
	}
	.sub-batch-table {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 5px;
		overflow: hidden;
		margin-bottom: 0;
		width: max-content !important;
		min-width: 100%;
	}
	.sub-batch-table thead th {
		background: #e2e8f0 !important;
		color: #475569 !important;
		font-size: 10px !important;
		font-weight: 600 !important;
		text-transform: uppercase !important;
		letter-spacing: 0.3px !important;
		padding: 5px 6px !important;
		border-bottom: 1px solid #cbd5e1 !important;
		border-top: none !important;
		white-space: nowrap !important;
	}
	.sub-batch-table tbody td {
		padding: 4px 6px !important;
		font-size: 11px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		vertical-align: middle !important;
		white-space: nowrap !important;
	}
	.batch-no-tag {
		font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
		font-size: 10.5px;
		font-weight: 600;
		color: #0284c7;
		background: #f0f9ff;
		border: 1px solid #e0f2fe;
		padding: 1px 5px;
		border-radius: 3px;
		display: inline-block;
	}
	.btn-micro-action {
		width: 20px;
		height: 20px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 3px;
		font-size: 10px;
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

<div class="row inventory-page-wrapper" id="table-bordered">
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
						<div class="kpi-label">Overall White Qty</div>
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
						<div class="kpi-label">Overall Black Qty</div>
						<div class="kpi-value" id="total_black_stat"><?php echo number_format($total['black_qty'] ?? 0); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Main Data Table -->
    <div class="col-12">
		<div class="stock-table-card">
			<!-- Column Visibility & Data Filter Dropdown -->
			<div class="stock-filter-toolbar">
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
							<label class="filter-chip" for="toggle-stock-batch" title="Expand or collapse all products with companies and batches">
								<input type="checkbox" id="toggle-stock-batch">
								<span class="chip-box"><i class="feather icon-check"></i></span>
								<span class="chip-text">Stock with Batch</span>
							</label>
							<label class="filter-chip" for="toggle-zero-qty" title="Show products, companies and batches with zero quantity">
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
						<div class="filter-menu-footer">
							<button type="button" class="btn-reset-filters" id="btn-reset-col-filters" title="Reset column filters to default">
								<i class="feather icon-rotate-ccw"></i> Reset Filters
							</button>
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
							<th class="text-end th-cost-wrap">Actual Cost<br>with Exp</th>
							<th class="text-end th-cost-wrap">Actual Cost<br>Net Amt</th>
							<th class="text-end th-cost-wrap">Official Cost<br>with Exp</th>
							<th class="text-end th-cost-wrap">Official Cost<br>Net Amt</th>
							<!-- Action column hidden on main overall stock table -->
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
    var BASE_URL = '<?php echo base_url(); ?>';
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

    function formatProductChildRow(rowData) {
        var showZeroQty = $('#toggle-zero-qty').is(':checked');
        var allCompanies = rowData.companies || [];
        var companies = showZeroQty ? allCompanies : allCompanies.filter(function (c) {
            return Number(c.quantity) > 0;
        });

        var html = '<div class="child-scroll-wrap">';
        html += '<div class="company-breakdown-card">';
        html += '<div class="company-card-header">';
        html += '  <div class="d-flex align-items-center gap-1">';
        html += '    <span class="company-tag-icon"><i class="feather icon-briefcase"></i></span>';
        html += '    <span class="company-title">Company Stock Breakdown</span>';
        html += '    <span class="company-prod-name ms-1">— ' + (rowData.raw_product_name || '') + '</span>';
        html += '  </div>';
        html += '  <span class="badge-company-count">' + companies.length + ' ' + (companies.length === 1 ? 'company' : 'companies') + '</span>';
        html += '</div>';

        if (companies.length === 0) {
            html += '<div class="text-center py-2 text-muted font-small-2">' + (showZeroQty ? 'No company stock records for this product.' : 'No active companies with stock for this product.') + '</div>';
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
            html += '  <table class="table sub-company-table align-middle">';
            html += '    <thead>';
            html += '      <tr>';
            html += '        <th class="text-center" style="width: 45px;">#</th>';
            html += '        <th>Company / Warehouse</th>';
            html += '        <th class="text-end">Quantity</th>';
            html += '        <th class="text-end">Black Qty</th>';
            html += '        <th class="text-end">White Qty</th>';
            html += '        <th class="text-end">Pending Qty</th>';
            html += '        <th class="text-end">Total White Qty</th>';
            html += '        <th class="text-end col-batch-booked" ' + styleBooked + '>Booked Qty</th>';
            html += '        <th class="text-end col-batch-po" ' + stylePo + '>PO Qty</th>';
            html += '        <th class="text-end col-batch-po" ' + stylePo + '>Priority Qty</th>';
            html += '        <th class="text-end col-batch-po" ' + stylePo + '>Loading Qty</th>';
            html += '        <th class="text-end col-batch-act-exp" ' + styleActExp + '>Actual Cost with Exp</th>';
            html += '        <th class="text-end col-batch-act-amt" ' + styleActAmt + '>Actual Cost Net Amt</th>';
            html += '        <th class="text-end col-batch-off-exp" ' + styleOffExp + '>Official Cost with Exp</th>';
            html += '        <th class="text-end col-batch-off-amt" ' + styleOffAmt + '>Official Cost Net Amt</th>';
            html += '        <th class="text-center" style="width: 70px;">Action</th>';
            html += '      </tr>';
            html += '    </thead>';
            html += '    <tbody>';

            for (var cIdx = 0; cIdx < companies.length; cIdx++) {
                var c = companies[cIdx];
                var cQty = Number(c.quantity);
                var cBlack = Number(c.black_qty);
                var cWhite = Number(c.white_qty);
                var cPending = Number(c.pending_qty);
                var cTotWhite = Number(c.total_white_qty);
                var cBooked = Number(c.booked_qty);
                var cPo = Number(c.po_qty);
                var cPriority = Number(c.priority_qty);
                var cLoading = Number(c.loading_qty);

                var badgeBlack = cBlack > 0 ? '<span class="stk-badge stk-badge-black">' + cBlack.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgeWhite = cWhite > 0 ? '<span class="stk-badge stk-badge-white">' + cWhite.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgePending = cPending > 0 ? '<span class="stk-badge stk-badge-pending">' + cPending.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgeTotWhite = cTotWhite > 0 ? '<span class="stk-badge stk-badge-total-white">' + cTotWhite.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                var badgeBooked = cBooked > 0 ? '<a href="javascript:void(0);" onclick="showProductBookedList(' + rowData.product_id + ', ' + c.company_id + ')" class="stk-badge stk-badge-booked">' + cBooked.toLocaleString() + '</a>' : '<span class="stk-badge-zero">-</span>';
                var badgePo = cPo > 0 ? '<a href="javascript:void(0);" onclick="showProductPOList(' + rowData.product_id + ', ' + c.company_id + ', \'pending\', \'' + c.warehouse_id + '\')" class="stk-badge stk-badge-po">' + cPo.toLocaleString() + '</a>' : '<span class="stk-badge-zero">-</span>';
                var badgePriority = cPriority > 0 ? '<a href="javascript:void(0);" onclick="showProductPOList(' + rowData.product_id + ', ' + c.company_id + ', \'priority\', \'' + c.warehouse_id + '\')" class="stk-badge stk-badge-priority">' + cPriority.toLocaleString() + '</a>' : '<span class="stk-badge-zero">-</span>';
                var badgeLoading = cLoading > 0 ? '<a href="javascript:void(0);" onclick="showProductPOList(' + rowData.product_id + ', ' + c.company_id + ', \'loading\', \'' + c.warehouse_id + '\')" class="stk-badge stk-badge-loading">' + cLoading.toLocaleString() + '</a>' : '<span class="stk-badge-zero">-</span>';

                var batches = showZeroQty ? (c.batches || []) : (c.batches || []).filter(function (b) {
                    return Number(b.quantity) > 0;
                });
                var hasBatches = batches.length > 0;
                var expandCompanyBtn = '<button type="button" class="btn-expand-company-row ' + (!hasBatches ? 'disabled' : '') + '" title="' + (hasBatches ? 'Click to toggle batches' : 'No batches') + '"><i class="feather icon-plus"></i></button>';

                html += '      <tr class="company-row">';
                html += '        <td class="text-center">';
                html += '          <div class="d-flex align-items-center justify-content-center">';
                html += '            ' + expandCompanyBtn;
                html += '            <span class="stk-sr-num ms-1">' + c.sr_no + '</span>';
                html += '          </div>';
                html += '        </td>';
                html += '        <td>';
                html += '          <div class="d-flex flex-column">';
                html += '            <span class="company-name-tag">' + (c.company_name || 'Unknown') + '</span>';
                html += '            <span class="warehouse-name-tag"><i class="feather icon-map-pin font-small-1 me-1"></i>' + (c.warehouse_name || 'Main Warehouse') + '</span>';
                html += '          </div>';
                html += '        </td>';
                html += '        <td class="text-end stk-qty-main">' + cQty.toLocaleString() + '</td>';
                html += '        <td class="text-end">' + badgeBlack + '</td>';
                html += '        <td class="text-end">' + badgeWhite + '</td>';
                html += '        <td class="text-end">' + badgePending + '</td>';
                html += '        <td class="text-end">' + badgeTotWhite + '</td>';
                html += '        <td class="text-end col-batch-booked" ' + styleBooked + '>' + badgeBooked + '</td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '>' + badgePo + '</td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '>' + badgePriority + '</td>';
                html += '        <td class="text-end col-batch-po" ' + stylePo + '>' + badgeLoading + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-exp ' + (parseFloat(c.actual_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActExp + '>₹' + c.actual_cost_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-act-amt ' + (parseFloat(c.actual_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActAmt + '>₹' + c.actual_cost_net + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-exp ' + (parseFloat(c.official_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffExp + '>₹' + c.official_cost_with_exp + '</td>';
                html += '        <td class="text-end stk-cost col-batch-off-amt ' + (parseFloat(c.official_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffAmt + '>₹' + c.official_cost_net + '</td>';
                html += '        <td class="text-center">';
                html += '          <div class="d-inline-flex align-items-center">';
                html += '            <a href="' + BASE_URL + 'inventory/stock-history/' + rowData.product_id + '" class="btn-table-action btn-action-view" data-toggle="tooltip" data-bs-placement="top" title="View History"><i class="feather icon-clock"></i></a>';
                html += '          </div>';
                html += '        </td>';
                html += '      </tr>';

                // Nested Layer 3: Batches Row under this Company
                html += '      <tr class="company-batches-row" style="display: none;">';
                html += '        <td colspan="16" class="p-0 border-0">';
                html += '          <div class="nested-child-scroll">';
                html += '          <div class="batch-breakdown-card">';
                html += '            <div class="batch-card-header">';
                html += '              <div class="d-flex align-items-center gap-1">';
                html += '                <span class="batch-tag-icon"><i class="feather icon-layers"></i></span>';
                html += '                <span class="batch-title">Batch Breakdown — ' + (c.company_name || '') + ' (' + (c.warehouse_name || '') + ')</span>';
                html += '              </div>';
                html += '              <span class="badge-batch-count">' + batches.length + ' ' + (batches.length === 1 ? 'batch' : 'batches') + '</span>';
                html += '            </div>';

                if (batches.length === 0) {
                    html += '        <div class="text-center py-2 text-muted font-small-2">No individual batches recorded for this company.</div>';
                } else {
                    html += '        <div class="table-responsive">';
                    html += '          <table class="table sub-batch-table align-middle">';
                    html += '            <thead>';
                    html += '              <tr>';
                    html += '                <th class="text-center" style="width: 35px;">#</th>';
                    html += '                <th>Batch No</th>';
                    html += '                <th class="text-end">Quantity</th>';
                    html += '                <th class="text-end">Black Qty</th>';
                    html += '                <th class="text-end">White Qty</th>';
                    html += '                <th class="text-end">Pending Qty</th>';
                    html += '                <th class="text-end">Total White Qty</th>';
                    html += '                <th class="text-end col-batch-booked" ' + styleBooked + '>Booked Qty</th>';
                    html += '                <th class="text-end col-batch-po" ' + stylePo + '>PO Qty</th>';
                    html += '                <th class="text-end col-batch-po" ' + stylePo + '>Priority Qty</th>';
                    html += '                <th class="text-end col-batch-po" ' + stylePo + '>Loading Qty</th>';
                    html += '                <th class="text-end col-batch-act-exp" ' + styleActExp + '>Actual Cost Per Pc with Exp</th>';
                    html += '                <th class="text-end col-batch-act-exp" ' + styleActExp + '>Actual Cost with Exp</th>';
                    html += '                <th class="text-end col-batch-act-amt" ' + styleActAmt + '>Actual Cost Per Pc Net</th>';
                    html += '                <th class="text-end col-batch-act-amt" ' + styleActAmt + '>Actual Cost Net Amt</th>';
                    html += '                <th class="text-end col-batch-off-exp" ' + styleOffExp + '>Official Cost Per Pc with Exp</th>';
                    html += '                <th class="text-end col-batch-off-exp" ' + styleOffExp + '>Official Cost with Exp</th>';
                    html += '                <th class="text-end col-batch-off-amt" ' + styleOffAmt + '>Official Cost Per Pc Net</th>';
                    html += '                <th class="text-end col-batch-off-amt" ' + styleOffAmt + '>Official Cost Net Amt</th>';
                    html += '              </tr>';
                    html += '            </thead>';
                    html += '            <tbody>';

                    for (var bIdx = 0; bIdx < batches.length; bIdx++) {
                        var b = batches[bIdx];
                        var bQty = Number(b.quantity);
                        var bBlack = Number(b.black_qty);
                        var bWhite = Number(b.white_qty);
                        var bPending = Number(b.pending_qty);
                        var bTotWhite = Number(b.total_white_qty);
                        var bBooked = Number(b.booked_qty);

                        var bBadgeBlack = bBlack > 0 ? '<span class="stk-badge stk-badge-black">' + bBlack.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                        var bBadgeWhite = bWhite > 0 ? '<span class="stk-badge stk-badge-white">' + bWhite.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                        var bBadgePending = bPending > 0 ? '<span class="stk-badge stk-badge-pending">' + bPending.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                        var bBadgeTotWhite = bTotWhite > 0 ? '<span class="stk-badge stk-badge-total-white">' + bTotWhite.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';
                        var bBadgeBooked = bBooked > 0 ? '<span class="stk-badge stk-badge-booked">' + bBooked.toLocaleString() + '</span>' : '<span class="stk-badge-zero">-</span>';

                        html += '              <tr>';
                        html += '                <td class="text-center fw-bold text-muted">' + b.sr_no + '</td>';
                        html += '                <td><span class="batch-no-tag">' + b.batch_no + '</span></td>';
                        html += '                <td class="text-end stk-qty-main">' + bQty.toLocaleString() + '</td>';
                        html += '                <td class="text-end">' + bBadgeBlack + '</td>';
                        html += '                <td class="text-end">' + bBadgeWhite + '</td>';
                        html += '                <td class="text-end">' + bBadgePending + '</td>';
                        html += '                <td class="text-end">' + bBadgeTotWhite + '</td>';
                        html += '                <td class="text-end col-batch-booked" ' + styleBooked + '>' + bBadgeBooked + '</td>';
                        html += '                <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                        html += '                <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                        html += '                <td class="text-end col-batch-po" ' + stylePo + '><span class="stk-badge-zero">-</span></td>';
                        html += '                <td class="text-end stk-cost col-batch-act-exp ' + (parseFloat(b.actual_cost_per_pc_with_exp) > 0 ? '' : 'stk-zero') + '" ' + styleActExp + '>₹' + b.actual_cost_per_pc_with_exp + '</td>';
                        html += '                <td class="text-end stk-cost col-batch-act-exp ' + (parseFloat(b.actual_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActExp + '>₹' + b.actual_cost_with_exp + '</td>';
                        html += '                <td class="text-end stk-cost col-batch-act-amt ' + (parseFloat(b.actual_cost_per_pc_net) > 0 ? '' : 'stk-zero') + '" ' + styleActAmt + '>₹' + b.actual_cost_per_pc_net + '</td>';
                        html += '                <td class="text-end stk-cost col-batch-act-amt ' + (parseFloat(b.actual_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleActAmt + '>₹' + b.actual_cost_net + '</td>';
                        html += '                <td class="text-end stk-cost col-batch-off-exp ' + (parseFloat(b.official_cost_per_pc_with_exp) > 0 ? '' : 'stk-zero') + '" ' + styleOffExp + '>₹' + b.official_cost_per_pc_with_exp + '</td>';
                        html += '                <td class="text-end stk-cost col-batch-off-exp ' + (parseFloat(b.official_cost_with_exp) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffExp + '>₹' + b.official_cost_with_exp + '</td>';
                        html += '                <td class="text-end stk-cost col-batch-off-amt ' + (parseFloat(b.official_cost_per_pc_net) > 0 ? '' : 'stk-zero') + '" ' + styleOffAmt + '>₹' + b.official_cost_per_pc_net + '</td>';
                        html += '                <td class="text-end stk-cost col-batch-off-amt ' + (parseFloat(b.official_cost_net) > 0 ? 'stk-cost-accent' : 'stk-zero') + '" ' + styleOffAmt + '>₹' + b.official_cost_net + '</td>';
                        html += '              </tr>';
                    }

                    html += '            </tbody>';
                    html += '          </table>';
                    html += '        </div>';
                }

                html += '          </div>';
                html += '          </div>';
                html += '        </td>';
                html += '      </tr>';
            }

            html += '    </tbody>';
            html += '  </table>';
            html += '</div>';
        }

        html += '</div></div>';
        return html;
    }

    $(document).ready(function ($) {
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
            "language": {
                sLengthMenu: "Show _MENU_ entries",
                search: "",
                searchPlaceholder: "Search product name, code...",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            "drawCallback": function (settings) {
                $('[data-toggle="tooltip"]').tooltip('update');

                // Update overall system totals from server response
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

                // Auto-expand all layers if Stock with Batch filter is active
                if ($('#toggle-stock-batch').is(':checked')) {
                    expandAllBatches();
                }
            },
            "ajax": {
                "url": "<?php echo base_url('inventory/get_overall_stock'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function (data) {
                    data.show_zero_qty = $('#toggle-zero-qty').is(':checked') ? 1 : 0;
                },
                "beforeSend": function () {
                    $('.loader').show();
                },
                "complete": function () {
                    $('.loader').hide();
                }
            },
            "columns": [
                { "data": "sr_no", "className": "text-center text-nowrap align-middle" },
                { "data": "product_name", "className": "text-start align-middle" },
                { "data": "model_no", "className": "text-start text-nowrap align-middle", "visible": false },
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
                { "data": "official_cost_net", "className": "text-end text-nowrap align-middle" }
            ],
            "buttons": [],
            "infoCallback": function (settings, start, end, max, total, pre) {
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

            $('#report-datatable_wrapper colgroup col').each(function () {
                this.style.setProperty('width', 'auto', 'important');
            });
        }

        // Keep appendable scroll inside child; hide parent H-scroll while open
        function syncParentChildOverflow() {
            var $scrollBody = $('#report-datatable_wrapper .dataTables_scrollBody');
            var hasOpen = $('#report-datatable_wrapper .dataTables_scrollBody tbody tr.child:visible').length > 0
                || $('#report-datatable_wrapper .dataTables_scrollBody tbody tr.shown').length > 0;
            $scrollBody.toggleClass('has-open-child', hasOpen);
            fitMainTableWidth();
        }

        function lockChildRow($childTr) {
            if (!$childTr || !$childTr.length) return;
            var $td = $childTr.children('td').first();
            var $wrap = $td.find('.child-scroll-wrap').first();
            if (!$wrap.length) return;

            $childTr.css('height', '');
            $td.attr('style', 'padding:0!important;border-top:none!important;background:transparent!important;');
            $wrap.attr('style',
                'display:block;width:0;min-width:100%;max-width:100%;' +
                'overflow-x:auto;overflow-y:hidden;box-sizing:border-box;padding:4px 2px;-webkit-overflow-scrolling:touch;'
            );
            $wrap.find('.nested-child-scroll').attr('style',
                'display:block;width:0;min-width:100%;max-width:100%;' +
                'overflow-x:auto;overflow-y:hidden;box-sizing:border-box;padding:2px 0 4px;-webkit-overflow-scrolling:touch;'
            );
            syncParentChildOverflow();
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
            syncParentChildOverflow();
        }

        function openChildRow(row, tr, rowData) {
            row.child(formatProductChildRow(rowData)).show();
            tr.addClass('shown');
            var $childTr = tr.next('tr');
            lockChildRow($childTr);
            setTimeout(function () {
                lockChildRow($childTr);
            }, 0);
            return $childTr;
        }

        $(window).on('resize', function () {
            fitChildScrollPanels();
            fitMainTableWidth();
        });

        // Expand/collapse all layers functions
        function expandAllBatches() {
            var showZeroQty = $('#toggle-zero-qty').is(':checked');
            dataTable.rows({ page: 'current' }).every(function () {
                var row = this;
                var rowData = row.data();
                if (rowData && rowData.companies) {
                    var activeCompanies = showZeroQty ? rowData.companies : rowData.companies.filter(function (c) {
                        return Number(c.quantity) > 0;
                    });
                    if (activeCompanies.length > 0) {
                        if (!row.child.isShown()) {
                            var tr = $(row.node());
                            openChildRow(row, tr, rowData);
                            tr.find('.btn-expand-row i').removeClass('icon-plus').addClass('icon-minus');
                        }
                        var childNode = $(row.child());
                        childNode.find('.company-row').each(function () {
                            var compTr = $(this);
                            var nextBatchesRow = compTr.next('.company-batches-row');
                            if (nextBatchesRow.length) {
                                nextBatchesRow.show();
                                compTr.addClass('company-shown');
                                compTr.find('.btn-expand-company-row i').removeClass('icon-plus').addClass('icon-minus');
                            }
                        });
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

        // Dynamic Column Visibility Filters Function across all layers
        function updateFilterCount() {
            // Count only deviations from default so the badge stays hidden until filters change
            var defaults = {
                'toggle-stock-batch': false,
                'toggle-zero-qty': false,
                'toggle-product-col': true,
                'toggle-model-col': false,
                'toggle-booked-qty': true,
                'toggle-po-qty': false,
                'toggle-act-cost-exp': true,
                'toggle-act-cost-amt': true,
                'toggle-off-cost-exp': true,
                'toggle-off-cost-amt': true
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
            var showProduct = $('#toggle-product-col').is(':checked');
            var showModel = $('#toggle-model-col').is(':checked');
            var showBooked = $('#toggle-booked-qty').is(':checked');
            var showPo = $('#toggle-po-qty').is(':checked');
            var showActExp = $('#toggle-act-cost-exp').is(':checked');
            var showActAmt = $('#toggle-act-cost-amt').is(':checked');
            var showOffExp = $('#toggle-off-cost-exp').is(':checked');
            var showOffAmt = $('#toggle-off-cost-amt').is(':checked');

            // Toggle Layer 1 (Main table columns)
            dataTable.column(1).visible(showProduct, false);
            dataTable.column(2).visible(showModel, false);
            dataTable.column(8).visible(showBooked, false);
            dataTable.column(9).visible(showPo, false);
            dataTable.column(10).visible(showPo, false);
            dataTable.column(11).visible(showPo, false);
            dataTable.column(12).visible(showActExp, false);
            dataTable.column(13).visible(showActAmt, false);
            dataTable.column(14).visible(showOffExp, false);
            dataTable.column(15).visible(showOffAmt, false);
            dataTable.columns.adjust();
            fitMainTableWidth();
            requestAnimationFrame(function () {
                fitMainTableWidth();
            });

            // Toggle Layer 2 (Company breakdown sub-tables) & Layer 3 (Batch sub-tables)
            $('.sub-company-table .col-batch-booked, .sub-batch-table .col-batch-booked').toggle(showBooked);
            $('.sub-company-table .col-batch-po, .sub-batch-table .col-batch-po').toggle(showPo);
            $('.sub-company-table .col-batch-act-exp, .sub-batch-table .col-batch-act-exp').toggle(showActExp);
            $('.sub-company-table .col-batch-act-amt, .sub-batch-table .col-batch-act-amt').toggle(showActAmt);
            $('.sub-company-table .col-batch-off-exp, .sub-batch-table .col-batch-off-exp').toggle(showOffExp);
            $('.sub-company-table .col-batch-off-amt, .sub-batch-table .col-batch-off-amt').toggle(showOffAmt);
            fitChildScrollPanels();
            updateFilterCount();
        }

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
        $('.column-filter-checkbox').on('change', function () {
            var isChecked = $(this).is(':checked');
            $(this).closest('.filter-chip').toggleClass('active', isChecked);
            applyColumnFilters();
        });

        // Reset Filter Handler
        $('#btn-reset-col-filters').on('click', function (e) {
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
            $('#toggle-act-cost-exp').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-act-cost-amt').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-off-cost-exp').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-off-cost-amt').prop('checked', true).closest('.filter-chip').addClass('active');
            applyColumnFilters();

            if (zeroWasChecked) {
                dataTable.ajax.reload();
            }
        });

        updateFilterCount();

        // Layer 1: Expand/Collapse Company Breakdown for a Product
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
                var allCompanies = rowData ? (rowData.companies || []) : [];
                var activeCompanies = showZeroQty ? allCompanies : allCompanies.filter(function (c) {
                    return Number(c.quantity) > 0;
                });
                if (activeCompanies.length > 0) {
                    openChildRow(row, tr, rowData);
                    icon.removeClass('icon-plus').addClass('icon-minus');
                    $('[data-toggle="tooltip"]').tooltip();
                    applyColumnFilters();
                }
            }
        });

        // Layer 2: Expand/Collapse Batches for a Company
        $('#report-datatable tbody').on('click', '.btn-expand-company-row', function (e) {
            e.stopPropagation();
            var compTr = $(this).closest('tr');
            var batchesTr = compTr.next('.company-batches-row');
            var icon = $(this).find('i');

            if (batchesTr.is(':visible')) {
                batchesTr.hide();
                compTr.removeClass('company-shown');
                icon.removeClass('icon-minus').addClass('icon-plus');
            } else {
                batchesTr.show();
                compTr.addClass('company-shown');
                icon.removeClass('icon-plus').addClass('icon-minus');
                $('[data-toggle="tooltip"]').tooltip();
                applyColumnFilters();
            }
            fitChildScrollPanels();
        });
    });
</script>