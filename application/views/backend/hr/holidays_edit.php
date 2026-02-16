
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
         <?php echo form_open('hr/holidays/edit_post/'.$id, ['class' => 'add-ajax-redirect-image-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
          <div class="row">
		  
		  
             <div class="col-md-6 col-12">
              <div class="form-group mb-1">
                 <label class="form-label">Staff Category <i class="required">*</i></label>
                 <select class="form-select select2" name="salary_type[]" multiple required>
					<?php $stypes = explode(',',$data['staff_typeid']);
					foreach($staff_category as $cat){?>
					 <option value="<?php echo $cat['id'];?>" <?php if(isset($stypes)){ if(in_array($cat['id'], $stypes)){ echo 'selected'; }}?>><?php echo $cat['name'];?></option>
					 <?php }?>
                 </select>
              </div>
            </div>	
			
		
           
            <div class="col-12 col-sm-6 mb-2">
              <label class="form-label">Holiday Name <i class="required">*</i></label>
              <input type="text" class="form-control" name="holiday_name" value="<?= $data['holiday_name'];?>" placeholder="Holiday Name" required>
            </div>           
           
            
            <div class="col-12 col-sm-6 mb-2">
              <label class="form-label">Holiday Date <i class="required">*</i></label>
              <input type="text" class="form-control flatpickr-all" name="holiday_date" value="<?= $data['holiday_date'];?>" placeholder="YYYY-MM-DD" required>
            </div> 

			
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify" value="Submit"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
          <?php echo form_close(); ?>	
        <!--/ form -->
      </div>
    </div>
    </div>
</div>
   
<script>
function check_salary_type(b) {
	 if(b == 'FIELD STAFF'){
		$('#state_div').show();
		$("#state_id").prop('required',true);
	}else{
		$('#state_id').removeAttr('required');
		$('#state_div').hide();
	}
   
} 
</script>   
