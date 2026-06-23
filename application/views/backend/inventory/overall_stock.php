<link rel="stylesheet" type="text/css"
    href="<?= base_url(); ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>

<style>
    .fixedElement {
        background: white;
        border-radius: .428rem;
    }

    .fw-bold {
        font-weight: bold;
    }

    .text-primary {
        color: #7367f0 !important;
    }

    .text-warning {
        color: #ff9f43 !important;
    }

    .text-success {
        color: #28c76f !important;
    }

    #report-datatable td {
        vertical-align: middle;
    }
</style>

<div class="row" id="table-bordered">
 
  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Total Entries <span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">

        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Product Name</th>
              <th>Quantity</th>
              <th>White Qty</th>
              <th>Black Qty</th>
              <th>Actions</th>
            </tr>
          </thead>
        </table>

      </div>

    </div>
  </div>
</div>

<!-- PO List Modal -->
<div class="modal fade" id="poListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-5 mx-50 pb-5">
                <h1 class="text-center mb-1" id="poListModalTitle">PO List</h1>
                <p class="text-center" id="poListModalSubTitle">Details of Purchase Orders</p>
                <div id="poListContent" class="mt-2">
                    <!-- Dynamic Content -->
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function showProductPOList(productId, companyId, status, warehouseId = '') {
        $('#poListModal').modal('show');
        $('#poListContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

        let statusTitle = status.charAt(0).toUpperCase() + status.slice(1);
        $('#poListModalTitle').text(statusTitle + ' Purchase Orders');

        $.ajax({
            url: "<?php echo base_url('inventory/get-product-po-list'); ?>",
            type: "POST",
            data: {
                product_id: productId,
                company_id: companyId,
                status: status,
                warehouse_id: warehouseId
            },
            success: function (response) {
                $('#poListContent').html(response);
            },
            error: function () {
                $('#poListContent').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
            }
        });
    }

    function showProductBatches(productId, warehouseId, productName) {
        $('#scrollable-modal').modal('hide');
        setTimeout(function () {
            var url = "<?php echo base_url('modal/popup_inventory/modal_batch_details'); ?>/" + productId + "/" + warehouseId;
            showAjaxModal(url, productName + ' - Batches');
        }, 400);
    }

    $(document).ready(function ($) {
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
                "url": "<?php echo base_url('inventory/get_overall_stock'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data) {
                    // data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
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
                { "data": "product_name" },
                { "data": "quantity" },
                { "data": "white_qty" },
                { "data": "black_qty" },
                { "data": "action" }
            ],
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
                    "exportOptions": {
                        "columns": [0, 1, 2, 3, 4]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
                    "exportOptions": {
                        "columns": [0, 1, 2, 3, 4]
                    }
                }
            ],
            "infoCallback": function (settings, start, end, max, total, pre) {
                $('#total_count').html(total);
                return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
            }
        });
    });
</script>