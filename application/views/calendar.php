<html>
<head>
<link rel="stylesheet" href="<?= base_url();?>assets/mpdf/calendar.css">
</head>
<body class="invoice">
 <div class="calendar-page">  
  <img src="<?= base_url();?>uploads/sample-calendar/cover-page.jpg" class="base-image"  style="width:99.99%"> 
 </div>	
 <?php 
 $months_arr = array("january", "february", "March", "april", "may", "june", "july", "august", "september", "october", "november", "december"); 
 foreach($doctors as $pos => $dr_photos){
 $month = strtolower($months_arr[$pos]);
 ?>
  <div class="calendar-page <?= $month;?>">	
   <img src="<?= base_url();?>uploads/sample-calendar/<?= $month;?>-front.jpg" class="base-image" id="<?= $month;?>-img"> 
	<?php foreach($dr_photos as $key => $dr){?>
		<img src="<?= base_url().$dr['photo_data'];?>" class="overlay-image photo<?= $key+1;?>-img">
		<div class="photo<?= $key+1;?>-city cal-city"><?= $dr['city_name'];?></div>	
		<div class="photo<?= $key+1;?>-text cal-txt"><?= $dr['print_name'];?></div>
	<?php } ?>
	<img src="<?= base_url();?>uploads/sample-calendar/<?= $month;?>-back.jpg" class="base-image"> 
   </div>
  <?php } ?>
 </body>
</html>