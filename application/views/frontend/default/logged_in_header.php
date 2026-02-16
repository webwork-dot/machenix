   <?php
   $menu   = $this->crud_model->get_menu_list();
   ?>
   <script type="text/javascript">
    $(function() {
        $('#company_input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '<?php echo base_url();?>home/search',
                        type: 'GET',
                        dataType: "json",
                        data: {
                            search: request.term,
                            request: 1
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(event, ui) {
                    event.preventDefault();
                    $("#company_input").val(ui.item.label);
                    var id = ui.item.value;
                    var url = ui.item.url;
                    window.location.href = "<?php echo base_url();?>"+ url;
                },
                focus: function(event, ui) {
                    event.preventDefault();
                    //$("#company_input").val(ui.item.label);
                },
            })
            .data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<a><div class='suggestText'> " + item.label + "</div></a>")
                    .appendTo(ul);
            };
    
    }); 
    $(function() {
        $('#mobile_input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '<?php echo base_url();?>home/search',
                        type: 'GET',
                        dataType: "json",
                        data: {
                            search: request.term,
                            request: 1
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(event, ui) {
                    event.preventDefault();
                    $("#mobile_input").val(ui.item.label);
                    var id = ui.item.value;
                    var url = ui.item.url;
                    window.location.href = "<?php echo base_url();?>"+ url;
                },
                focus: function(event, ui) {
                    event.preventDefault();
                    //$("#company_input").val(ui.item.label);
                },
            })
            .data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<a><div class='suggestText'> " + item.label + "</div></a>")
                    .appendTo(ul);
            };
    
    });
    </script>
    <header class="header-area header-style-1 header-height-2">
         <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container">
               <div class="header-wrap">
                  <div class="logo logo-width-1">
                     <a href="<?php echo base_url();?>"><img src="<?php echo base_url();?>assets/imgs/logo.png" alt="logo"></a>
                  </div>
                  <div class="header-right">
                     <div class="search-style-2">
                        <form action="#">
                           <input type="text" class="form-control ui-autocomplete-input" autocomplete="off" id="company_input" name="company_name" placeholder="Search from over 400+ products">
                        </form>
                     </div>
                     <div class="header-action-right">
                        <div class="header-action-2">
                            <div id="cart_items">
                             <?php $this->load->view('frontend/default/cart_items'); ?>
                            </div>
                           
                            <div class="header-action-icon-2">
								<a href="#">
									<img class="svgInject" alt="Nest" src="<?php echo base_url();?>assets/imgs/theme/icons/icon-user.svg">
								</a>
								<a href="#"><span class="lable ml-0">Account</span></a>
								<div class="cart-dropdown-wrap cart-dropdown-hm2 account-dropdown">
									<ul>
										<li><a href="<?php echo base_url();?>my-account"><i class="fi fi-rs-user mr-10"></i>My Account</a></li>
										<li><a href="<?php echo base_url();?>my-orders"><i class="fi fi-rs-location-alt mr-10"></i>My Order</a></li>
										<li><a href="<?php echo base_url();?>logout"><i class="fi fi-rs-sign-out mr-10"></i>Sign out</a></li>
									</ul>											
								</div>
							</div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="header-menu">
                <img src="<?php echo base_url(); ?>assets/imgs/leaf.png" class="leaf" />
                <div class="container">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="head-box">
                                <a href="<?php echo base_url(); ?>product/category/deaddiction">
                                <div class="tilte"> <img src="<?php echo base_url();?>assets/images/category/03122021053408_99578.jpg"> <span>De-Addiction</span></div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="head-box">
                                <a href="<?php echo base_url(); ?>product/category/nutraceutical">
                                <div class="tilte"><img src="<?php echo base_url();?>assets/images/category/03122021053552_62730.jpg">  <span>Nutraceutical</span></div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="head-box">
                                <a href="<?php echo base_url(); ?>product/category/cosmetics">
                                <div class="tilte"><img src="<?php echo base_url();?>assets/images/category/03122021053710_28348.jpg">  
                                <span>Cosmetics
                                </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="head-box">
                                <nav class="tilte">
                                    <ul>
                                        <?php 
                                        foreach($menu as $item){
                                        if($item['is_subcatgeory'] == 1){    
                                        ?>
                                        <li><a href="javascript:void(0)"><img src="<?php echo base_url();?>assets/images/category/03122021053815_59646.jpg"> <span><?php echo $item['category_name']; ?></span> </a>
                                        <ul>
                                            <?php 
                                             foreach($item['sub_cat'] as $product){
                                            ?>
                                            <li><a href="<?php echo base_url();?>product/<?php echo $item['category_slug']; ?>/<?php echo $product['sub_category_slug'];?>"><?php echo $product['sub_category_name'];?></a></li>
                                            <?php } ?>
                                        </ul>        
                                        </li>
                                        <?php } }?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>
         <div class="header-bottom header-bottom-bg-color sticky-bar">
            <div class="container">
               <div class="header-wrap header-space-between position-relative">
                  <div class="logo logo-width-1 d-block d-lg-none">
                     <a href="<?php echo base_url();?>"><img src="<?php echo base_url();?>assets/imgs/logo.png" alt="logo"></a>
                  </div>
                  <div class="header-nav d-none d-lg-flex">
                     
                     <div class="main-menu main-menu-padding-1 main-menu-lh-2 d-none d-lg-block font-heading">
                        <nav>
                           <ul>
                               <li><a href="<?php echo base_url();?>">Home</a></li>
                              <li><a href="<?php echo base_url();?>about-us">About Us</a></li>
                               <li>
                                    <a href="#">Our Products <i class="fi-rs-angle-down"></i></a>
                                    <ul class="sub-menu">
                                        <li><a href="<?php echo base_url(); ?>product/category/deaddiction">De-Addiction</a></li>
                                        <li><a href="<?php echo base_url(); ?>product/category/nutraceutical">Nutraceutical</a></li>
                                        <li><a href="<?php echo base_url(); ?>product/category/cosmetics">Cosmetics</a></li>
                                        <?php 
                                        foreach($menu as $item){
                                        if($item['is_subcatgeory'] == 1){    
                                        ?>
                                            <li><a href=""><?php echo $item['category_name']; ?> <i class="fi-rs-angle-right"></i></a>
                                                <ul class="level-menu level-menu-modify">
                                                    <?php 
                                                        foreach($item['sub_cat'] as $product){
                                                    ?>
                                                    <li><a href="<?php echo base_url();?>product/<?php echo $item['category_slug']; ?>/<?php echo $product['sub_category_slug'];?>"><?php echo $product['sub_category_name'];?></a></li>
                                                    <?php } ?>
                                                </ul>        
                                            </li>
                                        <?php } }?>
                                    </ul>
                                </li>
                              <li><a href="<?php echo base_url();?>export">Export</a></li>
                              <li><a href="<?php echo base_url();?>press-release">Press Release</a></li>
                              <li><a href="<?php echo base_url();?>our-inspirations">Our Inspirations</a></li>
                              <li><a href="<?php echo base_url();?>milestones">Milestones</a></li>
                              <li><a href="<?php echo base_url();?>contact-us">Contact Us</a></li>
                           </ul>
                        </nav>
                     </div>
                  </div>
                  <div class="hotline d-none d-lg-flex">
                     <img src="<?php echo base_url();?>assets/imgs/theme/icons/icon-headphone.svg" alt="hotline">
                     <p>022 6111 9111<span>Call Us</span></p>
                  </div>
                  <div class="header-action-icon-2 d-block d-lg-none">
                     <div class="burger-icon burger-icon-white">
                        <span class="burger-icon-top"></span>
                        <span class="burger-icon-mid"></span>
                        <span class="burger-icon-bottom"></span>
                     </div>
                  </div>
                  <div class="header-action-right d-block d-lg-none">
                     <div class="header-action-2">
                        <div class="header-action-icon-2">
                           <a class="mini-cart-icon" href="<?php echo base_url();?>cart">
                           <img alt="" src="<?php echo base_url();?>assets/imgs/theme/icons/icon-cart.svg">
                           <span><span class="pro-count white" id="cart_counts"><?php echo count($this->cart->contents());?></span></span>
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="mobile-search search-style-3 mobile-header-border">
                    <form action="#">
                        <input type="text" id="mobile_input" class="form-control ui-autocomplete-input" autocomplete="off" placeholder="Search from over 400+ products">
                        <button type="submit"><i class="fi-rs-search"></i></button>
                    </form>
                </div>
            </div>
         </div>
      </header>
      <div class="mobile-header-active mobile-header-wrapper-style">
         <div class="mobile-header-wrapper-inner">
            <div class="mobile-header-top">
               <div class="mobile-header-logo">
                  <a href="<?php echo base_url();?>"><img src="<?php echo base_url();?>assets/imgs/logo.png" alt="logo"></a>
               </div>
               <div class="mobile-menu-close close-style-wrap close-style-position-inherit">
                  <button class="close-style search-close">
                  <i class="icon-top"></i>
                  <i class="icon-bottom"></i>
                  </button>
               </div>
            </div>
            
            <div class="mobile-header-content-area">
                <div class="mobile-menu-wrap mobile-header-border">
                    <nav>
                        <ul class="mobile-menu font-heading">
                            <li><a href="<?php echo base_url();?>">Home</a></li>
                            <li><a href="<?php echo base_url();?>about-us">About Us</a></li>
                            <li class="menu-item-has-children">
                                <a href="#">Our Products</a>
                                <ul class="dropdown">
                                    <li><a href="<?php echo base_url(); ?>product/category/deaddiction">De-Addiction</a></li>
                                    <li><a href="<?php echo base_url(); ?>product/category/nutraceutical">Nutraceutical</a></li>
                                    <li><a href="<?php echo base_url(); ?>product/category/cosmetics">Cosmetics</a></li>
                                    
                                    
                                    <?php 
                                    foreach($menu as $item){
                                    if($item['is_subcatgeory'] == 1){    
                                    ?>
                                        <li class="menu-item-has-children"><a href=""><?php echo $item['category_name']; ?></a>
                                            <ul class="dropdown">
                                                <?php 
                                                    foreach($item['sub_cat'] as $product){
                                                ?>
                                                <li><a href="<?php echo base_url();?>product/<?php echo $item['category_slug']; ?>/<?php echo $product['sub_category_slug'];?>"><?php echo $product['sub_category_name'];?></a></li>
                                                <?php } ?>
                                            </ul>        
                                        </li>
                                    <?php } }?>
                                   
                                </ul>
                            </li>
                            <li><a href="<?php echo base_url();?>export">Export</a></li>
                              <li><a href="<?php echo base_url();?>press-release">Press Release</a></li>
                            <li><a href="<?php echo base_url();?>our-inspirations">Our Inspirations</a></li>
                            <li><a href="<?php echo base_url();?>milestones">Milestones</a></li>
                            <li><a href="<?php echo base_url();?>contact-us">Contact Us</a></li>
                            <li><a href="<?php echo base_url();?>my-account">My Account</a></li>
                            
                            
                        </ul>
                    </nav>
                </div>
            </div>
            
         </div>
      </div>
      
      
      