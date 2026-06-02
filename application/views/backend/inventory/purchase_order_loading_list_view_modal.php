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
      FROM loading_po_product pop
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

  // Group products by invoice number for the Supplier Invoice section
  $invoices = [];
  foreach ($products_raw as $product) {
      if (!empty($product['invoice_no'])) {
          $invoice_no = $product['invoice_no'];
          if (!isset($invoices[$invoice_no])) {
              $invoices[$invoice_no] = [
                  'invoice_no' => $invoice_no,
                  'supplier_name' => $product['invoice_supplier_name'] ?? 'N/A',
                  'invoice_info' => $product['invoice'] ?? '',
                  'invoice_date' => $product['invoice_date'] ?? '',
                  'invoice_terms' => $product['invoice_terms'] ?? '',
                  'invoice_price_terms' => $product['invoice_price_terms'] ?? '',
                  'product_count' => 0
              ];
          }
          $invoices[$invoice_no]['product_count']++;
      }
  }
  ksort($invoices);
?>

<style>
  .loading-list-view-modal {
    max-width: 1800px !important;
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
    margin-bottom: 4px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 2px;
  }
  
  .table-responsive {
    max-height: 450px;
    overflow-x: auto;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 4px;
  }
  
  .compact-table {
    min-width: 2600px;
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
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
    text-align: center;
    font-size: 11px;
  }
  
  .compact-table td {
    padding: 3px 6px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
    font-size: 11px;
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

  .variation-row-bg {
    background-color: #fafbfc;
  }
  
  .totals-row-bg {
    background-color: #f1f3f5 !important;
    font-weight: bold;
  }

  .invoice-cell-badge {
    background-color: #198754;
    color: white;
    padding: 0px 3px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 4px;
    display: inline-block;
  }

  .invoice-supplier-view-header {
    font-weight: bold;
    font-size: 13px;
    color: #5a79c0;
    margin-top: 15px;
    margin-bottom: 8px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 2px;
  }

  .invoice-card {
    background: #ffffff;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 10px;
    border: 1px solid #eef0f2;
    font-size: 11px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .invoice-card-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    border-bottom: 1px solid #f1f3f5;
    padding-bottom: 4px;
    font-weight: bold;
  }

  .invoice-card-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }

  .invoice-card-item {
    margin-bottom: 4px;
  }

  .invoice-card-label {
    font-size: 8px;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 700;
    margin-bottom: 1px;
  }

  .invoice-card-value {
    color: #1e293b;
    font-weight: 600;
  }

  .invoice-card-full {
    grid-column: span 2;
    background: #f8fafc;
    padding: 6px 8px;
    border-radius: 4px;
    border: 1px solid #f1f5f9;
  }

  .grand-totals-bar {
    background-color: #f8f9fa;
    border-top: 2px solid #5a79c0;
    margin-top: 15px;
    padding-top: 10px;
  }

  .grand-totals-bar .totals-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    text-align: center;
    font-size: 11px;
    gap: 10px;
  }

  .grand-totals-bar .total-block {
    border-right: 1px solid #dee2e6;
    padding: 2px 5px;
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
    color: #5a79c0;
  }
</style>

