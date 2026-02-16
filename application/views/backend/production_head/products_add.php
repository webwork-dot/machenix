
<div class="row">
  <div class="col-12">
    <!-- profile -->
    <div class="card">
      <div class="card-body py-1 my-0">

          <?php echo form_open('production_head/products/add_post', ['class' => 'add-ajax-redirect-form','onsubmit' => 'return checkForm(this);']);?>  
          <div class="row">
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label class="form-label">Product Name <span class="required">*</span></label>
                    <input type="text" class="form-control" placeholder="Enter Product Name" name="name" required="">
                </div>
            </div>
            
          
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label">Select Unit <span class="required">*</span></label>
              <select class="form-select select2" name="unit" required>
                <option value="">Select Unit </option>
                <?php foreach($units_list as $item){?>
                <option value="<?php echo $item->name;?>"><?php echo $item->name;?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-12 col-sm-4 mb-1">
              <label class="form-label">Select Department <span class="required">*</span></label>
              <select class=" form-select" name="dept" required>
                <option value="">Select Department </option>
                <?php foreach($department_list as $item){?>
                <option value="<?php echo $item->name;?>"><?php echo $item->name;?></option>
                <?php }?>
              </select>
            </div>     
            
		
			<div class="col-12 col-sm-4 mb-2">
				<label class="form-label">Standard Batch Size<i class="required">*</i></label>
				<div class="input-group">
				   <input type="text" class="form-control" placeholder="Batch Size" name="std_batch_size" required="">
					<select class="form-select" name="std_batch_size_unit" required>
					<option value="">Select Unit </option>
					<?php foreach($units_list as $item){?>
					<option value="<?php echo $item->name;?>"><?php echo $item->name;?></option>
					<?php }?>
				  </select>
				</div>
			</div>

                      
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label class="form-label">Shelf Life </label>
                    <input type="text" class="form-control" placeholder="Enter Shelf Life" name="shelf_life">
                </div>
            </div>
                        
            <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label class="form-label">Product Code <span class="required">*</span></label>
                    <input type="text" class="form-control" placeholder="Enter Product Code" name="code" required="">
                </div>
            </div>
			
	   	  <div class="row mt-20 mb-10">	 
			  <div class="card-body"> 
				<div class="row">
				   <div class="col-md-12">
					  <h5 class="mb-2 m-title"><b>General Details</b></h5> 
				   </div>
				</div>
				
			
				<div class="row">				
			     <div class="table-responsivex mt-1 mb-2">
					 <table class="table table-bordered mn-table" id="ingredients_area">
						<thead>
						   <tr>
							  <th style="width:50%">Active Ingredients</th>
							  <th>Form</th>
							  <th>REF.</th>
							  <th style="width:12%">PART USED</th>
							  <th style="width:12%">LABEL CLAIM</th>
							  <th style="width:35px"></th>
						   </tr>
						</thead>
						<tbody class="element-ingredients-1 q-table" id="ingredient_1">
						   <tr>
							  <td>
								 <span class="">
									<input type="hidden" name="item_other_id[]" value="">
									<select class="form-select" name="product_id[]" id="product_id_1" onchange="get_raw_product_details(this.value,1)" placeholder="Raw Products" required>
									   <option value="">Select Raw</option>
									   <?php  foreach($raw_list as $out){ ?>
									   <option value="<?= $out['id'];?>"><?= $out['name'];?></option>
									   <?php  } ?>
									</select>
								 </span>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="form_1" name="form[]" placeholder="Form" value="" class="form-control" readonly="readonly" required>
								 </p>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="ref_1" name="ref[]" placeholder="REF" value="" class="form-control" required>
								 </p>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="part_used_1" name="part_used[]" placeholder="Part Used" value="" class="form-control" required>
								 </p>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="label_claim_1" name="label_claim[]" placeholder="Label Claim" value="" class="form-control" required>
								 </p>
							  </td>
							  <td></td>
						   </tr>
						</tbody>
					 </table>
					 <hr>
					 <div class="row">
						<center>
						   <div class="col-md-12 mt-0 mb-2 pl-0 m-auto">
							  <button type="button" class="btn btn-outline-primary waves-effect mb-1" onclick="appendIngredients()"><i class="fa fa-plus" aria-hidden="true"></i> Add Active Ingredients</button>
						   </div>
						</center>
					 </div>
				  </div>
				  
							  
							 
     
                     <!-- Indoor Product Table and Button can be added similarly -->
                     <div class="table-responsivex mt-1  mb-2">
					 <table class="table table-bordered mn-table" id="excipient_area">
						<thead>
						   <tr>
							  <th style="width:50%">Excipient/Base</th>
							  <th>Form</th>
							  <th>REF.</th>
							  <th style="width:12%">PART USED</th>
							  <th style="width:12%">LABEL CLAIM</th>
							  <th style="width:35px"></th>
						   </tr>
						</thead>
						<tbody class="element-excipient-1 q-table" id="excipient_1">
						   <tr>
							  <td>
								 <span class="">
									<input type="hidden" name="ex_item_other_id[]" value="">
									<select class="form-select" name="ex_product_id[]" id="ex_product_id_1" onchange="get_ex_raw_product_details(this.value,1)" placeholder="Raw Products" required>
									   <option value=""  data-capacity="0">Select Raw</option>
									   <?php  foreach($raw_list as $out){ ?>
									   <option value="<?= $out['id'];?>"><?= $out['name'];?></option>
									   <?php  } ?>
									</select>
								 </span>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="ex_form_1" name="ex_form[]" placeholder="Form" value="" class="form-control" readonly="readonly" required>
								 </p>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="ex_ref_1" name="ex_ref[]" placeholder="REF" value="" class="form-control" required>
								 </p>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="ex_part_used_1" name="ex_part_used[]" placeholder="Part Used" value="" class="form-control" required>
								 </p>
							  </td>
							  <td>
								 <p class="td-blank">
									<input type="text" id="ex_label_claim_1" name="ex_label_claim[]" placeholder="Label Claim" value="" class="form-control" required>
								 </p>
							  </td>
							  <td></td>
						   </tr>
						</tbody>
					 </table>
					 <hr>
					 <div class="row">
						<center>
						   <div class="col-md-12 mt-0 mb-2 pl-0 m-auto">
							  <button type="button" class="btn btn-outline-primary waves-effect mb-1" onclick="appendExcipient()"><i class="fa fa-plus" aria-hidden="true"></i> Add Excipient/Base</button>
						   </div>
						</center>
					 </div> 
				  </div>
			  </div>					
		    </div>
		  </div>
		
				
		   <div class="row">
               <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label class="form-label">Permissible Yield</label>
                    <input type="text" class="form-control" placeholder="Permissible Yield" name="per_yield">
                </div>
			  </div> 
			  <div class="col-12 col-sm-4 mb-1">
                <div class="form-group">
                    <label class="form-label">Pack Size</label>
                    <input type="text" class="form-control" placeholder="Pack Size" name="pack_size">
                </div>
			  </div>  

			  <div class="col-12 col-sm-4 mb-1">
				  <label for="status" class="form-label">Status <span class="required">*</span></label>
				  <select id="status" class="form-select" name="status" required>
					<option value="1">Active</option>
					<option value="0">Inactive</option>
				  </select>
				 </div>
            </div>
            
          	
			
			
            <div class="col-12 mb-2">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
          <?php echo form_close(); ?>		
        <!--/ form -->
      </div>
    </div>
    </div>
</div>

<script>

function get_raw_product_details(b,nextindex) {
	$(".loader").show();
	
	var a = {
	  id: b,
	};
	  $.ajax({
	  type: "POST",
	  url:   "<?= base_url()?>production_head/get_raw_product_details_by_id",
	  data: a,
	  success: function(res) {
		  $(".loader").fadeOut("slow"); 
		  if(res.status == 200){
			   $('#form_'+nextindex).val(res.form); 
			}
			else{
			  $('#form_'+nextindex).val('');
			}
		}
	});
}
 
function get_ex_raw_product_details(b,nextindex) {
	$(".loader").show();
	var a = {
	  id: b,
	};
	  $.ajax({
	  type: "POST",
	  url:   "<?= base_url()?>production_head/get_raw_product_details_by_id",
	  data: a,
	  success: function(res) {
		  $(".loader").fadeOut("slow"); 
		  if(res.status == 200){
			   $('#ex_form_'+nextindex).val(res.form); 
			}
			else{
			  $('#ex_form_'+nextindex).val('');
			}
		}
	});
}

function appendIngredients() {
    $(".loader").show();   
    var total_element = $(".element-ingredients-1").length+1;  

    
    var lastid = $(".element-ingredients-1:last").attr("id");
    var split_id = lastid.split("_");
    //console.log(Number(split_id[1]));
    var nextindex = Number(split_id[1]) + 1;
	//console.log(nextindex);
	var i=nextindex;
  
    var newRow = `<tbody class="element-ingredients-1 q-table" id="ingredient_${i}">
        <tr>
            <td>
                <span class="">
                    <input type="hidden" name="item_other_id[]" value="">
                    <select class="form-select" name="product_id[]" id="product_id_${i}" onchange="get_raw_product_details(this.value,${i})" placeholder="Outdoor" required>
                        <option value="">Select Raw</option>
						   <?php  foreach($raw_list as $out){ ?>
					   <option value="<?= $out['id'];?>"><?= $out['name'];?></option>
					   <?php  } ?>
                    </select>
                </span>
            </td>
            <td>
                <p class="td-blank">
					<input type="text" id="form_${i}" name="form[]" placeholder="Form" value="" class="form-control" readonly="readonly" required>
                </p>
            </td>
            <td>
                <p class="td-blank">
					<input type="text" id="ref_${i}" name="ref[]" placeholder="REF" value="" class="form-control" required>
                </p>
            </td>   
			<td>
                <p class="td-blank">
					<input type="text" id="part_used_${i}" name="part_used[]" placeholder="Part Used" value="" class="form-control" required>
                </p>
            </td>
			<td>
                <p class="td-blank">
					<input type="text" id="label_claim_${i}" name="label_claim[]" placeholder="Label Claim" value="" class="form-control" required>
                </p>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm" style="margin-top: 0px;" name="button" onclick="removeIngredients(this)"> <i class="fa fa-minus"></i> </button></td>
        </tr>
    </tbody>
    `;
    $('#ingredients_area').append(newRow);
    
    $(".loader").fadeOut("slow");
}	

function removeIngredients(requirementElem) {
   $(requirementElem).parent().parent().remove();
}



function appendExcipient() {
    $(".loader").show();   
    var total_element = $(".element-excipient-1").length+1;  

    
    var lastid = $(".element-excipient-1:last").attr("id");
    var split_id = lastid.split("_");
    //console.log(Number(split_id[1]));
    var nextindex = Number(split_id[1]) + 1;
	//console.log(nextindex);
	var i=nextindex;
  
    var newRow = `<tbody class="element-excipient-1 q-table" id="ingredient_${i}">
        <tr>
            <td>
                <span class="">
                    <input type="hidden" name="ex_item_other_id[]" value="">
                    <select class="form-select" name="ex_product_id[]" id="ex_product_id_${i}" onchange="get_ex_raw_product_details(this.value,${i})" placeholder="Product" required>
                        <option value="">Select Raw</option>
						   <?php  foreach($raw_list as $out){ ?>
					   <option value="<?= $out['id'];?>"><?= $out['name'];?></option>
					   <?php  } ?>
                    </select>
                </span>
            </td>
            <td>
                <p class="td-blank">
					<input type="text" id="ex_form_${i}" name="ex_form[]" placeholder="Form" value="" class="form-control" readonly="readonly" required>
                </p>
            </td>
            <td>
                <p class="td-blank">
					<input type="text" id="ex_ref_${i}" name="ex_ref[]" placeholder="REF" value="" class="form-control" required>
                </p>
            </td>   
			<td>
                <p class="td-blank">
					<input type="text" id="ex_part_used_${i}" name="ex_part_used[]" placeholder="Part Used" value="" class="form-control" required>
                </p>
            </td>
			<td>
                <p class="td-blank">
					<input type="text" id="ex_label_claim_${i}" name="ex_label_claim[]" placeholder="Label Claim" value="" class="form-control" required>
                </p>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm" style="margin-top: 0px;" name="button" onclick="removeExcipient(this)"> <i class="fa fa-minus"></i> </button></td>
        </tr>
    </tbody>
    `;
    $('#excipient_area').append(newRow);
    
    $(".loader").fadeOut("slow");
}	

function removeExcipient(requirementElem) {
   $(requirementElem).parent().parent().remove();
} 
</script>