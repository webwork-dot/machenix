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
        <?php echo form_open('inventory/local-products/edit_post/' . $id, ['class' => 'add-ajax-redirect-image-form', 'onsubmit' => 'return checkForm(this);']); ?>

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
              if (isset($data)) {
                $category_ = explode(',', $data['categories']);
              } ?>
              <select name="category_id" class="select2 category-select" onchange="detectType(this.value)" required="">
                <?php $this->common_model->displayTreeOptions($category_tree, $category_); ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Model No. <span class="required req-cont">*</span></label>
              <input type="text" class="form-control old-sku req-inp" placeholder="Enter Model No."
                onkeyup="checkSKU(this)" value="<?php echo $data['item_code']; ?>" name="item_code" id="item_code"
                required="">
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

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Selling Price <span class="required">*</span></label>
              <input type="number" class="form-control" placeholder="Enter Selling Price" name="costing_price"
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

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Status <span class="required">*</span></label>
            <select class="form-select select2" name="status" required>
              <option value="1" <?php echo ($data['status'] == 1) ? 'selected' : ''; ?>>Active </option>
              <option value="0" <?php echo ($data['status'] == 0) ? 'selected' : ''; ?>>Inactive </option>
            </select>
          </div>

          <div class="col-12 pr_img_div" id="pr_img_div" style="">
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
                    value="<?php echo $data['listed_1']; ?>" <?php echo ('1' == $data['listed_1']) ? 'checked' : ''; ?> />
                  <label for="listed_1">Amazon</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_2" id="p_listed_2" value="<?php echo $data['listed_2']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_2" id="listed_2"
                    value="<?php echo $data['listed_2']; ?>" <?php echo ('1' == $data['listed_2']) ? 'checked' : ''; ?> />
                  <label for="listed_2">Snapdeal</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_3" id="p_listed_3" value="<?php echo $data['listed_3']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_3" id="listed_3"
                    value="<?php echo $data['listed_3']; ?>" <?php echo ('1' == $data['listed_3']) ? 'checked' : ''; ?> />
                  <label for="listed_3">Flipkart</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_4" id="p_listed_4" value="<?php echo $data['listed_4']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_4" id="listed_4"
                    value="<?php echo $data['listed_4']; ?>" <?php echo ('1' == $data['listed_4']) ? 'checked' : ''; ?> />
                  <label for="listed_4">Jio</label>
                </div>
                <div class="col-12 col-sm-2 mb-1">
                  <input type="hidden" name="p_listed_5" id="p_listed_5" value="<?php echo $data['listed_5']; ?>">
                  <input type="checkbox" class="is_other listed_product" name="listed_5" id="listed_5"
                    value="<?php echo $data['listed_5']; ?>" <?php echo ('1' == $data['listed_5']) ? 'checked' : ''; ?> />
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
              if ($skus != "" && count($skus) > 0) {
                foreach ($skus as $sku) {
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
                <?php }
              } else { ?>
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

  $(document).ready(function () {
    $(document).on('focus', '.category-select + .select2 .select2-selection', function () {
      $('.category-select').select2('open');
    });
  });

  $(document).ready(function () {
    $('.listed_product').change(function (event) {
      var isChecked = $(this).is(':checked');
      var checkboxId = $(this).attr('id');
      if (isChecked) {
        $("#p_" + checkboxId).val(1);
      } else {
        $("#p_" + checkboxId).val(0);
      }
    });
  });

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
            url: '<?php echo base_url(); ?>inventory/local-products/delete_sku',
            data: {
              id: old_id
            },
            dataType: 'json',
            success: function (res) {
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
    if (!document.querySelector('#item_code').hasAttribute('required')) {
      return;
    }
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
      data: { id: val },
      dataType: 'JSON',
      success: function (res) {
        if (res.type == 'spare') {
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

  $(document).on("click", ".btn-delete-product-img", function () {
    var b = $(this).attr("data-file-id");
    var a = {
      file_id: b
    };
    $.ajax({
      type: "POST",
      url: base_url + "file_controller/delete_image",
      data: a,
      success: function (c) {
        location.reload()
      }
    })
  });
</script>
