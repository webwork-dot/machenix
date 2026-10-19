<?php if (is_access('255')) { ?>
<div class="filter-accordion accordion mx-filter" id="accordionFilter">
   <div class="collapse-margin card ">
	  <div class="card-header" id="headingOne" data-toggle="collapse" role="button" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
		 <span class="lead collapse-title">
			<h4 class="mb-0"><i class="feather icon-filter"></i> Filter</h4>
		 </span>
	  </div>
	  <div id="collapseOne" class="pb-1 collapse show" aria-labelledby="headingOne" data-parent="#accordionFilter" style="">
		 <section class="filter-section">
			<div class="row match-height">
			   <div class="col-12">
				  <div class="card mb-0">
					 <div class="card-content">
						<div class="card-body">
						   <form class="form form-vertical" id="form_filter" method="GET" onsubmit="return checkForm(this);">
							<div class="form-body">
							<div class="row">
								<input type="hidden" name="search" value="true">
								<?php
								// Preserve existing GET parameters like customer_id, etc.
								foreach ($_GET as $key => $val) {
									if (!in_array($key, ['date_range', 'search'])) {
										if (is_array($val)) {
											foreach ($val as $subVal) {
												echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($subVal) . '">';
											}
										} else {
											echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '">';
										}
									}
								}
								?>
								<?php if (is_access('255')) { ?>
								<div class="col-md-3 col-12">
									<div class="form-group mb-0">
										<label>Date</label>
										<input type="text" autocomplete="off" class="form-control bg-white datepicker_report" name="date_range" value="<?php if(isset($_GET['date_range'])) { echo htmlspecialchars($_GET['date_range']); }?>" placeholder="Search Order Date">
									</div>
								</div>
								<?php } ?>

								<div class="col-md-3">
									<label style="display: block;">&nbsp; </label>
									<div class="form-group mb-0">
										<button type="submit" name="search" value="true" id="btn_verify" class="btn btn-primary btn_verify mr-1 mb-0 waves-effect waves-float waves-light">Search</button>
										<?php if(isset($_GET['search'])):
											$reset_params = $_GET;
											unset($reset_params['search'], $reset_params['date_range']);
											$reset_query = http_build_query($reset_params);
											$reset_url = currentUrl(true) . ($reset_query ? '?' . $reset_query : '');
										?>
											<a href="<?php echo $reset_url; ?>"><button type="button" class="btn btn-outline-danger mr-1">Reset</button></a>
										<?php endif; ?>
									</div>
								</div>
							</div>
							</div>
						   </form>
						</div>
					 </div>
				  </div>
			   </div>
			</div>
		 </section>
	  </div>
   </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
    //Date range picker
    $('.datepicker_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: false,
        locale: {
            format: 'DD-MM-YYYY',
        },
        maxDate: moment().add(0, 'days'),
    });

    $('.datepicker_report').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    });
</script>
<?php } ?>
