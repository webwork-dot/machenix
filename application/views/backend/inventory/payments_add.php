<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/payments/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">

         <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Supplier <span class="required">*</span></label>
              <select class="form-control select2" name="supplier_id" id="supplier_id" required>
                <option value="">Select</option>
                <?php foreach ($supplier_list as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label><?php echo get_phrase('invoice_no'); ?><span class="required">*</span></label>
              <input type="text" name="invoice_no" class="form-control " required>
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Batch No <span class="required">*</span></label>
              <select class="form-control select2" name="batch_no" id="batch_no" required>
                <option value="">Select</option>
                <?php foreach ($po as $key => $value): ?>
                  <option value="<?php echo $value['voucher_no'];?>"><?php echo $value['voucher_no'];?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount (in dollar)</label>
              <input type="number" name="amount_dollar" class="form-control" value="0" min="0" step="0.01">
            </div>
          </div>
          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount (in INR)</label>
              <input type="number" name="amount_rs" class="form-control" value="0" min="0" step="0.01">
            </div>
          </div>
          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount (in RMB)</label>
              <input type="number" name="amount_rmb" class="form-control" value="0" min="0" step="0.01">
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Payment type <span class="required">*</span></label>
              <select class="form-control select2" name="payment_type" id="payment_type" required>
                <option value="">Select</option>
                <option value="official">Official</option>
                <option value="unofficial">Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1" id="bank_account_wrap" style="display:none;">
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


          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d');?>" id="date_picker" required>
            </div>
          </div>

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration" ></textarea>
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

    // Dynamic Batch Loading
    function loadSupplierBatches() {
      const supplierId = $('#supplier_id').val();
      const $batchSelect = $('#batch_no');

      if (!supplierId) {
        $batchSelect.html('<option value="">Select Supplier First</option>').trigger('change');
        return;
      }

      // Show loading state
      $batchSelect.html('<option value="">Loading...</option>').trigger('change');

      $.ajax({
        url: '<?= base_url() ?>inventory/get_supplier_batches/' + supplierId,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          let options = '<option value="">Select Batch/Voucher</option>';
          if (data && data.length > 0) {
            data.forEach(function(batch) {
              options += `<option value="${batch.voucher_no}">${batch.voucher_no}</option>`;
            });
          } else {
            options = '<option value="">No Purchase In batches found</option>';
          }
          $batchSelect.html(options).trigger('change');
        },
        error: function() {
          $batchSelect.html('<option value="">Error loading batches</option>').trigger('change');
        }
      });
    }

    $('#supplier_id').on('change', loadSupplierBatches);

    // If supplier is pre-selected
    if ($('#supplier_id').val()) {
      loadSupplierBatches();
    }
  });

  $(document).ready(function () {
    $(document).on('focus', '#supplier_id + .select2 .select2-selection', function () {
        $('#supplier_id').select2('open');
    });
    $(document).on('focus', '#batch_no + .select2 .select2-selection', function () {
        $('#batch_no').select2('open');
    });
    $(document).on('focus', '#payment_type + .select2 .select2-selection', function () {
        $('#payment_type').select2('open');
    });
  });

</script>