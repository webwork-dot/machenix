
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

        <!-- form -->
        <form class="validate-form" action="<?php echo site_url('admin/staff/change_password/'.$id); ?>" onsubmit="return checkForm(this);"  enctype="multipart/form-data" method="post">
          <div class="row">
              
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="first_name">New Password<i class="required">*</i></label>
              <input type="hidden" name="id" value="" />
			  <input type="password" id="password" class="form-control" name="new_password" value="" required />
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="last_name">Change Password<i class="required">*</i></label>
              <input type="password" id="confirm_password" class="form-control" name="confirm_password" value="" required />
			  <span id='message'></span>
            </div>

            <div class="col-12 col-sm-4 mb-1">
			   <label class="row">&nbsp;</label>
              <button type="submit" class="btn btn-primary mt-0 me-1 btnf check">Submit</button>
            </div>
          </div>
        </form>
        <!--/ form -->
      </div>
    </div>
    </div>
</div>
  
<script>
    $('#password, #confirm_password').on('keyup', function () {
  if ($('#password').val() == $('#confirm_password').val()) {
    $('#message').html('').css('color', 'green');
     $('.check').prop('disabled', false);
  } else {
    $('#message').html('Not Matching').css('color', 'red');
    $('.check').prop('disabled', true);
  }
});
</script>
