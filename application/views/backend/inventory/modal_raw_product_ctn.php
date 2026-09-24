<?php
$product_id = (int)$param2;
$product = $this->common_model->getRowById('raw_products', '*', ['id' => $product_id, 'product_type' => 'import', 'is_deleted' => '0']);
if (!is_array($product)) {
  $product = [];
}
$variations = $this->common_model->getResultById('product_variation', '*', ['product_id' => $product_id]);
if (!is_array($variations)) {
  $variations = [];
}
?>

<style>
  .ctn-modal-wrap .variation-row-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #e3e7ed;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  }
  .ctn-modal-wrap .variation-row-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #5a79c0 0%, #7891d2 100%);
    border-radius: 12px 0 0 12px;
  }
  .ctn-modal-wrap .variation-header {
    background: linear-gradient(135deg, #5a79c0 0%, #7891d2 100%);
    color: #fff;
    padding: 8px 14px;
    border-radius: 8px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .ctn-modal-wrap .variation-header label {
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .ctn-modal-wrap .variation-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    font-weight: 700;
    font-size: 12px;
  }
  .ctn-modal-wrap .variation-add-btn-container {
    margin: 8px 0 16px;
    text-align: center;
  }
  .ctn-modal-wrap .btn-add-product {
    background: linear-gradient(135deg, #5a79c0 0%, #7891d2 100%);
    border: none;
    color: #fff;
    padding: 6px 16px;
    border-radius: 6px;
    font-weight: 600;
  }
  .ctn-modal-wrap .product-meta {
    margin-bottom: 14px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
  }
  .ctn-modal-wrap .product-meta strong {
    color: #0f172a;
  }
</style>

<div class="ctn-modal-wrap">
  <?php if (empty($product)): ?>
    <div class="alert alert-danger mb-0">Product not found.</div>
  <?php else: ?>
    <div class="product-meta">
      <strong><?php echo htmlspecialchars($product['name'] ?? ''); ?></strong>
      <?php if (!empty($product['item_code'])): ?>
        <span class="ms-1 text-muted">| Model: <?php echo htmlspecialchars($product['item_code']); ?></span>
      <?php endif; ?>
    </div>

    <?php echo form_open('inventory/update_raw_product_ctn/' . $product_id, [
      'id' => 'raw_product_ctn_form',
      'onsubmit' => 'return submitRawProductCtnForm(event);'
    ]); ?>

    <div id="ctn_rows_wrapper">
      <?php if (!empty($variations)): ?>
        <?php foreach ($variations as $index => $variation): ?>
          <div class="variation-row-container">
            <div class="variation-header">
              <label>
                <span class="variation-badge"><?php echo $index + 1; ?></span>
                Pkg (Ctn) - <?php echo $index + 1; ?>
              </label>
            </div>
            <div class="row variation-row" data-row-index="<?php echo $index; ?>">
              <input type="hidden" name="variation_id[]" value="<?php echo (int)$variation['id']; ?>">
              <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                  <label>Net Weight</label>
                  <input type="number" class="form-control" placeholder="Enter Net Weight" name="variation_net_weight[]" value="<?php echo clean_number($variation['net_weight'] ?? 0); ?>" step="0.00001">
                </div>
              </div>
              <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                  <label>Gross Weight</label>
                  <input type="number" class="form-control" placeholder="Enter Gross Weight" name="variation_gross_weight[]" value="<?php echo clean_number($variation['gross_weight'] ?? 0); ?>" step="0.00001">
                </div>
              </div>
              <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                  <label>Length</label>
                  <input type="number" class="form-control" placeholder="Enter Length" name="variation_length[]" value="<?php echo clean_number($variation['length'] ?? 0); ?>" step="0.00001">
                </div>
              </div>
              <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                  <label>Width</label>
                  <input type="number" class="form-control" placeholder="Enter Width" name="variation_width[]" value="<?php echo clean_number($variation['width'] ?? 0); ?>" step="0.00001">
                </div>
              </div>
              <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                  <label>Height</label>
                  <input type="number" class="form-control" placeholder="Enter Height" name="variation_height[]" value="<?php echo clean_number($variation['height'] ?? 0); ?>" step="0.00001">
                </div>
              </div>
              <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                  <label>CBM <span class="required">*</span></label>
                  <input type="number" class="form-control" placeholder="Enter CBM" name="variation_cbm[]" required value="<?php echo clean_number($variation['cbm'] ?? 0); ?>" step="0.00001">
                </div>
              </div>
              <?php if ($index > 0): ?>
                <div class="col-12 col-sm-4 mb-1 d-flex align-items-end">
                  <button type="button" class="btn btn-danger waves-effect waves-float waves-light" onclick="removeCtnVariationRow(this, '<?php echo (int)$variation['id']; ?>')">
                    <i class="fa fa-minus"></i> Remove
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="variation-row-container">
          <div class="variation-header">
            <label>
              <span class="variation-badge">1</span>
              Pkg (Ctn) - 1
            </label>
          </div>
          <div class="row variation-row" data-row-index="0">
            <input type="hidden" name="variation_id[]" value="0">
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Net Weight</label>
                <input type="number" class="form-control" placeholder="Enter Net Weight" name="variation_net_weight[]" value="<?php echo clean_number($product['net_weight'] ?? 0); ?>" step="0.00001">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Gross Weight</label>
                <input type="number" class="form-control" placeholder="Enter Gross Weight" name="variation_gross_weight[]" value="<?php echo clean_number($product['gross_weight'] ?? 0); ?>" step="0.00001">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Length</label>
                <input type="number" class="form-control" placeholder="Enter Length" name="variation_length[]" value="<?php echo clean_number($product['length'] ?? 0); ?>" step="0.00001">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Width</label>
                <input type="number" class="form-control" placeholder="Enter Width" name="variation_width[]" value="<?php echo clean_number($product['width'] ?? 0); ?>" step="0.00001">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Height</label>
                <input type="number" class="form-control" placeholder="Enter Height" name="variation_height[]" value="<?php echo clean_number($product['height'] ?? 0); ?>" step="0.00001">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>CBM <span class="required">*</span></label>
                <input type="number" class="form-control" placeholder="Enter CBM" name="variation_cbm[]" required value="<?php echo clean_number($product['cbm'] ?? 0); ?>" step="0.00001">
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div id="variation_rows_container"></div>

    <div class="variation-add-btn-container">
      <button type="button" class="btn btn-add-product" onclick="addCtnVariationRow()">
        <i class="fa fa-plus"></i> Add Pkg (Ctn)
      </button>
    </div>

    <div class="mt-1">
      <button type="submit" id="ctn_submit_btn" class="btn btn-primary waves-effect waves-float waves-light">
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
var ctnVariationRowCount = <?php echo !empty($variations) ? count($variations) : 1; ?>;

function addCtnVariationRow() {
  ctnVariationRowCount++;
  var rowHtml = `
    <div class="variation-row-container">
      <div class="variation-header">
        <label>
          <span class="variation-badge">${ctnVariationRowCount}</span>
          Pkg (Ctn) - ${ctnVariationRowCount}
        </label>
      </div>
      <div class="row variation-row" data-row-index="${ctnVariationRowCount - 1}">
        <input type="hidden" name="variation_id[]" value="0">
        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label>Net Weight</label>
            <input type="number" class="form-control" placeholder="Enter Net Weight" name="variation_net_weight[]" value="0" step="0.00001">
          </div>
        </div>
        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label>Gross Weight</label>
            <input type="number" class="form-control" placeholder="Enter Gross Weight" name="variation_gross_weight[]" value="0" step="0.00001">
          </div>
        </div>
        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label>Length</label>
            <input type="number" class="form-control" placeholder="Enter Length" name="variation_length[]" value="0" step="0.00001">
          </div>
        </div>
        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label>Width</label>
            <input type="number" class="form-control" placeholder="Enter Width" name="variation_width[]" value="0" step="0.00001">
          </div>
        </div>
        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label>Height</label>
            <input type="number" class="form-control" placeholder="Enter Height" name="variation_height[]" value="0" step="0.00001">
          </div>
        </div>
        <div class="col-12 col-sm-4 mb-1">
          <div class="form-group">
            <label>CBM <span class="required">*</span></label>
            <input type="number" class="form-control" placeholder="Enter CBM" name="variation_cbm[]" required value="0" step="0.00001">
          </div>
        </div>
        <div class="col-12 col-sm-4 mb-1 d-flex align-items-end">
          <button type="button" class="btn btn-danger waves-effect waves-float waves-light" onclick="removeCtnVariationRow(this, 0)">
            <i class="fa fa-minus"></i> Remove
          </button>
        </div>
      </div>
    </div>
  `;
  $('#variation_rows_container').append(rowHtml);
  updateCtnVariationHeadings();
}

function updateCtnVariationHeadings() {
  $('.ctn-modal-wrap .variation-row-container').each(function(index) {
    var $header = $(this).find('.variation-header label');
    var $badge = $header.find('.variation-badge');
    $badge.text(index + 1);
    $header.contents().filter(function() {
      return this.nodeType === 3;
    }).remove();
    $header.append(' Pkg (Ctn) - ' + (index + 1));
  });
  ctnVariationRowCount = $('.ctn-modal-wrap .variation-row').length;
}

function removeCtnVariationRow(btn, variationId) {
  var $container = $(btn).closest('.variation-row-container');

  if (variationId != 0) {
    Swal.fire({
      title: "Are you sure?",
      text: "You want to delete this variation!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, delete it!",
      customClass: {
        confirmButton: "btn btn-primary",
        cancelButton: "btn btn-outline-danger ms-1"
      },
      buttonsStyling: false
    }).then(function(result) {
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: '<?php echo base_url(); ?>inventory/raw_products_delete_variation',
          data: { id: variationId },
          dataType: 'json',
          success: function(res) {
            if (res.status == 200) {
              $container.remove();
              updateCtnVariationHeadings();
            } else {
              alert(res.message || 'Delete failed');
            }
          },
          error: function() {
            alert('Delete failed');
          }
        });
      }
    });
  } else {
    $container.remove();
    updateCtnVariationHeadings();
  }
}

function submitRawProductCtnForm(event) {
  event.preventDefault();

  var $form = $('#raw_product_ctn_form');
  var $submitBtn = $('#ctn_submit_btn');
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
          text: res.message || "CTN sections updated successfully",
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
</script>
