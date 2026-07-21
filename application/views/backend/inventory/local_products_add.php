<style>
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

  .newelement-1 .row {}

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

        <?php echo form_open('inventory/local-products/add_post', ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">
          <input name="is_variation" type="hidden" value="1">

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Product Name <span class="required">*</span></label>
              <input type="text" class="form-control" placeholder="Enter Product Name" name="name" required="">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Alias Name </label>
              <input type="text" class="form-control alias-name" placeholder="Enter Alias Name" name="alias" >
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Category <span class="required">*</span> </label>
              <?php 
              $category_ = array(); 
              if(isset($product)){  $category_ = explode(',',$product->category_id); } ?>
              <select name="category_id" class="category-select select2" onchange="detectType(this)" required="" >
                <?php $this->common_model->displayTreeOptions($category_tree,$category_);?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1 " >
            <div class="form-group">
              <label>Model No. <span class="required req-cont">*</span></label>
              <input type="text" class="form-control old-sku req-inp" placeholder="Enter Model No." name="item_code"
                id="item_code" required>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>GST Applicable <span class="required">*</span></label>
              <select class="form-select select2" name="is_gst_applicable" id="is_gst_applicable" required>
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>HSN Code <span class="required gst-req-star">*</span></label>
              <input type="text" class="form-control" placeholder="Enter HSN Code" name="hsn_code">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Tax Rate (in %)<span class="required gst-req-star">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Tax Rate" name="gst" value="0">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Unit <span class="required req-cont">*</span></label>
              <select class="form-select select2" name="unit" required>
                <option value="">Select Unit</option>
                <?php foreach ($product_units as $unit): ?>
                  <option value="<?php echo $unit['name']; ?>"><?php echo $unit['name']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Commission <span class="required">*</span></label>
              <select class="form-select select2" name="commission_id" required>
                <option value="">Select Commission</option>
                <?php foreach ($commissions as $comm): ?>
                  <option value="<?php echo $comm['id']; ?>"><?php echo $comm['name']; ?> (<?php echo $comm['commission']; ?>%)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Opening Stock</label>
              <input type="number" class="form-control" placeholder="Enter Opening Stock" name="opening_stock" min="0">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Min Billing Price <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Min Billing Price" name="product_mrp" required value="0">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Min Selling Price <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Min Selling Price" name="costing_price" required value="0">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Stock Intimation <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Stock Intimation" name="intimation"
                id="intimation" value='0' required="">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Status <span class="required">*</span></label>
            <select class="form-select select2" name="status" required>
              <option value="1">Active </option>
              <option value="0">Inactive </option>
            </select>
          </div>

          <div class="col-12  pr_img_div" id="pr_img_div">
            <div class="card">
              <div class="card-header">
                <h4 class="m-0">Product Images</h4>
              </div>
              <div class="card-body m-body mrg-top">
                <div class="row">
                  <div class="col-12">
                    <div class="form-group" style="margin-bottom: 0px;">
                      <?php 
                        $this->load->view("backend/inventory/cards/_image_upload_box");
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-9 mb-1 pr_img_div hidden" id="pr_img_div">
            <div class="listed-card">

              <label>Product Listed On : </label>
              <div class="row">
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_1" id="p_listed_1" value="0">
                  <input type="checkbox" class="is_other listed_product" name="listed_1" id="listed_1" value="0" />
                  <label for="listed_1">Amazon</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_2" id="p_listed_2" value="0">
                  <input type="checkbox" class="is_other listed_product" name="listed_2" id="listed_2" value="0" />
                  <label for="listed_2">Snapdeal</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_3" id="p_listed_3" value="0">
                  <input type="checkbox" class="is_other listed_product" name="listed_3" id="listed_3" value="0" />
                  <label for="listed_3">Flipkart</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_4" id="p_listed_4" value="0">
                  <input type="checkbox" class="is_other listed_product" name="listed_4" id="listed_4" value="0" />
                  <label for="listed_4">Jio</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_5" id="p_listed_5" value="0">
                  <input type="checkbox" class="is_other listed_product" name="listed_5" id="listed_5" value="0" />
                  <label for="listed_5">Machenix</label>
                </div>
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

function toggleGstRequirements() {
  var is_gst = $('#is_gst_applicable').val();
  if (is_gst == '1') {
    $('input[name="hsn_code"]').attr('required', 'required');
    $('input[name="gst"]').attr('required', 'required');
    $('.gst-req-star').removeClass('d-none');
  } else {
    $('input[name="hsn_code"]').removeAttr('required');
    $('input[name="gst"]').removeAttr('required');
    $('.gst-req-star').addClass('d-none');
  }
}

$(document).ready(function () {
    $('#is_gst_applicable').change(function() {
      toggleGstRequirements();
    });
    toggleGstRequirements();

    $(document).on('focus', '.category-select + .select2 .select2-selection', function () {
        $('.category-select').select2('open');
    });
});

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

function detectType(val) {
  $.ajax({
    type: "POST",
    url: "<?php echo base_url(); ?>inventory/get_category_by_id",
    data: {id: val.value},
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

$(document).on("click", ".btn-delete-product-img-session", function() {
  var b = $(this).attr("data-file-id");
  var a = {
    file_id: b
  };
  $.ajax({
    type: "POST",
    url: base_url + "file_controller/delete_image_session",
    data: a,
    success: function() {
      $("#uploaderFile" + b).remove()
    }
  })
});
</script>
