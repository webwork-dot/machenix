<style>
	.fixedElement{
		background : white;
		border-radius: .428rem;
	}
	
	.nav-pills.nav-justified .nav-item {
		display: flex;
		align-items: center;
	}
	
	.new-fix .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
		color: #1e652e;
		border: 1px solid #1e652e !important;
		background: white;
		box-shadow: initial;
		font-weight: 600;
	}
	
	.small-img{
		max-height: 50px;
		min-height: 50px;
		object-fit: cover;
		border-radius: 10px;
		border: 1px solid #e7e6e6;
		height: 50px;
		max-width: 60px;
	}
</style>

<div class="col-md-12 mb-1">
    <div class="fixedElement" id="fixedElement">
		<ul class="nav nav-pills bg-nav-pills nav-justified ">
			
            <li class="nav-item">
                <a href="<?php echo base_url();?>inventory/damage-stock" class="nav-link <?php echo ($page_name == 'damage_stock') ? 'active' : ''; ?> ">
                    <i class="mdi mdi-home-variant d-md-none d-block"></i>
                    <span class="d-none d-md-block">Entries</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo base_url();?>inventory/damage-stock-product" class="nav-link <?php echo ($page_name == 'damage_stock_product') ? 'active' : ''; ?> ">
                    <i class="mdi mdi-home-variant d-md-none d-block"></i>
                    <span class="d-none d-md-block">Damage Stock</span>
                </a>
            </li>
            <!--<li class="nav-item">-->
            <!--    <a href="<?php echo base_url();?>inventory/scrap-product" class="nav-link <?php echo ($page_name == 'scrap_product') ? 'active' : ''; ?> ">-->
            <!--        <i class="mdi mdi-home-variant d-md-none d-block"></i>-->
            <!--        <span class="d-none d-md-block">Scrap</span>-->
            <!--    </a>-->
            <!--</li>-->
            
		</ul>
	</div>
</div>