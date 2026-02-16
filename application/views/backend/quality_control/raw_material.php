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
</style>
<?php  
    if(empty($this->input->get('warehouse', true))){
       $warehouse_id = $warehouse_list[0]['id'];
    }
    else{
       $warehouse_id = $this->input->get('warehouse', true);
    }
    $type = 'pending';
?>
<div class="row" id="table-bordered">
	
	
	<div class="col-md-12 mb-1">
		<div class="fixedElement" id="fixedElement">
			<ul class="nav nav-pills bg-nav-pills nav-justified ">
				<?php
                 $active='active';
                 foreach($warehouse_list as $warehouse){?>
                <li class="nav-item">
                    <a href="<?php echo base_url();?>quality-control/raw-material?warehouse=<?php echo $warehouse['id'];?>" class="nav-link <?php echo ($warehouse_id == $warehouse['id']) ? 'active' : ''; ?>">
                        <i class="mdi mdi-home-variant d-md-none d-block"></i>
                        <span class="d-none d-md-block"><?php echo $warehouse['name'];?></span>
                    </a>
                </li>
                <?php $active='';}?>	
			</ul>
		</div>
	</div>
	
	<div class="col-md-12 mb-1">
		<div class="fixedElement new-fix" id="fixedElement">
			<ul class="nav nav-pills bg-nav-pills nav-justified ">
				<li class="nav-item">
					<a href="<?php echo base_url();?>quality-control/raw-material?warehouse=<?php echo $warehouse_id;?>" class="nav-link active">
						<i class="mdi mdi-home-variant d-md-none d-block"></i>
						<span class="d-none d-md-block">Pending</span>
					</a>
				</li>
				<li class="nav-item">
					<a href="<?php echo base_url();?>quality-control/raw-material-done?warehouse=<?php echo $warehouse_id;?>" class="nav-link ">
						<i class="mdi mdi-home-variant d-md-none d-block"></i>
						<span class="d-none d-md-block">Done</span>
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
      <!--            <h5 class="mb-0"><b>Total Reserved Order <span id="total_count"> (0)</span></b>-->
				  <!--</h5>-->
               </div>
            </div>
         </div>
         
        <div class="card-datatable d-report mb-2">
		       
		   <!--<a href="<?php echo site_url('inventory/raw-products/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_raw_products');?></span></a>          -->
     
		
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<th>Date</th>
					<th>Voucher No</th>
					<th>Supplier Name</th>
					<th>Product Name</th>
					<th>Quantity</th>
					<th>Bill No.</th>
					<th>Batch No.</th>
					<th>Expiry Date</th>
					<th>Action</th>
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
                "url": "<?php echo base_url('quality_control/get_raw_material'); ?>",
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
                { "data": "date" },
                { "data": "voucher_no" },
                { "data": "supplier_name" },
                { "data": "product_name" },
                { "data": "quantity" },
                { "data": "invoice_no" },
                { "data": "batch_no" },
                { "data": "expiry_date" },
                { "data": "action" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6]
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
</script>