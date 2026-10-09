<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-2 my-0">

        <?php echo form_open('inventory/manual_payment/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <input type="hidden" name="company_id" value="<?php echo $this->session->userdata('company_id'); ?>">

        <!-- Row 1: Invoice/Receipt No, Payment Date, Payment Type -->
        <div class="row mb-1">
          <div class="col-12 col-md-4">
            <div class="form-group">
              <label><?php echo get_phrase('invoice_no'); ?><span class="required">*</span></label>
              <input type="text" name="invoice_no" class="form-control" placeholder="Receipt / Invoice No" required>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d');?>" id="date_picker" required>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="form-group">
              <label>Payment Type <span class="required">*</span></label>
              <select class="form-control select2" name="payment_type" id="payment_type" required>
                <option value="official">Official</option>
                <option value="unofficial">Unofficial</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Row 2: Payment Method, Bank Account, Amount -->
        <div class="row mb-1">
          <div class="col-12 col-md-4">
            <div class="form-group">
              <label>Payment Method <span class="required">*</span></label>
              <select class="form-control select2" name="payment_method" id="payment_method" required>
                <option value="">Select</option>
                <option value="cash">Cash</option>
                <option value="cheque">Cheque</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-4" id="bank_account_wrap" style="display:none;">
            <div class="form-group">
              <label>Bank Account <span class="bank_required" style="display:none;">*</span></label>
              <select class="form-control select2" name="company_bank" id="company_bank">
                <option value="">Select</option>
                <?php foreach ($bank_accounts as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>" data-account-no="<?php echo htmlspecialchars($value['account_no']);?>">
                    <?php echo $value['bank_name'].' ('.$value['account_no'].')';?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="company_bank_account" id="company_bank_account" value="">
            </div>
          </div>

          <div class="col-12 col-md-8" id="amount_wrap">
            <div class="form-group">
              <label>Amount (in INR) <span class="required">*</span></label>
              <input type="number" name="amount_rs" id="amount_rs" class="form-control" placeholder="0.00" min="0.01" step="0.01" required>
            </div>
          </div>
        </div>

        <!-- Row 3: Narration -->
        <div class="row mb-2">
          <div class="col-12">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration / Remarks" name="narration"></textarea>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="row">
          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
          </div>
        </div>

        <?php echo form_close(); ?>

      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    function toggleBankAccount() {
      const paymentMethod = $('#payment_method').val();
      const showBank = (paymentMethod === 'cheque');

      $('#bank_account_wrap').toggle(showBank);
      $('#company_bank').prop('required', showBank);
      $('.bank_required').toggle(showBank);

      if (showBank) {
        $('#amount_wrap').removeClass('col-md-8').addClass('col-md-4');
        updateAccountNo();
      } else {
        $('#company_bank').val('').trigger('change');
        $('#company_bank_account').val('');
        $('#amount_wrap').removeClass('col-md-4').addClass('col-md-8');
      }
    }

    function updateAccountNo() {
      const accountNo = $('#company_bank').find(':selected').data('account-no') || '';
      $('#company_bank_account').val(accountNo);
    }

    $('#payment_method').on('change', toggleBankAccount);
    $('#company_bank').on('change', updateAccountNo);
    toggleBankAccount();
  });
</script>
