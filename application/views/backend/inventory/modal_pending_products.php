<?php
  // Get PO ID from param2
  $po_id = $param2;

  // Get PO header details
  $po_data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();
  
  if (empty($po_data)) {
    echo '<div class="alert alert-danger">Purchase Order not found.</div>';
    return;
  }

  // Get warehouse details
  $warehouse = $this->inventory_model->get_warehouse_by_id($po_data['warehouse_id'])->row_array();
  
  // Query pending products matching: received qty < ordered qty
  $products_query = $this->db->query("
      SELECT 
          pop.*, 
          s.name AS supplier_name,
          COALESCE((
              SELECT SUM(pip.actual_qty) 
              FROM purchase_in_product pip 
              WHERE pip.parent_id = pop.parent_id 
                AND pip.product_id = pop.product_id 
                AND pip.supplier_id = pop.supplier_id 
                AND pip.is_deleted = '0'
          ), 0) AS received_qty
      FROM purchase_order_product pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id'
        AND COALESCE((
              SELECT SUM(pip.actual_qty) 
              FROM purchase_in_product pip 
              WHERE pip.parent_id = pop.parent_id 
                AND pip.product_id = pop.product_id 
                AND pip.supplier_id = pop.supplier_id 
                AND pip.is_deleted = '0'
        ), 0) < pop.quantity
      ORDER BY pop.id ASC
  ")->result_array();

  if (empty($products_query)) {
    echo '<div class="alert alert-info text-center">No pending products found for this Purchase Order. All items have been fully received.</div>';
    return;
  }

  // Group products by supplier
  $grouped_products = array();
  foreach ($products_query as $product) {
    $supplier_id = $product['supplier_id'];
    $supplier_name = $product['supplier_name'] ?? 'Unknown Supplier';
    
    if (!isset($grouped_products[$supplier_id])) {
      $grouped_products[$supplier_id] = array(
        'supplier_name' => $supplier_name,
        'items' => array()
      );
    }
    
    $grouped_products[$supplier_id]['items'][] = $product;
  }

  // Calculate grand totals
  $grand_ordered_qty = 0;
  $grand_received_qty = 0;
  $grand_pending_qty = 0;
  foreach ($products_query as $product) {
    $ordered = intval($product['quantity']);
    $received = intval($product['received_qty']);
    $pending = $ordered - $received;

    $grand_ordered_qty += $ordered;
    $grand_received_qty += $received;
    $grand_pending_qty += $pending;
  }
?>

<style>
  .po-meta-container {
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 12px;
    border: 1px solid #dee2e6;
  }
  
  .po-meta-item {
    display: inline-flex;
    align-items: center;
    margin-right: 20px;
    margin-bottom: 4px;
    font-size: 11px;
    color: #495057;
  }
  
  .po-meta-item strong {
    color: #212529;
    margin-right: 4px;
  }
  
  .supplier-header {
    font-weight: bold;
    font-size: 13px;
    color: #c05a5a;
    margin-top: 12px;
    padding-bottom: 2px;
  }
  
  .compact-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    font-size: 11px;
  }
  
  .compact-table th {
    background-color: #f8f9fa;
    color: #495057;
    padding: 6px 8px;
    font-weight: 600;
    border: 1px solid #dee2e6;
    text-align: left;
  }
  
  .compact-table td {
    padding: 6px 8px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
  }
  
  .compact-table tbody tr:nth-child(even) {
    background-color: #fdfdfd;
  }
  
  .compact-table .text-right {
    text-align: right;
  }
  
  .compact-table .text-center {
    text-align: center;
  }
  
  .badge-ready {
    background-color: #d1e7dd;
    color: #0f5132;
    padding: 1px 4px;
    font-size: 9px;
    font-weight: 600;
    border-radius: 3px;
    display: inline-block;
  }
  
  .badge-spare {
    background-color: #fff3cd;
    color: #664d03;
    padding: 1px 4px;
    font-size: 9px;
    font-weight: 600;
    border-radius: 3px;
    display: inline-block;
  }
  
  .totals-row-bg {
    background-color: #f8f9fa !important;
    font-weight: bold;
  }

  .grand-totals-bar {
    background-color: #fdf6f6;
    border-top: 2px solid #c05a5a;
    margin-top: 15px;
    padding-top: 10px;
  }

  .grand-totals-bar .totals-grid {
    display: flex;
    justify-content: space-around;
    text-align: center;
    font-size: 11px;
  }

  .grand-totals-bar .total-block {
    flex: 1;
    border-right: 1px solid #dee2e6;
    padding: 2px 10px;
  }

  .grand-totals-bar .total-block:last-child {
    border-right: none;
  }

  .grand-totals-bar .total-title {
    color: #6c757d;
    font-size: 9px;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 2px;
  }

  .grand-totals-bar .total-value {
    font-size: 13px;
    font-weight: bold;
  }

  .grand-totals-bar .total-value.ordered {
    color: #0f5132;
  }

  .grand-totals-bar .total-value.received {
    color: #2b5bc0;
  }

  .grand-totals-bar .total-value.pending {
    color: #c0392b;
    font-size: 14px;
  }
