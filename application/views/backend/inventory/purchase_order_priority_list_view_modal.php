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
  
  .section-header {
    font-weight: bold;
    font-size: 13px;
    color: #5a79c0;
    margin-top: 12px;
    margin-bottom: 4px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 2px;
  }
  
  .table-responsive {
    max-height: 400px;
    overflow-y: auto;
  }
  
  .compact-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    font-size: 11px;
  }
  
  .compact-table th {
    position: sticky;
    top: 0;
    z-index: 10;
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

  .notes-box {
    background-color: #fff8e1;
    border-left: 4px solid #ffb300;
    padding: 6px 10px;
    margin-top: 10px;
    font-size: 11px;
    border-radius: 0 4px 4px 0;
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
    
    <!-- Priority List Section -->
    <div>
      <div class="section-header">
        <i class="fa fa-list-ul"></i> Priority List
      </div>
      <div class="table-responsive">
        <table class="compact-table">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">Sr No</th>
              <th style="width: 120px;">Supplier</th>
              <th style="width: 80px;" class="text-center">Type</th>
              <th style="width: 130px;">Category</th>
              <th>Product Name</th>
              <th style="width: 100px;">Model No</th>
              <th style="width: 70px;" class="text-center">Qty</th>
              <th style="width: 70px;" class="text-right">CBM</th>
              <th style="width: 90px;" class="text-right">Total CBM</th>
              <th style="width: 90px;" class="text-center">Pending PO</th>
              <th style="width: 90px;" class="text-center">Loading List</th>
              <th style="width: 90px;" class="text-center">In Stock</th>
              <th style="width: 100px;" class="text-center">Company Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $sr_no = 1;
            if (!empty($priority_products)) {
              foreach ($priority_products as $product): 
                $is_ready = ($product['product_type'] == 'ready');
            ?>
            <tr>
              <td class="text-center"><?php echo $sr_no++; ?></td>
              <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
              <td class="text-center">
                <?php if ($is_ready) { ?>
                  <span class="badge-ready">Ready</span>
                <?php } else { ?>
                  <span class="badge-spare">Spare</span>
                <?php } ?>
              </td>
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
            <tr class="totals-row-bg">
              <td colspan="6" class="text-right">Total:</td>
              <td class="text-center"><?php echo number_format($priority_total_qty, 0); ?></td>
              <td></td>
              <td class="text-right"><?php echo number_format($priority_total_cbm, 5); ?></td>
              <td colspan="5"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Loading List Section -->
    <?php if (!empty($loading_products)) { ?>
    <div style="margin-top: 15px;">
      <div class="section-header">
        <i class="fa fa-truck"></i> Loading List (2nd Load List, If Space Left)
      </div>
      <div class="table-responsive">
        <table class="compact-table">
          <thead>
            <tr>
              <th style="width: 50px;" class="text-center">Sr No</th>
              <th style="width: 120px;">Supplier</th>
              <th style="width: 80px;" class="text-center">Type</th>
              <th style="width: 130px;">Category</th>
              <th>Product Name</th>
              <th style="width: 100px;">Model No</th>
              <th style="width: 70px;" class="text-center">Qty</th>
              <th style="width: 70px;" class="text-right">CBM</th>
              <th style="width: 90px;" class="text-right">Total CBM</th>
              <th style="width: 90px;" class="text-center">Pending PO</th>
              <th style="width: 90px;" class="text-center">Loading List</th>
              <th style="width: 90px;" class="text-center">In Stock</th>
              <th style="width: 100px;" class="text-center">Company Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $sr_no = 1;
            foreach ($loading_products as $product): 
              $is_ready = ($product['product_type'] == 'ready');
            ?>
            <tr>
              <td class="text-center"><?php echo $sr_no++; ?></td>
              <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
              <td class="text-center">
                <?php if ($is_ready) { ?>
                  <span class="badge-ready">Ready</span>
                <?php } else { ?>
                  <span class="badge-spare">Spare</span>
                <?php } ?>
              </td>
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
            ?>
            <tr class="totals-row-bg">
              <td colspan="6" class="text-right">Total:</td>
              <td class="text-center"><?php echo number_format($loading_total_qty, 0); ?></td>
              <td></td>
              <td class="text-right"><?php echo number_format($loading_total_cbm, 5); ?></td>
              <td colspan="5"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php } ?>

    <!-- Remarks Section -->
    <?php if (!empty($po_data['notes'])): ?>
    <div class="notes-box">
      <strong><i class="fa fa-comment"></i> Notes / Remarks:</strong> 
      <span><?php echo htmlspecialchars($po_data['notes']); ?></span>
    </div>
    <?php endif; ?>

  </div>
</div>
