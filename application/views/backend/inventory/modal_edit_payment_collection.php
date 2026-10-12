<?php
$id = (int)($param2 ?? 0);
$session_company_id = (int)$this->session->userdata('company_id');

$transfer = $this->db->get_where('transferred_cash', [
  'id'         => $id,
  'is_deleted' => 0,
])->row_array();

if (
  empty($transfer)
  || (int)($transfer['is_reverse'] ?? 0) !== 1
  || !empty($transfer['is_approved'])
  || (int)($transfer['company_to_id'] ?? 0) !== $session_company_id
) {
  echo '<div class="alert alert-danger mb-0">Reverse payment collection not found or cannot be edited.</div>';
  return;
}

$companies = $this->common_model->getResultById('company', 'id, name', ['is_deleted' => 0]);
$companies = ($companies != '') ? $companies : [];

$bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', [
  'is_delete'  => '0',
  'company_id' => $session_company_id,
]);
$bank_accounts = ($bank_accounts != '') ? $bank_accounts : [];

$method_from = strtolower($transfer['method_from'] ?? 'cash');
$converted_from = (($transfer['converted_from'] ?? '') === 'unofficial') ? 'unofficial' : 'official';
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/payment-collection/edit_post/' . $id, ['id' => 'edit_payment_collection_form', 'onsubmit' => 'return submitEditPaymentCollectionForm(event);']); ?>

    <div class="row">
      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Company <span class="required">*</span></label>
          <select class="form-control select2" name="company_id" id="pc_edit_company_id" required>
            <option value="">Select Company</option>
            <?php foreach ($companies as $company): ?>
              <?php if ((string)$company['id'] === (string)$session_company_id) continue; ?>
              <option value="<?php echo $company['id']; ?>" <?php echo ((int)$company['id'] === (int)$transfer['company_id']) ? 'selected' : ''; ?>>
                <?php echo html_escape($company['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Type <span class="required">*</span></label>
          <select class="form-control select2" name="converted_from" id="pc_edit_payment_type" required>
            <option value="">Select</option>
            <option value="official" <?php echo ($converted_from === 'official') ? 'selected' : ''; ?>>Official</option>
            <option value="unofficial" <?php echo ($converted_from === 'unofficial') ? 'selected' : ''; ?>>Unofficial</option>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Source <span class="required">*</span></label>
          <select class="form-control select2" name="method_from" id="pc_edit_method_from" required>
            <option value="">Select</option>
            <option value="cash" <?php echo ($method_from === 'cash') ? 'selected' : ''; ?>>Cash</option>
            <option value="cheque" <?php echo ($method_from === 'cheque') ? 'selected' : ''; ?>>Bank</option>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1 <?php echo ($method_from === 'cheque') ? '' : 'd-none'; ?>" id="pc_edit_bank_wrap">
        <div class="form-group">
          <label>Bank Account <span class="required">*</span></label>
          <select class="form-control select2" name="company_bank_to" id="pc_edit_company_bank_to">
            <option value="">Select Bank</option>
            <?php foreach ($bank_accounts as $bank): ?>
              <option value="<?= $bank['id']; ?>"
                data-account-no="<?= htmlspecialchars($bank['account_no']); ?>"
                <?php echo ((int)$bank['id'] === (int)($transfer['company_bank_to'] ?? 0)) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($bank['bank_name'] . ' (' . $bank['account_no'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="company_bank_account_to" id="pc_edit_company_bank_account_to" value="<?= htmlspecialchars($transfer['company_bank_account_to'] ?? ''); ?>">
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Amount (in INR) <span class="required">*</span></label>
          <input type="number" name="amount" id="pc_edit_amount" class="form-control" value="<?= htmlspecialchars((float)$transfer['amount']); ?>" min="0.01" step="0.01" required>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Remark</label>
          <textarea name="remark" id="pc_edit_remark" class="form-control" rows="2"><?= htmlspecialchars($transfer['remark'] ?? ''); ?></textarea>
        </div>
      </div>

      <div class="col-12 mt-1">
        <button type="submit" id="pc_edit_submit_btn" class="btn btn-primary">Update</button>
      </div>
    </div>

    <?php echo form_close(); ?>
  </div>
</div>

<script>
function togglePcEditBankField() {
  var method = $('#pc_edit_method_from').val();
  if (method === 'cheque') {
    $('#pc_edit_bank_wrap').removeClass('d-none');
    $('#pc_edit_company_bank_to').prop('required', true);
  } else {
    $('#pc_edit_bank_wrap').addClass('d-none');
    $('#pc_edit_company_bank_to').prop('required', false);
  }
}

function submitEditPaymentCollectionForm(event) {
  event.preventDefault();

  var companyId = $('#pc_edit_company_id').val();
  var type = $('#pc_edit_payment_type').val();
  var method = $('#pc_edit_method_from').val();
  var amount = parseFloat($('#pc_edit_amount').val()) || 0;

  if (!companyId || !type || !method) {
    Swal.fire({ title: "Required Fields", text: "Please fill all required fields", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (method === 'cheque' && !$('#pc_edit_company_bank_to').val()) {
    Swal.fire({ title: "Bank Required", text: "Please select a bank account", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (amount <= 0) {
    Swal.fire({ title: "Invalid Amount", text: "Please enter an amount greater than 0", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }

  var $submitBtn = $('#pc_edit_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  if (typeof $(".loader") !== 'undefined') { $(".loader").show(); }

  $.ajax({
    type: 'POST',
    url: $('#edit_payment_collection_form').attr('action'),
    data: $('#edit_payment_collection_form').serialize(),
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      if (res.status == '200' || res.status == 200) {
        Swal.fire({ title: "Success!", text: res.message || "Updated successfully", icon: "success", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false }).then(() => {
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
    $('#pc_edit_company_id, #pc_edit_payment_type, #pc_edit_method_from, #pc_edit_company_bank_to').select2({
      dropdownParent: $('#scrollable-modal'),
      width: '100%'
    });
  }
  $('#pc_edit_method_from').on('change', togglePcEditBankField);
  togglePcEditBankField();
  $('#pc_edit_company_bank_to').on('change', function() {
    var accountNo = $(this).find('option:selected').data('account-no') || '';
    $('#pc_edit_company_bank_account_to').val(accountNo);
  });
});
</script>
