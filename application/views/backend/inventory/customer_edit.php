<style>
  /* Fieldset with floating legend */
  .form-fieldset{
    border: 1px solid #e7eaf2;
    border-radius: 10px;
    padding: 14px 14px 6px;
    margin-bottom: 14px;
    position: relative;
    background: #fff;
  }
  .form-fieldset legend{
    float: none;
    width: auto;
    padding: 0 10px;
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    color: #4b5563;
    letter-spacing: .3px;
    text-transform: uppercase;
    background: #fff;
    position: relative;
    top: 0px;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

        <?php echo form_open('inventory/customer/edit_post/'.$id, [
          'class' => 'add-ajax-redirect-form',
          'onsubmit' => 'return checkForm(this);'
        ]);?>

        <!-- General Details -->
        <fieldset class="form-fieldset">
          <legend>General Details</legend>

          <div class="row">
            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Company Name <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Company Name" name="company_name"
                  value="<?php echo $data['company_name'] ?? ''; ?>" required>
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Company <span class="required">*</span></label>
                <select class="form-select select2" name="company_id[]" id="company_id" onchange="get_staff();" multiple>
                  <?php
                    $selected_company_ids = [];
                    if (!empty($data['company_id'])) {
                      $selected_company_ids = is_array($data['company_id'])
                        ? $data['company_id']
                        : explode(',', $data['company_id']);
                    }
                  ?>
                  <?php foreach($companies as $company){ ?>
                    <option value="<?php echo $company['id'];?>"
                      <?php echo in_array($company['id'], $selected_company_ids) ? 'selected' : ''; ?>>
                      <?php echo $company['name'];?>
                    </option>
                  <?php } ?>
                </select>
              </div>
            </div>

            <?php if($this->session->userdata('super_type') == 'Inventory'){ ?>
              <div class="col-12 col-sm-3 mb-1" id="staff_div">
                <div class="form-group">
                  <label>Staff <span class="required">*</span></label>
                  <select class="form-select select2" name="staff_id" id="staff_id" required>
                    <option value="">Select Staff</option>
                    <?php foreach($staffs as $st){ ?>
                      <option value="<?php echo $st['id']; ?>"
                        <?php echo ($st['id'] == $data['added_by_id']) ? 'selected' : ''; ?>>
                        <?php echo $st['name']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <?php } else { ?>
              <input type="hidden" name="staff_id" value="<?php echo $this->session->userdata('super_user_id');?>">
            <?php } ?>

            <div class="col-12 col-sm-3 mb-1">
              <label class="form-label" for="state_id">Select State</label>
              <select class="form-select select2" name="state_id" id="state_id" onchange="get_city_(this.value);">
                <option value="">Select State</option>
                <?php foreach($states as $state){ ?>
                  <option value="<?php echo $state['id'];?>"
                    <?php echo ($state['id'] == ($data['state_id'] ?? 0)) ? 'selected' : ''; ?>>
                    <?php echo $state['name'];?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <label class="form-label" for="city_id">Select City</label>
              <select class="form-select select2" name="city_id" id="city_id">
                <option value="">Select City</option>
                <?php foreach(($citys ?? []) as $cit){ ?>
                  <option value="<?php echo $cit['id'];?>"
                    <?php echo ($cit['id'] == $data['city_id']) ? 'selected' : ''; ?>>
                    <?php echo $cit['name'];?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Pincode</label>
                <input type="text" class="form-control" placeholder="Enter Pincode" name="pincode"
                  inputmode="numeric" maxlength="6" minlength="6"
                  oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6);"
                  value="<?php echo $data['pincode'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>GST Name</label>
                <input type="text" class="form-control" placeholder="Enter GST Name" name="gst_name"
                  value="<?php echo $data['gst_name'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>GST No.</label>
                <input type="text" class="form-control" placeholder="Enter GST No." name="gst_no"
                  value="<?php echo $data['gst_no'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                <label>Address Line 1 <span class="required">*</span></label>
                <textarea class="form-control" placeholder="Enter Address Line 1" name="address" rows="2" required><?php echo $data['address'] ?? ''; ?></textarea>
              </div>
            </div>

            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                <label>Address Line 2</label>
                <textarea class="form-control" placeholder="Enter Address Line 2" name="address_2" rows="2"><?php echo $data['address_2'] ?? ''; ?></textarea>
              </div>
            </div>
          </div>
        </fieldset>

        <!-- Owner -->
        <fieldset class="form-fieldset">
          <legend>Owner</legend>

          <div class="row">
            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Owner Name <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Owner Name" name="owner_name"
                  value="<?php echo $data['owner_name'] ?? ''; ?>" required>
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Owner Email <span class="required">*</span></label>
                <input type="email" class="form-control" placeholder="Enter Owner Email" name="owner_email"
                  value="<?php echo $data['owner_email'] ?? ''; ?>" required>
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Owner Mobile Number <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Owner Mobile" name="owner_mobile"
                  inputmode="numeric" maxlength="10" minlength="10"
                  oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                  value="<?php echo $data['owner_mobile'] ?? ''; ?>" required>
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Owner Whatsapp Number <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Owner Whatsapp" name="owner_whatsapp"
                  inputmode="numeric" maxlength="10" minlength="10"
                  oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                  value="<?php echo $data['owner_whatsapp'] ?? ''; ?>" required>
              </div>
            </div>
          </div>
        </fieldset>

        <!-- Purchase Manager -->
        <fieldset class="form-fieldset">
          <legend>Purchase Manager</legend>

          <div class="row">
            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Purchase Manager Name</label>
                <input type="text" class="form-control" placeholder="Enter PM Name" name="pm_name"
                  value="<?php echo $data['pm_name'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Purchase Email</label>
                <input type="email" class="form-control" placeholder="Enter Purchase Email" name="pm_email"
                  value="<?php echo $data['pm_email'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Purchase Manager Mobile Number</label>
                <input type="text" class="form-control" placeholder="Enter PM Mobile" name="pm_mobile"
                  inputmode="numeric" maxlength="10" minlength="10"
                  oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                  value="<?php echo $data['pm_mobile'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Purchase Manager Whatsapp Number</label>
                <input type="text" class="form-control" placeholder="Enter PM Whatsapp" name="pm_whatsapp"
                  inputmode="numeric" maxlength="10" minlength="10"
                  oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                  value="<?php echo $data['pm_whatsapp'] ?? ''; ?>">
              </div>
            </div>
          </div>
        </fieldset>

        <!-- Other -->
        <fieldset class="form-fieldset">
          <legend>Other</legend>

          <div class="row">
            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Other Name</label>
                <input type="text" class="form-control" placeholder="Enter Other Name" name="other_name"
                  value="<?php echo $data['other_name'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Other Email</label>
                <input type="email" class="form-control" placeholder="Enter Other Email" name="other_email"
                  value="<?php echo $data['other_email'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Other Mobile Number</label>
                <input type="text" class="form-control" placeholder="Enter Other Mobile" name="other_mobile"
                  inputmode="numeric" maxlength="10" minlength="10"
                  oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                  value="<?php echo $data['other_mobile'] ?? ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <div class="form-group">
                <label>Other Whatsapp Number</label>
                <input type="text" class="form-control" placeholder="Enter Other Whatsapp" name="other_whatsapp"
                  inputmode="numeric" maxlength="10" minlength="10"
                  oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                  value="<?php echo $data['other_whatsapp'] ?? ''; ?>">
              </div>
            </div>
          </div>
        </fieldset>

        <div class="row">
          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
          </div>
        </div>

        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script>
  function get_city_(stateId) {
    $.ajax({
      type: "POST",
      url: "<?php echo base_url();?>admin/get_cities",
      data: { state_id: stateId },
      success: function (html) {
        $("#city_id").children("option:not(:first)").remove();
        $("#city_id").append(html);
      }
    });
  }

  function get_staff() {
    let company_id = $('#company_id').val();
    $('#staff_id').html('<option value="">Select Staff</option>');

    if (!company_id || company_id.length == 0) {
      return false;
    }

    $.ajax({
      type: "POST",
      url: "<?php echo base_url();?>inventory/get_staff_by_company_ids",
      dataType: "json",
      data: { company_id: company_id },
      success: function (res) {
        if (res.status == 200) {
          let html = '<option value="">Select Staff</option>';
          res.data.forEach(item => {
            html += `<option value="${item.id}">${item.name}</option>`;
          });
          $('#staff_id').html(html);

        }
      }
    });
  }
</script>
