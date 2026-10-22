<div class="filter-accordion accordion mx-filter" id="accordionFilter">
   <div class="collapse-margin card">
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
						   <form class="form form-vertical" id="form_filter" method="GET" onsubmit="return checkForm(this);">
							<div class="form-body">
							<div class="row align-items-center">
										<input type="hidden" name="search" value="true">
										<?php
										// Preserve existing GET parameters
										foreach ($_GET as $key => $val) {
											if (!in_array($key, ['date_range', 'search', 'company_id'])) {
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
										<div class="col-md-3 col-12">
												<div class="form-group mb-0">   
														<label>Date Range</label>
														<input type="text" autocomplete="off" class="form-control bg-white datepicker_report" name="date_range" value="<?php if(isset($_GET['date_range'])) { echo htmlspecialchars($_GET['date_range']); }?>" placeholder="Search Date Range">
												</div>
										</div>

										<div class="col-md-6 col-12">
												<div class="form-group mb-0">   
														<label>Shortcuts</label>
														<div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
																<button type="button" class="btn btn-outline-primary btn-sm waves-effect date-shortcut-btn" data-range="today">Today</button>
																<button type="button" class="btn btn-outline-primary btn-sm waves-effect date-shortcut-btn" data-range="yesterday">Yesterday</button>
																<button type="button" class="btn btn-outline-primary btn-sm waves-effect date-shortcut-btn" data-range="this_month">This Month</button>
																<button type="button" class="btn btn-outline-primary btn-sm waves-effect date-shortcut-btn" data-range="last_month">Last Month</button>
																<button type="button" class="btn btn-outline-primary btn-sm waves-effect date-shortcut-btn" data-range="this_year">This Year</button>
														</div>
												</div>
										</div>

										<div class="col-md-3 col-12">
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

<style>
.date-shortcut-btn {
    font-weight: 600;
    font-size: 12px;
    padding: 0.45rem 0.8rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.date-shortcut-btn.active, .date-shortcut-btn:focus.active {
    background-color: #7367f0 !important;
    border-color: #7367f0 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(115, 103, 240, 0.4) !important;
}
</style>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
    // Date range picker initialization
    $('.datepicker_report').daterangepicker({
        autoUpdateInput: false,
        autoApply: false,
        locale: {
            format: 'DD-MM-YYYY', 
        },  
        maxDate: moment().add(0, 'days'),
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'This Month': [moment().startOf('month'), moment()],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'This Year': [moment().startOf('year'), moment()]
        }
    });

    $('.datepicker_report').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        highlightDateShortcut();
    });

    // Date Shortcuts click handler
    $(document).on('click', '.date-shortcut-btn', function(e) {
        e.preventDefault();
        var range = $(this).data('range');
        var start, end;
        
        if (range === 'today') {
            start = moment();
            end   = moment();
        } else if (range === 'yesterday') {
            start = moment().subtract(1, 'days');
            end   = moment().subtract(1, 'days');
        } else if (range === 'this_month') {
            start = moment().startOf('month');
            end   = moment();
        } else if (range === 'last_month') {
            start = moment().subtract(1, 'month').startOf('month');
            end   = moment().subtract(1, 'month').endOf('month');
        } else if (range === 'this_year') {
            start = moment().startOf('year');
            end   = moment();
        }
        
        if (start && end) {
            var formatted = start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY');
            $('.datepicker_report').val(formatted);
            $('.date-shortcut-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
            $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
            $('#form_filter').submit();
        }
    });

    function highlightDateShortcut() {
        if (!$('.date-shortcut-btn').length) return;
        var val = $.trim($('.datepicker_report').val());
        $('.date-shortcut-btn').removeClass('active btn-primary').addClass('btn-outline-primary');

        if (!val) {
            $('.date-shortcut-btn[data-range="this_month"]').removeClass('btn-outline-primary').addClass('active btn-primary');
            return;
        }

        var todayStr      = moment().format('DD-MM-YYYY') + ' - ' + moment().format('DD-MM-YYYY');
        var yestStr       = moment().subtract(1, 'days').format('DD-MM-YYYY') + ' - ' + moment().subtract(1, 'days').format('DD-MM-YYYY');
        var thisMonthStr1 = moment().startOf('month').format('DD-MM-YYYY') + ' - ' + moment().format('DD-MM-YYYY');
        var thisMonthStr2 = moment().startOf('month').format('DD-MM-YYYY') + ' - ' + moment().endOf('month').format('DD-MM-YYYY');
        var lastMonthStr  = moment().subtract(1, 'month').startOf('month').format('DD-MM-YYYY') + ' - ' + moment().subtract(1, 'month').endOf('month').format('DD-MM-YYYY');
        var thisYearStr1  = moment().startOf('year').format('DD-MM-YYYY') + ' - ' + moment().format('DD-MM-YYYY');
        var thisYearStr2  = moment().startOf('year').format('DD-MM-YYYY') + ' - ' + moment().endOf('year').format('DD-MM-YYYY');

        if (val === todayStr) {
            $('.date-shortcut-btn[data-range="today"]').removeClass('btn-outline-primary').addClass('active btn-primary');
        } else if (val === yestStr) {
            $('.date-shortcut-btn[data-range="yesterday"]').removeClass('btn-outline-primary').addClass('active btn-primary');
        } else if (val === thisMonthStr1 || val === thisMonthStr2) {
            $('.date-shortcut-btn[data-range="this_month"]').removeClass('btn-outline-primary').addClass('active btn-primary');
        } else if (val === lastMonthStr) {
            $('.date-shortcut-btn[data-range="last_month"]').removeClass('btn-outline-primary').addClass('active btn-primary');
        } else if (val === thisYearStr1 || val === thisYearStr2) {
            $('.date-shortcut-btn[data-range="this_year"]').removeClass('btn-outline-primary').addClass('active btn-primary');
        }
    }
    highlightDateShortcut();
</script>
