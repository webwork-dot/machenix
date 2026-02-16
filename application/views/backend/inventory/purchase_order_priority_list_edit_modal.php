<style>
  .full-width-modal {
    max-width: 1400px !important;
  }

  input:read-only {
    background-color: #eee;
    border: 1px solid #ddd;
  }

  input {
    padding: 2px 5px;
  }

  .table-responsive {
    max-height: 500px;
    overflow-y: auto;
  }

  .priority-table th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 10;
  }
</style>

<?php
  // Get PO ID from param2
  $po_id = $param2;

  // Get PO details
  $po_data = $this->db->query("SELECT * FROM purchase_order WHERE id = '$po_id'")->row_array();

  // Get Priority List products (is_priority = 1)
  $priority_products = $this->db->query("
      SELECT pop.*, s.name as supplier_name
      FROM po_products pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id' AND pop.is_priority = 1
      ORDER BY pop.sort ASC
  ")->result_array();

  $temp_priority_products = [];
  foreach ($priority_products as $key => $product) {
    $row = $this->db->query("SELECT quantity FROM purchase_order_product WHERE product_id = '" . $product['product_id'] . "' AND parent_id = '$po_id' ");

    $temp_prod = $product;
    if($row->num_rows() > 0){
      $row = $row->row_array();
      $temp_prod['original_quantity'] = $row['quantity'];
    } else {
      $temp_prod['original_quantity'] = $product['quantity'];
    }
    
    $temp_priority_products[] = $temp_prod;
  }

  $priority_products = $temp_priority_products;

  // Get Loading List products (is_priority = 0)
  $loading_products = $this->db->query("
      SELECT pop.*, s.name as supplier_name
      FROM po_products pop
      LEFT JOIN supplier s ON s.id = pop.supplier_id
      WHERE pop.parent_id = '$po_id' AND pop.is_priority = 0
      ORDER BY pop.sort ASC
  ")->result_array();

  // Get suppliers for dropdown (filtered by company if needed)
  $company_id = $this->session->userdata('company_id');
  $supplier_where = array('is_deleted' => '0');
  if ($company_id) {
      $supplier_where['company_id'] = $company_id;
  }
  $supplier_list = $this->common_model->selectWhere('supplier', $supplier_where, 'ASC', 'name');

  // Get ready products with category names and supplier_id
  $ready_products = $this->db->query("
      SELECT rp.*, 
            (SELECT c.name FROM categories c WHERE FIND_IN_SET(c.id, rp.categories) > 0 LIMIT 1) as category_name
      FROM raw_products rp
      WHERE rp.is_deleted = '0' AND rp.type = 'ready' AND rp.status = '1'
      ORDER BY rp.name ASC
  ")->result();

  // Get spare products with category names and supplier_id
  $spare_products = $this->db->query("
      SELECT rp.*, 
            (SELECT c.name FROM categories c WHERE FIND_IN_SET(c.id, rp.categories) > 0 LIMIT 1) as category_name
      FROM raw_products rp
      WHERE rp.is_deleted = '0' AND rp.type = 'spare' AND rp.status = '1'
      ORDER BY rp.name ASC
  ")->result();
?>

<?php echo form_open('inventory/update_purchase_order_priority_list', ['class' => 'priority-list-form', 'onsubmit' => 'return checkForm(this);']); ?>
<input type="hidden" name="po_id" value="<?php echo $po_id; ?>">

<div class="row mt-2">
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-bordered table-striped priority-table" id="priority_table">
        <thead>
          <tr>
            <th style="width: 40px;">Sr</th>
            <th style="width: 120px;">Supplier Name</th>
            <th style="width: 100px;">Type</th>
            <th style="width: 150px;">Product Name</th>
            <th style="width: 100px;">Model No</th>
            <th style="width: 80px;">Quantity</th>
            <th style="width: 80px;">CBM</th>
            <th style="width: 100px;">Total CBM</th>
            <th style="width: 100px;">Pending PO Qty</th>
            <th style="width: 100px;">Loading List Qty</th>
            <th style="width: 100px;">In Stock Qty</th>
            <th style="width: 120px;">Selected Company Stock</th>
            <th style="width: 80px;">Action</th>
          </tr>
        </thead>
        <tbody id="priority_tbody">
          <?php 
            $sr_no = 1;
            foreach ($priority_products as $product): 
                $type_label = ($product['product_type'] == 'ready') ? 'Ready Goods' : (($product['product_type'] == 'spare') ? 'Spare Parts' : '');
            ?>
          <tr id="row_<?php echo $product['id']; ?>" data-product-id="<?php echo $product['id']; ?>">
            <td>
              <?php echo $sr_no++; ?>
              <input type="hidden" name="old_product_id[<?php echo $product['id']; ?>]"
                value="<?php echo $product['id']; ?>">
            </td>
            <td>
              <input type="text" class="form-control form-control-sm"
                value="<?php echo htmlspecialchars($product['supplier_name'] ?? ''); ?>" readonly>
              <input type="hidden" name="supplier_id[<?php echo $product['id']; ?>]"
                value="<?php echo $product['supplier_id']; ?>">
            </td>
            <td>
              <input type="text" class="form-control form-control-sm"
                value="<?php echo ($product['product_type'] == 'ready') ? 'Ready Goods' : (($product['product_type'] == 'spare') ? 'Spare Parts' : ''); ?>"
                readonly>
              <input type="hidden" name="product_type[<?php echo $product['id']; ?>]"
                value="<?php echo $product['product_type']; ?>">
            </td>
            <td>
              <input type="text" class="form-control form-control-sm"
                value="<?php echo htmlspecialchars($product['product_name']); ?>" readonly>
              <input type="hidden" name="product_id[<?php echo $product['id']; ?>]"
                value="<?php echo $product['product_id']; ?>">
              <input type="hidden" name="product_name[<?php echo $product['id']; ?>]"
                id="product_name_<?php echo $product['id']; ?>"
                value="<?php echo htmlspecialchars($product['product_name']); ?>">
            </td>
            <td>
              <input type="text" class="form-control form-control-sm" name="item_code[<?php echo $product['id']; ?>]"
                id="item_code_<?php echo $product['id']; ?>"
                value="<?php echo htmlspecialchars($product['item_code']); ?>" readonly>
            </td>
            <td>
              <input type="number" min="0" step="1" class="form-control form-control-sm qty-input"
                name="quantity[<?php echo $product['id']; ?>]" value="<?php echo $product['quantity']; ?>"
                data-original-qty="<?php echo $product['original_quantity']; ?>"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                onchange="updateTotalCBM(<?php echo $product['id']; ?>); checkQuantityChange(<?php echo $product['id']; ?>);">
              <input type="hidden" name="original_qty[<?php echo $product['id']; ?>]"
                value="<?php echo $product['quantity']; ?>">
              <input type="hidden" name="loading_list[<?php echo $product['id']; ?>]" value="0">
            </td>
            <td>
              <input type="number" step="any" class="form-control form-control-sm"
                name="cbm[<?php echo $product['id']; ?>]" id="cbm_<?php echo $product['id']; ?>"
                value="<?php echo $product['cbm']; ?>" readonly>
            </td>
            <td>
              <input type="number" step="any" class="form-control form-control-sm total-cbm-input"
                name="total_cbm[<?php echo $product['id']; ?>]" id="total_cbm_<?php echo $product['id']; ?>"
                value="<?php echo $product['total_cbm']; ?>" readonly>
            </td>
            <td>
              <input type="number" step="any" class="form-control form-control-sm"
                name="pending_po_qty[<?php echo $product['id']; ?>]" id="pending_po_qty_<?php echo $product['id']; ?>"
                value="<?php echo $product['pending_po_qty']; ?>" readonly>
            </td>
            <td>
              <input type="number" step="any" class="form-control form-control-sm"
                name="loading_list_qty[<?php echo $product['id']; ?>]"
                id="loading_list_qty_<?php echo $product['id']; ?>" value="<?php echo $product['loading_list_qty']; ?>"
                readonly>
            </td>
            <td>
              <input type="number" step="any" class="form-control form-control-sm"
                name="in_stock_qty[<?php echo $product['id']; ?>]" id="in_stock_qty_<?php echo $product['id']; ?>"
                value="<?php echo $product['in_stock_qty']; ?>" readonly>
            </td>
            <td>
              <input type="number" step="any" class="form-control form-control-sm"
                name="company_stock[<?php echo $product['id']; ?>]" id="company_stock_<?php echo $product['id']; ?>"
                value="<?php echo $product['current_company_qty']; ?>" readonly>
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(<?php echo $product['id']; ?>)">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr id="total_cbm_row" style="background-color: #f8f9fa; font-weight: bold;">
            <td colspan="7" class="text-right">
              <strong>Total CBM:</strong>
            </td>
            <td>
              <input type="number" step="any" class="form-control form-control-sm" id="grand_total_cbm" value="0"
                readonly style="font-weight: bold;">
            </td>
            <td colspan="5"></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="text-center mt-2">
      <button type="button" class="btn btn-primary btn-sm" onclick="addNewRow()">
        <i class="fa fa-plus"></i> Add New Product
      </button>
    </div>

    <!-- Remark Field -->
    <div class="row mt-2">
      <div class="col-md-12">
        <div class="form-group">
          <label>Notes</label>
          <textarea class="form-control" name="notes" id="notes" rows="3"
            placeholder="Enter notes..."><?php echo htmlspecialchars($po_data['notes'] ?? ''); ?></textarea>
        </div>
      </div>
    </div>

    <!-- Loading Products Table -->
    <div class="mt-2">
      <h5 class="mb-2">2st Load List, If Space Left</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-striped priority-table" id="loading_products_table">
          <thead>
            <tr>
              <th style="width: 40px;">Sr</th>
              <th style="width: 120px;">Supplier Name</th>
              <th style="width: 100px;">Type</th>
              <th style="width: 150px;">Product Name</th>
              <th style="width: 100px;">Model No</th>
              <th style="width: 80px;">Quantity</th>
              <th style="width: 80px;">CBM</th>
              <th style="width: 100px;">Total CBM</th>
              <th style="width: 100px;">Pending PO Qty</th>
              <th style="width: 100px;">Loading List Qty</th>
              <th style="width: 100px;">In Stock Qty</th>
              <th style="width: 120px;">Selected Company Stock</th>
              <th style="width: 80px;">Action</th>
            </tr>
          </thead>
          <tbody id="loading_products_tbody">
            <?php 
              $loading_sr_no = 1;
              foreach ($loading_products as $loading_product): 
            ?>
            <tr id="loading_row_<?php echo $loading_product['id']; ?>"
              data-original-row-id="<?php echo $loading_product['id']; ?>">
              <td>
                <?php echo $loading_sr_no++; ?>
                <input type="hidden" name="loading_old_product_id[<?php echo $loading_product['id']; ?>]"
                  value="<?php echo $loading_product['id']; ?>">
              </td>
              <td>
                <select class="form-control form-control-sm loading-supplier-select"
                  name="loading_supplier_id[<?php echo $loading_product['id']; ?>]"
                  onchange="handleLoadingSupplierChange(this, '<?php echo $loading_product['id']; ?>')" required>
                  <option value="">Select Supplier</option>
                  <?php foreach($supplier_list as $supplier): ?>
                  <option value="<?php echo $supplier->id; ?>"
                    <?php echo ($loading_product['supplier_id'] == $supplier->id) ? 'selected' : ''; ?>>
                    <?php echo $supplier->name; ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <select class="form-control form-control-sm loading-type-select"
                  name="loading_product_type[<?php echo $loading_product['id']; ?>]"
                  onchange="handleLoadingTypeChange(this, '<?php echo $loading_product['id']; ?>')" required>
                  <option value="">Select Type</option>
                  <option value="ready" <?php echo ($loading_product['product_type'] == 'ready') ? 'selected' : ''; ?>>
                    Ready Goods</option>
                  <option value="spare" <?php echo ($loading_product['product_type'] == 'spare') ? 'selected' : ''; ?>>
                    Spare Parts</option>
                </select>
              </td>
              <td>
                <select class="form-control form-control-sm loading-product-select select2"
                  name="loading_product_id[<?php echo $loading_product['id']; ?>]"
                  id="loading_product_select_<?php echo $loading_product['id']; ?>"
                  onchange="handleLoadingProductChange(this, '<?php echo $loading_product['id']; ?>')" required>
                  <option value="">Select Product</option>
                </select>
                <input type="hidden" name="loading_product_name[<?php echo $loading_product['id']; ?>]"
                  id="loading_product_name_<?php echo $loading_product['id']; ?>"
                  value="<?php echo htmlspecialchars($loading_product['product_name']); ?>">
                <input type="hidden" name="loading_original_product_id[<?php echo $loading_product['id']; ?>]"
                  value="<?php echo $loading_product['product_id']; ?>">
              </td>
              <td>
                <input type="text" class="form-control form-control-sm"
                  name="loading_item_code[<?php echo $loading_product['id']; ?>]"
                  id="loading_item_code_<?php echo $loading_product['id']; ?>"
                  value="<?php echo htmlspecialchars($loading_product['item_code']); ?>" readonly>
              </td>
              <td>
                <input type="number" min="0" step="1" class="form-control form-control-sm loading-qty-input"
                  name="loading_quantity[<?php echo $loading_product['id']; ?>]"
                  id="loading_quantity_<?php echo $loading_product['id']; ?>"
                  value="<?php echo $loading_product['quantity']; ?>"
                  data-original-qty="<?php echo $loading_product['quantity']; ?>"
                  oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                  onchange="updateLoadingTotalCBM('<?php echo $loading_product['id']; ?>'); checkLoadingQuantityChange('<?php echo $loading_product['id']; ?>');">
                <input type="hidden" name="loading_list[<?php echo $loading_product['id']; ?>]" value="1">
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm"
                  name="loading_cbm[<?php echo $loading_product['id']; ?>]"
                  id="loading_cbm_<?php echo $loading_product['id']; ?>" value="<?php echo $loading_product['cbm']; ?>"
                  readonly>
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm loading-total-cbm-input"
                  name="loading_total_cbm[<?php echo $loading_product['id']; ?>]"
                  id="loading_total_cbm_<?php echo $loading_product['id']; ?>"
                  value="<?php echo $loading_product['total_cbm']; ?>" readonly>
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm"
                  name="loading_pending_po_qty[<?php echo $loading_product['id']; ?>]"
                  id="loading_pending_po_qty_<?php echo $loading_product['id']; ?>"
                  value="<?php echo $loading_product['pending_po_qty']; ?>" readonly>
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm"
                  name="loading_loading_list_qty[<?php echo $loading_product['id']; ?>]"
                  id="loading_loading_list_qty_<?php echo $loading_product['id']; ?>"
                  value="<?php echo $loading_product['loading_list_qty']; ?>" readonly>
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm"
                  name="loading_in_stock_qty[<?php echo $loading_product['id']; ?>]"
                  id="loading_in_stock_qty_<?php echo $loading_product['id']; ?>"
                  value="<?php echo $loading_product['in_stock_qty']; ?>" readonly>
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm"
                  name="loading_company_stock[<?php echo $loading_product['id']; ?>]"
                  id="loading_company_stock_<?php echo $loading_product['id']; ?>"
                  value="<?php echo $loading_product['current_company_qty']; ?>" readonly>
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-danger"
                  onclick="removeLoadingRow('<?php echo $loading_product['id']; ?>')">
                  <i class="fa fa-trash"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="text-center mt-2">
        <button type="button" class="btn btn-primary btn-sm" onclick="addLoadingProductRow()">
          <i class="fa fa-plus"></i> Add New Product
        </button>
      </div>
    </div>

    <div class="text-center mt-3">
      <button type="submit" class="btn btn-primary btn_verify" name="btn_verify">Update</button>
    </div>
  </div>
</div>

</form>

<script>
// Tab Open select2
$(document).on('keydown', '.type-select, .loading-type-select', function (e) {
  if (e.key !== 'Tab' || e.shiftKey) return;
  const $row = $(this).closest('tr');
  const $productSelect = $row
    .find('.product-select.select2, .loading-product-select.select2')
    .first();

  setTimeout(() => {
    if ($productSelect.length) {
      $productSelect.select2('open');
    }
  }, 0);
});

var rowCounter = <?php echo count($priority_products); ?>;
var newRowCounter = 0;

// Store products with supplier_id for filtering
var readyProductsData = {};
<?php foreach($ready_products as $item): ?>
readyProductsData[<?php echo $item->id; ?>] = {
  id: <?php echo $item->id; ?>,
  name: '<?php echo addslashes($item->name); ?>',
  category_name: '<?php echo addslashes($item->category_name ?? '-'); ?>',
  supplier_id: <?php echo $item->supplier_id ?? 'null'; ?>,
  categories: '<?php echo addslashes($item->categories ?? ''); ?>'
};
<?php endforeach; ?>

var spareProductsData = {};
<?php foreach($spare_products as $item): ?>
spareProductsData[<?php echo $item->id; ?>] = {
  id: <?php echo $item->id; ?>,
  name: '<?php echo addslashes($item->name); ?>',
  category_name: '<?php echo addslashes($item->category_name ?? '-'); ?>',
  supplier_id: <?php echo $item->supplier_id ?? 'null'; ?>,
  categories: '<?php echo addslashes($item->categories ?? ''); ?>'
};
<?php endforeach; ?>

// Function to get filtered products by supplier_id and category
function getFilteredProducts(type, supplierId) {
  var productsData = type === 'ready' ? readyProductsData : spareProductsData;
  var filteredOptions = '<option value="">Select Product</option>';

  for (var productId in productsData) {
    var product = productsData[productId];
    // Filter by supplier_id: show product if supplier_id matches OR supplier_id is null/empty (products without supplier)
    if (!supplierId || !product.supplier_id || product.supplier_id == supplierId) {
      filteredOptions += '<option value="' + product.id + '">' +
        (product.category_name || '-') + ' - ' + product.name + '</option>';
    }
  }

  return filteredOptions;
}

// Generate supplier options for JavaScript
var supplierOptions = '';
<?php foreach($supplier_list as $supplier): ?>
supplierOptions += '<option value="<?php echo $supplier->id; ?>"><?php echo addslashes($supplier->name); ?></option>';
<?php endforeach; ?>

// Store original quantities for tracking
var originalQuantities = {};
<?php foreach($priority_products as $product): ?>
originalQuantities[<?php echo $product['id']; ?>] = <?php echo $product['original_quantity']; ?>;
<?php endforeach; ?>

// Store original quantities for loading products that came from priority list
var loadingOriginalQuantities = {};
<?php foreach($loading_products as $loading_product): ?>
loadingOriginalQuantities['<?php echo $loading_product['id']; ?>'] = <?php echo $loading_product['quantity']; ?>;
<?php endforeach; ?>

var loadingProductCounter = 0;

// Initialize on document ready
$(document).ready(function() {
  setTimeout(() => {
    $('.select2').select2({
      dropdownParent: $('#large-modal .modal-content')
    });
  }, 1000)

  initPriorityListSortable();
  // Initialize product dropdowns for existing loading products
  <?php foreach($loading_products as $loading_product): ?>
    (function() {
      var loadingRowId = '<?php echo $loading_product['id']; ?>';
      var loadingSupplierId = '<?php echo $loading_product['supplier_id']; ?>';
      var loadingProductType = '<?php echo $loading_product['product_type']; ?>';
      var loadingProductId = '<?php echo $loading_product['product_id']; ?>';

      // Use a function to initialize each row
      function initializeLoadingProduct() {
        var $row = $('#loading_row_' + loadingRowId);
        var loadingProductSelect = $('#loading_product_select_' + loadingRowId);

        if ($row.length === 0 || loadingProductSelect.length === 0) {
          setTimeout(initializeLoadingProduct, 50);
          return;
        }

        // Get current values from dropdowns (they should be pre-selected in HTML)
        var currentSupplierId = $row.find('select[name^="loading_supplier_id"]').val() || loadingSupplierId;
        var currentProductType = $row.find('select[name^="loading_product_type"]').val() || loadingProductType;

        if (currentSupplierId && currentProductType) {
          // Populate product dropdown with filtered products
          loadingProductSelect.html(getFilteredProducts(currentProductType, currentSupplierId));

          // Wait for options to be added to DOM, then select the product
          setTimeout(function() {
            if (loadingProductId) {
              // Set the value
              loadingProductSelect.val(loadingProductId);

              // Verify selection was successful
              if (loadingProductSelect.val() == loadingProductId) {
                // Update product name from selected option
                var selectedOptionText = loadingProductSelect.find('option:selected').text();
                if (selectedOptionText && selectedOptionText !== 'Select Product') {
                  var cleanName = selectedOptionText;
                  if (selectedOptionText.indexOf(' - ') > -1) {
                    var parts = selectedOptionText.split(' - ');
                    cleanName = parts.length > 1 ? parts.slice(1).join(' - ') : selectedOptionText;
                  }
                  $('#loading_product_name_' + loadingRowId).val(cleanName);
                }
              } else {
                // Product not found in filtered list - might be filtered out, try without filter
                // This shouldn't happen if supplier/type match, but handle it gracefully
                console.warn('Product ' + loadingProductId + ' not found in filtered list for row ' +
                  loadingRowId);
              }
            }
          }, 150);
        } else {
          // If supplier or type not available yet, try again
          setTimeout(initializeLoadingProduct, 50);
        }
      }

      // Start initialization after a short delay to ensure DOM is ready
      setTimeout(initializeLoadingProduct, 200);
    })();
  <?php endforeach; ?>

  calculateGrandTotalCBM();
  if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.replace('notes');
  }
  
  // Prevent form submission on Enter key press (except in textareas)
  $('form.priority-list-form').on('keydown', 'input:not(textarea), select', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      return false;
    }
  });
  
  // Also prevent Enter key on the form level
  $('form.priority-list-form').on('keypress', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      // Allow Enter in textareas
      if ($(e.target).is('textarea')) {
        return true;
      }
      // Prevent Enter in all other fields
      e.preventDefault();
      return false;
    }
  });
});

