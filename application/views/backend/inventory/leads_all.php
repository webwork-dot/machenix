<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>

<?php include('nav/nav_leads.php'); ?>

<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0px;">
        <div class="card-body">
          <div class="row">
              <div class="col-md-12 mt-10">
                <h5 class="mb-0">
                  <b>Total Leads <span id="total_count"> (0)</span></b>
                  <?php if (is_access('548')) { ?>
                  <button type="button" id="btn_assign_leads" class="btn btn-primary float-end" style="margin-top: -5px;" disabled>Assign leads (0)</button>
                  <?php } ?>
                </h5>
              </div>
          </div>
        </div>
        <div class="card-datatable d-report mb-2">
            <?php if (is_access('546')) { ?>
		      <a href="<?php echo site_url('inventory/leads/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_leads');?></span></a>
            <?php } ?>
            <?php if (is_access('547')) { ?>
		      <a href="<?php echo site_url('inventory/leads/import'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-outline-primary" style="margin-left: 6px;" tabindex="0"><span><i class="feather icon-file-text"></i> <?= get_phrase('import_excel');?></span></a>
            <?php } ?>
          <table class="table leads-table" id="report-datatable">
            <thead>
              <tr>
                  <th style="width: 40px;"><?php if (is_access('548')) { ?><input type="checkbox" class="form-check-input" id="select-all-leads"><?php } else { ?>#<?php } ?></th>
                  <?php if (is_access('539')) { ?><th>Distributor</th><?php } ?>
                  <?php if (is_access('540')) { ?><th>Company Name</th><?php } ?>
                  <?php if (is_access('541')) { ?><th>City</th><?php } ?>
                  <?php if (is_access('542')) { ?><th>State</th><?php } ?>
                  <?php if (is_access('543')) { ?><th>Owner Name</th><?php } ?>
                  <?php if (is_access('544')) { ?><th>Mobile/Whatsapp No</th><?php } ?>
                  <?php if (is_access('545')) { ?><th>Email</th><?php } ?>
                  <?php if (is_access('549') || is_access('550') || is_access('551') || is_access('552')) { ?><th>Actions</th><?php } ?>
              </tr>
            </thead>
          </table>
        </div>
      </div>
   </div>
</div>

<script type="text/javascript">
    function updateAssignLeadsButton() {
        var count = $('.lead-checkbox:checked').length;
        var $btn = $('#btn_assign_leads');
        if ($btn.length) {
            $btn.prop('disabled', count === 0).text('Assign leads (' + count + ')');
        }
        var totalVisible = $('.lead-checkbox').length;
        $('#select-all-leads').prop('checked', totalVisible > 0 && count === totalVisible);
    }

    $(document).ready(function($) {
        $(document).on('change', '.lead-checkbox', function() {
            updateAssignLeadsButton();
        });

        $(document).on('change', '#select-all-leads', function() {
            $('.lead-checkbox').prop('checked', $(this).is(':checked'));
            updateAssignLeadsButton();
        });

        $('#btn_assign_leads').on('click', function() {
            var selectedIds = [];
            $('.lead-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                return;
            }

            var url = "<?php echo base_url(); ?>modal/popup_inventory/customer_reinitiate_modal/0?lead_ids=" + selectedIds.join(',');
            showAjaxModal(url, 'Assign Staff');
        });

    	$('#report-datatable').DataTable({ 
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
                updateAssignLeadsButton();
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_customer'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                  data.type = "leads";
                  data.status = "all";
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
                <?php if (is_access('539')) { ?>{ "data": "distributor" },<?php } ?>
                <?php if (is_access('540')) { ?>{ "data": "name" },<?php } ?>
                <?php if (is_access('541')) { ?>{ "data": "city_name" },<?php } ?>
                <?php if (is_access('542')) { ?>{ "data": "state_name" },<?php } ?>
                <?php if (is_access('543')) { ?>{ "data": "owner_name" },<?php } ?>
                <?php if (is_access('544')) { ?>{ "data": "mobile_whatsapp" },<?php } ?>
                <?php if (is_access('545')) { ?>{ "data": "owner_email" },<?php } ?>
                <?php if (is_access('549') || is_access('550') || is_access('551') || is_access('552')) { ?>{ "data": "action" },<?php } ?>
            ], 
           
            "buttons": [
                <?php if (is_access('553')) { ?>
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
</script>
