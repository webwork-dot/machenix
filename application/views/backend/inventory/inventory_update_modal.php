<style>
    .jq-toast-wrap {
        display: block;
        position: fixed;
        width: 250px;
        pointer-events: none!important;
        letter-spacing: normal;
        z-index: 999999999!important;
    }
    input:read-only {
        background-color: #eee;
        border: 1px solid #ddd;
    }
    input{
       padding: 2px 5px;
    }
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
	.full-width-modal {
		max-width: 1300px!important;
	}
    
    /* Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }
</style>

<?php
    $data = $this->common_model->getRowById('inventory', '*', ['id' => $param2]);
    // echo json_encode($data);
?>

<?php echo form_open('inventory/update_inventory_product', ['class' => 'add-new-form','onsubmit' => 'return checkForm(this);']);?> 
<input type="hidden" name="parent_id" value="<?php echo $param2; ?>" >
<input type="hidden" name="curr_qty" value="<?php echo $data['quantity']; ?>" >

<div class="row mt-2">
    <div class="col-md-12">
      <div class="form-group">
         <h4>Product Details: </h4>
         <p class="mb-0"><?php echo $data['product_name'] . ' - ' . $data['size_name']; ?></p>
         <p class="mb-0"><b>Current Qty:-</b> <?php echo $data['quantity']; ?></p>
      </div>
    </div>
  
    <div class="col-md-6">
      <div class="form-group">
         <label>Type </label>
         <select class="form-control" name="manual" id="manual">
             <option value="manual_in">Add Quantity</option>
             <option value="manual_out">Remove Quantity</option>
         </select>
      </div>
    </div>
   
    <div class="col-md-6">
      <div class="form-group">
         <label>Quantity<span class="required">*</span></label>
         <input type="number" class="form-control" placeholder="Enter Quantity" id="qty" name="qty" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" required>
      </div>
    </div>
    
    <div class="col-md-12">
        <button type="submit" class="btn btn-primary btn_verify" name="btn_verify">Submit</button>
    </div>
</div>

</form>	


<script>

    $('.add-new-form').submit(function(e) {
        e.preventDefault(); 
        
        let manualType = document.querySelector('#manual').value;
        let qty = document.querySelector('#qty').value;
        
        if(qty == 0) {
            Swal.fire({
    			title: "Warning!",
    			text: "Enter Quantity!" ,
    			icon: "warning",
    			customClass: {
    				confirmButton: "btn btn-primary"
    			},
    			buttonsStyling: !1
    		}); 
    		
    		$('.btn_verify').html('Submit');
            $('.btn_verify').attr("disabled", false);
            $(".loader").fadeOut("slow"); 
        } else if(manualType == 'manual_out' && parseInt(qty) > parseInt('<?php echo $data['quantity']; ?>')) {
            Swal.fire({
    			title: "Error!",
    			text: "Quantity cannot be greater than current quantity!" ,
    			icon: "error",
    			customClass: {
    				confirmButton: "btn btn-primary"
    			},
    			buttonsStyling: !1
    		});
    		
    		$('.btn_verify').html('Submit');
            $('.btn_verify').attr("disabled", false);
            $(".loader").fadeOut("slow"); 
        } else {
            $(".loader").show(); 
            $('.btn_verify').attr("disabled", true)
            $('.btn_verify').html('<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i> Processing');
            var url = $(this).attr('action');
            $.ajax({
                type: 'POST',
                url: url,
                async: true,
                dataType: 'json',
                data: $(".add-new-form").serialize(),
                success: function(res) {
                    if (res.status == '200') {
                        $(".loader").fadeOut("slow"); 
                        $(".loader").fadeOut("slow"); 
    					Swal.fire({
    						title: "Success!",
    						text: "",
    						icon: "success",
    						customClass: {
    							confirmButton: "btn btn-primary"
    						},
    						buttonsStyling: !1
    					}).then(() => { location.reload()});
                    }else{
                        Swal.fire({
                			title: "Error!",
                			text: "Something Went Wrong !!!" ,
                			icon: "error",
                			customClass: {
                				confirmButton: "btn btn-primary"
                			},
                			buttonsStyling: !1
                		});
                		
    					$('.btn_verify').html('Submit');
    					$('.btn_verify').attr("disabled", false);
    					$(".loader").fadeOut("slow"); 
    				}
                }
            });
            return false;
        }
        
    });
	
    
</script>