<link href="https://cdn.jsdelivr.net/npm/remixicon@2.2.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.1.0/css/fixedColumns.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.1.0/js/dataTables.fixedColumns.min.js"></script>

<style>
   .returnData{
    display:none;
}
.returnData h6{
    font-size: 16px;
    color: red;
    text-align: center;
    font-weight: 600;
}
</style>

<!-- Bordered table start -->
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
      <div class="row">
        <div id="csv-text" >
          <?php echo form_open_multipart('phpspreadsheet/upload_emp_attendance', [ 'id'=>"import_form_excel",'onkeypress' => "return event.keyCode != 13;"]); ?>
           <div class="row">
                <div class="col-12">
                <div class="card mb-0">
                  <div class="row">
				  
					<div class="col-md-3 col-12">
						<label class="form-label">Select Month/Year <i class="required">*</i></label>
						<div class="input-group">
							<select class="form-select" name="month_id" required>
							<option value="" <?php if($_GET['month']==''){ echo 'selected';}?>>Select Month</option>
							<option value="01" <?php if($_GET['month']=='01'){ echo 'selected';}?>>January</option>
							<option value="02" <?php if($_GET['month']=='02'){ echo 'selected';}?>>February</option>
							<option value="03" <?php if($_GET['month']=='03'){ echo 'selected';}?>>March</option>
							<option value="04" <?php if($_GET['month']=='04'){ echo 'selected';}?>>April</option>
							<option value="05" <?php if($_GET['month']=='05'){ echo 'selected';}?>>May</option>
							<option value="06" <?php if($_GET['month']=='06'){ echo 'selected';}?>>June</option>
							<option value="07" <?php if($_GET['month']=='07'){ echo 'selected';}?>>July</option>
							<option value="08" <?php if($_GET['month']=='08'){ echo 'selected';}?>>August</option>
							<option value="09" <?php if($_GET['month']=='09'){ echo 'selected';}?>>September</option>
							<option value="10" <?php if($_GET['month']=='10'){ echo 'selected';}?>>October</option>
							<option value="11" <?php if($_GET['month']=='11'){ echo 'selected';}?>>November</option>
							<option value="12" <?php if($_GET['month']=='12'){ echo 'selected';}?>>December</option>
						</select>
						<select class="form-select" name="year" required>
							<option value="">Select Year</option>
							<?php
							$currentYear = CURREN_YEAR;
							for ($i = $currentYear; $i <= date('Y'); $i++) {
								$selected = ($_GET['year'] == $i) ? 'selected' : '';
								echo "<option value='$i' $selected>$i</option>";
							}
							?>
						</select>
					</div>
				   </div>
					
					
					
                    <div class="col-12 col-sm-4 mb-1">
                      <label class="form-label">Attendance Upload Via Excel <i class="required">*</i></label>
                        <a style="padding:0px;" class='btn btn-md btn-primary1 btn-file-upload float-left'>
                            <input type="file" class="form-control" name="fileURL" id="file_text" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" onchange="$('#upload-file-info3').html($(this).val().replace(/.*[\/\\]/, ''));" required>
                        </a>
                    </div>
                    
                    <div class="col-md-2 mt30">
                        <label  for="bills_pending"></label><br>
					    <button  style="margin-top: 5px;" type="submit" class='btn btn-md btn-primary blue btn-verify btn-file-upload float-left'>Upload File</button>
					</div>

					<div class="col-md-3 mt30" style="direction: rtl;">	
					<label  for="bills_pending"></label><br>
				      <a class="btn btn-md btn-outline-primary btn-file-upload downl-btn float-right" href="<?php echo base_url().'excel/sample-attendance-excel';?>" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download Format</a>
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
</div>


<div class="returnData">
<div id="returnData"></div>
</div>
   
<script>     
  function checkForm(form){
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
			url:"<?php echo base_url(); ?>phpspreadsheet/upload_emp_attendance",
			method:"POST",
			data:new FormData(this),
			contentType:false,
			cache:false,
			processData:false,
			success:function(data){	
                $(".returnData").css("display", "block");
			    $('.btn-verify').html('Upload File');
			    $(".loader").fadeOut("slow"); 
			    $('#file_sta').val('');
				if(data!=''){
				$('#returnData').html(data);
			    //toastr.success('Addded Successfully!');
				}
				else if(data=='false'){
			    toastr.error('Please import correct file, did not match excel sheet column!'); 
				}
				else{
			    //toastr.success('Addded Successfully!');
				}
			}
		})
	});
});

</script>

