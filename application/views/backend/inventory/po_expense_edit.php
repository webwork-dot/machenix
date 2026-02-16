<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<?php
// CI3 view-safe defaults
$po_exp = (isset($data) && is_array($data)) ? $data : [];
$details = (isset($lists) && is_array($lists)) ? $lists : [];

$selected_batch   = $po_exp['batch_no']      ?? '';
// some projects store vendor in vendor_id, some in company_id — keep your form name as company_id
$selected_vendor  = $po_exp['vendor_id']     ?? ($po_exp['company_id'] ?? '');
$selected_type    = $po_exp['type']          ?? '';
$selected_expense = $po_exp['expense_type']  ?? '';
$selected_gsttype = $po_exp['gst_type']      ?? '';
$selected_paytype = $po_exp['payment_type']  ?? '';

$cheque_no        = $po_exp['cheque_no']          ?? '';
$bank_name        = $po_exp['company_bank_name']  ?? '';
$recv_date        = !empty($po_exp['cheque_recv_date']) ? $po_exp['cheque_recv_date'] : date('Y-m-d');
$cheque_date      = !empty($po_exp['cheque_date'])      ? $po_exp['cheque_date']      : date('Y-m-d');
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
              <label>Vendor <span class="required">*</span></label>
              <select class="form-control select2" name="company_id" id="company_id" required>
                <option value="">Select</option>
                <?php foreach ($company_list as $key => $value): ?>
                  <option value="<?php echo (int)$value['id']; ?>"
                    <?php echo ((string)$selected_vendor === (string)$value['id']) ? 'selected' : ''; ?>>
                    <?php echo html_escape($value['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
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

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label><?php echo get_phrase('gst_type'); ?> <span class="required">*</span></label>
              <select class="form-control" name="gst_type" id="gst_type" required>
                <option value="">Select</option>
                <option value="igst"      <?php echo ($selected_gsttype === 'igst') ? 'selected' : ''; ?>>IGST</option>
                <option value="cgst_sgst" <?php echo ($selected_gsttype === 'cgst_sgst') ? 'selected' : ''; ?>>CGST/SGST</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1" id="payment_type_div">
            <div class="form-group">
              <label><?php echo get_phrase('payment_type'); ?> <span class="required">*</span></label>
              <select class="form-control" name="payment_type" id="payment_type" onchange="check_provider(this.value)" required>
                <option value="">Select</option>
                <?php
                  $payTypes = ['Cheque','RTGS','NEFT','IMPS','UPI','CASH','CARD'];
                  foreach ($payTypes as $pt):
                ?>
                  <option value="<?php echo $pt; ?>" <?php echo ($selected_paytype === $pt) ? 'selected' : ''; ?>>
                    <?php echo $pt; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-12" id="check" style="display:none">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label"><span class="check"></span> No.</label>
                  <input type="text" name="cheque_no" class="form-control" value="<?php echo html_escape($cheque_no); ?>">
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label><?php echo get_phrase('company_bank_name'); ?></label>
                  <input type="text" name="company_bank_name" class="form-control" value="<?php echo html_escape($bank_name); ?>">
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label"><span class="check"></span> Received Date</label>
                  <input type="date" class="form-control" name="cheque_recv_date" value="<?php echo html_escape($recv_date); ?>" id="cheque_recv_date">
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label class="control-label"><span class="check"></span> Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="cheque_date" value="<?php echo html_escape($cheque_date); ?>" id="cheque_date" required>
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
                    <th style="width:100px">GST (In %)</th>
                    <th style="width:100px">GST Amount</th>
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
                          <input type="text" name="expense_name[]" class="form-control expense_name" required
                                 value="<?php echo html_escape($row['expense_name'] ?? ''); ?>">
                        </td>

                        <td>
                          <input type="number" name="amount[]" class="form-control amount" min="0" step="0.01"
                                 value="<?php echo number_format((float)($row['amount'] ?? 0), 2, '.', ''); ?>">
                        </td>

                        <td>
                          <input type="number" name="gst[]" class="form-control gst" min="0" max="100" step="0.01" placeholder="0"
                                 value="<?php echo number_format((float)($row['gst'] ?? 0), 2, '.', ''); ?>">
                        </td>

                        <td>
                          <input type="text" name="gst_amt[]" class="form-control gst_amt" readonly
                                 value="<?php echo number_format((float)($row['gst_amt'] ?? 0), 2, '.', ''); ?>">
                        </td>

                        <td>
                          <input type="number" name="total_amt[]" class="form-control total_amt" min="0" step="0.01" required
                                 value="<?php echo number_format((float)($row['total_amt'] ?? 0), 2, '.', ''); ?>">
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
                      <td><input type="text" name="expense_name[]" class="form-control expense_name" required></td>
                      <td><input type="number" name="amount[]" class="form-control amount" min="0" step="0.01"></td>
                      <td><input type="number" name="gst[]" class="form-control gst" min="0" max="100" step="0.01" placeholder="0"></td>
                      <td><input type="text" name="gst_amt[]" class="form-control gst_amt" readonly></td>
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

                  <tr>
                    <td class="text-right"><label>GST Amount</label></td>
                    <td><input type="text" name="gst_total" id="gst_total" class="form-control" readonly value="<?php echo html_escape($gst_total); ?>"></td>
                  </tr>

                  <tr>
                    <td class="text-right"><label>Grand Total</label></td>
                    <td>
                      <input type="text" name="grand_total" id="grand_total" class="form-control" readonly value="<?php echo html_escape($grand_total); ?>">
                      <input type="hidden" name="final_amount" id="final_amount_hidden" value="<?php echo html_escape($grand_total); ?>">
                      <input type="hidden" name="cheque_amount" id="cheque_amount_hidden" value="<?php echo html_escape($grand_total); ?>">
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
function check_provider(provider) {
  // only affect cheque section fields (don’t touch other required fields)
  const $check = $('#check');
  const $chequeNo = $('input[name="cheque_no"]');
  const $recvDate = $('input[name="cheque_recv_date"]');

  if (provider === 'CASH') {
    $check.hide();
    $('.check').html('');
    $chequeNo.prop('required', false);
    $recvDate.prop('required', false);
  } else if (provider) {
    $check.show();
    $('.check').html(provider);
    // if you want these required for non-cash, enable:
    // $chequeNo.prop('required', true);
    // $recvDate.prop('required', true);
  } else {
    $check.hide();
    $('.check').html('');
    $chequeNo.prop('required', false);
    $recvDate.prop('required', false);
  }
}

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
    $('#cheque_amount_hidden').val(money(grandTotal));
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
    updateTotals();
  });

  // add row
  $('#addExpenseRow').on('click', function () {
    const newRow = `
      <tr class="expense-row" data-mode="amount">
        <td class="sr-no text-center"></td>
        <td><input type="text" name="expense_name[]" class="form-control expense_name" required></td>
        <td><input type="number" name="amount[]" class="form-control amount" min="0" step="0.01"></td>
        <td><input type="number" name="gst[]" class="form-control gst" min="0" max="100" step="0.01" placeholder="0"></td>
        <td><input type="text" name="gst_amt[]" class="form-control gst_amt" readonly></td>
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

  // init cheque section visibility
  check_provider($('#payment_type').val());

  // init totals
  renumberRows();
  updateTotals();
});
</script>
