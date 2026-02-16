<script src="<?php echo base_url('app-assets/vendors/js/forms/select/select2.full.min.js');?> "></script>
<script src="<?php echo base_url('app-assets/js/core/app-menu.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/js/core/app.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/js/scripts/customizer.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/vendors/js/forms/validation/jquery.validate.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/js/scripts/pages/auth-login.js');?>"></script>
<script src="<?php echo base_url('app-assets/js/scripts/charts/chart-apex.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/js/scripts/charts/chart-chartjs.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/vendors/js/charts/apexcharts.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/vendors/js/charts/chart.min.js');?>"></script>

<script src="<?php echo base_url('app-assets/vendors/js/pickers/pickadate/picker.js');?>"></script>
<script src="<?php echo base_url('app-assets/vendors/js/pickers/pickadate/picker.date.js');?>"></script>
<script src="<?php echo base_url('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js');?>"></script>
<!--<script src="<?php echo base_url('app-assets/js/scripts/forms/pickers/form-pickers.min.js');?>"></script>-->
<script src="<?php echo base_url('app-assets/js/scripts/forms/form-select2.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/js/scripts/jquery-ui.js');?>"></script>


<!--<script src="<?php echo base_url('app-assets/vendors/js/calendar/fullcalendar.min.js');?>  "></script>-->
<!--<script src="<?php echo base_url('app-assets/js/scripts/pages/app-calendar-events.min.js');?> "></script>-->
<!--<script src="<?php echo base_url('app-assets/js/scripts/pages/app-calendar.min.js');?> "></script>-->

<script src="<?php echo base_url('app-assets/js/scripts/extensions/ext-component-toastr.min.js');?>"></script>

<script src="<?php echo base_url('app-assets/vendors/js/forms/repeater/jquery.repeater.min.js');?>"></script>
<script src="<?php echo base_url('app-assets/js/scripts/forms/form-repeater.min.js');?>"></script>


<script>
function clearValue(input) {
  if (input.value === '0') {
    input.value = '';
  }
}

