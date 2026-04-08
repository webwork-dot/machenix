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
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="card-title">Overall Stock Overview</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="mb-0">
                            <b>Total Entries: <span id="total_count">(0)</span></b>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-datatable table-responsive mb-2">
                <table class="table table-bordered table-hover" id="report-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Company</th>
                            <th>Warehouse</th>
                            <th>Product Name</th>
                            <th>Current QTY</th>
                            <th>PO Qty</th>
                            <th>Priority Qty</th>
                            <th>Loading Qty</th>
                            <th>With Exp Cost</th>
                            <th>Without Exp Cost</th>
                            <th>Official Cost in INR</th>
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
    function showProductPOList(productId, companyId, status, warehouseId) {
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

    $(document).ready(function ($) {
        var dataTable = $('#report-datatable').DataTable({
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "processing": true,
            "serverSide": true,
            "pageLength": 25,
            "lengthMenu": [10, 25, 50, 100],
            "language": {
                sLengthMenu: "_MENU_",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            "ajax": {
                "url": "<?php echo base_url('inventory/get-overall-stock'); ?>",
                "type": "POST",
                "beforeSend": function () {
                    $('.loader').show();
                },
                "complete": function () {
                    $('.loader').hide();
                }
            },
            "columns": [
                { "data": "sr_no" },
                { "data": "company" },
                { "data": "warehouse" },
                { "data": "product_name" },
                { "data": "quantity" },
                { "data": "po_qty" },
                { "data": "priority_qty" },
                { "data": "loading_qty" },
                { "data": "with_exp_cost" },
                { "data": "without_exp_cost" },
                { "data": "official_cost_inr" },
                { "data": "action" }
            ],
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
                    "exportOptions": {
                        "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
                    "exportOptions": {
                        "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
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