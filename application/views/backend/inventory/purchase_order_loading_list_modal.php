<style>
    .inner-modal {
        background: rgba(0, 0, 0, 0.25);
    }

    .full-width-modal {
        max-width: 1800px !important;
    }

    input:read-only {
        background-color: #eee;
        border: 1px solid #ddd;
    }

    input {
        padding: 2px 5px;
    }

    .table-responsive {
        max-height: 600px;
        overflow-x: auto;
        overflow-y: auto;
        width: 100%;
        display: block;
        -webkit-overflow-scrolling: touch;
    }

    .loading-table {
        min-width: 2600px;
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        padding: 0 !important;
    }

    .loading-table th {
        white-space: nowrap;
        padding: 8px 10px;
    }

    .loading-table td {
        white-space: nowrap;
        padding: 8px 10px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Fixed width for variation/metrics columns (N.W., G.W., L, W, H, CBM etc.) */
    .loading-table td.metric-cell {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
    }

    /* Enforce column widths for quantity columns */
    .loading-table td:nth-child(4),
    .loading-table td:nth-child(5),
    .loading-table td:nth-child(6),
    .loading-table td:nth-child(7) {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
    }

    .loading-table input {
        width: 100%;
        box-sizing: border-box;
    }

    /* Text columns - allow wider inputs */
    .loading-table td:nth-child(2) input,
    .loading-table td:nth-child(3) input {
        /* min-width: 120px; */
    }

    /* Numeric columns - keep compact */
    .loading-table td:nth-child(n+4) input {
        min-width: 60px;
        max-width: 100%;
    }

    /* Quantity columns - enforce width */
    .loading-table td:nth-child(4) input,
    .loading-table td:nth-child(5) input,
    .loading-table td:nth-child(6) input,
    .loading-table td:nth-child(7) input {
        width: 100%;
        box-sizing: border-box;
    }

    /* Invoice Supplier Section Styles */
    #invoice_supplier_section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin: 20px 18px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .invoice-supplier-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #5a79c0;
    }

    .invoice-supplier-header h5 {
        margin: 0;
        color: #212529;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .invoice-supplier-header .badge {
        background: #5a79c0;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .invoice-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .invoice-card:hover {
        border-color: #5a79c0;
        box-shadow: 0 4px 12px rgba(90, 121, 192, 0.15);
        transform: translateY(-2px);
    }

    .invoice-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e9ecef;
    }

    .invoice-number-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 2px 6px rgba(90, 121, 192, 0.3);
    }

    .invoice-number-badge i {
        font-size: 1.1rem;
    }

    .invoice-product-count {
        background: #f8f9fa;
        color: #6c757d;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        border: 1px solid #dee2e6;
    }

    .invoice-supplier-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .invoice-supplier-label .required {
        color: #dc3545;
        font-weight: bold;
    }

    .invoice-supplier-select {
        border: 2px solid #ced4da;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: white;
    }

    .invoice-supplier-select:focus {
        border-color: #5a79c0;
        box-shadow: 0 0 0 0.2rem rgba(90, 121, 192, 0.25);
        outline: none;
    }

    .invoice-supplier-select option {
        padding: 10px;
    }

    .invoice-field-group {
        margin-bottom: 15px;
    }

    .invoice-field-group:last-child {
        margin-bottom: 0;
    }

    .invoice-field-input,
    .invoice-field-textarea {
        border: 2px solid #ced4da;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: white;
        width: 100%;
    }

    .invoice-field-input:focus,
    .invoice-field-textarea:focus {
        border-color: #5a79c0;
        box-shadow: 0 0 0 0.2rem rgba(90, 121, 192, 0.25);
        outline: none;
    }

    .invoice-field-textarea {
        min-height: 80px;
        resize: vertical;
    }

    .no-invoice-selected {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .no-invoice-selected i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .invoice-card {
            margin-bottom: 15px;
        }
    }

    /* Supplier Table Section Improvements */
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

    .supplier-header-actions {
        margin-left: auto;
        display: flex;
        align-items: center;
    }

    .supplier-reload-btn {
        padding: 2px 10px;
        font-size: 12px;
        border-radius: 4px;
    }

    /* Hide emoji icon for Grand Total section */
    .grand-total-section h5:before {
        content: none !important;
        display: none !important;
    }

    .loading-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .loading-table thead th {
        background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
        color: #ffffff;
        font-weight: 600;
        text-align: center;
        border: none;
        padding: 3px 5px;
        font-size: 0.9rem;
        letter-spacing: 0.3px;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .loading-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #e9ecef;
    }

    .loading-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.001);
        box-shadow: 0 2px 4px rgba(90, 121, 192, 0.08);
    }

    .loading-table tbody tr.main-product-row {
        background-color: #ffffff;
    }

    .loading-table tbody tr.variation-row {
        background-color: #fafbfc;
    }

    .loading-table tbody tr.variation-row:hover {
        background-color: #f0f2f5;
    }

    .loading-table tbody td {
        border-right: 1px solid #e9ecef;
        vertical-align: middle;
    }

    .loading-table tbody td:last-child {
        border-right: none;
    }

    .loading-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Prevent text overflow ellipsis on total rows */
    .loading-table tbody tr.supplier-total-row td,
    .loading-table tbody tr#grand-total-row td {
        overflow: visible !important;
        text-overflow: clip !important;
        white-space: nowrap;
    }

    .table-responsive {
        border-radius: 6px;
        border: 1px solid #dee2e6;
        overflow-x: auto;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Input field improvements */
    .loading-table input.form-control-sm,
    .loading-table select.form-control-sm {
        border: 1px solid #ced4da;
        transition: all 0.2s ease;
    }

    .loading-table input.form-control-sm:focus,
    .loading-table select.form-control-sm:focus {
        border-color: #5a79c0;
        box-shadow: 0 0 0 0.15rem rgba(90, 121, 192, 0.15);
        outline: none;
    }

    .loading-table input[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    /* Metric cells styling */
    .loading-table td.metric-cell {
        background-color: #fafbfc;
    }

    .loading-table tbody tr:hover td.metric-cell {
        background-color: #f0f2f5;
    }
</style>

<?php
   $company_id = $this->session->userdata('company_id');

  // Get PO ID from param2
  $po_id = $param2;

  // Get PO details
  $po_data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();

  // Get all products for this PO with raw_products, supplier, and product_variation details
  $products_raw = $this->db->query("
      SELECT 
          pop.*, 
          rp.rate as rate_rmb,
          rp.usd_rate,
          rp.cartoon_qty,
          rp.net_weight,
          rp.gross_weight,
          rp.length,
          rp.width,
          rp.height,
          s.name AS supplier_name,
          pv.id AS variation_id,
          pv.cartoon_qty AS variation_cartoon_qty,
          pv.net_weight AS variation_net_weight,
          pv.gross_weight AS variation_gross_weight,
          pv.length AS variation_length,
          pv.width AS variation_width,
          pv.height AS variation_height
      FROM po_products pop
      LEFT JOIN raw_products rp ON rp.id = pop.product_id
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      LEFT JOIN product_variation pv ON pv.product_id = pop.product_id
      WHERE pop.parent_id = '$po_id'
      ORDER BY pop.id ASC, pv.id ASC
  ")->result_array();

  // Group raw rows by po_products.id and collect variations
  $products_by_id = [];
  foreach ($products_raw as $row) {
      $pop_id = $row['id'];
      if (!isset($products_by_id[$pop_id])) {
          $products_by_id[$pop_id] = [
              'product'    => $row,
              'variations' => []
          ];
      }

      if (!empty($row['variation_id'])) {
          $products_by_id[$pop_id]['variations'][] = [
              'id'           => $row['variation_id'],
              'cartoon_qty'  => $row['variation_cartoon_qty'],
              'net_weight'   => $row['variation_net_weight'],
              'gross_weight' => $row['variation_gross_weight'],
              'length'       => $row['variation_length'],
              'width'        => $row['variation_width'],
              'height'       => $row['variation_height'],
          ];
      }
  }

  // Collect all suppliers that appear in this PO (priority or non-priority)
  $priority_supplier_ids = [];
  foreach ($products_by_id as $data) {
      $product = $data['product'];
      if (!empty($product['supplier_id'])) {
          $priority_supplier_ids[$product['supplier_id']] = $product['supplier_name'] ?? 'Unknown Supplier';
      }
  }

  // Group products (with their variations) by supplier (include is_priority = 1 and 0)
  $supplier_products = [];
  foreach ($products_by_id as $pop_id => $data) {
      $product = $data['product'];
      $sid = $product['supplier_id'] ?? 0;
      if (!isset($priority_supplier_ids[$sid])) {
          continue;
      }
      if (!isset($supplier_products[$sid])) {
          $supplier_products[$sid] = [];
      }
      $supplier_products[$sid][$pop_id] = $data;
  }

  // Flat array used only to decide if we show Submit button
  $products_query = $products_raw;

  // Get supplier list for invoice dropdowns
  $supplier_list = $this->db->query("SELECT * FROM supplier WHERE is_deleted = '0' AND company_id = '$company_id' ORDER BY name ASC")->result_array();
?>

<?php echo form_open('inventory/update_purchase_order_loading_list', ['class' => 'priority-list-form', 'onsubmit' => 'return checkForm(this);']); ?>
<input type="hidden" name="po_id" value="<?php echo $po_id; ?>">
    <div class="row mt-2">
        <div class="col-md-12">
            <?php if (!empty($supplier_products)): ?>
                <?php foreach ($supplier_products as $supplier_id => $products): ?>
                    <div class="supplier-section" data-supplier-id="<?php echo $supplier_id; ?>">
                        <h5>
                            Supplier: <?php echo htmlspecialchars($priority_supplier_ids[$supplier_id] ?? 'Unknown Supplier'); ?>
                            <span class="supplier-header-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm supplier-reload-btn" data-supp-id="<?php echo $supplier_id; ?>" onclick="reloadSupplierProducts(this)">
                                    <i class="fa fa-refresh"></i> Add Product
                                </button>
                            </span>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped loading-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Sr No.</th>
                                        <th style="width: 100px;">Invoice</th>
                                        <th style="width: 150px;">Model No.</th>
                                        <th style="width: 200px;">Product Name</th>
                                        <th style="width: 100px;">Priority List <br> (Qty)</th>
                                        <th style="width: 100px;">Loading Qty <br> (PCS)</th>
                                        <th style="width: 100px;">Unit Price <br> (RMB)</th>
                                        <th style="width: 80px;">Official CI <br> Qty</th>
                                        <th style="width: 80px;">Black Qty</th>
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
                                <tbody class="loading_list_tbody">
                                    <?php 
                                    $sr_no = 1;
                                    foreach ($products as $pop_id => $data): 
                                        $product    = $data['product'];
                                        $variations = $data['variations'];

                                        // Base values from product master
                                        $usd_rate    = isset($product['usd_rate']) ? $product['usd_rate'] : 0;
                                        $cartoon_qty = isset($product['cartoon_qty']) ? $product['cartoon_qty'] : 0;

                                        // Use first variation values if available, otherwise fall back to master
                                        if (!empty($variations)) {
                                            $firstVar   = $variations[0];
                                            $net_weight = $firstVar['net_weight'];
                                            $gross_weight = $firstVar['gross_weight'];
                                            $length     = $firstVar['length'];
                                            $width      = $firstVar['width'];
                                            $height     = $firstVar['height'];
                                        } else {
                                            $net_weight   = isset($product['net_weight']) ? $product['net_weight'] : 0;
                                            $gross_weight = isset($product['gross_weight']) ? $product['gross_weight'] : 0;
                                            $length       = isset($product['length']) ? $product['length'] : 0;
                                            $width        = isset($product['width']) ? $product['width'] : 0;
                                            $height       = isset($product['height']) ? $product['height'] : 0;
                                        }

                                        $rowspan = max(1, count($variations));
                                    ?>
                                    <tr id="loading_row_<?php echo $product['id']; ?>" class="main-product-row" data-row-id="<?php echo $product['id']; ?>" data-product-id="<?php echo $product['product_id']; ?>">
                                        <td rowspan="<?php echo $rowspan; ?>"><?php echo $sr_no++; ?> </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <select class="form-control form-control-sm invoice-select" 
                                                name="invoice_no[<?php echo $product['id']; ?>]" 
                                                id="invoice_no_<?php echo $product['id']; ?>"
                                                data-product-id="<?php echo $product['id']; ?>"
                                                onchange="updateInvoiceSuppliers();">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="text" class="form-control form-control-sm" 
                                                value="<?php echo htmlspecialchars($product['item_code']); ?>" name="item_code[<?php echo $product['id']; ?>]" readonly>
                                            <input type="hidden" name="product_id[<?php echo $product['id']; ?>]" 
                                                value="<?php echo $product['product_id']; ?>">
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="text" class="form-control form-control-sm" 
                                                value="<?php echo htmlspecialchars($product['product_name']); ?>" name="product_name[<?php echo $product['id']; ?>]" readonly>
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" class="form-control form-control-sm priority-qty" 
                                                value="<?php echo $product['quantity']; ?>" name="quantity[<?php echo $product['id']; ?>]" readonly>
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" min="0" step="1" class="form-control form-control-sm loading-qty" 
                                                name="loading_qty[<?php echo $product['id']; ?>]" 
                                                id="loading_qty_<?php echo $product['id']; ?>"
                                                data-rate-rmb="<?php echo $product['rate_rmb']; ?>"
                                                value=""
                                                onkeyup="calculateOfficial(<?php echo $product['id']; ?>);"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                onchange="calculateOfficial(<?php echo $product['id']; ?>);">
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" min="0" step="0.01" class="form-control form-control-sm unit-price-rmb" 
                                                name="unit_price_rmb[<?php echo $product['id']; ?>]" 
                                                id="unit_price_rmb_<?php echo $product['id']; ?>"
                                                value=""
                                                onkeyup="calculateRow(<?php echo $product['id']; ?>);"
                                                onchange="calculateRow(<?php echo $product['id']; ?>);">
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" min="0" step="1" class="form-control form-control-sm official-ci-qty" 
                                                name="official_ci_qty[<?php echo $product['id']; ?>]" 
                                                id="official_ci_qty_<?php echo $product['id']; ?>"
                                                value=""
                                                onkeyup="calculateRow(<?php echo $product['id']; ?>);"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                onchange="calculateRow(<?php echo $product['id']; ?>);">
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" step="any" class="form-control form-control-sm black-qty" 
                                                id="black_qty_<?php echo $product['id']; ?>" name="black_qty[<?php echo $product['id']; ?>]" readonly>
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" step="0.01" class="form-control form-control-sm total-amount-rmb" 
                                                id="total_amount_rmb_<?php echo $product['id']; ?>" name="total_amount_rmb[<?php echo $product['id']; ?>]" readonly>
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" step="0.01" class="form-control form-control-sm official-ci-unit-price-usd" 
                                                id="official_ci_unit_price_usd_<?php echo $product['id']; ?>"
                                                value="<?php echo number_format($usd_rate, 2, '.', ''); ?>" name="official_ci_unit_price_usd[<?php echo $product['id']; ?>]">
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" step="0.01" class="form-control form-control-sm total-amount-usd" 
                                                id="total_amount_usd_<?php echo $product['id']; ?>" name="total_amount_usd[<?php echo $product['id']; ?>]" readonly>
                                        </td>
                                        <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" step="0.01" class="form-control form-control-sm black-total-price" 
                                                id="black_total_price_<?php echo $product['id']; ?>" name="black_total_price[<?php echo $product['id']; ?>]" readonly>
                                        </td>
                                        <!-- <td rowspan="<?php echo $rowspan; ?>">
                                            <input type="number" step="0.01" class="form-control form-control-sm pkg-ctn" 
                                                id="pkg_ctn_<?php echo $product['id']; ?>"
                                                value="<?php echo $cartoon_qty; ?>" name="pkg_ctn[<?php echo $product['id']; ?>]" readonly>
                                        </td> -->

                                        <?php
                                        // First row's variation data (or master fallback)
                                        $variation_index = 0;
                                        $variation_id = !empty($variations) ? $variations[0]['id'] : 0;
                                        ?>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm pkg-ctn" 
                                                id="pkg_ctn_<?php echo $product['id']; ?>"
                                                value="1" name="pkg_ctn[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]" 
                                                onclick="calculateCTN(<?php echo $product['id']; ?>)" onkeyup="calculateCTN(<?php echo $product['id']; ?>)">
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm nw-kg" 
                                                name="nw_kg[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                value="<?php echo number_format($net_weight, 2, '.', ''); ?>">
                                            <input type="hidden" name="variation_id[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]" value="<?php echo $variation_id; ?>">
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm total-nw" 
                                                name="total_nw[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                readonly>
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm gw-kg" 
                                                name="gw_kg[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                value="<?php echo number_format($gross_weight, 2, '.', ''); ?>">
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm total-gw" 
                                                name="total_gw[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                readonly>
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm length" 
                                                name="length[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                value="<?php echo number_format($length, 2, '.', ''); ?>">
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm width" 
                                                name="width[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                value="<?php echo number_format($width, 2, '.', ''); ?>">
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm height" 
                                                name="height[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                value="<?php echo number_format($height, 2, '.', ''); ?>">
                                        </td>
                                        <td class="metric-cell">
                                            <input type="number" step="0.01" class="form-control form-control-sm total-cbm" 
                                                name="total_cbm[<?php echo $product['id']; ?>][<?php echo $variation_index; ?>]"
                                                readonly>
                                        </td>
                                    </tr>

                                    <?php if (count($variations) > 1): ?>
                                        <?php 
                                        $var_index = 1;
                                        foreach (array_slice($variations, 1) as $var): 
                                        ?>
                                            <tr class="variation-row" data-row-id="<?php echo $product['id']; ?>" data-product-id="<?php echo $product['product_id']; ?>">
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm pkg-ctn" 
                                                        id="pkg_ctn_<?php echo $product['id']; ?>"
                                                        value="1" name="pkg_ctn[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]" readonly>
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm nw-kg" 
                                                        name="nw_kg[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        value="<?php echo number_format($var['net_weight'], 2, '.', ''); ?>">
                                                    <input type="hidden" name="variation_id[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]" value="<?php echo $var['id']; ?>">
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm total-nw" 
                                                        name="total_nw[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        readonly>
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm gw-kg" 
                                                        name="gw_kg[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        value="<?php echo number_format($var['gross_weight'], 2, '.', ''); ?>">
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm total-gw" 
                                                        name="total_gw[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        readonly>
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm length" 
                                                        name="length[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        value="<?php echo number_format($var['length'], 2, '.', ''); ?>">
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm width" 
                                                        name="width[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        value="<?php echo number_format($var['width'], 2, '.', ''); ?>">
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm height" 
                                                        name="height[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        value="<?php echo number_format($var['height'], 2, '.', ''); ?>">
                                                </td>
                                                <td class="metric-cell">
                                                    <input type="number" step="0.01" class="form-control form-control-sm total-cbm" 
                                                        name="total_cbm[<?php echo $product['id']; ?>][<?php echo $var_index; ?>]"
                                                        readonly>
                                                </td>
                                            </tr>
                                        <?php 
                                        $var_index++;
                                        endforeach; 
                                        ?>
                                    <?php endif; ?>

                                    <?php endforeach; ?>
                                    <!-- Supplier Total Row -->
                                    <tr class="supplier-total-row" data-supplier-id="<?php echo $supplier_id; ?>" style="background-color: #fafafc; font-weight: bold;">
                                        <td colspan="4" style="text-align: right; padding: 10px;"><strong>Total:</strong></td>
                                        <td class="supplier-total-priority-qty">0</td>
                                        <td class="supplier-total-loading-qty">0</td>
                                        <td class="supplier-total-official-ci-qty">0</td>
                                        <td class="supplier-total-black-qty">0</td>
                                        <td class="supplier-total-unit-price-rmb">0.00</td>
                                        <td class="supplier-total-amount-rmb">0.00</td>
                                        <td class="supplier-total-official-ci-unit-price-usd">0.00</td>
                                        <td class="supplier-total-amount-usd">0.00</td>
                                        <td class="supplier-total-black-total-price">0.00</td>
                                        <td class="supplier-total-pkg-ctn">0</td>
                                        <td class="supplier-total-nw-kg">0.00</td>
                                        <td class="supplier-total-total-nw">0.00</td>
                                        <td class="supplier-total-gw-kg">0.00</td>
                                        <td class="supplier-total-total-gw">0.00</td>
                                        <td class="supplier-total-length">0.00</td>
                                        <td class="supplier-total-width">0.00</td>
                                        <td class="supplier-total-height">0.00</td>
                                        <td class="supplier-total-total-cbm">0.000000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Grand Total Section -->
            <?php if (!empty($supplier_products)): ?>
                <div class="supplier-section grand-total-section">
                    <h5>
                        <i class="fa fa-calculator"></i> Grand Total
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped loading-table">
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
                                <tr id="grand-total-row" style="background-color: #fafafc; color: #000; font-weight: bold;">
                                    <td colspan="4" style="text-align: right; padding: 10px; color: #000;"><strong>Grand Total:</strong></td>
                                    <td class="grand-total-priority-qty" style="color: #000;">0</td>
                                    <td class="grand-total-loading-qty" style="color: #000;">0</td>
                                    <td class="grand-total-official-ci-qty" style="color: #000;">0</td>
                                    <td class="grand-total-black-qty" style="color: #000;">0</td>
                                    <td class="grand-total-unit-price-rmb" style="color: #000;">0.00</td>
                                    <td class="grand-total-amount-rmb" style="color: #000;">0.00</td>
                                    <td class="grand-total-official-ci-unit-price-usd" style="color: #000;">0.00</td>
                                    <td class="grand-total-amount-usd" style="color: #000;">0.00</td>
                                    <td class="grand-total-black-total-price" style="color: #000;">0.00</td>
                                    <td class="grand-total-pkg-ctn" style="color: #000;">0</td>
                                    <td class="grand-total-nw-kg" style="color: #000;">0.00</td>
                                    <td class="grand-total-total-nw" style="color: #000;">0.00</td>
                                    <td class="grand-total-gw-kg" style="color: #000;">0.00</td>
                                    <td class="grand-total-total-gw" style="color: #000;">0.00</td>
                                    <td class="grand-total-length" style="color: #000;">0.00</td>
                                    <td class="grand-total-width" style="color: #000;">0.00</td>
                                    <td class="grand-total-height" style="color: #000;">0.00</td>
                                    <td class="grand-total-total-cbm" style="color: #000;">0.000000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if(count($products_query) > 0) { ?>
            <!-- Invoice Supplier Selection Section -->
            <div id="invoice_supplier_section" class="mt-2 mb-2" style="display: none;">
                <div class="invoice-supplier-header">
                    <h5>
                        <!-- <i class="fa fa-file" style="color: #5a79c0;"></i> -->
                        Select Supplier for Each Invoice
                        <span class="badge" id="invoice_count_badge">0</span>
                    </h5>
                </div>
                <div id="invoice_supplier_dropdowns" class="row">
                    <!-- Supplier dropdowns will be dynamically added here -->
                </div>
            </div>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary btn-lg btn_verify" name="btn_verify" style="padding: 12px 40px; font-weight: 600;">
                    <i class="fa fa-check-circle"></i> Submit
                </button>
            </div>
        <?php } ?>
    </div>

</form>	

<!-- Modal -->
<div class="modal fade inner-modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title-" id="staticBackdropLabel">Load Supplier Product</h5>
        <button type="button" class="btn-close" id="close-sub-modal" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="temp-supp-prods">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="load-product-btn">Load</button>
      </div>
    </div>
  </div>
</div>

<script>
// Supplier options for JavaScript
var supplierOptions = '';
<?php foreach($supplier_list as $supplier): ?>
supplierOptions += '<option value="<?php echo $supplier['id']; ?>"><?php echo addslashes($supplier['name']); ?></option>';
<?php endforeach; ?>

var loadingListRowCounter = 0;
var loadingListMethod = '<?php echo isset($po_data['method']) ? $po_data['method'] : 'local'; ?>';

function toNumber(value) {
    var parsed = parseFloat(value);
    return isNaN(parsed) ? 0 : parsed;
}

function mergeSupplierProducts(readyProducts, spareProducts) {
    var merged = [];
    var seen = {};
    (readyProducts || []).forEach(function(product) {
        var key = (product.id || '').toString();
        if (key && !seen[key]) {
            seen[key] = true;
            merged.push(product);
        }
    });
    (spareProducts || []).forEach(function(product) {
        var key = (product.id || '').toString();
        if (key && !seen[key]) {
            seen[key] = true;
            merged.push(product);
        }
    });
    return merged;
}

function updateSupplierRowNumbers($section) {
    var index = 1;
    $section.find('tr.main-product-row').each(function() {
        $(this).find('td:first').text(index++);
    });
}

function appendLoadingListProductRow($section, productData) {
    loadingListRowCounter++;
    var rowKey = 'new_' + loadingListRowCounter;
    var productId = (productData.id || '').toString();
    var productName = $('<div>').text(productData.name || '').html();
    var itemCode = $('<div>').text(productData.item_code || '').html();
    var priorityQty = toNumber(productData.priority_qty || 0);
    var rateRmb = toNumber(productData.rate_rmb || productData.rate || 0);
    var usdRate = toNumber(productData.usd_rate || 0);
    var variations = Array.isArray(productData.variations) && productData.variations.length ? productData.variations : [{}];
    var rowSpan = variations.length;

    let html = "";

    for (let i = 0; i < rowSpan; i++) {
        const variation = variations[i] || {};

        const variationId  = variation.id || 0;
        const netWeight    = toNumber(variation.net_weight   ?? productData.net_weight   ?? 0);
        const grossWeight  = toNumber(variation.gross_weight ?? productData.gross_weight ?? 0);
        const length       = toNumber(variation.length       ?? productData.length       ?? 0);
        const width        = toNumber(variation.width        ?? productData.width        ?? 0);
        const height       = toNumber(variation.height       ?? productData.height       ?? 0);

        const isMainRow = i === 0;

        const rowClass = isMainRow ? "main-product-row" : "variation-row";
        const rowId    = isMainRow ? `loading_row_${rowKey}` : "";

        const rowAttrs = isMainRow
            ? ` id="${rowId}" data-row-id="${rowKey}" data-product-id="${productId}"`
            : ` data-row-id="${rowKey}" data-product-id="${productId}"`;

        html += `<tr class="${rowClass}"${rowAttrs}>`;

        if (isMainRow) {
            html += `
            <td rowspan="${rowSpan}">0</td>

            <td rowspan="${rowSpan}">
                <select class="form-control form-control-sm invoice-select" name="invoice_no[${rowKey}]" id="invoice_no_${rowKey}" data-product-id="${rowKey}" onchange="updateInvoiceSuppliers();" >
                    <option value="1" selected>1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </td>

            <td rowspan="${rowSpan}">
                <input type="text" class="form-control form-control-sm" value="${itemCode}" name="item_code[${rowKey}]" readonly />
                <input type="hidden" name="product_id[${rowKey}]" value="${productId}" />
            </td>

            <td rowspan="${rowSpan}">
                <input type="text" class="form-control form-control-sm" value="${productName}" name="product_name[${rowKey}]" readonly />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" class="form-control form-control-sm priority-qty" value="${priorityQty}" name="quantity[${rowKey}]" readonly />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" min="0" step="1" class="form-control form-control-sm loading-qty" name="loading_qty[${rowKey}]" id="loading_qty_${rowKey}" data-rate-rmb="${rateRmb}" value="0" onkeyup="calculateOfficial('${rowKey}');" oninput="this.value = this.value.replace(/[^0-9]/g, '');" onchange="calculateOfficial('${rowKey}');" />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" min="0" step="0.01" class="form-control form-control-sm unit-price-rmb" name="unit_price_rmb[${rowKey}]" id="unit_price_rmb_${rowKey}" value="${rateRmb.toFixed(2)}" onkeyup="calculateRow('${rowKey}');" onchange="calculateRow('${rowKey}');" />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" min="0" step="1" class="form-control form-control-sm official-ci-qty" name="official_ci_qty[${rowKey}]" id="official_ci_qty_${rowKey}" value="0" onkeyup="calculateRow('${rowKey}');" oninput="this.value = this.value.replace(/[^0-9]/g, '');" onchange="calculateRow('${rowKey}');" />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" step="any" class="form-control form-control-sm black-qty" id="black_qty_${rowKey}" name="black_qty[${rowKey}]" readonly />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" step="0.01" class="form-control form-control-sm total-amount-rmb" id="total_amount_rmb_${rowKey}" name="total_amount_rmb[${rowKey}]" readonly />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" step="0.01" class="form-control form-control-sm official-ci-unit-price-usd" id="official_ci_unit_price_usd_${rowKey}" value="${usdRate.toFixed(2)}" name="official_ci_unit_price_usd[${rowKey}]" />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" step="0.01" class="form-control form-control-sm total-amount-usd" id="total_amount_usd_${rowKey}" name="total_amount_usd[${rowKey}]" readonly />
            </td>

            <td rowspan="${rowSpan}">
                <input type="number" step="0.01" class="form-control form-control-sm black-total-price" id="black_total_price_${rowKey}" name="black_total_price[${rowKey}]" readonly />
            </td>
            `;
        }

        html += `
            <td class="metric-cell">
                <input type="hidden" name="variation_id[${rowKey}][${i}]" value="${variationId}" />
                <input type="number" step="0.01" class="form-control form-control-sm pkg-ctn" id="pkg_ctn_${rowKey}_${i}" name="pkg_ctn[${rowKey}][${i}]" value="0" readonly />
            </td>

            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm nw-kg" name="nw_kg[${rowKey}][${i}]" value="${netWeight.toFixed(2)}" />
            </td>

            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm total-nw" name="total_nw[${rowKey}][${i}]" readonly />
            </td>

            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm gw-kg" name="gw_kg[${rowKey}][${i}]" value="${grossWeight.toFixed(2)}" />
            </td>

            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm total-gw" name="total_gw[${rowKey}][${i}]" readonly />
            </td>

            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm length" name="length[${rowKey}][${i}]" value="${length.toFixed(2)}" />
            </td>

            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm width" name="width[${rowKey}][${i}]" value="${width.toFixed(2)}" />
            </td>

            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm height" name="height[${rowKey}][${i}]" value="${height.toFixed(2)}" />
            </td>
            <td class="metric-cell">
                <input type="number" step="0.01" class="form-control form-control-sm total-cbm" name="total_cbm[${rowKey}][${i}]" readonly />
            </td>
        `;

        html += `</tr>`;
    }


    var $tbody = $section.find('.loading_list_tbody');
    var $totalRow = $tbody.find('.supplier-total-row');
    if ($totalRow.length) {
        $totalRow.before(html);
    } else {
        $tbody.append(html);
    }

    calculateRow(rowKey);
}

function supplierProducts(id) {
    let loadProducts = [];
    let selectedCheck =  document.querySelectorAll('.product-check');
    if(selectedCheck.length) {
        let value = [];
        selectedCheck.forEach((ele) => {
            if(ele.checked) {
                value.push(ele.value);
            }
        });

        if(value.length == 0) {
            Swal.fire({
                title: "Error!",
                text: "Select Atleast 1 Product",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
        } else {
            let supplierBtn = document.querySelector('[data-supp-id="' + id + '"]');
            console.log(supplierBtn)
            reloadSupplierProducts(supplierBtn, value)
        }
    } else {
        document.querySelector('#close-sub-modal').click();
    }
}

function reloadSupplierProducts(buttonEl, loadProducts = []) {
    document.querySelector('#close-sub-modal').click();
    var $btn = $(buttonEl);
    var $section = $btn.closest('.supplier-section');
    var supplierId = $section.data('supplier-id');

    if (!supplierId) {
        Swal.fire({
            title: "Error!",
            text: "Supplier not found for this section.",
            icon: "error",
            customClass: {
                confirmButton: "btn btn-primary"
            },
            buttonsStyling: !1
        });
        return;
    }

    var existingProductIds = [];
    $section.find('tr.main-product-row').each(function() {
        var pid = $(this).data('product-id');
        if (pid) {
            existingProductIds.push(pid.toString());
        }
    });

    var originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-refresh"></i> Loading');

    $.ajax({
        type: "POST",
        url: "<?php echo base_url(); ?>inventory/get_products_by_supplier",
        data: {
            supplier_id: supplierId,
            type: loadingListMethod
        },
        dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).html(originalHtml);

            if (res.status == 200) {
                var mergedProducts = mergeSupplierProducts(res.ready_products || [], res.spare_products || []);
                var newCount = 0;

                if(loadProducts.length == 0) {
                   
                    let html = `
                    <table class="table table-bordered table-sm">
                        <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Model No</th>
                            <th>Category</th>
                            <th class="text-end">Cartoon Qty</th>
                        </tr>
                        </thead>
                        <tbody>
                    `;

                    let body = '';
                    mergedProducts.forEach(function(product) {
                        var productId = (product.id || '').toString();
                        // if (productId && existingProductIds.indexOf(productId) === -1) {
                        if (productId) {
                            body += `
                                <tr>
                                <td>
                                    <input
                                    type="checkbox"
                                    class="product-check"
                                    value="${product.id}"
                                    data-product-id="${product.id}"
                                    />
                                </td>
                                <td>${product.name ?? ""}</td>
                                <td>${product.type ?? ""}</td>
                                <td>${product.item_code ?? ""}</td>
                                <td>${product.category_name ?? ""}</td>
                                <td class="text-end">${+product.cartoon_qty ?? ""}</td>
                                </tr>
                            `;
                        }
                    });

                    html += `
                            ${(body != '') ? body : '<td colspan="6" class="text-center">No Products to Select</td>'}
                        </tbody>
                    </table>
                    `;

                    jQuery('.inner-modal').modal('show', {backdrop: 'true'});
                    $("#temp-supp-prods").html(html);
                    document.querySelector("#load-product-btn").setAttribute('onclick',`supplierProducts(${supplierId})`);
                } else {
                    mergedProducts.forEach(function(product) {
                        var productId = (product.id || '').toString();
                        let findLoad = loadProducts.find((e) => e == productId);
                        // if (productId && existingProductIds.indexOf(productId) === -1 && findLoad) {
                        if (productId && findLoad) {
                            appendLoadingListProductRow($section, product);
                            existingProductIds.push(productId);
                            newCount++;
                        }
                    });
    
                    updateSupplierRowNumbers($section);
                    updateSupplierTotals($section);
                    updateGrandTotals();
                    updateInvoiceSuppliers();
    
                    if (newCount === 0) {
                        Swal.fire({
                            title: "Info!",
                            text: "No new products found for this supplier.",
                            icon: "info",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            },
                            buttonsStyling: !1,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            title: "Success!",
                            text: newCount + " product(s) loaded.",
                            icon: "success",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            },
                            buttonsStyling: !1,
                            timer: 2000
                        });
                    }
                }
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "Failed to fetch products.",
                    icon: "error",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    },
                    buttonsStyling: !1
                });
            }
        },
        error: function() {
            $btn.prop('disabled', false).html(originalHtml);
            Swal.fire({
                title: "Error!",
                text: "Error loading products. Please try again.",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
        }
    });
}

