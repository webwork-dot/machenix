<?php
$id = (int)($param2 ?? 0);
$company_id = (int)$this->session->userdata('company_id');

$transfer = $this->db->get_where('transferred_cash', ['id' => $id, 'is_deleted' => 0])->row_array();
if (empty($transfer)) {
  echo '<div class="alert alert-danger mb-0">Payment collection not found.</div>';
  return;
}

$from_type = (($transfer['converted_from'] ?? '') === 'unofficial') ? 'unofficial' : 'official';
if (empty($transfer['converted_from']) && !empty($transfer['payment_type'])) {
  $from_type = ($transfer['payment_type'] === 'unofficial') ? 'unofficial' : 'official';
}

$from_company = $transfer['company_name'] ?? '';
if ($from_company === '' && !empty($transfer['company_id'])) {
  $c = $this->db->get_where('company', ['id' => $transfer['company_id']])->row_array();
  $from_company = $c['name'] ?? '-';
}

$bank_accounts = $this->common_model->getResultById('bank_accounts', 'id, bank_name, account_no', [
  'is_delete'  => '0',
  'company_id' => $company_id,
]);
$bank_accounts = ($bank_accounts != '') ? $bank_accounts : [];

$is_unofficial = ($from_type === 'unofficial');
$type_badge_class = $is_unofficial ? 'bg-light-secondary text-secondary' : 'bg-light-primary text-primary';
$accent_border = $is_unofficial ? '#82868b' : '#7367f0';
$amount_class = $is_unofficial ? 'text-secondary' : 'text-primary';
$method_from = strtolower($transfer['method_from'] ?? '');
$method_from_label = ($method_from === 'cheque') ? 'Bank' : (($method_from === 'cash') ? 'Cash' : '-');
$method_from_badge = ($method_from === 'cheque')
  ? 'bg-light-info text-info'
  : (($method_from === 'cash') ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary');
?>

<style>
  .approve-cash-modal .summary-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    border-top: 4px solid <?= $accent_border; ?>;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    overflow: hidden;
  }
  .approve-cash-modal .summary-top {
    padding: 1rem 1.1rem .85rem;
    background: linear-gradient(180deg, #f8f8fc 0%, #ffffff 100%);
  }
  .approve-cash-modal .summary-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: #6e6b7b;
    margin-bottom: 4px;
  }
  .approve-cash-modal .summary-company {
    font-size: 1.05rem;
    font-weight: 700;
    color: #2b2b2b;
    line-height: 1.3;
  }
  .approve-cash-modal .summary-amount {
    font-size: 1.45rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
  }
  .approve-cash-modal .summary-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem .75rem;
    padding: .75rem 1.1rem;
    border-top: 1px dashed #ebe9f1;
    background: #fafafa;
  }
  .approve-cash-modal .summary-meta-item {
    flex: 1 1 140px;
    min-width: 120px;
  }
  .approve-cash-modal .summary-meta-item .value {
    font-size: 13px;
    font-weight: 600;
    color: #5e5873;
  }
  .approve-cash-modal .method-option {
    border: 1.5px solid #d8d6de;
    border-radius: 10px;
    padding: .85rem 1rem;
    cursor: pointer;
    transition: all .15s ease;
    background: #fff;
    height: 100%;
    display: flex;
    align-items: center;
    gap: .75rem;
    margin: 0;
  }
  .approve-cash-modal .method-option:hover {
    border-color: #7367f0;
    background: #f8f7ff;
  }
  .approve-cash-modal .method-option.active {
    border-color: #7367f0;
    background: #f3f2ff;
    box-shadow: 0 0 0 1px #7367f0;
  }
  .approve-cash-modal .method-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }
  .approve-cash-modal .method-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eeedf5;
    color: #7367f0;
    font-size: 18px;
    flex-shrink: 0;
  }
  .approve-cash-modal .method-option.active .method-icon {
    background: #7367f0;
    color: #fff;
  }
  .approve-cash-modal .method-title {
    font-weight: 700;
    color: #2b2b2b;
    margin: 0;
    font-size: 14px;
  }
  .approve-cash-modal .method-sub {
    font-size: 11.5px;
    color: #6e6b7b;
    margin: 0;
  }
  .approve-cash-modal .type-note {
    font-size: 12px;
    color: #6e6b7b;
    background: #f3f2f7;
    border-radius: 8px;
    padding: .55rem .75rem;
  }
</style>

