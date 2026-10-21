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
        text-decoration: none;
    }

    .sub-link.active {
        background: #5a79c0 !important;
        color: white !important;
    }
</style>

<?php
    $status = (isset($_GET['status']) && $_GET['status'] != '') ? $_GET['status'] : 'pending';
    if ($status == 'pending' && !is_access('454')) {
        if (is_access('455')) { $status = 'po'; }
        elseif (is_access('456')) { $status = 'loading'; }
        elseif (is_access('457')) { $status = 'received'; }
    } elseif ($status == 'po' && !is_access('455')) {
        if (is_access('454')) { $status = 'pending'; }
        elseif (is_access('456')) { $status = 'loading'; }
        elseif (is_access('457')) { $status = 'received'; }
    } elseif ($status == 'loading' && !is_access('456')) {
        if (is_access('454')) { $status = 'pending'; }
        elseif (is_access('455')) { $status = 'po'; }
        elseif (is_access('457')) { $status = 'received'; }
    } elseif ($status == 'received' && !is_access('457')) {
        if (is_access('454')) { $status = 'pending'; }
        elseif (is_access('455')) { $status = 'po'; }
        elseif (is_access('456')) { $status = 'loading'; }
    }
    $datatype_access = '938';
    if ($status == 'po') { $datatype_access = '940'; }
    elseif ($status == 'loading') { $datatype_access = '942'; }
    elseif ($status == 'received') { $datatype_access = '944'; }

    // Column / export access ids per tab
    if ($status == 'po') {
        $c_order_date = '467'; $c_order_no = '468'; $c_customer = '469'; $c_supplier = '470';
        $c_product = '471'; $c_sku = '472'; $c_qty = '473'; $c_order_by = '474'; $c_export = '475';
    } elseif ($status == 'loading') {
        $c_order_date = '476'; $c_order_no = '477'; $c_customer = '478'; $c_supplier = '479';
        $c_product = '480'; $c_sku = '481'; $c_qty = '482'; $c_order_by = '483'; $c_export = '484';
    } elseif ($status == 'received') {
        $c_order_date = '485'; $c_order_no = '486'; $c_customer = '487'; $c_supplier = '488';
        $c_product = '489'; $c_sku = '490'; $c_qty = '491'; $c_order_by = '492'; $c_export = '493';
    } else {
        $c_order_date = '458'; $c_order_no = '459'; $c_customer = '460'; $c_supplier = '461';
        $c_product = '462'; $c_sku = '463'; $c_qty = '464'; $c_order_by = '465'; $c_export = '466';
    }
?>

<div class="row" id="table-bordered">
   <div class="col-12 d-flex">
       <?php if (is_access('454')) { ?>
       <a href="<?php echo base_url('inventory/replace-products?status=pending'); ?>" class="sub-link <?php echo ($status == 'pending') ? 'active' : ''; ?>">Pending</a>
       <?php } ?>
       <?php if (is_access('455')) { ?>
       <a href="<?php echo base_url('inventory/replace-products?status=po'); ?>" class="sub-link <?php echo ($status == 'po') ? 'active' : ''; ?>">In PO</a>
       <?php } ?>
       <?php if (is_access('456')) { ?>
       <a href="<?php echo base_url('inventory/replace-products?status=loading'); ?>" class="sub-link <?php echo ($status == 'loading') ? 'active' : ''; ?>">In Loading</a>
       <?php } ?>
       <?php if (is_access('457')) { ?>
       <a href="<?php echo base_url('inventory/replace-products?status=received'); ?>" class="sub-link <?php echo ($status == 'received') ? 'active' : ''; ?>">Received</a>
       <?php } ?>
   </div>

   <div class="col-12">
      <div class="card" style="border-top-left-radius: 0;">
         <div class="card-body">
             <div class="row align-items-center">
                <div class="col-md-12 mt-10">
                   <h5 class="mb-0"><b>Replace Products <span id="total_count"> (0)</span></b></h5>
                </div>
             </div>
         </div>
         <div class="card-datatable d-report mb-2">
            <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
                     <th>#</th>
                     <?php if (is_access($c_order_date)) { ?><th>Order Date</th><?php } ?>
                     <?php if (is_access($c_order_no)) { ?><th>Order No</th><?php } ?>
                     <?php if (is_access($c_customer)) { ?><th>Customer</th><?php } ?>
                     <?php if (is_access($c_supplier)) { ?><th>Supplier</th><?php } ?>
                     <?php if (is_access($c_product)) { ?><th>Product Name</th><?php } ?>
                     <?php if (is_access($c_sku)) { ?><th>SKU</th><?php } ?>
                     <?php if (is_access($c_qty)) { ?><th>Quantity</th><?php } ?>
                     <?php if (is_access($c_order_by)) { ?><th>Order By</th><?php } ?>
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
                "url": "<?php echo base_url('inventory/get_replace_products'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.status = '<?php echo $status; ?>';
                    data.datatype = <?php echo is_access($datatype_access) ? '1' : '0'; ?>;
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
                <?php if (is_access($c_order_date)) { ?>{ "data": "order_date" },<?php } ?>
                <?php if (is_access($c_order_no)) { ?>{ "data": "order_no" },<?php } ?>
                <?php if (is_access($c_customer)) { ?>{ "data": "customer_name" },<?php } ?>
                <?php if (is_access($c_supplier)) { ?>{ "data": "supplier_name" },<?php } ?>
                <?php if (is_access($c_product)) { ?>{ "data": "product_name" },<?php } ?>
                <?php if (is_access($c_sku)) { ?>{ "data": "item_code" },<?php } ?>
                <?php if (is_access($c_qty)) { ?>{ "data": "qty" },<?php } ?>
                <?php if (is_access($c_order_by)) { ?>{ "data": "salesperson" },<?php } ?>
            ],
            "buttons": [
                <?php if (is_access($c_export)) { ?>
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                }
                <?php } ?>
            ], 
            "infoCallback": function( settings, start, end, max, total, pre ) {
                $(".loader").fadeOut("slow"); 
                $('#total_count').html('('+total+')');
                return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
            }
        });
    });
</script>
