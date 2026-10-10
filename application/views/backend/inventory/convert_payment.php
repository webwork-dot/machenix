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
    height: 100%;
  }
  .cash-stat-card.card-official { border-top: 4px solid #7367f0; }
  .cash-stat-card.card-unofficial { border-top: 4px solid #82868b; }
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
    line-height: 1.2;
  }
</style>

<?php
  $company_id = $this->session->userdata('company_id');
  $cash_summary = $this->inventory_model->get_cash_in_hand_summary($company_id);
  $official_cash   = (float)($cash_summary['official']['cash_in_hand'] ?? 0);
  $unofficial_cash = (float)($cash_summary['unofficial']['cash_in_hand'] ?? 0);
?>

<div class="row" id="table-bordered">
  <?php include('filter/date_range.php'); ?>

  <div class="col-12 mb-2">
    <div class="row g-2">
      <div class="col-12 col-md-6 mb-1 mb-md-0">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-shield text-primary me-50"></i> Official Cash in Hand</span>
            <span class="badge bg-light-primary text-primary font-weight-bold">Official</span>
          </div>
          <div class="stat-value <?= $official_cash < 0 ? 'text-danger' : 'text-primary'; ?>">
            <?= $official_cash < 0 ? '- ₹ ' . number_format(abs($official_cash), 2) : '₹ ' . number_format($official_cash, 2); ?>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="cash-stat-card card-unofficial">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial Cash in Hand</span>
            <span class="badge bg-light-secondary text-secondary font-weight-bold">Unofficial</span>
          </div>
          <div class="stat-value <?= $unofficial_cash < 0 ? 'text-danger' : 'text-secondary'; ?>">
            <?= $unofficial_cash < 0 ? '- ₹ ' . number_format(abs($unofficial_cash), 2) : '₹ ' . number_format($unofficial_cash, 2); ?>
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
            <h5 class="mb-0"><b>Total Conversions<span id="total_count"> (0)</span></b></h5>
          </div>
          <div class="col-md-6 col-12 mt-10 text-md-end">
            <h5 class="mb-0 d-inline-block"><b>Total Amount: <span id="total_amount" class="text-success">₹ 0.00</span></b></h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <a href="<?php echo site_url('inventory/convert-payment/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0">
          <span><i class="feather icon-plus"></i> Add Conversion</span>
        </a>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Amount</th>
              <th>From</th>
              <th>To</th>
              <th>Method From</th>
              <th>Method To</th>
              <th>Narration</th>
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
    "ajax": {
      "url": "<?php echo base_url('inventory/get_convert_payments_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
      },
      "dataSrc": function(json) {
        if (json.total_amount !== undefined) {
          $('#total_amount').html(json.total_amount);
        }
        return json.data;
      },
      "beforeSend": function() { $('.loader').show(); },
      "complete": function() { $('.loader').hide(); }
    },
    "columns": [
      { "data": "sr_no" },
      { "data": "date" },
      { "data": "amount" },
      { "data": "converted_from" },
      { "data": "converted_to" },
      { "data": "method_from" },
      { "data": "method_to" },
      { "data": "narration" },
      { "data": "added_by_name" },
      { "data": "actions", "className": "text-center" },
    ],
    "buttons": [{
        "extend": 'excel',
        "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i> Excel</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8] }
      },
      {
        "extend": 'pdfHtml5',
        "orientation": 'landscape',
        "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4, 5, 6, 7, 8] }
      }
    ],
    "infoCallback": function(settings, start, end, max, total, pre) {
      $(".loader").fadeOut("slow");
      $('#total_count').html('(' + total + ')');
      return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
    },
    'columnDefs': [{ "targets": 0, "className": "text-center" }]
  }).on('draw.dt', function() {
    $(".loader").fadeOut("slow");
  });
});
</script>
