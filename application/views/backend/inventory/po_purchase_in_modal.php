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

$products_raw = $this->db
    ->query("
        SELECT 
            pop.*,
            s.name AS supplier_name,
            rp.actual_usd_rate,
            inv.quantity AS inv_qty
        FROM po_products pop
        LEFT JOIN supplier s ON s.id = pop.supplier_id
        LEFT JOIN raw_products rp ON rp.id = pop.product_id
        LEFT JOIN inventory inv ON inv.product_id = pop.product_id 
             AND inv.batch_no = (SELECT voucher_no FROM purchase_order WHERE id = '$po_id')
             AND inv.warehouse_id = (SELECT warehouse_id FROM purchase_order WHERE id = '$po_id')
        WHERE pop.parent_id = '$po_id' AND pop.loading_qty > 0
        ORDER BY pop.supplier_id ASC, pop.id ASC
    ")
    ->result_array();

$supplier_products = [];
foreach ($products_raw as $product) {
    $supplier_id = isset($product['supplier_id']) ? $product['supplier_id'] : 0;
    if (!isset($supplier_products[$supplier_id])) {
        $supplier_products[$supplier_id] = [
            'supplier_name' => isset($product['supplier_name']) ? $product['supplier_name'] : 'Unknown Supplier',
            'products' => []
        ];
    }
    $supplier_products[$supplier_id]['products'][] = $product;
}
?>

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
                value="<?php echo $po_raw['boe_date'] ?? ''; ?>"
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
                    $g_total_rmb         = 0;
                    $g_total_usd         = 0;
                    $g_total_inr         = 0;
                    $g_official_qty      = 0;
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
                            <span class="supplier-header-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm supplier-reload-btn" data-supplier-id="<?php echo $supplier_id; ?>" onclick="reloadSupplierProducts(this)">
                                    <i class="fa fa-refresh"></i> Add Product
                                </button>
                            </span>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Sr No.</th>
                                        <th>Invoice#</th>
                                        <th>Product Name</th>
                                        <th>Model No.</th>
                                        <th>Actual Qty</th>
                                        <th>Actual RMB</th>
                                        <th>Total RMB</th>
                                        <th>Actual USD</th>
                                        <th>Total USD</th>
                                        <th>Actual INR</th>
                                        <th>Total INR</th>
                                        <th>Official Qty</th>
                                        <th>Official Rate USD</th>
                                        <th>Official Rate Rs.</th>
                                        <th>Official Total Rs.</th>
                                        <th>Duty %</th>
                                        <th>Duty Amt</th>
                                        <th>Duty Surcharge 10%</th>
                                        <th>Taxable Value</th>
                                        <th>GST Amt</th>
                                        <th>Total Amt</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sr_no = 1;

                                    // totals for this supplier table
                                    $t_actual_qty        = 0;
                                    $t_total_rmb         = 0;
                                    $t_total_usd         = 0;
                                    $t_total_inr         = 0;
                                    $t_official_qty      = 0;
                                    $t_official_total_rs = 0;
                                    $t_duty_amt          = 0;
                                    $t_duty_surcharge    = 0;
                                    $t_taxable_value     = 0;
                                    $t_gst_amt           = 0;
                                    $t_total_amt         = 0;
                                    foreach ($supplier_data['products'] as $ind => $product):
                                        $inr_rate = isset($po_raw['inr_rate']) ? (float)$po_raw['inr_rate'] : 0;
                                        $actual_qty = isset($product['loading_qty']) ? (float)$product['loading_qty'] : 0;
                                        if ($actual_qty <= 0) {
                                            continue;
                                        }

                                        $product_name = isset($product['product_name']) ? $product['product_name'] : '';
                                        $item_code = isset($product['item_code']) ? $product['item_code'] : '';

                                        $actual_rmb = isset($product['unit_price_rmb']) ? (float)$product['unit_price_rmb'] : 0;
                                        $total_rmb = $actual_qty * $actual_rmb;

                                        $official_qty = isset($product['official_ci_qty']) ? (float)$product['official_ci_qty'] : 0;
                                        $official_rate_rs = isset($product['official_ci_unit_price_usd']) ? ((float)$product['official_ci_unit_price_usd'] * $inr_rate) : 0;

                                        $official_total_rs = $official_qty * $official_rate_rs;

                                        // New Actual Fields
                                        $actual_usd = isset($product['actual_usd']) && $product['actual_usd'] > 0 ? (float)$product['actual_usd'] : (isset($product['actual_usd_rate']) ? (float)$product['actual_usd_rate'] : 0);
                                        $actual_inr = isset($product['actual_inr']) ? (float)$product['actual_inr'] : 0;
                                        
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

                                        $actual_qty = ($po_raw['delivery_status'] == 'purchase_in') ? (float)($product['actual_qty'] ?? 0) : (float)($product['loading_qty'] ?? 0);
                                        $total_rmb = $actual_qty * $actual_rmb;
                                        $total_usd = $actual_qty * $actual_usd;
                                        $total_inr = $actual_qty * $actual_inr;

                                        $duty_percent = 7.5;
                                        $duty_amt = $official_total_rs * $duty_percent / 100;
                                        $duty_surcharge = $duty_amt * 0.10;
                                        $taxable_value = $official_total_rs + $duty_amt + $duty_surcharge;
                                        $gst_amt = $taxable_value * 0.18;
                                        $total_amt = $taxable_value + $gst_amt;

                                        // accumulate totals
                                        $t_actual_qty        += $actual_qty;
                                        $t_total_rmb         += $total_rmb;
                                        $t_total_usd         += $total_usd;
                                        $t_total_inr         += $total_inr;
                                        $t_official_qty      += $official_qty;
                                        $t_official_total_rs += $official_total_rs;
                                        $t_duty_amt          += $duty_amt;
                                        $t_duty_surcharge    += $duty_surcharge;
                                        $t_taxable_value     += $taxable_value;
                                        $t_gst_amt           += $gst_amt;
                                        $t_total_amt         += $total_amt;

                                        $g_actual_qty          += $actual_qty;
                                        $g_total_rmb           += $total_rmb;
                                        $g_total_usd           += $total_usd;
                                        $g_total_inr           += $total_inr;
                                        $g_official_qty        += $official_qty;
                                        $g_official_total_rs   += $official_total_rs;
                                        $g_duty_amt            += $duty_amt;
                                        $g_duty_surcharge      += $duty_surcharge;
                                        $g_taxable_value       += $taxable_value;
                                        $g_gst_amt             += $gst_amt;
                                        $g_total_amt           += $total_amt;
                                    ?>
                                    <?php $row_id = (int)($product['id'] ?? 0); ?>
                                    <tr>
                                        <td class="text-center"><?php echo $sr_no++; ?></td>
                                        <td>
                                            <select class="form-control form-control-sm invoice-select" name="invoice_no[]" <?php echo $is_locked ? 'disabled' : ''; ?>>
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo (isset($product['invoice_no']) && $product['invoice_no'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <?php if($is_locked): ?>
                                                <input type="hidden" name="invoice_no[]" value="<?php echo $product['invoice_no'] ?? 1; ?>">
                                            <?php endif; ?>
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
                                            value="<?php echo $actual_rmb !== 0.0 ? number_format($actual_rmb, 2, '.', '') : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right total-rmb"
                                            name="total_rmb[]"
                                            value="<?php echo $total_rmb !== 0.0 ? number_format($total_rmb, 2, '.', '') : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right actual-usd"
                                            name="actual_usd[]"
                                            value="<?php echo $actual_usd !== 0.0 ? number_format($actual_usd, 2, '.', '') : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right total-usd"
                                            name="total_usd[]"
                                            value="<?php echo $total_usd !== 0.0 ? number_format($total_usd, 2, '.', '') : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right actual-inr"
                                            name="actual_inr[]"
                                            value="<?php echo $actual_inr !== 0.0 ? number_format($actual_inr, 2, '.', '') : ''; ?>"
                                            onkeyup="calculateActualINR(this)"
                                            <?php echo $is_locked ? 'readonly title="'.$lock_reason.'"' : ''; ?>>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right total-inr"
                                            name="total_inr[]"
                                            value="<?php echo $total_inr !== 0.0 ? number_format($total_inr, 2, '.', '') : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right official-qty"
                                            name="official_qty[]"
                                            value="<?php echo $official_qty !== 0.0 ? number_format($official_qty, 0) : ''; ?>"
                                            readonly>
                                        </td>

                                        <td>
                                            <input type="text"
                                            class="form-control form-control-sm text-right"
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
                                        <td colspan="4" class="text-right">TOTAL</td>
                                        <td class="text-right"><span class="js-sum-actual-qty"><?php echo number_format($t_actual_qty, 0); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right"><span class="js-sum-total-rmb"><?php echo number_format($t_total_rmb, 2, '.', ''); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right"><span class="js-sum-total-usd"><?php echo number_format($t_total_usd, 2, '.', ''); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right"><span class="js-sum-total-inr"><?php echo number_format($t_total_inr, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-official-qty"><?php echo number_format($t_official_qty, 0); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right">-</td>
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

                <div class="supplier-section mb-2" data-supplier-id="<?php echo $supplier_id; ?>">
                    <h5>Grand Total</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th colspan="4" style="width: 225px;">#</th>
                                    <th>Actual Qty</th>
                                    <th>Actual RMB</th>
                                    <th>Total RMB</th>
                                    <th>Actual USD</th>
                                    <th>Total USD</th>
                                    <th>Actual INR</th>
                                    <th>Total INR</th>
                                    <th>Official Qty</th>
                                    <th>Official Rate USD</th>
                                    <th>Official Rate Rs.</th>
                                    <th>Official Total Rs.</th>
                                    <th>Duty %</th>
                                    <th>Duty Amt</th>
                                    <th>Duty Surcharge 10%</th>
                                    <th>Taxable Value</th>
                                    <th>GST Amt</th>
                                    <th>Total Amt</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="font-weight-bold js-totals-row">
                                    <td colspan="4" class="text-right fw-bold">Total</td>
                                    <td class="text-right"><span id="grand-sum-actual-qty"><?php echo number_format($g_actual_qty, 0); ?></span></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right"><span id="grand-sum-total-rmb"><?php echo number_format($g_total_rmb, 2, '.', ''); ?></span></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right"><span id="grand-sum-total-usd"><?php echo number_format($g_total_usd, 2, '.', ''); ?></span></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right"><span id="grand-sum-total-inr"><?php echo number_format($g_total_inr, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-official-qty"><?php echo number_format($g_official_qty, 0); ?></span></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right"><span id="grand-sum-official-total"><?php echo number_format($g_official_total_rs, 2, '.', ''); ?></span></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right"><span id="grand-sum-duty-amt"><?php echo number_format($g_duty_amt, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-duty-surcharge"><?php echo number_format($g_duty_surcharge, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-taxable"><?php echo number_format($g_taxable_value, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-gst"><?php echo number_format($g_gst_amt, 2, '.', ''); ?></span></td>
                                    <td class="text-right"><span id="grand-sum-total-amt"><?php echo number_format($g_total_amt, 2, '.', ''); ?></span></td>
                                    <td class="text-right">-</td>
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
<div class="modal fade inner-modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Load Supplier Product</h5>
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
    total_rmb: 0,
    total_usd: 0,
    total_inr: 0,
    official_qty: 0,
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
    sum.total_rmb      += toNum($r.find('.total-rmb').val());
    sum.total_usd      += toNum($r.find('.total-usd').val());
    sum.total_inr      += toNum($r.find('.total-inr').val());

    sum.official_qty   += toNum($r.find('.official-qty').val());
    sum.official_total += toNum($r.find('.official-total').val());

    sum.duty_amt       += toNum($r.find('.duty-amt').val());
    sum.duty_surcharge += toNum($r.find('.duty-surcharge').val());
    sum.taxable        += toNum($r.find('.taxable-value').val());
    sum.gst            += toNum($r.find('.gst-amt').val());
    sum.total_amt      += toNum($r.find('.total-amt').val());
  });

  $table.find('.js-sum-actual-qty').text(fmtQty(sum.actual_qty));
  $table.find('.js-sum-total-rmb').text(fmtAmt(sum.total_rmb));
  $table.find('.js-sum-total-usd').text(fmtAmt(sum.total_usd));
  $table.find('.js-sum-total-inr').text(fmtAmt(sum.total_inr));
  $table.find('.js-sum-official-qty').text(fmtQty(sum.official_qty));
  $table.find('.js-sum-official-total').text(fmtAmt(sum.official_total));
  $table.find('.js-sum-duty-amt').text(fmtAmt(sum.duty_amt));
  $table.find('.js-sum-duty-surcharge').text(fmtAmt(sum.duty_surcharge));
  $table.find('.js-sum-taxable').text(fmtAmt(sum.taxable));
  $table.find('.js-sum-gst').text(fmtAmt(sum.gst));
  $table.find('.js-sum-total-amt').text(fmtAmt(sum.total_amt));

  // Grand Total
  const totalActualQty = [...document.querySelectorAll('.actual-qty')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalRmb = [...document.querySelectorAll('.total-rmb')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalUsd = [...document.querySelectorAll('.total-usd')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalInr = [...document.querySelectorAll('.total-inr')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalOfficialQty = [...document.querySelectorAll('.official-qty')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalOfficialTotal = [...document.querySelectorAll('.official-total')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalDutyAmt = [...document.querySelectorAll('.duty-amt')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalDutySurcharge = [...document.querySelectorAll('.duty-surcharge')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalTaxableValue = [...document.querySelectorAll('.taxable-value')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalGstAmt = [...document.querySelectorAll('.gst-amt')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
  const totalAmt = [...document.querySelectorAll('.total-amt')].reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);

  $('#grand-sum-actual-qty').text(totalActualQty);
  $('#grand-sum-total-rmb').text(totalRmb);
  $('#grand-sum-total-usd').text(totalUsd);
  $('#grand-sum-total-inr').text(totalInr);
  $('#grand-sum-official-qty').text(totalOfficialQty);
  $('#grand-sum-official-total').text(totalOfficialTotal);
  $('#grand-sum-duty-amt').text(totalDutyAmt);
  $('#grand-sum-duty-surcharge').text(totalDutySurcharge);
  $('#grand-sum-taxable').text(totalTaxableValue);
  $('#grand-sum-gst').text(totalGstAmt);
  $('#grand-sum-total-amt').text(totalAmt);
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
  if (officialQty <= 0 || usdRate <= 0) return;

  var unitInr = usdRate * inrRate;           // official_rate_rs
  var officialTotal = officialQty * unitInr; // official_total_rs

  var dutyPercent = toNum($row.find('.duty-percent').val()); // use input value (not data-*)
  var dutyAmt = officialTotal * dutyPercent / 100;
  var dutySurcharge = dutyAmt * 0.10;
  var taxableValue = officialTotal + dutyAmt + dutySurcharge;
  var gstAmt = taxableValue * 0.18;
  var totalAmt = taxableValue + gstAmt;

  setNum($row.find('.official-rate'), unitInr, 2);
  setNum($row.find('.official-total'), officialTotal, 2);
  setNum($row.find('.duty-amt'), dutyAmt, 2);
  setNum($row.find('.duty-surcharge'), dutySurcharge, 2);
  setNum($row.find('.taxable-value'), taxableValue, 2);
  setNum($row.find('.gst-amt'), gstAmt, 2);
  setNum($row.find('.total-amt'), totalAmt, 2);
  updateTableTotals($row.closest('table'));
}

// Actual Qty -> Total RMB, Total USD, Total INR
function calculateActual(el) {
  var $row = getRow(el);

  var qty = toNum($row.find('.actual-qty').val());
  
  // RMB
  var unitRmb = toNum($row.find('.actual-rmb').val());
  setNum($row.find('.total-rmb'), qty * unitRmb, 2);

  // USD
  var unitUsd = toNum($row.find('.actual-usd').val());
  setNum($row.find('.total-usd'), qty * unitUsd, 2);

  // INR
  var unitInr = toNum($row.find('.actual-inr').val());
  setNum($row.find('.total-inr'), qty * unitInr, 2);

  updateTableTotals($row.closest('table'));
}

function calculateActualINR(el) {
  var $row = getRow(el);
  var qty = toNum($row.find('.actual-qty').val());
  var unitInr = toNum($(el).val());
  setNum($row.find('.total-inr'), qty * unitInr, 2);
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

  var dutySurcharge = dutyAmt * 0.10;
  var taxableValue = officialTotal + dutyAmt + dutySurcharge;
  var gstAmt = taxableValue * 0.18;
  var totalAmt = taxableValue + gstAmt;

  setNum($row.find('.duty-surcharge'), dutySurcharge, 2);
  setNum($row.find('.taxable-value'), taxableValue, 2);
  setNum($row.find('.gst-amt'), gstAmt, 2);
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
  var gstAmt = taxableValue * 0.18;
  var totalAmt = taxableValue + gstAmt;

  setNum($row.find('.taxable-value'), taxableValue, 2);
  setNum($row.find('.gst-amt'), gstAmt, 2);
  setNum($row.find('.total-amt'), totalAmt, 2);
  updateTableTotals($row.closest('table'));
}

// GST manually changed -> total = taxable + gst
function calculateGST(el) {
  var $row = getRow(el);

  var taxableValue = toNum($row.find('.taxable-value').val());
  var gstAmt = toNum($row.find('.gst-amt').val());
  var totalAmt = taxableValue + gstAmt;

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
});

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
    document.querySelector('#close-sub-modal')?.click();
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
                    processReloadSupplierProducts(buttonEl, loadProducts, existingProductIds);
                }
            });
            return;
        }
    }

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
                    $('.inner-modal').modal('show');
                    $("#temp-supp-prods").html(html);
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
        $('.inner-modal').modal('hide');
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

function updateSupplierRowNumbers($section) {
    $section.find('tbody tr').each((i, row) => $(row).find('td:first').text(i + 1));
}

function appendPurchaseInProductRow($section, p) {
    purchaseInRowCounter++;
    var rowKey = 'new_' + purchaseInRowCounter;
    var inrRate = getInrRate();
    var usdRate = parseFloat(p.usd_rate) || 0;
    var rateInr = usdRate * inrRate;

    var html = `
    <tr data-product-id="${p.id}" data-new-row="true">
        <td class="text-center">0</td>
        <td>
            <select class="form-control form-control-sm invoice-select" name="invoice_no[]">
                <option value="1" selected>1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </td>
        <input type="hidden" name="row_id[]" value="0">
        <input type="hidden" name="product_id[]" value="${p.id}">
        <input type="hidden" name="supplier_id_row[]" value="${$section.data('supplier-id')}">
        <td><input type="text" class="form-control form-control-sm" name="product_name[]" value="${p.name}" readonly></td>
        <td><input type="text" class="form-control form-control-sm" name="item_code[]" value="${p.item_code}" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right actual-qty" name="actual_qty[]" value="0" onkeyup="calculateActual(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-right actual-rmb" name="actual_rmb[]" value="${parseFloat(p.rate || 0).toFixed(2)}" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right total-rmb" name="total_rmb[]" value="0.00" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right official-qty" name="official_qty[]" value="0" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right" value="${usdRate.toFixed(2)}" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right official-rate" name="official_rate_rs[]" value="${rateInr.toFixed(2)}" data-usd-rate="${usdRate}" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right official-total" name="official_total_rs[]" value="0.00" readonly></td>
        <td><input type="text" class="form-control form-control-sm text-right duty-percent" name="duty_percent[]" value="7.5" onkeyup="calculateDuty(this)"></td>
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
</script>
