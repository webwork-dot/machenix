<?php
$warehouse_id = (int)$param2;

// Fetch all local products that have has_formula = 1
$parent_products = $this->db->where(array('product_type' => 'local', 'has_formula' => 1, 'is_deleted' => 0))
                            ->order_by('name', 'ASC')
                            ->get('raw_products')
                            ->result_array();
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/add_product_stock_post', ['id' => 'add_product_stock_form']); ?>
      <input type="hidden" name="warehouse_id" value="<?= $warehouse_id; ?>">

      <div class="row">
        <div class="col-12 col-sm-6 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_batch_no">Batch No. <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="batch_no" id="modal_batch_no" required placeholder="Enter Batch Number">
          </div>
        </div>

        <div class="col-12 col-sm-6 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_product_id">Product <span class="text-danger">*</span></label>
            <select class="form-control select2" name="product_id" id="modal_product_id" required style="width:100%;">
              <option value="">Select Product</option>
              <?php foreach ($parent_products as $p): ?>
                <option value="<?= $p['id']; ?>" data-expense="<?= $p['expense']; ?>">
                  <?= htmlspecialchars($p['name']) . ' (' . htmlspecialchars($p['item_code']) . ')'; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="row mt-1">
        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_quantity">Quantity <span class="text-danger">*</span></label>
            <input type="number" min="1" step="1" class="form-control text-center" name="quantity" id="modal_quantity" value="1" required>
          </div>
        </div>

        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_expense">Expense <span class="text-danger">*</span></label>
            <input type="number" step="any" min="0" class="form-control text-center" name="expense" id="modal_expense" value="0.00" required>
          </div>
        </div>

        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_type">Type <span class="text-danger">*</span></label>
            <select class="form-control select2" name="type" id="modal_type" required style="width:100%;">
              <option value="white">White</option>
              <option value="black">Black</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Dynamic Ingredients Check Table -->
      <div class="row mt-2" id="ingredients_section" style="display:none;">
        <div class="col-12">
          <h6 class="mb-1 text-primary">Ingredients Stock Verification</h6>
          <div class="table-responsive">
            <table class="table table-bordered table-sm compact-table">
              <thead class="table-light text-center">
                <tr>
                  <th>Ingredient</th>
                  <th style="width: 100px;">Req. Qty (1 Pc)</th>
                  <th style="width: 120px;">Total Req. Qty</th>
                  <th style="width: 120px;">Available Stock</th>
                  <th style="width: 100px;">Status</th>
                </tr>
              </thead>
              <tbody id="modal_ingredients_tbody">
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary btn_verify waves-effect waves-float waves-light me-1" name="btn_verify" id="submit_production_btn">Submit</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    <?php echo form_close(); ?>
  </div>
</div>

<script>
var allIngredientsSufficient = true;

function loadIngredientsStatus() {
  var productId = $('#modal_product_id').val();
  var quantity = parseInt($('#modal_quantity').val()) || 0;
  var type = $('#modal_type').val();

  if (!productId || quantity <= 0) {
    $('#ingredients_section').hide();
    return;
  }

  $('#modal_ingredients_tbody').html('<tr><td colspan="5" class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>');
  $('#ingredients_section').show();

  $.ajax({
    url: '<?= base_url("inventory/get_formula_ingredients_status"); ?>',
    type: 'POST',
    dataType: 'json',
    data: {
      parent_id: productId,
      quantity: quantity,
      type: type,
      warehouse_id: <?= $warehouse_id; ?>
    },
    success: function(res) {
      if (res.status == 200) {
        var tbodyHtml = '';
        allIngredientsSufficient = res.all_sufficient;

        if (res.ingredients.length === 0) {
          tbodyHtml = '<tr><td colspan="5" class="text-center text-muted">This product has no ingredients in its formula.</td></tr>';
          allIngredientsSufficient = true;
        } else {
          $.each(res.ingredients, function(i, ing) {
            var badgeClass = ing.sufficient ? 'bg-success' : 'bg-danger';
            var badgeText = ing.sufficient ? 'Sufficient' : 'Insufficient';
            
            tbodyHtml += `
              <tr>
                <td>${escapeHtml(ing.name)} (${escapeHtml(ing.item_code)})</td>
                <td class="text-center font-monospace">${ing.req_qty_1}</td>
                <td class="text-center font-monospace font-weight-bold">${ing.total_req}</td>
                <td class="text-center font-monospace">${ing.available}</td>
                <td class="text-center"><span class="badge ${badgeClass}">${badgeText}</span></td>
              </tr>
            `;
          });
        }
        $('#modal_ingredients_tbody').html(tbodyHtml);
      } else {
        $('#ingredients_section').hide();
        allIngredientsSufficient = false;
      }
    },
    error: function() {
      $('#ingredients_section').hide();
      allIngredientsSufficient = false;
    }
  });
}

