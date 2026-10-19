<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>


<?php include('filter/date_range.php'); ?>	

<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Sales Return <span id="total_count"> (0)</span></b>
				  </h5>
               </div>
            </div>
         </div>
		 
        <div class="card-datatable d-report mb-2">
		   <?php if (is_access('450')) { ?>
		   <a href="<?php echo site_url('inventory/goods-return/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_goods_return');?></span></a>
           <?php } ?>
		
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<?php if (is_access('444')) { ?><th>Date</th><?php } ?>
					<?php if (is_access('445')) { ?><th>Company Name</th><?php } ?>
					<?php if (is_access('446')) { ?><th>Customer Name</th><?php } ?>
					<?php if (is_access('447')) { ?><th>Quantity</th><?php } ?>
					<?php if (is_access('448')) { ?><th>Refrence Order No</th><?php } ?>
					<?php if (is_access('449')) { ?><th>Reason</th><?php } ?>
					<?php if (is_access('451')) { ?><th>Action</th><?php } ?>
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
                "url": "<?php echo base_url('inventory/get_goods_return'); ?>",
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
                <?php if (is_access('444')) { ?>{ "data": "date" },<?php } ?>
                <?php if (is_access('445')) { ?>{ "data": "company_name" },<?php } ?>
                <?php if (is_access('446')) { ?>{ "data": "customer_name" },<?php } ?>
                <?php if (is_access('447')) { ?>{ "data": "product_qty" },<?php } ?>
                <?php if (is_access('448')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('449')) { ?>{ "data": "reason" },<?php } ?>
                <?php if (is_access('451')) { ?>{ "data": "action" },<?php } ?>
            ], 
           
            "buttons": [
                <?php if (is_access('452')) { ?>
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
