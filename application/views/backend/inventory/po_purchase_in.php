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
  <?php include('filter/po_purchase_in_filter.php'); ?>
  <?php include('nav/nav_import_po.php'); ?>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Total Purchase In<span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <?php if (is_access('136')) { ?><th>Date / Batch No.</th><?php } ?>
              <?php if (is_access('137')) { ?><th>BOE No.</th><?php } ?>
              <?php if (is_access('138')) { ?><th>BOE Date.</th><?php } ?>
              <?php if (is_access('139')) { ?><th>Supplier Name</th><?php } ?>
              <?php if (is_access('140')) { ?><th>No of Spare Parts</th><?php } ?>
              <?php if (is_access('141')) { ?><th>No of Ready Goods</th><?php } ?>
              <?php if (is_access('142')) { ?><th>Loading Qty</th><?php } ?>
              <?php if (is_access('143')) { ?><th>Official Qty</th><?php } ?>
              <?php if (is_access('144')) { ?><th>Total RMB</th><?php } ?>
              <?php if (is_access('145')) { ?><th>Total USD</th><?php } ?>
              <?php if (is_access('146')) { ?><th>Loading Date</th><?php } ?>
              <?php if (is_access('147')) { ?><th>Status</th><?php } ?>
              <?php if (is_access('148') || is_access('149') || is_access('150') || is_access('151')) { ?><th>Action</th><?php } ?>
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
      "url": "<?php echo base_url('inventory/get_po_purchase_in'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = $('#filter_date_range').val() || '';
        data.loading_date_range = $('#filter_loading_date_range').val() || '';
        data.expected_date_range = $('#filter_expected_date_range').val() || '';
        data.search.value = $('#filter_keywords').val() || '';
        data.status = $('#filter_status').val() || '';
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
      <?php if (is_access('136')) { ?>{ "data": "date" },<?php } ?>
      <?php if (is_access('137')) { ?>{ "data": "boe_no" },<?php } ?>
      <?php if (is_access('138')) { ?>{ "data": "boe_date" },<?php } ?>
      <?php if (is_access('139')) { ?>{ "data": "lo_suppliers" },<?php } ?>
      <?php if (is_access('140')) { ?>{ "data": "ll_ready_goods_count" },<?php } ?>
      <?php if (is_access('141')) { ?>{ "data": "ll_spare_parts_count" },<?php } ?>
      <?php if (is_access('142')) { ?>{ "data": "loading_qty" },<?php } ?>
      <?php if (is_access('143')) { ?>{ "data": "official_qty" },<?php } ?>
      <?php if (is_access('144')) { ?>{ "data": "total_rmb" },<?php } ?>
      <?php if (is_access('145')) { ?>{ "data": "total_usd" },<?php } ?>
      <?php if (is_access('146')) { ?>{ "data": "delivery_date" },<?php } ?>
      <?php if (is_access('147')) { ?>{ "data": "status" },<?php } ?>
      <?php if (is_access('148') || is_access('149') || is_access('150') || is_access('151')) { ?>{ "data": "purchase_in_action" },<?php } ?>
    ],

    "buttons": [
      <?php if (is_access('152')) { ?>
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

function revertPurchaseIn(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will move the Po back to the loading list and delete items from inventory. This can only be undone if the stock is still available!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, revert it!',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-danger ms-1'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $('.loader').show();
            $.ajax({
                url: "<?php echo base_url('inventory/revert_purchase_order_in/'); ?>" + id,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    $('.loader').hide();
                    if (res.status == '200') {
                        Swal.fire({
                            title: 'Reverted!',
                            text: res.message,
                            icon: 'success',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            $('#report-datatable').DataTable().ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: res.message,
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                },
                error: function() {
                    $('.loader').hide();
                    Swal.fire('Error!', 'Something went wrong on the server.', 'error');
                }
            });
        }
    });
}

$(document).ready(function() {
  setInterval(function() {
    if (document.querySelector('.cke_notification_close')) {
      document.querySelector('.cke_notification_close').click();
    }
  }, 500);
});
</script>
