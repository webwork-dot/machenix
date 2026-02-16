

<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
          
          <div class="row">
          
          <div class="row mt-1"> 
            
			<div class="col-12 col-sm-6 mb-1 ">
              <div class="form-group">
                 <label><?php echo get_phrase('candidate_name'); ?> : <b><?php echo $data['name']; ?></b></label>
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label><?php echo get_phrase('mobile_no.'); ?> : <b><?php echo ($data['phone']!='' && $data['phone']!=null) ? $data['phone'] : '-' ; ?></b></label>
              </div>
            </div>
            <div class="col-12 col-sm-6 mb-1  ">
              <label><?php echo get_phrase('select_state'); ?> : <b><?php echo ($data['state_name']!='' && $data['state_name']!=null) ? $data['state_name'] : '-' ; ?></b></label>
            </div>
            
            <div class="col-12 col-sm-6 mb-1  ">
              <label><?php echo get_phrase('select_city'); ?> : <b><?php echo ($data['city_name']!='' && $data['city_name']!=null) ? $data['city_name'] : '-' ; ?></b></label>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label><?php echo get_phrase('select_area'); ?> : <b><?php echo ($data['area_name']!='' && $data['area_name']!=null) ? $data['area_name'] : '-' ; ?></b></label>
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label><?php echo get_phrase('pincode'); ?> : <b><?php echo ($data['pincode']!='' && $data['pincode']!=null) ? $data['pincode'] : '-' ; ?></b></label>
              </div>
            </div>
            
            <div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                  <label><?php echo get_phrase('address'); ?> : <b><?php echo ($data['address']!='' && $data['address']!=null) ? $data['address'] : '-' ; ?></b></label>
                </div>
            </div> 
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label>Candidate Birthday : <b><?php echo ($data['dob']!='' && $data['dob']!=null) ? $data['dob'] : '-' ; ?></b></label>
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label>Candidate Anniversary : <b><?php echo ($data['doa']!='' && $data['doa']!=null) ? $data['doa'] : '-' ; ?></b></label>
              </div>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                 <label>Staff Type :  <b><?php echo ($data['staff_type']!='' && $data['staff_type']!=null) ? $data['staff_type'] : '-' ; ?></b></label>
              </div>
            </div> 

            
            <h4 style="margin-top:20px">Documents</h4>
            
            <div class="col-12 col-sm-6 mb-1">
                <label>Pan Card : 
                <?php if($details['pan_card']!='' && $details['pan_card']!= null){?><a href="<?php echo base_url().$details['pan_card']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
                <label>Aadhar Card : 
                <?php if($details['aadhar_card']!='' && $details['aadhar_card']!= null){?><a href="<?php echo base_url().$details['aadhar_card']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            
            <div class="col-12 col-sm-6 mb-1">
                <label>Electricty Bill : 
                <?php if($details['electricty_bill']!='' && $details['electricty_bill']!= null){?><a href="<?php echo base_url().$details['electricty_bill']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Passbook Front Page or Cancel Cheque : 
                <?php if($details['cancel_cheque']!='' && $details['cancel_cheque']!= null){?><a href="<?php echo base_url().$details['cancel_cheque']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Educational Certificates : 
                <?php if($details['educational']!='' && $details['educational']!= null){?><a href="<?php echo base_url().$details['educational']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Experience/Releiving letter/Salary Slips : 
                <?php if($details['salary_slip']!='' && $details['salary_slip']!= null){?><a href="<?php echo base_url().$details['salary_slip']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Photographs with Tie & Formals : 
                <?php if($details['photo']!='' && $details['photo']!= null){?><a href="<?php echo base_url().$details['photo']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Rent Agreement & Electricity Bill : 
                <?php if($details['rent_agreement']!='' && $details['rent_agreement']!= null){?><a href="<?php echo base_url().$details['rent_agreement']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Police Verification : 
                <?php if($details['police_verification']!='' && $details['police_verification']!= null){?><a href="<?php echo base_url().$details['police_verification']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Personal Vehicle RC Book Copy : 
                <?php if($details['rc_book']!='' && $details['rc_book']!= null){?><a href="<?php echo base_url().$details['rc_book']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Passport Copy : 
                <?php if($details['passport']!='' && $details['passport']!= null){?><a href="<?php echo base_url().$details['passport']; ?>" target="_blank" style="font-size: 12px;color: #358948;font-weight: 600;margin-top: 5px;">View </a><?php }else{ echo ' Not Uploaded !!';} ?>
                </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Manager No. : <b><?php echo ($details['manager_no']!='' && $details['manager_no']!=null) ? $details['manager_no'] : '-' ; ?></b> </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>HR No. : <b><?php echo ($details['hr_no']!='' && $details['hr_no']!=null) ? $details['hr_no'] : '-' ; ?></b> </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Alternate Number 1 : <b><?php echo ($details['alternate_no_1']!='' && $details['alternate_no_1']!=null) ? $details['alternate_no_1'] : '-' ; ?></b> </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Alternate Number 2 : <b><?php echo ($details['alternate_no_2']!='' && $details['alternate_no_2']!=null) ? $details['alternate_no_2'] : '-' ; ?></b> </label>
            </div>
            <div class="col-12 col-sm-6 mb-1">
                <label>Relatives Numbers & Address : <b><?php echo ($details['address']!='' && $details['address']!=null) ? $details['address'] : '-' ; ?></b> </label>
            </div>
           
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
</script> 
