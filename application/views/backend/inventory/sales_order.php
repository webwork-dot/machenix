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

<?php
    include('filter/date_range.php');
    $staff_access = (int)$this->session->userdata('super_type_id');
    $export_cols = '[' . implode(',', range(0, 11)) . ']';
?>
	
<div class="row" id="table-bordered">
    <?php include('nav/nav_sales_order.php'); ?>

   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0;">
         <div class="card-body pb-0">
             <div class="d-flex align-items-center flex-wrap gap-1 so-table-title-row">
                   <h5 class="mb-0">
                       <b>Total Sales Order <span id="total_count"> (0)</span></b>
				  </h5>

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
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <?php if($this->session->userdata('super_type_id') == 7) { ?>
                <a href="<?php echo site_url('inventory/sales-order/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary so-add-btn" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } elseif ($staff_access !== 7) { ?>
                <a href="<?php echo site_url('inventory/sales-invoice/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary so-add-btn" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } ?>
     
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
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
					<th>Status</th>
					<th>Actions</th>
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
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
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
                { "data": "status" },
                { "data": "action" }
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
                 return 'Showing ' +start+ ' to ' + end + ' of '+ total + ' entries';
             }, 
			createdRow: function (row, data, index) {
                   if(data['error']=='1'){
                    $(row).addClass('table-error');
                   }
            },
           
            'columnDefs': [
                {
                    "targets": [0, 1, 2, 3, 7, 8, 9, 11],
                    "className": "text-center",
                }
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });

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
