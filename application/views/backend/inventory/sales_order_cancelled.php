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

<?php include('filter/sales_order_cancelled_filter.php'); ?>
	
<div class="row" id="table-bordered">
    <?php include('nav/nav_sales_order.php'); ?>

   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0;">
         <div class="card-body pb-0">
             <div class="d-flex align-items-center flex-wrap gap-1 so-table-title-row">
                   <h5 class="mb-0">
                       <b>Total Cancelled <span id="total_count"> (0)</span></b>
                   <?php if (is_access('280')) { ?>
                      &nbsp;|&nbsp; <b>Total Amount: ₹<span id="total_sales_amount">0.00</span></b>
                   <?php } ?>
				  </h5>
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<?php if (is_access('271')) { ?><th>Date</th><?php } ?>
					<?php if (is_access('272')) { ?><th>Type</th><?php } ?>
					<?php if (is_access('273')) { ?><th>Invoice No</th><?php } ?>
					<?php if (is_access('274')) { ?><th>Invoice Date</th><?php } ?>
					<?php if (is_access('275')) { ?><th>Customer Name</th><?php } ?>
					<?php if (is_access('276')) { ?><th>Order NO</th><?php } ?>
					<?php if (is_access('277')) { ?><th>Warehouse</th><?php } ?>
					<?php if (is_access('278')) { ?><th>Total Qty</th><?php } ?>
					<?php if (is_access('279')) { ?><th>Total Products</th><?php } ?>
					<?php if (is_access('280')) { ?><th>Total Amount</th><?php } ?>
					<?php if (is_access('281')) { ?><th>Added By</th><?php } ?>
					<?php if (is_access('282') || is_access('283') || is_access('284')) { ?><th>Actions</th><?php } ?>
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
                "url": "<?php echo base_url('inventory/get_cancelled_sales_order'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.delivery_date_range = '<?php echo (isset($_GET['delivery_date_range'])) ? $_GET['delivery_date_range']:'' ?>';
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = 'cancelled';	
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
                <?php if (is_access('271')) { ?>{ "data": "date" },<?php } ?>
                <?php if (is_access('272')) { ?>{ "data": "type", "className": "text-center" },<?php } ?>
                <?php if (is_access('273')) { ?>{ "data": "invoice_no" },<?php } ?>
                <?php if (is_access('274')) { ?>{ "data": "invoice_date" },<?php } ?>
                <?php if (is_access('275')) { ?>{ "data": "customer_name" },<?php } ?>
                <?php if (is_access('276')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('277')) { ?>{ "data": "warehouse_name" },<?php } ?>
                <?php if (is_access('278')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('279')) { ?>{ "data": "total_pro" },<?php } ?>
                <?php if (is_access('280')) { ?>{ "data": "grand_total" },<?php } ?>
                <?php if (is_access('281')) { ?>{ "data": "added_by" },<?php } ?>
                <?php if (is_access('282') || is_access('283') || is_access('284')) { ?>{ "data": "action" },<?php } ?>
             ],
            
             "buttons": [
                 <?php if (is_access('285')) { ?>
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
</script>
