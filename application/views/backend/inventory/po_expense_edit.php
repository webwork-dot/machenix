<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<?php
// CI3 view-safe defaults
$po_exp = (isset($data) && is_array($data)) ? $data : [];
$details = (isset($lists) && is_array($lists)) ? $lists : [];

$other_charges_list = isset($other_charges) && is_array($other_charges) ? $other_charges : [];
$charges_options = '<option value="">Select Charge</option>';
foreach ($other_charges_list as $charge) {
    $charges_options .= '<option value="' . $charge['id'] . '" data-gst="' . $charge['gst'] . '" data-name="' . html_escape($charge['name']) . '">' . html_escape($charge['name']) . '</option>';
}
$selected_batch   = $po_exp['batch_no']      ?? '';
// some projects store vendor in vendor_id, some in company_id — keep your form name as company_id
$selected_vendor  = $po_exp['vendor_id']     ?? ($po_exp['company_id'] ?? '');
$selected_type    = $po_exp['type']          ?? '';
$selected_expense = $po_exp['expense_type']  ?? '';
$selected_gsttype = $po_exp['gst_type']      ?? '';
$selected_suppliers = !empty($po_exp['supplier_id']) ? explode(',', $po_exp['supplier_id']) : [];

$expense_date      = !empty($po_exp['expense_date'])      ? $po_exp['expense_date']      : date('Y-m-d');
$narration        = $po_exp['narration'] ?? '';

$sub_total  = isset($po_exp['sub_total'])   ? number_format((float)$po_exp['sub_total'], 2, '.', '')   : '';
$gst_total  = isset($po_exp['gst_total'])   ? number_format((float)$po_exp['gst_total'], 2, '.', '')   : '';
$grand_total= isset($po_exp['grand_total']) ? number_format((float)$po_exp['grand_total'], 2, '.', '') : '';

