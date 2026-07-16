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
              <label class="form-label" for="type">Type <span class="required">*</span></label>
              <select class="form-select select2" name="type" id="type" required>
                <option value="">Select Type</option>
                <option value="official">Official</option>
                <option value="unofficial">Unofficial</option>
              </select>
            </div>

            <div class="col-12 col-sm-3 mb-1">
              <label class="form-label" for="order_no">Invoice/Order No <span class="required">*</span></label>
              <select class="form-select select2" name="order_no" id="order_no" required>
                <option value="">Select Invoice/Order No</option>
              </select>
            </div>
            
            <div class="col-12 col-sm-3 mb-1">
                <div class="form-group">
                    <label class="form-label" for="date_picker">Date <span class="required">*</span></label>
                    <input type="date" class="form-control" name="date" max="<?php echo date('Y-m-d');?>" value="<?php echo date('Y-m-d');?>" id="date_picker">
                </div>
            </div>
            
			<div class="col-12 col-sm-12 mb-1">
                <div class="form-group">
                    <label>Reason<span class="required">*</span></label>
                    <textarea class="form-control" placeholder="" rows="1" name="reason" id="reason" required></textarea>
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
        var totalQty = 0;
        $('.qty-input').each(function() {
            totalQty += parseFloat($(this).val()) || 0;
        });

        if (totalQty <= 0) {
            Swal.fire({
                title: "Error!",
                text: "Please enter return quantity for at least one batch!",
                icon: "error"
            });
            return false;
        }
        return true;
    }

    $(document).ready(function() {
        $('#customer_id, #type').on('change', function() {
            loadInvoiceOrders();
        });

        $('#order_no').on('change', function() {
            loadInvoiceOrderDetails();
        });
    });

    function loadInvoiceOrders() {
        var customerId = $('#customer_id').val();
        var type = $('#type').val();
        var $orderSelect = $('#order_no');

        // Clear dropdown and details first
        $orderSelect.html('<option value="">Select Invoice/Order No</option>').trigger('change.select2');
        $('#order_details_container').hide().find('#order_details_content').html('');

        if (!customerId || !type) {
            return;
        }

        $(".loader").show();

        $.ajax({
            url: '<?php echo base_url("inventory/goods-return/get-invoices-or-orders"); ?>',
            type: 'POST',
            dataType: 'JSON',
            data: {
                customer_id: customerId,
                type: type
            },
            success: function(res) {
                $(".loader").fadeOut("slow");
                if (res.status === 'success') {
                    let options = '<option value="">Select Invoice/Order No</option>';
                    res.data.forEach(function(item) {
                        let val = (type === 'official') ? item.invoice_no : item.order_no;
                        let label = val ? val : ('ID: ' + item.id);
                        options += `<option value="${val}">${label}</option>`;
                    });
                    $orderSelect.html(options).trigger('change.select2');
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to load invoices/orders.",
                        icon: "error"
                    });
                }
            },
            error: function() {
                $(".loader").fadeOut("slow");
                Swal.fire({
                    title: "Error!",
                    text: "An error occurred while loading invoices/orders.",
                    icon: "error"
                });
            }
        });
    }

    function loadInvoiceOrderDetails() {
        var orderNo = $('#order_no').val();
        var type = $('#type').val();
        var $detailsContainer = $('#order_details_container');

        $detailsContainer.hide().find('#order_details_content').html('');

        if (!orderNo || !type) {
            return;
        }

        $(".loader").show();

        $.ajax({
            url: '<?php echo base_url("inventory/goods-return/get-details"); ?>',
            type: 'POST',
            dataType: 'JSON',
            data: {
                order_no: orderNo,
                type: type
            },
            success: function(res) {
                $(".loader").fadeOut("slow");
                if (res.status === 'success') {
                    var html = renderOrderDetails(res, type);
                    $('#order_details_content').html(html);
                    $detailsContainer.show();
                    calculateGrandTotals();
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: res.message || "Failed to load order details.",
                        icon: "error"
                    });
                }
            },
            error: function() {
                $(".loader").fadeOut("slow");
                Swal.fire({
                    title: "Error!",
                    text: "An error occurred while loading order details.",
                    icon: "error"
                });
            }
        });
    }

    function renderOrderDetails(data, type) {
        var html = '';
        
        // Order Info Header
        html += `
            <div class="divider divider-left divider-primary mb-1 mt-2">
                <div class="divider-text text-primary font-weight-bold"><i class="feather icon-info"></i> Order Information</div>
            </div>
            <div class="card mb-2 shadow-none border">
                <div class="card-body p-1" style="background-color: #fafbfc;">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>${type === 'official' ? 'Invoice No' : 'Order No'}:</strong> 
                            ${type === 'official' ? (data.order.invoice_no || '-') : (data.order.order_no || '-')}
                        </div>
                        <div class="col-md-4"><strong>Date:</strong> ${data.order.date}</div>
                        <div class="col-md-4"><strong>Customer:</strong> ${data.order.customer_name}</div>
                    </div>
                </div>
            </div>
        `;

        // Products table header
        html += `
            <div class="divider divider-left divider-info mb-1">
                <div class="divider-text text-info font-weight-bold"><i class="feather icon-shopping-bag"></i> Products & Batches</div>
            </div>
            <div class="table-responsive border rounded mb-2">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="text-align: left;">Product / Batch Details</th>
                            <th style="width: 120px;">Total Qty</th>
                            <th style="width: 120px;">Received Qty</th>
                            <th style="width: 150px;">Return Qty</th>
                            <th style="width: 120px;">Rate</th>
                            ${type === 'official' ? '<th style="width: 100px;">GST %</th>' : ''}
                            <th style="width: 130px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.products.forEach(function(p) {
            // Product Row Header
            html += `
                <tr class="table-light">
                    <td colspan="${type === 'official' ? '7' : '6'}" style="text-align: left; font-weight: bold;" class="text-dark py-50">
                        ${p.product_name} <span class="badge bg-light-secondary text-secondary ms-50 font-small-2">${p.item_code}</span>
                    </td>
                </tr>
            `;

            // Batch Rows
            p.batches.forEach(function(b) {
                if (type === 'official') {
                    let maxQty = b.qty - b.return_qty;
                    html += `
                        <tr class="batch-row" id="batch_row_${b.id}" data-id="${b.id}" data-rate="${b.amount}" data-gst="${b.gst}">
                            <td style="text-align: left; padding-left: 20px; vertical-align: middle;">
                                <i class="feather icon-package text-muted me-25"></i> Batch: <strong>${b.batch_no}</strong>
                                <input type="hidden" name="product_id[]" value="${p.product_id}">
                                <input type="hidden" name="product_batch_id[]" value="${b.id}">
                                <input type="hidden" name="batch_no[]" value="${b.batch_no}">
                                <input type="hidden" name="white_qty[]" class="submit-qty-input" value="0">
                                <input type="hidden" name="black_qty[]" value="0">
                                <input type="hidden" name="white_amt[]" value="${b.amount}">
                                <input type="hidden" name="black_amt[]" value="0">
                                <input type="hidden" name="gst[]" value="${b.gst || 0}">
                            </td>
                            <td class="text-center font-monospace" style="vertical-align: middle;">${b.qty}</td>
                            <td class="text-center font-monospace" style="vertical-align: middle;">${b.return_qty}</td>
                            <td style="vertical-align: middle;">
                                <input type="number" value="0" min="0" max="${maxQty}" class="form-control form-control-sm text-center qty-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                            </td>
                            <td class="text-end font-monospace" style="vertical-align: middle;">${b.amount.toFixed(2)}</td>
                            <td class="text-center font-monospace" style="vertical-align: middle;">${b.gst}%</td>
                            <td class="text-end font-monospace row-total-cell" style="vertical-align: middle;">0.00</td>
                        </tr>
                    `;
                } else {
                    let maxQty = b.black_qty - b.return_black_qty;
                    html += `
                        <tr class="batch-row" id="batch_row_${b.id}" data-id="${b.id}" data-rate="${b.amount}" data-gst="0">
                            <td style="text-align: left; padding-left: 20px; vertical-align: middle;">
                                <i class="feather icon-package text-muted me-25"></i> Batch: <strong>${b.batch_no}</strong>
                                <input type="hidden" name="product_id[]" value="${p.product_id}">
                                <input type="hidden" name="product_batch_id[]" value="${b.id}">
                                <input type="hidden" name="batch_no[]" value="${b.batch_no}">
                                <input type="hidden" name="white_qty[]" value="0">
                                <input type="hidden" name="black_qty[]" class="submit-qty-input" value="0">
                                <input type="hidden" name="white_amt[]" value="0">
                                <input type="hidden" name="black_amt[]" value="${b.amount}">
                                <input type="hidden" name="gst[]" value="0">
                            </td>
                            <td class="text-center font-monospace" style="vertical-align: middle;">${b.black_qty}</td>
                            <td class="text-center font-monospace" style="vertical-align: middle;">${b.return_black_qty}</td>
                            <td style="vertical-align: middle;">
                                <input type="number" value="0" min="0" max="${maxQty}" class="form-control form-control-sm text-center qty-input" onkeyup="updateRowTotal(this)" onchange="updateRowTotal(this)">
                            </td>
                            <td class="text-end font-monospace" style="vertical-align: middle;">${b.amount.toFixed(2)}</td>
                            <td class="text-end font-monospace row-total-cell" style="vertical-align: middle;">0.00</td>
                        </tr>
                    `;
                }
            });
        });

        // Summary Row
        html += `
                    </tbody>
                    <tfoot>
                        <tr class="table-light font-weight-bold">
                            <td colspan="${type === 'official' ? '6' : '5'}" class="text-end py-1">Grand Total:</td>
                            <td class="text-end text-primary py-1 font-monospace" id="order_grand_total">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        return html;
    }

    function updateRowTotal(input) {
        var $row = $(input).closest('tr');
        var rate = parseFloat($row.data('rate')) || 0;
        var gst = parseFloat($row.data('gst')) || 0;
        var returnQty = parseFloat($(input).val()) || 0;
        var maxQty = parseFloat($(input).attr('max')) || 0;

        if (returnQty < 0) {
            $(input).val(0);
            returnQty = 0;
        }
        if (returnQty > maxQty) {
            Swal.fire({
                title: "Limit Exceeded",
                text: "Return quantity cannot exceed " + maxQty,
                icon: "warning"
            });
            $(input).val(maxQty);
            returnQty = maxQty;
        }

        var total = returnQty * rate;
        if (gst > 0) {
            total = total * (1 + gst / 100);
        }

        $row.find('.submit-qty-input').val(returnQty);
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

        $('#order_grand_total').text(grandTotal.toFixed(2));

        if (type === 'official') {
            $('#white_total').val(grandTotal.toFixed(2));
            $('#black_total').val('0.00');
        } else {
            $('#white_total').val('0.00');
            $('#black_total').val(grandTotal.toFixed(2));
        }
        $('#final_total').val(grandTotal.toFixed(2));
    }
</script>