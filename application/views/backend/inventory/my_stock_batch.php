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
	.new-form .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
		color: #1e652e;
		border: 1px solid #1e652e !important;
		background: white;
		box-shadow: initial;
		font-weight: 600;
	}
	#report-datatable thead th {
		white-space: nowrap;
	}
	.batch-no-tag {
		font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
		font-size: 11px;
		font-weight: 600;
		color: #0284c7;
		background: #f0f9ff;
		border: 1px solid #e0f2fe;
		padding: 1px 6px;
		border-radius: 3px;
		display: inline-block;
	}
	.stk-badge {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 26px;
		padding: 2px 6px;
		font-size: 11.5px;
		font-weight: 600;
		line-height: 1.3;
		border-radius: 4px;
		text-decoration: none !important;
		font-variant-numeric: tabular-nums;
	}
	.stk-badge-zero {
		color: #94a3b8;
		font-weight: 400;
		font-size: 11.5px;
	}
	.stk-qty-main {
		font-weight: 700;
		color: #0f172a;
		font-size: 12.5px;
		font-variant-numeric: tabular-nums;
	}
	.stk-badge-black {
		background-color: #f1f5f9;
		color: #334155;
		border: 1px solid #e2e8f0;
	}
	.stk-badge-white {
		background-color: #eff6ff;
		color: #1d4ed8;
		border: 1px solid #dbeafe;
	}
	.stk-badge-pending {
		background-color: #fffbeb;
		color: #b45309;
		border: 1px solid #fef3c7;
	}
	.stk-badge-total-white {
		background-color: #e0e7ff;
		color: #4338ca;
		border: 1px solid #c7d2fe;
		font-weight: 700;
	}
	.stk-badge-booked {
		background-color: #fff1f2;
		color: #e11d48;
		border: 1px solid #ffe4e6;
	}
	.stk-cost {
		font-size: 12px;
		font-variant-numeric: tabular-nums;
		white-space: nowrap;
	}
	.stk-cost.stk-zero {
		color: #94a3b8;
	}
	.stk-cost-accent {
		font-weight: 600;
		color: #0f172a;
	}
</style>
<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
        <div class="card-body">
           <div class="row">
              <div class="col-md-12 mt-10">
              </div>
           </div>
        </div>
         
        <div class="card-datatable d-report mb-2">
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th class="text-center" style="width: 35px;">#</th>
					<th>Batch No</th>
					<th class="text-end">Quantity</th>
					<th class="text-end">Black Qty</th>
					<th class="text-end">White Qty</th>
					<th class="text-end">Pending Qty</th>
					<th class="text-end">Total White Qty</th>
					<th class="text-end">Booked Qty</th>
					<th class="text-end">PO Qty</th>
					<th class="text-end">Priority Qty</th>
					<th class="text-end">Loading Qty</th>
					<th class="text-end">Actual Cost Per Pc with Exp</th>
					<th class="text-end">Actual Cost with Exp</th>
					<th class="text-end">Actual Cost Per Pc Net Amt</th>
					<th class="text-end">Actual Cost Net Amt</th>
					<th class="text-end">Official Cost Per Pc with Exp</th>
					<th class="text-end">Official Cost with Exp</th>
					<th class="text-end">Official Cost Per Pc Net Amt</th>
					<th class="text-end">Official Cost Net Amt</th>
					<th class="text-center">Actions</th>
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
                "url": "<?php echo base_url('inventory/get_my_stock_batch'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.product_id = '<?php echo $product_id; ?>';			
                    data.warehouse_id = '<?php echo $warehouse_id; ?>';			
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                { "data": "sr_no", "className": "text-center" },
                { "data": "batch_no" },
                { "data": "quantity", "className": "text-end" },
                { "data": "black_qty", "className": "text-end" },
                { "data": "white_qty", "className": "text-end" },
                { "data": "pending_qty", "className": "text-end" },
                { "data": "total_white_qty", "className": "text-end" },
                { "data": "booked_qty", "className": "text-end" },
                { "data": "po_qty", "className": "text-end" },
                { "data": "priority_qty", "className": "text-end" },
                { "data": "loading_qty", "className": "text-end" },
                { "data": "actual_cost_per_pc_with_exp", "className": "text-end" },
                { "data": "actual_cost_with_exp", "className": "text-end" },
                { "data": "actual_cost_per_pc_net", "className": "text-end" },
                { "data": "actual_cost_net", "className": "text-end" },
                { "data": "official_cost_per_pc_with_exp", "className": "text-end" },
                { "data": "official_cost_with_exp", "className": "text-end" },
                { "data": "official_cost_per_pc_net", "className": "text-end" },
                { "data": "official_cost_net", "className": "text-end" },
                { "data": "action", "className": "text-center" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18]
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
</script>
