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
            <h5 class="mb-0"><b>Supplier Adjustments</b></h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <a href="<?php echo site_url('add-supplier-adjustment'); ?>"
          class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0"
          aria-controls="DataTables_Table_0">
          <span><i class="feather icon-plus"></i> Add Adjustment</span>
        </a>

        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Supplier Name</th>
              <th>Batch No</th>
              <th>USD</th>
              <th>RMB</th>
              <th>INR</th>
              <th>Amt Type</th>
              <th>Type</th>
              <th>Remark</th>
              <th>Added By</th>
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
    "pagingType": "simple_numbers",
    "processing": true,
    "scrollX": true,
    "serverSide": true,
    "lengthChange": true,
    "language": {
      sLengthMenu: "_MENU_",
      'processing': $('.loader').show()
    },
    "drawCallback": function(settings, json) {
      if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip('update');
      }
    },
    "ajax": {
      "url": "<?php echo base_url('inventory/get_supplier_adjustment'); ?>",
      "dataType": "json",
      "type": "POST",
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
      { "data": "supplier_name" },
      { "data": "batch_no" },
      { "data": "usd" },
      { "data": "rmb" },
      { "data": "inr" },
      { "data": "amt_type" },
      { "data": "type" },
      { "data": "remark" },
      { "data": "added_by" },
      { "data": "action" }
    ],
    "buttons": [
      {
        "extend": 'excel',
        "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
        "exportOptions": {
          "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        }
      }
    ]
  });
});
</script>
