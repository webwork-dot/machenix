<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/vendor_payments/edit_post/' . $id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">

         <div class="col-12 col-sm-6 mb-1">
            <div class="form-group">
              <label>Vendor <span class="required">*</span></label>
              <select class="form-control select2" name="vendor_id" id="vendor_id" required>
                <option value="">Select</option>
                <?php foreach ($vendor_list as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>" <?php if($data['vendor_id'] == $value['id']) echo 'selected'; ?>><?php echo $value['name'];?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-6 mb-1">
            <div class="form-group">
              <label><?php echo get_phrase('invoice_no'); ?><span class="required">*</span></label>
              <input type="text" name="invoice_no" class="form-control" value="<?php echo $data['invoice_no']; ?>" required>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount <span class="required">*</span></label>
              <input type="number" name="amount" class="form-control" value="<?php echo $data['amount']; ?>" min="0" step="0.01" required>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Payment type <span class="required">*</span></label>
              <select class="form-control select2" name="payment_type" id="payment_type" required>
                <option value="">Select</option>
                <option value="official" <?php if($data['payment_type'] == 'official') echo 'selected'; ?>>Official</option>
                <option value="unofficial" <?php if($data['payment_type'] == 'unofficial') echo 'selected'; ?>>Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1" id="bank_account_wrap" style="display:none;">
            <div class="form-group">
              <label>Bank Account <span class="bank_required" style="display:none;">*</span></label>
              <select class="form-control" name="bank_account" id="bank_account">
                <option value="">Select</option>
                <?php foreach ($bank_accounts as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>" <?php if($data['bank_account'] == $value['id']) echo 'selected'; ?>>
                    <?php echo $value['bank_name'].' ('.$value['account_no'].')';?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>


          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" value="<?php echo $data['payment_date']; ?>" id="date_picker" required>
            </div>
          </div>

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration" ><?php echo $data['narration']; ?></textarea>
            </div>
          </div>

          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
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
      const isOfficial = $('#payment_type').val() === 'official';

      $('#bank_account_wrap').toggle(isOfficial);
      $('#bank_account').prop('required', isOfficial);

      $('.bank_required').toggle(isOfficial);

      if (!isOfficial) {
        $('#bank_account').val(''); // reset when hidden
      }
    }

    $('#payment_type').on('change', toggleBankAccount);

    // run once on page load (for edit pages too)
    toggleBankAccount();
  });

  $(document).ready(function () {
    $(document).on('focus', '#vendor_id + .select2 .select2-selection', function () {
        $('#vendor_id').select2('open');
    });
    $(document).on('focus', '#payment_type + .select2 .select2-selection', function () {
        $('#payment_type').select2('open');
    });
  });

</script>
