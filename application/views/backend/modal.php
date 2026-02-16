<script type="text/javascript">
function smallAjaxModal(url, header)
{
    // SHOWING AJAX PRELOADER IMAGE
    jQuery('#small-modal .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url().'assets/global/bg-pattern-light.svg'; ?>" /></div>');
    jQuery('#small-modal .modal-title').html('...');
    // LOADING THE AJAX MODAL
    jQuery('#small-modal').modal('show', {backdrop: 'true'});

    // SHOW AJAX RESPONSE ON REQUEST SUCCESS
    $.ajax({
        url: url,
        success: function(response)
        {
            jQuery('#small-modal .modal-body').html(response);
            jQuery('#small-modal .modal-title').html(header);
        }
    });
}


function showAjaxModal(url, header)
{
    // SHOWING AJAX PRELOADER IMAGE
    jQuery('#scrollable-modal .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url().'assets/global/bg-pattern-light.svg'; ?>" /></div>');
    jQuery('#scrollable-modal .modal-title').html('...');
    // LOADING THE AJAX MODAL
    jQuery('#scrollable-modal').modal('show', {backdrop: 'true'});

    // SHOW AJAX RESPONSE ON REQUEST SUCCESS
    $.ajax({
        url: url,
        success: function(response)
        {
            jQuery('#scrollable-modal .modal-body').html(response);
            jQuery('#scrollable-modal .modal-title').html(header);
        }
    });
}

function showLargeModal(url, header)
{
    // SHOWING AJAX PRELOADER IMAGE
    jQuery('#large-modal .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url().'assets/global/bg-pattern-light.svg'; ?>" height = "50px" /></div>');
    jQuery('#large-modal .modal-title').html('...');
    // LOADING THE AJAX MODAL
    jQuery('#large-modal').modal('show', {backdrop: 'true'});

    // SHOW AJAX RESPONSE ON REQUEST SUCCESS
    $.ajax({
        url: url,
        success: function(response)
        {
            jQuery('#large-modal .modal-body').html(response);
            jQuery('#large-modal .modal-title').html(header);
        }
    });
}

function showRightCanvas(url, header)
{
  // 1) Loader + header
  $('#offcanvasRightLabel').text('...');
  $('#offcanvasRight .offcanvas-body').html(
    '<div class="text-center py-5">' +
      '<div class="spinner-border" role="status"></div>' +
    '</div>'
  );

  // 2) Open offcanvas using the "working" data-attribute way (fake button)
  (function openOffcanvasByTrigger() {
    const offcanvasId = 'offcanvasRight';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.style.display = 'none';

    // BS5 attributes (same as your working button)
    btn.setAttribute('data-bs-toggle', 'offcanvas');
    btn.setAttribute('data-bs-target', `#${offcanvasId}`);
    btn.setAttribute('aria-controls', offcanvasId);

    document.body.appendChild(btn);
    btn.click();
    btn.remove();
  })();

  // 3) Load dynamic body
  $.ajax({
    url: url,
    success: function (response) {
      $('#offcanvasRight .offcanvas-body').html(response);
      $('#offcanvasRightLabel').text(header || 'History');
    },
    error: function () {
      $('#offcanvasRightLabel').text(header || 'History');
      $('#offcanvasRight .offcanvas-body').html(
        '<div class="alert alert-danger mb-0">Failed to load data.</div>'
      );
    }
  });
}



function showCallsModal(url, header)
{
    // SHOWING AJAX PRELOADER IMAGE
    $(".loader").show();
    jQuery('#calls-popup-modal .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url().'assets/global/bg-pattern-light.svg'; ?>" height = "50px" /></div>');
    jQuery('#calls-popup-modal .modal-title').html('...');
    // LOADING THE AJAX MODAL
    jQuery('#calls-popup-modal').modal('show', {backdrop: 'true'});

    // SHOW AJAX RESPONSE ON REQUEST SUCCESS
    $.ajax({
        url: url,
        success: function(response)
        {
             $(".loader").fadeOut("slow");
            jQuery('#calls-popup-modal .modal-body').html(response);
            jQuery('#calls-popup-modal .modal-title').html(header);
        }
    });
}

function confirm_remark_popup(url,header)
{   
  $('#confirm_remark_popup').attr('action', url)
  $('#modal_confirm_remark_Form4').modal('show', {backdrop: 'static'});
  $('#modal_confirm_remark_Form4 .modal-title').html(header);
} 
</script>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header py-2">
    <h5 id="offcanvasRightLabel" class="text-white mb-0"></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">

  </div>
