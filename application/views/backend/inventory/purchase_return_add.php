<style>
    .text-right
    {
        text-align:  right;
    }
    .dis-input {
        margin-top: -7px;
        width: 65px !important;
        float: right !important;
        margin-left: 5px !important;
    }
	.fx-border {
		border: 1px solid #e0e0e0;
		padding: 5px 5px;
		box-shadow: 0 4px 24px 0 rgb(34 41 47 / 10%);
		background: #f4f8ff;
		position: relative;
		margin-bottom: 80px !important;
	}
</style>
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card" >
      <div class="card-body py-1 my-0">
            
          <?php echo form_open('inventory/purchase_return/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            
            
            <div class="col-12 col-sm-3 mb-1">
			  <input type="hidden" name="excel_id" id="excel_id" value="0">
              <label class="form-label" for="state">Warehouse <span class="required">*</span></label>
              <select class=" form-select select2" name="warehouse_id" id="from_warehouse_id" onchange="get_product_by_warehouse(this.value,'1');" required>
                <option value="">Select Warehouse </option>
                <?php foreach($warehouse_list as $item){?>
					<option value="<?php echo $item['id'];?>"><?php echo $item['name'];?></option>
                <?php }?>
              </select>
            </div>
			
			<div class="col-12 col-sm-3 mb-1">
              <label class="form-label" for="state">Supplier <span class="required">*</span></label>
              <select class=" form-select select2" name="supplier_id" id="supplier_id" required>
                <option value="">Select Supplier </option>
                <?php foreach($supplier_list as $item){?>
					<option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
                <?php }?>
              </select>
            </div>
			
			<div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Invoice No </label>
                    <input type="text" class="form-control" placeholder="Enter Order No" name="invoice_no" >
                </div>
            </div>
            
            <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Date <span class="required">*</span></label>
                    <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>" value="<?php echo date('Y-m-d');?>" id="date_picker">
                </div>
            </div>
            
			<div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                    <label>Reason<span class="required">*</span></label>
                    <textarea class="form-control" placeholder="" rows="1" name="reason" id="reason" required></textarea>
                </div>
            </div>
			
			<div class="col-12 col-sm-12 mt-1">
			  <div class="form-group">
				 <label>Select Input Method</label>
				 <div class="d-flex align-items-centermt-1">
					<div class="form-check me-3">
					   <input class="form-check-input" type="radio" id="manually" name="input_method" value="manually" checked onclick="toggleInputMethod('manually')">
					   <label class="form-check-label" for="manually">Manually</label>
					</div>
					<div class="form-check me-3">
					   <input class="form-check-input" type="radio" id="by_excel" name="input_method" value="by_excel" onclick="toggleInputMethod('by_excel')">
					   <label class="form-check-label" for="by_excel">By Excel</label>
					</div>
				 </div>
			  </div>
			</div>
            
			<div id = "requirement_area_1">
				<div class="col-12 col-sm-12 mb-1 fx-border mt-2 by_excel_div" style="display:none">
					<div class="col-12 p-2">
					  <div class="row">
						 <div class="col-12 col-sm-4 mb-1">
							<b>Upload Via Excel</b><br/>
							<a style="padding:0px;" class='btn-primary1 btn-file-upload '>
								<input type="file" class="form-control mt-1" name="fileURL" id="file_text" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" onchange="$('#upload-file-info3').html($(this).val().replace(/.*[\/\\]/, ''));" >
							</a>
						 </div>
						 <div class="col-12 col-sm-2 mb-1 mt-2">
							<label  for="bills_pending"></label><br>
							<span style="margin-top: 0px;" class='btn btn-md btn-primary blue btn-exverify btn-file-upload float-left' onClick="uploadExcel()">Upload File</span>
						 </div>
						 <div class="col-12 col-sm-2 mb-1">
						 </div>
						 <div class="col-12 col-sm-4 mb-1 mt-2">
							<div class="col-md-6 mt30" style="direction: rtl;">	
							   <label  for="bills_pending"></label><br>
							   <a class="btn btn-md btn-primary btn-file-upload downl-btn float-right" href="<?php echo base_url();?>uploads/purchase_return_stock_items.xlsx" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download Format</a>
							</div>
						 </div>
					  </div>
					</div>
				</div>
				
				<div class="col-12 col-sm-12 mb-1 manually_div">
                <div class="table-responsive">
                    <div class="col-lg-12 no-pad" style="min-height: 300px;">
                        <a class="btn btn-info text-white btn-sm" onclick="appendRequirement()" style="float:right;margin-bottom:5px;"><i class=" uil-plus-circle"></i>&nbsp;Add Row</a>
                        <table class="table table-striped table-bordered mn-table" id = "requirement_area">
                            <thead>
                               <tr>
                                  <th>
                                     <p>Product </p>
                                  </th>
                                    <th>
                                     <p>Available Stock</p>
                                    </th>
                                    <th>
                                     <p>Quantity</p>
                                    </th>
                                    <th>
                                     <p>Amount</p>
                                    </th>
                                    <th>
                                     <p>Action</p>
                                    </th>
                               </tr>
                            </thead>
                            <tbody class="element-1 new-table" id="product_1">
                               <tr>
                                  <td>
                                    <span class="new-td">
                                        <select class="form-control select2 product_id"  name="product_id[]"  id="product_id_1" data-toggle="select2" onchange="get_batch_by_product(this.value,'1');">
                                            <option value="">Select Product</option>
                                            <?php foreach($products_list as $item){?>
                                               <option value="<?php echo $item->id; ?>"><?php echo $item->item_code.' - '.$item->name; ?></option>
                                            <?php } ?>                            
                                        </select> 
                                    </span>
                                  </td>
                                  
                                  <td style="width: 100px !important;">
                                     <p class="td-blank"><input type="number" step="any" id="available_1"  name="available[]"  value="0" placeholder="" class="form-control" readonly></p>
                                  </td>
                                  <td style="width: 80px !important;">
                                     <p class="td-blank"><input type="number" step="any" id="quantity_1"  name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,'1')" value="0" class="form-control"></p>
                                  </td>
                                  <td style="width: 120px !important;">
                                     <p class="td-blank"><input type="number" step="any" id="amount_1"  name="amount[]" placeholder="Amount"  value="0" class="form-control"></p>
                                  </td>
                                  <td></td>
                               </tr>
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
				
			</div>
            
            
            
            <div class="col-12 text-center">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
          <?php echo form_close(); ?>		
        <!--/ form -->
      </div>
    </div>
    </div>
