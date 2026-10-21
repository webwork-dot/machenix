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
  <?php include('filter/loading_list_po_filter.php'); ?>
  <?php include('nav/nav_import_po.php'); ?>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Total Loading List<span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <?php if (is_access('129')) { ?>
        <a href="<?php echo site_url('inventory/loading-list-po/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_loading_list');?></span></a>
        <?php } ?>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <?php if (is_access('118')) { ?><th>PO Date / Batch No.</th><?php } ?>
              <?php if (is_access('119')) { ?><th>Supplier Name</th><?php } ?>
              <?php if (is_access('120')) { ?><th>No of Spare Parts</th><?php } ?>
              <?php if (is_access('121')) { ?><th>No of Ready Goods</th><?php } ?>
              <?php if (is_access('122')) { ?><th>Loading Qty</th><?php } ?>
              <?php if (is_access('123')) { ?><th>Official Qty</th><?php } ?>
              <?php if (is_access('124')) { ?><th>Total RMB</th><?php } ?>
              <?php if (is_access('125')) { ?><th>Total USD</th><?php } ?>
              <?php if (is_access('126')) { ?><th>Loading Date</th><?php } ?>
              <?php if (is_access('127')) { ?><th>Expected Date</th><?php } ?>
              <?php if (is_access('128')) { ?><th>Status</th><?php } ?>
              <?php if (is_access('130') || is_access('131') || is_access('132') || is_access('133') || is_access('134')) { ?><th>Action</th><?php } ?>
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
      "url": "<?php echo base_url('inventory/get_loading_list_po'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = $('#filter_date_range').val() || '';
        data.loading_date_range = $('#filter_loading_date_range').val() || '';
        data.expected_date_range = $('#filter_expected_date_range').val() || '';
        data.search.value = $('#filter_keywords').val() || '';
        data.status = $('#filter_status').val() || '';
        data.datatype = <?php echo is_access('900') ? '1' : '0'; ?>;
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
      <?php if (is_access('118')) { ?>{ "data": "date" },<?php } ?>
      <?php if (is_access('119')) { ?>{ "data": "lo_suppliers" },<?php } ?>
      <?php if (is_access('120')) { ?>{ "data": "ll_ready_goods_count" },<?php } ?>
      <?php if (is_access('121')) { ?>{ "data": "ll_spare_parts_count" },<?php } ?>
      <?php if (is_access('122')) { ?>{ "data": "loading_qty" },<?php } ?>
      <?php if (is_access('123')) { ?>{ "data": "official_qty" },<?php } ?>
      <?php if (is_access('124')) { ?>{ "data": "total_rmb" },<?php } ?>
      <?php if (is_access('125')) { ?>{ "data": "total_usd" },<?php } ?>
      <?php if (is_access('126')) { ?>{ "data": "arrival_date" },<?php } ?>
      <?php if (is_access('127')) { ?>{ "data": "expected_arrival_date" },<?php } ?>
      <?php if (is_access('128')) { ?>{ "data": "status" },<?php } ?>
      <?php if (is_access('130') || is_access('131') || is_access('132') || is_access('133') || is_access('134')) { ?>{ "data": "loading_list_action" },<?php } ?>
    ],

    "buttons": [
      <?php if (is_access('135')) { ?>
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
      $('#total_count').html('(' + total + ')');
      return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
    },

    'columnDefs': [{
      "targets": 0,
      "className": "text-center",
    }]
  }).on('draw.dt', function() {
    $(".loader").fadeOut("slow");
  });
});

function generate_excel(id) {
  window.location.href = "<?php echo base_url(); ?>inventory/generate_priotity_purchase_order_excel/" + id;
}

$(document).ready(function() {
  setInterval(function() {
    if (document.querySelector('.cke_notification_close')) {
      document.querySelector('.cke_notification_close').click();
    }
  }, 500);
});
</script>