// Check quantity changes and update Loading Products table
function checkQuantityChange(rowId) {
  // Only check for existing products (not new rows)
  if (typeof originalQuantities[rowId] === 'undefined') {
    return;
  }

  var row = $('#row_' + rowId);
  var originalQty = originalQuantities[rowId];
  var currentQty = parseFloat(row.find('.qty-input').val()) || 0;
  var removedQty = originalQty - currentQty;

  var loadingRowId = 'loading_' + rowId;
  var loadingRow = $('#loading_row_' + loadingRowId);

  if (removedQty > 0) {
    // Quantity reduced - add/update in Loading Products
    // Get product details
    var productId = row.find('input[name^="product_id"]').val();
    var productName = row.find('input[name^="product_name"]').val();
    var itemCode = row.find('input[name^="item_code"]').val();
    var supplierId = row.find('input[name^="supplier_id"]').val() || row.find('select[name^="supplier_id"]').val();
    var supplierSelect = row.find('.supplier-select');
    var supplierName = '';
    if (supplierSelect.length > 0) {
      supplierName = supplierSelect.find('option:selected').text() || '';
    } else {
      supplierName = row.find('input[name^="supplier_id"]').closest('td').find('input[type="text"]').val() || '';
    }
    var productType = row.find('input[name^="product_type"]').val() || row.find('select[name^="product_type"]').val();
    var typeLabel = (productType == 'ready') ? 'Ready Goods' : (productType == 'spare' ? 'Spare Parts' : '');
    var cbm = parseFloat(row.find('input[name^="cbm"]').val()) || 0;
    var pendingPoQty = parseFloat(row.find('input[name^="pending_po_qty"]').val()) || 0;
    var loadingListQty = parseFloat(row.find('input[name^="loading_list_qty"]').val()) || 0;
    var inStockQty = parseFloat(row.find('input[name^="in_stock_qty"]').val()) || 0;
    var companyStock = parseFloat(row.find('input[name^="company_stock"]').val()) || 0;

    // Check if this product already exists in Loading Products table (by product_id)
    var existingLoadingRow = null;
    $('#loading_products_tbody tr').each(function() {
      var $loadingRow = $(this);
      var loadingProductId = $loadingRow.find('input[name^="loading_product_id"]').val() ||
        $loadingRow.find('select[name^="loading_product_id"]').val() ||
        $loadingRow.find('input[name^="loading_original_product_id"]').val();
      if (loadingProductId == productId) {
        existingLoadingRow = $loadingRow;
        return false; // Break the loop
      }
    });

    // If product exists in Loading Products, remove it first
    if (existingLoadingRow && existingLoadingRow.length > 0) {
      existingLoadingRow.remove();
      updateLoadingRowNumbers();
    }

    // Now add/create a new row in Loading Products
    addToLoadingProducts(loadingRowId, rowId, supplierId, supplierName, productType, typeLabel, productId,
      productName, itemCode, removedQty, cbm, pendingPoQty, loadingListQty, inStockQty, companyStock);

    // If quantity is 0, hide the row
    if (currentQty == 0) {
      row.hide();
    } else {
      row.show();
    }
  } else {
    // Quantity restored or increased - remove from Loading Products
    if (loadingRow.length > 0) {
      loadingRow.remove();
      updateLoadingRowNumbers();
    } else {
      var productId = row.find('input[name^="product_id"]').val();
      var supplierId = row.find('input[name^="supplier_id"]').val() || row.find('select[name^="supplier_id"]').val();
      var productType = row.find('input[name^="product_type"]').val() || row.find('select[name^="product_type"]').val();

      var $found = null;
      $('#loading_products_tbody tr').each(function () {
        var $lr = $(this);
        var lp = $lr.find('input[name^="loading_original_product_id"]').val()
              || $lr.find('select[name^="loading_product_id"]').val();
        var ls = $lr.find('select[name^="loading_supplier_id"]').val() || $lr.find('input[name^="loading_supplier_id"]').val();
        var lt = $lr.find('select[name^="loading_product_type"]').val() || $lr.find('input[name^="loading_product_type"]').val();

        if (String(lp) === String(productId) && String(ls) === String(supplierId) && String(lt) === String(productType)) {
          $found = $lr;
          return false;
        }
      });

      if ($found && $found.length) {
        $found.remove();
        updateLoadingRowNumbers();
      }

    }

    row.show();
  }

}

