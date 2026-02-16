<link rel="stylesheet" href="<?php echo rapl_url();?>assets/css/cust_style.css">

<style type="text/css">
  .razorpay-payment-button,.razorpay-payment-button:active,.razorpay-payment-button:focus{
      background:#ff5252;
      font-size:14px;
      height:50px;
      margin:0;
      display:block;
      border-radius:3px;
      border:0;
      color:#fff;
      display:inline-block;
      box-shadow:none;
      line-height:42px;
      overflow:hidden;
      padding:0 25px;
      text-shadow:none;
      text-transform:capitalize;
      text-align:center;
      -webkit-transition:all .4s ease-out;
      -moz-transition:all .4s ease-out;
      -ms-transition:all .4s ease-out;
      -o-transition:all .4s ease-out;
      vertical-align:middle;
      white-space:nowrap;
      font-weight:500;
      width:100%
  }
  .razorpay-payment-button:hover {
      background:#d52115;
      color:#fff
  }
#addressModal .modal-dialog {
    max-width: 800px;
}
#addressModel .modal-dialog {
    max-width: 800px;
}
.add_address .modal-body {
    padding: 0px;
    margin: 0px;
}
.add_address .map_div
{
     padding: 0px;   
}
.add_address .form-group {
    margin-bottom: 10px !important;
}

.add_address .form-control {
    background: #fff !important;
    border-bottom: 0px !important;
    border: 1px solid #c1c1c1 !important;
    border-radius: 4px !important;
}
.add_address .btn {
    padding: 10px 25px !important;
    border-radius: 4px !important;
}
.save_btn{
    position: absolute;
    width: 100%;
    left: 1px;
    padding: 0px 10px;
    bottom: 0px;
}
.billing-fields input{
    border-radius:0px;
    background: #ffffff !important;
    height: 34px !important;
    border-bottom: 1px solid #e8e8e8 !important;
    border-top: 0px solid #e8e8e8 !important;
    border-left: 0px solid #e8e8e8 !important;
    border-right: 0px solid #e8e8e8 !important;
    padding: 0px 0px !important;
}
.billing-fields select{
    border-radius:0px;
    background: #ffffff !important;
    height: 34px !important;
    border-bottom: 1px solid #e8e8e8 !important;
    border-top: 0px solid #e8e8e8 !important;
    border-left: 0px solid #e8e8e8 !important;
    border-right: 0px solid #e8e8e8 !important;
    padding: 0px 0px !important;
}
label.radio-inline{
    margin-right: 10px;
    display: inline-flex;

}
label.radio-inline input{
 margin-right: 5px;
width: 20px;  
}
label.radio-inline span{
 margin-top: 5px;
}
.radio-inline{
padding-left: 0px;}

.header-bottom {
    padding: 25px 0;
}

.f-center{
 display: flex;
 flex-direction: row;
 justify-content: center;
}

.logo.logo-width-1 a img {
    width: 240px;
    min-width: 170px;
}

.logo.logo-width-1 {
  margin-right: 0px; 
  padding-left: 0px; 
}
.header-wrap{ display:block;}
.logo {
    padding: 0px 0;
}
.address_details_item .address_list span {
    margin-bottom: 0px;
    font-weight: 700;
}
.address_details_item .address_list  p{
    font-weight: 600;
}
.border-none{ border:none;}
.flex-vmiddle{
    display: flex;
    align-items: center;
}
.cart-prod h6 {
    margin-bottom: 5px;
}
.text-muted {
    color: #6c757d!important;
}

.cart-right-txt {
    color: #253D4E;
}
.trust_1 {
    display: flex;
    justify-content: space-around;
    width: 100%;
    align-content: center;
    align-items: center;
    flex-direction: column;
    flex-wrap: nowrap;
    flex-wrap: nowrap;
    margin-top: 5px;
}

.trust_1 span {
    margin-top: 0px;
    display: block;
    color: #116b31;
    font-weight: 500;
    font-size: 15px;
}
.checkout-area{  margin-top: 10px!important;}
.cart-prod h6,.product-des p {
    font-weight: 700;
}
.checkout-payment li:first-child label {
    font-weight: 700;
}