$input_method_val = $po_exp['input_method'] ?? ($type ?? '');
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/po_expense/edit_post/'.$id, [
          'class' => 'add-ajax-redirect-form',
          'onsubmit' => 'return checkForm(this);'
        ]);?>

        <div class="row">
          <input type="hidden" name="input_method" value="<?php echo html_escape($input_method_val); ?>">

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Batch No <span class="required">*</span></label>
              <select class="form-control select2" name="batch_no" id="batch_no" required>
                <option value="">Select</option>
                <?php foreach ($po as $key => $value): ?>
                  <?php $vno = $value['voucher_no']; ?>
                  <option value="<?php echo html_escape($vno); ?>"
                    <?php echo ($selected_batch == $vno) ? 'selected' : ''; ?>>
                    <?php echo html_escape($vno); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Suppliers <span class="required">*</span></label>
              <select class="form-control select2" name="supplier_id[]" id="supplier_id" multiple="multiple" required>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Vendor <span class="required">*</span></label>
              <select class="form-control select2" name="company_id" id="company_id" required>
                <option value="">Select</option>
                <?php foreach ($company_list as $key => $value): ?>
                  <option value="<?php echo (int)$value['id']; ?>"
                    data-state-id="<?php echo $value['state_id'];?>"
                    <?php echo ((string)$selected_vendor === (string)$value['id']) ? 'selected' : ''; ?>>
                    <?php echo html_escape($value['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Purchase No</label>
              <input type="text" class="form-control" name="purchase_no" id="purchase_no" placeholder="Purchase No" value="<?php echo html_escape($po_exp['purchase_no'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Purchase Date</label>
              <input type="date" class="form-control" name="purchase_date" id="purchase_date" value="<?php echo html_escape($po_exp['purchase_date'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Dollar USD</label>
              <input type="number" class="form-control" name="usd" id="usd" placeholder="0.00" step="0.01" min="0" value="<?php echo html_escape($po_exp['usd'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Type <span class="required">*</span></label>
              <select class="form-control" name="type" id="po_type" required>
                <option value="">Select</option>
                <option value="official"   <?php echo ($selected_type === 'official') ? 'selected' : ''; ?>>Official</option>
                <option value="unofficial" <?php echo ($selected_type === 'unofficial') ? 'selected' : ''; ?>>Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Expense Type <span class="required">*</span></label>
              <select class="form-control select2" name="expense_type" id="expense_type_id" required>
                <option value="">Select</option>
                <?php foreach($expenses as $exp){ ?>
                  <option value="<?php echo (int)$exp['id'];?>"
                    <?php echo ((string)$selected_expense === (string)$exp['id']) ? 'selected' : ''; ?>>
                    <?php echo html_escape($exp['name']);?>
                  </option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1 gst-type-container">
            <div class="form-group">
              <label><?php echo get_phrase('gst_type'); ?> <span class="required">*</span></label>
              <select class="form-control" name="gst_type" id="gst_type" required>
                <option value="">Select</option>
                <option value="igst"      <?php echo ($selected_gsttype === 'igst') ? 'selected' : ''; ?>>IGST</option>
                <option value="cgst_sgst" <?php echo ($selected_gsttype === 'cgst_sgst') ? 'selected' : ''; ?>>CGST/SGST</option>
              </select>
            </div>
          </div>


          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label class="control-label"> Expense Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="expense_date" value="<?php echo html_escape($expense_date); ?>" id="expense_date" required>
            </div>
          </div>

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label"> Narration </label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration"><?php echo html_escape($narration); ?></textarea>
            </div>
          </div>

          <!-- ===================== APPENDABLE EXPENSES SECTION ===================== -->
          <div class="col-12 mb-0">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="mb-0"><b>Expenses</b> <span class="required">*</span></label>
              <button type="button" class="btn btn-sm btn-outline-primary" id="addExpenseRow">+ Add Expense</button>
            </div>

            <div class="table-responsive">
              <table class="table table-striped table-bordered" id="expenseTable">
                <thead>
                  <tr>
                    <th style="width:70px">Sr No</th>
                    <th>Name <span class="required">*</span></th>
                    <th style="width:100px">Amount</th>
                    <th style="width:100px" class="gst-column">GST (In %)</th>
                    <th style="width:100px" class="gst-column">GST Amount</th>
                    <th style="width:100px">Total Amount <span class="required">*</span></th>
                    <th style="width:100px">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($details)): ?>
                    <?php foreach ($details as $i => $row): ?>
                      <tr class="expense-row" data-mode="keep">
                        <td class="sr-no text-center"><?php echo (int)($i + 1); ?></td>

                        <td>
                          <select name="charges_id[]" class="form-control charges_id" required>
                            <option value="">Select Charge</option>
                            <?php foreach ($other_charges_list as $charge): ?>
                              <option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-name="<?php echo html_escape($charge['name']); ?>"
                                <?php echo (isset($row['charges_id']) && (string)$row['charges_id'] === (string)$charge['id']) ? 'selected' : ''; ?>>
                                <?php echo html_escape($charge['name']); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <input type="hidden" name="expense_name[]" class="expense_name" value="<?php echo html_escape($row['expense_name'] ?? ''); ?>">
                        </td>

                        <td>
                          <input type="number" name="amount[]" class="form-control amount" min="0" step="0.01"
                                 value="<?php echo number_format((float)($row['amount'] ?? 0), 2, '.', ''); ?>">
                        </td>

                        <td class="gst-column">
                          <input type="number" name="gst[]" class="form-control gst" min="0" max="100" step="0.01" placeholder="0" value="<?php echo number_format((float)($row['gst'] ?? 0), 2, '.', ''); ?>">
                        </td>

                        <td class="gst-column">
                          <input type="text" name="gst_amt[]" class="form-control gst_amt" readonly value="<?php echo number_format((float)($row['gst_amt'] ?? 0), 2, '.', ''); ?>">
                        </td>

                        <td>
                          <input type="number" name="total_amt[]" class="form-control total_amt" min="0" step="0.01" required value="<?php echo number_format((float)($row['total_amt'] ?? 0), 2, '.', ''); ?>">
                        </td>

                        <td class="text-center">
                          <?php if ($i === 0): ?>
                            <span class="text-muted">—</span>
                          <?php else: ?>
                            <button type="button" class="btn btn-sm btn-outline-danger removeExpenseRow">Remove</button>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr class="expense-row" data-mode="keep">
                      <td class="sr-no text-center">1</td>
                      <td>
                        <select name="charges_id[]" class="form-control charges_id" required>
                          <?php echo $charges_options; ?>
                        </select>
                        <input type="hidden" name="expense_name[]" class="expense_name">
                      </td>
                      <td><input type="number" name="amount[]" class="form-control amount" min="0" step="0.01"></td>
                      <td class="gst-column"><input type="number" name="gst[]" class="form-control gst" min="0" max="100" step="0.01" placeholder="0"></td>
                      <td class="gst-column"><input type="text" name="gst_amt[]" class="form-control gst_amt" readonly></td>
                      <td><input type="number" name="total_amt[]" class="form-control total_amt" min="0" step="0.01" required></td>
                      <td class="text-center"><span class="text-muted">—</span></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ===================== TOTALS SECTION ===================== -->
          <div class="col-12 col-sm-12 mb-1">
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <tbody>
                  <tr>
                    <td style="width:80%" class="text-right"><label>Sub Total</label></td>
                    <td><input type="text" name="sub_total" id="sub_total" class="form-control" readonly value="<?php echo html_escape($sub_total); ?>"></td>
                  </tr>

                  <tr class="gst-total-row">
                    <td class="text-right"><label>GST Amount</label></td>
                    <td><input type="text" name="gst_total" id="gst_total" class="form-control" readonly value="<?php echo html_escape($gst_total); ?>"></td>
                  </tr>

                  <tr>
                    <td class="text-right"><label>Grand Total</label></td>
                    <td>
                      <input type="text" name="grand_total" id="grand_total" class="form-control" readonly value="<?php echo html_escape($grand_total); ?>">
                      <input type="hidden" name="final_amount" id="final_amount_hidden" value="<?php echo html_escape($grand_total); ?>">
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
          </div>

        </div>
        <?php echo form_close(); ?>

      </div>
    </div>
  </div>
</div>

<script>

$(function () {
  const $tbody = $('#expenseTable tbody');

  // ---------- helpers ----------
  const toNum = (v) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : 0;
  };

  const money = (n) => (Number.isFinite(n) ? n.toFixed(2) : '0.00');
  const clamp = (n, min, max) => Math.min(max, Math.max(min, n));
  const hasValue = (v) => String(v ?? '').trim() !== '';

  function renumberRows() {
    $tbody.find('tr.expense-row').each(function (i) {
      $(this).find('.sr-no').text(i + 1);
    });
  }

  // mode: keep | amount | total
  function setMode($row, mode) { $row.data('mode', mode); }
  function getMode($row) { return $row.data('mode') || 'keep'; }

  // ---------- row calculations ----------
  function calcFromAmount($row) {
    const amt = toNum($row.find('.amount').val());
    const gstP = clamp(toNum($row.find('.gst').val()), 0, 100);

    const gstAmt = (amt * gstP) / 100;
    const total = amt + gstAmt;

    $row.find('.gst_amt').val(money(gstAmt));
    $row.find('.total_amt').val(money(total)); // computed total

    return { amt, gstAmt, total };
  }

  function calcFromTotal($row) {
    const total = toNum($row.find('.total_amt').val());
    const gstP = clamp(toNum($row.find('.gst').val()), 0, 100);

    const divisor = 1 + (gstP / 100);
    const amt = divisor > 0 ? (total / divisor) : total;
    const gstAmt = total - amt;

    $row.find('.amount').val(money(amt));      // computed base amount
    $row.find('.gst_amt').val(money(gstAmt));  // computed gst amount

    return { amt, gstAmt, total };
  }

  function keepBoth($row) {
    const amt = toNum($row.find('.amount').val());
    const total = toNum($row.find('.total_amt').val());
    const gstAmt = total - amt;
    $row.find('.gst_amt').val(money(gstAmt));
    return { amt, gstAmt, total };
  }

  function updateRow($row) {
    const mode = getMode($row);
    const amtStr = $row.find('.amount').val();
    const totalStr = $row.find('.total_amt').val();

    // If edit-prefilled row has both values and mode=keep, don’t overwrite either.
    if (mode === 'keep' && hasValue(amtStr) && hasValue(totalStr)) return keepBoth($row);

    // normal behavior
    if (mode === 'total' && hasValue(totalStr)) return calcFromTotal($row);
    if (mode === 'amount' && hasValue(amtStr)) return calcFromAmount($row);

    // Smart fallback when one is empty
    if (!hasValue(amtStr) && hasValue(totalStr)) return calcFromTotal($row);
    if (hasValue(amtStr) && !hasValue(totalStr)) return calcFromAmount($row);

    // nothing entered
    $row.find('.gst_amt').val('');
    return { amt: 0, gstAmt: 0, total: 0 };
  }

  // ---------- totals ----------
  function updateTotals() {
    let subTotal = 0;
    let gstTotal = 0;
    let grandTotal = 0;

    $tbody.find('tr.expense-row').each(function () {
      const res = updateRow($(this));
      subTotal += res.amt;
      gstTotal += res.gstAmt;
      grandTotal += res.total;
    });

    $('#sub_total').val(money(subTotal));
    $('#gst_total').val(money(gstTotal));
    $('#grand_total').val(money(grandTotal));

    $('#final_amount_hidden').val(money(grandTotal));
  }

  // ---------- events ----------
  $(document).on('input', '#expenseTable .amount', function () {
    setMode($(this).closest('tr'), 'amount');
    updateTotals();
  });

  $(document).on('input', '#expenseTable .total_amt', function () {
    setMode($(this).closest('tr'), 'total');
    updateTotals();
  });

  $(document).on('input', '#expenseTable .gst', function () {
    // if user changes gst, use the row’s current mode to recalc
    const $row = $(this).closest('tr');
    if (getMode($row) === 'keep') {
      setMode($row, 'amount');
    }
    updateTotals();
  });

  function fetchSuppliers(batchNo, selectedIds = []) {
    const $supplierSelect = $('#supplier_id');
    if (batchNo) {
      $.ajax({
        url: '<?php echo base_url("inventory/get_suppliers_by_batch"); ?>',
        type: 'POST',
        data: { batch_no: batchNo },
        dataType: 'json',
        success: function(data) {
          $supplierSelect.empty();
          data.forEach(supplier => {
            const isSelected = selectedIds.length > 0 ? selectedIds.includes(supplier.id.toString()) : true;
            const option = new Option(supplier.name, supplier.id, isSelected, isSelected);
            $supplierSelect.append(option);
          });
          $supplierSelect.trigger('change');
        }
      });
    } else {
      $supplierSelect.empty().trigger('change');
    }
  }

  function toggleGstFields() {
    const type = $('#po_type').val();
    const isOfficial = (type === 'official');

    if (isOfficial) {
      $('.gst-type-container').show();
      $('.gst-column').show();
      $('.gst-total-row').show();
      $('#gst_type').prop('required', true);
    } else {
      $('.gst-type-container').hide();
      $('.gst-column').hide();
      $('.gst-total-row').hide();
      $('#gst_type').prop('required', false).val('');
      
      // Reset GST values to 0 for unofficial
      $('.gst').val(0);
      $('.gst_amt').val('0.00');
      updateTotals();
    }
  }

  function handleGstTypeAutoSelect() {
    const type = $('#po_type').val();
    if (type === 'official') {
      const vendorStateId = $('#company_id').find('option:selected').data('state-id');
      const currentCompanyStateId = <?php echo (int)($company_state_id ?? 0); ?>;
      
      if (vendorStateId && currentCompanyStateId) {
        if (vendorStateId == currentCompanyStateId) {
          $('#gst_type').val('cgst_sgst').trigger('change');
        } else {
          $('#gst_type').val('igst').trigger('change');
        }
      }
    }
  }

  $('#po_type').on('change', function() {
    toggleGstFields();
    handleGstTypeAutoSelect();
  });

  $('#company_id').on('change', function() {
    handleGstTypeAutoSelect();
  });

  $('#purchase_date').on('change input', function() {
    $('#expense_date').val($(this).val());
  });

  $('#batch_no').on('change', function() {
    fetchSuppliers($(this).val());
  });

  // Handle charges selection change
  $(document).on('change', '#expenseTable .charges_id', function () {
    const $row = $(this).closest('tr');
    const $selectedOpt = $(this).find('option:selected');
    const name = $selectedOpt.data('name') || '';
    const gst = toNum($selectedOpt.data('gst'));

    $row.find('.expense_name').val(name);
    
    // Set GST percentage if official PO type is selected
    const isOfficial = ($('#po_type').val() === 'official');
    if (isOfficial) {
      $row.find('.gst').val(gst);
    } else {
      $row.find('.gst').val(0);
    }
    
    // Set mode to amount and trigger totals update
    setMode($row, 'amount');
    updateTotals();
  });

  // add row
  $('#addExpenseRow').on('click', function () {
    const isOfficial = ($('#po_type').val() === 'official');
    const displayStyle = isOfficial ? '' : 'style="display:none"';
    const newRow = `
      <tr class="expense-row" data-mode="amount">
        <td class="sr-no text-center"></td>
        <td>
          <select name="charges_id[]" class="form-control charges_id" required>
            <?php echo $charges_options; ?>
          </select>
          <input type="hidden" name="expense_name[]" class="expense_name">
        </td>
        <td><input type="number" name="amount[]" class="form-control amount" min="0" step="0.01"></td>
        <td class="gst-column" ${displayStyle}><input type="number" name="gst[]" class="form-control gst" min="0" max="100" step="0.01" placeholder="0" value="0"></td>
        <td class="gst-column" ${displayStyle}><input type="text" name="gst_amt[]" class="form-control gst_amt" readonly></td>
        <td><input type="number" name="total_amt[]" class="form-control total_amt" min="0" step="0.01" required></td>
        <td class="text-center">
          <button type="button" class="btn btn-sm btn-outline-danger removeExpenseRow">Remove</button>
        </td>
      </tr>
    `;
    $tbody.append(newRow);
    renumberRows();
    updateTotals();
  });

  // remove row
  $(document).on('click', '.removeExpenseRow', function () {
    $(this).closest('tr').remove();
    renumberRows();
    updateTotals();
  });

  // init select2 values (safe with/without select2)
  $('#batch_no').val('<?php echo addslashes((string)$selected_batch); ?>').trigger('change');
  $('#company_id').val('<?php echo addslashes((string)$selected_vendor); ?>').trigger('change');
  $('#expense_type_id').val('<?php echo addslashes((string)$selected_expense); ?>').trigger('change');


  // init totals
  renumberRows();
  updateTotals();

  const initialBatch = $('#batch_no').val();
  if (initialBatch) {
    fetchSuppliers(initialBatch, <?php echo json_encode($selected_suppliers); ?>);
  }

  toggleGstFields();
});
</script>