// Track invoice selections and update supplier dropdowns
function updateInvoiceSuppliers() {
    var invoiceMap = {};
    
    // Collect all selected invoice numbers and their associated product IDs
    $('.invoice-select').each(function() {
        var invoiceNo = $(this).val();
        var productId = $(this).data('product-id');
        
        if (invoiceNo && invoiceNo !== '') {
            if (!invoiceMap[invoiceNo]) {
                invoiceMap[invoiceNo] = [];
            }
            invoiceMap[invoiceNo].push(productId);
        }
    });
    
    // Get unique invoice numbers
    var uniqueInvoices = Object.keys(invoiceMap).sort(function(a, b) {
        return parseInt(a) - parseInt(b);
    });
    
    // Clear existing dropdowns
    $('#invoice_supplier_dropdowns').empty();
    
    // Create supplier dropdown for each unique invoice number
    if (uniqueInvoices.length > 0) {
        $('#invoice_supplier_section').slideDown(300);
        $('#invoice_count_badge').text(uniqueInvoices.length);
        
        uniqueInvoices.forEach(function(invoiceNo) {
            var productCount = invoiceMap[invoiceNo].length;
            var productText = productCount === 1 ? 'product' : 'products';
            
            // Get current date in YYYY-MM-DD format for default value
            var today = new Date();
            var currentDate = today.getFullYear() + '-' + 
                String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                String(today.getDate()).padStart(2, '0');
            
            var dropdownHtml = '<div class="col-md-6">' +
                '<div class="invoice-card">' +
                '<div class="invoice-card-header">' +
                '<span class="invoice-number-badge">' +
                '<i class="fa fa-file"></i>' +
                'Invoice No. ' + invoiceNo +
                '</span>' +
                '<span class="invoice-product-count">' +
                '<i class="fa fa-dropbox"></i> ' + productCount + ' ' + productText +
                '</span>' +
                '</div>' +
                '<label class="invoice-supplier-label">' +
                '<i class="fa fa-truck"></i> ' +
                'Select Supplier <span class="required">*</span>' +
                '</label>' +
                '<select class="form-control invoice-supplier-select" name="invoice_supplier[' + invoiceNo + ']" ' +
                'id="invoice_supplier_' + invoiceNo + '" required>' +
                '<option value="">-- Choose Supplier --</option>' +
                supplierOptions +
                '</select>' +
                '<div class="invoice-field-group">' +
                '<label class="invoice-supplier-label">' +
                '<i class="fa fa-file-text"></i> ' +
                'Invoice Info' +
                '</label>' +
                '<input type="text" class="form-control invoice-field-input" name="invoice[' + invoiceNo + ']" ' +
                'id="invoice_' + invoiceNo + '" placeholder="Enter invoice information">' +
                '</div>' +
                '<div class="invoice-field-group">' +
                '<label class="invoice-supplier-label">' +
                '<i class="fa fa-calendar"></i> ' +
                'Invoice Date <span class="required">*</span>' +
                '</label>' +
                '<input type="date" class="form-control invoice-field-input" name="invoice_date[' + invoiceNo + ']" ' +
                'id="invoice_date_' + invoiceNo + '" value="' + currentDate + '" required>' +
                '</div>' +
                '<div class="invoice-field-group">' +
                '<label class="invoice-supplier-label">' +
                '<i class="fa fa-clipboard"></i> ' +
                'Invoice Terms' +
                '</label>' +
                '<textarea class="form-control invoice-field-textarea" name="invoice_terms[' + invoiceNo + ']" ' +
                'id="invoice_terms_' + invoiceNo + '" placeholder="Enter invoice terms"></textarea>' +
                '</div>' +
                '<div class="invoice-field-group">' +
                '<label class="invoice-supplier-label">' +
                '<i class="fa fa-dollar-sign"></i> ' +
                'Price Term' +
                '</label>' +
                '<input type="text" class="form-control invoice-field-input" name="invoice_price_terms[' + invoiceNo + ']" ' +
                'id="invoice_price_terms_' + invoiceNo + '" placeholder="Enter price term">' +
                '</div>' +
                '</div>' +
                '</div>';
            
            $('#invoice_supplier_dropdowns').append(dropdownHtml);
        });
    } else {
        $('#invoice_supplier_section').slideUp(300);
        $('#invoice_count_badge').text('0');
    }
}

