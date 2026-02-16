<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css"/>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
	
<style>
.mb-10 {
    margin-bottom: 15px!important;
}
.mt-20{
    margin-top: 15px!important;
}
	.ptt-10 {
    padding: 8px 6px 8px 0px;
}
.attach-view {
    padding: 6px 5px;
    border-radius: 10px;
    border: 1px solid #1e652e;
    font-size: 11px;
    font-weight: 500;
    color: #1e652e;
}

.attach-div {
    display: flex;
    margin-bottom: 5px;
    justify-content: flex-start;
    flex-wrap: wrap;
}

</style>


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


    
    <?php echo form_open_multipart('hr/assign_salary/update_staff_details/'.$id, ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?>  
					
     <div class="card">
      <div class="card-body py-1 my-0">   
		<div class="row">
		   <div class="col-md-12">
			  <h5 class="mb-2 m-title"><b>Bank Information</b></h5> 
		   </div>
		</div>
	  
          <div class="row">
          <div class="row"> 			
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
                 <input type="text" class="form-control" placeholder="Confirm Account Number" value="<?php echo $data['account_no']; ?>" onkeypress="return isNumberKey(event,this)" name="confirm_account_no" required>
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
			
           </div> 
         </div>
      </div>
    </div>   
	
	
	 <div class="card">
      <div class="card-body py-1 my-0">   
		<div class="row">
		   <div class="col-md-12">
			  <h5 class="mb-2 m-title"><b>Present Address</b></h5> 
		   </div>
		</div>
	  
       
					<div class="row">
					  <div class="col-12 col-sm-12 mb-1">
					  <div class="form-group">	
					  <label class="form-label">Flat/ Building/ Street <i class="required">*</i></label>
						<textarea class="form-control" rows="4" placeholder="Flat/ Building/ Street" name="address"  required><?php echo $data['address']; ?></textarea>
						  <span class="invalid-feedback"></span>
					  </div>
					 </div>	
						
					 <div class="col-12 col-sm-4 mb-1">
					 <div class="form-group">
				     <label class="form-label"><?php echo get_phrase('select_state'); ?> <i class="required">*</i></label>
					 <select class="form-control" name="state_id" id="state_id" onchange="get_city_(this.value);" required>
						<option value="">Select State</option>
						<?php foreach($states as $state){?>
						<option value="<?php echo $state['id']; ?>" <?php if($data['state_id']==$state['id']){ echo 'selected';}?>><?php echo $state['name'] ?></option>';
						<?php }?>
					  </select>
				  <span class="invalid-feedback"></span>
					</div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
					 <div class="form-group">
					  <label class="form-label"><?php echo get_phrase('select_city'); ?> <i class="required">*</i></label>
					  <select class="form-control" name="city_id"  id="states_" required>
						<option value="">Select City</option>
						<?php foreach($citys as $city){?>
						<option value="<?php echo $city['id']; ?>" <?php if($data['city_id']==$city['id']){ echo 'selected';}?>><?php echo $city['name'] ?></option>';
						<?php }?>
					  </select>
					  <span class="invalid-feedback"></span>
					</div> 
					</div> 
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label"><?php echo get_phrase('pincode'); ?> <i class="required">*</i></label>
						 <input type="text" class="form-control" placeholder="Pincode" onkeypress="return isNumberKey(event,this)" maxlength="6"  name="pincode" value="<?= $data['pincode'];?>" id="pincode_input" required>
						  <span class="invalid-feedback"></span>
					  </div>
					</div>						
					</div>
					
      </div>
    </div>   
	
		
	 <div class="card">
      <div class="card-body py-1 my-0">   
		<div class="row">
		   <div class="col-md-12">
			  <h5 class="mb-2 m-title"><b>Permanent Address</b></h5> 
		   </div>
		</div>
	  
          <div class="row">
        <div class="col-12 mt-2 mb-0">
					   <div class="form-check">
						  <input class="form-check-input mb-0" type="checkbox" value="1"  name="is_same" id="is_same" onchange="is_permanent(this)" <?php if($data['is_same_check']==1){ echo 'checked';}?>>
						  <label class="form-check-label mb-0" for="is_same">
							Is this your Permanent Address as well?
						  </label>
						</div>
					</div>
					
				
					<div class="row is-permanent mt-20 <?php if($data['is_same_check']==1){ echo 'hide'; $ireq='';} else { $ireq='required';} ?>">	
					
						<div class="col-12 col-sm-12 mb-1">
						  <div class="form-group">	
						  <label  class="form-label">Flat/ Building/ Street <i class="required">*</i></label>						
							<textarea class="form-control"  rows="4" placeholder="Flat/ Building/ Street" name="p_address" <?= $ireq;?>><?= $data['p_address'];?></textarea>
						  <span class="invalid-feedback"></span>
						</div>
					  </div>	
						
					 <div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
					  <label class="form-label"><?php echo get_phrase('select_state'); ?> <i class="required">*</i></label>
					  <select class="form-control" name="p_state_id" id="p_state_id" onchange="get_p_city_(this.value);" <?= $ireq;?>>
						<option value="">Select State</option>
						<?php foreach($states as $state){?>
						<option value="<?php echo $state['id']; ?>" <?php if($data['p_state_id']==$state['id']){ echo 'selected';}?>><?php echo $state['name'] ?></option>';
						<?php }?>
					  </select>
					  <span class="invalid-feedback"></span>
					</div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
					  <label class="form-label"><?php echo get_phrase('select_city'); ?> <i class="required">*</i></label>
					  <select class="form-control" name="p_city_id"  id="p_states_" <?= $ireq;?>>
						<option value="">Select City</option>
						<?php foreach($p_citys as $city){?>
						<option value="<?php echo $city['id']; ?>" <?php if($data['p_city_id']==$city['id']){ echo 'selected';}?>><?php echo $city['name'] ?></option>';
						<?php }?>
					  </select>
					  <span class="invalid-feedback"></span>
					</div> 
					</div> 
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label"><?php echo get_phrase('pincode'); ?> <i class="required">*</i></label>
						 <input type="text" class="form-control" placeholder="Pincode" onkeypress="return isNumberKey(event,this)" maxlength="6"  name="p_pincode"  value="<?= $data['p_pincode'];?>" <?= $ireq;?>>
						  <span class="invalid-feedback"></span>
					  </div>
					</div>
         </div>
      </div>
    </div>   
    </div>   
	
	
	<?php $attach=$data['documents'];?>
		
	 <div class="card">
      <div class="card-body py-1 my-0">   
		<div class="row">
		   <div class="col-md-12">
			  <h5 class="mb-0 m-title mb-2 "><b>Documents</b>
			  <small class="m-note">
						  (Note- Image must be clear. Max file size 2mb allowed. Allowed only jpg, png or pdf file formats)
						  </small></h5> 
		   </div>
		</div>
	  
          <div class="row">
            <div class="row">
				   <div class="col-4 col-xs-12 mb-10">
					  <div class="form-group">
					  <label class="form-label">Passport Size Photo  <i class="required">*</i></label>
					  <input type="file" class="form-control"  max-size="10" name="passport_pic" id="passport_pic" accept=".pdf,image/png,image/jpeg" >	
					  <span class="invalid-feedback"></span>

   					  <?php  if($attach['passport_pic'] ==NULL){ echo '<p>NA</p>'; } 
					  elseif(get_ext($attach['passport_pic']) == "pdf"){?>
					  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['passport_pic'];?>"><p><a class="attach-view" href="<?= $attach['passport_pic'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
					  <?php } else {?>
					   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['passport_pic'];?>"><p><a class="attach-view" href="<?= $attach['passport_pic'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
					  <?php } ?>
				   </div>
				   </div>
				   
                  
				   
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                            <label class="form-label">Pan Card <i class="required">*</i></label>
                            <input type="file" class="form-control" name="pan_card" id="pan_card"  accept=".pdf,image/png,image/jpeg" >
							<input type="text" class="form-control mt-1" name="pan_no" id="pan_no" maxlength="10" placeholder="Pan Card Number" value="<?= $attach['pan_no'];?>" >	
							<span class="invalid-feedback"></span>
							
							<?php  if($attach['pan_card'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['pan_card']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['pan_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['pan_card'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['pan_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['pan_card'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
                        </div>
                        </div>
						
                       
						
						
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                            <label class="form-label">Aadhar Card <i class="required">*</i></label>
                            <input type="file" class="form-control" name="aadhar_card" id="aadhar_card" accept=".pdf,image/png,image/jpeg">
							<input type="text" class="form-control mt-1" name="aadhar_no" maxlength="12" placeholder="Aadhar Card Number" value="<?= $attach['aadhar_no'];?>" >
						     <span class="invalid-feedback"></span>
							 
						 <?php  if($attach['aadhar_card'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['aadhar_card']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['aadhar_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['aadhar_card'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['aadhar_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['aadhar_card'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
                        </div>
                        </div>
						
						
                        
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                            <label class="form-label">Bank Details/Cancelled Cheque <i class="required">*</i></label>
                            <input type="file" class="form-control" name="bank_details" id="bank_details"  accept=".pdf,image/png,image/jpeg" >
						   <span class="invalid-feedback"></span>
						   
						  <?php  if($attach['bank_details'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['bank_details']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['bank_details'];?>"><p><a class="attach-view" href="<?= $attach['bank_details'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['bank_details'];?>"><p><a class="attach-view" href="<?= $attach['bank_details'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
                   
                        </div>
                        </div>
						


                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label  class="form-label">Educational Certificate <i class="required">*</i></label>
                              <input type="file" class="form-control" name="educational" id="educational" accept=".pdf,image/png,image/jpeg" >
						   <span class="invalid-feedback"></span>
						   
						    <?php  if($attach['educational'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['educational']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['educational'];?>"><p><a class="attach-view" href="<?= $attach['educational'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['educational'];?>"><p><a class="attach-view" href="<?= $attach['educational'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
                           </div>
                           </div>

                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label class="form-label">Previous Company offer letter/Salary Slips :</label>
                              <input type="file" class="form-control" name="salary_slip" id="salary_slip"
                                 accept=".pdf,image/png,image/jpeg">
						    <span class="invalid-feedback"></span>
							
						 <?php  if($attach['salary_slip'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['salary_slip']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['salary_slip'];?>"><p><a class="attach-view" href="<?= $attach['salary_slip'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['salary_slip'];?>"><p><a class="attach-view" href="<?= $attach['salary_slip'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
						  
                           </div>
                           </div>

                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label class="form-label">Current Electricity Bill <i class="required">*</i></label>
                              <input type="file" class="form-control" name="electricty_bill" id="electricty_bill" accept=".pdf,image/png,image/jpeg" >
						     <span class="invalid-feedback"></span>
							 
						  <?php if($attach['electricty_bill'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['electricty_bill']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['electricty_bill'];?>"><p><a class="attach-view" href="<?= $attach['electricty_bill'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['electricty_bill'];?>"><p><a class="attach-view" href="<?= $attach['electricty_bill'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
                           </div>
                           </div>

                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label  class="form-label">Rent Agreement</label>
                              <input type="file" class="form-control" name="rent_agreement" id="rent_agreement" accept=".pdf,image/png,image/jpeg">	

							  <?php  if($attach['rent_agreement'] ==NULL){ echo '<p>NA</p>'; } 
							  elseif(get_ext($attach['rent_agreement']) == "pdf"){?>
							  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['rent_agreement'];?>"><p><a class="attach-view" href="<?= $attach['rent_agreement'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>
							  <?php } else {?>
							   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['rent_agreement'];?>"><p><a class="attach-view" href="<?= $attach['rent_agreement'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
							  <?php } ?>
                           </div>
                           </div>

						    
						   <div class="col-4 col-xs-12 mb-10">
						   <div class="form-group">
                              <label  class="form-label">Previous Company HR/Manager Number</label>
							  <input type="text" class="form-control" name="hr_no"  value="<?= $data['hr_no'];?>" placeholder="HR/Manager Number" onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10">
                           </div>
                           </div>
						 
						 
						  <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label class="form-label">Police Verification</label>
                              <input type="file" class="form-control" name="police_verification" id="police_verification" accept=".pdf,image/png,image/jpeg">
							  
							    <?php  if($attach['police_verification']==NULL || $attach['police_verification']==''){ echo '<p>NA</p>'; } 
							  elseif(get_ext($attach['police_verification']) == "pdf"){?>
							  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['police_verification'];?>"><p><a class="attach-view" href="<?= $attach['police_verification'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>
							  <?php } else {?>
							   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['police_verification'];?>"><p><a class="attach-view" href="<?= $attach['police_verification'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
							  <?php } ?>
                           </div>
                           </div>
                          </div>
						  
						  
						  
         </div>
      </div>
    </div>   
	
	
	
		
	 <div class="card">
      <div class="card-body py-1 my-0">   
		<div class="row">
		   <div class="col-md-12">
			  <h5 class="mb-2 m-title"><b>Reference Information</b></h5> 
		   </div>
		</div>
	  
          <div class="row">
		
			<div class="row">
				<div class="col-12 col-sm-3 mb-1">
				  <div class="form-group">
					 <label class="form-label">Reference 1</label>
					 <input type="text" class="form-control alphaonly" name="ref1_name" value="<?= $attach['ref1_name'];?>" placeholder="Reference 1 Name">
				   <span class="invalid-feedback"></span>
				  </div>
				</div>	

				<div class="col-12 col-sm-3 mb-1">
				  <div class="form-group">
					 <label class="form-label hidden-xs"></label>
					 <input type="text" class="form-control" name="ref1_mobile" value="<?= $attach['ref1_mobile'];?>"  placeholder="Reference 1 Mobile Number" onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10">
					 <span class="invalid-feedback"></span>
				  </div>
				</div>
			
			
				<div class="col-12 col-sm-3 mb-1">
				  <div class="form-group">
					 <label class="form-label">Reference 2</label>
					 <input type="text" class="form-control alphaonly" name="ref2_name"  value="<?= $attach['ref2_name'];?>" placeholder="Reference 2 Name">
					<span class="invalid-feedback"></span>
				  </div>
				</div>	

				<div class="col-12 col-sm-3 mb-1">
				  <div class="form-group">
					 <label class="form-label hidden-xs"></label>
					 <input type="text" class="form-control" name="ref2_mobile" value="<?= $attach['ref2_mobile'];?>"  placeholder="Reference 2 Mobile Number"   onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10">
					 <span class="invalid-feedback"></span>
				  </div>
				</div>
			</div>
         </div>
      </div>
    </div>   
	
		
	
		<div class="col-12 mb-5">
			<center><button type="submit" class="btn btn-primary mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('verify_and_submit'); ?></button></center>
		</div>
	
	<?php echo form_close(); ?>	
		  
		  
    </div>
</div>



<script>
    function is_permanent(el) {
		if ($(el).is(':checked')) {
			$(".is-permanent").hide();                
			$('.is-permanent select,.is-permanent input,.is-permanent textarea').removeAttr('required');
		} else {
			$(".is-permanent").show();                
			$('.is-permanent select,.is-permanent input,.is-permanent textarea').attr('required', true); 
		}
	}


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


   var MAX_FILE_SIZE = 2 * 1024 * 1024; // 5MB
   $(document).ready(function() {
		$('#passport_pic,#pan_card,#aadhar_card,#bank_details,#educational,#salary_slip,#electricty_bill,#rent_agreement,#hr_no,#police_verification').change(function() {
        fileSize = this.files[0].size;
        if (fileSize > MAX_FILE_SIZE) { 
			$('.btn_verify').attr("disabled", true);
            this.setCustomValidity("File must not exceed 2 MB!"); 
            this.reportValidity();
			$(this).val('');
        } else { 
    		$('.btn_verify').attr("disabled", false);
            this.setCustomValidity("");
        }
    });
	 
  });
</script>   
    