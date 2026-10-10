<?php
$id = (int)($param2 ?? 0);
$company_id = (int)$this->session->userdata('company_id');

$transfer = $this->db->get_where('transferred_cash', ['id' => $id])->row_array();
if (empty($transfer) || (int)($transfer['company_id'] ?? 0) !== $company_id) {
  echo '<div class="alert alert-danger mb-0">Transfer not found.</div>';
  return;
}
if (!empty($transfer['is_approved'])) {
  echo '<div class="alert alert-warning mb-0">Approved transfers cannot be edited.</div>';
  return;
}

$cash_summary    = $this->inventory_model->get_cash_in_hand_summary($company_id);
$official_cash   = (float)($cash_summary['official']['cash_in_hand'] ?? 0);
$unofficial_cash = (float)($cash_summary['unofficial']['cash_in_hand'] ?? 0);

// Restore this transfer amount into available balance for its current type
$current_type = (($transfer['converted_from'] ?? '') === 'unofficial') ? 'unofficial' : 'official';
if (empty($transfer['converted_from']) && !empty($transfer['payment_type'])) {
  $current_type = ($transfer['payment_type'] === 'unofficial') ? 'unofficial' : 'official';
}
$current_amount = (float)($transfer['amount'] ?? 0);
if ($current_type === 'unofficial') {
  $unofficial_cash += $current_amount;
} else {
  $official_cash += $current_amount;
}

