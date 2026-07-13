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
              <select class=" form-select select2" name="warehouse_id" id="from_warehouse_id" onchange="onWarehouseChange(this.value);" required>
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
						<div class="col-lg-12 no-pad">
							<a class="btn btn-info text-white btn-sm" onclick="appendRequirement()" style="float:right;margin-bottom:5px;"><i class=" uil-plus-circle"></i>&nbsp;Add Row</a>
							<table class="table table-striped table-bordered mn-table" id = "requirement_area">
								<thead>
								   <tr>
								    <th>
									    <p>Batch</p>
								    </th>
								    <th>
									    <p>Product</p>
								    </th>
									<th style="width: 95px">
									    <p>White Qty</p>
									</th>
									<th style="width: 95px">
									    <p>Black Qty</p>
									</th>
									<th style="width: 95px">
									    <p>White Amt</p>
									</th>
									<th style="width: 110px">
									    <p>Total White Amt</p>
									</th>
									<th style="width: 95px">
									    <p>Black Amt</p>
									</th>
									<th style="width: 110px">
									    <p>Total Black Amt</p>
									</th>
									<th style="width: 110px">
									    <p>Final Amt</p>
									</th>
									<th style="width: 95px">
									    <p>Action</p>
									</th>
								   </tr>
								</thead>
								<tbody class="element-1 new-table" id="product_1">
								   <tr>
									  <td>
										<span class="new-td">
											<select class="form-control select2 batch_no" name="batch_no[]" id="batch_no_1" onchange="onBatchChange(this.value, 1)" required>
												<option value="">Select Batch</option>
											</select> 
										</span>
									  </td>
									  <td>
										<span class="new-td">
											<select class="form-control select2 product_id" name="product_id[]" id="product_id_1" onchange="onProductChange(1)" required>
												<option value="">Select Product</option>
											</select> 
										</span>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="white_qty_1" name="white_qty[]" value="0" class="form-control qty-input" onkeyup="calculateRowAmounts(1)" onchange="calculateRowAmounts(1)" required></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="black_qty_1" name="black_qty[]" value="0" class="form-control qty-input" onkeyup="calculateRowAmounts(1)" onchange="calculateRowAmounts(1)" required></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="white_amt_1" name="white_amt[]" value="0" class="form-control amt-input" onkeyup="calculateRowAmounts(1)" onchange="calculateRowAmounts(1)" required></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="white_total_1" name="white_total[]" value="0.00" class="form-control" readonly></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="black_amt_1" name="black_amt[]" value="0" class="form-control amt-input" onkeyup="calculateRowAmounts(1)" onchange="calculateRowAmounts(1)" required></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="black_total_1" name="black_total[]" value="0.00" class="form-control" readonly></p>
									  </td>
									  <td>
										 <p class="td-blank"><input type="number" step="any" id="final_total_1" name="final_total[]" value="0.00" class="form-control" readonly></p>
									  </td>
									  <td></td>
								   </tr>
								</tbody>
							</table>
							
						</div>
					</div>
				</div>
				
				<div class="row justify-content-end mt-2 mb-2 pr-2">
					<div class="col-12 col-md-4 float-right">
						<div class="card" style="background: #e3e3e3;">
							<div class="card-body p-2">
								<div class="d-flex justify-content-between mb-1">
									<strong>Total White Amt:</strong>
									<span id="grand_white_total">0.00</span>
									<input type="hidden" name="white_total" id="input_grand_white_total" value="0.00">
								</div>
								<div class="d-flex justify-content-between mb-1">
									<strong>Total Black Amt:</strong>
									<span id="grand_black_total">0.00</span>
									<input type="hidden" name="black_total" id="input_grand_black_total" value="0.00">
								</div>
								<div class="d-flex justify-content-between">
									<strong>Final Total:</strong>
									<span id="grand_final_total" class="fw-bold text-primary">0.00</span>
									<input type="hidden" name="final_total" id="input_grand_final_total" value="0.00">
								</div>
							</div>
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
    function onWarehouseChange(warehouse_id) {
        // Clear all except the first row, reset first row
        var rowCount = $(".element-1").length;
        if (rowCount > 1) {
            $(".element-1").not(":first").remove();
        }
        
        // Reset the first row
        $('#batch_no_1').val('').trigger('change.select2');
        $('#product_id_1').val('').trigger('change.select2');
        $('#white_qty_1').val(0);
        $('#black_qty_1').val(0);
        $('#white_amt_1').val(0);
        $('#white_total_1').val('0.00');
        $('#black_amt_1').val(0);
        $('#black_total_1').val('0.00');
        $('#final_total_1').val('0.00');
        
        calculateGrandTotals();
        
        if (warehouse_id !== '') {
            get_batches_by_warehouse(warehouse_id, 1);
        }
    }

    function get_batches_by_warehouse(warehouse_id, id) {
        if (warehouse_id === '') {
            $('#batch_no_' + id).html('<option value="">Select Batch</option>').trigger('change.select2');
            return;
        }
        $(".loader").show();
        $.ajax({
            type: "POST",
            url: "<?php echo base_url('inventory/get_batches_by_warehouse'); ?>",
            data: { warehouse_id: warehouse_id },
            dataType: 'JSON',
            success: function(data) {
                $(".loader").fadeOut("slow");
                let options = '<option value="">Select Batch</option>';
                data.forEach((batch) => {
                    options += `<option value="${batch.batch_no}">${batch.batch_no}</option>`;
                });
                $('#batch_no_' + id).html(options);
                $('#batch_no_' + id).select2();
            },
            error: function() {
                $(".loader").fadeOut("slow");
            }
        });
    }

    function onBatchChange(batch_no, id) {
        var warehouse_id = $('#from_warehouse_id').val();
        if (warehouse_id === '' || batch_no === '') {
            $('#product_id_' + id).html('<option value="">Select Product</option>').trigger('change.select2');
            return;
        }
        
        $(".loader").show();
        $.ajax({
            type: "POST",
            url: "<?php echo base_url('inventory/get_products_by_batch'); ?>",
            data: { warehouse_id: warehouse_id, batch_no: batch_no },
            dataType: 'JSON',
            success: function(data) {
                $(".loader").fadeOut("slow");
                let options = '<option value="">Select Product</option>';
                data.forEach((prod) => {
                    options += `<option value="${prod.id}">${prod.name}</option>`;
                });
                $('#product_id_' + id).html(options);
                $('#product_id_' + id).select2();
            },
            error: function() {
                $(".loader").fadeOut("slow");
            }
        });
    }

    function onProductChange(id) {
        // Reset quantities/amounts for the row when product changes
        $('#white_qty_' + id).val(0);
        $('#black_qty_' + id).val(0);
        $('#white_amt_' + id).val(0);
        $('#white_total_' + id).val('0.00');
        $('#black_amt_' + id).val(0);
        $('#black_total_' + id).val('0.00');
        $('#final_total_' + id).val('0.00');
        calculateRowAmounts(id);
    }

    function checkDuplicateCombination(id) {
        var current_batch = $('#batch_no_' + id).val();
        var current_product = $('#product_id_' + id).val();
        
        if (current_batch === '' || current_product === '') return false;

        var is_duplicate = false;
        $(".element-1").each(function() {
            var row_id = $(this).attr('id').split('_')[1];
            if (row_id != id) {
                var batch = $('#batch_no_' + row_id).val();
                var product = $('#product_id_' + row_id).val();
                if (batch === current_batch && product === current_product) {
                    is_duplicate = true;
                    return false; // break loop
                }
            }
        });

        if (is_duplicate) {
            Swal.fire({
                title: "Error!",
                text: "This Batch and Product combination is already added in another row!",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
            $('#product_id_' + id).val('').trigger('change.select2');
            return true;
        }
        return false;
    }

    function calculateRowAmounts(id) {
        // Check duplicate on qty/amt changes
        if (checkDuplicateCombination(id)) {
            return;
        }

        var white_qty = parseFloat($('#white_qty_' + id).val()) || 0;
        var white_amt = parseFloat($('#white_amt_' + id).val()) || 0;
        var black_qty = parseFloat($('#black_qty_' + id).val()) || 0;
        var black_amt = parseFloat($('#black_amt_' + id).val()) || 0;

        var white_total = white_qty * white_amt;
        var black_total = black_qty * black_amt;
        var final_total = white_total + black_total;

        $('#white_total_' + id).val(white_total.toFixed(2));
        $('#black_total_' + id).val(black_total.toFixed(2));
        $('#final_total_' + id).val(final_total.toFixed(2));

        calculateGrandTotals();
    }

    function calculateGrandTotals() {
        var grand_white_total = 0;
        var grand_black_total = 0;
        var grand_final_total = 0;

        $(".element-1").each(function() {
            var id = $(this).attr('id').split('_')[1];
            var white_total = parseFloat($('#white_total_' + id).val()) || 0;
            var black_total = parseFloat($('#black_total_' + id).val()) || 0;
            var final_total = parseFloat($('#final_total_' + id).val()) || 0;

            grand_white_total += white_total;
            grand_black_total += black_total;
            grand_final_total += final_total;
        });

        $('#grand_white_total').text(grand_white_total.toFixed(2));
        $('#input_grand_white_total').val(grand_white_total.toFixed(2));

        $('#grand_black_total').text(grand_black_total.toFixed(2));
        $('#input_grand_black_total').val(grand_black_total.toFixed(2));

        $('#grand_final_total').text(grand_final_total.toFixed(2));
        $('#input_grand_final_total').val(grand_final_total.toFixed(2));
    }

    function appendRequirement() {
        var warehouse_id = $('#from_warehouse_id').val();
        if (warehouse_id === '') {
            Swal.fire({
                title: "Error!",
                text: "Please Select Warehouse !!",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
            return;
        }

        var lastid = $(".element-1:last").attr("id");
        var split_id = lastid.split("_");
        var nextindex = Number(split_id[1]) + 1;

        if ($('#product_id_' + split_id[1]).val() === '') {
            Swal.fire({
                title: "Error!",
                text: "Please Select Previous Product !!",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
            return;
        }

        $(".loader").show();

        var newRow = `
            <tbody class="element-1 new-table" id="product_${nextindex}">
                <tr>
                    <td>
                        <span class="new-td">
                            <select class="form-control select2 batch_no" name="batch_no[]" id="batch_no_${nextindex}" onchange="onBatchChange(this.value, ${nextindex})" required>
                                <option value="">Select Batch</option>
                            </select> 
                        </span>
                    </td>
                    <td>
                        <span class="new-td">
                            <select class="form-control select2 product_id" name="product_id[]" id="product_id_${nextindex}" onchange="onProductChange(${nextindex})" required>
                                <option value="">Select Product</option>
                            </select> 
                        </span>
                    </td>
                    <td>
                        <p class="td-blank"><input type="number" step="any" id="white_qty_${nextindex}" name="white_qty[]" value="0" class="form-control qty-input" onkeyup="calculateRowAmounts(${nextindex})" onchange="calculateRowAmounts(${nextindex})" required></p>
                    </td>
                    <td>
                        <p class="td-blank"><input type="number" step="any" id="black_qty_${nextindex}" name="black_qty[]" value="0" class="form-control qty-input" onkeyup="calculateRowAmounts(${nextindex})" onchange="calculateRowAmounts(${nextindex})" required></p>
                    </td>
                    <td>
                        <p class="td-blank"><input type="number" step="any" id="white_amt_${nextindex}" name="white_amt[]" value="0" class="form-control amt-input" onkeyup="calculateRowAmounts(${nextindex})" onchange="calculateRowAmounts(${nextindex})" required></p>
                    </td>
                    <td>
                        <p class="td-blank"><input type="number" step="any" id="white_total_${nextindex}" name="white_total[]" value="0.00" class="form-control" readonly></p>
                    </td>
                    <td>
                        <p class="td-blank"><input type="number" step="any" id="black_amt_${nextindex}" name="black_amt[]" value="0" class="form-control amt-input" onkeyup="calculateRowAmounts(${nextindex})" onchange="calculateRowAmounts(${nextindex})" required></p>
                    </td>
                    <td>
                        <p class="td-blank"><input type="number" step="any" id="black_total_${nextindex}" name="black_total[]" value="0.00" class="form-control" readonly></p>
                    </td>
                    <td>
                        <p class="td-blank"><input type="number" step="any" id="final_total_${nextindex}" name="final_total[]" value="0.00" class="form-control" readonly></p>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" style="margin-top: 0px;" name="button" onclick="removeRequirement(this)"> <i class="dripicons-minus"></i> </button>
                    </td>
                </tr>
            </tbody>
        `;

        $('#requirement_area').append(newRow);
        $(".loader").fadeOut("slow");
        $(".select2").select2();

        // Populate batches for the new row
        get_batches_by_warehouse(warehouse_id, nextindex);
    }

    function removeRequirement(requirementElem) {
        $(requirementElem).parent().parent().remove();
        calculateGrandTotals();
    }

    function checkForm(form) {
        var total_qty = 0;
        $(".element-1").each(function() {
            var id = $(this).attr('id').split('_')[1];
            var w_qty = parseFloat($('#white_qty_' + id).val()) || 0;
            var b_qty = parseFloat($('#black_qty_' + id).val()) || 0;
            total_qty += (w_qty + b_qty);
        });

        if (total_qty <= 0) {
            Swal.fire({
                title: "Error!",
                text: "Total return quantity must be greater than 0!",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary"
                },
                buttonsStyling: !1
            });
            return false;
        }
        return true;
    }
</script>