<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
        <?php echo form_open('inventory/storage/edit_post/'.$id, ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Type <span class="required">*</span></label>
                <select class="form-control" name="type" required="">
                  <option value="">Select Type</option>
                  <option value="rack" <?php echo (isset($data['type']) && $data['type'] == 'rack') ? 'selected' : ''; ?>>Rack</option>
                  <option value="room" <?php echo (isset($data['type']) && $data['type'] == 'room') ? 'selected' : ''; ?>>Room</option>
                  <option value="draw" <?php echo (isset($data['type']) && $data['type'] == 'draw') ? 'selected' : ''; ?>>Draw</option>
                </select>
              </div>
            </div>
            <div class="col-12 col-sm-4 mb-1">
              <div class="form-group">
                <label>Name <span class="required">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Storage Name" name="name" required="" value="<?php echo isset($data['name']) ? $data['name'] : ''; ?>">
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
