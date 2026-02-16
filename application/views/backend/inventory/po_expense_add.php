<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/po_expense/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">
          <input type="hidden" name="input_method" value="<?php echo $type; ?>">

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Batch No <span class="required">*</span></label>
              <select class="form-control select2" name="batch_no" id="batch_no" required>
                <option value="">Select</option>
                <?php foreach ($po as $key => $value): ?>
                  <option value="<?php echo $value['voucher_no'];?>"><?php echo $value['voucher_no'];?></option>
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
                  <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Type <span class="required">*</span></label>
              <select class="form-control" name="type" id="po_type" required>
                <option value="">Select</option>
                <option value="official">Official</option>
                <option value="unofficial">Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Expense Type <span class="required">*</span></label>
              <select class="form-control select2" name="expense_type" id="expense_type_id" required>
                <option value="">Select</option>
                <?php foreach($expenses as $exp){ ?>
                  <option value="<?php echo $exp['id'];?>"><?php echo $exp['name'];?></option>
                <?php } ?>
              </select>
            </div>
          </div>

           <div class="col-md-4 mb-1">
            <div class="form-group">
              <label><?php echo get_phrase('gst_type'); ?> <span class="required">*</span></label>
              <select class="form-control" name="gst_type" id="gst_type" >
                <option value="">Select</option>
                <option value="igst">IGST</option>
                <option value="cgst_sgst">CGST/SGST</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1" id="payment_type_div">
            <div class="form-group">
              <label><?php echo get_phrase('payment_type'); ?> <span class="required">*</span></label>
              <select class="form-control" name="payment_type" id="payment_type" onchange="check_provider(this.value)" required>
                <option value="">Select</option>
                <option value="Cheque">Cheque</option>
                <option value="RTGS">RTGS</option>
                <option value="NEFT">NEFT</option>
                <option value="IMPS">IMPS</option>
                <option value="UPI">UPI</option>
                <option value="CASH">CASH</option>
                <option value="CARD">CARD</option>
              </select>
            </div>
          </div>

          <div class="col-md-12" id="check" style="display:none">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label"><span class="check"></span> No.</label>
                  <input type="text" name="cheque_no" class="form-control">
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label><?php echo get_phrase('company_bank_name'); ?> </label>
                  <input type="text" name="company_bank_name" class="form-control">
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label"><span class="check"></span> Received Date</label>
                  <input type="date" class="form-control required" name="cheque_recv_date"
                         value="<?php echo date('Y-m-d');?>" id="date_picker">
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label class="control-label"><span class="check"></span> Date <span class="required">*</span></label>
              <input type="date" class="form-control required" name="cheque_date"
                     value="<?php echo date('Y-m-d');?>" id="date_picker" required>
            </div>
          </div>

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label"> Narration </label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration" ></textarea>
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
                    <th style="width:100px">Amount </th>
                    <th style="width:100px">GST (In %)</th>
                    <th style="width:100px">GST Amount</th>
                    <th style="width:100px">Total Amount<span class="required">*</span></th>
                    <th style="width:100px">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="expense-row">
                    <td class="sr-no text-center">1</td>

                    <td>
                      <input type="text" name="expense_name[]" class="form-control expense_name" required>
                    </td>

                    <td>
                      <input type="number" name="amount[]" class="form-control amount"
                             min="0" step="0.01">
                    </td>

                    <td>
                      <input type="number" name="gst[]" class="form-control gst"
                             min="0" max="100" step="0.01" placeholder="0">
                    </td>

                    <td>
                      <input type="text" name="gst_amt[]" class="form-control gst_amt" readonly>
                    </td>

                    <td>
                      <input type="number" name="total_amt[]" class="form-control total_amt" min="0" step="0.01" required>
                    </td>

                    <td class="text-center">
                      <!-- First row: no delete -->
                      <span class="text-muted">—</span>
                    </td>
                  </tr>
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
                    <td style="width:80%" class="text-right">
                      <label>Sub Total</label>
                    </td>
                    <td>
                      <input type="text" name="sub_total" id="sub_total" class="form-control" readonly>
                    </td>
                  </tr>

                  <tr>
                    <td class="text-right">
                      <label>GST Amount</label>
                    </td>
                    <td>
                      <input type="text" name="gst_total" id="gst_total" class="form-control" readonly>
                    </td>
                  </tr>

                  <tr>
                    <td class="text-right">
                      <label>Grand Total</label>
                    </td>
                    <td>
                      <input type="text" name="grand_total" id="grand_total" class="form-control" readonly>
                      <!-- Optional hidden fields if your backend still expects old names -->
                      <input type="hidden" name="final_amount" id="final_amount_hidden">
                      <input type="hidden" name="cheque_amount" id="cheque_amount_hidden">
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
  if (provider === 'CASH') {
    $('#check').hide();
    $('.check').html('');
    $('.required').removeAttr('required');
  } else {
    $('#check').show();
    $('.check').html(provider);
    $('.required').prop('required', true);
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

  // Keep track of what the user last edited in each row: "amount" or "total"
  function setMode($row, mode) {
    $row.data('mode', mode);
  }
  function getMode($row) {
    return $row.data('mode') || 'amount';
  }

  // ---------- row calculations ----------
  function calcFromAmount($row) {
    const amt = toNum($row.find('.amount').val());
    const gstP = clamp(toNum($row.find('.gst').val()), 0, 100);

    const gstAmt = (amt * gstP) / 100;
    const total = amt + gstAmt;

    // amount is user field, so don't overwrite it
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

    // total is user field, so DON'T overwrite it
    $row.find('.amount').val(money(amt));      // computed base amount
    $row.find('.gst_amt').val(money(gstAmt));  // computed gst amount

    return { amt, gstAmt, total };
  }

  function updateRow($row) {
    const mode = getMode($row);

    const amtStr = $row.find('.amount').val();
    const totalStr = $row.find('.total_amt').val();

    // If user is in "total" mode but total is empty, fallback to amount mode
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
      grandTotal += res.total; // grand total is sum of total_amt
    });

    $('#sub_total').val(money(subTotal));
    $('#gst_total').val(money(gstTotal));
    $('#grand_total').val(money(grandTotal));

    // if backend expects these:
    $('#final_amount_hidden').val(money(grandTotal));
    $('#cheque_amount_hidden').val(money(grandTotal));
  }

  // ---------- events ----------
  // when user edits amount -> amount mode
  $(document).on('input', '#expenseTable .amount', function () {
    setMode($(this).closest('tr'), 'amount');
    updateTotals();
  });

  // when user edits total -> total mode
  $(document).on('input', '#expenseTable .total_amt', function () {
    setMode($(this).closest('tr'), 'total');
    updateTotals();
  });

  // gst change should recalc based on the row mode
  $(document).on('input', '#expenseTable .gst', function () {
    updateTotals();
  });

  // add row
  $('#addExpenseRow').on('click', function () {
    const newRow = `
      <tr class="expense-row">
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

  // init
  renumberRows();
  updateTotals();
});

</script>