</div>


 <div class="modal fade text-start"  id="calls-popup-modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered full-width-modal">
	  <div class="modal-content">
		<div class="modal-header">
		  <h4 class="modal-title" id="scrollableModalTitle">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body pt-0">
   
		  </div>                
	  
	  </div>
	</div>
  </div>


 <div class="modal fade text-start"  id="large-modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-xl">
	  <div class="modal-content">
		<div class="modal-header">
		  <h4 class="modal-title" id="scrollableModalTitle">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body ml-2 mr-2">
   
		  </div>                
	  
	  </div>
	</div>
  </div>

<!-- Scrollable modal -->
 <div class="modal fade text-start"  id="scrollable-modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
	  <div class="modal-content">
		<div class="modal-header">
		  <h4 class="modal-title" id="scrollableModalTitle">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body ml-2 mr-2">
   
		  </div>                
	  
	  </div>
	</div>
  </div>



<!-- small modal -->
<div class="modal fade text-start" id="small-modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
	  <div class="modal-content">
		<div class="modal-header">
		  <h4 class="modal-title" id="smallModalTitle">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body ml-2 mr-2">
   
		  </div>                
	  
	  </div>
	</div>
</div>


<script type="text/javascript">
function confirm_modal(url,header='Are you sure!'){
      $('#alert_modal_Form').attr('action', url)
      $('#alert-modal').modal('show', {backdrop: 'static'});
      $('#alert-modal .modal-title').html(header);
}
</script>

<!-- Info Alert Modal -->
<div id="alert-modal" class="modal fade" data-bs-backdrop="static"  tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm"> 
        <div class="modal-content">
            <div class="modal-body p-2">
                <div class="text-center">
                    <i class="feather icon-alert-circle text-info h1"></i>
                    <h4 class="mt-1"><?php echo get_phrase("heads_up"); ?>!</h4>
                    <p class="mt-1 modal-title"><?php echo get_phrase("are_you_sure"); ?></p>
                    
                    <form method="post" id="alert_modal_Form" onsubmit="return checkFormLoader(this);" enctype="multipart/form-data" accept-charset="utf-8">
                      <div class="col-12 text-center">
                        <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Continue</button>
                        <button type="reset" class="btn btn-outline-danger mt-1" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                      </div>
                    </form>
                    
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" id="modal_confirm_remark_Form4" data-bs-backdrop="static" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
   
      <div class="modal-body px-sm-5 mx-50 pb-5">
          <h5 class="text-center mb-1 modal-title" id="addNewCardTitle" style="font-weight: 600;font-size: 16px;">Are You Sure You Want To Delete?</h5>       
        <!-- form -->
         <form action="" method="post" id="confirm_remark_popup" class="add-ajax-confirm-model"  onsubmit="return checkForm(this);" enctype="multipart/form-data" accept-charset="utf-8">
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark</label>
            <textarea
                  class="form-control"
                  id="exampleFormControlTextarea1"
                  rows="3" name="remark"
                  placeholder="Remark" required
                ></textarea>
          </div>
          <div class="col-12 text-center">
            
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div> 

