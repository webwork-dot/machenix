<?php
$id = (int)($param2 ?? 0);
$company_id = (int)$this->session->userdata('company_id');

$transfer = $this->db->get_where('transferred_cash', ['id' => $id, 'is_deleted' => 0])->row_array();
if (empty($transfer) || (int)($transfer['company_id'] ?? 0) !== $company_id) {
  echo '<div class="alert alert-danger mb-0">Transfer not found.</div>';
  return;
}
if (!empty($transfer['is_approved'])) {
  echo '<div class="alert alert-warning mb-0">Approved transfers cannot be edited.</div>';
  return;
}

$bank_summary    = $this->inventory_model->get_bank_in_hand_summary($company_id);
$official_bank   = (float)($bank_summary['official']['bank_in_hand'] ?? 0);
$unofficial_bank = (float)($bank_summary['unofficial']['bank_in_hand'] ?? 0);

$current_type = (($transfer['converted_from'] ?? '') === 'unofficial') ? 'unofficial' : 'official';
$current_amount = (float)($transfer['amount'] ?? 0);
if ($current_type === 'unofficial') {
  $unofficial_bank += $current_amount;
} else {
  $official_bank += $current_amount;
}

$companies = $this->common_model->getResultById('company', 'id, name', ['is_deleted' => 0]);
$companies = ($companies != '') ? $companies : [];
$selected_to = (int)($transfer['company_to_id'] ?? 0);
$selected_bank = (int)($transfer['company_bank_from'] ?? 0);

$bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', [
  'is_delete'  => '0',
  'company_id' => $company_id,
]);
$bank_accounts = ($bank_accounts != '') ? $bank_accounts : [];
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/bank_book/transfer_edit_post/' . $id, ['id' => 'transfer_bank_edit_form', 'onsubmit' => 'return submitTransferBankEditForm(event);']); ?>
    <input type="hidden" name="method_from" value="cheque">

    <div class="row">
      <div class="col-12 mb-2">
        <div class="row g-1">
          <div class="col-md-6 mb-1 mb-md-0">
            <div class="card bg-light-primary border-primary shadow-none mb-0">
              <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h6 class="text-primary mb-25 font-weight-bold">Official Bank in Hand</h6>
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
            <option value="official" <?= $current_type === 'official' ? 'selected' : ''; ?>>Official</option>
            <option value="unofficial" <?= $current_type === 'unofficial' ? 'selected' : ''; ?>>Unofficial</option>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>From Bank Account <span class="required">*</span></label>
          <select class="form-control select2" name="company_bank_from" id="company_bank_from" required>
            <option value="">Select Bank</option>
            <?php foreach ($bank_accounts as $bank): ?>
              <option value="<?= $bank['id']; ?>" data-account-no="<?= htmlspecialchars($bank['account_no']); ?>" <?= ((int)$bank['id'] === $selected_bank) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($bank['bank_name'] . ' (' . $bank['account_no'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="company_bank_account_from" id="company_bank_account_from" value="<?= htmlspecialchars($transfer['company_bank_account_from'] ?? ''); ?>">
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Transfer Amount (in INR) <span class="required">*</span></label>
          <input type="number" name="amount" id="transfer_amount" class="form-control" value="<?= htmlspecialchars(number_format($current_amount, 2, '.', '')); ?>" min="0.01" step="0.01" required>
          <small class="text-danger mt-25 d-none font-weight-bold" id="transfer_amount_error_msg">Amount cannot exceed available bank in hand</small>
        </div>
      </div>

      <div class="col-md-12 mb-2">
        <div class="form-group">
          <label>Remark / Narration</label>
          <textarea class="form-control" rows="3" name="remark"><?= htmlspecialchars($transfer['remark'] ?? ''); ?></textarea>
        </div>
      </div>

      <div class="col-12">
        <button type="submit" id="transfer_submit_btn" class="btn btn-primary me-1"><i class="feather icon-check"></i> Update Transfer</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<script>
var transferBankByType = { official: <?= json_encode($official_bank); ?>, unofficial: <?= json_encode($unofficial_bank); ?> };
var maxTransferBank = 0;
function getTransferBankLimit() {
  var type = $('#transfer_payment_type').val();
  return (type === 'official' || type === 'unofficial') ? (parseFloat(transferBankByType[type]) || 0) : 0;
}
function updateTransferBankAmountLimit() {
  maxTransferBank = getTransferBankLimit();
  var type = $('#transfer_payment_type').val();
  $('#transfer_amount').attr('max', maxTransferBank)
    .attr('placeholder', 'Enter amount (Max: ₹' + maxTransferBank.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
  $('#transfer_amount_error_msg').text('Amount cannot exceed available ' + type + ' bank in hand (₹' + maxTransferBank.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
}
function submitTransferBankEditForm(event) {
  event.preventDefault();
  var type = $('#transfer_payment_type').val();
  var amount = parseFloat($('#transfer_amount').val()) || 0;
  maxTransferBank = getTransferBankLimit();
  if (!$('#company_to_id').val() || !type || !$('#company_bank_from').val() || amount <= 0) {
    Swal.fire({ title: "Required", text: "Please fill company, type, bank and amount", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (amount > maxTransferBank) {
    Swal.fire({ title: "Amount Exceeded", text: "Amount cannot exceed available " + type + " bank in hand", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  var $btn = $('#transfer_submit_btn');
  var original = $btn.html();
  $btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
  $.ajax({
    type: 'POST',
    url: $('#transfer_bank_edit_form').attr('action'),
    data: $('#transfer_bank_edit_form').serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.status == 200 || res.status == '200') {
        Swal.fire({ title: "Success!", text: res.message, icon: "success", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false }).then(function() {
          $('#scrollable-modal').modal('hide');
          window.location.href = res.url || location.href;
        });
      } else {
        Swal.fire({ title: "Error!", text: res.message || "Failed", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
        $btn.html(original).attr('disabled', false);
      }
    },
    error: function() {
      Swal.fire({ title: "Error!", text: "Request failed", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
      $btn.html(original).attr('disabled', false);
    }
  });
  return false;
}
$(document).ready(function() {
  if ($.fn.select2) {
    $('#company_to_id, #transfer_payment_type, #company_bank_from').select2({ dropdownParent: $('#scrollable-modal'), width: '100%' });
  }
  $('#transfer_payment_type').on('change', updateTransferBankAmountLimit);
  updateTransferBankAmountLimit();
  $('#company_bank_from').on('change', function() {
    $('#company_bank_account_from').val($(this).find(':selected').data('account-no') || '');
  }).trigger('change');
});
</script>
