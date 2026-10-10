<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-2 my-0">

        <?php echo form_open('inventory/payment_receipt/edit_post/' . $id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <input type="hidden" name="company_id" value="<?php echo $this->session->userdata('company_id'); ?>">

        <div class="row mb-1">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label>Customer <span class="required">*</span></label>
              <select class="form-control select2" name="customer_id" id="customer_id" required>
                <option value="">Select</option>
                <?php foreach ($customer_list as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>" <?php if(($data['customer_id'] ?? '') == $value['id']) echo 'selected'; ?>>
                    <?php echo $value['company_name'];?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label><?php echo get_phrase('invoice_no'); ?><span class="required">*</span></label>
              <input type="text" name="invoice_no" class="form-control" placeholder="Receipt / Invoice No" value="<?php echo htmlspecialchars($data['inv_no'] ?? ''); ?>" required>
            </div>
          </div>
        </div>

        <div class="row mb-1">
          <div class="col-12 col-md-3">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" value="<?php echo htmlspecialchars($data['date'] ?? date('Y-m-d')); ?>" id="date_picker" required>
            </div>
          </div>

          <div class="col-12 col-md-3">
            <div class="form-group">
              <label>Payment Mode <span class="required">*</span></label>
              <select class="form-control select2" name="payment_mode" id="payment_mode" required>
                <option value="payment" <?php if(($data['payment_mode'] ?? 'payment') == 'payment') echo 'selected'; ?>>Payment</option>
                <option value="return" <?php if(($data['payment_mode'] ?? '') == 'return') echo 'selected'; ?>>Return</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-3">
            <div class="form-group">
              <label>Payment Type <span class="required">*</span></label>
              <select class="form-control select2" name="payment_type" id="payment_type" required>
                <option value="official" <?php if(($data['payment_type'] ?? '') == 'official') echo 'selected'; ?>>Official</option>
                <option value="unofficial" <?php if(($data['payment_type'] ?? '') == 'unofficial') echo 'selected'; ?>>Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-3">
            <div class="form-group">
              <label>Payment Method <span class="required">*</span></label>
              <select class="form-control select2" name="payment_method" id="payment_method" required>
                <option value="">Select</option>
                <option value="cash" <?php if(($data['payment_method'] ?? '') == 'cash') echo 'selected'; ?>>Cash</option>
                <option value="cheque" <?php if(($data['payment_method'] ?? '') == 'cheque') echo 'selected'; ?>>Cheque</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row mb-1">
          <div class="col-12 col-md-6" id="bank_account_wrap" style="<?php echo (($data['payment_method'] ?? '') == 'cheque') ? '' : 'display:none;'; ?>">
            <div class="form-group">
              <label>Bank Account <span class="bank_required" style="<?php echo (($data['payment_method'] ?? '') == 'cheque') ? '' : 'display:none;'; ?>">*</span></label>
              <select class="form-control select2" name="company_bank" id="company_bank">
                <option value="">Select</option>
                <?php foreach ($bank_accounts as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>" data-account-no="<?php echo htmlspecialchars($value['account_no']);?>" <?php if(($data['company_bank'] ?? '') == $value['id']) echo 'selected'; ?>>
                    <?php echo $value['bank_name'].' ('.$value['account_no'].')';?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="company_bank_account" id="company_bank_account" value="<?php echo htmlspecialchars($data['company_bank_account'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-12 col-md-<?php echo (($data['payment_method'] ?? '') == 'cheque') ? '6' : '12'; ?>" id="amount_wrap">
            <div class="form-group">
              <label>Amount (in INR) <span class="required">*</span></label>
              <input type="number" name="amount_rs" id="amount_rs" class="form-control" placeholder="0.00" min="0.01" step="0.01" value="<?php echo htmlspecialchars($data['amount'] ?? ''); ?>" required>
            </div>
          </div>
        </div>

        <div class="row mb-2">
          <div class="col-12">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration / Remarks" name="narration"><?php echo htmlspecialchars($data['narration'] ?? ''); ?></textarea>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('update'); ?></button>
          </div>
        </div>

        <?php echo form_close(); ?>

      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    function toggleBankAccount(isInitial) {
      const paymentMethod = $('#payment_method').val();
      const showBank = (paymentMethod === 'cheque');

      $('#bank_account_wrap').toggle(showBank);
      $('#company_bank').prop('required', showBank);
      $('.bank_required').toggle(showBank);

      if (showBank) {
        $('#amount_wrap').removeClass('col-md-12').addClass('col-md-6');
        if (!isInitial) {
          updateAccountNo();
        }
      } else {
        if (!isInitial) {
          $('#company_bank').val('').trigger('change');
          $('#company_bank_account').val('');
        }
        $('#amount_wrap').removeClass('col-md-6').addClass('col-md-12');
      }
    }

    function updateAccountNo() {
      const accountNo = $('#company_bank').find(':selected').data('account-no') || '';
      $('#company_bank_account').val(accountNo);
    }

    $('#payment_method').on('change', function () { toggleBankAccount(false); });
    $('#company_bank').on('change', updateAccountNo);
    toggleBankAccount(true);
  });
</script>
