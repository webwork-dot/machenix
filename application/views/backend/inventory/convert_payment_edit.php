<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<style>
  .cash-stat-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 16px;
    height: 100%;
  }
  .cash-stat-card.card-official { border-top: 4px solid #7367f0; }
  .cash-stat-card.card-unofficial { border-top: 4px solid #82868b; }
  .cash-stat-card .stat-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #6e6b7b;
    margin-bottom: 4px;
  }
  .cash-stat-card .stat-value {
    font-size: 20px;
    font-weight: 800;
    margin: 0;
  }
</style>

<?php
  $official_cash   = (float)($official_cash ?? 0);
  $unofficial_cash = (float)($unofficial_cash ?? 0);
  $method_from = $data['method_from'] ?? 'cash';
  $method_to   = $data['method_to'] ?? 'cash';
  $conversion_direction = '';
  if (($data['converted_from'] ?? '') === 'official' && ($data['converted_to'] ?? '') === 'unofficial') {
    $conversion_direction = 'official_to_unofficial';
  } elseif (($data['converted_from'] ?? '') === 'unofficial' && ($data['converted_to'] ?? '') === 'official') {
    $conversion_direction = 'unofficial_to_official';
  }
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-2 my-0">

        <?php echo form_open('inventory/convert_payment/edit_post/' . $id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>

        <div class="row mb-2 g-2">
          <div class="col-md-6">
            <div class="cash-stat-card card-official">
              <div class="stat-title">Official Cash in Hand (for validation)</div>
              <p class="stat-value <?= $official_cash < 0 ? 'text-danger' : 'text-primary'; ?>">
                <?= $official_cash < 0 ? '- ₹ ' . number_format(abs($official_cash), 2) : '₹ ' . number_format($official_cash, 2); ?>
              </p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="cash-stat-card card-unofficial">
              <div class="stat-title">Unofficial Cash in Hand (for validation)</div>
              <p class="stat-value <?= $unofficial_cash < 0 ? 'text-danger' : 'text-secondary'; ?>">
                <?= $unofficial_cash < 0 ? '- ₹ ' . number_format(abs($unofficial_cash), 2) : '₹ ' . number_format($unofficial_cash, 2); ?>
              </p>
            </div>
          </div>
        </div>

        <div class="row mb-1">
          <div class="col-md-6">
            <div class="form-group">
              <label>Conversion <span class="required">*</span></label>
              <select class="form-control select2" name="conversion_direction" id="conversion_direction" required>
                <option value="">Select</option>
                <option value="official_to_unofficial" <?php if($conversion_direction == 'official_to_unofficial') echo 'selected'; ?>>Official to Unofficial</option>
                <option value="unofficial_to_official" <?php if($conversion_direction == 'unofficial_to_official') echo 'selected'; ?>>Unofficial to Official</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Amount (INR) <span class="required">*</span></label>
              <input type="number" name="amount" id="amount" class="form-control" min="0.01" step="0.01" value="<?php echo htmlspecialchars($data['amount'] ?? ''); ?>" required>
              <small class="text-muted" id="available_hint"></small>
            </div>
          </div>
        </div>

        <div class="row mb-1">
          <div class="col-md-3">
            <div class="form-group">
              <label>Method From <span class="required">*</span></label>
              <select class="form-control select2" name="method_from" id="method_from" required>
                <option value="">Select</option>
                <option value="cash" <?php if($method_from == 'cash') echo 'selected'; ?>>Cash</option>
                <option value="cheque" <?php if($method_from == 'cheque') echo 'selected'; ?>>Cheque</option>
              </select>
            </div>
          </div>
          <div class="col-md-3" id="bank_from_wrap" style="<?php echo ($method_from === 'cheque') ? '' : 'display:none;'; ?>">
            <div class="form-group">
              <label>Bank From <span class="required">*</span></label>
              <select class="form-control select2" name="company_bank_from" id="company_bank_from">
                <option value="">Select</option>
                <?php foreach ($bank_accounts as $value): ?>
                  <option value="<?php echo $value['id'];?>" data-account-no="<?php echo htmlspecialchars($value['account_no']);?>" <?php if((string)($data['company_bank_from'] ?? '') === (string)$value['id']) echo 'selected'; ?>>
                    <?php echo $value['bank_name'].' ('.$value['account_no'].')';?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="company_bank_account_from" id="company_bank_account_from" value="<?php echo htmlspecialchars($data['company_bank_account_from'] ?? ''); ?>">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Method To <span class="required">*</span></label>
              <select class="form-control select2" name="method_to" id="method_to" required>
                <option value="">Select</option>
                <option value="cash" <?php if($method_to == 'cash') echo 'selected'; ?>>Cash</option>
                <option value="cheque" <?php if($method_to == 'cheque') echo 'selected'; ?>>Cheque</option>
              </select>
            </div>
          </div>
          <div class="col-md-3" id="bank_to_wrap" style="<?php echo ($method_to === 'cheque') ? '' : 'display:none;'; ?>">
            <div class="form-group">
              <label>Bank To <span class="required">*</span></label>
              <select class="form-control select2" name="company_bank_to" id="company_bank_to">
                <option value="">Select</option>
                <?php foreach ($bank_accounts as $value): ?>
                  <option value="<?php echo $value['id'];?>" data-account-no="<?php echo htmlspecialchars($value['account_no']);?>" <?php if((string)($data['company_bank_to'] ?? '') === (string)$value['id']) echo 'selected'; ?>>
                    <?php echo $value['bank_name'].' ('.$value['account_no'].')';?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="company_bank_account_to" id="company_bank_account_to" value="<?php echo htmlspecialchars($data['company_bank_account_to'] ?? ''); ?>">
            </div>
          </div>
        </div>

        <div class="row mb-2">
          <div class="col-12">
            <div class="form-group">
              <label>Narration</label>
              <textarea class="form-control" rows="2" name="narration" placeholder="Narration / Remarks"><?php echo htmlspecialchars($data['narration'] ?? ''); ?></textarea>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btnf btn_verify" name="btn_verify"><?php echo get_phrase('update'); ?></button>
          </div>
        </div>

        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  var cashByType = {
    official: <?= json_encode($official_cash); ?>,
    unofficial: <?= json_encode($unofficial_cash); ?>
  };

  function getConvertFrom() {
    var dir = $('#conversion_direction').val();
    if (dir === 'official_to_unofficial') return 'official';
    if (dir === 'unofficial_to_official') return 'unofficial';
    return '';
  }

  function updateAvailableHint() {
    var from = getConvertFrom();
    var methodFrom = $('#method_from').val();
    if (from && methodFrom === 'cash') {
      var avail = parseFloat(cashByType[from]) || 0;
      $('#available_hint').text('Available ' + from + ' cash: ₹ ' + avail.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      $('#amount').attr('max', avail > 0 ? avail : '');
    } else {
      $('#available_hint').text('');
      $('#amount').removeAttr('max');
    }
  }

  function toggleBanks(isInitial) {
    var showFrom = $('#method_from').val() === 'cheque';
    var showTo = $('#method_to').val() === 'cheque';
    $('#bank_from_wrap').toggle(showFrom);
    $('#company_bank_from').prop('required', showFrom);
    if (!showFrom && !isInitial) {
      $('#company_bank_from').val('').trigger('change');
      $('#company_bank_account_from').val('');
    }
    $('#bank_to_wrap').toggle(showTo);
    $('#company_bank_to').prop('required', showTo);
    if (!showTo && !isInitial) {
      $('#company_bank_to').val('').trigger('change');
      $('#company_bank_account_to').val('');
    }
    updateAvailableHint();
  }

  $('#conversion_direction, #method_from').on('change', updateAvailableHint);
  $('#method_from, #method_to').on('change', function () { toggleBanks(false); });
  $('#company_bank_from').on('change', function () {
    $('#company_bank_account_from').val($(this).find(':selected').data('account-no') || '');
  });
  $('#company_bank_to').on('change', function () {
    $('#company_bank_account_to').val($(this).find(':selected').data('account-no') || '');
  });

  toggleBanks(true);
});
</script>