// Calculate Official Quantity for a product.
function calculateOfficial(rowId) {
    var $mainRow = $('#loading_row_' + rowId);
    var loadingQty = parseFloat($mainRow.find('.loading-qty').val()) || 0;
    var rateRMB = parseFloat($mainRow.find('.loading-qty').data('rate-rmb')) || 0;
    $mainRow.find('.unit-price-rmb').val(rateRMB);
    $mainRow.find('.official-ci-qty').val(loadingQty.toFixed(0));
    $mainRow.find('.black-qty').val(0);

    // For each variation row (including main row), calculate weights and CBM
    $('[data-row-id="' + rowId + '"]').each(function() {
        var $row = $(this);
        $row.find('.pkg-ctn').val(loadingQty);
    });

    calculateRow(rowId);
}

// Calculate CTN for a product
function calculateCTN(rowId) {
    var $mainRow = $('#loading_row_' + rowId);
    var loadingQty = parseFloat($mainRow.find('.loading-qty').val()) || 0;
    var officialCIQty = parseFloat($mainRow.find('.official-ci-qty').val()) || 0;
    var pkgCtn = parseFloat($mainRow.find('.pkg-ctn').val()) || 0;
   
    $('[data-row-id="' + rowId + '"]').each(function() {
        var $row = $(this);

        var pkgCtn = parseFloat($row.find('.pkg-ctn').val()) || 0;
        var nwKg = parseFloat($row.find('.nw-kg').val()) || 0;
        var gwKg = parseFloat($row.find('.gw-kg').val()) || 0;
        var length = parseFloat($row.find('.length').val()) || 0;
        var width = parseFloat($row.find('.width').val()) || 0;
        var height = parseFloat($row.find('.height').val()) || 0;

        // Calculate Total N.W.
        var totalNW = nwKg * pkgCtn;
        $row.find('.total-nw').val(totalNW.toFixed(2));
        
        // Calculate Total G.W.
        var totalGW = gwKg * pkgCtn;
        $row.find('.total-gw').val(totalGW.toFixed(2));
        
        // Calculate Total CBM: (L * W * H / 1000000000) * PKG (ctn)
        // Dimensions are in mm, dividing by 1000000000 converts mm³ to m³
        // Formula: (L * W * H / 1000000000) * PKG * (Loading Qty / PKG) = (L * W * H / 1000000000) * Loading Qty
        // But requirement specifies * PKG, so we'll use: (L * W * H / 1000000000) * PKG * (Loading Qty / PKG) if PKG > 0
        var volumeMm3 = length * width * height;
        var volumeM3PerUnit = volumeMm3 / 1000000000; // Convert mm³ to m³
        // If PKG > 0, calculate number of cartons, otherwise use direct calculation
        var totalCBM;
        if (pkgCtn > 0 && loadingQty > 0) {
            var cbmPerCarton = volumeM3PerUnit * pkgCtn; // CBM per carton
            var numberOfCartons = loadingQty / pkgCtn; // Number of cartons
            totalCBM = cbmPerCarton * numberOfCartons; // Total CBM
        } else {
            // Fallback: direct calculation if PKG is 0
            totalCBM = volumeM3PerUnit * loadingQty;
        }

        $row.find('.total-cbm').val(totalCBM.toFixed(6));
    });
    
    // Update supplier totals after row calculation
    updateSupplierTotals($mainRow.closest('.supplier-section'));
    // Update grand totals
    updateGrandTotals();
}

