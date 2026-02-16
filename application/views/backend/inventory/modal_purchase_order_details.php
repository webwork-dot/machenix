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
  
  // Get all products grouped by supplier and product_type
  $products_query = $this->db->query("
      SELECT pop.*, 
             s.name as supplier_name
      FROM purchase_order_product pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id'
      ORDER BY pop.supplier_id ASC, pop.product_type ASC, pop.id ASC
  ")->result_array();

  // Group products by supplier and product_type
  $grouped_products = array();
  foreach ($products_query as $product) {
    $supplier_id = $product['supplier_id'];
    $supplier_name = $product['supplier_name'] ?? 'Unknown Supplier';
    $product_type = $product['product_type'] ?? 'ready';
    
    if (!isset($grouped_products[$supplier_id])) {
      $grouped_products[$supplier_id] = array(
        'supplier_name' => $supplier_name,
        'ready' => array(),
        'spare' => array()
      );
    }
    
    $grouped_products[$supplier_id][$product_type][] = $product;
  }

  // Calculate totals
  $total_ready_qty = 0;
  $total_spare_qty = 0;
  $total_ready_cbm = 0;
  $total_spare_cbm = 0;
  foreach ($products_query as $product) {
    if ($product['product_type'] == 'ready') {
      $total_ready_qty += intval($product['quantity']);
      $total_ready_cbm += floatval($product['total_cbm']);
    } else {
      $total_spare_qty += intval($product['quantity']);
      $total_spare_cbm += floatval($product['total_cbm']);
    }
  }
?>

<style>
  .po-details-modal {
    max-width: 1400px !important;
  }
  
  .po-header-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
  }
  
  .po-header-section h5 {
    margin-bottom: 15px;
    color: #333;
    font-weight: bold;
  }
  
  .po-header-section .row {
    margin-bottom: 8px;
  }
  
  .po-header-section .label {
    font-weight: 600;
    color: #555;
    min-width: 150px;
    display: inline-block;
  }
  
  .supplier-section {
    margin-bottom: 30px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    overflow: hidden;
  }
  
  .supplier-header {
    background-color: #5a79c0;
    color: white;
    padding: 12px 15px;
    font-weight: bold;
    font-size: 16px;
  }
  
  .product-type-section {
    margin: 15px;
  }
  
  .product-type-header {
    background-color: #e9ecef;
    padding: 10px 15px;
    font-weight: bold;
    color: #495057;
    margin-bottom: 10px;
    border-left: 4px solid #5a79c0;
  }
  
  .product-type-header.ready {
    border-left-color: #28a745;
  }
  
  .product-type-header.spare {
    border-left-color: #ffc107;
  }
  
  .products-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
  }
  
  .products-table th {
    background-color: #f8f9fa;
    padding: 10px;
    text-align: left;
    border: 1px solid #dee2e6;
    font-weight: 600;
    font-size: 12px;
  }
  
  .products-table td {
    padding: 8px 10px;
    border: 1px solid #dee2e6;
    font-size: 12px;
  }
  
  .products-table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
  }
  
  .products-table .text-right {
    text-align: right;
  }
  
  .products-table .text-center {
    text-align: center;
  }
  
  .totals-row {
    background-color: #e9ecef !important;
    font-weight: bold;
  }
  
  .grand-totals-section {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
    border: 2px solid #5a79c0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .grand-totals-section h5 {
    margin-bottom: 20px;
    color: #5a79c0;
    font-weight: bold;
    font-size: 18px;
    text-align: center;
    padding-bottom: 10px;
    border-bottom: 2px solid #5a79c0;
  }
  
  .total-card {
    background-color: white;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  
  .total-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
  }
  
  .total-card-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
  }
  
  .total-card-value {
    font-size: 20px;
    font-weight: bold;
    color: #333;
  }
  
  .total-card-value.ready {
    color: #28a745;
  }
  
  .total-card-value.spare {
    color: #ffc107;
  }
  
  .grand-total-card {
    background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
    color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
    margin-top: 10px;
  }
  
  .grand-total-card-label {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    opacity: 0.9;
  }
  
  .grand-total-card-value {
    font-size: 28px;
    font-weight: bold;
    line-height: 1.2;
  }
</style>

