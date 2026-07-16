<div class="row">
  <div class="col-12">
    <div class="card shadow-sm border-0">
     
      <div class="card-body py-2">
        <?php echo form_open('inventory/formula-product-order/add_post', ['id' => 'add_formula_product_order_form', 'class' => 'add-ajax-redirect-form', 'onsubmit' => 'return validateProductionForm();']); ?>
        
        <!-- Header Section -->
        <div class="row">
          <div class="col-12 col-md-4 mb-1">
            <div class="form-group">
              <label class="form-label font-weight-bold" for="product_id">Formula Product <span class="text-danger">*</span></label>
              <select class="form-select select2" name="product_id" id="product_id" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p['id']; ?>">
                    <?= htmlspecialchars($p['name']) . ' (' . htmlspecialchars($p['item_code']) . ')'; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-4 mb-1">
            <div class="form-group">
              <label class="form-label font-weight-bold" for="warehouse_id">Warehouse <span class="text-danger">*</span></label>
              <select class="form-select select2" name="warehouse_id" id="warehouse_id" required>
                <option value="">Select Warehouse</option>
                <?php foreach ($warehouse_list as $w): ?>
                  <option value="<?= $w['id']; ?>">
                    <?= htmlspecialchars($w['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-4 mb-1">
            <div class="form-group">
              <label class="form-label font-weight-bold" for="batch_no">Batch No <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="batch_no" id="batch_no" required placeholder="Enter batch number for produced product">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label font-weight-bold" for="quantity">Quantity <span class="text-danger">*</span></label>
              <input type="number" min="1" step="1" class="form-control font-weight-bold text-center" name="quantity" id="quantity" value="1" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label font-weight-bold" for="type">Type <span class="text-danger">*</span></label>
              <select class="form-select select2" name="type" id="type" required>
                <option value="white">Official (White)</option>
                <option value="black">Unofficial (Black)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row mb-1">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label font-weight-bold" for="remark">Remark</label>
              <textarea class="form-control" name="remark" id="remark" rows="2" placeholder="Enter remark"></textarea>
            </div>
          </div>
        </div>

        <!-- Ingredients Stock Allocation -->
        <div class="row" id="ingredients_wrapper" style="display:none;">
          <div class="col-12">
            <div class="divider divider-left divider-primary mb-1">
              <div class="divider-text text-primary font-weight-bold"><i class="feather icon-package"></i> Ingredients Stock Allocation</div>
            </div>
            <div class="table-responsive border rounded mb-2">
              <table class="table table-bordered table-sm mb-0">
                <thead class="table-light text-center">
                  <tr>
                    <th>Ingredient Product</th>
                    <th style="width:160px;">Available Qty (B/W)</th>
                    <th style="width:120px;">Qty Per Pc</th>
                    <th style="width:120px;">Required Qty</th>
                    <th style="width:120px;">Allocation Status</th>
                    <th style="width:130px;">Action</th>
                  </tr>
                </thead>
                <tbody id="ingredients_container">
                  <!-- Dynamic ingredients will load here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Expense Section -->
        <div class="row mt-1">
          <div class="col-12 col-lg-7">
            <div class="divider divider-left divider-secondary mb-1">
              <div class="divider-text text-secondary font-weight-bold"><i class="feather icon-dollar-sign"></i> Expenses / Charges</div>
            </div>
            <div class="table-responsive border rounded mb-1">
              <table class="table table-bordered table-sm mb-0">
                <thead class="table-light text-center">
                  <tr>
                    <th>Expense Name <span class="text-danger">*</span></th>
                    <th style="width:200px;">Amount <span class="text-danger">*</span></th>
                    <th style="width:80px;">Action</th>
                  </tr>
                </thead>
                <tbody id="expense_tbody">
                  <!-- Expenses will load here -->
                </tbody>
              </table>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="addExpenseRow()">
              <i class="fa fa-plus"></i> Add Additional Expense
            </button>
          </div>

          <!-- Grand Total Summary Section -->
          <div class="col-12 col-lg-5">
            <div class="divider divider-left divider-primary mb-1">
              <div class="divider-text text-primary font-weight-bold"><i class="feather icon-info"></i> Grand Total Summary</div>
            </div>
            <div class="card shadow-none mb-2" style="background-color: #fafbfc; border: 1px solid #d8d6de; border-top: 3px solid #7367f0 !important;">
              <div class="card-body p-2">
                <div class="row mb-50">
                  <div class="col-6 font-weight-bold text-end">Total Official Cost:</div>
                  <div class="col-6">
                    <input type="number" step="any" class="form-control form-control-sm text-end" id="grand_total_off_cost" name="total_off_cost" value="0.00" readonly>
                  </div>
                </div>
                <div class="row mb-50">
                  <div class="col-6 font-weight-bold text-end">Total Black Cost:</div>
                  <div class="col-6">
                    <input type="number" step="any" class="form-control form-control-sm text-end" id="grand_total_black_cost" name="total_black_cost" value="0.00" readonly>
                  </div>
                </div>
                <div class="row mb-50">
                  <div class="col-6 font-weight-bold text-end">Total Actual Cost:</div>
                  <div class="col-6">
                    <input type="number" step="any" class="form-control form-control-sm text-end font-weight-bold" id="grand_total_actual_cost" name="total_actual_cost" value="0.00" readonly>
                  </div>
                </div>
                <div class="row mb-50">
                  <div class="col-6 font-weight-bold text-end">Off Cost Per Qty:</div>
                  <div class="col-6">
                    <input type="number" step="any" class="form-control form-control-sm text-end" id="grand_off_cost_pc" name="off_cost_pc" value="0.00" readonly>
                  </div>
                </div>
                <div class="row mb-50">
                  <div class="col-6 font-weight-bold text-end">Actual Cost Per Qty:</div>
                  <div class="col-6">
                    <input type="number" step="any" class="form-control form-control-sm text-end" id="grand_actual_cost_pc" name="actual_cost_pc" value="0.00" readonly>
                  </div>
                </div>
                <div class="row mb-50">
                  <div class="col-6 font-weight-bold text-end">Total Expense:</div>
                  <div class="col-6">
                    <input type="number" step="any" class="form-control form-control-sm text-end" id="grand_total_expense" name="total_expense" value="0.00" readonly>
                  </div>
                </div>
                <hr class="my-50">
                <div class="row">
                  <div class="col-6 text-primary font-weight-bold text-end align-middle pt-25">Final Total:</div>
                  <div class="col-6">
                    <input type="number" step="any" class="form-control form-control-sm text-end font-weight-bold text-primary" style="background-color: rgba(115, 103, 240, 0.08); border-color: #7367f0;" id="grand_final_total" name="final_total" value="0.00" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Submission Buttons -->
        <div class="row mt-2">
          <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary btn_verify waves-effect waves-float waves-light me-1" name="btn_verify">Submit Production</button>
            <a href="<?php echo base_url('inventory/formula-product-order'); ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>

        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
var ingredientList = [];
var expenseTypes = <?php echo json_encode($expenses ?? []); ?>;

function loadIngredientsAndExpenses() {
  var productId = $('#product_id').val();
  var warehouseId = $('#warehouse_id').val();

  if (!productId || !warehouseId) {
    $('#ingredients_wrapper').hide();
    $('#ingredients_container').html('');
    $('#expense_tbody').html('');
    recalculateAll();
    return;
  }

  $('#ingredients_container').html('<tr><td colspan="6" class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading formula ingredients and stock details...</td></tr>');
  $('#ingredients_wrapper').show();

  $.ajax({
    url: '<?= base_url("inventory/formula-product-order/get_ingredients"); ?>',
    type: 'POST',
    dataType: 'json',
    data: {
      product_id: productId,
      warehouse_id: warehouseId
    },
    success: function(res) {
      if (res.status == 200) {
        ingredientList = res.ingredients;
        
        // 1. Build Ingredients table
        var tbodyHtml = '';
        if (ingredientList.length === 0) {
          tbodyHtml = '<tr><td colspan="6" class="text-center py-2 text-danger">No ingredients found for this product formula.</td></tr>';
        } else {
          $.each(ingredientList, function(i, ing) {
            tbodyHtml += `
              <tr class="ingredient-header-row" id="ing_row_${ing.product_id}" data-id="${ing.product_id}" data-qty-pc="${ing.qty_per_pc}">
                <td>
                  <span class="font-weight-bold text-dark">${escapeHtml(ing.name)}</span><br>
                  <small class="text-muted">SKU: ${escapeHtml(ing.item_code)}</small>
                </td>
                <td class="text-center">
                  <div class="d-flex flex-column align-items-center">
                    <span class="badge bg-light-primary text-primary mb-25" style="font-size:0.75rem;">Off: <b>${ing.available_white}</b></span>
                    <span class="badge bg-light-secondary text-secondary" style="font-size:0.75rem;">Blk: <b>${ing.available_black}</b></span>
                  </div>
                </td>
                <td class="text-center font-monospace align-middle">${ing.qty_per_pc}</td>
                <td class="text-center font-monospace align-middle font-weight-bold required-qty-cell">0</td>
                <td class="text-center align-middle font-weight-bold status-cell">
                  <span class="badge bg-light-warning text-warning">Pending</span>
                </td>
                <td class="text-center align-middle">
                  <button type="button" class="btn btn-outline-primary btn-sm" onclick="addBatchRow(${ing.product_id})">
                    <i class="fa fa-plus"></i> Add Batch
                  </button>
                </td>
              </tr>
              <tr id="ing_batches_row_${ing.product_id}">
                <td colspan="6" class="p-1" style="background-color: #fafbfc;">
                  <div class="card shadow-none m-0 border" style="border-color: #d8d6de !important; background-color: #ffffff; border-radius: 0px !important;">
                    <div class="card-body p-50 bg-white">
                      <table class="table table-bordered table-sm mb-0 compact-table" style="background:#fff;">
                        <thead class="text-center" style="font-size:0.8rem; background-color: #f3f2f7; color: #5e5873;">
                          <tr>
                            <th>Batch No. <span class="text-danger">*</span></th>
                            <th style="width:120px;">Off. Qty</th>
                            <th style="width:120px;">Blk. Qty</th>
                            <th style="width:100px;">Off. Cost</th>
                            <th style="width:110px;">Total Off. Cost</th>
                            <th style="width:100px;">Blk. Cost</th>
                            <th style="width:110px;">Total Blk. Cost</th>
                            <th style="width:120px;">Actual Cost</th>
                            <th style="width:50px;"></th>
                          </tr>
                        </thead>
                        <tbody id="batches_container_${ing.product_id}">
                          <!-- Dynamic batch rows go here -->
                        </tbody>
                      </table>
                    </div>
                  </div>
                </td>
              </tr>
            `;
          });
        }
        $('#ingredients_container').html(tbodyHtml);

        // 2. Build default expenses
        var expHtml = '';
        if (res.expenses && res.expenses.length > 0) {
          $.each(res.expenses, function(i, exp) {
            var randomId = Math.floor(Math.random() * 1000000);
            expHtml += createExpenseRowMarkup(exp.expense_id, exp.amount, randomId);
          });
        }
        $('#expense_tbody').html(expHtml);
        $('#expense_tbody .select-charge-expense').select2();
        updateExpenseSelectOptions();

        updateRequiredQuantities();
        recalculateAll();
      } else {
        Swal.fire({ title: "Error!", text: res.message, icon: "error" });
        $('#ingredients_container').html('<tr><td colspan="6" class="text-center text-danger py-2">Failed to load.</td></tr>');
      }
    },
    error: function() {
      Swal.fire({ title: "Error!", text: "Could not fetch formula details.", icon: "error" });
      $('#ingredients_container').html('<tr><td colspan="6" class="text-center text-danger py-2">Failed to load.</td></tr>');
    }
  });
}

function updateRequiredQuantities() {
  var parentQty = parseInt($('#quantity').val()) || 0;
  $('.ingredient-header-row').each(function() {
    var qtyPc = parseInt($(this).data('qty-pc')) || 0;
    var required = qtyPc * parentQty;
    $(this).find('.required-qty-cell').text(required);
  });
}

function addBatchRow(ingId) {
  var ing = ingredientList.find(x => x.product_id == ingId);
  if (!ing) return;

  var batchOptionsHtml = '<option value="">Select Batch</option>';
  $.each(ing.batches, function(i, batch) {
    batchOptionsHtml += `<option value="${batch.id}" data-white="${batch.av_white}" data-black="${batch.av_black}" data-off-cost="${batch.official_rate}" data-black-cost="${batch.black_rate}">${escapeHtml(batch.batch_no)}</option>`;
  });

  var randomId = Math.floor(Math.random() * 1000000);
  var rowHtml = `
    <tr class="batch-row" id="batch_row_${randomId}" data-ing-id="${ingId}">
      <td>
        <select class="form-select form-select-sm select-batch" name="batch_id[${ingId}][]" onchange="onBatchSelectChange(this)" required style="min-width: 140px;">
          ${batchOptionsHtml}
        </select>
        <div class="mt-25 text-start font-small-2 px-25 text-muted batch-stock-info" style="display:none;">
          Avail - Off: <span class="avail-white-span font-weight-bold text-primary">0</span>, Blk: <span class="avail-black-span font-weight-bold text-secondary">0</span>
        </div>
      </td>
      <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm text-center white-qty-input" name="white_qty[${ingId}][]" value="0" onkeyup="onBatchQtyChange(this)" onchange="onBatchQtyChange(this)" disabled required>
      </td>
      <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm text-center black-qty-input" name="black_qty[${ingId}][]" value="0" onkeyup="onBatchQtyChange(this)" onchange="onBatchQtyChange(this)" disabled required>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm text-center off-cost-input font-monospace" style="background-color: #fafbfc; border-color: #d8d6de;" value="0.00" readonly>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm text-center total-off-cost-input font-monospace" style="background-color: #fafbfc; border-color: #d8d6de;" value="0.00" readonly>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm text-center black-cost-input font-monospace" style="background-color: #fafbfc; border-color: #d8d6de;" value="0.00" readonly>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm text-center total-black-cost-input font-monospace" style="background-color: #fafbfc; border-color: #d8d6de;" value="0.00" readonly>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm text-center actual-cost-input font-monospace font-weight-bold text-primary" style="background-color: rgba(115, 103, 240, 0.08); border-color: #7367f0;" value="0.00" readonly>
      </td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-flat-danger btn-sm p-25" onclick="removeBatchRow(this, ${ingId})">
          <i class="fa fa-times"></i>
        </button>
      </td>
    </tr>
  `;

  $(`#batches_container_${ingId}`).append(rowHtml);
  $(`#batch_row_${randomId} .select-batch`).select2();
  updateBatchSelectOptions(ingId);
  if ($('#type').val() === 'black') {
    $(`#batch_row_${randomId} .white-qty-input`).prop('readonly', true).val(0);
  }
}

function removeBatchRow(button, ingId) {
  $(button).closest('tr').remove();
  updateBatchSelectOptions(ingId);
  recalculateAll();
}

function updateBatchSelectOptions(ingId) {
  var selected = [];
  $(`#batches_container_${ingId} .select-batch`).each(function() {
    var val = $(this).val();
    if (val) {
      selected.push(val);
    }
  });

  $(`#batches_container_${ingId} .select-batch`).each(function() {
    var $select = $(this);
    var currentValue = $select.val();
    
    $select.find('option').each(function() {
      var optVal = $(this).val();
      if (!optVal) return;
      
      if (selected.includes(optVal) && optVal !== currentValue) {
        $(this).prop('disabled', true);
      } else {
        $(this).prop('disabled', false);
      }
    });
    // Trigger update on select2
    $select.trigger('change.select2');
  });
}

var isUpdatingBatch = false;

function onBatchSelectChange(select) {
  if (isUpdatingBatch) return;
  isUpdatingBatch = true;

  var $row = $(select).closest('tr');
  var $option = $(select).find('option:selected');
  var ingId = $row.data('ing-id');

  if (!$(select).val()) {
    $row.find('.batch-stock-info').hide();
    $row.find('.white-qty-input, .black-qty-input').prop('disabled', true).val(0);
    $row.find('.off-cost-input, .total-off-cost-input, .black-cost-input, .total-black-cost-input, .actual-cost-input').val('0.00');
    updateBatchSelectOptions(ingId);
    recalculateAll();
    isUpdatingBatch = false;
    return;
  }

  var whiteStock = parseInt($option.data('white')) || 0;
  var blackStock = parseInt($option.data('black')) || 0;
  var offCost = parseFloat($option.data('off-cost')) || 0;
  var blackCost = parseFloat($option.data('black-cost')) || 0;

  $row.find('.avail-white-span').text(whiteStock);
  $row.find('.avail-black-span').text(blackStock);
  $row.find('.batch-stock-info').show();

  $row.find('.off-cost-input').val(offCost.toFixed(2));
  $row.find('.black-cost-input').val(blackCost.toFixed(2));

  $row.find('.white-qty-input, .black-qty-input').prop('disabled', false);
  if ($('#type').val() === 'black') {
    $row.find('.white-qty-input').prop('readonly', true).val(0);
  } else {
    $row.find('.white-qty-input').prop('readonly', false);
  }

  // Set default allocated values (max possible but not exceeding required remaining)
  var requiredTotal = parseInt($(`#ing_row_${ingId}`).find('.required-qty-cell').text()) || 0;
  var currentAllocated = 0;
  $(`#batches_container_${ingId} .batch-row`).each(function() {
    if (this !== $row[0]) {
      var w = parseInt($(this).find('.white-qty-input').val()) || 0;
      var b = parseInt($(this).find('.black-qty-input').val()) || 0;
      currentAllocated += (w + b);
    }
  });

  var remaining = Math.max(0, requiredTotal - currentAllocated);
  
  // Allocate white first (only if type is white)
  var w_to_alloc = 0;
  if ($('#type').val() !== 'black') {
    w_to_alloc = Math.min(remaining, whiteStock);
  }
  remaining -= w_to_alloc;
  var b_to_alloc = Math.min(remaining, blackStock);

  $row.find('.white-qty-input').val(w_to_alloc);
  $row.find('.black-qty-input').val(b_to_alloc);

  updateBatchSelectOptions(ingId);
  calculateBatchCosts($row);
  recalculateAll();
  isUpdatingBatch = false;
}

function onBatchQtyChange(input) {
  var $row = $(input).closest('tr');
  var $option = $row.find('.select-batch option:selected');
  var ingId = $row.data('ing-id');

  var whiteStock = parseInt($option.data('white')) || 0;
  var blackStock = parseInt($option.data('black')) || 0;

  var val = parseInt($(input).val());
  if (isNaN(val) || val < 0) {
    $(input).val(0);
    val = 0;
  }

  // Rule 4: Don't allow negative value
  if ($(input).hasClass('white-qty-input')) {
    if (val > whiteStock) {
      $(input).val(whiteStock);
      Swal.fire({
        title: "Limit Exceeded",
        text: `Allocated white qty cannot exceed batch available stock (${whiteStock})`,
        icon: "warning"
      });
    }
  } else {
    if (val > blackStock) {
      $(input).val(blackStock);
      Swal.fire({
        title: "Limit Exceeded",
        text: `Allocated black qty cannot exceed batch available stock (${blackStock})`,
        icon: "warning"
      });
    }
  }

  calculateBatchCosts($row);
  recalculateAll();
}

function calculateBatchCosts($row) {
  var wQty = parseInt($row.find('.white-qty-input').val()) || 0;
  var bQty = parseInt($row.find('.black-qty-input').val()) || 0;
  var offCost = parseFloat($row.find('.off-cost-input').val()) || 0;
  var blackCost = parseFloat($row.find('.black-cost-input').val()) || 0;

  var totalOff = wQty * offCost;
  var totalBlack = bQty * blackCost;
  var actualCost = totalOff + totalBlack;

  $row.find('.total-off-cost-input').val(totalOff.toFixed(2));
  $row.find('.total-black-cost-input').val(totalBlack.toFixed(2));
  $row.find('.actual-cost-input').val(actualCost.toFixed(2));
}

function recalculateAll() {
  var totalQty = parseInt($('#quantity').val()) || 0;
  
  // 1. Calculate allocated and update status cell for each ingredient
  $('.ingredient-header-row').each(function() {
    var ingId = $(this).data('id');
    var required = parseInt($(this).find('.required-qty-cell').text()) || 0;
    
    var allocated = 0;
    $(`#batches_container_${ingId} .batch-row`).each(function() {
      var w = parseInt($(this).find('.white-qty-input').val()) || 0;
      var b = parseInt($(this).find('.black-qty-input').val()) || 0;
      allocated += (w + b);
    });

    var $statusCell = $(this).find('.status-cell');
    if (allocated === 0 && required > 0) {
      $statusCell.html(`<span class="badge bg-light-warning text-warning">Pending (0/${required})</span>`);
    } else if (allocated < required) {
      $statusCell.html(`<span class="badge bg-light-info text-info">Partially Allocated (${allocated}/${required})</span>`);
    } else if (allocated === required) {
      $statusCell.html(`<span class="badge bg-light-success text-success">Completed (${allocated}/${required})</span>`);
    } else {
      $statusCell.html(`<span class="badge bg-light-danger text-danger">Over Allocated (${allocated}/${required})</span>`);
    }
  });

  // 2. Sum up all batch costs
  var grandOffCost = 0;
  var grandBlackCost = 0;
  var grandActualCost = 0;

  $('.batch-row').each(function() {
    var off = parseFloat($(this).find('.total-off-cost-input').val()) || 0;
    var blk = parseFloat($(this).find('.total-black-cost-input').val()) || 0;
    var act = parseFloat($(this).find('.actual-cost-input').val()) || 0;
    grandOffCost += off;
    grandBlackCost += blk;
    grandActualCost += act;
  });

  // 3. Sum up all expenses
  var grandExpense = 0;
  $('.expense-amount-input').each(function() {
    grandExpense += parseFloat($(this).val()) || 0;
  });

  // 4. Update grand total fields
  $('#grand_total_off_cost').val(grandOffCost.toFixed(2));
  $('#grand_total_black_cost').val(grandBlackCost.toFixed(2));
  $('#grand_total_actual_cost').val(grandActualCost.toFixed(2));
  $('#grand_total_expense').val(grandExpense.toFixed(2));

  if (totalQty > 0) {
    $('#grand_off_cost_pc').val((grandOffCost / totalQty).toFixed(2));
    $('#grand_actual_cost_pc').val((grandActualCost / totalQty).toFixed(2));
  } else {
    $('#grand_off_cost_pc').val('0.00');
    $('#grand_actual_cost_pc').val('0.00');
  }

  $('#grand_final_total').val((grandActualCost + grandExpense).toFixed(2));
}

function updateExpenseSelectOptions() {
  var selected = [];
  $('.select-charge-expense').each(function() {
    var val = $(this).val();
    if (val) {
      selected.push(val);
    }
  });

  $('.select-charge-expense').each(function() {
    var $select = $(this);
    var currentValue = $select.val();
    
    $select.find('option').each(function() {
      var optVal = $(this).val();
      if (!optVal) return;
      
      var shouldDisable = false;
      if (selected.includes(optVal) && optVal !== currentValue) {
        shouldDisable = true;
      }
      
      $(this).prop('disabled', shouldDisable);
    });
    
    $select.trigger('change.select2');
  });
}

function addExpenseRow() {
  var randomId = Math.floor(Math.random() * 1000000);
  var markup = createExpenseRowMarkup('', '0.00', randomId);
  $('#expense_tbody').append(markup);
  $('#charge_expense_id_' + randomId).select2();
  updateExpenseSelectOptions();
}

function removeExpenseRow(button) {
  $(button).closest('tr').remove();
  updateExpenseSelectOptions();
  recalculateAll();
}

function createExpenseRowMarkup(selectedExpenseId = '', amount = '', rowId = '') {
  var randomId = rowId || Math.floor(Math.random() * 1000000);
  
  var optionsHtml = '<option value="">Select Expense Type</option>';
  $.each(expenseTypes, function(i, exp) {
    var selected = (selectedExpenseId && exp.id == selectedExpenseId) ? 'selected' : '';
    optionsHtml += '<option value="' + exp.id + '" ' + selected + '>' + escapeHtml(exp.name) + '</option>';
  });

  return `
    <tr class="expense-row" id="expense_row_${randomId}">
      <td>
        <select class="form-control form-control-sm select2 select-charge-expense" name="charge_expense_id[]" id="charge_expense_id_${randomId}" required style="width:100%">
          ${optionsHtml}
        </select>
      </td>
      <td>
        <input type="number" step="any" min="0" class="form-control form-control-sm text-end expense-amount-input" name="charge_amount[]" value="${parseFloat(amount || 0).toFixed(2)}" onkeyup="onExpenseAmountChange(this)" onchange="onExpenseAmountChange(this)" required>
      </td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-flat-danger btn-sm p-25" onclick="removeExpenseRow(this)">
          <i class="fa fa-times"></i>
        </button>
      </td>
    </tr>
  `;
}

function onExpenseAmountChange(input) {
  var val = parseFloat($(input).val());
  if (isNaN(val) || val < 0) {
    $(input).val('0.00');
  }
  recalculateAll();
}

function escapeHtml(text) {
  if (!text) return '';
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

function validateProductionForm() {
  var productId = $('#product_id').val();
  var warehouseId = $('#warehouse_id').val();
  var batchNo = $('#batch_no').val().trim();
  var quantity = parseInt($('#quantity').val()) || 0;

  if (!productId) {
    Swal.fire({ title: "Validation Error", text: "Please select a formula product.", icon: "error" });
    return false;
  }
  if (!warehouseId) {
    Swal.fire({ title: "Validation Error", text: "Please select a warehouse.", icon: "error" });
    return false;
  }
  if (!batchNo) {
    Swal.fire({ title: "Validation Error", text: "Please enter a batch number.", icon: "error" });
    return false;
  }
  if (quantity <= 0) {
    Swal.fire({ title: "Validation Error", text: "Quantity must be greater than 0.", icon: "error" });
    return false;
  }

  // Verify all ingredients allocation matching required qty exactly (Rule 3)
  var allValid = true;
  var validationMsg = '';

  $('.ingredient-header-row').each(function() {
    var ingId = $(this).data('id');
    var ingName = $(this).find('.text-dark').text();
    var required = parseInt($(this).find('.required-qty-cell').text()) || 0;

    var allocated = 0;
    $(`#batches_container_${ingId} .batch-row`).each(function() {
      var w = parseInt($(this).find('.white-qty-input').val()) || 0;
      var b = parseInt($(this).find('.black-qty-input').val()) || 0;
      allocated += (w + b);
    });

    if (allocated !== required) {
      allValid = false;
      validationMsg = `Stock allocated for ingredient '${ingName}' (${allocated}) does not match Required Qty (${required}). All ingredients must be fully allocated.`;
      return false; // Break loop
    }
  });

  if (!allValid) {
    Swal.fire({ title: "Allocation Incomplete", text: validationMsg, icon: "warning" });
    return false;
  }

  var selectedExpenses = [];
  var hasDuplicateExpense = false;
  var hasInvalidExpenseAmount = false;

  $('.expense-row').each(function() {
    var expenseId = $(this).find('.select-charge-expense').val();
    var amount = parseFloat($(this).find('.expense-amount-input').val()) || 0;

    if (expenseId) {
      if (selectedExpenses.includes(expenseId)) {
        hasDuplicateExpense = true;
      }
      selectedExpenses.push(expenseId);
      if (amount <= 0) {
        hasInvalidExpenseAmount = true;
      }
    }
  });

  if (hasDuplicateExpense) {
    Swal.fire({
      title: "Validation Error",
      text: "Duplicate expenses are not allowed in the charges list.",
      icon: "error"
    });
    return false;
  }

  if (hasInvalidExpenseAmount) {
    Swal.fire({
      title: "Validation Error",
      text: "Expense amounts must be greater than 0.",
      icon: "error"
    });
    return false;
  }

  return true;
}

function handleTypeRestriction() {
  var type = $('#type').val();
  if (type === 'black') {
    $('.white-qty-input').prop('readonly', true).val(0);
    $('.white-qty-input').each(function() {
      calculateBatchCosts($(this).closest('tr'));
    });
    recalculateAll();
  } else {
    $('.white-qty-input').each(function() {
      if (!$(this).prop('disabled')) {
        $(this).prop('readonly', false);
      }
    });
  }
}

$(document).ready(function() {
  // Initialize dynamic components
  $('#product_id').select2();
  $('#warehouse_id').select2();
  $('#type').select2({ minimumResultsForSearch: Infinity });

  // Handle selections
  $('#product_id, #warehouse_id').on('change', function() {
    loadIngredientsAndExpenses();
  });

  // Handle type change
  $('#type').on('change', function() {
    handleTypeRestriction();
  });

  // Listen to expense selection changes
  $(document).on('change', '.select-charge-expense', function() {
    updateExpenseSelectOptions();
  });

  // Handle total quantity changes
  $('#quantity').on('input change', function() {
    var val = parseInt($(this).val());
    if (isNaN(val) || val <= 0) {
      $(this).val(1);
    }
    updateRequiredQuantities();
    recalculateAll();
  });
});
</script>
