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
  .table-error td {
    background: #febdb9;
    color: #3c3a3a;
    font-weight: 600 !important;
  }

  .fixedElement {
    background: white;
    border-radius: .428rem;
  }

  .mp-table-title-row {
    gap: 10px;
    margin-bottom: 4px;
  }
  .mp-table-title-row .stock-filter-dropdown {
    position: relative;
    z-index: 5;
  }
  .mp-table-title-row .stock-filter-menu {
    min-width: 320px;
    max-width: 360px;
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
  #report-datatable tr.mp-total-row td {
    background: #eef3ff !important;
    font-weight: 700;
  }
</style>

<?php
  $company_id = $this->session->userdata('company_id');
  $summary = [
    'official'   => ['total' => 0.0, 'receive' => 0.0, 'transfer' => 0.0],
    'unofficial' => ['total' => 0.0, 'receive' => 0.0, 'transfer' => 0.0],
  ];

  $where_company = !empty($company_id) ? " WHERE company_id = '$company_id'" : " WHERE 1=1";
  $where_company .= " AND is_deleted = 0 AND type = 'manual'";

  if (isset($_GET['date_range']) && $_GET['date_range'] != '') {
    $date_range = explode(' - ', $_GET['date_range']);
    $from = date('Y-m-d', strtotime($date_range[0]));
    $to = date('Y-m-d', strtotime($date_range[1]));
    $where_company .= " AND (DATE(date) >= '$from' AND DATE(date) <= '$to')";
  }

  $sql = "SELECT payment_type,
            IFNULL(SUM(CASE WHEN (payment_mode = 'payment' OR payment_mode IS NULL) THEN amount ELSE 0 END), 0) as receive_amt,
            IFNULL(SUM(CASE WHEN payment_mode = 'return' THEN amount ELSE 0 END), 0) as transfer_amt
          FROM customer_payment
          $where_company
          GROUP BY payment_type";
  $q = $this->db->query($sql);
  if (!empty($q)) {
    foreach ($q->result_array() as $row) {
      $ptype = ($row['payment_type'] ?? '') === 'unofficial' ? 'unofficial' : 'official';
      $receive = (float)($row['receive_amt'] ?? 0);
      $transfer = (float)($row['transfer_amt'] ?? 0);
      $summary[$ptype]['receive']  += $receive;
      $summary[$ptype]['transfer'] += $transfer;
      $summary[$ptype]['total']    += ($receive - $transfer);
    }
  }

  $official_net   = (float)$summary['official']['total'];
  $unofficial_net = (float)$summary['unofficial']['total'];

  $has_col_filters = is_access('762') || is_access('1018') || is_access('763') || is_access('1019')
    || is_access('764') || is_access('765') || is_access('766') || is_access('1020')
    || is_access('1021') || is_access('767');
?>

