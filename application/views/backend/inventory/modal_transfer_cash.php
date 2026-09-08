<?php
$company_id   = $this->session->userdata('company_id');
$cash_in_hand = $this->inventory_model->get_cash_in_hand($company_id);
?>

<div class="row">
  <div class="col-12">
    <?php echo form_open('inventory/petty_cash/transfer_post', ['id' => 'transfer_cash_form', 'onsubmit' => 'return submitTransferCashForm(event);']); ?>
    <div class="row">

      <div class="col-12 mb-2">
        <div class="card bg-light-primary border-primary shadow-none mb-0">
          <div class="card-body p-1 d-flex justify-content-between align-items-center flex-wrap">
            <div>
              <h6 class="text-primary mb-25 font-weight-bold">Current Cash in Hand</h6>
              <small class="text-muted">Available balance to transfer</small>
            </div>
            <div>
              <h4 class="text-primary font-weight-bolder mb-0" id="modal_display_cash_in_hand">₹ <?= number_format($cash_in_hand ?? 0, 2); ?></h4>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12 mb-1">
        <div class="form-group">
          <label>Transfer Amount (in INR) <span class="required">*</span></label>
          <input type="number" name="amount" id="transfer_amount" class="form-control" value="" min="0.01" max="<?= (float)($cash_in_hand ?? 0); ?>" step="0.01" placeholder="Enter amount (Max: ₹<?= number_format($cash_in_hand ?? 0, 2); ?>)" required>
          <small class="text-danger mt-25 d-none font-weight-bold" id="transfer_amount_error_msg">Amount cannot exceed available cash in hand (₹<?= number_format($cash_in_hand ?? 0, 2); ?>)</small>
        </div>
      </div>

      <div class="col-md-12 mb-2">
        <div class="form-group">
          <label class="control-label">Remark / Narration</label>
          <textarea class="form-control" rows="3" placeholder="Enter remark or narration..." name="remark"></textarea>
        </div>
      </div>

      <div class="col-12">
        <button type="submit" id="transfer_submit_btn" class="btn btn-primary waves-effect waves-float waves-light me-1">
          <i class="feather icon-check"></i> Transfer
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>

    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<script>
var maxTransferCash = <?= (float)($cash_in_hand ?? 0); ?>;

function submitTransferCashForm(event) {
  event.preventDefault();

  var amount = parseFloat($('#transfer_amount').val()) || 0;
  if (amount <= 0) {
    Swal.fire({
      title: "Invalid Amount",
      text: "Please enter an amount greater than 0",
      icon: "warning",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    $('#transfer_amount').focus();
    return false;
  }

  if (amount > maxTransferCash) {
    Swal.fire({
      title: "Amount Exceeded",
      text: "Amount cannot exceed available cash in hand (₹" + maxTransferCash.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ")",
      icon: "error",
      customClass: { confirmButton: "btn btn-primary" },
      buttonsStyling: false
    });
    $('#transfer_amount').addClass('is-invalid');
    $('#transfer_amount_error_msg').removeClass('d-none');
    $('#transfer_amount').focus();
    return false;
  }

  var $submitBtn = $('#transfer_submit_btn');
  var originalText = $submitBtn.html();
  $submitBtn.attr("disabled", true);
  $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

  if (typeof $(".loader") !== 'undefined') {
    $(".loader").show();
  }

  var formData = $('#transfer_cash_form').serialize();
  var formUrl = $('#transfer_cash_form').attr('action');

  $.ajax({
    type: 'POST',
    url: formUrl,
    data: formData,
    dataType: 'json',
    success: function(res) {
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
      if (res.status == '200' || res.status == 200) {
        Swal.fire({
          title: "Success!",
          text: res.message || "Cash transferred successfully",
          icon: "success",
          customClass: { confirmButton: "btn btn-primary" },
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
          text: res.message || "An error occurred while transferring cash",
          icon: "error",
          customClass: { confirmButton: "btn btn-primary" },
          buttonsStyling: false
        });
        $submitBtn.html(originalText);
        $submitBtn.attr("disabled", false);
      }
    },
    error: function() {
      if (typeof $(".loader") !== 'undefined') {
        $(".loader").fadeOut("slow");
      }
      Swal.fire({
        title: "Error!",
        text: "An error occurred while processing request",
        icon: "error",
        customClass: { confirmButton: "btn btn-primary" },
        buttonsStyling: false
      });
      $submitBtn.html(originalText);
      $submitBtn.attr("disabled", false);
    }
  });

  return false;
}

$(document).ready(function() {
  $('#transfer_amount').on('input change', function() {
    var val = parseFloat($(this).val()) || 0;
    if (val > maxTransferCash) {
      $(this).addClass('is-invalid');
      $('#transfer_amount_error_msg').removeClass('d-none');
    } else {
      $(this).removeClass('is-invalid');
      $('#transfer_amount_error_msg').addClass('d-none');
    }
  });
});
</script>