</div>

<script>

	function toggleInputMethod(method) {
		//alert(method);
		
      if (method == 'manually') {
		  $('.manually_div').show();
		  $('.by_excel_div').hide();
      } else if (method == 'by_excel') {
		  $('.manually_div').hide();
		  $('.by_excel_div').show();
      }
	  
	}
    
    function appendRequirement() {
        var from_warehouse_id = $('#from_warehouse_id').find(":selected").val();
      
        if(from_warehouse_id==''){
            Swal.fire({
                title: "Error!",
                text: "Please Select Warehouse !!" ,
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
        } else {
            var total_element = $(".element-1").length;  
            var lastid = $(".element-1:last").attr("id");
            var split_id = lastid.split("_");
            var nextindex = Number(split_id[1]) + 1;
            if($('#product_id_'+split_id[1]).find(":selected").val() == ''){
                Swal.fire({
                    title: "Error!",
                    text: "Please Select Previous Product !!" ,
                    icon: "error",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    },
                    buttonsStyling: !1
                });
            } else {
                $(".loader").show();
                $('#requirement_area').append('<tbody class="element-1 new-table" id="product_'+ nextindex +'"><tr><td><span class="new-td"><select class="form-control select2 product_id"  name="product_id[]"  id="product_id_'+ nextindex +'" data-toggle="select2" onchange="get_batch_by_product(this.value,'+ nextindex +');"  required><option value="">Select Product</option><?php foreach($products_list as $item){?><option value="<?php echo $item->id; ?>"><?php echo $item->item_code.' - '.$item->name; ?></option><?php } ?></select></span></td><td style="width: 100px !important;"><p class="td-blank"><input type="number" step="any" id="available_'+ nextindex +'"  name="available[]" value="0" class="form-control" readonly></p></td><td style="width: 80px !important;"><p class="td-blank"><input type="number" step="any" id="quantity_'+ nextindex +'"  name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,'+ nextindex +')" value="0" class="form-control" required></p></td><td style="width: 120px !important;"><p class="td-blank"><input type="number" step="any" id="amount_'+ nextindex +'"  name="amount[]" placeholder="Amount"  value="0" class="form-control" required></p></td><td><button type="button" class="btn btn-danger btn-sm" style="margin-top: 0px;" name="button" onclick="removeRequirement(this)"> <i class="dripicons-minus"></i> </button></td></tr></tbody>');
				
				$(".loader").fadeOut("slow");
				$(".select2").select2();
				var warehouse_id = $('#from_warehouse_id').val();
				get_product_by_warehouse(warehouse_id,nextindex);
            }
        }	
    }
    
    function removeRequirement(requirementElem) {
       $(requirementElem).parent().parent().remove();
      
    }
	
	function check_available_qty(value,id) {
        let is_disabled = 0 ;
        let total_element = $(".element-1").length + 1; 
        for (let i = 1; i < total_element; i++) {
            if($("#quantity_"+i).val()){
                let quantity = parseInt($("#quantity_"+i).val());
				let available = parseInt($("#available_"+i).val());
				console.log('qty:',quantity);
				console.log('av:',available);
				if(quantity > available){
					is_disabled = 1 ;
				}
            }
        }
        
        if(is_disabled == 1) {
            alert('Quantity cannot greater than Available Quantity');
            $(':input[type="submit"]').prop('disabled', true);
        } else {
            $(':input[type="submit"]').prop('disabled', false);
        }
    }
    
    function get_product_by_warehouse(b,nextindex) {
        let type = document.querySelector("#by_excel");
        let file = document.querySelector("#file_text");

        
        if(type.checked && file.value != "") {
            window.location.reload();
        } else {
    		var warehouse_id = $('#from_warehouse_id').find(":selected").val();
    	    var a = {
    		   warehouse_id: b
    		};
    		$.ajax({
    			type: "POST",
    			url: "<?php echo base_url()?>inventory/get_product_by_warehouse",
    			data: a,
    			success: function(res) {
    			   $('#product_id_'+nextindex).children("option:not(:first)").remove();
    			   $('#product_id_'+nextindex).append(res);
    			}
    		});
        }
    }
	
	function get_batch_by_product(b,nextindex) {
		var warehouse_id = $('#from_warehouse_id').find(":selected").val();
		var product_id = $('#product_id_'+nextindex).find(":selected").val();
		var is_disabled = 0 ;
		var total_element = $(".element-1").length + 1; 
		
		for (let i = 1; i < total_element; i++) {
			if(nextindex != i){
				if($("#product_id_"+i).val() == product_id){
					$("#product_id_"+nextindex+" option").prop("selected", false);
					$(".select2").select2();
					is_disabled = 1 ;
				}
			}
		}
		
		if(is_disabled == 0) {
			$(".select2").select2();
			$('#available_'+nextindex).val(0);
			var a = {
			   warehouse_id: warehouse_id,
			   product_id: product_id,
			};
			$.ajax({
				type: "POST",
				url:   "<?php echo base_url()?>inventory/get_qty_by_product",
				data: a,
				success: function(res) {
				   $('#available_'+nextindex).val(res.quantity);
				}
			});
		} else {
			Swal.fire({
				title: "Error!",
				text: "Product Can't Be Same!!!" ,
				icon: "error",
				customClass: {
					confirmButton: "btn btn-primary"
				},
				buttonsStyling: !1
			});
			$(':input[type="submit"]').prop('disabled', true);
		}
    }
    
    function get_product_details(b,nextindex) {
		var warehouse_id = $('#from_warehouse_id').find(":selected").val();
		var product_id = $('#product_id_'+nextindex).find(":selected").val();
		var batch_no = $('#batch_no_'+nextindex).find(":selected").val();
		is_disabled = 0 ;
		var total_element = $(".element-1").length + 1; 
        for (let i = 1; i < total_element ; i++) {
			if($("#product_id_"+i).val() && nextindex != i){
                var old_product_id = $("#product_id_"+i).val();
                var old_batch_no = $("#batch_no_"+i).val();
				if(old_product_id == product_id && batch_no == old_batch_no){
					$("#batch_no_"+nextindex+" option").prop("selected", false);
					$(".select2").select2();
					$('#available_'+nextindex).val(0);
					is_disabled = 1 ;
				}
            }
        }
		
		if(is_disabled == 0){
			$(':input[type="submit"]').prop('disabled', false);
			var a = {
			   warehouse_id: warehouse_id,
			   product_id: product_id,
			   batch_no: batch_no,
			};
			$.ajax({
				type: "POST",
				url:   "<?php echo base_url()?>inventory/get_available_qty",
				data: a,
				success: function(res) {
				   $('#available_'+nextindex).val(res.quantity);
				}
			});
		}else{
			Swal.fire({
				title: "Error!",
				text: "Product Can't Be Same!!!" ,
				icon: "error",
				customClass: {
					confirmButton: "btn btn-primary"
				},
				buttonsStyling: !1
			});
			$(':input[type="submit"]').prop('disabled', true);
			//$('#product_id_'+nextindex).prop("selected", false);
			//$(".select2").select2();
			
		}
	    
    }
	
	function uploadExcel() {
		var warehouseId = $('#from_warehouse_id').val();
		
		if (!warehouseId) {
			Swal.fire({
				title: "Error!",
				text: "Please Select Warehouse !!",
				icon: "error",
				customClass: {
					confirmButton: "btn btn-primary"
				},
				buttonsStyling: false
			});
			return;
		}

		 var fileInput = document.getElementById('file_text');
		 var file = fileInput.files[0];

		 if (!file) {
		   toastr.error('Please select a file to upload.');
		   $(".loader").fadeOut("slow");
		   $('.btn-exverify').html('Upload File');
		   return;
		 }

		 var formData = new FormData();
		 formData.append('fileURL', file);
		 formData.append('warehouse_id', warehouseId);
		 formData.append('type', 'purchase');
		 
		 $.ajax({
			type: "POST",
			url: "<?php echo base_url(); ?>phpspreadsheet/upload_return_stock_items",
			data: formData,
			processData: false,
			contentType: false,
			success: function(data) {
				$(".returnData").css("display", "block");
				$('.btn-exverify').html('Upload File');
				$(".loader").fadeOut("slow");
				if (data.status == '200') {
					resetFileInput(fileInput);
					$('#requirement_area_1').append(data.action);
					$('#excel_id').val(data.unique_id);
					$(".select2").select2();
					$('.by_excel_div').hide();
					document.getElementById('manually').disabled = true;
					document.getElementById('by_excel').disabled = true;
				} else if (data.status == 'false') {
					toastr.error('Please import the correct file; it did not match the Excel sheet column!');
				} else {
					toastr.error('An error occurred while uploading the file.');
				}
			},
			error: function(xhr, status, error) {
				toastr.error('An error occurred: ' + error);
				$(".loader").fadeOut("slow");
				$('.btn-exverify').html('Upload File');
			}
		 });
	}
	   
	function resetFileInput(input) {
		// Create a new input element and replace the old one
		var newInput = document.createElement('input');
		newInput.type = 'file';
		newInput.className = input.className;
		newInput.id = input.id;
		newInput.name = input.name;
		newInput.accept = input.accept;
		//newInput.required = input.required;

		// Replace the old input with the new input
		input.parentNode.replaceChild(newInput, input);
	}
    
   
    
</script>