<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/other_charges/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                <label>Charge Name <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Charge Name" name="name" required="">
              </div>
            </div>
            <div class="col-12 col-sm-6 mb-1">
              <div class="form-group">
                <label>GST (%) <span class="required">*</span></label>
                <input type="number" step="0.01" class="form-control" placeholder="Enter GST %" name="gst" required="">
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
