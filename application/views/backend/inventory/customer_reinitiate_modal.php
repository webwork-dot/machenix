<?php
  $customer_id = $param2;
  $customer_data = $this->inventory_model->get_customer_by_id($customer_id)->row_array();
  $current_company_id = $this->session->userdata('company_id');
  $companies = $this->common_model->getResultById('company', '*', array('is_deleted' => 0));
?>

<div class="row">
  <div class="col-12">
      <?php echo form_open('inventory/customer/reassign', ['id' => 'customer_reassign_form', 'onsubmit' => 'return submitReassignForm(event);']); ?>
      
      <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
      <div class="row">
        
        <div class="col-6 mb-2">
          <div class="form-group">
            <label>Select Company <span class="required">*</span></label>
            <select class="form-control " name="target_company_id" id="target_company_id" onchange="getStaffByCompanyId(this.value);" required>
              <option value="">Select Company</option>
              <?php 
                if (!empty($companies)) {
                  foreach ($companies as $company) {
                    if(in_array($company['id'], explode(',', $customer_data['company_id']))) {
                      echo '<option value="' . $company['id'] . '">' . $company['name'] . '</option>';
                    }
                  }
                }
              ?>
            </select>
          </div>
        </div>

        <div class="col-6 mb-2">
          <div class="form-group">
            <label>Select Staff <span class="required">*</span></label>
            <select class="form-control " name="target_staff_id" id="target_staff_id" required>
              <option value="">Select Staff</option>
            </select>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" id="reassign_submit_btn" class="btn btn-primary waves-effect waves-float waves-light">
            <i class="fa fa-refresh"></i> Assign
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
function submitReassignForm(event) {
  event.preventDefault();
  
  // Validate form
  var targetCompanyId = $('#target_company_id').val();
 
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
          text: res.message || "Staff Change successfully",
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

function getStaffByCompanyId(companyId) {
  if (!companyId) {
    $('#target_staff_id').html('<option value="">Select Staff</option>');
    return;
  } else {
    $.ajax({
      type: 'POST',
      url: '<?php echo base_url('inventory/get_staff_by_company_id'); ?>',
      data: { company_id: companyId },
      dataType: 'json',
      success: function(res) {
        if (res.status == '200' || res.status == 200) {
          var staffOptions = '<option value="">Select Staff</option>';
          if (res.data && res.data.length > 0) {
            $.each(res.data, function(index, staff) {
              staffOptions += '<option value="' + staff.id + '">' + staff.name + '</option>';
            });
          }
          $('#target_staff_id').html(staffOptions);
        } else {
          Swal.fire({
            title: "Error!",
            text: res.message || "An error occurred while fetching staff",
            icon: "error",
            customClass: {
              confirmButton: "btn btn-primary"
            },
            buttonsStyling: !1
          });
        }
      }
    });
  }
}



</script>

