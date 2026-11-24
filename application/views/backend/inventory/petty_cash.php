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

  .new-form .nav-pills .nav-link.active,
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
  $tab_get = isset($_GET['tab']) ? $_GET['tab'] : 'cash';
  $tab = in_array($tab_get, ['cash', 'pending_transfer', 'transferred'], true) ? $tab_get : 'cash';
  if ($tab == 'cash' && !is_access('774')) {
    if (is_access('775')) { $tab = 'pending_transfer'; }
    elseif (is_access('776')) { $tab = 'transferred'; }
  } elseif ($tab == 'pending_transfer' && !is_access('775')) {
    if (is_access('774')) { $tab = 'cash'; }
    elseif (is_access('776')) { $tab = 'transferred'; }
  } elseif ($tab == 'transferred' && !is_access('776')) {
    if (is_access('774')) { $tab = 'cash'; }
    elseif (is_access('775')) { $tab = 'pending_transfer'; }
  }
  $date_range_param = (isset($_GET['date_range']) && $_GET['date_range'] != '') ? '&date_range=' . urlencode($_GET['date_range']) : '';
  $company_id = $this->session->userdata('company_id');
  $cash_summary = $this->inventory_model->get_cash_in_hand_summary($company_id);
  $official_balance   = (float)($cash_summary['official']['cash_in_hand'] ?? 0);
  $unofficial_balance = (float)($cash_summary['unofficial']['cash_in_hand'] ?? 0);
  $actual_on_hand     = $official_balance + $unofficial_balance;
  $is_transfer_tab = in_array($tab, ['transferred', 'pending_transfer'], true);
  $tab_title = ($tab == 'pending_transfer') ? 'Pending Transfer' : (($tab == 'transferred') ? 'Total Transferred' : 'Cash Ledger');
  $show_actions = false;
  $show_export = false;
  if ($tab == 'cash') {
    $show_actions = is_access('787') || is_access('788');
    $show_export = is_access('789');
  } elseif ($tab == 'pending_transfer') {
    $show_actions = is_access('797');
    $show_export = is_access('798');
  } elseif ($tab == 'transferred') {
    $show_actions = is_access('806') || is_access('807') || is_access('808');
    $show_export = is_access('809');
  }

  $pending_transfer_count = 0;
  if (is_access('775') && !empty($company_id)) {
    $pending_transfer_count = (int) $this->db->query(
      "SELECT COUNT(id) AS cnt FROM transferred_cash
       WHERE company_id = ?
         AND is_deleted = 0
         AND LOWER(method_from) = 'cash'
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
    <?php if (is_access('774')) { ?>
    <a href="<?php echo base_url('inventory/cash-book?tab=cash' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'cash') ? 'active' : ''; ?>">Cash</a>
    <?php } ?>
    <?php if (is_access('775')) { ?>
    <a href="<?php echo base_url('inventory/cash-book?tab=pending_transfer' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'pending_transfer') ? 'active' : ''; ?>">Pending Transfer <?php if ($pending_transfer_count > 0) { ?><span class="badge bg-danger rounded-pill"><?= $pending_transfer_count; ?></span><?php } ?></a>
    <?php } ?>
    <?php if (is_access('776')) { ?>
    <a href="<?php echo base_url('inventory/cash-book?tab=transferred' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'transferred') ? 'active' : ''; ?>">Transferred</a>
    <?php } ?>
  </div>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="d-flex align-items-center flex-wrap gap-1 cb-table-title-row">
          <h5 class="mb-0"><b><?= $tab_title; ?><span id="total_count"> (0)</span></b></h5>

          <?php
            $has_cash_col_filters = ($tab == 'cash') && (
              is_access('777') || is_access('778') || is_access('782') || is_access('1022')
              || is_access('779') || is_access('780') || is_access('781')
              || is_access('783') || is_access('784')
            );
          ?>
          <?php if ($has_cash_col_filters) { ?>
          <div class="dropdown stock-filter-dropdown ms-50">
            <button class="btn-filter-dropdown dropdown-toggle" type="button" id="cbFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <i class="feather icon-filter"></i>
              <span>Filters</span>
              <span class="filter-count-badge" id="filter-active-count">0</span>
            </button>
            <div class="dropdown-menu stock-filter-menu" aria-labelledby="cbFilterDropdown">
              <div class="filter-menu-header">
                <span class="title">Table Filters</span>
              </div>
              <div class="filter-menu-body" id="column-filters-container">
                <div class="filter-menu-label">Columns</div>
                <?php if (is_access('777')) { ?>
                <label class="filter-chip active" for="toggle-date" title="Toggle Date column">
                  <input type="checkbox" id="toggle-date" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Date</span>
                </label>
                <?php } ?>
                <?php if (is_access('778')) { ?>
                <label class="filter-chip active" for="toggle-customer-name" title="Toggle Customer Name column">
                  <input type="checkbox" id="toggle-customer-name" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Customer Name</span>
                </label>
                <?php } ?>
                <?php if (is_access('782')) { ?>
                <label class="filter-chip active" for="toggle-debit" title="Toggle Debit Amt column">
                  <input type="checkbox" id="toggle-debit" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Debit Amt</span>
                </label>
                <?php } ?>
                <?php if (is_access('1022')) { ?>
                <label class="filter-chip active" for="toggle-credit" title="Toggle Credit Amt column">
                  <input type="checkbox" id="toggle-credit" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Credit Amt</span>
                </label>
                <?php } ?>
                <?php if (is_access('779')) { ?>
                <label class="filter-chip active" for="toggle-source" title="Toggle Source column">
                  <input type="checkbox" id="toggle-source" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Source</span>
                </label>
                <?php } ?>
                <?php if (is_access('780')) { ?>
                <label class="filter-chip active" for="toggle-direction" title="Toggle In/Out column">
                  <input type="checkbox" id="toggle-direction" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">In/Out</span>
                </label>
                <?php } ?>
                <?php if (is_access('781')) { ?>
                <label class="filter-chip active" for="toggle-type" title="Toggle Type column">
                  <input type="checkbox" id="toggle-type" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Type</span>
                </label>
                <?php } ?>
                <?php if (is_access('783')) { ?>
                <label class="filter-chip active" for="toggle-narration" title="Toggle Narration column">
                  <input type="checkbox" id="toggle-narration" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Narration</span>
                </label>
                <?php } ?>
                <?php if (is_access('784')) { ?>
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
        <?php if ($tab == 'cash'): ?>
          <?php if (is_access('785')) { ?>
          <a href="<?php echo site_url('inventory/cash-book/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0"><span><i class="feather icon-plus"></i> Add Expense</span></a>
          <?php } ?>
          <?php if (is_access('786')) { ?>
          <a href="javascript:void(0);" onclick="showAjaxModal('<?php echo base_url('modal/popup_inventory/modal_transfer_cash'); ?>', 'Transfer Cash');" class="dt-button add-new desktop-tab add-btn btn btn-success" style="margin-right: 8px;" tabindex="0"><span><i class="feather icon-arrow-right-circle"></i> Transfer</span></a>
          <?php } ?>
        <?php endif; ?>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <?php if ($tab == 'cash') { ?>
                <?php if (is_access('777')) { ?><th>Date</th><?php } ?>
                <?php if (is_access('778')) { ?><th>Customer Name</th><?php } ?>
                <?php if (is_access('782')) { ?><th>Debit Amt</th><?php } ?>
                <?php if (is_access('1022')) { ?><th>Credit Amt</th><?php } ?>
                <?php if (is_access('779')) { ?><th>Source</th><?php } ?>
                <?php if (is_access('780')) { ?><th>In/Out</th><?php } ?>
                <?php if (is_access('781')) { ?><th>Type</th><?php } ?>
                <?php if (is_access('783')) { ?><th>Narration</th><?php } ?>
                <?php if (is_access('784')) { ?><th>Added By</th><?php } ?>
              <?php } elseif ($tab == 'pending_transfer') { ?>
                <?php if (is_access('790')) { ?><th>Date</th><?php } ?>
                <?php if (is_access('791')) { ?><th>Company</th><?php } ?>
                <?php if (is_access('792')) { ?><th>Type</th><?php } ?>
                <?php if (is_access('793')) { ?><th>Amount</th><?php } ?>
                <?php if (is_access('794')) { ?><th>Remark / Narration</th><?php } ?>
                <?php if (is_access('795')) { ?><th>Status</th><?php } ?>
                <?php if (is_access('796')) { ?><th>Added By</th><?php } ?>
              <?php } else { ?>
                <?php if (is_access('799')) { ?><th>Date</th><?php } ?>
                <?php if (is_access('800')) { ?><th>Company</th><?php } ?>
                <?php if (is_access('801')) { ?><th>Type</th><?php } ?>
                <?php if (is_access('802')) { ?><th>Amount</th><?php } ?>
                <?php if (is_access('803')) { ?><th>Remark / Narration</th><?php } ?>
                <?php if (is_access('804')) { ?><th>Status</th><?php } ?>
                <?php if (is_access('805')) { ?><th>Added By</th><?php } ?>
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

  <?php if ($tab == 'cash') {
    $ci = 0;
    $col_date = $col_customer = $col_debit = $col_credit = 0;
    $col_source = $col_direction = $col_type = $col_narration = $col_added = 0;
    if (is_access('777')) { $ci++; $col_date = $ci; }
    if (is_access('778')) { $ci++; $col_customer = $ci; }
    if (is_access('782')) { $ci++; $col_debit = $ci; }
    if (is_access('1022')) { $ci++; $col_credit = $ci; }
    if (is_access('779')) { $ci++; $col_source = $ci; }
    if (is_access('780')) { $ci++; $col_direction = $ci; }
    if (is_access('781')) { $ci++; $col_type = $ci; }
    if (is_access('783')) { $ci++; $col_narration = $ci; }
    if (is_access('784')) { $ci++; $col_added = $ci; }
  } ?>

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
    "createdRow": function(row, data) {
      if (data && data.is_total) {
        $(row).addClass('cb-total-row');
      }
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
        var rows = json.data || [];
        <?php if ($tab == 'cash') { ?>
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
            remark: '',
            status: '',
            added_by_name: '',
            actions: ''
          });
        }
        <?php } ?>
        return rows;
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
      <?php if ($tab == 'cash') { ?>
        <?php if (is_access('777')) { ?>{ "data": "date", "className": "text-center" },<?php } ?>
        <?php if (is_access('778')) { ?>{ "data": "supplier_name" },<?php } ?>
        <?php if (is_access('782')) { ?>{ "data": "debit_amt", "className": "text-end" },<?php } ?>
        <?php if (is_access('1022')) { ?>{ "data": "credit_amt", "className": "text-end" },<?php } ?>
        <?php if (is_access('779')) { ?>{ "data": "source", "className": "text-center" },<?php } ?>
        <?php if (is_access('780')) { ?>{ "data": "direction", "className": "text-center" },<?php } ?>
        <?php if (is_access('781')) { ?>{ "data": "payment_type", "className": "text-center" },<?php } ?>
        <?php if (is_access('783')) { ?>{ "data": "remark" },<?php } ?>
        <?php if (is_access('784')) { ?>{ "data": "added_by_name" },<?php } ?>
      <?php } elseif ($tab == 'pending_transfer') { ?>
        <?php if (is_access('790')) { ?>{ "data": "date" },<?php } ?>
        <?php if (is_access('791')) { ?>{ "data": "supplier_name" },<?php } ?>
        <?php if (is_access('792')) { ?>{ "data": "payment_type" },<?php } ?>
        <?php if (is_access('793')) { ?>{ "data": "amount" },<?php } ?>
        <?php if (is_access('794')) { ?>{ "data": "remark" },<?php } ?>
        <?php if (is_access('795')) { ?>{ "data": "status" },<?php } ?>
        <?php if (is_access('796')) { ?>{ "data": "added_by_name" },<?php } ?>
      <?php } else { ?>
        <?php if (is_access('799')) { ?>{ "data": "date" },<?php } ?>
        <?php if (is_access('800')) { ?>{ "data": "supplier_name" },<?php } ?>
        <?php if (is_access('801')) { ?>{ "data": "payment_type" },<?php } ?>
        <?php if (is_access('802')) { ?>{ "data": "amount" },<?php } ?>
        <?php if (is_access('803')) { ?>{ "data": "remark" },<?php } ?>
        <?php if (is_access('804')) { ?>{ "data": "status" },<?php } ?>
        <?php if (is_access('805')) { ?>{ "data": "added_by_name" },<?php } ?>
      <?php } ?>
      <?php if ($show_actions) { ?>{ "data": "actions", "className": "text-center" },<?php } ?>
    ],

    "buttons": [
      <?php if ($show_export) { ?>
      {
        "extend": 'excel',
        "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
      },
      {
        "extend": 'pdfHtml5',
        "orientation": 'landscape',
        "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
      }
      <?php } ?>
    ],

    "infoCallback": function(settings, start, end, max, total, pre) {
      $(".loader").fadeOut("slow");
      $('#total_count').html('(' + total + ')');
      return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
    }

  }).on('draw.dt', function() {
    $(".loader").fadeOut("slow");
  });

  <?php if ($tab == 'cash') { ?>
  function updateFilterCount() {
    var defaults = {
      <?php if (is_access('777')) { ?>'toggle-date': true,<?php } ?>
      <?php if (is_access('778')) { ?>'toggle-customer-name': true,<?php } ?>
      <?php if (is_access('782')) { ?>'toggle-debit': true,<?php } ?>
      <?php if (is_access('1022')) { ?>'toggle-credit': true,<?php } ?>
      <?php if (is_access('779')) { ?>'toggle-source': true,<?php } ?>
      <?php if (is_access('780')) { ?>'toggle-direction': true,<?php } ?>
      <?php if (is_access('781')) { ?>'toggle-type': true,<?php } ?>
      <?php if (is_access('783')) { ?>'toggle-narration': true,<?php } ?>
      <?php if (is_access('784')) { ?>'toggle-added-by': true<?php } ?>
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
    <?php if (is_access('777')) { ?>dataTable.column(<?php echo (int)$col_date; ?>).visible($('#toggle-date').is(':checked'), false);<?php } ?>
    <?php if (is_access('778')) { ?>dataTable.column(<?php echo (int)$col_customer; ?>).visible($('#toggle-customer-name').is(':checked'), false);<?php } ?>
    <?php if (is_access('782')) { ?>dataTable.column(<?php echo (int)$col_debit; ?>).visible($('#toggle-debit').is(':checked'), false);<?php } ?>
    <?php if (is_access('1022')) { ?>dataTable.column(<?php echo (int)$col_credit; ?>).visible($('#toggle-credit').is(':checked'), false);<?php } ?>
    <?php if (is_access('779')) { ?>dataTable.column(<?php echo (int)$col_source; ?>).visible($('#toggle-source').is(':checked'), false);<?php } ?>
    <?php if (is_access('780')) { ?>dataTable.column(<?php echo (int)$col_direction; ?>).visible($('#toggle-direction').is(':checked'), false);<?php } ?>
    <?php if (is_access('781')) { ?>dataTable.column(<?php echo (int)$col_type; ?>).visible($('#toggle-type').is(':checked'), false);<?php } ?>
    <?php if (is_access('783')) { ?>dataTable.column(<?php echo (int)$col_narration; ?>).visible($('#toggle-narration').is(':checked'), false);<?php } ?>
    <?php if (is_access('784')) { ?>dataTable.column(<?php echo (int)$col_added; ?>).visible($('#toggle-added-by').is(':checked'), false);<?php } ?>
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
    <?php if (is_access('777')) { ?>$('#toggle-date').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('778')) { ?>$('#toggle-customer-name').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('782')) { ?>$('#toggle-debit').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('1022')) { ?>$('#toggle-credit').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('779')) { ?>$('#toggle-source').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('780')) { ?>$('#toggle-direction').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('781')) { ?>$('#toggle-type').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('783')) { ?>$('#toggle-narration').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('784')) { ?>$('#toggle-added-by').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    applyColumnFilters();
  });

  applyColumnFilters();
  <?php } ?>
});

</script>
