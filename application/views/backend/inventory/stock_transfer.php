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
</style>
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card" >
      <div class="card-body py-1 my-0">
            
          <?php echo form_open('inventory/stock_transfer/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">From Warehouse <span class="required">*</span></label>
              <select class=" form-select select2" name="from_warehouse_id" id="from_warehouse_id" onchange="get_product_by_warehouse(this.value,'1');" required>
                <option value="">Select Warehouse </option>
                <?php foreach($warehouse_list as $item){?>
					<option value="<?php echo $item['id'];?>"><?php echo $item['name'];?></option>
                <?php }?>
              </select>
            </div>
			
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">To Warehouse <span class="required">*</span></label>
              <select class=" form-select select2" name="to_warehouse_id" id="to_warehouse_id" onchange="check_warehouse_duplicate();" required>
                <option value="">Select Warehouse </option>
                <?php foreach($warehouse_list as $item){?>
					<option value="<?php echo $item['id'];?>"><?php echo $item['name'];?></option>
                <?php }?>
              </select>
            </div>
            
            
            
            <div class="col-12 col-sm-12 mb-1">
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
                                     <p>Quantity</p>
                                    </th>
                                    <th>
                                     <p>Available Stock</p>
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
                                        <select class="form-control select2 product_id"  name="product_id[]"  id="product_id_1" data-toggle="select2" onchange="get_batch_by_product(this.value,'1');"  required>
                                            <option value="">Select Product</option>
                                            <?php foreach($products_list as $item){?>
                                               <option value="<?php echo $item->id; ?>"><?php echo $item->name; ?></option>
                                            <?php } ?>                            
                                        </select> 
                                    </span>
                                  </td>
                                  <td style="width: 80px !important;">
                                     <p class="td-blank"><input type="number" step="any" id="quantity_1"  name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,'1')" value="0" class="form-control" required></p>
                                  </td>
                                  <td style="width: 150px !important;">
                                     <p class="td-blank"><input type="number" step="any" id="available_1"  name="available[]"  value="0" placeholder="" class="form-control" readonly></p>
                                  </td>
                                  <td></td>
                               </tr>
                            </tbody>
                        </table>
                        
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
    
    function appendRequirement() {
        var from_warehouse_id = $('#from_warehouse_id').find(":selected").val();
        var to_warehouse_id = $('#to_warehouse_id').find(":selected").val();
      
        if(from_warehouse_id==''){
            Swal.fire({
    			title: "Error!",
    			text: "Please Select From Warehouse !!" ,
    			icon: "error",
    			customClass: {
    				confirmButton: "btn btn-primary"
    			},
    			buttonsStyling: !1
    		});
        }else if(to_warehouse_id==''){
            Swal.fire({
    			title: "Error!",
    			text: "Please Select To Warehouse !!" ,
    			icon: "error",
    			customClass: {
    				confirmButton: "btn btn-primary"
    			},
    			buttonsStyling: !1
    		});
        }else{
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
            }else{
                $(".loader").show();
                $('#requirement_area').append('<tbody class="element-1 new-table" id="product_'+ nextindex +'"><tr><td><span class="new-td"><select class="form-control select2 product_id"  name="product_id[]"  id="product_id_'+ nextindex +'" data-toggle="select2" onchange="get_batch_by_product(this.value,'+ nextindex +');"  required><option value="">Select Product</option><?php foreach($products_list as $item){?><option value="<?php echo $item->id; ?>"><?php echo $item->name; ?></option><?php } ?></select></span></td><td style="width: 80px !important;"><p class="td-blank"><input type="number" step="any" id="quantity_'+ nextindex +'"  name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,'+ nextindex +')" value="0" class="form-control" required></p></td><td style="width: 120px !important;"><p class="td-blank"><input type="number" step="any" id="available_'+ nextindex +'"  name="available[]" value="0" class="form-control" readonly></p></td><td><button type="button" class="btn btn-danger btn-sm" style="margin-top: 0px;" name="button" onclick="removeRequirement(this)"> <i class="dripicons-minus"></i> </button></td></tr></tbody>');
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
        var is_disabled = 0 ;
        var total_element = $(".element-1").length + 1; 
        for (let i = 1; i < total_element ; i++) {
            if($("#quantity_"+i).val()){
                var quantity = Number($("#quantity_"+i).val());
				var available = Number($("#available_"+i).val());
				if(quantity > available){
					is_disabled = 1 ;
				}
            }
        }
        if(is_disabled == 1){
            alert('Quantity cannot greater than Available Quantity')
            $(':input[type="submit"]').prop('disabled', true);
        }else{
            $(':input[type="submit"]').prop('disabled', false);
        }
    }
    
    function check_warehouse_duplicate() {
        var from = $("#from_warehouse_id").val();
        var to = $("#to_warehouse_id").val();
		if(from!='' && to!=''){
			if(from == to){
				Swal.fire({
					title: "Error!",
					text: "From Warehouse Not Same To Warehouse!!!" ,
					icon: "error",
					customClass: {
						confirmButton: "btn btn-primary"
					},
					buttonsStyling: !1
				});
				$("#to_warehouse_id option").prop("selected", false);
				$(".select2").select2();
				$('.btn_verify').html('Submit');
				$(':input[type="submit"]').prop('disabled', true);
			}else{
				$(':input[type="submit"]').prop('disabled', false);
			}
		}
    }
    
    function get_product_by_warehouse(b,nextindex) {
		var warehouse_id = $('#from_warehouse_id').find(":selected").val();
	    var a = {
		   warehouse_id: b
		};
		$.ajax({
			type: "POST",
			url:   "<?php echo base_url()?>inventory/get_product_by_warehouse",
			data: a,
			success: function(res) {
			   $('#product_id_'+nextindex).children("option:not(:first)").remove();
			   $('#product_id_'+nextindex).append(res);
			}
		});
    }
    
    function get_batch_by_product(b,nextindex) {
		var warehouse_id = $('#from_warehouse_id').find(":selected").val();
		var product_id = $('#product_id_'+nextindex).find(":selected").val();
		var is_disabled = 0 ;
		var total_element = $(".element-1").length + 1; 
		for (let i = 1; i < total_element ; i++) {
			if(nextindex != i){
				if($("#product_id_"+i).val() == product_id){
					$("#product_id_"+nextindex+" option").prop("selected", false);
					$(".select2").select2();
					is_disabled = 1 ;
				}
			}
		}
		
		if(is_disabled == 0){
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
    
   
    
</script>