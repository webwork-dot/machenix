<?php
    $type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : 'order';
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
	.new-fix .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
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
    <?php include('filter/ajax_commom_filter.php'); ?>	
    <div class="col-md-12 mb-1 mt-1">
        <div class="fixedElement new-fix" id="fixedElement">
            <ul class="nav nav-pills bg-nav-pills nav-justified">
                <li class="nav-item">
                    <a href="<?php echo base_url('inventory/pending_po?type=order'); ?>" class="nav-link <?php echo ($type == 'order') ? 'active' : ''; ?>">
                        <i class="mdi mdi-format-list-bulleted d-md-none d-block"></i>
                        <span class="d-none d-md-block">Order</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url('inventory/pending_po?type=product'); ?>" class="nav-link <?php echo ($type == 'product') ? 'active' : ''; ?>">
                        <i class="mdi mdi-package-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Product</span>
                    </a>
                </li>
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
                      <th>Date / Batch No.</th>
                      <th>Product Name</th>
                      <th>Total Qty</th>
                      <th>Received Qty</th>
                      <th>Remaining Qty</th>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <th>#</th>
                      <th>Date / Batch No.</th>
                      <th>Supplier Name</th>
                      <th>No of Spare Parts</th>
                      <th>No of Ready Goods</th>
                      <th>Loading Date</th>
                      <th>Actions</th>
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
                     
            "columns": <?php echo ($type == 'product') ? json_encode([
                [ "data" => "sr_no" ],
                [ "data" => "batch_no" ],
                [ "data" => "product_name" ],
                [ "data" => "total_qty" ],
                [ "data" => "received_qty" ],
                [ "data" => "remaining_qty" ],
            ]) : json_encode([
                [ "data" => "sr_no" ],
                [ "data" => "date" ],
                [ "data" => "suppliers" ],
                [ "data" => "spare_parts_count" ],
                [ "data" => "ready_goods_count" ],
                [ "data" => "delivery_date" ],
                [ "data" => "action" ],
            ]); ?>, 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5]
                    }
                }
            ], 
           
            "infoCallback": function( settings, start, end, max, total, pre ) {
                $(".loader").fadeOut("slow"); 
                $('#total_count').html('('+total+')');
                return 'Showing ' +start+ ' to ' + end + ' of '+ total + ' entries';
            }, 
           
            'columnDefs': [
                {
                    "targets": 0, // your case first column
                    "className": "text-center",
                },
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
    });

    function generate_excel(id) {
        // Direct navigation to trigger file download
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
