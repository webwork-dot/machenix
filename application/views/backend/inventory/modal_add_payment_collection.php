<?php
$company_id = $this->session->userdata('company_id');

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
    <?php echo form_open('inventory/payment-collection/add_post', ['id' => 'add_payment_collection_form', 'onsubmit' => 'return submitAddPaymentCollectionForm(event);']); ?>

    <div class="row">
      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Company <span class="required">*</span></label>
          <select class="form-control select2" name="company_id" id="pc_company_id" required>
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
          <select class="form-control select2" name="converted_from" id="pc_payment_type" required>
            <option value="">Select</option>
            <option value="official">Official</option>
            <option value="unofficial">Unofficial</option>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Source <span class="required">*</span></label>
          <select class="form-control select2" name="method_from" id="pc_method_from" required>
            <option value="">Select</option>
            <option value="cash">Cash</option>
            <option value="cheque">Bank</option>
          </select>
        </div>
      </div>

      <div class="col-md-12 mb-1 d-none" id="pc_bank_wrap">
        <div class="form-group">
          <label>Bank Account <span class="required">*</span></label>
          <select class="form-control select2" name="company_bank_to" id="pc_company_bank_to">
            <option value="">Select Bank</option>
            <?php foreach ($bank_accounts as $bank): ?>
              <option value="<?= $bank['id']; ?>" data-account-no="<?= htmlspecialchars($bank['account_no']); ?>">
                <?= htmlspecialchars($bank['bank_name'] . ' (' . $bank['account_no'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="company_bank_account_to" id="pc_company_bank_account_to" value="">
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Amount (in INR) <span class="required">*</span></label>
          <input type="number" name="amount" id="pc_amount" class="form-control" value="" min="0.01" step="0.01" placeholder="Enter amount" required>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Remark</label>
          <textarea name="remark" id="pc_remark" class="form-control" rows="2" placeholder="Optional remark"></textarea>
        </div>
      </div>

      <div class="col-12 mt-1">
        <button type="submit" id="pc_submit_btn" class="btn btn-primary">Submit</button>
      </div>
    </div>

    <?php echo form_close(); ?>
  </div>
</div>

<script>
function togglePcBankField() {
  var method = $('#pc_method_from').val();
  if (method === 'cheque') {
    $('#pc_bank_wrap').removeClass('d-none');
    $('#pc_company_bank_to').prop('required', true);
  } else {
    $('#pc_bank_wrap').addClass('d-none');
    $('#pc_company_bank_to').prop('required', false).val('').trigger('change');
    $('#pc_company_bank_account_to').val('');
  }
}

function submitAddPaymentCollectionForm(event) {
  event.preventDefault();

  var companyId = $('#pc_company_id').val();
  var type = $('#pc_payment_type').val();
  var method = $('#pc_method_from').val();
  var amount = parseFloat($('#pc_amount').val()) || 0;

  if (!companyId) {
    Swal.fire({ title: "Company Required", text: "Please select a company", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (!type) {
    Swal.fire({ title: "Type Required", text: "Please select official or unofficial type", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (!method) {
    Swal.fire({ title: "Source Required", text: "Please select Cash or Bank", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (method === 'cheque' && !$('#pc_company_bank_to').val()) {
    Swal.fire({ title: "Bank Required", text: "Please select a bank account", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }
  if (amount <= 0) {
    Swal.fire({ title: "Invalid Amount", text: "Please enter an amount greater than 0", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }

  var $submitBtn = $('#pc_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  if (typeof $(".loader") !== 'undefined') { $(".loader").show(); }

  $.ajax({
    type: 'POST',
    url: $('#add_payment_collection_form').attr('action'),
    data: $('#add_payment_collection_form').serialize(),
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      if (res.status == '200' || res.status == 200) {
        Swal.fire({ title: "Success!", text: res.message || "Payment collection added successfully", icon: "success", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false }).then(() => {
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
    $('#pc_company_id, #pc_payment_type, #pc_method_from, #pc_company_bank_to').select2({
      dropdownParent: $('#scrollable-modal'),
      width: '100%'
    });
  }

  $('#pc_method_from').on('change', togglePcBankField);
  togglePcBankField();

  $('#pc_company_bank_to').on('change', function() {
    var accountNo = $(this).find('option:selected').data('account-no') || '';
    $('#pc_company_bank_account_to').val(accountNo);
  });
});
</script>
