<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom">
        <h4 class="card-title">Edit Petty Cash</h4>
      </div>
      <div class="card-body py-2">
        <?php echo form_open('inventory/petty_cash/edit_post/' . $id, ['class' => 'add-ajax-redirect-form', 'id' => 'petty_cash_edit_form', 'onsubmit' => 'return validatePettyCash(this);']);?>
        <div class="row">

          <div class="col-12 mb-2">
            <div class="card bg-light-primary border-primary shadow-none mb-0">
              <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h6 class="text-primary mb-25 font-weight-bold">Current Cash in Hand</h6>
                  <small class="text-muted">Available balance for this entry</small>
                </div>
                <div>
                  <h4 class="text-primary font-weight-bolder mb-0" id="display_cash_in_hand">₹ <?= number_format($cash_in_hand ?? 0, 2); ?></h4>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-12 mb-1">
            <div class="form-group">
              <label>Amount (in INR) <span class="required">*</span></label>
              <input type="number" name="amount" id="amount" class="form-control" value="<?= (float)($data['amount'] ?? 0); ?>" min="0.01" max="<?= (float)($cash_in_hand ?? 0); ?>" step="0.01" placeholder="Enter amount (Max: ₹<?= number_format($cash_in_hand ?? 0, 2); ?>)" required>
              <small class="text-danger mt-25 d-none font-weight-bold" id="amount_error_msg">Amount cannot exceed available cash in hand (₹<?= number_format($cash_in_hand ?? 0, 2); ?>)</small>
            </div>
          </div>

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label">Remark / Narration</label>
              <textarea class="form-control" rows="3" placeholder="Enter remark or narration..." name="remark"><?= htmlspecialchars($data['remark'] ?? ''); ?></textarea>
            </div>
          </div>

          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
            <a href="<?php echo base_url('inventory/petty-cash'); ?>" class="btn btn-outline-secondary mt-1">Cancel</a>
          </div>

        </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script>
var maxCashInHand = <?= (float)($cash_in_hand ?? 0); ?>;

function validatePettyCash(form) {
  var amount = parseFloat($('#amount').val()) || 0;
  if (amount <= 0) {
    Swal.fire({
      title: "Invalid Amount",
      text: "Please enter an amount greater than 0",
      icon: "warning",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    $('#amount').focus();
    return false;
  }

  if (amount > maxCashInHand) {
    Swal.fire({
      title: "Amount Exceeded",
      text: "Amount cannot exceed available cash in hand (₹" + maxCashInHand.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ")",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    $('#amount').addClass('is-invalid');
    $('#amount_error_msg').removeClass('d-none');
    $('#amount').focus();
    return false;
  }

  $('#amount').removeClass('is-invalid');
  $('#amount_error_msg').addClass('d-none');
  return checkForm(form);
}

$(document).ready(function() {
  $('#amount').on('input change', function() {
    var val = parseFloat($(this).val()) || 0;
    if (val > maxCashInHand) {
      $(this).addClass('is-invalid');
      $('#amount_error_msg').removeClass('d-none');
    } else {
      $(this).removeClass('is-invalid');
      $('#amount_error_msg').addClass('d-none');
    }
  });
});
</script>
