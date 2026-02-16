<div class="row">
  <div class="col-6">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
          
          <div class="row">
           <div class="col-md-12">  
           
       
           
           <div class="demo-inline-spacing">
            <div class="form-check form-check-inline form-check-success">
              <input  class="form-check-input" type="radio" name="candidate_type" id="old" value="old"  onchange="check_provider(this.value)"  checked required/>
              <label class="form-check-label" for="old">Old Candidate</label>
            </div>
            <div class="form-check form-check-inline form-check-success">
              <input  class="form-check-input" type="radio" name="candidate_type" id="new" value="new"  onchange="check_provider(this.value)"  required/>
              <label class="form-check-label" for="new">New Candidate</label>
            </div>
           </div>
        </div>
          
        <div class="old" id="old_view"> 
        <?php echo form_open('hr/calls/add_old_calls', ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?> 
          <div class="row mt-1">
            
			<div class="col-4 col-sm-6 mb-1 ">
              <label class="form-label"><?php echo get_phrase('select_candidate'); ?></label>
              <input type="hidden"  name="old_candidate_type" id="old_candidate_type" value="old" >
              <select class="select2 form-select candidate_ajax" name="candidate_id" id="candidate_id" onchange="check_candidate(this.value),get_timeline_(this.value);" required>
                <option value="">Search Candidate Name & Mobile No</option>
              </select>
            </div>
           
          
             
             <div class="col-12 col-sm-6 mb-1 new hide">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('candidate_name'); ?></label>
                 <input type="text" class="form-control" placeholder="Candidate Name" name="candidate_name" id="candidate_name" readonly>
                 <input type="hidden"  name="candidate_type" id="candidate_type" value="new" >
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('mobile_no.'); ?></label>
                 <input type="text" class="form-control" placeholder="Mobile No." data-toggle="input-mask" maxlength="10" data-mask-format="0000000000" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="10" name="mobile_no" id="mobile_no" readonly required>
              </div>
            </div>
              
            
            
            <div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('select_state'); ?></label>
                 <input type="text" class="form-control " name="state_name" placeholder="State" id="state_input" readonly>
              </div>
            </div>   
            
            <div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('select_city'); ?></label>
                 <input type="text" class="form-control" placeholder="City" name="city_name" id="city_input" readonly>
              </div>
            </div>
            
     
            
            <div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('pincode'); ?></label>
                 <input type="text" class="form-control" placeholder="Pincode" name="pincode" id="pincode_input" readonly>
              </div>
            </div>
			
			<div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                  <label class="form-label"><?php echo get_phrase('address'); ?></label>
                  <textarea class="form-control"  rows="1" placeholder="Address" name="address" id="address" readonly ></textarea>
                </div>
            </div> 
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Candidate Birthday</label>
                 <input type="date" class="form-control flatpickr-max" name="old_date_birth" id="birthday_input" placeholder="YYYY-MM-DD" max="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Candidate Anniversary</label>
                 <input type="date" class="form-control flatpickr-max" name="old_date_anniversary" id="anniversary_input" placeholder="YYYY-MM-DD" max="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1 hidden">
              <div class="form-group">
                 <label class="form-label">Called Type</label>
                 <select class="form-select" name="old_called_type">
                     <option value="Outbound" selected >Outbound</option>
                    <option value="Inbound" >Inbound</option>
                 </select>
              </div>
            </div>	
			   
            <div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label class="form-label">Staff Category</label>
                 <input type="text" class="form-control" placeholder="Staff Category" name="old_salary_type" id="old_salary_type" readonly>
              </div>
            </div>
             
			<div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label class="form-label">Staff Type</label>
                 <input type="text" class="form-control" placeholder="Staff Type" name="old_staff_type" id="old_staff_type" readonly>
              </div>
            </div>
			
     
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Follow Up Date</label>
                 <input type="date" class="form-control" name="old_followup_date" min="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
                
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Follow Up Time</label>
                 <input type="time" class="form-control" name="old_followup_time" min="<?php echo date('h:i');?>">
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Shortlisted</label>
                 <select class="form-select" name="old_is_short" id="old_is_short">
                    <option value="0" >No</option>
                    <option value="1" >Yes</option>
                 </select>
              </div>
            </div> 
            
            <div class="col-12 col-sm-12 mb-1">
              <label class="form-label">Upload Resume <small> (PDF & Docs Only)</small></label>
                <input type="file" class="form-control" name="old_resume"  accept=".pdf,.docx,.doc">
                <span id="old_resume_1" ></span>
            </div>
            
            <div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                  <label class="form-label"><?php echo get_phrase('remark'); ?></label>
                  <textarea  class="form-control" rows="1" name="old_remark" placeholder="Remark"></textarea>
                </div>
            </div> 
            
            <div class="row">    
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary btn_verify mt-2 mb-2" name= "btn_verify">Submit</button>
                </div>
            </div> 
            <?php echo form_close(); ?>
        </div>    
        </div>    
            
         <div class="new" id="new_view" style="display:none">  
         <?php echo form_open('hr/calls/add_new_calls', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?> 
          <div class="row mt-1"> 
            
			<div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('candidate_name'); ?><i class="required">*</i></label>
                 <input type="text" class="form-control alphaonly" placeholder="Candidate Name" name="new_candidate_name" id="candidate_name" required>
                 <input type="hidden"  name="candidate_type" value="new" >
              </div>
            </div>
            
			
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('mobile_no.'); ?><i class="required">*</i></label>
                  <input type="text" class="form-control " placeholder="Mobile No." onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10" name="new_mobile_no" id="mobile_no"  required>
              </div>
            </div>
            <div class="col-12 col-sm-6 mb-1  ">
              <label class="form-label"><?php echo get_phrase('select_state'); ?><i class="required">*</i></label>
              <select class="form-select" name="new_state_id" id="state_id" onchange="get_city_(this.value);" required>
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id']; ?>"><?php echo $state['name'] ?></option>';
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-6 mb-1  ">
              <label class="form-label"><?php echo get_phrase('select_city'); ?><i class="required">*</i></label>
              <select class="form-select" name="new_city_id"  id="states_" onchange="get_area_(this.value);" required>
                <option value="">Select City</option>
              </select>
            </div>
            
      
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('pincode'); ?><i class="required">*</i></label>
                 <input type="text" class="form-control" placeholder="Pincode" onkeypress="return isNumberKey(event,this)" maxlength="6"  name="new_pincode" id="pincode_input" required>
              </div>
            </div>
            
            <div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                  <label class="form-label"><?php echo get_phrase('address'); ?><i class="required">*</i></label>
                  <textarea class="form-control"  rows="1" placeholder="Address" name="new_address" id="address" required></textarea>
                </div>
            </div> 
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Candidate Birthday</label>
                 <input type="date" class="form-control flatpickr-max" name="new_date_birth" id="birthday_input" placeholder="YYYY-MM-DD" max="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Candidate Anniversary</label>
                 <input type="date" class="form-control flatpickr-max" name="new_date_anniversary" id="anniversary_input" placeholder="YYYY-MM-DD" max="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1 hidden">
              <div class="form-group">
                 <label class="form-label">Called Type</label>
                 <select class="form-select" name="new_called_type">
                     <option value="Outbound" selected >Outbound</option>
                    <option value="Inbound" >Inbound</option>
                 </select>
              </div>
            </div> 	
			
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Staff Category</label>
                 <select class="form-select" name="new_salary_type" onchange="get_staff_type_(this.value);">
                    <option value="" >Select</option> 
					<?php foreach($staff_category as $cat){?>
					 <option value="<?php echo $cat['id'];?>"><?php echo $cat['name'];?></option>
					 <?php }?>
                 </select>
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Staff Type</label>
                 <select class="form-select" name="new_staff_type" id="new_staff_type"> 
				    <option value="" >Select</option>
                 </select>
              </div>
            </div> 
            
            <!--<div class="col-12 col-sm-6 mb-1"></div>-->
            
            
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Follow Up Date</label>
                 <input type="date" class="form-control" name="new_followup_date" min="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
                
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Follow Up Time</label>
                 <input type="time" class="form-control" name="new_followup_time" >
              </div>
            </div>

            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label class="form-label">Shortlisted</label>
                 <select class="form-select" name="new_is_short">
                    <option value="0" selected >No</option>
                    <option value="1" >Yes</option>
                 </select>
              </div>
            </div> 
            
            <div class="col-12 col-sm-12 mb-1">
              <label class="form-label">Upload Resume <small> (PDF & Docs Only)</small></label>
                <input type="file" class="form-control" name="resume"  accept=".pdf,.docx,.doc">
            </div>
            
            <div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                  <label class="form-label"><?php echo get_phrase('remark'); ?></label>
                  <textarea  class="form-control" rows="1" name="new_remark" placeholder="Remark"></textarea>
                </div>
            </div> 
            
            <div class="row">    
                <div class="col-12 text-center">
                  <button type="submit" class="btn btn-primary btn_verify mt-2 mb-2" name= "btn_verify">Submit</button>
                </div>
            </div> 
            <?php echo form_close(); ?>
            </div>    
            </div> 
            
          </div>
          	
        <!--/ form -->
      </div>
    </div>
    </div>
    
    <div class="col-6 new" id="time-body" style="display:none">
      <ul class="timeline card" id="timeline-body">
        
      </ul>
    </div>           
   
</div>
 
  
<script>  
   function check_provider(provider) {
        if (provider == 'new') {
            $('#new_view').show();
            $('#old_view').hide();
            $('#time-body').hide();
        }else{ 
            $('#new_view').hide();
            $('#old_view').show();
            $('#time-body').show();
        }
    }
    
    
     function check_candidate(candidate_id) {
          //alert(provider);
        $.ajax({
            type: 'POST',
            url: "<?php echo base_url();?>hr/get_ajax_candidate_details",
            async: false,
            dataType: 'json',
            data: {candidate_id:candidate_id},
            success: function(res) {
                if (res.status == "200") {	
				   $('#mobile_no').val(res.data['phone']); 
				   $('#address').val(res.data['address']);  
				   $('#state_input').val(res.data['state_name']);
				   $('#city_input').val(res.data['city_name']); 
				   $('#area_input').val(res.data['area_name']);
				   $('#pincode_input').val(res.data['pincode']);
				   $('#birthday_input').val(res.data['birthday']);
				   $('#anniversary_input').val(res.data['anniversary']);
				   $('#candidate_type').val(res.data['candidate_type']);
				   $('#old_staff_type').val(res.data['staff_type']);
				   $('#old_salary_type').val(res.data['salary_type']);
				   $('#old_is_short').val(res.data['is_short']);
				   
				   if(res.data['resume']!=''){
				     var  new_resume = '<a href="'+res.data['resume']+'" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;display: block;">View Old Resume</a>'  
				     $('#old_resume_1').html(new_resume);
				   }
				   
                } else {
					$('#mobile_no').val('');                    
					$('#address').val('');       
					$('#state_input').val('');  
					$('#city_input').val('');
					$('#area_input').val('');
					$('#pincode_input').val('');
					$('#birthday_input').val('');
					$('#anniversary_input').val('');
					$('#candidate_type').val('');
					$('#old_staff_type').val('');
					$('#old_salary_type').val('');
					$('#old_is_short').val('');
                }
            }
        });
        return false;

    }

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
               $("#new_staff_type").children("option:not(:first)").remove();
               $("#new_staff_type").append(c);
           }
       })
   } 
</script> 

<script>
    function get_timeline_(b) {
   
      var a = {
          candidate_id: b,
          type: 'calls',
      };
      $.ajax({
          type: "POST",
          url: "<?php echo base_url();?>hr/get_timeline_form",
          data: a,
          success: function(c) {
              $("#time-body").show();
              $("#timeline-body").html(c);
          }
      })
    } 
</script>