<?php
$id = (int)($param2 ?? 0);
$company_id = (int)$this->session->userdata('company_id');

$transfer = $this->db->get_where('transferred_cash', ['id' => $id, 'is_deleted' => 0])->row_array();
if (
  empty($transfer)
  || (int)($transfer['is_reverse'] ?? 0) !== 1
  || !empty($transfer['is_approved'])
  || (int)($transfer['company_id'] ?? 0) !== $company_id
) {
  echo '<div class="alert alert-danger mb-0">Pending transfer not found or cannot be approved.</div>';
  return;
}

$to_company = $transfer['company_to'] ?? '';
if ($to_company === '' && !empty($transfer['company_to_id'])) {
  $c = $this->db->get_where('company', ['id' => $transfer['company_to_id']])->row_array();
  $to_company = $c['name'] ?? '-';
}

$type_val = (($transfer['converted_from'] ?? '') === 'unofficial') ? 'unofficial' : 'official';
$method_from = strtolower($transfer['method_from'] ?? 'cash');
$is_cheque = ($method_from === 'cheque');
$type_badge_class = ($type_val === 'unofficial') ? 'bg-light-secondary text-secondary' : 'bg-light-primary text-primary';
$method_label = $is_cheque ? 'Bank' : 'Cash';
$method_badge = $is_cheque ? 'bg-light-info text-info' : 'bg-light-success text-success';

$bank_accounts = [];
if ($is_cheque) {
  $bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', [
    'is_delete'  => '0',
    'company_id' => $company_id,
  ]);
  $bank_accounts = ($bank_accounts != '') ? $bank_accounts : [];
}
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/payment-collection/approve_pending_transfer/' . $id, ['id' => 'approve_pending_transfer_form', 'onsubmit' => 'return submitApprovePendingTransferForm(event);']); ?>

    <div class="card border shadow-none mb-2">
      <div class="card-body p-2">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
          <div>
            <small class="text-muted text-uppercase font-weight-bold">Transfer To</small>
            <div class="font-weight-bold"><?= htmlspecialchars($to_company ?: '-'); ?></div>
          </div>
          <div class="text-end">
            <small class="text-muted text-uppercase font-weight-bold">Amount</small>
            <div class="h4 mb-0 text-primary font-weight-bolder">₹ <?= number_format((float)$transfer['amount'], 2); ?></div>
          </div>
        </div>
        <div class="mt-1 d-flex flex-wrap gap-1">
          <span class="badge <?= $type_badge_class; ?>"><?= ucfirst($type_val); ?></span>
          <span class="badge <?= $method_badge; ?>"><?= $method_label; ?></span>
        </div>
        <?php if (!empty($transfer['remark'])): ?>
          <div class="mt-1"><small class="text-muted">Remark:</small> <?= htmlspecialchars($transfer['remark']); ?></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($is_cheque): ?>
    <div class="form-group mb-1">
      <label>From Bank Account <span class="required">*</span></label>
      <select class="form-control select2" name="company_bank_from" id="pt_company_bank_from" required>
        <option value="">Select Bank</option>
        <?php foreach ($bank_accounts as $bank): ?>
          <option value="<?= $bank['id']; ?>" data-account-no="<?= htmlspecialchars($bank['account_no']); ?>">
            <?= htmlspecialchars($bank['bank_name'] . ' (' . $bank['account_no'] . ')'); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="company_bank_account_from" id="pt_company_bank_account_from" value="">
    </div>
    <?php endif; ?>

    <div class="mt-1">
      <button type="submit" id="pt_approve_btn" class="btn btn-success">Approve Transfer</button>
    </div>

    <?php echo form_close(); ?>
  </div>
</div>

<script>
function submitApprovePendingTransferForm(event) {
  event.preventDefault();

  <?php if ($is_cheque): ?>
  if (!$('#pt_company_bank_from').val()) {
    Swal.fire({ title: "Bank Required", text: "Please select a bank account", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  <?php endif; ?>

  var $submitBtn = $('#pt_approve_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  if (typeof $(".loader") !== 'undefined') { $(".loader").show(); }

  $.ajax({
    type: 'POST',
    url: $('#approve_pending_transfer_form').attr('action'),
    data: $('#approve_pending_transfer_form').serialize(),
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      if (res.status == '200' || res.status == 200) {
        Swal.fire({ title: "Success!", text: res.message || "Transfer approved successfully", icon: "success", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false }).then(() => {
          $('#scrollable-modal').modal('hide');
          window.location.href = res.url || location.href;
        });
      } else {
        Swal.fire({ title: "Error!", text: res.message || "An error occurred", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
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
    $('#pt_company_bank_from').select2({
      dropdownParent: $('#scrollable-modal'),
      width: '100%'
    });
  }
  $('#pt_company_bank_from').on('change', function() {
    var accountNo = $(this).find('option:selected').data('account-no') || '';
    $('#pt_company_bank_account_from').val(accountNo);
  });
});
</script>
