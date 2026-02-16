


<form action="<?php echo site_url('hr/interview_schedule/accept/'.$param2); ?>"  onsubmit="return checkForm(this);" method="post" enctype="multipart/form-data">

	<div class="col-12 col-sm-12 mb-1 new">
	  <div class="form-group">
        <label><?php echo get_phrase('remark'); ?></label>
        <textarea name="remark" class="form-control"></textarea>
         </div>
	</div>
    

  <div class="row text-center mt-2"> 
    <div class="col-12">
      <button type="submit" class="btn btn-primary mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
    </div>
  </div> 


</form>