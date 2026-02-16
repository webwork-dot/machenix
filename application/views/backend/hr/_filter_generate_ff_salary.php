<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

 <div class="card-body border-bottom pb-5">
           <form class="form form-vertical" id="form_filter" method="GET">
              <input type="hidden" name="filter" value="">
              <div class="form-body">
                <div class="row">	
				
		
				 
			  <div class="col-12 col-sm-4 mb-1">
				  <label for="bills_pending">Month <i class="required">*</i></label>
				  <select name="month_id" class="form-control" required>
				  <option value="">Select</option>
				   <?php for($month = 1; $month <= 12; $month++) {
						$monthId = date('n', mktime(0, 0, 0, $month, 1));
						$monthName = date('F', mktime(0, 0, 0, $month, 1));
						$monthYear = date('Y', mktime(0, 0, 0, $month, 1));?>
					  <option value="<?= $monthId;?>"  <?php echo ($this->input->get('month_id', true) == $monthId) ? 'selected' : ''; ?>><?= $monthName.' - '.$monthYear;?></option>
					<?php } ?>							
				  </select>	
				</div>  

				<div class="col-md-3 col-12">
                       <div class="form-group mb-0">   
                        <label>keywords</label>
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
	 maxSpan: {days: 30},
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