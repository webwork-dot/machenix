<?php
  // Get Invoice Order ID from param2
  $invoice_order_id = $param2;

  // Get invoice order details
  $invoice_order = $this->db->query("SELECT * FROM invoice_order WHERE id = '$invoice_order_id'")->row_array();
  
  if (empty($invoice_order)) {
    echo '<div class="alert alert-danger">Invoice Order not found.</div>';
    return;
  }

  // Get invoice products
  $products = $this->db->query("SELECT * FROM invoice_order_products WHERE parent_id = '$invoice_order_id'")->result_array();

  // Calculate product totals
  $total_qty = 0;
  $total_amt = 0;
  $total_bill_exc_gst = 0;
  $total_gst = 0;
  $total_bill_inc_gst = 0;
  $total_final = 0;

  foreach ($products as $p) {
      $total_qty += floatval($p['qty'] ?? 0);
      $total_amt += floatval($p['total_amount'] ?? 0);
      $total_bill_exc_gst += floatval($p['bill_total'] ?? 0);
      $total_gst += floatval($p['gst_amount'] ?? 0);
      $total_bill_inc_gst += floatval($p['total_bill_gst_amount'] ?? 0);
      $total_final += floatval($p['final_total'] ?? 0);
  }
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

  .invoice-order-view-modal {
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
    color: #0f172a;
    font-weight: 600;
  }

  .address-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    height: 100%;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  }

  .address-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .shipping-theme { color: #3b82f6; }
  .billing-theme { color: #10b981; }

  .address-text {
    font-size: 0.88rem;
    color: #334155;
    line-height: 1.5;
    margin-bottom: 12px;
    min-height: 48px;
  }

  .address-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 0.8rem;
    color: #64748b;
    border-top: 1px solid #f1f5f9;
    padding-top: 10px;
  }

  .notes-card {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 15px;
  }

  .notes-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #b45309;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .notes-content {
    font-size: 0.88rem;
    color: #78350f;
    line-height: 1.5;
  }

  .section-heading {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 20px 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
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

  .summary-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    margin-bottom: 10px;
  }

  .summary-header {
    background: #f8fafc;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 700;
    font-size: 0.95rem;
    color: #1e293b;
  }

  .summary-body {
    padding: 10px 20px;
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
</style>

<div class="invoice-order-view-modal">
  <div class="row">
    <div class="col-12">
      
      <!-- Meta Information Dashboard -->
      <div class="meta-dashboard">
        <div class="meta-dashboard-title">
          <span><i class="fa fa-file-text-o me-2" style="color:#5a79c0;"></i> Invoice No: <?php echo htmlspecialchars($invoice_order['invoice_no']); ?></span>

        </div>
        
        <div class="meta-grid">
          <div class="meta-item">
            <span class="meta-label">Customer</span>
            <span class="meta-value"><?php echo htmlspecialchars($invoice_order['customer_name']); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Order No</span>
            <span class="meta-value"><?php echo htmlspecialchars($invoice_order['order_no']); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Invoice Date</span>
            <span class="meta-value"><?php echo date('d M, Y', strtotime($invoice_order['invoice_date'])); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Warehouse</span>
            <span class="meta-value"><?php echo !empty($invoice_order['warehouse_name']) ? htmlspecialchars($invoice_order['warehouse_name']) : 'N/A'; ?></span>
          </div>
        </div>
      </div>

      <!-- Address Cards Side-by-Side -->
      <div class="row">
        <div class="col-md-6 mb-2">
          <div class="address-card shipping-card">
            <h5 class="address-title shipping-theme"><i class="fa fa-truck"></i> Shipping Address</h5>
            <div class="address-text"><?php echo htmlspecialchars($invoice_order['shipping_address'] ?? 'N/A'); ?></div>
            <div class="address-meta">
              <span><strong>City:</strong> <?php echo htmlspecialchars($invoice_order['shipping_city_name'] ?? 'N/A'); ?></span>
              <span><strong>State:</strong> <?php echo htmlspecialchars($invoice_order['shipping_state_name'] ?? 'N/A'); ?></span>
              <span><strong>Pincode:</strong> <?php echo htmlspecialchars($invoice_order['shipping_pincode'] ?? 'N/A'); ?></span>
              <?php if (!empty($invoice_order['shipping_gst_no'])) { ?>
                <span><strong>GSTIN:</strong> <?php echo htmlspecialchars($invoice_order['shipping_gst_no']); ?></span>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-2">
          <div class="address-card billing-card">
            <h5 class="address-title billing-theme"><i class="fa fa-envelope"></i> Billing Address</h5>
            <div class="address-text"><?php echo htmlspecialchars($invoice_order['billing_address'] ?? 'N/A'); ?></div>
            <div class="address-meta">
              <span><strong>City:</strong> <?php echo htmlspecialchars($invoice_order['billing_city_name'] ?? 'N/A'); ?></span>
              <span><strong>State:</strong> <?php echo htmlspecialchars($invoice_order['billing_state_name'] ?? 'N/A'); ?></span>
              <span><strong>Pincode:</strong> <?php echo htmlspecialchars($invoice_order['billing_pincode'] ?? 'N/A'); ?></span>
              <?php if (!empty($invoice_order['billing_gst_no'])) { ?>
                <span><strong>GSTIN:</strong> <?php echo htmlspecialchars($invoice_order['billing_gst_no']); ?></span>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Remark / Narration Section -->
      <?php if (!empty($invoice_order['remark']) || !empty($invoice_order['narration'])) { ?>
        <div class="notes-card">
          <div class="notes-title"><i class="fa fa-commenting-o"></i> Remark / Narration</div>
          <div class="notes-content">
            <?php 
              if (!empty($invoice_order['remark'])) echo "<strong>Remark:</strong> " . nl2br(htmlspecialchars($invoice_order['remark'])) . "<br>";
              if (!empty($invoice_order['narration'])) echo "<strong>Narration:</strong> " . nl2br(htmlspecialchars($invoice_order['narration']));
            ?>
          </div>
        </div>
      <?php } ?>

      <!-- Products Section -->
      <h4 class="section-heading"><i class="fa fa-cubes"></i> Invoice Products</h4>
      <div class="table-responsive-container">
        <div class="table-responsive">
          <table class="table table-bordered premium-table">
            <thead>
              <tr>
                <th style="width: 60px;">Sr No.</th>
                <th style="text-align: left;">Product</th>
                <th style="width: 100px;">Batch</th>
                <th style="width: 80px;">Qty</th>
                <th style="width: 120px;">Rate</th>
                <th style="width: 120px;">Total Amt</th>
                <th style="width: 80px;">GST %</th>
                <th style="width: 120px;">GST Amt</th>
                <th style="width: 140px;">Final Total</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $sr = 1; 
                foreach ($products as $p) { 
                  $batch_no = '';
                  if (!empty($p['batch_id'])) {
                      $batch_record = $this->db->get_where('sales_order_product_batch', ['id' => $p['batch_id']])->row_array();
                      $batch_no = $batch_record['batch_no'] ?? '';
                  }
              ?>
                <tr>
                  <td class="text-center"><?php echo $sr++; ?></td>
                  <td style="text-align: left; font-weight: 600;">
                    <?php echo htmlspecialchars($p['product_name']); ?>
                    <?php if (!empty($p['item_code'])) { ?>
                      <br><small class="text-secondary"><?php echo htmlspecialchars($p['item_code']); ?></small>
                    <?php } ?>
                  </td>
                  <td class="text-center"><?php echo !empty($batch_no) ? htmlspecialchars($batch_no) : '-'; ?></td>
                  <td class="text-center"><?php echo (int)$p['qty']; ?></td>
                  <td class="text-right"><?php echo number_format($p['amount'], 2); ?></td>
                  <td class="text-right"><?php echo number_format($p['total_amount'], 2); ?></td>
                  <td class="text-center"><?php echo number_format($p['gst'], 2); ?>%</td>
                  <td class="text-right"><?php echo number_format($p['gst_amount'], 2); ?></td>
                  <td class="text-right" style="font-weight: 600;"><?php echo number_format($p['final_total'], 2); ?></td>
                </tr>
              <?php } ?>
              
              <!-- Summary Row -->
              <tr class="totals-row">
                <td colspan="3" class="text-right">Totals:</td>
                <td class="text-center"><?php echo (int)$total_qty; ?></td>
                <td class="text-right">&mdash;</td>
                <td class="text-right"><?php echo number_format($total_amt, 2); ?></td>
                <td class="text-right">&mdash;</td>
                <td class="text-right"><?php echo number_format($total_gst, 2); ?></td>
                <td class="text-right"><?php echo number_format($total_final, 2); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Financial Calculation Summary Card -->
      <div class="row justify-content-end mt-3">
        <div class="col-md-5">
          <div class="summary-card">
            <div class="summary-header">Invoice Summary</div>
            <div class="summary-body">
              <div class="summary-row">
                <span>Basic Value</span>
                <strong>₹<?php echo number_format($invoice_order['basic_value'], 2); ?></strong>
              </div>
              <div class="summary-row">
                <span>GST Total</span>
                <strong>₹<?php echo number_format($invoice_order['gst_total'], 2); ?></strong>
              </div>
              <?php if (!empty($invoice_order['round_of']) && $invoice_order['round_of'] != 0) { ?>
                <div class="summary-row">
                  <span>Round Off</span>
                  <strong>₹<?php echo number_format($invoice_order['round_of'], 2); ?></strong>
                </div>
              <?php } ?>
              <div class="summary-row grand-total-row">
                <span>Grand Total</span>
                <span>₹<?php echo number_format($invoice_order['grand_total'], 2); ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
