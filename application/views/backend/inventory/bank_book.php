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
  }
  .sub-link.active { background: #5a79c0 !important; color: white !important; }
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
  .cash-stat-card.card-total { border-top: 4px solid #28c76f; }
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
    margin-bottom: 0;
  }

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
  $tab_get = isset($_GET['tab']) ? $_GET['tab'] : 'bank';
  $tab = in_array($tab_get, ['bank', 'pending_transfer', 'transferred'], true) ? $tab_get : 'bank';
  if ($tab == 'bank' && !is_access('811')) {
    if (is_access('812')) { $tab = 'pending_transfer'; }
    elseif (is_access('813')) { $tab = 'transferred'; }
  } elseif ($tab == 'pending_transfer' && !is_access('812')) {
    if (is_access('811')) { $tab = 'bank'; }
    elseif (is_access('813')) { $tab = 'transferred'; }
  } elseif ($tab == 'transferred' && !is_access('813')) {
    if (is_access('811')) { $tab = 'bank'; }
    elseif (is_access('812')) { $tab = 'pending_transfer'; }
  }
  $date_range_param = (isset($_GET['date_range']) && $_GET['date_range'] != '') ? '&date_range=' . urlencode($_GET['date_range']) : '';
  $company_id = $this->session->userdata('company_id');
  $bank_summary = $this->inventory_model->get_bank_in_hand_summary($company_id);
  $official_balance   = (float)($bank_summary['official']['bank_in_hand'] ?? 0);
  $unofficial_balance = (float)($bank_summary['unofficial']['bank_in_hand'] ?? 0);
  $actual_on_bank     = $official_balance + $unofficial_balance;
  $is_transfer_tab = in_array($tab, ['transferred', 'pending_transfer'], true);
  $tab_title = ($tab == 'pending_transfer') ? 'Pending Transfer' : (($tab == 'transferred') ? 'Total Transferred' : 'Bank Ledger');
  $show_actions = false;
  $show_export = false;
  if ($tab == 'bank') {
    $show_actions = false;
    $show_export = is_access('824');
  } elseif ($tab == 'pending_transfer') {
    $show_actions = is_access('833');
    $show_export = is_access('834');
  } elseif ($tab == 'transferred') {
    $show_actions = is_access('843') || is_access('844') || is_access('845');
    $show_export = is_access('846');
  }
?>

<div class="row" id="table-bordered">
  <?php include('filter/date_range.php'); ?>

  <div class="col-12 mb-2">
    <div class="row g-2">
      <div class="col-12 col-md-4 mb-1 mb-md-0">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-credit-card text-primary me-50"></i> Official Bank in Hand</span>
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
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial Bank in Hand</span>
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
            <span class="stat-title"><i class="feather icon-dollar-sign text-success me-50"></i> Actual Bank on Hand</span>
            <span class="badge bg-light-success text-success font-weight-bold">Official + Unofficial</span>
          </div>
          <div class="stat-value <?= $actual_on_bank < 0 ? 'text-danger' : 'text-success'; ?>">
            <?= $actual_on_bank < 0 ? '- ₹ ' . number_format(abs($actual_on_bank), 2) : '₹ ' . number_format($actual_on_bank, 2); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 d-flex">
    <?php if (is_access('811')) { ?>
    <a href="<?php echo base_url('inventory/bank-book?tab=bank' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'bank') ? 'active' : ''; ?>">Bank</a>
    <?php } ?>
    <?php if (is_access('812')) { ?>
    <a href="<?php echo base_url('inventory/bank-book?tab=pending_transfer' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'pending_transfer') ? 'active' : ''; ?>">Pending Transfer</a>
    <?php } ?>
    <?php if (is_access('813')) { ?>
    <a href="<?php echo base_url('inventory/bank-book?tab=transferred' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'transferred') ? 'active' : ''; ?>">Transferred</a>
    <?php } ?>
  </div>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-12 mt-10">
            <h5 class="mb-0"><b><?= $tab_title; ?><span id="total_count"> (0)</span></b></h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <?php if ($tab == 'bank' && is_access('823')): ?>
        <a href="javascript:void(0);" onclick="showAjaxModal('<?php echo base_url('modal/popup_inventory/modal_transfer_bank'); ?>', 'Transfer Bank');" class="dt-button add-new desktop-tab add-btn btn btn-success" style="margin-right: 8px;"><span><i class="feather icon-arrow-right-circle"></i> Transfer</span></a>
        <?php endif; ?>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <?php if ($tab == 'bank') { ?>
                <?php if (is_access('814')) { ?><th>Date</th><?php } ?>
                <?php if (is_access('815')) { ?><th>Particular</th><?php } ?>
                <?php if (is_access('816')) { ?><th>Source</th><?php } ?>
                <?php if (is_access('817')) { ?><th>In/Out</th><?php } ?>
                <?php if (is_access('818')) { ?><th>Type</th><?php } ?>
                <?php if (is_access('819')) { ?><th>Bank Account</th><?php } ?>
                <?php if (is_access('820')) { ?><th>Amount</th><?php } ?>
                <?php if (is_access('821')) { ?><th>Remark / Narration</th><?php } ?>
                <?php if (is_access('822')) { ?><th>Added By</th><?php } ?>
              <?php } elseif ($tab == 'pending_transfer') { ?>
                <?php if (is_access('825')) { ?><th>Date</th><?php } ?>
                <?php if (is_access('826')) { ?><th>Company</th><?php } ?>
                <?php if (is_access('827')) { ?><th>Type</th><?php } ?>
                <?php if (is_access('828')) { ?><th>Bank Account</th><?php } ?>
                <?php if (is_access('829')) { ?><th>Amount</th><?php } ?>
                <?php if (is_access('830')) { ?><th>Remark / Narration</th><?php } ?>
                <?php if (is_access('831')) { ?><th>Status</th><?php } ?>
                <?php if (is_access('832')) { ?><th>Added By</th><?php } ?>
              <?php } else { ?>
                <?php if (is_access('835')) { ?><th>Date</th><?php } ?>
                <?php if (is_access('836')) { ?><th>Company</th><?php } ?>
                <?php if (is_access('837')) { ?><th>Type</th><?php } ?>
                <?php if (is_access('838')) { ?><th>Bank Account</th><?php } ?>
                <?php if (is_access('839')) { ?><th>Amount</th><?php } ?>
                <?php if (is_access('840')) { ?><th>Remark / Narration</th><?php } ?>
                <?php if (is_access('841')) { ?><th>Status</th><?php } ?>
                <?php if (is_access('842')) { ?><th>Added By</th><?php } ?>
              <?php } ?>
              <?php if ($show_actions) { ?><th style="width: 80px;" class="text-center">Action</th><?php } ?>
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

  $('#report-datatable').DataTable({
    "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "ordering": false,
    "pagingType": "simple_numbers",
    "processing": true,
    'scrollX': true,
    "serverSide": true,
    "ajax": {
      "url": "<?php echo base_url('inventory/get_bank_book_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
        data.tab = '<?php echo $tab; ?>';
      },
      "dataSrc": function(json) {
        return json.data;
      },
      "beforeSend": function() { $('.loader').show(); },
      "complete": function() { $('.loader').hide(); }
    },
    "columns": [
      { "data": "sr_no", "className": "text-center" },
      <?php if ($tab == 'bank') { ?>
        <?php if (is_access('814')) { ?>{ "data": "date" },<?php } ?>
        <?php if (is_access('815')) { ?>{ "data": "supplier_name" },<?php } ?>
        <?php if (is_access('816')) { ?>{ "data": "source" },<?php } ?>
        <?php if (is_access('817')) { ?>{ "data": "direction" },<?php } ?>
        <?php if (is_access('818')) { ?>{ "data": "payment_type" },<?php } ?>
        <?php if (is_access('819')) { ?>{ "data": "bank_account" },<?php } ?>
        <?php if (is_access('820')) { ?>{ "data": "amount" },<?php } ?>
        <?php if (is_access('821')) { ?>{ "data": "remark" },<?php } ?>
        <?php if (is_access('822')) { ?>{ "data": "added_by_name" },<?php } ?>
      <?php } elseif ($tab == 'pending_transfer') { ?>
        <?php if (is_access('825')) { ?>{ "data": "date" },<?php } ?>
        <?php if (is_access('826')) { ?>{ "data": "supplier_name" },<?php } ?>
        <?php if (is_access('827')) { ?>{ "data": "payment_type" },<?php } ?>
        <?php if (is_access('828')) { ?>{ "data": "bank_account" },<?php } ?>
        <?php if (is_access('829')) { ?>{ "data": "amount" },<?php } ?>
        <?php if (is_access('830')) { ?>{ "data": "remark" },<?php } ?>
        <?php if (is_access('831')) { ?>{ "data": "status" },<?php } ?>
        <?php if (is_access('832')) { ?>{ "data": "added_by_name" },<?php } ?>
      <?php } else { ?>
        <?php if (is_access('835')) { ?>{ "data": "date" },<?php } ?>
        <?php if (is_access('836')) { ?>{ "data": "supplier_name" },<?php } ?>
        <?php if (is_access('837')) { ?>{ "data": "payment_type" },<?php } ?>
        <?php if (is_access('838')) { ?>{ "data": "bank_account" },<?php } ?>
        <?php if (is_access('839')) { ?>{ "data": "amount" },<?php } ?>
        <?php if (is_access('840')) { ?>{ "data": "remark" },<?php } ?>
        <?php if (is_access('841')) { ?>{ "data": "status" },<?php } ?>
        <?php if (is_access('842')) { ?>{ "data": "added_by_name" },<?php } ?>
      <?php } ?>
      <?php if ($show_actions) { ?>{ "data": "actions", "className": "text-center" },<?php } ?>
    ],
    "buttons": [
      <?php if ($show_export) { ?>
      {
        "extend": 'excel',
        "text": '<button class="btn btn-success"><i class="fa fa-file-excel-o"></i> Excel</button>',
      },
      {
        "extend": 'pdfHtml5',
        "orientation": 'landscape',
        "text": '<button class="btn btn-danger"><i class="fa fa-file-pdf-o"></i> PDF</button>',
      }
      <?php } ?>
    ],
    "infoCallback": function(settings, start, end, max, total, pre) {
      $('#total_count').html('(' + total + ')');
      return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
    }
  });
});
</script>
