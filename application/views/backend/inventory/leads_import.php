<div class="row">
  <div class="col-12">
    <div class="card" style="border-radius: 14px; border: 1px solid #edf2f7; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);">
      <div class="card-body" style="padding: 22px 28px;">
        <?php echo form_open_multipart('phpspreadsheet/upload_leads', ['id' => 'import_leads_form']); ?>
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <h4 class="card-title mb-0" style="font-size: 18px; font-weight: 700; color: #1e293b;">Insert Leads</h4>
            <small class="text-muted" style="font-size: 13px; color: #64748b;">Upload Via Excel</small>
          </div>
          <div>
            <a href="<?php echo base_url('phpspreadsheet/sample_leads_excel'); ?>" target="_blank" class="btn btn-primary waves-effect waves-float waves-light" style="background-color: #649ed4; border-color: #649ed4; font-weight: 600; padding: 8px 18px; border-radius: 6px;">
              Download Format <i class="fa fa-download ms-1"></i>
            </a>
          </div>
        </div>

        <div class="d-flex align-items-center" style="gap: 16px;">
          <div style="max-width: 340px; flex-grow: 1;">
            <input type="file" class="form-control" name="fileURL" id="file_text" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, .csv" required style="border-radius: 6px; border: 1px solid #d8d6de; padding: 6px 12px;">
          </div>
          <div>
            <button type="submit" class="btn btn-primary btn-verify waves-effect waves-float waves-light" style="background-color: #649ed4; border-color: #649ed4; font-weight: 600; padding: 8px 20px; border-radius: 6px;">Upload File</button>
          </div>
        </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>

<div class="returnData" style="display:none; margin-top: 20px;">
  <div id="returnData"></div>
</div>

<script>
$(document).ready(function() {
    $('#import_leads_form').on('submit', function(event) {
        $(".loader").show(); 
        $('.btn-verify').html('Processing ...<i class="fa fa-spinner fa-spin" style="font-size: 14px;color: #fff;"></i>');
        event.preventDefault();
        $.ajax({
            url: "<?php echo base_url(); ?>phpspreadsheet/upload_leads",
            method: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {	
                $(".returnData").css("display", "block");
                $('.btn-verify').html('Upload File');
                $(".loader").fadeOut("slow"); 
                if (data != '') {
                    $('#returnData').html(data);
                } else if (data == 'false') {
                    toastr.error('Please import correct file, did not match excel sheet column!'); 
                }
            },
            error: function() {
                $(".loader").fadeOut("slow");
                $('.btn-verify').html('Upload File');
                toastr.error('An error occurred during file upload.');
            }
        });
    });
});
</script>
