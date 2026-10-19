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
	.nav-pills.nav-justified .nav-item {
		display: flex;
		align-items: center;
	}
	.new-form .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
		color: #1e652e;
		border: 1px solid #1e652e !important;
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

<?php
    include('filter/date_range.php');
    $status = (isset($_GET['status']) && $_GET['status'] != '') ? $_GET['status'] : 'pending';
    if ($status != 'completed' && !is_access('393') && is_access('394')) {
        $status = 'completed';
    } elseif ($status == 'completed' && !is_access('394') && is_access('393')) {
        $status = 'pending';
    }
    $is_completed = ($status == 'completed');
?>
	
<div class="row" id="table-bordered">

    <div class="col-md-12 mb-1">
		<div class="fixedElement" id="fixedElement">
			<ul class="nav nav-pills bg-nav-pills nav-justified ">
                <?php if (is_access('393')) { ?>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/black-order" class="nav-link <?php echo (!$is_completed) ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Pending</span>
                    </a>
                </li>
                <?php } ?>
                <?php if (is_access('394')) { ?>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/black-order?status=completed" class="nav-link <?php echo ($is_completed) ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Completed</span>
                    </a>
                </li>
                <?php } ?>
			</ul>
		</div>
	</div>

   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Black Order <span id="total_count"> (0)</span></b>
                    <?php if (!$is_completed && is_access('404')) { ?>
                        <button type="button" id="btn_generate_invoice" class="btn btn-primary float-end" style="margin-top: -5px;" disabled>Generate Invoice</button>
                    <?php } ?>
				  </h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
                    <?php if (!$is_completed) { ?>
                      <th style="width: 15px;">#</th>
                      <?php if (is_access('395')) { ?><th>Date</th><?php } ?>
                      <?php if (is_access('396')) { ?><th>Customer Name</th><?php } ?>
                      <?php if (is_access('397')) { ?><th>Order NO</th><?php } ?>
                      <?php if (is_access('398')) { ?><th>Product Name</th><?php } ?>
                      <?php if (is_access('399')) { ?><th>Item Code</th><?php } ?>
                      <?php if (is_access('400')) { ?><th>Batch No</th><?php } ?>
                      <?php if (is_access('401')) { ?><th>Warehouse</th><?php } ?>
                      <?php if (is_access('402')) { ?><th>Black Qty</th><?php } ?>
                      <?php if (is_access('403')) { ?><th>Actions</th><?php } ?>
                    <?php } else { ?>
                      <th style="width: 15px;">#</th>
                      <?php if (is_access('406')) { ?><th>Date</th><?php } ?>
                      <?php if (is_access('407')) { ?><th>Customer Name</th><?php } ?>
                      <?php if (is_access('408')) { ?><th>Invoice No</th><?php } ?>
                      <?php if (is_access('409')) { ?><th>Warehouse</th><?php } ?>
                      <?php if (is_access('410')) { ?><th>Product Count</th><?php } ?>
                      <?php if (is_access('411')) { ?><th>Qty Count</th><?php } ?>
                      <?php if (is_access('412')) { ?><th>Grand Total</th><?php } ?>
                      <?php if (is_access('413') || is_access('414') || is_access('415') || is_access('416') || is_access('417')) { ?><th>Actions</th><?php } ?>
                    <?php } ?>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">       
    function applyCheckboxVisibility() {
        var checkedCheckboxes = $('.batch-checkbox:checked');
        if (checkedCheckboxes.length > 0) {
            var activeWarehouseId = checkedCheckboxes.first().attr('data-warehouse-id');
            $('.batch-checkbox').each(function() {
                if ($(this).is(':checked')) {
                    $(this).show();
                } else {
                    var whId = $(this).attr('data-warehouse-id');
                    if (whId === activeWarehouseId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                }
            });
        } else {
            $('.batch-checkbox').show();
        }
        $('#btn_generate_invoice').prop('disabled', checkedCheckboxes.length === 0);
    }

    $(document).ready(function($) {
        $(document).on('change', '.batch-checkbox', function() {
            applyCheckboxVisibility();
        });

        $('#btn_generate_invoice').on('click', function() {
            var selectedBatchIds = [];
            var firstCheckbox = $('.batch-checkbox:checked').first();
            var customerId = firstCheckbox.attr('data-customer-id') || '0';
            var orderId = firstCheckbox.attr('data-order-id') || '0';
            
            $('.batch-checkbox:checked').each(function() {
                selectedBatchIds.push($(this).val());
            });
            
            if (selectedBatchIds.length > 0) {
                var url = "<?php echo base_url(); ?>modal/popup_inventory/sales_order_generate_bill_modal/" + customerId + "/" + orderId + "?batch_ids=" + selectedBatchIds.join(',');
                showLargeModal(url, 'Generate Invoice');
            }
        });

    	var dataTable = $('#report-datatable').DataTable({ 
    	    "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "pageLength": 25,
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
                <?php if (!$is_completed) { ?>
                applyCheckboxVisibility();
                <?php } ?>
            },
      
            "ajax":{
                <?php if (!$is_completed) { ?>
                "url": "<?php echo base_url('inventory/get_black_order'); ?>",
                <?php } else { ?>
                "url": "<?php echo base_url('inventory/get_completed_black_order'); ?>",
                <?php } ?>
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = '<?php echo $status; ?>';	
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                <?php if (!$is_completed) { ?>
                    { "data": "sr_no" },
                    <?php if (is_access('395')) { ?>{ "data": "date" },<?php } ?>
                    <?php if (is_access('396')) { ?>{ "data": "customer_name" },<?php } ?>
                    <?php if (is_access('397')) { ?>{ "data": "order_no" },<?php } ?>
                    <?php if (is_access('398')) { ?>{ "data": "product_name" },<?php } ?>
                    <?php if (is_access('399')) { ?>{ "data": "item_code" },<?php } ?>
                    <?php if (is_access('400')) { ?>{ "data": "batch_no" },<?php } ?>
                    <?php if (is_access('401')) { ?>{ "data": "warehouse_name" },<?php } ?>
                    <?php if (is_access('402')) { ?>{ "data": "black_qty" },<?php } ?>
                    <?php if (is_access('403')) { ?>{ "data": "action" },<?php } ?>
                <?php } else { ?>
                    { "data": "sr_no" },
                    <?php if (is_access('406')) { ?>{ "data": "date" },<?php } ?>
                    <?php if (is_access('407')) { ?>{ "data": "customer_name" },<?php } ?>
                    <?php if (is_access('408')) { ?>{ "data": "invoice_no" },<?php } ?>
                    <?php if (is_access('409')) { ?>{ "data": "warehouse_name" },<?php } ?>
                    <?php if (is_access('410')) { ?>{ "data": "product_count" },<?php } ?>
                    <?php if (is_access('411')) { ?>{ "data": "qty_count" },<?php } ?>
                    <?php if (is_access('412')) { ?>{ "data": "grand_total" },<?php } ?>
                    <?php if (is_access('413') || is_access('414') || is_access('415') || is_access('416') || is_access('417')) { ?>{ "data": "action" },<?php } ?>
                <?php } ?>
            ], 
           
            "buttons": [
                <?php if ((!$is_completed && is_access('405')) || ($is_completed && is_access('418'))) { ?>
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
            "lengthMenu": [
                [25, 50, 100, 250, 500, 1000],
                [25, 50, 100, 250, 500, 1000]
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
