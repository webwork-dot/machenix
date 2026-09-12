<style>
  .adj-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    background: #ffffff;
  }
  .adj-card .card-header {
    background: #ffffff;
    border-bottom: 1px solid #edf2f7;
    padding: 10px 16px;
  }
  .adj-nav-pills {
    display: inline-flex;
    background: #f1f5f9;
    padding: 2px;
    border-radius: 6px;
    gap: 2px;
  }
  .adj-nav-pills .nav-link {
    border-radius: 4px;
    font-weight: 600;
    font-size: 12px;
    padding: 4px 12px;
    color: #64748b;
    background: transparent;
    border: none;
    transition: all 0.15s ease;
    line-height: 1.4;
  }
  .adj-nav-pills .nav-link:hover {
    color: #1e293b;
  }
  .adj-nav-pills .nav-link.active {
    background: #3b82f6;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(59, 130, 246, 0.25);
  }
  .adj-table {
    border: 1px solid #e2e8f0;
    margin-bottom: 0;
    width: 100%;
  }
  .adj-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border: 1px solid #e2e8f0;
    padding: 5px 7px;
    vertical-align: middle;
    white-space: nowrap;
  }
  .adj-table thead tr:first-child th.group-header {
    background: #f1f5f9;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
  }
  .adj-table tbody td {
    padding: 4px 6px;
    vertical-align: middle;
    border: 1px solid #f1f5f9;
  }
  .adj-table tfoot td {
    background: #f8fafc;
    border-top: 2px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    border-left: 1px solid #f1f5f9;
    border-right: 1px solid #f1f5f9;
    padding: 6px 7px;
    vertical-align: middle;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
  }
  .mono-amount {
    font-family: "DM Mono", Menlo, Consolas, monospace;
    font-size: 12px;
  }
  .table-input {
    height: 32px;
    border-radius: 4px;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    padding: 2px 7px;
    transition: all 0.15s ease;
  }
  .table-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
  }
  .table-input-disabled {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #94a3b8 !important;
    cursor: not-allowed;
  }
  .btn-remove-row {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: 1px solid #fecaca;
    background: #fff5f5;
    color: #ef4444;
    transition: all 0.15s ease;
    padding: 0;
    cursor: pointer;
  }
  .btn-remove-row i {
    pointer-events: none;
    font-size: 13px;
  }
  .btn-remove-row:hover {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
  }
  .select2-container--default .select2-selection--single {
    height: 32px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 4px !important;
    position: relative;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    font-size: 12px;
    padding-left: 8px;
    padding-right: 22px;
    color: #1e293b;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 30px !important;
    position: absolute;
    top: 1px;
    right: 4px;
    width: 18px;
  }
  .ledger-badge-due {
    display: inline-flex;
    align-items: center;
    font-size: 10px;
    font-weight: 600;
    color: #dc2626;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 3px;
    padding: 0 4px;
    line-height: 1.3;
  }
  .ledger-badge-advance {
    display: inline-flex;
    align-items: center;
    font-size: 10px;
    font-weight: 600;
    color: #16a34a;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 3px;
    padding: 0 4px;
    line-height: 1.3;
  }
  .ledger-badge-zero {
    display: inline-flex;
    align-items: center;
    font-size: 10px;
    font-weight: 500;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    padding: 0 4px;
    line-height: 1.3;
  }
  .vendor-ledger-info {
    margin-top: 2px;
    line-height: 1;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card adj-card mb-3">
      <form action="<?= base_url('inventory/vendor-adjustment/add_post') ?>" method="post" id="adjustmentForm" novalidate>
        
        <!-- Header Bar with Title, Tabs, Date, Type & Add Row Button -->
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 fw-bold text-dark fs-14">Add Vendor Adjustment</h5>
            
            <ul class="nav adj-nav-pills" id="adjustmentTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-to-vendors-btn" data-bs-toggle="pill" data-bs-target="#tab-to-vendors" type="button" role="tab" aria-controls="tab-to-vendors" aria-selected="true">
                  <i class="feather icon-users me-1"></i> To Vendors
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-ledger-btn" data-bs-toggle="pill" data-bs-target="#tab-ledger" type="button" role="tab" aria-controls="tab-ledger" aria-selected="false">
                  <i class="feather icon-book me-1"></i> Ledger
                </button>
              </li>
            </ul>
            <input type="hidden" name="adjustment_mode" id="adjustment_mode" value="to_vendors">
          </div>

          <!-- Date & Type & Add Row Button on Right -->
          <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-1">
              <label for="date" class="form-label fw-semibold fs-11 text-secondary mb-0 text-nowrap">Date <span class="text-danger">*</span>:</label>
              <input type="date" name="date" id="date" class="form-control table-input" style="width: 135px;" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="d-flex align-items-center gap-1">
              <label for="type" class="form-label fw-semibold fs-11 text-secondary mb-0 text-nowrap">Type <span class="text-danger">*</span>:</label>
              <select name="type" id="type" class="form-select table-input" style="width: 115px;">
                <option value="unofficial" selected>Unofficial</option>
                <option value="official">Official</option>
              </select>
            </div>
            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1 px-2 py-1 fs-12 ms-1" id="btnAddRowBtn" style="border-radius: 4px; height: 32px;">
              <i class="feather icon-plus"></i> Add Row
            </button>
          </div>
        </div>

        <div class="card-body p-3">
          <!-- Tab Content -->
          <div class="tab-content" id="adjustmentTabsContent">
            
            <!-- Tab 1: To Vendors -->
            <div class="tab-pane fade show active" id="tab-to-vendors" role="tabpanel" aria-labelledby="tab-to-vendors-btn">
              <div class="table-responsive rounded-2">
                <table class="table adj-table align-middle" id="toVendorsTable">
                  <thead>
                    <tr>
                      <th rowspan="2" style="width: 35px;" class="text-center">#</th>
                      <th rowspan="2" style="min-width: 220px;">Vendor <span class="text-danger">*</span></th>
                      <th colspan="3" class="text-center group-header">Debit</th>
                      <th colspan="3" class="text-center group-header">Credit</th>
                      <th rowspan="2" style="min-width: 160px;">Remark</th>
                      <th rowspan="2" style="width: 38px;" class="text-center"></th>
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
                  <tbody id="toVendorsTableBody">
                    <tr class="adj-row">
                      <td class="text-center row-index fw-semibold text-muted fs-12">1</td>
                      <td>
                        <select name="vendor_id[]" class="form-select select2-dynamic vendor-select">
                          <option value="">-- Select Vendor --</option>
                          <?php if (!empty($vendors)): ?>
                            <?php foreach ($vendors as $v): ?>
                              <option value="<?= $v['id'] ?>"><?= html_escape($v['name']) ?></option>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </select>
                        <div class="vendor-ledger-info d-none"></div>
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
                        <button type="button" class="btn-remove-row btn-remove-row-tovendors" title="Delete / Clear Row">
                          <i class="feather icon-trash-2"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="2" class="text-end text-secondary fw-semibold">Total:</td>
                      <td class="text-end mono-amount fw-bold" id="totalDebitINR">₹ 0.00</td>
                      <td class="text-end mono-amount fw-bold" id="totalDebitUSD">$ 0.00</td>
                      <td class="text-end mono-amount fw-bold" id="totalDebitRMB">¥ 0.00</td>
                      <td class="text-end mono-amount fw-bold" id="totalCreditINR">₹ 0.00</td>
                      <td class="text-end mono-amount fw-bold" id="totalCreditUSD">$ 0.00</td>
                      <td class="text-end mono-amount fw-bold" id="totalCreditRMB">¥ 0.00</td>
                      <td colspan="2"></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <!-- Tab 2: Ledger -->
            <div class="tab-pane fade" id="tab-ledger" role="tabpanel" aria-labelledby="tab-ledger-btn">
              <div class="table-responsive rounded-2">
                <table class="table adj-table align-middle" id="ledgerTable">
                  <thead>
                    <tr>
                      <th style="width: 35px;" class="text-center">#</th>
                      <th style="min-width: 220px;">Vendor <span class="text-danger">*</span></th>
                      <th style="width: 180px;">Amount Type</th>
                      <th style="width: 120px;">Type <span class="text-danger">*</span></th>
                      <th style="width: 110px;" class="text-end">INR (₹)</th>
                      <th style="width: 105px;" class="text-end">USD ($)</th>
                      <th style="width: 105px;" class="text-end">RMB (¥)</th>
                      <th style="min-width: 160px;">Remark</th>
                      <th style="width: 38px;" class="text-center"></th>
                    </tr>
                  </thead>
                  <tbody id="ledgerTableBody">
                    <tr class="adj-row">
                      <td class="text-center row-index fw-semibold text-muted fs-12">1</td>
                      <td>
                        <select name="ledger_vendor_id[]" class="form-select select2-dynamic vendor-select">
                          <option value="">-- Select Vendor --</option>
                          <?php if (!empty($vendors)): ?>
                            <?php foreach ($vendors as $v): ?>
                              <option value="<?= $v['id'] ?>"><?= html_escape($v['name']) ?></option>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </select>
                        <div class="vendor-ledger-info d-none"></div>
                      </td>
                      <td>
                        <select name="ledger_amt_type_id[]" class="form-select table-input select2-dynamic amt-type-select">
                          <option value="">-- Select Amount Type --</option>
                          <?php if (!empty($other_charges)): ?>
                            <?php foreach ($other_charges as $oc): ?>
                              <option value="<?= $oc['id'] ?>"><?= html_escape($oc['name']) ?></option>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </select>
                      </td>
                      <td>
                        <select name="ledger_amt_type[]" class="form-select table-input">
                          <option value="plus">Plus (+)</option>
                          <option value="minus">Minus (-)</option>
                        </select>
                      </td>
                      <td>
                        <input type="number" step="0.01" min="0" name="ledger_inr[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
                      </td>
                      <td>
                        <input type="number" step="0.00001" min="0" name="ledger_usd[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
                      </td>
                      <td>
                        <input type="number" step="0.00001" min="0" name="ledger_rmb[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
                      </td>
                      <td>
                        <input type="text" name="ledger_remark[]" class="form-control table-input" placeholder="Enter remark...">
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn-remove-row btn-remove-row-ledger" title="Delete / Clear Row">
                          <i class="feather icon-trash-2"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>

          <!-- Bottom Actions -->
          <div class="d-flex justify-content-end align-items-center gap-2 pt-3 mt-2 border-top">
            <a href="<?= base_url('inventory/vendor-adjustment') ?>" class="btn btn-light px-3 py-1 fs-13" style="border-radius: 4px; font-weight: 500;">Cancel</a>
            <button type="submit" class="btn btn-primary px-3 py-1 fs-13" id="btnSubmit" style="border-radius: 4px; font-weight: 500;">
              <i class="feather icon-check me-1"></i> Submit
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Template for dynamic clean rows in To Vendors Tab -->
<template id="toVendorsRowTemplate">
  <tr class="adj-row">
    <td class="text-center row-index fw-semibold text-muted fs-12"></td>
    <td>
      <select name="vendor_id[]" class="form-select select2-dynamic vendor-select">
        <option value="">-- Select Vendor --</option>
        <?php if (!empty($vendors)): ?>
          <?php foreach ($vendors as $v): ?>
            <option value="<?= $v['id'] ?>"><?= html_escape($v['name']) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="vendor-ledger-info d-none"></div>
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
      <button type="button" class="btn-remove-row btn-remove-row-tovendors" title="Delete / Clear Row">
        <i class="feather icon-trash-2"></i>
      </button>
    </td>
  </tr>
</template>

<!-- Template for dynamic clean rows in Ledger Tab -->
<template id="ledgerRowTemplate">
  <tr class="adj-row">
    <td class="text-center row-index fw-semibold text-muted fs-12"></td>
    <td>
      <select name="ledger_vendor_id[]" class="form-select select2-dynamic vendor-select">
        <option value="">-- Select Vendor --</option>
        <?php if (!empty($vendors)): ?>
          <?php foreach ($vendors as $v): ?>
            <option value="<?= $v['id'] ?>"><?= html_escape($v['name']) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="vendor-ledger-info d-none"></div>
    </td>
    <td>
      <select name="ledger_amt_type_id[]" class="form-select table-input select2-dynamic amt-type-select">
        <option value="">-- Select Amount Type --</option>
        <?php if (!empty($other_charges)): ?>
          <?php foreach ($other_charges as $oc): ?>
            <option value="<?= $oc['id'] ?>"><?= html_escape($oc['name']) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </td>
    <td>
      <select name="ledger_amt_type[]" class="form-select table-input">
        <option value="plus">Plus (+)</option>
        <option value="minus">Minus (-)</option>
      </select>
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="ledger_inr[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.00001" min="0" name="ledger_usd[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="number" step="0.00001" min="0" name="ledger_rmb[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
    </td>
    <td>
      <input type="text" name="ledger_remark[]" class="form-control table-input" placeholder="Enter remark...">
    </td>
    <td class="text-center">
      <button type="button" class="btn-remove-row btn-remove-row-ledger" title="Delete / Clear Row">
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
        var placeholderText = $(this).hasClass('vendor-select') ? "-- Select Vendor --" : "-- Select Amount Type --";
        $(this).select2({
          placeholder: placeholderText,
          width: '100%'
        });
      }
    });
  }

  // Handle Tab Switching
  $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("data-bs-target");
    if (target === '#tab-ledger') {
      $('#adjustment_mode').val('ledger');
      initSelect2($('#tab-ledger'));
    } else {
      $('#adjustment_mode').val('to_vendors');
      initSelect2($('#tab-to-vendors'));
      calculateTotals();
    }
  });

  // Master Add Row button in Header Bar
  $('#btnAddRowBtn').on('click', function() {
    var mode = $('#adjustment_mode').val();
    if (mode === 'ledger') {
      var html = $('#ledgerRowTemplate').html();
      var $newRow = $(html);
      $('#ledgerTableBody').append($newRow);
      initSelect2($newRow);
      updateRowIndices('#ledgerTableBody');
    } else {
      var html = $('#toVendorsRowTemplate').html();
      var $newRow = $(html);
      $('#toVendorsTableBody').append($newRow);
      initSelect2($newRow);
      updateRowIndices('#toVendorsTableBody');
      calculateTotals();
    }
  });

  function updateRowIndices(tableBodyId) {
    $(tableBodyId + ' tr').each(function(index) {
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

    $infoBox.removeClass('d-none').html('<span class="text-muted fs-10"><i class="feather icon-loader fa-spin"></i> Loading...</span>');

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
          $infoBox.html('<span class="text-muted fs-10">No ledger data</span>');
        }
      },
      error: function() {
        $infoBox.html('<span class="text-danger fs-10">Failed to load</span>');
      }
    });
  }

  function renderVendorLedger($infoBox, data) {
    var balINR = parseFloat(data.balance.inr) || 0;
    var balUSD = parseFloat(data.balance.usd) || 0;
    var balRMB = parseFloat(data.balance.rmb) || 0;

    var badges = [];
    if (Math.abs(balINR) > 0.005) {
      badges.push(balINR > 0 ? '₹ ' + formatMoney(balINR, 2) + ' Due' : '₹ ' + formatMoney(Math.abs(balINR), 2) + ' Adv');
    }
    if (Math.abs(balUSD) > 0.0001) {
      badges.push(balUSD > 0 ? '$ ' + formatMoney(balUSD, 2) + ' Due' : '$ ' + formatMoney(Math.abs(balUSD), 2) + ' Adv');
    }
    if (Math.abs(balRMB) > 0.0001) {
      badges.push(balRMB > 0 ? '¥ ' + formatMoney(balRMB, 2) + ' Due' : '¥ ' + formatMoney(Math.abs(balRMB), 2) + ' Adv');
    }

    if (badges.length === 0) {
      $infoBox.removeClass('d-none').html('<span class="ledger-badge-zero">Balance: 0.00</span>');
    } else {
      var isDue = (balINR > 0 || balUSD > 0 || balRMB > 0);
      var badgeClass = isDue ? 'ledger-badge-due' : 'ledger-badge-advance';
      var iconClass = isDue ? 'feather icon-alert-circle' : 'feather icon-check-circle';
      $infoBox.removeClass('d-none').html('<span class="' + badgeClass + '"><i class="' + iconClass + ' me-1"></i>' + badges.join(' | ') + '</span>');
    }
  }

  $(document).on('change', '.vendor-select', function() {
    fetchVendorLedger($(this));
  });

  // Mutual exclusion: Debit vs Credit in To Vendors Tab
  $(document).on('input', '.debit-field', function() {
    var $row = $(this).closest('tr');
    var hasVal = false;
    $row.find('.debit-field').each(function() {
      if (parseFloat($(this).val()) > 0) hasVal = true;
    });

    var $creditFields = $row.find('.credit-field');
    if (hasVal) {
      $creditFields.val('').prop('disabled', true).addClass('table-input-disabled');
    } else {
      $creditFields.prop('disabled', false).removeClass('table-input-disabled');
    }
    calculateTotals();
  });

  $(document).on('input', '.credit-field', function() {
    var $row = $(this).closest('tr');
    var hasVal = false;
    $row.find('.credit-field').each(function() {
      if (parseFloat($(this).val()) > 0) hasVal = true;
    });

    var $debitFields = $row.find('.debit-field');
    if (hasVal) {
      $debitFields.val('').prop('disabled', true).addClass('table-input-disabled');
    } else {
      $debitFields.prop('disabled', false).removeClass('table-input-disabled');
    }
    calculateTotals();
  });

  // Universal Responsive Remove / Clear Row Handler
  $(document).on('click', '.btn-remove-row, .btn-remove-row-tovendors, .btn-remove-row-ledger', function(e) {
    e.preventDefault();
    var $row = $(this).closest('tr');
    var $tbody = $row.closest('tbody');
    var isLedger = $tbody.attr('id') === 'ledgerTableBody';

    if ($tbody.find('tr').length > 1) {
      $row.remove();
      if (isLedger) {
        updateRowIndices('#ledgerTableBody');
      } else {
        updateRowIndices('#toVendorsTableBody');
        calculateTotals();
      }
    } else {
      $row.find('.vendor-select').val('').trigger('change');
      $row.find('.vendor-ledger-info').empty().addClass('d-none');
      if (isLedger) {
        $row.find('.amt-type-select').val('').trigger('change');
        $row.find('select[name="ledger_amt_type[]"]').val('plus');
        $row.find('input[name="ledger_inr[]"]').val('');
        $row.find('input[name="ledger_usd[]"]').val('');
        $row.find('input[name="ledger_rmb[]"]').val('');
        $row.find('input[name="ledger_remark[]"]').val('');
      } else {
        $row.find('.debit-field, .credit-field').val('').prop('disabled', false).removeClass('table-input-disabled');
        $row.find('input[name="remark[]"]').val('');
        calculateTotals();
      }
    }
  });

  // Calculate Totals for To Vendors Tab
  function calculateTotals() {
    var dINR = 0, dUSD = 0, dRMB = 0;
    var cINR = 0, cUSD = 0, cRMB = 0;

    $('#toVendorsTableBody tr').each(function() {
      dINR += parseFloat($(this).find('.debit-inr').val()) || 0;
      dUSD += parseFloat($(this).find('.debit-usd').val()) || 0;
      dRMB += parseFloat($(this).find('.debit-rmb').val()) || 0;
      cINR += parseFloat($(this).find('.credit-inr').val()) || 0;
      cUSD += parseFloat($(this).find('.credit-usd').val()) || 0;
      cRMB += parseFloat($(this).find('.credit-rmb').val()) || 0;
    });

    $('#totalDebitINR').text('₹ ' + formatMoney(dINR));
    $('#totalDebitUSD').text('$ ' + formatMoney(dUSD));
    $('#totalDebitRMB').text('¥ ' + formatMoney(dRMB));
    $('#totalCreditINR').text('₹ ' + formatMoney(cINR));
    $('#totalCreditUSD').text('$ ' + formatMoney(cUSD));
    $('#totalCreditRMB').text('¥ ' + formatMoney(cRMB));

    var totalRows = $('#toVendorsTableBody tr').length;
    var diffINR = Math.abs(dINR - cINR);
    var diffUSD = Math.abs(dUSD - cUSD);
    var diffRMB = Math.abs(dRMB - cRMB);

    if (totalRows > 1 && (diffINR >= 0.005 || diffUSD >= 0.0001 || diffRMB >= 0.0001)) {
      $('#totalDebitINR, #totalCreditINR, #totalDebitUSD, #totalCreditUSD, #totalDebitRMB, #totalCreditRMB').addClass('text-danger');
    } else {
      $('#totalDebitINR, #totalCreditINR, #totalDebitUSD, #totalCreditUSD, #totalDebitRMB, #totalCreditRMB').removeClass('text-danger');
    }
  }

  function formatMoney(num, decimals) {
    var val = parseFloat(num);
    if (isNaN(val)) return '0.00';
    var dec = (decimals !== undefined) ? decimals : 2;
    return val.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
  }

  // Form Submit Validation
  $('#adjustmentForm').on('submit', function(e) {
    var dateVal = $('#date').val();
    if (!dateVal) {
      e.preventDefault();
      alert("Please select a date.");
      $('#date').focus();
      return false;
    }

    var mode = $('#adjustment_mode').val();

    if (mode === 'ledger') {
      $('#tab-to-vendors').find('input, select').prop('disabled', true);
      $('#tab-ledger').find('input, select').prop('disabled', false);

      var validRows = 0;
      var hasError = false;
      var errorMessage = "";

      $('#ledgerTableBody tr').each(function(idx) {
        var rowNum = idx + 1;
        var vendor = $(this).find('.vendor-select').val();
        var inr = parseFloat($(this).find('input[name="ledger_inr[]"]').val()) || 0;
        var usd = parseFloat($(this).find('input[name="ledger_usd[]"]').val()) || 0;
        var rmb = parseFloat($(this).find('input[name="ledger_rmb[]"]').val()) || 0;

        if (!vendor) {
          hasError = true;
          errorMessage = "Please select a vendor for row #" + rowNum;
          return false;
        }

        if (inr <= 0 && usd <= 0 && rmb <= 0) {
          hasError = true;
          errorMessage = "Please enter at least one amount (INR, USD, or RMB) greater than 0 for row #" + rowNum;
          return false;
        }

        validRows++;
      });

      if (hasError) {
        e.preventDefault();
        $('#tab-to-vendors').find('input, select').prop('disabled', false);
        alert(errorMessage);
        return false;
      }

      if (validRows < 1) {
        e.preventDefault();
        $('#tab-to-vendors').find('input, select').prop('disabled', false);
        alert("Please add at least one valid adjustment row.");
        return false;
      }

      return true;

    } else {
      $('#tab-ledger').find('input, select').prop('disabled', true);
      $('#tab-to-vendors').find('input, select').prop('disabled', false);

      var validRows = 0;
      var dINR = 0, dUSD = 0, dRMB = 0;
      var cINR = 0, cUSD = 0, cRMB = 0;
      var hasError = false;
      var errorMessage = "";

      $('#toVendorsTableBody tr').each(function(idx) {
        var rowNum = idx + 1;
        var vendor = $(this).find('.vendor-select').val();
        var debINR = parseFloat($(this).find('.debit-inr').val()) || 0;
        var debUSD = parseFloat($(this).find('.debit-usd').val()) || 0;
        var debRMB = parseFloat($(this).find('.debit-rmb').val()) || 0;
        var crdINR = parseFloat($(this).find('.credit-inr').val()) || 0;
        var crdUSD = parseFloat($(this).find('.credit-usd').val()) || 0;
        var crdRMB = parseFloat($(this).find('.credit-rmb').val()) || 0;

        var hasDeb = (debINR > 0 || debUSD > 0 || debRMB > 0);
        var hasCrd = (crdINR > 0 || crdUSD > 0 || crdRMB > 0);

        if (!vendor) {
          hasError = true;
          errorMessage = "Please select a vendor for row #" + rowNum;
          return false;
        }

        if (!hasDeb && !hasCrd) {
          hasError = true;
          errorMessage = "Please enter either Debit or Credit amount for row #" + rowNum;
          return false;
        }

        if (hasDeb && hasCrd) {
          hasError = true;
          errorMessage = "Row #" + rowNum + " cannot contain both Debit and Credit amounts.";
          return false;
        }

        validRows++;
        dINR += debINR; dUSD += debUSD; dRMB += debRMB;
        cINR += crdINR; cUSD += crdUSD; cRMB += crdRMB;
      });

      if (hasError) {
        e.preventDefault();
        $('#tab-ledger').find('input, select').prop('disabled', false);
        alert(errorMessage);
        return false;
      }

      if (validRows < 1) {
        e.preventDefault();
        $('#tab-ledger').find('input, select').prop('disabled', false);
        alert("Please add at least one valid adjustment row.");
        return false;
      }

      if (validRows > 1) {
        if (Math.abs(dINR - cINR) >= 0.005 || Math.abs(dUSD - cUSD) >= 0.0001 || Math.abs(dRMB - cRMB) >= 0.0001) {
          e.preventDefault();
          $('#tab-ledger').find('input, select').prop('disabled', false);
          alert("Total Debit amounts must match Total Credit amounts when there are multiple entries.");
          return false;
        }
      }

      $('.debit-field, .credit-field').prop('disabled', false);
      return true;
    }
  });

});
</script>
