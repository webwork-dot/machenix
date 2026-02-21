<?php 
    $user_id = $this->session->userdata('super_user_id');
    if($user_id != 4){
        $new_where = "id='$user_id'";
        $usr_det = $this->common_model->getRowById('sys_users','staff_access',$new_where);
        $access_id = $usr_det['staff_access'];
        $access_array = $this->common_model->getBulkNameIds('access','access_id',$access_id);
        $access_array = explode(',',$access_array);
    } else {
        $access_array = array();
    }
    
?>

<div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
        <li class="nav-item <?php if ($page_name == 'dashboard')echo 'active';?>">
        	<a class="d-flex align-items-center" href="<?php echo site_url('inventory/dashboard'); ?>">
        	    <i data-feather="home"></i>
        	    <span class="menu-title text-truncate fw-bolder" data-i18n="Dashboard">Dashboard</span>
            </a>
        </li>  
        
        <?php if($user_id == 4) {?>
            <li class="nav-item hidden <?php if($page_name == 'manage_access' || $page_name == 'manage_access_add' || $page_name == 'manage_access_edit') echo 'active'; ?>">
                <a class="d-flex align-items-center " href="<?php echo site_url('inventory/manage-access'); ?>">
                    <i data-feather="align-left"></i>
                    <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Access Management</span>
                </a>
            </li>
             
            <li class="nav-item <?php if($page_name == 'manage_staff' || $page_name == 'staff_add' || $page_name == 'staff_edit' || $page_name ==  'staff_password') echo 'active'; ?>">
                <a class="d-flex align-items-center " href="<?php echo site_url('inventory/manage-staff'); ?>">
                    <i data-feather="align-left"></i>
                    <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Staff Management</span>
                </a>
            </li>
        <?php } ?>

        <?php if($user_id == 4 || in_array('19', $access_array) || in_array('20', $access_array) || in_array('21', $access_array) || in_array('22', $access_array)) { ?>
		<!-- <li class="nav-item <?php if($page_name == 'raw_products' || $page_name == 'product_color' || $page_name == 'product_color_add' || $page_name == 'product_color_edit' || $page_name == 'categories' || $page_name == 'category_add' || $page_name == 'category_edit' || $page_name == 'product_size_add' || $page_name == 'product_size_edit' || $page_name == 'raw_products_import') echo 'active'; ?>">   
            <a class="d-flex align-items-center" href="#">
                <i class="feather icon-layers"></i>
               <span class="menu-title text-truncate" data-i18n="Orders">Product Master</span>
            </a>
            <ul class="menu-content"> -->
                <?php if($user_id == 4 || in_array('19',$access_array)) {?>
                    <!-- <li class="nav-item <?php if($page_name == 'categories' || $page_name == 'category_add' || $page_name == 'category_edit') echo 'active'; ?>">
                       <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/category">
                           <i class="feather icon-circle"></i>
                           <span class="menu-title text-truncate">Category</span>
                       </a>
                    </li> -->
                <?php } ?>
                <?php if($user_id == 4 || in_array('20',$access_array)) {?>
                    <!-- <li class="nav-item <?php if($page_name == 'product_size' || $page_name == 'product_size_add' || $page_name == 'product_size_edit') echo 'active'; ?>">
                       <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/product-size">
                           <i class="feather icon-circle"></i>
                           <span class="menu-title text-truncate">Sizes</span>
                       </a>
                    </li> -->
                <?php } ?>
                <?php if($user_id == 4 || in_array('21',$access_array)) {?>
                    <!-- <li class="nav-item <?php if($page_name == 'product_color' || $page_name == 'product_color_add' || $page_name == 'product_color_edit') echo 'active'; ?>">
                       <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/product-color">
                           <i class="feather icon-circle"></i>
                           <span class="menu-title text-truncate">Color</span>
                       </a>
                    </li> -->
                <?php } ?>
                
            <!-- </ul>
        </li> -->
        <?php } ?>
        
        <?php if($user_id == 4 || in_array('22',$access_array)) {?>
            <li class="nav-item <?php if($page_name == 'raw_products' || $page_name == 'raw_products_add' || $page_name == 'raw_products_edit' || $page_name == 'raw_products_import') echo 'active'; ?>">
                <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/raw-products">
                    <i class="feather icon-layers"></i>
                    <span class="menu-title text-truncate">Product</span>
                </a>
            </li>
        <?php } ?>
        
        <?php if($user_id == 4 || in_array('24',$access_array) || in_array('25',$access_array)) {?>
		<li class="nav-item <?php if($navigation == 'import_purchase_order' || $navigation == 'purchase_order') echo 'active'; ?>">   
            <a class="d-flex align-items-center" href="#">
                <i class="feather icon-layers"></i>
               <span class="menu-title text-truncate" data-i18n="Orders">Purchase</span>
           </a>
           <ul class="menu-content">
                <li class="nav-item <?php if($navigation == 'import_purchase_order') echo 'active'; ?>">
                    <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/imp-purchase-order">
                        <i class="feather icon-circle"></i>
                        <span class="menu-title text-truncate">Import PO</span>
                    </a>
                </li>
                <li class="nav-item <?php if($navigation == 'purchase_order') echo 'active'; ?>">
                    <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/purchase-order">
                         <i class="feather icon-circle"></i>
                        <span class="menu-title text-truncate">Local PO</span>
                    </a>
                </li>
           </ul>
        </li>
        <?php } ?>

        <?php if($user_id == 4 || in_array('23',$access_array)) {?>
	    <li class="nav-item <?php if($page_name == 'my_stock' || $page_name == 'qc_pending' || $page_name == 'my_stock_history' || $page_name == 'my_stock_batch') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/my-stock'); ?>">
               <i data-feather="database"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">My Stock</span>
           </a>
        </li>
        <?php } ?>
        
        <?php if($user_id == 4 || in_array('26',$access_array)) {?>
	    <li class="nav-item <?php if($page_name == 'sales_order' || $page_name == 'sales_order_add' || $page_name == 'sales_order_view'|| $page_name == 'sales_order_products') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/sales-order'); ?>">
               <i data-feather="align-left"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Sales</span>
           </a>
        </li>
		<?php } ?>
	   
        <?php if($user_id == 4 || in_array('27',$access_array)) {?>
		<li class="nav-item hidden <?php if($page_name == 'goods_return' || $page_name == 'goods_return_add' || $page_name == 'goods_return_view') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/goods-return'); ?>">
               <i data-feather="align-left"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Sales Return</span>
           </a>
        </li>
        <?php } ?>
        <?php if($user_id == 4 || in_array('28',$access_array)) {?>
		<li class="nav-item hidden <?php if($page_name == 'payment_reconceliation' || $page_name == 'payment_reconceliation_add' || $page_name == 'payment_reconceliation_view') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/payment-reconceliation'); ?>">
               <i data-feather="align-left"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Payment Reconceliation</span>
           </a>
        </li>
        <?php } ?>
        
        <?php if($user_id == 4 || in_array('4',$access_array)) {?>
		<li class="nav-item hidden <?php if($page_name == 'purchase_return' || $page_name == 'purchase_return_add' || $page_name == 'purchase_return_view') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/purchase-return'); ?>">
               <i data-feather="align-left"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Purchase Return</span>
           </a>
        </li>
        <?php } ?>
        
        <?php if($user_id == 4 || in_array('29',$access_array)) {?>
		<li class="nav-item hidden <?php if($page_name == 'scrap_product' || $page_name == 'damage_stock_product' || $page_name == 'damage_stock' || $page_name == 'damage_stock_add' || $page_name == 'damage_stock_view') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/damage-stock'); ?>">
               <i data-feather="align-left"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Damage Stock</span>
           </a>
        </li>
        <?php } ?>
		
        <?php if($user_id == 4 || in_array('7',$access_array)) {?>
		<li class="nav-item hidden <?php if($page_name == 'reserved_order' || $page_name == 'reserved_order_add' ) echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/reserved-order'); ?>">
               <i data-feather="clipboard"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Reserved Stock</span>
           </a>
        </li>
        <?php } ?>
     
        <?php if($user_id == 4 || in_array('9',$access_array)) {?>
	    <li class="nav-item hidden <?php if($page_name == 'stock_transfer_list' || $page_name == 'stock_transfer') echo 'active'; ?>">
           <a class="d-flex align-items-center " href="<?php echo site_url('inventory/stock-transfer-list'); ?>">
               <i data-feather="database"></i>
               <span class="menu-title text-truncate fw-bolder" data-i18n="admin">Stock Transfer</span>
           </a>
        </li>
        <?php } ?>
        
        <?php if($user_id == 4 || in_array('30',$access_array) || in_array('31',$access_array) || in_array('32',$access_array)) { ?>
		<li class="nav-item hidden">   
		   <a class="d-flex align-items-center" href="#">
				<i class="feather icon-layers"></i>
			   <span class="menu-title text-truncate" data-i18n="Orders">Reports</span>
		   </a>
		   <ul class="menu-content">
		       
                <?php if($user_id == 4 || in_array('30',$access_array)) {?>
				<li class="nav-item ">
				   <a class="d-flex align-items-center " href="<?php echo site_url('inventory/purchase-reports'); ?>">
					   <i class="feather icon-circle"></i>
					   <span class="menu-title text-truncate">Purchase</span>
				   </a>
			   </li>
               <?php } ?>
               <?php if($user_id == 4 || in_array('16',$access_array)) {?>
				<li class="nav-item hidden">
				   <a class="d-flex align-items-center " href="#">
					   <i class="feather icon-circle"></i>
					   <span class="menu-title text-truncate">Purchase Return</span>
				   </a>
			   </li>
               <?php } ?>
               <?php if($user_id == 4 || in_array('31',$access_array)) {?>
				<li class="nav-item ">
				   <a class="d-flex align-items-center " href="<?php echo site_url('inventory/sales-reports'); ?>">
					   <i class="feather icon-circle"></i>
					   <span class="menu-title text-truncate">Sales</span>
				   </a>
			   </li>
               <?php } ?>
               <?php if($user_id == 4 || in_array('32',$access_array)) {?>
				<li class="nav-item ">
				   <a class="d-flex align-items-center " href="<?php echo site_url('inventory/sales-return-reports'); ?>">
					   <i class="feather icon-circle"></i>
					   <span class="menu-title text-truncate">Sales Return</span>
				   </a>
			   </li>
               <?php } ?>
               <?php if($user_id == 4 || in_array('37',$access_array)) {?>
				<li class="nav-item ">
				   <a class="d-flex align-items-center " href="<?php echo site_url('inventory/stock-reports'); ?>">
					   <i class="feather icon-circle"></i>
					   <span class="menu-title text-truncate">Stock Report</span>
				   </a>
			   </li>
               <?php } ?>
		   </ul>
		</li>
        <?php } ?>
		
        <?php if($user_id == 4 || in_array('33',$access_array)) {?>
        <li class="nav-item <?php if($page_name == 'warehouse' || $page_name == 'warehouse_add' || $page_name == 'warehouse_edit') echo 'active'; ?>">
            <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/warehouse">
                <i class="feather icon-layers"></i>
                <span class="menu-title text-truncate">Warehouses</span>
            </a>
        </li>
        <?php } ?>
        <?php if($user_id == 4 || in_array('34',$access_array)) {?>
        <li class="nav-item <?php if($page_name == 'supplier' || $page_name == 'supplier_add' || $page_name == 'supplier_edit') echo 'active'; ?>">
            <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/supplier">
                <i class="feather icon-layers"></i>
                <span class="menu-title text-truncate">Supplier</span>
            </a>
        </li>
        <?php } ?>
       
        <?php if($user_id == 4 || in_array('36',$access_array)) {?>
        <li class="nav-item <?php if($navigation == 'customer') echo 'active'; ?>">
            <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/customer">
                <i class="feather icon-layers"></i>
                <span class="menu-title text-truncate">Customer</span>
            </a>
        </li>
        <?php } ?>

        <?php if($user_id == 4 || in_array('2',$access_array)) {?>
        <li class="nav-item <?php if($navigation == 'leads') echo 'active'; ?>">
            <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/leads/<?php echo ($this->session->userdata('super_type') == 'Inventory') ? 'all' : 'new';?>">
                <i class="feather icon-layers"></i>
                <span class="menu-title text-truncate">Leads</span>
            </a>
        </li>
        <?php } ?>

		<?php if($user_id == 4) {?>
        <li class="nav-item <?php if($page_name == 'payments' || $page_name == 'payments_add' || $page_name == 'payments_edit') echo 'active'; ?>">
            <a class="d-flex align-items-center " href="<?php echo base_url();?>inventory/payments">
                <i class="feather icon-layers"></i>
                <span class="menu-title text-truncate">Payments</span>
            </a>
        </li>
        <?php } ?>

    </ul>
</div>
