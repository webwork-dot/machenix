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
            
          <?php echo form_open('inventory/goods_return/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
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
              <label class="form-label" for="state">Customer <span class="required">*</span></label>
              <select class=" form-select select2" name="customer_id" id="customer_id" required>
                <option value="">Select Customer </option>
                <?php foreach($customer_list as $item){?>
					<option value="<?php echo $item['id'];?>"><?php echo $item['company_name'];?></option>
                <?php }?>
              </select>
            </div>
            
			<div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label>Refrence Order No </label>
                    <input type="text" class="form-control" placeholder="Enter Order No" name="order_no" >
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
			
			<div id = "requirement_area_1">
				<div class="col-12 col-sm-12 mb-1">
					<div class="table-responsive">
						<div class="col-lg-12 no-pad" style="min-height: 300px;">
							<a class="btn btn-info text-white btn-sm" onclick="appendRequirement()" style="float:right;margin-bottom:5px;"><i class=" uil-plus-circle"></i>&nbsp;Add Row</a>
							<table class="table table-striped table-bordered mn-table" id = "requirement_area">
								<thead>
								   <tr>
									<th>
									    <p>Order ID </p>
									</th>
								    <th>
									    <p>Product </p>
								    </th>
									<th style="width: 95px">
									    <p>Customer</p>
									</th>
									<th style="width: 95px">
									    <p>Sale Quantity</p>
									</th>
									<th style="width: 95px">
									    <p>Quantity</p>
									</th>
									<th style="width: 95px">
									    <p>Amount</p>
									</th>
									<th style="width: 180px">
									    <p>Reason</p>
									</th>
									<th style="width: 95px">
									    <p>Action</p>
									</th>
								   </tr>
								</thead>
								<tbody class="element-1 new-table" id="product_1">
								   <tr>
								      <td style="width: 120px !important;">
										<input type="text" step="any" id="porder_id_1" name="porder_id[]" onkeyup="getProductsById(this, 1)" class="form-control" required>
									  </td>
									  <td>
										<span class="new-td">
											<select class="form-control select2 product_id"  name="product_id[]"  id="product_id_1" data-toggle="select2" onchange="getOrderById(this, 1)" required>
												<option value="">Select SKU - Size</option>
											</select> 
										</span>
									  </td>
									  <td>
                                         <p class="td-blank">
                                            <input type="text" id="customer_1"  name="customer[]" placeholder="Customer" class="form-control" readonly>
                                         </p>
                                      </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="sale_quantity_1"  name="sale_quantity[]" placeholder="Sale Qty" value="0" class="form-control" readonly></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="quantity_1"  name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,'1')" value="0" class="form-control" required></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="amount_1"  name="amount[]" placeholder="Amount"  value="0" class="form-control" readonly></p>
									  </td>
									  <td>
										 <span class="new-td">
											<select class="form-control reason" name="reason_id[]"  id="reason_1" required>
												<option value="Customer Return">Customer Return</option>
												<option value="RTO">RTO</option>
												<option value="Cancelled">Cancelled</option>
											</select> 
										</span>
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

    let debouncer;
    function getProductsById(element, id) {
        clearTimeout(debouncer);
        if(element.value != '') {
            debouncer = setTimeout(() => {
                let warehouse = document.querySelector('#from_warehouse_id').value;
                if(warehouse == '') {
                     Swal.fire({
                        title: "Error!",
                        text: "Please Select Warehouse !!" ,
                        icon: "error",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        },
                        buttonsStyling: !1
            		});
            		
            		$('#porder_id_' + id).val('');
                } else {
                    $(".loader").show(); 
                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>inventory/get_sale_order_items",
                        data: {value: element.value},
                        dataType: 'JSON',
                        success: function(data) {
                            $(".loader").fadeOut("slow");
                            if(data.product.length == 0) {
                                resetCurrentField(id)
                            } else { 
                                resetCurrentField(id)
                                let options = '<option value="">Select SKU - Size</option>';
                                data.product.forEach((prod) => {
                                    let totalQty = parseInt(prod.qty);
                                    let returnQty = parseInt(prod.return_qty);
                                    
                                    if(totalQty > returnQty) {
                                        options += `<option value="${prod.id}">${prod.item_code} - ${prod.size_name}</option>`;
                                    }
                                }); 
                                
                                document.querySelector('#product_id_' + id).innerHTML = options;
                                $('#product_id_' + id).select2();
                            }
                        },
                        
                    });     
                }
            }, 500);
        } else {
            resetCurrentField(id);
        }
	} 
	
	function getOrderById(element, id) {
	    let value = element.value;
	    let orderId = $('#porder_id_' + id).val();
	    
	    let allValue = document.querySelectorAll('[name="product_id[]"]');
	    let allOrderId = document.querySelectorAll('[name="porder_id[]"]');
	    let counter = 0;
	    for(let i = 0; i < allValue.length; i++) {
	        if(allValue[i].value == value && allOrderId[i].value == orderId) {
	            if(counter != 2) {
	                counter++;
	            } else {
	                break;
	            }
	        }
	    }
	    
	    if(counter == 2) {
	        element.innerHTML = `<option value="">Select SKU - Size</option>`;
	        $('#porder_id_' + id).val('');
	        
	        Swal.fire({
    			title: "Error!",
    			text: "SKU and Order ID cannot be same" ,
    			icon: "error",
    			customClass: {
    				confirmButton: "btn btn-primary"
    			},
    			buttonsStyling: !1
    		});
	    } else {
            $(".loader").show();
            $.ajax({
                type: "POST",
                url: "<?php echo base_url(); ?>inventory/get_sale_order_product",
                data: {value: value},
                dataType: 'JSON',
                success: function(data) {
                    $(".loader").fadeOut("slow");
                    console.log(data.product.length)
                    if(data.product.length == 0) {
                        document.querySelector('#sale_quantity_' + id).value = 0;
                        document.querySelector('#quantity_' + id).value = 0;
                        document.querySelector('#amount_' + id).value = 0;
                        document.querySelector('#customer_' + id).value = '';
                    } else {
                        document.querySelector('#sale_quantity_' + id).value = data.product.sale_qty;
                        document.querySelector('#quantity_' + id).value = 0;
                        document.querySelector('#amount_' + id).value = data.product.total_amount;
                        document.querySelector('#customer_' + id).value = data.product.customer_name;
                    }
                },
                
            });   
	    }
	}
	 
	function resetCurrentField(id) {
	    document.querySelector('#product_id_' + id).innerHTML = `<option value="">Select SKU - Size</option>`;
	    document.querySelector('#sale_quantity_' + id).value = 0;
	    document.querySelector('#quantity_' + id).value = 0;
	    document.querySelector('#amount_' + id).value = 0;
	    document.querySelector('#customer_' + id).value = '';
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
                // $('#requirement_area').append('<tbody class="element-1 new-table" id="product_'+ nextindex +'"><tr><td><span class="new-td"><select class="form-control select2 product_id"  name="product_id[]"  id="product_id_'+ nextindex +'" data-toggle="select2" onchange="get_batch_by_product(this.value,'+ nextindex +');"  required><option value="">Select Product</option><?php foreach($products_list as $item){?><option value="<?php echo $item->id; ?>"><?php echo $item->item_code.' - '.$item->name; ?></option><?php } ?></select></span></td><td style="width: 120px !important;"><input type="text" step="any" id="porder_id_'+ nextindex +'" name="porder_id[]" class="form-control" ></td><td style="width: 80px !important;"><p class="td-blank"><input type="number" step="any" id="quantity_'+ nextindex +'"  name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,'+ nextindex +')" value="0" class="form-control" required></p></td><td><button type="button" class="btn btn-danger btn-sm" style="margin-top: 0px;" name="button" onclick="removeRequirement(this)"> <i class="dripicons-minus"></i> </button></td></tr></tbody>');
                $('#requirement_area').append(`<tbody class="element-1 new-table" id="product_${nextindex}">
                                                  <tr>
                                                    <td>
                                                      <input type="text" step="any" id="porder_id_${nextindex}" name="porder_id[]" class="form-control" onkeyup="getProductsById(this, ${nextindex})" required/>
                                                    </td>
                                                    <td>
                                                      <span class="new-td">
                                                        <select class="form-control select2 product_id" name="product_id[]" id="product_id_${nextindex}" onchange="getOrderById(this, ${nextindex})" data-toggle="select2" required>
                                                          <option value="">Select SKU - Size</option>
                                                        </select>
                                                      </span>
                                                    </td>
                                                    <td>
                                                      <p class="td-blank">
                                                        <input type="text" id="customer_${nextindex}" name="customer[]" placeholder="Customer" class="form-control" readonly>
                                                      </p>
                                                    </td>
                                                    <td>
                                                      <p class="td-blank">
                                                        <input type="number" step="any" id="sale_quantity_${nextindex}"  name="sale_quantity[]" placeholder="Sale Qty" value="0" class="form-control" readonly>
                                                      </p>
                                                    </td>
                                                    <td>
                                                      <p class="td-blank">
                                                        <input type="number" step="any" id="quantity_${nextindex}" name="quantity[]" placeholder="Qty" onkeyup="check_available_qty(this.value,${nextindex})" value="0" class="form-control" required />
                                                      </p>
                                                    </td>
                                                     <td>
                                                        <p class="td-blank">
                                                          <input type="number" step="any" id="amount_${nextindex}"  name="amount[]" placeholder="Amount"  value="0" class="form-control" readonly>
                                                        </p>
                                                      </td>
                                                      <td>
                                                        <span class="new-td">
                											<select class="form-control reason" name="reason_id[]"  id="reason_${nextindex}" required>
                												<option value="Customer Return">Customer Return</option>
                												<option value="RTO">RTO</option>
                												<option value="Cancelled">Cancelled</option>
                											</select> 
                										</span>
                									  </td>
                                                    <td>
                                                      <button type="button" class="btn btn-danger btn-sm" style="margin-top: 0px" name="button" onclick="removeRequirement(this)">
                                                        <i class="dripicons-minus"></i>
                                                      </button>
                                                    </td>
                                                  </tr>
                                                </tbody>`);
                                                
                                                
                                                
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
	
	function check_available_qty(value, id) {
        let availableQty = document.querySelector('#sale_quantity_' + id).value
        if(parseFloat(value) > parseFloat(availableQty)) {
            document.querySelector('#quantity_' + id).value = 0;
            
            Swal.fire({
                title: "Error!",
                text: "Quantity Can't be greater than Sale Quantity" ,
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
	    }
	    
		/*
            var is_disabled = 0 ;
            var total_element = $(".element-1").length + 1; 
            for (let i = 1; i < total_element ; i++) {
                if($("#quantity_"+i).val()){
                    var quantity = parseInt($("#quantity_"+i).val());
    				console.log('qty:',quantity);
    				console.log('av:',available);
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
		*/
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
			 //  $('#product_id_'+nextindex).children("option:not(:first)").remove();
			 //  $('#product_id_'+nextindex).append(res);
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
					//$('#available_'+nextindex).val(0);
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