<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-2 my-0">
        <?php echo form_open('inventory/product_formula/edit_post/' . $id, ['class' => 'add-ajax-redirect-form', 'onsubmit' => 'return validateForm() && checkForm(this);']);?>
        <div class="row">
          <div class="col-12 col-sm-6 mb-1">
            <div class="form-group">
              <label class="form-label">Product Name</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($parent_product['name']) . ' (' . htmlspecialchars($parent_product['item_code']) . ')'; ?>" disabled>
              <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $id; ?>">
            </div>
          </div>
          <div class="col-12 col-sm-6 mb-1">
            <div class="form-group">
              <label class="form-label" for="expense">Expense</label>
              <input type="number" step="any" min="0" class="form-control" name="expense" id="expense" value="<?php echo floatval($parent_product['expense']); ?>" placeholder="Enter Expense">
            </div>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-12">
            <h6 class="mb-1">Formula Ingredients <span class="text-danger">*</span></h6>
            <div class="table-responsive">
              <table class="table table-bordered table-sm compact-table">
                <thead class="table-light text-center">
                  <tr>
                    <th style="min-width:300px;">Ingredient Product <span class="text-danger">*</span></th>
                    <th style="min-width:150px;">Quantity <span class="text-danger">*</span></th>
                    <th style="width:100px;">Action</th>
                  </tr>
                </thead>
                <tbody id="ingredient_area">
                  <!-- Dynamic rows pre-populated -->
                  <?php 
                  $index = 0;
                  foreach($formula_items as $item) { 
                    $index++;
                  ?>
                    <tr class="ingredient-row" id="row_<?php echo $index; ?>" data-id="<?php echo $index; ?>">
                      <td>
                        <select class="form-control select2 select-ingredient" name="product_id[]" id="ingredient_id_<?php echo $index; ?>" required style="width:100%">
                          <option value="">Select Ingredient</option>
                          <?php foreach($ingredient_products as $ing) { ?>
                            <option value="<?php echo $ing['id']; ?>" <?php if($ing['id'] == $item['product_id']) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($ing['name']) . ' (' . htmlspecialchars($ing['item_code']) . ')'; ?>
                            </option>
                          <?php } ?>
                        </select>
                      </td>
                      <td>
                        <input type="number" min="1" step="1" id="quantity_<?php echo $index; ?>" name="quantity[]" class="form-control text-center input-quantity" value="<?php echo intval($item['quantity']); ?>" required>
                      </td>
                      <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeIngredientRow(this)"><i class="fa fa-times"></i></button>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
            <div class="mt-1 mb-2">
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="appendIngredientRow()">
                <i class="fa fa-plus"></i> Add Ingredient
              </button>
            </div>
          </div>
        </div>

        <div class="row mt-1">
          <div class="col-12">
            <button type="submit" class="btn btn-primary waves-effect waves-float waves-light me-1 btn_verify" name="btn_verify"><?php echo get_phrase('submit'); ?></button>
            <a href="<?php echo base_url('inventory/product-formula'); ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
var nextindex = <?php echo $index; ?>;
var ingredientProducts = <?php echo json_encode($ingredient_products); ?>;

function updateIngredientOptions() {
  var parentId = $('#parent_id').val();
  
  // 1. Gather all selected ingredient values
  var selected = [];
  $('.select-ingredient').each(function() {
    var val = $(this).val();
    if (val) {
      selected.push(val);
    }
  });

  // 2. Disable options in each select dropdown that are selected elsewhere
  $('.select-ingredient').each(function() {
    var $select = $(this);
    var currentValue = $select.val();
    
    $select.find('option').each(function() {
      var optVal = $(this).val();
      if (!optVal) return; // Skip empty placeholder option
      
      var shouldDisable = false;
      
      // Disable if selected in another row
      if (selected.includes(optVal) && optVal !== currentValue) {
        shouldDisable = true;
      }
      
      // Disable if it matches parent product ID
      if (parentId && optVal == parentId) {
        shouldDisable = true;
      }
      
      $(this).prop('disabled', shouldDisable);
    });
    
    // Refresh Select2 rendering
    $select.trigger('change.select2');
  });
}

function appendIngredientRow() {
  nextindex++;
  
  var optionsHtml = '<option value="">Select Ingredient</option>';
  $.each(ingredientProducts, function(i, prod) {
    optionsHtml += '<option value="' + prod.id + '">' + escapeHtml(prod.name) + ' (' + escapeHtml(prod.item_code) + ')</option>';
  });

  var newRowHtml = `
    <tr class="ingredient-row" id="row_${nextindex}" data-id="${nextindex}">
      <td>
        <select class="form-control select2 select-ingredient" name="product_id[]" id="ingredient_id_${nextindex}" required style="width:100%">
          ${optionsHtml}
        </select>
      </td>
      <td>
        <input type="number" min="1" step="1" id="quantity_${nextindex}" name="quantity[]" class="form-control text-center input-quantity" value="1" required>
      </td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-danger btn-sm" onclick="removeIngredientRow(this)"><i class="fa fa-times"></i></button>
      </td>
    </tr>
  `;

  $('#ingredient_area').append(newRowHtml);
  $('#ingredient_id_' + nextindex).select2();
  updateIngredientOptions();
}

function removeIngredientRow(button) {
  $(button).closest('tr').remove();
  updateIngredientOptions();
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

function validateForm() {
  var parentId = $('#parent_id').val();
  if (!parentId) {
    Swal.fire({
      title: "Error!",
      text: "Invalid parent product ID.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  var selectedIngredients = [];
  var hasDuplicate = false;
  var hasParentAsIngredient = false;
  var hasInvalidQty = false;
  var count = 0;

  $('.ingredient-row').each(function() {
    var rowId = $(this).attr('data-id');
    var ingId = $('#ingredient_id_' + rowId).val();
    var qty = parseInt($('#quantity_' + rowId).val()) || 0;

    if (ingId) {
      count++;
      if (ingId == parentId) {
        hasParentAsIngredient = true;
      }
      if (selectedIngredients.includes(ingId)) {
        hasDuplicate = true;
      }
      selectedIngredients.push(ingId);

      if (qty <= 0) {
        hasInvalidQty = true;
      }
    }
  });

  if (count < 2) {
    Swal.fire({
      title: "Error!",
      text: "You must add at least 2 ingredients in the formula.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  if (hasParentAsIngredient) {
    Swal.fire({
      title: "Error!",
      text: "A product cannot be added as its own ingredient.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  if (hasDuplicate) {
    Swal.fire({
      title: "Error!",
      text: "Duplicate ingredients are not allowed in the formula list.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  if (hasInvalidQty) {
    Swal.fire({
      title: "Error!",
      text: "Ingredient quantities must be greater than 0.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  return true;
}

$(document).ready(function() {
  // Initialize select2 on pre-populated selects
  $('.select-ingredient').select2();

  // Listen to ingredient selection changes
  $(document).on('change', '.select-ingredient', function() {
    updateIngredientOptions();
  });

  // Run once initially to disable pre-populated options
  updateIngredientOptions();
});
</script>
