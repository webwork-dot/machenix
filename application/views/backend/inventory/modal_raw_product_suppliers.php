<?php
$product_id = (int)$param2;
$company_id = $this->session->userdata('company_id');
$product = $this->common_model->getRowById('raw_products', '*', ['id' => $product_id, 'product_type' => 'import', 'is_deleted' => '0']);
if (!is_array($product)) {
  $product = [];
}

$suppliers = $this->common_model->getResultById('supplier', 'id, name', ['company_id' => $company_id, 'type' => 'import', 'is_deleted' => '0']);
if (!is_array($suppliers)) {
  $suppliers = [];
}

$selected_suppliers = !empty($product['supplier_id']) ? array_filter(explode(',', $product['supplier_id'])) : [];

$product_variations = $this->common_model->getResultById('product_variations', '*', ['product_id' => $product_id]);
if (!is_array($product_variations)) {
  $product_variations = [];
}

$existing_pricing = [];
foreach ($product_variations as $pv) {
  $existing_pricing[$pv['supplier_id']] = [
    'usd_rate' => clean_number($pv['usd_rate'] ?? 0),
    'actual_usd_rate' => clean_number($pv['actual_usd_rate'] ?? 0),
    'rate' => clean_number($pv['rate'] ?? 0),
    'product_mrp' => clean_number($pv['product_mrp'] ?? 0),
    'costing_price' => clean_number($pv['costing_price'] ?? 0),
    'intimation' => (int)($pv['intimation'] ?? 0),
  ];
}

if (empty($existing_pricing) && !empty($selected_suppliers)) {
  foreach ($selected_suppliers as $s_id) {
    $existing_pricing[$s_id] = [
      'usd_rate' => clean_number($product['usd_rate'] ?? 0),
      'actual_usd_rate' => clean_number($product['actual_usd_rate'] ?? 0),
      'rate' => clean_number($product['rate'] ?? 0),
      'product_mrp' => clean_number($product['product_mrp'] ?? 0),
      'costing_price' => clean_number($product['costing_price'] ?? 0),
      'intimation' => (int)($product['intimation'] ?? 0),
    ];
  }
}
?>

<style>
  .supplier-modal-wrap .supplier-pricing-container {
    background: linear-gradient(135deg, #ffffff 0%, #fcfdfe 100%);
    border: 2px solid #e3e7ed;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }
  .supplier-modal-wrap .supplier-pricing-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #7367f0 0%, #9e95f5 100%);
    border-radius: 12px 0 0 12px;
  }
  .supplier-modal-wrap .supplier-pricing-header {
    background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%);
    color: #fff;
    padding: 8px 14px;
    border-radius: 8px;
    margin-bottom: 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 14px;
  }
  .supplier-modal-wrap .product-meta {
    margin-bottom: 14px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
  }
  .supplier-modal-wrap .product-meta strong {
    color: #0f172a;
  }
</style>

