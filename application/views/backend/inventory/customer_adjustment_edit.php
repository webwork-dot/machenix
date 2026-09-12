<?php
$row = $data ?? [];
$id = !empty($id) ? $id : ($row['id'] ?? 0);
$details = $details ?? [];
if (empty($details) && !empty($row)) {
  $details = [$row];
}

$is_ledger_mode = false;
if (!empty($details)) {
  foreach ($details as $d) {
    if (!empty($d['amt_type_id'])) {
      $is_ledger_mode = true;
      break;
    }
  }
}
?>

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
    padding: 6px 8px;
    vertical-align: middle;
    white-space: nowrap;
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
    padding: 6px 8px;
    vertical-align: middle;
    font-weight: 700;
    font-size: 12px;
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
  .customer-ledger-info {
    margin-top: 2px;
    line-height: 1;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card adj-card mb-3">
      <form action="<?= base_url('inventory/customer-adjustment/edit_post/' . $id) ?>" method="post" id="adjustmentForm" novalidate>
        
        <!-- Header Bar with Title, Tabs, Date, Type & Add Row Button -->
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 fw-bold text-dark fs-14">Edit Customer Adjustment <?= !empty($id) ? '#' . html_escape($id) : '' ?></h5>
            
            <ul class="nav adj-nav-pills" id="adjustmentTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link <?= !$is_ledger_mode ? 'active' : '' ?>" id="tab-to-customers-btn" data-bs-toggle="pill" data-bs-target="#tab-to-customers" type="button" role="tab" aria-controls="tab-to-customers" aria-selected="<?= !$is_ledger_mode ? 'true' : 'false' ?>">
                  <i class="feather icon-users me-1"></i> To Customers
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link <?= $is_ledger_mode ? 'active' : '' ?>" id="tab-ledger-btn" data-bs-toggle="pill" data-bs-target="#tab-ledger" type="button" role="tab" aria-controls="tab-ledger" aria-selected="<?= $is_ledger_mode ? 'true' : 'false' ?>">
                  <i class="feather icon-book me-1"></i> Ledger
                </button>
              </li>
            </ul>
            <input type="hidden" name="adjustment_mode" id="adjustment_mode" value="<?= $is_ledger_mode ? 'ledger' : 'to_customers' ?>">
          </div>

          <!-- Date & Type & Add Row Button on Right -->
          <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-1">
              <label for="date" class="form-label fw-semibold fs-11 text-secondary mb-0 text-nowrap">Date <span class="text-danger">*</span>:</label>
              <input type="date" name="date" id="date" class="form-control table-input" style="width: 135px;" value="<?= html_escape($row['date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="d-flex align-items-center gap-1">
              <label for="type" class="form-label fw-semibold fs-11 text-secondary mb-0 text-nowrap">Type <span class="text-danger">*</span>:</label>
              <select name="type" id="type" class="form-select table-input" style="width: 115px;">
                <option value="unofficial" <?= (isset($row['type']) && $row['type'] == 'unofficial') ? 'selected' : '' ?>>Unofficial</option>
                <option value="official" <?= (isset($row['type']) && $row['type'] == 'official') ? 'selected' : '' ?>>Official</option>
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
            
            <!-- Tab 1: To Customers -->
            <div class="tab-pane fade <?= !$is_ledger_mode ? 'show active' : '' ?>" id="tab-to-customers" role="tabpanel" aria-labelledby="tab-to-customers-btn">
              <div class="table-responsive rounded-2">
                <table class="table adj-table align-middle" id="toCustTable">
                  <thead>
                    <tr>
                      <th style="width: 35px;" class="text-center">#</th>
                      <th style="min-width: 260px;">Customer <span class="text-danger">*</span></th>
                      <th style="width: 150px;" class="text-end">Debit (₹)</th>
                      <th style="width: 150px;" class="text-end">Credit (₹)</th>
                      <th style="min-width: 180px;">Remark</th>
                      <th style="width: 38px;" class="text-center"></th>
                    </tr>
                  </thead>
                  <tbody id="toCustTableBody">
                    <?php if (!$is_ledger_mode && !empty($details)): ?>
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
                            <select name="customer_id[]" class="form-select select2-dynamic customer-select">
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
                            <div class="customer-ledger-info d-none"></div>
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
                            <button type="button" class="btn-remove-row btn-remove-row-tocust" title="Delete / Clear Row">
                              <i class="feather icon-trash-2"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr class="adj-row">
                        <td class="text-center row-index fw-semibold text-muted fs-12">1</td>
                        <td>
                          <select name="customer_id[]" class="form-select select2-dynamic customer-select">
                            <option value="">-- Select Customer --</option>
                            <?php if (!empty($customers)): ?>
                              <?php foreach ($customers as $c): 
                                $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
                              ?>
                                <option value="<?= $c['id'] ?>"><?= html_escape($displayName) ?></option>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </select>
                          <div class="customer-ledger-info d-none"></div>
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
                          <button type="button" class="btn-remove-row btn-remove-row-tocust" title="Delete / Clear Row">
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
            </div>

            <!-- Tab 2: Ledger -->
            <div class="tab-pane fade <?= $is_ledger_mode ? 'show active' : '' ?>" id="tab-ledger" role="tabpanel" aria-labelledby="tab-ledger-btn">
              <div class="table-responsive rounded-2">
                <table class="table adj-table align-middle" id="ledgerTable">
                  <thead>
                    <tr>
                      <th style="width: 35px;" class="text-center">#</th>
                      <th style="min-width: 250px;">Customer <span class="text-danger">*</span></th>
                      <th style="width: 200px;">Amount Type</th>
                      <th style="width: 125px;">Type <span class="text-danger">*</span></th>
                      <th style="width: 140px;" class="text-end">Amount (₹) <span class="text-danger">*</span></th>
                      <th style="min-width: 180px;">Remark</th>
                      <th style="width: 38px;" class="text-center"></th>
                    </tr>
                  </thead>
                  <tbody id="ledgerTableBody">
                    <?php if ($is_ledger_mode && !empty($details)): ?>
                      <?php foreach ($details as $idx => $det): 
                        $selected_cust = $det['customer_id'] ?? 0;
                        $selected_amt_type_id = $det['amt_type_id'] ?? 0;
                        $selected_amt_type = $det['amt_type'] ?? 'plus';
                        $amt = (float)($det['inr'] ?? 0);
                        $amt_val = $amt > 0 ? number_format($amt, 2, '.', '') : '';
                      ?>
                        <tr class="adj-row">
                          <td class="text-center row-index fw-semibold text-muted fs-12"><?= $idx + 1 ?></td>
                          <td>
                            <select name="ledger_customer_id[]" class="form-select select2-dynamic customer-select">
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
                            <div class="customer-ledger-info d-none"></div>
                          </td>
                          <td>
                            <select name="ledger_amt_type_id[]" class="form-select table-input select2-dynamic amt-type-select">
                              <option value="">-- Select Amount Type --</option>
                              <?php if (!empty($other_charges)): ?>
                                <?php foreach ($other_charges as $oc): ?>
                                  <option value="<?= $oc['id'] ?>" <?= ($selected_amt_type_id == $oc['id']) ? 'selected' : '' ?>><?= html_escape($oc['name']) ?></option>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </select>
                          </td>
                          <td>
                            <select name="ledger_amt_type[]" class="form-select table-input">
                              <option value="plus" <?= ($selected_amt_type == 'plus') ? 'selected' : '' ?>>Plus (+)</option>
                              <option value="minus" <?= ($selected_amt_type == 'minus') ? 'selected' : '' ?>>Minus (-)</option>
                            </select>
                          </td>
                          <td>
                            <input type="number" step="0.01" min="0.01" name="ledger_inr[]" class="form-control table-input mono-amount text-end" placeholder="0.00" value="<?= $amt_val ?>">
                          </td>
                          <td>
                            <input type="text" name="ledger_remark[]" class="form-control table-input" placeholder="Enter remark..." value="<?= html_escape($det['remark'] ?? '') ?>">
                          </td>
                          <td class="text-center">
                            <button type="button" class="btn-remove-row btn-remove-row-ledger" title="Delete / Clear Row">
                              <i class="feather icon-trash-2"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr class="adj-row">
                        <td class="text-center row-index fw-semibold text-muted fs-12">1</td>
                        <td>
                          <select name="ledger_customer_id[]" class="form-select select2-dynamic customer-select">
                            <option value="">-- Select Customer --</option>
                            <?php if (!empty($customers)): ?>
                              <?php foreach ($customers as $c): 
                                $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
                              ?>
                                <option value="<?= $c['id'] ?>"><?= html_escape($displayName) ?></option>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </select>
                          <div class="customer-ledger-info d-none"></div>
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
                          <input type="number" step="0.01" min="0.01" name="ledger_inr[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
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
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>

          <!-- Bottom Actions -->
          <div class="d-flex justify-content-end align-items-center gap-2 pt-3 mt-2 border-top">
            <a href="<?= base_url('inventory/customer-adjustment') ?>" class="btn btn-light px-3 py-1 fs-13" style="border-radius: 4px; font-weight: 500;">Cancel</a>
            <button type="submit" class="btn btn-primary px-3 py-1 fs-13" id="btnSubmit" style="border-radius: 4px; font-weight: 500;">
              <i class="feather icon-check me-1"></i> Update
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Template for dynamic clean rows in To Customers Tab -->
<template id="toCustRowTemplate">
  <tr class="adj-row">
    <td class="text-center row-index fw-semibold text-muted fs-12"></td>
    <td>
      <select name="customer_id[]" class="form-select select2-dynamic customer-select">
        <option value="">-- Select Customer --</option>
        <?php if (!empty($customers)): ?>
          <?php foreach ($customers as $c): 
            $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
          ?>
            <option value="<?= $c['id'] ?>"><?= html_escape($displayName) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="customer-ledger-info d-none"></div>
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
      <button type="button" class="btn-remove-row btn-remove-row-tocust" title="Delete / Clear Row">
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
      <select name="ledger_customer_id[]" class="form-select select2-dynamic customer-select">
        <option value="">-- Select Customer --</option>
        <?php if (!empty($customers)): ?>
          <?php foreach ($customers as $c): 
            $displayName = !empty($c['company_name']) ? $c['company_name'] : (!empty($c['owner_name']) ? $c['owner_name'] : ('Customer #' . $c['id']));
          ?>
            <option value="<?= $c['id'] ?>"><?= html_escape($displayName) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="customer-ledger-info d-none"></div>
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
      <input type="number" step="0.01" min="0.01" name="ledger_inr[]" class="form-control table-input mono-amount text-end" placeholder="0.00">
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
        var placeholderText = $(this).hasClass('customer-select') ? "-- Select Customer --" : "-- Select Amount Type --";
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
      $('#adjustment_mode').val('to_customers');
      initSelect2($('#tab-to-customers'));
      calculateTotals();
    }
  });

  // Master Add Row button in Header Bar (adds to currently active tab)
  $('#btnAddRowBtn').on('click', function() {
    var mode = $('#adjustment_mode').val();
    if (mode === 'ledger') {
      var html = $('#ledgerRowTemplate').html();
      var $newRow = $(html);
      $('#ledgerTableBody').append($newRow);
      initSelect2($newRow);
      updateRowIndices('#ledgerTableBody');
    } else {
      var html = $('#toCustRowTemplate').html();
      var $newRow = $(html);
      $('#toCustTableBody').append($newRow);
      initSelect2($newRow);
      updateRowIndices('#toCustTableBody');
      calculateTotals();
    }
  });

  function updateRowIndices(tableBodyId) {
    $(tableBodyId + ' tr').each(function(index) {
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

    $infoBox.removeClass('d-none').html('<span class="text-muted fs-10"><i class="feather icon-loader fa-spin"></i> Loading...</span>');

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
          $infoBox.html('<span class="text-muted fs-10">No ledger data</span>');
        }
      },
      error: function() {
        $infoBox.html('<span class="text-danger fs-10">Failed to load</span>');
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

  // Mutual exclusion: Debit vs Credit in To Customers Tab
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

  // Universal Responsive Remove / Clear Row Handler
  $(document).on('click', '.btn-remove-row, .btn-remove-row-tocust, .btn-remove-row-ledger', function(e) {
    e.preventDefault();
    var $row = $(this).closest('tr');
    var $tbody = $row.closest('tbody');
    var isLedger = $tbody.attr('id') === 'ledgerTableBody';

    if ($tbody.find('tr').length > 1) {
      $row.remove();
      if (isLedger) {
        updateRowIndices('#ledgerTableBody');
      } else {
        updateRowIndices('#toCustTableBody');
        calculateTotals();
      }
    } else {
      // Clear inputs if it's the only remaining row
      $row.find('.customer-select').val('').trigger('change');
      $row.find('.customer-ledger-info').empty().addClass('d-none');
      if (isLedger) {
        $row.find('.amt-type-select').val('').trigger('change');
        $row.find('select[name="ledger_amt_type[]"]').val('plus');
        $row.find('input[name="ledger_inr[]"]').val('');
        $row.find('input[name="ledger_remark[]"]').val('');
      } else {
        $row.find('.debit-inr').val('').prop('disabled', false).removeClass('table-input-disabled');
        $row.find('.credit-inr').val('').prop('disabled', false).removeClass('table-input-disabled');
        $row.find('input[name="remark[]"]').val('');
        calculateTotals();
      }
    }
  });

  // Calculate Totals for To Customers Tab
  function calculateTotals() {
    var totalDebit = 0;
    var totalCredit = 0;

    $('#toCustTableBody tr').each(function() {
      var deb = parseFloat($(this).find('.debit-inr').val()) || 0;
      var crd = parseFloat($(this).find('.credit-inr').val()) || 0;
      totalDebit += deb;
      totalCredit += crd;
    });

    $('#totalDebitINR').text('₹ ' + formatMoney(totalDebit));
    $('#totalCreditINR').text('₹ ' + formatMoney(totalCredit));

    var totalRows = $('#toCustTableBody tr').length;
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
    var dateVal = $('#date').val();
    if (!dateVal) {
      e.preventDefault();
      alert("Please select a date.");
      $('#date').focus();
      return false;
    }

    var mode = $('#adjustment_mode').val();

    if (mode === 'ledger') {
      $('#tab-to-customers').find('input, select').prop('disabled', true);
      $('#tab-ledger').find('input, select').prop('disabled', false);

      var validRows = 0;
      var hasError = false;
      var errorMessage = "";

      $('#ledgerTableBody tr').each(function(idx) {
        var rowNum = idx + 1;
        var cust = $(this).find('.customer-select').val();
        var amt = parseFloat($(this).find('input[name="ledger_inr[]"]').val()) || 0;

        if (!cust) {
          hasError = true;
          errorMessage = "Please select a customer for row #" + rowNum;
          return false;
        }

        if (amt <= 0) {
          hasError = true;
          errorMessage = "Please enter a valid amount greater than 0 for row #" + rowNum;
          return false;
        }

        validRows++;
      });

      if (hasError) {
        e.preventDefault();
        $('#tab-to-customers').find('input, select').prop('disabled', false);
        alert(errorMessage);
        return false;
      }

      if (validRows < 1) {
        e.preventDefault();
        $('#tab-to-customers').find('input, select').prop('disabled', false);
        alert("Please add at least one valid adjustment row.");
        return false;
      }

      return true;

    } else {
      $('#tab-ledger').find('input, select').prop('disabled', true);
      $('#tab-to-customers').find('input, select').prop('disabled', false);

      var validRows = 0;
      var totalDebit = 0;
      var totalCredit = 0;
      var hasError = false;
      var errorMessage = "";

      $('#toCustTableBody tr').each(function(idx) {
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
        var diff = Math.abs(totalDebit - totalCredit);
        if (diff >= 0.005) {
          e.preventDefault();
          $('#tab-ledger').find('input, select').prop('disabled', false);
          alert("Total Debit (₹ " + formatMoney(totalDebit) + ") must match Total Credit (₹ " + formatMoney(totalCredit) + ") when there are multiple entries.");
          return false;
        }
      }

      $('.debit-inr, .credit-inr').prop('disabled', false);
      return true;
    }
  });

});
</script>
