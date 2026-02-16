<style>
    .error {
        color: red;
        margin-bottom: 10px;
        text-align: center;
        font-weight: 700;
    }
</style>
<!-- Bordered table start -->
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
      <div class="row d-block">
        <div id="csv-text" >
          <?php echo form_open_multipart('phpspreadsheet/upload_orders', [ 'id'=>"import_form_excel",'onkeypress' => "return event.keyCode != 13;"]); ?>
			
                <div class="col-12">
                <div class="card mb-0">
                  <div class="row">
					<div class="col-12 col-md-12 text-center mb-2" style="direction: rtl;border-bottom: 1px solid #ddd; padding-bottom: 15px;">	
                        <label for="bills_pending"></label>
                        <!--<a class="btn btn-md btn-primary btn-file-upload downl-btn " href="<?php echo base_url();?>uploads/kidsisland_orders.xlsx"   target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download Format</a>-->
                        <a class="btn btn-md btn-primary btn-file-upload downl-btn " href="<?php echo base_url();?>phpspreadsheet/sample_product_sales_excel"   target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download Format</a>
					</div>
					<div class="col-12 col-sm-3 mb-1">
						<label class="form-label" for="state">Customer <span class="required">*</span></label>
						<select class=" form-select select2" name="customer_id" id="customer_id"  required>
							<option value="">Select Customer </option>
							<?php foreach($customer_list as $item){?>
							    <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
							<?php }?>
						</select>
					</div>
                    
					<div class="col-12 col-sm-3 mb-1">
                        <div class="form-group">
                            <label>Refrence Order No </label>
                            <input type="text" class="form-control" placeholder="Enter Order No" name="refrence_no" >
                        </div>
                    </div>
					
					<div class="col-12 col-sm-3 mb-1">
                        <div class="form-group">
                            <label>Date <span class="required">*</span></label>
                            <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>" value="<?php echo date('Y-m-d');?>" id="date_picker">
                        </div>
                    </div>
            
					<div class="col-12 col-sm-3 mb-1">
					  <label class="form-label" for="state">Warehouse <span class="required">*</span></label>
					  <select class=" form-select select2" name="warehouse_id" id="warehouse_id"   required>
						<option value="">Select Warehouse </option>
						<?php foreach($warehouse_list as $item){?>
						<option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
						<?php }?>
					  </select>
					</div>
					
					<div class="col-12 col-sm-3 mb-1">
					  <label class="form-label" for="state">Company <span class="required">*</span></label>
					  <select class=" form-select select2" name="company_id" id="company_id"  required>
						 <option value="">Select Company </option>
						 <?php foreach($company_list as $item){?>
						 <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
						 <?php }?>
					  </select>
				   </div>
				   
                    <div class="col-12 col-sm-3 mb-1">
						<label class="form-label" for="state">Upload File<span class="required">*</span></label>
                        <a style="padding:0px;" class='btn-primary1 btn-file-upload '>
                            <input type="file" class="form-control" name="fileURL" id="file_text" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" onchange="$('#upload-file-info3').html($(this).val().replace(/.*[\/\\]/, ''));" required>
                        </a>
                    </div>
                    
         <!--           <div class="col-md-2 mt30">-->
         <!--               <label  for="bills_pending"></label><br>-->
					    <!--<button style="margin-top: 0px;" type="button" class='btn btn-md btn-primary blue btn-file-upload float-left btn-checkfile' onclick="checkFile()">Check File</button>-->
         <!--           </div>-->
                    
                    <!--<div class="col-md-2 mt30 d-none" id="submit-btn">-->
                    <div class="col-md-2 mt30" id="submit-btn">
                        <label for="bills_pending"></label><br>
					    <button style="margin-top: 0px;" type="submit" class='btn btn-md btn-primary blue btn-verify btn-file-upload float-left'>Submit</button>
                    </div>

                  </div>
                </div>
                </div>
			 <?php echo form_close(); ?>
            </div>
            
     </div>
   </div>
  </div>
 </div>
</div>


<div class="returnData">
    <div id="returnData"></div>
</div>
</div>
   
<script>     
  function checkForm(form) // Submit button clicked
  {
    form.btn_add.disabled = true; 
    $('#btn_add').html('Processing ...<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i>');
    return true;
  } 
  </script>  
<script>
$(document).ready(function() {
 $('#import_form_excel').on('submit', function(event){
   $(".loader").show(); 
    $('.btn-verify').html('Processing ...<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i>');
		event.preventDefault();
		$.ajax({
			url:"<?php echo base_url(); ?>phpspreadsheet/upload_orders",
			method:"POST",
			data:new FormData(this),
			contentType:false,
			cache:false,
			processData:false,
			success:function(data){	
                $(".returnData").css("display", "block");
			    $('.btn-verify').html('Submit');
			    $(".loader").fadeOut("slow"); 
			    $('#file_sta').val('');
				if(data!=''){
				$('#returnData').html(data);
				// 	toastr.success('Added Successfully!');
				}
				else if(data=='false'){
					toastr.error('Please import correct file, did not match excel sheet column!'); 
				} else {
					//toastr.success('Addded Successfully!');
				}
			}
		})
	});
});

function checkFile() {
    let form = $('#import_form_excel')[0]
    $(".loader").show(); 
    $('.btn-checkfile').html('Processing ...<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i>');
    $.ajax({
		url:"<?php echo base_url(); ?>phpspreadsheet/check_orders",
		method:"POST",
		data: new FormData(form),
		contentType:false,
		cache:false,
		processData:false,
		success:function(data){	
            $(".returnData").css("display", "block");
		    $('.btn-checkfile').html('Check File');
		    $(".loader").fadeOut("slow"); 
		    $('#file_sta').val('');
			if(data!=''){
			    $('#returnData').html(data);
			} else if(data=='false'){
				toastr.error('Please import correct file, did not match excel sheet column!'); 
			}
			else{
				
			}
			
			
		}
	})
}
</script>