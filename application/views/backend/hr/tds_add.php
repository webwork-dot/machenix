<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
          <?php echo form_open('attendance/tds/add_post', ['class' => 'add-ajax-redirect-image-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
          <div class="row">
		  	  
		    <div class="col-4 col-sm-4 mb-2">
              <label class="form-label">Staff<i class="required">*</i></label>            
              <select class="select2 form-select pure_candidate_ajax" name="emp_id" required>
                <option value="">Search Candidate Name & Mobile No</option>
              </select>
            </div>
              
			
		   	<div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label">Amount<i class="required">*</i></label>
                 <input type="number" step="any" name="amount" id="amount" class="form-control check-emi" placeholder="Amount" required>
              </div>
            </div>

		
			
			<div class="col-12 col-sm-4 mb-2">
			<label class="form-label">Deduction On Salary<i class="required">*</i></label>
			<div class="input-group">
				<select class="form-select" name="month" required>
					<option value="">Select Month</option>
					<option value="01">January</option>
					<option value="02">February</option>
					<option value="03">March</option>
					<option value="04">April</option>
					<option value="05">May</option>
					<option value="06">June</option>
					<option value="07">July</option>
					<option value="08">August</option>
					<option value="09">September</option>
					<option value="10">October</option>
					<option value="11">November</option>
					<option value="12">December</option>
				</select>
				<select class="form-select" name="year" required>
					<option value="">Select Year</option>
					<?php
					$currentYear =2023;
					for ($i = $currentYear; $i <= $currentYear + 10; $i++) {
						echo "<option value='$i'>$i</option>";
					}
					?>
				</select>
			</div>
		</div>

			

			<div class="col-12 col-sm-8 mb-2">
               <div class="form-group">
                <label class="form-label"><?php echo get_phrase('remark'); ?></label>
                <textarea  class="form-control" rows="2" name="remark" maxlength="100"  placeholder="Remark"></textarea>
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
  