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
	.small-img{
		max-height: 50px;
		min-height: 50px;
		object-fit: cover;
		border-radius: 10px;
		border: 1px solid #e7e6e6;
		height: 50px;
		max-width: 60px;
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
    .card-datatable.d-report .dataTables_wrapper .dataTables_filter {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }
    .card-datatable.d-report .dataTables_wrapper .dataTables_filter label {
        margin: 0;
        display: inline-flex;
        align-items: center;
    }
    .card-datatable.d-report .so-add-btn {
        float: none !important;
        margin: 0 !important;
        margin-top: 0 !important;
        margin-right: 0 !important;
        position: static;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
</style>

<?php
    // echo json_encode($this->session->userdata());
    include('filter/date_range.php');
    $staff_access = (int)$this->session->userdata('super_type_id');

    $status = (isset($_GET['status']) && $_GET['status'] != '') ? $_GET['status'] : 'pending';
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
?>
	
<div class="row" id="table-bordered">
    
    <div class="col-md-12 mb-1">
        <div class="fixedElement" id="fixedElement">
            <ul class="nav nav-pills bg-nav-pills nav-justified ">
                
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=pending<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'pending') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Pending</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=invoice<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'invoice') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Generate Invoice</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=complete<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'complete') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Complete</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=all<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'all') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">All Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=cancelled<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'cancelled') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Cancelled</span>
                    </a>
                </li>
                
            </ul>
        </div>
    </div>

    <?php if ($status == 'all') { ?>
        <div class="col-md-12 mb-1">
            <div class="fixedElement subtabs-bar">
                <ul class="nav nav-pills subtabs-nav">
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=all&sub_tab=order<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab != 'product') ? 'active' : ''; ?>">
                            <i class="fa fa-list me-1"></i> Order Wise
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=all&sub_tab=product<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab == 'product') ? 'active' : ''; ?>">
                            <i class="fa fa-cubes me-1"></i> Product Wise
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    <?php } ?>

   <div class="col-12">
      <div class="card">
         <div class="card-body pb-0">
             <div class="d-flex align-items-center flex-wrap gap-1 so-table-title-row">
                   <h5 class="mb-0">
                   <?php if ($status == 'all' && $sub_tab == 'product') { ?>
                       <b>Total Product Batches <span id="total_count"> (0)</span></b>
                   <?php } elseif ($status == 'cancelled') { ?>
                       <b>Total Cancelled <span id="total_count"> (0)</span></b>
                   <?php } else { ?>
                       <b>Total Sales Order <span id="total_count"> (0)</span></b>
                   <?php } ?>
                   <?php if (($status == 'complete' || $status == 'cancelled') && $staff_access !== 7) { ?>
                      &nbsp;|&nbsp; <b>Total Amount: ₹<span id="total_sales_amount">0.00</span></b>
                   <?php } ?>
				  </h5>

            <?php if ($status == 'pending') { ?>
                <div class="dropdown stock-filter-dropdown ms-50">
                    <button class="btn-filter-dropdown dropdown-toggle" type="button" id="soFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="feather icon-filter"></i>
                        <span>Filters</span>
                        <span class="filter-count-badge" id="filter-active-count">0</span>
                    </button>
                    <div class="dropdown-menu stock-filter-menu" aria-labelledby="soFilterDropdown">
                        <div class="filter-menu-header">
                            <span class="title">Table Filters</span>
                        </div>
                        <div class="filter-menu-body" id="column-filters-container">
                            <div class="filter-menu-label">Columns</div>
                            <label class="filter-chip active" for="toggle-booking-date" title="Toggle Booking Date column">
                                <input type="checkbox" id="toggle-booking-date" class="column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Booking Date</span>
                            </label>
                            <label class="filter-chip active" for="toggle-delivery-date" title="Toggle Delivery Date column">
                                <input type="checkbox" id="toggle-delivery-date" class="column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Delivery Date</span>
                            </label>
                            <label class="filter-chip" for="toggle-warehouse" title="Toggle Warehouse column">
                                <input type="checkbox" id="toggle-warehouse" class="column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Warehouse</span>
                            </label>
                            <label class="filter-chip active" for="toggle-sales-person" title="Toggle Sales Person column">
                                <input type="checkbox" id="toggle-sales-person" class="column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Sales Person</span>
                            </label>
                        </div>
                        <div class="filter-menu-footer">
                            <button type="button" class="btn-reset-filters" id="btn-reset-col-filters" title="Reset column filters to default">
                                <i class="feather icon-rotate-ccw"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <?php if($status == 'pending' && $this->session->userdata('super_type_id') == 7) { ?>
                <a href="<?php echo site_url('inventory/sales-order/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary so-add-btn" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } elseif ($status == 'pending' && $staff_access !== 7) { ?>
                <a href="<?php echo site_url('inventory/sales-invoice/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary so-add-btn" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } ?>
     
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
                  <?php if ($status == 'all' && $sub_tab == 'product') { ?>
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
                  <?php } elseif ($status == 'pending') { ?>
					<th>#</th>
					<th>Booking Date</th>
					<th>Delivery Date</th>
					<th>Booking Days</th>
					<th>Customer Name</th>
					<th>Order No</th>
					<th>Warehouse</th>
					<th>Tot Qty</th>
					<th>Tot Product</th>
					<th>Tot Amt</th>
					<th>Sales Person</th>
					<th>Actions</th>
                  <?php } elseif ($status == 'cancelled') { ?>
					<th>#</th>
					<th>Date</th>
					<th>Type</th>
					<th>Invoice No</th>
					<th>Invoice Date</th>
					<th>Customer Name</th>
					<th>Order NO</th>
					<th>Warehouse</th>
					<th>Total Qty</th>
					<th>Total Products</th>
					<th>Total Amount</th>
                    <?php if ($staff_access !== 7) { ?>
                        <th>Added By</th>
                    <?php } ?>
                    <th>Actions</th>
                  <?php } else { ?>
					<th>#</th>
					<th>Date</th>
                    <?php if ($status == 'complete') { ?>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <?php } ?>
					<th>Customer Name</th>
					<th>Order NO</th>
					<th>Warehouse</th>
					<th>Total Qty</th>
					<th>Total Products</th>
					<th>Total Amount</th>
                    <?php if ($staff_access !== 7) { ?>
                        <th>Added By</th>
                    <?php } ?>
                    <th>Actions</th>
                  <?php } ?>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">
<?php
if ($status == 'all' && $sub_tab == 'product') {
    $num_cols = 23;
} elseif ($status == 'pending') {
    $num_cols = 11; // exclude Actions from export
} elseif ($status == 'cancelled') {
    $num_cols = 11;
    if ($staff_access !== 7) {
        $num_cols += 1;
    }
} else {
    $num_cols = 8;
    if ($status == 'complete') {
        $num_cols += 2;
    }
    if ($staff_access !== 7) {
        $num_cols += 1;
    }
}
$export_cols = '[' . implode(',', range(0, $num_cols - 1)) . ']';
?>
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
            "initComplete": function () {
                var $addBtn = $('.card-datatable.d-report .so-add-btn').first();
                var $filter = $('#report-datatable_wrapper .dataTables_filter');
                if ($addBtn.length && $filter.length) {
                    $filter.append($addBtn);
                }
            },
      
            "ajax":{
                <?php if ($status == 'all' && $sub_tab == 'product') { ?>
                "url": "<?php echo base_url('inventory/get_sales_order_product_wise'); ?>",
                <?php } elseif ($status == 'cancelled') { ?>
                "url": "<?php echo base_url('inventory/get_cancelled_sales_order'); ?>",
                <?php } elseif ($status != 'complete') { ?>
                "url": "<?php echo base_url('inventory/get_sales_order'); ?>",
                <?php } else { ?>
                "url": "<?php echo base_url('inventory/get_completed_sales_order'); ?>",
                <?php } ?>
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.delivery_date_range = '<?php echo (isset($_GET['delivery_date_range'])) ? $_GET['delivery_date_range']:'' ?>';
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = '<?php echo (isset($_GET['status'])) ? $_GET['status']: 'pending'; ?>';	
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                <?php if ($status == 'all' && $sub_tab == 'product') { ?>
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
                <?php } elseif ($status == 'pending') { ?>
                { "data": "sr_no" },
                { "data": "booking_date" },
                { "data": "delivery_date" },
                { "data": "booking_days" },
                { "data": "customer_name" },
                { "data": "order_no" },
                { "data": "warehouse_name", "visible": false },
                { "data": "qty" },
                { "data": "total_pro" },
                { "data": "grand_total" },
                { "data": "sales_person" },
                { "data": "action" }
                <?php } elseif ($status == 'cancelled') { ?>
                { "data": "sr_no" },
                { "data": "date" },
                { "data": "type" },
                { "data": "invoice_no" },
                { "data": "invoice_date" },
                { "data": "customer_name" },
                { "data": "order_no" },
                { "data": "warehouse_name" },
                { "data": "qty" },
                { "data": "total_pro" },
                { "data": "grand_total" },
                 <?php if ($staff_access !== 7) { ?>
                 { "data": "added_by" },
                 <?php } ?>
                 { "data": "action" }
                <?php } else { ?>
                { "data": "sr_no" },
                { "data": "date" },
                <?php if ($status == 'complete') { ?>
                { "data": "invoice_no" },
                { "data": "invoice_date" },
                <?php } ?>
                { "data": "customer_name" },
                { "data": "order_no" },
                { "data": "warehouse_name" },
                { "data": "qty" },
                { "data": "total_pro" },
                { "data": "grand_total" },
                 <?php if ($staff_access !== 7) { ?>
                 { "data": "added_by" },
                 <?php } ?>
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
                <?php if ($status == 'all' && $sub_tab == 'product') { ?>
                {
                    "targets": [0, 1, 2, 3, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
                    "className": "text-center",
                }
                <?php } elseif ($status == 'pending') { ?>
                {
                    "targets": [0, 1, 2, 3, 7, 8, 9],
                    "className": "text-center",
                }
                <?php } elseif ($status == 'cancelled') { ?>
                {
                    "targets": [0, 2],
                    "className": "text-center",
                }
                <?php } else { ?>
                {
                    "targets": 0,
                    "className": "text-center",
                }
                <?php } ?>
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });

        <?php if ($status == 'pending') { ?>
        function updateFilterCount() {
            var defaults = {
                'toggle-booking-date': true,
                'toggle-delivery-date': true,
                'toggle-warehouse': false,
                'toggle-sales-person': true
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
            var showBooking = $('#toggle-booking-date').is(':checked');
            var showDelivery = $('#toggle-delivery-date').is(':checked');
            var showWarehouse = $('#toggle-warehouse').is(':checked');
            var showSalesPerson = $('#toggle-sales-person').is(':checked');

            dataTable.column(1).visible(showBooking, false);
            dataTable.column(2).visible(showDelivery, false);
            dataTable.column(6).visible(showWarehouse, false);
            dataTable.column(10).visible(showSalesPerson, false);
            dataTable.columns.adjust();
            updateFilterCount();
        }

        $('.column-filter-checkbox').on('change', function() {
            var isChecked = $(this).is(':checked');
            $(this).closest('.filter-chip').toggleClass('active', isChecked);
            applyColumnFilters();
        });

        $('#btn-reset-col-filters').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#toggle-booking-date').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-delivery-date').prop('checked', true).closest('.filter-chip').addClass('active');
            $('#toggle-warehouse').prop('checked', false).closest('.filter-chip').removeClass('active');
            $('#toggle-sales-person').prop('checked', true).closest('.filter-chip').addClass('active');
            applyColumnFilters();
        });

        updateFilterCount();
        <?php } ?>
    });

    function deleteSalesInvoice(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the sales invoice and revert the received quantities in the sales order batches!",
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
                    url: "<?php echo base_url('inventory/sales_invoice_delete/'); ?>" + id,
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
                            text: 'An error occurred while deleting the sales invoice.',
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

    function cancelSalesInvoice(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will mark the sales invoice as cancelled and revert the received quantities in the sales order batches!",
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
                    url: "<?php echo base_url('inventory/sales_invoice_cancel/'); ?>" + id,
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
                            text: 'An error occurred while cancelling the sales invoice.',
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