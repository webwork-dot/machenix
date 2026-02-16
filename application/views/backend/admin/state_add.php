<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('admin/state/add_post', ['class' => 'add-ajax-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="code">Code</label>
              <input type="text" class="form-control" id="code" name="code" placeholder="Code" required>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">State</label>
              <input type="text" class="form-control" id="state" name="state" placeholder="State" required>
            </div>
			
          <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>

          <?php echo form_close(); ?>		
        <!--/ form -->
      </div>
    </div>
    </div>
</div>