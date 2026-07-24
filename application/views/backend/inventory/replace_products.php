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
?>

<div class="row" id="table-bordered">
   <div class="col-12 d-flex">
       <a href="<?php echo base_url('inventory/replace-products?status=pending'); ?>" class="sub-link <?php echo ($status == 'pending') ? 'active' : ''; ?>">Pending</a>
       <a href="<?php echo base_url('inventory/replace-products?status=po'); ?>" class="sub-link <?php echo ($status == 'po') ? 'active' : ''; ?>">In PO</a>
       <a href="<?php echo base_url('inventory/replace-products?status=loading'); ?>" class="sub-link <?php echo ($status == 'loading') ? 'active' : ''; ?>">In Loading</a>
       <a href="<?php echo base_url('inventory/replace-products?status=received'); ?>" class="sub-link <?php echo ($status == 'received') ? 'active' : ''; ?>">Received</a>
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
                     <th>Order Date</th>
                     <th>Order No</th>
                     <th>Customer</th>
                     <th>Supplier</th>
                     <th>Product Name</th>
                     <th>SKU</th>
                     <th>Quantity</th>
                     <th>Order By</th>
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
                { "data": "order_date" },
                { "data": "order_no" },
                { "data": "customer_name" },
                { "data": "supplier_name" },
                { "data": "product_name" },
                { "data": "item_code" },
                { "data": "qty" },
                { "data": "salesperson" }
             ],
             "buttons": [
                 {
                     "extend": 'excel',
                     "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
                     "exportOptions": {
                        "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8]
                     }
                 },
                 {
                     "extend": 'pdfHtml5',
                     "orientation": 'landscape',
                     "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                     "exportOptions": {
                        "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8]
                     }
                 }
             ], 
             "infoCallback": function( settings, start, end, max, total, pre ) {
                 $(".loader").fadeOut("slow"); 
                 $('#total_count').html('('+total+')');
                 return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
             }
        });
    });
</script>
