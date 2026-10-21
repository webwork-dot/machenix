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

<?php include('filter/sales_order_filter.php'); ?>
	
<div class="row" id="table-bordered">
    <?php include('nav/nav_sales_order.php'); ?>

   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0;">
         <div class="card-body pb-0">
             <div class="d-flex align-items-center flex-wrap gap-1 so-table-title-row">
                   <h5 class="mb-0">
                       <b>Total Sales Order <span id="total_count"> (0)</span></b>
				  </h5>

                <?php if (is_access('217') || is_access('218') || is_access('222') || is_access('226')) { ?>
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
                            <?php if (is_access('217')) { ?>
                            <label class="filter-chip active" for="toggle-booking-date" title="Toggle Booking Date column">
                                <input type="checkbox" id="toggle-booking-date" class="column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Booking Date</span>
                            </label>
                            <?php } ?>
                            <?php if (is_access('218')) { ?>
                            <label class="filter-chip active" for="toggle-delivery-date" title="Toggle Delivery Date column">
                                <input type="checkbox" id="toggle-delivery-date" class="column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Delivery Date</span>
                            </label>
                            <?php } ?>
                            <?php if (is_access('222')) { ?>
                            <label class="filter-chip" for="toggle-warehouse" title="Toggle Warehouse column">
                                <input type="checkbox" id="toggle-warehouse" class="column-filter-checkbox">
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Warehouse</span>
                            </label>
                            <?php } ?>
                            <?php if (is_access('226')) { ?>
                            <label class="filter-chip active" for="toggle-sales-person" title="Toggle Sales Person column">
                                <input type="checkbox" id="toggle-sales-person" class="column-filter-checkbox" checked>
                                <span class="chip-box"><i class="feather icon-check"></i></span>
                                <span class="chip-text">Sales Person</span>
                            </label>
                            <?php } ?>
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
            <?php if (is_access('228')) { ?>
                <a href="<?php echo site_url('inventory/sales-order/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary so-add-btn" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } elseif (is_access('229')) { ?>
                <a href="<?php echo site_url('inventory/sales-invoice/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary so-add-btn" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } ?>
     
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<?php if (is_access('217')) { ?><th>Booking Date</th><?php } ?>
					<?php if (is_access('218')) { ?><th>Delivery Date</th><?php } ?>
					<?php if (is_access('219')) { ?><th>Booking Days</th><?php } ?>
					<?php if (is_access('220')) { ?><th>Customer Name</th><?php } ?>
					<?php if (is_access('221')) { ?><th>Order No</th><?php } ?>
					<?php if (is_access('222')) { ?><th>Warehouse</th><?php } ?>
					<?php if (is_access('223')) { ?><th>Tot Qty</th><?php } ?>
					<?php if (is_access('224')) { ?><th>Tot Product</th><?php } ?>
					<?php if (is_access('225')) { ?><th>Tot Amt</th><?php } ?>
					<?php if (is_access('226')) { ?><th>Sales Person</th><?php } ?>
					<?php if (is_access('227')) { ?><th>Status</th><?php } ?>
					<?php if (is_access('230') || is_access('231') || is_access('232') || is_access('233') || is_access('234') || is_access('235') || is_access('236') || is_access('237') || is_access('238')) { ?><th>Actions</th><?php } ?>
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
            "initComplete": function () {
                var $addBtn = $('.card-datatable.d-report .so-add-btn').first();
                var $filter = $('#report-datatable_wrapper .dataTables_filter');
                if ($addBtn.length && $filter.length) {
                    $filter.append($addBtn);
                }
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_sales_order'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.delivery_date_range = '<?php echo (isset($_GET['delivery_date_range'])) ? $_GET['delivery_date_range']:'' ?>';
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = 'pending';
                    data.datatype = <?php echo is_access('910') ? '1' : '0'; ?>;
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                { "data": "sr_no", "className": "text-center" },
                <?php if (is_access('217')) { ?>{ "data": "booking_date", "className": "text-center" },<?php } ?>
                <?php if (is_access('218')) { ?>{ "data": "delivery_date", "className": "text-center" },<?php } ?>
                <?php if (is_access('219')) { ?>{ "data": "booking_days", "className": "text-center" },<?php } ?>
                <?php if (is_access('220')) { ?>{ "data": "customer_name" },<?php } ?>
                <?php if (is_access('221')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('222')) { ?>{ "data": "warehouse_name", "visible": false },<?php } ?>
                <?php if (is_access('223')) { ?>{ "data": "qty", "className": "text-center" },<?php } ?>
                <?php if (is_access('224')) { ?>{ "data": "total_pro", "className": "text-center" },<?php } ?>
                <?php if (is_access('225')) { ?>{ "data": "grand_total", "className": "text-center" },<?php } ?>
                <?php if (is_access('226')) { ?>{ "data": "sales_person" },<?php } ?>
                <?php if (is_access('227')) { ?>{ "data": "status", "className": "text-center" },<?php } ?>
                <?php if (is_access('230') || is_access('231') || is_access('232') || is_access('233') || is_access('234') || is_access('235') || is_access('236') || is_access('237') || is_access('238')) { ?>{ "data": "action" },<?php } ?>
             ],
            
             "buttons": [
                 <?php if (is_access('239')) { ?>
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

        <?php
            // Column indices for toggles (0 = '#')
            $ci = 0;
            $col_booking = $col_delivery = $col_warehouse = $col_sales = 0;
            if (is_access('217')) { $ci++; $col_booking = $ci; }
            if (is_access('218')) { $ci++; $col_delivery = $ci; }
            if (is_access('219')) { $ci++; }
            if (is_access('220')) { $ci++; }
            if (is_access('221')) { $ci++; }
            if (is_access('222')) { $ci++; $col_warehouse = $ci; }
            if (is_access('223')) { $ci++; }
            if (is_access('224')) { $ci++; }
            if (is_access('225')) { $ci++; }
            if (is_access('226')) { $ci++; $col_sales = $ci; }
        ?>
        function updateFilterCount() {
            var defaults = {
                <?php if (is_access('217')) { ?>'toggle-booking-date': true,<?php } ?>
                <?php if (is_access('218')) { ?>'toggle-delivery-date': true,<?php } ?>
                <?php if (is_access('222')) { ?>'toggle-warehouse': false,<?php } ?>
                <?php if (is_access('226')) { ?>'toggle-sales-person': true<?php } ?>
            };
            var count = 0;
            $.each(defaults, function (id, defaultOn) {
                if ($('#' + id).length && $('#' + id).is(':checked') !== defaultOn) {
                    count++;
                }
            });
            var $badge = $('#filter-active-count');
            $badge.text(count);
            $badge.toggleClass('has-count', count > 0);
        }

        function applyColumnFilters() {
            <?php if (is_access('217')) { ?>dataTable.column(<?php echo $col_booking; ?>).visible($('#toggle-booking-date').is(':checked'), false);<?php } ?>
            <?php if (is_access('218')) { ?>dataTable.column(<?php echo $col_delivery; ?>).visible($('#toggle-delivery-date').is(':checked'), false);<?php } ?>
            <?php if (is_access('222')) { ?>dataTable.column(<?php echo $col_warehouse; ?>).visible($('#toggle-warehouse').is(':checked'), false);<?php } ?>
            <?php if (is_access('226')) { ?>dataTable.column(<?php echo $col_sales; ?>).visible($('#toggle-sales-person').is(':checked'), false);<?php } ?>
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
            <?php if (is_access('217')) { ?>$('#toggle-booking-date').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
            <?php if (is_access('218')) { ?>$('#toggle-delivery-date').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
            <?php if (is_access('222')) { ?>$('#toggle-warehouse').prop('checked', false).closest('.filter-chip').removeClass('active');<?php } ?>
            <?php if (is_access('226')) { ?>$('#toggle-sales-person').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
            applyColumnFilters();
        });

        updateFilterCount();
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
