<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('common/reminder/add_post', ['class' => 'add-ajax-redirect-image-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
          <div class="row">
            <div class="col-12 col-sm-4 mb-2">
              <label class="form-label" for="name">Title<span class="required">*</span></label>
              <input type="text" class="form-control" id="title" name="title" maxlength="30" placeholder="Enter Title" required>
			  <p><small class="text-muted">Max 30 Character length</small></p>
               <span class="invalid-feedback"></span>
            </div> 
			
            <div class="col-12 col-sm-4 mb-2">
				<label class="form-label">Reminder Date/Time<i class="required">*</i></label>
				<div class="input-group">
				 <input type="text" class="form-control flatpickr-min readonly" name="rem_date"placeholder="Reminder Date" autocomplete="off"  required>
				 <input type="text" class="form-control flatpickr-time" name="rem_time" placeholder="Reminder Time" autocomplete="off" required>
				 <span class="invalid-feedback"></span>
				</div>
			</div>
           
            <div class="col-12 col-sm-8 mb-2">
                <label class="form-label" for="description">Description</label>     

				<fieldset class="form-label-group mb-0">
				<textarea data-length="120" maxlength="120" class="form-control char-textarea" name="description" id="textarea-counter" rows="3" placeholder="Description"></textarea>					
				</fieldset><small class="counter-value float-right"><span class="char-count">0</span> / 120 </small>
               <span class="invalid-feedback"></span>
            </div>

       
			
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-0 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
          <?php echo form_close(); ?>		
        <!--/ form -->
      </div>
    </div>
    </div>
</div>

