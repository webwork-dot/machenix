
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('admin/staff/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="first_name">First Name<i class="required">*</i></label>
              <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" onkeyup="this.value=this.value.replace(/[^A-z]/g,'');"  required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="last_name">Last Name<i class="required">*</i></label>
              <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" onkeyup="this.value=this.value.replace(/[^A-z]/g,'');"  required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="email">Email<i class="required">*</i></label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
              
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="mobileNo">Mobile No.<i class="required">*</i></label>
              <input type="text" class="form-control allow_numeric" placeholder="Mobile" name="phone" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" minlength="10" maxlength="10" required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="altMobileNo">Alternate Mobile No.</label>
              <input type="text" class="form-control allow_numeric" placeholder="Alternate Mobile No." data-toggle="input-mask" data-mask-format="0000000000" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" minlength="10" maxlength="10"  id="altMobileNo" name="alt_phone">
            </div>

            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">Select State<i class="required">*</i></label>
              <select class=" form-select" name="state_id" onchange="get_city_(this.value);" required>
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id'];?>"><?php echo $state['name'];?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="city">Select City<i class="required">*</i></label>
              <select class=" form-select" name="city_id"  id="states_" required>
                <option value="">Select City</option>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" rows="1" name="address" placeholder="Address"></textarea>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="dateOfBirth">Date Of Birth</label>
              <input type="date" class="form-control flatpickr-max" name="date_birth" placeholder="YYYY-MM-DD"  max="<?php echo date("Y-m-d"); ?>">
              
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="joiningDate">Joining Date</label>
              <input type="date" class="form-control flatpickr-basic" name="join_date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            
       
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="password">Password<i class="required">*</i></label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="user_type">Select User Type<i class="required">*</i></label>
              <select id="user_type" class="form-select" name="user_type_id" onchange="get_warehouse_(this.value);"  required>
                <option value="">Select User Type</option>
                <?php foreach($user_types as $user){?>
                <option value="<?php echo $user['id'];?>"><?php echo $user['name'];?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1" id="warehouse_div" style="display:none">
              <label class="form-label" for="warehouse_id">Select Warehouse<i class="required">*</i></label>
              <select id="warehouse_id" class="form-select" name="warehouse_id" >
                <option value="">Select Warehouse</option>
                <?php foreach($warehouse as $warehouse_id){?>
                <option value="<?php echo $warehouse_id['id']; ?>" > <?php echo $warehouse_id['name'] ?></option>';
                <?php }?>
              </select>
            </div>


            <div class="col-12 col-sm-4 mb-1">
              <label for="status" class="form-label">Status</label>
              <select id="status" class="form-select" name="status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
            
                        
            
            
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
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
           url: "<?php echo base_url();?>admin/get_cities",
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
    