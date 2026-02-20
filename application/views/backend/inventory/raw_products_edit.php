<?php
// Helper function to format numbers (remove trailing zeros)
function formatNumber($value) {
  if ($value === null || $value === '') return '0';
  $num = floatval($value);
  // Remove trailing zeros and unnecessary decimal point
  return rtrim(rtrim(number_format($num, 5, '.', ''), '0'), '.');
}
?>
<style>
  .variation-row-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #e3e7ed;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
  }

  .variation-row-container:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    border-color: #5a79c0;
  }

  .variation-row-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #5a79c0 0%, #7891d2 100%);
    border-radius: 12px 0 0 12px;
  }

  .variation-header {
    background: linear-gradient(135deg, #5a79c0 0%, #7891d2 100%);
    color: #fff;
    padding: 10px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(90, 121, 192, 0.3);
  }

  .variation-header label {
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
  }

  .variation-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    font-weight: 700;
    font-size: 13px;
    margin-right: 8px;
  }

  .btn-add-product {
      background: linear-gradient(135deg, #5a79c0 0%, #7891d2 100%);
      border: none;
      color: #fff;
      padding: 6px 16px;
      border-radius: 6px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(90, 121, 192, 0.3);
  }

  .variation-row {
    margin-top: 0;
  }

  #variation_rows_container .variation-row-container {
    animation: fadeIn 0.3s ease-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .variation-add-btn-container {
    margin-top: 10px;
    margin-bottom: 20px;
    text-align: center;
  }

  .variation-tab {
    border: 1px solid #ddd;
  }

  .variation-tab h6 {
    text-align: center;
    padding: 5px;
    background: #dddd;
    font-weight: 700;
  }

  .variation-tab #requirement_area {
    padding: 0 10px;
  }

  .pt-05 {
    padding-top: 5px;
  }

  .newelement-1 .row .col-md-2 {
    padding-left: 0;
  }

  .remove-no {
    border: 2px solid #ea5455;
    display: inline-block;
    padding: 5px 5px;
    font-weight: 900;
    position: relative;
    left: -20px;
    top: 0;
    color: #ea5455;
    font-size: 23px;
    line-height: 0px;
    border-radius: 4px;
  }

  .xtra-input {
    //margin-top: -23px;
  }

  .listed-card-1 {
    background: #efefef;
    padding: 5px 20px;
    padding-bottom: 0;
    border: 1px solid #ddd;
    border-radius: 4px;
  }

  .listed-card-1 .col-sm-2 {
    flex: 0 0 auto;
    width: auto;
  }

  .mr-grey {
    background: #f4f8ff;
    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.25);
    border-radius: 5px;
  }
</style>

