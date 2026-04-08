<style>
	.text-right {
		text-align: right;
	}

	.dis-input {
		margin-top: -7px;
		width: 65px !important;
		float: right !important;
		margin-left: 5px !important;
	}

	.dis-input-1 {
		margin-top: 0px;
		width: 200px !important;
		float: right !important;
		margin-left: 5px !important;
	}

	.fx-border {
		border: 1px solid #e0e0e0;
		padding: 5px 5px;
		box-shadow: 0 4px 24px 0 rgb(34 41 47 / 10%);
		background: #f4f8ff;
		position: relative;
		margin-bottom: 10px;
	}

	.jsr-no {
		border: 1px dashed #4a4949;
		display: inline-block;
		padding: 0.3em 0.44em;
		font-weight: 700;
		line-height: 15px;
		padding-right: 0.7em;
		padding-left: 0.7em;
		border-radius: 10rem;
		position: absolute;
		left: -10px;
		top: -10px;
		background: #4a4949;
		color: #fff;
		font-size: 12px;
	}

	.select2-results__option[aria-selected] {
		cursor: pointer;
		font-weight: 800;
	}

	.pl-0 {
		padding-left: 0px !important;
	}

	.pr-0 {
		padding-right: 0px !important;
	}

	#requirement_area .flex-grow-1 .form-group label {
		font-size: 12px;
	}

	.mn-table td {
		padding: 0px 10px !important;
	}

	.mn-table td .td-blank {
		margin: 5px !important;
	}

	input {
		height: 30px;
	}

	#requirement_area .select2-container--default .select2-selection--single .select2-selection__rendered {
		color: #444;
		line-height: normal;
		font-weight: 800;
	}

	.select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 30px;
		min-height: 30px;
		line-height: normal;
	}

	.select2-container--default .select2-selection--single {
		height: 30px;
		min-height: 30px;
		line-height: normal;
	}

	.select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 26px;
		position: absolute;
		top: -5px;
		right: 1px;
		width: 20px;
	}

	.f-title {
		border-bottom: 1px dashed #3d3d3d;
		width: max-content;
		margin-top: 10px;
	}

	.m-acc .m-stock-avl {
		position: absolute;
		right: 0;
	}

	.m-stock-avl label {
		border: 1px dashed #037e03;
		color: #037e03;
		padding: 2px 5px;
		margin-top: 5px;
	}
