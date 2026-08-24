<?php
$customers = $this->common_model->getSessionCustomers();
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/add_customer_call_post', ['id' => 'customer_call_add_form', 'onsubmit' => 'return submitCustomerCallForm(event);']); ?>
      
      <div class="row">
        <!-- 1. Customer Dropdown (Staff-wise) -->
        <div class="col-12 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_customer_id">Customer <span class="text-danger">*</span></label>
            <select class="form-select select2" name="customer_id" id="modal_customer_id" required style="width: 100%;">
              <option value="">Select Customer</option>
              <?php if (!empty($customers)): ?>
                <?php foreach ($customers as $c): ?>
                  <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['company_name']); ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <!-- Customer History Scrollable Container -->
          <div id="customer_history_wrapper" class="mt-1" style="display: none;">
            <label class="form-label text-muted mb-50" style="font-size: 12px; font-weight: 600;">Customer History </label>
            <div id="customer_history_container" style="max-height: 220px; overflow-y: auto; padding: 8px; border: 1px solid #edf2f7; border-radius: 8px; background-color: #f8fafc;">
            </div>
          </div>
        </div>

        <!-- 2. Follow Up Date -->
        <div class="col-12 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_follow_up_date">Follow Up Date <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control" name="date" id="modal_follow_up_date" value="<?php echo date('Y-m-d\TH:i'); ?>" min="<?php echo date('Y-m-d\TH:i'); ?>" required>
          </div>
        </div>

        <!-- 3. Remark (Non-mandatory) -->
        <div class="col-12 mb-1">
          <div class="form-group">
            <label class="form-label" for="modal_remark">Remark</label>
            <textarea class="form-control" name="remark" id="modal_remark" rows="3" placeholder="Enter remark"></textarea>
          </div>
        </div>
      </div>

      <div class="col-12 text-center mt-1">
        <button type="submit" id="customer_call_submit_btn" class="btn btn-primary waves-effect waves-float waves-light me-1" name="btn_submit">
          <?php echo get_phrase('submit'); ?>
        </button>
      </div>

    <?php echo form_close(); ?>
  </div>
</div>

<script>
  $(document).ready(function() {
    if ($.fn.select2) {
      $('#modal_customer_id').select2({
        dropdownParent: $('#scrollable-modal')
      });
    }

    // Fetch and display customer history on selection
    $('#modal_customer_id').on('change', function() {
      var customerId = $(this).val();
      if (customerId) {
        $('#customer_history_wrapper').show();
        $('#customer_history_container').html('<div class="text-center p-1"><span class="spinner-border spinner-border-sm text-primary" role="status"></span> Loading history...</div>');
        $.ajax({
          type: 'POST',
          url: '<?php echo base_url("inventory/get_customer_history_ajax"); ?>',
          data: { customer_id: customerId },
          success: function(response) {
            $('#customer_history_container').html(response);
          },
          error: function() {
            $('#customer_history_container').html('<div class="text-center text-danger p-1"><small>Error loading history</small></div>');
          }
        });
      } else {
        $('#customer_history_wrapper').hide();
        $('#customer_history_container').html('');
      }
    });
  });

  function submitCustomerCallForm(event) {
    event.preventDefault();

    var inpVal = $('#modal_follow_up_date').val();
    if (inpVal) {
      var selectedDate = new Date(inpVal);
      var now = new Date();
      if (selectedDate < now) {
        Swal.fire({
          title: "Invalid Date!",
          text: "Follow-Up date and time cannot be in the past.",
          icon: "warning",
          customClass: {
            confirmButton: "btn btn-primary"
          },
          buttonsStyling: false
        });
        return false;
      }
    }
   
    var $submitBtn = $('#customer_call_submit_btn');
    var originalText = $submitBtn.html();
    $submitBtn.attr("disabled", true);
    $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
    
    if (typeof $(".loader") !== 'undefined') {
      $(".loader").show();
    }
    
    var formData = $('#customer_call_add_form').serialize();
    var formUrl = $('#customer_call_add_form').attr('action');
    
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
            text: res.message || "Customer Call Added Successfully",
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
            text: res.message || "An error occurred while adding customer call",
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
