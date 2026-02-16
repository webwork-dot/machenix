
<form action="<?php echo site_url('hr/interview_schedule/re_schedule_interview/'.$param2); ?>"  onsubmit="return checkForm(this);" method="post" enctype="multipart/form-data">

    <div class="row">
        <div class="col-12 col-sm-6 mb-1">
          <div class="form-group">
             <label class="form-label">Interview Date<i class="required">*</i></label>
             <input type="date" class="form-control" name="interview_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
        </div>
            
        <div class="col-12 col-sm-6 mb-1">
          <div class="form-group">
             <label class="form-label">Interview Time<i class="required">*</i></label>
             <input type="time" class="form-control" name="interview_time" required>
          </div>
        </div>
        <div class="col-12 col-sm-12 mb-1 new">
          <div class="form-group">
            <label class="form-label"><?php echo get_phrase('remark'); ?></label>
            <textarea name="remark" class="form-control"></textarea>
             </div>
        </div>
    </div>
	
    

  <div class="row text-center mt-2"> 
    <div class="col-12">
      <button type="submit" class="btn btn-primary mt-1 me-1 btnf btn_verify" name= "btn_verify"><?php echo get_phrase('submit'); ?></button>
    </div>
  </div> 


</form>