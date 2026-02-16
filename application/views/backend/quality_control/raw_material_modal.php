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
<?php echo form_open('quality_control/complete_raw_material_product', ['class' => 'add-new-form','onsubmit' => 'return checkForm(this);']);?> 
<input type="hidden" name="parent_id" value="<?php echo $param2; ?>" >
<table class="table table-striped table-bordered table-hover mt-2" id = "requirement_area">
    <thead>
        <tr>
            <th>Date</th>
			<th>Voucher No</th>
			<th>Supplier Name</th>
			<th>Product Name</th>
			<th>Quantity</th>
			<th>Bill No.</th>
			<th>Batch No.</th>
			<th>Expiry Date</th>
			<th>Approved Date</th>
			<th>Sample Qty</th>
        </tr>
    </thead>
    <tbody class="new-table" id="po_order_product">
        
    </tbody>
</table>
<div class="col-md-12">
    <button type="submit" class="btn btn-primary btn_verify" name= "btn_verify">Submit</button>
</div>
</form>	


<script>
    $( document ).ready(function() {
        get_product();
    });
    
    function get_product() {
        $(".loader").show();
        var a = {
          id : <?php echo $param2; ?>,
        };
          $.ajax({
          type: "POST",
          url:   "<?php echo base_url()?>quality_control/get_raw_material_product",
          data: a,
          success: function(res) {
                $('#po_order_product').find('tr').remove();
                $('#po_order_product').append(res);
				$(".loader").fadeOut("slow"); 
            }
        });
    }
    
    $('.add-new-form').submit(function(e) {
        e.preventDefault(); 
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
    });
	
	
    
</script>