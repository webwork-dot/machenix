<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css"/>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
	
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
			<div class="step_1">
              <div class="card mt-0">
				
                <div class="card-body"> 			
				
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-2 m-title"><b>Basic Information</b></h5> 
					   </div>
					</div>
					
                    <div class="row mb-10">						
					     <div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Full Name</label>
							 <p><?= $data['name'];?></p>
						  </div>
						</div>  

						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Mobile Phone</label>
							  <p><?= $data['phone'];?></p>
						  </div>
						</div>	

						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Email Id</label>
							   <p><?= $data['email'];?></p>
						  </div>
						</div>	
					
						
						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							<label class="form-label">Marital Status</label>							
							   <p><?= $data['marital_status'];?></p>
						  </div>
						</div>
						
						
						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Date of Birth</label>	
							   <p><?= $data['dob'];?></p>
						  </div>
						</div>	

						
						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Date of Joining</label>
							 <p><?= $data['doa'];?></p>
						  </div>
						</div>
					
					<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Last Update Document</label>
							 <p><?= $data['doc_date'];?></p>
						  </div>
						</div>
						
						
          					
                      </div>    
                     </div>    
                   </div>    
                 
			
				<div class="card mt-20 mb-10">	 
                  <div class="card-body"> 
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-2 m-title"><b>Present Address</b></h5> 
					   </div>
					</div>
					
				
					<div class="row">	
					
					  <div class="col-12 col-sm-12 mb-1">
					  <div class="form-group">	
					     <label class="form-label">Flat/ Building/ Street</label>
						 <p><?= $data['address'];?></p>
					  </div>
					 </div>	
						
					 <div class="col-12 col-sm-4 mb-1">
					 <div class="form-group">
				     <label class="form-label">State</label>
					 <p><?= $data['state_name'];?></p>
					</div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
					   <label class="form-label">City</label>
					   <p><?= $data['city_name'];?></p>
					 </div> 
					</div> 
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						<label class="form-label">Pincode</label>
					   <p><?= $data['pincode'];?></p>
					  </div>
					  </div>						
					</div>		
				</div>
			</div>
				 
			
              <div class="card mt-20 mb-10">	 
                <div class="card-body">
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-2 m-title"><b>Permanent Address</b></h5> 
					   </div>
					</div>
					
			
					
				
					<div class="row is-permanent mt-20">	
					
						<div class="col-12 col-sm-12 mb-1">
						  <div class="form-group">	
						  <label class="form-label">Flat/ Building/ Street</label>
						  <p><?= $data['p_address'];?></p>					
						</div>
					  </div>	
						
					 <div class="col-12 col-sm-4 mb-1">
						 <div class="form-group">
					    <label class="form-label">State</label>
						<p><?= $data['p_state_name'];?></p>					  
				      </div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
					   <div class="form-group">
					  <label class="form-label">City</label>
					  <p><?= $data['p_city_name'];?></p>		
					  </div> 
					</div> 
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Pincode</label>
					     <p><?= $data['p_pincode'];?></p>							
					  </div>
					</div>
		       </div>	
			 </div>	
			</div>	
				
				
					
			<?php $attach=$data['documents'];?>
            <div class="card mt-20 mb-10">		
                <div class="card-body">  
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="m-title"><b>Documents</b></h5> 						 
					   </div>
					</div>
					
                    <div class="row">
					   <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
						  <label class="form-label">Passport Size Photo </label>
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
                            <label class="form-label">Pan Card</label> 
							
					 	<?php  if($attach['pan_card'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['pan_card']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['pan_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['pan_card'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['pan_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['pan_card'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
						  
					      <small><b>PAN No-</b><?= $attach['pan_no'];?></small>	<br/><br/>				  
                        </div>
                        </div>
						
                       
						
						
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                          <label class="form-label">Aadhar Card</label>
												
					      <?php  if($attach['aadhar_card'] ==NULL){ echo '<p>NA</p>'; } 
						  elseif(get_ext($attach['aadhar_card']) == "pdf"){?>
						  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['aadhar_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['aadhar_card'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>						  
						  <?php } else {?>
						   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['aadhar_card'];?>"><p class="mb-0"><a class="attach-view" href="<?= $attach['aadhar_card'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
						  <?php } ?>
					       <small><b>Aadhar Card No-</b><?= $attach['aadhar_no'];?></small>	<br/><br/>	
                        </div>
                        </div>
						
						
                        
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                            <label class="form-label">Bank Details/Cancelled Cheque</label>
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
                          <label  class="form-label">Educational Certificate</label>
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
                             <label class="form-label">Current Electricity Bill</label>
							  					
						  <?php  if($attach['electricty_bill'] ==NULL){ echo '<p>NA</p>'; } 
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
							  <p><?= $data['hr_no'];?></p>	
                           </div>
                           </div>
						 
						 
						  <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label class="form-label">Police Verification</label>
							  <?php  if($attach['police_verification']==NULL || $attach['police_verification']==''){ echo '<p>NA</p>'; } 
							  elseif(get_ext($attach['police_verification']) == "pdf"){?>
							  <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['police_verification'];?>"><p><a class="attach-view" href="<?= $attach['police_verification'];?>" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a></p></div>
							  <?php } else {?>
							   <div class="ptt-10" data-fancybox="gallery-1" data-src="<?= $attach['police_verification'];?>"><p><a class="attach-view" href="<?= $attach['police_verification'];?>" target="_blank"><i class="fa fa-file-image-o" aria-hidden="true"></i> Image</a></p></div>
							  <?php } ?>
							  
                           </div>
                           </div>
                          </div>
						   
					
                    <div class="row">
    					<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Reference 1</label>
							   <p><?= $attach['ref1_name'];?></p>	
							   
						  </div>
						</div>	

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Reference 1 Mobile Number</label>
							   <p><?= $attach['ref1_mobile'];?></p>								 
						  </div>
						</div>
					
					
    					<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Reference 2</label>
							  <p><?= $attach['ref2_name'];?></p>	
						  </div>
						</div>	

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Reference 2 Mobile Number</label>
							  <p><?= $attach['ref2_mobile'];?></p>	
						  </div>
						</div>
						</div>
                            
                    </div>    
                    </div>    	
          
		  
		  
		  	
              <div class="card mt-20 mb-10">	 
                <div class="card-body">
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-2 m-title"><b>Payroll Details</b></h5> 
					   </div>
					</div>			
			
				
				  <div class="row is-permanent mt-20">					
					<div class="col-12 col-sm-12 mb-1">
						  <div class="form-group">	
						  <label class="form-label">Emp. Code </label>
						  <p><?= $data['emp_id'];?></p>					
						</div>
					  </div>	
						
					 <div class="col-12 col-sm-4 mb-1">
						 <div class="form-group">
					    <label class="form-label">Gross Salary</label>
						<p><?= $data['salary'];?></p>					  
				      </div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
					   <div class="form-group">
					  <label class="form-label">Basic Salary</label>
					  <p><?= $data['basic_salary'];?></p>		
					  </div> 
					</div> 
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">HRA </label>
					     <p><?= $data['hra'];?></p>							
					  </div>
					</div>
						
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Gross Edu. Allow </label>
					     <p><?= $data['gross_edu'];?></p>							
					  </div>
					</div>	
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Shift Type</label>
					     <p><?= get_phrase($data['shift_type']);?></p>							
					  </div>
					</div>	
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Paid Leaves</label>
					     <p><?= $data['paid_leaves'];?></p>							
					  </div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Is PF Applicable</label>
					     <p><?= ($data['is_pf']==1 ? 'Yes':'No');?></p>						
					  </div>
					</div>
					
								
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Is ESIC Applicable</label>
					     <p><?= ($data['is_esic']==1 ? 'Yes':'No');?></p>
					  </div>
					</div>	
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Is TDS Applicable </label>
					     <p><?= ($data['is_tds']==1 ? 'Yes':'No');?></p>
					  </div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Gender</label>
					     <p><?= $data['gender'];?></p>
					  </div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Group of Salary</label>
					     <p><?= $data['salary_type'];?></p>
					  </div>
					</div>
					
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Staff Type</label>
					     <p><?= $data['staff_type'];?></p>
					  </div>
					</div>	
					
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Bank</label>
					     <p><?= ($data['bank'] ? $data['bank']:'-');?></p>
					  </div>
					</div>	
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">Account No</label>
					     <p><?= ($data['account_no'] ? $data['account_no']:'-');?></p>
					  </div>
					</div>

					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label">IFSC Code</label>
					     <p><?= ($data['ifsc_code'] ? $data['ifsc_code']:'-');?></p>
					  </div>
					</div>
					
					
		       </div>	
			   
			 </div>	
			</div>	
				
		  
		  
		  
		  
		           <?php if($data['is_doc']==1 && $data['is_pure']==0):?>
                   <div class="row">
                     <div class="col-12">
					   <div class="col-12 mt-0 text-center">
						<button type="button" class="btn btn-primary btn_verify mt-2  mr-1" name= "btn_verify" onclick="verify_candidate()"><i class="fa fa-check-circle" aria-hidden="true"></i> Verify & Approve</button>
						</div>
					  </div>
					</div>
		           <?php endif;?>
		  
		  
					</div> 
						
						 
          <br/>  
          <br/>  
          <br/>  
   
   <script>
   
    function verify_candidate() {  
	  $('.btn_verify').attr("disabled", true);
	  $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
   
     
      var href =  "<?php echo base_url();?>hr/approved_candidate/<?php echo $id;?>";
      var confirmDlg = duDialog(null, "Are you sure you want to verify candidate?", {
        init: true,
        dark: false, 
        buttons: duDialog.OK_CANCEL,
        okText: 'Proceed',
        callbacks: {
		  cancelClick: function(e) { 
				confirmDlg.hide();
				$('.btn_verify').html('<i class="fa fa-check-circle" aria-hidden="true"></i> Verify &amp; Approve');
				$('.btn_verify').attr("disabled", false);
			  
		  },
          okClick: function(e) {
            $(".dlg-actions").find("button").attr("disabled",true);
            $(".ok-action").html('<i class="fa fa-spinner fa-pulse"></i> Please wait!');
            $(".loader").show();  
            $.ajax({
              type: 'POST',
              url: href,
              dataType: 'json', 
              data: {id:<?php echo $id;?>},
            })
            .done(function(res) {
              confirmDlg.hide();
              if (res.status == '200') {
                $(".loader").hide(); 
                 Swal.fire({
            		title: "Success!",
            		text: res.message,
            		icon: "success",
            		customClass: {
            			confirmButton: "btn btn-primary"
            		},
            		buttonsStyling: !1
            	  }).then(() => {window.location.href = res.url;}); 
        		
              } else {
                  $(".loader").hide(); 
                    Swal.fire({
            			title: "Error!",
            			text: res.message ,
            			icon: "error",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})  
				
                    $('.btn_verify').html('<i class="fa fa-check-circle" aria-hidden="true"></i> Verify &amp; Approve');
                    $('.btn_verify').attr("disabled", false);
              }
            })
            .fail(function(response) {
                $(".loader").fadeOut("slow");  
                Swal.fire({
        			title: "Error!",
        			text: res.message ,
        			icon: "error",
        			customClass: {
        				confirmButton: "btn btn-primary"
        			},
        			buttonsStyling: !1
        		})
				$('.btn_verify').html('<i class="fa fa-check-circle" aria-hidden="true"></i> Verify &amp; Approve');
				$('.btn_verify').attr("disabled", false);
            });
          }
	
        }
      });
      confirmDlg.show();
   } 
   
   
   </script>   