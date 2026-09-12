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
    .subtabs-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 16px;
    }
    .subtabs-nav .nav-link {
        color: #4b5563;
        background: #f1f5f9;
        border-radius: 6px;
        padding: 7px 18px;
        font-weight: 600;
        margin-right: 8px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .subtabs-nav .nav-link:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .subtabs-nav .nav-link.active {
        background: #7367f0 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
    }
</style>

<?php
    // echo json_encode($this->session->userdata());
    include('filter/date_range.php');
    $staff_access = (int)$this->session->userdata('super_type_id');

    $status = (isset($_GET['status']) && $_GET['status'] != '') ? $_GET['status'] : 'pending';
    $sub_tab = (isset($_GET['sub_tab']) && $_GET['sub_tab'] != '') ? $_GET['sub_tab'] : 'order';

    $date_range_param = '';
    if (isset($_GET['date_range']) && $_GET['date_range'] != '') {
        $date_range_param .= '&date_range=' . urlencode($_GET['date_range']);
        if (isset($_GET['search'])) {
            $date_range_param .= '&search=' . urlencode($_GET['search']);
        }
    }
    if (isset($_GET['customer_id']) && $_GET['customer_id'] != '') {
        $date_range_param .= '&customer_id=' . urlencode($_GET['customer_id']);
    }
?>
	
<div class="row" id="table-bordered">
    
    <div class="col-md-12 mb-1">
        <div class="fixedElement" id="fixedElement">
            <ul class="nav nav-pills bg-nav-pills nav-justified ">
                
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=pending<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'pending') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Pending</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=invoice<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'invoice') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Generate Invoice</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=complete<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'complete') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">Complete</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/sales-order?status=all<?php echo $date_range_param; ?>" class="nav-link <?php echo ($status == 'all') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block">All Orders</span>
                    </a>
                </li>
                
            </ul>
        </div>
    </div>

    <?php if ($status == 'all') { ?>
        <div class="col-md-12 mb-1">
            <div class="fixedElement subtabs-bar">
                <ul class="nav nav-pills subtabs-nav">
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=all&sub_tab=order<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab != 'product') ? 'active' : ''; ?>">
                            <i class="fa fa-list me-1"></i> Order Wise
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=all&sub_tab=product<?php echo $date_range_param; ?>" class="nav-link <?php echo ($sub_tab == 'product') ? 'active' : ''; ?>">
                            <i class="fa fa-cubes me-1"></i> Product Wise
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    <?php } ?>

   <div class="col-12">
      <div class="card">
         <div class="card-body">
             <div class="row">
                <div class="col-md-12 mt-10">
                   <h5 class="mb-0">
                   <?php if ($status == 'all' && $sub_tab == 'product') { ?>
                       <b>Total Product Batches <span id="total_count"> (0)</span></b>
                   <?php } else { ?>
                       <b>Total Sales Order <span id="total_count"> (0)</span></b>
                   <?php } ?>
                   <?php if ($status == 'complete' && $staff_access !== 7) { ?>
                      &nbsp;|&nbsp; <b>Total Amount: ₹<span id="total_sales_amount">0.00</span></b>
                   <?php } ?>
				  </h5>
                </div>
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <?php if($status == 'pending' && $this->session->userdata('super_type_id') == 7) { ?>
                <a href="<?php echo site_url('inventory/sales-order/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } elseif ($status == 'pending' && $staff_access !== 7) { ?>
                <a href="<?php echo site_url('inventory/sales-invoice/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } ?>       
     
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
                  <?php if ($status == 'all' && $sub_tab == 'product') { ?>
					<th>#</th>
					<th>Batch No</th>
					<th>Order No</th>
					<th>Order Date</th>
					<th>Party Name</th>
					<th>Product Name</th>
					<th>Model No., Rate</th>
					<th>Bill amt</th>
					<th>CGST</th>
					<th>SGST</th>
					<th>IGST</th>
					<th>Total Bill Amt</th>
					<th>Cash Amt</th>
					<th>Total Amt</th>
					<th>Comm Amt</th>
					<th>Comm Name</th>
					<th>Profit</th>
                  <?php } else { ?>
					<th>#</th>
					<th>Date</th>
                    <?php if ($status == 'complete') { ?>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <?php } ?>
					<th>Customer Name</th>
					<th>Order NO</th>
					<th>Warehouse</th>
					<th>Total Qty</th>
					<th>Total Products</th>
					<th>Total Amount</th>
                    <?php if ($staff_access !== 7) { ?>
                        <th>Added By</th>
                    <?php } ?>
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
<?php
if ($status == 'all' && $sub_tab == 'product') {
    $num_cols = 17;
} else {
    $num_cols = 8;
    if ($status == 'complete') {
        $num_cols += 2;
    }
    if ($staff_access !== 7) {
        $num_cols += 1;
    }
}
$export_cols = '[' . implode(',', range(0, $num_cols - 1)) . ']';
?>
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
                <?php if ($status == 'all' && $sub_tab == 'product') { ?>
                "url": "<?php echo base_url('inventory/get_sales_order_product_wise'); ?>",
                <?php } elseif ($status != 'complete') { ?>
                "url": "<?php echo base_url('inventory/get_sales_order'); ?>",
                <?php } else { ?>
                "url": "<?php echo base_url('inventory/get_completed_sales_order'); ?>",
                <?php } ?>
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';	
                    data.customer_id = '<?php echo (isset($_GET['customer_id'])) ? $_GET['customer_id']:'' ?>';	
                    data.status = '<?php echo (isset($_GET['status'])) ? $_GET['status']: 'pending'; ?>';	
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                <?php if ($status == 'all' && $sub_tab == 'product') { ?>
                { "data": "sr_no" },
                { "data": "batch_no" },
                { "data": "order_no" },
                { "data": "order_date" },
                { "data": "party_name" },
                { "data": "product_name" },
                { "data": "model_rate" },
                { "data": "bill_amt" },
                { "data": "cgst" },
                { "data": "sgst" },
                { "data": "igst" },
                { "data": "total_bill_amt" },
                { "data": "cash_amt" },
                { "data": "total_amt" },
                { "data": "comm_amt" },
                { "data": "comm_name" },
                { "data": "profit" }
                <?php } else { ?>
                { "data": "sr_no" },
                { "data": "date" },
                <?php if ($status == 'complete') { ?>
                { "data": "invoice_no" },
                { "data": "invoice_date" },
                <?php } ?>
                { "data": "customer_name" },
                { "data": "order_no" },
                { "data": "warehouse_name" },
                { "data": "qty" },
                { "data": "total_pro" },
                { "data": "grand_total" },
                 <?php if ($staff_access !== 7) { ?>
                 { "data": "added_by" },
                 <?php } ?>
                 { "data": "action" }
                <?php } ?>
             ],
            
             "buttons": [
                 {
                     "extend": 'excel',
                     "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                     "exportOptions": {
                        "columns": <?php echo $export_cols; ?>
                     }
                 },
                 {
                     "extend": 'pdfHtml5',
                     "orientation": 'landscape',
                     "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                     "exportOptions": {
                        "columns": <?php echo $export_cols; ?>
                     }
                 }
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
                <?php if ($status == 'all' && $sub_tab == 'product') { ?>
                {
                    "targets": [0, 1, 2, 3, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
                    "className": "text-center",
                }
                <?php } else { ?>
                {
                    "targets": 0,
                    "className": "text-center",
                }
                <?php } ?>
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
    });
</script>