
<link rel="stylesheet" href="<?php echo base_url('assets/css/po.css'); ?>">
<style>
  #invoice_supplier_section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    margin-top: 18px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  }

  .invoice-supplier-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #5a79c0;
  }

  .invoice-supplier-header h5 {
    margin: 0;
    color: #212529;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .invoice-supplier-header .badge {
    background: #5a79c0;
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: .85rem;
    font-weight: 500;
  }

  .invoice-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    border: 2px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .invoice-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
  }

  .invoice-number-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #5a79c0 0%, #4a6ba8 100%);
    color: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
  }

  .invoice-product-count {
    background: #f8f9fa;
    color: #6c757d;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: .85rem;
    font-weight: 500;
    border: 1px solid #dee2e6;
  }

  .invoice-supplier-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    font-size: .95rem;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .invoice-supplier-label .required {
    color: #dc3545;
    font-weight: 700;
  }

  .invoice-supplier-select,
  .invoice-field-input,
  .invoice-field-textarea {
    border: 2px solid #ced4da;
    border-radius: 8px;
    padding: 10px 15px;
    font-size: .95rem;
    background-color: #fff;
    width: 100%;
  }

  .invoice-field-group {
    margin-bottom: 15px;
  }

  .invoice-field-group:last-child {
    margin-bottom: 0;
  }

  .invoice-field-textarea {
    min-height: 80px;
    resize: vertical;
  }

  .grand-total-section {
    background: #fff;
    border: 1px solid #e4e9f2;
    border-radius: 10px;
    padding: 14px;
  }

  .grand-total-section h5 {
    margin: 0 0 12px 0;
    font-weight: 600;
    color: #212529;
  }

  #grand-total-row td {
    background-color: #fafafc;
    color: #000;
    font-weight: 700;
  }
</style>
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/loading_list_po/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
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

                      <div class="products-section mb-2">
                        <div class="table-responsive">
                          <table class="table table-bordered table-striped product-table" id="products_table_1" style="width: 1600px;">
                            <thead>
                              <tr>
                                <th>Invoice</th>
                                <th>Product Name</th>
                                <th>Model No.</th>
                                <th>Quantity</th>
                                <th>Unit Price (RMB)</th>
                                <th>Official Quantity</th>
                                <th>Black Qty</th>
                                <th>Total Amount (RMB)</th>
                                <th>Official CI Unit Price (USD)</th>
                                <th>Total Amount (USD)</th>
                                <th>Black Total Price</th>
                                <th>PKG (ctn)</th>
                                <th>N.W. (kg)</th>
                                <th>Total N.W. (kg)</th>
                                <th>G.W. (kg)</th>
                                <th>Total G.W. (kg)</th>
                                <th>L</th>
                                <th>W</th>
                                <th>H</th>
                                <th>Total CBM</th>
                              </tr>
                            </thead>
                            <tbody id="products_1">
                              <tr>
                                <td colspan="20" class="text-center p-2 text-muted">
                                  <i class="fa fa-info-circle"></i> Select a supplier to get products
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
            <div class="grand-total-section">
              <h5><i class="fa fa-calculator"></i> Grand Total</h5>
              <div class="table-responsive">
                <table class="table table-bordered table-striped product-table" style="width: 1600px;">
                  <thead>
                    <tr>
                      <th>Invoice</th>
                      <th>Product Name</th>
                      <th>Model No.</th>
                      <th>Quantity</th>
                      <th>Unit Price (RMB)</th>
                      <th>Official Quantity</th>
                      <th>Black Qty</th>
                      <th>Total Amount (RMB)</th>
                      <th>Official CI Unit Price (USD)</th>
                      <th>Total Amount (USD)</th>
                      <th>Black Total Price</th>
                      <th>PKG (ctn)</th>
                      <th>N.W. (kg)</th>
                      <th>Total N.W. (kg)</th>
                      <th>G.W. (kg)</th>
                      <th>Total G.W. (kg)</th>
                      <th>L</th>
                      <th>W</th>
                      <th>H</th>
                      <th>Total CBM</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr id="grand-total-row">
                      <td colspan="3" style="text-align:right; padding:10px;"><strong>Grand Total:</strong></td>
                      <td class="grand-total-qty">0</td>
                      <td class="grand-total-unit-price-rmb">0.00</td>
                      <td class="grand-total-official-qty">0</td>
                      <td class="grand-total-black-qty">0</td>
                      <td class="grand-total-amount-rmb">0.00</td>
                      <td class="grand-total-official-ci-unit-price-usd">0.00</td>
                      <td class="grand-total-amount-usd">0.00</td>
                      <td class="grand-total-black-total-price">0.00</td>
                      <td class="grand-total-pkg-ctn">0</td>
                      <td class="grand-total-nw-kg">0.00</td>
                      <td class="grand-total-total-nw">0.00</td>
                      <td class="grand-total-gw-kg">0.00</td>
                      <td class="grand-total-total-gw">0.00</td>
                      <td class="grand-total-length">0.00</td>
                      <td class="grand-total-width">0.00</td>
                      <td class="grand-total-height">0.00</td>
                      <td class="grand-total-total-cbm">0.000000</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-12 mb-1">
            <div id="invoice_supplier_section" style="display:none;">
              <div class="invoice-supplier-header">
                <h5>
                  Select Supplier for Each Invoice
                  <span class="badge" id="invoice_count_badge">0</span>
                </h5>
              </div>
              <div id="invoice_supplier_dropdowns" class="row"></div>
            </div>
          </div>

          <div class="col-12 col-sm-12 mb-1 d-none">
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
var productRowCounts = {}; // Track product row counts per supplier row
var supplierOptions = '';

