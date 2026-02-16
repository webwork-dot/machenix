  <?php echo form_open_multipart('hr/calls/update_calls/'.$param2, ['class' => 'modal-ajax-redirect-image','onsubmit' => 'return checkForm(this);']);?>
    <div class="row mb-2">

          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Date <i class="required">*</i></label>
            <input type="date" class="form-control" placeholder="Followup Date" name="followup_date"  min="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Time <i class="required">*</i></label>
            <input type="time" class="form-control" name="followup_time" min="<?php echo date('h:i');?>" required>
          </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark</label>
            <textarea class="form-control" rows="3" name="remark" placeholder="Remark" ></textarea>
          </div>

          <div class="col-12 text-center">
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>

<?php echo form_close(); ?>

<script type="text/javascript">
	  $('.modal-ajax-redirect-image').submit(function(e) {
        e.preventDefault();  
          $(".loader").show(); 
          $('.btn_verify').attr("disabled", true)
          $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
          var url = $(this).attr('action');
   
         // Get form
        var form = $('.modal-ajax-redirect-image')[0];

        // FormData object 
         var data = new FormData(form);
        
        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            dataType: 'json',
            data: data,     
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status == '200') { 
                  $(".loader").fadeOut("slow"); 
                  Swal.fire({
            		title: "Success!",
            		text: res.message,
            		icon: "success",
            		customClass: {
            			confirmButton: "btn btn-primary"
            		},
            		buttonsStyling: !1
            	  }).then(() => {window.location.href = res.url;});
                }
                else {    
                   Swal.fire({
            			title: "Error!",
            			text: res.message ,
            			icon: "error",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})
                    $('.btn_verify').html('Submit');
                    $('.btn_verify').attr("disabled", false);
                    $(".loader").fadeOut("slow"); 
                }
            }
        });
        return false;
    }); 
</script>