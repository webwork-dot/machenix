<link rel="stylesheet" href="<?php echo rapl_url();?>assets/css/cust_style.css">

<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<script src="<?= base_url();?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://crm.raplgroup.in/assets/css/style.css">
 <link rel="stylesheet" type="text/css" href="https://crm.raplgroup.in/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<script src="https://crm.raplgroup.in/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script> 

<style type="text/css">
	.btn.disabled, .btn:disabled, fieldset:disabled .btn {
		opacity: .65;
		cursor: not-allowed;
	}
   .form-control, .form-control:focus {
        color: #6E6B7B;
        background-color: #FFF;
    }
    .form-control, .form-control-plaintext, .form-select {
        line-height: 1.45;
        width: 100%;
    }
    .form-control {
        height: auto;
        display: block;
		border: 1px solid #D8D6DE !important;
        padding: .4rem 0.5rem !important;
        font-size: 1rem;
        background-clip: padding-box;
        appearance: none;
        border-radius: 0.357rem;
        -webkit-transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
		height: 40px !important;
        line-height: 28px;
    }
	
	.form-control, .form-control:focus {
		color: #6E6B7B !important;;
		background-color: #FFF !important;;
	}
    label{
         color: black;
         font-weight: 500;
    }
	
	.form-control:disabled, .form-control[readonly] {  
		cursor: no-drop;
	}
	.m-title {
		font-size: 17px;
		color: #286545;
		font-weight: 600;
		border-bottom: 1px dashed #286545;
		line-height: 10px;
		padding-bottom: 12px;
	}
	
	.required{ color:red}
	
.form-label {
    font-size: 16px;
    color: #000;
    font-weight: 600;
    margin-bottom: 3px;
}

.form-control:disabled {
    background-color: #e9ecef!Important;
    opacity: 1;
}
.mb-10 {
    margin-bottom: 15px!important;
}

textarea.form-control {
    min-height: calc(3em + .75rem + 2px);
}
.card {
    border-radius: 8px;
}

body {
    background-color: rgb(205, 232, 192);
}

.header-style-1.header-height-2 {
    border-bottom: 0;
    background: #116B31;
    border-radius: 8px;
    margin-top: 10px;
}


.form-check-input  {
    padding-left: 15px;
}
.m-note {
    color: #ff1100;
    line-height: 20px;
    display: block;
    margin-top: 10px;
    font-size: 13px;
}
@media only screen and (max-width: 767px) {
   .logo {
		padding: 0px 0;
	}
	.header-bottom {
		padding: 0px 0;
	}
	.header-style-1 .header-bottom-bg-color {
		background-color: #1b612f;
	}
   }
   
 

</style> 

<style> 
</style> 

	  
	  
