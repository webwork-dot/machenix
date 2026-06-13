<?php
$batch_no = urldecode($param2);

// Fetch all inventory records for this batch where quantity > 0
$products = $this->db->query("
    SELECT id, product_id, product_name, item_code, quantity 
    FROM inventory 
    WHERE batch_no = ? 
    AND quantity > 0 
    ORDER BY product_name ASC
", array($batch_no))->result_array();
?>

<div class="row">
  <div class="col-12">
    <h5 class="mb-2">Batch Products: <strong><?= htmlspecialchars($batch_no); ?></strong></h5>
    
    <div class="table-responsive">
      <table class="table table-bordered table-striped" id="modal-barcode-products-table">
        <thead>
          <tr>
            <th class="text-center" style="width: 50px;">
              <div class="form-check justify-content-center">
                  <input type="checkbox" class="form-check-input" id="select_all_barcode_products" checked>
              </div>
            </th>
            <th>Product Name</th>
            <th>Item Code</th>
            <th class="text-center">Quantity</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($products)): ?>
            <?php foreach ($products as $prod): ?>
              <tr>
                <td class="text-center">
                  <div class="form-check justify-content-center">
                    <input type="checkbox" name="product_id[]" value="<?= $prod['product_id']; ?>" class="form-check-input barcode_product_checkbox" checked>
                  </div>
                </td>
                <td><?= htmlspecialchars($prod['product_name']); ?></td>
                <td><?= htmlspecialchars($prod['item_code']); ?></td>
                <td class="text-center"><?= htmlspecialchars($prod['quantity']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">No products found with quantity &gt; 0 for this batch.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="row mt-2">
      <div class="col-12 text-end">
        <button type="button" id="generate_barcode_submit_btn" class="btn btn-primary waves-effect waves-float waves-light" <?= empty($products) ? 'disabled' : ''; ?>>
          Generate Barcode
        </button>
        <button type="button" class="btn btn-secondary waves-effect waves-float waves-light" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  $('#select_all_barcode_products').on('change', function() {
    $('.barcode_product_checkbox').prop('checked', this.checked);
  });
  
  $('.barcode_product_checkbox').on('change', function() {
    if ($('.barcode_product_checkbox:checked').length === $('.barcode_product_checkbox').length) {
      $('#select_all_barcode_products').prop('checked', true);
    } else {
      $('#select_all_barcode_products').prop('checked', false);
    }
  });
});
</script>
