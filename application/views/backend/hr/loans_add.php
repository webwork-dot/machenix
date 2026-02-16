
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('attendance/loans/add_post', ['class' => 'add-ajax-redirect-image-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
          <div class="row">
		  	  
		    <div class="col-4 col-sm-4 mb-2">
              <label class="form-label">Staff<i class="required">*</i></label>            
              <select class="select2 form-select pure_candidate_ajax" name="emp_id" required>
                <option value="">Search Candidate Name & Mobile No</option>
              </select>
            </div>
             

		   <div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label">Loan Type <i class="required">*</i></label>
                 <select class="form-select" name="loan_type" required>
                    <option value="">Select</option>
                    <option value="mobile_loan">Mobile Loan</option>
                    <option value="cash_loan">Cash Loan</option>
                 </select>
              </div>
            </div> 
			
		   	<div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label">Amount<i class="required">*</i></label>
                 <input type="number" step="any" name="amount" id="amount" class="form-control check-emi" placeholder="Amount" required>
              </div>
            </div>

		     <div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label">Instalment<i class="required">*</i></label>
                 <input type="text" class="form-control check-emi" placeholder="Instalment" onkeypress="return isWholeNumberKey(event,this)" name="instalment" id="instalment" required>
              </div>
            </div>	

		
			
			<div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label">EMI</label>
                 <input type="number" step="any" name="emi" id="emi" class="form-control" placeholder="EMI" readonly disabled> 
              </div>
            </div>   
			
            <div class="col-12 col-sm-4 mb-2">
              <label class="form-label">Applied Date<i class="required">*</i></label>
              <input type="text" class="form-control flatpickr-max" name="applied_date" placeholder="YYYY-MM-DD" required>
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

			<div class="col-12 col-sm-4 mb-1">
			  <label class="form-label">Attachment <small>(accepts only pdf,jpg,png)</small></label>
			  <input type="file" class="form-control" name="attachment" multiple accept=".pdf, .png, .jpg, .jpeg">
			</div>
			
			<div class="col-12 col-sm-4 mb-2">
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
   
   
<script>
 $(document).ready(function () {
	  $('.check-emi').on('keyup', function () {		
		var amount = $('#amount').val();
		var instalment = $('#instalment').val();
		var emi = Number(amount)/Number(instalment);
		emi = parseFloat(emi).toFixed(2);
		
		$('#emi').val(emi);
	  }); 
  }); 
</script>   