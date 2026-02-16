<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/file-uploader/css/jquery.dm-uploader.min.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/file-uploader/css/styles.css" />
<script src="<?php echo base_url(); ?>assets/file-uploader/js/jquery.dm-uploader.min.js"></script>
<script src="<?php echo base_url(); ?>assets/file-uploader/js/demo-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js"></script>
<style>
.gu-mirror {
  position: fixed !important;
  margin: 0 !important;
  z-index: 9999 !important;
  opacity: 0.8;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
  filter: alpha(opacity=80);
}

.gu-hide {
  display: none !important;
}

.gu-unselectable {
  -webkit-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
  user-select: none !important;
}

.gu-transit {
  opacity: 0.2;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=20)";
  filter: alpha(opacity=20);
}

.draggable-item {
  cursor: all-scroll;
}

.bg-dragula table {
  border-collapse: collapse;
  width: 100%;
}

.gu-mirror {
  width: 150px;
  height: 150px;
}

.gu-mirror {
  transform: scale(0.3);
}
</style>

<div class="dm-uploader-container">
  <div id="drag-and-drop-zone" class="dm-uploader text-center">
    <p class="dm-upload-icon">
      <i class="feather icon-upload"></i>
    </p>
    <p class="dm-upload-text"><?php echo get_phrase("drag_drop_images_here"); ?>&nbsp;<span
        style="text-decoration: underline"><?php echo get_phrase('browse_files'); ?></span></p>

    <a class='btn btn-md dm-btn-select-files'>
      <input type="file" name="file" size="40" multiple="multiple" accept="image/*">
    </a>

    <div data-plugin="dragula" data-containers='["files-image"]'>
      <ul class="dm-uploaded-files dm-change" id="files-image">
        <?php if (!empty($modesy_images)):
			foreach ($modesy_images as $modesy_image):?>
        <li class="media draggable-item on-hover-action" id="uploaderFile<?php echo $modesy_image->file_id; ?>">
          <img src="<?php echo base_url(); ?>uploads/temp/<?php echo $modesy_image->img_default; ?>" alt="">
          <a href="javascript:void(0)" class="btn-img-delete btn-delete-product-img-session"
            data-file-id="<?php echo $modesy_image->file_id; ?>">
            <i class="fa fa-times-circle"></i>
          </a>
          <?php if ($modesy_image->is_main == 1): ?>
          <!-- <a href="javascript:void(0)"
            class="badge badge-success badge-is-image-main btn-set-image-main-session">main</a> -->
          <?php else: ?>
          <!-- <a href="javascript:void(0)" class="badge badge-secondary badge-is-image-main btn-set-image-main-session"
            data-file-id="<?php echo $modesy_image->file_id; ?>">main</a> -->
          <?php endif; ?>
        </li>
        <?php endforeach;
				endif; ?>
      </ul>
    </div>

    <div class="error-message error-message-img-upload">
      <p class="m-b-5 text-center">
      </p>
    </div>

  </div>

</div>

<!-- <p class="images-exp"><i class="icon-exclamation-circle"></i><?php echo get_phrase("product_image_exp"); ?></p> -->
<div class="drag-target"></div>

<script type="text/html" id="files-template-image">
<li class="media">
  <img class="preview-img" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="bg">
  <div class="media-body">
    <div class="progress">
      <div class="dm-progress-waiting"><?php echo get_phrase("waiting"); ?></div>
      <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
        aria-valuemax="100"></div>
    </div>
  </div>
</li>
</script>

<script>
$(document).on("click", ".btn-set-image-main-session", function() {
  var b = $(this).attr("data-file-id");
  var a = {
    file_id: b
  };
  $(".badge-is-image-main").removeClass("badge-success");
  $(".badge-is-image-main").addClass("badge-secondary");
  $(this).removeClass("badge-secondary");
  $(this).addClass("badge-success");
  $.ajax({
    type: "POST",
    url: base_url + "file_controller/set_image_main_session",
    data: a,
    success: function(c) {}
  })
});
$(document).on("click", ".btn-set-image-main", function() {
  var b = $(this).attr("data-image-id");
  var c = $(this).attr("data-product-id");
  var a = {
    image_id: b,
    product_id: c
  };
  $(".badge-is-image-main").removeClass("badge-success");
  $(".badge-is-image-main").addClass("badge-secondary");
  $(this).removeClass("badge-secondary");
  $(this).addClass("badge-success");
  $.ajax({
    type: "POST",
    url: base_url + "file_controller/set_image_main",
    data: a,
    success: function(d) {}
  })
});

$(function() {

  dragula([document.getElementById('files-image')])
    .on('drag', function(el) {
      //console.log('start1');
    }).on('drop', function(el) {
      //console.log('start2');
    }).on('over', function(el, container) {
      //console.log('start3');
    }).on('out', function(el, container) {

    });


  $('#drag-and-drop-zone').dmUploader({
    url: '<?php echo base_url(); ?>file_controller/upload_image_session',
    maxFileSize: 10485760,
    queue: true,
    allowedTypes: 'image/*',
    extFilter: ["jpg", "jpeg", "png", "gif", "avif", "webp"],
    extraData: function(id) {
      return {
        "file_id": id
      };
    },
    onDragEnter: function() {
      this.addClass('active');
    },
    onDragLeave: function() {
      this.removeClass('active');
    },
    onInit: function() {},
    onComplete: function(id) {},
    onNewFile: function(id, file) {
      ui_multi_add_file(id, file, "image");
      if (typeof FileReader !== "undefined") {
        var reader = new FileReader();
        var img = $('#uploaderFile' + id).find('img');

        reader.onload = function(e) {
          img.attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
      }
    },
    onBeforeUpload: function(id) {
      $('#uploaderFile' + id + ' .dm-progress-waiting').hide();
      ui_multi_update_file_progress(id, 0, '', true);
      ui_multi_update_file_status(id, 'uploading', 'Uploading...');
    },
    onUploadProgress: function(id, percent) {
      ui_multi_update_file_progress(id, percent);
    },
    onUploadSuccess: function(id, data) {
      console.log('data ', data);
      var data = {
        "file_id": id,
      };
      $.ajax({
        type: "POST",
        url: base_url + "file_controller/get_sess_uploaded_image",
        data: data,
        success: function(response) {
          document.getElementById("uploaderFile" + id).innerHTML = response;
        }
      });
      ui_multi_update_file_status(id, 'success', 'Upload Complete');
      ui_multi_update_file_progress(id, 100, 'success', false);
    },
    onUploadError: function(id, xhr, status, message) {
      if (message == "Not Acceptable") {
        $("#uploaderFile" + id).remove();
        $(".error-message-img-upload").show();
        $(".error-message-img-upload p").html("You can upload 5 files.");
        setTimeout(function() {
          $(".error-message-img-upload").fadeOut("slow");
        }, 4000)
      }
    },
    onFallbackMode: function() {},
    onFileSizeError: function(file) {
      $(".error-message-img-upload").show();
      $(".error-message-img-upload p").html("File Too Large 10.00 MB");
      setTimeout(function() {
        $(".error-message-img-upload").fadeOut("slow");
      }, 4000)
    },
    onFileTypeError: function(file) {},
    onFileExtError: function(file) {},
  });
});
</script>