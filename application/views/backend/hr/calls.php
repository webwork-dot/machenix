<style>
    .df{display:flex;}
    i.ri-arrow-left-s-line{font-size:24px !important;margin-top: -3px;}
    i.ri-arrow-right-s-line{font-size:24px !important;margin-top: -3px;}
    .mx24{width: 200px;}
    h3.sc-hBUSln.fLxuBP{text-align: center;}
    .notf{
        margin:0px;padding:0px;padding-top: 10px !important;text-align:center;
    }
    .card-body.pt-0{padding: 0rem 1rem 1rem 1rem !important;}
</style>
<link href="https://cdn.jsdelivr.net/npm/remixicon@2.2.0/fonts/remixicon.css" rel="stylesheet">
</style>
<!-- Bordered table start -->
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
         <div class="col-sm-12 col-md-12 mb-0">
             <form class="form form-vertical" method="GET" id="myForm">
              <div class="form-body">
                 <div class="row">   
                      
                      <div class="col-md-4 align-ver">
                          <div class="df">
                               <i style="" class="ri-arrow-left-s-line"  onclick="PreFunction()"></i>
                               <div class="mx24">
                                   <input type="hidden" name="date" id="date" value="<?php if(isset($_GET['date'])) { echo $_GET['date']; }else{ echo  date("Y-m-d"); } ?>">
                                   <input type="hidden" name="c_date" id="c_date" value="<?php if(isset($_GET['c_date'])) { echo $_GET['c_date']; }else{ echo  date("Y-m-d"); } ?>">
                                   <h3 class="sc-hBUSln fLxuBP">
                                      <?php if(isset($_GET['date'])) { echo $_GET['date']; }else{ echo  date("d M Y | D"); } ?>
                                    </h3>
                               </div>
                               <?php 
                                    $current_date = strtotime(date("Y-m-d"));
                                 
                                    if(isset($_GET['c_date'])) 
                                    { 
                                        $new_date= strtotime($_GET['c_date']);
                                    }
                                    else{ 
                                        $new_date=strtotime(date("Y-m-d"));    
                                    }
                                    
                                    if($new_date>=$current_date){
                                         $disable="display:none";
                                    } else{
                                        $disable="";
                                    }
                                ?>
                               <i style="font-size:20px;<?php echo $disable;?>" id="next" class="ri-arrow-right-s-line" onclick="NextFunction()"></i>
                            </div>
                      </div>
                      
                      <div class="col-md-2 align-ver" style="float:left;">
                         <label class="" style="float:right;">Total: <?php echo $total_count;?></label>
                      </div>
                    
                      <div class="col-sm-12 col-md-4 ">
                      <form class="form form-vertical" method="GET">
                    	 <div class="form-body">
                    		<div class="row">
                    			
                    		   <div class="col-md-6">
                    			  <div class="form-group">
                    				<input name="keywords" class="form-control" placeholder="Keywords" type="keywords" value="<?php echo html_escape($this->input->get('keywords', true)); ?>">
                    			  </div>
                    		   </div>
                    		   <div class="col-md-6 col-12 no-padd">
                    			  <div class="form-group mb-0">
                    				  <button type="submit" name="search" value="true" class="btn btn-primary mr-1 mb-1">Search</button>
                    				  <?php if(isset($_GET['search'])):?>
                    				  <a href="<?php echo currentUrl($_SERVER["REQUEST_URI"]);?>"><button type="button" class="btn btn-outline-danger mr-1 mb-1">Reset</button> </a>
                    				  <?php endif;?>
                    			   </div>
                    		   </div>
                    		</div>
                    	 </div>
                      </form>
                    </div>
                    
                    <div class="col-md-2">
                         <a style="float:right;" href="<?php echo site_url('hr/calls/add'); ?>" class="dt-button add-new btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i data-feather='plus'></i> Add Calls</span></a>
                    </div>
                 </div>
              </div>
             </form>
         </div>
        </div>
      </div>
      
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
                <th>#</th>
                <th>Candidate  Name</th>
                <th>Called Type</th>
                <th>State</th>
                <th>Followup Date</th>
                <th>Followup Time</th>
                <th>Remark</th>
                <th>Action</th>
            </tr>
          </thead>
          
          <tbody>
            <?php foreach($orders as $key => $item): ?>
			<tr>
			  <td><?php echo $key+1;?></td> 
			  <td><?php echo $item['name'];?> <br> <?php echo $item['phone'];?></td> 
			  <td><?php echo $item['type'];?></td>
			  <td><?php echo $item['state_name'];?></td>
			  <td><?php echo $item['follow_up_date'];?></td>
			  <td><?php echo $item['follow_up_time'];?></td>
			  <td><?php echo $item['remark'];?></td> 
			  <td>
	            <a href="javascript::" onclick="showAjaxModal('<?php echo site_url('modal/popup/modal_calls_edit/'.$item['follow_id']); ?>', 'Update Calls')" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 icon-btn-edit"><i data-feather="edit-2"></i></button></a>
	           
	            </td>
          	</tr>
			<?php endforeach; ?>
			
          </tbody>
			   
		  <?php if (empty($orders)): ?> 
			  <tr>
			   <td colspan="10"><p class="notf">Calls Not Found</p></td>
			</tr>
		  <?php endif; ?>
          </table>
          <div class="d-flex justify-content-between mx-0 row">
           <div class="col-sm-12 col-md-12">
              <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_3_paginate">
                 <ul class="pagination justify-content-end mt-2">
                     <?php echo $this->pagination->create_links(); ?>
                 </ul>
              </div>
           </div>
          </div>
        
      </div>
    </div>
  </div>
