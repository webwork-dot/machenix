<style>.error{font-size: 12px; color: red;}</style>


<div class="row">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-body">
            
            <form class="required-form my-1 add-ajax-redirect-image-form" action="<?php echo site_url('inventory/manage_staff/add_post'); ?>" onsubmit="return checkForm(this);"  enctype="multipart/form-data" method="post">      
                
                <div class="row">
                  <div class="col-md-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('name'); ?><span class="required">*</span></label>
                         <input type="text" class="form-control" placeholder="Name" name="first_name" onkeyup="this.value = this.value.replace(/[^A-Za-z ]/g, '');" required>
                      </div>
                  </div>

                  <div class="col-md-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('mobile_no.'); ?><span class="required">*</span></label>
                          <input type="text" class="form-control" placeholder="Mobile" name="phone" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" minlength="10" maxlength="10" required>
                      </div>
                  </div>

                  <div class="col-md-4">
                     <div class="form-group">
                        <label><?php echo get_phrase('staff_type'); ?> <span class="required">*</span></label>
                        <select class="form-control" name="staff_access" required>
                           <option value=""><?php echo get_phrase('select_staff_type'); ?></option>
                           <?php foreach($staff_access as $item){?>
                           <option value="<?php echo $item['id']?>"><?php echo $item['name']?></option>
                           <?php } ?>
                        </select>
                     </div>
                  </div>
                  
                   <div class="col-md-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('email'); ?><span class="required">*</span></label>
                          <input type="text" class="form-control" placeholder="Email" name="email" required>
                      </div>
                   </div> 
                   
                  <div class="col-md-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('password'); ?><span class="required">*</span></label>
                          <input type="password" class="form-control" placeholder="Password" name="password" required>
                      </div>
                  </div>
                   
                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('company'); ?> <span class="required">*</span></label>
                     <select class="form-control select2" name="company_id[]" multiple required>
                        <?php foreach($company_list as $item){?>
                        <option value="<?php echo $item->id?>"><?php echo $item->name?></option>
                        <?php } ?>
                     </select>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('profile_img'); ?></label>
                     <input type="file" class="form-control" name="profile_img" accept="image/*">
                    </div>
                  </div>
                  <div class="col-md-8">
                    
                  </div>

                   <div class="col-md-6">
                    <div class="form-group">
                     <label><?php echo get_phrase('address'); ?></label>
                     <textarea name="address" class="form-control" id="address" rows="3"></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                     <label><?php echo get_phrase('remark'); ?></label>
                     <textarea name="remark" class="form-control" id="remark" rows="3"></textarea>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('aadhar_no.'); ?></label>
                     <input type="text" class="form-control" placeholder="Aadhar No." name="aadhar_no">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('aadhar_photo'); ?></label>
                     <input type="file" class="form-control" name="aadhar_photo" accept="image/*">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('pan_no.'); ?></label>
                     <input type="text" class="form-control" placeholder="PAN No." name="pan_no">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('pan_photo'); ?></label>
                     <input type="file" class="form-control" name="pan_photo" accept="image/*">
                    </div>
                  </div>
               </div>
                
               <div class="row">
                  <div class="col-12">
                     <div class="text-left">                             
                        <div class="mb-2 mt-1">
                           <button type="submit" class="btn btn-primary btn_verify" name= "btn_verify">Submit</button>
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