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
          <table class="table leads-table" id="report-datatable">
            <thead>
              <tr>
                  <th>#</th>
                  <?php if (is_access('618')) { ?><th>Distributor</th><?php } ?>
                  <?php if (is_access('619')) { ?><th>Company Name</th><?php } ?>
                  <?php if (is_access('620')) { ?><th>City</th><?php } ?>
                  <?php if (is_access('621')) { ?><th>State</th><?php } ?>
                  <?php if (is_access('622')) { ?><th>Owner Name</th><?php } ?>
                  <?php if (is_access('623')) { ?><th>Mobile/Whatsapp No</th><?php } ?>
                  <?php if (is_access('624')) { ?><th>Email</th><?php } ?>
                  <?php if (is_access('625')) { ?><th>Status</th><?php } ?>
                  <?php if (is_access('626')) { ?><th>Staff</th><?php } ?>
                  <?php if (is_access('627')) { ?><th>Added By</th><?php } ?>
                  <?php if (is_access('628')) { ?><th>Assigned Date</th><?php } ?>
                  <?php if (is_access('629') || is_access('630') || is_access('631') || is_access('632') || is_access('633')) { ?><th>Actions</th><?php } ?>
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
                  data.status = "missed";
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
                <?php if (is_access('618')) { ?>{ "data": "distributor" },<?php } ?>
                <?php if (is_access('619')) { ?>{ "data": "name" },<?php } ?>
                <?php if (is_access('620')) { ?>{ "data": "city_name" },<?php } ?>
                <?php if (is_access('621')) { ?>{ "data": "state_name" },<?php } ?>
                <?php if (is_access('622')) { ?>{ "data": "owner_name" },<?php } ?>
                <?php if (is_access('623')) { ?>{ "data": "mobile_whatsapp" },<?php } ?>
                <?php if (is_access('624')) { ?>{ "data": "owner_email" },<?php } ?>
                <?php if (is_access('625')) { ?>{ "data": "status" },<?php } ?>
                <?php if (is_access('626')) { ?>{ "data": "staff" },<?php } ?>
                <?php if (is_access('627')) { ?>{ "data": "added_by_name" },<?php } ?>
                <?php if (is_access('628')) { ?>{ "data": "assigned_date" },<?php } ?>
                <?php if (is_access('629') || is_access('630') || is_access('631') || is_access('632') || is_access('633')) { ?>{ "data": "action" },<?php } ?>
            ], 
           
            "buttons": [
                <?php if (is_access('634')) { ?>
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
