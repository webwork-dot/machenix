<?php
 $system_title ="Machenix CRM";
 $logged_in_user_role = strtolower($this->session->userdata('super_role'));
?>
<!DOCTYPE html>

<html class="loading <?= $phpvariable;?>" lang="en" data-textdirection="ltr">
   <head>
	  <!-- all the meta tags -->
      <?php include 'metas.php'; ?>
      <title><?php echo get_phrase($page_title); ?> | <?php echo $system_title; ?></title>
      <!-- all the css files -->
      <?php include 'includes_top.php'; ?>
	  <style>
	  .mtop-2 {margin-top: 2rem!important;}
	  .table-link {font-weight: 500; color: #2496f7 !important;}
	  .menu-collapsed .navbar-brand .sm-image{
	      display:none!important;
	  }
	  .menu-collapsed .main-menu{
	      display:none!important;
	  }
	  .navbar-brand .sm-image{
	      display:none !important;
	  }
	  .menu-collapsed .navbar-brand .bg-image{
	      display:none !important;
	  }
	  .navbar-brand .bg-image{
	      display:block !important;
	  }
	  html .menu-collapsed .content.app-content {
            padding: calc(0.5rem + 4.45rem + 1.3rem) 2rem 0;
        }
	  </style>
   </head>
   
   <?php if($page_name == 'pending_orders_verification_details' || $page_name == 'hold_orders_details' || $page_name == 'revised_order_details' || $page_name == 'pending_orders_details' || $page_name == 'samman_samaroh_details' || $page_name == 'patient_orders_details' || $page_name == 'prepaid_orders_details' || $page_name == 'patient_orders_verification_details' || $page_name == 'pending_gift_orders_details'){;?>
        <body class="vertical-layout vertical-menu-modern navbar-floating footer-static menu-collapsed" data-open="click" data-menu="vertical-menu-modern" data-col="">
   <?php }else{ ?>
        <body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="">
   <?php } ?>
   

<div class="preloader-message hidden">
   <div class="multichannel-preloader-box text-center pt-lg pb-lg pr">
      <div class="message text-left text-white ng-binding"> Please Wait.</div>
   </div>
</div>


  <div class="loader preloader-message">   
	<div class="multichannel-preloader-box text-center pt-lg pb-lg pr">
	  <div class="message text-left text-white ng-binding"> Please Wait.</div>
   </div>
  </div>
  
  <div class="invoice-loader preloader-invoice" style="display: none;">   
	<div class="multichannel-preloader-box text-center pt-lg pb-lg pr">
	  <div class="message text-left ng-binding"> Generating Invoice..<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle"></div>
   </div>
  </div>

	   
	   
      <!-- HEADER -->
    <?php include 'header.php'; ?>

      <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
         <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
               <li class="nav-item me-auto">
                  <a class="navbar-brand" href="<?php echo base_url();?>inventory/dashboard">
                  <img src="<?php echo base_url();?>app-assets/images/logo/logo.png" class="bg-image">
                  <img src="<?php echo base_url();?>app-assets/images/logo/logo.png" class="sm-image">
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
                
               <!-- BEGIN PlACE PAGE CONTENT HERE -->
			
				
				<?php
				if($page_name == 'doctor_details_ledger' || $page_name == 'doctor_details_timeline' || $page_name == 'doctor_details_calls_timeline' || $page_name == 'doctor_details_field_force_timeline'){
				  include 'staff/'.$page_name.'.php';	
				}	
				elseif($page_name == 'patient_details_ledger'){
				  include 'patient_coordinator/'.$page_name.'.php';	
				}
				else{
					if(!isset($page_folder) && $page_folder==''){
						include $logged_in_user_role.'/'.$page_name.'.php';
					}
					else{
						include $logged_in_user_role.'/'.$page_folder.'/'.$page_name.'.php';  
					}
				}
				?>
				
			   <!-- END PLACE PAGE CONTENT HERE -->
			   
			   
			   
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
</script>