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
		  <a href="<?php echo site_url('inventory/formula-product-order/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0"><span><i class="feather icon-plus"></i> Add Formula Product In</span></a>          
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<th>Date</th>
					<th>Batch No</th>
					<th>Formula Product</th>
					<th>Qty</th>
					<th>Total Off Cost</th>
					<th>Total Actual Cost</th>
					<th>Total Expense</th>
					<th>Final Total</th>
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
                { "data": "date" },
                { "data": "batch_no" },
                { "data": "product_name" },
                { "data": "qty" },
                { "data": "total_off_cost" },
                { "data": "total_actual_cost" },
                { "data": "total_expense" },
                { "data": "final_total" },
                { "data": "action" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7,8]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7,8]
                    }
                }
            ], 
           
            "infoCallback": function( settings, start, end, max, total, pre ) {
                $(".loader").fadeOut("slow"); 
                $('#total_count').html('('+total+')');
                return 'Showing ' +start+ ' to ' + end + ' of '+ total + ' entries';
            }, 
       });
    });
</script>
