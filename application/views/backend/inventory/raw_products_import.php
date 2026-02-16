<link href="https://cdn.jsdelivr.net/npm/remixicon@2.2.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" type="text/css"
  href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
  href="https://cdn.datatables.net/fixedcolumns/4.1.0/css/fixedColumns.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.1.0/js/dataTables.fixedColumns.min.js"></script>
<style>
    .returnData {
      display: none;
    }
    
    .returnData h6 {
      font-size: 16px;
      color: red;
      text-align: center;
      font-weight: 600;
    }
</style>
<div class="mobile_view home">

  <!-- Bordered table start -->
  <div class="row" id="table-bordered">
      
    <div class="col-12">
      <div class="card">
        <div class="card-body py-1 my-0">
          <div class="row d-block">
            <div id="csv-text">
              <?php echo form_open_multipart('phpspreadsheet/upload_products', [ 'id'=>"import_form_excel",'onkeypress' => "return event.keyCode != 13;"]); ?>
              <h3>Insert Products</h3>
              <div class="col-12">
                <div class="card mb-0">
                  <div class="row">
                    <div class="col-12 col-sm-4 mb-1">
                      Upload Via Excel<br />
                      <a style="padding:0px;" class='btn-primary1 btn-file-upload '>
                        <input type="file" class="form-control" name="fileURL" id="file_text"
                          accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                          onchange="$('#upload-file-info3').html($(this).val().replace(/.*[\/\\]/, ''));" required>
                      </a>
                    </div>
                    <div class="col-md-2 mt30">
                      <label for="bills_pending"></label><br>
                      <button type="submit" class='btn btn-md btn-primary blue btn-verify btn-file-upload float-left'>Upload File</button>
                    </div>
                    <div class="col-md-6 mt30" style="direction: rtl;">
                      <label for="bills_pending"></label><br>
                      <a class="btn btn-md btn-primary btn-file-upload downl-btn float-right" href="<?php echo base_url();?>phpspreadsheet/sample_product_excel" target="_blank">
                          <i class="fa fa-download" aria-hidden="true"></i> Download Format
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <?php echo form_close(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <div class="returnData">
    <div id="returnData"></div>
  </div>
</div>
<script>
function checkForm(form) // Submit button clicked
{
  form.btn_add.disabled = true;
  $('#btn_add').html(
    'Processing ...<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i>');
  return true;
}
</script>
<script>
$(document).ready(function() {
  $('#import_form_excel').on('submit', function(event) {
    $(".loader").show();
    $('.btn-verify').html(
      'Processing ...<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i>'
      );
    event.preventDefault();
    $.ajax({
      url: "<?php echo base_url(); ?>phpspreadsheet/upload_products",
      method: "POST",
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData: false,
      success: function(data) {
        $(".returnData").css("display", "block");
        $('.btn-verify').html('Upload File');
        $(".loader").fadeOut("slow");
        $('#file_sta').val('');
        if (data != '') {
          $('#returnData').html(data);
          //toastr.success('Addded Successfully!');
        } else if (data == 'false') {
          toastr.error('Please import correct file, did not match excel sheet column!');
        } else {
          //toastr.success('Addded Successfully!');
        }
      }
    })
  });
});
</script>