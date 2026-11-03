<link rel="stylesheet" type="text/css"
  href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
<script src="//cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>

<?php include('nav/nav_settings.php'); ?>
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Total Bank Accounts <span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">

        <?php if (is_access('992')) { ?>
        <a href="<?php echo site_url('inventory/bank-accounts/add'); ?>"
          class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0"
          aria-controls="DataTables_Table_0"><span><i class="feather icon-plus"></i>
            <?= get_phrase('add_bank_account');?></span></a>
        <?php } ?>

        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <?php if (is_access('988')) { ?><th>Name</th><?php } ?>
              <?php if (is_access('989')) { ?><th>IFSC Code</th><?php } ?>
              <?php if (is_access('990')) { ?><th>Bank Name</th><?php } ?>
              <?php if (is_access('991')) { ?><th>Account No</th><?php } ?>
              <?php if (is_access('993') || is_access('994')) { ?><th>Actions</th><?php } ?>
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
    "language": {
      sLengthMenu: "_MENU_",
      'processing': $('.loader').show()
    },
    "drawCallback": function(settings, json) {
      $('[data-toggle="tooltip"]').tooltip('update');
    },

    "ajax": {
      "url": "<?php echo base_url('inventory/get_bank_accounts'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        var date_range = "";
      },
      "beforeSend": function() {
        $('.loader').show();
      },
      "complete": function() {
        $('.loader').hide();
      }
    },
    columns: [
      { data: 'sr_no' },
      <?php if (is_access('988')) { ?>{ data: 'name' },<?php } ?>
      <?php if (is_access('989')) { ?>{ data: 'ifsc_code' },<?php } ?>
      <?php if (is_access('990')) { ?>{ data: 'bank_name' },<?php } ?>
      <?php if (is_access('991')) { ?>{ data: 'account_no' },<?php } ?>
      <?php if (is_access('993') || is_access('994')) { ?>{ data: 'action' },<?php } ?>
    ],
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    "pageLength": 10,
    "buttons": [
      <?php if (is_access('995')) { ?>
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
  });
});
</script>