<main class="main">
   <div class="page-content pt-0">
      <div class="container new-width">
	  
	    
   <header class="header-area header-style-1 header-height-2">
         <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container f-center">
               <div class="header-wrap">
                    <div class="logo logo-width-1">
                     <a href="#"><img src="<?php echo rapl_url();?>assets/imgs/logo.png" alt="logo"></a>
                    </div>
                    <div class="header-right">
                     <div class="search-style-2">
                        <form action="#">
                           </form>
                     </div>
                     <div class="header-action-right">
                        <div class="header-action-2">
                            <div id="cart_items">
                            </div>
                           
                        </div>
                     </div>
                    </div>
                </div>
            </div>
            <div class="header-menu">
                <img src="<?php echo rapl_url(); ?>assets/imgs/leaf.png" class="leaf" />
                <div class="container">
                    <div class="row">
                
                    </div>
                </div>
            </div>
         </div>
         
         <div class="header-bottom header-bottom-bg-color sticky-bar hidden-md hidden-lg">
            <div class="container f-center">
               <div class="header-wrap">
                  <div class="logo">
                     <a href="#"><img src="<?php echo rapl_url();?>assets/imgs/logo-new.jpg" alt="logo2"></a>
                  </div>
               </div>
            </div>
         </div>
      </header>
   <div class="checkout-area mt-15">	   

          
				
			<form action="<?php echo base_url().'candidate_front/add_documentation/'.$order_id;?>" class="add-ajax-redirect-image-form was-validated" method="post" accept-charset="utf-8">
			<div class="step_1">
              <div class="card mt-0 mb-10">
			     <div class="row">
					  <h2 class="title-detail text-center mb-1 mt-3">Documentation</h2>
				   </div>		
				<hr>
				
                <div class="card-body"> 			
				
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-3 m-title"><b>Basic Information</b></h5> 
					   </div>
					</div>
					
                    <div class="row mb-10">						
					     <div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Full Name</label>
							 <input type="text" class="form-control" value="<?php echo $data['name']; ?>" name="name" disabled>
						  </div>
						</div>  

						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Mobile Phone</label>
							 <input type="text" class="form-control" value="<?php echo $data['phone']; ?>" name="phone" disabled>
						  </div>
						</div>	

						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Email Id <i class="required">*</i></label>
							 <input type="email" class="form-control" placeholder="Email Id" value="<?php echo $data['email'];?>" name="email" required>
							  <span class="invalid-feedback"></span>
						  </div>
						</div>	
					
						
						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							<label class="form-label">Marital Status <i class="required">*</i></label>
							<select class="form-control" name="marital_status" required>
								<option value="">Select</option>
								<option value="Married">Married</option>
								<option value="Single">Single</option>
								<option value="Divorced">Divorced</option>
							</select>
						  <span class="invalid-feedback"></span>
						  </div>
						</div>
						
						
						<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
							 <label class="form-label">Date of Birth <i class="required">*</i></label>
							  <input type="text" name="dob" class="form-control flatpickr-basic" placeholder="DD-MM-YYYY" max="<?php echo date('Y-m-d'); ?>" required>
							  <span class="invalid-feedback"></span>
						  </div>
						</div>	
						
          					
                      </div>    
                     </div>    
                   </div>    
                 
			
				<div class="card mt-20 mb-10">	 
                  <div class="card-body"> 
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-3 m-title"><b>Present Address</b></h5> 
					   </div>
					</div>
					
				
					<div class="row">	
					
					  <div class="col-12 col-sm-12 mb-1">
					  <div class="form-group">	
					  <label class="form-label">Flat/ Building/ Street <i class="required">*</i></label>
						<textarea class="form-control"  rows="4" placeholder="Flat/ Building/ Street" name="address"  required></textarea>
						  <span class="invalid-feedback"></span>
					  </div>
					 </div>	
						
					 <div class="col-12 col-sm-4 mb-1">
					 <div class="form-group">
				     <label class="form-label"><?php echo get_phrase('select_state'); ?> <i class="required">*</i></label>
					 <select class="form-control" name="state_id" id="state_id" onchange="get_city_(this.value);" required>
						<option value="">Select State</option>
						<?php foreach($states as $state){?>
						<option value="<?php echo $state['id']; ?>"><?php echo $state['name'] ?></option>';
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
					  </select>
					  <span class="invalid-feedback"></span>
					</div> 
					</div> 
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label"><?php echo get_phrase('pincode'); ?> <i class="required">*</i></label>
						 <input type="text" class="form-control" placeholder="Pincode" onkeypress="return isNumberKey(event,this)" maxlength="6"  name="pincode" id="pincode_input" required>
						  <span class="invalid-feedback"></span>
					  </div>
					</div>						
					</div>
					
					
		
					</div>
					</div>
				 
			
            <div class="card mt-20 mb-10">	 
                <div class="card-body"> 
			
				
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-3 m-title"><b>Permanent Address</b></h5> 
					   </div>
					</div>
					
						
					<div class="col-12 mt-2 mb-0">
					   <div class="form-check">
						  <input class="form-check-input mb-0" type="checkbox" value="1"  name="is_same" id="is_same" onchange="is_permanent(this)">
						  <label class="form-check-label mb-0" for="is_same">
							Is this your Permanent Address as well?
						  </label>
						</div>
					</div>
					
				
					<div class="row is-permanent mt-20">	
					
						<div class="col-12 col-sm-12 mb-1">
						  <div class="form-group">	
						  <label>Flat/ Building/ Street <i class="required">*</i></label>						
							<textarea class="form-control"  rows="4" placeholder="Flat/ Building/ Street" name="p_address" required></textarea>
						  <span class="invalid-feedback"></span>
						</div>
					  </div>	
						
					 <div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
					  <label class="form-label"><?php echo get_phrase('select_state'); ?> <i class="required">*</i></label>
					  <select class="form-control" name="p_state_id" id="p_state_id" onchange="get_p_city_(this.value);" required>
						<option value="">Select State</option>
						<?php foreach($states as $state){?>
						<option value="<?php echo $state['id']; ?>"><?php echo $state['name'] ?></option>';
						<?php }?>
					  </select>
					  <span class="invalid-feedback"></span>
					</div>
					</div>
					
					<div class="col-12 col-sm-4 mb-1">
						  <div class="form-group">
					  <label class="form-label"><?php echo get_phrase('select_city'); ?> <i class="required">*</i></label>
					  <select class="form-control" name="p_city_id"  id="p_states_" required>
						<option value="">Select City</option>
					  </select>
					  <span class="invalid-feedback"></span>
					</div> 
					</div> 
					
					<div class="col-12 col-sm-4 mb-1">
					  <div class="form-group">
						 <label class="form-label"><?php echo get_phrase('pincode'); ?> <i class="required">*</i></label>
						 <input type="text" class="form-control" placeholder="Pincode" onkeypress="return isNumberKey(event,this)" maxlength="6"  name="p_pincode"  required>
						  <span class="invalid-feedback"></span>
					  </div>
					</div>
						
		       </div>	
			 </div>	
			</div>	
				
				
					
			
            <div class="card mt-20 mb-10">		
                <div class="card-body">  
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="m-title"><b>Documents</b></h5> 
						  <small class="mb-3 m-note">
						  (Note- Image must be clear. Max file size 2mb allowed.<br>Allowed only jpg, png or pdf file formats)
						  </small>
					   </div>
					</div>
					
                    <div class="row">
					   <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
						  <label class="form-label">Passport Size Photo  <i class="required">*</i></label>
						  <input type="file" class="form-control"  max-size="10" name="passport_pic" id="passport_pic" accept=".pdf,image/png,image/jpeg" required>
						  <span class="invalid-feedback"></span>
					   </div>
					   </div>
					   
                  
				   
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                            <label class="form-label">Pan Card</label>
                            <input type="file" class="form-control" name="pan_card" id="pan_card"  accept=".pdf,image/png,image/jpeg" >
							<input type="text" class="form-control mt-1" name="pan_no" id="pan_no" maxlength="10" placeholder="Pan Card Number">
							<span class="invalid-feedback"></span>
                        </div>
                        </div>
						
                       
						
						
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                            <label class="form-label">Aadhar Card <i class="required">*</i></label>
                            <input type="file" class="form-control" name="aadhar_card" id="aadhar_card" accept=".pdf,image/png,image/jpeg" required>
							<input type="text" class="form-control mt-1" name="aadhar_no" maxlength="12" placeholder="Aadhar Card Number" required>
						     <span class="invalid-feedback"></span>
                        </div>
                        </div>
						
						
                        
                        <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                            <label class="form-label">Bank Details/Cancelled Cheque <i class="required">*</i></label>
                            <input type="file" class="form-control" name="bank_details" id="bank_details"  accept=".pdf,image/png,image/jpeg" required>
						   <span class="invalid-feedback"></span>
                   
                        </div>
                        </div>
						


                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label  class="form-label">Educational Certificate <i class="required">*</i></label>
                              <input type="file" class="form-control" name="educational" id="educational"
                                 accept=".pdf,image/png,image/jpeg" required>
						   <span class="invalid-feedback"></span>
                           </div>
                           </div>

                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label class="form-label">Previous Company offer letter/Salary Slips :</label>
                              <input type="file" class="form-control" name="salary_slip" id="salary_slip"
                                 accept=".pdf,image/png,image/jpeg">
						    <span class="invalid-feedback"></span>
                           </div>
                           </div>

                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label class="form-label">Current Electricity Bill </label>
                              <input type="file" class="form-control" name="electricty_bill" id="electricty_bill" accept=".pdf,image/png,image/jpeg" >
						     <span class="invalid-feedback"></span>
                           </div>
                           </div>

                           <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label  class="form-label">Rent Agreement</label>
                              <input type="file" class="form-control" name="rent_agreement" id="rent_agreement" accept=".pdf,image/png,image/jpeg">
                           </div>
                           </div>

						    
						   <div class="col-4 col-xs-12 mb-10">
						   <div class="form-group">
                              <label  class="form-label">Previous Company HR/Manager Number</label>
							  <input type="text" class="form-control" name="hr_no"  placeholder="HR/Manager Number" onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10">
                           </div>
                           </div>
						 
						 
						  <div class="col-4 col-xs-12 mb-10">
						  <div class="form-group">
                              <label class="form-label">Police Verification</label>
                              <input type="file" class="form-control" name="police_verification" id="police_verification" accept=".pdf,image/png,image/jpeg">
                           </div>
                           </div>
                          </div>
						  
						  
						  
				 <div class="row mt-1"> 			
					<div class="col-12 col-sm-4  mb-10">
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
					<div class="col-12 col-sm-4  mb-10">
					  <div class="form-group">
						 <label class="form-label"><?php echo get_phrase('account_no'); ?><i class="required">*</i></label>
						 <input type="text" class="form-control" placeholder="Account Number" onkeypress="return isNumberKey(event,this)" name="account_no" value="<?php echo $data['account_no']; ?>" required>
					   <span class="invalid-feedback"></span>
					  </div>
					</div> 

					<div class="col-12 col-sm-4 mb-10">
					  <div class="form-group">
						 <label  class="form-label"><?php echo get_phrase('confirm_account_no'); ?><i class="required">*</i></label>
						 <input type="text" class="form-control" placeholder="Confirm Account Number" onkeypress="return isNumberKey(event,this)" name="confirm_account_no" required>
					   <span class="invalid-feedback"></span>
					  </div>
					</div>	
					
					<div class="col-12 col-sm-4 mb-10">
					  <div class="form-group">
						 <label class="form-label">IFSC Code<i class="required">*</i></label>
						 <input type="text" class="form-control" placeholder="IFSC Code" name="ifsc_code" value="<?php echo $data['ifsc_code']; ?>"  required>
					   <span class="invalid-feedback"></span>
					  </div>
					</div>
				
					</div>  	  
						  
						  
						   
					
                    <div class="row">
    					<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Reference 1 <i class="required">*</i></label>
							 <input type="text" class="form-control alphaonly" name="ref1_name" placeholder="Reference 1 Name" required>
						   <span class="invalid-feedback"></span>
						  </div>
						</div>	

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label hidden-xs"></label>
							 <input type="text" class="form-control" name="ref1_mobile"  placeholder="Reference 1 Mobile Number" onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10" required>
						     <span class="invalid-feedback"></span>
						  </div>
						</div>
					
					
    					<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Reference 2 <i class="required">*</i></label>
							 <input type="text" class="form-control alphaonly" name="ref2_name" placeholder="Reference 2 Name" required>
						    <span class="invalid-feedback"></span>
						  </div>
						</div>	

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label hidden-xs"></label>
							 <input type="text" class="form-control" name="ref2_mobile"  placeholder="Reference 2 Mobile Number"   onkeypress="return isNumberKey(event,this)" minlength="10" maxlength="10" required>
						     <span class="invalid-feedback"></span>
						  </div>
						</div>
						</div>
                            
                    </div>    
                    </div>    	
                        
					<div class="row">    
						<div class="col-12 mb-10 text-center">
						 <button type="submit" class="btn btn_verify w-100 mt-2 mb-2" name= "btn_verify">Submit</button>
						</div>
					</div> 
					</div> 
						
						
                    <?php echo form_close(); ?>
                </div>    
            </div> 
            
         </div>
      </div>
   </div>
</main>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

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



  $(document).ready(function(){   
        $('.flatpickr-basic').flatpickr({
        	dateFormat: "d-m-Y",
			allowInput: true, 
        	maxDate: new Date()
        });  
		$('.flatpickr-all').flatpickr({
        	dateFormat: "d-m-Y",
        });
        
         $(".allow_numeric").on("input", function(evt) {
          var self = $(this);
          self.val(self.val().replace(/[^\d].+/, ""));
          if ((evt.which < 48 || evt.which > 57)) 
          {
            evt.preventDefault();
          }
         });
         
         $(".allow_decimal").on("input", function(evt) {
          var self = $(this);
          self.val(self.val().replace(/[^0-9\.]/g, ''));
          if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) 
          {
            evt.preventDefault();
          }
         });
         
         
         $('.alphaonly').bind('keyup blur',function(){ 
           var node = $(this);
           node.val(node.val().replace(/[^a-zA-Z\s]/g,'') ); }
         );
         
     });

   	function ValidatePAN(txtPANCard) {
        var regex = /([A-Z]){5}([0-9]){4}([A-Z]){1}$/;
        if (regex.test(txtPANCard)) { return true; } 
		else { return false; }
     }
	 


    $('.add-ajax-redirect-image-form').submit(function(e) {
        e.preventDefault();  
          $(".loader").show(); 
          $('.btn_verify').attr("disabled", true)
          $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
          var url = $(this).attr('action');
    
   
         var pan=$('#pan_no').val();	 
        var aadhar_card=$('#aadhar_card').val();	
		
		if (!ValidatePAN(pan)) {
			   Swal.fire({
				title: "Alert!",
				text: 'Invalid PAN Card Number' ,
				icon: "error",
				customClass: {
					confirmButton: "btn btn-primary"
				},
				buttonsStyling: !1
			})
			$('.btn_verify').html('Submit');
			$('.btn_verify').attr("disabled", false);
			$(".loader").fadeOut("slow"); 
		}
		else{
		
         // Get form
        var form = $('.add-ajax-redirect-image-form')[0];

        // FormData object 
         var data = new FormData(form);
        
        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            dataType: 'json',
            data: data,     
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status == '200') { 
                  $(".loader").fadeOut("slow"); 
                  Swal.fire({
            		title: "Success!",
            		text: res.message,
            		icon: "success",
            		customClass: {
            			confirmButton: "btn btn-primary"
            		},
            		buttonsStyling: !1
            	  }).then(() => {
            	      window.location.href = res.url;
            	      
            	  });
                }
                else {  
				  $.each(res.errors, function(key, value){
                        $('[name="'+key+'"]').addClass('is-invalid'); //select parent twice to select div form-group class and add has-error class
                        $('[name="'+key+'"]').next().html(value); //select span help-block class set text error string
                        if(value == ""){
                            $('[name="'+key+'"]').removeClass('is-invalid');
                            $('[name="'+key+'"]').addClass('is-valid');
                        }
                    });   
                   Swal.fire({
            			title: "Error!",
						html: true,
            			html: res.message ,
            			icon: "error",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})
                    $('.btn_verify').html('Submit');
                    $('.btn_verify').attr("disabled", false);
                    $(".loader").fadeOut("slow"); 
                }
            }
        });
		}
        return false;
    }); 
	
	function isNumberKey(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46))
        return false;
    else {
        var len = $(element).val().length;
        var index = $(element).val().indexOf('.');
        if (index > 0 && charCode == 46) {
            return false;
        }
        if (index > 0) {
            var CharAfterdot = (len + 1) - index;
            if (CharAfterdot > 100) {
                return false;
            }
        }

    }
    return true;
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
   
   function get_p_city_(b) {
       var a = {
           state_id: b
       };
       $.ajax({
           type: "POST",
           url: base_url + "admin/get_cities",
           data: a,
           success: function(c) {
               $("#p_states_").children("option:not(:first)").remove();
               $("#p_states_").append(c);
           }
       })
   } 

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
