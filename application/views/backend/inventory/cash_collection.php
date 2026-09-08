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

  .sub-link {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    margin-right: 4px;
    background: white;
    padding: 8px 18px;
    min-width: 100px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    color: #5e5873;
    display: inline-block;
    transition: all 0.2s ease;
  }

  .sub-link:hover {
    color: #5a79c0;
  }

  .sub-link.active {
    background: #5a79c0 !important;
    color: white !important;
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
</style>

<?php
  $status = (isset($_GET['status']) && $_GET['status'] == 'received') ? 'received' : 'pending';
  $date_range_param = (isset($_GET['date_range']) && $_GET['date_range'] != '') ? '&date_range=' . urlencode($_GET['date_range']) : '';
  $company_id = $this->session->userdata('company_id');

  $total_overall_amount = 0;
  if ($this->db->table_exists('transferred_cash')) {
    $coll_sql = "SELECT IFNULL(SUM(amount), 0) as total_amt FROM transferred_cash WHERE 1=1";
    if (!empty($company_id)) {
      $coll_sql .= " AND company_id = '$company_id'";
    }
    if ($this->db->field_exists('is_deleted', 'transferred_cash')) {
      $coll_sql .= " AND is_deleted = 0";
    }
    if ($status == 'received') {
      $coll_sql .= " AND is_approved = 1";
    } else {
      $coll_sql .= " AND (is_approved = 0 OR is_approved IS NULL)";
    }
    $res = $this->db->query($coll_sql)->row_array();
    $total_overall_amount = (float)($res['total_amt'] ?? 0);
  }
?>

<div class="row" id="table-bordered">
  <?php include('filter/date_range.php'); ?>

  <div class="col-12 d-flex">
    <a href="<?php echo base_url('inventory/cash-collection?status=pending' . $date_range_param); ?>" class="sub-link <?php echo ($status == 'pending') ? 'active' : ''; ?>">Pending</a>
    <a href="<?php echo base_url('inventory/cash-collection?status=received' . $date_range_param); ?>" class="sub-link <?php echo ($status == 'received') ? 'active' : ''; ?>">Received</a>
  </div>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-6 col-12 mt-10">
            <h5 class="mb-0"><b>Total <?= ($status == 'received') ? 'Received' : 'Pending'; ?> Cash Collections<span id="total_count"> (0)</span></b>
            </h5>
          </div>
          <div class="col-md-6 col-12 mt-10 text-md-end">
            <h5 class="mb-0"><b>Total <?= ($status == 'received') ? 'Received' : 'Pending'; ?> Amount: <span id="total_cash_collection_amount" class="text-primary">₹ <?= number_format($total_overall_amount, 2); ?></span></b></h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Remark / Narration</th>
              <th>Transferred By</th>
              <?php if ($status == 'received'): ?>
              <th>Approval Date</th>
              <?php endif; ?>
              <th>Status</th>
              <th style="width: 80px;" class="text-center">Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function($) {
  if ($('#form_filter').length && !$('#form_filter input[name="status"]').length) {
    $('#form_filter').append('<input type="hidden" name="status" value="<?php echo $status; ?>">');
  }

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
      "url": "<?php echo base_url('inventory/get_cash_collection_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
        data.status = '<?php echo $status; ?>';
      },
      "dataSrc": function(json) {
        if (json.total_amount !== undefined) {
          $('#total_cash_collection_amount').html(json.total_amount);
        }
        return json.data;
      },
      "beforeSend": function() {
        $('.loader').show();
      },
      "complete": function() {
        $('.loader').hide();
      }
    },

    "columns": [
      { "data": "sr_no", "className": "text-center" },
      { "data": "date" },
      { "data": "amount" },
      { "data": "remark" },
      { "data": "transferred_by" },
      <?php if ($status == 'received'): ?>
      { "data": "approval_date" },
      <?php endif; ?>
      { "data": "status" },
      { "data": "actions", "className": "text-center" },
    ],

    "buttons": [{
        "extend": 'excel',
        "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4, <?php echo ($status == 'received') ? '5, 6' : '5'; ?>] }
      },
      {
        "extend": 'pdfHtml5',
        "orientation": 'landscape',
        "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4, <?php echo ($status == 'received') ? '5, 6' : '5'; ?>] }
      }
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