<div class="row" id="table-bordered">
  <?php include('filter/date_range.php'); ?>

  <div class="col-12 mb-2">
    <div class="row g-2">
      <div class="col-12 col-md-6 mb-1 mb-md-0">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-shield text-primary me-50"></i> Official</span>
            <span class="badge bg-light-primary text-primary font-weight-bold">Official</span>
          </div>
          <div class="stat-value <?= $official_net < 0 ? 'text-danger' : 'text-primary'; ?>" id="official_total">
            <?= $official_net < 0 ? '- ₹ ' . number_format(abs($official_net), 2) : '₹ ' . number_format($official_net, 2); ?>
          </div>
          <div class="stat-detail">
            <span>Receive: <b id="official_receive" class="text-success">₹ <?= number_format($summary['official']['receive'], 2); ?></b></span>
            <span>Transfer: <b id="official_transfer" class="text-danger">₹ <?= number_format($summary['official']['transfer'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="cash-stat-card card-unofficial">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial</span>
            <span class="badge bg-light-secondary text-secondary font-weight-bold">Unofficial</span>
          </div>
          <div class="stat-value <?= $unofficial_net < 0 ? 'text-danger' : 'text-secondary'; ?>" id="unofficial_total">
            <?= $unofficial_net < 0 ? '- ₹ ' . number_format(abs($unofficial_net), 2) : '₹ ' . number_format($unofficial_net, 2); ?>
          </div>
          <div class="stat-detail">
            <span>Receive: <b id="unofficial_receive" class="text-success">₹ <?= number_format($summary['unofficial']['receive'], 2); ?></b></span>
            <span>Transfer: <b id="unofficial_transfer" class="text-danger">₹ <?= number_format($summary['unofficial']['transfer'], 2); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center flex-wrap gap-1 mp-table-title-row">
          <h5 class="mb-0"><b>Total Manual Payments<span id="total_count"> (0)</span></b></h5>

          <?php if ($has_col_filters) { ?>
          <div class="dropdown stock-filter-dropdown ms-50">
            <button class="btn-filter-dropdown dropdown-toggle" type="button" id="mpFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <i class="feather icon-filter"></i>
              <span>Filters</span>
              <span class="filter-count-badge" id="filter-active-count">0</span>
            </button>
            <div class="dropdown-menu stock-filter-menu" aria-labelledby="mpFilterDropdown">
              <div class="filter-menu-header">
                <span class="title">Table Filters</span>
              </div>
              <div class="filter-menu-body" id="column-filters-container">
                <div class="filter-menu-label">Columns</div>
                <?php if (is_access('762')) { ?>
                <label class="filter-chip active" for="toggle-date" title="Toggle Date column">
                  <input type="checkbox" id="toggle-date" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Date</span>
                </label>
                <?php } ?>
                <?php if (is_access('1018')) { ?>
                <label class="filter-chip active" for="toggle-customer-name" title="Toggle Customer Name column">
                  <input type="checkbox" id="toggle-customer-name" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Customer Name</span>
                </label>
                <?php } ?>
                <?php if (is_access('763')) { ?>
                <label class="filter-chip active" for="toggle-debit" title="Toggle Debit Amt column">
                  <input type="checkbox" id="toggle-debit" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Debit Amt</span>
                </label>
                <?php } ?>
                <?php if (is_access('1019')) { ?>
                <label class="filter-chip active" for="toggle-credit" title="Toggle Credit Amt column">
                  <input type="checkbox" id="toggle-credit" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Credit Amt</span>
                </label>
                <?php } ?>
                <?php if (is_access('764')) { ?>
                <label class="filter-chip active" for="toggle-mode" title="Toggle Mode column">
                  <input type="checkbox" id="toggle-mode" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Mode</span>
                </label>
                <?php } ?>
                <?php if (is_access('765')) { ?>
                <label class="filter-chip active" for="toggle-type" title="Toggle Type column">
                  <input type="checkbox" id="toggle-type" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Type</span>
                </label>
                <?php } ?>
                <?php if (is_access('766')) { ?>
                <label class="filter-chip active" for="toggle-method" title="Toggle Method column">
                  <input type="checkbox" id="toggle-method" class="column-filter-checkbox" checked>
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Method</span>
                </label>
                <?php } ?>
                <?php if (is_access('1020')) { ?>
                <label class="filter-chip" for="toggle-bank" title="Toggle Bank Name & AC No column">
                  <input type="checkbox" id="toggle-bank" class="column-filter-checkbox">
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Bank Name & AC No</span>
                </label>
                <?php } ?>
                <?php if (is_access('1021')) { ?>
                <label class="filter-chip" for="toggle-narration" title="Toggle Narration column">
                  <input type="checkbox" id="toggle-narration" class="column-filter-checkbox">
                  <span class="chip-box"><i class="feather icon-check"></i></span>
                  <span class="chip-text">Narration</span>
                </label>
                <?php } ?>
                <?php if (is_access('767')) { ?>
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
        <?php if (is_access('768')) { ?>
        <a href="<?php echo site_url('inventory/manual-payment/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0">
          <span><i class="feather icon-plus"></i> Add Manual Payment</span>
        </a>
        <?php } ?>
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <?php if (is_access('762')) { ?><th>Date</th><?php } ?>
              <?php if (is_access('1018')) { ?><th>Customer Name</th><?php } ?>
              <?php if (is_access('763')) { ?><th>Debit Amt</th><?php } ?>
              <?php if (is_access('1019')) { ?><th>Credit Amt</th><?php } ?>
              <?php if (is_access('764')) { ?><th>Mode</th><?php } ?>
              <?php if (is_access('765')) { ?><th>Type</th><?php } ?>
              <?php if (is_access('766')) { ?><th>Method</th><?php } ?>
              <?php if (is_access('1020')) { ?><th>Bank Name & AC No</th><?php } ?>
              <?php if (is_access('1021')) { ?><th>Narration</th><?php } ?>
              <?php if (is_access('767')) { ?><th>Added By</th><?php } ?>
              <?php if (is_access('769') || is_access('770') || is_access('771')) { ?><th style="width: 80px;" class="text-center">Action</th><?php } ?>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function($) {
  function fmtAmt(val) {
    var n = parseFloat(val) || 0;
    return '₹ ' + Math.abs(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  <?php
    $ci = 0;
    $col_date = $col_customer = $col_debit = $col_credit = 0;
    $col_mode = $col_type = $col_method = $col_bank = $col_narration = $col_added = 0;
    if (is_access('762')) { $ci++; $col_date = $ci; }
    if (is_access('1018')) { $ci++; $col_customer = $ci; }
    if (is_access('763')) { $ci++; $col_debit = $ci; }
    if (is_access('1019')) { $ci++; $col_credit = $ci; }
    if (is_access('764')) { $ci++; $col_mode = $ci; }
    if (is_access('765')) { $ci++; $col_type = $ci; }
    if (is_access('766')) { $ci++; $col_method = $ci; }
    if (is_access('1020')) { $ci++; $col_bank = $ci; }
    if (is_access('1021')) { $ci++; $col_narration = $ci; }
    if (is_access('767')) { $ci++; $col_added = $ci; }
  ?>

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
        $(row).addClass('mp-total-row');
      }
    },

    "ajax": {
      "url": "<?php echo base_url('inventory/get_manual_payments_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
      },
      "dataSrc": function(json) {
        function fmtNet(val) {
          var n = parseFloat(val) || 0;
          if (n < 0) {
            return '- ₹ ' + Math.abs(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
          }
          return '₹ ' + Math.abs(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        if (json.summary) {
          var oNet = parseFloat(json.summary.official.total) || 0;
          var uNet = parseFloat(json.summary.unofficial.total) || 0;
          $('#official_total').html(fmtNet(oNet))
            .toggleClass('text-danger', oNet < 0)
            .toggleClass('text-primary', oNet >= 0);
          $('#official_receive').html(fmtAmt(json.summary.official.receive));
          $('#official_transfer').html(fmtAmt(json.summary.official.transfer));
          $('#unofficial_total').html(fmtNet(uNet))
            .toggleClass('text-danger', uNet < 0)
            .toggleClass('text-secondary', uNet >= 0);
          $('#unofficial_receive').html(fmtAmt(json.summary.unofficial.receive));
          $('#unofficial_transfer').html(fmtAmt(json.summary.unofficial.transfer));
        }

        var rows = json.data || [];
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
            customer_name: '',
            debit_amt: '<span class="fw-bold">' + fmtAmt(debitTotal) + '</span>',
            credit_amt: '<span class="fw-bold">' + fmtAmt(creditTotal) + '</span>',
            debit_raw: 0,
            credit_raw: 0,
            payment_mode: '',
            payment_type: '',
            payment_method: '',
            bank_detail: '',
            narration: '',
            added_by_name: '',
            actions: ''
          });
        }
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
      <?php if (is_access('762')) { ?>{ "data": "date", "className": "text-center" },<?php } ?>
      <?php if (is_access('1018')) { ?>{ "data": "customer_name" },<?php } ?>
      <?php if (is_access('763')) { ?>{ "data": "debit_amt", "className": "text-end" },<?php } ?>
      <?php if (is_access('1019')) { ?>{ "data": "credit_amt", "className": "text-end" },<?php } ?>
      <?php if (is_access('764')) { ?>{ "data": "payment_mode", "className": "text-center" },<?php } ?>
      <?php if (is_access('765')) { ?>{ "data": "payment_type", "className": "text-center" },<?php } ?>
      <?php if (is_access('766')) { ?>{ "data": "payment_method", "className": "text-center" },<?php } ?>
      <?php if (is_access('1020')) { ?>{ "data": "bank_detail", "visible": false },<?php } ?>
      <?php if (is_access('1021')) { ?>{ "data": "narration", "visible": false },<?php } ?>
      <?php if (is_access('767')) { ?>{ "data": "added_by_name" },<?php } ?>
      <?php if (is_access('769') || is_access('770') || is_access('771')) { ?>{ "data": "actions", "className": "text-center" },<?php } ?>
    ],

    "buttons": [
      <?php if (is_access('772')) { ?>
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

    "infoCallback": function(settings, start, end, max, total, pre) {
      $(".loader").fadeOut("slow");
      $('#total_count').html('(' + total + ')');
      return 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
    }

  }).on('draw.dt', function() {
    $(".loader").fadeOut("slow");
  });

  function updateFilterCount() {
    var defaults = {
      <?php if (is_access('762')) { ?>'toggle-date': true,<?php } ?>
      <?php if (is_access('1018')) { ?>'toggle-customer-name': true,<?php } ?>
      <?php if (is_access('763')) { ?>'toggle-debit': true,<?php } ?>
      <?php if (is_access('1019')) { ?>'toggle-credit': true,<?php } ?>
      <?php if (is_access('764')) { ?>'toggle-mode': true,<?php } ?>
      <?php if (is_access('765')) { ?>'toggle-type': true,<?php } ?>
      <?php if (is_access('766')) { ?>'toggle-method': true,<?php } ?>
      <?php if (is_access('1020')) { ?>'toggle-bank': false,<?php } ?>
      <?php if (is_access('1021')) { ?>'toggle-narration': false,<?php } ?>
      <?php if (is_access('767')) { ?>'toggle-added-by': true<?php } ?>
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
    <?php if (is_access('762')) { ?>dataTable.column(<?php echo (int)$col_date; ?>).visible($('#toggle-date').is(':checked'), false);<?php } ?>
    <?php if (is_access('1018')) { ?>dataTable.column(<?php echo (int)$col_customer; ?>).visible($('#toggle-customer-name').is(':checked'), false);<?php } ?>
    <?php if (is_access('763')) { ?>dataTable.column(<?php echo (int)$col_debit; ?>).visible($('#toggle-debit').is(':checked'), false);<?php } ?>
    <?php if (is_access('1019')) { ?>dataTable.column(<?php echo (int)$col_credit; ?>).visible($('#toggle-credit').is(':checked'), false);<?php } ?>
    <?php if (is_access('764')) { ?>dataTable.column(<?php echo (int)$col_mode; ?>).visible($('#toggle-mode').is(':checked'), false);<?php } ?>
    <?php if (is_access('765')) { ?>dataTable.column(<?php echo (int)$col_type; ?>).visible($('#toggle-type').is(':checked'), false);<?php } ?>
    <?php if (is_access('766')) { ?>dataTable.column(<?php echo (int)$col_method; ?>).visible($('#toggle-method').is(':checked'), false);<?php } ?>
    <?php if (is_access('1020')) { ?>dataTable.column(<?php echo (int)$col_bank; ?>).visible($('#toggle-bank').is(':checked'), false);<?php } ?>
    <?php if (is_access('1021')) { ?>dataTable.column(<?php echo (int)$col_narration; ?>).visible($('#toggle-narration').is(':checked'), false);<?php } ?>
    <?php if (is_access('767')) { ?>dataTable.column(<?php echo (int)$col_added; ?>).visible($('#toggle-added-by').is(':checked'), false);<?php } ?>
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
    <?php if (is_access('762')) { ?>$('#toggle-date').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('1018')) { ?>$('#toggle-customer-name').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('763')) { ?>$('#toggle-debit').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('1019')) { ?>$('#toggle-credit').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('764')) { ?>$('#toggle-mode').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('765')) { ?>$('#toggle-type').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('766')) { ?>$('#toggle-method').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    <?php if (is_access('1020')) { ?>$('#toggle-bank').prop('checked', false).closest('.filter-chip').removeClass('active');<?php } ?>
    <?php if (is_access('1021')) { ?>$('#toggle-narration').prop('checked', false).closest('.filter-chip').removeClass('active');<?php } ?>
    <?php if (is_access('767')) { ?>$('#toggle-added-by').prop('checked', true).closest('.filter-chip').addClass('active');<?php } ?>
    applyColumnFilters();
  });

  applyColumnFilters();
});
</script>
