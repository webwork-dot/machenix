<?php
 $system_title ="RAPL CRM";
 $logged_in_user_role = strtolower($this->session->userdata('super_role'));
?>
<!DOCTYPE html>

<html class="loading md-view" lang="en" data-textdirection="ltr">
   <head>
	  <!-- all the meta tags -->
      <?php include 'metas.php'; ?>
      <title><?php echo get_phrase($page_title); ?> | <?php echo $system_title; ?></title>
      <!-- all the css files -->
      <?php include 'includes_top.php'; ?>
	  <style>
	  .mtop-2 {margin-top: 2rem!important;}
	  .table-link {font-weight: 500; color: #2496f7 !important;}	  
	  </style>
	  
	  <style>
   .sys-pass .progress {
        height: 5px;
		margin-bottom: 10px;
    }

   .sys-pass .control-label {
        text-align: left !important;
        padding-bottom: 7px;
    }

    .sys-pass select.form-control:focus {
        border-color: #e9ab66;
        box-shadow: none;
    }

    .sys-pass .block-help {
        font-weight: 300;
    }

    .sys-pass .terms {
        text-decoration: underline;
    }

    .sys-pass .modal {
        text-align: center;
        padding: 0!important;
    }

   .sys-pass .modal:before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -4px;
    }

    .sys-pass .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
    }

    .sys-pass .divider {
        position: absolute;
        height: 2px;
        border: 1px solid #eee;
        width: 100%;
        top: 10px;
        z-index: -5;
    }

    .sys-pass .ex-account {
        position: relative;
    }

    .sys-pass .ex-account p {
        background-color: rgba(255, 255, 255, 0.41);
    }

    .sys-pass select:hover {
        color: #444645;
        background: #ddd;
    }

    .sys-pass .fa-file-text {
        color: #edda39;
    }

    .sys-pass .mar-top-bot-50 {
        margin-top: 50px;
        margin-bottom: 50px;
    }

	
	.progress-bar-success {
		background-color: #5cb85c;
	}
	.progress-bar-warning {
		background-color: #f0ad4e;
	}
	.progress-bar-danger {
		background-color: #d9534f;
	}
	  </style>
   </head>
      <!-- HEADER -->
 

<?php if ($this->session->userdata('super_role_id')==13) {?>  
<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/m-custom.css')?>">
<body class="vertical-layout vertical-menu-modern navbar-floating footer-static flash-monitor">
    <div class="loader"></div>
      <!-- HEADER -->
    <?php include 'md_header.php'; ?>	  
<?php } else{?>  
   <body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="">
    <div class="loader"></div>
    <?php include 'header.php'; ?>
      <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
         <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
               <li class="nav-item me-auto">
                  <a class="navbar-brand" href="<?php echo base_url();?>staff/dashboard?type=today">
                  <img src="<?php echo base_url();?>app-assets/images/logo/logo.png">
                  </a>
               </li>
            </ul>
         </div>
         <div class="shadow-bottom"></div>
         <div class="main-menu-content">
      
		 <!-- SIDEBAR -->
		 <?php include $logged_in_user_role.'/'.'navigation.php' ?>
         </div>
      </div>
<?php } ?>
	  

      <div class="app-content content ">
         <div class="content-overlay"></div>
         <div class="header-navbar-shadow"></div>
         <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
      
          
<?php if ($this->session->flashdata('info_message') != ""):?>
   <div id="alert" class="alert alert-primary alert-dismissible fade show error-shake" role="alert">
      <div class="alert-body"><?php echo $this->session->flashdata('info_message'); ?></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;?>

<?php if ($this->session->flashdata('error_message') != ""):?>
   <div id="alert" class="alert alert-danger alert-dismissible fade show error-shake" role="alert">
      <div class="alert-body"><?php echo $this->session->flashdata('error_message'); ?></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;?>

<?php if ($this->session->flashdata('flash_message') != ""):?>
   <div id="alert" class="alert alert-success alert-dismissible fade show error-shake" role="alert">
      <div class="alert-body"><?php echo $this->session->flashdata('flash_message'); ?></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;?>      
                

