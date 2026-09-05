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
						   <form class="form form-vertical" id="form_filter" method="GET" onsubmit="return false;">
							<div class="form-body">
							<div class="row">
										<input type="hidden" name="search" value="true">

										<div class="col-lg-2 col-md-3 col-sm-6 col-12">
												<div class="form-group mb-0">   
														<label>Date</label>
														<input type="text" autocomplete="off" class="form-control bg-white datepicker_report" name="date_range" id="filter_date_range" placeholder="Search Order Date">
												</div>
										</div>

                    <?php if($page_name == "purchase_order" || $page_name == "priority_po" || $page_name == "loading_list_po" || $page_name == "pending_po" || $page_name == "po_purchase_in") {?>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-12">
                        <div class="form-group mb-0">   
                            <label>Loading Date</label>
                            <input type="text" autocomplete="off" class="form-control bg-white datepicker_loading_report" name="loading_date_range" id="filter_loading_date_range" placeholder="Search Loading Date">
                        </div>
                    </div>
                    <?php } ?>

                    <?php if($page_name == "purchase_order" || $page_name == "priority_po" || $page_name == "loading_list_po") {?>
                      <div class="col-lg-2 col-md-3 col-sm-6 col-12">
                        <div class="form-group mb-0">   
                          <label>Status</label>
                          <div class="form-group">
                            <select name="status" id="filter_status" class="form-control" onchange="dataTable.draw()">
                              <option value="">All</option>
                              <?php if($page_name == "purchase_order") {?>
                                <option value="pending" selected>Pending</option>
                                <option value="priority">Priority</option>
                                <option value="loading">Loading</option>
                                <option value="purchase_in">Purchase In</option>
                              <?php } elseif($page_name == "priority_po") { ?>
                                <option value="priority" selected>Priority</option>
                                <option value="loading">Loading</option>
                                <option value="purchase_in">Purchase In</option>
                              <?php } else { ?>
                                <option value="loading" selected>Loading</option>
                                <option value="purchase_in">Purchase In</option>
                              <?php } ?>
                            </select>
                          </div>
                        </div>
                      </div>
                    <?php } ?>

                    <?php if($page_name == "po_expense") { ?>
                      <div class="col-lg-2 col-md-3 col-sm-6 col-12">
                        <div class="form-group mb-0">   
                          <label>Type</label>
                          <div class="form-group">
                            <select name="type" id="filter_type" class="form-control" onchange="dataTable.draw()">
                              <option value="">All</option>
                              <option value="official">Official</option>
                              <option value="unofficial">Unofficial</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    <?php } ?>

										<div class="col-lg-3 col-md-3 col-sm-6 col-12">
											<div class="form-group mb-0">   
												<label>Keywords</label>
												<div class="form-group">
													<input name="keywords" id="filter_keywords" class="form-control" placeholder="Keywords" type="text">
												</div>
											</div>
										</div>


										<div class="col-lg-2 col-md-3 col-sm-6 col-12">
												<label style="display: block;">&nbsp; </label>
												<div class="form-group mb-0">
														<!-- <button type="button" id="btn_reset_filter" class="btn btn-outline-danger mr-1">Reset</button> -->
                            <a href="<?php echo currentUrl($_SERVER["REQUEST_URI"]); ?>"><button type="button" class="btn btn-outline-danger mr-1">Reset</button></a>
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
    //Date range picker for Order Date
    $('.datepicker_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY', 
        },  
        maxDate: moment().add(0, 'days'),
    });

    //Date range picker for Loading Date
    $('.datepicker_loading_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY', 
        }
    });
    
    $('.datepicker_report, .datepicker_loading_report').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    $('.datepicker_report, .datepicker_loading_report').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    $(document).on('keyup input', '#filter_keywords', function() {
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    $(document).on('change', '#filter_type', function() {
        if (typeof dataTable !== 'undefined') {
            dataTable.draw();
        }
    });

    // $(document).on('click', '#btn_reset_filter', function() {
    //     $('#filter_date_range').val('');
    //     $('#filter_loading_date_range').val('');
    //     $('#filter_keywords').val('');
    //     if (typeof dataTable !== 'undefined') {
    //         dataTable.draw();
    //     }
    // });
</script>