</div>
<!-- Bordered table end -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script>
    function NextFunction() {
      const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "June",
              "July", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];
      var weekday = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];     
      var current_date = document.getElementById("c_date").value;
      const currentDayInMilli = new Date(current_date).getTime()
      const oneDay = 1000 * 60 *60 *24
      const previousDayInMilli = currentDayInMilli + oneDay
      const previousDate = new Date(previousDayInMilli)
      
      months   = previousDate.getMonth()  + 1, 
       yr      = previousDate.getFullYear(),
      month   = monthNames[previousDate.getMonth()],
      days   = weekday[previousDate.getDay()],
      day     = previousDate.getDate()  < 10 ? '0' + previousDate.getDate()  : previousDate.getDate(),
      newDate = day + ' ' + month + ' ' + yr + ' | ' + days;
     
     
     const d = new Date();
     yr_1      = previousDate.getFullYear(),
     month_1   = monthNames[d.getMonth()],
     days_1   = weekday[d.getDay()],
     day_1     = d.getDate()  < 10 ? '0' + d.getDate()  : d.getDate(),
     newDate_1 = day_1 + ' ' + month_1 + ' ' + yr_1 + ' | ' + days_1;
     newDate_2 = yr + '-' + months + '-' + day;
     if(newDate_1 == newDate){
         $("#next").hide();
     }
      
      document.getElementById("c_date").value = newDate_2
      document.getElementById("date").value = newDate
      $(".fLxuBP").html(newDate);
      
      document.getElementById("myForm").submit();
    }
    
    function PreFunction() {
      const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "June",
              "July", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];
      var weekday = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];        
      var current_date = document.getElementById("c_date").value;
      const currentDayInMilli = new Date(current_date).getTime()
      const oneDay = 1000 * 60 *60 *24
      const previousDayInMilli = currentDayInMilli - oneDay
      const previousDate = new Date(previousDayInMilli);
      
     months   = previousDate.getMonth() + 1,
     yr      = previousDate.getFullYear(),
     month   = monthNames[previousDate.getMonth()],
     days   = weekday[previousDate.getDay()],
     day     = previousDate.getDate()  < 10 ? '0' + previousDate.getDate()  : previousDate.getDate(),
     newDate = day + ' ' + month + ' ' + yr + ' | ' + days;
     
     const d = new Date();
     
     yr_1      = previousDate.getFullYear(),
     month_1   = monthNames[d.getMonth()],
     days_1   = weekday[d.getDay()],
     day_1     = d.getDate()  < 10 ? '0' + d.getDate()  : d.getDate(),
     newDate_1 = day_1 + ' ' + month_1 + ' ' + yr_1 + ' | ' + days_1;
     newDate_2 = yr + '-' + months + '-' + day;
     
     if(newDate_1 > newDate){
         $("#next").show();
     }
      document.getElementById("c_date").value = newDate_2
      document.getElementById("date").value = newDate
      $(".fLxuBP").html(newDate);
      
      document.getElementById("myForm").submit();
    }
    
    
    
</script>