function validateProductionForm() {
  var productId = $('#modal_product_id').val();
  var batchNo = $('#modal_batch_no').val().trim();
  var qty = parseInt($('#modal_quantity').val()) || 0;

  if (!batchNo) {
    Swal.fire({
      title: "Error!",
      text: "Please enter a batch number.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  if (!productId) {
    Swal.fire({
      title: "Error!",
      text: "Please select a product.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  if (qty <= 0) {
    Swal.fire({
      title: "Error!",
      text: "Quantity must be greater than 0.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  if (!allIngredientsSufficient) {
    Swal.fire({
      title: "Error!",
      text: "Cannot submit: one or more ingredients have insufficient stock.",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  return true;
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

$(document).ready(function() {
  // Initialize select2 inside modal
  $('#modal_product_id').select2({
    dropdownParent: $('#scrollable-modal')
  });
  $('#modal_type').select2({
    dropdownParent: $('#scrollable-modal'),
    minimumResultsForSearch: Infinity
  });

  // Handle product selection change
  $('#modal_product_id').on('change', function() {
    var selectedOpt = $(this).find('option:selected');
    var expense = selectedOpt.data('expense') || 0.00;
    $('#modal_expense').val(parseFloat(expense).toFixed(2));
    loadIngredientsStatus();
  });

  // Handle quantity and type changes
  $('#modal_quantity').on('input change', function() {
    loadIngredientsStatus();
  });
  $('#modal_type').on('change', function() {
    loadIngredientsStatus();
  });

  // AJAX form submission matching the global pattern
  $('#add_product_stock_form').submit(function(e) {
    e.preventDefault();
    if (!validateProductionForm()) {
      return false;
    }

    var form = this;
    form.btn_verify.disabled = true; 
    $('.btn_verify').attr("disabled", true);
    $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
    $(".loader").show();

    var url = $(this).attr('action');
    $.ajax({
      type: 'POST',
      url: url,
      dataType: 'json',
      data: $(this).serialize(),
      success: function(res) {
        $(".loader").fadeOut("slow"); 
        if (res.status == '200') {
          Swal.fire({
            title: "Success!",
            text: res.message,
            icon: "success",
            customClass: {
              confirmButton: "btn btn-primary"
            },
            buttonsStyling: false
          }).then(() => {
            $('#scrollable-modal').modal('hide');
            window.location.href = res.url;
          });
        } else {	
          Swal.fire({
            title: "Error!",
            text: res.message,
            icon: "error",
            customClass: {
              confirmButton: "btn btn-primary"
            },
            buttonsStyling: false
          });
          $('.btn_verify').html('Submit');
          $('.btn_verify').attr("disabled", false);
        }
      },
      error: function() {
        $(".loader").fadeOut("slow");
        Swal.fire({
          title: "Error!",
          text: "An error occurred during submission. Please try again.",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: false
        });
        $('.btn_verify').html('Submit');
        $('.btn_verify').attr("disabled", false);
      }
    });
  });
});
</script>