</style>

<div class="row">
  <div class="col-12">
    <!-- PO Header Information -->
    <div class="po-meta-container">
      <div class="d-flex flex-wrap">
        <div class="po-meta-item"><strong>Batch No:</strong> <?php echo htmlspecialchars($po_data['voucher_no']); ?></div>
        <div class="po-meta-item"><strong>Date:</strong> <?php echo date('d M, Y', strtotime($po_data['date'])); ?></div>
        <div class="po-meta-item"><strong>Loading Date:</strong> <?php echo date('d M, Y', strtotime($po_data['delivery_date'])); ?></div>
        <div class="po-meta-item"><strong>Warehouse:</strong> <?php echo htmlspecialchars($warehouse['name'] ?? 'N/A'); ?></div>
      </div>
    </div>

    <!-- Products by Supplier -->
    <?php 
    $supplier_count = 0;
    foreach ($grouped_products as $supplier_id => $supplier_data) { 
      $supplier_count++;
    ?>
    <div>
      <div class="supplier-header">
        Supplier <?php echo $supplier_count; ?>: <?php echo htmlspecialchars($supplier_data['supplier_name']); ?>
      </div>
      
      <table class="compact-table">
        <thead>
          <tr>
            <th style="width: 50px;" class="text-center">Sr No.</th>
            <th>Product Name</th>
            <th style="width: 150px;">Model No.</th>
            <th style="width: 80px;" class="text-center">Type</th>
            <th style="width: 100px;" class="text-center">Ordered Qty</th>
            <th style="width: 100px;" class="text-center">Received Qty</th>
            <th style="width: 100px;" class="text-center">Pending Qty</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $sr_no = 1;
          $subtotal_ordered = 0;
          $subtotal_received = 0;
          $subtotal_pending = 0;
          
          foreach ($supplier_data['items'] as $product) {
            $is_ready = ($product['product_type'] == 'ready');
            $ordered = intval($product['quantity']);
            $received = intval($product['received_qty']);
            $pending = $ordered - $received;

            $subtotal_ordered += $ordered;
            $subtotal_received += $received;
            $subtotal_pending += $pending;
          ?>
          <tr>
            <td class="text-center"><?php echo $sr_no++; ?></td>
            <td><?php echo htmlspecialchars($product['product_name'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($product['item_code'] ?? 'N/A'); ?></td>
            <td class="text-center">
              <?php if ($is_ready) { ?>
                <span class="badge-ready">Ready</span>
              <?php } else { ?>
                <span class="badge-spare">Spare</span>
              <?php } ?>
            </td>
            <td class="text-center"><?php echo number_format($ordered, 0); ?></td>
            <td class="text-center"><?php echo number_format($received, 0); ?></td>
            <td class="text-center text-danger font-weight-bold"><?php echo number_format($pending, 0); ?></td>
          </tr>
          <?php } ?>
          
          <tr class="totals-row-bg">
            <td colspan="4" class="text-right">Subtotal:</td>
            <td class="text-center"><?php echo number_format($subtotal_ordered, 0); ?></td>
            <td class="text-center"><?php echo number_format($subtotal_received, 0); ?></td>
            <td class="text-center text-danger"><?php echo number_format($subtotal_pending, 0); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
    <?php } ?>

    <!-- Grand Totals Bar -->
    <div class="grand-totals-bar text-center">
      <div class="totals-grid">
        <div class="total-block">
          <div class="total-title">Grand Total Ordered</div>
          <div class="total-value ordered"><?php echo number_format($grand_ordered_qty, 0); ?></div>
        </div>
        <div class="total-block">
          <div class="total-title">Grand Total Received</div>
          <div class="total-value received"><?php echo number_format($grand_received_qty, 0); ?></div>
        </div>
        <div class="total-block">
          <div class="total-title">Grand Total Pending</div>
          <div class="total-value pending"><?php echo number_format($grand_pending_qty, 0); ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
