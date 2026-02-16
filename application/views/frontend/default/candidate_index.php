<!DOCTYPE html>
<html lang="en">
<head>

	<title><?php echo ucwords($page_title); ?></title>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">

	<meta name="description" content="<?php echo $meta_description; ?>" />
	<meta name="keyword" content="<?php echo $meta_keyword; ?>" />
	<meta name="author" content="Rajasthan Aushadhalaya | Manufacture of Ayurvedic medicine in Mumbai" />
   <link rel="icon" href="<?php echo rapl_url(); ?>assets/img/favicon.png">
   <link rel="stylesheet" href="<?php echo rapl_url(); ?>assets/css/plugins/animate.min.css">
   <link rel="stylesheet" href="<?php echo rapl_url();?>assets/css/jquery-ui.css">
  
   <link rel="stylesheet" href="<?php echo rapl_url(); ?>assets/css/c-main.css">
   <link rel="stylesheet" href="<?php echo rapl_url(); ?>assets/css/toastr.css"/>
   <link rel="stylesheet" href="<?php echo rapl_url(); ?>assets/css/style.min.css"/>
   <script src="<?php echo rapl_url(); ?>assets/js/toastr.js"></script>
   <script src="<?php echo rapl_url(); ?>assets/js/vendor/jquery-3.6.0.min.js"></script>
   <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
   <style>
       .loader {
        display: none;
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 999999999;
        background: url('<?= base_url();?>assets/image/ajax-spinner.gif') 50% 50% no-repeat rgba(255,255,255,0.4);
        background-size: 100px;
    }
   </style>      
</head>
   <body class="single-product">
    <div class="loader"></div>
	<?php
	include $page_name.'.php';
	include 'footer.php';
	include 'modal.php';
	?>
    </div>
	<script>var base_url="<?php echo base_url();?>";</script>

   <script src="<?php echo rapl_url(); ?>assets/js/vendor/bootstrap.bundle.min.js"></script>
   <script src="<?php echo rapl_url(); ?>assets/js/plugins/wow.js"></script>
   <script src="<?php echo rapl_url(); ?>assets/js/plugins/images-loaded.js"></script>
   <script src="<?php echo rapl_url(); ?>assets/js/plugins/scrollup.js"></script>
   
    
    <?php	include 'includes_bottom.php';	?>
    
    <!-- SHOW TOASTR NOTIFIVATION -->
    <?php if ($this->session->flashdata('flash_message') != ""):?>
    
    <script type="text/javascript">
    	toastr.success('<?php echo $this->session->flashdata("flash_message");?>');
    </script>
    
    <?php endif;?>
    
    <?php if ($this->session->flashdata('error_message') != ""):?>
    
    <script type="text/javascript">
    	toastr.error('<?php echo $this->session->flashdata("error_message");?>');
    </script>
    <?php endif;?>


</body>
</html>
