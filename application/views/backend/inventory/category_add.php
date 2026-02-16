<style>.select2-container--default .select2-selection--single { margin-bottom: 10px;}</style>

<div class="mobile_view home">


<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
          <?php echo form_open('inventory/category/add_post', ['class' => 'add-ajax-redirect-image-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
          
          <div class="row">
				<div class="col-12 col-sm-4">
				   <div class="form-group">
						<label class="control-label">Name <span class="required">*</span></label>
						<input type="text" class="form-control" name="name" placeholder="Name" required>
				   </div>
				</div>
			
				<div class="col-12 col-sm-4">
				   <div class="form-group">
					  <label class="control-label">Status <span class="required">*</span></label>
					  <select name="status" class="select2" required="">
						<option value="1">Active</option>
						<option value="0">Inactive</option>
					  </select>
				   </div>
				</div> 
            </div>
            
          <div class="row">
              
            <div class="col-12 col-sm-4">
               <div class="form-group">
                  <label class="mb-10"><?php echo get_phrase('parent_category'); ?></label>
                  <select class="select2 form-control mb-10" name="parent_id[]"  data-toggle="select2" onchange="get_subcategories(this.value, 0);" required>
					<option value="0"><?php echo get_phrase('none'); ?></option>
					<?php foreach ($parent_categories as $parent_category): ?>
						<option value="<?php echo $parent_category->id;?>"><?php echo $parent_category->name; ?></option>
					<?php endforeach; ?>
				</select>
				<div class="" id="subcategories_container"></div>
               </div>
            </div>
			
			<div class="col-12 col-sm-4 mb-2">
               <div class="form-group">
                    <label class="control-label">Image <span class="required">*</span></label>
                    <input type="file" class="form-control" name="image" accept="image/*">
               </div>
            </div>
				
            <div class="col-12">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mb-10 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
        <?php echo form_close(); ?>		
      </div>
    </div>
    </div>
</div>
 
</div>
 
 <script>
    function get_subcategories(category_id, data_select_id) {
        var subcategories = get_subcategories_array(category_id);
        var date = new Date();
        //reset subcategories
        $('.subcategory-select').each(function () {
            if (parseInt($(this).attr('data-select-id')) > parseInt(data_select_id)) {
                $(this).remove();
            }
        });
        if (category_id == 0) {
            return false;
        }
        if (subcategories.length > 0) {
            var new_data_select_id = date.getTime();
            var select_tag = '<select class="form-control subcategory-select select2 mb-10" data-toggle="select2"  data-select-id="' + new_data_select_id + '" name="parent_id[]" onchange="get_subcategories(this.value,' + new_data_select_id + ');">' +
                '<option value=""><?php echo get_phrase('none'); ?></option>';
            for (i = 0; i < subcategories.length; i++) {
                select_tag += '<option value="' + subcategories[i].id + '">' + subcategories[i].name + '</option>';
            }
            select_tag += '</select>';  
            $('#subcategories_container').append(select_tag);
            //$(".select2").select2()
        }
    }

    function get_subcategories_array(category_id) {
        var categories_array = <?php echo get_categories_json(); ?>;
        var subcategories_array = [];
        for (i = 0; i < categories_array.length; i++) {
            if (categories_array[i].parent_id == category_id) {
                subcategories_array.push(categories_array[i]);
            }
        }
        console.log(subcategories_array);
        return subcategories_array;
    }
</script>

 