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
	.small-img{
		max-height: 50px;
		min-height: 50px;
		object-fit: cover;
		border-radius: 10px;
		border: 1px solid #e7e6e6;
		height: 50px;
		max-width: 60px;
	}
</style>

<?php include('filter/date_range.php'); ?>
	
<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
             <div class="row">
                <div class="col-md-12 mt-10">
                   <h5 class="mb-0"><b>Total Customer Quotations <span id="total_count"> (0)</span></b></h5>
                </div>
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <?php if (is_access('359')) { ?>
            <a href="<?php echo site_url('inventory/customer-quotations/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0"><span><i class="feather icon-plus"></i> Add Quotation</span></a>
            <?php } ?>
     
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<?php if (is_access('349')) { ?><th>Date</th><?php } ?>
					<?php if (is_access('350')) { ?><th>Quotation No</th><?php } ?>
					<?php if (is_access('351')) { ?><th>Customer Name</th><?php } ?>
					<?php if (is_access('352')) { ?><th>Total Products</th><?php } ?>
					<?php if (is_access('353')) { ?><th>Total Qty</th><?php } ?>
					<?php if (is_access('354')) { ?><th>Basic Value</th><?php } ?>
					<?php if (is_access('355')) { ?><th>Total Black Amt</th><?php } ?>
					<?php if (is_access('356')) { ?><th>Grand Total</th><?php } ?>
					<?php if (is_access('357')) { ?><th>Remark</th><?php } ?>
					<?php if (is_access('358')) { ?><th>Added By</th><?php } ?>
					<?php if (is_access('360') || is_access('361') || is_access('362')) { ?><th>Actions</th><?php } ?>
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
                "url": "<?php echo base_url('inventory/get_customer_quotations'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
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
                <?php if (is_access('349')) { ?>{ "data": "date" },<?php } ?>
                <?php if (is_access('350')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('351')) { ?>{ "data": "customer_name" },<?php } ?>
                <?php if (is_access('352')) { ?>{ "data": "total_pro" },<?php } ?>
                <?php if (is_access('353')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('354')) { ?>{ "data": "basic_value" },<?php } ?>
                <?php if (is_access('355')) { ?>{ "data": "total_black_amt" },<?php } ?>
                <?php if (is_access('356')) { ?>{ "data": "grand_total" },<?php } ?>
                <?php if (is_access('357')) { ?>{ "data": "remark" },<?php } ?>
                <?php if (is_access('358')) { ?>{ "data": "added_by" },<?php } ?>
                <?php if (is_access('360') || is_access('361') || is_access('362')) { ?>{ "data": "action" },<?php } ?>
            ],
            
            "buttons": [
                <?php if (is_access('363')) { ?>
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
			createdRow: function (row, data, index) {
                if(data['error']=='1'){
                    $(row).addClass('table-error');
                }
            },
           
            'columnDefs': [
                {
                    "targets": 0,
                    "className": "text-center",
                },
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
    });
</script>
