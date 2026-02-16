<?php
// $param2 = lesson id and $param3 = course id
?>

<script>
var curr_adj_remark = $('#adj_remark_'+<?= $param3;?>).val();
$('#modal_adj_remark').val(curr_adj_remark);
</script>

<form action="#"  onsubmit="return checkForm(this);" method="post" class="add-ajax-modal-form"  enctype="multipart/form-data">

	<div class="col-12 col-sm-12 mb-1 new">
	  <div class="form-group">
        <label><?php echo get_phrase('adjustment_remark'); ?> <i class="required">*</i></label>
        <textarea name="adj_remark" id="modal_adj_remark" class="form-control" required></textarea>
         </div>
	</div>
    

  <div class="row text-center mt-2"> 
    <div class="col-12">
      <button type="submit" class="btn btn-primary mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
    </div>
  </div> 


</form>

<script>
    $('.add-ajax-modal-form').submit(function(e) {
        e.preventDefault();  
        $(".loader").show(); 
        $('.btn_verify').attr("disabled", true)
        $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
        var url = $(this).attr('action');		
		var adj_remark = $('#modal_adj_remark').val();
		$('#adj_remark_'+<?= $param3;?>).val(adj_remark); //
		
		
		 $('.btn_verify').html('Submit');
		   $('.btn_verify').attr("disabled", false);
		   $(".loader").fadeOut("slow"); 
		   Swal.fire({
			title: "Success!",
			text: 'Adjustment Remark Added Successfully',
			icon: "success",
			customClass: {
				confirmButton: "btn btn-primary"
			},
			buttonsStyling: !1
		  }).then(() => { $('#small-modal').modal('hide');}); 
            	  
        return false;
    });
	
</script>   	