<div class="row">
  <div class="col-12">
    <!-- PO Header Information -->
    <div class="po-header-section">
      <h5>Purchase Order Information</h5>
      <div class="row">
        <div class="col-md-6">
          <span class="label">Batch No:</span> <?php echo $po_data['voucher_no']; ?>
        </div>
        <div class="col-md-6">
          <span class="label">Date:</span> <?php echo date('d M, Y', strtotime($po_data['date'])); ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <span class="label">Loading Date:</span> <?php echo date('d M, Y', strtotime($po_data['delivery_date'])); ?>
        </div>
        <div class="col-md-6">
          <span class="label">Warehouse:</span> <?php echo $warehouse['name'] ?? 'N/A'; ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <span class="label">Delivery Address:</span> <?php echo $po_data['delivery_address'] ?? 'N/A'; ?>
        </div>
      </div>
      <?php if (!empty($po_data['mode_of_payment'])) { ?>
      <div class="row">
        <div class="col-md-6">
          <span class="label">Mode / Terms of Payment:</span> <?php echo $po_data['mode_of_payment']; ?>
        </div>
        <div class="col-md-6">
          <span class="label">Dispatch Through:</span> <?php echo $po_data['dispatch'] ?? 'N/A'; ?>
        </div>
      </div>
      <?php } ?>
      <?php if (!empty($po_data['destination'])) { ?>
      <div class="row">
        <div class="col-md-6">
          <span class="label">Destination:</span> <?php echo $po_data['destination']; ?>
        </div>
        <div class="col-md-6">
          <span class="label">Other Reference:</span> <?php echo $po_data['other_refrence'] ?? 'N/A'; ?>
        </div>
      </div>
      <?php } ?>
      <?php if (!empty($po_data['terms_of_delivery'])) { ?>
      <div class="row">
        <div class="col-md-12">
          <span class="label">Terms of Delivery:</span> <?php echo $po_data['terms_of_delivery']; ?>
        </div>
      </div>
      <?php } ?>
      <?php if (!empty($po_data['narration'])) { ?>
      <div class="row">
        <div class="col-md-12">
          <span class="label">Narration:</span> <?php echo $po_data['narration']; ?>
        </div>
      </div>
      <?php } ?>
    </div>

    <!-- Products by Supplier -->
    <?php 
    $supplier_count = 0;
    foreach ($grouped_products as $supplier_id => $supplier_data) { 
      $supplier_count++;
      $has_ready = !empty($supplier_data['ready']);
      $has_spare = !empty($supplier_data['spare']);
    ?>
    <div class="supplier-section">
      <div class="supplier-header">
        Supplier <?php echo $supplier_count; ?>: <?php echo $supplier_data['supplier_name']; ?>
      </div>
      
      <!-- Ready Goods Section -->
      <?php if ($has_ready) { ?>
      <div class="product-type-section">
        <div class="product-type-header ready">
          <i class="fa fa-check-circle"></i> Ready Stock
        </div>
        <table class="products-table">
          <thead>
            <tr>
              <th style="width: 50px;">Sr No.</th>
              <th>Product Name</th>
              <th style="width: 120px;">Model No.</th>
              <th style="width: 80px;" class="text-center">Qty</th>
              <th style="width: 80px;" class="text-right">CBM</th>
              <th style="width: 100px;" class="text-right">Total CBM</th>
              <th style="width: 100px;" class="text-center">Pending PO Qty</th>
              <th style="width: 100px;" class="text-center">Loading List Qty</th>
              <th style="width: 100px;" class="text-center">In Stock Qty</th>
              <th style="width: 120px;" class="text-center">Company Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $sr_no = 1;
            $ready_subtotal_qty = 0;
            $ready_subtotal_cbm = 0;
            foreach ($supplier_data['ready'] as $product) {
              $ready_subtotal_qty += intval($product['quantity']);
              $ready_subtotal_cbm += floatval($product['total_cbm']);
            ?>
            <tr>
              <td class="text-center"><?php echo $sr_no++; ?></td>
              <td><?php echo htmlspecialchars($product['product_name'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($product['item_code'] ?? 'N/A'); ?></td>
              <td class="text-center"><?php echo number_format($product['quantity'], 0); ?></td>
              <td class="text-right"><?php echo number_format($product['cbm'], 5); ?></td>
              <td class="text-right"><?php echo number_format($product['total_cbm'], 5); ?></td>
              <td class="text-center"><?php echo number_format($product['pending_po_qty'] ?? 0, 0); ?></td>
              <td class="text-center"><?php echo number_format($product['loading_list_qty'] ?? 0, 0); ?></td>
              <td class="text-center"><?php echo number_format($product['in_stock_qty'] ?? 0, 0); ?></td>
              <td class="text-center"><?php echo number_format($product['current_company_qty'] ?? 0, 0); ?></td>
            </tr>
            <?php } ?>
            <tr class="totals-row">
              <td colspan="3" class="text-right"><strong>Subtotal (Ready):</strong></td>
              <td class="text-center"><strong><?php echo number_format($ready_subtotal_qty, 0); ?></strong></td>
              <td colspan="1"></td>
              <td class="text-right"><strong><?php echo number_format($ready_subtotal_cbm, 5); ?></strong></td>
              <td colspan="4"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <?php } ?>
      
      <!-- Spare Parts Section -->
      <?php if ($has_spare) { ?>
      <div class="product-type-section">
        <div class="product-type-header spare">
          <i class="fa fa-tool"></i> Spare Part
        </div>
        <table class="products-table">
          <thead>
            <tr>
              <th style="width: 50px;">Sr No.</th>
              <th>Product Name</th>
              <th style="width: 120px;">Model No.</th>
              <th style="width: 80px;" class="text-center">Qty</th>
              <th style="width: 80px;" class="text-right">CBM</th>
              <th style="width: 100px;" class="text-right">Total CBM</th>
              <th style="width: 100px;" class="text-center">Pending PO Qty</th>
              <th style="width: 100px;" class="text-center">Loading List Qty</th>
              <th style="width: 100px;" class="text-center">In Stock Qty</th>
              <th style="width: 120px;" class="text-center">Company Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $sr_no = 1;
            $spare_subtotal_qty = 0;
            $spare_subtotal_cbm = 0;
            foreach ($supplier_data['spare'] as $product) {
              $spare_subtotal_qty += intval($product['quantity']);
              $spare_subtotal_cbm += floatval($product['total_cbm']);
            ?>
            <tr>
              <td class="text-center"><?php echo $sr_no++; ?></td>
              <td><?php echo htmlspecialchars($product['product_name'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($product['item_code'] ?? 'N/A'); ?></td>
              <td class="text-center"><?php echo number_format($product['quantity'], 0); ?></td>
              <td class="text-right"><?php echo number_format($product['cbm'], 5); ?></td>
              <td class="text-right"><?php echo number_format($product['total_cbm'], 5); ?></td>
              <td class="text-center"><?php echo number_format($product['pending_po_qty'] ?? 0, 0); ?></td>
              <td class="text-center"><?php echo number_format($product['loading_list_qty'] ?? 0, 0); ?></td>
              <td class="text-center"><?php echo number_format($product['in_stock_qty'] ?? 0, 0); ?></td>
              <td class="text-center"><?php echo number_format($product['current_company_qty'] ?? 0, 0); ?></td>
            </tr>
            <?php } ?>
            <tr class="totals-row">
              <td colspan="3" class="text-right"><strong>Subtotal (Spare):</strong></td>
              <td class="text-center"><strong><?php echo number_format($spare_subtotal_qty, 0); ?></strong></td>
              <td colspan="1"></td>
              <td class="text-right"><strong><?php echo number_format($spare_subtotal_cbm, 5); ?></strong></td>
              <td colspan="4"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <?php } ?>
    </div>
    <?php } ?>

    <!-- Grand Totals -->
    <div class="grand-totals-section">
      <h5><i class="fa fa-calculator"></i> Grand Totals</h5>
      
      <div class="row">
        <div class="col-md-3">
          <div class="total-card">
            <div class="total-card-label">Ready Goods Qty</div>
            <div class="total-card-value ready"><?php echo number_format($total_ready_qty, 0); ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="total-card">
            <div class="total-card-label">Spare Parts Qty</div>
            <div class="total-card-value spare"><?php echo number_format($total_spare_qty, 0); ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="total-card">
            <div class="total-card-label">Ready Goods CBM</div>
            <div class="total-card-value ready"><?php echo number_format($total_ready_cbm, 5); ?></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="total-card">
            <div class="total-card-label">Spare Parts CBM</div>
            <div class="total-card-value spare"><?php echo number_format($total_spare_cbm, 5); ?></div>
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <div class="grand-total-card">
            <div class="grand-total-card-label">Total Quantity</div>
            <div class="grand-total-card-value"><?php echo number_format($total_ready_qty + $total_spare_qty, 0); ?></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="grand-total-card">
            <div class="grand-total-card-label">Total CBM</div>
            <div class="grand-total-card-value"><?php echo number_format($po_data['total_cbm'] ?? ($total_ready_cbm + $total_spare_cbm), 5); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

