<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/purchase_order/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">
          <input type="hidden" name="company_id" id="company_id"
            value="<?php echo $this->session->userdata('company_id'); ?>">
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Batch No <span class="required">*</span></label>
              <input type="text" class="form-control" placeholder="Batch No" name="voucher_no" value="" required>
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1 hidden">
            <div class="form-group">
              <label>Reference No </label>
              <input type="text" class="form-control" placeholder="Enter Refrence No" name="refrence_no">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>"
                value="<?php echo date('Y-m-d');?>" id="date_picker">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label> Loading Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="delivery_date" value="<?php echo date('Y-m-d');?>"
                id="date_picker">
            </div>
          </div>
          <input type="hidden" name="warehouse_state" id="warehouse_state" value="">
          <input type="hidden" name="gst_type" id="gst_type" value="">

          <div class="col-12 col-sm-4 mb-1">
            <label class="form-label" for="state">Warehouse <span class="required">*</span></label>
            <select class=" form-select select2" name="warehouse_id" id="warehouse_id"
              onchange="get_warehouse_details(this.value);" required>
              <option value="">Select Warehouse </option>
              <?php foreach($warehouse_list as $item){?>
              <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
              <?php }?>
            </select>
          </div>
          <div class="col-12 col-sm-8 mb-1">
            <div class="form-group">
              <label>Delivery Address<span class="required">*</span></label>
              <textarea class="form-control" placeholder="" rows="1" name="delivery_address"
                id="delivery_address"></textarea>
            </div>
          </div>

          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Mode / Terms of Payment </label>
              <input type="text" class="form-control" placeholder="Enter Mode / Terms of Payment"
                name="mode_of_payment">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Dispatch Through </label>
              <input type="text" class="form-control" placeholder="Enter Dispatch Through" name="dispatch">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Destination </label>
              <input type="text" class="form-control" placeholder="Enter Destination" name="destination">
            </div>
          </div>
          <div class="col-12 col-sm-4 mb-1">
            <div class="form-group">
              <label>Other Refrence </label>
              <input type="text" class="form-control" placeholder="Enter Other Refrence" name="other_refrence">
            </div>
          </div>
          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Terms of Delivery </label>
              <input type="text" class="form-control" placeholder="Enter Terms of Delivery" name="terms_of_delivery">
            </div>
          </div>
          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Narration</label>
              <textarea class="form-control" placeholder="" rows="1" name="narration" id="narration"></textarea>
            </div>
          </div>
          <input type="hidden" name="input_method" value="<?php echo $type; ?>">
          <div class="col-12 col-sm-12 mb-1 mt-1">
            <div class="form-group">

              <div class="mt-2">
                <div class="col-12">
                  <div id="supplier_area">
                    <!-- Supplier Row 1 -->
                    <div class="supplier-row" id="supplier_row_1" data-supplier-id="1">
                      <div class="supplier-header">
                        <div class="supplier-header-left">
                          <label>
                            <span class="supplier-badge">1</span>
                            Select Supplier <span class="required">*</span>
                          </label>
                          <select class="form-control select2 supplier-select" name="supplier_id[]" id="supplier_id_1"
                            data-toggle="select2" onchange="handleSupplierChange(this, 1)" required
                            style="width: 100%;">
                            <option value="">Select Supplier</option>
                            <?php foreach($supplier_list as $supplier){?>
                            <option value="<?php echo $supplier->id; ?>"><?php echo $supplier->name; ?></option>
                            <?php } ?>
                          </select>
                        </div>
                        <div class="supplier-header-right">
                          <button type="button" class="btn btn-refresh btn-sm waves-effect waves-float waves-light"
                            onclick="refreshSupplierProducts(1)" id="refresh_supplier_1"
                            style="display:none; min-width: 38px;" title="Refresh Products">
                            <i data-feather="refresh-cw"></i>
                          </button>
                          <button type="button" class="btn btn-remove btn-sm waves-effect waves-float waves-light"
                            onclick="removeSupplierRow(1)" id="remove_supplier_1"
                            style="display:none; min-width: 38px;">
                            <i data-feather="trash-2"></i>
                          </button>
                        </div>
                      </div>

                      <!-- Ready Stock Section -->
                      <div class="ready-stock-section mb-2">
                        <h5 class="section-heading ready-stock">
                          <i data-feather="check-circle"></i>
                          Ready Stock
                        </h5>
                        <div class="table-responsive">
                          <table class="table table-bordered table-striped product-table" id="ready_products_table_1">
                            <thead>
                              <tr>
                                <th style="width: 25%;">Product Name</th>
                                <th style="width: 10%;">Model No.</th>
                                <th style="width: 8%;">Qty.</th>
                                <th style="width: 8%;">CBM</th>
                                <th style="width: 8%;">Total CBM</th>
                                <th style="width: 8%;">Pending PO Qty</th>
                                <th style="width: 8%;">Loading List Qty</th>
                                <th style="width: 8%;">In Stock Qty</th>
                                <th style="width: 8%;">Company Stock</th>
                              </tr>
                            </thead>
                            <tbody id="ready_products_1">
                              <tr>
                                <td colspan="9" class="text-center p-2 text-muted">
                                  <i class="fa fa-info-circle"></i> Select a supplier to get Ready Goods
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>

                      <!-- Spare Part Section -->
                      <div class="spare-part-section">
                        <h5 class="section-heading spare-part">
                          <i data-feather="tool"></i>
                          Spare Part
                        </h5>
                        <div class="table-responsive">
                          <table class="table table-bordered table-striped product-table" id="spare_products_table_1">
                            <thead>
                              <tr>
                                <th style="width: 25%;">Product Name</th>
                                <th style="width: 10%;">Model No.</th>
                                <th style="width: 8%;">Qty.</th>
                                <th style="width: 8%;">CBM</th>
                                <th style="width: 8%;">Total CBM</th>
                                <th style="width: 8%;">Pending PO Qty</th>
                                <th style="width: 8%;">Loading List Qty</th>
                                <th style="width: 8%;">In Stock Qty</th>
                                <th style="width: 8%;">Company Stock</th>
                              </tr>
                            </thead>
                            <tbody id="spare_products_1">
                              <tr>
                                <td colspan="9" class="text-center p-2 text-muted">
                                  <i class="fa fa-info-circle"></i> Select a supplier to get Spare Parts
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <center>
                  <div class="col-md-12 pl-0 m-auto mt-2">
                    <button type="button" class="btn btn-add-supplier waves-effect" onclick="addSupplierRow()">
                      <i class="fa fa-plus"></i> Add Supplier
                    </button>
                  </div>
                </center>
              </div>
            </div>
          </div>
          
          <div class="col-12 col-sm-12 mb-1">
            <div class="table-responsive">
              <div class="col-lg-12 no-pad">
                <table class="table table-striped table-bordered mn-table mt-1">
                  <tbody>
                    <tr>
                      <td colspan="4" class="text-right" style="width:80%">
                        <label style="float:right;display: contents;">Total CBM</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="total_cbm"
                            id="total_cbm" value="0" placeholder="Total CBM"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
          </div>
        </div>
        <?php echo form_close(); ?>
        <!--/ form -->
      </div>
    </div>
  </div>
