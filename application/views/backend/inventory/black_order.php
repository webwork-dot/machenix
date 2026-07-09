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
	.new-fix .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
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
    // echo json_encode($this->session->userdata());
    include('filter/date_range.php');
    $staff_access = (int)$this->session->userdata('super_type_id');
?>
	
<div class="row" id="table-bordered">

    <div class="col-md-12 mb-1">
		<div class="fixedElement" id="fixedElement">
			<ul class="nav nav-pills bg-nav-pills nav-justified ">
				
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/black-order" class="nav-link <?php echo ($_GET['status']!= 'completed') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Pending</span>
                    </a>
                </li>
				
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/black-order?status=completed" class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Completed</span>
                    </a>
                </li>
                
			</ul>
		</div>
	</div>

   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Black Order <span id="total_count"> (0)</span></b>
                      <button type="button" id="btn_generate_invoice" class="btn btn-primary float-end" style="margin-top: -5px;" disabled>Generate Invoice</button>
				  </h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th style="width: 15px;">#</th>
					<th>Date</th>
					<th>Customer Name</th>
					<th>Order NO</th>
					<th>Product Name</th>
					<th>Item Code</th>
					<th>Batch No</th>
					<th>Warehouse</th>
					<th>Black Qty</th>
                    <?php if ($staff_access !== 7) { ?>
                    <th>Actions</th>
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
                applyCheckboxVisibility();
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_black_order'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = '<?php echo (isset($_GET['status'])) ? $_GET['status']:'pending'; ?>';	
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
                { "data": "customer_name" },
                { "data": "order_no" },
                { "data": "product_name" },
                { "data": "item_code" },
                { "data": "batch_no" },
                { "data": "warehouse_name" },
                { "data": "black_qty" },
                <?php if ($staff_access !== 7) { ?>
                   { "data": "action" },
                <?php } ?>
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
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
                    "targets": 0, // your case first column
                    "className": "text-center",
                },
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
    });
</script>
