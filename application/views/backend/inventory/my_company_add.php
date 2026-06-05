<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('inventory/my_company/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Vendor Name <span class="required">*</span></label>
                    <input type="text" class="form-control" placeholder="Enter Vendor Name" name="name" required="">
                </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Contact Person Name </label>
                     <input type="text" class="form-control" placeholder="Enter Contact Name" name="contact_name" >
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Contact Person No </label>
                     <input type="text" class="form-control" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" minlength="10" maxlength="10" placeholder="Enter Contact No" name="contact_no" >
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Email </label>
                     <input type="email" class="form-control" placeholder="Enter Email" name="email" >
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Address 1 <span class="required">*</span></label>
                     <input type="text" class="form-control" placeholder="Enter Address 1" name="address" required="">
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Address 2 <span class="required">*</span></label>
                     <input type="text" class="form-control" placeholder="Enter Address 2" name="address_2" required="">
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Address 3 <span class="required">*</span></label>
                     <input type="text" class="form-control" placeholder="Enter Address 3" name="address_3" required="">
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Pincode <span class="required">*</span></label>
                     <input type="text" class="form-control" placeholder="Enter Pincode" name="pincode" required="">
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>GST Name </label>
                     <input type="text" class="form-control" placeholder="Enter Name" name="gst_name" >
               </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>GST No. </label>
                     <input type="text" class="form-control" placeholder="Enter GST No." name="gst_no" >
               </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="country">Select Country </label>
              <select class=" form-select select2 country_id" name="country_id" onchange="get_states_(this.value);">
                <option value="">Select Country</option>
                <?php foreach($countries as $country){?>
                <option value="<?php echo $country['id'];?>"><?php echo $country['name'];?></option>
                <?php }?>
              </select>
            </div>

            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">Select State </label>
              <select class=" form-select select2 state_id" name="state_id" id="state_id_" onchange="get_city_(this.value);" >
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id'];?>"><?php echo $state['name'];?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="city">Select City </label>
              <select class=" form-select select2 city_id" name="city_id"  id="states_" >
                <option value="">Select City</option>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>State Code </label>
                     <input type="text" class="form-control" placeholder="Enter State Code" name="state_code" >
               </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Opening Amount </label>
                     <input type="number" step="0.01" class="form-control" placeholder="Enter Opening Amount" name="outstanding" value="0.00">
               </div>
            </div>
            
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
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
      $("#states_").html('<option value="">Select City</option>'); // Clear cities
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

