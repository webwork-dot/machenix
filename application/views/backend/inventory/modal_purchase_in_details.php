<?php
  // Get Purchase In / PO ID from param2
  $po_id = $param2;

  // Get PO header details
  $po_data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();
  
  if (empty($po_data)) {
    echo '<div class="alert alert-danger">Purchase In record not found.</div>';
    return;
  }

  // Get warehouse details
  $warehouse_name = $po_data['warehouse_name'] ?? '';
  if (empty($warehouse_name) && !empty($po_data['warehouse_id'])) {
    $warehouse = $this->db->query("SELECT name FROM warehouse WHERE id = '" . $po_data['warehouse_id'] . "'")->row_array();
    $warehouse_name = $warehouse['name'] ?? 'N/A';
  }

  // Purchase method
  $method = !empty($po_data['method']) ? strtolower($po_data['method']) : 'local';

  // Get supplier / company details
  $supplier_name = $po_data['supplier_name'] ?? '';
  if (empty($supplier_name) && !empty($po_data['supplier_id'])) {
    if ($method == 'company') {
      $comp = $this->db->query("SELECT name FROM company WHERE id = '" . $po_data['supplier_id'] . "'")->row_array();
      $supplier_name = $comp['name'] ?? 'N/A';
    } else {
      $sup = $this->db->query("SELECT name FROM supplier WHERE id = '" . $po_data['supplier_id'] . "'")->row_array();
      $supplier_name = $sup['name'] ?? 'N/A';
    }
  }

  // Get products
  if ($method == 'company') {
    $products = $this->db->query("
      SELECT pop.*, COALESCE(c.name, '') as sup_name
      FROM purchase_order_product pop
      LEFT JOIN company c ON c.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id'
      ORDER BY pop.id ASC
    ")->result_array();
  } else {
    $products = $this->db->query("
      SELECT pop.*, COALESCE(s.name, '') as sup_name
      FROM purchase_order_product pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id'
      ORDER BY pop.id ASC
    ")->result_array();
  }

  // Get other charges
  $charges = $this->db->query("SELECT * FROM purchase_order_charges WHERE order_id = '$po_id' ORDER BY id ASC")->result_array();

  // Calculate totals
  $total_white_qty    = 0;
  $total_black_qty    = 0;
  $total_qty          = 0;
  $total_bill_exc_gst = 0;
  $total_gst_amount   = 0;
  $total_bill_inc_gst = 0;
  $total_black_amt    = 0;
  $total_final_amt    = 0;
  $total_cbm          = 0;

  foreach ($products as $p) {
    $w_qty = floatval($p['white_qty'] ?? 0);
    $b_qty = floatval($p['black_qty'] ?? 0);
    $qty   = floatval($p['quantity'] ?? ($w_qty + $b_qty));
    $rate  = floatval($p['rate'] ?? 0);
    $per_qty_bill = floatval($p['basic_amount'] ?? 0);
    $bill_amt = $per_qty_bill * $w_qty;
    $gst_amt  = floatval($p['gst_amount'] ?? 0);
    $bill_gst = floatval($p['total_val'] ?? ($bill_amt + $gst_amt));
    $blk_amt  = floatval($p['black_amt_total'] ?? 0);
    $fin_amt  = floatval($p['grand_total'] ?? ($bill_gst + $blk_amt));
    $cbm_item = floatval($p['total_cbm'] ?? 0);

    $total_white_qty    += $w_qty;
    $total_black_qty    += $b_qty;
    $total_qty          += $qty;
    $total_bill_exc_gst += $bill_amt;
    $total_gst_amount   += $gst_amt;
    $total_bill_inc_gst += $bill_gst;
    $total_black_amt    += $blk_amt;
    $total_final_amt    += $fin_amt;
    $total_cbm          += $cbm_item;
  }

  // Header financial amounts fallback
  $po_basic_value = floatval($po_data['basic_value'] ?? 0);
  if ($po_basic_value == 0 && $total_bill_exc_gst > 0) {
    $po_basic_value = $total_bill_exc_gst;
  }
  $po_net_sales_value_1 = floatval($po_data['net_sales_value_1'] ?? 0);
  if ($po_net_sales_value_1 == 0 && $total_bill_inc_gst > 0) {
    $po_net_sales_value_1 = $total_bill_inc_gst;
  }
  $po_black_amt = floatval($po_data['total_black_amount_summary'] ?? 0);
  if ($po_black_amt == 0 && $total_black_amt > 0) {
    $po_black_amt = $total_black_amt;
  }
  $po_net_sales_value_2 = floatval($po_data['net_sales_value_2'] ?? 0);
  if ($po_net_sales_value_2 == 0 && $total_final_amt > 0) {
    $po_net_sales_value_2 = $total_final_amt;
  }
  $po_charges_amount = floatval($po_data['other_charges_amount'] ?? 0);
  $po_round_of       = floatval($po_data['round_of'] ?? 0);
  $po_grand_total    = floatval($po_data['grand_total'] ?? 0);
  if ($po_grand_total == 0) {
    $po_grand_total = $po_net_sales_value_2 + $po_charges_amount + $po_round_of;
  }
?>

<style>
  .purchase-in-view-modal {
    padding: 6px;
    font-family: inherit;
  }
  
  .meta-dashboard {
    background: #ffffff;
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
  }

  .meta-dashboard-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px 18px;
  }

  .meta-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .meta-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
    font-weight: 700;
  }

  .meta-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: #0f172a;
  }

  .section-heading {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    margin-top: 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .section-heading i {
    color: #5a79c0;
  }

  .table-responsive-container {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    overflow: hidden;
    margin-bottom: 14px;
  }
  
  .table-responsive-custom {
    max-height: 480px;
    overflow-x: auto;
    overflow-y: auto;
  }

  .purchase-in-table {
    min-width: 1400px;
    width: 100%;
    margin-bottom: 0;
    font-size: 0.85rem;
    border-collapse: collapse;
  }

  .purchase-in-table thead th {
    background: linear-gradient(135deg, #2f3b52 0%, #1e2533 100%) !important;
    color: #ffffff !important;
    font-weight: 600;
    text-align: center;
    padding: 10px 8px;
    border: none;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 0.82rem;
  }

  .purchase-in-table tbody td {
    padding: 8px 10px;
    vertical-align: middle;
    border-color: #f1f5f9;
    white-space: nowrap;
    border-right: 1px solid #f1f5f9;
  }

  .purchase-in-table tbody td:last-child {
    border-right: none;
  }

  .purchase-in-table tbody tr:nth-child(even) {
    background-color: #f8fafc;
  }

  .purchase-in-table tbody tr:hover {
    background-color: #f1f5f9;
  }

  .totals-row-bg {
    background-color: #e9ecef !important;
    font-weight: 700;
    color: #0f172a;
    border-top: 2px solid #cbd5e1;
  }

  .summary-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    overflow: hidden;
  }

  .summary-header {
    background: #f8fafc;
    padding: 12px 16px;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
  }

  .summary-body {
    padding: 16px;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.88rem;
    color: #475569;
  }

  .summary-row:last-of-type {
    border-bottom: none;
  }

  .summary-row.grand-total-row {
    background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
    color: #ffffff;
    padding: 14px 16px;
    margin: 12px -16px -16px -16px;
    font-weight: 800;
    font-size: 1.1rem;
    border-radius: 0 0 10px 10px;
    border-top: none;
  }

  .badge-method-local {
    background-color: #d1e7dd;
    color: #0f5132;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
    display: inline-block;
  }

  .badge-method-company {
    background-color: #cfe2ff;
    color: #084298;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
    display: inline-block;
  }

  .badge-ready {
    background-color: #e0f2fe;
    color: #0369a1;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 3px;
  }

  .badge-spare {
    background-color: #fef3c7;
    color: #92400e;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 3px;
  }

  .charges-table {
    width: 100%;
    margin-bottom: 0;
    font-size: 0.85rem;
  }

  .charges-table thead th {
    background: #f1f3f5 !important;
    color: #495057 !important;
    font-weight: 600;
    padding: 8px 10px;
    font-size: 0.82rem;
  }

  .charges-table tbody td {
    padding: 8px 10px;
    border-color: #e9ecef;
  }
</style>

<div class="purchase-in-view-modal">
  <!-- Header / Metadata Dashboard -->
  <div class="meta-dashboard">
    <div class="meta-dashboard-title">
      <div>
        <i class="fa fa-file-text-o text-primary me-1"></i>
        <span>Purchase In: <strong><?php echo htmlspecialchars($po_data['voucher_no'] ?? '-'); ?></strong></span>
        <?php if (!empty($po_data['refrence_no'])) { ?>
          <span class="text-muted ms-2" style="font-size: 0.85rem; font-weight: normal;">(Ref: <?php echo htmlspecialchars($po_data['refrence_no']); ?>)</span>
        <?php } ?>
      </div>
      <div>
        <?php if ($method == 'company') { ?>
          <span class="badge-method-company"><i class="fa fa-building-o"></i> Company Purchase</span>
        <?php } else { ?>
          <span class="badge-method-local"><i class="fa fa-truck"></i> Local Purchase</span>
        <?php } ?>
      </div>
    </div>

    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Purchase Date</span>
        <span class="meta-value"><?php echo !empty($po_data['date']) ? date('d M, Y', strtotime($po_data['date'])) : '-'; ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Loading Date</span>
        <span class="meta-value"><?php echo !empty($po_data['delivery_date']) ? date('d M, Y', strtotime($po_data['delivery_date'])) : '-'; ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Supplier / Vendor</span>
        <span class="meta-value text-primary"><?php echo htmlspecialchars(!empty($supplier_name) ? $supplier_name : '-'); ?></span>
      </div>

      <div class="meta-item">
        <span class="meta-label">Warehouse</span>
        <span class="meta-value"><?php echo htmlspecialchars(!empty($warehouse_name) ? $warehouse_name : '-'); ?></span>
      </div>

      <?php if (!empty($po_data['mode_of_payment'])) { ?>
      <div class="meta-item">
        <span class="meta-label">Payment Terms</span>
        <span class="meta-value"><?php echo htmlspecialchars($po_data['mode_of_payment']); ?></span>
      </div>
      <?php } ?>

      <?php if (!empty($po_data['dispatch'])) { ?>
      <div class="meta-item">
        <span class="meta-label">Dispatch Through</span>
        <span class="meta-value"><?php echo htmlspecialchars($po_data['dispatch']); ?></span>
      </div>
      <?php } ?>

      <?php if (!empty($po_data['destination'])) { ?>
      <div class="meta-item">
        <span class="meta-label">Destination</span>
        <span class="meta-value"><?php echo htmlspecialchars($po_data['destination']); ?></span>
      </div>
      <?php } ?>

      <?php if (!empty($po_data['other_refrence'])) { ?>
      <div class="meta-item">
        <span class="meta-label">Other Reference</span>
        <span class="meta-value"><?php echo htmlspecialchars($po_data['other_refrence']); ?></span>
      </div>
      <?php } ?>

      <?php if (!empty($po_data['terms_of_delivery'])) { ?>
      <div class="meta-item">
        <span class="meta-label">Delivery Terms</span>
        <span class="meta-value"><?php echo htmlspecialchars($po_data['terms_of_delivery']); ?></span>
      </div>
      <?php } ?>

      <?php if (!empty($po_data['added_by_name'])) { ?>
      <div class="meta-item">
        <span class="meta-label">Created By</span>
        <span class="meta-value"><?php echo htmlspecialchars($po_data['added_by_name']); ?></span>
      </div>
      <?php } ?>
    </div>

    <?php if (!empty($po_data['delivery_address'])) { ?>
    <div class="mt-2 pt-1 border-top">
      <span class="meta-label">Delivery Address:</span>
      <span class="text-secondary" style="font-size: 0.88rem;"><?php echo nl2br(htmlspecialchars($po_data['delivery_address'])); ?></span>
    </div>
    <?php } ?>

    <?php if (!empty($po_data['narration'])) { ?>
    <div class="mt-1">
      <span class="meta-label">Narration / Notes:</span>
      <span class="text-secondary" style="font-size: 0.88rem;"><?php echo nl2br(htmlspecialchars($po_data['narration'])); ?></span>
    </div>
    <?php } ?>
  </div>

  <!-- Products Section -->
  <div class="section-heading">
    <i class="fa fa-cube"></i>
    <span>Products List (<?php echo count($products); ?> items)</span>
  </div>

  <div class="table-responsive-container">
    <div class="table-responsive-custom">
      <table class="table purchase-in-table">
        <thead>
          <tr>
            <th style="width: 40px;" class="text-center">#</th>
            <th style="min-width: 220px;" class="text-left">Product Name</th>
            <th style="min-width: 110px;" class="text-left">Model / SKU</th>
            <th style="min-width: 80px;" class="text-center">Type</th>
            <th style="min-width: 90px;" class="text-right">Rate (₹)</th>
            <th style="min-width: 75px;" class="text-center">White Qty</th>
            <th style="min-width: 75px;" class="text-center">Black Qty</th>
            <th style="min-width: 75px;" class="text-center">Total Qty</th>
            <th style="min-width: 100px;" class="text-right">Per Qty Bill</th>
            <th style="min-width: 105px;" class="text-right">Total Bill</th>
            <th style="min-width: 65px;" class="text-center">GST %</th>
            <th style="min-width: 95px;" class="text-right">GST Amt</th>
            <th style="min-width: 110px;" class="text-right">Total Bill GST</th>
            <th style="min-width: 100px;" class="text-right">Per Qty Black</th>
            <th style="min-width: 105px;" class="text-right">Total Black</th>
            <th style="min-width: 115px;" class="text-right">Final Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (!empty($products)) {
            $sr = 1;
            foreach ($products as $p) {
              $w_qty = floatval($p['white_qty'] ?? 0);
              $b_qty = floatval($p['black_qty'] ?? 0);
              $qty   = floatval($p['quantity'] ?? ($w_qty + $b_qty));
              $rate  = floatval($p['rate'] ?? 0);
              $per_qty_bill = floatval($p['basic_amount'] ?? 0);
              $bill_amt = $per_qty_bill * $w_qty;
              $gst_rate = floatval($p['gst'] ?? 0);
              $gst_amt  = floatval($p['gst_amount'] ?? 0);
              $bill_gst = floatval($p['total_val'] ?? ($bill_amt + $gst_amt));
              $per_qty_blk = floatval($p['black_amt'] ?? ($rate - $per_qty_bill));
              $blk_amt  = floatval($p['black_amt_total'] ?? 0);
              $fin_amt  = floatval($p['grand_total'] ?? ($bill_gst + $blk_amt));
              $is_ready = ($p['product_type'] == 'ready');
          ?>
          <tr>
            <td class="text-center"><?php echo $sr++; ?></td>
            <td class="text-left font-weight-bold">
              <?php echo htmlspecialchars($p['product_name'] ?? 'N/A'); ?>
              <?php if (!empty($p['color_name'])) { ?>
                <small class="text-muted d-block">Color: <?php echo htmlspecialchars($p['color_name']); ?></small>
              <?php } ?>
            </td>
            <td class="text-left font-monospace"><?php echo htmlspecialchars($p['item_code'] ?? '-'); ?></td>
            <td class="text-center">
              <?php if ($is_ready) { ?>
                <span class="badge-ready">Ready</span>
              <?php } else { ?>
                <span class="badge-spare">Spare</span>
              <?php } ?>
            </td>
            <td class="text-right"><?php echo number_format($rate, 2); ?></td>
            <td class="text-center"><?php echo number_format($w_qty, 0); ?></td>
            <td class="text-center"><?php echo number_format($b_qty, 0); ?></td>
            <td class="text-center font-weight-bold"><?php echo number_format($qty, 0); ?></td>
            <td class="text-right"><?php echo number_format($per_qty_bill, 2); ?></td>
            <td class="text-right"><?php echo number_format($bill_amt, 2); ?></td>
            <td class="text-center"><?php echo number_format($gst_rate, 2); ?>%</td>
            <td class="text-right"><?php echo number_format($gst_amt, 2); ?></td>
            <td class="text-right font-weight-bold"><?php echo number_format($bill_gst, 2); ?></td>
            <td class="text-right"><?php echo number_format($per_qty_blk, 2); ?></td>
            <td class="text-right"><?php echo number_format($blk_amt, 2); ?></td>
            <td class="text-right font-weight-bold text-primary"><?php echo number_format($fin_amt, 2); ?></td>
          </tr>
          <?php 
            }
          } else { 
          ?>
          <tr>
            <td colspan="16" class="text-center py-3 text-muted">No products found in this Purchase In.</td>
          </tr>
          <?php } ?>

          <?php if (!empty($products)) { ?>
          <tr class="totals-row-bg">
            <td colspan="5" class="text-right"><strong>Total:</strong></td>
            <td class="text-center"><strong><?php echo number_format($total_white_qty, 0); ?></strong></td>
            <td class="text-center"><strong><?php echo number_format($total_black_qty, 0); ?></strong></td>
            <td class="text-center"><strong><?php echo number_format($total_qty, 0); ?></strong></td>
            <td></td>
            <td class="text-right"><strong><?php echo number_format($total_bill_exc_gst, 2); ?></strong></td>
            <td></td>
            <td class="text-right"><strong><?php echo number_format($total_gst_amount, 2); ?></strong></td>
            <td class="text-right"><strong><?php echo number_format($total_bill_inc_gst, 2); ?></strong></td>
            <td></td>
            <td class="text-right"><strong><?php echo number_format($total_black_amt, 2); ?></strong></td>
            <td class="text-right text-primary"><strong><?php echo number_format($total_final_amt, 2); ?></strong></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="row">
    <!-- Other Charges (if any) -->
    <div class="col-12 col-md-6 mb-2">
      <?php if (!empty($charges)) { ?>
      <div class="summary-card mb-2">
        <div class="summary-header">
          <i class="fa fa-list-alt text-primary"></i>
          <span>Other Charges</span>
        </div>
        <div class="p-0">
          <table class="table charges-table">
            <thead>
              <tr>
                <th>Charge Description</th>
                <th class="text-right">Amount (₹)</th>
                <th class="text-center">GST %</th>
                <th class="text-right">Total (₹)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($charges as $ch) { ?>
              <tr>
                <td><?php echo htmlspecialchars($ch['type'] ?? 'Charge'); ?></td>
                <td class="text-right"><?php echo number_format(floatval($ch['amount'] ?? 0), 2); ?></td>
                <td class="text-center"><?php echo number_format(floatval($ch['gst'] ?? 0), 2); ?>%</td>
                <td class="text-right font-weight-bold"><?php echo number_format(floatval($ch['total_amt'] ?? 0), 2); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php } else { ?>
      <div class="meta-dashboard h-100">
        <span class="meta-label">Quantity Breakdown</span>
        <h4 class="text-primary mt-1 mb-0"><strong><?php echo number_format($total_qty, 0); ?></strong> Total Units</h4>
        <div class="mt-2 text-muted" style="font-size: 0.85rem;">
          <div><i class="fa fa-check-circle text-success me-1"></i> Total Products: <strong><?php echo count($products); ?></strong></div>
          <div><i class="fa fa-check-circle text-success me-1"></i> White Quantity: <strong><?php echo number_format($total_white_qty, 0); ?></strong></div>
          <div><i class="fa fa-check-circle text-success me-1"></i> Black Quantity: <strong><?php echo number_format($total_black_qty, 0); ?></strong></div>
        </div>
      </div>
      <?php } ?>
    </div>

    <!-- Financial Summary Card -->
    <div class="col-12 col-md-6 mb-2">
      <div class="summary-card">
        <div class="summary-header">
          <i class="fa fa-calculator text-primary"></i>
          <span>Financial Summary</span>
        </div>
        <div class="summary-body">
          <div class="summary-row">
            <span>Total Bill Amt (Exc GST):</span>
            <span class="font-weight-bold">₹<?php echo number_format($po_basic_value, 2); ?></span>
          </div>

          <?php 
          $gst_type = $po_data['gst_type'] ?? 'Central GST / State GST';
          if ($gst_type == 'IGST') { 
          ?>
          <div class="summary-row">
            <span>IGST Amount:</span>
            <span>₹<?php echo number_format(floatval($po_data['igst_amount'] ?? $total_gst_amount), 2); ?></span>
          </div>
          <?php } else { ?>
          <div class="summary-row">
            <span>Central GST (CGST):</span>
            <span>₹<?php echo number_format(floatval($po_data['cgst_amount'] ?? ($total_gst_amount / 2)), 2); ?></span>
          </div>
          <div class="summary-row">
            <span>State GST (SGST):</span>
            <span>₹<?php echo number_format(floatval($po_data['sgst_amount'] ?? ($total_gst_amount / 2)), 2); ?></span>
          </div>
          <?php } ?>

          <div class="summary-row">
            <span>Total Bill Amt (Incl GST):</span>
            <span class="font-weight-bold">₹<?php echo number_format($po_net_sales_value_1, 2); ?></span>
          </div>

          <div class="summary-row">
            <span>Total Black Amt:</span>
            <span class="font-weight-bold">₹<?php echo number_format($po_black_amt, 2); ?></span>
          </div>

          <div class="summary-row">
            <span>Final Products Total:</span>
            <span class="font-weight-bold">₹<?php echo number_format($po_net_sales_value_2, 2); ?></span>
          </div>

          <?php if ($po_charges_amount > 0) { ?>
          <div class="summary-row">
            <span>Other Charges:</span>
            <span>₹<?php echo number_format($po_charges_amount, 2); ?></span>
          </div>
          <?php } ?>

          <?php if ($po_round_of != 0) { ?>
          <div class="summary-row">
            <span>Round Off:</span>
            <span><?php echo ($po_round_of > 0 ? '+' : '') . number_format($po_round_of, 2); ?></span>
          </div>
          <?php } ?>

          <div class="summary-row grand-total-row">
            <span>Grand Total:</span>
            <span>₹<?php echo number_format($po_grand_total, 2); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
