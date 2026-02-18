<?php
$company_ids = explode(',', $data['company_id']);
?>

<div class="row">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-body">
            <form class="required-form my-1 add-ajax-redirect-image-form" action="<?php echo site_url('inventory/manage_staff/edit_post/'.$id); ?>" onsubmit="return checkForm(this);" enctype="multipart/form-data" method="post">
                <div class="row">
                  <div class="col-md-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('name'); ?><span class="required">*</span></label>
                         <input type="text" class="form-control" placeholder="Name" name="first_name" value="<?php echo $data['first_name']; ?>" onkeyup="this.value = this.value.replace(/[^A-Za-z ]/g, '');" required>
                      </div>
                  </div>

                  <div class="col-md-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('mobile_no.'); ?><span class="required">*</span></label>
                          <input type="text" class="form-control" placeholder="Mobile" name="phone" value="<?php echo $data['phone']; ?>" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" minlength="10" maxlength="10" required>
                      </div>
                  </div>

                  <div class="col-md-4">
                     <div class="form-group">
                        <label><?php echo get_phrase('staff_type'); ?> <span class="required">*</span></label>
                        <select class="form-control" name="staff_access" required>
                           <option value=""><?php echo get_phrase('select_staff_type'); ?></option>
                           <?php foreach($staff_access as $item){?>
                           <option value="<?php echo $item['id']?>" <?php echo ($data['staff_access'] == $item['id']) ? 'selected':'';?>><?php echo $item['name']?></option>
                           <?php } ?>
                        </select>
                     </div>
                  </div>
                  
                   <div class="col-md-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('email'); ?><span class="required">*</span></label>
                          <input type="text" class="form-control" placeholder="Email" name="email" value="<?php echo $data['email']; ?>" required>
                      </div>
                   </div> 
                   
                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('company'); ?> <span class="required">*</span></label>
                     <select class="form-control select2" name="company_id[]" multiple required>
                        <?php foreach($company_list as $item){?>
                        <option value="<?php echo $item->id?>" <?php echo in_array($item->id, $company_ids) ? 'selected' : '';?>><?php echo $item->name?></option>
                        <?php } ?>
                     </select>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('profile_img'); ?></label>
                     <?php if (!empty($data['profile_img'])) { ?>
                        <a href="<?php echo base_url().$data['profile_img']; ?>" target="_blank"> <b>(View Old File)</b> </a>
                     <?php } ?>
                     <input type="file" class="form-control" name="profile_img" accept="image/*">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                     <label><?php echo get_phrase('address'); ?></label>
                     <textarea name="address" class="form-control" id="address" rows="3"><?php echo $data['address']; ?></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                     <label><?php echo get_phrase('remark'); ?></label>
                     <textarea name="remark" class="form-control" id="remark" rows="3"><?php echo $data['remark']; ?></textarea>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('aadhar_no.'); ?></label>
                     <input type="text" class="form-control" placeholder="Aadhar No." name="aadhar_no" value="<?php echo $data['aadhar_no']; ?>" >
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('aadhar_photo'); ?></label>
                     <?php if (!empty($data['aadhar_photo'])) { ?>
                        <a href="<?php echo base_url().$data['aadhar_photo']; ?>" target="_blank"> <b>(View Old File)</b> </a>
                     <?php } ?>
                     <input type="file" class="form-control" name="aadhar_photo" accept="image/*">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('pan_no.'); ?></label>
                     <input type="text" class="form-control" placeholder="PAN No." name="pan_no" value="<?php echo $data['pan_no']; ?>">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                     <label><?php echo get_phrase('pan_photo'); ?></label>
                     <?php if (!empty($data['pan_photo'])) { ?>
                        <a href="<?php echo base_url().$data['pan_photo']; ?>" target="_blank"> <b>(View Old File)</b> </a>
                     <?php } ?>
                     <input type="file" class="form-control" name="pan_photo" accept="image/*">
                    </div>
                  </div>
               </div>
                
                <div class="row">
                   <div class="col-12">
                      <div class="text-left">                             
                         <div class="mb-2 mt-1">
                            <button type="submit" class="btn btn-primary btn_verify" name="btn_verify"><?php echo get_phrase('update'); ?></button>
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

