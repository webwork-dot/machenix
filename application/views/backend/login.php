<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
   <head>
      <?php include 'metas.php'; ?>
      <title><?php echo $page_title; ?></title>
      <?php include 'includes_top.php'; ?> 
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css" integrity="sha512-/VYneElp5u4puMaIp/4ibGxlTd2MV3kuUIroR3NSQjS2h9XKQNebRQiyyoQKeiGE9mRdjSCIZf9pb7AVJ8DhCg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   </head>

   <style>
      	.login-page{
		background-image: url(<?= base_url();?>assets/global/login_bgs.png);
		background-repeat: no-repeat;
		background-size: cover;
		height: 100%;
		display: flex;
		-webkit-box-pack: end;
		justify-content: flex-end;
		-webkit-box-align: center;
		align-items: center;
	}

   .login-page .main-bg {
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.login-page .main-bg .card-body1 {
		display: flex;
		flex-direction: column;
		flex-wrap: nowrap;
		justify-content: center;
	}
	.content-text {
		margin: 0 0 0 0;
		padding: 0 80px;
		position: relative;
		height: 400px;
		align-content: flex-end;
		color: white;
		font-size: 18px !important;
		line-height: 2.375rem;
		letter-spacing: 0px;
		width: 100%;
		padding-left: 0;
	}
	.content-text h3 {
		color: white;
		font-size: 26px !important;
		font-weight: 600;
		line-height: 2.375rem;
		letter-spacing: 0px;
	}
	.secondry-color {
		color: #F0E019 !important;
	}
	
	.auth-wrapper.auth-basic .auth-inner {
		max-width: 550px;
		width: 550px;
		height: 450px;
	}
	
	@media only screen and (max-width:767px) {
	   .container {
			max-width: 100%;
		}
		
		.login-page .auth-wrapper.auth-basic {
			display: flex;
			flex-direction: column;
			padding: 0 !important;
		}
		.login-page .auth-wrapper .auth-inner {
			order: 1; 
		}

		.login-page  .auth-wrapper .content-text {
			order: 2; 
			height: auto;
		}
	}
   </style>
   
   <div class="app-content content login-page">
      <div class="content-overlay"></div>
      <div class="header-navbar-shadow"></div>
      <div class="container">
      
      <div class="content-wrapper">
         <div class="content-header row">
         </div>
         <div class="content-body">
            <div class="auth-wrapper auth-basic px-2">
                
                <div class="content-text">
                    <h3>Streamline Your <span class="secondry-color">Sales </span>, Supercharge Your <span class="secondry-color">Growth</span></h3>
                    <div class="row">
                        <div class="col-md-4 text-white">
                            <i class="ri-user-follow-line"></i>
                            Insights
                        </div>
                        <div class="col-md-4 text-white">
                            <i class="ri-service-fill"></i>
                            Easy To Use
                        </div>
                        <div class="col-md-4 text-white">
                            <i class="ri-dashboard-line"></i>
                            Powerful Dashboard
                        </div>
                    </div>
                </div>
                
               <div class="auth-inner my-2">
                  <div class="card mb-0 main-bg">
                     <div class="card-body1" >
                        <a href="#" class="brand-logo">
                            <img src="<?php echo base_url();?>app-assets/images/logo/logo.png" height="120">
                            <h2 class="" style="font-size: 25px;font-weight: 600;color: #323232;margin-top: 20px;"></h2>
                        </a>
                        <h4 class="card-title text-center mb-1" style="font-size:22px;">Login</h4>
                        <p class="card-text text-center mb-2">Please sign-in to your account</p>
                        <body class="vertical-layout vertical-menu-modern blank-page navbar-floating footer-static  " data-open="click" data-menu="vertical-menu-modern" data-col="blank-page">
                           <?php if ($this->session->flashdata('info_message') != ""):?>
                           <div id="alert" class="alert alert-primary alert-dismissible fade show" role="alert">
                              <div class="alert-body"><?php echo $this->session->flashdata('info_message'); ?></div>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                           </div>
                           <?php endif;?>
                           <?php if ($this->session->flashdata('error_message') != ""):?>
                           <div id="alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                              <div class="alert-body"><?php echo $this->session->flashdata('error_message'); ?></div>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                           </div>
                           <?php endif;?>
                           <?php if ($this->session->flashdata('flash_message') != ""):?>
                           <div id="alert" class="alert alert-success alert-dismissible fade show" role="alert">
                              <div class="alert-body"><?php echo $this->session->flashdata('flash_message'); ?></div>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                           </div>
                           <?php endif;?>
                           <form class="auth-login-form mt-2" action="<?php echo site_url('login/validate_login/admin'); ?>" method="POST">
                              <div class="mb-1">
                                 <label class="form-label" for="email-id-icon">Username</label>
                                 <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i data-feather="mail"></i></span>
                                    <input type="text" id="email-id-icon" class="form-control" name="email" placeholder="Enter Email or Mobile Number">
                                 </div>
                              </div>
                              <div class="mb-1" style="margin-bottom:.75rem!important">
                                 <label class="form-label" for="password-icon">Password</label>
                                 <div class="input-group input-group-merge form-password-toggle">
                                    <span class="input-group-text"><i data-feather="lock"></i></span>
                                    <input type="password" id="password-icon" class="form-control" name="password" placeholder="Password" aria-describedby="login-password">
                                    <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                 </div>
                              </div>
                              <button class="btn btn-primary w-100">Sign In</button>
                           </form>
                     </div>
                  </div>
               </div>
            </div>
            
         </div>
         </div>
      </div>
   </div>
   <?php include 'includes_bottom.php'; ?>
   </body>
</html>
                            