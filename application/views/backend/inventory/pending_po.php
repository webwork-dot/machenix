<?php
    $type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : 'order';
    if ($type == 'order' && !is_access('165') && is_access('166')) {
        $type = 'product';
    } elseif ($type == 'product' && !is_access('166') && is_access('165')) {
        $type = 'order';
    }
?>
<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
<script src="//cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>

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
	.new-form .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
		color: #5a79c0;
		border: 1px solid #5a79c0 !important;
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
	
</style>

<div class="row" id="table-bordered">
    <?php include('filter/pending_po_filter.php'); ?>	
    <div class="col-md-12 mb-1 mt-1">
        <div class="fixedElement new-form" id="fixedElement">
            <ul class="nav nav-pills bg-nav-pills nav-justified">
                <?php if (is_access('165')) { ?>
                <li class="nav-item">
                    <a href="<?php echo base_url('inventory/pending-po?type=order'); ?>" class="nav-link <?php echo ($type == 'order') ? 'active' : ''; ?>">
                        <i class="mdi mdi-format-list-bulleted d-md-none d-block"></i>
                        <span class="d-none d-md-block">Order</span>
                    </a>
                </li>
                <?php } ?>
                <?php if (is_access('166')) { ?>
                <li class="nav-item">
                    <a href="<?php echo base_url('inventory/pending-po?type=product'); ?>" class="nav-link <?php echo ($type == 'product') ? 'active' : ''; ?>">
                        <i class="mdi mdi-package-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Product</span>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <?php include('nav/nav_import_po.php'); ?>

    
   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0;">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Pending <?php echo ($type == 'product') ? 'Products' : 'PO'; ?><span id="total_count"> (0)</span></b>
				  </h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
          
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <?php if ($type == 'product'): ?>
                    <tr>
                      <th>#</th>
                      <?php if (is_access('176')) { ?><th>Date / Batch No.</th><?php } ?>
                      <?php if (is_access('177')) { ?><th>Product Name</th><?php } ?>
                      <?php if (is_access('178')) { ?><th>Total Qty</th><?php } ?>
                      <?php if (is_access('179')) { ?><th>Received Qty</th><?php } ?>
                      <?php if (is_access('180')) { ?><th>Remaining Qty</th><?php } ?>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <th>#</th>
                      <?php if (is_access('167')) { ?><th>Date / Batch No.</th><?php } ?>
                      <?php if (is_access('168')) { ?><th>Supplier Name</th><?php } ?>
                      <?php if (is_access('169')) { ?><th>No of Spare Parts</th><?php } ?>
                      <?php if (is_access('170')) { ?><th>No of Ready Goods</th><?php } ?>
                      <?php if (is_access('171')) { ?><th>Loading Date</th><?php } ?>
                      <?php if (is_access('172') || is_access('173') || is_access('174')) { ?><th>Actions</th><?php } ?>
                    </tr>
                  <?php endif; ?>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">       
    $(document).ready(function($) {
    	dataTable = $('#report-datatable').DataTable({ 
    	"dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
                "url": "<?php echo base_url('inventory/get_pending_po'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = $('#filter_date_range').val() || '';
                    data.loading_date_range = $('#filter_loading_date_range').val() || '';
                    data.search.value = $('#filter_keywords').val() || '';
                    data.status = $('#filter_status').val() || '';
                    data.type = '<?php echo $type; ?>';
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
                <?php if ($type == 'product') { ?>
                    <?php if (is_access('176')) { ?>{ "data": "batch_no" },<?php } ?>
                    <?php if (is_access('177')) { ?>{ "data": "product_name" },<?php } ?>
                    <?php if (is_access('178')) { ?>{ "data": "total_qty" },<?php } ?>
                    <?php if (is_access('179')) { ?>{ "data": "received_qty" },<?php } ?>
                    <?php if (is_access('180')) { ?>{ "data": "remaining_qty" },<?php } ?>
                <?php } else { ?>
                    <?php if (is_access('167')) { ?>{ "data": "date" },<?php } ?>
                    <?php if (is_access('168')) { ?>{ "data": "suppliers" },<?php } ?>
                    <?php if (is_access('169')) { ?>{ "data": "spare_parts_count" },<?php } ?>
                    <?php if (is_access('170')) { ?>{ "data": "ready_goods_count" },<?php } ?>
                    <?php if (is_access('171')) { ?>{ "data": "delivery_date" },<?php } ?>
                    <?php if (is_access('172') || is_access('173') || is_access('174')) { ?>{ "data": "action" },<?php } ?>
                <?php } ?>
            ], 
           
            "buttons": [
                <?php if (($type == 'product' && is_access('181')) || ($type != 'product' && is_access('175'))) { ?>
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

    function generate_excel(id) {
        window.location.href = "<?php echo base_url(); ?>inventory/generate_purchase_order_excel/" + id;
    }

    $(document).ready(function() {
        setInterval(function() {
            if(document.querySelector('.cke_notification_close')) {
                document.querySelector('.cke_notification_close').click();
            }
        }, 500);
    });

</script>
