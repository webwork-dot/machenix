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
    .so-table-title-row {
        gap: 10px;
        margin-bottom: 4px;
    }
</style>

<?php include('filter/sales_order_invoice_filter.php'); ?>
	
<div class="row" id="table-bordered">
    <?php include('nav/nav_sales_order.php'); ?>

   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0;">
         <div class="card-body pb-0">
             <div class="d-flex align-items-center flex-wrap gap-1 so-table-title-row">
                   <h5 class="mb-0">
                       <b>Total Sales Order <span id="total_count"> (0)</span></b>
				  </h5>
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<?php if (is_access('240')) { ?><th>Date</th><?php } ?>
					<?php if (is_access('241')) { ?><th>Customer Name</th><?php } ?>
					<?php if (is_access('242')) { ?><th>Order NO</th><?php } ?>
					<?php if (is_access('243')) { ?><th>Warehouse</th><?php } ?>
					<?php if (is_access('244')) { ?><th>Total Qty</th><?php } ?>
					<?php if (is_access('245')) { ?><th>Total Products</th><?php } ?>
					<?php if (is_access('246')) { ?><th>Total Amount</th><?php } ?>
					<?php if (is_access('247')) { ?><th>Added By</th><?php } ?>
					<?php if (is_access('248') || is_access('249') || is_access('250') || is_access('251') || is_access('252') || is_access('253')) { ?><th>Actions</th><?php } ?>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">
    $(document).ready(function($) {
    	$('#report-datatable').DataTable({ 
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
                "url": "<?php echo base_url('inventory/get_sales_order'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.delivery_date_range = '<?php echo (isset($_GET['delivery_date_range'])) ? $_GET['delivery_date_range']:'' ?>';
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = 'invoice';	
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
                <?php if (is_access('240')) { ?>{ "data": "date" },<?php } ?>
                <?php if (is_access('241')) { ?>{ "data": "customer_name" },<?php } ?>
                <?php if (is_access('242')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('243')) { ?>{ "data": "warehouse_name" },<?php } ?>
                <?php if (is_access('244')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('245')) { ?>{ "data": "total_pro" },<?php } ?>
                <?php if (is_access('246')) { ?>{ "data": "grand_total" },<?php } ?>
                <?php if (is_access('247')) { ?>{ "data": "added_by" },<?php } ?>
                <?php if (is_access('248') || is_access('249') || is_access('250') || is_access('251') || is_access('252') || is_access('253')) { ?>{ "data": "action" },<?php } ?>
             ],
            
             "buttons": [
                 <?php if (is_access('254')) { ?>
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
           
            'columnDefs': [
                {
                    "targets": 0,
                    "className": "text-center",
                }
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
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
