<script src="<?php echo base_url('app-assets/js/core/app-menu.min.js'); ?>"></script>
<script src="<?php echo base_url('app-assets/js/core/app.min.js'); ?>"></script>
<script src="<?php echo base_url('app-assets/vendors/js/forms/validation/jquery.validate.min.js'); ?>"></script>
<script src="<?php echo base_url('app-assets/js/scripts/pages/auth-login.js'); ?>"></script>
<script>
  var base_url = "<?php echo base_url(); ?>";
  $(window).on('load', function() {
    if (feather) {
      feather.replace({ width: 14, height: 14 });
    }
  });
</script>
