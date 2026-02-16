<html>
<link rel="stylesheet" href="<?= base_url();?>assets/mpdf/bootstrap.min.css">
<link rel="stylesheet" href="<?= base_url();?>assets/mpdf/custom.css">
<body class="invoice txtup">
      <div class="panel-body" id="page-wrap" style="margin:0px">
		<table id="invoice">
            <thead>
            <tr>
                <th class="text-left" style="width: 50%;padding: 0;">
        			<img src="<?= base_url();?>assets/image/logo-pdf.jpg" style="width:150px;">
                </th>
                <th class="text-right head" style="width: 50%;padding: 0;">
                  <p style="margin:0px!important;"><b>Shipping Bill</b></p>
        	      <p style="font-size:5px!important;margin: 0px!important;">RAPL CUSTOMER CARE : +91 022 6111 9111 / Email : info@raplgroup.in</p>
                </th>
            </tr>
            </thead>
        </table>
        
		<table id="invoice_3" class="m-t-0 table " style="table-layout: fixed;margin-bottom: 0px;  border-bottom: 1px dashed #000 !important;border-top: 2px solid #808080;">
            <thead>
            <tr class="border-b">
                <th class="p-l-r text-left" style="width: 100%;">
                    <p><b>To,</b></p>
                    <p><?= $data['doctor_name'];?></p>
                    <p style="line-height:10px;font-size:10px!important;"><?= $data['address'];?>,  <?= $data['city_name'];?>, <?= $data['area_name'];?>, <?= $data['state_name'];?>-<?= $data['pincode'];?></p>
                    <p><b>GSTIN : </b> <?= $data['dr_gst_no'];?></p>
                </th>
            </tr>  
         </table> 
        </thead> 
        
		<table id="invoice_3" class="m-t-0 table " style="table-layout: fixed;">
         <thead>
            <tr>
                <th class="p-l-r text-left" style="width: 100%;">
                    <p><b>From,</b></p>
                    <p><b><?= $data['warehouse'];?></b></p>
                    <p style="line-height:10px;font-size:10px!important;"><?= $data['warehouse_address'];?></p>
                    <p><b>GSTIN : </b> <?= $data['warehouse_gst_no'];?></p>
                 </th>
            </tr> 
           </thead>
        </table>
        
	</div>	
    </body>
    </html>