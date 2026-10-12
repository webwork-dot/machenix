<?php include('nav/nav_settings.php'); ?>
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card" style="border-top-left-radius: 0;">
      <div class="card-body py-1 my-0">
        <div class="row align-items-center mb-1 mt-1">
          <div class="col-12">
            <h5 class="mb-0"><b>Settings</b></h5>
          </div>
        </div>
        <?php echo form_open('inventory/settings/update_post', ['class' => 'add-ajax-redirect-form', 'id' => 'settings_form', 'onsubmit' => 'return checkForm(this);']); ?>
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Sales Booking days <span class="required">*</span></label>
                <input type="number"
                       class="form-control"
                       name="max_booking_days"
                       id="max_booking_days"
                       placeholder="Enter Sales Booking days"
                       min="1"
                       step="1"
                       required
                       value="<?php echo isset($settings['max_booking_days']) ? (int)$settings['max_booking_days'] : ''; ?>">
                <!-- <small class="text-muted">Must be greater than 0</small> -->
              </div>
            </div>
            <div class="col-12">
              <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name="btn_verify">Submit</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
  $('#settings_form').on('submit', function() {
    var days = parseInt($('#max_booking_days').val(), 10);
    if (isNaN(days) || days <= 0) {
      Swal.fire({
        title: "Invalid Value",
        text: "Sales Booking days must be greater than 0",
        icon: "warning",
        customClass: { confirmButton: "btn btn-primary" },
        buttonsStyling: false
      });
      $('#max_booking_days').focus();
      return false;
    }
  });
});
</script>
