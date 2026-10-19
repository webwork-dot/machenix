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
                </h5>
              </div>
          </div>
        </div>
        <div class="card-datatable d-report mb-2">
        <?php if (is_access('565')) { ?>
		      <a href="<?php echo site_url('inventory/leads/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_leads');?></span></a>
        <?php } ?>
          <table class="table leads-table" id="report-datatable">
            <thead>
              <tr>
                  <th>#</th>
                  <?php if (is_access('554')) { ?><th>Distributor</th><?php } ?>
                  <?php if (is_access('555')) { ?><th>Company Name</th><?php } ?>
                  <?php if (is_access('556')) { ?><th>City</th><?php } ?>
                  <?php if (is_access('557')) { ?><th>State</th><?php } ?>
                  <?php if (is_access('558')) { ?><th>Owner Name</th><?php } ?>
                  <?php if (is_access('559')) { ?><th>Mobile/Whatsapp No</th><?php } ?>
                  <?php if (is_access('560')) { ?><th>Email</th><?php } ?>
                  <?php if (is_access('561')) { ?><th>Status</th><?php } ?>
                  <?php if (is_access('562')) { ?><th>Staff</th><?php } ?>
                  <?php if (is_access('563')) { ?><th>Added By</th><?php } ?>
                  <?php if (is_access('564')) { ?><th>Assigned Date</th><?php } ?>
                  <?php if (is_access('566') || is_access('567') || is_access('568') || is_access('569') || is_access('570')) { ?><th>Actions</th><?php } ?>
              </tr>
            </thead>
          </table>
        </div>
      </div>
   </div>
</div>

<script type="text/javascript">
    $(document).ready(function($) {
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
            },
      
            "ajax":{
                "url": "<?php echo base_url('inventory/get_customer'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                  data.type = "leads";
                  data.status = "new";
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
                <?php if (is_access('554')) { ?>{ "data": "distributor" },<?php } ?>
                <?php if (is_access('555')) { ?>{ "data": "name" },<?php } ?>
                <?php if (is_access('556')) { ?>{ "data": "city_name" },<?php } ?>
                <?php if (is_access('557')) { ?>{ "data": "state_name" },<?php } ?>
                <?php if (is_access('558')) { ?>{ "data": "owner_name" },<?php } ?>
                <?php if (is_access('559')) { ?>{ "data": "mobile_whatsapp" },<?php } ?>
                <?php if (is_access('560')) { ?>{ "data": "owner_email" },<?php } ?>
                <?php if (is_access('561')) { ?>{ "data": "status" },<?php } ?>
                <?php if (is_access('562')) { ?>{ "data": "staff" },<?php } ?>
                <?php if (is_access('563')) { ?>{ "data": "added_by_name" },<?php } ?>
                <?php if (is_access('564')) { ?>{ "data": "assigned_date" },<?php } ?>
                <?php if (is_access('566') || is_access('567') || is_access('568') || is_access('569') || is_access('570')) { ?>{ "data": "action" },<?php } ?>
            ], 
           
            "buttons": [
                <?php if (is_access('571')) { ?>
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
