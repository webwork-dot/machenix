 <?php include('_staff_update_tabs.php'); ?>
	 
<div class="row">
  <div class="col-12">
    <!-- profile -->
	
	  <div class="card mt-0">				
		<div class="card-body"> 			
		
			<div class="row">
			   <div class="col-md-12">
				  <h5 class="mb-2 m-title"><b>Basic Information</b></h5> 
			   </div>
			</div>
			
			<div class="row mb-10">						
				 <div class="col-12 col-sm-3 mb-0">
				  <div class="form-group">
					 <label class="form-label">Full Name</label>
					 <p><?= $data['name'];?></p>
				  </div>
				</div>  

				<div class="col-12 col-sm-3 mb-0">
				  <div class="form-group">
					 <label class="form-label">Mobile Phone</label>
					  <p><?= $data['phone'];?></p>
				  </div>
				</div>	

				<div class="col-12 col-sm-3 mb-0">
				  <div class="form-group">
					 <label class="form-label">Email Id</label>
					   <p><?= $data['email'];?></p>
				  </div>
				</div>	
				
				<div class="col-12 col-sm-3 mb-0">
				  <div class="form-group">
					 <label class="form-label">Date of Joining</label>
					 <p><?= $data['joining_date'];?></p>
				  </div>
				</div>	
				
				<div class="col-12 col-sm-3 mb-0">
				  <div class="form-group">
				  <label class="form-label">Resume</label>
				  <?php  if($data['resume'] ==NULL){ echo '<p>NA</p>'; } 
				  elseif(get_ext($data['resume']) == "pdf"){?>
				  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $data['resume'];?>"><p><a class="attach-view" href="<?= $data['resume'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
				  <?php } else {?>
				  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $data['resume'];?>"><p><a class="attach-view" href="<?= $data['resume'];?>" target="_blank"><i class="fa fa fa-file-word-o" aria-hidden="true"></i> Docx</a></p></div>	
				  <?php } ?>
			      </div>
			   </div>
					
			  </div>    
			 </div>    
		   </div>  

	
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('hr/assign_salary/update_salary/'.$id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Emp. Code <i class="required">*</i></label>
              <input type="text" class="form-control" name="emp_id" value="<?= $data['emp_id'];?>" id="emp_id" placeholder="Emp Code"  required>
            </div> 	
			<div class="col-12 col-sm-3 mb-2">
			  <div class="form-group">
				 <label class="form-label">Date of Joining  <i class="required">*</i></label>
				  <input type="text" name="joining_date" class="form-control flatpickr-max" placeholder="DD-MM-YYYY" max="<?php echo date('Y-m-d'); ?>"  value="<?= $data['joining_date_input'];?>" required>
				  <span class="invalid-feedback"></span>
			  </div>
			</div>
            </div>  
			
			<div class="row">
	         <div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Gross Salary <i class="required">*</i></label>
              <input type="number" class="form-control" name="salary" value="<?= $data['salary'];?>" id="salary" placeholder="Gross Salary"  required>
            </div> 
			
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Basic Salary (50%) <i class="required">*</i></label>
              <input type="number" class="form-control" name="basic_salary" value="<?= $data['basic_salary'];?>" id="basic_salary" placeholder="Basic Salary" readonly required>
            </div> 
			
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">HRA (25%) <i class="required">*</i></label>
              <input type="number" class="form-control" name="hra" value="<?= $data['hra'];?>"  id="hra" placeholder="HRA" readonly required>
            </div> 
			
			 <div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Gross Edu. Allow (25%) <i class="required">*</i></label>
              <input type="number" class="form-control" name="gross_edu" value="<?= $data['gross_edu'];?>" id="gross_edu" placeholder="Gross Edu. Allow" readonly required>
            </div>  
            
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Shift Type <i class="required">*</i></label>
              <select class="form-select" name="shift_type" required>
				 <option value="" <?php if($data['shift_type']==''){ echo 'selected';}?>>Select</option> 
				 <?php foreach($shift_types as $stype){?>
				 <option value="<?php echo $stype['value'];?>" <?php if($data['shift_type']==$stype['value']){ echo 'selected';}?>><?php echo $stype['name'];?></option>
				 <?php }?>
              </select>
            </div>	
			 
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Paid Leaves <i class="required">*</i></label>
			  <input type="text" class="form-control" name="paid_leaves" value="<?= $data['paid_leaves'];?>" onkeypress="return isNumberKey(event,this)" placeholder="Paid Leaves" required>
            </div> 	
			
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Is PF Applicable <i class="required">*</i></label>
              <select class="form-select" name="is_pf" required>
               <option value="" <?php if($data['is_pf']==''){ echo 'selected';}?>>Select</option>
				<option value="0" <?php if($data['is_pf']==0){ echo 'selected';}?>>No</option>   
                <option value="1" <?php if($data['is_pf']==1){ echo 'selected';}?>>Yes</option> 
              </select>
            </div>

			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Is ESIC Applicable <i class="required">*</i></label>
              <select class="form-select" name="is_esic" required>
               <option value="" <?php if($data['is_esic']==''){ echo 'selected';}?>>Select</option>
				<option value="0" <?php if($data['is_esic']==0){ echo 'selected';}?>>No</option>   
                <option value="1" <?php if($data['is_esic']==1){ echo 'selected';}?>>Yes</option>  
              </select>
            </div>
			
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Is TDS Applicable <i class="required">*</i></label>
              <select class="form-select" name="is_tds" required>
                <option value="" <?php if($data['is_tds']==''){ echo 'selected';}?>>Select</option>
				<option value="0" <?php if($data['is_tds']==0){ echo 'selected';}?>>No</option>   
                <option value="1" <?php if($data['is_tds']==1){ echo 'selected';}?>>Yes</option> 
              </select>
            </div>	
			
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Is PF Applicable <i class="required">*</i></label>
              <select class="form-select" name="is_pf" required>
               <option value="" <?php if($data['is_pf']==''){ echo 'selected';}?>>Select</option>
				<option value="0" <?php if($data['is_pf']==0){ echo 'selected';}?>>No</option>   
                <option value="1" <?php if($data['is_pf']==1){ echo 'selected';}?>>Yes</option> 
              </select>
            </div>
			
			<div class="col-12 col-sm-3 mb-2">
              <label class="form-label">Is PT Applicable <i class="required">*</i></label>
              <select class="form-select" name="is_ptax" onchange="check_ptax(this.value)" required>
               <option value="" <?php if($data['is_ptax']==''){ echo 'selected';}?>>Select</option>
				<option value="0" <?php if($data['is_ptax']==0){ echo 'selected';}?>>No</option>   
                <option value="1" <?php if($data['is_ptax']==1){ echo 'selected';}?>>Yes</option> 
              </select>
            </div>
			
	
			
			
			<div class="col-12 col-sm-3 mb-2">
              <div class="form-group">
                 <label class="form-label">Gender<i class="required">*</i></label>
                 <select class="form-select" name="gender" required>
                    <option value="" <?php if($data['gender']==''){ echo 'selected';}?>>Select</option>
                    <option value="Male" <?php if($data['gender']=='Male'){ echo 'selected';}?>>Male</option>
                    <option value="Female" <?php if($data['gender']=='Female'){ echo 'selected';}?>>Female</option>
                 </select>
              </div>
            </div>
			
			<div class="col-12 col-sm-3 mb-2">
              <div class="form-group">
                 <label class="form-label">Staff Category<i class="required">*</i></label>
                 <select class="form-select" name="salary_type"  onchange="get_staff_type_(this.value);" required>
                    <option value="" <?php if($data['staff_catid']==''){ echo 'selected';}?>>Select</option> 
					<?php   foreach($staff_category as $cat){?>
					 <option value="<?php echo $cat['id'];?>" <?php if($data['staff_catid']==$cat['id']){ echo 'selected';}?>><?php echo $cat['name'];?></option>
					 <?php }?>
                 </select>
              </div>
            </div>
            
			<div class="col-12 col-sm-3 mb-2">
              <div class="form-group">
                 <label class="form-label">Staff Type<i class="required">*</i></label>
                 <select class="form-select" name="staff_type" id="staff_type" required> 
				    <option value="" <?php if($data['staff_typeid']==''){ echo 'selected';}?>>Select</option> 
					<?php foreach($staff_types as $stype){?>
					 <option value="<?php echo $stype['id'];?>" <?php if($data['staff_typeid']==$stype['id']){ echo 'selected';}?>><?php echo $stype['name'];?></option>
					 <?php }?>
                 </select>
              </div>
            </div>   

			<div class="col-12 col-sm-3 mb-2">
			  <label for="status" class="form-label">Status</label>
			  <select id="status" class="form-select" name="status">
				<option value="1" <?php echo ($data['status'] == '1') ? 'selected':'';?>>Active</option>
				<option value="0" <?php echo ($data['status'] == '0') ? 'selected':'';?>>In Active</option>
			  </select>
			</div>
    

            <div class="col-12">
                <button type="submit" class="btn btn-primary mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
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
   
   function check_salary(b) {
	 
        if(b == '1'){
            $('.disp-tcs').show();
            $(".disp-tcs input").prop('required',true);
        }
		else{   
            $('.disp-tcs').show();
            $(".disp-tcs input").prop('required',false);
        }
    }
	
	
	
$(document).ready(function() {
   //SALARY BIFURCATION
  var totalInput = $("#salary");
  var basic_salary = $("#basic_salary");
  var hra = $("#hra");
  var gross_edu = $("#gross_edu");
  totalInput.on("input", function() {
    var total = parseFloat(totalInput.val());

    // Calculate 25%-50% amount
    var twentyFivePercent = total * 0.25;
    var fiftyPercent = total * 0.5;
	
    // set 25% amount
    hra.val(twentyFivePercent.toFixed(2));
    gross_edu.val(twentyFivePercent.toFixed(2));

    // set 50% amount
    basic_salary.val(fiftyPercent.toFixed(2));
  });
});

   
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
    