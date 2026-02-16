<?php
// $param2 = lesson id and $param3 = course id
$follow_details = $this->crud_model->get_candidate_followup_by_id($param2)->row_array();
?>


<form action="<?php echo site_url('hr/calls/edit_post/'.$param2); ?>"  onsubmit="return checkForm(this);" method="post" enctype="multipart/form-data">

	<div class="col-12 col-sm-12 mb-1 new">
	  <div class="form-group">
        <label class="form-label"><?php echo get_phrase('remark'); ?></label>
        <textarea name="remark" class="form-control" required><?php echo $follow_details['remark']; ?></textarea>
         </div>
	</div>
    

  <div class="row text-center mt-2"> 
    <div class="col-12">
      <button type="submit" class="btn btn-primary mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
    </div>
  </div> 


</form>