<?php if($page_name=='today_followup'){?>
<script type="text/javascript">
   function confirm_remark_modal(url,id,header)
   {   
      $('#confirm_remark_Form').attr('action', url)
      $('#modal-confirm_remark_modal').modal('show', {backdrop: 'static'});
      $("#id_").val(id);
      $('#modal-confirm_remark_modal .modal-title').html(header);
   }  

</script> 

<script type="text/javascript">
   function confirm_remark_modal1(url,id,header)
   {   
      $('#confirm_remark_Form').attr('action', url)
      $('#modal-confirm_remark_modal1').modal('show', {backdrop: 'static'});
      $("#id_").val(id);
      $('#modal-confirm_remark_modal1 .modal-title').html(header);
   }  

</script> 

<script type="text/javascript">
   function confirm_remark_modal2(url,id,header)
   {   
      $('#confirm_remark_Form').attr('action', url)
      $('#modal-confirm_remark_modal2').modal('show', {backdrop: 'static'});
      $("#id_").val(id);
      $('#modal-confirm_remark_modal2 .modal-title').html(header);
   }  

</script> 

<script type="text/javascript">
   function confirm_remark_modal3(url,id,header)
   {   
      $('#confirm_remark_Form').attr('action', url)
      $('#modal-confirm_remark_modal3').modal('show', {backdrop: 'static'});
      $("#id_").val(id);
      $('#modal-confirm_remark_modal3 .modal-title').html(header);
   }  

</script>

<div class="modal fade" id="modal-confirm_remark_modal" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body px-sm-5 mx-50 pb-5">
          <h5 class="text-center mb-1" id="addNewCardTitle">Today's Followup</h5>       
        <!-- form -->
        
         <form action="<?php echo staff_url();?>calls/update_calls/<?php echo $item['id'];?>" method="post" id="form_validate" onsubmit="return checkForm(this);" enctype="multipart/form-data" accept-charset="utf-8">
          <div class="row">
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Date</label>
            <input type="date" class="form-control" name="followup_date">
            <input type="hidden" class="form-control" name="type" value="Outbound">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Time</label>
            <input type="time" class="form-control" name="followup_time">
          </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark</label>
            <textarea
                  class="form-control"
                  id="exampleFormControlTextarea1"
                  rows="3" name="remark"
                  placeholder="Remark"
                ></textarea>
          </div>
          <div class="col-12 text-center">
            
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal-confirm_remark_modal1" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body px-sm-5 mx-50 pb-5">
          <h5 class="text-center mb-1" id="addNewCardTitle">Today's Followup</h5>       
        <!-- form -->
        
         <form action="<?php echo manager_url();?>calls/update_calls/<?php echo $item['id'];?>" method="post" id="form_validate" onsubmit="return checkForm(this);" enctype="multipart/form-data" accept-charset="utf-8">
          <div class="row">
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Date</label>
            <input type="date" class="form-control" name="followup_date">
            <input type="hidden" class="form-control" name="type" value="Outbound">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Time</label>
            <input type="time" class="form-control" name="followup_time">
          </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark</label>
            <textarea
                  class="form-control"
                  id="exampleFormControlTextarea1"
                  rows="3" name="remark"
                  placeholder="Remark"
                ></textarea>
          </div>
          <div class="col-12 text-center">
            
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal-confirm_remark_modal2" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body px-sm-5 mx-50 pb-5">
          <h5 class="text-center mb-1" id="addNewCardTitle">Today's Followup</h5>       
        <!-- form -->
        
         <form action="<?php echo digital_coordinator_url();?>calls/update_calls/<?php echo $item['id'];?>" method="post" id="form_validate" onsubmit="return checkForm(this);" enctype="multipart/form-data" accept-charset="utf-8">
          <div class="row">
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Date</label>
            <input type="date" class="form-control" name="followup_date">
            <input type="hidden" class="form-control" name="type" value="Outbound">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Time</label>
            <input type="time" class="form-control" name="followup_time">
          </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark</label>
            <textarea
                  class="form-control"
                  id="exampleFormControlTextarea1"
                  rows="3" name="remark"
                  placeholder="Remark"
                ></textarea>
          </div>
          <div class="col-12 text-center">
            
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal-confirm_remark_modal3" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body px-sm-5 mx-50 pb-5">
          <h5 class="text-center mb-1" id="addNewCardTitle">Today's Followup</h5>       
        <!-- form -->
        
         <form action="<?php echo patient_coordinator_url();?>calls/update_calls/<?php echo $item['id'];?>" method="post" id="form_validate" onsubmit="return checkForm(this);" enctype="multipart/form-data" accept-charset="utf-8">
          <div class="row">
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Date</label>
            <input type="date" class="form-control" name="followup_date">
            <input type="hidden" class="form-control" name="type" value="Outbound">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Time</label>
            <input type="time" class="form-control" name="followup_time">
          </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark</label>
            <textarea
                  class="form-control"
                  id="exampleFormControlTextarea1"
                  rows="3" name="remark"
                  placeholder="Remark"
                ></textarea>
          </div>
          <div class="col-12 text-center">
            
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php }?>


<script type="text/javascript">
   function followup_modal(url,header){   
      $('#followup_modal_Form').attr('action', url)
      $('#modal-followup_modal').modal('show', {backdrop: 'static'});
      $('#modal-followup_modal .modal-title').html(header);
   }  

   function halt_modal(url,header){ 
 
      $('#halt_modal_Form').attr('action', url)
      $('#modal-halt_modal').modal('show', {backdrop: 'static'});
      $('#modal-halt_modal .modal-title').html(header);
    
   }  
</script> 


<div class="modal fade" id="modal-halt_modal" data-bs-backdrop="static" tabindex="-1" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">	
       <div class="modal-header">
		  <h4 class="modal-title" id="">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
      <div class="modal-body px-sm-5 mx-50 pb-5">   
         <form method="post" id="halt_modal_Form" onsubmit="return checkFormLoader(this);" enctype="multipart/form-data" accept-charset="utf-8">
 
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control"   rows="3" name="remark" placeholder="Remark" required></textarea>
          </div>
          <div class="col-12 text-center">
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modal-followup_modal" data-bs-backdrop="static" tabindex="-1" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content"> 
    <div class="modal-header">
		  <h4 class="modal-title" id="">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <!-- form -->
        
         <form method="post" id="followup_modal_Form" onsubmit="return checkForm(this);" enctype="multipart/form-data" accept-charset="utf-8">
          <div class="row mb-2">
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Date*</label>
            <input type="date" class="form-control"  min="<?php echo date('Y-m-d');?>" name="followup_date" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Time*</label>
            <input type="time" class="form-control" name="followup_time" required>
          </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" rows="3" name="remark" placeholder="Remark" required></textarea>
          </div>
          <div class="col-12 text-center">
            
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>