<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/raw_products/edit_post/'.$id, ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?>

        <div class="row">

          <input name="is_variation" type="hidden" value="1">
          <input name="old_sizes" type="hidden" value="<?php echo $data['sizes']; ?>">

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Product Name <span class="required">*</span></label>
              <input type="text" class="form-control" placeholder="Enter Product Name" name="name"
                value="<?php echo $data['name']; ?>" required="">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Alias Name </label>
              <input type="text" class="form-control alias-name" placeholder="Enter Alias Name"
                value="<?php echo $data['alias']; ?>" name="alias">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Category <span class="required">*</span> </label>
              <?php 
                $category_ = array(); 
                if(isset($data)){  $category_ = explode(',', $data['categories']); } ?>
              <select name="category_id" class="select2 category-select" onchange="detectType(this.value)" required="">
                <?php  $this->common_model->displayTreeOptions($category_tree,$category_);?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Supplier <span class="required">*</span></label>
              <select class="form-select select2" name="supplier_id" id="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                  <option value="<?php echo $supplier['id']; ?>" <?php echo ($data['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>><?php echo $supplier['name']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Model No. <span class="required req-cont">*</span></label>
              <input type="text" class="form-control old-sku req-inp" placeholder="Enter Model No." onkeyup="checkSKU(this)"
                value="<?php echo $data['item_code']; ?>" name="item_code" id="item_code" required="">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>HSN Code <span class="required req-cont">*</span></label>
              <input type="text" class="form-control req-inp" placeholder="Enter HSN Code" name="hsn_code"
                value="<?php echo $data['hsn_code']; ?>" required="">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Tax Rate (in %)<span class="required req-cont">*</span></label>
              <input type="number" class="form-control req-inp" placeholder="Enter Tax Rate (in %)" name="gst"
                value="<?php echo $data['gst']; ?>">
            </div>
          </div>

          <?php 
          // echo "<pre>";
          // print_r($variations);
          // echo "</pre>";
          // exit;
            $variations = isset($variations) ? $variations : [];
            $variation_count = count($variations);
            $first_variation = !empty($variations) ? $variations[0] : null;
          ?>
          
          <?php if (!empty($variations)): ?>
            <?php foreach ($variations as $index => $variation): ?>
              <div class="col-12 mb-2">
                <div class="variation-row-container">
                  <div class="variation-header">
                    <label>
                      <span class="variation-badge"><?php echo $index + 1; ?></span>
                      Pkg (Ctn) - <?php echo $index + 1; ?>
                    </label>
                  </div>
                  <div class="row variation-row" data-row-index="<?php echo $index; ?>">
                    <input type="hidden" name="variation_id[]" value="<?php echo $variation['id']; ?>">
                    <div class="col-12 col-sm-3 mb-1">
                      <div class="form-group">
                        <label>Net Weight</label>
                        <input type="number" class="form-control" placeholder="Enter Net Weight" name="variation_net_weight[]" value="<?php echo formatNumber($variation['net_weight']); ?>" step="0.00001">
                      </div>
                    </div>
                    
                    <div class="col-12 col-sm-3 mb-1">
                      <div class="form-group">
                        <label>Gross Weight</label>
                        <input type="number" class="form-control" placeholder="Enter Gross Weight" name="variation_gross_weight[]" value="<?php echo formatNumber($variation['gross_weight']); ?>" step="0.00001">
                      </div>
                    </div>

                    <div class="col-12 col-sm-3 mb-1">
                      <div class="form-group">
                        <label>Length</label>
                        <input type="number" class="form-control" placeholder="Enter Length" name="variation_length[]" value="<?php echo formatNumber($variation['length']); ?>" step="0.00001">
                      </div>
                    </div>

                    <div class="col-12 col-sm-3 mb-1">
                      <div class="form-group">
                        <label>Width</label>
                        <input type="number" class="form-control" placeholder="Enter Width" name="variation_width[]" value="<?php echo formatNumber($variation['width']); ?>" step="0.00001">
                      </div>
                    </div>

                    <div class="col-12 col-sm-3 mb-1">
                      <div class="form-group">
                        <label>Height</label>
                        <input type="number" class="form-control" placeholder="Enter Height" name="variation_height[]" value="<?php echo formatNumber($variation['height']); ?>" step="0.00001">
                      </div>
                    </div>

                    <div class="col-12 col-sm-3 mb-1">
                      <div class="form-group">
                        <label>CBM <span class="required">*</span></label>
                        <input type="number" class="form-control" placeholder="Enter CBM" name="variation_cbm[]" required value="<?php echo formatNumber($variation['cbm']); ?>" step="0.00001">
                      </div>
                    </div>

                    <?php if ($index > 0): ?>
                    <div class="col-12 col-sm-3 mb-1 d-flex align-items-end">
                      <button type="button" class="btn btn-danger waves-effect waves-float waves-light" onclick="removeVariationRow(this, '<?php echo $variation['id']; ?>')">
                        <i class="fa fa-minus"></i> Remove
                      </button>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 mb-2">
              <div class="variation-row-container">
                <div class="variation-header">
                  <label>
                    <span class="variation-badge">1</span>
                    Pkg (Ctn) - 1
                  </label>
                </div>
                <div class="row variation-row" data-row-index="0">
                  <input type="hidden" name="variation_id[]" value="0">
                  <div class="col-12 col-sm-3 mb-1">
                    <div class="form-group">
                      <label>Net Weight</label>
                      <input type="number" class="form-control" placeholder="Enter Net Weight" name="variation_net_weight[]" value="<?php echo formatNumber($data['net_weight']); ?>" step="0.00001">
                    </div>
                  </div>
                  
                  <div class="col-12 col-sm-3 mb-1">
                    <div class="form-group">
                      <label>Gross Weight</label>
                      <input type="number" class="form-control" placeholder="Enter Gross Weight" name="variation_gross_weight[]" value="<?php echo formatNumber($data['gross_weight']); ?>" step="0.00001">
                    </div>
                  </div>

                  <div class="col-12 col-sm-3 mb-1">
                    <div class="form-group">
                      <label>Length</label>
                      <input type="number" class="form-control" placeholder="Enter Length" name="variation_length[]" value="<?php echo formatNumber($data['length']); ?>" step="0.00001">
                    </div>
                  </div>

                  <div class="col-12 col-sm-3 mb-1">
                    <div class="form-group">
                      <label>Width</label>
                      <input type="number" class="form-control" placeholder="Enter Width" name="variation_width[]" value="<?php echo formatNumber($data['width']); ?>" step="0.00001">
                    </div>
                  </div>

                  <div class="col-12 col-sm-3 mb-1">
                    <div class="form-group">
                      <label>Height</label>
                      <input type="number" class="form-control" placeholder="Enter Height" name="variation_height[]" value="<?php echo formatNumber($data['height']); ?>" step="0.00001">
                    </div>
                  </div>

                  <div class="col-12 col-sm-3 mb-1">
                    <div class="form-group">
                      <label>CBM <span class="required">*</span></label>
                      <input type="number" class="form-control" placeholder="Enter CBM" name="variation_cbm[]" required value="<?php echo formatNumber($data['cbm']); ?>" step="0.00001">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div id="variation_rows_container"></div>

          <div class="col-12 variation-add-btn-container">
            <button type="button" class="btn-add-product waves-effect waves-float waves-light" onclick="addVariationRow()">
              <i class="uil uil-plus-circle"></i> Add Pkg (Ctn)
            </button>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>USD Rate</label>
              <input type="number" class="form-control" placeholder="Enter USD Rate" value="<?php echo $data['usd_rate']; ?>" name="usd_rate">
            </div>
          </div>

           <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Rate</label>
              <input type="number" class="form-control" placeholder="Enter Rate" value="<?php echo $data['rate']; ?>" name="rate">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Min Billing Price <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Min Billing Price" name="product_mrp"
                value="<?php echo $data['product_mrp']; ?>" required>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Min Selling Price <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Min Selling Price" name="costing_price"
                value="<?php echo $data['costing_price']; ?>" required>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Intimation <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Intimation" name="intimation" id="intimation"
                required="" value="<?php echo $data['intimation']; ?>">
            </div>
          </div>

          <!-- <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Select Unit <span class="required">*</span></label>
            <select class=" form-select select2" name="unit" required>
              <option value="">Select Unit </option>
              <?php foreach($units_list as $item){?>
              <option value="<?php echo $item->name;?>" <?php if($item->name == $data['unit']) { echo 'selected';} ?>>
                <?php echo $item->name;?></option>
              <?php }?>
            </select>
          </div> -->

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Status <span class="required">*</span></label>
            <select class="form-select select2" name="status" required>
              <option value="1" <?php echo ($data['status'] == 1) ? 'selected':''; ?>>Active </option>
              <option value="0" <?php echo ($data['status'] == 0) ? 'selected':''; ?>>Inactive </option>
            </select>
          </div>

          <div class="col-12 pr_img_div" id="pr_img_div" style="">
            <!-- <div class="form-group">
              <label>Product Image </label>
              <?php if($data['image']!='' && $data['image']!=null ) { ?> <a
                href="<?php echo base_url().$data['image'] ?>" target="_blank"> <b>(View Old File)</b> </a><?php } ?>
              <input type="file" name="image" value="fileupload" id="fileupload" accept=".gif, .jpg, .png, jpeg">
            </div> -->
            <div class="card">
              <div class="card-header">
                <h4 class="m-0">Product Images</h4>
              </div>
              <div class="card-body m-body mrg-top">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group" style="margin-bottom: 0px;">
                      <?php
                        $this->load->view("backend/inventory/cards/_image_update_box");
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-9 mb-1 pr_img_div hidden" id="pr_img_div" style=" ">
            <div class="listed-card">

              <label>Product Listed On : </label>
              <div class="row">
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_1" id="p_listed_1" value="<?php echo $data['listed_1']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_1" id="listed_1"
                    value="<?php echo $data['listed_1']; ?>"
                    <?php echo ('1' == $data['listed_1']) ? 'checked' : ''; ?> />
                  <label for="listed_1">Amazon</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_2" id="p_listed_2" value="<?php echo $data['listed_2']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_2" id="listed_2"
                    value="<?php echo $data['listed_2']; ?>"
                    <?php echo ('1' == $data['listed_2']) ? 'checked' : ''; ?> />
                  <label for="listed_2">Snapdeal</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_3" id="p_listed_3" value="<?php echo $data['listed_3']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_3" id="listed_3"
                    value="<?php echo $data['listed_3']; ?>"
                    <?php echo ('1' == $data['listed_3']) ? 'checked' : ''; ?> />
                  <label for="listed_3">Flipkart</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_4" id="p_listed_4" value="<?php echo $data['listed_4']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_4" id="listed_4"
                    value="<?php echo $data['listed_4']; ?>"
                    <?php echo ('1' == $data['listed_4']) ? 'checked' : ''; ?> />
                  <label for="listed_4">Jio</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_5" id="p_listed_5" value="<?php echo $data['listed_5']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_5" id="listed_5"
                    value="<?php echo $data['listed_5']; ?>"
                    <?php echo ('1' == $data['listed_5']) ? 'checked' : ''; ?> />
                  <label for="listed_5">Good Price Store</label>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1"></div>

          <div class="col-12 col-sm-3 mb-1 hidden">
            <label class="form-label" for="is_other_sku">Other Sku <span class="required">*</span></label>
            <select class="form-select select2" name="is_other_sku" onchange="showOtherSKU(this)">
              <option value="0" <?php echo ($data['is_other_sku'] == "0") ? 'selected' : ''; ?>>No </option>
              <option value="1" <?php echo ($data['is_other_sku'] == "1") ? 'selected' : ''; ?>>Yes </option>
            </select>
          </div>

          <div class="col-12 col-sm-12 mb-1" id="other_sku_display"
            style="<?php echo ($data['is_other_sku'] == "0") ? 'display: none;' : 'display: block;'; ?>">
            <div class="row mx-auto py-1 mr-grey">

              <?php 
                    if($skus != "" && count($skus) > 0) { 
                    foreach($skus as $sku) {
                  ?>
              <div class="col-12 col-sm-3 mb-1 d-flex align-items-center">
                <div class="form-group ">
                  <label>SKU</label>
                  <input type="hidden" name="old_sku_id[]" value="<?php echo $sku['id']; ?>">
                  <input type="text" class="form-control" placeholder="SKU" name="other_sku[]" onkeyup="checkSKU(this)"
                    value="<?php echo $sku['sku_code']; ?>">
                </div>
                <a class="btn btn-danger text-white btn-sm waves-effect waves-float waves-light ms-1 mt-1"
                  onclick="removeRequirement(this, '<?php echo $sku['id']; ?>')"><i class=" fa fa-minus"></i></a>
              </div>
              <?php }} else { ?>
              <div class="col-12 col-sm-3 mb-1 d-flex align-items-center">
                <div class="form-group ">
                  <label>SKU</label>
                  <input type="hidden" name="old_sku_id[]" value="0">
                  <input type="text" class="form-control" placeholder="SKU" name="other_sku[]" onkeyup="checkSKU(this)">
                </div>
                <a class="btn btn-danger text-white btn-sm waves-effect waves-float waves-light ms-1 mt-1"
                  onclick="removeRequirement(this, 0)"><i class=" fa fa-minus"></i></a>
              </div>
              <?php } ?>

              <div class="col-12 col-sm-3 mt-1" style="align-self: center;">
                <a class="btn btn-success text-white btn-sm waves-effect waves-float waves-light"
                  onclick="appendRequirement(this)"><i class=" uil-plus-circle"></i>&nbsp;Add SKU</a>
              </div>
            </div>
          </div>

          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
          </div>
          <?php echo form_close(); ?>
          <!--/ form -->
        </div>
      </div>
    </div>
  </div>
