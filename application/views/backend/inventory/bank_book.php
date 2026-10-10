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

<style>
  .cash-stat-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 14px 18px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
  }
  .cash-stat-card:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
  }
  .cash-stat-card.card-official {
    border-top: 4px solid #7367f0;
  }
  .cash-stat-card.card-unofficial {
    border-top: 4px solid #82868b;
  }
  .cash-stat-card .stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
  }
  .cash-stat-card .stat-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6e6b7b;
    margin: 0;
  }
  .cash-stat-card .stat-value {
    font-size: 22px;
    font-weight: 800;
    color: #2b2b2b;
    line-height: 1.2;
    margin-bottom: 8px;
  }
  .cash-stat-card .stat-detail {
    font-size: 11.5px;
    color: #5e5873;
    padding-top: 8px;
    border-top: 1px dashed #ebe9f1;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
  }
  .cash-stat-card .stat-detail span b {
    color: #1e1e1e;
  }
</style>

<?php
  $company_id = $this->session->userdata('company_id');
  $bank_summary = $this->inventory_model->get_bank_in_hand_summary($company_id);
  $official_balance   = (float)($bank_summary['official']['bank_in_hand'] ?? 0);
  $unofficial_balance = (float)($bank_summary['unofficial']['bank_in_hand'] ?? 0);
?>

<div class="row" id="table-bordered">
  <?php include('filter/date_range.php'); ?>

  <div class="col-12 mb-2">
    <div class="row g-2">
      <div class="col-12 col-md-6 mb-1 mb-md-0">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-credit-card text-primary me-50"></i> Official Bank in Hand</span>
            <span class="badge bg-light-primary text-primary font-weight-bold">Official</span>
          </div>
          <div class="stat-value <?= $official_balance < 0 ? 'text-danger' : 'text-primary'; ?>">
            <?= $official_balance < 0 ? '- ₹ ' . number_format(abs($official_balance), 2) : '₹ ' . number_format($official_balance, 2); ?>
          </div>
          <div class="stat-detail">
            <span>Received: <b>₹ <?= number_format($bank_summary['official']['received'] ?? 0, 2); ?></b></span>
            <span>Out: <b>₹ <?= number_format($bank_summary['official']['out'] ?? 0, 2); ?></b></span>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="cash-stat-card card-unofficial">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial Bank in Hand</span>
            <span class="badge bg-light-secondary text-secondary font-weight-bold">Unofficial</span>
          </div>
          <div class="stat-value <?= $unofficial_balance < 0 ? 'text-danger' : 'text-secondary'; ?>">
            <?= $unofficial_balance < 0 ? '- ₹ ' . number_format(abs($unofficial_balance), 2) : '₹ ' . number_format($unofficial_balance, 2); ?>
          </div>
          <div class="stat-detail">
            <span>Received: <b>₹ <?= number_format($bank_summary['unofficial']['received'] ?? 0, 2); ?></b></span>
            <span>Out: <b>₹ <?= number_format($bank_summary['unofficial']['out'] ?? 0, 2); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-6 col-12 mt-10">
            <h5 class="mb-0"><b>Bank Ledger<span id="total_count"> (0)</span></b></h5>
          </div>
          <div class="col-md-6 col-12 mt-10 text-md-end">
            <small class="me-1">In: <b class="text-success" id="ledger_total_in">₹ 0.00</b></small>
            <small class="me-1">Out: <b class="text-danger" id="ledger_total_out">₹ 0.00</b></small>
            <small>Net: <b id="ledger_net_amount">₹ 0.00</b></small>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <th>Date</th>
              <th>Particular</th>
              <th>Source</th>
              <th>In/Out</th>
              <th>Type</th>
              <th>Bank Account</th>
              <th>Amount</th>
              <th>Remark / Narration</th>
              <th>Added By</th>
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
      "url": "<?php echo base_url('inventory/get_bank_book_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
      },
      "dataSrc": function(json) {
        if (json.total_in !== undefined) { $('#ledger_total_in').html(json.total_in); }
        if (json.total_out !== undefined) { $('#ledger_total_out').html(json.total_out); }
        if (json.net_amount !== undefined) { $('#ledger_net_amount').html(json.net_amount); }
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
      { "data": "supplier_name" },
      { "data": "source" },
      { "data": "direction" },
      { "data": "payment_type" },
      { "data": "bank_account" },
      { "data": "amount" },
      { "data": "remark" },
      { "data": "added_by_name" },
      { "data": "actions", "className": "text-center" },
    ],

    "buttons": [{
        "extend": 'excel',
        "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] }
      },
      {
        "extend": 'pdfHtml5',
        "orientation": 'landscape',
        "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] }
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
