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

  .small-img {
    max-height: 50px;
    min-height: 50px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e7e6e6;
    height: 50px;
    max-width: 60px;
  }

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
  .cash-stat-card.card-total {
    border-top: 4px solid #28c76f;
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
    margin-bottom: 0;
  }

  /* Ledger label badges (theme only ships bg-light-primary) */
  #report-datatable .badge {
    font-weight: 600;
    padding: 0.35em 0.65em;
    border-radius: 0.358rem;
  }
  #report-datatable .badge.bg-light-primary {
    background-color: rgba(115, 103, 240, 0.12) !important;
    color: #7367f0 !important;
  }
  #report-datatable .badge.bg-light-secondary {
    background-color: rgba(130, 134, 139, 0.12) !important;
    color: #82868b !important;
  }
  #report-datatable .badge.bg-light-success {
    background-color: rgba(40, 199, 111, 0.12) !important;
    color: #28c76f !important;
  }
  #report-datatable .badge.bg-light-danger {
    background-color: rgba(234, 84, 85, 0.12) !important;
    color: #ea5455 !important;
  }
  #report-datatable .badge.bg-light-info {
    background-color: rgba(0, 207, 232, 0.12) !important;
    color: #00cfe8 !important;
  }
  #report-datatable .badge.bg-light-warning {
    background-color: rgba(255, 159, 67, 0.12) !important;
    color: #ff9f43 !important;
  }
</style>

<?php
  $tab_get = isset($_GET['tab']) ? $_GET['tab'] : 'cash';
  $tab = in_array($tab_get, ['cash', 'pending_transfer', 'transferred'], true) ? $tab_get : 'cash';
  $date_range_param = (isset($_GET['date_range']) && $_GET['date_range'] != '') ? '&date_range=' . urlencode($_GET['date_range']) : '';
  $company_id = $this->session->userdata('company_id');
  $cash_summary = $this->inventory_model->get_cash_in_hand_summary($company_id);
  $official_balance   = (float)($cash_summary['official']['cash_in_hand'] ?? 0);
  $unofficial_balance = (float)($cash_summary['unofficial']['cash_in_hand'] ?? 0);
  $actual_on_hand     = $official_balance + $unofficial_balance;
?>

<div class="row" id="table-bordered">
  <?php include('filter/date_range.php'); ?>

  <div class="col-12 mb-2">
    <div class="row g-2">
      <div class="col-12 col-md-4 mb-1 mb-md-0">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-shield text-primary me-50"></i> Official Cash in Hand</span>
            <span class="badge bg-light-primary text-primary font-weight-bold">Official</span>
          </div>
          <div class="stat-value <?= $official_balance < 0 ? 'text-danger' : 'text-primary'; ?>">
            <?= $official_balance < 0 ? '- ₹ ' . number_format(abs($official_balance), 2) : '₹ ' . number_format($official_balance, 2); ?>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4 mb-1 mb-md-0">
        <div class="cash-stat-card card-unofficial">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial Cash in Hand</span>
            <span class="badge bg-light-secondary text-secondary font-weight-bold">Unofficial</span>
          </div>
          <div class="stat-value <?= $unofficial_balance < 0 ? 'text-danger' : 'text-secondary'; ?>">
            <?= $unofficial_balance < 0 ? '- ₹ ' . number_format(abs($unofficial_balance), 2) : '₹ ' . number_format($unofficial_balance, 2); ?>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-total">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-dollar-sign text-success me-50"></i> Actual Cash on Hand</span>
            <span class="badge bg-light-success text-success font-weight-bold">Official + Unofficial</span>
          </div>
          <div class="stat-value <?= $actual_on_hand < 0 ? 'text-danger' : 'text-success'; ?>">
            <?= $actual_on_hand < 0 ? '- ₹ ' . number_format(abs($actual_on_hand), 2) : '₹ ' . number_format($actual_on_hand, 2); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 d-flex">
    <a href="<?php echo base_url('inventory/cash-book?tab=cash' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'cash') ? 'active' : ''; ?>">Cash</a>
    <a href="<?php echo base_url('inventory/cash-book?tab=pending_transfer' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'pending_transfer') ? 'active' : ''; ?>">Pending Transfer</a>
    <a href="<?php echo base_url('inventory/cash-book?tab=transferred' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'transferred') ? 'active' : ''; ?>">Transferred</a>
  </div>

  <?php
    $is_transfer_tab = in_array($tab, ['transferred', 'pending_transfer'], true);
    $tab_title = ($tab == 'pending_transfer') ? 'Pending Transfer' : (($tab == 'transferred') ? 'Total Transferred' : 'Cash Ledger');
  ?>
  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-12 mt-10">
            <h5 class="mb-0"><b><?= $tab_title; ?><span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <?php if ($tab == 'cash'): ?>
        <a href="<?php echo site_url('inventory/cash-book/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0"><span><i class="feather icon-plus"></i> Add Expense</span></a>     
        <a href="javascript:void(0);" onclick="showAjaxModal('<?php echo base_url('modal/popup_inventory/modal_transfer_cash'); ?>', 'Transfer Cash');" class="dt-button add-new desktop-tab add-btn btn btn-success" style="margin-right: 8px;" tabindex="0"><span><i class="feather icon-arrow-right-circle"></i> Transfer</span></a>
        <?php endif; ?>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <th>Date</th>
              <th><?= $is_transfer_tab ? 'Company' : 'Particular'; ?></th>
              <?php if (!$is_transfer_tab): ?>
              <th>Source</th>
              <th>In/Out</th>
              <?php endif; ?>
              <th>Type</th>
              <th>Amount</th>
              <th>Remark / Narration</th>
              <?php if ($is_transfer_tab): ?>
              <th>Status</th>
              <?php endif; ?>
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
  if ($('#form_filter').length && !$('#form_filter input[name="tab"]').length) {
    $('#form_filter').append('<input type="hidden" name="tab" value="<?php echo $tab; ?>">');
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
      "url": "<?php echo base_url('inventory/get_petty_cash_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
        data.tab = '<?php echo $tab; ?>';
      },
      "dataSrc": function(json) {
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
      <?php if (!$is_transfer_tab): ?>
      { "data": "source" },
      { "data": "direction" },
      <?php endif; ?>
      { "data": "payment_type" },
      { "data": "amount" },
      { "data": "remark" },
      <?php if ($is_transfer_tab): ?>
      { "data": "status" },
      <?php endif; ?>
      { "data": "added_by_name" },
      { "data": "actions", "className": "text-center" },
    ],

    "buttons": [{
        "extend": 'excel',
        "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
        "exportOptions": { "columns": <?php echo $is_transfer_tab ? '[0, 1, 2, 3, 4, 5, 6, 7]' : '[0, 1, 2, 3, 4, 5, 6, 7, 8]'; ?> }
      },
      {
        "extend": 'pdfHtml5',
        "orientation": 'landscape',
        "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
        "exportOptions": { "columns": <?php echo $is_transfer_tab ? '[0, 1, 2, 3, 4, 5, 6, 7]' : '[0, 1, 2, 3, 4, 5, 6, 7, 8]'; ?> }
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
