
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
		  
          <?php echo form_open('attendance/tds/edit_post/'.$id, ['class' => 'add-ajax-redirect-image-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
          <div class="row">
		  	  
	  	  
		    <div class="col-4 col-sm-4 mb-2">
              <label class="form-label">Staff<i class="required">*</i></label>            
              <select class="select2 form-select pure_candidate_ajax" name="emp_id" required>
                <option value="<?= $data['emp_id'];?>"><?= $data['emp_name'];?></option>
              </select>
            </div>
			
			<div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label">Amount<i class="required">*</i></label>
                 <input type="number" step="any" name="amount" id="amount" value="<?= $data['amount'];?>" class="form-control check-emi" placeholder="Amount" required>
              </div>
            </div>

	
			
			<div class="col-12 col-sm-4 mb-2">
			<label class="form-label">Deduction On Salary<i class="required">*</i></label>
			<div class="input-group">
				<select class="form-select" name="month" required>
					<option value="" <?php if($data['month']==''){ echo 'selected';}?>>Select Month</option>
					<option value="01" <?php if($data['month']=='01'){ echo 'selected';}?>>January</option>
					<option value="02" <?php if($data['month']=='02'){ echo 'selected';}?>>February</option>
					<option value="03" <?php if($data['month']=='03'){ echo 'selected';}?>>March</option>
					<option value="04" <?php if($data['month']=='04'){ echo 'selected';}?>>April</option>
					<option value="05" <?php if($data['month']=='05'){ echo 'selected';}?>>May</option>
					<option value="06" <?php if($data['month']=='06'){ echo 'selected';}?>>June</option>
					<option value="07" <?php if($data['month']=='07'){ echo 'selected';}?>>July</option>
					<option value="08" <?php if($data['month']=='08'){ echo 'selected';}?>>August</option>
					<option value="09" <?php if($data['month']=='09'){ echo 'selected';}?>>September</option>
					<option value="10" <?php if($data['month']=='10'){ echo 'selected';}?>>October</option>
					<option value="11" <?php if($data['month']=='11'){ echo 'selected';}?>>November</option>
					<option value="12" <?php if($data['month']=='12'){ echo 'selected';}?>>December</option>
				</select>
				<select class="form-select" name="year" required>
					<option value="">Select Year</option>
					<?php
					$currentYear = 2023;
					for ($i = $currentYear; $i <= $currentYear + 10; $i++) {
						$selected = ($data['year'] == $i) ? 'selected' : '';
						echo "<option value='$i' $selected>$i</option>";
					}
					?>
				</select>
			</div>
		</div>

			

			<div class="col-12 col-sm-8 mb-2">
               <div class="form-group">
                <label class="form-label"><?php echo get_phrase('remark'); ?></label>
                <textarea  class="form-control" rows="2" name="remark" maxlength="100"  placeholder="Remark"><?= $data['remark'];?></textarea>
               </div>
            </div> 
		
            
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 mb-2 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
            
          </div>
          <?php echo form_close(); ?>		
        <!--/ form -->
      </div>
    </div>
    </div>
</div>
   
   
