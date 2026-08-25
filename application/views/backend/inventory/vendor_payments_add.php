<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<style>
  .currency-input-group {
    display: flex;
    align-items: stretch;
    width: 100%;
  }
  .currency-input-group .input-group-text {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    border-right: 0 !important;
    background-color: #f3f4f6;
    color: #374151;
    font-weight: 600;
    padding: 0.375rem 0.75rem;
    display: flex;
    align-items: center;
  }
  .currency-input-group .form-control {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-2 my-0">

        <?php echo form_open('inventory/vendor_payments/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        
        <!-- Row 1: Vendor & Invoice No -->
        <div class="row mb-1">
          <div class="col-md-6">
            <div class="form-group">
              <label>Vendor <span class="required">*</span></label>
              <select class="form-control select2" name="vendor_id" id="vendor_id" required>
                <option value="">Select</option>
                <?php foreach ($vendor_list as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label><?php echo get_phrase('invoice_no'); ?><span class="required">*</span></label>
              <input type="text" name="invoice_no" class="form-control" required>
            </div>
          </div>
        </div>

        <!-- Row 2: Payment Type, Bank Account, Payment Date -->
        <div class="row mb-1">
          <div class="col-md-4" id="pay_type_wrap">
            <div class="form-group">
              <label>Payment type <span class="required">*</span></label>
              <select class="form-control select2" name="payment_type" id="payment_type" required>
                <option value="">Select</option>
                <option value="official">Official</option>
                <option value="unofficial">Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4" id="bank_account_wrap" style="display:none;">
            <div class="form-group">
              <label>Bank Account <span class="bank_required" style="display:none;">*</span></label>
              <select class="form-control" name="bank_account" id="bank_account">
                <option value="">Select</option>
                <?php foreach ($bank_accounts as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>">
                    <?php echo $value['bank_name'].' ('.$value['account_no'].')';?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4" id="pay_date_wrap">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d');?>" id="date_picker" required>
            </div>
          </div>
        </div>

        <!-- Row 3: Amount Fields (USD, RMB, INR) -->
        <div class="row mb-1">
          <div class="col-md-4" id="usd_wrap">
            <div class="form-group">
              <label>Amount (USD)</label>
              <div class="input-group currency-input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="usd" id="usd" class="form-control" value="0.00" step="0.00001">
              </div>
            </div>
          </div>

          <div class="col-md-4" id="rmb_wrap">
            <div class="form-group">
              <label>Amount (RMB)</label>
              <div class="input-group currency-input-group">
                <span class="input-group-text">¥</span>
                <input type="number" name="rmb" id="rmb" class="form-control" value="0.00" step="0.00001">
              </div>
            </div>
          </div>

          <div class="col-md-4" id="inr_wrap">
            <div class="form-group">
              <label>Amount (INR)</label>
              <div class="input-group currency-input-group">
                <span class="input-group-text">₹</span>
                <input type="number" name="inr" id="inr" class="form-control" value="0.00" step="0.00001">
              </div>
            </div>
          </div>
        </div>

        <!-- Row 4: Narration -->
        <div class="row mb-1">
          <div class="col-md-12">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration"></textarea>
            </div>
          </div>
        </div>

        <div class="row">
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
    function togglePaymentTypeFields() {
      const isOfficial = $('#payment_type').val() === 'official';

      $('#bank_account_wrap').toggle(isOfficial);
      $('#bank_account').prop('required', isOfficial);
      $('.bank_required').toggle(isOfficial);

      if (isOfficial) {
        // Official: Bank account visible -> Row 2 has 3 cols (4 each)
        $('#pay_type_wrap').removeClass('col-md-6').addClass('col-md-4');
        $('#pay_date_wrap').removeClass('col-md-6').addClass('col-md-4');

        // Hide RMB, USD and INR expand to 6 each
        $('#rmb_wrap').hide();
        $('#rmb').val('0.00');
        $('#usd_wrap').removeClass('col-md-4').addClass('col-md-6');
        $('#inr_wrap').removeClass('col-md-4').addClass('col-md-6');
      } else {
        // Unofficial: Bank account hidden -> Row 2 has 2 cols (6 each)
        $('#bank_account').val('');
        $('#pay_type_wrap').removeClass('col-md-4').addClass('col-md-6');
        $('#pay_date_wrap').removeClass('col-md-4').addClass('col-md-6');

        // Show RMB, all 3 amounts take 4 each
        $('#rmb_wrap').show();
        $('#usd_wrap').removeClass('col-md-6').addClass('col-md-4');
        $('#inr_wrap').removeClass('col-md-6').addClass('col-md-4');
      }
    }

    $('#payment_type').on('change', togglePaymentTypeFields);

    // run once on page load
    togglePaymentTypeFields();
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
