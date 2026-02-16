<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url(); ?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<style>
  .table-error td {
    background: #febdb9;
    color: #3c3a3a;
    font-weight: 600 !important;
  }
</style>
<style>
  .fixedElement {
    background: white;
    border-radius: .428rem;
  }

  .nav-pills.nav-justified .nav-item {
    display: flex;
    align-items: center;
  }

  .new-fix .nav-pills .nav-link.active,
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


<?php include('filter/date_range.php'); ?>
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mt-10">
            <h5 class="mb-0"><b>Total Sales Reports<span id="total_count"> (0)</span></b></h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Company</th>
              <th>Order ID</th>
              <th>SKU Code + Size</th>
              <th>Customer</th>
              <th>State</th>
              <th>Pincode</th>
              <th>SP</th>
              <th>Quantity</th>
              <th>Total Amount</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
 
<script type="text/javascript">
  $(document).ready(function($) {
      
    var dateRange = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range'] : '' ?>';
    var showPagination = (dateRange === ''); // false if date_range is set
    
    var dataTable = $('#report-datatable').DataTable({
      "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      "ordering": false,
      "sDom": 'rt<"dtPagination"lp><"clear">',
      "pagingType": "simple_numbers",
      "processing": true,
      'scrollX': true,
      "serverSide": true,
      "pageLength": showPagination ? 100 : -1,  // 👈 show all if date_range is set
      "lengthChange": showPagination,           // 👈 disable "Show X entries" dropdown
      "paging": showPagination,  
      "language": {
        sLengthMenu: "_MENU_",
        'processing': $('.loader').show()
      },
      "drawCallback": function(settings, json) {
        $('[data-toggle="tooltip"]').tooltip('update');
      },
      "ajax": {
        "url": "<?php echo base_url('inventory/get_sales_reports'); ?>",
        "dataType": "json",
        "type": "POST",
        "data": function(data) {
          data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range'] : '' ?>';
          data.company_id = '<?php echo (isset($_GET['company_id'])) ? $_GET['company_id'] : '' ?>';
          data.order_id = '<?php echo (isset($_GET['order_id'])) ? $_GET['order_id'] : '' ?>';
        },
        "beforeSend": function() {
          $('.loader').show();
        },
        "complete": function() {
          $('.loader').hide();
        }
      },
      "columns": [{
          "data": "sr_no",
          "className": "text-center"
        },
        {
          "data": "date"
        },
        {
          "data": "company_name"
        },
        {
          "data": "product_order_id"
        },
        {
          "data": "sku_size"
        },
        {
          "data": "customer_name"
        },
        {
          "data": "state"
        },
        {
          "data": "pincode"
        },
        {
          "data": "sp"
        },
        {
          "data": "qty"
        },
        {
          "data": "total_amount"
        }
      ],
      "buttons": [{
          "extend": 'excel',
          "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
        },
        {
          "extend": 'pdfHtml5',
          "orientation": 'landscape',
          "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
        }
      ],
      "infoCallback": function(settings, start, end, max, total, pre) {
        $(".loader").fadeOut("slow");
        $('#total_count').html('(' + total + ')');
        return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
      },
      createdRow: function(row, data, index) {
        if (data['error'] == '1') {
          $(row).addClass('table-error');
        }
      }
    }).on('draw.dt', function() {
      $(".loader").fadeOut("slow");
    });
  });
</script>


<script>
  //Date range picker
  $('.datepicker_report').daterangepicker({
    autoUpdateInput: false,
    autoApply: false,
    //autoclose: true, 
    locale: {
      format: 'DD-MM-YYYY',
      cancelLabel: 'Clear'
    },
    maxDate: moment(),
    //maxDate: moment().add(0, 'days'), // 30 days from the current day
  })
  //Date range picker with time picker

  $('.datepicker_report').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
  });
</script>