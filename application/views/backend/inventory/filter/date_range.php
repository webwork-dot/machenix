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
										<div class="col-md-3 col-12">
												<div class="form-group mb-0">   
														<label>Date</label>
														<input type="text" autocomplete="off" class="form-control bg-white datepicker_report" name="date_range" value="<?php if(isset($_GET['date_range'])) { echo $_GET['date_range']; }?>" placeholder="Search Order Date">
												</div>
										</div>

										<?php if ($page_name == 'purchase_reports') {?>
												<div class="col-md-3 col-12">
												<div class="form-group mb-0">   
														<label>Supplier</label>
														<select class="form-control select2" name="supplier_id">
																<option value="">All Suppliers</option>
																<?php 
																$suppliers = $this->db->query("SELECT id, name FROM supplier WHERE is_deleted='0' ORDER BY name ASC")->result_array();
																foreach($suppliers as $supplier): 
																		$selected = (isset($_GET['supplier_id']) && $_GET['supplier_id'] == $supplier['id']) ? 'selected' : '';
																?>
																<option value="<?= $supplier['id'] ?>" <?= $selected ?>><?= $supplier['name'] ?></option>
																<?php endforeach; ?>
														</select>
												</div>
										</div>
										<?php }?>

										<?php if ($page_name == 'sales_order') {?>
										<div class="col-md-3 col-12">
											<div class="form-group mb-0">   
												<label>Customer</label>
												<select class="form-control select2" name="customer_id">
													<option value="">All Customer</option>
													<?php 
													$companys = $this->db->query("SELECT id, name FROM customer WHERE is_deleted='0' ORDER BY name ASC")->result_array();
													foreach($companys as $supplier): 
															$selected = (isset($_GET['customer_id']) && $_GET['customer_id'] == $supplier['id']) ? 'selected' : '';
													?>
													<option value="<?= $supplier['id'] ?>" <?= $selected ?>><?= $supplier['name'] ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</div>
										<?php }?>
										
										<?php if ($page_name == 'sales_reports') {?>
										<div class="col-md-3 col-12">
											<div class="form-group mb-0">   
												<label>Company</label>
												<select class="form-control select2" name="company_id">
													<option value="">All Company</option>
													<?php 
													$companys = $this->db->query("SELECT id, name FROM company WHERE is_deleted='0' ORDER BY name ASC")->result_array();
													foreach($companys as $supplier): 
															$selected = (isset($_GET['company_id']) && $_GET['company_id'] == $supplier['id']) ? 'selected' : '';
													?>
													<option value="<?= $supplier['id'] ?>" <?= $selected ?>><?= $supplier['name'] ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</div>
										<?php }?>

										<div class="col-md-3">
												<label style="display: block;">&nbsp; </label>
												<div class="form-group mb-0">
														<button type="submit" name="search" value="true" id="btn_verify" class="btn btn-primary btn_verify mr-1 mb-0 waves-effect waves-float waves-light">Search</button>
														<?php if(isset($_GET['search'])): ?>
																<a href="<?php echo currentUrl($_SERVER["REQUEST_URI"]); ?>"><button type="button" class="btn btn-outline-danger mr-1">Reset</button></a>
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
        //autoclose: true, 
        locale: {
            format: 'DD-MM-YYYY', 
            //cancelLabel: 'Clear'
        },  
        maxDate: moment().add(0, 'days'), // 30 days from the current day
    })
        //Date range picker with time picker
    
    $('.datepicker_report').on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    });
</script>