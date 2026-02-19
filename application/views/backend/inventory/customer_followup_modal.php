<?php
  $customer_data = $this->inventory_model->get_customer_by_id($param2)->row_array();
?>

<div class="row">
  <div class="col-12">
      <?php echo form_open('inventory/customer/follow', ['id' => 'customer_reassign_form', 'onsubmit' => 'return submitReassignForm(event);']); ?>
      
      <input type="hidden" name="customer_id" value="<?php echo $param2; ?>">
      <div class="row">
        
        <div class="col-12 mb-2">
          <div class="form-group">
            <label> Priority <span class="required">*</span></label>
            <select class="form-control " name="status" onchange="decideDate(this.value)" id="status" required>
              <option value="">Select Priority</option>
              <option value="follow | Needs Follow Up">Needs Follow Up</option>
              <option value="follow | Tentative">Tentative</option>
              <option value="follow | Might Turn Up">Might Turn Up</option>
              <option value="lost | Lost">Lost</option>
            </select>
          </div>
        </div>

        <div class="col-12 mb-2" id="fd-cont">
          <div class="form-group">
            <label> Follow-Up Date <span class="required">*</span></label>
            <input type="datetime-local" class="form-control" name="status_date" id="fd-inp" required>
          </div>
        </div>
        
        <div class="col-12 mb-2">
          <div class="form-group">
            <label>Remark</label>
            <textarea name="remark" id="remark" class="form-control" rows="3"></textarea>
          </div>
        </div>
        
        <div class="col-12">
          <button type="submit" id="reassign_submit_btn" class="btn btn-primary waves-effect waves-float waves-light">
            <i class="fa fa-refresh"></i> Submit
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

function decideDate(value) {
  if(value == 'lost | Lost') {
    document.querySelector("#fd-cont").classList.add('d-none');
    document.querySelector("#fd-inp").removeAttribute('required');
  } else {
    document.querySelector("#fd-cont").classList.remove('d-none');
    document.querySelector("#fd-inp").setAttribute('required', true);
  }
}

function submitReassignForm(event) {
  event.preventDefault();
 
  // Disable submit button and show loading
  var $submitBtn = $('#reassign_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  
  // Show loader if available
  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }
  
  // Get form data
  var formData = $('#customer_reassign_form').serialize();
  var formUrl = $('#customer_reassign_form').attr('action');
  
  // Submit via AJAX
  $.ajax({
    type: 'POST',
    url: formUrl,
    data: formData,
    dataType: 'json',
    success: function(res) {
      if (res.status == '200' || res.status == 200) {
        // Hide loader
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
        
        // Show success message
        Swal.fire({
          title: "Success!",
          text: res.message || "Follow Up Added Successfully",
          icon: "success",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        }).then(() => {
          // Close modal
          $('#scrollable-modal').modal('hide');
          
          // Reload page or refresh customer list
          if (res.url) {
            window.location.href = res.url;
          } else {
            location.reload();
          }
        });
      } else {
        // Show error message
        Swal.fire({
          title: "Error!",
          text: res.message || "An error occurred while changing staff",
          icon: "error",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        });
        
        // Re-enable submit button
        $submitBtn.html(originalText);
        $submitBtn.attr("disabled", false);
        
        // Hide loader
        if (typeof $(".loader") !== 'undefined') {
          $(".loader").fadeOut("slow");
        }
      }
    },
    error: function(xhr, status, error) {
      // Show error message
      Swal.fire({
        title: "Error!",
        text: "An error occurred while processing your request. Please try again.",
        icon: "error",
        customClass: {
          confirmButton: "btn btn-primary"
        },
        buttonsStyling: !1
      });
      
      // Re-enable submit button
      $submitBtn.html(originalText);
      $submitBtn.attr("disabled", false);
      
      // Hide loader
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
    }
  });
  
  return false;
}

</script>

