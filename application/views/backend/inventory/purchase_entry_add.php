<style>
    .error
    {
        color: red;
    }
    
	  
</style>
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">
            
          <?php echo form_open('inventory/purchase_entry/add_post', ['class' => 'add-ajax-redirect-image-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            
            
           <div class="col-12 col-sm-4 mb-1">
              <label class="form-label" for="state">Supplier <span class="required">*</span></label>
              <select class=" form-select select2" name="supplier_id" id="supplier_id" required>
                <option value="">Select Supplier </option>
                <?php foreach($supplier_list as $item){?>
                <option value="<?php echo $item->id;?>"><?php echo $item->name;?></option>
                <?php }?>
              </select>
            </div>
			
			<div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Invoice Number <span class="required">*</span></label>
                    <input type="text" class="form-control" placeholder="Invoice Number" name="invoice_number">
                </div>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Invoice Date <span class="required">*</span></label>
                    <input type="date" class="form-control" name="invoice_date" max="<?php echo date('Y-m-d');?>" value="<?php echo date('Y-m-d');?>" id="date_picker">
                </div>
            </div>
			
			<div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label>Invoice Amount <span class="required">*</span></label>
                    <input type="number" step="any" class="form-control" placeholder="Invoice Amount" name="invoice_amount">
                </div>
            </div>
			
			<div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label >Document <small class="error">(png,jpg,jpeg,pdf Only)</small></label>
					<input type="file" class="form-control" name="image"  accept=".gif, .jpg, .png, jpeg,.pdf">
                </div>
            </div>
			
			
            
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
          <?php echo form_close(); ?>		
        <!--/ form -->
      </div>
    </div>
    </div>
</div>