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
		background-color: #FFF !important;
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
.font-18{
    font-size: 17px!important;
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

          
				
			<form action="<?php echo base_url().'candidate_front/add_documentationxxxxxxx/'.$order_id;?>" class="add-ajax-redirect-image-form was-validated" method="post" accept-charset="utf-8">
			<div class="step_1">
              <div class="card mt-0 mb-10">
			     <div class="row">
					  <h2 class="title-detail text-center mb-1 mt-3">Exit Form</h2>
				   </div>		
				<hr>
				
                <div class="card-body"> 			
				
					<div class="row">
					   <div class="col-md-12">
						  <h5 class="mb-3 m-title"><b>Employee Exit Interview</b></h5> 
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
							 <label class="form-label">Email Id </label>
							 <input type="email" class="form-control" placeholder="Email Id" value="<?php echo $data['email'];?>" name="email" disabled>
							  <span class="invalid-feedback"></span>
						  </div>
						</div>	
					
						
						<div class="col-12 col-sm-12 mb-1">
						  <div class="form-group">
							<label class="form-label">What was your main reason for leaving Rajasthan Aushdhalaya? <i class="required">*</i></label>
							<textarea class="form-control"  rows="4" placeholder="Comments" name="leaving_reason"  required></textarea>
							<span class="invalid-feedback"></span>
						  </div>
						</div>	
						
						<div class="col-12 col-sm-12 mb-1">
						  <div class="mb-10">
							<label class="form-label">Could your leaving have been avoided? <i class="required">*</i></label><br>
							
							<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="is_leaving_avoided" id="is_la1" value="Yes">
							  <label class="form-check-label" for="is_la1">Yes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="is_leaving_avoided" id="is_la2" value="No">
							  <label class="form-check-label" for="is_la2">No</label>
							</div>
							</div>							
							<textarea class="form-control"  rows="4" placeholder="Comments" name="leaving_avoided_msg"></textarea>
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
						  <h5 class="mb-3 m-title"><b>The Company</b></h5> 
					   </div>
					</div>
					
				
					<div class="row">	
					
					  <div class="col-12 col-sm-12 mb-0">
					  <div class="form-group">	
					  <label class="form-label font-18">What did you think of the overall management of Rajasthan Aushdhalaya on the following points:</label>						
					  </div>
					 </div>

			
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">1. Followed policies and procedures?</label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_policies" id="mgmt_policies_1" value="Almost Always">
							  <label class="form-check-label" for="mgmt_policies_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_policies" id="mgmt_policies_2" value="Usually">
							  <label class="form-check-label" for="mgmt_policies_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_policies" id="mgmt_policies_3" value="Sometimes">
							  <label class="form-check-label" for="mgmt_policies_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_policies" id="mgmt_policies_4" value="Never">
							  <label class="form-check-label" for="mgmt_policies_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
						
					 
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">2. Demonstrated fair and equal treatment? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_treatment" id="mgmt_treatment_1" value="Almost Always">
							  <label class="form-check-label" for="mgmt_treatment_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_treatment" id="mgmt_treatment_2" value="Usually">
							  <label class="form-check-label" for="mgmt_treatment_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_treatment" id="mgmt_treatment_3" value="Sometimes">
							  <label class="form-check-label" for="mgmt_treatment_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_treatment" id="mgmt_treatment_4" value="Never">
							  <label class="form-check-label" for="mgmt_treatment_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
				
					 
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">3. Provided recognition on the job? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_recognition" id="mgmt_recognition_1" value="Almost Always">
							  <label class="form-check-label" for="mgmt_recognition_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_recognition" id="mgmt_recognition_2" value="Usually">
							  <label class="form-check-label" for="mgmt_recognition_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_recognition" id="mgmt_recognition_3" value="Sometimes">
							  <label class="form-check-label" for="mgmt_recognition_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_recognition" id="mgmt_recognition_4" value="Never">
							  <label class="form-check-label" for="mgmt_recognition_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					 
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">4. Developed Cooperation? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_cooperation" id="mgmt_cooperation_1" value="Almost Always">
							  <label class="form-check-label" for="mgmt_cooperation_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_cooperation" id="mgmt_cooperation_2" value="Usually">
							  <label class="form-check-label" for="mgmt_cooperation_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_cooperation" id="mgmt_cooperation_3" value="Sometimes">
							  <label class="form-check-label" for="mgmt_cooperation_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_cooperation" id="mgmt_cooperation_4" value="Never">
							  <label class="form-check-label" for="mgmt_cooperation_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">5. Resolved complaints, grievances & problems? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_complaints" id="mgmt_complaints_1" value="Almost Always">
							  <label class="form-check-label" for="mgmt_complaints_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_cooperation" id="mgmt_complaints_2" value="Usually">
							  <label class="form-check-label" for="mgmt_complaints_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_cooperation" id="mgmt_complaints_3" value="Sometimes">
							  <label class="form-check-label" for="mgmt_complaints_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="mgmt_cooperation" id="mgmt_complaints_4" value="Never">
							  <label class="form-check-label" for="mgmt_complaints_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>	


				   <div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<textarea class="form-control"  rows="4" placeholder="Comments" name="mgmt_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
				
								
					</div>
					
					<hr/>
					
					<div class="row">
							
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">How would you rate the communications within the company? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="coms_rate" id="coms_rate_1" value="Poor">
							  <label class="form-check-label" for="coms_rate_1">Poor</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="coms_rate" id="coms_rate_2" value="Fair">
							  <label class="form-check-label" for="coms_rate_2">Fair</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="coms_rate" id="coms_rate_3" value="Good">
							  <label class="form-check-label" for="coms_rate_3">Good</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="coms_rate" id="coms_rate_4" value="Very Good">
							  <label class="form-check-label" for="coms_rate_4">Very Good</label>
							</div>	
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="coms_rate" id="coms_rate_5" value="Outstanding">
							  <label class="form-check-label" for="coms_rate_5">Outstanding</label>
							</div>					
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="coms_rate_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
						
					 
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">What improvements do you think can be made to overall customer service either internally or externally? </label><br>
						
						<div class="ml-5">
							<textarea class="form-control"  rows="4" placeholder="Comments" name="improvement_msg"></textarea>
							<span class="invalid-feedback"></span>					
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">How do you feel about Rajasthan Aushadhalaya's employee benefits? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="emp_benefits" id="emp_benefits_1" value="Poor">
							  <label class="form-check-label" for="emp_benefits_1">Poor</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="emp_benefits" id="emp_benefits_2" value="Fair">
							  <label class="form-check-label" for="emp_benefits_2">Fair</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="emp_benefits" id="emp_benefits_3" value="Good">
							  <label class="form-check-label" for="emp_benefits_3">Good</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="emp_benefits" id="emp_benefits_4" value="Very Good">
							  <label class="form-check-label" for="emp_benefits_4">Very Good</label>
							</div>	
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="emp_benefits" id="emp_benefits_5" value="Outstanding">
							  <label class="form-check-label" for="emp_benefits_5">Outstanding</label>
							</div>					
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="emp_benefits_msg"></textarea>
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
						  <h5 class="mb-3 m-title"><b>Your Department</b></h5> 
					   </div>
					</div>
					
				
					<div class="row">	
					
					  <div class="col-12 col-sm-12 mb-0">
					  <div class="form-group">	
					  <label class="form-label font-18">What did you think of the <b>department's management</b> on the following points?</label>						
					  </div>
					 </div>

			
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">1. Followed policies and procedures?</label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_policies" id="dept_policies_1" value="Almost Always">
							  <label class="form-check-label" for="dept_policies_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_policies" id="dept_policies_2" value="Usually">
							  <label class="form-check-label" for="dept_policies_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_policies" id="dept_policies_3" value="Sometimes">
							  <label class="form-check-label" for="dept_policies_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_policies" id="dept_policies_4" value="Never">
							  <label class="form-check-label" for="dept_policies_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
						
					 
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">2. Demonstrated fair and equal treatment? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_treatment" id="dept_treatment_1" value="Almost Always">
							  <label class="form-check-label" for="dept_treatment_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_treatment" id="dept_treatment_2" value="Usually">
							  <label class="form-check-label" for="dept_treatment_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_treatment" id="dept_treatment_3" value="Sometimes">
							  <label class="form-check-label" for="dept_treatment_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_treatment" id="dept_treatment_4" value="Never">
							  <label class="form-check-label" for="dept_treatment_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
				
					 
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">3. Provided recognition on the job? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_recognition" id="dept_recognition_1" value="Almost Always">
							  <label class="form-check-label" for="dept_recognition_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_recognition" id="dept_recognition_2" value="Usually">
							  <label class="form-check-label" for="dept_recognition_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_recognition" id="dept_recognition_3" value="Sometimes">
							  <label class="form-check-label" for="dept_recognition_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_recognition" id="dept_recognition_4" value="Never">
							  <label class="form-check-label" for="dept_recognition_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					 
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">4. Developed/encouraged cooperation ? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_cooperation" id="dept_cooperation_1" value="Almost Always">
							  <label class="form-check-label" for="dept_cooperation_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_cooperation" id="dept_cooperation_2" value="Usually">
							  <label class="form-check-label" for="dept_cooperation_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_cooperation" id="dept_cooperation_3" value="Sometimes">
							  <label class="form-check-label" for="dept_cooperation_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_cooperation" id="dept_cooperation_4" value="Never">
							  <label class="form-check-label" for="dept_cooperation_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">5. Resolved complaints, grievances & problems? </label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_complaints" id="dept_complaints_1" value="Almost Always">
							  <label class="form-check-label" for="dept_complaints_1">Almost Always</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_cooperation" id="dept_complaints_2" value="Usually">
							  <label class="form-check-label" for="dept_complaints_2">Usually</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_cooperation" id="dept_complaints_3" value="Sometimes">
							  <label class="form-check-label" for="dept_complaints_3">Sometimes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_cooperation" id="dept_complaints_4" value="Never">
							  <label class="form-check-label" for="dept_complaints_4">Never</label>
							</div>						
						</div>	
						<span class="invalid-feedback"></span>
					  </div>
					</div>	


				   <div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<textarea class="form-control"  rows="4" placeholder="Comments" name="dept_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
				
								
					</div>
					
					<hr/>
					
					<div class="row">
							
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">How would you rate the communications within your department? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_coms" id="dept_coms_1" value="Poor">
							  <label class="form-check-label" for="dept_coms_1">Poor</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_coms" id="dept_coms_2" value="Fair">
							  <label class="form-check-label" for="dept_coms_2">Fair</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_coms" id="dept_coms_3" value="Good">
							  <label class="form-check-label" for="dept_coms_3">Good</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_coms" id="dept_coms_4" value="Very Good">
							  <label class="form-check-label" for="dept_coms_4">Very Good</label>
							</div>	
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_coms" id="dept_coms_5" value="Outstanding">
							  <label class="form-check-label" for="dept_coms_5">Outstanding</label>
							</div>					
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="dept_coms_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">How would you rate the department training that you received? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_training" id="dept_training_1" value="Poor">
							  <label class="form-check-label" for="dept_training_1">Poor</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_training" id="dept_training_2" value="Fair">
							  <label class="form-check-label" for="dept_training_2">Fair</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_training" id="dept_training_3" value="Good">
							  <label class="form-check-label" for="dept_training_3">Good</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_training" id="dept_training_4" value="Very Good">
							  <label class="form-check-label" for="dept_training_4">Very Good</label>
							</div>	
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="dept_training" id="dept_training_5" value="Outstanding">
							  <label class="form-check-label" for="dept_training_5">Outstanding</label>
							</div>					
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="dept_training_msg"></textarea>
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
						  <h5 class="mb-3 m-title"><b>Your Job</b></h5> 
					   </div>
					</div>
				
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">What did you especially like about your job at Rajasthan Aushdhalaya? <i class="required">*</i></label><br>											
						<textarea class="form-control"  rows="4" placeholder="Comments" name="like_job_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">What did you dislike about your job at Rajasthan Aushdhalaya? <i class="required">*</i></label><br>											
						<textarea class="form-control"  rows="4" placeholder="Comments" name="dislike_job_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>

					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">What would you have changed about your job? <i class="required">*</i></label><br>											
						<textarea class="form-control"  rows="4" placeholder="Comments" name="changed_job_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>


					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">Was your workload usually? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="workload" id="workload_1" value="Heavy">
							  <label class="form-check-label" for="workload_1">Heavy</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="workload" id="workload_2" value="Challenging">
							  <label class="form-check-label" for="workload_2">Challenging</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="workload" id="workload_3" value="Normal">
							  <label class="form-check-label" for="workload_3">Normal</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="workload" id="workload_4" value="Easy Going">
							  <label class="form-check-label" for="workload_4">Easy Going</label>
							</div>	
										
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="workload_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
					
				    <div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">How did you feel about the training you received? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="training_rec" id="training_rec_1" value="Poor">
							  <label class="form-check-label" for="training_rec_1">Poor</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="training_rec" id="training_rec_2" value="Fair">
							  <label class="form-check-label" for="training_rec_2">Fair</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="training_rec" id="training_rec_3" value="Good">
							  <label class="form-check-label" for="training_rec_3">Good</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="training_rec" id="training_rec_4" value="Very Good">
							  <label class="form-check-label" for="training_rec_4">Very Good</label>
							</div>	
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="training_rec" id="training_rec_5" value="Outstanding">
							  <label class="form-check-label" for="training_rec_5">Outstanding</label>
							</div>					
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="training_rec_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>					
					
				    <div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">Did you receive enough training to do your job effectively? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="enough_training" id="enough_training_1" value="Yes">
							  <label class="form-check-label" for="enough_training_1">Yes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="enough_training" id="enough_training_2" value="No">
							  <label class="form-check-label" for="enough_training_2">No</label>
							</div>												
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="enough_training_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
						
						
				   <div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">Did Rajasthan Aushdhalaya help you to fulfill your career goals? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="fulfill_career" id="fulfill_career_1" value="Yes">
							  <label class="form-check-label" for="fulfill_career_1">Yes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="fulfill_career" id="fulfill_career_2" value="No">
							  <label class="form-check-label" for="fulfill_career_2">No</label>
							</div>												
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="fulfill_career_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">How did you feel about the supervision you received? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="supervision" id="supervision_1" value="Poor">
							  <label class="form-check-label" for="supervision_1">Poor</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="supervision" id="supervision_2" value="Fair">
							  <label class="form-check-label" for="supervision_2">Fair</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="supervision" id="supervision_3" value="Good">
							  <label class="form-check-label" for="supervision_3">Good</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="supervision" id="supervision_4" value="Very Good">
							  <label class="form-check-label" for="supervision_4">Very Good</label>
							</div>	
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="supervision" id="supervision_5" value="Outstanding">
							  <label class="form-check-label" for="supervision_5">Outstanding</label>
							</div>					
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="supervision_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>
					
						
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">Were you satisfied with the merit review process? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="merit_review" id="merit_review_1" value="Yes">
							  <label class="form-check-label" for="merit_review_1">Yes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="merit_review" id="merit_review_2" value="No">
							  <label class="form-check-label" for="merit_review_2">No</label>
							</div>												
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="merit_review_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>	

					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">Did you receive sufficient feedback about your Performance between merit reviews? <i class="required">*</i></label><br>
						
						<div class="ml-5">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="merit_review_fb" id="merit_review_fb_1" value="Yes">
							  <label class="form-check-label" for="merit_review_fb_1">Yes</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="merit_review_fb" id="merit_review_fb_2" value="No">
							  <label class="form-check-label" for="merit_review_fb_2">No</label>
							</div>												
						</div>							
						<textarea class="form-control"  rows="4" placeholder="Comments" name="merit_review_fb_msg"></textarea>
						<span class="invalid-feedback"></span>
					  </div>
					</div>		
					
					<div class="col-12 col-sm-12 mb-1">
					  <div class="mb-10">
						<label class="form-label">Do you have any additional comments regarding Rajasthan Aushdhalaya or your reasons for leaving? <i class="required">*</i></label><br>
										
						<textarea class="form-control"  rows="4" placeholder="Comments" name="additional_msg"></textarea>
						<span class="invalid-feedback"></span>
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
