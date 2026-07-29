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
              <select class="form-select select2" name="product_id_select" id="product_id_select" required>
                <option value="">Select Product</option>
                <?php foreach($product_list as $item){?>
 					<option value="<?php echo $item->id;?>"><?php echo htmlspecialchars($item->name . ' (' . $item->item_code . ')');?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label class="form-label" for="date_picker">Date <span class="required">*</span></label>
                    <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>" value="<?php echo date('Y-m-d');?>" id="date_picker">
                </div>
            </div>
            
 			<div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label class="form-label" for="reason">Reason<span class="required">*</span></label>
                    <textarea class="form-control" placeholder="Enter reason" rows="1" name="reason" id="reason" required></textarea>
                </div>
            </div>
            
            <div class="col-12" id="order_details_container" style="display: none;">
                <input type="hidden" name="white_total" id="white_total" value="0.00">
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
    function checkForm(form) {
        var selectedSource = $('.order-source-radio:checked').length;
        if (selectedSource === 0) {
            Swal.fire({
                title: "Error!",
                text: "Please select an Invoice or Order to return from!",
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
        $('#customer_id, #product_id_select').on('change', function() {
            loadProductReturns();
        });
    });

    function loadProductReturns() {
        var customerId = $('#customer_id').val();
        var productId = $('#product_id_select').val();
        var $detailsContainer = $('#order_details_container');

        $detailsContainer.hide().find('#order_details_content').html('');
        $('#type').val('');
        $('#order_no').val('');

        if (!customerId || !productId) {
            return;
        }

        $(".loader").show();

        $.ajax({
            url: '<?php echo base_url("inventory/goods-return/get-customer-product-returns"); ?>',
            type: 'POST',
            dataType: 'JSON',
            data: {
                customer_id: customerId,
                product_id: productId
            },
            success: function(res) {
                $(".loader").fadeOut("slow");
                if (res.status === 'success') {
                    var html = renderReturnSections(res.white, res.black);
                    $('#order_details_content').html(html);
                    $detailsContainer.show();
                    calculateGrandTotals();

                    // Add listener to radio button changes
                    $('.order-source-radio').on('change', function() {
                        var isChecked = $(this).is(':checked');
                        if (isChecked) {
                            var type = $(this).data('type');
                            var orderNo = $(this).data('order-no');

                            // Set form hidden fields
                            $('#type').val(type);
                            $('#order_no').val(orderNo);

                            // Enable inputs in the selected card
                            var $currentCard = $(this).closest('.card');
                            $currentCard.find('input, select').not('.order-source-radio').prop('disabled', false);

                            // Disable inputs in all other cards and reset their values
                            $('#order_details_content .card').not($currentCard).each(function() {
                                $(this).find('.qty-input').val(0);
                                $(this).find('.submit-qty-input').val(0);
                                $(this).find('.row-total-cell').text('0.00');
                                $(this).find('input, select').not('.order-source-radio').prop('disabled', true);
                            });

                            calculateGrandTotals();
                        }
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: res.message || "Failed to load details.",
                        icon: "error"
                    });
                }
            },
            error: function() {
                $(".loader").fadeOut("slow");
                Swal.fire({
                    title: "Error!",
                    text: "An error occurred while loading details.",
                    icon: "error"
                });
            }
        });
    }

    function renderReturnSections(whiteList, blackList) {
        var html = '';

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
                        batches: []
                    };
                }
                groupedWhite[key].batches.push(item);
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
                        batches: []
                    };
                }
                groupedBlack[key].batches.push(item);
            });
        }

        // White Section Rendering
        html += `
            <div class="divider divider-left divider-primary mb-1 mt-2">
                <div class="divider-text text-primary font-weight-bold"><i class="feather icon-file-text"></i> White Section (Invoices)</div>
            </div>
        `;
        if (Object.keys(groupedWhite).length > 0) {
            Object.values(groupedWhite).forEach(function(group) {
                html += `
                <div class="card mb-2 shadow-none border">
                    <div class="card-header bg-light-primary py-50 d-flex justify-content-between align-items-center" style="background-color: #f0f4fd; padding: 10px;">
                        <div>
                            <strong>Invoice No:</strong> ${group.invoice_no || '-'} | <strong>Date:</strong> ${group.date}
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="selected_order_source" class="form-check-input order-source-radio" data-type="official" data-order-no="${group.invoice_no}" id="select_white_${group.order_id}">
                            <label class="form-check-label font-weight-bold text-primary" for="select_white_${group.order_id}">Select this Invoice to return</label>
                        </div>
                    </div>
                    <div class="table-responsive border-top">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="text-align: left;">Product / Batch Details</th>
                                    <th style="width: 120px;">Total Qty</th>
                                    <th style="width: 120px;">Received Qty</th>
                                    <th style="width: 150px;">Return Qty</th>
                                    <th style="width: 120px;">Rate</th>
                                    <th style="width: 100px;">GST %</th>
                                    <th style="width: 130px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                group.batches.forEach(function(item) {
                    var maxQty = item.qty - item.return_qty;
                    html += `
                                <tr class="batch-row" id="batch_row_${item.product_batch_id}" data-id="${item.product_batch_id}">
                                    <td style="text-align: left; padding-left: 20px; vertical-align: middle;">
                                        <i class="feather icon-package text-muted me-25"></i> Batch: <strong>${item.batch_no || '-'}</strong>
                                        <input type="hidden" name="product_id[]" value="${item.product_id}" disabled>
                                        <input type="hidden" name="product_batch_id[]" value="${item.product_batch_id}" disabled>
                                        <input type="hidden" name="batch_no[]" value="${item.batch_no || '-'}" disabled>
                                        <input type="hidden" name="white_qty[]" class="submit-qty-input" value="0" disabled>
                                        <input type="hidden" name="black_qty[]" value="0" disabled>
                                        <input type="hidden" name="white_amt[]" class="rate-hidden-input" value="${item.amount}" disabled>
                                        <input type="hidden" name="black_amt[]" value="0" disabled>
                                        <input type="hidden" name="gst[]" class="gst-hidden-input" value="${item.gst || 0}" disabled>
                                    </td>
                                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.qty}</td>
                                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.return_qty}</td>
                                    <td style="vertical-align: middle;">
                                        <input type="number" value="0" min="0" max="${maxQty}" class="form-control form-control-sm text-center qty-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)" disabled>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <input type="number" value="${parseFloat(item.amount).toFixed(2)}" step="0.01" class="form-control form-control-sm text-end rate-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)" disabled>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <input type="number" value="${item.gst}" step="0.01" class="form-control form-control-sm text-center gst-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)" disabled>
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
            });
        } else {
            html += `<div class="alert alert-secondary p-1">No official invoices found with this product for this customer.</div>`;
        }

        // Black Section Rendering
        html += `
            <div class="divider divider-left divider-dark mb-1 mt-2">
                <div class="divider-text text-dark font-weight-bold"><i class="feather icon-file-text"></i> Black Section (Orders)</div>
            </div>
        `;
        if (Object.keys(groupedBlack).length > 0) {
            Object.values(groupedBlack).forEach(function(group) {
                html += `
                <div class="card mb-2 shadow-none border">
                    <div class="card-header bg-light-secondary py-50 d-flex justify-content-between align-items-center" style="background-color: #f7f7f7; padding: 10px;">
                        <div>
                            <strong>Order No:</strong> ${group.order_no || '-'} | <strong>Date:</strong> ${group.date}
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="selected_order_source" class="form-check-input order-source-radio" data-type="unofficial" data-order-no="${group.order_no}" id="select_black_${group.order_id}">
                            <label class="form-check-label font-weight-bold text-dark" for="select_black_${group.order_id}">Select this Order to return</label>
                        </div>
                    </div>
                    <div class="table-responsive border-top">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="text-align: left;">Product / Batch Details</th>
                                    <th style="width: 120px;">Total Qty</th>
                                    <th style="width: 120px;">Received Qty</th>
                                    <th style="width: 150px;">Return Qty</th>
                                    <th style="width: 120px;">Rate</th>
                                    <th style="width: 130px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                group.batches.forEach(function(item) {
                    var maxQty = item.qty - item.return_qty;
                    html += `
                                <tr class="batch-row" id="batch_row_${item.product_batch_id}" data-id="${item.product_batch_id}">
                                    <td style="text-align: left; padding-left: 20px; vertical-align: middle;">
                                        <i class="feather icon-package text-muted me-25"></i> Batch: <strong>${item.batch_no || '-'}</strong>
                                        <input type="hidden" name="product_id[]" value="${item.product_id}" disabled>
                                        <input type="hidden" name="product_batch_id[]" value="${item.product_batch_id}" disabled>
                                        <input type="hidden" name="batch_no[]" value="${item.batch_no || '-'}" disabled>
                                        <input type="hidden" name="white_qty[]" value="0" disabled>
                                        <input type="hidden" name="black_qty[]" class="submit-qty-input" value="0" disabled>
                                        <input type="hidden" name="white_amt[]" value="0" disabled>
                                        <input type="hidden" name="black_amt[]" class="rate-hidden-input" value="${item.amount}" disabled>
                                        <input type="hidden" name="gst[]" value="0" disabled>
                                    </td>
                                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.qty}</td>
                                    <td class="text-center font-monospace" style="vertical-align: middle;">${item.return_qty}</td>
                                    <td style="vertical-align: middle;">
                                        <input type="number" value="0" min="0" max="${maxQty}" class="form-control form-control-sm text-center qty-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)" disabled>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <input type="number" value="${parseFloat(item.amount).toFixed(2)}" step="0.01" class="form-control form-control-sm text-end rate-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)" disabled>
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
            });
        } else {
            html += `<div class="alert alert-secondary p-1">No unofficial orders found with this product for this customer.</div>`;
        }

        // Summary Card at the bottom
        html += `
            <div class="card mb-2 shadow-none border mt-2">
                <div class="card-body py-1 bg-light-primary d-flex justify-content-between align-items-center" style="background-color: #f0f4fd; padding: 10px;">
                    <h5 class="mb-0 text-primary"><strong>Grand Total:</strong></h5>
                    <h5 class="mb-0 text-primary font-monospace" id="order_grand_total"><strong>0.00</strong></h5>
                </div>
            </div>
        `;

        return html;
    }

    function updateRowTotal(input) {
        var $row = $(input).closest('tr');
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

        var rate = parseFloat($row.find('.rate-input').val()) || 0;
        var gst = parseFloat($row.find('.gst-input').val()) || 0;

        $row.find('.submit-qty-input').val(returnQty);
        $row.find('.rate-hidden-input').val(rate);
        if ($row.find('.gst-hidden-input').length) {
            $row.find('.gst-hidden-input').val(gst);
        }

        var total = returnQty * rate;
        if (gst > 0) {
            total = total * (1 + gst / 100);
        }

        $row.find('.row-total-cell').text(total.toFixed(2));
        calculateGrandTotals();
    }

    function calculateGrandTotals() {
        var type = $('#type').val();
        var grandTotal = 0;

        $('.batch-row').each(function() {
            var rowTotal = parseFloat($(this).find('.row-total-cell').text()) || 0;
            grandTotal += rowTotal;
        });

        $('#order_grand_total').html('<strong>' + grandTotal.toFixed(2) + '</strong>');

        if (type === 'official') {
            $('#white_total').val(grandTotal.toFixed(2));
            $('#black_total').val('0.00');
        } else if (type === 'unofficial') {
            $('#white_total').val('0.00');
            $('#black_total').val(grandTotal.toFixed(2));
        } else {
            $('#white_total').val('0.00');
            $('#black_total').val('0.00');
        }
        $('#final_total').val(grandTotal.toFixed(2));
    }
</script>