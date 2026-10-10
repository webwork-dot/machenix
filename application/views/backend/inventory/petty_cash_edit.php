<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<?php
  $row = isset($data) && is_array($data) ? $data : array();

  $supplier_id   = isset($row['supplier_id']) ? $row['supplier_id'] : '';
  $amount_rs     = isset($row['amount_rs']) ? $row['amount_rs'] : 0;
  $payment_type  = isset($row['payment_type']) ? $row['payment_type'] : '';
  $payment_date  = !empty($row['payment_date']) ? $row['payment_date'] : date('Y-m-d');
  $narration     = isset($row['narration']) ? $row['narration'] : '';
  $official_cash   = (float)($official_cash ?? 0);
  $unofficial_cash = (float)($unofficial_cash ?? 0);
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom">
        <h4 class="card-title">Edit Cash Book Expense</h4>
      </div>
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/petty_cash/edit_post/' . $id, ['class' => 'add-ajax-redirect-form', 'id' => 'petty_cash_edit_form', 'onsubmit' => 'return validatePettyCash(this);']);?>
        <input type="hidden" name="payment_method" value="cash">
        <div class="row">

          <div class="col-12 mb-2">
            <div class="row g-1">
              <div class="col-md-6 mb-1 mb-md-0">
                <div class="card bg-light-primary border-primary shadow-none mb-0">
                  <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                      <h6 class="text-primary mb-25 font-weight-bold">Official Cash in Hand</h6>
                      <small class="text-muted">Customer payment − supplier expense</small>
                    </div>
                    <div>
                      <h4 class="<?= $official_cash < 0 ? 'text-danger' : 'text-primary'; ?> font-weight-bolder mb-0" id="display_official_cash"><?= $official_cash < 0 ? '- ₹ ' . number_format(abs($official_cash), 2) : '₹ ' . number_format($official_cash, 2); ?></h4>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-light-secondary border-secondary shadow-none mb-0">
                  <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                      <h6 class="text-secondary mb-25 font-weight-bold">Unofficial Cash in Hand</h6>
                      <small class="text-muted">Customer payment − supplier expense</small>
                    </div>
                    <div>
                      <h4 class="<?= $unofficial_cash < 0 ? 'text-danger' : 'text-secondary'; ?> font-weight-bolder mb-0" id="display_unofficial_cash"><?= $unofficial_cash < 0 ? '- ₹ ' . number_format(abs($unofficial_cash), 2) : '₹ ' . number_format($unofficial_cash, 2); ?></h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Local Supplier <span class="required">*</span></label>
              <select class="form-control select2" name="supplier_id" id="supplier_id" required>
                <option value="">Select</option>
                <?php foreach ($supplier_list as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>"
                    <?php echo ((string)$supplier_id === (string)$value['id']) ? 'selected' : ''; ?>>
                    <?php echo $value['name'];?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Payment type <span class="required">*</span></label>
              <select class="form-control select2" name="payment_type" id="payment_type" required>
                <option value="">Select</option>
                <option value="official"   <?php echo ($payment_type === 'official') ? 'selected' : ''; ?>>Official</option>
                <option value="unofficial" <?php echo ($payment_type === 'unofficial') ? 'selected' : ''; ?>>Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount (in INR) <span class="required">*</span></label>
              <input type="number" name="amount_rs" id="amount_rs" class="form-control"
                     value="<?php echo html_escape($amount_rs); ?>"
                     min="0.01" step="0.01" required>
              <small class="text-danger mt-25 d-none font-weight-bold" id="amount_error_msg">Amount cannot exceed available cash in hand</small>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" id="date_picker" required
                     value="<?php echo html_escape($payment_date); ?>">
            </div>
          </div>

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration"><?php
                echo html_escape($narration);
              ?></textarea>
            </div>
          </div>

          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
            <a href="<?php echo base_url('inventory/cash-book'); ?>" class="btn btn-outline-secondary mt-1">Cancel</a>
          </div>

        </div>
        <?php echo form_close(); ?>

      </div>
    </div>
  </div>
</div>

<script>
var cashByType = {
  official: <?= json_encode($official_cash); ?>,
  unofficial: <?= json_encode($unofficial_cash); ?>
};
var maxCashInHand = 0;

function getSelectedCashLimit() {
  var type = $('#payment_type').val();
  if (type === 'official' || type === 'unofficial') {
    return parseFloat(cashByType[type]) || 0;
  }
  return 0;
}

function updateAmountLimit() {
  maxCashInHand = getSelectedCashLimit();
  var $amount = $('#amount_rs');
  var type = $('#payment_type').val();

  if (!type) {
    $amount.attr('max', '').attr('placeholder', 'Select payment type first');
    return;
  }

  $amount.attr('max', maxCashInHand)
    .attr('placeholder', 'Enter amount (Max: ₹' + maxCashInHand.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
  $('#amount_error_msg').text('Amount cannot exceed available ' + type + ' cash in hand (₹' + maxCashInHand.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
  $amount.trigger('input');
}

function validatePettyCash(form) {
  var type = $('#payment_type').val();
  var amount = parseFloat($('#amount_rs').val()) || 0;
  maxCashInHand = getSelectedCashLimit();

  if (!type) {
    Swal.fire({
      title: "Payment Type Required",
      text: "Please select official or unofficial payment type",
      icon: "warning",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  if (amount <= 0) {
    Swal.fire({
      title: "Invalid Amount",
      text: "Please enter an amount greater than 0",
      icon: "warning",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    $('#amount_rs').focus();
    return false;
  }

  if (amount > maxCashInHand) {
    Swal.fire({
      title: "Amount Exceeded",
      text: "Amount cannot exceed available " + type + " cash in hand (₹" + maxCashInHand.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ")",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    $('#amount_rs').addClass('is-invalid');
    $('#amount_error_msg').removeClass('d-none');
    $('#amount_rs').focus();
    return false;
  }

  $('#amount_rs').removeClass('is-invalid');
  $('#amount_error_msg').addClass('d-none');
  return checkForm(form);
}

$(function () {
  $('#payment_type').on('change', updateAmountLimit);
  updateAmountLimit();

  $('#amount_rs').on('input change', function() {
    var val = parseFloat($(this).val()) || 0;
    maxCashInHand = getSelectedCashLimit();
    if ($('#payment_type').val() && val > maxCashInHand) {
      $(this).addClass('is-invalid');
      $('#amount_error_msg').removeClass('d-none');
    } else {
      $(this).removeClass('is-invalid');
      $('#amount_error_msg').addClass('d-none');
    }
  });
});

$(document).ready(function () {
  $(document).on('focus', '#supplier_id + .select2 .select2-selection', function () {
      $('#supplier_id').select2('open');
  });
  $(document).on('focus', '#payment_type + .select2 .select2-selection', function () {
      $('#payment_type').select2('open');
  });
});
</script>