</div>
<script>
var supplierRowCount = 1;
var productRowCounts = {}; // Track product row counts per supplier and section

// Initialize product row counts
productRowCounts['ready'] = {};
productRowCounts['spare'] = {};
productRowCounts['ready'][1] = 0;
productRowCounts['spare'][1] = 0;

// Generate product options for JavaScript
var readyProductsOptions = '';
<?php if(isset($ready_products_list) && !empty($ready_products_list)){ 
  foreach($ready_products_list as $item){ ?>
readyProductsOptions += '<option value="<?php echo $item->id; ?>"><?php echo addslashes(($item->category_name ?? '-') . ' - ' . $item->name); ?></option>';
<?php } } ?>

var spareProductsOptions = '';
<?php if(isset($spare_products_list) && !empty($spare_products_list)){ 
  foreach($spare_products_list as $item){ ?>
spareProductsOptions += '<option value="<?php echo $item->id; ?>"><?php echo addslashes(($item->category_name ?? '-') . ' - ' . $item->name); ?></option>';
<?php } } ?>

// Add new supplier row
function addSupplierRow() {
  supplierRowCount++;
  var supplierRowId = supplierRowCount;
  productRowCounts['ready'][supplierRowId] = 1;
  productRowCounts['spare'][supplierRowId] = 1;

  var supplierRowHtml = `
    <div class="supplier-row" id="supplier_row_${supplierRowId}" data-supplier-id="${supplierRowId}">
      <div class="supplier-header">
        <div class="supplier-header-left">
          <label>
            <span class="supplier-badge">${supplierRowId}</span>
            Select Supplier <span class="required">*</span>
          </label>
          <select class="form-control select2 supplier-select" name="supplier_id[]" id="supplier_id_${supplierRowId}" 
            data-toggle="select2" onchange="handleSupplierChange(this, ${supplierRowId})" required style="width: 100%;">
            <option value="">Select Supplier</option>
            <?php foreach($supplier_list as $supplier){?>
            <option value="<?php echo $supplier->id; ?>"><?php echo $supplier->name; ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="supplier-header-right">
          <button type="button" class="btn btn-refresh btn-sm waves-effect waves-float waves-light" 
            onclick="refreshSupplierProducts(${supplierRowId})" id="refresh_supplier_${supplierRowId}" 
            style="display:none; min-width: 38px;" title="Refresh Products">
            <i data-feather="refresh-cw"></i>
          </button>
          <button type="button" class="btn btn-remove btn-sm waves-effect waves-float waves-light" 
            onclick="removeSupplierRow(${supplierRowId})" id="remove_supplier_${supplierRowId}" style="display:none; min-width: 38px;">
            <i data-feather="trash-2"></i>
          </button>
        </div>
      </div>

      <!-- Ready Stock Section -->
      <div class="ready-stock-section mb-2">
        <h5 class="section-heading ready-stock">
          <i data-feather="check-circle"></i>
          Ready Stock
        </h5>
        <div class="table-responsive">
          <table class="table table-bordered table-striped product-table" id="ready_products_table_${supplierRowId}">
            <thead>
              <tr>
                <th style="width: 25%;">Product Name</th>
                <th style="width: 10%;">Model No.</th>
                <th style="width: 8%;">Qty.</th>
                <th style="width: 8%;">CBM</th>
                <th style="width: 8%;">Total CBM</th>
                <th style="width: 8%;">Pending PO Qty</th>
                <th style="width: 8%;">Loading List Qty</th>
                <th style="width: 8%;">In Stock Qty</th>
                <th style="width: 8%;">Company Stock</th>
              </tr>
            </thead>
            <tbody id="ready_products_${supplierRowId}">
              <tr>
                <td colspan="9" class="text-center p-2 text-muted">
                  <i class="fa fa-info-circle"></i> Select a supplier to get Ready Goods
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Spare Part Section -->
      <div class="spare-part-section">
        <h5 class="section-heading spare-part">
          <i data-feather="tool"></i>
          Spare Part
        </h5>
        <div class="table-responsive">
          <table class="table table-bordered table-striped product-table" id="spare_products_table_${supplierRowId}">
            <thead>
              <tr>
                <th style="width: 25%;">Product Name</th>
                <th style="width: 10%;">Model No.</th>
                <th style="width: 8%;">Qty.</th>
                <th style="width: 8%;">CBM</th>
                <th style="width: 8%;">Total CBM</th>
                <th style="width: 8%;">Pending PO Qty</th>
                <th style="width: 8%;">Loading List Qty</th>
                <th style="width: 8%;">In Stock Qty</th>
                <th style="width: 8%;">Company Stock</th>
              </tr>
            </thead>
            <tbody id="spare_products_${supplierRowId}">
              <tr>
                <td colspan="9" class="text-center p-2 text-muted">
                  <i class="fa fa-info-circle"></i> Select a supplier to get Spare Parts
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `;

  $('#supplier_area').append(supplierRowHtml);
  $('.select2').select2();
  updateSupplierDropdowns();
  updateSupplierRemoveButtons();
  // Initialize Feather icons for the new row
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
}

