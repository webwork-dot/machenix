<style>
.select2-container--default .select2-selection--single { margin-bottom: 10px;}
</style>
<link rel="stylesheet" type="text/css" href="<?= base_url();?>assets/css/main-2.1.css">

<div class="mobile_view home">

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-1 my-0">
         <?php echo form_open('inventory/category/edit_post/'.$id, ['class' => 'add-ajax-redirect-image-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
		 
          <div class="row">
            <div class="col-12 col-sm-4">
               <div class="form-group">
                    <label class="control-label">Name <span class="required">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="First Name" value="<?php echo $data['name']; ?>" required>
               </div>
            </div>
            <div class="col-12 col-sm-4">
               <div class="form-group">
                  <label class="control-label">Status <span class="required">*</span></label>
                  <select name="status" class="select2" required="">
                    <option value="1" <?php echo ($data['status'] == '1') ? 'selected':'';?>>Active</option>
                <option value="0" <?php echo ($data['status'] == '0') ? 'selected':'';?>>In Active</option>
                  </select>
               </div>
            </div>
            
            </div>
            
                        
          <div class="row">   
            <div class="col-12 col-sm-4 mb-2">
                <div class="">
					<label><?php echo get_phrase('parent_category'); ?></label>

                <div id="category_select_container">
                        <?php $parent_array = array();
                        if (!empty($category->parent_tree)) {
                            $parent_array = explode(',', $category->parent_tree);
                        }
                        array_push($parent_array, $category->id);
                        $level = 1;
                        foreach ($parent_array as $parent_id):
                            $parent_item = $this->category_model->get_category_by_id($parent_id);
                            if (!empty($parent_item)):
                                $subcategories = $this->category_model->get_subcategories_by_parent_id($parent_item->parent_id);
                                if (!empty($subcategories)): ?>
                                    <select name="parent_id[]" class="form-control subcategory-select " data-level="<?= $level; ?>" onchange="get_subcategories(this.value,'<?= $level; ?>');">
                                        <option value=""><?php echo trans('none'); ?></option>
                                        <?php foreach ($subcategories as $subcategory):
                                            if ($subcategory->id != $category->id):?>
                                                <option value="<?= $subcategory->id; ?>" <?= $subcategory->id == $parent_item->id ? 'selected' : ''; ?>><?= category_name($subcategory); ?></option>
                                            <?php endif;
                                        endforeach; ?>
                                    </select>
                                <?php endif;
                            endif;
                            $level++;
                        endforeach; ?>
                    </div>
				</div>
			</div>

            <div class="col-12 col-sm-4 mb-2">
                <div class="form-group">
                        <label class="control-label">Image <span class="required">*</span></label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                </div>
                <?php if($data['image']){?>
                    <img src="<?php echo base_url() . $data['image'];?>" height="70">
                <?php } ?>  
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

</div>
 
 <script>
    function get_subcategories(parent_id, level, div_container = 'category_select_container') {
        level = parseInt(level);
        var new_level = level + 1;
        var data = {
            'parent_id': parent_id
        };
        $.ajax({
            type: "POST",
            url: base_url + "inventory/get_subcategories",
            data: data,
            success: function (response) {
                $('.subcategory-select').each(function () {
                    if (parseInt($(this).attr('data-level')) > level) {
                        $(this).remove();
                    }
                });
                var obj = JSON.parse(response);
                if (obj.result == 1 && obj.html_content != '') {
                    var select_tag = '<select class="form-control  subcategory-select" data-level="' + new_level + '" name="parent_id[]" onchange="get_subcategories(this.value,' + new_level + ',\'' + div_container + '\');">' +
                        '<option value=""><?= trans('none'); ?></option>' + obj.html_content + '</select>';
                    $('#' + div_container).append(select_tag);
                }
            }
        });
    }
</script>