<?php foreach($supplier_list as $supplier) { ?>
supplierOptions += '<option value="<?php echo $supplier->id; ?>"><?php echo addslashes($supplier->name); ?></option>';
<?php } ?>

// Initialize product row count for first supplier row
productRowCounts[1] = 0;

function getCurrentDateISO() {
  var today = new Date();
  return today.getFullYear() + '-' +
    String(today.getMonth() + 1).padStart(2, '0') + '-' +
    String(today.getDate()).padStart(2, '0');
}

function updateInvoiceSuppliers() {
  var invoiceMap = {};

  $('.invoice-select').each(function() {
    var invoiceNo = $(this).val();
    var productId = $(this).data('product-id');

    if (invoiceNo && invoiceNo !== '') {
      if (!invoiceMap[invoiceNo]) {
        invoiceMap[invoiceNo] = [];
      }
      invoiceMap[invoiceNo].push(productId);
    }
  });

  var uniqueInvoices = Object.keys(invoiceMap).sort(function(a, b) {
    return parseInt(a, 10) - parseInt(b, 10);
  });

  $('#invoice_supplier_dropdowns').empty();

  if (uniqueInvoices.length > 0) {
    $('#invoice_supplier_section').slideDown(250);
    $('#invoice_count_badge').text(uniqueInvoices.length);

    uniqueInvoices.forEach(function(invoiceNo) {
      var productCount = invoiceMap[invoiceNo].length;
      var productText = productCount === 1 ? 'product' : 'products';
      var currentDate = getCurrentDateISO();

      var dropdownHtml = '<div class="col-md-6">' +
        '<div class="invoice-card">' +
        '<div class="invoice-card-header">' +
        '<span class="invoice-number-badge">' +
        '<i class="fa fa-file"></i> Invoice No. ' + invoiceNo +
        '</span>' +
        '<span class="invoice-product-count">' +
        '<i class="fa fa-dropbox"></i> ' + productCount + ' ' + productText +
        '</span>' +
        '</div>' +
        '<label class="invoice-supplier-label">' +
        '<i class="fa fa-truck"></i> Select Supplier <span class="required">*</span>' +
        '</label>' +
        '<select class="form-control invoice-supplier-select" name="invoice_supplier[' + invoiceNo + ']" ' +
        'id="invoice_supplier_' + invoiceNo + '" required>' +
        '<option value="">-- Choose Supplier --</option>' +
        supplierOptions +
        '</select>' +
        '<div class="invoice-field-group">' +
        '<label class="invoice-supplier-label">' +
        '<i class="fa fa-file-text"></i> Invoice Info' +
        '</label>' +
        '<input type="text" class="form-control invoice-field-input" name="invoice[' + invoiceNo + ']" ' +
        'id="invoice_' + invoiceNo + '" placeholder="Enter invoice information">' +
        '</div>' +
        '<div class="invoice-field-group">' +
        '<label class="invoice-supplier-label">' +
        '<i class="fa fa-calendar"></i> Invoice Date <span class="required">*</span>' +
        '</label>' +
        '<input type="date" class="form-control invoice-field-input" name="invoice_date[' + invoiceNo + ']" ' +
        'id="invoice_date_' + invoiceNo + '" value="' + currentDate + '" required>' +
        '</div>' +
        '<div class="invoice-field-group">' +
        '<label class="invoice-supplier-label">' +
        '<i class="fa fa-clipboard"></i> Invoice Terms' +
        '</label>' +
        '<textarea class="form-control invoice-field-textarea" name="invoice_terms[' + invoiceNo + ']" ' +
        'id="invoice_terms_' + invoiceNo + '" placeholder="Enter invoice terms"></textarea>' +
        '</div>' +
        '<div class="invoice-field-group">' +
        '<label class="invoice-supplier-label">' +
        '<i class="fa fa-dollar-sign"></i> Price Term' +
        '</label>' +
        '<input type="text" class="form-control invoice-field-input" name="invoice_price_terms[' + invoiceNo + ']" ' +
        'id="invoice_price_terms_' + invoiceNo + '" placeholder="Enter price term">' +
        '</div>' +
        '</div>' +
        '</div>';

      $('#invoice_supplier_dropdowns').append(dropdownHtml);
    });
  } else {
    $('#invoice_supplier_section').slideUp(200);
    $('#invoice_count_badge').text('0');
  }
}

