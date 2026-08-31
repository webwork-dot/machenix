<?php
  // Get Customer Quotation ID from param2
  $quotation_id = $param2;

  // Get quotation details
  $quotation = $this->db->query("SELECT * FROM customer_quotations WHERE id = '$quotation_id'")->row_array();
  
  if (empty($quotation)) {
    echo '<div class="alert alert-danger">Customer Quotation not found.</div>';
    return;
  }

  // Get products
  $products = $this->db->query("SELECT * FROM customer_quotation_products WHERE parent_id = '$quotation_id' OR order_id = '{$quotation['order_no']}'")->result_array();

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
      $black_tot = floatval($p['total_amount'] ?? 0) - floatval($p['bill_total'] ?? 0);
      $total_black += $black_tot;
      $total_final += floatval($p['final_total'] ?? 0);
  }
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

  .quotation-view-modal {
    padding: 10px;
    font-family: 'Outfit', 'Inter', 'Segoe UI', sans-serif;
  }
  
  .meta-dashboard {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  }

  .meta-dashboard-title {
    font-size: 1.1rem;
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
    gap: 15px;
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
  }

  .address-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 12px;
    padding-bottom: 8px;
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
    line-height: 1.5;
    color: #334155;
    white-space: pre-wrap;
  }

  .address-meta {
    margin-top: 10px;
    font-size: 0.82rem;
    color: #64748b;
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 6px;
  }

  .table-responsive-container {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    overflow: hidden;
    margin-top: 15px;
    margin-bottom: 15px;
  }
  
  .table-responsive {
    max-height: 500px;
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
    padding: 10px 8px;
    border: none;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .premium-table tbody td {
    padding: 8px 10px;
    vertical-align: middle;
    border-color: #f1f5f9;
    white-space: nowrap;
    border-right: 1px solid #f1f5f9;
  }

  .totals-row {
    background: #f8fafc !important;
    font-weight: 700;
    color: #1e293b;
    border-top: 2px solid #cbd5e1 !important;
  }

  .summary-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 16px 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 0.9rem;
  }

  .summary-row.total-highlight {
    border-bottom: none;
    border-top: 2px solid #5a79c0;
    margin-top: 8px;
    padding-top: 10px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #5a79c0;
  }
</style>

