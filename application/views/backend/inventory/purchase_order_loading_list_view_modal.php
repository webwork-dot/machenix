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

  // Get all products for this PO with loading list data
  $products_raw = $this->db->query("
      SELECT 
          pop.*, 
          s.name AS supplier_name,
          inv_s.name AS invoice_supplier_name
      FROM po_products pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      LEFT JOIN supplier inv_s ON inv_s.id = pop.invoice_supplier_id
      WHERE pop.parent_id = '$po_id'
      ORDER BY pop.id ASC
  ")->result_array();

  // Get loading_product_total data for variations
  $loading_totals = $this->db->query("SELECT * FROM loading_product_total WHERE po_id = '$po_id' ORDER BY parent_id ASC, id ASC")->result_array();
  $loading_totals_by_parent = [];
  foreach ($loading_totals as $lt) {
      $parent_id = $lt['parent_id'];
      if (!isset($loading_totals_by_parent[$parent_id])) {
          $loading_totals_by_parent[$parent_id] = [];
      }
      $loading_totals_by_parent[$parent_id][] = $lt;
  }

  // Group products by supplier
  $supplier_products = [];
  foreach ($products_raw as $product) {
      $supplier_id = $product['supplier_id'] ?? 0;
      if (!isset($supplier_products[$supplier_id])) {
          $supplier_products[$supplier_id] = [
              'supplier_name' => $product['supplier_name'] ?? 'Unknown Supplier',
              'products' => []
          ];
      }
      $supplier_products[$supplier_id]['products'][] = $product;
  }

  // Calculate grand totals
  $grand_total_loading_qty = 0;
  $grand_total_official_ci_qty = 0;
  $grand_total_black_qty = 0;
  $grand_total_amount_rmb = 0;
  $grand_total_amount_usd = 0;
  $grand_total_black_price = 0;
  $grand_total_nw = 0;
  $grand_total_gw = 0;
  $grand_total_cbm = 0;
  
  foreach ($products_raw as $product) {
      $grand_total_loading_qty += floatval($product['loading_qty'] ?? 0);
      $grand_total_official_ci_qty += floatval($product['official_ci_qty'] ?? 0);
      $grand_total_black_qty += floatval($product['black_qty'] ?? 0);
      $grand_total_amount_rmb += floatval($product['total_amount_rmb'] ?? 0);
      $grand_total_amount_usd += floatval($product['total_amount_usd'] ?? 0);
      $grand_total_black_price += floatval($product['black_total_price'] ?? 0);
      $grand_total_nw += floatval($product['total_nw_kg'] ?? 0);
      $grand_total_gw += floatval($product['total_gw_kg'] ?? 0);
      $grand_total_cbm += floatval($product['total_cbm_value'] ?? 0);
  }
?>

<style>
  .loading-list-view-modal {
    max-width: 1800px !important;
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
    background: #ffffff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
  }

  .supplier-section h5 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #5a79c0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .supplier-section h5:before {
    content: "🏭";
    font-size: 1.2rem;
  }
  
  .table-responsive {
    max-height: 600px;
    overflow-x: auto;
    overflow-y: auto;
    border-radius: 6px;
    border: 1px solid #dee2e6;
  }
  
  .loading-view-table {
    min-width: 2600px;
    width: 100%;
    margin-bottom: 0;
  }
  
  .loading-view-table thead th {
    background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
    color: #ffffff;
    font-weight: 600;
    text-align: center;
    border: none;
    padding: 10px 8px;
    font-size: 0.9rem;
    position: sticky;
    top: 0;
    z-index: 10;
    white-space: nowrap;
  }
  
  .loading-view-table tbody td {
    padding: 8px 10px;
    white-space: nowrap;
    border-right: 1px solid #e9ecef;
    vertical-align: middle;
  }

  .loading-view-table tbody td:last-child {
    border-right: none;
  }

  .loading-view-table tbody tr {
    border-bottom: 1px solid #e9ecef;
  }

  .loading-view-table tbody tr.variation-row {
    background-color: #fafbfc;
  }

  .loading-view-table tbody tr:hover {
    background-color: #f8f9fa;
  }
  
  .totals-row {
    background-color: #f8f9fa;
    font-weight: bold;
  }

  .invoice-cell {
    padding: 8px 5px !important;
    vertical-align: top;
  }

  .invoice-number {
    display: inline-block;
    background: #28a745;
    color: white;
    padding: 5px 12px;
    border-radius: 5px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 6px;
    white-space: nowrap;
  }

  .invoice-supplier-name {
    display: block;
    color: #495057;
    font-size: 0.8rem;
    line-height: 1.4;
    margin-top: 4px;
    word-wrap: break-word;
    white-space: normal;
  }

  .grand-totals-section {
    background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-top: 30px;
  }

  .grand-totals-section h4 {
    color: white;
    margin-bottom: 15px;
    font-weight: bold;
  }

  .grand-totals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
  }

  .grand-total-item {
    background: rgba(255, 255, 255, 0.1);
    padding: 12px;
    border-radius: 6px;
  }

  .grand-total-label {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-bottom: 5px;
  }

  .grand-total-value {
    font-size: 1.2rem;
    font-weight: bold;
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
    </div>
    
    <!-- Loading List by Supplier -->
    <?php if (!empty($supplier_products)): ?>
        <?php foreach ($supplier_products as $supplier_id => $supplier_data): ?>
            <div class="supplier-section">
                <h5>
                    Supplier: <?php echo htmlspecialchars($supplier_data['supplier_name']); ?>
                </h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped loading-view-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Sr No.</th>
                                <th style="width: 100px;">Invoice</th>
                                <th style="width: 150px;">Model No.</th>
                                <th style="width: 200px;">Product Name</th>
                                <th style="width: 100px;">Priority List <br> (Qty)</th>
                                <th style="width: 100px;">Loading Qty <br> (PCS)</th>
                                <th style="width: 80px;">Official CI <br> Qty</th>
                                <th style="width: 80px;">Black Qty</th>
                                <th style="width: 100px;">Unit Price <br> (RMB)</th>
                                <th style="width: 110px;">Total Amount <br> (RMB)</th>
                                <th style="width: 130px;">Official CI <br>Unit Price (USD)</th>
                                <th style="width: 110px;">Total Amount <br> (USD)</th>
                                <th style="width: 100px;">Black Total <br> Price</th>
                                <th style="width: 80px;">PKG <br> (ctn)</th>
                                <th style="width: 80px;">N.W. <br> (kg)</th>
                                <th style="width: 100px;">Total N.W.</th>
                                <th style="width: 80px;">G.W. <br> (kg)</th>
                                <th style="width: 100px;">Total G.W.</th>
                                <th style="width: 70px;">L</th>
                                <th style="width: 70px;">W</th>
                                <th style="width: 70px;">H</th>
                                <th style="width: 110px;">Total CBM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sr_no = 1;
                            foreach ($supplier_data['products'] as $product): 
                                $lt_data = $loading_totals_by_parent[$product['id']] ?? [];
                                $rowspan = max(1, count($lt_data));
                            ?>
                            <tr class="main-product-row">
                                <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo $sr_no++; ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="invoice-cell">
                                    <?php if (!empty($product['invoice_no'])): ?>
                                        <div class="invoice-number">Invoice #<?php echo $product['invoice_no']; ?></div>
                                        <?php if (!empty($product['invoice_supplier_name'])): ?>
                                            <div class="invoice-supplier-name"><?php echo htmlspecialchars($product['invoice_supplier_name']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($product['item_code'] ?? 'N/A'); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($product['product_name'] ?? 'N/A'); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo number_format($product['quantity'] ?? 0, 0); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo number_format($product['loading_qty'] ?? 0, 0); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo number_format($product['official_ci_qty'] ?? 0, 0); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo number_format($product['black_qty'] ?? 0, 0); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-right"><?php echo number_format($product['unit_price_rmb'] ?? 0, 2); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-right"><?php echo number_format($product['total_amount_rmb'] ?? 0, 2); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-right"><?php echo number_format($product['official_ci_unit_price_usd'] ?? 0, 2); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-right"><?php echo number_format($product['total_amount_usd'] ?? 0, 2); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-right"><?php echo number_format($product['black_total_price'] ?? 0, 2); ?></td>
                                <!-- <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo number_format($product['pkg_ctn'] ?? 0, 0); ?></td> -->
                                
                                <?php
                                // First variation or main product metrics
                                $first_lt = !empty($lt_data) ? $lt_data[0] : null;
                                ?>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['pkg_ctn'] : 1); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['nw_kg'] : ($product['nw_kg'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['total_nw_kg'] : ($product['total_nw_kg'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['gw_kg'] : ($product['gw_kg'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['total_gw_kg'] : ($product['total_gw_kg'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['length'] : ($product['length'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['width'] : ($product['width'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['height'] : ($product['height'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['total_cbm_value'] : ($product['total_cbm_value'] ?? 0), 6); ?></td>
                            </tr>

                            <?php if (count($lt_data) > 1): ?>
                                <?php foreach (array_slice($lt_data, 1) as $lt): ?>
                                    <tr class="variation-row">
                                        <td class="text-right"><?php echo number_format($lt['pkg_ctn'], 0); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['nw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['total_nw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['gw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['total_gw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['length'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['width'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['height'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['total_cbm_value'], 6); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <?php
                            // Calculate supplier totals
                            $supplier_total_priority_qty = 0;
                            $supplier_total_loading_qty = 0;
                            $supplier_total_official_ci_qty = 0;
                            $supplier_total_black_qty = 0;
                            $supplier_total_unit_price_rmb = 0;
                            $supplier_total_amount_rmb = 0;
                            $supplier_total_official_ci_unit_price_usd = 0;
                            $supplier_total_amount_usd = 0;
                            $supplier_total_black_total_price = 0;
                            $supplier_total_pkg_ctn = 0;
                            $supplier_total_nw_kg = 0;
                            $supplier_total_total_nw = 0;
                            $supplier_total_gw_kg = 0;
                            $supplier_total_total_gw = 0;
                            $supplier_total_length = 0;
                            $supplier_total_width = 0;
                            $supplier_total_height = 0;
                            $supplier_total_total_cbm = 0;
                            
                            foreach ($supplier_data['products'] as $product):
                                $supplier_total_priority_qty += floatval($product['quantity'] ?? 0);
                                $supplier_total_loading_qty += floatval($product['loading_qty'] ?? 0);
                                $supplier_total_official_ci_qty += floatval($product['official_ci_qty'] ?? 0);
                                $supplier_total_black_qty += floatval($product['black_qty'] ?? 0);
                                $supplier_total_unit_price_rmb += floatval($product['unit_price_rmb'] ?? 0);
                                $supplier_total_amount_rmb += floatval($product['total_amount_rmb'] ?? 0);
                                $supplier_total_official_ci_unit_price_usd += floatval($product['official_ci_unit_price_usd'] ?? 0);
                                $supplier_total_amount_usd += floatval($product['total_amount_usd'] ?? 0);
                                $supplier_total_black_total_price += floatval($product['black_total_price'] ?? 0);
                                
                                
                                // Sum all variation rows for metrics
                                $lt_data = $loading_totals_by_parent[$product['id']] ?? [];
                                if (!empty($lt_data)) {
                                    foreach ($lt_data as $lt) {
                                        $supplier_total_pkg_ctn += floatval($lt['pkg_ctn'] ?? 0);
                                        $supplier_total_nw_kg += floatval($lt['nw_kg'] ?? 0);
                                        $supplier_total_total_nw += floatval($lt['total_nw_kg'] ?? 0);
                                        $supplier_total_gw_kg += floatval($lt['gw_kg'] ?? 0);
                                        $supplier_total_total_gw += floatval($lt['total_gw_kg'] ?? 0);
                                        $supplier_total_length += floatval($lt['length'] ?? 0);
                                        $supplier_total_width += floatval($lt['width'] ?? 0);
                                        $supplier_total_height += floatval($lt['height'] ?? 0);
                                        $supplier_total_total_cbm += floatval($lt['total_cbm_value'] ?? 0);
                                    }
                                } else {
                                    // Fallback to product values if no variations
                                    $supplier_total_pkg_ctn += floatval($product['loading_qty'] ?? 0);
                                    $supplier_total_nw_kg += floatval($product['nw_kg'] ?? 0);
                                    $supplier_total_total_nw += floatval($product['total_nw_kg'] ?? 0);
                                    $supplier_total_gw_kg += floatval($product['gw_kg'] ?? 0);
                                    $supplier_total_total_gw += floatval($product['total_gw_kg'] ?? 0);
                                    $supplier_total_length += floatval($product['length'] ?? 0);
                                    $supplier_total_width += floatval($product['width'] ?? 0);
                                    $supplier_total_height += floatval($product['height'] ?? 0);
                                    $supplier_total_total_cbm += floatval($product['total_cbm_value'] ?? 0);
                                }
                            endforeach;
                            ?>
                            <!-- Supplier Total Row -->
                            <tr class="totals-row" style="background-color: #fafafc; font-weight: bold;">
                                <td colspan="4" style="text-align: right; padding: 10px;"><strong>Total:</strong></td>
                                <td class="text-center"><?php echo number_format($supplier_total_priority_qty, 0); ?></td>
                                <td class="text-center"><?php echo number_format($supplier_total_loading_qty, 0); ?></td>
                                <td class="text-center"><?php echo number_format($supplier_total_official_ci_qty, 0); ?></td>
                                <td class="text-center"><?php echo number_format($supplier_total_black_qty, 0); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_unit_price_rmb, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_amount_rmb, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_official_ci_unit_price_usd, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_amount_usd, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_black_total_price, 2); ?></td>
                                <td class="text-center"><?php echo number_format($supplier_total_pkg_ctn, 0); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_nw_kg, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_total_nw, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_gw_kg, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_total_gw, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_length, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_width, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_height, 2); ?></td>
                                <td class="text-right"><?php echo number_format($supplier_total_total_cbm, 6); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No loading list data found for this Purchase Order.</div>
    <?php endif; ?>

    <!-- Grand Totals Section -->
    <?php if (!empty($products_raw)): ?>
    <div class="grand-totals-section">
        <h4><i class="fa fa-calculator"></i> Grand Totals</h4>
        <div class="grand-totals-grid">
            <div class="grand-total-item">
                <div class="grand-total-label">Total Loading Qty</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_loading_qty, 0); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total Official CI Qty</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_official_ci_qty, 0); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total Black Qty</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_black_qty, 0); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total Amount (RMB)</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_amount_rmb, 2); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total Amount (USD)</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_amount_usd, 2); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total Black Price</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_black_price, 2); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total N.W. (kg)</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_nw, 2); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total G.W. (kg)</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_gw, 2); ?></div>
            </div>
            <div class="grand-total-item">
                <div class="grand-total-label">Total CBM</div>
                <div class="grand-total-value"><?php echo number_format($grand_total_cbm, 6); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

  </div>
</div>

