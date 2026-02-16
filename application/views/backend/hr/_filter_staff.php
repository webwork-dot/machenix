<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

 <div class="card-body border-bottom pb-5">
           <form class="form form-vertical" id="form_filter" method="GET">
              <input type="hidden" name="filter" value="">
              <div class="form-body">
                 <div class="row">
				  <div class="col-md-3 col-12">
					 <div class="form-group mb-0">   
					  <label class="form-label">Date</label>
					  <div class="form-group">
						<input type="text" class="form-control datepicker_rg" name="date_range" value="<?php if(isset($_GET['date_range'])) { echo $_GET['date_range']; }?>" placeholder="Search  Date" autocomplete="off" readonly>
					 </div>
					</div>
				 </div>    
				 
				  <div class="col-md-3 col-12">
					  <div class="form-group mb-0">
						 <label class="form-label">Staff Type</label>
						 <select class="form-select" name="staff_type" placeholder="Staff Type">
							<option value="" >Select Staff Type</option>
							 <?php 
							 $staff_types=$this->hr_model->get_filter_staff_type();
							 foreach($staff_types as $stype){?>
							 <option value="<?php echo $stype['id'];?>" <?php if($this->input->get('staff_type') == $stype['id']){ echo 'selected';}?>><?php echo $stype['name'];?></option>
							 <?php }?>
						 </select>
					  </div>
					</div>
              

                 <div class="col-md-3 col-12">
                       <div class="form-group mb-0">   
                        <label class="form-label">keywords</label>
                        <div class="form-group">
                         <input name="keywords" class="form-control" placeholder="Keywords" type="keywords" value="<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>">
                      </div>
                     </div>
                 </div>
                 
                <div class="col-md-3">
                  <label style="display: block;">&nbsp; </label>
                    <div class="form-group mb-0">
                      <button type="submit" name="search" value="true" id="search"  class="btn btn-outline-dark  mr-1 mb-0">Search</button>
                      <?php if(isset($_GET['filter'])):?>
                      <a href="<?php echo currentUrl($_SERVER["REQUEST_URI"]);?>"><button type="button" class="btn btn-outline-danger mr-1 mb-0 " id="show">Reset</button> </a>
                     <?php endif;?>
                   </div>
                 </div>
                 
                 </div>
              </div>
           </form>
        </div>
          
<script>
$(document).ready(function () {
    //Date range picker
    $('.datepicker_rg').daterangepicker({
      autoUpdateInput: false,
    autoApply: true,
        locale: {
         format: 'DD-MM-YYYY', 
         cancelLabel: 'Clear'
    },  
      maxDate: moment().add(0, 'days'), // 30 days from the current day
    })
    //Date range picker with time picker

  $('.datepicker_rg').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
  });

  })
</script>