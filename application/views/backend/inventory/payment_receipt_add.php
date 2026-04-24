<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/payment_receipt/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Customer <span class="required">*</span></label>
              <select class="form-control select2" name="customer_id" id="customer_id" required>
                <option value="">Select</option>
                <?php foreach ($customer_list as $key => $value): ?>
                  <option value="<?php echo $value['id'];?>"><?php echo $value['owner_name'];?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-3 mb-1">
            <div class="form-group">
              <label class="control-label">Payment Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d');?>" id="date_picker" required>
            </div>
          </div>

          <div class="col-md-3 mb-1">
            <div class="form-group">
              <label><?php echo get_phrase('invoice_no'); ?><span class="required">*</span></label>
              <input type="text" name="invoice_no" class="form-control " required>
            </div>
          </div>

          <div class="col-md-3 mb-1">
            <div class="form-group">
              <label>Amount (in INR)</label>
              <input type="number" name="amount_rs" id="amount_rs" class="form-control" value="0" min="0" step="0.01">
            </div>
          </div>
          
          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Payment type <span class="required">*</span></label>
              <select class="form-control select2" name="payment_type" id="payment_type" required>
                <option value="official">Official</option>
                <option value="unofficial">Unofficial</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 mb-1">
            <div class="form-group">
              <label>Payment Method <span class="required">*</span></label>
              <select class="form-control select2" name="payment_method" id="payment_method" required>
                <option value="">Select</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank & Cheque</option>
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

          <div class="col-md-12 mb-2">
            <div class="form-group">
              <label class="control-label">Narration</label>
              <textarea class="form-control" rows="2" placeholder="Narration" name="narration" ></textarea>
            </div>
          </div>

          <div class="col-md-12" id="customer_credits_container">
            <!-- Dynamic Customer Credits Table will load here -->
          </div>

          <div class="col-md-12" id="sales_orders_container">
            <!-- Dynamic Sales Orders Table will load here -->
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
      const paymentMethod = $('#payment_method').val();
      const showBank = (paymentMethod !== '' && paymentMethod !== 'cash');

      $('#bank_account_wrap').toggle(showBank);
      $('#bank_account').prop('required', showBank);

      $('.bank_required').toggle(showBank);

      if (!showBank) {
        $('#bank_account').val(''); // reset when hidden
      }
    }

    $('#payment_method').on('change', toggleBankAccount);

    // run once on page load (for edit pages too)
    toggleBankAccount();

    // Dynamic Sales Order Loading
    function loadCustomerSalesOrders() {
      const customerId = $('#customer_id').val();
      const paymentType = $('#payment_type').val();
      const $container = $('#sales_orders_container');

      if (!customerId || !paymentType) {
        $container.html('');
        return;
      }

      $container.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-1">Loading Outstanding Invoices...</p></div>');

      $.ajax({
        url: '<?= base_url() ?>inventory/get_customer_sales_orders',
        type: 'POST',
        data: {
          customer_id: customerId,
          payment_type: paymentType
        },
        success: function(html) {
          $container.html(html);
        },
        error: function() {
          $container.html('<div class="alert alert-danger">Error loading sales orders. Please try again.</div>');
        }
      });
    }

    $('#customer_id, #payment_type').on('change', loadCustomerSalesOrders);
    $('#customer_id').on('change', loadCustomerCredits);

    // If customer is already selected (e.g. on page load if applicable)
    if ($('#customer_id').val()) {
      if ($('#payment_type').val()) {
        loadCustomerSalesOrders();
      }
      loadCustomerCredits();
    }

    // Dynamic Customer Credit Loading
    function loadCustomerCredits() {
      const customerId = $('#customer_id').val();
      const $container = $('#customer_credits_container');

      if (!customerId) {
        $container.html('');
        return;
      }

      $container.html('<div class="text-center py-5"><div class="spinner-border text-success" role="status"></div><p class="mt-1 text-success">Loading Customer Credits...</p></div>');

      $.ajax({
        url: '<?= base_url() ?>inventory/get_customer_credits',
        type: 'POST',
        data: {
          customer_id: customerId
        },
        success: function(html) {
          $container.html(html);
        },
        error: function() {
          $container.html('<div class="alert alert-danger">Error loading customer credits.</div>');
        }
      });
    }

    // Main Amount Sync
    $('#amount_rs').on('input', function() {
      distributeAmountToSelected();
      calculateTotals();
    });

    function distributeAmountToSelected() {
      let mainAmount = parseFloat($('#amount_rs').val()) || 0;
      let totalCreditApplied = 0;
      $('.credit_checkbox:checked').each(function() {
        totalCreditApplied += parseFloat($(this).closest('tr').find('.apply_credit_amount').val()) || 0;
      });

      let totalTender = mainAmount + totalCreditApplied;
      let remaining = totalTender;

      $('.order_checkbox:checked').each(function() {
        const $row = $(this).closest('tr');
        const $input = $row.find('.apply_amount');
        const pending = parseFloat($input.data('pending')) || 0;

        const amountToApply = Math.min(pending, remaining);
        $input.val(amountToApply.toFixed(2));
        remaining -= amountToApply;
        
        updateRowRemaining($row);
      });
    }

    // Outstanding Table Interactions
    $(document).on('change', '#select_all_checkbox', function() {
      const isChecked = $(this).is(':checked');
      $('.order_checkbox').each(function() {
        if ($(this).is(':checked') !== isChecked) {
          $(this).prop('checked', isChecked).trigger('change');
        }
      });
      distributeAmountToSelected();
      calculateTotals();
    });

    $(document).on('click', '#select_all_btn', function() {
      $('#select_all_checkbox').prop('checked', true).trigger('change');
    });

    $(document).on('click', '#clear_all_btn', function() {
      $('#select_all_checkbox').prop('checked', false).trigger('change');
    });

    $(document).on('change', '.order_checkbox', function() {
      const $row = $(this).closest('tr');
      const $input = $row.find('.apply_amount');
      const pending = parseFloat($input.data('pending')) || 0;

      if ($(this).is(':checked')) {
        let totalAllocatedOthers = 0;
        $('.order_checkbox:checked').not(this).each(function() {
          totalAllocatedOthers += parseFloat($(this).closest('tr').find('.apply_amount').val()) || 0;
        });

        const mainAmount = parseFloat($('#amount_rs').val()) || 0;
        let totalCreditApplied = 0;
        $('.credit_checkbox:checked').each(function() {
          totalCreditApplied += parseFloat($(this).closest('tr').find('.apply_credit_amount').val()) || 0;
        });

        const totalTender = mainAmount + totalCreditApplied;
        const available = Math.max(0, totalTender - totalAllocatedOthers);
        const amountToApply = Math.min(pending, available);

        $input.val(amountToApply.toFixed(2)).prop('readonly', false);
        $row.addClass('table-warning');
      } else {
        $input.val('0.00').prop('readonly', true);
        $row.removeClass('table-warning');
      }
      
      updateRowRemaining($row);
      calculateTotals();
    });

    $(document).on('input', '.apply_amount', function() {
      const $row = $(this).closest('tr');
      let val = parseFloat($(this).val()) || 0;
      const pending = parseFloat($(this).data('pending')) || 0;

      if (val > pending) {
        alert('Applied amount cannot be greater than the pending amount (₹' + pending.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ')');
        $(this).val(pending.toFixed(2));
        val = pending;
      }

      // Logic: if Allocated (Inv) exceeds Total Tender, increase Total Tender to match
      let totalAllocated = 0;
      $('.order_checkbox:checked').each(function() {
        totalAllocated += parseFloat($(this).closest('tr').find('.apply_amount').val()) || 0;
      });

      let totalCreditApplied = 0;
      $('.credit_checkbox:checked').each(function() {
        totalCreditApplied += parseFloat($(this).closest('tr').find('.apply_credit_amount').val()) || 0;
      });

      let currentTotalTender = (parseFloat($('#amount_rs').val()) || 0) + totalCreditApplied;
      if (totalAllocated > currentTotalTender) {
        // Increase main amount input to cover the gap
        const neededFromMain = totalAllocated - totalCreditApplied;
        $('#amount_rs').val(neededFromMain.toFixed(2));
      }

      updateRowRemaining($row);
      calculateTotals();
    });

    function updateRowRemaining($row) {
      const $input = $row.find('.apply_amount');
      const val = parseFloat($input.val()) || 0;
      const pending = parseFloat($input.data('pending')) || 0;
      const remaining = Math.max(0, pending - val);
      
      // Update visual text
      $row.find('.remaining_amount').text('₹' + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      
      // Update hidden input for pending (to store the remaining amount for server processing)
      const orderId = $row.find('.order_checkbox').val();
      $row.find('input[name="order_pending[' + orderId + ']"]').val(remaining.toFixed(2));
    }

    function calculateTotals() {
      let totalAllocated = 0;
      let totalOutstandingSelected = 0;
      let selectedCount = 0;
      let totalInvoiceRemaining = 0;

      $('.order_checkbox:checked').each(function() {
        const $row = $(this).closest('tr');
        const applied = parseFloat($row.find('.apply_amount').val()) || 0;
        const pending = parseFloat($row.find('.apply_amount').data('pending')) || 0;
        
        totalAllocated += applied;
        totalOutstandingSelected += pending;
        totalInvoiceRemaining += Math.max(0, pending - applied);
        selectedCount++;
      });

      let totalCreditApplied = 0;
      let totalCreditRemaining = 0;
      $('.credit_checkbox:checked').each(function() {
        const $row = $(this).closest('tr');
        const applied = parseFloat($row.find('.apply_credit_amount').val()) || 0;
        const pending = parseFloat($row.find('.apply_credit_amount').data('pending')) || 0;
        
        totalCreditApplied += applied;
        totalCreditRemaining += Math.max(0, pending - applied);
      });

      const mainAmount = parseFloat($('#amount_rs').val()) || 0;
      const totalTender = mainAmount + totalCreditApplied;
      const onAccount = Math.max(0, totalTender - totalAllocated);
      
      // Adjustments = Sum of selected APPLY/PAY from credits
      // Balance After = Sum of invoice remaining + credit remaining
      const displayAdjustments = parseFloat(totalCreditApplied.toFixed(2));
      const displayBalanceAfter = parseFloat((totalInvoiceRemaining + totalCreditRemaining).toFixed(2));
      const displayTotalAllocated = parseFloat(totalAllocated.toFixed(2));
      const displayTotalTender = parseFloat(totalTender.toFixed(2));
      const displayOnAccount = parseFloat(onAccount.toFixed(2));
      const displayTotalOutstanding = parseFloat(totalOutstandingSelected.toFixed(2));

      // Update Summary Fields
      $('#summary_selected_count').text(selectedCount);
      $('#summary_balance_after').text('₹' + displayBalanceAfter.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      $('#summary_total_outstanding').text('₹' + displayTotalOutstanding.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      $('#summary_allocated_inv').text('₹' + displayTotalAllocated.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      $('#summary_adjustments').text('₹' + displayAdjustments.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      $('#summary_total_tender').text('₹' + displayTotalTender.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      $('#summary_on_account').text('₹' + displayOnAccount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      
      // Update Hidden Inputs
      $('#hidden_selected_count').val(selectedCount);
      $('#hidden_balance_after').val(displayBalanceAfter);
      $('#hidden_total_outstanding').val(displayTotalOutstanding);
      $('#hidden_allocated_inv').val(displayTotalAllocated);
      $('#hidden_adjustments').val(displayAdjustments);
      $('#hidden_total_tender').val(displayTotalTender);
      $('#hidden_on_account').val(displayOnAccount);

      // Update Net Allocation Total
      $('#net_allocation_total').text('₹' + displayTotalAllocated.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // Credits Table Interactions
    $(document).on('change', '#select_all_credits', function() {
      const isChecked = $(this).is(':checked');
      $('.credit_checkbox').each(function() {
        if ($(this).is(':checked') !== isChecked) {
          $(this).prop('checked', isChecked).trigger('change');
        }
      });
      distributeAmountToSelected();
      calculateTotals();
    });

    $(document).on('click', '#select_all_credits_btn', function() {
      $('#select_all_credits').prop('checked', true).trigger('change');
    });

    $(document).on('click', '#clear_all_credits_btn', function() {
      $('#select_all_credits').prop('checked', false).trigger('change');
    });

    $(document).on('change', '.credit_checkbox', function() {
      const $row = $(this).closest('tr');
      const $input = $row.find('.apply_credit_amount');
      const pending = parseFloat($input.data('pending')) || 0;

      if ($(this).is(':checked')) {
        $input.val(pending.toFixed(2)).prop('readonly', false);
        $row.addClass('table-success');
      } else {
        $input.val('0.00').prop('readonly', true);
        $row.removeClass('table-success');
      }
      distributeAmountToSelected();
      calculateTotals();
    });

    $(document).on('input', '.apply_credit_amount', function() {
      const $row = $(this).closest('tr');
      const val = parseFloat($(this).val()) || 0;
      const pending = parseFloat($(this).data('pending')) || 0;

      if (val > pending) {
        alert('Applied credit cannot be greater than the pending amount (₹' + pending.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ')');
        $(this).val(pending.toFixed(2));
      }

      const remaining = Math.max(0, pending - parseFloat($(this).val()));
      $row.find('.remaining_credit_amount').text('₹' + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      distributeAmountToSelected();
      calculateTotals();
    });

  });

</script>