.footer-mobile {
    width: 100%;
    height: auto;
    position: fixed;
    padding: env(safe-area-inset-bottom);
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 9;
    transition: all ease 0.5s;
    -webkit-transition: all ease 0.5s;
    -moz-transition: all ease 0.5s;
    -ms-transition: all ease 0.5s;
}
.justify-content-around {
    -webkit-justify-content: space-around !important;
    -ms-flex-pack: distribute !important;
    justify-content: space-around !important;
}
.no-gutters {
    margin-right: 0;
    margin-left: 0;
}
.no-gutters > .col, .no-gutters > [class*='col-'] {
    padding-right: 0;
    padding-left: 0;
}
.btn-app{
    width: 100%;
    border-radius: 0px;
    font-weight: 700;
    letter-spacing: 1px;
}
.btn-app:hover {
    color: #fff;
    background-color: #116B31!important;
    border-color: #116B31!important;
}
.btn-app:disabled {
    color: #fff;
    opacity:1!important;
    background-color: #116B31!important;
    border-color: #116B31!important;
}



@media only screen and (max-width: 767px) {
.checkout-area{  margin-top: 5px!important;}
.logo.logo-width-1 a img {
    margin-top: 15px;
}
.header-bottom {
    padding: 0px 0;
}

.logo img{height: 60px;}
.footer-mid .logo img {
  max-width: 100%;
}
.cart-prod h6 {
    margin-bottom: 5px;
}


.cart-prod .text-brand {
   color: #116B31 !important;
   top: 0px; 
   margin-bottom: 5px;
}
.checkout-form-area,.your-order-fields {
    margin-top: 0px !important;
}

.checkout-payment li {
    margin-top: 15px;
}
.cart-totals .btn {
    font-size: 14px;
    font-weight: 600;
    padding: 5px 10px!important;
}

.checkout-form-area .checkout-title > h3 {
    font-weight: 700;
}
.text-muted, .text-end {  
    color: #253D4E !important;
    font-weight: 700;
}

.cart-prod h6 {
    font-size: 14px;
    font-weight: 700;
}
.address_details_item .address_list span {
    font-size: 14px;
    font-weight: 700;
}
.address_details_item .address_list  p {
   font-size: 14px;
    font-weight: 700;
}

.text-brand {
    color: #116B31 !important;
}
}
</style>

<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<script src="<?= base_url();?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<main class="main">
 <div class="page-content pt-0">
 <div class="container new-width">
            
<div class="trust_1">
	<img src="<?= base_url();?>assets/image/shield.png">
	<span>100% Safe &amp; Secure Payments</span>
</div>


 <div class="checkout-area mt-20 ">
  <div class="container">
  <div class="row"> 
    <h2 class="title-detail text-center mb-1 hidden-xs">Checkout</h2>
    <div class="col-md-8">
      <div class="checkout-form-area  bg-white border mt-20 p-md-3 br-10" style="border-radius: 10px;">
       <div class="checkout-title">
          <h3>Products</h3>
        </div>
        <div class="address_details_block">
            <div class="">
             <?php 
             $delivery_total = 0;
             $delivery_total = 0;
             foreach($data['order_items'] as $item){
              $uid=$item['id'];
              $title=$item['product_name'];
              $price=$item['price'];
              ?>
            <div class="row mb-1 pt-2 pb-1 cart-prod">
                <div class="col-lg-9 col-xs-8">
                    <h6><?php echo $item['product_name'];?></h6>
                    <h6 class="text-brand">₹<?php echo $item['total_price'];?></h6>
                </div>
                <div class="col-lg-3 col-xs-4 mmt-6 flex-vmiddle">
                    <div class="col-lg-12 product-des product-name text-right">
                        <p class="text-left ml-30">Qty : <?php echo $item['quantity']; ?></p>
                    </div>
                </div>
            </div>
            <?php } ?>
          </div>
        </div> 
        
       </div> 
       
      <div class="checkout-form-area  bg-white border mt-10 mb-20 p-md-3 br-10" style="border-radius: 10px;">
          
        <div class="checkout-title">
          <h3>Shipping Details </h3>
        </div>
        <div class="address_details_block">
          <?php 
           $order_address_id=$data['order_address'];
           $addresses=$data['addresses'];
           foreach ($addresses as $key => $value) {?>
          <div class="address_details_item border-none">
            <label class="container">
              <input type="radio" name="radio" class="address_radio" value="<?=$value['id'];?>" checked="checked">
              <span class="checkmark"></span>
            </label>
            
            <div class="address_list">
              <span><?=$value['name'];?> <?=$value['mobile_no'];?></span>
              <p>
                <?=$value['building_name'].', '.$value['district'].', '.$value['city'].', '.$value['state'].', '.$value['country'].' - '.$value['pincode'];?>
              </p>
            </div>
          </div>
          <?php } ?>

          <div class="ceckout-form add_addresss_block" style="background: #eee;padding: 20px;<?php if(empty($addresses)){ echo 'display: block';}else{  echo 'display: none'; } ?>" >

          </div>
        </div>

      </div>
      
    </div>
    <?php 
       $total_cart_amt=$delivery_charge=$delivery_total=0;
       $delivery_total=$data['delivery_charge'];
    ?>
    
  <div class="col-md-4">
    	<div class=" checkout-form-area border your-order-fields p-md-3 mt-20 cart-totals bg-white mb-10">
          <div class="checkout-title">
          <h3>Order Details</h3>
        </div>
        
        <div class="table-responsive mt-2">
            <table class="table no-border">
                <tbody>
                    <tr>
                        <td class="cart_total_label">
                            <h6 class="text-muted">Subtotal</h6>
                        </td>
                        <td class="cart_total_amount">
                            <h4 class="text-brand text-end">₹<?php echo $data['price_total']; ?></h4>
                        </td>
                    </tr>
                    <tr>
                        <td scope="col" colspan="2">
                            <div class="divider-2"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="cart_total_label">
                            <h6 class="text-muted">Delivery</h6>
                        </td>
                        <td class="cart_total_amount">
                            <h5 class="text-end text-brand">₹<?php echo $delivery_total; ?></h5></td> </tr> 
                            <tr>
                        <td scope="col" colspan="2">
                            <div class="divider-2"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="cart_total_label">
                            <h6 class="text-muted">Total</h6>
                        </td>
                        <td class="cart_total_amount">
                            <h4 class="text-brand text-end">₹<?php echo price_format_decimal($data['payment_amount']); ?></h4>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    

       
	<div class="checkout-form-area checkout-payment border p-md-3 cart-totals bg-white mb-10">
     <div><img src="<?= base_url();?>assets/image/pwim_modes_v5.png" class="mt-2"></div>
   </div>
   
   
  </div>
  </div>
