<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>

<?php 
  $type_label = ($report_type == 'calls') ? 'Customer Calls & Leads' : 'Sales Orders';
  $dur_label  = '';
  switch ($duration) {
      case '0_30': $dur_label = '0 to 30 Days'; break;
      case '31_60': $dur_label = '31 to 60 Days'; break;
      case '61_90': $dur_label = '61 to 90 Days'; break;
      case '90_plus': $dur_label = '90+ Days'; break;
      case 'no_orders': $dur_label = 'No Orders'; break;
      case 'no_calls': $dur_label = 'No Calls'; break;
      default: $dur_label = str_replace('_', ' ', $duration); break;
  }
  $is_inventory = ($this->session->userdata('super_type') == 'Inventory');
?>

<div class="row mb-1">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <a href="<?php echo site_url('inventory/dashboard'); ?>" class="btn btn-outline-primary waves-effect">
        <i class="feather icon-arrow-left me-25"></i> Back to Dashboard
      </a>
    </div>
    <div>
      <span class="badge bg-light-primary text-primary fs-6 px-1 py-50">
        <i class="feather <?php echo ($report_type == 'calls') ? 'icon-phone-call' : 'icon-shopping-cart'; ?> me-25"></i> 
        <?php echo $type_label; ?> — <?php echo $dur_label; ?>
      </span>
    </div>
  </div>
</div>

<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12">
                  <h5 class="mb-0"><b>Customer List <span id="total_count"> (0)</span></b></h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
          <table class="table customer-report-table" id="customer-report-datatable">
               <thead>
                  <tr>
					<th>#</th>
					<th>Company Name</th>
					<th>Contact Person</th>
					<th>Mobile Number</th>
                    <th><?php echo ($report_type == 'calls') ? 'Last Call Date' : 'Last Order Date'; ?></th>
                    <th>Duration</th>
                    <th>GST Name</th>
                    <th>Pincode</th>
                    <?php if($is_inventory){ ?>
                        <th>Added By</th>
					<?php } ?>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">       
    $(document).ready(function($) {
    	var dataTable = $('#customer-report-datatable').DataTable({ 
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
                "url": "<?php echo base_url('inventory/get_customer_report'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                       data.report_type = "<?php echo $report_type; ?>";
                       data.duration    = "<?php echo $duration; ?>";
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
                { "data": "company_name" },
                { "data": "contact_person" },
                { "data": "mobile" },
                { "data": "last_date" },
                { "data": "days" },
                { "data": "gst_name" },
                { "data": "pincode" },
                <?php if($is_inventory){ ?>
                    { "data": "added_by_name" },
                <?php } ?>
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
                    "exportOptions": {
                       "columns": <?php echo $is_inventory ? '[0,1,2,3,4,5,6,7,8]' : '[0,1,2,3,4,5,6,7]'; ?>
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": <?php echo $is_inventory ? '[0,1,2,3,4,5,6,7,8]' : '[0,1,2,3,4,5,6,7]'; ?>
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
