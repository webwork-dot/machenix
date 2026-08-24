<?php
  $customer_data = $this->inventory_model->get_customer_by_id($param2)->row_array();
?>

<div class="row">
  <div class="col-12">
      <?php echo form_open('inventory/customer/add_call', ['id' => 'customer_add_call_form', 'onsubmit' => 'return submitAddCallForm(event);']); ?>
      
      <input type="hidden" name="customer_id" value="<?php echo $param2; ?>">
      <div class="row">
        
        <div class="col-12 mb-2">
          <div class="form-group">
            <label>Remark <span class="required">*</span></label>
            <textarea name="remark" id="remark" class="form-control" rows="3" required placeholder="Enter remark"></textarea>
          </div>
        </div>
        
        <div class="col-12">
          <button type="submit" id="add_call_submit_btn" class="btn btn-primary waves-effect waves-float waves-light">
            <i class="fa fa-phone"></i> Submit
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
function submitAddCallForm(event) {
  event.preventDefault();
 
  // Disable submit button and show loading
  var $submitBtn = $('#add_call_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  
  // Show loader if available
  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }
  
  // Get form data
  var formData = $('#customer_add_call_form').serialize();
  var formUrl = $('#customer_add_call_form').attr('action');
  
  // Submit via AJAX
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
          text: res.message || "Call Added Successfully",
          icon: "success",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: false
        }).then(() => {
          $('#scrollable-modal').modal('hide');
          
          if (res.url) {
            window.location.href = res.url;
          } else {
            location.reload();
          }
        });
      } else {
        Swal.fire({
          title: "Error!",
          text: res.message || "An error occurred while adding call",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: false
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
        buttonsStyling: false
      });
      
      $submitBtn.html(originalText);
      $submitBtn.attr("disabled", false);
      
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
    }
  });
  
  return false;
}
</script>