</div>


<script>
// Tab Open select2
$(document).on('keydown', '.alias-name', function (e) {
  if (e.key !== 'Tab' || e.shiftKey) return;
  const $row = $(this).closest('tr');
  const $productSelect = $('.category-select');

  setTimeout(() => {
    if ($productSelect.length) {
      $productSelect.select2('open');
    }
  }, 0);
});

var variationRowCount = <?php echo !empty($variations) ? count($variations) : 1; ?>;

$(document).ready(function() {
  $('.listed_product').change(function(event) {
    var isChecked = $(this).is(':checked');
    var checkboxId = $(this).attr('id');
    if (isChecked) {
      $("#p_" + checkboxId).val(1);
    } else {
      $("#p_" + checkboxId).val(0);
    }
  });
});

function addVariationRow() {
  variationRowCount++;
  var rowHtml = `
    <div class="col-12 mb-2">
      <div class="variation-row-container">
        <div class="variation-header">
          <label>
            <span class="variation-badge">${variationRowCount}</span>
            Pkg (Ctn) - ${variationRowCount}
          </label>
        </div>
        <div class="row variation-row" data-row-index="${variationRowCount - 1}">
          <input type="hidden" name="variation_id[]" value="0">
          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Net Weight</label>
              <input type="number" class="form-control" placeholder="Enter Net Weight" name="variation_net_weight[]" value="0" step="0.00001">
            </div>
          </div>
          
          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Gross Weight</label>
              <input type="number" class="form-control" placeholder="Enter Gross Weight" name="variation_gross_weight[]" value="0" step="0.00001">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Length</label>
              <input type="number" class="form-control" placeholder="Enter Length" name="variation_length[]" value="0" step="0.00001">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Width</label>
              <input type="number" class="form-control" placeholder="Enter Width" name="variation_width[]" value="0" step="0.00001">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Height</label>
              <input type="number" class="form-control" placeholder="Enter Height" name="variation_height[]" value="0" step="0.00001">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>CBM <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter CBM" name="variation_cbm[]" required value="0" step="0.00001">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger waves-effect waves-float waves-light" onclick="removeVariationRow(this, 0)">
              <i class="fa fa-minus"></i> Remove
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  $('#variation_rows_container').append(rowHtml);
  
  // Update all headings
  updateVariationHeadings();
  
  // Smooth scroll to new row
  $('html, body').animate({
    scrollTop: $('#variation_rows_container .variation-row-container:last').offset().top - 100
  }, 300);
}

function updateVariationHeadings() {
  $('.variation-row-container').each(function(index) {
    var $header = $(this).find('.variation-header label');
    var $badge = $header.find('.variation-badge');
    $badge.text(index + 1);
    // Remove existing text nodes and add new one
    $header.contents().filter(function() {
      return this.nodeType === 3;
    }).remove();
    $badge.after(' Pkg (Ctn) - ' + (index + 1));
  });
}

function removeVariationRow(btn, variationId) {
  var $row = $(btn).closest('.col-12.mb-2');
  
  if (variationId != 0) {
    // Existing variation - show confirmation
    Swal.fire({
      title: "Are you sure?",
      text: "You want to delete this variation!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: 'POST',
          url: '<?php echo base_url(); ?>inventory/raw_products_delete_variation',
          data: {
            id: variationId
          },
          dataType: 'json',
          success: function(res) {
            if (res.status == 200) {
              $row.fadeOut(300, function() {
                $(this).remove();
                updateRowNumbers();
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: res.message || "Some Error Occurred",
              });
            }
          }
        });
      }
    });
  } else {
    // New variation - just remove
    $row.fadeOut(300, function() {
      $(this).remove();
      updateRowNumbers();
    });
  }
}

function updateRowNumbers() {
  updateVariationHeadings();
  variationRowCount = $('.variation-row').length;
}

function appendRequirement(ele) {
  let html = `<div class="col-12 col-sm-3 mb-1 d-flex align-items-center">
                    <div class="form-group ">
                        <label>SKU</label>
                        <input type="text" class="form-control" placeholder="SKU" name="other_sku[]" onkeyup="checkSKU(this)">
                    </div>
                    <a class="btn btn-danger text-white btn-sm waves-effect waves-float waves-light ms-1 mt-1" onclick="removeRequirement(this, 0)"><i class=" fa fa-minus"></i></a>
                  </div>`;
  ele.parentNode.insertAdjacentHTML('beforebegin', html);
}

function showOtherSKU(ele) {
  if (ele.value == 0) {
    document.querySelector('#other_sku_display').style.display = 'none';
  } else {
    document.querySelector('#other_sku_display').style.display = 'block';
  }
}

function removeRequirement(ele, old_id) {
  if (old_id == 0) {
    ele.parentNode.remove()
  } else {
    Swal.fire({
      title: "Are you sure?",
      text: "You want to delete this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: 'POST',
          url: '<?php echo base_url(); ?>inventory/raw_products_delete_sku',
          data: {
            id: old_id
          },
          dataType: 'json',
          success: function(res) {
            if (res.status == 200) {
              Swal.fire({
                icon: "success",
                title: "Success",
                text: res.message,
              }).then(() => {
                ele.parentNode.remove()
              });
            } else {
              Swal.fire({
                icon: "rrror",
                title: "Error",
                text: "Some Error Occured",
              });
            }
          }
        })
      }
    });

  }
}

function checkSKU(ele) {
  let sku = document.querySelector('#item_code').value;
  let otherSku = document.querySelectorAll('[name="other_sku[]"]');
  let skuList = [];
  if (sku !== '') {
    skuList.push(sku);
  }

  if (otherSku) {
    otherSku.forEach(input => {
      if (input.value != '') {
        skuList.push(input.value.trim());
      }
    });
  }

  let count = 0;
  skuList.forEach((e) => {
    if (e == ele.value) {
      count++;
    }
  });

  if (count > 1) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Cannot Add Same SKU Twice",
    }).then(() => {
      ele.value = '';
    });
  }
}

function detectType(val) {
  $.ajax({
    type: "POST",
    url: "<?php echo base_url(); ?>inventory/get_category_by_id",
    data: {id: val},
    dataType: 'JSON',
    success: function(res) {
      if(res.type == 'spare') {
        document.querySelectorAll('.req-cont').forEach((ele) => {
            ele.classList.add('d-none');
        })

        document.querySelectorAll('.req-inp').forEach((ele) => {
            ele.removeAttribute('required');
        })
      } else {
        document.querySelectorAll('.req-cont').forEach((ele) => {
          ele.classList.remove('d-none')
        })

        document.querySelectorAll('.req-inp').forEach((ele) => {
          ele.setAttribute('required', 'true')
        })
      }
    }
  })
}

detectType('<?php echo $data['categories']; ?>');

$(document).on("click", ".btn-delete-product-img", function() {
  var b = $(this).attr("data-file-id");
  var a = {
    file_id: b
  };
  $.ajax({
    type: "POST",
    url: base_url + "file_controller/delete_image",
    data: a,
    success: function(c) {
      location.reload()
    }
  })
});
</script>