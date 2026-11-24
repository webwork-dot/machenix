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
  .cb-table-title-row {
    gap: 10px;
    margin-bottom: 4px;
  }
  .cb-table-title-row .stock-filter-dropdown {
    position: relative;
    z-index: 5;
  }
  .cb-table-title-row .stock-filter-menu {
    min-width: 320px;
    max-width: 360px;
  }
  #report-datatable tr.cb-total-row td {
    background: #eef3ff !important;
    font-weight: 700;
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
    $show_actions = true;
    $show_export = is_access('824');
  } elseif ($tab == 'pending_transfer') {
    $show_actions = is_access('833');
    $show_export = is_access('834');
  } elseif ($tab == 'transferred') {
    $show_actions = is_access('843') || is_access('844') || is_access('845');
    $show_export = is_access('846');
  }

  $pending_transfer_count = 0;
  if (is_access('812') && !empty($company_id)) {
    $pending_transfer_count = (int) $this->db->query(
      "SELECT COUNT(id) AS cnt FROM transferred_cash
       WHERE company_id = ?
         AND is_deleted = 0
         AND LOWER(method_from) = 'cheque'
         AND IFNULL(is_reverse, 0) = 1
         AND (is_approved = 0 OR is_approved IS NULL)",
      [(int) $company_id]
    )->row()->cnt;
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
    <a href="<?php echo base_url('inventory/bank-book?tab=pending_transfer' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'pending_transfer') ? 'active' : ''; ?>">Pending Transfer <?php if ($pending_transfer_count > 0) { ?><span class="badge bg-danger rounded-pill"><?= $pending_transfer_count; ?></span><?php } ?></a>
    <?php } ?>
    <?php if (is_access('813')) { ?>
    <a href="<?php echo base_url('inventory/bank-book?tab=transferred' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'transferred') ? 'active' : ''; ?>">Transferred</a>
    <?php } ?>
  </div>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="d-flex align-items-center flex-wrap gap-1 cb-table-title-row">
          <h5 class="mb-0"><b><?= $tab_title; ?><span id="total_count"> (0)</span></b></h5>

          <?php
            $has_bank_col_filters = ($tab == 'bank') && (
              is_access('814') || is_access('815') || is_access('820') || is_access('1023')
              || is_access('816') || is_access('817') || is_access('818')
              || is_access('819') || is_access('1024') || is_access('821') || is_access('822')
            );
          ?>
          <?php if ($has_bank_col_filters) { ?>
          <div class="dropdown stock-filter-dropdown ms-50">
            <button class="btn-filter-dropdown dropdown-toggle" type="button" id="bbFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <i class="feather icon-filter"></i>
              <span>Filters</span>
              <span class="filter-count-badge" id="filter-active-count">0</span>
            </button>
            <div class="dropdown-menu stock-filter-menu" aria-labelledby="bbFilterDropdown">
              <div class="filter-menu-header">
                <span class="title">Table Filters</span>
              </div>
              <div class="filter-menu-body" id="column-filters-container">
                <div class="filter-menu-label">Columns</div>
                <?php if (is_access('814')) { ?>
                <label class="filter-chip active" for="toggle-date" title="Toggle Date column">
                  <input type="checkbox" id="toggle-date" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Date</span>
                </label>
                <?php } ?>
                <?php if (is_access('815')) { ?>
                <label class="filter-chip active" for="toggle-customer-name" title="Toggle Customer Name column">
                  <input type="checkbox" id="toggle-customer-name" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Customer Name</span>
                </label>
                <?php } ?>
                <?php if (is_access('820')) { ?>
                <label class="filter-chip active" for="toggle-debit" title="Toggle Debit Amt column">
                  <input type="checkbox" id="toggle-debit" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Debit Amt</span>
                </label>
                <?php } ?>
                <?php if (is_access('1023')) { ?>
                <label class="filter-chip active" for="toggle-credit" title="Toggle Credit Amt column">
                  <input type="checkbox" id="toggle-credit" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Credit Amt</span>
                </label>
                <?php } ?>
                <?php if (is_access('816')) { ?>
                <label class="filter-chip active" for="toggle-source" title="Toggle Source column">
                  <input type="checkbox" id="toggle-source" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Source</span>
                </label>
                <?php } ?>
                <?php if (is_access('817')) { ?>
                <label class="filter-chip active" for="toggle-direction" title="Toggle In/Out column">
                  <input type="checkbox" id="toggle-direction" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">In/Out</span>
                </label>
                <?php } ?>
                <?php if (is_access('818')) { ?>
                <label class="filter-chip active" for="toggle-type" title="Toggle Type column">
                  <input type="checkbox" id="toggle-type" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Type</span>
                </label>
                <?php } ?>
                <?php if (is_access('819')) { ?>
                <label class="filter-chip active" for="toggle-bank-name" title="Toggle Bank Name column">
                  <input type="checkbox" id="toggle-bank-name" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Bank Name</span>
                </label>
                <?php } ?>
                <?php if (is_access('1024')) { ?>
                <label class="filter-chip active" for="toggle-account-no" title="Toggle AC No column">
                  <input type="checkbox" id="toggle-account-no" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">AC No</span>
                </label>
                <?php } ?>
                <?php if (is_access('821')) { ?>
                <label class="filter-chip active" for="toggle-narration" title="Toggle Narration column">
                  <input type="checkbox" id="toggle-narration" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Narration</span>
                </label>
                <?php } ?>
                <?php if (is_access('822')) { ?>
                <label class="filter-chip active" for="toggle-added-by" title="Toggle Added By column">
                  <input type="checkbox" id="toggle-added-by" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Added By</span>
                </label>
                <?php } ?>
              </div>
              <div class="filter-menu-footer">
                <button type="button" class="btn-reset-filters" id="btn-reset-col-filters" title="Reset column filters to default">
                  <i class="feather icon-rotate-ccw"></i> Reset Filters
                </button>
              </div>
            </div>
          </div>
          <?php } ?>
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
                <?php if (is_access('815')) { ?><th>Customer Name</th><?php } ?>
                <?php if (is_access('820')) { ?><th>Debit Amt</th><?php } ?>
                <?php if (is_access('1023')) { ?><th>Credit Amt</th><?php } ?>
                <?php if (is_access('816')) { ?><th>Source</th><?php } ?>
                <?php if (is_access('817')) { ?><th>In/Out</th><?php } ?>
                <?php if (is_access('818')) { ?><th>Type</th><?php } ?>
                <?php if (is_access('819')) { ?><th>Bank Name</th><?php } ?>
                <?php if (is_access('1024')) { ?><th>AC No</th><?php } ?>
                <?php if (is_access('821')) { ?><th>Narration</th><?php } ?>
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

  function fmtAmt(val) {
    var n = parseFloat(val) || 0;
    return '₹ ' + Math.abs(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  <?php if ($tab == 'bank') {
    $ci = 0;
    $col_date = $col_customer = $col_debit = $col_credit = 0;
    $col_source = $col_direction = $col_type = $col_bank_name = $col_account_no = $col_narration = $col_added = 0;
    if (is_access('814')) { $ci++; $col_date = $ci; }
    if (is_access('815')) { $ci++; $col_customer = $ci; }
    if (is_access('820')) { $ci++; $col_debit = $ci; }
    if (is_access('1023')) { $ci++; $col_credit = $ci; }
    if (is_access('816')) { $ci++; $col_source = $ci; }
    if (is_access('817')) { $ci++; $col_direction = $ci; }
    if (is_access('818')) { $ci++; $col_type = $ci; }
    if (is_access('819')) { $ci++; $col_bank_name = $ci; }
    if (is_access('1024')) { $ci++; $col_account_no = $ci; }
    if (is_access('821')) { $ci++; $col_narration = $ci; }
    if (is_access('822')) { $ci++; $col_added = $ci; }
  } ?>

  var dataTable = $('#report-datatable').DataTable({
    "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    "ordering": false,
    "pagingType": "simple_numbers",
    "processing": true,
    'scrollX': true,
    "serverSide": true,
    "createdRow": function(row, data) {
      if (data && data.is_total) {
        $(row).addClass('cb-total-row');
      }
    },
    "ajax": {
      "url": "<?php echo base_url('inventory/get_bank_book_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
        data.tab = '<?php echo $tab; ?>';
      },
      "dataSrc": function(json) {
        var rows = json.data || [];
        <?php if ($tab == 'bank') { ?>
        var debitTotal = 0;
        var creditTotal = 0;
        for (var i = 0; i < rows.length; i++) {
          debitTotal += parseFloat(rows[i].debit_raw) || 0;
          creditTotal += parseFloat(rows[i].credit_raw) || 0;
        }
        if (rows.length > 0) {
          rows.push({
            is_total: 1,
            sr_no: '',
            date: '<span class="fw-bold">TOTAL</span>',
            supplier_name: '',
            debit_amt: '<span class="fw-bold">' + fmtAmt(debitTotal) + '</span>',
            credit_amt: '<span class="fw-bold">' + fmtAmt(creditTotal) + '</span>',
            debit_raw: 0,
            credit_raw: 0,
            source: '',
            direction: '',
            payment_type: '',
            bank_name: '',
            account_no: '',
            remark: '',
            status: '',
            added_by_name: '',
            actions: ''
          });
        }
        <?php } ?>
        return rows;
      },
      "beforeSend": function() { $('.loader').show(); },
      "complete": function() { $('.loader').hide(); }
    },
    "columns": [
      { "data": "sr_no", "className": "text-center" },
      <?php if ($tab == 'bank') { ?>
        <?php if (is_access('814')) { ?>{ "data": "date", "className": "text-center" },<?php } ?>
        <?php if (is_access('815')) { ?>{ "data": "supplier_name" },<?php } ?>
        <?php if (is_access('820')) { ?>{ "data": "debit_amt", "className": "text-end" },<?php } ?>
        <?php if (is_access('1023')) { ?>{ "data": "credit_amt", "className": "text-end" },<?php } ?>
        <?php if (is_access('816')) { ?>{ "data": "source", "className": "text-center" },<?php } ?>
        <?php if (is_access('817')) { ?>{ "data": "direction", "className": "text-center" },<?php } ?>
        <?php if (is_access('818')) { ?>{ "data": "payment_type", "className": "text-center" },<?php } ?>
        <?php if (is_access('819')) { ?>{ "data": "bank_name" },<?php } ?>
        <?php if (is_access('1024')) { ?>{ "data": "account_no" },<?php } ?>
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

  <?php if ($tab == 'bank') { ?>
  function updateFilterCount() {
    var defaults = {
      <?php if (is_access('814')) { ?>'toggle-date': true,<?php } ?>
      <?php if (is_access('815')) { ?>'toggle-customer-name': true,<?php } ?>
      <?php if (is_access('820')) { ?>'toggle-debit': true,<?php } ?>
      <?php if (is_access('1023')) { ?>'toggle-credit': true,<?php } ?>
      <?php if (is_access('816')) { ?>'toggle-source': true,<?php } ?>
      <?php if (is_access('817')) { ?>'toggle-direction': true,<?php } ?>
      <?php if (is_access('818')) { ?>'toggle-type': true,<?php } ?>
      <?php if (is_access('819')) { ?>'toggle-bank-name': true,<?php } ?>
      <?php if (is_access('1024')) { ?>'toggle-account-no': true,<?php } ?>
      <?php if (is_access('821')) { ?>'toggle-narration': true,<?php } ?>
      <?php if (is_access('822')) { ?>'toggle-added-by': true<?php } ?>
    };
    var count = 0;
    $.each(defaults, function (id, defaultOn) {
      if ($('#' + id).length && $('#' + id).is(':checked') !== defaultOn) {
        count++;
      }
    });
    var $badge = $('#filter-active-count');
    $badge.text(count);
    $badge.toggleClass('has-count', count > 0);
  }

  function applyColumnFilters() {
    <?php if (is_access('814')) { ?>dataTable.column(<?php echo (int)$col_date; ?>).visible($('#toggle-date').is(':checked'), false);<?php } ?>
    <?php if (is_access('815')) { ?>dataTable.column(<?php echo (int)$col_customer; ?>).visible($('#toggle-customer-name').is(':checked'), false);<?php } ?>
    <?php if (is_access('820')) { ?>dataTable.column(<?php echo (int)$col_debit; ?>).visible($('#toggle-debit').is(':checked'), false);<?php } ?>
    <?php if (is_access('1023')) { ?>dataTable.column(<?php echo (int)$col_credit; ?>).visible($('#toggle-credit').is(':checked'), false);<?php } ?>
    <?php if (is_access('816')) { ?>dataTable.column(<?php echo (int)$col_source; ?>).visible($('#toggle-source').is(':checked'), false);<?php } ?>
    <?php if (is_access('817')) { ?>dataTable.column(<?php echo (int)$col_direction; ?>).visible($('#toggle-direction').is(':checked'), false);<?php } ?>
    <?php if (is_access('818')) { ?>dataTable.column(<?php echo (int)$col_type; ?>).visible($('#toggle-type').is(':checked'), false);<?php } ?>
    <?php if (is_access('819')) { ?>dataTable.column(<?php echo (int)$col_bank_name; ?>).visible($('#toggle-bank-name').is(':checked'), false);<?php } ?>
    <?php if (is_access('1024')) { ?>dataTable.column(<?php echo (int)$col_account_no; ?>).visible($('#toggle-account-no').is(':checked'), false);<?php } ?>
    <?php if (is_access('821')) { ?>dataTable.column(<?php echo (int)$col_narration; ?>).visible($('#toggle-narration').is(':checked'), false);<?php } ?>
    <?php if (is_access('822')) { ?>dataTable.column(<?php echo (int)$col_added; ?>).visible($('#toggle-added-by').is(':checked'), false);<?php } ?>
    dataTable.columns.adjust();
    updateFilterCount();
  }

  $('.column-filter-checkbox').on('change', function() {
    var isChecked = $(this).is(':checked');
    $(this).closest('.filter-chip').toggleClass('active', isChecked);
    applyColumnFilters();
  });

  $('#btn-reset-col-filters').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    <?php if (is_access('814')) { ?>$('#toggle-date').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('815')) { ?>$('#toggle-customer-name').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('820')) { ?>$('#toggle-debit').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('1023')) { ?>$('#toggle-credit').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('816')) { ?>$('#toggle-source').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('817')) { ?>$('#toggle-direction').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('818')) { ?>$('#toggle-type').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('819')) { ?>$('#toggle-bank-name').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('1024')) { ?>$('#toggle-account-no').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('821')) { ?>$('#toggle-narration').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('822')) { ?>$('#toggle-added-by').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    applyColumnFilters();
  });

  applyColumnFilters();
  <?php } ?>
});
</script>
