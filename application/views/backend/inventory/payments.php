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
  <?php include('filter/date_range.php'); ?>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Total Payments<span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <?php if (is_access('730')) { ?>
        <a href="<?php echo site_url('inventory/payments/add'); ?>" class="dt-button add-new desktop-tab  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i class="feather icon-plus"></i> <?= get_phrase('add_payments');?></span></a>
        <?php } ?>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <?php if (is_access('722')) { ?><th>Batch No.</th><?php } ?>
              <?php if (is_access('723')) { ?><th>Supplier Name</th><?php } ?>
              <?php if (is_access('724')) { ?><th>Invoice No</th><?php } ?>
              <?php if (is_access('725')) { ?><th>Payment Type</th><?php } ?>
              <?php if (is_access('726')) { ?><th>Amount (Dollar)</th><?php } ?>
              <?php if (is_access('727')) { ?><th>Amount (INR)</th><?php } ?>
              <?php if (is_access('728')) { ?><th>Amount (RMB)</th><?php } ?>
              <?php if (is_access('729')) { ?><th>Date</th><?php } ?>
              <?php if (is_access('731') || is_access('732')) { ?><th>Action</th><?php } ?>
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
      "url": "<?php echo base_url('inventory/get_payments'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
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
      <?php if (is_access('722')) { ?>{ "data": "batch_no" },<?php } ?>
      <?php if (is_access('723')) { ?>{ "data": "supplier_name" },<?php } ?>
      <?php if (is_access('724')) { ?>{ "data": "invoice_no" },<?php } ?>
      <?php if (is_access('725')) { ?>{ "data": "type" },<?php } ?>
      <?php if (is_access('726')) { ?>{ "data": "amount_dollar" },<?php } ?>
      <?php if (is_access('727')) { ?>{ "data": "amount_inr" },<?php } ?>
      <?php if (is_access('728')) { ?>{ "data": "amount_rmb" },<?php } ?>
      <?php if (is_access('729')) { ?>{ "data": "date" },<?php } ?>
      <?php if (is_access('731') || is_access('732')) { ?>{ "data": "actions" },<?php } ?>
    ],

    "buttons": [
      <?php if (is_access('733')) { ?>
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
    }, ]

  }).on('draw.dt', function() {
    $(".loader").fadeOut("slow");
  });
});

</script>
