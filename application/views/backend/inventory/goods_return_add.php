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
	#charges_area tr .btn-add-charge {
		display: none;
	}
	#charges_area tr:last-child .btn-add-charge {
		display: inline-flex;
	}
	.compact-table th, .compact-table td {
		padding: 4px !important;
		vertical-align: middle;
	}
	.compact-table .form-control {
		height: 32px;
		min-height: 32px;
		padding: 4px 8px;
		font-size: 13px;
		border-radius: 3px;
	}
	.hidden {
		display: none !important;
	}
</style>
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card" >
      <div class="card-body py-1 my-0">
            
          <?php echo form_open('inventory/goods_return/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <input type="hidden" name="excel_id" id="excel_id" value="0">
          <input type="hidden" name="type" id="type" value="">
          <input type="hidden" name="order_no" id="order_no" value="">
          <div class="row">
            
            <div class="col-12 col-sm-3 mb-1">
              <label class="form-label" for="customer_id">Customer <span class="required">*</span></label>
              <select class="form-select select2" name="customer_id" id="customer_id" required>
                <option value="">Select Customer </option>
                <?php foreach($customer_list as $item){?>
 					<option value="<?php echo $item['id'];?>"><?php echo $item['company_name'];?></option>
                <?php }?>
              </select>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <label class="form-label" for="product_id_select">Product <span class="required">*</span></label>
              <select class="form-select select2" name="product_id_select[]" id="product_id_select" multiple="multiple" data-placeholder="Select Products" required>
                <?php foreach($product_list as $item){?>
 					<option value="<?php echo $item->id;?>"><?php echo htmlspecialchars($item->name . ' (' . $item->item_code . ')');?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label class="form-label" for="credit_note_no">Credit Note No<span class="required">*</span></label>
                    <input type="text" name="credit_note_no" id="credit_note_no" class="form-control" placeholder="Enter Credit Note No" required>
                </div>
            </div>

            <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label class="form-label" for="date_picker">Credit Note Date <span class="required">*</span></label>
                    <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>" value="<?php echo date('Y-m-d');?>" id="date_picker">
                </div>
            </div>
            
 			<div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                    <label class="form-label" for="reason">Reason<span class="required">*</span></label>
                    <textarea class="form-control" placeholder="Enter reason" rows="1" name="reason" id="reason" required></textarea>
                </div>
            </div>
            
            <div class="col-12" id="order_details_container" style="display: none;">
                <input type="hidden" name="white_total" id="white_total" value="0.00">
                <input type="hidden" name="gst_total_amt" id="gst_total_amt" value="0.00">
                <input type="hidden" name="black_total" id="black_total" value="0.00">
                <input type="hidden" name="final_total" id="final_total" value="0.00">
                <div id="order_details_content"></div>
            </div>
            
            <div class="col-12 text-center mt-1">
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
    var g_filteredWhite = {};
    var g_filteredBlack = {};

    function checkForm(form) {
        var selectedSource = $('.order-source-radio:checked').length;
        var currentType = $('#type').val();
        if (!currentType && selectedSource === 0) {
            Swal.fire({
                title: "Error!",
                text: "Please select an Invoice or Order from the dropdown!",
                icon: "error"
            });
            return false;
        }

        var totalQty = 0;
        $('.qty-input').each(function() {
            if (!$(this).prop('disabled')) {
                totalQty += parseFloat($(this).val()) || 0;
            }
        });

        if (totalQty <= 0) {
            Swal.fire({
                title: "Error!",
                text: "Please enter return quantity for the selected item!",
                icon: "error"
            });
            return false;
        }
        return true;
    }

    $(document).ready(function() {
        $('#product_id_select').select2({
            placeholder: "Select Products"
        });

        $('#customer_id, #product_id_select').on('change', function() {
            loadProductReturns();
        });
    });

    function loadProductReturns() {
        var customerId = $('#customer_id').val();
        var selectedProductIds = $('#product_id_select').val();
        if (typeof selectedProductIds === 'string') {
            selectedProductIds = selectedProductIds ? [selectedProductIds] : [];
        }
        var $detailsContainer = $('#order_details_container');

        $detailsContainer.hide().find('#order_details_content').html('');
        $('#type').val('');
        $('#order_no').val('');
        g_filteredWhite = {};
        g_filteredBlack = {};

        if (!customerId || !selectedProductIds || selectedProductIds.length === 0) {
            return;
        }

        $(".loader").show();

        var ajaxRequests = selectedProductIds.map(function(pId) {
            return $.ajax({
                url: '<?php echo base_url("inventory/goods-return/get-customer-product-returns"); ?>',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    customer_id: customerId,
                    product_id: pId
                }
            });
        });

        $.when.apply($, ajaxRequests).done(function() {
            $(".loader").fadeOut("slow");

            var responses = (selectedProductIds.length === 1) ? [arguments] : Array.prototype.slice.call(arguments);
            var allWhite = [];
            var allBlack = [];

            responses.forEach(function(resp) {
                var resData = resp[0] || resp;
                if (resData && resData.status === 'success') {
                    if (resData.white && resData.white.length > 0) {
                        allWhite = allWhite.concat(resData.white);
                    }
                    if (resData.black && resData.black.length > 0) {
                        allBlack = allBlack.concat(resData.black);
                    }
                }
            });

            renderReturnSelectionLayout(allWhite, allBlack, selectedProductIds);
            $detailsContainer.show();
            calculateGrandTotals();
        }).fail(function() {
            $(".loader").fadeOut("slow");
            Swal.fire({
                title: "Error!",
                text: "An error occurred while loading details.",
                icon: "error"
            });
        });
    }

    function renderReturnSelectionLayout(whiteList, blackList, selectedProductIds) {
        var numSelectedProducts = selectedProductIds ? selectedProductIds.length : 1;

        // Group White Section by order_id
        var groupedWhite = {};
        if (whiteList && whiteList.length > 0) {
            whiteList.forEach(function(item) {
                var key = item.order_id;
                if (!groupedWhite[key]) {
                    groupedWhite[key] = {
                        order_id: item.order_id,
                        invoice_no: item.invoice_no,
                        order_no: item.order_no,
                        date: item.date,
                        customer_name: item.customer_name,
                        batches: [],
                        product_ids: {}
                    };
                }
                groupedWhite[key].batches.push(item);
                groupedWhite[key].product_ids[String(item.product_id)] = true;
            });
        }

        // Group Black Section by order_id
        var groupedBlack = {};
        if (blackList && blackList.length > 0) {
            blackList.forEach(function(item) {
                var key = item.order_id;
                if (!groupedBlack[key]) {
                    groupedBlack[key] = {
                        order_id: item.order_id,
                        order_no: item.order_no,
                        date: item.date,
                        customer_name: item.customer_name,
                        batches: [],
                        product_ids: {}
                    };
                }
                groupedBlack[key].batches.push(item);
                groupedBlack[key].product_ids[String(item.product_id)] = true;
            });
        }

        // Filter groupedWhite to only include orders/invoices that contain ALL selected products
        g_filteredWhite = {};
        Object.keys(groupedWhite).forEach(function(key) {
            var group = groupedWhite[key];
            var count = 0;
            if (selectedProductIds && selectedProductIds.length > 0) {
                selectedProductIds.forEach(function(pid) {
                    if (group.product_ids[String(pid)]) {
                        count++;
                    }
                });
            }
            if (count === numSelectedProducts) {
                g_filteredWhite[key] = group;
            }
        });

        // Filter groupedBlack to include orders that contain AT LEAST ONE of the selected products
        g_filteredBlack = {};
        Object.keys(groupedBlack).forEach(function(key) {
            var group = groupedBlack[key];
            var count = 0;
            if (selectedProductIds && selectedProductIds.length > 0) {
                selectedProductIds.forEach(function(pid) {
                    if (group.product_ids[String(pid)]) {
                        count++;
                    }
                });
            }
            if (count > 0) {
                g_filteredBlack[key] = group;
            }
        });

        var html = '';

        // White Section Header & Dropdown
        html += `
            <div class="divider divider-left divider-primary mb-1 mt-2">
                <div class="divider-text text-primary font-weight-bold"><i class="feather icon-file-text"></i> White Section (Invoices)</div>
            </div>
            <div class="row mb-2">
                <div class="col-12 col-md-6">
                    <label class="form-label font-weight-bold" for="invoice_select">Select Invoice</label>
                    <select class="form-select select2" id="invoice_select">
                        <option value="">-- Select Invoice --</option>
        `;
        if (Object.keys(g_filteredWhite).length > 0) {
            Object.values(g_filteredWhite).forEach(function(group) {
                html += `<option value="${group.order_id}">Invoice No: ${group.invoice_no || '-'} (Date: ${group.date})</option>`;
            });
        } else {
            html += `<option value="" disabled>No official invoices found containing all selected products</option>`;
        }
        html += `
                    </select>
                </div>
            </div>
            <div id="invoice_table_container"></div>
        `;

        // Black Section Header & Dropdown
        html += `
            <div class="divider divider-left divider-dark mb-1 mt-2">
                <div class="divider-text text-dark font-weight-bold"><i class="feather icon-file-text"></i> Black Section (Orders)</div>
            </div>
            <div class="row mb-2">
                <div class="col-12 col-md-6">
                    <label class="form-label font-weight-bold" for="order_select">Select Order(s)</label>
                    <select class="form-select select2" id="order_select" multiple="multiple" data-placeholder="Select Orders">
        `;
        if (Object.keys(g_filteredBlack).length > 0) {
            Object.values(g_filteredBlack).forEach(function(group) {
                html += `<option value="${group.order_id}">Order No: ${group.order_no || '-'} (Date: ${group.date})</option>`;
            });
        }
        html += `
                    </select>
                </div>
            </div>
            <div id="order_tables_container"></div>
        `;

        // Other Charges / Expenses Section
        html += `
            <div class="col-12 mt-2">
                <div class="divider divider-left divider-info mb-1">
                    <div class="divider-text text-info font-weight-bold"><i class="feather icon-plus-circle"></i> Other Charges / Expenses</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm compact-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="min-width:200px;">Type / Description</th>
                                <th style="min-width:80px;">GST %</th>
                                <th style="min-width:120px;">Amount</th>
                                <th style="min-width:120px;">Total Amount</th>
                                <th style="min-width:50px;">Act</th>
                            </tr>
                        </thead>
                        <tbody id="charges_area">
                            <tr class="element-charge-1 charge-line-item" id="charge_1" data-id="1">
                                <td>
                                    <select class="form-control select2 charge_id" name="charge_id[]" id="charge_id_1" data-toggle="select2" onchange="get_charge_details(this.value, '1');">
                                        <option value="">Select Charges</option>
                                        <?php if(isset($other_charges) && !empty($other_charges)){ foreach($other_charges as $charge) { ?>
                                            <option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>"><?php echo htmlspecialchars($charge['name']); ?></option>
                                        <?php } } ?>
                                    </select>
                                </td>
                                <td><input type="number" step="any" id="charge_gst_1" name="charge_gst[]" placeholder="GST %" class="form-control charge-input" onkeyup="calculate_charge('1')" onchange="calculate_charge('1')" value="0"></td>
                                <td><input type="number" step="any" id="charge_price_1" name="charge_price[]" placeholder="Amount" class="form-control charge-input" onkeyup="calculate_charge('1')" onchange="calculate_charge('1')" value="0"></td>
                                <td><input type="number" step="any" id="charge_total_1" name="charge_total[]" placeholder="Total Amount" class="form-control" tabindex="-1" readonly value="0.00"></td>
                                <td class="text-center align-middle" style="white-space:nowrap;">
                                    <button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-charge" onclick="appendCharge()"> <i class="feather icon-plus" aria-hidden="true"></i> </button>
                                    <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-charge" onclick="removeCharge(this, 1)"> <i class="feather icon-x" aria-hidden="true"></i> </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        // Grand Total Summary Section
        html += `
            <div class="col-12 mt-2">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mn-table mt-1">
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-right" style="width:80%">
                                    <label style="float:right;display: contents;">Total Bill Amt (Exc GST)</label>
                                </td>
                                <td colspan="1">
                                    <p class="td-blank"><input type="number" step="any" name="basic_value" id="basic_value" value="0.00" placeholder="Total Bill Amt (Exc GST)" class="form-control text-end font-monospace" readonly></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right align-middle">
                                    <div class="d-flex flex-column align-items-end">
                                        <span class="mb-0 text-capitalize">Select GST</span>
                                        <select class="form-control" name="gst_type" id="gst_type" onchange="change_gst(this.value); calculateGrandTotals();" style="width : 200px !important;float:right !important">
                                            <option value="Central GST / State GST" selected>Central GST / State GST</option>
                                            <option value="IGST">IGST</option>
                                        </select>
                                    </div>
                                </td>
                                <td colspan="1">
                                    <div id="cgst_sgst_inputs">
                                        <p class="td-blank mb-25">
                                            <input type="number" step="any" name="central_gst" id="central_gst" value="0.00" placeholder="CGST Amount" class="form-control text-end font-monospace" readonly>
                                        </p>
                                        <p class="td-blank mb-0">
                                            <input type="number" step="any" name="state_gst" id="state_gst" value="0.00" placeholder="SGST Amount" class="form-control text-end font-monospace" readonly>
                                        </p>
                                    </div>
                                    <div id="igst_input" class="hidden">
                                        <p class="td-blank mb-0">
                                            <input type="number" step="any" name="igst" id="igst" value="0.00" placeholder="IGST Amount" class="form-control text-end font-monospace" readonly>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <label>Total Bill Amt (Incl GST)</label>
                                </td>
                                <td colspan="1">
                                    <p class="td-blank"><input type="number" step="any" name="net_sales_value_1" id="net_sales_value_1" value="0.00" placeholder="Total Bill Amt (Incl GST)" class="form-control text-end font-monospace" readonly></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <label>Total Black Amt</label>
                                </td>
                                <td colspan="1">
                                    <p class="td-blank"><input type="number" step="any" name="total_black_amount_summary" id="total_black_amount_summary" value="0.00" placeholder="Total Black Amt" class="form-control text-end font-monospace" readonly></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <label>Final Total</label>
                                </td>
                                <td colspan="1">
                                    <p class="td-blank"><input type="number" step="any" name="net_sales_value_2" id="net_sales_value_2" value="0.00" placeholder="Final Total" class="form-control text-end font-monospace" readonly></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <label>Other Charges / Expenses</label>
                                </td>
                                <td colspan="1">
                                    <p class="td-blank"><input type="number" step="any" name="other_charges_amount" id="other_charges_amount" placeholder="Charge Amount" class="form-control text-end font-monospace" value="0.00" readonly></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <label>Round Off</label>
                                </td>
                                <td colspan="1">
                                    <p class="td-blank"><input type="number" step="any" name="round_of" id="round_of" placeholder="Round Off" class="form-control text-end font-monospace" value="0" onkeyup="calculateGrandTotals()" onchange="calculateGrandTotals()"></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right font-weight-bold">
                                    <label class="text-primary h5 mb-0">Grand Total</label>
                                </td>
                                <td colspan="1">
                                    <p class="td-blank"><input type="number" step="any" name="grand_total" id="grand_total" placeholder="0.00" class="form-control text-end font-monospace font-weight-bold text-primary fs-5" readonly></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        // Banner Card at the bottom
        html += `
            <div class="card mb-2 shadow-none border mt-2">
                <div class="card-body py-1 bg-light-primary d-flex justify-content-between align-items-center" style="background-color: #f0f4fd; padding: 10px;">
                    <h5 class="mb-0 text-primary"><strong>Grand Total:</strong></h5>
                    <h5 class="mb-0 text-primary font-monospace" id="order_grand_total"><strong>0.00</strong></h5>
                </div>
            </div>
        `;

        $('#order_details_content').html(html);

        // Initialize Select2 on the newly rendered dropdowns
        $('#invoice_select').select2({
            placeholder: "-- Select Invoice --",
            allowClear: true
        });
        $('#order_select').select2({
            placeholder: "Select Orders",
            allowClear: true
        });
        $('#charge_id_1').select2({ dropdownParent: $('body') });

        // Event listener for Single Selected Invoice dropdown
        $('#invoice_select').on('change', function() {
            var selectedOrderId = $(this).val();
            if (selectedOrderId && g_filteredWhite[selectedOrderId]) {
                // Clear Order multi-select if Invoice is chosen
                if ($('#order_select').val() && $('#order_select').val().length > 0) {
                    $('#order_select').val(null).trigger('change.select2');
                    $('#order_tables_container').html('');
                }

                var group = g_filteredWhite[selectedOrderId];
                var tableHtml = renderSingleInvoiceTable(group);
                $('#invoice_table_container').html(tableHtml);

                $('#type').val('official');
                $('#order_no').val(group.invoice_no);
            } else {
                $('#invoice_table_container').html('');
                if (!($('#order_select').val() && $('#order_select').val().length > 0)) {
                    $('#type').val('');
                    $('#order_no').val('');
                }
            }
            calculateGrandTotals();
        });

        // Event listener for Multi Selected Order dropdown
        $('#order_select').on('change', function() {
            var selectedOrderIds = $(this).val() || [];
            if (typeof selectedOrderIds === 'string') {
                selectedOrderIds = [selectedOrderIds];
            }
            if (selectedOrderIds.length > 0) {
                // Clear Invoice single-select if Order is chosen
                if ($('#invoice_select').val()) {
                    $('#invoice_select').val(null).trigger('change.select2');
                    $('#invoice_table_container').html('');
                }

                var tableHtml = '';
                var orderNos = [];
                selectedOrderIds.forEach(function(orderId) {
                    if (g_filteredBlack[orderId]) {
                        var group = g_filteredBlack[orderId];
                        orderNos.push(group.order_no);
                        tableHtml += renderSingleOrderTable(group);
                    }
                });
                $('#order_tables_container').html(tableHtml);

                $('#type').val('unofficial');
                $('#order_no').val(orderNos.join(', '));
            } else {
                $('#order_tables_container').html('');
                if (!$('#invoice_select').val()) {
                    $('#type').val('');
                    $('#order_no').val('');
                }
            }
            calculateGrandTotals();
        });
    }

    function renderSingleInvoiceTable(group) {
        var html = `
        <div class="card mb-2 shadow-none border white-invoice-card">
            <div class="card-header bg-light-primary py-50 d-flex justify-content-between align-items-center" style="background-color: #f0f4fd; padding: 10px;">
                <div>
                    <strong>Invoice No:</strong> ${group.invoice_no || '-'} | <strong>Date:</strong> ${group.date}
                </div>
                <div class="form-check form-check-inline ms-auto me-1">
                    <input type="checkbox" name="is_white_to_black" id="is_white_to_black" class="form-check-input" value="1" onchange="toggleReturnAsBlack(this)">
                    <label class="form-check-label font-weight-bold text-danger" for="is_white_to_black"><i class="feather icon-repeat me-25"></i> Return As Black</label>
                </div>
                <div class="form-check form-check-inline" style="display:none;">
                    <input type="radio" name="selected_order_source" class="form-check-input order-source-radio" data-type="official" data-order-no="${group.invoice_no}" id="select_white_${group.order_id}" checked>
                    <label class="form-check-label font-weight-bold text-primary" for="select_white_${group.order_id}">Selected Invoice</label>
                </div>
            </div>
            <div class="table-responsive border-top">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="text-align: left;">Product / Batch Details</th>
                            <th style="width: 90px;">Total Qty</th>
                            <th style="width: 100px;">Received Qty</th>
                            <th style="width: 100px;">Return Qty</th>
                            <th style="width: 100px;">Amount</th>
                            <th style="width: 100px;" class="col-white-field">Bill Amt</th>
                            <th style="width: 110px;" class="col-white-field">Bill Total</th>
                            <th style="width: 80px;" class="col-white-field">GST %</th>
                            <th style="width: 100px;" class="col-white-field">GST Amt</th>
                            <th style="width: 120px; display: none;" class="col-black-field">Black Amt</th>
                            <th style="width: 110px;" class="col-white-field">Black Amt Total</th>
                            <th style="width: 120px;">Total Amt</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        group.batches.forEach(function(item) {
            var maxQty = item.qty - item.return_qty;
            var billAmtVal = (item.bill_amount !== undefined && item.bill_amount !== null && item.bill_amount !== '') ? parseFloat(item.bill_amount).toFixed(2) : parseFloat(item.amount).toFixed(2);
            var gstVal = (item.gst !== undefined && item.gst !== null) ? parseFloat(item.gst).toFixed(2) : '0.00';

            html += `
                <tr class="batch-row" id="batch_row_${item.product_batch_id}" data-id="${item.product_batch_id}">
                    <td style="text-align: left; padding-left: 15px; vertical-align: middle;">
                        <strong>${item.product_name || ''}</strong> ${item.item_code ? '<small class="text-muted">(' + item.item_code + ')</small>' : ''}<br>
                        <small class="text-muted"><i class="feather icon-package me-25"></i> Batch: <strong>${item.batch_no || '-'}</strong></small>
                        <input type="hidden" name="product_id[]" value="${item.product_id}">
                        <input type="hidden" name="product_batch_id[]" value="${item.product_batch_id}">
                        <input type="hidden" name="batch_no[]" value="${item.batch_no || '-'}">
                        <input type="hidden" name="white_qty[]" class="white-qty-hidden-input submit-qty-input" value="0">
                        <input type="hidden" name="black_qty[]" class="black-qty-hidden-input" value="0">
                        <input type="hidden" name="amount[]" class="amount-hidden-input" value="${item.amount}">
                        <input type="hidden" name="white_amt[]" class="white-amt-hidden-input" value="${billAmtVal}">
                        <input type="hidden" name="white_total_row[]" class="white-total-hidden-input" value="0.00">
                        <input type="hidden" name="black_amt[]" class="black-amt-hidden-input" value="0.00">
                        <input type="hidden" name="black_total_row[]" class="black-total-hidden-input" value="0.00">
                        <input type="hidden" name="gst[]" class="gst-hidden-input" value="${gstVal}">
                        <input type="hidden" name="gst_amt[]" class="gst-amt-hidden-input" value="0.00">
                        <input type="hidden" name="final_total_row[]" class="final-total-hidden-input" value="0.00">
                    </td>
                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.qty}</td>
                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.return_qty}</td>
                    <td style="vertical-align: middle;">
                        <input type="number" value="0" min="0" max="${maxQty}" class="form-control form-control-sm text-center qty-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                    </td>
                    <td style="vertical-align: middle;">
                        <input type="number" value="${parseFloat(item.amount).toFixed(2)}" class="form-control form-control-sm text-end amount-input" readonly>
                    </td>
                    <td class="col-white-field" style="vertical-align: middle;">
                        <input type="number" value="${billAmtVal}" step="0.01" class="form-control form-control-sm text-end bill-amt-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                    </td>
                    <td class="text-end font-monospace bill-total-cell col-white-field" style="vertical-align: middle;">0.00</td>
                    <td class="col-white-field" style="vertical-align: middle;">
                        <input type="number" value="${gstVal}" step="0.01" class="form-control form-control-sm text-center gst-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                    </td>
                    <td class="text-end font-monospace gst-amt-cell col-white-field" style="vertical-align: middle;">0.00</td>
                    <td class="col-black-field" style="vertical-align: middle; display: none;">
                        <input type="number" value="${billAmtVal}" step="0.01" class="form-control form-control-sm text-end black-amt-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                    </td>
                    <td class="col-white-field" style="vertical-align: middle;">
                        <input type="number" value="0.00" step="0.01" class="form-control form-control-sm text-end black-total-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                    </td>
                    <td class="text-end font-monospace row-total-cell" style="vertical-align: middle;">0.00</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        </div>
        `;
        return html;
    }

    function toggleReturnAsBlack(chkElem) {
        var isChecked = $(chkElem).is(':checked');
        if (isChecked) {
            $('.col-white-field').hide();
            $('.col-black-field').show();
        } else {
            $('.col-white-field').show();
            $('.col-black-field').hide();
        }
        $('.white-invoice-card .batch-row').each(function() {
            var qtyInput = $(this).find('.qty-input');
            updateRowTotal(qtyInput);
        });
    }

    function renderSingleOrderTable(group) {
        var html = `
        <div class="card mb-2 shadow-none border black-order-card" data-order-no="${group.order_no}">
            <div class="card-header bg-light-secondary py-50 d-flex justify-content-between align-items-center" style="background-color: #f7f7f7; padding: 10px;">
                <div>
                    <strong>Order No:</strong> ${group.order_no || '-'} | <strong>Date:</strong> ${group.date}
                </div>
                <div class="form-check form-check-inline" style="display:none;">
                    <input type="radio" name="selected_order_source" class="form-check-input order-source-radio" data-type="unofficial" data-order-no="${group.order_no}" id="select_black_${group.order_id}" checked>
                </div>
            </div>
            <div class="table-responsive border-top">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="text-align: left;">Product / Batch Details</th>
                            <th style="width: 100px;">Total Qty</th>
                            <th style="width: 100px;">Received Qty</th>
                            <th style="width: 120px;">Return Qty</th>
                            <th style="width: 120px;">Amount</th>
                            <th style="width: 120px;">Black Amt</th>
                            <th style="width: 140px;">Black Amt Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        group.batches.forEach(function(item) {
            var maxQty = item.qty - item.return_qty;
            var blackAmtVal = parseFloat(item.bill_amount || item.black_amount || item.amount || 0).toFixed(2);

            html += `
                <tr class="batch-row" id="batch_row_${item.product_batch_id}" data-id="${item.product_batch_id}">
                    <td style="text-align: left; padding-left: 15px; vertical-align: middle;">
                        <strong>${item.product_name || ''}</strong> ${item.item_code ? '<small class="text-muted">(' + item.item_code + ')</small>' : ''}<br>
                        <small class="text-muted"><i class="feather icon-package me-25"></i> Batch: <strong>${item.batch_no || '-'}</strong></small>
                        <input type="hidden" name="product_id[]" value="${item.product_id}">
                        <input type="hidden" name="product_batch_id[]" value="${item.product_batch_id}">
                        <input type="hidden" name="batch_no[]" value="${item.batch_no || '-'}">
                        <input type="hidden" name="white_qty[]" value="0">
                        <input type="hidden" name="black_qty[]" class="submit-qty-input" value="0">
                        <input type="hidden" name="amount[]" class="amount-hidden-input" value="${item.amount}">
                        <input type="hidden" name="white_amt[]" value="0.00">
                        <input type="hidden" name="white_total_row[]" value="0.00">
                        <input type="hidden" name="black_amt[]" class="black-amt-hidden-input" value="${blackAmtVal}">
                        <input type="hidden" name="black_total_row[]" class="black-total-hidden-input" value="0.00">
                        <input type="hidden" name="gst[]" value="0.00">
                        <input type="hidden" name="gst_amt[]" value="0.00">
                        <input type="hidden" name="final_total_row[]" class="final-total-hidden-input" value="0.00">
                    </td>
                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.qty}</td>
                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.return_qty}</td>
                    <td style="vertical-align: middle;">
                        <input type="number" value="0" min="0" max="${maxQty}" class="form-control form-control-sm text-center qty-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                    </td>
                    <td style="vertical-align: middle;">
                        <input type="number" value="${parseFloat(item.amount).toFixed(2)}" class="form-control form-control-sm text-end amount-input" readonly>
                    </td>
                    <td style="vertical-align: middle;">
                        <input type="number" value="${blackAmtVal}" step="0.01" class="form-control form-control-sm text-end black-amt-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                    </td>
                    <td class="text-end font-monospace black-total-cell row-total-cell" style="vertical-align: middle;">0.00</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        </div>
        `;
        return html;
    }

    function updateRowTotal(input) {
        var $row = $(input).closest('tr');
        var $card = $(input).closest('.card');
        var isWhite = $card.hasClass('white-invoice-card');
        var isBlack = $card.hasClass('black-order-card');

        var returnQty = parseFloat($row.find('.qty-input').val()) || 0;
        var maxQty = parseFloat($row.find('.qty-input').attr('max')) || 0;

        if (returnQty < 0) {
            $row.find('.qty-input').val(0);
            returnQty = 0;
        }
        if (returnQty > maxQty) {
            Swal.fire({
                title: "Limit Exceeded",
                text: "Return quantity cannot exceed " + maxQty,
                icon: "warning"
            });
            $row.find('.qty-input').val(maxQty);
            returnQty = maxQty;
        }

        var amount = parseFloat($row.find('.amount-input').val()) || 0;
        $row.find('.amount-hidden-input').val(amount);

        if (isWhite) {
            var isWhiteToBlack = $('#is_white_to_black').is(':checked');
            if (isWhiteToBlack) {
                var blackAmt = parseFloat($row.find('.black-amt-input').val()) || 0;
                var blackTotal = returnQty * blackAmt;

                $row.find('.row-total-cell').text(blackTotal.toFixed(2));

                $row.find('.white-qty-hidden-input').val(0);
                $row.find('.black-qty-hidden-input').val(returnQty);
                $row.find('.white-amt-hidden-input').val(0);
                $row.find('.white-total-hidden-input').val('0.00');
                $row.find('.gst-hidden-input').val(0);
                $row.find('.gst-amt-hidden-input').val('0.00');
                $row.find('.black-amt-hidden-input').val(blackAmt.toFixed(2));
                $row.find('.black-total-hidden-input').val(blackTotal.toFixed(2));
                $row.find('.final-total-hidden-input').val(blackTotal.toFixed(2));
            } else {
                var billAmt = parseFloat($row.find('.bill-amt-input').val()) || 0;
                var gst = parseFloat($row.find('.gst-input').val()) || 0;
                var manualBlackTotal = parseFloat($row.find('.black-total-input').val()) || 0;

                var billTotal = returnQty * billAmt;
                var gstAmt = (billTotal * gst) / 100;
                var finalTotal = billTotal + gstAmt + manualBlackTotal;

                $row.find('.bill-total-cell').text(billTotal.toFixed(2));
                $row.find('.gst-amt-cell').text(gstAmt.toFixed(2));
                $row.find('.row-total-cell').text(finalTotal.toFixed(2));

                $row.find('.white-qty-hidden-input').val(returnQty);
                $row.find('.black-qty-hidden-input').val(0);
                $row.find('.white-amt-hidden-input').val(billAmt);
                $row.find('.white-total-hidden-input').val(billTotal.toFixed(2));
                $row.find('.gst-hidden-input').val(gst);
                $row.find('.gst-amt-hidden-input').val(gstAmt.toFixed(2));
                $row.find('.black-amt-hidden-input').val('0.00');
                $row.find('.black-total-hidden-input').val(manualBlackTotal.toFixed(2));
                $row.find('.final-total-hidden-input').val(finalTotal.toFixed(2));
            }
        } else if (isBlack) {
            var blackAmt = parseFloat($row.find('.black-amt-input').val()) || 0;
            var blackTotal = returnQty * blackAmt;

            $row.find('.submit-qty-input').val(returnQty);
            $row.find('.black-total-cell').text(blackTotal.toFixed(2));
            $row.find('.row-total-cell').text(blackTotal.toFixed(2));

            $row.find('.black-amt-hidden-input').val(blackAmt);
            $row.find('.black-total-hidden-input').val(blackTotal.toFixed(2));
            $row.find('.final-total-hidden-input').val(blackTotal.toFixed(2));
        }

        calculateGrandTotals();
    }

    function get_charge_details(val, index) {
        if (val == "") {
            $('#charge_gst_' + index).val(0);
            $('#charge_price_' + index).val(0);
            calculate_charge(index);
        } else {
            var option = $('#charge_id_' + index).find('option:selected');
            var gst = option.data('gst') || 0;
            var price = option.data('price') || 0;
            $('#charge_gst_' + index).val(gst);
            $('#charge_price_' + index).val(price);
            calculate_charge(index);
        }
    }

    function calculate_charge(index) {
        var charge_id = $('#charge_id_' + index).val();
        var gst = parseFloat($('#charge_gst_' + index).val()) || 0;
        var price = parseFloat($('#charge_price_' + index).val()) || 0;

        if (charge_id == "" && (gst > 0 || price > 0)) {
            Swal.fire({
                title: "Error!",
                text: "Please select the charges first",
                icon: "error"
            });
            $('#charge_gst_' + index).val(0);
            $('#charge_price_' + index).val(0);
            $('#charge_total_' + index).val('0.00');
            calculateGrandTotals();
            return;
        }

        var total = price + (price * gst / 100);
        $('#charge_total_' + index).val(total.toFixed(2));
        calculateGrandTotals();
    }

    function appendCharge() {
        var last_row = $("#charges_area .charge-line-item:last");
        var nextindex = 1;
        if (last_row.length > 0) {
            var currentId = last_row.data("id") || 0;
            nextindex = parseInt(currentId) + 1;

            var prev_charge = $('#charge_id_' + currentId).val();
            if (prev_charge == '') {
                Swal.fire({
                    title: "Error!",
                    text: "Please select previous charge !!",
                    icon: "error"
                });
                return;
            }
        }

        var optionsHtml = '<option value="">Select Charges</option>';
        <?php if(isset($other_charges) && !empty($other_charges)){ foreach($other_charges as $charge){ ?>
        optionsHtml += '<option value="<?php echo $charge['id']; ?>" data-gst="<?php echo $charge['gst']; ?>" data-price="<?php echo $charge['price']; ?>"><?php echo htmlspecialchars($charge['name']); ?></option>';
        <?php } } ?>

        $('#charges_area').append(`
            <tr class="element-charge-${nextindex} charge-line-item" id="charge_${nextindex}" data-id="${nextindex}">
                <td>
                    <select class="form-control select2 charge_id" name="charge_id[]" id="charge_id_${nextindex}" data-toggle="select2" onchange="get_charge_details(this.value, '${nextindex}');">
                        ${optionsHtml}
                    </select>
                </td>
                <td><input type="number" step="any" id="charge_gst_${nextindex}" name="charge_gst[]" placeholder="GST %" class="form-control charge-input" onkeyup="calculate_charge('${nextindex}')" onchange="calculate_charge('${nextindex}')" value="0"></td>
                <td><input type="number" step="any" id="charge_price_${nextindex}" name="charge_price[]" placeholder="Amount" class="form-control charge-input" onkeyup="calculate_charge('${nextindex}')" onchange="calculate_charge('${nextindex}')" value="0"></td>
                <td><input type="number" step="any" id="charge_total_${nextindex}" name="charge_total[]" placeholder="Total Amount" class="form-control" tabindex="-1" readonly value="0.00"></td>
                <td class="text-center align-middle" style="white-space:nowrap;">
                    <button type="button" class="btn btn-primary btn-sm waves-effect waves-float waves-light btn-add-charge" onclick="appendCharge()"> <i class="feather icon-plus" aria-hidden="true"></i> </button>
                    <button type="button" class="btn btn-danger btn-sm waves-effect waves-float waves-light btn-remove-charge" onclick="removeCharge(this, ${nextindex})"> <i class="feather icon-x" aria-hidden="true"></i> </button>
                </td>
            </tr>
        `);

        $('#charge_id_' + nextindex).select2({ dropdownParent: $('body') });
    }

    function removeCharge(element, index) {
        if(document.querySelector('#charges_area') && document.querySelector('#charges_area').children.length > 1){
            $(element).closest('tr').remove();
            calculateGrandTotals();
        } else {
            $('#charge_id_' + index).val("").trigger('change');
            $('#charge_gst_' + index).val(0);
            $('#charge_price_' + index).val(0);
            $('#charge_total_' + index).val('0.00');
            calculateGrandTotals();
        }
    }

    function change_gst(value) {
        let cgstSgstInputs = document.querySelector("#cgst_sgst_inputs");
        let igstInput = document.querySelector("#igst_input");
        if (!cgstSgstInputs || !igstInput) return;

        if (value == "Central GST / State GST") {
            cgstSgstInputs.classList.remove('hidden');
            igstInput.classList.add('hidden');
            cgstSgstInputs.style.display = 'block';
            igstInput.style.display = 'none';
        } else if (value == "IGST") {
            cgstSgstInputs.classList.add('hidden');
            igstInput.classList.remove('hidden');
            cgstSgstInputs.style.display = 'none';
            igstInput.style.display = 'block';
        } else {
            cgstSgstInputs.classList.add('hidden');
            igstInput.classList.add('hidden');
            cgstSgstInputs.style.display = 'none';
            igstInput.style.display = 'none';
        }
    }

    function calculateGrandTotals() {
        var type = $('#type').val();
        var grandWhiteTotal = 0;
        var grandGstTotal = 0;
        var grandBlackTotal = 0;
        var grandFinalTotal = 0;

        if (type === 'official') {
            $('.white-invoice-card').find('.batch-row').each(function() {
                grandWhiteTotal += parseFloat($(this).find('.white-total-hidden-input').val()) || 0;
                grandGstTotal += parseFloat($(this).find('.gst-amt-hidden-input').val()) || 0;
                grandBlackTotal += parseFloat($(this).find('.black-total-hidden-input').val()) || 0;
                grandFinalTotal += parseFloat($(this).find('.final-total-hidden-input').val()) || 0;
            });
        } else if (type === 'unofficial') {
            $('.black-order-card').find('.batch-row').each(function() {
                grandBlackTotal += parseFloat($(this).find('.black-total-hidden-input').val()) || 0;
                grandFinalTotal += parseFloat($(this).find('.final-total-hidden-input').val()) || 0;
            });
        }

        var whiteTotalInclGst = grandWhiteTotal + grandGstTotal;

        // Set summary table values
        $('#basic_value').val(grandWhiteTotal.toFixed(2));
        $('#net_sales_value_1').val(whiteTotalInclGst.toFixed(2));
        $('#total_black_amount_summary').val(grandBlackTotal.toFixed(2));
        $('#net_sales_value_2').val(grandFinalTotal.toFixed(2));

        // GST split
        var gstType = $('#gst_type').val() || 'Central GST / State GST';
        if (gstType === 'IGST') {
            $('#igst').val(grandGstTotal.toFixed(2));
            $('#central_gst').val('0.00');
            $('#state_gst').val('0.00');
        } else {
            var halfGst = grandGstTotal / 2;
            $('#central_gst').val(halfGst.toFixed(2));
            $('#state_gst').val(halfGst.toFixed(2));
            $('#igst').val('0.00');
        }

        // Expenses / Other Charges Total
        var totalChargeAmt = 0;
        $('input[name="charge_total[]"]').each(function() {
            totalChargeAmt += parseFloat($(this).val()) || 0;
        });
        $('#other_charges_amount').val(totalChargeAmt.toFixed(2));

        // Round Off
        var roundOf = parseFloat($('#round_of').val()) || 0;

        // Overall Grand Total
        var overallGrandTotal = grandFinalTotal + totalChargeAmt + roundOf;

        $('#grand_total').val(overallGrandTotal.toFixed(2));
        $('#order_grand_total').html('<strong>' + overallGrandTotal.toFixed(2) + '</strong>');

        $('#white_total').val(grandWhiteTotal.toFixed(2));
        $('#gst_total_amt').val(grandGstTotal.toFixed(2));
        $('#black_total').val(grandBlackTotal.toFixed(2));
        $('#final_total').val(overallGrandTotal.toFixed(2));
    }
</script>