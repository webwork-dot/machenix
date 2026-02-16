<div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
       <li class="nav-item <?php if ($page_name == 'dashboard')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('admin/dashboard'); ?>">
        	<i data-feather="home"></i>
        	<span class="menu-title text-truncate fw-bolder" data-i18n="Dashboard">Dashboard</span>
            </a>
       </li>  
     
	  <li class="nav-item <?php if($page_name == 'staff' || $page_name == 'staff_add' || $page_name == 'staff_edit') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('admin/staff'); ?>">
               <i data-feather="user"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Manage Staff</span>
           </a>
       </li>
    </ul>
</div>





	  
	  
	
      