// Calculate all fields for a product (and its variation rows)
function calculateRow(rowId) {
    var $mainRow = $('#loading_row_' + rowId);

    // Get product-level input values from main row
    var loadingQty = parseFloat($mainRow.find('.loading-qty').val()) || 0;
    var officialCIQty = parseFloat($mainRow.find('.official-ci-qty').val()) || 0;
    var blackQty = parseFloat($mainRow.find('.black-qty').val()) || 0;
    var unitPriceRMB = parseFloat($mainRow.find('.unit-price-rmb').val()) || 0;
    var officialCIUnitPriceUSD = parseFloat($mainRow.find('.official-ci-unit-price-usd').val()) || 0;
    var pkgCtn = parseFloat($mainRow.find('.pkg-ctn').val()) || 0;
    
    // Validate Official CI Qty
    if (officialCIQty > loadingQty) {
        Swal.fire({
            title: "Validation Error!",
            text: "Official CI Qty cannot be more than Loading Qty (PCS)!",
            icon: "error",
            customClass: {
                confirmButton: "btn btn-primary"
            },
            buttonsStyling: !1
        });

        officialCIQty = loadingQty;
        $mainRow.find('.official-ci-qty').val(officialCIQty);
    } 
    
    // Calculate Black Qty
    var blackQty = loadingQty - officialCIQty;
    $mainRow.find('.black-qty').val(blackQty.toFixed(0));
    
    // Calculate Total Amount (RMB)
    var totalAmountRMB = loadingQty * unitPriceRMB;
    $mainRow.find('.total-amount-rmb').val(totalAmountRMB.toFixed(2));
    
    // Calculate Total Amount (USD)
    var totalAmountUSD = officialCIQty * officialCIUnitPriceUSD;
    $mainRow.find('.total-amount-usd').val(totalAmountUSD.toFixed(2));
    
    // Calculate Black Total Price (Official CI Unit Price USD * Black Qty)
    var blackTotalPrice = officialCIUnitPriceUSD * blackQty;
    $mainRow.find('.black-total-price').val(blackTotalPrice.toFixed(2));
    
    // For each variation row (including main row), calculate weights and CBM
    $('[data-row-id="' + rowId + '"]').each(function() {
        var $row = $(this);

        var pkgQty = parseFloat($row.find('.pkg-ctn').val()) || 0;
        var nwKg = parseFloat($row.find('.nw-kg').val()) || 0;
        var gwKg = parseFloat($row.find('.gw-kg').val()) || 0;
        var length = parseFloat($row.find('.length').val()) || 0;
        var width = parseFloat($row.find('.width').val()) || 0;
        var height = parseFloat($row.find('.height').val()) || 0;


        // $row.find('.pkg-ctn').val(loadingQty);

        // Calculate Total N.W.
        var totalNW = nwKg * pkgQty;
        $row.find('.total-nw').val(totalNW.toFixed(2));
        
        // Calculate Total G.W.
        var totalGW = gwKg * pkgQty;
        $row.find('.total-gw').val(totalGW.toFixed(2));
        
        // Calculate Total CBM: (L * W * H / 1000000000) * PKG (ctn)
        // Dimensions are in mm, dividing by 1000000000 converts mm³ to m³
        // Formula: (L * W * H / 1000000000) * PKG * (Loading Qty / PKG) = (L * W * H / 1000000000) * Loading Qty
        // But requirement specifies * PKG, so we'll use: (L * W * H / 1000000000) * PKG * (Loading Qty / PKG) if PKG > 0
        var volumeMm3 = length * width * height;
        var volumeM3PerUnit = volumeMm3 / 1000000000; // Convert mm³ to m³
        // If PKG > 0, calculate number of cartons, otherwise use direct calculation
        var totalCBM;
        if (pkgCtn > 0 && loadingQty > 0) {
            var cbmPerCarton = volumeM3PerUnit * pkgCtn; // CBM per carton
            totalCBM = cbmPerCarton // Total CBM
            // var numberOfCartons = loadingQty / pkgCtn; // Number of cartons
            // totalCBM = cbmPerCarton * numberOfCartons; // Total CBM
        } else {
            // Fallback: direct calculation if PKG is 0
            var cbmPerCarton = volumeM3PerUnit * pkgCtn;
            totalCBM = cbmPerCarton;
            // totalCBM = volumeM3PerUnit * loadingQty;
        }

        $row.find('.total-cbm').val(totalCBM.toFixed(6));
    });
    
    // Update supplier totals after row calculation
    updateSupplierTotals($mainRow.closest('.supplier-section'));
    // Update grand totals
    updateGrandTotals();
}

