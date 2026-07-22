<?php
  // Get Sales Order ID from param2
  $sales_order_id = $param2;

  // Get sales order details
  $sales_order = $this->db->query("SELECT * FROM sales_order WHERE id = '$sales_order_id'")->row_array();
  
  if (empty($sales_order)) {
    echo '<div class="alert alert-danger">Sales Order not found.</div>';
    return;
  }

  // Get products
  $products = $this->db->query("SELECT * FROM sales_order_product WHERE order_id = '$sales_order_id'")->result_array();

  // Get charges
  $charges = $this->db->query("SELECT * FROM sales_order_charges WHERE order_id = '$sales_order_id'")->result_array();

  // Get batch allocations
  $batches = $this->db->query("SELECT * FROM sales_order_product_batch WHERE order_id = '$sales_order_id'")->result_array();
  $batches_by_product = [];
  foreach ($batches as $b) {
      $batches_by_product[$b['order_product_id']][] = $b;
  }

  // commissions
  $commissions = $this->db->query("SELECT * FROM sales_commission WHERE order_id = '$sales_order_id'")->result_array();
  
  $is_distributor = isset($sales_order['is_distributor']) ? (int)$sales_order['is_distributor'] : 0;

  $commission_map = [];
  foreach ($commissions as $comm) {
      $comm_pct = ($is_distributor == 1) ? floatval($comm['distributer_comm']) : floatval($comm['customer_comm']);
      $product_comm = floatval($comm['product_comm']);
      
      if (!empty($comm['order_product_id'])) {
          $commission_map['order_product_' . $comm['order_product_id']] = [
              'product_comm' => $product_comm,
              'sale_comm_pct' => $comm_pct
          ];
      }
      if (!empty($comm['product_id'])) {
          $commission_map['product_' . $comm['product_id']] = [
              'product_comm' => $product_comm,
              'sale_comm_pct' => $comm_pct
          ];
      }
  }
  $grand_total_commission = 0.00;
  $grand_total_sale_commission = 0.00;

  // Calculate product totals
  $total_qty = 0;
  $total_amt = 0;
  $total_bill_exc_gst = 0;
  $total_gst = 0;
  $total_bill_inc_gst = 0;
  $total_black = 0;
  $total_final = 0;

  foreach ($products as $p) {
      $total_qty += floatval($p['qty'] ?? 0);
      $total_amt += floatval($p['total_amount'] ?? 0);
      $total_bill_exc_gst += floatval($p['bill_total'] ?? 0);
      $total_gst += floatval($p['gst_amount'] ?? 0);
      $total_bill_inc_gst += floatval($p['total_bill_gst_amount'] ?? 0);
      $total_black += floatval($p['black_total'] ?? 0);
      $total_final += floatval($p['final_total'] ?? 0);
  }

  // Calculate charge totals
  $total_charges_amt = 0;
  foreach ($charges as $c) {
      $total_charges_amt += floatval($c['total_amt'] ?? 0);
  }
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

  .sales-order-view-modal {
    padding: 10px;
    font-family: 'Outfit', 'Inter', 'Segoe UI', sans-serif;
  }
  
  .meta-dashboard {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 15px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  }

  .meta-dashboard-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
  }

  .meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .meta-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
    font-weight: 700;
  }

  .meta-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #0f172a;
  }

  .address-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    height: 100%;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .address-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(90, 121, 192, 0.08);
  }

  .address-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .shipping-theme {
    color: #10b981;
    border-bottom-color: #ecfdf5 !important;
  }
  .shipping-card {
    border-left: 4px solid #10b981;
  }
  .billing-theme {
    color: #5a79c0;
    border-bottom-color: #eff6ff !important;
  }
  .billing-card {
    border-left: 4px solid #5a79c0;
  }

  .address-text {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #334155;
    white-space: pre-wrap;
  }

  .address-meta {
    margin-top: 12px;
    font-size: 0.82rem;
    color: #64748b;
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
  }

  .notes-card {
    background: #f8fafc;
    border-left: 4px solid #64748b;
    border-radius: 8px;
    padding: 16px;
    margin-top: 10px;
  }

  .notes-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .notes-content {
    font-size: 0.88rem;
    color: #475569;
    line-height: 1.5;
    white-space: pre-wrap;
  }

  .section-heading {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin-top: 20px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .section-heading i {
    color: #5a79c0;
  }

  .table-responsive-container {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    overflow: hidden;
    margin-bottom: 10px;
  }
  
  .table-responsive {
    max-height: 600px;
    overflow-x: auto;
    overflow-y: auto;
  }

  .premium-table {
    min-width: 1800px;
    width: 100%;
    margin-bottom: 0;
    font-size: 0.86rem;
  }

  .premium-table thead th {
    background: linear-gradient(135deg, #2f3b52 0%, #1e2533 100%) !important;
    color: #ffffff !important;
    font-weight: 600;
    text-align: center;
    padding: 12px 10px;
    border: none;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .premium-table tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    border-color: #f1f5f9;
    white-space: nowrap;
    border-right: 1px solid #f1f5f9;
  }

  .premium-table tbody td:last-child {
    border-right: none;
  }

  .premium-table tbody tr {
    transition: background-color 0.15s ease;
  }

  .premium-table tbody tr:hover {
    background-color: #f8fafc;
  }

  .totals-row {
    background-color: #f8fafc;
    font-weight: 700;
    color: #0f172a;
    border-top: 2px solid #cbd5e1;
  }

  .batch-row {
    background-color: #fafbfc !important;
  }

  .batch-row td {
    font-size: 0.82rem;
    color: #475569;
    padding: 6px 12px !important;
    border-bottom: 1px dashed #e2e8f0;
  }

  .batch-indicator {
    position: relative;
    padding-left: 32px !important;
  }

  .batch-indicator::before {
    content: '';
    position: absolute;
    left: 20px;
    top: -8px;
    bottom: 50%;
    width: 10px;
    border-left: 2px solid #cbd5e1;
    border-bottom: 2px solid #cbd5e1;
    border-bottom-left-radius: 4px;
  }

  .summary-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    overflow: hidden;
  }

  .summary-header {
    background: #f8fafc;
    padding: 15px 20px;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .summary-body {
    padding: 20px;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
    color: #475569;
  }

  .summary-row:last-of-type {
    border-bottom: none;
  }

  .summary-row.grand-total-row {
    background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
    color: #ffffff;
    padding: 16px 20px;
    margin: 12px -20px -20px -20px;
    font-weight: 800;
    font-size: 1.2rem;
    border-radius: 0 0 12px 12px;
    border-top: none;
    box-shadow: 0 -4px 10px rgba(90, 121, 192, 0.1);
  }

  .charges-table {
    width: 100%;
    margin-bottom: 0;
  }

  .charges-table thead th {
    background: #f8fafc !important;
    color: #475569 !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 0.5px;
    padding: 12px 10px;
    border-bottom: 2px solid #e2e8f0 !important;
  }

  .charges-table tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    font-size: 0.88rem;
    border-bottom: 1px solid #f1f5f9;
  }
</style>

<div class="sales-order-view-modal">
  <div class="row">
    <div class="col-12">
      
      <!-- Meta Information Dashboard -->
      <div class="meta-dashboard">
        <div class="meta-dashboard-title">
          <span><i class="fa fa-file-text-o me-2" style="color:#5a79c0;"></i> Sales Order #<?php echo htmlspecialchars($sales_order['order_no']); ?></span>
        </div>
        
        <div class="meta-grid">
          <div class="meta-item">
            <span class="meta-label">Customer</span>
            <span class="meta-value"><?php echo htmlspecialchars($sales_order['customer_name']); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Reference No</span>
            <span class="meta-value"><?php echo !empty($sales_order['refrence_no']) ? htmlspecialchars($sales_order['refrence_no']) : '<span class="text-secondary">N/A</span>'; ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Order Date</span>
            <span class="meta-value"><?php echo date('d M, Y', strtotime($sales_order['date'])); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Warehouse</span>
            <span class="meta-value"><?php echo !empty($sales_order['warehouse_name']) ? htmlspecialchars($sales_order['warehouse_name']) : '<span class="text-secondary">Not Allocated</span>'; ?></span>
          </div>
        </div>
      </div>

      <!-- Products Section -->
      <h4 class="section-heading"><i class="fa fa-cubes"></i> Ordered Products</h4>
      <div class="table-responsive-container">
        <div class="table-responsive">
          <table class="table table-bordered premium-table">
            <thead>
              <tr>
                <th style="width: 60px;">Sr No.</th>
                <th style="width: 250px; text-align: left;">Product</th>
                <th style="width: 80px;">Qty</th>
                <th style="width: 120px;">Per Qty Amt</th>
                <th style="width: 120px;">Total Amt</th>
                <th style="width: 120px;">Per Qty Bill</th>
                <th style="width: 120px;">Total Bill</th>
                <th style="width: 80px;">GST %</th>
                <th style="width: 120px;">GST Amt</th>
                <th style="width: 140px;">Total Bill GST</th>
                <th style="width: 120px;">Per Qty Black</th>
                <th style="width: 120px;">Total Black</th>
                <th style="width: 140px;">Final Total</th>
                <th style="width: 140px;">Total Commission</th>
                <th style="width: 140px;">Sale Commission</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $sr = 1; 
                foreach ($products as $p) { 
                  $order_product_id = $p['id'];
                  $product_batches = $batches_by_product[$order_product_id] ?? [];
                  $has_batches = !empty($product_batches);
              ?>
                <!-- Main Product Row -->
                <tr>
                  <td class="text-center"><?php echo $sr++; ?></td>
                  <td style="text-align: left; font-weight: 600;">
                    <?php echo htmlspecialchars($p['product_name']); ?>
                    <?php if (!empty($p['item_code'])) { ?>
                      <br><small class="text-secondary"><?php echo htmlspecialchars($p['item_code']); ?></small>
                    <?php } ?>
                  </td>
                  <td class="text-center"><?php echo number_format($p['qty'], 2); ?></td>
                  <td class="text-right"><?php echo number_format($p['amount'], 2); ?></td>
                  
                  <?php if ($has_batches) { ?>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                  <?php } else { ?>
                    <td class="text-right"><?php echo number_format($p['total_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($p['bill_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($p['bill_total'], 2); ?></td>
                  <?php } ?>

                  <td class="text-center"><?php echo number_format($p['gst'], 2); ?>%</td>

                  <?php if ($has_batches) { ?>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                    <td class="text-center text-secondary">&mdash;</td>
                  <?php } else { ?>
                    <td class="text-right"><?php echo number_format($p['gst_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($p['total_bill_gst_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($p['black_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($p['black_total'], 2); ?></td>
                    <td class="text-right" style="font-weight: 600;"><?php echo number_format($p['final_total'], 2); ?></td>
                    <?php 
                      $p_comm = null;
                      $sale_comm_pct = 0.00;
                      if (isset($commission_map['order_product_' . $p['id']])) {
                          $p_comm = $commission_map['order_product_' . $p['id']]['product_comm'];
                          $sale_comm_pct = $commission_map['order_product_' . $p['id']]['sale_comm_pct'];
                      } elseif (isset($commission_map['product_' . $p['product_id']])) {
                          $p_comm = $commission_map['product_' . $p['product_id']]['product_comm'];
                          $sale_comm_pct = $commission_map['product_' . $p['product_id']]['sale_comm_pct'];
                      }
                      $p_comm_val = 0.00;
                      if ($p_comm !== null && $p_comm > 0) {
                          $p_qty = floatval($p['qty']);
                          $p_amount = floatval($p['amount']);
                          $p_comm_val = ($p_amount * $p_qty) / (1 + ($p_comm / 100));
                      }
                      $grand_total_commission += $p_comm_val;
                      $p_sale_comm_val = $p_comm_val * $sale_comm_pct / 100;
                      $grand_total_sale_commission += $p_sale_comm_val;
                    ?>
                    <td class="text-right"><?php echo number_format($p_comm_val, 2); ?></td>
                    <td class="text-right"><?php echo number_format($p_sale_comm_val, 2); ?></td>
                  <?php } ?>
                </tr>

                <!-- Nested Batch Allocation Rows -->
                <?php if (!empty($product_batches)) { ?>
                  <?php foreach ($product_batches as $b) { ?>
                    <tr class="batch-row">
                      <td class="batch-indicator"></td>
                      <td style="text-align: left; font-style: italic;">
                        <i class="fa fa-barcode text-secondary me-1"></i> Batch: <strong><?php echo htmlspecialchars($b['batch_no']); ?></strong>
                      </td>
                      <td class="text-center">
                        <?php echo number_format($b['white_qty'] + $b['black_qty'], 2); ?>
                        <br><small class="text-secondary">(W: <?php echo number_format($b['white_qty'], 2); ?>, B: <?php echo number_format($b['black_qty'], 2); ?>)</small>
                      </td>
                      <td class="text-right"><?php echo number_format($b['amount'], 2); ?></td>
                      <td class="text-right"><?php echo number_format(($b['white_qty'] + $b['black_qty']) * $b['amount'], 2); ?></td>
                      <td class="text-right"><?php echo number_format($b['bill_amount'], 2); ?></td>
                      <td class="text-right"><?php echo number_format($b['bill_total'], 2); ?></td>
                      <td class="text-center"><?php echo number_format($b['gst'], 2); ?>%</td>
                      <td class="text-right"><?php echo number_format($b['gst_amount'], 2); ?></td>
                      <td class="text-right"><?php echo number_format($b['total_bill_gst_amount'], 2); ?></td>
                      <td class="text-right"><?php echo number_format($b['black_amount'], 2); ?></td>
                      <td class="text-right"><?php echo number_format($b['black_total'], 2); ?></td>
                      <td class="text-right" style="font-weight: 600;"><?php echo number_format($b['final_total'], 2); ?></td>
                      <?php
                        $p_comm = null;
                        $sale_comm_pct = 0.00;
                        if (isset($commission_map['order_product_' . $p['id']])) {
                            $p_comm = $commission_map['order_product_' . $p['id']]['product_comm'];
                            $sale_comm_pct = $commission_map['order_product_' . $p['id']]['sale_comm_pct'];
                        } elseif (isset($commission_map['product_' . $p['product_id']])) {
                            $p_comm = $commission_map['product_' . $p['product_id']]['product_comm'];
                            $sale_comm_pct = $commission_map['product_' . $p['product_id']]['sale_comm_pct'];
                        }
                        $b_comm_val = 0.00;
                        if ($p_comm !== null && $p_comm > 0) {
                            $b_qty = floatval($b['white_qty'] + $b['black_qty']);
                            $b_amt = floatval($b['amount']);
                            $b_comm_val = ($b_amt * $b_qty) * ($p_comm / ($p_comm + 100));
                        }
                        $grand_total_commission += $b_comm_val;
                        $b_sale_comm_val = $b_comm_val * $sale_comm_pct / 100;
                        $grand_total_sale_commission += $b_sale_comm_val;
                      ?>
                      <td class="text-right"><?php echo number_format($b_comm_val, 2); ?></td>
                      <td class="text-right"><?php echo number_format($b_sale_comm_val, 2); ?></td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              <?php } ?>

              <!-- Product Grand Totals Row -->
              <tr class="totals-row">
                <td colspan="2" class="text-right">Total:</td>
                <td class="text-center"><?php echo number_format($total_qty, 2); ?></td>
                <td></td>
                <td class="text-right"><?php echo number_format($total_amt, 2); ?></td>
                <td></td>
                <td class="text-right"><?php echo number_format($total_bill_exc_gst, 2); ?></td>
                <td></td>
                <td class="text-right"><?php echo number_format($total_gst, 2); ?></td>
                <td class="text-right"><?php echo number_format($total_bill_inc_gst, 2); ?></td>
                <td></td>
                <td class="text-right"><?php echo number_format($total_black, 2); ?></td>
                <td class="text-right"><?php echo number_format($total_final, 2); ?></td>
                <td class="text-right"><?php echo number_format($grand_total_commission, 2); ?></td>
                <td class="text-right"><?php echo number_format($grand_total_sale_commission, 2); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Other Charges & Summary -->
      <div class="row">
        <!-- Other Charges Table -->
        <div class="col-md-6 mb-3">
          <div class="card h-100" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
            <div class="summary-header">
              <i class="fa fa-truck" style="color: #5a79c0;"></i> Other Charges
            </div>
            <div class="card-body p-0">
              <?php if (!empty($charges)) { ?>
                <table class="table charges-table">
                  <thead>
                    <tr>
                      <th class="text-center" style="width: 60px;">Sr No</th>
                      <th style="text-align: left;">Charge Type</th>
                      <th class="text-center" style="width: 100px;">GST %</th>
                      <th class="text-right" style="width: 120px;">Amount</th>
                      <th class="text-right" style="width: 140px;">Total Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $cr_sr = 1; foreach ($charges as $c) { ?>
                      <tr>
                        <td class="text-center"><?php echo $cr_sr++; ?></td>
                        <td style="text-align: left; font-weight: 600;"><?php echo htmlspecialchars($c['type']); ?></td>
                        <td class="text-center"><?php echo number_format($c['gst'], 2); ?>%</td>
                        <td class="text-right"><?php echo number_format($c['amount'], 2); ?></td>
                        <td class="text-right" style="font-weight: 600;"><?php echo number_format($c['total_amt'], 2); ?></td>
                      </tr>
                    <?php } ?>
                    <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                      <td colspan="4" class="text-right">Total Charges:</td>
                      <td class="text-right"><?php echo number_format($total_charges_amt, 2); ?></td>
                    </tr>
                  </tbody>
                </table>
              <?php } else { ?>
                <div class="text-center py-5 text-secondary">
                  <i class="fa fa-info-circle fa-2x mb-2 text-secondary"></i>
                  <p class="mb-0">No other charges applied to this order.</p>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <!-- Summary Totals Breakdown -->
        <div class="col-md-6 mb-3">
          <div class="summary-card">
            <div class="summary-header">
              <i class="fa fa-calculator" style="color: #5a79c0;"></i> Financial Summary
            </div>
            <div class="summary-body">
              <div class="summary-row">
                <span>Total Bill Amount (Exc. GST)</span>
                <strong><?php echo number_format($sales_order['basic_value'], 2); ?></strong>
              </div>

              <!-- GST Breakdown -->
              <?php if ($sales_order['gst_type'] == 'IGST') { ?>
                <div class="summary-row">
                  <span>IGST Amount</span>
                  <strong><?php echo number_format($sales_order['igst'], 2); ?></strong>
                </div>
              <?php } else { ?>
                <div class="summary-row">
                  <span>CGST Amount</span>
                  <strong><?php echo number_format($sales_order['central_gst'], 2); ?></strong>
                </div>
                <div class="summary-row">
                  <span>SGST Amount</span>
                  <strong><?php echo number_format($sales_order['state_gst'], 2); ?></strong>
                </div>
              <?php } ?>

              <div class="summary-row">
                <span>Total Bill Amount (Incl. GST)</span>
                <strong><?php echo number_format($sales_order['net_sales_value_1'], 2); ?></strong>
              </div>

              <div class="summary-row" style="border-bottom: 2px dashed #e2e8f0; padding-bottom: 12px;">
                <span>Total Black Amount</span>
                <strong><?php echo number_format($sales_order['total_black_amt'], 2); ?></strong>
              </div>

              <div class="summary-row" style="padding-top: 12px;">
                <span>Subtotal (Bill + Black)</span>
                <strong><?php echo number_format($sales_order['net_sales_value_2'], 2); ?></strong>
              </div>

              <div class="summary-row">
                <span>Other Charges</span>
                <strong><?php echo number_format($sales_order['other_charges_amount'], 2); ?></strong>
              </div>

              <div class="summary-row">
                <span>Round Off</span>
                <strong><?php echo number_format($sales_order['round_of'], 2); ?></strong>
              </div>

              <div class="summary-row grand-total-row">
                <span>Grand Total</span>
                <span><?php echo number_format($sales_order['grand_total'], 2); ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
