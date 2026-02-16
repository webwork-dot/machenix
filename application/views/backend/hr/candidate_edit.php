<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
          
          <div class="row">
          <?php echo form_open('hr/candidate/edit_candidate/'.$id, ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?> 
          <div class="row mt-1"> 
            
			<div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('candidate_name'); ?><i class="required">*</i></label>
                 <input type="text" class="form-control alphaonly" placeholder="Candidate Name" name="candidate_name" id="candidate_name" value="<?php echo $data['name']; ?>" required>
                 <input type="hidden"  name="candidate_type" value="new" >
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('mobile_no.'); ?><i class="required">*</i></label>
                  <input type="text" class="form-control " placeholder="Mobile No." onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10" name="mobile_no" id="mobile_no"  value="<?php echo $data['phone']; ?>" required>
              </div>
            </div>
            <div class="col-12 col-sm-6 mb-1  ">
              <label class="form-label"><?php echo get_phrase('select_state'); ?><i class="required">*</i></label>
              <select class="form-select" name="state_id" id="state_id" onchange="get_city_(this.value);" required>
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id']; ?>" <?php if($data['state_id'] == $state['id']){ echo 'selected'; } ?>><?php echo $state['name'] ?></option>';
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-6 mb-1  ">
              <label class="form-label"><?php echo get_phrase('select_city'); ?><i class="required">*</i></label>
              <?php $cities   = $this->crud_model->get_city_by_state($data['state_id']);?>
              <select class="form-select" name="city_id"  id="states_" onchange="get_area_(this.value);" required>
                <option value="">Select City</option>
                 <?php foreach($cities as $item){?>
                <option value="<?php echo $item['id']; ?>" <?php if($data['city_id'] == $item['id']){ echo 'selected'; } ?>><?php echo $item['name'] ?></option>';
                <?php }?>
              </select>
            </div>
            
                
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('pincode'); ?><i class="required">*</i></label>
                 <input type="text" class="form-control" placeholder="Pincode" onkeypress="return isNumberKey(event,this)" maxlength="6"  name="pincode" id="pincode_input" value="<?php echo $data['pincode']; ?>" required>
              </div>
            </div>
            
            <div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                  <label class="form-label"><?php echo get_phrase('address'); ?></label>
                  <textarea class="form-control"  rows="1" placeholder="Address" name="address" id="address" ><?php echo $data['address']; ?></textarea>
                </div>
            </div> 
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Candidate Birthday</label>
                 <input type="date" class="form-control flatpickr-max" name="date_birth" id="birthday_input" placeholder="YYYY-MM-DD" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $data['dob']; ?>">
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Candidate Anniversary</label>
                 <input type="date" class="form-control flatpickr-max" name="date_anniversary" id="anniversary_input" placeholder="YYYY-MM-DD" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $data['doa']; ?>">
              </div>
            </div>
			
			<div class="col-12 col-sm-6 mb-2">
              <div class="form-group">
                 <label class="form-label">Staff Category</label>
                 <select class="form-select" name="salary_type"  onchange="get_staff_type_(this.value);">
                    <option value="" <?php if($data['staff_catid']==''){ echo 'selected';}?>>Select</option> 
					<?php   foreach($staff_category as $cat){?>
					 <option value="<?php echo $cat['id'];?>" <?php if($data['staff_catid']==$cat['id']){ echo 'selected';}?>><?php echo $cat['name'];?></option>
					 <?php }?>
                 </select>
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Staff Type</label>
                 <select class="form-select" name="staff_type" id="staff_type" > 
				    <option value="" <?php if($data['staff_typeid']==''){ echo 'selected';}?>>Select</option> 
					<?php   foreach($staff_types as $stype){?>
					 <option value="<?php echo $stype['id'];?>" <?php if($data['staff_typeid']==$stype['id']){ echo 'selected';}?>><?php echo $stype['name'];?></option>
					 <?php }?>
                 </select>
              </div>
            </div> 
						            
            <div class="col-12 col-sm-6 mb-1">
              <label class="form-label">Upload Resume <small> (PDF & Docs Only)</small></label>
                <input type="file" class="form-control" name="resume"  accept=".pdf,.docx,.doc">
                <?php if($data['resume']!='' && $data['resume']!= null){?>
                <span id="old_resume_1" ><a href="<?php echo base_url().$data['resume']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;display: block;">View Old Resume</a></span>
                <?php } ?>
            </div>
            
            <div class="row">    
                <div class="col-12 text-center">
                  <button type="submit" class="btn btn-primary btn_verify mt-2 mb-2" name= "btn_verify">Submit</button>
                </div>
            </div> 
            <?php echo form_close(); ?>
            </div>  
          
            
            
          </div>
          	
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
   
   function get_area_(b) {
       var a = {
           area_id: b
       };
       $.ajax({
           type: "POST",
           url: "<?php echo base_url();?>admin/get_area",
           data: a,
           success: function(c) {
               $("#city_").children("option:not(:first)").remove();
               $("#city_").append(c);
           }
       })
   }  
   
  function get_staff_type_(b) { 
	   $(".loader").show();
       var a = {
           category_id: b
       };
       $.ajax({
           type: "POST",
           url: "<?php echo base_url();?>hr/get_staff_type",
           data: a,
           success: function(c) {
               $(".loader").fadeOut("slow");
               $("#staff_type").children("option:not(:first)").remove();
               $("#staff_type").append(c);
           }
       })
   } 
</script> 
