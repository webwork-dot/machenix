<?php
  // Get PO ID from param2
  $po_id = $param2;

  // Get PO details
  $po_data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();
  
  if (empty($po_data)) {
    echo '<div class="alert alert-danger">Purchase Order not found.</div>';
    return;
  }

  // Get warehouse details
  $warehouse = $this->inventory_model->get_warehouse_by_id($po_data['warehouse_id'])->row_array();

  // Get Priority List products (is_priority = 1)
  $priority_products = $this->db->query("
      SELECT pop.*, 
             s.name as supplier_name,
             (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
              FROM categories c 
              WHERE FIND_IN_SET(c.id, pop.categories) > 0) as category_names
      FROM po_products pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id' AND pop.is_priority = 1
      ORDER BY pop.id ASC
  ")->result_array();

  // Get Loading List products (is_priority = 0)
  $loading_products = $this->db->query("
      SELECT pop.*, 
             s.name as supplier_name,
             (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
              FROM categories c 
              WHERE FIND_IN_SET(c.id, pop.categories) > 0) as category_names
      FROM po_products pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id' AND pop.is_priority = 0
      ORDER BY pop.id ASC
  ")->result_array();

  // Calculate totals for Priority List
  $priority_total_qty = 0;
  $priority_total_cbm = 0;
  foreach ($priority_products as $product) {
    $priority_total_qty += intval($product['quantity']);
    $priority_total_cbm += floatval($product['total_cbm']);
  }

  // Calculate totals for Loading List
  $loading_total_qty = 0;
  $loading_total_cbm = 0;
  foreach ($loading_products as $product) {
    $loading_total_qty += intval($product['quantity']);
    $loading_total_cbm += floatval($product['total_cbm']);
  }
?>

<style>
  .priority-list-view-modal {
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
  
  .priority-list-section {
    margin-bottom: 30px;
  }
  
  .section-title {
    background-color: #5a79c0;
    color: white;
    padding: 12px 15px;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 0;
    border-radius: 5px 5px 0 0;
  }
  
  .table-responsive {
    max-height: 500px;
    overflow-y: auto;
  }
  
  .priority-table th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 10;
    background-color: #f8f9fa;
    font-weight: bold;
  }
  
  .totals-row {
    background-color: #f8f9fa;
    font-weight: bold;
  }
  
  .remarks-section {
    margin-top: 30px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
  }
  
  .remarks-section h5 {
    margin-bottom: 10px;
    color: #333;
    font-weight: bold;
  }
  
  .remarks-content {
    color: #555;
    white-space: pre-wrap;
    word-wrap: break-word;
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
    
    <!-- Priority List Section -->
    <div class="priority-list-section">
      <h4 class="section-title">
        <i class="fa fa-list-ul"></i> Priority List
      </h4>
      <div class="table-responsive">
        <table class="table table-bordered table-striped priority-table">
          <thead>
            <tr>
              <th style="width: 50px;">Sr No</th>
              <th style="width: 120px;">Supplier</th>
              <th style="width: 100px;">Type</th>
              <th style="width: 150px;">Category</th>
              <th style="width: 200px;">Product Name</th>
              <th style="width: 100px;">Model No</th>
              <th style="width: 80px;">Quantity</th>
              <th style="width: 80px;">CBM</th>
              <th style="width: 100px;">Total CBM</th>
              <th style="width: 100px;">Pending PO Qty</th>
              <th style="width: 100px;">Loading List Qty</th>
              <th style="width: 100px;">In Stock Qty</th>
              <th style="width: 120px;">Company Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $sr_no = 1;
            if (!empty($priority_products)) {
              foreach ($priority_products as $product): 
                $type_label = ($product['product_type'] == 'ready') ? 'Ready Goods' : (($product['product_type'] == 'spare') ? 'Spare Parts' : '');
            ?>
            <tr>
              <td class="text-center"><?php echo $sr_no++; ?></td>
              <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
              <td><?php echo $type_label; ?></td>
              <td><?php echo htmlspecialchars($product['category_names'] ?? '-'); ?></td>
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
            <?php 
              endforeach;
            } else {
            ?>
            <tr>
              <td colspan="13" class="text-center text-muted">No products in Priority List</td>
            </tr>
            <?php } ?>
            <tr class="totals-row">
              <td colspan="6" class="text-right"><strong>Total:</strong></td>
              <td class="text-center"><strong><?php echo number_format($priority_total_qty, 0); ?></strong></td>
              <td></td>
              <td class="text-right"><strong><?php echo number_format($priority_total_cbm, 5); ?></strong></td>
              <td colspan="5"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Loading List Section -->
    <div class="priority-list-section">
      <h4 class="section-title">
        <i class="fa fa-truck"></i> Loading List (2nd Load List, If Space Left)
      </h4>
      <div class="table-responsive">
        <table class="table table-bordered table-striped priority-table">
          <thead>
            <tr>
              <th style="width: 50px;">Sr No</th>
              <th style="width: 120px;">Supplier</th>
              <th style="width: 100px;">Type</th>
              <th style="width: 150px;">Category</th>
              <th style="width: 200px;">Product Name</th>
              <th style="width: 100px;">Model No</th>
              <th style="width: 80px;">Quantity</th>
              <th style="width: 80px;">CBM</th>
              <th style="width: 100px;">Total CBM</th>
              <th style="width: 100px;">Pending PO Qty</th>
              <th style="width: 100px;">Loading List Qty</th>
              <th style="width: 100px;">In Stock Qty</th>
              <th style="width: 120px;">Company Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $sr_no = 1;
            if (!empty($loading_products)) {
              foreach ($loading_products as $product): 
                $type_label = ($product['product_type'] == 'ready') ? 'Ready Goods' : (($product['product_type'] == 'spare') ? 'Spare Parts' : '');
            ?>
            <tr>
              <td class="text-center"><?php echo $sr_no++; ?></td>
              <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
              <td><?php echo $type_label; ?></td>
              <td><?php echo htmlspecialchars($product['category_names'] ?? '-'); ?></td>
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
            <?php 
              endforeach;
            } else {
            ?>
            <tr>
              <td colspan="13" class="text-center text-muted">No products in Loading List</td>
            </tr>
            <?php } ?>
            <tr class="totals-row">
              <td colspan="6" class="text-right"><strong>Total:</strong></td>
              <td class="text-center"><strong><?php echo number_format($loading_total_qty, 0); ?></strong></td>
              <td></td>
              <td class="text-right"><strong><?php echo number_format($loading_total_cbm, 5); ?></strong></td>
              <td colspan="5"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Remarks Section -->
    <?php if (!empty($po_data['notes'])): ?>
    <div class="remarks-section">
      <h5><i class="fa fa-comment"></i> Notes / Remarks</h5>
      <div class="remarks-content"><?php echo ($po_data['notes']); ?></div>
    </div>
    <?php endif; ?>

  </div>
</div>

