<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open_multipart('inventory/vc_manager/edit_post/'.$id, ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?>
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Company Name <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Company Name" name="company_name" required="" value="<?php echo isset($data['company_name']) ? htmlspecialchars($data['company_name']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Contact Name</label>
                <input type="text" class="form-control" placeholder="Enter Contact Name" name="name" value="<?php echo isset($data['name']) ? htmlspecialchars($data['name']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" placeholder="Enter Email" name="email" value="<?php echo isset($data['email']) ? htmlspecialchars($data['email']) : ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Mobile</label>
                <input type="text" class="form-control" placeholder="Enter Mobile" name="mobile" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="15" value="<?php echo isset($data['mobile']) ? htmlspecialchars($data['mobile']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Mobile 2</label>
                <input type="text" class="form-control" placeholder="Enter Mobile 2" name="mobile_2" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="15" value="<?php echo isset($data['mobile_2']) ? htmlspecialchars($data['mobile_2']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Mobile 3</label>
                <input type="text" class="form-control" placeholder="Enter Mobile 3" name="mobile_3" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="15" value="<?php echo isset($data['mobile_3']) ? htmlspecialchars($data['mobile_3']) : ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Address</label>
                <input type="text" class="form-control" placeholder="Enter Address" name="address" value="<?php echo isset($data['address']) ? htmlspecialchars($data['address']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Address 2</label>
                <input type="text" class="form-control" placeholder="Enter Address 2" name="address_2" value="<?php echo isset($data['address_2']) ? htmlspecialchars($data['address_2']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Pincode</label>
                <input type="text" class="form-control" placeholder="Enter Pincode" name="pincode" value="<?php echo isset($data['pincode']) ? htmlspecialchars($data['pincode']) : ''; ?>">
              </div>
            </div>

            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="country">Select Country</label>
              <select class="form-select select2 country_id" name="country_id" onchange="get_states_(this.value);">
                <option value="">Select Country</option>
                <?php foreach ($countries as $country) { ?>
                <option value="<?php echo $country['id']; ?>" <?php echo (!empty($data['country_id']) && $country['id'] == $data['country_id']) ? 'selected' : ''; ?>><?php echo $country['name']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">Select State</label>
              <select class="form-select select2 state_id" name="state_id" id="state_id_" onchange="get_city_(this.value);">
                <option value="">Select State</option>
                <?php foreach ($states as $state) { ?>
                <option value="<?php echo $state['id']; ?>" <?php echo (!empty($data['state_id']) && $state['id'] == $data['state_id']) ? 'selected' : ''; ?>><?php echo $state['name']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="city">Select City</label>
              <select class="form-select select2 city_id" name="city_id" id="states_">
                <option value="">Select City</option>
                <?php if (!empty($citys)) { foreach ($citys as $cit) { ?>
                <option value="<?php echo $cit['id']; ?>" <?php echo (!empty($data['city_id']) && $cit['id'] == $data['city_id']) ? 'selected' : ''; ?>><?php echo $cit['name']; ?></option>
                <?php } } ?>
              </select>
            </div>

            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>GST Name</label>
                <input type="text" class="form-control" placeholder="Enter GST Name" name="gst_name" value="<?php echo isset($data['gst_name']) ? htmlspecialchars($data['gst_name']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>GST No</label>
                <input type="text" class="form-control" placeholder="Enter GST No" name="gst_no" value="<?php echo isset($data['gst_no']) ? htmlspecialchars($data['gst_no']) : ''; ?>">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Document</label>
                <input type="file" class="form-control" name="document">
                <?php if (!empty($data['document'])) { ?>
                <small class="d-block mt-50"><a href="<?php echo base_url($data['document']); ?>" target="_blank">View current document</a></small>
                <?php } ?>
              </div>
            </div>

            <div class="col-12 col-sm-8 mb-1">
              <div class="form-group">
                <label>Description</label>
                <textarea class="form-control" name="desc" rows="3" placeholder="Enter Description"><?php echo isset($data['desc']) ? htmlspecialchars($data['desc']) : ''; ?></textarea>
              </div>
            </div>

            <div class="col-12">
              <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name="btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script>
function get_states_(country_id) {
  $.ajax({
    type: "POST",
    url: "<?php echo base_url();?>admin/get_states",
    data: { country_id: country_id },
    success: function(response) {
      $("#state_id_").html(response);
      $("#states_").html('<option value="">Select City</option>');
    }
  });
}

function get_city_(state_id) {
  $.ajax({
    type: "POST",
    url: "<?php echo base_url();?>admin/get_cities",
    data: { state_id: state_id },
    success: function(response) {
      $("#states_").html(response);
    }
  });
}

$(document).ready(function () {
  $(document).on('focus', '.state_id + .select2 .select2-selection', function () {
    $('.state_id').select2('open');
  });
  $(document).on('focus', '.city_id + .select2 .select2-selection', function () {
    $('.city_id').select2('open');
  });
});
</script>
