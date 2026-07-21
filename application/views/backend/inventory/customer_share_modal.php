<?php
  $customer_id = $param2;
  $customer_data = $this->inventory_model->get_customer_by_id($customer_id)->row_array();
  $current_company_id = $this->session->userdata('company_id');
  
  // Get all staffs for the companies associated with this customer
  $company_ids = explode(',', $customer_data['company_id']);
  $staffs = $this->inventory_model->get_staff_by_company_ids($company_ids, 'array');
  
  // Get active commissions
  $commissions = $this->common_model->getResultById('product_commission_slab', 'id, name, commission', ['is_deleted' => '0']);
  
  // Get existing customer commissions (to pre-populate)
  $customer_commissions = $this->common_model->getResultById('customer_commission', 'commission_id, shared_commission', ['customer_id' => $customer_id]);
  
  $existing_comm = [];
  if (!empty($customer_commissions)) {
      foreach ($customer_commissions as $cc) {
          $existing_comm[$cc['commission_id']] = (float)$cc['shared_commission'];
      }
  }
?>

<div class="row">
  <div class="col-12">
      <?php echo form_open('inventory/customer/share', ['id' => 'customer_share_form', 'onsubmit' => 'return submitShareForm(event);']); ?>
      
      <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
      <div class="row">
        
        <div class="col-12 mb-2">
          <div class="form-group">
            <label class="form-label">Select Staff to Share With <span class="required">*</span></label>
            <select class="form-select" name="shared_id" id="shared_id" required>
              <option value="">Select Staff</option>
              <?php foreach ($staffs as $st): ?>
                <?php if ($st['id'] != $customer_data['added_by_id']): ?>
                  <option value="<?php echo $st['id']; ?>" <?php echo ($st['id'] == $customer_data['shared_id']) ? 'selected' : ''; ?>>
                    <?php echo $st['name']; ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-12 mb-2">
          <h5 class="mb-1">Commission Sharing</h5>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Commission Slab</th>
                  <th>Share Commission (%)</th>
                  <th>My Commission (%)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($commissions as $comm): 
                  $share_val = isset($existing_comm[$comm['id']]) ? floatval($existing_comm[$comm['id']]) : 0.00;
                  $my_val = 100 - $share_val;
                ?>
                  <tr>
                    <td>
                      <?php echo $comm['name']; ?> (<?php echo $comm['commission']; ?>%)
                      <input type="hidden" name="commission_ids[]" value="<?php echo $comm['id']; ?>">
                    </td>
                    <td>
                      <input type="number" step="0.01" min="0" max="100" class="form-control" name="share_comm[<?php echo $comm['id']; ?>]" id="share_comm_<?php echo $comm['id']; ?>" value="<?php echo $share_val; ?>" oninput="calculateCommission(<?php echo $comm['id']; ?>)" placeholder="0.00">
                    </td>
                    <td>
                      <input type="number" step="0.01" class="form-control" name="my_comm[<?php echo $comm['id']; ?>]" id="my_comm_<?php echo $comm['id']; ?>" value="<?php echo $my_val; ?>" readonly>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-12 mt-1">
          <button type="submit" id="share_submit_btn" class="btn btn-primary waves-effect waves-float waves-light me-1">
            <i class="fa fa-share"></i> Share
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
function calculateCommission(slabId) {
  let shareInput = document.getElementById('share_comm_' + slabId);
  let myInput = document.getElementById('my_comm_' + slabId);
  let shareVal = parseFloat(shareInput.value) || 0;
  if (shareVal < 0) shareVal = 0;
  if (shareVal > 100) shareVal = 100;
  shareInput.value = shareVal;
  let myVal = (100 - shareVal).toFixed(2);
  myInput.value = myVal;
}

function submitShareForm(event) {
  event.preventDefault();
  
  var sharedId = $('#shared_id').val();
  if (!sharedId) {
    Swal.fire({
      title: "Error!",
      text: "Please select a staff member to share with.",
      icon: "error",
      customClass: {
        confirmButton: "btn btn-primary"
      },
      buttonsStyling: false
    });
    return false;
  }
 
  var $submitBtn = $('#share_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
  
  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }
  
  var formData = $('#customer_share_form').serialize();
  var formUrl = $('#customer_share_form').attr('action');
  
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
          text: res.message || "Customer shared successfully",
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
          text: res.message || "An error occurred while sharing customer",
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
