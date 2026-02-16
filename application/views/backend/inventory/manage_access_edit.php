<style>.error{font-size: 12px; color: red;}
.access-div{
    display: flex;
    flex-wrap: nowrap;
    align-items: flex-start;
    float: left;
    margin-right: 15px;
    margin-bottom: 10px !important;
}

.p-l-6 {
    padding-left: 6px;
}
</style>
<div class="row">
   <div class="col-xl-12">
      <div class="card page-header">
         <div class="card-body">
            <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
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
            <h4 class="header-title mb-3"><?php echo get_phrase(''); ?></h4>
            <form class="required-form add-ajax-form" action="<?php echo site_url('inventory/manage_access/edit_post/'.$id); ?>" onsubmit="return checkForm(this);"  enctype="multipart/form-data" method="post">      
                
                <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                         <label><?php echo get_phrase('name'); ?><span class="required">*</span></label>
                         <input type="text" class="form-control" placeholder="" name="name"  value="<?php echo $data['name']?>" required>
                      </div>
                    </div>
                    
                    <div class="col-md-12 my-1">
                        <div class="form-group">
                            <?php $access_ = explode(',',$data['access_id']); foreach($access_type as $item):?>
                            <div class="form-check mb-1 access-div">
                                <input type="checkbox" class="form-check-input" name="access_id[]" value="<?php echo $item['id'];?>" id="<?php echo $item['id'];?>" <?php echo (in_array($item['id'], $access_)) ? 'checked':'';?>>
                                <label class="form-check-label p-l-6" for="<?php echo $item['id'];?>"><?php echo $item['name'];?></label>
                            </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                  
                  
               </div>
                
                <div class="row">
                   <div class="col-12">
                      <div class="text-left">                             
                         <div class="mb-3">
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

<script>
    function SubmitAlert() {
        if (confirm("Do you want to submit this data?") == true) {
            $("#form_validate").submit();
        }
    }
</script>