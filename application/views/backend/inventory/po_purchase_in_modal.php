<?php
$po_id = $param2;

$po_raw = $this->db
    ->query("
        SELECT 
            inr_rate
        FROM purchase_order
        WHERE id = '$po_id'
    ")
    ->row_array();

$products_raw = $this->db
    ->query("
        SELECT 
            pop.*,
            s.name AS supplier_name
        FROM po_products pop
        LEFT JOIN supplier s ON s.id = pop.supplier_id
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
                value=""
                required
            >
        </div>
        <div class="col-3 d-flex">
            <label class="mr-2 mb-0">BOE Date <span class="text-danger">*</span></label>
            <input
                type="date"
                name="boe_date"
                class="form-control form-control-sm text-right"
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
                <?php foreach ($supplier_products as $supplier_id => $supplier_data): ?>
                    <div class="supplier-section mb-2" data-supplier-id="<?php echo $supplier_id; ?>">
                        <h5>Supplier: <?php echo htmlspecialchars($supplier_data['supplier_name']); ?></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Sr No.</th>
                                        <th>Product Name</th>
                                        <th>Model No.</th>
                                        <th>Actual Qty</th>
                                        <th>Actual RMB</th>
                                        <th>Total RMB</th>
                                        <th>Official Qty</th>
                                        <th>Official Rate Rs.</th>
                                        <th>Official Total Rs.</th>
                                        <th>Duty %</th>
                                        <th>Duty Amt</th>
                                        <th>Duty Surcharge 10%</th>
                                        <th>Taxable Value</th>
                                        <th>GST Amt</th>
                                        <th>Total Amt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sr_no = 1;

                                    // totals for this supplier table
                                    $t_actual_qty        = 0;
                                    $t_total_rmb         = 0;
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

                                        $duty_percent = 7.5;
                                        $duty_amt = $official_total_rs * $duty_percent / 100;
                                        $duty_surcharge = $duty_amt * 0.10;
                                        $taxable_value = $official_total_rs + $duty_amt + $duty_surcharge;
                                        $gst_amt = $taxable_value * 0.18;
                                        $total_amt = $taxable_value + $gst_amt;

                                        // accumulate totals
                                        $t_actual_qty        += $actual_qty;
                                        $t_total_rmb         += $total_rmb;
                                        $t_official_qty      += $official_qty;
                                        $t_official_total_rs += $official_total_rs;
                                        $t_duty_amt          += $duty_amt;
                                        $t_duty_surcharge    += $duty_surcharge;
                                        $t_taxable_value     += $taxable_value;
                                        $t_gst_amt           += $gst_amt;
                                        $t_total_amt         += $total_amt;
                                    ?>
                                    <?php $row_id = (int)($product['id'] ?? 0); ?>
                                    <tr>
                                        <td class="text-center"><?php echo $sr_no++; ?></td>

                                        <!-- optional: helps if you want a flat list too -->
                                        <input type="hidden" name="row_id[]" value="<?php echo $row_id; ?>">

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
                                            <input type="text"
                                            class="form-control form-control-sm text-right actual-qty"
                                            name="actual_qty[]"
                                            value="<?php echo $actual_qty !== 0.0 ? number_format($actual_qty, 0) : ''; ?>"
                                            onkeyup="calculateActual(this)">
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
                                            class="form-control form-control-sm text-right official-qty"
                                            name="official_qty[]"
                                            value="<?php echo $official_qty !== 0.0 ? number_format($official_qty, 0) : ''; ?>"
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
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold js-totals-row">
                                        <td colspan="3" class="text-right">TOTAL</td>
                                        <td class="text-right"><span class="js-sum-actual-qty"><?php echo number_format($t_actual_qty, 0); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right"><span class="js-sum-total-rmb"><?php echo number_format($t_total_rmb, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-official-qty"><?php echo number_format($t_official_qty, 0); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right"><span class="js-sum-official-total"><?php echo number_format($t_official_total_rs, 2, '.', ''); ?></span></td>
                                        <td class="text-right">-</td>
                                        <td class="text-right"><span class="js-sum-duty-amt"><?php echo number_format($t_duty_amt, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-duty-surcharge"><?php echo number_format($t_duty_surcharge, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-taxable"><?php echo number_format($t_taxable_value, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-gst"><?php echo number_format($t_gst_amt, 2, '.', ''); ?></span></td>
                                        <td class="text-right"><span class="js-sum-total-amt"><?php echo number_format($t_total_amt, 2, '.', ''); ?></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
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

  $table.find('.js-sum-official-qty').text(fmtQty(sum.official_qty));
  $table.find('.js-sum-official-total').text(fmtAmt(sum.official_total));

  $table.find('.js-sum-duty-amt').text(fmtAmt(sum.duty_amt));
  $table.find('.js-sum-duty-surcharge').text(fmtAmt(sum.duty_surcharge));
  $table.find('.js-sum-taxable').text(fmtAmt(sum.taxable));
  $table.find('.js-sum-gst').text(fmtAmt(sum.gst));
  $table.find('.js-sum-total-amt').text(fmtAmt(sum.total_amt));
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

// Actual Qty -> Total RMB
function calculateActual(el) {
  var $row = getRow(el);

  var qty = toNum($row.find('.actual-qty').val());
  var unitRmb = toNum($row.find('.actual-rmb').val());
  var totalRmb = qty * unitRmb;

  setNum($row.find('.total-rmb'), totalRmb, 2);
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
</script>
