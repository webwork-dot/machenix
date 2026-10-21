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

<style>
  .table-error td {
    background: #febdb9;
    color: #3c3a3a;
    font-weight: 600 !important;
  }

  .fixedElement {
    background: white;
    border-radius: .428rem;
  }

  .nav-pills.nav-justified .nav-item {
    display: flex;
    align-items: center;
  }

  .new-form .nav-pills .nav-link.active,
  .nav-pills .show>.nav-link {
    color: #1e652e;
    border: 1px solid #1e652e !important;
    background: white;
    box-shadow: initial;
    font-weight: 600;
  }

  .small-img {
    max-height: 50px;
    min-height: 50px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e7e6e6;
    height: 50px;
    max-width: 60px;
  }
</style>

<div class="row" id="table-bordered">
  <?php include('filter/po_expense_filter.php'); ?>
  <?php include('nav/nav_import_po.php'); ?>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Total PO Expense<span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <?php if (is_access('161')) { ?>
        <a href="<?php echo site_url('inventory/po-expense/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_expense');?></span></a>
        <?php } ?>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <?php if (is_access('153')) { ?><th>Batch No.</th><?php } ?>
              <?php if (is_access('154')) { ?><th>Vendor Name</th><?php } ?>
              <?php if (is_access('155')) { ?><th>Type</th><?php } ?>
              <?php if (is_access('156')) { ?><th>Expense Type</th><?php } ?>
              <?php if (is_access('157')) { ?><th>Net Amount Total</th><?php } ?>
              <?php if (is_access('158')) { ?><th>GST Amount Total</th><?php } ?>
              <?php if (is_access('159')) { ?><th>Final Amount Total</th><?php } ?>
              <?php if (is_access('160')) { ?><th>Expense Date</th><?php } ?>
              <?php if (is_access('162') || is_access('163')) { ?><th>Action</th><?php } ?>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function($) {
  dataTable = $('#report-datatable').DataTable({
    "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "ordering": false,
    "sDom": 'rt<"dtPagination"lp><"clear">',
    "pagingType": "simple_numbers",
    "paging": true,
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
      "url": "<?php echo base_url('inventory/get_po_expense'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = $('#filter_date_range').val() || '';
        data.type = $('#filter_type').val() || '';
        data.keywords = $('#filter_keywords').val() || '';
        data.datatype = <?php echo is_access('182') ? '1' : '0'; ?>;
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
      <?php if (is_access('153')) { ?>{ "data": "batch_no" },<?php } ?>
      <?php if (is_access('154')) { ?>{ "data": "company_name" },<?php } ?>
      <?php if (is_access('155')) { ?>{ "data": "type" },<?php } ?>
      <?php if (is_access('156')) { ?>{ "data": "expense_type" },<?php } ?>
      <?php if (is_access('157')) { ?>{ "data": "sub_total" },<?php } ?>
      <?php if (is_access('158')) { ?>{ "data": "gst_total" },<?php } ?>
      <?php if (is_access('159')) { ?>{ "data": "amount" },<?php } ?>
      <?php if (is_access('160')) { ?>{ "data": "date" },<?php } ?>
      <?php if (is_access('162') || is_access('163')) { ?>{ "data": "action" },<?php } ?>
    ],

    "buttons": [
      <?php if (is_access('164')) { ?>
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

    "infoCallback": function(settings, start, end, max, total, pre) {
      $(".loader").fadeOut("slow");
      var actualTotal = total > 0 ? total - 1 : 0;
      var actualEnd = end > 0 ? end - 1 : 0;
      $('#total_count').html('(' + actualTotal + ')');
      return 'Showing ' + start + ' to ' + actualEnd + ' of ' + actualTotal + ' entries';
    },

    'columnDefs': [{
      "targets": 0,
      "className": "text-center",
    }]
  }).on('draw.dt', function() {
    $(".loader").fadeOut("slow");
  });
});
</script>
