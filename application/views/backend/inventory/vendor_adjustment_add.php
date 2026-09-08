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
  .adj-table thead tr:first-child th.group-header {
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
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
        <h5 class="mb-0 fw-bold text-dark">Add Vendor Adjustment</h5>
      </div>
      <div class="card-body p-3">
        <form action="<?= base_url('inventory/vendor-adjustment/add_post') ?>" method="post" id="adjustmentForm">
          
          <!-- Header Date & Type & Amount Type -->
          <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
              <label for="date" class="form-label fw-semibold fs-12 text-secondary mb-1">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" id="date" class="form-control table-input" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3 col-sm-6">
              <label for="type" class="form-label fw-semibold fs-12 text-secondary mb-1">Type <span class="text-danger">*</span></label>
              <select name="type" id="type" class="form-select table-input" required>
                <option value="unofficial" selected>Unofficial</option>
                <option value="official">Official</option>
              </select>
            </div>
            <div class="col-md-3 col-sm-6">
              <label for="amt_type_id" class="form-label fw-semibold fs-12 text-secondary mb-1">Amount Type</label>
              <select name="amt_type_id" id="amt_type_id" class="form-select table-input">
                <option value="">-- Select Amount Type --</option>
                <?php if (!empty($other_charges)): ?>
                  <?php foreach ($other_charges as $oc): ?>
                    <option value="<?= $oc['id'] ?>"><?= html_escape($oc['name']) ?></option>
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
                  <th rowspan="2" style="width: 40px;" class="text-center">#</th>
                  <th rowspan="2" style="min-width: 220px;">Vendor <span class="text-danger">*</span></th>
                  <th colspan="3" class="text-center group-header">Debit</th>
                  <th colspan="3" class="text-center group-header">Credit</th>
                  <th rowspan="2" style="min-width: 160px;">Remark</th>
                  <th rowspan="2" style="width: 45px;" class="text-center"></th>
                </tr>
                <tr>
                  <th style="width: 110px;" class="text-end">INR (₹)</th>
                  <th style="width: 105px;" class="text-end">USD ($)</th>
                  <th style="width: 105px;" class="text-end">RMB (¥)</th>
                  <th style="width: 110px;" class="text-end">INR (₹)</th>
                  <th style="width: 105px;" class="text-end">USD ($)</th>
                  <th style="width: 105px;" class="text-end">RMB (¥)</th>
                </tr>
              </thead>
              <tbody id="adjTableBody">
                <tr class="adj-row">
                  <td class="text-center row-index fw-semibold text-muted fs-12">1</td>
                  <td>
                    <select name="vendor_id[]" class="form-select select2-dynamic vendor-select" required>
                      <option value="">-- Select Vendor --</option>
                      <?php if (!empty($vendors)): ?>
                        <?php foreach ($vendors as $v): ?>
                          <option value="<?= $v['id'] ?>"><?= html_escape($v['name']) ?></option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                    <div class="vendor-ledger-info ledger-info-box mt-1 d-none"></div>
                  </td>
                  <td>
                    <input type="number" step="0.01" min="0" name="debit_inr[]" class="form-control table-input debit-field debit-inr mono-amount text-end" placeholder="0.00">
                  </td>
                  <td>
                    <input type="number" step="0.00001" min="0" name="debit_usd[]" class="form-control table-input debit-field debit-usd mono-amount text-end" placeholder="0.00">
                  </td>
                  <td>
                    <input type="number" step="0.00001" min="0" name="debit_rmb[]" class="form-control table-input debit-field debit-rmb mono-amount text-end" placeholder="0.00">
                  </td>
                  <td>
                    <input type="number" step="0.01" min="0" name="credit_inr[]" class="form-control table-input credit-field credit-inr mono-amount text-end" placeholder="0.00">
                  </td>
                  <td>
                    <input type="number" step="0.00001" min="0" name="credit_usd[]" class="form-control table-input credit-field credit-usd mono-amount text-end" placeholder="0.00">
                  </td>
                  <td>
                    <input type="number" step="0.00001" min="0" name="credit_rmb[]" class="form-control table-input credit-field credit-rmb mono-amount text-end" placeholder="0.00">
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
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2" class="text-end text-secondary fw-semibold">Total:</td>
                  <td class="text-end mono-amount fw-bold" id="debTotINR">0.00</td>
                  <td class="text-end mono-amount fw-bold" id="debTotUSD">0.00</td>
                  <td class="text-end mono-amount fw-bold" id="debTotRMB">0.00</td>
                  <td class="text-end mono-amount fw-bold" id="crdTotINR">0.00</td>
                  <td class="text-end mono-amount fw-bold" id="crdTotUSD">0.00</td>
                  <td class="text-end mono-amount fw-bold" id="crdTotRMB">0.00</td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Bottom Actions -->
          <div class="d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
            <a href="<?= base_url('inventory/vendor-adjustment') ?>" class="btn btn-light px-4 py-2" style="border-radius: 6px; font-weight: 500;">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2" id="btnSubmit" style="border-radius: 6px; font-weight: 500;">
              <i class="feather icon-check me-1"></i> Submit
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
      <select name="vendor_id[]" class="form-select select2-dynamic vendor-select" required>
        <option value="">-- Select Vendor --</option>
        <?php if (!empty($vendors)): ?>
          <?php foreach ($vendors as $v): ?>
            <option value="<?= $v['id'] ?>"><?= html_escape($v['name']) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="vendor-ledger-info ledger-info-box mt-1 d-none"></div>
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="debit_inr[]" class="form-control table-input debit-field debit-inr mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.00001" min="0" name="debit_usd[]" class="form-control table-input debit-field debit-usd mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.00001" min="0" name="debit_rmb[]" class="form-control table-input debit-field debit-rmb mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="credit_inr[]" class="form-control table-input credit-field credit-inr mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.00001" min="0" name="credit_usd[]" class="form-control table-input credit-field credit-usd mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.00001" min="0" name="credit_rmb[]" class="form-control table-input credit-field credit-rmb mono-amount text-end" placeholder="0.00">
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

  var currentAdjId = 0;
  var vendorLedgerCache = {};

  initSelect2();

  function initSelect2($context) {
    var $targets = $context ? $context.find('.select2-dynamic') : $('.select2-dynamic');
    $targets.each(function() {
      if (!$(this).hasClass("select2-hidden-accessible")) {
        $(this).select2({
          placeholder: "-- Select Vendor --",
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

  // Fetch Vendor Ledger Summary on Select
  function fetchVendorLedger($selectElem) {
    var vendorId = $selectElem.val();
    var $infoBox = $selectElem.closest('td').find('.vendor-ledger-info');

    if (!vendorId) {
      $infoBox.empty().addClass('d-none');
      return;
    }

    if (vendorLedgerCache[vendorId]) {
      renderVendorLedger($infoBox, vendorLedgerCache[vendorId]);
      return;
    }

    $infoBox.removeClass('d-none').html('<span class="text-muted fs-11"><i class="feather icon-loader fa-spin"></i> Loading...</span>');

    $.ajax({
      url: '<?= base_url('inventory/get_vendor_ledger_summary_ajax') ?>',
      type: 'POST',
      dataType: 'json',
      data: {
        vendor_id: vendorId,
        current_adj_id: currentAdjId
      },
      success: function(res) {
        if (res.success && res.data) {
          vendorLedgerCache[vendorId] = res.data;
          renderVendorLedger($infoBox, res.data);
        } else {
          $infoBox.html('<span class="text-muted fs-11">No ledger data</span>');
        }
      },
      error: function() {
        $infoBox.html('<span class="text-danger fs-11">Failed to load</span>');
      }
    });
  }

  function renderVendorLedger($infoBox, data) {
    var bal = data.balance || {};
    var rmb = parseFloat(bal.rmb) || 0;
    var usd = parseFloat(bal.usd) || 0;
    var inr = parseFloat(bal.inr) || 0;

    var badges = [];

    // INR
    if (inr > 0.005) {
      badges.push('<span class="ledger-badge-due">Out: ₹ ' + formatMoney(inr, 2) + '</span>');
    } else if (inr < -0.005) {
      badges.push('<span class="ledger-badge-advance">Adv: ₹ ' + formatMoney(Math.abs(inr), 2) + '</span>');
    }

    // USD
    if (usd > 0.00005) {
      badges.push('<span class="ledger-badge-due">Out: $ ' + formatMoney(usd, 2) + '</span>');
    } else if (usd < -0.00005) {
      badges.push('<span class="ledger-badge-advance">Adv: $ ' + formatMoney(Math.abs(usd), 2) + '</span>');
    }

    // RMB
    if (rmb > 0.00005) {
      badges.push('<span class="ledger-badge-due">Out: ¥ ' + formatMoney(rmb, 2) + '</span>');
    } else if (rmb < -0.00005) {
      badges.push('<span class="ledger-badge-advance">Adv: ¥ ' + formatMoney(Math.abs(rmb), 2) + '</span>');
    }

    if (badges.length === 0) {
      badges.push('<span class="ledger-badge-zero">Balance: Nil</span>');
    }

    var html = '<div class="d-flex flex-wrap gap-1 align-items-center">' + badges.join('') + '</div>';
    $infoBox.removeClass('d-none').html(html);
  }

  $(document).on('change', '.vendor-select', function() {
    fetchVendorLedger($(this));
  });

  // Handle Mutual Exclusion between Debit & Credit
  $(document).on('input', '.debit-field', function() {
    var $row = $(this).closest('tr');
    var hasDebit = checkRowHasDebit($row);
    var $creditFields = $row.find('.credit-field');

    if (hasDebit) {
      $creditFields.val('').prop('disabled', true).addClass('table-input-disabled');
    } else {
      $creditFields.prop('disabled', false).removeClass('table-input-disabled');
    }
    calculateTotals();
  });

  $(document).on('input', '.credit-field', function() {
    var $row = $(this).closest('tr');
    var hasCredit = checkRowHasCredit($row);
    var $debitFields = $row.find('.debit-field');

    if (hasCredit) {
      $debitFields.val('').prop('disabled', true).addClass('table-input-disabled');
    } else {
      $debitFields.prop('disabled', false).removeClass('table-input-disabled');
    }
    calculateTotals();
  });

  function checkRowHasDebit($row) {
    var inr = parseFloat($row.find('.debit-inr').val()) || 0;
    var usd = parseFloat($row.find('.debit-usd').val()) || 0;
    var rmb = parseFloat($row.find('.debit-rmb').val()) || 0;
    return (inr > 0 || usd > 0 || rmb > 0);
  }

  function checkRowHasCredit($row) {
    var inr = parseFloat($row.find('.credit-inr').val()) || 0;
    var usd = parseFloat($row.find('.credit-usd').val()) || 0;
    var rmb = parseFloat($row.find('.credit-rmb').val()) || 0;
    return (inr > 0 || usd > 0 || rmb > 0);
  }

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
    var debINR = 0, debUSD = 0, debRMB = 0;
    var crdINR = 0, crdUSD = 0, crdRMB = 0;

    $('#adjTableBody tr').each(function() {
      var dINR = parseFloat($(this).find('.debit-inr').val()) || 0;
      var dUSD = parseFloat($(this).find('.debit-usd').val()) || 0;
      var dRMB = parseFloat($(this).find('.debit-rmb').val()) || 0;

      var cINR = parseFloat($(this).find('.credit-inr').val()) || 0;
      var cUSD = parseFloat($(this).find('.credit-usd').val()) || 0;
      var cRMB = parseFloat($(this).find('.credit-rmb').val()) || 0;

      debINR += dINR;
      debUSD += dUSD;
      debRMB += dRMB;

      crdINR += cINR;
      crdUSD += cUSD;
      crdRMB += cRMB;
    });

    $('#debTotINR').text(formatMoney(debINR, 2));
    $('#debTotUSD').text(formatMoney(debUSD, 2));
    $('#debTotRMB').text(formatMoney(debRMB, 2));

    $('#crdTotINR').text(formatMoney(crdINR, 2));
    $('#crdTotUSD').text(formatMoney(crdUSD, 2));
    $('#crdTotRMB').text(formatMoney(crdRMB, 2));

    var totalRows = $('#adjTableBody tr').length;
    var diffINR = Math.abs(debINR - crdINR);
    var diffUSD = Math.abs(debUSD - crdUSD);
    var diffRMB = Math.abs(debRMB - crdRMB);

    if (totalRows > 1 && diffINR >= 0.005) {
      $('#debTotINR, #crdTotINR').addClass('text-danger');
    } else {
      $('#debTotINR, #crdTotINR').removeClass('text-danger');
    }

    if (totalRows > 1 && diffUSD >= 0.00005) {
      $('#debTotUSD, #crdTotUSD').addClass('text-danger');
    } else {
      $('#debTotUSD, #crdTotUSD').removeClass('text-danger');
    }

    if (totalRows > 1 && diffRMB >= 0.00005) {
      $('#debTotRMB, #crdTotRMB').addClass('text-danger');
    } else {
      $('#debTotRMB, #crdTotRMB').removeClass('text-danger');
    }
  }

  function formatMoney(num, decimals) {
    var val = parseFloat(num);
    if (isNaN(val)) return (0).toFixed(decimals);
    return val.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
  }

  // Form Submit Validation
  $('#adjustmentForm').on('submit', function(e) {
    var validRows = 0;
    var debINR = 0, debUSD = 0, debRMB = 0;
    var crdINR = 0, crdUSD = 0, crdRMB = 0;
    var hasError = false;
    var errorMessage = "";

    $('#adjTableBody tr').each(function(idx) {
      var rowNum = idx + 1;
      var ven = $(this).find('.vendor-select').val();

      var dINR = parseFloat($(this).find('.debit-inr').val()) || 0;
      var dUSD = parseFloat($(this).find('.debit-usd').val()) || 0;
      var dRMB = parseFloat($(this).find('.debit-rmb').val()) || 0;

      var cINR = parseFloat($(this).find('.credit-inr').val()) || 0;
      var cUSD = parseFloat($(this).find('.credit-usd').val()) || 0;
      var cRMB = parseFloat($(this).find('.credit-rmb').val()) || 0;

      var hasDeb = (dINR > 0 || dUSD > 0 || dRMB > 0);
      var hasCrd = (cINR > 0 || cUSD > 0 || cRMB > 0);

      if (!ven) {
        hasError = true;
        errorMessage = "Please select a vendor for row #" + rowNum;
        return false;
      }

      if (!hasDeb && !hasCrd) {
        hasError = true;
        errorMessage = "Please enter either a Debit or Credit amount for row #" + rowNum;
        return false;
      }

      if (hasDeb && hasCrd) {
        hasError = true;
        errorMessage = "Row #" + rowNum + " cannot contain both Debit and Credit amounts.";
        return false;
      }

      validRows++;
      debINR += dINR;
      debUSD += dUSD;
      debRMB += dRMB;

      crdINR += cINR;
      crdUSD += cUSD;
      crdRMB += cRMB;
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
      var diffINR = Math.abs(debINR - crdINR);
      var diffUSD = Math.abs(debUSD - crdUSD);
      var diffRMB = Math.abs(debRMB - crdRMB);

      if (diffINR >= 0.005 || diffUSD >= 0.00005 || diffRMB >= 0.00005) {
        e.preventDefault();
        alert("Debit and Credit totals must match across all currencies when there are multiple entries.");
        return false;
      }
    }

    // Enable all inputs before submit so POST data is complete
    $('.debit-field, .credit-field').prop('disabled', false);
  });

});
</script>
