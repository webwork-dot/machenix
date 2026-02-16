<div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
       <li class="nav-item <?php if ($page_name == 'dashboard')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('quality-control/dashboard'); ?>">
        	<i data-feather="home"></i>
        	<span class="menu-title text-truncate fw-bolder" data-i18n="Dashboard">Dashboard</span>
            </a>
       </li> 
       <li class="nav-item <?php if ($page_name == 'raw_material' || $page_name == 'raw_material_done')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('quality-control/raw-material'); ?>">
        	<i data-feather="home"></i>
        	<span class="menu-title text-truncate fw-bolder" data-i18n="Dashboard">Raw Material</span>
            </a>
       </li> 
    </ul>
</div>
	  
	  
	
      