</style>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/sales_order/edit_post/' . $id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>
        <div class="row">
          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Order No <span class="required">*</span></label>
              <input type="text" class="form-control" placeholder="Order No" name="order_no"
                value="<?php echo $data['order_no'];?>" readonly>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Refrence Order No </label>
              <input type="text" class="form-control" placeholder="Enter Order No" name="refrence_no" value="<?php echo $data['refrence_no'];?>">
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <div class="form-group">
              <label>Date <span class="required">*</span></label>
              <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>"
                value="<?php echo $data['date'];?>" id="date_picker" readonly>
            </div>
          </div>

          <div class="col-12 col-sm-3 mb-1">
            <label class="form-label" for="state">Customer <span class="required">*</span></label>
            <select class=" form-select select2" name="customer_id" id="customer_id" required>
              <option value="">Select Customer </option>
              <?php foreach($customer_list as $item){?>
              	<option value="<?php echo $item['id'];?>" <?php echo $data['customer_id'] == $item['id'] ? 'selected' : '';?> ><?php echo $item['owner_name'];?></option>
              <?php }?>
            </select>
          </div>

          <div class="col-12 col-sm-3 mb-1 d-none">
            <label class="form-label" for="warehouse_id">Warehouse <span class="required">*</span></label>
            <select class=" form-select select2" name="warehouse_id" id="warehouse_id">
              <option value="0">Select Warehouse</option>
            </select>
          </div>

					<input type="hidden" name="company_id" value="<?php echo $data['company_id']; ?>">
          
          <div class="col-12 col-sm-12 mb-1 mt-1">
            <div class="form-group">
              <label>Narration</label>
              <textarea class="form-control" placeholder="" rows="1" name="narration" id="narration"><?php echo $data['narration'];?></textarea>
            </div>
          </div>

          <div class="col-12 col-sm-12 mb-1">
            <div class="form-group">
              <label>Remark</label>
              <textarea class="form-control" placeholder="" rows="1" name="remark" id="remark"><?php echo $data['remark'];?></textarea>
            </div>
          </div>

          <div class="col-12">
            <div id="requirement_area">
              <?php 
                $i = 1; 
                foreach($data['products'] as $item) { 
              ?>
              <div class="d-block mt-2 element-1 fx-border" id="product_<?php echo $i;?>" data-id="<?php echo $i;?>">
                <b class="jsr-no"><?php echo $i;?></b>
                <div class="flex-grow-1 px-0 ml-15">
                  <div class="row">

                      <div class="col-md-3 pl-0">
                        <input type="hidden" name="x_value[]" id="x_value_<?php echo $i;?>" value="<?php echo $i;?>">
                        <div class="form-group">
                          <label>Select Product (Stock)<span class="required">*</span></label>
                          <select class="form-control select2 product_id" name="product_id[]" id="product_id_<?php echo $i;?>" data-toggle="select2" onchange="get_details_by_product(this.value,'<?php echo $i;?>');" data-selected="<?php echo $item['product_id'];?>" required>
                            <option value="">Select Product</option>
                            <?php foreach($products_list as $p_item){?>
                              <option value="<?php echo $p_item['id'];?>" <?php echo $p_item['id'] == $item['product_id'] ? 'selected' : '';?>><?php echo $p_item['name'];?></option>
                            <?php }?>
                          </select>
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>Qty <span class="required">*</span></label>
                          <input type="number" step="any" id="quantity_<?php echo $i;?>" name="quantity[]" placeholder="Qty"
                            onkeyup="calculate_amt('<?php echo $i;?>')" value="<?php echo $item['qty'];?>" class="form-control"
                            required="">
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>Amount <span class="required">*</span></label>
                          <div class="input-group">
                            <input type="number" step="any" id="master_amount_<?php echo $i;?>" name="master_amount[]"
                              onkeyup="calculate_amt('<?php echo $i;?>')" value="<?php echo $item['master_amount'] ?? $item['total_amount'];?>" class="form-control">
                            <span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('<?php echo $i;?>')"><i class="fa fa-history px-1"></i></span>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>Bill Amt <span class="required">*</span></label>
                          <input type="number" step="any" id="bill_amount_<?php echo $i;?>" name="bill_amount[]"
                            onkeyup="markManual('<?php echo $i;?>'); calculate_amt('<?php echo $i;?>')" value="<?php echo $item['bill_amount'] ?? $item['white_amount'];?>" class="form-control"
                            data-manual="<?php echo ($item['black_amount'] != 0) ? 'true' : 'false'; ?>">
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>GST % <span class="required">*</span></label>
                          <input type="number" step="any" id="gst_<?php echo $i;?>" name="gst[]" onkeyup="calculate_amt('<?php echo $i;?>')" value="<?php echo $item['gst'];?>" class="form-control">
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>GST Amt</label>
                          <input type="number" step="any" id="gst_amount_<?php echo $i;?>" name="gst_amount[]" value="<?php echo $item['gst_amount'] ?? 0;?>" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>Bill Total</label>
                          <input type="number" step="any" id="bill_total_<?php echo $i;?>" name="bill_total[]"
                              value="<?php echo $item['bill_total'] ?? $item['white_total'];?>" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>Black Amt</label>
                          <input type="number" step="any" id="black_amount_<?php echo $i;?>" name="black_amount[]" value="<?php echo $item['black_amount'];?>" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>Final Total</label>
                          <input type="number" step="any" id="final_total_<?php echo $i;?>" name="final_total[]" value="<?php echo $item['final_total'] ?? ($item['white_total'] + $item['black_amount']);?>" class="form-control" readonly>
                          <input type="hidden" id="available_<?php echo $i;?>" name="available[]" value="<?php echo $item['available'];?>">
                        </div>
                      </div>

                      <div class="col-md-1 pl-0">
                        <div class="form-group">
                          <label>&nbsp;</label><br />
                          <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light"
                            style="" name="button" onclick="removeRequirement(this,'<?php echo $i;?>')"> <i class="fa fa-times"
                              aria-hidden="true"></i> </button>
                        </div>
                      </div>

                      <!-- Batch assignment restricted for salesmen -->

                  </div>
                </div>
              </div>
              <?php $i++; } ?>

            </div>
          </div>

          <center>
            <div class="col-md-12  pl-0 m-auto">
              <button type="button" class="btn btn-outline-primary waves-effect" onclick="appendRequirement()"> 
                <i class="fa fa-plus" aria-hidden="true"></i> Add New Product
              </button>
            </div>
          </center>

          <div class="col-12 col-sm-12 mb-1">
            <div class="table-responsive">
              <div class="col-lg-12 no-pad">

                <table class="table table-striped table-bordered mn-table mt-1">
                  <tbody>
                    <tr>
                      <td colspan="4" class="text-right" style="width:80%">
                        <label style="float:right;display: contents;">Total Basic Amount</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="basic_value" id="basic_value"
                            value="<?php echo $data['basic_value'];?>" placeholder="Basic Value" class="form-control" readonly></p>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right align-middle">
                        <div class="d-flex flex-column align-items-end">
                          <span class="mb-0 text-capitalize">Basic Net Sales Value (Exclu. GST)</span>
                          <select class="form-control " name="gst_type" id="gst_type" style="width : 200px !important;float:right !important">
                            <option value="">Select GST</option>
                            <option value="Central GST / State GST">Central GST / State GST</option>
                            <option value="IGST">IGST</option>
                          </select>
                        </div>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="net_sales_value_1"
                            id="net_sales_value_1" value="<?php echo $data['net_sales_value_1'];?>" placeholder="Basic Net Sales Value (Excl. GST)"
                            class="form-control" readonly></p>
                      </td>
                    </tr>

                    <tr class="hidden" id="cgst_gst">
                      <th colspan="4" class="text-right align-middle">
                        <b class="mb-0 text-capitalize">Add : IN: Central GST</b>
                        <input type="text"
                          oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                          name="cgst_per" id="cgst_per" onkeyup="recalculate()" placeholder="in(%)" value="<?php echo $data['cgst_per'];?>"
                          class="form-control dis-input">
                      </th>
                      <th colspan="1">
                        <p class="td-blank"><b><input type="number" step="any" name="central_gst" id="central_gst"
                              value="<?php echo $data['central_gst'];?>" placeholder="Add : IN: Central GST" class="form-control" readonly></b></p>
                      </th>
                    </tr>

                    <tr class="hidden" id="sgst_gst">
                      <th colspan="4" class="text-right align-middle">
                        <b class="mb-0 text-capitalize">Add : IN: State GST</b>
                        <input type="text"
                          oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                          name="sgst_per" id="sgst_per" onkeyup="recalculate()" placeholder="in(%)" value="<?php echo $data['sgst_per'];?>"
                          class="form-control dis-input" readonly>
                      </th>
                      <th colspan="1">
                        <p class="td-blank"><b><input type="number" step="any" name="state_gst" id="state_gst" value="<?php echo $data['state_gst'];?>"
                              placeholder="Add : IN: State GST" class="form-control" readonly></b></p>
                      </th>
                    </tr>

                    <tr class="hidden" id="igst_gst">
                      <th colspan="4" class="text-right align-middle">
                        <b class="mb-0 text-capitalize">Add : IN: IGST</b>
                        <input type="text"
                          oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                          name="igst_per" id="igst_per" onkeyup="recalculate()" placeholder="in(%)" value="<?php echo $data['igst_per'];?>"
                          class="form-control dis-input">
                      </th>
                      <th colspan="1">
                        <p class="td-blank"><b><input type="number" step="any" name="igst" id="igst" value="<?php echo $data['igst'];?>"
                              placeholder="Add : IN: IGST" class="form-control" readonly></b></p>
                      </th>
                    </tr>

                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Net Sales Value (Inc. GST)</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="net_sales_value_2"
                            id="net_sales_value_2" value="<?php echo $data['net_sales_value_2'];?>" placeholder="Basic Net Sales Value (Inc. GST)"
                            class="form-control" readonly></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Add : Other Charges</label>
                        <input type="text" step="any" name="other_charges_name" id="other_charges_name" value="<?php echo $data['other_charges_name'];?>"
                          placeholder="Charge Name" class="form-control dis-input-1">
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="other_charges_amount"
                            id="other_charges_amount" placeholder="Charge Amount" class="form-control" value="<?php echo $data['other_charges_amount'];?>"
                            onkeyup="recalculate()"></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Round Of</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="round_of" id="round_of"
                            placeholder="Round Of" class="form-control" value="<?php echo $data['round_of'];?>" onkeyup="recalculate()"></p>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-right">
                        <label>Total Order Value</label>
                      </td>
                      <td colspan="1">
                        <p class="td-blank"><input type="number" step="any" name="grand_total" id="grand_total"
                            placeholder="" class="form-control" value="<?php echo $data['grand_total'];?>" readonly></p>
                      </td>
                    </tr>
                  </tbody>
                </table>

              </div>
            </div>
          </div>

          <div class="col-12">
            <button type="submit"
              class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify"
              name="btn_verify"><?php echo get_phrase('submit'); ?></button>
          </div>
        </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script>
    function markManual(index) {
        $('#bill_amount_' + index).attr('data-manual', 'true');
    }

    function calculate_amt(index) {
        var qty = Number($('#quantity_' + index).val()) || 0;
        var master_amt = Number($('#master_amount_' + index).val()) || 0;
        var bill_amt_el = $('#bill_amount_' + index);
        var is_manual = bill_amt_el.attr('data-manual') === 'true';

        var gross_total = qty * master_amt;

        if (!is_manual) {
            bill_amt_el.val(gross_total.toFixed(2));
        }

        var bill_amt = Number(bill_amt_el.val()) || 0;
        var gst_per = Number($('#gst_' + index).val()) || 0;
        var available = Number($('#available_' + index).val()) || 0;

        if (qty > available && available > 0) {
            Swal.fire({
                title: "Warning!",
                text: "Quantity (" + qty + ") exceeds available stock (" + available + ")",
                icon: "warning"
            });
        }

        var black_amt = gross_total - bill_amt;
        var gst_amt = (bill_amt * gst_per) / 100;
        var bill_total = bill_amt + gst_amt;
        var final_total = bill_total + black_amt;

        $('#black_amount_' + index).val(black_amt.toFixed(2));
        $('#gst_amount_' + index).val(gst_amt.toFixed(2));
        $('#bill_total_' + index).val(bill_total.toFixed(2));
        $('#final_total_' + index).val(final_total.toFixed(2));

        recalculate();
    }

    function subtotal_cal() {
        var total_element = $(".element-1").length;
        var grand_total = 0;
        var total_bill_amount = 0;
        var total_gst_amount = 0;
        var total_black_amount = 0;

        $('.element-1').each(function() {
            var id = $(this).data('id');
            var bill_total = Number($('#bill_total_' + id).val()) || 0;
            var black_amt = Number($('#black_amount_' + id).val()) || 0;
            var bill_amt = Number($('#bill_amount_' + id).val()) || 0;
            var gst_amt = Number($('#gst_amount_' + id).val()) || 0;

            total_bill_amount += bill_amt;
            total_gst_amount += gst_amt;
            total_black_amount += black_amt;
            grand_total += (bill_total + black_amt);
        });

        $("#basic_value").val(total_bill_amount.toFixed(2));
        $("#net_sales_value_1").val(total_bill_amount.toFixed(2));
        
        var gst_type = $('#gst_type').val();
        if (gst_type == 'IGST') {
            $('#igst').val(total_gst_amount.toFixed(2));
            $('#central_gst').val(0);
            $('#state_gst').val(0);
        } else {
            $('#central_gst').val((total_gst_amount / 2).toFixed(2));
            $('#state_gst').val((total_gst_amount / 2).toFixed(2));
            $('#igst').val(0);
        }

        $("#net_sales_value_2").val((total_bill_amount + total_gst_amount).toFixed(2));

        var other_charges_amount = parseFloat($("#other_charges_amount").val()) || 0;
        var round_of = parseFloat($("#round_of").val()) || 0;

        var final_grand_total = total_bill_amount + total_gst_amount + total_black_amount + other_charges_amount + round_of;
        $('#grand_total').val(final_grand_total.toFixed(2));
    }

  function recalculate() {
    subtotal_cal();
  };

  function change_gst(value) {
    let cgst_gst = document.querySelector("#cgst_gst");
    let sgst_gst = document.querySelector("#sgst_gst");
    let igst_gst = document.querySelector("#igst_gst");

    if (value == "Central GST / State GST") {
      sgst_gst.classList.remove('hidden')
      cgst_gst.classList.remove('hidden')
      igst_gst.classList.add('hidden')
    } else if (value == "IGST") {
      sgst_gst.classList.add('hidden')
      cgst_gst.classList.add('hidden')
      igst_gst.classList.remove('hidden')
    } else {
      sgst_gst.classList.add('hidden')
      cgst_gst.classList.add('hidden')
      igst_gst.classList.add('hidden')
    }
  }

  function appendRequirement() {
    var customer_id = $('#customer_id').find(":selected").val();

    if (customer_id == '') {
      Swal.fire({
        title: "Error!",
        text: "Please Select Customer !!",
        icon: "error"
      });
    } else {
      var last_row = $("#requirement_area .element-1:last");
      var nextindex = 1;
      if (last_row.length > 0) {
        var lastid = last_row.attr("id");
        var split_id = lastid.split("_");
        nextindex = Number(split_id[1]) + 1;
        
        var prev_product = $('#product_id_' + split_id[1]).val();
        if (prev_product == '') {
          Swal.fire({
            title: "Error!",
            text: "Please Select Previous Product !!",
            icon: "error"
          });
          return;
        }
      }

      $(".loader").show();
      $('#requirement_area').append(`
          <div class="d-block mt-2 element-1 fx-border" id="product_${nextindex}" data-id="${nextindex}">
            <b class="jsr-no">${nextindex}</b>
            <div class="flex-grow-1 px-0 ml-15">
              <div class="row">
                <div class="col-md-3 pl-0">
                  <input type="hidden" name="x_value[]" id="x_value_${nextindex}" value="${nextindex}">
                  <div class="form-group">
                    <label>Select Product (Stock)<span class="required">*</span></label>
                    <select class="form-control select2 product_id" name="product_id[]" id="product_id_${nextindex}" onchange="get_details_by_product(this.value,'${nextindex}');" required>
                      <option value="">Select Product</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>Qty <span class="required">*</span></label>
                    <input type="number" step="any" id="quantity_${nextindex}" name="quantity[]" placeholder="Qty" value="1"
                      class="form-control" onkeyup="calculate_amt('${nextindex}')" required>
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>Amount <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="number" step="any" id="master_amount_${nextindex}" name="master_amount[]" class="form-control" onkeyup="calculate_amt('${nextindex}')">
                        <span class="input-group-text p-0" style="cursor:pointer" onclick="showPriceHistory('${nextindex}')"><i class="fa fa-history px-1"></i></span>
                    </div>
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>Bill Amt <span class="required">*</span></label>
                    <input type="number" step="any" id="bill_amount_${nextindex}" name="bill_amount[]" class="form-control" 
                      onkeyup="markManual('${nextindex}'); calculate_amt('${nextindex}')" data-manual="false">
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>GST % <span class="required">*</span></label>
                    <input type="number" step="any" id="gst_${nextindex}" name="gst[]" class="form-control" onkeyup="calculate_amt('${nextindex}')">
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>GST Amt</label>
                    <input type="number" step="any" id="gst_amount_${nextindex}" name="gst_amount[]" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>Bill Total</label>
                    <input type="number" step="any" id="bill_total_${nextindex}" name="bill_total[]" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>Black Amt</label>
                    <input type="number" step="any" id="black_amount_${nextindex}" name="black_amount[]" class="form-control" readonly>
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <label>Final Total</label>
                    <input type="number" step="any" id="final_total_${nextindex}" name="final_total[]" class="form-control" readonly>
                    <input type="hidden" id="available_${nextindex}" name="available[]" value="0">
                  </div>
                </div>

                <div class="col-md-1 pl-0">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light" onclick="removeRequirement(this,${nextindex})">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Batch assignment logic removed for salesmen -->
              </div>
            </div>
          </div>
        `);
        $(".select2").select2();
        var company_id = $('[name="company_id"]').val();
        load_products_by_company(company_id, nextindex, true);
    }
  }

    function load_products_by_company(company_id, index, shouldScroll = false, selectedValue = null, skipRecalculate = false) {
        $.ajax({
            type: "POST",
            url: "<?php echo base_url()?>inventory/get_product_by_company",
            data: { company_id: company_id },
            success: function(res) {
                console.log("Products Loaded for index " + index + ":", res);
                var select = $('#product_id_' + index);
                select.html('<option value="">Select Product</option>' + res);
                
                if (selectedValue) {
                    select.val(selectedValue);
                    if (!skipRecalculate) {
                        select.trigger('change');
                    } else {
                        get_details_by_product(selectedValue, index, true);
                    }
                }
                select.select2();
                $(".loader").fadeOut("slow");
                
                if (shouldScroll && $("#product_" + index).length) {
                    $('html, body').animate({
                        scrollTop: $("#product_" + index).offset().top
                    }, 300);
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                $(".loader").fadeOut("slow");
            }
        });
    }

    function get_details_by_product(product_id, index, skipPriceUpdate = false) {
        var company_id = $('[name="company_id"]').val();
        if(!product_id) return;

        $.ajax({
            type: "POST",
            url: "<?php echo base_url()?>inventory/get_qty_by_product_company",
            data: { company_id: company_id, product_id: product_id },
            success: function(res) {
                if(res.status == 200) {
                    $('#available_' + index).val(res.quantity);
                    if (!skipPriceUpdate) {
                        $('#gst_' + index).val(res.tax);
                        $('#master_amount_' + index).val(res.rate);
                        calculate_amt(index);
                    }
                } else {
                    alert(res.message);
                }
            }
        });
    }

    function removeRequirement(e, index) {
        $(e).closest('.element-1').remove();
        recalculate();
    }

    function showPriceHistory(index) {
        var product_id = $('#product_id_' + index).val();
        var customer_id = $('#customer_id').val();
        
        if(!product_id || !customer_id) {
            Swal.fire("Error", "Please select product and customer first", "error");
            return;
        }

        $.ajax({
            url: "<?php echo base_url()?>inventory/get_last_selling_price",
            type: "POST",
            data: { customer_id: customer_id, product_id: product_id },
            success: function(res) {
                $('#priceHistoryModalContent').html(res);
                $('#priceHistoryModal').modal('show');
            }
        });
    }

    $(document).ready(function() {
        var company_id = $('[name="company_id"]').val();
        $('.product_id').each(function() {
            var full_id = $(this).attr('id');
            var split_id = full_id.split('_');
            var id = split_id[split_id.length - 1];
            var current_val = $(this).attr('data-selected') || $(this).val();
            load_products_by_company(company_id, id, false, current_val, true);
        });

        // Date is always read-only for salesman in this view
        $('#date_picker').prop('readonly', true);
        $('#date_picker').on('click', function(e){ e.preventDefault(); });
        
        // Final call to ensure calculations are correct on load
        recalculate();
    });
</script>

<!-- Price History Modal -->
<div class="modal fade" id="priceHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Last Selling Prices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="priceHistoryModalContent">
            </div>
        </div>
    </div>
</div>
