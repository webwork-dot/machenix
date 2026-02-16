<?php
 $system_title ="RHIPL CRM";
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
	  </style>
   </head>
   <body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="">

    <div class="loader"></div>
      <!-- HEADER -->
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
                
    			<?php include 'common/'.$page_name.'.php';?>
				
			   
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