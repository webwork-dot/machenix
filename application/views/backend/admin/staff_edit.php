
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
          <?php echo form_open('admin/staff/edit_post/'.$id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>   
              
           <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="first_name">First Name<i class="required">*</i></label>
              <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" value="<?php echo $data['first_name']; ?>" required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="last_name">Last Name<i class="required">*</i></label>
              <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" value="<?php echo $data['last_name']; ?>" required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="email">Email<i class="required">*</i></label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo $data['email']; ?>" required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="mobileNo">Mobile No.<i class="required">*</i></label>
              <input type="text" class="form-control allow_numeric" id="mobileNo" name="phone" placeholder="Mobile No." data-toggle="input-mask" data-mask-format="0000000000" maxlength="10" value="<?php echo $data['phone']; ?>" required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="altMobileNo">Alternate Mobile No.</label>
              <input type="text" class="form-control allow_numeric" id="altMobileNo" placeholder="Alternate Mobile No." name="alt_phone" value="<?php echo $data['alt_phone']; ?>">
            </div>

            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">Select State<i class="required">*</i></label>
              <select class="form-select" name="state_id" onchange="get_city_(this.value);"  required>
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id']; ?>" <?php if($state['id']== $data['state_id']) { echo 'selected';} ?>><?php echo $state['name'] ?></option>';
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="city">Select City<i class="required">*</i></label>
              <select class="form-select" name="city_id"  id="states_" required>
                <option value="">Select City</option>
                <?php foreach($citys as $cit){?>
                <option value="<?php echo $cit['id'];?>" <?php if($cit['id'] == $data['city_id']){ echo 'selected'; } ?>><?php echo $cit['name'];?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" rows="1" name="address" placeholder="Address"><?php echo $data['address']; ?></textarea>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="dateOfBirth">Date Of Birth</label>
              <input type="date" id="fp-default" class="form-control flatpickr-max" name="date_birth" placeholder="YYYY-MM-DD" value="<?php echo $data['date_birth']; ?>" >
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="joiningDate">Joining Date</label>
              <input type="date" id="fp-default2" class="form-control flatpickr-basic" name="join_date" placeholder="YYYY-MM-DD"  value="<?php echo $data['join_date']; ?>" >
            </div>
         
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="user_type">Select User Type<i class="required">*</i></label>
              <select id="user_type" class="select2 form-select" name="user_type_id" onchange="get_warehouse_(this.value);" required>
                <?php echo $data['type'];?>
                <option value="">Select User Type</option>
                <?php foreach($user_types as $user_type){?>
                <option value="<?php echo $user_type['id']; ?>" <?php if($user_type['id']== $data['role_id']) { echo 'selected';} ?> > <?php echo $user_type['name'] ?></option>';
                <?php }?>
              </select>
            </div>


            <div class="col-12 col-sm-4 mb-1">
              <label for="status" class="form-label">Status</label>
              <select id="status" class="select2 form-select" name="status">
                <option value="1" <?php echo ($data['status'] == '1') ? 'selected':'';?>>Active</option>
                <option value="0" <?php echo ($data['status'] == '0') ? 'selected':'';?>>In Active</option>
              </select>
            </div>
            
           
              
              
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          <?php echo form_close(); ?>	
        <!--/ form -->
      </div>
    </div>
    </div>
</div>
    
<script>
   function get_city_(b) {
       var a = {
           state_id: b
       };
       $.ajax({
           type: "POST",
           url: base_url + "admin/get_cities",
           data: a,
           success: function(c) {
               $("#states_").children("option:not(:first)").remove();
               $("#states_").append(c);
           }
       })
    } 
    
    function get_warehouse_(b) {
        if(b == 13){
            $('.mx-access').show();
        }
        else if(b == 16){
            $('#warehouse_div').show();
            $("#warehouse_id").prop('required',true);
            
            $('.mx-access').hide();
        }else{
            $('#warehouse_id').removeAttr('required');
            $('#warehouse_div').hide();
            
            $('.mx-access').hide();
        }
       
    } 
</script>   
    
