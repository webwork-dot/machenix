<style>
	.m-title {
		color: #286545;
		font-weight: 600;
		border-bottom: 1px dashed #286545;
		line-height: 30px;
		padding-bottom: 0px;
	}
	.ptt-10 {
    padding: 8px 6px 8px 0px;
}

</style>

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
					 <p><?= $data['doa'];?></p>
				  </div>
				</div>
					
			  </div>    
			 </div>    
		   </div>    
		 
			
    <div class="card">
      <div class="card-body py-1 my-0">
          
          <div class="row">
          <?php echo form_open('hr/my_staff/update_staff/'.$id, ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?> 
          <div class="row mt-1"> 			
            <div class="col-12 col-sm-4 mb-2">
              <label class="form-label"><?php echo get_phrase('select_bank'); ?><i class="required">*</i></label>          
				<select class="form-select" name="bank_id"  required>
                <option value="">Select Bank</option>
                <?php foreach($banks as $bank){?>
                <option value="<?php echo $bank['id']; ?>" <?php if($data['bank_id'] == $bank['id']){ echo 'selected'; } ?>><?php echo $bank['name'] ?></option>
                <?php }?>
              </select>
            </div>
          </div>
            
         <div class="row"> 
            <div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label"><?php echo get_phrase('account_no'); ?><i class="required">*</i></label>
                 <input type="text" class="form-control" placeholder="Account Number" onkeypress="return isNumberKey(event,this)" name="account_no" value="<?php echo $data['account_no']; ?>" required>
               <span class="invalid-feedback"></span>
              </div>
            </div> 

			<div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label  class="form-label"><?php echo get_phrase('confirm_account_no'); ?><i class="required">*</i></label>
                 <input type="text" class="form-control" placeholder="Confirm Account Number" onkeypress="return isNumberKey(event,this)" name="confirm_account_no" required>
               <span class="invalid-feedback"></span>
              </div>
            </div>	
			
			<div class="col-12 col-sm-4 mb-2">
              <div class="form-group">
                 <label class="form-label">IFSC Code<i class="required">*</i></label>
                 <input type="text" class="form-control" placeholder="IFSC Code" name="ifsc_code" value="<?php echo $data['ifsc_code']; ?>"  required>
               <span class="invalid-feedback"></span>
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
          	
        <!--/ form -->
      </div>
    </div>
    </div>     
   
</div>
 
  