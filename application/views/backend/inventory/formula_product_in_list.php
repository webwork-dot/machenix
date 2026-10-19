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
</style>

<div class="row" id="table-bordered">
    <?php include('filter/date_range.php'); ?>	
    
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Formula Product In<span id="total_count"> (0)</span></b></h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
		  <?php if (is_access('206')) { ?>
		  <a href="<?php echo site_url('inventory/formula-product-order/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0"><span><i class="feather icon-plus"></i> Add Formula Product In</span></a>
		  <?php } ?>
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<?php if (is_access('198')) { ?><th>Date</th><?php } ?>
					<?php if (is_access('199')) { ?><th>Batch No</th><?php } ?>
					<?php if (is_access('200')) { ?><th>Formula Product</th><?php } ?>
					<?php if (is_access('201')) { ?><th>Qty</th><?php } ?>
					<?php if (is_access('202')) { ?><th>Total Off Cost</th><?php } ?>
					<?php if (is_access('203')) { ?><th>Total Actual Cost</th><?php } ?>
					<?php if (is_access('204')) { ?><th>Total Expense</th><?php } ?>
					<?php if (is_access('205')) { ?><th>Final Total</th><?php } ?>
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
                "url": "<?php echo base_url('inventory/get_formula_product_orders'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                       data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
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
                <?php if (is_access('198')) { ?>{ "data": "date" },<?php } ?>
                <?php if (is_access('199')) { ?>{ "data": "batch_no" },<?php } ?>
                <?php if (is_access('200')) { ?>{ "data": "product_name" },<?php } ?>
                <?php if (is_access('201')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('202')) { ?>{ "data": "total_off_cost" },<?php } ?>
                <?php if (is_access('203')) { ?>{ "data": "total_actual_cost" },<?php } ?>
                <?php if (is_access('204')) { ?>{ "data": "total_expense" },<?php } ?>
                <?php if (is_access('205')) { ?>{ "data": "final_total" },<?php } ?>
            ], 
           
            "buttons": [
                <?php if (is_access('207')) { ?>
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
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
       });
    });
</script>
