<?php
$company_id      = $this->session->userdata('company_id');
$bank_summary    = $this->inventory_model->get_bank_in_hand_summary($company_id);
$official_bank   = (float)($bank_summary['official']['bank_in_hand'] ?? 0);
$unofficial_bank = (float)($bank_summary['unofficial']['bank_in_hand'] ?? 0);

$companies = $this->common_model->getResultById('company', 'id, name', ['is_deleted' => 0]);
$companies = ($companies != '') ? $companies : [];

$bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', [
  'is_delete'  => '0',
  'company_id' => $company_id,
]);
$bank_accounts = ($bank_accounts != '') ? $bank_accounts : [];
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/bank_book/transfer_post', ['id' => 'transfer_bank_form', 'onsubmit' => 'return submitTransferBankForm(event);']); ?>
    <input type="hidden" name="method_from" value="cheque">

    <div class="row">
      <div class="col-12 mb-2">
        <div class="row g-1">
          <div class="col-md-6 mb-1 mb-md-0">
            <div class="card bg-light-primary border-primary shadow-none mb-0">
              <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h6 class="text-primary mb-25 font-weight-bold">Official Bank in Hand</h6>
                  <small class="text-muted">Available to transfer</small>
                </div>
                <div>
                  <h4 class="<?= $official_bank < 0 ? 'text-danger' : 'text-primary'; ?> font-weight-bolder mb-0"><?= $official_bank < 0 ? '- ₹ ' . number_format(abs($official_bank), 2) : '₹ ' . number_format($official_bank, 2); ?></h4>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card bg-light-secondary border-secondary shadow-none mb-0">
              <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h6 class="text-secondary mb-25 font-weight-bold">Unofficial Bank in Hand</h6>
                  <small class="text-muted">Available to transfer</small>
                </div>
                <div>
                  <h4 class="<?= $unofficial_bank < 0 ? 'text-danger' : 'text-secondary'; ?> font-weight-bolder mb-0"><?= $unofficial_bank < 0 ? '- ₹ ' . number_format(abs($unofficial_bank), 2) : '₹ ' . number_format($unofficial_bank, 2); ?></h4>
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
              <option value="<?php echo $company['id']; ?>"><?php echo html_escape($company['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Type <span class="required">*</span></label>
          <select class="form-control select2" name="converted_from" id="transfer_payment_type" required>
            <option value="">Select</option>
            <option value="official">Official</option>
            <option value="unofficial">Unofficial</option>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>From Bank Account <span class="required">*</span></label>
          <select class="form-control select2" name="company_bank_from" id="company_bank_from" required>
            <option value="">Select Bank</option>
            <?php foreach ($bank_accounts as $bank): ?>
              <option value="<?= $bank['id']; ?>" data-account-no="<?= htmlspecialchars($bank['account_no']); ?>">
                <?= htmlspecialchars($bank['bank_name'] . ' (' . $bank['account_no'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="company_bank_account_from" id="company_bank_account_from" value="">
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Transfer Amount (in INR) <span class="required">*</span></label>
          <input type="number" name="amount" id="transfer_amount" class="form-control" value="" min="0.01" step="0.01" placeholder="Select type first" required disabled>
          <small class="text-danger mt-25 d-none font-weight-bold" id="transfer_amount_error_msg">Amount cannot exceed available bank in hand</small>
        </div>
      </div>

      <div class="col-md-12 mb-2">
        <div class="form-group">
          <label class="control-label">Remark / Narration</label>
          <textarea class="form-control" rows="3" placeholder="Enter remark or narration..." name="remark"></textarea>
        </div>
      </div>

      <div class="col-12">
        <button type="submit" id="transfer_submit_btn" class="btn btn-primary me-1">
          <i class="feather icon-check"></i> Transfer
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<script>
var transferBankByType = {
  official: <?= json_encode($official_bank); ?>,
  unofficial: <?= json_encode($unofficial_bank); ?>
};
var maxTransferBank = 0;

function getTransferBankLimit() {
  var type = $('#transfer_payment_type').val();
  if (type === 'official' || type === 'unofficial') {
    return parseFloat(transferBankByType[type]) || 0;
  }
  return 0;
}

function updateTransferBankAmountLimit() {
  maxTransferBank = getTransferBankLimit();
  var $amount = $('#transfer_amount');
  var type = $('#transfer_payment_type').val();
  if (!type) {
    $amount.prop('disabled', true).attr('max', '').attr('placeholder', 'Select type first');
    $('#transfer_amount_error_msg').addClass('d-none');
    return;
  }
  $amount.prop('disabled', false)
    .attr('max', maxTransferBank)
    .attr('placeholder', 'Enter amount (Max: ₹' + maxTransferBank.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
  $('#transfer_amount_error_msg').text('Amount cannot exceed available ' + type + ' bank in hand (₹' + maxTransferBank.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
  $amount.trigger('input');
}

function submitTransferBankForm(event) {
  event.preventDefault();
  var type = $('#transfer_payment_type').val();
  var companyTo = $('#company_to_id').val();
  var bankFrom = $('#company_bank_from').val();
  var amount = parseFloat($('#transfer_amount').val()) || 0;
  maxTransferBank = getTransferBankLimit();

  if (!companyTo) {
    Swal.fire({ title: "Company Required", text: "Please select a company to transfer to", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (!type) {
    Swal.fire({ title: "Type Required", text: "Please select official or unofficial type", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (!bankFrom) {
    Swal.fire({ title: "Bank Required", text: "Please select a bank account", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (amount <= 0) {
    Swal.fire({ title: "Invalid Amount", text: "Please enter an amount greater than 0", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (amount > maxTransferBank) {
    Swal.fire({ title: "Amount Exceeded", text: "Amount cannot exceed available " + type + " bank in hand (₹" + maxTransferBank.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ")", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }

  var $submitBtn = $('#transfer_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
  if (typeof $(".loader") !== 'undefined') { $(".loader").show(); }

  $.ajax({
    type: 'POST',
    url: $('#transfer_bank_form').attr('action'),
    data: $('#transfer_bank_form').serialize(),
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      if (res.status == '200' || res.status == 200) {
        Swal.fire({ title: "Success!", text: res.message || "Transferred successfully", icon: "success", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false }).then(function() {
          $('#scrollable-modal').modal('hide');
          window.location.href = res.url || location.href;
        });
      } else {
        Swal.fire({ title: "Error!", text: res.message || "Transfer failed", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
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
    $('#company_to_id, #transfer_payment_type, #company_bank_from').select2({
      dropdownParent: $('#scrollable-modal'),
      width: '100%'
    });
  }
  $('#transfer_payment_type').on('change', updateTransferBankAmountLimit);
  updateTransferBankAmountLimit();
  $('#company_bank_from').on('change', function() {
    $('#company_bank_account_from').val($(this).find(':selected').data('account-no') || '');
  });
  $('#transfer_amount').on('input change', function() {
    var val = parseFloat($(this).val()) || 0;
    maxTransferBank = getTransferBankLimit();
    if ($('#transfer_payment_type').val() && val > maxTransferBank) {
      $(this).addClass('is-invalid');
      $('#transfer_amount_error_msg').removeClass('d-none');
    } else {
      $(this).removeClass('is-invalid');
      $('#transfer_amount_error_msg').addClass('d-none');
    }
  });
});
</script>
