<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/bank_accounts/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Name <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Name" name="name" required="">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>IFSC Code <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter IFSC Code" name="ifsc_code" required="">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Bank Name <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Bank Name" name="bank_name" required="">
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Account No <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Account No" name="account_no" required="">
              </div>
            </div>
            <div class="col-12">
              <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name="btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
        <?php echo form_close(); ?>		
      </div>
    </div>
  </div>
</div>

