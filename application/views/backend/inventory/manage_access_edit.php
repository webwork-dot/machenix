<style>
    .error{font-size: 12px; color: red;}
    .am-form-wrap {
        max-width: 100%;
    }
    .am-form-wrap .form-group {
        margin-bottom: 0.75rem;
    }
    .am-form-wrap .header-title {
        font-size: 1.1rem;
        margin-bottom: 0.85rem !important;
    }
    .am-form-wrap .card-body {
        padding: 1rem 1.15rem;
    }
</style>

<div class="row">
   <div class="col-xl-12">
      <div class="card am-form-wrap">
         <div class="card-body">
            <h4 class="header-title mb-3"><?php echo !empty($page_title) ? $page_title : 'Edit Access from staff'; ?></h4>
            <form class="required-form add-ajax-form" action="<?php echo site_url('inventory/manage_access/edit_post/'.$id); ?>" onsubmit="return checkForm(this);" enctype="multipart/form-data" method="post">

                <div class="row">
                    <div class="col-md-5 col-lg-4">
                      <div class="form-group">
                         <label><?php echo get_phrase('name'); ?><span class="required">*</span></label>
                         <input type="text" class="form-control form-control-sm" placeholder="" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" required>
                      </div>
                    </div>
                </div>

                <div class="row mt-1">
                    <div class="col-md-12">
                        <label class="mb-1"><b>Assign Access</b></label>
                        <?php
                            $this->load->view('backend/inventory/partials/access_tree', array(
                                'access_tree' => isset($access_tree) ? $access_tree : array(),
                                'selected_access' => isset($selected_access) ? $selected_access : array(),
                            ));
                        ?>
                    </div>
                </div>

                <div class="row mt-2">
                   <div class="col-12">
                      <div class="mb-1">
                         <button type="submit" class="btn btn-primary btn-sm btn_verify" name="btn_verify">Submit</button>
                      </div>
                   </div>
                </div>

			</form>
         </div>
      </div>
   </div>
</div>