function resetValue(input) {
  if (input.value === '') {
    input.value = '0';
  }
}
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
function isWholeNumberKey(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode == 46 || charCode == 45) {
        // Disallow decimal points (46) and negative signs (45)
        return false;
    }
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
}

    $(document).ready(function(){   
		$('.flatpickr-range').flatpickr({
            mode: "range",
        	dateFormat: "Y-m-d",
        }); 
        $('.flatpickr-basic').flatpickr({
        	dateFormat: "Y-m-d",
			allowInput: true, 
        	minDate: new Date()
        });  
		$('.flatpickr-min').flatpickr({
        	dateFormat: "Y-m-d",
			allowInput: true, 
        	minDate: "<?= date('Y-m-d');?>"
        }); 
		$('.flatpickr-max').flatpickr({
        	dateFormat: "Y-m-d",
			allowInput: true, 
        	maxDate: new Date()
        });  
		$('.flatpickr-all').flatpickr({
        	dateFormat: "Y-m-d",
        });   
		$('.flatpickr-time').flatpickr({
		    enableTime: true,
			noCalendar: true,
			dateFormat: "H:i",
			defaultDate: "<?= date('H:i');?>"
        }); 
        
         $(".allow_numeric").on("input", function(evt) {
          var self = $(this);
          self.val(self.val().replace(/[^\d].+/, ""));
          if ((evt.which < 48 || evt.which > 57)) 
          {
            evt.preventDefault();
          }
         });
         
         $(".allow_decimal").on("input", function(evt) {
          var self = $(this);
          self.val(self.val().replace(/[^0-9\.]/g, ''));
          if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) 
          {
            evt.preventDefault();
          }
         });
         
         
         $('.alphaonly').bind('keyup blur',function(){ 
           var node = $(this);
           node.val(node.val().replace(/[^a-zA-Z\s]/g,'') ); }
         );
         
     });
 
     
    $(".patient_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-patient-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Patients Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });   
  
    $(".candidate_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-candidate-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Candidate Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
	
   $(".pure_candidate_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-pure-candidate-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Staff Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
   
    $(".doctor_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-doctors-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });  
    
    $(".unpure_doctor_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-unpure-doctors-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
    
    
    $(".co_doctor_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>co-ajax-doctors-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
    
    $(".mgr_doctor_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>mgr-ajax-doctors-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });

      $(".pure_doctor_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-pure-doctors-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
      
     $(".pure_patient_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-pure-patient-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Patient Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
     
     $(".product_ajax").select2({ 
         minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-product-list",
            type: "POST",
            dataType: 'json',
            delay: 250,
            placeholder: "Search Products",
            dropdownPosition: 'bottom',
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    }); 
    
    
       $(".asm_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-asm-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search ASM Name",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
      
    
   
   $(".asm_state_wise_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-asm-state-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search ASM Name & Coordinator",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term,
                    state_id: $('#state_id_input').find(":selected").val()
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });    


    $(".product_type_ajax").select2({ 
         minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-type-product-list",
            type: "POST",
            dataType: 'json',
            delay: 250,
            placeholder: "Search Products",
            dropdownPosition: 'bottom',
            data: function (params) {
                var type = $('#type').find(":selected").val();
                return {
                    searchTerm: params.term,
                    type: type,
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    }); 
	
    $(".patients_product_ajax").select2({ 
         minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-patients-product-list",
            type: "POST",
            dataType: 'json',
            delay: 250,
            placeholder: "Search Products",
            dropdownPosition: 'bottom',
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    }); 


<?php if($page_name='dss_doctors'){?>
 $(".ajax_dss_venue").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-dss-venue-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search DSS Venue",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
<?php }?>    



<?php if($this->session->userdata('super_type')=='Digital Coordinator'){?>
   $(".digital_doctor_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>digital-ajax-doctors-list",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
    
     $(".digital_asm_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>digital-ajax-asm-list/self",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
    
     $(".digital_all_asm_ajax").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>digital-ajax-asm-list/all",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Doctor Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
<?php }?>      

     
    $(".ajax_users_by_role").select2({
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo base_url();?>ajax-users-by-role",
            type: "POST",
            dataType: 'json',
            placeholder: "Search Name & Mobile No",
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                    super_type: $('#super_type').val()
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
     });  
    
    function confirm_delete_(b,url) {
       var a = {
          id: b
      };
    
      var href = url;
      var confirmDlg = duDialog(null, "Are you sure?", {
        init: true,
        dark: false, 
        buttons: duDialog.OK_CANCEL,
        okText: 'Proceed',
        callbacks: {
          okClick: function(e) {
            $(".dlg-actions").find("button").attr("disabled",true);
            $(".ok-action").html('<i class="fa fa-spinner fa-pulse"></i> Please wait!');
            $(".loader").show();  
            $.ajax({
              type: 'POST',
              url: href,
              dataType: 'json', 
              data: a,
            })
            .done(function(res) {
              confirmDlg.hide();
              if (res.status == '200') {
                $(".loader").fadeOut("slow");  
        		$(".row-"+b).fadeOut("slow");
                 Swal.fire({
        			title: "Success!",
        			text: 'Mapping Deleted Successfully' ,
        			icon: "success",
        			customClass: {
        				confirmButton: "btn btn-primary"
        			},
        			buttonsStyling: !1
        		}); 
        		
              } else {
                $(".loader").fadeOut("slow");  
                Swal.fire({
        			title: "Error!",
        			text: res.message ,
        			icon: "error",
        			customClass: {
        				confirmButton: "btn btn-primary"
        			},
        			buttonsStyling: !1
        		})
              }
            })
            .fail(function(response) {
                $(".loader").fadeOut("slow");  
                Swal.fire({
        			title: "Error!",
        			text: res.message ,
        			icon: "error",
        			customClass: {
        				confirmButton: "btn btn-primary"
        			},
        			buttonsStyling: !1
        		})
            });
          }
        }
      });
      confirmDlg.show();
   } 
  
  function checkForm(form) // Submit button clicked
  {
    form.btn_verify.disabled = true; 
	$('.btn_verify').attr("disabled", true);
	$('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
     
    return true;
  } 
  
    function checkFm(form) // Submit button clicked
  {
    form.btn_vefy.disabled = true; 
	$('.btn_vefy').attr("disabled", true);
	$('.btn_vefy').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
     
    return true;
  } 
  
  function checkFormLoader(form) {
    $(".loader").show(); 
    form.btn_verify.disabled = true; 
	$('.btn_verify').attr("disabled", true);
	$('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
    $(".loader").fadeOut("slow"); 
    return true;
  }
    
        $('.add-ajax-form').submit(function(e) { 
        e.preventDefault();  
		var buttonText=$(".btn_verify").val().trim()==="" ? "Submit":$(".btn_verify").val();
		
        $(".loader").show(); 
        $('.btn_verify').attr("disabled", true)
        $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
        var url = $(this).attr('action');
        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            dataType: 'json',
            data: $(".add-ajax-form").serialize(),
            success: function(res) {
                if (res.status == '200') {
                    $(".loader").fadeOut("slow"); 
                    location.reload();
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
                    $('.btn_verify').html(buttonText);
                    $('.btn_verify').attr("disabled", false);
                    $(".loader").fadeOut("slow"); 
                }
            }
        });
        return false;
    });
    
    
      
   $('.add-ajax-image-form').submit(function(e) {
        e.preventDefault();  
		var buttonText=$(".btn_verify").val().trim()==="" ? "Submit":$(".btn_verify").val();
		
          $(".loader").show(); 
          $('.btn_verify').attr("disabled", true)
          $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
          var url = $(this).attr('action');
   
         // Get form
        var form = $('.add-ajax-image-form')[0];

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
                    $('.btn_verify').html(buttonText);
                    $('.btn_verify').attr("disabled", false);
                    $(".loader").fadeOut("slow"); 
                }
            }
        });
        return false;
    }); 
    
     
      $('.add-ajax-datatable-form').submit(function(e) {
        e.preventDefault();  
		var buttonText=$(".btn_verify").val().trim()==="" ? "Submit":$(".btn_verify").val();
		
        $(".loader").show(); 
        $('.btn_verify').attr("disabled", true)
        $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
        var url = $(this).attr('action');
        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            dataType: 'json',
            data: $(".add-ajax-datatable-form").serialize(),
            success: function(res) {
                if (res.status == '200') {
                  $(".loader").fadeOut("slow"); 
                     Swal.fire({
            			title: "Success!",
            			text: res.message ,
            			icon: "success",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})
                  $('.btn_verify').html(buttonText);
                  $('.btn_verify').attr("disabled", false);
                
        		  var dataTable =  $('#report-datatable').DataTable(); 
        		  dataTable.draw();
         
                }
                else {	
                  $(".loader").fadeOut("slow");
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
                }
            }
        });
        return false;
    });
     
      

    $('.add-ajax-redirect-form').submit(function(e) {
        e.preventDefault();  
		var buttonText=$(".btn_verify").val().trim()==="" ? "Submit":$(".btn_verify").val();
		
        $(".loader").show(); 
        $('.btn_verify').attr("disabled", true)
        $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
        var url = $(this).attr('action');
        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            dataType: 'json',
            data: $(".add-ajax-redirect-form").serialize(),
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
                    $('.btn_verify').html(buttonText);
                    $('.btn_verify').attr("disabled", false);
                    $(".loader").fadeOut("slow"); 
                }
            }
        });
        return false;
    });
	
	
	  $('.add-ajax-redirect-image-form').submit(function(e) {
          e.preventDefault();  
		  var buttonText=$(".btn_verify").val().trim()==="" ? "Submit":$(".btn_verify").val();
		
          $(".loader").show(); 
          $('.btn_verify').attr("disabled", true);
          $('.btn_verify').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-25 align-middle">Loading...</span>');
          var url = $(this).attr('action');
   
         // Get form
        var form = $('.add-ajax-redirect-image-form')[0];

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
            	  }).then(() => {
            	      window.location.href = res.url;
            	      
            	  });
                }
                else {    
                   Swal.fire({
            			title: "Error!",
            			html: res.message ,
            			icon: "error",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})
                    $('.btn_verify').html(buttonText);
                    $('.btn_verify').attr("disabled", false);
                    $(".loader").fadeOut("slow"); 
                }
            }
        });
        return false;
    }); 
	
	    
    $('.add-ajax-form-new-call').submit(function(e) {
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
            data: $(".add-ajax-form-new-call").serialize(),
            success: function(res) {
                if (res.status == '200') { 
                    $(".loader").fadeOut("slow"); 
                   location.reload();
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
	
	
	$('.add-ajax-form-moc').submit(function(e) {
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
            data: $(".add-ajax-form-moc").serialize(),
            success: function(res) {
                if (res.status == '200') { 
                    $(".loader").fadeOut("slow"); 
                    location.reload();
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
	
	$('.add-ajax-form-mnc').submit(function(e) {
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
            data: $(".add-ajax-form-mnc").serialize(),
            success: function(res) {
                if (res.status == '200') { 
                    $(".loader").fadeOut("slow"); 
                    location.reload();
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
	
     $('.add-ajax-confirm-model').submit(function(e) {
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
            data: $(".add-ajax-confirm-model").serialize(),
            success: function(res) {
                if (res.status == '200') { 
                    $(".loader").fadeOut("slow"); 
                    location.reload();
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
    
    
     $('.add-ajax-halt-model').submit(function(e) {
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
            data: $(".add-ajax-halt-model").serialize(),
            success: function(res) {
                if (res.status == '200') {
                  $(".loader").fadeOut("slow"); 
                     Swal.fire({
            			title: "Success!",
            			text: res.message ,
            			icon: "success",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})
                  $('.btn_verify').html('Submit');
                  $('.btn_verify').attr("disabled", false);
                  $('.modal').modal('hide');
                  $('#camp_'+res.id).html(res.action);
                  $('#timeline_'+res.id).html(res.timeline);
                  
                  
                }
                else {	
                  $(".loader").fadeOut("slow");
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
                }
            }
        });
        return false;
    });
    
    $('.add-ajax-followup-model').submit(function(e) {
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
            data: $(".add-ajax-followup-model").serialize(),
            success: function(res) {
                if (res.status == '200') {
                  $(".loader").fadeOut("slow"); 
                     Swal.fire({
            			title: "Success!",
            			text: res.message ,
            			icon: "success",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})
                  $('.btn_verify').html('Submit');
                  $('.btn_verify').attr("disabled", false);   
                  $('.modal').modal('hide');
                  $('#camp_'+res.id).html(res.action);
                  $('#timeline_'+res.id).html(res.timeline);
                  $('#lstatus_'+res.id).html(res.lstatus);
                }
                else {	
                  $(".loader").fadeOut("slow");
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
                }
            }
        });
        return false;
    });
    
    
    $('.add-ajax-alert-model').submit(function(e) {
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
            data: $(".add-ajax-alert-model").serialize(),
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
    
    
    $('.flash-ajax-redirect-form').submit(function(e) {
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
            data: $(".flash-ajax-redirect-form").serialize(),
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
						html: res.message,
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

<script>
 $(window).on('load',  function(){
    $(".loader").fadeOut("slow");    
   if (feather) {
     feather.replace({ width: 14, height: 14 });
   }
 })
</script>
<script>
    var base_url = "<?php echo base_url();?>";
</script>


		  
<link rel="stylesheet" href="<?= base_url();?>assets/reminder/reminder.css">
<script  src="<?= base_url();?>assets/reminder/reminder.js"></script>
<div id="reminder_noti"></div>
<!-- partial:index.partial.html -->
<svg display="none">

	<symbol id="warning" viewBox="0 0 32 32" >
		<polygon points="16,1 31,31 1,31" fill="none" stroke="hsl(33,90%,55%)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
		<line x1="16" y1="12" x2="16" y2="20" stroke="hsl(33,90%,55%)" stroke-width="2" stroke-linecap="round" />
		<line x1="16" y1="25" x2="16" y2="25" stroke="hsl(33,90%,55%)" stroke-width="3" stroke-linecap="round" />
	</symbol>
</svg>
	    