<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0 sys-pass">

        <!-- form -->
        <form class="flash-ajax-redirect-form" action="<?php echo site_url('admin/system_password/change_password'); ?>" onsubmit="return checkForm(this);"  enctype="multipart/form-data" method="post">
          <div class="row">   
            <div class="col-12 col-sm-6 mb-1">
                <div class="mb-1">
                  <label class="form-label" for="current_password">Old Password <span class="required">*</span></label>
                  <div class="input-group input-group-merge form-password-toggle">
                    <span class="input-group-text"><i data-feather="lock"></i></span>
                    <input type="password" id="current_password" class="form-control" name="current_password" placeholder="Old Password" required>
                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                  </div>  
				  <span class="invalid-feedback"></span>
              </div>
             </div>
          </div>     
          
 
        
		   <div class="row">   
            <div class="col-12 col-sm-6 mb-1">
		  <!-- Password input-->
	    	<div class="form-group">
			<label class="col-md-12 form-label" for="passwordinput">Password <span id="popover-password-top" class="hide pull-right block-help"><i class="fa fa-info-circle text-danger" aria-hidden="true"></i> Enter a strong password</span></label>
				
			<div class="col-md-12">
				
			      <div class="input-group input-group-merge form-password-toggle">
                    <span class="input-group-text"><i data-feather="lock"></i></span>
                    <input type="password" id="password" class="form-control" name="password"  placeholder="New Password" required>
                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                  </div> 
                  </div> 

				  <span class="invalid-feedback"></span>
				
			<div class="col-md-12">
				
				<div id="popover-password">
					<p><small>Password Strength: <span id="result"> </span></small></p>
					<div class="progress">
						<div id="password-strength" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
						</div>
					</div>
					<ul class="list-unstyled">
						<li class=""><span class="low-upper-case"><i class="fa fa-file-text" aria-hidden="true"></i></span>&nbsp; 1 lowercase &amp; 1 uppercase</li>
						<li class=""><span class="one-number"><i class="fa fa-file-text" aria-hidden="true"></i></span> &nbsp;1 number (0-9)</li>
						<li class=""><span class="one-special-char"><i class="fa fa-file-text" aria-hidden="true"></i></span> &nbsp;1 Special Character (!@#$%^&*).</li>
						<li class=""><span class="eight-character"><i class="fa fa-file-text" aria-hidden="true"></i></span>&nbsp; Atleast 8 Character</li>
					</ul>
				</div>
			</div>
		   </div>
		  </div>
		</div>
		
	      <div class="row">   
            <div class="col-12 col-sm-6 mb-1">
                <div class="mb-1">
                  <label class="form-label" for="confirm_password">Confirm Password <span class="required">* </span> <span id="popover-cpassword" class="hide pull-right block-help"><i class="fa fa-info-circle text-danger" aria-hidden="true"></i> Password don't match</span></label>
                  <div class="input-group input-group-merge form-password-toggle">
                    <span class="input-group-text"><i data-feather="lock"></i></span>
                    <input type="password" id="confirm_password" class="form-control" name="confirm_password"  placeholder="Confirm Password" required>
                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                  </div> 
				  <span class="invalid-feedback"></span>
              </div>
             </div>
          </div>

           <div class="row">  
            <div class="col-12">
              <button type="submit" id="sign-up" class="btn btn-primary mt-1 me-1 btnf check">Change</button>
            </div>
          </div>
        </form>
        <!--/ form -->
      </div>
    </div>
    </div>
</div>

	   
			   
        </div>
       </div>
      </div>
  
      <div class="sidenav-overlay"></div>
      <div class="drag-target"></div>
      <!-- all the js files -->
    <?php include 'modal.php'; ?>
    <?php include 'includes_bottom.php'; ?>

    <?php include 'common_scripts.php'; ?>
   </body>
</html>

<script> 
var skin_layout=localStorage.getItem('light-layout-current-skin');
 $("html").addClass(skin_layout);
 if(skin_layout=='dark-layout'){
    $(".nav-link-style").find(".ficon").replaceWith(feather.icons.sun.toSvg({class: "ficon"	}));  
 }
 else{
    $(".nav-link-style").find(".ficon").replaceWith(feather.icons.moon.toSvg({class: "ficon"})); 
     
 }
 window.onbeforeunload = function () {
    window.scrollTo(0, 0);
}


$(document).ready(function() {  
        $('#password').keyup(function() {
            var password = $('#password').val();
            if (checkStrength(password) == false) {
                $('#sign-up').attr('disabled', true);
            }	
        });
        $('#confirm_password').keyup(function() {
            if ($('#password').val() !== $('#confirm_password').val()) {
                $('#popover-cpassword').removeClass('hide');
                $('#sign-up').attr('disabled', true);
				var password = $('#password').val();
				if (checkStrength(password) == false) {
					$('#sign-up').attr('disabled', true);
				}
				console.log('1-'+password);
            } else {
				var password = $('#password').val();
				if (checkStrength(password) == false) {
					$('#sign-up').attr('disabled', true);
				}
				else{
					$('#sign-up').attr('disabled', false);					
				}
                $('#popover-cpassword').addClass('hide');
				console.log('2-'+password);
            }
        });


        function checkStrength(password) {
            var strength = 0;
            //If password contains both lower and uppercase characters, increase strength value.
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
                strength += 1;
                $('.low-upper-case').addClass('text-success');
                $('.low-upper-case i').removeClass('fa-file-text').addClass('fa-check');
                $('#popover-password-top').addClass('hide');

            } else { 
  			 strength -= 1;
                $('.low-upper-case').removeClass('text-success');
                $('.low-upper-case i').addClass('fa-file-text').removeClass('fa-check');
                $('#popover-password-top').removeClass('hide');
            }

            //If it has numbers and characters, increase strength value.
            if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) {
                strength += 1;
                $('.one-number').addClass('text-success');
                $('.one-number i').removeClass('fa-file-text').addClass('fa-check');
                $('#popover-password-top').addClass('hide');

            } else {
  			 strength -= 1;
                $('.one-number').removeClass('text-success');
                $('.one-number i').addClass('fa-file-text').removeClass('fa-check');
                $('#popover-password-top').removeClass('hide');
            }

            //If it has one special character, increase strength value.
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
                strength += 1;
                $('.one-special-char').addClass('text-success');
                $('.one-special-char i').removeClass('fa-file-text').addClass('fa-check');
                $('#popover-password-top').addClass('hide');

            } else {
                $('.one-special-char').removeClass('text-success');
                $('.one-special-char i').addClass('fa-file-text').removeClass('fa-check');
                $('#popover-password-top').removeClass('hide');
            }

            if (password.length > 7) {
                strength += 1;
                $('.eight-character').addClass('text-success');
                $('.eight-character i').removeClass('fa-file-text').addClass('fa-check');
                $('#popover-password-top').addClass('hide');

            } else {
                $('.eight-character').removeClass('text-success');
                $('.eight-character i').addClass('fa-file-text').removeClass('fa-check');
                $('#popover-password-top').removeClass('hide');
            }




            // If value is less than 2

            if (strength < 2) {
                $('#result').removeClass()
                $('#password-strength').addClass('progress-bar-danger');

                $('#result').addClass('text-danger').text('Very Week');
                $('#password-strength').css('width', '10%'); 
				return false
            } else if (strength == 2 && strength < 4) {
                $('#result').addClass('good');
                $('#password-strength').removeClass('progress-bar-danger');
                $('#password-strength').addClass('progress-bar-warning');
                $('#result').addClass('text-warning').text('Week')
                $('#password-strength').css('width', '60%');
                return false
            } else if (strength == 4) {
                $('#result').removeClass()
                $('#result').addClass('strong');
                $('#password-strength').removeClass('progress-bar-warning');
                $('#password-strength').removeClass('progress-bar-danger');
                $('#password-strength').addClass('progress-bar-success');
                $('#result').addClass('text-success').text('Strength');
                $('#password-strength').css('width', '100%');

                return  true
            }

        }

    });
</script>