// Calculate supplier totals for a specific supplier section
function updateSupplierTotals($supplierSection) {
    var $tbody = $supplierSection.find('.loading_list_tbody');
    var $totalRow = $supplierSection.find('.supplier-total-row');
    
    if ($totalRow.length === 0) return;
    
    var totals = {
        priorityQty: 0,
        loadingQty: 0,
        officialCIQty: 0,
        blackQty: 0,
        unitPriceRMB: 0,
        totalAmountRMB: 0,
        officialCIUnitPriceUSD: 0,
        totalAmountUSD: 0,
        blackTotalPrice: 0,
        pkgCtn: 0,
        nwKg: 0,
        totalNW: 0,
        gwKg: 0,
        totalGW: 0,
        length: 0,
        width: 0,
        height: 0,
        totalCBM: 0
    };
    
    // Sum all main product rows in this supplier section
    $tbody.find('tr.main-product-row').each(function() {
        var $row = $(this);
        
        totals.priorityQty += parseFloat($row.find('.priority-qty').val()) || 0;
        totals.loadingQty += parseFloat($row.find('.loading-qty').val()) || 0;
        totals.officialCIQty += parseFloat($row.find('.official-ci-qty').val()) || 0;
        totals.blackQty += parseFloat($row.find('.black-qty').val()) || 0;
        totals.unitPriceRMB += parseFloat($row.find('.unit-price-rmb').val()) || 0;
        totals.totalAmountRMB += parseFloat($row.find('.total-amount-rmb').val()) || 0;
        totals.officialCIUnitPriceUSD += parseFloat($row.find('.official-ci-unit-price-usd').val()) || 0;
        totals.totalAmountUSD += parseFloat($row.find('.total-amount-usd').val()) || 0;
        totals.blackTotalPrice += parseFloat($row.find('.black-total-price').val()) || 0;
        totals.pkgCtn += parseFloat($row.find('.pkg-ctn').val()) || 0;
        
        // For metrics, sum all variation rows for this product
        var rowId = $row.data('row-id');
        $('[data-row-id="' + rowId + '"]').each(function() {
            var $varRow = $(this);
            totals.nwKg += parseFloat($varRow.find('.nw-kg').val()) || 0;
            totals.totalNW += parseFloat($varRow.find('.total-nw').val()) || 0;
            totals.gwKg += parseFloat($varRow.find('.gw-kg').val()) || 0;
            totals.totalGW += parseFloat($varRow.find('.total-gw').val()) || 0;
            totals.length += parseFloat($varRow.find('.length').val()) || 0;
            totals.width += parseFloat($varRow.find('.width').val()) || 0;
            totals.height += parseFloat($varRow.find('.height').val()) || 0;
            totals.totalCBM += parseFloat($varRow.find('.total-cbm').val()) || 0;
        });
    });
    
    // Update total row
    $totalRow.find('.supplier-total-priority-qty').text(Math.round(totals.priorityQty));
    $totalRow.find('.supplier-total-loading-qty').text(Math.round(totals.loadingQty));
    $totalRow.find('.supplier-total-official-ci-qty').text(Math.round(totals.officialCIQty));
    $totalRow.find('.supplier-total-black-qty').text(Math.round(totals.blackQty));
    $totalRow.find('.supplier-total-unit-price-rmb').text(totals.unitPriceRMB.toFixed(2));
    $totalRow.find('.supplier-total-amount-rmb').text(totals.totalAmountRMB.toFixed(2));
    $totalRow.find('.supplier-total-official-ci-unit-price-usd').text(totals.officialCIUnitPriceUSD.toFixed(2));
    $totalRow.find('.supplier-total-amount-usd').text(totals.totalAmountUSD.toFixed(2));
    $totalRow.find('.supplier-total-black-total-price').text(totals.blackTotalPrice.toFixed(2));
    $totalRow.find('.supplier-total-pkg-ctn').text(Math.round(totals.pkgCtn));
    $totalRow.find('.supplier-total-nw-kg').text(totals.nwKg.toFixed(2));
    $totalRow.find('.supplier-total-total-nw').text(totals.totalNW.toFixed(2));
    $totalRow.find('.supplier-total-gw-kg').text(totals.gwKg.toFixed(2));
    $totalRow.find('.supplier-total-total-gw').text(totals.totalGW.toFixed(2));
    $totalRow.find('.supplier-total-length').text(totals.length.toFixed(2));
    $totalRow.find('.supplier-total-width').text(totals.width.toFixed(2));
    $totalRow.find('.supplier-total-height').text(totals.height.toFixed(2));
    $totalRow.find('.supplier-total-total-cbm').text(totals.totalCBM.toFixed(6));
}

