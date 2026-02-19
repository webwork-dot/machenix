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
  .sub-link {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    margin-right: 4px;
    background: white;
    padding: 8px 10px;
    min-width: 100px;
    text-align: center;
  }

  .sub-link.active {
    background: #5a79c0 !important;
    color: white;
  }
</style>

<div class="col-12 d-flex">
    <?php if($this->session->userdata('super_type') == 'Inventory'){ ?>
      <a href="<?php echo site_url('inventory/leads/all'); ?>" class="sub-link <?php echo ($status == 'all') ? 'active' : ''; ?>">All Leads</a>
    <?php } ?>
    <a href="<?php echo site_url('inventory/leads/new'); ?>" class="sub-link <?php echo ($status == 'new') ? 'active' : ''; ?>">New Leads</a>
    <a href="<?php echo site_url('inventory/leads/today'); ?>" class="sub-link <?php echo ($status == 'today') ? 'active' : ''; ?>">Todays Follow-up</a>
    <a href="<?php echo site_url('inventory/leads/upcoming'); ?>" class="sub-link <?php echo ($status == 'upcoming') ? 'active' : ''; ?>">Upcoming Follow-up</a>
    <a href="<?php echo site_url('inventory/leads/missed'); ?>" class="sub-link <?php echo ($status == 'missed') ? 'active' : ''; ?>">Missed Leads</a>
    <a href="<?php echo site_url('inventory/leads/lost'); ?>" class="sub-link <?php echo ($status == 'lost') ? 'active' : ''; ?>">Lost Leads</a>
    <a href="<?php echo site_url('inventory/leads/moved'); ?>" class="sub-link <?php echo ($status == 'moved') ? 'active' : ''; ?>">Move To Customer</a>
</div>

<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0px;">
        <div class="card-body">
          <div class="row">
              <div class="col-md-12 mt-10">
                <h5 class="mb-0"><b>Total Leads <span id="total_count"> (0)</span></b></h5>
              </div>
          </div>
        </div>
        <div class="card-datatable d-report mb-2">
        <?php if($status == 'all'){ ?>
		      <a href="<?php echo site_url('inventory/leads/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_leads');?></span></a>          
        <?php } ?>
          <table class="table leads-table" id="report-datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>Company Name</th>
                <th>Name</th>
                <th>Number</th>
                <?php if($status != 'all' && $status != 'moved'){ ?>
                  <th>Status</th>
                <?php } ?>
                <?php if($status == 'moved'){ ?>
                  <th>Move Date</th>
                <?php } ?>
                <?php if($this->session->userdata('super_type') == 'Inventory'){ ?>
                  <th>Staff</th>
                  <th>Added By</th>
                <?php } ?>
                <th>Actions</th>
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
                "url": "<?php echo base_url('inventory/get_customer'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                  var date_range="";			
                  data.type = "leads";
                  data.status = "<?php echo $status; ?>";
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
                { "data": "name" },
                { "data": "owner_name" },
                { "data": "owner_no" },
                <?php if($status != 'all' && $status != 'moved'){ ?>
                  { "data": "status" },
                <?php } ?>
                <?php if($status == 'moved'){ ?>
                  { "data": "move_date" },
                <?php } ?>
                <?php if($this->session->userdata('super_type') == 'Inventory'){ ?>
                    { "data": "staff" },
                    { "data": "added_by_name" },
                <?php } ?>
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