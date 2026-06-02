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
      ORDER BY pop.id
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
    color: #5a79c0;
    margin-top: 12px;
    /* margin-bottom: 4px;
    border-bottom: 1px solid #dee2e6; */
    padding-bottom: 2px;
  }
  
  .compact-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    font-size: 11px;
  }
  
  .compact-table th {
    background-color: #f1f3f5;
    color: #495057;
    padding: 4px 6px;
    font-weight: 600;
    border: 1px solid #dee2e6;
    text-align: left;
  }
  
  .compact-table td {
    padding: 4px 6px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
  }
  
  .compact-table tbody tr:nth-child(even) {
    background-color: #f8fafc;
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
    background-color: #f1f3f5 !important;
    font-weight: bold;
  }

  .grand-totals-bar {
    background-color: #f8f9fa;
    border-top: 2px solid #5a79c0;
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

  .grand-totals-bar .total-value.ready {
    color: #198754;
  }

  .grand-totals-bar .total-value.spare {
    color: #b58100;
  }

  .grand-totals-bar .total-value.grand {
    color: #5a79c0;
    font-size: 14px;
  }
</style>

<div class="row">
  <div class="col-12">
    <!-- PO Header Information -->
    <div class="po-meta-container">
      <div class="d-flex flex-wrap">
        <div class="po-meta-item"><strong>Batch No:</strong> <?php echo $po_data['voucher_no']; ?></div>
        <div class="po-meta-item"><strong>Date:</strong> <?php echo date('d M, Y', strtotime($po_data['date'])); ?></div>
        <div class="po-meta-item"><strong>Loading Date:</strong> <?php echo date('d M, Y', strtotime($po_data['delivery_date'])); ?></div>
        <div class="po-meta-item"><strong>Warehouse:</strong> <?php echo $warehouse['name'] ?? 'N/A'; ?></div>
        <?php if (!empty($po_data['mode_of_payment'])) { ?>
          <div class="po-meta-item"><strong>Payment Mode/Terms:</strong> <?php echo $po_data['mode_of_payment']; ?></div>
        <?php } ?>
        <?php if (!empty($po_data['dispatch'])) { ?>
          <div class="po-meta-item"><strong>Dispatch Through:</strong> <?php echo $po_data['dispatch']; ?></div>
        <?php } ?>
        <?php if (!empty($po_data['destination'])) { ?>
          <div class="po-meta-item"><strong>Destination:</strong> <?php echo $po_data['destination']; ?></div>
        <?php } ?>
        <?php if (!empty($po_data['other_refrence'])) { ?>
          <div class="po-meta-item"><strong>Other Ref:</strong> <?php echo $po_data['other_refrence']; ?></div>
        <?php } ?>
        <?php if (!empty($po_data['terms_of_delivery'])) { ?>
          <div class="po-meta-item"><strong>Delivery Terms:</strong> <?php echo $po_data['terms_of_delivery']; ?></div>
        <?php } ?>
        <?php if (!empty($po_data['narration'])) { ?>
          <div class="po-meta-item"><strong>Narration:</strong> <?php echo $po_data['narration']; ?></div>
        <?php } ?>
        <div class="po-meta-item w-100 mt-1"><strong>Delivery Address:</strong> <?php echo $po_data['delivery_address'] ?? 'N/A'; ?></div>
      </div>
    </div>

    <!-- Products by Supplier -->
    <?php 
    $supplier_count = 0;
    foreach ($grouped_products as $supplier_id => $supplier_data) { 
      $supplier_count++;
      $supplier_all_products = array_merge($supplier_data['ready'], $supplier_data['spare']);
    ?>
    <div>
      <div class="supplier-header">
        Supplier <?php echo $supplier_count; ?>: <?php echo $supplier_data['supplier_name']; ?>
      </div>
      
      <table class="compact-table">
        <thead>
          <tr>
            <th style="width: 50px;" class="text-center">Sr No.</th>
            <th>Product Name</th>
            <th style="width: 120px;">Model No.</th>
            <th style="width: 80px;" class="text-center">Type</th>
            <th style="width: 70px;" class="text-center">Qty</th>
            <th style="width: 70px;" class="text-right">CBM</th>
            <th style="width: 90px;" class="text-right">Total CBM</th>
            <th style="width: 90px;" class="text-center">Pending PO</th>
            <th style="width: 90px;" class="text-center">Loading PO</th>
            <th style="width: 90px;" class="text-center">In Stock</th>
            <th style="width: 100px;" class="text-center">Company Stock</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $sr_no = 1;
          $ready_subtotal_qty = 0;
          $ready_subtotal_cbm = 0;
          $spare_subtotal_qty = 0;
          $spare_subtotal_cbm = 0;
          
          foreach ($supplier_all_products as $product) {
            $is_ready = ($product['product_type'] == 'ready');
            if ($is_ready) {
              $ready_subtotal_qty += intval($product['quantity']);
              $ready_subtotal_cbm += floatval($product['total_cbm']);
            } else {
              $spare_subtotal_qty += intval($product['quantity']);
              $spare_subtotal_cbm += floatval($product['total_cbm']);
            }
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
            <td class="text-center"><?php echo number_format($product['quantity'], 0); ?></td>
            <td class="text-right"><?php echo number_format($product['cbm'], 5); ?></td>
            <td class="text-right"><?php echo number_format($product['total_cbm'], 5); ?></td>
            <td class="text-center"><?php echo number_format($product['pending_po_qty'] ?? 0, 0); ?></td>
            <td class="text-center"><?php echo number_format($product['loading_list_qty'] ?? 0, 0); ?></td>
            <td class="text-center"><?php echo number_format($product['in_stock_qty'] ?? 0, 0); ?></td>
            <td class="text-center"><?php echo number_format($product['current_company_qty'] ?? 0, 0); ?></td>
          </tr>
          <?php } ?>
          
          <?php if ($ready_subtotal_qty > 0) { ?>
          <tr class="totals-row-bg">
            <td colspan="4" class="text-right">Subtotal (Ready):</td>
            <td class="text-center"><?php echo number_format($ready_subtotal_qty, 0); ?></td>
            <td></td>
            <td class="text-right"><?php echo number_format($ready_subtotal_cbm, 5); ?></td>
            <td colspan="4"></td>
          </tr>
          <?php } ?>
          
          <?php if ($spare_subtotal_qty > 0) { ?>
          <tr class="totals-row-bg">
            <td colspan="4" class="text-right">Subtotal (Spare):</td>
            <td class="text-center"><?php echo number_format($spare_subtotal_qty, 0); ?></td>
            <td></td>
            <td class="text-right"><?php echo number_format($spare_subtotal_cbm, 5); ?></td>
            <td colspan="4"></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php } ?>

    <!-- Grand Totals Bar -->
    <div class="grand-totals-bar">
      <div class="totals-grid">
        <div class="total-block">
          <div class="total-title">Ready Qty</div>
          <div class="total-value ready"><?php echo number_format($total_ready_qty, 0); ?></div>
        </div>
        <div class="total-block">
          <div class="total-title">Ready CBM</div>
          <div class="total-value ready"><?php echo number_format($total_ready_cbm, 5); ?></div>
        </div>
        <div class="total-block">
          <div class="total-title">Spare Qty</div>
          <div class="total-value spare"><?php echo number_format($total_spare_qty, 0); ?></div>
        </div>
        <div class="total-block">
          <div class="total-title">Spare CBM</div>
          <div class="total-value spare"><?php echo number_format($total_spare_cbm, 5); ?></div>
        </div>
        <div class="total-block">
          <div class="total-title">Grand Total Qty</div>
          <div class="total-value grand"><?php echo number_format($total_ready_qty + $total_spare_qty, 0); ?></div>
        </div>
        <div class="total-block">
          <div class="total-title">Grand Total CBM</div>
          <div class="total-value grand"><?php echo number_format($po_data['total_cbm'] ?? ($total_ready_cbm + $total_spare_cbm), 5); ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
