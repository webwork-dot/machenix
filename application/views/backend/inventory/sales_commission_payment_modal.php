<div class="row">
  <div class="col-12">
      <?php echo form_open('inventory/make_sales_commission_payment', ['id' => 'sales_commission_payment_form', 'onsubmit' => 'return submitPaymentForm(event);']); ?>
      
      <input type="hidden" name="order_ids" value="<?php echo urldecode($param2); ?>">
      
      <div class="row">
        <div class="col-6 mb-2">
          <div class="form-group">
            <label>Total Amt <span class="required">*</span></label>
            <input type="text" class="form-control" name="total_amount" value="<?php echo number_format((float)$param3, 2, '.', ''); ?>" readonly required>
          </div>
        </div>

        <div class="col-6 mb-2">
          <div class="form-group">
            <label>Date <span class="required">*</span></label>
            <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
        </div>
        
        <div class="col-12 mb-2">
          <div class="form-group">
            <label>Remark <span class="required">*</span></label>
            <textarea name="remark" id="remark" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        
        <div class="col-12 text-end">
          <button type="submit" id="payment_submit_btn" class="btn btn-primary waves-effect waves-float waves-light">
            Submit
          </button>
          <button type="button" class="btn btn-secondary waves-effect waves-float waves-light" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </div>

      <?php echo form_close(); ?>
  </div>
</div>

<script>
function submitPaymentForm(event) {
  event.preventDefault();
 
  var $submitBtn = $('#payment_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  
  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }
  
  var formData = $('#sales_commission_payment_form').serialize();
  var formUrl = $('#sales_commission_payment_form').attr('action');
  
  $.ajax({
    type: 'POST',
    url: formUrl,
    data: formData,
    dataType: 'json',
    success: function(res) {
      if (res.status == '200' || res.status == 200) {
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
        
        Swal.fire({
          title: "Success!",
          text: res.message || "Payment Processed Successfully",
          icon: "success",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        }).then(() => {
          $('#scrollable-modal').modal('hide');
          location.reload();
        });
      } else {
        Swal.fire({
          title: "Error!",
          text: res.message || "An error occurred while processing payment",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        });
        
        $submitBtn.html(originalText);
        $submitBtn.attr("disabled", false);
        
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
      }
    },
    error: function(xhr, status, error) {
      Swal.fire({
        title: "Error!",
        text: "An error occurred while processing your request. Please try again.",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });
      
      $submitBtn.html(originalText);
      $submitBtn.attr("disabled", false);
      
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
    }
  });
}
</script>
