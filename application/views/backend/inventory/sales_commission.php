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
    if ($status != 'complete' && !is_access('420') && is_access('421')) {
        $status = 'complete';
    } elseif ($status == 'complete' && !is_access('421') && is_access('420')) {
        $status = 'pending';
    }
    $is_complete = ($status == 'complete');
?>
	
<div class="row" id="table-bordered">
    
        <div class="col-md-12 mb-1">
            <div class="fixedElement" id="fixedElement">
                <ul class="nav nav-pills bg-nav-pills nav-justified ">
                    <?php if (is_access('420')) { ?>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-commission?status=pending" class="nav-link <?php echo (!$is_complete) ? 'active' : ''; ?>">
                            <i class="mdi mdi-home-variant d-md-none d-block"></i>
                            <span class="d-none d-md-block">Pending</span>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if (is_access('421')) { ?>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-commission?status=complete" class="nav-link <?php echo ($is_complete) ? 'active' : ''; ?>">
                            <i class="mdi mdi-home-variant d-md-none d-block"></i>
                            <span class="d-none d-md-block">Complete</span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

   <div class="col-12">
      <div class="card">
         <div class="card-body">
             <div class="row align-items-center">
                <div class="col-md-8 mt-10">
                   <h5 class="mb-0"><b>Total Sales Commission <span id="total_count"> (0)</span></b>
                      <?php if ((!$is_complete && is_access('427')) || ($is_complete && is_access('438'))) { ?>
                      &nbsp;|&nbsp; <b>Total Amount: ₹<span id="total_sales_amount"><?php echo number_format(($is_complete) ? $complete_commission_total : $pending_commission_total, 2, '.', ','); ?></span></b>
                      <?php } ?>
				  </h5>
                </div>
                <?php if (!$is_complete && is_access('431')) { ?>
                <div class="col-md-4 text-end mt-10">
                   <button type="button" id="pay-btn" class="btn btn-primary waves-effect waves-float waves-light" style="display:none;" onclick="openPaymentModal()">Make Payment</button>
                </div>
                <?php } ?>
              </div>
          </div>
        <div class="card-datatable d-report mb-2">
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th><?php if (!$is_complete && is_access('431')) { echo '<input type="checkbox" id="select-all" class="form-check-input">'; } else { echo '#'; } ?></th>
                    <?php if (!$is_complete) { ?>
					<?php if (is_access('422')) { ?><th>Batch No</th><?php } ?>
					<?php if (is_access('423')) { ?><th>Product Name</th><?php } ?>
					<?php if (is_access('424')) { ?><th>Order No</th><?php } ?>
					<?php if (is_access('425')) { ?><th>Warehouse</th><?php } ?>
					<?php if (is_access('426')) { ?><th>Quantity</th><?php } ?>
					<?php if (is_access('427')) { ?><th>Total Commission</th><?php } ?>
					<?php if (is_access('428')) { ?><th>Commission</th><?php } ?>
					<?php if (is_access('429')) { ?><th>Shared Commission</th><?php } ?>
					<?php if (is_access('430')) { ?><th>Actions</th><?php } ?>
                    <?php } else { ?>
					<?php if (is_access('433')) { ?><th>Batch No</th><?php } ?>
					<?php if (is_access('434')) { ?><th>Product Name</th><?php } ?>
					<?php if (is_access('435')) { ?><th>Order No</th><?php } ?>
					<?php if (is_access('436')) { ?><th>Warehouse</th><?php } ?>
					<?php if (is_access('437')) { ?><th>Quantity</th><?php } ?>
					<?php if (is_access('438')) { ?><th>Total Commission</th><?php } ?>
					<?php if (is_access('439')) { ?><th>Commission</th><?php } ?>
					<?php if (is_access('440')) { ?><th>Shared Commission</th><?php } ?>
					<?php if (is_access('441')) { ?><th>Actions</th><?php } ?>
                    <?php } ?>
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
                "url": "<?php echo base_url('inventory/get_sales_commission'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = '<?php echo $status; ?>';
                    data.datatype = <?php echo is_access('934') ? '1' : '0'; ?>;
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
                <?php if (!$is_complete) { ?>
                <?php if (is_access('422')) { ?>{ "data": "batch_no" },<?php } ?>
                <?php if (is_access('423')) { ?>{ "data": "product_name" },<?php } ?>
                <?php if (is_access('424')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('425')) { ?>{ "data": "warehouse_name" },<?php } ?>
                <?php if (is_access('426')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('427')) { ?>{ "data": "total_commission" },<?php } ?>
                <?php if (is_access('428')) { ?>{ "data": "my_commission" },<?php } ?>
                <?php if (is_access('429')) { ?>{ "data": "shared_commission" },<?php } ?>
                <?php if (is_access('430')) { ?>{ "data": "action" },<?php } ?>
                <?php } else { ?>
                <?php if (is_access('433')) { ?>{ "data": "batch_no" },<?php } ?>
                <?php if (is_access('434')) { ?>{ "data": "product_name" },<?php } ?>
                <?php if (is_access('435')) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access('436')) { ?>{ "data": "warehouse_name" },<?php } ?>
                <?php if (is_access('437')) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access('438')) { ?>{ "data": "total_commission" },<?php } ?>
                <?php if (is_access('439')) { ?>{ "data": "my_commission" },<?php } ?>
                <?php if (is_access('440')) { ?>{ "data": "shared_commission" },<?php } ?>
                <?php if (is_access('441')) { ?>{ "data": "action" },<?php } ?>
                <?php } ?>
             ],
            
             "buttons": [
                 <?php if ((!$is_complete && is_access('432')) || ($is_complete && is_access('442'))) { ?>
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
                },
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
            $('#select-all').prop('checked', false);
            togglePayButton();
        });

        $(document).on('change', '#select-all', function() {
            $('.order-chk').prop('checked', this.checked);
            togglePayButton();
        });

        $(document).on('change', '.order-chk', function() {
            var allChecked = ($('.order-chk:checked').length === $('.order-chk').length);
            $('#select-all').prop('checked', allChecked);
            togglePayButton();
        });

        function togglePayButton() {
            var checked_count = $('.order-chk:checked').length;
            if (checked_count > 0) {
                var total_amount = 0.00;
                $('.order-chk:checked').each(function() {
                    total_amount += parseFloat($(this).data('amount')) || 0.00;
                });
                $('#pay-btn').show().html('Make Payment (₹' + total_amount.toFixed(2) + ')');
            } else {
                $('#pay-btn').hide();
            }
        }
    });

    function openPaymentModal() {
        var selected_ids = [];
        var total_amount = 0.00;
        $('.order-chk:checked').each(function() {
            selected_ids.push($(this).val());
            total_amount += parseFloat($(this).data('amount')) || 0.00;
        });
        
        if (selected_ids.length > 0) {
            var url = "<?php echo base_url('modal/popup_inventory/sales_commission_payment_modal'); ?>/" + encodeURIComponent(selected_ids.join(',')) + "/" + total_amount.toFixed(2);
            showAjaxModal(url, 'Commission Payment');
        }
    }
</script>
