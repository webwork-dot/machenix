
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<div class="row">
  <div class="col-12">
    <!-- profile -->

          <?php echo form_open('salary_staff/add_previous_pl/'.$param2, ['class' => 'add-ajax-modal-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
          <div class="row">
            <div class="col-12 col-sm-12 mb-2">
              <label class="form-label" for="name">Paid Leave <span class="required">*</span></label>
              <input type="text" min="1" max="30" step="any" onkeypress="return isNumberKey(event,this)" class="form-control m-deduct" name="paid_leave" required="">
               <span class="invalid-feedback"></span>
            </div>

            
            <div class="col-12 col-sm-12 mb-2">
              <label class="form-label" for="company">PL Month <span class="required">*</span></label>
              <input type="text" class="form-control flatpickr-monthYear" name="pl_date" placeholder="PL Month" required>
               <span class="invalid-feedback"></span>
            </div>   

      
            <div class="col-12 mb-2">
                <button type="submit" class="dt-button add-new btn btn-primary waves-effect waves-float waves-light mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
            </div>
          </div>
          <?php echo form_close(); ?>		
        <!--/ form -->
      </div>
  
</div>
    
<script>
   function isNumberKey(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46))
        return false;
    else {
        var len = $(element).val().length;
        var index = $(element).val().indexOf('.');
        if (index > 0 && charCode == 46) {
            return false;
        }
        if (index > 0) {
            var CharAfterdot = (len + 1) - index;
            if (CharAfterdot > 100) {
                return false;
            }
        }

    }
    return true;
}

 $(document).ready(function(){
	var currentDate = new Date();
	var lastDayOfPreviousMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 0);

	$('.flatpickr-monthYear').flatpickr({
	 plugins: [
		new monthSelectPlugin({
		  shorthand: true,
		  allowInput: true,
		  dateFormat: "m-Y",
		  altFormat: "F Y",
		  theme: "dark"
		})
	  ],
	   maxDate:lastDayOfPreviousMonth
	}); 
	
});



    $('.add-ajax-modal-form').submit(function(e) {
        e.preventDefault();  
        $(".loader").show(); 
        $('.btn_verify').attr("disabled", true)
        $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
        var url = $(this).attr('action');
        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            dataType: 'json',
            data: $(".add-ajax-modal-form").serialize(),
            success: function(res) {
                if (res.status == '200') {
                   $('.btn_verify').html('Submit');
                   $('.btn_verify').attr("disabled", false);
                   $(".loader").fadeOut("slow");                
					Swal.fire({
						title: "Success!",
						text: res.message,
						icon: "success",
						customClass: {
							confirmButton: "btn btn-primary"
						},
						buttonsStyling: !1
					  }).then(() => {  location.reload();});
            	  

                  
                }
                else {	
                    $.each(res.errors, function(key, value){
                        $('[name="'+key+'"]').addClass('is-invalid'); //select parent twice to select div form-group class and add has-error class
                        $('[name="'+key+'"]').next().html(value); //select span help-block class set text error string
                        if(value == ""){
                            $('[name="'+key+'"]').removeClass('is-invalid');
                            $('[name="'+key+'"]').addClass('is-valid');
                        }
                    });
                    Swal.fire({
            			title: "Error!",
						html: true,
            			html: res.message ,
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
   