// Calculate grand totals across all suppliers
function updateGrandTotals() {
    var grandTotals = {
        priorityQty: 0,
        loadingQty: 0,
        officialCIQty: 0,
        blackQty: 0,
        unitPriceRMB: 0,
        totalAmountRMB: 0,
        officialCIUnitPriceUSD: 0,
        totalAmountUSD: 0,
        blackTotalPrice: 0,
        pkgCtn: 0,
        nwKg: 0,
        totalNW: 0,
        gwKg: 0,
        totalGW: 0,
        length: 0,
        width: 0,
        height: 0,
        totalCBM: 0
    };
    
    // Calculate directly from all product rows across all suppliers (more reliable)
    $('.loading_list_tbody tr.main-product-row').each(function() {
        var $row = $(this);
        
        grandTotals.priorityQty += parseFloat($row.find('.priority-qty').val()) || 0;
        grandTotals.loadingQty += parseFloat($row.find('.loading-qty').val()) || 0;
        grandTotals.officialCIQty += parseFloat($row.find('.official-ci-qty').val()) || 0;
        grandTotals.blackQty += parseFloat($row.find('.black-qty').val()) || 0;
        grandTotals.unitPriceRMB += parseFloat($row.find('.unit-price-rmb').val()) || 0;
        grandTotals.totalAmountRMB += parseFloat($row.find('.total-amount-rmb').val()) || 0;
        grandTotals.officialCIUnitPriceUSD += parseFloat($row.find('.official-ci-unit-price-usd').val()) || 0;
        grandTotals.totalAmountUSD += parseFloat($row.find('.total-amount-usd').val()) || 0;
        grandTotals.blackTotalPrice += parseFloat($row.find('.black-total-price').val()) || 0;
        grandTotals.pkgCtn += parseFloat($row.find('.pkg-ctn').val()) || 0;
        
        // For metrics, sum all variation rows for this product
        var rowId = $row.data('row-id');
        $('[data-row-id="' + rowId + '"]').each(function() {
            var $varRow = $(this);
            grandTotals.nwKg += parseFloat($varRow.find('.nw-kg').val()) || 0;
            grandTotals.totalNW += parseFloat($varRow.find('.total-nw').val()) || 0;
            grandTotals.gwKg += parseFloat($varRow.find('.gw-kg').val()) || 0;
            grandTotals.totalGW += parseFloat($varRow.find('.total-gw').val()) || 0;
            grandTotals.length += parseFloat($varRow.find('.length').val()) || 0;
            grandTotals.width += parseFloat($varRow.find('.width').val()) || 0;
            grandTotals.height += parseFloat($varRow.find('.height').val()) || 0;
            grandTotals.totalCBM += parseFloat($varRow.find('.total-cbm').val()) || 0;
        });
    });
    
    // Update grand total row
    var $grandTotalRow = $('#grand-total-row');
    if ($grandTotalRow.length > 0) {
        $grandTotalRow.find('.grand-total-priority-qty').text(Math.round(grandTotals.priorityQty));
        $grandTotalRow.find('.grand-total-loading-qty').text(Math.round(grandTotals.loadingQty));
        $grandTotalRow.find('.grand-total-official-ci-qty').text(Math.round(grandTotals.officialCIQty));
        $grandTotalRow.find('.grand-total-black-qty').text(Math.round(grandTotals.blackQty));
        $grandTotalRow.find('.grand-total-unit-price-rmb').text(grandTotals.unitPriceRMB.toFixed(2));
        $grandTotalRow.find('.grand-total-amount-rmb').text(grandTotals.totalAmountRMB.toFixed(2));
        $grandTotalRow.find('.grand-total-official-ci-unit-price-usd').text(grandTotals.officialCIUnitPriceUSD.toFixed(2));
        $grandTotalRow.find('.grand-total-amount-usd').text(grandTotals.totalAmountUSD.toFixed(2));
        $grandTotalRow.find('.grand-total-black-total-price').text(grandTotals.blackTotalPrice.toFixed(2));
        $grandTotalRow.find('.grand-total-pkg-ctn').text(Math.round(grandTotals.pkgCtn));
        $grandTotalRow.find('.grand-total-nw-kg').text(grandTotals.nwKg.toFixed(2));
        $grandTotalRow.find('.grand-total-total-nw').text(grandTotals.totalNW.toFixed(2));
        $grandTotalRow.find('.grand-total-gw-kg').text(grandTotals.gwKg.toFixed(2));
        $grandTotalRow.find('.grand-total-total-gw').text(grandTotals.totalGW.toFixed(2));
        $grandTotalRow.find('.grand-total-length').text(grandTotals.length.toFixed(2));
        $grandTotalRow.find('.grand-total-width').text(grandTotals.width.toFixed(2));
        $grandTotalRow.find('.grand-total-height').text(grandTotals.height.toFixed(2));
        $grandTotalRow.find('.grand-total-total-cbm').text(grandTotals.totalCBM.toFixed(6));
    }
}


