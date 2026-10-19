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
    .table-error td{
    	background: #febdb9;
        color: #3c3a3a;
        font-weight: 600 !important;
    }
	.fixedElement{
		background : white;
		border-radius: .428rem;
	}
	.nav-pills.nav-justified .nav-item {
		display: flex;
		align-items: center;
	}
	.new-fix .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
		color: #1e652e;
		border: 1px solid #1e652e !important;
		background: white;
		box-shadow: initial;
		font-weight: 600;
	}
    .subtabs-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 16px;
    }
    .subtabs-nav .nav-link {
        color: #4b5563;
        background: #f1f5f9;
        border-radius: 6px;
        padding: 7px 18px;
        font-weight: 600;
        margin-right: 8px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .subtabs-nav .nav-link:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .subtabs-nav .nav-link.active {
        background: #7367f0 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
    }
    .so-table-title-row {
        gap: 10px;
        margin-bottom: 4px;
    }
    .so-table-title-row .stock-filter-dropdown {
        position: relative;
        z-index: 5;
    }
    .so-table-title-row .stock-filter-menu {
        min-width: 320px;
        max-width: 360px;
    }
</style>
<?php
    include('filter/sales_order_all_filter.php');
    $sub_tab = (isset($_GET['sub_tab']) && $_GET['sub_tab'] != '') ? $_GET['sub_tab'] : 'order';

    if ($sub_tab != 'product' && !is_access('215') && is_access('216')) {
        $sub_tab = 'product';
    } elseif ($sub_tab == 'product' && !is_access('216') && is_access('215')) {
        $sub_tab = 'order';
    }

    $date_range_param = '';
    if (isset($_GET['date_range']) && $_GET['date_range'] != '') {
        $date_range_param .= '&date_range=' . urlencode($_GET['date_range']);
        if (isset($_GET['search'])) {
            $date_range_param .= '&search=' . urlencode($_GET['search']);
        }
    }
    if (isset($_GET['delivery_date_range']) && $_GET['delivery_date_range'] != '') {
        $date_range_param .= '&delivery_date_range=' . urlencode($_GET['delivery_date_range']);
        if (isset($_GET['search']) && strpos($date_range_param, 'search=') === false) {
            $date_range_param .= '&search=' . urlencode($_GET['search']);
        }
    }
    if (isset($_GET['customer_id']) && $_GET['customer_id'] != '') {
        $date_range_param .= '&customer_id=' . urlencode($_GET['customer_id']);
    }
?>
	
<div class="row" id="table-bordered">
    <?php include('nav/nav_sales_order.php'); ?>
    <div class="col-md-12 mb-1">
        <div class="fixedElement subtabs-bar">
            <ul class="nav nav-pills subtabs-nav">
                <?php if (is_access('215')) { ?>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order-all?sub_tab=order<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab != 'product') ? 'active' : ''; ?>">
                        <i class="fa fa-list me-1"></i> Order Wise
                    </a>
                </li>
                <?php } ?>
                <?php if (is_access('216')) { ?>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order-all?sub_tab=product<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab == 'product') ? 'active' : ''; ?>">
                        <i class="fa fa-cubes me-1"></i> Product Wise
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
   <div class="col-12">
      <div class="card">
         <div class="card-body pb-0">
             <div class="d-flex align-items-center flex-wrap gap-1 so-table-title-row">
                   <h5 class="mb-0">
                   <?php if ($sub_tab == 'product') { ?>
                       <b>Total Product Batches <span id="total_count"> (0)</span></b>
                   <?php } else { ?>
                       <b>Total Sales Order Items <span id="total_count"> (0)</span></b>
                   <?php } ?>
                   <?php if ($sub_tab != 'product' && is_access('306')) { ?>
                      &nbsp;|&nbsp; <b>Total Amount: ₹<span id="total_sales_amount">0.00</span></b>
                   <?php } ?>
				  </h5>
            <?php if ($sub_tab != 'product') { ?>
                <div class="dropdown stock-filter-dropdown ms-50">
                    <button class="btn-filter-dropdown dropdown-toggle" type="button" id="soOrderWiseFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="feather icon-filter"></i>
                        <span>Filters</span>
                        <span class="filter-count-badge" id="orderwise-filter-active-count">0</span>
                    </button>
                    <div class="dropdown-menu stock-filter-menu" aria-labelledby="soOrderWiseFilterDropdown">
                        <div class="filter-menu-header">
                            <span class="title">Table Filters</span>
                        </div>
                        <div class="filter-menu-body" id="orderwise-column-filters-container">
                            <div class="filter-menu-label">Dates & Reference</div>
                            <?php if (is_access('286')) { ?>

                            <label class="filter-chip" for="toggle-ow-bill-date" title="Toggle Bill Date column">
                                <input type="checkbox" id="toggle-ow-bill-date" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Bill Date</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('287')) { ?>

                            <label class="filter-chip" for="toggle-ow-delivery-date" title="Toggle Delivery Date column">
                                <input type="checkbox" id="toggle-ow-delivery-date" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Delivery Date</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('288')) { ?>

                            <label class="filter-chip" for="toggle-ow-inv-no" title="Toggle Inv No column">
                                <input type="checkbox" id="toggle-ow-inv-no" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Inv No</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('291')) { ?>

                            <label class="filter-chip" for="toggle-ow-warehouse" title="Toggle Warehouse column">
                                <input type="checkbox" id="toggle-ow-warehouse" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Warehouse</span>
                            </label>

                            <?php } ?>
                            <div class="filter-menu-label">Product & Item Details</div>
                            <?php if (is_access('292')) { ?>

                            <label class="filter-chip active" for="toggle-ow-batch-no" title="Toggle Batch No column">
                                <input type="checkbox" id="toggle-ow-batch-no" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Batch No</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('293')) { ?>

                            <label class="filter-chip active" for="toggle-ow-product" title="Toggle Product column">
                                <input type="checkbox" id="toggle-ow-product" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Product</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('294')) { ?>

                            <label class="filter-chip active" for="toggle-ow-model-no" title="Toggle Model No column">
                                <input type="checkbox" id="toggle-ow-model-no" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Model No</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('295')) { ?>

                            <label class="filter-chip active" for="toggle-ow-qty" title="Toggle Qty column">
                                <input type="checkbox" id="toggle-ow-qty" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Qty</span>
                            </label>

                            <?php } ?>
                            <div class="filter-menu-label">Rates & Billing</div>
                            <?php if (is_access('296')) { ?>

                            <label class="filter-chip active" for="toggle-ow-rate" title="Toggle Rate column">
                                <input type="checkbox" id="toggle-ow-rate" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Rate</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('297')) { ?>

                            <label class="filter-chip active" for="toggle-ow-tot-rate" title="Toggle Tot Rate column">
                                <input type="checkbox" id="toggle-ow-tot-rate" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Rate</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('298')) { ?>

                            <label class="filter-chip active" for="toggle-ow-bill-amt" title="Toggle Bill Amt column">
                                <input type="checkbox" id="toggle-ow-bill-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Bill Amt</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('299')) { ?>

                            <label class="filter-chip active" for="toggle-ow-taxable-amt" title="Toggle Tot Taxable Amt column">
                                <input type="checkbox" id="toggle-ow-taxable-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Taxable Amt</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('300')) { ?>

                            <label class="filter-chip active" for="toggle-ow-cgst" title="Toggle CGST column">
                                <input type="checkbox" id="toggle-ow-cgst" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">CGST</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('301')) { ?>

                            <label class="filter-chip active" for="toggle-ow-sgst" title="Toggle SGST column">
                                <input type="checkbox" id="toggle-ow-sgst" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">SGST</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('302')) { ?>

                            <label class="filter-chip active" for="toggle-ow-igst" title="Toggle IGST column">
                                <input type="checkbox" id="toggle-ow-igst" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">IGST</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('303')) { ?>

                            <label class="filter-chip active" for="toggle-ow-inv-amt" title="Toggle Inv Amt column">
                                <input type="checkbox" id="toggle-ow-inv-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Inv Amt</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('304')) { ?>

                            <label class="filter-chip active" for="toggle-ow-cash" title="Toggle Cash column">
                                <input type="checkbox" id="toggle-ow-cash" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Cash</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('305')) { ?>

                            <label class="filter-chip active" for="toggle-ow-tot-cash" title="Toggle Tot Cash column">
                                <input type="checkbox" id="toggle-ow-tot-cash" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Cash</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('306')) { ?>

                            <label class="filter-chip active" for="toggle-ow-final-amt" title="Toggle Final Amt column">
                                <input type="checkbox" id="toggle-ow-final-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Final Amt</span>
                            </label>

                            <?php } ?>
                            <div class="filter-menu-label">Sales Person & Commission</div>
                            <?php if (is_access('310')) { ?>

                            <label class="filter-chip active" for="toggle-ow-sales-person" title="Toggle Sales Person column">
                                <input type="checkbox" id="toggle-ow-sales-person" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Sales Person</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('307')) { ?>

                            <label class="filter-chip" for="toggle-ow-comm-amt" title="Toggle Comm Amt column">
                                <input type="checkbox" id="toggle-ow-comm-amt" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Comm Amt</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('308')) { ?>

                            <label class="filter-chip" for="toggle-ow-tot-comm-amt" title="Toggle Tot Comm Amt column">
                                <input type="checkbox" id="toggle-ow-tot-comm-amt" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Comm Amt</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('309')) { ?>

                            <label class="filter-chip" for="toggle-ow-comm-per" title="Toggle Comm % column">
                                <input type="checkbox" id="toggle-ow-comm-per" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Comm %</span>
                            </label>

                            <?php } ?>
                            <div class="filter-menu-label">Cost & Profit</div>
                            <?php if (is_access('311')) { ?>

                            <label class="filter-chip" for="toggle-ow-act-cost-per-pc" title="Toggle Act Cst Per Pc With Expense column">
                                <input type="checkbox" id="toggle-ow-act-cost-per-pc" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Act Cst Per Pc With Expense</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('312')) { ?>

                            <label class="filter-chip" for="toggle-ow-tot-act-cost" title="Toggle Tot Act Cst With Expense column">
                                <input type="checkbox" id="toggle-ow-tot-act-cost" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Act Cst With Expense</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('313')) { ?>

                            <label class="filter-chip" for="toggle-ow-profit-amt-per-pc" title="Toggle Profit Amt Per Pc column">
                                <input type="checkbox" id="toggle-ow-profit-amt-per-pc" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Profit Amt Per Pc</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('314')) { ?>

                            <label class="filter-chip" for="toggle-ow-total-profit" title="Toggle Total Profit column">
                                <input type="checkbox" id="toggle-ow-total-profit" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Total Profit</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('315')) { ?>

                            <label class="filter-chip" for="toggle-ow-tot-profit-per" title="Toggle Tot Profit % column">
                                <input type="checkbox" id="toggle-ow-tot-profit-per" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Profit %</span>
                            </label>

                            <?php } ?>
                            <?php if (is_access('316')) { ?>

                            <label class="filter-chip" for="toggle-ow-added-by" title="Toggle Added By column">
                                <input type="checkbox" id="toggle-ow-added-by" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Added By</span>
                            </label>

                            <?php } ?>
                        </div>
                        <div class="filter-menu-footer">
                            <button type="button" class="btn-reset-filters" id="btn-reset-ow-col-filters" title="Reset column filters to default">
                                <i class="feather icon-rotate-ccw"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
                  <?php if ($sub_tab == 'product') { ?>
					<th>#</th>
					<?php if (is_access('325')) { ?><th>Batch No</th><?php } ?>
					<?php if (is_access('326')) { ?><th>Order No</th><?php } ?>
					<?php if (is_access('327')) { ?><th>Order Date</th><?php } ?>
					<?php if (is_access('328')) { ?><th>Party Name</th><?php } ?>
					<?php if (is_access('329')) { ?><th>Product Name</th><?php } ?>
					<?php if (is_access('330')) { ?><th>Model No.</th><?php } ?>
					<?php if (is_access('331')) { ?><th>Qty</th><?php } ?>
					<?php if (is_access('332')) { ?><th>Rate</th><?php } ?>
					<?php if (is_access('333')) { ?><th>Bill amt</th><?php } ?>
					<?php if (is_access('334')) { ?><th>Total Taxable Amt</th><?php } ?>
					<?php if (is_access('335')) { ?><th>CGST</th><?php } ?>
					<?php if (is_access('336')) { ?><th>SGST</th><?php } ?>
					<?php if (is_access('337')) { ?><th>IGST</th><?php } ?>
					<?php if (is_access('338')) { ?><th>Total Bill Amt</th><?php } ?>
					<?php if (is_access('339')) { ?><th>Cash Amt</th><?php } ?>
					<?php if (is_access('340')) { ?><th>Total Cash Amt</th><?php } ?>
					<?php if (is_access('341')) { ?><th>Total Amt</th><?php } ?>
					<?php if (is_access('342')) { ?><th>Comm Amt</th><?php } ?>
					<?php if (is_access('343')) { ?><th>Comm Name</th><?php } ?>
					<?php if (is_access('344')) { ?><th>Profit</th><?php } ?>
					<?php if (is_access('345')) { ?><th>Act Cst With Expense/Pc</th><?php } ?>
					<?php if (is_access('346')) { ?><th>Total Cst With Expense</th><?php } ?>
                  <?php } else { ?>
					<th>#</th>
					<?php if (is_access('286')) { ?><th>Bill Date</th><?php } ?>
					<?php if (is_access('287')) { ?><th>Delivery Date</th><?php } ?>
					<?php if (is_access('288')) { ?><th>Inv No</th><?php } ?>
					<?php if (is_access('289')) { ?><th>Customer Name</th><?php } ?>
					<?php if (is_access('290')) { ?><th>Order No</th><?php } ?>
					<?php if (is_access('291')) { ?><th>Warehouse</th><?php } ?>
					<?php if (is_access('292')) { ?><th>Batch No</th><?php } ?>
					<?php if (is_access('293')) { ?><th>Product Name</th><?php } ?>
					<?php if (is_access('294')) { ?><th>Model No.</th><?php } ?>
					<?php if (is_access('295')) { ?><th>Qty</th><?php } ?>
					<?php if (is_access('296')) { ?><th>Rate</th><?php } ?>
					<?php if (is_access('297')) { ?><th>Tot Rate</th><?php } ?>
					<?php if (is_access('298')) { ?><th>Bill Amt</th><?php } ?>
					<?php if (is_access('299')) { ?><th>Taxable Amt</th><?php } ?>
					<?php if (is_access('300')) { ?><th>CGST</th><?php } ?>
					<?php if (is_access('301')) { ?><th>SGST</th><?php } ?>
					<?php if (is_access('302')) { ?><th>IGST</th><?php } ?>
					<?php if (is_access('303')) { ?><th>Inv Amt</th><?php } ?>
					<?php if (is_access('304')) { ?><th>Cash</th><?php } ?>
					<?php if (is_access('305')) { ?><th>Tot Cash Amt</th><?php } ?>
					<?php if (is_access('306')) { ?><th>Final Amt</th><?php } ?>
					<?php if (is_access('307')) { ?><th>Comm Amt</th><?php } ?>
					<?php if (is_access('308')) { ?><th>Tot Comm Amt</th><?php } ?>
					<?php if (is_access('309')) { ?><th>Comm %</th><?php } ?>
					<?php if (is_access('310')) { ?><th>Sales Person</th><?php } ?>
					<?php if (is_access('311')) { ?><th>Act Cst Per Pc With Expense</th><?php } ?>
					<?php if (is_access('312')) { ?><th>Tot Act Cst With Expense</th><?php } ?>
					<?php if (is_access('313')) { ?><th>Profit Amt Per Pc</th><?php } ?>
					<?php if (is_access('314')) { ?><th>Total Profit</th><?php } ?>
					<?php if (is_access('315')) { ?><th>Tot Profit %</th><?php } ?>
					<?php if (is_access('316')) { ?><th>Added By</th><?php } ?>
					<?php if (is_access('317') || is_access('318') || is_access('319') || is_access('320') || is_access('321') || is_access('322') || is_access('323')) { ?><th>ACTIONS</th><?php } ?>
                  <?php } ?>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">
    $(document).ready(function($) {
    	var dataTable = $('#report-datatable').DataTable({ 
    	    "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true, 
            "lengthChange": true,  
            "language" : {
                sLengthMenu: "_MENU_",
                'processing': $('.loader').show()
            },	
            "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
            },
      
            "ajax":{
                <?php if ($sub_tab == 'product') { ?>
                "url": "<?php echo base_url('inventory/get_sales_order_product_wise'); ?>",
                <?php } else { ?>
                "url": "<?php echo base_url('inventory/get_sales_order_all_order_wise'); ?>",
                <?php } ?>
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.delivery_date_range = '<?php echo (isset($_GET['delivery_date_range'])) ? $_GET['delivery_date_range']:'' ?>';
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = 'all';	
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                <?php if ($sub_tab == 'product') { ?>
                { "data": "sr_no" },
                <?php if (is_access('325')) { ?>{ "data": "batch_no" },<?php } ?>
                <?php if (is_access('326')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('327')) { ?>{ "data": "order_date" },<?php } ?>
                <?php if (is_access('328')) { ?>{ "data": "party_name" },<?php } ?>
                <?php if (is_access('329')) { ?>{ "data": "product_name" },<?php } ?>
                <?php if (is_access('330')) { ?>{ "data": "model_no" },<?php } ?>
                <?php if (is_access('331')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('332')) { ?>{ "data": "rate" },<?php } ?>
                <?php if (is_access('333')) { ?>{ "data": "bill_amt" },<?php } ?>
                <?php if (is_access('334')) { ?>{ "data": "total_taxable_amt" },<?php } ?>
                <?php if (is_access('335')) { ?>{ "data": "cgst" },<?php } ?>
                <?php if (is_access('336')) { ?>{ "data": "sgst" },<?php } ?>
                <?php if (is_access('337')) { ?>{ "data": "igst" },<?php } ?>
                <?php if (is_access('338')) { ?>{ "data": "total_bill_amt" },<?php } ?>
                <?php if (is_access('339')) { ?>{ "data": "cash_amt" },<?php } ?>
                <?php if (is_access('340')) { ?>{ "data": "total_cash_amt" },<?php } ?>
                <?php if (is_access('341')) { ?>{ "data": "total_amt" },<?php } ?>
                <?php if (is_access('342')) { ?>{ "data": "comm_amt" },<?php } ?>
                <?php if (is_access('343')) { ?>{ "data": "comm_name" },<?php } ?>
                <?php if (is_access('344')) { ?>{ "data": "profit" },<?php } ?>
                <?php if (is_access('345')) { ?>{ "data": "act_cost_with_exp" },<?php } ?>
                <?php if (is_access('346')) { ?>{ "data": "total_cost_with_exp" },<?php } ?>
                <?php } else { ?>
                { "data": "sr_no" },
                <?php if (is_access('286')) { ?>{ "data": "bill_date", "visible": false },<?php } ?>
                <?php if (is_access('287')) { ?>{ "data": "delivery_date", "visible": false },<?php } ?>
                <?php if (is_access('288')) { ?>{ "data": "inv_no", "visible": false },<?php } ?>
                <?php if (is_access('289')) { ?>{ "data": "customer_name" },<?php } ?>
                <?php if (is_access('290')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('291')) { ?>{ "data": "warehouse_name", "visible": false },<?php } ?>
                <?php if (is_access('292')) { ?>{ "data": "batch_no" },<?php } ?>
                <?php if (is_access('293')) { ?>{ "data": "product_name" },<?php } ?>
                <?php if (is_access('294')) { ?>{ "data": "model_no" },<?php } ?>
                <?php if (is_access('295')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('296')) { ?>{ "data": "rate" },<?php } ?>
                <?php if (is_access('297')) { ?>{ "data": "tot_rate" },<?php } ?>
                <?php if (is_access('298')) { ?>{ "data": "bill_amt" },<?php } ?>
                <?php if (is_access('299')) { ?>{ "data": "taxable_amt" },<?php } ?>
                <?php if (is_access('300')) { ?>{ "data": "cgst" },<?php } ?>
                <?php if (is_access('301')) { ?>{ "data": "sgst" },<?php } ?>
                <?php if (is_access('302')) { ?>{ "data": "igst" },<?php } ?>
                <?php if (is_access('303')) { ?>{ "data": "inv_amt" },<?php } ?>
                <?php if (is_access('304')) { ?>{ "data": "cash" },<?php } ?>
                <?php if (is_access('305')) { ?>{ "data": "tot_cash_amt" },<?php } ?>
                <?php if (is_access('306')) { ?>{ "data": "final_amt" },<?php } ?>
                <?php if (is_access('307')) { ?>{ "data": "comm_amt", "visible": false },<?php } ?>
                <?php if (is_access('308')) { ?>{ "data": "tot_comm_amt", "visible": false },<?php } ?>
                <?php if (is_access('309')) { ?>{ "data": "comm_per", "visible": false },<?php } ?>
                <?php if (is_access('310')) { ?>{ "data": "sales_person" },<?php } ?>
                <?php if (is_access('311')) { ?>{ "data": "act_cost_per_pc", "visible": false },<?php } ?>
                <?php if (is_access('312')) { ?>{ "data": "tot_act_cost", "visible": false },<?php } ?>
                <?php if (is_access('313')) { ?>{ "data": "profit_amt_per_pc", "visible": false },<?php } ?>
                <?php if (is_access('314')) { ?>{ "data": "total_profit", "visible": false },<?php } ?>
                <?php if (is_access('315')) { ?>{ "data": "tot_profit_per", "visible": false },<?php } ?>
                <?php if (is_access('316')) { ?>{ "data": "added_by", "visible": false },<?php } ?>
                <?php if (is_access('317') || is_access('318') || is_access('319') || is_access('320') || is_access('321') || is_access('322') || is_access('323')) { ?>{ "data": "action" }<?php } ?>
                <?php } ?>
            ],
            
             "buttons": [
                <?php if ($sub_tab == 'product' && is_access('347')) { ?>
                 {
                     "extend": 'excel',
                     "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                 },
                 {
                     "extend": 'pdfHtml5',
                     "orientation": 'landscape',
                     "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                 }
                <?php } elseif ($sub_tab != 'product' && is_access('324')) { ?>
                 {
                     "extend": 'excel',
                     "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                 },
                 {
                     "extend": 'pdfHtml5',
                     "orientation": 'landscape',
                     "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                 }
                <?php } ?>
             ], 
           
             "infoCallback": function( settings, start, end, max, total, pre ) {
                 $(".loader").fadeOut("slow"); 
                 $('#total_count').html('('+total+')');
                 var json = settings.json;
                 if (json && json.total_amount !== undefined) {
                     $('#total_sales_amount').html(json.total_amount);
                 } else {
                     $('#total_sales_amount').html('0.00');
                 }
                 return 'Showing ' +start+ ' to ' + end + ' of '+ total + ' entries';
             }, 
			createdRow: function (row, data, index) {
                   if(data['error']=='1'){
                    $(row).addClass('table-error');
                   }
            },

        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
        <?php if ($sub_tab != 'product') { ?>
        var orderWiseDefaults = {
            'toggle-ow-bill-date': false,
            'toggle-ow-delivery-date': false,
            'toggle-ow-inv-no': false,
            'toggle-ow-warehouse': false,
            'toggle-ow-batch-no': true,
            'toggle-ow-product': true,
            'toggle-ow-model-no': true,
            'toggle-ow-qty': true,
            'toggle-ow-rate': true,
            'toggle-ow-tot-rate': true,
            'toggle-ow-bill-amt': true,
            'toggle-ow-taxable-amt': true,
            'toggle-ow-cgst': true,
            'toggle-ow-sgst': true,
            'toggle-ow-igst': true,
            'toggle-ow-inv-amt': true,
            'toggle-ow-cash': true,
            'toggle-ow-tot-cash': true,
            'toggle-ow-final-amt': true,
            'toggle-ow-sales-person': true,
            'toggle-ow-comm-amt': false,
            'toggle-ow-tot-comm-amt': false,
            'toggle-ow-comm-per': false,
            'toggle-ow-act-cost-per-pc': false,
            'toggle-ow-tot-act-cost': false,
            'toggle-ow-profit-amt-per-pc': false,
            'toggle-ow-total-profit': false,
            'toggle-ow-tot-profit-per': false,
            'toggle-ow-added-by': false
        };
        function updateOrderWiseFilterCount() {
            var count = 0;
            $.each(orderWiseDefaults, function (id, defaultOn) {
                if ($('#' + id).length && $('#' + id).is(':checked') !== defaultOn) {
                    count++;
                }
            });
            var $badge = $('#orderwise-filter-active-count');
            $badge.text(count);
            $badge.toggleClass('has-count', count > 0);
        }
        <?php
            $ci = 0;
            $c286 = $c287 = $c288 = $c291 = $c292 = $c293 = $c294 = $c295 = 0;
            $c296 = $c297 = $c298 = $c299 = $c300 = $c301 = $c302 = $c303 = 0;
            $c304 = $c305 = $c306 = $c307 = $c308 = $c309 = $c310 = 0;
            $c311 = $c312 = $c313 = $c314 = $c315 = $c316 = 0;
            if (is_access('286')) { $ci++; $c286 = $ci; }
            if (is_access('287')) { $ci++; $c287 = $ci; }
            if (is_access('288')) { $ci++; $c288 = $ci; }
            if (is_access('289')) { $ci++; }
            if (is_access('290')) { $ci++; }
            if (is_access('291')) { $ci++; $c291 = $ci; }
            if (is_access('292')) { $ci++; $c292 = $ci; }
            if (is_access('293')) { $ci++; $c293 = $ci; }
            if (is_access('294')) { $ci++; $c294 = $ci; }
            if (is_access('295')) { $ci++; $c295 = $ci; }
            if (is_access('296')) { $ci++; $c296 = $ci; }
            if (is_access('297')) { $ci++; $c297 = $ci; }
            if (is_access('298')) { $ci++; $c298 = $ci; }
            if (is_access('299')) { $ci++; $c299 = $ci; }
            if (is_access('300')) { $ci++; $c300 = $ci; }
            if (is_access('301')) { $ci++; $c301 = $ci; }
            if (is_access('302')) { $ci++; $c302 = $ci; }
            if (is_access('303')) { $ci++; $c303 = $ci; }
            if (is_access('304')) { $ci++; $c304 = $ci; }
            if (is_access('305')) { $ci++; $c305 = $ci; }
            if (is_access('306')) { $ci++; $c306 = $ci; }
            if (is_access('307')) { $ci++; $c307 = $ci; }
            if (is_access('308')) { $ci++; $c308 = $ci; }
            if (is_access('309')) { $ci++; $c309 = $ci; }
            if (is_access('310')) { $ci++; $c310 = $ci; }
            if (is_access('311')) { $ci++; $c311 = $ci; }
            if (is_access('312')) { $ci++; $c312 = $ci; }
            if (is_access('313')) { $ci++; $c313 = $ci; }
            if (is_access('314')) { $ci++; $c314 = $ci; }
            if (is_access('315')) { $ci++; $c315 = $ci; }
            if (is_access('316')) { $ci++; $c316 = $ci; }
        ?>
        function applyOrderWiseColumnFilters() {
            <?php if (is_access('286')) { ?>if ($('#toggle-ow-bill-date').length) { dataTable.column(<?php echo $c286; ?>).visible($('#toggle-ow-bill-date').is(':checked'), false); }<?php } ?>
            <?php if (is_access('287')) { ?>if ($('#toggle-ow-delivery-date').length) { dataTable.column(<?php echo $c287; ?>).visible($('#toggle-ow-delivery-date').is(':checked'), false); }<?php } ?>
            <?php if (is_access('288')) { ?>if ($('#toggle-ow-inv-no').length) { dataTable.column(<?php echo $c288; ?>).visible($('#toggle-ow-inv-no').is(':checked'), false); }<?php } ?>
            <?php if (is_access('291')) { ?>if ($('#toggle-ow-warehouse').length) { dataTable.column(<?php echo $c291; ?>).visible($('#toggle-ow-warehouse').is(':checked'), false); }<?php } ?>
            <?php if (is_access('292')) { ?>if ($('#toggle-ow-batch-no').length) { dataTable.column(<?php echo $c292; ?>).visible($('#toggle-ow-batch-no').is(':checked'), false); }<?php } ?>
            <?php if (is_access('293')) { ?>if ($('#toggle-ow-product').length) { dataTable.column(<?php echo $c293; ?>).visible($('#toggle-ow-product').is(':checked'), false); }<?php } ?>
            <?php if (is_access('294')) { ?>if ($('#toggle-ow-model-no').length) { dataTable.column(<?php echo $c294; ?>).visible($('#toggle-ow-model-no').is(':checked'), false); }<?php } ?>
            <?php if (is_access('295')) { ?>if ($('#toggle-ow-qty').length) { dataTable.column(<?php echo $c295; ?>).visible($('#toggle-ow-qty').is(':checked'), false); }<?php } ?>
            <?php if (is_access('296')) { ?>if ($('#toggle-ow-rate').length) { dataTable.column(<?php echo $c296; ?>).visible($('#toggle-ow-rate').is(':checked'), false); }<?php } ?>
            <?php if (is_access('297')) { ?>if ($('#toggle-ow-tot-rate').length) { dataTable.column(<?php echo $c297; ?>).visible($('#toggle-ow-tot-rate').is(':checked'), false); }<?php } ?>
            <?php if (is_access('298')) { ?>if ($('#toggle-ow-bill-amt').length) { dataTable.column(<?php echo $c298; ?>).visible($('#toggle-ow-bill-amt').is(':checked'), false); }<?php } ?>
            <?php if (is_access('299')) { ?>if ($('#toggle-ow-taxable-amt').length) { dataTable.column(<?php echo $c299; ?>).visible($('#toggle-ow-taxable-amt').is(':checked'), false); }<?php } ?>
            <?php if (is_access('300')) { ?>if ($('#toggle-ow-cgst').length) { dataTable.column(<?php echo $c300; ?>).visible($('#toggle-ow-cgst').is(':checked'), false); }<?php } ?>
            <?php if (is_access('301')) { ?>if ($('#toggle-ow-sgst').length) { dataTable.column(<?php echo $c301; ?>).visible($('#toggle-ow-sgst').is(':checked'), false); }<?php } ?>
            <?php if (is_access('302')) { ?>if ($('#toggle-ow-igst').length) { dataTable.column(<?php echo $c302; ?>).visible($('#toggle-ow-igst').is(':checked'), false); }<?php } ?>
            <?php if (is_access('303')) { ?>if ($('#toggle-ow-inv-amt').length) { dataTable.column(<?php echo $c303; ?>).visible($('#toggle-ow-inv-amt').is(':checked'), false); }<?php } ?>
            <?php if (is_access('304')) { ?>if ($('#toggle-ow-cash').length) { dataTable.column(<?php echo $c304; ?>).visible($('#toggle-ow-cash').is(':checked'), false); }<?php } ?>
            <?php if (is_access('305')) { ?>if ($('#toggle-ow-tot-cash').length) { dataTable.column(<?php echo $c305; ?>).visible($('#toggle-ow-tot-cash').is(':checked'), false); }<?php } ?>
            <?php if (is_access('306')) { ?>if ($('#toggle-ow-final-amt').length) { dataTable.column(<?php echo $c306; ?>).visible($('#toggle-ow-final-amt').is(':checked'), false); }<?php } ?>
            <?php if (is_access('307')) { ?>if ($('#toggle-ow-comm-amt').length) { dataTable.column(<?php echo $c307; ?>).visible($('#toggle-ow-comm-amt').is(':checked'), false); }<?php } ?>
            <?php if (is_access('308')) { ?>if ($('#toggle-ow-tot-comm-amt').length) { dataTable.column(<?php echo $c308; ?>).visible($('#toggle-ow-tot-comm-amt').is(':checked'), false); }<?php } ?>
            <?php if (is_access('309')) { ?>if ($('#toggle-ow-comm-per').length) { dataTable.column(<?php echo $c309; ?>).visible($('#toggle-ow-comm-per').is(':checked'), false); }<?php } ?>
            <?php if (is_access('310')) { ?>if ($('#toggle-ow-sales-person').length) { dataTable.column(<?php echo $c310; ?>).visible($('#toggle-ow-sales-person').is(':checked'), false); }<?php } ?>
            <?php if (is_access('311')) { ?>if ($('#toggle-ow-act-cost-per-pc').length) { dataTable.column(<?php echo $c311; ?>).visible($('#toggle-ow-act-cost-per-pc').is(':checked'), false); }<?php } ?>
            <?php if (is_access('312')) { ?>if ($('#toggle-ow-tot-act-cost').length) { dataTable.column(<?php echo $c312; ?>).visible($('#toggle-ow-tot-act-cost').is(':checked'), false); }<?php } ?>
            <?php if (is_access('313')) { ?>if ($('#toggle-ow-profit-amt-per-pc').length) { dataTable.column(<?php echo $c313; ?>).visible($('#toggle-ow-profit-amt-per-pc').is(':checked'), false); }<?php } ?>
            <?php if (is_access('314')) { ?>if ($('#toggle-ow-total-profit').length) { dataTable.column(<?php echo $c314; ?>).visible($('#toggle-ow-total-profit').is(':checked'), false); }<?php } ?>
            <?php if (is_access('315')) { ?>if ($('#toggle-ow-tot-profit-per').length) { dataTable.column(<?php echo $c315; ?>).visible($('#toggle-ow-tot-profit-per').is(':checked'), false); }<?php } ?>
            <?php if (is_access('316')) { ?>if ($('#toggle-ow-added-by').length) { dataTable.column(<?php echo $c316; ?>).visible($('#toggle-ow-added-by').is(':checked'), false); }<?php } ?>
            dataTable.columns.adjust();
            updateOrderWiseFilterCount();
        }
        $('.ow-column-filter-checkbox').on('change', function() {
            var isChecked = $(this).is(':checked');
            $(this).closest('.filter-chip').toggleClass('active', isChecked);
            applyOrderWiseColumnFilters();
        });
        $('#btn-reset-ow-col-filters').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $.each(orderWiseDefaults, function (id, defaultOn) {
                $('#' + id).prop('checked', defaultOn).closest('.filter-chip').toggleClass('active', defaultOn);
            });
            applyOrderWiseColumnFilters();
        });
        updateOrderWiseFilterCount();
        <?php } ?>
    });
    function cancelSalesOrder(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will mark the sales order as cancelled and revert any booked stock!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, cancel it!',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('.loader').show();
                $.ajax({
                    url: "<?php echo base_url('inventory/sales_order_cancel/'); ?>" + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        $('.loader').hide();
                        if (res.status == 200 || res.status == '200') {
                            Swal.fire({
                                title: 'Cancelled!',
                                text: res.message,
                                icon: 'success',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                $('#report-datatable').DataTable().ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: res.message,
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function() {
                        $('.loader').hide();
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while cancelling the sales order.',
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    }
    function deleteSalesOrder(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure want to delete this sales order?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('.loader').show();
                $.ajax({
                    url: "<?php echo base_url('inventory/sales_order/delete/'); ?>" + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        $('.loader').hide();
                        if (res.status == 200 || res.status == '200') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: res.message,
                                icon: 'success',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                $('#report-datatable').DataTable().ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                title: 'Warning!',
                                text: res.message,
                                icon: 'warning',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function() {
                        $('.loader').hide();
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the sales order.',
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    }
</script>
