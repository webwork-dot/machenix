<div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
       <li class="nav-item <?php if ($page_name == 'dashboard')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('production_head/dashboard'); ?>">
        	<i data-feather="home"></i>
        	<span class="menu-title text-truncate fw-bolder" data-i18n="Dashboard">Dashboard</span>
            </a>
       </li>  
     
	 
     
	  <li class="nav-item <?php if($page_name == 'products' || $page_name == 'products_add' || $page_name == 'products_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('production_head/products'); ?>">
                <i class='fa fa-product-hunt'></i>
               <span class="menu-title text-truncate fw-bolder">Products</span>
           </a>
       </li>

    </ul>
</div>





	  
	  
	
      