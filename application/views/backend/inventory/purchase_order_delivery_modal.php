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
<?php echo form_open('inventory/purchase_order_received_data', ['class' => 'add-new-form','onsubmit' => 'return checkForm(this);']);?> 
<input type="hidden" name="parent_id" value="<?php echo $param2; ?>" >
<table class="table table-striped table-bordered table-hover mt-2" id = "requirement_area">
    <thead>
        <tr>
            <td>Name</td>
            <td>Size</td>
            <td>Quantity</td>
            <td>Total Amount</td>
            <td>Pending / Status</td>
            <td>Received</td>
            <td>Invoice No</td>
            <td>Received Amount</td>
            <td>Received Date</td>
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
    $(document).ready(function(){
        get_product();
    });
    
    function get_product() {
        $(".loader").show();
        var a = {
          id : <?php echo $param2; ?>,
        };
          $.ajax({
          type: "POST",
          url:   "<?php echo base_url()?>inventory/get_purchase_order_product",
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
	
	function get_check_rcv_qty() {
		var is_disabled = 0 ;
		var total_element = $(".element-1").length + 1; 
		//console.log('lenght:',total_element);
        for (let i = 1; i < total_element ; i++) {
			var product_id = $("#id_"+i).val();
			var rcv_quantity = parseInt($("#rcv_quantity_"+product_id).val());
			var final_quantity = parseInt($("#final_quantity_"+product_id).val());
			var quantity = parseInt($("#quantity_"+product_id).val());
			var total_amount = parseFloat($("#total_amount_"+product_id).val());
			var new_amount = total_amount /  final_quantity ;
			var final_amount = new_amount * rcv_quantity;
			$("#received_amount_"+product_id).val(final_amount.toFixed(2));
			if(rcv_quantity > quantity){
				
				is_disabled = 1 ;
			}
        }
        if(is_disabled == 1){
            alert('Recieved quantity cannot greater than pending quantity')
            $(':input[type="submit"]').prop('disabled', true);
        }else{
            $(':input[type="submit"]').prop('disabled', false);
        }
    }
	
	function get_check_rcv_multi_qty() {
		var is_disabled = 0 ;
		var total_element = $(".element-1").length + 1; 
		console.log('total_element :',total_element);
		//console.log('lenght:',total_element);
        for (let i = 1; i < total_element ; i++) {
			var quantity = 0;
			var product_id = $("#id_"+i).val();
			var rcv_quantity = 0;
			var r_quantity = 0;
			var x_class = '.multi-qty-'+product_id;
			$(x_class).each(function(index, currentElement) {
				r_quantity = parseInt($(this).val());
				console.log('rcv_quantity :',r_quantity);
				rcv_quantity += r_quantity;
				var dataId = $(this).attr('data-id');
				var final_quantity = parseInt($("#final_quantity_"+product_id).val());
				var total_amount = parseFloat($("#total_amount_"+product_id).val());
				var new_amount = total_amount /  final_quantity ;
				var final_amount = new_amount * r_quantity;
				$("#received_amount_"+dataId).val(final_amount.toFixed(2));
			});
			
			quantity = parseInt($("#quantity_"+product_id).val());
			
			//console.log('x_product_id :',rcv_quantity);
			if(rcv_quantity > quantity){
				//console.log('product_id :',product_id);
				//console.log('rcv_quantity :',rcv_quantity);
				//console.log('quantity :',quantity);
				is_disabled = 1 ;
			}
        }
        if(is_disabled == 1){
            alert('Recieved quantity cannot greater than pending quantity')
            $(':input[type="submit"]').prop('disabled', true);
        }else{
            $(':input[type="submit"]').prop('disabled', false);
        }
    }
	
	$(document).ready(function(){
        $(".batch_no").on("input", function(){
            $(this).val($(this).val().toUpperCase());
        });
    });
    
</script>