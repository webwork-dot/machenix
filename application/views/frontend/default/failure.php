<link rel="stylesheet" href="<?php echo rapl_url();?>assets/css/cust_style.css">
<style>
.mr-10{
    margin-right: 10px;
}
.order-confirm .anticon-check-circle {
    color: #0f4104;
    font-size: 110px;
    margin-bottom: 20px;
}
.card {
    border: 0px solid #ececec;
    border-radius: 10px;
}
.f-center {
    display: flex;
    flex-direction: row;
    justify-content: center;
}
.mx-height {
    min-height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
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

}
</style>
<main class="main">

 <div class="page-content pt-0">
<div class="container new-width">
<div class="checkout-area mt-10 ">
<div class="container">
    
    
    
<div class="row justify-content-center">
   <div class="col-lg-10 col-md-12 col-sm-12">
      <div class="card mb-10">
         <div class="card-body text-center mx-height">
            <div class="order-confirm">
               <div role="img" aria-label="check-circle" class="anticon anticon-check-circle">
                  <img src="<?php echo base_url();?>assets/image/failure.gif" style="height:100px"/>
               </div>
            </div>
            <h2 class="pb-2">Transaction Failed!</h2>
            <p class="font-size-sm mb-2">Your payment was not successfully processed. Please contact our customer support.</p>
         </div>
      </div>
   </div>
</div>
  
  
  
</div>
</div>



            
                       
            </div>
        </div>
    </main>     






