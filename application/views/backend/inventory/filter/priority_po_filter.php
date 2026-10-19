<div class="filter-accordion accordion mx-filter" id="accordionFilter">
   <div class="collapse-margin card ">
	  <div class="card-header" id="headingOne" data-toggle="collapse" role="button" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
		 <span class="lead collapse-title">
			<h4 class="mb-0"><i class="feather icon-filter"></i> Filter</h4>
		 </span>
	  </div>
	  <div id="collapseOne" class="pb-1 collapse show" aria-labelledby="headingOne" data-parent="#accordionFilter">
		 <section class="filter-section">
			<div class="row match-height">
			   <div class="col-12">
				  <div class="card mb-0">
					 <div class="card-content">
						<div class="card-body">
						   <form class="form form-vertical" id="form_filter" method="GET" onsubmit="return false;">
							<div class="form-body">
							<div class="row">
								<input type="hidden" name="search" value="true">

								<?php if (is_access('106')) { ?>
								<div class="col-lg-2 col-md-3 col-sm-6 col-12">
									<div class="form-group mb-0">
										<label>Date</label>
										<input type="text" autocomplete="off" class="form-control bg-white datepicker_report" name="date_range" id="filter_date_range" placeholder="Search Order Date">
									</div>
								</div>
								<?php } ?>

								<?php if (is_access('110')) { ?>
								<div class="col-lg-2 col-md-3 col-sm-6 col-12">
									<div class="form-group mb-0">
										<label>Loading Date</label>
										<input type="text" autocomplete="off" class="form-control bg-white datepicker_loading_report" name="loading_date_range" id="filter_loading_date_range" placeholder="Search Loading Date">
									</div>
								</div>
								<?php } ?>

								<?php if (is_access('111')) { ?>
								<div class="col-lg-2 col-md-3 col-sm-6 col-12">
									<div class="form-group mb-0">
										<label>Status</label>
										<select name="status" id="filter_status" class="form-control" data-default="priority">
											<option value="">All</option>
											<option value="priority" selected>Priority</option>
											<option value="loading">Loading</option>
											<option value="purchase_in">Purchase In</option>
										</select>
									</div>
								</div>
								<?php } ?>

								<div class="col-lg-3 col-md-3 col-sm-6 col-12">
									<div class="form-group mb-0">
										<label>Keywords</label>
										<input name="keywords" id="filter_keywords" class="form-control" placeholder="Keywords" type="text">
									</div>
								</div>

								<div class="col-lg-2 col-md-3 col-sm-6 col-12">
									<label style="display: block;">&nbsp;</label>
									<div class="form-group mb-0">
										<button type="button" id="btn_reset_filter" class="btn btn-outline-danger mr-1">Reset</button>
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
<?php include('po_filter_scripts.php'); ?>