// Add to Loading Products table
function addToLoadingProducts(loadingRowId, originalRowId, supplierId, supplierName, productType, typeLabel, productId,
  productName, itemCode, quantity, cbm, pendingPoQty, loadingListQty, inStockQty, companyStock) {
  var loadingSrNo = $('#loading_products_tbody tr').length + 1;
  var totalCBM = quantity * cbm;

  var loadingRow = `
        <tr id="loading_row_${loadingRowId}" data-original-row-id="${originalRowId}">
            <td>
                ${loadingSrNo}
                <input type="hidden" name="loading_old_product_id[${loadingRowId}]" value="${originalRowId}">
            </td>
            <td>
                <select class="form-control form-control-sm loading-supplier-select" name="loading_supplier_id[${loadingRowId}]" required>
                    <option value="">Select Supplier</option>
                </select>
            </td>
            <td>
                <select class="form-control form-control-sm loading-type-select" name="loading_product_type[${loadingRowId}]" 
                    onchange="handleLoadingTypeChange(this, '${loadingRowId}')" required>
                    <option value="">Select Type</option>
                    <option value="ready" ${productType == 'ready' ? 'selected' : ''}>Ready Goods</option>
                    <option value="spare" ${productType == 'spare' ? 'selected' : ''}>Spare Parts</option>
                </select>
            </td>
            <td>
                <select class="form-control form-control-sm loading-product-select select2" name="loading_product_id[${loadingRowId}]" 
                    id="loading_product_select_${loadingRowId}" onchange="handleLoadingProductChange(this, '${loadingRowId}')" required>
                    <option value="">Select Product</option>
                </select>
                <input type="hidden" name="loading_product_name[${loadingRowId}]" id="loading_product_name_${loadingRowId}" value="${productName}">
                <input type="hidden" name="loading_original_product_id[${loadingRowId}]" value="${productId}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="loading_item_code[${loadingRowId}]" 
                    id="loading_item_code_${loadingRowId}" value="${itemCode}" readonly>
            </td>
            <td>
                <input type="number" min="0" step="1" class="form-control form-control-sm loading-qty-input" 
                    name="loading_quantity[${loadingRowId}]" value="${quantity}" 
                    data-original-qty="${quantity}"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                    onchange="updateLoadingTotalCBM('${loadingRowId}'); checkLoadingQuantityChange('${loadingRowId}');">
                <input type="hidden" name="loading_list[${loadingRowId}]" value="1">
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_cbm[${loadingRowId}]" id="loading_cbm_${loadingRowId}" value="${cbm}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm loading-total-cbm-input" 
                    name="loading_total_cbm[${loadingRowId}]" id="loading_total_cbm_${loadingRowId}" value="${totalCBM.toFixed(2)}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_pending_po_qty[${loadingRowId}]" id="loading_pending_po_qty_${loadingRowId}" value="${pendingPoQty}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_loading_list_qty[${loadingRowId}]" id="loading_loading_list_qty_${loadingRowId}" value="${loadingListQty}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_in_stock_qty[${loadingRowId}]" id="loading_in_stock_qty_${loadingRowId}" value="${inStockQty}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_company_stock[${loadingRowId}]" id="loading_company_stock_${loadingRowId}" value="${companyStock}" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeLoadingRow('${loadingRowId}')">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

  $('#loading_products_tbody').append(loadingRow);
  
  // Store original quantity if this came from priority list
  if (originalRowId && originalRowId != '0' && originalRowId != 0) {
    loadingOriginalQuantities[loadingRowId] = quantity;
  }

  // Use setTimeout to ensure DOM is ready before populating dropdowns
  setTimeout(function() {
    // Populate supplier dropdown and set value
    var supplierSelect = $('#loading_row_' + loadingRowId).find('.loading-supplier-select');
    supplierSelect.html('<option value="">Select Supplier</option>' + supplierOptions);
    if (supplierId) {
      supplierSelect.val(supplierId).trigger('change');
    }

    // Set product type dropdown value
    var typeSelect = $('#loading_row_' + loadingRowId).find('.loading-type-select');
    if (productType) {
      typeSelect.val(productType);
    }

    // Populate product dropdown based on type and supplier, and set value
    var productSelect = $('#loading_product_select_' + loadingRowId);
    productSelect.html(getFilteredProducts(productType, supplierId));

    // Set product value after a small delay to ensure options are loaded
    setTimeout(function() {
      if (productId) {
        productSelect.val(productId);
        // Also update the hidden product name field (extract only product name, remove category)
        var selectedOptionText = productSelect.find('option:selected').text();
        if (selectedOptionText && selectedOptionText !== 'Select Product') {
          var cleanName = selectedOptionText;
          if (selectedOptionText.indexOf(' - ') > -1) {
            var parts = selectedOptionText.split(' - ');
            cleanName = parts.length > 1 ? parts.slice(1).join(' - ') : selectedOptionText;
          }
          $('#loading_product_name_' + loadingRowId).val(cleanName);
        } else {
          var cleanProductName = productName;
          if (productName.indexOf(' - ') > -1) {
            var parts = productName.split(' - ');
            cleanProductName = parts.length > 1 ? parts.slice(1).join(' - ') : productName;
          }
          $('#loading_product_name_' + loadingRowId).val(cleanProductName);
        }
      }

      $('.select2').select2({
        dropdownParent: $('#large-modal .modal-content')
      });
    }, 50);
  }, 10);

  // Set product name immediately (extract only product name, remove category if present)
  var cleanProductName = productName;
  if (productName.indexOf(' - ') > -1) {
    var parts = productName.split(' - ');
    cleanProductName = parts.length > 1 ? parts.slice(1).join(' - ') : productName;
  }
  $('#loading_product_name_' + loadingRowId).val(cleanProductName);

  updateLoadingRowNumbers();
}

// Update Loading Products row numbers
function updateLoadingRowNumbers() {
  $('#loading_products_tbody tr').each(function(index) {
    var rowId = $(this).attr('id').replace('loading_row_', '');
    var originalRowId = $(this).data('original-row-id') || 0;
    var firstTd = $(this).find('td:first');

    // Update hidden input
    var hiddenInput = firstTd.find('input[name^="loading_old_product_id"]');
    if (hiddenInput.length === 0) {
      firstTd.append('<input type="hidden" name="loading_old_product_id[' + rowId + ']" value="' + originalRowId +
        '">');
    } else {
      hiddenInput.val(originalRowId);
    }

    // Update serial number text
    var textContent = firstTd.clone().children().remove().end().text().trim();
    firstTd.contents().filter(function() {
      return this.nodeType === 3; // Text node
    }).remove();
    firstTd.prepend((index + 1) + ' ');
  });
}

// Update Loading Total CBM
function updateLoadingTotalCBM(loadingRowId) {
  var row = $('#loading_row_' + loadingRowId);
  var qty = parseFloat(row.find('.loading-qty-input').val()) || 0;
  var cbm = parseFloat(row.find('#loading_cbm_' + loadingRowId).val()) || 0;
  var totalCBM = qty * cbm;
  row.find('#loading_total_cbm_' + loadingRowId).val(totalCBM.toFixed(2));
}

function findPriorityRowByKeys(productId, supplierId, productType) {
  var found = null;

  $('#priority_tbody tr').each(function () {
    var $r = $(this);

    var rPid = $r.find('input[name^="product_id"]').val() || $r.find('select[name^="product_id"]').val();
    var rSid = $r.find('input[name^="supplier_id"]').val() || $r.find('select[name^="supplier_id"]').val();
    var rTyp = $r.find('input[name^="product_type"]').val() || $r.find('select[name^="product_type"]').val();

    if (String(rPid) === String(productId) && String(rSid) === String(supplierId) && String(rTyp) === String(productType)) {
      found = $r;
      return false; // break
    }
  });

  return found;
}

function getLoadingRowSnapshot(loadingRowId) {
  var $row = $('#loading_row_' + loadingRowId);

  var supplierId = $row.find('select[name^="loading_supplier_id"]').val() || $row.find('input[name^="loading_supplier_id"]').val() || '';
  var supplierName = ($row.find('.loading-supplier-select option:selected').text() || '').trim();

  var productType = $row.find('select[name^="loading_product_type"]').val() || $row.find('input[name^="loading_product_type"]').val() || '';
  var typeLabel = (productType === 'ready') ? 'Ready Goods' : (productType === 'spare' ? 'Spare Parts' : '');

  var productId = $row.find('input[name^="loading_original_product_id"]').val()
    || $row.find('select[name^="loading_product_id"]').val()
    || '';

  var productName = $row.find('input[name^="loading_product_name"]').val() || '';
  if (!productName) {
    // fallback to selected option text
    var t = ($row.find('select[name^="loading_product_id"] option:selected').text() || '').trim();
    if (t.indexOf(' - ') > -1) {
      var parts = t.split(' - ');
      productName = parts.length > 1 ? parts.slice(1).join(' - ') : t;
    } else {
      productName = t;
    }
  }

  var itemCode = $row.find('input[name^="loading_item_code"]').val() || '';
  var cbm = parseFloat($row.find('input[name^="loading_cbm"]').val()) || 0;

  var pendingPoQty = parseFloat($row.find('input[name^="loading_pending_po_qty"]').val()) || 0;
  var loadingListQty = parseFloat($row.find('input[name^="loading_loading_list_qty"]').val()) || 0;
  var inStockQty = parseFloat($row.find('input[name^="loading_in_stock_qty"]').val()) || 0;
  var companyStock = parseFloat($row.find('input[name^="loading_company_stock"]').val()) || 0;

  return {
    supplierId, supplierName,
    productType, typeLabel,
    productId, productName,
    itemCode, cbm,
    pendingPoQty, loadingListQty,
    inStockQty, companyStock
  };
}

function addPriorityRowFromLoading(data, qtyToMove) {
  // make a new priority row (so backend creates a new priority record)
  newRowCounter++;
  rowCounter++;

  var rowId = 'from_loading_' + newRowCounter;
  var totalCBM = (qtyToMove * (data.cbm || 0));

  // basic html escaping
  function esc(v) {
    return String(v ?? '')
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  var html = `
    <tr id="row_${rowId}" data-product-id="${rowId}">
      <td>
        ${rowCounter}
        <input type="hidden" name="old_product_id[${rowId}]" value="0">
      </td>

      <td>
        <input type="text" class="form-control form-control-sm" value="${esc(data.supplierName)}" readonly>
        <input type="hidden" name="supplier_id[${rowId}]" value="${esc(data.supplierId)}">
      </td>

      <td>
        <input type="text" class="form-control form-control-sm" value="${esc(data.typeLabel)}" readonly>
        <input type="hidden" name="product_type[${rowId}]" value="${esc(data.productType)}">
      </td>

      <td>
        <input type="text" class="form-control form-control-sm" value="${esc(data.productName)}" readonly>
        <input type="hidden" name="product_id[${rowId}]" value="${esc(data.productId)}">
        <input type="hidden" name="product_name[${rowId}]" id="product_name_${rowId}" value="${esc(data.productName)}">
      </td>

      <td>
        <input type="text" class="form-control form-control-sm"
          name="item_code[${rowId}]" id="item_code_${rowId}" value="${esc(data.itemCode)}" readonly>
      </td>

      <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm qty-input"
          name="quantity[${rowId}]"
          value="${qtyToMove}"
          data-original-qty="${qtyToMove}"
          oninput="this.value=this.value.replace(/[^0-9]/g,'');"
          onchange="updateTotalCBM('${rowId}');">
        <input type="hidden" name="original_qty[${rowId}]" value="${qtyToMove}">
        <input type="hidden" name="loading_list[${rowId}]" value="0">
      </td>

      <td>
        <input type="number" step="any" class="form-control form-control-sm"
          name="cbm[${rowId}]" id="cbm_${rowId}" value="${data.cbm}" readonly>
      </td>

      <td>
        <input type="number" step="any" class="form-control form-control-sm total-cbm-input"
          name="total_cbm[${rowId}]" id="total_cbm_${rowId}" value="${totalCBM.toFixed(2)}" readonly>
      </td>

      <td>
        <input type="number" step="any" class="form-control form-control-sm"
          name="pending_po_qty[${rowId}]" id="pending_po_qty_${rowId}" value="${data.pendingPoQty}" readonly>
      </td>

      <td>
        <input type="number" step="any" class="form-control form-control-sm"
          name="loading_list_qty[${rowId}]" id="loading_list_qty_${rowId}" value="${data.loadingListQty}" readonly>
      </td>

      <td>
        <input type="number" step="any" class="form-control form-control-sm"
          name="in_stock_qty[${rowId}]" id="in_stock_qty_${rowId}" value="${data.inStockQty}" readonly>
      </td>

      <td>
        <input type="number" step="any" class="form-control form-control-sm"
          name="company_stock[${rowId}]" id="company_stock_${rowId}" value="${data.companyStock}" readonly>
      </td>

      <td>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow('${rowId}')">
          <i class="fa fa-trash"></i>
        </button>
      </td>
    </tr>
  `;

  $('#priority_tbody').append(html);
  updateRowNumbers();
  calculateGrandTotalCBM();

  return rowId;
}

// Check loading quantity changes and restore to priority list if needed
function checkLoadingQuantityChange(loadingRowId) {
  var $loadingRow = $('#loading_row_' + loadingRowId);
  if ($loadingRow.length === 0) return;

  var currentQty = parseFloat($loadingRow.find('.loading-qty-input').val()) || 0;

  // baseline qty for "how much moved back to priority"
  var originalQty = (typeof loadingOriginalQuantities[loadingRowId] !== 'undefined')
    ? (parseFloat(loadingOriginalQuantities[loadingRowId]) || 0)
    : (parseFloat($loadingRow.find('.loading-qty-input').attr('data-original-qty')) || 0);

  var movedBackQty = originalQty - currentQty;

  // only when user reduces loading qty
  if (movedBackQty <= 0) return;

  var data = getLoadingRowSnapshot(loadingRowId);
  if (!data.productId || !data.supplierId || !data.productType) return;

  // 1) find matching priority row
  var $priorityRow = findPriorityRowByKeys(data.productId, data.supplierId, data.productType);

  if ($priorityRow && $priorityRow.length > 0) {
    // add qty into existing priority row
    var priorityRowId = $priorityRow.attr('id').replace('row_', '');
    var curPriorityQty = parseFloat($priorityRow.find('.qty-input').val()) || 0;

    $priorityRow.find('.qty-input').val(curPriorityQty + movedBackQty);
    updateTotalCBM(priorityRowId);
    $priorityRow.show();
  } else {
    // 2) create a new priority row if missing
    addPriorityRowFromLoading(data, movedBackQty);
  }

  // update baseline so next change is incremental
  loadingOriginalQuantities[loadingRowId] = currentQty;
  $loadingRow.find('.loading-qty-input').attr('data-original-qty', currentQty);

  updateLoadingTotalCBM(loadingRowId);

  // if loading becomes 0, remove row
  if (currentQty === 0) {
    $loadingRow.remove();
    updateLoadingRowNumbers();
    delete loadingOriginalQuantities[loadingRowId];
  }
}


// Restore product back to Priority List
function restoreToPriorityList(loadingRowId, quantity) {
  var loadingRow = $('#loading_row_' + loadingRowId);
  
  // Get all product details from loading row
  var productId = loadingRow.find('input[name^="loading_original_product_id"]').val() || 
                  loadingRow.find('select[name^="loading_product_id"]').val();
  var productName = loadingRow.find('input[name^="loading_product_name"]').val();
  var itemCode = loadingRow.find('input[name^="loading_item_code"]').val();
  var supplierId = loadingRow.find('select[name^="loading_supplier_id"]').val();
  var supplierSelect = loadingRow.find('.loading-supplier-select');
  var supplierName = supplierSelect.find('option:selected').text() || '';
  var productType = loadingRow.find('select[name^="loading_product_type"]').val();
  var cbm = parseFloat(loadingRow.find('input[name^="loading_cbm"]').val()) || 0;
  var pendingPoQty = parseFloat(loadingRow.find('input[name^="loading_pending_po_qty"]').val()) || 0;
  var loadingListQty = parseFloat(loadingRow.find('input[name^="loading_loading_list_qty"]').val()) || 0;
  var inStockQty = parseFloat(loadingRow.find('input[name^="loading_in_stock_qty"]').val()) || 0;
  var companyStock = parseFloat(loadingRow.find('input[name^="loading_company_stock"]').val()) || 0;

  // Find matching priority list row by product_id, supplier_id, and product_type
  var priorityRow = null;
  var originalRowId = null;
  $('#priority_tbody tr').each(function() {
    var $row = $(this);
    var rowProductId = $row.find('input[name^="product_id"]').val();
    var rowSupplierId = $row.find('input[name^="supplier_id"]').val();
    var rowProductType = $row.find('input[name^="product_type"]').val();
    
    if (rowProductId == productId && rowSupplierId == supplierId && rowProductType == productType) {
      priorityRow = $row;
      originalRowId = $row.attr('id').replace('row_', '');
      return false; // Break loop
    }
  });

  if (priorityRow && priorityRow.length > 0 && originalRowId) {
    // Row exists, just update quantity
    var currentQty = parseFloat(priorityRow.find('.qty-input').val()) || 0;
    var newQty = currentQty + quantity;
    priorityRow.find('.qty-input').val(newQty);
    updateTotalCBM(originalRowId);
    priorityRow.show();
  }
  
  // Update loading row quantity or remove it
  var currentLoadingQty = parseFloat(loadingRow.find('.loading-qty-input').val()) || 0;
  var newLoadingQty = currentLoadingQty - quantity;
  
  if (newLoadingQty <= 0) {
    // Remove from loading list
    loadingRow.remove();
    updateLoadingRowNumbers();
    delete loadingOriginalQuantities[loadingRowId];
  } else {
    // Update quantity
    loadingRow.find('.loading-qty-input').val(newLoadingQty);
    loadingRow.find('.loading-qty-input').attr('data-original-qty', newLoadingQty);
    loadingOriginalQuantities[loadingRowId] = newLoadingQty;
    updateLoadingTotalCBM(loadingRowId);
  }
}

// Remove Loading Product Row
function removeLoadingRow(loadingRowId) {
  var loadingRow = $('#loading_row_' + loadingRowId);
  var currentQty = parseFloat(loadingRow.find('.loading-qty-input').val()) || 0;

  // Check if this product came from priority list by matching product_id, supplier_id, and product_type
  var productId = loadingRow.find('input[name^="loading_original_product_id"]').val() || 
                  loadingRow.find('select[name^="loading_product_id"]').val();
  var supplierId = loadingRow.find('select[name^="loading_supplier_id"]').val();
  var productType = loadingRow.find('select[name^="loading_product_type"]').val();
  
  // Find matching priority list row
  var priorityRow = null;
  $('#priority_tbody tr').each(function() {
    var $row = $(this);
    var rowProductId = $row.find('input[name^="product_id"]').val();
    var rowSupplierId = $row.find('input[name^="supplier_id"]').val();
    var rowProductType = $row.find('input[name^="product_type"]').val();
    
    if (rowProductId == productId && rowSupplierId == supplierId && rowProductType == productType) {
      priorityRow = $row;
      return false; // Break loop
    }
  });

  // If this came from priority list, restore it back
  if (priorityRow && priorityRow.length > 0 && currentQty > 0) {
    restoreToPriorityList(loadingRowId, currentQty);
  } else {
    // Simply remove the row if it's a new product
    loadingRow.remove();
    updateLoadingRowNumbers();
  }
  
  // Clean up tracking
  if (loadingOriginalQuantities[loadingRowId]) {
    delete loadingOriginalQuantities[loadingRowId];
  }
}

// Update Total CBM when quantity changes
function updateTotalCBM(rowId) {
  var row = $('#row_' + rowId);
  var qty = parseFloat(row.find('.qty-input').val()) || 0;
  var cbm = parseFloat(row.find('input[name^="cbm"]').val()) || 0;
  var totalCBM = qty * cbm;
  row.find('.total-cbm-input').val(totalCBM.toFixed(2));
  calculateGrandTotalCBM();
}

// Calculate grand total CBM
function calculateGrandTotalCBM() {
  var grandTotal = 0;
  $('.total-cbm-input').each(function() {
    var value = parseFloat($(this).val()) || 0;
    grandTotal += value;
  });
  $('#grand_total_cbm').val(grandTotal.toFixed(2));
}

// Remove row
function removeRow(rowId) {
  Swal.fire({
    title: "Are you sure?",
    text: "Do you want to remove this row?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, remove it!",
    cancelButtonText: "Cancel",
    customClass: {
      confirmButton: "btn btn-primary",
      cancelButton: "btn btn-secondary"
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // If it's an existing product, add to Loading Products
      if (typeof originalQuantities[rowId] !== 'undefined') {
        var row = $('#row_' + rowId);
        var originalQty = originalQuantities[rowId];

        if (originalQty > 0) {
          var productId = row.find('input[name^="product_id"]').val();
          var productName = row.find('input[name^="product_name"]').val();
          var itemCode = row.find('input[name^="item_code"]').val();
          var supplierId = row.find('input[name^="supplier_id"]').val() || row.find('select[name^="supplier_id"]')
            .val();
          var supplierSelect = row.find('.supplier-select');
          var supplierName = '';
          if (supplierSelect.length > 0) {
            supplierName = supplierSelect.find('option:selected').text() || '';
          } else {
            supplierName = row.find('input[name^="supplier_id"]').closest('td').find('input[type="text"]').val() ||
              '';
          }
          var productType = row.find('input[name^="product_type"]').val() || row.find(
            'select[name^="product_type"]').val();
          var typeLabel = (productType == 'ready') ? 'Ready Goods' : (productType == 'spare' ? 'Spare Parts' : '');
          var cbm = parseFloat(row.find('input[name^="cbm"]').val()) || 0;
          var pendingPoQty = parseFloat(row.find('input[name^="pending_po_qty"]').val()) || 0;
          var loadingListQty = parseFloat(row.find('input[name^="loading_list_qty"]').val()) || 0;
          var inStockQty = parseFloat(row.find('input[name^="in_stock_qty"]').val()) || 0;
          var companyStock = parseFloat(row.find('input[name^="company_stock"]').val()) || 0;

          // Check if this product already exists in Loading Products table (by product_id)
          var existingLoadingRow = null;
          $('#loading_products_tbody tr').each(function() {
            var $loadingRow = $(this);
            var loadingProductId = $loadingRow.find('input[name^="loading_product_id"]').val() ||
              $loadingRow.find('input[name^="loading_original_product_id"]').val();
            if (loadingProductId == productId) {
              existingLoadingRow = $loadingRow;
              return false; // Break the loop
            }
          });

          // If product exists in Loading Products, remove it first
          if (existingLoadingRow && existingLoadingRow.length > 0) {
            existingLoadingRow.remove();
            updateLoadingRowNumbers();
          }

          // Now add/create a new row in Loading Products with full original quantity
          var loadingRowId = 'loading_' + rowId;
          addToLoadingProducts(loadingRowId, rowId, supplierId, supplierName, productType, typeLabel, productId,
            productName, itemCode, originalQty, cbm, pendingPoQty, loadingListQty, inStockQty, companyStock);
        }
      }

      $('#row_' + rowId).remove();
      updateRowNumbers();
      calculateGrandTotalCBM();
    }
  });
}

// Add new row
function addNewRow() {
  newRowCounter++;
  var newRowId = 'new_' + newRowCounter;
  rowCounter++;

  var newRow = `
        <tr id="row_${newRowId}" data-product-id="${newRowId}">
            <td>
                ${rowCounter}
                <input type="hidden" name="old_product_id[${newRowId}]" value="0">
            </td>
            <td>
                <select class="form-control form-control-sm supplier-select" name="supplier_id[${newRowId}]" 
                    onchange="handleSupplierChange(this, '${newRowId}')" required>
                    <option value="">Select Supplier</option>
                    <?php foreach($supplier_list as $supplier): ?>
                    <option value="<?php echo $supplier->id; ?>"><?php echo addslashes($supplier->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select class="form-control form-control-sm type-select" name="product_type[${newRowId}]" 
                    onchange="handleTypeChange(this, '${newRowId}')" required>
                    <option value="">Select Type</option>
                    <option value="ready">Ready Goods</option>
                    <option value="spare">Spare Parts</option>
                </select>
            </td>
            <td>
                <select class="form-control form-control-sm product-select select2" name="product_id[${newRowId}]" 
                    id="product_select_${newRowId}" onchange="handleProductChange(this, '${newRowId}')" required>
                    <option value="">Select Product</option>
                </select>
                <input type="hidden" name="product_name[${newRowId}]" id="product_name_${newRowId}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="item_code[${newRowId}]" 
                    id="item_code_${newRowId}" readonly>
            </td>
            <td>
                <input type="number" min="0" step="1" class="form-control form-control-sm qty-input" 
                    name="quantity[${newRowId}]" value="0" 
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                    onchange="updateTotalCBM('${newRowId}')">
                <input type="hidden" name="loading_list[${newRowId}]" value="0">
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="cbm[${newRowId}]" id="cbm_${newRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm total-cbm-input" 
                    name="total_cbm[${newRowId}]" id="total_cbm_${newRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="pending_po_qty[${newRowId}]" id="pending_po_qty_${newRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_list_qty[${newRowId}]" id="loading_list_qty_${newRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="in_stock_qty[${newRowId}]" id="in_stock_qty_${newRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="company_stock[${newRowId}]" id="company_stock_${newRowId}" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow('${newRowId}')">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

  // Insert new row before the Total CBM row (at the end of tbody)
  $('#priority_tbody').append(newRow);
  $('.select2').select2({
    dropdownParent: $('#large-modal .modal-content')
  });
  updateRowNumbers();
}

// Handle type change - populate product dropdown filtered by supplier
function handleTypeChange(selectElement, rowId) {
  var type = $(selectElement).val();
  var productSelect = $('#product_select_' + rowId);

  // Get supplier_id from the row
  var supplierId = $('#row_' + rowId).find('select[name^="supplier_id"]').val() ||
    $('#row_' + rowId).find('input[name^="supplier_id"]').val();

  // Clear product select and populate with filtered products
  productSelect.html(getFilteredProducts(type, supplierId));

  // Clear all fields
  $('#item_code_' + rowId).val('');
  $('#cbm_' + rowId).val(0);
  $('#total_cbm_' + rowId).val(0);
  $('#pending_po_qty_' + rowId).val(0);
  $('#loading_list_qty_' + rowId).val(0);
  $('#in_stock_qty_' + rowId).val(0);
  $('#company_stock_' + rowId).val(0);
}

// Handle supplier change - filter products by supplier
function handleSupplierChange(selectElement, rowId) {
  var supplierId = $(selectElement).val();
  var typeSelect = $('#row_' + rowId).find('select[name^="product_type"]');
  var productSelect = $('#product_select_' + rowId);

  // If type is already selected, update product dropdown
  if (typeSelect.length && typeSelect.val()) {
    var type = typeSelect.val();
    productSelect.html(getFilteredProducts(type, supplierId));
  }

  // Clear product selection and fields if supplier changes
  productSelect.val('').trigger('change');
  $('#item_code_' + rowId).val('');
  $('#cbm_' + rowId).val(0);
  $('#total_cbm_' + rowId).val(0);
  $('#pending_po_qty_' + rowId).val(0);
  $('#loading_list_qty_' + rowId).val(0);
  $('#in_stock_qty_' + rowId).val(0);
  $('#company_stock_' + rowId).val(0);
  $('#product_name_' + rowId).val('');
}

// Check if product is already selected in Priority List (only checks Priority List, not Loading Products)
function isProductAlreadySelectedInPriorityList(productId, currentRowId) {
  var isDuplicate = false;

  // Check all product inputs in Priority List (for new rows with dropdowns)
  $('.product-select').each(function() {
    var $select = $(this);
    var selectId = $select.attr('id');

    // Extract row ID from the select ID
    var match = selectId.match(/product_select_(.+)/);
    if (match) {
      var rowId = match[1];

      // Skip the current dropdown
      if (rowId == currentRowId) {
        return true; // Continue to next iteration
      }

      // Check if this dropdown has the same product selected
      if ($select.val() == productId && $select.val() != '') {
        isDuplicate = true;
        return false; // Break the loop
      }
    }
  });

  // Also check existing rows with hidden product_id fields (priority list only)
  if (!isDuplicate) {
    $('input[name^="product_id"]').each(function() {
      var $input = $(this);
      var inputName = $input.attr('name');

      // Extract row ID from the input name (format: product_id[rowId])
      var match = inputName.match(/product_id\[(.+)\]/);
      if (match) {
        var rowId = match[1];

        // Skip the current row
        if (rowId == currentRowId) {
          return true; // Continue to next iteration
        }

        // Check if this row has the same product_id
        if ($input.val() == productId && $input.val() != '') {
          isDuplicate = true;
          return false; // Break the loop
        }
      }
    });
  }

  return isDuplicate;
}

// Check if product is already selected in Loading Products (only checks Loading Products, not Priority List)
function isProductAlreadySelectedInLoadingProducts(productId, currentRowId) {
  var isDuplicate = false;

  // Check all loading product dropdowns (for new rows)
  $('#loading_products_tbody select[name^="loading_product_id"]').each(function() {
    var $select = $(this);
    var selectId = $select.attr('id');

    // Extract row ID from the select ID
    var match = selectId.match(/loading_product_select_(.+)/);
    if (match) {
      var rowId = match[1];

      // Skip the current dropdown
      if (rowId == currentRowId) {
        return true; // Continue to next iteration
      }

      // Check if this dropdown has the same product selected
      if ($select.val() == productId && $select.val() != '') {
        isDuplicate = true;
        return false; // Break the loop
      }
    }
  });

  // Also check existing loading products with hidden product_id fields
  if (!isDuplicate) {
    $('input[name^="loading_product_id"], input[name^="loading_original_product_id"]').each(function() {
      var $input = $(this);
      var inputName = $input.attr('name');

      // Extract row ID from the input name
      var match = inputName.match(/loading_(?:original_)?product_id\[(.+)\]/);
      if (match) {
        var rowId = match[1];

        // Skip the current row
        if (rowId == currentRowId) {
          return true; // Continue to next iteration
        }

        // Check if this loading product has the same product_id
        if ($input.val() == productId && $input.val() != '') {
          isDuplicate = true;
          return false; // Break the loop
        }
      }
    });
  }

  return isDuplicate;
}

// Handle product change - AJAX call to get product details
function handleProductChange(selectElement, rowId) {
  var productId = $(selectElement).val();

  if (productId) {
    // Check if product is already selected in Priority List (only check within Priority List)
    if (isProductAlreadySelectedInPriorityList(productId, rowId)) {
      // Show error message
      Swal.fire({
        title: "Error!",
        text: "This product has already been selected in another row!",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });

      // Reset the dropdown to empty
      $(selectElement).val('').trigger('change');

      // Clear all fields for this product row
      $('#item_code_' + rowId).val('');
      $('#cbm_' + rowId).val(0);
      $('#total_cbm_' + rowId).val(0);
      $('#pending_po_qty_' + rowId).val(0);
      $('#loading_list_qty_' + rowId).val(0);
      $('#in_stock_qty_' + rowId).val(0);
      $('#company_stock_' + rowId).val(0);
      $('#product_name_' + rowId).val('');
      calculateGrandTotalCBM();

      return false; // Exit function
    }

    // Check if product exists in Loading Products - block if supplier/product type don't match
    var supplierId = $('#row_' + rowId).find('input[name^="supplier_id"]').val() || $('#row_' + rowId).find(
      'select[name^="supplier_id"]').val();
    var productType = $('#row_' + rowId).find('input[name^="product_type"]').val() || $('#row_' + rowId).find(
      'select[name^="product_type"]').val();

    // Check if product exists in Loading Products with different supplier/type
    var loadingProductRow = $('#loading_products_tbody tr').filter(function() {
      var $row = $(this);
      var rowProductId = $row.find('input[name^="loading_product_id"]').val() ||
        $row.find('input[name^="loading_original_product_id"]').val();
      return rowProductId == productId && rowProductId != '';
    }).first();

    if (loadingProductRow.length > 0) {
      var loadingSupplierId = loadingProductRow.find('input[name^="loading_supplier_id"]').val() || loadingProductRow
        .find('select[name^="loading_supplier_id"]').val();
      var loadingProductType = loadingProductRow.find('input[name^="loading_product_type"]').val() || loadingProductRow
        .find('select[name^="loading_product_type"]').val();

      // Block if supplier or product type don't match
      if (loadingSupplierId != supplierId || loadingProductType != productType) {
        Swal.fire({
          title: "Error!",
          text: "This product exists in Loading Products with a different supplier or product type. Please select the same supplier and product type to add it to Priority List.",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        });

        $(selectElement).val('').trigger('change');
        $('#item_code_' + rowId).val('');
        $('#cbm_' + rowId).val(0);
        $('#total_cbm_' + rowId).val(0);
        $('#pending_po_qty_' + rowId).val(0);
        $('#loading_list_qty_' + rowId).val(0);
        $('#in_stock_qty_' + rowId).val(0);
        $('#company_stock_' + rowId).val(0);
        $('#product_name_' + rowId).val('');
        calculateGrandTotalCBM();

        return false;
      }
    }

    // Make AJAX call to get product details
    $.ajax({
      type: "POST",
      url: "<?php echo base_url(); ?>inventory/get_purchase_order_product_details",
      data: {
        product_id: productId,
        type: '<?php echo isset($po_data['method']) ? $po_data['method'] : 'local'; ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status == 200) {
          // Get product name from selected option
          var productName = $('#product_select_' + rowId + ' option:selected').text();
          $('#product_name_' + rowId).val(productName);

          // Populate fields
          $('#item_code_' + rowId).val(res.item_code || '');
          $('#cbm_' + rowId).val(res.cbm || 0);
          $('#pending_po_qty_' + rowId).val(res.pending_po_qty || 0);
          $('#loading_list_qty_' + rowId).val(res.loading_list_qty || 0);
          $('#in_stock_qty_' + rowId).val(res.in_stock_qty || 0);
          $('#company_stock_' + rowId).val(res.company_stock || 0);

          // Recalculate total CBM
          updateTotalCBM(rowId);
        } else {
          // Clear fields on error
          $('#item_code_' + rowId).val('');
          $('#cbm_' + rowId).val(0);
          $('#total_cbm_' + rowId).val(0);
          $('#pending_po_qty_' + rowId).val(0);
          $('#loading_list_qty_' + rowId).val(0);
          $('#in_stock_qty_' + rowId).val(0);
          $('#company_stock_' + rowId).val(0);
        }
      },
      error: function() {
        // Clear fields on error
        $('#item_code_' + rowId).val('');
        $('#cbm_' + rowId).val(0);
        $('#total_cbm_' + rowId).val(0);
        $('#pending_po_qty_' + rowId).val(0);
        $('#loading_list_qty_' + rowId).val(0);
        $('#in_stock_qty_' + rowId).val(0);
        $('#company_stock_' + rowId).val(0);
      }
    });
  } else {
    // Clear fields when no product is selected
    $('#item_code_' + rowId).val('');
    $('#cbm_' + rowId).val(0);
    $('#total_cbm_' + rowId).val(0);
    $('#pending_po_qty_' + rowId).val(0);
    $('#loading_list_qty_' + rowId).val(0);
    $('#in_stock_qty_' + rowId).val(0);
    $('#company_stock_' + rowId).val(0);
  }
}

// Form submission
$('.priority-list-form').submit(function(e) {
  e.preventDefault();

  // Update CKEditor content to textarea before submission
  if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.notes) {
    CKEDITOR.instances.notes.updateElement();
  }

  $(".loader").show();
  $('.btn_verify').attr("disabled", true);
  $('.btn_verify').html('<i class="fa fa-spinner fa-spin"></i> Processing');

  var url = $(this).attr('action');
  $.ajax({
    type: 'POST',
    url: url,
    async: true,
    dataType: 'json',
    data: $(".priority-list-form").serialize(),
    success: function(res) {
      if (res.status == '200') {
        $(".loader").fadeOut("slow");
        Swal.fire({
          title: "Success!",
          text: res.message || "Priority list updated successfully!",
          icon: "success",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          title: "Error!",
          text: res.message || "Something went wrong!",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        });
        $('.btn_verify').html('Update');
        $('.btn_verify').attr("disabled", false);
        $(".loader").fadeOut("slow");
      }
    },
    error: function() {
      Swal.fire({
        title: "Error!",
        text: "Something went wrong!",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });
      $('.btn_verify').html('Update');
      $('.btn_verify').attr("disabled", false);
      $(".loader").fadeOut("slow");
    }
  });
  return false;
});

// Add Loading Product Row
function addLoadingProductRow() {
  loadingProductCounter++;
  var loadingRowId = 'new_loading_' + loadingProductCounter;
  var loadingSrNo = $('#loading_products_tbody tr').length + 1;

  var loadingRow = `
        <tr id="loading_row_${loadingRowId}">
            <td>
                ${loadingSrNo}
                <input type="hidden" name="loading_old_product_id[${loadingRowId}]" value="0">
            </td>
            <td>
                <select class="form-control form-control-sm loading-supplier-select" name="loading_supplier_id[${loadingRowId}]" 
                    onchange="handleLoadingSupplierChange(this, '${loadingRowId}')" required>
                    <option value="">Select Supplier</option>
                    <?php foreach($supplier_list as $supplier): ?>
                    <option value="<?php echo $supplier->id; ?>"><?php echo addslashes($supplier->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select class="form-control form-control-sm loading-type-select" name="loading_product_type[${loadingRowId}]" 
                    onchange="handleLoadingTypeChange(this, '${loadingRowId}')" required>
                    <option value="">Select Type</option>
                    <option value="ready">Ready Goods</option>
                    <option value="spare">Spare Parts</option>
                </select>
            </td>
            <td>
                <select class="form-control form-control-sm loading-product-select select2" name="loading_product_id[${loadingRowId}]" 
                    id="loading_product_select_${loadingRowId}" onchange="handleLoadingProductChange(this, '${loadingRowId}')" required>
                    <option value="">Select Product</option>
                </select>
                <input type="hidden" name="loading_product_name[${loadingRowId}]" id="loading_product_name_${loadingRowId}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="loading_item_code[${loadingRowId}]" 
                    id="loading_item_code_${loadingRowId}" readonly>
            </td>
            <td>
                <input type="number" min="0" step="1" class="form-control form-control-sm loading-qty-input" 
                    name="loading_quantity[${loadingRowId}]" id="loading_quantity_${loadingRowId}" value="0" required
                    data-original-qty="0"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                    onchange="updateLoadingTotalCBM('${loadingRowId}'); checkLoadingQuantityChange('${loadingRowId}');">
                <input type="hidden" name="loading_list[${loadingRowId}]" value="1">
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_cbm[${loadingRowId}]" id="loading_cbm_${loadingRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm loading-total-cbm-input" 
                    name="loading_total_cbm[${loadingRowId}]" id="loading_total_cbm_${loadingRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_pending_po_qty[${loadingRowId}]" id="loading_pending_po_qty_${loadingRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_loading_list_qty[${loadingRowId}]" id="loading_loading_list_qty_${loadingRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_in_stock_qty[${loadingRowId}]" id="loading_in_stock_qty_${loadingRowId}" readonly>
            </td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm" 
                    name="loading_company_stock[${loadingRowId}]" id="loading_company_stock_${loadingRowId}" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeLoadingRow('${loadingRowId}')">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

  $('#loading_products_tbody').append(loadingRow);
  $('.select2').select2({
    dropdownParent: $('#large-modal .modal-content')
  });
  updateLoadingRowNumbers();
}

// Handle loading product type change - filter by supplier
function handleLoadingTypeChange(selectElement, rowId) {
  var type = $(selectElement).val();
  var productSelect = $('#loading_product_select_' + rowId);

  // Get supplier_id from the row
  var supplierId = $('#loading_row_' + rowId).find('select[name^="loading_supplier_id"]').val();

  // Clear product select and populate with filtered products
  productSelect.html(getFilteredProducts(type, supplierId));

  // Clear fields
  $('#loading_item_code_' + rowId).val('');
  $('#loading_cbm_' + rowId).val(0);
  $('#loading_total_cbm_' + rowId).val(0);
  $('#loading_pending_po_qty_' + rowId).val(0);
  $('#loading_loading_list_qty_' + rowId).val(0);
  $('#loading_in_stock_qty_' + rowId).val(0);
  $('#loading_company_stock_' + rowId).val(0);
  $('#loading_product_name_' + rowId).val('');
}

// Handle loading supplier change - filter products by supplier
function handleLoadingSupplierChange(selectElement, rowId) {
  var supplierId = $(selectElement).val();
  var typeSelect = $('#loading_row_' + rowId).find('select[name^="loading_product_type"]');
  var productSelect = $('#loading_product_select_' + rowId);

  // If type is already selected, update product dropdown
  if (typeSelect.length && typeSelect.val()) {
    var type = typeSelect.val();
    productSelect.html(getFilteredProducts(type, supplierId));
  }

  // Clear product selection and fields if supplier changes
  productSelect.val('').trigger('change');
  $('#loading_item_code_' + rowId).val('');
  $('#loading_cbm_' + rowId).val(0);
  $('#loading_total_cbm_' + rowId).val(0);
  $('#loading_pending_po_qty_' + rowId).val(0);
  $('#loading_loading_list_qty_' + rowId).val(0);
  $('#loading_in_stock_qty_' + rowId).val(0);
  $('#loading_company_stock_' + rowId).val(0);
  $('#loading_product_name_' + rowId).val('');
}

// Update row numbers
function updateRowNumbers() {
  $('#priority_tbody tr').each(function(index) {
    var $firstTd = $(this).find('td:first');
    var $hiddenInputs = $firstTd.find('input[type="hidden"]').detach();
    $firstTd.empty().append((index + 1) + ' ').append($hiddenInputs);
  });
}

function initPriorityListSortable() {
  if (!$.fn.sortable) {
    return;
  }
  var fixHelper = function(e, tr) {
    var $originals = tr.children();
    var $helper = tr.clone();
    $helper.children().each(function(index) {
      $(this).width($originals.eq(index).outerWidth());
    });
    return $helper;
  };
  $('#priority_tbody').sortable({
    items: '> tr',
    helper: fixHelper,
    cancel: 'input,select,textarea,button,a',
    update: function() {
      updateRowNumbers();
    }
  });
  $('#loading_products_tbody').sortable({
    items: '> tr',
    helper: fixHelper,
    cancel: 'input,select,textarea,button,a',
    update: function() {
      updateLoadingRowNumbers();
    }
  });
}

// Handle loading product change
function handleLoadingProductChange(selectElement, rowId) {
  var productId = $(selectElement).val();

  if (productId) {
    // Check if product is already selected in Loading Products (only check within Loading Products)
    if (isProductAlreadySelectedInLoadingProducts(productId, rowId)) {
      Swal.fire({
        title: "Error!",
        text: "This product has already been selected in another Loading Products row!",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });
      $(selectElement).val('').trigger('change');
      $('#loading_item_code_' + rowId).val('');
      $('#loading_cbm_' + rowId).val(0);
      $('#loading_total_cbm_' + rowId).val(0);
      $('#loading_pending_po_qty_' + rowId).val(0);
      $('#loading_loading_list_qty_' + rowId).val(0);
      $('#loading_in_stock_qty_' + rowId).val(0);
      $('#loading_company_stock_' + rowId).val(0);
      $('#loading_product_name_' + rowId).val('');
      return false;
    }

    // Check if product exists in Priority List - allow only if supplier and product type match
    var supplierId = $('#loading_row_' + rowId).find('input[name^="loading_supplier_id"]').val() || $('#loading_row_' +
      rowId).find('select[name^="loading_supplier_id"]').val();
    var productType = $('#loading_row_' + rowId).find('input[name^="loading_product_type"]').val() || $(
      '#loading_row_' + rowId).find('select[name^="loading_product_type"]').val();

    // Check if product exists in Priority List with different supplier/type
    var priorityListRow = $('#priority_tbody tr').filter(function() {
      var $row = $(this);
      var rowProductId = $row.find('input[name^="product_id"]').val();
      var rowSupplierId = $row.find('input[name^="supplier_id"]').val() || $row.find('select[name^="supplier_id"]')
        .val();
      var rowProductType = $row.find('input[name^="product_type"]').val() || $row.find(
        'select[name^="product_type"]').val();
      return rowProductId == productId && rowProductId != '';
    }).first();

    if (priorityListRow.length > 0) {
      var prioritySupplierId = priorityListRow.find('input[name^="supplier_id"]').val() || priorityListRow.find(
        'select[name^="supplier_id"]').val();
      var priorityProductType = priorityListRow.find('input[name^="product_type"]').val() || priorityListRow.find(
        'select[name^="product_type"]').val();

      // Only allow if supplier and product type match
      if (prioritySupplierId != supplierId || priorityProductType != productType) {
        Swal.fire({
          title: "Error!",
          text: "This product exists in Priority List with a different supplier or product type. Please select the same supplier and product type to add it to Loading Products.",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        });
        $(selectElement).val('').trigger('change');
        $('#loading_item_code_' + rowId).val('');
        $('#loading_cbm_' + rowId).val(0);
        $('#loading_total_cbm_' + rowId).val(0);
        $('#loading_pending_po_qty_' + rowId).val(0);
        $('#loading_loading_list_qty_' + rowId).val(0);
        $('#loading_in_stock_qty_' + rowId).val(0);
        $('#loading_company_stock_' + rowId).val(0);
        $('#loading_product_name_' + rowId).val('');
        return false;
      }
    }

    // Make AJAX call to get product details
    $.ajax({
      type: "POST",
      url: "<?php echo base_url(); ?>inventory/get_purchase_order_product_details",
      data: {
        product_id: productId,
        type: '<?php echo isset($po_data['method']) ? $po_data['method'] : 'local'; ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status == 200) {
          // Extract product name only (remove category prefix)
          var selectedText = $('#loading_product_select_' + rowId + ' option:selected').text();
          var productName = selectedText;
          if (selectedText.indexOf(' - ') > -1) {
            // Split by " - " and take the product name part (after the dash)
            var parts = selectedText.split(' - ');
            productName = parts.length > 1 ? parts.slice(1).join(' - ') : selectedText;
          }
          $('#loading_product_name_' + rowId).val(productName);
          $('#loading_item_code_' + rowId).val(res.item_code || '');
          $('#loading_cbm_' + rowId).val(res.cbm || 0);
          $('#loading_pending_po_qty_' + rowId).val(res.pending_po_qty || 0);
          $('#loading_loading_list_qty_' + rowId).val(res.loading_list_qty || 0);
          $('#loading_in_stock_qty_' + rowId).val(res.in_stock_qty || 0);
          $('#loading_company_stock_' + rowId).val(res.company_stock || 0);
          updateLoadingTotalCBM(rowId);
        }
      }
    });
  } else {
    $('#loading_item_code_' + rowId).val('');
    $('#loading_cbm_' + rowId).val(0);
    $('#loading_total_cbm_' + rowId).val(0);
    $('#loading_pending_po_qty_' + rowId).val(0);
    $('#loading_loading_list_qty_' + rowId).val(0);
    $('#loading_in_stock_qty_' + rowId).val(0);
    $('#loading_company_stock_' + rowId).val(0);
    $('#loading_product_name_' + rowId).val('');
  }
}

</script>