function updateSupplierRemoveButtons() {
  var $rows = $('#supplier_area .supplier-row');
  $rows.find('.btn-remove').hide();
  if ($rows.length > 1) {
    $rows.last().find('.btn-remove').show();
  }
}

// Remove supplier row
function removeSupplierRow(supplierRowId) {
  if (supplierRowCount > 1) {
    $('#supplier_row_' + supplierRowId).remove();
    supplierRowCount--;
    updateSupplierDropdowns();
    updateSupplierRemoveButtons();
    // Recalculate total CBM after removing a supplier row
    calculateGrandTotalCBM();
  } else {
    Swal.fire({
      title: "Error!",
      text: "At least one supplier row is required!",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
  }
}

// Handle supplier change - prevent duplicate selection
function handleSupplierChange(selectElement, supplierRowId) {
  var selectedSupplierId = $(selectElement).val();

  if (selectedSupplierId) {
    // Check all other supplier dropdowns
    var isDuplicate = false;
    $('.supplier-select').each(function() {
      var currentRowId = $(this).attr('id').replace('supplier_id_', '');
      if (currentRowId != supplierRowId && $(this).val() == selectedSupplierId) {
        isDuplicate = true;
        return false; // Break the loop
      }
    });
    
    // If duplicate supplier found, show alert, clear tables, and exit
    if (isDuplicate) {
      Swal.fire({
        title: "Error!",
        text: "This supplier is already selected in another row!",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      }).then(function() {
        // Clear product tables
        $('#ready_products_' + supplierRowId + ' tr').remove();
        $('#spare_products_' + supplierRowId + ' tr').remove();
        
        // Reset product row counts
        if (!productRowCounts['ready']) productRowCounts['ready'] = {};
        if (!productRowCounts['spare']) productRowCounts['spare'] = {};
        productRowCounts['ready'][supplierRowId] = 0;
        productRowCounts['spare'][supplierRowId] = 0;
        
        // Show initial message
        $('#ready_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2 text-muted"><i class="fa fa-info-circle"></i> Select a supplier to get Ready Goods</td></tr>');
        $('#spare_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2 text-muted"><i class="fa fa-info-circle"></i> Select a supplier to get Spare Parts</td></tr>');
        
        // Hide refresh button
        $('#refresh_supplier_' + supplierRowId).hide();
        
        // Reset dropdown
        $(selectElement).val('').trigger('change');
        
        // Recalculate totals
        calculateGrandTotalCBM();
      });
      return; // Exit function early
    }

    // Clear existing products (keep table structure, just clear tbody)
    $('#ready_products_' + supplierRowId + ' tr').remove();
    $('#spare_products_' + supplierRowId + ' tr').remove();
    
    // Reset product row counts
    if (!productRowCounts['ready']) productRowCounts['ready'] = {};
    if (!productRowCounts['spare']) productRowCounts['spare'] = {};
    productRowCounts['ready'][supplierRowId] = 0;
    productRowCounts['spare'][supplierRowId] = 0;

    // Show loading indicator
    $('#ready_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2"><i class="fa fa-spinner fa-spin"></i> Loading products...</td></tr>');
    $('#spare_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2"><i class="fa fa-spinner fa-spin"></i> Loading products...</td></tr>');

    // Fetch products by supplier
    $.ajax({
      type: "POST",
      url: "<?php echo base_url(); ?>inventory/get_products_by_supplier",
      data: { 
        supplier_id: selectedSupplierId,
        type: '<?php echo $type; ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status == 200) {
          // Clear loading indicators (remove loading row)
          $('#ready_products_' + supplierRowId + ' tr').remove();
          $('#spare_products_' + supplierRowId + ' tr').remove();

          // Populate ready products
          if (res.ready_products && res.ready_products.length > 0) {
            res.ready_products.forEach(function(product) {
              createProductRowWithData('ready', supplierRowId, product);
            });
          } else {
            // Show no data found for ready products
            $('#ready_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2 text-muted">No Ready Goods found</td></tr>');
          }

          // Populate spare products
          if (res.spare_products && res.spare_products.length > 0) {
            res.spare_products.forEach(function(product) {
              createProductRowWithData('spare', supplierRowId, product);
            });
          } else {
            // Show no data found for spare products
            $('#spare_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2 text-muted">No Spare Parts found</td></tr>');
          }

          // Initialize select2 and feather icons
          $('.select2').select2();
          if (typeof feather !== 'undefined') {
            feather.replace();
          }
          
          // Recalculate totals
          calculateGrandTotalCBM();
        } else {
          $('#ready_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-3 text-muted">No products found</td></tr>');
          $('#spare_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-3 text-muted">No products found</td></tr>');
        }
      },
      error: function() {
        $('#ready_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-3 text-danger">Error loading products</td></tr>');
        $('#spare_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-3 text-danger">Error loading products</td></tr>');
      }
    });
  } else {
    // Clear products if supplier is deselected - show initial message
    $('#ready_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2 text-muted"><i class="fa fa-info-circle"></i> Select a supplier to get Ready Goods</td></tr>');
    $('#spare_products_' + supplierRowId).html('<tr><td colspan="9" class="text-center p-2 text-muted"><i class="fa fa-info-circle"></i> Select a supplier to get Spare Parts</td></tr>');
    productRowCounts['ready'][supplierRowId] = 0;
    productRowCounts['spare'][supplierRowId] = 0;
    // Hide refresh button when supplier is deselected
    $('#refresh_supplier_' + supplierRowId).hide();
  }

  // Show refresh button if supplier is selected
  if (selectedSupplierId) {
    $('#refresh_supplier_' + supplierRowId).show();
  } else {
    $('#refresh_supplier_' + supplierRowId).hide();
  }
}

// Refresh supplier products - fetch only new products that don't exist
function refreshSupplierProducts(supplierRowId) {
  var selectedSupplierId = $('#supplier_id_' + supplierRowId).val();
  
  if (!selectedSupplierId) {
    Swal.fire({
      title: "Warning!",
      text: "Please select a supplier first!",
      icon: "warning",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
    return;
  }

  // Get existing product IDs from both ready and spare tables
  var existingProductIds = [];
  
  // Get product IDs from ready products
  $('#ready_products_' + supplierRowId + ' .product-row').each(function() {
    var productId = $(this).attr('data-product-id');
    if (productId) {
      existingProductIds.push(productId.toString());
    }
  });
  
  // Get product IDs from spare products
  $('#spare_products_' + supplierRowId + ' .product-row').each(function() {
    var productId = $(this).attr('data-product-id');
    if (productId) {
      existingProductIds.push(productId.toString());
    }
  });

  // Show loading indicator on refresh button
  var $refreshBtn = $('#refresh_supplier_' + supplierRowId);
  var originalHtml = $refreshBtn.html();
  $refreshBtn.html('<i data-feather="refresh-cw"></i>').prop('disabled', true);
  $refreshBtn.addClass('spinning');
  if (typeof feather !== 'undefined') {
    feather.replace();
  }

  // Fetch products by supplier
  $.ajax({
    type: "POST",
    url: "<?php echo base_url(); ?>inventory/get_products_by_supplier",
    data: { 
      supplier_id: selectedSupplierId,
      type: '<?php echo $type; ?>'
    },
    dataType: 'json',
    success: function(res) {
      // Restore refresh button
      $refreshBtn.html(originalHtml).prop('disabled', false).removeClass('spinning');
      if (typeof feather !== 'undefined') {
        feather.replace();
      }

      if (res.status == 200) {
        var newProductsAdded = 0;

        // Process ready products - only add new ones
        if (res.ready_products && res.ready_products.length > 0) {
          res.ready_products.forEach(function(product) {
            var productId = product.id.toString();
            // Only add if product doesn't exist
            if (existingProductIds.indexOf(productId) === -1) {
              createProductRowWithData('ready', supplierRowId, product);
              existingProductIds.push(productId); // Add to list to avoid duplicates in same refresh
              newProductsAdded++;
            }
          });
        }

        // Process spare products - only add new ones
        if (res.spare_products && res.spare_products.length > 0) {
          res.spare_products.forEach(function(product) {
            var productId = product.id.toString();
            // Only add if product doesn't exist
            if (existingProductIds.indexOf(productId) === -1) {
              createProductRowWithData('spare', supplierRowId, product);
              existingProductIds.push(productId); // Add to list to avoid duplicates in same refresh
              newProductsAdded++;
            }
          });
        }

        // Show message if no new products were added
        if (newProductsAdded === 0) {
          Swal.fire({
            title: "Info!",
            text: "No new products found. All products are already loaded.",
            icon: "info",
            customClass: {
              confirmButton: "btn btn-primary"
            },
            buttonsStyling: !1,
            timer: 2000
          });
        } else {
          // Show success message
          Swal.fire({
            title: "Success!",
            text: newProductsAdded + " new product(s) added successfully!",
            icon: "success",
            customClass: {
              confirmButton: "btn btn-primary"
            },
            buttonsStyling: !1,
            timer: 2000
          });
        }

        // Initialize select2 and feather icons
        $('.select2').select2();
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
        
        // Recalculate totals
        calculateGrandTotalCBM();
      } else {
        Swal.fire({
          title: "Error!",
          text: "Failed to fetch products!",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        });
      }
    },
    error: function() {
      // Restore refresh button
      $refreshBtn.html(originalHtml).prop('disabled', false).removeClass('spinning');
      if (typeof feather !== 'undefined') {
        feather.replace();
      }
      
      Swal.fire({
        title: "Error!",
        text: "Error loading products. Please try again.",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });
    }
  });
}

// Update supplier dropdowns to exclude already selected suppliers
function updateSupplierDropdowns() {
  var selectedSuppliers = [];
  $('.supplier-select').each(function() {
    if ($(this).val()) {
      selectedSuppliers.push($(this).val());
    }
  });

  $('.supplier-select').each(function() {
    var currentValue = $(this).val();
    $(this).find('option').each(function() {
      var optionValue = $(this).val();
      if (optionValue && optionValue != currentValue && selectedSuppliers.indexOf(optionValue) !== -1) {
        $(this).prop('disabled', true);
      } else {
        $(this).prop('disabled', false);
      }
    });
  });
}

// Add product row to a section
function addProductRow(sectionType, supplierRowId) {
  if (!productRowCounts[sectionType][supplierRowId]) {
    productRowCounts[sectionType][supplierRowId] = 0;
  }
  productRowCounts[sectionType][supplierRowId]++;
  var productRowId = productRowCounts[sectionType][supplierRowId];

  var sectionPrefix = sectionType === 'ready' ? 'ready' : 'spare';
  var sectionLabel = sectionType === 'ready' ? 'Ready Stock' : 'Spare Part';

  var productRowHtml = `
    <tr class="product-row ${sectionType}-product" id="${sectionPrefix}_product_${supplierRowId}_${productRowId}" data-product-id="${productRowId}">
      <td>
        <input type="hidden" name="${sectionPrefix}_product_id[${supplierRowId}][]" value="">
        <span class="product-name-display"></span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_model_no[${supplierRowId}][]" id="${sectionPrefix}_model_no_${supplierRowId}_${productRowId}" value="">
        <span class="model-no-display"></span>
      </td>
      <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm" name="${sectionPrefix}_qty[${supplierRowId}][]" 
          id="${sectionPrefix}_qty_${supplierRowId}_${productRowId}" value="0" onkeyup="calculateTotalCBM('${sectionType}', ${supplierRowId}, ${productRowId})" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_cbm[${supplierRowId}][]" id="${sectionPrefix}_cbm_${supplierRowId}_${productRowId}" value="0">
        <span class="cbm-display" id="${sectionPrefix}_cbm_display_${supplierRowId}_${productRowId}">0</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_total_cbm[${supplierRowId}][]" id="${sectionPrefix}_total_cbm_${supplierRowId}_${productRowId}" value="0">
        <span class="total-cbm-display" id="${sectionPrefix}_total_cbm_display_${supplierRowId}_${productRowId}">0</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_pending_po_qty[${supplierRowId}][]" id="${sectionPrefix}_pending_po_qty_${supplierRowId}_${productRowId}" value="0">
        <span class="pending-po-qty-display">0</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_loading_list_qty[${supplierRowId}][]" id="${sectionPrefix}_loading_list_qty_${supplierRowId}_${productRowId}" value="0">
        <span class="loading-list-qty-display">0</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_in_stock_qty[${supplierRowId}][]" id="${sectionPrefix}_in_stock_qty_${supplierRowId}_${productRowId}" value="0">
        <span class="in-stock-qty-display">0</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_company_stock[${supplierRowId}][]" id="${sectionPrefix}_company_stock_${supplierRowId}_${productRowId}" value="0">
        <span class="company-stock-display">0</span>
      </td>
    </tr>
  `;

  $('#' + sectionPrefix + '_products_' + supplierRowId).append(productRowHtml);
  // Initialize Feather icons for the new row
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
  // Recalculate total CBM after adding a new row
  calculateGrandTotalCBM();
}


// Create "No product selected" message row
function createNoProductRow(sectionType, supplierRowId) {
  var sectionPrefix = sectionType === 'ready' ? 'ready' : 'spare';
  var sectionLabel = sectionType === 'ready' ? 'Ready Goods' : 'Spare Parts';
  
  var noProductRowHtml = `
    <tr class="no-product-row">
      <td colspan="9" class="text-center p-2 text-muted">
        <i class="fa fa-info-circle"></i> No product selected
      </td>
    </tr>
  `;
  
  $('#' + sectionPrefix + '_products_' + supplierRowId).html(noProductRowHtml);
}

// Create product row with pre-selected data
function createProductRowWithData(sectionType, supplierRowId, productData) {
  if (!productRowCounts[sectionType]) {
    productRowCounts[sectionType] = {};
  }
  if (!productRowCounts[sectionType][supplierRowId]) {
    productRowCounts[sectionType][supplierRowId] = 0;
  }
  productRowCounts[sectionType][supplierRowId]++;
  var productRowId = productRowCounts[sectionType][supplierRowId];

  var sectionPrefix = sectionType === 'ready' ? 'ready' : 'spare';
  
  // Escape HTML to prevent XSS
  var categoryName = $('<div>').text(productData.category_name || '-').html();
  var productName = $('<div>').text(productData.name || '').html();
  var displayName = productName;

  var productRowHtml = `
    <tr class="product-row ${sectionType}-product" id="${sectionPrefix}_product_${supplierRowId}_${productRowId}" data-product-id="${productData.id}">
      <td>
        <input type="hidden" name="${sectionPrefix}_product_id[${supplierRowId}][]" value="${productData.id}">
        <span class="product-name-display">${displayName}</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_model_no[${supplierRowId}][]" id="${sectionPrefix}_model_no_${supplierRowId}_${productRowId}" value="${productData.item_code || ''}">
        <span class="model-no-display">${productData.item_code || ''}</span>
      </td>
      <td>
        <input type="number" min="0" step="1" class="form-control form-control-sm" name="${sectionPrefix}_qty[${supplierRowId}][]" 
          id="${sectionPrefix}_qty_${supplierRowId}_${productRowId}" value="0" onkeyup="calculateTotalCBM('${sectionType}', ${supplierRowId}, ${productRowId})" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_cbm[${supplierRowId}][]" id="${sectionPrefix}_cbm_${supplierRowId}_${productRowId}" value="${productData.cbm || 0}">
        <span class="cbm-display" id="${sectionPrefix}_cbm_display_${supplierRowId}_${productRowId}">${productData.cbm || 0}</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_total_cbm[${supplierRowId}][]" id="${sectionPrefix}_total_cbm_${supplierRowId}_${productRowId}" value="0">
        <span class="total-cbm-display" id="${sectionPrefix}_total_cbm_display_${supplierRowId}_${productRowId}">0</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_pending_po_qty[${supplierRowId}][]" id="${sectionPrefix}_pending_po_qty_${supplierRowId}_${productRowId}" value="${productData.pending_po_qty || 0}">
        <span class="pending-po-qty-display">${productData.pending_po_qty || 0}</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_loading_list_qty[${supplierRowId}][]" id="${sectionPrefix}_loading_list_qty_${supplierRowId}_${productRowId}" value="${productData.loading_list_qty || 0}">
        <span class="loading-list-qty-display">${productData.loading_list_qty || 0}</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_in_stock_qty[${supplierRowId}][]" id="${sectionPrefix}_in_stock_qty_${supplierRowId}_${productRowId}" value="${productData.in_stock_qty || 0}">
        <span class="in-stock-qty-display">${productData.in_stock_qty || 0}</span>
      </td>
      <td>
        <input type="hidden" name="${sectionPrefix}_company_stock[${supplierRowId}][]" id="${sectionPrefix}_company_stock_${supplierRowId}_${productRowId}" value="${productData.company_stock || 0}">
        <span class="company-stock-display">${productData.company_stock || 0}</span>
      </td>
    </tr>
  `;

  console.log('#' + sectionPrefix + '_products_' + supplierRowId)

  $('#' + sectionPrefix + '_products_' + supplierRowId).append(productRowHtml);
}

// Remove product row
function removeProductRow(sectionType, supplierRowId, productRowId) {
  var sectionPrefix = sectionType === 'ready' ? 'ready' : 'spare';
  var productRow = $('#' + sectionPrefix + '_product_' + supplierRowId + '_' + productRowId);
  var tbody = $('#' + sectionPrefix + '_products_' + supplierRowId);
  
  // Remove the product row
  productRow.remove();
  
  // Check if there are any product rows left in the table
  var remainingProductRows = tbody.find('.product-row');
  
  // If no products left, show "No product selected" message
  if (remainingProductRows.length === 0) {
    createNoProductRow(sectionType, supplierRowId);
  }
  
  // Recalculate total CBM after removing a row
  calculateGrandTotalCBM();
}

// Update remove buttons visibility
function updateRemoveButtons(sectionType, supplierRowId) {
  var sectionPrefix = sectionType === 'ready' ? 'ready' : 'spare';
  var productRows = $('#' + sectionPrefix + '_products_' + supplierRowId + ' .product-row');

  // Always show remove buttons for all rows
  productRows.each(function() {
    var rowId = $(this).attr('id');
    var match = rowId.match(/_(\d+)$/);
    if (match) {
      var productRowId = match[1];
      $('#remove_' + sectionPrefix + '_' + supplierRowId + '_' + productRowId).show();
    }
  });
}

// Calculate Total CBM (CBM * Qty)
function calculateTotalCBM(sectionType, supplierRowId, productRowId) {
  var sectionPrefix = sectionType === 'ready' ? 'ready' : 'spare';
  var cbm = parseFloat($('#' + sectionPrefix + '_cbm_' + supplierRowId + '_' + productRowId).val()) || 0;
  var qty = parseFloat($('#' + sectionPrefix + '_qty_' + supplierRowId + '_' + productRowId).val()) || 0;
  var totalCBM = cbm * qty;
  var totalCBMFormatted = totalCBM.toFixed(2);
  
  // Update hidden input
  $('#' + sectionPrefix + '_total_cbm_' + supplierRowId + '_' + productRowId).val(totalCBMFormatted);
  // Update display
  $('#' + sectionPrefix + '_total_cbm_display_' + supplierRowId + '_' + productRowId).text(totalCBMFormatted);
  
  // Calculate and update total CBM
  calculateGrandTotalCBM();
}

// Calculate sum of all total CBM values
function calculateGrandTotalCBM() {
  var grandTotalCBM = 0;
  
  // Sum all ready total CBM values
  $('input[name^="ready_total_cbm"]').each(function() {
    var value = parseFloat($(this).val()) || 0;
    grandTotalCBM += value;
  });
  
  // Sum all spare total CBM values
  $('input[name^="spare_total_cbm"]').each(function() {
    var value = parseFloat($(this).val()) || 0;
    grandTotalCBM += value;
  });
  
  // Update the total CBM field
  $('#total_cbm').val(grandTotalCBM.toFixed(2));
}

// Note: Product selection is now handled automatically when supplier is selected
// Products are displayed as readonly text inputs, so handleProductChange is no longer needed

// Initialize on page load
$(document).ready(function() {
  $('.select2').select2();
  // Initialize Feather icons
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
  // Calculate initial total CBM
  calculateGrandTotalCBM();
  updateSupplierRemoveButtons();
  
  // Prevent form submission on Enter key press (except in textareas)
  $('form.add-ajax-redirect-form').on('keydown', 'input:not(textarea), select', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      return false;
    }
  });
  
  // Also prevent Enter key on the form level
  $('form.add-ajax-redirect-form').on('keypress', function(e) {
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

// Get warehouse details and populate delivery address
function get_warehouse_details(warehouseId) {
  $(".loader").show();
  var a = {
    supplier_id: warehouseId,
  };
  $.ajax({
    type: "POST",
    url: "<?php echo base_url()?>inventory/get_warehouse_details",
    data: a,
    success: function(res) {
      if (res.status == 200) {
        $('#delivery_address').val(res.address);
        $('#warehouse_state').val(res.state_id);
        $(".loader").fadeOut("slow");
      } else {
        $('#delivery_address').val('');
        $(".loader").fadeOut("slow");
      }
    }
  })
}
</script>