</div>
</div>



            
                       
            </div>
        </div>
    </main>  
    


	 <div class="footer-mobile">
			<div class="clearfix"></div>
			<div class="row no-gutters justify-content-around" id="bg">
				<div class="col-12 col-md-12">
        		<form method="POST" name="place_order"> 
        			<input type="hidden" name="order_address" id="order_address" value="<?php echo $order_address_id;?>">
                  	<input  type="hidden" id="payment_method_pay" class="input-radio" name="payment_method" value="cash_free">
              
        	   	 <button type="button"  class="btn btn-primary btn_place_order btn-app">PLACE ORDER</button>
               </form>
				
			</div> 
			</div>
		</div> 


<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script type="text/javascript">
    $(".btn_place_order").on("click", function(e) {
        e.preventDefault();
        $(".process_loader").show();
        var _count = 0;

        var btn = $(this);

        var _formData = $("form[name='place_order']").serializeArray();

        var order_address=$('input[name="order_address"]').val(); 
     
        if (order_address != '0') {
            var _payment_method=$('input[name="payment_method"]').val();
          

           if (_payment_method == 'cash_free') {
                btn.attr("disabled", true);
                _count = 0;
                if (_count == 0) {
                    $(".process_loader").show();
                    href = base_url + 'patient/place_order_pg/<?= $enc_id;?>';
                    $.ajax({
                        type: 'POST',
                        url: href,
                        data: $("form[name='place_order']").serialize(),
                        success: function(res) {
                            var obj = $.parseJSON(res);
                            if (obj.success == '1') {
                                window.location.href = obj.msg;
                            } 
                            else if (obj.success == '-1') {
                                btn.attr("disabled", false);
                                var obj = $.parseJSON(res);
                                $(".process_loader").hide();
                                            
                                 Swal.fire({
                                  title: "Opps!",
                                  text: obj.msg,
                                  type: "error"
                                }).then((result) => {
                                  location.reload();
                                })   
                                                                
                            } else {
                                btn.attr("disabled", false);
                                var obj = $.parseJSON(res);
                                $(".process_loader").hide();
                               Swal.fire({
                                  icon: 'error',     
                                  title: 'Oops...',
                                  text: 'something went wrong',
                                })
                            }

                        }
                    });
                }
            }
        }
        else { 
        (".process_loader").hide();
          Swal.fire({
              icon: 'error',     
              title: 'Oops...',
              text: 'Please Enter Shipping Details',
            })
        }

    });
  
</script>

