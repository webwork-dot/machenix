<?php
$supplier_id = $param2;
$supplier_data = $this->inventory_model->get_supplier_by_id($supplier_id)->row_array();
$current_company_id = $this->session->userdata('company_id');
$companies = $this->common_model->getResultById('company', '*', array('is_deleted' => 0));
?>

<div class="row">
  <div class="col-12">
      <?php echo form_open('inventory/supplier/replicate_post', ['id' => 'supplier_replicate_form', 'onsubmit' => 'return submitReplicateForm(event);']); ?>
      
      <input type="hidden" name="supplier_id" value="<?php echo $supplier_id; ?>">
      <div class="row">
        
        <div class="col-12 mb-2">
          <div class="form-group">
            <label>Select Company to Replicate <span class="required">*</span></label>
            <select class="form-control " name="target_company_id" id="target_company_id" required>
              <option value="">Select Company</option>
              <?php 
              if (!empty($companies)) {
                foreach ($companies as $company) {
                  // Exclude current company
                  if ($company['id'] != $current_company_id) {
                    echo '<option value="' . $company['id'] . '">' . $company['name'] . '</option>';
                  }
                }
              }
              ?>
            </select>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" id="replicate_submit_btn" class="btn btn-primary waves-effect waves-float waves-light">
            <i class="fa fa-refresh"></i> Replicate Supplier
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
function submitReplicateForm(event) {
  event.preventDefault();
  
  // Validate form
  var targetCompanyId = $('#target_company_id').val();
  if (!targetCompanyId || targetCompanyId === '') {
    Swal.fire({
      title: "Error!",
      text: "Please select a company to replicate",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: !1
    });
    return false;
  }
  
  // Disable submit button and show loading
  var $submitBtn = $('#replicate_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  
  // Show loader if available
  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }
  
  // Get form data
  var formData = $('#supplier_replicate_form').serialize();
  var formUrl = $('#supplier_replicate_form').attr('action');
  
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
          text: res.message || "Supplier replicated successfully",
          icon: "success",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: !1
        }).then(() => {
          // Close modal
          $('#scrollable-modal').modal('hide');
          
          // Reload page or refresh supplier list
          if (res.url) {
            window.location.href = res.url;
          } else {
            // Reload current page
            location.reload();
          }
        });
      } else {
        // Show error message
        Swal.fire({
          title: "Error!",
          text: res.message || "An error occurred while replicating supplier",
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

$(document).ready(function() {
  // Form is ready
});
</script>