<div class="row approve-cash-modal">
  <div class="col-12">

    <div class="summary-card mb-2">
      <div class="summary-top">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
          <div class="pe-1" style="min-width: 0; flex: 1;">
            <div class="summary-label">From Company</div>
            <div class="summary-company text-truncate" title="<?= htmlspecialchars($from_company ?: '-'); ?>">
              <?= htmlspecialchars($from_company ?: '-'); ?>
            </div>
          </div>
          <div class="text-md-end">
            <div class="summary-label">Amount</div>
            <p class="summary-amount <?= $amount_class; ?>">₹ <?= number_format((float)$transfer['amount'], 2); ?></p>
          </div>
        </div>
      </div>
      <div class="summary-meta">
        <div class="summary-meta-item">
          <div class="summary-label">Type</div>
          <span class="badge <?= $type_badge_class; ?> font-weight-bold"><?= ucfirst($from_type); ?></span>
        </div>
        <div class="summary-meta-item">
          <div class="summary-label">Sent As</div>
          <span class="badge <?= $method_from_badge; ?> font-weight-bold"><?= $method_from_label; ?></span>
        </div>
        <?php if (!empty($transfer['remark'])): ?>
        <div class="summary-meta-item" style="flex: 2 1 200px;">
          <div class="summary-label">Remark</div>
          <div class="value"><?= htmlspecialchars($transfer['remark']); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($transfer['added_by_name'])): ?>
        <div class="summary-meta-item">
          <div class="summary-label">Transferred By</div>
          <div class="value"><?= htmlspecialchars($transfer['added_by_name']); ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="type-note mb-2">
      <i class="feather icon-info me-50"></i>
      Receive type stays <b><?= ucfirst($from_type); ?></b> — same as transferred. Choose how to receive the amount.
    </div>

    <?php echo form_open('inventory/cash_collection/approve/' . $id, ['id' => 'approve_cash_collection_form', 'onsubmit' => 'return submitApproveCashCollection(event);']); ?>
    <input type="hidden" name="converted_to" value="<?= html_escape($from_type); ?>">
    <input type="hidden" name="method_to" id="approve_method_to" value="">

    <div class="mb-1">
      <label class="form-label font-weight-bold mb-50">Receive Method <span class="required">*</span></label>
      <div class="row g-1">
        <div class="col-6">
          <label class="method-option" id="method_opt_cash" for="method_radio_cash">
            <input type="radio" name="method_to_radio" id="method_radio_cash" value="cash">
            <span class="method-icon"><i class="feather icon-dollar-sign"></i></span>
            <span>
              <p class="method-title">Cash</p>
              <p class="method-sub">Add to cash in hand</p>
            </span>
          </label>
        </div>
        <div class="col-6">
          <label class="method-option" id="method_opt_bank" for="method_radio_bank">
            <input type="radio" name="method_to_radio" id="method_radio_bank" value="cheque">
            <span class="method-icon"><i class="feather icon-credit-card"></i></span>
            <span>
              <p class="method-title">Bank</p>
              <p class="method-sub">Credit to bank account</p>
            </span>
          </label>
        </div>
      </div>
    </div>

    <div class="mb-2" id="approve_bank_wrap" style="display:none;">
      <div class="form-group mb-0">
        <label>Bank Account <span class="required">*</span></label>
        <select class="form-control select2" name="company_bank_to" id="approve_company_bank_to">
          <option value="">Select bank account</option>
          <?php foreach ($bank_accounts as $bank): ?>
            <option value="<?= $bank['id']; ?>" data-account-no="<?= htmlspecialchars($bank['account_no']); ?>">
              <?= htmlspecialchars($bank['bank_name'] . ' (' . $bank['account_no'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="company_bank_account_to" id="approve_company_bank_account_to" value="">
      </div>
    </div>

    <div class="d-flex flex-wrap gap-1 mt-1">
      <button type="submit" id="approve_cash_submit_btn" class="btn btn-success">
        <i class="feather icon-check"></i> Approve & Receive
      </button>
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<script>
function setApproveMethod(method) {
  $('#approve_method_to').val(method || '');
  $('#method_opt_cash, #method_opt_bank').removeClass('active');
  if (method === 'cash') {
    $('#method_opt_cash').addClass('active');
    $('#method_radio_cash').prop('checked', true);
  } else if (method === 'cheque') {
    $('#method_opt_bank').addClass('active');
    $('#method_radio_bank').prop('checked', true);
  }
  toggleApproveBank();
}

function toggleApproveBank() {
  var isBank = $('#approve_method_to').val() === 'cheque';
  $('#approve_bank_wrap').toggle(isBank);
  $('#approve_company_bank_to').prop('required', isBank);
  if (!isBank) {
    $('#approve_company_bank_to').val('').trigger('change');
    $('#approve_company_bank_account_to').val('');
  }
}

function submitApproveCashCollection(event) {
  event.preventDefault();

  var methodTo = $('#approve_method_to').val();
  if (!methodTo) {
    Swal.fire({ title: "Method Required", text: "Please select Cash or Bank", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }

  if (methodTo === 'cheque' && !$('#approve_company_bank_to').val()) {
    Swal.fire({ title: "Bank Required", text: "Please select a bank account", icon: "warning", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
    return false;
  }

  var $btn = $('#approve_cash_submit_btn');
  var original = $btn.html();
  $btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
  if (typeof $(".loader") !== 'undefined') { $(".loader").show(); }

  $.ajax({
    type: 'POST',
    url: $('#approve_cash_collection_form').attr('action'),
    data: $('#approve_cash_collection_form').serialize(),
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      if (res.status == 200 || res.status == '200') {
        Swal.fire({ title: "Success!", text: res.message || "Approved successfully", icon: "success", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false }).then(function() {
          $('#scrollable-modal').modal('hide');
          window.location.href = res.url || location.href;
        });
      } else {
        Swal.fire({ title: "Error!", text: res.message || "Failed to approve", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
        $btn.html(original).attr('disabled', false);
      }
    },
    error: function() {
      if (typeof $(".loader") !== 'undefined') { $(".loader").fadeOut("slow"); }
      Swal.fire({ title: "Error!", text: "An error occurred while processing request", icon: "error", customClass: { confirmButton: "btn btn-primary" }, buttonsStyling: false });
      $btn.html(original).attr('disabled', false);
    }
  });

  return false;
}

$(document).ready(function() {
  if ($.fn.select2) {
    $('#approve_company_bank_to').select2({
      dropdownParent: $('#scrollable-modal'),
      width: '100%'
    });
  }

  $('input[name="method_to_radio"]').on('change', function() {
    setApproveMethod($(this).val());
  });

  $('#method_opt_cash, #method_opt_bank').on('click', function(e) {
    e.preventDefault();
    var val = $(this).find('input').val();
    setApproveMethod(val);
  });

  $('#approve_company_bank_to').on('change', function() {
    $('#approve_company_bank_account_to').val($(this).find(':selected').data('account-no') || '');
  });

  setApproveMethod('');
});
</script>
