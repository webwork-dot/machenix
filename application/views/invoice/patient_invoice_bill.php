<?php $shipping=$data['shipping'];
if($shipping['billing_state']!=$data['warehouse_state']){
 $igst=1;    
}
else{
 $igst=0;   
}
?>
<html>
<link rel="stylesheet" href="<?= base_url();?>assets/mpdf/bootstrap.min.css">
<link rel="stylesheet" href="<?= base_url();?>assets/mpdf/custom.css">
<body class="invoice txtup">
      <div class="panel-body" id="page-wrap">
		<table id="invoice">
            <thead>
            <tr>
                <th class="head-img text-left">
        			<img src="<?= base_url();?>assets/image/logo-pdf.jpg">
                </th>
                <th class="text-right head">
                  <img src="<?= base_url();?>assets/image/logo-sehat.jpg" style="height:30px">
                  <p style="margin:0px !important;line-height:10px"><b>Tax Invoice</b></p>
        	      <p style="margin:0px !important;font-size:7.4px!important;line-height:10px">RAPL CUSTOMER CARE : +91 022 6111 9111 / Email : info@raplgroup.in</p>
                </th>
            </tr>
            </thead>
        </table>
		
		<table id="invoice_3" class="m-t-10 table" style="table-layout: fixed;border-top: 2px solid #808080;">
            <thead>
            <tr>
                <th class="p-l-r text-left border-r" style="width: 50%;">
                    <p><b>Rajasthan Aushdhalaya Pvt. Ltd.</b></p>
                    <p><?= $data['warehouse_address'];?></p>
                    <p><b>GSTIN : </b> <?= $data['warehouse_gst_no'];?></p>
                </th>
                <th class="p-l-r text-left"  style="width: 50%;">
                    <p><b>Order No : </b> <?= $data['order_unique_id'];?></p>
                    <p><b>Invoice No : </b> <?= $data['invoice_no'];?></p>
                    <p><b>Invoice Date : </b> <?= $data['invoice_date'];?> &nbsp; | &nbsp; <b>Invoice Time : </b> <?= $data['invoice_time'];?></p>
                    <p><b>Godown Name : </b>  <?= $data['warehouse'];?> &nbsp;&nbsp;  | &nbsp; <b>Invoice Maker : </b> <?= $data['invoice_maker'];?></p>
                </th>
            </tr>  
            
            <tr class="">
                <th class="p-l-r text-left border-t border-b border-r" style="width: 50%;">
                    <p><b> Patient Name : </b> <?= $shipping['doctor_name'];?></p>
					<p><b> Mobile No. : </b> <?= $shipping['mobile_no'];?></p>
                    <p><b> Billing Address : </b><?= $shipping['billing_address'].', '.$shipping['billing_state'].', '.$shipping['billing_city'].' - '.$shipping['billing_pincode'];?></p>
                    <p><b>GSTIN : </b> <?= $data['dr_gst_no'];?> &nbsp; &nbsp; &nbsp; &nbsp; 
					<b>State Code : </b> <?= $this->common_model->get_state_code($shipping['billing_state']);?></p>
                </th>
                <th class="p-l-r text-left border-t border-b"  style="width: 50%;">
                    <p><b>Place of Supply : </b> <?= $shipping['shipping_city'];?></p> 
					<p><b> Mobile No. : </b> <?= $shipping['mobile_no'];?></p>
                    <p><b> Shipping Address : </b><?= $shipping['shipping_address'].', '.$shipping['shipping_state'].', '.$shipping['shipping_city'].' - '.$shipping['shipping_pincode'];?></p>
                    <p><b>State Code : </b> <?= $this->common_model->get_state_code($shipping['shipping_state']);?></p>
                </th>
            </tr>
           </thead>
        </table>
        		
        <table id="invoice_1" class="m-t-10 mb-0 product table table-bordered">
        	<thead>
        
        		</thead>
        		<thead>
                    <tr>
		            <th rowspan="2" class="bold" style="width:20px">Sr.<br/>No</th>
        			<th rowspan="2" class="text-left bold"  style="width:200px">&nbsp; Description of Goods&nbsp;</th>
        			<th rowspan="2" class="bold text-center" style="width:50px"> HSN <br/>&nbsp; Code &nbsp;</th>
        			<th rowspan="2" class="bold" style="width:30px"> &nbsp;QTY. &nbsp;</th>
        			<th rowspan="2" class="text-left bold" style="width:30px"> Free<br/> Sch.</th>
        			<th rowspan="2" class="bold" style="width:80px"> &nbsp; Batch &nbsp; </th>
        			<th rowspan="2" class="bold" style="width:60px"> &nbsp; Expiry &nbsp; </th>
        			<th rowspan="2" class="bold" style="width:50px">Rate </th>
        			<th rowspan="2" class="bold" style="width:55px"> &nbsp; Taxable <br/> Value</th>
        			<?php if($igst==0){?>
                    <th colspan="2" class="bold text-center">CGST</span></th>
                    <th colspan="2" class="bold text-center">SGST</th> 
                    <?php } else{?>
                    <th colspan="2" class="bold text-center">IGST</th>  
                    <?php }?>
        		</tr> 
        		<tr>
        		   <?php if($igst==0){?>
                    <th class="bold" style="width:25px">%</th>
                    <th class="bold text-center" style="width:45px">Amt</th>
                    <th class="bold" style="width:25px">%</th>
                    <th class="bold text-center" style="width:45px">Amt</th>  
                    <?php } else{?>
                    <th class="bold" style="width:25px">%</th>
                    <th class="bold text-center" style="width:45px">Amt</th>  
                    <?php }?>
                </tr>
        	</thead>
        	<tbody>
        	  <?php 
        	  $total_qty=$total_rate=$total_taxable_amt=$total_gst_amt=$total_discount_amt=0;
        	  $is_change=1;
        	  $product_arr=array();
        	  $min_height='18';
        	  $pending_height=$min_height-count($data['inv_products']);
        	  foreach($data['inv_products'] as $key => $op): 
        	   $gst=(int) $op['gst'];
               $gst_amt=$op['gst_amt'];
               $product_id=$op['product_id'];
               $free_quantity=$op['free_batch_qty'];
               
       	       $total_qty=$total_qty + $op['batch_qty'];  
       	       $total_rate=$total_rate + $op['excl_price'];  
       	       $total_taxable_amt=$total_taxable_amt + $op['price_total'];    
       	       $total_discount_amt=$total_discount_amt + $op['discount'];    
       	       $total_gst_amt=$total_gst_amt + $gst_amt;  
              $key++;
             ?>  
        	  <tr>
    			<td><?= $key;?></td>
    			<td class="text-left"><?= $op['product_title'];?></td>
    			<td><?= $op['hsn_code'];?></td>
    			<td><?= $op['batch_qty'];?></td>
    			<td><?= $free_quantity;?></td>
    			<td><?= $op['batch_no'];?></td>
    			<td><?= $op['expiry_date'];?></td>
    			<td><?= $op['excl_price'];?></td>
    			<td><?= $op['price_total'];?></td>
    	     	<?php if($igst==0){?>
    	        <td><span style=""></span><?= $gst/2;?></td>
    			<td><span style=""></span><?= price_decimal($gst_amt/2);?></td> 
    			<td><span style=""></span><?= $gst/2;?></td>
    			<td><span style=""></span><?= price_decimal($gst_amt/2);?></td> 
    			<?php } else{?>
    			<td><span style=""></span><?= $gst;?></td>
    			<td><span style=""></span><?= price_decimal($gst_amt);?></td>
    		    <?php }?>
    		</tr>
          <?php endforeach;?>
          
          <?php for($i=0;$i<$pending_height;$i++):?>  
             <tr>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    	     	<?php if($igst==0){?>
    	        <td>&nbsp;</td>
    			<td>&nbsp;</td> 
    			<td>&nbsp;</td>
    			<td>&nbsp;</td> 
    			<?php } else{?>
    			<td>&nbsp;</td>
    			<td>&nbsp;</td>
    		    <?php }?>
    		</tr>
          <?php endfor;?>   
          
			</tbody>
			<tfoot>
    	   	     <tr>
        		  	<td  class="text-left bold" colspan="3">Total :</td>
        		  	<td  class="text-center bold"><?= $total_qty;?></td>
        			<td></td>
        			<td></td>
        			<td></td>
        			<td><span class="bold"></span></td>
        			<td><span style=""></span><span class="bold"></span></td>
        			
        			<?php if($igst==0){?>   
        	         <td></td>
        			 <td><span style=""></span><span class="bold"><?= price_decimal($total_gst_amt/2);?></span></td>
        	         <td></td>
        			 <td><span style=""></span><span class="bold"><?= price_decimal($total_gst_amt/2);?></span></td>
        			<?php } else{?>
        	         <td></td>
        			 <td><span style=""></span><span class="bold"><?= price_decimal($total_gst_amt);?></span></td>
        		    <?php }?>
        	       
        		</tr>
        
				<tr>
			  	  <td colspan="7"  class="text-left">
				   <p class="wl-20">Invoice value</p> <p class="wl-80"><?= rupees_word($data['price_total']);?></p>
				  </td>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">Taxable Value</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($total_taxable_amt);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">Taxable Value</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($total_taxable_amt);?></span></th>
        			<?php } ?>
        		</tr> 	
        		
        		<?php if($data['discount_per']!=NULL && $data['discount']>0){?>
        		<tr>
			    <td colspan="7" class="text-left"> <p class="text-center" style="font-size:7px !important;">Registered Address : RAPL House, Killedar Apartment, Opp. MTNL Office, S.V.Road, Jogeshwari (W), Mumbai - 400102 Ph. : 022 6111 9111</p></td>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">TOTAL Discount(%)</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['discount']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">TOTAL Discount(%)</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['discount']);?></span></th>
        			<?php } ?>
        		</tr> 
        		<?php } ?>
        		
        		<tr>
				
				
        		<?php if($data['delivery_charge']!=NULL && $data['delivery_charge']>0){?>				
			  	  <td colspan="7" rowspan="7" class="text-left">
        		<?php } else{?>
			  	  <td colspan="7" rowspan="5" class="text-left">	
        		<?php } ?>
			  	   
			  	    <div style="width:20%;display: inline-block;vertical-align: middle;margin-left:20px">
			  	        <p class="bold"> Name : ICICI BANK</p>
			  	        <p> A/C : 119405000747</p>
			  	        <p> IFSC : ICIC0001194</p>
			  	        
			  	        <hr style="margin: 5px 0;color:#000"/>
			  	        
			  	        <p class="bold"> Name : SBI BANK</p>
			  	        <p>A/C : 31177173426</p>
			  	        <p>IFSC : SBIN0004626</p>
			  	    </div>  
			  	    
			  	    <div style="width:25%;display: inline-block;vertical-align: middle;text-align: center;">
			  	       <p class="bold text-center" style="margin-bottom:10px"><u>BANK DETAILS:</u></p>
        		       <img src="<?= base_url();?>assets/image/rajasthan_qrcode.png" style="width:100px">
			  	    </div> 
			  	    
			  	    <div style="width:35%;display: inline-block;vertical-align: middle;margin-left:20px">
			  	        <p class="bold"> Quick Responce</p>
			  	        <p>Pay Through QR Code</p>
			  	        
			  	        <hr style="margin: 10px 0;"/>
			  	        
			  	        <p class="bold"> Name : ICICI BANK</p>
			  	        <p> A/C : 119405000006</p>
			  	        <p> IFSC : ICIC0001194</p>
			  	    </div> 
			  	      
			  	  </td>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">TOTAL CGST</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['gst_total']/2);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">TOTAL IGST</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['gst_total']);?></span></th>
        			<?php } ?>
        		</tr> 
        		
        		<tr>
				  <?php if($igst==0){?>   
        			<th  colspan="4" class="text-center"><span class="bold">TOTAL SGST</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['gst_total']/2);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">GST Total</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['gst_total']);?></span></th>
        			<?php } ?>
        		</tr>
        		
        		<tr>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">Freight Charges</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['freight_charges']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">Freight Charges</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['freight_charges']);?></span></th>
        			<?php } ?>
        		</tr> 
        		
        		<tr>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">TCS Tax (<?= price_decimal($data['tcs_per']);?>)</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['tcs']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">TCS Tax (<?= price_decimal($data['tcs_per']);?>)</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['tcs']);?></span></th>
        			<?php } ?>
        		</tr>
        		
        		
        		
        		<tr>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">Round Off</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['round_off']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">Round Off</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['round_off']);?></span></th>
        			<?php } ?>
        		</tr> 
        		
        		<?php if($data['delivery_charge']!=NULL && $data['delivery_charge']>0){?>
					<tr>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">Sub Total</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['price_total']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">Sub Total</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['price_total']);?></span></th>
        			<?php } ?>
        		</tr> 
        		<tr>
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">Delivery Charges</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['delivery_charge']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">Delivery Charges</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['delivery_charge']);?></span></th>
        			<?php } ?>
        		</tr> 
				  <tr>
			  	  <td colspan="7" class="text-center"><span class="bold">Term & Condition</span></td>
			  	  
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">Grand Total</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['payment_amount']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">Grand Total</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['payment_amount']);?></span></th>
        			<?php } ?>
        		</tr> 
        		<?php } else{?>  
				<tr>
			  	  <td colspan="7" class="text-center"><span class="bold">Term & Condition</span></td>
			  	  
				  <?php if($igst==0){?>   
        			<th colspan="4" class="text-center"><span class="bold">Grand Total</span></th>
        			<th colspan="3" class="text-center"><span class="bold"><?= price_decimal($data['price_total']);?></span></th>
        			<?php } else {?>
        			<th colspan="3" class="text-center"><span class="bold">Grand Total</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['price_total']);?></span></th>
        			<?php } ?>
        		</tr> 
        		<?php } ?>
				
     
        													

			</tfoot>	
        </table>
        
        <table id="invoice" class="m-t-10">
    		<tr class="border-top">
    			<td colspan="9" class="left">
		        	<p class="text-left" style="font-size:7.5px !important;">1. Good Once Sold will not be taken back after 6 months Irrespective of Manufacturing Defect.</p>
		        	<p class="text-left" style="font-size:7.5px !important;">2. Cheque Bounce Charges will be Added @ Rs. 500/-.</p>
		        	<p class="text-left" style="font-size:7.5px !important;">3. After transferring the Money in our account Please inform our Marketing Co-ordinator.</p>
		        	<p class="text-left" style="font-size:7.5px !important;">4. Any damaged goods, if recieved, please inform H.O. within 3-4 hours of receipt of the same.</p>
		        	<p class="text-left" style="font-size:7.5px !important;">5. Any disputes arising  from invoicing and payments will be under the juridiction of Mumbai courts only.</p>
    			</td>
    			<td class="right" colspan="3">
        		  <div class="text-right">
        		   <span class="bold">For RAJASTHAN AUSHDHALAYA PVT. LTD.</span><br/>
                  <img src="<?= base_url();?>assets/image/rapl-signature.jpg" style="height:50px"></div>
    		   	<b>Authorized Signatory</b>
    		</td>
       	</tr> 
      </table>

               		
            <br/>
       <table  class="m-t-10 border-t">
    		<tr class="border-top">
    			<td>
    		   	<p class="text-center" style="font-size:8px !important;">This is a computer generated invoice no signature required.</p>
    		</td>
    	</tr> 
    </table> 
	</div>	
</body>
</html>