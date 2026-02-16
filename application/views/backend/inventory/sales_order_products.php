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
</style>

<div class="card">
     <div class="card-body">
        <div class="row">
           <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Order No <span class="required">*</span></label>
                    <input type="text" class="form-control" placeholder="Order No" name="order_no" value="<?php echo $data['order_no'];?>" required="" readonly>
                </div>
            </div>
            
            <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Refrence Order No </label>
                    <input type="text" class="form-control" placeholder="Enter Order No" name="refrence_no" value="<?php echo $data['refrence_no'];?>" readonly>
                </div>
            </div>
            
            <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Date <span class="required">*</span></label>
                    <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>" value="<?php echo $data['date'];?>" readonly id="date_picker">
                </div>
            </div>
			
			<div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Customer </label>
                    <input type="text" class="form-control" name="customer_name" value="<?php echo $data['customer_name'];?>" readonly>
                </div>
            </div>
			
			<div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Warehouse </label>
                    <input type="text" class="form-control" name="warehouse_name" value="<?php echo $data['warehouse_name'];?>" readonly>
                </div>
            </div>
			<div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Company </label>
                    <input type="text" class="form-control" name="warehouse_name" value="<?php echo $data['company_name'];?>" readonly>
                </div>
            </div>
			
			<div class="col-12 col-sm-6 mb-1 mt-1"></div>
            
            <div class="col-12 col-sm-6 mb-1 mt-1">
                <div class="form-group">
                    <label>Narration</label>
                    <textarea class="form-control" placeholder="" rows="1" name="narration" id="narration" readonly>
					<?php echo trim($data['narration']);?></textarea>
                </div>
            </div>
			
            <div class="col-12 col-sm-6 mb-1 mt-1">
                <div class="form-group">
                    <label>Remark</label>
                    <textarea class="form-control" placeholder="" rows="1" name="remark" id="remark" readonly><?php echo trim($data['remark']);?></textarea>
                </div>
            </div>
        </div>
     </div>
</div>

<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0">
                      <b>Total Sale Order Products <span id="total_count"> (0)</span></b>
                  </h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
            
            <a href="#" class="dt-button add-new desktop-tab add-btn btn btn-outline-primary" tabindex="0" aria-controls="DataTables_Table_0" > Payment (<span class="show-selected">0</span>)</a>   
		    <a href="#" class="dt-button add-new desktop-tab add-btn mx-1 btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" > Return (<span class="show-selected">0</span>)</a>          
     
			<table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<th>Order ID</th>
					<th>Product Name</th>
					<th>Color</th>
					<th>Size</th>
					<th>Quantity</th>
					<th>Amount</th>
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
    	"dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true, 
            "lengthChange": true, 
			"pageLength": 1000,
			"lengthMenu" : [
                   [10, 25, 50,100,500,1000, -1],
                    [10, 25, 50,100,500,1000, 'All']
            ],
            "language" : {
                sLengthMenu: "_MENU_",
                'processing': $('.loader').show()
            },	
            "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
				mergeRowsBasedOnProductName();
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_sales_order_products/'.$id); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                       var date_range="";			
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
                { "data": "order_id" },
                { "data": "product_name" },
                { "data": "color_name" },
                { "data": "size_name" },
                { "data": "qty" },
                { "data": "total_amount" },
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
		
		function mergeRowsBasedOnProductName() {
        // The column index of "Product Name"
			let productNameIndex = 1;

			// The columns to merge when "Product Name" matches
			let columnsToMerge = [1];

			var last = null;
			var rowspan = 1;

			$('#report-datatable').find('tr').each(function(i) {
				var currentProductCell = $(this).find('td:eq(' + productNameIndex + ')');

				if (last !== null && currentProductCell.text() === last.text()) {
					rowspan++;
					
					// Hide the current row's cells and update the rowspan for the last row's cells
					columnsToMerge.forEach(function(colIndex) {
						var currentCell = $(this).find('td:eq(' + colIndex + ')');
						var lastCell = last.closest('tr').find('td:eq(' + colIndex + ')');

						lastCell.attr('rowspan', rowspan);
						currentCell.hide();
					}.bind(this));
				} else {
					last = currentProductCell;
					rowspan = 1;
				}
			});
		}
		
    });
    
	function getReturnId(e) {
        let products = 0;
        document.querySelectorAll('.product-id').forEach((ele) => {
            if(ele.checked) {
                products++;
            }
        });
        
        $('.show-selected').html(products);
	}
</script>