// Add new supplier row
function addSupplierRow() {
  supplierRowCount++;
  var supplierRowId = supplierRowCount;
  productRowCounts[supplierRowId] = 0;

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

      <div class="products-section mb-2">
        <div class="table-responsive">
          <table class="table table-bordered table-striped product-table" id="products_table_${supplierRowId}" style="width: 1600px;">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Product Name</th>
                <th>Model No.</th>
                <th>Quantity</th>
                <th>Unit Price (RMB)</th>
                <th>Official Quantity</th>
                <th>Black Qty</th>
                <th>Total Amount (RMB)</th>
                <th>Official CI Unit Price (USD)</th>
                <th>Total Amount (USD)</th>
                <th>Black Total Price</th>
                <th>PKG (ctn)</th>
                <th>N.W. (kg)</th>
                <th>Total N.W. (kg)</th>
                <th>G.W. (kg)</th>
                <th>Total G.W. (kg)</th>
                <th>L</th>
                <th>W</th>
                <th>H</th>
                <th>Total CBM</th>
              </tr>
            </thead>
            <tbody id="products_${supplierRowId}">
              <tr>
                <td colspan="20" class="text-center p-2 text-muted">
                  <i class="fa fa-info-circle"></i> Select a supplier to get products
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
    updateInvoiceSuppliers();
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
        // Clear product table
        $('#products_' + supplierRowId + ' tr').remove();
        
        // Reset product row count
        productRowCounts[supplierRowId] = 0;
        
        // Show initial message
        $('#products_' + supplierRowId).html('<tr><td colspan="20" class="text-center p-2 text-muted"><i class="fa fa-info-circle"></i> Select a supplier to get products</td></tr>');
        
        // Hide refresh button
        $('#refresh_supplier_' + supplierRowId).hide();
        
        // Reset dropdown
        $(selectElement).val('').trigger('change');
        
        // Recalculate totals
        calculateGrandTotalCBM();
        updateInvoiceSuppliers();
      });
      return; // Exit function early
    }

    // Clear existing products (keep table structure, just clear tbody)
    $('#products_' + supplierRowId + ' tr').remove();
    
    // Reset product row count
    productRowCounts[supplierRowId] = 0;
    calculateGrandTotalCBM();

    // Show loading indicator
    $('#products_' + supplierRowId).html('<tr><td colspan="20" class="text-center p-2"><i class="fa fa-spinner fa-spin"></i> Loading products...</td></tr>');
    updateInvoiceSuppliers();

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
          $('#products_' + supplierRowId + ' tr').remove();

          var readyProducts = res.ready_products || [];
          var spareProducts = res.spare_products || [];
          var mergedProducts = mergeSupplierProducts(readyProducts, spareProducts);
          var hasProducts = mergedProducts.length > 0;

          if (hasProducts) {
            mergedProducts.forEach(function(item) {
              createProductRowWithData(item.sectionType, supplierRowId, item.product);
            });
          } else {
            $('#products_' + supplierRowId).html('<tr><td colspan="20" class="text-center p-2 text-muted">No products found</td></tr>');
          }

          // Initialize select2 and feather icons
          $('.select2').select2();
          if (typeof feather !== 'undefined') {
            feather.replace();
          }
          
          // Recalculate totals
          calculateGrandTotalCBM();
          updateInvoiceSuppliers();
        } else {
          $('#products_' + supplierRowId).html('<tr><td colspan="20" class="text-center p-3 text-muted">No products found</td></tr>');
          calculateGrandTotalCBM();
          updateInvoiceSuppliers();
        }
      },
      error: function() {
        $('#products_' + supplierRowId).html('<tr><td colspan="20" class="text-center p-3 text-danger">Error loading products</td></tr>');
        calculateGrandTotalCBM();
        updateInvoiceSuppliers();
      }
    });
  } else {
    // Clear products if supplier is deselected - show initial message
    $('#products_' + supplierRowId).html('<tr><td colspan="20" class="text-center p-2 text-muted"><i class="fa fa-info-circle"></i> Select a supplier to get products</td></tr>');
    productRowCounts[supplierRowId] = 0;
    // Hide refresh button when supplier is deselected
    $('#refresh_supplier_' + supplierRowId).hide();
    calculateGrandTotalCBM();
    updateInvoiceSuppliers();
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

  var existingProductIds = [];
  $('#products_' + supplierRowId + ' .product-row').each(function() {
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
        var mergedProducts = mergeSupplierProducts(res.ready_products || [], res.spare_products || []);

        mergedProducts.forEach(function(item) {
          var productId = item.product.id.toString();
          if (existingProductIds.indexOf(productId) === -1) {
            createProductRowWithData(item.sectionType, supplierRowId, item.product);
            existingProductIds.push(productId);
            newProductsAdded++;
          }
        });

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
        updateInvoiceSuppliers();
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

function mergeSupplierProducts(readyProducts, spareProducts) {
  var merged = [];
  var seenProductIds = {};

  (readyProducts || []).forEach(function(product) {
    var key = (product.id || '').toString();
    if (key && !seenProductIds[key]) {
      seenProductIds[key] = true;
      merged.push({
        sectionType: 'ready',
        product: product
      });
    }
  });

  (spareProducts || []).forEach(function(product) {
    var key = (product.id || '').toString();
    if (key && !seenProductIds[key]) {
      seenProductIds[key] = true;
      merged.push({
        sectionType: 'spare',
        product: product
      });
    }
  });

  return merged;
}

function toNumber(value) {
  var parsed = parseFloat(value);
  return isNaN(parsed) ? 0 : parsed;
}

function sanitizeNonNegativeIntegerInput(element) {
  var value = (element.value || '').replace(/[^0-9]/g, '');
  if (value === '') {
    element.value = 0;
    return;
  }
  element.value = parseInt(value, 10);
}

function createNoProductRow(sectionType, supplierRowId) {
  var noProductRowHtml = `
    <tr class="no-product-row">
      <td colspan="20" class="text-center p-2 text-muted">
        <i class="fa fa-info-circle"></i> No product selected
      </td>
    </tr>
  `;
  $('#products_' + supplierRowId).html(noProductRowHtml);
}

function createProductRowWithData(sectionType, supplierRowId, productData) {
  if (!productRowCounts[supplierRowId]) {
    productRowCounts[supplierRowId] = 0;
  }
  productRowCounts[supplierRowId]++;
  var productRowId = productRowCounts[supplierRowId];
  var inputPrefix = 'product';
  var supplierKey = ($('#supplier_id_' + supplierRowId).val() || supplierRowId).toString();
  var productKey = (productData.id || productRowId).toString();

  var productName = $('<div>').text(productData.name || '').html();
  var itemCode = $('<div>').text(productData.item_code || '').html();
  var rate = toNumber(productData.rate || 0);
  var usdRate = toNumber(productData.usd_rate || 0);
  var pendingPoQty = toNumber(productData.pending_po_qty || 0);
  var loadingListQty = toNumber(productData.loading_list_qty || 0);
  var inStockQty = toNumber(productData.in_stock_qty || 0);
  var companyStock = toNumber(productData.company_stock || 0);
  var cbm = toNumber(productData.cbm || 0);
  var variations = Array.isArray(productData.variations) && productData.variations.length ? productData.variations : [{}];
  var variationCount = variations.length;

  var productRowHtml = '';
  for (var i = 0; i < variationCount; i++) {
    var variation = variations[i] || {};
    var variationId = variation.id || '';
    var netWeight = toNumber(variation.net_weight || 0);
    var grossWeight = toNumber(variation.gross_weight || 0);
    var length = toNumber(variation.length || 0);
    var width = toNumber(variation.width || 0);
    var height = toNumber(variation.height || 0);

    var rowClass = i === 0
      ? `product-row ${sectionType}-product`
      : `product-variation-row ${sectionType}-product`;
    var rowId = i === 0 ? `product_${supplierRowId}_${productRowId}` : `product_${supplierRowId}_${productRowId}_var_${i}`;
    var keyAttr = i === 0 ? `data-product-id="${productData.id}"` : '';

    productRowHtml += `<tr class="${rowClass}" id="${rowId}" ${keyAttr}>`;

    if (i === 0) {
      productRowHtml += `
      <td rowspan="${variationCount}">
          <select class="form-control form-control-sm invoice-select" name="${inputPrefix}_invoice[${supplierKey}][]" id="${inputPrefix}_invoice_${supplierRowId}_${productRowId}" data-product-id="${productData.id}" onchange="updateInvoiceSuppliers();">
            <option value="1" selected>1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
          </select>
        </td>
        <td rowspan="${variationCount}">
          <input type="hidden" name="product_id[${supplierKey}][]" value="${productData.id}">
          <input type="hidden" name="${inputPrefix}_type[${supplierKey}][]" value="${sectionType}">
          <span class="product-name-display">${productName}</span>
        </td>
        <td rowspan="${variationCount}">
          <input type="hidden" name="${inputPrefix}_model_no[${supplierKey}][]" id="${inputPrefix}_model_no_${supplierRowId}_${productRowId}" value="${itemCode}">
          <span class="model-no-display">${itemCode}</span>
        </td>
        <td rowspan="${variationCount}">
          <input type="number" min="0" step="1" class="form-control form-control-sm" name="${inputPrefix}_qty[${supplierKey}][]" id="${inputPrefix}_qty_${supplierRowId}_${productRowId}" value="0" oninput="sanitizeNonNegativeIntegerInput(this); updateProductCalculations('${sectionType}', ${supplierRowId}, ${productRowId});">
        </td>
        <td rowspan="${variationCount}">
          <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_unit_price_rmb[${supplierKey}][]" id="${inputPrefix}_unit_price_rmb_${supplierRowId}_${productRowId}" value="${rate.toFixed(5)}" oninput="updateProductCalculations('${sectionType}', ${supplierRowId}, ${productRowId});">
        </td>
        <td rowspan="${variationCount}">
          <input type="number" min="0" step="1" class="form-control form-control-sm" name="${inputPrefix}_official_qty[${supplierKey}][]" id="${inputPrefix}_official_qty_${supplierRowId}_${productRowId}" value="0" oninput="sanitizeNonNegativeIntegerInput(this); updateProductCalculations('${sectionType}', ${supplierRowId}, ${productRowId});">
        </td>
        <td rowspan="${variationCount}">
          <input type="number" class="form-control form-control-sm" name="${inputPrefix}_black_qty[${supplierKey}][]" id="${inputPrefix}_black_qty_${supplierRowId}_${productRowId}" value="0" readonly>
        </td>
        <td rowspan="${variationCount}">
          <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_total_amount_rmb[${supplierKey}][]" id="${inputPrefix}_total_amount_rmb_${supplierRowId}_${productRowId}" value="0.00" readonly>
        </td>
        <td rowspan="${variationCount}">
          <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_official_ci_unit_price_usd[${supplierKey}][]" id="${inputPrefix}_official_ci_unit_price_usd_${supplierRowId}_${productRowId}" value="${usdRate.toFixed(5)}" oninput="updateProductCalculations('${sectionType}', ${supplierRowId}, ${productRowId});">
        </td>
        <td rowspan="${variationCount}">
          <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_total_amount_usd[${supplierKey}][]" id="${inputPrefix}_total_amount_usd_${supplierRowId}_${productRowId}" value="0.00" readonly>
        </td>
        <td rowspan="${variationCount}">
          <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_black_total_price[${supplierKey}][]" id="${inputPrefix}_black_total_price_${supplierRowId}_${productRowId}" value="0.00" readonly>
          <input type="hidden" name="${inputPrefix}_cbm[${supplierKey}][]" id="${inputPrefix}_cbm_${supplierRowId}_${productRowId}" value="${cbm.toFixed(6)}">
          <input type="hidden" name="${inputPrefix}_total_cbm[${supplierKey}][]" id="${inputPrefix}_total_cbm_${supplierRowId}_${productRowId}" value="0">
          <input type="hidden" name="${inputPrefix}_pending_po_qty[${supplierKey}][]" id="${inputPrefix}_pending_po_qty_${supplierRowId}_${productRowId}" value="${pendingPoQty}">
          <input type="hidden" name="${inputPrefix}_loading_list_qty[${supplierKey}][]" id="${inputPrefix}_loading_list_qty_${supplierRowId}_${productRowId}" value="${loadingListQty}">
          <input type="hidden" name="${inputPrefix}_in_stock_qty[${supplierKey}][]" id="${inputPrefix}_in_stock_qty_${supplierRowId}_${productRowId}" value="${inStockQty}">
          <input type="hidden" name="${inputPrefix}_company_stock[${supplierKey}][]" id="${inputPrefix}_company_stock_${supplierRowId}_${productRowId}" value="${companyStock}">
        </td>
      `;
    }

    productRowHtml += `
      <td>
        <input type="hidden" name="${inputPrefix}_variation_id[${supplierKey}][${productKey}][]" value="${variationId}">
        <input type="number" class="form-control form-control-sm pkg-qty-${supplierRowId}-${productRowId}" name="${inputPrefix}_pkg_ctn[${supplierKey}][${productKey}][]" id="${inputPrefix}_pkg_${supplierRowId}_${productRowId}_${i}" value="0" readonly>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_net_weight[${supplierKey}][${productKey}][]" id="${inputPrefix}_net_weight_${supplierRowId}_${productRowId}_${i}" value="${netWeight.toFixed(5)}" oninput="updateVariationCalculations('${sectionType}', ${supplierRowId}, ${productRowId}, ${i});">
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_total_net_weight[${supplierKey}][${productKey}][]" id="${inputPrefix}_total_net_weight_${supplierRowId}_${productRowId}_${i}" value="0.00000" readonly>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_gross_weight[${supplierKey}][${productKey}][]" id="${inputPrefix}_gross_weight_${supplierRowId}_${productRowId}_${i}" value="${grossWeight.toFixed(5)}" oninput="updateVariationCalculations('${sectionType}', ${supplierRowId}, ${productRowId}, ${i});">
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_total_gross_weight[${supplierKey}][${productKey}][]" id="${inputPrefix}_total_gross_weight_${supplierRowId}_${productRowId}_${i}" value="0.00000" readonly>
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_length[${supplierKey}][${productKey}][]" id="${inputPrefix}_length_${supplierRowId}_${productRowId}_${i}" value="${length.toFixed(5)}" oninput="updateVariationCalculations('${sectionType}', ${supplierRowId}, ${productRowId}, ${i});">
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_width[${supplierKey}][${productKey}][]" id="${inputPrefix}_width_${supplierRowId}_${productRowId}_${i}" value="${width.toFixed(5)}" oninput="updateVariationCalculations('${sectionType}', ${supplierRowId}, ${productRowId}, ${i});">
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm" name="${inputPrefix}_height[${supplierKey}][${productKey}][]" id="${inputPrefix}_height_${supplierRowId}_${productRowId}_${i}" value="${height.toFixed(5)}" oninput="updateVariationCalculations('${sectionType}', ${supplierRowId}, ${productRowId}, ${i});">
      </td>
      <td>
        <input type="number" step="any" class="form-control form-control-sm var-total-cbm-${supplierRowId}-${productRowId}" name="${inputPrefix}_variation_total_cbm[${supplierKey}][${productKey}][]" id="${inputPrefix}_total_cbm_var_${supplierRowId}_${productRowId}_${i}" value="0.000000" readonly>
      </td>
    `;

    productRowHtml += '</tr>';
  }

  $('#products_' + supplierRowId).append(productRowHtml);
  updateProductCalculations(sectionType, supplierRowId, productRowId);
}

function updateProductCalculations(sectionType, supplierRowId, productRowId) {
  var inputPrefix = 'product';

  var qtySelector = '#' + inputPrefix + '_qty_' + supplierRowId + '_' + productRowId;
  var officialQtySelector = '#' + inputPrefix + '_official_qty_' + supplierRowId + '_' + productRowId;
  var unitPriceRmbSelector = '#' + inputPrefix + '_unit_price_rmb_' + supplierRowId + '_' + productRowId;
  var unitPriceUsdSelector = '#' + inputPrefix + '_official_ci_unit_price_usd_' + supplierRowId + '_' + productRowId;

  var quantity = parseInt(toNumber($(qtySelector).val()), 10);
  if (quantity < 0) quantity = 0;
  $(qtySelector).val(quantity);

  var officialQuantity = parseInt(toNumber($(officialQtySelector).val()), 10);
  if (officialQuantity < 0) officialQuantity = 0;
  if (quantity === 0) {
    officialQuantity = 0;
  } else if (officialQuantity > quantity) {
    officialQuantity = quantity;
  }
  $(officialQtySelector).val(officialQuantity);

  var blackQty = quantity - officialQuantity;
  var unitPriceRmb = toNumber($(unitPriceRmbSelector).val());
  var unitPriceUsd = toNumber($(unitPriceUsdSelector).val());

  $('#' + inputPrefix + '_black_qty_' + supplierRowId + '_' + productRowId).val(blackQty);
  $('#' + inputPrefix + '_total_amount_rmb_' + supplierRowId + '_' + productRowId).val((unitPriceRmb * officialQuantity).toFixed(2));
  $('#' + inputPrefix + '_total_amount_usd_' + supplierRowId + '_' + productRowId).val((unitPriceUsd * officialQuantity).toFixed(2));
  $('#' + inputPrefix + '_black_total_price_' + supplierRowId + '_' + productRowId).val((unitPriceUsd * blackQty).toFixed(2));

  $('[id^="' + inputPrefix + '_pkg_' + supplierRowId + '_' + productRowId + '_"]').each(function() {
    $(this).val(quantity);
    var idParts = this.id.split('_');
    var variationIndex = parseInt(idParts[idParts.length - 1], 10);
    updateVariationCalculations(sectionType, supplierRowId, productRowId, variationIndex, false);
  });

  updateProductTotalCBM(sectionType, supplierRowId, productRowId);
}

function updateVariationCalculations(sectionType, supplierRowId, productRowId, variationIndex, shouldUpdateProductTotal) {
  var inputPrefix = 'product';

  var pkg = toNumber($('#' + inputPrefix + '_pkg_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val());
  var netWeight = toNumber($('#' + inputPrefix + '_net_weight_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val());
  var grossWeight = toNumber($('#' + inputPrefix + '_gross_weight_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val());
  var length = toNumber($('#' + inputPrefix + '_length_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val());
  var width = toNumber($('#' + inputPrefix + '_width_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val());
  var height = toNumber($('#' + inputPrefix + '_height_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val());

  var totalNetWeight = netWeight * pkg;
  var totalGrossWeight = grossWeight * pkg;
  var totalCBM = ((length * width * height) / 1000000000) * pkg;

  $('#' + inputPrefix + '_total_net_weight_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val(totalNetWeight.toFixed(5));
  $('#' + inputPrefix + '_total_gross_weight_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val(totalGrossWeight.toFixed(5));
  $('#' + inputPrefix + '_total_cbm_var_' + supplierRowId + '_' + productRowId + '_' + variationIndex).val(totalCBM.toFixed(6));

  if (shouldUpdateProductTotal !== false) {
    updateProductTotalCBM(sectionType, supplierRowId, productRowId);
  }
}

function updateProductTotalCBM(sectionType, supplierRowId, productRowId) {
  var inputPrefix = 'product';
  var sum = 0;

  $('.var-total-cbm-' + supplierRowId + '-' + productRowId).each(function() {
    sum += toNumber($(this).val());
  });

  $('#' + inputPrefix + '_total_cbm_' + supplierRowId + '_' + productRowId).val(sum.toFixed(6));
  calculateGrandTotalCBM();
}

function sumInputs(selector) {
  var total = 0;
  $(selector).each(function() {
    total += toNumber($(this).val());
  });
  return total;
}

function updateGrandTotalsRow() {
  var totals = {
    qty: sumInputs('input[name^="product_qty["]'),
    unitPriceRMB: sumInputs('input[name^="product_unit_price_rmb["]'),
    officialQty: sumInputs('input[name^="product_official_qty["]'),
    blackQty: sumInputs('input[name^="product_black_qty["]'),
    totalAmountRMB: sumInputs('input[name^="product_total_amount_rmb["]'),
    officialCIUnitPriceUSD: sumInputs('input[name^="product_official_ci_unit_price_usd["]'),
    totalAmountUSD: sumInputs('input[name^="product_total_amount_usd["]'),
    blackTotalPrice: sumInputs('input[name^="product_black_total_price["]'),
    pkgCtn: sumInputs('input[name^="product_pkg_ctn["]'),
    nwKg: sumInputs('input[name^="product_net_weight["]'),
    totalNW: sumInputs('input[name^="product_total_net_weight["]'),
    gwKg: sumInputs('input[name^="product_gross_weight["]'),
    totalGW: sumInputs('input[name^="product_total_gross_weight["]'),
    length: sumInputs('input[name^="product_length["]'),
    width: sumInputs('input[name^="product_width["]'),
    height: sumInputs('input[name^="product_height["]'),
    totalCBM: sumInputs('input[name^="product_variation_total_cbm["]')
  };

  var $grandTotalRow = $('#grand-total-row');
  if ($grandTotalRow.length === 0) {
    return;
  }

  $grandTotalRow.find('.grand-total-qty').text(Math.round(totals.qty));
  $grandTotalRow.find('.grand-total-unit-price-rmb').text(totals.unitPriceRMB.toFixed(2));
  $grandTotalRow.find('.grand-total-official-qty').text(Math.round(totals.officialQty));
  $grandTotalRow.find('.grand-total-black-qty').text(Math.round(totals.blackQty));
  $grandTotalRow.find('.grand-total-amount-rmb').text(totals.totalAmountRMB.toFixed(2));
  $grandTotalRow.find('.grand-total-official-ci-unit-price-usd').text(totals.officialCIUnitPriceUSD.toFixed(2));
  $grandTotalRow.find('.grand-total-amount-usd').text(totals.totalAmountUSD.toFixed(2));
  $grandTotalRow.find('.grand-total-black-total-price').text(totals.blackTotalPrice.toFixed(2));
  $grandTotalRow.find('.grand-total-pkg-ctn').text(Math.round(totals.pkgCtn));
  $grandTotalRow.find('.grand-total-nw-kg').text(totals.nwKg.toFixed(2));
  $grandTotalRow.find('.grand-total-total-nw').text(totals.totalNW.toFixed(2));
  $grandTotalRow.find('.grand-total-gw-kg').text(totals.gwKg.toFixed(2));
  $grandTotalRow.find('.grand-total-total-gw').text(totals.totalGW.toFixed(2));
  $grandTotalRow.find('.grand-total-length').text(totals.length.toFixed(2));
  $grandTotalRow.find('.grand-total-width').text(totals.width.toFixed(2));
  $grandTotalRow.find('.grand-total-height').text(totals.height.toFixed(2));
  $grandTotalRow.find('.grand-total-total-cbm').text(totals.totalCBM.toFixed(6));
}

// Calculate sum of all total CBM values
function calculateGrandTotalCBM() {
  var grandTotalCBM = 0;

  $('input[name^="product_total_cbm"]').each(function() {
    var value = parseFloat($(this).val()) || 0;
    grandTotalCBM += value;
  });

  $('#total_cbm').val(grandTotalCBM.toFixed(6));
  updateGrandTotalsRow();
}

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
  updateInvoiceSuppliers();
  
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
