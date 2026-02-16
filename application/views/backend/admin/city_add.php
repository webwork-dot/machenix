<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('admin/city/add_post', ['class' => 'add-ajax-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">Select State</label>
              <select class="select2 form-select" name="state_id" onchange="get_city_(this.value);">
                <option value="">Select State</option>
                <?php foreach($states as $state){?>
                <option value="<?php echo $state['id'];?>"><?php echo $state['name'];?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="city">District</label>
              <input type="text" class="form-control" id="district" name="district" placeholder="District" required>
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


<script>
   function get_city_(b) {
       var a = {
           state_id: b
       };
       $.ajax({
           type: "POST",
           url: "<?php echo base_url();?>staff/get_cities",
           data: a,
           success: function(c) {
               $("#states_").children("option:not(:first)").remove();
               $("#states_").append(c);
           }
       })
   } 
</script>   
    