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
  $summary = [
    'official'   => ['payment' => 0.0, 'return' => 0.0],
    'unofficial' => ['payment' => 0.0, 'return' => 0.0],
  ];

  if ($this->db->table_exists('customer_payment')) {
    $where_company = !empty($company_id) ? " WHERE company_id = '$company_id'" : " WHERE 1=1";
    if ($this->db->field_exists('is_deleted', 'customer_payment')) {
      $where_company .= " AND is_deleted = 0";
    }
    if ($this->db->field_exists('type', 'customer_payment')) {
      $where_company .= " AND (type = 'customer' OR type IS NULL OR type = '')";
    }

    if (isset($_GET['date_range']) && $_GET['date_range'] != '') {
      $date_range = explode(' - ', $_GET['date_range']);
      $from = date('Y-m-d', strtotime($date_range[0]));
      $to = date('Y-m-d', strtotime($date_range[1]));
      $where_company .= " AND (DATE(date) >= '$from' AND DATE(date) <= '$to')";
    }

    $has_mode = $this->db->field_exists('payment_mode', 'customer_payment');
    $sql = "SELECT payment_type,
              IFNULL(SUM(CASE WHEN " . ($has_mode ? "(payment_mode = 'payment' OR payment_mode IS NULL)" : "1=1") . " THEN amount ELSE 0 END), 0) as payment_amt,
              IFNULL(SUM(CASE WHEN " . ($has_mode ? "payment_mode = 'return'" : "1=0") . " THEN amount ELSE 0 END), 0) as return_amt
            FROM customer_payment
            $where_company
            GROUP BY payment_type";
    $q = $this->db->query($sql);
    if (!empty($q)) {
      foreach ($q->result_array() as $row) {
        $ptype = ($row['payment_type'] ?? '') === 'unofficial' ? 'unofficial' : 'official';
        $summary[$ptype]['payment'] += (float)($row['payment_amt'] ?? 0);
        $summary[$ptype]['return']  += (float)($row['return_amt'] ?? 0);
      }
    }
  }

  $official_net   = $summary['official']['payment'] - $summary['official']['return'];
  $unofficial_net = $summary['unofficial']['payment'] - $summary['unofficial']['return'];
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
          <div class="stat-value <?= $official_net < 0 ? 'text-danger' : 'text-primary'; ?>" id="official_net">
            <?= $official_net < 0 ? '- ₹ ' . number_format(abs($official_net), 2) : '₹ ' . number_format($official_net, 2); ?>
          </div>
          <div class="stat-detail">
            <span>Payment: <b id="official_payment" class="text-success">₹ <?= number_format($summary['official']['payment'], 2); ?></b></span>
            <span>Return: <b id="official_return" class="text-danger">₹ <?= number_format($summary['official']['return'], 2); ?></b></span>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="cash-stat-card card-unofficial">
          <div class="stat-header">
            <span class="stat-title"><i class="feather icon-briefcase text-secondary me-50"></i> Unofficial</span>
            <span class="badge bg-light-secondary text-secondary font-weight-bold">Unofficial</span>
          </div>
          <div class="stat-value <?= $unofficial_net < 0 ? 'text-danger' : 'text-secondary'; ?>" id="unofficial_net">
            <?= $unofficial_net < 0 ? '- ₹ ' . number_format(abs($unofficial_net), 2) : '₹ ' . number_format($unofficial_net, 2); ?>
          </div>
          <div class="stat-detail">
            <span>Payment: <b id="unofficial_payment" class="text-success">₹ <?= number_format($summary['unofficial']['payment'], 2); ?></b></span>
            <span>Return: <b id="unofficial_return" class="text-danger">₹ <?= number_format($summary['unofficial']['return'], 2); ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-12 mt-10">
            <h5 class="mb-0"><b>Total Payments<span id="total_count"> (0)</span></b>
            </h5>
          </div>
        </div>
      </div>
      <div class="card-datatable d-report mb-2">
        <a href="<?php echo site_url('inventory/payment-receipt/add'); ?>" class="dt-button add-new desktop-tab add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0"><span><i class="feather icon-plus"></i> <?= get_phrase('add_payment_receipt');?></span></a>     
        <table class="table leads-table" id="report-datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Inv No</th>
              <th>Customer Name</th>
              <th>Amount</th>
              <th>Mode</th>
              <th>Type</th>
              <th>Method</th>
              <th>Added By</th>
              <th class="text-center">Status</th>
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
      "url": "<?php echo base_url('inventory/get_customer_payments_ajax'); ?>",
      "dataType": "json",
      "type": "POST",
      "data": function(data) {
        data.date_range = '<?php echo (isset($_GET['date_range'])) ? $_GET['date_range']:'' ?>';
      },
      "dataSrc": function(json) {
        function fmtAmt(val, isNet) {
          var n = parseFloat(val) || 0;
          if (isNet && n < 0) {
            return '- ₹ ' + Math.abs(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
          }
          return '₹ ' + Math.abs(n).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        if (json.summary) {
          var oPay = parseFloat(json.summary.official.payment) || 0;
          var oRet = parseFloat(json.summary.official.return) || 0;
          var uPay = parseFloat(json.summary.unofficial.payment) || 0;
          var uRet = parseFloat(json.summary.unofficial.return) || 0;
          var oNet = oPay - oRet;
          var uNet = uPay - uRet;

          $('#official_payment').html(fmtAmt(oPay));
          $('#official_return').html(fmtAmt(oRet));
          $('#official_net').html(fmtAmt(oNet, true))
            .toggleClass('text-danger', oNet < 0)
            .toggleClass('text-primary', oNet >= 0);

          $('#unofficial_payment').html(fmtAmt(uPay));
          $('#unofficial_return').html(fmtAmt(uRet));
          $('#unofficial_net').html(fmtAmt(uNet, true))
            .toggleClass('text-danger', uNet < 0)
            .toggleClass('text-secondary', uNet >= 0);
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
      { "data": "sr_no" },
      { "data": "date" },
      { "data": "inv_no" },
      { "data": "customer_name" },
      { "data": "amount" },
      { "data": "payment_mode" },
      { "data": "payment_type" },
      { "data": "payment_method" },
      { "data": "added_by_name" },
      { "data": "status", "className": "text-center" },
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
