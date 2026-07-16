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

    $status = (isset($_GET['status']) && $_GET['status'] != '') ? $_GET['status'] : 'pending';
?>
	
<div class="row" id="table-bordered">
    
    <?php // if ($staff_access == 7) { ?>
        <div class="col-md-12 mb-1">
            <div class="fixedElement" id="fixedElement">
                <ul class="nav nav-pills bg-nav-pills nav-justified ">
                    
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=pending" class="nav-link <?php echo ($status == 'pending') ? 'active' : ''; ?>">
                            <i class="mdi mdi-home-variant d-md-none d-block"></i>
                            <span class="d-none d-md-block">Pending</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=invoice" class="nav-link <?php echo ($status == 'invoice') ? 'active' : ''; ?>">
                            <i class="mdi mdi-home-variant d-md-none d-block"></i>
                            <span class="d-none d-md-block">Generate Invoice</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=complete" class="nav-link <?php echo ($status == 'complete') ? 'active' : ''; ?>">
                            <i class="mdi mdi-home-variant d-md-none d-block"></i>
                            <span class="d-none d-md-block">Complete</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/sales-order?status=all" class="nav-link <?php echo ($status == 'all') ? 'active' : ''; ?>">
                            <i class="mdi mdi-home-variant d-md-none d-block"></i>
                            <span class="d-none d-md-block">All Orders</span>
                        </a>
                    </li>
                    
                </ul>
            </div>
        </div>
    <?php // } ?>

   <div class="col-12">
      <div class="card">
         <div class="card-body">
             <div class="row">
                <div class="col-md-12 mt-10">
                   <h5 class="mb-0"><b>Total Sales Order <span id="total_count"> (0)</span></b>
                   <?php if ($status == 'complete' && $staff_access !== 7) { ?>
                      &nbsp;|&nbsp; <b>Total Amount: ₹<span id="total_sales_amount">0.00</span></b>
                   <?php } ?>
				  </h5>
                </div>
             </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <!-- <a href="<?php echo site_url('inventory/import-order'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-outline-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-upload"></i> <?= get_phrase('upload_via_excel');?></span></a>    -->
            <?php if($status == 'pending' && $this->session->userdata('super_type_id') == 7) { ?>
                <a href="<?php echo site_url('inventory/sales-order/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } elseif ($status == 'pending' && $staff_access !== 7) { ?>
                <a href="<?php echo site_url('inventory/sales-invoice/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_sales_order');?></span></a>   
            <?php } ?>       
     
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<th>Date</th>
                    <?php if ($status == 'complete') { ?>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <?php } ?>
					<!-- <th>Company Name</th> -->
					<!--<th>Reference Number</th>-->
					<th>Customer Name</th>
					<th>Order NO</th>
					<th>Warehouse</th>
					<th>Total Qty</th>
					<th>Total Products</th>
					<th>Total Amount</th>
				    <!--<th>Remark</th>-->
                    <?php if ($staff_access !== 7) { ?>
                        <th>Added By</th>
                    <?php } ?>
                    <?php // if ($staff_access !== 7) { ?>
                    <th>Actions</th>
                    <?php // } ?>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">
<?php
$num_cols = 8;
if ($status == 'complete') {
    $num_cols += 2;
}
if ($staff_access !== 7) {
    $num_cols += 1;
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
                <?php if ($status != 'complete') { ?>
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
                { "data": "sr_no" },
                { "data": "date" },
                <?php if ($status == 'complete') { ?>
                { "data": "invoice_no" },
                { "data": "invoice_date" },
                <?php } ?>
                // { "data": "company_name" },
                // { "data": "refrence_no" },
                { "data": "customer_name" },
                { "data": "order_no" },
                { "data": "warehouse_name" },
                { "data": "qty" },
                { "data": "total_pro" },
                { "data": "grand_total" },
                // { "data": "remark" },
                 <?php if ($staff_access !== 7) { ?>
                 { "data": "added_by" },
                 <?php } ?>
                 <?php // if ($staff_access !== 7) { ?>
                    { "data": "action" },
                 <?php // } ?>
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