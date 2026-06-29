<?php
$po_id = $param2;

$po_raw = $this->db
    ->query("
        SELECT 
            inr_rate,
            boe_no,
            boe_date,
            delivery_status
        FROM purchase_order
        WHERE id = '$po_id'
    ")
    ->row_array();

$inr_rate = (float)($po_raw['inr_rate'] ?? 0);
$source_table = ($po_raw['delivery_status'] == 'purchase_in') ? 'purchase_in_product' : 'loading_po_product';

$products_raw = $this->db
    ->query("
        SELECT 
            pop.*,
            s.name AS supplier_name,
            rp.rate AS rp_rate,
            rp.actual_usd_rate AS rp_actual_usd_rate,
            rp.duty_charge,
            inv.quantity AS inv_qty,
            (SELECT is_complete FROM purchase_overflow_product WHERE parent_id = pop.id LIMIT 1) AS overflow_is_complete
        FROM $source_table pop
        LEFT JOIN supplier s ON s.id = pop.supplier_id
        LEFT JOIN raw_products rp ON rp.id = pop.product_id
        LEFT JOIN inventory inv ON 
             (inv.po_row_id = pop.id) 
             OR 
             (inv.po_row_id = 0 AND inv.product_id = pop.product_id 
              AND inv.batch_no = (SELECT voucher_no FROM purchase_order WHERE id = '$po_id')
              AND inv.warehouse_id = (SELECT warehouse_id FROM purchase_order WHERE id = '$po_id'))
        WHERE pop.parent_id = '$po_id' AND pop.loading_qty > 0 AND pop.is_deleted = 0
        ORDER BY pop.id ASC
    ")
    ->result_array();

$supplier_products = [];
foreach ($products_raw as $product) {
    $supplier_id = isset($product['supplier_id']) ? $product['supplier_id'] : 0;
    if (!isset($supplier_products[$supplier_id])) {
        $supplier_products[$supplier_id] = [
            'supplier_name' => isset($product['supplier_name']) ? $product['supplier_name'] : 'Unknown Supplier',
            'products' => [],
            'invoice' => '',
            'invoice_date' => ''
        ];
    }
    $supplier_products[$supplier_id]['products'][] = $product;

    // Capture invoice info if it matches this supplier
    if (!empty($product['invoice']) && isset($product['invoice_supplier_id']) && $product['invoice_supplier_id'] == $supplier_id) {
        $supplier_products[$supplier_id]['invoice'] = $product['invoice'];
        $supplier_products[$supplier_id]['invoice_date'] = $product['invoice_date'];
    }
}

// Get supplier list for the "Add Supplier" modal
$company_id = $this->session->userdata('company_id');
$supplier_list = $this->db->query("SELECT * FROM supplier WHERE is_deleted = '0' AND company_id = '$company_id' ORDER BY name ASC")->result_array();
?>

<style>
    .inner-modal {
        background: rgba(0, 0, 0, 0.25);
    }
    .supplier-table-container {
        max-height: 55vh;
        overflow-y: auto;
    }
    .supplier-table-container thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f8f9fa;
        box-shadow: inset 0 -2px 0 #dee2e6; /* Ensures bottom border remains visible */
    }
</style>

<?php echo form_open('inventory/update_purchase_order_in', ['class' => 'priority-list-form', 'onsubmit' => 'return checkForm(this);']); ?>
<input type="hidden" name="po_id" value="<?php echo $po_id; ?>">
    <div class="row">
        <div class="col-3 d-flex">
            <label class="mr-2 mb-0">BOE No <span class="text-danger">*</span></label>
            <input
                type="text"
                name="boe_no"
                class="form-control form-control-sm text-right"
                placeholder="Enter Bill of entry no"
                value="<?php echo $po_raw['boe_no'] ?? ''; ?>"
                required
            >
        </div>
        <div class="col-3 d-flex">
            <label class="mr-2 mb-0">BOE Date <span class="text-danger">*</span></label>
            <input
                type="date"
                name="boe_date"
                class="form-control form-control-sm text-right"
                value="<?php echo (!empty($po_raw['boe_date']) && $po_raw['boe_date'] !== '0000-00-00 00:00:00' && $po_raw['boe_date'] !== '0000-00-00') ? date('Y-m-d', strtotime($po_raw['boe_date'])) : ''; ?>"
                required
            >
        </div>
        <div class="col-3 d-flex">
            <label class="mr-2 mb-0">INR Rate <span class="text-danger">*</span></label>
            <input
                type="number"
                step="0.01"
                name="inr_rate"
                class="form-control form-control-sm text-right supplier-inr-rate"
                placeholder="Enter INR rate"
                value="<?php echo isset($po_raw['inr_rate']) ? number_format((float)$po_raw['inr_rate'], 2) : '0'; ?>"
                required
            >
        </div>
        <div class="col-md-12 mt-2">
            <?php if (!empty($supplier_products)): ?>
                <?php 
                    $g_actual_qty        = 0;
                    $g_actual_rmb        = 0;
                    $g_total_rmb         = 0;
                    $g_actual_usd        = 0;
                    $g_total_usd         = 0;
                    $g_actual_inr        = 0;
                    $g_total_inr         = 0;
                    $g_official_qty      = 0;
                    $g_official_rate_usd = 0;
                    $g_official_rate_rs  = 0;
                    $g_official_total_rs = 0;
                    $g_duty_amt          = 0;
                    $g_duty_surcharge    = 0;
                    $g_taxable_value     = 0;
                    $g_gst_amt           = 0;
                    $g_total_amt         = 0;
                    foreach ($supplier_products as $supplier_id => $supplier_data): ?>
                    <div class="supplier-section mb-2" data-supplier-id="<?php echo $supplier_id; ?>">
                        <h5>
                            Supplier: <?php echo htmlspecialchars($supplier_data['supplier_name']); ?>
                            <?php if (!empty($supplier_data['invoice'])): ?>
                                <span class="badge badge-soft-primary ml-2" style="font-size: 0.8rem; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                    <i class="fa fa-file-invoice mr-1"></i> Inv: <?php echo htmlspecialchars($supplier_data['invoice']); ?>
                                </span>
                                <span class="badge badge-soft-secondary ml-1" style="font-size: 0.8rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                                    <i class="fa fa-calendar-alt mr-1"></i> <?php echo !empty($supplier_data['invoice_date']) ? date('d-M-Y', strtotime($supplier_data['invoice_date'])) : '-'; ?>
                                </span>
                            <?php endif; ?>
                            <span class="supplier-header-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm supplier-reload-btn" data-supplier-id="<?php echo $supplier_id; ?>" onclick="reloadSupplierProducts(this)">
                                    <i class="fa fa-refresh"></i> Add Product
                                </button>
                            </span>
                        </h5>
                        <div class="table-responsive supplier-table-container">
                            <table class="table table-bordered table-striped table-sm" style="min-width: 2250px;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Sr No.</th>
                                        <th style="width: 250px;">Product Name</th>
                                        <th style="width: 150px;">Model No.</th>
                                        <th style="width: 100px;">Actual Qty</th>
                                        <th style="width: 100px;">Actual RMB</th>
                                        <th style="width: 100px;">Total RMB</th>
                                        <th style="width: 100px;">Actual USD</th>
                                        <th style="width: 100px;">Total USD</th>
                                        <th style="width: 100px;">Actual INR</th>
                                        <th style="width: 100px;">Total INR</th>
                                        <th style="width: 100px;">Official Qty</th>
                                        <th style="width: 100px;">Official Rate USD</th>
                                        <th style="width: 100px;">Official Rate Rs.</th>
                                        <th style="width: 100px;">Official Total Rs.</th>
                                        <th style="width: 100px;">Duty %</th>
                                        <th style="width: 100px;">Duty Amt</th>
                                        <th style="width: 100px;">Duty Surcharge 10%</th>
                                        <th style="width: 100px;">Taxable Value</th>
                                        <th style="width: 100px;">GST Amt</th>
                                        <th style="width: 100px;">Total Duty/GST</th>
                                        <th style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sr_no = 1;

                                    // totals for this supplier table
                                    $t_actual_qty        = 0;
                                    $t_actual_rmb        = 0;
                                    $t_total_rmb         = 0;
                                    $t_actual_usd        = 0;
                                    $t_total_usd         = 0;
                                    $t_actual_inr        = 0;
                                    $t_total_inr         = 0;
                                    $t_official_qty      = 0;
                                    $t_official_rate_usd = 0;
                                    $t_official_rate_rs  = 0;
                                    $t_official_total_rs = 0;
                                    $t_duty_amt          = 0;
                                    $t_duty_surcharge    = 0;
                                    $t_taxable_value     = 0;
                                    $t_gst_amt           = 0;
                                    $t_total_amt         = 0;
                                    foreach ($supplier_data['products'] as $ind => $product):
                                        $delivery_status = $po_raw['delivery_status'];
                                        
                                        // Product details
                                        $product_name = $product['product_name'] ?? '';
                                        $item_code = $product['item_code'] ?? '';
                                        
                                        // Quantities
                                        $actual_qty = (float)($product['actual_qty'] ?? 0);
                                        // If first time opening Stock In, show loading hints
                                        if ($delivery_status != 'purchase_in' && $actual_qty == 0) {
                                            $actual_qty = (float)($product['loading_qty'] ?? 0);
                                        }
                                        
                                        if ($actual_qty <= 0 && $delivery_status != 'purchase_in') {
                                            $actual_qty = (float)($product['loading_qty'] ?? 0);
                                        }

                                        // Prices - handles different field names between tables
                                        if ($delivery_status != 'purchase_in') {
                                            $actual_rmb = (float)($product['unit_price_rmb'] ?? 0);
                                            $actual_usd = (float)($product['rp_actual_usd_rate'] ?? 0);
                                            $actual_inr = (float)($actual_usd * $inr_rate);
                                        } else {
                                            $actual_rmb = (float)($product['actual_rmb'] ?? 0);
                                            $actual_usd = (float)($product['actual_usd'] ?? 0);
                                            $actual_inr = (float)($product['actual_inr'] ?? 0);
                                        }
                                        
                                        // Official Details
                                        $official_qty = (float)($product['official_ci_qty'] ?? 0);
                                        // if ($official_qty == 0) $official_qty = $actual_qty; 
                                        
                                        $official_rate_usd = (float)($product['official_ci_unit_price_usd'] ?? 0);
                                        $official_rate_rs = ($official_qty > 0) ? ($official_rate_usd * $inr_rate) : 0.0;
                                        $official_total_rs = $official_qty * $official_rate_rs;
                                        
                                        // Locking Logic
                                        $is_locked = false;
                                        $lock_reason = "";
                                        if ($po_raw['delivery_status'] == 'purchase_in') {
                                            $stocked_qty = (float)($product['actual_qty'] ?? 0);
                                            $current_inv = (float)($product['inv_qty'] ?? 0);
                                            if ($stocked_qty > 0 && $current_inv != $stocked_qty) {
                                                $is_locked = true;
                                                $lock_reason = "Stock Used (Stocked: $stocked_qty, Current: $current_inv)";
                                            }
                                        }
                                        if (isset($product['overflow_is_complete']) && $product['overflow_is_complete'] == 1) {
                                            $is_locked = true;
                                            $lock_reason = "Overflow complete";
                                        }

                                        if ($po_raw['delivery_status'] != 'purchase_in') {
                                            $duty_percent = isset($product['duty_charge']) ? (float)$product['duty_charge'] : 7.5;
                                        } else {
                                            $duty_percent = (float)($product['duty_percent'] ?? 7.5);
                                        }
                                        
                                        // Tax/Duty mapping
                                        if ($delivery_status == 'purchase_in') {
                                            $duty_amt = (float)($product['duty_amt'] ?? 0);
                                            $duty_surcharge = (float)($product['duty_surcharge'] ?? 0);
                                            $taxable_value = (float)($product['taxable_value'] ?? 0);
                                            $gst_amt = (float)($product['gst_amt'] ?? 0);
                                            $total_amt = (float)($product['total_amt'] ?? 0);
                                        } else {
                                            $duty_amt = round($official_total_rs * $duty_percent / 100, 1);
                                            $duty_surcharge = round($duty_amt * 0.10, 1);
                                            $taxable_value = $official_total_rs + $duty_amt + $duty_surcharge;
                                            $gst_amt = round($taxable_value * 0.18, 1);
                                            $total_amt = $duty_amt + $duty_surcharge + $gst_amt;
                                        }

                                        // Derived Totals
                                        $total_rmb = $actual_qty * $actual_rmb;
                                        $total_usd = $actual_qty * $actual_usd;
                                        $total_inr = $actual_qty * $actual_inr;

                                        if ($actual_qty <= 0) continue; 

                                        // accumulate totals
                                        $t_actual_qty          += $actual_qty;
                                        $t_actual_rmb          += $actual_rmb;
                                        $t_total_rmb           += $total_rmb;
                                        $t_actual_usd          += $actual_usd;
                                        $t_total_usd           += $total_usd;
                                        $t_actual_inr          += $actual_inr;
                                        $t_total_inr           += $total_inr;
                                        $t_official_qty        += $official_qty;
                                        $t_official_rate_usd   += (float)($product['official_ci_unit_price_usd'] ?? 0);
                                        $t_official_rate_rs    += $official_rate_rs;
                                        $t_official_total_rs   += $official_total_rs;
                                        $t_duty_amt            += $duty_amt;
                                        $t_duty_surcharge      += $duty_surcharge;
                                        $t_taxable_value       += $taxable_value;
                                        $t_gst_amt             += $gst_amt;
                                        $t_total_amt           += $total_amt;

                                        $g_actual_qty          += $actual_qty;
                                        $g_actual_rmb          += $actual_rmb;
                                        $g_total_rmb           += $total_rmb;
                                        $g_actual_usd          += $actual_usd;
                                        $g_total_usd           += $total_usd;
                                        $g_actual_inr          += $actual_inr;
                                        $g_total_inr           += $total_inr;
                                        $g_official_qty        += $official_qty;
                                        $g_official_rate_usd   += (float)($product['official_ci_unit_price_usd'] ?? 0);
                                        $g_official_rate_rs    += $official_rate_rs;
                                        $g_official_total_rs   += $official_total_rs;
                                        $g_duty_amt            += $duty_amt;
                                        $g_duty_surcharge      += $duty_surcharge;
                                        $g_taxable_value       += $taxable_value;
                                        $g_gst_amt             += $gst_amt;
                                        $g_total_amt           += $total_amt;
                                    ?>
                                    <?php $row_id = (int)($product['id'] ?? 0); ?>
                                    <tr data-product-id="<?php echo $product['product_id']; ?>">
                                        <td class="text-center">
                                            <?php echo $sr_no++; ?>
                                            <input type="hidden" name="invoice_no[]" value="<?php echo $product['invoice_no'] ?? 1; ?>">
                                        </td>

                                        <!-- optional: helps if you want a flat list too -->
                                        <input type="hidden" name="row_id[]" value="<?php echo $row_id; ?>">
                                        <input type="hidden" name="product_id[]" value="<?php echo $product['product_id']; ?>">
                                        <input type="hidden" name="supplier_id_row[]" value="<?php echo $supplier_id; ?>">

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm"
                                            name="product_name[]"
                                            value="<?php echo htmlspecialchars($product_name); ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm"
                                            name="item_code[]"
                                            value="<?php echo htmlspecialchars($item_code); ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <?php if($is_locked): ?>
                                                <div class="text-center text-danger" title="<?php echo $lock_reason; ?>"><i class="fa fa-lock"></i></div>
                                            <?php endif; ?>
                                            <input type="text"
                                            class="form-control form-control-sm text-right actual-qty"
                                            name="actual_qty[]"
                                            value="<?php echo $actual_qty !== 0.0 ? number_format($actual_qty, 0) : ''; ?>"
                                            onkeyup="calculateActual(this)"
                                            <?php echo $is_locked ? 'readonly title="'.$lock_reason.'"' : ''; ?>>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right actual-rmb"
                                            name="actual_rmb[]"
                                            value="<?php echo $actual_rmb !== 0.0 ? $actual_rmb : ''; ?>"
                                            onkeyup="calculateActual(this)">
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right total-rmb"
                                            name="total_rmb[]"
                                            value="<?php echo $total_rmb !== 0.0 ? $total_rmb : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right actual-usd"
                                            name="actual_usd[]"
                                            value="<?php echo $actual_usd !== 0.0 ? $actual_usd : ''; ?>"
                                            onkeyup="calculateActual(this)">
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right total-usd"
                                            name="total_usd[]"
                                            value="<?php echo $total_usd !== 0.0 ? $total_usd : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right actual-inr"
                                            name="actual_inr[]"
                                            value="<?php echo $actual_inr !== 0.0 ? $actual_inr : ''; ?>"
                                            onkeyup="calculateActualINR(this)"
                                            <?php echo $is_locked ? 'readonly title="'.$lock_reason.'"' : ''; ?>>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right total-inr"
                                            name="total_inr[]"
                                            value="<?php echo $total_inr !== 0.0 ? $total_inr : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right official-qty"
                                            name="official_qty[]"
                                            value="<?php echo $official_qty !== 0.0 ? number_format($official_qty, 0) : '0'; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right official-rate-usd"
                                            value="<?php echo number_format((float)$product['official_ci_unit_price_usd'], 2); ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right official-rate"
                                            name="official_rate_rs[]"
                                            value="<?php echo $official_rate_rs !== 0.0 ? number_format($official_rate_rs, 2, '.', '') : '0'; ?>"
                                            data-usd-rate="<?php echo $product['official_ci_unit_price_usd']; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right official-total"
                                            name="official_total_rs[]"
                                            value="<?php echo $official_total_rs !== 0.0 ? number_format($official_total_rs, 2, '.', '') : '0'; ?>"
                                            readonly>
                                        </td>
                                        
                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right duty-percent"
                                            name="duty_percent[]"
                                            value="<?php echo number_format($duty_percent, 1); ?>"
                                            onkeyup="calculateDuty(this)">
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right duty-amt"
                                            name="duty_amt[]"
                                            value="<?php echo $duty_amt !== 0.0 ? number_format($duty_amt, 2, '.', '') : '0'; ?>"
                                            onkeyup="calculateDutyChrg(this)">
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right duty-surcharge"
                                            name="duty_surcharge[]"
                                            value="<?php echo $duty_surcharge !== 0.0 ? number_format($duty_surcharge, 2, '.', '') : '0'; ?>"
                                            onkeyup="calculateDutySur(this)">
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right taxable-value"
                                            name="taxable_value[]"
                                            value="<?php echo $taxable_value !== 0.0 ? number_format($taxable_value, 2, '.', '') : '0'; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right gst-amt"
                                            name="gst_amt[]"
                                            value="<?php echo $gst_amt !== 0.0 ? number_format($gst_amt, 2, '.', '') : '0'; ?>"
                                            onkeyup="calculateGST(this)">
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right total-amt"
                                            name="total_amt[]"
                                            value="<?php echo $total_amt !== 0.0 ? number_format($total_amt, 2, '.', '') : '0'; ?>"
                                            readonly>
                                        </td>
                                        <td class="text-center">
                                            <?php if(!$is_locked): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePurchaseInRow(this, '<?php echo $row_id; ?>')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <?php else: ?>
                                                <span class="badge badge-light-secondary" title="Stock used, cannot delete">Locked</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold js-totals-row">
                                         <td colspan="3" class="text-right">TOTAL</td>
                                         <td class="text-right"><span class="js-sum-actual-qty"><?php echo number_format($t_actual_qty, 0); ?></span></td>
                                         <td class="text-right"><span class="js-sum-actual-rmb"><?php // echo $t_actual_rmb; ?>-</span></td>
                                         <td class="text-right"><span class="js-sum-total-rmb"><?php echo $t_total_rmb; ?></span></td>
                                         <td class="text-right"><span class="js-sum-actual-usd"><?php // echo $t_actual_usd; ?>-</span></td>
                                         <td class="text-right"><span class="js-sum-total-usd"><?php echo $t_total_usd; ?></span></td>
                                         <td class="text-right"><span class="js-sum-actual-inr"><?php // echo $t_actual_inr; ?>-</span></td>
                                         <td class="text-right"><span class="js-sum-total-inr"><?php echo $t_total_inr; ?></span></td>
                                        <td class="text-right"><span class="js-sum-official-qty"><?php echo number_format($t_official_qty, 0); ?></span></td>
                                        <td class="text-right"><span class="js-sum-official-rate-usd"><?php // echo number_format($t_official_rate_usd, 2, '.', ''); ?>-</span></td>
                                        <td class="text-right"><span class="js-sum-official-rate-rs"><?php // echo number_format($t_official_rate_rs, 2, '.', ''); ?>-</span></td>
                                        <td class="text-right"><span class="js-sum-official-total"><?php echo number_format($t_official_total_rs, 2, '.', ''); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right"><span class="js-sum-duty-amt"><?php echo number_format($t_duty_amt, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-duty-surcharge"><?php echo number_format($t_duty_surcharge, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-taxable"><?php echo number_format($t_taxable_value, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-gst"><?php echo number_format($t_gst_amt, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-total-amt"><?php echo number_format($t_total_amt, 2, '.', ''); ?></span></td>
                                        <td class="text-right">-</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-outline-success btn-sm" id="add_supplier_btn">
                        <i class="fa fa-plus-circle"></i> Add Supplier
                    </button>
                </div>

                <div class="supplier-section mb-2" data-supplier-id="<?php echo $supplier_id; ?>">
                    <h5>Grand Total</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm" style="min-width: 2250px;">
                            <thead>
                                <tr>
                                    <th colspan="3" style="width: 450px;">#</th>
                                    <th style="width: 100px;">Actual Qty</th>
                                    <th style="width: 100px;">Actual RMB</th>
                                    <th style="width: 100px;">Total RMB</th>
                                    <th style="width: 100px;">Actual USD</th>
                                    <th style="width: 100px;">Total USD</th>
                                    <th style="width: 100px;">Actual INR</th>
                                    <th style="width: 100px;">Total INR</th>
                                    <th style="width: 100px;">Official Qty</th>
                                    <th style="width: 100px;">Official Rate USD</th>
                                    <th style="width: 100px;">Official Rate Rs.</th>
                                    <th style="width: 100px;">Official Total Rs.</th>
                                    <th style="width: 100px;">Duty %</th>
                                    <th style="width: 100px;">Duty Amt</th>
                                    <th style="width: 100px;">Duty Surcharge 10%</th>
                                    <th style="width: 100px;">Taxable Value</th>
                                    <th style="width: 100px;">GST Amt</th>
                                    <th style="width: 200px;">Total Amt</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="font-weight-bold js-totals-row">
                                    <td colspan="3" class="text-right fw-bold">Total</td>
                                    <td class="text-right"><span id="grand-sum-actual-qty"><?php echo number_format($g_actual_qty, 0); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-actual-rmb">-</span></td>
                                    <!-- <td class="text-right"><span id="grand-sum-actual-rmb"><?php echo number_format($g_actual_rmb, 2, '.', ''); ?></span></td> -->
                                    <td class="text-right"><span id="grand-sum-total-rmb"><?php echo number_format($g_total_rmb, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-actual-usd">-</span></td>
                                    <!-- <td class="text-right"><span id="grand-sum-actual-usd"><?php echo number_format($g_actual_usd, 2, '.', ''); ?></span></td> -->
                                    <td class="text-right"><span id="grand-sum-total-usd"><?php echo number_format($g_total_usd, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-actual-inr">-</span></td>
                                    <!-- <td class="text-right"><span id="grand-sum-actual-inr"><?php echo number_format($g_actual_inr, 2, '.', ''); ?></span></td> -->
                                    <td class="text-right"><span id="grand-sum-total-inr"><?php echo number_format($g_total_inr, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-official-qty"><?php echo number_format($g_official_qty, 0); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-official-rate-usd">-</span></td>
                                    <!-- <td class="text-right"><span id="grand-sum-official-rate-usd"><?php echo number_format($g_official_rate_usd, 2, '.', ''); ?></span></td> -->
                                    <td class="text-right"><span id="grand-sum-official-rate-rs">-</span></td>
                                    <!-- <td class="text-right"><span id="grand-sum-official-rate-rs"><?php echo number_format($g_official_rate_rs, 2, '.', ''); ?></span></td> -->
                                    <td class="text-right"><span id="grand-sum-official-total"><?php echo number_format($g_official_total_rs, 2, '.', ''); ?></span></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right"><span id="grand-sum-duty-amt"><?php echo number_format($g_duty_amt, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-duty-surcharge"><?php echo number_format($g_duty_surcharge, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-taxable"><?php echo number_format($g_taxable_value, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-gst"><?php echo number_format($g_gst_amt, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-total-amt"><?php echo number_format($g_total_amt, 2, '.', ''); ?></span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No loading list data found for this Purchase Order.</div>
            <?php endif; ?>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg btn_verify" name="btn_verify" style="padding: 12px 40px; font-weight: 600;">
                <i class="fa fa-check-circle"></i> Submit
            </button>
        </div>
    </div>
</form>

<!-- Modal -->
</div>

<!-- Modal for Adding Supplier -->
<div class="modal fade inner-modal" id="addSupplierModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-1">
            <input type="text" id="supplier_search" class="form-control" placeholder="Search supplier name..." onkeyup="searchSuppliers()">
        </div>
        <div id="available_suppliers_list" style="max-height: 300px; overflow-y: auto;">
            <!-- Suppliers checkboxes will be loaded here -->
            <div class="text-center p-3">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p>Loading suppliers...</p>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="confirm_add_supplier_btn">Add Selected</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Product Selection -->
<div class="modal fade inner-modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Load Supplier Product</h5>
        <button type="button" class="btn-close" id="close-sub-modal" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
            <input type="text" id="product_search" class="form-control" placeholder="Search product by name or model..." onkeyup="searchProducts()">
        </div>
        <div id="temp-supp-prods" style="max-height: 400px; overflow-y: auto;">
            
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="load-product-btn">Load</button>
      </div>
    </div>
  </div>
</div>


<style>
    .supplier-section h5 {
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 2px solid #5a79c0;
        padding-bottom: 10px;
        margin-bottom: 15px;
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
</style>

<script>
var allSuppliers = {};
<?php foreach($supplier_list as $supplier): ?>
allSuppliers[<?php echo $supplier['id']; ?>] = '<?php echo addslashes($supplier['name']); ?>';
<?php endforeach; ?>

function customRound(val) {
  if (!val && val !== 0) return 0;
  return Math.round(toNum(val) * 10) / 10;
}

function toNum(v) {
  if (v === null || v === undefined) return 0;
  v = ('' + v).replace(/,/g, '').trim();
  var n = parseFloat(v);
  return isNaN(n) ? 0 : n;
}

function setNum($el, n, decimals) {
  if (!$el || !$el.length) return;
  if (decimals === undefined) decimals = 2;
  $el.val((toNum(n)).toFixed(decimals));
}

function fmtQty(n) {
  return Math.round(toNum(n)).toLocaleString('en-IN');
}

function fmtAmt(n) {
  return toNum(n).toFixed(2); // matches your PHP number_format(..., 2, '.', '')
}

function updateTableTotals($table) {
  var sum = {
    actual_qty: 0,
    actual_rmb: 0,
    total_rmb: 0,
    actual_usd: 0,
    total_usd: 0,
    actual_inr: 0,
    total_inr: 0,
    official_qty: 0,
    official_rate_usd: 0,
    official_rate_rs: 0,
    official_total: 0,
    duty_amt: 0,
    duty_surcharge: 0,
    taxable: 0,
    gst: 0,
    total_amt: 0
  };

  $table.find('tbody tr').each(function () {
    var $r = $(this);

    sum.actual_qty     += toNum($r.find('.actual-qty').val());
    sum.actual_rmb     += toNum($r.find('.actual-rmb').val());
    sum.total_rmb      += toNum($r.find('.total-rmb').val());
    sum.actual_usd     += toNum($r.find('.actual-usd').val());
    sum.total_usd      += toNum($r.find('.total-usd').val());
    sum.actual_inr     += toNum($r.find('.actual-inr').val());
    sum.total_inr      += toNum($r.find('.total-inr').val());

    sum.official_qty        += toNum($r.find('.official-qty').val());
    sum.official_rate_usd   += toNum($r.find('.official-rate-usd').val());
    sum.official_rate_rs    += toNum($r.find('.official-rate').val());
    sum.official_total      += toNum($r.find('.official-total').val());

    sum.duty_amt       += toNum($r.find('.duty-amt').val());
    sum.duty_surcharge += toNum($r.find('.duty-surcharge').val());
    sum.taxable        += toNum($r.find('.taxable-value').val());
    sum.gst            += toNum($r.find('.gst-amt').val());
    sum.total_amt      += toNum($r.find('.total-amt').val());
  });

  $table.find('.js-sum-actual-qty').text(fmtQty(sum.actual_qty));
//   $table.find('.js-sum-actual-rmb').text(sum.actual_rmb);
  $table.find('.js-sum-total-rmb').text(toNum(sum.total_rmb));
//   $table.find('.js-sum-actual-usd').text(sum.actual_usd);
  $table.find('.js-sum-total-usd').text(toNum(sum.total_usd));
//   $table.find('.js-sum-actual-inr').text(sum.actual_inr);
  $table.find('.js-sum-total-inr').text(toNum(sum.total_inr));
  $table.find('.js-sum-official-qty').text(fmtQty(sum.official_qty));
//   $table.find('.js-sum-official-rate-usd').text(fmtAmt(sum.official_rate_usd));
//   $table.find('.js-sum-official-rate-rs').text(fmtAmt(sum.official_rate_rs));
  $table.find('.js-sum-official-total').text(fmtAmt(sum.official_total));
  $table.find('.js-sum-duty-amt').text(fmtAmt(sum.duty_amt));
  $table.find('.js-sum-duty-surcharge').text(fmtAmt(sum.duty_surcharge));
  $table.find('.js-sum-taxable').text(fmtAmt(sum.taxable));
  $table.find('.js-sum-gst').text(fmtAmt(sum.gst));
  $table.find('.js-sum-total-amt').text(fmtAmt(sum.total_amt));

  // Grand Total
  const totalActualQty = [...document.querySelectorAll('.actual-qty')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalActualRmb = [...document.querySelectorAll('.actual-rmb')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalRmb = [...document.querySelectorAll('.total-rmb')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalActualUsd = [...document.querySelectorAll('.actual-usd')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalUsd = [...document.querySelectorAll('.total-usd')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalActualInr = [...document.querySelectorAll('.actual-inr')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalInr = [...document.querySelectorAll('.total-inr')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalOfficialQty = [...document.querySelectorAll('.official-qty')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalOfficialRateUsd = [...document.querySelectorAll('.official-rate-usd')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalOfficialRateRs = [...document.querySelectorAll('.official-rate')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalOfficialTotal = [...document.querySelectorAll('.official-total')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalDutyAmt = [...document.querySelectorAll('.duty-amt')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalDutySurcharge = [...document.querySelectorAll('.duty-surcharge')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalTaxableValue = [...document.querySelectorAll('.taxable-value')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalGstAmt = [...document.querySelectorAll('.gst-amt')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalAmt = [...document.querySelectorAll('.total-amt')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);

  $('#grand-sum-actual-qty').text(totalActualQty);
//   $('#grand-sum-actual-rmb').text(totalActualRmb.toFixed(2));
  $('#grand-sum-total-rmb').text(totalRmb.toFixed(2));
//   $('#grand-sum-actual-usd').text(totalActualUsd.toFixed(2));
  $('#grand-sum-total-usd').text(totalUsd.toFixed(2));
//   $('#grand-sum-actual-inr').text(totalActualInr.toFixed(2));
  $('#grand-sum-total-inr').text(totalInr.toFixed(2));
  $('#grand-sum-official-qty').text(totalOfficialQty);
//   $('#grand-sum-official-rate-usd').text(totalOfficialRateUsd.toFixed(2));
//   $('#grand-sum-official-rate-rs').text(totalOfficialRateRs.toFixed(2));
  $('#grand-sum-official-total').text(totalOfficialTotal.toFixed(2));
  $('#grand-sum-duty-amt').text(totalDutyAmt.toFixed(2));
  $('#grand-sum-duty-surcharge').text(totalDutySurcharge.toFixed(2));
  $('#grand-sum-taxable').text(totalTaxableValue.toFixed(2));
  $('#grand-sum-gst').text(totalGstAmt.toFixed(2));
  $('#grand-sum-total-amt').text(totalAmt.toFixed(2));
}

function updateAllSupplierTotals() {
  $('.supplier-section table').each(function () {
    updateTableTotals($(this));
  });
}

function getRow(el) {
  return $(el).closest('tr');
}

function getInrRate() {
  return toNum($('.supplier-inr-rate').val());
}

// Recalculate all "official" columns based on current row + inr rate
function recalcOfficialAndTotals($row) {
  var inrRate = getInrRate();
  if (inrRate <= 0) return;

  var officialQty = toNum($row.find('.official-qty').val());
  var usdRate = toNum($row.find('.official-rate').data('usd-rate')); // USD

  var unitInr = 0;
  var officialTotal = 0;
  var dutyPercent = toNum($row.find('.duty-percent').val()); // use input value (not data-*)
  var dutyAmt = 0;
  var dutySurcharge = 0;
  var taxableValue = 0;
  var gstAmt = 0;
  var totalAmt = 0;

  if (officialQty > 0 && usdRate > 0) {
    unitInr = usdRate * inrRate;           // official_rate_rs
    officialTotal = officialQty * unitInr; // official_total_rs
    dutyAmt = customRound(officialTotal * dutyPercent / 100);
    dutySurcharge = customRound(dutyAmt * 0.10);
    taxableValue = officialTotal + dutyAmt + dutySurcharge;
    gstAmt = customRound(taxableValue * 0.18);
    totalAmt = dutyAmt + dutySurcharge + gstAmt;
  }

  setNum($row.find('.official-rate'), unitInr, 2);
  setNum($row.find('.official-total'), officialTotal, 2);
  setNum($row.find('.duty-amt'), dutyAmt, 1);
  setNum($row.find('.duty-surcharge'), dutySurcharge, 1);
  setNum($row.find('.taxable-value'), taxableValue, 2);
  setNum($row.find('.gst-amt'), gstAmt, 1);
  setNum($row.find('.total-amt'), totalAmt, 2);
  updateTableTotals($row.closest('table'));
}

// Actual Qty -> Total RMB, Total USD, Total INR
function calculateActual(el) {
  var $row = getRow(el);

  var qty = toNum($row.find('.actual-qty').val());
  
  // RMB
  var unitRmb = toNum($row.find('.actual-rmb').val());
  $row.find('.total-rmb').val(toNum(qty * unitRmb));

  // USD
  var unitUsd = toNum($row.find('.actual-usd').val());
  $row.find('.total-usd').val(toNum(qty * unitUsd));

  // INR
  var unitInr = toNum($row.find('.actual-inr').val());
  $row.find('.total-inr').val(toNum(qty * unitInr));

  updateTableTotals($row.closest('table'));
}

function calculateActualINR(el) {
  var $row = getRow(el);
  var qty = toNum($row.find('.actual-qty').val());
  var unitInr = toNum($(el).val());
  $row.find('.total-inr').val(toNum(qty * unitInr));
  updateTableTotals($row.closest('table'));
}

// Duty % changed -> recompute duty, surcharge, taxable, gst, total (full chain)
function calculateDuty(el) {
  var $row = getRow(el);
  recalcOfficialAndTotals($row);
  updateTableTotals($row.closest('table'));
}

// Duty Amt manually changed -> surcharge (10%) + taxable + gst(18%) + total
function calculateDutyChrg(el) {
  var $row = getRow(el);

  var officialTotal = toNum($row.find('.official-total').val());
  var dutyAmt = toNum($row.find('.duty-amt').val());

  var dutySurcharge = customRound(dutyAmt * 0.10);
  var taxableValue = officialTotal + dutyAmt + dutySurcharge;
  var gstAmt = customRound(taxableValue * 0.18);
  var totalAmt = dutyAmt + dutySurcharge + gstAmt;

  setNum($row.find('.duty-surcharge'), dutySurcharge, 1);
  setNum($row.find('.taxable-value'), taxableValue, 2);
  setNum($row.find('.gst-amt'), gstAmt, 1);
  setNum($row.find('.total-amt'), totalAmt, 2);
  updateTableTotals($row.closest('table'));
}

// Duty Surcharge manually changed -> taxable + gst(18%) + total
function calculateDutySur(el) {
  var $row = getRow(el);

  var officialTotal = toNum($row.find('.official-total').val());
  var dutyAmt = toNum($row.find('.duty-amt').val());
  var dutySurcharge = toNum($row.find('.duty-surcharge').val());

  var taxableValue = officialTotal + dutyAmt + dutySurcharge;
  var gstAmt = customRound(taxableValue * 0.18);
  var totalAmt = dutyAmt + dutySurcharge + gstAmt;

  setNum($row.find('.taxable-value'), taxableValue, 2);
  setNum($row.find('.gst-amt'), gstAmt, 1);
  setNum($row.find('.total-amt'), totalAmt, 2);
  updateTableTotals($row.closest('table'));
}

// GST manually changed -> total = taxable + gst
function calculateGST(el) {
  var $row = getRow(el);

  var dutyAmt = toNum($row.find('.duty-amt').val());
  var dutySurcharge = toNum($row.find('.duty-surcharge').val());
  var gstAmt = toNum($row.find('.gst-amt').val());
  var totalAmt = dutyAmt + dutySurcharge + gstAmt;

  setNum($row.find('.total-amt'), totalAmt, 2);
  updateTableTotals($row.closest('table'));
}

$(document).ready(function () {
  // INR Rate change -> update all rows using current duty % input values
  $('.supplier-inr-rate').on('keyup change', function () {
    var inrRate = getInrRate();
    if (inrRate <= 0) return;

    $('tbody tr').each(function () {
      recalcOfficialAndTotals($(this));
    });

    updateAllSupplierTotals();
  });

  $(document).on('click', '#add_supplier_btn', function() {
    var existingSupplierIds = [];
    $('.supplier-section[data-supplier-id]').each(function() {
        var id = $(this).attr('data-supplier-id');
        if (id) existingSupplierIds.push(id.toString());
    });

    var html = '<div class="row">';
    var count = 0;
    for (var id in allSuppliers) {
        if (existingSupplierIds.indexOf(id.toString()) === -1) {
            html += '<div class="col-md-6 mb-2">';
            html += '<div class="form-check">';
            html += '<input class="form-check-input supplier-checkbox" type="checkbox" value="' + id + '" id="supp_' + id + '">';
            html += '<label class="form-check-label ms-2" for="supp_' + id + '">' + allSuppliers[id] + '</label>';
            html += '</div>';
            html += '</div>';
            count++;
        }
    }
    html += '</div>';

    if (count === 0) {
        html = '<div class="alert alert-warning">All available suppliers are already added.</div>';
        $('#confirm_add_supplier_btn').hide();
    } else {
        $('#confirm_add_supplier_btn').show();
    }

    $('#supplier_search').val('');
    $('#available_suppliers_list').html(html);
    $('#addSupplierModal').modal('show');
  });

  $(document).on('click', '#confirm_add_supplier_btn', function() {
    var selectedSuppliers = [];
    $('.supplier-checkbox:checked').each(function() {
        selectedSuppliers.push({
            id: $(this).val(),
            name: allSuppliers[$(this).val()]
        });
    });

    if (selectedSuppliers.length === 0) {
        Swal.fire("Warning!", "Please select at least one supplier.", "warning");
        return;
    }

    selectedSuppliers.forEach(function(supplier) {
        createSupplierSection(supplier.id, supplier.name);
    });

    $('#addSupplierModal').modal('hide');
  });
});

function createSupplierSection(supplierId, supplierName) {
    if ($('.supplier-section[data-supplier-id="' + supplierId + '"]').length > 0) return;

    var sectionHtml = `
    <div class="supplier-section mb-2" data-supplier-id="${supplierId}">
        <h5>
            Supplier: ${supplierName}
            <span class="supplier-header-actions">
                <button type="button" class="btn btn-outline-primary btn-sm supplier-reload-btn" data-supplier-id="${supplierId}" onclick="reloadSupplierProducts(this)">
                    <i class="fa fa-refresh"></i> Add Product
                </button>
            </span>
        </h5>
        <div class="table-responsive supplier-table-container">
            <table class="table table-bordered table-striped table-sm" style="min-width: 2250px;">
                <thead>
                    <tr>
                        <th style="width: 50px;">Sr No.</th>
                        <th style="width: 250px;">Product Name</th>
                        <th style="width: 150px;">Model No.</th>
                        <th style="width: 100px;">Actual Qty</th>
                        <th style="width: 100px;">Actual RMB</th>
                        <th style="width: 100px;">Total RMB</th>
                        <th style="width: 100px;">Actual USD</th>
                        <th style="width: 100px;">Total USD</th>
                        <th style="width: 100px;">Actual INR</th>
                        <th style="width: 100px;">Total INR</th>
                        <th style="width: 100px;">Official Qty</th>
                        <th style="width: 100px;">Official Rate USD</th>
                        <th style="width: 100px;">Official Rate Rs.</th>
                        <th style="width: 100px;">Official Total Rs.</th>
                        <th style="width: 100px;">Duty %</th>
                        <th style="width: 100px;">Duty Amt</th>
                        <th style="width: 100px;">Duty Surcharge 10%</th>
                        <th style="width: 100px;">Taxable Value</th>
                        <th style="width: 100px;">GST Amt</th>
                        <th style="width: 100px;">Total Amt</th>
                        <th style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody class="supplier_products_tbody">
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold js-totals-row">
                        <td colspan="3" class="text-right">TOTAL</td>
                        <td class="text-right"><span class="js-sum-actual-qty">0</span></td>
                        <td class="text-right"><span class="js-sum-actual-rmb">-</span></td>
                        <td class="text-right"><span class="js-sum-total-rmb">0</span></td>
                        <td class="text-right"><span class="js-sum-actual-usd">-</span></td>
                        <td class="text-right"><span class="js-sum-total-usd">0</span></td>
                        <td class="text-right"><span class="js-sum-actual-inr">-</span></td>
                        <td class="text-right"><span class="js-sum-total-inr">0</span></td>
                        <td class="text-right"><span class="js-sum-official-qty">0</span></td>
                        <td class="text-right"><span class="js-sum-official-rate-usd">-</span></td>
                        <td class="text-right"><span class="js-sum-official-rate-rs">-</span></td>
                        <td class="text-right"><span class="js-sum-official-total">0.00</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right"><span class="js-sum-duty-amt">0.00</span></td>
                        <td class="text-right"><span class="js-sum-duty-surcharge">0.00</span></td>
                        <td class="text-right"><span class="js-sum-taxable">0.00</span></td>
                        <td class="text-right"><span class="js-sum-gst">0.00</span></td>
                        <td class="text-right"><span class="js-sum-total-amt">0.00</span></td>
                        <td class="text-right">-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>`;

    var $container = $('.col-md-12.mt-2');
    var $grandTotalSection = $container.find('.supplier-section h5:contains("Grand Total")').closest('.supplier-section');
    
    if ($grandTotalSection.length > 0) {
        $(sectionHtml).insertBefore($grandTotalSection);
    } else {
        $container.find('.alert-info').remove();
        $container.append(sectionHtml);
    }
}

$(document).ready(function() {
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
                } else {	
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

var purchaseInRowCounter = 0;

function reloadSupplierProducts(buttonEl, loadProducts = []) {
    var $btn = $(buttonEl);
    var $section = $btn.closest('.supplier-section');
    var supplierId = $section.data('supplier-id');

    if (!supplierId) {
        Swal.fire("Error!", "Supplier not found for this section.", "error");
        return;
    }

    var existingProductIds = [];
    $section.find('tr').each(function() {
        var pid = $(this).data('product-id');
        if (pid) existingProductIds.push(pid.toString());
    });

    // Duplicate Check with SweetAlert
    if (loadProducts.length > 0) {
        var duplicates = loadProducts.filter(id => existingProductIds.includes(id.toString()));
        if (duplicates.length > 0) {
            Swal.fire({
                title: "Warning!",
                text: "Some selected products already exist in the list. Do you still want to add them?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, add them!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-outline-danger ms-1"
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('#close-sub-modal')?.click();
                    processReloadSupplierProducts(buttonEl, loadProducts, existingProductIds);
                }
            });
            return;
        }
    }

    document.querySelector('#close-sub-modal')?.click();
    processReloadSupplierProducts(buttonEl, loadProducts, existingProductIds);
}

function processReloadSupplierProducts(buttonEl, loadProducts, existingProductIds) {
    var $btn = $(buttonEl);
    var $section = $btn.closest('.supplier-section');
    var supplierId = $section.data('supplier-id');
    var originalHtml = $btn.html();

    $.ajax({
        type: "POST",
        url: "<?php echo base_url(); ?>inventory/get_products_by_supplier",
        data: { supplier_id: supplierId },
        dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).html(originalHtml);
            if (res.status == 200) {
                var mergedProducts = mergeSupplierProducts(res.ready_products || [], res.spare_products || []);
                if(loadProducts.length == 0) {
                    let html = `
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Name</th>
                                <th>Model No</th>
                                <th class="text-end">Cartoon Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;
                    let body = '';
                    mergedProducts.forEach(function(product) {
                        body += `
                            <tr>
                                <td><input type="checkbox" class="product-check" value="${product.id}" data-product-id="${product.id}" /></td>
                                <td>${product.name ?? ""}</td>
                                <td>${product.item_code ?? ""}</td>
                                <td class="text-end">${+product.cartoon_qty ?? ""}</td>
                            </tr>
                        `;
                    });
                    html += body || '<tr><td colspan="4" class="text-center">No Products Found</td></tr>';
                    html += `</tbody></table>`;
                    $('#product_search').val('');
                    $("#temp-supp-prods").html(html);
                    $('#staticBackdrop').modal('show');
                    $("#load-product-btn").attr('onclick', `supplierProducts(${supplierId})`);
                } else {
                    loadProducts.forEach(function(pId) {
                        var product = mergedProducts.find(p => p.id == pId);
                        if (product) appendPurchaseInProductRow($section, product);
                    });
                    updateSupplierRowNumbers($section);
                    updateAllSupplierTotals();
                }
            }
        }
    });
}

function supplierProducts(id) {
    let value = [];
    $('.product-check:checked').each(function() { value.push($(this).val()); });
    if(value.length == 0) {
        Swal.fire("Error!", "Select at least 1 Product", "error");
    } else {
        reloadSupplierProducts(document.querySelector(`[data-supplier-id="${id}"] .btn-outline-primary`), value);
        $('#staticBackdrop').modal('hide');
    }
}

function mergeSupplierProducts(readyProducts, spareProducts) {
    var merged = [];
    var seen = {};
    (readyProducts || []).concat(spareProducts || []).forEach(p => {
        if (p.id && !seen[p.id]) { seen[p.id] = true; merged.push(p); }
    });
    return merged;
}

function updateAllSupplierTotals() {
    var $tables = $('table');
    if ($tables.length > 0) {
        updateTableTotals($tables.first());
    }
}

function updateSupplierRowNumbers($section) {
    $section.find('tbody tr').each((i, row) => {
        $(row).find('td:first').contents().filter(function() {
            return this.nodeType === 3; // Text node
        }).first().replaceWith((i + 1).toString());
    });
}

function appendPurchaseInProductRow($section, p) {
    purchaseInRowCounter++;
    var rowKey = 'new_' + purchaseInRowCounter;
    var inrRate = getInrRate();
    var actualUsdRate = parseFloat(p.actual_usd_rate) || 0;
    var officialUsdRate = parseFloat(p.usd_rate) || 0;
    var officialRateInr = officialUsdRate * inrRate;

    var html = `
    <tr data-product-id="${p.id}" data-new-row="true">
        <td class="text-center">
            0
            <input type="hidden" name="invoice_no[]" value="1">
        </td>
        <input type="hidden" name="row_id[]" value="0">
        <input type="hidden" name="product_id[]" value="${p.id}">
        <input type="hidden" name="supplier_id_row[]" value="${$section.data('supplier-id')}">
        <td><input type="text" class="form-control form-control-sm" name="product_name[]" value="${p.name}" readonly></td>
        <td><input type="text" class="form-control form-control-sm" name="item_code[]" value="${p.item_code}" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right actual-qty" name="actual_qty[]" value="0" onkeyup="calculateActual(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right actual-rmb" name="actual_rmb[]" value="${parseFloat(p.rate || 0)}" onkeyup="calculateActual(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right total-rmb" name="total_rmb[]" value="0" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right actual-usd" name="actual_usd[]" value="${actualUsdRate}" onkeyup="calculateActual(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right total-usd" name="total_usd[]" value="0" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right actual-inr" name="actual_inr[]" value="0" onkeyup="calculateActualINR(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right total-inr" name="total_inr[]" value="0" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right official-qty" name="official_qty[]" value="0" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right official-rate-usd" value="${officialUsdRate.toFixed(2)}" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right official-rate" name="official_rate_rs[]" value="${officialRateInr.toFixed(2)}" data-usd-rate="${officialUsdRate}" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right official-total" name="official_total_rs[]" value="0.00" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right duty-percent" name="duty_percent[]" value="${parseFloat(p.duty_charge || 0).toFixed(1)}" onkeyup="calculateDuty(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right duty-amt" name="duty_amt[]" value="0.00" onkeyup="calculateDutyChrg(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right duty-surcharge" name="duty_surcharge[]" value="0.00" onkeyup="calculateDutySur(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right taxable-value" name="taxable_value[]" value="0.00" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right gst-amt" name="gst_amt[]" value="0.00" onkeyup="calculateGST(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right total-amt" name="total_amt[]" value="0.00" readonly></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePurchaseInRow(this, '0')">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>`;
    $section.find('tbody').append(html);
}

function removePurchaseInRow(btn, rowId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Remove this item?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it!',
        buttonsStyling: false,
        customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-outline-danger ms-1' }
    }).then((result) => {
        if (result.isConfirmed) {
            var $row = $(btn).closest('tr');
            var $table = $row.closest('table');
            if (rowId == '0' || $row.data('new-row')) {
                $row.remove();
                updateAllSupplierTotals();
            } else {
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>inventory/delete_loading_list_item",
                    data: { id: rowId },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 200) {
                            $row.remove();
                            updateAllSupplierTotals();
                            Swal.fire('Removed!', '', 'success');
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    }
                });
            }
        }
    });
}

function searchSuppliers() {
    var input = document.getElementById("supplier_search");
    var filter = input.value.toLowerCase();
    var list = document.getElementById("available_suppliers_list");
    var items = list.getElementsByClassName("col-md-6");

    for (var i = 0; i < items.length; i++) {
        var label = items[i].getElementsByClassName("form-check-label")[0];
        if (label) {
            var txtValue = label.textContent || label.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }
}

function searchProducts() {
    var input = document.getElementById("product_search");
    var filter = input.value.toLowerCase();
    var table = document.querySelector("#temp-supp-prods table");
    if (!table) return;
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) { // Skip header
        var tdName = tr[i].getElementsByTagName("td")[1]; // Product Name
        var tdModel = tr[i].getElementsByTagName("td")[3]; // Model No
        if (tdName || tdModel) {
            var txtValueName = tdName.textContent || tdName.innerText;
            var txtValueModel = tdModel.textContent || tdModel.innerText;
            if (txtValueName.toLowerCase().indexOf(filter) > -1 || txtValueModel.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>