<div class="quotation-view-modal">
  
  <!-- Header Meta -->
  <div class="meta-dashboard">
    <div class="meta-dashboard-title">
      <span><i class="feather icon-file-text me-1"></i> Customer Quotation Details</span>
      <span class="badge bg-primary"><?php echo $quotation['order_no']; ?></span>
    </div>
    <div class="meta-grid">
      <div class="meta-item">
        <span class="meta-label">Customer Name</span>
        <span class="meta-value"><?php echo $quotation['customer_name']; ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Date</span>
        <span class="meta-value"><?php echo date('d M, Y', strtotime($quotation['date'])); ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Company</span>
        <span class="meta-value"><?php echo $quotation['company_name'] ? $quotation['company_name'] : '-'; ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">GST Type</span>
        <span class="meta-value"><?php echo $quotation['gst_type'] ? $quotation['gst_type'] : 'Central GST / State GST'; ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Created By</span>
        <span class="meta-value"><?php echo $quotation['added_by_name'] ? $quotation['added_by_name'] : '-'; ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Created At</span>
        <span class="meta-value"><?php echo ($quotation['added_date'] != '0000-00-00 00:00:00') ? date('d M, Y h:i A', strtotime($quotation['added_date'])) : '-'; ?></span>
      </div>
    </div>

    <?php if (!empty($quotation['remark'])) { ?>
      <div class="mt-2 p-1 bg-light rounded">
        <strong>Remark:</strong> <?php echo nl2br(htmlspecialchars($quotation['remark'])); ?>
      </div>
    <?php } ?>
  </div>

  <!-- Products Table -->
  <div class="table-responsive-container">
    <div class="table-responsive">
      <table class="table table-bordered table-hover premium-table">
        <thead>
          <tr>
            <th>#</th>
            <th class="text-start">Product</th>
            <th class="text-start">Item Code</th>
            <th>Qty</th>
            <th>Rate (Amt)</th>
            <th>Total Amt</th>
            <th>Bill Rate</th>
            <th>Total Bill</th>
            <th>GST %</th>
            <th>GST Amt</th>
            <th>Total Bill GST</th>
            <th>Per Qty Black</th>
            <th>Total Black</th>
            <th>Final Total</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $i = 1; 
          foreach ($products as $p) { 
            $per_black = floatval($p['amount']) - floatval($p['bill_amount']);
            $tot_black = floatval($p['total_amount']) - floatval($p['bill_total']);
          ?>
          <tr>
            <td class="text-center"><?php echo $i++; ?></td>
            <td class="text-start fw-bold"><?php echo $p['product_name']; ?></td>
            <td class="text-start"><?php echo $p['item_code'] ? $p['item_code'] : '-'; ?></td>
            <td class="text-center fw-bold"><?php echo $p['qty']; ?></td>
            <td class="text-end">₹<?php echo price_format_decimal($p['amount']); ?></td>
            <td class="text-end fw-bold">₹<?php echo price_format_decimal($p['total_amount']); ?></td>
            <td class="text-end">₹<?php echo price_format_decimal($p['bill_amount']); ?></td>
            <td class="text-end">₹<?php echo price_format_decimal($p['bill_total']); ?></td>
            <td class="text-center"><?php echo floatval($p['gst']); ?>%</td>
            <td class="text-end">₹<?php echo price_format_decimal($p['gst_amount']); ?></td>
            <td class="text-end">₹<?php echo price_format_decimal($p['total_bill_gst_amount']); ?></td>
            <td class="text-end">₹<?php echo price_format_decimal($per_black); ?></td>
            <td class="text-end">₹<?php echo price_format_decimal($tot_black); ?></td>
            <td class="text-end fw-bold text-primary">₹<?php echo price_format_decimal($p['final_total']); ?></td>
          </tr>
          <?php } ?>
          <tr class="totals-row">
            <td colspan="3" class="text-end">Total:</td>
            <td class="text-center"><?php echo $total_qty; ?></td>
            <td></td>
            <td class="text-end">₹<?php echo price_format_decimal($total_amt); ?></td>
            <td></td>
            <td class="text-end">₹<?php echo price_format_decimal($total_bill_exc_gst); ?></td>
            <td></td>
            <td class="text-end">₹<?php echo price_format_decimal($total_gst); ?></td>
            <td class="text-end">₹<?php echo price_format_decimal($total_bill_inc_gst); ?></td>
            <td></td>
            <td class="text-end">₹<?php echo price_format_decimal($total_black); ?></td>
            <td class="text-end text-primary">₹<?php echo price_format_decimal($total_final); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row justify-content-end">
    <div class="col-md-6 col-lg-5">
      <div class="summary-card">
        <div class="summary-row">
          <span>Total Bill Amt (Exc GST):</span>
          <strong>₹<?php echo price_format_decimal($quotation['basic_value']); ?></strong>
        </div>
        <?php if ($quotation['gst_type'] == 'IGST') { ?>
          <div class="summary-row">
            <span>IGST:</span>
            <strong>₹<?php echo price_format_decimal($quotation['igst']); ?></strong>
          </div>
        <?php } else { ?>
          <div class="summary-row">
            <span>CGST:</span>
            <strong>₹<?php echo price_format_decimal($quotation['central_gst']); ?></strong>
          </div>
          <div class="summary-row">
            <span>SGST:</span>
            <strong>₹<?php echo price_format_decimal($quotation['state_gst']); ?></strong>
          </div>
        <?php } ?>
        <div class="summary-row">
          <span>Total Bill Amt (Incl GST):</span>
          <strong>₹<?php echo price_format_decimal($quotation['net_sales_value_1']); ?></strong>
        </div>
        <div class="summary-row">
          <span>Total Black Amt:</span>
          <strong>₹<?php echo price_format_decimal($quotation['total_black_amt']); ?></strong>
        </div>
        <div class="summary-row">
          <span>Final Total:</span>
          <strong>₹<?php echo price_format_decimal($quotation['net_sales_value_2']); ?></strong>
        </div>
        <?php if (floatval($quotation['other_charges_amount']) > 0) { ?>
        <div class="summary-row">
          <span>Other Charges:</span>
          <strong>₹<?php echo price_format_decimal($quotation['other_charges_amount']); ?></strong>
        </div>
        <?php } ?>
        <?php if (floatval($quotation['round_of']) != 0) { ?>
        <div class="summary-row">
          <span>Round Off:</span>
          <strong>₹<?php echo price_format_decimal($quotation['round_of']); ?></strong>
        </div>
        <?php } ?>
        <div class="summary-row total-highlight">
          <span>Grand Total:</span>
          <span>₹<?php echo price_format_decimal($quotation['grand_total']); ?></span>
        </div>
      </div>
    </div>
  </div>

</div>
