<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>


<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10 hidden">
                  <h5 class="mb-0"><b>Total Sales Return <span id="total_count"> (0)</span></b>
				  </h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
		
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<th>Date</th>
					<!--<th>Invoice No</th>-->
					<th>Product Name</th>
					<th>Size</th>
					<th>Qty</th>
					<th>Amount</th>
					<th>Total Qty</th>
					<th>Total Amount</th>
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
			"pageLength": 1000,
            "lengthChange": false,  
            "language" : {
                sLengthMenu: "_MENU_",
                'processing': $('.loader').show()
            },	
            "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
				// mergeRowsBasedOnProductName();
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_purchase_order_entry_history/'.$id); ?>",
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
                { "data": "date" },
                // { "data": "invoice_no" },
                { "data": "product_name" },
                { "data": "item_code" },
                { "data": "product_qty" },
                { "data": "basic_amount" },
                { "data": "total_qty" },
                { "data": "total_amt" },
            ],
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    
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
		
		function mergeRowsBasedOnProductName() {
            // The column index of "Product Name"
			let productNameIndex = 3;

			// The columns to merge when "Product Name" matches
			let columnsToMerge = [0, 1, 2, 3,7,8];

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
</script>