<div class="supplier-modal-wrap">
  <?php if (empty($product)): ?>
    <div class="alert alert-danger mb-0">Product not found.</div>
  <?php else: ?>
    <div class="product-meta">
      <strong><?php echo htmlspecialchars($product['name'] ?? ''); ?></strong>
      <?php if (!empty($product['item_code'])): ?>
        <span class="ms-1 text-muted">| Model: <?php echo htmlspecialchars($product['item_code']); ?></span>
      <?php endif; ?>
    </div>

    <?php echo form_open('inventory/update_raw_product_suppliers/' . $product_id, [
      'id' => 'raw_product_supplier_form',
      'onsubmit' => 'return submitRawProductSupplierForm(event);'
    ]); ?>

    <div class="row">
      <div class="col-12 mb-1">
        <div class="form-group">
          <label>Supplier <span class="required">*</span></label>
          <select class="form-select" name="supplier_id[]" id="modal_supplier_id" multiple required style="width: 100%;">
            <?php foreach ($suppliers as $supplier): ?>
              <option value="<?php echo (int)$supplier['id']; ?>" <?php echo in_array((string)$supplier['id'], array_map('strval', $selected_suppliers), true) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($supplier['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="col-12" id="supplier_pricing_sections_container"></div>
    </div>

    <div class="mt-1">
      <button type="submit" id="supplier_submit_btn" class="btn btn-primary waves-effect waves-float waves-light">
        <?php echo get_phrase('submit'); ?>
      </button>
      <button type="button" class="btn btn-secondary waves-effect waves-float waves-light" data-bs-dismiss="modal">
        Cancel
      </button>
    </div>

    <?php echo form_close(); ?>
  <?php endif; ?>
</div>

<script>
var existingSupplierPricing = <?php echo json_encode($existing_pricing); ?>;

function updateModalSupplierPricingSections() {
  var container = $('#supplier_pricing_sections_container');
  var selectedOptions = $('#modal_supplier_id').find(':selected');

  var currentValues = {};
  container.find('.supplier-pricing-container').each(function() {
    var supplierId = $(this).data('supplier-id');
    currentValues[supplierId] = {
      usd_rate: $(this).find('input[name^="supplier_usd_rate"]').val(),
      actual_usd_rate: $(this).find('input[name^="supplier_actual_usd_rate"]').val(),
      rate: $(this).find('input[name^="supplier_rate"]').val(),
      product_mrp: $(this).find('input[name^="supplier_product_mrp"]').val(),
      costing_price: $(this).find('input[name^="supplier_costing_price"]').val(),
      intimation: $(this).find('input[name^="supplier_intimation"]').val()
    };
  });

  container.empty();

  if (selectedOptions.length === 0) {
    return;
  }

  selectedOptions.each(function() {
    var supplierId = $(this).val();
    var supplierName = $(this).text().trim();

    var old = currentValues[supplierId] || existingSupplierPricing[supplierId] || {
      usd_rate: '0',
      actual_usd_rate: '0',
      rate: '0',
      product_mrp: '0',
      costing_price: '0',
      intimation: '0'
    };

    var cardHtml = `
      <div class="supplier-pricing-container mb-2" data-supplier-id="${supplierId}">
        <div class="supplier-pricing-header">
          <i class="fa fa-user"></i> Supplier: ${supplierName}
        </div>
        <div class="row">
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Official USD Rate</label>
              <input type="number" class="form-control" placeholder="Enter USD Rate" name="supplier_usd_rate[${supplierId}]" value="${old.usd_rate}" step="any">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Actual USD Rate</label>
              <input type="number" class="form-control" placeholder="Enter USD Rate" name="supplier_actual_usd_rate[${supplierId}]" value="${old.actual_usd_rate}" step="any">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Actual RMB</label>
              <input type="number" class="form-control" placeholder="Enter Rate" name="supplier_rate[${supplierId}]" value="${old.rate}" step="any">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Min Billing Price <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Min Billing Price" name="supplier_product_mrp[${supplierId}]" required value="${old.product_mrp}" step="any">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Min Selling Price <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Min Selling Price" name="supplier_costing_price[${supplierId}]" required value="${old.costing_price}" step="any">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Stock Intimation <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Stock Intimation" name="supplier_intimation[${supplierId}]" value="${old.intimation}" required>
            </div>
          </div>
        </div>
      </div>
    `;
    container.append(cardHtml);
  });
}

function submitRawProductSupplierForm(event) {
  event.preventDefault();

  var selectedSuppliers = $('#modal_supplier_id').val();
  if (!selectedSuppliers || selectedSuppliers.length === 0) {
    Swal.fire({
      title: "Error!",
      text: "Please select at least one supplier",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    return false;
  }

  var $form = $('#raw_product_supplier_form');
  var $submitBtn = $('#supplier_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }

  $.ajax({
    type: 'POST',
    url: $form.attr('action'),
    data: $form.serialize(),
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }

      if (res.status == 200 || res.status == '200') {
        Swal.fire({
          title: "Success!",
          text: res.message || "Suppliers updated successfully",
          icon: "success",
          customClass: { confirmButton: "btn btn-primary" },
          buttonsStyling: false
        }).then(function() {
          $('#large-modal').modal('hide');
          if (typeof dataTable !== 'undefined') {
            dataTable.ajax.reload(null, false);
          }
        });
      } else {
        Swal.fire({
          title: "Error!",
          text: res.message || "Update failed",
          icon: "error",
          customClass: { confirmButton: "btn btn-primary" },
          buttonsStyling: false
        });
        $submitBtn.html(originalText).attr('disabled', false);
      }
    },
    error: function() {
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
      Swal.fire({
        title: "Error!",
        text: "An error occurred while processing your request. Please try again.",
        icon: "error",
        customClass: { confirmButton: "btn btn-primary" },
        buttonsStyling: false
      });
      $submitBtn.html(originalText).attr('disabled', false);
    }
  });

  return false;
}

$(document).ready(function() {
  if ($('#modal_supplier_id').length) {
    $('#modal_supplier_id').select2({
      dropdownParent: $('#large-modal'),
      width: '100%',
      placeholder: 'Select Supplier'
    });

    $('#modal_supplier_id').on('change', function() {
      updateModalSupplierPricingSections();
    });

    updateModalSupplierPricingSections();
  }
});
</script>
