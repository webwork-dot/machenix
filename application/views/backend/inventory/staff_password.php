<div class="row ">
   <div class="col-xl-12">
      <div class="card page-header">
         <div class="card-body">
            <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> 
              <!--<a href = "<?php echo site_url('admin/manage-staff'); ?>" class="btn btn-outline-primary btn-sm btn-rounded alignToTitle"><i class="mdi mdi-chevron-left"></i><?php echo get_phrase('back'); ?></a>-->
            </h4>
         </div>
         <!-- end card body-->
      </div>
      <!-- end card -->
   </div>
   <!-- end col-->
</div>
<div class="row">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-body">
            <form class="required-form my-1 add-ajax-form" action="<?php echo site_url('inventory/manage_staff/change_password/'.$id); ?>" onsubmit="return checkForm(this);" enctype="multipart/form-data" method="post">
               
                <div class="row">
                  <div class="col-md-6">
                      <div class="form-group">
                         <label><?php echo get_phrase('new_password'); ?><span class="required">*</span></label>
                           <div class="input-group input-group-merge">
                                <input type="password" class="form-control" placeholder="<?php echo get_phrase('new_password'); ?>" name="new_password" id="new_password"  required>
                                <div class="input-group-text" data-password="false" onclick="password_view('new_password')">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                         
                      </div>
                  </div>
                  
                  <div class="col-md-6">
                      <div class="form-group">
                         <label><?php echo get_phrase('confirm_password'); ?><span class="required">*</span></label>
                         <div class="input-group input-group-merge">
                                <input type="password" class="form-control" placeholder="<?php echo get_phrase('confirm_password'); ?>" name="confirm_password" id="confirm_password"  required>
                                <div class="input-group-text" data-password="false" onclick="password_view('confirm_password')">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                      </div>
                  </div>
                </div>
                
               <div class="row">
                  <div class="col-12">
                     <div class="text-left">
                        <div class="mb-2 mt-1">
                           <button type="submit" class="btn btn-primary btn_verify" name= "btn_verify"><?php echo get_phrase('update'); ?></button>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
         <!-- end card-body -->
      </div>
      <!-- end card-->
   </div>
</div>
<script>
    function password_view(id) {
      var x = document.getElementById(id);
      if (x.type === "password") {
        x.type = "text";
      } else {
        x.type = "password";
      }
    }
</script>