<script type="text/javascript">
   function ajax_followup_modal(url,header){   
      $('#ajax_followup_modal_Form').attr('action', url)
      $('#modal-ajax_followup_modal').modal('show', {backdrop: 'static'});
      $('#modal-ajax_followup_modal .modal-title').html(header);
   }  

   function ajax_halt_modal(url,header){ 
 
      $('#ajax_halt_modal_Form').attr('action', url)
      $('#modal-ajax_halt_modal').modal('show', {backdrop: 'static'});
      $('#modal-ajax_halt_modal .modal-title').html(header);
    
   }  
</script> 


<div class="modal fade" id="modal-ajax_halt_modal" data-bs-backdrop="static" tabindex="-1" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">	
       <div class="modal-header">
		  <h4 class="modal-title" id="">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
      <div class="modal-body px-sm-5 mx-50 pb-5">   
         <form method="post" id="ajax_halt_modal_Form"  class="add-ajax-halt-model"  onsubmit="return checkFormLoader(this);" enctype="multipart/form-data" accept-charset="utf-8">
 
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control"   rows="3" name="remark" placeholder="Remark" required></textarea>
          </div>
          <div class="col-12 text-center">
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modal-ajax_followup_modal" data-bs-backdrop="static" tabindex="-1" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content"> 
    <div class="modal-header">
		  <h4 class="modal-title" id="">Modal title</h4>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
      <div class="modal-body px-sm-5 mx-50 pb-5">
        <!-- form -->
        
         <form method="post" id="ajax_followup_modal_Form" class="add-ajax-followup-model"  onsubmit="return checkForm(this);" enctype="multipart/form-data" accept-charset="utf-8">
          <div class="row mb-2">
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Date*</label>
            <input type="date" class="form-control"  min="<?php echo date('Y-m-d');?>" name="followup_date" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modalAddCardName">Followup Time*</label>
            <input type="time" class="form-control" name="followup_time" required>
          </div>
          </div>
          <div class="col-md-12">
            <label class="form-label" for="modalAddCardName">Remark*</label>
            <textarea class="form-control" rows="3" name="remark" placeholder="Remark" required></textarea>
          </div>
          <div class="col-12 text-center">
            
            <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Submit</button>
            <button type="reset" class="btn btn-outline-secondary mt-1" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">
function confirm_modal(url,header='Are you sure!'){
      $('#ajax-alert_modal_Form').attr('action', url)
      $('#ajax-alert-modal').modal('show', {backdrop: 'static'});
      $('#ajax-alert-modal .modal-title').html(header);
}
</script>

<!-- Info ajax-alert Modal -->
<div id="ajax-alert-modal" class="modal fade" data-bs-backdrop="static"  tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm"> 
        <div class="modal-content">
            <div class="modal-body p-2">
                <div class="text-center">
                    <i class="feather icon-alert-circle text-info h1"></i>
                    <h4 class="mt-1"><?php echo get_phrase("heads_up"); ?>!</h4>
                    <p class="mt-1 modal-title"><?php echo get_phrase("are_you_sure"); ?></p>
                    
                    <form method="post" class="add-ajax-alert-model" id="ajax-alert_modal_Form" onsubmit="return checkFormLoader(this);" enctype="multipart/form-data" accept-charset="utf-8">
                      <div class="col-12 text-center">
                        <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Continue</button>
                        <button type="reset" class="btn btn-outline-danger mt-1" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                      </div>
                    </form>
                    
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<script type="text/javascript">
function accept_modal(url,header='Are you sure!'){
      $('#accept-alert_modal_Form').attr('action', url)
      $('#accept-alert-modal').modal('show', {backdrop: 'static'});
      $('#accept-alert-modal .modal-title').html(header);
}
</script>

<!-- Info accept-alert Modal -->
<div id="accept-alert-modal" class="modal fade" data-bs-backdrop="static"  tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm mx-collect"> 
        <div class="modal-content">
            <div class="modal-body p-2">
                <div class="text-center">
                    <i class="feather icon-alert-circle text-danger h1"></i>
                    <h1 class="mt-1"><?php echo get_phrase("Alert"); ?>!</h1>
                    <h4 class="mt-1 modal-title"><?php echo get_phrase("are_you_sure"); ?></h4>
                    
                    <form method="post" class="add-ajax-alert-model" id="accept-alert_modal_Form" onsubmit="return checkFormLoader(this);" enctype="multipart/form-data" accept-charset="utf-8">
                      <div class="col-12 text-center">
                        <button type="submit" name="btn_verify" id="submit_btn" class="btn btn-primary me-1 mt-1 btn_verify">Continue</button>
                        <button type="reset" class="btn btn-outline-danger mt-1" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                      </div>
                    </form>
                    
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