$companies = $this->common_model->getResultById('company', 'id, name', ['is_deleted' => 0]);
$companies = ($companies != '') ? $companies : [];
$selected_to = (int)($transfer['company_to_id'] ?? 0);
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/petty_cash/transfer_edit_post/' . $id, ['id' => 'transfer_cash_edit_form', 'onsubmit' => 'return submitTransferCashEditForm(event);']); ?>
    <input type="hidden" name="method_from" value="cash">

    <div class="row">
      <div class="col-12 mb-2">
        <div class="row g-1">
          <div class="col-md-6 mb-1 mb-md-0">
            <div class="card bg-light-primary border-primary shadow-none mb-0">
              <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h6 class="text-primary mb-25 font-weight-bold">Official Cash in Hand</h6>
                  <small class="text-muted">Available to transfer</small>
                </div>
                <div>
                  <h4 class="<?= $official_cash < 0 ? 'text-danger' : 'text-primary'; ?> font-weight-bolder mb-0"><?= $official_cash < 0 ? '- ₹ ' . number_format(abs($official_cash), 2) : '₹ ' . number_format($official_cash, 2); ?></h4>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card bg-light-secondary border-secondary shadow-none mb-0">
              <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h6 class="text-secondary mb-25 font-weight-bold">Unofficial Cash in Hand</h6>
                  <small class="text-muted">Available to transfer</small>
                </div>
                <div>
                  <h4 class="<?= $unofficial_cash < 0 ? 'text-danger' : 'text-secondary'; ?> font-weight-bolder mb-0"><?= $unofficial_cash < 0 ? '- ₹ ' . number_format(abs($unofficial_cash), 2) : '₹ ' . number_format($unofficial_cash, 2); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Transfer To Company <span class="required">*</span></label>
          <select class="form-control select2" name="company_to_id" id="company_to_id" required>
            <option value="">Select Company</option>
            <?php foreach ($companies as $company): ?>
              <?php if ((string)$company['id'] === (string)$company_id) continue; ?>
              <option value="<?php echo $company['id']; ?>" <?= ((int)$company['id'] === $selected_to) ? 'selected' : ''; ?>>
                <?php echo html_escape($company['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Type <span class="required">*</span></label>
          <select class="form-control select2" name="converted_from" id="transfer_payment_type" required>
            <option value="">Select</option>
            <option value="official" <?= $current_type === 'official' ? 'selected' : ''; ?>>Official</option>
            <option value="unofficial" <?= $current_type === 'unofficial' ? 'selected' : ''; ?>>Unofficial</option>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Transfer Amount (in INR) <span class="required">*</span></label>
          <input type="number" name="amount" id="transfer_amount" class="form-control" value="<?= htmlspecialchars(number_format($current_amount, 2, '.', '')); ?>" min="0.01" step="0.01" required>
          <small class="text-danger mt-25 d-none font-weight-bold" id="transfer_amount_error_msg">Amount cannot exceed available cash in hand</small>
        </div>
      </div>

      <div class="col-md-12 mb-2">
        <div class="form-group">
          <label class="control-label">Remark / Narration</label>
          <textarea class="form-control" rows="3" placeholder="Enter remark or narration..." name="remark"><?= htmlspecialchars($transfer['remark'] ?? ''); ?></textarea>
        </div>
      </div>

      <div class="col-12">
        <button type="submit" id="transfer_submit_btn" class="btn btn-primary waves-effect waves-float waves-light me-1">
          <i class="feather icon-check"></i> Update Transfer
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<script>
var transferCashByType = {
  official: <?= json_encode($official_cash); ?>,
  unofficial: <?= json_encode($unofficial_cash); ?>
};
var maxTransferCash = 0;

function getTransferCashLimit() {
  var type = $('#transfer_payment_type').val();
  if (type === 'official' || type === 'unofficial') {
    return parseFloat(transferCashByType[type]) || 0;
  }
  return 0;
}

function updateTransferAmountLimit() {
  maxTransferCash = getTransferCashLimit();
  var $amount = $('#transfer_amount');
  var type = $('#transfer_payment_type').val();

  if (!type) {
    $amount.prop('disabled', true).attr('max', '').attr('placeholder', 'Select type first');
    $('#transfer_amount_error_msg').addClass('d-none');
    return;
  }

  $amount.prop('disabled', false)
    .attr('max', maxTransferCash)
    .attr('placeholder', 'Enter amount (Max: ₹' + maxTransferCash.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
  $('#transfer_amount_error_msg').text('Amount cannot exceed available ' + type + ' cash in hand (₹' + maxTransferCash.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
  $amount.trigger('input');
}

function submitTransferCashEditForm(event) {
  event.preventDefault();

  var type = $('#transfer_payment_type').val();
  var companyTo = $('#company_to_id').val();
  var amount = parseFloat($('#transfer_amount').val()) || 0;
  maxTransferCash = getTransferCashLimit();

  if (!companyTo) {
    Swal.fire({ title: "Company Required", text: "Please select a company to transfer to", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (!type) {
    Swal.fire({ title: "Type Required", text: "Please select official or unofficial type", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (amount <= 0) {
    Swal.fire({ title: "Invalid Amount", text: "Please enter an amount greater than 0", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (amount > maxTransferCash) {
    Swal.fire({ title: "Amount Exceeded", text: "Amount cannot exceed available " + type + " cash in hand (₹" + maxTransferCash.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ")", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    $('#transfer_amount').addClass('is-invalid');
    $('#transfer_amount_error_msg').removeClass('d-none');
    return false;
  }

  var $submitBtn = $('#transfer_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
  if (typeof $(".loader") !== 'undefined') { $(".loader").show(); }

  $.ajax({
    type: 'POST',
    url: $('#transfer_cash_edit_form').attr('action'),
    data: $('#transfer_cash_edit_form').serialize(),
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      if (res.status == '200' || res.status == 200) {
        Swal.fire({ title: "Success!", text: res.message || "Transfer updated successfully", icon: "success", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false }).then(function() {
          $('#scrollable-modal').modal('hide');
          window.location.href = res.url || location.href;
        });
      } else {
        Swal.fire({ title: "Error!", text: res.message || "Failed to update transfer", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
        $submitBtn.html(originalText).attr("disabled", false);
      }
    },
    error: function() {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      Swal.fire({ title: "Error!", text: "An error occurred while processing request", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
      $submitBtn.html(originalText).attr("disabled", false);
    }
  });

  return false;
}

$(document).ready(function() {
  if ($.fn.select2) {
    $('#company_to_id, #transfer_payment_type').select2({
      dropdownParent: $('#scrollable-modal'),
      width: '100%'
    });
  }
  $('#transfer_payment_type').on('change', updateTransferAmountLimit);
  updateTransferAmountLimit();
  $('#transfer_amount').on('input change', function() {
    var val = parseFloat($(this).val()) || 0;
    maxTransferCash = getTransferCashLimit();
    if ($('#transfer_payment_type').val() && val > maxTransferCash) {
      $(this).addClass('is-invalid');
      $('#transfer_amount_error_msg').removeClass('d-none');
    } else {
      $(this).removeClass('is-invalid');
      $('#transfer_amount_error_msg').addClass('d-none');
    }
  });
});
</script>
