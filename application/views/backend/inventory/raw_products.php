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
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Products <span id="total_count"> (0)</span></b>
				  </h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <a href="<?php echo site_url('inventory/raw-products/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" >
                <span><i class="feather icon-plus"></i> <?= get_phrase('add_products');?></span>
            </a>
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<th>Image</th>
					<th>Product Name</th>
					<th>Alias Name</th>
					<th>Category</th>
					<th>Model No</th>
					<th>HSN Code</th>
					<th>Duty Charge</th>
					<th>Tax Rate</th>
					<th>Unit</th>
					<th>Commission</th>
					<th>Min Billing Price</th>
					<th>Min Selling Price</th>
					<th>Stock Intimation</th>
					<th>Opening Stock</th>
					<th>Off Sale Amt</th>
					<th>Status</th>
					<th>Actions</th>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">   
	var dataTable;
	var rawProductFieldTimers = {};

    $(document).ready(function($) {
    	dataTable = $('#report-datatable').DataTable({ 
        "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            "scrollX": true,
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
                "url": "<?php echo base_url('inventory/get_raw_products'); ?>",
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
                { "data": "image" },
                { "data": "name" },
                { "data": "alias" },
                { "data": "category_name" },
                { "data": "item_code" },
                { "data": "hsn_code" },
                { "data": "duty_charge" },
                { "data": "gst" },
                { "data": "unit" },
                { "data": "commission_id" },
                { "data": "product_mrp" },
                { "data": "costing_price" },
                { "data": "intimation" },
                { "data": "opening_stock" },
                { "data": "off_sale_price" },
                { "data": "status" },
                { "data": "action" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                       "columns": [0, 2, 4],

                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0, 2, 4]
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
                    "targets": 0,
                    "className": "text-center",
                },
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });
    });

	function updateRawProductField(elemt) {
		var $el = $(elemt);
		var id = $el.data('id');
		var field = $el.data('field');
		var value = $el.val();
		var key = id + '_' + field;
		var isSelect = $el.is('select');

		if (rawProductFieldTimers[key]) {
			clearTimeout(rawProductFieldTimers[key]);
		}

		var delay = isSelect ? 0 : 500;
		rawProductFieldTimers[key] = setTimeout(function() {
			$.ajax({
				type: "POST",
				url: "<?php echo base_url(); ?>inventory/update_raw_product_field",
				dataType: "json",
				data: {
					id: id,
					field: field,
					value: value
				},
				success: function(res) {
					if (res.status != 200) {
						alert(res.message || 'Update failed');
					}
				},
				error: function() {
					alert('Update failed');
				}
			});
		}, delay);
	}
	
</script>
