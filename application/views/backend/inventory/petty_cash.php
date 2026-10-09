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
  $tab = (isset($_GET['tab']) && $_GET['tab'] == 'transferred') ? 'transferred' : 'cash';
  $date_range_param = (isset($_GET['date_range']) && $_GET['date_range'] != '') ? '&date_range=' . urlencode($_GET['date_range']) : '';
  $company_id = $this->session->userdata('company_id');
  $cash_summary = $this->inventory_model->get_cash_in_hand_summary($company_id);
  $cash_in_hand = $cash_summary['cash_in_hand'];

  $total_overall_amount = 0;
  if ($tab == 'transferred') {
    if ($this->db->table_exists('transferred_cash')) {
      $trans_sql = "SELECT IFNULL(SUM(amount), 0) as total_amt FROM transferred_cash WHERE 1=1";
      if (!empty($company_id)) {
        $trans_sql .= " AND company_id = '$company_id'";
      }
      if ($this->db->field_exists('is_deleted', 'transferred_cash')) {
        $trans_sql .= " AND is_deleted = 0";
      }
      $res = $this->db->query($trans_sql)->row_array();
      $total_overall_amount = (float)($res['total_amt'] ?? 0);
    }
  } else {
    if ($this->db->table_exists('petty_cash')) {
      $petty_sql = "SELECT IFNULL(SUM(amount), 0) as total_amt FROM petty_cash WHERE 1=1";
      if (!empty($company_id)) {
        $petty_sql .= " AND company_id = '$company_id'";
      }
      if ($this->db->field_exists('is_deleted', 'petty_cash')) {
        $petty_sql .= " AND is_deleted = 0";
      }
      $res = $this->db->query($petty_sql)->row_array();
      $total_overall_amount = (float)($res['total_amt'] ?? 0);
    }
  }
?>

<div class="row" id="table-bordered">
  <?php include('filter/date_range.php'); ?>

  <!-- Cash in Hand Summary Cards (Official & Unofficial) -->
  <div class="col-12 mb-2">
    <div class="row g-2">
      <!-- Official Cash in Hand -->
      <div class="col-12 col-md-4 mb-1 mb-md-0">
        <div class="cash-stat-card card-official">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-shield text-primary me-50"></i> Official Cash in Hand</span>
            <span class="badge bg-light-primary text-primary font-weight-bold">Official</span>
          </div>
          <div class="stat-value text-primary">
            ₹ <?= number_format($cash_summary['official']['total'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Payment: <b>₹ <?= number_format($cash_summary['official']['customer_net'], 2); ?></b></span>
            <span>Manual: <b>₹ <?= number_format($cash_summary['official']['manual_payments'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- Unofficial Cash in Hand -->
      <div class="col-12 col-md-4 mb-1 mb-md-0">
        <div class="cash-stat-card card-unofficial">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial Cash in Hand</span>
            <span class="badge bg-light-secondary text-secondary font-weight-bold">Unofficial</span>
          </div>
          <div class="stat-value text-secondary">
            ₹ <?= number_format($cash_summary['unofficial']['total'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Payment: <b>₹ <?= number_format($cash_summary['unofficial']['customer_net'], 2); ?></b></span>
            <span>Manual: <b>₹ <?= number_format($cash_summary['unofficial']['manual_payments'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <!-- Total Cash in Hand -->
      <div class="col-12 col-md-4">
        <div class="cash-stat-card card-total">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-pocket text-success me-50"></i> Total Cash in Hand</span>
            <span class="badge bg-light-success text-success font-weight-bold">Net Available</span>
          </div>
          <div class="stat-value text-success">
            ₹ <?= number_format($cash_summary['cash_in_hand'], 2); ?>
          </div>
          <div class="stat-detail">
            <span>Total Recv: <b>₹ <?= number_format($cash_summary['total_received'], 2); ?></b></span>
            <span>Expense: <b>₹ <?= number_format($cash_summary['total_expense'] + $cash_summary['total_transferred'], 2); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 d-flex">
    <a href="<?php echo base_url('inventory/cash-book?tab=cash' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'cash') ? 'active' : ''; ?>">Cash</a>
    <a href="<?php echo base_url('inventory/cash-book?tab=transferred' . $date_range_param); ?>" class="sub-link <?php echo ($tab == 'transferred') ? 'active' : ''; ?>">Transferred</a>
  </div>

  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-lg-4 col-md-12 mt-10">
            <h5 class="mb-0"><b>Total <?= ($tab == 'transferred') ? 'Transferred' : 'Expense'; ?><span id="total_count"> (0)</span></b>
            </h5>
          </div>
          <div class="col-lg-8 col-md-12 mt-10 text-lg-end d-flex justify-content-lg-end justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0 me-2"><b>Cash in Hand: <span class="text-success">₹ <?= number_format($cash_in_hand ?? 0, 2); ?></span></b></h5>
            <h5 class="mb-0"><b><?= ($tab == 'transferred') ? 'Total Amount Transferred' : 'Total Expense'; ?>: <span id="total_petty_amount" class="text-primary">₹ <?= number_format($total_overall_amount, 2); ?></span></b></h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <a href="<?php echo site_url('inventory/cash-book/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0"><span><i class="feather icon-plus"></i> Add Expense</span></a>     
        <a href="javascript:void(0);" onclick="showAjaxModal('<?php echo base_url('modal/popup_inventory/modal_transfer_cash'); ?>', 'Transfer Cash');" class="dt-button add-new desktop-tab add-btn btn btn-success" style="margin-right: 8px;" tabindex="0"><span><i class="feather icon-arrow-right-circle"></i> Transfer</span></a>     
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <th>Date</th>
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
        if (json.total_amount !== undefined) {
          $('#total_petty_amount').html(json.total_amount);
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
      { "data": "added_by_name" },
      { "data": "actions", "className": "text-center" },
    ],

    "buttons": [{
        "extend": 'excel',
        "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4] }
      },
      {
        "extend": 'pdfHtml5',
        "orientation": 'landscape',
        "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
        "exportOptions": { "columns": [0, 1, 2, 3, 4] }
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
