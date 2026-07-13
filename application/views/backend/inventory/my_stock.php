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
    if(empty($this->input->get('warehouse', true))){
       $warehouse_id = $warehouse_list[0]['id'];
    }
    else{
       $warehouse_id = $this->input->get('warehouse', true);
    }
    if(empty($this->input->get('type', true))){
       $type = 'complete';
    }
    else{
       $type = $this->input->get('type', true);
    }
?>

<div class="row" id="table-bordered">
	<div class="col-md-12 mb-1">
		<div class="fixedElement" id="fixedElement">
			<ul class="nav nav-pills bg-nav-pills nav-justified ">
				<?php
                 $active='active';
                 foreach($warehouse_list as $warehouse){?>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>inventory/my-stock?warehouse=<?php echo $warehouse['id'];?>" class="nav-link <?php echo ($warehouse_id == $warehouse['id']) ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block"><?php echo $warehouse['name'];?></span>
                    </a>
                </li>
                <?php $active='';}?>	
			</ul>
		</div>
	</div>
	
    <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0">
                    <b>Total Product <span id="total_count">(0)</span></b> |
                    <b>Total Qty: <span id="total_qty"><?php echo $total['qty']; ?></span></b> 
                    <!-- |
                    <b>Total Amount: ₹<span id="total_amount"><?php echo $total['total']; ?></span></b> -->
				  </h5>
               </div>
            </div>
         </div>
         
        <div class="card-datatable d-report mb-2">
          <!-- <button onclick="showAjaxModal('<?php echo site_url('modal/popup_inventory/modal_add_product_stock/' . $warehouse_id); ?>', 'Add Product Stock')" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" style="margin-right: 10px;">
              <span><i class="feather icon-plus"></i> Add Product</span>
          </button> -->
          <!--<a href="<?php echo site_url('inventory/stock-transfer'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-repeat"></i> <?= get_phrase('stock_transfer');?></span></a>         -->
            <table class="table leads-table" id="report-datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Black Qty</th>
                        <th>White Qty</th>
                        <th>PO Qty</th>
                        <th>Priority Qty</th>
                        <th>Loading Qty</th>
                        <th>Cost</th>
                        <th>Cost with Expense</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
         </div>
      </div>
    </div>
</div>

<!-- PO List Modal -->
<div class="modal fade" id="poListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-5 mx-50 pb-5">
                <h1 class="text-center mb-1" id="poListModalTitle">PO List</h1>
                <p class="text-center" id="poListModalSubTitle">Details of Purchase Orders</p>
                <div id="poListContent" class="mt-2">
                    <!-- Dynamic Content -->
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">       
    function showProductPOList(productId, companyId, status, warehouseId = '') {
        $('#poListModal').modal('show');
        $('#poListContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

        let statusTitle = status.charAt(0).toUpperCase() + status.slice(1);
        $('#poListModalTitle').text(statusTitle + ' Purchase Orders');

        $.ajax({
            url: "<?php echo base_url('inventory/get_product_po_list'); ?>",
            type: "POST",
            data: {
                product_id: productId,
                company_id: companyId,
                status: status,
                warehouse_id: warehouseId
            },
            success: function (response) {
                $('#poListContent').html(response);
            },
            error: function () {
                $('#poListContent').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
            }
        });
    }

    $(document).ready(function($) {
    	var dataTable = $('#report-datatable').DataTable({ 
    	"dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true, 
			"pageLength": 25,
            "lengthChange": true,
			"lengthMenu": [10,25, 50, 100, 250, 500,1000,2000],
            "language" :{
                    sLengthMenu: "_MENU_",
                    'processing': $('.loader').show()
            },	
            "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_my_stock'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                       data.warehouse_id = '<?php echo $warehouse_id; ?>';			
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
                { "data": "category" },
                { "data": "product_name" },
                { "data": "quantity" },
                { "data": "black_qty" },
                { "data": "white_qty" },
                { "data": "po_qty" },
                { "data": "priority_qty" },
                { "data": "loading_qty" },
                { "data": "no_expense_amt" },
                { "data": "expense_amt" },
                { "data": "action" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7,8,9,10,11]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7,8,9,10,11]
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
				{
                    "targets": 1, // your case first column
                    "className": "text-center",
                },
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
    });
</script>