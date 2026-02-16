<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">

<?php
  // controller sends: $data in $page_data['data']
  $row = isset($data) && is_array($data) ? $data : array();

  $supplier_id   = isset($row['supplier_id']) ? $row['supplier_id'] : '';
  $invoice_no    = isset($row['invoice_no']) ? $row['invoice_no'] : '';
  $batch_no      = isset($row['batch_no']) ? $row['batch_no'] : '';

  $amount_dollar = isset($row['amount_dollar']) ? $row['amount_dollar'] : 0;
  $amount_rs     = isset($row['amount_rs']) ? $row['amount_rs'] : 0;
  $amount_rmb    = isset($row['amount_rmb']) ? $row['amount_rmb'] : 0;

  $payment_type  = isset($row['payment_type']) ? $row['payment_type'] : '';
  $bank_account  = isset($row['bank_account']) ? $row['bank_account'] : '';

  $payment_date  = !empty($row['payment_date']) ? $row['payment_date'] : date('Y-m-d');
  $narration     = isset($row['narration']) ? $row['narration'] : '';
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/payments/edit_post/' . $id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Supplier <span class="required">*</span></label>
              <select class="form-control select2" name="supplier_id" id="supplier_id" required>
                <option value="">Select</option>
                <?php foreach ($supplier_list as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>"
                    <?php echo ((string)$supplier_id === (string)$value['id']) ? 'selected' : ''; ?>>
                    <?php echo $value['name'];?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label><?php echo get_phrase('invoice_no'); ?><span class="required">*</span></label>
              <input type="text" name="invoice_no" class="form-control" required
                     value="<?php echo html_escape($invoice_no); ?>">
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Batch No <span class="required">*</span></label>
              <select class="form-control select2" name="batch_no" id="batch_no" required>
                <option value="">Select</option>
                <?php foreach ($po as $key => $value): ?>
                  <?php $vno = $value['voucher_no']; ?>
                  <option value="<?php echo html_escape($vno);?>"
                    <?php echo ((string)$batch_no === (string)$vno) ? 'selected' : ''; ?>>
                    <?php echo html_escape($vno);?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount (in dollar)</label>
              <input type="number" name="amount_dollar" class="form-control"
                     min="0" step="0.01"
                     value="<?php echo html_escape($amount_dollar); ?>">
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount (in INR)</label>
              <input type="number" name="amount_rs" class="form-control"
                     min="0" step="0.01"
                     value="<?php echo html_escape($amount_rs); ?>">
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Amount (in RMB)</label>
              <input type="number" name="amount_rmb" class="form-control"
                     min="0" step="0.01"
                     value="<?php echo html_escape($amount_rmb); ?>">
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Payment type <span class="required">*</span></label>
              <select class="form-control" name="payment_type" id="payment_type" required>
                <option value="">Select</option>
                <option value="official"   <?php echo ($payment_type === 'official') ? 'selected' : ''; ?>>Official</option>
                <option value="unofficial" <?php echo ($payment_type === 'unofficial') ? 'selected' : ''; ?>>Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1" id="bank_account_wrap" style="display:none;">
            <div class="form-group">
              <label>Bank Account <span class="bank_required" style="display:none;">*</span></label>
              <select class="form-control" name="bank_account" id="bank_account">
                <option value="">Select</option>
                <?php foreach ($bank_accounts as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>"
                    <?php echo ((string)$bank_account === (string)$value['id']) ? 'selected' : ''; ?>>
                    <?php echo $value['bank_name'].' ('.$value['account_no'].')';?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" id="date_picker" required
                     value="<?php echo html_escape($payment_date); ?>">
            </div>
          </div>

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration"><?php
                echo html_escape($narration);
              ?></textarea>
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

      // for edit: do NOT reset bank if it already has a value
      if (!isOfficial) {
        $('#bank_account').val('');
      }
    }

    $('#payment_type').on('change', toggleBankAccount);

    // run once on page load
    toggleBankAccount();
  });
</script>
