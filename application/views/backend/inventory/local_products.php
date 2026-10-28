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
.local-prod-field {
    min-width: 90px;
}
select.local-prod-field {
    min-width: 110px;
}
</style>
     
<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Local Products <span id="total_count"> (0)</span></b>
				  </h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <?php if (is_access('72')) { ?>
            <a href="<?php echo site_url('inventory/local-products/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" >
                <span><i class="feather icon-plus"></i> <?= get_phrase('add_products');?></span>
            </a>
            <?php } ?>
            <?php if (is_access('73')) { ?>
            <button type="button" id="btn-toggle-edit-mode" class="dt-button add-new desktop-tab add-btn btn btn-outline-primary" style="margin-right: 10px;" tabindex="0">
                <span><i class="feather icon-edit-2" id="mode-icon"></i> <span id="mode-text">Edit Mode</span></span>
            </button>
            <?php } ?>
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<?php if (is_access('59')) { ?><th>Image</th><?php } ?>
					<?php if (is_access('60')) { ?><th>Product Name</th><?php } ?>
					<?php if (is_access('61')) { ?><th>Alias Name</th><?php } ?>
					<?php if (is_access('62')) { ?><th>Category</th><?php } ?>
					<?php if (is_access('63')) { ?><th>Model No</th><?php } ?>
					<?php if (is_access('64')) { ?><th>HSN Code</th><?php } ?>
					<?php if (is_access('65')) { ?><th>Tax Rate</th><?php } ?>
					<?php if (is_access('66')) { ?><th>Commission</th><?php } ?>
					<?php if (is_access('67')) { ?><th>Min Billing Price</th><?php } ?>
					<?php if (is_access('68')) { ?><th>Min Selling Price</th><?php } ?>
					<?php if (is_access('69')) { ?><th>Stock Intimation</th><?php } ?>
					<?php if (is_access('70')) { ?><th>Off Sale Amt</th><?php } ?>
					<?php if (is_access('71')) { ?><th>Status</th><?php } ?>
					<?php if (is_access('73') || is_access('74') || is_access('75')) { ?><th>Actions</th><?php } ?>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">   
	var dataTable;
	var isEditMode = false;
	var localProductFieldTimers = {};

    $(document).ready(function($) {
		$('#btn-toggle-edit-mode').on('click', function(e) {
			e.preventDefault();

			// If an input is currently being edited, flush it immediately
			$(':focus.local-prod-field').each(function() {
				updateLocalProductField(this, true);
			});

			isEditMode = !isEditMode;
			if (isEditMode) {
				$(this).removeClass('btn-outline-primary').addClass('btn-primary');
				$('#mode-icon').removeClass('icon-edit-2').addClass('icon-eye');
				$('#mode-text').text('View Mode');
			} else {
				$(this).removeClass('btn-primary').addClass('btn-outline-primary');
				$('#mode-icon').removeClass('icon-eye').addClass('icon-edit-2');
				$('#mode-text').text('Edit Mode');
			}

			if (dataTable) {
				dataTable.ajax.reload(null, false);
			}
		});

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
                "url": "<?php echo base_url('inventory/get_local_products'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                       var date_range="";
                       data.edit_mode = isEditMode ? 1 : 0;
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
                <?php if (is_access('59')) { ?>{ "data": "image" },<?php } ?>
                <?php if (is_access('60')) { ?>{ "data": "name" },<?php } ?>
                <?php if (is_access('61')) { ?>{ "data": "alias" },<?php } ?>
                <?php if (is_access('62')) { ?>{ "data": "category_name" },<?php } ?>
                <?php if (is_access('63')) { ?>{ "data": "item_code" },<?php } ?>
                <?php if (is_access('64')) { ?>{ "data": "hsn_code" },<?php } ?>
                <?php if (is_access('65')) { ?>{ "data": "gst" },<?php } ?>
                <?php if (is_access('66')) { ?>{ "data": "commission_id" },<?php } ?>
                <?php if (is_access('67')) { ?>{ "data": "product_mrp" },<?php } ?>
                <?php if (is_access('68')) { ?>{ "data": "costing_price" },<?php } ?>
                <?php if (is_access('69')) { ?>{ "data": "intimation" },<?php } ?>
                <?php if (is_access('70')) { ?>{ "data": "off_sale_price" },<?php } ?>
                <?php if (is_access('71')) { ?>{ "data": "status" },<?php } ?>
                <?php if (is_access('73') || is_access('74') || is_access('75')) { ?>{ "data": "action" },<?php } ?>
            ], 
           
            "buttons": [
                <?php if (is_access('76')) { ?>
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

	function updateLocalProductField(elemt, immediate) {
		var $el = $(elemt);
		var id = $el.data('id');
		var field = $el.data('field');
		var value = $el.val();
		var key = id + '_' + field;
		var isSelect = $el.is('select');

		if (localProductFieldTimers[key]) {
			clearTimeout(localProductFieldTimers[key]);
		}

		var delay = (immediate || isSelect) ? 0 : 500;
		localProductFieldTimers[key] = setTimeout(function() {
			$.ajax({
				type: "POST",
				url: "<?php echo base_url(); ?>inventory/update_local_product_field",
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
