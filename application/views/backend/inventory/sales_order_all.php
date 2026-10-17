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
    include('filter/date_range.php');
    $staff_access = (int)$this->session->userdata('super_type_id');
    $sub_tab = (isset($_GET['sub_tab']) && $_GET['sub_tab'] != '') ? $_GET['sub_tab'] : 'order';

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

    if ($sub_tab == 'product') {
        $num_cols = 23;
        $export_cols = '[' . implode(',', range(0, $num_cols - 1)) . ']';
    } else {
        $export_cols = "':visible:not(:last-child)'";
    }
?>
	
<div class="row" id="table-bordered">
    <?php include('nav/nav_sales_order.php'); ?>

    <div class="col-md-12 mb-1">
        <div class="fixedElement subtabs-bar">
            <ul class="nav nav-pills subtabs-nav">
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order-all?sub_tab=order<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab != 'product') ? 'active' : ''; ?>">
                        <i class="fa fa-list me-1"></i> Order Wise
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order-all?sub_tab=product<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab == 'product') ? 'active' : ''; ?>">
                        <i class="fa fa-cubes me-1"></i> Product Wise
                    </a>
                </li>
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
                   <?php if ($sub_tab != 'product' && $staff_access !== 7) { ?>
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
                            <label class="filter-chip" for="toggle-ow-bill-date" title="Toggle Bill Date column">
                                <input type="checkbox" id="toggle-ow-bill-date" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Bill Date</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-delivery-date" title="Toggle Delivery Date column">
                                <input type="checkbox" id="toggle-ow-delivery-date" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Delivery Date</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-inv-no" title="Toggle Inv No column">
                                <input type="checkbox" id="toggle-ow-inv-no" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Inv No</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-warehouse" title="Toggle Warehouse column">
                                <input type="checkbox" id="toggle-ow-warehouse" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Warehouse</span>
                            </label>

                            <div class="filter-menu-label">Product & Item Details</div>
                            <label class="filter-chip active" for="toggle-ow-batch-no" title="Toggle Batch No column">
                                <input type="checkbox" id="toggle-ow-batch-no" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Batch No</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-product" title="Toggle Product column">
                                <input type="checkbox" id="toggle-ow-product" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Product</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-model-no" title="Toggle Model No column">
                                <input type="checkbox" id="toggle-ow-model-no" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Model No</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-qty" title="Toggle Qty column">
                                <input type="checkbox" id="toggle-ow-qty" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Qty</span>
                            </label>

                            <div class="filter-menu-label">Rates & Billing</div>
                            <label class="filter-chip active" for="toggle-ow-rate" title="Toggle Rate column">
                                <input type="checkbox" id="toggle-ow-rate" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Rate</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-tot-rate" title="Toggle Tot Rate column">
                                <input type="checkbox" id="toggle-ow-tot-rate" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Rate</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-bill-amt" title="Toggle Bill Amt column">
                                <input type="checkbox" id="toggle-ow-bill-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Bill Amt</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-taxable-amt" title="Toggle Tot Taxable Amt column">
                                <input type="checkbox" id="toggle-ow-taxable-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Taxable Amt</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-cgst" title="Toggle CGST column">
                                <input type="checkbox" id="toggle-ow-cgst" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">CGST</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-sgst" title="Toggle SGST column">
                                <input type="checkbox" id="toggle-ow-sgst" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">SGST</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-igst" title="Toggle IGST column">
                                <input type="checkbox" id="toggle-ow-igst" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">IGST</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-inv-amt" title="Toggle Inv Amt column">
                                <input type="checkbox" id="toggle-ow-inv-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Inv Amt</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-cash" title="Toggle Cash column">
                                <input type="checkbox" id="toggle-ow-cash" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Cash</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-tot-cash" title="Toggle Tot Cash column">
                                <input type="checkbox" id="toggle-ow-tot-cash" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Cash</span>
                            </label>
                            <label class="filter-chip active" for="toggle-ow-final-amt" title="Toggle Final Amt column">
                                <input type="checkbox" id="toggle-ow-final-amt" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Final Amt</span>
                            </label>

                            <div class="filter-menu-label">Sales Person & Commission</div>
                            <label class="filter-chip active" for="toggle-ow-sales-person" title="Toggle Sales Person column">
                                <input type="checkbox" id="toggle-ow-sales-person" class="ow-column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Sales Person</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-comm-amt" title="Toggle Comm Amt column">
                                <input type="checkbox" id="toggle-ow-comm-amt" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Comm Amt</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-tot-comm-amt" title="Toggle Tot Comm Amt column">
                                <input type="checkbox" id="toggle-ow-tot-comm-amt" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Comm Amt</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-comm-per" title="Toggle Comm % column">
                                <input type="checkbox" id="toggle-ow-comm-per" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Comm %</span>
                            </label>

                            <div class="filter-menu-label">Cost & Profit</div>
                            <label class="filter-chip" for="toggle-ow-act-cost-per-pc" title="Toggle Act Cst Per Pc With Expense column">
                                <input type="checkbox" id="toggle-ow-act-cost-per-pc" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Act Cst Per Pc With Expense</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-tot-act-cost" title="Toggle Tot Act Cst With Expense column">
                                <input type="checkbox" id="toggle-ow-tot-act-cost" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Act Cst With Expense</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-profit-amt-per-pc" title="Toggle Profit Amt Per Pc column">
                                <input type="checkbox" id="toggle-ow-profit-amt-per-pc" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Profit Amt Per Pc</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-total-profit" title="Toggle Total Profit column">
                                <input type="checkbox" id="toggle-ow-total-profit" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Total Profit</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-tot-profit-per" title="Toggle Tot Profit % column">
                                <input type="checkbox" id="toggle-ow-tot-profit-per" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Tot Profit %</span>
                            </label>
                            <label class="filter-chip" for="toggle-ow-added-by" title="Toggle Added By column">
                                <input type="checkbox" id="toggle-ow-added-by" class="ow-column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Added By</span>
                            </label>
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
					<th>Batch No</th>
					<th>Order No</th>
					<th>Order Date</th>
					<th>Party Name</th>
					<th>Product Name</th>
					<th>Model No.</th>
					<th>Qty</th>
					<th>Rate</th>
					<th>Bill amt</th>
					<th>Total Taxable Amt</th>
					<th>CGST</th>
					<th>SGST</th>
					<th>IGST</th>
					<th>Total Bill Amt</th>
					<th>Cash Amt</th>
					<th>Total Cash Amt</th>
					<th>Total Amt</th>
					<th>Comm Amt</th>
					<th>Comm Name</th>
					<th>Profit</th>
					<th>Act Cst With Expense/Pc</th>
					<th>Total Cst With Expense</th>
                  <?php } else { ?>
					<th>#</th>
					<th>Bill Date</th>
					<th>Delivery Date</th>
					<th>Inv No</th>
					<th>Customer Name</th>
					<th>Order No</th>
					<th>Warehouse</th>
					<th>Batch No</th>
					<th>Product Name</th>
					<th>Model No.</th>
					<th>Qty</th>
					<th>Rate</th>
					<th>Tot Rate</th>
					<th>Bill Amt</th>
					<th>Taxable Amt</th>
					<th>CGST</th>
					<th>SGST</th>
					<th>IGST</th>
					<th>Inv Amt</th>
					<th>Cash</th>
					<th>Tot Cash Amt</th>
					<th>Final Amt</th>
					<th>Comm Amt</th>
					<th>Tot Comm Amt</th>
					<th>Comm %</th>
					<th>Sales Person</th>
					<th>Act Cst Per Pc With Expense</th>
					<th>Tot Act Cst With Expense</th>
					<th>Profit Amt Per Pc</th>
					<th>Total Profit</th>
					<th>Tot Profit %</th>
					<th>Added By</th>
					<th>ACTIONS</th>
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
                { "data": "batch_no" },
                { "data": "order_no" },
                { "data": "order_date" },
                { "data": "party_name" },
                { "data": "product_name" },
                { "data": "model_no" },
                { "data": "qty" },
                { "data": "rate" },
                { "data": "bill_amt" },
                { "data": "total_taxable_amt" },
                { "data": "cgst" },
                { "data": "sgst" },
                { "data": "igst" },
                { "data": "total_bill_amt" },
                { "data": "cash_amt" },
                { "data": "total_cash_amt" },
                { "data": "total_amt" },
                { "data": "comm_amt" },
                { "data": "comm_name" },
                { "data": "profit" },
                { "data": "act_cost_with_exp" },
                { "data": "total_cost_with_exp" }
                <?php } else { ?>
                { "data": "sr_no" },
                { "data": "bill_date", "visible": false },
                { "data": "delivery_date", "visible": false },
                { "data": "inv_no", "visible": false },
                { "data": "customer_name" },
                { "data": "order_no" },
                { "data": "warehouse_name", "visible": false },
                { "data": "batch_no" },
                { "data": "product_name" },
                { "data": "model_no" },
                { "data": "qty" },
                { "data": "rate" },
                { "data": "tot_rate" },
                { "data": "bill_amt" },
                { "data": "taxable_amt" },
                { "data": "cgst" },
                { "data": "sgst" },
                { "data": "igst" },
                { "data": "inv_amt" },
                { "data": "cash" },
                { "data": "tot_cash_amt" },
                { "data": "final_amt" },
                { "data": "comm_amt", "visible": false },
                { "data": "tot_comm_amt", "visible": false },
                { "data": "comm_per", "visible": false },
                { "data": "sales_person" },
                { "data": "act_cost_per_pc", "visible": false },
                { "data": "tot_act_cost", "visible": false },
                { "data": "profit_amt_per_pc", "visible": false },
                { "data": "total_profit", "visible": false },
                { "data": "tot_profit_per", "visible": false },
                { "data": "added_by", "visible": false },
                { "data": "action" }
                <?php } ?>
             ],
            
             "buttons": [
                 {
                     "extend": 'excel',
                     "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                     "exportOptions": {
                        "columns": <?php echo $export_cols; ?>
                     }
                 },
                 {
                     "extend": 'pdfHtml5',
                     "orientation": 'landscape',
                     "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                     "exportOptions": {
                        "columns": <?php echo $export_cols; ?>
                     }
                 }
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
           
            'columnDefs': [
                <?php if ($sub_tab == 'product') { ?>
                {
                    "targets": [0, 1, 2, 3, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
                    "className": "text-center",
                }
                <?php } else { ?>
                {
                    "targets": [0, 1, 2, 3, 5, 7, 9, 10, 15, 16, 17, 24, 28, 29, 30, 32],
                    "className": "text-center",
                },
                {
                    "targets": [11, 12, 13, 14, 18, 19, 20, 21, 22, 23, 26, 27],
                    "className": "text-end",
                }
                <?php } ?>
            ] 
            
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
                if ($('#' + id).is(':checked') !== defaultOn) {
                    count++;
                }
            });
            var $badge = $('#orderwise-filter-active-count');
            $badge.text(count);
            $badge.toggleClass('has-count', count > 0);
        }

        function applyOrderWiseColumnFilters() {
            var showBillDate = $('#toggle-ow-bill-date').is(':checked');
            var showDeliveryDate = $('#toggle-ow-delivery-date').is(':checked');
            var showInvNo = $('#toggle-ow-inv-no').is(':checked');
            var showWarehouse = $('#toggle-ow-warehouse').is(':checked');
            var showBatchNo = $('#toggle-ow-batch-no').is(':checked');
            var showProduct = $('#toggle-ow-product').is(':checked');
            var showModelNo = $('#toggle-ow-model-no').is(':checked');
            var showQty = $('#toggle-ow-qty').is(':checked');
            var showRate = $('#toggle-ow-rate').is(':checked');
            var showTotRate = $('#toggle-ow-tot-rate').is(':checked');
            var showBillAmt = $('#toggle-ow-bill-amt').is(':checked');
            var showTaxableAmt = $('#toggle-ow-taxable-amt').is(':checked');
            var showCgst = $('#toggle-ow-cgst').is(':checked');
            var showSgst = $('#toggle-ow-sgst').is(':checked');
            var showIgst = $('#toggle-ow-igst').is(':checked');
            var showInvAmt = $('#toggle-ow-inv-amt').is(':checked');
            var showCash = $('#toggle-ow-cash').is(':checked');
            var showTotCash = $('#toggle-ow-tot-cash').is(':checked');
            var showFinalAmt = $('#toggle-ow-final-amt').is(':checked');
            var showCommAmt = $('#toggle-ow-comm-amt').is(':checked');
            var showTotCommAmt = $('#toggle-ow-tot-comm-amt').is(':checked');
            var showCommPer = $('#toggle-ow-comm-per').is(':checked');
            var showSalesPerson = $('#toggle-ow-sales-person').is(':checked');
            var showActCostPerPc = $('#toggle-ow-act-cost-per-pc').is(':checked');
            var showTotActCost = $('#toggle-ow-tot-act-cost').is(':checked');
            var showProfitAmtPerPc = $('#toggle-ow-profit-amt-per-pc').is(':checked');
            var showTotalProfit = $('#toggle-ow-total-profit').is(':checked');
            var showTotProfitPer = $('#toggle-ow-tot-profit-per').is(':checked');
            var showAddedBy = $('#toggle-ow-added-by').is(':checked');

            dataTable.column(1).visible(showBillDate, false);
            dataTable.column(2).visible(showDeliveryDate, false);
            dataTable.column(3).visible(showInvNo, false);
            dataTable.column(6).visible(showWarehouse, false);
            dataTable.column(7).visible(showBatchNo, false);
            dataTable.column(8).visible(showProduct, false);
            dataTable.column(9).visible(showModelNo, false);
            dataTable.column(10).visible(showQty, false);
            dataTable.column(11).visible(showRate, false);
            dataTable.column(12).visible(showTotRate, false);
            dataTable.column(13).visible(showBillAmt, false);
            dataTable.column(14).visible(showTaxableAmt, false);
            dataTable.column(15).visible(showCgst, false);
            dataTable.column(16).visible(showSgst, false);
            dataTable.column(17).visible(showIgst, false);
            dataTable.column(18).visible(showInvAmt, false);
            dataTable.column(19).visible(showCash, false);
            dataTable.column(20).visible(showTotCash, false);
            dataTable.column(21).visible(showFinalAmt, false);
            dataTable.column(22).visible(showCommAmt, false);
            dataTable.column(23).visible(showTotCommAmt, false);
            dataTable.column(24).visible(showCommPer, false);
            dataTable.column(25).visible(showSalesPerson, false);
            dataTable.column(26).visible(showActCostPerPc, false);
            dataTable.column(27).visible(showTotActCost, false);
            dataTable.column(28).visible(showProfitAmtPerPc, false);
            dataTable.column(29).visible(showTotalProfit, false);
            dataTable.column(30).visible(showTotProfitPer, false);
            dataTable.column(31).visible(showAddedBy, false);

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
