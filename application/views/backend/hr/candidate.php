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

<!-- Bordered table start -->
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
	
	<?php include '_filter_process.php';?>
	
     
      
      <div class="card-body">
        <table class="table table-bordered leads-table tfixed">
          <thead>
            <tr>
                <th>#</th>
                <th>Candidate Name</th>
                <th>Type</th>
                <th>State</th>
                <th>City</th>
                <th>HR Name</th>
                <th style="width:160px">Action</th>
            </tr>
          </thead>
          
          <tbody>
            <?php $start= page_number(20);
				foreach($orders as $key => $item): ?>
			<tr>
			  <td><?php echo ++$start;?></td>
			  <td><?php echo $item['name'];?> <br> <?php echo $item['phone'];?></td> 
			  <td><?php echo $item['staff_type'];?></td>
			  <td><?php echo $item['state_name'];?></td>
			  <td><?php echo $item['city_name'];?></td>
			  <td><?php echo $item['added_by_name'];?></td>
			  <td>
			     <button  type="button" class="btn icon-btn mr-1 mb-1"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasEnd"
                aria-controls="offcanvasEnd"
                onclick="get_timeline_(<?php echo $item['id'];?>);"><span>View Timeline</span></button> 
                <a href="javascript::" onclick="showAjaxModal('<?php echo site_url('modal/popup/modal_shortlist/'.$item['id']); ?>', 'Shortlist')" data-bs-toggle="tooltip" data-bs-placement="bottom"><button type="button" class="btn mr-1  mb-1 icon-btn-pass">Shortlist</button></a>
	            <a href="<?php echo site_url('hr/candidate/edit/'.$item['id']); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" ><button type="button" class="btn mr-1 mb-1 icon-btn-edit">Edit</button></a>
	           
	            </td>
			        
          	</tr>
			<?php endforeach; ?>
			
          </tbody> 
   		    <?php if (empty($orders)): ?>
			   <tr>
			   <td colspan="7">
			   <p class="text-center mt-1">Candidate Data Not Found</p> 
			  </td>
			</tr>
            <?php endif; ?>
            
          
          </table>
        
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
                <div class="offcanvas-header">
                  <h5 id="offcanvasEndLabel" class="offcanvas-title">Timeline</h5>
                  <button
                    type="button"
                    class="btn-close text-reset"
                    data-bs-dismiss="offcanvas"
                    aria-label="Close"
                  ></button>
                </div>
                <div class="offcanvas-body mx-0 flex-grow-0">
                  <ul class="timeline " id="timeline-body">
                   
                    
                  </ul>
                </div>
            </div>
            
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
<script>
    function get_timeline_(b) {
      var a = {
          candidate_id: b
      };
      $.ajax({
          type: "POST",
          url: "<?php echo base_url();?>hr/get_timeline_form",
          data: a,
          success: function(c) {
              $("#timeline-body").html(c);
          }
      })
    } 
</script>