// Initialize calculations on page load
$(document).ready(function() {
    // Calculate all main product rows
    $('.loading_list_tbody tr.main-product-row').each(function() {
        var rowId = $(this).data('row-id') || $(this).attr('id').replace('loading_row_', '');
        calculateRow(rowId);
    });

    // Initialize invoice supplier section
    updateInvoiceSuppliers();
    
    // Prevent form submission on Enter key press (except in textareas)
    $('form.priority-list-form').on('keydown', 'input:not(textarea), select', function(e) {
      if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        return false;
      }
    });
    
    // Also prevent Enter key on the form level
    $('form.priority-list-form').on('keypress', function(e) {
      if (e.key === 'Enter' || e.keyCode === 13) {
        // Allow Enter in textareas
        if ($(e.target).is('textarea')) {
          return true;
        }
        // Prevent Enter in all other fields
        e.preventDefault();
        return false;
      }
    });

    // Recalculate when key fields are edited
    $(document).on('keyup change', '.official-ci-unit-price-usd, .nw-kg, .gw-kg, .length, .width, .height', function() {
        var $row = $(this).closest('tr');
        var rowId = $row.data('row-id');
        if (!rowId) {
            // Fallback for main row where id attribute is used
            var idAttr = $row.attr('id') || '';
            if (idAttr.indexOf('loading_row_') === 0) {
                rowId = idAttr.replace('loading_row_', '');
            }
        }
        if (rowId) {
            calculateRow(rowId);
        }
    });
    
    // Initialize all supplier totals and grand totals
    $('.supplier-section').each(function() {
        updateSupplierTotals($(this));
    });
    updateGrandTotals();

    $('.priority-list-form').submit(function(e) {
        e.preventDefault();  
		var buttonText=$(".btn_verify").val().trim()==="" ? "Submit":$(".btn_verify").val();
		
        $(".loader").show(); 
        $('.btn_verify').attr("disabled", true)
        $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
        var url = $(this).attr('action');
        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            dataType: 'json',
            data: $(".priority-list-form").serialize(),
            success: function(res) {
                if (res.status == '200') {
                  $(".loader").fadeOut("slow"); 
                  Swal.fire({
            		title: "Success!",
            		text: res.message,
            		icon: "success",
            		customClass: {
            			confirmButton: "btn btn-primary"
            		},
            		buttonsStyling: !1
            	  }).then(() => {window.location.href = res.url;});
                  
                }
                else {	
                    Swal.fire({
            			title: "Error!",
            			text: res.message ,
            			icon: "error",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})
                    $('.btn_verify').html(buttonText);
                    $('.btn_verify').attr("disabled", false);
                    $(".loader").fadeOut("slow"); 
                }
            }
        });
        return false;
    });
});
</script>