<div class="row">
  <div class="col-12">
    
    <!-- PO Header Information -->
    <div class="po-meta-container">
      <div class="d-flex flex-wrap">
        <div class="po-meta-item"><strong>Batch No:</strong> <?php echo $po_data['voucher_no']; ?></div>
        <div class="po-meta-item"><strong>Order Date:</strong> <?php echo date('d M, Y', strtotime($po_data['date'])); ?></div>
        <div class="po-meta-item"><strong>Loading Date:</strong> <?php echo !empty($po_data['expected_date']) ? date('d M, Y', strtotime($po_data['expected_date'])) : 'N/A'; ?></div>
        <div class="po-meta-item"><strong>Expected Arrival Date:</strong> <?php echo !empty($po_data['arrival_date']) ? date('d M, Y', strtotime($po_data['arrival_date'])) : 'N/A'; ?></div>
        <div class="po-meta-item"><strong>Warehouse:</strong> <?php echo $warehouse['name'] ?? 'N/A'; ?></div>
        <?php if (!empty($po_data['mode_of_payment'])) { ?>
          <div class="po-meta-item"><strong>Payment Mode/Terms:</strong> <?php echo $po_data['mode_of_payment']; ?></div>
        <?php } ?>
        <?php if (!empty($po_data['dispatch'])) { ?>
          <div class="po-meta-item"><strong>Dispatch Through:</strong> <?php echo $po_data['dispatch']; ?></div>
        <?php } ?>
        <div class="po-meta-item w-100 mt-1"><strong>Delivery Address:</strong> <?php echo $po_data['delivery_address'] ?? 'N/A'; ?></div>
      </div>
    </div>
    
    <!-- Loading List by Supplier -->
    <?php if (!empty($supplier_products)): ?>
        <?php foreach ($supplier_products as $supplier_id => $supplier_data): ?>
            <div>
                <div class="supplier-header">
                    Supplier: <?php echo htmlspecialchars($supplier_data['supplier_name']); ?>
                </div>
                <div class="table-responsive">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Sr No.</th>
                                <th style="width: 70px;">Invoice No.</th>
                                <!-- <th style="width: 200px;">Invoice Supplier.</th> -->
                                <th style="width: 150px;">Model No.</th>
                                <th style="width: 200px;">Product Name</th>
                                <th style="width: 60px;">Priority List (Qty)</th>
                                <th style="width: 60px;">Loading Qty (PCS)</th>
                                <th style="width: 60px;">Official CI Qty</th>
                                <th style="width: 60px;">Black Qty</th>
                                <th style="width: 100px;">Unit Price (RMB)</th>
                                <th style="width: 110px;">Total Amount (RMB)</th>
                                <th style="width: 130px;">Official CI Unit Price (USD)</th>
                                <th style="width: 110px;">Total Amount (USD)</th>
                                <th style="width: 100px;">Black Total Price</th>
                                <th style="width: 80px;">PKG (ctn)</th>
                                <th style="width: 80px;">N.W. (kg)</th>
                                <th style="width: 100px;">Total N.W.</th>
                                <th style="width: 80px;">G.W. (kg)</th>
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
                            <tr>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-center"><?php echo $sr_no++; ?></td>
                                <td rowspan="<?php echo $rowspan; ?>" class="text-center">
                                    <?php if (!empty($product['invoice_no'])): ?>
                                        <div class="invoice-cell-badge">Invoice #<?php echo $product['invoice_no']; ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <!-- <td rowspan="<?php echo $rowspan; ?>" class="text-center">
                                    <?php if (!empty($product['invoice_no'])): ?>
                                        <?php if (!empty($product['invoice_supplier_name'])): ?>
                                            <div style="font-size: 9px; color: #495057; line-height: 1.2;"><?php echo htmlspecialchars($product['invoice_supplier_name']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td> -->
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
                                <td class="text-right"><?php echo number_format($first_lt ? $first_lt['total_cbm_value'] : ($product['total_cbm_value'] ?? 0), 2); ?></td>
                            </tr>

                            <?php if (count($lt_data) > 1): ?>
                                <?php foreach (array_slice($lt_data, 1) as $lt): ?>
                                    <tr class="variation-row-bg">
                                        <td class="text-right"><?php echo number_format($lt['pkg_ctn'], 0); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['nw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['total_nw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['gw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['total_gw_kg'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['length'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['width'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['height'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format($lt['total_cbm_value'], 2); ?></td>
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
                            $supplier_total_amount_rmb = 0;
                            $supplier_total_amount_usd = 0;
                            $supplier_total_pkg_ctn = 0;
                            $supplier_total_total_nw = 0;
                            $supplier_total_total_gw = 0;
                            $supplier_total_total_cbm = 0;
                            
                            foreach ($supplier_data['products'] as $product):
                                $supplier_total_priority_qty += floatval($product['quantity'] ?? 0);
                                $supplier_total_loading_qty += floatval($product['loading_qty'] ?? 0);
                                $supplier_total_official_ci_qty += floatval($product['official_ci_qty'] ?? 0);
                                $supplier_total_black_qty += floatval($product['black_qty'] ?? 0);
                                $supplier_total_amount_rmb += floatval($product['total_amount_rmb'] ?? 0);
                                $supplier_total_amount_usd += floatval($product['total_amount_usd'] ?? 0);
                                
                                $lt_data = $loading_totals_by_parent[$product['id']] ?? [];
                                if (!empty($lt_data)) {
                                    foreach ($lt_data as $lt) {
                                        $supplier_total_pkg_ctn += floatval($lt['pkg_ctn'] ?? 0);
                                        $supplier_total_total_nw += floatval($lt['total_nw_kg'] ?? 0);
                                        $supplier_total_total_gw += floatval($lt['total_gw_kg'] ?? 0);
                                        $supplier_total_total_cbm += floatval($lt['total_cbm_value'] ?? 0);
                                    }
                                } else {
                                    $supplier_total_pkg_ctn += floatval($product['loading_qty'] ?? 0);
                                    $supplier_total_total_nw += floatval($product['total_nw_kg'] ?? 0);
                                    $supplier_total_total_gw += floatval($product['total_gw_kg'] ?? 0);
                                    $supplier_total_total_cbm += floatval($product['total_cbm_value'] ?? 0);
                                }
                            endforeach;
                            ?>
                            <tr class="totals-row-bg">
                                <td colspan="4" class="text-right">Total:</td>
                                <td class="text-center"><?php echo number_format($supplier_total_priority_qty, 0); ?></td>
                                <td class="text-center"><?php echo number_format($supplier_total_loading_qty, 0); ?></td>
                                <td class="text-center"><?php echo number_format($supplier_total_official_ci_qty, 0); ?></td>
                                <td class="text-center"><?php echo number_format($supplier_total_black_qty, 0); ?></td>
                                <td class="text-right"></td>
                                <td class="text-right"><?php echo number_format($supplier_total_amount_rmb, 2); ?></td>
                                <td class="text-right"></td>
                                <td class="text-right"><?php echo number_format($supplier_total_amount_usd, 2); ?></td>
                                <td class="text-right"></td>
                                <td class="text-center"><?php echo number_format($supplier_total_pkg_ctn, 0); ?></td>
                                <td class="text-right"></td>
                                <td class="text-right"><?php echo number_format($supplier_total_total_nw, 2); ?></td>
                                <td class="text-right"></td>
                                <td class="text-right"><?php echo number_format($supplier_total_total_gw, 2); ?></td>
                                <td colspan="3"></td>
                                <td class="text-right"><?php echo number_format($supplier_total_total_cbm, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No loading list data found for this Purchase Order.</div>
    <?php endif; ?>

    <!-- Supplier Invoices Section -->
    <?php if (!empty($invoices)): ?>
    <div>
        <div class="invoice-supplier-view-header">
             <i class="fa fa-file"></i> Supplier Invoices (<?php echo count($invoices); ?>)
        </div>
        
        <div class="row">
            <?php foreach ($invoices as $inv): ?>
            <div class="col-md-6 col-lg-3">
                <div class="invoice-card">
                    <div class="invoice-card-header">
                        <span>Invoice #<?php echo $inv['invoice_no']; ?></span>
                        <span class="badge badge-light" style="color: #4a6ba8;"><?php echo $inv['product_count']; ?> <?php echo $inv['product_count'] == 1 ? 'Item' : 'Items'; ?></span>
                    </div>
                    
                    <div class="invoice-card-grid">
                        <div class="invoice-card-item">
                            <div class="invoice-card-label">Supplier</div>
                            <div class="invoice-card-value"><?php echo htmlspecialchars($inv['supplier_name']); ?></div>
                        </div>
                        
                        <div class="invoice-card-item">
                            <div class="invoice-card-label">Date</div>
                            <div class="invoice-card-value">
                                <?php echo !empty($inv['invoice_date']) ? date('d M, Y', strtotime($inv['invoice_date'])) : 'N/A'; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($inv['invoice_price_terms'])): ?>
                        <div class="invoice-card-item">
                            <div class="invoice-card-label">Terms of Price</div>
                            <div class="invoice-card-value"><?php echo htmlspecialchars($inv['invoice_price_terms']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($inv['invoice_info'])): ?>
                        <div class="invoice-card-item">
                            <div class="invoice-card-label">Invoice Info</div>
                            <div class="invoice-card-value"><?php echo htmlspecialchars($inv['invoice_info']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($inv['invoice_terms'])): ?>
                        <div class="invoice-card-item invoice-card-full">
                            <div class="invoice-card-label">Terms of Payment</div>
                            <div class="invoice-card-value" style="white-space: pre-wrap; font-size: 10px; line-height: 1.3; color: #475569;"><?php echo htmlspecialchars($inv['invoice_terms']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Grand Totals Section -->
    <?php if (!empty($products_raw)): ?>
    <div class="grand-totals-bar">
        <div class="totals-grid">
            <div class="total-block">
                <div class="total-title">Loading Qty</div>
                <div class="total-value"><?php echo number_format($grand_total_loading_qty, 0); ?></div>
            </div>
            <div class="total-block">
                <div class="total-title">Official CI Qty</div>
                <div class="total-value"><?php echo number_format($grand_total_official_ci_qty, 0); ?></div>
            </div>
            <div class="total-block">
                <div class="total-title">Black Qty</div>
                <div class="total-value"><?php echo number_format($grand_total_black_qty, 0); ?></div>
            </div>
            <div class="total-block">
                <div class="total-title">Amt (RMB)</div>
                <div class="total-value"><?php echo number_format($grand_total_amount_rmb, 2); ?></div>
            </div>
            <div class="total-block">
                <div class="total-title">Amt (USD)</div>
                <div class="total-value"><?php echo number_format($grand_total_amount_usd, 2); ?></div>
            </div>
            <div class="total-block">
                <div class="total-title">N.W. (kg)</div>
                <div class="total-value"><?php echo number_format($grand_total_nw, 2); ?></div>
            </div>
            <div class="total-block">
                <div class="total-title">G.W. (kg)</div>
                <div class="total-value"><?php echo number_format($grand_total_gw, 2); ?></div>
            </div>
            <div class="total-block">
                <div class="total-title">Total CBM</div>
                <div class="total-value"><?php echo number_format($grand_total_cbm, 2); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

  </div>
</div>
