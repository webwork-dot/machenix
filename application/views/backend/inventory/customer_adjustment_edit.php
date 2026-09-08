<?php
$row = $data ?? [];
$id = $id ?? 0;
$details = $details ?? [];
if (empty($details) && !empty($row)) {
  $details = [$row];
}
?>

<style>
  .adj-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    background: #ffffff;
  }
  .adj-card .card-header {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 16px 20px;
  }
  .adj-table {
    border: 1px solid #e2e8f0;
    margin-bottom: 0;
  }
  .adj-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid #e2e8f0;
    padding: 8px 10px;
    vertical-align: middle;
  }
  .adj-table tbody td {
    padding: 6px 8px;
    vertical-align: middle;
    border: 1px solid #f1f5f9;
  }
  .adj-table tfoot td {
    background: #f8fafc;
    border-top: 2px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    border-left: 1px solid #f1f5f9;
    border-right: 1px solid #f1f5f9;
    padding: 10px 8px;
    vertical-align: middle;
    font-weight: 700;
    font-size: 12px;
  }
  .mono-amount {
    font-family: "DM Mono", Menlo, Consolas, monospace;
    font-size: 12.5px;
  }
  .table-input {
    height: 35px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    font-size: 12.5px;
    padding: 4px 8px;
    transition: all 0.15s ease;
  }
  .table-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }
  .table-input-disabled {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #94a3b8 !important;
    cursor: not-allowed;
  }
  .btn-remove-row {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    border: 1px solid #fecaca;
    background: #fff5f5;
    color: #ef4444;
    transition: all 0.15s ease;
  }
  .btn-remove-row:hover {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
  }
  .select2-container--default .select2-selection--single {
    height: 35px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    position: relative;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 33px !important;
    font-size: 12.5px;
    padding-left: 10px;
    padding-right: 25px;
    color: #1e293b;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 33px !important;
    position: absolute;
    top: 1px;
    right: 6px;
    width: 20px;
  }
  .ledger-badge-due {
    display: inline-flex;
    align-items: center;
    font-size: 10.5px;
    font-weight: 600;
    color: #dc2626;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 4px;
    padding: 1px 5px;
    line-height: 1.3;
  }
  .ledger-badge-advance {
    display: inline-flex;
    align-items: center;
    font-size: 10.5px;
    font-weight: 600;
    color: #16a34a;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 4px;
    padding: 1px 5px;
    line-height: 1.3;
  }
  .ledger-badge-zero {
    display: inline-flex;
    align-items: center;
    font-size: 10.5px;
    font-weight: 500;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 1px 5px;
    line-height: 1.3;
  }
  .ledger-info-box {
    min-height: 16px;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card adj-card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">Edit Customer Adjustment #<?= $id ?></h5>
      </div>
      <div class="card-body p-3">
        <form action="<?= base_url('inventory/customer-adjustment/edit_post/' . $id) ?>" method="post" id="adjustmentForm">
          
          <!-- Header Date & Type & Amount Type -->
          <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
              <label for="date" class="form-label fw-semibold fs-12 text-secondary mb-1">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" id="date" class="form-control table-input" value="<?= html_escape($row['date'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="col-md-3 col-sm-6">
              <label for="type" class="form-label fw-semibold fs-12 text-secondary mb-1">Type <span class="text-danger">*</span></label>
              <select name="type" id="type" class="form-select table-input" required>
                <option value="unofficial" <?= (isset($row['type']) && $row['type'] == 'unofficial') ? 'selected' : '' ?>>Unofficial</option>
                <option value="official" <?= (isset($row['type']) && $row['type'] == 'official') ? 'selected' : '' ?>>Official</option>
              </select>
            </div>
            <div class="col-md-3 col-sm-6">
              <label for="amt_type_id" class="form-label fw-semibold fs-12 text-secondary mb-1">Amount Type</label>
              <select name="amt_type_id" id="amt_type_id" class="form-select table-input">
                <option value="">-- Select Amount Type --</option>
                <?php if (!empty($other_charges)): ?>
                  <?php foreach ($other_charges as $oc): ?>
                    <option value="<?= $oc['id'] ?>" <?= (isset($row['amt_type_id']) && $row['amt_type_id'] == $oc['id']) ? 'selected' : '' ?>><?= html_escape($oc['name']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <!-- Dynamic Adjustment Table -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold text-dark fs-13">Adjustment Entries</div>
            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1 px-3 py-1" id="btnAddRow" style="border-radius: 6px;">
              <i class="feather icon-plus"></i> Add Row
            </button>
          </div>

          <div class="table-responsive rounded-3 mb-4">
            <table class="table adj-table align-middle" id="adjTable">
              <thead>
                <tr>
                  <th style="width: 40px;" class="text-center">#</th>
                  <th style="min-width: 260px;">Customer <span class="text-danger">*</span></th>
                  <th style="width: 170px;" class="text-end">Debit (₹)</th>
                  <th style="width: 170px;" class="text-end">Credit (₹)</th>
                  <th style="min-width: 180px;">Remark</th>
                  <th style="width: 45px;" class="text-center"></th>
                </tr>
              </thead>
              <tbody id="adjTableBody">
                <?php if (!empty($details)): ?>
                  <?php foreach ($details as $idx => $det): 
                    $selected_cust = $det['customer_id'] ?? 0;
                    $is_debit = (isset($det['amt_type']) && $det['amt_type'] == 'minus');
                    $is_credit = (isset($det['amt_type']) && $det['amt_type'] == 'plus');
                    $amt = (float)($det['inr'] ?? 0);
                    $deb_val = $is_debit && $amt > 0 ? number_format($amt, 2, '.', '') : '';
                    $crd_val = $is_credit && $amt > 0 ? number_format($amt, 2, '.', '') : '';
                  ?>
                    <tr class="adj-row">
                      <td class="text-center row-index fw-semibold text-muted fs-12"><?= $idx + 1 ?></td>
                      <td>
                        <select name="customer_id[]" class="form-select select2-dynamic customer-select" required>
                          <option value="">-- Select Customer --</option>
                          <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $c): 
                              $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
                            ?>
                              <option value="<?= $c['id'] ?>" <?= ($selected_cust == $c['id']) ? 'selected' : '' ?>>
                                <?= html_escape($displayName) ?>
                              </option>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </select>
                        <div class="customer-ledger-info ledger-info-box mt-1 d-none"></div>
                      </td>
                      <td>
                        <input type="number" step="0.01" min="0" name="debit_inr[]" class="form-control table-input debit-inr mono-amount text-end <?= ($is_credit && $crd_val !== '') ? 'table-input-disabled' : '' ?>" placeholder="0.00" value="<?= $deb_val ?>" <?= ($is_credit && $crd_val !== '') ? 'disabled' : '' ?>>
                      </td>
                      <td>
                        <input type="number" step="0.01" min="0" name="credit_inr[]" class="form-control table-input credit-inr mono-amount text-end <?= ($is_debit && $deb_val !== '') ? 'table-input-disabled' : '' ?>" placeholder="0.00" value="<?= $crd_val ?>" <?= ($is_debit && $deb_val !== '') ? 'disabled' : '' ?>>
                      </td>
                      <td>
                        <input type="text" name="remark[]" class="form-control table-input" placeholder="Enter remark..." value="<?= html_escape($det['remark'] ?? '') ?>">
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn-remove-row" title="Delete Row">
                          <i class="feather icon-trash-2"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr class="adj-row">
                    <td class="text-center row-index fw-semibold text-muted fs-12">1</td>
                    <td>
                      <select name="customer_id[]" class="form-select select2-dynamic customer-select" required>
                        <option value="">-- Select Customer --</option>
                        <?php if (!empty($customers)): ?>
                          <?php foreach ($customers as $c): 
                            $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
                          ?>
                            <option value="<?= $c['id'] ?>"><?= html_escape($displayName) ?></option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                      <div class="customer-ledger-info ledger-info-box mt-1 d-none"></div>
                    </td>
                    <td>
                      <input type="number" step="0.01" min="0" name="debit_inr[]" class="form-control table-input debit-inr mono-amount text-end" placeholder="0.00">
                    </td>
                    <td>
                      <input type="number" step="0.01" min="0" name="credit_inr[]" class="form-control table-input credit-inr mono-amount text-end" placeholder="0.00">
                    </td>
                    <td>
                      <input type="text" name="remark[]" class="form-control table-input" placeholder="Enter remark...">
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row" title="Delete Row">
                        <i class="feather icon-trash-2"></i>
                      </button>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2" class="text-end text-secondary fw-semibold">Total:</td>
                  <td class="text-end mono-amount fw-bold" id="totalDebitINR">₹ 0.00</td>
                  <td class="text-end mono-amount fw-bold" id="totalCreditINR">₹ 0.00</td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Bottom Actions -->
          <div class="d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
            <a href="<?= base_url('inventory/customer-adjustment') ?>" class="btn btn-light px-4 py-2" style="border-radius: 6px; font-weight: 500;">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" id="btnSubmit" style="border-radius: 6px; font-weight: 500;">
              <i class="feather icon-check me-1"></i> Update
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<!-- Template for adding dynamic clean rows without select2 cloning conflicts -->
<template id="emptyRowTemplate">
  <tr class="adj-row">
    <td class="text-center row-index fw-semibold text-muted fs-12"></td>
    <td>
      <select name="customer_id[]" class="form-select select2-dynamic customer-select" required>
        <option value="">-- Select Customer --</option>
        <?php if (!empty($customers)): ?>
          <?php foreach ($customers as $c): 
            $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
          ?>
            <option value="<?= $c['id'] ?>"><?= html_escape($displayName) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="customer-ledger-info ledger-info-box mt-1 d-none"></div>
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="debit_inr[]" class="form-control table-input debit-inr mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="credit_inr[]" class="form-control table-input credit-inr mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="text" name="remark[]" class="form-control table-input" placeholder="Enter remark...">
    </td>
    <td class="text-center">
      <button type="button" class="btn-remove-row" title="Delete Row">
        <i class="feather icon-trash-2"></i>
      </button>
    </td>
  </tr>
</template>

<script type="text/javascript">
$(document).ready(function() {

  var currentAdjId = <?= json_encode((int)$id) ?>;
  var customerLedgerCache = {};

  initSelect2();
  calculateTotals();

  // Load ledger info for existing pre-selected customers
  $('.customer-select').each(function() {
    if ($(this).val()) {
      fetchCustomerLedger($(this));
    }
  });

  function initSelect2($context) {
    var $targets = $context ? $context.find('.select2-dynamic') : $('.select2-dynamic');
    $targets.each(function() {
      if (!$(this).hasClass("select2-hidden-accessible")) {
        $(this).select2({
          placeholder: "-- Select Customer --",
          width: '100%'
        });
      }
    });
  }

  function updateRowIndices() {
    $('#adjTableBody tr').each(function(index) {
      $(this).find('.row-index').text(index + 1);
    });
  }

  // Fetch Customer Ledger Summary on Select
  function fetchCustomerLedger($selectElem) {
    var customerId = $selectElem.val();
    var $infoBox = $selectElem.closest('td').find('.customer-ledger-info');

    if (!customerId) {
      $infoBox.empty().addClass('d-none');
      return;
    }

    if (customerLedgerCache[customerId]) {
      renderCustomerLedger($infoBox, customerLedgerCache[customerId]);
      return;
    }

    $infoBox.removeClass('d-none').html('<span class="text-muted fs-11"><i class="feather icon-loader fa-spin"></i> Loading...</span>');

    $.ajax({
      url: '<?= base_url('inventory/get_customer_ledger_summary_ajax') ?>',
      type: 'POST',
      dataType: 'json',
      data: {
        customer_id: customerId,
        current_adj_id: currentAdjId
      },
      success: function(res) {
        if (res.success && res.data) {
          customerLedgerCache[customerId] = res.data;
          renderCustomerLedger($infoBox, res.data);
        } else {
          $infoBox.html('<span class="text-muted fs-11">No ledger data</span>');
        }
      },
      error: function() {
        $infoBox.html('<span class="text-danger fs-11">Failed to load</span>');
      }
    });
  }

  function renderCustomerLedger($infoBox, data) {
    var bal = parseFloat(data.balance) || 0;
    var html = '';
    if (bal > 0.005) {
      html = '<span class="ledger-badge-due"><i class="feather icon-alert-circle me-1"></i>Outstanding: ₹ ' + formatMoney(bal, 2) + '</span>';
    } else if (bal < -0.005) {
      html = '<span class="ledger-badge-advance"><i class="feather icon-check-circle me-1"></i>Advance: ₹ ' + formatMoney(Math.abs(bal), 2) + '</span>';
    } else {
      html = '<span class="ledger-badge-zero">Balance: ₹ 0.00</span>';
    }
    $infoBox.removeClass('d-none').html(html);
  }

  $(document).on('change', '.customer-select', function() {
    fetchCustomerLedger($(this));
  });

  // Mutual exclusion: Debit vs Credit
  $(document).on('input', '.debit-inr', function() {
    var $row = $(this).closest('tr');
    var val = parseFloat($(this).val());
    var $creditInput = $row.find('.credit-inr');

    if (!isNaN(val) && val > 0) {
      $creditInput.val('').prop('disabled', true).addClass('table-input-disabled');
    } else {
      $creditInput.prop('disabled', false).removeClass('table-input-disabled');
    }
    calculateTotals();
  });

  $(document).on('input', '.credit-inr', function() {
    var $row = $(this).closest('tr');
    var val = parseFloat($(this).val());
    var $debitInput = $row.find('.debit-inr');

    if (!isNaN(val) && val > 0) {
      $debitInput.val('').prop('disabled', true).addClass('table-input-disabled');
    } else {
      $debitInput.prop('disabled', false).removeClass('table-input-disabled');
    }
    calculateTotals();
  });

  // Add Row using clean template
  $('#btnAddRow').on('click', function() {
    var html = $('#emptyRowTemplate').html();
    var $newRow = $(html);

    $('#adjTableBody').append($newRow);
    initSelect2($newRow);
    updateRowIndices();
    calculateTotals();
  });

  // Remove Row
  $(document).on('click', '.btn-remove-row', function() {
    if ($('#adjTableBody tr').length > 1) {
      $(this).closest('tr').remove();
      updateRowIndices();
      calculateTotals();
    } else {
      alert('At least one adjustment row is required.');
    }
  });

  // Calculate Totals
  function calculateTotals() {
    var totalDebit = 0;
    var totalCredit = 0;

    $('#adjTableBody tr').each(function() {
      var deb = parseFloat($(this).find('.debit-inr').val()) || 0;
      var crd = parseFloat($(this).find('.credit-inr').val()) || 0;
      totalDebit += deb;
      totalCredit += crd;
    });

    $('#totalDebitINR').text('₹ ' + formatMoney(totalDebit));
    $('#totalCreditINR').text('₹ ' + formatMoney(totalCredit));

    var totalRows = $('#adjTableBody tr').length;
    var diff = Math.abs(totalDebit - totalCredit);

    if (totalRows > 1 && diff >= 0.005) {
      $('#totalDebitINR, #totalCreditINR').addClass('text-danger');
    } else {
      $('#totalDebitINR, #totalCreditINR').removeClass('text-danger');
    }
  }

  function formatMoney(num) {
    var val = parseFloat(num);
    if (isNaN(val)) return '0.00';
    return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Form Submit Validation
  $('#adjustmentForm').on('submit', function(e) {
    var validRows = 0;
    var totalDebit = 0;
    var totalCredit = 0;
    var hasError = false;
    var errorMessage = "";

    $('#adjTableBody tr').each(function(idx) {
      var rowNum = idx + 1;
      var cust = $(this).find('.customer-select').val();
      var deb = parseFloat($(this).find('.debit-inr').val()) || 0;
      var crd = parseFloat($(this).find('.credit-inr').val()) || 0;

      if (!cust) {
        hasError = true;
        errorMessage = "Please select a customer for row #" + rowNum;
        return false;
      }

      if (deb <= 0 && crd <= 0) {
        hasError = true;
        errorMessage = "Please enter either a Debit or Credit amount for row #" + rowNum;
        return false;
      }

      if (deb > 0 && crd > 0) {
        hasError = true;
        errorMessage = "Row #" + rowNum + " cannot contain both Debit and Credit amounts.";
        return false;
      }

      validRows++;
      totalDebit += deb;
      totalCredit += crd;
    });

    if (hasError) {
      e.preventDefault();
      alert(errorMessage);
      return false;
    }

    if (validRows < 1) {
      e.preventDefault();
      alert("Please add at least one valid adjustment row.");
      return false;
    }

    if (validRows > 1) {
      var diff = Math.abs(totalDebit - totalCredit);
      if (diff >= 0.005) {
        e.preventDefault();
        alert("Total Debit (₹ " + formatMoney(totalDebit) + ") must match Total Credit (₹ " + formatMoney(totalCredit) + ") when there are multiple entries.");
        return false;
      }
    }

    // Enable inputs before submit so data is sent in POST
    $('.debit-inr, .credit-inr').prop('disabled', false);
  });

});
</script>
