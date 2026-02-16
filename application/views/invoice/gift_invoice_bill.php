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
                    <p><b> Name : </b> <?= $data['delivery_name'];?></p>
                    <p><b> Billing Address : </b><?= $data['delivery_address'];?></p>
                    <p><b>GSTIN : </b>URP</p>
                </th>
                <th class="p-l-r text-left border-t border-b"  style="width: 50%;">
                    <p><b> Place of Supply : </b> <?= $data['city_name'];?></p>
                    <p><b> Shipping Address : </b><?= $data['delivery_address'];?></p>
                </th>
            </tr>
           </thead>
        </table>
        		
        <table id="invoice_1" class="m-t-10 mb-0 product table table-bordered">
        	<thead>
        
        		</thead>
        		<thead>
                    <tr>
		            <th  class="bold" style="width:20px">Sr.<br/>No</th>
        			<th  class="text-left bold"  style="width:200px">&nbsp; Description of Goods&nbsp;</th>
        			<th  class="bold text-center" style="width:50px"> HSN <br/>&nbsp; Code &nbsp;</th>
        			<th  class="bold" style="width:30px"> &nbsp;QTY. &nbsp;</th>
        			<th  class="text-left bold" style="width:30px"> Free<br/> Sch.</th>
        			<th  class="bold" style="width:80px"> &nbsp; Batch &nbsp; </th>
        			<th  class="bold" style="width:60px"> &nbsp; Expiry &nbsp; </th>
        			<th  class="bold" style="width:50px">Rate </th>
        			<th  class="bold" style="width:55px"> &nbsp; Amount</th>
        	
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
        			
        	       
        		</tr>
        
				<tr>
			  	  <td colspan="5"  class="text-left">
				   <p class="wl-20">Invoice value</p> <p class="wl-80"><?= rupees_word($data['price_total']);?></p>
				  </td>
				
        			<th colspan="3" class="text-center"><span class="bold">Sub Total</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($total_taxable_amt);?></span></th>
        			
        		</tr> 	
        		
        		<?php if($data['discount_per']!=NULL && $data['discount']>0){?>
        		<tr>
			     <td colspan="5" class="text-left"> <p class="text-center" style="font-size:6.5px !important;">Regd Addr : RAPL House, Killedar Apartment, Opp. MTNL Office, S.V.Road, Jogeshwari (W), Mumbai - 400102 Ph. : 022 6111 9111</p></td>
				<th colspan="3" class="text-center"><span class="bold">TOTAL Discount(%)</span></th>
				<th class="text-center"><span class="bold"><?= price_decimal($data['discount']);?></span></th>
        		
        		</tr> 
        		<?php } ?>
        		
        		<tr>
			  	  <td colspan="5" rowspan="4" class="text-left" >
			  	   
			  	    <div style="width:20%;display: inline-block;vertical-align: middle;margin-left:20px">
			  	        <p class="bold" style="font-size: 8px;"> Name : ICICI BANK</p>
			  	        <p style="font-size: 8px!important;line-height: 8px;"> A/C : 119405000747</p>
			  	        <p style="font-size: 8px!important;line-height: 8px;"> IFSC : ICIC0001194</p>
			  	        
			  	        <hr style="margin: 5px 0;color:#000"/>
			  	        
			  	        <p class="bold" style="font-size: 8px;"> Name : SBI BANK</p>
			  	        <p style="font-size: 8px!important;line-height: 8px;">A/C : 31177173426</p>
			  	        <p style="font-size: 8px!important;line-height: 8px;">IFSC : SBIN0004626</p>
			  	    </div>  
			  	    
			  	    <div style="width:25%;display: inline-block;vertical-align: middle;text-align: center;">
			  	       <p class="bold text-center" style="margin-bottom:5px"><u>BANK DETAILS:</u></p>
        		       <img src="<?= base_url();?>assets/image/rajasthan_qrcode.png" style="width:75px">
			  	    </div> 
			  	    
			  	    <div style="width:35%;display: inline-block;vertical-align: middle;margin-left:20px">
			  	        <p class="bold" style="font-size: 8px!important;line-height: 8px;"> Quick Responce</p>
			  	        <p style="font-size: 8px!important;line-height: 8px;">Pay Through QR Code</p>
			  	        
			  	        <hr style="margin: 5px 0;"/>
			  	        
			  	        <p class="bold" style="font-size: 8px;line-height:8px;"> Name : ICICI BANK</p>
			  	        <p style="font-size: 8px!important;line-height: 8px;"> A/C : 119405000006</p>
			  	        <p style="font-size: 8px!important;line-height: 8px;"> IFSC : ICIC0001194</p>
			  	    </div> 
			  	      
			  	  </td>
				
        			<th colspan="3" class="text-center"><span class="bold">Freight Charges</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['freight_charges']);?></span></th>
        		
        		</tr> 
        	
        		
        		
        		
        		<tr>
        			<th colspan="3" class="text-center"><span class="bold">Round Off</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['round_off']);?></span></th>
        		
        		</tr> 
        		<tr>
        			<th colspan="3" class="text-center"><span class="bold">Grand Total</span></th>
        			<th class="text-center"><span class="bold"><?= price_decimal($data['price_total']);?></span></th>        		
        		</tr> 
				
				<tr>
        			<td colspan="3" class="text-center"></td>
        			<td class="text-center"><span class="bold"></span></td>        		
        		</tr>
        		
        		<tr>
			  	  <td colspan="5" class="text-center"><span class="bold">Term & Condition</span></td>
			  	  
        			<td colspan="3" class="text-center"><span class="bold"></span></td>
        			<td class="text-center"><span class="bold"></span></td>        		
        		</tr> 
        													

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